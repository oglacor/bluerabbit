<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Frontend: enqueue assets for email-notifications page ────────────────────

add_action( 'wp_enqueue_scripts', 'br_email_frontend_assets' );
function br_email_frontend_assets(): void {
	if ( ! is_page( 'email-notifications' ) ) return;
	wp_enqueue_editor();
	wp_localize_script( 'jquery', 'brEmailFront', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'br_email_ajax' ),
	] );
}

// ── Frontend: permission helper ───────────────────────────────────────────────

function br_email_user_can_send( int $user_id, int $adventure_id ): bool {
	global $wpdb;

	if ( user_can( $user_id, 'manage_options' ) ) return true;

	$adv = $wpdb->get_row( $wpdb->prepare(
		"SELECT adventure_owner FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
		$adventure_id
	) );
	if ( $adv && (int) $adv->adventure_owner === $user_id ) return true;

	return (bool) $wpdb->get_var( $wpdb->prepare(
		"SELECT 1 FROM {$wpdb->prefix}br_player_adventure
		  WHERE player_id = %d AND adventure_id = %d AND player_adventure_role = 'gm'",
		$user_id,
		$adventure_id
	) );
}

// ── Frontend: AJAX send handler ───────────────────────────────────────────────

add_action( 'wp_ajax_br_send_notification_email', 'br_email_handle_notification_send' );
function br_email_handle_notification_send(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );

	$current_user = wp_get_current_user();
	$adventure_id = (int) ( $_POST['adventure_id'] ?? 0 );

	if ( ! $adventure_id || ! $current_user->ID ) {
		wp_send_json_error( [ 'message' => __( 'Missing adventure context.', 'bluerabbit' ) ] );
	}

	if ( ! br_email_user_can_send( $current_user->ID, $adventure_id ) ) {
		wp_send_json_error( [ 'message' => __( 'You do not have permission to send emails for this adventure.', 'bluerabbit' ) ] );
	}

	$subject    = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$body       = wp_kses_post( wp_unslash( $_POST['body'] ?? '' ) );
	$recipients = sanitize_text_field( $_POST['recipients'] ?? 'all' );

	if ( ! $subject || ! $body ) {
		wp_send_json_error( [ 'message' => __( 'Subject and body are required.', 'bluerabbit' ) ] );
	}

	global $wpdb;
	$adventure = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d AND adventure_status = 'publish'",
		$adventure_id
	) );

	if ( ! $adventure ) {
		wp_send_json_error( [ 'message' => __( 'Adventure not found.', 'bluerabbit' ) ] );
	}

	$sender_name  = sanitize_text_field( wp_unslash( $_POST['sender_name']  ?? '' ) );
	$sender_email = sanitize_email( wp_unslash( $_POST['sender_email'] ?? '' ) );
	if ( ! $sender_name || ! $sender_email ) {
		$sender_name  = $current_user->display_name;
		$sender_email = $current_user->user_email;
	}

	$from_name      = $sender_name . ' · ' . $adventure->adventure_title;
	$reply_to_email = $sender_email;
	$reply_to_name  = $sender_name;

	$mailer = new BR_Mailer();
	$mailer->set_sender_override( $from_name, $reply_to_email, $reply_to_name );
	$mailer->set_sender_id( $current_user->ID );

	$all_users = $mailer->get_adventure_users( $adventure_id );

	if ( $recipients !== 'all' ) {
		$player_ids = array_map( 'intval', explode( ',', $recipients ) );
		$player_ids = array_filter( $player_ids );
		if ( empty( $player_ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No recipients selected.', 'bluerabbit' ) ] );
		}
		$id_set = array_flip( $player_ids );
		$users  = array_values( array_filter( $all_users, function ( $u ) use ( $id_set ) {
			return isset( $id_set[ (int) $u['player_id'] ] );
		} ) );
	} else {
		$users = $all_users;
	}

	if ( empty( $users ) ) {
		wp_send_json_error( [ 'message' => __( 'No eligible recipients found.', 'bluerabbit' ) ] );
	}

	$result = $mailer->send_to_users( $users, $adventure_id, $subject, $body );

	$parts = [];
	if ( $result['queued'] && ! $result['sent'] && ! $result['failed'] ) {
		$parts[] = sprintf(
			_n( '%d email queued for delivery', '%d emails queued for delivery', $result['queued'], 'bluerabbit' ),
			$result['queued']
		);
		$parts[] = __( 'check the Send Log for status', 'bluerabbit' );
	} else {
		if ( $result['sent'] )   $parts[] = sprintf( _n( '%d email sent', '%d emails sent', $result['sent'], 'bluerabbit' ), $result['sent'] );
		if ( $result['failed'] ) $parts[] = sprintf( '%d failed', $result['failed'] );
		if ( $result['queued'] ) $parts[] = sprintf( '%d queued', $result['queued'] );
	}

	wp_send_json_success( [
		'sent'    => $result['sent'],
		'failed'  => $result['failed'],
		'queued'  => $result['queued'],
		'message' => implode( ' · ', $parts ),
	] );
}

// ── Frontend: CSV downloads ───────────────────────────────────────────────────

add_action( 'init', 'br_email_frontend_csv_handler' );
function br_email_frontend_csv_handler(): void {
	if ( ! is_user_logged_in() ) return;

	global $wpdb;
	$user_id = get_current_user_id();

	if ( ! empty( $_GET['br_fe_csv_sent'] ) ) {
		$cid    = (int) $_GET['br_fe_csv_sent'];
		$adv_id = (int) ( $_GET['adv_id'] ?? 0 );
		check_admin_referer( 'br_fe_csv_sent_' . $cid );
		if ( ! br_email_user_can_send( $user_id, $adv_id ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.sent_at
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'sent'
			  ORDER BY u.display_name",
			$cid
		) );
		br_email_output_csv( "sent-campaign-{$cid}.csv", $rows );
	}

	if ( ! empty( $_GET['br_fe_csv_failed'] ) ) {
		$cid    = (int) $_GET['br_fe_csv_failed'];
		$adv_id = (int) ( $_GET['adv_id'] ?? 0 );
		check_admin_referer( 'br_fe_csv_failed_' . $cid );
		if ( ! br_email_user_can_send( $user_id, $adv_id ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.detail, l.sent_at
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'failed'
			  ORDER BY l.sent_at DESC",
			$cid
		) );
		br_email_output_csv( "failed-campaign-{$cid}.csv", $rows );
	}

	if ( ! empty( $_GET['br_fe_csv_missing'] ) ) {
		$cid    = (int) $_GET['br_fe_csv_missing'];
		$adv_id = (int) ( $_GET['adv_id'] ?? 0 );
		check_admin_referer( 'br_fe_csv_missing_' . $cid );
		if ( ! br_email_user_can_send( $user_id, $adv_id ) ) wp_die( 'Forbidden', 403 );

		$enrolled = $wpdb->get_col( $wpdb->prepare(
			"SELECT player_id FROM {$wpdb->prefix}br_player_adventure
			  WHERE adventure_id = %d AND player_adventure_status = 'in'",
			$adv_id
		) );
		$reached  = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT user_id FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d",
			$cid
		) );
		$missing  = array_values( array_diff( $enrolled, $reached ) );

		$rows = [];
		if ( ! empty( $missing ) ) {
			$ph   = implode( ',', array_fill( 0, count( $missing ), '%d' ) );
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT display_name, user_email FROM {$wpdb->users}
					  WHERE ID IN ( {$ph} ) ORDER BY display_name",
					...$missing
				)
			);
		}
		br_email_output_csv( "missing-campaign-{$cid}.csv", $rows );
	}
}

// ── Frontend: retry handler ───────────────────────────────────────────────────

add_action( 'init', 'br_email_frontend_retry_handler' );
function br_email_frontend_retry_handler(): void {
	if ( ! is_user_logged_in() ) return;

	$user_id = get_current_user_id();

	if ( ! empty( $_GET['br_fe_retry'] ) ) {
		$cid    = (int) $_GET['br_fe_retry'];
		$adv_id = (int) ( $_GET['adv_id'] ?? 0 );
		check_admin_referer( 'br_fe_retry_' . $cid );
		if ( ! br_email_user_can_send( $user_id, $adv_id ) ) wp_die( 'Forbidden', 403 );

		$mailer = new BR_Mailer();
		$result = $mailer->retry_campaign( $cid );

		$back = add_query_arg( [
			'adventure_id' => $adv_id,
			'view'         => 'log',
			'log_campaign' => $cid,
			'log_tab'      => 'failed',
			'retried'      => $result['sent'],
			'retry_failed' => $result['failed'],
		], get_permalink( (int) $_GET['back_post'] ) );

		wp_redirect( $back );
		exit;
	}
}

// ── Frontend log: router ──────────────────────────────────────────────────────

function br_email_frontend_log( int $adv_id, string $log_base_url ): void {
	$campaign_id = (int) ( $_GET['log_campaign'] ?? 0 );
	if ( $campaign_id ) {
		br_email_frontend_log_detail( $campaign_id, $adv_id, $log_base_url );
	} else {
		br_email_frontend_log_list( $adv_id, $log_base_url );
	}
}

// ── Frontend log: campaigns list ──────────────────────────────────────────────

function br_email_frontend_log_list( int $adv_id, string $log_base_url ): void {
	global $wpdb;
	$log_table      = "{$wpdb->prefix}br_email_log";
	$campaign_table = "{$wpdb->prefix}br_email_campaigns";

	$campaigns = $wpdb->get_results( $wpdb->prepare(
		"SELECT c.campaign_id, c.subject, c.recipient_count, c.created_at,
		        sender.display_name AS sender_name,
		        SUM(CASE WHEN l.status = 'sent'   THEN 1 ELSE 0 END) AS sent_count,
		        SUM(CASE WHEN l.status = 'failed' THEN 1 ELSE 0 END) AS failed_count
		   FROM {$campaign_table} c
		   LEFT JOIN {$log_table} l       ON l.campaign_id = c.campaign_id
		   LEFT JOIN {$wpdb->users} sender ON sender.ID     = c.sender_id
		  WHERE c.adventure_id = %d
		  GROUP BY c.campaign_id
		  ORDER BY c.created_at DESC",
		$adv_id
	) );

	if ( empty( $campaigns ) ) {
		echo '<div class="br-panel br-notif-compose-panel">';
		echo '<p class="br-notif-log-hint">' . esc_html__( 'No emails have been sent yet for this adventure.', 'bluerabbit' ) . '</p>';
		echo '</div>';
		return;
	}
	?>
	<div class="br-panel br-notif-compose-panel">
		<table class="br-notif-log-table">
			<thead>
				<tr>
					<th class="br-notif-log-num">#</th>
					<th><?php esc_html_e( 'Date', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Sent by', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Target', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Sent', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Failed', 'bluerabbit' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $campaigns as $c ) :
					$sent    = (int) $c->sent_count;
					$failed  = (int) $c->failed_count;
					$detail_url = add_query_arg( [ 'view' => 'log', 'log_campaign' => (int) $c->campaign_id ], $log_base_url );
				?>
				<tr>
					<td class="br-notif-log-num"><?php echo (int) $c->campaign_id; ?></td>
					<td class="br-notif-log-date"><?php echo esc_html( $c->created_at ); ?></td>
					<td><?php echo esc_html( $c->subject ); ?></td>
					<td><?php echo esc_html( $c->sender_name ?: '—' ); ?></td>
					<td class="br-notif-log-count muted"><?php echo (int) $c->recipient_count; ?></td>
					<td class="br-notif-log-count green"><?php echo $sent; ?></td>
					<td class="br-notif-log-count <?php echo $failed > 0 ? 'red' : 'muted'; ?>"><?php echo $failed; ?></td>
					<td>
						<a href="<?php echo esc_url( $detail_url ); ?>" class="br-btn br-stats-btn-sm">
							<?php esc_html_e( 'Details →', 'bluerabbit' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

// ── Frontend log: campaign detail ─────────────────────────────────────────────

function br_email_frontend_log_detail( int $campaign_id, int $adv_id, string $log_base_url ): void {
	global $wpdb;
	$log_table      = "{$wpdb->prefix}br_email_log";
	$campaign_table = "{$wpdb->prefix}br_email_campaigns";

	$campaign = $wpdb->get_row( $wpdb->prepare(
		"SELECT c.*, sender.display_name AS sender_name
		   FROM {$campaign_table} c
		   LEFT JOIN {$wpdb->users} sender ON sender.ID = c.sender_id
		  WHERE c.campaign_id = %d AND c.adventure_id = %d",
		$campaign_id, $adv_id
	) );

	if ( ! $campaign ) {
		echo '<div class="br-panel br-notif-compose-panel"><p class="br-notif-log-hint">'
			. esc_html__( 'Campaign not found.', 'bluerabbit' ) . '</p></div>';
		return;
	}

	$valid_tabs  = [ 'sent', 'failed', 'missing' ];
	$tab         = in_array( $_GET['log_tab'] ?? 'sent', $valid_tabs, true ) ? ( $_GET['log_tab'] ?? 'sent' ) : 'sent';
	$paged       = max( 1, (int) ( $_GET['log_paged'] ?? 1 ) );
	$per_page    = 50;
	$offset      = ( $paged - 1 ) * $per_page;
	$detail_base = add_query_arg( [ 'view' => 'log', 'log_campaign' => $campaign_id ], $log_base_url );
	$tab_url     = fn( string $t ) => add_query_arg( 'log_tab', $t, $detail_base );

	// Tab counts
	$sent_count = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$log_table} WHERE campaign_id = %d AND status = 'sent'",
		$campaign_id
	) );
	$failed_count = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$log_table} WHERE campaign_id = %d AND status = 'failed'",
		$campaign_id
	) );

	$enrolled_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT player_id FROM {$wpdb->prefix}br_player_adventure
		  WHERE adventure_id = %d AND player_adventure_status = 'in'",
		$adv_id
	) );
	$reached_ids  = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT user_id FROM {$log_table} WHERE campaign_id = %d",
		$campaign_id
	) );
	$missing_ids   = array_values( array_diff( $enrolled_ids, $reached_ids ) );
	$missing_count = count( $missing_ids );

	// Tab data
	$tab_rows  = [];
	$tab_total = 0;
	$tab_pages = 1;

	if ( $tab === 'sent' ) {
		$tab_total = $sent_count;
		$tab_pages = max( 1, (int) ceil( $tab_total / $per_page ) );
		$tab_rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.sent_at
			   FROM {$log_table} l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'sent'
			  ORDER BY u.display_name LIMIT %d OFFSET %d",
			$campaign_id, $per_page, $offset
		) );

	} elseif ( $tab === 'failed' ) {
		$tab_total = $failed_count;
		$tab_pages = max( 1, (int) ceil( $tab_total / $per_page ) );
		$tab_rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.detail, l.sent_at
			   FROM {$log_table} l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'failed'
			  ORDER BY u.display_name LIMIT %d OFFSET %d",
			$campaign_id, $per_page, $offset
		) );

	} elseif ( $tab === 'missing' ) {
		$tab_total = $missing_count;
		$tab_pages = max( 1, (int) ceil( $tab_total / $per_page ) );
		$page_ids  = array_slice( $missing_ids, $offset, $per_page );
		if ( ! empty( $page_ids ) ) {
			$ph       = implode( ',', array_fill( 0, count( $page_ids ), '%d' ) );
			$tab_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT display_name, user_email FROM {$wpdb->users}
					  WHERE ID IN ( {$ph} ) ORDER BY display_name",
					...$page_ids
				)
			);
		}
	}

	// Retry notice
	$notice = '';
	if ( isset( $_GET['retried'] ) ) {
		$parts = [];
		$re    = (int) $_GET['retried'];
		$rf    = (int) ( $_GET['retry_failed'] ?? 0 );
		if ( $re ) $parts[] = sprintf( _n( '%d re-sent', '%d re-sent', $re, 'bluerabbit' ), $re );
		if ( $rf ) $parts[] = sprintf( _n( '%d failed again', '%d failed again', $rf, 'bluerabbit' ), $rf );
		if ( $parts ) {
			$notice = '<div id="br-notif-status" class="br-notif-status" style="display:block;background:rgba(28,194,235,0.08);border:1px solid rgba(28,194,235,0.25);color:#1cc2eb">'
				. implode( ' &bull; ', $parts ) . '</div>';
		}
	}

	$back_url  = add_query_arg( 'view', 'log', $log_base_url );
	$post_id   = get_queried_object_id();
	$user_id   = get_current_user_id();

	?>
	<a href="<?php echo esc_url( $back_url ); ?>" class="br-notif-back">
		&#8592; <?php esc_html_e( 'Send Log', 'bluerabbit' ); ?>
	</a>

	<?php echo $notice; ?>

	<div class="br-panel br-notif-compose-panel">
		<div class="br-notif-campaign-info">
			<span class="br-notif-campaign-info-key"><?php esc_html_e( 'Subject:', 'bluerabbit' ); ?></span>
			<span class="br-notif-campaign-info-val"><?php echo esc_html( $campaign->subject ); ?></span>
			<span class="br-notif-campaign-info-key"><?php esc_html_e( 'Sent by:', 'bluerabbit' ); ?></span>
			<span class="br-notif-campaign-info-val"><?php echo esc_html( $campaign->sender_name ?: '—' ); ?></span>
			<span class="br-notif-campaign-info-key"><?php esc_html_e( 'Date:', 'bluerabbit' ); ?></span>
			<span class="br-notif-campaign-info-val"><?php echo esc_html( $campaign->created_at ); ?></span>
			<span class="br-notif-campaign-info-key"><?php esc_html_e( 'Target:', 'bluerabbit' ); ?></span>
			<span class="br-notif-campaign-info-val"><?php echo (int) $campaign->recipient_count; ?> <?php esc_html_e( 'players', 'bluerabbit' ); ?></span>
		</div>

		<!-- Inner tabs -->
		<div class="br-notif-inner-tabs">
			<a href="<?php echo esc_url( $tab_url( 'sent' ) ); ?>" class="br-notif-inner-tab <?php echo $tab === 'sent' ? 'active' : ''; ?>">
				&#10003; <?php esc_html_e( 'Sent', 'bluerabbit' ); ?>
				<span class="br-notif-badge green"><?php echo $sent_count; ?></span>
			</a>
			<a href="<?php echo esc_url( $tab_url( 'failed' ) ); ?>" class="br-notif-inner-tab <?php echo $tab === 'failed' ? 'active' : ''; ?>">
				&#10007; <?php esc_html_e( 'Failed', 'bluerabbit' ); ?>
				<span class="br-notif-badge <?php echo $failed_count > 0 ? 'red' : 'muted'; ?>"><?php echo $failed_count; ?></span>
			</a>
			<a href="<?php echo esc_url( $tab_url( 'missing' ) ); ?>" class="br-notif-inner-tab <?php echo $tab === 'missing' ? 'active' : ''; ?>">
				&#9888; <?php esc_html_e( 'Missing', 'bluerabbit' ); ?>
				<span class="br-notif-badge <?php echo $missing_count > 0 ? 'amber' : 'muted'; ?>"><?php echo $missing_count; ?></span>
			</a>
		</div>

		<!-- Tab content -->
		<?php if ( $tab === 'sent' ) : ?>

			<div class="br-notif-log-toolbar">
				<span class="br-notif-log-hint">
					<?php printf( esc_html__( '%d delivered successfully.', 'bluerabbit' ), $sent_count ); ?>
				</span>
				<?php if ( $sent_count ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url(
					add_query_arg( [ 'br_fe_csv_sent' => $campaign_id, 'adv_id' => $adv_id ], get_permalink( $post_id ) ),
					'br_fe_csv_sent_' . $campaign_id
				) ); ?>" class="br-btn br-stats-btn-sm">
					&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
				</a>
				<?php endif; ?>
			</div>
			<?php if ( empty( $tab_rows ) ) : ?>
				<p class="br-notif-log-hint"><?php esc_html_e( 'No emails sent yet.', 'bluerabbit' ); ?></p>
			<?php else : ?>
			<table class="br-notif-log-table">
				<thead><tr>
					<th><?php esc_html_e( 'Name', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Email', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Sent at', 'bluerabbit' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $tab_rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( $r->display_name ); ?></td>
						<td><?php echo esc_html( $r->user_email ); ?></td>
						<td class="br-notif-log-date"><?php echo esc_html( $r->sent_at ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

		<?php elseif ( $tab === 'failed' ) : ?>

			<div class="br-notif-log-toolbar">
				<span class="br-notif-log-hint">
					<?php printf( esc_html__( '%d failed to deliver.', 'bluerabbit' ), $failed_count ); ?>
				</span>
				<?php if ( $failed_count ) : ?>
				<div class="br-actions br-gap-6">
					<a href="<?php echo esc_url( wp_nonce_url(
						add_query_arg( [ 'br_fe_retry' => $campaign_id, 'adv_id' => $adv_id, 'back_post' => $post_id ], get_permalink( $post_id ) ),
						'br_fe_retry_' . $campaign_id
					) ); ?>" class="br-btn br-stats-btn-sm"
					   onclick="return confirm('<?php esc_attr_e( 'Retry all failed emails?', 'bluerabbit' ); ?>');">
						&#8635; <?php esc_html_e( 'Retry All', 'bluerabbit' ); ?>
					</a>
					<a href="<?php echo esc_url( wp_nonce_url(
						add_query_arg( [ 'br_fe_csv_failed' => $campaign_id, 'adv_id' => $adv_id ], get_permalink( $post_id ) ),
						'br_fe_csv_failed_' . $campaign_id
					) ); ?>" class="br-btn br-stats-btn-sm">
						&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
					</a>
				</div>
				<?php endif; ?>
			</div>
			<?php if ( empty( $tab_rows ) ) : ?>
				<p class="br-notif-log-hint">&#10003; <?php esc_html_e( 'No failures — all delivered.', 'bluerabbit' ); ?></p>
			<?php else : ?>
			<table class="br-notif-log-table">
				<thead><tr>
					<th><?php esc_html_e( 'Name', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Email', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Error', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Date', 'bluerabbit' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $tab_rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( $r->display_name ); ?></td>
						<td><?php echo esc_html( $r->user_email ); ?></td>
						<td class="br-notif-log-error"><?php echo esc_html( mb_substr( $r->detail, 0, 200 ) ); ?></td>
						<td class="br-notif-log-date"><?php echo esc_html( $r->sent_at ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

		<?php elseif ( $tab === 'missing' ) : ?>

			<div class="br-notif-log-toolbar">
				<span class="br-notif-log-hint">
					<?php printf( esc_html__( '%d enrolled players not yet reached.', 'bluerabbit' ), $missing_count ); ?>
				</span>
				<?php if ( $missing_count ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url(
					add_query_arg( [ 'br_fe_csv_missing' => $campaign_id, 'adv_id' => $adv_id ], get_permalink( $post_id ) ),
					'br_fe_csv_missing_' . $campaign_id
				) ); ?>" class="br-btn br-stats-btn-sm">
					&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
				</a>
				<?php endif; ?>
			</div>
			<?php if ( empty( $tab_rows ) ) : ?>
				<p class="br-notif-log-hint">&#10003; <?php esc_html_e( 'All enrolled players have been reached.', 'bluerabbit' ); ?></p>
			<?php else : ?>
			<table class="br-notif-log-table">
				<thead><tr>
					<th><?php esc_html_e( 'Name', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Email', 'bluerabbit' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $tab_rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( $r->display_name ); ?></td>
						<td><?php echo esc_html( $r->user_email ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( $tab_pages > 1 ) :
			$paged_base = add_query_arg( 'log_tab', $tab, $detail_base );
		?>
		<div class="br-pagination" style="margin-top:14px">
			<?php
			echo paginate_links( [
				'base'      => add_query_arg( 'log_paged', '%#%', $paged_base ),
				'format'    => '',
				'current'   => $paged,
				'total'     => $tab_pages,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
			] );
			?>
		</div>
		<?php endif; ?>

	</div>
	<?php
}

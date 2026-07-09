<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Log page: router ─────────────────────────────────────────────────────────

function br_email_log_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$campaign_id = (int) ( $_GET['campaign_id'] ?? 0 );

	if ( $campaign_id ) {
		br_email_log_campaign_detail( $campaign_id );
	} else {
		br_email_log_campaigns_list();
	}
}

// ── View 1: Campaigns list ────────────────────────────────────────────────────

function br_email_log_campaigns_list(): void {
	global $wpdb;
	$log_table      = "{$wpdb->prefix}br_email_log";
	$campaign_table = "{$wpdb->prefix}br_email_campaigns";

	$adv_filter = (int) ( $_GET['adv_filter'] ?? 0 );

	$filter_adventures = $wpdb->get_results(
		"SELECT DISTINCT a.adventure_id, a.adventure_title
		   FROM {$wpdb->prefix}br_adventures a
		  WHERE a.adventure_id IN ( SELECT DISTINCT adventure_id FROM {$campaign_table} )
		  ORDER BY a.adventure_title"
	);

	if ( $adv_filter ) {
		$campaigns = $wpdb->get_results( $wpdb->prepare(
			"SELECT c.campaign_id, c.adventure_id, c.subject, c.recipient_count, c.created_at,
			        a.adventure_title, sender.display_name AS sender_name,
			        SUM(CASE WHEN l.status = 'sent'   THEN 1 ELSE 0 END) AS sent_count,
			        SUM(CASE WHEN l.status = 'failed' THEN 1 ELSE 0 END) AS failed_count
			   FROM {$campaign_table} c
			   LEFT JOIN {$log_table} l                      ON l.campaign_id  = c.campaign_id
			   LEFT JOIN {$wpdb->prefix}br_adventures a      ON a.adventure_id = c.adventure_id
			   LEFT JOIN {$wpdb->users} sender               ON sender.ID      = c.sender_id
			  WHERE c.adventure_id = %d
			  GROUP BY c.campaign_id
			  ORDER BY c.created_at DESC",
			$adv_filter
		) );
	} else {
		$campaigns = $wpdb->get_results(
			"SELECT c.campaign_id, c.adventure_id, c.subject, c.recipient_count, c.created_at,
			        a.adventure_title, sender.display_name AS sender_name,
			        SUM(CASE WHEN l.status = 'sent'   THEN 1 ELSE 0 END) AS sent_count,
			        SUM(CASE WHEN l.status = 'failed' THEN 1 ELSE 0 END) AS failed_count
			   FROM {$campaign_table} c
			   LEFT JOIN {$log_table} l                      ON l.campaign_id  = c.campaign_id
			   LEFT JOIN {$wpdb->prefix}br_adventures a      ON a.adventure_id = c.adventure_id
			   LEFT JOIN {$wpdb->users} sender               ON sender.ID      = c.sender_id
			  GROUP BY c.campaign_id
			  ORDER BY c.created_at DESC"
		);
	}

	$notice = br_email_retry_notice();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Send Log', 'bluerabbit' ); ?></h1>
		<?php echo $notice; ?>

		<?php if ( ! empty( $filter_adventures ) ) : ?>
		<form method="get" style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
			<input type="hidden" name="page" value="br_email_log">
			<label for="br-adv-filter" style="font-weight:600"><?php esc_html_e( 'Filter by Adventure:', 'bluerabbit' ); ?></label>
			<select id="br-adv-filter" name="adv_filter" onchange="this.form.submit()" style="max-width:300px">
				<option value="0"><?php esc_html_e( '— All Adventures —', 'bluerabbit' ); ?></option>
				<?php foreach ( $filter_adventures as $fa ) : ?>
					<option value="<?php echo (int) $fa->adventure_id; ?>" <?php selected( $adv_filter, (int) $fa->adventure_id ); ?>>
						<?php echo esc_html( $fa->adventure_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( $adv_filter ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=br_email_log' ) ); ?>" class="button">
					<?php esc_html_e( 'Clear', 'bluerabbit' ); ?>
				</a>
			<?php endif; ?>
		</form>
		<?php endif; ?>

		<?php if ( empty( $campaigns ) ) : ?>
			<p style="color:#999"><?php esc_html_e( 'No campaigns sent yet.', 'bluerabbit' ); ?></p>
		<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th style="width:40px">#</th>
					<th><?php esc_html_e( 'Date', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Adventure', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Sent by', 'bluerabbit' ); ?></th>
					<th style="width:60px;text-align:center"><?php esc_html_e( 'Target', 'bluerabbit' ); ?></th>
					<th style="width:60px;text-align:center"><?php esc_html_e( 'Sent', 'bluerabbit' ); ?></th>
					<th style="width:60px;text-align:center"><?php esc_html_e( 'Failed', 'bluerabbit' ); ?></th>
					<th style="width:90px"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $campaigns as $c ) : ?>
				<tr>
					<td style="color:#999"><?php echo (int) $c->campaign_id; ?></td>
					<td style="white-space:nowrap"><?php echo esc_html( $c->created_at ); ?></td>
					<td><?php echo esc_html( $c->adventure_title ?: "ID {$c->adventure_id}" ); ?></td>
					<td><?php echo esc_html( $c->subject ); ?></td>
					<td><?php echo esc_html( $c->sender_name ?: '—' ); ?></td>
					<td style="text-align:center;color:#888;font-weight:600"><?php echo (int) $c->recipient_count; ?></td>
					<td style="text-align:center;color:#46b450;font-weight:600"><?php echo (int) $c->sent_count; ?></td>
					<td style="text-align:center;font-weight:600;color:<?php echo $c->failed_count > 0 ? '#dc3232' : '#999'; ?>">
						<?php echo (int) $c->failed_count; ?>
					</td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=br_email_log&campaign_id=' . (int) $c->campaign_id ) ); ?>"
						   class="button button-small">
							<?php esc_html_e( 'Details →', 'bluerabbit' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php
}

// ── View 2: Campaign detail with tabs ────────────────────────────────────────

function br_email_log_campaign_detail( int $campaign_id ): void {
	global $wpdb;
	$log_table      = "{$wpdb->prefix}br_email_log";
	$campaign_table = "{$wpdb->prefix}br_email_campaigns";

	$campaign = $wpdb->get_row( $wpdb->prepare(
		"SELECT c.*, a.adventure_title, sender.display_name AS sender_name
		   FROM {$campaign_table} c
		   LEFT JOIN {$wpdb->prefix}br_adventures a ON a.adventure_id = c.adventure_id
		   LEFT JOIN {$wpdb->users} sender           ON sender.ID      = c.sender_id
		  WHERE c.campaign_id = %d",
		$campaign_id
	) );

	if ( ! $campaign ) {
		echo '<div class="wrap"><p>' . esc_html__( 'Campaign not found.', 'bluerabbit' ) . '</p></div>';
		return;
	}

	$adv_id   = (int) $campaign->adventure_id;
	$valid_tabs = [ 'sent', 'failed', 'missing' ];
	$tab      = in_array( $_GET['tab'] ?? 'sent', $valid_tabs, true ) ? ( $_GET['tab'] ?? 'sent' ) : 'sent';
	$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$per_page = 50;
	$offset   = ( $paged - 1 ) * $per_page;

	$base_url = admin_url( "admin.php?page=br_email_log&campaign_id={$campaign_id}" );
	$tab_url  = fn( string $t ) => add_query_arg( 'tab', $t, $base_url );

	// ── Tab counts ──────────────────────────────────────────────────────────

	$sent_count   = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$log_table} WHERE campaign_id = %d AND status = 'sent'",
		$campaign_id
	) );
	$failed_count = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$log_table} WHERE campaign_id = %d AND status = 'failed'",
		$campaign_id
	) );

	// Missing: all enrolled minus any log entry
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

	// ── Tab data ────────────────────────────────────────────────────────────

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
			  ORDER BY u.display_name
			  LIMIT %d OFFSET %d",
			$campaign_id, $per_page, $offset
		) );

	} elseif ( $tab === 'failed' ) {
		$tab_total = $failed_count;
		$tab_pages = max( 1, (int) ceil( $tab_total / $per_page ) );
		$tab_rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.detail, l.sent_at, l.log_id
			   FROM {$log_table} l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'failed'
			  ORDER BY u.display_name
			  LIMIT %d OFFSET %d",
			$campaign_id, $per_page, $offset
		) );

	} elseif ( $tab === 'missing' ) {
		$tab_total = $missing_count;
		$tab_pages = max( 1, (int) ceil( $tab_total / $per_page ) );
		$page_ids  = array_slice( $missing_ids, $offset, $per_page );

		if ( ! empty( $page_ids ) ) {
			$ph  = implode( ',', array_fill( 0, count( $page_ids ), '%d' ) );
			$tab_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT display_name, user_email FROM {$wpdb->users}
					  WHERE ID IN ( {$ph} ) ORDER BY display_name",
					...$page_ids
				)
			);
		}
	}

	// ── Render ──────────────────────────────────────────────────────────────

	$notice = br_email_retry_notice();

	$tab_cls = function( string $t ) use ( $tab ): string {
		$active = 'display:inline-block;padding:10px 18px;background:#fff;border:1px solid #ccd0d4;border-bottom-color:#fff;font-weight:600;text-decoration:none;color:#1d2327;margin-bottom:-1px;position:relative;';
		$idle   = 'display:inline-block;padding:10px 18px;color:#2271b1;text-decoration:none;border:1px solid transparent;';
		return $t === $tab ? $active : $idle;
	};

	?>
	<div class="wrap">
		<p style="margin-bottom:4px">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=br_email_log' ) ); ?>"
			   style="text-decoration:none;color:#2271b1;font-size:13px">
				&#8592; <?php esc_html_e( 'Send Log', 'bluerabbit' ); ?>
			</a>
		</p>
		<h1 style="margin-top:4px"><?php echo esc_html( $campaign->subject ); ?></h1>
		<?php echo $notice; ?>

		<table style="border-collapse:collapse;margin-bottom:20px;font-size:13px;color:#3c434a">
			<tr>
				<td style="padding:3px 20px 3px 0;color:#666"><?php esc_html_e( 'Adventure:', 'bluerabbit' ); ?></td>
				<td style="padding:3px 0;font-weight:600"><?php echo esc_html( $campaign->adventure_title ?: "ID {$campaign->adventure_id}" ); ?></td>
			</tr>
			<tr>
				<td style="padding:3px 20px 3px 0;color:#666"><?php esc_html_e( 'Sent by:', 'bluerabbit' ); ?></td>
				<td style="padding:3px 0"><?php echo esc_html( $campaign->sender_name ?: '—' ); ?></td>
			</tr>
			<tr>
				<td style="padding:3px 20px 3px 0;color:#666"><?php esc_html_e( 'Date:', 'bluerabbit' ); ?></td>
				<td style="padding:3px 0"><?php echo esc_html( $campaign->created_at ); ?></td>
			</tr>
			<tr>
				<td style="padding:3px 20px 3px 0;color:#666"><?php esc_html_e( 'Target:', 'bluerabbit' ); ?></td>
				<td style="padding:3px 0"><?php echo (int) $campaign->recipient_count; ?> <?php esc_html_e( 'players', 'bluerabbit' ); ?></td>
			</tr>
		</table>

		<div style="border-bottom:1px solid #ccd0d4">
			<a href="<?php echo esc_url( $tab_url( 'sent' ) ); ?>" style="<?php echo $tab_cls( 'sent' ); ?>">
				&#10003; <?php esc_html_e( 'Sent', 'bluerabbit' ); ?>
				<span style="background:#46b450;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px"><?php echo $sent_count; ?></span>
			</a>
			<a href="<?php echo esc_url( $tab_url( 'failed' ) ); ?>" style="<?php echo $tab_cls( 'failed' ); ?>">
				&#10007; <?php esc_html_e( 'Failed', 'bluerabbit' ); ?>
				<span style="background:<?php echo $failed_count > 0 ? '#dc3232' : '#999'; ?>;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px"><?php echo $failed_count; ?></span>
			</a>
			<a href="<?php echo esc_url( $tab_url( 'missing' ) ); ?>" style="<?php echo $tab_cls( 'missing' ); ?>">
				&#9888; <?php esc_html_e( 'Missing', 'bluerabbit' ); ?>
				<span style="background:<?php echo $missing_count > 0 ? '#f0b429' : '#999'; ?>;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px"><?php echo $missing_count; ?></span>
			</a>
		</div>

		<div style="background:#fff;border:1px solid #ccd0d4;border-top:none;padding:20px 24px">

			<?php if ( $tab === 'sent' ) : ?>

				<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
					<span style="color:#666">
						<?php printf( esc_html__( '%d email(s) delivered successfully.', 'bluerabbit' ), $sent_count ); ?>
					</span>
					<?php if ( $sent_count ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url(
						add_query_arg( [ 'br_email_csv_sent_campaign' => $campaign_id ], admin_url( 'admin.php' ) ),
						'br_csv_sent_' . $campaign_id
					) ); ?>" class="button button-small">
						&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
					</a>
					<?php endif; ?>
				</div>
				<?php if ( empty( $tab_rows ) ) : ?>
					<p style="color:#999"><?php esc_html_e( 'No emails sent yet.', 'bluerabbit' ); ?></p>
				<?php else : ?>
				<table class="widefat striped">
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
							<td style="white-space:nowrap;color:#666"><?php echo esc_html( $r->sent_at ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>

			<?php elseif ( $tab === 'failed' ) : ?>

				<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
					<span style="color:#666">
						<?php printf( esc_html__( '%d email(s) failed to deliver.', 'bluerabbit' ), $failed_count ); ?>
					</span>
					<?php if ( $failed_count ) : ?>
					<div style="display:flex;gap:8px">
						<a href="<?php echo esc_url( wp_nonce_url(
							add_query_arg( [ 'page' => 'br_email_log', 'br_retry_campaign' => $campaign_id, 'br_back_campaign' => $campaign_id ], admin_url( 'admin.php' ) ),
							'br_retry_' . $campaign_id
						) ); ?>" class="button button-small"
						   onclick="return confirm('<?php esc_attr_e( 'Retry all failed emails in this campaign?', 'bluerabbit' ); ?>');">
							&#8635; <?php esc_html_e( 'Retry All', 'bluerabbit' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_nonce_url(
							add_query_arg( [ 'br_email_csv_campaign' => $campaign_id ], admin_url( 'admin.php' ) ),
							'br_csv_' . $campaign_id
						) ); ?>" class="button button-small">
							&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>
				<?php if ( empty( $tab_rows ) ) : ?>
					<p style="color:#46b450">&#10003; <?php esc_html_e( 'No failures — all emails delivered.', 'bluerabbit' ); ?></p>
				<?php else : ?>
				<table class="widefat striped">
					<thead><tr>
						<th><?php esc_html_e( 'Name', 'bluerabbit' ); ?></th>
						<th><?php esc_html_e( 'Email', 'bluerabbit' ); ?></th>
						<th><?php esc_html_e( 'Error', 'bluerabbit' ); ?></th>
						<th><?php esc_html_e( 'Date', 'bluerabbit' ); ?></th>
						<th style="width:50px"></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $tab_rows as $r ) : ?>
						<tr>
							<td><?php echo esc_html( $r->display_name ); ?></td>
							<td><?php echo esc_html( $r->user_email ); ?></td>
							<td style="font-size:11px;color:#dc3232;max-width:280px;word-break:break-all">
								<?php echo esc_html( mb_substr( $r->detail, 0, 200 ) ); ?>
							</td>
							<td style="white-space:nowrap;color:#666"><?php echo esc_html( $r->sent_at ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url(
									add_query_arg( [
										'page'            => 'br_email_log',
										'br_retry_single' => $r->log_id,
										'br_back_campaign'=> $campaign_id,
									], admin_url( 'admin.php' ) ),
									'br_retry_single_' . $r->log_id
								) ); ?>" class="button button-small"
								   onclick="return confirm('<?php esc_attr_e( 'Retry this email?', 'bluerabbit' ); ?>');"
								   title="<?php esc_attr_e( 'Retry', 'bluerabbit' ); ?>">&#8635;</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>

			<?php elseif ( $tab === 'missing' ) : ?>

				<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
					<span style="color:#666">
						<?php printf(
							esc_html__( '%d enrolled player(s) have not received this email.', 'bluerabbit' ),
							$missing_count
						); ?>
					</span>
					<?php if ( $missing_count ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url(
						add_query_arg( [ 'br_email_csv_missing' => $campaign_id ], admin_url( 'admin.php' ) ),
						'br_csv_missing_' . $campaign_id
					) ); ?>" class="button button-small">
						&#128196; <?php esc_html_e( 'Download CSV', 'bluerabbit' ); ?>
					</a>
					<?php endif; ?>
				</div>
				<?php if ( empty( $tab_rows ) ) : ?>
					<p style="color:#46b450">&#10003; <?php esc_html_e( 'All enrolled players have been reached.', 'bluerabbit' ); ?></p>
				<?php else : ?>
				<table class="widefat striped">
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

			<?php if ( $tab_pages > 1 ) : ?>
			<div class="tablenav bottom" style="margin-top:16px">
				<div class="tablenav-pages">
					<?php
					echo paginate_links( [
						'base'    => add_query_arg( 'paged', '%#%', add_query_arg( 'tab', $tab, $base_url ) ),
						'format'  => '',
						'current' => $paged,
						'total'   => $tab_pages,
					] );
					?>
				</div>
			</div>
			<?php endif; ?>

		</div>
	</div>
	<?php
}

// ── Shared: retry notice ──────────────────────────────────────────────────────

function br_email_retry_notice(): string {
	if ( ! isset( $_GET['br_retried'] ) ) return '';
	$retried      = (int) $_GET['br_retried'];
	$retry_failed = (int) ( $_GET['br_retry_failed'] ?? 0 );
	$parts = [];
	if ( $retried )      $parts[] = sprintf( _n( '%d email re-sent', '%d emails re-sent', $retried, 'bluerabbit' ), $retried );
	if ( $retry_failed ) $parts[] = sprintf( _n( '%d failed again', '%d failed again', $retry_failed, 'bluerabbit' ), $retry_failed );
	if ( ! $parts ) return '';
	return '<div class="notice notice-info is-dismissible"><p>' . implode( ' &bull; ', $parts ) . '</p></div>';
}

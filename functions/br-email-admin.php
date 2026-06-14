<?php
/**
 * BLUERABBIT Email Notification System — Admin UI
 *
 * Settings:  WP Admin → BR Email → Settings    (slug: br_email_settings)
 * Compose:   WP Admin → BR Email → Compose     (slug: br_email_compose)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── DB table ──────────────────────────────────────────────────────────────────


// ── Admin menu ────────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'br_email_register_menus' );
function br_email_register_menus(): void {
	add_menu_page(
		__( 'BR Email', 'bluerabbit' ),
		__( 'BR Email', 'bluerabbit' ),
		'manage_options',
		'br_email_settings',
		'br_email_settings_page',
		'dashicons-email-alt',
		80
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Email Settings', 'bluerabbit' ),
		__( 'Settings', 'bluerabbit' ),
		'manage_options',
		'br_email_settings',
		'br_email_settings_page'
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Compose & Send', 'bluerabbit' ),
		__( 'Compose & Send', 'bluerabbit' ),
		'manage_options',
		'br_email_compose',
		'br_email_compose_page'
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Send Log', 'bluerabbit' ),
		__( 'Send Log', 'bluerabbit' ),
		'manage_options',
		'br_email_log',
		'br_email_log_page'
	);
}

// ── Enqueue admin assets ──────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', 'br_email_enqueue_scripts' );
function br_email_enqueue_scripts( string $hook ): void {
	if ( strpos( $hook, 'br_email' ) === false ) return;

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_media();

	// Inline JS: colour picker init + media uploader + adventure user count
	wp_add_inline_script( 'wp-color-picker', '
jQuery(function($){
	/* Colour pickers */
	$(".br-color-picker").wpColorPicker();

	/* Media uploader */
	$(document).on("click", ".br-upload-btn", function(e){
		e.preventDefault();
		var btn    = $(this);
		var target = btn.data("target");
		var frame  = wp.media({
			title:    "Select Logo",
			button:   { text: "Use this image" },
			multiple: false
		});
		frame.on("select", function(){
			var att = frame.state().get("selection").first().toJSON();
			$("#" + target).val(att.url);
			var prev = btn.siblings(".br-logo-preview");
			if(prev.length){ prev.attr("src", att.url).show(); }
		});
		frame.open();
	});

	/* Adventure → user count */
	$(document).on("change", "#br_adventure_id", function(){
		var adv_id = $(this).val();
		var badge  = $("#br_user_count");
		if(!adv_id){ badge.text("—"); return; }
		$.post(ajaxurl, { action:"br_email_user_count", adventure_id:adv_id,
		                  nonce:brEmail.nonce }, function(r){
			badge.text(r.success ? r.data.count + " recipients" : "?");
		});
	});

	/* Preview */
	$(document).on("click", "#br_preview_btn", function(e){
		e.preventDefault();
		var subject  = $("#br_subject").val();
		var body_val = "";
		if(typeof tinyMCE !== "undefined" && tinyMCE.get("br_email_body")){
			body_val = tinyMCE.get("br_email_body").getContent();
		} else {
			body_val = $("#br_email_body").val();
		}
		$.post(ajaxurl, {
			action:   "br_email_preview",
			subject:  subject,
			body:     body_val,
			nonce:    brEmail.nonce
		}, function(r){
			if(!r.success) return;
			var win = window.open("", "_blank",
				"width=700,height=600,scrollbars=yes,resizable=yes");
			win.document.write(r.data.html);
			win.document.close();
		});
	});
});
' );

	// Pass nonce to JS
	wp_localize_script( 'wp-color-picker', 'brEmail', [
		'nonce' => wp_create_nonce( 'br_email_ajax' ),
	] );
}

// ── AJAX: user count ──────────────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_user_count', 'br_email_ajax_user_count' );
function br_email_ajax_user_count(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( null, 403 );

	$adv_id = (int) ( $_POST['adventure_id'] ?? 0 );
	if ( ! $adv_id ) wp_send_json_error( 'missing adventure_id' );

	$mailer = new BR_Mailer();
	wp_send_json_success( [ 'count' => $mailer->count_adventure_users( $adv_id ) ] );
}

// ── AJAX: preview ─────────────────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_preview', 'br_email_ajax_preview' );
function br_email_ajax_preview(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( null, 403 );

	$settings = get_option( 'br_email_settings', [] );
	$settings['_adventure_name'] = 'Sample Adventure';

	$mailer  = new BR_Mailer();
	$subject = sanitize_text_field( $_POST['subject'] ?? 'Preview Subject' );
	$body    = wp_kses_post( $_POST['body'] ?? '<p>Hello {{name}},</p><p>This is a preview.</p>' );

	$dummy_user = [
		'player_id'    => 0,
		'user_email'   => 'preview@example.com',
		'display_name' => 'Preview User',
	];

	$html = $mailer->render_template( $settings, $subject, $body, $dummy_user );
	wp_send_json_success( [ 'html' => $html ] );
}

// ── Settings page ─────────────────────────────────────────────────────────────

function br_email_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$saved   = get_option( 'br_email_settings', [] );
	$notice  = '';

	if ( isset( $_GET['br_saved'] ) ) {
		$notice = '<div class="notice notice-success is-dismissible"><p>'
		        . esc_html__( 'Email settings saved.', 'bluerabbit' )
		        . '</p></div>';
	}
	if ( isset( $_GET['br_error'] ) ) {
		$notice = '<div class="notice notice-error"><p>'
		        . esc_html__( 'Error saving settings. Please try again.', 'bluerabbit' )
		        . '</p></div>';
	}

	$logo_url      = esc_url( $saved['logo_url']      ?? '' );
	$primary       = esc_attr( $saved['primary_color'] ?? '#1cc2eb' );
	$accent        = esc_attr( $saved['accent_color']  ?? '#9f40e2' );
	$from_name     = esc_attr( $saved['from_name']     ?? get_bloginfo( 'name' ) );
	$from_email    = esc_attr( $saved['from_email']    ?? get_bloginfo( 'admin_email' ) );
	$has_key       = ! empty( $saved['api_key'] );
	$email_service = esc_attr( $saved['email_service'] ?? 'resend' );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Settings', 'bluerabbit' ); ?></h1>
		<?php echo $notice; // Already escaped above ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'br_save_email_settings', 'br_settings_nonce' ); ?>
			<input type="hidden" name="action" value="br_save_email_settings">

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row">
						<label for="br_logo_url"><?php esc_html_e( 'Logo URL', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="text" id="br_logo_url" name="br_email[logo_url]"
							value="<?php echo $logo_url; ?>"
							class="regular-text" placeholder="https://…/logo.png">
						<button type="button" class="button br-upload-btn" data-target="br_logo_url">
							<?php esc_html_e( 'Choose Image', 'bluerabbit' ); ?>
						</button>
						<?php if ( $logo_url ) : ?>
							<br><img class="br-logo-preview" src="<?php echo $logo_url; ?>"
								style="max-height:60px;margin-top:8px;">
						<?php else : ?>
							<br><img class="br-logo-preview" src="" style="max-height:60px;margin-top:8px;display:none;">
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_primary_color"><?php esc_html_e( 'Primary colour', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="text" id="br_primary_color" name="br_email[primary_color]"
							value="<?php echo $primary; ?>"
							class="br-color-picker" data-default-color="#1cc2eb">
						<p class="description"><?php esc_html_e( 'Header background colour.', 'bluerabbit' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_accent_color"><?php esc_html_e( 'Accent colour', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="text" id="br_accent_color" name="br_email[accent_color]"
							value="<?php echo $accent; ?>"
							class="br-color-picker" data-default-color="#9f40e2">
						<p class="description"><?php esc_html_e( 'Links and footer accent colour.', 'bluerabbit' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_from_name"><?php esc_html_e( 'From name', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="text" id="br_from_name" name="br_email[from_name]"
							value="<?php echo $from_name; ?>" class="regular-text">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_from_email"><?php esc_html_e( 'From email', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="email" id="br_from_email" name="br_email[from_email]"
							value="<?php echo $from_email; ?>" class="regular-text">
						<p class="description">
							<?php esc_html_e( 'Must be verified in your email service dashboard.', 'bluerabbit' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_email_service"><?php esc_html_e( 'Email service', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<select id="br_email_service" name="br_email[email_service]">
							<option value="resend"   <?php selected( $email_service, 'resend' ); ?>>Resend</option>
							<option value="sendgrid" <?php selected( $email_service, 'sendgrid' ); ?>>SendGrid</option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_api_key"><?php esc_html_e( 'API key', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="password" id="br_api_key" name="br_email[api_key]"
							value="" class="regular-text"
							autocomplete="new-password"
							placeholder="<?php echo $has_key
								? esc_attr__( '(leave blank to keep existing key)', 'bluerabbit' )
								: esc_attr__( 'Paste API key here', 'bluerabbit' ); ?>">
						<?php if ( $has_key ) : ?>
							<span style="color:#46b450;margin-left:8px;">&#10003; <?php esc_html_e( 'Key saved', 'bluerabbit' ); ?></span>
						<?php endif; ?>
						<p class="description">
							<?php esc_html_e( 'Stored encrypted in the database.', 'bluerabbit' ); ?>
						</p>
					</td>
				</tr>

			</table>

			<?php submit_button( __( 'Save Settings', 'bluerabbit' ) ); ?>
		</form>
	</div>
	<?php
}

// ── Settings save handler ─────────────────────────────────────────────────────

add_action( 'admin_post_br_save_email_settings', 'br_email_save_settings' );
function br_email_save_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'br_save_email_settings', 'br_settings_nonce' );

	$input   = $_POST['br_email'] ?? [];
	$current = get_option( 'br_email_settings', [] );

	$new = [
		'logo_url'      => esc_url_raw( $input['logo_url']      ?? '' ),
		'primary_color' => sanitize_hex_color( $input['primary_color'] ?? '#1cc2eb' ) ?: '#1cc2eb',
		'accent_color'  => sanitize_hex_color( $input['accent_color']  ?? '#9f40e2' ) ?: '#9f40e2',
		'from_name'     => sanitize_text_field( $input['from_name']    ?? '' ),
		'from_email'    => sanitize_email( $input['from_email']         ?? '' ),
		'email_service' => in_array( $input['email_service'] ?? '', [ 'resend', 'sendgrid' ], true )
			? $input['email_service']
			: 'resend',
	];

	// Only replace API key if a new one was provided
	$raw_key = trim( $input['api_key'] ?? '' );
	if ( $raw_key !== '' ) {
		$new['api_key'] = BR_Mailer::encrypt_key( $raw_key );
	} elseif ( ! empty( $current['api_key'] ) ) {
		$new['api_key'] = $current['api_key'];
	}

	update_option( 'br_email_settings', $new );

	wp_redirect( add_query_arg(
		[ 'page' => 'br_email_settings', 'br_saved' => '1' ],
		admin_url( 'admin.php' )
	) );
	exit;
}

// ── Compose page ──────────────────────────────────────────────────────────────

function br_email_compose_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	global $wpdb;

	$adventures = $wpdb->get_results(
		"SELECT adventure_id, adventure_title
		   FROM {$wpdb->prefix}br_adventures
		  WHERE adventure_status = 'publish'
		  ORDER BY adventure_title ASC"
	);

	$notice = '';
	if ( isset( $_GET['br_sent'] ) ) {
		$sent   = (int) $_GET['br_sent'];
		$failed = (int) ( $_GET['br_failed'] ?? 0 );
		$queued = (int) ( $_GET['br_queued'] ?? 0 );
		$parts  = [ sprintf( _n( '%d email sent', '%d emails sent', $sent, 'bluerabbit' ), $sent ) ];
		if ( $failed ) $parts[] = sprintf( _n( '%d failed', '%d failed', $failed, 'bluerabbit' ), $failed );
		if ( $queued ) $parts[] = sprintf(
			_n( '%d queued via background job', '%d queued via background jobs', $queued, 'bluerabbit' ),
			$queued
		);
		$notice = '<div class="notice notice-success is-dismissible"><p>' . implode( ' &bull; ', $parts ) . '</p></div>';
	}
	if ( isset( $_GET['br_error'] ) ) {
		$msg = sanitize_text_field( urldecode( $_GET['br_error'] ) );
		$notice = '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Compose & Send', 'bluerabbit' ); ?></h1>
		<?php echo $notice; // Already escaped above ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			id="br_compose_form">
			<?php wp_nonce_field( 'br_send_adventure_email', 'br_compose_nonce' ); ?>
			<input type="hidden" name="action" value="br_send_adventure_email">

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row">
						<label for="br_adventure_id"><?php esc_html_e( 'Adventure', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<select id="br_adventure_id" name="br_adventure_id" required>
							<option value=""><?php esc_html_e( '— Select an adventure —', 'bluerabbit' ); ?></option>
							<?php foreach ( $adventures as $adv ) : ?>
								<option value="<?php echo (int) $adv->adventure_id; ?>">
									<?php echo esc_html( $adv->adventure_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span id="br_user_count"
							style="margin-left:12px;font-style:italic;color:#555;">—</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="br_subject"><?php esc_html_e( 'Subject', 'bluerabbit' ); ?></label>
					</th>
					<td>
						<input type="text" id="br_subject" name="br_subject"
							class="large-text" required
							placeholder="<?php esc_attr_e( 'Email subject line…', 'bluerabbit' ); ?>">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Message', 'bluerabbit' ); ?>
					</th>
					<td>
						<p class="description" style="margin-bottom:10px;">
							<?php esc_html_e(
								'Available merge tags: {{name}}, {{adventure_name}}, {{site_name}}',
								'bluerabbit'
							); ?>
						</p>
						<?php
						wp_editor( '', 'br_email_body', [
							'textarea_name' => 'br_email_body',
							'textarea_rows' => 18,
							'tinymce'       => [
								'toolbar1'      => 'formatselect,|,bold,italic,underline,strikethrough,|,forecolor,backcolor,|,link,unlink',
								'toolbar2'      => 'bullist,numlist,blockquote,|,alignleft,aligncenter,alignright,|,hr,removeformat,|,undo,redo',
								'block_formats' => 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Blockquote=blockquote;Preformatted=pre',
							],
						] );
						?>
					</td>
				</tr>

			</table>

			<p>
				<button type="button" id="br_preview_btn" class="button button-secondary">
					<?php esc_html_e( '&#128065; Preview Email', 'bluerabbit' ); ?>
				</button>
				&nbsp;
				<button type="submit" name="br_confirm_send" value="1" class="button button-primary"
					onclick="return confirm('<?php
						esc_attr_e( 'Send this email to all enrolled users in the selected adventure?', 'bluerabbit' );
					?>');">
					<?php esc_html_e( '&#9993; Send Email', 'bluerabbit' ); ?>
				</button>
			</p>

		</form>
	</div>
	<?php
}

// ── Send handler ──────────────────────────────────────────────────────────────

add_action( 'admin_post_br_send_adventure_email', 'br_email_handle_send' );
function br_email_handle_send(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'br_send_adventure_email', 'br_compose_nonce' );

	$adventure_id = (int) ( $_POST['br_adventure_id'] ?? 0 );
	$subject      = sanitize_text_field( $_POST['br_subject']    ?? '' );
	$body         = wp_kses_post( $_POST['br_email_body']         ?? '' );

	if ( ! $adventure_id || ! $subject || ! $body ) {
		wp_redirect( add_query_arg(
			[ 'page' => 'br_email_compose', 'br_error' => rawurlencode( 'Adventure, subject and body are all required.' ) ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	$mailer = new BR_Mailer();
	$result = $mailer->send_to_adventure( $adventure_id, $subject, $body );

	wp_redirect( add_query_arg(
		[
			'page'      => 'br_email_compose',
			'br_sent'   => $result['sent'],
			'br_failed' => $result['failed'],
			'br_queued' => $result['queued'],
		],
		admin_url( 'admin.php' )
	) );
	exit;
}

// ── Log page ──────────────────────────────────────────────────────────────────

function br_email_log_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	global $wpdb;

	$per_page = 50;
	$page_num = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$offset   = ( $page_num - 1 ) * $per_page;

	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}br_email_log" );
	$logs  = $wpdb->get_results( $wpdb->prepare(
		"SELECT l.log_id, l.user_id, l.adventure_id, l.subject, l.status,
		        IFNULL(l.detail,'') AS detail, l.sent_at,
		        u.display_name, a.adventure_title
		   FROM {$wpdb->prefix}br_email_log l
		   LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id
		   LEFT JOIN {$wpdb->prefix}br_adventures a ON a.adventure_id = l.adventure_id
		  ORDER BY l.sent_at DESC
		  LIMIT %d OFFSET %d",
		$per_page,
		$offset
	) );

	$pages = ceil( $total / $per_page );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Send Log', 'bluerabbit' ); ?></h1>

		<p><?php printf(
			esc_html__( 'Showing %d–%d of %d entries', 'bluerabbit' ),
			$offset + 1,
			min( $offset + $per_page, $total ),
			$total
		); ?></p>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'User', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Adventure', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'Status', 'bluerabbit' ); ?></th>
					<th><?php esc_html_e( 'API Response', 'bluerabbit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No entries yet.', 'bluerabbit' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $logs as $row ) :
						$status_colour = $row->status === 'sent' ? '#46b450' : '#dc3232';
					?>
					<tr>
						<td><?php echo esc_html( $row->sent_at ); ?></td>
						<td><?php echo esc_html( $row->display_name ?: "ID {$row->user_id}" ); ?></td>
						<td><?php echo esc_html( $row->adventure_title ?: "ID {$row->adventure_id}" ); ?></td>
						<td><?php echo esc_html( $row->subject ); ?></td>
						<td style="color:<?php echo esc_attr( $status_colour ); ?>;font-weight:bold;">
							<?php echo esc_html( ucfirst( $row->status ) ); ?>
						</td>
						<td style="font-size:12px;color:#555;max-width:300px;word-break:break-all;">
							<?php echo esc_html( $row->detail ?? '' ); ?>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo paginate_links( [
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $page_num,
						'total'   => $pages,
					] );
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

// ── Unsubscribe handler ───────────────────────────────────────────────────────

add_action( 'init', 'br_email_handle_unsubscribe' );
function br_email_handle_unsubscribe(): void {
	if ( empty( $_GET['br_email_unsub'] ) || empty( $_GET['uid'] ) ) return;

	$user_id = (int) $_GET['uid'];
	$token   = sanitize_text_field( $_GET['br_email_unsub'] );
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		wp_die( esc_html__( 'Invalid unsubscribe link.', 'bluerabbit' ), '', [ 'response' => 400 ] );
	}

	$expected = BR_Mailer::unsub_token( $user_id, $user->user_email );

	if ( ! hash_equals( $expected, $token ) ) {
		wp_die( esc_html__( 'Invalid unsubscribe token.', 'bluerabbit' ), '', [ 'response' => 403 ] );
	}

	update_user_meta( $user_id, 'br_email_optout', 1 );

	wp_die(
		'<p style="font-family:sans-serif;text-align:center;margin-top:60px;">'
		. esc_html__( 'You have been unsubscribed from BlueRabbit email notifications.', 'bluerabbit' )
		. '</p>',
		esc_html__( 'Unsubscribed', 'bluerabbit' ),
		[ 'response' => 200 ]
	);
}

// ── Frontend: enqueue TinyMCE + pass nonce for email-notifications page ───────

add_action( 'wp_enqueue_scripts', 'br_email_frontend_assets' );
function br_email_frontend_assets(): void {
	if ( ! is_page( 'email-notifications' ) ) return;
	wp_enqueue_editor();
	// jQuery is already loaded by BR; attach nonce data to it
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

	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$body    = wp_kses_post( wp_unslash( $_POST['body'] ?? '' ) );

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

	// Build the sender context from the adventure owner
	$owner     = get_userdata( (int) $adventure->adventure_owner );
	$from_name = $owner
		? $owner->display_name . ' · ' . $adventure->adventure_title
		: $adventure->adventure_title;
	$reply_to_email = $owner ? $owner->user_email : '';
	$reply_to_name  = $owner ? $owner->display_name : '';

	$mailer = new BR_Mailer();
	$mailer->set_sender_override( $from_name, $reply_to_email, $reply_to_name );

	$result = $mailer->send_to_adventure( $adventure_id, $subject, $body );

	wp_send_json_success( [
		'sent'    => $result['sent'],
		'failed'  => $result['failed'],
		'queued'  => $result['queued'],
		'message' => sprintf(
			_n( '%d email sent', '%d emails sent', $result['sent'], 'bluerabbit' ),
			$result['sent']
		) . ( $result['queued'] ? sprintf( ' · %d queued', $result['queued'] ) : '' ),
	] );
}

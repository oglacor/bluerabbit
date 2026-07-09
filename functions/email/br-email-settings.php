<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
		<?php echo $notice; ?>

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

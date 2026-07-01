<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
		$parts = [];
		if ( $queued && ! $sent && ! $failed ) {
			$parts[] = sprintf( _n( '%d email queued for background delivery', '%d emails queued for background delivery', $queued, 'bluerabbit' ), $queued );
			$parts[] = __( 'check the Send Log for delivery status', 'bluerabbit' );
		} else {
			if ( $sent )   $parts[] = sprintf( _n( '%d email sent', '%d emails sent', $sent, 'bluerabbit' ), $sent );
			if ( $failed ) $parts[] = sprintf( _n( '%d failed', '%d failed', $failed, 'bluerabbit' ), $failed );
			if ( $queued ) $parts[] = sprintf( _n( '%d queued via background job', '%d queued via background jobs', $queued, 'bluerabbit' ), $queued );
		}
		$notice = '<div class="notice notice-success is-dismissible"><p>' . implode( ' &bull; ', $parts ) . '</p></div>';
	}
	if ( isset( $_GET['br_error'] ) ) {
		$msg = sanitize_text_field( urldecode( $_GET['br_error'] ) );
		$notice = '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Compose & Send', 'bluerabbit' ); ?></h1>
		<?php echo $notice; ?>

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

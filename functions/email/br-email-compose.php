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

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'BR Email — Compose & Send', 'bluerabbit' ); ?></h1>
		<div id="br_compose_notice"></div>

		<form id="br_compose_form" onsubmit="return false;">

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
				<button type="button" id="br_send_btn" class="button button-primary">
					<?php esc_html_e( '&#9993; Send Email', 'bluerabbit' ); ?>
				</button>
				<span id="br_send_progress" style="margin-left:12px;display:none;font-weight:600;"></span>
			</p>

		</form>
	</div>

	<script>
	jQuery(function($){
		function getBody(){
			return ( typeof tinyMCE !== 'undefined' && tinyMCE.get('br_email_body') )
				? tinyMCE.get('br_email_body').getContent()
				: $('#br_email_body').val();
		}

		function pollBatch( campaignId, total ){
			$.post( ajaxurl, { action: 'br_email_send_batch', nonce: brEmail.nonce, campaign_id: campaignId }, function(r){
				if ( ! r.success ) {
					$('#br_send_progress').text( 'Error: ' + ( r.data && r.data.message || 'send failed' ) );
					return;
				}
				var remaining = r.data.remaining;
				var done = total - remaining;
				$('#br_send_progress').text( done + ' / ' + total + ' processed…' );
				if ( remaining > 0 ) {
					setTimeout( function(){ pollBatch( campaignId, total ); }, 50 );
				} else {
					$('#br_send_progress').text( 'Done — ' + total + ' processed. See the Send Log for sent/failed counts.' );
					$('#br_send_btn').prop('disabled', false);
				}
			}).fail(function(){
				// A single dropped request never loses data (every send is logged
				// immediately) - just try the same batch again.
				setTimeout( function(){ pollBatch( campaignId, total ); }, 2000 );
			});
		}

		$('#br_send_btn').on('click', function(){
			var adventureId = $('#br_adventure_id').val();
			var subject     = $('#br_subject').val();
			var body        = getBody();
			if ( ! adventureId || ! subject || ! body ) {
				$('#br_compose_notice').html('<div class="notice notice-error"><p><?php echo esc_js( __( 'Adventure, subject and body are all required.', 'bluerabbit' ) ); ?></p></div>');
				return;
			}
			if ( ! confirm( '<?php echo esc_js( __( 'Send this email to all enrolled users in the selected adventure?', 'bluerabbit' ) ); ?>' ) ) return;

			$('#br_send_btn').prop('disabled', true);
			$('#br_compose_notice').html('');
			$('#br_send_progress').show().text('Starting…');

			$.post( ajaxurl, {
				action: 'br_email_start_campaign', nonce: brEmail.nonce,
				adventure_id: adventureId, subject: subject, body: body
			}, function(r){
				if ( ! r.success ) {
					$('#br_send_progress').hide();
					$('#br_send_btn').prop('disabled', false);
					$('#br_compose_notice').html('<div class="notice notice-error"><p>' + ( r.data && r.data.message || 'Failed to start campaign' ) + '</p></div>');
					return;
				}
				pollBatch( r.data.campaign_id, r.data.total );
			});
		});
	});
	</script>
	<?php
}

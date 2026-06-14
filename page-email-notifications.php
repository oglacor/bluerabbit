<?php
/**
 * Template Name: Email Notifications
 *
 * Frontend compose-and-send page for adventure owners and GMs.
 * Access:  /email-notifications/?adventure_id=X
 * Who:     adventure owner, GMs, site admins.
 */

include ( get_stylesheet_directory() . '/header.php' );

// header.php sets $current_user, $adventure, $adventure_id, $isGM, $isOwner
$can_send = isset( $adventure ) && $adventure
	&& ( $isGM || $isOwner || current_user_can( 'manage_options' ) );

if ( ! isset( $adventure_id ) || ! $adventure_id ) :
?>
<div class="w-full padding-10 grey-bg-800">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40 blue-bg-400"><span class="icon icon-mail"></span></span>
		<span class="icon-content">
			<span class="line font _24 blue-400"><?php _e( 'Email Notifications', 'bluerabbit' ); ?></span>
			<span class="line font _14 grey-400"><?php _e( 'No adventure selected. Add ?adventure_id=X to the URL.', 'bluerabbit' ); ?></span>
		</span>
	</span>
</div>

<?php elseif ( ! $can_send ) : ?>

<div class="w-full padding-10 red-bg-800">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-cancel"></span></span>
		<span class="icon-content">
			<span class="line font _24 red-200"><?php _e( 'Access Denied', 'bluerabbit' ); ?></span>
			<span class="line font _14 red-100"><?php _e( 'You must be the adventure owner or a Game Master to send notifications.', 'bluerabbit' ); ?></span>
		</span>
	</span>
</div>

<?php else :

global $wpdb;

// Adventure owner info (for "From" display and reply-to)
$owner      = get_userdata( (int) $adventure->adventure_owner );
$owner_name = $owner ? $owner->display_name : get_bloginfo( 'name' );
$from_label = $owner_name . ' · ' . $adventure->adventure_title;

// Recipient count (opted-in enrolled players)
$recipient_count = $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*)
	   FROM {$wpdb->prefix}br_player_adventure pa
	   JOIN {$wpdb->users} u ON u.ID = pa.player_id
	  WHERE pa.adventure_id            = %d
	    AND pa.player_adventure_status = 'in'
	    AND NOT EXISTS (
	        SELECT 1 FROM {$wpdb->usermeta} m
	         WHERE m.user_id   = pa.player_id
	           AND m.meta_key  = 'br_email_optout'
	           AND m.meta_value = '1'
	    )",
	$adventure_id
) );
?>

<!-- ── Page header ─────────────────────────────────────────────────── -->
<div class="w-full padding-10 grey-bg-800">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40 blue-bg-400"><span class="icon icon-mail"></span></span>
		<span class="icon-content">
			<span class="line font _24 blue-400"><?php _e( 'Email Notification', 'bluerabbit' ); ?></span>
			<span class="line font _14 grey-300">
				<?php echo esc_html( $adventure->adventure_title ); ?>
				&nbsp;·&nbsp;
				<strong><?php echo (int) $recipient_count; ?></strong>
				<?php _e( 'recipients', 'bluerabbit' ); ?>
			</span>
		</span>
		<span class="icon-content pull-right">
			<span class="line font _12 grey-400"><?php _e( 'From', 'bluerabbit' ); ?></span>
			<span class="line font _14 white-color"><?php echo esc_html( $from_label ); ?></span>
		</span>
	</span>
</div>

<!-- ── Compose form ────────────────────────────────────────────────── -->
<div class="w-full padding-10">
	<div id="br-notif-status" style="display:none;" class="w-full padding-10 margin-bottom-10"></div>

	<form id="br-email-notif-form" onsubmit="return false;">
		<input type="hidden" name="adventure_id" value="<?php echo (int) $adventure_id; ?>">

		<!-- Subject -->
		<div class="w-full margin-bottom-10">
			<label class="font _12 grey-400 uppercase"><?php _e( 'Subject', 'bluerabbit' ); ?></label>
			<input type="text" id="br-notif-subject" name="subject"
				class="form-input w-full"
				placeholder="<?php esc_attr_e( 'Email subject line…', 'bluerabbit' ); ?>"
				style="width:100%;padding:10px;font-size:15px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;margin-top:4px;">
		</div>

		<!-- Body (TinyMCE) -->
		<div class="w-full margin-bottom-10">
			<label class="font _12 grey-400 uppercase"><?php _e( 'Message', 'bluerabbit' ); ?></label>
			<p class="font _12 grey-400" style="margin:4px 0 8px;">
				<?php _e( 'Merge tags:', 'bluerabbit' ); ?>
				<code>{{name}}</code> &nbsp; <code>{{adventure_name}}</code> &nbsp; <code>{{site_name}}</code>
			</p>
			<?php
			wp_editor( '', 'br_notif_body', [
				'textarea_name' => 'body',
				'textarea_rows' => 16,
				'editor_class'  => 'br-notif-editor',
				'tinymce'       => [
					'toolbar1'      => 'formatselect,|,bold,italic,underline,strikethrough,|,forecolor,backcolor,|,link,unlink',
					'toolbar2'      => 'bullist,numlist,blockquote,|,alignleft,aligncenter,alignright,|,hr,removeformat,|,undo,redo',
					'block_formats' => 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Blockquote=blockquote;Preformatted=pre',
				],
			] );
			?>
		</div>

		<!-- Actions -->
		<div class="w-full margin-top-10">
			<button type="button" id="br-notif-preview-btn"
				class="form-ui grey-bg-400 white-color">
				<span class="icon icon-view"></span>
				<?php _e( 'Preview', 'bluerabbit' ); ?>
			</button>
			&nbsp;
			<button type="submit" id="br-notif-send-btn"
				class="form-ui blue-bg-400 white-color">
				<span class="icon icon-mail"></span>
				<?php _e( 'Send to all enrolled players', 'bluerabbit' ); ?>
			</button>
			<span id="br-notif-spinner" style="display:none;margin-left:12px;font-size:13px;color:#888;">
				<?php _e( 'Sending…', 'bluerabbit' ); ?>
			</span>
		</div>
	</form>
</div>

<script>
(function($){
	function getEditorBody() {
		if ( typeof tinyMCE !== 'undefined' && tinyMCE.get('br_notif_body') ) {
			return tinyMCE.get('br_notif_body').getContent();
		}
		return $('#br_notif_body').val();
	}

	function showStatus( msg, colour ) {
		colour = colour || 'green';
		$('#br-notif-status')
			.removeClass()
			.addClass('w-full padding-10 ' + colour + '-bg-50 font _14')
			.html(msg)
			.show();
	}

	/* Preview */
	$('#br-notif-preview-btn').on('click', function(){
		var subject = $('#br-notif-subject').val();
		var body    = getEditorBody();
		if ( !subject || !body ) {
			showStatus('<?php esc_attr_e( 'Please fill in subject and message before previewing.', 'bluerabbit' ); ?>', 'orange');
			return;
		}
		$.post( brEmailFront.ajaxurl, {
			action:  'br_email_preview',
			subject: subject,
			body:    body,
			nonce:   brEmailFront.nonce
		}, function(r){
			if ( !r.success ) return;
			var win = window.open('', '_blank', 'width=700,height=650,scrollbars=yes,resizable=yes');
			win.document.write(r.data.html);
			win.document.close();
		});
	});

	/* Send */
	$('#br-email-notif-form').on('submit', function(){
		var subject = $('#br-notif-subject').val().trim();
		var body    = getEditorBody();

		if ( !subject || !body ) {
			showStatus('<?php esc_attr_e( 'Please fill in both the subject and the message.', 'bluerabbit' ); ?>', 'orange');
			return;
		}

		if ( !confirm('<?php esc_attr_e( 'Send this email to all enrolled players in this adventure?', 'bluerabbit' ); ?>') ) {
			return;
		}

		$('#br-notif-send-btn').prop('disabled', true);
		$('#br-notif-spinner').show();

		$.post( brEmailFront.ajaxurl, {
			action:       'br_send_notification_email',
			nonce:        brEmailFront.nonce,
			adventure_id: <?php echo (int) $adventure_id; ?>,
			subject:      subject,
			body:         body
		}, function(r){
			$('#br-notif-send-btn').prop('disabled', false);
			$('#br-notif-spinner').hide();

			if ( r.success ) {
				showStatus(
					'<span class="icon icon-check"></span> ' + r.data.message,
					'green'
				);
				// Clear the editor after successful send
				if ( typeof tinyMCE !== 'undefined' && tinyMCE.get('br_notif_body') ) {
					tinyMCE.get('br_notif_body').setContent('');
				} else {
					$('#br_notif_body').val('');
				}
				$('#br-notif-subject').val('');
			} else {
				showStatus(
					'<span class="icon icon-cancel"></span> ' + ( r.data.message || '<?php esc_attr_e('Send failed. Check the email log.','bluerabbit'); ?>' ),
					'red'
				);
			}
		}).fail(function(){
			$('#br-notif-send-btn').prop('disabled', false);
			$('#br-notif-spinner').hide();
			showStatus('<?php esc_attr_e( 'Request failed. Please try again.', 'bluerabbit' ); ?>', 'red');
		});
	});
}(jQuery));
</script>

<?php endif; ?>

<?php include ( get_stylesheet_directory() . '/footer.php' ); ?>

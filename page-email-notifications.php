<?php
/**
 * Template Name: Email Notifications
 */

include ( get_stylesheet_directory() . '/header.php' );

$can_send = isset( $adventure ) && $adventure
	&& ( $isGM || $isOwner || current_user_can( 'manage_options' ) );

if ( ! isset( $adventure_id ) || ! $adventure_id ) :
?>
<div class="br-page">
	<div class="br-panel" style="max-width:800px;margin:40px auto;text-align:center;padding:40px">
		<span class="icon icon-mail" style="font-size:48px;color:rgba(28,194,235,0.4);display:block;margin-bottom:16px"></span>
		<h2 style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:28px;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.9);margin:0 0 8px"><?php _e( 'Email Notifications', 'bluerabbit' ); ?></h2>
		<p style="color:rgba(255,255,255,0.4);font-size:14px"><?php _e( 'No adventure selected. Add ?adventure_id=X to the URL.', 'bluerabbit' ); ?></p>
	</div>
</div>

<?php elseif ( ! $can_send ) : ?>

<div class="br-page">
	<div class="br-panel" style="max-width:800px;margin:40px auto;text-align:center;padding:40px;border-color:rgba(244,67,54,0.3)">
		<span class="icon icon-cancel" style="font-size:48px;color:rgba(244,67,54,0.5);display:block;margin-bottom:16px"></span>
		<h2 style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:28px;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;color:#f44336;margin:0 0 8px"><?php _e( 'Access Denied', 'bluerabbit' ); ?></h2>
		<p style="color:rgba(255,255,255,0.4);font-size:14px"><?php _e( 'You must be the adventure owner or a Game Master to send notifications.', 'bluerabbit' ); ?></p>
	</div>
</div>

<?php else :

global $wpdb;

$sender = wp_get_current_user();
$owner  = get_userdata( (int) $adventure->adventure_owner );

// Use same query pattern as player-select-achievement.php (which works)
$enrolled_players = $wpdb->get_results(
	"SELECT a.player_id, a.player_level,
	        b.player_display_name, b.player_picture,
	        u.user_email, u.display_name
	   FROM {$wpdb->prefix}br_player_adventure a
	   LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
	   LEFT JOIN {$wpdb->users} u ON a.player_id = u.ID
	  WHERE a.adventure_id = {$adv_parent_id}
	    AND a.player_adventure_status = 'in'
	  ORDER BY u.user_email LIMIT 1000"
);
$recipient_count  = count( $enrolled_players );
$sender_is_owner  = ( $owner && (int) $owner->ID === (int) $sender->ID );
?>

<div class="br-page" style="max-width:900px">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(28,194,235,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(28,194,235,0.4)">
			<span class="icon icon-mail" style="font-size:28px;color:#1cc2eb"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?php _e( 'Email Notification', 'bluerabbit' ); ?></h1>
			<span class="br-page-subtitle"><?php echo esc_html( $adventure->adventure_title ); ?></span>
		</div>
		<div style="margin-left:auto;text-align:right">
			<span style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.35);display:block"><?php _e( 'Eligible', 'bluerabbit' ); ?></span>
			<span style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:28px;font-weight:900;color:#1cc2eb"><?php echo (int) $recipient_count; ?></span>
		</div>
	</div>

	<!-- Status Banner -->
	<div id="br-notif-status" style="display:none;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:14px"></div>

	<!-- Sender + Compose -->
	<div class="br-panel" style="padding:20px">
		<h3 class="br-panel-title"><span class="icon icon-document"></span> <?php _e( 'Compose Message', 'bluerabbit' ); ?></h3>

		<form id="br-email-notif-form" onsubmit="return false;">
			<input type="hidden" name="adventure_id" value="<?php echo (int) $adv_parent_id; ?>">

			<div class="br-form-group">
				<label class="br-form-label"><?php _e( 'Send on behalf of', 'bluerabbit' ); ?></label>
				<span class="br-form-hint"><?php _e( 'The reply-to address will be set to this person.', 'bluerabbit' ); ?></span>
				<select class="br-input" id="br-notif-sender" style="max-width:400px">
					<?php if ( $owner ) : ?>
					<option value="<?php echo (int) $owner->ID; ?>" data-name="<?php echo esc_attr( $owner->display_name ); ?>" data-email="<?php echo esc_attr( $owner->user_email ); ?>" selected>
						<?php echo esc_html( $owner->display_name ); ?> &middot; <?php _e( 'Adventure Owner', 'bluerabbit' ); ?> (<?php echo esc_html( $owner->user_email ); ?>)
					</option>
					<?php endif; ?>
					<?php if ( ! $sender_is_owner ) : ?>
					<option value="<?php echo (int) $sender->ID; ?>" data-name="<?php echo esc_attr( $sender->display_name ); ?>" data-email="<?php echo esc_attr( $sender->user_email ); ?>">
						<?php echo esc_html( $sender->display_name ); ?> &middot; <?php _e( 'Me', 'bluerabbit' ); ?> (<?php echo esc_html( $sender->user_email ); ?>)
					</option>
					<?php endif; ?>
				</select>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?php _e( 'Subject', 'bluerabbit' ); ?></label>
				<input type="text" id="br-notif-subject" name="subject" class="br-input br-input-lg"
					   placeholder="<?php esc_attr_e( 'Email subject line…', 'bluerabbit' ); ?>">
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?php _e( 'Message', 'bluerabbit' ); ?></label>
				<span class="br-form-hint" style="display:block;margin-bottom:8px">
					<?php _e( 'Merge tags:', 'bluerabbit' ); ?>
					<code style="background:rgba(28,194,235,0.1);color:#1cc2eb;padding:2px 6px;border-radius:4px;font-size:12px">{{name}}</code>
					<code style="background:rgba(28,194,235,0.1);color:#1cc2eb;padding:2px 6px;border-radius:4px;font-size:12px">{{adventure_name}}</code>
					<code style="background:rgba(28,194,235,0.1);color:#1cc2eb;padding:2px 6px;border-radius:4px;font-size:12px">{{site_name}}</code>
				</span>
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
		</form>
	</div>

	<!-- Recipients — always visible -->
	<div class="br-panel" style="padding:20px">
		<h3 class="br-panel-title"><span class="icon icon-players"></span> <?php _e( 'Recipients', 'bluerabbit' ); ?></h3>

		<div class="br-form-group" style="margin-bottom:12px">
			<label class="br-form-label"><?php _e( 'Send to', 'bluerabbit' ); ?></label>
			<select class="br-input" id="br-notif-recipient-mode" style="max-width:300px">
				<option value="all"><?php printf( __( 'All enrolled players (%d)', 'bluerabbit' ), $recipient_count ); ?></option>
				<option value="select"><?php _e( 'Selected players only', 'bluerabbit' ); ?></option>
			</select>
		</div>

		<!-- Player selection panel — hidden until "Selected players only" is chosen -->
		<div id="br-recipient-panel" style="display:none">

			<!-- CSV Upload -->
			<div style="padding:10px 0;border-bottom:1px solid rgba(28,194,235,0.08)">
				<label class="br-form-label" style="margin-bottom:6px"><?php _e( 'Bulk select from CSV', 'bluerabbit' ); ?></label>
				<span class="br-form-hint" style="display:block;margin-bottom:8px"><?php _e( 'Upload a CSV with a single column of email addresses to auto-select matching players.', 'bluerabbit' ); ?></span>
				<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
					<input type="file" id="br-notif-csv" class="br-input" style="padding:7px 14px;flex:1;min-width:200px" accept=".csv">
					<button type="button" class="br-btn br-btn-green" id="br-notif-csv-btn">
						<span class="icon icon-upload"></span> <?php _e( 'Match & Select', 'bluerabbit' ); ?>
					</button>
				</div>
			</div>

			<!-- Search bar + actions -->
			<div style="display:flex;align-items:center;gap:10px;padding:12px 0;flex-wrap:wrap">
				<input type="text" class="br-input" id="br-notif-player-search" placeholder="<?php esc_attr_e( 'Search players...', 'bluerabbit' ); ?>" style="flex:1;min-width:180px">
				<span style="font-size:13px;color:rgba(255,255,255,0.4)">
					<span id="br-notif-visible-count"><?php echo $recipient_count; ?></span> <?php _e( 'players', 'bluerabbit' ); ?>
					&middot; <span id="br-notif-selected-count" style="color:#24da98;font-weight:700">0</span> <?php _e( 'selected', 'bluerabbit' ); ?>
				</span>
				<div class="br-actions" style="gap:6px">
					<button type="button" class="br-btn" style="padding:6px 12px;font-size:12px" onclick="brNotifSelectAll()">
						<span class="icon icon-check"></span> <?php _e( 'Select all', 'bluerabbit' ); ?>
					</button>
					<button type="button" class="br-btn br-btn-red" style="padding:6px 12px;font-size:12px" onclick="brNotifDeselectAll()">
						<span class="icon icon-cancel"></span> <?php _e( 'Clear', 'bluerabbit' ); ?>
					</button>
				</div>
			</div>

			<!-- Player Cards -->
			<?php if ( $recipient_count === 0 ) : ?>
			<div class="br-empty" style="padding:20px">
				<span class="icon icon-players"></span>
				<h3><?php _e( 'No enrolled players found', 'bluerabbit' ); ?></h3>
			</div>
			<?php else : ?>
			<div class="br-player-grid" id="br-notif-player-list">
				<?php foreach ( $enrolled_players as $p ) :
					$pName = $p->player_display_name ?: $p->display_name;
				?>
				<div class="br-player-card"
					 data-pid="<?php echo (int) $p->player_id; ?>"
					 data-email="<?php echo esc_attr( strtolower( $p->user_email ) ); ?>"
					 data-search="<?php echo esc_attr( strtolower( $pName . ' ' . $p->user_email ) ); ?>">
					<div class="br-player-avatar" style="background-image:url(<?php echo esc_url( $p->player_picture ); ?>)"></div>
					<div class="br-player-info">
						<span class="br-player-name"><?php echo esc_html( $pName ); ?></span>
						<span class="br-player-meta">
							<span style="color:#9f40e2;font-weight:700">Lv.<?php echo (int) $p->player_level; ?></span> &middot; <?php echo esc_html( $p->user_email ); ?>
						</span>
					</div>
					<span class="br-player-check icon icon-check"></span>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="br-pagination" id="br-notif-pagination"></div>
			<?php endif; ?>

		</div>
	</div>

	<!-- Actions -->
	<div class="br-panel" style="padding:16px 20px">
		<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
			<button type="button" id="br-notif-preview-btn" class="br-btn" style="padding:10px 20px">
				<span class="icon icon-view"></span> <?php _e( 'Preview', 'bluerabbit' ); ?>
			</button>
			<button type="button" id="br-notif-send-btn" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px">
				<span class="icon icon-mail"></span> <?php _e( 'Send Email', 'bluerabbit' ); ?>
			</button>
			<span id="br-notif-spinner" style="display:none;font-size:13px;color:rgba(255,255,255,0.4)">
				<span class="icon icon-rotate" style="animation:spin 1s linear infinite"></span>
				<?php _e( 'Sending…', 'bluerabbit' ); ?>
			</span>
			<span id="br-notif-send-summary" style="margin-left:auto;font-size:13px;color:rgba(255,255,255,0.4)"></span>
		</div>
	</div>

</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
jQuery(function($){

	var selectMode = false;

	function getEditorBody() {
		if ( typeof tinyMCE !== 'undefined' && tinyMCE.get('br_notif_body') )
			return tinyMCE.get('br_notif_body').getContent();
		return $('#br_notif_body').val();
	}

	function showStatus( msg, type ) {
		type = type || 'green';
		var colors = { green: ['rgba(36,218,152,0.1)','rgba(36,218,152,0.3)','#24da98'],
		               red:   ['rgba(244,67,54,0.1)','rgba(244,67,54,0.3)','#f44336'],
		               orange:['rgba(255,152,0,0.1)','rgba(255,152,0,0.3)','#ff9800'] };
		var c = colors[type] || colors.green;
		$('#br-notif-status').css({ background:c[0], border:'1px solid '+c[1], color:c[2] }).html(msg).show();
	}

	// ── Mode toggle ──
	$('#br-notif-recipient-mode').on('change', function(){
		selectMode = $(this).val() === 'select';
		if (selectMode) {
			$('#br-recipient-panel').show();
			$('#br-notif-player-list').addClass('br-selectable');
			PG.page = 1;
			PG.render();
		} else {
			$('#br-recipient-panel').hide();
			$('#br-notif-player-list').removeClass('br-selectable');
			$('#br-notif-player-list .br-player-card').removeClass('active');
			$('#br-notif-player-search').val('');
			updateSelectedCount();
		}
		updateSummary();
	});

	// ── Click to toggle (only in select mode) ──
	$(document).on('click', '#br-notif-player-list.br-selectable .br-player-card', function(){
		$(this).toggleClass('active');
		updateSelectedCount();
	});

	window.brNotifSelectAll = function(){
		$('#br-notif-player-list .br-player-card').not('[style*="display: none"]').addClass('active');
		updateSelectedCount();
	};
	window.brNotifDeselectAll = function(){
		$('#br-notif-player-list .br-player-card').removeClass('active');
		updateSelectedCount();
	};

	function updateSelectedCount(){
		$('#br-notif-selected-count').text( $('#br-notif-player-list .br-player-card.active').length );
		updateSummary();
	}

	function updateSummary(){
		if (!selectMode) {
			$('#br-notif-send-summary').text('<?php printf( esc_attr__( 'Sending to all %d players', 'bluerabbit' ), $recipient_count ); ?>');
		} else {
			$('#br-notif-send-summary').text( $('#br-notif-player-list .br-player-card.active').length + ' <?php esc_attr_e( 'players selected', 'bluerabbit' ); ?>' );
		}
	}
	updateSummary();

	function getRecipients(){
		if (!selectMode) return 'all';
		var ids = [];
		$('#br-notif-player-list .br-player-card.active').each(function(){ ids.push( $(this).attr('data-pid') ); });
		return ids.join(',');
	}

	// ── Pagination — vanilla show/hide, no hidden-class tricks ──
	var PG = {
		page: 1, perPage: 20,
		render: function(){
			var search = ($('#br-notif-player-search').val() || '').toLowerCase();
			var $all   = $('#br-notif-player-list .br-player-card');
			var visible = [];
			$all.each(function(){
				var match = !search || ($(this).attr('data-search') || '').indexOf(search) >= 0;
				if (match) visible.push(this);
				this.style.display = 'none';
			});
			var total = visible.length;
			var pages = Math.ceil(total / this.perPage);
			if (this.page > pages) this.page = Math.max(1, pages);
			var start = (this.page - 1) * this.perPage;
			var end   = Math.min(start + this.perPage, total);
			for (var i = start; i < end; i++) visible[i].style.display = '';
			$('#br-notif-visible-count').text(total);
			this.nav(pages);
		},
		nav: function(pages){
			if (pages <= 1){ $('#br-notif-pagination').html(''); return; }
			var h='', p=this.page;
			if(p>1) h+='<button class="br-page-btn" onclick="PG.goTo('+(p-1)+')">&laquo;</button>';
			var s=Math.max(1,p-3), e=Math.min(pages,p+3);
			if(s>1){ h+='<button class="br-page-btn" onclick="PG.goTo(1)">1</button>'; if(s>2) h+='<span style="color:rgba(255,255,255,0.3)">&hellip;</span>'; }
			for(var i=s;i<=e;i++) h+='<button class="br-page-btn'+(i===p?' active':'')+'" onclick="PG.goTo('+i+')">'+i+'</button>';
			if(e<pages){ if(e<pages-1) h+='<span style="color:rgba(255,255,255,0.3)">&hellip;</span>'; h+='<button class="br-page-btn" onclick="PG.goTo('+pages+')">'+pages+'</button>'; }
			if(p<pages) h+='<button class="br-page-btn" onclick="PG.goTo('+(p+1)+')">&raquo;</button>';
			$('#br-notif-pagination').html(h);
		},
		goTo: function(p){ this.page=p; this.render(); document.getElementById('br-notif-player-list').scrollIntoView({behavior:'smooth',block:'start'}); }
	};
	window.PG = PG;

	$('#br-notif-player-search').on('keyup', function(){ PG.page=1; PG.render(); });

	// ── CSV match ──
	$('#br-notif-csv-btn').on('click', function(){
		var fi = document.getElementById('br-notif-csv');
		if (!fi||!fi.files[0]){ showStatus('<?php esc_attr_e('Select a CSV file first.','bluerabbit');?>','orange'); return; }
		var reader = new FileReader();
		reader.onload = function(e){
			var emails = {};
			e.target.result.split(/[\r\n]+/).forEach(function(line){
				var c = line.split(',')[0].replace(/['"]/g,'').trim().toLowerCase();
				if (c && c.indexOf('@')>-1 && c!=='email') emails[c]=true;
			});
			var matched=0;
			$('#br-notif-player-list .br-player-card').each(function(){
				if(emails[$(this).attr('data-email')]){ $(this).addClass('active'); matched++; }
			});
			updateSelectedCount();
			showStatus('<span class="icon icon-check"></span> '+matched+' <?php esc_attr_e('players matched from CSV','bluerabbit');?>','green');
			fi.value='';
		};
		reader.readAsText(fi.files[0]);
	});

	// ── Preview ──
	$('#br-notif-preview-btn').on('click', function(){
		var subject = $('#br-notif-subject').val(), body = getEditorBody();
		if (!subject||!body){ showStatus('<span class="icon icon-warning"></span> <?php esc_attr_e('Fill in subject and message first.','bluerabbit');?>','orange'); return; }
		$.post(brEmailFront.ajaxurl,{
			action:'br_email_preview', subject:subject, body:body,
			adventure_id:<?php echo (int)$adv_parent_id;?>, nonce:brEmailFront.nonce
		},function(r){
			if(!r.success)return;
			var w=window.open('','_blank','width=700,height=650,scrollbars=yes,resizable=yes');
			w.document.write(r.data.html); w.document.close();
		});
	});

	// ── Send ──
	$('#br-notif-send-btn').on('click', function(){
		var subject=$.trim($('#br-notif-subject').val()), body=getEditorBody(), recipients=getRecipients();
		var $so=$('#br-notif-sender option:selected');
		if(!subject||!body){ showStatus('<span class="icon icon-warning"></span> <?php esc_attr_e('Fill in subject and message.','bluerabbit');?>','orange'); return; }
		if(recipients!=='all'&&!recipients){ showStatus('<span class="icon icon-warning"></span> <?php esc_attr_e('No players selected.','bluerabbit');?>','orange'); return; }
		var msg = recipients==='all'
			? '<?php printf(esc_attr__('Send this email to all %d enrolled players?','bluerabbit'),$recipient_count);?>'
			: '<?php esc_attr_e('Send this email to the selected players?','bluerabbit');?>';
		if(!confirm(msg)) return;
		$('#br-notif-send-btn').prop('disabled',true); $('#br-notif-spinner').show();
		$.post(brEmailFront.ajaxurl,{
			action:'br_send_notification_email', nonce:brEmailFront.nonce,
			adventure_id:<?php echo (int)$adv_parent_id;?>, subject:subject, body:body,
			recipients:recipients, sender_name:$so.data('name'), sender_email:$so.data('email')
		},function(r){
			$('#br-notif-send-btn').prop('disabled',false); $('#br-notif-spinner').hide();
			if(r.success){
				showStatus('<span class="icon icon-check"></span> '+r.data.message,'green');
				if(typeof tinyMCE!=='undefined'&&tinyMCE.get('br_notif_body')) tinyMCE.get('br_notif_body').setContent('');
				else $('#br_notif_body').val('');
				$('#br-notif-subject').val('');
			} else {
				showStatus('<span class="icon icon-cancel"></span> '+(r.data.message||'<?php esc_attr_e("Send failed.","bluerabbit");?>'),'red');
			}
		}).fail(function(){
			$('#br-notif-send-btn').prop('disabled',false); $('#br-notif-spinner').hide();
			showStatus('<span class="icon icon-cancel"></span> <?php esc_attr_e("Request failed.","bluerabbit");?>','red');
		});
	});

});
</script>

<?php endif; ?>
<?php include ( get_stylesheet_directory() . '/footer.php' ); ?>

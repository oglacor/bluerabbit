<?php
$status_colors = array(
	'pending' => 'amber',
	'read' => 'blue',
	'resolved' => 'green',
	'dismissed' => 'red'
);
$badge_class = isset($status_colors[$req->request_status]) ? 'br-badge-' . $status_colors[$req->request_status] : 'br-badge-blue';
$profile_pic = $req->player_picture ? $req->player_picture : get_bloginfo('template_directory').'/images/no-profile.png';
$time_ago = human_time_diff(strtotime($req->request_date), current_time('timestamp'));
?>
<div class="br-panel request-card br-req-card" id="request-<?= $req->request_id; ?>">
	<div class="br-req-layout">
		<div class="br-player-avatar br-req-avatar" style="background-image:url(<?= $profile_pic; ?>)"></div>
		<div class="br-req-body">
			<div class="br-req-header">
				<div>
					<span class="br-req-subject"><?= esc_html($req->request_subject); ?></span>
					<span class="<?= $badge_class; ?>"><?= $req->request_status; ?></span>
				</div>
				<span class="br-req-time"><?= $time_ago . ' ' . __("ago","bluerabbit"); ?></span>
			</div>
			<div class="br-req-sender">
				<strong class="br-req-sender-name"><?= esc_html($req->player_display_name); ?></strong>
				<?php if($req->player_email){ ?>
					<span class="br-req-sender-email">(<?= esc_html($req->player_email); ?>)</span>
				<?php } ?>
			</div>
			<div class="br-form-component br-req-content"><?= esc_html($req->request_content); ?></div>

			<?php if($req->request_admin_note){ ?>
				<div class="br-req-admin-note">
					<span class="icon icon-document"></span> <strong><?= __("Admin note:","bluerabbit"); ?></strong> <?= esc_html($req->request_admin_note); ?>
				</div>
			<?php } ?>

			<div class="br-req-actions-wrap">
				<textarea class="br-input" id="admin-note-<?= $req->request_id; ?>" rows="2" placeholder="<?= __("Admin note (optional)","bluerabbit"); ?>"><?= esc_html($req->request_admin_note); ?></textarea>
				<div class="br-actions br-req-actions-bar">
					<?php if($req->request_status == 'pending'){ ?>
						<button class="br-btn cyan" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'read');">
							<span class="icon icon-check"></span> <?= __("Mark Read","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'resolved'){ ?>
						<button class="br-btn green" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'resolved');">
							<span class="icon icon-check"></span> <?= __("Resolve","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'dismissed'){ ?>
						<button class="br-btn ghost" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'dismissed');">
							<span class="icon icon-cancel"></span> <?= __("Dismiss","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'pending'){ ?>
						<button class="br-btn amber" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'pending');">
							<span class="icon icon-restore"></span> <?= __("Reopen","bluerabbit"); ?>
						</button>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

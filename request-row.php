<?php
$status_colors = array(
	'pending' => 'orange',
	'read' => 'cyan',
	'resolved' => 'green',
	'dismissed' => 'grey'
);
$color = isset($status_colors[$req->request_status]) ? $status_colors[$req->request_status] : 'grey';
$profile_pic = $req->player_picture ? $req->player_picture : get_bloginfo('template_directory').'/images/no-profile.png';
$time_ago = human_time_diff(strtotime($req->request_date), current_time('timestamp'));
?>
<div class="request-card border padding-10 margin-bottom-10 white-bg rounded" id="request-<?= $req->request_id; ?>">
	<div class="flex-row">
		<div class="flex-shrink">
			<div class="sq-40 rounded-max overflow-hidden margin-right-10" style="background-image: url(<?= $profile_pic; ?>); background-size: cover;"></div>
		</div>
		<div class="flex-grow">
			<div class="flex-row">
				<div class="flex-grow">
					<span class="font _16 w700 grey-800"><?= esc_html($req->request_subject); ?></span>
					<span class="font _12 <?= $color; ?>-bg-400 white-color padding-3 rounded uppercase w700" style="margin-left: 8px;"><?= $req->request_status; ?></span>
				</div>
				<div class="flex-shrink text-right">
					<span class="font _12 grey-400"><?= $time_ago . ' ' . __("ago","bluerabbit"); ?></span>
				</div>
			</div>
			<div class="font _13 grey-600 margin-top-5">
				<strong><?= esc_html($req->player_display_name); ?></strong>
				<?php if($req->player_email){ ?>
					<span class="opacity-60">(<?= esc_html($req->player_email); ?>)</span>
				<?php } ?>
			</div>
			<div class="font _14 grey-800 padding-10 grey-bg-100 rounded margin-top-10" style="white-space: pre-wrap;"><?= esc_html($req->request_content); ?></div>

			<?php if($req->request_admin_note){ ?>
				<div class="font _13 blue-grey-600 padding-5 margin-top-5">
					<span class="icon icon-document"></span> <strong><?= __("Admin note:","bluerabbit"); ?></strong> <?= esc_html($req->request_admin_note); ?>
				</div>
			<?php } ?>

			<div class="margin-top-10">
				<textarea class="form-ui w-full font _13" id="admin-note-<?= $req->request_id; ?>" rows="2" placeholder="<?= __("Admin note (optional)","bluerabbit"); ?>"><?= esc_html($req->request_admin_note); ?></textarea>
				<div class="margin-top-5">
					<?php if($req->request_status == 'pending'){ ?>
						<button class="form-ui cyan-bg-400 white-color font _13" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'read');">
							<span class="icon icon-check"></span> <?= __("Mark Read","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'resolved'){ ?>
						<button class="form-ui green-bg-400 white-color font _13" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'resolved');">
							<span class="icon icon-check"></span> <?= __("Resolve","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'dismissed'){ ?>
						<button class="form-ui grey-bg-400 white-color font _13" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'dismissed');">
							<span class="icon icon-cancel"></span> <?= __("Dismiss","bluerabbit"); ?>
						</button>
					<?php } ?>
					<?php if($req->request_status != 'pending'){ ?>
						<button class="form-ui orange-bg-400 white-color font _13" onClick="updateRequestStatus(<?= $req->request_id; ?>, 'pending');">
							<span class="icon icon-restore"></span> <?= __("Reopen","bluerabbit"); ?>
						</button>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

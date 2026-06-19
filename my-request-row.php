<?php
$status_colors = array(
	'pending' => 'orange',
	'read' => 'cyan',
	'resolved' => 'green',
	'dismissed' => 'grey'
);
$status_labels = array(
	'pending' => __("Pending","bluerabbit"),
	'read' => __("Read","bluerabbit"),
	'resolved' => __("Resolved","bluerabbit"),
	'dismissed' => __("Dismissed","bluerabbit")
);
$color = isset($status_colors[$req->request_status]) ? $status_colors[$req->request_status] : 'grey';
$label = isset($status_labels[$req->request_status]) ? $status_labels[$req->request_status] : $req->request_status;
$time_ago = human_time_diff(strtotime($req->request_date), current_time('timestamp'));
?>
<div class="request-card border padding-10 margin-bottom-10 white-bg rounded">
	<div class="flex-row">
		<div class="flex-grow">
			<div class="flex-row">
				<div class="flex-grow">
					<span class="font _16 w700 grey-800"><?= esc_html($req->request_subject); ?></span>
				</div>
				<div class="flex-shrink text-right">
					<span class="font _12 <?= $color; ?>-bg-400 white-color padding-3 rounded uppercase w700"><?= $label; ?></span>
					<span class="font _12 grey-400 margin-left-5"><?= $time_ago . ' ' . __("ago","bluerabbit"); ?></span>
				</div>
			</div>
			<div class="font _14 grey-700 padding-10 grey-bg-100 rounded margin-top-10" style="white-space: pre-wrap;"><?= esc_html($req->request_content); ?></div>

			<?php if($req->request_admin_note){ ?>
				<div class="font _14 padding-10 blue-bg-50 rounded margin-top-10">
					<span class="icon icon-document blue-400"></span>
					<strong class="blue-grey-800"><?= __("Admin response:","bluerabbit"); ?></strong>
					<div class="margin-top-5 grey-800" style="white-space: pre-wrap;"><?= esc_html($req->request_admin_note); ?></div>
				</div>
			<?php } ?>

			<?php if($req->request_resolved_date && ($req->request_status == 'resolved' || $req->request_status == 'dismissed')){ ?>
				<div class="font _12 grey-400 margin-top-5">
					<?= __("Updated","bluerabbit"); ?>: <?= human_time_diff(strtotime($req->request_resolved_date), current_time('timestamp')) . ' ' . __("ago","bluerabbit"); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

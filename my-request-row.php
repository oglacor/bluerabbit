<?php
$status_map = [
	'pending'   => ['badge' => 'br-badge-amber',  'label' => __("Pending","bluerabbit"),   'icon' => 'icon-time'],
	'read'      => ['badge' => 'br-badge-blue',   'label' => __("Read","bluerabbit"),      'icon' => 'icon-view'],
	'resolved'  => ['badge' => 'br-badge-green',  'label' => __("Resolved","bluerabbit"),  'icon' => 'icon-check'],
	'dismissed' => ['badge' => 'br-badge-red',    'label' => __("Dismissed","bluerabbit"), 'icon' => 'icon-cancel'],
];
$s = isset($status_map[$req->request_status]) ? $status_map[$req->request_status] : $status_map['pending'];
$time_ago = human_time_diff(strtotime($req->request_date), current_time('timestamp'));
?>
<div class="br-panel request-card br-myreq-card" data-status="<?= $req->request_status; ?>">

	<div class="br-myreq-header">
		<div class="br-myreq-body">
			<span class="br-myreq-subject"><?= esc_html($req->request_subject); ?></span>
		</div>
		<div class="br-myreq-status-row">
			<span class="br-badge <?= $s['badge']; ?>"><span class="icon <?= $s['icon']; ?>"></span> <?= $s['label']; ?></span>
			<span class="br-myreq-time"><?= $time_ago . ' ' . __("ago","bluerabbit"); ?></span>
		</div>
	</div>

	<div class="br-form-component br-myreq-content"><?= esc_html($req->request_content); ?></div>

	<?php if($req->request_admin_note){ ?>
		<div class="br-myreq-admin-response">
			<span class="br-myreq-admin-label">
				<span class="icon icon-document"></span> <?= __("Admin response","bluerabbit"); ?>
			</span>
			<div class="br-myreq-admin-text"><?= esc_html($req->request_admin_note); ?></div>
		</div>
	<?php } ?>

	<?php if($req->request_resolved_date && ($req->request_status == 'resolved' || $req->request_status == 'dismissed')){ ?>
		<div class="br-myreq-updated">
			<?= __("Updated","bluerabbit"); ?>: <?= human_time_diff(strtotime($req->request_resolved_date), current_time('timestamp')) . ' ' . __("ago","bluerabbit"); ?>
		</div>
	<?php } ?>

</div>

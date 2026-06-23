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
<div class="br-panel request-card" data-status="<?= $req->request_status; ?>" style="padding:16px;margin-bottom:4px;border-radius:0;">

	<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
		<div style="flex:1;min-width:0;">
			<span style="font-size:16px;font-weight:700;color:#fff;"><?= esc_html($req->request_subject); ?></span>
		</div>
		<div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
			<span class="br-badge <?= $s['badge']; ?>"><span class="icon <?= $s['icon']; ?>"></span> <?= $s['label']; ?></span>
			<span style="font-size:11px;color:rgba(255,255,255,0.35);"><?= $time_ago . ' ' . __("ago","bluerabbit"); ?></span>
		</div>
	</div>

	<div class="br-form-component" style="margin-top:10px;white-space:pre-wrap;font-size:14px;color:rgba(255,255,255,0.75);"><?= esc_html($req->request_content); ?></div>

	<?php if($req->request_admin_note){ ?>
		<div style="margin-top:10px;padding:12px;background:rgba(28,194,235,0.08);border:1px solid rgba(28,194,235,0.15);border-radius:8px;">
			<span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#1cc2eb;">
				<span class="icon icon-document"></span> <?= __("Admin response","bluerabbit"); ?>
			</span>
			<div style="margin-top:6px;font-size:14px;color:rgba(255,255,255,0.85);white-space:pre-wrap;"><?= esc_html($req->request_admin_note); ?></div>
		</div>
	<?php } ?>

	<?php if($req->request_resolved_date && ($req->request_status == 'resolved' || $req->request_status == 'dismissed')){ ?>
		<div style="margin-top:6px;font-size:11px;color:rgba(255,255,255,0.3);">
			<?= __("Updated","bluerabbit"); ?>: <?= human_time_diff(strtotime($req->request_resolved_date), current_time('timestamp')) . ' ' . __("ago","bluerabbit"); ?>
		</div>
	<?php } ?>

</div>

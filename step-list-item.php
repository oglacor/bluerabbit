<?php
$step_colors = [
	'dialogue' => '#1cc2eb', 'open' => '#42a5f5', 'jump' => '#7c4dff',
	'system' => '#ff9800', 'win' => '#24da98', 'fail' => '#f44336',
	'item-req' => '#e040fb', 'item-grab' => '#e040fb', 'path-choice' => '#9f40e2',
	'choose-avatar' => '#7c4dff', 'choose-nickname' => '#7c4dff',
	'video' => '#f7cb15', 'scorm' => '#00bcd4',
];
$sc = $step_colors[$step->step_type] ?? '#1cc2eb';
?>
<div class="br-step-item" id="step-<?= $step->step_id; ?>" style="--step-color:<?= $sc; ?>">
	<input type="hidden" class="the_step_id_val" value="<?= $step->step_id; ?>">
	<div class="br-step-row">
		<div class="br-step-order"><?= $step->step_order; ?></div>
		<span class="br-step-type step-type"><?= $step->step_type; ?></span>
		<div class="br-step-title step-title"><?= esc_html($step->step_title); ?><?= $step->step_type == 'path-choice' ? " <span style='opacity:0.5'>[Group: $step->step_achievement_group]</span>" : ""; ?></div>
		<div class="br-step-actions">
			<button class="br-step-btn br-step-btn-amber" onClick="addStep(<?= $step->step_id; ?>);" title="<?= __('Duplicate', 'bluerabbit'); ?>">
				<span class="icon icon-duplicate"></span>
			</button>
			<button class="br-step-btn br-step-btn-green br-step-edit-btn" onClick="editStep(<?= $step->step_id; ?>);" title="<?= __('Edit', 'bluerabbit'); ?>">
				<span class="icon icon-edit"></span>
			</button>
			<button class="br-step-btn br-step-btn-red" onClick="removeStep(<?= $step->step_id; ?>);" title="<?= __('Delete', 'bluerabbit'); ?>">
				<span class="icon icon-trash"></span>
			</button>
		</div>
	</div>
	<div class="br-step-accordion" id="step-accordion-<?= $step->step_id; ?>"></div>
</div>

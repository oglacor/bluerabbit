<?php
$step_colors = [
	'dialogue' => '#1cc2eb', 'video' => '#f7cb15', 'audio' => '#ff9800', 'gallery' => '#42a5f5', 'find_item' => '#e040fb',
	'multiple_choice' => '#7c4dff', 'keyphrase' => '#00bcd4', 'cryptex' => '#00bcd4', 'puzzle' => '#9f40e2', 'backpack_item' => '#e040fb', 'scorm' => '#00bcd4',
	'survey_choice' => '#42a5f5', 'survey_rating' => '#f7cb15', 'survey_poll' => '#42a5f5', 'open_text' => '#42a5f5', 'upload_image' => '#ff9800', 'upload_video' => '#ff9800',
	'jump_to_step' => '#7c4dff', 'branch_choice' => '#9f40e2',
	'system' => '#ff9800', 'win' => '#24da98', 'fail' => '#f44336', 'choose_nickname' => '#7c4dff', 'choose_avatar' => '#7c4dff',
	'open' => '#42a5f5', 'jump' => '#7c4dff', 'item-req' => '#e040fb', 'item-grab' => '#e040fb', 'path-choice' => '#9f40e2',
	'choose-avatar' => '#7c4dff', 'choose-nickname' => '#7c4dff',
];
$display_skin = $step->step_skin ?: $step->step_type;
$sc = $step_colors[$display_skin] ?? '#1cc2eb';
?>
<div class="br-step-item" id="step-<?= $step->step_id; ?>" style="--step-color:<?= $sc; ?>">
	<input type="hidden" class="the_step_id_val" value="<?= $step->step_id; ?>">
	<div class="br-step-row">
		<div class="br-step-order"><?= $step->step_order; ?></div>
		<span class="br-step-type step-type"><?= $display_skin; ?></span>
		<div class="br-step-title step-title"><?= esc_html($step->step_title); ?></div>
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

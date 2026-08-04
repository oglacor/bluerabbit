<?php
// One row in the achievement Conditions editor (page-new-achievement.php). $c is a
// br_conditions row when editing an existing condition, or null for a blank row (used
// once inside the <template> that brAddAchievementConditionRow() clones in script.js).
// $cond_quests / $cond_tabis / $adv_parent_id come from the including page.
$cur_type      = $c->condition_type ?? '';
$cur_object_id = $c->object_id ?? '';
$cur_threshold = $c->threshold_value ?? '';
?>
<div class="br-cond-row br-flex br-gap-sm br-mb-sm">
	<select class="br-input br-cond-type" onchange="brAchCondRowUpdate(this);">
		<option value=""><?= __("Select a condition...", "bluerabbit"); ?></option>
		<?php foreach (BR_Conditions::CONDITION_TYPES as $type => $label) { ?>
		<option value="<?= esc_attr($type); ?>" <?= $cur_type === $type ? 'selected' : ''; ?>><?= esc_html__($label, "bluerabbit"); ?></option>
		<?php } ?>
	</select>
	<select class="br-input br-cond-quest-picker" style="display:none;">
		<option value=""><?= __("Select milestone...", "bluerabbit"); ?></option>
		<?php foreach ($cond_quests as $q) { ?>
		<option value="<?= (int) $q->quest_id; ?>" <?= ((string) $cur_object_id === (string) $q->quest_id) ? 'selected' : ''; ?>><?= esc_html($q->quest_title); ?></option>
		<?php } ?>
	</select>
	<select class="br-input br-cond-tabi-picker" style="display:none;">
		<option value=""><?= __("Select Tabi...", "bluerabbit"); ?></option>
		<?php foreach ($cond_tabis as $t) { ?>
		<option value="<?= (int) $t->tabi_id; ?>" <?= ((string) $cur_object_id === (string) $t->tabi_id) ? 'selected' : ''; ?>><?= esc_html($t->tabi_name); ?></option>
		<?php } ?>
	</select>
	<input type="number" class="br-input br-cond-threshold br-input-narrow" min="0" step="0.01"
		   placeholder="<?= esc_attr__('Threshold', 'bluerabbit'); ?>" value="<?= esc_attr($cur_threshold); ?>" style="display:none;">
	<button class="br-btn red br-btn-sm" onClick="$(this).closest('.br-cond-row').remove();">
		<span class="icon icon-trash"></span>
	</button>
</div>

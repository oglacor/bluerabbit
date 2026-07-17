<h3 class="br-panel-title" id="tutorial-quest-conditions"><span class="icon icon-lock"></span> <?= __("Conditions", "bluerabbit"); ?></h3>
<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("Milestones/achievements required, a key item required, and thresholds like level or journey completion %", "bluerabbit"); ?></span>

<?php if ($is_edit) { ?>
	<button type="button" class="br-btn" onClick="openQuestConditionsModal(<?= $quest->quest_id; ?>);">
		<span class="icon icon-check"></span> <?= __("Conditions", "bluerabbit"); ?>
	</button>
<?php } else { ?>
	<p class="br-form-hint"><?= __("Save this milestone first to set conditions.", "bluerabbit"); ?></p>
<?php } ?>

<div class="overlay-layer quest-conditions-overlay" id="quest-conditions-overlay">
	<div class="tabi-conditions-modal-content" id="quest-conditions-content">
		<span class="br-text-12 grey-400"><?php _e("Loading...", "bluerabbit"); ?></span>
	</div>
</div>

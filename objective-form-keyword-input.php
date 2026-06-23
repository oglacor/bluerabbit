<?php
$objective_content_editor_id = "objective_content_" . $c->objective_id;
$objective_success_editor_id = "objective_success_message_" . $c->objective_id;
?>
<div class="w-full h-70"></div>
<div class="objective max-w-900 boxed relative layer base" id="objective-form-<?= $c->objective_id; ?>"
	 style="background:rgba(11,61,79,0.95);border:1px solid rgba(28,194,235,0.2);border-radius:12px;color:#fff">
	<input type="hidden" class="objective-id-value" value="<?= $c->objective_id; ?>">
	<input type="hidden" value="keyword-input" class="objective-type">
	<input type="hidden" value="<?= $c->objective_order; ?>" class="objective-order">

	<!-- Header -->
	<div style="padding:14px 20px;border-bottom:1px solid rgba(28,194,235,0.12);display:flex;align-items:center;gap:12px">
		<span class="icon icon-objectives" style="font-size:20px;color:#1cc2eb"></span>
		<span style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:22px;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;flex:1"><?= __("Edit Input Keyword", "bluerabbit"); ?></span>
		<button class="br-btn br-btn-red" style="padding:6px 10px" onClick="tinymce.remove('#<?= $objective_success_editor_id; ?>');hideAllOverlay();"><span class="icon icon-cancel"></span></button>
	</div>

	<div style="padding:20px">
		<!-- Keyword -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Keyword", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg objective-keyword" id="objective-text-<?= $key; ?>" type="text" maxlength="36"
				   placeholder="<?= __('Type the Keyword to find', 'bluerabbit'); ?>" value="<?= esc_attr($c->objective_keyword); ?>">
			<span class="br-form-hint" style="color:#f44336"><span class="icon icon-warning"></span> <?= __("Updating the keyword will reset the objective for the players", "bluerabbit"); ?></span>
		</div>

		<!-- Hint -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Hint", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Clue or question", "bluerabbit"); ?></span>
			<?php
			$wp_editor_settings = ['quicktags' => true, 'editor_height' => 250];
			wp_editor($c->objective_content, $objective_content_editor_id, $wp_editor_settings);
			?>
		</div>

		<!-- EP Cost -->
		<?php if ($use_encounters) { ?>
		<div class="br-form-group">
			<label class="br-form-label"><span class="icon icon-activity"></span> <?= __("EP Cost", "bluerabbit"); ?></label>
			<input class="br-input objective-ep-cost" id="ep-cost-<?= $key; ?>" type="number" value="<?= $c->ep_cost; ?>">
		</div>
		<?php } else { ?>
		<input id="ep-cost-<?= $key; ?>" type="hidden" value="0" disabled>
		<?php } ?>

		<!-- Success Message -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Success Message", "bluerabbit"); ?></label>
			<?php wp_editor($c->objective_success_message, $objective_success_editor_id, $wp_editor_settings); ?>
		</div>

		<div style="text-align:center;padding-top:12px">
			<button class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateObjective(<?= $c->objective_id; ?>);">
				<span class="icon icon-check"></span> <?= __("Update objective", "bluerabbit"); ?>
			</button>
		</div>
	</div>
</div>

<script>
	tinyMCE.execCommand('mceAddEditor', true, '<?= $objective_success_editor_id; ?>');
	tinyMCE.execCommand('mceAddEditor', true, '<?= $objective_content_editor_id; ?>');
</script>

<tr id="achievement-unique-code-<?= $c->code_id; ?>">
	<td class="br-auc-copy-cell">
		<input id="<?= "ach-code-$c->code_id"; ?>" type="hidden" value="<?= get_bloginfo('url') . "/magic-link/?c=$c->code_value&adv=$a->adventure_id"; ?>">
		<button class="br-step-btn br-step-btn-green br-auc-copy-btn" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');" title="<?= __('Copy', 'bluerabbit'); ?>">
			<span class="icon icon-qr br-auc-copy-icon"></span>
		</button>
	</td>
	<td class="br-auc-code-cell">
		<span class="br-auc-code-value"><?= $c->code_value; ?></span>
		<span class="legend border rounded-max br-auc-copied-legend" id="legend-<?= $c->code_id; ?>">
			<?= __("Link Copied", "bluerabbit"); ?>
		</span>
	</td>
	<td class="br-auc-actions-cell">
		<div class="br-auc-actions-row">
			<button class="br-btn br-auc-action-copy" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');">
				<span class="icon icon-duplicate"></span> <?= __("Copy", "bluerabbit"); ?>
			</button>
			<button class="br-step-btn br-step-btn-red br-auc-action-delete" onClick="deleteAchievementCode(<?= $c->code_id; ?>);" title="<?= __('Delete', 'bluerabbit'); ?>">
				<span class="icon icon-trash"></span>
			</button>
		</div>
	</td>
</tr>

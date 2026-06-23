<tr id="achievement-unique-code-<?= $c->code_id; ?>">
	<td style="width:50px">
		<input id="<?= "ach-code-$c->code_id"; ?>" type="hidden" value="<?= get_bloginfo('url') . "/magic-link/?c=$c->code_value&adv=$a->adventure_id"; ?>">
		<button class="br-step-btn br-step-btn-green" style="width:36px;height:36px" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');" title="<?= __('Copy', 'bluerabbit'); ?>">
			<span class="icon icon-qr" style="font-size:18px"></span>
		</button>
	</td>
	<td style="position:relative">
		<span style="font-size:15px;font-weight:600;letter-spacing:0.5px;color:rgba(255,255,255,0.85)"><?= $c->code_value; ?></span>
		<span class="legend border rounded-max" id="legend-<?= $c->code_id; ?>" style="background:#24da98;color:#fff;position:absolute;top:-6px;right:10px;padding:3px 10px;border-radius:12px;font-size:11px;opacity:0;transition:opacity 0.3s">
			<?= __("Link Copied", "bluerabbit"); ?>
		</span>
	</td>
	<td style="width:120px">
		<div style="display:flex;gap:4px;justify-content:flex-end">
			<button class="br-btn" style="padding:4px 10px;font-size:12px" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');">
				<span class="icon icon-duplicate"></span> <?= __("Copy", "bluerabbit"); ?>
			</button>
			<button class="br-step-btn br-step-btn-red" style="width:30px;height:30px" onClick="deleteAchievementCode(<?= $c->code_id; ?>);" title="<?= __('Delete', 'bluerabbit'); ?>">
				<span class="icon icon-trash"></span>
			</button>
		</div>
	</td>
</tr>

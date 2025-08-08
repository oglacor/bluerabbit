<tr class="<?= $c->player_id ? 'purple-bg-400 white-color' : 'white-bg'; ?> padding-10" id="achievement-unique-code-<?=$c->code_id; ?>">
	<td>
		<input id="<?= "ach-code-$c->code_id"; ?>" type="hidden" class="form-ui w-full" value="<?php echo get_bloginfo('url')."/magic-link/?c=$c->code_value&adv=$a->adventure_id"; ?>">
		<button class="icon-button font _24 sq-40  white-bg purple-400" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');">
			<span class="icon icon-qr font _28"></span>
		</button>
	</td>
	<td class="relative">
		<div class="icon-group">
			<div class="icon-content">
				<span class="line font _24 grey-800"><?= $c->code_value; ?></span>
			</div>
		</div>
		<span class="legend border rounded-max black-bg white-color" id="legend-<?= $c->code_id; ?>">
			<span class="font _12  padding-10 "><?php _e("Link Copied","bluerabbit"); ?></span>
		</span>
	</td>
	<td>
		<button class="form-ui purple-bg-400 white-color font main w300 _16" onClick="copyTextFrom('<?= "#ach-code-$c->code_id"; ?>','#legend-<?= $c->code_id; ?>');">
			<span class="line font _14"> <?= __("Copy link","bluerabbit"); ?></span>
		</button>
		<button class="icon-button font _24 sq-40  red-bg-400 white-color" onClick="deleteAchievementCode(<?= $c->code_id; ?>);">
			<span class="icon icon-trash"></span>
		</button>
	</td>
</tr>

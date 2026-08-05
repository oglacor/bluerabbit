<?php $org_role = $player->role ?? 'player'; ?>
<tr id="player-org-row-<?= (int)$player->player_id; ?>">
	<td><?= (int)$player->player_id; ?></td>
	<td><?= esc_html($player->player_display_name ?: trim($player->player_first . ' ' . $player->player_last)); ?></td>
	<td><?= esc_html($player->player_email); ?></td>
	<td>
		<button class="br-btn br-btn-sm <?= $org_role === 'manager' ? 'amber' : 'ghost'; ?>"
			title="<?= esc_attr($org_role === 'manager' ? __('Remove manager role','bluerabbit') : __('Set as org manager','bluerabbit')); ?>"
			onclick="orgSetPlayerRole(<?= (int)$player->player_id; ?>, '<?= $org_role === 'manager' ? 'player' : 'manager'; ?>');">
			<span class="icon icon-star"></span>
		</button>
	</td>
	<td>
		<button class="br-btn red br-btn-sm"
			title="<?= esc_attr(__('Remove from org','bluerabbit')); ?>"
			onclick="brConfirmInline(this,'<?= esc_js(__('Remove?','bluerabbit')); ?>',function(){ removePlayerFromOrg(<?= (int)$player->player_id; ?>, <?= (int)$org->org_id; ?>); });">
			<span class="icon icon-cancel"></span>
		</button>
	</td>
</tr>

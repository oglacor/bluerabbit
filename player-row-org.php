<?php $player_role = $player->role; ?>
<tr id="player-org-row-<?= (int)$player->player_id; ?>" class="role-<?= esc_attr($player_role); ?>">
	<td><?= (int)$player->player_id; ?></td>
	<td><?= esc_html($player->player_display_name ?: trim($player->player_first . ' ' . $player->player_last)); ?></td>
	<td><?= esc_html($player->player_email); ?></td>
	<td>
		<div class="br-actions">
			<button class="br-btn br-btn-sm <?= $player_role === 'player' ? 'green' : 'ghost'; ?>"
				<?php if ($player_role !== 'player'): ?>onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>, 'player');"<?php endif; ?>>
				<span class="icon icon-check"></span> <?= __('Player','bluerabbit'); ?>
			</button>
			<button class="br-btn br-btn-sm <?= $player_role === 'gm' ? 'amber' : 'ghost'; ?>"
				<?php if ($player_role !== 'gm'): ?>onclick="showOverlay('#confirm-gm-<?= (int)$player->player_id; ?>');"<?php endif; ?>>
				<span class="icon icon-star"></span> <?= __('GM','bluerabbit'); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-gm-<?= (int)$player->player_id; ?>">
				<button class="br-btn cyan" onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>, 'gm');">
					<span class="icon icon-activity"></span> <?= __('Grant superpowers?','bluerabbit'); ?>
				</button>
				<button class="br-close-btn" onclick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
			<button class="br-btn br-btn-sm <?= $player_role === 'npc' ? 'purple' : 'ghost'; ?>"
				<?php if ($player_role !== 'npc'): ?>onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>, 'npc');"<?php endif; ?>>
				<span class="icon icon-carrot"></span> <?= __('NPC','bluerabbit'); ?>
			</button>
		</div>
	</td>
	<td>
		<button class="br-btn red br-btn-sm"
			title="<?= esc_attr(__('Remove from org','bluerabbit')); ?>"
			onclick="brConfirmInline(this,'<?= esc_js(__('Remove?','bluerabbit')); ?>',function(){ removePlayerFromOrg(<?= (int)$player->player_id; ?>, <?= (int)$org->org_id; ?>); });">
			<span class="icon icon-cancel"></span>
		</button>
	</td>
</tr>

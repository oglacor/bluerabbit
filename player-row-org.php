<?php $player_role = $player->role; ?>
<tr id="player-org-row-<?= (int)$player->player_id; ?>" class="role-<?= esc_attr($player_role); ?>">
	<td class="br-text-12-muted"><?= (int)$player->player_id; ?></td>
	<td class="w500">
		<?= esc_html($player->player_display_name ?: trim($player->player_first . ' ' . $player->player_last)); ?>
	</td>
	<td class="br-text-12-muted"><?= esc_html($player->player_email); ?></td>
	<td class="roles">
		<button class="form-ui role-button-player" <?php if ($player_role !== 'player'): ?> onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>, 'player');" <?php endif; ?>>
			<span class="icon icon-check"></span> <?= __("Player","bluerabbit"); ?>
		</button>
		<button class="form-ui role-button-gm" <?php if ($player_role !== 'gm'): ?> onclick="showOverlay('#confirm-gm-<?= (int)$player->player_id; ?>');" <?php endif; ?>>
			<span class="icon icon-star"></span> <?= __("GM","bluerabbit"); ?>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-gm-<?= (int)$player->player_id; ?>">
			<button class="form-ui white-bg" onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>, 'gm');">
				<span class="icon-group">
					<span class="br-icon-btn br-icon-btn-teal"><span class="icon icon-activity white-color"></span></span>
					<span class="icon-content">
						<span class="line teal-400 font _18 w900"><?= __("Grant superpowers?","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="br-close-btn" onclick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
		<button class="form-ui role-button-npc" <?php if ($player_role !== 'npc'): ?> onclick="setPlayerOrgCapabilities(<?= (int)$player->player_id; ?>,'npc');" <?php endif; ?>>
			<span class="icon icon-carrot"></span> <?= __("NPC","bluerabbit"); ?>
		</button>
	</td>
	<td>
		<button class="br-icon-cancel-red" onclick="showOverlay('#confirm-remove-org-player-<?= (int)$player->player_id; ?>');" title="<?= esc_attr(__('Remove from org','bluerabbit')); ?>">
			<span class="icon icon-cancel white-color"></span>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-remove-org-player-<?= (int)$player->player_id; ?>">
			<button class="form-ui white-bg" onclick="removePlayerFromOrg(<?= (int)$player->player_id; ?>, <?= (int)$org->org_id; ?>);">
				<span class="icon-group">
					<span class="br-icon-btn br-icon-btn-red"><span class="icon icon-cancel white-color"></span></span>
					<span class="icon-content">
						<span class="line red-A400 font _18 w900"><?= __("Remove from org?","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="br-close-btn" onclick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
	</td>
</tr>

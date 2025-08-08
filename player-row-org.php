<?php $player_role = $player->role;  ?>
<tr id="player-org-row-<?= $player->player_id; ?>" class="<?= "role-$player_role"; ?>">
	<td><?= $player->player_id; ?></td>
	<td><?= $player->player_nickname; ?></td>
	<td><?= $player->player_first; ?></td>
	<td><?= $player->player_last; ?></td>
	<td><?= $player->player_email; ?></td>
	<td class="roles">
		<button class="form-ui role-button-player"  <?php if($player_role !='player'){ ?> onClick="setPlayerOrgCapabilities(<?= $player->player_id; ?>, 'player');"  <?php } ?>  >
			<span class="icon icon-check"></span>
			<?= __("Player","bluerabbit"); ?>
		</button>

		<button class="form-ui role-button-gm"  <?php if($player_role != 'gm'){ ?> onClick="showOverlay('#confirm-gm-<?= $player->player_id; ?>');"  <?php } ?> >
			<span class="icon icon-star"></span>
			<?= __("GM","bluerabbit"); ?>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-gm-<?= $player->player_id; ?>">
			<button class="form-ui white-bg" onClick="setPlayerOrgCapabilities(<?= $player->player_id; ?>, 'gm');">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  icon-sm teal-bg-400 icon-sm">
						<span class="icon icon-activity white-color"></span>
					</span>
					<span class="icon-content">
						<span class="line teal-400 font _18 w900"><?= __("Grant superpowers?","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
		<button class="form-ui role-button-npc" <?php if($player_role != 'npc'){ ?> onClick="setPlayerOrgCapabilities(<?= $player->player_id; ?>,'npc');"  <?php } ?>  >
			<span class="icon icon-carrot"></span>
			<?= __("NPC","bluerabbit"); ?>
		</button>
	</td>
	<td>
		<button class="form-ui icon-sm red-bg-200 white-color" onClick="showOverlay('#confirm-remove-player-<?= $player->player_id; ?>');">
			<?= __("Remove Player","bluerabbit"); ?>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-remove-player-<?= $player->player_id; ?>">
			<button class="form-ui white-bg" onClick="removePlayerFromOrg(<?= $player->player_id; ?>,<?= $org->org_id; ?>);">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
						<span class="icon icon-cancel white-color"></span>
					</span>
					<span class="icon-content">
						<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
	</td>
</tr>

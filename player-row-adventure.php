<?php $player_role = $play->player_adventure_role;  ?>
<tr id="player-row-<?= $play->player_id; ?>" class="<?= "role-$player_role"; ?>">
	<td><?= $play->player_id; ?></td>
	<td><?= $play->user_login; ?></td>
	<td><?= $play->player_first; ?></td>
	<td><?= $play->player_last; ?></td>
	<td><?= $play->player_email; ?></td>
	<td>
		<a target="_blank" href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adventure->adventure_id&player_id=$play->player_id"; ?>">
			<span class="icon icon-document"></span>
		</a>
	</td>
	<td class="roles">
		<?php if($play->player_id != $adventure->adventure_owner){ ?>
			<button class="form-ui role-button-player"  <?php if($player_role !='player'){ ?> onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'player'"; ?>);"  <?php } ?>  >
				<span class="icon icon-check"></span>
				<?= __("Player","bluerabbit"); ?>
			</button>
			<?php if($config['multiple_gms']['value']>0){  ?>
				<button class="form-ui role-button-gm"  <?php if($player_role != 'gm'){ ?> onClick="showOverlay('#confirm-gm-<?= $play->player_id; ?>');"  <?php } ?> >
					<span class="icon icon-star"></span>
					<?= __("GM","bluerabbit"); ?>
				</button>
				<div class="confirm-action overlay-layer" id="confirm-gm-<?= $play->player_id; ?>">
					<button class="form-ui white-bg" onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'gm'"; ?>);">
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
			<?php } ?>
			<button class="form-ui role-button-npc" <?php if($player_role != 'npc'){ ?> onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'npc'"; ?>);"  <?php } ?>  >
				<span class="icon icon-carrot"></span>
				<?= __("NPC","bluerabbit"); ?>
			</button>
		<?php }else{ ?>
			<span class="icon icon-star amber-500"></span><strong><?= __("Owner","bluerabbit"); ?></strong>
		<?php } ?>
	</td>
	<td>
		<?php if($player_role != 'gm') { ?>
			<button class="form-ui icon-sm red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?= $play->player_id; ?>');">
				<?= __("Remove Player","bluerabbit"); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-option-<?= $play->player_id; ?>">
				<button class="form-ui white-bg" onClick="updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id, 'out'"; ?>);">
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
		<?php }else{ ?>
			<?php if($play->player_id == $adventure->adventure_owner){ ?>
				<?= __("Owner","bluerabbit"); ?>
			<?php }else{ ?>
				<button class="form-ui icon-sm grey-bg-200 grey-300" disabled>
					<?= __("Turn into player first","bluerabbit"); ?>
				</button>
			<?php } ?>
		<?php } ?>
	</td>
</tr>

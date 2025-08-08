<li id="player-to-org-<?= $p->player_id; ?>" class="margin-5" onClick="addPlayerToOrg(<?= $p->player_id; ?>);">
	<div class="icon-group">
		<button class="icon-button player-picture white-bg sq-60" style="background-image: url(<?= $p->player_picture; ?>);">

		</button>
		<div class="icon-content text-left">
			<span class="line font _18 player-name">
				<?php if($p->player_display_name != ''){
					echo $p->player_display_name;
				}else{
					echo $p->display_name;
				}
				?>
			</span>
			<span class="line player-email font _12">
				<span class='icon icon-level deep-purple-400'></span><strong class='deep-purple-400'><?= $p->player_absolute_level; ?></strong> | 
				<?= $p->player_email; ?>
			</span>
			<input type="hidden" class="player-id" value="<?= $p->player_id; ?>">
		</div>
	</div>
</li>

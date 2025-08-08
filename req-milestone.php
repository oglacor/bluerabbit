<?php
	$border = "";
	$icon_color = "";
	if($mi->quest_type == 'quest'){
		$mi_color = $icon_color = 'light-blue';
	}elseif($mi->quest_type == 'challenge'){
		$mi_color = $icon_color = 'red';
	}elseif($mi->quest_type == 'mission'){
		$mi_color = $icon_color = 'amber';
	}elseif($mi->quest_type == 'survey'){
		$mi_color = $icon_color = 'teal';
	}elseif($mi->quest_type == 'blog-post'){
		$mi_color = $icon_color = 'indigo';
	}
?>
	<div class="card <?php echo "$mi->quest_type";  ?>" id="<?= "req-card-$mi->quest_id"; ?>">
		<div class="background <?=$mi_color; ?> border rounded-8 blend-overlay"></div>
		<div class="background mix-blend-overlay border rounded-8 opacity-30  background-image" style="background-image: url(<?= $mi->mech_badge; ?>);"></div>
		<div class="background mix-blend-overlay border rounded-8 grey-gradient-900 opacity-50"></div>
		<div class="background blue-grey-gradient-900 border rounded-8 "></div>
		<div class="layer base relative text-center padding-10 border rounded-8">
			<?php if($isFinished){ ?>
				<h3 class="font _24 w900 white-color"><?= $mi->quest_title; ?></h3>
				<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $mi->mech_badge; ?>);">
				</div>
				<span class="icon-button absolute font _36 lime-bg-400 layer overlay req-status">
					<span class="icon-check lime-900 perfect-center"></span>
				</span>
				<br>
				<?php
				if($mi->quest_achievement_id > 0){
					if(!in_array($mi->quest_achievement_id, $player_achievements)){ ?>
						<button class="form-ui font _18 grey-bg-800 lime-400" disabled>
							<?= __("Finished by","bluerabbit"); ?>
							<?= $work_authors[$mi->quest_id]; ?>
							
						</button>
					<?php }else{ ?>
						<button class="form-ui font _18 green-bg-400" onClick="loadQuestCard(<?= $mi->quest_id; ?>);">
							<?= __("View","bluerabbit"); ?>
						</button>
					<?php }
				} else{?>
					<button class="form-ui font _18 green-bg-400" onClick="loadQuestCard(<?= $mi->quest_id; ?>);">
						<?= __("View","bluerabbit"); ?>
					</button>
				<?php }?>
			
			
			<?php }else{?>
				<h3 class="font _24 w900 white-color"><?= $mi->quest_title; ?></h3>
				<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $mi->mech_badge; ?>);">
				</div>
				<span class="icon-button perfect-center absolute font _36 red-bg-400 layer overlay  req-status">
					<span class="icon-cancel perfect-center"></span>
				</span>
				<br>
				<?php
				if($mi->quest_achievement_id > 0){
					if(!in_array($mi->quest_achievement_id, $player_achievements)){ ?>
						<button class="form-ui font _18" disabled>
							<?= __("Ask a guild member for help!","bluerabbit"); ?>
						</button>
					<?php }else{ ?>
						<button class="form-ui font _18 blue-bg-400" onClick="loadQuestCard(<?= $mi->quest_id; ?>);">
							<?= __("Attempt now!","bluerabbit"); ?>
						</button>
					<?php }
				} else{?>
					<button class="form-ui font _18 blue-bg-400" onClick="loadQuestCard(<?= $mi->quest_id; ?>);">
						<?= __("Attempt now!","bluerabbit"); ?>
					</button>

				<?php }?>
			<?php }?>
		</div>
	</div>


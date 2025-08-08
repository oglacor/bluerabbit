<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container system-message">
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center">
				<div class="step-content">	
					<div class="input-group inline-table">
						<label class="blue-bg-400 font _18"><?= __("Nickname","bluerabbit"); ?></label>
						<input type="text" class="form-ui font _18" id="the_player_nickname_<?=$step->step_id;?>" value="<?= $current_player->player_display_name; ?>">
					</div>

				</div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<div class="action-buttons">
			<?php if($i >= count($steps)-1){ ?>
				<button class="action-button confirm" id="last-button" onClick="setNickname(<?= $step->step_id;?>); submitPlayerWork();">
					<?= __("Finish","bluerabbit"); ?>
				</button>
			<?php }else{ ?>
				<a class="action-button confirm" href="#step-<?= $steps[($i+1)]->step_order; ?>" onClick="setNickname(<?= $step->step_id;?>);">
					<?= __("Confirm","bluerabbit"); ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
</div>

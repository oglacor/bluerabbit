<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container avatar-choice">
		<?= stepTag(__("Choose your avatar","bluerabbit")); ?>
		<div class="step-avatars">
			<?php if($step_buttons[$step->step_id]){ ?>
				<?php foreach($step_buttons[$step->step_id] as $key=>$b){ ?>
					<button class="avatar-button" id="avatar-button-<?=$b->button_id;?>" onClick="setProfilePicture(<?=$b->button_id;?>);" style="background-image: url(<?= $b->button_image;?>);">
					</button>
					<input type="hidden" id="the_player_picture_<?=$b->button_id;?>" value="<?= $b->button_image;?>">
				<?php } ?>
			<?php }else{ ?>
					<?= __("No avatars to show","bluerabbit"); ?>
			<?php } ?>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>

</div>

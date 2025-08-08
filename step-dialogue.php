<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-dialogue dialogue-attach-<?= $step->step_attach ? $step->step_attach : 'none'; ?>">

		<?php if($step->step_character_image){ ?>
			<div class="character-<?= $step->step_attach; ?> character">
				<div class="character-image">
					<img src="<?= $step->step_character_image; ?>" alt="<?= $step->step_character_name; ?>" id="step-character-<?=$step->step_order;?>">
				</div>
			</div>
		<?php } ?>

		<div class="dialogue-content-container attach-<?= $step->step_attach ? $step->step_attach : 'none'; ?>">
			<?php if($step->step_character_name){ ?>
				<?= stepTag($step->step_character_name); ?>
			<?php } ?>
			<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
				<div class="corner-tl"></div>
				<div class="edge-top"></div>
				<div class="corner-tr"></div>

				<div class="edge-left"></div>
				<div class="center"><?= apply_filters('the_content',$step->step_content);  ?></div>
				<div class="edge-right"></div>

				<div class="corner-bl"></div>
				<div class="edge-bottom"></div> 
				<div class="corner-br"></div>
			</div>
		</div>
		<div class="dialogue-content-spacer"></div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>


<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container jump-to">
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center">
				<div class="step-content">	
					<?= apply_filters('the_content',$step->step_content);  ?>
				</div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<?= stepTag("..// ".__("Choose one option","bluerabbit")); ?>

		<div class="step-choices">
			<?php if($step_buttons[$step->step_id]){ ?>
				<?php foreach($step_buttons[$step->step_id] as $key=>$b){ ?>
					<a class="step-choice action-button" id="button-<?=$b->button_id;?>"  href="#step-<?= $b->button_step_next; ?>">
						<?php if($b->button_image){ ?><img src="<?= $b->button_image;?>" alt=""> <?php } ?>
						<p><?= $b->button_text; ?></p>
					</a>
				<?php } ?>
			<?php }else{ ?>
				<a class="action-button confirm" id="button-<?=$b->button_id;?>" href="#step-<?= $steps[($i+1)]->step_order; ?>">
					<?= __("Next","bluerabbit"); ?>
				</a>
			<?php } ?>
		</div>
	</div>
</div>

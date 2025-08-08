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
					<?= apply_filters('the_content',$step->step_content);  ?>
				</div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<div class="action-buttons">
			<?php if($i >= count($steps)-1){ ?>
				<button class="action-button confirm" id="last-button" onClick="submitPlayerWork();">
					<?= __("Finish","bluerabbit"); ?>
				</button>
			<?php }else{ ?>
				<a class="action-button confirm" href="#step-<?= $steps[($i+1)]->step_order; ?>">
					<?= __("Continue","bluerabbit"); ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
</div>

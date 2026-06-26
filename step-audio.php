<?php $settings = $step->step_settings ? json_decode($step->step_settings, true) : []; ?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-audio">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if (!empty($settings['url'])) { ?>
				<audio controls style="width:100%;margin-top:12px">
					<source src="<?= esc_attr($settings['url']); ?>">
				</audio>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>

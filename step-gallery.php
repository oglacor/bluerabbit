<?php $settings = $step->step_settings ? json_decode($step->step_settings, true) : []; $images = $settings['images'] ?? []; ?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-gallery">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if ($images) { ?>
				<div class="br-step-gallery-grid">
					<?php foreach ($images as $img) { ?>
					<a href="<?= esc_attr($img); ?>" target="_blank" class="br-step-gallery-item">
						<img src="<?= esc_attr($img); ?>" alt="">
					</a>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>

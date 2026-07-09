<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$prev_response = $already_done && $player_step->ps_response ? json_decode($player_step->ps_response, true) : [];
$max_mb = (int) ($settings['max_size_mb'] ?? 5);
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-collect">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center br-text-center">
				<?php if (!empty($settings['prompt'])) { ?><h3><?= esc_html($settings['prompt']); ?></h3><?php } ?>
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>

				<?php if ($already_done && !empty($prev_response['url'])) { ?>
				<img src="<?= esc_attr($prev_response['url']); ?>" alt="" class="br-step-uploaded-preview">
				<div class="br-step-recorded"><span class="icon icon-check"></span> <?= __("Image uploaded", "bluerabbit"); ?></div>
				<?php } else { ?>
				<div class="br-step-upload-area">
					<input type="hidden" id="upload-img-url-<?= $step->step_id; ?>" value="">
					<div id="upload-img-preview-<?= $step->step_id; ?>"></div>
					<button class="action-button" onClick="brUploadStepImage(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<span class="icon icon-image"></span> <?= __("Select Image", "bluerabbit"); ?>
					</button>
					<p class="br-step-upload-hint"><?= sprintf(__("Max %dMB", "bluerabbit"), $max_mb); ?></p>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_done) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$prev_response = $already_done && $player_step->ps_response ? json_decode($player_step->ps_response, true) : [];
$max_mb = (int) ($settings['max_size_mb'] ?? 100);
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-collect">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center" style="text-align:center">
				<?php if (!empty($settings['prompt'])) { ?><h3><?= esc_html($settings['prompt']); ?></h3><?php } ?>
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>

				<?php if ($already_done && !empty($prev_response['url'])) { ?>
				<video controls style="max-width:100%;border-radius:8px;margin:12px 0">
					<source src="<?= esc_attr($prev_response['url']); ?>">
				</video>
				<div style="padding:8px;color:rgba(255,255,255,0.4);font-size:12px"><span class="icon icon-check"></span> <?= __("Video uploaded", "bluerabbit"); ?></div>
				<?php } else { ?>
				<div style="margin:16px 0">
					<input type="hidden" id="upload-vid-url-<?= $step->step_id; ?>" value="">
					<div id="upload-vid-preview-<?= $step->step_id; ?>" style="margin-bottom:8px"></div>
					<button class="action-button" onClick="brUploadStepVideo(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<span class="icon icon-video"></span> <?= __("Select Video", "bluerabbit"); ?>
					</button>
					<p style="font-size:11px;opacity:0.4;margin-top:6px"><?= sprintf(__("Max %dMB", "bluerabbit"), $max_mb); ?></p>
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

<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-validate">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center" style="text-align:center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>

				<?php if ($already_done) { ?>
				<div style="padding:12px;border-radius:8px;background:rgba(36,218,152,0.15);border:1px solid rgba(36,218,152,0.3);color:#24da98;font-weight:700;margin:12px 0">
					<span class="icon icon-check"></span> <?= __("Puzzle complete!", "bluerabbit"); ?>
				</div>
				<?php } else { ?>
				<?php if (!empty($settings['image'])) { ?>
				<div style="margin:16px 0">
					<img src="<?= esc_attr($settings['image']); ?>" alt="" style="max-width:100%;border-radius:8px">
				</div>
				<?php } ?>
				<p style="opacity:0.5;font-size:13px"><?= sprintf(__("Pieces: %d | Difficulty: %s", "bluerabbit"), $settings['pieces'] ?? 9, $settings['difficulty'] ?? 'medium'); ?></p>
				<div class="steps-navigation action-buttons">
					<button class="action-button" onClick="brSubmitGenericStep(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>, {});">
						<?= __("Mark as Complete", "bluerabbit"); ?>
					</button>
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

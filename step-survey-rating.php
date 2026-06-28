<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$min = (int) ($settings['min'] ?? 1);
$max = (int) ($settings['max'] ?? 5);
$labels = $settings['labels'] ?? [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$prev_value = $already_done && $player_step->ps_response ? json_decode($player_step->ps_response, true)['value'] ?? null : null;
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-collect">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center br-text-center">
				<?php if (!empty($settings['question'])) { ?><h3><?= esc_html($settings['question']); ?></h3><?php } ?>

				<div class="br-rating-row">
					<?php if (!empty($labels['min'])) { ?>
					<span class="br-rating-label"><?= esc_html($labels['min']); ?></span>
					<?php } ?>
					<div class="br-rating-buttons" id="sr-buttons-<?= $step->step_id; ?>">
						<?php for ($v = $min; $v <= $max; $v++) { ?>
						<button class="sr-rating-btn <?= ($prev_value !== null && (int)$prev_value === $v) ? 'br-rating-active' : ''; ?>"
							data-value="<?= $v; ?>"
							<?= $already_done ? 'disabled' : ''; ?>
							onClick="brSelectRating(<?= $step->step_id; ?>, <?= $v; ?>);"><?= $v; ?></button>
						<?php } ?>
					</div>
					<?php if (!empty($labels['max'])) { ?>
					<span class="br-rating-label"><?= esc_html($labels['max']); ?></span>
					<?php } ?>
				</div>
				<input type="hidden" id="sr-value-<?= $step->step_id; ?>" value="<?= $prev_value ?? ''; ?>">

				<?php if (!$already_done) { ?>
				<div class="steps-navigation action-buttons">
					<button class="action-button" onClick="brSubmitSurveyRating(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Submit", "bluerabbit"); ?>
					</button>
				</div>
				<?php } else { ?>
				<div class="br-step-recorded"><span class="icon icon-check"></span> <?= __("Response recorded", "bluerabbit"); ?></div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_done) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

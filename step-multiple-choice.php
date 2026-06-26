<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$options = $settings['options'] ?? [];
$allow_multi = !empty($settings['allow_multiple']);
$input_type = $allow_multi ? 'checkbox' : 'radio';
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_correct = ($player_step && $player_step->ps_correct == 1);
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-validate">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if (!empty($settings['question'])) { ?>
				<div class="step-content"><h3><?= esc_html($settings['question']); ?></h3></div>
				<?php } ?>
				<?php if (!empty($settings['question_image'])) { ?>
				<img src="<?= esc_attr($settings['question_image']); ?>" alt="" style="max-width:100%;border-radius:8px;margin:8px 0">
				<?php } ?>

				<div class="br-step-mc-options" id="mc-options-<?= $step->step_id; ?>" style="display:flex;flex-direction:column;gap:8px;margin:16px 0">
					<?php foreach ($options as $opt) { ?>
					<label class="br-step-option <?= $already_correct ? 'disabled' : ''; ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:all 0.15s">
						<input type="<?= $input_type; ?>" name="mc-<?= $step->step_id; ?>" value="<?= esc_attr($opt['id']); ?>" class="mc-input" <?= $already_correct ? 'disabled' : ''; ?> style="width:18px;height:18px;accent-color:#1cc2eb">
						<?php if (!empty($opt['image'])) { ?>
						<img src="<?= esc_attr($opt['image']); ?>" alt="" style="width:40px;height:40px;border-radius:6px;object-fit:cover">
						<?php } ?>
						<span style="font-size:15px"><?= esc_html($opt['text']); ?></span>
					</label>
					<?php } ?>
				</div>

				<div id="mc-feedback-<?= $step->step_id; ?>" style="display:none;padding:10px;border-radius:8px;margin-bottom:12px;font-weight:700;text-align:center"></div>

				<?php if (!$already_correct) { ?>
				<div class="steps-navigation action-buttons" id="mc-submit-<?= $step->step_id; ?>">
					<button class="action-button" onClick="brSubmitMcStep(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Submit Answer", "bluerabbit"); ?>
					</button>
				</div>
				<?php } ?>
				<div id="mc-next-<?= $step->step_id; ?>" style="<?= $already_correct ? '' : 'display:none'; ?>"></div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_correct) { ?>
			<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
		<?php } ?>
	</div>
</div>

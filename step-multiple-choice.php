<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$options = $settings['options'] ?? [];
$step_correct = $step->step_correct ? json_decode($step->step_correct, true) : [];
$correct_count = count($step_correct);
$is_multi = $correct_count > 1 || !empty($settings['allow_multiple']);
$input_type = $is_multi ? 'checkbox' : 'radio';
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_correct = ($player_step && $player_step->ps_correct == 1);
$player_selected = [];
if ($already_correct && $player_step->ps_response) {
	$resp = json_decode($player_step->ps_response, true);
	$player_selected = (array) ($resp['selected'] ?? []);
}
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
				<img src="<?= esc_attr($settings['question_image']); ?>" alt="" class="br-step-question-image">
				<?php } ?>
				<?php if ($is_multi && $correct_count > 1 && !$already_correct) { ?>
				<p class="br-step-mc-hint"><?= sprintf(__("Choose %d options", "bluerabbit"), $correct_count); ?></p>
				<?php } ?>

				<div class="br-step-options-list" id="mc-options-<?= $step->step_id; ?>">
					<?php foreach ($options as $opt) {
						$was_chosen = in_array($opt['id'], $player_selected);
					?>
					<label class="br-step-option <?= $already_correct ? 'disabled' : ''; ?> <?= $was_chosen ? 'br-option-correct' : ''; ?>">
						<input type="<?= $input_type; ?>" name="mc-<?= $step->step_id; ?><?= $is_multi ? '[]' : ''; ?>" value="<?= esc_attr($opt['id']); ?>" class="mc-input" <?= $already_correct ? 'disabled' : ''; ?> <?= $was_chosen ? 'checked' : ''; ?>>
						<?php if (!empty($opt['image'])) { ?>
						<img src="<?= esc_attr($opt['image']); ?>" alt="" class="br-step-option-image">
						<?php } ?>
						<span class="br-step-option-text"><?= esc_html($opt['text']); ?></span>
						<?php if ($was_chosen) { ?>
						<span class="icon icon-check br-option-check-icon"></span>
						<?php } ?>
					</label>
					<?php } ?>
				</div>

				<?php if ($already_correct) { ?>
				<div class="br-step-feedback br-step-feedback-success">
					<span class="icon icon-check"></span> <?= __("Correct!", "bluerabbit"); ?>
				</div>
				<?php } else { ?>
				<div id="mc-feedback-<?= $step->step_id; ?>" class="br-step-feedback"></div>
				<div class="steps-navigation action-buttons" id="mc-submit-<?= $step->step_id; ?>">
					<button class="action-button" onClick="brSubmitMcStep(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Submit Answer", "bluerabbit"); ?>
					</button>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_correct) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

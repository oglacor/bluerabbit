<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$options = $settings['options'] ?? [];
$allow_multi = !empty($settings['allow_multiple']);
$input_type = $allow_multi ? 'checkbox' : 'radio';
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$prev_response = $already_done && $player_step->ps_response ? json_decode($player_step->ps_response, true) : [];
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-collect">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if (!empty($settings['question'])) { ?><h3><?= esc_html($settings['question']); ?></h3><?php } ?>

				<div id="sc-options-<?= $step->step_id; ?>" style="display:flex;flex-direction:column;gap:8px;margin:16px 0">
					<?php foreach ($options as $opt) { ?>
					<label class="br-step-option <?= $already_done ? 'disabled' : ''; ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);cursor:pointer">
						<input type="<?= $input_type; ?>" name="sc-<?= $step->step_id; ?>" value="<?= esc_attr($opt['id']); ?>" class="sc-input" <?= $already_done ? 'disabled' : ''; ?> style="width:18px;height:18px;accent-color:#1cc2eb"
							<?= (isset($prev_response['selected']) && in_array($opt['id'], (array) $prev_response['selected'])) ? 'checked' : ''; ?>>
						<span><?= esc_html($opt['text']); ?></span>
					</label>
					<?php } ?>
				</div>

				<?php if (!$already_done) { ?>
				<div class="steps-navigation action-buttons" id="sc-submit-<?= $step->step_id; ?>">
					<button class="action-button" onClick="brSubmitSurveyChoice(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Submit", "bluerabbit"); ?>
					</button>
				</div>
				<?php } else { ?>
				<div style="padding:8px;color:rgba(255,255,255,0.4);font-size:12px;text-align:center"><span class="icon icon-check"></span> <?= __("Response recorded", "bluerabbit"); ?></div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_done) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

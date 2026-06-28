<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$wheel_count = (int) ($settings['wheel_count'] ?? 7);
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
			<div class="center br-text-center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if (!empty($settings['prompt'])) { ?>
				<h3 class="br-step-prompt"><?= esc_html($settings['prompt']); ?></h3>
				<?php } ?>

				<?php if ($already_correct) { ?>
				<div class="br-step-feedback br-step-feedback-success">
					<span class="icon icon-check"></span> <?= __("Unlocked!", "bluerabbit"); ?>
				</div>
				<?php } else { ?>
				<div class="br-cryptex-wheels" id="cryptex-<?= $step->step_id; ?>">
					<?php for ($w = 0; $w < $wheel_count; $w++) { ?>
					<input type="text" maxlength="1" class="cryptex-wheel form-ui" data-index="<?= $w; ?>"
						onkeyup="if(this.value.length===1){var n=this.nextElementSibling;if(n&&n.classList.contains('cryptex-wheel'))n.focus();}" autocomplete="off">
					<?php } ?>
				</div>
				<div id="cryptex-feedback-<?= $step->step_id; ?>" class="br-step-feedback"></div>
				<div class="steps-navigation action-buttons">
					<button class="action-button" onClick="brSubmitCryptexStep(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Unlock", "bluerabbit"); ?>
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

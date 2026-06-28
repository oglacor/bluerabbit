<?php
$ot_settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$min_words = (int) ($ot_settings['min_words'] ?? 0);
$ai_validate = !empty($ot_settings['ai_validate']);
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$player_answer = '';
if ($already_done && $player_step->ps_response) {
	$resp = json_decode($player_step->ps_response, true);
	$player_answer = $resp['content'] ?? '';
}
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container open-field"
		data-step-id="<?= $step->step_id; ?>"
		data-quest-id="<?= $q->quest_id; ?>"
		data-adventure-id="<?= $adv_child_id; ?>"
		data-min-words="<?= $min_words; ?>"
		data-ai-validate="<?= $ai_validate ? '1' : '0'; ?>">
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center">
				<div class="step-content">
					<?= apply_filters('the_content', $step->step_content); ?>
				</div>

				<?php if ($already_done) { ?>
				<!-- Submitted answer view -->
				<div class="br-ot-answer-display" id="ot-answer-<?= $step->step_id; ?>">
					<div class="br-ot-answer-label"><?= __("Your answer", "bluerabbit"); ?></div>
					<div class="br-ot-answer-text"><?= apply_filters('the_content', $player_answer); ?></div>
					<button class="br-btn br-btn-sm" onClick="brEditOpenText(<?= $step->step_id; ?>);">
						<span class="icon icon-edit"></span> <?= __("Edit Content", "bluerabbit"); ?>
					</button>
				</div>
				<!-- Hidden editor for editing -->
				<div class="br-ot-editor-wrap br-initially-hidden" id="ot-editor-wrap-<?= $step->step_id; ?>">
					<div class="step-content-text-editor editor" id="step-content-text-<?=$step->step_id;?>">
						<?php
							$wp_editor_settings = array(
								'quicktags'=> false, 'textarea_rows'=>5, 'media_buttons'=>false,
								'tinymce' => array(
									'toolbar1'=> 'bold,italic,separator,alignleft,aligncenter,alignright,separator,link,bullist,wp_add_media',
								),
							);
							wp_editor($player_answer ?? '', 'the_pp_content', $wp_editor_settings);
						?>
					</div>
					<?php if ($min_words > 0) { ?>
					<p class="br-step-mc-hint"><?= sprintf(__("Minimum %d words required", "bluerabbit"), $min_words); ?></p>
					<?php } ?>
					<div id="ot-feedback-<?= $step->step_id; ?>" class="br-step-feedback"></div>
					<div class="steps-navigation action-buttons">
						<button class="action-button" onClick="brCheckOpenText(<?= $step->step_id; ?>);">
							<?= __("Check Answer", "bluerabbit"); ?>
						</button>
					</div>
				</div>
				<div class="br-step-feedback br-step-feedback-success" id="ot-success-<?= $step->step_id; ?>">
					<span class="icon icon-check"></span> <?= __("Submitted!", "bluerabbit"); ?>
				</div>

				<?php } else { ?>
				<!-- First-time editor -->
				<div class="br-ot-editor-wrap" id="ot-editor-wrap-<?= $step->step_id; ?>">
					<div class="step-content-text-editor editor" id="step-content-text-<?=$step->step_id;?>">
						<?php
							$wp_editor_settings = array(
								'quicktags'=> false, 'textarea_rows'=>5, 'media_buttons'=>false,
								'tinymce' => array(
									'toolbar1'=> 'bold,italic,separator,alignleft,aligncenter,alignright,separator,link,bullist,wp_add_media',
								),
							);
							wp_editor($q->pp_content ?? '', 'the_pp_content', $wp_editor_settings);
						?>
					</div>
					<?php if ($min_words > 0) { ?>
					<p class="br-step-mc-hint"><?= sprintf(__("Minimum %d words required", "bluerabbit"), $min_words); ?></p>
					<?php } ?>
					<div id="ot-feedback-<?= $step->step_id; ?>" class="br-step-feedback"></div>
					<div class="steps-navigation action-buttons" id="ot-check-btn-<?= $step->step_id; ?>">
						<button class="action-button" onClick="brCheckOpenText(<?= $step->step_id; ?>);">
							<?= __("Check Answer", "bluerabbit"); ?>
						</button>
					</div>
				</div>
				<!-- Answer display (shown after validation passes) -->
				<div class="br-ot-answer-display br-initially-hidden" id="ot-answer-<?= $step->step_id; ?>">
					<div class="br-ot-answer-label"><?= __("Your answer", "bluerabbit"); ?></div>
					<div class="br-ot-answer-text" id="ot-answer-text-<?= $step->step_id; ?>"></div>
					<button class="br-btn br-btn-sm" onClick="brEditOpenText(<?= $step->step_id; ?>);">
						<span class="icon icon-edit"></span> <?= __("Edit Content", "bluerabbit"); ?>
					</button>
				</div>
				<div class="br-step-feedback br-step-feedback-success br-initially-hidden" id="ot-success-<?= $step->step_id; ?>">
					<span class="icon icon-check"></span> <?= __("Submitted!", "bluerabbit"); ?>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($already_done) { ?>
			<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
		<?php } ?>
	</div>
</div>

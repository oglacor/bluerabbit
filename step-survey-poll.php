<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$options = $settings['options'] ?? [];
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = !empty($player_step);
$vote_counts = [];
if ($already_done) {
	$all_votes = $wpdb->get_results($wpdb->prepare(
		"SELECT ps_response FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND quest_id = %d",
		$step->step_id, $q->quest_id
	));
	foreach ($all_votes as $v) {
		$r = json_decode($v->ps_response, true);
		$sel = $r['selected'] ?? [];
		foreach ((array)$sel as $s) { $vote_counts[$s] = ($vote_counts[$s] ?? 0) + 1; }
	}
}
$total_votes = array_sum($vote_counts) ?: 1;
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-collect">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center">
				<?php if (!empty($settings['question'])) { ?><h3><?= esc_html($settings['question']); ?></h3><?php } ?>

				<div id="poll-options-<?= $step->step_id; ?>" style="display:flex;flex-direction:column;gap:8px;margin:16px 0">
					<?php foreach ($options as $opt) { ?>
					<?php $pct = $already_done ? round(($vote_counts[$opt['id']] ?? 0) / $total_votes * 100) : 0; ?>
					<label class="br-step-option <?= $already_done ? 'disabled' : ''; ?>" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);cursor:pointer;position:relative;overflow:hidden">
						<?php if ($already_done) { ?>
						<div style="position:absolute;left:0;top:0;bottom:0;width:<?= $pct; ?>%;background:rgba(28,194,235,0.12);transition:width 0.3s"></div>
						<?php } ?>
						<input type="radio" name="poll-<?= $step->step_id; ?>" value="<?= esc_attr($opt['id']); ?>" class="poll-input" <?= $already_done ? 'disabled' : ''; ?> style="width:18px;height:18px;accent-color:#1cc2eb;position:relative;z-index:1">
						<span style="position:relative;z-index:1;flex:1"><?= esc_html($opt['text']); ?></span>
						<?php if ($already_done) { ?>
						<span style="position:relative;z-index:1;font-weight:700;color:#1cc2eb"><?= $pct; ?>%</span>
						<?php } ?>
					</label>
					<?php } ?>
				</div>

				<?php if (!$already_done) { ?>
				<div class="steps-navigation action-buttons">
					<button class="action-button" onClick="brSubmitPoll(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>);">
						<?= __("Vote", "bluerabbit"); ?>
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

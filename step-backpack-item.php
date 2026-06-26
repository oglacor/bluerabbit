<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$req_item_id = $settings['item_id'] ?? $step->step_item;
$req_item = $req_item_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id = %d", $req_item_id)) : null;
$player_step = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps WHERE step_id = %d AND player_id = %d AND quest_id = %d AND adventure_id = %d",
	$step->step_id, $current_user->ID, $q->quest_id, $adv_child_id
));
$already_done = ($player_step && $player_step->ps_correct == 1);
$has_item = $req_item_id ? $wpdb->get_var($wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions WHERE player_id = %d AND adventure_id = %d AND object_id = %d AND trnx_status = 'publish'",
	$current_user->ID, $adv_child_id, $req_item_id
)) : 0;
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-validate">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center" style="text-align:center">
				<?php if (!empty($settings['prompt'])) { ?><h3><?= esc_html($settings['prompt']); ?></h3><?php } ?>
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>

				<?php if ($req_item) { ?>
				<div style="margin:16px auto;display:inline-block;opacity:<?= ($has_item || $already_done) ? '1' : '0.4'; ?>">
					<?php if ($req_item->item_badge) { ?>
					<img src="<?= esc_attr($req_item->item_badge); ?>" alt="" style="max-width:100px;border-radius:12px">
					<?php } ?>
					<div style="font-weight:700;margin-top:6px"><?= esc_html($req_item->item_name); ?></div>
				</div>
				<?php } ?>

				<?php if ($already_done) { ?>
				<div style="padding:12px;border-radius:8px;background:rgba(36,218,152,0.15);color:#24da98;font-weight:700;margin:12px 0">
					<span class="icon icon-check"></span> <?= __("Item verified!", "bluerabbit"); ?>
				</div>
				<?php } elseif ($has_item) { ?>
				<div class="steps-navigation action-buttons">
					<button class="action-button success" onClick="brSubmitGenericStep(<?= $step->step_id; ?>, <?= $q->quest_id; ?>, <?= $adv_child_id; ?>, {player_id:<?= $current_user->ID; ?>,adventure_id:<?= $adv_child_id; ?>});">
						<?= __("Use Item", "bluerabbit"); ?>
					</button>
				</div>
				<?php } else { ?>
				<div style="padding:12px;border-radius:8px;background:rgba(244,67,54,0.15);color:#f44336;font-weight:700;margin:12px 0">
					<span class="icon icon-lock"></span> <?= __("You don't have this item yet", "bluerabbit"); ?>
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

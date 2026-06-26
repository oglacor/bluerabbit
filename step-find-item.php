<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$reward_item_id = $step->step_item_reward ?: ($settings['item_id'] ?? 0);
$reward_item = $reward_item_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id = %d", $reward_item_id)) : null;
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-find-item">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center" style="text-align:center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if ($reward_item) { ?>
				<div style="margin:16px auto;display:inline-block">
					<?php if ($reward_item->item_badge) { ?>
					<img src="<?= esc_attr($reward_item->item_badge); ?>" alt="<?= esc_attr($reward_item->item_name); ?>" style="max-width:120px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.4)">
					<?php } ?>
					<div style="font-size:18px;font-weight:700;margin-top:8px"><?= esc_html($reward_item->item_name); ?></div>
					<div style="font-size:12px;opacity:0.6"><?= esc_html($settings['message'] ?? __("Added to your backpack!", "bluerabbit")); ?></div>
				</div>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>

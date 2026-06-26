<?php
$settings = $step->step_settings ? json_decode($step->step_settings, true) : [];
$group_id = $step->step_branch_group_id ?: ($settings['group_id'] ?? 0);
$existing_choice = $group_id ? BR_Branch::instance()->getPlayerBranch($current_user->ID, $adv_child_id, $group_id) : null;
$paths = $group_id ? BR_Branch::instance()->getGroupAchievements($group_id) : [];
?>
<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container step-branch">
		<div class="dialogue-box">
			<div class="corner-tl"></div><div class="edge-top"></div><div class="corner-tr"></div>
			<div class="edge-left"></div>
			<div class="center" style="text-align:center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if (!empty($settings['prompt'])) { ?><h3 style="margin:16px 0"><?= esc_html($settings['prompt']); ?></h3><?php } ?>

				<?php if ($existing_choice) { ?>
				<?php $chosen_ach = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id = %d", $existing_choice->achievement_id)); ?>
				<div style="padding:16px;border-radius:12px;background:rgba(28,194,235,0.1);border:1px solid rgba(28,194,235,0.25);margin:16px 0">
					<?php if ($chosen_ach && $chosen_ach->achievement_badge) { ?>
					<img src="<?= esc_attr($chosen_ach->achievement_badge); ?>" alt="" style="max-width:80px;border-radius:10px;margin-bottom:8px">
					<?php } ?>
					<div style="font-size:18px;font-weight:700;color:#1cc2eb"><?= $chosen_ach ? esc_html($chosen_ach->achievement_name) : ''; ?></div>
					<div style="font-size:12px;opacity:0.5;margin-top:4px"><?= __("Your path has been chosen", "bluerabbit"); ?></div>
				</div>
				<?php } elseif ($paths) { ?>
				<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:20px 0">
					<?php foreach ($paths as $pa) { ?>
					<button class="br-path-choice-card" onClick="brChooseBranch(<?= $adv_child_id; ?>, <?= $group_id; ?>, <?= $pa->achievement_id; ?>, <?= $step->step_id; ?>, <?= $q->quest_id; ?>);"
						style="flex:1;min-width:140px;max-width:200px;padding:20px 16px;border-radius:12px;background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.1);cursor:pointer;transition:all 0.2s;text-align:center">
						<?php if ($pa->achievement_badge) { ?>
						<img src="<?= esc_attr($pa->achievement_badge); ?>" alt="" style="max-width:70px;border-radius:10px;margin-bottom:8px">
						<?php } ?>
						<div style="font-weight:700;font-size:16px;color:#fff"><?= esc_html($pa->achievement_name); ?></div>
						<?php if ($pa->achievement_content) { ?>
						<div style="font-size:12px;color:rgba(255,255,255,0.5);margin-top:4px"><?= esc_html(wp_trim_words($pa->achievement_content, 15)); ?></div>
						<?php } ?>
					</button>
					<?php } ?>
				</div>
				<p style="font-size:11px;opacity:0.35"><?= __("This choice is permanent and cannot be undone.", "bluerabbit"); ?></p>
				<?php } else { ?>
				<p style="opacity:0.5"><?= __("No paths available for this branch.", "bluerabbit"); ?></p>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($existing_choice) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

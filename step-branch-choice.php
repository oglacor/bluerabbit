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
			<div class="center br-text-center">
				<?php if ($step->step_content) { ?><div class="step-content"><?= apply_filters('the_content', $step->step_content); ?></div><?php } ?>
				<?php if (!empty($settings['prompt'])) { ?><h3 class="br-step-prompt"><?= esc_html($settings['prompt']); ?></h3><?php } ?>

				<?php if ($existing_choice) { ?>
				<?php $chosen_ach = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id = %d", $existing_choice->achievement_id)); ?>
				<div class="br-branch-chosen">
					<?php if ($chosen_ach && $chosen_ach->achievement_badge) { ?>
					<img src="<?= esc_attr($chosen_ach->achievement_badge); ?>" alt="" class="br-branch-chosen-badge">
					<?php } ?>
					<div class="br-branch-chosen-name"><?= $chosen_ach ? esc_html($chosen_ach->achievement_name) : ''; ?></div>
					<div class="br-branch-chosen-label"><?= __("Your path has been chosen", "bluerabbit"); ?></div>
				</div>
				<?php } elseif ($paths) { ?>
				<div class="br-branch-cards">
					<?php foreach ($paths as $pa) { ?>
					<button class="br-path-choice-card" onClick="brChooseBranch(<?= $adv_child_id; ?>, <?= $group_id; ?>, <?= $pa->achievement_id; ?>, <?= $step->step_id; ?>, <?= $q->quest_id; ?>);">
						<?php if ($pa->achievement_badge) { ?>
						<img src="<?= esc_attr($pa->achievement_badge); ?>" alt="" class="br-path-card-badge">
						<?php } ?>
						<div class="br-path-card-name"><?= esc_html($pa->achievement_name); ?></div>
						<?php if ($pa->achievement_content) { ?>
						<div class="br-path-card-desc"><?= esc_html(wp_trim_words($pa->achievement_content, 15)); ?></div>
						<?php } ?>
					</button>
					<?php } ?>
				</div>
				<p class="br-branch-warning"><?= __("This choice is permanent and cannot be undone.", "bluerabbit"); ?></p>
				<?php } else { ?>
				<p class="br-muted"><?= __("No paths available for this branch.", "bluerabbit"); ?></p>
				<?php } ?>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div><div class="edge-bottom"></div><div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php if ($existing_choice) { include (TEMPLATEPATH . "/step-nav-button-next.php"); } ?>
	</div>
</div>

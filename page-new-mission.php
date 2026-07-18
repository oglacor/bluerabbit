<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$quest_id = br_require_id('questID', false);
$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND (quest_type='quest' OR quest_type='challenge' OR quest_type='survey' OR quest_type='mission') ORDER BY mech_level ASC, quest_order ASC");
$items        = BR_Item::instance()->getItems($adventure->adventure_id);
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id);
$paths        = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');
if ($quest_id) {
	foreach ($quests as $q) {
		if ($q->quest_id == $quest_id) {
			$quest = $q;
			$requirements = $wpdb->get_results("SELECT b.req_object_id, b.req_type FROM {$wpdb->prefix}br_quests a LEFT JOIN {$wpdb->prefix}br_reqs b ON a.quest_id = b.quest_id WHERE a.quest_id=$quest->quest_id AND a.quest_status='publish'");
			$objectives = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_objectives WHERE quest_id=$quest->quest_id AND objective_status='publish'");
			$reqs = [];
			foreach ($requirements as $r) {
				if ($r->req_type == 'quest') $reqs['quests'][] = $r->req_object_id;
				elseif ($r->req_type == 'item') $reqs['items'][] = $r->req_object_id;
				elseif ($r->req_type == 'achievement') $reqs['achievements'][] = $r->req_object_id;
			}
		}
	}
}
$is_edit = isset($quest) && $quest;
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-amber">
			<span class="icon icon-mission br-icon-lg br-icon-amber"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Mission", "bluerabbit") : __("New Mission", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_type" value="mission">
		<input type="hidden" id="the_quest_id" value="<?= $is_edit ? $quest->quest_id : ''; ?>">
		<input type="hidden" id="the_quest_order" value="<?= $is_edit ? $quest->quest_order : count($quests); ?>">
		<input type="hidden" value="0" id="the_quest_guild">
		<?php if ($is_edit) { ?>
		<a class="br-btn br-ml-auto" href="<?= get_bloginfo('url') . "/mission/?questID=$quest->quest_id&adventure_id=$quest->adventure_id"; ?>" target="_blank">
			<span class="icon icon-view"></span> <?= __("View Mission", "bluerabbit"); ?>
		</a>
		<?php } ?>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="main-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this);">
			<span class="icon icon-settings"></span> <?= __("Basic", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('mechanics', this);">
			<span class="icon icon-tools"></span> <?= __("Mechanics", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('content', this);">
			<span class="icon icon-document"></span> <?= __("Content", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('objectives', this);">
			<span class="icon icon-objectives"></span> <?= __("Objectives", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('requirements', this);">
			<span class="icon icon-lock"></span> <?= __("Requirements", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('advanced-options', this);">
			<span class="icon icon-config"></span> <?= __("Advanced", "bluerabbit"); ?>
		</button>
	</div>

	<!-- Tab Content -->

		<!-- ═══ BASIC ═══ -->
		<div class="br-scroll-section" id="general"><div class="br-panel br-panel-narrow">
			<h3 class="br-panel-title"><span class="icon icon-settings"></span> <?= __("Basic Settings", "bluerabbit"); ?></h3>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Name", "bluerabbit"); ?></label>
				<input class="br-input br-input-lg" type="text" id="the_quest_title"
					   value="<?= $is_edit ? esc_attr($quest->quest_title) : ''; ?>"
					   placeholder="<?= __('Mission Title', 'bluerabbit'); ?>">
			</div>

			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
					<div class="br-form-component" id="tutorial-color-select">
						<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : 'red'; ?>
						<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
						<?php $color_select_id = "#the_quest_color"; include(TEMPLATEPATH . '/color-select.php'); ?>
					</div>
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Icon", "bluerabbit"); ?></label>
					<div class="br-form-component" id="tutorial-icon-select">
						<?php $selected_icon = isset($quest->quest_icon) ? $quest->quest_icon : 'mission'; ?>
						<input id="the_quest_icon" class="icon-selected" type="hidden" value="<?= $selected_icon; ?>">
						<?php $icon_select_id = "#the_quest_icon"; include(TEMPLATEPATH . '/icon-select.php'); ?>
					</div>
				</div>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Main Image", "bluerabbit"); ?> <span class="br-required">*<?= __("Required", "bluerabbit"); ?></span></label>
				<div class="br-form-component">
					<div class="br-gallery br-gallery-single">
						<?php $thumb_id = 'the_quest_badge'; $file = $is_edit ? $quest->mech_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
					</div>
				</div>
			</div>
		</div></div>

		<!-- ═══ CONTENT ═══ -->
		<div class="br-scroll-section" id="content"><div class="br-panel br-panel-narrow">
			<?php include(get_stylesheet_directory() . '/component-quest-content.php'); ?>
		</div></div>

		<!-- ═══ MECHANICS ═══ -->
		<div class="br-scroll-section" id="mechanics"><div class="br-panel br-panel-narrow">
			<?php include(get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>
		</div></div>

		<!-- ═══ OBJECTIVES ═══ -->
		<div class="br-scroll-section" id="objectives"><div class="br-panel br-panel-narrow">
			<h3 class="br-panel-title"><span class="icon icon-objectives"></span> <?= __("Mission Objectives", "bluerabbit"); ?></h3>

			<?php if ($is_edit) { ?>
				<?php if (empty($objectives)) { ?>
				<p class="br-empty-text">— <?= __("No objectives", "bluerabbit"); ?> —</p>
				<?php } ?>
				<table class="br-table" id="objectives">
					<thead>
						<tr>
							<th><?= __("ID", "bluerabbit"); ?></th>
							<th><?= __("Hint", "bluerabbit"); ?></th>
							<th><?= __("Solution", "bluerabbit"); ?></th>
							<th><?= __("Type", "bluerabbit"); ?></th>
							<?php if ($use_encounters) { ?><th><?= __("EP", "bluerabbit"); ?></th><?php } ?>
							<th><span class="icon icon-edit"></span></th>
							<th><span class="icon icon-trash"></span></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($objectives as $key => $c) { ?>
							<?php include(TEMPLATEPATH . '/objective-row.php'); ?>
						<?php } ?>
					</tbody>
				</table>

				<div class="br-flex-between br-mt-sm">
					<div class="br-actions">
						<button class="br-btn br-btn-red default-actions" onClick="showOverlay('#confirm-reset-objectives');">
							<span class="icon icon-rotate"></span> <?= __("Reset Objectives for Players", "bluerabbit"); ?>
						</button>
						<div class="confirm-action overlay-layer shadow text-center padding-10" id="confirm-reset-objectives">
							<button class="br-btn br-btn-red br-btn-confirm" onClick="resetQuestObjectives(<?= $quest->quest_id; ?>);">
								<span class="icon icon-warning"></span> <?= __("Are you sure?", "bluerabbit"); ?>
							</button>
						</div>
					</div>
					<div class="br-actions">
						<button class="br-btn br-btn-green default-actions" onClick="showOverlay('#new-objective-menu');">
							<span class="icon icon-add"></span> <?= __("New Objective", "bluerabbit"); ?>
						</button>
						<div class="confirm-action overlay-layer shadow text-center padding-10 br-objective-menu" id="new-objective-menu">
							<button class="br-btn" onClick="addObjective('keyword-input');">
								<span class="icon icon-comment"></span> <?= __("Type Keyword", "bluerabbit"); ?>
							</button>
							<button class="br-btn" onClick="addObjective('true-false');">
								<span class="icon icon-like"></span> <?= __("True/False", "bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
			<?php } else { ?>
			<div class="br-empty br-empty-md">
				<span class="icon icon-objectives"></span>
				<h3><?= __("Save the mission first", "bluerabbit"); ?></h3>
			</div>
			<?php } ?>
		</div></div>

		<!-- ═══ REQUIREMENTS ═══ -->
		<div class="br-scroll-section" id="requirements"><div class="br-panel br-panel-narrow">
			<?php
			include(TEMPLATEPATH . '/component-quest-reqs.php');
			include(TEMPLATEPATH . '/component-quest-key-item-req.php');
			include(TEMPLATEPATH . '/component-quest-achievement-reqs.php');
			?>
		</div></div>

		<!-- ═══ ADVANCED ═══ -->
		<div class="br-scroll-section" id="advanced-options"><div class="br-panel br-panel-narrow">
			<?php
			include(TEMPLATEPATH . '/component-quest-additional-mechs.php');
			include(TEMPLATEPATH . '/component-quest-item-reward.php');
			include(TEMPLATEPATH . '/component-quest-achievement-reward.php');
			?>
		</div></div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>">
		<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
	</a>
	<div class="br-actions">
		<?php if (isset($paths['publish'])) { ?>
		<select id="the_achievement_id" class="br-input br-select-auto">
			<option value="0" <?= !isset($quest->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
			<?php foreach ($paths['publish'] as $a) { ?>
			<option id="achievement-option-<?= $a->achievement_id; ?>" value="<?= $a->achievement_id; ?>"
					<?= (isset($quest) && $quest->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>><?= esc_html($a->achievement_name); ?></option>
			<?php } ?>
		</select>
		<?php } else { ?>
		<input id="the_achievement_id" type="hidden" value="0">
		<?php } ?>

		<select id="the_quest_status" class="br-input br-select-auto">
			<option value="publish" <?= (!$is_edit || $quest->quest_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
			<option value="draft" <?= ($is_edit && $quest->quest_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft", "bluerabbit"); ?></option>
			<option value="locked" <?= ($is_edit && $quest->quest_status == 'locked') ? 'selected' : ''; ?>><?= __("Locked", "bluerabbit"); ?></option>
			<option value="trash" <?= ($is_edit && $quest->quest_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
		</select>

		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>">
		<input type="hidden" id="delete-question-nonce" value="<?= wp_create_nonce('br_delete_question_nonce'); ?>">
		<input type="hidden" id="delete-option-nonce" value="<?= wp_create_nonce('br_delete_option_nonce'); ?>">

		<button id="submit-button" type="button" class="br-btn br-btn-green br-btn-submit" onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= $is_edit ? __("Update Mission", "bluerabbit") : __("Create Mission", "bluerabbit"); ?>
		</button>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php if ($is_edit) { ?>checkRequirements(<?= $quest->mech_level; ?>);<?php } else { ?>checkRequirements(1);<?php } ?>
});
</script>

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('.br-tabs-sticky .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('.br-tabs-sticky .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();
</script>

<?php include(get_stylesheet_directory() . '/footer.php'); ?>

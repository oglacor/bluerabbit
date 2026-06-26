<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if ($adventure && ($isGM || $isAdmin)) {
	$questID = isset($_GET['questID']) ? (int) $_GET['questID'] : null;
	if ($questID) {
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$questID AND adventure_id=$adventure_id");
		if ($quest) {
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id!=$quest->quest_id ORDER BY mech_level ASC, quest_order ASC");
		} else {
			$quest = '';
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id ORDER BY mech_level ASC, quest_order ASC");
		}
	} else {
		$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id ORDER BY mech_level ASC, quest_order ASC");
	}
	$items        = BR_Item::instance()->getItems($adventure->adventure_id);
	$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id);
	$paths        = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');

	if (isset($quest) && $quest) {
		$requirements = $wpdb->get_results("SELECT b.req_object_id, b.req_type FROM {$wpdb->prefix}br_quests a LEFT JOIN {$wpdb->prefix}br_reqs b ON a.quest_id = b.quest_id WHERE a.quest_id=$quest->quest_id AND a.quest_status='publish'");
		$reqs = [];
		foreach ($requirements as $r) {
			if ($r->req_type == 'quest') $reqs['quests'][] = $r->req_object_id;
			elseif ($r->req_type == 'item') $reqs['items'][] = $r->req_object_id;
			elseif ($r->req_type == 'achievement') $reqs['achievements'][] = $r->req_object_id;
		}
	}
	$is_edit = (isset($quest) && $quest);
	$adventures = $adventures ?? $wpdb->get_results("SELECT adventure_id, adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_status='publish'");
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(33,150,243,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(33,150,243,0.4)">
			<span class="icon icon-quest" style="font-size:28px;color:#2196f3"></span>
		</div>
		<div>
			<h1 class="br-page-title" id="quest-title-label"><?= $is_edit ? __("Edit Milestone", "bluerabbit") . ' &rsaquo; ' . esc_html($quest->quest_title) : __("New Milestone", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_type" value="quest">
		<input type="hidden" id="the_quest_id" value="<?= $is_edit ? $quest->quest_id : ''; ?>">
		<input type="hidden" id="the_quest_order" value="<?= $is_edit ? $quest->quest_order : count($quests ?? []); ?>">
		<?php if ($is_edit) { ?>
		<a class="br-btn" href="<?= get_bloginfo('url') . "/quest/?questID=$quest->quest_id&adventure_id=$quest->adventure_id"; ?>" target="_blank" style="margin-left:auto">
			<span class="icon icon-view"></span> <?= __("View Milestone", "bluerabbit"); ?>
		</a>
		<?php } ?>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="main-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this)">
			<span class="icon icon-quest"></span> <?= __("Basic", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('mechanics', this)">
			<span class="icon icon-tools"></span> <?= __("Mechanics", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('content', this)">
			<span class="icon icon-document"></span> <?= __("Content", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('boss-fight-steps', this)">
			<span class="icon icon-level"></span> <?= __("Steps", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('advanced-options', this)">
			<span class="icon icon-config"></span> <?= __("Advanced", "bluerabbit"); ?>
		</button>
	</div>

		<!-- ═══ BASIC SETTINGS ═══ -->
		<div class="br-scroll-section" id="general">
		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-quest"></span> <?= __("Basic Settings", "bluerabbit"); ?></h3>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Name", "bluerabbit"); ?></label>
				<input class="br-input br-input-lg" type="text" id="the_quest_title"
					   placeholder="<?= __('Milestone Title', 'bluerabbit'); ?>"
					   value="<?= $is_edit ? esc_attr($quest->quest_title) : ''; ?>"
					   onChange="$('#quest-title-label').text('<?= __("Edit Milestone", "bluerabbit"); ?> › '+$('#the_quest_title').val());">
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Autoload?", "bluerabbit"); ?></label>
				<select class="br-input" id="the_quest_relevance">
					<option value="0" <?= ($is_edit && $quest->quest_relevance != 'autoload') ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="autoload" <?= ($is_edit && $quest->quest_relevance == 'autoload') ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>

			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
					<div class="br-form-component" id="tutorial-color-select">
						<?php $selected_color = ($is_edit && $quest->quest_color) ? $quest->quest_color : 'red'; ?>
						<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
						<?php $color_select_id = "#the_quest_color"; include(TEMPLATEPATH . '/color-select.php'); ?>
					</div>
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Icon", "bluerabbit"); ?></label>
					<div class="br-form-component" id="tutorial-icon-select">
						<?php $selected_icon = ($is_edit && $quest->quest_icon) ? $quest->quest_icon : 'challenge'; ?>
						<input id="the_quest_icon" class="icon-selected" type="hidden" value="<?= $selected_icon; ?>">
						<?php include(TEMPLATEPATH . '/icon-select.php'); ?>
					</div>
				</div>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Main Image", "bluerabbit"); ?> <span style="color:#f44336;font-size:10px;letter-spacing:0">*<?= __("Required", "bluerabbit"); ?></span></label>
				<div class="br-form-component">
					<div class="br-gallery br-gallery-single">
						<?php $thumb_id = 'the_quest_badge'; $file = $is_edit ? $quest->mech_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
					</div>
				</div>
			</div>
		</div>
		</div>

		<!-- ═══ MECHANICS ═══ -->
		<div class="br-scroll-section" id="mechanics">
		<div class="br-panel">
			<?php include(get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>

			<h3 class="br-panel-title" style="margin-top:24px"><span class="icon icon-quest"></span> <?= __("Milestone Mechanics", "bluerabbit"); ?></h3>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Required Words", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="1" id="the_quest_min_words"
						   value="<?= isset($quest->mech_min_words) ? $quest->mech_min_words : 1; ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Required Links", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="0" id="the_quest_min_links"
						   value="<?= isset($quest->mech_min_links) ? $quest->mech_min_links : 0; ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Required Images", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="0" id="the_quest_min_images"
						   value="<?= isset($quest->mech_min_images) ? $quest->mech_min_images : 0; ?>">
				</div>
			</div>
		</div>
		</div>

		<!-- ═══ CONTENT ═══ -->
		<div class="br-scroll-section" id="content">
		<div class="br-panel">
			<?php include(get_stylesheet_directory() . '/component-quest-content.php'); ?>
		</div>
		</div>

		<!-- ═══ STEPS ═══ -->
		<div class="br-scroll-section" id="boss-fight-steps">
		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-progression"></span> <?= __("Milestone Steps", "bluerabbit"); ?></h3>
			<span class="br-form-hint" style="display:block;margin:-12px 0 16px"><?= __("All the steps the player must do to complete the milestone", "bluerabbit"); ?></span>

			<?php if ($is_edit) { ?>
			<?php $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest->quest_id AND adventure_id=$quest->adventure_id AND step_status='publish' ORDER BY step_order, step_id"); ?>
			<?php if (!$steps) { ?>
				<p id="no-steps-label" style="text-align:center;color:rgba(255,255,255,0.35);padding:16px">— <?= __("No steps", "bluerabbit"); ?> —</p>
			<?php } ?>

			<div class="br-step-list" id="steps-list">
				<?php foreach ($steps as $key => $step) { ?>
					<?php include(TEMPLATEPATH . '/step-list-item.php'); ?>
				<?php } ?>
			</div>

			<div style="display:flex;gap:8px;margin-top:12px">
				<button class="br-btn" onClick="addStep();"><span class="icon icon-add"></span> <?= __("Add Step", "bluerabbit"); ?></button>
			</div>
			<?php } else { ?>
			<div class="br-empty" style="padding:24px">
				<span class="icon icon-level"></span>
				<h3><?= __("Save the milestone first", "bluerabbit"); ?></h3>
			</div>
			<?php } ?>
		</div>
		</div>

		<!-- ═══ ADVANCED ═══ -->
		<div class="br-scroll-section" id="advanced-options">
		<div class="br-panel">
			<?php
			include(TEMPLATEPATH . '/component-quest-additional-mechs.php');
			include(TEMPLATEPATH . '/component-quest-item-reward.php');
			include(TEMPLATEPATH . '/component-quest-achievement-reward.php');
			include(TEMPLATEPATH . '/component-quest-key-item-req.php');
			include(TEMPLATEPATH . '/component-quest-reqs.php');
			include(TEMPLATEPATH . '/component-quest-achievement-reqs.php');
			?>
		</div>
		</div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>">
		<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
	</a>
	<div class="br-actions">
		<?php if (isset($paths['publish'])) { ?>
		<select id="the_achievement_id" class="br-input" style="width:auto">
			<option value="0" <?= !isset($quest->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
			<?php foreach ($paths['publish'] as $a) { ?>
			<option id="achievement-option-<?= $a->achievement_id; ?>" value="<?= $a->achievement_id; ?>"
					<?= (isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>><?= esc_html($a->achievement_name); ?></option>
			<?php } ?>
		</select>
		<?php } else { ?>
		<input id="the_achievement_id" type="hidden" value="0">
		<?php } ?>

		<select id="the_quest_status" class="br-input" style="width:auto">
			<option value="publish" <?= (!$is_edit || $quest->quest_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
			<option value="draft" <?= ($is_edit && $quest->quest_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft", "bluerabbit"); ?></option>
			<option value="locked" <?= ($is_edit && $quest->quest_status == 'locked') ? 'selected' : ''; ?>><?= __("Locked", "bluerabbit"); ?></option>
			<option value="trash" <?= ($is_edit && $quest->quest_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
		</select>

		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>">
		<input type="hidden" id="delete-question-nonce" value="<?= wp_create_nonce('br_delete_question_nonce'); ?>">
		<input type="hidden" id="delete-option-nonce" value="<?= wp_create_nonce('br_delete_option_nonce'); ?>">

		<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= $is_edit ? __("Update Milestone", "bluerabbit") : __("Create Milestone", "bluerabbit"); ?>
		</button>

		<?php if ($is_edit) { ?>
		<button class="br-btn" onClick="showOverlay('#list-of-adventures');"><span class="icon icon-infinite"></span> <?= __("Duplicate", "bluerabbit"); ?></button>
		<div class="confirm-action overlay-layer red-bg-400" id="list-of-adventures">
			<span class="line font _14 w900 white-color"><?= __("Select destination", "bluerabbit"); ?></span>
			<select class="form-ui" id="adventure_target">
				<?php foreach ($adventures as $c) { ?>
				<option value="<?= $c->adventure_id; ?>"><?= $c->adventure_id == $adventure->adventure_id ? __("Same adventure", "bluerabbit") : esc_html($c->adventure_title); ?></option>
				<?php } ?>
			</select><br>
			<button class="form-ui red-A400 white-bg" onClick="duplicateQuest(<?= $quest->quest_id; ?>);"><span class="icon icon-infinite"></span> <?= __("Duplicate", "bluerabbit"); ?></button>
			<button class="form-ui grey-bg-600 white-color" onClick="hideAllOverlay();"><span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?></button>
			<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>">
		</div>
		<?php } ?>
	</div>
</div>

<?php if ($is_edit && isset($quest->quest_qr_token) && $quest->quest_qr_token) { ?>
<style>.br-form-bottom-bar::after{content:'';display:none}</style>
<?php
$qr_url = get_bloginfo('url') . "/quest-qr/?token=" . $quest->quest_qr_token;
$qr_dir = WP_CONTENT_DIR . '/uploads/br-quest-qr/';
$qr_url_base = content_url('uploads/br-quest-qr/');
if (!file_exists($qr_dir)) wp_mkdir_p($qr_dir);
$qr_filename = 'quest-' . $quest->quest_id . '.png';
$qr_file_path = $qr_dir . $qr_filename;
$qr_file_url = $qr_url_base . $qr_filename;
if (!file_exists($qr_file_path)) {
	require_once(get_template_directory() . "/libs/phpqrcode/qrlib.php");
	QRcode::png($qr_url, $qr_file_path, QR_ECLEVEL_M, 6);
}
?>
<?php } ?>

<?php
$steps_json = [];
if (isset($steps)) { foreach ($steps as $s) { $steps_json[] = ['text' => $s->step_title, 'value' => $s->step_id]; } }
$items_json = [];
if (isset($items['key'])) { foreach ($items['key'] as $i) { $items_json[] = ['text' => $i->item_name, 'value' => $i->item_id]; } }
?>
<input type="hidden" id="steps-list-values" value='<?= json_encode($steps_json); ?>'>
<input type="hidden" id="key-items-for-opened-backpack" value='<?= json_encode($items_json); ?>'>
<script>
jQuery(document).ready(function($) {
	<?php if ($is_edit) { ?>checkRequirements(<?= $quest->mech_level; ?>);<?php } else { ?>checkRequirements(1);<?php } ?>
	$(document).keyup(function(e) { if (e.ctrlKey && e.keyCode == 13) updateQuest(); });
	brInitStepSortable();
});
function brScrollTo(id, btn) {
	document.querySelectorAll('#main-tabs-buttons .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('#main-tabs-buttons .br-tab-btn');
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

<?php } else { ?>
<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php } ?>
<?php include(get_stylesheet_directory() . '/footer.php'); ?>

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$questID = isset($_GET['questID']) ? (int) $_GET['questID'] : null;
if ($questID) {
	$quest = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$questID AND quest_type='lore'");
}
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, "path");
$is_edit = ($adventure && isset($quest));
?>

<div class="br-page" style="max-width:900px">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(159,64,226,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(159,64,226,0.4)">
			<span class="icon icon-narrative" style="font-size:28px;color:#9f40e2"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Resource", "bluerabbit") : __("New Resource", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_id" value="<?= isset($quest) ? $quest->quest_id : ''; ?>">
		<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : ''; ?>">
		<input type="hidden" id="the_quest_type" value="lore">
	</div>

	<!-- Form -->
	<div class="br-panel">

		<!-- Resource Name -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Resource Name", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_quest_title"
				   value="<?= isset($quest) ? esc_attr($quest->quest_title) : ''; ?>"
				   placeholder="<?= __('Enter resource name', 'bluerabbit'); ?>">
		</div>

		<!-- Style + Level row -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Style", "bluerabbit"); ?></label>
				<select class="br-input" id="the_quest_style">
					<option value="resource" <?= !isset($quest->quest_style) || $quest->quest_style == 'resource' ? 'selected' : ''; ?>><?= __("Resource", "bluerabbit"); ?></option>
					<option value="article" <?= isset($quest->quest_style) && $quest->quest_style == 'article' ? 'selected' : ''; ?>><?= __("Article", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Level", "bluerabbit"); ?></label>
				<input class="br-input" type="number" max="99" min="1" id="the_quest_level"
					   value="<?= isset($quest->mech_level) ? $quest->mech_level : 1; ?>">
			</div>
		</div>

		<!-- File or Link -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("File or Link", "bluerabbit"); ?></label>
			<div class="br-input-row">
				<input class="br-input" id="the_quest_secondary_headline"
					   value="<?= isset($quest->quest_secondary_headline) ? esc_attr($quest->quest_secondary_headline) : ''; ?>"
					   placeholder="<?= __('Paste a URL or select a file', 'bluerabbit'); ?>">
				<button class="br-btn br-btn-amber" onClick="showWPUpload('the_quest_secondary_headline');">
					<span class="icon icon-image"></span> <?= __("Select file", "bluerabbit"); ?>
				</button>
			</div>
		</div>

		<!-- Content -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Content", "bluerabbit"); ?></label>
			<?php
			$wp_editor_settings = ($roles[0] == "administrator")
				? ['quicktags' => true, 'editor_height' => 350]
				: ['quicktags' => false, 'editor_height' => 350];
			wp_editor(isset($quest->quest_content) ? $quest->quest_content : '', 'the_quest_content', $wp_editor_settings);
			?>
		</div>

		<!-- Image -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Image", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<?php $selected_book = isset($quest->mech_badge) ? $quest->mech_badge : ''; ?>
				<input id="the_quest_badge" type="hidden" value="<?= $selected_book; ?>">
				<?php include(TEMPLATEPATH . '/component-book-select.php'); ?>
			</div>
		</div>

		<!-- Color -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : ''; ?>
				<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
				<?php
				$color_select_id = '#the_quest_color';
				include(TEMPLATEPATH . '/color-select.php');
				?>
			</div>
		</div>

		<!-- Available for -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Available for", "bluerabbit"); ?></label>
			<select id="the_achievement_id" class="br-input" onChange="hideAchievementReward();">
				<option value="0" <?= !isset($quest->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
				<?php if (isset($achievements['publish'])) { ?>
					<?php foreach ($achievements['publish'] as $a) { ?>
					<option id="achievement-option-<?= $a->achievement_id; ?>"
							value="<?= $a->achievement_id; ?>"
							<?= (isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>>
						<?= esc_html($a->achievement_name); ?>
					</option>
					<?php } ?>
				<?php } ?>
			</select>
		</div>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>">
			<input type="hidden" id="the_quest_status" value="publish">
			<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateQuest();">
				<span class="icon icon-check"></span>
				<?= $is_edit ? __("Update Resource", "bluerabbit") : __("Publish Resource", "bluerabbit"); ?>
			</button>
		</div>

	</div>

</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

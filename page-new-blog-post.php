<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$questID = isset($_GET['questID']) ? (int) $_GET['questID'] : null;
if ($questID) {
	$quest = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$questID");
}
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, "path");
$is_edit = (isset($adventure) && isset($quest));
?>

<div class="br-page" style="max-width:900px">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(159,64,226,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(159,64,226,0.4)">
			<span class="icon icon-story" style="font-size:28px;color:#9f40e2"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Post", "bluerabbit") : __("New Post", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_id" value="<?= isset($quest) ? $quest->quest_id : ''; ?>">
		<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : ''; ?>">
		<input type="hidden" id="the_quest_type" value="blog-post">
	</div>

	<!-- Form -->
	<div class="br-panel">

		<!-- Headline -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Post Headline", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_quest_title"
				   value="<?= isset($quest) ? esc_attr($quest->quest_title) : ''; ?>"
				   placeholder="<?= __('Enter post headline', 'bluerabbit'); ?>">
		</div>

		<!-- Image -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Post Image", "bluerabbit"); ?> <span style="color:#f44336;font-size:10px;letter-spacing:0">*<?= __("Required", "bluerabbit"); ?></span></label>
			<div class="br-form-component">
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= isset($quest->mech_badge) ? $quest->mech_badge : ''; ?>);" onClick="showWPUpload('the_quest_badge');" id="the_quest_badge_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40 green-bg-400" onClick="showWPUpload('the_quest_badge');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40 red-bg-400" onClick="clearImage('#the_quest_badge');"><span class="icon icon-trash"></span></button>
							<input type="hidden" id="the_quest_badge" value="<?= isset($quest->mech_badge) ? $quest->mech_badge : ''; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Level + Style + Start Date -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Level", "bluerabbit"); ?></label>
				<input class="br-input" type="number" max="99" min="1" id="the_quest_level"
					   value="<?= isset($quest->mech_level) ? $quest->mech_level : 1; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Display Style", "bluerabbit"); ?></label>
				<select id="the_quest_style" class="br-input">
					<option value="text-right" <?= (!isset($quest->quest_style) || $quest->quest_style == 'text-right') ? 'selected' : ''; ?>><?= __("Text on Right", "bluerabbit"); ?></option>
					<option value="text-left" <?= (isset($quest->quest_style) && $quest->quest_style == 'text-left') ? 'selected' : ''; ?>><?= __("Text on Left", "bluerabbit"); ?></option>
					<option value="news-highlight" <?= (isset($quest->quest_style) && $quest->quest_style == 'news-highlight') ? 'selected' : ''; ?>><?= __("News Highlight", "bluerabbit"); ?></option>
					<option value="headline" <?= (isset($quest->quest_style) && $quest->quest_style == 'headline') ? 'selected' : ''; ?>><?= __("Headline", "bluerabbit"); ?></option>
				</select>
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Start Date", "bluerabbit"); ?></label>
				<?php
				$pretty_start_date = (isset($quest) && $quest->mech_start_date != '0000-00-00 00:00:00')
					? date('Y/m/d H:i', strtotime($quest->mech_start_date)) : '';
				?>
				<input class="br-input the_start_date datetimepicker" autocomplete="off" id="the_quest_start_date"
					   value="<?= $pretty_start_date; ?>" placeholder="<?= __('Select date', 'bluerabbit'); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Available for", "bluerabbit"); ?></label>
				<select id="the_achievement_id" class="br-input" onChange="hideAchievementReward();">
					<option value="0" <?= !isset($quest->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
					<?php if (isset($achievements['publish'])) { ?>
						<?php foreach ($achievements['publish'] as $a) { ?>
						<option id="achievement-option-<?= $a->achievement_id; ?>" value="<?= $a->achievement_id; ?>"
								<?= (isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>>
							<?= esc_html($a->achievement_name); ?>
						</option>
						<?php } ?>
					<?php } ?>
				</select>
			</div>
		</div>

		<!-- Secondary Headline -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Secondary Headline", "bluerabbit"); ?></label>
			<textarea class="br-input" rows="3" maxlength="200" id="the_quest_secondary_headline" placeholder="<?= __('Short description or subtitle', 'bluerabbit'); ?>"><?= isset($quest->quest_secondary_headline) ? esc_textarea($quest->quest_secondary_headline) : ''; ?></textarea>
		</div>

		<!-- Content -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Content", "bluerabbit"); ?></label>
			<?php
			$wp_editor_settings = ['editor_height' => 350];
			wp_editor(isset($quest->quest_content) ? $quest->quest_content : '', 'the_quest_content', $wp_editor_settings);
			?>
		</div>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<div class="br-actions">
				<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>">
				<select id="the_quest_status" class="br-input" style="width:auto">
					<option value="publish" <?= (!isset($quest) || $quest->quest_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
					<option value="draft" <?= (isset($quest) && $quest->quest_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft", "bluerabbit"); ?></option>
					<option value="trash" <?= (isset($quest) && $quest->quest_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
				</select>
				<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateQuest();">
					<span class="icon icon-check"></span>
					<?= $is_edit ? __("Update Post", "bluerabbit") : __("Create Post", "bluerabbit"); ?>
				</button>
			</div>
		</div>

	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

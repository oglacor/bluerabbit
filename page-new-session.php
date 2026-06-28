<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$session_id = isset($_GET['session_id']) ? (int) $_GET['session_id'] : null;
if ($session_id) {
	$session = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_sessions WHERE session_id=$session_id");
}
$quests   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND (quest_type='quest' OR quest_type='challenge') AND quest_status='publish'");
$speakers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_speakers WHERE adventure_id=$adventure_id AND speaker_status='publish' ORDER BY speaker_first_name");
$speaker_ids = (!empty($session->speaker_ids)) ? explode(',', $session->speaker_ids) : [];
$paths  = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');
$guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_name ASC");
$is_edit = ($adventure && isset($session) && $session);
?>

<div class="br-page br-page-narrow">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-brown">
			<span class="icon icon-calendar br-icon-lg br-icon-brown"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Session", "bluerabbit") : __("New Session", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_session_id" value="<?= isset($session) ? $session->session_id : ''; ?>">
	</div>

	<!-- Form -->
	<div class="br-panel">

		<!-- Title -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Title", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_session_title"
				   value="<?= isset($session) ? esc_attr($session->session_title) : ''; ?>"
				   placeholder="<?= __('Session title', 'bluerabbit'); ?>">
		</div>

		<!-- Description -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Session Description", "bluerabbit"); ?></label>
			<?php
			$wp_editor_settings = ($roles[0] == 'administrator')
				? ['quicktags' => true, 'editor_height' => 350]
				: ['quicktags' => false, 'editor_height' => 350];
			wp_editor(isset($session->session_description) ? $session->session_description : '', 'the_session_description', $wp_editor_settings);
			?>
		</div>

		<!-- Start + End -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Session Start", "bluerabbit"); ?></label>
				<?php
				$pretty_start = (isset($session->session_start) && $session->session_start != '0000-00-00 00:00:00')
					? date('Y/m/d H:i', strtotime($session->session_start)) : '';
				?>
				<input class="br-input datetimepicker" autocomplete="off" id="the_session_start"
					   value="<?= $pretty_start; ?>" placeholder="<?= __('Select start date', 'bluerabbit'); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Session End", "bluerabbit"); ?></label>
				<?php
				$pretty_end = (isset($session->session_end) && $session->session_end != '0000-00-00 00:00:00')
					? date('Y/m/d H:i', strtotime($session->session_end)) : '';
				?>
				<input class="br-input datetimepicker" autocomplete="off" id="the_session_end"
					   value="<?= $pretty_end; ?>" placeholder="<?= __('Select end date', 'bluerabbit'); ?>">
			</div>
		</div>

		<!-- Room + Quest -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Room Information", "bluerabbit"); ?></label>
				<input class="br-input" type="text" id="the_session_room"
					   value="<?= isset($session->session_room) ? esc_attr($session->session_room) : ''; ?>"
					   placeholder="<?= __('Room name or number', 'bluerabbit'); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Attach to Quest", "bluerabbit"); ?></label>
				<select id="the_quest_id" class="br-input">
					<option value="0"><?= __("No Quest Attached", "bluerabbit"); ?></option>
					<?php if (isset($quests)) { foreach ($quests as $q) { ?>
					<option value="<?= $q->quest_id; ?>" <?= (isset($session->quest_id) && $session->quest_id == $q->quest_id) ? 'selected' : ''; ?>>
						[<?= strtoupper($q->quest_type[0]); ?>] <?= esc_html($q->quest_title); ?>
					</option>
					<?php } } ?>
				</select>
			</div>
		</div>

		<!-- Path + Guild -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Available for", "bluerabbit"); ?></label>
				<?php if (isset($paths['publish'])) { ?>
				<select id="the_achievement_id" class="br-input">
					<option value="0" <?= !isset($session->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
					<?php foreach ($paths['publish'] as $a) { ?>
					<option value="<?= $a->achievement_id; ?>" <?= (isset($session) && $session->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>>
						<?= esc_html($a->achievement_name); ?>
					</option>
					<?php } ?>
				</select>
				<?php } else { ?>
				<input id="the_achievement_id" type="hidden" value="0">
				<input class="br-input" value="<?= __('All Paths', 'bluerabbit'); ?>" disabled>
				<?php } ?>
			</div>
			<?php if ($guilds) { ?>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Guild", "bluerabbit"); ?></label>
				<select id="the_guild_id" class="br-input">
					<option value="0" <?= !isset($session->guild_id) ? 'selected' : ''; ?>><?= __("All guilds", "bluerabbit"); ?></option>
					<?php foreach ($guilds as $t) { ?>
					<option value="<?= $t->guild_id; ?>" <?= (isset($session->guild_id) && $session->guild_id == $t->guild_id) ? 'selected' : ''; ?>>
						<?= esc_html($t->guild_name); ?>
					</option>
					<?php } ?>
				</select>
			</div>
			<?php } ?>
		</div>

		<!-- Speakers -->
		<?php if (!empty($speakers)) { ?>
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Speakers", "bluerabbit"); ?></label>
			<div class="br-form-component br-speaker-grid">
				<?php foreach ($speakers as $s) { ?>
				<label class="br-btn br-speaker-label<?= in_array($s->speaker_id, $speaker_ids) ? ' active' : ''; ?>">
					<input type="checkbox" class="speaker_ids br-initially-hidden" name="speaker_ids" value="<?= $s->speaker_id; ?>"
						   <?= in_array($s->speaker_id, $speaker_ids) ? 'checked' : ''; ?>>
					<?= esc_html("$s->speaker_first_name $s->speaker_last_name"); ?>
				</label>
				<?php } ?>
			</div>
		</div>
		<?php } ?>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure->adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<div class="br-actions">
				<input type="hidden" id="session_nonce" value="<?= wp_create_nonce('br_session_nonce'); ?>">
				<select id="the_session_status" class="br-input br-select-auto">
					<option value="publish"><?= __("Publish", "bluerabbit"); ?></option>
					<option value="draft"><?= __("Draft", "bluerabbit"); ?></option>
					<option value="trash"><?= __("Trash", "bluerabbit"); ?></option>
				</select>
				<button id="submit-button" type="button" class="br-btn br-btn-green br-btn-submit" onClick="updateSession();">
					<span class="icon icon-check"></span>
					<?= $is_edit ? __("Update Session", "bluerabbit") : __("Create Session", "bluerabbit"); ?>
				</button>
			</div>
		</div>

	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

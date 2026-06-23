<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$speaker = isset($_GET['speaker_id']) ? $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id = %d", (int) $_GET['speaker_id']
)) : null;
$is_edit = ($adventure && $speaker);
$the_roles    = ['br_npc', 'br_game_master', 'administrator'];
$speakerUsers = get_users(['role__in' => $the_roles]);
?>

<div class="br-page" style="max-width:900px">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(121,85,72,0.25);display:flex;align-items:center;justify-content:center;border-color:rgba(121,85,72,0.5)">
			<span class="icon icon-socialiser" style="font-size:28px;color:#8d6e63"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Speaker", "bluerabbit") : __("New Speaker", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_speaker_id" value="<?= $speaker ? $speaker->speaker_id : ''; ?>">
	</div>

	<!-- Form -->
	<div class="br-panel">

		<!-- Connect to Player -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Connect to Player", "bluerabbit"); ?></label>
			<select id="the_speaker_player_id" class="br-input" onChange="updateSpeaker();">
				<option value="0" <?= (!$speaker || !$speaker->player_id) ? 'selected' : ''; ?>><?= __("None", "bluerabbit"); ?></option>
				<?php foreach ($speakerUsers as $at) { ?>
				<option value="<?= $at->ID; ?>" <?= ($speaker && $at->ID == $speaker->player_id) ? 'selected' : ''; ?>>
					<?= esc_html($at->display_name ?: $at->user_email); ?>
					<?php
					if ($at->roles[0] == 'administrator') echo ' | Admin';
					elseif ($at->roles[0] == 'br_game_master') echo ' | GM';
					elseif ($at->roles[0] == 'br_npc') echo ' | NPC';
					?>
				</option>
				<?php } ?>
			</select>
		</div>

		<!-- Speaker Picture -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Speaker Picture", "bluerabbit"); ?> <span style="color:#f44336;font-size:10px;letter-spacing:0">*<?= __("Required", "bluerabbit"); ?></span></label>
			<div class="br-form-component">
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background speaker-editable" style="background-image: url(<?= $speaker ? $speaker->speaker_picture : ''; ?>);" onClick="showWPUpload('the_speaker_picture');" id="the_speaker_picture_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40 green-bg-400 speaker-editable" onClick="showWPUpload('the_speaker_picture');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40 red-bg-400 speaker-editable" onClick="clearImage('#the_speaker_picture');"><span class="icon icon-trash"></span></button>
							<input type="hidden" id="the_speaker_picture" value="<?= $speaker ? $speaker->speaker_picture : ''; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- First + Last name -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("First Name", "bluerabbit"); ?></label>
				<input class="br-input br-input-lg speaker-editable" type="text" id="the_speaker_first_name"
					   value="<?= $speaker ? esc_attr($speaker->speaker_first_name) : ''; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Last Name", "bluerabbit"); ?></label>
				<input class="br-input br-input-lg speaker-editable" type="text" id="the_speaker_last_name"
					   value="<?= $speaker ? esc_attr($speaker->speaker_last_name) : ''; ?>">
			</div>
		</div>

		<!-- Company + Website -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Company", "bluerabbit"); ?></label>
				<input class="br-input speaker-editable" type="text" id="the_speaker_company"
					   value="<?= $speaker ? esc_attr($speaker->speaker_company) : ''; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Website", "bluerabbit"); ?></label>
				<input class="br-input speaker-editable" type="text" id="the_speaker_website"
					   value="<?= $speaker ? esc_attr($speaker->speaker_website) : ''; ?>" placeholder="https://">
			</div>
		</div>

		<!-- LinkedIn -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("LinkedIn", "bluerabbit"); ?></label>
			<input class="br-input speaker-editable" type="text" id="the_speaker_linkedin"
				   value="<?= $speaker ? esc_attr($speaker->speaker_linkedin) : ''; ?>" placeholder="https://linkedin.com/in/">
		</div>

		<!-- Bio -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Speaker Bio", "bluerabbit"); ?></label>
			<?php
			$wp_editor_settings = ($roles[0] == 'administrator')
				? ['quicktags' => true, 'editor_height' => 350]
				: ['quicktags' => false, 'editor_height' => 350];
			wp_editor($speaker ? $speaker->speaker_bio : '', 'the_speaker_bio', $wp_editor_settings);
			?>
		</div>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure->adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<input type="hidden" id="speaker_nonce" value="<?= wp_create_nonce('br_speaker_nonce'); ?>">
			<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateSpeaker();">
				<span class="icon icon-check"></span>
				<?= $is_edit ? __("Update Speaker", "bluerabbit") : __("Create Speaker", "bluerabbit"); ?>
			</button>
		</div>

	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

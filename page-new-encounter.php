<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$enc_id = isset($_GET['enc_id']) ? (int) $_GET['enc_id'] : null;
if ($enc_id) {
	$encounter = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}br_encounters WHERE adventure_id = %d AND enc_id = %d",
		$adventure->adventure_id, $enc_id
	));
}
$paths = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');
$is_edit = ($adventure && isset($encounter) && $encounter);
?>

<div class="br-page" style="max-width:900px">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(0,188,212,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(0,188,212,0.4)">
			<span class="icon icon-run" style="font-size:28px;color:#00bcd4"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Encounter", "bluerabbit") : __("New Encounter", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_enc_id" value="<?= isset($encounter) ? $encounter->enc_id : ''; ?>">
	</div>

	<!-- Form -->
	<div class="br-panel">

		<!-- Level + Path -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Encounter Level", "bluerabbit"); ?></label>
				<input class="br-input" type="number" max="99" min="1" id="the_enc_level"
					   value="<?= isset($encounter->enc_level) ? $encounter->enc_level : 1; ?>"
					   onBlur="checkLevel('#the_enc_level');" onChange="checkLevel('#the_enc_level');">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Path Available", "bluerabbit"); ?></label>
				<select id="the_enc_achievement_id" class="br-input">
					<option value="0" <?= !isset($encounter->achievement_id) ? 'selected' : ''; ?>><?= __("All paths", "bluerabbit"); ?></option>
					<?php if (isset($paths['publish'])) { foreach ($paths['publish'] as $a) {
						if ($a->achievement_display != 'path') continue;
					?>
					<option value="<?= $a->achievement_id; ?>" <?= (isset($encounter->achievement_id) && $encounter->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>>
						<?= esc_html($a->achievement_name); ?>
					</option>
					<?php } } ?>
				</select>
			</div>
		</div>

		<!-- Question -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Question", "bluerabbit"); ?></label>
			<textarea class="br-input" rows="4" id="the_enc_question"
					  placeholder="<?= __('Type your question', 'bluerabbit'); ?>"><?= isset($encounter->enc_question) ? esc_textarea($encounter->enc_question) : ''; ?></textarea>
		</div>

		<!-- Correct Option -->
		<div class="br-form-group">
			<label class="br-form-label" style="color:#24da98"><span class="icon icon-check"></span> <?= __("Correct Option", "bluerabbit"); ?></label>
			<textarea class="br-input" rows="3" id="the_enc_correct" style="border-color:rgba(36,218,152,0.25)"
					  placeholder="<?= __('Correct answer', 'bluerabbit'); ?>"><?= isset($encounter->enc_right_option) ? esc_textarea($encounter->enc_right_option) : ''; ?></textarea>
		</div>

		<!-- Decoy Options -->
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label" style="color:#f44336"><span class="icon icon-cancel"></span> <?= __("Decoy Option 1", "bluerabbit"); ?></label>
				<textarea class="br-input" rows="3" id="the_enc_decoy1" style="border-color:rgba(244,67,54,0.2)"
						  placeholder="<?= __('Wrong answer', 'bluerabbit'); ?>"><?= isset($encounter->enc_decoy_option1) ? esc_textarea($encounter->enc_decoy_option1) : ''; ?></textarea>
			</div>
			<div class="br-form-group">
				<label class="br-form-label" style="color:#f44336"><span class="icon icon-cancel"></span> <?= __("Decoy Option 2", "bluerabbit"); ?></label>
				<textarea class="br-input" rows="3" id="the_enc_decoy2" style="border-color:rgba(244,67,54,0.2)"
						  placeholder="<?= __('Wrong answer', 'bluerabbit'); ?>"><?= isset($encounter->enc_decoy_option2) ? esc_textarea($encounter->enc_decoy_option2) : ''; ?></textarea>
			</div>
		</div>

		<!-- Image -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Image", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= isset($encounter->enc_badge) ? $encounter->enc_badge : ''; ?>);" onClick="showWPUpload('the_enc_badge');" id="the_enc_badge_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40 green-bg-400" onClick="showWPUpload('the_enc_badge');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40 red-bg-400" onClick="clearImage('#the_enc_badge');"><span class="icon icon-trash"></span></button>
							<input type="hidden" id="the_enc_badge" value="<?= isset($encounter->enc_badge) ? $encounter->enc_badge : ''; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- EP / XP / BLOO -->
		<div class="br-form-grid" style="grid-template-columns:1fr 1fr 1fr">
			<div class="br-form-group">
				<label class="br-form-label"><?= $ep_label; ?></label>
				<input class="br-input" type="number" id="the_enc_ep"
					   value="<?= isset($encounter->enc_ep) ? $encounter->enc_ep : 10; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= $xp_label; ?></label>
				<input class="br-input" type="number" id="the_enc_xp"
					   value="<?= isset($encounter->enc_xp) ? $encounter->enc_xp : 0; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= $bloo_label; ?></label>
				<input class="br-input" type="number" id="the_enc_bloo"
					   value="<?= isset($encounter->enc_bloo) ? $encounter->enc_bloo : 0; ?>">
			</div>
		</div>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure->adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<div class="br-actions">
				<input type="hidden" id="new-encounter-nonce" value="<?= wp_create_nonce('br_update_encounter_nonce'); ?>">
				<select id="the_enc_status" class="br-input" style="width:auto">
					<option value="publish" <?= (!isset($encounter) || !isset($encounter->enc_status) || $encounter->enc_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
					<option value="trash" <?= (isset($encounter) && isset($encounter->enc_status) && $encounter->enc_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
				</select>
				<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateEncounter();">
					<span class="icon icon-check"></span>
					<?= $is_edit ? __("Update Encounter", "bluerabbit") : __("Create Encounter", "bluerabbit"); ?>
				</button>
			</div>
		</div>

	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php $step_editor_id = "step-content-" . $s->step_id; ?>
<div class="br-step-form" id="step-form-<?= $s->step_id; ?>">
	<input type="hidden" class="step-id-value" id="step-id" value="<?= $s->step_id; ?>">
	<input type="hidden" value="<?= $s->step_order; ?>" class="step-order">

	<!-- Accordion Header -->
	<div class="br-step-form-header">
		<span class="icon icon-edit" style="font-size:18px;color:var(--step-color, #1cc2eb)"></span>
		<div style="flex:1">
			<span style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:20px;font-weight:900;text-transform:uppercase;letter-spacing:1px"><?= __("Edit Step", "bluerabbit"); ?></span>
			<span style="font-size:12px;color:rgba(255,255,255,0.5);margin-left:8px">[<?= $s->step_order; ?>] <?= esc_html($s->step_title); ?></span>
		</div>
		<button class="br-btn" style="padding:6px 10px" onClick="brStartStepFormTour();" title="<?= __('Tutorial', 'bluerabbit'); ?>"><span class="icon icon-question"></span></button>
		<button class="br-btn br-btn-red" style="padding:6px 10px" onClick="closeStepAccordion(<?= $s->step_id; ?>);"><span class="icon icon-cancel"></span></button>
	</div>

	<div style="padding:20px">
		<!-- Label -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Label", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("The reference to the step, only seen in admin", "bluerabbit"); ?></span>
			<input class="br-input" id="step-title-<?= $s->step_id; ?>" type="text" maxlength="255" value="<?= esc_attr($s->step_title); ?>">
		</div>

		<!-- Type -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Type", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("The type of action the player will do", "bluerabbit"); ?></span>
			<select id="step-type-<?= $s->step_id; ?>" class="br-input step-type" onChange="checkStepType();">
				<option value="dialogue" <?= ($s->step_type == 'dialogue' || $s->step_type == 'instruction') ? 'selected' : ''; ?>><?= __("Dialogue", "bluerabbit"); ?></option>
				<option value="open" <?= $s->step_type == 'open' ? 'selected' : ''; ?>><?= __("Open Field", "bluerabbit"); ?></option>
				<option value="jump" <?= $s->step_type == 'jump' ? 'selected' : ''; ?>><?= __("Jump to step", "bluerabbit"); ?></option>
				<option value="system" <?= $s->step_type == 'system' ? 'selected' : ''; ?>><?= __("System Message", "bluerabbit"); ?></option>
				<option value="win" <?= $s->step_type == 'win' ? 'selected' : ''; ?>><?= __("Win", "bluerabbit"); ?></option>
				<option value="fail" <?= $s->step_type == 'fail' ? 'selected' : ''; ?>><?= __("Fail", "bluerabbit"); ?></option>
				<option value="video" <?= $s->step_type == 'video' ? 'selected' : ''; ?>><?= __("Video", "bluerabbit"); ?></option>
				<option value="item-grab" <?= $s->step_type == 'item-grab' ? 'selected' : ''; ?>><?= __("Find item in path", "bluerabbit"); ?></option>
				<option value="item-req" <?= $s->step_type == 'item-req' ? 'selected' : ''; ?>><?= __("Require item to advance", "bluerabbit"); ?></option>
				<option value="path-choice" <?= $s->step_type == 'path-choice' ? 'selected' : ''; ?>><?= __("Choose a path", "bluerabbit"); ?></option>
				<option value="choose-nickname" <?= $s->step_type == 'choose-nickname' ? 'selected' : ''; ?>><?= __("Choose Nickname", "bluerabbit"); ?></option>
				<option value="choose-avatar" <?= $s->step_type == 'choose-avatar' ? 'selected' : ''; ?>><?= __("Choose Avatar", "bluerabbit"); ?></option>
				<option value="scorm" <?= $s->step_type == 'scorm' ? 'selected' : ''; ?>><?= __("SCORM Package", "bluerabbit"); ?></option>
			</select>
		</div>

		<!-- Character Position (dialogue) -->
		<div class="br-form-group conditional-display dialogue-display">
			<label class="br-form-label"><?= __("Character Position", "bluerabbit"); ?></label>
			<select id="step-attach-<?= $s->step_id; ?>" class="br-input step-attach">
				<option value="none" <?= $s->step_attach == 'none' ? 'selected' : ''; ?>><?= __("No one. Just describing the scene.", "bluerabbit"); ?></option>
				<option value="left" <?= $s->step_attach == 'left' ? 'selected' : ''; ?>><?= __("On the left", "bluerabbit"); ?></option>
				<option value="right" <?= $s->step_attach == 'right' ? 'selected' : ''; ?>><?= __("On the right", "bluerabbit"); ?></option>
			</select>
		</div>

		<!-- Character Name (dialogue) -->
		<div class="br-form-group conditional-display dialogue-display">
			<label class="br-form-label"><?= __("Character Name", "bluerabbit"); ?></label>
			<input class="br-input" id="step-character-name-<?= $s->step_id; ?>" type="text" maxlength="255" value="<?= esc_attr($s->step_character_name); ?>">
		</div>

		<!-- Content / Dialogue / Question editor -->
		<div class="br-form-group conditional-display dialogue-display open-display jump-display system-display win-display fail-display item-req-display item-grab-display" id="step-content-row-<?= $s->step_id; ?>">
			<label class="br-form-label">
				<span class="conditional-display system-display"><?= __("Content", "bluerabbit"); ?></span>
				<span class="conditional-display dialogue-display"><?= __("Dialogue", "bluerabbit"); ?></span>
				<span class="conditional-display open-display"><?= __("Open Field", "bluerabbit"); ?></span>
				<span class="conditional-display choice-display jump-display"><?= __("Question", "bluerabbit"); ?></span>
				<span class="conditional-display item-grab-display"><?= __("About the item", "bluerabbit"); ?></span>
				<span class="conditional-display item-req-display"><?= __("Clue or Info", "bluerabbit"); ?></span>
				<span class="conditional-display win-display"><?= __("Win Message", "bluerabbit"); ?></span>
				<span class="conditional-display fail-display"><?= __("Fail Message", "bluerabbit"); ?></span>
			</label>
			<?php
			$wp_editor_settings = ['quicktags' => true, 'editor_height' => 250];
			wp_editor($s->step_content, $step_editor_id, $wp_editor_settings);
			?>
		</div>

		<!-- Video -->
		<div class="br-form-group conditional-display video-display">
			<label class="br-form-label"><?= __("Select the video", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<div class="gallery">
					<?php $thumb_id = 'the_step_image_' . $s->step_id; ?>
					<div class="gallery-item setting">
						<div class="background" onClick="showWPUploadVideo('<?= $thumb_id; ?>');" id="<?= $thumb_id; ?>_thumb">
							<?php $mime = wp_check_filetype($s->step_image); ?>
							<video id="<?= $thumb_id; ?>_thumb_video" class="gallery-item-video <?= strstr($mime['type'] ?? '', 'video') ? 'active' : ''; ?>" controls>
								<source src="<?= $s->step_image; ?>">
							</video>
						</div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40 green-bg-400" onClick="showWPUploadVideo('<?= $thumb_id; ?>');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40 red-bg-400" onClick="clearImage('#<?= $thumb_id; ?>');"><span class="icon icon-trash"></span></button>
							<input type="hidden" id="<?= $thumb_id; ?>" value="<?= $s->step_image; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Choose Nickname -->
		<div class="br-form-group conditional-display choose-nickname-display" style="text-align:center;padding:16px">
			<p style="color:rgba(255,255,255,0.5)"><?= __("The system prompts the player to input their First and Last names", "bluerabbit"); ?></p>
		</div>

		<!-- Choose Avatar -->
		<div class="br-form-group conditional-display choose-avatar-display" style="text-align:center;padding:16px">
			<p style="color:rgba(255,255,255,0.5)"><?= __("Upload the images of the avatars available to the players", "bluerabbit"); ?></p>
		</div>

		<!-- SCORM -->
		<div class="br-form-group conditional-display scorm-display">
			<label class="br-form-label"><?= __("SCORM Package", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Upload a SCORM 1.2 .zip file", "bluerabbit"); ?></span>
			<?php
			$scorm_settings = $s->step_settings ? json_decode($s->step_settings, true) : [];
			$scorm_url = $scorm_settings['scorm_launch_url'] ?? '';
			?>
			<div id="scorm-info-<?= $s->step_id; ?>" style="font-size:12px;color:<?= $scorm_url ? '#24da98' : 'rgba(255,255,255,0.35)'; ?>;padding:6px 0">
				<?php if ($scorm_url) { ?><span class="icon icon-check"></span> <?= esc_html($scorm_url); ?><?php } else { ?><?= __("No package uploaded yet", "bluerabbit"); ?><?php } ?>
			</div>
			<div class="br-input-row">
				<input type="file" id="scorm-zip-<?= $s->step_id; ?>" accept=".zip" class="br-input" style="padding:7px 14px">
				<button class="br-btn br-btn-green" id="scorm-upload-btn-<?= $s->step_id; ?>" onclick="brUploadScorm(<?= $s->step_id; ?>, <?= $s->adventure_id; ?>);">
					<span class="icon icon-upload"></span> <?= $scorm_url ? __("Replace", "bluerabbit") : __("Upload", "bluerabbit"); ?>
				</button>
			</div>
			<input type="hidden" id="scorm-nonce-<?= $s->step_id; ?>" value="<?= wp_create_nonce('br_scorm_upload'); ?>">
			<button class="br-btn br-btn-red" style="margin-top:8px;font-size:11px" onclick="brResetScorm(<?= (int) $s->step_id; ?>, '<?= wp_create_nonce('br_scorm_reset_all'); ?>')">
				<span class="icon icon-trash"></span> <?= __("Clear All Attempts", "bluerabbit"); ?>
			</button>
		</div>

		<!-- Path Choice -->
		<div class="br-form-group conditional-display path-choice-display">
			<label class="br-form-label"><?= __("Path Group", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("What achievement group do you want the players to choose from", "bluerabbit"); ?></span>
			<select id="the_step_achievement_group" class="br-input">
				<option value="" <?= $s->step_achievement_group == '' ? 'selected' : ''; ?>><?= __("No Group", "bluerabbit"); ?></option>
				<?php foreach (['A'=>'red','B'=>'orange','C'=>'amber','D'=>'green','E'=>'teal','F'=>'cyan','G'=>'blue','H'=>'indigo','I'=>'deep-purple','J'=>'pink'] as $g => $c) { ?>
				<option value="<?= $g; ?>" <?= $s->step_achievement_group == $g ? 'selected' : ''; ?>>Group <?= $g; ?></option>
				<?php } ?>
			</select>
		</div>

		<!-- Character Image (dialogue) -->
		<div class="br-form-group conditional-display dialogue-display">
			<label class="br-form-label"><?= __("Character Image", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Best at 9:16 proportions", "bluerabbit"); ?></span>
			<div class="br-form-component">
				<div class="gallery">
					<?php BR_Utils::instance()->insertGalleryItem('the_step_character_image', $s->step_character_image); ?>
				</div>
			</div>
		</div>

		<!-- Background -->
		<div class="br-form-group" id="step-background-row-<?= $s->step_id; ?>">
			<label class="br-form-label"><?= __("Background", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Background image of the scene", "bluerabbit"); ?></span>
			<div class="br-form-component">
				<div class="gallery">
					<?php BR_Utils::instance()->insertGalleryItem('the_step_background', $s->step_background); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Buttons container (loaded dynamically) -->
	<div class="br-step-buttons-container" id="step-buttons-form-container">
	</div>

	<!-- Update button -->
	<div class="br-step-form-footer">
		<button class="br-btn br-btn-green" style="padding:10px 32px;font-size:14px" id="step-update-button-<?= $s->step_id; ?>" onClick="updateStep(<?= $s->step_id; ?>);">
			<span class="icon icon-check"></span> <?= __("Update Step", "bluerabbit"); ?>
		</button>
	</div>
</div>

<script>
	checkStepType();
	tinyMCE.execCommand('mceAddEditor', true, '<?= $step_editor_id; ?>');
</script>
<?php include(get_stylesheet_directory() . '/tutorials/tutorial-step-form.php'); ?>

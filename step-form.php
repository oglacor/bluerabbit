<?php
$step_editor_id = "step-content-" . $s->step_id;
$sid = $s->step_id;
$settings = $s->step_settings ? json_decode($s->step_settings, true) : [];
$skin = $s->step_skin ?: $s->step_type;

// Map legacy step_type → new skin for the dropdown
$legacy_map = [
	'instruction' => 'dialogue',
	'open'        => 'open_text',
	'jump'        => 'jump_to_step',
	'item-grab'   => 'find_item',
	'item-req'    => 'backpack_item',
	'path-choice' => 'branch_choice',
	'choose-nickname' => 'choose_nickname',
	'choose-avatar'   => 'choose_avatar',
];
$skin = $legacy_map[$skin] ?? $skin;

// Category map for auto-setting step_type on save
$category_map = [
	'dialogue' => 'deliver', 'video' => 'deliver', 'audio' => 'deliver', 'gallery' => 'deliver', 'find_item' => 'deliver',
	'multiple_choice' => 'validate', 'keyphrase' => 'validate', 'cryptex' => 'validate', 'puzzle' => 'validate', 'backpack_item' => 'validate', 'scorm' => 'validate',
	'survey_choice' => 'collect', 'survey_rating' => 'collect', 'survey_poll' => 'collect', 'open_text' => 'collect', 'upload_image' => 'collect', 'upload_video' => 'collect',
	'jump_to_step' => 'flow', 'branch_choice' => 'flow',
	'system' => 'deliver', 'win' => 'flow', 'fail' => 'flow', 'choose_nickname' => 'deliver', 'choose_avatar' => 'deliver',
];

$items = BR_Item::instance()->getItems($s->adventure_id);
$achievements = BR_Achievement::instance()->getAchievements($s->adventure_id);

// Correct answers
$step_correct = $s->step_correct ? json_decode($s->step_correct, true) : [];
// Options from settings
$options = $settings['options'] ?? [];
?>
<div class="br-step-form" id="step-form-<?= $sid; ?>">
	<input type="hidden" class="step-id-value" id="step-id" value="<?= $sid; ?>">
	<input type="hidden" value="<?= $s->step_order; ?>" class="step-order">
	<input type="hidden" id="step-category-<?= $sid; ?>" value="<?= $category_map[$skin] ?? 'deliver'; ?>">

	<div class="br-step-form-header">
		<span class="icon icon-edit" style="font-size:18px;color:var(--step-color, #1cc2eb)"></span>
		<div style="flex:1">
			<span style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:20px;font-weight:900;text-transform:uppercase;letter-spacing:1px"><?= __("Edit Step", "bluerabbit"); ?></span>
			<span style="font-size:12px;color:rgba(255,255,255,0.5);margin-left:8px">[<?= $s->step_order; ?>] <?= esc_html($s->step_title); ?></span>
		</div>
		<button class="br-btn" style="padding:6px 10px" onClick="brStartStepFormTour();" title="<?= __('Tutorial', 'bluerabbit'); ?>"><span class="icon icon-question"></span></button>
		<button class="br-btn br-btn-red" style="padding:6px 10px" onClick="closeStepAccordion(<?= $sid; ?>);"><span class="icon icon-cancel"></span></button>
	</div>

	<div style="padding:20px">

		<!-- ═══ LABEL ═══ -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Label", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Admin reference only — players never see this", "bluerabbit"); ?></span>
			<input class="br-input" id="step-title-<?= $sid; ?>" type="text" maxlength="255" value="<?= esc_attr($s->step_title); ?>">
		</div>

		<!-- ═══ TYPE SELECTOR ═══ -->
		<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Step Type", "bluerabbit"); ?></label>
				<select id="step-skin-<?= $sid; ?>" class="br-input" onChange="brCheckStepSkin(<?= $sid; ?>);">
					<optgroup label="<?= __('DELIVER — Auto-complete on view', 'bluerabbit'); ?>">
						<option value="dialogue" <?= $skin == 'dialogue' ? 'selected' : ''; ?>><?= __("Dialogue", "bluerabbit"); ?></option>
						<option value="video" <?= $skin == 'video' ? 'selected' : ''; ?>><?= __("Video", "bluerabbit"); ?></option>
						<option value="audio" <?= $skin == 'audio' ? 'selected' : ''; ?>><?= __("Audio", "bluerabbit"); ?></option>
						<option value="gallery" <?= $skin == 'gallery' ? 'selected' : ''; ?>><?= __("Gallery", "bluerabbit"); ?></option>
						<option value="find_item" <?= $skin == 'find_item' ? 'selected' : ''; ?>><?= __("Find Item", "bluerabbit"); ?></option>
					</optgroup>
					<optgroup label="<?= __('VALIDATE — Player must answer correctly', 'bluerabbit'); ?>">
						<option value="multiple_choice" <?= $skin == 'multiple_choice' ? 'selected' : ''; ?>><?= __("Multiple Choice", "bluerabbit"); ?></option>
						<option value="keyphrase" <?= $skin == 'keyphrase' ? 'selected' : ''; ?>><?= __("Keyphrase", "bluerabbit"); ?></option>
						<option value="cryptex" <?= $skin == 'cryptex' ? 'selected' : ''; ?>><?= __("Cryptex", "bluerabbit"); ?></option>
						<option value="puzzle" <?= $skin == 'puzzle' ? 'selected' : ''; ?>><?= __("Puzzle", "bluerabbit"); ?></option>
						<option value="backpack_item" <?= $skin == 'backpack_item' ? 'selected' : ''; ?>><?= __("Require Backpack Item", "bluerabbit"); ?></option>
						<option value="scorm" <?= $skin == 'scorm' ? 'selected' : ''; ?>><?= __("SCORM Package", "bluerabbit"); ?></option>
					</optgroup>
					<optgroup label="<?= __('COLLECT — Player submits, no right/wrong', 'bluerabbit'); ?>">
						<option value="open_text" <?= $skin == 'open_text' ? 'selected' : ''; ?>><?= __("Open Text", "bluerabbit"); ?></option>
						<option value="survey_choice" <?= $skin == 'survey_choice' ? 'selected' : ''; ?>><?= __("Survey Choice", "bluerabbit"); ?></option>
						<option value="survey_rating" <?= $skin == 'survey_rating' ? 'selected' : ''; ?>><?= __("Rating Scale", "bluerabbit"); ?></option>
						<option value="survey_poll" <?= $skin == 'survey_poll' ? 'selected' : ''; ?>><?= __("Poll", "bluerabbit"); ?></option>
						<option value="upload_image" <?= $skin == 'upload_image' ? 'selected' : ''; ?>><?= __("Upload Image", "bluerabbit"); ?></option>
						<option value="upload_video" <?= $skin == 'upload_video' ? 'selected' : ''; ?>><?= __("Upload Video", "bluerabbit"); ?></option>
					</optgroup>
					<optgroup label="<?= __('FLOW — Routing & Branching', 'bluerabbit'); ?>">
						<option value="jump_to_step" <?= $skin == 'jump_to_step' ? 'selected' : ''; ?>><?= __("Jump to Step", "bluerabbit"); ?></option>
						<option value="branch_choice" <?= $skin == 'branch_choice' ? 'selected' : ''; ?>><?= __("Branch Choice", "bluerabbit"); ?></option>
					</optgroup>
					<optgroup label="<?= __('SPECIAL', 'bluerabbit'); ?>">
						<option value="system" <?= $skin == 'system' ? 'selected' : ''; ?>><?= __("System Message", "bluerabbit"); ?></option>
						<option value="win" <?= $skin == 'win' ? 'selected' : ''; ?>><?= __("Win Screen", "bluerabbit"); ?></option>
						<option value="fail" <?= $skin == 'fail' ? 'selected' : ''; ?>><?= __("Fail Screen", "bluerabbit"); ?></option>
						<option value="choose_nickname" <?= $skin == 'choose_nickname' ? 'selected' : ''; ?>><?= __("Choose Nickname", "bluerabbit"); ?></option>
						<option value="choose_avatar" <?= $skin == 'choose_avatar' ? 'selected' : ''; ?>><?= __("Choose Avatar", "bluerabbit"); ?></option>
					</optgroup>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Required?", "bluerabbit"); ?></label>
				<select id="step-required-<?= $sid; ?>" class="br-input">
					<option value="1" <?= $s->step_required ? 'selected' : ''; ?>><?= __("Yes — must complete to advance", "bluerabbit"); ?></option>
					<option value="0" <?= !$s->step_required ? 'selected' : ''; ?>><?= __("No — optional step", "bluerabbit"); ?></option>
				</select>
			</div>
		</div>

		<!-- ═══ DIALOGUE FIELDS ═══ -->
		<div class="br-skin-panel" data-skins="dialogue,system">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Character Position", "bluerabbit"); ?></label>
				<select id="step-attach-<?= $sid; ?>" class="br-input">
					<option value="none" <?= ($s->step_attach ?? '') == 'none' ? 'selected' : ''; ?>><?= __("No one — just describing the scene", "bluerabbit"); ?></option>
					<option value="left" <?= ($s->step_attach ?? '') == 'left' ? 'selected' : ''; ?>><?= __("On the left", "bluerabbit"); ?></option>
					<option value="right" <?= ($s->step_attach ?? '') == 'right' ? 'selected' : ''; ?>><?= __("On the right", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Character Name", "bluerabbit"); ?></label>
				<input class="br-input" id="step-character-name-<?= $sid; ?>" type="text" maxlength="255" value="<?= esc_attr($s->step_character_name); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Character Image", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Best at 9:16 proportions", "bluerabbit"); ?></span>
				<div class="gallery"><?php BR_Utils::instance()->insertGalleryItem('the_step_character_image', $s->step_character_image); ?></div>
			</div>
		</div>

		<!-- ═══ CONTENT EDITOR (shared across many types) ═══ -->
		<div class="br-skin-panel" data-skins="dialogue,system,open_text,win,fail,find_item">
			<div class="br-form-group" id="step-content-row-<?= $sid; ?>">
				<label class="br-form-label"><?= __("Content", "bluerabbit"); ?></label>
				<?php wp_editor($s->step_content, $step_editor_id, ['quicktags' => true, 'editor_height' => 250]); ?>
			</div>
		</div>

		<!-- ═══ VIDEO ═══ -->
		<div class="br-skin-panel" data-skins="video">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Video File", "bluerabbit"); ?></label>
				<?php $thumb_id = 'the_step_image_' . $sid; ?>
				<div class="gallery">
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

		<!-- ═══ AUDIO ═══ -->
		<div class="br-skin-panel" data-skins="audio">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Audio File", "bluerabbit"); ?></label>
				<div class="br-form-component">
					<input type="hidden" id="step-audio-url-<?= $sid; ?>" value="<?= esc_attr($settings['url'] ?? ''); ?>">
					<div style="display:flex;gap:8px;align-items:center">
						<button class="br-btn" onClick="showWPUpload('step-audio-url-<?= $sid; ?>');"><span class="icon icon-upload"></span> <?= __("Select Audio", "bluerabbit"); ?></button>
						<span id="step-audio-label-<?= $sid; ?>" style="font-size:12px;color:rgba(255,255,255,0.5)"><?= !empty($settings['url']) ? basename($settings['url']) : __('No file selected', 'bluerabbit'); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- ═══ GALLERY ═══ -->
		<div class="br-skin-panel" data-skins="gallery">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Gallery Images", "bluerabbit"); ?> <span style="color:rgba(255,255,255,0.35)">(<?= __("max 7", "bluerabbit"); ?>)</span></label>
				<div id="step-gallery-<?= $sid; ?>" style="display:flex;flex-wrap:wrap;gap:8px;padding:8px 0">
					<?php $gallery_images = $settings['images'] ?? []; foreach ($gallery_images as $gi => $img) { ?>
					<div class="br-gallery-thumb" data-index="<?= $gi; ?>">
						<div style="width:80px;height:80px;border-radius:6px;background:url(<?= esc_attr($img); ?>) center/cover;border:1px solid rgba(255,255,255,0.1)"></div>
						<button class="br-btn br-btn-red" style="padding:2px 6px;font-size:10px;margin-top:2px" onClick="brRemoveGalleryImage(<?= $sid; ?>,<?= $gi; ?>)"><span class="icon icon-trash"></span></button>
						<input type="hidden" class="gallery-image-url" value="<?= esc_attr($img); ?>">
					</div>
					<?php } ?>
				</div>
				<button class="br-btn" onClick="brAddGalleryImage(<?= $sid; ?>);" <?= count($gallery_images) >= 7 ? 'disabled' : ''; ?>><span class="icon icon-add"></span> <?= __("Add Image", "bluerabbit"); ?></button>
			</div>
		</div>

		<!-- ═══ FIND ITEM ═══ -->
		<div class="br-skin-panel" data-skins="find_item">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Item to grant", "bluerabbit"); ?></label>
				<select id="step-find-item-<?= $sid; ?>" class="br-input">
					<option value=""><?= __("Select item...", "bluerabbit"); ?></option>
					<?php if (!empty($items)) { foreach ($items as $type => $list) { if (is_array($list)) { foreach ($list as $it) { ?>
					<option value="<?= $it->item_id; ?>" <?= ($s->step_item_reward == $it->item_id || ($settings['item_id'] ?? '') == $it->item_id) ? 'selected' : ''; ?>><?= esc_html($it->item_name); ?> (<?= $type; ?>)</option>
					<?php } } } } ?>
				</select>
			</div>
		</div>

		<!-- ═══ MULTIPLE CHOICE ═══ -->
		<div class="br-skin-panel" data-skins="multiple_choice">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Question", "bluerabbit"); ?></label>
				<input class="br-input" id="step-mc-question-<?= $sid; ?>" value="<?= esc_attr($settings['question'] ?? ''); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Question Image", "bluerabbit"); ?> <span style="color:rgba(255,255,255,0.3)">(<?= __("optional", "bluerabbit"); ?>)</span></label>
				<input type="hidden" id="step-mc-image-<?= $sid; ?>" value="<?= esc_attr($settings['question_image'] ?? ''); ?>">
				<div style="display:flex;gap:8px;align-items:center">
					<button class="br-btn" onClick="showWPUpload('step-mc-image-<?= $sid; ?>');"><span class="icon icon-image"></span></button>
					<span id="step-mc-image-label-<?= $sid; ?>" style="font-size:12px;color:rgba(255,255,255,0.4)"><?= !empty($settings['question_image']) ? basename($settings['question_image']) : ''; ?></span>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Allow multiple answers?", "bluerabbit"); ?></label>
				<select id="step-mc-multi-<?= $sid; ?>" class="br-input" style="width:auto">
					<option value="0" <?= empty($settings['allow_multiple']) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="1" <?= !empty($settings['allow_multiple']) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Options", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Check the box to mark correct answer(s)", "bluerabbit"); ?></span>
				<div id="step-mc-options-<?= $sid; ?>">
					<?php if (!empty($options)) { foreach ($options as $oi => $opt) { ?>
					<div class="br-option-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
						<input type="checkbox" class="mc-correct" value="<?= esc_attr($opt['id']); ?>" <?= in_array($opt['id'], $step_correct) ? 'checked' : ''; ?>>
						<input class="br-input mc-option-text" style="flex:1" value="<?= esc_attr($opt['text']); ?>" placeholder="<?= __('Option text', 'bluerabbit'); ?>">
						<input type="hidden" class="mc-option-id" value="<?= esc_attr($opt['id']); ?>">
						<button class="br-btn br-btn-red" style="padding:4px 8px" onClick="$(this).closest('.br-option-row').remove();"><span class="icon icon-trash"></span></button>
					</div>
					<?php } } ?>
				</div>
				<button class="br-btn" onClick="brAddMcOption(<?= $sid; ?>);"><span class="icon icon-add"></span> <?= __("Add Option", "bluerabbit"); ?></button>
			</div>
		</div>

		<!-- ═══ KEYPHRASE / CRYPTEX ═══ -->
		<div class="br-skin-panel" data-skins="keyphrase,cryptex">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Prompt / Question", "bluerabbit"); ?></label>
				<input class="br-input" id="step-kp-prompt-<?= $sid; ?>" value="<?= esc_attr($settings['prompt'] ?? ''); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Accepted Answers", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Comma-separated. Any match = correct.", "bluerabbit"); ?></span>
				<input class="br-input" id="step-kp-answers-<?= $sid; ?>" value="<?= esc_attr(implode(', ', $step_correct)); ?>">
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Case sensitive?", "bluerabbit"); ?></label>
					<select id="step-kp-case-<?= $sid; ?>" class="br-input">
						<option value="0" <?= empty($settings['case_sensitive']) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
						<option value="1" <?= !empty($settings['case_sensitive']) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
					</select>
				</div>
				<div class="br-form-group br-skin-panel-inline" data-skins="cryptex">
					<label class="br-form-label"><?= __("Wheel Count", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="1" max="20" id="step-cryptex-wheels-<?= $sid; ?>" value="<?= (int) ($settings['wheel_count'] ?? 7); ?>">
				</div>
			</div>
		</div>

		<!-- ═══ PUZZLE ═══ -->
		<div class="br-skin-panel" data-skins="puzzle">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Puzzle Image", "bluerabbit"); ?></label>
				<input type="hidden" id="step-puzzle-image-<?= $sid; ?>" value="<?= esc_attr($settings['image'] ?? ''); ?>">
				<div style="display:flex;gap:8px;align-items:center">
					<button class="br-btn" onClick="showWPUpload('step-puzzle-image-<?= $sid; ?>');"><span class="icon icon-image"></span></button>
					<div id="step-puzzle-thumb-<?= $sid; ?>" style="width:80px;height:80px;border-radius:6px;background:url(<?= esc_attr($settings['image'] ?? ''); ?>) center/cover;border:1px solid rgba(255,255,255,0.1)"></div>
				</div>
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Pieces", "bluerabbit"); ?></label>
					<select id="step-puzzle-pieces-<?= $sid; ?>" class="br-input">
						<option value="4" <?= ($settings['pieces'] ?? 9) == 4 ? 'selected' : ''; ?>>4</option>
						<option value="9" <?= ($settings['pieces'] ?? 9) == 9 ? 'selected' : ''; ?>>9</option>
						<option value="16" <?= ($settings['pieces'] ?? 9) == 16 ? 'selected' : ''; ?>>16</option>
					</select>
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Difficulty", "bluerabbit"); ?></label>
					<select id="step-puzzle-diff-<?= $sid; ?>" class="br-input">
						<option value="easy" <?= ($settings['difficulty'] ?? 'medium') == 'easy' ? 'selected' : ''; ?>><?= __("Easy", "bluerabbit"); ?></option>
						<option value="medium" <?= ($settings['difficulty'] ?? 'medium') == 'medium' ? 'selected' : ''; ?>><?= __("Medium", "bluerabbit"); ?></option>
						<option value="hard" <?= ($settings['difficulty'] ?? 'medium') == 'hard' ? 'selected' : ''; ?>><?= __("Hard", "bluerabbit"); ?></option>
					</select>
				</div>
			</div>
		</div>

		<!-- ═══ BACKPACK ITEM ═══ -->
		<div class="br-skin-panel" data-skins="backpack_item">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Prompt", "bluerabbit"); ?></label>
				<input class="br-input" id="step-bi-prompt-<?= $sid; ?>" value="<?= esc_attr($settings['prompt'] ?? ''); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Required Item", "bluerabbit"); ?></label>
				<select id="step-bi-item-<?= $sid; ?>" class="br-input">
					<option value=""><?= __("Select item...", "bluerabbit"); ?></option>
					<?php if (!empty($items)) { foreach ($items as $type => $list) { if (is_array($list)) { foreach ($list as $it) { ?>
					<option value="<?= $it->item_id; ?>" <?= (($settings['item_id'] ?? '') == $it->item_id || $s->step_item == $it->item_id) ? 'selected' : ''; ?>><?= esc_html($it->item_name); ?></option>
					<?php } } } } ?>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Consume item on use?", "bluerabbit"); ?></label>
				<select id="step-bi-consume-<?= $sid; ?>" class="br-input" style="width:auto">
					<option value="0" <?= empty($settings['consume_item']) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="1" <?= !empty($settings['consume_item']) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>
		</div>

		<!-- ═══ SCORM ═══ -->
		<div class="br-skin-panel" data-skins="scorm">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("SCORM Package", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Upload a SCORM 1.2 .zip file", "bluerabbit"); ?></span>
				<?php $scorm_url = $settings['scorm_launch_url'] ?? ''; ?>
				<div id="scorm-info-<?= $sid; ?>" style="font-size:12px;color:<?= $scorm_url ? '#24da98' : 'rgba(255,255,255,0.35)'; ?>;padding:6px 0">
					<?php if ($scorm_url) { ?><span class="icon icon-check"></span> <?= esc_html($scorm_url); ?><?php } else { ?><?= __("No package uploaded yet", "bluerabbit"); ?><?php } ?>
				</div>
				<div style="display:flex;gap:8px">
					<input type="file" id="scorm-zip-<?= $sid; ?>" accept=".zip" class="br-input" style="padding:7px 14px">
					<button class="br-btn br-btn-green" onClick="brUploadScorm(<?= $sid; ?>, <?= $s->adventure_id; ?>);">
						<span class="icon icon-upload"></span> <?= $scorm_url ? __("Replace", "bluerabbit") : __("Upload", "bluerabbit"); ?>
					</button>
				</div>
				<input type="hidden" id="scorm-nonce-<?= $sid; ?>" value="<?= wp_create_nonce('br_scorm_upload'); ?>">
			</div>
		</div>

		<!-- ═══ SURVEY CHOICE / POLL ═══ -->
		<div class="br-skin-panel" data-skins="survey_choice,survey_poll">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Question", "bluerabbit"); ?></label>
				<input class="br-input" id="step-sc-question-<?= $sid; ?>" value="<?= esc_attr($settings['question'] ?? ''); ?>">
			</div>
			<div class="br-form-group br-skin-panel-inline" data-skins="survey_choice">
				<label class="br-form-label"><?= __("Allow multiple answers?", "bluerabbit"); ?></label>
				<select id="step-sc-multi-<?= $sid; ?>" class="br-input" style="width:auto">
					<option value="0" <?= empty($settings['allow_multiple']) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="1" <?= !empty($settings['allow_multiple']) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Show results after vote?", "bluerabbit"); ?></label>
				<select id="step-sc-results-<?= $sid; ?>" class="br-input" style="width:auto">
					<option value="0" <?= empty($settings['show_results']) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="1" <?= !empty($settings['show_results']) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Options", "bluerabbit"); ?></label>
				<div id="step-sc-options-<?= $sid; ?>">
					<?php if (!empty($options)) { foreach ($options as $oi => $opt) { ?>
					<div class="br-option-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
						<input class="br-input sc-option-text" style="flex:1" value="<?= esc_attr($opt['text']); ?>">
						<input type="hidden" class="sc-option-id" value="<?= esc_attr($opt['id']); ?>">
						<button class="br-btn br-btn-red" style="padding:4px 8px" onClick="$(this).closest('.br-option-row').remove();"><span class="icon icon-trash"></span></button>
					</div>
					<?php } } ?>
				</div>
				<button class="br-btn" onClick="brAddScOption(<?= $sid; ?>);"><span class="icon icon-add"></span> <?= __("Add Option", "bluerabbit"); ?></button>
			</div>
		</div>

		<!-- ═══ SURVEY RATING ═══ -->
		<div class="br-skin-panel" data-skins="survey_rating">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Question", "bluerabbit"); ?></label>
				<input class="br-input" id="step-sr-question-<?= $sid; ?>" value="<?= esc_attr($settings['question'] ?? ''); ?>">
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Min", "bluerabbit"); ?></label>
					<input class="br-input" type="number" id="step-sr-min-<?= $sid; ?>" value="<?= (int) ($settings['min'] ?? 1); ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Max", "bluerabbit"); ?></label>
					<input class="br-input" type="number" id="step-sr-max-<?= $sid; ?>" value="<?= (int) ($settings['max'] ?? 5); ?>">
				</div>
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Min Label", "bluerabbit"); ?></label>
					<input class="br-input" id="step-sr-lmin-<?= $sid; ?>" value="<?= esc_attr($settings['labels']['min'] ?? ''); ?>" placeholder="<?= __('e.g. Strongly Disagree', 'bluerabbit'); ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Max Label", "bluerabbit"); ?></label>
					<input class="br-input" id="step-sr-lmax-<?= $sid; ?>" value="<?= esc_attr($settings['labels']['max'] ?? ''); ?>" placeholder="<?= __('e.g. Strongly Agree', 'bluerabbit'); ?>">
				</div>
			</div>
		</div>

		<!-- ═══ OPEN TEXT ═══ -->
		<div class="br-skin-panel" data-skins="open_text">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Min Words", "bluerabbit"); ?></label>
				<input class="br-input" type="number" min="0" id="step-ot-minwords-<?= $sid; ?>" value="<?= (int) ($settings['min_words'] ?? 0); ?>" style="width:120px">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Use Rich Text Editor?", "bluerabbit"); ?></label>
				<select id="step-ot-editor-<?= $sid; ?>" class="br-input" style="width:auto">
					<option value="1" <?= ($settings['use_wp_editor'] ?? true) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
					<option value="0" <?= isset($settings['use_wp_editor']) && !$settings['use_wp_editor'] ? 'selected' : ''; ?>><?= __("No — plain textarea", "bluerabbit"); ?></option>
				</select>
			</div>
		</div>

		<!-- ═══ UPLOAD IMAGE / VIDEO ═══ -->
		<div class="br-skin-panel" data-skins="upload_image,upload_video">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Prompt", "bluerabbit"); ?></label>
				<input class="br-input" id="step-upload-prompt-<?= $sid; ?>" value="<?= esc_attr($settings['prompt'] ?? ''); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Max File Size (MB)", "bluerabbit"); ?></label>
				<input class="br-input" type="number" min="1" id="step-upload-maxsize-<?= $sid; ?>" value="<?= (int) ($settings['max_size_mb'] ?? 5); ?>" style="width:120px">
			</div>
		</div>

		<!-- ═══ JUMP TO STEP ═══ -->
		<div class="br-skin-panel" data-skins="jump_to_step">
			<div class="br-step-buttons-container" id="step-buttons-form-container"></div>
		</div>

		<!-- ═══ BRANCH CHOICE ═══ -->
		<div class="br-skin-panel" data-skins="branch_choice">
			<?php $branch_groups = BR_Branch::instance()->getBranchGroups($s->adventure_id); ?>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Branch", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Which branch group the player chooses from. Create branches in the Branches tab.", "bluerabbit"); ?></span>
				<select id="step-branch-group-<?= $sid; ?>" class="br-input">
					<option value=""><?= __("Select a branch...", "bluerabbit"); ?></option>
					<?php if ($branch_groups) { foreach ($branch_groups as $bg) {
						$bg_count = count(BR_Branch::instance()->getGroupAchievements($bg->group_id));
					?>
					<option value="<?= $bg->group_id; ?>" <?= $s->step_branch_group_id == $bg->group_id ? 'selected' : ''; ?>><?= esc_html($bg->group_name); ?> (<?= $bg_count; ?> <?= __("paths", "bluerabbit"); ?>)</option>
					<?php } } ?>
				</select>
			</div>
			<?php if (empty($branch_groups)) { ?>
			<p style="font-size:13px;color:rgba(255,255,255,0.4);padding:4px 0">
				<span class="icon icon-warning" style="color:#f7cb15"></span>
				<?= __("No branches created yet. Go to the Branches tab in Manage Adventure to create one.", "bluerabbit"); ?>
			</p>
			<?php } ?>
			<input type="hidden" id="the_step_achievement_group" value="">
		</div>

		<!-- ═══ CHOOSE NICKNAME / AVATAR ═══ -->
		<div class="br-skin-panel" data-skins="choose_nickname" style="text-align:center;padding:16px">
			<p style="color:rgba(255,255,255,0.5)"><?= __("The system prompts the player to input their First and Last names", "bluerabbit"); ?></p>
		</div>
		<div class="br-skin-panel" data-skins="choose_avatar" style="text-align:center;padding:16px">
			<p style="color:rgba(255,255,255,0.5)"><?= __("Upload the images of the avatars available to the players", "bluerabbit"); ?></p>
		</div>

		<!-- ═══ MISTAKE MESSAGE (validate types) ═══ -->
		<div class="br-skin-panel" data-skins="multiple_choice,keyphrase,cryptex,puzzle,backpack_item,scorm">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Mistake Message", "bluerabbit"); ?> <span style="color:rgba(255,255,255,0.3)">(<?= __("optional", "bluerabbit"); ?>)</span></label>
				<span class="br-form-hint"><?= __("Shown when the player answers incorrectly", "bluerabbit"); ?></span>
				<input class="br-input" id="step-mistake-msg-<?= $sid; ?>" value="<?= esc_attr($s->step_mistake_message); ?>">
			</div>
		</div>

		<!-- ═══ BACKGROUND ═══ -->
		<div class="br-form-group" id="step-background-row-<?= $sid; ?>">
			<label class="br-form-label"><?= __("Background", "bluerabbit"); ?></label>
			<span class="br-form-hint"><?= __("Background image of the scene", "bluerabbit"); ?></span>
			<div class="gallery"><?php BR_Utils::instance()->insertGalleryItem('the_step_background', $s->step_background); ?></div>
		</div>

		<!-- ═══ STEP REWARDS ═══ -->
		<div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.06)">
			<label class="br-form-label" style="margin-bottom:10px"><span class="icon icon-basket"></span> <?= __("Step Rewards", "bluerabbit"); ?> <span style="color:rgba(255,255,255,0.3)">(<?= __("optional — granted on completion", "bluerabbit"); ?>)</span></label>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label" style="font-size:11px"><?= __("XP", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="0" id="step-reward-xp-<?= $sid; ?>" value="<?= (int) $s->step_xp_reward; ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label" style="font-size:11px"><?= __("BLOO", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="0" id="step-reward-bloo-<?= $sid; ?>" value="<?= (int) $s->step_bloo_reward; ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label" style="font-size:11px"><?= __("EP", "bluerabbit"); ?></label>
					<input class="br-input" type="number" min="0" id="step-reward-ep-<?= $sid; ?>" value="<?= (int) $s->step_ep_reward; ?>">
				</div>
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label" style="font-size:11px"><?= __("Item Reward", "bluerabbit"); ?></label>
					<select id="step-reward-item-<?= $sid; ?>" class="br-input">
						<option value=""><?= __("None", "bluerabbit"); ?></option>
						<?php if (!empty($items)) { foreach ($items as $type => $list) { if (is_array($list)) { foreach ($list as $it) { ?>
						<option value="<?= $it->item_id; ?>" <?= $s->step_item_reward == $it->item_id ? 'selected' : ''; ?>><?= esc_html($it->item_name); ?></option>
						<?php } } } } ?>
					</select>
				</div>
				<div class="br-form-group">
					<label class="br-form-label" style="font-size:11px"><?= __("Achievement Reward", "bluerabbit"); ?></label>
					<select id="step-reward-ach-<?= $sid; ?>" class="br-input">
						<option value=""><?= __("None", "bluerabbit"); ?></option>
						<?php if (!empty($achievements['publish'])) { foreach ($achievements['publish'] as $ach) { ?>
						<option value="<?= $ach->achievement_id; ?>" <?= $s->step_achievement_reward == $ach->achievement_id ? 'selected' : ''; ?>><?= esc_html($ach->achievement_name); ?></option>
						<?php } } ?>
					</select>
				</div>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<div class="br-step-form-footer">
		<button class="br-btn br-btn-green" style="padding:10px 32px;font-size:14px" onClick="updateStep(<?= $sid; ?>);">
			<span class="icon icon-check"></span> <?= __("Update Step", "bluerabbit"); ?>
		</button>
	</div>
</div>

<script>
brCheckStepSkin(<?= $sid; ?>);
tinyMCE.execCommand('mceAddEditor', true, '<?= $step_editor_id; ?>');
</script>
<?php include(get_stylesheet_directory() . '/tutorials/tutorial-step-form.php'); ?>

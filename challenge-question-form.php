<div class="accordion-tab relative question br-question-item" id="accordion-tab-question-<?= $qKey; ?>">
	<div class="accordion-button br-question-header cursor-pointer" onClick="activate('#accordion-tab-question-<?= $qKey; ?>');animateScroll('#accordion-tab-question-<?= $qKey; ?>');">
		<span class="br-question-num">Q #<?= $qCount; ?></span>
		<span class="question-text"><?= $question['title'] ?: __('Untitled Question', 'bluerabbit'); ?></span>
		<span class="icon icon-arrow-down br-question-chevron"></span>
	</div>
	<div class="accordion-content">
		<div class="br-question-body" id="question-<?= $qKey; ?>">

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Question Text", "bluerabbit"); ?></label>
				<textarea id="question-text-<?= $qKey; ?>" placeholder="<?= __('Question Text','bluerabbit'); ?>" class="br-input" rows="3" maxlength="800" onChange="updateQuestion('challenge',<?= $qKey; ?>);"><?= $question['title']; ?></textarea>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Media", "bluerabbit"); ?></label>
				<?php BR_Utils::instance()->insertMultimediaItem('question-'.$qKey.'-img', $question['image'], $qKey); ?>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Answer Options", "bluerabbit"); ?></label>
				<div class="br-options-header">
					<span class="br-opt-col-toggle"><?= __("Correct", "bluerabbit"); ?></span>
					<span class="br-opt-col-img"><?= __("Image", "bluerabbit"); ?></span>
					<span class="br-opt-col-text"><?= __("Answer", "bluerabbit"); ?></span>
					<span class="br-opt-col-del"></span>
				</div>
				<div class="question-options">
					<?php
					$oCount = 0;
					if($question['answers']){
						foreach($question['answers'] as $option) {
							$oKey = $option['answer_id'];
							include (TEMPLATEPATH . '/challenge-question-option-form.php');
							$oCount++;
						}
					}else{
						$options = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$qKey ", "ARRAY_A");
						foreach($options as $option) {
							$oKey = $option['answer_id'];
							include (TEMPLATEPATH . '/challenge-question-option-form.php');
							$oCount++;
						}
					}
					?>
				</div>
				<div style="padding:8px 0">
					<button class="br-btn br-btn-sm" style="border-color:rgba(36,218,152,0.4);color:#24da98" onClick="addOption('challenge',<?= $qKey; ?>);">
						<span class="icon icon-add"></span> <?= __('Add Option','bluerabbit'); ?>
					</button>
				</div>
			</div>

			<div class="br-question-actions">
				<div class="relative" style="display:inline-block">
					<button class="br-btn br-btn-sm br-btn-red" onClick="showOverlay('#confirm-question-<?= $qKey; ?>');">
						<span class="icon icon-trash"></span> <?= __("Remove", "bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-question-<?= $qKey; ?>" style="background:rgba(30,30,30,0.95);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;white-space:nowrap">
						<span style="display:block;font-size:13px;font-weight:700;color:#f44336;margin-bottom:8px"><?= __("Are you sure?", "bluerabbit"); ?></span>
						<span style="display:block;font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:10px"><?= __("You can't undo this", "bluerabbit"); ?></span>
						<div style="display:flex;gap:6px">
							<button class="br-btn br-btn-sm br-btn-red" onClick="removeQuestion(<?= $qKey; ?>,'challenge');">
								<span class="icon icon-trash"></span> <?= __("Delete", "bluerabbit"); ?>
							</button>
							<button class="br-btn br-btn-sm" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</div>
				<div class="relative" style="display:inline-block">
					<button class="br-btn br-btn-sm" style="border-color:rgba(255,152,0,0.4);color:#ff9800" onClick="showOverlay('#confirm-duplicate-<?= $qKey; ?>');">
						<span class="icon icon-duplicate"></span> <?= __("Duplicate", "bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-duplicate-<?= $qKey; ?>" style="background:rgba(30,30,30,0.95);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:12px;white-space:nowrap">
						<span style="display:block;font-size:13px;font-weight:700;color:#ff9800;margin-bottom:8px"><?= __("Duplicate this question?", "bluerabbit"); ?></span>
						<div style="display:flex;gap:6px">
							<button class="br-btn br-btn-sm" style="border-color:rgba(255,152,0,0.4);color:#ff9800" onClick="duplicateQuestion(<?= $qKey; ?>,'challenge');">
								<span class="icon icon-duplicate"></span> <?= __("Duplicate", "bluerabbit"); ?>
							</button>
							<button class="br-btn br-btn-sm" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

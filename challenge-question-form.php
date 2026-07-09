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
				<div class="br-add-option-wrap">
					<button class="br-btn br-btn-sm br-btn-green" onClick="addOption('challenge',<?= $qKey; ?>);">
						<span class="icon icon-add"></span> <?= __('Add Option','bluerabbit'); ?>
					</button>
				</div>
			</div>

			<div class="br-question-actions">
				<div class="relative br-inline-block">
					<button class="br-btn br-btn-sm br-btn-red" onClick="showOverlay('#confirm-question-<?= $qKey; ?>');">
						<span class="icon icon-trash"></span> <?= __("Remove", "bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer br-confirm-popup" id="confirm-question-<?= $qKey; ?>">
						<span class="br-confirm-title br-confirm-title-red"><?= __("Are you sure?", "bluerabbit"); ?></span>
						<span class="br-confirm-subtitle"><?= __("You can't undo this", "bluerabbit"); ?></span>
						<div class="br-confirm-buttons">
							<button class="br-btn br-btn-sm br-btn-red" onClick="removeQuestion(<?= $qKey; ?>,'challenge');">
								<span class="icon icon-trash"></span> <?= __("Delete", "bluerabbit"); ?>
							</button>
							<button class="br-btn br-btn-sm" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</div>
				<div class="relative br-inline-block">
					<button class="br-btn br-btn-sm br-btn-amber" onClick="showOverlay('#confirm-duplicate-<?= $qKey; ?>');">
						<span class="icon icon-duplicate"></span> <?= __("Duplicate", "bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer br-confirm-popup" id="confirm-duplicate-<?= $qKey; ?>">
						<span class="br-confirm-title br-confirm-title-amber"><?= __("Duplicate this question?", "bluerabbit"); ?></span>
						<div class="br-confirm-buttons">
							<button class="br-btn br-btn-sm br-btn-amber" onClick="duplicateQuestion(<?= $qKey; ?>,'challenge');">
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

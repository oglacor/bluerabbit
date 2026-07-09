<?php $params = "'option-image-$oKey','o','challenge',$qKey, $oKey"; ?>
<div class="br-option-row" id="option-<?= $oKey; ?>">
	<div class="br-opt-col-toggle">
		<button class="toggle-button question <?= ($option['answer_correct'] > 0) ? 'active' : ''; ?>" onClick="toggleCorrect('#option-<?= $oKey; ?>');updateOption('challenge',<?= "$qKey, $oKey"; ?>);">&nbsp;</button>
		<input type="hidden" class="option-correct" value="<?= ($option['answer_correct'] > 0) ? 1 : 0; ?>">
	</div>
	<div class="br-opt-col-img">
		<button class="br-opt-img-btn" id="option-image-<?= $oKey; ?>_thumb" style="<?= $option['answer_image'] ? 'background-image:url('.$option['answer_image'].');' : ''; ?>" onClick="showWPUpload(<?= $params; ?>);"></button>
		<button class="br-opt-img-clear" onClick="clearImage('#option-image-<?= $oKey; ?>'); updateOption('challenge',<?= "$qKey, $oKey"; ?>);">
			<span class="icon icon-trash"></span>
		</button>
		<input type="hidden" id="option-image-<?= $oKey; ?>" value="<?= $option['answer_image']; ?>">
	</div>
	<div class="br-opt-col-text">
		<textarea id="option-text-<?= $oKey; ?>" rows="2" class="br-input" placeholder="<?= __("Option Text","bluerabbit"); ?>" onChange="updateOption('challenge',<?= "$qKey, $oKey"; ?>);"><?= $option['answer_value']; ?></textarea>
	</div>
	<div class="br-opt-col-del relative">
		<button class="br-btn br-btn-sm br-btn-red br-btn-xs" onClick="showOverlay('#confirm-option-<?= $oKey; ?>');">
			<span class="icon icon-cancel"></span>
		</button>
		<div class="confirm-action overlay-layer br-confirm-popup" id="confirm-option-<?= $oKey; ?>">
			<span class="br-confirm-title br-confirm-title-red"><?= __("Delete option?", "bluerabbit"); ?></span>
			<div class="br-confirm-buttons">
				<button class="br-btn br-btn-sm br-btn-red" onClick="removeOption(<?= $oKey; ?>,'challenge');">
					<span class="icon icon-trash"></span> <?= __("Delete", "bluerabbit"); ?>
				</button>
				<button class="br-btn br-btn-sm" onClick="hideAllOverlay();">
					<span class="icon icon-cancel"></span>
				</button>
			</div>
		</div>
	</div>
</div>

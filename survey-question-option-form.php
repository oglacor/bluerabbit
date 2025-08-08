<?php $params = "'option-image-$oKey','o','survey',$qKey, $oKey"; ?>

<li class="option form" id="option-<?php echo $oKey; ?>">
	<div class="option-image" id="option-image-<?php echo $oKey; ?>_thumb" style="background-image: url(<?php echo $option['survey_option_image']; ?>);" onClick="showWPUpload(<?php echo $params; ?>);"></span>
		<input type="hidden" id="option-image-<?php echo $oKey; ?>" value="<?php echo $option['survey_option_image']; ?>">
	</div>
	<div class="option-image-buttons">
		<button class="icon-button font _24 sq-40  green-bg-500 white-color" onClick="showWPUpload(<?php echo $params; ?>);">
			<span class="icon icon-image"></span>
		</button>
		<button class="icon-button font _24 sq-40  red-bg-400 white-color" onClick="clearImage('#option-image-<?php echo $oKey; ?>');updateOption('survey',<?php echo "$qKey, $oKey"; ?>);">
			<span class="icon icon-trash"></span>
		</button>
	</div>
	<div class="option-text blue-grey-bg-200">
		<input id="option-text-<?php echo $oKey; ?>" type="text" value="<?php echo $option['survey_option_text']; ?>" class="form-ui option-value" placeholder="<?php _e("Type in the option","bluerabbit"); ?>"  onChange="updateOption('survey',<?php echo "$qKey, $oKey"; ?>);">
	</div>
	<div class="option-actions">
		<button class="icon-button font _24 sq-40  red-bg-400 white-color remove-option" onClick="showOverlay('#confirm-option-<?php echo $oKey; ?>');">
			<span class="icon icon-cancel"></span>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $oKey; ?>">
			<h3 class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></h3>
			<h5 class="line font _14 grey-600 w500"><?= __("You can't undo this","bluerabbit"); ?></h5>
			<div class="confirm-buttons-container">
				<button class="icon-button green-bg-400 font _20 sq-30" onClick="removeOption(<?php echo $oKey; ?>,'survey');">
					<span class="icon icon-check white-color"></span>
				</button>
				<button class="icon-button font _20 sq-30 red-bg-400 white-color" onClick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
		</div>
	</div>
	
</li>

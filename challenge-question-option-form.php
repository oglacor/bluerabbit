<?php $params = "'option-image-$oKey','o','challenge',$qKey, $oKey"; ?>
<tr id="option-<?php echo $oKey; ?>">
	<td>
		<button class="toggle-button question <?= ($option['answer_correct'] > 0) ? 'active' : false; ?>" onClick="toggleCorrect('#option-<?php echo $oKey; ?>');updateOption('challenge',<?php echo "$qKey, $oKey"; ?>);">&nbsp;</button>

		<input type="hidden" class="option-correct" value="<?= ($option['answer_correct'] > 0) ? 1 : 0; ?>">			
	</td>
	<td>
		<button class="icon-button sq-50" id="option-image-<?php echo $oKey; ?>_thumb" style="background-image: url(<?php echo $option['answer_image']; ?>);" onClick="showWPUpload(<?php echo $params; ?>);"></button>
		<button class="icon-button font _14 sq-20 red-bg-400 white-color" onClick="clearImage('#option-image-<?php echo $oKey; ?>'); updateOption('challenge',<?php echo "$qKey, $oKey"; ?>);">
			<span class="icon icon-trash"></span>
		</button>
		<input type="hidden" id="option-image-<?php echo $oKey; ?>" value="<?php echo $option['answer_image']; ?>">

	</td>
	<td>
		<textarea id="option-text-<?php echo $oKey; ?>" rows="2" class="form-ui option-value grey-bg-100 border border-all grey-border-800" placeholder="<?php _e("Option Text","bluerabbit"); ?>" onChange="updateOption('challenge',<?php echo "$qKey, $oKey"; ?>);" ><?php echo $option['answer_value'] ; ?></textarea>
	</td>
	<td class="relative">
		<button class="icon-button font _24 sq-40  red-bg-400 white-color remove-option" onClick="showOverlay('#confirm-option-<?php echo $oKey; ?>');">
			<span class="icon icon-cancel"></span>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $oKey; ?>">
			<button class="form-ui white-bg" onClick="removeOption(<?php echo $oKey; ?>,'challenge');">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
						<span class="icon icon-trash white-color"></span>
					</span>
					<span class="icon-content">
						<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
						<span class="line font _14 grey-400"><?php _e("You can't undo this","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
	</td>
</tr>

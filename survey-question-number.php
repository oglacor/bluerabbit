<div class="w-full padding-10">
	<?php if($q['survey_question_display'] =='dropdown'){ ?>
		<div class="input-group font _24 w900 text-center w-200 boxed">
			<label class="deep-purple-bg-400"><?php _e("Value","bluearbbit"); ?>: </label>
			<select class="form-ui range-value font _24 w900 white-bg" id="question-answer-value-<?php echo $key; ?>" onChange="submitSurveyAnswer(<?php echo $key; ?>);">
				<?php for($i=0; $i<=$q['range']; $i++){ ?>
					<option value="<?= $i; ?>" <?php echo ($i==$q['survey_answer_value']) ? 'selected' : ''; ?>><?= $i; ?></option>
				<?php } ?>
			</select>
		</div>
	<?php }elseif($q['survey_question_display'] =='spinner'){ ?>
		<div class="text-center w-full">
			<div class="spinner">
				<button class="icon-button font _24 sq-40  spin-down deep-purple-bg-700" onClick="spinDown('#question-answer-value-<?php echo $key; ?>',0);submitSurveyAnswer(<?php echo $key; ?>);">
					<div class="icon icon-remove"></div>
				</button>
				<input class="number text-center font _36 w900 grey-800 white-bg padding-5" disabled type="number" max="<?= $q['range']; ?>" min="0" id="question-answer-value-<?php echo $key; ?>" value="<?= $q['survey_answer_value'] ? $q['survey_answer_value'] : 0; ?>">
				<button class="icon-button font _24 sq-40  spin-up deep-purple-bg-700" onClick="spinUp('#question-answer-value-<?php echo $key; ?>',<?= $q['range']; ?>);submitSurveyAnswer(<?php echo $key; ?>);">
					<div class="icon icon-add"></div>
				</button>
			</div>
		</div>
	<?php }else{ ?>
		<div class="input-group font _24 w900 text-center w-200 boxed">
			<label class="deep-purple-bg-400"><?php _e("Value","bluearbbit"); ?>: </label>
			<input type="number" id="question-answer-value-<?php echo $key; ?>" value="<?= $q['survey_answer_value']; ?>" class=" text-center form-ui range-value white-bg" onChange="submitSurveyAnswer(<?php echo $key; ?>);">
		</div>
	<?php } ?>
</div>

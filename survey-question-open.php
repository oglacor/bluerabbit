<div class="survey-open-question">
	<h3><?php _e("Your answer","bluerabbit"); ?>: </h3>
	<textarea type="text" rows="10" class="form-ui white-bg " value="<?php echo $q['survey_answer_value']; ?>" id="question-answer-value-<?php echo $key; ?>" onChange="submitSurveyAnswer(<?php echo $key; ?>);"><?php echo $q['survey_answer_value']; ?></textarea>
</div>

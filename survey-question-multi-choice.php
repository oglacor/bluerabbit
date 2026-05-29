	<?php 
	$oCount = 0;
	$active_options = isset($q['survey_answer_value']) ? explode(',', $q['survey_answer_value']) : array();
	foreach($q['options'] as $oKey=>$o) {
		$oCount ++;
		include (TEMPLATEPATH . '/survey-question-option-multi.php');
	}
	?>
<input type="hidden" value="<?= isset($q['survey_answer_value']) ? $q['survey_answer_value'] : "" ; ?>" id="question-answer-value-<?= $i; ?>">

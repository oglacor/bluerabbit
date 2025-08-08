<h4><?= __("Choose as many as you want","bluerabbit"); ?></h4>
<ul class="question-options multiple" id="question-<?= $key; ?>">
	<?php 
	$oCount = 0;
	$active_options = isset($q['survey_answer_value']) ? explode(',', $q['survey_answer_value']) : array();
	foreach($q['options'] as $oKey=>$o) {
		$oCount ++;
		include (TEMPLATEPATH . '/survey-question-option-multi.php');
	}
	?>
</ul>
<input type="hidden" value="<?= isset($q['survey_answer_value']) ? $q['survey_answer_value'] : "" ; ?>" id="question-answer-value-<?= $key; ?>">

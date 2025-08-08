<div class="rating padding-10 survey-rating-question" id="question-<?php echo $key; ?>">
	<input type="hidden" class="form-ui" value="<?= isset($q['survey_answer_value']) ? $q['survey_answer_value'] : ""; ?>" id="question-answer-value-<?= $key; ?>">
	<?php $range = $q['range'] ? $q['range'] : 5; ?>
	<button class="star star-0 <?= isset($q['survey_answer_value']) && ($q['survey_answer_value']==='0') ? 'active' : ''; ?>" onClick="updateQuestionValue('<?= $key; ?>',0);">
		<span class="icon icon-cancel"></span>
	</button>
	<?php for($i=1; $i<=$range; $i++){ ?>
		<button class="star star-<?= $i; ?> <?= isset($q['survey_answer_value']) && ($q['survey_answer_value'] >= $i) ? 'active' : ''; ?>" onClick="updateQuestionValue('<?= $key; ?>',<?= $i; ?>);">
			<span class="icon icon-star"></span>
		</button>
	<?php } ?>
</div>
	

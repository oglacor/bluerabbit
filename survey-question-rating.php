<div class="rating padding-10 survey-rating-question" id="question-<?php echo $i; ?>">
	<input type="hidden" class="form-ui" value="<?= isset($q['survey_answer_value']) ? $q['survey_answer_value'] : ""; ?>" id="question-answer-value-<?= $i; ?>">
	<?php $range = $q['range'] ? $q['range'] : 5; ?>
	<button class="star star-0 <?= isset($q['survey_answer_value']) && ($q['survey_answer_value']==='0') ? 'active' : ''; ?>" onClick="updateQuestionValue('<?= $i; ?>',0);">
		<span class="icon icon-cancel"></span>
	</button>
	<?php for($j=1; $j<=$range; $j++){ ?>
		<button class="star star-<?= $j; ?> <?= isset($q['survey_answer_value']) && ($q['survey_answer_value'] >= $j) ? 'active' : ''; ?>" onClick="updateQuestionValue('<?= $i; ?>',<?= $j; ?>);">
			<span class="icon icon-star"></span>
		</button>
	<?php } ?>
</div>
	

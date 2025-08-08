<tr id="question-number-<?= $qs[$i]->question_id; ?>" class="question-number" onClick="navToQuestion('<?= $qs[$i]->question_id; ?>');">
	<td class="question-number">
		<?= ($i+1); ?>
	</td>
	<td class="question-title">
		<?php $q_title = substrwords($qs[$i]->question_title, 65); ?>
		<span class="question-title-text"><?=  $q_title; ?></span>
	</td>
</tr>

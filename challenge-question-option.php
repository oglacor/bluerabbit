
<li class="question-option cursor-pointer" id="op<?php echo $a->answer_id."-".$a->question_id; ?>" onClick="submitAnswer(<?php echo $a->answer_id.",".$a->question_id; ?>);">
	<input type="hidden" class="answer-id" value="<?php echo $a->answer_id; ?>">
	<?php if($a->answer_image) { ?>
		<img src="<?php echo $a->answer_image; ?>"><br>
	<?php }?>
	<?php if($a->answer_value !== NULL) { ?>
		<span class="font _18 w500 padding-10"><?php echo $a->answer_value; ?></span>
	<?php }?>
</li>

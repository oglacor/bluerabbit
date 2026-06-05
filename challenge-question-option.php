<?php if($opts_all_images){ ?>

<li class="question-option cursor-pointer"
    id="op<?= $a->answer_id."-".$a->question_id; ?>"
    onClick="submitAnswer(<?= $a->answer_id.",".$a->question_id; ?>);">
	<input type="hidden" class="answer-id" value="<?= $a->answer_id; ?>">
	<div class="opt-img-wrap">
		<?php if($a->answer_image){ ?>
			<img src="<?= $a->answer_image; ?>" alt="<?= esc_attr($a->answer_value); ?>">
		<?php } ?>
		<span class="opt-letter-badge"><?= $oLetter; ?></span>
		<span class="opt-check-badge"><span class="icon icon-check"></span></span>
	</div>
	<?php if($a->answer_value){ ?>
	<div class="opt-img-label"><span class="font _12"><?= $a->answer_value; ?></span></div>
	<?php } ?>
</li>

<?php } else { ?>

<li class="question-option cursor-pointer"
    id="op<?= $a->answer_id."-".$a->question_id; ?>"
    onClick="submitAnswer(<?= $a->answer_id.",".$a->question_id; ?>);">
	<input type="hidden" class="answer-id" value="<?= $a->answer_id; ?>">
	<?php if($a->answer_image){ ?>
		<img src="<?= $a->answer_image; ?>" alt="">
	<?php } ?>
	<?php if($a->answer_value !== NULL){ ?>
		<span class="font _14 w500"><?= $a->answer_value; ?></span>
	<?php } ?>
</li>

<?php } ?>
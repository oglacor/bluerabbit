<?php $active = (isset($q['selected_answer']) && $q['selected_answer'] == $oKey) ? 'active' : ''; ?>
<li class="option cursor-pointer <?php echo $active; ?>" id="option-<?php echo $oKey; ?>" onClick="submitSurveyAnswer(<?php echo $key; ?>,<?php echo $oKey; ?>);">
 	<?php if($o['image']) { ?>
        <div class="option-image" style="background-image: url(<?php echo $o['image']; ?>);"></div>
	<?php }?>
 	<?php if($o['text']) { ?>
        <div class="option-text"><?php echo $o['text']; ?></div>
	<?php }?>
</li>
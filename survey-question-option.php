<?php $active = (isset($q['selected_answer']) && $q['selected_answer'] == $oKey) ? 'active' : ''; ?>
<button class="step-choice action-button <?= $active; ?>" id="option-<?php echo $oKey; ?>" onClick="submitSurveyAnswer(<?php echo $i; ?>,<?php echo $oKey; ?>);">
    <?php if($o['image']){ ?><img src="<?= $o['image'];?>" alt=""> <?php } ?>
    <p><?php echo $o['text']; ?></p>
</button>



<?php $active = isset($active_options) && (in_array($oKey, $active_options)) ? 'active' : ''; ?>
<button class="step-choice action-button <?= $active; ?>" id="option-<?php echo $oKey; ?>"onClick="prepareMultiChoiceValue(<?php echo $i; ?>,'#option-<?php echo $oKey; ?>');">
    <?php if($o['image']){ ?><img src="<?= $o['image'];?>" alt=""> <?php } ?>
    <p><?php echo $o['text']; ?></p>
</button>

<?php $active = isset($active_options) && (in_array($oKey, $active_options)) ? 'active' : ''; ?>
<li class="option cursor-pointer <?php echo $active; ?>" id="option-<?php echo $oKey; ?>" onClick="prepareMultiChoiceValue(<?php echo $key; ?>,'#option-<?php echo $oKey; ?>');">
	<input type="hidden" class="option-value" value="<?php echo $oKey; ?>">
 	<?php if($o['image']) { ?>
        <div class="option-image" style="background-image: url(<?php echo $o['image']; ?>);"></div>
	<?php }?>
 	<?php if($o['text']) { ?>
        <div class="option-text"><?php echo $o['text']; ?></div>
	<?php }?>
</li>

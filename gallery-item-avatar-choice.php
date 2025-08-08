
<div class="gallery-item setting" id="step-button-<?=$btn->button_id; ?>">
	<div class="background" style="background-image: url(<?= $image_url; ?>);" onClick="showWPUpload('<?= $thumb_id;?>' <?= $callback;?>);" id="<?= $thumb_id;?>_thumb">
		<?php $mime = (wp_check_filetype($image_url));?>
		<video id="<?= $thumb_id;?>_thumb_video" class="gallery-item-video <?= strstr($mime['type'], "video") ? 'active' : ''; ?>" controls>
			<source src="<?=$image_url; ?>">
		</video>
	</div>
	<div class="gallery-item-options relative">
		<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('<?= $thumb_id;?>' <?= $callback;?>);"><span class="icon icon-image"></span></button>
		<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?= $thumb_id;?>');"> <span class="icon icon-trash"></span> </button>
		<input type="hidden" id="<?= $thumb_id;?>" value="<?php echo $image_url; ?>"/>
		<button class="icon-button font _24 sq-40 pink-bg-400" onClick="removeStepButton(<?=$btn->button_id; ?>);"><span class="icon icon-cancel"></span></button>
	</div>
</div>

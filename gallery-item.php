
<div class="gallery-item setting">
	<div class="background" style="background-image: url(<?= $file; ?>);" onClick="showWPUpload('<?= $thumb_id;?>' <?= $callback;?>);" id="<?= $thumb_id;?>_thumb">
		<?php $mime = (wp_check_filetype($file));?>
		
		<video id="<?= $thumb_id;?>_thumb_video" class="gallery-item-video <?= strstr($mime['type'], "video") ? 'active' : ''; ?>" controls>
			<source src="<?=$file; ?>">
		</video>
	</div>
	<div class="gallery-item-options relative">
		<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('<?= $thumb_id;?>' <?= $callback;?>);"><span class="icon icon-image"></span></button>
		<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?= $thumb_id;?>');"> <span class="icon icon-trash"></span> </button>
		<input type="hidden" id="<?= $thumb_id;?>" value="<?php echo $file; ?>"/>
	</div>
</div>


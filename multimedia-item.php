		<input type="hidden" id="<?=  $thumb_id; ?>" value="<?= $file; ?>"/>
		<?php $mime = (wp_check_filetype($file));?>
		<?php // strstr($mime['type'], "video", "audio", "image") ? 'active' : ''; ?>
		<div class="multimedia-gallery">
			<div class="multimedia-item setting relative" id="<?=  $thumb_id; ?>">
				<div class="action-buttons">
					<button class="foreground icon-button font _24 sq-40  green-bg-400" onClick="showWPUploadMultimedia('<?=  $thumb_id; ?>', 'challenge',<?=  $index; ?>);"><span class="icon icon-image"></span></button>
					<button class="foreground icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?=  $thumb_id; ?>'); updateQuestion('challenge',<?=  $index; ?>);"> <span class="icon icon-trash"></span> </button>
				</div>
				<div class="multimedia-element">
					<?php if(strstr($mime['type'], "image")){ ?>
						<img id="<?= $thumb_id; ?>_thumb" src="<?= $file; ?>" alt="">
					<?php }elseif(strstr($mime['type'], "video")){ ?>
						<video id="<?= $thumb_id; ?>_thumb" controls class="gallery-item-video">
							<source src="<?= $file; ?>">
						</video>
					<?php }elseif(strstr($mime['type'], "audio")){ ?>
						<audio id="<?= $thumb_id; ?>_thumb" controls class="gallery-item-audio">
							<source src="<?= $file; ?>">
						</audio>
					<?php }?> 
				</div>
			</div>
		</div>

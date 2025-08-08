<div class="image-select" id="icon-art-books">
	<?php for($i=1;$i<=12;$i++){ ?>
		<?php $file = get_bloginfo('template_directory')."/images/icon-art/books/$i.png"; ?>
		<?php $active = ($selected_book == $file) ? 'active' : ''; ?>
		<button class="relative button form-ui font _18 sq-100 margin-5  <?=$active;?>" onClick="selectImage('#book-image-<?=$i; ?>','#icon-art-books');" id="book-image-<?=$i; ?>">
			<span class="active-content layer base absolute bottom-5 right-5 border border-all rounded-max sq-30 green-bg-500 white-color">
				<span class="layer base absolute perfect-center icon icon-check"></span>
			</span>
			<div class="layer background absolute sq-full block top left" style="background-image: url(<?= $file;?>)"></div>
			<div class="layer background absolute sq-full inactive-content white-bg opacity-30 block top left"></div>
			<input type="hidden" class="value" value="<?=$file;?>">
		</button>
	<?php } ?>
</div>
<div class="gallery">
	<div class="gallery-item setting">
		<div class="background" style="background-image: url(<?= isset($quest->mech_badge) ? ($quest->mech_badge) : ""; ?>);" onClick="showWPUpload('the_quest_badge');" id="the_quest_badge_thumb"></div>
		<div class="gallery-item-options relative">
			<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_quest_badge');"><span class="icon icon-image"></span></button>
			<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_quest_badge');"> <span class="icon icon-trash"></span> </button>
		</div>
	</div>
</div>

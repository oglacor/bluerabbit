<?php 
	$thumb_id = 'the_step_button_image-'.$btn->button_id; 
	$image_url = $btn->button_image;
	$theFile = (get_template_directory()."/gallery-item-avatar-choice.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}
?>
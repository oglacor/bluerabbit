	<div class="padding-10 blue-bg-700 white-color w-full sticky top layer overlay">
		<button class="button form-ui blue-bg-800 pull-right layer relative base" onClick="addStepButton();"><?= __("Add avatar","bluerabbit"); ?></button>
		<h2 class="font _24 w900">
			<span class="icon icon-objectives"></span>
			<?= __("Avatars","bluerabbit"); ?>
		</h2>
		<h3 class="font _18 w200 opacity-70">
			<?= __("Add the options of avatars the player has","bluerabbit"); ?>
		</h3>
	</div>

	<div class="padding-10 white-color w-full relative">
		<?php $buttons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE step_id=$s->step_id AND button_status='publish' AND button_type='choose-avatar'"); ?>
		<div id="step-buttons-list"  class="gallery margin-10 avatar-choice-list">
			<?php foreach($buttons as $key=>$btn){ ?>
			
				<?php
				$thumb_id = 'the_step_button_image-'.$btn->button_id; 
				$image_url = $btn->button_image;
				$callback = ",'step','step',".$btn->button_id;
				$theFile = (get_template_directory()."/gallery-item-avatar-choice.php");
				if(file_exists($theFile)) {
					include ($theFile);
				}
				?>
			<?php } ?>
		</div>
	</div>



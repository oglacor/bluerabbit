	<div style="padding:14px 20px;border-bottom:1px solid rgba(28,194,235,0.12);display:flex;align-items:center;justify-content:space-between">
		<h3 class="br-panel-title" style="margin:0"><span class="icon icon-objectives"></span> <?= __("Avatars", "bluerabbit"); ?></h3>
		<button class="br-btn" onClick="addStepButton();"><span class="icon icon-add"></span> <?= __("Add avatar", "bluerabbit"); ?></button>
	</div>
	<div style="padding:14px 20px">
		<span class="br-form-hint" style="display:block;margin-bottom:12px"><?= __("Add the options of avatars the player has", "bluerabbit"); ?></span>
		<?php $buttons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE step_id=$s->step_id AND button_status='publish' AND button_type='choose-avatar'"); ?>
		<div id="step-buttons-list" class="gallery margin-10 avatar-choice-list">
			<?php foreach ($buttons as $key => $btn) {
				$thumb_id = 'the_step_button_image-' . $btn->button_id;
				$image_url = $btn->button_image;
				$callback = ",'step','step'," . $btn->button_id;
				$theFile = get_template_directory() . "/gallery-item-avatar-choice.php";
				if (file_exists($theFile)) include($theFile);
			} ?>
		</div>
	</div>

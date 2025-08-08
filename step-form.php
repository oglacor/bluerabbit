<?php $step_editor_id = "step-content-".$s->step_id; ?>
<div class="layer background fixed sq-full" onClick="tinymce.remove('#<?= $step_editor_id;?>');unloadContent();"></div>
<div class="folder">
	<div class="step-form white-bg max-w-900 min-w-700 boxed relative layer base folder-page active" id="step-<?= $s->step_id; ?>">
		<input type="hidden" class="step-id-value" id="step-id" value="<?= $s->step_id; ?>">
		<input type="hidden" value="<?= $s->step_order; ?>" class="step-order">
		<div class="padding-10 teal-bg-400 white-color w-full sticky top left layer base">
			<h3 class="font _24">
				<span class="icon icon-objectives"></span>
				<span class="font w300"><?= __("Edit Step","bluerabbit"); ?></span>
				<span class="font w900"><?= "[$s->step_order] - $s->step_title"; ?></span>
			</h3>
			<button class="icon-button red-bg-500 absolute top-5 right-5 sq-36 font _18" onClick="tinymce.remove('#<?= $step_editor_id;?>');unloadContent();"><span class="icon icon-cancel"></span></button>
		</div>
		<table class="table w-full" cellpadding="0">
			<thead>
				<tr class="font _12 grey-600">
					<td class="text-right w-200"><?php _e('Setting','bluerabbit'); ?></td>
					<td><?php _e('Value','bluerabbit'); ?></td>
				</tr>
			</thead>
			<tbody class="font _16">
				<tr>
					<td class="text-right line-60">
						<?= __("Label","bluerabbit"); ?><br>
						<span class="font _12 grey-400"><?= __("The reference to the step, only seen in admin","bluerabbit"); ?></span>
					</td>
					<td>
						<input id="step-title-<?=$s->step_id; ?>" type="text" maxlength="255" class="form-ui step-title white-bg w-full font _20 grey-900" value="<?= $s->step_title; ?>">
					</td>
				</tr>
				<tr>
					<td class="text-right line-60">
						<?= __("Type","bluerabbit"); ?><br>
						<span class="font _12 grey-400"><?= __("The type of action the player will do","bluerabbit"); ?></span>
					</td>
					<td>
						<select id="step-type-<?=$s->step_id;?>" class="form-ui step-type white-bg w-full font _20" onChange="checkStepType();">
							<!-- Content -->
							<option value="dialogue" <?= ($s->step_type=='dialogue' || $s->step_type=='instruction' ) ? "selected" : '';?> ><?= __("Dialogue","bluerabbit"); ?></option>
							<option value="open" <?= ($s->step_type=='open') ? "selected" : '';?> ><?= __("Open Field","bluerabbit"); ?></option>
							<option value="jump" <?= $s->step_type=='jump' ? "selected" : '';?> ><?= __("Jump to step","bluerabbit"); ?></option>
							<option value="system" <?= $s->step_type=='system' ? "selected" : '';?> ><?= __("System Message","bluerabbit"); ?></option>
							<option value="win" <?= $s->step_type=='win' ? "selected" : '';?> ><?= __("Win","bluerabbit"); ?></option>
							<option value="fail" <?= $s->step_type=='fail' ? "selected" : '';?> ><?= __("Fail","bluerabbit"); ?></option>
							<option value="video" <?= $s->step_type=='video' ? "selected" : '';?> ><?= __("Video","bluerabbit"); ?></option>
							<!-- Items -->
							<option value="item-grab" <?= $s->step_type=='item-grab' ? "selected" : '';?> ><?= __("Find item in path","bluerabbit"); ?></option>
							<option value="item-req" <?= $s->step_type=='item-req' ? "selected" : '';?> ><?= __("Require item to advance","bluerabbit"); ?></option>
							<!-- Achievement paths -->
							<option value="path-choice" <?= $s->step_type=='path-choice' ? "selected" : '';?> ><?= __("Choose a path","bluerabbit"); ?></option>
							<!-- Profile input -->
							<option value="choose-nickname" <?= $s->step_type=='choose-nickname' ? "selected" : '';?> ><?= __("Choose Nickname","bluerabbit"); ?></option>
							<option value="choose-avatar" <?= $s->step_type=='choose-avatar' ? "selected" : '';?> ><?= __("Choose Avatar","bluerabbit"); ?></option>
						</select>
					</td>
				</tr>
				<tr class="conditional-display dialogue-display">
					<td class="text-right line-60  ">
						<?= __("Character Position","bluerabbit"); ?><br>
						<span class="font _12 grey-400"><?= __("On which side is the character.","bluerabbit"); ?></span>
					</td>
					<td>
						<select id="step-attach-<?=$s->step_id;?>" class="form-ui step-attach white-bg w-full font _20">
							<option value="none" <?= $s->step_attach=='none' ? "selected" : '';?> ><?= __("No one. Just describing the scene.","bluerabbit"); ?></option>
							<option value="left" <?= $s->step_attach=='left' ? "selected" : '';?> ><?= __("On the left","bluerabbit"); ?></option>
							<option value="right" <?= $s->step_attach=='right' ? "selected" : '';?> ><?= __("On the right","bluerabbit"); ?></option>
						</select>
					</td>
				</tr>
				<tr class="conditional-display dialogue-display">
					<td class="text-right line-60  ">
						<?= __("Character Name","bluerabbit"); ?><br>
						<span class="font _12 grey-400"><?= __("Optional. The name of the character talking.","bluerabbit"); ?></span>
					</td>
					<td>
						<input id="step-character-name-<?=$s->step_id; ?>" type="text" maxlength="255" class="form-ui step-title white-bg w-full font _20 grey-900" value="<?= $s->step_character_name; ?>">
					</td>
				</tr>
				<tr class="conditional-display dialogue-display open-display jump-display system-display win-display fail-display item-req-display item-grab-display">
					<td class="text-right ">
						<span class="conditional-display system-display">
							<span class="font _16 block"><?= __("Content","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("The text explaining something. Followed by an OK button","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display dialogue-display">
							<span class="font _16 block"><?= __("Dialogue","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("The dialogue of the characters. Align left or right accordingly","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display open-display">
							<span class="font _16 block"><?= __("Open Field","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("The instructions for the player","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display choice-display jump-display">
							<span class="font _16 block"><?= __("Question","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("The question the players must answer with their choice","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display item-grab-display">
							<span class="font _16 block"><?= __("About the item","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("What's the relevance of this item (optional)","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display item-req-display">
							<span class="font _16 block"><?= __("Clue or Info","bluerabbit"); ?></span>
							<span class="font _12 grey-400 block"><?= __("Information about the item they need to search in their backpacks","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display win-display">
							<span class="font _16 block"><?= __("Message","bluerabbit"); ?></span>
							<span class="font _12 green-300 block"><?= __("The message the players will read when they WIN the quest","bluerabbit"); ?></span>
						</span>
						<span class="conditional-display fail-display">
							<span class="font _16 block"><?= __("Message","bluerabbit"); ?></span>
							<span class="font _12 red-300 block"><?= __("The message the players will read when they FAIL the quest","bluerabbit"); ?></span>
						</span>
					</td>
					<td>
						<?php
						$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
						wp_editor($s->step_content, $step_editor_id, $wp_editor_settings); 
						?>
					</td>
				</tr>
				<tr class="conditional-display video-display">
					<td class="text-center">
						<h3 class="padding-5"><?= __("Select the video","bluerabbit"); ?></h3>
					</td>
					<td>
						<div class="gallery">

							<?php $thumb_id = 'the_step_image_'.$s->step_id; ?>
							<div class="gallery-item setting">
								<div class="background" onClick="showWPUploadVideo('<?= $thumb_id;?>');" id="<?= $thumb_id;?>_thumb">
									<?php $mime = (wp_check_filetype($s->step_image));?>
									
									<video id="<?= $thumb_id;?>_thumb_video" class="gallery-item-video <?= strstr($mime['type'], "video") ? 'active' : ''; ?>" controls>
										<source src="<?= $s->step_image; ?>">
									</video>
								</div>
								<div class="gallery-item-options relative">
									<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUploadVideo('<?= $thumb_id;?>');"><span class="icon icon-image"></span></button>
									<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?= $thumb_id;?>');"> <span class="icon icon-trash"></span> </button>
									<input type="hidden" id="<?= $thumb_id;?>" value="<?= $s->step_image; ?>"/>
								</div>
							</div>





						</div>
					</td>
				</tr>
				<tr class="conditional-display choose-nickname-display">
					<td class="text-center" colspan="2">
						<h3 class="padding-5"><?= __("The system prompts the player to input their First and Last names (it can be their nickname for the game) ","bluerabbit"); ?></h3>
					</td>
				</tr>
				<tr class="conditional-display choose-avatar-display">
					<td class="text-center" colspan="2">
						<h3 class="padding-5"><?= __("Upload the images of the avatars available to the players","bluerabbit"); ?></h3>
					</td>
				</tr>
				<tr class="conditional-display path-choice-display">
					<td class="text-right line-60">
						<?= __("Path Group","bluerabbit"); ?><br>
						<span class="font _12 grey-400"><?= __("What achievement group do you want the players to choose from","bluerabbit"); ?></span>
					</td>
					<td>
						<select id="the_step_achievement_group" class="form-ui step-type white-bg w-full font _20">
							<option class="font w900" value="" <?= $s->step_achievement_group == "" ? "selected" : ""; ?>>No Group</option>
							<option class="red-bg-400 white-color" value="A" <?= $s->step_achievement_group == "A" ? "selected" : ""; ?>>Group A</option>
							<option class="orange-bg-400 white-color" value="B" <?= $s->step_achievement_group == "B" ? "selected" : ""; ?>>Group B</option>
							<option class="amber-bg-400 white-color" value="C" <?= $s->step_achievement_group == "C" ? "selected" : ""; ?>>Group C</option>
							<option class="green-bg-400 white-color" value="D" <?= $s->step_achievement_group == "D" ? "selected" : ""; ?>>Group D</option>
							<option class="teal-bg-400 white-color" value="E" <?= $s->step_achievement_group == "E" ? "selected" : ""; ?>>Group E</option>
							<option class="cyan-bg-400 white-color" value="F" <?= $s->step_achievement_group == "F" ? "selected" : ""; ?>>Group F</option>
							<option class="blue-bg-400 white-color" value="G" <?= $s->step_achievement_group == "G" ? "selected" : ""; ?>>Group G</option>
							<option class="indigo-bg-400 white-color" value="H" <?= $s->step_achievement_group == "H" ? "selected" : ""; ?>>Group H</option>
							<option class="deep-purple-bg-400 white-color" value="I" <?= $s->step_achievement_group == "I" ? "selected" : ""; ?>>Group I</option>
							<option class="pink-bg-400 white-color" value="J" <?= $s->step_achievement_group == "J" ? "selected" : ""; ?>>Group J</option>
						</select>
					</td>
				</tr>
				<tr class="conditional-display dialogue-display">
					<td class="text-right v-top">
						<span class="font _16 block"><?= __("Character image","bluerabbit");?></span>
						<span class="font _12 block grey-500">
							<?php _e("The image of the character. Best at 9:16 proportions.","bluerabbit"); ?>
						</span>
					</td>
					<td>
						<div class="gallery">
							<?php insertGalleryItem('the_step_character_image', $s->step_character_image); ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="text-right v-top">
						<span class="font _16 block"><?= __("Background","bluerabbit");?></span>
						<span class="font _12 block grey-500">
							<?php _e("Background image of the scene.","bluerabbit"); ?>
						</span>
					</td>
					<td>
						<div class="gallery">
							<?php insertGalleryItem('the_step_background', $s->step_background); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="step-form white-bg max-w-900 min-w-700 boxed relative layer base folder-page step-buttons" id="step-buttons-form-container">
	</div>
	<div class=" text-center padding-10 teal-bg-400 white-color w-full sticky bottom layer base max-w-900 min-w-700 boxed">
		<button class="form-ui green-bg-400 white-color" onClick="updateStep(<?= $s->step_id; ?>);">
			<span class="icon icon-check"></span><?php _e("Update Step","bluerabbit"); ?>
		</button>
	</div>
</div>
<script>
	checkStepType();
	tinyMCE.execCommand( 'mceAddEditor', true, '<?= $step_editor_id; ?>' );
</script>

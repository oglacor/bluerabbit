	<div class="padding-10 blue-bg-700 white-color w-full sticky top layer overlay">
		<button class="button form-ui blue-bg-800 pull-right layer relative base" onClick="addStepButton();"><?= __("Add button","bluerabbit"); ?></button>
		<h2 class="font _24 w900">
			<span class="icon icon-objectives"></span>
			<?= __("Buttons","bluerabbit"); ?>
		</h2>
		<h3 class="font _18 w200 opacity-70">
			<?= __("The options available to the player","bluerabbit"); ?>
		</h3>
	</div>

	<div class="padding-10 white-color w-full relative">
		<?php $buttons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE step_id=$s->step_id AND button_status='publish' AND button_type='jump'"); ?>
		<?php $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$s->quest_id AND adventure_id=$s->adventure_id AND step_status='publish' AND step_id !=$s->step_id ORDER BY step_order, step_id"); ?>
				
		<table class="table">
			<thead class="black-color opacity-80 text-center">
				<tr>
					<td><?= __("Image","bluerabbit"); ?></td>
					<td><?= __("Content","bluerabbit"); ?></td>
					<td><?= __("Actions","bluerabbit"); ?></td>
				</tr>
			</thead>
			<tbody id="step-buttons-list" class="step-buttons-list">
				<?php foreach($buttons as $key=>$btn){ ?>
					<tr id="step-button-<?=$btn->button_id; ?>" >
						<td class="w-250">
							<div class="gallery margin-10">
								<?php insertGalleryItem('the_step_button_image-'.$btn->button_id, $btn->button_image); ?>
							</div>
						</td>
						<td>
							<input type="hidden"  class="step_button_id" value="<?=$btn->button_id; ?>">
							<div class="input-group">
								<label class=""><?= __("Label","bluerabbit"); ?></label>
								<input type="text" class="form-ui button_text white-bg w-full" onChange="updateStepButton(<?= $btn->button_id; ?>);" value="<?=$btn->button_text; ?>">
							</div>
							<div class="input-group">
								<label class=""><?= __("Jump to","bluerabbit"); ?></label>
								<select class="form-ui button_step_next" onChange="updateStepButton(<?=$btn->button_id; ?>);">
									<?php foreach($steps as $sb){ ?>
										<option value="<?=$sb->step_order;?>" <?= $btn->button_step_next==$sb->step_order ? 'selected' : ''; ?>><?="[$sb->step_order] $sb->step_title";?></option>
									<?php } ?>
								</select>
							</div>
						</td>
						<td>
							<button class="icon-button blue-bg-400 font _18 sq-30" onClick="updateStepButton(<?=$btn->button_id; ?>);"><span class="icon icon-check"></span></button>
							<button class="icon-button red-bg-400 font _18 sq-30" onClick="removeStepButton(<?=$btn->button_id; ?>);"><span class="icon icon-trash"></span></button>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>










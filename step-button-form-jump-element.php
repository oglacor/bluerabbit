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
								<input type="text" class="form-ui button_text white-bg w-full" onChange="updateStepButton(<?=$btn->button_id; ?>);" value="<?=$btn->button_text; ?>">
							</div>
							<div class="input-group">
								<label class=""><?= __("Jump to ","bluerabbit"); ?></label>
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

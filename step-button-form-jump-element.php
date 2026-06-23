					<tr id="step-button-<?= $btn->button_id; ?>">
						<td style="width:200px">
							<div class="gallery margin-10">
								<?php BR_Utils::instance()->insertGalleryItem('the_step_button_image-' . $btn->button_id, $btn->button_image); ?>
							</div>
						</td>
						<td>
							<input type="hidden" class="step_button_id" value="<?= $btn->button_id; ?>">
							<div class="br-form-group" style="margin-bottom:8px">
								<label class="br-form-label"><?= __("Label", "bluerabbit"); ?></label>
								<input type="text" class="br-input button_text" onChange="updateStepButton(<?= $btn->button_id; ?>);" value="<?= esc_attr($btn->button_text); ?>">
							</div>
							<div class="br-form-group" style="margin-bottom:0">
								<label class="br-form-label"><?= __("Jump to", "bluerabbit"); ?></label>
								<select class="br-input button_step_next" onChange="updateStepButton(<?= $btn->button_id; ?>);">
									<?php foreach ($steps as $sb) { ?>
									<option value="<?= $sb->step_order; ?>" <?= $btn->button_step_next == $sb->step_order ? 'selected' : ''; ?>>[<?= $sb->step_order; ?>] <?= esc_html($sb->step_title); ?></option>
									<?php } ?>
								</select>
							</div>
						</td>
						<td>
							<div class="br-actions" style="flex-direction:column">
								<button class="br-btn br-btn-green" style="padding:4px 8px" onClick="updateStepButton(<?= $btn->button_id; ?>);"><span class="icon icon-check"></span></button>
								<button class="br-btn br-btn-red" style="padding:4px 8px" onClick="removeStepButton(<?= $btn->button_id; ?>);"><span class="icon icon-trash"></span></button>
							</div>
						</td>
					</tr>

	<div style="padding:14px 20px;border-bottom:1px solid rgba(28,194,235,0.12);display:flex;align-items:center;justify-content:space-between">
		<h3 class="br-panel-title" style="margin:0"><span class="icon icon-objectives"></span> <?= __("Buttons", "bluerabbit"); ?></h3>
		<button class="br-btn" onClick="addStepButton();"><span class="icon icon-add"></span> <?= __("Add button", "bluerabbit"); ?></button>
	</div>
	<div style="padding:14px 20px">
		<span class="br-form-hint" style="display:block;margin-bottom:12px"><?= __("The options available to the player", "bluerabbit"); ?></span>
		<?php $buttons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE step_id=$s->step_id AND button_status='publish' AND button_type='jump'"); ?>
		<?php $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$s->quest_id AND adventure_id=$s->adventure_id AND step_status='publish' AND step_id !=$s->step_id ORDER BY step_order, step_id"); ?>

		<table class="br-table">
			<thead>
				<tr>
					<th style="width:200px"><?= __("Image", "bluerabbit"); ?></th>
					<th><?= __("Content", "bluerabbit"); ?></th>
					<th style="width:100px"><?= __("Actions", "bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody id="step-buttons-list" class="step-buttons-list">
				<?php foreach ($buttons as $key => $btn) { ?>
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
				<?php } ?>
			</tbody>
		</table>
	</div>

				<h3 class="br-panel-title"><span class="icon icon-config"></span> <?= __("Mechanics", "bluerabbit"); ?></h3>
				<?php $tabis = BR_Tabi::instance()->getTabis($adv_parent_id); ?>

				<div class="br-form-group">
					<label class="br-form-label"><?= __("Tabi", "bluerabbit"); ?></label>
					<select class="br-input" id="the_tabi_id">
						<option value=""><?= __("None", "bluerabbit"); ?></option>
						<?php if ($tabis) { foreach ($tabis as $tabi) { ?>
						<option value="<?= $tabi->tabi_id; ?>" <?= (isset($quest->tabi_id) && $quest->tabi_id == $tabi->tabi_id) ? 'selected' : ''; ?>><?= esc_html($tabi->tabi_name); ?></option>
						<?php } } ?>
					</select>
				</div>

				<div class="br-form-grid">
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Level", "bluerabbit"); ?></label>
						<input class="br-input" type="number" max="99" min="1" id="the_quest_level"
							   value="<?= isset($quest->mech_level) ? $quest->mech_level : 1; ?>"
							   onBlur="checkLevel('#the_quest_level');" onChange="checkLevel('#the_quest_level');">
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= $xp_long_label; ?></label>
						<input class="br-input" type="number" min="0" id="the_quest_xp"
							   value="<?= isset($quest->mech_xp) ? $quest->mech_xp : 1; ?>">
					</div>
				</div>

				<div class="br-form-grid">
					<div class="br-form-group">
						<label class="br-form-label"><?= $bloo_long_label; ?></label>
						<input class="br-input" type="number" min="0" id="the_quest_bloo"
							   value="<?= isset($quest->mech_bloo) ? $quest->mech_bloo : 1; ?>">
					</div>
					<?php if (isset($use_encounters)) { ?>
					<div class="br-form-group">
						<label class="br-form-label"><?= $ep_long_label; ?></label>
						<input class="br-input" type="number" min="0" id="the_quest_ep"
							   value="<?= isset($quest->mech_ep) ? $quest->mech_ep : 1; ?>">
					</div>
					<?php } ?>
				</div>

				<div class="br-form-grid">
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Start Date", "bluerabbit"); ?></label>
						<?php
						$pretty_start_date = (isset($quest) && $quest->mech_start_date != "0000-00-00 00:00:00" && $quest->mech_start_date != null)
							? date('Y/m/d H:i', strtotime($quest->mech_start_date)) : '';
						?>
						<input class="br-input the_start_date datetimepicker" autocomplete="off" id="the_quest_start_date"
							   value="<?= $pretty_start_date; ?>" placeholder="<?= __('No start date', 'bluerabbit'); ?>">
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Deadline", "bluerabbit"); ?></label>
						<?php
						$pretty_deadline = (isset($quest) && $quest->mech_deadline != "0000-00-00 00:00:00" && $quest->mech_deadline != null)
							? date('Y/m/d H:i', strtotime($quest->mech_deadline)) : '';
						?>
						<input class="br-input the_deadline datetimepicker" autocomplete="off" id="the_quest_deadline"
							   value="<?= $pretty_deadline; ?>" placeholder="<?= __('No deadline', 'bluerabbit'); ?>">
					</div>
				</div>

				<div class="br-form-grid">
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Optional", "bluerabbit"); ?></label>
						<label class="br-btn br-mech-checkbox-btn <?= (isset($quest->mech_optional) && $quest->mech_optional) ? 'is-checked' : ''; ?>" data-checked-class="is-checked">
							<input type="checkbox" id="the_quest_optional" <?= (isset($quest->mech_optional) && $quest->mech_optional) ? 'checked' : ''; ?>>
							<?= __("Doesn't count toward Tabi unlock", "bluerabbit"); ?>
						</label>
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Validate", "bluerabbit"); ?></label>
						<label class="br-btn br-mech-checkbox-btn <?= (isset($quest->mech_validate) && $quest->mech_validate) ? 'is-checked-green' : ''; ?>" data-checked-class="is-checked-green">
							<input type="checkbox" id="the_quest_validate" <?= (isset($quest->mech_validate) && $quest->mech_validate) ? 'checked' : ''; ?>>
							<?= __("Require validation before awarding", "bluerabbit"); ?>
						</label>
					</div>
				</div>

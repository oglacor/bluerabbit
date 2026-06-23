				<h3 class="br-panel-title"><span class="icon icon-config"></span> <?= __("Additional Mechanics", "bluerabbit"); ?></h3>
				<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("These are completely optional", "bluerabbit"); ?></span>

				<div class="br-form-grid">
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Deadline Cost", "bluerabbit"); ?></label>
						<input class="br-input" type="number" id="the_quest_deadline_cost"
							   value="<?= isset($quest) ? $quest->mech_deadline_cost : ''; ?>"
							   placeholder="0">
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Unlock Cost", "bluerabbit"); ?></label>
						<input class="br-input" type="number" id="the_quest_unlock_cost"
							   value="<?= isset($quest) ? $quest->mech_unlock_cost : ''; ?>"
							   placeholder="0">
					</div>
				</div>

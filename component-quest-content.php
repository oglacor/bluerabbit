				<h3 class="br-panel-title"><span class="icon icon-quest"></span> <?= __("Content", "bluerabbit"); ?></h3>

				<div class="br-form-group">
					<label class="br-form-label"><?= __("Short Description", "bluerabbit"); ?></label>
					<span class="br-form-hint"><?= __("Players will read this in the preview when clicking in the journey", "bluerabbit"); ?></span>
					<textarea class="br-input" rows="3" maxlength="200" id="the_quest_secondary_headline"
							  placeholder="<?= __('Brief description of the quest', 'bluerabbit'); ?>"><?= isset($quest) ? $quest->quest_secondary_headline : ''; ?></textarea>
				</div>

				<div class="br-form-group">
					<label class="br-form-label"><?= __("Instructions", "bluerabbit"); ?></label>
					<span class="br-form-hint"><?= __("Describe what the players must do to earn the reward", "bluerabbit"); ?></span>
					<?php
					$wp_editor_settings = ($roles[0] == "administrator")
						? ['quicktags' => true, 'editor_height' => 350]
						: ['quicktags' => false, 'editor_height' => 350];
					wp_editor(isset($quest) ? $quest->quest_content : '', 'the_quest_content', $wp_editor_settings);
					?>
				</div>

				<div class="br-form-group">
					<label class="br-form-label"><?= __("Success Message", "bluerabbit"); ?></label>
					<span class="br-form-hint"><?= __("Reward your players with information after completing this quest", "bluerabbit"); ?></span>
					<?php
					wp_editor(isset($quest) ? $quest->quest_success_message : '', 'the_quest_success_message', $wp_editor_settings);
					?>
				</div>

				<h3 class="br-panel-title" id="tutorial-achievement-reward"><span class="icon icon-achievement"></span> <?= __("Achievement Reward", "bluerabbit"); ?></h3>
				<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("There must be achievements", "bluerabbit"); ?></span>

				<div class="br-form-component">
					<?php if (isset($achievements['publish'])) { ?>
					<ul class="selectable-list grid" id="the_mech_achievement_reward">
						<?php foreach ($achievements['publish'] as $aKey => $a) {
							if ($a->achievement_display == 'badge' || $a->achievement_display == 'path') {
								$status = '';
								if (isset($quest->mech_achievement_reward) && $quest->mech_achievement_reward == $a->achievement_id) $status = 'active';
								elseif (isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id) $status = 'hidden';
						?>
						<li id="achievement-reward-<?= $a->achievement_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleSingleReq('#achievement-reward-<?= $a->achievement_id; ?>'); checkPublishFor(<?= $a->achievement_id; ?>);" style="background-image: url(<?= $a->achievement_badge; ?>);">
							<input type="hidden" class="achievement-reward-id" value="<?= $a->achievement_id; ?>">
							<div class="layer background absolute sq-full top left color-overlay"></div>
							<span class="button-icon green-bg-400 active-content font _18 absolute top-10 right-10"><span class="icon icon-check"></span></span>
							<div class="layer base absolute perfect-center text-center achievement-name"><span class="font _18"><?= $a->achievement_name; ?></span></div>
						</li>
						<?php } } ?>
					</ul>
					<?php } else { ?>
					<div class="br-empty" style="padding:20px">
						<h3><?= __("No achievements available", "bluerabbit"); ?></h3>
						<a class="br-btn br-btn-amber" href="<?= get_bloginfo('url') . "/new-achievement/?adventure_id=$adventure->adventure_id"; ?>"><?= __("Add new achievement", "bluerabbit"); ?></a>
					</div>
					<?php } ?>
				</div>

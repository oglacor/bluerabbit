				<h3 class="br-panel-title" id="tutorial-quests-required"><span class="icon icon-lock"></span> <?= __("Quests Required", "bluerabbit"); ?></h3>
				<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("Required quests must be the same level or lower as this one", "bluerabbit"); ?></span>

				<div class="br-form-component">
					<?php if (isset($quests)) { ?>
					<ul class="selectable-list grid select-multiple" id="quests-reqs">
						<?php foreach ($quests as $key => $q) {
							if ((isset($quest) && $q->quest_id != $quest->quest_id) && in_array($q->quest_type, ['quest', 'challenge', 'survey']) && $q->quest_status == 'publish') {
								$status = (!empty($reqs['quests']) && in_array($q->quest_id, $reqs['quests'])) ? 'active' : '';
						?>
						<li id="req-<?= $q->quest_id; ?>" class="<?= $status; ?> border border-all border-2 white-bg" onClick="toggleReq('#req-<?= $q->quest_id; ?>');" style="<?= br_color_attr($q->quest_color, 'border', true) ?> background-image: url(<?= $q->mech_badge; ?>);">
							<div class="layer background absolute sq-full top left color-overlay"></div>
							<span class="button-icon green-bg-400 active-content font _18 absolute top-10 right-10"><span class="icon icon-check"></span></span>
							<div class="layer base absolute perfect-center text-center achievement-name"><span class="font _18"><?= $q->quest_title; ?></span></div>
							<input type="hidden" class="reqs-id" value="<?= $q->quest_id; ?>">
							<input type="hidden" class="reqs-level" value="<?= $q->mech_level; ?>">
						</li>
						<?php } } ?>
					</ul>
					<?php } else { ?>
					<p style="color:rgba(255,255,255,0.35);text-align:center;padding:16px"><?= __("No quests available", "bluerabbit"); ?></p>
					<?php } ?>
				</div>

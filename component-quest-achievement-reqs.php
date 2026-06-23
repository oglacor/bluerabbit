				<h3 class="br-panel-title" id="tutorial-achievements-required"><span class="icon icon-achievement"></span> <?= __("Achievements Required", "bluerabbit"); ?></h3>

				<div class="br-form-component">
					<?php if (isset($achievements['publish'])) { ?>
					<ul class="selectable-list grid select-multiple" id="quest-achievement-reqs">
						<?php foreach ($achievements['publish'] as $key => $a) {
							if ($a->achievement_display == 'badge') {
								$status = (!empty($reqs['achievements']) && in_array($a->achievement_id, $reqs['achievements'])) ? 'active' : '';
						?>
						<li id="req-achievement-<?= $a->achievement_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleReq('#req-achievement-<?= $a->achievement_id; ?>'); checkPublishFor(<?= $a->achievement_id; ?>);" style="background-image: url(<?= $a->achievement_badge; ?>);">
							<input type="hidden" class="reqs-id" value="<?= $a->achievement_id; ?>">
							<input type="hidden" class="reqs-ref-id" value="<?= $a->ref_id ?? ''; ?>">
							<div class="layer background absolute sq-full top left color-overlay"></div>
							<span class="button-icon green-bg-400 active-content font _18 absolute top-10 right-10"><span class="icon icon-check"></span></span>
							<div class="layer base absolute perfect-center text-center achievement-name"><span class="font _18"><?= $a->achievement_name; ?></span></div>
						</li>
						<?php } } ?>
					</ul>
					<?php } else { ?>
					<p style="color:rgba(255,255,255,0.35);text-align:center;padding:16px"><?= __("No achievements available", "bluerabbit"); ?></p>
					<?php } ?>
				</div>

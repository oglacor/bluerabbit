				<h3 class="br-panel-title" id="tutorial-key-item-required"><span class="icon icon-key"></span> <?= __("Key Item Required", "bluerabbit"); ?></h3>
				<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("There should be Key Items in the shop", "bluerabbit"); ?></span>

				<div class="br-form-component">
					<?php if (isset($items['key'])) { ?>
					<ul class="selectable-list grid" id="item_required">
						<?php foreach ($items['key'] as $key => $i) {
							$status = (!empty($reqs['items']) && in_array($i->item_id, $reqs['items'])) ? 'active' : '';
						?>
						<li id="item-<?= $i->item_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleSingleReq('#item-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
							<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
							<div class="layer background absolute sq-full top left color-overlay"></div>
							<span class="button-icon green-bg-400 active-content font _18 absolute top-10 right-10"><span class="icon icon-check"></span></span>
							<div class="layer base absolute perfect-center text-center achievement-name"><span class="font _18"><?= $i->item_name; ?></span></div>
						</li>
						<?php } ?>
					</ul>
					<?php } else { ?>
					<div class="br-empty" style="padding:20px">
						<h3><?= __("No key items available", "bluerabbit"); ?></h3>
						<a class="br-btn" href="<?= get_bloginfo('url') . "/new-item/?adventure_id=$adventure->adventure_id&type=key"; ?>"><?= __("Add new key item", "bluerabbit"); ?></a>
					</div>
					<?php } ?>
				</div>

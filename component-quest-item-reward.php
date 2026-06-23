				<h3 class="br-panel-title" id="tutorial-item-reward"><span class="icon icon-achievement"></span> <?= __("Item Reward", "bluerabbit"); ?></h3>
				<span class="br-form-hint" style="margin-top:-12px;margin-bottom:16px;display:block"><?= __("There must be item rewards in the shop", "bluerabbit"); ?></span>

				<div class="br-form-component">
					<?php if (!empty($items['reward']) || !empty($items['key']) || !empty($items['tabi-piece'])) { ?>
					<ul class="selectable-list grid" id="mech_item_reward">
						<?php foreach (['reward', 'key', 'tabi-piece'] as $item_type) {
							if (empty($items[$item_type])) continue;
							foreach ($items[$item_type] as $key => $i) {
								$status = (isset($quest->mech_item_reward) && $quest->mech_item_reward == $i->item_id) ? 'active' : '';
						?>
						<li id="item-reward-<?= $i->item_id; ?>" class="item <?= $status; ?> pink-border-400 border border-all border-2" onClick="toggleSingleReq('#item-reward-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
							<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
							<div class="layer background absolute sq-full top left color-overlay"></div>
							<span class="button-icon green-bg-400 active-content font _18 absolute top-10 right-10"><span class="icon icon-check"></span></span>
							<div class="layer base absolute perfect-center text-center achievement-name"><span class="font _18"><?= $i->item_name; ?></span></div>
						</li>
						<?php } } ?>
					</ul>
					<?php } else { ?>
					<div class="br-empty" style="padding:20px">
						<h3><?= __("No reward items available", "bluerabbit"); ?></h3>
						<a class="br-btn br-btn-green" href="<?= get_bloginfo('url') . "/new-item/?adventure_id=$adventure->adventure_id&type=reward"; ?>"><?= __("Add new reward item", "bluerabbit"); ?></a>
					</div>
					<?php } ?>
				</div>

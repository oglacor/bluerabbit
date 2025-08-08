					<div class="highlight padding-10 grey-bg-100" id="tutorial-item-reward">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  teal-bg-400">
								<span class="icon icon-achievement"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line teal-400"><?php _e("Item Reward","bluerabbit"); ?></span>
								<span class="line font _14 grey-500"><?php _e("There must be item rewards in the shop","bluerabbit"); ?></span>
							</span>
						</span>
					</div>
					<div class="content padding-10">
						<?php if(!empty($items['reward']) || !empty($items['key']) || !empty($items['tabi-piece'])){ ?>
							<ul class="selectable-list grid" id="mech_item_reward">
								<?php foreach ($items['reward'] as $key=>$i){ ?>
									<?php if($quest->mech_item_reward == $i->item_id){ $status='active';}else{$status="";} ?>
									<li id="item-reward-<?= $i->item_id; ?>" class="item <?= $status; ?> pink-border-400 border border-all border-2" onClick="toggleSingleReq('#item-reward-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
										<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
										<div class="layer background absolute sq-full top left color-overlay"></div>
										<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
											<span class="icon icon-check"></span>
										</span>
										<div class="layer base absolute perfect-center text-center achievement-name">
											<span class="font _18"><?= $i->item_name; ?></span>
										</div>
									</li>
								<?php }	?>
								<?php foreach ($items['key'] as $key=>$i){ ?>
									<?php if($quest->mech_item_reward == $i->item_id){ $status='active';}else{$status="";} ?>
									<li id="item-reward-<?= $i->item_id; ?>" class="item <?= $status; ?> pink-border-400 border border-all border-2" onClick="toggleSingleReq('#item-reward-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
										<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
										<div class="layer background absolute sq-full top left color-overlay"></div>
										<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
											<span class="icon icon-check"></span>
										</span>
										<div class="layer base absolute perfect-center text-center achievement-name">
											<span class="font _18"><?= $i->item_name; ?></span>
										</div>
									</li>
								<?php }	?>
								<?php foreach ($items['tabi-piece'] as $key=>$i){ ?>
									<?php if($quest->mech_item_reward == $i->item_id){ $status='active';}else{$status="";} ?>
									<li id="item-reward-<?= $i->item_id; ?>" class="item <?= $status; ?> pink-border-400 border border-all border-2" onClick="toggleSingleReq('#item-reward-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
										<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
										<div class="layer background absolute sq-full top left color-overlay"></div>
										<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
											<span class="icon icon-check"></span>
										</span>
										<div class="layer base absolute perfect-center text-center achievement-name">
											<span class="font _18"><?= $i->item_name; ?></span>
										</div>
									</li>
								<?php }	?>
							</ul>
						<?php }else{ ?>
							<div class="text-center">
								<h4 class="margin-5 font _24 w900 grey-400"><?php _e("No reward items available",'bluerabbit'); ?></h4>
								<a class="form-ui teal-bg-600" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&type=reward"; ?>"><?php _e("Add new reward item",'bluerabbit'); ?></a>
							</div>
						<?php } ?>
					</div>

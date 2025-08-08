					<div class="highlight padding-10 grey-bg-100" id="tutorial-achievement-reward">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  purple-bg-400">
								<span class="icon icon-achievement"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line purple-400"><?php _e("Achievement Reward","bluerabbit"); ?></span>
								<span class="line font _14 grey-500"><?php _e("There must be achievements","bluerabbit"); ?></span>
							</span>
						</span>
					</div>
					<div class="content padding-10">
						<?php if(isset($achievements['publish'])){ ?>
							<ul class="selectable-list grid" id="the_mech_achievement_reward">
								<?php foreach ($achievements['publish'] as $aKey=>$a){ ?>
									<?php if($a->achievement_display=='badge' || $a->achievement_display=='path'){ ?>
										<?php
											if(isset($quest->mech_achievement_reward) && $quest->mech_achievement_reward == $a->achievement_id){
												$status = "active";
											}elseif(isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id){
												$status = "hidden";
											}else{
												$status = "";
											}
										?>
										<li id="achievement-reward-<?= $a->achievement_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleSingleReq('#achievement-reward-<?= $a->achievement_id; ?>'); checkPublishFor(<?= $a->achievement_id; ?>);" style="background-image: url(<?= $a->achievement_badge; ?>);">
											<input type="hidden" class="achievement-reward-id" value="<?= $a->achievement_id; ?>">
											<div class="layer background absolute sq-full top left color-overlay"></div>
											<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
												<span class="icon icon-check"></span>
											</span>
											<div class="layer base absolute perfect-center text-center achievement-name">
												<span class="font _18"><?= $a->achievement_name; ?></span>
											</div>
										</li>
									<?php }	?>
								<?php }	?>
							</ul>
						<?php }else{ ?>
							<div class="text-center">
								<h4 class="margin-5 font _24 w900 grey-400"><?php _e("No achievements available",'bluerabbit'); ?></h4>
								<a class="form-ui purple-bg-600" href="<?= get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id"; ?>"><?php _e("Add new achievement",'bluerabbit'); ?></a>
							</div>
						<?php } ?>
					</div>

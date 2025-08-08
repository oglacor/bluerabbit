					<div class="highlight padding-10 grey-bg-100" id="tutorial-quests-required">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  deep-purple-bg-800">
								<span class="icon icon-lock"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line deep-purple-800"><?php _e("Quests Required","bluerabbit"); ?></span>
								<span class="line font _14 grey-500"><?php _e("Required quests must be the same level or lower as this one","bluerabbit"); ?></span>
							</span>
						</span>
					</div>
					<div class="content">
						<?php if(isset($quests)){ ?>
							<ul class="selectable-list grid select-multiple" id="quests-reqs">
								<?php
								foreach ($quests as $key=>$q){
									$status = "";
									if((isset($quest) && $q->quest_id != $quest->quest_id) && ($q->quest_type == "quest" || $q->quest_type == "challenge" || $q->quest_type == "survey") && $q->quest_status == 'publish'){
										if(!empty($reqs['quests']) ){
											if(in_array($q->quest_id,$reqs['quests'])){
												$status = "active";
											}
										}
										?>
								
										<li id="req-<?= $q->quest_id; ?>" class="<?= $status; ?> <?=$q->quest_color;?>-border-400 border border-all border-2 white-bg" onClick="toggleReq('#req-<?= $q->quest_id; ?>');" style="background-image: url(<?= $q->mech_badge; ?>);">
											<div class="layer background absolute sq-full top left color-overlay"></div>
											<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
												<span class="icon icon-check"></span>
											</span>
											<div class="layer base absolute perfect-center text-center achievement-name">
												<span class="font _18"><?= $q->quest_title; ?></span>
											</div>
											<input type="hidden" class="reqs-id" value="<?= $q->quest_id; ?>">
											<input type="hidden" class="reqs-level" value="<?= $q->mech_level; ?>">
										</li>
								
										<?php
									}
								}
								?>
							</ul>
						<?php }else{ ?>
							<h4><?php _e('No quests available','bluerabbit'); ?></h4>
						<?php } ?>
					</div>

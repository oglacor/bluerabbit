					<div class="highlight padding-10 amber-bg-400 sticky" id="tutorial-achievements-required">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  grey-bg-800">
								<span class="icon icon-achievement"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line grey-800"><?= __("Achievements Required","bluerabbit"); ?></span>
								<span class="line font _14 grey-600"><?= __("Choose wisely.","bluerabbit"); ?></span>
							</span>
						</span>
					</div>
					<div class="content">
						<?php if(isset($achievements['publish'])){ ?>
							<ul class="selectable-list grid select-multiple" id="quest-achievement-reqs">
								<?php
								foreach ($achievements['publish'] as $key=>$a){
									if($a->achievement_display=='badge'){
										$status = "";
										if(!empty($reqs['achievements']) && in_array($a->achievement_id, $reqs['achievements'])){
											$status = "active";
										}
										?>
										<li id="req-achievement-<?= $a->achievement_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleReq('#req-achievement-<?= $a->achievement_id; ?>'); checkPublishFor(<?= $a->achievement_id; ?>);" style="background-image: url(<?= $a->achievement_badge; ?>);">
											<input type="hidden" class="reqs-id" value="<?= $a->achievement_id; ?>">
											<input type="hidden" class="reqs-ref-id" value="<?= $a->ref_id; ?>">
											<div class="layer background absolute sq-full top left color-overlay"></div>
											<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
												<span class="icon icon-check"></span>
											</span>
											<div class="layer base absolute perfect-center text-center achievement-name">
												<span class="font _18"><?= $a->achievement_name; ?></span>
											</div>
										</li>
										<?php
										}
									}
								?>
							</ul>
						<?php }else{ ?>
							<h4 class="margin-5 font _24 w900 grey-400"><?php _e('No achievements available','bluerabbit'); ?></h4>
						<?php } ?>
					</div>

					<div class="step-content-container">
						<div class="step-image <?=$step->step_attach ? $step->step_attach : '';?>">
							<img class="layer base relative " src="<?= $step->step_image; ?>" alt="" id="step-slide-image-<?=$step->step_id;?>">
						</div>
						<div class="step-content">
							<div class="step-content-text">
								<?= apply_filters('the_content',$step->step_content); ?>
							</div>
							<div class="step-buttons">
								<?php if($step_buttons[$step->step_id]){
									shuffle($step_buttons[$step->step_id]);
								?>
									<?php foreach($step_buttons[$step->step_id] as $b){ ?>

										<?php if($b->button_image){ ?>
											<button class="form-ui w-100 h-100 step-button <?= $b->button_classes ? $b->button_classes : 'blue-bg-400 white-color'; ?>" id="button-<?=$b->button_id;?>" style="background-image: url(<?= $b->button_image; ?>);">
											</button>
										<?php }else{ ?>
											<button class="form-ui <?= $b->button_classes ? $b->button_classes : 'blue-bg-400 white-color'; ?>" id="button-<?=$b->button_id;?>">
												<?= $b->button_text ? $b->button_text : __("Next","bluerabbit"); ?>
											</button>
										<?php } ?>
										<script>
											$('#button-<?=$b->button_id;?>').click(function(){
												<?php if(!$b->button_actions || $b->button_actions == 'next'){ ?>
													jumpToStep(<?=$steps[($i+1)]->step_order;?>);
												<?php }elseif($b->button_actions == 'prev'){ ?>
													jumpToStep(<?=$steps[($i-1)]->step_order;?>);
												<?php }elseif($b->button_actions == 'complete'){ ?>
													submitPlayerWork();
												<?php }elseif($b->button_actions == 'fail'){ ?>
													failQuest();
												<?php }elseif($b->button_actions == 'backpack'){ ?>
													loadContent("backpack");
												<?php }elseif($b->button_actions == 'item-shop'){ ?>
													document.location.href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>";
												<?php }elseif($b->button_actions == 'encounter'){ ?>
													randomEncounter();
												<?php }else{ ?>
													jumpToStep(<?=$b->button_actions;?>,<?=$b->button_ep_cost;?>);				
												<?php } ?>
											});
										</script>
									<?php } ?>
								<?php }else{ ?>
									<?php if($i > 0){ ?>
										<button class="form-ui lime-bg-500 blue-grey-900 font w900" id="button-<?=$b->button_id;?>" onClick="jumpToStep(<?= $steps[($i-1)]->step_order; ?>);">
											<?= __("Back","bluerabbit"); ?>
										</button>
									<?php }?>
									<?php if($i >= count($steps)-1){ ?>
										<button class="form-ui orange-bg-400 blue-grey-900 font w900" id="last-button" onClick="submitPlayerWork();">
											<?= __("Finish","bluerabbit"); ?>
										</button>
									<?php }else{ ?>
										<button class="form-ui lime-bg-500 blue-grey-900 font w900" id="button-<?=$b->button_id;?>" onClick="jumpToStep(<?= $steps[($i+1)]->step_order; ?>);">
											<?= __("Next","bluerabbit"); ?>
										</button>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>

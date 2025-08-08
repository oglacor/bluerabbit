					<div class="highlight padding-10 grey-bg-100" id="tutorial-key-item-required">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  blue-bg-800">
								<span class="icon icon-key"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line blue-800"><?php _e("Key Item Required","bluerabbit"); ?></span>
								<span class="line font _14 grey-500"><?php _e("There should be Key Items in the shop","bluerabbit"); ?></span>
							</span>
						</span>
					</div>
					<div class="content padding-10">
						<?php if(isset($items['key'])) { ?>
							<ul class="selectable-list grid" id="item_required">
								<?php foreach ($items['key'] as $key=>$i){ ?>
									<?php
										if(!empty($reqs['items']) && in_array($i->item_id, $reqs['items'])){
											$status = "active";
										}else{
											$status = "";
										}
									?>
									<li id="item-<?= $i->item_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg" onClick="toggleSingleReq('#item-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
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
								<h4 class="margin-5 font _24 w900 grey-400"><?php _e("No key items available",'bluerabbit'); ?></h4>
								<a class="form-ui indigo-bg-600" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&type=key"; ?>"><?php _e("Add new key item",'bluerabbit'); ?></a>
							</div>
						<?php } ?>
					</div>

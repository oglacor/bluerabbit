<?php $items = BR_Item::instance()->getItems($adventure->adventure_id);
	$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id,'path|rank');
	$i_type_colors = array(
		'key'=>'indigo-bg-400',
		'consumable'=>'pink-bg-400',
		'reward'=>'teal-bg-400',
	);
	$colors = array(
		"red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey"
	);
?>
<div class="br-journey-manager">
				<input type="hidden" id="item-cat-nonce" value="<?= wp_create_nonce('item_cat_nonce'); ?>" />

				<!-- ════════════ HEADER BAR ════════════ -->
				<div class="br-header">
					<div class="br-header-left">
						<div class="br-icon"><span class="icon icon-basket"></span></div>
						<h2><?= __('Items','bluerabbit'); ?></h2>
					</div>
					<div class="br-header-right">
						<form id="upload_bulk_quests_form" class="br-bulk-upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">
							<input type="file" name="the_csv_file_with_items" id="the_csv_file_with_items" size="20" />
							<button type="button" class="br-btn ghost" onClick="uploadBulkItems();">
								<span class="icon icon-upload"></span> <?= __("Upload file","bluerabbit"); ?>
							</button>
						</form>
					</div>
				</div>

				<!-- ════════════ STICKY TOOLBAR ════════════ -->
				<div class="br-toolbar">
					<div class="br-search">
						<span class="icon icon-search"></span>
						<input type="text" id="search" placeholder="<?= __("Search","bluerabbit"); ?>">
					</div>
					<script>
						$('#search').keyup(function(){
							var valThis = $(this).val().toLowerCase();
							if(valThis == ""){
								$('table#item-table tbody > tr').show();
							}else{
								$('table#item-table tbody > tr').each(function(){
									var text = $(this).text().toLowerCase();
									(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
								});
							};
						});
					</script>
				</div>

				<?php if($items['publish']){ ?>

				<div class="br-table-wrap">
					<table class="br-table table-items" id="table-item">
						<thead>
							<tr>
							<th class="badge"><button class="br-btn ghost" onClick="toggleColumn('badge');"><?= __("Image","bluerabbit"); ?></button></th>
							<th class="ID"><button class="br-btn ghost" onClick="toggleColumn('id');"><?= __("ID","bluerabbit"); ?></button></th>
							<th class="level w-100"><button class="br-btn ghost" onClick="toggleColumn('level');"><?= __("Level","bluerabbit"); ?></button></th>
							<th><button class="br-btn ghost" onClick="toggleColumn('name');"><?= __("Name","bluerabbit"); ?></button></th>
							<th><button class="br-btn ghost" onClick="toggleColumn('category');"><?= __("Category","bluerabbit"); ?></button></th>
							<th class="text-center"><button class="br-btn ghost" onClick="toggleColumn('cost');"><?= __("Cost","bluerabbit"); ?></button></th>
							<th class="text-center"><button class="br-btn ghost" onClick="toggleColumn('stock');"><?= __("Stock","bluerabbit"); ?></button></th>
							<th class="text-center"><button class="br-btn ghost" onClick="toggleColumn('max');"><?= __("Max","bluerabbit"); ?></button></th>
							<th class="text-center"><button class="br-btn ghost" onClick="toggleColumn('path');"><?= __("Path","bluerabbit"); ?></button></th>
							<th class="text-center"><button class="br-btn ghost" onClick="toggleColumn('steps');"><?= __("Step","bluerabbit"); ?></button></th>
							<th class="text-center"><span class="icon icon-edit"></span></th>
							<th class="text-center"><span class="icon icon-duplicate"></span></th>
							<th class="text-center"><span class="icon icon-trash"></span></th>
							<th class="text-center"><span class="icon icon-infinite"></span></th>
							</tr>
						</thead>
						<tbody class="sortable">
							<?php foreach($items['publish'] as $key=>$i){ $a_color='';?>

								<?php if($achievements['publish']){
									foreach($achievements['publish'] as $a){
										if($i->achievement_id == $a->achievement_id && $i->achievement_id != 0){
											$a_color = $a->achievement_color."-bg-100";
										}
									}
								} ?>
								<tr class="item-row " id="item-<?= $i->item_id;?>">
									<td class="badge">
										<input type="hidden" value="<?= $i->item_badge; ?>" id="the_item_badge-<?= $i->item_id; ?>">
										<button class="br-thumb" onClick="showWPUpload('the_item_badge-<?= $i->item_id; ?>','a','item',<?= $i->item_id; ?>);" id="the_item_badge-<?= $i->item_id; ?>_thumb" style="background-image: url(<?=$i->item_badge; ?>);">
										</button>
									</td>
									<td class="<?=$a_color; ?> ID">
										<span class="br-badge br-badge-<?= $i->item_type == 'key' ? 'purple' : ($i->item_type == 'consumable' ? 'red' : 'teal'); ?>">
											<?= $i->item_id; ?>
										</span>
									</td>
									<td class="<?=$a_color; ?> level">
										<div class="br-num">
											<input type="number" class="br-input" id="the_level-item-<?= $i->item_id; ?>" value="<?= $i->item_level; ?>" onChange="setLevel(<?= $i->item_id; ?>,'item');">
										</div>
										<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
									</td>
									<td class="<?=$a_color; ?> name">
										<div class="br-name br-items-name-cell">
											<span class="br-badge br-badge-<?= $i->item_type == 'key' ? 'purple' : ($i->item_type == 'consumable' ? 'red' : 'teal'); ?>">
												<?php if($i->item_type == "consumable") { ?>
													<span class="icon icon-basket"></span>
												<?php }elseif($i->item_type == "key"){ ?>
													<span class="icon icon-key"></span>
												<?php }elseif($i->item_type == "reward"){ ?>
													<span class="icon icon-achievement"></span>
												<?php } ?>
												<span class="hidden"><?= $i->item_type; ?></span>
											</span>
											<input type="text" class="row-title" id="the_title-item-<?= $i->item_id; ?>" value="<?= $i->item_name; ?>" onChange="setTitle(<?= $i->item_id; ?>,'item');">
										</div>
									</td>

									<td class="text-center <?=$a_color; ?> category" >
										<?php if($i->item_type == 'consumable'){ ?>
										<div class="br-num">
											<select id="the_item_category-<?= $i->item_id; ?>" class="br-input" onChange="setCategory(<?= $i->item_id; ?>);">
												<option value="0" <?= !$i->item_category ? 'selected' : ''; ?>>
													- <?= __("No Category","bluerabbit"); ?> -
												</option>
												<?php foreach($colors as $key=> $color){ ?>
													<option value="<?= $color; ?>" <?= $i->item_category== $color ? 'selected' : ''; ?>>
														<?= $color; ?>
													</option>
												<?php } ?>
											</select>
										</div>
										<?php }else{ ?>
											<span class="icon icon-remove br-items-icon-dim"></span>
										<?php }?>
									</td>
									<td class="text-center <?=$a_color; ?> cost">
										<?php if($i->item_type != 'reward' ){ ?>
											<div class="br-num">
												<span class="icon icon-bloo br-items-bloo-icon"></span>
												<input type="number" class="br-input" id="the_bloo-item-<?= $i->item_id; ?>" value="<?= $i->item_cost; ?>" onChange="setBLOO(<?= $i->item_id; ?>,'item');">
											</div>
										<?php }else{?>
											<span class="icon icon-remove br-items-icon-dim"></span>
										<?php }?>
									</td>
									<td class="text-center <?=$a_color; ?> stock">
										<?php if($i->item_type == 'consumable'){ ?>
											<span class="br-badge br-badge-red"><?= $i->item_stock; ?></span>
										<?php }else{?>
											<span class="br-badge br-badge-<?= $i->item_type == 'key' ? 'purple' : 'teal'; ?>"><span class="icon icon-infinite"></span></span>
										<?php }?>
									</td>
									<td class=" <?=$a_color; ?> max">
										<div class="br-num">
											<input type="number" disabled class="br-input" id="the_item_max-item-<?= $i->item_id; ?>" value="<?= $i->item_player_max; ?>" onChange="setItemMax(<?= $i->item_id; ?>,'item');">
										</div>
									</td>
									<td class="text-center <?=$a_color; ?> path">
										<?php if($i->item_type != 'reward' ){ ?>
											<select class="br-input update-achievement" onChange="setAchievement(<?= $i->item_id; ?>,'item')">
												<option value="0"  <?php if(!$i->achievement_id){ echo 'selected'; }?>><?= __('All players','bluerabbit'); ?></option>
												<?php if($achievements['publish']){ ?>
													<?php foreach($achievements['publish'] as $a){ ?>
													<option value="<?= $a->achievement_id;?>" <?php if($i->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										<?php }else{?>
											<span class="icon icon-remove br-items-icon-dim"></span>
										<?php }?>
									</td>
									<td class="text-center <?=$a_color; ?> step">
										<?php if($i->step_id){ ?>
											<a href="<?= get_bloginfo('url')."/new-quest/?adventure_id=$adventure->adventure_id&questID=$i->quest_id";?>" target="_blank" class="br-btn cyan" title="Item registered in step: <?= "[ $i->step_order ] $i->step_title";?> in Quest > <?= $i->quest_title; ?>">
												<span class="icon icon-quest"></span>
											</a>
										<?php }else{?>
											<span class="icon icon-remove br-items-icon-dim"></span>
										<?php }?>
									</td>
									<td class="text-center <?=$a_color; ?>">
										<a class="br-action-link edit" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>"><span class="icon icon-edit"></span></a>
									</td>
									<td class="text-center <?=$a_color; ?>">
										<button class="br-action-link duplicate" onClick="showOverlay('#confirm-draft-<?= $i->item_id; ?>');">
											<span class="icon icon-duplicate"></span>
										</button>
										<div class="confirm-action overlay-layer draft-confirm" id="confirm-draft-<?= $i->item_id; ?>">
											<button class="br-btn amber" onClick="confirmStatus(<?= $i->item_id; ?>,'item','draft');">
												<span class="icon icon-duplicate"></span>
												<?= __("Are you sure?","bluerabbit"); ?>
											</button>
											<button class="br-btn ghost" onClick="hideAllOverlay();">
												<span class="icon icon-cancel"></span>
											</button>
										</div>
									</td>
									<td class="text-center <?=$a_color; ?>">
										<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?= $i->item_id; ?>');">
											<span class="icon icon-trash"></span>
										</button>
										<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $i->item_id; ?>">
											<button class="br-btn red" onClick="confirmStatus(<?= $i->item_id; ?>,'item','trash');">
												<span class="icon icon-trash"></span>
												<?= __("Are you sure?","bluerabbit"); ?>
											</button>
											<button class="br-btn ghost" onClick="hideAllOverlay();">
												<span class="icon icon-cancel"></span>
											</button>
										</div>
									</td>
									<td class="text-center <?=$a_color; ?>">
										<button class="br-action-link duplicate" onClick="showOverlay('#confirm-duplicate-<?= $i->item_id; ?>');">
											<span class="icon icon-infinite"></span>
										</button>
										<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?= $i->item_id; ?>">
											<button class="br-btn amber" onClick="duplicateRow(<?= $i->item_id; ?>);">
												<span class="icon icon-infinite"></span>
												<?= __("Are you sure?","bluerabbit"); ?>
											</button>
											<button class="br-btn ghost" onClick="hideAllOverlay();">
												<span class="icon icon-cancel"></span>
											</button>
										</div>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

				<!-- ════════════ REORDER BAR ════════════ -->
				<div class="br-summary-bar">
					<button class="br-btn cyan" onclick="reorderItems('#table-item')">
						<span class="icon icon-list"></span>
						<?= __("Reorder Items","bluerabbit"); ?>
					</button>
				</div>

				<!-- ════════════ SUMMARY TABLE ════════════ -->
				<div class="br-table-wrap">
					<table class="br-table" id="">
						<thead>
							<tr>
							<th class="level w-100"><?= __("Level","bluerabbit"); ?></th>
							<th><?= __("Name","bluerabbit"); ?></th>
							<th class="text-center"><?= __("Cost","bluerabbit"); ?></th>
							<th class="text-center"><?= __("Stock","bluerabbit"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($items['publish'] as $key=>$i){ $a_color='';?>
								<tr>
									<td>
										<?= $i->item_level; ?>
									</td>
									<td class="name">
										<?= $i->item_name; ?>
									</td>

									<td class="cost">
										<?= $i->item_cost;?>
									</td>
									<td class="stock">
										<?= $i->item_stock; ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-basket"></span>
					<h3><?= __("No items found","bluerabbit"); ?></h3>
				</div>
				<?= BR_Utils::instance()->addNewButton(__("Add New Item","bluerabbit"),'pink', 'item', $adventure->adventure_id); ?>
				<?php } ?>

				<!-- ════════════ DRAFT SECTION ════════════ -->
				<?php if(isset($items['draft'])){ ?>
				<div class="br-section">
					<div class="br-section-header" onclick="$(this).toggleClass('collapsed');$(this).next('.br-section-body').toggleClass('collapsed');">
						<h3>
							<span class="icon icon-duplicate br-items-draft-icon"></span>
							<?= __('Draft Items','bluerabbit'); ?>
							<span class="br-count-badge"><?= count($items['draft']); ?></span>
						</h3>
						<span class="br-toggle-icon icon icon-down"></span>
					</div>
					<div class="br-section-body">
						<table class="br-table table-items" id="draft-items-table">
							<thead>
								<tr>
									<th class="text-center"><?= __("ID","bluerabbit"); ?></th>
									<th><?= __("Level","bluerabbit"); ?></th>
									<th><?= __("Name","bluerabbit"); ?></th>
									<th><?= __("Category","bluerabbit"); ?></th>
									<th class="text-center"><?= __("Cost","bluerabbit"); ?></th>
									<th class="text-center"><?= __("Type","bluerabbit"); ?></th>
									<th class="text-center"><span class="icon icon-achievement"></span></th>
									<th class="text-center"><span class="icon icon-guild"></span></th>
									<th class="text-center"><span class="icon icon-edit"></span></th>
									<th class="text-center"><span class="icon icon-restore"></span></th>
									<th class="text-center"><span class="icon icon-trash"></span></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($items['draft'] as $key=>$i){ ?>
									<tr class="item" id="item-<?= $i->item_id;?>">
										<td class="text-center"><?= $i->item_id; ?></td>
										<td class="text-center"><?= $i->item_level; ?></td>
										<td><?= $i->item_name; ?></td>
										<td><?= $i->item_category; ?></td>
										<td class="text-center"><?= BR_Utils::instance()->toMoney($i->item_cost); ?></td>
										<td class="text-center">
											<?php if($i->item_type == "consumable") { ?>
												<span class="br-badge br-badge-red"><span class="icon icon-basket"></span> <?= $i->item_stock; ?></span>
											<?php }elseif($i->item_type == "key"){ ?>
												<span class="br-badge br-badge-purple"><span class="icon icon-key"></span></span>
											<?php }elseif($i->item_type == "reward"){ ?>
												<span class="br-badge br-badge-teal"><span class="icon icon-achievement"></span></span>
											<?php } ?>
										</td>
										<td class="text-center">
											<?php
										   	if($i->achievement_id){
										   		foreach ($achievements['publish'] as $a){
													if($i->achievement_id == $a->achievement_id){
														echo $a->achievement_name;
													}
												}
										  	}else{
												echo '<span class="icon icon-remove br-items-icon-dim"></span>';
										  	}
											?>
										</td>
										<td class="text-center">
											<?php
										   	if($i->guild_id){
										   		foreach ($guilds['publish'] as $a){
													if($i->item_id == $a->item_id){
														echo $a->guild_name;
													}
												}
										  	}else{
												echo '<span class="icon icon-remove br-items-icon-dim"></span>';
										  	}
											?>
										</td>
										<td class="text-center">
											<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span></a>
										</td>
										<td class="text-center">
											<button class="br-action-link expand" onClick="showOverlay('#confirm-option-restore-<?= $i->item_id; ?>');">
												<span class="icon icon-restore"></span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-restore-<?= $i->item_id; ?>">
												<button class="br-btn cyan" onClick="confirmStatus(<?= $i->item_id; ?>,'item','publish');">
													<span class="icon icon-restore"></span>
													<?= __("Are you sure?","bluerabbit"); ?>
												</button>
												<button class="br-btn ghost" onClick="hideAllOverlay();">
													<span class="icon icon-cancel"></span>
												</button>
											</div>
										</td>
										<td>
											<button class="br-action-link trash" onClick="showOverlay('#confirm-option-trash-<?= $i->item_id; ?>');">
												<span class="icon icon-trash"></span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-trash-<?= $i->item_id; ?>">
												<button class="br-btn red" onClick="confirmStatus(<?= $i->item_id; ?>,'item','trash');">
													<span class="icon icon-trash"></span>
													<?= __("Are you sure?","bluerabbit"); ?>
												</button>
												<button class="br-btn ghost" onClick="hideAllOverlay();">
													<span class="icon icon-cancel"></span>
												</button>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-duplicate"></span>
					<h3><?= __("No draft items","bluerabbit"); ?></h3>
				</div>
				<?php } ?>

				<!-- ════════════ TRASH SECTION ════════════ -->
				<?php if(isset($items['trash'])){ ?>
				<div class="br-section">
					<div class="br-section-header collapsed" onclick="$(this).toggleClass('collapsed');$(this).next('.br-section-body').toggleClass('collapsed');">
						<h3>
							<span class="icon icon-trash br-items-trash-icon"></span>
							<?= __('Trashed Items','bluerabbit'); ?>
							<span class="br-count-badge"><?= count($items['trash']); ?></span>
						</h3>
						<span class="br-toggle-icon icon icon-down"></span>
					</div>
					<div class="br-section-body collapsed">
						<table class="br-table table-items" id="trashed-items-table">
							<thead>
								<tr>
									<th><?= __("ID","bluerabbit"); ?></th>
									<th><?= __("Name","bluerabbit"); ?></th>
									<th><?= __("Cost","bluerabbit"); ?></th>
									<th><?= __("Type","bluerabbit"); ?></th>
									<th><?= __("Actions","bluerabbit"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($items['trash'] as $key=>$i){ ?>
									<tr class="item" id="item-<?= $i->item_id;?>">
										<td><?= $i->item_id; ?></td>
										<td>
											<?= $i->item_name; ?>
											<img class="spread max-w-100" src="<?= $i->item_badge; ?>">
										</td>
										<td><?= BR_Utils::instance()->toMoney($i->item_cost); ?></td>
										<td>
											<strong><?= $i->item_type; ?></strong>
											<br>
											<?php if($i->item_type == "consumable") { ?>
												<span class="br-badge br-badge-red"><span class="icon icon-basket"></span></span>
												<span class="br-badge br-badge-amber"><?= $i->item_stock; ?></span>
											<?php }elseif($i->item_type == "key"){ ?>
												<span class="br-badge br-badge-purple"><span class="icon icon-key"></span></span>
											<?php }elseif($i->item_type == "reward"){ ?>
												<span class="br-badge br-badge-teal"><span class="icon icon-achievement"></span></span>
											<?php } ?>
										</td>
										<td>
											<div class="br-actions">
												<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span></a>

												<button class="br-action-link expand" onClick="showOverlay('#confirm-option-restore-<?= $i->item_id; ?>');">
													<span class="icon icon-restore"></span>
												</button>
												<div class="confirm-action overlay-layer" id="confirm-option-restore-<?= $i->item_id; ?>">
													<button class="br-btn cyan" onClick="confirmStatus(<?= $i->item_id; ?>,'item','publish');">
														<span class="icon icon-restore"></span>
														<?= __("Are you sure?","bluerabbit"); ?>
													</button>
													<button class="br-btn ghost" onClick="hideAllOverlay();">
														<span class="icon icon-cancel"></span>
													</button>
												</div>

												<button class="br-action-link trash" onClick="showOverlay('#confirm-option-delete-<?= $i->item_id; ?>');">
													<span class="icon icon-cancel"></span>
												</button>
												<div class="confirm-action overlay-layer" id="confirm-option-delete-<?= $i->item_id; ?>">
													<button class="br-btn red" onClick="confirmStatus(<?= $i->item_id; ?>,'item','delete');">
														<span class="icon icon-cancel"></span>
														<?= __("Are you sure?","bluerabbit"); ?>
													</button>
													<button class="br-btn ghost" onClick="hideAllOverlay();">
														<span class="icon icon-cancel"></span>
													</button>
												</div>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-trash"></span>
					<h3><?= __("Trash is empty","bluerabbit"); ?></h3>
				</div>
				<?php } ?>
			<input type="hidden" id="row_type" value="item"/>

</div><!-- /.br-journey-manager -->
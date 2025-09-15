<?php $items = getItems($adventure->adventure_id); 
	$achievements = getAchievements($adventure->adventure_id,'path|rank');
	$i_type_colors = array(
		'key'=>'indigo-bg-400',
		'consumable'=>'pink-bg-400',
		'reward'=>'teal-bg-400',
	);
	$colors = array(
		"red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey"
	); 
?>
				<input type="hidden" id="item-cat-nonce" value="<?= wp_create_nonce('item_cat_nonce'); ?>" />
					<div class="highlight padding-10 pink-bg-50">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  pink-bg-400"><span class="icon icon-basket"></span></span>
							<span class="icon-content">
								<span class="line font _24 grey-800"><?= __('Items','bluerabbit'); ?></span>
							</span>
						</span>
						<div class="input-group pull-right">
							<div class="form-ui font _14">
								<form id="upload_bulk_quests_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
									<table>
										<tbody>
											<tr>
												<td class="w-200">
													<label for="the_csv_file_with_items" class="">Upload Items:</label>
													<input type="file" name="the_csv_file_with_items" id="the_csv_file_with_items" size="20" />
												</td>
												<td class="w-100">
													<button type="button" onClick="uploadBulkItems();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
												</td>
											</tr>
										</tbody>
									</table>
								</form>
							</div>
						</div>
						<div class="highlight-cell pull-right padding-10">
							<div class="search sticky">
								<div class="input-group">
									<input type="text" class="form-ui" id="search" placeholder="<?= __("Search","bluerabbit"); ?>">
									<label>
										<span class="icon icon-search"></span>
									</label>
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
							</div>
						</div>
					</div>
				<?php if($items['publish']){ ?>

					<div class="content">
						<table class="table compact table-items" id="table-item">
							<thead>
								<tr>
								<td class="badge"><button class="form-ui" onClick="toggleColumn('badge');"><?= __("Image","bluerabbit"); ?></button></td>
								<td class="ID"><button class="form-ui" onClick="toggleColumn('id');"><?= __("ID","bluerabbit"); ?></button></td>
								<td class="level w-100"><button class="form-ui" onClick="toggleColumn('level');"><?= __("Level","bluerabbit"); ?></button></td>
								<td class=""><button class="form-ui" onClick="toggleColumn('name');"><?= __("Name","bluerabbit"); ?></button></td>
								<td class=""><button class="form-ui" onClick="toggleColumn('category');"><?= __("Category","bluerabbit"); ?></button></td>
								<td class="text-center"><button class="form-ui" onClick="toggleColumn('cost');"><?= __("Cost","bluerabbit"); ?></button></td>
								<td class="text-center"><button class="form-ui" onClick="toggleColumn('stock');"><?= __("Stock","bluerabbit"); ?></button></td>
								<td class="text-center"><button class="form-ui" onClick="toggleColumn('max');"><?= __("Max","bluerabbit"); ?></button></td>
								<td class="text-center"><button class="form-ui" onClick="toggleColumn('path');"><?= __("Path","bluerabbit"); ?></button></td>
								<td class="text-center"><button class="form-ui" onClick="toggleColumn('steps');"><?= __("Step","bluerabbit"); ?></button></td>
								<td class="text-center"><span class="icon icon-edit"></td>
								<td class="text-center"><span class="icon icon-duplicate"></td>
								<td class="text-center"><span class="icon icon-trash"></td>
								<td class="text-center"><span class="icon icon-infinite"></td>
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
											<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_item_badge-<?= $i->item_id; ?>','a','item',<?= $i->item_id; ?>);" id="the_item_badge-<?= $i->item_id; ?>_thumb" style="background-image: url(<?=$i->item_badge; ?>);">
											</button>
										</td>
										<td class="<?=$a_color; ?> ID">
											<span class="icon-button <?=$i_type_colors[$i->item_type];?> sq-40 font _14 w500 white-color padding-10">
												<?= $i->item_id; ?>
											</span>
										</td>
										<td class="<?=$a_color; ?> level">
											<div class="input-group w-full">
												<input type="number" class="form-ui w-100" id="the_level-item-<?= $i->item_id; ?>" value="<?= $i->item_level; ?>" onChange="setLevel(<?= $i->item_id; ?>,'item');">
											</div>
											<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
										</td>
										<td class="<?=$a_color; ?> name">
											<div class="input-group w-full">
												<label class="<?= $i_type_colors[$i->item_type]; ?>">
													<span class="hidden"><?= $i->item_type; ?></span>
													<?php if($i->item_type == "consumable") { ?>
														<span class="icon icon-basket"></span>
													<?php }elseif($i->item_type == "key"){ ?>
														<span class="icon icon-key"></span>
													<?php }elseif($i->item_type == "reward"){ ?>
														<span class="icon icon-achievement"></span>
													<?php } ?>
												</label>
												<input type="text" class="form-ui row-title" id="the_title-item-<?= $i->item_id; ?>" value="<?= $i->item_name; ?>" onChange="setTitle(<?= $i->item_id; ?>,'item');">
											</div>
										</td>

										<td class="text-center <?=$a_color; ?> category" >
											<?php if($i->item_type == 'consumable'){ ?>
											<div class="input-group w-full">
												<label class="<?= $i->item_category ? $i->item_category."-bg-400" : 'black-bg'; ?>">
													<span class="icon icon-list"></span>
												</label>
												<select id="the_item_category-<?= $i->item_id; ?>" class="form-ui font w900 capitalize" onChange="setCategory(<?= $i->item_id; ?>);">
													<option class="font _18 white-color black-bg capitalize" value="0"  <?= !$i->item_category ? 'selected' : ''; ?>>
														- <?= __("No Category","bluerabbit"); ?> -
													</option>
													<?php foreach($colors as $key=> $color){ ?>
														<option class="font _18 padding-5 white-color <?= $color; ?>-bg-400 capitalize" value="<?= $color; ?>" <?= $i->item_category== $color ? 'selected' : ''; ?>>
															<?= $color; ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<?php }else{ ?>
												<span class="icon icon-remove"></span>
											<?php }?>
										</td>
										<td class="text-center <?=$a_color; ?> cost">
											<?php if($i->item_type != 'reward' ){ ?>
												<div class="input-group w-full">
													<label class="green-bg-400">
														<span class="icon icon-bloo"></span>
													</label>
													<input type="number" class="form-ui" id="the_bloo-item-<?= $i->item_id; ?>" value="<?= $i->item_cost; ?>" onChange="setBLOO(<?= $i->item_id; ?>,'item');">
												</div>
											<?php }else{?>
												<span class="icon icon-remove"></span>
											<?php }?>
										</td>
										<td class="text-center <?=$a_color; ?> stock">
											<?php if($i->item_type == 'consumable'){ ?>
												<span class="pink-400 font w600"><?= $i->item_stock; ?></span>
											<?php }else{?>
												<span class="icon-button font _24 sq-40  <?= $i_type_colors[$i->item_type]; ?>"><span class="icon icon-infinite"></span></span>
											<?php }?>
										</td>
										<td class=" <?=$a_color; ?> max">
											<div class="input-group w-full">
												<input type="number" disabled class="form-ui" id="the_item_max-item-<?= $i->item_id; ?>" value="<?= $i->item_player_max; ?>" onChange="setItemMax(<?= $i->item_id; ?>,'item');">
											</div>
										</td>
										<td class="text-center <?=$a_color; ?> path">
											<?php if($i->item_type != 'reward' ){ ?>
												<select class="form-ui update-achievement" onChange="setAchievement(<?= $i->item_id; ?>,'item')">
													<option value="0"  <?php if(!$i->achievement_id){ echo 'selected'; }?>><?= __('All players','bluerabbit'); ?></option>
													<?php if($achievements['publish']){ ?>
														<?php foreach($achievements['publish'] as $a){ ?>
														<option value="<?= $a->achievement_id;?>" <?php if($i->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
														<?php } ?>
													<?php } ?>
												</select>
											<?php }else{?>
												<span class="icon icon-remove"></span>
											<?php }?>
										</td>
										<td class="text-center <?=$a_color; ?> step">
											<?php if($i->step_id){ ?>
												<a href="<?= get_bloginfo('url')."/new-quest/?adventure_id=$adventure->adventure_id&questID=$i->quest_id";?>" target="_blank" class="icon-button blue-bg-400" title="Item registered in step: <?= "[ $i->step_order ] $i->step_title";?> in Quest > <?= $i->quest_title; ?>">
													<span class="icon icon-quest"></span>
												</a>
											<?php }else{?>
												<span class="icon icon-remove"></span>
											<?php }?>
										</td>
										<td class="text-center <?=$a_color; ?>">
											<a class="icon-button font _24 sq-40  icon-sm green-bg-400 edit-button" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>"><span class="icon icon-edit"></span></a>
										</td>
										<td class="text-center <?=$a_color; ?>">
											<button class="icon-button font _24 sq-40  icon-sm orange-bg-400 white-color draft-button" onClick="showOverlay('#confirm-draft-<?= $i->item_id; ?>');">
												<span class="icon icon-duplicate"></span>
											</button>
											<div class="confirm-action overlay-layer draft-confirm" id="confirm-draft-<?= $i->item_id; ?>">
												<button class="form-ui white-bg draft-confirm-button" onClick="confirmStatus(<?= $i->item_id; ?>,'item','draft');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  orange-bg-400 icon-sm">
															<span class="icon icon-duplicate white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line orange-400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
										<td class="text-center <?=$a_color; ?>">
											<button class="icon-button font _24 sq-40  icon-sm red-bg-400 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $i->item_id; ?>');">
												<span class="icon icon-trash"></span>
											</button>
											<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $i->item_id; ?>">
												<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $i->item_id; ?>,'item','trash');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
															<span class="icon icon-trash white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
										<td class="text-center <?=$a_color; ?>">
											<button class="icon-button font _24 sq-40  icon-sm amber-bg-400 white-color duplicate-button" onClick="showOverlay('#confirm-duplicate-<?= $i->item_id; ?>');">
												<span class="icon icon-infinite"></span>
											</button>
											<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?= $i->item_id; ?>">
												<button class="form-ui white-bg duplicate-confirm-button" onClick="duplicateRow(<?= $i->item_id; ?>);">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  amber-bg-A400 icon-sm">
															<span class="icon icon-infinite white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line amber-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						
						
					</div>
					<div class="highlight padding-10 blue-grey-bg-800 sticky-bottom text-center">
						<button class="form-ui pink-bg-400" onclick="reorderItems('#table-item')">
							<span class="icon icon-list"></span>
							<?= __("Reorder Items","bluerabbit"); ?>
						</button>
					</div>
					<div>
						<table class="table compact" id="">
							<thead>
								<tr>
								<td class="level w-100"><?= __("Level","bluerabbit"); ?></td>
								<td class=""><?= __("Name","bluerabbit"); ?></td>
								<td class="text-center"><?= __("Cost","bluerabbit"); ?></td>
								<td class="text-center"><?= __("Stock","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody class="">
								<?php foreach($items['publish'] as $key=>$i){ $a_color='';?>
									<tr>
										<td class="">
											<div class="input-group w-full">
												<?= $i->item_level; ?>
											</div>
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
					<?= notFound(__("No items found"),'light-green'); ?>
					<?= addNewButton(__("Add New Item","bluerabbit"),'pink', 'item', $adventure->adventure_id); ?>
				<?php } ?>
				<?php if(isset($items['draft'])){ ?>
					<div class="highlight padding-10 amber-bg-50">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  amber-bg-400"><span class="icon icon-duplicate"></span></span>
							<span class="icon-content">
								<span class="line font _24 grey-800"><?= __('Draft Items','bluerabbit'); ?></span>
							</span>
						</span>
						<div class="highlight-cell pull-right padding-10">
							<div class="search sticky">
								<div class="input-group">
									<input type="text" class="form-ui" id="search" placeholder="<?= __("Search","bluerabbit"); ?>">
									<label>
										<span class="icon icon-search"></span>
									</label>
									<script>
										$('#search').keyup(function(){
											var valThis = $(this).val().toLowerCase();
											if(valThis == ""){
												$('table#draft-items-table tbody > tr').show();           
											}else{
												$('table#draft-items-table tbody > tr').each(function(){
													var text = $(this).text().toLowerCase();
													(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
												});
											};
										});
									</script>				
								</div>
							</div>
						</div>
					</div>
					<div class="content">
						<table class="table table-items" id="draft-items-table">
							<thead>
								<tr>
									<td width="3%" class="text-center"><?= __("ID","bluerabbit"); ?></td>
									<td width="5%"><?= __("Level","bluerabbit"); ?></td>
									<td width="25%"><?= __("Name","bluerabbit"); ?></td>
									<td width="18%"><?= __("Category","bluerabbit"); ?></td>
									<td width="10%" class="text-center"><?= __("Cost","bluerabbit"); ?></td>
									<td width="10%" class="text-center"><?= __("Type","bluerabbit"); ?></td>
									<td width="10%" class="text-center"><span class="icon icon-achievement"></span></td>
									<td width="10%" class="text-center"><span class="icon icon-guild"></span></td>
									<td width="3%" class="text-center"><span class="icon icon-edit"></span></td>
									<td width="3%" class="text-center"><span class="icon icon-restore"></span></td>
									<td width="3%" class="text-center"><span class="icon icon-trash"></span></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach($items['draft'] as $key=>$i){ ?>
									<tr class="item amber-bg-50" id="item-<?= $i->item_id;?>">
										<td class="text-center"><?= $i->item_id; ?></td>
										<td class="text-center"><?= $i->item_level; ?></td>
										<td><?= $i->item_name; ?></td>
										<td><?= $i->item_category; ?></td>
										<td class="text-center"><?= toMoney($i->item_cost); ?></td>
										<td class="text-center">
											<?php if($i->item_type == "consumable") { ?>
												<button class="form-ui pink-bg-400 icon-sm"><span class="icon icon-basket"></span> <?= $i->item_stock; ?></button>
											<?php }elseif($i->item_type == "key"){ ?>
												<span class="icon-button font _24 sq-40  indigo-bg-400 icon-sm"><span class="icon icon-key"></span></span>
											<?php }elseif($i->item_type == "reward"){ ?>
												<span class="icon-button font _24 sq-40  teal-bg-400 icon-sm"><span class="icon icon-achievement"></span></span>
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
												echo '<span class="icon-button font _24 sq-40  deep-purple-bg-300 icon-sm"><span class="icon icon-remove"></span></span>';
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
												echo '<span class="icon-button font _24 sq-40  light-green-bg-300 icon-sm"><span class="icon icon-remove"></span></span>';
										  	}
											?>
										</td>
										<td class="text-center">
											<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit"></span></a>
										</td>
										<td class="text-center">
											<button class="icon-button font _24 sq-40  blue-bg-A400 white-color icon-sm" onClick="showOverlay('#confirm-option-restore-<?= $i->item_id; ?>');">
												<span class="icon icon-restore"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?= __("Restore","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-restore-<?= $i->item_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?= $i->item_id; ?>,'item','publish');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  blue-bg-A700 icon-sm">
															<span class="icon icon-restore white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line blue-A700 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
										<td>
											<button class="icon-button font _24 sq-40  red-bg-400 white-color icon-sm"  onClick="showOverlay('#confirm-option-trash-<?= $i->item_id; ?>');">
												<span class="icon icon-trash"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?= __("Trash","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-trash-<?= $i->item_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?= $i->item_id; ?>,'item','trash');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  blue-bg-A700 icon-sm">
															<span class="icon icon-trash white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line red-700 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php }else{ ?>
					<?= notFound(__("No draft items"),'amber'); ?>
				<?php } ?>
				<?php if(isset($items['trash'])){ ?>
					<div class="highlight padding-10 deep-purple-bg-50">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-trash"></span></span>
							<span class="icon-content">
								<span class="line font _24 grey-800"><?= __('Trashed Items','bluerabbit'); ?></span>
							</span>
						</span>
						<div class="highlight-cell pull-right padding-10">
							<div class="search sticky">
								<div class="input-group">
									<input type="text" class="form-ui" id="search" placeholder="<?= __("Search","bluerabbit"); ?>">
									<label>
										<span class="icon icon-search"></span>
									</label>
									<script>
										$('#search').keyup(function(){
											var valThis = $(this).val().toLowerCase();
											if(valThis == ""){
												$('table#trashed-items-table tbody > tr').show();           
											}else{
												$('table#trashed-items-table tbody > tr').each(function(){
													var text = $(this).text().toLowerCase();
													(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
												});
											};
										});
									</script>				
								</div>
							</div>
						</div>
					</div>
					<div class="content">
						<table class="table table-items" id="trashed-items-table">
							<thead>
								<tr>
									<td class=""><?= __("ID","bluerabbit"); ?></td>
									<td class=""><?= __("Name","bluerabbit"); ?></td>
									<td class=""><?= __("Cost","bluerabbit"); ?></td>
									<td class=""><?= __("Type","bluerabbit"); ?></td>
									<td class=""><?= __("Actions","bluerabbit"); ?></td>
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
										<td><?= toMoney($i->item_cost); ?></td>
										<td>
											<strong><?= $i->item_type; ?></strong>
											<br>
											<?php if($i->item_type == "consumable") { ?>
												<span class="icon-button font _24 sq-40  pink"><span class="icon icon-basket"></span></span>
												<span class="icon-button font _24 sq-40  amber"><?= $i->item_stock; ?></span>
											<?php }elseif($i->item_type == "key"){ ?>
												<span class="icon-button font _24 sq-40  indigo"><span class="icon icon-key"></span></span>
											<?php }elseif($i->item_type == "reward"){ ?>
												<span class="icon-button font _24 sq-40  teal"><span class="icon icon-achievement"></span></span>
											<?php } ?>
										</td>
										<td>
											<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id";?>" class="icon-button font _24 sq-40  green-bg-400"><span class="icon icon-edit"></span></a>


											<button class="icon-button font _24 sq-40  blue-bg-A400 white-color" onClick="showOverlay('#confirm-option-restore-<?= $i->item_id; ?>');">
												<span class="icon icon-restore"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?= __("Restore","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-restore-<?= $i->item_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?= $i->item_id; ?>,'item','publish');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  blue-bg-A700 icon-sm">
															<span class="icon icon-restore white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line blue-A700 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
											<button class="icon-button font _24 sq-40  red-bg-A400 white-color" onClick="showOverlay('#confirm-option-delete-<?= $i->item_id; ?>');">
												<span class="icon icon-cancel"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?= __("Delete Forever","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-delete-<?= $i->item_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?= $i->item_id; ?>,'item','delete');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
															<span class="icon icon-cancel white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
															<span class="line font _14 grey-400"><?= __("You can't undo this","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php }else{ ?>
					<?= notFound(__("Trash is empty"),'red'); ?>
				<?php } ?>
			<input type="hidden" id="row_type" value="item"/>

<?php 
	$colors = array(
		'quest'=>'blue-bg-50',
		'mission'=>'amber-bg-50',
		'challenge'=>'brown-bg-50',
		'survey'=>'teal-bg-50',
	);
	$colors_dark = array(
		'quest'=>'blue-bg-700',
		'mission'=>'amber-bg-800',
		'challenge'=>'brown-bg-700',
		'survey'=>'teal-bg-700',
	);
	$h=false;
	$show_added = false;
	if(isset($_GET['path'])){
		$path_str = '&path='.$_GET['path'];
		$path = $_GET['path'];
	} else{
		$path_str = "";
		$path = "";
	}
	if(isset($_GET['order'])){
		if($_GET['order'] == 'A'){
			$show_added = true;
			$order = 'mech_level, mech_start_date, quest_order, quest_title';
		}elseif($_GET['order'] == 'B'){ /// Date First Order Second, Level third
			$order = 'mech_start_date ASC,  mech_level, quest_order,quest_title';
		}elseif($_GET['order'] == 'C'){ /// Date First Order Second, Level third
			$order = 'mech_deadline DESC, mech_level, quest_order, quest_title';
		}elseif($_GET['order'] == 'D'){ /// Title first only
			$order = 'quest_title, mech_level, quest_id';
		}else{ /// Quest order, Level and Start Date
			$h = true;
			$order = 'quest_order, mech_level, mech_start_date, quest_title';
		}
	}else{
		$h = true;
		$order='quest_order, mech_level, mech_start_date, quest_title';
	}
	$quests = getQuests($adventure->adventure_id,'',"blog-post' AND quest_type!='lore", $order, $path);
	$achievements = getAchievements($adventure->adventure_id, "path|rank");
?>
				<div class="highlight padding-10 deep-purple-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  deep-purple-bg-400"><span class="icon icon-journey"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800"><?php _e('Journey','bluerabbit'); ?></span>
							<span class="line font _14 grey-600"><?php _e("The journey is ordered first by level and then by list",'bluerabbit'); ?></span>
						</span>
					</span>
					<div class="input-group pull-right">
						<div class="form-ui font _14">
							<form id="upload_bulk_quests_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
								<table>
									<tbody>
										<tr>
											<td class="w-200">
												<label for="the_csv_file_with_quests" class="">Upload Quests:</label>
												<input type="file" name="the_csv_file_with_quests" id="the_csv_file_with_quests" size="20" />
											</td>
											<td class="w-100">
												<button type="button" onClick="uploadBulkQuests();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
											</td>
										</tr>
									</tbody>
								</table>
							</form>
						</div>
					</div>
				</div>
				<div class="highlight padding-10 deep-purple-bg-50">

					<div class="icon-group padding-10">
						<span class="icon-button font _24 sq-40  font _16 blue-bg-400 white-color"><span class="icon icon-list"></span></span>
						<span class="icon-content">
							<a class="form-ui <?= !isset($_GET['order']) ? 'blue-bg-400' : ''; ?>" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id$path_str"; ?>"><?= __("Journey","bluerabbit"); ?></a>
							<a class="form-ui <?= isset($_GET['order']) && $_GET['order']=='A' ? 'blue-bg-400' : ''; ?>" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&order=A$path_str"; ?>"><?= __("Level","bluerabbit"); ?></a>
							<a class="form-ui <?= isset($_GET['order']) && $_GET['order']=='B' ? 'blue-bg-400' : ''; ?>" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&order=B$path_str"; ?>"><?= __("Start Date","bluerabbit"); ?></a>
							<a class="form-ui <?= isset($_GET['order']) && $_GET['order']=='C' ? 'blue-bg-400' : ''; ?>" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&order=C$path_str"; ?>"><?= __("Deadline","bluerabbit"); ?></a>
							<a class="form-ui <?= isset($_GET['order']) && $_GET['order']=='D' ? 'blue-bg-400' : ''; ?>" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&order=D$path_str"; ?>"><?= __("Title","bluerabbit"); ?></a>
						</span>
					</div>
					<div class="icon-group padding-10">
						<?php $resource_autofill = isset($config['resource_auto_fill']) ? $config['resource_auto_fill'] : 0 ; ?>
						<div id="resource_autofill">
							<select id="resource-autofill" class="setting-value" onChange="saveSetting('#resource_autofill');"> 
								<option value="0"><?= __("Off","bluerabbit"); ?></option>
								<option value="65" <?= $resource_autofill['value']=='65' ? 'selected' : ''; ?>><?= __("Easy","bluerabbit"); ?></option>
								<option value="50" <?= $resource_autofill['value']=='50' ? 'selected' : ''; ?>><?= __("Normal","bluerabbit"); ?></option>
								<option value="35" <?= $resource_autofill['value']=='35' ? 'selected' : ''; ?>><?= __("Hard","bluerabbit"); ?></option>
								<option value="25" <?= $resource_autofill['value']=='25' ? 'selected' : ''; ?>><?= __("Very Hard","bluerabbit"); ?></option>
								<option value="10" <?= $resource_autofill['value']=='10' ? 'selected' : ''; ?>><?= __("Legendary","bluerabbit"); ?></option>
							</select>
							<input type="hidden" class="setting-id" value="<?= $resource_autofill['id']; ?>">
							<input type="hidden" class="setting-name" value="resource_autofill">
							<input type="hidden" class="setting-label" value="Resource Autofill">
						</div>
					</div>
					<div class="icon-group">
						<span class="icon-content">
							<span class="line font _14 grey-800"><?php _e('Show','bluerabbit'); ?></span>
						</span>
	<button id="filter-all" class="icon-button font _24 sq-40  icon-sm black-bg" onClick="filterAdminTable('all','#table-quest .admin-table-body .row-container');"><span class="icon icon-infinite"></span></button>
	<button id="filter-quest" class="icon-button font _24 sq-40  icon-sm blue-bg-400" onClick="filterAdminTable('quest','#table-quest .admin-table-body .row-container');"><span class="icon icon-quest"></span></button>
	<button id="filter-mission" class="icon-button font _24 sq-40  icon-sm amber-bg-400" onClick="filterAdminTable('mission','#table-quest .admin-table-body .row-container');"><span class="icon icon-mission"></span></button>
	<button id="filter-challenge" class="icon-button font _24 sq-40  icon-sm red-bg-400" onClick="filterAdminTable('challenge','#table-quest .admin-table-body .row-container');"><span class="icon icon-challenge"></span></button>
	<button id="filter-survey" class="icon-button font _24 sq-40  icon-sm teal-bg-400" onClick="filterAdminTable('survey','#table-quest .admin-table-body .row-container');"><span class="icon icon-survey"></span></button>
						<select onChange="document.location.href=$('#the_achievement_filter').val();" id="the_achievement_filter">
							<option value="<?= get_bloginfo('url')."/manage-adventure/?adventure_id={$adventure->adventure_id}";?>"><?= __("All paths","bluerabbit"); ?></option>
							<?php foreach($achievements['publish'] as $a){ ?>
								<option <?= ($path == $a->achievement_id) ? "selected" : ""; ?> value="<?= get_bloginfo('url')."/manage-adventure/?adventure_id={$adventure->adventure_id}&path={$a->achievement_id}"; ?>">
									<?= $a->achievement_name; ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="highlight-cell pull-right">
						<div class="search sticky">
							<div class="input-group">
								<input type="text" class="form-ui" id="search-quests" placeholder="<?php _e("Search quests","bluerabbit"); ?>">
								<label>
									<span class="icon icon-search"></span>
								</label>
								<script>
									$('#search-quests').keyup(function(){
										var valThis = $(this).val().toLowerCase();
										if(valThis == ""){
											$('table#table-quest tbody > tr').show();           
										}else{
											$('table#table-quest tbody > tr').each(function(){
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
				<?php if(isset($current_path)) { ?>
					<div class="highlight red-bg-100 black-color text-center">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40 " style="background-image: url(<?= $current_path->achievement_badge; ?>);"></span>
							<span class="icon-content">
								<span class="line font _24"><?= $current_path->achievement_name; ?></span>
								<span class="line font _14 opacity-50"><?= __("Currently filtering this path","bluerabbit");?></span>
							</span>
						</span>
					</div>
				<?php } ?>
				<div class="content">
					<?php if($quests['publish']){ ?>

						<div class="admin-table table-quests" id="table-quest">
							<div class="admin-table-header">
								<div class="row admin-row <?= $use_encounters ? 'with-ep' : ''; ?>">
									<div class="cell cell-drag">&nbsp;</div>
									<div class="cell cell-name">Name</div>
									<div class="cell cell-badge">Badge</div>
									<div class="cell cell-color">Color</div>
									<div class="cell cell-level">Level</div>
									<div class="cell cell-xp"><?= $xp_label; ?></div>
									<div class="cell cell-bloo"><?= $bloo_label; ?></div>
									<?php if($use_encounters){ ?>
										<div class="cell cell-ep"><?= $ep_label; ?></div>
									<?php } ?>
								</div>
							</div>
							<div class="admin-table-body sortable-row-container">
								<?php 
									$curlevel = 1; 
									$curXp = [];
									$curBloo = [];
								?>
								<?php foreach($quests['publish'] as $key=>$q){ ?>
									<?php if($q->mech_level > $curlevel && $show_added){ ?>
										<div class="row admin-row level-split">
											<?php
												if($curXp[$curlevel] >= ($curlevel*1000)){
													$heat_xp_lv = 'green-bg-400 grey-900';
												}else{
													$heat_xp_lv = 'red-bg-400 white-color';
												}
													
											?>
											<div class="cell cell-level">
												<?= __("Level","bluerabbit")." <strong>{ ".($curlevel)." }</strong>"; ?>
											</div>
											<div class="cell cell-xp <?= $heat_xp_lv;?>">
												<?= $xp_label." <strong>{ ".($curXp[$curlevel])." }</strong>"; ?>
											</div>
											<div class="cell cell-bloo ">
												<?= $bloo_label." <strong>{ ".($curBloo[$curlevel])." }</strong>"; ?>
											</div>
										</div>
										<?php $curlevel=$q->mech_level; ?>
									<?php } ?>
									<div class="row-container <?= $q->quest_type; ?> <?= $colors[$q->quest_type]; ?>" id="<?= $q->quest_type."-".$q->quest_id; ?>">
										<div class="row admin-row <?= $use_encounters ? 'with-ep' : ''; ?>">
											<div class="cell cell-drag drag-handle">
												<img src="<?= get_template_directory_uri(); ?>/images/drag-handle.svg" class="drag-icon">
											</div>
											<div class="cell cell-name">
												<?php if($current_user->ID==1){ echo "$q->quest_id | ";} ?>
												<input type="text" class="form-ui w-full" id="the_title-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->quest_title; ?>" onChange="setTitle(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
												<input type="hidden" class="quest-id" value="<?= $q->quest_id; ?>">
												<div class="quest-links font _12 w600 grey-600">
													<a class="" href="<?= get_bloginfo('url')."/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="" target="_blank">
														<?php _e("View","bluerabbit"). " $q->quest_type"; ?>
													</a> | 
													<?php if($q->quest_type =='challenge'){ ?>
														<a href="<?= get_bloginfo('url')."/challenges-report/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="" target="_blank">
															<?php _e("Challenge Report","bluerabbit"); ?>
														</a> |
													<?php }elseif($q->quest_type =='survey'){ ?>
														<a href="<?= get_bloginfo('url')."/survey-results/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="" target="_blank">
															<?php _e("Survey Report","bluerabbit"); ?>
														</a> |
													<?php }elseif($q->quest_type =='quest'){ ?>
														<a href="<?= get_bloginfo('url')."/review-player-posts/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="" target="_blank">
															<?php _e("Quest Review","bluerabbit"); ?>
														</a> |
													<?php } ?>
													<a href="<?= get_bloginfo('url')."/new-$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="">
														<span class="icon icon-edit "></span> <?php _e("Edit","bluerabbit"). " $q->quest_type"; ?>
													</a> | 
													<span class="inline-block cursor-pointer" onClick="activate('#<?= $q->quest_type."-".$q->quest_id; ?>');">
														<?php _e("Quick Edit","bluerabbit"); ?>
													</span>
												</div>
											</div>
											<div class="cell cell-badge">
												<input type="hidden" value="<?= $q->mech_badge; ?>" id="the_quest_badge-<?= $q->quest_id; ?>">
												<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_quest_badge-<?= $q->quest_id; ?>','a','quest',<?= $q->quest_id; ?>);" id="the_quest_badge-<?= $q->quest_id; ?>_thumb" style="background-image: url(<?= $q->mech_badge; ?>);">
												</button>
											</div>
											<div class="cell cell-color relative layer base">
												<button class="icon-button font _24 sq-40 <?=$q->quest_color;?>-bg-400" id="color-trigger-quest-<?= $q->quest_id; ?>" onClick="activate('#color-select-<?=$q->quest_id;?>');">
													<span class="icon icon-<?= $q->quest_icon; ?>"></span>
												</button> 
												<input type="hidden" value="<?= $q->quest_color; ?>" id="the_quest_color-<?= $q->quest_id; ?>">
											</div>
											<div class="cell cell-level">
												<div class="input-group">
													<input type="number" class="form-ui w-full" id="the_level-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_level; ?>" onChange="setLevel(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
												</div>
											</div>
											<div class="cell cell-xp">
												<div class="input-group">
													<label>
														<span class="icon icon-star"></span>
													</label>
													<input type="number" class="form-ui w-full" id="the_xp-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_xp; ?>" onChange="setXP(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
												</div>
											</div>
											<div class="cell cell-bloo">
												<div class="input-group">
													<label>
														<span class="icon icon-bloo"></span>
													</label>
													<input type="number" class="form-ui w-full" id="the_bloo-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_bloo; ?>" onChange="setBLOO(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
												</div>
											</div>
											<?php if($use_encounters){ ?>
												<div class="cell cell-ep">
													<div class="input-group">
														<label>
															<span class="icon icon-activity"></span>
														</label>
														<input type="number" class="form-ui w-full" id="the_ep-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_ep; ?>" onChange="setEP(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
													</div>
												</div>
											<?php } ?>
										</div>
										<div class="row admin-row color-select-row" id="color-select-<?= $q->quest_id; ?>">
											<?php
											$selected_color = $q->quest_color; 
											$object_color_id = $q->quest_id;
											$object_type='quest';
											?>
											<?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
										</div>
										<div class="row admin-row quick-edit" id="quick-edit-<?= $q->quest_type."-".$q->quest_id; ?>">
											<div class="cell cell-start-date">
												<div class="input-group">
												<label>
													<?= __("Start date","bluerabbit"); ?>
												</label>
												<?php
												if($q->mech_start_date != "0000-00-00 00:00:00" && $q->mech_start_date != NULL){ 
													$pretty_start_date = date('Y/m/d H:i', strtotime($q->mech_start_date));
												}else{
													$pretty_start_date = '';
												}
												?>
												<input autocomplete="off" class="form-ui text-center font w300 datetimepicker _14" autocomplete="off"  id="the_start_date-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" type="text" value="<?= $pretty_start_date; ?>" onChange="setStartDate(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');" >

												</div>
											</div>
											<div class="cell cell-deadline">
												<div class="input-group">
													<label>
														<?= __("Deadline","bluerabbit"); ?>
													</label>
													<?php
													if($q->mech_deadline != "0000-00-00 00:00:00" && $q->mech_deadline != NULL){ 
														$pretty_deadline = date('Y/m/d H:i', strtotime($q->mech_deadline));
													}else{
														$pretty_deadline = '';
													}
													?>
													<input autocomplete="off" class="form-ui text-center font w300 datetimepicker _14" autocomplete="off"  id="the_deadline-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" type="text" value="<?= $pretty_deadline; ?>" onChange="setDeadline(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');" >

												</div>
											</div>
											<div class="cell cell-path">
												<div class="input-group">
													<label>
														<?= __("Path","bluerabbit"); ?>
													</label>
													<select class="form-ui update-achievement" onChange="setAchievement(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>')">
														<option value="0"  <?php if(!$q->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
														<?php if($achievements['publish']){ ?>
															<?php foreach($achievements['publish'] as $a){ ?>
															<option value="<?= $a->achievement_id;?>" <?php if($q->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
															<?php } ?>
														<?php } ?>
													</select>
												</div>
											</div>
											<div class="cell cell-actions">
												<a href="<?= get_bloginfo('url')."/new-$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="form-ui button font _12 green-bg-400 edit-button">
													<span class="icon icon-edit "></span> <?php _e("Edit","bluerabbit"). " $q->quest_type"; ?>
												</a>
												<button class="form-ui button font _12 amber-bg-200 grey-700 duplicate-button" onClick="showOverlay('#confirm-duplicate-<?= $q->quest_id; ?>');">
													<?php _e("Duplicate","bluerabbit"); ?>
												</button>
												<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?= $q->quest_id; ?>">
													<button class="form-ui white-bg duplicate-confirm-button" onClick="duplicateRow(<?= $q->quest_id; ?>);">
														<span class="icon-group">
															<span class="icon-button font _24 sq-40  icon-sm amber-bg-A400 icon-sm">
																<span class="icon icon-duplicate white-color"></span>
															</span>
															<span class="icon-content">
																<span class="line amber-400 font _18 w900"><?php _e("Duplicate?","bluerabbit"); ?></span>
															</span>
														</span>
													</button>
													<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
														<span class="icon icon-cancel white-color"></span>
													</button>
												</div>
												<button class="form-ui button font _12 red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $q->quest_id; ?>');">
													<?php _e("Send to trash","bluerabbit"); ?>
												</button>
												<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $q->quest_id; ?>">
													<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $q->quest_id; ?>,'quest','trash');">
														<span class="icon-group">
															<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
																<span class="icon icon-trash white-color"></span>
															</span>
															<span class="icon-content">
																<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
															</span>
														</span>
													</button>
													<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
														<span class="icon icon-cancel white-color"></span>
													</button>
												</div>
											</div>
										</div>
									</div>
									<?php
										$curXp[$q->mech_level] += $q->mech_xp;
										$curBloo[$q->mech_level] += $q->mech_bloo;
									?>
								<?php } ?>
								<?php if($show_added){ ?>

									<div class="row admin-row level-split">
										<div class="cell cell-level">
											<?= __("Level","bluerabbit")." <strong>{ ".($curlevel)." }</strong>"; ?>
										</div>
										<?php
											if($curXp[$curlevel] >= ($curlevel*1000)){
												$heat_xp_lv = 'green-bg-400 grey-900';
											}else{
												$heat_xp_lv = 'red-bg-400 white-color';
											}
												
										?>
										<div class="cell cell-xp <?= $heat_xp_lv; ?>">
											<?= $xp_label." <strong>{ ".($curXp[$curlevel])." }</strong>"; ?>
										</div>
										<div class="cell cell-bloo">
											<?= $bloo_label." <strong>{ ".($curBloo[$curlevel])." }</strong>"; ?>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="highlight padding-10 blue-bg-800 white-color sticky layer overlay bottom-50 text-center">
							<?php $totalXP = array_sum($curXp); ?>
							<?php $totalBLOO = array_sum($curBloo); ?>
							<div class="icon-group">
								<span class="icon-button font _24 sq-40  light-blue-bg-400">
									<span class="icon icon-quest"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?= count($quests['publish']); ?></span>
									<span class="line font _14 w400"> <?= __("Total Quests","bluerabbit"); ?></span>
								</div>
								<span class="icon-button font _24 sq-40  amber-bg-400">
									<span class="icon icon-star"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?= $totalXP; ?></span>
									<span class="line font _14 w400"> <?= __("Total","bluerabbit")." $xp_label"; ?></span>
								</div>
								<span class="icon-button font _24 sq-40  green-bg-400">
									<span class="icon icon-bloo"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?= $totalBLOO; ?></span>
									<span class="line font _14 w400"> <?= __("Total","bluerabbit")." $bloo_label"; ?></span>
								</div>
								<div class="icon-content">
									<button class="form-ui blue-bg-400 font _16 main w300" onclick="reorder()">
										<span class="icon icon-list"></span> <strong><?php _e("Reorder Journey","bluerabbit"); ?></strong>
									</button>
								</div>
							</div>
						</div>
					<?php }else{ ?> 
						<div class="highlight padding-10 grey-bg-50 text-center font _18 condensed w500">
							<span class="icon icon-cancel"></span> <?php _e("No quests found","bluerabbit"); ?>
						</div>
					<?php } ?>
					<input type="hidden" id="row_type" value="quest"/>
				</div>
				<div class="content">
					<?php if(isset($quests['draft'])){ ?>
						<div class="highlight padding-10 amber-bg-50 ">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  amber-bg-400 icon-sm">
									<span class="icon icon-document"></span>
								</span>
								<span class="icon-content">
									<span class="line font _24 condensed w500"><?php _e("Journey Drafts","bluerabbit"); ?></span>
								</span>
							</span>
						</div>
						<table class="table compact table-quests" id="table-draft-quests">
							<thead>
								<tr>
									<td class="cell-1"><?php _e("Level","bluerabbit"); ?></td>
									<td class="cell-2"><strong><?php _e("Name","bluerabbit"); ?></strong></td>
									<td class="cell-2"><span class="icon icon-star solid-amber"></span></td>
									<td class="cell-2"><span class="icon icon-bloo solid-green"></span></td>
									<td class="cell-2"><span class="icon icon-edit solid-green"></span></td>
									<td class="cell-1"><?php _e("Status","bluerabbit"); ?></td>
									<td class="cell-2"><?php _e("Achievement","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody class="sortable">
								<?php foreach($quests['draft'] as $key=>$q){ ?>
									<tr class="quest-item <?= $colors[$q->quest_type]; ?>" id="<?= $q->quest_type."-".$q->quest_id; ?>">
										<td class="cell-1">
											<span class="icon icon-<?= $q->quest_type; ?>"></span>
											<span class="hidden">Level</span> <?= $q->mech_level; ?>
											<input type="hidden" class="quest-id" value="<?= $q->quest_id; ?>">
										</td>
										<td class="cell-3"><strong><?= $q->quest_title; ?></strong></td>
										<td class="cell-2">
											<div class="input-group">
												<label>
													<span class="icon icon-star"></span>
												</label>
												<input type="number" class="form-ui" id="the_xp-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_xp; ?>" onChange="setXP(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
											</div>
										</td>
										<td class="cell-2">
											<div class="input-group">
												<label>
													<span class="icon icon-bloo"></span>
												</label>
												<input type="number" class="form-ui" id="the_bloo-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_bloo; ?>" onChange="setBLOO(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>');">
											</div>
										</td>
										<td class="cell-1">
											<a href="<?= get_bloginfo('url')."/new-$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="icon-button font _24 sq-40  icon-sm green-bg-400">
												<span class="icon icon-edit"></span>
												<span class="tool-tip">
													<span class="tool-tip-text"><?php _e("Edit","bluerabbit"); ?></span>
												</span>
											</a>
											<a href="<?= get_bloginfo('url')."/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="icon-button font _24 sq-40  icon-sm indigo-bg-300" target="_blank">
												<span class="icon icon-view"></span>
												<span class="tool-tip">
													<span class="tool-tip-text"><?php _e("View","bluerabbit"); ?></span>
												</span>
											</a>
										</td>
										<td class="cell-1">
											<select class="form-ui update-status" onChange="updateStatus(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>')">
												<option value=""><?php _e("Draft","bluerabbit"); ?></option>
												<option value="trash"><?php _e("Trash","bluerabbit"); ?></option>
												<option value="publish"><?php _e("Publish","bluerabbit"); ?></option>
											</select>
										</td>
										<td class="cell-2">
											<select class="form-ui update-achievement" onChange="setAchievement(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>')">
												<option value="0"  <?php if(!$quest->achievement_id){ echo 'selected'; }?>><?php _e('All players','bluerabbit'); ?></option>
												<?php if($achievements['publish']){ ?>
													<?php foreach($achievements['publish'] as $a){ ?>
													<option value="<?= $a->achievement_id;?>" <?php if($q->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
													<?php } ?>
												<?php } ?>
											</select>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php }else{ ?> 
						<div class="highlight padding-10 grey-bg-50 text-center font _18 condensed w500">
							<span class="icon icon-cancel"></span> <?php _e("No draft quests found","bluerabbit"); ?>
						</div>
					<?php } ?>
				</div>
				<div class="content">
						<?php if(isset($quests['trash'])){ ?>
							<div class="highlight padding-10 red-bg-800 white-color ">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  red-bg-400 icon-sm">
										<span class="icon icon-trash"></span>
									</span>
									<span class="icon-content">
										<span class="line font _24 condensed w500"><?php _e("Journey Trash","bluerabbit"); ?></span>
									</span>
								</span>
								<span class="block w-100 pull-right">&nbsp;</span>
								<span class="block pull-right relative">
									
									<button class="form-ui red-bg-A400 white-color empty-trash-button" onClick="showOverlay('#confirm-empty-trash');">
										<?php _e("Empty Trash","bluerabbit"); ?>
									</button>
									<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-empty-trash">
										<button class="form-ui white-bg border border-all red-border-A400 duplicate-confirm-button" onClick="emptyTrash('quest');">
											<span class="icon-group">
												<span class="icon-button font _24 sq-40 red-bg-A400">
													<span class="icon icon-cancel white-color"></span>
												</span>
												<span class="icon-content">
													<span class="line red-400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
												</span>
											</span>
										</button>
										<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
											<span class="icon icon-cancel white-color"></span>
										</button>
									</div>
									
									
								</span>
							</div>
							<table class="table compact table-quests" id="table-draft-quests">
								<thead>
									<tr>
										<td class=""><?php _e("Level","bluerabbit"); ?></td>
										<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
										<td class=""><span class="icon icon-star solid-amber"></span></td>
										<td class=""><span class="icon icon-bloo solid-green"></span></td>
										<td class=""><?php _e("Status","bluerabbit"); ?></td>
									</tr>
								</thead>
								<tbody class="">
									<?php foreach($quests['trash'] as $key=>$q){ ?>
									
										<tr class="quest-item red-bg-50" id="<?= $q->quest_type."-".$q->quest_id; ?>">
											<td class="">
												<span class="icon icon-<?= $q->quest_type; ?>"></span>
												<span class="hidden">Level</span> <?= $q->mech_level; ?>
												<input type="hidden" class="quest-id" value="<?= $q->quest_id; ?>">
											</td>
											<td class=""><strong><?= $q->quest_title; ?></strong></td>
											<td class="">
												<div class="input-group">
													<label><span class="icon icon-star"></span></label>
													<input type="number" class="form-ui" disabled id="the_xp-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_xp; ?>">
												</div>
											</td>
											<td class="">
												<div class="input-group">
													<label> <span class="icon icon-bloo"></span> </label>
													<input type="number" class="form-ui" disabled id="the_bloo-<?= $q->quest_type; ?>-<?= $q->quest_id; ?>" value="<?= $q->mech_bloo; ?>">
												</div>
											</td>
											<td class="cell-1">
												<select class="update-status form-ui" onChange="updateStatus(<?= $q->quest_id; ?>,'<?= $q->quest_type; ?>')">
													<option value=""><?php _e("Trash","bluerabbit"); ?></option>
													<option value="publish"><?php _e("Publish","bluerabbit"); ?></option>
													<option value="draft"><?php _e("Draft","bluerabbit"); ?></option>
													<option value="delete"><?php _e("Delete","bluerabbit"); ?></option>
												</select>
											</td>
										</tr>
									<?php } ?>
									
								</tbody>
							</table>
						<?php }else{ ?>
							<div class="highlight padding-10 grey-bg-50 text-center font _18 condensed w500">
								<span class="icon icon-cancel"></span> <?php _e("No quests found in trash","bluerabbit"); ?>
							</div>
						<?php } ?>

</div>
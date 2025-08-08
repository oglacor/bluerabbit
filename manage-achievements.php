<?php
$allPaths =  getAchievements($adventure->adventure_id,'path'); 
if(isset($_GET['path'])){
	$path = $_GET['path'];
	$achievements = array();
	if($path =='general'){
		$qry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id AND (achievement_path=0 OR achievement_path IS NULL) ORDER BY FIELD(achievement_display, 'badge', 'path', 'rank'), achievement_order, achievement_name, achievement_id");
	}else{
		$qry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id AND (achievement_path=$path OR achievement_id=$path) ORDER BY FIELD(achievement_display, 'badge', 'path', 'rank'), achievement_order, achievement_name, achievement_id");
		
	}
	foreach($qry as $o){
		if($o->achievement_status == 'trash'){
			$achievements['trash'][]=$o;
		}elseif($o->achievement_status == 'draft'){
			$achievements['draft'][]=$o;
		}elseif($o->achievement_status == 'publish'){
			$achievements['publish'][]=$o;
		}
	}
}else{
	$achievements = getAchievements($adventure->adventure_id); 
} 
?>
				<input type="hidden" id="magic-code-nonce" value="<?php echo wp_create_nonce('magic_code_nonce'); ?>" />
				<input type="hidden" id="max-players-nonce" value="<?php echo wp_create_nonce('max_players_nonce'); ?>" />
					<div class="highlight padding-10 deep-purple-bg-50">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-achievement"></span></span>
							<span class="icon-content">
								<span class="line font _24 grey-800"><?php _e('Achievements','bluerabbit'); ?></span>
							</span>
						</span>
						<span class="icon-group">
							<span class="icon-content">
								<span class="line font _14 grey-800"><?php _e('Filter Path','bluerabbit'); ?></span>
							</span>
							<a class="icon-button font _24 sq-40  icon-sm black-bg" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=achievements";?>">
								<span class="icon icon-infinite"></span>
							</a>
							<a class="icon-button font _24 sq-40  icon-sm purple-bg-400" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=achievements&path=general";?>">
								<span class="icon icon-achievement"></span>
							</a>
							<?php foreach($allPaths['publish'] as $a){ ?>
								<?php if($a->achievement_display=='path'){ ?>
									<?php if(isset($path) && $path == $a->achievement_id){ ?>
										<button class="icon-button font _24 sq-40  icon-sm <?= $a->achievement_color; ?>-bg-400 blend-luminosity" style="background-image: url(<?= $a->achievement_badge; ?>)"></button>
										<?php $current_path = $a; ?>
									<?php }else{ ?>
										<a class="icon-button font _24 sq-40  icon-sm grey-bg-400 blend-luminosity opacity-50" style="background-image: url(<?= $a->achievement_badge; ?>)" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id={$adventure->adventure_id}&manage=achievements&path={$a->achievement_id}"; ?>"></a>
									<?php } ?>
								<?php } ?> 
							<?php } ?> 

						</span>
						<div class="input-group pull-right">
							<div class="form-ui font _14">
								<form id="upload_bulk_achievements_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
									<table>
										<tbody>
											<tr>
												<td class="w-200">
													<label for="the_csv_file_with_achievements" class="">Upload Achievements:</label>
													<input type="file" name="the_csv_file_with_achievements" id="the_csv_file_with_achievements" size="20" />
												</td>
												<td class="w-100">
													<button type="button" onClick="uploadBulkAchievements();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
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
									<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
									<label>
										<span class="icon icon-search"></span>
									</label>
									<script>
										$('#search').keyup(function(){
											var valThis = $(this).val().toLowerCase();
											if(valThis == ""){
												$('table#table-achievement tbody > tr').show();           
											}else{
												$('table#table-achievement tbody > tr').each(function(){
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
				<?php if($achievements['publish']){ ?>
					<div class="content">
						<table class="table compact table-achievement" id="table-achievement">
							<thead>
								<tr class="text-center">
									<td class=""><span class="icon icon-image"></span></td>
									<td class=""><?php _e("Color","bluerabbit"); ?></td>
									<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
									<?php if($a->achievement_display !='rank'){ ?>
										<td class=""><?php _e("Magic Code","bluerabbit"); ?></td>
										<td class=""><?php _e("Max Players","bluerabbit"); ?></td>
									<?php } ?>
									<td class=""><span class="icon icon-star"></span></td>
									<td class=""><span class="icon icon-bloo"></span></td>
									<td class=""><span class="icon icon-infinite"></span></td>
									<td class=""><span class="icon icon-edit"></span></td>
									<td class=""><span class="icon icon-trash"></span></td>
								</tr>
							</thead>
							<tbody class="sortable">
								<?php $curXp = $curBloo = 0;  ?>
								<?php $colorXP = array('red'=>0,'pink'=>0,'purple'=>0,'deep-purple'=>0,'indigo'=>0,'blue'=>0,'light-blue'=>0,'cyan'=>0,'teal'=>0,'green'=>0,'light-green'=>0,'lime'=>0,'yellow'=>0,'amber'=>0,'orange'=>0,'deep-orange'=>0,'brown'=>0,'grey'=>0,'blue-grey'=>0);  ?>
								
								
								<?php $colorBLOO = array('red'=>0,'pink'=>0,'purple'=>0,'deep-purple'=>0,'indigo'=>0,'blue'=>0,'light-blue'=>0,'cyan'=>0,'teal'=>0,'green'=>0,'light-green'=>0,'lime'=>0,'yellow'=>0,'amber'=>0,'orange'=>0,'deep-orange'=>0,'brown'=>0,'grey'=>0,'blue-grey'=>0);  ?>
								<?php $curr = 'badge'; $icon = 'achievement'; ?>
								<tr class="unsortable opacity-100">
									<td colspan="8">
										<div class="icon-group">
											<div class="icon-button font _24 sq-40  purple-bg-400">
												<span class="icon icon-achievement"></span>
											</div>
											<div class="icon-content">
												<span class="line font _24 purple-300 w100 kerning-2"><?=__("Achievements","bluerabbit");?></span>
												<span class="line font _14 black-color w400 opacity-50"><?=__("Earned through magic codes, qr codes or by being awesome","bluerabbit");?></span>
											</div>
										</div>
									</td>
								</tr>
								<?php foreach($achievements['publish'] as $key=>$a){ ?>
									<?php if($curr != $a->achievement_display){ ?>
										<?php
											$curr = $a->achievement_display; 
											$str = '';
											if($a->achievement_display == 'path' ){
												$str = __("Paths","bluerabbit");
												$str2 = __("Changes the player journey","bluerabbit");
												$icon = 'journey';
											}else if($a->achievement_display == 'rank' ){
												$str = __("Ranks","bluerabbit");
												$str2 = __("Earned by leveling up","bluerabbit");
												$icon = 'rank';
											}
										?>
										<tr class="unsortable opacity-100">
											<td colspan="8"  class="">
												<div class="icon-group">
													<div class="icon-button font _24 sq-40  purple-bg-400">
														<span class="icon icon-<?= $icon; ?>"></span>
													</div>
													<div class="icon-content">
														<span class="line font _24 purple-A100 w100 kerning-2"><?=$str;?></span>
														<span class="line font _14 black-color w400 opacity-50"><?=$str2;?></span>
													</div>
												</div>
											</td>
										</tr>
									<?php } ?>
									<tr id="achievement-<?php echo $a->achievement_id;?>" class="achievement purple-bg-50">
										<td class="">
											<input type="hidden" value="<?= $a->achievement_badge; ?>" id="the_achievement_badge-<?= $a->achievement_id; ?>">
											<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_achievement_badge-<?= $a->achievement_id; ?>','a','achievement',<?= $a->achievement_id; ?>);" id="the_achievement_badge-<?= $a->achievement_id; ?>_thumb" style="background-image: url(<?= $a->achievement_badge; ?>);">
											</button>
										</td>
										<td class="color relative layer base">
											<input type="hidden" value="<?= $a->achievement_color; ?>" id="the_achievement_color-<?= $a->achievement_id; ?>">
											<button class="icon-button font _24 sq-40 <?=$a->achievement_color;?>-bg-400" id="color-trigger-achievement-<?= $a->achievement_id; ?>" onClick="activate('#color-select-<?=$a->achievement_id;?>');"><span class="icon icon-<?= $icon; ?>"></span>
											</button> 
											<div class="color-select-popup" id="color-select-<?=$a->achievement_id;?>">
												<?php
												$selected_color = $a->achievement_color; 
												$object_color_id = $a->achievement_id;
												$object_type='achievement';
												?>
												<?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
											</div>
										</td>
										<td class="">
											<input type="text" class="form-ui row-title <?php echo $a->achievement_color; ?>-bg-400 white-color" id="the_title-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_name; ?>" onChange="setTitle(<?php echo $a->achievement_id; ?>,'achievement');">
											<input type="hidden" class="achievement-id" value="<?php echo $a->achievement_id; ?>">
										</td>
										<?php if($a->achievement_display !='rank'){ ?>
											<td class="">
												<div class="input-group w-full">
													<input type="text" class="form-ui magic-code" id="the_magic_code-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_code; ?>" onChange="setMagicCode(<?php echo $a->achievement_id; ?>);" maxlength="250">
													<label class="cyan-bg-400">
														<button class="form-ui create-magic-code-button" onClick="createMagicCode(<?php echo $a->achievement_id; ?>);">
															<span class="icon icon-magic"></span>
														</button>
													</label>
													<label class="amber-bg-400">
														<button class="form-ui revert-magic-code-button" onClick="revertMagicCode(<?php echo $a->achievement_id; ?>,'<?php echo $a->achievement_code; ?>');">
															<span class="icon icon-restore"></span>
														</button>
													</label>

												</div>
											</td>
										<?php } ?>
										<?php if($a->achievement_display !='rank'){ ?>
											<td class="">
												<div class="input-group w-full">
													<label>
														<span class="icon icon-players"></span>
													</label>
													<input type="number" class="form-ui" id="the_max_players-achievement-<?= $a->achievement_id; ?>" value="<?= $a->achievement_max; ?>" onChange="setMaxPlayers(<?= $a->achievement_id; ?>);">
												</div>
											</td>
											<td class="">
												<div class="input-group">
													<label>
														<span class="icon icon-star"></span>
													</label>
													<input type="number" class="form-ui the-xp" id="the_xp-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_xp; ?>" onChange="setXP(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
											<td class="">
												<div class="input-group">
													<label>
														<span class="icon icon-bloo"></span>
													</label>
													<input type="number" class="form-ui the-bloo" id="the_bloo-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_bloo; ?>" onChange="setBLOO(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
										<?php }else{ ?>
											<td colspan="4">
												<h4 class="font _18 grey-800 text-center"><?= __("These options aren't used in ranks","bluerabbit"); ?></h4>
											</td>
										<?php } ?>
										<td class="">
											<button class="icon-button font _24 sq-40  icon-sm amber-bg-200 grey-700 duplicate-button" onClick="showOverlay('#confirm-duplicate-<?php echo $a->achievement_id; ?>');">
												<span class="icon icon-duplicate"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?php _e("Duplicate","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?php echo $a->achievement_id; ?>">
												<button class="form-ui white-bg duplicate-confirm-button" onClick="duplicateRow(<?php echo $a->achievement_id; ?>);">
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
										</td>
										<td class="">
											<a href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id&achievement_id=$a->achievement_id";?>" class="icon-button font _24 sq-40  icon-sm green-bg-400 edit-button"><span class="icon icon-edit "></span></a>
											<?php if($a->achievement_parent){ ?>
											<button class="form-ui" type="button" onClick="breakParent(<?= $a->achievement_id; ?>, 'achievement');"><?= __("Break Parent","bluerabbit"); ?></button>
											<?php } ?>
										</td>
										<td class="">
											<button class="icon-button font _24 sq-40  icon-sm red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?php echo $a->achievement_id; ?>');">
												<span class="icon icon-trash"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $a->achievement_id; ?>">
												<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','trash');">
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
										</td>
									</tr>
									<?php 
										$colorXP[$a->achievement_color]+=$a->achievement_xp;
										$colorBLOO[$a->achievement_color]+=$a->achievement_bloo;
										$curXp+= $a->achievement_xp;
										$curBloo+= $a->achievement_bloo;
									?>
								<?php } ?>
							</tbody>
						</table>
						
						<div class="highlight padding-10 deep-purple-bg-900 white-color sticky-bottom text-center">
							<div class="icon-group">
								<span class="icon-button font _24 sq-40  purple-bg-300">
									<span class="icon icon-achievement"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?php echo count($achievements['publish']); ?></span>
									<span class="line font _14 w400"> <?php echo __("Total Achievements","bluerabbit"); ?></span>
								</div>
								<span class="icon-button font _24 sq-40  amber-bg-400">
									<span class="icon icon-star"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?php echo $curXp; ?></span>
									<span class="line font _14 w400"> <?php echo __("Total","bluerabbit")." $xp_label"; ?></span>
								</div>
								<span class="icon-button font _24 sq-40  green-bg-400">
									<span class="icon icon-bloo"></span>
								</span>
								<div class="icon-content">
									<span class="line font _24 w400"> <?php echo $curBloo; ?></span>
									<span class="line font _14 w400"> <?php echo __("Total","bluerabbit")." $bloo_label"; ?></span>
								</div>
								<div class="icon-content">
									<button class="form-ui purple-bg-300 font _16 main w300" onclick="reorderAchievements('#table-achievement')">
										<span class="icon icon-list"></span> <strong><?php _e("Reorder Achievements","bluerabbit"); ?></strong>
									</button>
								</div>
							</div>
						</div>
						<div class="highlight padding-10 text-center grey-bg-50">
							<span class="icon-group">
								<?php foreach($colorXP as $key=>$color){ ?>
									<?php if($color > 0){ ?>
										<span class="icon-content <?php echo $key; ?>-bg-400 white-color">
											<span class="line font _18 w400  padding-5"> <?php echo __("XP").": $color"; ?> </span>
											<span class="line font _18 w400  padding-5"> <?php echo __("Coins").": ".$colorBLOO[$key]; ?> </span>
										</span>
									<?php } ?>
								<?php } ?>
							</span>
						</div>
					</div>
				<?php }else{ ?> 
					<div class="highlight padding-10 deep-purple-bg-50">
						<span class="icon-group text-center">
							<span class="icon-content">
								<span class="icon icon-cancel"></span> <?php _e("No achievements found","bluerabbit"); ?>
							</span>
						</span>
					</div>
				<?php } ?>
				<?php if(isset($achievements['trash'])){ ?>
					<div class="highlight padding-10 deep-purple-bg-50">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-trash"></span></span>
							<span class="icon-content">
								<span class="line font _24 grey-800"><?php _e('Trashed Achievements','bluerabbit'); ?></span>
							</span>
						</span>
						<div class="highlight-cell pull-right padding-10">
							<div class="search sticky">
								<div class="input-group">
									<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
									<label>
										<span class="icon icon-search"></span>
									</label>
									<script>
										$('#search').keyup(function(){
											var valThis = $(this).val().toLowerCase();
											if(valThis == ""){
												$('table#trashed-achievements tbody > tr').show();           
											}else{
												$('table#trashed-achievements tbody > tr').each(function(){
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
						<table class="table compact table-achievements" id="table-trash-achievement">
							<thead>
								<tr>
									<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
									<td class=""><?php _e("Magic Code","bluerabbit"); ?></td>
									<td class=""><span class="icon icon-star solid-amber"></span></td>
									<td class=""><span class="icon icon-bloo solid-green"></span></td>
									<td class=""><span class="icon icon-edit solid-green"></span></td>
									<td class=""><?php _e("Status","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody class="sortable">
								<?php foreach($achievements['trash'] as $key=>$a){ ?>
									<tr id="achievement-<?php echo $a->achievement_id;?> purple-bg-50">
										<td class="">
											<strong><?php echo $a->achievement_name; ?></strong>
										</td>
										<td class=""><?php echo $a->achievement_code; ?></td>
										<td class="">
											<div class="input-group">
												<label>
													<span class="icon icon-star"></span>
												</label>
												<input type="number" class="form-ui" id="the_xp-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_xp; ?>" onChange="setXP(<?php echo $a->achievement_id; ?>,'achievement');">
											</div>
										</td>
										<td class="">
											<div class="input-group">
												<label>
													<span class="icon icon-bloo"></span>
												</label>
												<input type="number" class="form-ui" id="the_bloo-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_bloo; ?>" onChange="setBLOO(<?php echo $a->achievement_id; ?>,'achievement');">
											</div>
										</td>
										<td class="">
											<a href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id&achievement_id=$a->achievement_id";?>" class="form-ui green"><span class="icon icon-edit"></span></a>
										</td>
										<td class="">

											<button class="icon-button font _24 sq-40  blue-bg-A400 white-color" onClick="showOverlay('#confirm-option-restore-<?php echo $a->achievement_id; ?>');">
												<span class="icon icon-restore"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?php _e("Restore","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-restore-<?php echo $a->achievement_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','publish');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  blue-bg-A700 icon-sm">
															<span class="icon icon-restore white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line blue-A700 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
														</span>
													</span>
												</button>
												<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
													<span class="icon icon-cancel white-color"></span>
												</button>
											</div>
											<button class="icon-button font _24 sq-40  red-bg-A400 white-color" onClick="showOverlay('#confirm-option-delete-<?php echo $a->achievement_id; ?>');">
												<span class="icon icon-cancel"></span>
												<span class="tool-tip bottom">
													<span class="tool-tip-text font _12"><?php _e("Delete Forever","bluerabbit"); ?></span>
												</span>
											</button>
											<div class="confirm-action overlay-layer" id="confirm-option-delete-<?php echo $a->achievement_id; ?>">
												<button class="form-ui white-bg" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','delete');">
													<span class="icon-group">
														<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
															<span class="icon icon-cancel white-color"></span>
														</span>
														<span class="icon-content">
															<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
															<span class="line font _14 grey-400"><?php _e("You can't undo this","bluerabbit"); ?></span>
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
					<div class="highlight padding-10 deep-purple-bg-50">
						<span class="icon-group text-center">
							<span class="icon-content">
								<span class="icon icon-cancel"></span> <?php _e("No achievements found in trash","bluerabbit"); ?>
							</span>
						</span>
					</div>
				<?php } ?>
			<input type="hidden" id="row_type" value="achievement"/>
<?php $achievements_table = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id AND achievement_status='publish' ORDER BY  achievement_id, achievement_name");; ?>
<div class="content">
	<table>
		<thead>
			<tr>
				<td>Link</td>
			</tr>
		</thead>
		<tbody>
		<?php foreach($achievements_table as $t_ach){ ?>
			<?php $magicLink = get_bloginfo('url')."/magic-link/?c=$t_ach->achievement_code&adv=$t_ach->adventure_id"; ?>
			<tr>
				<td><?= $magicLink;?></td>
				<td><?=$t_ach->achievement_name;?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>




<?php
$allPaths =  BR_Achievement::instance()->getAchievements($adventure->adventure_id,'path');
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
	$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id);
}

$achievement_award_counts = array();
$award_counts_qry = $wpdb->get_results($wpdb->prepare(
	"SELECT achievement_id, COUNT(*) as cnt FROM {$wpdb->prefix}br_player_achievement WHERE adventure_id=%d GROUP BY achievement_id",
	$adventure_id
));
foreach($award_counts_qry as $row){
	$achievement_award_counts[$row->achievement_id] = $row->cnt;
}
?>
<div class="br-journey-manager">
				<input type="hidden" id="magic-code-nonce" value="<?php echo wp_create_nonce('magic_code_nonce'); ?>" />

				<!-- ════════════ HEADER BAR ════════════ -->
				<div class="br-header">
					<div class="br-header-left">
						<div class="br-icon"><span class="icon icon-achievement"></span></div>
						<div>
							<h2><?php _e('Achievements','bluerabbit'); ?></h2>
							<div class="br-header-counts">
								<span class="br-count-badge"><span class="br-cnt"><?= isset($achievements['publish']) ? count($achievements['publish']) : 0; ?></span> <?php _e('Published','bluerabbit'); ?></span>
								<?php if(isset($achievements['draft']) && count($achievements['draft']) > 0){ ?>
									<span class="br-count-badge type-mission"><span class="br-cnt"><?= count($achievements['draft']); ?></span> <?php _e('Drafts','bluerabbit'); ?></span>
								<?php } ?>
								<?php if(isset($achievements['trash']) && count($achievements['trash']) > 0){ ?>
									<span class="br-count-badge type-challenge"><span class="br-cnt"><?= count($achievements['trash']); ?></span> <?php _e('Trashed','bluerabbit'); ?></span>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="br-header-right">
						<!-- Path Filters -->
						<a class="br-btn ghost" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=achievements";?>">
							<span class="icon icon-infinite"></span> <?php _e('All','bluerabbit'); ?>
						</a>
						<a class="br-btn purple" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=achievements&path=general";?>">
							<span class="icon icon-achievement"></span> <?php _e('General','bluerabbit'); ?>
						</a>
						<?php if(isset($allPaths['publish'])){ foreach($allPaths['publish'] as $a){ ?>
							<?php if($a->achievement_display=='path'){ ?>
								<?php if(isset($path) && $path == $a->achievement_id){ ?>
									<button class="br-btn cyan" style="background-image: url(<?= $a->achievement_badge; ?>); background-size:cover; background-position:center; width:36px; height:36px; padding:0; border-radius:8px;"></button>
									<?php $current_path = $a; ?>
								<?php }else{ ?>
									<a class="br-btn ghost" style="background-image: url(<?= $a->achievement_badge; ?>); background-size:cover; background-position:center; width:36px; height:36px; padding:0; border-radius:8px; opacity:0.5;" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id={$adventure->adventure_id}&manage=achievements&path={$a->achievement_id}"; ?>"></a>
								<?php } ?>
							<?php } ?>
						<?php } } ?>

						<!-- Bulk Upload -->
						<form id="upload_bulk_achievements_form" class="br-bulk-upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">
							<input type="file" name="the_csv_file_with_achievements" id="the_csv_file_with_achievements" />
							<button type="button" class="br-btn ghost" onClick="uploadBulkAchievements();">
								<span class="icon icon-upload"></span> <?= __("Upload file","bluerabbit"); ?>
							</button>
						</form>
					</div>
				</div>

				<!-- ════════════ STICKY TOOLBAR ════════════ -->
				<div class="br-toolbar">
					<div class="br-search">
						<span class="icon icon-search"></span>
						<input type="text" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
					</div>
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

				<!-- ════════════ PUBLISHED ACHIEVEMENTS ════════════ -->
				<?php if(isset($achievements['publish']) && $achievements['publish']){ ?>
					<div class="br-table-wrap">
						<table class="br-table table-achievement" id="table-achievement">
							<thead>
								<tr class="text-center">
									<th><span class="icon icon-image"></span></th>
									<th><?php _e("Color","bluerabbit"); ?></th>
									<th><?php _e("Name","bluerabbit"); ?></th>
									<?php if(isset($a) && $a->achievement_display !='rank'){ ?>
										<th><?php _e("Magic Code","bluerabbit"); ?></th>
										<th><?php _e("Awarded","bluerabbit"); ?></th>
									<?php } ?>
									<th><span class="icon icon-star"></span></th>
									<th><span class="icon icon-bloo"></span></th>
									<th><span class="icon icon-infinite"></span></th>
									<th><span class="icon icon-edit"></span></th>
									<th><span class="icon icon-trash"></span></th>
								</tr>
							</thead>
							<tbody class="sortable">
								<?php $curXp = $curBloo = 0;  ?>
								<?php $colorXP = array('red'=>0,'pink'=>0,'purple'=>0,'deep-purple'=>0,'indigo'=>0,'blue'=>0,'light-blue'=>0,'cyan'=>0,'teal'=>0,'green'=>0,'light-green'=>0,'lime'=>0,'yellow'=>0,'amber'=>0,'orange'=>0,'deep-orange'=>0,'brown'=>0,'grey'=>0,'blue-grey'=>0);  ?>


								<?php $colorBLOO = array('red'=>0,'pink'=>0,'purple'=>0,'deep-purple'=>0,'indigo'=>0,'blue'=>0,'light-blue'=>0,'cyan'=>0,'teal'=>0,'green'=>0,'light-green'=>0,'lime'=>0,'yellow'=>0,'amber'=>0,'orange'=>0,'deep-orange'=>0,'brown'=>0,'grey'=>0,'blue-grey'=>0);  ?>
								<?php $curr = 'badge'; $icon = 'achievement'; ?>
								<tr class="unsortable">
									<td colspan="10">
										<div class="br-actions">
											<span class="br-type-icon quest"><span class="icon icon-achievement"></span></span>
											<div>
												<span class="br-panel-title"><?=__("Achievements","bluerabbit");?></span>
												<span class="br-badge br-badge-blue"><?=__("Earned through magic codes, qr codes or by being awesome","bluerabbit");?></span>
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
										<tr class="unsortable">
											<td colspan="10">
												<div class="br-actions">
													<span class="br-type-icon quest"><span class="icon icon-<?= $icon; ?>"></span></span>
													<div>
														<span class="br-panel-title"><?=$str;?></span>
														<span class="br-badge br-badge-blue"><?=$str2;?></span>
													</div>
												</div>
											</td>
										</tr>
									<?php } ?>
									<tr id="achievement-<?php echo $a->achievement_id;?>" class="achievement">
										<td>
											<input type="hidden" value="<?= $a->achievement_badge; ?>" id="the_achievement_badge-<?= $a->achievement_id; ?>">
											<button class="br-thumb" onClick="showWPUpload('the_achievement_badge-<?= $a->achievement_id; ?>','a','achievement',<?= $a->achievement_id; ?>);" id="the_achievement_badge-<?= $a->achievement_id; ?>_thumb" style="background-image: url(<?= $a->achievement_badge; ?>);">
											</button>
										</td>
										<td class="color relative layer base">
											<input type="hidden" value="<?= $a->achievement_color; ?>" id="the_achievement_color-<?= $a->achievement_id; ?>">
											<button class="br-type-icon" id="color-trigger-achievement-<?= $a->achievement_id; ?>" onClick="activate('#color-select-<?=$a->achievement_id;?>');" style="background:rgba(var(--color-rgb),0.3);"><span class="icon icon-<?= $icon; ?>"></span>
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
										<td>
											<div class="br-name">
												<input type="text" id="the_title-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_name; ?>" onChange="setTitle(<?php echo $a->achievement_id; ?>,'achievement');">
											</div>
											<input type="hidden" class="achievement-id" value="<?php echo $a->achievement_id; ?>">
										</td>
										<?php if($a->achievement_display !='rank'){ ?>
											<td>
												<div class="br-input-row">
													<input type="text" class="br-input magic-code" id="the_magic_code-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_code; ?>" onChange="setMagicCode(<?php echo $a->achievement_id; ?>);" maxlength="250">
													<button class="br-btn br-btn-blue create-magic-code-button" onClick="createMagicCode(<?php echo $a->achievement_id; ?>);">
														<span class="icon icon-magic"></span>
													</button>
													<button class="br-btn br-btn-amber revert-magic-code-button" onClick="revertMagicCode(<?php echo $a->achievement_id; ?>,'<?php echo $a->achievement_code; ?>');">
														<span class="icon icon-restore"></span>
													</button>
												</div>
											</td>
										<?php } ?>
										<?php if($a->achievement_display !='rank'){ ?>
											<td>
												<div class="br-num br-num-static">
													<span class="icon icon-players"></span>
													<span><?= isset($achievement_award_counts[$a->achievement_id]) ? $achievement_award_counts[$a->achievement_id] : 0; ?></span>
												</div>
											</td>
											<td>
												<div class="br-num">
													<span class="icon icon-star"></span>
													<input type="number" class="the-xp" id="the_xp-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_xp; ?>" onChange="setXP(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
											<td>
												<div class="br-num">
													<span class="icon icon-bloo"></span>
													<input type="number" class="the-bloo" id="the_bloo-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_bloo; ?>" onChange="setBLOO(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
										<?php }else{ ?>
											<td colspan="4">
												<span class="br-badge br-badge-amber"><?= __("These options aren't used in ranks","bluerabbit"); ?></span>
											</td>
										<?php } ?>
										<td>
											<div class="br-actions">
												<button class="br-action-link duplicate" onClick="showOverlay('#confirm-duplicate-<?php echo $a->achievement_id; ?>');">
													<span class="icon icon-duplicate"></span>
													<span class="tool-tip bottom">
														<span class="tool-tip-text font _12"><?php _e("Duplicate","bluerabbit"); ?></span>
													</span>
												</button>
												<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?php echo $a->achievement_id; ?>">
													<button class="br-btn amber duplicate-confirm-button" onClick="duplicateRow(<?php echo $a->achievement_id; ?>);">
														<span class="icon icon-duplicate"></span> <?php _e("Duplicate?","bluerabbit"); ?>
													</button>
													<button class="br-btn ghost close-confirm" onClick="hideAllOverlay();">
														<span class="icon icon-cancel"></span>
													</button>
												</div>
											</div>
										</td>
										<td>
											<a href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id&achievement_id=$a->achievement_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span></a>
											<?php if($a->achievement_parent){ ?>
											<button class="br-btn br-btn-amber" type="button" onClick="breakParent(<?= $a->achievement_id; ?>, 'achievement');"><?= __("Break Parent","bluerabbit"); ?></button>
											<?php } ?>
										</td>
										<td>
											<div class="br-actions">
												<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?php echo $a->achievement_id; ?>');">
													<span class="icon icon-trash"></span>
													<span class="tool-tip bottom">
														<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
													</span>
												</button>
												<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $a->achievement_id; ?>">
													<button class="br-btn red trash-confirm-button" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','trash');">
														<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
													</button>
													<button class="br-btn ghost close-confirm" onClick="hideAllOverlay();">
														<span class="icon icon-cancel"></span>
													</button>
												</div>
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
						</table></div><!-- /.br-section-body -->
					</div>

					<!-- ════════════ SUMMARY BAR ════════════ -->
					<div class="br-summary-bar">
						<div class="br-summary-stat">
							<span class="icon icon-achievement"></span>
							<div>
								<span class="br-stat-val"><?php echo count($achievements['publish']); ?></span>
								<span class="br-stat-label"><?php echo __("Total Achievements","bluerabbit"); ?></span>
							</div>
						</div>
						<div class="br-summary-stat">
							<span class="icon icon-star"></span>
							<div>
								<span class="br-stat-val"><?php echo $curXp; ?></span>
								<span class="br-stat-label"><?php echo __("Total","bluerabbit")." $xp_label"; ?></span>
							</div>
						</div>
						<div class="br-summary-stat">
							<span class="icon icon-bloo"></span>
							<div>
								<span class="br-stat-val"><?php echo $curBloo; ?></span>
								<span class="br-stat-label"><?php echo __("Total","bluerabbit")." $bloo_label"; ?></span>
							</div>
						</div>
						<button class="br-btn cyan" onclick="reorderAchievements('#table-achievement')">
							<span class="icon icon-list"></span> <?php _e("Reorder Achievements","bluerabbit"); ?>
						</button>
					</div>

					<!-- Color XP/Bloo breakdown -->
					<div class="br-panel">
						<div class="br-actions" style="flex-wrap:wrap;gap:8px;">
							<?php foreach($colorXP as $key=>$color){ ?>
								<?php if($color > 0){ ?>
									<span class="br-badge br-badge-blue">
										<?php echo "$key — ".__("XP").": $color | ".__("Coins").": ".$colorBLOO[$key]; ?>
									</span>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				<?php }else{ ?>
					<div class="br-panel">
						<div class="br-empty">
							<span class="icon icon-cancel"></span>
							<h3><?php _e("No achievements found","bluerabbit"); ?></h3>
						</div>
					</div>
				<?php } ?>

				<!-- ════════════ TRASHED ACHIEVEMENTS ════════════ -->
				<?php if(isset($achievements['trash'])){ ?>
					<div class="br-section">
						<div class="br-section-header" onclick="this.classList.toggle('collapsed'); this.nextElementSibling.classList.toggle('collapsed');">
							<h3><span class="icon icon-trash"></span> <?php _e('Trashed Achievements','bluerabbit'); ?> <span class="br-badge br-badge-red"><?= count($achievements['trash']); ?></span></h3>
							<span class="br-toggle-icon icon icon-chevron-down"></span>
						</div>
						<div class="br-section-body">
							<table class="br-table table-achievements" id="table-trash-achievement">
								<thead>
									<tr>
										<th><?php _e("Name","bluerabbit"); ?></th>
										<th><?php _e("Magic Code","bluerabbit"); ?></th>
										<th><span class="icon icon-star"></span></th>
										<th><span class="icon icon-bloo"></span></th>
										<th><span class="icon icon-edit"></span></th>
										<th><?php _e("Actions","bluerabbit"); ?></th>
									</tr>
								</thead>
								<tbody class="sortable">
									<?php foreach($achievements['trash'] as $key=>$a){ ?>
										<tr id="achievement-<?php echo $a->achievement_id;?>">
											<td>
												<strong><?php echo $a->achievement_name; ?></strong>
											</td>
											<td><?php echo $a->achievement_code; ?></td>
											<td>
												<div class="br-num">
													<span class="icon icon-star"></span>
													<input type="number" id="the_xp-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_xp; ?>" onChange="setXP(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
											<td>
												<div class="br-num">
													<span class="icon icon-bloo"></span>
													<input type="number" id="the_bloo-achievement-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_bloo; ?>" onChange="setBLOO(<?php echo $a->achievement_id; ?>,'achievement');">
												</div>
											</td>
											<td>
												<a href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id&achievement_id=$a->achievement_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span></a>
											</td>
											<td>
												<div class="br-actions">
													<button class="br-action-link expand" onClick="showOverlay('#confirm-option-restore-<?php echo $a->achievement_id; ?>');">
														<span class="icon icon-restore"></span>
														<span class="tool-tip bottom">
															<span class="tool-tip-text font _12"><?php _e("Restore","bluerabbit"); ?></span>
														</span>
													</button>
													<div class="confirm-action overlay-layer" id="confirm-option-restore-<?php echo $a->achievement_id; ?>">
														<button class="br-btn cyan" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','publish');">
															<span class="icon icon-restore"></span> <?php _e("Restore?","bluerabbit"); ?>
														</button>
														<button class="br-btn ghost close-confirm" onClick="hideAllOverlay();">
															<span class="icon icon-cancel"></span>
														</button>
													</div>
													<button class="br-action-link trash" onClick="showOverlay('#confirm-option-delete-<?php echo $a->achievement_id; ?>');">
														<span class="icon icon-cancel"></span>
														<span class="tool-tip bottom">
															<span class="tool-tip-text font _12"><?php _e("Delete Forever","bluerabbit"); ?></span>
														</span>
													</button>
													<div class="confirm-action overlay-layer" id="confirm-option-delete-<?php echo $a->achievement_id; ?>">
														<button class="br-btn red" onClick="confirmStatus(<?php echo $a->achievement_id; ?>,'achievement','delete');">
															<span class="icon icon-cancel"></span> <?php _e("Delete Forever?","bluerabbit"); ?>
														</button>
														<button class="br-btn ghost close-confirm" onClick="hideAllOverlay();">
															<span class="icon icon-cancel"></span>
														</button>
													</div>
												</div>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table></div><!-- /.br-section-body -->
						</div>
					</div>
				<?php }else{ ?>
					<div class="br-panel">
						<div class="br-empty">
							<span class="icon icon-trash"></span>
							<h3><?php _e("No achievements found in trash","bluerabbit"); ?></h3>
						</div>
					</div>
				<?php } ?>
			<input type="hidden" id="row_type" value="achievement"/>
<?php $achievements_table = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id AND achievement_status='publish' ORDER BY  achievement_id, achievement_name");; ?>

<!-- ════════════ MAGIC LINKS TABLE ════════════ -->
<div class="br-section">
	<div class="br-section-header collapsed" onclick="this.classList.toggle('collapsed'); this.nextElementSibling.classList.toggle('collapsed');">
		<h3><span class="icon icon-link"></span> <?php _e('Magic Links','bluerabbit'); ?></h3>
		<span class="br-toggle-icon icon icon-chevron-down"></span>
	</div>
	<div class="br-section-body collapsed">
		<div class="br-section-body"><table class="br-table">
			<thead>
				<tr>
					<th><?php _e('Link','bluerabbit'); ?></th>
					<th><?php _e('Achievement','bluerabbit'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($achievements_table as $t_ach){ ?>
				<?php $magicLink = get_bloginfo('url')."/magic-link/?c=$t_ach->achievement_code&adv=$t_ach->adventure_id"; ?>
				<tr>
					<td><a href="<?= $magicLink;?>" target="_blank"><?= $magicLink;?></a></td>
					<td><?=$t_ach->achievement_name;?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table></div><!-- /.br-section-body -->
	</div>
</div>

</div><!-- /.br-journey-manager -->
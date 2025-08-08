<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if(isset($isGM) || isset($isNPC) || isset($isAdmin)){ ?>
			<?php 
			if(isset($_GET['order'])){
				if($_GET['order'] == 'xp'){
					$order=" ORDER BY a.player_xp DESC";
				}elseif($_GET['order'] == 'bloo'){
					$order=" ORDER BY a.player_bloo DESC";
				}elseif($_GET['order'] == 'level'){
					$order=" ORDER BY a.player_level DESC";
				}elseif($_GET['order'] == 'name'){
					$order=" ORDER BY b.player_last ASC";
				}elseif($_GET['order'] == 'gpa'){
					$order=" ORDER BY a.player_gpa DESC";
				}elseif($_GET['order'] == 'login'){
					$order=" ORDER BY a.player_last_login DESC";
				}
			}else{
				$order=" ORDER BY a.player_id ASC";
			}
			if(isset($_GET['roles']) && ($_GET['roles'] == 'all')){
				$player_roles = '';
			}else{
				$player_roles = "AND a.player_adventure_role='player'";
			}
			$players = $wpdb->get_results("
				SELECT 
				a.*, 
				b.player_first, b.player_last, b.player_nickname,
				b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad
				FROM {$wpdb->prefix}br_player_adventure a
				LEFT JOIN {$wpdb->prefix}br_players b
				ON a.player_id=b.player_id
				WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' $player_roles
				GROUP BY a.player_id $order
			");
	
			$player_posts = $wpdb->get_results("
			SELECT 
			a.player_id, a.quest_id, a.adventure_id, a.pp_grade
			FROM {$wpdb->prefix}br_player_posts a
			LEFT JOIN {$wpdb->prefix}br_players b
			ON a.player_id=b.player_id
			WHERE a.adventure_id=$adventure->adventure_id
			
			UNION
			
			SELECT 
			a.player_id, a.quest_id, a.adventure_id, a.attempt_grade
			FROM {$wpdb->prefix}br_challenge_attempts a
			LEFT JOIN {$wpdb->prefix}br_players b
			ON a.player_id=b.player_id
			WHERE a.adventure_id=$adventure->adventure_id AND a.attempt_status='success' "); 
											   
			$player_achievements = $wpdb->get_results("											   
			
			SELECT 
			a.player_id, a.achievement_id, a.adventure_id
			FROM {$wpdb->prefix}br_player_achievement a
			WHERE a.adventure_id=$adventure->adventure_id
			"); 
					 
			$player_post_by_id = array();
			$player_achievements_by_id = array();
			foreach($player_posts as $pp){
				$player_post_by_id[$pp->quest_id][$pp->player_id]['p_id']= $pp->player_id;
				$player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->pp_grade;
				if($pp->attempt_grade){
					if($pp->attempt_grade > $player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->pp_grade){
						$player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->attempt_grade;
					}
				}
			}
			foreach($player_achievements as $pp){
				$player_achievements_by_id[$pp->achievement_id][$pp->player_id] = $pp->player_id;
			}
			$colors=array(
				'quest'=>'blue',
				'challenge'=>'brown',
				'mission'=>'amber',
				'survey'=>'teal',
			);
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests
			WHERE adventure_id=$adv_parent_id AND quest_type IN ('quest','challenge','mission','survey') AND quest_status='publish' ORDER BY quest_order
			");
	
			$achievements = getAchievements($adv_parent_id);
			$achievements = isset($achievements['publish']) ? $achievements['publish'] : NULL;

	
			$total_quests = isset($quests) ? count($quests) : 0;
			$total_achievements = isset($achievements) ? count($achievements) : 0;
	
			$totalWidth = (900+(($total_quests+$total_achievements)*170))."px"; 
	
	
			$absoluteValues=array(
				'max_possible'=>0,
				'finished'=>0
			); 
			?>




		<div class="sticky top left white-bg w-full max-w-900 layer overlay">
			<div class="input-group search-players w-full ">
				<label class="orange-bg-400">
					<span class="icon icon-search"></span>
				</label>
				<input type="text" class="form-ui w-full" id="search-players" placeholder="<?php _e("Search players","bluerabbit"); ?>">
				<label class="orange-bg-400">
					<?php if($_GET['roles']=='all'){ ?>
						<a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id";?>" class="form-ui pull-right font-18 blue-grey-bg-800 white-color"><?= __("Hide GMs and NPCs","bluerabbit"); ?></a>
					<?php }else{ ?>
						<a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&roles=all";?>" class="form-ui pull-right font-18 blue-grey-bg-800 white-color"><?= __("Show GMs and NPCs","bluerabbit"); ?></a>
					<?php } ?>
				</label>	
				<label class="pink-bg-400">
					<button class="form-ui pink-bg-800 white-color" onClick="exportPlayersWork();">
						<?php _e("Export Players","bluerabbit"); ?>
					</button>
				</label>	
				<script>
					$('#search-players').keyup(function(){
						var valThis = $(this).val().toLowerCase();
						if(valThis == ""){
							$('div.table-body > div.table-row').show();           
						}else{
							$('div.table-body > div.table-row').each(function(){
								var text = $(this).text().toLowerCase();
								(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
							});
						};
					});
				</script>
			</div>
		</div>
		<div class="container layer base relative" style="width: <?= $totalWidth; ?>">
			<div class="players-table">
				<div class="table-header layer foreground" id="players-table-header">
					
					<div class="header-row table-row" style="width: <?= $totalWidth; ?>">
						<div class="cell player-id cursor-pointer layer foreground w-50 text-center" onClick="toggleAllGrades();">
							<?php _e("Pos","bluerabbit"); ?>
							<input type="hidden" class="cell-text-value" value="<?= __("","bluerabbit"); ?>">
						</div>
						<?php if(isset($isAdmin)){ ?>
							<div class="cell player-refresh cursor-pointer w-80">
								<button class="icon-button blue-bg-800" onClick="updatePlayer(<?="$adventure->adventure_id, $p->player_id"; ?>);">
									<span class="icon icon-rotate"></span>
								</a>
							</div>
						<?php } ?>
						<div class="cell player-name cursor-pointer layer foreground w-250">
							<a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&order=name"; ?>"><?php _e("Name","bluerabbit"); ?></a>
							<input type="hidden" class="cell-text-value" value="<?= __("Name","bluerabbit"); ?>">

						</div>
						<div class="cell player-hexad"><span class="icon icon-hexad"></span>
							<input type="hidden" class="cell-text-value" value="<?= __("Player Type","bluerabbit"); ?>">
						</div>
						<div class="cell player-level"><a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&order=level"; ?>">
						<span class="icon icon-level"></span></a>
							<input type="hidden" class="cell-text-value" value="<?= __("Level","bluerabbit"); ?>">
						</div>
						<div class="cell player-xp"><a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&order=xp"; ?>">
							<span class="icon icon-star"></span></a>
							<input type="hidden" class="cell-text-value" value="<?= $xp_label; ?>">
						</div>
						<div class="cell player-bloo">
							<a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&order=bloo"; ?>"><span class="icon icon-bloo"></span></a>
							<input type="hidden" class="cell-text-value" value="<?= $bloo_label; ?>">
						</div>
						<div class="cell player-last-login w-150"><a href="<?= get_bloginfo('url')."/players/?adventure_id=$adventure->adventure_id&order=login"; ?>">
							<span class="icon icon-time"></span></a>
							<input type="hidden" class="cell-text-value" value="<?= __("Last login","bluerabbit"); ?>">
						</div>
						<?php if($adventure->adventure_grade_scale == 'percentage'||$adventure->adventure_grade_scale == 'letters'){  ?>
							<div class="cell player-gpa"><?php _e("GPA","bluerabbit"); ?></div>
						<?php } ?>
						<?php foreach ($quests as $k=>$q) { ?>
							<div class="cell quest-title <?= $q->quest_type; ?> player-post cursor-pointer column-<?= $q->quest_id; ?>" id="<?= $q->quest_type; ?>-<?= $q->quest_id; ?>"  onClick="toggleColGrades('<?= $q->quest_id; ?>')">
								<input type="hidden" class="cell-text-value" value="<?= $q->quest_title; ?>">
								<span class="icon-button sq-40" style="background-image: url(<?= $q->mech_badge ; ?>);"></span>
							</div>
						<?php } ?>
						<?php foreach ($achievements as $akey=>$a) { ?>
							<div class="cell quest-title achievement player-post cursor-pointer column-a-<?= $a->achievement_id; ?>" id="achievement-<?= $a->achievement_id; ?>"  onClick="toggleColGrades('a-<?= $a->achievement_id; ?>')">
								<?= $a->achievement_name; ?><br>
								<span class="icon icon-star"></span>  <?= $a->achievement_xp; ?> | <span class="icon icon-bloo"></span> <?= $a->achievement_bloo; ?>
								<input type="hidden" class="cell-text-value" value="<?= $a->achievement_name; ?>">
							</div>
						<?php } ?>
					</div>
				</div>
				
				<div class="table-body">
				<?php foreach ($players as $k=>$p) { ?>
					<div class="player-row table-row layer base relative" id="player-<?= $p->player_id; ?>" style="width: <?= $totalWidth; ?>">
						<div class="cell layer foreground player-id cursor-pointer w-50 text-center row-<?= $p->player_id; ?>" onClick="toggleRowGrades('<?= $p->player_id; ?>')">
							<?= ($k+1); ?>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_id; ?>">
						</div>
						<?php if($isAdmin){ ?>
							<div class="cell player-refresh cursor-pointer w-80 text-center row-<?= $p->player_id; ?>">
								<button class="icon-button blue-bg-800" onClick="updatePlayer(<?="$adventure->adventure_id, $p->player_id"; ?>);">
									<span class="icon icon-rotate"></span>
								</a>
							</div>
						<?php } ?>
						<div class="cell layer foreground player-name cursor-pointer w-250 row-<?= $p->player_id; ?>" onClick="toggleRowGrades('<?= $p->player_id; ?>')">
							<div class="name-container relative block w-full h-full overflow-hidden">
								<a target="_blank" href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adventure->adventure_id&player_id=$p->player_id"; ?>" class="block absolute w-full h-full top left">
									<?php
									if($p->player_adventure_role == 'gm'){ 
										$color = "teal-400";
									}elseif($p->player_adventure_role == 'npc'){ 
										$color = "light-blue-800";
									}else{
										$color = "";
									}
									?>
									<h3 class="font _16 w600 <?= $color; ?>">
										<?= $p->player_nickname; ?>
									</h3>
									<span class="font w300 grey-500 _12">
										<?= $p->player_first ? $p->player_first." ".$p->player_last : $p->player_email ; ?>
									</span>
								</a>
							</div>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_first ? $p->player_first." ".$p->player_last : $p->player_email ; ?>">
						</div>
						<div class="cell player-hexad row-<?= $p->player_id; ?>">
								<?php if($config['use_hexad']['value']>0){ ?>
									<?php if($p->player_adventure_role == 'gm'){ ?>
									<span class="icon-button font _24 sq-40  border border-all border-3 teal-border-400" style="background-image: url(<?= $p->player_picture; ?>);"></span>
								<?php }elseif($p->player_adventure_role == 'npc'){ ?>
									<span class="icon-button font _24 sq-40  border border-all border-3 light-blue-border-800" style="background-image: url(<?= $p->player_picture; ?>);"></span>	
								<?php }else{ ?>
									<span class="icon-button font _24 sq-40 " style="background-image: url(<?= $p->player_picture; ?>);"></span>
								<?php } ?>
							<?php }else{ ?>
								<button class="icon-button font _24 sq-40  type-<?= $p->player_hexad_slug; ?>">
									<span class="icon icon-<?= $p->player_hexad_slug; ?>"></span>
									<span class="tool-tip">

										<span class="tool-tip-text"><?= $p->player_hexad; ?></span>
									</span>
								</button>
							<?php } ?>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_hexad; ?>">
						</div>
						<div class="cell player-level row-<?= $p->player_id; ?>">
							<?= $p->player_level; ?>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_level; ?>">
						</div>
						<div class="cell player-xp row-<?= $p->player_id; ?>">
							<?= $p->player_xp; ?>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_xp; ?>">
						</div>
						<div class="cell player-bloo row-<?= $p->player_id; ?>">
							<?= $p->player_bloo; ?>
							<input type="hidden" class="cell-text-value" value="<?= $p->player_bloo; ?>">
						
						</div>
						<div class="cell player-last-login w-150 row-<?= $p->player_id; ?> font _12">
							<?php 
								if($p->player_last_login){
									$pretty_login_date = date('d / m / Y', strtotime($p->player_last_login));
									$pretty_login_time = date('H:i', strtotime($p->player_last_login));
									echo "$pretty_login_date <br> $pretty_login_time";
									?>
									<input type="hidden" class="cell-text-value" value="<?= "$pretty_login_date - $pretty_login_time"; ?>">

									<?php
								}else{
									echo __("never",'bluerabbit');
									?>
									<input type="hidden" class="cell-text-value" value="<?= __("Never","bluerabbit"); ?>">

									<?php
								}
							?>
						</div>
						<?php if($adventure->adventure_grade_scale != 'none'){  ?>
							<div class="cell player-gpa row-<?= $p->player_id; ?>">
								<input type="hidden" class="cell-text-value" value="<?= $p->player_gpa; ?>">
								<?= $p->player_gpa ? $p->player_gpa : 0; ?>
							</div>
						<?php } ?>
						<?php foreach ($quests as $qkey=>$q) { ?>
							<?php $absoluteValues['max_possible']+=1; ?>
							<?php if($player_post_by_id[$q->quest_id][$p->player_id]['p_id']==$p->player_id){ ?>
								<div class="cell quest-title player-post <?= $q->quest_type; ?> column-<?= $q->quest_id; ?> row-<?= $p->player_id; ?>" id="<?= $q->quest_type."-".$q->quest_id."-".$p->player_id; ?>">
									<div class="layer absolute sq-full background light-green-bg-400 opacity-30"></div>
									<?php 
									if(($adventure->adventure_grade_scale == "percentage" || $adventure->adventure_grade_scale == "letters") && ($q->quest_type =='quest' || $q->quest_type =='challenge')){
										$the_grade = $player_post_by_id[$q->quest_id][$p->player_id]['grade'];
										if($the_grade >= 80){
											$grade_color = 'green';
										}elseif($the_grade < 80 && $the_grade >= 60){
											$grade_color = 'amber';
										}elseif($the_grade > 0 && $the_grade < 60){
											$grade_color = 'red';
										}else{
											$grade_color = 'grey';
										}
									}elseif($q->quest_type=='mission'){
										$grade_color = 'blue';
									}else{
										$grade_color = 'blue-grey';
									}
									?>
									<div class="relative layer base border rounded-max overflow-hidden <?=$grade_color;?>-bg-400">
										<?php if($q->quest_type=="quest"){ ?>
											<a class="icon-button relative layer base font _16 sq-30 transparent-bg icon-sm" href="<?= get_bloginfo('url')."/post/?adventure_id=$adventure->adventure_id&questID=$q->quest_id&uID=$p->player_id"; ?>">
												<span class="icon icon-check white-color perfect-center"></span>
												<?php $absoluteValues['finished']+=1; ?>
												<?php $absoluteValues['finished_q'][$q->quest_id]['title']=$q->quest_title; ?>
												<?php $absoluteValues['finished_q'][$q->quest_id]['value']+=1; ?>
											</a>
											<?php if($adventure->adventure_grade_scale == "percentage"){ ?>
												<input class="form-ui relative layer base text-center" onChange="setGrade(<?= "$q->quest_id,$p->player_id"; ?>);" type="number" min="0" max="100" class="form-ui" id="the_post_grade_<?= $q->quest_id."_".$p->player_id; ?>" value="<?= $the_grade; ?>">
												<input type="hidden" class="cell-text-value" value="<?= $the_grade; ?>">
											<?php }elseif($adventure->adventure_grade_scale == "letters"){   ?>
												<select <?= $disabled; ?> class="form-ui relative layer base w-full text-center" id="the_post_grade_<?= $q->quest_id."_".$p->player_id; ?>" onChange="setGrade(<?= "$q->quest_id,$p->player_id"; ?>);">
													<option value="100" <?php if($the_grade == 100){ echo 'selected'; } ?>>A</option>
													<option value="91.75" <?php if($the_grade < 100 && $the_grade >= 91.75){ echo 'selected';  }?>>A-</option>
													<option value="83.25" <?php if($the_grade < 91.75 && $the_grade >= 83.25){ echo 'selected';  }?>>B+</option>
													<option value="75" <?php if($the_grade < 83.25 && $the_grade >= 75){ echo 'selected'; } ?>>B</option>
													<option value="66.75" <?php if($the_grade < 75 && $the_grade >= 66.75){ echo 'selected'; } ?>>B-</option>
													<option value="58.25" <?php if($the_grade < 66.75 && $the_grade >= 58.25){ echo 'selected'; } ?>>C+</option>
													<option value="50" <?php if($the_grade < 58.25 && $the_grade >= 50){ echo 'selected'; } ?>>C</option>
													<option value="25" <?php if($the_grade < 50 && $the_grade >= 25){ echo 'selected'; } ?>>D</option>
													<option value="0" <?php if($the_grade < 25){ echo 'selected'; } ?>>F</option>
													<option value="NULL" <?php if($the_grade===NULL){ echo 'selected'; } ?>><?php _e("No grade","bluerabbit"); ?></option>
												</select>
												<input type="hidden" class="cell-text-value" value="<?= $the_grade; ?>">
											<?php }else{  ?>
												<input type="hidden" class="cell-text-value" value="<?= __("YES","bluerabbit"); ?>">
											<?php }  ?>
										<?php }elseif($q->quest_type =='challenge'){  ?>
											<span class="icon icon-check white-color"></span>
											<span class="icon icon-challenge white-color"></span>
											<input class="form-ui relative layer base w-full text-center" readonly class="form-ui text-center" value="<?= $the_grade; ?>">
											<input type="hidden" class="cell-text-value" value="<?= $the_grade; ?>">
										<?php }elseif($q->quest_type =='mission'){  ?>
											<span class="icon icon-check white-color"></span>
											<span class="icon icon-mission white-color"></span>
											<input type="hidden" class="cell-text-value" value="<?= __("YES","bluerabbit"); ?>">
										<?php } ?>
									</div>
								</div>
							<?php }else{ ?>
								<div class="cell quest-title player-post column-<?= $q->quest_id; ?> row-<?= $p->player_id; ?>" id="<?= $q->quest_type."-".$q->quest_id."-".$p->player_id; ?>">
									<input type="hidden" class="cell-text-value" value="<?= __("NO","bluerabbit"); ?>">
									<span class="background <?= $colors[$q->quest_type]."-bg-200"; ?> opacity-40"></span>
									<span class="foreground icon icon-<?= $q->quest_type; ?> "></span>
								</div>
							<?php } ?>
						<?php } ?>
						<?php foreach ($achievements as $akey=>$a) { ?>
							<?php $absoluteValues['max_possible']+=1; ?>
							<?php if($player_achievements_by_id[$a->achievement_id][$p->player_id]==$p->player_id){ ?>
								<div class="cell quest-title player-post column-a-<?= $a->achievement_id; ?> row-<?= $p->player_id; ?>" id="<?= $q->quest_type."-".$q->quest_id."-".$p->player_id; ?>">
									<input type="hidden" class="cell-text-value" value="<?= __("EARNED","bluerabbit"); ?>">
									<span class="background light-green-bg-400 opacity-30"></span>
									<span class="foreground icon-button font _24 sq-40  white-bg icon-sm">
										<span class="icon icon-check light-green-400"></span>
										<?php $absoluteValues['finished']+=1; ?>
										<?php $absoluteValues['finished_a'][$a->achievement_id]['title']=$a->achievement_name; ?>
										<?php $absoluteValues['finished_a'][$a->achievement_id]['value']+=1; ?>
									</span>
								</div>
							<?php }else{ ?>
								<div class="cell quest-title player-post column-a-<?= $a->achievement_id; ?> row-<?= $p->player_id; ?>" id="<?= "achievement-".$a->achievement_id."-".$p->player_id; ?>">
									<input type="hidden" class="cell-text-value" value="<?= __("NOT","bluerabbit"); ?>">
									<span class="background purple-bg-200 opacity-40"></span>
									<span class="foreground grey-500 icon icon-achievement"></span>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>"/>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404/"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

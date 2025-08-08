<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$questID =  $_GET['questID'];
	if( $_GET['uID'] && $isGM){
		$player_id =  $_GET['uID'];
	}else{
		$player_id =  $current_user->ID;
	}
	$q = $wpdb->get_row("SELECT 
	a.*,
	d.pp_content, d.pp_grade, d.player_id, d.pp_date, d.pp_modified, d.pp_quest_rating
	
	FROM {$wpdb->prefix}br_quests a
	LEFT JOIN {$wpdb->prefix}br_player_posts d
	ON a.quest_id = d.quest_id AND d.player_id=$player_id

	WHERE a.adventure_id=$adventure->adventure_id AND a.quest_status='publish' AND a.quest_id=$questID");
	
	$requirements = $wpdb->get_results("SELECT 
	
	a.req_object_id, a.req_type, a.req_object_id,
	b.mech_level, b.mech_xp, b.mech_bloo,
	c.quest_title, c.quest_type, c.mech_badge,
	d.item_name
	
	FROM {$wpdb->prefix}br_reqs a
	LEFT JOIN {$wpdb->prefix}br_quests b
	ON a.quest_id = b.quest_id AND b.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_quests c
	ON a.req_object_id = c.quest_id AND c.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_items d
	ON a.req_object_id = d.item_id
	
	WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$questID
	
	");
	if($requirements){
		$reqs = array(); $reqs_ids = array();
		foreach($requirements as $r){
			$reqs[]=$r;
			$reqs_ids[$r->req_type][]=$r->req_object_id;
		}
		sort($reqs_ids['quest']);		
		$reqs_list = implode(",",$reqs_ids['quest']);
		$player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
		WHERE adventure_id=$adventure->adventure_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
		$player_challenges = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_challenge_attempts
		WHERE adventure_id=$adventure->adventure_id AND attempt_status='success' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
		
		$player_work = array_merge($player_quests,$player_challenges);sort($player_work);
	}
	if($q->mech_item_reward){ 
		$item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id={$q->mech_item_reward} AND item_type='reward' AND item_status='publish'");
	}
	if($q->mech_achievement_reward){ 
		$achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id={$q->mech_achievement_reward} AND achievement_status='publish'");
	}

	if($q->quest_type == 'challenge'){
		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_attempts WHERE quest_id=$questID AND player_id=$player_id AND (attempt_status='success' OR attempt_status='fail')");
		$grades=array();
		foreach($attempts as $att){
			$grades[]=$att->attempt_grade;
		}
		$best_grade = max($grades);
	}
?>
<?php if($q){ ?>

<div class="tabs boxed max-w-900">
	<div class="tab-header text-center" id="post-tabs-buttons">
		<h1 class="font _40 w400 margin-20 white-color"><?= $q->quest_title; ?></h1>
		<button id="summary-tab-button" class="tab-button transparent-bg button form-ui relative white-color active" onClick="switchTabs('#post-tabs','#summary')">
			<span class="layer base relative"><?= __("Summary","bluerabbit"); ?></span>
			<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
			<span class="background absolute sq-full layer active-content blue-bg-400"></span>
		</button>
		<?php if($q->quest_type == 'quest'){ ?>
			<button id="quest-answer-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#quest-answer')">
				<span class="layer base relative"><?= __("Answer","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content blue-bg-400"></span>
			</button>
		<?php } ?>
		<?php if($q->quest_content){ ?>
			<button id="instructions-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#instructions')">
				<span class="layer base relative"><?= __("Instructions","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content blue-bg-400"></span>
			</button>
		<?php } ?>
		<?php if($q->quest_success_message){ ?>
			<button id="success-message-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#success-message')">
				<span class="layer base relative"><?= __("Message","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content blue-bg-400"></span>
			</button>
		<?php } ?>
	</div>
	
	<div class="tab-group tabs white-color" id="post-tabs">
		<div class="tab active text-center" id="summary">
			<div class="quest-image relative w-360 boxed">
				<img src="<?= $q->mech_badge; ?>" class="w-full max-w-300">
				<div class="absolute layer base w-full padding-10 bottom left text-center">
					
					<span class="relative layer base icon icon-check font _40 lime-500"></span>
					<div class="layer background grey-gradient-900"></div>
				</div>
			</div>
			<div class="text-center padding-10">
				<span class="icon-group inline-table">
					<span class="icon-button font _24 sq-40  amber-bg-400 font _28">
						<span class="icon icon-star white-color "></span>
					</span>
					<span class="icon-content">
						<span class="line font _24 w600 amber-800"><?= toMoney($q->mech_xp,""); ?></span>
						<span class="line font _14 grey-500"><?= $xp_label; ?></span>
					</span>
					<span class="icon-button font _24 sq-40  green-bg-400 font _28">
						<span class="icon icon-bloo white-color "></span>
					</span>
					<span class="icon-content">
						<span class="line font _24 w600 green-800"><?= toMoney($q->mech_bloo,""); ?></span>
						<span class="line font _14 grey-500"><?= $bloo_label; ?></span>
					</span>
				</span>
			</div>
			<?php if($achievement_reward){ ?>
				<div class="text-center relative padding-10 margin-10">
					<div class="background layer absolute sq-full purple-gradient-400 opacity-50"></div>
					<img src="<?= $achievement_reward->achievement_badge;?>" class="w-150 margin-5 grey-bg-50 overflow-hidden border rounded-max layer relative base cursor-pointer" onClick="loadAchievementCard(<?= $achievement_reward->achievement_id; ?>);">
					<div class="icon-group inline-table layer relative base">
						<button class="icon-button font _24 sq-40  purple-bg-400 font _28" onClick="loadAchievementCard(<?= $achievement_reward->achievement_id; ?>);">
							<span class="icon icon-achievement white-color"></span>
						</button>
						<span class="icon-content">
							<span class="line white-color font w100 _12 opacity-80"><?php _e("You earned an achievement","bluerabbit");?></span>
							<span class="line white-color font w900 _18"><?= $achievement_reward->achievement_name;?></span>
						</span>
					</div>
				</div>
			<?php } ?>
			<?php if($item_reward){ ?>
				<div class="text-center relative padding-10 margin-10">
					<div class="background layer absolute sq-full teal-gradient-400 opacity-50"></div>
					<img src="<?= $item_reward->item_badge;?>" class="w-150 margin-5 grey-bg-50 overflow-hidden border rounded-max layer relative base">
					<div class="icon-group inline-table layer relative base">
						<a class="icon-button font _24 sq-40 teal-bg-400 font _28" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id";?>">
							<span class="icon icon-backpack white-color"></span>
						</a>
						<span class="icon-content">
							<span class="line white-color font w100 _12 opacity-80"><?php _e("You found an item","bluerabbit");?></span>
							<span class="line white-color font w900 _18"><?= $item_reward->item_name;?></span>
						</span>
					</div>
				</div>
			<?php } ?>
			
			<?php if($requirements){ ?>
				<?php 
					$my_items = getMyItems($adventure->adventure_id); 
					$myKeyItems = $my_items['ids']['key'];
				?>
				<?php if(!empty($reqs)){ ?>
					<?php 
						$my_quests = getMyQuests($adventure->adventure_id);
						$my_achievements = getMyAchievements($adventure->adventure_id); 
					?>
					<h4 class="font _24 white-color w900 uppercase padding-20 text-center"><?php _e("Requirements","bluerabbit"); ?>: </h4>
					<div class="highlight padding-10">
						<div class="card-deck">
							<?php foreach($reqs as $r){
								if($r->req_type=="quest"){  
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $player_work) ? true : false;
									include (TEMPLATEPATH . '/req-milestone.php');
								}elseif($r->req_type=="item") {
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $myKeyItems) ? true : false;
									include (TEMPLATEPATH . '/req-item.php');
								}elseif($r->req_type=="achievement") {
									$a = $r;
									$isEarned = in_array($r->req_object_id, $my_achievements) ? true : false;
									include (TEMPLATEPATH . '/req-achievement.php');
								} 
							} ?>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
			<?php if($config['rate_quests']['value']>0){ ?>
				<div class="text-center padding-10">

					<div class="icon-group inline-table">
						<span class="icon-content">
							<span class="line font _24 amber-800"><?php _e("Rating","bluerabbit"); ?></span>
							<span class="line font _14 grey-400"><?php _e("Rate this quest please!","bluerabbit"); ?></span>
						</span>
					</div>
					<div class="icon-group rating inline-table">
						<button id="rating-star-1" class="icon-button button font _24 sq-40  <?= $q->pp_quest_rating >= 1 ? 'amber-bg-400' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,1);">
							<span class="icon icon-star"></span>
						</button>
						<button id="rating-star-2" class="icon-button button font _24 sq-40  <?= $q->pp_quest_rating >= 2 ? 'amber-bg-400' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,2);">
							<span class="icon icon-star"></span>
						</button>
						<button id="rating-star-3" class="icon-button button font _24 sq-40  <?= $q->pp_quest_rating >= 3 ? 'amber-bg-400' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,3);">
							<span class="icon icon-star"></span>
						</button>
						<button id="rating-star-4" class="icon-button button font _24 sq-40  <?= $q->pp_quest_rating >= 4 ? 'amber-bg-400' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,4);">
							<span class="icon icon-star"></span>
						</button>
						<button id="rating-star-5" class="icon-button button font _24 sq-40  <?= $q->pp_quest_rating >= 5 ? 'amber-bg-400' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,5);">
							<span class="icon icon-star"></span>
						</button>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="tab" id="quest-answer">
			<?php if($q->quest_type == 'challenge'){  ?>
				<div class="highlight padding-10 brown-bg-50">
					<h3 class="font _18 w700 brown-400 text-center condensed uppercase"><?php _e("Challenge","bluerabbit");?></h3>
					<table width="100%" align="center">
						<tr class="font w900 _40 condensed brown-400">
							<td class="text-center"><?= $q->mech_answers_to_win; ?></td>
							<td class="text-center">
								<?php if($q->mech_max_attempts > 0){ ?>
									<?= $q->mech_max_attempts; ?>
								<?php }else{ ?>
									<span class="icon icon-infinite"></span>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php 
								if($q->mech_time_limit > 0){
									echo $q->mech_time_limit;
								}else{
									echo "<span class='icon icon-infinite'></span>";
								}
								?>
							</td>
						</tr>
						<tr class="font w100 _12 grey-600">
							<td class="text-center"><?php _e("Answers"); ?></td>
							<td class="text-center"><?php _e("Max Attempts"); ?></td>
							<td class="text-center"><?php _e("Time Limit"); ?></td>
						</tr>
					</table>
				</div>
				<div class="highlight padding-10 grey-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400">
							<span class="icon icon-challenge"></span>
						</span>
						<span class="icon-content font _18 w700 brown-400">
							<?php _e("Your attempts","bluerabbit");?>
						</span>
						<span class="icon-content text-right font _24 w300 grey-800">
							<?= __("Your best grade","bluerabbit").": <strong class='green-400 font w900'>".$best_grade."</strong>"; ?>
						</span>
					</span>
				</div>
				<div class="content">
					<?php if($attempts){ ?>
						<?php 
							if($q->mech_questions_to_display > 0){
								$totalqs = $q->mech_questions_to_display;
							}else{
								$qs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions a WHERE quest_id=$q->quest_id");	
								$totalqs = count($qs);
							}
						?>
						<?php $att_count=0; ?>
						<table class="table compact" width="100%">
							<thead>
								<tr>
									<td><?php _e("Attempt","bluerabbit"); ?></td>
									<td><?php _e("Answers","bluerabbit"); ?></td>
									<td><?php _e("Grade","bluerabbit"); ?></td>
									<td><?php _e("Date","bluerabbit"); ?></td>
									<td><?php _e("Status","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach($attempts as $key=>$att){ ?>
									<tr class="attempt">

										<td> <?= "#".($key+1); ?> </td>
										<td><?= "<strong>$att->attempt_answers/$totalqs</strong>";?></td>
										<td><?= "$att->attempt_grade";?></td>
										<td><?= date('D, M jS, Y',strtotime($att->attempt_date));?></td>
										<td>
											<?php if($att->attempt_status=='success'){ ?>
												<?php $att_success=true; ?>
												<span class="icon-button font _24 sq-40  icon-sm green-bg-400">
													<span class="icon icon-check"></span>
												</span>
											<?php }else{ ?>
												<span class="icon-button font _24 sq-40  icon-sm red-bg-400">
													<span class="icon icon-cancel"></span>
												</span>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
				</div>
			<?php }else{ ?>
					<h2 class="font _24"><?php _e("Your answer","bluerabbit");?></h2>
					<?php if($q->pp_date == $q->pp_modified){ ?>
						<h4 class="font _14 grey-500"><?= __("Published","bluerabbit").":<em> $q->pp_date </em>"; ?></h4>
					<?php }else{ ?>
						<h4 class="font _14 red-500"><?= __("Modified","bluerabbit").":<em> $q->pp_modified </em>"; ?></h4>
					<?php } ?>
					<?php if($isGM && $adventure->adventure_grade_scale != 'none'){ ?>
						<?php $the_grade = $q->pp_grade; ?>
						<div class="highlight-cell pull-right">
							<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>"/>
							<div class="input-group">
								<label class="red-bg-700"><?php _e("Grade","bluerabbit"); ?>: </label>
								<?php if($adventure->adventure_grade_scale == "percentage"){ ?>
									<input onChange="setGrade(<?= "$q->quest_id,$q->player_id"; ?>);" type="number" min="0" max="100" class="form-ui white-bg w-200" id="the_quest_grade" value="<?= $the_grade; ?>">
								<?php }elseif($adventure->adventure_grade_scale == "letters"){   ?>
									<select class="form-ui" id="the_quest_grade" onChange="setGrade(<?= "$q->quest_id,$q->player_id"; ?>);">
										<option value="100" <?php if($the_grade == 100){ echo 'selected'; } ?>>A</option>
										<option value="91.75" <?php if($the_grade < 100 && $the_grade >= 91.75){ echo 'selected';  }?>>A-</option>
										<option value="83.25" <?php if($the_grade < 91.75 && $the_grade >= 83.25){ echo 'selected';  }?>>B+</option>
										<option value="75" <?php if($the_grade < 83.25 && $the_grade >= 75){ echo 'selected'; } ?>>B</option>
										<option value="66.75" <?php if($the_grade < 75 && $the_grade >= 66.75){ echo 'selected'; } ?>>B-</option>
										<option value="58.25" <?php if($the_grade < 66.75 && $the_grade >= 58.25){ echo 'selected'; } ?>>C+</option>
										<option value="50" <?php if($the_grade < 58.25 && $the_grade >= 50){ echo 'selected'; } ?>>C</option>
										<option value="25" <?php if($the_grade < 50 && $the_grade >= 25){ echo 'selected'; } ?>>D</option>
										<option value="0" <?php if($the_grade < 25 && $the_grade >= 0 || !$the_grade){ echo 'selected'; } ?>>F</option>
									</select>
								<?php }  ?>
							</div>
						</div>
					<?php } ?>
					<div class="content">
						<?= apply_filters('the_content',$q->pp_content); ?>
					</div>
					<div class="highlight padding-10 text-right">
						<?php if($adventure->adventure_grade_scale == "none" || ($adventure->adventure_grade_scale != "none" && $adventure->adventure_progression_type=='before') || ($adventure->adventure_grade_scale != "none"  && !$q->pp_grade  && $adventure->adventure_progression_type=='after')) {	 ?>
							<a class="form-ui green-bg-400" href="<?= get_bloginfo('url').'/quest/?adventure_id='.$adventure_id."&questID=".$q->quest_id;?>">
								<span class="icon icon-edit"></span><?php _e("Edit your answer","bluerabbit"); ?>
							</a>
						<?php }else{ ?>
							<h4><?php _e("This post has already been graded. Can't edit or delete.","bluerabbit"); ?></h4>
						<?php } ?>
					</div>
			<?php } ?>
		</div>
		<?php if($q->quest_success_message){ ?>
			<div class="tab" id="success-message">
				<h2 class="font _24 grey-500"><?php _e("This information should help you on your journey!","bluerabbit");?></h2>
				<div class="content">
					<?= apply_filters('the_content',$q->quest_success_message); ?>
				</div>
			</div>
		<?php } ?>
		<?php if($q->quest_type == 'quest'){ ?>
			<div class="tab" id="instructions">
				<h2 class="font _24 grey-500 margin-20"><?php _e("Quest instructions","bluerabbit");?></h2>
				<div class="content">
					<?= apply_filters('the_content',$q->quest_content); ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>



<script>
	jQuery(document).ready(function($) {
		$('img.size-full').removeAttr('width height').addClass('max-w-400');
	});
</script>
<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
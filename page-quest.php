<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	if(!$isGM){
		$or_draft = " AND a.quest_status='publish'";
		$player_id_val = $current_user->ID;
	}else{
		$or_draft = "";
		$player_id_val = isset($_GET['playerID']) ? $_GET['playerID'] : $current_user->ID;
	}
	$questID =  isset($_GET['questID']) ? $_GET['questID'] : NULL;
	if($questID){
		$q = $wpdb->get_row("SELECT 
		a.quest_id, a.quest_relevance, a.quest_title, a.quest_content, a.quest_type,a.adventure_id, a.achievement_id, a.quest_success_message, a.quest_status,
		a.mech_level, a.mech_xp, a.mech_bloo, a.mech_ep, a.mech_badge, a.mech_deadline_cost, a.mech_unlock_cost, a.mech_item_reward, a.mech_deadline, a.mech_start_date, a.mech_min_words, a.quest_date_modified, a.quest_color, 
		d.pp_content, d.pp_grade, d.player_id, d.pp_quest_rating

		FROM {$wpdb->prefix}br_quests a
		LEFT JOIN {$wpdb->prefix}br_player_posts d
		ON a.quest_id = d.quest_id AND d.player_id=$player_id_val AND d.pp_status='publish' AND d.adventure_id=$adv_child_id
		WHERE a.adventure_id=$adv_parent_id $or_draft AND a.quest_id=$questID");

		if($q->achievement_id > 0){
			if(!in_array($q->achievement_id, $player_achievements)){
			?>
			<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
			<?php 
				die();
			}
		}

		$requirements = $wpdb->get_results("SELECT 

		a.req_object_id, a.req_type, 
		c.quest_type, c.mech_badge, c.quest_id, c.quest_title, 
		d.item_name, d.item_badge, d.item_id,
		e.achievement_name, e.achievement_badge, e.achievement_id, e.achievement_color,
		f.achievement_applied

		FROM {$wpdb->prefix}br_reqs a
		LEFT JOIN {$wpdb->prefix}br_quests c
		ON a.req_object_id = c.quest_id AND c.quest_status='publish' AND a.quest_id=$q->quest_id
		LEFT JOIN {$wpdb->prefix}br_items d
		ON a.req_object_id = d.item_id
		LEFT JOIN {$wpdb->prefix}br_achievements e
		ON a.req_object_id = e.achievement_id
		LEFT JOIN {$wpdb->prefix}br_player_achievement f
		ON e.achievement_id = f.achievement_id AND f.player_id=$current_player->player_id

		WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$q->quest_id

		");
		if($requirements){
			$reqs = array(); $reqs_ids = array();
			foreach($requirements as $r){
				$reqs[]=$r;
				$reqs_ids[$r->req_type][]=$r->req_object_id;
			}

			if (is_array($reqs_ids['quest'])) {
				sort($reqs_ids['quest']);
			}
			if (is_array($reqs_ids['item'])) {
				sort($reqs_ids['item']);
			}
			if (is_array($reqs_ids['achievement'])) {
				sort($reqs_ids['achievement']); 
			}
			$reqs_list = implode(",",$reqs_ids['quest']);
			$player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
			WHERE adventure_id=$adventure->adventure_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");

			$player_challenges = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_challenge_attempts
			WHERE adventure_id=$adventure->adventure_id AND attempt_status='success' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");

			$player_work = array_merge($player_quests,$player_challenges);sort($player_work);
		}
	}
	?>

	<?php if(isset($q)){ ?>
		<div class="container boxed max-w-1200 white-color wrap">
			<div class="layer base relative ">
					<div class="content">
					<?php if($adventure->adventure_grade_scale == "none" || ($adventure->adventure_grade_scale != "none" && $adventure->adventure_progression_type=='before') || ($adventure->adventure_grade_scale != "none"  && !$q->pp_grade  && $adventure->adventure_progression_type=='after')) {	 ?>

						<?php if(!empty($requirements)){ ?>
							<?php 
								$my_quests = getMyQuests($adventure->adventure_id);
								$myKeyItems = $my_items['key'];
							?>
							<div class="highlight padding-10 layer base relative">
								<div class="card-deck">
									<?php foreach($reqs as $r){
										if($r->req_type=="quest"){  
											$mi = $r;
											$isFinished = in_array($r->req_object_id, $player_work) ? true : false;
											if(!$isFinished){
												include (TEMPLATEPATH . '/req-milestone.php');
											}
										}elseif($r->req_type=="item") {
											$mi = $r;
											$isFinished = in_array($r->req_object_id, $myKeyItems) ? true : false;
											if(!$isFinished){
												include (TEMPLATEPATH . '/req-item.php');
											}
										}elseif($r->req_type=="achievement") {
											$a = $r;
											$isEarned = in_array($r->req_object_id, $my_achievements) ? true : false;
											if(!$isEarned){
												include (TEMPLATEPATH . '/req-achievement.php');
											}
										} 
									} ?>
								</div>
							</div>
						<?php } ?>
						<?php $today = date('YmdHi'); ?>
						<?php if($current_player->player_level < $q->mech_level){ ?>
							<div class="highlight text-center padding-10">
								<button class="form-ui deep-purple-bg-400 font _24">
									<span class="icon icon-lock"></span><strong><?= __("LEVEL","bluerabbit")." ".$q->mech_level." ". $q->quest_type; ?></strong>
									<?php _e("You must Level up first","bluerabbit"); ?>
								</button>
							</div>
						<?php }else{ ?>
							<?php if($q->mech_unlock_cost > 0 && !in_array($q->quest_id,$player['unlocks'])){ ?>
								<div class="highlight text-center padding-10">
									<button class="form-ui cyan-bg-400 font _24" onClick="showOverlay('#confirm-purchase-quest-<?= $q->quest_id; ?>');">
										<span class="icon icon-bloo"></span><?= __("Purchase this quest","bluerabbit"); ?>
									</button>
									<div class="overlay-layer confirm-action" id="confirm-purchase-quest-<?= $q->quest_id; ?>">
										<h2 class="font _24 w700"><?= __("Are you sure?","bluerabbit"); ?></h2>
										<button class="form-ui teal-bg-400" onClick="payment(<?=$q->quest_id;?>,'unlock');">
											<span class="icon icon-bloo"></span> <?= __("Pay","bluerabbit")." ".toMoney($q->mech_unlock_cost,""); ?>
										</button>
									</div>
								</div>
							<?php }else{ ?>
								<?php if($today < date('YmdHi',strtotime($q->mech_start_date)) && $q->mech_start_date != NULL  && $q->mech_start_date != '0000-00-00 00:00:00'){ ?>
									<div class="highlight text-center padding-10">
										<button class="form-ui cyan-bg-400 font _24"><span class="icon icon-lock"></span>
											<?= $q->quest_type." ".__("available on","bluerabbit").": ".date('D, M jS, Y',strtotime($q->mech_start_date)); ?>
										</button>
									</div>
								<?php } else { ?>
									<?php $niceDeadline = date('D, M jS, Y',strtotime($q->mech_deadline)); ?>
									<?php if($q->mech_deadline!='0000-00-00 00:00:00' && $q->mech_deadline != NULL && $today > date('YmdHi',strtotime($q->mech_deadline )) && $q->mech_deadline_cost <= 0){ ?>
										<div class="highlight text-center padding-10">
											<h3><?= __("You missed this deadline!","bluerabbit"); ?></h3>
											<h1><?= $q->quest_title; ?></h1>
										</div>
										<div class="highlight text-center padding-10">
											<button class="form-ui red-bg-400 font _24"><span class="icon icon-lock"></span>
												<?= $q->quest_type." ".__("overdue","bluerabbit").": ".$niceDeadline; ?>
											</button>
										</div>
									<?php }elseif($q->mech_deadline!='0000-00-00 00:00:00' && $q->mech_deadline != NULL && $today >  date('YmdHi',strtotime($q->mech_deadline)) && $q->mech_deadline_cost > 0 && !in_array($q->quest_id,$player['deadlines'])){ ?>

										<div class="highlight text-center padding-10">
											<h3><?= __("You missed this deadline!","bluerabbit"); ?></h3>
											<h1><?= $q->quest_title; ?></h1>
										</div>
										<div class="highlight text-center padding-10">
											<button class="form-ui red-bg-400 font _24" onClick="showOverlay('#confirm-deadline-quest-<?= $q->quest_id; ?>');">
												<?= __("Quest overdue","bluerabbit");?><br>
												<strong><?= $niceDeadline; ?></strong><br>
												<span class="icon icon-bloo"></span><?= __("Purchase this deadline","bluerabbit"); ?>
											</button>
											<div class="overlay-layer confirm-action" id="confirm-deadline-quest-<?= $q->quest_id; ?>">
												<button class="form-ui blue-bg-400" onClick="payment(<?=$q->quest_id;?>,'deadline');">
													<span class="icon icon-bloo"></span> <?= __("Confirm purchase","bluerabbit")." ".toMoney($q->mech_deadline_cost,""); ?>
												</button>
											</div>
										</div>
									<?php }else{ ?>
										<?php
											if(!empty($reqs_ids['quest'])){
												$allQuestsSet = array_intersect($player_work, $reqs_ids['quest']);
												$questsReady = ($allQuestsSet == $reqs_ids['quest']) ? true : false;
											}else{
												$questsReady= true;
											}

											if(!empty($reqs_ids['item'])){
												$allItemsSet = array_intersect($myKeyItems,$reqs_ids['item']);
												$itemsReady = ($allItemsSet == $reqs_ids['item']) ? true : false;
											}else{
												$itemsReady= true;
											}

											if(!empty($reqs_ids['achievement'])){
												$allAchievementsSet = array_intersect($my_achievements,$reqs_ids['achievement']);
												$allAchievementsSet = array_values($allAchievementsSet);
												$achievementsReady = ($allAchievementsSet == $reqs_ids['achievement']) ? true : false;
											}else{
												$achievementsReady= true;
											}
										?>
										<?php if($questsReady && $itemsReady && $achievementsReady ){	?>
											<?php if($player['debt']<=0){ ?>
												<?php include (TEMPLATEPATH . '/quest.php'); ?>
												<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_player_post_nonce'); ?>"/>
												<input type="hidden" id="the_quest_id" value="<?= $q->quest_id; ?>"/>
												<input type="hidden" id="the_pp_type" value="<?= $q->quest_type; ?>"/>
											<?php }else{ ?>
												<div class="highlight cyan-bg-50 text-center padding-10">
													<button href="<?= get_bloginfo('url')."/blockers/?adventure_id=$q->adventure_id"; ?>" class="form-ui cyan-bg-400">
														<?php _e("Clear my debt","bluerabbit"); ?><span class="icon icon-bloo"></span><?= $player['debt']; ?>
													</button>
												</div>
											<?php } ?>
										<?php }else{?>
											<div class="highlight purple-bg-50 text-center padding-10">
												<button class="form-ui purple-bg-400">
													<span class="icon icon-lock"></span>
													<?= __("Complete requirements first","bluerabbit"); ?>
												</button>
											</div>
										<?php }//hasReqs?>
									<?php }//isAlive?>
								<?php } //isOpen ?>
							<?php } //isLevel ?>
						<?php } //isLevel ?>
					<?php }else{ ?>
						<h4 class="font _24 white-color w900 uppercase padding-20 text-center"><?= __("Your answer","bluerabbit"); ?></h4>
						<?= apply_filters('the_content',$q->pp_content); ?>
						<h4><?php _e("This post has already been graded. Can't edit or delete.","bluerabbit"); ?></h4>
					<?php } ?>
					<input type="hidden" id="payment_nonce" value="<?= wp_create_nonce('br_payment_nonce'); ?>"/>
				</div>
			</div>
		</div>
		<input type="hidden" id="purchase-nonce" value="<?= wp_create_nonce('br_item_nonce'); ?>"/>

<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const containers = document.querySelectorAll('.dialogue-box');

  containers.forEach(container => {
    container.querySelectorAll('a').forEach(link => {
      link.setAttribute('target', '_blank');
      link.setAttribute('rel', 'noopener noreferrer'); // 🚨 security best practice
    });
  });
});

</script>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

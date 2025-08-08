<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($adventure){ ?>
	<?php $survey_data = getSurvey($_GET['questID']); ?>
	<?php if($survey_data){ ?>
		<?php 
		$survey_id = $_GET['questID'];
		$s = $survey_data['survey'];
		$qs = $survey_data['questions'];
		$answers = $survey_data['answers']; 
		$requirements = $wpdb->get_results("SELECT 

		a.req_object_id, a.req_type,
		c.quest_type, c.mech_badge, c.quest_id,
		d.item_name, d.item_badge, d.item_id,
		e.achievement_name, e.achievement_badge, e.achievement_id, e.achievement_color,
		f.achievement_applied

		FROM {$wpdb->prefix}br_reqs a
		LEFT JOIN {$wpdb->prefix}br_quests c
		ON a.req_object_id = c.quest_id AND c.quest_status='publish' AND a.quest_id=$survey_id
		LEFT JOIN {$wpdb->prefix}br_items d
		ON a.req_object_id = d.item_id
		LEFT JOIN {$wpdb->prefix}br_achievements e
		ON a.req_object_id = e.achievement_id
		LEFT JOIN {$wpdb->prefix}br_player_achievement f
		ON e.achievement_id = f.achievement_id AND f.player_id=$current_player->player_id
		WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$survey_id ");

		if($requirements){
			$reqs = array(); $reqs_ids = array();
			foreach($requirements as $r){
				$reqs[]=$r;
				$reqs_ids[$r->req_type][]=$r->req_object_id;
			}
			sort($reqs_ids['quest']); sort($reqs_ids['item']); sort($reqs_ids['achievement']);	
			$reqs_list = implode(",",$reqs_ids['quest']);
			$player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
			WHERE adventure_id=$adventure->adventure_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
			$player_challenges = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_challenge_attempts
			WHERE adventure_id=$adventure->adventure_id AND attempt_status='success' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
			$player_work = array_merge($player_quests,$player_challenges);sort($player_work);
		}
		?>
		<div class="container boxed max-w-1200">
			<div class="body-ui white-bg">
				<?php if(count($requirements) > 0){ ?>
					<?php 
						$my_quests = getMyQuests($adventure->adventure_id);
						$my_items = getMyItems($adventure->adventure_id); 
						$myKeyItems = $my_items['ids']['key'];
						$my_achievements = getMyAchievements($adventure->adventure_id); 
						$allQuestsSet = array_intersect($player_work,$reqs_ids['quest']);
						$allItemsSet = array_intersect($myKeyItems,$reqs_ids['item']);
						$allAchievementsSet = array_intersect($my_achievements, $reqs_ids['achievement']);
						$allAchievementsSet = array_values($allAchievementsSet);
						$questsReady = ($allQuestsSet == $reqs_ids['quest']) ? true : false;
						$itemsReady = ($allItemsSet == $reqs_ids['item']) ? true : false;
						$achievementsReady = ($allAchievementsSet == $reqs_ids['achievement']) ? true : false;
					?>
					<h4 class="font _24 indigo-400 w600"><?php _e("Requirements","bluerabbit"); ?>: </h4>
					<div class="highlight padding-10 amber-bg-50">
						<ul class="cards album text-center">
							<?php foreach($reqs as $r){
								if($r->req_type=="quest"){  
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $player_work) ? true : false;
									include (TEMPLATEPATH . '/milestone-req.php');
								}elseif($r->req_type=="item") {
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $myKeyItems) ? true : false;
									include (TEMPLATEPATH . '/item-req.php');
								}elseif($r->req_type=="achievement") {
									$a = $r;
									$isEarned = in_array($r->req_object_id, $my_achievements) ? true : false;
									include (TEMPLATEPATH . '/achievement-req.php');
								} 
							} ?>
						</ul>
						<?php if($questsReady && $itemsReady && $achievementsReady ){	?>
							<?php if($reqs){	?>
								<h3 class="text-center"><span class="icon icon-star"><?= __("All requirements completed","bluerabbit"); ?></span></h3>
							<?php } ?>
						<?php } ?>
					</div>
				<?php }else{ ?>
					<?php $questsReady = $itemsReady = $achievementsReady = true; ?>
				<?php } ?>
				<div class="content">
					<?php if($questsReady && $itemsReady && $achievementsReady ){	?>
						<div class="steps">
						<?php
							$count=0;
							foreach($qs as $key=>$q){ 
							?>

							<div class="step <?= $count==0 ? 'active' : ''; ?>" id="step-<?= $count; ?>">
								<div class="step-content-container">
									<div class="step-choices">
										<div class="step-choices-cell">
											<div class="step-system-message">
												<div class="survey-question-block">
													<h4> <?= __("Question","bluerabbit")." <strong>".($count+1)."</strong>/".count($qs).""; ?></h4>
													<h2><?php echo $q['text']; ?></h2>
													<?php if($q['survey_question_description']){ ?>
														<div class="font _14 padding-5"><?php echo $q['survey_question_description']; ?></div>
													<?php } ?>
													<?php if($q['image']) { ?>
														<div class="question-image">
															<img src="<?php echo $q['image']; ?>">
														</div>
													<?php } ?>
												</div>
												<?php $nonceStr = "{$q['text']}-$key"; ?>
												<input type="hidden" id="sq-nonce-<?php echo $key; ?>" value="<?php echo wp_create_nonce($nonceStr); ?>"/>
												<div class="content">
													<?php include (TEMPLATEPATH . "/survey-question-{$q['survey_question_type']}.php");	?>
												</div>
												
												
												
												<div class="step-buttons">
												<?php if($count > 0){ ?>
													<a class="step-back-button" id="button-system-back-<?= $count;?>" href="#step-<?= $count-1; ?>">
														<?= __("Back","bluerabbit"); ?>
													</a>
												<?php }?>
												<?php if($count >= count($qs)-1){ ?>
													<button class="finish-quest-button" id="button-system-finish-<?=$count;?>" onClick="fakeSubmit();">
														<?= __("Finish","bluerabbit"); ?>
													</button>
													<?php if($show_survey_answers || $isGM|| $isAdmin){ ?>
														<a class="form-ui orange-bg-400" href="<?php echo get_bloginfo('url')."/survey-results/?questID=$survey_id&adventure_id=$adventure_id"; ?>">
															<?php _e('View Results','bluerabbit'); ?>
														</a>
													<?php } ?>
												<?php }else{ ?>
													<a class="step-next-button" id="button-system-next-<?= $count;?>" href="#step-<?= $count+1?>">
														<?= __("Next","bluerabbit"); ?>
													</a>
												<?php } ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php $count++; ?>
							<?php } ?>
						</div>					
					<?php }else{ ?>
						<div class="highlight purple-bg-50 text-center padding-10">
							<button class="form-ui purple-bg-400">
								<span class="icon icon-lock"></span>
								<?php echo __("Complete requirements first","bluerabbit"); ?>
							</button>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<input type="hidden" id="the_survey_id" value="<?php echo $s->quest_id; ?>"/>
	<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php } ?>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
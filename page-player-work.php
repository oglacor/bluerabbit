<?php include (get_stylesheet_directory() . '/header.php'); ?>


<div class="text-center padding-10">
	<span class="icon-button sq-100 white-bg border border-1 border-all white-border" style="background-image: url(<?= $current_player->player_picture; ?>); "></span>
	<h2 class="font _48 white-color"><?php _e("My Work","bluerabbit"); ?></h2>
</div>
	<?php 


	if(($isGM || $isAdmin || $isNPC) && $_GET['player_id']){
		$the_player_id = $_GET['player_id'];
		$current_player = getPlayerData($the_player_id);
	}else{
		$the_player_id = $current_user->ID;
	}
	$myquests = $wpdb->get_results("SELECT 
		a.pp_grade, a.pp_modified, a.quest_id,a.pp_status,
		b.quest_title, b.quest_type,
		b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge, b.quest_success_message

		FROM {$wpdb->prefix}br_player_posts a
		LEFT JOIN {$wpdb->prefix}br_quests b
		ON a.quest_id = b.quest_id

		WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id AND b.quest_status='publish'
		ORDER BY a.pp_modified
	");
	$mysurveys = $wpdb->get_results("SELECT surveys.*
	FROM {$wpdb->prefix}br_survey_answers answers
	JOIN  {$wpdb->prefix}br_quests surveys
	ON surveys.quest_id = answers.survey_id AND surveys.quest_status='publish'
	JOIN  {$wpdb->prefix}br_survey_questions questions
	ON surveys.quest_id = questions.survey_id AND questions.survey_question_status='publish'
	WHERE surveys.adventure_id=$adventure_id AND answers.player_id=$the_player_id AND (answers.survey_option_id > 0 OR answers.survey_answer_value!='') GROUP BY answers.survey_id");
	$attempts = $wpdb->get_results("
		SELECT
		a.attempt_grade, a.attempt_answers, a.quest_id, a.attempt_id, a.attempt_date, a.attempt_status,
		b.quest_title, b.quest_type, b.quest_id,
		b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge

		FROM {$wpdb->prefix}br_challenge_attempts a
		LEFT JOIN {$wpdb->prefix}br_quests b
		ON a.quest_id = b.quest_id

		WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id AND b.quest_type='challenge'  AND a.attempt_status !='trash'
	");

	$attempt_answers = $wpdb->get_results("
		SELECT a.*, b.answer_value AS c_answer_value, c.question_title FROM {$wpdb->prefix}br_challenge_attempt_answers a
		LEFT JOIN {$wpdb->prefix}br_challenge_answers b
		ON a.answer_id=b.answer_id
		LEFT JOIN {$wpdb->prefix}br_challenge_questions c
		ON a.question_id=c.question_id
		LEFT JOIN {$wpdb->prefix}br_quests d
		ON c.quest_id=d.quest_id

		WHERE d.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id
	"); 
	$current_user = wp_get_current_user();

	?>

<div class="tabs boxed max-w-900">
	<div class="tab-header" id="post-tabs-buttons">
		<button id="quests-tab-tab-button" class="tab-button transparent-bg button form-ui relative white-color active" onClick="switchTabs('#post-tabs','#quests-tab')">
			<span class="layer base relative"><?= __("Quests","bluerabbit"); ?></span>
			<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
			<span class="background absolute sq-full layer active-content blue-bg-400"></span>
		</button>
		<button id="challenges-tab-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#challenges-tab')">
			<span class="layer base relative"><?= __("Challenges","bluerabbit"); ?></span>
			<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
			<span class="background absolute sq-full layer active-content red-bg-400"></span>
		</button>
<!--
			<button id="secret-messages-tab-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#secret-messages-tab')">
				<span class="layer base relative"><?= __("Secrets & Clues","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content blue-bg-400"></span>
			</button>
-->
		<?php if($use_surveys){ ?>
			<button id="surveys-tab-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#surveys-tab')">
				<span class="layer base relative"><?= __("Surveys","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content teal-bg-400"></span>
			</button>
		<?php } ?>
		<?php if(getSetting('demo_adventure',$adventure->adventure_id)){ ?>
			<button id="reset-tab-tab-button" class="tab-button transparent-bg button form-ui relative white-color" onClick="switchTabs('#post-tabs','#reset-tab')">
				<span class="layer base relative"><?= __("Reset Demo","bluerabbit"); ?></span>
				<span class="background absolute sq-full layer inactive-content grey-bg-800"></span>
				<span class="background absolute sq-full layer active-content blue-bg-400"></span>
			</button>
		<?php } ?>
	</div>
	
	<div class="tab-group tabs white-color" id="post-tabs">
		<div class="tab active text-center" id="quests-tab">
			<?php if($myquests){ ?>
				<div class="content">
					<h1 class=" font _36 w100 padding-10 w-full white-color blue-bg-400 text-left">
						<span class="icon icon-quest"></span>
						<?php _e("Quests","bluerabbit"); ?>
					</h1>
					<table class="table">
						<thead>
							<tr>
								<td class=""><?php _e("Level","bluerabbit"); ?></td>
								<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
								<td class=""><span class="icon icon-star solid-amber"></span></td>
								<td class=""><span class="icon icon-bloo solid-green"></span></td>
								<td class=""><?php _e("Actions","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($myquests as $key=>$q){ ?>
								<?php if($q->quest_type=='quest'){ ?>
								<tr class="quest-item <?php echo $q->quest_type." ".$q->pp_status; ?>">
									<td class="text-center"><?php echo $q->mech_level; ?></td>
									<td class=""><a href="<?php echo get_bloginfo('url')."/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>"><?php echo $q->quest_title; ?></td>
									<td class=""><?php echo $q->mech_xp; ?></td>
									<td class=""><?php echo $q->mech_bloo; ?></td>
									<td>
										<?php if($q->pp_status =='publish'){ ?>
											<a href="<?php echo get_bloginfo('url')."/post/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="form-ui indigo-bg-400">
												<span class="icon icon-<?php echo $q->quest_type; ?>"></span> <?php _e("View","bluerabbit"); ?>
											</a>
											<?php if(!$q->pp_grade){ ?>
												<a href="<?php echo get_bloginfo('url')."/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>" class="form-ui green-bg-400">
													<span class="icon icon-edit"></span><?php _e("Edit","bluerabbit"); ?>
												</a> 
												<button class="form-ui red-bg-400" onClick="br_confirm_trd('trash',<?php echo $q->quest_id; ?>,'player_post');"><span class=" icon icon-trash"></span> <?php _e("Trash","bluerabbit"); ?></button>
											<?php }else{ ?>
												<strong><?php _e("Graded","bluerabbit"); ?></strong>
											<?php } ?>
										<?php }else{?>
											<button class="form-ui blue-bg-400" onClick="br_confirm_trd('publish',<?php echo $q->quest_id; ?>,'player_post');"><span class=" icon icon-restore"></span> <?php _e("Restore","bluerabbit"); ?></button>
											<button class="form-ui red-A400" onClick="br_confirm_trd('delete',<?php echo $q->quest_id; ?>,'player_post');"><span class=" icon icon-trash"></span> <?php _e("Delete","bluerabbit"); ?></button>
										<?php } ?>
									</td>
								</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php }else{ ?> 
				<div class="highlight blue-bg-50 text-center padding-10">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  blue-bg-400"><span class="icon icon-quest"></span></span>
					</span>
					<span class="icon-content">
						<span class="line font _24 grey-700"><?php _e("No quests found","bluerabbit"); ?></span>
						<span class="line font _14 black-color opacity-50"><?php _e("You must solve quests to show the results here","bluerabbit"); ?></span>
					</span>
				</div>
			<?php } ?>
		</div>
		<div class="tab text-center" id="challenges-tab">
			<?php if($attempts){ ?>
				<div class="content">
					<h1 class=" font _36 w100 padding-10 w-full white-color red-bg-400 text-left">
						<span class="icon icon-quest"></span>
						<?php _e("Challenge Attempts","bluerabbit"); ?>
					</h1>
					<table class="table">
						<thead>
							<tr>
								<td>ID</td>
								<td><?php _e("Name","bluerabbit"); ?></td>
								<td><?php _e("Status","bluerabbit"); ?></td>
								<td><?php _e("Date","bluerabbit"); ?></td>
								<?php if($isGM){ ?>
									<td><?php _e("Actions","bluerabbit"); ?></td>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach($attempts as $key=>$a){ ?>
								<tr>
									<td><?php echo "$a->attempt_id | $a->quest_id"; ?></td>
									<td><?php echo $a->quest_title; ?></td>
									<td>
										<?php if($a->attempt_status =='success'){ ?>
											<span class="icon-sm icon-button font _24 sq-40  green-bg-300"><span class="icon icon-check"></span></span>
										<?php }else{ ?>
											<span class="icon-sm icon-button font _24 sq-40  red-bg-300"><span class="icon icon-cancel"></span></span>
										<?php } ?>
									</td>
									<td>
										<?php 
											$difference = date('Z');
											 $date = date('Y - m - d', strtotime($a->attempt_date));
											 $time = date('g:i:s A', strtotime($a->attempt_date)+$difference);
											echo  "$date, $time";
										?>
									</td>
									<?php if($isGM){ ?>
										<td>
											<button class="form-ui red-bg-A400" onClick="br_confirm_trd('trash', <?php echo $a->attempt_id; ?>,'attempt');">
												<span class=" icon icon-trash"></span><?php _e("Trash","bluerabbit"); ?></button>
											<input type="hidden" class="quest-id" value="<?php echo $a->attempt_id; ?>">
										</td>
									<?php } ?>
								</tr>
								<?php if($isGM){ ?>
									<tr>
										<td colspan="5">
											<h4><?php _e("Answers","bluerabbit"); ?></h4>
											<ul class="inline-block" >
												<?php foreach ($attempt_answers as $aa){ ?>
													<?php if($aa->attempt_id == $a->attempt_id){ ?>
														<li>

															<span class="blue-grey-200 font _14"><strong>Q: </strong><?php echo $aa->question_title; ?></span><br>
															<span class="font _16 w400">
                                                                <?php 
                                                                if($aa->answer_value){
                                                                    $a_values = $wpdb->get_col("SELECT answer_value FROM {$wpdb->prefix}br_challenge_answers WHERE answer_id IN ($aa->answer_value)");
                                                                    $total = count($a_values);
                                                                    echo "<strong>  ".__("Your answers","bluerabbit").": [$total] </strong>";
                                                                    foreach($a_values as $mca){
                                                                        echo "<button class='form-ui blue-grey-bg-200 blue-grey-900'>$mca </button> " ;
                                                                    }
                                                                }else{
                                                                    echo "<strong>".__("Your answer","bluerabbit").": </strong> $aa->c_answer_value";
                                                                }
                                                                ?>
                                                            </span>
														</li>
													<?php } ?>	
												<?php } ?>
											</ul>
										</td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php }else{ ?> 
				<div class="highlight brown-bg-50 text-center padding-10">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400"><span class="icon icon-challenge"></span></span>
					</span>
					<span class="icon-content">
						<span class="line font _24 grey-700"><?php _e("No attempts found","bluerabbit"); ?></span>
						<span class="line font _14 black-color opacity-50"><?php _e("You must attempt challenges to show the results here","bluerabbit"); ?></span>
					</span>
				</div>
			<?php } ?>
		</div>
		<div class="tab text-center" id="surveys-tab">
			<?php if($mysurveys){ ?>
				<div class="content">
					<h1 class=" font _36 w100 padding-10 w-full white-color teal-bg-400 text-left">
						<span class="icon icon-quest"></span>
						<?php _e("Survey Answers","bluerabbit"); ?>
					</h1>
					<table class="table">
						<thead>
							<tr>
								<td class=""><?php _e("Level","bluerabbit"); ?></td>
								<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
								<td class=""><span class="icon icon-star solid-amber"></span></td>
								<td class=""><span class="icon icon-bloo solid-green"></span></td>
								<td class=""><?php _e("Actions","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($mysurveys as $key=>$s){ ?>
								<tr class="survey-item">
									<td class="text-center"><?php echo $s->mech_level; ?></td>
									<td class=""><a href="<?php echo get_bloginfo('url')."/survey/?adventure_id=$adventure->adventure_id&questID=$s->quest_id";?>"><?php echo $s->quest_title; ?></td>
									<td class=""><?php echo $s->mech_xp; ?></td>
									<td class=""><?php echo $s->mech_bloo; ?></td>
									<td>
										<a href="<?php echo get_bloginfo('url')."/survey/?adventure_id=$adventure->adventure_id&questID=$s->quest_id";?>" class="form-ui indigo-bg-400">
											<span class="icon icon-survey"></span> <?php _e("View","bluerabbit"); ?>
										</a>
										<button class="icon-button font _24 sq-40  icon-sm red-bg-200 white-color delete-button" onClick="showOverlay('#confirm-delete-<?php echo $s->quest_id; ?>');">
											<span class="icon icon-delete"></span>
											<span class="tool-tip bottom">
												<span class="tool-tip-text font _12"><?php _e("Delete my answers","bluerabbit"); ?></span>
											</span>
										</button>
										<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?php echo $s->quest_id; ?>">
											<button class="form-ui grey-bg-800 delete-confirm-button" onClick="confirmStatus(<?php echo $s->quest_id; ?>,'survey-answer','delete');">
												<span class="icon-group">
													<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
														<span class="icon icon-delete white-color"></span>
													</span>
													<span class="icon-content">
														<span class="line white-color font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
														<span class="line white-color  font _14 w300"><?php _e("You can't undo this","bluerabbit"); ?></span>
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
				<div class="highlight teal-bg-50 text-center padding-10">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  teal-bg-400"><span class="icon icon-survey"></span></span>
					</span>
					<span class="icon-content">
						<span class="line font _24 grey-700"><?php _e("No survey answers found","bluerabbit"); ?></span>
						<span class="line font _14 black-color opacity-50"><?php _e("You must answer surveys to show the results here","bluerabbit"); ?></span>
					</span>
				</div>
			<?php } ?>
		</div>
			<?php if($isDemo){ ?>
				<div class="tab text-center" id="reset-tab">
					<div class="padding-10 white-color text-center font _24 w900 layer base relative">
						<button class="form-ui red-bg-400 white-color" onClick="showOverlay('#reset-demo-form');"><span class="icon icon-rotate"></span><?= __("Reset Demo","bluerabbit"); ?></button>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
		
<input type="hidden" id="reload" value="true">
<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>" />
<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

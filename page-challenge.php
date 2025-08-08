<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($adventure){ ?>

<?php $challenge_data = getChallenge($_GET['questID'], $adv_child_id); ?>
<?php if($challenge_data){ ?>
	<?php 
	$c = $challenge_data['challenge']; 
	
	$finished = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_posts WHERE player_id=$current_player->player_id AND quest_id=$c->quest_id");
	$qs = $challenge_data['questions'];
	$answers = $challenge_data['answers'];  
	$fails = $challenge_data['fails'];
	$attempts = $challenge_data['attempts'];
	
	$qtd = $c->mech_questions_to_display > count($qs) ? count($qs) : $c->mech_questions_to_display;
	$atw = $c->mech_answers_to_win > count($qs) ? count($qs) : $c->mech_answers_to_win;
	
	$numbers = array(
		'attempts' => count($attempts),
		'fails' => $fails,
		'questions' => count($qs),
		'questions-needed' => $qtd,
		'correct-needed' => $atw,
		'max-attempts' => $c->mech_max_attempts,
		'time-limit' => $c->mech_time_limit,
	);
	if(isset($qs) && is_array($qs)){
		shuffle ($qs); 
	}
	if(isset($answers) && is_array($answers)){
		shuffle ($answers); 		
	}

?>

<div class="layer background fixed" style="background-image: url(<?= $c->mech_badge; ?>);"></div>
<div class="layer background fixed challenge-gradient-overlay"></div>

<div class="challenge-quest idle" id="challenge">
	<div class="challenge-sidebar">
		<div class="content">
			<div class="challenge-title">
				<h1><?= $c->quest_title; ?></h1>
				<?php if($finished){ ?>
					<h1 class="font _20 w900 text-center w-full padding-10 lime-bg-400 blue-grey-900"><?= __("Finished!","bluerabbit"); ?></h1>
				<?php } ?>
			</div>
			<?php if($c->mech_time_limit){ ?>
			<div class="challenge-timer" id="challenge-timer">
				<input type="hidden" id="the_time_limit" value="<?= $c->mech_time_limit; ?>">
				<span class="icon-button timer-icon pink-bg-400 white-color sq-40 font _26"><span class="icon icon-stopwatch"></span></span>
				<div class="progress" style="width:100%;"></div>
				<span class="timer-container"><span id="timer" class="timer"><?= $c->mech_time_limit; ?></span>s</span>
			</div>
			<?php } ?>
			<div class="challenge-nav-mobile">
				<?php
				$show = $qtd;
				for($i=0;$i<$show;$i++){ ?>
				<button class="icon-button question-number" id="question-number-mobile-<?= $qs[$i]->question_id; ?>" onClick="navToQuestion('<?= $qs[$i]->question_id; ?>');">
					<?= ($i+1);?>
				</button>
				<?php }  ?>
			</div>
			<div class="challenge-nav">
				<table cellspacing="10">
					<tbody>
						<?php
						for($i=0;$i<$show;$i++){
							include (TEMPLATEPATH . '/challenge-question-nav-item.php');
						} 
						?>
					</tbody>
				</table>
				<div class="challenge-complete-actions relative text-center padding-20">
					<button class="form-ui font _18 green-bg-400 blue-grey-900 white-color" onClick="showOverlay('#confirm-challenge-complete');">
						<span class="icon icon-goal "></span>
						<?= __("Submit answers!","bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-challenge-complete">
						<button class="form-ui red-bg-A400" onClick="gradeChallenge();">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  icon-sm white-bg icon-sm">
									<span class="icon icon-goal blue-grey-800"></span>
								</span>
								<span class="icon-content">
									<span class="line red-bg-A400 white-color font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
								</span>
							</span>
						</button>
						<button class="close-confirm icon-button font _16 sq-30 red-bg-400 white-color padding-5" onClick="hideAllOverlay();">
							<span class="icon icon-cancel white-color"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="challenge-conditions white-color">
		<div class="content">
			<h3 class="font special _16 yellow-500"><?= __("Conditions","bluerabbit"); ?></h3>
			<h3 class="font special _16 yellow-500"></h3>
			<?php $max_attempts = ($c->mech_max_attempts > 0) ? $c->mech_max_attempts : "<span class='icon icon-infinite'></span>"; ?>
			<h2>
				<span class="icon icon-objectives yellow-400"></span>
				<?= __("Used","bluerabbit")." <strong>{$numbers['attempts']}</strong>/$max_attempts ".__("attempts","bluerabbit"); ?>
			</h2>
			<h2>
				<span class="icon icon-check yellow-400"></span>
				<?= "<strong>{$numbers['correct-needed']}</strong>/{$numbers['questions-needed']} ".__("Correct answers","bluerabbit"); ?>
			</h2>
			<?php if(isset($c->mech_time_limit) && $c->mech_time_limit >0){ ?>
				<h2>
					<span class="icon icon-stopwatch yellow-400"></span>
					<?= " <strong>$c->mech_time_limit</strong> ".__("seconds","bluerabbit"); ?>
				</h2>
			<?php }else{ ?>
				<h2>
					<span class="icon icon-stopwatch yellow-400"></span>
					<?= __("No time limit","bluerabbit"); ?>
				</h2>
			<?php } ?>
			<h3 class="font special _16 yellow-500"><?= __("Resources","bluerabbit"); ?></h3>
			<div class="block-text font  inline-block border rounded-8 padding-5 blue-grey-bg-900 amber-border-400 border-all">
				<span class="font condensed amber-400 w100"><?= $xp_label; ?></span> <span class="font w600 white-color"><?= toMoney($c->mech_xp,""); ?></span>
			</div>
			<div class="block-text font  inline-block border rounded-8 padding-5 blue-grey-bg-900 light-green-border-A200 border-all">
				<span class="font condensed light-green-A200 w100"><?= $bloo_label; ?></span> <span class="font w600 white-color"><?= toMoney($c->mech_bloo,""); ?></span>
			</div>
			<?php if($use_encounters){ ?>
			<div class="block-text font  inline-block border rounded-8 padding-5 blue-grey-bg-900 cyan-border-A200 border-all">
				<span class="font condensed cyan-A200 w100"><?= $ep_label; ?></span> <span class="font w600 white-color"><?= toMoney($c->mech_ep,""); ?></span>
			</div>
			<?php } ?>
			
			<div class="action-button">
			
				<?php if(isset($challenge_data['unavailable']) ) { ?>
					<div class="layer base challenge-actions challenge-unavailable" id="challenge-actions">
						<?php if($challenge_data['locks']['level']==true) { ?>

							<span class="challenge-main-action-button level-button">
								<span class="main-text"><span class="icon icon-cancel"></span></span>
								<span class="button-legend">
									<?= __("Available at Level","bluerabbit")." <strong>$c->mech_level</strong>"; ?>
								</span>
								<span class="circle-back"></span><span class="circle-front"></span>
							</span>
						<?php }elseif($challenge_data['locks']['max_attempts']==true)  { ?>
							<span class="challenge-main-action-button max-button">
								<span class="main-text"><span class="icon icon-cancel"></span></span>
								<span class="button-legend">
									<?= __("Max Attempts Reached","bluerabbit"); ?>
								</span>
								<span class="circle-back"></span><span class="circle-front"></span>
							</span>
						<?php }elseif($challenge_data['locks']['requirements']==true)  { ?>
							<!-- Requirements -->
							<span class="challenge-main-action-button reqs-button">
								<span class="main-text"><span class="icon icon-cancel"></span></span>
								<span class="button-legend">
									<?= __("Requierements not met!","bluerabbit"); ?>
								</span>
								<span class="circle-back"></span><span class="circle-front"></span>
							</span>
						<?php }elseif($challenge_data['locks']['deadline']==true)  { ?>
							<!-- DEADLINE -->
							<?php $niceDeadline = date('D, F jS, Y',strtotime($c->mech_deadline));?>

							<span class="challenge-main-action-button deadline-button">
								<span class="main-text"><span class="icon icon-cancel"></span></span>
								<span class="button-legend">
									<?= $niceDeadline; ?>
								</span>
								<span class="circle-back"></span><span class="circle-front"></span>
							</span>
							<?php if($challenge_data['locks']['deadline_cost']){ ?>
								<div class="deadline-cost-confirm-button">
									<button class="form-ui w-200 red-bg-400 font _24" onClick="showOverlay('#confirm-deadline-quest-<?= $c->quest_id; ?>');">
										<span class="icon icon-bloo"></span><?= __("Purchase deadline","bluerabbit"); ?>
									</button>
									<div class="overlay-layer confirm-action bottom" id="confirm-deadline-quest-<?= $c->quest_id; ?>">

										<button class="form-ui blue-bg-400" onClick="payment(<?=$c->quest_id;?>,'deadline');">
											<span class="font _24 w700"><?= __("Are you sure?","bluerabbit"); ?></span><br>
											<span class="font _18 w300">
												<span class="icon icon-bloo"></span>
												<?= __("Pay","bluerabbit")." ".toMoney($c->mech_deadline_cost,""); ?>
											</span>
										</button>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				<?php }else { ?>
					<div class="layer base challenge-actions" id="challenge-actions">
						<button class="challenge-main-action-button light-green-A200" id="start-attempt-btn" onClick="startAttempt();">
							<?= __("Start!","bluerabbit"); ?>
						</button>
						<div class="attempt-cost-legend light-green-bg-A200">
							<?php
							if(($challenge_data['freeAttempts'] > 0 || $c->mech_attempt_cost <= 0) || $challenge_data['locks']['conquered']==true){ ?>
								<h3 class="font special"><?= __("Free attempt ","bluerabbit"); ?></h3>
								<?php $cost=0; ?>
							<?php }else{ ?>
								<h3 class="font special"><?= __("Attempt cost","bluerabbit"); ?></h3>
								<h1 class=""><span class='icon icon-bloo'></span><?= toMoney($c->mech_attempt_cost); ?></h1>
								<?php $cost=$c->mech_attempt_cost; ?>
							<?php }	?>
						</div>
						<input type="hidden" id="the_attempt_cost" value="<?= $cost; ?>">
					</div>
				<?php } ?>
			
			</div>
		</div>
	</div>
	<div class="challenge-content">
		<div id="challenge-questions" class="layer base challenge-questions">
			<?php
			for($i=0;$i<$show;$i++){
				include (TEMPLATEPATH . '/challenge-question.php');
			} 
			?>
		</div>
		<div class="challenge-complete-actions-bottom-page relative layer overlay text-center padding-20">
			<button class="form-ui font _18 green-bg-400 blue-grey-900 white-color" onClick="showOverlay('#confirm-challenge-bottom-page');">
				<span class="icon icon-goal "></span>
				<?= __("Submit answers!","bluerabbit"); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-challenge-bottom-page">
				<button class="form-ui red-bg-A400" onClick="gradeChallenge();">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  icon-sm white-bg icon-sm">
							<span class="icon icon-goal blue-grey-800"></span>
						</span>
						<span class="icon-content">
							<span class="line red-bg-A400 white-color font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
						</span>
					</span>
				</button>
				<button class="close-confirm icon-button font _16 sq-30 red-bg-400 white-color padding-5" onClick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
		</div>

	</div>
	<?php if($c->mech_time_limit){ ?>
		<div class="times-up layer feedback fixed sq-full" id="times-up" onClick="gradeChallenge(); $('#times-up').fadeOut(500);">
			<div class="layer absolute sq-full background black-bg opacity-80"></div>
			<div class="absolute base perfect-center layer white-color">
				<img src="<?= get_bloginfo('template_directory'); ?>/images/timesup.png" width="300" alt=""/>
				<h1 class="font _48"><strong><?php _e("Time's up!","bluerabbit"); ?></strong></h1>
			</div>
		</div>
	<?php } ?>
	<div class="challenge-complete-actions-mobile relative text-center padding-20">
		<button class="form-ui font _18 green-bg-400 blue-grey-900 white-color" onClick="showOverlay('#confirm-challenge-complete-mobile');">
			<span class="icon icon-goal "></span>
			<?= __("Submit answers!","bluerabbit"); ?>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-challenge-complete-mobile">
			<button class="form-ui red-bg-A400" onClick="gradeChallenge();">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  icon-sm white-bg icon-sm">
						<span class="icon icon-goal blue-grey-800"></span>
					</span>
					<span class="icon-content">
						<span class="line red-bg-A400 white-color font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="close-confirm icon-button font _16 sq-30 red-bg-400 white-color padding-5" onClick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
	</div>

</div>


<input type="hidden" id="payment_nonce" value="<?= wp_create_nonce('br_payment_nonce'); ?>"/>
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_player_new_attempt_nonce'); ?>" />
<input type="hidden" id="the_challenge_id" value="<?= $c->quest_id; ?>"/>
<input type="hidden" id="the_attempt_id" value=""/>

	<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php } ?>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
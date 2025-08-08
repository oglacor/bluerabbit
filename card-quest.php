<?php
/// COLOR
	$icon_color = "";
	if($quest->quest_type == 'quest'){
		$mi_color = $icon_color = 'light-blue';
	}elseif($quest->quest_type == 'challenge'){
		$mi_color = $icon_color = 'red';
	}elseif($quest->quest_type == 'mission'){
		$mi_color = $icon_color = 'amber';
	}elseif($quest->quest_type == 'social'){
		$mi_color = $icon_color = 'red';
	}elseif($quest->quest_type == 'survey'){
		$mi_color = $icon_color = 'teal';
	}
	$mi_link = get_bloginfo("url")."/$quest->quest_type/?questID=$quest->quest_id&adventure_id=$adv_child_id";
	if($isFinished){
		if($mi->quest_type == "quest" || $mi->quest_type == "challenge"){
			$mi_link = get_bloginfo("url")."/post/?questID=$mi->quest_id&adventure_id=$adv_child_id";
		} 
		switch ($quest->quest_type){
			case 'quest': $legend = __("View answer","bluerabbit");  break;
			case 'challenge' : $legend = __("Check results!","bluerabbit"); break;
			case 'mission' : $legend = __("Mission brief","bluerabbit"); break;
			case 'social' : $legend = __("Check tweets!","bluerabbit"); break;
			case 'survey' : $legend = __("View answers","bluerabbit"); break;
			default : $legend = __("View","bluerabbit"); break;
		}
	}else{
		switch ($quest->quest_type){
			case 'quest':  $legend = __("Solve this!","bluerabbit"); break;
			case 'challenge' : $legend = __("Attempt now!","bluerabbit"); break;
			case 'mission' :$legend =  __("Mission brief","bluerabbit"); break;
			case 'social' : $legend = __("Tweet Now!","bluerabbit"); break;
			case 'survey' : $legend = __("Answer survey","bluerabbit"); break;
			default : $legend = __("Go!","bluerabbit"); break;
		}
		
	}
?>


<div class="background blue-grey-bg-700 opacity-60 fixed" onClick="unloadCard();"></div>
<div class="card card-scene <?= "$quest->quest_type level{$quest->mech_level}";  ?>" id="<?= "quest-card-$quest->quest_id"; ?>">
	<div class="card-content">
		<div class="card-face frontface">
			<?php if($isGM || $isNPC || $isAdmin){ ?>
				<a class="layer foreground icon-button font _14 sq-20 absolute top-10 left-10 green-bg-400" href="<?= get_bloginfo("url")."/new-$quest->quest_type/?questID=$quest->quest_id&adventure_id=$adv_child_id"; ?>">
					<span class="icon icon-edit"></span>
				</a>
			<?php } ?>
			<button class="layer foreground absolute icon-button font _14 sq-20  top-10 right-10 red-bg-400" onClick="unloadCard();"><span class="icon icon-cancel"></span></button>

			<div class="layer background absolute sq-full top left blend-luminosity grey-bg-900 opacity-80" style="background-image: url(<?= $quest->mech_badge; ?>);"></div>
			<div class="layer background absolute sq-full top left grey-bg-900 opacity-80"></div>
			<div class="layer background absolute sq-full top left <?=$mi_color;?>-gradient-900 opacity-60"></div>
			<div class="layer base absolute sq-full top left">
				<div class="card-type text-center <?=$mi_color;?>-bg-400 blue-grey-900" >
					<span class="inline-block" id="xp-number-q-<?=$quest->quest_id; ?>">
						<span class="icon icon-star"></span>
						<span class="number">0</span>
						<input type="hidden" class="end-value" value="<?= $quest->mech_xp; ?>">
					</span>
					<script>animateNumber('#xp-number-q-<?=$quest->quest_id; ?>',1500,1500);</script>
					<span class="inline-block" id="bloo-number-q-<?=$quest->quest_id; ?>">
						<span class="icon icon-bloo"></span>
						<span class="number">0</span>
						<input type="hidden" class="end-value" value="<?= $quest->mech_bloo; ?>">
					</span>
					<script>animateNumber('#bloo-number-q-<?=$quest->quest_id; ?>',1500,1500);</script>
					<?php if(getSetting('use_encounters', $quest->adventure_id) > 0){ ?>
						<span class="inline-block" id="ep-number-q-<?=$quest->quest_id; ?>">
							<span class="icon icon-activity"></span>
							<span class="number">0</span>
							<input type="hidden" class="end-value" value="<?= $quest->mech_ep; ?>">
						</span>
						<script>animateNumber('#ep-number-q-<?=$quest->quest_id; ?>',1500,1500);</script>
					<?php } ?>
				</div>
				<div class="layer base perfect-center absolute text-center w-full">
					<div class="badge-container">
						<img src="<?= $quest->mech_badge; ?>" class="badge" >
						<img class="rotate-L-20 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-R-30 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-L-40 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-R-60 mix-blend-overlay halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-L-90 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<h1 class="font _30 w600 condensed kerning-1 padding-10 text-center w-full"><?= $quest->quest_title; ?></h1>
					<?php if($quest->quest_secondary_headline){ ?>
						<p class="padding-5 font _14 w300 opacity-80"><?= $quest->quest_secondary_headline; ?></p>
					<?php } ?>

					<div class="mechanics">
						<?php if($quest->quest_type =='challenge'){?>
							<div class="highlight text-center padding-0">
								<div class="icon-group inline-table padding-10">
									<span class="icon-button font _24 sq-40  light-green-bg-400">
										<span class="background opacity-20 black-color overflow-hidden border rounded-max"><span class="icon icon-check icon-md"></span></span>
										<span class="foreground"><?= $quest->mech_answers_to_win; ?></span>
										<span class="legend active font _12"><?= __("Answers","bluerabbit"); ?></span>
									</span>
									<span class="icon-button font _24 sq-40  red-bg-400">
										<span class="background opacity-20 black-color overflow-hidden border rounded-max"><span class="icon icon-objectives icon-lg"></span></span>
										<?php if($quest->mech_max_attempts > 0){ ?>
											<?= $quest->mech_max_attempts; ?>
										<?php }else{ ?>
											<span class="icon icon-infinite"></span>
										<?php } ?>
										<span class="legend active bottom font _12"><?= __("Attempts","bluerabbit"); ?></span>
									</span>
									<span class="icon-button font _24 sq-40  pink-bg-400">
										<span class="background opacity-20 black-color overflow-hidden border rounded-max"><span class="icon icon-time"></span></span>
										<?php 
										if($quest->mech_time_limit > 0){
											echo $quest->mech_time_limit;
										}else{
											echo "<span class='icon icon-infinite'></span>";
										}
										?>
										<span class="legend active font _12"><?= __("Time","bluerabbit"); ?></span>
									</span>
								</div>
							</div>
						<?php }elseif($quest->quest_type =='survey'){?>
							<?php
								$survey_qs = $wpdb->get_results("
									SELECT a.*, COUNT(DISTINCT a.survey_question_id) AS total_questions, COUNT(DISTINCT b.survey_question_id) AS total_answers
									FROM {$wpdb->prefix}br_survey_questions a
									LEFT JOIN {$wpdb->prefix}br_survey_answers b ON a.survey_id=b.survey_id AND b.player_id=$current_user->ID
									WHERE a.survey_id=$quest->quest_id AND a.survey_question_status='publish'
								");
							?>
							<div class="highlight text-center padding-0">
								<span class="icon-group inline-table padding-10 white-color">
									<span class="icon-button font _24 sq-40  teal-bg-400 font w900 _24"><span class="icon icon-question"></span></span>
									<span class="icon-content">
										<span class="line font _24 w700"><?= "{$survey_qs[0]->total_answers} / {$survey_qs[0]->total_questions}"; ?></span>
										<span class="line font _14"><?= __("Answered / Questions","bluerabbit"); ?></span>
									</span>
								</span>
							</div>
						<?php } ?>
						<?php if($quest->mech_deadline != '0000-00-00 00:00:00'){ ?>
							<div class="highlight text-center padding-0 white-color font _20">
								<h3 class="font uppercase _18 w300 kerning-2 padding-10 foreground"><?= __("Time left","bluerabbit"); ?></h3>
								<span class="icon-group font special padding-20  white-color border rounded-max" id="deadline-countdown">
									<div class="background black-bg opacity-60  border rounded-max"></div>
									<span class="font special icon-button font _20 sq-40  orange-300 transparent-bg" id="deadline-days">
										<span class="number"></span>
										<span class="legend active bottom font _10 text-center lowercase main"><?= __("Days","bluerabbit"); ?></span>
										<span class="halo rotate-L-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
									</span>
									<span class="font special icon-button font _20 sq-40  cyan-600 transparent-bg" id="deadline-hours">
										<span class="number"></span>
										<span class="legend active bottom font _10 text-center lowercase main"><?= __("Hours","bluerabbit"); ?></span>
										<span class="halo rotate-R-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
									</span>
									<span class="font special icon-button font _20 sq-40  cyan-600 transparent-bg" id="deadline-minutes">
										<span class="number"></span>
										<span class="legend active bottom font _10 text-center lowercase main"><?= __("Minutes","bluerabbit"); ?></span>
										<span class="halo rotate-L-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
									</span>
									<span class="font special icon-button font _20 sq-40  blue-grey-200 transparent-bg" id="deadline-seconds">
										<span class="number"></span>
										<span class="legend active bottom font _10 text-center lowercase main"><?= __("Seconds","bluerabbit"); ?></span>
										<span class="halo rotate-R-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
									</span>
								</span>
								<script>
									deadlineCountdown('<?= date('M j, Y H:i:s',strtotime($quest->mech_deadline)); ?>');
								</script>
							</div>
						<?php } ?>
					</div>
					
					<a href="<?php echo $mi_link; ?>" class="form-ui <?php echo $mi_color; ?>-bg-400 font _20 padding-10 margin-10">
						<?=$legend;?>
					</a>
					<?php if(($isGM || $isNPC || $isAdmin) && $quest->quest_type=='survey'){ ?>
						<br>
						<a href="<?php echo get_bloginfo('url')."/survey-results/?questID=$quest->quest_id&adventure_id=$adv_child_id"; ?>" class="form-ui teal-bg-400">
							<span class="icon icon-<?php echo $quest->quest_type; ?>"></span>
							<?php echo __("View survey results","bluerabbit"); ?>
						</a>
					<?php } ?>

				</div>
			</div>
		</div>
		<div class="card-face backface">
			<div class="layer base absolute sq-full <?=$quest->quest_color; ?>-bg-400" style="background-image: url(<?= $adventure->adventure_badge; ?>);"></div>
			<div class="layer base absolute sq-full <?=$quest->quest_color; ?>-gradient-500"></div>
				<div class="layer foreground absolute perfect-center mix-blend-overlay">
					<span class="relative block border border-all rounded-max border-10 white-color sq-200 padding-20">
						<span class="icon icon-<?=$quest->quest_type;?> perfect-center font _100"></span>
					</span>
				</div>
		</div>
	</div>
</div>
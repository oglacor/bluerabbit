<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php 
	$order='quest_order, mech_level, mech_start_date, quest_title';
	$questsSummary = getQuests($adventure->adventure_id,'',"blog-post' AND quest_type!='lore");

	$questsSummary = $wpdb->get_results("SELECT quests.*, achs.achievement_name, achs.achievement_color, achs.achievement_display
	FROM {$wpdb->prefix}br_quests quests
	LEFT JOIN {$wpdb->prefix}br_achievements achs ON quests.achievement_id=achs.achievement_id
	WHERE quests.adventure_id=$adventure_id AND quest_type !='blog-post' AND quest_type!='lore' AND quests.quest_status='publish'
	ORDER BY quest_order, quest_id, mech_level, mech_start_date, quest_title
	");


	$all_qs = $wpdb->get_results("
		SELECT a.*, b.answer_id, b.answer_value, b.answer_image, b.answer_correct
		FROM {$wpdb->prefix}br_challenge_questions a
		JOIN {$wpdb->prefix}br_challenge_answers b
		ON a.quest_id = b.quest_id AND a.question_id=b.question_id AND b.answer_status='publish'
		JOIN {$wpdb->prefix}br_quests c
		ON a.quest_id = c.quest_id AND c.quest_status='publish'
		WHERE c.adventure_id=$adventure_id AND a.question_status='publish'
	");

	$questions = array();
	foreach($all_qs as $kq=>$qs){
		$questions[$qs->quest_id][$qs->question_id]['quest_id']=$qs->quest_id;
		$questions[$qs->quest_id][$qs->question_id]['question_id']=$qs->question_id;
		$questions[$qs->quest_id][$qs->question_id]['title']=$qs->question_title;
		$questions[$qs->quest_id][$qs->question_id]['image']=$qs->question_image;
		$questions[$qs->quest_id][$qs->question_id]['answers'][$qs->answer_id]['answer_id']=$qs->answer_id;
		$questions[$qs->quest_id][$qs->question_id]['answers'][$qs->answer_id]['answer_value']=$qs->answer_value;
		$questions[$qs->quest_id][$qs->question_id]['answers'][$qs->answer_id]['answer_image']=$qs->answer_image;
		$questions[$qs->quest_id][$qs->question_id]['answers'][$qs->answer_id]['answer_correct']=$qs->answer_correct;
	}
	$all_objs = $wpdb->get_results("
		SELECT objs.*
		FROM {$wpdb->prefix}br_objectives objs
		JOIN {$wpdb->prefix}br_quests quests
		ON objs.quest_id = quests.quest_id 
		WHERE objs.adventure_id=$adventure_id AND objs.objective_status='publish'
	");

	$all_steps = $wpdb->get_results("
		SELECT steps.*
		FROM {$wpdb->prefix}br_steps steps
		JOIN {$wpdb->prefix}br_quests quests
		ON steps.quest_id = quests.quest_id 
		WHERE steps.adventure_id=$adventure_id AND steps.step_status='publish'
	");

	$objectives = array();
	foreach($all_objs as $kq=>$o){
		$objectives[$o->quest_id][$o->objective_id]=$o;
	}
	$steps = array();
	foreach($all_steps as $kq=>$s){
		$steps[$s->quest_id][$s->step_id]=$s;
	}
	$step_colors = [
		'dialogue'=>'blue-bg-50',
		'jump'=> 'indigo-bg-50',
		'system'=>'orange-bg-50',
		'win'=>'light-green-bg-100',
		'fail'=>'red-bg-100',
		'item-req'=>'pink-bg-50',
		'item-grab'=>'pink-bg-50',
		'path-choice'=>'purple-bg-50',
		'choose-avatar'=>'indigo-bg-50',
		'choose-nickname'=>'indigo-bg-50',
	];
?>

	
	<div class="adventure-summary">
		<h1><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-summary.png" height="50" alt=""/><?= __("Adventure Summary","bluerabbit"); ?></h1>
		<?php foreach($questsSummary as $qKey=>$q){ ?>
			<div class="summary-item type-<?= $q->quest_type;?>" id="summary-item-<?= $q->quest_id; ?>">
				<div class="summary-item-header">
					<div class="toggle-tab" onClick="activate('#summary-item-<?= $q->quest_id; ?>',0,1);"></div>
					<h2 onClick="activate('#summary-item-<?= $q->quest_id; ?>',0,1);"> <span class="icon-button sq-20 border rounded <?= $q->quest_color; ?>-bg-400"></span> <?= $q->quest_title; ?></h2>
					<ul class="pills">
						<li>ID: <strong><?= $q->quest_id; ?></strong></li>
						<li><?= __("Order","bluerabbit").": <strong>$q->quest_order</strong>"; ?></li>
						<li><?= __("Type","bluerabbit").":<strong> $q->quest_type</strong>"; ?></li>
						<li class="green-bg-400">
							<a href="<?= get_bloginfo('url')."/new-$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>"><?= __("Edit","bluerabbit"); ?></a>
						</li>
						<li class="amber-bg-400">
							<a href="<?= get_bloginfo('url')."/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>"><?= __("View","bluerabbit"); ?></a>
						</li>
						<li class="metric"><?= "$xp_label : $q->mech_xp"; ?></li>
						<li class="metric"><?= "$bloo_label : $q->mech_bloo"; ?></li>
						<li class="metric"><?= "$ep_label : $q->mech_ep"; ?></li>
						<li class="metric"><?= __("Paths", "bluerabbit"); ?> : <?= $q->achievement_name ? $q->achievement_name : 'All paths'; ?></li>
					</ul>
				</div>
				<div class="summary-item-base-mechanics">
					<?php if($q->quest_type == 'quest'){ ?>
						<ul>
							<li><?= __("Minimum Words","bluerabbit"); ?>: <?= $q->mech_min_words; ?></li>
							<li><?= __("Minimum Links","bluerabbit"); ?>: <?= $q->mech_min_links; ?></li>
							<li><?= __("Minimum Images","bluerabbit"); ?>: <?= $q->mech_min_images; ?></li>
						</ul>
					<?php } ?>
					<?php if($q->quest_type == 'challenge'){ ?>
						<ul>
							<li><?= __("Free attempts","bluerabbit").": <strong>".$q->mech_free_attempts."</strong>"; ?></li>
							<li><?= __("Attempt Cost","bluerabbit").": <strong>".$q->mech_attempt_cost."</strong>"; ?></li>
							<li><?= __("Display","bluerabbit").": <strong>".$q->mech_questions_to_display."</strong>"; ?></li>
							<li><?= __("To win","bluerabbit").": <strong>".$q->mech_answers_to_win."</strong>"; ?></li>
							<li><?= __("Time","bluerabbit").": <strong>".$q->mech_time_limit."</strong>"; ?></li>
							<li><?= __("Total Questions","bluerabbit").": <strong>".count($questions[$q->quest_id])."</strong>"; ?></li>
						</ul>
					<?php } ?>
					<?php if($q->quest_type == 'survey'){ ?>
						<?php $sData = getSurvey($q->quest_id);
							$survey_qs = $sData['questions'];
						?>
						<ul>
							<li><?= __("Total Questions","bluerabbit").": <strong>".count($survey_qs)."</strong>"; ?></li>
						</ul>
					<?php } ?>
				</div>
				<?php if($q->quest_success_message || $q->quest_content){ ?>
					<div class="summary-item-quest-instructions">
						<?php if($q->quest_content){ ?>
							<div class="instruction">
								<h3>Content / Instructions</h3>
								<div class="entry">
									<?= apply_filters('the_content',$q->quest_content); ?>
								</div>
							</div>
						<?php } ?>
						<?php if($q->quest_success_message){ ?>
							<div class="instruction">
								<h3>Success Message</h3>
								<div class="entry">
									<?= apply_filters('the_content',$q->quest_success_message); ?>
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
				<div class="summary-item-quest-content">
					<?php if($q->quest_type == 'quest'){ ?>
						<?php if($steps[$q->quest_id]){ ?>
							<h4><?= __("Quest steps","bluerabbit"); ?>:</h4>
							<ul>
							<?php foreach($steps[$q->quest_id] as $sKey=>$ss){?>
								<li class="margin-5 relative block padding-10">
									<div class="background layer black-bg opacity-60 sq-full absolute"></div>
									<div class="layer base relative">
										<div class="objective_content padding-5 <?= $step_colors[$s->step_type]; ?>"><?= apply_filters('the_content',$ss->step_content); ?></div>
									</div>
								</li>
							<?php } ?>
							</ul>
						<?php } ?>
					<?php }elseif($q->quest_type =='challenge'){ ?>
						<h4><?= __("Challenge Questions","bluerabbit"); ?>:</h4>
						<ul>
						<?php foreach($questions[$q->quest_id] as $qKey=>$qq){?>
							<li class="margin-5 relative block padding-10">
								<div class="background layer black-bg opacity-60 sq-full absolute"></div>
								<div class="layer base relative">
								<h3><?= $qq['title']; ?></h3>
								<?php if($qq['image']){ ?><img src="<?= $qq['image']; ?>" class="w-150"><?php } ?>
									<ul>
										<?php foreach($qq['answers'] as $aKey=>$aa){ ?>
										<li class="margin-5 padding-10 inline-block border rounded-8 white-color <?= $aa['answer_correct'] ? 'green-bg-400' : 'red-bg-400'; ?>">
											<h4><?= $aa['answer_value']; ?></h3>
											<?php if($aa['answer_image']){ ?><img src="<?= $aa['answer_image']; ?>" class="w-150"><?php } ?>
										</li>
										<?php } ?>
									</ul>
								</div>
							</li>
						<?php } ?>
						</ul>
					<?php }elseif($q->quest_type =='survey'){ ?>
						<h4><?= __("Survey Questions","bluerabbit"); ?>:</h4>
						<ul>
						<?php foreach($survey_qs as $qKey=>$qq){?>
							<li class="margin-5 relative block padding-10">
								<h2 class="font _24 w600 padding-5"><?php echo $qq['text']; ?></h2>
								<?php if($qq['survey_question_description']){ ?>
									<div class="font _14 padding-5"><?php echo $qq['survey_question_description']; ?></div>
								<?php } ?>
								<?php if($qq['image']) { ?>
									<div class="question-image">
										<img src="<?php echo $qq['image']; ?>">
									</div>
								<?php } ?>
								<ul>
									<?php foreach($qq['options'] as $oKey=>$oo){ ?>
										<li class="margin-5 padding-10 inline-block border rounded-8 white-color">
											<?php if($oo['image']) { ?>
												<img src="<?php echo $oo['image']; ?>" width="100">
											<?php }?>
											<?php if($oo['text']) { ?>
												<div class="font _18 w500 padding-10"><?php echo $oo['text']; ?></div>
											<?php }?>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php } ?>
						</ul>
					<?php }elseif($q->quest_type =='mission'){ ?>
						<h4>Objectives:</h4>
						<ul>
						<?php foreach($objectives[$q->quest_id] as $oKey=>$oo){?>
							<li class="margin-5 relative block padding-10">
								<div class="background layer black-bg opacity-60 sq-full absolute"></div>
								<div class="layer base relative">
									<div class="objective_content padding-5 teal-bg-900"><?= apply_filters('the_content',$oo->objective_content); ?></div>
									<h3 class="block padding-5 teal-bg-400 white-color">Answer: <strong><?= $oo->objective_keyword; ?></strong></h3>
									<?php if($oo->objective_success_message){ ?>
										<div class="objective_success_message padding-5 layer base relative brown-bg-900">
											<h3 class="font _18 w900 uppercase">Success Message:</h3>
											<?= apply_filters('the_content',$oo->objective_success_message); ?>
										</div>
									<?php } ?>
								</div>
							</li>
						<?php } ?>
						</ul>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
	
<?php include (get_stylesheet_directory() . '/footer.php'); ?>






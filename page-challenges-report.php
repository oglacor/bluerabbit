<?php include (get_stylesheet_directory() . '/header.php'); ?>

<div class="container boxed max-w-1200">
<?php 
	$quest_id=$_GET['questID'];
	$c = $wpdb->get_row(" SELECT a.*, b.achievement_name, b.achievement_color
	FROM {$wpdb->prefix}br_quests a 
	LEFT JOIN {$wpdb->prefix}br_achievements b
	ON a.achievement_id = b.achievement_id
	WHERE a.adventure_id=$adventure_id AND a.quest_type='challenge' AND a.quest_status='publish' AND a.quest_id=$quest_id ORDER BY a.achievement_id ASC, a.mech_level ASC, a.quest_order ASC ");
	
	if($c){
	$all_qs = $wpdb->get_results("
		SELECT a.*, b.answer_id, b.answer_value, b.answer_image, b.answer_correct
		FROM {$wpdb->prefix}br_challenge_questions a
		JOIN {$wpdb->prefix}br_challenge_answers b
		ON a.quest_id = b.quest_id AND a.question_id=b.question_id AND b.answer_status='publish'
		JOIN {$wpdb->prefix}br_quests c
		ON a.quest_id = c.quest_id AND c.quest_status='publish'
		WHERE c.adventure_id=$adventure_id AND a.question_status='publish' AND a.quest_id=$quest_id 
	");

	$questions = array();
	foreach($all_qs as $kq=>$qs){
		$questions[$qs->question_id]['id']=$qs->quest_id;
		$questions[$qs->question_id]['question_id']=$qs->question_id;
		$questions[$qs->question_id]['title']=$qs->question_title;
		$questions[$qs->question_id]['image']=$qs->question_image;
		$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_id']=$qs->answer_id;
		$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_value']=$qs->answer_value;
		$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_image']=$qs->answer_image;
		$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_correct']=$qs->answer_correct;
	}
?>
	
	<div class="card">
			<div class="body-ui">
				<div class="highlight <?php echo $c->achievement_color ? $c->achievement_color : "brown"; ?>-bg-800 padding-10 page-break text-center">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400 icon-lg">
							<span class="icon icon-challenge"></span>
						</span>
						<span class="icon-content white-color">
							<span class="line font _36 w300"> <?php echo $c->quest_title; ?> </span>
							<span class="line fon _18 w600 <?php echo $c->achievement_color; ?>-400">
								<?php echo $c->achievement_name ? __("Track","bluerabbit").": $c->achievement_name" : "Challenge"; ?>
							</span>
						</span>
					</div>
				</div>
				<div class="highlight padding-20 brown-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400"><span class="icon icon-challenge"></span></span>
						<span class="icon-content text-left">
							<span class="line font _24 w900"><?php _e("Results by Question","bluerabbit"); ?></span>
							<span class="line font _16 w100"><?php _e("The numbers below each answer represent the number of times that answer was chosen over the total of times shown ","bluerabbit"); ?></span>
						</span>
						<span class="icon-content text-left">
							<button class="form-ui blue-grey-bg-400" onClick="$('#simple-table').toggleClass('hidden')"><span class="icon icon-list"></span> <?= __("Show simple table","bluerabbit"); ?></button>
						</span>
					</span>
				</div>
				
			<div id="simple-table" class="hidden">
				<div class="highlight blue-grey-bg-800 padding-10 page-break text-center">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400 icon-lg">
							<span class="icon icon-challenge"></span>
						</span>
						<span class="icon-content white-color">
							<span class="line font _24 w300"> <?php _e('Quick Export Table',"bluerabbit");?> </span>
							<span class="line fon _14 w600">
								<?php echo __("Just copy and paste","bluerabbit"); ?>
							</span>
						</span>
					</div>
				</div>
				<div class="content">
					<table class="white-bg">
						<tbody>
							<tr>
								<td><?php echo $c->quest_title; ?></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<?php foreach($questions as $qKey=>$question){ ?>
									<?php if($question['id'] == $c->quest_id){ ?>

										<?php
										  $total_attempts = $wpdb->get_row("SELECT COUNT(*) as total_answers FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE question_id={$question['question_id']}"); ?>
											<tr>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
											</tr>
											<tr>
												<td><?php echo $question['title']; ?></td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
											</tr>
											<tr>
												<td>Option</td>
												<td>Attempts</td>
												<td>Total Answers</td>
												<td>Percentage</td>
												<td>Correct?</td>
											</tr>
										<?php foreach($question['answers'] as $option) { ?>
											<tr>

												<?php
													$a_count = $wpdb->get_row("SELECT COUNT(*) as total_answers FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE answer_id={$option['answer_id']}");
												?>

												<td class="icon-content"> <?php echo $option['answer_value'] ; ?> </td>
												<td><?php echo "$a_count->total_answers" ; ?></td>  
												<td><?php echo $total_attempts->total_answers; ?></td>  
												<td><?php echo round($a_count->total_answers / $total_attempts->total_answers *100)."%" ; ?></td>  
												<?php if($option['answer_correct'] > 0){ ?>
													<td>[C]</td>
												<?php }else{ ?>
													<td>&nbsp;</td>
												<?php } ?>
											</tr>
										<?php }	?>
										</tbody>
									<?php } ?>
							<?php } ?>
						</table>
					</div>
				</div>
				
				<?php foreach($questions as $qKey=>$question){ ?>
					<div class="highlight orange-bg-50 padding-10">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  brown-bg-300 font w900 _24"> ? </span>
							<span class="icon-content">
								<span class="line font _14 w100"><?php echo __("Question ID","bluerabbit")."#$qKey"; ?></span>
								<span class="line font _24 w100"><?php echo $question['title']; ?></span>
							</span>
						</span>
					</div>
					<div class="highlight padding-10 grey-bg-50">
						<?php if($question['id'] == $c->quest_id){ ?>
						
							<?php
							  $total_attempts = $wpdb->get_row("SELECT COUNT(*) as total_answers FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE question_id={$question['question_id']}"); ?>

							<?php foreach($question['answers'] as $option) { ?>
								<span class="icon-group">
									
									<?php
										$a_count = $wpdb->get_row("SELECT COUNT(*) as total_answers FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE answer_id={$option['answer_id']}");
									?>
									<?php if($option['answer_correct'] > 0){ ?>
										<span class="icon-button font _24 sq-40  green-bg-400">
											<span class="icon-check icon"></span>
										</span>
									<?php }else{ ?>
										<span class="icon-button font _24 sq-40  red-bg-400">
											<span class="icon-cancel icon"></span>
										</span>
									<?php } ?>
									
									<span class="icon-content">
										<span class="line font _24"> <?php echo $option['answer_value'] ; ?> </span>
										<span class="line font _18">
											<?php echo "$a_count->total_answers / $total_attempts->total_answers" ; ?> :  
											<strong><?php echo round($a_count->total_answers / $total_attempts->total_answers *100)."%" ; ?></strong>
										</span>
										
									</span>
								</span>
							<?php }	?>
						<?php } ?>
					</div>
				<?php } ?>
				<?php $attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_attempts WHERE quest_id=$c->quest_id ORDER BY player_id, attempt_date"); ?>
				<div class="highlight padding-20 brown-bg-50 text-center">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  brown-bg-400"><span class="icon icon-challenge"></span></span>
						<span class="icon-content  text-left">
							<span class="line font _24 w900"><?php _e("Results by Attempt","bluerabbit"); ?></span>
							<span class="line font _14 w100 "><?php echo __("Total Attempts","bluerabbit").": ".count($attempts);?></span>
						</span>
					</span>
				</div>
				<div class="content">
					<?php $success = 0; $failure = 0; ?>
					<?php 
						if($c->mech_questions_to_display > 0){
							$totalqs = $c->mech_questions_to_display;
						}else{
							$totalqs = count($questions);
						}
					?>
					<?php $att_count=0; ?>
					
					
					<table class="table compact text-center" width="100%">
						<thead class="white-color">
							<tr>
								<td><?php _e("Attempt ID","bluerabbit"); ?></td>
								<td><?php _e("Player","bluerabbit"); ?></td>
								<td><?php _e("Answers","bluerabbit"); ?></td>
								<td><?php _e("Grade","bluerabbit"); ?></td>
								<td><?php _e("Date","bluerabbit"); ?></td>
								<td><?php _e("Status","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php $pID=0; ?>
							<?php $attemptsByPlayer=0; ?>
							<?php foreach($attempts as $key=>$att){ ?>
								<?php 
									$attemptsByPlayer++;
									if($pID != $att->player_id){
										$pID = $att->player_id; 
										$attemptsLine = $attemptsByPlayer;
										$attemptsByPlayer=0;
										if($bg_color =='blue-grey-bg-200'){
											$bg_color = 'blue-grey-bg-50';
										}else{
											$bg_color='blue-grey-bg-200';
										}
									}
								?>
								<?php if($attemptsLine > 0){ ?>
									<tr class="attempt green-bg-50 blue-grey-900 font w900">

										<td colspan="6" class="text-center"> <?=$attemptsLine; ?> </td>
									</tr>
									<?php $attemptsLine = 0; ?>
								<?php } ?>
								<tr class="attempt <?php echo $bg_color; ?>">

									<td> <?php echo $att->attempt_id; ?> </td>
									<td> <?php echo $att->player_id; ?> </td>
									<?php $att_answers = $att->attempt_answers ? $att->attempt_answers : 0; ?>
									<td><?php echo "<strong>$att_answers/$totalqs</strong>";?></td>
									<td><?php echo $att->attempt_grade ? $att->attempt_grade : 0;?></td>
									<td><?php echo date('D, M jS, Y H:i:s',strtotime($att->attempt_date));?></td>
									<td>
										<?php if($att->attempt_status=='success'){ ?>
											<?php $success++; ?>
											<span class="icon-button font _24 sq-40  icon-sm green-bg-400">
												<span class="icon icon-check"></span>
											</span>
										<?php }else{ ?>
											<?php $failure++; ?>
											<span class="icon-button font _24 sq-40  icon-sm red-bg-400">
												<span class="icon icon-cancel"></span>
											</span>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<div class="highlight padding-20 deep-purple-bg-50 text-center">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  deep-purple-bg-400"><span class="icon icon-skill"></span></span>
						<span class="icon-content">
							<span class="line font _24 w900"><?php echo count($attempts); ?></span>
							<span class="line font _14 w100 condensed"><?php _e("Total Attempts","bluerabbit"); ?></span>
						</span>
					</span>
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  light-green-bg-400"><span class="icon icon-check"></span></span>
						<span class="icon-content">
							<span class="line font _24 w900"><?php echo $success; ?></span>
							<span class="line font _14 w100 condensed"><?php _e("Successful Attempts","bluerabbit"); ?></span>
						</span>
					</span>
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-cancel"></span></span>
						<span class="icon-content">
							<span class="line font _24 w900"><?php echo $failure; ?></span>
							<span class="line font _14 w100 condensed"><?php _e("Failed Attempts","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
		</div>
	</div>
	<?php }else{ ?>	
	 <h1 class="font _48 text-center padding-20 w900"><?php _e("Challenge not found!","bluerabbit"); ?></h1>
	<?php } ?>	
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>






<?php include (get_stylesheet_directory() . '/header.php'); ?>
.<?php if($adventure){ ?>
	<?php $survey_data = getSurveyResults($_GET['questID']); ?>
	<?php if($survey_data){ ?>
		<?php 
		$survey_id = $_GET['questID'];
		$s = $survey_data['survey'];
		$qs = $survey_data['questions'];
		$answers = $survey_data['answer_values']; 
		?>
		<div class="container boxed max-w-1200">
			<div class="card">
				<div class="w-full h-250 relative  fluid" style="background-image: url(<?= $s->mech_badge; ?>);">
					<div class="spacer fixed-250">
						<?php if($isGM){ ?>
							<div class="table">
								<div class="table-cell text-center">
									<a class="form-ui green-bg-400" href="<?= get_bloginfo('url')."/new-survey/?adventure_id=$adventure->adventure_id&questID=$s->quest_id";?>"><span class="icon icon-edit"></span> <?php _e("Edit","bluerabbit"); ?></a>
									<button class="form-ui red-bg-400" onClick="br_confirm_trd('trash',<?= $s->quest_id; ?>,'quest');"><span class=" icon icon-trash"></span> <?php _e("Trash","bluerabbit"); ?></button>
									<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>" />
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="spacer fluid">
						<div class="background teal-bg-400 opacity-60"></div>
						<div class="foreground padding-20">
							<div class="table">
								<div class="table-cell text-center">
									<span class="icon-group">
										<span class="icon-button font _24 sq-40  icon-lg teal-bg-400"><span class="icon icon-survey"></span></span>
										<span class="icon-content">
											<span class="line font _48 white-color">
												<?= $s->quest_title; ?>
											</span>
										</span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="body-ui white-bg">
					<div class="content">
						<ul class="questions">
							<?php
							$count=0;
							foreach($qs as $key=>$q){ 
								include (TEMPLATEPATH . '/survey-question-result.php');
							}
							?>
						</ul>
					</div>
					<?php if($isGM || $isNPC || $isAdmin){ ?>
						<div class="highlight padding-10 grey-bg-50">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  icon-sm teal-bg-400"><span class="icon icon-star"></span></span>
								<span class="icon-content">
									<span class="line font _24 teal-800"><?php _e("Rating Questions","bluerabbit"); ?></span>
								</span>
							</span>
						</div>
						<div class="content">
							<table class="table compact">
								<thead>
									<tr class="text-center">
										<td>ID</td>
										<td><?php _e("Question","bluerabbit"); ?></td>
										<td>N/A <span class="icon icon-star"></td>
										<td>1 <span class="icon icon-star"></td>
										<td>2 <span class="icon icon-star"></td>
										<td>3 <span class="icon icon-star"></td>
										<td>4 <span class="icon icon-star"></td>
										<td>5 <span class="icon icon-star"></td>
										<td>6 <span class="icon icon-star"></td>
										<td>7 <span class="icon icon-star"></td>
										<td>8 <span class="icon icon-star"></td>
										<td>9 <span class="icon icon-star"></td>
										<td>10 <span class="icon icon-star"></td>
										<td>Total</td>
									</tr>
								</thead>
								<tbody>
									<?php
										$count=0;
										foreach($qs as $key=>$q){ 
											if($q['survey_question_type']=='rating'){
												include (TEMPLATEPATH . '/survey-question-result-row-rating.php');
											}
										}
									?>
								</tbody>
							</table>
						</div>
						<div class="highlight padding-10 grey-bg-50 text-center">
							<form action="<?= get_bloginfo('template_directory').'/survey-results-download.php'; ?>" method="post">
								<button type="submit" class="form-ui orange-bg-400 font main w400">
									<?php _e("Download Rating Questions CSV","bluerabbit"); ?>
								</button>
								<?php 
								foreach($qs as $key=>$q){ 
									if($q['survey_question_type']=='rating'){ ?>
										<input type="hidden" name="survey_data[<?= $key; ?>][track]" value="<?=  $s->achievement_name; ?>">
										<input type="hidden" name="survey_data[<?= $key; ?>][id]" value="<?= $key; ?>">
										<input type="hidden" name="survey_data[<?= $key; ?>][survey_question_description]" value="<?= $q['survey_question_description']; ?>">
										<input type="hidden" name="survey_data[<?= $key; ?>][question]" value="<?= $q['text']; ?>">
										<?php 
										$rating=array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0);
										foreach($answers[$key]['values'] as $answer){
											$rating[$answer]++;
										} 
										?>	
										<?php foreach ($rating as $rkey=>$r){ ?>

											<input type="hidden" name="survey_data[<?= $key; ?>][ratings][<?= $rkey; ?>]" value="<?= $r; ?>">
										<?php } ?>
										<input type="hidden" name="survey_data[<?= $key; ?>][total]" value="<?= array_sum($rating); ?>">
								
									<?php }
								}

								?>
								
							</form>
						</div>
					<?php } ?>
				</div>
				<div class="footer-ui grey-bg-800 text-center">
					
					<a class="form-ui blue-bg-400" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure_id"; ?>">
						<?php _e('Home page','bluerabbit'); ?>
					</a>
					<a class="form-ui green-bg-400" href="<?= get_bloginfo('url')."/survey/?questID=$survey_id&adventure_id=$adventure_id"; ?>">
						<?php _e('View My Results','bluerabbit'); ?>
					</a>
				</div>
			</div>
		</div>
	<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php } ?>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
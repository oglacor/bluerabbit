<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php 
	$questID = isset($_GET['questID']) ? $_GET['questID'] : NULL ;
	$quests = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id ORDER BY mech_level ASC, quest_order ASC ");
	$items = getItems($adventure->adventure_id);
	$achievements = getAchievements($adventure->adventure_id);
	$paths = getAchievements($adventure->adventure_id, 'path|rank');
	if($questID && $use_challenges){
		foreach($quests as $q){
			if($q->quest_id == $questID){
				$quest = $q;
				$requirements = $wpdb->get_results("
				SELECT  
				b.req_object_id, b.req_type
				FROM {$wpdb->prefix}br_quests a
				LEFT JOIN {$wpdb->prefix}br_reqs b
				ON a.quest_id = b.quest_id
				WHERE a.quest_id=$questID AND a.quest_status='publish'
				");
				$reqs=array();
				foreach($requirements as $r){
					if($r->req_type=='quest'){
						$reqs['quests'][]=$r->req_object_id;
					}else if ($r->req_type=='item'){
						$reqs['items'][]=$r->req_object_id;
					}
				}
				if($q->quest_type == 'challenge'){
					$all_qs = $wpdb->get_results("
					SELECT a.*, b.answer_id, b.answer_value, b.answer_image, b.answer_correct
					FROM {$wpdb->prefix}br_challenge_questions a
					LEFT JOIN {$wpdb->prefix}br_challenge_answers b
					ON a.quest_id = b.quest_id AND a.question_id=b.question_id AND b.answer_status='publish'
					WHERE a.quest_id=$questID AND a.question_status='publish'
					");

					$questions = array();
					foreach($all_qs as $kq=>$qs){
						$questions[$qs->question_id]['title']=$qs->question_title;
						$questions[$qs->question_id]['image']=$qs->question_image;
						$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_id']=$qs->answer_id;
						$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_value']=$qs->answer_value;
						$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_image']=$qs->answer_image;
						$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_correct']=$qs->answer_correct;
					}

				}
			}
		}
	}
if($isAdmin || $isGM){
	$adventures = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_adventures a
		JOIN {$wpdb->prefix}br_player_adventure b 
		ON a.adventure_id=b.adventure_id
		WHERE a.adventure_status='publish' AND (a.adventure_owner=$current_user->ID OR (b.player_id=$current_user->ID AND b.player_adventure_status='in' AND b.player_adventure_role='gm')) GROUP BY a.adventure_id ORDER BY a.adventure_title 
	"); 

}
?>
<div class="dashboard">
	<div class="dashboard-sidebar grey-bg-800 sticky padding-10">
		<div class="tabs-buttons" id="tab-group-buttons">
			<ul class="margin-0 padding-0">
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden active" id="general-tab-button" onClick="switchTabs('#tab-group','#general');">
						<span class="icon icon-tools foreground relative"></span>
						<span class="foreground relative"><?= __("General Settings","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="challenge-mechanics-tab-button" onClick="switchTabs('#tab-group','#challenge-mechanics');">
						<span class="icon icon-config foreground relative"></span>
						<span class="foreground relative"><?= __("Mechanics","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="content-tab-button" onClick="switchTabs('#tab-group','#content');">
						<span class="icon icon-document foreground relative"></span>
						<span class="foreground relative"><?= __("Content","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="challenge-questions-tab-button" onClick="switchTabs('#tab-group','#challenge-questions');">
						<span class="icon icon-question foreground relative"></span>
						<span class="foreground relative"><?= __("Questions","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="advanced-options-tab-button" onClick="switchTabs('#tab-group','#advanced-options');">
						<span class="icon icon-config foreground relative"></span>
						<span class="foreground relative"><?= __("Advanced Options","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block text-center padding-5">
					<?php if(isset($paths['publish'])){ ?>
						<select id="the_achievement_id" class="form-ui">
							<option value="0"  <?php if(!isset($quest->achievement_id)){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
							<?php foreach($paths['publish'] as $a){ ?>
								<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if(isset($quest) && $quest->achievement_id == $a->achievement_id){ echo 'selected'; }?> ><?php echo $a->achievement_name; ?></option>
							<?php } ?>
						</select>
					<?php }else{ ?>
						<input id="the_achievement_id" type="hidden" value="0">
						<input class="form-ui" value="<?php _e('All Paths','bluerabbit'); ?>" disabled>
					<?php } ?>
				</li>
				<li class="block">
					<h4 class="white-color padding-5 text-center font _18 condensed"><?= __("Status","bluerabbit"); ?></h4>
				</li>
				<li class="block">
					<select id="the_quest_status" class="form-ui">
						<option value="publish" <?php if(!isset($quest) || $quest->quest_status == 'publish'){ echo 'selected'; }?>><?php _e('Publish','bluerabbit'); ?></option>
						<option value="draft" <?php if(isset($quest) && $quest->quest_status == 'draft'){ echo 'selected'; }?>><?php _e('Draft','bluerabbit'); ?></option>
						<option value="trash" <?php if(isset($quest) && $quest->quest_status == 'trash'){ echo 'selected'; }?>><?php _e('Trash','bluerabbit'); ?></option>
					</select>
				</li>
				<li class="block text-center">
					<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>"/>
					<input type="hidden" id="delete-question-nonce" value="<?= wp_create_nonce('br_delete_question_nonce'); ?>"/>
					<input type="hidden" id="delete-option-nonce" value="<?= wp_create_nonce('br_delete_option_nonce'); ?>"/>
					<button id="submit-button" type="button" class="form-ui green-bg-400 w-full" onClick="updateQuest();">
						<span class="icon icon-check"></span>
						<?= ($adventure_id && $questID) ? __("Update Challenge","bluerabbit") : __("Create Challenge","bluerabbit"); ?>
					</button>
				</li>
				<li class="block text-center">
					<a class="form-ui red-bg-400 font _14" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
						<span class="icon icon-xs icon-cancel"></span><?php _e('Cancel','bluerabbit'); ?><br>
					</a>
				</li>
				<?php if(isset($quest)){ ?>
					<li class="block text-center padding-10"> </li>
					<li class="block text-center relative">
						<button class="form-ui black-bg white-color" id="tutorial-button-duplicate" onClick="showOverlay('#list-of-adventures');">
							<span class="icon icon-infinite"></span> <?= __("Duplicate Challenge","bluerabbit"); ?>
						</button>
						<div class="confirm-action overlay-layer red-bg-400" id="list-of-adventures">
							<span class="line font _14 w900 white-color"><?php _e('Select destination','bluerabbit'); ?></span>
							<select class="form-ui" id="adventure_target">
								<?php foreach($adventures as $c){ ?>
									<option value="<?= $c->adventure_id;?>">
										<?= $c->adventure_id == $adventure->adventure_id ? __("Same adventure","bluerabbit") : $c->adventure_title;?>
									</option>
								<?php } ?>
							</select>
							<br>
							<button class="form-ui red-A400 white-bg" id="tutorial-duplicate-button" onClick="duplicateQuest(<?= $quest->quest_id; ?>);">
								<span class="icon icon-infinite"></span>
								<?php _e("Duplicate","bluerabbit");?>
							</button>
							<button class="form-ui grey-bg-600 white-color" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
								<?php _e("Cancel","bluerabbit");?>
							</button>
							<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>"/>
						</div>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="dashboard-content white-bg">
		<div class="highlight padding-10 pink-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-challenge"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800">
						<?= isset($quest) ? __("Edit Challenge","bluerabbit") : __("New Challenge","bluerabbit"); ?>
					</span>
					<input type="hidden" id="the_quest_type" value="challenge">
					<input type="hidden" id="the_quest_id" value="<?= $questID; ?>">
					<input type="hidden" id="the_challenge_id" value="<?= $questID; ?>">
					<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : count($quests) ; ?>">
				</span>
			</span>
		</div>
		<div class="tabs" id="tab-group">
			<div class="active tab max-w-900 padding-10" id="general">
				<div class="highlight padding-10 grey-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-challenge"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800"><?php _e("General Settings","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
				<table class="table w-full" cellpadding="0">
					<thead>
						<tr class="font _12 grey-600">
							<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
							<td><?php _e('Value','bluerabbit'); ?></td>
						</tr>
					</thead>
					<tbody class="font _16">
						<tr>
							<td class="text-right w-150"><?php _e('Name','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<label class="light-blue-bg-800 font w900"><span class="icon icon-challenge"></span></label>
									<input class="form-ui font _30 w-full" placeholder="<?= __("Challenge Title","bluerabbit"); ?>" type="text" value="<?= isset($quest) ? $quest->quest_title : NULL ; ?>" id="the_quest_title">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Color','bluerabbit'); ?></td>
							<td>
								<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
									<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : 'red' ; ?>
									<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the_quest_color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Icon','bluerabbit'); ?></td>
							<td>
								<div class="highlight padding-10 grey-bg-200" id="tutorial-icon-select">
									<?php $selected_icon = isset($quest->quest_icon) ? $quest->quest_icon : 'challenge' ; ?>
									<input id="the_quest_icon" class="icon-selected" type="hidden" value="<?= $selected_icon; ?>">
									<?php include (TEMPLATEPATH . '/icon-select.php'); ?>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right v-top">
								<span class="font _16 block"><?= __("Main image","bluerabbit");?></span>
								<span class="font _12 block red-500">
									<?php _e("Required","bluerabbit"); ?>
								</span>
							</td>
							<td>
								<div class="gallery">
									<div class="gallery-item setting">
										<div class="background" style="background-image: url(<?= isset($quest) ? $quest->mech_badge : ""; ?>);" onClick="showWPUpload('the_quest_badge');" id="the_quest_badge_thumb"></div>
										<div class="gallery-item-options relative">
											<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_quest_badge');"><span class="icon icon-image"></span></button>
											<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_quest_badge');"> <span class="icon icon-trash"></span> </button>
											<input type="hidden" id="the_quest_badge" value="<?= isset($quest) ? $quest->mech_badge : ""; ?>"/>
										</div>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="tab max-w-900 padding-10" id="challenge-mechanics">
				<!-- BASE QUEST MECH COMPONENT -->
				<?php include (get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>
				<div class="highlight padding-10 grey-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-challenge"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800"><?php _e("Challenge Mechanics","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
				<table class="table w-full" cellpadding="0">
					<thead>
						<tr class="font _12 grey-600">
							<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
							<td><?php _e('Value','bluerabbit'); ?></td>
						</tr>
					</thead>
					<tbody class="font _16">
						<tr>
							<td class="text-right w-150"><?php _e('Time Limit','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input type="number" class="form-ui" id="the_quest_time_limit" value="<?= isset($quest->mech_time_limit) ? $quest->mech_time_limit : ""; ?>">
									<label class="pink-bg-400 font _18 condensed w900 uppercase"><?php _e('seconds','bluerabbit'); ?></label>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Questions to display','bluerabbit'); ?></td>
							<td>
									<input type="number" class="form-ui"id="the_quest_questions_to_display" value="<?= isset($quest->mech_questions_to_display) ? $quest->mech_questions_to_display : NULL ; ?>">
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Answers to win','bluerabbit'); ?></td>
							<td>
									<input type="number" class="form-ui"id="the_quest_answers_to_win" value="<?= isset($quest->mech_answers_to_win) ? $quest->mech_answers_to_win : NULL ; ?>">
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Max Chances','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input type="number" min="1" class="form-ui"id="the_quest_max_attempts" value="<?= isset($quest->mech_max_attempts) ? $quest->mech_max_attempts : NULL ; ?>">
									<label class="grey-bg-400 black-color font _12"><?php _e('zero for infinite','bluerabbit'); ?></label>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Free Attempts','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input type="number" class="form-ui"id="the_quest_free_attempts" value="<?= isset($quest->mech_free_attempts) ? $quest->mech_free_attempts : NULL ; ?>">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Attempt Cost','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input type="number" class="form-ui"id="the_quest_attempt_cost" value="<?= isset($quest->mech_attempt_cost) ? $quest->mech_attempt_cost : NULL ; ?>">
									<label class="grey-bg-400 black-color font _12"><?php _e('after all free attempts are used','bluerabbit'); ?></label>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="tab max-w-900 padding-10" id="content">
				<!-- QUEST CONTENT COMPONENT -->
				<?php include (get_stylesheet_directory() . '/component-quest-content.php'); ?>
			</div>
			<div class="tab max-w-900 padding-10" id="challenge-questions">
				<div class="highlight padding-10 grey-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-question"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800"><?php _e("Challenge Questions","bluerabbit"); ?></span>
							<span class="line font _14 grey-500"><?php _e("The more questions the more random the challenge","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
				<?php if(isset($adventure) && isset($quest)){ ?>
					<div class="w-full">
						<div class="questions accordion" id="questions">
							<?php
							if(isset($questions)){
								$qCount = 0;
								foreach($questions as $qKey=>$question){ 
									$qCount++;
									include (TEMPLATEPATH . '/challenge-question-form.php');
								}
							} 
							?>
						</div>
						<div id="questions-bottom"></div>

						<div class="highlight padding-10 indigo-bg-100 text-right">
							<button class="form-ui blue-bg-800 font condensed _18 w300" onClick="addQuestion('challenge');">
								<span class="icon icon-add"></span>
								<?php _e("Add Question","bluerabbit"); ?>
							</button>
						</div>
					</div>
				<?php }else{ ?>
					<div class="highlight padding-10 red-bg-100 text-center font _24 w400">
						<?= __('Please save the challenge before adding questions','bluerabbit'); ?>
					</div>
				<?php } ?>
			</div>
			<div class="tab max-w-900 padding-10" id="advanced-options">
				<?php 
					// Advanced Options Components
					include (TEMPLATEPATH . '/component-quest-additional-mechs.php'); 
					include (TEMPLATEPATH . '/component-quest-item-reward.php'); 
					include (TEMPLATEPATH . '/component-quest-achievement-reward.php'); 
					include (TEMPLATEPATH . '/component-quest-key-item-req.php'); 
					include (TEMPLATEPATH . '/component-quest-reqs.php'); 
					include (TEMPLATEPATH . '/component-quest-achievement-reqs.php'); 
				?>
			</div>
		</div>
	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

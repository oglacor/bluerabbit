<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if($adventure && ($isGM || $isAdmin)){
	$questID = isset($_GET['questID']) ? $_GET['questID'] : NULL;
	if(isset($questID)){
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$questID AND adventure_id=$adventure_id");
		if($quest){
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id!=$quest->quest_id ORDER BY mech_level ASC, quest_order ASC ");
		}else{
			$quest = '';
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id ORDER BY mech_level ASC, quest_order ASC ");
		}
	}
	$items = getItems($adventure->adventure_id);
	$achievements = getAchievements($adventure->adventure_id);
	$paths = getAchievements($adventure->adventure_id, 'path|rank');

	
	if(isset($quest)){
		$requirements = $wpdb->get_results("
		SELECT  
		b.req_object_id, b.req_type
		FROM {$wpdb->prefix}br_quests a
		LEFT JOIN {$wpdb->prefix}br_reqs b
		ON a.quest_id = b.quest_id
		WHERE a.quest_id=$quest->quest_id AND a.quest_status='publish'
		");
		$reqs=array();
		foreach($requirements as $r){
			if($r->req_type=='quest'){
				$reqs['quests'][]=$r->req_object_id;
			}else if ($r->req_type=='item'){
				$reqs['items'][]=$r->req_object_id;
			}else if ($r->req_type=='achievement'){
				$reqs['achievements'][]=$r->req_object_id;
			}
		}
		if($quest->quest_type == 'challenge'){
			$all_qs = $wpdb->get_results("
				SELECT *
				FROM {$wpdb->prefix}br_challenge_questions a
				LEFT JOIN {$wpdb->prefix}br_challenge_answers b
				ON a.quest_id = b.quest_id AND a.question_id=b.question_id
				WHERE a.quest_id=$quest->quest_id
			");

			$questions = array();
			foreach($all_qs as $kq=>$qs){
				$questions[$qs->question_id]['title']=$qs->question_title;
				$questions[$qs->question_id]['image']=$qs->question_image;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['value']=$qs->answer_value;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['correct']=$qs->answer_correct;
			}
		}
	}
?>

		<div class="dashboard">
			<div class="dashboard-sidebar grey-bg-800 sticky padding-10">
				<div class="tabs-buttons" id="main-tabs-buttons">
					<ul class="margin-0 padding-0">
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden active" id="general-tab-button" onClick="switchTabs('#main-tabs','#general');">
								<span class="icon icon-quest foreground relative"></span>
								<span class="foreground relative"><?= __("Basic Settings","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="mechanics-tab-button" onClick="switchTabs('#main-tabs','#mechanics');">
								<span class="icon icon-tools foreground relative"></span>
								<span class="foreground relative"><?= __("Mechanics","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="content-tab-button" onClick="switchTabs('#main-tabs','#content');">
								<span class="icon icon-document foreground relative"></span>
								<span class="foreground relative"><?= __("Content","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="boss-fight-steps-tab-button" onClick="switchTabs('#main-tabs','#boss-fight-steps');">
								<span class="icon icon-level foreground relative"></span>
								<span class="foreground relative"><?= __("Steps","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="advanced-options-tab-button" onClick="switchTabs('#main-tabs','#advanced-options');">
								<span class="icon icon-config foreground relative"></span>
								<span class="foreground relative"><?= __("Advanced","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						<?php if(isset($quest)){ ?>
						<li class="block">
							<a class="form-ui w-full relative tab-button overflow-hidden text-center blue-bg-500" href="<?= get_bloginfo('url')."/quest/?questID=$quest->quest_id&adventure_id=$quest->adventure_id"; ?>" target="_blank" class="tab-button">
								<span class="foreground relative"><?= __("View Quest","bluerabbit");?></span>
							</a>
						</li>
						<?php } ?>
						<li class="block text-center padding-5">
							<?php if(isset($paths['publish'])){ ?>
								<select id="the_achievement_id" class="form-ui">
									<option value="0"  <?php if(!isset($quest->achievement_id)){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
									<?php foreach($paths['publish'] as $a){ ?>
										<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if(isset($quest->achievement_id) && $quest->achievement_id == $a->achievement_id){ echo 'selected'; }?> ><?php echo $a->achievement_name; ?></option>
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
							<?php if(isset($quest)){ ?>
								<select id="the_quest_status" class="form-ui">
									<option value="publish" <?php if(!$quest->quest_status|| $quest->quest_status == 'publish'){ echo 'selected'; }?>><?php _e('Publish','bluerabbit'); ?></option>
									<option value="draft" <?php if($quest->quest_status == 'draft'){ echo 'selected'; }?>><?php _e('Draft','bluerabbit'); ?></option>
									<option value="trash" <?php if($quest->quest_status == 'trash'){ echo 'selected'; }?>><?php _e('Trash','bluerabbit'); ?></option>
								</select>
							<?php }else{ ?>
								<select id="the_quest_status" class="form-ui">
									<option value="publish" ><?php _e('Publish','bluerabbit'); ?></option>
									<option value="draft"><?php _e('Draft','bluerabbit'); ?></option>
									<option value="trash"><?php _e('Trash','bluerabbit'); ?></option>
								</select>
							<?php } ?>
						</li>
						
						<li class="block text-center">
							<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>"/>
							<input type="hidden" id="delete-question-nonce" value="<?= wp_create_nonce('br_delete_question_nonce'); ?>"/>
							<input type="hidden" id="delete-option-nonce" value="<?= wp_create_nonce('br_delete_option_nonce'); ?>"/>
							<button id="submit-button" type="button" class="form-ui green-bg-400 w-full" onClick="updateQuest();">
								<span class="icon icon-check"></span>
								<?= ($adventure_id && $questID) ? __("Update Quest","bluerabbit") : __("Create Quest","bluerabbit"); ?>
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
									<span class="icon icon-infinite"></span> <?= __("Duplicate Quest","bluerabbit"); ?>
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
				<div class="w-full padding-10 blue-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 blue-bg-400"><span class="icon icon-quest"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800" id="quest-title-label">
								<?= isset($quest) ? __("Edit Quest","bluerabbit")." > $quest->quest_title" : __("New Quest","bluerabbit"); ?>
							</span>
							<input type="hidden" id="the_quest_type" value="quest">
							<input type="hidden" id="the_quest_id" value="<?= isset($quest) ? $quest->quest_id : ""; ?>">
							<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : count($quests) ; ?>">
						</span>
					</span>
				</div>
				<div class="tabs" id="main-tabs">
					<div class="tab max-w-900 padding-10 active" id="general">
						<div class="highlight padding-10 grey-bg-200">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  blue-bg-400"><span class="icon icon-quest"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Basic Settings","bluerabbit"); ?></span>
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
	<label class="light-blue-bg-800 font _24"><span class="icon icon-quest"></span></label>
	<input class="form-ui font _30 w-full" type="text" placeholder="<?= __('Quest Title',"bluerabbit"); ?>" value="<?= isset($quest) ? $quest->quest_title : NULL; ?>" id="the_quest_title" onChange="$('#quest-title-label').text('Edit Quest > '+$('#the_quest_title').val());">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Autoload?','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="light-blue-bg-800 font w900"><span class="icon icon-enemy"></span></label>
											<select class="form-ui" id="the_quest_relevance">
												<option <?= isset($quest) && $quest->quest_relevance != 'autoload' ? "selected" : ""; ?> value="0"><?= __("No","bluerabbit"); ?></option>
												<option <?= isset($quest) && $quest->quest_relevance == 'autoload' ? "selected" : ""; ?> value="autoload"><?= __("Yes","bluerabbit"); ?></option>
											</select>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Color','bluerabbit'); ?></td>
									<td>
										<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
											<?php 
											$selected_color = (isset($quest) && $quest->quest_color) ? $quest->quest_color : 'red' ; 
											$object_color_id = $quest->quest_id;
											$object_type='quest';
											?>
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
											<?php $selected_icon = isset($quest) && $quest->quest_icon ? $quest->quest_icon : 'challenge' ; ?>
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
												<div class="background" style="background-image: url(<?= isset($quest) ? $quest->mech_badge : NULL; ?>);" onClick="showWPUpload('the_quest_badge');" id="the_quest_badge_thumb"></div>
												<div class="gallery-item-options relative">
													<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_quest_badge');"><span class="icon icon-image"></span></button>
													<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_quest_badge');"> <span class="icon icon-trash"></span> </button>
													<input type="hidden" id="the_quest_badge" value="<?= isset($quest) ? $quest->mech_badge : NULL; ?>"/>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="tab max-w-900 padding-10" id="mechanics">
						<!-- BASE QUEST MECH COMPONENT -->
						<?php include (get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>
						<div class="highlight padding-10 blue-bg-50">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40 blue-bg-400"><span class="icon icon-quest"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Quest Mechanics","bluerabbit"); ?></span>
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
									<td class="text-right w-150"><?php _e('Required Words','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="orange-bg-400"><span class="icon icon-edit"></span> </label>
											<input type="number" class="form-ui"id="the_quest_min_words" min="1" value="<?= isset($quest->mech_min_words) ? $quest->mech_min_words : 1; ?>">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Required Links','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="orange-bg-400"><span class="icon icon-edit"></span> </label>
										<input type="number" class="form-ui"id="the_quest_min_links" min="0" value="<?= isset($quest->mech_min_links) ? $quest->mech_min_links : 0 ; ?>">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Required Images','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="orange-bg-400"><span class="icon icon-image"></span> </label>
										<input type="number" class="form-ui"id="the_quest_min_images" min="0" value="<?= isset($quest->mech_min_images) ? $quest->mech_min_images : 0; ?>">
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
					<div class="tab max-w-1200 padding-10" id="boss-fight-steps">
						<div class="highlight padding-10 indigo-bg-400">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  white-bg">
									<span class="icon icon-progression indigo-400"></span>
								</span>
								<span class="icon-content font _24">
									<span class="line indigo-bg-400 white-color"><?php _e("Quest Steps","bluerabbit"); ?></span>
									<span class="line font _14 white-color opacity-80"><?php _e("All the steps the player must do to complete the quest","bluerabbit"); ?></span>
								</span>
							</span>
						</div>
						<?php if(isset($quest)){ ?>
							<div class="w-full" >
								<?php $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest->quest_id AND adventure_id=$quest->adventure_id AND step_status='publish' ORDER BY step_order, step_id"); ?>
								<?php if(!$steps){ ?>
									<h3 id="no-steps-label" class="text-center font _18 padding-5 grey-500">- <?= __("No steps","bluerabbit"); ?> -</h3>
								<?php } ?>
									<table class="steps-list table w-full" id="steps-list-table">
										<thead>
											<tr>
												<td class="text-center w-50">
													<?= __("Step","bluerabbit"); ?>
												</td>
												<td class="w-150 text-center"><?= __("Type","bluerabbit"); ?></td>
												<td class=""><?= __("Title","bluerabbit"); ?></td>
												<td class="w-150 text-center"><?= __("Actions","bluerabbit"); ?></td>
											</tr>
										</thead>
										<tbody id="steps-list" class="sortable">
											<?php foreach($steps as $key=>$step){ ?>
												<?php include (TEMPLATEPATH . '/step-list-item.php'); ?>
											<?php } ?>
										</tbody>
									</table>
								<button class="form-ui half-width blue-bg-300" onClick="addStep();">
									<span class="icon icon-add"></span>
									<?= __("Add Step","bluerabbit"); ?>
								</button>
								<button class="form-ui half-width blue-bg-300" onClick="reorderSteps();">
									<span class="icon icon-rotate"></span>
									<?= __("Reorder Steps","bluerabbit"); ?>
								</button>
							</div>
						<?php }else{ ?>
							<div class="content flex" id="">
								<h3 class="text-center">- <?= __("Save the quest first","bluerabbit"); ?> -</h3>
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
	</div>
	<?php 
		$steps_json = array();
		if(isset($steps)){
			foreach($steps as $s){
				$steps_json[] = array('text'=>"$s->step_title", 'value'=>$s->step_id);
			}
		}
	?>
	<?php 
		$items_json = array();
		if(isset($items['key'])){
			foreach($items['key'] as $i){
				$items_json[] = array('text'=>"$i->item_name", 'value'=>$i->item_id);
			}
		}
	?>
	<input type="hidden" id="steps-list-values" value='<?= json_encode($steps_json); ?>'>
	<input type="hidden" id="key-items-for-opened-backpack" value='<?= json_encode($items_json); ?>'>
	<script>
		jQuery(document).ready(function($) {
			<?php if($questID){ ?>
				checkRequirements(<?= $quest->mech_level; ?>);
			 <?php }else{ ?>
				checkRequirements(1);
		   <?php } ?>
			$(document).keyup(function(e) {
				if (e.ctrlKey && e.keyCode == 13) {
				updateQuest();
				}				
			});
		});
	</script>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

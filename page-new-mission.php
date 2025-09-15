<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$quest_id = isset($_GET['questID']) ? $_GET['questID'] : NULL ;
	$quests = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND (quest_type='quest' OR quest_type='challenge' OR quest_type='survey' OR quest_type='mission') ORDER BY mech_level ASC, quest_order ASC ");
	$items = getItems($adventure->adventure_id);
	$achievements = getAchievements($adventure->adventure_id);
	$paths = getAchievements($adventure->adventure_id, 'path|rank');
	if($quest_id){
		foreach($quests as $q){
			if($q->quest_id == $quest_id){
				$quest = $q;
				$requirements = $wpdb->get_results("
				SELECT  
				b.req_object_id, b.req_type
				FROM {$wpdb->prefix}br_quests a
				LEFT JOIN {$wpdb->prefix}br_reqs b
				ON a.quest_id = b.quest_id
				WHERE a.quest_id=$quest->quest_id AND a.quest_status='publish'
				");
                $objectives = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_objectives WHERE quest_id=$quest->quest_id AND objective_status='publish'");
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
						<span class="icon icon-settings foreground relative"></span>
						<span class="foreground relative"><?= __("Basic Settings","bluerabbit");?></span>
						<span class="active-content background blue-bg-400"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="mechanics-tab-button" onClick="switchTabs('#main-tabs','#mechanics');">
						<span class="icon icon-tools foreground relative"></span>
						<span class="foreground relative"><?= __("Mechanics","bluerabbit");?></span>
						<span class="active-content background amber-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="content-tab-button" onClick="switchTabs('#main-tabs','#content');">
						<span class="icon icon-document foreground relative"></span>
						<span class="foreground relative"><?= __("Content","bluerabbit");?></span>
						<span class="active-content background amber-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="objectives-tab-button" onClick="switchTabs('#main-tabs','#objectives');">
						<span class="icon icon-objectives foreground relative"></span>
						<span class="foreground relative"><?= __("Objectives","bluerabbit");?></span>
						<span class="active-content background purple-bg-400"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="requirements-tab-button" onClick="switchTabs('#main-tabs','#requirements');">
						<span class="icon icon-thumb-view foreground relative"></span>
						<span class="foreground relative"><?= __("Requirements","bluerabbit");?></span>
						<span class="active-content background blue-bg-400"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="advanced-options-tab-button" onClick="switchTabs('#main-tabs','#advanced-options');">
						<span class="icon icon-config foreground relative"></span>
						<span class="foreground relative"><?= __("Advanced","bluerabbit");?></span>
						<span class="active-content background orange-bg-400"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<?php if(isset($quest)){ ?>
				<li class="block">
					<a class="form-ui w-full relative tab-button overflow-hidden text-center blue-bg-500" href="<?= get_bloginfo('url')."/mission/?questID=$quest->quest_id&adventure_id=$quest->adventure_id"; ?>" target="_blank" class="tab-button">
						<span class="foreground relative"><?= __("View Mission","bluerabbit");?></span>
					</a>
				</li>
				<?php } ?>
				<li class="block text-center padding-5">
					<?php if(isset($paths['publish'])){ ?>
						<select id="the_achievement_id" class="form-ui">
							<option value="0"  <?php if(!isset($quest->achievement_id)){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
							<?php foreach($paths['publish'] as $a){ ?>
								<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if(isset($quest) && $quest->achievement_id == $a->achievement_id){ echo 'selected'; }?> class=""><?php echo $a->achievement_name; ?></option>
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
						<?= (isset($adventure_id) && isset($quest) ) ? __("Update Mission","bluerabbit") : __("Create Mission","bluerabbit"); ?>
					</button>
				</li>
				<li class="block text-center">
					<a class="form-ui red-bg-400 font _14" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
						<span class="icon icon-xs icon-cancel"></span><?php _e('Cancel','bluerabbit'); ?><br>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="dashboard-content white-bg">
		<div class="w-full padding-10 amber-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40 amber-bg-400"><span class="icon icon-mission"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800">
						<?= isset($quest) ? __("Edit Mission","bluerabbit") : __("New Mission","bluerabbit"); ?>
					</span>
					<input type="hidden" id="the_quest_type" value="mission">
					<input type="hidden" id="the_quest_id" value="<?= isset($quest) ?$quest->quest_id : NULL; ?>">
					<input type="hidden" id="the_quest_order" value="<?= isset($quest) ?$quest->quest_order : count($quests) ; ?>">
				</span>
			</span>
		</div>
		<div class="tabs" id="main-tabs">
			<div class="tab max-w-900 padding-10 active" id="general">
				<div class="highlight padding-10 grey-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  blue-grey-bg-400"><span class="icon icon-settings"></span></span>
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
									<label class="amber-bg-800 font _30 w900"><span class="icon icon-mission"></span></label>
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($quest) ? $quest->quest_title : NULL; ?>" id="the_quest_title">
									<input type="hidden" value="0" id="the_quest_guild">
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
									<?php $selected_icon = isset($quest->quest_icon) ? $quest->quest_icon : 'mission' ; ?>
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
			<div class="tab max-w-900 padding-10" id="content">
				<!-- QUEST CONTENT COMPONENT -->
				<?php include (get_stylesheet_directory() . '/component-quest-content.php'); ?>
			</div>
			<div class="tab max-w-900 padding-10" id="mechanics">
				<!-- BASE QUEST MECH COMPONENT -->
				<?php include (get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>
			</div>
			<div class="tab max-w-900 padding-10" id="objectives">
				<div class="highlight padding-10 purple-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 purple-bg-400"><span class="icon icon-objectives"></span></span>
						<span class="icon-content">
							<span class="line font _24 white-color"><?php _e("Mission Objectives","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
				<?php if(isset($quest)){ ?>
					<?php if(!$objectives){ ?>
						<h3 class="text-center">- <?= __("No objectives","bluerabbit"); ?> -</h3>
					<?php } ?>
					<table class="table" id="objectives">
						<thead class="">
							<tr>
								<td><?=__("ID","bluerabbit");?></td>
								<td><?=__("Hint","bluerabbit");?></td>
								<td><?=__("Solution","bluerabbit");?></td>
								<td><?=__("Type","bluerabbit");?></td>
								<?php if($use_encounters){ ?><td><?=__("EP","bluerabbit");?></td><?php } ?>
								<td><span class="icon icon-edit"></span></td>
								<td><span class="icon icon-trash"></span></td>
							</tr>
						</thead>
						<tbody id="">
							<?php foreach($objectives as $key=>$c){ ?>
								<?php include (TEMPLATEPATH . '/objective-row.php'); ?>
							<?php } ?>
						</tbody>
					</table>
				<?php }else{ ?>
					<h3 class="text-center">- <?= __("Save the mission first","bluerabbit"); ?> -</h3>
				<?php } ?>
				<div class="highlight grey-bg-200 white-color padding-10 text-right" id="tutorial-add-new-objective-bar">
					
					<button class="form-ui pull-left blue-grey-bg-800 default-actions relative" onClick="showOverlay('#confirm-reset-objectives');">
						<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-reset.png" width="20" class="">
						<?php _e("Reset Objectives for Players","bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer shadow text-center padding-10" id="confirm-reset-objectives">
						<button class="form-ui red-bg-400 white-color font _24" onClick="resetQuestObjectives(<?=isset($quest) ? $quest->quest_id : "";?>);">
							<span class="icon icon-warning"></span>
							<?php _e("Are you sure?","bluerabbit"); ?>
						</button>
					</div>
					<button class="form-ui teal-bg-500 default-actions" onClick="showOverlay('#new-objective-menu');">
						<span class="icon icon-add"></span>
						<?php _e("New Mission Objective","bluerabbit"); ?>
					</button>
					<div class="confirm-action overlay-layer shadow grey-bg-900 text-center padding-10" id="new-objective-menu">
						<button class="form-ui cyan-bg-400" onClick="addObjective('keyword-input');">
							<span class="icon icon-comment"></span>
							<?php _e("Type Keyword","bluerabbit"); ?>
						</button>
						<button class="form-ui teal-bg-400" onClick="addObjective('true-false');">
							<span class="icon icon-like"></span>
							<?php _e("True/False","bluerabbit"); ?>
							<span class="icon icon-dislike"></span>
						</button>
					</div>
				</div>         
			</div>
			<div class="tab" id="requirements">
				<?php 
					include (TEMPLATEPATH . '/component-quest-reqs.php'); 
					include (TEMPLATEPATH . '/component-quest-key-item-req.php'); 
					include (TEMPLATEPATH . '/component-quest-achievement-reqs.php'); 
				?>
			</div>
			<div class="tab max-w-900 padding-10" id="advanced-options">
				<?php 
					include (TEMPLATEPATH . '/component-quest-additional-mechs.php'); 
					include (TEMPLATEPATH . '/component-quest-item-reward.php'); 
					include (TEMPLATEPATH . '/component-quest-achievement-reward.php'); 
				?>
			</div>
		</div>
	</div>
</div>

	<script>
		jQuery(document).ready(function($) {
			<?php if(isset($quest)){ ?>
				checkRequirements(<?= $quest->mech_level; ?>);
			 <?php }else{ ?>
				checkRequirements(1);
		   <?php } ?>
		});
	</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

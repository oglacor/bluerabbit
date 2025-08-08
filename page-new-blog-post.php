<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

	$questID = isset($_GET['questID']) ? $_GET['questID'] : NULL ;
	if(isset($questID)){
		$quest = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$questID");
	}
	$achievements = getAchievements($adventure->adventure_id, "path");
?>			
<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 purple-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40 purple-bg-400"><span class="icon icon-story"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800">
					<?= (isset($adventure) && isset($quest)) ? __("Edit Post","bluerabbit") : __("New Post","bluerabbit"); ?>
				</span>
				<input type="hidden" id="the_quest_id" value="<?= isset($quest) ? $quest->quest_id : ""; ?>">
				<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : ""; ?>">
				<input type="hidden" id="the_quest_type" value="blog-post">
			</span>
		</span>
	</div>
	<table class="table w-full" cellpadding="0">
		<thead>
			<tr class="font _12 grey-600">
				<td class="text-right w-150"><?= __('Setting','bluerabbit'); ?></td>
				<td><?= __('Value','bluerabbit'); ?></td>
			</tr>
		</thead>
		<tbody class="font _16">
			<tr>
				<td class="text-right w-150"><?= __('Post Headline','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="purple-bg-800 font w900"><span class="icon icon-story"></span></label>
						<input class="form-ui font _30 w-full" type="text" value="<?= isset($quest) ? $quest->quest_title : ""; ?>" id="the_quest_title">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right v-top">
					<span class="font _16 block"><?= __("Post Image","bluerabbit");?></span>
					<span class="font _12 block red-500">
						<?= __("Required","bluerabbit"); ?>
					</span>
				</td>
				<td>
					<div class="gallery">
						<div class="gallery-item setting">
							<div class="background" style="background-image: url(<?= isset($quest->mech_badge) ? $quest->mech_badge : ""; ?>);" onClick="showWPUpload('the_quest_badge');" id="the_quest_badge_thumb"></div>
							<div class="gallery-item-options relative">
								<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_quest_badge');"><span class="icon icon-image"></span></button>
								<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_quest_badge');"> <span class="icon icon-trash"></span> </button>
								<input type="hidden" id="the_quest_badge" value="<?= isset($quest->mech_badge) ? $quest->mech_badge : ""; ?>"/>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Level','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="light-blue-bg-800 font w900"><span class="icon icon-level"></span></label>
						<input class="number form-ui" type="number" max="99" min="1" id="the_quest_level" value="<?= isset($quest->mech_level) ? $quest->mech_level : 1 ; ?>">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Start Date','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<?php
						if(isset($quest) && $quest->mech_start_date != "0000-00-00 00:00:00"){ 
							$pretty_start_date = date('Y/m/d H:i', strtotime($quest->mech_start_date));
						}else{
							$pretty_start_date = '';
						}
						?>
						<label class="cyan-bg-400 font w900"><span class="icon icon-calendar"></span></label>
						<input class="form-ui text-center font w600 the_start_date"  autocomplete="off" id="the_quest_start_date" value="<?= $pretty_start_date; ?>">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Display Style','bluerabbit'); ?></td>
				<td>
					<select id="the_quest_style" class="form-ui">
						<option value="text-right"  <?php if(!isset($quest->quest_style) || $quest->quest_style=='text-right'){ echo 'selected'; }?>><?= __('Text on right','bluerabbit'); ?></option>
						<option value="text-left"  <?php if(isset($quest->quest_style)  && $quest->quest_style=='text-left'){ echo 'selected'; }?>><?= __('Text on Left','bluerabbit'); ?></option>
						<option value="news-highlight"  <?php if(isset($quest->quest_style) && $quest->quest_style=='news-highlight'){ echo 'selected'; }?>><?= __('News Highlight','bluerabbit'); ?></option>
						<option value="headline"  <?php if(isset($quest->quest_style) && $quest->quest_style=='headline'){ echo 'selected'; }?>><?= __('Headline','bluerabbit'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Available for','bluerabbit'); ?></td>
				<td>
					<select id="the_achievement_id" class="form-ui" onChange="hideAchievementReward();">
						<option value="0"  <?php if(!isset($quest->achievement_id)){ echo 'selected'; }?>><?= __('All paths','bluerabbit'); ?></option>
						<?php if(isset($achievements['publish'])){ ?>
							<?php foreach($achievements['publish'] as $a){ ?>
								<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if($quest->achievement_id == $a->achievement_id){ echo 'selected'; }?> class="<?php echo $status; ?>"><?php echo $a->achievement_name; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Secondary Headline','bluerabbit'); ?></td>
				<td>
					<textarea class="form-ui grey-bg-50 border border-all blue-border-700 border-2" rows="3" maxlength="200" id="the_quest_secondary_headline"><?= isset($quest->quest_secondary_headline) ? $quest->quest_secondary_headline : ""; ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Content','bluerabbit'); ?></td>
				<td>
					<?php 
					$wp_editor_settings = array( 'editor_height'=>350);
					if(isset($quest->quest_content)){
						wp_editor( $quest->quest_content, 'the_quest_content',$wp_editor_settings); 	
					}else{
						wp_editor( "", 'the_quest_content',$wp_editor_settings); 	
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="w-full padding-5 grey-bg-100 text-right">
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_update_quest_nonce'); ?>"/>
		<a class="form-ui red-bg-400 pull-left" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id; ?>">
			<span class="icon icon-xs icon-cancel"></span><?= __('Cancel','bluerabbit'); ?><br>
		</a>

		<div class="input-group inline-table"> 
			<label class="purple-bg-400 font condensed w900 uppercase white-color"><?= __('Status',"bluerabbit"); ?></label>
			<select id="the_quest_status" class="form-ui">
				<option value="publish" <?php if(!isset($quest) || $quest->quest_status == 'publish'){ echo 'selected'; }?>><?= __('Publish','bluerabbit'); ?></option>
				<option value="draft" <?php if(isset($quest) && $quest->quest_status == 'draft'){ echo 'selected'; }?>><?= __('Draft','bluerabbit'); ?></option>
				<option value="trash" <?php if(isset($quest) && $quest->quest_status == 'trash'){ echo 'selected'; }?>><?= __('Trash','bluerabbit'); ?></option>
			</select>
		</div>
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= ($adventure && isset($quest)) ?  __('Update Post','bluerabbit') : __('Create Post','bluerabbit'); ?>
		</button>
	</div>
</div>
<?php //wp_enqueue_media();?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

	$questID = isset($_GET['questID']) ? $_GET['questID'] : NULL ;
	if(isset($questID)){ 
		$quest = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$questID AND quest_type='lore'");
	}
	$achievements = getAchievements($adventure->adventure_id, "path");
?>			
<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 purple-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40 purple-bg-400"><span class="icon icon-narrative"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800">
					<?= ($adventure && isset($quest)) ? __("Edit Resource","bluerabbit") : __("New Resource","bluerabbit"); ?>
				</span>
				<input type="hidden" id="the_quest_id" value="<?=isset($quest) ? $quest->quest_id : ""; ?>">
				<input type="hidden" id="the_quest_order" value="<?=isset($quest) ?  $quest->quest_order : ""; ?>">
				<input type="hidden" id="the_quest_type" value="lore">
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
				<td class="text-right w-150"><?= __('Resource Name','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="purple-bg-800 font w900"><span class="icon icon-narrative"></span></label>
						<input class="form-ui font _30 w-full" type="text" value="<?=isset($quest) ? $quest->quest_title : ""; ?>" id="the_quest_title">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Style','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="purple-bg-800 font w900"><span class="icon icon-tools"></span></label>
						<select class="form-ui w-full" id="the_quest_style">
							<option value="resource" <?= !isset($quest->quest_style) || ($quest->quest_style) =='resource' ? "selected" : ""; ?>><?= __("Resource","bluerabbit"); ?></option>
							<option value="article" <?= isset($quest->quest_style) && $quest->quest_style =='article' ? "selected" : ""; ?>><?= __("Article","bluerabbit"); ?></option>
						</select>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('File or Link','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="purple-bg-800 font w900"><span class="icon icon-narrative"></span></label>
						<input class="form-ui w-full" id="the_quest_secondary_headline" value="<?= isset($quest->quest_secondary_headline) ? $quest->quest_secondary_headline : "";?>">
						<label class="red-bg-400">
							<button class="form-ui red-bg-400 white-color" onClick="showWPUpload('the_quest_secondary_headline');"><?= __("Select file","bluerabbit"); ?></button>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Content','bluerabbit'); ?></td>
				<td>
					<?php 
					if($roles[0]=="administrator"){
						$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
					}else{
						$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
					}
					if(isset($quest->quest_content)){
						wp_editor( $quest->quest_content, 'the_quest_content',$wp_editor_settings);
					}else{
						wp_editor("", 'the_quest_content',$wp_editor_settings);
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="text-right v-top">
					<span class="font _16 block"><?= __("Image","bluerabbit");?></span>
				</td>
				<td>
					<?php $selected_book = isset($quest->mech_badge) ? $quest->mech_badge : ""; ?>
					<input id="the_quest_badge" type="hidden" value="<?= $selected_book; ?>">
					<?php include (TEMPLATEPATH . '/component-book-select.php'); ?>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
				<td>
					<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
						<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : ""; ?>
						<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the_quest_color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
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
				<td class="text-right w-150"><?= __('Available for','bluerabbit'); ?></td>
				<td>
					<select id="the_achievement_id" class="form-ui" onChange="hideAchievementReward();">
						<option value="0"  <?php if(!isset($quest->achievement_id)){ echo 'selected'; }?>><?= __('All paths','bluerabbit'); ?></option>
						<?php if(isset($achievements['publish'])){ ?>
							<?php foreach($achievements['publish'] as $a){ ?>
								<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if(isset($quest->achievement_id) &&$quest->achievement_id == $a->achievement_id){ echo 'selected'; }?> ><?php echo $a->achievement_name; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="w-full padding-5 grey-bg-100 text-right">
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_update_quest_nonce'); ?>"/>
		<a class="form-ui red-bg-400 pull-left" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id; ?>">
			<span class="icon icon-xs icon-cancel"></span><?= __('Cancel','bluerabbit'); ?><br>
		</a>
		<input type="hidden" id="the_quest_status" value="publish"/>
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= ($adventure && isset($quest)) ?  __('Update Resource','bluerabbit') : __('Publish Resource','bluerabbit'); ?>
		</button>
	</div>
</div>
<?php //wp_enqueue_media();?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if($adventure && ($isGM || $isAdmin || $isNPC)){
	$achievement_id = isset($_GET['achievement_id']) ? $_GET['achievement_id'] : 0;
	
	$paths = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE adventure_id=$adv_parent_id AND achievement_display!='badge' AND achievement_status='publish' AND achievement_id != $achievement_id ");
	
	$a = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE achievement_id=$achievement_id AND achievement_status='publish'");
	if(isset($a)){
		$selected_players = $wpdb->get_col("SELECT player_id FROM ".$wpdb->prefix."br_player_achievement WHERE achievement_id=$a->achievement_id AND adventure_id=$adv_child_id");
	}
	?>
		<div class="dashboard">
			<div class="dashboard-sidebar grey-bg-800 sticky padding-10">
				<div class="tabs-buttons" id="main-tabs-buttons">
					<ul class="margin-0 padding-0">
						<?php if($isGM || $isAdmin){ ?>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden active" id="general-tab-button" onClick="switchTabs('#main-tabs','#general');">
									<span class="icon icon-settings foreground relative"></span>
									<span class="foreground relative"><?= __("Settings","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden" id="achievement-codes-tab-button" onClick="switchTabs('#main-tabs','#achievement-codes');">
									<span class="icon icon-qr foreground relative"></span>
									<span class="foreground relative"><?= __("Achievement Codes","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
						<?php } ?>
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden <?= $isNPC ? "active" : ""; ?>" id="select-players-tab-button" onClick="switchTabs('#main-tabs','#select-players');">
								<span class="icon icon-players foreground relative"></span>
								<span class="foreground relative"><?= __("Players","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						<?php if($isGM || $isAdmin){ ?>
							<li class="block">
								<h4 class="white-color padding-5 text-center font _18 condensed"><?= __("Status","bluerabbit"); ?></h4>
							</li>
							<li class="block">
								<?php if(isset($a)){ ?>
									<select id="the_achievement_status" class="form-ui">
										<option value="publish" <?php if(!$a->achievement_status|| $a->achievement_status == 'publish'){ echo 'selected'; }?>><?php _e('Publish','bluerabbit'); ?></option>
										<option value="draft" <?php if($a->achievement_status == 'draft'){ echo 'selected'; }?>><?php _e('Draft','bluerabbit'); ?></option>
										<option value="trash" <?php if($a->achievement_status == 'trash'){ echo 'selected'; }?>><?php _e('Trash','bluerabbit'); ?></option>
									</select>
								<?php } else{ ?>
									<select id="the_achievement_status" class="form-ui">
										<option value="publish" selected><?php _e('Publish','bluerabbit'); ?></option>
										<option value="draft"><?php _e('Draft','bluerabbit'); ?></option>
										<option value="trash"><?php _e('Trash','bluerabbit'); ?></option>
									</select>
								<?php }?>

							</li>
							<li class="block text-center">
								<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_achievement_nonce'); ?>"/>
								<button id="submit-button" type="button" class="form-ui green-bg-400 w-full" onClick="updateAchievement();">
									<span class="icon icon-check"></span>
									<?= ($adventure && $a) ? __("Update Achievement","bluerabbit") : __("Create Achievement","bluerabbit"); ?>
								</button>
							</li>
							<li class="block text-center">
								<a class="form-ui red-bg-400 font _14" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id"; ?>">
									<span class="icon icon-xs icon-cancel"></span><?php _e('Cancel','bluerabbit'); ?><br>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</div>
			<div class="dashboard-content white-bg">
				<div class="w-full padding-10 purple-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 purple-bg-400"><span class="icon icon-achievement"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800">
								<?= (isset($adventure) && isset($a)) ? __("Edit Achievement","bluerabbit")." ".$a->achievement_name : __("New Achievement","bluerabbit"); ?>
							</span>
							<?php if(isset($a)){ ?>
								<input type="hidden" id="the_achievement_id" value="<?= $a->achievement_id; ?>">
								<input type="hidden" id="the_achievement_ref_id" value="<?= $a->ref_id; ?>">
							<?php } ?>
						</span>
					</span>
				</div>
				<div class="tabs" id="main-tabs">
					<?php if($isGM || $isAdmin){ ?>
					<div class="tab max-w-900 padding-10 active" id="general">
						<div class="highlight padding-10 grey-bg-200">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-achievement"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Achievement Settings","bluerabbit"); ?></span>
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
											<label class="purple-bg-800 font w900"><span class="icon icon-achievement"></span></label>
											<input class="form-ui font _30 w-full" type="text" value="<?= isset($a) ? $a->achievement_name : ""; ?>" id="the_achievement_name">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right v-top">
										<span class="font _16 block"><?= __("Achievement Badge","bluerabbit");?></span>
										<span class="font _12 block red-500">
											<?php _e("Required","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="gallery">
											<div class="gallery-item setting">
												<div class="background" style="background-image: url(<?= isset($a) ? $a->achievement_badge : ""; ?>);" onClick="showWPUpload('the_achievement_badge');" id="the_achievement_badge_thumb"></div>
												<div class="gallery-item-options relative">
													<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_achievement_badge');"><span class="icon icon-image"></span></button>
													<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_achievement_badge');"> <span class="icon icon-trash"></span> </button>
													<input type="hidden" id="the_achievement_badge" value="<?= isset($a) ? $a->achievement_badge : ""; ?>"/>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Color','bluerabbit'); ?></td>
									<td>
										<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
											<?php $selected_color = isset($a) ? $a->achievement_color : 'purple' ; ?>
											<input id="the_achievement_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the_achievement_color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150 purple-bg-100"><?php _e('Type','bluerabbit'); ?></td>
									<td class="purple-bg-100">
										<div class="input-group w-full">
											<label class="purple-bg-800 font w900"><span class="icon icon-view"></span></label>
											<select class="form-ui" id="the_achievement_display" onChange="checkPath();">
												<option <?= (isset($a) && $a->achievement_display=='badge') ? 'selected' : ''; ?> value='badge'><?=__("Badge","bluerabbit"); ?></option>
												<option <?= (isset($a) && $a->achievement_display=='rank') ? 'selected' : ''; ?> value='rank'><?=__("Rank","bluerabbit"); ?></option>
												<option <?= (isset($a) && $a->achievement_display=='path') ? 'selected' : ''; ?> value='path'><?=__("Path","bluerabbit"); ?></option>
											</select>
										</div>
									</td>
								</tr>
								<tr class="conditional-display path-display">
									<td class="text-right w-150 purple-bg-100">
										<span class="font _16 block"><?php _e('Group','bluerabbit'); ?></span>
										<span class="font _12 block"><?php _e('In case you want this to be exclusive between several paths, players will only earn ONE achievement from the group','bluerabbit'); ?></span>
									</td>
									<td class="purple-bg-100">
										<div class="input-group w-full">
											<label class="purple-bg-800 font w900"><span class="icon icon-view"></span></label>
											<?php $groups = array(
												array("A","red-bg-400 white-color"),
												array("B","orange-bg-400 white-color"),
												array("C","amber-bg-400 grey-900"),
												array("D","green-bg-400 white-color"),
												array("E","teal-bg-400 white-color"),
												array("F","cyan-bg-400 white-color"),
												array("G","blue-bg-400 white-color"),
												array("H","indigo-bg-400 white-color"),
												array("I","deep-purple-bg-400 white-color"),
												array("J","pink-bg-400 white-color"),
											); 
											?>
											<select class="form-ui w-full" id="the_achievement_group">
												<option class="font w900" value="" <?= (!$a || !$a->achievement_group) ? "selected" : ""; ?>>No Group</option>
												<?php foreach ($groups as $g){ ?>
													<option class="<?=$g[1];?>" value="<?=$g[0];?>" <?= (isset($a) && $a->achievement_group==$g[0]) ? "selected" : ""; ?>>Group <?=$g[0];?></option>
												<?php } ?>
											</select>
										</div>
									</td>
								</tr>
								<tr class="conditional-display path-display badge-display">
									<td class="text-right w-150 red-bg-200"><?php _e('Available for','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="purple-bg-800 font w900"><span class="icon icon-tag"></span></label>
											<select id="the_achievement_path" class="form-ui">
												<option <?= (isset($a) && !$a->achievement_path) ? 'selected' : ''; ?> value='0'><?= __("All Paths","bluerabbit"); ?></option>
												<?php foreach($paths as $path){ ?>
													<option value='<?=$path->achievement_id; ?>' <?= $a->achievement_path == $path->achievement_id ? 'selected' : ''; ?>>
														<?=$path->achievement_name; ?>
													</option>
												<?php } ?>
											</select>
										</div>
									</td>
								</tr>
								
								<tr class="conditional-display badge-display ">
									<td class="text-right w-150">
										<?php _e('Max Players','bluerabbit'); ?><br>
										<span class="grey-400 font _12"><?php _e('Leave blank for no limit','bluerabbit'); ?><br>
									</td>
									<td>
										<div class="input-group w-full">
											<input class="form-ui" type="number" value="<?= isset($a) ? $a->achievement_max : ""; ?>" id="the_achievement_max">
										</div>
									</td>
								</tr>
								<tr class="conditional-display badge-display path-display">
									<td class="text-right w-150"><?php _e('XP','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="purple-bg-800 font w900"><span class="icon icon-star"></span></label>
											<input type="number" class="form-ui w-full" id="the_achievement_xp" value="<?=  isset($a) ? $a->achievement_xp : "";?>">
										</div>
									</td>
								</tr>
								<?php if($use_encounters){ ?>
									<tr class="conditional-display badge-display path-display">
										<td class="text-right w-150"><?php _e('EP','bluerabbit'); ?></td>
										<td>
											<div class="input-group w-full">
												<label class="purple-bg-800 font w900"><span class="icon icon-activity"></span></label>
												<input type="number" class="form-ui w-full" id="the_achievement_ep" value="<?=  isset($a) ? $a->achievement_ep : "";?>">
											</div>
										</td>
									</tr>
								<?php } ?>
								<tr class="conditional-display badge-display path-display">
									<td class="text-right w-150"><?php _e('BLOO','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="purple-bg-800 font w900"><span class="icon icon-bloo"></span></label>
											<input type="number" class="form-ui w-full" id="the_achievement_bloo" value="<?=  isset($a) ? $a->achievement_bloo : "";?>">
										</div>
									</td>
								</tr>
								<tr class="conditional-display badge-display">
									<td class="text-right w-150"><?php _e('Deadline','bluerabbit'); ?></td>
									<td>
										<?php 
										if(isset($a) && $a->achievement_deadline != "0000-00-00 00:00:00"){ 
											$deadline =date('Y/m/d H:i',strtotime($a->achievement_deadline)); 
										}else{
											$deadline = '';
										} ?>
										<input class="form-ui font w600 datetimepicker grey-900 text-left"  autocomplete="off" id="the_achievement_deadline" type="text" value="<?= $deadline; ?>" >
										<input class="the_start_date" type="hidden" value="<?= date('Y/m/d H:i');?>" >
									</td>
								</tr>
								<tr class="">
									<td class="text-right w-150">
										<?php _e('Secret Message',"bluerabbit"); ?>
									</td>
									<td>
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  indigo-bg-400">
												<span class="icon icon-warning white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line font _14 w300 grey-500"><?php _e('This message will be seen once the players earn the achievement.','bluerabbit'); ?></span>
											</span>
										</span>
										<div class="padding-5 w-full">
											<?php 
											if($roles[0]=="administrator"){
												$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
											}else{
												$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
											}
											if( isset($a) ){
												wp_editor( $a->achievement_content, 'the_achievement_content',$wp_editor_settings); 	
											}else{
												wp_editor('', 'the_achievement_content',$wp_editor_settings); 	
											}
											?>
										</div>
									</td>
								</tr>
								
								
							</tbody>
						</table>
					</div>
					<div class="tab max-w-900 padding-10" id="achievement-codes">
						<div class="highlight padding-10 grey-bg-200">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-achievement"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Magic Code","bluerabbit"); ?></span>
									<span class="line font _14 grey-800">
										<?= __("This code can be used by all players.","bluerabbit"); ?><br>
										<?= __("The achievement is assigned only once so it doesn't matter if the player scans or uses the code multiple times.","bluerabbit"); ?><br>
										<?= __("The reward only happens once.","bluerabbit"); ?>
									</span>
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
									<td class="text-right w-150"><?php _e('Magic code','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full font _30">
											<label class="purple-bg-800 font _30 w900"><span class="icon icon-magic"></span></label>
											<input class="form-ui" type="text" value="<?= isset($a) ? $a->achievement_code : ""; ?>" id="the_achievement_code" onChange="updateMagicCode();">
											<label class="cyan-bg-400">
												<button class="form-ui cyan-bg-600 font _24" onClick="createMagicCode();"><?php _e("Generate","bluerabbit"); ?></button>
											</label>
											<label class="red-bg-400">
												<button class="form-ui transparent-bg font _24" onClick="clearMagicCode();"><span class="icon icon-cancel"></span></button>
											</label>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Magic link','bluerabbit'); ?></td>
									<td>
										<?php 
											if( isset($a) && $a->achievement_code){ 
												$magicLink = get_bloginfo('url')."/magic-link/?c=$a->achievement_code&adv=$a->adventure_id"; 
											}else{
												$magicLink = '';
											}
										?>
										<input type="hidden" id="site-url" value="<?= get_bloginfo('url')."/magic-link/?&c="; ?>">
										<div class="input-group w-full badge-display path-display conditional-display" id="tutorial-magic-link">
											<label class="teal-bg-400"><span class="icon icon-qr"></span></label>
											<input class="form-ui teal w-full" id="the_magic_link" readonly type="text" value="<?= $magicLink; ?>">
											<span class="tool-tip top">
												<span class="tool-tip-text"><?php _e("The magic link is generated automatically","bluerabbit"); ?></span>
											</span>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="highlight padding-10 deep-purple-bg-100">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-qr"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Unique Codes","bluerabbit"); ?></span>
									<span class="line font _14 grey-800">
										<?= __("This codes can only be used once.","bluerabbit"); ?><br>
										<?= __("A player can't earn the same achievement twice so only one code per player will work.","bluerabbit"); ?><br>
										<?= __("Codes are affected by the max number of players that can earn the achievement and the deadline.","bluerabbit"); ?>
									</span>
								</span>
							</span>
						</div>
						<?php if(isset($a)){ ?>
							<div class="tabs" id="unique-code-tabs">
								<div class="tabs-header" id="unique-code-tabs-buttons">
									<div class="tabs-buttons">
										<button onClick="switchTabs('#unique-code-tabs','#unique-codes-avaliable');" class="tab-button active purple-border-400" id="unique-codes-avaliable-tab-button">
											<?= __("Available","bluerabbit");?>
										</button>
										<button onClick="switchTabs('#unique-code-tabs','#unique-codes-redeemed');" class="tab-button purple-border-400" id="unique-codes-redeemed-tab-button">
											<?= __("Redeemed","bluerabbit"); ?>
										</button>
										<button onClick="switchTabs('#unique-code-tabs','#unique-codes-expired');" class="tab-button purple-border-400" id="unique-codes-expired-tab-button">
											<?= __("Expired","bluerabbit"); ?>
										</button>
										<button class="form-ui font _18 pull-right" onClick="newUniqueAchievementCode(<?=$a->achievement_id; ?>);switchTabs('#unique-code-tabs','#unique-codes-avaliable');">
											<?= __("Create Unique Code","bluerabbit"); ?>
										</button>
									</div>
								</div>
								<div class="tab active" id="unique-codes-avaliable">
									<table class="table">
										<thead>
											<tr>
												<td><?=__("ID","bluerabbit");?></td>
												<td><?=__("Code","bluerabbit");?></td>
												<td><?=__("Actions","bluerabbit");?></td>
											</tr>
										</thead>
										<tbody id="achievement-codes-table">
										<?php
										$codes = getUniqueAchievementCodes($a->achievement_id);
										foreach($codes as $key=>$c){ 
											if($c->code_status=='publish'){
												include (TEMPLATEPATH . '/achievement-unique-code.php'); 
											}
										} ?>
										</tbody>
									</table>
									<div class="list-of-links">
										<ul>
										<?php
										foreach($codes as $key=>$c){ 
											if($c->code_status=='publish'){
												?>
											<li><?php echo get_bloginfo('url')."/magic-link/?c=$c->code_value&adv=$a->adventure_id"; ?></li>
											<?php
											}
										} ?>
											
										</ul>
									</div>
								</div>
								<div class="tab" id="unique-codes-redeemed">
									<table class="table">
										<thead>
											<tr>
												<td><?=__("Code","bluerabbit");?></td>
												<td><?=__("Status","bluerabbit");?></td>
												<td><?=__("Redeemed","bluerabbit");?></td>
												<td><?=__("Player","bluerabbit");?></td>
												<td><?=__("Link","bluerabbit");?></td>
											</tr>
										</thead>
										<tbody id="">
											<?php foreach($codes as $key=>$c){ ?>
												<?php if($c->code_status == 'redeem'){ ?>
													<tr class="padding-10">
														<td class="relative"><?= $c->code_value; ?> </td>
														<td> <?=$c->code_status;?> </td>
														<td> <?=$c->code_redeemed; ?> </td>
														<td><?= $c->player_display_name; ?></td>
														<td> <?= get_bloginfo('url')."/magic-link/?c=$c->code_value"; ?> </td>
													</tr>
												<?php } ?>
											<?php } ?>
										</tbody>
									</table>
								</div>
								<div class="tab" id="unique-codes-expired">
									<table class="table">
										<thead>
											<tr>
												<td><?=__("Code","bluerabbit");?></td>
												<td><?=__("Expired","bluerabbit");?></td>
											</tr>
										</thead>
										<tbody id="">
											<?php foreach($codes as $key=>$c){ ?>
												<?php if($c->code_status == 'expired'){ ?>
													<tr class="padding-10">
														<td class="relative">
															<?= $c->code_value; ?>
															<?= $c->code_status;?>
														</td>
														<td> <?=$c->code_deadline; ?> </td>
													</tr>
												<?php } ?>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
						<?php }else{ ?>
							<div class="padding-20 amber-bg-200">
								<div class="icon-group">
									<div class="icon-button font _24 sq-48 amber-bg-500">
										<span class="icon icon-warning white-color"></span>
									</div>
									<div class="icon-content">
										<div class="line font w500 _26"><?= __("Must save the achievement before generating UNIQUE codes","bluerabbit");?></div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<?php } ?>
					<div class="tab max-w-900 padding-10  <?= $isNPC ? "active" : ""; ?>" id="select-players">
						<?php if(isset($a)){ ?>
							<div class="tabs" id="assign-manually">
								<div class="tabs-header" id="assign-manually-buttons">
									<div class="tabs-buttons">
										<button onClick="switchTabs('#assign-manually','#players-selection');" class="tab-button active purple-border-400" id="players-selection-tab-button">
											<?= __("Select Players","bluerabbit");?>
										</button>
										<button onClick="switchTabs('#assign-manually','#players-awarded');" class="tab-button purple-border-400" id="players-awarded-tab-button">
											<?= __("Awarded","bluerabbit"); ?>
										</button>
									</div>
								</div>
								<div class="tab active" id="players-selection">
									
									<?php include (TEMPLATEPATH . '/player-select-achievement.php'); ?>
								</div>
								<div class="tab" id="players-awarded">
									<?php
									$player_ids = implode(",",$selected_players);
									$players = $wpdb->get_results("
									SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug FROM {$wpdb->prefix}br_player_adventure a
									LEFT JOIN {$wpdb->prefix}br_players b 
									on a.player_id = b.player_id
									WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' AND a.player_id IN ($player_ids) ORDER BY b.player_email LIMIT 1000

									");
									?>
									<div class="highlight padding-10 grey-bg-100" id="tutorial-earned-players">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  indigo-bg-400">
												<?= count($players); ?>
											</span>
											<span class="icon-content">
												<span class="line font w500 _26"><?php _e('Awarded Players',"bluerabbit"); ?></span>
												<span class="line font _14 w300 grey-500"><?php _e('A list of the players that earned the achievement.','bluerabbit'); ?></span>
											</span>
										</span>
									</div>
									<div class="content w-full">
										<table class="table compact">
											<thead>
												<tr>
													<td><?php _e("ID","bluerabbit"); ?></td>
													<td><?php _e("Name","bluerabbit"); ?></td>
													<td><?php _e("Email","bluerabbit"); ?></td>
													<td><?php _e("Actions","bluerabbit"); ?></td>
												</tr>
											</thead>
											<tbody>
												<?php foreach($players as $play){ ?>
													<tr>
														<td><?= $play->player_id; ?></td>
														<td><?= $play->player_first." ".$play->player_last; ?></td>
														<td><?= $play->player_email; ?></td>
														<td id="player-achievement-list-<?=$play->player_id;?>" class="active">
															<button class="active-content form-ui red-bg-400 white-color" onClick="triggerAchievement(<?php echo "$a->achievement_id, $play->player_id"; ?>);">
																<?= __("Remove","bluerabbit"); ?>
															</button>
															<button class="inactive-content form-ui blue-bg-400 white-color" onClick="triggerAchievement(<?php echo "$a->achievement_id, $play->player_id"; ?>);">
																<?= __("Restore","bluerabbit"); ?>
															</button>
														
														
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						<?php }else{ ?>
							<div class="padding-20 amber-bg-200">
								<div class="icon-group">
									<div class="icon-button font _24 sq-48 amber-bg-500">
										<span class="icon icon-warning white-color"></span>
									</div>
									<div class="icon-content">
										<div class="line font w500 _26"><?= __("Must save the achievement before awarding players","bluerabbit");?></div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
</div>

<script> checkPath(); </script>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

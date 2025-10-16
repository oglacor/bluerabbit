<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php 

if($roles[0] == 'administrator'){
	$orgs = getOrgs();
}
if($adventure){
	 if(!$isAdmin && !$isGM){
	?> <script>document.location.href="<?php bloginfo('url');?>/404"; </script> <?php
	exit();
	 }
}

$adv_config=array(
	'journey_zoom_level' => array(
		'label'=>__("Zoom Level","bluerabbit"),
		'icon'=>'lock',
		'type'=>'select',
		'options' => array(
			array( "-1000", "10%" ),
			array( "-900", "20%" ),
			array( "-800", "30%" ),
			array( "-700", "40%" ),

			array( "-600", "50%" ),
			array( "-500", "60%" ),
			array( "-400", "70%" ),
			array( "-300", "80%" ),
			array( "-200", "90%" ),
			array( "0", "100%" ),
			array( "100", "110%" ),
			array( "200", "120%" ),
			array( "300", "130%" ),
			array( "400", "140%" )
		),
	),
	'hide_achievements' => array(
		'label'=>__("Hide Achievements","bluerabbit"),
		'desc'=>__("In the badges page, hide the achievements until the player has earned them","bluerabbit"),
		'icon'=>'view',
		'type'=>'radio',
	),
	'purchase_permission' => array(
		'label'=>__("Purchase Permissions","bluerabbit"),
		'icon'=>'lock',
		'type'=>'select',
		'options' => array(
			array( "all", "Admin, GM, NPCs and Players" ),
			array( "gm", "GM, NPCs and Players" ),
			array( "npc", "NPCs and Players" ),
			array( "players", "Only Players" ),
		),
	),
	'use_items' => array(
		'label'=>__("Use Items","bluerabbit"),
		'desc'=>__("Allow players to consume/spend their items","bluerabbit"),
		'icon'=>'transactions',
		'type'=>'radio',
	),
	'show_my_transactions' => array(
		'label'=>__("Show player's transactions","bluerabbit"),
		'icon'=>'transactions',
		'type'=>'radio',
	),
	'rate_quests' => array(
		'label'=>__("Allow players to rate quests","bluerabbit"),
		'icon'=>'star',
		'type'=>'radio',
	),
	'default_journey_view' => array(
		'label'=>__("Default Journey View","bluerabbit"),
		'icon'=>'journey',
		'type'=>'select',
		'options' => array(
			array( "map", "Map" ),
			array( "list", "List" ),
		),
	),
	'demo_adventure' => array(
		'label'=>__("Demo adventure","bluerabbit"),
		'desc'=>__("This allows players to delete all their data.","bluerabbit"),
		'icon'=>'star',
		'type'=>'radio',
	),
	'req_password_reset_demo' => array(
		'label'=>__("Require password to reset demo","bluerabbit"),
		'icon'=>'star',
		'type'=>'radio',
	),
	'show_certificate' => array(
		'label'=>__("Show Certificate in Achievements page","bluerabbit"),
		'icon'=>'achiever',
		'type'=>'radio',
	),
	'show_torch' => array(
		'label'=>__("Show torch button (go back to last viewed step)","bluerabbit"),
		'icon'=>'',
		'type'=>'radio',
	),
	'support_email' => array( 
		'label'=>__("Custom Support Email","bluerabbit"),
		'placeholder'=>__("help@domain.com","bluerabbit"),
		'type'=>'text',
	),
	'adventure_theme' => array(
		'label'=>__("Theme for adventure","bluerabbit"),
		'type'=>'select',
		'options' => array(
			array("default",__("Default","bluerabbit")),
			array("pirate",__("Pirate","bluerabbit")),
		),
	),
);
$config = getSysConfig();
$image_types = array(
	'default_bg' 		=> array('label' => __("Default Background","bluerabbit"),	),
	'journey_bg' 		=> array('label' => __("Journey Background","bluerabbit"),	),
	'item_shop_bg' 		=> array('label' => __("Item shop Background","bluerabbit"),	),
	'backpack_bg' 		=> array('label' => __("Backpack Background","bluerabbit"),	),
	'guilds_bg' 		=> array('label' => __("Guilds Background","bluerabbit"),	),
	'schedule_bg' 		=> array('label' => __("Schedule Background","bluerabbit"),	),
	'blog_bg' 			=> array('label' => __("Blog Background","bluerabbit"),	),
	'lore_bg' 			=> array('label' => __("Lore Background","bluerabbit"),	),
	'wall_bg' 			=> array('label' => __("Wall Background","bluerabbit"),	),
	'leaderboard_bg' 	=> array('label' => __("Leaderboard Background","bluerabbit"),	),
	'my_work_bg' 		=> array('label' => __("My Work Background","bluerabbit"),	),
);
?>
		<h1 class="padding-10 font condensed white-color w900 uppercase _20 light-blue-bg-800">
			<?php if(isset($adventure)){ ?>
				<span class="icon icon-edit"></span>
				<?= __('Edit Adventure','bluerabbit'); ?>
			<?php }else{ ?>
				<span class="icon icon-add"></span>
				<?= __('Create New Adventure','bluerabbit'); ?>
			<?php } ?>
		</h1>
		<input type="hidden" id="the_adventure_id" value="<?= $adventure->adventure_id; ?>">
		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_adventure_nonce'); ?>"/>
		<input type="hidden" id="register_nonce" value=""/>
		<input type="hidden" id="player-status-nonce" value="<?= wp_create_nonce('br_player_adventure_status_nonce'); ?>"/>
		<div class="dashboard">
			<div class="dashboard-sidebar grey-bg-800 relative padding-10">
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
							<button class="form-ui w-full relative tab-button overflow-hidden" id="adventure-intro-tab-button" onClick="switchTabs('#tab-group','#adventure-intro');">
								<span class="icon icon-document foreground relative"></span>
								<span class="foreground relative"><?= __("Intro","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>

						</li>
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="certificate-settings-tab-button" onClick="switchTabs('#tab-group','#certificate-settings');">
								<span class="icon icon-achievement foreground relative"></span>
								<span class="foreground relative"><?= __("Certificate","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						<?php if(isset($adventure)){ ?>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden" id="reset-settings-tab-button" onClick="switchTabs('#tab-group','#reset-settings');">
									<span class="icon icon-repeat foreground relative"></span>
									<span class="foreground relative"><?= __("Reset","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden" id="ranks-settings-tab-button" onClick="switchTabs('#tab-group','#ranks-settings');">
									<span class="icon icon-rank foreground relative"></span>
									<span class="foreground relative"><?= __("Level Ranks","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden" id="tabis-settings-tab-button" onClick="switchTabs('#tab-group','#tabis-settings');">
									<span class="icon icon-sabotage foreground relative"></span>
									<span class="foreground relative"><?= __("Tabis","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
							<li class="block">
								<button class="form-ui w-full relative tab-button overflow-hidden" id="enrolled-players-tab-button" onClick="switchTabs('#tab-group','#enrolled-players');">
									<span class="icon icon-players foreground relative"></span>
									<span class="foreground relative"><?= __("Enrolled Players","bluerabbit");?></span>
									<span class="active-content background orange-bg-700"></span>
									<span class="inactive-content background grey-bg-600"></span>
								</button>
							</li>
							
						<?php } ?>
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="features-tab-button" onClick="switchTabs('#tab-group','#features');">
								<span class="icon icon-teamwork foreground relative"></span>
								<span class="foreground relative"><?= __("Features","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						
						<li class="block">
							<button class="form-ui w-full relative tab-button overflow-hidden" id="image_settings-tab-button" onClick="switchTabs('#tab-group','#image_settings');">
								<span class="icon icon-image foreground relative"></span>
								<span class="foreground relative"><?= __("Images","bluerabbit");?></span>
								<span class="active-content background orange-bg-700"></span>
								<span class="inactive-content background grey-bg-600"></span>
							</button>
						</li>
						<li class="block border border-top border-1 white-border opacity-50 margin-20"> </li>
						<li class="block">
							<h4 class="white-color padding-5 text-center font _18 condensed"><?= __("Status","bluerabbit"); ?></h4>
						</li>
						<li class="block">
							<select id="the_adventure_status" class="form-ui ">
								<option value="publish" <?php if($adventure->adventure_status == 'publish' || !$adventure){echo 'selected';} ?> ><?= __('Publish','bluerabbit'); ?></option>
								<option value="draft"  <?php if($adventure->adventure_status == 'draft'){echo 'selected';} ?>><?= __('Draft','bluerabbit'); ?></option>
								<option value="trash"  <?php if($adventure->adventure_status == 'trash'){echo 'selected';} ?>><?= __('Trash','bluerabbit'); ?></option>
							</select>
						</li>
						
						<li class="block text-center">
							<button type="button" class="form-ui light-green-bg-200 green-900 font w300 _18 w-full" onClick="updateAdventure();">
								<?php if($adventure_id){ ?>
									<span class="icon icon-repeat"></span> <?= __('Update Adventure','bluerabbit'); ?>
								<?php }else{ ?>
									<span class="icon icon-freespirit"></span> <?= __('Create Adventure','bluerabbit'); ?>
								<?php } ?>
							</button>
						</li>
						
						<li class="block text-center">
							<a class="form-ui red-bg-300 white-color w-full" href="<?= bloginfo('url'); ?>">
								<span class="icon icon-cancel"></span> <?= __('Cancel','bluerabbit'); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="dashboard-content white-bg">
				<div class="tabs" id="tab-group">
					<div class="active tab max-w-900 padding-10" id="general">
						<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-adventure white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('General Settings',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('Basic settings','bluerabbit'); ?></span>
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
									<td class="text-right w-150"><?= __('Adventure Name','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<label class="light-blue-bg-800 font _24"><span class="icon icon-quest"></span></label>
											<input class="form-ui font _30 w-full" placeholder="Adventure Name" maxlength="50" type="text" value="<?php if($adventure){echo $adventure->adventure_title;} ?>" id="the_adventure_title">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?= __('Enrollment Link','bluerabbit'); ?></td>
									<td>
										<input type="text" readonly class="form-ui font w600 w-full" value="<?= get_bloginfo('url')."/enroll/?enroll_code=$adventure->adventure_code"; ?>">
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?= __('Image','bluerabbit'); ?></td>
									<td>
										<div class="gallery">
											<div class="gallery-item setting">
												<div class="background" style="background-image: url(<?= $adventure->adventure_badge; ?>);" onClick="showWPUpload('the_adventure_badge');" id="the_adventure_badge_thumb"></div>
												<div class="gallery-item-options relative">
													<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_badge');"><span class="icon icon-image"></span></button>
													<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_badge');"> <span class="icon icon-trash"></span> </button>
													<input type="hidden" id="the_adventure_badge" value="<?php echo $adventure->adventure_badge; ?>"/>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?= __('Highlight Color','bluerabbit'); ?></td>
									<td>
										<?php $selected_color = $adventure->adventure_color; ?>
										<input id="the_adventure_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
										<?php 
										$color_select_id = "#the_adventure_color";
										include (TEMPLATEPATH . '/color-select.php');
										?>
									</td>
								</tr>
								<?php if($roles[0] == 'administrator'){ ?>
									<tr>
										<td class="text-right w-150"><?= __('Adventure Type','bluerabbit'); ?></td>
										<td>
											<select id="the_adventure_type" class="form-ui font _24">
												<option value="normal" <?= $adventure->adventure_type == "normal" ? "selected" : ""; ?>><?= __("Normal","bluerabbit"); ?></option>
												<option value="template" <?= $adventure->adventure_type == "template" ? "selected" : ""; ?>><?= __("Template","bluerabbit"); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td class="text-right w-150"><?= __('Owner','bluerabbit'); ?></td>
										<td>
											<?php 
											   $the_owner = $adventure->adventure_owner; 
											   if(!$the_owner){
												   $the_owner = $current_user->ID; 
											   }
											?>
											<?php $the_roles = array('br_npc','br_game_master','administrator'); ?>
											<?php $allTeachers = get_users(array('role__in'=>$the_roles)); ?>
											<select id="the_adventure_owner" class="form-ui font _24">
												<?php foreach($allTeachers as $at){ ?>
													<option value="<?= $at->ID; ?>" <?php if($at->ID == $the_owner) { echo'selected class="green-bg-400 white-color w900 font"'; }?>>
														<?= $at->display_name; ?>
														<?php 
														if($at->roles[0] =='administrator'){
															echo " | Admin";
														}elseif($at->roles[0] =='br_game_master'){
															echo " | GM";
														}elseif($at->roles[0] =='br_npc'){
															echo " | NPC";
														}
														?>
													</option>
												<?php } ?>
											</select>
										</td>
									</tr>
								<?php }else{?>
									<input type="hidden" id="the_adventure_owner" value="<?= $current_user->ID; ?>">
								<?php } ?>
								<tr>
									<?php if($config['adventure_privacy']['value'] > 0 ){ ?>
										<td class="text-right w-150"><?= __('Privacy','bluerabbit'); ?></td>
										<td>
											<?php  $the_adventure_privacy = $adventure->adventure_privacy ? $adventure->adventure_privacy : $config['adventure_privacy']['value'];  ?>
											<select id="the_adventure_privacy" class="form-ui ">
												<option value="public"  <?php if($the_adventure_privacy == 'public'){echo 'selected';} ?>><?= __('Public','bluerabbit'); ?></option>
												<option value="invite-only" <?php if($the_adventure_privacy == 'invite-only'){echo 'selected';} ?>><?= __('Invite Only','bluerabbit'); ?></option>
											</select>
										</td>
									<?php }else{ ?>
										<input type="hidden" value="invite-only" id="the_adventure_privacy">
									<?php } ?>
								</tr>
							</tbody>
						</table>
						<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-config white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('Core Mechanics',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('Settings that affect the narrative and environment','bluerabbit'); ?></span>
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
									<td class="text-right w-150">
										<span class="block"><?= __('Experience Points','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("The name for the points that allow the player to level up","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="input-group w-full">
											<label class="light-blue-bg-600"><?= __("Long Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="Experience Points" type="text" value="<?= $xp_long_label; ?>" id="the_adventure_xp_long_label">
										</div>
										<div class="input-group w-full">
											<label class="light-blue-bg-600"><?= __("Short Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="XP" type="text" value="<?= $xp_label; ?>" id="the_adventure_xp_label">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Virtual Currency','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("The name for the points the players use to pay for anything","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="input-group w-full">
											<label class="light-green-bg-600"><?= __("Long Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="Bloo Coins" type="text" value="<?= $bloo_long_label; ?>" id="the_adventure_bloo_long_label">
										</div>
										<div class="input-group w-full">
											<label class="light-green-bg-600"><?= __("Short Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="Bloo" type="text" value="<?= $bloo_label; ?>" id="the_adventure_bloo_label">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Rechargeable Points','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("The name for the points the players spend when energy is needed","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="input-group w-full">
											<label class="cyan-bg-700"><?= __("Long Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="Energy Points" type="text" value="<?= $ep_long_label; ?>" id="the_adventure_ep_long_label">
										</div>
										<div class="input-group w-full">
											<label class="cyan-bg-700"><?= __("Short Label","bluerabbit"); ?></label>
											<input class="form-ui w-full" placeholder="EP" type="text" value="<?= $ep_label; ?>" id="the_adventure_ep_label">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Nickname','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("The default way of calling them within the adventure. Write in singular.","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="input-group w-full">
											<input class="form-ui" type="text" value="<?php if($adventure){echo $adventure->adventure_nickname;} ?>" id="the_adventure_nickname">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Assign guilds on login','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("If assigning random guilds to the players, turn this on.","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<div class="input-group w-full">
											<select id="the_adventure_has_guilds" class="form-ui">
												<option <?php if(!$adventure->adventure_has_guilds){ echo 'selected'; }?> value="0">
													<?= __('No','bluerabbit'); ?>
												</option>
												<option <?php if($adventure->adventure_has_guilds ){ echo 'selected'; }?> value="1">
													<?= __('Yes','bluerabbit'); ?>
												</option>
											</select>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-config white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('Time Mechanics',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('Settings that affect the schedule, deadlines and start dates','bluerabbit'); ?></span>
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
									<td class="text-right w-150">
										<span class="block"><?= __('Time zone','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("The time zone of the adventure. Default is GMT 0","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<?php 
										function tz_list() {
										  $zones_array = array();
										  $timestamp = time();
										  foreach(timezone_identifiers_list() as $key => $zone) {
											date_default_timezone_set($zone);
											$zones_array[$key]['gmt_value'] = $zone.' GMT ' . date('P', $timestamp);
											$zones_array[$key]['tz'] = $zone;
										  }
										  return $zones_array;
										}
										?>
										<div class="block text-center w-full padding-10 indigo-bg-50 indigo-900">
											<span class="icon icon-time"></span><?= __('Current adventure time','bluerabbit').": ";?> <strong><?= date('Y-m-d H:i'); ?></strong>
										</div>
										<div class="input-group w-full">
											<label class="indigo-bg-400"><span class="icon icon-time"></span> <?= __('Time zone','bluerabbit'); ?></label>

											<select class="form-ui w-full" id="the_adventure_gmt">
												<option value="0" class="font w900"><?= __("Please, select timezone","bluerabbit"); ?></option>
												<?php $tz_group = "Africa"; ?>
												<optgroup class="light-blue-bg-700 white-color" label="<?= $tz_group; ?>"> 
												<?php foreach(tz_list() as $key=>$t) { ?>
													<?php $new_tz_group = explode("/",$t['tz']); ?>
													<?php if($new_tz_group[0] != $tz_group){  ?>
														<?php $tz_group = $new_tz_group[0]; ?>
														</optgroup>
														<optgroup  class="light-blue-bg-700 white-color"  label="<?= $tz_group; ?>">  
													<?php } ?>
													<option class="grey-900" value="<?php print $t['tz']; ?>" <?php if($adventure->adventure_gmt == $t['tz']){echo 'selected';}?>>
														<?php print $t['gmt_value']; ?>
													</option> 
												<?php } ?>
												 </optgroup>
											</select>
										</div>

									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Hide quests','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("How to hide quests based on time.","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<select id="the_adventure_hide_quests" class="form-ui w-full">
											<option <?php if(!$adventure->adventure_hide_quests){ echo 'selected'; }?> value="">
												<?= __('Never','bluerabbit'); ?>
											</option>
											<option <?php if($adventure->adventure_hide_quests=='before' ){ echo 'selected'; }?> value="before">
												<?= __('Before Start Date','bluerabbit'); ?>
											</option>
											<option <?php if($adventure->adventure_hide_quests=='after' ){ echo 'selected'; }?> value="after">
												<?= __('After Deadline','bluerabbit'); ?>
											</option>
											<option <?php if($adventure->adventure_hide_quests=='both' ){ echo 'selected'; }?> value="both">
												<?= __('Before and After','bluerabbit'); ?>
											</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Schedule','bluerabbit'); ?></span>
									</td>
									<td>
										<select id="the_adventure_hide_schedule" class="form-ui">
											<option <?php if(!$adventure->adventure_hide_schedule || $adventure->adventure_hide_schedule=='show'){ echo 'selected'; }?> value="show">
												<?= __('Show all days','bluerabbit'); ?>
											</option>
											<option <?php if($adventure->adventure_hide_schedule == "hide"){ echo 'selected'; }?> value="hide">
												<?= __('Just today','bluerabbit'); ?>
											</option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-config white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('Resource Mechanics',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('Settings that affect the when the players receive their resources','bluerabbit'); ?></span>
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
									<td class="text-right w-150">
										<span class="block"><?= __('Grade scale type','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("If grading the players, what type of scale are you using.","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<select id="the_adventure_grade_scale" class="form-ui w-full">
											<option <?php if($adventure->adventure_grade_scale == 'none' || !$adventure->adventure_grade_scale){ echo 'selected'; }?> value="none">
												<?= __('None','bluerabbit'); ?>
											</option>
											<option  <?php if($adventure->adventure_grade_scale == 'percentage'){ echo 'selected'; }?> value="percentage">
												<?= __('Percentage','bluerabbit'); ?>
											</option>
											<option <?php if($adventure->adventure_grade_scale == 'letters'){ echo 'selected'; }?> value="letters">
												<?= __('GPA (letters)','bluerabbit'); ?>
											</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150">
										<span class="block"><?= __('Assign Resources','bluerabbit'); ?></span>
										<span class="font _12 block grey-500">
											<?= __("If grading the players, when will the players receive the resources from each quest.","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<select id="the_adventure_progression_type" class="form-ui">
											<option  <?php if($adventure->adventure_progression_type == 'before' || !$adventure->adventure_progression_type){ echo 'selected'; }?> value="before"><?= __('Before Grading','bluerabbit'); ?></option>
											<option <?php if($adventure->adventure_progression_type == 'after'){ echo 'selected'; }?> value="after"><?= __('After Grading','bluerabbit'); ?></option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="tab max-w-900 padding-10" id="adventure-intro">
						<div class="highlight padding-10 grey-bg-200" id="tutorial-adventure-intro">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-adventure white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('Adventure Intro',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('This message will be seen when players log in for the first time to the adventure.','bluerabbit'); ?></span>
								</span>
							</span>
						</div>
						<div class="content">
							<?php 
							if($roles[0]=="administrator"){
								$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
							}else{
								$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
							}
							wp_editor( $adventure->adventure_instructions, 'the_adventure_instructions',$wp_editor_settings); 	
							?>
						</div>
					</div>
					<div class="tab max-w-900 padding-10" id="certificate-settings">
						<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  indigo-bg-400">
									<span class="icon icon-config white-color"></span>
								</span>
								<span class="icon-content font w500 _26">
									<span class="line"><?= __('Certificate Settings',"bluerabbit"); ?></span>
									<span class="line font _14 w300 grey-500"><?= __('Start and End dates for the adventure if any','bluerabbit'); ?></span>
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
									<td class="text-right w-150"><?php _e('Start Date','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<?php
											if(isset($adventure) && $adventure->adventure_start_date){ 
												$pretty_start_date = date('Y/m/d H:i', strtotime($adventure->adventure_start_date));
											}else{
												$pretty_start_date = '';
											}
											?>
											<label class="cyan-bg-400 font w900"><span class="icon icon-calendar"></span></label>
											<input class="form-ui text-center font w600 the_start_date"  autocomplete="off" id="the_adventure_start_date" value="<?= $pretty_start_date; ?>">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('End Date','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full">
											<?php
											if(isset($adventure) && $adventure->adventure_end_date){ 
												$pretty_deadline = date('Y/m/d H:i', strtotime($adventure->adventure_end_date));
											}else{
												$pretty_deadline = '';
											}
											?>
											<label class="red-bg-800 font w900"><span class="icon icon-calendar"></span></label>
											<input class="form-ui text-center font w600 the_deadline"  autocomplete="off" id="the_adventure_end_date" value="<?= $pretty_deadline; ?>">
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?= __('Logo','bluerabbit'); ?></td>
									<td>
										<div class="gallery">
											<div class="gallery-item setting">
												<div class="background" style="background-image: url(<?= $adventure->adventure_logo; ?>);" onClick="showWPUpload('the_adventure_logo');" id="the_adventure_logo_thumb"></div>
												<div class="gallery-item-options relative">
													<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_logo');"><span class="icon icon-image"></span></button>
													<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_logo');"> <span class="icon icon-trash"></span> </button>
													<input type="hidden" id="the_adventure_logo" value="<?php echo $adventure->the_adventure_logo; ?>"/>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?= __('Signature','bluerabbit'); ?></td>
									<td>
										<div class="gallery">
											<div class="gallery-item setting">
												<div class="background" style="background-image: url(<?= $adventure->adventure_certificate_signature; ?>);" onClick="showWPUpload('the_adventure_certificate_signature');" id="the_adventure_certificate_signature_thumb"></div>
												<div class="gallery-item-options relative">
													<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_certificate_signature');"><span class="icon icon-image"></span></button>
													<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_certificate_signature');"> <span class="icon icon-trash"></span> </button>
													<input type="hidden" id="the_adventure_certificate_signature" value="<?php echo $adventure->adventure_certificate_signature; ?>"/>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php if($adventure){ ?>
						<div class="tab max-w-900 padding-10" id="reset-settings">
							<div class="w-full relative text-center padding-10">
								<button class="form-ui red-bg-300 font _30" onClick="resetIntro();" id="tutorial-reset-intro"><?= __("Reset Display Intro","bluerabbit"); ?></button>
							</div>
							<div class="w-full relative text-center padding-10">
								<button class="form-ui deep-purple-bg-300 font _30" onClick="resetPrevLevel();" id="tutorial-reset-prev-level"><?= __("Reset Prev Level","bluerabbit"); ?></button>
							</div>
							<div class="w-full relative text-center padding-10">
								<button class="form-ui light-green-bg-400 font _30"  onClick="showOverlay('#confirm-reset-guilds');" id="tutorial-reset-guilds"><?= __("Reset Guilds","bluerabbit"); ?></button>
								<div class="confirm-action overlay-layer bottom padding-10 relative" id="confirm-reset-guilds">
									<div class="layer background absolute sq-full grey-bg-900 opacity-80"></div>
									<button class="form-ui white-bg layer relative base" onClick="resetGuilds();">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  icon-sm red-bg-400 icon-sm">
												<span class="icon icon-warning white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _20 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
												<span class="line red-400 font _16 w900"><?= __("This will be an issue if players are already competing","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40 layer base  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</div>
						</div>
						<div class="tab max-w-900 padding-10" id="ranks-settings">
							<div class="highlight padding-10 green-bg-50" id="tutorial-adventure-ranks">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  green-bg-400">
										<span class="icon icon-rank white-color"></span>
									</span>
									<span class="icon-content font w500 _26">
										<span class="line"><?= __('Adventure Ranks',"bluerabbit"); ?></span>
										<span class="line font _14 w300 grey-500"><?= __('This will show a special message every time the player reaches the specified level and will assign your chosen achievement (this will show as the rank for the player).','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
							<div class="content">
								<?php 
									$achievements = $wpdb->get_results(
										"SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_status = 'publish' AND achievement_display='rank'"
									); 
								?>
								<?php if($achievements) { ?>
									<table class="table" id="adventure-ranks">
										<thead>
											<tr>
												<td><?= __("Level","bluerabbit"); ?></td>
												<td><?= __("Achievement","bluerabbit"); ?></td>
												<td><?= __("Actions","bluerabbit"); ?></td>
											</tr>
										</thead>
										<?php 
											 $ranks = $wpdb->get_results(
												"SELECT * FROM {$wpdb->prefix}br_adventure_ranks
												WHERE adventure_id=$adv_parent_id ORDER BY rank_level"
											); 
										?>
										<tbody>
											<?php foreach($ranks as $key=>$rank){ ?>
												<tr id="row-<?= $key; ?>">
													<td>
														<div class="input-group w-full">
															<input type="number" max="99" min="1" class="form-ui rank-level" value="<?= $rank->rank_level; ?>">
															<span class="tool-tip top">
																<span class="tool-tip-text blue-bg-700"> <?= __("Only numbers between 1-99","bluerabbit"); ?> </span>
															</span>
														</div>
													</td>
													<td>
														<select class="form-ui rank-achievement unique">
															<option value="0"><?= __("Select achievement","bluerabbit"); ?></option>
															<?php foreach($achievements as $a){ ?>
																<option value="<?= $a->achievement_id; ?>" <?= $a->achievement_id==$rank->achievement_id ? "selected" : ""; ?>>
																	<?= $a->achievement_name;  ?>
																</option>
															<?php } ?>
														</select>
													</td>
													<td>
														<button class="icon-button font _24 sq-40  red-bg-A400" onClick="removeTableRow('#row-<?= $key; ?>');">
															<span class="icon icon-trash"></span>
														</button>
													</td>
												</tr>
											<?php } ?>
											<!-- Default -->
											<tr id="row-0">
												<td>
													<div class="input-group w-full">
														<input type="number" max="99" min="1" class="form-ui rank-level" value="">
														<span class="tool-tip top">
															<span class="tool-tip-text blue-bg-700"> <?= __("Only numbers between 1-99","bluerabbit"); ?> </span>
														</span>
													</div>
												</td>
												<td>
													<select class="form-ui rank-achievement unique">
														<option value="0"><?= __("Select achievement","bluerabbit"); ?></option>
														<?php foreach($achievements as $a){ ?>
															<option value="<?= $a->achievement_id; ?>">
																<?= $a->achievement_name;  ?>
															</option>
														<?php } ?>
													</select>
												</td>
												<td>
													<button class="icon-button font _24 sq-40  red-bg-A400 remove-row">
														<span class="icon icon-trash"></span>
													</button>
												</td>
											</tr>
										</tbody>
									</table>
									<div class="highlight padding-10 grey-bg-100">
										<button class="form-ui blue-bg-700 white-color" onClick="addTableRow('#adventure-ranks');">
											<span class="icon icon-add"></span>
											<?= __('Add Rank',"bluerabbit"); ?>
										</button>
									</div>
								<?php }else{ ?>
									<div class="highlight padding-10 red-bg-50 text-center font _24 grey-700">
										<a class="form-ui purple-bg-400 white-color" href="<?= get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id"; ?>">
											<span class="icon icon-add"></span>
											<?= __('Create the first rank',"bluerabbit"); ?>
										</a>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="tab max-w-900 padding-10" id="tabis-settings">
							<div class="highlight padding-10 green-bg-50" id="tutorial-adventure-tabis">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  green-bg-400">
										<span class="icon icon-rank white-color"></span>
									</span>
									<span class="icon-content font w500 _26">
										<span class="line"><?= __('Adventure Tabis',"bluerabbit"); ?></span>
										<span class="line font _14 w300 grey-500"><?= __('Create avatars the players can build by purchasing or earning rewards.','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
							<div class="content">
								<?php $tabis = getTabis($adv_parent_id); ?>
								<div class="admin-table table-tabis" id="table-tabis">
									<div class="admin-table-header">
										<div class="row admin-row with-tabi-assign">
											<div class="cell cell-tabi-id">&nbsp;</div>
											<div class="cell cell-name"><?= __("Name","bluerabbit");?></div>
											<div class="cell cell-badge"><?= __("Background","bluerabbit");?></div>
											<div class="cell cell-color"><?= __("Color","bluerabbit");?></div>
											<div class="cell cell-level"><?= __("Level","bluerabbit");?></div>
											<div class="cell cell-width"><?= __("Width","bluerabbit");?></div>
											<div class="cell cell-height"><?= __("Height","bluerabbit");?></div>
											<div class="cell cell-location"><?= __("Show in Journey","bluerabbit");?></div>
										</div>
									</div>
									<?php if($tabis){ ?>
										<?php foreach($tabis as $avKey=>$a){ ?>
											<?php 
												$rowNumber = $avKey+1;
												include (get_stylesheet_directory() . '/tabi-row.php'); 
												?>
										<?php } ?>
									<?php } ?>
								</div>
								<div class="highlight padding-10 text-center">
									<button class="form-ui blue-bg-400" onClick="addTabi();";><?= __("Add Tabi","bluerabbit"); ?></button>
								</div>
							</div>
						</div>
						<div class="tab max-w-1200 padding-10" id="enrolled-players">
							<?php 
							$players = $wpdb->get_results("
								SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug, users.user_login FROM {$wpdb->prefix}br_player_adventure a
								JOIN {$wpdb->prefix}users users 
								on a.player_id = users.ID
								LEFT JOIN {$wpdb->prefix}br_players b 
								on a.player_id = b.player_id
								WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' LIMIT 1000
							");
							?>
							<div class="highlight padding-10 grey-bg-200 h-60" id="tutorial-players">
								<div class="icon-group">
									<span class="icon-button font _24 sq-40  orange-bg-200">
										<span class="icon icon-players white-color"></span>
									</span>
									<span class="icon-content font w500 _26">
										<span class="line font _24 w300"><?= __("Enrolled players","bluerabbit"); ?></span>
										<span class="line font _14 w900"><?= __("Total","bluerabbit")." ".count($players); ?></span>
									</span>
								</div>
								<div class="highlight-cell pull-right">
									<div class="input-group inline-table">
										<label> <span class="icon icon-search"></span> </label>
										<input type="text" class="form-ui" id="search-players" placeholder="<?= __("Search players","bluerabbit"); ?>">
										<script>
											$('#search-players').keyup(function(){
												var valThis = $(this).val().toLowerCase();
												if(valThis == ""){
													$('tbody#players-list > tr').show();           
												}else{
													$('tbody#players-list > tr').each(function(){
														var text = $(this).text().toLowerCase();
														(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
													});
												};
											});
										</script>
									</div>
								</div>

							</div>
							<div class="content">
								<div class="row">
									<table class="table compact players-list">
										<thead>
											<tr>
												<td><?= __("ID","bluerabbit"); ?></td>
												<td><?= __("User Login","bluerabbit"); ?></td>
												<td><?= __("Name","bluerabbit"); ?></td>
												<td><?= __("Lastname","bluerabbit"); ?></td>
												<td><?= __("Email","bluerabbit"); ?></td>
												<td><?= __("Work","bluerabbit"); ?></td>
												<td><?= __("Role","bluerabbit"); ?></td>
												<td><?= __("Actions","bluerabbit"); ?></td>
											</tr>
										</thead>
										<tbody id="players-list">
											<?php foreach($players as $play){ ?>
												<?php $player_role = $play->player_adventure_role;  ?>
												<tr id="player-row-<?= $play->player_id; ?>" class="<?= "role-$player_role"; ?>">
													<td><?= $play->player_id; ?></td>
													<td><?= $play->user_login; ?></td>
													<td><?= $play->player_first; ?></td>
													<td><?= $play->player_last; ?></td>
													<td><?= $play->player_email; ?></td>
													<td>
														<a target="_blank" href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adventure->adventure_id&player_id=$play->player_id"; ?>">
															<span class="icon icon-document"></span>
														</a>
													</td>
													<td class="roles">
														<?php if($play->player_id != $adventure->adventure_owner){ ?>
															<button class="form-ui role-button-player"  <?php if($player_role !='player'){ ?> onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'player'"; ?>);"  <?php } ?>  >
																<span class="icon icon-check"></span>
																<?= __("Player","bluerabbit"); ?>
															</button>
															<?php if($config['multiple_gms']['value']>0){  ?>
																<button class="form-ui role-button-gm"  <?php if($player_role != 'gm'){ ?> onClick="showOverlay('#confirm-gm-<?= $play->player_id; ?>');"  <?php } ?> >
																	<span class="icon icon-star"></span>
																	<?= __("GM","bluerabbit"); ?>
																</button>
																<div class="confirm-action overlay-layer" id="confirm-gm-<?= $play->player_id; ?>">
																	<button class="form-ui white-bg" onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'gm'"; ?>);">
																		<span class="icon-group">
																			<span class="icon-button font _24 sq-40  icon-sm teal-bg-400 icon-sm">
																				<span class="icon icon-activity white-color"></span>
																			</span>
																			<span class="icon-content">
																				<span class="line teal-400 font _18 w900"><?= __("Grant superpowers?","bluerabbit"); ?></span>
																			</span>
																		</span>
																	</button>
																	<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
																		<span class="icon icon-cancel white-color"></span>
																	</button>
																</div>
															<?php } ?>
															<button class="form-ui role-button-npc" <?php if($player_role != 'npc'){ ?> onClick="setPlayerAdventureRole(<?= "$adventure->adventure_id, $play->player_id, 'npc'"; ?>);"  <?php } ?>  >
																<span class="icon icon-carrot"></span>
																<?= __("NPC","bluerabbit"); ?>
															</button>
														<?php }else{ ?>
															<span class="icon icon-star amber-500"></span><strong><?= __("Owner","bluerabbit"); ?></strong>
														<?php } ?>
													</td>
													<td>
														<?php if($player_role != 'gm') { ?>
															<button class="form-ui icon-sm red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?= $play->player_id; ?>');">
																<?= __("Remove","bluerabbit"); ?>
															</button>
															<div class="confirm-action overlay-layer" id="confirm-option-<?= $play->player_id; ?>">
																<button class="form-ui white-bg" onClick="updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id, 'out'"; ?>);">
																	<span class="icon-group">
																		<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
																			<span class="icon icon-cancel white-color"></span>
																		</span>
																		<span class="icon-content">
																			<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
																		</span>
																	</span>
																</button>
																<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
																	<span class="icon icon-cancel white-color"></span>
																</button>
															</div>
															<?php if($config['allow_gm_reset_password']['value'] > 0){ ?>
																<button class="form-ui icon-sm red-bg-200 white-color" onClick="loadContent('update-player-password',<?= $play->player_id; ?>);">
																	<?= __("Update Password","bluerabbit"); ?>
																</button>
															<?php } ?>
														<?php }else{ ?>
															<?php if($play->player_id == $adventure->adventure_owner){ ?>
																<?= __("Owner","bluerabbit"); ?>
															<?php }else{ ?>
																<button class="form-ui icon-sm grey-bg-200 grey-300" disabled>
																	<?= __("Can't remove a GM","bluerabbit"); ?>
																</button>
															<?php } ?>
														<?php } ?>
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="highlight padding-10 grey-bg-200 h-60" id="add-players">
								<div class="icon-group">
									<span class="icon-button font _24 sq-40  orange-bg-200">
										<span class="icon icon-players white-color"></span>
									</span>
									<span class="icon-content font w500 _26">
										<span class="line font _24 w300"><?= __("Add players","bluerabbit"); ?></span>
									</span>
								</div>
								<a href="<?= get_bloginfo('template_directory');?>/sources/bulk-upload-users.csv" class="form-ui button blue-bg-700 white-color pull-right" target="_blank">
									<?= __("Download CSV Template","bluerabbit"); ?>
								</a>
							</div>
							<div class="content">
								<div class="highlight">
									<div class="form-ui font _14">
										<form id="upload_bulk_users_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
										
											<table>
												<tbody>
													<tr>
														<td class="w-200">
															<label for="the_csv_file_with_users" class="">Select CSV File:</label>
															<input type="file" name="the_csv_file_with_users" id="the_csv_file_with_users" size="20" />
														</td>
														<td class="w-100">
															<button type="button" onClick="uploadBulkUsers();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
														</td>
													</tr>
												</tbody>
											</table>
										</form>
										
									</div>
								</div>
								<div class="row">
									<table class="table" id="just-uploaded-users">
										<thead>
											<tr>
												<td><input type="checkbox" id="select-all"></td>
												<td><?= __("Nickname","bluerabbit"); ?></td>
												<td><?= __("Password","bluerabbit"); ?></td>
												<td><?= __("Email","bluerabbit"); ?></td>
												<td><?= __("First Name","bluerabbit"); ?></td>
												<td><?= __("Last Name","bluerabbit"); ?></td>
												<td><?= __("Lang","bluerabbit"); ?></td>
												<td><?= __("Status","bluerabbit"); ?></td>
											</tr>
										</thead>
										<tbody id="just-uploaded-users-body">
										</tbody>
									</table>
									<div class="call-to-action highlight text-center" id="call-to-action">
									</div>
								</div>
								<div class="highlight padding-10 grey-bg-200 h-60" id="add-players">
									<div class="icon-group">
										<span class="icon-button font _24 sq-40  orange-bg-200">
											<span class="icon icon-players white-color"></span>
										</span>
										<span class="icon-content font w500 _26">
											<span class="line font _24 w300"><?= __("Add single player","bluerabbit"); ?></span>
										</span>
									</div>
								</div>
								<div class="add-single-player">
									<div class="username-search-form">
										<h3><?= __("Check if Username or Email exists","bluerabbit"); ?></h3>
										<input class="form-ui" type="text" id="username-search" maxlength="30" placeholder="<?= __("Nickname or Email","bluerabbit");?>" onBlur="checkUserDataExists(this);">
									</div>
									<div id="new-player-warnings" class="new-player-warnings">
									</div>
									<div id="add-single-player-form" class="add-single-player-form">
										<div class="player-data-content">
											<div class="row nickame">
												<h3><?= __("Nickname","bluerabbit"); ?></h3>
												<input type="hidden" id="new-player-lang" value="<?= $current_player->player_lang;?>">
												<input class="form-ui" type="text" id="new-player-username" maxlength="30" placeholder="<?= __("Nickname","bluerabbit");?>">
											</div>
											<div class="row email">
												<h3><?= __("Email","bluerabbit"); ?></h3>
												<input class="form-ui" type="email" id="new-player-email" maxlength="255" placeholder="<?= __("Email","bluerabbit");?>">
											</div>
											<div class="row password">
												<h3><?= __("Password","bluerabbit"); ?></h3>
												<input class="form-ui" type="text" id="new-player-user-password" maxlength="18" placeholder="<?= __("Password","bluerabbit");?>">
											</div>
										</div>
										<div class="player-data-actions">
											<button id="btn-reg-player" class="form-ui"><?= __("Register player","bluerabbit");?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="tab max-w-900 padding-10" id="features">
						<div class="highlight padding-10 grey-bg-100">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  blue-bg-800"><span class="icon icon-config"></span></span>
								<span class="icon-content font _24 grey-700"><?= __("Features","bluerabbit"); ?></span>
								<span class="icon-content">
								<button class="button form-ui" onClick="allToggleButtonsOn('#features');"><?= __("All On","bluerabbit"); ?></button>
								<button class="button form-ui red-bg-400" onClick="allToggleButtonsOff('#features');"><?= __("All Off","bluerabbit"); ?></button>
								</span>
							</span>
						</div>
						<div class="content" id="tutorial-adventure-features">
							<table class="table w-full" cellpadding="5">
								<thead>
									<tr>
										<td class="w-200"><?= __("Setting","bluerabbit"); ?></td>
										<td><?= __("Value","bluerabbit"); ?></td>
									</tr>
								</thead>
								<tbody>
									<?php $all_features = array_merge($features, $adv_config); ?>
									<?php foreach($all_features as $sKey=>$s){ ?>
										<?php if($s['type'] != 'number') { ?>
											<tr id="<?=$sKey; ?>" class="setting">				
												<td>
													<span class="font _16 block black w600">
														<?php if(isset($s['icon'])) { ?>
															<span class="icon icon-<?= $s['icon']; ?>"></span>
														<?php } ?>
													<?= $s['label']; ?></span>
													<?php if(isset($s['desc'])) { ?>
														<span class="font _12 block grey-500"><?= $s['desc']; ?></span>
													<?php } ?>

												</td>
												<td>
													<?php if($s['type']=='radio'|| $s['type']=='checkbox'){ ?>
														<button class="toggle-button <?= (isset($adv_settings[$sKey]['value']) &&($adv_settings[$sKey]['value']) != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#<?=$sKey; ?>');">&nbsp;</button>
														<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
													<?php }elseif($s['type']=='text'){ ?>
														<input class="form-ui setting-value" type="text" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
													<?php }elseif($s['type']=='select'){ ?>
														<select class="form-ui setting-value">
															<?php foreach($s['options'] as $opt){ ?>
																<option <?php if(isset($adv_settings[$sKey]['value']) && $adv_settings[$sKey]['value'] == $opt[0]) { echo 'selected';} ?> value="<?= $opt[0]; ?>">
																	<?= $opt[1]; ?>
																</option>
															<?php } ?>
														</select>
													<?php }?>
													<input class="setting-id" type="hidden" value="<?= isset($adv_settings[$sKey]['id']) ? $adv_settings[$sKey]['id'] : ""; ?>" >
													<input class="setting-name" type="hidden" value="<?=$sKey; ?>" >
													<input class="setting-label" type="hidden" value="<?= $s['label']; ?>" >
												</td>
											</tr>
										<?php }else{ ?>
											<?php if($sKey == 'max_players' || $sKey == 'max_adventures' ) { ?>
												<tr id="<?=$sKey; ?>" class="setting">
													<td>
														<span class="font _16 block black w600">
															<?php if(isset($s['icon'])) { ?>
																<span class="icon icon-<?= $s['icon']; ?>"></span>
															<?php } ?>
														<?= $s['label']; ?></span>
														<?php if($s['desc']) { ?>
															<span class="font _12 block grey-500"><?= $s['desc']; ?></span>
														<?php } ?>
													</td>
													<td>
														<span class="font _16"><?=$s[$f_role]; ?></span>
														<input class="setting-value" type="hidden" readonly disabled value="<?=$s[$f_role]; ?>" >
														<input class="setting-id" type="hidden" value="<?= isset($adv_settings[$sKey]['id']) ? $adv_settings[$sKey]['id'] : ""; ?>" >
														<input class="setting-name" type="hidden" value="<?=$sKey; ?>" >
														<input class="setting-label" type="hidden" value="<?= $s['label']; ?>" >
													</td>
												</tr>
											<?php }else{ ?>
												<tr id="<?=$sKey; ?>" class="setting">
													<td>
														<span class="font _16 block black w600">
															<?php if(isset($s['icon'])) { ?>
																<span class="icon icon-<?= $s['icon']; ?>"></span>
															<?php } ?>
														<?= $s['label']; ?></span>
														<?php if($s['desc']) { ?>
															<span class="font _12 block grey-500"><?= $s['desc']; ?></span>
														<?php } ?>
													</td>
													<td>
														<input class="form-ui setting-value" max="<?=$s[$f_role]; ?>" type="number" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
														<input class="setting-id" type="hidden" value="<?= isset($adv_settings[$sKey]['id']) ? $adv_settings[$sKey]['id'] : ""; ?>" >
														<input class="setting-name" type="hidden" value="<?=$sKey; ?>" >
														<input class="setting-label" type="hidden" value="<?= $s['label']; ?>" >
													</td>
												</tr>
											<?php } ?>
										<?php } ?>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab" id="image_settings">
						<h2 class="font _30 padding-10 blue-700 w300">
							<span class="icon icon-image"></span><?=__("Images","bluerabbit");?>
						</h2>
						<div class="gallery">
							<?php foreach($image_types as $iKey=>$img){ ?>
								<?php 
								$desc_warning = '';
								if(isset($adv_settings[$iKey]['value'])){
									$img_url = $adv_settings[$iKey]['value']; 
								}else{
									$img_url = $config[$iKey]['value'] ;
									if(($config[$iKey]['value'])){
										$desc_warning = __("System Default","bluerabbit");
									}else{
										$desc_warning = __("Using Default","bluerabbit");
									}
								}
								?>
								<div class="gallery-item setting">
									<div class="gallery-image-thumb" style="background-image: url(<?= $img_url; ?>);" id="<?=$iKey;?>_thumb"></div>
									<div class="gallery-item-options">
										<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('<?=$iKey; ?>');"><span class="icon icon-image"></span></button>
										<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?=$iKey; ?>');"> <span class="icon icon-trash"></span> </button>
									</div>
									<div class="gallery-item-description white-color foreground">
										<div class="background black-bg opacity-50"></div>
										<h3 class="foreground font _18 w600 padding-10"><?=$img['label']; ?></h3>
										<?php if(isset($img['desc'])){ ?>
											<h5 class="foreground font _12 w600 padding-10"><?=$img['desc']; ?></h5>
										<?php } ?>
										<?php if(isset($desc_warning)){ ?>
											<h5 class="foreground font _12 w500 amber-500 padding-10">
												<span class="icon icon-warning"></span>
												<?=$desc_warning ?>
											</h5>
										<?php } ?>
									</div>
									<input class="form-ui setting-value"  id="<?=$iKey; ?>" type="hidden" value="<?= isset($adv_settings[$iKey]['value']) ? $adv_settings[$iKey]['value'] : ""; ?>">
									<input class="form-ui setting-id" type="hidden" value="<?= isset($adv_settings[$iKey]['id']) ? $adv_settings[$iKey]['id'] : ""; ?>" >
									<input class="form-ui setting-name" type="hidden" value="<?=$iKey; ?>" >
									<input class="form-ui setting-label" type="hidden" value="<?=$img['label']; ?>" >

								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<div class="tabi-editor-container" id="tabi-editor-container">
		
	</div>

	<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
	<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
	<input type="hidden" id="level-nonce" value="<?php echo wp_create_nonce('level_nonce'); ?>" />
	<input type="hidden" id="title-nonce" value="<?php echo wp_create_nonce('title_nonce'); ?>" />
	<input type="hidden" id="dimensions-nonce" value="<?php echo wp_create_nonce('dimensions_nonce'); ?>" />
	<input type="hidden" id="tabi-on-journey-nonce" value="<?php echo wp_create_nonce('tabi_on_journey_nonce'); ?>" />
	<input type="hidden" id="add-tabi-nonce" value="<?php echo wp_create_nonce('add_tabi_nonce'); ?>" />



<?php include (get_stylesheet_directory() . '/footer.php'); ?>

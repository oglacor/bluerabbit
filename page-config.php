<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($isAdmin){?>
<?php
	global $wpdb;
	$adventures = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_status='publish' AND adventure_type != 'template'" );
	$config = getSysConfig();
	$sysFeatures = getFeatures();

	
	$settings_array = array(
		'general_settings' => array(
			'icon'=>'tools',
			'label'=>__("General","bluerabbit"),
			'settings'=>array(
				'google_property_id' => array(
					'label'=>__("Google Property ID","bluerabbit"),
					'type'=>'text',
				),
				'registration' => array(
					'label'=>__("Registration Open","bluerabbit"),
					'type'=>'radio',
				),
				'restrict_domain' => array(
					'label'=>__("Restrict Domain registration?","bluerabbit"),
					'type'=>'radio',
				),
				'restrict_domain_url' => array( 
					'label'=>__("Domain Restriction for new users","bluerabbit"),
					'placeholder'=>__("domain.com","bluerabbit"),
					'type'=>'text',
				),
				'default_adventure' => array(
					'label'=>__("Default Adventure","bluerabbit"),
					'type'=>'select-adventure',
					'options' => $adventures,
				),
				'display_admin_nav_bar' => array(
					'label'=>__("Display Admin Nav Bar","bluerabbit"),
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
				'add_new_adventure' => array(
					'label'=>__("Add New Adventure","bluerabbit"),
					'type'=>'select',
					'options' => array(
						array("admin",__("Administrator","bluerabbit")),
						array("gm",__("Game Masters","bluerabbit")),
						array("npc",__("Non Player Characters","bluerabbit")),
						array("all",__("Everyone","bluerabbit")),
					),
				),
				'add_adventure_from_template' => array(
					'label'=>__("Add Adventure From Template","bluerabbit"),
					'type'=>'select',
					'options' => array(
						array("admin",__("Administrator","bluerabbit")),
						array("gm",__("Game Masters","bluerabbit")),
						array("npc",__("Non Player Characters","bluerabbit")),
						array("all",__("Everyone","bluerabbit")),
					),
				),
				'allow_gm_reset_password' => array(
					'label'=>__("Let GMs and NPCs reset player's password","bluerabbit"),
					'type'=>'radio',
				),
				'default_theme' => array(
					'label'=>__("Default Theme for adventures","bluerabbit"),
					'type'=>'select',
					'options' => array(
						array("default",__("Default","bluerabbit")),
						array("pirate",__("Pirate","bluerabbit")),
					),
				),

				'resource_auto_fill' => array(
					'label'=>__("Resource Autofill","bluerabbit"),
					'type'=>'select',
					'options' => array(
						array("0",__("Off","bluerabbit")),
						array("65",__("Easy","bluerabbit")),
						array("50",__("Normal","bluerabbit")),
						array("35",__("Hard","bluerabbit")),
						array("25",__("Very Hard","bluerabbit")),
						array("10",__("Legendary","bluerabbit")),
					),
				),
				'adventure_privacy' => array(
					'label'=>__("Allow Public Adventures","bluerabbit"),
					'type'=>'radio',
				),
				'multiple_gms' => array(
					'label'=>__("Multiple Game Masters","bluerabbit"),
					'type'=>'radio',
				),
				'support_email' => array( 
					'label'=>__("Default support email","bluerabbit"),
					'placeholder'=>__("help@bluerabbit.io","bluerabbit"),
					'type'=>'text',
				),
			),
		),
		'profile_settings' => array(
			'icon'=>'profile',
			'label'=>__("Profile","bluerabbit"),
			'settings'=>array(
				'default_language' => array(
					'label'=>__("Default Language","bluerabbit"),
					'type'=>'select',
					'options' => array(
						array( "en_US", "U.S. English" ),
						array( "es_MX", "Espa&ntilde;ol" ),
					),
				),
				'show_upgrade' => array(
					'label'=>__("Show Upgrade","bluerabbit"),
					'type'=>'radio',
				),
				'show_anonimize_button' => array(
					'label'=>__("Show Anonimize Button","bluerabbit"),
					'type'=>'radio',
				),
				'use_hexad' => array(
					'label'=>__("Use HEXAD Player Type Test","bluerabbit"),
					'type'=>'radio',
				),
			),
		),
		'labels_settings' => array(
			'icon'=>'narrative',
			'label'=>__("Custom Labels","bluerabbit"),
			'settings'=>array(
				'journey_label' => array(
					'label'=>__("Journey section name","bluerabbit"),
					'type'=>'text',
				),
				'item_shop_label' => array(
					'label'=>__("Item Shop section name","bluerabbit"),
					'type'=>'text',
				),
				'leaderboard_label' => array(
					'label'=>__("Leaderboard section name","bluerabbit"),
					'type'=>'text',
				),
				'blog_label' => array(
					'label'=>__("Blog section name","bluerabbit"),
					'type'=>'text',
				),
				'lore_label' => array(
					'label'=>__("Resources section name","bluerabbit"),
					'type'=>'text',
				),
				'wall_label' => array(
					'label'=>__("Wall section name","bluerabbit"),
					'type'=>'text',
				),
				'magic_code_label' => array(
					'label'=>__("Magic Code button name","bluerabbit"),
					'type'=>'text',
				),
				'secrets_and_clues_label' => array(
					'label'=>__("Secrets and Clues section name","bluerabbit"),
					'type'=>'text',
				),
				'achievements_label' => array(
					'label'=>__("Achievements section name","bluerabbit"),
					'type'=>'text',
				),
				'backpack_label' => array(
					'label'=>__("Backpack section name","bluerabbit"),
					'type'=>'text',
				),
				'guilds_label' => array(
					'label'=>__("Guilds section name","bluerabbit"),
					'type'=>'text',
				),
				'blockers_label' => array(
					'label'=>__("Blocker section name","bluerabbit"),
					'type'=>'text',
				),
			),
		),
/*
		'miscelaneous_settings' => array(
			'icon'=>'mystery',
			'label'=>__("Miscelaneous","bluerabbit"),
			'settings'=>array(
			),
		),
		'custom_settings' => array(
			'icon'=>'carrot',
			'label'=>__("Custom","bluerabbit"),
			'settings'=>array(
				'use_access_code' => array(
					'label'=>__("Use Access Code","bluerabbit"),
					'type'=>'radio',
				),
				'access_code_value' => array(
					'label'=>__("Access Code Value","bluerabbit"),
					'type'=>'text',
				),
				'access_code_time_limit' => array(
					'label'=>__("Access Code Time Limit","bluerabbit"),
					'type'=>'number',
				),
				'access_code_video' => array(
					'label'=>__("Video on access code failure","bluerabbit"),
					'type'=>'upload',
				),
			),
		),
*/
	);
	$image_types = array(
		'main_logo' => array(
			'label' => __("Main Logo","bluerabbit"),
			'desc' => __("The main logo to use as default","bluerabbit"),
		),
		'default_bg' => array('label' => __("Default Background","bluerabbit"),	),
		'login_logo' => array(
			'label' => __("Login Logo","bluerabbit"),
			'desc' => __("This logo only appears in the login screen. If the Background is dark, use a light logo","bluerabbit"),
		),
		'login_bg' => array('label' => __("Login Background","bluerabbit"),	),
		'favicon' => array(
			'label' => __("Favicon","bluerabbit"),
			'desc' => __("The favicon for the site","bluerabbit"),
		),
		
		'no_adventure_badge' => array(
			'label' => __("No adventure Image","bluerabbit"),
			'desc' => __("The default image for No Adventures","bluerabbit"),
		),
		
		
		'journey_bg' => array('label' => __("Journey Background","bluerabbit"),	),
		'item_shop_bg' => array('label' => __("Item shop Background","bluerabbit"),	),
		'backpack_bg' => array('label' => __("Backpack Background","bluerabbit"),	),
		'guilds_bg' => array('label' => __("Guilds Background","bluerabbit"),	),
		'schedule_bg' => array('label' => __("Schedule Background","bluerabbit"),	),
		'blog_bg' => array('label' => __("Blog Background","bluerabbit"),	),
		'lore_bg' => array('label' => __("Lore Background","bluerabbit"),	),
		'wall_bg' => array('label' => __("Wall Background","bluerabbit"),	),
		'leaderboard_bg' => array('label' => __("Leaderboard Background","bluerabbit"),	),
		'my_work_bg' => array('label' => __("My Work Background","bluerabbit"),	),
	);
	$sponsors = getSponsors();
	$orgs = getOrgs();
?>

<h1 class="font _30 padding-20 text-center blue-bg-400 white-color w700">
	<span class="icon-button font _24 sq-40  white-bg blue-700"><span class="icon icon-logo"></span></span>
	<?php _e("System Settings","bluerabbit"); ?>
</h1>
<div class="dashboard white-bg">
		<div class="dashboard-sidebar grey-bg-800 relative padding-10">
			<div class="background black-gradient"></div>
			<div class="foreground" id="main-tabs-buttons">
				<ul class="margin-0 padding-0">
					<?php foreach($settings_array as $sgKey=>$sg){ ?>
						<li onClick="switchTabs('#main-tabs','#<?=$sgKey; ?>');" class="block white-color cursor-pointer tab-button relative" id="<?=$sgKey; ?>-tab-button">
							<div class="inactive-content padding-5">
								<span class="icon icon-<?=$sg['icon']; ?>"></span><?=$sg['label']; ?>
							</div>
							<div class="active-content background blue-grey-bg-100"></div>
							<div class="active-content padding-5 grey-900 foreground">
								<span class="icon icon-<?=$sg['icon']; ?>"></span><?=$sg['label']; ?>
							</div>
						</li>
					<?php } ?>
					<li onClick="switchTabs('#main-tabs','#features');" class="block white-color cursor-pointer tab-button relative" id="features-tab-button">
						<div class="inactive-content padding-5">
							<span class="icon icon-teamwork"></span><?= __("Features","bluerabbit"); ?>
						</div>
						<div class="active-content background blue-grey-bg-100"></div>
						<div class="active-content padding-5 grey-900 foreground">
							<span class="icon icon-teamwork"></span><?= __("Features","bluerabbit"); ?>
						</div>
					</li>
					<li onClick="switchTabs('#main-tabs','#image_settings');" class="block white-color cursor-pointer tab-button relative" id="image_settings-tab-button">
						<div class="inactive-content padding-5">
							<span class="icon icon-image"></span><?= __("Images","bluerabbit");?>
						</div>
						<div class="active-content background orange-bg-700"></div>
						<div class="active-content padding-5 white-color foreground">
							<span class="icon icon-image"></span><?= __("Images","bluerabbit");?>
						</div>
					</li>
					<li onClick="switchTabs('#main-tabs','#organizations_settings');" class="block white-color cursor-pointer tab-button relative" id="organizations_settings-tab-button">
						<div class="inactive-content padding-5">
							<span class="icon icon-wall"></span><?= __("Organizations","bluerabbit");?>
						</div>
						<div class="active-content background pink-bg-700"></div>
						<div class="active-content padding-5 white-color foreground">
							<span class="icon icon-wall"></span><?= __("Organizations","bluerabbit");?>
						</div>
					</li>
					<li onClick="switchTabs('#main-tabs','#sponsors_settings');" class="block white-color cursor-pointer tab-button relative" id="sponsors_settings-tab-button">
						<div class="inactive-content padding-5">
							<span class="icon icon-carrot"></span><?= __("Sponsors","bluerabbit");?>
						</div>
						<div class="active-content background orange-bg-700"></div>
						<div class="active-content padding-5 white-color foreground">
							<span class="icon icon-carrot"></span><?= __("Sponsors","bluerabbit");?>
						</div>
					</li>
					<li onClick="switchTabs('#main-tabs','#settings-reference');" class="block white-color cursor-pointer tab-button relative" id="settings-reference-tab-button">
						<div class="inactive-content padding-5">
							<span class="icon icon-story"></span><?= __("Reference","bluerabbit");?>
						</div>
						<div class="active-content background blue-grey-bg-500"></div>
						<div class="active-content padding-5 white-color foreground">
							<span class="icon icon-story"></span><?= __("Reference","bluerabbit");?>
						</div>
					</li>
				</ul>
				<button class="form-ui w-full margin-5 green-bg-400" onClick="saveSysConfig();">
					<span class="icon icon-config"></span>
					<?php _e("Save Settings","bluerabbit");?>
				</button>
			</div>
		</div>
		<div class="dashboard-content padding-10">
			<div class="tabs w-full" id="main-tabs">
				<?php foreach($settings_array as $sgKey=>$sg){ ?>
					<div class="tab <?= $sgKey =='general_settings' ? 'active' : ''; ?>" id="<?=$sgKey; ?>">
						<h2 class="font _30 padding-10 blue-700 w300">
							<span class="icon icon-<?=$sg['icon']; ?>"></span><?=$sg['label']; ?>
						</h2>
						<button class="button form-ui" onClick="allToggleButtonsOn('#<?=$sgKey; ?>');"><?= __("All On","bluerabbit"); ?></button>
						<button class="button form-ui red-bg-400" onClick="allToggleButtonsOff('#<?=$sgKey; ?>');"><?= __("All Off","bluerabbit"); ?></button>
						<table class="table w-full" cellpadding="5">
							<thead>
								<tr>
									<td class="w-200"><?= __("Setting","bluerabbit"); ?></td>
									<td><?= __("Value","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach($sg['settings'] as $sKey=>$s){ ?>
									<tr id="<?=$sKey; ?>" class="config">				
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
											<?php if($s['type']=='radio'){ ?>
												<button class="toggle-button <?= ($config[$sKey]['value']!=0) ? 'active' : ''; ?>" onClick="toggleSetting('#<?=$sKey; ?>');">&nbsp;</button>
												<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= $config[$sKey]['value']; ?>">
											<?php }elseif($s['type']=='text'){ ?>
												<input class="form-ui setting-value" type="text" value="<?= $config[$sKey]['value']; ?>" placeholder="<?= isset($s['placeholder']) ? $s['placeholder'] : "" ; ?>" >
											<?php }elseif($s['type']=='number'){ ?>
												<input class="form-ui setting-value" type="number" value="<?= $config[$sKey]['value']; ?>">
											<?php }elseif($s['type']=='select'){ ?>
												<select class="form-ui setting-value">
													<?php foreach($s['options'] as $opt){ ?>
														<option <?= isset($config[$sKey]['value']) && $config[$sKey]['value'] == $opt[0] ? 'selected' : ""; ?> value="<?= $opt[0]; ?>">
															<?= $opt[1]; ?>
														</option>
													<?php } ?>
												</select>
											<?php }elseif($s['type']=='select-adventure'){ ?>
												<select class="form-ui setting-value">
													<option <?php if(!$config['default_adventure']['value']){ echo 'selected'; }?> value="0"> <?php echo __('None','bluerabbit'); ?> </option>
													<?php foreach($adventures as $key=>$adv){ ?>
														<option <?php if($config['default_adventure']['value']==$adv->adventure_id){ echo 'selected'; }?> value="<?php echo $adv->adventure_id; ?>">
															<?php echo "$adv->adventure_title [ $adv->adventure_id ]"; ?> 
														</option>
													<?php }  ?>
												</select>
											<?php }elseif($s['type']=='upload'){ ?>
												<div class="input-group w-full">
													<input class="form-ui setting-value" id="<?=$sKey; ?>_upload_field" type="text" readonly placeholder="<?= __("File upload","bluerabbit");?>" value="<?= $config[$sKey]['value']; ?>">
													<label class="grey-bg-700">
														<button class="form-ui orange-bg-400" onClick="showWPUpload('<?=$sKey; ?>_upload_field');">
															<?= __("Upload","bluerabbit");?>
														</button>
													</label>
												</div>
											<?php }elseif($s['type']=='image'){ ?>
												<div class="gallery block">
													<div class="gallery-item setting">
														<div class="gallery-image-thumb" style="background-image: url(<?= $config[$sKey]['value']; ?>);" id="<?=$sKey;?>_thumb"></div>
														<div class="gallery-item-options">
															<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('<?=$sKey; ?>');"><span class="icon icon-image"></span></button>
															<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#<?=$sKey; ?>');"> <span class="icon icon-trash"></span> </button>
														</div>
														<div class="gallery-item-description white-color foreground">
															<div class="background black-bg opacity-50"></div>
															<h3 class="foreground font _18 w600 padding-10"><?=$s['label']; ?></h3>
															<?php if($s['desc']){ ?>
																<h5 class="foreground font _12 w600 padding-10"><?=$s['desc']; ?></h5>
															<?php } ?>
														</div>
													</div>
												</div>
											<?php }?>
											<input class="form-ui setting-id" type="hidden" value="<?=isset($config[$sKey]['id']) ? $config[$sKey]['id'] : ""; ?>" >
											<input class="form-ui setting-name" type="hidden" value="<?= $sKey;?>" >
											<input class="form-ui setting-label" type="hidden" value="<?= $s['label']; ?>" >
											<input class="form-ui setting-desc" type="hidden" value="<?= isset($s['desc']) ? $s['desc'] : ""; ?>" >
											<input class="form-ui setting-type" type="hidden" value="<?= $s['type']; ?>" >
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>
				<!--FEATURES -->
				<?php
					$features = array(
						'use_challenges' => array(
							'label'=>__("Use Challenges","bluerabbit"),'type'=>'checkbox',
						),
						'use_missions' => array(
							'label'=>__("Use Missions","bluerabbit"),'type'=>'checkbox',
						),
						'use_encounters' => array(
							'label'=>__("Use Encounters","bluerabbit"),'type'=>'checkbox',
						),
						'use_achievements' => array(
							'label'=>__("Use Achievements","bluerabbit"),'type'=>'checkbox',
						),
						'use_magic_codes' => array(
							'label'=>__("Use Magic Codes","bluerabbit"),'type'=>'checkbox',
						),
						'use_surveys' => array(
							'label'=>__("Use Surveys","bluerabbit"),'type'=>'checkbox',
						),
						'use_blockers' => array(
							'label'=>__("Use Blockers","bluerabbit"),'type'=>'checkbox',
						),
						'use_wall' => array(
							'label'=>__("Use Wall","bluerabbit"),'type'=>'checkbox',
						),
						'use_guilds' => array(
							'label'=>__("Use Guilds","bluerabbit"),'type'=>'checkbox',
						),
						'use_blog' => array(
							'label'=>__("Use Blog","bluerabbit"),'type'=>'checkbox',
						),
						'use_lore' => array(
							'label'=>__("Use Lore","bluerabbit"),'type'=>'checkbox',
						),
						'use_leaderboard' => array(
							'label'=>__("Use Leaderboard","bluerabbit"),'type'=>'checkbox',
						),
						'leaderboard_limit' => array(
							'label'=>__("Players in leaderboard","bluerabbit"),'type'=>'number',
						),
						'use_schedule' => array(
							'label'=>__("Use Schedule","bluerabbit"),'type'=>'checkbox',
						),
						'use_speakers' => array(
							'label'=>__("Use Speakers","bluerabbit"),'type'=>'checkbox',
						),
						'use_backpack' => array(
							'label'=>__("Use Backpack","bluerabbit"),'type'=>'checkbox',
						),
						'use_item_shop' => array(
							'label'=>__("Use Item Shop","bluerabbit"),'type'=>'checkbox',
						),
						'rate_quests' => array(
							'label'=>__("Allow players to rate quests","bluerabbit"),'type'=>'checkbox',
						),
						'show_adventure_status' => array(
							'label'=>__("Show adventure status","bluerabbit"),'type'=>'checkbox',
							'desc'=>__("Status bars that show the added XP, EP and Coins of all players in the same adventure","bluerabbit"),

						),
						'max_players' => array(
							'label'=>__("Max Players per adventure","bluerabbit"),'type'=>'number',
						),
						'max_adventures' => array(
							'label'=>__("Max Adventures per player","bluerabbit"),'type'=>'number',
						),
					);
				?>
				<div class="tab features" id="features">
					<h2 class="font _30 padding-10 blue-700 w300">
						<span class="icon icon-teamwork"></span><?= __("Features","bluerabbit"); ?>
					</h2>
					<button class="button form-ui" onClick="allToggleButtonsOn('#features');"><?= __("All On","bluerabbit"); ?></button>
					<button class="button form-ui red-bg-400" onClick="allToggleButtonsOff('#features');"><?= __("All Off","bluerabbit"); ?></button>
					<table class="table w-full" cellpadding="5">
						<thead>
							<tr>
								<td><?= __("Setting","bluerabbit"); ?></td>
								<td class="w-100 text-center cursor-pointer" onClick="checkAllFeatures('free');"><?= __("Free","bluerabbit"); ?></td>
								<td class="w-100 text-center cursor-pointer" onClick="checkAllFeatures('pro');"><?= __("Pro","bluerabbit"); ?></td>
								<td class="w-100 text-center cursor-pointer" onClick="checkAllFeatures('admin');"><?= __("Admin","bluerabbit"); ?></td>
								<td class="w-100 text-center cursor-pointer" onClick="checkAllFeatures('god');"><?= __("God","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody class="text-center">
							<?php foreach($features as $fKey=>$f){ ?>
								<tr id="<?=$fKey; ?>" class="feature">				
									<td class="font _16 block black w600 text-left">
										<?= $f['label']; ?>
										<?php if(isset($f['desc'])) { ?>
											<span class="font _12 block grey-500"><?= $f['desc']; ?></span>
										<?php } ?>
										<input class="form-ui feature-id" type="hidden" value="<?=$sysFeatures[$fKey]['id']; ?>" >
										<input class="form-ui feature-name" type="hidden" value="<?= $fKey;?>" >
										<input class="form-ui feature-label" type="hidden" value="<?= $f['label']; ?>" >
										<input class="form-ui feature-type" type="hidden" value="<?= $f['type']; ?>" >
										<input class="form-ui feature-desc" type="hidden" value="<?= isset($f['desc']) ? $f['desc'] : ""; ?>" >
									</td>
									<?php if($f['type']=='checkbox'){ ?>
										<td><input type="checkbox" class="feature-free" <?= $sysFeatures[$fKey]['free'] ? 'checked' : ''; ?>></td>
										<td><input type="checkbox" class="feature-pro" <?= $sysFeatures[$fKey]['pro'] ? 'checked' : ''; ?>></td>
										<td><input type="checkbox" class="feature-admin" <?= $sysFeatures[$fKey]['admin'] ? 'checked' : ''; ?>></td>
										<td><input type="checkbox" class="feature-god" <?= $sysFeatures[$fKey]['god'] ? 'checked' : ''; ?>></td>
									<?php }elseif($f['type']=='number'){ ?>
										<td><input type="number" class="feature-free form-ui w-100" value="<?= $sysFeatures[$fKey]['free']; ?>"></td>
										<td><input type="number" class="feature-pro form-ui w-100" value="<?= $sysFeatures[$fKey]['pro']; ?>"></td>
										<td><input type="number" class="feature-admin form-ui w-100" value="<?= $sysFeatures[$fKey]['admin']; ?>"></td>
										<td><input type="number" class="feature-god form-ui w-100" value="<?= $sysFeatures[$fKey]['god']; ?>"></td>
									<?php } ?>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				
				<!--FEATURES END -->
				
				
				
				<div class="tab" id="image_settings">
					<h2 class="font _30 padding-10 blue-700 w300">
						<span class="icon icon-image"></span><?=__("Images","bluerabbit");?>
					</h2>
					<div class="gallery">
						<?php foreach($image_types as $iKey=>$img){ ?>
							<div class="gallery-item config">
								<div class="gallery-image-thumb" style="background-image: url(<?= $config[$iKey]['value']; ?>);" id="<?=$iKey;?>_thumb"></div>
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
								</div>
								<input class="form-ui setting-value"  id="<?=$iKey; ?>" type="hidden" value="<?= $config[$iKey]['value']; ?>">
								<input class="form-ui setting-id" type="hidden" value="<?=$config[$iKey]['id']; ?>" >
								<input class="form-ui setting-name" type="hidden" value="<?=$iKey; ?>" >
								<input class="form-ui setting-label" type="hidden" value="<?=$img['label']; ?>" >

							</div>
						<?php } ?>
					</div>
				</div>
				<div class="tab" id="organizations_settings">
					<h2 class="font _30 padding-10 pink-700 w300">
						<span class="icon icon-wall"></span><?=__("Organizations","bluerabbit");?>
					</h2>
					<table id="organizations" class="table">
						<thead>
							<tr>
								<td><?= __("ID","bluerabbit"); ?></td>
								<td><?= __("Name","bluerabbit"); ?></td>
								<td><?= __("Logo","bluerabbit"); ?></td>
								<td><?= __("Color","bluerabbit"); ?></td>
								<td><?= __("About","bluerabbit"); ?></td>
								<td><?= __("Owner","bluerabbit"); ?></td>
								<td><?= __("Actions","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($orgs as $sKey=>$org){ ?>
							<tr>
								<td><?= $org->org_id; ?></td>
								<td><?= $org->org_name ;?></td>
								<td><img src="<?= $org->org_logo ;?>" height="70"></td>
								<td><?= $org->org_color ;?></td>
								<td><?= apply_filters('the_content',$org->org_content) ;?></td>
								<td><?= $org->player_display_name ;?></td>
								<td>
									<a href="<?= get_bloginfo('url').'/organization/?id='.$org->org_id; ?>" class="form-ui blue-bg-400">
										<span class="icon icon-edit"></span><?= __("Manage","bluerabbit"); ?>
									</a>
									<button class="form-ui green-bg-400" onClick="loadContent('new-org',<?= $org->org_id; ?>);">
										<?= __("Edit","bluerabbit"); ?>
									</button>
									<button class="form-ui red-bg-400" onClick="trashOrg(<?= $org->org_id; ?>);">
										<?= __("Trash","bluerabbit"); ?>
									</button>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<button class="form-ui orange-bg-400 grey-900" onClick="loadContent('new-org');"><?= __("New Organization","bluerabbit"); ?></button>
				</div>
				<div class="tab" id="sponsors_settings">
					<h2 class="font _30 padding-10 blue-700 w300">
						<span class="icon icon-image"></span><?=__("Sponsors","bluerabbit");?>
					</h2>
					<table id="sponsors" class="table">
						<thead>
							<tr>
								<td><?= __("ID","bluerabbit"); ?></td>
								<td><?= __("Name","bluerabbit"); ?></td>
								<td><?= __("URL","bluerabbit"); ?></td>
								<td><?= __("Logo","bluerabbit"); ?></td>
								<td><?= __("Color","bluerabbit"); ?></td>
								<td><?= __("Level","bluerabbit"); ?></td>
								<td><?= __("Image","bluerabbit"); ?></td>
								<td><?= __("About","bluerabbit"); ?></td>
								<td><?= __("Twitter","bluerabbit"); ?></td>
								<td><?= __("LinkedIn","bluerabbit"); ?></td>
								<td><?= __("Actions","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($sponsors as $sKey=>$sponsor){ ?>
							<tr>
								<td><?= $sponsor->sponsor_id; ?></td>
								<td><?= $sponsor->sponsor_name ;?></td>
								<td><?= $sponsor->sponsor_url ;?></td>
								<td><img src="<?= $sponsor->sponsor_logo ;?>" height="70"></td>
								<td><?= $sponsor->sponsor_color ;?></td>
								<td><?= $sponsor->sponsor_level ;?></td>
								<td><img src="<?= $sponsor->sponsor_image ;?>" height="70"></td>
								<td><?= apply_filters('the_content',$sponsor->sponsor_about) ;?></td>
								<td><?= $sponsor->sponsor_twitter ;?></td>
								<td><?= $sponsor->sponsor_linkedin ;?></td>
								<td>
									<button class="form-ui green-bg-400" onClick="loadContent('new-sponsor',<?= $sponsor->sponsor_id; ?>);"><?= __("Edit","bluerabbit"); ?></button>
									<button class="form-ui red-bg-400" onClick=";confirmStatus(<?= $sponsor->sponsor_id; ?>,'sponsor','trash');"><?= __("Trash","bluerabbit"); ?></button>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<button class="form-ui orange-bg-400 grey-900" onClick="loadContent('new-sponsor');"><?= __("New sponsor","bluerabbit"); ?></button>
					<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
					<input type="hidden" id="reload" value="true"/>
				</div>
				<div class="tab" id="settings-reference">
					<h2 class="font _30 padding-10 blue-700 w300">
						<span class="icon icon-story"></span><?=__("Settings Code Reference","bluerabbit");?>
					</h2>
					<table class="table w-full font _16 padding-5" cellpadding="5">
						<thead>
							<tr>
								<td class="w-50"><?= __("ID","bluerabbit"); ?></td>
								<td class="w-200"><?= __("Setting","bluerabbit"); ?></td>
								<td class="w-200"><?= __("Value","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($config as $ckey=>$c){ ?>
								<tr>
									<td><?= $c['id']; ?></td>
									<td><?= $ckey; ?></td>
									<td class="font w900"><?= $c['value']; ?></td>
								</tr>
							<?php }	?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<br clear="all">
</div>
<?php //wp_enqueue_media();?>
<?php }else{ ?>
<script>document.location.href="<?php echo get_bloginfo('url')."/404"; ?>";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

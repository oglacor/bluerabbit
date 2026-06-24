<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($isAdmin){?>
<?php
	global $wpdb;
	$adventures = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_status='publish' AND adventure_type != 'template'" );
	$config = BR_Config::instance()->getSysConfig();
	$sysFeatures = BR_Config::instance()->getFeatures();
	$allPlans = BR_Config::instance()->getPlans('active');
	$allPlansAll = BR_Config::instance()->getPlans(null);

	
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
	$sponsors = BR_Session::instance()->getSponsors();
	$orgs = BR_Organization::instance()->getOrgs();
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(28,194,235,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(28,194,235,0.4)">
			<span class="icon icon-logo" style="font-size:28px;color:#1cc2eb"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= __("System Settings","bluerabbit"); ?></h1>
			<span class="br-page-subtitle">BlueRabbit</span>
		</div>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="config-tabs-buttons">
		<?php foreach($settings_array as $sgKey=>$sg){ ?>
		<button class="br-tab-btn <?= $sgKey == 'general_settings' ? 'active' : ''; ?>" onClick="brScrollTo('<?= $sgKey; ?>', this)">
			<span class="icon icon-<?= $sg['icon']; ?>"></span> <?= $sg['label']; ?>
		</button>
		<?php } ?>
		<button class="br-tab-btn" onClick="brScrollTo('features', this)"><span class="icon icon-teamwork"></span> <?= __("Features","bluerabbit"); ?></button>
		<button class="br-tab-btn" onClick="brScrollTo('plans_settings', this)"><span class="icon icon-rank"></span> <?= __("Plans","bluerabbit"); ?></button>
		<button class="br-tab-btn" onClick="brScrollTo('image_settings', this)"><span class="icon icon-image"></span> <?= __("Images","bluerabbit"); ?></button>
		<button class="br-tab-btn" onClick="brScrollTo('organizations_settings', this)"><span class="icon icon-wall"></span> <?= __("Organizations","bluerabbit"); ?></button>
		<button class="br-tab-btn" onClick="brScrollTo('sponsors_settings', this)"><span class="icon icon-carrot"></span> <?= __("Sponsors","bluerabbit"); ?></button>
		<button class="br-tab-btn" onClick="brScrollTo('settings-reference', this)"><span class="icon icon-story"></span> <?= __("Reference","bluerabbit"); ?></button>
	</div>

	<!-- Settings Sections -->
	<?php foreach($settings_array as $sgKey=>$sg){ ?>
	<div class="br-scroll-section" id="<?= $sgKey; ?>">
	<div class="br-panel">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
			<h3 class="br-panel-title" style="margin:0"><span class="icon icon-<?= $sg['icon']; ?>"></span> <?= $sg['label']; ?></h3>
			<div class="br-actions" style="gap:6px">
				<button class="br-btn" style="padding:4px 10px;font-size:11px" onClick="allToggleButtonsOn('#<?= $sgKey; ?>');"><?= __("All On","bluerabbit"); ?></button>
				<button class="br-btn br-btn-red" style="padding:4px 10px;font-size:11px" onClick="allToggleButtonsOff('#<?= $sgKey; ?>');"><?= __("All Off","bluerabbit"); ?></button>
			</div>
		</div>

		<div style="display:flex;flex-direction:column;gap:4px">
		<?php foreach($sg['settings'] as $sKey=>$s){ ?>
			<div class="br-step-row" style="border-left:3px solid rgba(28,194,235,0.3);cursor:default;align-items:flex-start;padding:10px 14px" id="<?= $sKey; ?>">
				<div style="flex:1;min-width:0" class="config">
					<span style="font-size:14px;font-weight:600;color:rgba(255,255,255,0.9);display:block">
						<?php if(isset($s['icon'])){ ?><span class="icon icon-<?= $s['icon']; ?>" style="opacity:0.5"></span> <?php } ?>
						<?= $s['label']; ?>
					</span>
					<?php if(isset($s['desc'])){ ?>
					<span style="font-size:11px;color:rgba(255,255,255,0.35)"><?= $s['desc']; ?></span>
					<?php } ?>
				</div>
				<div style="flex-shrink:0;min-width:180px" class="config">
					<?php if($s['type']=='radio'){ ?>
						<button class="toggle-button <?= ($config[$sKey]['value']!=0) ? 'active' : ''; ?>" onClick="toggleSetting('#<?= $sKey; ?>');">&nbsp;</button>
						<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= $config[$sKey]['value']; ?>">
					<?php }elseif($s['type']=='text'){ ?>
						<input class="br-input setting-value" type="text" value="<?= $config[$sKey]['value']; ?>" placeholder="<?= isset($s['placeholder']) ? $s['placeholder'] : ''; ?>" style="width:100%">
					<?php }elseif($s['type']=='number'){ ?>
						<input class="br-input setting-value" type="number" value="<?= $config[$sKey]['value']; ?>" style="width:100%">
					<?php }elseif($s['type']=='select'){ ?>
						<select class="br-input setting-value" style="width:100%">
							<?php foreach($s['options'] as $opt){ ?>
							<option <?= isset($config[$sKey]['value']) && $config[$sKey]['value'] == $opt[0] ? 'selected' : ''; ?> value="<?= $opt[0]; ?>"><?= $opt[1]; ?></option>
							<?php } ?>
						</select>
					<?php }elseif($s['type']=='select-adventure'){ ?>
						<select class="br-input setting-value" style="width:100%">
							<option <?= !$config['default_adventure']['value'] ? 'selected' : ''; ?> value="0"><?= __('None','bluerabbit'); ?></option>
							<?php foreach($adventures as $key=>$adv){ ?>
							<option <?= $config['default_adventure']['value']==$adv->adventure_id ? 'selected' : ''; ?> value="<?= $adv->adventure_id; ?>">
								<?= "$adv->adventure_title [ $adv->adventure_id ]"; ?>
							</option>
							<?php } ?>
						</select>
					<?php }elseif($s['type']=='upload'){ ?>
						<div style="display:flex;gap:6px;align-items:center">
							<input class="br-input setting-value" id="<?= $sKey; ?>_upload_field" type="text" readonly placeholder="<?= __("File upload","bluerabbit"); ?>" value="<?= $config[$sKey]['value']; ?>" style="flex:1">
							<button class="br-btn br-btn-amber" style="padding:4px 10px" onClick="showWPUpload('<?= $sKey; ?>_upload_field');"><?= __("Upload","bluerabbit"); ?></button>
						</div>
					<?php }elseif($s['type']=='image'){ ?>
						<div class="br-gallery br-gallery-single">
							<?php $thumb_id = $sKey; $file = $config[$sKey]['value']; include(TEMPLATEPATH . '/gallery-item.php'); ?>
						</div>
					<?php } ?>
					<input class="form-ui setting-id" type="hidden" value="<?= isset($config[$sKey]['id']) ? $config[$sKey]['id'] : ''; ?>">
					<input class="form-ui setting-name" type="hidden" value="<?= $sKey; ?>">
					<input class="form-ui setting-label" type="hidden" value="<?= $s['label']; ?>">
					<input class="form-ui setting-desc" type="hidden" value="<?= isset($s['desc']) ? $s['desc'] : ''; ?>">
					<input class="form-ui setting-type" type="hidden" value="<?= $s['type']; ?>">
				</div>
			</div>
		<?php } ?>
		</div>
	</div>
	</div>
	<?php } ?>
	<!-- Features -->
	<?php $dbFeatures = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_features ORDER BY feature_id ASC"); ?>
	<div class="br-scroll-section" id="features">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-teamwork"></span> <?= __("Features","bluerabbit"); ?></h3>
		<span class="br-form-hint" style="display:block;margin:-12px 0 16px"><?= __("Manage the system features available to plans. Add or remove features as your platform grows.","bluerabbit"); ?></span>

		<table class="br-table">
			<thead>
				<tr>
					<th style="width:50px"><?= __("ID","bluerabbit"); ?></th>
					<th><?= __("Name","bluerabbit"); ?></th>
					<th><?= __("Label","bluerabbit"); ?></th>
					<th style="width:90px"><?= __("Type","bluerabbit"); ?></th>
					<th><?= __("Description","bluerabbit"); ?></th>
					<th style="width:90px"><?= __("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody id="features-list">
				<?php foreach($dbFeatures as $f){ ?>
				<tr id="feature-row-<?= $f->feature_id; ?>">
					<td style="opacity:0.4"><?= $f->feature_id; ?></td>
					<td><code style="color:#1cc2eb;font-size:12px"><?= $f->feature_name; ?></code></td>
					<td style="font-weight:600"><?= $f->feature_label; ?></td>
					<td><span class="br-badge <?= $f->feature_type == 'number' ? 'br-badge-teal' : 'br-badge-blue'; ?>"><?= $f->feature_type; ?></span></td>
					<td style="font-size:12px;opacity:0.5"><?= $f->feature_desc; ?></td>
					<td>
						<div style="display:flex;gap:4px">
							<button class="br-step-btn br-step-btn-green" style="width:28px;height:28px" onClick="editFeature(<?= $f->feature_id; ?>, '<?= esc_attr($f->feature_name); ?>', '<?= esc_attr($f->feature_label); ?>', '<?= esc_attr($f->feature_type); ?>', '<?= esc_attr($f->feature_desc); ?>');">
								<span class="icon icon-edit"></span>
							</button>
							<button class="br-step-btn br-step-btn-red" style="width:28px;height:28px" onClick="deleteFeatureConfirm(<?= $f->feature_id; ?>, '<?= esc_attr($f->feature_name); ?>');">
								<span class="icon icon-trash"></span>
							</button>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<div style="margin-top:12px">
			<button class="br-btn br-btn-amber" onClick="showAddFeatureForm();">
				<span class="icon icon-add"></span> <?= __("Add Feature","bluerabbit"); ?>
			</button>
		</div>

		<!-- Add/Edit Feature Form -->
		<div id="feature-form" style="display:none;background:rgba(4,22,30,0.5);border:1px solid rgba(28,194,235,0.15);border-radius:8px;padding:16px;margin-top:12px">
			<h3 class="br-panel-title" id="feature-form-title"><?= __("Add Feature","bluerabbit"); ?></h3>
			<input type="hidden" id="feature-form-id" value="0">
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Feature Name","bluerabbit"); ?></label>
					<input type="text" id="feature-form-name" class="br-input" placeholder="<?= __('e.g. use_custom_module','bluerabbit'); ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Label","bluerabbit"); ?></label>
					<input type="text" id="feature-form-label" class="br-input" placeholder="<?= __('e.g. Use Custom Module','bluerabbit'); ?>">
				</div>
			</div>
			<div class="br-form-grid" style="grid-template-columns:1fr 1fr">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Type","bluerabbit"); ?></label>
					<select id="feature-form-type" class="br-input">
						<option value="checkbox"><?= __("Checkbox (on/off)","bluerabbit"); ?></option>
						<option value="number"><?= __("Number (limit value)","bluerabbit"); ?></option>
					</select>
					<span class="br-form-hint"><?= __("Use 'Number' for limits like max_players. Value of 0 = no limit.","bluerabbit"); ?></span>
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Description","bluerabbit"); ?></label>
					<input type="text" id="feature-form-desc" class="br-input" placeholder="<?= __('Optional description','bluerabbit'); ?>">
				</div>
			</div>
			<div style="display:flex;gap:8px;margin-top:8px">
				<button class="br-btn br-btn-green" onClick="saveFeatureAction();"><span class="icon icon-config"></span> <?= __("Save Feature","bluerabbit"); ?></button>
				<button class="br-btn br-btn-red" onClick="$('#feature-form').hide();"><span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?></button>
			</div>
		</div>
	</div>
	</div>

	<!-- Plans -->
	<div class="br-scroll-section" id="plans_settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-rank"></span> <?= __("Plans","bluerabbit"); ?></h3>

		<div id="plans-list-view">
			<table class="br-table">
				<thead>
					<tr>
						<th><?= __("Plan","bluerabbit"); ?></th>
						<th style="width:100px"><?= __("Key","bluerabbit"); ?></th>
						<th style="width:90px"><?= __("Type","bluerabbit"); ?></th>
						<th style="width:80px"><?= __("Status","bluerabbit"); ?></th>
						<th style="width:70px"><?= __("Users","bluerabbit"); ?></th>
						<th style="width:140px"><?= __("Actions","bluerabbit"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($allPlansAll as $plan){
						$user_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}br_players WHERE user_plan_id = %d", $plan->plan_id));
						$type_badge = $plan->plan_type == 'system' ? 'br-badge-red' : ($plan->plan_type == 'standard' ? 'br-badge-blue' : 'br-badge-amber');
					?>
					<tr>
						<td style="font-weight:600"><?= $plan->plan_label; ?></td>
						<td><code style="color:#1cc2eb;font-size:12px"><?= $plan->plan_key; ?></code></td>
						<td><span class="br-badge <?= $type_badge; ?>"><?= ucfirst($plan->plan_type); ?></span></td>
						<td><?= $plan->plan_status; ?></td>
						<td class="text-center"><?= $user_count; ?></td>
						<td>
							<div style="display:flex;gap:4px">
								<button class="br-btn" style="padding:3px 8px;font-size:11px" onClick="editPlanFeatures(<?= $plan->plan_id; ?>, '<?= esc_attr($plan->plan_label); ?>');">
									<span class="icon icon-config"></span> <?= __("Features","bluerabbit"); ?>
								</button>
								<?php if($plan->plan_type == 'custom'){ ?>
								<button class="br-step-btn br-step-btn-red" style="width:26px;height:26px" onClick="deletePlanConfirm(<?= $plan->plan_id; ?>, '<?= esc_attr($plan->plan_label); ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<?php } ?>
							</div>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<div style="margin-top:12px">
				<button class="br-btn br-btn-amber" onClick="showNewPlanForm();"><span class="icon icon-add"></span> <?= __("New Custom Plan","bluerabbit"); ?></button>
			</div>

			<div id="new-plan-form" style="display:none;background:rgba(4,22,30,0.5);border:1px solid rgba(28,194,235,0.15);border-radius:8px;padding:16px;margin-top:12px">
				<h3 class="br-panel-title"><?= __("Create New Plan","bluerabbit"); ?></h3>
				<div class="br-form-grid" style="grid-template-columns:1fr 1fr 1fr">
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Plan Name","bluerabbit"); ?></label>
						<input type="text" id="new-plan-label" class="br-input" placeholder="<?= __('e.g. Enterprise','bluerabbit'); ?>">
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Notes","bluerabbit"); ?></label>
						<textarea id="new-plan-notes" class="br-input" rows="1"></textarea>
					</div>
					<div class="br-form-group">
						<label class="br-form-label"><?= __("Clone features from","bluerabbit"); ?></label>
						<select id="new-plan-clone" class="br-input">
							<option value="0"><?= __("Start empty","bluerabbit"); ?></option>
							<?php foreach($allPlans as $plan){ ?>
							<option value="<?= $plan->plan_id; ?>"><?= $plan->plan_label; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div style="display:flex;gap:8px;margin-top:8px">
					<button class="br-btn br-btn-green" onClick="createNewPlan();"><span class="icon icon-add"></span> <?= __("Create Plan","bluerabbit"); ?></button>
					<button class="br-btn br-btn-red" onClick="$('#new-plan-form').hide();"><span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?></button>
				</div>
			</div>
		</div>

		<!-- Plan Feature Editor -->
		<div id="plan-feature-editor" style="display:none;">
			<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap">
				<button class="br-btn" onClick="backToPlans();"><span class="icon icon-back"></span> <?= __("Back","bluerabbit"); ?></button>
				<span style="font-family:'proxima-nova-extra-condensed',sans-serif;font-size:22px;font-weight:900;text-transform:uppercase;letter-spacing:1px" id="editing-plan-label"></span>
				<div style="margin-left:auto;display:flex;gap:6px">
					<select id="copy-from-plan-select" class="br-input" style="width:auto">
						<option value=""><?= __("Copy features from...","bluerabbit"); ?></option>
						<?php foreach($allPlans as $cp){ ?>
						<option value="<?= $cp->plan_id; ?>"><?= $cp->plan_label; ?></option>
						<?php } ?>
					</select>
					<button class="br-btn br-btn-amber" onClick="copyFromPlanAction();"><span class="icon icon-repeat"></span> <?= __("Copy","bluerabbit"); ?></button>
				</div>
			</div>
			<input type="hidden" id="editing-plan-id" value="">
			<table class="br-table" id="plan-features-table">
				<thead><tr><th><?= __("Feature","bluerabbit"); ?></th><th style="width:150px" class="text-center"><?= __("Value","bluerabbit"); ?></th></tr></thead>
				<tbody></tbody>
			</table>
			<div style="margin-top:12px">
				<button class="br-btn br-btn-green" onClick="savePlanFeaturesAction();"><span class="icon icon-config"></span> <?= __("Save Plan Features","bluerabbit"); ?></button>
			</div>
		</div>

		<!-- Role Defaults -->
		<div style="border-top:1px solid rgba(28,194,235,0.1);margin-top:20px;padding-top:20px">
			<h3 class="br-panel-title"><span class="icon icon-config"></span> <?= __("Role Default Plans","bluerabbit"); ?></h3>
			<span class="br-form-hint" style="display:block;margin:-12px 0 16px"><?= __("When a user has no explicit plan assigned, they get the default plan for their WordPress role.","bluerabbit"); ?></span>
			<table class="br-table">
				<thead><tr><th><?= __("Role","bluerabbit"); ?></th><th style="width:200px"><?= __("Default Plan","bluerabbit"); ?></th></tr></thead>
				<tbody>
					<?php
					$role_config_map = array(
						'administrator'   => __("Administrator","bluerabbit"),
						'br_game_master'  => __("Game Master","bluerabbit"),
						'br_npc'          => __("NPC","bluerabbit"),
						'br_player'       => __("Player","bluerabbit"),
						'default'         => __("Default (fallback)","bluerabbit"),
					);
					foreach($role_config_map as $role_key=>$role_label){
						$cfg_name = 'role_default_plan_'.$role_key;
						$current_val = isset($config[$cfg_name]['value']) ? $config[$cfg_name]['value'] : '';
					?>
					<tr>
						<td style="font-weight:600"><?= $role_label; ?></td>
						<td>
							<select class="br-input role-default-select" data-role="<?= $role_key; ?>" style="width:100%">
								<?php foreach($allPlans as $rp){ ?>
								<?php if($rp->plan_key == 'god') continue; ?>
								<option value="<?= $rp->plan_key; ?>" <?= $current_val == $rp->plan_key ? 'selected' : ''; ?>><?= $rp->plan_label; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<div style="margin-top:10px">
				<button class="br-btn br-btn-green" onClick="saveRoleDefaults();"><span class="icon icon-config"></span> <?= __("Save Role Defaults","bluerabbit"); ?></button>
			</div>
		</div>

		<!-- User Plan Assignment -->
		<div style="border-top:1px solid rgba(28,194,235,0.1);margin-top:20px;padding-top:20px">
			<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Assign Plan to User","bluerabbit"); ?></h3>
			<div class="br-form-group">
				<input type="text" id="plan-user-search" class="br-input" placeholder="<?= __('Search by name or email...','bluerabbit'); ?>" onKeyUp="searchUsersForPlanAssign();" style="max-width:400px">
			</div>
			<div id="plan-user-results"></div>
		</div>
	</div>
	</div>

	<!-- Images -->
	<div class="br-scroll-section" id="image_settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-image"></span> <?= __("Images","bluerabbit"); ?></h3>
		<div class="br-gallery br-gallery-large">
			<?php foreach($image_types as $iKey=>$img){ ?>
			<div style="position:relative">
				<?php $thumb_id = $iKey; $file = $config[$iKey]['value']; include(TEMPLATEPATH . '/gallery-item.php'); ?>
				<div class="br-gallery-label"><?= $img['label']; ?></div>
				<input class="form-ui setting-id" type="hidden" value="<?= $config[$iKey]['id']; ?>">
				<input class="form-ui setting-name" type="hidden" value="<?= $iKey; ?>">
				<input class="form-ui setting-label" type="hidden" value="<?= $img['label']; ?>">
			</div>
			<?php } ?>
		</div>
	</div>
	</div>

	<!-- Organizations -->
	<div class="br-scroll-section" id="organizations_settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-wall"></span> <?= __("Organizations","bluerabbit"); ?></h3>
		<table class="br-table" id="organizations">
			<thead>
				<tr><th><?= __("ID","bluerabbit"); ?></th><th><?= __("Name","bluerabbit"); ?></th><th><?= __("Logo","bluerabbit"); ?></th><th><?= __("Color","bluerabbit"); ?></th><th><?= __("Owner","bluerabbit"); ?></th><th><?= __("Actions","bluerabbit"); ?></th></tr>
			</thead>
			<tbody>
				<?php foreach($orgs as $sKey=>$org){ ?>
				<tr>
					<td style="opacity:0.4"><?= $org->org_id; ?></td>
					<td style="font-weight:600"><?= $org->org_name; ?></td>
					<td><img src="<?= $org->org_logo; ?>" style="height:40px;border-radius:6px"></td>
					<td><?= $org->org_color; ?></td>
					<td><?= $org->player_display_name; ?></td>
					<td>
						<div style="display:flex;gap:4px">
							<a href="<?= get_bloginfo('url').'/organization/?id='.$org->org_id; ?>" class="br-btn" style="padding:3px 8px;font-size:11px"><span class="icon icon-edit"></span> <?= __("Manage","bluerabbit"); ?></a>
							<button class="br-btn br-btn-green" style="padding:3px 8px;font-size:11px" onClick="loadContent('new-org',<?= $org->org_id; ?>);"><?= __("Edit","bluerabbit"); ?></button>
							<button class="br-step-btn br-step-btn-red" style="width:26px;height:26px" onClick="trashOrg(<?= $org->org_id; ?>);"><span class="icon icon-trash"></span></button>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<div style="margin-top:12px">
			<button class="br-btn br-btn-amber" onClick="loadContent('new-org');"><span class="icon icon-add"></span> <?= __("New Organization","bluerabbit"); ?></button>
		</div>
	</div>
	</div>

	<!-- Sponsors -->
	<div class="br-scroll-section" id="sponsors_settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-carrot"></span> <?= __("Sponsors","bluerabbit"); ?></h3>
		<table class="br-table" id="sponsors">
			<thead>
				<tr><th><?= __("ID","bluerabbit"); ?></th><th><?= __("Name","bluerabbit"); ?></th><th><?= __("URL","bluerabbit"); ?></th><th><?= __("Logo","bluerabbit"); ?></th><th><?= __("Level","bluerabbit"); ?></th><th><?= __("Actions","bluerabbit"); ?></th></tr>
			</thead>
			<tbody>
				<?php foreach($sponsors as $sKey=>$sponsor){ ?>
				<tr>
					<td style="opacity:0.4"><?= $sponsor->sponsor_id; ?></td>
					<td style="font-weight:600"><?= $sponsor->sponsor_name; ?></td>
					<td style="font-size:12px;opacity:0.5;word-break:break-all"><?= $sponsor->sponsor_url; ?></td>
					<td><img src="<?= $sponsor->sponsor_logo; ?>" style="height:40px;border-radius:6px"></td>
					<td><?= $sponsor->sponsor_level; ?></td>
					<td>
						<div style="display:flex;gap:4px">
							<button class="br-btn br-btn-green" style="padding:3px 8px;font-size:11px" onClick="loadContent('new-sponsor',<?= $sponsor->sponsor_id; ?>);"><?= __("Edit","bluerabbit"); ?></button>
							<button class="br-step-btn br-step-btn-red" style="width:26px;height:26px" onClick="confirmStatus(<?= $sponsor->sponsor_id; ?>,'sponsor','trash');"><span class="icon icon-trash"></span></button>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<div style="margin-top:12px">
			<button class="br-btn br-btn-amber" onClick="loadContent('new-sponsor');"><span class="icon icon-add"></span> <?= __("New Sponsor","bluerabbit"); ?></button>
		</div>
		<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>">
		<input type="hidden" id="reload" value="true">
	</div>
	</div>

	<!-- Reference -->
	<div class="br-scroll-section" id="settings-reference">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-story"></span> <?= __("Settings Code Reference","bluerabbit"); ?></h3>
		<table class="br-table">
			<thead><tr><th style="width:50px"><?= __("ID","bluerabbit"); ?></th><th><?= __("Setting","bluerabbit"); ?></th><th><?= __("Value","bluerabbit"); ?></th></tr></thead>
			<tbody>
				<?php foreach($config as $ckey=>$c){ ?>
				<tr>
					<td style="opacity:0.4"><?= $c['id']; ?></td>
					<td><code style="color:#1cc2eb;font-size:12px"><?= $ckey; ?></code></td>
					<td style="font-weight:700"><?= $c['value']; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	</div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<span></span>
	<button class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="saveSysConfig();">
		<span class="icon icon-config"></span> <?= __("Save Settings","bluerabbit"); ?>
	</button>
</div>
<script>
var brPlans = <?= json_encode(array_map(function($p){ return array('plan_id'=>$p->plan_id, 'plan_key'=>$p->plan_key, 'plan_label'=>$p->plan_label, 'plan_type'=>$p->plan_type); }, $allPlans)); ?>;
var brSysFeatures = <?= json_encode($sysFeatures); ?>;

function brScrollTo(id, btn) {
	document.querySelectorAll('#config-tabs-buttons .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	if (btn) btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('#config-tabs-buttons .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();
</script>
<?php //wp_enqueue_media();?>
<?php }else{ ?>
<script>document.location.href="<?php echo get_bloginfo('url')."/404"; ?>";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

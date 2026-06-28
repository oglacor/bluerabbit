<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$my_features = BR_Config::instance()->getFeatures($f_role);
if($roles[0] == 'administrator'){
	$orgs = BR_Organization::instance()->getOrgs();
}
if (isset($adventure) && $adventure) {
	if (!$isAdmin && !$isGM) {
	?> <script>document.location.href="<?php bloginfo('url');?>/404"; </script> <?php
	exit();
	}
}

if (!isset($adventure) || !$adventure) {
	$adventure = (object) [
		'adventure_id' => '', 'adventure_title' => '', 'adventure_code' => '',
		'adventure_badge' => '', 'adventure_logo' => '', 'adventure_color' => '',
		'adventure_type' => 'normal', 'adventure_owner' => get_current_user_id(),
		'adventure_privacy' => '', 'adventure_nickname' => '',
		'adventure_has_guilds' => 0, 'adventure_gmt' => 'America/Mexico_City',
		'adventure_hide_quests' => '', 'adventure_hide_schedule' => 'no',
		'adventure_grade_scale' => 'none', 'adventure_progression_type' => 'before',
		'adventure_instructions' => '', 'adventure_start_date' => '',
		'adventure_end_date' => '', 'adventure_certificate_signature' => '',
		'adventure_level_up_array' => '', 'adventure_status' => 'publish',
		'adventure_ai_api_key' => '',
		'adventure_xp_label' => 'XP', 'adventure_bloo_label' => 'BLOO',
		'adventure_ep_label' => 'EP', 'adventure_xp_long_label' => 'Experience Points',
		'adventure_bloo_long_label' => 'Bloo', 'adventure_ep_long_label' => 'Exploration Points',
	];
}
if (!isset($xp_label)) $xp_label = $adventure->adventure_xp_label ?: 'XP';
if (!isset($bloo_label)) $bloo_label = $adventure->adventure_bloo_label ?: 'BLOO';
if (!isset($ep_label)) $ep_label = $adventure->adventure_ep_label ?: 'EP';
if (!isset($xp_long_label)) $xp_long_label = $adventure->adventure_xp_long_label ?: 'Experience Points';
if (!isset($bloo_long_label)) $bloo_long_label = $adventure->adventure_bloo_long_label ?: 'Bloo coins';
if (!isset($ep_long_label)) $ep_long_label = $adventure->adventure_ep_long_label ?: 'Energy Points';
if (!isset($adventure_id)) $adventure_id = $adventure->adventure_id ?: 0;
if (!isset($adv_parent_id)) $adv_parent_id = $adventure_id;
if (!isset($adv_child_id)) $adv_child_id = $adventure_id;
if (!isset($isAdmin)) $isAdmin = in_array('administrator', $roles ?? []);
if (!isset($isGM)) $isGM = $isAdmin;
if (!isset($isOwner)) $isOwner = false;
if (!isset($use_achievements)) $use_achievements = '';
if (!isset($use_items)) $use_items = '';
if (!isset($use_backpack)) $use_backpack = '';
if (!isset($use_blockers)) $use_blockers = '';
if (!isset($use_guilds)) $use_guilds = '';
if (!isset($use_leaderboard)) $use_leaderboard = '';
if (!isset($allow_magic_codes)) $allow_magic_codes = '';
if (!isset($use_blog)) $use_blog = '';
if (!isset($use_lore)) $use_lore = '';
if (!isset($use_schedule)) $use_schedule = '';
if (!isset($use_speakers)) $use_speakers = '';
if (!isset($use_wall)) $use_wall = '';
if (!isset($use_encounters)) $use_encounters = '';

$adv_config=array(
	'journey_zoom_level' => array(
		'label'=>__("Zoom Level","bluerabbit"),
		'icon'=>'lock',
		'type'=>'select',
		'default'=>'0',
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
		'label'=>__("Allow players to rate milestones","bluerabbit"),
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
$config = BR_Config::instance()->getSysConfig();
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

<div class="br-page br-has-bottom-bar">

	<!-- Hidden inputs -->
	<input type="hidden" id="the_adventure_id" value="<?= $adventure->adventure_id; ?>">
	<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_adventure_nonce'); ?>"/>
	<input type="hidden" id="register_nonce" value=""/>
	<input type="hidden" id="player-status-nonce" value="<?= wp_create_nonce('br_player_adventure_status_nonce'); ?>"/>

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-adv-header-avatar">
			<span class="icon icon-adventure br-adv-header-icon"></span>
		</div>
		<div>
			<h1 class="br-page-title">
				<?php if(isset($adventure) && $adventure){ ?>
					<?= __('Edit Adventure','bluerabbit'); ?>
				<?php }else{ ?>
					<?= __('Create New Adventure','bluerabbit'); ?>
				<?php } ?>
			</h1>
			<span class="br-page-subtitle"><?= isset($adventure) && $adventure ? esc_html($adventure->adventure_title) : __('New Adventure','bluerabbit'); ?></span>
		</div>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="main-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this)">
			<span class="icon icon-tools"></span> <?= __("General","bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('adventure-intro', this)">
			<span class="icon icon-document"></span> <?= __("Intro","bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('certificate-settings', this)">
			<span class="icon icon-achievement"></span> <?= __("Certificate","bluerabbit"); ?>
		</button>
		<?php if(isset($adventure) && $adventure){ ?>
			<button class="br-tab-btn" onClick="brScrollTo('reset-settings', this)">
				<span class="icon icon-repeat"></span> <?= __("Reset","bluerabbit"); ?>
			</button>
			<button class="br-tab-btn" onClick="brScrollTo('ranks-settings', this)">
				<span class="icon icon-rank"></span> <?= __("Ranks","bluerabbit"); ?>
			</button>
			<button class="br-tab-btn" onClick="brScrollTo('enrolled-players', this)">
				<span class="icon icon-players"></span> <?= __("Players","bluerabbit"); ?>
			</button>
		<?php } ?>
		<button class="br-tab-btn" onClick="brScrollTo('features', this)">
			<span class="icon icon-teamwork"></span> <?= __("Features","bluerabbit"); ?>
		</button>
		<?php $br_ai_allowed = !empty($adventure) && !($my_features && isset($my_features['allow_use_claude_api'][$f_role]) && !$my_features['allow_use_claude_api'][$f_role]); ?>
		<?php if ($br_ai_allowed) { ?>
		<button class="br-tab-btn" onClick="brScrollTo('ai-settings', this)">
			<span class="icon icon-data"></span> <?= __("A.I.","bluerabbit"); ?>
		</button>
		<?php } ?>
		<button class="br-tab-btn" onClick="brScrollTo('quick-links', this)">
			<span class="icon icon-link"></span> <?= __("Quick Links","bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('image_settings', this)">
			<span class="icon icon-image"></span> <?= __("Images","bluerabbit"); ?>
		</button>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- GENERAL SETTINGS                                       -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="general">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-adventure"></span> <?= __('General Settings',"bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Adventure Name','bluerabbit'); ?></label>
			<input class="br-input br-input-lg" placeholder="<?= __('Adventure Name','bluerabbit'); ?>" maxlength="90" type="text" value="<?php if($adventure){echo $adventure->adventure_title;} ?>" id="the_adventure_title">
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Enrollment Link','bluerabbit'); ?></label>
			<input type="text" readonly class="br-input" value="<?= get_bloginfo('url')."/enroll/?enroll_code=$adventure->adventure_code"; ?>">
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Image','bluerabbit'); ?></label>
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= $adventure->adventure_badge; ?>);" onClick="showWPUpload('the_adventure_badge');" id="the_adventure_badge_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_badge');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_badge');"> <span class="icon icon-trash"></span> </button>
							<input type="hidden" id="the_adventure_badge" value="<?php echo $adventure->adventure_badge; ?>"/>
						</div>
					</div>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Highlight Color','bluerabbit'); ?></label>
				<?php $selected_color = $adventure->adventure_color; ?>
				<input id="the_adventure_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
				<?php
				$color_select_id = "#the_adventure_color";
				include (TEMPLATEPATH . '/color-select.php');
				?>
			</div>
		</div>

		<?php if($roles[0] == 'administrator'){ ?>
			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __('Adventure Type','bluerabbit'); ?></label>
					<select id="the_adventure_type" class="br-input">
						<option value="normal" <?= $adventure->adventure_type == "normal" ? "selected" : ""; ?>><?= __("Normal","bluerabbit"); ?></option>
						<option value="template" <?= $adventure->adventure_type == "template" ? "selected" : ""; ?>><?= __("Template","bluerabbit"); ?></option>
					</select>
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __('Owner','bluerabbit'); ?></label>
					<?php
					   $the_owner = $adventure->adventure_owner;
					   if(!$the_owner){
						   $the_owner = $current_user->ID;
					   }
					?>
					<?php $the_roles = array('br_npc','br_game_master','administrator'); ?>
					<?php $allTeachers = get_users(array('role__in'=>$the_roles)); ?>
					<select id="the_adventure_owner" class="br-input">
						<?php foreach($allTeachers as $at){ ?>
							<option value="<?= $at->ID; ?>" <?php if($at->ID == $the_owner) { echo'selected'; }?>>
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
				</div>
			</div>
		<?php }else{?>
			<input type="hidden" id="the_adventure_owner" value="<?= $current_user->ID; ?>">
		<?php } ?>

		<?php if($config['adventure_privacy']['value'] > 0 ){ ?>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Privacy','bluerabbit'); ?></label>
				<?php  $the_adventure_privacy = $adventure->adventure_privacy ? $adventure->adventure_privacy : $config['adventure_privacy']['value'];  ?>
				<select id="the_adventure_privacy" class="br-input">
					<option value="public"  <?php if($the_adventure_privacy == 'public'){echo 'selected';} ?>><?= __('Public','bluerabbit'); ?></option>
					<option value="invite-only" <?php if($the_adventure_privacy == 'invite-only'){echo 'selected';} ?>><?= __('Invite Only','bluerabbit'); ?></option>
				</select>
			</div>
		<?php }else{ ?>
			<input type="hidden" value="invite-only" id="the_adventure_privacy">
		<?php } ?>

	</div>

	<!-- Core Mechanics -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-config"></span> <?= __('Core Mechanics',"bluerabbit"); ?></h3>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('XP Long Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="Experience Points" type="text" value="<?= $xp_long_label; ?>" id="the_adventure_xp_long_label">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('XP Short Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="XP" type="text" value="<?= $xp_label; ?>" id="the_adventure_xp_label">
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Currency Long Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="Bloo Coins" type="text" value="<?= $bloo_long_label; ?>" id="the_adventure_bloo_long_label">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Currency Short Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="Bloo" type="text" value="<?= $bloo_label; ?>" id="the_adventure_bloo_label">
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Energy Long Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="Energy Points" type="text" value="<?= $ep_long_label; ?>" id="the_adventure_ep_long_label">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Energy Short Label','bluerabbit'); ?></label>
				<input class="br-input" placeholder="EP" type="text" value="<?= $ep_label; ?>" id="the_adventure_ep_label">
			</div>
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Nickname','bluerabbit'); ?></label>
			<span class="br-form-hint"><?= __("The default way of calling them within the adventure. Write in singular.","bluerabbit"); ?></span>
			<input class="br-input" type="text" value="<?php if($adventure){echo $adventure->adventure_nickname;} ?>" id="the_adventure_nickname">
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Assign guilds on login','bluerabbit'); ?></label>
			<span class="br-form-hint"><?= __("If assigning random guilds to the players, turn this on.","bluerabbit"); ?></span>
			<select id="the_adventure_has_guilds" class="br-input">
				<option <?php if(!$adventure->adventure_has_guilds){ echo 'selected'; }?> value="0">
					<?= __('No','bluerabbit'); ?>
				</option>
				<option <?php if($adventure->adventure_has_guilds ){ echo 'selected'; }?> value="1">
					<?= __('Yes','bluerabbit'); ?>
				</option>
			</select>
		</div>
	</div>

	<!-- Time Mechanics -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-time"></span> <?= __('Time Mechanics',"bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Time zone','bluerabbit'); ?></label>
			<span class="br-form-hint"><?= __("The time zone of the adventure. Default is GMT 0","bluerabbit"); ?></span>
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
			<div class="br-badge"><?= __('Current adventure time','bluerabbit').": ";?> <strong><?= date('Y-m-d H:i'); ?></strong></div>
			<select class="br-input" id="the_adventure_gmt">
				<option value="0"><?= __("Please, select timezone","bluerabbit"); ?></option>
				<?php $tz_group = "Africa"; ?>
				<optgroup label="<?= $tz_group; ?>">
				<?php foreach(tz_list() as $key=>$t) { ?>
					<?php $new_tz_group = explode("/",$t['tz']); ?>
					<?php if($new_tz_group[0] != $tz_group){  ?>
						<?php $tz_group = $new_tz_group[0]; ?>
						</optgroup>
						<optgroup label="<?= $tz_group; ?>">
					<?php } ?>
					<option value="<?php print $t['tz']; ?>" <?php if($adventure->adventure_gmt == $t['tz']){echo 'selected';}?>>
						<?php print $t['gmt_value']; ?>
					</option>
				<?php } ?>
				 </optgroup>
			</select>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Hide milestones','bluerabbit'); ?></label>
				<span class="br-form-hint"><?= __("How to hide milestones based on time.","bluerabbit"); ?></span>
				<select id="the_adventure_hide_quests" class="br-input">
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
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Schedule','bluerabbit'); ?></label>
				<select id="the_adventure_hide_schedule" class="br-input">
					<option <?php if(!$adventure->adventure_hide_schedule || $adventure->adventure_hide_schedule=='show'){ echo 'selected'; }?> value="show">
						<?= __('Show all days','bluerabbit'); ?>
					</option>
					<option <?php if($adventure->adventure_hide_schedule == "hide"){ echo 'selected'; }?> value="hide">
						<?= __('Just today','bluerabbit'); ?>
					</option>
				</select>
			</div>
		</div>
	</div>

	<!-- Resource Mechanics -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-config"></span> <?= __('Resource Mechanics',"bluerabbit"); ?></h3>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Grade scale type','bluerabbit'); ?></label>
				<span class="br-form-hint"><?= __("If grading the players, what type of scale are you using.","bluerabbit"); ?></span>
				<select id="the_adventure_grade_scale" class="br-input">
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
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Assign Resources','bluerabbit'); ?></label>
				<span class="br-form-hint"><?= __("If grading the players, when will the players receive the resources from each milestone.","bluerabbit"); ?></span>
				<select id="the_adventure_progression_type" class="br-input">
					<option  <?php if($adventure->adventure_progression_type == 'before' || !$adventure->adventure_progression_type){ echo 'selected'; }?> value="before"><?= __('Before Grading','bluerabbit'); ?></option>
					<option <?php if($adventure->adventure_progression_type == 'after'){ echo 'selected'; }?> value="after"><?= __('After Grading','bluerabbit'); ?></option>
				</select>
			</div>
		</div>
	</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- ADVENTURE INTRO                                        -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="adventure-intro">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-document"></span> <?= __('Adventure Intro',"bluerabbit"); ?></h3>
		<p class="br-form-hint"><?= __('This message will be seen when players log in for the first time to the adventure.','bluerabbit'); ?></p>
		<div class="br-form-group">
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
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- CERTIFICATE SETTINGS                                   -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="certificate-settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-achievement"></span> <?= __('Certificate Settings',"bluerabbit"); ?></h3>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?php _e('Start Date','bluerabbit'); ?></label>
				<?php
				if(isset($adventure) && $adventure->adventure_start_date){
					$pretty_start_date = date('Y/m/d H:i', strtotime($adventure->adventure_start_date));
				}else{
					$pretty_start_date = '';
				}
				?>
				<input class="br-input the_start_date" autocomplete="off" id="the_adventure_start_date" value="<?= $pretty_start_date; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?php _e('End Date','bluerabbit'); ?></label>
				<?php
				if(isset($adventure) && $adventure->adventure_end_date){
					$pretty_deadline = date('Y/m/d H:i', strtotime($adventure->adventure_end_date));
				}else{
					$pretty_deadline = '';
				}
				?>
				<input class="br-input the_deadline" autocomplete="off" id="the_adventure_end_date" value="<?= $pretty_deadline; ?>">
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Logo','bluerabbit'); ?></label>
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= $adventure->adventure_logo; ?>);" onClick="showWPUpload('the_adventure_logo');" id="the_adventure_logo_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_logo');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_logo');"> <span class="icon icon-trash"></span> </button>
							<input type="hidden" id="the_adventure_logo" value="<?php echo $adventure->adventure_logo ?? ''; ?>"/>
						</div>
					</div>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Signature','bluerabbit'); ?></label>
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= $adventure->adventure_certificate_signature; ?>);" onClick="showWPUpload('the_adventure_certificate_signature');" id="the_adventure_certificate_signature_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40  green-bg-400" onClick="showWPUpload('the_adventure_certificate_signature');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40  red-bg-400" onClick="clearImage('#the_adventure_certificate_signature');"> <span class="icon icon-trash"></span> </button>
							<input type="hidden" id="the_adventure_certificate_signature" value="<?php echo $adventure->adventure_certificate_signature; ?>"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>

	<?php if(isset($adventure) && $adventure){ ?>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- RESET SETTINGS                                         -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="reset-settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-repeat"></span> <?= __('Reset Settings',"bluerabbit"); ?></h3>

		<div class="br-form-grid">
			<div class="br-form-group">
				<button class="br-btn red" onClick="resetIntro();" id="tutorial-reset-intro"><?= __("Reset Display Intro","bluerabbit"); ?></button>
			</div>
			<div class="br-form-group">
				<button class="br-btn red" onClick="resetPrevLevel();" id="tutorial-reset-prev-level"><?= __("Reset Prev Level","bluerabbit"); ?></button>
			</div>
		</div>
		<div class="br-form-group">
			<button class="br-btn red" onClick="showOverlay('#confirm-reset-guilds');" id="tutorial-reset-guilds"><?= __("Reset Guilds","bluerabbit"); ?></button>
			<div class="confirm-action overlay-layer bottom padding-10 relative" id="confirm-reset-guilds">
				<div class="layer background absolute sq-full grey-bg-900 opacity-80"></div>
				<button class="form-ui white-bg layer relative base" onClick="resetGuilds();">
					<span class="icon-group">
						<span class="button-icon font _24 sq-40  icon-sm red-bg-A400 icon-sm">
							<span class="icon icon-warning white-color"></span>
						</span>
						<span class="icon-content">
							<span class="line red-A400 font _20 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
							<span class="line red-400 font _16 w900"><?= __("This will be an issue if players are already competing","bluerabbit"); ?></span>
						</span>
					</span>
				</button>
				<button class="close-confirm button-icon font _24 sq-40 layer base  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
					<span class="icon icon-cancel white-color"></span>
				</button>
			</div>
		</div>
	</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- LEVEL RANKS                                            -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="ranks-settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-rank"></span> <?= __('Adventure Ranks',"bluerabbit"); ?></h3>
		<p class="br-form-hint"><?= __('This will show a special message every time the player reaches the specified level and will assign your chosen achievement (this will show as the rank for the player).','bluerabbit'); ?></p>

		<?php
			$achievements = [];
			if ($adv_parent_id) {
				$achievements = $wpdb->get_results(
					"SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_status = 'publish' AND achievement_display='rank'"
				);
			}
		?>
		<?php if($achievements) { ?>
			<table class="br-table" id="adventure-ranks">
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
								<input type="number" max="99" min="1" class="br-input rank-level" value="<?= $rank->rank_level; ?>">
							</td>
							<td>
								<select class="br-input rank-achievement unique">
									<option value="0"><?= __("Select achievement","bluerabbit"); ?></option>
									<?php foreach($achievements as $a){ ?>
										<option value="<?= $a->achievement_id; ?>" <?= $a->achievement_id==$rank->achievement_id ? "selected" : ""; ?>>
											<?= $a->achievement_name;  ?>
										</option>
									<?php } ?>
								</select>
							</td>
							<td>
								<button class="br-btn red" onClick="removeTableRow('#row-<?= $key; ?>');">
									<span class="icon icon-trash"></span>
								</button>
							</td>
						</tr>
					<?php } ?>
					<!-- Default -->
					<tr id="row-0">
						<td>
							<input type="number" max="99" min="1" class="br-input rank-level" value="">
						</td>
						<td>
							<select class="br-input rank-achievement unique">
								<option value="0"><?= __("Select achievement","bluerabbit"); ?></option>
								<?php foreach($achievements as $a){ ?>
									<option value="<?= $a->achievement_id; ?>">
										<?= $a->achievement_name;  ?>
									</option>
								<?php } ?>
							</select>
						</td>
						<td>
							<button class="br-btn red remove-row">
								<span class="icon icon-trash"></span>
							</button>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="br-actions">
				<button class="br-btn cyan" onClick="addTableRow('#adventure-ranks');">
					<span class="icon icon-add"></span>
					<?= __('Add Rank',"bluerabbit"); ?>
				</button>
			</div>
		<?php }else{ ?>
			<div class="br-empty">
				<a class="br-btn cyan" href="<?= get_bloginfo('url')."/new-achievement/?adventure_id=$adventure->adventure_id"; ?>">
					<span class="icon icon-add"></span>
					<?= __('Create the first rank',"bluerabbit"); ?>
				</a>
			</div>
		<?php } ?>
	</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- ENROLLED PLAYERS                                       -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="enrolled-players">
	<div class="br-panel">
		<?php
		$players = [];
		if ($adventure->adventure_id) {
			$players = $wpdb->get_results("
				SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug, users.user_login FROM {$wpdb->prefix}br_player_adventure a
				JOIN {$wpdb->prefix}users users
				on a.player_id = users.ID
				LEFT JOIN {$wpdb->prefix}br_players b
				on a.player_id = b.player_id
				WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' LIMIT 5000
			");
		}
		?>
		<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Enrolled Players","bluerabbit"); ?> <span class="br-badge"><?= count($players); ?></span></h3>

		<div class="br-adv-enrolled-toolbar">
			<input type="text" class="br-input br-adv-enrolled-search" id="search-enrolled-players" placeholder="<?= __("Search players...","bluerabbit"); ?>">
			<span class="br-adv-enrolled-count"><span id="enrolled-visible-count"><?= count($players); ?></span> / <?= count($players); ?> <?= __("players","bluerabbit"); ?></span>
		</div>

		<table class="br-table">
			<thead>
				<tr>
					<th><?= __("ID","bluerabbit"); ?></th>
					<th><?= __("User Login","bluerabbit"); ?></th>
					<th><?= __("Name","bluerabbit"); ?></th>
					<th><?= __("Lastname","bluerabbit"); ?></th>
					<th><?= __("Email","bluerabbit"); ?></th>
					<th><?= __("Work","bluerabbit"); ?></th>
					<th><?= __("Role","bluerabbit"); ?></th>
					<th><?= __("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody id="players-list">
				<?php foreach($players as $play){ ?>
					<?php $player_role = $play->player_adventure_role;  ?>
					<tr id="player-row-<?= $play->player_id; ?>" class="<?= "role-$player_role"; ?>"
						data-search="<?= esc_attr(strtolower($play->player_id.' '.$play->user_login.' '.$play->player_first.' '.$play->player_last.' '.$play->player_email)); ?>">
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
												<span class="button-icon font _24 sq-40  icon-sm teal-bg-400 icon-sm">
													<span class="icon icon-activity white-color"></span>
												</span>
												<span class="icon-content">
													<span class="line teal-400 font _18 w900"><?= __("Grant superpowers?","bluerabbit"); ?></span>
												</span>
											</span>
										</button>
										<button class="close-confirm button-icon font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
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
											<span class="button-icon font _24 sq-40  icon-sm red-bg-A400 icon-sm">
												<span class="icon icon-cancel white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm button-icon font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
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
		<div class="br-pagination" id="enrolled-pagination"></div>

		<script>
		jQuery(function($){
			var EPG = {
				page:1, perPage:30,
				render: function(){
					var search = ($('#search-enrolled-players').val()||'').toLowerCase();
					var $rows = $('#players-list > tr');
					var visible = [];
					$rows.each(function(){
						var match = !search || ($(this).attr('data-search')||'').indexOf(search) >= 0;
						if (match) visible.push(this);
						this.style.display = 'none';
					});
					var total = visible.length;
					var pages = Math.ceil(total / this.perPage);
					if (this.page > pages) this.page = Math.max(1, pages);
					var start = (this.page - 1) * this.perPage;
					var end = Math.min(start + this.perPage, total);
					for (var i = start; i < end; i++) visible[i].style.display = '';
					$('#enrolled-visible-count').text(total);
					this.nav(pages);
				},
				nav: function(pages){
					if (pages <= 1){ $('#enrolled-pagination').html(''); return; }
					var h='', p=this.page;
					if(p>1) h+='<button class="br-page-btn" onclick="EPG.goTo('+(p-1)+')">&laquo;</button>';
					var s=Math.max(1,p-3), e=Math.min(pages,p+3);
					if(s>1){ h+='<button class="br-page-btn" onclick="EPG.goTo(1)">1</button>'; if(s>2) h+='<span class="br-pagination-ellipsis">&hellip;</span>'; }
					for(var i=s;i<=e;i++) h+='<button class="br-page-btn'+(i===p?' active':'')+'" onclick="EPG.goTo('+i+')">'+i+'</button>';
					if(e<pages){ if(e<pages-1) h+='<span class="br-pagination-ellipsis">&hellip;</span>'; h+='<button class="br-page-btn" onclick="EPG.goTo('+pages+')">'+pages+'</button>'; }
					if(p<pages) h+='<button class="br-page-btn" onclick="EPG.goTo('+(p+1)+')">&raquo;</button>';
					$('#enrolled-pagination').html(h);
				},
				goTo: function(p){ this.page=p; this.render(); document.getElementById('enrolled-players').scrollIntoView({behavior:'smooth',block:'start'}); }
			};
			window.EPG = EPG;
			$('#search-enrolled-players').on('keyup', function(){ EPG.page=1; EPG.render(); });
			EPG.render();
		});
		</script>
	</div>

	<!-- Add Players -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Add Players","bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Bulk Upload","bluerabbit"); ?></label>
			<a href="<?= get_bloginfo('template_directory');?>/sources/bulk-upload-users.csv" class="br-btn ghost" target="_blank">
				<?= __("Download CSV Template","bluerabbit"); ?>
			</a>
		</div>

		<div class="br-form-group">
			<form id="upload_bulk_users_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
				<label for="the_csv_file_with_users" class="br-form-label"><?= __("Select CSV File","bluerabbit"); ?></label>
				<input type="file" name="the_csv_file_with_users" id="the_csv_file_with_users" size="20" class="br-input" />
				<button type="button" onClick="uploadBulkUsers();" name="upload_csv" class="br-btn cyan"><?= __("Upload file","bluerabbit"); ?></button>
			</form>
		</div>

		<table class="br-table" id="just-uploaded-users">
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
		<div class="call-to-action text-center" id="call-to-action">
		</div>

		<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Add Single Player","bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Check if Username or Email exists","bluerabbit"); ?></label>
			<input class="br-input" type="text" id="username-search" maxlength="255" placeholder="<?= __("Nickname or Email","bluerabbit");?>" onBlur="checkUserDataExists(this);">
		</div>
		<div id="new-player-warnings" class="new-player-warnings">
		</div>
		<div id="add-single-player-form" class="add-single-player-form">
			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Nickname","bluerabbit"); ?></label>
					<input type="hidden" id="new-player-lang" value="<?= $current_player->player_lang;?>">
					<input class="br-input" type="text" id="new-player-username" maxlength="50" placeholder="<?= __("Nickname","bluerabbit");?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Email","bluerabbit"); ?></label>
					<input class="br-input" type="email" id="new-player-email" maxlength="255" placeholder="<?= __("Email","bluerabbit");?>">
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Password","bluerabbit"); ?></label>
				<input class="br-input" type="text" id="new-player-user-password" maxlength="25" placeholder="<?= __("Password","bluerabbit");?>">
			</div>
			<div class="br-actions">
				<button id="btn-reg-player" class="br-btn cyan"><?= __("Register player","bluerabbit");?></button>
			</div>
		</div>
	</div>
	</div>

	<?php } ?>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- FEATURES                                               -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="features">
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-teamwork"></span> <?= __("Features","bluerabbit"); ?>
			<span class="br-actions br-adv-features-actions">
				<button class="br-btn ghost" onClick="allToggleButtonsOn('#features');"><?= __("All On","bluerabbit"); ?></button>
				<button class="br-btn red" onClick="allToggleButtonsOff('#features');"><?= __("All Off","bluerabbit"); ?></button>
			</span>
		</h3>

		<div id="tutorial-adventure-features">
			<table class="br-table">
				<thead>
					<tr>
						<td><?= __("Setting","bluerabbit"); ?></td>
						<td><?= __("Value","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$all_features = $adv_config;
					if (is_array($features)) {
						foreach ($features as $fk => $fv) {
							if (isset($all_features[$fk])) {
								$all_features[$fk] = array_merge($all_features[$fk], $fv);
							} else {
								$all_features[$fk] = $fv;
							}
						}
					}
					?>
					<?php foreach($all_features as $sKey=>$s){ ?>
						<?php
						if ($my_features && isset($my_features[$sKey][$f_role]) && !$my_features[$sKey][$f_role]) {
							continue;
						}
						?>
						<?php if($s['type'] != 'number') { ?>
							<tr id="<?=$sKey; ?>" class="setting">
								<td>
									<span class="font _16 block white-color w600">
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
										<input class="br-input setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
									<?php }elseif($s['type']=='text'){ ?>
										<input class="br-input setting-value" type="text" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
									<?php }elseif($s['type']=='select'){ ?>
										<select class="br-input setting-value">
											<?php
											$currentSelectVal = isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : (isset($s['default']) ? $s['default'] : '');
											foreach($s['options'] as $opt){ ?>
												<option <?php if($currentSelectVal == $opt[0]) { echo 'selected';} ?> value="<?= $opt[0]; ?>">
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
										<span class="font _16 block white-color w600">
											<?php if(isset($s['icon'])) { ?>
												<span class="icon icon-<?= $s['icon']; ?>"></span>
											<?php } ?>
										<?= $s['label']; ?></span>
										<?php if($s['desc']) { ?>
											<span class="font _12 block grey-500"><?= $s['desc']; ?></span>
										<?php } ?>
									</td>
									<td>
										<?php $num_limit = isset($s[$f_role]) ? intval($s[$f_role]) : 0; ?>
									<span class="font _16"><?= $num_limit > 0 ? $num_limit : __('No limit','bluerabbit'); ?></span>
										<input class="setting-value" type="hidden" readonly disabled value="<?= $num_limit; ?>" >
										<input class="setting-id" type="hidden" value="<?= isset($adv_settings[$sKey]['id']) ? $adv_settings[$sKey]['id'] : ""; ?>" >
										<input class="setting-name" type="hidden" value="<?=$sKey; ?>" >
										<input class="setting-label" type="hidden" value="<?= $s['label']; ?>" >
									</td>
								</tr>
							<?php }else{ ?>
								<tr id="<?=$sKey; ?>" class="setting">
									<td>
										<span class="font _16 block white-color w600">
											<?php if(isset($s['icon'])) { ?>
												<span class="icon icon-<?= $s['icon']; ?>"></span>
											<?php } ?>
										<?= $s['label']; ?></span>
										<?php if($s['desc']) { ?>
											<span class="font _12 block grey-500"><?= $s['desc']; ?></span>
										<?php } ?>
									</td>
									<td>
										<?php $num_limit2 = isset($s[$f_role]) ? intval($s[$f_role]) : 0; ?>
										<input class="br-input setting-value" <?= $num_limit2 > 0 ? 'max="'.$num_limit2.'"' : ''; ?> type="number" value="<?= isset($adv_settings[$sKey]['value']) ? $adv_settings[$sKey]['value'] : ""; ?>">
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
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- A.I. SETTINGS                                          -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<?php if ($br_ai_allowed) { ?>
	<div class="br-scroll-section" id="ai-settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-data"></span> <?= __("A.I. Content Validation","bluerabbit"); ?></h3>
		<span class="br-form-hint"><?= __("Add a Claude API key to enable A.I. validation on Open Text steps. The key is stored per-adventure and used server-side only.","bluerabbit"); ?></span>

		<?php $ai_key = $adventure->adventure_ai_api_key ?? ''; ?>
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Claude API Key","bluerabbit"); ?></label>
			<div class="br-form-grid br-form-grid-2">
				<input type="password" class="br-input" id="the_adventure_ai_api_key" value="<?= esc_attr($ai_key); ?>" placeholder="sk-ant-...">
				<div>
					<button class="br-btn br-btn-green" onClick="brSaveAiKey();"><?= __("Save Key","bluerabbit"); ?></button>
					<?php if ($ai_key) { ?>
					<button class="br-btn red" onClick="brRemoveAiKey();"><?= __("Remove Key","bluerabbit"); ?></button>
					<?php } ?>
				</div>
			</div>
		</div>

		<details class="br-ai-help">
			<summary class="br-form-label"><?= __("How to get a Claude API Key", "bluerabbit"); ?></summary>
			<div class="br-ai-help-content">
				<ol>
					<li><?= __('Go to', 'bluerabbit'); ?> <a href="https://console.anthropic.com/" target="_blank" rel="noopener">console.anthropic.com</a></li>
					<li><?= __('Create an account or sign in.', 'bluerabbit'); ?></li>
					<li><?= __('Navigate to', 'bluerabbit'); ?> <strong>Settings &rarr; API Keys</strong></li>
					<li><?= __('Click "Create Key", give it a name, and copy the key.', 'bluerabbit'); ?></li>
					<li><?= __('Paste the key above. It starts with', 'bluerabbit'); ?> <code>sk-ant-</code></li>
				</ol>
				<p class="br-muted"><?= __('The key is used server-side to validate player text responses with Claude Haiku. Typical cost: less than $0.01 per validation.', 'bluerabbit'); ?></p>
			</div>
		</details>
	</div>
	</div>
	<?php } ?>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- QUICK LINKS                                            -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="quick-links">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-link"></span> <?= __("Quick Links","bluerabbit"); ?></h3>
		<span class="br-form-hint br-ql-hint"><?= __("Configure the shortcut buttons that appear in the taskbar. Hidden links won't show for players.","bluerabbit"); ?></span>

		<!-- Built-in links -->
		<div class="br-ql-list">

			<!-- Journey -->
			<div class="br-step-row br-ql-row" style="--ql-color:#1cc2eb" id="ql_journey">
				<span class="icon icon-journey br-ql-icon" style="--ql-color:#1cc2eb"></span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Journey","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("Link to the adventure map/list","bluerabbit"); ?></span>
				</div>
				<div class="setting">
					<button class="toggle-button <?= (!isset($adv_settings['ql_journey']['value']) || $adv_settings['ql_journey']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_journey');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_journey']['value']) ? $adv_settings['ql_journey']['value'] : "1"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_journey']['id']) ? $adv_settings['ql_journey']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_journey">
					<input class="setting-label" type="hidden" value="<?= __("Journey Quick Link","bluerabbit"); ?>">
				</div>
			</div>

			<!-- Magic Code -->
			<?php if($use_achievements){ ?>
			<div class="br-step-row br-ql-row" style="--ql-color:#9f40e2" id="ql_magic_code">
				<span class="icon icon-qr br-ql-icon" style="--ql-color:#9f40e2"></span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Magic Code","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("Opens the magic code input form","bluerabbit"); ?></span>
				</div>
				<div class="setting">
					<button class="toggle-button <?= (isset($adv_settings['ql_magic_code']['value']) && $adv_settings['ql_magic_code']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_magic_code');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_magic_code']['value']) ? $adv_settings['ql_magic_code']['value'] : "1"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_magic_code']['id']) ? $adv_settings['ql_magic_code']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_magic_code">
					<input class="setting-label" type="hidden" value="<?= __("Magic Code Quick Link","bluerabbit"); ?>">
				</div>
			</div>
			<?php } ?>

			<!-- Item Shop -->
			<?php if($use_items){ ?>
			<div class="br-step-row br-ql-row" style="--ql-color:#f7cb15" id="ql_item_shop">
				<span class="icon icon-shop br-ql-icon" style="--ql-color:#f7cb15"></span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Item Shop","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("Link to the item shop","bluerabbit"); ?></span>
				</div>
				<div class="setting">
					<button class="toggle-button <?= (isset($adv_settings['ql_item_shop']['value']) && $adv_settings['ql_item_shop']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_item_shop');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_item_shop']['value']) ? $adv_settings['ql_item_shop']['value'] : "1"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_item_shop']['id']) ? $adv_settings['ql_item_shop']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_item_shop">
					<input class="setting-label" type="hidden" value="<?= __("Item Shop Quick Link","bluerabbit"); ?>">
				</div>
			</div>
			<?php } ?>

			<!-- Feedback -->
			<div class="br-step-row br-ql-row" style="--ql-color:#24da98" id="ql_feedback">
				<span class="icon icon-comment br-ql-icon" style="--ql-color:#24da98"></span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Feedback","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("Contact admin form","bluerabbit"); ?></span>
				</div>
				<div class="setting">
					<button class="toggle-button <?= (!isset($adv_settings['ql_feedback']['value']) || $adv_settings['ql_feedback']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_feedback');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_feedback']['value']) ? $adv_settings['ql_feedback']['value'] : "1"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_feedback']['id']) ? $adv_settings['ql_feedback']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_feedback">
					<input class="setting-label" type="hidden" value="<?= __("Feedback Quick Link","bluerabbit"); ?>">
				</div>
			</div>

			<!-- Cooper Support -->
			<div class="br-step-row br-ql-row" style="--ql-color:#00bcd4" id="ql_cooper">
				<span class="icon icon-comment br-ql-icon" style="--ql-color:#00bcd4"></span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Cooper Support","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("AI support chatbot","bluerabbit"); ?></span>
				</div>
				<div class="setting">
					<button class="toggle-button <?= (!isset($adv_settings['ql_cooper']['value']) || $adv_settings['ql_cooper']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_cooper');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_cooper']['value']) ? $adv_settings['ql_cooper']['value'] : "1"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_cooper']['id']) ? $adv_settings['ql_cooper']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_cooper">
					<input class="setting-label" type="hidden" value="<?= __("Cooper Quick Link","bluerabbit"); ?>">
				</div>
			</div>

			<!-- Cooper Slug -->
			<div class="br-step-row br-ql-row" style="--ql-color:rgba(0,188,212,0.3)" id="ql_cooper_slug">
				<span class="br-ql-icon" style="--ql-color:rgba(0,188,212,0.4)">&nbsp;</span>
				<div class="br-ql-info">
					<span class="br-ql-title"><?= __("Cooper Slug","bluerabbit"); ?></span>
					<span class="br-ql-desc"><?= __("Custom Cooper client slug for this adventure","bluerabbit"); ?></span>
				</div>
				<div class="setting br-ql-setting-wide">
					<input class="br-input setting-value br-w-full" type="text" placeholder="e.g. my-company" value="<?= isset($adv_settings['ql_cooper_slug']['value']) ? $adv_settings['ql_cooper_slug']['value'] : ""; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_cooper_slug']['id']) ? $adv_settings['ql_cooper_slug']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_cooper_slug">
					<input class="setting-label" type="hidden" value="<?= __("Cooper Slug","bluerabbit"); ?>">
				</div>
			</div>

		</div>

		<!-- Custom Links -->
		<h3 class="br-panel-title br-ql-custom-links-title"><span class="icon icon-link"></span> <?= __("Custom Links","bluerabbit"); ?></h3>
		<span class="br-form-hint br-ql-hint"><?= __("Up to 3 custom buttons. They open in a new tab.","bluerabbit"); ?></span>

		<?php for($ql_i = 1; $ql_i <= 3; $ql_i++){ ?>
		<div class="br-ql-custom-card" id="ql-custom-<?= $ql_i; ?>-group">

			<div class="br-ql-custom-header">
				<span class="br-ql-custom-number"><?= $ql_i; ?></span>
				<span class="br-ql-custom-title"><?= sprintf(__("Custom Link %d","bluerabbit"), $ql_i); ?></span>
				<div id="ql_custom_<?= $ql_i; ?>_show" class="setting">
					<button class="toggle-button <?= (isset($adv_settings['ql_custom_'.$ql_i.'_show']['value']) && $adv_settings['ql_custom_'.$ql_i.'_show']['value'] != 0) ? 'active' : ''; ?>" onClick="toggleSetting('#ql_custom_<?= $ql_i; ?>_show');">&nbsp;</button>
					<input class="form-ui setting-value radio-setting-value" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_show']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_show']['value'] : "0"; ?>">
					<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_show']['id']) ? $adv_settings['ql_custom_'.$ql_i.'_show']['id'] : ""; ?>">
					<input class="setting-name" type="hidden" value="ql_custom_<?= $ql_i; ?>_show">
					<input class="setting-label" type="hidden" value="<?= sprintf(__("Custom Link %d Show","bluerabbit"), $ql_i); ?>">
				</div>
			</div>

			<div class="br-form-grid br-ql-grid-2">
				<div class="br-form-group" id="ql_custom_<?= $ql_i; ?>_label">
					<label class="br-form-label"><?= __("Label","bluerabbit"); ?></label>
					<div class="setting">
						<input class="br-input setting-value" type="text" placeholder="<?= __("Button label","bluerabbit"); ?>" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_label']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_label']['value'] : ""; ?>">
						<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_label']['id']) ? $adv_settings['ql_custom_'.$ql_i.'_label']['id'] : ""; ?>">
						<input class="setting-name" type="hidden" value="ql_custom_<?= $ql_i; ?>_label">
						<input class="setting-label" type="hidden" value="<?= sprintf(__("Custom Link %d Label","bluerabbit"), $ql_i); ?>">
					</div>
				</div>
				<div class="br-form-group" id="ql_custom_<?= $ql_i; ?>_link">
					<label class="br-form-label"><?= __("URL","bluerabbit"); ?></label>
					<div class="setting">
						<input class="br-input setting-value" type="url" placeholder="https://..." value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_link']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_link']['value'] : ""; ?>">
						<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_link']['id']) ? $adv_settings['ql_custom_'.$ql_i.'_link']['id'] : ""; ?>">
						<input class="setting-name" type="hidden" value="ql_custom_<?= $ql_i; ?>_link">
						<input class="setting-label" type="hidden" value="<?= sprintf(__("Custom Link %d URL","bluerabbit"), $ql_i); ?>">
					</div>
				</div>
			</div>

			<div class="br-form-grid br-ql-grid-2">
				<div class="br-form-group" id="ql_custom_<?= $ql_i; ?>_icon">
					<label class="br-form-label"><?= __("Icon","bluerabbit"); ?></label>
					<div class="setting br-ql-icon-setting">
						<button class="br-btn br-ql-icon-btn" onClick="showWPUpload('ql_custom_<?= $ql_i; ?>_icon_val');">
							<span class="icon icon-image"></span> <?= __("Choose","bluerabbit"); ?>
						</button>
						<img id="ql_custom_<?= $ql_i; ?>_icon_preview" src="<?= isset($adv_settings['ql_custom_'.$ql_i.'_icon']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_icon']['value'] : ''; ?>" class="br-ql-icon-preview" style="<?= isset($adv_settings['ql_custom_'.$ql_i.'_icon']['value']) && $adv_settings['ql_custom_'.$ql_i.'_icon']['value'] ? '' : 'display:none;'; ?>">
						<input class="form-ui setting-value" type="hidden" id="ql_custom_<?= $ql_i; ?>_icon_val" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_icon']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_icon']['value'] : ""; ?>">
						<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_icon']['id']) ? $adv_settings['ql_custom_'.$ql_i.'_icon']['id'] : ""; ?>">
						<input class="setting-name" type="hidden" value="ql_custom_<?= $ql_i; ?>_icon">
						<input class="setting-label" type="hidden" value="<?= sprintf(__("Custom Link %d Icon","bluerabbit"), $ql_i); ?>">
					</div>
				</div>
				<div class="br-form-group" id="ql_custom_<?= $ql_i; ?>_color">
					<label class="br-form-label"><?= __("Color","bluerabbit"); ?></label>
					<div class="br-form-component setting">
						<?php $ql_color_val = isset($adv_settings['ql_custom_'.$ql_i.'_color']['value']) ? $adv_settings['ql_custom_'.$ql_i.'_color']['value'] : 'grey'; ?>
						<input id="ql_custom_<?= $ql_i; ?>_color_val" class="color-selected setting-value" type="hidden" value="<?= $ql_color_val; ?>">
						<input class="setting-id" type="hidden" value="<?= isset($adv_settings['ql_custom_'.$ql_i.'_color']['id']) ? $adv_settings['ql_custom_'.$ql_i.'_color']['id'] : ""; ?>">
						<input class="setting-name" type="hidden" value="ql_custom_<?= $ql_i; ?>_color">
						<input class="setting-label" type="hidden" value="<?= sprintf(__("Custom Link %d Color","bluerabbit"), $ql_i); ?>">
						<?php $color_select_id = "#ql_custom_".$ql_i."_color_val"; include (TEMPLATEPATH . '/color-select.php'); ?>
					</div>
				</div>
			</div>

		</div>
		<?php } ?>
	</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- IMAGES                                                 -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-scroll-section" id="image_settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-image"></span> <?=__("Images","bluerabbit");?></h3>

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
						<button class="button-icon font _24 sq-40  green-bg-400" onClick="showWPUpload('<?=$iKey; ?>');"><span class="icon icon-image"></span></button>
						<button class="button-icon font _24 sq-40  red-bg-400" onClick="clearImage('#<?=$iKey; ?>');"> <span class="icon icon-trash"></span> </button>
					</div>
					<div class="gallery-item-description white-color foreground">
						<div class="background black-bg opacity-50"></div>
						<h3 class="foreground font _18 w600 padding-10"><?=$img['label']; ?></h3>
						<?php if(isset($img['desc'])){ ?>
							<h5 class="foreground font _12 w600 padding-10"><?=$img['desc']; ?></h5>
						<?php } ?>
						<?php if(isset($desc_warning) && $desc_warning){ ?>
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

	<!-- Bottom Bar -->
	<div class="br-form-bottom-bar">
		<div class="br-actions">
			<select class="br-input br-select-auto" id="the_adventure_status">
				<option value="publish" <?php if($adventure->adventure_status == 'publish' || !$adventure){echo 'selected';} ?> ><?= __('Publish','bluerabbit'); ?></option>
				<option value="draft"  <?php if($adventure->adventure_status == 'draft'){echo 'selected';} ?>><?= __('Draft','bluerabbit'); ?></option>
				<option value="trash"  <?php if($adventure->adventure_status == 'trash'){echo 'selected';} ?>><?= __('Trash','bluerabbit'); ?></option>
			</select>
		</div>
		<div class="br-actions">
			<a class="br-btn red" href="<?= get_bloginfo('url'); ?>">
				<span class="icon icon-cancel"></span> <?= __('Cancel','bluerabbit'); ?>
			</a>
			<button class="br-btn cyan" id="submit-button" onClick="updateAdventure();">
				<?php if($adventure_id){ ?>
					<span class="icon icon-check"></span> <?= __('Update Adventure','bluerabbit'); ?>
				<?php }else{ ?>
					<span class="icon icon-check"></span> <?= __('Create Adventure','bluerabbit'); ?>
				<?php } ?>
			</button>
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
<input type="hidden" id="tabi-as-category-nonce" value="<?php echo wp_create_nonce('tabi_as_category_nonce'); ?>" />
<input type="hidden" id="add-tabi-nonce" value="<?php echo wp_create_nonce('add_tabi_nonce'); ?>" />

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('#main-tabs-buttons .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('#main-tabs-buttons .br-tab-btn');
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

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php 
function theme_core_setup(){
	$roles_args = array(
		'moderate_comments' => 0,
		'manage_categories' => 1,
		'manage_links' => 1,
		'upload_files' => 1,
		'unfiltered_html' => 1,
		'edit_posts' => 1,
		'edit_others_posts' => 1,
		'edit_published_posts' => 1,
		'publish_posts' => 1,
		'edit_pages' => 0,
		'read' => 1,
		'level_7' => 1,
		'level_6' => 1,
		'level_5' => 1,
		'level_4' => 1,
		'level_3' => 1,
		'level_2' => 1,
		'level_1' => 1,
		'level_0' => 1,
		'edit_others_pages' => 1,
		'edit_published_pages' => 1,
		'publish_pages' => 1,
		'delete_pages' => 0,
		'delete_others_pages' => 0,
		'delete_published_pages' => 0,
		'delete_posts' => 0,
		'delete_others_posts' => 0,
		'delete_published_posts' => 1,
		'delete_private_posts' => 0,
		'edit_private_posts' => 0,
		'read_private_posts' => 0,
		'delete_private_pages' => 0,
		'edit_private_pages' => 0,
		'read_private_pages' => 0,
	);
	$player = add_role('br_player', __('Player','bluerabbit'),$roles_args); /// Plays the adventures they are enrolled into.
	$game_master = add_role('br_game_master', __('Game Master','bluerabbit'),$roles_args ); /// Manages everything within the organization they are part of.
	$npc = add_role('br_npc', __('NPC','bluerabbit'),$roles_args ); /// Teachers, instructors, facilitators > Can clone adventures from their organization pool. Can't edit the adventure itself, but can see the settings. We need a new state for disabling all settings and prevent the manage adventure page from NPCs.
	
	if ( null !== $player ) {
		echo '<div class="updated notice"><p>Player Role Created</p></div>';
	}else{
		echo '<div class="updated notice"><p>Player Role Not Created. Already exist.</p></div>';
	}
	if ( null !== $game_master ) {
		echo '<div class="updated notice"><p>Game Master Role Created</p></div>';
	}else{
		echo '<div class="updated notice"><p>Game Master Role Not Created. Already exist.</p></div>';
	}
	if ( null !== $npc ) {
		echo '<div class="updated notice"><p>Non-Player Character Role Created</p></div>';
	}else{
		echo '<div class="updated notice"><p>Non-Player Character Role Not Created. Already exist.</p></div>';
	}
	global $wpdb;
	$enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_players WHERE player_id=1");
	if(!$enrolled){
		$admin = wp_get_current_user();
		$profile_pic_default = get_bloginfo('template_directory')."/images/no-profile.png";
		$new_player_sql="INSERT INTO {$wpdb->prefix}br_players
		(`player_id`, `player_email`, `player_password`, `player_display_name`, `player_lang`, `player_picture`, `player_nickname`)				
		VALUES (%d,%s,%s,%s,%s,%s,%s)";
		$new_player = $wpdb->query($wpdb->prepare($new_player_sql, 1, $admin->user_email,'none', $admin->user_login, 'en_US', $profile_pic_default, $admin->user_login ));
	}
	$charset_collate = $wpdb->get_charset_collate();
$sql = "
	CREATE TABLE {$wpdb->prefix}br_achievements (
		`achievement_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`org_id` BIGINT NULL,
		`ref_id` VARCHAR(8) NULL,
		`achievement_parent` BIGINT NULL,
		`achievement_name` VARCHAR(255) NULL,
		`achievement_xp` INT NULL,
		`achievement_bloo` INT NULL,
		`achievement_ep` INT NULL,
		`achievement_max` INT NULL,
		`achievement_deadline` DATETIME NULL,
		`achievement_badge` TEXT NULL,
		`achievement_display` VARCHAR(50) NOT NULL DEFAULT 'badge',
		`achievement_group` VARCHAR(50) NULL,
		`achievement_path` BIGINT NULL,
		`achievement_color` VARCHAR(20) NOT NULL DEFAULT 'amber',
		`achievement_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`achievement_code` VARCHAR(250) NULL,
		`achievement_content` LONGTEXT NULL,
		`achievement_order` INT NULL,
		`achievement_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`achievement_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`achievement_id`))$charset_collate;
  
	CREATE TABLE {$wpdb->prefix}br_achievement_codes (
		`code_id` BIGINT NOT NULL AUTO_INCREMENT,
		`code_value` VARCHAR(50) NULL UNIQUE,
		`code_status` VARCHAR(20) NULL DEFAULT 'publish',
		`code_redeemed` DATETIME NULL,
		`code_deadline` DATETIME NULL,
		`code_modified` DATETIME NULL,
		`code_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`achievement_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`player_id` BIGINT NULL,
		PRIMARY KEY (`code_id`))$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_activity_log (
		`log_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NULL,
		`player_id` BIGINT NULL,
		`log_action` VARCHAR(100) NOT NULL,
		`log_type` VARCHAR(100) NOT NULL,
		`log_object_id` BIGINT NULL,
		`log_object_child_id` BIGINT NULL,
		`log_content` TEXT NOT NULL,
		`log_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`log_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_adventures (
		`adventure_id` BIGINT NOT NULL AUTO_INCREMENT,
		`org_id` BIGINT NULL,
		`adventure_owner` BIGINT NOT NULL,
		`adventure_date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`adventure_date_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`adventure_badge` TEXT NULL,
		`adventure_logo` TEXT NULL,
		`adventure_certificate_signature` TEXT NULL,
		`adventure_gmt` VARCHAR(255) NULL DEFAULT 'America/Mexico_City',
		`adventure_type` VARCHAR(30) NOT NULL DEFAULT 'normal',
		`adventure_title` TEXT NOT NULL,
		`adventure_xp_label` TEXT NOT NULL,
		`adventure_bloo_label` TEXT NOT NULL,
		`adventure_ep_label` TEXT NOT NULL,
		`adventure_xp_long_label` VARCHAR(255) NOT NULL,
		`adventure_bloo_long_label` VARCHAR(255) NOT NULL,
		`adventure_ep_long_label` VARCHAR(255) NOT NULL,
		`adventure_grade_scale` VARCHAR(20) NULL DEFAULT 'none',
		`adventure_progression_type` VARCHAR(20) NULL DEFAULT 'before',
		`adventure_privacy` VARCHAR(20) NOT NULL,
		`adventure_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`adventure_instructions` LONGTEXT NOT NULL,
		`adventure_nickname` VARCHAR(50) NULL,
		`adventure_code` VARCHAR(20) NULL,
		`adventure_level_up_array` LONGTEXT NULL,
		`adventure_color` VARCHAR(50) NULL,
		`adventure_hide_quests` VARCHAR(50) NULL,
		`adventure_hide_schedule` VARCHAR(4) NOT NULL DEFAULT 'no',
		`adventure_topic_id` VARCHAR(50) NULL,
		`adventure_has_guilds` TINYINT NOT NULL DEFAULT 0,
		`adventure_start_date` DATETIME NULL,
		`adventure_end_date` DATETIME NULL,
		`adventure_parent` BIGINT NULL,
	PRIMARY KEY (`adventure_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_adventure_ranks (
		`adventure_id` BIGINT NOT NULL,
		`rank_level` INT NOT NULL,
		`achievement_id` BIGINT NOT NULL,
	PRIMARY KEY (`adventure_id`,`rank_level`,`achievement_id`) )$charset_collate;
    
	CREATE TABLE {$wpdb->prefix}br_announcements (
		`ann_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`ann_content` LONGTEXT NOT NULL,
		`ann_type` VARCHAR(20) NOT NULL DEFAULT 'normal',
		`ann_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`ann_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`ann_author` BIGINT(20) NOT NULL,
	PRIMARY KEY (`ann_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_tabis (
		`tabi_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`tabi_name` LONGTEXT NOT NULL,
		`tabi_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`tabi_width` INT NULL DEFAULT 1080,
		`tabi_height` INT NULL DEFAULT 1920,
		`tabi_color` VARCHAR(20) NULL DEFAULT 'blue',
		`tabi_background` TEXT NULL,
		`tabi_level` INT NULL,
		`tabi_on_journey` TINYINT NULL,

	PRIMARY KEY (`tabi_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_blockers (
		`blocker_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`blocker_date` DATETIME NOT NULL,
		`blocker_cost` INT NOT NULL DEFAULT 0,
		`blocker_description` LONGTEXT NOT NULL,
		`blocker_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
	PRIMARY KEY (`blocker_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_challenge_answers (
		`answer_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_id` BIGINT NOT NULL,
		`question_id` BIGINT NOT NULL,
		`answer_value` TEXT NOT NULL,
		`answer_image` TEXT NULL,
		`answer_correct` TINYINT NOT NULL DEFAULT 0,
		`answer_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`ref_id` VARCHAR(8) NULL,
		`answer_parent` BIGINT NULL,
	PRIMARY KEY (`answer_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_challenge_attempts (
		`attempt_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`attempt_status` VARCHAR(20) NOT NULL DEFAULT 'fail',
		`attempt_grade` INT(11) NULL,
		`attempt_answers` INT NULL,
		`attempt_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`attempt_id`))$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_challenge_attempt_answers (
		`attempt_id` BIGINT NOT NULL,
		`question_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`quest_id` BIGINT NOT NULL,
		`answer_id` BIGINT NULL,
		`answer_value` TEXT NULL,
		`timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`question_id`, `player_id`, `quest_id`, `attempt_id`))$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_challenge_questions (
		`question_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_id` BIGINT NOT NULL,
		`question_title` TEXT NOT NULL,
		`question_image` TEXT NOT NULL,
		`question_type` VARCHAR(20) NOT NULL DEFAULT 'single',
		`question_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`ref_id` VARCHAR(8) NULL,
		`question_parent` BIGINT NULL,
	PRIMARY KEY (`question_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_config (
		`config_id` BIGINT NOT NULL AUTO_INCREMENT,
		`config_name` VARCHAR(255) NOT NULL,
		`config_label` TEXT NULL,
		`config_type` VARCHAR(50) NOT NULL DEFAULT 'radio',
		`config_desc` TEXT NULL,
		`config_value` TEXT NULL DEFAULT 0,
	PRIMARY KEY (`config_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_encounters (
		`enc_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`achievement_id` BIGINT NULL DEFAULT 0,
		`ref_id` VARCHAR(8) NULL,
		`enc_parent` BIGINT NULL,
		`enc_question` TEXT NOT NULL,
		`enc_right_option` VARCHAR(255) NOT NULL,
		`enc_decoy_option1` VARCHAR(255) NOT NULL,
		`enc_decoy_option2` VARCHAR(255) NOT NULL,
		`enc_badge` TEXT NOT NULL,
		`enc_color` VARCHAR(20) NOT NULL DEFAULT 'blue',
		`enc_icon` VARCHAR(20) NOT NULL DEFAULT 'battle',
		`enc_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`enc_xp` INT NULL,
		`enc_bloo` INT NULL,
		`enc_ep` INT NULL,
		`enc_level` INT NULL,
		`enc_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`enc_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`enc_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_features (
		`feature_id` BIGINT NOT NULL AUTO_INCREMENT,
		`feature_name` VARCHAR(255) NOT NULL,
		`feature_label` TEXT NULL,
		`feature_value` TEXT NULL,
		`feature_type` VARCHAR(50) NOT NULL DEFAULT 'radio',
		`feature_desc` TEXT NULL,
		`feature_access_free` INT NULL,
		`feature_access_pro` INT NULL,
		`feature_access_admin` INT NULL,
		`feature_access_god` INT NULL,
	PRIMARY KEY (`feature_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_guilds (
		`guild_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`guild_name` TEXT NOT NULL,
		`guild_logo` TEXT NOT NULL,
		`guild_color` VARCHAR(20) NOT NULL DEFAULT 'amber',
		`guild_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`guild_xp` INT NULL,
		`guild_bloo` INT NULL,
		`guild_capacity` INT NULL,
		`guild_code` VARCHAR(50) NULL DEFAULT '',
		`guild_group` VARCHAR(255) NULL DEFAULT '',
		`guild_members` INT NULL DEFAULT 0,
		`assign_on_login` TINYINT NOT NULL DEFAULT 0,
	PRIMARY KEY (`guild_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_hexad (
		`hexad_id` BIGINT NOT NULL AUTO_INCREMENT,
		`hexad_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`hexad_answers` LONGTEXT NOT NULL,
		`player_id` BIGINT NOT NULL,
	PRIMARY KEY (`hexad_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_items (
		`item_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`org_id` BIGINT NULL,
		`ref_id` VARCHAR(8) NULL,
		`item_parent` BIGINT NULL,
		`item_post_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`item_post_modified` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`item_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`item_author` BIGINT NOT NULL,
		`item_name` VARCHAR(150) NOT NULL,
		`item_description` LONGTEXT NOT NULL,
		`item_secret_description` LONGTEXT NULL,
		`item_cost` INT NOT NULL,
		`item_type` VARCHAR(20) NOT NULL DEFAULT 'consumable',
		`item_visibility` VARCHAR(20) NULL DEFAULT 'visible',
		`item_badge` TEXT NULL,
		`item_secret_badge` TEXT NULL,
		`item_stock` INT NULL,
		`item_player_max` INT NULL,
		`item_level` INT NOT NULL DEFAULT 1,
		`item_category` VARCHAR(40) NULL,
		`item_order` INT NULL,
		`item_deadline` DATETIME NULL,
		`item_start_date` DATETIME NULL,
		`achievement_id` BIGINT NULL DEFAULT 0,
		`tabi_id` BIGINT NULL DEFAULT 0,
		`item_x` DECIMAL(8,3) NULL DEFAULT 0,
		`item_y` DECIMAL(8,3) NULL DEFAULT 0,
		`item_z` INT NULL DEFAULT 0,
		`item_scale` DECIMAL(8,3) NULL DEFAULT 10,
		`item_rotation` INT NULL DEFAULT 0,

	PRIMARY KEY (`item_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_notifications (
		`ann_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`n_status` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`ann_id`, `player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_objectives (
		`objective_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`ref_id` VARCHAR(8) NULL,
		`objective_parent` BIGINT NULL ,
		`objective_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`objective_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`objective_keyword` VARCHAR(150) NULL,
		`objective_content` LONGTEXT NOT NULL,
		`objective_success_message` LONGTEXT NOT NULL,
		`objective_type` VARCHAR(20) NULL DEFAULT 'normal',
		`objective_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`objective_order` INT NULL DEFAULT 1000,
		`ep_cost` INT NULL DEFAULT 0,
		`blog_post_id` BIGINT NULL,
	PRIMARY KEY (`objective_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_orgs (
		`org_id` BIGINT NOT NULL AUTO_INCREMENT,
		`org_name` TEXT NULL,
		`org_logo` TEXT NULL,
		`org_content` LONGTEXT NULL,
		`org_color` VARCHAR(50) NULL DEFAULT 'blue',
		`org_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`org_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`org_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`owner_id` BIGINT NOT NULL,
	PRIMARY KEY (`org_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_org_adventure (
		`org_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
	PRIMARY KEY (`org_id`,`adventure_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_paypal_transactions (
		`paypal_transaction_id` bigint(20) NOT NULL,
		`tnx_id` varchar(255)  NOT NULL,
		`txn_type` varchar(255)  NOT NULL,
		`verify_sign` text  DEFAULT NULL,
		`subscr_date` datetime NOT NULL DEFAULT current_timestamp(),
		`receipt_id` text  NOT NULL,
		`first_name` varchar(64)  NOT NULL,
		`last_name` varchar(64)  NOT NULL,
		`payer_business_name` varchar(127)  NOT NULL,
		`payer_email` varchar(127)  NOT NULL,
		`payer_id` varchar(13)  NOT NULL,
		`payer_status` varchar(13)  NOT NULL,
		`mc_currency` varchar(50)  NOT NULL,
		`mc_fee` varchar(50)  NOT NULL,
		`mc_gross` varchar(50)  NOT NULL,
		`payment_date` datetime NOT NULL,
		`payment_status` varchar(64)  NOT NULL,
		`amount` varchar(64)  NOT NULL,
		`product_name` varchar(127)  NOT NULL,
		`product_type` varchar(127)  NOT NULL,
		`player_id` bigint(20) NOT NULL,
	PRIMARY KEY (`paypal_transaction_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_players (
		`player_id` BIGINT NOT NULL AUTO_INCREMENT,
		`player_email` VARCHAR(255) NOT NULL,
		`player_password` VARCHAR(255) NOT NULL,
		`player_status` VARCHAR(50) NOT NULL DEFAULT 'publish',
		`player_capabilities` VARCHAR(50) NOT NULL DEFAULT 'user',
		`player_access_level` INT NOT NULL DEFAULT 1,
		`player_absolute_level` INT NOT NULL DEFAULT 1,
		`player_first` VARCHAR(255) NULL,
		`player_last` VARCHAR(255) NULL,
		`player_display_name` VARCHAR(255) NULL,
		`player_gmt`  VARCHAR(50) NULL DEFAULT '+00:00',
		`player_country` VARCHAR(50) NULL,
		`player_lang` VARCHAR(8) NOT NULL DEFAULT 'en_US',
		`player_picture` TEXT NULL,
		`player_nickname` VARCHAR(50) NULL,
		`player_twitter` VARCHAR(255) NULL,
		`player_hexad` VARCHAR(50) NULL,
		`player_hexad_slug` VARCHAR(50) NULL,
		`player_registered` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`player_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`player_system_last_login` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`player_secret_code` VARCHAR(255) NULL,
	PRIMARY KEY (`player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_meta (
		player_meta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		player_id BIGINT UNSIGNED NOT NULL,
		player_gender VARCHAR(50) NULL,
		work_level VARCHAR(100) NULL,
		work_function VARCHAR(150) NULL,
		work_sub_function VARCHAR(150) NULL,
		job_profile VARCHAR(150) NULL,
		business_pillar VARCHAR(150) NULL,
		work_cluster VARCHAR(150) NULL,
		work_country VARCHAR(150) NULL,
		work_location VARCHAR(150) NULL,
	PRIMARY KEY (`player_meta_id`) )$charset_collate;





	CREATE TABLE {$wpdb->prefix}br_player_achievement (
		`achievement_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`achievement_applied` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`achievement_id`, `player_id`, `adventure_id`))$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_adventure (
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`achievement_id` BIGINT NULL DEFAULT 0,
		`player_xp` INT NULL DEFAULT 0,
		`player_bloo` INT NULL DEFAULT 0,
		`player_ep` INT NULL DEFAULT 100,
		`player_level` INT NULL DEFAULT 1,
		`player_prev_level` INT NULL DEFAULT 0,
		`player_gpa` INT NULL,
		`player_adventure_status` VARCHAR(20) NOT NULL DEFAULT 'in',
		`player_adventure_role` VARCHAR(20) NOT NULL DEFAULT 'player',
		`player_date_enrolled` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`player_last_login` DATETIME NULL,
		`player_hide_intro` TINYINT NOT NULL DEFAULT '0',
		`player_guild` BIGINT NULL,
		`player_last_random_encounter_id` BIGINT NOT NULL DEFAULT 0,
		`player_current_quest_id` BIGINT NULL DEFAULT 0,
		`player_current_quest_step` BIGINT NULL DEFAULT 1,
	PRIMARY KEY (`player_id`, `adventure_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_blocker (
		`blocker_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`blocker_application_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`blocker_id`, `player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_guild (
		`adventure_id` BIGINT NOT NULL,
		`guild_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`guild_enroll_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`adventure_id`, `guild_id`, `player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_objectives (
		`player_id` BIGINT NOT NULL,
		`objective_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`player_id`, `objective_id`, `adventure_id`) )$charset_collate;
    
	CREATE TABLE {$wpdb->prefix}br_player_energy_log (
		`energy_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`energy` BIGINT NOT NULL,
		`enc_id` BIGINT  NULL,
		`enc_xp` INT NULL,
		`enc_bloo` INT NULL,
		`enc_option_content` TEXT NULL,
		`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`energy_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_org (
		`player_id` BIGINT NOT NULL,
		`org_id` BIGINT NOT NULL,
		`role` VARCHAR(50) NULL DEFAULT 'player',
	PRIMARY KEY (`org_id`, `player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_posts (
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`pp_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`pp_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`pp_status` VARCHAR(20) NULL DEFAULT 'publish',
		`pp_content` LONGTEXT NULL,
		`pp_type` VARCHAR(20) NOT NULL,
		`pp_grade` INT NULL,
		`pp_quest_rating` INT NULL,
		`guild_id` BIGINT NULL,
	PRIMARY KEY (`quest_id`, `adventure_id`, `player_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_player_steps (
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`step_id` BIGINT NOT NULL,
		`ps_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`ps_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`ps_status` VARCHAR(20) NULL DEFAULT 'publish',
		`ps_content` LONGTEXT NULL,
		`ps_req_words` INT NOT NULL DEFAULT 10,
	PRIMARY KEY (`quest_id`, `adventure_id`, `player_id`, `step_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_quests (
		`quest_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_author` BIGINT NOT NULL,
		`quest_order` INT NOT NULL DEFAULT 0,
		`adventure_id` BIGINT NOT NULL,
		`org_id` BIGINT NULL,
		`quest_parent` BIGINT NULL,
		`achievement_id` BIGINT NULL DEFAULT 0,
		`quest_date_posted` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`quest_date_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`quest_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`quest_relevance` VARCHAR(20) NULL DEFAULT 'normal',
		`quest_title` VARCHAR(255) NOT NULL,
		`quest_content` LONGTEXT NOT NULL,
		`quest_success_message` LONGTEXT NULL,
		`quest_guild` TINYINT NOT NULL DEFAULT 0,
		`quest_color` VARCHAR(20) NOT NULL DEFAULT 'amber',
		`quest_icon` VARCHAR(20) NOT NULL DEFAULT 'quest',
		`quest_type` VARCHAR(20) NULL DEFAULT 'quest',
		`quest_style` VARCHAR(20) NULL,
		`quest_secondary_headline` TEXT NULL,
		`milestone_top` FLOAT NULL,
		`milestone_left` FLOAT NULL,
		`milestone_x` FLOAT NULL,
		`milestone_y` FLOAT NULL,
		`milestone_z` FLOAT NULL,
		`milestone_rotation` FLOAT NULL,
		`mech_level` INT NULL DEFAULT 1,
		`mech_xp` INT NULL DEFAULT 0,
		`mech_bloo` INT NULL DEFAULT 0,
		`mech_ep` INT NULL DEFAULT 0,
		`mech_badge` TEXT NULL,
		`mech_deadline` DATETIME NULL,
		`mech_start_date` DATETIME NULL,
		`mech_deadline_cost` INT NULL DEFAULT 0,
		`mech_unlock_cost` INT NULL DEFAULT 0,
		`mech_min_words` INT NULL,
		`mech_min_links` INT NULL DEFAULT 0,
		`mech_min_images` INT NULL DEFAULT 0,
		`mech_max_attempts` INT NULL,
		`mech_free_attempts` INT NULL,
		`mech_attempt_cost` INT NULL DEFAULT 0,
		`mech_questions_to_display` INT NULL,
		`mech_answers_to_win` INT NULL,
		`mech_time_limit` INT NULL DEFAULT 0,
		`mech_show_answers` TINYINT NULL DEFAULT 0,
		`mech_item_reward` BIGINT NULL,
		`mech_achievement_reward` BIGINT NULL,
	PRIMARY KEY (`quest_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_reqs (
		`req_id` BIGINT NOT NULL AUTO_INCREMENT,
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`req_object_id` BIGINT NULL,
		`req_type` VARCHAR(20) NOT NULL DEFAULT 'quest',
		`ref_id` VARCHAR(8) NULL,
		`req_value` INT NULL,
	PRIMARY KEY (`req_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_settings (
		`setting_id` BIGINT NOT NULL AUTO_INCREMENT,
		`setting_name` VARCHAR(255) NOT NULL,
		`setting_label` TEXT NULL,
		`setting_value` TEXT NULL DEFAULT 0,
		`adventure_id` INT NOT NULL,
	PRIMARY KEY (`setting_id`) )$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_projects` (
		project_id INT AUTO_INCREMENT PRIMARY KEY,
		user_id BIGINT UNSIGNED NOT NULL,
		name VARCHAR(255) NOT NULL,
		description TEXT,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_ai_files` (
		file_id INT AUTO_INCREMENT PRIMARY KEY,
		user_id BIGINT UNSIGNED NOT NULL,
		file_name VARCHAR(255) NOT NULL,
		file_path TEXT NOT NULL,
		uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_project_files` (
		project_file_id INT AUTO_INCREMENT PRIMARY KEY,
		project_id INT NOT NULL,
		file_id INT NOT NULL,
		linked_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_ai_chunks` (
		chunk_id INT AUTO_INCREMENT PRIMARY KEY,
		user_id BIGINT UNSIGNED NOT NULL,
		file_id INT NOT NULL,
		project_id INT NOT NULL,
		chunk_index INT NOT NULL,
		chunk_text LONGTEXT,
		chars INT NULL DEFAULT 0,
		est_tokens INT NULL DEFAULT 0,
		status VARCHAR(20) NOT NULL DEFAULT 'ready',
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_ai_processed_chunks` (
		processed_chunk_id INT AUTO_INCREMENT PRIMARY KEY,
		chunk_id INT NOT NULL,
		project_id INT NOT NULL,
		user_id BIGINT UNSIGNED NOT NULL,
		processing_style TEXT NULL,
		gpt_model VARCHAR(50) NOT NULL,
		depth VARCHAR(50),
		prompt_settings TEXT,
		ai_response LONGTEXT,
		token_usage INT,
		cost_estimate DECIMAL(10, 4),
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_ai_temp_results` (
		temp_result_id INT AUTO_INCREMENT PRIMARY KEY,
		user_id BIGINT UNSIGNED NOT NULL,
		data LONGTEXT,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)$charset_collate;

	CREATE TABLE `{$wpdb->prefix}br_ai_project_context` (
		context_id INT AUTO_INCREMENT PRIMARY KEY,
		project_id INT NOT NULL,
		context_text LONGTEXT,
		tokens INT,
		updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		label VARCHAR(100) DEFAULT 'default'
	)$charset_collate;



	CREATE TABLE {$wpdb->prefix}br_speakers (
		`speaker_id` BIGINT NOT NULL AUTO_INCREMENT,
		`player_id` INT NULL,
		`adventure_id` INT NULL,
		`speaker_first_name` LONGTEXT NOT NULL,
		`speaker_last_name` TEXT NULL,
		`speaker_bio` LONGTEXT NULL,
		`speaker_picture` TEXT NULL,
		`speaker_company` TEXT NULL,
		`speaker_website` TEXT NULL,
		`speaker_linkedin` TEXT NULL,
		`speaker_twitter` TEXT NULL,
		`speaker_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
	PRIMARY KEY (`speaker_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_sponsors (
		`sponsor_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` INT NULL,
		`sponsor_name` LONGTEXT NOT NULL,
		`sponsor_url` TEXT NULL,
		`sponsor_logo` LONGTEXT NULL,
		`sponsor_color` TEXT NULL,
		`sponsor_level` INT NULL,
		`sponsor_image` TEXT NULL,
		`sponsor_about` LONGTEXT NULL,
		`sponsor_twitter` TEXT NULL,
		`sponsor_linkedin` TEXT NULL,
		`sponsor_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
	PRIMARY KEY (`sponsor_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_steps (
		`step_id` BIGINT NOT NULL AUTO_INCREMENT,
		`step_title` VARCHAR(150) NULL,
		`step_content` LONGTEXT NULL,
		`step_image` TEXT NULL,
		`step_character_name` TEXT NULL,
		`step_character_image` TEXT NULL,
		`step_background` TEXT NULL,
		`step_type` VARCHAR(50) NOT NULL DEFAULT 'dialogue',
		`step_attach` VARCHAR(255) NULL,
		`step_achievement_group` VARCHAR(50) NULL,
		`step_order` INT NULL DEFAULT 9999,
		`step_next` INT NULL,
		`step_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`step_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`step_modified` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`step_settings` LONGTEXT NULL,
		`step_item` INT NOT NULL DEFAULT 0,
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`ref_id` VARCHAR(8) NULL,
		`step_parent` BIGINT NULL,
	PRIMARY KEY (`step_id`) )$charset_collate;
	
	CREATE TABLE {$wpdb->prefix}br_step_buttons (
		`button_id` BIGINT NOT NULL AUTO_INCREMENT,
		`step_id` BIGINT NOT NULL,
		`quest_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`button_type` VARCHAR(50) NOT NULL DEFAULT 'jump',
		`button_text` VARCHAR(150) NULL,
		`button_image` TEXT NULL,
		`button_object_id` INT NULL,
		`button_ep_cost` INT NULL DEFAULT 0,
		`button_step_next` INT NULL,
		`button_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
		`button_parent` BIGINT NULL,
		`ref_id` VARCHAR(8) NULL,
	PRIMARY KEY (`button_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_survey_answers (
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`survey_id` BIGINT NOT NULL,
		`survey_question_id` BIGINT NOT NULL,
		`survey_option_id` BIGINT NULL,
		`survey_answer_value` TEXT NULL,
		`survey_answer_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
		`survey_answer_modified` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`player_id`,`survey_id`,`survey_question_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_survey_options (
		`survey_option_id` BIGINT NOT NULL AUTO_INCREMENT,
		`survey_id` BIGINT NOT NULL,
		`survey_option_text` LONGTEXT NOT NULL,
		`survey_option_image` TEXT NULL,
		`survey_option_status` VARCHAR(20) NULL DEFAULT 'publish',
		`survey_question_id` BIGINT NOT NULL,
		`survey_option_parent` BIGINT NULL,
		`ref_id` VARCHAR(8) NULL,
	PRIMARY KEY (`survey_option_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_survey_questions (
		`survey_question_id` BIGINT NOT NULL AUTO_INCREMENT,
		`survey_id` BIGINT NOT NULL,
		`survey_question_text` LONGTEXT NOT NULL,
		`survey_question_image` TEXT NULL,
		`survey_question_status` VARCHAR(20) NULL DEFAULT 'publish',
		`survey_question_type` VARCHAR(20) NULL DEFAULT 'closed',
		`survey_question_display` VARCHAR(20) NULL DEFAULT 'expanded',
		`survey_question_range` INT NULL,
		`survey_question_order` INT NOT NULL DEFAULT 0,
		`survey_question_description` LONGTEXT NULL,
		`survey_question_parent` BIGINT NULL,
		`ref_id` VARCHAR(8) NULL,
	PRIMARY KEY (`survey_question_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_sessions (
		`session_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`session_title` TEXT NULL,
		`session_start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`session_end` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`session_room` TEXT NULL,
		`session_description` LONGTEXT NULL,
		`session_order` INT NOT NULL DEFAULT 0,
		`quest_id` BIGINT NULL,
		`speaker_id` BIGINT NULL,
		`achievement_id` BIGINT NULL DEFAULT 0,
		`guild_id` BIGINT NULL,
		`session_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
	PRIMARY KEY (`session_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_transactions (
		`trnx_id` BIGINT NOT NULL AUTO_INCREMENT,
		`player_id` BIGINT NOT NULL,
		`adventure_id` BIGINT NOT NULL,
		`object_id` BIGINT NOT NULL,
		`trnx_author` BIGINT NOT NULL,
		`trnx_amount` INT NULL DEFAULT 0,
		`trnx_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`trnx_type` VARCHAR(20) NOT NULL,
		`trnx_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`trnx_status` VARCHAR(20) NULL DEFAULT 'publish',
		`trnx_use` TINYINT NOT NULL DEFAULT 0,
	PRIMARY KEY (`trnx_id`) )$charset_collate;
	
";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql);
	$new_pages = array(
		"About Adventure",
		"Achievement",
		"Achievements",
		"Adventure",
		"Adventures",
		"Adventure Summary",
		"Documents", // Page to show unprocessed chunks. Here we display all chunk cards and allow the user to edit and customize the project.
		"Document Chunks", // Page to show unprocessed chunks. Here we display all chunk cards and allow the user to edit and customize the project.
		"Projects", // List of projects to access and manage them.
		"Project", // Here we edit name and description of the project.
		"Assign Achievement",
		"Backpack",
		"Blocker",
		"Blockers",
		"Blog Post",
		"Blog",
		"Builder",
		"Challenge",
		"Challenges",
		"Challenges Report",
		"Certificate",
		"Config",
		"Duplicator",
		"Encounters",
		"Enroll",
		"Guild",
		"Guilds",
		"Guild Enroll",
		"Item Shop",
		"Item",
		"Leaderboard",
		"Login",
		"Lore",
		"Magic Link",
		"Manage Adventure",
		"Manage Adventures",
		"Manage Players",
		"Mission",
		"My Account",
		"My Organization",
		"My Report",
		"New Achievement",
		"New Adventure",
		"New Tabi",
		"New Blocker",
		"New Blog Post",
		"New Challenge",
		"New Encounter",
		"New Guild",
		"New HEXAD",
		"New Item",
		"New Lore",
		"New Mission",
		"New Organization",
		"New Player Post",
		"New Quest",
		"New Speaker",
		"New Survey",
		"New Session",
		"Organization",
		"Organizations",
		"No Access",
		"Paypal Ipn",
		"Player Work",
		"Player",
		"Players",
		"Post",
		"Quest",
		"Register",
		"Report",
		"Review Player Posts",
		"Schedule",
		"Secrets and clues",
		"Session",
		"Speaker",
		"Speakers",
		"Survey Results",
		"Survey",
		"Stats",
		"Transactions",
		"Tabis",
		"Wall"
	);
	foreach ($new_pages as $np) {
		// Exact, case-insensitive match on post_title
		$q = new WP_Query([
			'post_type'      => 'page',
			'title'          => $np,       // exact match on the title
			'post_status'    => 'any',     // mirror get_page_by_title behavior
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'fields'         => 'ids',     // faster: return only IDs
		]);

		if (empty($q->posts)) {
			$nparray = [
				'post_type'    => 'page',
				'post_title'   => $np,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
			];
			$npid = wp_insert_post($nparray);
		}
	}
}
// remove wp version param from any enqueued scripts
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );

function theme_name_setup(){
	load_theme_textdomain( 'bluerabbit', get_template_directory() . '/languages' );
}
add_filter( 'locale', 'br_theme_localized' );
function br_theme_localized($locale){
	global $wpdb;
    $current_user  = wp_get_current_user();
	if($current_user){
		$data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_players WHERE player_id=$current_user->ID");
		$locale = $data ?  $data->player_lang : '';
	}
	if(!$locale){
		$config = getSysConfig();
        $locale = $config['default_language']['value'] ? $config['default_language']['value'] : 'en_US';
	}
	return $locale;
}

load_theme_textdomain( 'bluerabbit', get_template_directory().'/languages' );

function delete_roles () {
	remove_role( 'br_player' ); 
	remove_role( 'br_game_master' ); 
	remove_role( 'br_npc' ); 
}
function create_slug($string){
   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}
function user_has_role($user_id, $role_name)
{
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;
    return in_array($role_name, $user_roles);
}
//////////////////////////   get Setting //////////////////////////////
function getSetting($name,$adv_id){
	global $wpdb;
	$data = array();
	$setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_settings WHERE setting_name='$name' AND adventure_id=$adv_id");
	if($setting){
		return $setting->setting_value;
	}else{
		return false;
	}
}
//////////////////////////   get Settings //////////////////////////////
function getSettings($adv_id){
	global $wpdb;
	$settings = array();
	$settings_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_settings WHERE adventure_id=$adv_id");
	foreach($settings_query as $key=>$sq){
		$settings[$sq->setting_name]['id'] = $sq->setting_id;
		$settings[$sq->setting_name]['label'] = $sq->setting_label;
		$settings[$sq->setting_name]['value'] = $sq->setting_value;
	}
	return $settings;
}
//////////////////////////   FEATURES  //////////////////////////////
function getFeatures($role=NULL){
	global $wpdb; 
	$features = array();
	if($role){
		$the_role = 'feature_access_'.$role;
		$features_query = $wpdb->get_results("SELECT feature_id, feature_name, feature_label, feature_desc, feature_type, $the_role  FROM {$wpdb->prefix}br_features WHERE $the_role != '' ");
		foreach($features_query as $key=>$f){
			$features[$f->feature_name]['id'] = $f->feature_id;
			$features[$f->feature_name]['label'] = $f->feature_label;
			$features[$f->feature_name]['desc'] = $f->feature_desc;
			$features[$f->feature_name]['type'] = $f->feature_type;
			$features[$f->feature_name][$role] = $f->$the_role;
		}
	}else{
		$features_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_features");	
		foreach($features_query as $key=>$f){
			$features[$f->feature_name]['id'] = $f->feature_id;
			$features[$f->feature_name]['label'] = $f->feature_label;
			$features[$f->feature_name]['desc'] = $f->feature_desc;
			$features[$f->feature_name]['type'] = $f->feature_type;
			$features[$f->feature_name]['free'] = $f->feature_access_free;
			$features[$f->feature_name]['pro'] = $f->feature_access_pro;
			$features[$f->feature_name]['admin'] = $f->feature_access_admin;
			$features[$f->feature_name]['god'] = $f->feature_access_god;
		}
	}
	
	if(!empty($features)){
		return $features;
	}else{
		return false;
	}
}

//////////////////////////   System Configuration  //////////////////////////////
function getSysConfig($config_name=NULL){
	global $wpdb; 
	$config = array();
	if($config_name){
		$sq = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_config WHERE config_name='$config_name'");
		if($sq){
			$config[$sq->config_name]['id'] = $sq->config_id;
			$config[$sq->config_name]['label'] = $sq->config_label;
			$config[$sq->config_name]['desc'] = $sq->config_desc;
			$config[$sq->config_name]['type'] = $sq->config_type;
			$config[$sq->config_name]['value'] = $sq->config_value;
		}
	}else{
		$config_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_config");
		foreach($config_query as $key=>$sq){
			$config[$sq->config_name]['id'] = $sq->config_id;
			$config[$sq->config_name]['label'] = $sq->config_label;
			$config[$sq->config_name]['desc'] = $sq->config_desc;
			$config[$sq->config_name]['type'] = $sq->config_type;
			$config[$sq->config_name]['value'] = $sq->config_value;
		}
	}
	return $config;
}

function embed_responsive_videos( $html ) {
    return '<div class="responsive-video-container">' . $html . '</div>';
}
 
add_filter( 'embed_oembed_html', 'embed_responsive_videos', 10, 3 );
add_filter( 'video_embed_html', 'embed_responsive_videos' ); // Jetpack

function add_upload_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
    return $mimes;
} 

function shuffle_assoc($list) { 
  if (!is_array($list)) return $list; 

  $keys = array_keys($list); 
  shuffle($keys); 
  $random = array(); 
  foreach ($keys as $key) { 
    $random[$key] = $list[$key]; 
  }
  return $random; 
} 
function identical_values( $arrayA , $arrayB ) { 
    sort( $arrayA ); 
    sort( $arrayB ); 
    return $arrayA == $arrayB; 
} 
function substrwords($text, $maxchar, $end='...') {
	if (strlen($text) > $maxchar || $text == '') {
		$words = preg_split('/\s/', $text);      
		$output = '';
		$i      = 0;
		while (1) {
			$length = strlen($output)+strlen($words[$i]);
			if ($length > $maxchar) {
				break;
			} 
			else {
				$output .= " " . $words[$i];
				++$i;
			}
		}
		$output .= $end;
	} 
	else {
		$output = $text;
	}
	return $output;
}
function get_time_ago( $time, $adventure_id=0 ){
	global $wpdb;
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
    $time_difference = time() - $time;
    if( $time_difference < 1 ) { return __('less than 1 second ago',"bluerabbit"); }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                30 * 24 * 60 * 60       =>  __('month',"bluerabbit"),
                24 * 60 * 60            =>  __('day',"bluerabbit"),
                60 * 60                 =>  __('hour',"bluerabbit"),
                60                      =>  __('minute',"bluerabbit"),
                1                       =>  __('second',"bluerabbit")
    );
    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;

        if( $d >= 1 )
        {
            $t = round( $d );
            return $t . ' ' . $str . ( $t > 1 ? 's' : '' );
        }
    }
}

function br_est_tokens($text) { return max(1, (int)ceil(mb_strlen($text,'UTF-8')/4)); }
function br_est_cost($model, $in_toks, $out_cap = 800) {
  $prices = [
    'gpt-4o-mini'   => ['in'=>0.000150/1000,'out'=>0.000600/1000],
    'gpt-3.5-turbo' => ['in'=>0.000500/1000,'out'=>0.001500/1000],
    'gpt-4o'        => ['in'=>0.000500/1000,'out'=>0.001500/1000],
  ];
  $p = $prices[$model] ?? $prices['gpt-4o-mini'];
  return round($in_toks*$p['in'] + $out_cap*$p['out'], 4);
}


function registerAdventureLogin($adventure_id) { 
	global $wpdb; $current_user = wp_get_current_user();
	$adventure = $wpdb->get_row("
		SELECT adv.*, player.player_last_login FROM {$wpdb->prefix}br_adventures adv LEFT JOIN 
		{$wpdb->prefix}br_player_adventure player ON adv.adventure_id=player.adventure_id AND player.player_id=$current_user->ID
		WHERE adv.adventure_id=$adventure_id
	");
	$debug = print_r($wpdb->last_query,true);
	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$today_compare = date('Ymd');
	$last_login = $adventure->player_last_login ? date('Ymd', strtotime($adventure->player_last_login)) : 0;
	logActivity($adventure_id,'login','adventure');
	if($today_compare > $last_login){
		$sql="UPDATE {$wpdb->prefix}br_player_adventure SET player_last_login=%s WHERE adventure_id=$adventure_id AND player_id=$current_user->ID";
		$registerLogin = $wpdb->query($wpdb->prepare($sql, $today, $adventure_id, $current_user->ID));
		return(true);
	}else{
		return(false);
	}
	die();
} 
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png);
            padding-bottom: 30px;
			width:230px;
			background-size:cover;
        }
		p#nav{
			display:none;
		}
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function createHexad(){
	$testHexad= array(
		array('player_style'=>"ph","question"=>__("It makes me happy if I am able to help others","bluerabbit")),
		array('player_style'=>"ph","question"=>__("I like helping others to orient themselves in new situations","bluerabbit")),
		array('player_style'=>"ph","question"=>__("I like sharing my knowledge with others","bluerabbit")),
		array('player_style'=>"ph","question"=>__("The well being of others is important to me","bluerabbit")),
		array('player_style'=>"s","question"=>__("Interacting with others is important to me","bluerabbit")),
		array('player_style'=>"s","question"=>__("I like being part of a team","bluerabbit")),
		array('player_style'=>"s","question"=>__("It is important for me to feel like I am part of a community","bluerabbit")),
		array('player_style'=>"s","question"=>__("I enjoy group activities","bluerabbit")),
		array('player_style'=>"f","question"=>__("It is important to me to follow my own path","bluerabbit")),
		array('player_style'=>"f","question"=>__("I often let my curiosity guide me","bluerabbit")),
		array('player_style'=>"f","question"=>__("I like to try new things","bluerabbit")),
		array('player_style'=>"f","question"=>__("Being independent is important to me","bluerabbit")),
		array('player_style'=>"a","question"=>__("I like overcoming obstacles","bluerabbit")),
		array('player_style'=>"a","question"=>__("It is important to me to always carry out my tasks completely","bluerabbit")),
		array('player_style'=>"a","question"=>__("It is difficult for me to let go of a problem before I have found a solution","bluerabbit")),
		array('player_style'=>"a","question"=>__("I like mastering difficult tasks","bluerabbit")),
		array('player_style'=>"d","question"=>__("I like to provoke","bluerabbit")),
		array('player_style'=>"d","question"=>__("I like to question the status quo","bluerabbit")),
		array('player_style'=>"d","question"=>__("I see myself as a rebel","bluerabbit")),
		array('player_style'=>"d","question"=>__("I dislike following rules","bluerabbit")),
		array('player_style'=>"p","question"=>__("I like competitions where a prize can be won","bluerabbit")),
		array('player_style'=>"p","question"=>__("Rewards are a great way to motivate me","bluerabbit")),
		array('player_style'=>"p","question"=>__("Return of investment is important to me","bluerabbit")),
		array('player_style'=>"p","question"=>__("If the reward is enough I will put in the effort","bluerabbit")),
	);
	
	return $testHexad;
}


// ////////////////// TIMEZONES /////////////////////////

function generate_timezone_list(){
	static $regions = array(
		DateTimeZone::AFRICA,
		DateTimeZone::AMERICA,
		DateTimeZone::ANTARCTICA,
		DateTimeZone::ASIA,
		DateTimeZone::ATLANTIC,
		DateTimeZone::AUSTRALIA,
		DateTimeZone::EUROPE,
		DateTimeZone::INDIAN,
		DateTimeZone::PACIFIC,
	);

	$timezones = array();
	foreach( $regions as $region )
	{
		$timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
	}

	$timezone_offsets = array();
	foreach( $timezones as $timezone )
	{
		$tz = new DateTimeZone($timezone);
		$timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
	}

	// sort timezone by timezone name
	ksort($timezone_offsets);

	$timezone_list = array();
	foreach( $timezone_offsets as $timezone => $offset ) {

		$t = new DateTimeZone($timezone);
		$c = new DateTime(null, $t);
		$current_time = $c->format('g:i A');
		$timezone_list[$timezone] = "$timezone - $current_time";
		//$timezone_list[$offset] = "$offset";
	}

	return $timezone_list;
}
function random_str($length, $keyspace = '!@#$&0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
function all_admin_init_functions() {
	if (!current_user_can( 'update_core' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		wp_redirect( get_bloginfo('url')); 
		exit;
	}
}


function notFound($p_message,$p_color){
	$message = $p_message ? $p_message : __("Empty","bluerabbit");
	$color = $p_color ? $p_color : "blue-grey";
	$notFound = '
		<div class="highlight padding-10 '.$color.'-bg-50 text-center">
			<span class="icon-group">
				<span class="icon-content">
					<span class="icon icon-cancel"></span>'.$message.'
				</span>
			</span>
		</div>
	';
	return $notFound;
}
function addNewButton($p_message,$p_color,$type=NULL, $adventure_id=NULL){
	if($type && $adventure_id){
		$message = $p_message ? $p_message : __("Add new","bluerabbit");
		$color = $p_color ? $p_color : "blue-grey";
		$button = '
			<div class="highlight padding-10 white-bg-50 text-center">
				<a href="'.get_bloginfo('url').'/new-'.$type.'/?adventure_id='.$adventure_id.'" class="form-ui '.$color.'-bg-400">
					<span class="icon icon-add"></span>'.$message.'
				</a>
			</div>
		';
		return $button;
	}else{
		return false;
	}
	
}
function my_default_image_size () {
    return 'medium'; 
}
add_filter( 'pre_option_image_default_size', 'my_default_image_size' );

function tiny_mce_add_extra_buttons() {
    if(!is_page('quest')){
		add_filter( 'mce_external_plugins', 'add_extra_buttons_plugin' );
		add_filter( 'mce_buttons_2', 'register_player_data_buttons' );
	}
}
function register_player_data_buttons( $buttons ) {
    if(is_page('new-quest')){
        array_push( $buttons, 'quest_instructions_page_break' );
    }
    array_unshift( $buttons, 'underline' );
    array_unshift( $buttons, 'removeformat' );
    array_push( $buttons, 'player_data' );
    return $buttons;
}
function add_extra_buttons_plugin( $plugin_array ) {
	$plugin_array['player_data'] = get_bloginfo('template_directory').'/js/tiny-mce-extra-buttons.js';
	$plugin_array['quest_instructions_page_break'] = get_bloginfo('template_directory').'/js/tiny-mce-extra-buttons.js';
	return $plugin_array;
}
add_action('init', 'tiny_mce_add_extra_buttons');

function player_data_shortcode($atts=array()) {
    extract(shortcode_atts(
        array( 'field' => 'player_nickname'), $atts)
    );
    $current_user = wp_get_current_user();
    $player_data = getPlayerData($current_user->ID, 'ARRAY_A');
    $content = "<strong class='font capitalize player-nickname'>{$player_data[$field]}</strong>";
    return $content;
} 
function quest_instructions_page_break_shortcode($atts=array()) {
    $content = "
		<br class='clear'>
		<input type='hidden' class='step-id-value' id=''>
		<div class='instructions-nav'>
			<button class='form-ui blue-bg-400 white-color prev-button'>
				<span class='icon icon-arrow-left'></span>".__("Prev","bluerabbit")."
			</button>
			<button class='form-ui blue-bg-400 white-color pull-right next-button'>
				".__("Next","bluerabbit")."<span class='icon icon-arrow-right'></span>
			</button>
		</div>
	</div>
	<div class='instructions-step'>";
    return $content;
} 

// register shortcode
add_shortcode('player_data', 'player_data_shortcode'); 
add_shortcode('page_break', 'quest_instructions_page_break_shortcode'); 


/*

CLASSES

*/


function stepTag($the_text=""){
	include (TEMPLATEPATH.'/step-tag.php');
}

add_filter( 'wp_video_shortcode', 'remove_video_dimensions_from_shortcode' );
function remove_video_dimensions_from_shortcode( $output ) {
	$output = preg_replace( '/width="[^"]*"/i', '', $output );
	$output = preg_replace( '/height="[^"]*"/i', '', $output );
	return $output;
}

add_action( 'template_redirect', 'my_redirect_if_user_not_logged_in' );
	
function my_redirect_if_user_not_logged_in() {
	if (!is_user_logged_in() && !is_page('login') && !is_page('enroll')){
		if(isset($_GET['c'])){
			$redirect = '?c=' . $_GET['c'];
		}elseif(isset($_GET['enroll_code'])){
			$redirect = '?enroll_code=' . $_GET['enroll_code'];
		}else{
			$redirect = "";
		}
		wp_redirect(get_bloginfo('url').'/login/'.$redirect);
		exit;
	}
}

add_action( 'admin_init', 'all_admin_init_functions');

$dirName = dirname(__FILE__);
$baseName = basename(realpath($dirName));



//********************************************************************************//
//--------------------------- UPLOAD FRONT END -----------------------------------//
//................................................................................//

function filter_media( $query ) {
	// admins get to see everything
	if (!current_user_can( 'manage_options' ) ){
		$query['author'] = get_current_user_id();
	}
	return $query;
}
add_filter( 'ajax_query_attachments_args','filter_media' );

//********************************************************************************//
//------------------------------------- AJAX -------------------------------------//
//................................................................................//

function ajaxFunctions() {
	wp_enqueue_script( 'ajaxFunctions', get_template_directory_uri().'/script.js', 'jquery', true); 
	wp_localize_script( 'ajaxFunctions', 'runAJAX', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

require_once ("$dirName/functions/ajax.php");
require_once ("$dirName/functions/player.php");
require_once ("$dirName/functions/adventure-management.php");
require_once ("$dirName/functions/progression.php");
require_once ("$dirName/classes/Notification.php");
require_once ("$dirName/classes/Project.php");

$br_project = new Project();
$n = new Notification();

add_action( 'after_setup_theme', 'theme_name_setup' );
add_filter( 'upload_mimes', 'add_upload_mime_types' );
add_action('after_switch_theme', 'theme_core_setup');
add_action('switch_theme', 'delete_roles');
add_filter('show_admin_bar', '__return_false');
add_action('template_redirect', 'ajaxFunctions');

add_action('wp_login', 'absolute_level_calc');


///////// FUNCTION PERMISSIONS //////////////

add_action("wp_ajax_br_notify", "notify");
add_action("wp_ajax_switchRank", "switchRank");
add_action("wp_ajax_closeIntro", "closeIntro");
add_action("wp_ajax_resetIntro", "resetIntro");
add_action("wp_ajax_resetPrevLevel", "resetPrevLevel");
add_action("wp_ajax_resetGuilds", "resetGuilds");
add_action("wp_ajax_resetPlayerAdventure", "resetPlayerAdventure");
add_action("wp_ajax_updatePlayer", "updatePlayer");
add_action("wp_ajax_setGrade", "setGrade");
add_action("wp_ajax_updateProfile", "updateProfile");
add_action("wp_ajax_nopriv_bluerabbit_add_new_player", "bluerabbit_add_new_player");
add_action("wp_ajax_bluerabbit_add_new_player", "bluerabbit_add_new_player");
add_action("wp_ajax_checkUserDataExists", "checkUserDataExists");
add_action("wp_ajax_enrollUser", "enrollUser");
add_action("wp_ajax_uploadBulkUsers", "uploadBulkUsers");
add_action("wp_ajax_bulkEnrollUsers", "bulkEnrollUsers");
add_action("wp_ajax_uploadBulkSpeakers", "uploadBulkSpeakers");
add_action("wp_ajax_uploadBulkSessions", "uploadBulkSessions");
add_action("wp_ajax_uploadBulkQuests", "uploadBulkQuests");
add_action("wp_ajax_uploadBulkQuestions", "uploadBulkQuestions");
add_action("wp_ajax_uploadBulkAchievements", "uploadBulkAchievements");
add_action("wp_ajax_uploadBulkItems", "uploadBulkItems");
add_action("wp_ajax_newHexad", "newHexad");
add_action("wp_ajax_reorder", "reorder");
add_action("wp_ajax_reorderItems", "reorderItems");
add_action("wp_ajax_reorderAchievements", "reorderAchievements");
add_action("wp_ajax_reorderQuestions", "reorderQuestions");
add_action("wp_ajax_loadJourney", "loadJourney");
add_action("wp_ajax_loadQuestCard", "loadQuestCard");
add_action("wp_ajax_loadItemCard", "loadItemCard");
add_action("wp_ajax_loadBackpackItem", "loadBackpackItem");
add_action("wp_ajax_loadAchievementCard", "loadAchievementCard");
add_action("wp_ajax_displayAchievementCard", "displayAchievementCard");
add_action("wp_ajax_loadGuildCard", "loadGuildCard");
add_action("wp_ajax_loadSpeakerCard", "loadSpeakerCard");
add_action("wp_ajax_loadSessionCard", "loadSessionCard");
add_action("wp_ajax_loadBlogCard", "loadBlogCard");
add_action("wp_ajax_loadLore", "loadLore");
add_action("wp_ajax_searchLore", "searchLore");
add_action("wp_ajax_updateAdventure", "updateAdventure");
add_action("wp_ajax_resetHideIntro", "resetHideIntro");
add_action("wp_ajax_addTabi", "addTabi");
add_action("wp_ajax_insertTabiRow", "insertTabiRow");
add_action("wp_ajax_saveTabiPiecePosition", "saveTabiPiecePosition");
add_action("wp_ajax_updateMilestonePosition", "updateMilestonePosition");
add_action("wp_ajax_updateQuest", "updateQuest");
add_action("wp_ajax_updateEncounter", "updateEncounter");
add_action("wp_ajax_randomEncounter", "randomEncounter");
add_action("wp_ajax_answerEncounter", "answerEncounter");
add_action("wp_ajax_updateAchievement", "updateAchievement");
add_action("wp_ajax_setAchievement", "setAchievement");
add_action("wp_ajax_setGuild", "setGuild");
add_action("wp_ajax_setSpeaker", "setSpeaker");
add_action("wp_ajax_setSpeakerData", "setSpeakerData");
add_action("wp_ajax_updateOrg", "updateOrg");
add_action("wp_ajax_updateSponsor", "updateSponsor");
add_action("wp_ajax_findPlayersToOrg", "findPlayersToOrg");
add_action("wp_ajax_addPlayerToOrg", "addPlayerToOrg");
add_action("wp_ajax_setPlayerOrgCapabilities", "setPlayerOrgCapabilities");
add_action("wp_ajax_previewTemplate", "previewTemplate");
add_action("wp_ajax_createChildAdventure", "createChildAdventure");
add_action("wp_ajax_updateGuild", "updateGuild");
add_action("wp_ajax_updateBlocker", "updateBlocker");
add_action("wp_ajax_payBlocker", "payBlocker");
add_action("wp_ajax_payment", "payment");
add_action("wp_ajax_submitPlayerWork", "submitPlayerWork");
add_action("wp_ajax_validatePlayerWork", "validatePlayerWork");
add_action("wp_ajax_postToWall", "postToWall");
add_action("wp_ajax_loadChat", "loadChat");
add_action("wp_ajax_updateItem", "updateItem");
add_action("wp_ajax_buyItem", "buyItem");
add_action("wp_ajax_pickupItem", "pickupItem");
add_action("wp_ajax_checkItem", "checkItem");
add_action("wp_ajax_useItem", "useItem");
add_action("wp_ajax_getUnlocks", "getUnlocks");
add_action("wp_ajax_registerAttempt", "registerAttempt");
add_action("wp_ajax_startAttempt", "startAttempt");
add_action("wp_ajax_submitAnswer", "submitAnswer");
add_action("wp_ajax_gradeChallenge", "gradeChallenge");
add_action("wp_ajax_setCurrentQuest", "setCurrentQuest");
add_action("wp_ajax_submitSurveyAnswer", "submitSurveyAnswer");
add_action("wp_ajax_br_trash", "br_trash");
add_action("wp_ajax_br_empty_trash", "br_empty_trash");
add_action("wp_ajax_magicCode", "magicCode");
add_action("wp_ajax_choosePath", "choosePath");
add_action("wp_ajax_triggerAchievement", "triggerAchievement");
add_action("wp_ajax_triggerAchievements", "triggerAchievements");
add_action("wp_ajax_triggerGuild", "triggerGuild");
add_action("wp_ajax_resetTransactions", "resetTransactions");
add_action("wp_ajax_resetDemoAdventure", "resetDemoAdventure");
add_action("wp_ajax_resetDemoAdventurePlayer", "resetDemoAdventurePlayer");
add_action("wp_ajax_resetPlayerPassword", "resetPlayerPassword");
add_action("wp_ajax_setPlayerAdventureRole", "setPlayerAdventureRole");
add_action("wp_ajax_updatePlayerAdventureStatus", "updatePlayerAdventureStatus");
add_action("wp_ajax_updateAdventureTitle", "updateAdventureTitle");
add_action("wp_ajax_setTitle", "setTitle");
add_action("wp_ajax_setBadge", "setBadge");
add_action("wp_ajax_setColor", "setColor");
add_action("wp_ajax_setLevel", "setLevel");
add_action("wp_ajax_setXP", "setXP");
add_action("wp_ajax_setEP", "setEP");
add_action("wp_ajax_setBLOO", "setBLOO");
add_action("wp_ajax_setMaxPlayers", "setMaxPlayers");
add_action("wp_ajax_setHashtags", "setHashtags");
add_action("wp_ajax_setHandle", "setHandle");
add_action("wp_ajax_setTweets", "setTweets");
add_action("wp_ajax_setStartDate", "setStartDate");
add_action("wp_ajax_setDeadline", "setDeadline");
add_action("wp_ajax_setMagicCode", "setMagicCode");
add_action("wp_ajax_setCategory", "setCategory");
add_action("wp_ajax_setItemStock", "setItemStock");
add_action("wp_ajax_setGuildGroup", "setGuildGroup");
add_action("wp_ajax_setGuildCapacity", "setGuildCapacity");
add_action("wp_ajax_setDisplayStyle", "setDisplayStyle");
add_action("wp_ajax_setDimensions", "setDimensions");
add_action("wp_ajax_setTabiOnJourney", "setTabiOnJourney");
add_action("wp_ajax_setNickname", "setNickname");
add_action("wp_ajax_setProfilePicture", "setProfilePicture");
add_action("wp_ajax_exportPlayersWork", "exportPlayersWork");
add_action("wp_ajax_newUniqueAchievementCode", "newUniqueAchievementCode");
add_action("wp_ajax_deleteAchievementCode", "deleteAchievementCode");
add_action("wp_ajax_duplicateQuests", "duplicateQuests");
add_action("wp_ajax_duplicateQuest", "duplicateQuest");
add_action("wp_ajax_duplicateRow", "duplicateRow");
add_action("wp_ajax_breakParent", "breakParent");
add_action("wp_ajax_updatePrevLevel", "updatePrevLevel");
add_action("wp_ajax_rateQuest", "rateQuest");
add_action("wp_ajax_failQuest", "failQuest");
add_action("wp_ajax_saveSettings", "saveSettings");
add_action("wp_ajax_saveSetting", "saveSetting");
add_action("wp_ajax_saveSysConfig", "saveSysConfig");
add_action("wp_ajax_anonimizeAdventure", "anonimizeAdventure");
add_action("wp_ajax_spendEP", "spendEP");
add_action("wp_ajax_addObjective", "addObjective");
add_action("wp_ajax_editObjective", "editObjective");
add_action("wp_ajax_updateObjective", "updateObjective");
add_action("wp_ajax_resetQuestObjectives", "resetQuestObjectives");
add_action("wp_ajax_removeObjective", "removeObjective");
add_action("wp_ajax_getObjectives", "getObjectives");
add_action("wp_ajax_factCheck", "factCheck");
add_action("wp_ajax_insertSolvedObjective", "insertSolvedObjective");
add_action("wp_ajax_addStep", "addStep");
add_action("wp_ajax_editStep", "editStep");
add_action("wp_ajax_removeStep", "removeStep");
add_action("wp_ajax_updateStep", "updateStep");
add_action("wp_ajax_newStepListItem", "newStepListItem");
add_action("wp_ajax_reorderSteps", "reorderSteps");
add_action("wp_ajax_loadStepButtonForm", "loadStepButtonForm");
add_action("wp_ajax_addStepButton", "addStepButton");
add_action("wp_ajax_removeStepButton", "removeStepButton");
add_action("wp_ajax_updateStepButton", "updateStepButton");
add_action("wp_ajax_addQuestion", "addQuestion");
add_action("wp_ajax_duplicateQuestion", "duplicateQuestion");
add_action("wp_ajax_updateQuestion", "updateQuestion");
add_action("wp_ajax_removeQuestion", "removeQuestion");
add_action("wp_ajax_addOption", "addOption");
add_action("wp_ajax_updateOption", "updateOption");
add_action("wp_ajax_removeOption", "removeOption");
add_action("wp_ajax_updateSchedule", "updateSchedule");
add_action("wp_ajax_updateSpeaker", "updateSpeaker");
add_action("wp_ajax_updateSession", "updateSession");
add_action("wp_ajax_downloadAllImages", "downloadAllImages");
add_action("wp_ajax_checkPlayerSecretCode", "checkPlayerSecretCode");
add_action("wp_ajax_loadContent", "loadContent");
add_action("wp_ajax_loadStory", "loadStory");
add_action("wp_ajax_br_logout", "br_logout");



<?php
// Validates a $_GET value that's expected to be a record id (questID, adventure_id,
// achievement_id, etc). Anything that isn't a plain positive integer - garbage,
// a stray title string, an empty value - sends the visitor to 404 instead of
// letting it reach a raw-interpolated SQL query or a ->property access on null
// several includes deeper. A real HTTP redirect isn't reliable here since these
// templates are always called after header.php has already started echoing HTML,
// so this uses the same JS-redirect idiom already used elsewhere in the theme.
function br_require_id( $key, $required = true ) {
	if ( ! isset( $_GET[ $key ] ) || $_GET[ $key ] === '' ) {
		if ( $required ) {
			echo '<script>document.location.href="' . esc_url( get_bloginfo( 'url' ) ) . '/404";</script>';
			exit;
		}
		return null;
	}
	// Present but malformed (not a plain positive integer) always 404s, even on
	// pages where the id itself is optional - a garbage value is never valid input.
	if ( ! ctype_digit( (string) $_GET[ $key ] ) ) {
		echo '<script>document.location.href="' . esc_url( get_bloginfo( 'url' ) ) . '/404";</script>';
		exit;
	}
	return (int) $_GET[ $key ];
}

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
		`achievement_parent` BIGINT NULL,
		`achievement_name` VARCHAR(255) NULL,
		`achievement_xp` INT NULL,
		`achievement_bloo` INT NULL,
		`achievement_ep` INT NULL,
		`achievement_max` INT NULL,
		`achievement_deadline` DATETIME NULL,
		`achievement_badge` TEXT NULL,
		`achievement_qrcode` TEXT NULL,
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
		`tabi_as_category` TINYINT NULL DEFAULT 0,
		`tabi_top` INT NULL DEFAULT 350,
		`tabi_left` INT NULL DEFAULT 350,
	PRIMARY KEY (`tabi_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_journey_assets (
		`asset_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`tabi_id` BIGINT NULL DEFAULT 0,
		`asset_image` TEXT NULL,
		`asset_top` INT NULL DEFAULT 350,
		`asset_left` INT NULL DEFAULT 350,
		`asset_width` INT NULL DEFAULT 200,
		`asset_z` INT NULL DEFAULT 5,
		`asset_rotation` INT NULL DEFAULT 0,
		`asset_type` VARCHAR(30) NOT NULL DEFAULT 'graphic',
		`asset_link` TEXT NULL,
		`asset_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
	PRIMARY KEY (`asset_id`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_tabi_prerequisites (
		`prereq_id` BIGINT NOT NULL AUTO_INCREMENT,
		`tabi_id` BIGINT NOT NULL,
		`requires_tabi_id` BIGINT NOT NULL,
	PRIMARY KEY (`prereq_id`) )$charset_collate;

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

	CREATE TABLE {$wpdb->prefix}br_plans (
		`plan_id` BIGINT NOT NULL AUTO_INCREMENT,
		`plan_key` VARCHAR(50) NOT NULL,
		`plan_label` TEXT NOT NULL,
		`plan_type` VARCHAR(20) NOT NULL DEFAULT 'standard',
		`plan_status` VARCHAR(20) NOT NULL DEFAULT 'active',
		`plan_notes` TEXT NULL,
		`plan_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`plan_id`),
	UNIQUE KEY `plan_key` (`plan_key`) )$charset_collate;

	CREATE TABLE {$wpdb->prefix}br_plan_features (
		`id` BIGINT NOT NULL AUTO_INCREMENT,
		`plan_id` BIGINT NOT NULL,
		`feature_id` BIGINT NOT NULL,
		`feature_value` TEXT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `plan_feature` (`plan_id`, `feature_id`) )$charset_collate;

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

    CREATE TABLE {$wpdb->prefix}br_email_log (
        `log_id`       BIGINT       NOT NULL AUTO_INCREMENT,
        `user_id`      BIGINT       NOT NULL DEFAULT 0,
        `adventure_id` BIGINT       NOT NULL DEFAULT 0,
        `subject`      VARCHAR(255) NOT NULL DEFAULT '',
        `status`       VARCHAR(20)  NOT NULL DEFAULT 'sent',
        `detail`       VARCHAR(500) NOT NULL DEFAULT '',
        `sent_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `idx_adventure` (`adventure_id`),
        KEY `idx_user`      (`user_id`)
    ) {$charset_collate};

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
		`player_bio` LONGTEXT NULL,
		`player_company` TEXT NULL,
		`player_website` TEXT NULL,
		`player_linkedin` TEXT NULL,
		`user_plan_id` BIGINT NULL,
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
   		`tabi_id` BIGINT NULL,
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

	CREATE TABLE {$wpdb->prefix}br_speakers (
		`speaker_id` BIGINT NOT NULL AUTO_INCREMENT,
		`player_id` INT NULL,
		`adventure_id` INT NULL,
		`speaker_first_name` LONGTEXT NOT NULL,
		`speaker_last_name` TEXT NULL,
		`speaker_picture` TEXT NULL,
		`speaker_bio` LONGTEXT NULL,
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
		`speaker_ids` TEXT NULL,
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

	CREATE TABLE {$wpdb->prefix}br_requests (
		`request_id` BIGINT NOT NULL AUTO_INCREMENT,
		`adventure_id` BIGINT NOT NULL,
		`player_id` BIGINT NOT NULL,
		`request_subject` VARCHAR(255) NOT NULL,
		`request_content` LONGTEXT NOT NULL,
		`request_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
		`request_admin_note` LONGTEXT NULL,
		`request_resolved_by` BIGINT NULL,
		`request_resolved_date` DATETIME NULL,
		`request_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`request_id`) )$charset_collate;

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
        "Email Notifications",
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
        "Quest QR",
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
		"Wall",
		"Manage Requests",
		"My Requests"
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

	// Seed standard plans
	$standard_plans = array(
		array('key'=>'god',        'label'=>'God Mode',      'type'=>'system'),
		array('key'=>'basic',      'label'=>'Basic',         'type'=>'standard'),
		array('key'=>'pro',        'label'=>'Pro',           'type'=>'standard'),
		array('key'=>'enterprise', 'label'=>'Enterprise',    'type'=>'standard'),
	);
	$has_plans = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}br_plans'");
	if($has_plans){
		foreach($standard_plans as $sp){
			$exists = $wpdb->get_var($wpdb->prepare(
				"SELECT plan_id FROM {$wpdb->prefix}br_plans WHERE plan_key = %s", $sp['key']
			));
			if(!$exists){
				$wpdb->insert("{$wpdb->prefix}br_plans", array(
					'plan_key'=>$sp['key'], 'plan_label'=>$sp['label'], 'plan_type'=>$sp['type']
				), array('%s','%s','%s'));
			}
		}
		// Assign God Mode to player_id=1
		$god_plan_id = $wpdb->get_var("SELECT plan_id FROM {$wpdb->prefix}br_plans WHERE plan_key='god'");
		if($god_plan_id){
			$wpdb->update("{$wpdb->prefix}br_players",
				array('user_plan_id'=>$god_plan_id),
				array('player_id'=>1),
				array('%d'), array('%d')
			);
		}
	}

	// Seed role default plan mappings in br_config
	$role_defaults = array(
		'role_default_plan_administrator' => array('label'=>'Default plan for Administrators',    'value'=>'enterprise'),
		'role_default_plan_br_game_master'=> array('label'=>'Default plan for Game Masters',      'value'=>'pro'),
		'role_default_plan_br_npc'        => array('label'=>'Default plan for NPCs',              'value'=>'pro'),
		'role_default_plan_br_player'     => array('label'=>'Default plan for Players',           'value'=>'basic'),
		'role_default_plan_default'       => array('label'=>'Default plan (fallback)',             'value'=>'basic'),
	);
	foreach($role_defaults as $rd_name=>$rd){
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT config_id FROM {$wpdb->prefix}br_config WHERE config_name = %s", $rd_name
		));
		if(!$exists){
			$wpdb->insert("{$wpdb->prefix}br_config", array(
				'config_name'=>$rd_name, 'config_label'=>$rd['label'],
				'config_type'=>'text', 'config_value'=>$rd['value']
			), array('%s','%s','%s','%s'));
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
		$config = BR_Config::instance()->getSysConfig();
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
function embed_responsive_videos( $html ) {
    return '<div class="responsive-video-container">' . $html . '</div>';
}
 
add_filter( 'embed_oembed_html', 'embed_responsive_videos', 10, 3 );
add_filter( 'video_embed_html', 'embed_responsive_videos' ); // Jetpack

function add_upload_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
    return $mimes;
} 



function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo-unilever.png);
            padding-bottom: 30px;
			width:230px;
			background-size:contain;
        }
		p#nav{
			display:none;
		}
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );


function all_admin_init_functions() {
	if (!current_user_can( 'update_core' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		wp_redirect( get_bloginfo('url')); 
		exit;
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
    $player_data = BR_Player::instance()->getPlayerData($current_user->ID, 'ARRAY_A');
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
	wp_enqueue_script( 'br-scorm-api', get_template_directory_uri().'/js/scorm-api.js', array('jquery','ajaxFunctions'), '1.0', true);
}

require_once ("$dirName/classes/Notification.php");
require_once ("$dirName/classes/BR-Utils.php");
require_once ("$dirName/classes/BR-Config.php");
require_once ("$dirName/classes/BR-Activity.php");
require_once ("$dirName/classes/BR-Player.php");
require_once ("$dirName/classes/BR-Adventure.php");
require_once ("$dirName/classes/BR-Quest.php");
require_once ("$dirName/classes/BR-Step.php");
require_once ("$dirName/classes/BR-Objective.php");
require_once ("$dirName/classes/BR-Achievement.php");
require_once ("$dirName/classes/BR-Item.php");
require_once ("$dirName/classes/BR-Guild.php");
require_once ("$dirName/classes/BR-Challenge.php");
require_once ("$dirName/classes/BR-Survey.php");
require_once ("$dirName/classes/BR-Encounter.php");
require_once ("$dirName/classes/BR-Tabi.php");
require_once ("$dirName/classes/BR-Session.php");
require_once ("$dirName/classes/BR-Organization.php");
require_once ("$dirName/classes/BR-Blocker.php");
require_once ("$dirName/classes/BR-Transaction.php");
require_once ("$dirName/classes/BR-Announcement.php");
require_once ("$dirName/classes/BR-Request.php");
require_once ("$dirName/classes/BR-Content.php");
require_once ("$dirName/classes/BR-Progression.php");
require_once ("$dirName/classes/BR-Branch.php");
require_once ("$dirName/classes/BR-Trash.php");
require_once ("$dirName/classes/BR-Scorm.php");
require_once ("$dirName/classes/BR-mailer.php");
require_once ("$dirName/classes/BR-Stats.php");
require_once ("$dirName/classes/BR-PlayerMeta.php");
require_once ("$dirName/functions/br-email-admin.php");

// ── Stats Dashboard ─────────────────────────────────────────────────────────
function br_enqueue_analytics() {
    // Cloudflare passes the visitor's country in this header
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
     
    // Skip GA entirely for China
    if ( $country === 'CN' ) return;
    
    $google_property = BR_Config::instance()->getSysConfig('google_property_id');
    $gads_id = $google_property['value'] ? $google_property['value'] : 'G-F1QPQC2JZL';
    // Everyone else gets GA as normal
    wp_enqueue_script(
        'google-analytics',
        'https://www.googletagmanager.com/gtag/js?id='.$gads_id,
        [], null, false
    );
    wp_add_inline_script('google-analytics', "
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '".$gads_id."');
    ");
}
add_action('wp_enqueue_scripts', 'br_enqueue_analytics');

// ── Color System ─────────────────────────────────────────────────────────────

function br_color_map(): array {
	return [
		// Legacy Material Design names → hex
		'red'          => '#f44336', 'pink'         => '#e91e63', 'purple'       => '#9c27b0',
		'deep-purple'  => '#673ab7', 'indigo'       => '#3f51b5', 'blue'         => '#2196f3',
		'light-blue'   => '#03a9f4', 'cyan'         => '#00bcd4', 'teal'         => '#009688',
		'green'        => '#4caf50', 'light-green'  => '#8bc34a', 'lime'         => '#cddc39',
		'yellow'       => '#ffeb3b', 'amber'        => '#ffc107', 'orange'       => '#ff9800',
		'deep-orange'  => '#ff5722', 'brown'        => '#795548', 'grey'         => '#9e9e9e',
		'blue-grey'    => '#607d8b',
		// Dark theme palette
		'br-primary'   => '#1cc2eb', 'br-accent'    => '#f7cb15', 'br-purple'    => '#9f40e2',
		'br-green'     => '#24da98', 'br-red'       => '#f44336', 'br-amber'     => '#ffc107',
		'br-teal'      => '#00bcd4', 'br-orange'    => '#ff9800', 'br-pink'      => '#e040fb',
		'br-indigo'    => '#7c4dff', 'br-lime'      => '#c6ff00', 'br-coral'     => '#ff6e6e',
		'br-sky'       => '#42a5f5', 'br-mint'      => '#69f0ae', 'br-rose'      => '#ff80ab',
		'br-gold'      => '#ffd54f', 'br-slate'     => '#78909c', 'br-charcoal'  => '#455a64',
	];
}

function br_color_to_hex( string $color ): string {
	if ( empty( $color ) ) return '#9e9e9e';
	if ( $color[0] === '#' ) return $color;
	if ( strpos( $color, 'rgba' ) === 0 || strpos( $color, 'rgb' ) === 0 ) return $color;
	$map = br_color_map();
	return $map[ $color ] ?? '#9e9e9e';
}

function br_color_style( string $color, string $property = 'background-color', float $opacity = 1.0 ): string {
	$hex = br_color_to_hex( $color );
	if ( $opacity < 1.0 && $hex[0] === '#' ) {
		$r = hexdec( substr( $hex, 1, 2 ) );
		$g = hexdec( substr( $hex, 3, 2 ) );
		$b = hexdec( substr( $hex, 5, 2 ) );
		return "{$property}:rgba({$r},{$g},{$b},{$opacity})";
	}
	return "{$property}:{$hex}";
}

/**
 * Backward-compatible color class OR inline style.
 * Usage:  <div class="foo" <?= br_color_attr($quest->quest_color, 'bg') ?>>
 * Output for legacy "red":       class fragment not used — outputs style="background-color:#f44336"
 * Output for hex "#f44336":      style="background-color:#f44336"
 * Output for rgba:               style="background-color:rgba(36,218,152,0.5)"
 *
 * $type: 'bg' = background-color, 'border' = border-color, 'text' = color
 */
function br_color_attr( string $color, string $type = 'bg', bool $declaration_only = false ): string {
	$props = [ 'bg' => 'background-color', 'border' => 'border-color', 'text' => 'color' ];
	$prop  = $props[ $type ] ?? 'background-color';
	$val   = br_color_to_hex( $color );
	if ( $declaration_only ) return "{$prop}:{$val};";
	return 'style="' . esc_attr( "{$prop}:{$val}" ) . '"';
}

function br_stats_enqueue_assets() {
	wp_enqueue_style( 'br-table', get_template_directory_uri() . '/css/br-table.css', [], '1.0' );
	wp_enqueue_style( 'br-notify', get_template_directory_uri() . '/css/br-notify.css', [], '1.0' );
	if ( is_page('login') ) {
		wp_enqueue_style( 'br-auth', get_template_directory_uri() . '/css/br-auth.css', ['br-table'], '1.0' );
	}
	if ( is_page('stats') ) {
		wp_enqueue_style( 'br-stats', get_template_directory_uri() . '/css/br-stats.css', ['br-table'], '1.0' );
		wp_enqueue_script( 'br-stats', get_template_directory_uri() . '/js/br-stats.js', ['jquery'], '1.0', true );
	}
	if ( is_page('milestone-funnel') ) {
		wp_enqueue_style( 'br-stats', get_template_directory_uri() . '/css/br-stats.css', ['br-table'], '1.0' );
		wp_enqueue_script( 'br-milestone-funnel', get_template_directory_uri() . '/js/br-milestone-funnel.js', ['jquery'], '1.0', true );
	}
}
add_action( 'wp_enqueue_scripts', 'br_stats_enqueue_assets' );

function br_stats_is_manager( $adventure_id ) {
	if ( current_user_can( 'manage_options' ) ) return true;
	$pa = BR_Player::instance()->getPlayerAdventureData( $adventure_id, get_current_user_id() );
	return $pa && isset( $pa->player_adventure_role )
		&& in_array( $pa->player_adventure_role, [ 'gm', 'npc' ] );
}

function br_stats_xp_history() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$uid = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : get_current_user_id();
	$aid = (int) $_POST['adventure_id'];
	if ( $uid !== get_current_user_id() && ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$stats = new BR_Stats();
	wp_send_json_success( $stats->get_player_xp_history( $uid, $aid, isset( $_POST['days'] ) ? (int) $_POST['days'] : 30 ) );
}
add_action( 'wp_ajax_br_stats_xp_history', 'br_stats_xp_history' );

function br_stats_quest_funnel() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$filter_type  = sanitize_text_field( $_POST['filter_type'] ?? 'all' );
	$filter_value = isset( $_POST['filter_value'] ) ? (int) $_POST['filter_value'] : 0;
	$stats = new BR_Stats();
	wp_send_json_success( $stats->get_quest_funnel( $aid, $filter_type, $filter_value ) );
}
add_action( 'wp_ajax_br_stats_quest_funnel', 'br_stats_quest_funnel' );

function br_stats_xp_distribution() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$stats = new BR_Stats();
	wp_send_json_success( $stats->get_xp_distribution( $aid ) );
}
add_action( 'wp_ajax_br_stats_xp_distribution', 'br_stats_xp_distribution' );

function br_stats_activity_heatmap() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid  = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$from = sanitize_text_field( $_POST['from'] ?? '' );
	$to   = sanitize_text_field( $_POST['to']   ?? '' );
	$days = (int) ( $_POST['days'] ?? 30 );
	$stats = new BR_Stats();
	wp_send_json_success( $stats->get_activity_heatmap( $aid, $days, $from, $to ) );
}
add_action( 'wp_ajax_br_stats_activity_heatmap', 'br_stats_activity_heatmap' );

function br_stats_segment_breakdown() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$dimension = sanitize_text_field( $_POST['dimension'] ?? '' );
	if ( ! array_key_exists( $dimension, BR_Stats::SEGMENT_DIMENSIONS ) ) wp_send_json_error( 'Invalid dimension' );
	$stats = new BR_Stats();
	wp_send_json_success( $stats->get_engagement_by_segment( $aid, $dimension ) );
}
add_action( 'wp_ajax_br_stats_segment_breakdown', 'br_stats_segment_breakdown' );

function br_stats_player_panel() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$uid = (int) $_POST['user_id'];
	$aid = (int) $_POST['adventure_id'];
	if ( $uid !== get_current_user_id() && ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );
	$stats     = new BR_Stats();
	$adventure = BR_Adventure::instance()->getAdventure( $aid );
	wp_send_json_success( [
		'summary'         => array_merge(
			$stats->get_player_summary( $uid, $aid ),
			[ 'avatar_url' => get_avatar_url( $uid, [ 'size' => 80 ] ) ]
		),
		'quests'          => $stats->get_player_quest_progress( $uid, $aid ),
		'achievements'    => $stats->get_player_achievements( $uid, $aid ),
		'guild'           => $stats->get_player_guild( $uid, $aid ),
		'scorm'           => $stats->get_player_scorm_completions( $uid ),
		'tabis'           => $stats->get_player_tabi_progress( $uid, $aid ),
		'type_completion' => $stats->get_player_type_completion( $uid, $aid ),
		'last_activity'   => $stats->get_player_last_activity( $uid, $aid ),
		'engagement'      => $stats->get_player_engagement( $uid, $aid ),
		'adventure_title' => $adventure ? $adventure->adventure_title : '',
		'labels'          => [
			'xp'   => $adventure ? ( $adventure->adventure_xp_label ?: 'XP' ) : 'XP',
			'bloo' => $adventure ? ( $adventure->adventure_bloo_label ?: 'BLOO' ) : 'BLOO',
			'ep'   => $adventure ? ( $adventure->adventure_ep_label ?: 'EP' ) : 'EP',
		],
	] );
}
add_action( 'wp_ajax_br_stats_player_panel', 'br_stats_player_panel' );

function br_meta_manager_enqueue_assets() {
	if ( is_page( 'player-meta' ) ) {
		wp_enqueue_style( 'br-table', get_template_directory_uri() . '/css/br-table.css', [], '1.0' );
		wp_enqueue_script( 'br-meta-manager', get_template_directory_uri() . '/js/br-meta-manager.js', [ 'jquery' ], '1.0', true );
	}
}
add_action( 'wp_enqueue_scripts', 'br_meta_manager_enqueue_assets' );

function br_meta_save_player() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );

	$player_id = (int) $_POST['player_id'];
	$fields    = [];
	foreach ( array_keys( BR_PlayerMeta::FIELDS ) as $col ) {
		if ( isset( $_POST['fields'][ $col ] ) ) $fields[ $col ] = wp_unslash( $_POST['fields'][ $col ] );
	}

	$meta = new BR_PlayerMeta();
	$ok   = $meta->save_player_meta( $player_id, $fields );
	if ( $ok ) {
		wp_send_json_success( $fields );
	} else {
		wp_send_json_error( 'Save failed' );
	}
}
add_action( 'wp_ajax_br_meta_save_player', 'br_meta_save_player' );

function br_meta_preview_csv() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );

	$csv  = wp_unslash( $_POST['csv'] ?? '' );
	$meta = new BR_PlayerMeta();
	wp_send_json_success( $meta->import_csv( $aid, $csv, true ) );
}
add_action( 'wp_ajax_br_meta_preview_csv', 'br_meta_preview_csv' );

function br_meta_commit_csv() {
	check_ajax_referer( 'br_stats_nonce', 'nonce' );
	$aid = (int) $_POST['adventure_id'];
	if ( ! br_stats_is_manager( $aid ) ) wp_send_json_error( 'Unauthorized' );

	$csv  = wp_unslash( $_POST['csv'] ?? '' );
	$meta = new BR_PlayerMeta();
	wp_send_json_success( $meta->import_csv( $aid, $csv, false ) );
}
add_action( 'wp_ajax_br_meta_commit_csv', 'br_meta_commit_csv' );

add_action( 'after_setup_theme', 'theme_name_setup' );
add_filter( 'upload_mimes', 'add_upload_mime_types' );
add_action('after_switch_theme', 'theme_core_setup');

function br_migrate_tabi_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_tabis LIKE 'tabi_top'");
	if (empty($col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_tabis ADD COLUMN `tabi_top` INT NULL DEFAULT 350");
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_tabis ADD COLUMN `tabi_left` INT NULL DEFAULT 350");
	}

	$col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_tabis LIKE 'tabi_as_category'");
	if (empty($col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_tabis ADD COLUMN `tabi_as_category` TINYINT NULL DEFAULT 0");
	}

	$table = $wpdb->prefix . 'br_tabi_prerequisites';
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
		$wpdb->query("CREATE TABLE $table (
			`prereq_id` BIGINT NOT NULL AUTO_INCREMENT,
			`tabi_id` BIGINT NOT NULL,
			`requires_tabi_id` BIGINT NOT NULL,
			PRIMARY KEY (`prereq_id`)
		) $charset_collate");
	}

	$col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_quests LIKE 'mech_optional'");
	if (empty($col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_quests ADD COLUMN `mech_optional` TINYINT NULL DEFAULT 0");
	}

	$col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_quests LIKE 'quest_qr_token'");
	if (empty($col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_quests ADD COLUMN `quest_qr_token` VARCHAR(40) NULL DEFAULT NULL");
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_quests ADD UNIQUE INDEX `quest_qr_token` (`quest_qr_token`)");
	}

	$col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_quests LIKE 'mech_validate'");
	if (empty($col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_quests ADD COLUMN `mech_validate` TINYINT NULL DEFAULT 0");
	}

	$table = $wpdb->prefix . 'br_journey_assets';
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
		$wpdb->query("CREATE TABLE $table (
			`asset_id` BIGINT NOT NULL AUTO_INCREMENT,
			`adventure_id` BIGINT NOT NULL,
			`asset_image` TEXT NULL,
			`asset_top` INT NULL DEFAULT 350,
			`asset_left` INT NULL DEFAULT 350,
			`asset_width` INT NULL DEFAULT 200,
			`asset_z` INT NULL DEFAULT 5,
			`asset_rotation` INT NULL DEFAULT 0,
			`asset_type` VARCHAR(30) NOT NULL DEFAULT 'graphic',
			`asset_link` TEXT NULL,
			`asset_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
			PRIMARY KEY (`asset_id`)
		) $charset_collate");
	} else {
		$cols = $wpdb->get_col("SHOW COLUMNS FROM $table");
		if(!in_array('asset_type', $cols)){
			$wpdb->query("ALTER TABLE $table ADD COLUMN `asset_type` VARCHAR(30) NOT NULL DEFAULT 'graphic'");
		}
		if(!in_array('asset_link', $cols)){
			$wpdb->query("ALTER TABLE $table ADD COLUMN `asset_link` TEXT NULL DEFAULT NULL");
		}
		if(!in_array('tabi_id', $cols)){
			$wpdb->query("ALTER TABLE $table ADD COLUMN `tabi_id` BIGINT NULL DEFAULT 0");
		}
	}
}
add_action('init', 'br_migrate_tabi_tables');

function br_migrate_milestone_schema() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$prefix = $wpdb->prefix;

	$quest_cols = $wpdb->get_col("SHOW COLUMNS FROM {$prefix}br_quests");
	if (!in_array('milestone_grade_type', $quest_cols)) {
		$wpdb->query("ALTER TABLE {$prefix}br_quests ADD COLUMN `milestone_grade_type` VARCHAR(20) DEFAULT 'completion'");
	}
	if (!in_array('quest_branch_lock', $quest_cols)) {
		$wpdb->query("ALTER TABLE {$prefix}br_quests ADD COLUMN `quest_branch_lock` TINYINT NULL DEFAULT 0");
	}

	$ach_cols = $wpdb->get_col("SHOW COLUMNS FROM {$prefix}br_achievements");
	if (!in_array('branch_group_id', $ach_cols)) {
		$wpdb->query("ALTER TABLE {$prefix}br_achievements ADD COLUMN `branch_group_id` BIGINT DEFAULT NULL");
	}

	$step_cols = $wpdb->get_col("SHOW COLUMNS FROM {$prefix}br_steps");
	$step_additions = [
		'step_skin'               => "VARCHAR(50) DEFAULT NULL",
		'step_correct'            => "LONGTEXT DEFAULT NULL",
		'step_mistake_message'    => "LONGTEXT DEFAULT NULL",
		'step_xp_reward'          => "INT DEFAULT 0",
		'step_bloo_reward'        => "INT DEFAULT 0",
		'step_ep_reward'          => "INT DEFAULT 0",
		'step_item_reward'        => "BIGINT DEFAULT NULL",
		'step_achievement_reward' => "BIGINT DEFAULT NULL",
		'step_required'           => "TINYINT DEFAULT 1",
		'step_branch_group_id'    => "BIGINT DEFAULT NULL",
	];
	foreach ($step_additions as $col => $def) {
		if (!in_array($col, $step_cols)) {
			$wpdb->query("ALTER TABLE {$prefix}br_steps ADD COLUMN `$col` $def");
		}
	}

	$ps_cols = $wpdb->get_col("SHOW COLUMNS FROM {$prefix}br_player_steps");
	$ps_additions = [
		'ps_step_type' => "VARCHAR(50) DEFAULT NULL",
		'ps_response'  => "LONGTEXT DEFAULT NULL",
		'ps_correct'   => "TINYINT DEFAULT NULL",
		'ps_attempt'   => "INT DEFAULT 1",
		'ps_score'     => "INT DEFAULT NULL",
	];
	foreach ($ps_additions as $col => $def) {
		if (!in_array($col, $ps_cols)) {
			$wpdb->query("ALTER TABLE {$prefix}br_player_steps ADD COLUMN `$col` $def");
		}
	}

	$table = $prefix . 'br_branch_groups';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
		$wpdb->query("CREATE TABLE $table (
			`group_id` BIGINT NOT NULL AUTO_INCREMENT,
			`adventure_id` BIGINT NOT NULL,
			`group_name` VARCHAR(255) NOT NULL,
			`group_description` TEXT DEFAULT NULL,
			`group_status` VARCHAR(20) NOT NULL DEFAULT 'publish',
			`group_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`group_id`),
			KEY `adventure_id` (`adventure_id`)
		) $charset_collate");
	}

	$table = $prefix . 'br_player_branches';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
		$wpdb->query("CREATE TABLE $table (
			`player_id` BIGINT NOT NULL,
			`adventure_id` BIGINT NOT NULL,
			`group_id` BIGINT NOT NULL,
			`achievement_id` BIGINT NOT NULL,
			`chosen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`player_id`, `adventure_id`, `group_id`)
		) $charset_collate");
	}

	$table = $prefix . 'br_branch_rules';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
		$wpdb->query("CREATE TABLE $table (
			`rule_id` BIGINT NOT NULL AUTO_INCREMENT,
			`achievement_id` BIGINT NOT NULL,
			`adventure_id` BIGINT NOT NULL,
			`rule_action` VARCHAR(20) NOT NULL,
			`rule_target_type` VARCHAR(20) NOT NULL,
			`rule_target_id` BIGINT NOT NULL,
			`rule_order` INT DEFAULT 0,
			PRIMARY KEY (`rule_id`),
			KEY `achievement_id` (`achievement_id`),
			KEY `adventure_id` (`adventure_id`)
		) $charset_collate");
	}
	// AI API key on adventures
	$ai_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_adventures LIKE 'adventure_ai_api_key'");
	if (empty($ai_col)) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}br_adventures ADD COLUMN adventure_ai_api_key VARCHAR(255) DEFAULT NULL");
	}
}
add_action('init', 'br_migrate_milestone_schema');

function br_save_ai_api_key() {
	global $wpdb;
	$n = new Notification();

	if (!wp_verify_nonce($_POST['nonce'] ?? '', 'br_update_adventure_nonce')) {
		echo json_encode(['success' => false, 'message' => $n->pop(__('Security check failed.', 'bluerabbit'), 'red', 'cancel'), 'just_notify' => true]);
		die();
	}

	$adventure_id = intval($_POST['adventure_id'] ?? 0);
	$api_key = sanitize_text_field($_POST['api_key'] ?? '');

	$wpdb->update(
		"{$wpdb->prefix}br_adventures",
		['adventure_ai_api_key' => $api_key ?: null],
		['adventure_id' => $adventure_id],
		['%s'],
		['%d']
	);

	echo json_encode(['success' => true, 'message' => $n->pop(__('API Key saved!', 'bluerabbit'), 'green', 'check'), 'just_notify' => true]);
	die();
}

function br_ai_validate_text() {
	global $wpdb;

	$step_id = intval($_POST['step_id'] ?? 0);
	$adventure_id = intval($_POST['adventure_id'] ?? 0);
	$content = wp_kses_post($_POST['content'] ?? '');

	if (!$step_id || !$adventure_id) {
		echo json_encode(['valid' => false, 'message' => __('Invalid parameters.', 'bluerabbit')]);
		die();
	}

	$adventure = $wpdb->get_row($wpdb->prepare(
		"SELECT adventure_ai_api_key FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d", $adventure_id
	));

	$api_key = $adventure->adventure_ai_api_key ?? '';
	if (!$api_key) {
		echo json_encode(['valid' => true, 'message' => __('No AI key configured — skipping validation.', 'bluerabbit')]);
		die();
	}

	$step = $wpdb->get_row($wpdb->prepare(
		"SELECT step_content, step_settings FROM {$wpdb->prefix}br_steps WHERE step_id = %d", $step_id
	));
	$step_prompt = $step->step_content ? wp_strip_all_tags($step->step_content) : '';

	$plain_text = wp_strip_all_tags($content);
	$word_count = str_word_count($plain_text);

	$system_prompt = "You are a content validator for an educational platform. A student was asked to write a response to a prompt. You must determine if the response is a genuine attempt to address the prompt. Reject responses that are: random words, copy-pasted gibberish, completely off-topic, or obvious filler text. Be lenient — imperfect grammar or short answers are fine as long as they genuinely try to address the topic. Respond ONLY with a JSON object: {\"valid\": true} or {\"valid\": false, \"reason\": \"brief explanation\"}";

	$user_message = "PROMPT GIVEN TO STUDENT:\n" . ($step_prompt ?: '(open response, no specific prompt)') . "\n\nSTUDENT RESPONSE ({$word_count} words):\n" . $plain_text;

	$response = wp_remote_post('https://api.anthropic.com/v1/messages', [
		'timeout' => 15,
		'headers' => [
			'Content-Type' => 'application/json',
			'x-api-key' => $api_key,
			'anthropic-version' => '2023-06-01',
		],
		'body' => json_encode([
			'model' => 'claude-haiku-4-5-20251001',
			'max_tokens' => 150,
			'system' => $system_prompt,
			'messages' => [['role' => 'user', 'content' => $user_message]],
		]),
	]);

	if (is_wp_error($response)) {
		echo json_encode(['valid' => true, 'message' => __('AI service unavailable — accepted.', 'bluerabbit')]);
		die();
	}

	$body = json_decode(wp_remote_retrieve_body($response), true);
	$ai_text = $body['content'][0]['text'] ?? '';

	$json_match = [];
	if (preg_match('/\{[^}]+\}/', $ai_text, $json_match)) {
		$result = json_decode($json_match[0], true);
		if (isset($result['valid'])) {
			$message = $result['valid']
				? __('Content validated!', 'bluerabbit')
				: ($result['reason'] ?? __("Your response doesn't seem to address the question. Please revise and try again.", 'bluerabbit'));
			echo json_encode(['valid' => (bool) $result['valid'], 'message' => $message]);
			die();
		}
	}

	echo json_encode(['valid' => true, 'message' => __('Content accepted.', 'bluerabbit')]);
	die();
}

function br_run_milestone_migration() {
	if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }

	global $wpdb;
	$prefix = $wpdb->prefix;
	$log = [];

	$count_before = [
		'objectives'        => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_objectives"),
		'player_objectives' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_player_objectives"),
		'survey_questions'  => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_survey_questions"),
		'survey_answers'    => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_survey_answers"),
		'steps_before'      => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_steps"),
		'player_steps_before' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_player_steps"),
	];
	$log['counts_before'] = $count_before;

	// Ensure all source rows have ref_ids before migration
	$null_obj_refs = $wpdb->get_results("SELECT objective_id FROM {$prefix}br_objectives WHERE ref_id IS NULL");
	foreach ($null_obj_refs as $row) {
		$wpdb->update("{$prefix}br_objectives", ['ref_id' => substr(md5(uniqid(rand(), true)), 0, 8)], ['objective_id' => $row->objective_id]);
	}
	$null_sq_refs = $wpdb->get_results("SELECT survey_question_id FROM {$prefix}br_survey_questions WHERE ref_id IS NULL");
	foreach ($null_sq_refs as $row) {
		$wpdb->update("{$prefix}br_survey_questions", ['ref_id' => substr(md5(uniqid(rand(), true)), 0, 8)], ['survey_question_id' => $row->survey_question_id]);
	}

	// 4a — Migrate objectives → steps
	$objectives = $wpdb->get_results("SELECT * FROM {$prefix}br_objectives");
	$obj_migrated = 0;
	foreach ($objectives as $o) {
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_steps WHERE ref_id = %s AND quest_id = %d",
			$o->ref_id, $o->quest_id
		));
		if ($exists) continue;

		$is_keyword = ($o->objective_type === 'keyword-input' && !empty($o->objective_keyword));
		$step_type = $is_keyword ? 'validate' : 'collect';
		$step_skin = $is_keyword ? 'keyphrase' : 'open_text';

		$settings = [
			'prompt'          => $o->objective_content,
			'case_sensitive'  => false,
			'trim_whitespace' => true,
		];
		if (!empty($o->objective_keyword)) {
			$settings['keyword'] = $o->objective_keyword;
		}
		if (!empty($o->ep_cost)) {
			$settings['ep_cost'] = (int) $o->ep_cost;
		}

		$step_correct = $is_keyword ? json_encode([$o->objective_keyword]) : null;

		$wpdb->insert("{$prefix}br_steps", [
			'quest_id'             => $o->quest_id,
			'adventure_id'         => $o->adventure_id,
			'ref_id'               => $o->ref_id,
			'step_type'            => $step_type,
			'step_skin'            => $step_skin,
			'step_title'           => '',
			'step_content'         => $o->objective_content,
			'step_order'           => $o->objective_order,
			'step_status'          => $o->objective_status,
			'step_date'            => $o->objective_date,
			'step_modified'        => $o->objective_modified,
			'step_settings'        => json_encode($settings),
			'step_correct'         => $step_correct,
			'step_mistake_message' => !empty($o->objective_success_message) ? $o->objective_success_message : null,
			'step_required'        => 1,
			'step_parent'          => $o->objective_parent,
			'step_ep_reward'       => (int) $o->ep_cost,
		]);
		$obj_migrated++;
	}
	$log['objectives_migrated'] = $obj_migrated;

	// 4b — Migrate player_objectives → player_steps
	$player_objs = $wpdb->get_results("SELECT po.*, o.quest_id, o.ref_id, o.objective_type
		FROM {$prefix}br_player_objectives po
		JOIN {$prefix}br_objectives o ON o.objective_id = po.objective_id");
	$po_migrated = 0;
	foreach ($player_objs as $po) {
		$step_id = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_steps WHERE ref_id = %s AND quest_id = %d",
			$po->ref_id, $po->quest_id
		));
		if (!$step_id) continue;

		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_player_steps WHERE player_id = %d AND step_id = %d",
			$po->player_id, $step_id
		));
		if ($exists) continue;

		$ps_type = ($po->objective_type === 'keyword-input') ? 'keyphrase' : 'open_text';
		$wpdb->insert("{$prefix}br_player_steps", [
			'quest_id'     => $po->quest_id,
			'adventure_id' => $po->adventure_id,
			'player_id'    => $po->player_id,
			'step_id'      => $step_id,
			'ps_date'      => $po->timestamp,
			'ps_status'    => 'publish',
			'ps_step_type' => $ps_type,
			'ps_correct'   => null,
		]);
		$po_migrated++;
	}
	$log['player_objectives_migrated'] = $po_migrated;

	// 4c — Migrate survey_questions → steps
	$sq_rows = $wpdb->get_results("SELECT * FROM {$prefix}br_survey_questions");
	$sq_migrated = 0;
	foreach ($sq_rows as $sq) {
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_steps WHERE ref_id = %s",
			$sq->ref_id
		));
		if ($exists) continue;

		$adventure_id = $wpdb->get_var($wpdb->prepare(
			"SELECT adventure_id FROM {$prefix}br_quests WHERE quest_id = %d",
			$sq->survey_id
		));
		if (!$adventure_id) continue;

		$options_rows = $wpdb->get_results($wpdb->prepare(
			"SELECT survey_option_id, survey_option_text, survey_option_image
			FROM {$prefix}br_survey_options
			WHERE survey_question_id = %d AND survey_option_status = 'publish'",
			$sq->survey_question_id
		));
		$options_arr = [];
		foreach ($options_rows as $opt) {
			$options_arr[] = [
				'id'    => (string) $opt->survey_option_id,
				'text'  => $opt->survey_option_text,
				'image' => $opt->survey_option_image,
			];
		}

		switch ($sq->survey_question_type) {
			case 'rating':
				$step_skin = 'survey_rating';
				$settings = [
					'question' => $sq->survey_question_text,
					'min'      => 1,
					'max'      => (int) ($sq->survey_question_range ?: 5),
					'labels'   => ['min' => '', 'max' => ''],
				];
				break;
			case 'number':
				$step_skin = 'survey_rating';
				$settings = [
					'question' => $sq->survey_question_text,
					'min'      => 1,
					'max'      => (int) ($sq->survey_question_range ?: 10),
					'labels'   => ['min' => '', 'max' => ''],
					'display'  => $sq->survey_question_display,
				];
				break;
			case 'open':
				$step_skin = 'open_text';
				$settings = [
					'prompt'         => $sq->survey_question_text,
					'min_words'      => 0,
					'use_wp_editor'  => false,
					'llm_validate'   => false,
					'llm_prompt'     => null,
				];
				break;
			case 'multi-choice':
				$step_skin = 'survey_choice';
				$settings = [
					'question'       => $sq->survey_question_text,
					'question_image' => $sq->survey_question_image,
					'options'        => $options_arr,
					'allow_multiple' => true,
					'show_results'   => false,
				];
				break;
			default: // closed
				$step_skin = 'survey_choice';
				$settings = [
					'question'       => $sq->survey_question_text,
					'question_image' => $sq->survey_question_image,
					'options'        => $options_arr,
					'allow_multiple' => false,
					'show_results'   => false,
					'display'        => $sq->survey_question_display,
				];
				break;
		}

		$step_type = ($step_skin === 'open_text') ? 'collect' : 'collect';

		$wpdb->insert("{$prefix}br_steps", [
			'quest_id'     => $sq->survey_id,
			'adventure_id' => $adventure_id,
			'ref_id'       => $sq->ref_id,
			'step_type'    => $step_type,
			'step_skin'    => $step_skin,
			'step_content' => $sq->survey_question_text,
			'step_order'   => $sq->survey_question_order,
			'step_status'  => $sq->survey_question_status,
			'step_settings'=> json_encode($settings),
			'step_parent'  => $sq->survey_question_parent,
			'step_required'=> 1,
		]);
		$sq_migrated++;
	}
	$log['survey_questions_migrated'] = $sq_migrated;

	// 4d — Migrate survey_answers → player_steps
	$sa_rows = $wpdb->get_results("SELECT sa.*, sq.ref_id, sq.survey_question_type
		FROM {$prefix}br_survey_answers sa
		JOIN {$prefix}br_survey_questions sq ON sq.survey_question_id = sa.survey_question_id");
	$sa_migrated = 0;
	foreach ($sa_rows as $sa) {
		$step_id = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_steps WHERE ref_id = %s",
			$sa->ref_id
		));
		if (!$step_id) continue;

		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT step_id FROM {$prefix}br_player_steps WHERE player_id = %d AND step_id = %d",
			$sa->player_id, $step_id
		));
		if ($exists) continue;

		$skin_map = [
			'rating' => 'survey_rating', 'number' => 'survey_rating',
			'open' => 'open_text', 'multi-choice' => 'survey_choice',
		];
		$ps_type = $skin_map[$sa->survey_question_type] ?? 'survey_choice';

		$response = json_encode([
			'option_id' => $sa->survey_option_id ?? null,
			'value'     => $sa->survey_answer_value ?? null,
		]);

		$wpdb->insert("{$prefix}br_player_steps", [
			'quest_id'     => $sa->survey_id,
			'adventure_id' => $sa->adventure_id,
			'player_id'    => $sa->player_id,
			'step_id'      => $step_id,
			'ps_date'      => $sa->survey_answer_date,
			'ps_status'    => 'publish',
			'ps_step_type' => $ps_type,
			'ps_correct'   => null,
			'ps_response'  => $response,
		]);
		$sa_migrated++;
	}
	$log['survey_answers_migrated'] = $sa_migrated;

	// Update quest_type values
	$missions_updated = $wpdb->query("UPDATE {$prefix}br_quests SET quest_type = 'quest' WHERE quest_type = 'mission'");
	$surveys_updated  = $wpdb->query("UPDATE {$prefix}br_quests SET quest_type = 'quest' WHERE quest_type = 'survey'");
	$log['quest_type_updates'] = [
		'missions_to_quest' => $missions_updated,
		'surveys_to_quest'  => $surveys_updated,
	];

	// Verification counts
	$log['counts_after'] = [
		'steps_total'        => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_steps"),
		'player_steps_total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_player_steps"),
		'quest_types'        => $wpdb->get_results("SELECT quest_type, COUNT(*) as cnt FROM {$prefix}br_quests GROUP BY quest_type"),
		'step_skins'         => $wpdb->get_results("SELECT step_skin, COUNT(*) as cnt FROM {$prefix}br_steps WHERE step_skin IS NOT NULL GROUP BY step_skin"),
		'orphaned_steps'     => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}br_steps s LEFT JOIN {$prefix}br_quests q ON q.quest_id = s.quest_id WHERE q.quest_id IS NULL"),
	];

	wp_send_json_success($log);
}

add_action('switch_theme', 'delete_roles');
add_filter('show_admin_bar', '__return_false');
add_action('template_redirect', 'ajaxFunctions');

add_action('wp_login', [BR_Player::instance(), 'absolute_level_calc']);


///////// AJAX REGISTRATIONS (class-based callbacks) //////////////

add_action("wp_ajax_br_notify", [new Notification(), 'notify']);
add_action("wp_ajax_switchRank", [BR_Achievement::instance(), 'switchRank']);
add_action("wp_ajax_closeIntro", [BR_Player::instance(), 'closeIntro']);
add_action("wp_ajax_resetIntro", [BR_Player::instance(), 'resetIntro']);
add_action("wp_ajax_br_dismiss_tutorial", [BR_Player::instance(), 'br_dismiss_tutorial']);
add_action("wp_ajax_resetPrevLevel", [BR_Player::instance(), 'resetPrevLevel']);
add_action("wp_ajax_resetGuilds", [BR_Player::instance(), 'resetGuilds']);
add_action("wp_ajax_resetPlayerAdventure", [BR_Player::instance(), 'resetPlayerAdventure']);
add_action("wp_ajax_updatePlayer", [BR_Player::instance(), 'updatePlayer']);
add_action("wp_ajax_setGrade", [BR_Quest::instance(), 'setGrade']);
add_action("wp_ajax_validatePlayerPost", [BR_Quest::instance(), 'validatePlayerPost']);
add_action("wp_ajax_updateProfile", [BR_Player::instance(), 'updateProfile']);
add_action("wp_ajax_nopriv_bluerabbit_add_new_player", [BR_Player::instance(), 'bluerabbit_add_new_player']);
add_action("wp_ajax_bluerabbit_add_new_player", [BR_Player::instance(), 'bluerabbit_add_new_player']);
add_action("wp_ajax_checkUserDataExists", [BR_Player::instance(), 'checkUserDataExists']);
add_action("wp_ajax_enrollUser", [BR_Player::instance(), 'enrollUser']);
add_action("wp_ajax_uploadBulkUsers", [BR_Player::instance(), 'uploadBulkUsers']);
add_action("wp_ajax_bulkEnrollUsers", [BR_Player::instance(), 'bulkEnrollUsers']);
add_action("wp_ajax_uploadBulkSpeakers", [BR_Session::instance(), 'uploadBulkSpeakers']);
add_action("wp_ajax_uploadBulkSessions", [BR_Session::instance(), 'uploadBulkSessions']);
add_action("wp_ajax_uploadBulkQuests", [BR_Quest::instance(), 'uploadBulkQuests']);
add_action("wp_ajax_uploadBulkQuestions", [BR_Challenge::instance(), 'uploadBulkQuestions']);
add_action("wp_ajax_uploadBulkAchievements", [BR_Achievement::instance(), 'uploadBulkAchievements']);
add_action("wp_ajax_uploadBulkItems", [BR_Item::instance(), 'uploadBulkItems']);
add_action("wp_ajax_newHexad", [BR_Player::instance(), 'newHexad']);
add_action("wp_ajax_reorder", [BR_Quest::instance(), 'reorder']);
add_action("wp_ajax_reorderItems", [BR_Item::instance(), 'reorderItems']);
add_action("wp_ajax_reorderAchievements", [BR_Achievement::instance(), 'reorderAchievements']);
add_action("wp_ajax_reorderQuestions", [BR_Challenge::instance(), 'reorderQuestions']);
add_action("wp_ajax_loadQuestCard", [BR_Content::instance(), 'loadQuestCard']);
add_action("wp_ajax_loadItemCard", [BR_Item::instance(), 'loadItemCard']);
add_action("wp_ajax_loadBackpackItem", [BR_Item::instance(), 'loadBackpackItem']);
add_action("wp_ajax_loadAchievementCard", [BR_Achievement::instance(), 'loadAchievementCard']);
add_action("wp_ajax_displayAchievementCard", [BR_Achievement::instance(), 'displayAchievementCard']);
add_action("wp_ajax_loadGuildCard", [BR_Guild::instance(), 'loadGuildCard']);
add_action("wp_ajax_loadLore", [BR_Content::instance(), 'loadLore']);
add_action("wp_ajax_searchLore", [BR_Content::instance(), 'searchLore']);
add_action("wp_ajax_updateAdventure", [BR_Adventure::instance(), 'updateAdventure']);
add_action("wp_ajax_addTabi", [BR_Tabi::instance(), 'addTabi']);
add_action("wp_ajax_insertTabiRow", [BR_Tabi::instance(), 'insertTabiRow']);
add_action("wp_ajax_saveTabiPiecePosition", [BR_Tabi::instance(), 'saveTabiPiecePosition']);
add_action("wp_ajax_updateMilestonePosition", [BR_Tabi::instance(), 'updateMilestonePosition']);
add_action("wp_ajax_updateQuest", [BR_Quest::instance(), 'updateQuest']);
add_action("wp_ajax_updateEncounter", [BR_Encounter::instance(), 'updateEncounter']);
add_action("wp_ajax_randomEncounter", [BR_Encounter::instance(), 'randomEncounter']);
add_action("wp_ajax_answerEncounter", [BR_Encounter::instance(), 'answerEncounter']);
add_action("wp_ajax_updateAchievement", [BR_Achievement::instance(), 'updateAchievement']);
add_action("wp_ajax_setAchievement", [BR_Achievement::instance(), 'setAchievement']);
add_action("wp_ajax_setQuestTabi", [BR_Quest::instance(), 'setQuestTabi']);
add_action("wp_ajax_setGuild", [BR_Guild::instance(), 'setGuild']);
add_action("wp_ajax_setSpeaker", [BR_Session::instance(), 'setSpeaker']);
add_action("wp_ajax_setSpeakerData", [BR_Session::instance(), 'setSpeakerData']);
add_action("wp_ajax_updateOrg", [BR_Organization::instance(), 'updateOrg']);
add_action("wp_ajax_updateSponsor", [BR_Session::instance(), 'updateSponsor']);
add_action("wp_ajax_findPlayersToOrg", [BR_Organization::instance(), 'findPlayersToOrg']);
add_action("wp_ajax_addPlayerToOrg", [BR_Organization::instance(), 'addPlayerToOrg']);
add_action("wp_ajax_setPlayerOrgCapabilities", [BR_Organization::instance(), 'setPlayerOrgCapabilities']);
add_action("wp_ajax_previewTemplate", [BR_Adventure::instance(), 'previewTemplate']);
add_action("wp_ajax_createChildAdventure", [BR_Adventure::instance(), 'createChildAdventure']);
add_action("wp_ajax_updateGuild", [BR_Guild::instance(), 'updateGuild']);
add_action("wp_ajax_updateBlocker", [BR_Blocker::instance(), 'updateBlocker']);
add_action("wp_ajax_payBlocker", [BR_Blocker::instance(), 'payBlocker']);
add_action("wp_ajax_payment", [BR_Transaction::instance(), 'payment']);
add_action("wp_ajax_submitPlayerWork", [BR_Quest::instance(), 'submitPlayerWork']);
add_action("wp_ajax_validatePlayerWork", [BR_Quest::instance(), 'validatePlayerWork']);
add_action("wp_ajax_postToWall", [BR_Quest::instance(), 'postToWall']);
add_action("wp_ajax_loadChat", [BR_Announcement::instance(), 'loadChat']);
add_action("wp_ajax_updateItem", [BR_Item::instance(), 'updateItem']);
add_action("wp_ajax_buyItem", [BR_Item::instance(), 'buyItem']);
add_action("wp_ajax_pickupItem", [BR_Item::instance(), 'pickupItem']);
add_action("wp_ajax_checkItem", [BR_Item::instance(), 'checkItem']);
add_action("wp_ajax_useItem", [BR_Item::instance(), 'useItem']);
add_action("wp_ajax_startAttempt", [BR_Challenge::instance(), 'startAttempt']);
add_action("wp_ajax_submitAnswer", [BR_Challenge::instance(), 'submitAnswer']);
add_action("wp_ajax_gradeChallenge", [BR_Challenge::instance(), 'gradeChallenge']);
add_action("wp_ajax_setCurrentQuest", [BR_Player::instance(), 'setCurrentQuest']);
add_action("wp_ajax_submitSurveyAnswer", [BR_Survey::instance(), 'submitSurveyAnswer']);
add_action("wp_ajax_br_trash", [BR_Trash::instance(), 'br_trash']);
add_action("wp_ajax_br_empty_trash", [BR_Trash::instance(), 'br_empty_trash']);
add_action("wp_ajax_magicCode", [BR_Achievement::instance(), 'magicCode']);
add_action("wp_ajax_choosePath", [BR_Achievement::instance(), 'choosePath']);
add_action("wp_ajax_triggerAchievement", [BR_Achievement::instance(), 'triggerAchievement']);
add_action("wp_ajax_triggerAchievements", [BR_Achievement::instance(), 'triggerAchievements']);
add_action("wp_ajax_triggerGuild", [BR_Guild::instance(), 'triggerGuild']);
add_action("wp_ajax_bulkAssignGuild", [BR_Guild::instance(), 'bulkAssignGuild']);
add_action("wp_ajax_bulkAssignAchievement", [BR_Achievement::instance(), 'bulkAssignAchievement']);
add_action("wp_ajax_resetTransactions", [BR_Transaction::instance(), 'resetTransactions']);
add_action("wp_ajax_resetDemoAdventurePlayer", [BR_Transaction::instance(), 'resetDemoAdventurePlayer']);
add_action("wp_ajax_resetPlayerPassword", [BR_Player::instance(), 'resetPlayerPassword']);
add_action("wp_ajax_setPlayerAdventureRole", [BR_Player::instance(), 'setPlayerAdventureRole']);
add_action("wp_ajax_updatePlayerAdventureStatus", [BR_Player::instance(), 'updatePlayerAdventureStatus']);
add_action("wp_ajax_updateAdventureTitle", [BR_Adventure::instance(), 'updateAdventureTitle']);
add_action("wp_ajax_setTitle", [BR_Adventure::instance(), 'setTitle']);
add_action("wp_ajax_setBadge", [BR_Adventure::instance(), 'setBadge']);
add_action("wp_ajax_setColor", [BR_Adventure::instance(), 'setColor']);
add_action("wp_ajax_setLevel", [BR_Adventure::instance(), 'setLevel']);
add_action("wp_ajax_setXP", [BR_Adventure::instance(), 'setXP']);
add_action("wp_ajax_setEP", [BR_Adventure::instance(), 'setEP']);
add_action("wp_ajax_setBLOO", [BR_Adventure::instance(), 'setBLOO']);
add_action("wp_ajax_setValidate", [BR_Adventure::instance(), 'setValidate']);
add_action("wp_ajax_setOptional", [BR_Adventure::instance(), 'setOptional']);
add_action("wp_ajax_setMaxPlayers", [BR_Adventure::instance(), 'setMaxPlayers']);
add_action("wp_ajax_setStartDate", [BR_Adventure::instance(), 'setStartDate']);
add_action("wp_ajax_setDeadline", [BR_Adventure::instance(), 'setDeadline']);
add_action("wp_ajax_setMagicCode", [BR_Adventure::instance(), 'setMagicCode']);
add_action("wp_ajax_setCategory", [BR_Adventure::instance(), 'setCategory']);
add_action("wp_ajax_setGuildGroup", [BR_Guild::instance(), 'setGuildGroup']);
add_action("wp_ajax_setGuildCapacity", [BR_Guild::instance(), 'setGuildCapacity']);
add_action("wp_ajax_setDisplayStyle", [BR_Adventure::instance(), 'setDisplayStyle']);
add_action("wp_ajax_setDimensions", [BR_Tabi::instance(), 'setDimensions']);
add_action("wp_ajax_setTabiOnJourney", [BR_Tabi::instance(), 'setTabiOnJourney']);
add_action("wp_ajax_setTabiAsCategory", [BR_Tabi::instance(), 'setTabiAsCategory']);
add_action("wp_ajax_saveTabiSize", [BR_Tabi::instance(), 'saveTabiSize']);
add_action("wp_ajax_saveTabiPosition", [BR_Tabi::instance(), 'saveTabiPosition']);
add_action("wp_ajax_saveTabiPrerequisites", [BR_Tabi::instance(), 'saveTabiPrerequisites']);
add_action("wp_ajax_addJourneyAsset", [BR_Tabi::instance(), 'addJourneyAsset']);
add_action("wp_ajax_trashJourneyAsset", [BR_Tabi::instance(), 'trashJourneyAsset']);
add_action("wp_ajax_duplicateJourneyAsset", [BR_Tabi::instance(), 'duplicateJourneyAsset']);
add_action("wp_ajax_saveJourneyAssetPosition", [BR_Tabi::instance(), 'saveJourneyAssetPosition']);
add_action("wp_ajax_saveJourneyAssetProperties", [BR_Tabi::instance(), 'saveJourneyAssetProperties']);
add_action("wp_ajax_setJourneyAssetImage", [BR_Tabi::instance(), 'setJourneyAssetImage']);
add_action("wp_ajax_saveJourneyAssetMeta", [BR_Tabi::instance(), 'saveJourneyAssetMeta']);
add_action("wp_ajax_saveJourneyAssetTabi", [BR_Tabi::instance(), 'saveJourneyAssetTabi']);
add_action("wp_ajax_setNickname", [BR_Player::instance(), 'setNickname']);
add_action("wp_ajax_setProfilePicture", [BR_Player::instance(), 'setProfilePicture']);
add_action("wp_ajax_exportPlayersWork", [BR_Player::instance(), 'exportPlayersWork']);
add_action("wp_ajax_newUniqueAchievementCode", [BR_Achievement::instance(), 'newUniqueAchievementCode']);
add_action("wp_ajax_deleteAchievementCode", [BR_Achievement::instance(), 'deleteAchievementCode']);
add_action("wp_ajax_duplicateQuests", [BR_Quest::instance(), 'duplicateQuests']);
add_action("wp_ajax_duplicateQuest", [BR_Quest::instance(), 'duplicateQuest']);
add_action("wp_ajax_duplicateRow", [BR_Quest::instance(), 'duplicateRow']);
add_action("wp_ajax_breakParent", [BR_Adventure::instance(), 'breakParent']);
add_action("wp_ajax_updatePrevLevel", [BR_Player::instance(), 'updatePrevLevel']);
add_action("wp_ajax_rateQuest", [BR_Quest::instance(), 'rateQuest']);
add_action("wp_ajax_failQuest", [BR_Quest::instance(), 'failQuest']);
add_action("wp_ajax_saveSettings", [BR_Config::instance(), 'saveSettings']);
add_action("wp_ajax_saveSysConfig", [BR_Config::instance(), 'saveSysConfig']);
add_action("wp_ajax_savePlan", [BR_Config::instance(), 'savePlan']);
add_action("wp_ajax_deletePlan", [BR_Config::instance(), 'deletePlan']);
add_action("wp_ajax_savePlanFeatures", [BR_Config::instance(), 'savePlanFeatures']);
add_action("wp_ajax_assignUserPlan", [BR_Config::instance(), 'assignUserPlan']);
add_action("wp_ajax_searchPlayersForPlan", [BR_Config::instance(), 'searchPlayersForPlan']);
add_action("wp_ajax_saveFeature", [BR_Config::instance(), 'saveFeature']);
add_action("wp_ajax_deleteFeature", [BR_Config::instance(), 'deleteFeature']);
add_action("wp_ajax_copyPlanFeatures", [BR_Config::instance(), 'copyPlanFeatures']);
add_action("wp_ajax_saveRoleDefaults", [BR_Config::instance(), 'saveRoleDefaults']);
add_action("wp_ajax_anonimizeAdventure", [BR_Player::instance(), 'anonimizeAdventure']);
add_action("wp_ajax_spendEP", [BR_Objective::instance(), 'spendEP']);
add_action("wp_ajax_addObjective", [BR_Objective::instance(), 'addObjective']);
add_action("wp_ajax_editObjective", [BR_Objective::instance(), 'editObjective']);
add_action("wp_ajax_updateObjective", [BR_Objective::instance(), 'updateObjective']);
add_action("wp_ajax_resetQuestObjectives", [BR_Objective::instance(), 'resetQuestObjectives']);
add_action("wp_ajax_removeObjective", [BR_Objective::instance(), 'removeObjective']);
add_action("wp_ajax_getObjectives", [BR_Objective::instance(), 'getObjectives']);
add_action("wp_ajax_factCheck", [BR_Objective::instance(), 'factCheck']);
add_action("wp_ajax_insertSolvedObjective", [BR_Objective::instance(), 'insertSolvedObjective']);
add_action("wp_ajax_addStep", [BR_Step::instance(), 'addStep']);
add_action("wp_ajax_editStep", [BR_Step::instance(), 'editStep']);
add_action("wp_ajax_removeStep", [BR_Step::instance(), 'removeStep']);
add_action("wp_ajax_updateStep", [BR_Step::instance(), 'updateStep']);
add_action("wp_ajax_reorderSteps", [BR_Step::instance(), 'reorderSteps']);
add_action("wp_ajax_loadStepButtonForm", [BR_Step::instance(), 'loadStepButtonForm']);
add_action("wp_ajax_addStepButton", [BR_Step::instance(), 'addStepButton']);
add_action("wp_ajax_removeStepButton", [BR_Step::instance(), 'removeStepButton']);
add_action("wp_ajax_updateStepButton", [BR_Step::instance(), 'updateStepButton']);
add_action("wp_ajax_addQuestion", [BR_Challenge::instance(), 'addQuestion']);
add_action("wp_ajax_duplicateQuestion", [BR_Challenge::instance(), 'duplicateQuestion']);
add_action("wp_ajax_updateQuestion", [BR_Challenge::instance(), 'updateQuestion']);
add_action("wp_ajax_removeQuestion", [BR_Challenge::instance(), 'removeQuestion']);
add_action("wp_ajax_addOption", [BR_Challenge::instance(), 'addOption']);
add_action("wp_ajax_updateOption", [BR_Challenge::instance(), 'updateOption']);
add_action("wp_ajax_removeOption", [BR_Challenge::instance(), 'removeOption']);
add_action("wp_ajax_updateSpeaker", [BR_Session::instance(), 'updateSpeaker']);
add_action("wp_ajax_updateSession", [BR_Session::instance(), 'updateSession']);
add_action("wp_ajax_downloadAllImages", [BR_Utils::instance(), 'downloadAllImages']);
add_action("wp_ajax_loadContent", [BR_Content::instance(), 'loadContent']);
add_action("wp_ajax_loadStory", [BR_Adventure::instance(), 'loadStory']);
add_action("wp_ajax_br_logout", [BR_Player::instance(), 'br_logout']);
add_action("wp_ajax_br_scorm_upload", array('BR_SCORM', 'ajax_upload'));
add_action("wp_ajax_br_scorm_save_data", array('BR_SCORM', 'ajax_save_data'));
add_action("wp_ajax_br_scorm_reset_all", array('BR_SCORM', 'ajax_reset_all'));
add_action("wp_ajax_submitRequest", [BR_Request::instance(), 'submitRequest']);
add_action("wp_ajax_getRequests", [BR_Request::instance(), 'getRequests']);
add_action("wp_ajax_getMyRequests", [BR_Request::instance(), 'getMyRequests']);
add_action("wp_ajax_updateRequestStatus", [BR_Request::instance(), 'updateRequestStatus']);
add_action("wp_ajax_br_run_milestone_migration", 'br_run_milestone_migration');
add_action("wp_ajax_br_complete_step", [BR_Step::instance(), 'ajaxCompleteStep']);
add_action("wp_ajax_br_update_branch_group", [BR_Branch::instance(), 'updateBranchGroup']);
add_action("wp_ajax_br_save_branch_rule", [BR_Branch::instance(), 'saveBranchRule']);
add_action("wp_ajax_br_delete_branch_rule", [BR_Branch::instance(), 'deleteBranchRule']);
add_action("wp_ajax_br_assign_achievement_to_group", [BR_Branch::instance(), 'assignAchievementToGroup']);
add_action("wp_ajax_br_remove_achievement_from_group", [BR_Branch::instance(), 'removeAchievementFromGroup']);
add_action("wp_ajax_br_delete_branch_group", [BR_Branch::instance(), 'deleteBranchGroup']);
add_action("wp_ajax_br_player_branch_choice", [BR_Branch::instance(), 'ajaxPlayerBranchChoice']);
add_action("wp_ajax_br_save_ai_api_key", 'br_save_ai_api_key');
add_action("wp_ajax_br_ai_validate_text", 'br_ai_validate_text');


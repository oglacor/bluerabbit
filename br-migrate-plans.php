<?php
/**
 * BlueRabbit Plans Migration Script
 *
 * Run once via WP-CLI:   wp eval-file wp-content/themes/bluerabbit/br-migrate-plans.php
 * Or via admin URL:      ?br_migrate_plans=1  (admin only)
 *
 * This script:
 * 1. Creates br_plans and br_plan_features tables (if not exist)
 * 2. Seeds the 4 plans: God Mode (system), Basic, Pro, Enterprise (standard)
 * 3. Migrates feature_access_* column values into br_plan_features rows
 * 4. Seeds role default plan mappings in br_config
 * 5. Sets default user_plan_id on br_players based on WP roles
 *    (player_id=1 always gets God Mode)
 * 6. Logs a summary of what was migrated
 *
 * Safe to run multiple times (idempotent).
 */

if (defined('WP_CLI') && WP_CLI) {
    // Already bootstrapped
} elseif (!defined('ABSPATH')) {
    $wp_load = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once($wp_load);
    } else {
        die('Cannot find wp-load.php');
    }
}

if (!defined('WP_CLI') || !WP_CLI) {
    if (!current_user_can('manage_options')) {
        die('Access denied. Administrator required.');
    }
    if (!isset($_GET['br_migrate_plans'])) {
        die('Add ?br_migrate_plans=1 to the URL to run this migration.');
    }
}

global $wpdb;
$log = array();

function br_log($msg) {
    global $log;
    $log[] = date('H:i:s') . ' - ' . $msg;
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log($msg);
    }
}

// --- Step 1: Create tables ---
br_log('Step 1: Creating tables...');

$charset_collate = $wpdb->get_charset_collate();
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$sql_plans = "CREATE TABLE {$wpdb->prefix}br_plans (
    `plan_id` BIGINT NOT NULL AUTO_INCREMENT,
    `plan_key` VARCHAR(50) NOT NULL,
    `plan_label` TEXT NOT NULL,
    `plan_type` VARCHAR(20) NOT NULL DEFAULT 'standard',
    `plan_status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `plan_notes` TEXT NULL,
    `plan_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`plan_id`),
UNIQUE KEY `plan_key` (`plan_key`) ) $charset_collate;";

$sql_plan_features = "CREATE TABLE {$wpdb->prefix}br_plan_features (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `plan_id` BIGINT NOT NULL,
    `feature_id` BIGINT NOT NULL,
    `feature_value` TEXT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `plan_feature` (`plan_id`, `feature_id`) ) $charset_collate;";

dbDelta($sql_plans);
dbDelta($sql_plan_features);
br_log('Tables created (or already exist).');

// --- Step 2: Seed plans ---
br_log('Step 2: Seeding plans...');

$standard_plans = array(
    array('key' => 'god',        'label' => 'God Mode',      'type' => 'system'),
    array('key' => 'basic',      'label' => 'Basic',         'type' => 'standard'),
    array('key' => 'pro',        'label' => 'Pro',           'type' => 'standard'),
    array('key' => 'enterprise', 'label' => 'Enterprise',    'type' => 'standard'),
);

$plans_seeded = 0;
foreach ($standard_plans as $sp) {
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT plan_id FROM {$wpdb->prefix}br_plans WHERE plan_key = %s", $sp['key']
    ));
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}br_plans", array(
            'plan_key'   => $sp['key'],
            'plan_label' => $sp['label'],
            'plan_type'  => $sp['type'],
        ), array('%s', '%s', '%s'));
        $plans_seeded++;
        br_log("  Inserted plan: {$sp['key']} ({$sp['type']})");
    } else {
        br_log("  Plan '{$sp['key']}' already exists (id: $exists), skipping.");
    }
}
br_log("Plans seeded: $plans_seeded new.");

// --- Step 3: Migrate feature_access_* to br_plan_features ---
br_log('Step 3: Migrating feature access values...');

// Map legacy column names to new plan keys
$legacy_to_plan = array(
    'feature_access_free'  => 'basic',
    'feature_access_pro'   => 'pro',
    'feature_access_admin' => 'enterprise',
    'feature_access_god'   => 'god',
);

$plan_map = array();
$plans_rows = $wpdb->get_results("SELECT plan_id, plan_key FROM {$wpdb->prefix}br_plans");
foreach ($plans_rows as $p) {
    $plan_map[$p->plan_key] = $p->plan_id;
}

$features = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_features");
$migrated = 0;
$skipped = 0;

foreach ($features as $f) {
    foreach ($legacy_to_plan as $col => $plan_key) {
        $val = isset($f->$col) ? $f->$col : '0';
        if (!isset($plan_map[$plan_key])) continue;
        $pid = $plan_map[$plan_key];

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}br_plan_features WHERE plan_id = %d AND feature_id = %d",
            $pid, $f->feature_id
        ));

        if (!$exists) {
            $wpdb->insert("{$wpdb->prefix}br_plan_features", array(
                'plan_id'       => $pid,
                'feature_id'    => $f->feature_id,
                'feature_value' => $val,
            ), array('%d', '%d', '%s'));
            $migrated++;
        } else {
            $skipped++;
        }
    }
}
br_log("Feature values migrated: $migrated new rows, $skipped already existed.");

// --- Step 4: Seed role default plan mappings ---
br_log('Step 4: Seeding role default plan mappings in br_config...');

$role_defaults = array(
    'role_default_plan_administrator'    => array('label' => 'Default plan for Administrators',    'value' => 'enterprise'),
    'role_default_plan_br_game_master'   => array('label' => 'Default plan for Game Masters',      'value' => 'pro'),
    'role_default_plan_br_npc'           => array('label' => 'Default plan for NPCs',              'value' => 'pro'),
    'role_default_plan_br_player'        => array('label' => 'Default plan for Players',           'value' => 'basic'),
    'role_default_plan_default'          => array('label' => 'Default plan (fallback)',             'value' => 'basic'),
);

foreach ($role_defaults as $rd_name => $rd) {
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT config_id FROM {$wpdb->prefix}br_config WHERE config_name = %s", $rd_name
    ));
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}br_config", array(
            'config_name'  => $rd_name,
            'config_label' => $rd['label'],
            'config_type'  => 'text',
            'config_value' => $rd['value'],
        ), array('%s', '%s', '%s', '%s'));
        br_log("  Inserted role default: $rd_name = {$rd['value']}");
    } else {
        br_log("  Role default '$rd_name' already exists, skipping.");
    }
}

// --- Step 5: Set user_plan_id on br_players ---
br_log('Step 5: Setting user_plan_id on players...');

$col_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}br_players LIKE 'user_plan_id'");
if (empty($col_exists)) {
    $wpdb->query("ALTER TABLE {$wpdb->prefix}br_players ADD COLUMN user_plan_id BIGINT NULL");
    br_log('  Added user_plan_id column to br_players.');
} else {
    br_log('  user_plan_id column already exists.');
}

// Player ID 1 always gets God Mode
if (isset($plan_map['god'])) {
    $wpdb->update(
        "{$wpdb->prefix}br_players",
        array('user_plan_id' => $plan_map['god']),
        array('player_id' => 1),
        array('%d'), array('%d')
    );
    br_log('  Player ID 1 assigned God Mode.');
}

// Set defaults for remaining players without a plan
$players = $wpdb->get_results("SELECT player_id, player_email FROM {$wpdb->prefix}br_players WHERE user_plan_id IS NULL");
$assigned = 0;

foreach ($players as $player) {
    $wp_user = get_user_by('email', $player->player_email);
    if (!$wp_user) continue;

    $role = isset($wp_user->roles[0]) ? $wp_user->roles[0] : '';
    $plan_key = 'basic';
    if ($role == 'administrator') {
        $plan_key = 'enterprise';
    } elseif ($role == 'br_game_master' || $role == 'br_npc') {
        $plan_key = 'pro';
    }

    if (isset($plan_map[$plan_key])) {
        $wpdb->update(
            "{$wpdb->prefix}br_players",
            array('user_plan_id' => $plan_map[$plan_key]),
            array('player_id' => $player->player_id),
            array('%d'), array('%d')
        );
        $assigned++;
    }
}
br_log("User plan assignments: $assigned players updated, " . (count($players) - $assigned) . " skipped.");

// --- Summary ---
br_log('');
br_log('=== MIGRATION COMPLETE ===');
br_log("Plans in br_plans: " . $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}br_plans"));
br_log("Rows in br_plan_features: " . $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}br_plan_features"));
br_log("Players with user_plan_id set: " . $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}br_players WHERE user_plan_id IS NOT NULL"));
br_log("Features in br_features: " . count($features));
br_log('');
br_log('Legacy mapping used: free->basic, pro->pro, admin->enterprise, god->god');
br_log('The legacy feature_access_* columns in br_features have NOT been dropped.');

if (!defined('WP_CLI') || !WP_CLI) {
    header('Content-Type: text/plain');
    echo implode("\n", $log);
}

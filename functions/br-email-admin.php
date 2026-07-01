<?php
/**
 * BLUERABBIT Email Notification System
 *
 * Settings:  WP Admin → BR Email → Settings    (slug: br_email_settings)
 * Compose:   WP Admin → BR Email → Compose     (slug: br_email_compose)
 * Log:       WP Admin → BR Email → Send Log    (slug: br_email_log)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$br_email_dir = __DIR__ . '/email/';
require_once $br_email_dir . 'br-email-core.php';
require_once $br_email_dir . 'br-email-settings.php';
require_once $br_email_dir . 'br-email-compose.php';
require_once $br_email_dir . 'br-email-log.php';
require_once $br_email_dir . 'br-email-ajax.php';
require_once $br_email_dir . 'br-email-actions.php';
require_once $br_email_dir . 'br-email-frontend.php';

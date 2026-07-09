<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── DB migration ─────────────────────────────────────────────────────────────

add_action( 'init', 'br_email_maybe_migrate', 5 );
function br_email_maybe_migrate(): void {
	global $wpdb;
	$log_table      = "{$wpdb->prefix}br_email_log";
	$campaign_table = "{$wpdb->prefix}br_email_campaigns";
	$charset        = $wpdb->get_charset_collate();

	$row = $wpdb->get_row( "SHOW COLUMNS FROM {$log_table} LIKE 'sender_id'" );
	if ( ! $row ) {
		$wpdb->query( "ALTER TABLE {$log_table} ADD COLUMN sender_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER log_id" );
	}

	$wpdb->query( "CREATE TABLE IF NOT EXISTS {$campaign_table} (
		campaign_id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		adventure_id INT(11) NOT NULL,
		sender_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		subject VARCHAR(255) NOT NULL,
		body LONGTEXT NOT NULL,
		recipient_count INT(11) NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL
	) {$charset}" );

	$row = $wpdb->get_row( "SHOW COLUMNS FROM {$log_table} LIKE 'campaign_id'" );
	if ( ! $row ) {
		$wpdb->query( "ALTER TABLE {$log_table} ADD COLUMN campaign_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER sender_id" );
	}
}

// ── Admin menu ────────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'br_email_register_menus' );
function br_email_register_menus(): void {
	add_menu_page(
		__( 'BR Email', 'bluerabbit' ),
		__( 'BR Email', 'bluerabbit' ),
		'manage_options',
		'br_email_settings',
		'br_email_settings_page',
		'dashicons-email-alt',
		80
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Email Settings', 'bluerabbit' ),
		__( 'Settings', 'bluerabbit' ),
		'manage_options',
		'br_email_settings',
		'br_email_settings_page'
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Compose & Send', 'bluerabbit' ),
		__( 'Compose & Send', 'bluerabbit' ),
		'manage_options',
		'br_email_compose',
		'br_email_compose_page'
	);
	add_submenu_page(
		'br_email_settings',
		__( 'Send Log', 'bluerabbit' ),
		__( 'Send Log', 'bluerabbit' ),
		'manage_options',
		'br_email_log',
		'br_email_log_page'
	);
}

// ── Enqueue admin assets ──────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', 'br_email_enqueue_scripts' );
function br_email_enqueue_scripts( string $hook ): void {
	if ( strpos( $hook, 'br_email' ) === false ) return;

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_media();

	wp_add_inline_script( 'wp-color-picker', '
jQuery(function($){
	/* Colour pickers */
	$(".br-color-picker").wpColorPicker();

	/* Media uploader */
	$(document).on("click", ".br-upload-btn", function(e){
		e.preventDefault();
		var btn    = $(this);
		var target = btn.data("target");
		var frame  = wp.media({
			title:    "Select Logo",
			button:   { text: "Use this image" },
			multiple: false
		});
		frame.on("select", function(){
			var att = frame.state().get("selection").first().toJSON();
			$("#" + target).val(att.url);
			var prev = btn.siblings(".br-logo-preview");
			if(prev.length){ prev.attr("src", att.url).show(); }
		});
		frame.open();
	});

	/* Adventure → user count */
	$(document).on("change", "#br_adventure_id", function(){
		var adv_id = $(this).val();
		var badge  = $("#br_user_count");
		if(!adv_id){ badge.text("—"); return; }
		$.post(ajaxurl, { action:"br_email_user_count", adventure_id:adv_id,
		                  nonce:brEmail.nonce }, function(r){
			badge.text(r.success ? r.data.count + " recipients" : "?");
		});
	});

	/* Preview */
	$(document).on("click", "#br_preview_btn", function(e){
		e.preventDefault();
		var subject  = $("#br_subject").val();
		var body_val = "";
		if(typeof tinyMCE !== "undefined" && tinyMCE.get("br_email_body")){
			body_val = tinyMCE.get("br_email_body").getContent();
		} else {
			body_val = $("#br_email_body").val();
		}
		$.post(ajaxurl, {
			action:   "br_email_preview",
			subject:  subject,
			body:     body_val,
			nonce:    brEmail.nonce
		}, function(r){
			if(!r.success) return;
			var win = window.open("", "_blank",
				"width=700,height=600,scrollbars=yes,resizable=yes");
			win.document.write(r.data.html);
			win.document.close();
		});
	});
});
' );

	wp_localize_script( 'wp-color-picker', 'brEmail', [
		'nonce' => wp_create_nonce( 'br_email_ajax' ),
	] );
}

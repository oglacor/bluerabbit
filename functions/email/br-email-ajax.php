<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── AJAX: user count ──────────────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_user_count', 'br_email_ajax_user_count' );
function br_email_ajax_user_count(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( null, 403 );

	$adv_id = (int) ( $_POST['adventure_id'] ?? 0 );
	if ( ! $adv_id ) wp_send_json_error( 'missing adventure_id' );

	$mailer = new BR_Mailer();
	wp_send_json_success( [ 'count' => $mailer->count_adventure_users( $adv_id ) ] );
}

// ── AJAX: preview ─────────────────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_preview', 'br_email_ajax_preview' );
function br_email_ajax_preview(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );

	$current_user = wp_get_current_user();
	$adventure_id = (int) ( $_POST['adventure_id'] ?? 0 );

	$allowed = current_user_can( 'manage_options' )
		|| ( $adventure_id && br_email_user_can_send( $current_user->ID, $adventure_id ) );
	if ( ! $allowed ) wp_send_json_error( null, 403 );

	$settings = get_option( 'br_email_settings', [] );

	if ( $adventure_id ) {
		global $wpdb;
		$adv = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
			$adventure_id
		) );
		$settings['_adventure_name'] = $adv ? $adv->adventure_title : 'Sample Adventure';
	} else {
		$settings['_adventure_name'] = 'Sample Adventure';
	}

	$mailer  = new BR_Mailer();
	$subject = sanitize_text_field( $_POST['subject'] ?? 'Preview Subject' );
	$body    = wp_kses_post( $_POST['body'] ?? '<p>Hello {{name}},</p><p>This is a preview.</p>' );

	$preview_user = [
		'player_id'    => $current_user->ID,
		'user_email'   => $current_user->user_email,
		'display_name' => $current_user->display_name,
	];

	$html = $mailer->render_template( $settings, $subject, $body, $preview_user );
	wp_send_json_success( [ 'html' => $html ] );
}

// ── AJAX: get campaign body ───────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_get_campaign_body', 'br_email_ajax_get_campaign_body' );
function br_email_ajax_get_campaign_body(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( null, 403 );

	$campaign_id = (int) ( $_POST['campaign_id'] ?? 0 );
	$campaign    = BR_Mailer::get_campaign( $campaign_id );
	if ( ! $campaign ) wp_send_json_error( [ 'message' => 'Campaign not found.' ] );

	wp_send_json_success( [
		'subject' => esc_html( $campaign->subject ),
		'body'    => wp_kses_post( $campaign->body ),
	] );
}

// ── AJAX: missing recipients ──────────────────────────────────────────────────

add_action( 'wp_ajax_br_email_missing_recipients', 'br_email_ajax_missing_recipients' );
function br_email_ajax_missing_recipients(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );

	global $wpdb;
	$campaign_id  = (int) ( $_POST['campaign_id']  ?? 0 );
	$adventure_id = (int) ( $_POST['adventure_id'] ?? 0 );

	if ( ! $campaign_id || ! $adventure_id ) {
		wp_send_json_error( [ 'message' => 'Missing parameters.' ] );
	}

	// Step 1: ALL enrolled players — including opted-out, so count is always accurate.
	$enrolled = $wpdb->get_col( $wpdb->prepare(
		"SELECT pa.player_id
		   FROM {$wpdb->prefix}br_player_adventure pa
		  WHERE pa.adventure_id            = %d
		    AND pa.player_adventure_status = 'in'",
		$adventure_id
	) );

	// Step 2: Everyone who has ANY log entry for this campaign (sent OR failed).
	$reached = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT user_id FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d",
		$campaign_id
	) );

	// Step 3: Subtract — these are players who never received an attempt.
	$missing_ids = array_values( array_diff( $enrolled, $reached ) );

	// Step 4: Get opted-out IDs so we can label them in the list.
	$optout_ids = [];
	if ( ! empty( $missing_ids ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $missing_ids ), '%d' ) );
		$optout_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta}
				  WHERE meta_key = 'br_email_optout' AND meta_value = '1'
				    AND user_id IN ( {$placeholders} )",
				...$missing_ids
			)
		);
	}
	$optout_set = array_flip( $optout_ids );

	$users = [];
	if ( ! empty( $missing_ids ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $missing_ids ), '%d' ) );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID AS player_id, user_email, display_name
				   FROM {$wpdb->users}
				  WHERE ID IN ( {$placeholders} )
				  ORDER BY display_name",
				...$missing_ids
			), ARRAY_A
		);
		foreach ( $rows as $row ) {
			$row['optout'] = isset( $optout_set[ $row['player_id'] ] );
			$users[] = $row;
		}
	}

	$csv_url = wp_nonce_url(
		add_query_arg( [ 'br_email_csv_missing' => $campaign_id ], admin_url( 'admin.php' ) ),
		'br_csv_missing_' . $campaign_id
	);

	wp_send_json_success( [
		'count'   => count( $users ),
		'users'   => $users,
		'csv_url' => $csv_url,
	] );
}

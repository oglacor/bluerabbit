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

	$campaign_id = (int) ( $_POST['campaign_id'] ?? 0 );
	if ( ! $campaign_id ) wp_send_json_error( [ 'message' => 'Missing parameters.' ] );

	$mailer = new BR_Mailer();
	$users  = $mailer->get_missing_recipients( $campaign_id );

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

// ── AJAX: start a campaign (creates the campaign + persists the exact
//    recipient list, but sends nothing — the caller then polls
//    br_email_send_batch repeatedly until 'remaining' is 0) ──────────────────

add_action( 'wp_ajax_br_email_start_campaign', 'br_email_ajax_start_campaign' );
function br_email_ajax_start_campaign(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );

	$current_user = wp_get_current_user();
	$adventure_id = (int) ( $_POST['adventure_id'] ?? 0 );

	$allowed = current_user_can( 'manage_options' )
		|| ( $adventure_id && br_email_user_can_send( $current_user->ID, $adventure_id ) );
	if ( ! $allowed ) wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );

	$subject    = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$body       = wp_kses_post( wp_unslash( $_POST['body'] ?? '' ) );
	$recipients = sanitize_text_field( $_POST['recipients'] ?? 'all' );

	if ( ! $adventure_id || ! $subject || ! $body ) {
		wp_send_json_error( [ 'message' => __( 'Adventure, subject and body are all required.', 'bluerabbit' ) ] );
	}

	global $wpdb;
	$adventure = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d AND adventure_status = 'publish'",
		$adventure_id
	) );
	if ( ! $adventure ) wp_send_json_error( [ 'message' => __( 'Adventure not found.', 'bluerabbit' ) ] );

	$mailer = new BR_Mailer();

	$sender_name  = sanitize_text_field( wp_unslash( $_POST['sender_name']  ?? '' ) );
	$sender_email = sanitize_email( wp_unslash( $_POST['sender_email'] ?? '' ) );
	if ( $sender_name && $sender_email ) {
		$mailer->set_sender_override( $sender_name . ' · ' . $adventure->adventure_title, $sender_email, $sender_name );
	}
	$mailer->set_sender_id( $current_user->ID );

	$all_users = $mailer->get_adventure_users( $adventure_id );

	if ( $recipients !== 'all' ) {
		$player_ids = array_filter( array_map( 'intval', explode( ',', $recipients ) ) );
		if ( empty( $player_ids ) ) wp_send_json_error( [ 'message' => __( 'No recipients selected.', 'bluerabbit' ) ] );
		$id_set = array_flip( $player_ids );
		$users  = array_values( array_filter( $all_users, function ( $u ) use ( $id_set ) {
			return isset( $id_set[ (int) $u['player_id'] ] );
		} ) );
	} else {
		$users = $all_users;
	}

	if ( empty( $users ) ) wp_send_json_error( [ 'message' => __( 'No eligible recipients found.', 'bluerabbit' ) ] );

	$result = $mailer->start_campaign( $users, $adventure_id, $subject, $body );
	wp_send_json_success( $result );
}

// ── AJAX: send the next small batch for a campaign (polled repeatedly) ───────

add_action( 'wp_ajax_br_email_send_batch', 'br_email_ajax_send_batch' );
function br_email_ajax_send_batch(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );

	$campaign_id = (int) ( $_POST['campaign_id'] ?? 0 );
	if ( ! $campaign_id ) wp_send_json_error( [ 'message' => 'Missing campaign_id' ] );

	$campaign = BR_Mailer::get_campaign( $campaign_id );
	if ( ! $campaign ) wp_send_json_error( [ 'message' => 'Campaign not found' ] );

	$current_user = wp_get_current_user();
	$allowed = current_user_can( 'manage_options' )
		|| br_email_user_can_send( $current_user->ID, (int) $campaign->adventure_id );
	if ( ! $allowed ) wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );

	$mailer = new BR_Mailer();
	$result = $mailer->send_next_batch( $campaign_id );
	wp_send_json_success( $result );
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Frontend: enqueue assets for email-notifications page ────────────────────

add_action( 'wp_enqueue_scripts', 'br_email_frontend_assets' );
function br_email_frontend_assets(): void {
	if ( ! is_page( 'email-notifications' ) ) return;
	wp_enqueue_editor();
	wp_localize_script( 'jquery', 'brEmailFront', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'br_email_ajax' ),
	] );
}

// ── Frontend: permission helper ───────────────────────────────────────────────

function br_email_user_can_send( int $user_id, int $adventure_id ): bool {
	global $wpdb;

	if ( user_can( $user_id, 'manage_options' ) ) return true;

	$adv = $wpdb->get_row( $wpdb->prepare(
		"SELECT adventure_owner FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
		$adventure_id
	) );
	if ( $adv && (int) $adv->adventure_owner === $user_id ) return true;

	return (bool) $wpdb->get_var( $wpdb->prepare(
		"SELECT 1 FROM {$wpdb->prefix}br_player_adventure
		  WHERE player_id = %d AND adventure_id = %d AND player_adventure_role = 'gm'",
		$user_id,
		$adventure_id
	) );
}

// ── Frontend: AJAX send handler ───────────────────────────────────────────────

add_action( 'wp_ajax_br_send_notification_email', 'br_email_handle_notification_send' );
function br_email_handle_notification_send(): void {
	check_ajax_referer( 'br_email_ajax', 'nonce' );

	$current_user = wp_get_current_user();
	$adventure_id = (int) ( $_POST['adventure_id'] ?? 0 );

	if ( ! $adventure_id || ! $current_user->ID ) {
		wp_send_json_error( [ 'message' => __( 'Missing adventure context.', 'bluerabbit' ) ] );
	}

	if ( ! br_email_user_can_send( $current_user->ID, $adventure_id ) ) {
		wp_send_json_error( [ 'message' => __( 'You do not have permission to send emails for this adventure.', 'bluerabbit' ) ] );
	}

	$subject    = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$body       = wp_kses_post( wp_unslash( $_POST['body'] ?? '' ) );
	$recipients = sanitize_text_field( $_POST['recipients'] ?? 'all' );

	if ( ! $subject || ! $body ) {
		wp_send_json_error( [ 'message' => __( 'Subject and body are required.', 'bluerabbit' ) ] );
	}

	global $wpdb;
	$adventure = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d AND adventure_status = 'publish'",
		$adventure_id
	) );

	if ( ! $adventure ) {
		wp_send_json_error( [ 'message' => __( 'Adventure not found.', 'bluerabbit' ) ] );
	}

	$sender_name  = sanitize_text_field( wp_unslash( $_POST['sender_name']  ?? '' ) );
	$sender_email = sanitize_email( wp_unslash( $_POST['sender_email'] ?? '' ) );
	if ( ! $sender_name || ! $sender_email ) {
		$sender_name  = $current_user->display_name;
		$sender_email = $current_user->user_email;
	}

	$from_name      = $sender_name . ' · ' . $adventure->adventure_title;
	$reply_to_email = $sender_email;
	$reply_to_name  = $sender_name;

	$mailer = new BR_Mailer();
	$mailer->set_sender_override( $from_name, $reply_to_email, $reply_to_name );
	$mailer->set_sender_id( $current_user->ID );

	$all_users = $mailer->get_adventure_users( $adventure_id );

	if ( $recipients !== 'all' ) {
		$player_ids = array_map( 'intval', explode( ',', $recipients ) );
		$player_ids = array_filter( $player_ids );
		if ( empty( $player_ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No recipients selected.', 'bluerabbit' ) ] );
		}
		$id_set = array_flip( $player_ids );
		$users  = array_values( array_filter( $all_users, function ( $u ) use ( $id_set ) {
			return isset( $id_set[ (int) $u['player_id'] ] );
		} ) );
	} else {
		$users = $all_users;
	}

	if ( empty( $users ) ) {
		wp_send_json_error( [ 'message' => __( 'No eligible recipients found.', 'bluerabbit' ) ] );
	}

	$result = $mailer->send_to_users( $users, $adventure_id, $subject, $body );

	$parts = [];
	if ( $result['queued'] && ! $result['sent'] && ! $result['failed'] ) {
		$parts[] = sprintf(
			_n( '%d email queued for delivery', '%d emails queued for delivery', $result['queued'], 'bluerabbit' ),
			$result['queued']
		);
		$parts[] = __( 'check the Send Log for status', 'bluerabbit' );
	} else {
		if ( $result['sent'] )   $parts[] = sprintf( _n( '%d email sent', '%d emails sent', $result['sent'], 'bluerabbit' ), $result['sent'] );
		if ( $result['failed'] ) $parts[] = sprintf( '%d failed', $result['failed'] );
		if ( $result['queued'] ) $parts[] = sprintf( '%d queued', $result['queued'] );
	}

	wp_send_json_success( [
		'sent'    => $result['sent'],
		'failed'  => $result['failed'],
		'queued'  => $result['queued'],
		'message' => implode( ' · ', $parts ),
	] );
}

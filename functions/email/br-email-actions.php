<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Retry campaign handler ────────────────────────────────────────────────────

add_action( 'admin_init', 'br_email_handle_retry' );
function br_email_handle_retry(): void {
	if ( ! empty( $_GET['br_retry_campaign'] ) ) {
		$cid = (int) $_GET['br_retry_campaign'];
		check_admin_referer( 'br_retry_' . $cid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$mailer = new BR_Mailer();
		$result = $mailer->retry_campaign( $cid );

		// Requeued items become "pending" again - go to the Missing tab and
		// use "Resume Sending" there to actually redrive them (see
		// send_next_batch()'s doc comment for why this isn't synchronous here).
		wp_redirect( add_query_arg( [
			'page'         => 'br_email_log',
			'campaign_id'  => $cid,
			'tab'          => 'missing',
			'br_requeued'  => $result['requeued'],
		], admin_url( 'admin.php' ) ) );
		exit;
	}

	if ( ! empty( $_GET['br_retry_single'] ) ) {
		$lid = (int) $_GET['br_retry_single'];
		check_admin_referer( 'br_retry_single_' . $lid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$mailer  = new BR_Mailer();
		$success = $mailer->retry_single( $lid );

		$back = (int) ( $_GET['br_back_campaign'] ?? 0 );
		$args = [
			'page'            => 'br_email_log',
			'br_retried'      => $success ? 1 : 0,
			'br_retry_failed' => $success ? 0 : 1,
		];
		if ( $back ) {
			$args['campaign_id'] = $back;
			$args['tab']         = 'failed';
		}
		wp_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}

// ── CSV download handler ──────────────────────────────────────────────────────

add_action( 'admin_init', 'br_email_handle_csv_download' );
function br_email_handle_csv_download(): void {
	global $wpdb;

	if ( ! empty( $_GET['br_email_csv_missing'] ) ) {
		$cid = (int) $_GET['br_email_csv_missing'];
		check_admin_referer( 'br_csv_missing_' . $cid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$mailer = new BR_Mailer();
		$rows   = array_map( function ( $u ) {
			return [ 'display_name' => $u->display_name, 'user_email' => $u->user_email ];
		}, $mailer->get_missing_recipients( $cid ) );

		br_email_output_csv( "missing-campaign-{$cid}.csv", $rows );
	}

	if ( ! empty( $_GET['br_email_csv_sent_campaign'] ) ) {
		$cid = (int) $_GET['br_email_csv_sent_campaign'];
		check_admin_referer( 'br_csv_sent_' . $cid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.display_name, u.user_email, l.sent_at
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'sent'
			  ORDER BY u.display_name",
			$cid
		) );

		br_email_output_csv( "sent-campaign-{$cid}.csv", $rows );
	}

	if ( ! empty( $_GET['br_email_csv_campaign'] ) ) {
		$cid = (int) $_GET['br_email_csv_campaign'];
		check_admin_referer( 'br_csv_' . $cid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT u.user_email, u.display_name, l.detail, l.sent_at
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'failed'
			  ORDER BY l.sent_at DESC",
			$cid
		) );

		br_email_output_csv( "failed-campaign-{$cid}.csv", $rows );
	}

	if ( ! empty( $_GET['br_email_csv_all_failed'] ) ) {
		check_admin_referer( 'br_csv_all_failed' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results(
			"SELECT u.user_email, u.display_name, l.subject, l.detail, l.sent_at, a.adventure_title
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			   LEFT JOIN {$wpdb->prefix}br_adventures a ON a.adventure_id = l.adventure_id
			  WHERE l.status = 'failed'
			  ORDER BY l.sent_at DESC"
		);

		br_email_output_csv( 'all-failed-emails.csv', $rows );
	}

	if ( ! empty( $_GET['br_email_csv_all_sent'] ) ) {
		check_admin_referer( 'br_csv_all_sent' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$rows = $wpdb->get_results(
			"SELECT u.user_email, u.display_name, l.subject, l.detail, l.sent_at, a.adventure_title
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			   LEFT JOIN {$wpdb->prefix}br_adventures a ON a.adventure_id = l.adventure_id
			  WHERE l.status = 'sent'
			  ORDER BY l.sent_at DESC"
		);

		br_email_output_csv( 'all-sent-emails.csv', $rows );
	}
}

function br_email_output_csv( string $filename, array $rows ): void {
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	$fp = fopen( 'php://output', 'w' );
	if ( ! empty( $rows ) ) {
		fputcsv( $fp, array_keys( (array) $rows[0] ) );
		foreach ( $rows as $row ) {
			fputcsv( $fp, (array) $row );
		}
	}
	fclose( $fp );
	exit;
}

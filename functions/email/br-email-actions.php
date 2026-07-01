<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Unsubscribe handler ───────────────────────────────────────────────────────

add_action( 'init', 'br_email_handle_unsubscribe' );
function br_email_handle_unsubscribe(): void {
	if ( empty( $_GET['br_email_unsub'] ) || empty( $_GET['uid'] ) ) return;

	$user_id = (int) $_GET['uid'];
	$token   = sanitize_text_field( $_GET['br_email_unsub'] );
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		wp_die( esc_html__( 'Invalid unsubscribe link.', 'bluerabbit' ), '', [ 'response' => 400 ] );
	}

	$expected = BR_Mailer::unsub_token( $user_id, $user->user_email );

	if ( ! hash_equals( $expected, $token ) ) {
		wp_die( esc_html__( 'Invalid unsubscribe token.', 'bluerabbit' ), '', [ 'response' => 403 ] );
	}

	update_user_meta( $user_id, 'br_email_optout', 1 );

	wp_die(
		'<p style="font-family:sans-serif;text-align:center;margin-top:60px;">'
		. esc_html__( 'You have been unsubscribed from BlueRabbit email notifications.', 'bluerabbit' )
		. '</p>',
		esc_html__( 'Unsubscribed', 'bluerabbit' ),
		[ 'response' => 200 ]
	);
}

// ── Retry campaign handler ────────────────────────────────────────────────────

add_action( 'admin_init', 'br_email_handle_retry' );
function br_email_handle_retry(): void {
	if ( ! empty( $_GET['br_retry_campaign'] ) ) {
		$cid = (int) $_GET['br_retry_campaign'];
		check_admin_referer( 'br_retry_' . $cid );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$mailer = new BR_Mailer();
		$result = $mailer->retry_campaign( $cid );

		// Go back to the campaign's Failed tab.
		$back = (int) ( $_GET['br_back_campaign'] ?? $cid );
		wp_redirect( add_query_arg( [
			'page'            => 'br_email_log',
			'campaign_id'     => $back,
			'tab'             => 'failed',
			'br_retried'      => $result['sent'],
			'br_retry_failed' => $result['failed'],
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

		$campaign = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_id FROM {$wpdb->prefix}br_email_campaigns WHERE campaign_id = %d",
			$cid
		) );
		if ( ! $campaign ) wp_die( 'Campaign not found.', 404 );

		$adv_id = (int) $campaign->adventure_id;

		// All enrolled players (no opt-out exclusion — show everyone).
		$enrolled_csv = $wpdb->get_col( $wpdb->prepare(
			"SELECT pa.player_id
			   FROM {$wpdb->prefix}br_player_adventure pa
			  WHERE pa.adventure_id            = %d
			    AND pa.player_adventure_status = 'in'",
			$adv_id
		) );

		$reached_csv = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT user_id FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d",
			$cid
		) );

		$missing_csv = array_values( array_diff( $enrolled_csv, $reached_csv ) );

		$rows = [];
		if ( ! empty( $missing_csv ) ) {
			// Get opted-out IDs to add a column in the CSV.
			$ph        = implode( ',', array_fill( 0, count( $missing_csv ), '%d' ) );
			$optout_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT user_id FROM {$wpdb->usermeta}
					  WHERE meta_key = 'br_email_optout' AND meta_value = '1'
					    AND user_id IN ( {$ph} )",
					...$missing_csv
				)
			);
			$optout_set = array_flip( $optout_ids );

			$user_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, display_name, user_email
					   FROM {$wpdb->users}
					  WHERE ID IN ( {$ph} )
					  ORDER BY display_name",
					...$missing_csv
				)
			);

			foreach ( $user_rows as $u ) {
				$rows[] = [
					'display_name' => $u->display_name,
					'user_email'   => $u->user_email,
					'unsubscribed' => isset( $optout_set[ $u->ID ] ) ? 'Yes' : 'No',
				];
			}
		}

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

	if ( ! empty( $_GET['br_email_csv_optout'] ) ) {
		check_admin_referer( 'br_csv_optout' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );

		$adv_id = (int) ( $_GET['adv_filter'] ?? 0 );

		if ( $adv_id ) {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT u.display_name, u.user_email
				   FROM {$wpdb->usermeta} m
				   JOIN {$wpdb->users} u ON u.ID = m.user_id
				   JOIN {$wpdb->prefix}br_player_adventure pa ON pa.player_id = m.user_id AND pa.adventure_id = %d
				  WHERE m.meta_key = 'br_email_optout' AND m.meta_value = '1'
				  ORDER BY u.display_name",
				$adv_id
			) );
			$filename = "unsubscribed-adventure-{$adv_id}.csv";
		} else {
			$rows = $wpdb->get_results(
				"SELECT u.display_name, u.user_email
				   FROM {$wpdb->usermeta} m
				   JOIN {$wpdb->users} u ON u.ID = m.user_id
				  WHERE m.meta_key = 'br_email_optout' AND m.meta_value = '1'
				  ORDER BY u.display_name"
			);
			$filename = 'unsubscribed-all.csv';
		}

		br_email_output_csv( $filename, $rows );
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

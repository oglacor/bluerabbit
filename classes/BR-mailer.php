<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Mailer {

	private array  $settings;
	private string $api_key;
	private string $service;
	private int    $sender_id   = 0;
	private int    $campaign_id = 0;

	private const CIPHER          = 'AES-256-CBC';
	private const POLL_BATCH_SIZE = 10;
	private const SEND_DELAY_MS   = 200;
	private const MAX_ATTEMPTS    = 3;
	private const RETRY_DELAY_MS  = [ 500, 1500 ];

	public function __construct() {
		$raw            = get_option( 'br_email_settings', [] );
		$this->settings = is_array( $raw ) ? $raw : [];
		$this->api_key  = isset( $this->settings['api_key'] )
			? self::decrypt_key( $this->settings['api_key'] )
			: '';
		$this->service  = $this->settings['email_service'] ?? 'resend';
	}

	// ── Key encryption ────────────────────────────────────────────────────────

	public static function encrypt_key( string $plain ): string {
		$key    = substr( hash( 'sha256', AUTH_KEY ), 0, 32 );
		$iv_len = openssl_cipher_iv_length( self::CIPHER );
		$iv     = random_bytes( $iv_len );
		$enc    = openssl_encrypt( $plain, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );
		return base64_encode( $iv . $enc );
	}

	public static function decrypt_key( string $stored ): string {
		if ( empty( $stored ) ) return '';
		$key     = substr( hash( 'sha256', AUTH_KEY ), 0, 32 );
		$decoded = base64_decode( $stored );
		$iv_len  = openssl_cipher_iv_length( self::CIPHER );
		$iv      = substr( $decoded, 0, $iv_len );
		$enc     = substr( $decoded, $iv_len );
		$plain   = openssl_decrypt( $enc, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );
		return $plain !== false ? $plain : '';
	}

	// ── Sender / Campaign context ─────────────────────────────────────────────

	public function set_sender_id( int $id ): void   { $this->sender_id = $id; }
	public function set_campaign_id( int $id ): void  { $this->campaign_id = $id; }

	public function set_sender_override( string $from_name, string $reply_to_email, string $reply_to_name = '' ): void {
		$this->settings['_from_name_override'] = $from_name;
		$this->settings['_reply_to_email']     = $reply_to_email;
		$this->settings['_reply_to_name']      = $reply_to_name ?: $from_name;
	}

	// ── Campaign catalog ──────────────────────────────────────────────────────

	private function create_campaign( int $adventure_id, string $subject, string $body, int $count ): int {
		global $wpdb;
		$wpdb->insert(
			"{$wpdb->prefix}br_email_campaigns",
			[
				'adventure_id'    => $adventure_id,
				'sender_id'       => $this->sender_id,
				'subject'         => mb_substr( $subject, 0, 255 ),
				'body'            => $body,
				'recipient_count' => $count,
				'created_at'      => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s', '%d', '%s' ]
		);
		return (int) $wpdb->insert_id;
	}

	public static function get_campaign( int $campaign_id ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}br_email_campaigns WHERE campaign_id = %d",
			$campaign_id
		) );
	}

	// Persists the exact recipient set for a campaign — the authoritative
	// "who is this for" list a group/guild-restricted send needs, so
	// "pending"/"missing" is never computed against the whole adventure roster.
	private function store_campaign_targets( int $campaign_id, array $users ): void {
		global $wpdb;
		$targets_table = "{$wpdb->prefix}br_email_campaign_targets";

		$values        = [];
		$placeholders  = [];
		foreach ( $users as $u ) {
			$player_id = (int) ( $u['player_id'] ?? 0 );
			if ( ! $player_id ) continue;
			$placeholders[] = '(%d, %d)';
			$values[]       = $campaign_id;
			$values[]       = $player_id;
		}
		if ( empty( $placeholders ) ) return;

		// Chunk the insert - a very large recipient list could otherwise
		// exceed max_allowed_packet / placeholder limits in one query.
		foreach ( array_chunk( $placeholders, 500 ) as $i => $chunk ) {
			$chunk_values = array_slice( $values, $i * 500 * 2, count( $chunk ) * 2 );
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$targets_table} (campaign_id, player_id) VALUES " . implode( ', ', $chunk ),
				$chunk_values
			) );
		}
	}

	// ── Adventure users ───────────────────────────────────────────────────────

	public function get_adventure_users( int $adventure_id ): array {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pa.player_id,
				        u.user_email,
				        u.display_name
				   FROM {$wpdb->prefix}br_player_adventure pa
				   JOIN {$wpdb->users} u ON u.ID = pa.player_id
				  WHERE pa.adventure_id             = %d
				    AND pa.player_adventure_status  = 'in'",
				$adventure_id
			),
			ARRAY_A
		);
		return $rows ?: [];
	}

	public function count_adventure_users( int $adventure_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				   FROM {$wpdb->prefix}br_player_adventure pa
				   JOIN {$wpdb->users} u ON u.ID = pa.player_id
				  WHERE pa.adventure_id            = %d
				    AND pa.player_adventure_status = 'in'",
				$adventure_id
			)
		);
	}

	// ── Template renderer ─────────────────────────────────────────────────────

	public function render_template( array $settings, string $subject, string $body, array $user ): string {
		$logo          = esc_url( $settings['logo_url'] ?? '' );
		$primary       = sanitize_hex_color( $settings['primary_color'] ?? '#1cc2eb' ) ?: '#1cc2eb';
		$accent        = sanitize_hex_color( $settings['accent_color']  ?? '#9f40e2' ) ?: '#9f40e2';
		$site_name     = esc_html( get_bloginfo( 'name' ) );
		$adventure     = esc_html( $settings['_adventure_name'] ?? '' );
		$display_name  = esc_html( $user['display_name'] ?? '' );
		$user_id       = (int) ( $user['player_id'] ?? 0 );
		$user_email    = $user['user_email'] ?? '';

		$merge_tags = [
			'name'           => $display_name,
			'adventure_name' => $adventure,
			'site_name'      => $site_name,
		];
		$body = preg_replace(
			'/&#123;&#123;(name|adventure_name|site_name)&#125;&#125;/i',
			'{{$1}}',
			$body
		);
		foreach ( $merge_tags as $tag => $value ) {
			$body = str_replace( '{{' . $tag . '}}', $value, $body );
		}

		$logo_html = $logo
			? '<img src="' . $logo . '" alt="' . $site_name . '" style="max-height:60px;max-width:240px;display:block;margin:0 auto;">'
			: '<span style="font-size:22px;font-weight:bold;color:#ffffff;">' . $site_name . '</span>';

		$subject_escaped = esc_html( $subject );

		return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{$subject_escaped}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5;">
  <tr><td align="center" style="padding:30px 10px;">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">
      <tr><td style="background-color:{$primary};padding:32px 30px;text-align:center;border-radius:8px 8px 0 0;">{$logo_html}</td></tr>
      <tr><td style="background-color:#ffffff;padding:40px 36px;color:#333333;font-size:16px;line-height:1.65;">{$body}</td></tr>
      <tr><td style="background-color:#ffffff;padding:0 36px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-top:1px solid #e8e8e8;"></td></tr></table></td></tr>
      <tr><td style="background-color:#ffffff;padding:24px 36px 32px;text-align:center;border-radius:0 0 8px 8px;">
        <p style="margin:0 0 8px;font-size:13px;color:#999999;">{$site_name}</p>
        <p style="margin:0;font-size:12px;color:#bbbbbb;">You are receiving this because you are enrolled in <strong>{$adventure}</strong>.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
	}

	// ── Single send ───────────────────────────────────────────────────────────

	public function send(
		string $to_email, string $to_name, string $subject, string $html,
		int $user_id = 0, int $adventure_id = 0
	): bool {
		$from_name  = $this->settings['_from_name_override']
			?? $this->settings['from_name']
			?? get_bloginfo( 'name' );
		$from_email = $this->settings['from_email'] ?? get_bloginfo( 'admin_email' );
		$reply_to_email = $this->settings['_reply_to_email'] ?? '';
		$reply_to_name  = $this->settings['_reply_to_name']  ?? '';

		if ( $this->service === 'sendgrid' ) {
			$endpoint = 'https://api.sendgrid.com/v3/mail/send';
			$payload  = [
				'personalizations' => [ [ 'to' => [ [ 'email' => $to_email, 'name' => $to_name ] ] ] ],
				'from'             => [ 'email' => $from_email, 'name' => $from_name ],
				'subject'          => $subject,
				'content'          => [ [ 'type' => 'text/html', 'value' => $html ] ],
			];
			if ( $reply_to_email ) {
				$payload['reply_to'] = [ 'email' => $reply_to_email, 'name' => $reply_to_name ];
			}
		} else {
			$endpoint = 'https://api.resend.com/emails';
			$payload  = [
				'from'    => "{$from_name} <{$from_email}>",
				'to'      => [ "{$to_name} <{$to_email}>" ],
				'subject' => $subject,
				'html'    => $html,
			];
			if ( $reply_to_email ) {
				$payload['reply_to'] = $reply_to_name
					? "{$reply_to_name} <{$reply_to_email}>"
					: $reply_to_email;
			}
		}

		$success = false;
		$detail  = '';
		$attempt = 0;

		// Retry transient failures (rate limits, brief network blips) inline
		// before giving up - most single-item failures in practice are
		// transient, so this meaningfully tightens the delivery guarantee
		// without depending on a human clicking "Retry" later.
		while ( $attempt < self::MAX_ATTEMPTS ) {
			$attempt++;

			$response = wp_remote_post( $endpoint, [
				'headers' => [
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				],
				'body'    => wp_json_encode( $payload ),
				'timeout' => 15,
			] );

			if ( is_wp_error( $response ) ) {
				$detail = 'WP_Error: ' . $response->get_error_message();
			} else {
				$code   = (int) wp_remote_retrieve_response_code( $response );
				$r_body = wp_remote_retrieve_body( $response );
				if ( $code < 300 ) {
					$success = true;
					$detail  = '';
					break;
				}
				$detail = "HTTP {$code}: {$r_body}";
			}

			if ( $attempt < self::MAX_ATTEMPTS ) {
				usleep( self::RETRY_DELAY_MS[ $attempt - 1 ] * 1000 );
			}
		}

		if ( ! $success && $attempt > 1 ) {
			$detail = "(failed after {$attempt} attempts) {$detail}";
		}

		$this->log_send( $user_id, $adventure_id, $subject, $success ? 'sent' : 'failed', $detail );
		return $success;
	}

	// ── Campaign creation (fast - no sending happens here) ────────────────────

	// Creates the campaign row and persists the exact recipient list, but
	// sends nothing - actual delivery is driven afterwards by repeated small
	// send_next_batch() calls from the browser (see functions/email/br-email-ajax.php).
	// This is the fix for the "170/234 delivered" bug: a single request/cron
	// tick trying to send a whole campaign (throttled + real network latency
	// per recipient) reliably exceeded real hosts' execution-time limits and
	// silently dropped whatever hadn't sent yet, with no record and no retry.
	public function start_campaign( array $users, int $adventure_id, string $subject, string $body ): array {
		if ( ! $this->campaign_id ) {
			$this->campaign_id = $this->create_campaign( $adventure_id, $subject, $body, count( $users ) );
		}
		if ( ! empty( $users ) ) {
			$this->store_campaign_targets( $this->campaign_id, $users );
		}
		return [ 'campaign_id' => $this->campaign_id, 'total' => count( $users ) ];
	}

	public function start_campaign_for_adventure( int $adventure_id, string $subject, string $body ): array {
		return $this->start_campaign( $this->get_adventure_users( $adventure_id ), $adventure_id, $subject, $body );
	}

	// ── Pending recipients (targets minus anything already logged) ───────────

	public function get_pending_batch( int $campaign_id, int $limit = self::POLL_BATCH_SIZE ): array {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT t.player_id, u.user_email, u.display_name
			   FROM {$wpdb->prefix}br_email_campaign_targets t
			   JOIN {$wpdb->users} u ON u.ID = t.player_id
			  WHERE t.campaign_id = %d
			    AND NOT EXISTS (
			        SELECT 1 FROM {$wpdb->prefix}br_email_log l
			         WHERE l.campaign_id = t.campaign_id AND l.user_id = t.player_id
			    )
			  ORDER BY u.display_name
			  LIMIT %d",
			$campaign_id, $limit
		), ARRAY_A );
		return $rows ?: [];
	}

	// Paginated version of get_pending_batch(), for the Missing tab / CSV
	// exports - same "targets minus logged" definition, just with an offset
	// and no implied "process these now" intent.
	public function get_missing_recipients( int $campaign_id, int $limit = 0, int $offset = 0 ): array {
		global $wpdb;
		$sql = "SELECT t.player_id, u.display_name, u.user_email
			   FROM {$wpdb->prefix}br_email_campaign_targets t
			   JOIN {$wpdb->users} u ON u.ID = t.player_id
			  WHERE t.campaign_id = %d
			    AND NOT EXISTS (
			        SELECT 1 FROM {$wpdb->prefix}br_email_log l
			         WHERE l.campaign_id = t.campaign_id AND l.user_id = t.player_id
			    )
			  ORDER BY u.display_name";
		$args = [ $campaign_id ];
		if ( $limit > 0 ) {
			$sql   .= ' LIMIT %d OFFSET %d';
			$args[] = $limit;
			$args[] = $offset;
		}
		return $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) ?: [];
	}

	public function count_pending( int $campaign_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
			   FROM {$wpdb->prefix}br_email_campaign_targets t
			  WHERE t.campaign_id = %d
			    AND NOT EXISTS (
			        SELECT 1 FROM {$wpdb->prefix}br_email_log l
			         WHERE l.campaign_id = t.campaign_id AND l.user_id = t.player_id
			    )",
			$campaign_id
		) );
	}

	// ── Send the next small batch for a campaign (called repeatedly by the
	//    browser's polling loop until 'remaining' is 0) ───────────────────────

	public function send_next_batch( int $campaign_id, int $limit = self::POLL_BATCH_SIZE ): array {
		$campaign = self::get_campaign( $campaign_id );
		if ( ! $campaign ) return [ 'sent' => 0, 'failed' => 0, 'remaining' => 0 ];

		global $wpdb;
		$adv = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
			$campaign->adventure_id
		) );
		$this->settings['_adventure_name'] = $adv ? $adv->adventure_title : '';
		$this->campaign_id = $campaign_id;
		if ( ! $this->sender_id ) $this->sender_id = (int) $campaign->sender_id;

		$batch = $this->get_pending_batch( $campaign_id, $limit );

		$sent = 0; $failed = 0;
		foreach ( $batch as $i => $user ) {
			$html = $this->render_template( $this->settings, $campaign->subject, $campaign->body, $user );
			$ok   = $this->send( $user['user_email'], $user['display_name'], $campaign->subject, $html, (int) $user['player_id'], (int) $campaign->adventure_id );
			$ok ? $sent++ : $failed++;
			if ( $i < count( $batch ) - 1 ) usleep( self::SEND_DELAY_MS * 1000 );
		}

		return [ 'sent' => $sent, 'failed' => $failed, 'remaining' => $this->count_pending( $campaign_id ) ];
	}

	// ── Retry failed emails in a campaign ─────────────────────────────────────

	// Clears the failed flag so these recipients become "pending" again -
	// they're picked back up by the normal send_next_batch() polling loop
	// (started via the "Resume Sending" button), not resent synchronously
	// here, since a campaign can have far more failures than one request
	// should try to resend at once.
	public function retry_campaign( int $campaign_id ): array {
		global $wpdb;

		$campaign = self::get_campaign( $campaign_id );
		if ( ! $campaign ) return [ 'requeued' => 0 ];

		$failed_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d AND status = 'failed'",
			$campaign_id
		) );
		if ( ! $failed_count ) return [ 'requeued' => 0 ];

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d AND status = 'failed'",
			$campaign_id
		) );

		return [ 'requeued' => $failed_count ];
	}

	public function retry_single( int $log_id ): bool {
		global $wpdb;

		$entry = $wpdb->get_row( $wpdb->prepare(
			"SELECT l.*, u.user_email, u.display_name
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.log_id = %d AND l.status = 'failed'",
			$log_id
		) );
		if ( ! $entry || ! $entry->campaign_id ) return false;

		$campaign = self::get_campaign( (int) $entry->campaign_id );
		if ( ! $campaign ) return false;

		$adv = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
			$campaign->adventure_id
		) );
		$this->settings['_adventure_name'] = $adv ? $adv->adventure_title : '';
		$this->campaign_id = (int) $entry->campaign_id;
		$this->sender_id   = (int) $campaign->sender_id;

		$user = [ 'player_id' => $entry->user_id, 'user_email' => $entry->user_email, 'display_name' => $entry->display_name ];
		$html = $this->render_template( $this->settings, $campaign->subject, $campaign->body, $user );

		$wpdb->delete( "{$wpdb->prefix}br_email_log", [ 'log_id' => $log_id ], [ '%d' ] );

		return $this->send( $entry->user_email, $entry->display_name, $campaign->subject, $html, (int) $entry->user_id, (int) $campaign->adventure_id );
	}

	// ── Logging ───────────────────────────────────────────────────────────────

	private function log_send( int $user_id, int $adventure_id, string $subject, string $status, string $detail = '' ): void {
		global $wpdb;
		$wpdb->insert(
			"{$wpdb->prefix}br_email_log",
			[
				'sender_id'    => $this->sender_id,
				'campaign_id'  => $this->campaign_id,
				'user_id'      => $user_id,
				'adventure_id' => $adventure_id,
				'subject'      => mb_substr( $subject, 0, 255 ),
				'status'       => $status,
				'detail'       => mb_substr( $detail, 0, 500 ),
				'sent_at'      => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
		);
	}

	// ── Unsubscribe token ─────────────────────────────────────────────────────

	public static function unsub_token( int $user_id, string $email ): string {
		return wp_hash( $user_id . ':' . $email . ':optout' );
	}
}

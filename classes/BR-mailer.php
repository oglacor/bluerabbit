<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Mailer {

	private array  $settings;
	private string $api_key;
	private string $service;
	private int    $sender_id   = 0;
	private int    $campaign_id = 0;

	private const CIPHER       = 'AES-256-CBC';
	private const RATE_PER_SEC = 4;
	private const BATCH_SIZE   = 100;

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

		$response = wp_remote_post( $endpoint, [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode( $payload ),
			'timeout' => 15,
		] );

		if ( is_wp_error( $response ) ) {
			$detail  = 'WP_Error: ' . $response->get_error_message();
			$success = false;
		} else {
			$code    = (int) wp_remote_retrieve_response_code( $response );
			$r_body  = wp_remote_retrieve_body( $response );
			$success = $code < 300;
			$detail  = $success ? '' : "HTTP {$code}: {$r_body}";
		}

		$this->log_send( $user_id, $adventure_id, $subject, $success ? 'sent' : 'failed', $detail );
		return $success;
	}

	// ── Bulk send (with rate limiting) ────────────────────────────────────────

	public function send_to_users( array $users, int $adventure_id, string $subject, string $body ): array {
		global $wpdb;

		$adv = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
			$adventure_id
		) );
		$this->settings['_adventure_name'] = $adv ? $adv->adventure_title : '';

		if ( ! $this->campaign_id ) {
			$this->campaign_id = $this->create_campaign( $adventure_id, $subject, $body, count( $users ) );
		}

		if ( empty( $users ) ) {
			return [ 'sent' => 0, 'failed' => 0, 'queued' => 0 ];
		}

		// Queue ALL recipients — never block the web request with bulk sending,
		// which would time out PHP and produce incomplete, inaccurate counts.
		$job_key = 'br_email_batch_' . uniqid( '', true );
		set_transient( $job_key, [
			'users'        => $users,
			'subject'      => $subject,
			'body'         => $body,
			'adventure_id' => $adventure_id,
			'settings'     => $this->settings,
			'sender_id'    => $this->sender_id,
			'campaign_id'  => $this->campaign_id,
		], 2 * HOUR_IN_SECONDS );
		wp_schedule_single_event( time(), 'br_email_batch_send', [ $job_key ] );
		spawn_cron();

		return [ 'sent' => 0, 'failed' => 0, 'queued' => count( $users ) ];
	}

	public function send_to_adventure( int $adventure_id, string $subject, string $body ): array {
		return $this->send_to_users( $this->get_adventure_users( $adventure_id ), $adventure_id, $subject, $body );
	}

	// ── Retry failed emails in a campaign ─────────────────────────────────────

	public function retry_campaign( int $campaign_id ): array {
		global $wpdb;

		$campaign = self::get_campaign( $campaign_id );
		if ( ! $campaign ) return [ 'sent' => 0, 'failed' => 0, 'queued' => 0 ];

		$failed_users = $wpdb->get_results( $wpdb->prepare(
			"SELECT l.log_id, l.user_id, u.user_email, u.display_name
			   FROM {$wpdb->prefix}br_email_log l
			   JOIN {$wpdb->users} u ON u.ID = l.user_id
			  WHERE l.campaign_id = %d AND l.status = 'failed'",
			$campaign_id
		), ARRAY_A );

		if ( empty( $failed_users ) ) return [ 'sent' => 0, 'failed' => 0, 'queued' => 0 ];

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}br_email_log WHERE campaign_id = %d AND status = 'failed'",
			$campaign_id
		) );

		$users = array_map( function ( $f ) {
			return [ 'player_id' => $f['user_id'], 'user_email' => $f['user_email'], 'display_name' => $f['display_name'] ];
		}, $failed_users );

		$this->campaign_id = $campaign_id;
		$this->sender_id   = (int) $campaign->sender_id;

		return $this->send_to_users( $users, (int) $campaign->adventure_id, $campaign->subject, $campaign->body );
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

	// ── Cron batch processor ──────────────────────────────────────────────────

	public static function process_batch( string $job_key ): void {
		$data = get_transient( $job_key );
		if ( ! $data || ! is_array( $data ) ) return;
		delete_transient( $job_key );

		$mailer              = new self();
		$mailer->settings    = $data['settings'];
		$mailer->api_key     = self::decrypt_key( $data['settings']['api_key'] ?? '' );
		$mailer->service     = $data['settings']['email_service'] ?? 'resend';
		$mailer->sender_id   = (int) ( $data['sender_id']   ?? 0 );
		$mailer->campaign_id = (int) ( $data['campaign_id'] ?? 0 );

		$users        = $data['users'];
		$subject      = $data['subject'];
		$body         = $data['body'];
		$adventure_id = (int) $data['adventure_id'];

		$batch = array_slice( $users, 0, self::BATCH_SIZE );
		$queue = array_slice( $users, self::BATCH_SIZE );

		global $wpdb;
		$adv = $wpdb->get_row( $wpdb->prepare(
			"SELECT adventure_title FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
			$adventure_id
		) );
		$mailer->settings['_adventure_name'] = $adv ? $adv->adventure_title : '';

		$rate_count = 0;
		$rate_start = microtime( true );

		foreach ( $batch as $user ) {
			$html = $mailer->render_template( $mailer->settings, $subject, $body, $user );
			$mailer->send( $user['user_email'], $user['display_name'], $subject, $html, (int) $user['player_id'], $adventure_id );

			$rate_count++;
			if ( $rate_count >= self::RATE_PER_SEC ) {
				$elapsed = microtime( true ) - $rate_start;
				if ( $elapsed < 1.0 ) {
					usleep( (int) ( ( 1.05 - $elapsed ) * 1_000_000 ) );
				}
				$rate_start = microtime( true );
				$rate_count = 0;
			}
		}

		if ( ! empty( $queue ) ) {
			$next_key = 'br_email_batch_' . uniqid( '', true );
			set_transient( $next_key, array_merge( $data, [ 'users' => $queue ] ), 2 * HOUR_IN_SECONDS );
			wp_schedule_single_event( time(), 'br_email_batch_send', [ $next_key ] );
			spawn_cron();
		}
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

add_action( 'br_email_batch_send', [ 'BR_Mailer', 'process_batch' ] );

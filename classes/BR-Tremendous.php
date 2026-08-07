<?php
class BR_Tremendous {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    const API_PROD    = 'https://api.tremendous.com/api/v2';
    const API_SANDBOX = 'https://testflight.tremendous.com/api/v2';

    // Set only by a bootstrap test script - short-circuits apiRequest() with a canned
    // response instead of a real HTTP call, so the whole sendReward() flow (fraud
    // checks, BLOO deduction, receipt email, order log row) can be verified against
    // the dev DB before real Tremendous credentials exist. No code path elsewhere
    // reads or sets this.
    public static $test_mode = false;
    public static $test_response = null;

    // ── Config ──────────────────────────────────────────────────────────

    public function getConfig($adventure_id) {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_tremendous_config WHERE adventure_id=%d AND config_status='active'",
            $adventure_id
        ));
        if (!$row) return null;
        // Decrypted secrets only ever live on these runtime properties - never echoed
        // back to the browser in any AJAX response.
        $row->api_key = $row->api_key_enc ? BR_Mailer::decrypt_key($row->api_key_enc) : '';
        $row->webhook_secret = !empty($row->webhook_secret_enc) ? BR_Mailer::decrypt_key($row->webhook_secret_enc) : '';
        return $row;
    }

    public function saveConfig($adventure_id, $data) {
        global $wpdb;
        $set = array(
            'sandbox_mode'      => empty($data['sandbox_mode']) ? 0 : 1,
            'funding_source_id' => $data['funding_source_id'] !== '' ? sanitize_text_field($data['funding_source_id']) : 'BALANCE',
            'campaign_id'       => !empty($data['campaign_id']) ? sanitize_text_field($data['campaign_id']) : null,
            'currency_code'     => !empty($data['currency_code']) ? sanitize_text_field($data['currency_code']) : 'EUR',
        );
        // Reuses BR_Mailer's own AES-256-CBC key-encryption pair (keyed off AUTH_KEY)
        // rather than inventing a second encrypted-secret scheme - this codebase's one
        // actual precedent for it. A blank submitted key means "leave the stored one
        // alone" (the UI never round-trips the decrypted key back into this field).
        if (!empty($data['api_key'])) {
            $set['api_key_enc'] = BR_Mailer::encrypt_key($data['api_key']);
        }
        if (!empty($data['webhook_secret'])) {
            $set['webhook_secret_enc'] = BR_Mailer::encrypt_key($data['webhook_secret']);
        }

        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT config_id FROM {$wpdb->prefix}br_tremendous_config WHERE adventure_id=%d",
            $adventure_id
        ));
        if ($existing_id) {
            return $wpdb->update("{$wpdb->prefix}br_tremendous_config", $set, array('adventure_id' => $adventure_id)) !== false;
        }
        $set['adventure_id'] = $adventure_id;
        return $wpdb->insert("{$wpdb->prefix}br_tremendous_config", $set) !== false;
    }

    // ── Tremendous API calls ──────────────────────────────────────────────

    private function apiRequest($method, $endpoint, $body, $api_key, $sandbox) {
        if (self::$test_mode) {
            return is_array(self::$test_response) ? self::$test_response : array('success' => false, 'http_code' => 0, 'data' => array());
        }
        $base = $sandbox ? self::API_SANDBOX : self::API_PROD;
        $args = array(
            'method'  => $method,
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
        );
        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }
        $response = wp_remote_request($base . $endpoint, $args);
        if (is_wp_error($response)) {
            return array('success' => false, 'http_code' => 0, 'data' => array('error' => $response->get_error_message()));
        }
        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return array('success' => ($code >= 200 && $code < 300), 'http_code' => $code, 'data' => is_array($data) ? $data : array());
    }

    // Tremendous puts a plain-language diagnosis in the response body, and it is usually
    // the whole answer - a production key sent to the sandbox endpoint comes back naming
    // both the key prefix and the endpoint it was sent to. Reporting "check the API key"
    // instead of that message turns a self-solving mistake into a support ticket.
    private function apiError($res) {
        foreach (array(
            $res['data']['errors']['message'] ?? null,
            $res['data']['error'] ?? null,
            $res['data']['message'] ?? null,
        ) as $candidate) {
            if (is_string($candidate) && $candidate !== '') return $candidate;
        }
        return '';
    }

    // Turns a stored api_response into one line a GM can act on. Players get a
    // deliberately vague message - they can't fix a 422 and shouldn't see API internals -
    // but that left the operator with a red badge and no way to find out why without
    // reading the table by hand. Tremendous names the offending fields in `payload`, so
    // "products: this or campaign_id must be set" survives all the way to the screen.
    public static function describeFailure($api_response) {
        $data = json_decode((string) $api_response, true);
        if (!is_array($data)) return '';

        $parts = array();
        foreach (array(
            $data['errors']['message'] ?? null,
            $data['error'] ?? null,
            $data['message'] ?? null,
        ) as $candidate) {
            if (is_string($candidate) && $candidate !== '') { $parts[] = $candidate; break; }
        }

        // payload is nested field => [messages], sometimes under a rewards[] list.
        $fields  = array();
        $walk    = function ($node, $prefix = '') use (&$walk, &$fields) {
            if (!is_array($node)) return;
            foreach ($node as $key => $value) {
                if (is_int($key)) { $walk($value, $prefix); continue; }
                if (is_array($value) && $value && is_string(reset($value))) {
                    $fields[] = $key . ': ' . implode(', ', array_filter($value, 'is_string'));
                } else {
                    $walk($value, $key);
                }
            }
        };
        $walk($data['errors']['payload'] ?? array());
        if ($fields) $parts[] = implode(' | ', array_unique($fields));

        return implode(' — ', $parts);
    }

    // Tremendous prefixes its keys by environment, so the commonest setup mistake - a
    // production key while Mode is Sandbox, or the reverse - is worth catching before
    // spending a round trip to be told the same thing.
    private function keyModeMismatch($api_key, $sandbox) {
        if ($sandbox && stripos($api_key, 'PROD_') === 0) {
            return __("That's a production API key, but Mode is set to Sandbox. Either paste a sandbox key or switch Mode to Production.", 'bluerabbit');
        }
        if (!$sandbox && stripos($api_key, 'TEST_') === 0) {
            return __("That's a sandbox API key, but Mode is set to Production. Either paste a production key or switch Mode to Sandbox.", 'bluerabbit');
        }
        return '';
    }

    // Returns ['ok'=>bool, 'items'=>array, 'message'=>string]. Callers that only want the
    // list can read 'items'; the AJAX handlers use 'message' so the operator sees what
    // Tremendous actually said.
    private function fetchList($adventure_id, $endpoint, $key) {
        $config = $this->getConfig($adventure_id);
        if (!$config || !$config->api_key) {
            return array('ok' => false, 'items' => array(),
                'message' => __('No API key saved yet - paste one and click Save Settings first.', 'bluerabbit'));
        }
        $mismatch = $this->keyModeMismatch($config->api_key, $config->sandbox_mode);
        if ($mismatch) {
            return array('ok' => false, 'items' => array(), 'message' => $mismatch);
        }
        $res = $this->apiRequest('GET', $endpoint, null, $config->api_key, $config->sandbox_mode);
        if (!$res['success']) {
            $detail = $this->apiError($res);
            return array('ok' => false, 'items' => array(), 'message' => $detail !== ''
                ? sprintf(__('Tremendous rejected the request (HTTP %1$d): %2$s', 'bluerabbit'), $res['http_code'], $detail)
                : sprintf(__("Couldn't reach Tremendous (HTTP %d).", 'bluerabbit'), $res['http_code']));
        }
        return array('ok' => true, 'items' => $res['data'][$key] ?? array(), 'message' => '');
    }

    public function getFundingSources($adventure_id) {
        $res = $this->fetchList($adventure_id, '/funding_sources', 'funding_sources');
        return $res['items'];
    }

    public function getCatalog($adventure_id) {
        $res = $this->fetchList($adventure_id, '/products', 'products');
        return $res['items'];
    }

    // ── Funding + balance ─────────────────────────────────────────────────

    // Not every funding source can pay for an API order. A real account carries both a
    // 'balance' source and an invoice source whose usage_permissions are empty - offering
    // the second one in the picker sets up a failure at send time for no reason.
    // $sources lets a caller that already has the list filter it without paying for a
    // second round trip.
    public function usableFundingSources($adventure_id, $sources = null) {
        if (!is_array($sources)) $sources = $this->getFundingSources($adventure_id);
        return array_values(array_filter($sources, function ($s) {
            $active = !isset($s['status']) || strtolower($s['status']) === 'active';
            // No permissions key at all: assume usable rather than hide a source we simply
            // don't understand. A key that IS present is authoritative - and an empty list
            // means no permissions, not "unknown", which is exactly what a real account's
            // invoice source looks like.
            $perms   = array_key_exists('usage_permissions', $s) ? $s['usage_permissions'] : null;
            $allowed = !is_array($perms) || in_array('api_orders', $perms, true);
            return $active && $allowed;
        }));
    }

    // The config stores the literal 'BALANCE' as its default, but an order has to name a
    // real funding source id. Resolve it at send time so an adventure that was never
    // taken through Test Connection still pays from the account balance.
    private function resolveFundingSourceId($adventure_id, $configured) {
        if ($configured && strtoupper($configured) !== 'BALANCE') return $configured;
        foreach ($this->usableFundingSources($adventure_id) as $s) {
            if (strtolower($s['method'] ?? '') === 'balance') return $s['id'];
        }
        return $configured;
    }

    // Returns ['known'=>bool, 'amount'=>float, 'currency'=>string, 'id'=>string] for the
    // configured source. Cached briefly: this is consulted on every purchase, and a live
    // round trip per gift card would put Tremendous's latency inside the player's click.
    // The cache is only ever used to REFUSE early - a stale value can never authorise a
    // send, because Tremendous re-checks the balance itself and we surface what it says.
    public function fundingBalance($adventure_id, $use_cache = true) {
        $key = 'br_tremendous_balance_' . (int) $adventure_id;
        if ($use_cache) {
            $cached = get_transient($key);
            if (is_array($cached)) return $cached;
        }

        $out = array('known' => false, 'amount' => 0.0, 'currency' => '', 'id' => '');
        $config = $this->getConfig($adventure_id);
        if (!$config) return $out;

        $wanted = $config->funding_source_id;
        foreach ($this->usableFundingSources($adventure_id) as $s) {
            $is_match = (strtoupper((string) $wanted) === 'BALANCE')
                ? strtolower($s['method'] ?? '') === 'balance'
                : ($s['id'] ?? '') === $wanted;
            if (!$is_match) continue;

            // available_amount is the same figure as available_cents/100; prefer the
            // explicit one and fall back rather than assuming either is present.
            $meta = $s['meta'] ?? array();
            if (isset($meta['available_amount'])) {
                $out['amount'] = (float) $meta['available_amount'];
                $out['known']  = true;
            } elseif (isset($meta['available_cents'])) {
                $out['amount'] = ((float) $meta['available_cents']) / 100;
                $out['known']  = true;
            }
            $out['currency'] = $meta['currency_code'] ?? '';
            $out['id']       = $s['id'] ?? '';
            break;
        }

        set_transient($key, $out, 5 * MINUTE_IN_SECONDS);
        return $out;
    }

    public function forgetBalance($adventure_id) {
        delete_transient('br_tremendous_balance_' . (int) $adventure_id);
    }

    // Three outcomes the old version collapsed into a single "couldn't connect": not
    // configured, rejected by Tremendous, and connected-but-no-funding-sources. Only the
    // middle one is a credentials problem, and an empty account reported as a connection
    // failure sends you back to re-checking a key that was right all along.
    public function testConnection($adventure_id) {
        $res = $this->fetchList($adventure_id, '/funding_sources', 'funding_sources');
        if (!$res['ok']) {
            return array('success' => false, 'funding_sources' => array(), 'color' => 'red', 'message' => $res['message']);
        }
        if (!$res['items']) {
            return array('success' => true, 'funding_sources' => array(), 'color' => 'blue',
                'message' => __('Connected - but this Tremendous account has no funding sources yet. Add one in your Tremendous dashboard before configuring a gift-card item.', 'bluerabbit'));
        }

        // A real account also carries sources that cannot pay for an API order - an
        // invoice source with empty usage_permissions, for one. Offering those in the
        // picker only sets up a failure at send time.
        $usable = $this->usableFundingSources($adventure_id, $res['items']);
        if (!$usable) {
            return array('success' => false, 'funding_sources' => array(), 'color' => 'red',
                'message' => sprintf(__('Connected, but none of this account\'s %d funding sources can pay for API orders. Check their permissions in your Tremendous dashboard.', 'bluerabbit'), count($res['items'])));
        }

        $hidden = count($res['items']) - count($usable);
        return array('success' => true, 'funding_sources' => $usable, 'color' => 'green',
            'message' => sprintf(_n('Connected! %d usable funding source found.', 'Connected! %d usable funding sources found.', count($usable), 'bluerabbit'), count($usable))
                . ($hidden > 0 ? ' ' . sprintf(_n('(%d other cannot pay for API orders.)', '(%d others cannot pay for API orders.)', $hidden, 'bluerabbit'), $hidden) : ''));
    }

    // ── Webhooks ──────────────────────────────────────────────────────────

    const WEBHOOK_ROUTE = 'bluerabbit/v1';
    const WEBHOOK_PATH  = '/tremendous-webhook';

    public static function webhookUrl() {
        return rest_url(self::WEBHOOK_ROUTE . self::WEBHOOK_PATH);
    }

    // Tremendous signs each delivery with the secret it returns once, at webhook-creation
    // time. Compared with hash_equals because a timing-safe compare is the entire point of
    // an HMAC: a byte-at-a-time == would leak the expected digest to anyone allowed to
    // POST here, and this endpoint is public by necessity.
    private function signatureMatches($raw_body, $secret, $provided) {
        if ($secret === '' || $provided === '') return false;
        // Accept both a bare hex digest and the "sha256=..." form; which one arrives is a
        // detail of the sender, not something worth rejecting a real event over.
        $provided = trim($provided);
        if (stripos($provided, 'sha256=') === 0) $provided = substr($provided, 7);
        return hash_equals(hash_hmac('sha256', $raw_body, $secret), strtolower($provided));
    }

    // Maps an event name onto a delivery state without hard-coding Tremendous's exact
    // vocabulary. The names are matched by substring on purpose: this integration cannot
    // verify the full event list without a live webhook, and an unrecognised event is
    // recorded and flagged rather than guessed at or dropped.
    private function deliveryStateFor($event) {
        $e = strtoupper((string) $event);
        if (strpos($e, 'FAIL') !== false)                                   return 'failed';
        if (strpos($e, 'CANCEL') !== false)                                 return 'canceled';
        if (strpos($e, 'REFUND') !== false)                                 return 'refunded';
        if (strpos($e, 'REDEEM') !== false)                                 return 'redeemed';
        if (strpos($e, 'DELIVER') !== false || strpos($e, 'SENT') !== false) return 'delivered';
        return '';
    }

    // Pull the first value for any of $keys anywhere in a nested payload. Tremendous nests
    // the reward differently per event type and the shapes are not all known here, so this
    // searches rather than assuming a path.
    private function digUp($node, array $keys) {
        if (!is_array($node)) return null;
        foreach ($keys as $k) {
            if (isset($node[$k]) && is_scalar($node[$k]) && $node[$k] !== '') return (string) $node[$k];
        }
        foreach ($node as $value) {
            if (is_array($value)) {
                $found = $this->digUp($value, $keys);
                if ($found !== null) return $found;
            }
        }
        return null;
    }

    // Public endpoint. Always 200s once the body is stored: a webhook sender that gets an
    // error retries, and retrying will not fix an event we simply don't recognise. What
    // matters is that every delivery is durable and visible in br_tremendous_events.
    public function handleWebhook($request) {
        global $wpdb;

        $raw     = $request->get_body();
        $data    = json_decode($raw, true);
        $data    = is_array($data) ? $data : array();
        $event   = $this->digUp($data, array('event', 'event_type', 'type'));
        $ext_id  = $this->digUp($data, array('external_id'));
        $trem_id = $this->digUp($data, array('reward_id', 'order_id', 'id'));

        $order = null;
        if ($ext_id) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}br_tremendous_orders WHERE tremendous_external_id=%s", $ext_id));
        }
        if (!$order && $trem_id) {
            // Could be either identifier depending on which object the event is about.
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}br_tremendous_orders WHERE tremendous_reward_id=%s OR tremendous_order_id=%s",
                $trem_id, $trem_id));
        }

        // The signing secret is per-adventure, and the adventure is only known once the
        // order is matched. An event we cannot attribute is therefore never trusted.
        $valid = false;
        if ($order) {
            $config = $this->getConfig($order->adventure_id);
            $secret = ($config && !empty($config->webhook_secret)) ? $config->webhook_secret : '';
            foreach (array('tremendous-webhook-signature', 'x-tremendous-signature', 'x-signature') as $header) {
                if ($this->signatureMatches($raw, $secret, (string) $request->get_header($header))) { $valid = true; break; }
            }
        }

        $state   = $this->deliveryStateFor($event);
        $applied = false;
        $note    = '';

        if (!$order) {
            $note = 'no matching order';
        } elseif (!$valid) {
            // Recorded, never acted on. Without a verified signature anyone who finds this
            // URL could mark rewards delivered or refunded at will.
            $note = 'signature not verified - event stored but not applied';
        } elseif (!$state) {
            $note = 'unrecognised event type';
        } else {
            $wpdb->update("{$wpdb->prefix}br_tremendous_orders", array(
                'delivery_status' => $state,
                'last_event'      => (string) $event,
                'last_event_at'   => current_time('mysql'),
            ), array('order_id' => $order->order_id));
            $applied = true;
        }

        $wpdb->insert("{$wpdb->prefix}br_tremendous_events", array(
            'event_type'       => $event ? substr((string) $event, 0, 60) : null,
            'tremendous_id'    => $trem_id,
            'external_id'      => $ext_id,
            'matched_order_id' => $order ? $order->order_id : null,
            'signature_valid'  => $valid ? 1 : 0,
            'applied'          => $applied ? 1 : 0,
            'note'             => $note ?: null,
            'payload'          => $raw,
        ));

        return new WP_REST_Response(array('received' => true, 'applied' => $applied), 200);
    }

    // ── The main send ─────────────────────────────────────────────────────

    // Returns ['success'=>bool, 'message'=>string, 'order_id'=>string|null, 'error'=>string|null].
    // Caller (buyItem()/assignItem()) has already validated stock/level/window/conditions/
    // cap and resolved which player/item/adventure this is for - this method owns
    // everything from "is this actually configured and enabled" through the external
    // send and its own transaction/audit bookkeeping.
    public function sendReward($player_id, $item_id, $adventure_id) {
        global $wpdb;

        $config = $this->getConfig($adventure_id);
        if (!$config || !$config->api_key) {
            return array('success' => false, 'error' => 'not_configured', 'message' => __('Gift card rewards are not configured for this adventure.', 'bluerabbit'));
        }

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=%d AND item_status='publish'",
            $item_id
        ));
        if (!$item || !$item->item_tremendous_enabled || !$item->item_tremendous_amount) {
            return array('success' => false, 'error' => 'not_enabled', 'message' => __('This item is not a configured gift card reward.', 'bluerabbit'));
        }

        // Tremendous requires EITHER an explicit product list on the reward OR a campaign
        // to choose from, and rejects an order carrying neither with a 422. Resolved here,
        // before the order row and the stock reservation exist, because failing later
        // meant a misconfigured item burned a slot and left a 'failed' row behind on every
        // single attempt - and told the player only "something went wrong".
        $products = array();
        if ($item->item_tremendous_products) {
            $decoded = json_decode($item->item_tremendous_products, true);
            if (is_array($decoded)) $products = $decoded;
        }
        if (!$products && empty($config->campaign_id)) {
            return array('success' => false, 'error' => 'no_products', 'message' => __("This gift card isn't finished being set up - no Tremendous products or campaign have been chosen for it. Please contact your administrator.", 'bluerabbit'));
        }

        // Refuse a card the account cannot pay for, before the order row and the stock
        // reservation exist. Tremendous would reject it anyway; the difference is that a
        // campaign running out of funds otherwise fails one player at a time, each of them
        // burning a reservation and leaving a 'failed' row, with nobody told why.
        $balance = $this->fundingBalance($adventure_id);
        if ($balance['known'] && (float) $item->item_tremendous_amount > $balance['amount']) {
            // Cached figure - re-check live before refusing, so a top-up that happened in
            // the last few minutes doesn't block a legitimate purchase.
            $balance = $this->fundingBalance($adventure_id, false);
        }
        if ($balance['known'] && (float) $item->item_tremendous_amount > $balance['amount']) {
            return array('success' => false, 'error' => 'insufficient_funds',
                'message' => __('This reward is temporarily unavailable - the gift card account needs topping up. Please contact your administrator.', 'bluerabbit'),
                'admin_detail' => sprintf(
                    __('Tremendous balance is %1$s %2$s but this card costs %3$s.', 'bluerabbit'),
                    number_format($balance['amount'], 2), $balance['currency'], number_format((float) $item->item_tremendous_amount, 2)
                ));
        }

        // Always the player's own WP account email - no override parameter exists on
        // this method at all, so "send to the actual player" isn't just a UI default,
        // it's structurally the only option.
        $user = get_userdata($player_id);
        if (!$user || !$user->user_email) {
            return array('success' => false, 'error' => 'no_email', 'message' => __('This player has no email on file.', 'bluerabbit'));
        }
        $recipient_email = $user->user_email;
        $recipient_name  = $user->display_name ?: $user->user_login;

        $external_id = "br_{$player_id}_{$item_id}_{$adventure_id}";

        // Fraud check 1 + reuse-on-retry: a genuinely 'sent' row blocks permanently.
        // A prior 'pending'/'failed'/'duplicate_blocked' row (a previous attempt that
        // never actually reached Tremendous, or failed there) is reused rather than
        // left to collide with the UNIQUE key on a legitimate retry - failures stay
        // visible in the order log instead of being deleted, but don't permanently
        // lock the player out.
        $existing_order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_tremendous_orders WHERE tremendous_external_id=%s",
            $external_id
        ));
        if ($existing_order) {
            if ($existing_order->status === 'sent') {
                return array('success' => false, 'error' => 'already_redeemed', 'message' => __('You have already redeemed this reward.', 'bluerabbit'));
            }
            $order_row_id = $existing_order->order_id;
            $wpdb->update("{$wpdb->prefix}br_tremendous_orders",
                array('status' => 'pending', 'recipient_email' => $recipient_email, 'amount' => $item->item_tremendous_amount, 'currency_code' => $config->currency_code, 'sandbox' => $config->sandbox_mode),
                array('order_id' => $order_row_id)
            );
        } else {
            // Fraud check 2: the UNIQUE key on tremendous_external_id is the real lock -
            // if two requests somehow both reach this INSERT for the same player+item+
            // adventure, only one can succeed.
            $inserted = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}br_tremendous_orders (player_id, adventure_id, item_id, tremendous_external_id, recipient_email, amount, currency_code, status, sandbox)
                VALUES (%d, %d, %d, %s, %s, %f, %s, 'pending', %d)",
                $player_id, $adventure_id, $item_id, $external_id, $recipient_email, $item->item_tremendous_amount, $config->currency_code, $config->sandbox_mode
            ));
            if ($inserted === false) {
                return array('success' => false, 'error' => 'already_redeemed', 'message' => __('You have already redeemed this reward.', 'bluerabbit'));
            }
            $order_row_id = $wpdb->insert_id;
        }

        // Claim the item's stock/category/per-player slot BEFORE calling Tremendous -
        // this is a real external send, so the race that actually matters is here, not
        // just the bookkeeping. Same trnx_lock_key mechanism buyItem() uses (see
        // br_migrate_transaction_lock_schema() in functions.php): if two different
        // players are racing the last unit of a Tremendous-enabled item, only one of
        // these reservation INSERTs can succeed, and the loser never reaches the API
        // at all - so at most one real gift card can ever go out for that slot.
        $alltrnx = $wpdb->get_results($wpdb->prepare(
            "SELECT trnx_id FROM {$wpdb->prefix}br_transactions WHERE object_id=%d AND (trnx_type='consumable' OR trnx_type='gift-card') AND trnx_status='publish' AND adventure_id=%d",
            $item_id, $adventure_id
        ));
        if ($item->item_category_id) {
            $trnxs = $wpdb->get_results($wpdb->prepare(
                "SELECT a.trnx_id FROM {$wpdb->prefix}br_transactions a JOIN {$wpdb->prefix}br_items b ON a.object_id=b.item_id
                WHERE a.adventure_id=%d AND a.player_id=%d AND b.item_category_id=%d AND a.trnx_status='publish'",
                $adventure_id, $player_id, $item->item_category_id
            ));
        } else {
            $trnxs = $wpdb->get_results($wpdb->prepare(
                "SELECT trnx_id FROM {$wpdb->prefix}br_transactions WHERE adventure_id=%d AND player_id=%d AND object_id=%d AND trnx_status='publish'",
                $adventure_id, $player_id, $item_id
            ));
        }
        if ($item->item_stock > 0 && $item->item_stock < 99999) {
            $lock_key = "stock_{$item_id}_{$adventure_id}_" . (count($alltrnx) + 1);
        } elseif ($item->item_player_max > 0) {
            $scope = $item->item_category_id ? "cat{$item->item_category_id}" : "item{$item_id}";
            $lock_key = "cap_{$player_id}_{$scope}_{$adventure_id}_" . (count($trnxs) + 1);
        } else {
            $lock_key = "buy_{$player_id}_{$item_id}_{$adventure_id}_" . (count($trnxs) + 1);
        }

        $today = current_time('mysql');
        $trnx_inserted = $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified, trnx_lock_key)
            VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s)",
            $player_id, $adventure_id, $item_id, $player_id, $item->item_cost, $item->item_type, $today, $today, $lock_key
        ));
        if ($trnx_inserted === false) {
            $wpdb->update("{$wpdb->prefix}br_tremendous_orders", array('status' => 'failed', 'api_response' => 'Sold out before Tremendous was contacted.'), array('order_id' => $order_row_id));
            return array('success' => false, 'error' => 'sold_out', 'message' => __('No More Items Left', 'bluerabbit'));
        }
        $trnx_id = $wpdb->insert_id;

        $reward = array(
            'value' => array(
                'denomination'  => (float) $item->item_tremendous_amount,
                'currency_code' => $config->currency_code,
            ),
            'delivery'  => array('method' => 'EMAIL'),
            'recipient' => array('name' => $recipient_name, 'email' => $recipient_email),
        );
        // Send whichever of the two Tremendous accepts - never an empty products array,
        // which reads to the API as "products not set" and produces the same 422 as
        // omitting it, only less obviously.
        if ($products) {
            $reward['products'] = $products;
        } else {
            $reward['campaign_id'] = $config->campaign_id;
        }
        $order_payload = array(
            'payment'     => array('funding_source_id' => $this->resolveFundingSourceId($adventure_id, $config->funding_source_id)),
            'rewards'     => array($reward),
            'external_id' => $external_id,
        );

        $res = $this->apiRequest('POST', '/orders', $order_payload, $config->api_key, $config->sandbox_mode);

        // Money moved either way - a success spent it, a failure may have been caused by
        // not having it. Neither leaves the cached figure worth trusting.
        $this->forgetBalance($adventure_id);

        if ($res['success']) {
            // Confirmed against a real response: the order carries `rewards` (an ARRAY),
            // not `reward`. The previous singular lookup always missed and fell back to
            // the order id, which then got used as a reward id - so the dashboard link in
            // the orders log pointed at a reward that does not exist. They are two
            // different identifiers and both are worth keeping.
            $order = $res['data']['order'] ?? array();
            $tremendous_order_id  = $order['id'] ?? null;
            $tremendous_reward_id = $order['rewards'][0]['id'] ?? ($order['reward']['id'] ?? null);
            $wpdb->update("{$wpdb->prefix}br_tremendous_orders",
                array('status' => 'sent', 'tremendous_order_id' => $tremendous_order_id, 'tremendous_reward_id' => $tremendous_reward_id, 'api_response' => wp_json_encode($res['data'])),
                array('order_id' => $order_row_id)
            );

            BR_Activity::instance()->logActivity($adventure_id, 'purchase', 'tremendous-item', "$item->item_type", $item_id, $player_id);
            BR_Player::instance()->resetPlayer($adventure_id, $player_id);
            $this->sendReceiptEmail($recipient_email, $recipient_name, $player_id, $adventure_id, $item, $config);

            return array('success' => true, 'message' => __('Your gift card is on its way!', 'bluerabbit'), 'order_id' => $tremendous_order_id);
        }

        // Failed/duplicate at Tremendous - tear down the speculative reservation so no
        // BLOO stays deducted and no stock/cap slot stays consumed for a reward that
        // was never actually sent. The order row itself stays (status updated), so the
        // GM-facing log still shows what happened.
        $wpdb->delete("{$wpdb->prefix}br_transactions", array('trnx_id' => $trnx_id));
        $status = ($res['http_code'] == 409) ? 'duplicate_blocked' : 'failed';
        $wpdb->update("{$wpdb->prefix}br_tremendous_orders",
            array('status' => $status, 'api_response' => wp_json_encode($res['data'])),
            array('order_id' => $order_row_id)
        );

        if ($status === 'duplicate_blocked') {
            return array('success' => false, 'error' => 'already_redeemed', 'message' => __('You have already redeemed this reward.', 'bluerabbit'));
        }
        return array('success' => false, 'error' => 'send_failed', 'message' => __('Something went wrong sending your reward. Please contact your administrator.', 'bluerabbit'));
    }

    // Reuses BR_Mailer entirely (render_template for the branded HTML shell, send()
    // for the single recipient + its own logging to br_email_log) - not a new mail
    // pathway. Deliberately contains no redemption link or card code of its own -
    // that's exclusively Tremendous's own delivery email, sent separately to the same
    // address; this is only ever a purchase receipt.
    private function sendReceiptEmail($to_email, $to_name, $player_id, $adventure_id, $item, $config) {
        $mailer = new BR_Mailer();
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $label = $item->item_tremendous_label ?: $item->item_name;
        $amount_display = BR_Utils::instance()->toMoney((float) $item->item_tremendous_amount, $this->currencySymbol($config->currency_code), 2);

        $subject = sprintf(__('Your gift card purchase receipt - %s', 'bluerabbit'), get_bloginfo('name'));
        $body = '<p>' . sprintf(__('You redeemed %1$s for %2$s.', 'bluerabbit'), '<strong>' . esc_html($label) . '</strong>', '<strong>' . esc_html($amount_display) . '</strong>') . '</p>'
            . '<p>' . __('A separate email from Tremendous, sent to this same address, will arrive shortly with your actual gift card code and redemption instructions - that email is where you\'ll claim your reward.', 'bluerabbit') . '</p>'
            . '<p>' . __('This message is just your BlueRabbit purchase receipt for your records.', 'bluerabbit') . '</p>';

        $settings = get_option('br_email_settings', array());
        $settings = is_array($settings) ? $settings : array();
        $settings['_adventure_name'] = $adventure ? $adventure->adventure_title : '';

        $html = $mailer->render_template($settings, $subject, $body, array(
            'display_name' => $to_name,
            'player_id'    => $player_id,
            'user_email'   => $to_email,
        ));
        $mailer->send($to_email, $to_name, $subject, $html, $player_id, $adventure_id);
    }

    private function currencySymbol($code) {
        $symbols = array('EUR' => '€', 'USD' => '$', 'GBP' => '£');
        return $symbols[$code] ?? ($code . ' ');
    }

    // ── AJAX handlers ─────────────────────────────────────────────────────

    private function currentUserIsGMFor($adventure) {
        global $current_user;
        if (isset($current_user->roles[0]) && $current_user->roles[0] === 'administrator') return true;
        if (!$adventure) return false;
        if ($adventure->adventure_owner == $current_user->ID) return true;
        return in_array($adventure->player_adventure_role, array('gm', 'npc'));
    }

    public function ajax_save_config() {
        $current_user = wp_get_current_user();
        $data = array('success' => false);
        $notification = new Notification();

        if (!wp_verify_nonce($_POST['nonce'], 'br_update_adventure_nonce')) {
            $data['message'] = $notification->pop(__("Security check failed, please reload the page and try again.", 'bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $adventure_id = intval($_POST['adventure_id']);
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if (!$adventure || !$this->currentUserIsGMFor($adventure)) {
            $data['message'] = $notification->pop(__("You don't have permission to do this.", 'bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $ok = $this->saveConfig($adventure_id, array(
            'api_key'           => $_POST['api_key'] ?? '',
            'webhook_secret'    => $_POST['webhook_secret'] ?? '',
            'sandbox_mode'      => $_POST['sandbox_mode'] ?? 0,
            'funding_source_id' => $_POST['funding_source_id'] ?? '',
            'campaign_id'       => $_POST['campaign_id'] ?? '',
            'currency_code'     => $_POST['currency_code'] ?? '',
        ));
        // Mode, funding source or key may all have changed - the cached figure belonged
        // to the old configuration.
        $this->forgetBalance($adventure_id);

        $data['success'] = $ok;
        $data['message'] = $ok
            ? $notification->pop(__('Tremendous settings saved!', 'bluerabbit'), 'green', 'check')
            : $notification->pop(__("Couldn't save Tremendous settings.", 'bluerabbit'), 'red', 'cancel');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function ajax_test_connection() {
        $current_user = wp_get_current_user();
        $data = array('success' => false);
        $notification = new Notification();

        if (!wp_verify_nonce($_POST['nonce'], 'br_update_adventure_nonce')) {
            $data['message'] = $notification->pop(__("Security check failed, please reload the page and try again.", 'bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $adventure_id = intval($_POST['adventure_id']);
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if (!$adventure || !$this->currentUserIsGMFor($adventure)) {
            $data['message'] = $notification->pop(__("You don't have permission to do this.", 'bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $result = $this->testConnection($adventure_id);
        $data['success'] = $result['success'];
        $data['funding_sources'] = $result['funding_sources'];
        $data['message'] = $notification->pop($result['message'], $result['color'], $result['success'] ? 'check' : 'cancel');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function ajax_get_catalog() {
        $current_user = wp_get_current_user();
        $data = array('success' => false);
        $notification = new Notification();

        $adventure_id = intval($_POST['adventure_id']);
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if (!wp_verify_nonce($_POST['nonce'], 'br_update_item_nonce') || !$adventure || !$this->currentUserIsGMFor($adventure)) {
            $data['message'] = $notification->pop(__("You don't have permission to do this.", 'bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $res = $this->fetchList($adventure_id, '/products', 'products');
        $data['success']  = $res['ok'];
        $data['products'] = $res['items'];
        // Same reasoning as the connection test: show what Tremendous said rather than a
        // generic "check the connection" that sends the operator to the wrong screen.
        if (!$res['ok']) $data['error'] = $res['message'];
        echo json_encode($data);
        die();
    }
}

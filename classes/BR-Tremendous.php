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

    const API_PROD    = 'https://www.tremendousrewards.com/api/v2';
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
        // Decrypted key only ever lives on this runtime property - never echoed back
        // to the browser in any AJAX response.
        $row->api_key = $row->api_key_enc ? BR_Mailer::decrypt_key($row->api_key_enc) : '';
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

    public function getFundingSources($adventure_id) {
        $config = $this->getConfig($adventure_id);
        if (!$config || !$config->api_key) return array();
        $res = $this->apiRequest('GET', '/funding_sources', null, $config->api_key, $config->sandbox_mode);
        return $res['success'] ? ($res['data']['funding_sources'] ?? array()) : array();
    }

    public function getCatalog($adventure_id) {
        $config = $this->getConfig($adventure_id);
        if (!$config || !$config->api_key) return array();
        $res = $this->apiRequest('GET', '/products', null, $config->api_key, $config->sandbox_mode);
        return $res['success'] ? ($res['data']['products'] ?? array()) : array();
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

        $products = array();
        if ($item->item_tremendous_products) {
            $decoded = json_decode($item->item_tremendous_products, true);
            if (is_array($decoded)) $products = $decoded;
        }

        $reward = array(
            'value' => array(
                'denomination'  => (float) $item->item_tremendous_amount,
                'currency_code' => $config->currency_code,
            ),
            'delivery'  => array('method' => 'EMAIL'),
            'recipient' => array('name' => $recipient_name, 'email' => $recipient_email),
            'products'  => $products,
        );
        if (!empty($config->campaign_id)) {
            $reward['campaign_id'] = $config->campaign_id;
        }
        $order_payload = array(
            'payment'     => array('funding_source_id' => $config->funding_source_id),
            'rewards'     => array($reward),
            'external_id' => $external_id,
        );

        $res = $this->apiRequest('POST', '/orders', $order_payload, $config->api_key, $config->sandbox_mode);

        if ($res['success']) {
            $tremendous_order_id = $res['data']['order']['reward']['id'] ?? ($res['data']['order']['id'] ?? null);
            $wpdb->update("{$wpdb->prefix}br_tremendous_orders",
                array('status' => 'sent', 'tremendous_order_id' => $tremendous_order_id, 'api_response' => wp_json_encode($res['data'])),
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
            'sandbox_mode'      => $_POST['sandbox_mode'] ?? 0,
            'funding_source_id' => $_POST['funding_source_id'] ?? '',
            'campaign_id'       => $_POST['campaign_id'] ?? '',
            'currency_code'     => $_POST['currency_code'] ?? '',
        ));

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

        $sources = $this->getFundingSources($adventure_id);
        if ($sources) {
            $data['success'] = true;
            $data['funding_sources'] = $sources;
            $data['message'] = $notification->pop(__('Connected! Your Tremendous account is reachable.', 'bluerabbit'), 'green', 'check');
        } else {
            $data['message'] = $notification->pop(__("Couldn't connect - check the API key and mode (sandbox/production).", 'bluerabbit'), 'red', 'cancel');
        }
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

        $data['success'] = true;
        $data['products'] = $this->getCatalog($adventure_id);
        echo json_encode($data);
        die();
    }
}

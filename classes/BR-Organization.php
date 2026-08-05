<?php
class BR_Organization {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    private function is_admin(): bool {
        return in_array('administrator', wp_get_current_user()->roles, true);
    }

    // ── Org CRUD ─────────────────────────────────────────────────────────────

    public function updateOrg(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = [];
        if (wp_verify_nonce($_POST['nonce'], 'br_update_org_nonce')) {
            $org_data = $_POST['org_data'];
            $id    = intval($org_data['id']);
            $name  = sanitize_text_field($org_data['name']);
            $logo  = esc_url_raw($org_data['logo']);
            $color = sanitize_text_field($org_data['color']);
            $status = sanitize_text_field($org_data['status']);
            $about = wp_kses_post(stripslashes_deep($org_data['about']));
            $today = date("Y-m-d H:i:s");

            $sql = $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}br_orgs
                 (`org_id`,`org_name`,`org_logo`,`org_content`,`org_color`,`owner_id`,`org_status`)
                 VALUES (%d, %s, %s, %s, %s, %d, %s)
                 ON DUPLICATE KEY UPDATE
                 `org_name`=%s,`org_logo`=%s,`org_content`=%s,`org_color`=%s,`owner_id`=%d,`org_status`=%s,`org_modified`=%s",
                $id, $name, $logo, $about, $color, $current_user->ID, $status,
                $name, $logo, $about, $color, $current_user->ID, $status, $today
            );
            $wpdb->query($sql);
            $org_id = $wpdb->insert_id ?: $id;
            $n = new Notification();
            $data['message']     = $n->pop(__('Organization Saved!','bluerabbit'), 'green');
            $data['success']     = true;
            $data['just_notify'] = true;
            BR_Activity::instance()->logActivity(0, $id ? 'update' : 'add', 'org', '', $org_id);
        } else {
            $data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1><h4>'.__("Unauthorized access","bluerabbit").'</h4>';
        }
        echo json_encode($data); die();
    }

    public function getOrgs($org_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        if (!in_array('administrator', $current_user->roles, true)) return false;
        if ($org_id) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname
                 FROM {$wpdb->prefix}br_orgs orgs
                 LEFT JOIN {$wpdb->prefix}br_players players ON orgs.owner_id = players.player_id
                 WHERE orgs.org_id = %d",
                $org_id
            ));
        }
        return $wpdb->get_results(
            "SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname
             FROM {$wpdb->prefix}br_orgs orgs
             LEFT JOIN {$wpdb->prefix}br_players players ON orgs.owner_id = players.player_id"
        );
    }

    // ── Players ───────────────────────────────────────────────────────────────

    public function getOrgPlayers($org_id=NULL){
        global $wpdb;
        if (!$this->is_admin() || !$org_id) return [];
        return $wpdb->get_results($wpdb->prepare(
            "SELECT players.*, org.role
             FROM {$wpdb->prefix}br_players players
             JOIN {$wpdb->prefix}br_player_org org ON org.player_id = players.player_id AND org.org_id = %d LIMIT 10000",
            $org_id
        )) ?: [];
    }

    public function findPlayersToOrg(){
        global $wpdb;
        if (!$this->is_admin() || !wp_verify_nonce($_POST['nonce'] ?? '', 'br_search_player_org_nonce')) return false;
        $s = '%' . $wpdb->esc_like($_POST['search_string'] ?? '') . '%';
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT players.* FROM {$wpdb->prefix}br_players players
             WHERE player_email LIKE %s OR player_first LIKE %s OR player_last LIKE %s OR player_display_name LIKE %s
             LIMIT 20",
            $s, $s, $s, $s
        ));
        if ($results) {
            foreach ($results as $p) {
                $theFile = get_template_directory() . '/player-select-org.php';
                if (file_exists($theFile)) include $theFile;
            }
        }
        die();
    }

    public function addPlayerToOrg(){
        global $wpdb;
        $player = BR_Player::instance()->getPlayerData(intval($_POST['player_id'] ?? 0));
        $org    = $this->getOrgs(intval($_POST['org_id'] ?? 0));
        if ($player && $org && $this->is_admin()) {
            $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->prefix}br_player_org (player_id, org_id) VALUES (%d, %d)",
                $player->player_id, $org->org_id
            ));
            $theFile = get_template_directory() . '/player-row-org.php';
            if (file_exists($theFile)) include $theFile;
            die();
        }
        echo json_encode(['success' => false]); die();
    }

    public function removePlayerFromOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }
        $player_id = intval($_POST['player_id'] ?? 0);
        $org_id    = intval($_POST['org_id']    ?? 0);
        if ($player_id && $org_id) {
            $wpdb->delete("{$wpdb->prefix}br_player_org",
                ['player_id' => $player_id, 'org_id' => $org_id], ['%d', '%d']
            );
            $data['success'] = true;
        }
        echo json_encode($data); die();
    }

    public function setPlayerOrgCapabilities(){
        global $wpdb;
        if (!$this->is_admin()) {
            echo json_encode(['success' => false, 'message' => __('Role not updated.','bluerabbit')]); die();
        }

        $org_id    = intval($_POST['org_id']    ?? 0);
        $player_id = intval($_POST['player_id'] ?? 0);
        $role      = sanitize_text_field($_POST['role'] ?? 'player');
        // Only these two are meaningful at org level; anything else is a bad request.
        if (!in_array($role, ['player', 'manager'], true)) $role = 'player';

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}br_player_org SET `role`=%s WHERE org_id=%d AND player_id=%d",
            $role, $org_id, $player_id
        ));

        echo json_encode([
            'success'   => true,
            'player_id' => $player_id,
            'role'      => $role,
            // The caller swaps this in whole rather than patching the old markup,
            // so the button's state, title and next-click role stay in one place.
            'row_html'  => $this->renderOrgPlayerRow($org_id, $player_id, $role),
            'message'   => $role === 'manager'
                ? __('Set as org manager','bluerabbit')
                : __('Manager role removed','bluerabbit'),
        ]); die();
    }

    // Renders player-row-org.php for one player, which expects $player and $org.
    private function renderOrgPlayerRow(int $org_id, int $player_id, string $role): string {
        $org    = $this->getOrgs($org_id);
        $player = BR_Player::instance()->getPlayerData($player_id);
        if (!$org || !$player) return '';
        $player->role = $role;

        $file = get_template_directory() . '/player-row-org.php';
        if (!file_exists($file)) return '';
        ob_start();
        include $file;
        return ob_get_clean();
    }

    // ── CSV bulk import: add existing users to org by email ──────────────────

    public function importPlayersToOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }

        $raw_emails = $_POST['emails'] ?? [];
        $org_id     = intval($_POST['org_id'] ?? 0);
        if (!$raw_emails || !$org_id) {
            $data['message'] = 'Missing data'; echo json_encode($data); die();
        }

        // Sanitize + deduplicate
        $emails = array_unique(array_filter(array_map(function($e){
            return strtolower(sanitize_email(trim($e)));
        }, $raw_emails)));

        if (!$emails) { $data['message'] = 'No valid emails'; echo json_encode($data); die(); }

        // Fetch all matching players in one query
        $ph      = implode(',', array_fill(0, count($emails), '%s'));
        $found   = $wpdb->get_results($wpdb->prepare(
            "SELECT player_id, player_email FROM {$wpdb->prefix}br_players WHERE player_email IN ($ph)",
            ...$emails
        ));
        $found_map  = [];
        $id_to_email = [];
        foreach ($found as $p) {
            $em = strtolower($p->player_email);
            $found_map[$em]            = (int)$p->player_id;
            $id_to_email[(int)$p->player_id] = $em;
        }

        $not_found_emails = [];
        $player_ids       = [];
        foreach ($emails as $email) {
            if (!isset($found_map[$email])) { $not_found_emails[] = $email; }
            else                            { $player_ids[] = $found_map[$email]; }
        }

        $already_in = 0; $added = 0;
        $added_emails = []; $already_in_emails = [];
        if ($player_ids) {
            // Check which are already in org
            $ph2      = implode(',', array_fill(0, count($player_ids), '%d'));
            $existing = $wpdb->get_col($wpdb->prepare(
                "SELECT player_id FROM {$wpdb->prefix}br_player_org WHERE org_id=%d AND player_id IN ($ph2)",
                array_merge([$org_id], $player_ids)
            ));
            $existing_set = array_flip(array_map('intval', $existing));
            $new_ids      = array_values(array_filter($player_ids, fn($id) => !isset($existing_set[$id])));
            $already_ids  = array_values(array_filter($player_ids, fn($id) =>  isset($existing_set[$id])));
            $already_in   = count($already_ids);
            $already_in_emails = array_map(fn($id) => $id_to_email[$id] ?? '', $already_ids);

            if ($new_ids) {
                $ins_ph  = implode(',', array_fill(0, count($new_ids), '(%d,%d)'));
                $ins_vals = [];
                foreach ($new_ids as $pid) { $ins_vals[] = $pid; $ins_vals[] = $org_id; }
                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO {$wpdb->prefix}br_player_org (player_id, org_id) VALUES $ins_ph",
                    $ins_vals
                ));
                $added        = (int)$wpdb->rows_affected;
                $added_emails = array_map(fn($id) => $id_to_email[$id] ?? '', $new_ids);
            }
        }

        $data['success']            = true;
        $data['added']              = $added;
        $data['added_emails']       = $added_emails;
        $data['already_in']         = $already_in;
        $data['already_in_emails']  = $already_in_emails;
        $data['not_found']          = count($not_found_emails);
        $data['not_found_emails']   = $not_found_emails;
        echo json_encode($data); die();
    }

    // ── Bulk: add all players from an adventure to this org ──────────────────

    public function bulkPlayersFromAdventure(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }

        $adventure_id = intval($_POST['adventure_id'] ?? 0);
        $org_id       = intval($_POST['org_id']       ?? 0);
        if (!$adventure_id || !$org_id) {
            $data['message'] = 'Missing data'; echo json_encode($data); die();
        }

        // Everyone who belongs to the adventure comes across, not just the
        // players: game masters, NPCs and the adventure owner are org members
        // too, and leaving them out meant an org could not see its own staff.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT player_id, player_adventure_role AS role
             FROM {$wpdb->prefix}br_player_adventure
             WHERE adventure_id=%d AND player_adventure_status='in'",
            $adventure_id
        ), ARRAY_A) ?: [];

        $by_role = [];
        foreach ($rows as $r) {
            $by_role[$r['role'] ?: 'player'][] = (int)$r['player_id'];
        }

        // The owner is on the adventure record, not in the enrolment table.
        $owner_id = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT adventure_owner FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d",
            $adventure_id
        ));
        $enrolled = array_map('intval', array_column($rows, 'player_id'));
        if ($owner_id && !in_array($owner_id, $enrolled, true)) {
            $by_role['owner'][] = $owner_id;
        }

        $players = array_values(array_unique(array_merge($enrolled, $owner_id ? [$owner_id] : [])));

        $added = 0; $already_in = 0;
        if ($players) {
            $ph2      = implode(',', array_fill(0, count($players), '%d'));
            $existing = $wpdb->get_col($wpdb->prepare(
                "SELECT player_id FROM {$wpdb->prefix}br_player_org WHERE org_id=%d AND player_id IN ($ph2)",
                array_merge([$org_id], $players)
            ));
            $existing_set = array_flip(array_map('intval', $existing));
            $new_ids = array_filter($players, fn($id) => !isset($existing_set[(int)$id]));
            $already_in = count($players) - count($new_ids);

            if ($new_ids) {
                $ins_ph   = implode(',', array_fill(0, count($new_ids), '(%d,%d)'));
                $ins_vals = [];
                foreach ($new_ids as $pid) { $ins_vals[] = (int)$pid; $ins_vals[] = $org_id; }
                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO {$wpdb->prefix}br_player_org (player_id, org_id) VALUES $ins_ph",
                    $ins_vals
                ));
                $added = (int)$wpdb->rows_affected;
            }
        }

        $data['success']    = true;
        $data['added']      = $added;
        $data['already_in'] = $already_in;
        $data['total']      = count($players);
        // Per-role tallies so the console can say what kind of people came over.
        $data['by_role']    = array_map('count', $by_role);
        echo json_encode($data); die();
    }

    // ── Adventures ───────────────────────────────────────────────────────────

    public function getOrgAdventures($org_id=NULL){
        global $wpdb;
        if (!$this->is_admin() || !$org_id) return [];
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, p.player_display_name, p.player_email
             FROM {$wpdb->prefix}br_adventures a
             JOIN {$wpdb->prefix}br_org_adventure oa ON a.adventure_id = oa.adventure_id AND oa.org_id = %d
             LEFT JOIN {$wpdb->prefix}br_players p ON a.adventure_owner = p.player_id
             ORDER BY a.adventure_title ASC",
            $org_id
        )) ?: [];
    }

    public function searchAdventuresForOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }
        $search = '%' . $wpdb->esc_like(sanitize_text_field($_POST['search'] ?? '')) . '%';
        $org_id = intval($_POST['org_id'] ?? 0);
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT a.adventure_id, a.adventure_title, a.adventure_badge, a.adventure_color, p.player_display_name
             FROM {$wpdb->prefix}br_adventures a
             LEFT JOIN {$wpdb->prefix}br_players p ON a.adventure_owner = p.player_id
             WHERE a.adventure_title LIKE %s
               AND a.adventure_status = 'publish'
               AND a.adventure_id NOT IN (
                   SELECT adventure_id FROM {$wpdb->prefix}br_org_adventure WHERE org_id = %d
               )
             ORDER BY a.adventure_title ASC
             LIMIT 10",
            $search, $org_id
        )) ?: [];
        $data['success'] = true;
        $data['results'] = $results;
        echo json_encode($data); die();
    }

    public function addAdventureToOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }
        $adventure_id = intval($_POST['adventure_id'] ?? 0);
        $org_id       = intval($_POST['org_id']       ?? 0);
        if ($adventure_id && $org_id) {
            $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->prefix}br_org_adventure (org_id, adventure_id) VALUES (%d, %d)",
                $org_id, $adventure_id
            ));
            $adv = $wpdb->get_row($wpdb->prepare(
                "SELECT a.adventure_id, a.adventure_title, a.adventure_badge, a.adventure_color, a.adventure_code, p.player_display_name
                 FROM {$wpdb->prefix}br_adventures a
                 LEFT JOIN {$wpdb->prefix}br_players p ON a.adventure_owner = p.player_id
                 WHERE a.adventure_id = %d",
                $adventure_id
            ));
            if ($adv) {
                $data['success']          = true;
                $data['adventure_id']     = (int)$adv->adventure_id;
                $data['adventure_title']  = $adv->adventure_title;
                $data['adventure_code']   = $adv->adventure_code;
                $data['owner_name']       = $adv->player_display_name ?? '';
            }
        }
        echo json_encode($data); die();
    }

    public function removeAdventureFromOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin()) { echo json_encode($data); die(); }
        $adventure_id = intval($_POST['adventure_id'] ?? 0);
        $org_id       = intval($_POST['org_id']       ?? 0);
        if ($adventure_id && $org_id) {
            $wpdb->delete("{$wpdb->prefix}br_org_adventure",
                ['org_id' => $org_id, 'adventure_id' => $adventure_id], ['%d', '%d']
            );
            $data['success'] = true;
        }
        echo json_encode($data); die();
    }

    // ── Create / Delete Org ──────────────────────────────────────────────────

    public function createOrg(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = ['success' => false];
        if (!$this->is_admin() || !wp_verify_nonce($_POST['nonce'] ?? '', 'br_manage_orgs_nonce')) {
            echo json_encode($data); die();
        }
        $name   = sanitize_text_field($_POST['name']   ?? '');
        $color  = sanitize_text_field($_POST['color']  ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'publish');
        if (!$name) { $data['message'] = 'Name required'; echo json_encode($data); die(); }

        $wpdb->insert(
            "{$wpdb->prefix}br_orgs",
            ['org_name' => $name, 'org_color' => $color, 'org_status' => $status, 'owner_id' => $current_user->ID],
            ['%s', '%s', '%s', '%d']
        );
        $org_id = (int)$wpdb->insert_id;
        BR_Activity::instance()->logActivity(0, 'add', 'org', '', $org_id);
        $data['success'] = true;
        $data['org_id']  = $org_id;
        echo json_encode($data); die();
    }

    public function deleteOrg(){
        global $wpdb;
        $data = ['success' => false];
        if (!$this->is_admin() || !wp_verify_nonce($_POST['nonce'] ?? '', 'br_manage_orgs_nonce')) {
            echo json_encode($data); die();
        }
        $org_id = intval($_POST['org_id'] ?? 0);
        if (!$org_id) { echo json_encode($data); die(); }
        $wpdb->delete("{$wpdb->prefix}br_player_org",    ['org_id' => $org_id], ['%d']);
        $wpdb->delete("{$wpdb->prefix}br_org_adventure", ['org_id' => $org_id], ['%d']);
        $wpdb->delete("{$wpdb->prefix}br_orgs",          ['org_id' => $org_id], ['%d']);
        $data['success'] = true;
        echo json_encode($data); die();
    }
}

<?php
class BR_Branch {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function getBranchGroups($adventure_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_branch_groups
             WHERE adventure_id = %d AND group_status = 'publish'
             ORDER BY group_date",
            $adventure_id
        ));
    }

    public function getBranchGroup($group_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_branch_groups WHERE group_id = %d",
            $group_id
        ));
    }

    public function updateBranchGroup() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();
        $adventure_id = (int) $_POST['adventure_id'];
        $group_id = isset($_POST['group_id']) ? (int) $_POST['group_id'] : 0;
        $group_name = sanitize_text_field($_POST['group_name'] ?? '');
        $group_description = sanitize_textarea_field($_POST['group_description'] ?? '');
        $group_status = sanitize_text_field($_POST['group_status'] ?? 'publish');

        if (!$group_name) {
            $data['message'] = $notification->pop(__('Name is required', 'bluerabbit'), 'red', 'warning');
            echo json_encode($data); die();
        }

        if ($group_id) {
            $wpdb->update("{$wpdb->prefix}br_branch_groups", [
                'group_name'        => $group_name,
                'group_description' => $group_description,
                'group_status'      => $group_status,
            ], ['group_id' => $group_id]);
            $data['message'] = $notification->pop(__('Branch group updated', 'bluerabbit'), 'green', 'check');
        } else {
            $wpdb->insert("{$wpdb->prefix}br_branch_groups", [
                'adventure_id'      => $adventure_id,
                'group_name'        => $group_name,
                'group_description' => $group_description,
                'group_status'      => $group_status,
            ]);
            $group_id = $wpdb->insert_id;
            $data['message'] = $notification->pop(__('Branch group created', 'bluerabbit'), 'green', 'check');
            BR_Activity::instance()->logActivity($adventure_id, 'add', 'branch_group', '', $group_id);
        }

        $data['success'] = true;
        $data['group_id'] = $group_id;
        echo json_encode($data);
        die();
    }

    public function getGroupAchievements($group_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_achievements
             WHERE branch_group_id = %d AND achievement_status = 'publish'
             ORDER BY achievement_order, achievement_name",
            $group_id
        ));
    }

    public function assignAchievementToGroup() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();
        $achievement_id = (int) $_POST['achievement_id'];
        $group_id = (int) $_POST['group_id'];

        $already = $wpdb->get_var($wpdb->prepare(
            "SELECT branch_group_id FROM {$wpdb->prefix}br_achievements WHERE achievement_id = %d",
            $achievement_id
        ));
        if ($already && (int)$already !== $group_id) {
            $other = $wpdb->get_var($wpdb->prepare(
                "SELECT group_name FROM {$wpdb->prefix}br_branch_groups WHERE group_id = %d", $already
            ));
            $data['message'] = $notification->pop(
                sprintf(__('Already assigned to "%s"', 'bluerabbit'), $other), 'amber', 'warning'
            );
            echo json_encode($data); die();
        }

        $wpdb->update("{$wpdb->prefix}br_achievements",
            ['branch_group_id' => $group_id],
            ['achievement_id' => $achievement_id]
        );
        $data['success'] = true;
        $data['message'] = $notification->pop(__('Achievement assigned', 'bluerabbit'), 'green', 'check');
        echo json_encode($data);
        die();
    }

    public function removeAchievementFromGroup() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();
        $achievement_id = (int) $_POST['achievement_id'];

        $wpdb->update("{$wpdb->prefix}br_achievements",
            ['branch_group_id' => null],
            ['achievement_id' => $achievement_id]
        );
        $data['success'] = true;
        $data['message'] = $notification->pop(__('Achievement removed from group', 'bluerabbit'), 'green', 'check');
        echo json_encode($data);
        die();
    }

    public function deleteBranchGroup() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();
        $group_id = (int) $_POST['group_id'];

        $count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_achievements WHERE branch_group_id = %d",
            $group_id
        ));
        if ($count > 0) {
            $data['message'] = $notification->pop(
                __('Remove all achievements first', 'bluerabbit'), 'amber', 'warning'
            );
            echo json_encode($data); die();
        }

        $wpdb->delete("{$wpdb->prefix}br_branch_groups", ['group_id' => $group_id]);
        $data['success'] = true;
        $data['message'] = $notification->pop(__('Branch group deleted', 'bluerabbit'), 'red', 'trash');
        echo json_encode($data);
        die();
    }

    public function getBranchRules($achievement_id, $adventure_id = null) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}br_branch_rules WHERE achievement_id = %d";
        $params = [$achievement_id];
        if ($adventure_id) {
            $sql .= " AND adventure_id = %d";
            $params[] = $adventure_id;
        }
        $sql .= " ORDER BY rule_order";
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public function saveBranchRule() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();

        $rule_id         = isset($_POST['rule_id']) ? (int) $_POST['rule_id'] : 0;
        $achievement_id  = (int) $_POST['achievement_id'];
        $adventure_id    = (int) $_POST['adventure_id'];
        $rule_action     = sanitize_text_field($_POST['rule_action'] ?? 'show');
        $rule_target_type = sanitize_text_field($_POST['rule_target_type'] ?? 'quest');
        $rule_target_id  = (int) $_POST['rule_target_id'];
        $rule_order      = (int) ($_POST['rule_order'] ?? 0);

        $valid_actions = ['show', 'hide', 'lock', 'unlock'];
        $valid_targets = ['quest', 'achievement', 'item', 'branch_group'];
        if (!in_array($rule_action, $valid_actions) || !in_array($rule_target_type, $valid_targets)) {
            $data['message'] = $notification->pop(__('Invalid rule parameters', 'bluerabbit'), 'red', 'warning');
            echo json_encode($data); die();
        }

        if ($rule_id) {
            $wpdb->update("{$wpdb->prefix}br_branch_rules", [
                'rule_action'      => $rule_action,
                'rule_target_type' => $rule_target_type,
                'rule_target_id'   => $rule_target_id,
                'rule_order'       => $rule_order,
            ], ['rule_id' => $rule_id]);
        } else {
            $wpdb->insert("{$wpdb->prefix}br_branch_rules", [
                'achievement_id'   => $achievement_id,
                'adventure_id'     => $adventure_id,
                'rule_action'      => $rule_action,
                'rule_target_type' => $rule_target_type,
                'rule_target_id'   => $rule_target_id,
                'rule_order'       => $rule_order,
            ]);
            $rule_id = $wpdb->insert_id;
        }

        $data['success'] = true;
        $data['rule_id'] = $rule_id;
        $data['message'] = $notification->pop(__('Rule saved', 'bluerabbit'), 'green', 'check');
        echo json_encode($data);
        die();
    }

    public function deleteBranchRule() {
        global $wpdb;
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();
        $rule_id = (int) $_POST['rule_id'];

        $wpdb->delete("{$wpdb->prefix}br_branch_rules", ['rule_id' => $rule_id]);
        $data['success'] = true;
        $data['message'] = $notification->pop(__('Rule removed', 'bluerabbit'), 'red', 'trash');
        echo json_encode($data);
        die();
    }

    // Guards every achievement-reward grant site (quest/challenge/step/QR completion),
    // not just the dedicated branch-choice step - an achievement assigned to a branch
    // group must stay mutually exclusive with its groupmates however it's earned, or
    // players can double up by completing two different quests that reward two
    // different achievements from the same branch. Covers both the newer branch_group_id
    // (br_branch_groups) grouping and the legacy achievement_group string field still used
    // by BR_Achievement::magicCode()'s "path" achievements - same exclusivity rule either way.
    public function canGrantAchievement($player_id, $adventure_id, $achievement_id) {
        return $this->getHeldBranchmate($player_id, $adventure_id, $achievement_id) === null;
    }

    // Returns the achievement (full row) the player already holds from the same branch as
    // $achievement_id, if any - so callers can tell the player exactly what's blocking them.
    // Null means no branch conflict (either not part of a branch, or no groupmate held yet).
    public function getHeldBranchmate($player_id, $adventure_id, $achievement_id) {
        global $wpdb;
        $ach = $wpdb->get_row($wpdb->prepare(
            "SELECT branch_group_id, achievement_group, achievement_display FROM {$wpdb->prefix}br_achievements WHERE achievement_id = %d",
            $achievement_id
        ));
        if (!$ach) return null;

        if ($ach->branch_group_id) {
            $held = $wpdb->get_row($wpdb->prepare(
                "SELECT a.* FROM {$wpdb->prefix}br_player_achievement pa
                 JOIN {$wpdb->prefix}br_achievements a ON pa.achievement_id = a.achievement_id
                 WHERE pa.player_id = %d AND pa.adventure_id = %d
                   AND a.branch_group_id = %d AND pa.achievement_id != %d
                 LIMIT 1",
                $player_id, $adventure_id, $ach->branch_group_id, $achievement_id
            ));
            if ($held) return $held;
        }

        if ($ach->achievement_group !== '' && $ach->achievement_group !== null && $ach->achievement_display === 'path') {
            $held = $wpdb->get_row($wpdb->prepare(
                "SELECT a.* FROM {$wpdb->prefix}br_player_achievement pa
                 JOIN {$wpdb->prefix}br_achievements a ON pa.achievement_id = a.achievement_id
                 WHERE pa.player_id = %d AND pa.adventure_id = %d
                   AND a.achievement_group = %s AND pa.achievement_id != %d
                 LIMIT 1",
                $player_id, $adventure_id, $ach->achievement_group, $achievement_id
            ));
            if ($held) return $held;
        }

        return null;
    }

    public function getPlayerBranch($player_id, $adventure_id, $group_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_player_branches
             WHERE player_id = %d AND adventure_id = %d AND group_id = %d",
            $player_id, $adventure_id, $group_id
        ));
    }

    public function getPlayerBranches($player_id, $adventure_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT pb.*, bg.group_name
             FROM {$wpdb->prefix}br_player_branches pb
             JOIN {$wpdb->prefix}br_branch_groups bg ON pb.group_id = bg.group_id
             WHERE pb.player_id = %d AND pb.adventure_id = %d",
            $player_id, $adventure_id
        ));
    }

    public function playerBranchChoice($player_id, $adventure_id, $group_id, $achievement_id) {
        global $wpdb;

        // Composite PK enforces one choice — INSERT will fail on duplicate
        $inserted = $wpdb->insert("{$wpdb->prefix}br_player_branches", [
            'player_id'      => $player_id,
            'adventure_id'   => $adventure_id,
            'group_id'       => $group_id,
            'achievement_id' => $achievement_id,
        ]);

        if (!$inserted) {
            $existing = $this->getPlayerBranch($player_id, $adventure_id, $group_id);
            return [
                'success'        => false,
                'already_chosen' => true,
                'achievement_id' => $existing ? $existing->achievement_id : null,
            ];
        }

        // Grant the PATH achievement
        $has_ach = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_achievement
             WHERE player_id = %d AND adventure_id = %d AND achievement_id = %d",
            $player_id, $adventure_id, $achievement_id
        ));
        if (!$has_ach) {
            $now = date('Y-m-d H:i:s');
            $wpdb->insert("{$wpdb->prefix}br_player_achievement", [
                'player_id'           => $player_id,
                'adventure_id'        => $adventure_id,
                'achievement_id'      => $achievement_id,
                'achievement_applied' => $now,
            ]);
        }

        $this->applyBranchRules($player_id, $adventure_id, $achievement_id);

        BR_Activity::instance()->logActivity($adventure_id, 'branch_choice', 'branch', $achievement_id, $group_id);
        BR_Player::instance()->resetPlayer($adventure_id, $player_id);

        return [
            'success'        => true,
            'achievement_id' => $achievement_id,
            'group_id'       => $group_id,
        ];
    }

    public function applyBranchRules($player_id, $adventure_id, $achievement_id) {
        global $wpdb;

        $rules = $this->getBranchRules($achievement_id, $adventure_id);
        if (!$rules) return;

        foreach ($rules as $rule) {
            switch ($rule->rule_target_type) {
                case 'quest':
                    if ($rule->rule_action === 'lock') {
                        $wpdb->update("{$wpdb->prefix}br_quests",
                            ['quest_branch_lock' => 1],
                            ['quest_id' => $rule->rule_target_id]
                        );
                    } elseif ($rule->rule_action === 'unlock') {
                        $wpdb->update("{$wpdb->prefix}br_quests",
                            ['quest_branch_lock' => 0],
                            ['quest_id' => $rule->rule_target_id]
                        );
                    }
                    break;
            }
        }
    }

    public function ajaxPlayerBranchChoice() {
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();

        $current_user = wp_get_current_user();
        $player_id    = $current_user->ID;
        $adventure_id = (int) $_POST['adventure_id'];
        $group_id     = (int) $_POST['group_id'];
        $achievement_id = (int) $_POST['achievement_id'];

        if (!$player_id || !$adventure_id || !$group_id || !$achievement_id) {
            $data['message'] = $notification->pop(__('Missing parameters', 'bluerabbit'), 'red', 'warning');
            echo json_encode($data); die();
        }

        $result = $this->playerBranchChoice($player_id, $adventure_id, $group_id, $achievement_id);

        if ($result['success']) {
            $data['success'] = true;
            $data['message'] = $notification->pop(__('Path chosen!', 'bluerabbit'), 'green', 'check');
            $data['result'] = $result;
        } else {
            $data['message'] = $notification->pop(__('You already made this choice', 'bluerabbit'), 'amber', 'warning');
            $data['result'] = $result;
        }

        echo json_encode($data);
        die();
    }
}

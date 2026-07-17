<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Unified conditions engine for threshold/count-based gates - journey %,
 * milestone count, tabi count, level, transaction count, items consumed -
 * attachable to any target: quest, tabi, achievement, item, or item_category.
 *
 * Object-reference gates (requires this specific quest/tabi/achievement/key-item)
 * are NOT handled here - those live in br_reqs (which gained a `target_type`
 * column so Tabis can reuse the same quest/achievement/item requirement rows
 * Quests already have, plus a new 'tabi' req_type). Keeping the two concerns
 * in separate tables avoids two different mechanisms both claiming to answer
 * "does this target require that specific quest/achievement".
 *
 * This sits alongside - not instead of - br_tabi_prerequisites (tabi-to-tabi)
 * and br_adventure_ranks (level-based rank award), which are left untouched.
 */
class BR_Conditions {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // condition_type => label, purely for admin-UI dropdowns; all types here compare
    // a snapshot metric against threshold_value.
    const CONDITION_TYPES = [
        'level'               => 'Level',
        'journey_pct'         => 'Journey Completion %',
        'milestone_count'     => 'Milestones Completed',
        'tabi_count'          => 'Tabis Completed',
        'transaction_count'   => 'Item Shop Transactions',
        'item_consumed_count' => 'Items Consumed',
    ];

    // ── CRUD (replace-on-save, matching the existing br_reqs/br_tabi_prerequisites convention) ──

    public function getConditions($adventure_id, $target_type, $target_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_conditions
            WHERE adventure_id=%d AND target_type=%s AND target_id=%s
            ORDER BY condition_id ASC",
            $adventure_id, $target_type, (string) $target_id
        ));
    }

    // $conditions: array of ['condition_type'=>string, 'threshold_value'=>float]
    public function saveConditions($adventure_id, $target_type, $target_id, $conditions) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}br_conditions WHERE adventure_id=%d AND target_type=%s AND target_id=%s",
            $adventure_id, $target_type, (string) $target_id
        ));
        if (empty($conditions)) return true;

        foreach ($conditions as $c) {
            $type = sanitize_text_field($c['condition_type'] ?? '');
            if (!array_key_exists($type, self::CONDITION_TYPES)) continue;
            if (!isset($c['threshold_value']) || $c['threshold_value'] === '') continue;

            $wpdb->insert("{$wpdb->prefix}br_conditions", [
                'adventure_id'    => (int) $adventure_id,
                'target_type'     => $target_type,
                'target_id'       => (string) $target_id,
                'condition_type'  => $type,
                'threshold_value' => (float) $c['threshold_value'],
            ]);
        }
        return true;
    }

    // ── Progress snapshot ──

    // $player_progress is the array already returned by BR_Progression::getPlayerProgress()
    // (cached once per page load as $playerReset in header.php) - reused here instead of
    // re-querying fqs/level/items, which are already expensive to compute. $adv_parent_id
    // scopes quest/tabi definitions (template-level); $adv_child_id scopes player-specific
    // transaction history, matching the convention used everywhere else in the codebase.
    public function buildProgressSnapshot($adv_parent_id, $adv_child_id, $player_id, $player_progress) {
        global $wpdb;

        $fqs             = $player_progress['player']['fqs'] ?? [];
        $level           = $player_progress['player']['level'] ?? 1;
        $achievement_ids = $player_progress['achievements_ids'] ?? [];
        $key_item_ids    = array_keys($player_progress['items']['key'] ?? []);

        $total_milestones = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id=%d AND quest_status='publish' AND quest_type IN ('quest','challenge','survey','mission')",
            $adv_parent_id
        ));
        $milestone_count = count($fqs);
        $journey_pct     = $total_milestones > 0 ? round(($milestone_count / $total_milestones) * 100, 2) : 0;

        $completed_tabi_ids = BR_Tabi::instance()->getCompletedTabiIds($adv_parent_id, $player_id);
        $total_tabis        = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_tabis WHERE adventure_id=%d AND tabi_status='publish'",
            $adv_parent_id
        ));

        // "Itemshop transactions" = purchases only (consumable/key), excluding blocker/deadline/unlock/attempt payments.
        $transaction_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
            WHERE adventure_id=%d AND player_id=%d AND trnx_status='publish' AND trnx_type IN ('consumable','key')",
            $adv_child_id, $player_id
        ));
        $item_consumed_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
            WHERE adventure_id=%d AND player_id=%d AND trnx_status='publish' AND trnx_use=1",
            $adv_child_id, $player_id
        ));

        return [
            'level'               => (int) $level,
            'fqs'                 => $fqs,
            'milestone_count'     => $milestone_count,
            'total_milestones'    => $total_milestones,
            'journey_pct'         => $journey_pct,
            'completed_tabi_ids'  => $completed_tabi_ids,
            'tabi_count'          => count($completed_tabi_ids),
            'total_tabis'         => $total_tabis,
            'achievement_ids'     => $achievement_ids,
            'key_item_ids'        => $key_item_ids,
            'transaction_count'   => $transaction_count,
            'item_consumed_count' => $item_consumed_count,
        ];
    }

    // ── Evaluation ──

    // True only if EVERY condition attached to this target is satisfied (AND semantics,
    // matching the existing "all required quests must be finished" convention elsewhere).
    // A target with no attached conditions is always considered met.
    public function evaluate($adventure_id, $target_type, $target_id, $snapshot) {
        foreach ($this->getConditions($adventure_id, $target_type, $target_id) as $c) {
            if (!$this->conditionMet($c, $snapshot)) return false;
        }
        return true;
    }

    // Same check as evaluate(), but returns the unmet condition rows instead of a bool -
    // for UI messaging (e.g. "Requires 75% journey completion, you're at 40%").
    public function getUnmetConditions($adventure_id, $target_type, $target_id, $snapshot) {
        $unmet = [];
        foreach ($this->getConditions($adventure_id, $target_type, $target_id) as $c) {
            if (!$this->conditionMet($c, $snapshot)) $unmet[] = $c;
        }
        return $unmet;
    }

    private function conditionMet($c, $snapshot) {
        switch ($c->condition_type) {
            case 'level':
                return ($snapshot['level'] ?? 0) >= (float) $c->threshold_value;
            case 'journey_pct':
                return ($snapshot['journey_pct'] ?? 0) >= (float) $c->threshold_value;
            case 'milestone_count':
                return ($snapshot['milestone_count'] ?? 0) >= (float) $c->threshold_value;
            case 'tabi_count':
                return ($snapshot['tabi_count'] ?? 0) >= (float) $c->threshold_value;
            case 'transaction_count':
                return ($snapshot['transaction_count'] ?? 0) >= (float) $c->threshold_value;
            case 'item_consumed_count':
                return ($snapshot['item_consumed_count'] ?? 0) >= (float) $c->threshold_value;
            default:
                // Unknown condition_type - fail closed rather than silently grant access.
                return false;
        }
    }
}

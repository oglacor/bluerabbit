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
        'achievement_count'   => 'Achievements Earned',
        'specific_quest'      => 'Complete This Milestone',
        'specific_tabi'       => 'Complete This Tabi',
        'tabi_pct'            => 'Progress % in This Tabi',
    ];

    // Types that reference one specific quest/tabi via the object_id column (a plain
    // membership check, not a >= threshold) - the admin UI needs a picker for these,
    // not a bare number input.
    const OBJECT_TYPES = ['specific_quest', 'specific_tabi', 'tabi_pct'];

    // Of the OBJECT_TYPES, which also carry a numeric threshold alongside the object_id
    // (tabi_pct: "X% of THIS tabi") vs. pure membership (specific_quest/specific_tabi:
    // "done or not done", no threshold).
    const OBJECT_TYPES_WITH_THRESHOLD = ['tabi_pct'];

    // The pre-existing "one number input per type" UIs (rank condition dropdowns, and
    // the Quest/Tabi/Item Conditions modals' threshold list) only ever compare a single
    // number against a snapshot metric - they have no picker for "which quest/tabi", so
    // OBJECT_TYPES must stay hidden from them. Only the achievement Conditions tab
    // (which has a real object picker) uses the full CONDITION_TYPES list.
    public static function simpleTypes() {
        return array_diff_key(self::CONDITION_TYPES, array_flip(self::OBJECT_TYPES));
    }

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

    // $conditions: array of ['condition_type'=>string, 'threshold_value'=>float, 'object_id'=>int]
    // object_id only matters (and is required) for OBJECT_TYPES; threshold_value is
    // skipped for the pure-membership object types (specific_quest/specific_tabi).
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

            $is_object_type = in_array($type, self::OBJECT_TYPES, true);
            $needs_threshold = !$is_object_type || in_array($type, self::OBJECT_TYPES_WITH_THRESHOLD, true);

            $object_id = null;
            if ($is_object_type) {
                $object_id = isset($c['object_id']) ? (int) $c['object_id'] : 0;
                if (!$object_id) continue; // no target picked - skip this row rather than save a broken condition
            }

            $threshold_value = null;
            if ($needs_threshold) {
                if (!isset($c['threshold_value']) || $c['threshold_value'] === '') continue;
                $threshold_value = (float) $c['threshold_value'];
            }

            $wpdb->insert("{$wpdb->prefix}br_conditions", [
                'adventure_id'    => (int) $adventure_id,
                'target_type'     => $target_type,
                'target_id'       => (string) $target_id,
                'condition_type'  => $type,
                'object_id'       => $object_id,
                'threshold_value' => $threshold_value,
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
        $key_item_ids    = $this->keyItemIds($player_progress);

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
            // Carried through so conditionMet() can lazily compute a per-tabi percentage
            // (tabi_pct) without every snapshot having to precompute progress for every
            // tabi in the adventure up front.
            'adv_parent_id'       => $adv_parent_id,
            'player_id'           => $player_id,
        ];
    }

    // Key-item ids the player currently holds (tabi-pieces count as keys, matching
    // BR_Item::getMyItems()).
    //
    // Two different "player state" arrays reach buildProgressSnapshot() and they
    // disagree about where owned items live:
    //
    //   getPlayerProgress()  ['items']           = getMyItems() output - key and
    //                                              tabi-piece merged under ['key'],
    //                                              keyed by item_id, spent items excluded
    //   resetPlayer()        ['player']['items'] = plain lists of item ids per
    //                                              transaction type
    //
    // Reading only the first shape returned an empty list for every caller holding a
    // resetPlayer() result, and an empty list fails the key-item gate in
    // BR_Progression::milestoneState() - so the "next milestone" offered after a
    // quest, challenge or survey stayed locked for players who did hold the key.
    // resetPlayer() now publishes ['items'] too; this reads either so no future
    // caller can lose the list again by assembling its own array.
    private function keyItemIds($player_progress): array {
        $ids = [];

        foreach ((array) ($player_progress['items']['key'] ?? []) as $item_id => $row) {
            $ids[] = is_object($row) && isset($row->item_id) ? (int) $row->item_id : (int) $item_id;
        }

        if (!$ids) {
            foreach (['key', 'tabi-piece'] as $type) {
                foreach ((array) ($player_progress['player']['items'][$type] ?? []) as $item_id) {
                    if (is_scalar($item_id)) $ids[] = (int) $item_id;
                }
            }
        }

        return array_values(array_filter(array_unique($ids)));
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
            case 'achievement_count':
                return count($snapshot['achievement_ids'] ?? []) >= (float) $c->threshold_value;
            case 'specific_quest':
                return in_array((int) $c->object_id, array_map('intval', $snapshot['fqs'] ?? []), true);
            case 'specific_tabi':
                return in_array((int) $c->object_id, array_map('intval', $snapshot['completed_tabi_ids'] ?? []), true);
            case 'tabi_pct':
                $pct = BR_Tabi::instance()->getTabiProgressPct(
                    $snapshot['adv_parent_id'] ?? 0, $snapshot['player_id'] ?? 0, (int) $c->object_id
                );
                return $pct >= (float) $c->threshold_value;
            default:
                // Unknown condition_type - fail closed rather than silently grant access.
                return false;
        }
    }

    // Human-friendly "why did I earn this" line for the reward overlay - prefers the
    // first specific-object condition (names the actual quest/tabi by its own title, no
    // internal jargon), falling back to a plain description of the first threshold
    // condition attached. Achievements built this way are expected to carry one
    // condition in practice, so "first condition" is a reasonable summary even though
    // evaluate() itself is AND across all of them.
    public function describeMetCondition($adventure_id, $target_type, $target_id) {
        global $wpdb;
        foreach ($this->getConditions($adventure_id, $target_type, $target_id) as $c) {
            switch ($c->condition_type) {
                case 'specific_tabi':
                    $name = $wpdb->get_var($wpdb->prepare("SELECT tabi_name FROM {$wpdb->prefix}br_tabis WHERE tabi_id=%d", $c->object_id));
                    if ($name) return sprintf(__('You completed %s!', 'bluerabbit'), wp_strip_all_tags($name));
                    break;
                case 'specific_quest':
                    $title = $wpdb->get_var($wpdb->prepare("SELECT quest_title FROM {$wpdb->prefix}br_quests WHERE quest_id=%d", $c->object_id));
                    if ($title) return sprintf(__('You completed %s!', 'bluerabbit'), $title);
                    break;
                case 'tabi_pct':
                    $name = $wpdb->get_var($wpdb->prepare("SELECT tabi_name FROM {$wpdb->prefix}br_tabis WHERE tabi_id=%d", $c->object_id));
                    if ($name) return sprintf(__('You made great progress on %s!', 'bluerabbit'), wp_strip_all_tags($name));
                    break;
                case 'journey_pct':
                    return sprintf(__("You've completed %s%% of the journey!", 'bluerabbit'), rtrim(rtrim((string) $c->threshold_value, '0'), '.'));
                case 'milestone_count':
                    return sprintf(__('You completed %d Milestones!', 'bluerabbit'), (int) $c->threshold_value);
                case 'tabi_count':
                    return sprintf(__('You completed %d Tabis!', 'bluerabbit'), (int) $c->threshold_value);
                case 'achievement_count':
                    return sprintf(__('You earned %d Achievements!', 'bluerabbit'), (int) $c->threshold_value);
                case 'transaction_count':
                    return sprintf(__('You made %d purchases!', 'bluerabbit'), (int) $c->threshold_value);
                case 'item_consumed_count':
                    return sprintf(__('You used %d items!', 'bluerabbit'), (int) $c->threshold_value);
                case 'level':
                    return sprintf(__('You reached Level %d!', 'bluerabbit'), (int) $c->threshold_value);
            }
        }
        return __('You earned this achievement!', 'bluerabbit');
    }
}

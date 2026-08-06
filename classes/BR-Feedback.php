<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * One vocabulary for "the player just gained something".
 *
 * Before this there were two celebration overlays (the legacy #level-up explosion
 * and the newer rewards drawer) plus a toast list, and a single action could fire
 * two of them one after another - level up, then a separate popup for the rank the
 * level-up just earned. Every action now describes what happened in the same shape
 * and the client renders them together in one panel.
 *
 * An event is deliberately thin: what kind of thing it was, a verb for the headline
 * sentence, a title, an optional line of context, and something to look at.
 *
 *   [
 *     'type'     => 'achievement',
 *     'verb'     => 'earned an achievement',   // composed into "You just ..."
 *     'title'    => 'Rank 3',
 *     'subtitle' => 'You reached Level 3!',
 *     'image'    => 'https://.../badge.png',
 *     'color'    => '#9f40e2',
 *     'value'    => null,                      // the big number, for level-ups
 *     'icon'     => 'icon-achievement',
 *   ]
 *
 * Add events during a request, then hand them to the client with attach():
 *
 *   BR_Feedback::instance()->levelUp(7);
 *   BR_Feedback::instance()->achievement($achievement, $reason, true);
 *   $data = BR_Feedback::instance()->attach($data);
 *
 * This is for things a player EARNED. Ordinary acknowledgements ("Settings saved")
 * stay on the toast list - see brNotify() - and admin batch progress stays on the
 * op console. A full-screen panel for every save would be miserable.
 */
class BR_Feedback {

    private static $instance = null;
    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }
    private function __construct() {}

    /** @var array events collected during this request */
    private $events = [];

    // Verb and icon per event type. The verb completes "You just ..." in the panel
    // headline, so several events in one action read as one sentence:
    // "You just leveled up and earned an achievement".
    public static function types() {
        return [
            'levelup'        => [ 'verb' => __('leveled up','bluerabbit'),            'icon' => 'icon-star' ],
            'achievement'    => [ 'verb' => __('earned an achievement','bluerabbit'), 'icon' => 'icon-achievement' ],
            'rank'           => [ 'verb' => __('reached a new rank','bluerabbit'),    'icon' => 'icon-progression' ],
            'tabi'           => [ 'verb' => __('completed a Tabi','bluerabbit'),      'icon' => 'icon-sabotage' ],
            'quest'          => [ 'verb' => __('completed a milestone','bluerabbit'), 'icon' => 'icon-quest' ],
            'item_earned'    => [ 'verb' => __('earned an item','bluerabbit'),        'icon' => 'icon-box' ],
            'item_purchased' => [ 'verb' => __('bought an item','bluerabbit'),        'icon' => 'icon-bloo' ],
        ];
    }

    // ── Collecting ────────────────────────────────────────────────────────────

    public function add( $type, array $args = [] ) {
        $types = self::types();
        if ( ! isset( $types[ $type ] ) ) return $this;

        $this->events[] = [
            'type'     => $type,
            'verb'     => $args['verb']     ?? $types[ $type ]['verb'],
            'title'    => (string) ( $args['title']    ?? '' ),
            'subtitle' => (string) ( $args['subtitle'] ?? '' ),
            'image'    => (string) ( $args['image']    ?? '' ),
            'color'    => $args['color'] ? br_color_to_hex( $args['color'] ) : '',
            'value'    => $args['value'] ?? null,
            'icon'     => $args['icon'] ?? $types[ $type ]['icon'],
        ];
        return $this;
    }

    public function levelUp( $level ) {
        return $this->add( 'levelup', [
            'title' => sprintf( __('Level %d','bluerabbit'), (int) $level ),
            'value' => (int) $level,
        ] );
    }

    // $achievement is a br_achievements row; $is_rank switches the verb so a rank
    // reads as a promotion rather than just another badge.
    public function achievement( $achievement, $reason = '', $is_rank = false ) {
        if ( ! $achievement ) return $this;
        return $this->add( $is_rank ? 'rank' : 'achievement', [
            'title'    => $achievement->achievement_name ?? '',
            'subtitle' => $reason,
            'image'    => $achievement->achievement_badge ?? '',
            'color'    => $achievement->achievement_color ?? '',
        ] );
    }

    public function item( $item, $purchased = false ) {
        if ( ! $item ) return $this;
        return $this->add( $purchased ? 'item_purchased' : 'item_earned', [
            'title'    => $item->item_name ?? '',
            'subtitle' => $item->item_description ?? '',
            'image'    => $item->item_badge ?? '',
            'color'    => $item->item_color ?? '',
        ] );
    }

    // ── Handing over ──────────────────────────────────────────────────────────

    public function events() { return $this->events; }
    public function reset()  { $this->events = []; return $this; }

    // Merges the collected events into an AJAX payload under 'celebrate' and clears
    // them, so one request can never leak its celebration into the next.
    public function attach( array $data ) {
        if ( $this->events ) $data['celebrate'] = $this->events;
        $this->reset();
        return $data;
    }

    // Same, for the many handlers in this codebase that build their array inline.
    public function pull() {
        $events = $this->events;
        $this->reset();
        return $events;
    }

    // ── Pending queue ─────────────────────────────────────────────────────────
    //
    // An award and the moment the player is looking at the screen are not the same
    // moment. A tabi finishes because a quest was completed; a GM validates work
    // hours later; a rank lands during a batch. Handing the events back in the AJAX
    // response only works when the player themselves triggered the request - which
    // is why finishing a Tabi never showed anything on the journey page.
    //
    // So events are persisted against the player and drained the next time they look
    // at any page. Whoever drains first delivers, exactly once.

    public function queue( $player_id, $adventure_id, array $events = null ) {
        global $wpdb;
        $events = $events === null ? $this->pull() : $events;
        if ( ! $events || ! $player_id ) return $this;

        $wpdb->insert( "{$wpdb->prefix}br_feedback_queue", [
            'player_id'    => (int) $player_id,
            'adventure_id' => (int) $adventure_id,
            'payload'      => wp_json_encode( $events ),
            'created'      => current_time( 'mysql' ),
        ] );
        return $this;
    }

    // Returns every pending event for this player and clears them in the same breath,
    // so a reload can never replay a celebration the player already saw.
    public function popFor( $player_id, $adventure_id = null ) {
        global $wpdb;
        $player_id = (int) $player_id;
        if ( ! $player_id ) return [];

        $where  = "player_id = %d";
        $params = [ $player_id ];
        if ( $adventure_id ) { $where .= " AND adventure_id = %d"; $params[] = (int) $adventure_id; }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT queue_id, payload FROM {$wpdb->prefix}br_feedback_queue WHERE $where ORDER BY queue_id ASC",
            $params
        ) );
        if ( ! $rows ) return [];

        $ids    = array_map( function ( $r ) { return (int) $r->queue_id; }, $rows );
        $wpdb->query( "DELETE FROM {$wpdb->prefix}br_feedback_queue WHERE queue_id IN (" . implode( ',', $ids ) . ")" );

        $events = [];
        foreach ( $rows as $r ) {
            $decoded = json_decode( $r->payload, true );
            if ( is_array( $decoded ) ) $events = array_merge( $events, $decoded );
        }
        return $events;
    }

    public function countPending( $player_id ) {
        global $wpdb;
        if ( ! $player_id ) return 0;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_feedback_queue WHERE player_id = %d", (int) $player_id
        ) );
    }

    // ── Rules versioning ──────────────────────────────────────────────────────
    //
    // resetPlayer() costs ~43 queries. Running it on every journey-page view would
    // roughly triple the database load of the busiest page in the app, so it runs
    // only when it can actually find something new.
    //
    // It can only find something new for two reasons: the player's own progress
    // changed - which already runs resetPlayer() at the source, so it is handled -
    // or the RULES changed underneath them. Adventures therefore carry a version
    // that rule edits bump, and players carry the version they were last evaluated
    // against. Same number, nothing to do.

    // Called whenever conditions, ranks, or anything else that decides an award are
    // edited. $adventure_id is the PARENT - that is where rules live.
    public function bumpRules( $adventure_id ) {
        global $wpdb;
        $adventure_id = (int) $adventure_id;
        if ( ! $adventure_id ) return;
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}br_adventures SET rules_version = rules_version + 1 WHERE adventure_id = %d",
            $adventure_id
        ) );
    }

    // One indexed query: is this player evaluated against the current rules of the
    // adventure they are in (following the parent, where rules live)?
    public function needsSync( $player_id, $adventure_id ) {
        global $wpdb;
        $player_id    = (int) $player_id;
        $adventure_id = (int) $adventure_id;
        if ( ! $player_id || ! $adventure_id ) return false;

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT pa.synced_rules, rules.rules_version
             FROM {$wpdb->prefix}br_player_adventure pa
             JOIN {$wpdb->prefix}br_adventures adv  ON adv.adventure_id = pa.adventure_id
             JOIN {$wpdb->prefix}br_adventures rules
               ON rules.adventure_id = COALESCE(NULLIF(adv.adventure_parent, 0), adv.adventure_id)
             WHERE pa.player_id = %d AND pa.adventure_id = %d AND pa.player_adventure_status = 'in'",
            $player_id, $adventure_id
        ) );
        if ( ! $row ) return false;
        return (int) $row->synced_rules !== (int) $row->rules_version;
    }

    // Recorded once the player has been evaluated, so the next visit is a no-op.
    public function markSynced( $player_id, $adventure_id, $adv_parent_id ) {
        global $wpdb;
        $version = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT rules_version FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d", (int) $adv_parent_id
        ) );
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}br_player_adventure SET synced_rules = %d WHERE player_id = %d AND adventure_id = %d",
            $version, (int) $player_id, (int) $adventure_id
        ) );
    }

    // Builds events straight from resetPlayer()'s levelup/newly_earned output, which
    // is where nearly every award in the app already surfaces.
    public function fromPlayerState( $levelup, $new_level, $newly_earned ) {
        if ( $levelup ) $this->levelUp( $new_level );
        foreach ( (array) $newly_earned as $a ) {
            $this->add( ! empty( $a['is_rank'] ) ? 'rank' : 'achievement', [
                'title'    => $a['achievement_name']  ?? '',
                'subtitle' => $a['reason']            ?? '',
                'image'    => $a['achievement_badge'] ?? '',
                'color'    => $a['achievement_color'] ?? '',
            ] );
        }
        return $this;
    }
}

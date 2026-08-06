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

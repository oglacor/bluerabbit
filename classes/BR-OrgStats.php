<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_OrgStats {

    private static $instance = null;
    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }
    private function __construct() {}

    // Same whitelist as BR_Stats — always validate dimension against this before interpolating into SQL.
    const SEGMENT_DIMENSIONS = [
        'work_country'    => 'Country',
        'business_pillar' => 'Business Pillar',
        'work_function'   => 'Function',
        'work_level'      => 'Level',
        'player_gender'   => 'Gender',
        'work_cluster'    => 'Cluster',
    ];

    // ── KPI summary across all org adventures ─────────────────────────────────

    public function get_org_summary( int $org_id ): array {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                COUNT(DISTINCT pa.player_id)    AS total_players,
                COUNT(DISTINCT pa.adventure_id) AS total_adventures,
                COUNT(DISTINCT CASE WHEN pa.player_last_login >= DATE_SUB(NOW(), INTERVAL  7 DAY) THEN pa.player_id END) AS active_7d,
                COUNT(DISTINCT CASE WHEN pa.player_last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN pa.player_id END) AS active_30d
             FROM {$wpdb->prefix}br_player_adventure pa
             JOIN {$wpdb->prefix}br_org_adventure oa
                ON pa.adventure_id = oa.adventure_id AND oa.org_id = %d
             WHERE pa.player_adventure_status = 'in'
               AND pa.player_adventure_role   = 'player'",
            $org_id
        ), ARRAY_A );
        return array_map( 'intval', $row ?: [ 'total_players' => 0, 'total_adventures' => 0, 'active_7d' => 0, 'active_30d' => 0 ] );
    }

    // ── Per-adventure breakdown (first chart on stats tab) ────────────────────

    public function get_progress_by_adventure( int $org_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                a.adventure_id,
                a.adventure_title,
                a.adventure_badge,
                a.adventure_color,
                a.adventure_xp_label,
                COUNT(pa.player_id) AS enrolled_count,
                ROUND(AVG(pa.player_level), 1) AS avg_level,
                COUNT(CASE WHEN pa.player_last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) AS active_7d
             FROM {$wpdb->prefix}br_adventures a
             JOIN {$wpdb->prefix}br_org_adventure oa
                ON a.adventure_id = oa.adventure_id AND oa.org_id = %d
             LEFT JOIN {$wpdb->prefix}br_player_adventure pa
                ON a.adventure_id = pa.adventure_id
               AND pa.player_adventure_status = 'in'
               AND pa.player_adventure_role   = 'player'
             GROUP BY a.adventure_id, a.adventure_title, a.adventure_badge, a.adventure_color, a.adventure_xp_label
             ORDER BY enrolled_count DESC, a.adventure_title ASC",
            $org_id
        ), ARRAY_A ) ?: [];
    }

    // ── Daily active users aggregated across all org adventures ───────────────

    public function get_org_activity_heatmap( int $org_id, string $from = '', string $to = '', int $days = 90 ): array {
        global $wpdb;

        if ( $from && $to ) {
            $start = date( 'Y-m-d', strtotime( $from ) );
            $end   = date( 'Y-m-d', strtotime( $to ) );
        } else {
            $end   = date( 'Y-m-d' );
            $start = date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(al.log_date) AS date, COUNT(DISTINCT al.player_id) AS count
             FROM {$wpdb->prefix}br_activity_log al
             JOIN {$wpdb->prefix}br_org_adventure oa
                ON al.adventure_id = oa.adventure_id AND oa.org_id = %d
             JOIN {$wpdb->prefix}br_player_adventure pa
                ON al.player_id  = pa.player_id
               AND al.adventure_id = pa.adventure_id
               AND pa.player_adventure_status = 'in'
               AND pa.player_adventure_role   = 'player'
             WHERE DATE(al.log_date) >= %s AND DATE(al.log_date) <= %s
             GROUP BY DATE(al.log_date)
             ORDER BY date ASC",
            $org_id, $start, $end
        ), ARRAY_A ) ?: [];

        // Fill date gaps with zeros so the line chart has no breaks.
        $map = [];
        foreach ( $rows as $r ) $map[ $r['date'] ] = (int) $r['count'];
        $out = [];
        $cur = strtotime( $start );
        $endTs = strtotime( $end );
        while ( $cur <= $endTs ) {
            $d     = date( 'Y-m-d', $cur );
            $out[] = [ 'date' => $d, 'count' => $map[ $d ] ?? 0 ];
            $cur   = strtotime( '+1 day', $cur );
        }
        return [ 'rows' => $out, 'start' => $start, 'end' => $end ];
    }

    // ── Engagement across all org adventures ──────────────────────────────────
    //
    // Mirrors BR_Stats::get_adventure_engagement() but for every adventure in the
    // org at once: ~7 bulk queries total regardless of how many adventures the org
    // has, then all scoring happens in PHP. Safe for 1000+ players.
    //
    // A player enrolled in several org adventures is scored once per adventure
    // (that is what feeds the per-adventure chart), and their BEST score is what
    // represents them in the org-wide roll-up — someone on fire in one journey and
    // idle in another is an engaged person, not half a dormant one.

    const ENG_BUCKETS = [ 'on_fire', 'active', 'moderate', 'cooling_off', 'dormant', 'never_logged_in' ];

    public function get_org_engagement( int $org_id ): array {
        global $wpdb;

        $adventures = $wpdb->get_results( $wpdb->prepare(
            "SELECT a.adventure_id, a.adventure_title
             FROM {$wpdb->prefix}br_adventures a
             JOIN {$wpdb->prefix}br_org_adventure oa ON a.adventure_id = oa.adventure_id
             WHERE oa.org_id = %d
             ORDER BY a.adventure_title ASC",
            $org_id
        ), ARRAY_A ) ?: [];

        if ( empty( $adventures ) ) return $this->empty_engagement();

        $aids = array_map( 'intval', array_column( $adventures, 'adventure_id' ) );
        $aph  = implode( ',', array_fill( 0, count( $aids ), '%d' ) );

        // 1 — every (player, adventure) enrolment in the org
        $pairs = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, adventure_id, player_level, player_last_login
             FROM {$wpdb->prefix}br_player_adventure
             WHERE adventure_id IN ($aph)
               AND player_adventure_status = 'in'
               AND player_adventure_role   = 'player'",
            ...$aids
        ), ARRAY_A ) ?: [];

        if ( empty( $pairs ) ) return $this->empty_engagement( $adventures );

        // 2 — last activity-log date per (player, adventure)
        $log_map = $this->key_pair_map( $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, adventure_id, MAX(log_date) AS val
             FROM {$wpdb->prefix}br_activity_log
             WHERE adventure_id IN ($aph)
             GROUP BY player_id, adventure_id",
            ...$aids
        ), ARRAY_A ) );

        // 3 — published milestone count per adventure
        $quest_map = [];
        foreach ( $wpdb->get_results( $wpdb->prepare(
            "SELECT adventure_id, COUNT(*) AS cnt
             FROM {$wpdb->prefix}br_quests
             WHERE adventure_id IN ($aph)
               AND quest_status = 'publish'
               AND quest_type IN ('quest','challenge','survey','mission')
             GROUP BY adventure_id",
            ...$aids
        ), ARRAY_A ) ?: [] as $r ) $quest_map[ (int) $r['adventure_id'] ] = (int) $r['cnt'];

        // 4 — total completions per (player, adventure)
        $comp_map = $this->key_pair_map( $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, adventure_id, COUNT(*) AS val
             FROM {$wpdb->prefix}br_player_posts
             WHERE adventure_id IN ($aph)
             GROUP BY player_id, adventure_id",
            ...$aids
        ), ARRAY_A ) );

        // 5 — completions in the last 30 days per (player, adventure)
        $recent_map = $this->key_pair_map( $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, adventure_id, COUNT(*) AS val
             FROM {$wpdb->prefix}br_player_posts
             WHERE adventure_id IN ($aph)
               AND pp_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY player_id, adventure_id",
            ...$aids
        ), ARRAY_A ) );

        // 6 — shop transactions per (player, adventure)
        $txn_map = $this->key_pair_map( $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, adventure_id, COUNT(*) AS val
             FROM {$wpdb->prefix}br_transactions
             WHERE adventure_id IN ($aph)
             GROUP BY player_id, adventure_id",
            ...$aids
        ), ARRAY_A ) );

        // 7 — top level reached per adventure (the progression yardstick)
        $max_lvl = [];
        foreach ( $pairs as $p ) {
            $aid = (int) $p['adventure_id'];
            $max_lvl[ $aid ] = max( $max_lvl[ $aid ] ?? 0, (int) $p['player_level'] );
        }

        // ── Score every enrolment ─────────────────────────────────────────────
        $per_adv = [];
        foreach ( $adventures as $a ) {
            $per_adv[ (int) $a['adventure_id'] ] = [
                'adventure_id'    => (int) $a['adventure_id'],
                'adventure_title' => $a['adventure_title'],
                'distribution'    => array_fill_keys( self::ENG_BUCKETS, 0 ),
                'count'           => 0,
                'scored'          => 0,
                'sum'             => 0,
            ];
        }

        $best = [];  // player_id => best scored enrolment (null score = never logged in)
        $now  = time();

        foreach ( $pairs as $p ) {
            $pid = (int) $p['player_id'];
            $aid = (int) $p['adventure_id'];
            $key = $pid . ':' . $aid;
            if ( ! isset( $per_adv[ $aid ] ) ) continue;
            $per_adv[ $aid ]['count']++;

            $login_ts = ! empty( $p['player_last_login'] ) ? (int) strtotime( $p['player_last_login'] ) : 0;
            $log_ts   = isset( $log_map[ $key ] )          ? (int) strtotime( $log_map[ $key ] )        : 0;

            if ( $login_ts <= 0 && $log_ts <= 0 ) {
                $per_adv[ $aid ]['distribution']['never_logged_in']++;
                if ( ! array_key_exists( $pid, $best ) ) $best[ $pid ] = null;
                continue;
            }

            $total_q = $quest_map[ $aid ] ?? 0;
            $scores  = $this->score_enrolment(
                $now,
                max( $login_ts, $log_ts ),
                $recent_map[ $key ] ?? 0,
                $comp_map[ $key ]   ?? 0,
                $total_q,
                (int) $p['player_level'],
                $max_lvl[ $aid ]    ?? 0,
                $txn_map[ $key ]    ?? 0
            );

            $per_adv[ $aid ]['scored']++;
            $per_adv[ $aid ]['sum'] += $scores['total'];
            $per_adv[ $aid ]['distribution'][ $this->eng_bucket( $scores['total'] ) ]++;

            if ( empty( $best[ $pid ] ) || $scores['total'] > $best[ $pid ]['total'] ) {
                $best[ $pid ] = $scores;
            }
        }

        // ── Org-wide roll-up, one row per unique player ───────────────────────
        $dist   = array_fill_keys( self::ENG_BUCKETS, 0 );
        $sum    = 0;
        $scored = 0;
        $comp_sums = [ 'recency' => 0, 'frequency' => 0, 'completion' => 0, 'progression' => 0, 'economy' => 0 ];
        $avg_days_inactive = 0;
        $avg_recent_comp   = 0;
        $avg_comp_pct      = 0;

        foreach ( $best as $s ) {
            if ( $s === null ) { $dist['never_logged_in']++; continue; }
            $scored++;
            $sum += $s['total'];
            $dist[ $this->eng_bucket( $s['total'] ) ]++;
            foreach ( $comp_sums as $k => $_ ) $comp_sums[ $k ] += $s[ $k ];
            $avg_days_inactive += $s['days_inactive'];
            $avg_recent_comp   += $s['recent_completions'];
            $avg_comp_pct      += $s['completion_pct'];
        }

        $avg = function ( $v ) use ( $scored ) { return $scored > 0 ? round( $v / $scored, 1 ) : 0; };

        $by_adventure = [];
        foreach ( $per_adv as $row ) {
            $by_adventure[] = [
                'adventure_id'    => $row['adventure_id'],
                'adventure_title' => $row['adventure_title'],
                'count'           => $row['count'],
                'scored'          => $row['scored'],
                'avg_score'       => $row['scored'] > 0 ? (int) round( $row['sum'] / $row['scored'] ) : 0,
                'distribution'    => $row['distribution'],
            ];
        }
        usort( $by_adventure, function ( $a, $b ) {
            return $b['avg_score'] <=> $a['avg_score'] ?: strcasecmp( $a['adventure_title'], $b['adventure_title'] );
        } );

        return [
            'overall' => [
                'avg_score'     => $scored > 0 ? (int) round( $sum / $scored ) : 0,
                'level'         => $this->eng_bucket( $scored > 0 ? $sum / $scored : 0 ),
                'distribution'  => $dist,
                'count'         => count( $best ),
                'scored'        => $scored,
                'avg_breakdown' => [
                    'recency'     => [ 'score' => $avg( $comp_sums['recency'] ),     'max' => 25, 'avg_days'             => $avg( $avg_days_inactive ) ],
                    'frequency'   => [ 'score' => $avg( $comp_sums['frequency'] ),   'max' => 25, 'avg_completions_30d'  => $avg( $avg_recent_comp ) ],
                    'completion'  => [ 'score' => $avg( $comp_sums['completion'] ),  'max' => 25, 'avg_pct'              => $avg( $avg_comp_pct ) ],
                    'progression' => [ 'score' => $avg( $comp_sums['progression'] ), 'max' => 15 ],
                    'economy'     => [ 'score' => $avg( $comp_sums['economy'] ),     'max' => 10 ],
                ],
            ],
            'by_adventure' => $by_adventure,
        ];
    }

    // Same thresholds and weights as BR_Stats::get_adventure_engagement().
    private function score_enrolment( int $now, int $last_ts, int $recent_done, int $done, int $total_q, int $level, int $max_lvl, int $txns ): array {
        $days = max( 0, ( $now - $last_ts ) / 86400 );
        if      ( $days <= 1 )  $rec = 25;
        elseif  ( $days <= 3 )  $rec = 22;
        elseif  ( $days <= 7 )  $rec = 18;
        elseif  ( $days <= 14 ) $rec = 12;
        elseif  ( $days <= 30 ) $rec = 6;
        else                    $rec = max( 0, round( 25 - $days / 10 ) );

        $frq = $total_q > 0 ? (int) round( min( 1, $recent_done / max( 1, $total_q * 0.3 ) ) * 25 ) : 0;
        $cmp = $total_q > 0 ? (int) round( ( $done / $total_q ) * 25 ) : 0;
        $prg = $max_lvl > 0 ? (int) round( ( $level / $max_lvl ) * 15 ) : 0;
        $eco = min( 10, (int) round( $txns * 2 ) );

        return [
            'recency'            => $rec,
            'frequency'          => $frq,
            'completion'         => $cmp,
            'progression'        => $prg,
            'economy'            => $eco,
            'total'              => $rec + $frq + $cmp + $prg + $eco,
            'days_inactive'      => $days,
            'recent_completions' => $recent_done,
            'completion_pct'     => $total_q > 0 ? round( ( $done / $total_q ) * 100, 1 ) : 0,
        ];
    }

    private function eng_bucket( float $score ): string {
        if ( $score >= 80 ) return 'on_fire';
        if ( $score >= 60 ) return 'active';
        if ( $score >= 40 ) return 'moderate';
        if ( $score >= 20 ) return 'cooling_off';
        return 'dormant';
    }

    // Rows of (player_id, adventure_id, val) → [ "pid:aid" => val ].
    private function key_pair_map( $rows ): array {
        $map = [];
        foreach ( (array) $rows as $r ) {
            $map[ $r['player_id'] . ':' . $r['adventure_id'] ] = $r['val'];
        }
        return $map;
    }

    private function empty_engagement( array $adventures = [] ): array {
        return [
            'overall' => [
                'avg_score'     => 0,
                'level'         => 'dormant',
                'distribution'  => array_fill_keys( self::ENG_BUCKETS, 0 ),
                'count'         => 0,
                'scored'        => 0,
                'avg_breakdown' => [
                    'recency'     => [ 'score' => 0, 'max' => 25, 'avg_days' => 0 ],
                    'frequency'   => [ 'score' => 0, 'max' => 25, 'avg_completions_30d' => 0 ],
                    'completion'  => [ 'score' => 0, 'max' => 25, 'avg_pct' => 0 ],
                    'progression' => [ 'score' => 0, 'max' => 15 ],
                    'economy'     => [ 'score' => 0, 'max' => 10 ],
                ],
            ],
            'by_adventure' => array_map( function ( $a ) {
                return [
                    'adventure_id'    => (int) $a['adventure_id'],
                    'adventure_title' => $a['adventure_title'],
                    'count'           => 0,
                    'scored'          => 0,
                    'avg_score'       => 0,
                    'distribution'    => array_fill_keys( self::ENG_BUCKETS, 0 ),
                ];
            }, $adventures ),
        ];
    }

    // ── Player demographics, deduped across all org adventures ────────────────

    public function get_org_segment_breakdown( int $org_id, string $dimension ): array {
        global $wpdb;
        if ( ! array_key_exists( $dimension, self::SEGMENT_DIMENSIONS ) ) $dimension = 'work_country';

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                COALESCE(NULLIF(pm.{$dimension}, ''), 'Unknown') AS label,
                COUNT(DISTINCT pm.player_id)                      AS count
             FROM {$wpdb->prefix}br_player_meta pm
             JOIN (
                 SELECT player_id, MAX(player_meta_id) AS max_id
                 FROM {$wpdb->prefix}br_player_meta
                 GROUP BY player_id
             ) pm_latest ON pm.player_meta_id = pm_latest.max_id
             INNER JOIN (
                 SELECT DISTINCT pa.player_id
                 FROM {$wpdb->prefix}br_player_adventure pa
                 JOIN {$wpdb->prefix}br_org_adventure oa
                    ON pa.adventure_id = oa.adventure_id AND oa.org_id = %d
                 WHERE pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'
             ) org_players ON pm.player_id = org_players.player_id
             GROUP BY label
             ORDER BY count DESC
             LIMIT 20",
            $org_id
        ), ARRAY_A ) ?: [];

        return [
            'dimension' => $dimension,
            'label'     => self::SEGMENT_DIMENSIONS[ $dimension ],
            'segments'  => $rows,
        ];
    }
}

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

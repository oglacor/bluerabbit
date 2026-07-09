<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Stats {

    // ── Single user ──────────────────────────────────────────

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_summary( int $user_id, int $adventure_id ): array {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                pa.player_xp, pa.player_bloo, pa.player_ep, pa.player_level,
                pa.achievement_id, pa.player_adventure_role, pa.player_last_login,
                u.display_name, u.user_email,
                a.achievement_name AS rank_name, a.achievement_badge AS rank_badge,
                a.achievement_color AS rank_color
            FROM {$wpdb->prefix}br_player_adventure pa
            LEFT JOIN {$wpdb->users} u ON pa.player_id = u.ID
            LEFT JOIN {$wpdb->prefix}br_achievements a
                ON pa.achievement_id = a.achievement_id AND a.achievement_status = 'publish'
            WHERE pa.player_id = %d AND pa.adventure_id = %d",
            $user_id, $adventure_id
        ), ARRAY_A );

        if ( ! $row ) return [];

        $guild = $wpdb->get_row( $wpdb->prepare(
            "SELECT g.guild_name, g.guild_logo, g.guild_color
            FROM {$wpdb->prefix}br_player_guild pg
            JOIN {$wpdb->prefix}br_guilds g ON pg.guild_id = g.guild_id
            WHERE pg.player_id = %d AND pg.adventure_id = %d AND g.guild_status = 'publish'
            LIMIT 1",
            $user_id, $adventure_id
        ), ARRAY_A );

        $row['guild_name']  = $guild ? $guild['guild_name'] : '';
        $row['guild_logo']  = $guild ? $guild['guild_logo'] : '';
        $row['guild_color'] = $guild ? $guild['guild_color'] : '';

        $row['rank_position'] = $this->get_player_rank( $user_id, $adventure_id );

        $row['total_players'] = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );

        return $row;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_xp_history( int $user_id, int $adventure_id, int $days = 30 ): array {
        global $wpdb;

        // XP from encounters
        $energy_xp = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(timestamp) AS date, SUM(enc_xp) AS xp_gained
            FROM {$wpdb->prefix}br_player_energy_log
            WHERE player_id = %d AND adventure_id = %d
              AND timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(timestamp)",
            $user_id, $adventure_id, $days
        ), ARRAY_A );

        // XP from quest completions
        $quest_xp = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(pp.pp_date) AS date, SUM(q.mech_xp) AS xp_gained
            FROM {$wpdb->prefix}br_player_posts pp
            JOIN {$wpdb->prefix}br_quests q ON pp.quest_id = q.quest_id
            WHERE pp.player_id = %d AND pp.adventure_id = %d
              AND pp.pp_date >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(pp.pp_date)",
            $user_id, $adventure_id, $days
        ), ARRAY_A );

        // XP from achievements
        $ach_xp = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(pa.achievement_applied) AS date, SUM(a.achievement_xp) AS xp_gained
            FROM {$wpdb->prefix}br_player_achievement pa
            JOIN {$wpdb->prefix}br_achievements a ON pa.achievement_id = a.achievement_id
            WHERE pa.player_id = %d AND pa.adventure_id = %d
              AND pa.achievement_applied >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(pa.achievement_applied)",
            $user_id, $adventure_id, $days
        ), ARRAY_A );

        $merged = [];
        foreach ( array_merge( $energy_xp, $quest_xp, $ach_xp ) as $row ) {
            $d = $row['date'];
            if ( ! isset( $merged[ $d ] ) ) $merged[ $d ] = 0;
            $merged[ $d ] += (int) $row['xp_gained'];
        }

        $output = [];
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $d = date( 'Y-m-d', strtotime( "-{$i} days" ) );
            $output[] = [
                'date'      => $d,
                'xp_gained' => $merged[ $d ] ?? 0,
            ];
        }
        return $output;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_quest_progress( int $user_id, int $adventure_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                q.quest_id, q.quest_title, q.quest_type, q.quest_color, q.quest_icon,
                q.mech_xp, q.mech_bloo, q.quest_order,
                pp.pp_status AS status, pp.pp_date AS completed_at, pp.pp_grade AS score
            FROM {$wpdb->prefix}br_quests q
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON q.quest_id = pp.quest_id AND pp.player_id = %d AND pp.adventure_id = %d
            WHERE q.adventure_id = %d AND q.quest_status = 'publish'
              AND q.quest_type IN ('quest','challenge','survey','mission')
            ORDER BY q.quest_order ASC",
            $user_id, $adventure_id, $adventure_id
        ), ARRAY_A );
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_achievements( int $user_id, int $adventure_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                a.achievement_id, a.achievement_name, a.achievement_badge, a.achievement_color,
                a.achievement_xp, a.achievement_bloo, a.achievement_display, a.achievement_group,
                pa.achievement_applied AS earned_at
            FROM {$wpdb->prefix}br_achievements a
            LEFT JOIN {$wpdb->prefix}br_player_achievement pa
                ON a.achievement_id = pa.achievement_id AND pa.player_id = %d AND pa.adventure_id = %d
            WHERE a.adventure_id = %d AND a.achievement_status = 'publish'
            ORDER BY a.achievement_order ASC, a.achievement_id ASC",
            $user_id, $adventure_id, $adventure_id
        ), ARRAY_A );
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_guild( int $user_id, int $adventure_id ): array {
        global $wpdb;
        $guild = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                g.guild_id, g.guild_name, g.guild_logo, g.guild_color,
                g.guild_capacity,
                COUNT(pg2.player_id) AS member_count,
                COALESCE(SUM(pa.player_xp), 0) AS total_xp,
                COALESCE(SUM(pa.player_bloo), 0) AS total_bloo
            FROM {$wpdb->prefix}br_player_guild pg
            JOIN {$wpdb->prefix}br_guilds g ON pg.guild_id = g.guild_id
            LEFT JOIN {$wpdb->prefix}br_player_guild pg2 ON g.guild_id = pg2.guild_id
            LEFT JOIN {$wpdb->prefix}br_player_adventure pa
                ON pg2.player_id = pa.player_id AND pa.adventure_id = g.adventure_id
            WHERE pg.player_id = %d AND pg.adventure_id = %d AND g.guild_status = 'publish'
            GROUP BY g.guild_id
            LIMIT 1",
            $user_id, $adventure_id
        ), ARRAY_A );

        if ( ! $guild ) return [];

        $all_guilds = $wpdb->get_results( $wpdb->prepare(
            "SELECT g.guild_id, COALESCE(SUM(pa.player_xp), 0) AS total_xp
            FROM {$wpdb->prefix}br_guilds g
            LEFT JOIN {$wpdb->prefix}br_player_guild pg ON g.guild_id = pg.guild_id
            LEFT JOIN {$wpdb->prefix}br_player_adventure pa
                ON pg.player_id = pa.player_id AND pa.adventure_id = g.adventure_id
            WHERE g.adventure_id = %d AND g.guild_status = 'publish'
            GROUP BY g.guild_id
            ORDER BY total_xp DESC",
            $adventure_id
        ), ARRAY_A );

        $guild['rank'] = 1;
        foreach ( $all_guilds as $i => $g ) {
            if ( (int) $g['guild_id'] === (int) $guild['guild_id'] ) {
                $guild['rank'] = $i + 1;
                break;
            }
        }
        $guild['total_guilds'] = count( $all_guilds );

        return $guild;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_scorm_completions( int $user_id ): array {
        global $wpdb;
        $metas = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->usermeta}
            WHERE user_id = %d AND meta_key LIKE %s",
            $user_id, $wpdb->esc_like( 'br_scorm_lesson_status_' ) . '%'
        ), ARRAY_A );

        if ( empty( $metas ) ) return [];

        $step_ids = [];
        foreach ( $metas as $m ) {
            $step_ids[] = (int) str_replace( 'br_scorm_lesson_status_', '', $m['meta_key'] );
        }

        $placeholders = implode( ',', array_fill( 0, count( $step_ids ), '%d' ) );
        $steps = $wpdb->get_results( $wpdb->prepare(
            "SELECT step_id, step_title, quest_id FROM {$wpdb->prefix}br_steps WHERE step_id IN ($placeholders)",
            ...$step_ids
        ), OBJECT_K );

        $completions = [];
        foreach ( $metas as $m ) {
            $sid = (int) str_replace( 'br_scorm_lesson_status_', '', $m['meta_key'] );
            $completions[] = [
                'step_id'    => $sid,
                'step_title' => isset( $steps[ $sid ] ) ? $steps[ $sid ]->step_title : "Step #{$sid}",
                'status'     => $m['meta_value'],
                'quest_id'   => isset( $steps[ $sid ] ) ? (int) $steps[ $sid ]->quest_id : 0,
            ];
        }
        return $completions;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_rank( int $user_id, int $adventure_id ): int {
        global $wpdb;
        $players = $wpdb->get_col( $wpdb->prepare(
            "SELECT player_id FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'
            ORDER BY player_xp DESC",
            $adventure_id
        ) );
        $pos = array_search( $user_id, $players );
        return $pos !== false ? $pos + 1 : 0;
    }

    // ── Manager view ─────────────────────────────────────────

    // Portable: swap $wpdb for PDO to migrate.
    public function get_adventure_summary( int $adventure_id ): array {
        global $wpdb;
        $summary = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                COUNT(*) AS total_players,
                ROUND(AVG(player_xp)) AS avg_xp,
                SUM(player_xp) AS total_xp,
                SUM(player_bloo) AS total_bloo
            FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ), ARRAY_A );

        $summary['active_7d'] = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT player_id) FROM {$wpdb->prefix}br_activity_log
            WHERE adventure_id = %d AND log_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $adventure_id
        ) );

        $total_quests = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish' AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );

        $total_completions = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_posts pp
            JOIN {$wpdb->prefix}br_player_adventure pa
                ON pp.player_id = pa.player_id AND pp.adventure_id = pa.adventure_id
            WHERE pp.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'",
            $adventure_id
        ) );

        $possible = (int) $summary['total_players'] * $total_quests;
        $summary['completion_pct'] = $possible > 0 ? round( ( $total_completions / $possible ) * 100, 1 ) : 0;

        // Available XP from published non-locked quests
        $summary['available_xp'] = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT IFNULL(SUM(mech_xp), 0) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish' AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );

        return $summary;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_all_players( int $adventure_id, int $limit = 30, int $offset = 0 ): array {
        global $wpdb;

        $total_quests = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish' AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );

        $players = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                pa.player_id, pa.player_xp, pa.player_bloo, pa.player_ep, pa.player_level,
                pa.player_last_login, pa.player_date_enrolled,
                u.display_name, u.user_email,
                COUNT(pp.quest_id) AS quests_completed
            FROM {$wpdb->prefix}br_player_adventure pa
            LEFT JOIN {$wpdb->users} u ON pa.player_id = u.ID
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON pa.player_id = pp.player_id AND pa.adventure_id = pp.adventure_id
            WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'
            GROUP BY pa.player_id
            ORDER BY pa.player_xp DESC
            LIMIT %d OFFSET %d",
            $adventure_id, $limit, $offset
        ), ARRAY_A );

        foreach ( $players as &$p ) {
            $p['completion_pct'] = $total_quests > 0
                ? round( ( (int) $p['quests_completed'] / $total_quests ) * 100, 1 )
                : 0;
        }

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );

        return [ 'players' => $players, 'total' => $total, 'total_quests' => $total_quests ];
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_quest_funnel( int $adventure_id ): array {
        global $wpdb;
        $total_players = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );

        $quests = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                q.quest_id, q.quest_title, q.quest_type, q.quest_order, q.quest_status,
                COUNT(pp.player_id) AS completed_count
            FROM {$wpdb->prefix}br_quests q
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON q.quest_id = pp.quest_id AND pp.adventure_id = %d
            WHERE q.adventure_id = %d AND q.quest_status IN ('publish','locked')
              AND q.quest_type IN ('quest','challenge','survey','mission')
            GROUP BY q.quest_id
            ORDER BY q.quest_order ASC",
            $adventure_id, $adventure_id
        ), ARRAY_A );

        foreach ( $quests as &$q ) {
            $q['started_count'] = $total_players;
            $q['is_locked'] = ( $q['quest_status'] === 'locked' && (int) $q['completed_count'] === 0 );
        }

        return $quests;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_xp_distribution( int $adventure_id ): array {
        global $wpdb;
        $max_xp = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(player_xp) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );

        if ( $max_xp <= 0 ) return [];

        $bucket_size = max( 1, ceil( $max_xp / 10 ) );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                FLOOR(player_xp / %d) AS bucket,
                COUNT(*) AS count
            FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'
            GROUP BY bucket
            ORDER BY bucket ASC",
            $bucket_size, $adventure_id
        ), ARRAY_A );

        $output = [];
        foreach ( $rows as $r ) {
            $low  = (int) $r['bucket'] * $bucket_size;
            $high = $low + $bucket_size - 1;
            $output[] = [
                'label' => number_format( $low ) . '-' . number_format( $high ),
                'count' => (int) $r['count'],
            ];
        }
        return $output;
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_activity_heatmap( int $adventure_id, int $days = 30, string $from = '', string $to = '' ): array {
        global $wpdb;

        if ( $from && $to ) {
            $start = date( 'Y-m-d', strtotime( $from ) );
            $end   = date( 'Y-m-d', strtotime( $to ) );
        } else {
            $end   = date( 'Y-m-d' );
            $start = date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(log_date) AS date, COUNT(DISTINCT player_id) AS count
            FROM {$wpdb->prefix}br_activity_log
            WHERE adventure_id = %d AND DATE(log_date) >= %s AND DATE(log_date) <= %s
            GROUP BY DATE(log_date)
            ORDER BY date ASC",
            $adventure_id, $start, $end
        ), ARRAY_A );

        $map = [];
        foreach ( $rows as $r ) $map[ $r['date'] ] = (int) $r['count'];

        $output  = [];
        $current = strtotime( $start );
        $last    = strtotime( $end );
        while ( $current <= $last ) {
            $d = date( 'Y-m-d', $current );
            $output[] = [ 'date' => $d, 'count' => $map[ $d ] ?? 0 ];
            $current += 86400;
        }
        return $output;
    }

    // ── Tabi / Type / Activity / Engagement ─────────────────

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_tabi_progress( int $user_id, int $adventure_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                t.tabi_id, t.tabi_name, t.tabi_color,
                COUNT(q.quest_id) AS total_quests,
                COUNT(pp.quest_id) AS completed_quests
            FROM {$wpdb->prefix}br_tabis t
            LEFT JOIN {$wpdb->prefix}br_quests q
                ON t.tabi_id = q.tabi_id AND q.quest_status = 'publish'
                AND q.quest_type IN ('quest','challenge','survey','mission')
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON q.quest_id = pp.quest_id AND pp.player_id = %d AND pp.adventure_id = %d
            WHERE t.adventure_id = %d AND t.tabi_status = 'publish'
            GROUP BY t.tabi_id
            ORDER BY t.tabi_id ASC",
            $user_id, $adventure_id, $adventure_id
        ), ARRAY_A );
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_type_completion( int $user_id, int $adventure_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                q.quest_type,
                COUNT(q.quest_id) AS total,
                COUNT(pp.quest_id) AS completed
            FROM {$wpdb->prefix}br_quests q
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON q.quest_id = pp.quest_id AND pp.player_id = %d AND pp.adventure_id = %d
            WHERE q.adventure_id = %d AND q.quest_status = 'publish'
              AND q.quest_type IN ('quest','challenge','survey','mission')
            GROUP BY q.quest_type
            ORDER BY q.quest_type ASC",
            $user_id, $adventure_id, $adventure_id
        ), ARRAY_A );
    }

    // Portable: swap $wpdb for PDO to migrate.
    public function get_player_last_activity( int $user_id, int $adventure_id ): array {
        global $wpdb;

        $last_login = $wpdb->get_var( $wpdb->prepare(
            "SELECT player_last_login FROM {$wpdb->prefix}br_player_adventure
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );

        $last_quest = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(pp_date) FROM {$wpdb->prefix}br_player_posts
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );

        $last_log = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(log_date) FROM {$wpdb->prefix}br_activity_log
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );

        $now = time();

        return [
            'last_login'          => $last_login,
            'last_quest'          => $last_quest,
            'last_activity'       => $last_log,
            'days_since_login'    => ( $last_login && strtotime( $last_login ) > 0 )
                ? round( ( $now - strtotime( $last_login ) ) / 86400, 1 ) : null,
            'days_since_quest'    => ( $last_quest && strtotime( $last_quest ) > 0 )
                ? round( ( $now - strtotime( $last_quest ) ) / 86400, 1 ) : null,
            'days_since_activity' => ( $last_log && strtotime( $last_log ) > 0 )
                ? round( ( $now - strtotime( $last_log ) ) / 86400, 1 ) : null,
        ];
    }

    // Portable: swap $wpdb for PDO to migrate.
    // Engagement score 0-100 from five weighted components.
    public function get_player_engagement( int $user_id, int $adventure_id ): array {
        global $wpdb;

        // ── Recency (0-25): days since last login or activity ──
        $last_login_ts = strtotime( $wpdb->get_var( $wpdb->prepare(
            "SELECT player_last_login FROM {$wpdb->prefix}br_player_adventure
            WHERE player_id = %d AND adventure_id = %d", $user_id, $adventure_id
        ) ) ?: '1970-01-01' );
        $last_log_ts = strtotime( $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(log_date) FROM {$wpdb->prefix}br_activity_log
            WHERE player_id = %d AND adventure_id = %d", $user_id, $adventure_id
        ) ) ?: '1970-01-01' );
        $most_recent   = max( $last_login_ts, $last_log_ts );
        $days_inactive = max( 0, ( time() - $most_recent ) / 86400 );

        if      ( $days_inactive <= 1 )  $recency = 25;
        elseif  ( $days_inactive <= 3 )  $recency = 22;
        elseif  ( $days_inactive <= 7 )  $recency = 18;
        elseif  ( $days_inactive <= 14 ) $recency = 12;
        elseif  ( $days_inactive <= 30 ) $recency = 6;
        else                             $recency = max( 0, round( 25 - $days_inactive / 10 ) );

        // ── Frequency (0-25): completions in last 30 d relative to available ──
        $recent = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_posts
            WHERE player_id = %d AND adventure_id = %d AND pp_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $user_id, $adventure_id
        ) );
        $total_q = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish'
              AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );
        $freq_ratio = $total_q > 0 ? min( 1, $recent / max( 1, $total_q * 0.3 ) ) : 0;
        $frequency  = (int) round( $freq_ratio * 25 );

        // ── Completion (0-25): % of all quests done ──
        $done = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_posts
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );
        $comp_pct   = $total_q > 0 ? $done / $total_q : 0;
        $completion = (int) round( $comp_pct * 25 );

        // ── Progression (0-15): level vs best player in adventure ──
        $level = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT player_level FROM {$wpdb->prefix}br_player_adventure
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );
        $max_level = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(player_level) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );
        $progression = $max_level > 0 ? (int) round( ( $level / $max_level ) * 15 ) : 0;

        // ── Economy (0-10): shop / item transactions ──
        $txns    = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
            WHERE player_id = %d AND adventure_id = %d",
            $user_id, $adventure_id
        ) );
        $economy = min( 10, (int) round( $txns * 2 ) );

        $total = $recency + $frequency + $completion + $progression + $economy;

        if      ( $total >= 80 ) $eng_level = 'on_fire';
        elseif  ( $total >= 60 ) $eng_level = 'active';
        elseif  ( $total >= 40 ) $eng_level = 'moderate';
        elseif  ( $total >= 20 ) $eng_level = 'cooling_off';
        else                     $eng_level = 'dormant';

        return [
            'score' => $total,
            'level' => $eng_level,
            'breakdown' => [
                'recency'     => [ 'score' => $recency,     'max' => 25, 'days_inactive' => round( $days_inactive, 1 ) ],
                'frequency'   => [ 'score' => $frequency,   'max' => 25, 'recent_completions' => $recent ],
                'completion'  => [ 'score' => $completion,   'max' => 25, 'pct' => round( $comp_pct * 100, 1 ) ],
                'progression' => [ 'score' => $progression,  'max' => 15, 'level' => $level ],
                'economy'     => [ 'score' => $economy,      'max' => 10, 'transactions' => $txns ],
            ],
            'days_inactive' => round( $days_inactive, 1 ),
        ];
    }

    // ── Adventure-wide tabi + engagement ────────────────────

    // Portable: swap $wpdb for PDO to migrate.
    public function get_adventure_tabi_completion( int $adventure_id ): array {
        global $wpdb;

        $total_players = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ) );

        $tabis = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                t.tabi_id, t.tabi_name, t.tabi_color,
                COUNT(DISTINCT q.quest_id) AS total_quests,
                COUNT(pp.player_id) AS total_completions
            FROM {$wpdb->prefix}br_tabis t
            LEFT JOIN {$wpdb->prefix}br_quests q
                ON t.tabi_id = q.tabi_id AND q.quest_status = 'publish'
                AND q.quest_type IN ('quest','challenge','survey','mission')
            LEFT JOIN {$wpdb->prefix}br_player_posts pp
                ON q.quest_id = pp.quest_id AND pp.adventure_id = %d
            WHERE t.adventure_id = %d AND t.tabi_status = 'publish'
            GROUP BY t.tabi_id
            ORDER BY t.tabi_id ASC",
            $adventure_id, $adventure_id
        ), ARRAY_A );

        foreach ( $tabis as &$t ) {
            $possible = $total_players * (int) $t['total_quests'];
            $t['completion_pct'] = $possible > 0
                ? round( ( (int) $t['total_completions'] / $possible ) * 100, 1 )
                : 0;
            $t['total_players'] = $total_players;
        }
        return $tabis;
    }

    // Portable: swap $wpdb for PDO to migrate.
    // Bulk-computes engagement for every enrolled player in ~7 queries,
    // then scores in PHP. Safe for 1000+ players.
    public function get_adventure_engagement( int $adventure_id ): array {
        global $wpdb;

        // 1 — all players (level, last_login)
        $players = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, player_level, player_last_login
            FROM {$wpdb->prefix}br_player_adventure
            WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_adventure_role = 'player'",
            $adventure_id
        ), ARRAY_A );

        if ( empty( $players ) ) {
            return [ 'avg_score' => 0, 'distribution' => array_fill_keys(
                [ 'on_fire', 'active', 'moderate', 'cooling_off', 'dormant', 'never_logged_in' ], 0
            ), 'count' => 0, 'scored' => 0 ];
        }

        $pids = array_column( $players, 'player_id' );
        $ph   = implode( ',', array_fill( 0, count( $pids ), '%d' ) );
        $pmap = [];
        foreach ( $players as $p ) $pmap[ $p['player_id'] ] = $p;

        // 2 — last activity-log date per player
        $log_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, MAX(log_date) AS last_log
            FROM {$wpdb->prefix}br_activity_log
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $log_map = [];
        foreach ( $log_rows as $r ) $log_map[ $r['player_id'] ] = $r['last_log'];

        // 3 — total quest count
        $total_q = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish'
              AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );

        // 4 — total completions per player
        $comp_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS done
            FROM {$wpdb->prefix}br_player_posts
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $comp_map = [];
        foreach ( $comp_rows as $r ) $comp_map[ $r['player_id'] ] = (int) $r['done'];

        // 5 — recent completions (30 d)
        $rec_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS done
            FROM {$wpdb->prefix}br_player_posts
            WHERE adventure_id = %d AND player_id IN ($ph)
              AND pp_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $rec_map = [];
        foreach ( $rec_rows as $r ) $rec_map[ $r['player_id'] ] = (int) $r['done'];

        // 6 — max level
        $max_lvl = (int) max( array_column( $players, 'player_level' ) );

        // 7 — transaction counts
        $txn_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS cnt
            FROM {$wpdb->prefix}br_transactions
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $txn_map = [];
        foreach ( $txn_rows as $r ) $txn_map[ $r['player_id'] ] = (int) $r['cnt'];

        // ── Score every player in PHP ───────────────────────
        $dist  = [ 'on_fire' => 0, 'active' => 0, 'moderate' => 0, 'cooling_off' => 0, 'dormant' => 0, 'never_logged_in' => 0 ];
        $sum   = 0;
        $comp_sums = [ 'recency' => 0, 'frequency' => 0, 'completion' => 0, 'progression' => 0, 'economy' => 0 ];
        $avg_days_inactive = 0;
        $avg_recent_comp   = 0;
        $avg_comp_pct      = 0;
        $scored = 0;
        $now   = time();

        foreach ( $pids as $pid ) {
            $p = $pmap[ $pid ];

            $has_login = ! empty( $p['player_last_login'] ) && strtotime( $p['player_last_login'] ) > 0;
            $has_log   = isset( $log_map[ $pid ] );

            if ( ! $has_login && ! $has_log ) {
                $dist['never_logged_in']++;
                continue;
            }

            $scored++;

            // recency
            $login_ts = $has_login ? strtotime( $p['player_last_login'] ) : 0;
            $log_ts   = $has_log   ? strtotime( $log_map[ $pid ] )       : 0;
            $days     = max( 0, ( $now - max( $login_ts, $log_ts ) ) / 86400 );
            if      ( $days <= 1 )  $rec = 25;
            elseif  ( $days <= 3 )  $rec = 22;
            elseif  ( $days <= 7 )  $rec = 18;
            elseif  ( $days <= 14 ) $rec = 12;
            elseif  ( $days <= 30 ) $rec = 6;
            else                    $rec = max( 0, round( 25 - $days / 10 ) );

            // frequency
            $rd    = $rec_map[ $pid ] ?? 0;
            $frq   = $total_q > 0 ? (int) round( min( 1, $rd / max( 1, $total_q * 0.3 ) ) * 25 ) : 0;

            // completion
            $dn    = $comp_map[ $pid ] ?? 0;
            $cmp   = $total_q > 0 ? (int) round( ( $dn / $total_q ) * 25 ) : 0;

            // progression
            $prg   = $max_lvl > 0 ? (int) round( ( (int) $p['player_level'] / $max_lvl ) * 15 ) : 0;

            // economy
            $eco   = min( 10, (int) round( ( $txn_map[ $pid ] ?? 0 ) * 2 ) );

            $total = $rec + $frq + $cmp + $prg + $eco;
            $sum  += $total;

            $comp_sums['recency']     += $rec;
            $comp_sums['frequency']   += $frq;
            $comp_sums['completion']  += $cmp;
            $comp_sums['progression'] += $prg;
            $comp_sums['economy']     += $eco;
            $avg_days_inactive += $days;
            $avg_recent_comp   += $rd;
            $avg_comp_pct      += $total_q > 0 ? round( ( $dn / $total_q ) * 100, 1 ) : 0;

            if      ( $total >= 80 ) $dist['on_fire']++;
            elseif  ( $total >= 60 ) $dist['active']++;
            elseif  ( $total >= 40 ) $dist['moderate']++;
            elseif  ( $total >= 20 ) $dist['cooling_off']++;
            else                     $dist['dormant']++;
        }

        $avg_breakdown = [
            'recency'     => [ 'score' => $scored > 0 ? round( $comp_sums['recency'] / $scored, 1 ) : 0,     'max' => 25, 'avg_days' => $scored > 0 ? round( $avg_days_inactive / $scored, 1 ) : 0 ],
            'frequency'   => [ 'score' => $scored > 0 ? round( $comp_sums['frequency'] / $scored, 1 ) : 0,   'max' => 25, 'avg_completions_30d' => $scored > 0 ? round( $avg_recent_comp / $scored, 1 ) : 0 ],
            'completion'  => [ 'score' => $scored > 0 ? round( $comp_sums['completion'] / $scored, 1 ) : 0,   'max' => 25, 'avg_pct' => $scored > 0 ? round( $avg_comp_pct / $scored, 1 ) : 0 ],
            'progression' => [ 'score' => $scored > 0 ? round( $comp_sums['progression'] / $scored, 1 ) : 0, 'max' => 15 ],
            'economy'     => [ 'score' => $scored > 0 ? round( $comp_sums['economy'] / $scored, 1 ) : 0,     'max' => 10 ],
        ];

        return [
            'avg_score'     => $scored > 0 ? (int) round( $sum / $scored ) : 0,
            'distribution'  => $dist,
            'count'         => count( $pids ),
            'scored'        => $scored,
            'avg_breakdown' => $avg_breakdown,
        ];
    }

    // Whitelist of player_meta columns exposed as segment dimensions.
    // Never interpolate a client-supplied column name into SQL - always
    // validate against this map first (also re-validated in the AJAX handler).
    const SEGMENT_DIMENSIONS = [
        'work_country'    => 'Country',
        'business_pillar' => 'Business Pillar',
        'work_function'   => 'Function',
        'work_level'      => 'Level',
        'player_gender'   => 'Gender',
        'work_cluster'    => 'Cluster',
    ];

    // Portable: swap $wpdb for PDO to migrate.
    // Same bulk-then-score-in-PHP pattern as get_adventure_engagement(), but
    // grouped by a whitelisted br_player_meta column instead of one global bucket.
    public function get_engagement_by_segment( int $adventure_id, string $dimension ): array {
        global $wpdb;

        if ( ! array_key_exists( $dimension, self::SEGMENT_DIMENSIONS ) ) {
            $dimension = 'work_country';
        }

        // 1 — all players (xp, level, last_login) + their segment value.
        // player_meta has no unique index on player_id, so pick one row
        // deterministically via MAX(player_meta_id) rather than GROUP BY.
        $players = $wpdb->get_results( $wpdb->prepare(
            "SELECT pa.player_id, pa.player_xp, pa.player_level, pa.player_last_login,
                COALESCE(NULLIF(pm.{$dimension}, ''), 'Unknown') AS segment
            FROM {$wpdb->prefix}br_player_adventure pa
            LEFT JOIN {$wpdb->prefix}br_player_meta pm
                ON pm.player_meta_id = (
                    SELECT MAX(player_meta_id) FROM {$wpdb->prefix}br_player_meta
                    WHERE player_id = pa.player_id
                )
            WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'",
            $adventure_id
        ), ARRAY_A );

        if ( empty( $players ) ) {
            return [ 'dimension' => $dimension, 'label' => self::SEGMENT_DIMENSIONS[ $dimension ], 'coverage_pct' => 0, 'segments' => [] ];
        }

        $pids = array_column( $players, 'player_id' );
        $ph   = implode( ',', array_fill( 0, count( $pids ), '%d' ) );
        $pmap = [];
        foreach ( $players as $p ) $pmap[ $p['player_id'] ] = $p;

        // 2 — last activity-log date per player
        $log_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, MAX(log_date) AS last_log
            FROM {$wpdb->prefix}br_activity_log
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $log_map = [];
        foreach ( $log_rows as $r ) $log_map[ $r['player_id'] ] = $r['last_log'];

        // 3 — total quest count
        $total_q = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = %d AND quest_status = 'publish'
              AND quest_type IN ('quest','challenge','survey','mission')",
            $adventure_id
        ) );

        // 4 — total completions per player
        $comp_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS done
            FROM {$wpdb->prefix}br_player_posts
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $comp_map = [];
        foreach ( $comp_rows as $r ) $comp_map[ $r['player_id'] ] = (int) $r['done'];

        // 5 — recent completions (30 d)
        $rec_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS done
            FROM {$wpdb->prefix}br_player_posts
            WHERE adventure_id = %d AND player_id IN ($ph)
              AND pp_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $rec_map = [];
        foreach ( $rec_rows as $r ) $rec_map[ $r['player_id'] ] = (int) $r['done'];

        // 6 — max level
        $max_lvl = (int) max( array_column( $players, 'player_level' ) );

        // 7 — transaction counts
        $txn_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT player_id, COUNT(*) AS cnt
            FROM {$wpdb->prefix}br_transactions
            WHERE adventure_id = %d AND player_id IN ($ph)
            GROUP BY player_id",
            $adventure_id, ...$pids
        ), ARRAY_A );
        $txn_map = [];
        foreach ( $txn_rows as $r ) $txn_map[ $r['player_id'] ] = (int) $r['cnt'];

        // ── Score every player in PHP, bucketed by segment ──────
        $buckets     = []; // segment label => running sums
        $known_count = 0;
        $now         = time();

        foreach ( $pids as $pid ) {
            $p       = $pmap[ $pid ];
            $segment = $p['segment'];

            $has_login = ! empty( $p['player_last_login'] ) && strtotime( $p['player_last_login'] ) > 0;
            $has_log   = isset( $log_map[ $pid ] );

            if ( ! $has_login && ! $has_log ) {
                $rec = 0;
            } else {
                $login_ts = $has_login ? strtotime( $p['player_last_login'] ) : 0;
                $log_ts   = $has_log   ? strtotime( $log_map[ $pid ] )       : 0;
                $days     = max( 0, ( $now - max( $login_ts, $log_ts ) ) / 86400 );
                if      ( $days <= 1 )  $rec = 25;
                elseif  ( $days <= 3 )  $rec = 22;
                elseif  ( $days <= 7 )  $rec = 18;
                elseif  ( $days <= 14 ) $rec = 12;
                elseif  ( $days <= 30 ) $rec = 6;
                else                    $rec = max( 0, round( 25 - $days / 10 ) );
            }

            $rd  = $rec_map[ $pid ] ?? 0;
            $frq = $total_q > 0 ? (int) round( min( 1, $rd / max( 1, $total_q * 0.3 ) ) * 25 ) : 0;

            $dn       = $comp_map[ $pid ] ?? 0;
            $cmp      = $total_q > 0 ? (int) round( ( $dn / $total_q ) * 25 ) : 0;
            $comp_pct = $total_q > 0 ? ( $dn / $total_q ) * 100 : 0;

            $prg = $max_lvl > 0 ? (int) round( ( (int) $p['player_level'] / $max_lvl ) * 15 ) : 0;
            $eco = min( 10, (int) round( ( $txn_map[ $pid ] ?? 0 ) * 2 ) );

            $score = $rec + $frq + $cmp + $prg + $eco;

            if ( ! isset( $buckets[ $segment ] ) ) {
                $buckets[ $segment ] = [ 'count' => 0, 'score_sum' => 0, 'xp_sum' => 0, 'comp_pct_sum' => 0 ];
            }
            $buckets[ $segment ]['count']++;
            $buckets[ $segment ]['score_sum']    += $score;
            $buckets[ $segment ]['xp_sum']       += (int) $p['player_xp'];
            $buckets[ $segment ]['comp_pct_sum'] += $comp_pct;

            if ( $segment !== 'Unknown' ) $known_count++;
        }

        // "Unknown" always shown on its own; long tail beyond the top 8
        // segments (by headcount) rolls into "Other" to avoid a huge legend.
        $unknown = $buckets['Unknown'] ?? null;
        unset( $buckets['Unknown'] );
        uasort( $buckets, function ( $a, $b ) { return $b['count'] <=> $a['count']; } );

        $top  = array_slice( $buckets, 0, 8, true );
        $rest = array_slice( $buckets, 8, null, true );

        if ( ! empty( $rest ) ) {
            $other = [ 'count' => 0, 'score_sum' => 0, 'xp_sum' => 0, 'comp_pct_sum' => 0 ];
            foreach ( $rest as $r ) {
                $other['count']        += $r['count'];
                $other['score_sum']    += $r['score_sum'];
                $other['xp_sum']       += $r['xp_sum'];
                $other['comp_pct_sum'] += $r['comp_pct_sum'];
            }
            $top['Other'] = $other;
        }
        if ( $unknown ) $top['Unknown'] = $unknown;

        $segments = [];
        foreach ( $top as $label => $b ) {
            $segments[] = [
                'label'              => $label,
                'count'              => $b['count'],
                'avg_score'          => $b['count'] > 0 ? (int) round( $b['score_sum'] / $b['count'] ) : 0,
                'avg_xp'             => $b['count'] > 0 ? (int) round( $b['xp_sum'] / $b['count'] ) : 0,
                'avg_completion_pct' => $b['count'] > 0 ? round( $b['comp_pct_sum'] / $b['count'], 1 ) : 0,
            ];
        }

        return [
            'dimension'    => $dimension,
            'label'        => self::SEGMENT_DIMENSIONS[ $dimension ],
            'coverage_pct' => count( $pids ) > 0 ? round( ( $known_count / count( $pids ) ) * 100, 1 ) : 0,
            'segments'     => $segments,
        ];
    }

    // Portable: swap $wpdb for PDO to migrate.
    // Reuses the same guild leaderboard pattern from page-guilds.php
    public function get_guild_leaderboard( int $adventure_id ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT
                g.guild_id, g.guild_name, g.guild_logo, g.guild_color, g.guild_capacity,
                COUNT(pg.player_id) AS member_count,
                COALESCE(SUM(pa.player_xp), 0) AS total_xp,
                COALESCE(SUM(pa.player_bloo), 0) AS total_bloo
            FROM {$wpdb->prefix}br_guilds g
            LEFT JOIN {$wpdb->prefix}br_player_guild pg ON g.guild_id = pg.guild_id
            LEFT JOIN {$wpdb->prefix}br_player_adventure pa
                ON pg.player_id = pa.player_id AND pa.adventure_id = g.adventure_id
            WHERE g.adventure_id = %d AND g.guild_status = 'publish'
            GROUP BY g.guild_id
            ORDER BY total_xp DESC",
            $adventure_id
        ), ARRAY_A );
    }
}

<?php
/**
 * Golden-master harness for BR_Player::resetPlayer().
 *
 * resetPlayer() is the single choke point for XP, currency, levels, unlocks,
 * requirements, ranks and condition awards. It has no tests and it cannot be
 * refactored safely on inspection alone - the only trustworthy check is that the
 * new implementation returns byte-identical results to the old one for a wide
 * spread of real players.
 *
 *   php tools/reset-player-baseline.php --capture          # before refactoring
 *   ...refactor...
 *   php tools/reset-player-baseline.php --compare          # must report NO DIFFS
 *
 * Run --capture on a database you are happy to leave as-is: resetPlayer writes
 * (it recomputes and stores xp/bloo/ep/level), so the harness snapshots and
 * restores the rows it touches.
 *
 * CLI only.
 */
// Two independent checks, because what this file can do (mint auth cookies,
// run the award pass for arbitrary players) is worth more than one line of
// defence. PHP_SAPI is 'cli' only for the command line; a web request always
// carries REQUEST_METHOD, whatever SAPI is serving it.
if (PHP_SAPI !== 'cli' || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    exit('CLI only');
}

define('WP_USE_THEMES', false);
require dirname(__DIR__, 4) . '/wp-load.php';
global $wpdb;

$opt   = getopt('', ['capture', 'compare', 'players::', 'adventure::', 'file::']);
$LIMIT = (int) ($opt['players']   ?? 200);
$ADV   = (int) ($opt['adventure'] ?? 0);
// Written OUTSIDE the webroot by default. The capture contains player submissions
// (pp_content), quest content and success messages, and anything sitting in the
// theme directory is served straight over HTTP - a .htaccess protects Apache and
// does nothing at all for nginx. Keeping it out of the document root is the part
// that holds regardless of server.
$FILE  = $opt['file'] ?? rtrim( sys_get_temp_dir(), '/\\' ) . '/br-reset-player-baseline.json';
$MODE  = isset($opt['compare']) ? 'compare' : (isset($opt['capture']) ? 'capture' : '');
if (!$MODE) exit("usage: --capture | --compare  [--players=N] [--adventure=ID] [--file=path]\n");

// A spread of real players: different adventures, levels and amounts of progress,
// because the interesting bugs live in the branches only some players reach.
$where = $ADV ? $wpdb->prepare('AND pa.adventure_id = %d', $ADV) : '';
$rows  = $wpdb->get_results($wpdb->prepare(
    "SELECT pa.player_id, pa.adventure_id
     FROM {$wpdb->prefix}br_player_adventure pa
     JOIN {$wpdb->prefix}br_adventures a ON a.adventure_id = pa.adventure_id AND a.adventure_status = 'publish'
     WHERE pa.player_adventure_status = 'in' $where
     ORDER BY (SELECT COUNT(*) FROM {$wpdb->prefix}br_player_posts pp
               WHERE pp.player_id = pa.player_id AND pp.adventure_id = pa.adventure_id) DESC,
              pa.player_level DESC, pa.player_id ASC
     LIMIT %d", $LIMIT));

// resetPlayer() writes. Snapshot every row it can touch so a capture run leaves
// the database exactly as it found it.
$ids = array_map(fn($r) => "({$r->player_id},{$r->adventure_id})", $rows);
$pa_before  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE (player_id, adventure_id) IN (" . implode(',', $ids) . ")", ARRAY_A);
$ach_before = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_player_achievement", ARRAY_A);
$q_before   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_feedback_queue", ARRAY_A);

// Volatile values: things that legitimately differ between two runs and would drown
// a real regression in noise.
//
// resetPlayer returns arrays of stdClass rows straight from $wpdb, and
// array_walk_recursive does NOT descend into objects - so timestamps buried in a
// result set were never normalised. A single UPDATE to a quest during unrelated
// testing then reported 134 false "quests" diffs, on quest_date_modified. Convert
// to plain arrays first so the whole tree is reachable.
function normalise($data) {
    unset($data['debug'], $data['celebrate']);
    $data = json_decode(json_encode($data), true);
    array_walk_recursive($data, function (&$v) {
        if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $v)) $v = '<timestamp>';
        if (is_numeric($v)) $v = 0 + $v;   // '4' and 4 are the same answer
    });
    return $data;
}

$results = [];
$t0 = microtime(true);
foreach ($rows as $r) {
    wp_set_current_user($r->player_id);
    $out = BR_Player::instance()->resetPlayer($r->adventure_id, $r->player_id);
    $results["{$r->player_id}:{$r->adventure_id}"] = normalise($out);
}
$elapsed = microtime(true) - $t0;

// Restore
$wpdb->query("DELETE FROM {$wpdb->prefix}br_player_achievement");
foreach ($ach_before as $a) $wpdb->insert("{$wpdb->prefix}br_player_achievement", $a);
$wpdb->query("DELETE FROM {$wpdb->prefix}br_feedback_queue");
foreach ($q_before as $a) $wpdb->insert("{$wpdb->prefix}br_feedback_queue", $a);
foreach ($pa_before as $a) {
    $wpdb->update("{$wpdb->prefix}br_player_adventure", $a,
        ['player_id' => $a['player_id'], 'adventure_id' => $a['adventure_id']]);
}

printf("%d players in %.1fs (%.1f ms each)\n", count($results), $elapsed, $elapsed * 1000 / max(1, count($results)));

if ($MODE === 'capture') {
    file_put_contents($FILE, json_encode($results, JSON_PRETTY_PRINT));
    printf("baseline written to %s (%s KB)\n", $FILE, number_format(filesize($FILE) / 1024));
    exit;
}

if (!file_exists($FILE)) exit("no baseline at $FILE - run --capture first\n");
$baseline = json_decode(file_get_contents($FILE), true);

$diffs = 0;
foreach ($baseline as $key => $before) {
    if (!isset($results[$key])) { printf("MISSING  %s\n", $key); $diffs++; continue; }
    $after = $results[$key];
    foreach ($before as $field => $value) {
        if (json_encode($value) !== json_encode($after[$field] ?? null)) {
            printf("DIFF     %s -> %s\n         was: %s\n         now: %s\n", $key, $field,
                substr(json_encode($value), 0, 160), substr(json_encode($after[$field] ?? null), 0, 160));
            $diffs++;
        }
    }
}
printf("\n%s\n", $diffs ? "$diffs DIFFERENCES — the refactor changed behaviour" : 'NO DIFFS — behaviour is identical');
exit($diffs ? 1 : 0);

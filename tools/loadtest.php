<?php
/**
 * Journey-page capacity test.
 *
 * Answers "how many requests per second can this survive, and where does it bend"
 * by RAMPING concurrency in short bursts and stopping at the first sign of trouble.
 *
 *   php tools/loadtest.php                          # safe ramp, ~2 minutes
 *   php tools/loadtest.php --url=https://staging.example.com
 *   php tools/loadtest.php --mode=sync              # the award endpoint instead
 *
 * WHY A RAMP AND NOT A FLOOD
 * An earlier version of this tool took --users/--loads/--window and fired that many
 * requests at that rate no matter what. Point it at a box that cannot drain the
 * queue and the queue simply grows: requests pile up, memory follows, and the
 * machine stops responding - which is exactly what happened. Throughput is a rate
 * question, and a rate question is answered by finding the knee, not by sustaining
 * a guess for ten minutes. 19,500 requests tell you nothing 500 well-chosen ones do
 * not.
 *
 * SAFETY
 * - Stops the moment p95 passes --abort-p95 or any request fails.
 * - Never runs more than --max-concurrency in flight.
 * - Writes results after every step, so a crash still leaves you the data.
 * - Warns when the target is local, because then the generator is stealing CPU
 *   from the thing it is measuring and every number is pessimistic.
 *
 * CLI only.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

define('WP_USE_THEMES', false);
require dirname(__DIR__, 4) . '/wp-load.php';
global $wpdb;

$opt = getopt('', [
    'url::', 'adventure::', 'mode::', 'steps::', 'burst::',
    'max-concurrency::', 'abort-p95::', 'out::', 'flood::',
]);
$BASE      = rtrim($opt['url'] ?? get_bloginfo('url'), '/');
$ADV       = (int) ($opt['adventure'] ?? 17);
$MODE      = $opt['mode'] ?? 'page';
$BURST     = (int) ($opt['burst'] ?? 30);              // requests per step
$MAXCONC   = (int) ($opt['max-concurrency'] ?? 32);
$ABORT_P95 = (int) ($opt['abort-p95'] ?? 5000);        // ms
$OUT       = $opt['out'] ?? __DIR__ . '/loadtest-results.json';

$players = $wpdb->get_col($wpdb->prepare(
    "SELECT player_id FROM {$wpdb->prefix}br_player_adventure
     WHERE adventure_id=%d AND player_adventure_status='in' LIMIT 200", $ADV));
if (!$players) exit("no enrolled players in adventure $ADV\n");

$expiry  = time() + 7200;
$cookies = [];
foreach ($players as $pid) $cookies[$pid] = LOGGED_IN_COOKIE . '=' . wp_generate_auth_cookie($pid, $expiry, 'logged_in');

$page_url = "$BASE/adventure/?adventure_id=$ADV";
$ajax_url = admin_url('admin-ajax.php');

$is_local = (bool) preg_match('#//(localhost|127\.0\.0\.1)#i', $BASE);

// --mode=sync runs the real award pass for the real player whose cookie it borrows.
// Against a live site that means granting their achievements and DRAINING their
// pending celebration queue - so the celebration they were owed is delivered to a
// curl handle and they never see it. Read-only page loads are fine; this is not.
if ($MODE === 'sync' && !$is_local && !isset($opt['i-understand-this-writes'])) {
    exit("REFUSED: --mode=sync against a non-local target would grant achievements and\n"
       . "consume real players' pending celebrations. Run it locally, or pass\n"
       . "--i-understand-this-writes if the target is genuinely disposable.\n");
}

// On a live site the players are real and so is the traffic they are already
// generating. Start small, stay small, and let the ramp's own abort do the rest.
if (!$is_local && $MAXCONC > 8) {
    echo "note        : capping concurrency at 8 for a remote target (was $MAXCONC).\n";
    echo "              raise it deliberately with --max-concurrency once you have seen the curve.\n";
    $MAXCONC = 8;
}
echo "target      : " . ($MODE === 'sync' ? $ajax_url . ' [brSyncRewards]' : $page_url) . "\n";
echo "ramp        : " . $BURST . " requests per step, stopping at p95 > {$ABORT_P95}ms or any error\n";
if ($is_local) {
    echo "\n  ! target is THIS machine. The generator competes with the server for CPU,\n";
    echo "    so every number below is worse than production would be. Use --url= to\n";
    echo "    point at staging for a figure you can plan with.\n";
}
echo "\n";

/**
 * Proves the target is actually serving the journey page to a logged-in player
 * before any timing is believed.
 *
 * Without this the tool happily measures the speed of the WRONG thing: a redirect
 * to login (auth cookies do not validate across hosts - the salts in wp-config
 * differ), a 404 template, or a full-page cache. All of those return HTTP 200 in a
 * few milliseconds, which reads as spectacular throughput and means nothing.
 */
function assert_real_page($url, $cookie, $mode) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Cookie: ' . $cookie],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false)  return [false, 'no response at all (host unreachable?)'];
    if ($code !== 200)    return [false, "HTTP $code - not a page load"];

    $len = strlen($body);
    if ($mode === 'sync') {
        if (strpos($body, '"success"') === false) return [false, "response is not the AJAX payload ({$len} bytes)"];
        return [true, "AJAX responded, {$len} bytes"];
    }
    // The journey page is a logged-in player view. A login form means the cookie was
    // rejected; a tiny body means something other than the page answered.
    if (stripos($body, 'name="log"') !== false || stripos($body, 'user_login') !== false) {
        return [false, "served the LOGIN page ({$len} bytes) - auth cookies are not valid on this host"];
    }
    if ($len < 5000) return [false, "body is only {$len} bytes - too small to be the journey page"];
    if (stripos($body, 'adventure') === false) return [false, "body does not mention the adventure ({$len} bytes)"];
    return [true, number_format($len) . ' bytes, looks like the real page'];
}

function run_burst($n, $conc, $cookies, $players, $url, $post) {
    $mh = curl_multi_init();
    $active = []; $lat = []; $codes = []; $sent = 0; $done = 0;
    while ($done < $n) {
        while ($sent < $n && count($active) < $conc) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Cookie: ' . $cookies[$players[$sent % count($players)]]],
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_ENCODING       => 'gzip',
            ]);
            if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
            curl_multi_add_handle($mh, $ch);
            $active[(int) $ch] = microtime(true);
            $sent++;
        }
        curl_multi_exec($mh, $running);
        if ($running) curl_multi_select($mh, 0.05);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle'];
            $lat[] = (microtime(true) - $active[(int) $ch]) * 1000;
            $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $codes[$code] = ($codes[$code] ?? 0) + 1;
            unset($active[(int) $ch]);
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
            $done++;
        }
    }
    curl_multi_close($mh);
    return [$lat, $codes];
}

// Prove we are measuring the right thing before measuring anything.
[$valid, $why] = assert_real_page(
    $MODE === 'sync' ? $ajax_url : $page_url,
    $cookies[$players[0]], $MODE
);
echo "sanity check: $why\n";
if (!$valid) {
    echo "\nABORTED. Every number this tool could produce would be measuring the wrong\n";
    echo "response. Fix the target first:\n";
    echo "  - auth cookies only validate on the site whose salts this wp-config holds,\n";
    echo "    so --url must point at an installation sharing them\n";
    echo "  - a full-page cache in front of the site serves HTML without running PHP\n";
    echo "  - check the adventure id exists and the players are enrolled in it\n";
    exit(1);
}

// The query counter reads the database THIS script is connected to. Point the test
// at another host and it is counting an idle local database, not the one under load.
$remote = !preg_match('#//(localhost|127\.0\.0\.1)#i', $BASE);
if ($remote) echo "note        : queries/req is blank for a remote target - it can only count the local database\n";

// Warm-up, discarded. The first request into a cold PHP opcache and a cold InnoDB
// buffer pool costs several seconds; letting that land in the first measured burst
// puts a 5-second outlier straight into p95 and aborts the ramp before it starts.
echo "warming up...";
run_burst(5, 2, $cookies, $players,
    $MODE === 'sync' ? $ajax_url : $page_url,
    $MODE === 'sync' ? ['action' => 'brSyncRewards', 'adventure_id' => $ADV] : null);
echo " done\n\n";

$results = [];
$best    = ['rps' => 0, 'conc' => 0];
printf("%-6s %-9s %-9s %-9s %-11s %s\n", 'CONC', 'req/s', 'p50', 'p95', 'queries/req', 'status');

foreach ([1, 2, 4, 8, 12, 16, 24, 32, 48, 64] as $conc) {
    if ($conc > $MAXCONC) break;

    $q0 = (int) $wpdb->get_var("SHOW GLOBAL STATUS LIKE 'Questions'", 1);
    $t0 = microtime(true);
    [$lat, $codes] = run_burst($BURST, $conc, $cookies, $players,
        $MODE === 'sync' ? $ajax_url : $page_url,
        $MODE === 'sync' ? ['action' => 'brSyncRewards', 'adventure_id' => $ADV] : null);
    $wall = microtime(true) - $t0;
    $q    = (int) $wpdb->get_var("SHOW GLOBAL STATUS LIKE 'Questions'", 1) - $q0;

    sort($lat);
    $p   = fn($x) => $lat[min(count($lat) - 1, (int) floor(count($lat) * $x))];
    $rps = $BURST / $wall;
    $ok  = ($codes[200] ?? 0) === $BURST;

    $row = ['concurrency' => $conc, 'rps' => round($rps, 1), 'p50' => round($p(0.5)),
            'p95' => round($p(0.95)), 'queries_per_req' => round($q / $BURST, 1), 'status' => $codes];
    $results[] = $row;
    file_put_contents($OUT, json_encode($results, JSON_PRETTY_PRINT));  // survive a crash

    printf("%-6d %-9.1f %-9.0f %-9.0f %-11.1f %s\n", $conc, $rps, $p(0.5), $p(0.95), $q / $BURST, json_encode($codes));

    if ($rps > $best['rps']) $best = ['rps' => $rps, 'conc' => $conc];

    if (!$ok)                  { echo "\nSTOPPED: non-200 responses - this is the ceiling.\n"; break; }
    if ($p(0.95) > $ABORT_P95) { echo "\nSTOPPED: p95 above {$ABORT_P95}ms - past the knee, no point pushing further.\n"; break; }
}

printf("\nbest sustained: %.1f req/s at concurrency %d\n", $best['rps'], $best['conc']);
printf("results written to %s\n\n", $OUT);

echo "what that supports, if every player loads the journey page N times in 10 minutes:\n";
foreach ([5, 10, 15] as $loads) {
    $supported = $best['rps'] * 600 / $loads;
    printf("   %2d loads each  ->  %s concurrent players%s\n", $loads, number_format($supported),
        $is_local ? ' (pessimistic: measured on the same box)' : '');
}

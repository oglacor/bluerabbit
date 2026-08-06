<?php
/**
 * Journey-page load test.
 *
 * Simulates N distinct logged-in players hitting /adventure/ concurrently, using
 * real WordPress auth cookies so the request costs exactly what a real one costs.
 *
 *   php loadtest.php --users=2000 --loads=15 --window=600 --concurrency=40
 *
 * Reports latency percentiles and, more usefully, the MySQL query count the run
 * actually caused - the database is what falls over first here, not PHP.
 *
 * CLI only. Never expose this over HTTP.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

define('WP_USE_THEMES', false);
require 'c:/xampp/htdocs/br1/wp-load.php';
global $wpdb;

$opt = getopt('', ['users::', 'loads::', 'window::', 'concurrency::', 'adventure::', 'url::', 'mode::']);
// page : the journey page only (what a browser fetches first)
// sync : the brSyncRewards AJAX only - the expensive award pass, fired by the page
//        ONLY when the rules moved since that player was last evaluated
// both : what a real browser does after a rule change
$MODE = $opt['mode'] ?? 'page';
$USERS       = (int) ($opt['users']       ?? 50);
$LOADS       = (int) ($opt['loads']       ?? 3);
$WINDOW      = (int) ($opt['window']      ?? 30);   // seconds to spread the load over
$CONCURRENCY = (int) ($opt['concurrency'] ?? 20);
$ADV         = (int) ($opt['adventure']   ?? 17);
$BASE        = rtrim($opt['url'] ?? get_bloginfo('url'), '/');

$players = $wpdb->get_col($wpdb->prepare(
    "SELECT player_id FROM {$wpdb->prefix}br_player_adventure
     WHERE adventure_id=%d AND player_adventure_status='in' LIMIT %d", $ADV, $USERS));
if (!$players) exit("no enrolled players in adventure $ADV\n");

// Real auth cookies, one per simulated player.
$expiry  = time() + 3600;
$cookies = [];
foreach ($players as $pid) {
    $cookies[$pid] = LOGGED_IN_COOKIE . '=' . wp_generate_auth_cookie($pid, $expiry, 'logged_in');
}

$page_url = "$BASE/adventure/?adventure_id=$ADV";
$ajax_url = admin_url('admin-ajax.php');
$url      = $MODE === 'sync' ? $ajax_url : $page_url;
$total    = count($players) * $LOADS;
$rate     = $total / max(1, $WINDOW);
$interval = 1 / $rate;

printf("target : %s players x %d loads = %s requests over %ds (%.1f req/s)\n",
    number_format(count($players)), $LOADS, number_format($total), $WINDOW, $rate);
printf("url    : %s\nconcurrency: %d\n\n", $url, $CONCURRENCY);

// Baseline for the database counter, so we can attribute queries to this run.
$q_before = (int) $wpdb->get_var("SHOW GLOBAL STATUS LIKE 'Questions'", 1);

$queue = [];
for ($i = 0; $i < $total; $i++) $queue[] = $players[$i % count($players)];
shuffle($queue);

$mh = curl_multi_init();
$active = []; $lat = []; $codes = []; $sent = 0; $done = 0; $total_extra = 0;
$start = microtime(true);

function spawn($mh, &$active, $url, $cookie, $post = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Cookie: ' . $cookie],
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_ENCODING       => 'gzip',
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_multi_add_handle($mh, $ch);
    $active[(int) $ch] = microtime(true);
}

while ($done < $total + $total_extra) {
    // Feed the pipe at the target rate, never exceeding the concurrency cap.
    $elapsed = microtime(true) - $start;
    while ($sent < $total && count($active) < $CONCURRENCY && $elapsed >= $sent * $interval) {
        $cookie = $cookies[$queue[$sent]];
        if ($MODE === 'sync') {
            spawn($mh, $active, $ajax_url, $cookie, ['action' => 'brSyncRewards', 'adventure_id' => $ADV]);
        } else {
            spawn($mh, $active, $page_url, $cookie);
            // A browser that is told to sync fires the AJAX straight after the page.
            if ($MODE === 'both') {
                spawn($mh, $active, $ajax_url, $cookie, ['action' => 'brSyncRewards', 'adventure_id' => $ADV]);
                $total_extra++;
            }
        }
        $sent++;
    }

    curl_multi_exec($mh, $running);
    if ($running) curl_multi_select($mh, 0.05);

    while ($info = curl_multi_info_read($mh)) {
        $ch = $info['handle'];
        $lat[]   = (microtime(true) - $active[(int) $ch]) * 1000;
        $code    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $codes[$code] = ($codes[$code] ?? 0) + 1;
        unset($active[(int) $ch]);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        $done++;
        if ($done % 25 === 0) printf("\r  %d/%d done…", $done, $total);
    }
    if (!$running && $sent >= $total && !$active) break;
}
curl_multi_close($mh);

$wall = microtime(true) - $start;
$q_after = (int) $wpdb->get_var("SHOW GLOBAL STATUS LIKE 'Questions'", 1);
sort($lat);
$p = fn($x) => $lat ? $lat[min(count($lat) - 1, (int) floor(count($lat) * $x))] : 0;

printf("\r%-30s\n\n", '');
printf("completed   %s requests in %.1fs (%.1f req/s achieved)\n", number_format($done), $wall, $done / $wall);
printf("status      %s\n", json_encode($codes));
printf("latency     p50 %.0f ms   p95 %.0f ms   p99 %.0f ms   max %.0f ms\n", $p(0.5), $p(0.95), $p(0.99), end($lat));
printf("mysql       %s queries total, %.1f per request, %.0f queries/s\n",
    number_format($q_after - $q_before), ($q_after - $q_before) / max(1, $done), ($q_after - $q_before) / $wall);
if (!empty($codes[200]) && $codes[200] < $done) echo "\nWARNING: non-200 responses - check the status map above.\n";

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
$is_manager = ($isGM || $isAdmin || $isNPC);
$view_user_id = $is_manager && isset($_GET['uid']) ? (int)$_GET['uid'] : get_current_user_id();
$stats = new BR_Stats();

$p_summary      = $stats->get_player_summary($view_user_id, $adv_child_id);
$p_quests       = $stats->get_player_quest_progress($view_user_id, $adv_child_id);
$p_achievements = $stats->get_player_achievements($view_user_id, $adv_child_id);
$p_guild        = $stats->get_player_guild($view_user_id, $adv_child_id);
$p_scorm        = $stats->get_player_scorm_completions($view_user_id);
$p_tabis        = $stats->get_player_tabi_progress($view_user_id, $adv_child_id);
$p_types        = $stats->get_player_type_completion($view_user_id, $adv_child_id);
$p_last         = $stats->get_player_last_activity($view_user_id, $adv_child_id);
$p_engagement   = $stats->get_player_engagement($view_user_id, $adv_child_id);

$page     = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
$per_page = 50;

if ($is_manager) {
    $adv_summary      = $stats->get_adventure_summary($adv_child_id);
    $all_players_data = $stats->get_all_players($adv_child_id, $per_page, ($page - 1) * $per_page);
    $adv_tabis        = $stats->get_adventure_tabi_completion($adv_child_id);
    $adv_engagement   = $stats->get_adventure_engagement($adv_child_id);
}
?>

<script>
window.brStats = {
    ajaxurl: '<?= admin_url("admin-ajax.php"); ?>',
    nonce: '<?= wp_create_nonce("br_stats_nonce"); ?>',
    adventureId: <?= (int)$adv_child_id; ?>,
    userId: <?= (int)$view_user_id; ?>,
    isManager: <?= $is_manager ? 'true' : 'false'; ?>,
    labels: {
        xp: '<?= esc_js($xp_label); ?>',
        bloo: '<?= esc_js($bloo_label); ?>',
        ep: '<?= esc_js($ep_label); ?>'
    },
    adventureTitle: '<?= esc_js($adventure->adventure_title); ?>',
    typeCompletion: <?= json_encode($p_types); ?>,
    engagement: <?= json_encode($p_engagement); ?>
};
</script>

<div class="br-stats-page">

<?php if($is_manager){ ?>
<!-- ═══════════════════ MANAGER VIEW ═══════════════════ -->
<div class="br-stats-manager">

    <div class="br-stats-section-title">
        <span class="icon icon-skill"></span>
        <h2><?= __("Adventure Dashboard", "bluerabbit"); ?></h2>
    </div>

    <!-- KPI Boxes -->
    <div class="br-stats-kpis">
        <div class="br-stats-kpi">
            <span class="br-stats-kpi-value"><?= number_format($adv_summary['total_players']); ?></span>
            <span class="br-stats-kpi-label"><?= __("Total Players", "bluerabbit"); ?></span>
        </div>
        <div class="br-stats-kpi accent">
            <span class="br-stats-kpi-value"><?= number_format($adv_summary['active_7d']); ?></span>
            <span class="br-stats-kpi-label"><?= __("Active 7d", "bluerabbit"); ?></span>
        </div>
        <div class="br-stats-kpi purple">
            <span class="br-stats-kpi-value"><?= number_format($adv_summary['avg_xp']); ?></span>
            <span class="br-stats-kpi-label"><?= __("Avg", "bluerabbit"); ?> <?= $xp_label; ?></span>
        </div>
        <div class="br-stats-kpi green">
            <span class="br-stats-kpi-value"><?= $adv_summary['completion_pct']; ?>%</span>
            <span class="br-stats-kpi-label"><?= __("Completion", "bluerabbit"); ?></span>
        </div>
    </div>

    <!-- Manager Charts -->
    <div class="br-stats-charts-row">
        <div class="br-stats-panel br-stats-half">
            <h3><?= __("Quest Funnel", "bluerabbit"); ?></h3>
            <div class="br-stats-chart-wrap">
                <canvas id="br-quest-funnel-chart"></canvas>
            </div>
        </div>
        <div class="br-stats-panel br-stats-half">
            <h3><?= __("XP Distribution", "bluerabbit"); ?></h3>
            <div class="br-stats-chart-wrap">
                <canvas id="br-xp-distribution-chart"></canvas>
            </div>
        </div>
    </div>

    <div class="br-stats-panel">
        <h3><?= __("Daily Active Users", "bluerabbit"); ?></h3>
        <div class="br-stats-chart-wrap">
            <canvas id="br-activity-chart"></canvas>
        </div>
    </div>

    <!-- Tabi Completion + Engagement Distribution -->
    <div class="br-stats-charts-row">
        <?php if (!empty($adv_tabis)) { ?>
        <div class="br-stats-panel br-stats-half">
            <h3><?= __("Tabi Completion Rate", "bluerabbit"); ?></h3>
            <div class="br-stats-quest-list">
                <?php foreach ($adv_tabis as $at) { ?>
                <div class="br-stats-quest-row">
                    <div class="br-stats-quest-info">
                        <span class="icon icon-tabi"></span>
                        <span class="br-stats-quest-title"><?= esc_html($at['tabi_name']); ?></span>
                    </div>
                    <div class="br-stats-quest-bar-wrap">
                        <?php $at_class = $at['completion_pct'] >= 100 ? 'complete' : ($at['completion_pct'] > 0 ? 'in-progress' : 'locked'); ?>
                        <div class="br-stats-quest-bar <?= $at_class; ?>" style="width:<?= $at['completion_pct']; ?>%"></div>
                    </div>
                    <span class="br-stats-quest-status <?= $at_class; ?>"><?= $at['completion_pct']; ?>%</span>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <div class="br-stats-panel br-stats-half">
            <h3><?= __("Engagement Overview", "bluerabbit"); ?></h3>
            <div class="br-stats-engagement-overview">
                <?php
                $avg_eng = $adv_engagement['avg_score'];
                $avg_circ = 326.73;
                $avg_off  = round($avg_circ * (1 - $avg_eng / 100), 2);
                $avg_lvl  = $avg_eng >= 80 ? 'on_fire' : ($avg_eng >= 60 ? 'active' : ($avg_eng >= 40 ? 'moderate' : ($avg_eng >= 20 ? 'cooling_off' : 'dormant')));
                $avg_colors = ['on_fire'=>'#f7cb15','active'=>'#24da98','moderate'=>'#1cc2eb','cooling_off'=>'#ff9800','dormant'=>'#f44336'];
                $avg_col = $avg_colors[$avg_lvl];
                ?>
                <div class="br-stats-engagement-avg">
                    <svg viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="<?= $avg_col; ?>" stroke-width="8"
                                stroke-dasharray="<?= $avg_circ; ?>" stroke-dashoffset="<?= $avg_off; ?>"
                                transform="rotate(-90 60 60)" stroke-linecap="round"/>
                        <text x="60" y="52" text-anchor="middle" fill="#ffffff" font-size="28" font-weight="900" font-family="proxima-nova-extra-condensed,sans-serif"><?= $avg_eng; ?></text>
                        <text x="60" y="70" text-anchor="middle" fill="<?= $avg_col; ?>" font-size="9" font-weight="700">AVG SCORE</text>
                    </svg>
                </div>
                <div class="br-stats-engagement-dist">
                    <?php
                    $dist_items = [
                        'on_fire'    => ['label' => __("On Fire", "bluerabbit"),    'color' => '#f7cb15'],
                        'active'     => ['label' => __("Active", "bluerabbit"),     'color' => '#24da98'],
                        'moderate'   => ['label' => __("Moderate", "bluerabbit"),   'color' => '#1cc2eb'],
                        'cooling_off'=> ['label' => __("Cooling Off", "bluerabbit"),'color' => '#ff9800'],
                        'dormant'    => ['label' => __("Dormant", "bluerabbit"),    'color' => '#f44336'],
                        'never_logged_in'    => ['label' => __("Never Logged In", "bluerabbit"),    'color' => '#607d8b'],
                    ];
                    $eng_total = max(1, $adv_engagement['count']);
                    foreach ($dist_items as $dk => $di) {
                        $dcount = $adv_engagement['distribution'][$dk] ?? 0;
                        $dpct = round(($dcount / $eng_total) * 100);
                    ?>
                    <div class="br-stats-eng-row">
                        <span class="br-stats-eng-label" style="color:<?= $di['color']; ?>"><?= $di['label']; ?></span>
                        <div class="br-stats-eng-bar-wrap">
                            <div class="br-stats-eng-bar" style="width:<?= $dpct; ?>%;background:<?= $di['color']; ?>"></div>
                        </div>
                        <span class="br-stats-eng-score"><?= $dcount; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Engagement Breakdown -->
    <div class="br-stats-panel">
        <h3><?= __("Engagement Breakdown", "bluerabbit"); ?></h3>
        <div class="br-stats-kpis br-stats-kpis-5">
            <?php
            $eng_kpi = [
                'recency'     => ['label' => __("Recency", "bluerabbit"),     'color' => '#1cc2eb', 'icon' => 'clock',       'detail' => round($p_engagement['breakdown']['recency']['days_inactive']) . 'd inactive'],
                'frequency'   => ['label' => __("Frequency", "bluerabbit"),   'color' => '#24da98', 'icon' => 'quest',       'detail' => $p_engagement['breakdown']['frequency']['recent_completions'] . ' in 30d'],
                'completion'  => ['label' => __("Completion", "bluerabbit"),  'color' => '#9f40e2', 'icon' => 'check',       'detail' => $p_engagement['breakdown']['completion']['pct'] . '% done'],
                'progression' => ['label' => __("Progression", "bluerabbit"), 'color' => '#f7cb15', 'icon' => 'level-up',    'detail' => 'Lv. ' . $p_engagement['breakdown']['progression']['level']],
                'economy'     => ['label' => __("Economy", "bluerabbit"),     'color' => '#ff9800', 'icon' => 'item-shop',   'detail' => $p_engagement['breakdown']['economy']['transactions'] . ' txns'],
            ];
            foreach ($p_engagement['breakdown'] as $key => $comp) {
                $meta = $eng_kpi[$key];
            ?>
            <div class="br-stats-kpi br-stats-kpi-eng" style="border-color: <?= $meta['color']; ?>33">
                <span class="br-stats-kpi-value" style="color:<?= $meta['color']; ?>"><?= $comp['score']; ?><small>/<?= $comp['max']; ?></small></span>
                <span class="br-stats-kpi-label"><?= $meta['label']; ?></span>
                <span class="br-stats-kpi-detail"><?= $meta['detail']; ?></span>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Player Table -->
    <div class="br-stats-section-title" style="margin-top:8px">
        <h2><?= __("Players", "bluerabbit"); ?></h2>
    </div>
    <div class="br-stats-panel">
        <table class="table transparent-bg br-stats-table" id="br-stats-player-table">
            <thead>
                <tr>
                    <td>#</td>
                    <td><?= __("Player", "bluerabbit"); ?></td>
                    <td class="text-center"><?= $xp_label; ?> <span class="icon icon-sort"></span></td>
                    <td class="text-center"><?= $bloo_label; ?></td>
                    <td class="text-center"><?= __("Completion", "bluerabbit"); ?></td>
                    <td class="text-center"><?= __("Last Active", "bluerabbit"); ?></td>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_players_data['players'] as $idx => $ap){ ?>
                <tr class="br-stats-player-row<?= ($ap['player_id'] == $view_user_id) ? ' active' : ''; ?>"
                    data-uid="<?= $ap['player_id']; ?>">
                    <td><?= ($page - 1) * $per_page + $idx + 1; ?></td>
                    <td>
                        <span class="br-stats-player-name">
                            <img src="<?= esc_url(get_avatar_url($ap['player_id'], ['size' => 32])); ?>"
                                 class="br-stats-avatar-sm" alt="">
                            <?= esc_html($ap['display_name']); ?>
                        </span>
                    </td>
                    <td class="text-center"><?= number_format($ap['player_xp']); ?></td>
                    <td class="text-center"><?= number_format($ap['player_bloo']); ?></td>
                    <td class="text-center"><?= $ap['completion_pct']; ?>%</td>
                    <td class="text-center">
                        <?= ($ap['player_last_login'] && strtotime($ap['player_last_login']) > 0) ? get_time_ago(strtotime($ap['player_last_login']), $adv_child_id) : '&mdash;'; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        $total_pages = ceil($all_players_data['total'] / $per_page);
        if ($total_pages > 1) { ?>
        <div class="br-stats-pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pg' => $i])); ?>"
               class="br-stats-page-link<?= $i == $page ? ' active' : ''; ?>"><?= $i; ?></a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

</div>
<?php } ?>


<!-- ═══════════════════ PLAYER VIEW ═══════════════════ -->
<div id="br-stats-player-panel" class="br-stats-player" data-uid="<?= $view_user_id; ?>">

<?php if (!empty($p_summary)) { ?>

    <!-- Hero Bar -->
    <?php
    $eng_score  = $p_engagement['score'];
    $eng_level  = $p_engagement['level'];
    $eng_labels = [
        'on_fire'    => __("On Fire", "bluerabbit"),
        'active'     => __("Active", "bluerabbit"),
        'moderate'   => __("Moderate", "bluerabbit"),
        'cooling_off'=> __("Cooling Off", "bluerabbit"),
        'dormant'    => __("Dormant", "bluerabbit"),
    ];
    $eng_colors = [
        'on_fire'=>'#f7cb15','active'=>'#24da98','moderate'=>'#1cc2eb',
        'cooling_off'=>'#ff9800','dormant'=>'#f44336',
    ];
    $eng_circ   = 326.73;
    $eng_offset = round($eng_circ * (1 - $eng_score / 100), 2);
    $eng_color  = $eng_colors[$eng_level] ?? '#1cc2eb';
    ?>
    <div class="br-stats-hero">
        <div class="br-stats-hero-avatar">
            <img src="<?= esc_url(get_avatar_url($view_user_id, ['size' => 80])); ?>" alt="">
        </div>
        <div class="br-stats-hero-info">
            <h2 class="br-stats-hero-name"><?= esc_html($p_summary['display_name']); ?></h2>
            <span class="br-stats-hero-adventure"><?= esc_html($adventure->adventure_title); ?></span>
            <div class="br-stats-hero-meta">
                <?php if (!empty($p_summary['rank_name'])) { ?>
                <span class="br-stats-badge <?= esc_attr($p_summary['rank_color']); ?>">
                    <?= esc_html($p_summary['rank_name']); ?>
                </span>
                <?php } ?>
                <span class="br-stats-hero-level">Lv. <?= (int)$p_summary['player_level']; ?></span>
                <span class="br-stats-hero-rank">
                    #<?= $p_summary['rank_position']; ?>
                    <?= __("of", "bluerabbit"); ?>
                    <?= $p_summary['total_players']; ?> <?= __("players", "bluerabbit"); ?>
                </span>
            </div>
            <div class="br-stats-last-activity">
                <?php if ($p_last['days_since_login'] !== null) { ?>
                <span><?= __("Last login", "bluerabbit"); ?>: <strong><?= round($p_last['days_since_login']); ?>d ago</strong></span>
                <?php } ?>
                <?php if ($p_last['days_since_quest'] !== null) { ?>
                <span><?= __("Last quest", "bluerabbit"); ?>: <strong><?= round($p_last['days_since_quest']); ?>d ago</strong></span>
                <?php } ?>
                <?php if ($p_last['days_since_activity'] !== null) { ?>
                <span><?= __("Last activity", "bluerabbit"); ?>: <strong><?= round($p_last['days_since_activity']); ?>d ago</strong></span>
                <?php } ?>
            </div>
        </div>
        <div class="br-stats-engagement-gauge">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                <circle cx="60" cy="60" r="52" fill="none" stroke="<?= $eng_color; ?>" stroke-width="8"
                        stroke-dasharray="<?= $eng_circ; ?>" stroke-dashoffset="<?= $eng_offset; ?>"
                        transform="rotate(-90 60 60)" stroke-linecap="round"/>
                <text x="60" y="52" text-anchor="middle" fill="#ffffff" font-size="28" font-weight="900" font-family="proxima-nova-extra-condensed,sans-serif"><?= $eng_score; ?></text>
                <text x="60" y="70" text-anchor="middle" fill="<?= $eng_color; ?>" font-size="9" font-weight="700" text-transform="uppercase" letter-spacing="1"><?= strtoupper($eng_labels[$eng_level]); ?></text>
            </svg>
            <span class="br-stats-engagement-label"><?= __("Engagement", "bluerabbit"); ?></span>
        </div>
    </div>

    <!-- Currency Row -->
    <div class="br-stats-currencies">
        <div class="br-stats-currency xp">
            <span class="br-stats-currency-value"><?= number_format($p_summary['player_xp']); ?></span>
            <span class="br-stats-currency-label"><?= $xp_label; ?></span>
        </div>
        <div class="br-stats-currency bloo">
            <span class="br-stats-currency-value"><?= number_format($p_summary['player_bloo']); ?></span>
            <span class="br-stats-currency-label"><?= $bloo_label; ?></span>
        </div>
        <div class="br-stats-currency ep">
            <span class="br-stats-currency-value"><?= number_format($p_summary['player_ep']); ?></span>
            <span class="br-stats-currency-label"><?= $ep_label; ?></span>
        </div>
    </div>

    <!-- XP Over Time + Type Completion -->
    <div class="br-stats-charts-row">
        <div class="br-stats-panel br-stats-two-thirds">
            <h3><?= $xp_label; ?> <?= __("Over Time", "bluerabbit"); ?></h3>
            <div class="br-stats-chart-wrap">
                <canvas id="br-xp-history-chart"></canvas>
            </div>
        </div>
        <div class="br-stats-panel br-stats-one-third">
            <h3><?= __("Completion by Type", "bluerabbit"); ?></h3>
            <div class="br-stats-chart-wrap br-stats-doughnut-wrap">
                <canvas id="br-type-completion-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quest Progress -->
    <div class="br-stats-panel">
        <h3><?= __("Quest Progress", "bluerabbit"); ?></h3>
        <div class="br-stats-quest-list">
            <?php foreach ($p_quests as $pq) {
                $status_class = 'locked';
                $bar_pct = 0;
                $status_text = __("Locked", "bluerabbit");
                if ($pq['status'] === 'publish') {
                    $status_class = 'complete';
                    $bar_pct = 100;
                    $status_text = __("Complete", "bluerabbit");
                } elseif ($pq['status']) {
                    $status_class = 'in-progress';
                    $bar_pct = 50;
                    $status_text = __("In Progress", "bluerabbit");
                }
            ?>
            <div class="br-stats-quest-row">
                <div class="br-stats-quest-info">
                    <span class="icon icon-<?= esc_attr($pq['quest_icon'] ?: 'quest'); ?>"></span>
                    <span class="br-stats-quest-title"><?= esc_html($pq['quest_title']); ?></span>
                </div>
                <div class="br-stats-quest-bar-wrap">
                    <div class="br-stats-quest-bar <?= $status_class; ?>" style="width:<?= $bar_pct; ?>%"></div>
                </div>
                <span class="br-stats-quest-status <?= $status_class; ?>"><?= $status_text; ?></span>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Tabi Progress -->
    <?php if (!empty($p_tabis)) { ?>
    <div class="br-stats-panel">
        <h3><?= __("Tabi Progress", "bluerabbit"); ?></h3>
        <div class="br-stats-quest-list">
            <?php foreach ($p_tabis as $tb) {
                $tb_total = (int) $tb['total_quests'];
                $tb_done  = (int) $tb['completed_quests'];
                $tb_pct   = $tb_total > 0 ? round(($tb_done / $tb_total) * 100) : 0;
                $tb_class = $tb_pct >= 100 ? 'complete' : ($tb_pct > 0 ? 'in-progress' : 'locked');
            ?>
            <div class="br-stats-quest-row">
                <div class="br-stats-quest-info">
                    <span class="icon icon-tabi"></span>
                    <span class="br-stats-quest-title"><?= esc_html($tb['tabi_name']); ?></span>
                </div>
                <div class="br-stats-quest-bar-wrap">
                    <div class="br-stats-quest-bar <?= $tb_class; ?>" style="width:<?= $tb_pct; ?>%"></div>
                </div>
                <span class="br-stats-quest-status <?= $tb_class; ?>"><?= $tb_done; ?>/<?= $tb_total; ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Achievements Grid -->
    <?php if (!empty($p_achievements)) { ?>
    <div class="br-stats-panel">
        <h3><?= __("Achievements", "bluerabbit"); ?></h3>
        <div class="br-stats-achievements-grid">
            <?php foreach ($p_achievements as $pa) { ?>
            <div class="br-stats-achievement <?= $pa['earned_at'] ? 'earned' : 'locked'; ?>">
                <?php if ($pa['achievement_badge']) { ?>
                <img src="<?= esc_url($pa['achievement_badge']); ?>"
                     alt="<?= esc_attr($pa['achievement_name']); ?>">
                <?php } else { ?>
                <div class="br-stats-achievement-placeholder <?= esc_attr($pa['achievement_color']); ?>">
                    <span class="icon icon-achievement"></span>
                </div>
                <?php } ?>
                <span class="br-stats-achievement-name"><?= esc_html($pa['achievement_name']); ?></span>
                <?php if ($pa['earned_at']) { ?>
                <span class="br-stats-achievement-date"><?= date('M j', strtotime($pa['earned_at'])); ?></span>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Guild Card -->
    <?php if (!empty($p_guild)) { ?>
    <div class="br-stats-panel br-stats-guild-card">
        <h3><?= __("Guild", "bluerabbit"); ?></h3>
        <div class="br-stats-guild-content">
            <?php if ($p_guild['guild_logo']) { ?>
            <img src="<?= esc_url($p_guild['guild_logo']); ?>" class="br-stats-guild-logo" alt="">
            <?php } ?>
            <div class="br-stats-guild-info">
                <h4><?= esc_html($p_guild['guild_name']); ?></h4>
                <div class="br-stats-guild-stats">
                    <span>
                        <strong><?= __("Rank", "bluerabbit"); ?>:</strong>
                        #<?= $p_guild['rank']; ?> / <?= $p_guild['total_guilds']; ?>
                    </span>
                    <span>
                        <strong><?= $xp_label; ?>:</strong>
                        <?= number_format($p_guild['total_xp']); ?>
                    </span>
                    <span>
                        <strong><?= __("Members", "bluerabbit"); ?>:</strong>
                        <?= $p_guild['member_count']; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>


    <!-- SCORM Completions -->
    <?php if (!empty($p_scorm)) { ?>
    <div class="br-stats-panel">
        <h3><?= __("SCORM Completions", "bluerabbit"); ?></h3>
        <table class="table transparent-bg">
            <thead>
                <tr>
                    <td><?= __("Step", "bluerabbit"); ?></td>
                    <td class="text-center"><?= __("Status", "bluerabbit"); ?></td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($p_scorm as $sc) {
                    $sc_class = ($sc['status'] === 'completed' || $sc['status'] === 'passed') ? 'complete' : 'incomplete';
                ?>
                <tr>
                    <td><?= esc_html($sc['step_title']); ?></td>
                    <td class="text-center">
                        <span class="br-stats-scorm-status <?= $sc_class; ?>">
                            <?= esc_html(ucfirst($sc['status'])); ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

<?php } else { ?>
    <div class="br-stats-panel text-center">
        <p class="white-color font _18"><?= __("No data available for this player.", "bluerabbit"); ?></p>
    </div>
<?php } ?>

</div><!-- #br-stats-player-panel -->

</div><!-- .br-stats-page -->

<?php } else { ?>
    <script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

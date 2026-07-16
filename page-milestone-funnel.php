<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if ($adventure) { ?>
<?php
$is_manager = ($isGM || $isAdmin || $isNPC);
if (!$is_manager) {
	echo '<div class="br-page"><div class="br-panel">' . __("You don't have access to this page.", "bluerabbit") . '</div></div>';
	include (get_stylesheet_directory() . '/footer.php');
	exit;
}

$stats       = new BR_Stats();
$tabis       = $stats->get_adventure_tabi_list($adv_child_id);
$levels      = $stats->get_adventure_level_list($adv_child_id);
$adv_summary = $stats->get_adventure_summary($adv_child_id);
$active_pct  = (int) $adv_summary['total_players'] > 0
    ? round( ( $adv_summary['active_7d'] / $adv_summary['total_players'] ) * 100, 1 )
    : 0;
?>

<script>
window.brMilestoneFunnel = {
    ajaxurl: '<?= admin_url("admin-ajax.php"); ?>',
    nonce: '<?= wp_create_nonce("br_stats_nonce"); ?>',
    adventureId: <?= (int) $adv_child_id; ?>,
    totalEnrolled: <?= (int) $adv_summary['total_players']; ?>,
    loggedInCount: <?= (int) $adv_summary['logged_in_count']; ?>,
    loggedInPct: <?= (float) $adv_summary['logged_in_pct']; ?>,
    loggedInLabel: <?= json_encode(__("players have logged in", "bluerabbit")); ?>
};
</script>

<div class="br-mf-page">

    <div class="br-mf-header">
        <div class="br-stats-section-title" style="margin-bottom:0">
            <span class="icon icon-skill"></span>
            <h2><?= __("Milestone Funnel Details", "bluerabbit"); ?></h2>
        </div>
        <a class="br-mf-back-link" href="<?= esc_url(get_bloginfo("url")."/stats/?adventure_id={$adv_child_id}"); ?>">
            <span class="icon icon-arrow-left"></span> <?= __("Back to Stats", "bluerabbit"); ?>
        </a>
    </div>

    <div class="br-stats-panel">
        <div class="br-mf-filter-row">
            <label for="br-mf-filter"><?= __("View", "bluerabbit"); ?></label>
            <select class="br-input" id="br-mf-filter">
                <option value="all|0"><?= __("All Milestones", "bluerabbit"); ?></option>
                <?php if (!empty($tabis)) { ?>
                <optgroup label="<?= esc_attr__("By Tabi", "bluerabbit"); ?>">
                    <?php foreach ($tabis as $t) { ?>
                    <option value="tabi|<?= (int) $t['tabi_id']; ?>"><?= esc_html($t['tabi_name']); ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
                <?php if (!empty($levels)) { ?>
                <optgroup label="<?= esc_attr__("By Level", "bluerabbit"); ?>">
                    <?php foreach ($levels as $lvl) { ?>
                    <option value="level|<?= (int) $lvl; ?>"><?= __("Level", "bluerabbit"); ?> <?= (int) $lvl; ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>

            <div class="br-mf-context-stats">
                <div class="br-mf-context-stat">
                    <span class="br-mf-context-value"><?= number_format($adv_summary['total_players']); ?></span>
                    <span class="br-mf-context-label"><?= __("Enrolled", "bluerabbit"); ?></span>
                </div>
                <div class="br-mf-context-stat orange">
                    <span class="br-mf-context-value"><?= $adv_summary['logged_in_pct']; ?>%</span>
                    <span class="br-mf-context-label"><?= __("Logged In", "bluerabbit"); ?></span>
                </div>
                <div class="br-mf-context-stat accent">
                    <span class="br-mf-context-value"><?= $active_pct; ?>%</span>
                    <span class="br-mf-context-label"><?= __("Active 7d", "bluerabbit"); ?></span>
                </div>
            </div>
        </div>

        <div class="br-mf-chart-shell">
            <div class="br-mf-scroll-wrap" id="br-mf-scroll-wrap">
                <div class="br-mf-yaxis-sticky">
                    <div class="br-mf-yaxis-labels" id="br-mf-yaxis-labels"></div>
                </div>
                <div class="br-mf-chart-area" id="br-mf-chart-area">
                    <canvas id="br-mf-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<?php } else { ?>
    <script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

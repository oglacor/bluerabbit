<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$org = BR_Organization::instance()->getOrgs(br_require_id('id'));
if (!$org) { echo '<p class="padding-20">' . __('Organization not found.','bluerabbit') . '</p>'; include get_stylesheet_directory().'/footer.php'; exit; }

$org_players    = BR_Organization::instance()->getOrgPlayers($org->org_id);
$org_adventures = BR_Organization::instance()->getOrgAdventures($org->org_id);
$total_players  = count($org_players);
$total_advs     = count($org_adventures);

// Stats pre-load
$org_stats    = BR_OrgStats::instance()->get_org_summary($org->org_id);
$org_progress = BR_OrgStats::instance()->get_progress_by_adventure($org->org_id);
$org_segment  = BR_OrgStats::instance()->get_org_segment_breakdown($org->org_id, 'work_country');

// All adventures for bulk-from-adventure dropdown
global $wpdb;
$all_adventures = $wpdb->get_results(
    "SELECT adventure_id, adventure_title FROM {$wpdb->prefix}br_adventures
     WHERE adventure_status='publish' ORDER BY adventure_title ASC"
);
?>

<h1 class="padding-10 font condensed white-color w900 uppercase _20 light-blue-bg-800">
    <?= esc_html($org->org_name ?: __('Manage Organization','bluerabbit')); ?>
</h1>
<input type="hidden" id="the_org_id" value="<?= (int)$org->org_id; ?>">
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_org_nonce'); ?>">
<input type="hidden" id="search-player-nonce" value="<?= wp_create_nonce('br_search_player_org_nonce'); ?>">
<input type="hidden" id="org-nonce" value="<?= wp_create_nonce('br_org_nonce'); ?>">

<div class="dashboard">
    <div class="dashboard-sidebar grey-bg-800 relative padding-10">
        <div class="tabs-buttons" id="tab-group-buttons">
            <ul class="margin-0 padding-0">
                <li class="block">
                    <button class="form-ui w-full relative tab-button overflow-hidden active" id="general-tab-button" onclick="switchTabs('#tab-group','#general');">
                        <span class="icon icon-tools foreground relative"></span>
                        <span class="foreground relative"><?= __("General","bluerabbit"); ?></span>
                        <span class="active-content background orange-bg-700"></span>
                        <span class="inactive-content background grey-bg-600"></span>
                    </button>
                </li>
                <li class="block">
                    <button class="form-ui w-full relative tab-button overflow-hidden" id="players-tab-button" onclick="switchTabs('#tab-group','#org-players-tab'); orgPlayersInit();">
                        <span class="icon icon-player foreground relative"></span>
                        <span class="foreground relative"><?= __("Players","bluerabbit"); ?> <span class="br-text-12 opacity-50">(<?= $total_players; ?>)</span></span>
                        <span class="active-content background orange-bg-700"></span>
                        <span class="inactive-content background grey-bg-600"></span>
                    </button>
                </li>
                <li class="block">
                    <button class="form-ui w-full relative tab-button overflow-hidden" id="adventures-tab-button" onclick="switchTabs('#tab-group','#org-adventures-tab');">
                        <span class="icon icon-adventure foreground relative"></span>
                        <span class="foreground relative"><?= __("Adventures","bluerabbit"); ?> <span class="br-text-12 opacity-50">(<?= $total_advs; ?>)</span></span>
                        <span class="active-content background orange-bg-700"></span>
                        <span class="inactive-content background grey-bg-600"></span>
                    </button>
                </li>
                <li class="block">
                    <button class="form-ui w-full relative tab-button overflow-hidden" id="stats-tab-button" onclick="switchTabs('#tab-group','#org-stats-tab'); orgStatsInit();">
                        <span class="icon icon-skill foreground relative"></span>
                        <span class="foreground relative"><?= __("Stats","bluerabbit"); ?></span>
                        <span class="active-content background orange-bg-700"></span>
                        <span class="inactive-content background grey-bg-600"></span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="dashboard-content white-bg">
        <div class="tabs" id="tab-group">

            <!-- ═══════ GENERAL ═══════ -->
            <div class="active tab max-w-900 padding-10" id="general">
                <div class="highlight padding-10 grey-bg-200 sticky top left layer base">
                    <span class="icon-group">
                        <span class="br-icon-btn br-icon-btn-indigo">
                            <span class="icon icon-tools white-color"></span>
                        </span>
                        <span class="icon-content font w500 _26">
                            <span class="line"><?= __('General Settings','bluerabbit'); ?></span>
                            <span class="line br-text-14 w300 grey-500"><?= __('Name, logo, color, about','bluerabbit'); ?></span>
                        </span>
                    </span>
                </div>
                <table class="table w-full" cellpadding="0">
                    <thead>
                        <tr class="br-text-12 grey-600">
                            <td class="text-right w-150"><?= __('Setting','bluerabbit'); ?></td>
                            <td><?= __('Value','bluerabbit'); ?></td>
                        </tr>
                    </thead>
                    <tbody class="br-text-16">
                        <tr>
                            <td class="text-right w-150"><?= __('Name','bluerabbit'); ?></td>
                            <td>
                                <div class="input-group w-full">
                                    <label class="light-blue-bg-800 br-text-24"><span class="icon icon-quest"></span></label>
                                    <input class="form-ui br-text-30 w-full" placeholder="<?= __('Organization Name','bluerabbit'); ?>" maxlength="50" type="text" value="<?= esc_attr($org->org_name); ?>" id="the-org-name">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right w-150"><?= __('Logo','bluerabbit'); ?></td>
                            <td>
                                <div class="gallery">
                                    <?php BR_Utils::instance()->insertGalleryItem('the-org-logo', $org->org_logo); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
                            <td>
                                <input id="the-org-color" class="color-selected" type="hidden" value="<?= esc_attr($org->org_color); ?>">
                                <?php $color_select_id = "#the-org-color"; include TEMPLATEPATH.'/color-select.php'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right w-150"><?= __('About','bluerabbit'); ?></td>
                            <td>
                                <?php wp_editor($org->org_content, 'the-org-content', ['quicktags'=>true,'editor_height'=>250]); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ═══════ PLAYERS ═══════ -->
            <div class="tab max-w-1200 padding-10" id="org-players-tab">

                <div class="highlight padding-10 grey-bg-200">
                    <span class="icon-group">
                        <span class="br-icon-btn br-icon-btn-orange-light">
                            <span class="icon icon-players white-color"></span>
                        </span>
                        <span class="icon-content">
                            <span class="line br-text-20 w500"><?= __("Players","bluerabbit"); ?></span>
                            <span class="line br-text-14 grey-500" id="org-player-count"><?= $total_players; ?> <?= __("total","bluerabbit"); ?></span>
                        </span>
                    </span>
                </div>

                <!-- Add by search -->
                <div class="br-panel padding-10 margin-bottom-10">
                    <p class="br-line-subtitle"><?= __("Add by search","bluerabbit"); ?></p>
                    <div class="input-group">
                        <label><span class="icon icon-search"></span></label>
                        <input type="text" class="form-ui" id="player-search-string" autocomplete="off" placeholder="<?= esc_attr(__("Type email or name…","bluerabbit")); ?>">
                        <label><button class="button main" onclick="findPlayersToOrg();"><?= __("Find","bluerabbit"); ?></button></label>
                    </div>
                    <div id="search-players-results"><ul class="player-select"></ul></div>
                </div>

                <!-- CSV bulk upload -->
                <div class="br-panel padding-10 margin-bottom-10">
                    <p class="br-line-subtitle"><?= __("Bulk import by CSV","bluerabbit"); ?></p>
                    <p class="br-text-12-muted"><?= __("First column must be email. Existing users are added; unknown emails are reported.","bluerabbit"); ?></p>
                    <div class="input-group">
                        <label><span class="icon icon-upload"></span></label>
                        <input type="file" class="form-ui" id="org-csv-players" accept=".csv,.txt">
                        <label><button class="button main" onclick="orgImportPlayersCsv();"><?= __("Import","bluerabbit"); ?></button></label>
                    </div>
                </div>

                <!-- Bulk from adventure -->
                <div class="br-panel padding-10 margin-bottom-10">
                    <p class="br-line-subtitle"><?= __("Bulk add from adventure","bluerabbit"); ?></p>
                    <p class="br-text-12-muted"><?= __("Copies all enrolled players from a chosen adventure into this organization.","bluerabbit"); ?></p>
                    <div class="input-group">
                        <label><span class="icon icon-adventure"></span></label>
                        <select class="form-ui" id="org-bulk-adventure-select">
                            <option value=""><?= __("Select adventure…","bluerabbit"); ?></option>
                            <?php foreach ($all_adventures as $a): ?>
                                <option value="<?= (int)$a->adventure_id; ?>"><?= esc_html($a->adventure_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label><button class="button main" onclick="orgBulkFromAdventure();"><?= __("Add All","bluerabbit"); ?></button></label>
                    </div>
                </div>

                <!-- Player table -->
                <div class="highlight padding-10 grey-bg-200">
                    <span class="icon-group">
                        <span class="br-icon-btn br-icon-btn-orange-light">
                            <span class="icon icon-players white-color"></span>
                        </span>
                        <span class="icon-content">
                            <span class="line br-text-16 w500" id="org-player-count-2"><?= $total_players; ?> <?= __("players in org","bluerabbit"); ?></span>
                        </span>
                    </span>
                    <div class="highlight-cell pull-right">
                        <div class="input-group inline-table">
                            <label><span class="icon icon-search"></span></label>
                            <input type="text" class="form-ui" id="search-org-players" placeholder="<?= esc_attr(__("Filter…","bluerabbit")); ?>">
                        </div>
                    </div>
                </div>
                <table class="table compact">
                    <thead>
                        <tr>
                            <td><?= __("ID","bluerabbit"); ?></td>
                            <td><?= __("Name","bluerabbit"); ?></td>
                            <td><?= __("Email","bluerabbit"); ?></td>
                            <td><?= __("Role","bluerabbit"); ?></td>
                            <td><?= __("Remove","bluerabbit"); ?></td>
                        </tr>
                    </thead>
                    <tbody id="org-players-list">
                        <?php foreach ($org_players as $player): include TEMPLATEPATH.'/player-row-org.php'; endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ═══════ ADVENTURES ═══════ -->
            <div class="tab max-w-1200 padding-10" id="org-adventures-tab">

                <div class="highlight padding-10 grey-bg-200">
                    <span class="icon-group">
                        <span class="br-icon-btn br-icon-btn-orange-light">
                            <span class="icon icon-adventure white-color"></span>
                        </span>
                        <span class="icon-content">
                            <span class="line br-text-20 w500"><?= __("Adventures","bluerabbit"); ?></span>
                            <span class="line br-text-14 grey-500" id="org-adv-count"><?= $total_advs; ?> <?= __("in this org","bluerabbit"); ?></span>
                        </span>
                    </span>
                </div>

                <!-- Search & add -->
                <div class="br-panel padding-10 margin-bottom-10">
                    <p class="br-line-subtitle"><?= __("Add adventure","bluerabbit"); ?></p>
                    <div class="input-group">
                        <label><span class="icon icon-search"></span></label>
                        <input type="text" class="form-ui" id="adv-search-string" autocomplete="off" placeholder="<?= esc_attr(__("Search adventures…","bluerabbit")); ?>">
                        <label><button class="button main" onclick="orgSearchAdventures();"><?= __("Search","bluerabbit"); ?></button></label>
                    </div>
                    <ul id="org-adv-search-results" class="player-select"></ul>
                </div>

                <!-- Adventure table -->
                <table class="table compact" id="org-adventures-table">
                    <thead>
                        <tr>
                            <td><?= __("ID","bluerabbit"); ?></td>
                            <td><?= __("Adventure","bluerabbit"); ?></td>
                            <td><?= __("Owner","bluerabbit"); ?></td>
                            <td><?= __("Enroll Link","bluerabbit"); ?></td>
                            <td><?= __("Remove","bluerabbit"); ?></td>
                        </tr>
                    </thead>
                    <tbody id="org-adventures-list">
                        <?php foreach ($org_adventures as $adv): ?>
                        <tr id="org-adv-row-<?= (int)$adv->adventure_id; ?>">
                            <td class="br-text-12-muted"><?= (int)$adv->adventure_id; ?></td>
                            <td class="w500"><?= esc_html($adv->adventure_title); ?></td>
                            <td class="br-text-12-muted"><?= esc_html($adv->player_display_name ?? '—'); ?></td>
                            <td class="br-text-12-muted">
                                <?php if ($adv->adventure_code): ?>
                                <a href="<?= esc_url(get_bloginfo('url').'/enroll/?enroll_code='.$adv->adventure_code); ?>" target="_blank" class="br-text-12-muted"><?= get_bloginfo('url'); ?>/enroll/?enroll_code=<?= esc_html($adv->adventure_code); ?></a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <button class="br-icon-cancel-red" onclick="orgRemoveAdventure(<?= (int)$adv->adventure_id; ?>);" title="<?= esc_attr(__('Remove from org','bluerabbit')); ?>">
                                    <span class="icon icon-cancel white-color"></span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ═══════ STATS ═══════ -->
            <div class="tab" id="org-stats-tab">

                <div class="br-stats-manager padding-10">

                    <div class="br-stats-section-title">
                        <span class="icon icon-skill"></span>
                        <h2><?= __("Organization Dashboard","bluerabbit"); ?> — <?= esc_html($org->org_name); ?></h2>
                    </div>

                    <!-- KPIs -->
                    <div class="br-stats-kpis">
                        <div class="br-stats-kpi">
                            <span class="br-stats-kpi-value"><?= number_format($org_stats['total_players']); ?></span>
                            <span class="br-stats-kpi-label"><?= __("Unique Players","bluerabbit"); ?></span>
                        </div>
                        <div class="br-stats-kpi purple">
                            <span class="br-stats-kpi-value"><?= number_format($org_stats['total_adventures']); ?></span>
                            <span class="br-stats-kpi-label"><?= __("Adventures","bluerabbit"); ?></span>
                        </div>
                        <div class="br-stats-kpi accent">
                            <span class="br-stats-kpi-value"><?= number_format($org_stats['active_7d']); ?></span>
                            <span class="br-stats-kpi-label"><?= __("Active 7d","bluerabbit"); ?></span>
                        </div>
                        <div class="br-stats-kpi green">
                            <span class="br-stats-kpi-value"><?= number_format($org_stats['active_30d']); ?></span>
                            <span class="br-stats-kpi-label"><?= __("Active 30d","bluerabbit"); ?></span>
                        </div>
                    </div>

                    <!-- Progress by Adventure -->
                    <div class="br-stats-section-title">
                        <span class="icon icon-adventure"></span>
                        <h2><?= __("Progress by Adventure","bluerabbit"); ?></h2>
                    </div>
                    <div class="br-stats-chart-wrap" style="height:<?= max(160, count($org_progress) * 50 + 60); ?>px">
                        <canvas id="org-progress-chart"></canvas>
                    </div>

                    <!-- Daily Active Users -->
                    <div class="br-stats-section-title">
                        <span class="icon icon-time"></span>
                        <h2><?= __("Daily Active Users","bluerabbit"); ?></h2>
                    </div>
                    <div class="br-stats-date-filter">
                        <label class="br-text-12-muted"><?= __("From","bluerabbit"); ?></label>
                        <input type="text" class="form-ui datetimepicker" id="org-activity-from" placeholder="YYYY-MM-DD" readonly>
                        <label class="br-text-12-muted"><?= __("To","bluerabbit"); ?></label>
                        <input type="text" class="form-ui datetimepicker" id="org-activity-to" placeholder="YYYY-MM-DD" readonly>
                        <button class="br-form-btn-blue" onclick="orgLoadActivity();"><?= __("Apply","bluerabbit"); ?></button>
                    </div>
                    <div class="br-stats-chart-wrap" id="org-activity-wrap" style="height:220px">
                        <canvas id="org-activity-chart"></canvas>
                    </div>

                    <!-- Segment Breakdown -->
                    <div class="br-stats-section-title">
                        <span class="icon icon-player"></span>
                        <h2><?= __("Workforce Demographics","bluerabbit"); ?></h2>
                    </div>
                    <div class="br-stats-segment-controls">
                        <?php foreach (BR_OrgStats::SEGMENT_DIMENSIONS as $key => $label): ?>
                        <button class="br-stats-dim-btn<?= $key === 'work_country' ? ' active' : ''; ?>"
                                onclick="orgLoadSegment('<?= esc_js($key); ?>', this);">
                            <?= esc_html($label); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="br-stats-chart-wrap" style="height:300px">
                        <canvas id="org-segment-chart"></canvas>
                    </div>
                    <p class="br-text-12-muted padding-10" id="org-segment-coverage"></p>

                </div>
            </div>

        </div><!-- /tabs -->
    </div><!-- /dashboard-content -->
</div><!-- /dashboard -->

<?php include get_stylesheet_directory().'/br-op-console.php'; ?>

<!-- Hidden success/error messages for JS notifications -->
<div class="hidden" id="msg-player-added-to-org">
    <li class="border green-bg-400 green-border-800">
        <span class="icon-group">
            <span class="br-icon-btn"><span class="icon icon-check white-color"></span></span>
            <span class="icon-content white-color"><span class="line br-text-16"><?= __("Player added to Org!","bluerabbit"); ?></span></span>
        </span>
    </li>
</div>
<div class="hidden" id="msg-player-not-added-to-org">
    <li class="border red-bg-400 red-border-800">
        <span class="icon-group">
            <span class="br-icon-btn"><span class="icon icon-cancel white-color"></span></span>
            <span class="icon-content white-color"><span class="line br-text-16"><?= __("Error adding player","bluerabbit"); ?></span></span>
        </span>
    </li>
</div>

<script>
// ── Bootstrap data for org stats charts ──────────────────────────────────────
window.brOrgStats = {
    ajaxurl: '<?= esc_js(admin_url('admin-ajax.php')); ?>',
    orgId:   <?= (int)$org->org_id; ?>,
    progressByAdventure: <?= json_encode($org_progress); ?>,
    segment: <?= json_encode($org_segment); ?>,
    segmentDimensions: <?= json_encode(BR_OrgStats::SEGMENT_DIMENSIONS); ?>
};

// ── Player tab filter ─────────────────────────────────────────────────────────
document.getElementById('search-org-players')?.addEventListener('input', function(){
    var val = this.value.toLowerCase();
    document.querySelectorAll('#org-players-list tr').forEach(function(tr){
        tr.style.display = (!val || tr.textContent.toLowerCase().includes(val)) ? '' : 'none';
    });
});

var orgPlayersInited = false;
function orgPlayersInit(){
    if (orgPlayersInited) return; orgPlayersInited = true;
    // nothing to lazy-load yet
}

// ── Remove player from org ─────────────────────────────────────────────────────
function removePlayerFromOrg(player_id, org_id){
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: { action: 'removePlayerFromOrg', player_id: player_id, org_id: org_id },
        success: function(r){
            try { var d = JSON.parse(r); } catch(e){ var d = r; }
            if (d.success) {
                jQuery('#player-org-row-' + player_id).fadeOut(300, function(){ jQuery(this).remove(); });
            }
        }
    });
}

// ── CSV import players to org ─────────────────────────────────────────────────
function orgImportPlayersCsv(){
    var fileInput = document.getElementById('org-csv-players');
    if (!fileInput || !fileInput.files[0]) { alert('<?= esc_js(__("Please select a CSV file.","bluerabbit")); ?>'); return; }
    var reader = new FileReader();
    reader.onload = function(e){
        var lines  = e.target.result.split(/[\r\n]+/);
        var emails = [];
        lines.forEach(function(line){
            var cell = line.split(',')[0].replace(/['"]/g,'').trim().toLowerCase();
            if (cell && cell.indexOf('@') > -1 && cell !== 'email') emails.push(cell);
        });
        if (!emails.length){ alert('<?= esc_js(__("No valid emails found.","bluerabbit")); ?>'); return; }
        brOpConsoleOpen('<?= esc_js(__("Import Players to Org","bluerabbit")); ?>');
        brOpConsoleLog('<?= esc_js(__("Found","bluerabbit")); ?> ' + emails.length + ' <?= esc_js(__("email addresses…","bluerabbit")); ?>', 'info');
        jQuery.ajax({
            url: runAJAX.ajaxurl, method: 'POST',
            data: { action: 'importPlayersToOrg', org_id: jQuery('#the_org_id').val(), emails: emails },
            success: function(r){
                var d = JSON.parse(r);
                if (!d.success){ brOpConsoleLog(d.message || '<?= esc_js(__("Error","bluerabbit")); ?>', 'error'); brOpConsoleDone(); return; }
                var summary = d.added + ' <?= esc_js(__("added","bluerabbit")); ?>, ' + d.already_in + ' <?= esc_js(__("already in org","bluerabbit")); ?>';
                if (d.not_found) summary += ', ' + d.not_found + ' <?= esc_js(__("not found","bluerabbit")); ?>.';
                brOpConsoleDone(summary);
                if (d.not_found_emails && d.not_found_emails.length){
                    brOpConsoleLog('<?= esc_js(__("Emails not found","bluerabbit")); ?> (' + d.not_found_emails.length + '):', 'warn');
                    d.not_found_emails.forEach(function(em){ brOpConsoleLog(em, 'warn'); });
                }
                // Reload player count (simple: add to counter)
                jQuery('#org-player-count, #org-player-count-2').text((parseInt(jQuery('#org-player-count').text()) || 0) + d.added + ' total');
                fileInput.value = '';
            },
            error: function(){ brOpConsoleLog('<?= esc_js(__("Request failed","bluerabbit")); ?>', 'error'); brOpConsoleDone(); }
        });
    };
    reader.readAsText(fileInput.files[0]);
}

// ── Bulk add players from adventure ──────────────────────────────────────────
function orgBulkFromAdventure(){
    var adv_id = jQuery('#org-bulk-adventure-select').val();
    if (!adv_id){ alert('<?= esc_js(__("Please select an adventure.","bluerabbit")); ?>'); return; }
    var adv_name = jQuery('#org-bulk-adventure-select option:selected').text();
    if (!confirm('<?= esc_js(__("Add all enrolled players from","bluerabbit")); ?> "' + adv_name + '" <?= esc_js(__("to this org?","bluerabbit")); ?>')) return;
    brOpConsoleOpen('<?= esc_js(__("Bulk Add from Adventure","bluerabbit")); ?>');
    brOpConsoleLog('<?= esc_js(__("Processing","bluerabbit")); ?> ' + adv_name + '…', 'info');
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'bulkPlayersFromAdventure', org_id: jQuery('#the_org_id').val(), adventure_id: adv_id },
        success: function(r){
            var d = JSON.parse(r);
            if (!d.success){ brOpConsoleLog(d.message || '<?= esc_js(__("Error","bluerabbit")); ?>', 'error'); brOpConsoleDone(); return; }
            brOpConsoleDone(d.added + ' <?= esc_js(__("added","bluerabbit")); ?>, ' + d.already_in + ' <?= esc_js(__("already in org","bluerabbit")); ?> — ' + d.total + ' <?= esc_js(__("total in adventure","bluerabbit")); ?>.');
        },
        error: function(){ brOpConsoleLog('<?= esc_js(__("Request failed","bluerabbit")); ?>', 'error'); brOpConsoleDone(); }
    });
}

// ── Adventure search & add ────────────────────────────────────────────────────
function orgSearchAdventures(){
    var s = jQuery('#adv-search-string').val().trim();
    if (!s) return;
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'searchAdventuresForOrg', org_id: jQuery('#the_org_id').val(), search: s },
        success: function(r){
            var d = JSON.parse(r);
            if (!d.success || !d.results.length){
                jQuery('#org-adv-search-results').html('<li class="padding-5 br-text-12-muted"><?= esc_js(__("No adventures found.","bluerabbit")); ?></li>');
                return;
            }
            var html = '';
            d.results.forEach(function(adv){
                html += '<li class="border padding-8 margin-bottom-4 pointer" onclick="orgAddAdventure(' + adv.adventure_id + ', this);">'
                    + '<span class="icon-group">'
                    + '<span class="br-icon-btn br-icon-btn-indigo"><span class="icon icon-adventure white-color"></span></span>'
                    + '<span class="icon-content"><span class="line br-text-16 w500">' + adv.adventure_title + '</span>'
                    + '<span class="line br-text-12-muted">' + (adv.player_display_name || '') + '</span></span>'
                    + '</span></li>';
            });
            jQuery('#org-adv-search-results').html(html);
        }
    });
}

function orgAddAdventure(adventure_id, el){
    jQuery(el).css('opacity', 0.4);
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'addAdventureToOrg', org_id: jQuery('#the_org_id').val(), adventure_id: adventure_id },
        success: function(r){
            var d = JSON.parse(r);
            if (!d.success) return;
            jQuery(el).remove();
            var enroll = d.adventure_code ? '<?= esc_js(get_bloginfo('url')); ?>/enroll/?enroll_code=' + d.adventure_code : '—';
            var row = '<tr id="org-adv-row-' + d.adventure_id + '">'
                + '<td class="br-text-12-muted">' + d.adventure_id + '</td>'
                + '<td class="w500">' + d.adventure_title + '</td>'
                + '<td class="br-text-12-muted">' + (d.owner_name || '—') + '</td>'
                + '<td class="br-text-12-muted">' + (d.adventure_code ? '<a href="' + enroll + '" target="_blank" class="br-text-12-muted">' + enroll + '</a>' : '—') + '</td>'
                + '<td><button class="br-icon-cancel-red" onclick="orgRemoveAdventure(' + d.adventure_id + ');" title="Remove"><span class="icon icon-cancel white-color"></span></button></td>'
                + '</tr>';
            jQuery('#org-adventures-list').append(row);
            jQuery('#adv-search-string').val('');
            jQuery('#org-adv-search-results').empty();
        }
    });
}

function orgRemoveAdventure(adventure_id){
    if (!confirm('<?= esc_js(__("Remove this adventure from the org?","bluerabbit")); ?>')) return;
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'removeAdventureFromOrg', org_id: jQuery('#the_org_id').val(), adventure_id: adventure_id },
        success: function(r){
            var d = JSON.parse(r);
            if (d.success) jQuery('#org-adv-row-' + adventure_id).fadeOut(300, function(){ jQuery(this).remove(); });
        }
    });
}

// ── Stats tab ────────────────────────────────────────────────────────────────
var orgStatsInited = false;
function orgStatsInit(){
    if (orgStatsInited) return; orgStatsInited = true;
    if (typeof brOrgStats === 'undefined') return;
    // Init date range pickers
    if (typeof jQuery.fn.datetimepicker !== 'undefined') {
        jQuery('#org-activity-from, #org-activity-to').datetimepicker({ format: 'Y-m-d', timepicker: false });
    }
    // Charts are in br-org-stats.js — trigger init
    if (typeof brOrgChartsInit === 'function') brOrgChartsInit();
}

// Enter on search fields
jQuery(function($){
    $('#adv-search-string').on('keyup', function(e){ if (e.key === 'Enter') orgSearchAdventures(); });
    $('#player-search-string').on('keyup', function(e){ if (e.key === 'Enter') findPlayersToOrg(); });
});
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

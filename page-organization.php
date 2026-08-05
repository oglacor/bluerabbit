<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$org = BR_Organization::instance()->getOrgs(br_require_id('id'));
if (!$org) { echo '<p class="br-page">' . __('Organization not found.','bluerabbit') . '</p>'; include get_stylesheet_directory().'/footer.php'; exit; }

$org_players    = BR_Organization::instance()->getOrgPlayers($org->org_id);
$org_adventures = BR_Organization::instance()->getOrgAdventures($org->org_id);
$total_players  = count($org_players);
$total_advs     = count($org_adventures); 

$org_stats    = BR_OrgStats::instance()->get_org_summary($org->org_id);
$org_progress = BR_OrgStats::instance()->get_progress_by_adventure($org->org_id);
$org_segment  = BR_OrgStats::instance()->get_org_segment_breakdown($org->org_id, 'work_country');

global $wpdb;
$all_adventures = $wpdb->get_results(
    "SELECT adventure_id, adventure_title FROM {$wpdb->prefix}br_adventures
     WHERE adventure_status='publish' ORDER BY adventure_title ASC"
);
?>

<div class="br-page">

<input type="hidden" id="the_org_id" value="<?= (int)$org->org_id; ?>">
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_org_nonce'); ?>">
<input type="hidden" id="search-player-nonce" value="<?= wp_create_nonce('br_search_player_org_nonce'); ?>">
<input type="hidden" id="org-nonce" value="<?= wp_create_nonce('br_org_nonce'); ?>">

<!-- ── Page Header ──────────────────────────────────────────────────────── -->
<div class="br-panel br-page-header">
    <div class="br-page-header-avatar br-avatar-orange">
        <span class="icon icon-quest br-icon-lg br-icon-orange"></span>
    </div>
    <div class="br-flex-1">
        <span class="br-page-subtitle"><?= __('Manage Organization','bluerabbit'); ?></span>
        <h1 class="br-page-title"><?= esc_html($org->org_name ?: __('Untitled Org','bluerabbit')); ?></h1>
    </div>
    <div class="br-actions br-ml-auto">
        <span class="br-badge br-badge-blue"><?= $total_players; ?> <?= __('players','bluerabbit'); ?></span>
        <span class="br-badge br-badge-amber"><?= $total_advs; ?> <?= __('adventures','bluerabbit'); ?></span>
    </div>
</div>

<!-- ── Tabs ─────────────────────────────────────────────────────────────── -->
<div class="br-tabs br-tabs-sticky">
    <button class="br-tab-btn active" onclick="brSwitchPanel('#org-panels','#org-general',this);">
        <span class="icon icon-tools"></span> <?= __('General','bluerabbit'); ?>
    </button>
    <button class="br-tab-btn" onclick="brSwitchPanel('#org-panels','#org-players',this);">
        <span class="icon icon-players"></span> <?= __('Players','bluerabbit'); ?>
        <span class="br-badge br-badge-blue"><?= $total_players; ?></span>
    </button>
    <button class="br-tab-btn" onclick="brSwitchPanel('#org-panels','#org-adventures',this);">
        <span class="icon icon-adventure"></span> <?= __('Adventures','bluerabbit'); ?>
        <span class="br-badge br-badge-amber"><?= $total_advs; ?></span>
    </button>
    <button class="br-tab-btn" onclick="brSwitchPanel('#org-panels','#org-stats',this); orgStatsInit();">
        <span class="icon icon-skill"></span> <?= __('Stats','bluerabbit'); ?>
    </button>
</div>

<!-- ── Panel container ─────────────────────────────────────────────────── -->
<div id="org-panels">

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- GENERAL                                                            -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="br-panel-group" id="org-general">
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-tools"></span> <?= __('General Settings','bluerabbit'); ?></h3>

            <div class="br-form-group">
                <label class="br-form-label"><?= __('Name','bluerabbit'); ?></label>
                <input class="br-input" type="text" id="the-org-name" maxlength="50"
                       placeholder="<?= esc_attr(__('Organization Name','bluerabbit')); ?>"
                       value="<?= esc_attr($org->org_name); ?>">
            </div>

            <div class="br-form-group">
                <label class="br-form-label"><?= __('Logo','bluerabbit'); ?></label>
                <div class="gallery">
                    <?php BR_Utils::instance()->insertGalleryItem('the-org-logo', $org->org_logo); ?>
                </div>
            </div>

            <div class="br-form-group">
                <label class="br-form-label"><?= __('Color','bluerabbit'); ?></label>
                <input id="the-org-color" class="color-selected" type="hidden" value="<?= esc_attr($org->org_color); ?>">
                <?php $color_select_id = "#the-org-color"; include TEMPLATEPATH.'/color-select.php'; ?>
            </div>

            <div class="br-form-group">
                <label class="br-form-label"><?= __('About','bluerabbit'); ?></label>
                <?php wp_editor($org->org_content, 'the-org-content', ['quicktags'=>true,'editor_height'=>250]); ?>
            </div>

            <div class="br-form-footer">
                <span></span>
                <button class="br-btn cyan" onclick="saveOrgSettings();">
                    <span class="icon icon-check"></span> <?= __('Save Settings','bluerabbit'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- PLAYERS                                                            -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="br-panel-group br-initially-hidden" id="org-players">

        <!-- Add players panel -->
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-add"></span> <?= __('Add Players','bluerabbit'); ?></h3>

            <!-- Player search -->
            <div class="br-form-group">
                <label class="br-form-label"><?= __('Find someone already registered','bluerabbit'); ?></label>
                <div class="br-ap-search-wrap">
                    <span class="icon icon-search br-ap-search-icon"></span>
                    <input class="br-input br-ap-search" type="text" id="org-ap-search"
                           autocomplete="off" placeholder="<?= esc_attr(__('Type email or name…','bluerabbit')); ?>">
                    <button class="br-ap-clear br-initially-hidden" id="org-ap-clear" onclick="orgApClearSearch();">
                        <span class="icon icon-cancel"></span>
                    </button>
                </div>
                <div class="br-ap-status" id="org-ap-status"></div>
                <div class="br-ap-results" id="org-ap-results"></div>
                <div class="br-ap-log br-initially-hidden" id="org-ap-log">
                    <div class="br-ap-log-head">
                        <span><?= __('Added in this session','bluerabbit'); ?></span>
                        <span class="br-badge br-badge-green" id="org-ap-log-count">0</span>
                    </div>
                    <ul class="br-ap-log-list" id="org-ap-log-list"></ul>
                </div>
            </div>

            <!-- CSV import -->
            <div class="br-panel-subsection">
                <h3 class="br-panel-title"><span class="icon icon-upload"></span> <?= __('Bulk Import by CSV','bluerabbit'); ?></h3>
                <span class="br-form-hint"><?= __('First column must be email. Existing users are added; unknown emails are reported.','bluerabbit'); ?></span>
                <div class="br-input-row br-mt-lg">
                    <input type="file" class="br-input" id="org-csv-players" accept=".csv,.txt">
                    <button class="br-btn cyan" onclick="orgImportPlayersCsv();">
                        <span class="icon icon-upload"></span> <?= __('Import','bluerabbit'); ?>
                    </button>
                </div>
            </div>

            <!-- Bulk from adventure -->
            <div class="br-panel-subsection">
                <h3 class="br-panel-title"><span class="icon icon-adventure"></span> <?= __('Bulk Add from Adventure','bluerabbit'); ?></h3>
                <span class="br-form-hint"><?= __('Copies all enrolled players from a chosen adventure into this organization.','bluerabbit'); ?></span>
                <div class="br-input-row br-mt-lg">
                    <select class="br-input" id="org-bulk-adventure-select">
                        <option value=""><?= __('Select adventure…','bluerabbit'); ?></option>
                        <?php foreach ($all_adventures as $a): ?>
                            <option value="<?= (int)$a->adventure_id; ?>"><?= esc_html($a->adventure_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="br-btn cyan" onclick="orgBulkFromAdventure();">
                        <span class="icon icon-players"></span> <?= __('Add All','bluerabbit'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Player list panel -->
        <div class="br-panel">
            <div class="br-panel-header">
                <h3 class="br-panel-title">
                    <span class="icon icon-players"></span>
                    <span id="org-player-count"><?= $total_players; ?></span> <?= __('players in org','bluerabbit'); ?>
                </h3>
                <input type="text" class="br-input br-input-auto" id="search-org-players"
                       placeholder="<?= esc_attr(__('Filter…','bluerabbit')); ?>">
            </div>

            <?php if ($org_players): ?>
            <div class="br-table-scroll">
                <table class="br-table">
                    <thead>
                        <tr>
                            <th><?= __('ID','bluerabbit'); ?></th>
                            <th><?= __('Name','bluerabbit'); ?></th>
                            <th><?= __('Email','bluerabbit'); ?></th>
                            <th><?= __('Remove','bluerabbit'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="org-players-list">
                        <?php foreach ($org_players as $player): include TEMPLATEPATH.'/player-row-org.php'; endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="br-empty">
                <span class="icon icon-players"></span>
                <h3><?= __('No players yet','bluerabbit'); ?></h3>
                <p><?= __('Use the search or bulk import above to add players to this organization.','bluerabbit'); ?></p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ADVENTURES                                                         -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="br-panel-group br-initially-hidden" id="org-adventures">

        <!-- Add adventure panel -->
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-add"></span> <?= __('Add Adventure','bluerabbit'); ?></h3>
            <div class="br-input-row">
                <input type="text" class="br-input" id="adv-search-string" autocomplete="off"
                       placeholder="<?= esc_attr(__('Search adventures…','bluerabbit')); ?>">
                <button class="br-btn cyan" onclick="orgSearchAdventures();">
                    <span class="icon icon-search"></span> <?= __('Search','bluerabbit'); ?>
                </button>
            </div>
            <ul id="org-adv-search-results" class="player-select"></ul>
        </div>

        <!-- Adventures table -->
        <div class="br-panel">
            <h3 class="br-panel-title">
                <span class="icon icon-adventure"></span> <?= __('Adventures in Org','bluerabbit'); ?>
                <span class="br-badge br-badge-amber" id="org-adv-count"><?= $total_advs; ?></span>
            </h3>

            <?php if ($org_adventures): ?>
            <div class="br-table-scroll">
                <table class="br-table" id="org-adventures-table">
                    <thead>
                        <tr>
                            <th><?= __('ID','bluerabbit'); ?></th>
                            <th><?= __('Adventure','bluerabbit'); ?></th>
                            <th><?= __('Owner','bluerabbit'); ?></th>
                            <th><?= __('Enroll Link','bluerabbit'); ?></th>
                            <th><?= __('Remove','bluerabbit'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="org-adventures-list">
                        <?php foreach ($org_adventures as $adv): ?>
                        <tr id="org-adv-row-<?= (int)$adv->adventure_id; ?>">
                            <td><?= (int)$adv->adventure_id; ?></td>
                            <td><?= esc_html($adv->adventure_title); ?></td>
                            <td><?= esc_html($adv->player_display_name ?? '—'); ?></td>
                            <td>
                                <?php if ($adv->adventure_code): ?>
                                <a href="<?= esc_url(get_bloginfo('url').'/enroll/?enroll_code='.$adv->adventure_code); ?>" target="_blank">
                                    <?= esc_html(get_bloginfo('url').'/enroll/?enroll_code='.$adv->adventure_code); ?>
                                </a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <button class="br-btn red br-btn-sm" title="<?= esc_attr(__('Remove from org','bluerabbit')); ?>"
                                        onclick="brConfirmInline(this,'<?= esc_js(__('Remove?','bluerabbit')); ?>',function(){ orgRemoveAdventure(<?= (int)$adv->adventure_id; ?>); });">
                                    <span class="icon icon-cancel"></span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="br-empty">
                <span class="icon icon-adventure"></span>
                <h3><?= __('No adventures yet','bluerabbit'); ?></h3>
                <p><?= __('Search for an adventure above to add it to this organization.','bluerabbit'); ?></p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- STATS                                                              -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="br-panel-group br-initially-hidden" id="org-stats">

        <!-- KPIs -->
        <div class="br-stats-kpis">
            <div class="br-stats-kpi">
                <span class="br-stats-kpi-value"><?= number_format($org_stats['total_players']); ?></span>
                <span class="br-stats-kpi-label"><?= __('Unique Players','bluerabbit'); ?></span>
            </div>
            <div class="br-stats-kpi purple">
                <span class="br-stats-kpi-value"><?= number_format($org_stats['total_adventures']); ?></span>
                <span class="br-stats-kpi-label"><?= __('Adventures','bluerabbit'); ?></span>
            </div>
            <div class="br-stats-kpi accent">
                <span class="br-stats-kpi-value"><?= number_format($org_stats['active_7d']); ?></span>
                <span class="br-stats-kpi-label"><?= __('Active 7d','bluerabbit'); ?></span>
            </div>
            <div class="br-stats-kpi green">
                <span class="br-stats-kpi-value"><?= number_format($org_stats['active_30d']); ?></span>
                <span class="br-stats-kpi-label"><?= __('Active 30d','bluerabbit'); ?></span>
            </div>
        </div>

        <!-- Progress by Adventure -->
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-adventure"></span> <?= __('Progress by Adventure','bluerabbit'); ?></h3>
            <div class="br-stats-chart-wrap" id="org-progress-wrap">
                <canvas id="org-progress-chart"></canvas>
            </div>
        </div>

        <!-- Daily Active Users -->
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-time"></span> <?= __('Daily Active Users','bluerabbit'); ?></h3>
            <div class="br-stats-date-filter">
                <label class="br-form-label"><?= __('From','bluerabbit'); ?></label>
                <input type="text" class="br-input datetimepicker" id="org-activity-from" placeholder="YYYY-MM-DD" readonly>
                <label class="br-form-label"><?= __('To','bluerabbit'); ?></label>
                <input type="text" class="br-input datetimepicker" id="org-activity-to" placeholder="YYYY-MM-DD" readonly>
                <button class="br-btn" onclick="orgLoadActivity();"><?= __('Apply','bluerabbit'); ?></button>
            </div>
            <div class="br-stats-chart-wrap">
                <canvas id="org-activity-chart"></canvas>
            </div>
        </div>

        <!-- Segment Breakdown -->
        <div class="br-panel">
            <h3 class="br-panel-title"><span class="icon icon-player"></span> <?= __('Workforce Demographics','bluerabbit'); ?></h3>
            <div class="br-stats-segment-controls">
                <?php foreach (BR_OrgStats::SEGMENT_DIMENSIONS as $key => $label): ?>
                <button class="br-stats-dim-btn<?= $key === 'work_country' ? ' active' : ''; ?>"
                        onclick="orgLoadSegment('<?= esc_js($key); ?>', this);">
                    <?= esc_html($label); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="br-stats-chart-wrap">
                <canvas id="org-segment-chart"></canvas>
            </div>
            <p class="br-form-hint" id="org-segment-coverage"></p>
        </div>

    </div>

</div><!-- /#org-panels -->

<?php include get_stylesheet_directory().'/br-op-console.php'; ?>


</div><!-- /.br-page -->

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

// ── Save general settings ─────────────────────────────────────────────────────
function saveOrgSettings() {
    var about = '';
    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('the-org-content')) {
        about = tinyMCE.get('the-org-content').getContent();
    } else {
        about = jQuery('#the-org-content').val();
    }
    jQuery.post(runAJAX.ajaxurl, {
        action: 'updateOrg',
        nonce: jQuery('#nonce').val(),
        org_data: {
            id:     jQuery('#the_org_id').val(),
            name:   jQuery('#the-org-name').val(),
            logo:   jQuery('#the-org-logo').val(),
            color:  jQuery('#the-org-color').val(),
            status: 'publish',
            about:  about
        }
    }, function(r) {
        var d = (typeof r === 'string') ? JSON.parse(r) : r;
        if (d.success || d.updated) {
            brNotify('<?= esc_js(__('Settings saved','bluerabbit')); ?>', 'success');
        } else {
            brNotify('<?= esc_js(__('Save failed','bluerabbit')); ?>', 'error');
        }
    });
}

// ── Remove player from org ─────────────────────────────────────────────────────
function removePlayerFromOrg(player_id, org_id) {
    jQuery.post(runAJAX.ajaxurl,
        { action: 'removePlayerFromOrg', player_id: player_id, org_id: org_id },
        function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (d.success) {
                jQuery('#player-org-row-' + player_id).fadeOut(300, function(){ jQuery(this).remove(); });
                var cur = parseInt(jQuery('#org-player-count').text()) || 0;
                jQuery('#org-player-count').text(Math.max(0, cur - 1));
            }
        }
    );
}

// ── Player search (add-player-box style) ─────────────────────────────────────
function orgApFind() {
    var s = jQuery('#org-ap-search').val().trim();
    if (!s) return;
    jQuery('#org-ap-status').removeClass().addClass('br-ap-status br-ap-status-hint').text('<?= esc_js(__('Searching…','bluerabbit')); ?>');
    jQuery('#org-ap-results').empty();
    jQuery('#org-ap-clear').removeClass('br-initially-hidden');
    jQuery.post(runAJAX.ajaxurl, {
        action: 'findPlayersToOrg',
        nonce: jQuery('#search-player-nonce').val(),
        search_string: s
    }, function(html) {
        var $res = jQuery('#org-ap-results').html(html || '');
        var count = $res.find('.br-ap-row').length;
        if (count > 0) {
            jQuery('#org-ap-status').removeClass().addClass('br-ap-status br-ap-status-found')
                .text(count + ' <?= esc_js(__('result','bluerabbit')); ?>' + (count !== 1 ? 's' : ''));
        } else {
            jQuery('#org-ap-status').removeClass().addClass('br-ap-status br-ap-status-empty')
                .text('<?= esc_js(__('No results found.','bluerabbit')); ?>');
        }
    });
}

function orgApClearSearch() {
    jQuery('#org-ap-search').val('');
    jQuery('#org-ap-results').empty();
    jQuery('#org-ap-clear').addClass('br-initially-hidden');
    jQuery('#org-ap-status').removeClass().addClass('br-ap-status').text('');
}

function orgApAddPlayer(player_id, btn) {
    var $btn = jQuery(btn);
    var $row = jQuery('#org-ap-row-' + player_id);
    $btn.prop('disabled', true).html('<span class="br-ap-spin icon icon-refresh"></span>');
    jQuery.post(runAJAX.ajaxurl, {
        action: 'addPlayerToOrg',
        org_id: jQuery('#the_org_id').val(),
        player_id: player_id
    }, function(html) {
        if (html && html.trim()) {
            var name = $row.find('.br-ap-name').text().trim() || ('Player ' + player_id);
            $row.addClass('br-ap-row-done');
            $row.find('.br-ap-action').html('<span class="br-badge br-badge-green"><span class="icon icon-check"></span></span>');
            var $log = jQuery('#org-ap-log');
            var $list = jQuery('#org-ap-log-list');
            $log.removeClass('br-initially-hidden');
            $list.prepend('<li><span class="icon icon-check"></span> ' + jQuery('<span>').text(name).html() + '</li>');
            jQuery('#org-ap-log-count').text($list.find('li').length);
            jQuery('#org-players-list').append(html);
            var cur = parseInt(jQuery('#org-player-count').text()) || 0;
            jQuery('#org-player-count').text(cur + 1);
        } else {
            $row.addClass('br-ap-row-error');
            $row.find('.br-ap-action').html('<span class="br-badge br-badge-red"><span class="icon icon-cancel"></span></span>');
        }
    });
}

// ── CSV import players to org (batched, per-email logging) ────────────────────
function orgImportPlayersCsv() {
    var fileInput = document.getElementById('org-csv-players');
    if (!fileInput || !fileInput.files[0]) { alert('<?= esc_js(__('Please select a CSV file.','bluerabbit')); ?>'); return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        var lines  = e.target.result.split(/[\r\n]+/);
        var emails = [];
        lines.forEach(function(line){
            var cell = line.split(',')[0].replace(/['"]/g, '').trim().toLowerCase();
            if (cell && cell.indexOf('@') > -1 && cell !== 'email') emails.push(cell);
        });
        if (!emails.length) { alert('<?= esc_js(__('No valid emails found.','bluerabbit')); ?>'); return; }

        var CHUNK = 20, chunks = [], i;
        for (i = 0; i < emails.length; i += CHUNK) chunks.push(emails.slice(i, i + CHUNK));
        var total = emails.length, done = 0, addedTotal = 0, alreadyInTotal = 0, notFoundTotal = 0;

        brOpConsoleOpen('<?= esc_js(__('Import Players to Org','bluerabbit')); ?>');
        brOpConsoleLog(emails.length + ' <?= esc_js(__('email addresses found in CSV…','bluerabbit')); ?>', 'info');
        brOpConsoleSetProgress(0, total);

        function processChunk(idx) {
            if (idx >= chunks.length) {
                var summary = addedTotal + ' <?= esc_js(__('added','bluerabbit')); ?>, '
                    + alreadyInTotal + ' <?= esc_js(__('already in org','bluerabbit')); ?>';
                if (notFoundTotal) summary += ', ' + notFoundTotal + ' <?= esc_js(__('not found','bluerabbit')); ?>';
                brOpConsoleDone(summary);
                var cur = parseInt(jQuery('#org-player-count').text()) || 0;
                jQuery('#org-player-count').text(cur + addedTotal);
                fileInput.value = '';
                return;
            }
            jQuery.post(runAJAX.ajaxurl, {
                action: 'importPlayersToOrg',
                org_id: jQuery('#the_org_id').val(),
                emails: chunks[idx]
            }, function(r) {
                var d = (typeof r === 'string') ? JSON.parse(r) : r;
                if (!d.success) { brOpConsoleLog(d.message || '<?= esc_js(__('Error','bluerabbit')); ?>', 'error'); brOpConsoleDone(); return; }
                addedTotal     += d.added      || 0;
                alreadyInTotal += d.already_in || 0;
                notFoundTotal  += d.not_found  || 0;
                done += chunks[idx].length;
                (d.added_emails      || []).forEach(function(em){ brOpConsoleLog('+ ' + em, 'success'); });
                (d.already_in_emails || []).forEach(function(em){ brOpConsoleLog('= ' + em, 'info'); });
                (d.not_found_emails  || []).forEach(function(em){ brOpConsoleLog('? ' + em, 'warn'); });
                brOpConsoleSetProgress(done, total);
                processChunk(idx + 1);
            }).fail(function(){ brOpConsoleLog('<?= esc_js(__('Request failed','bluerabbit')); ?>', 'error'); brOpConsoleDone(); });
        }
        processChunk(0);
    };
    reader.readAsText(fileInput.files[0]);
}

// ── Bulk add players from adventure ──────────────────────────────────────────
function orgBulkFromAdventure() {
    var adv_id = jQuery('#org-bulk-adventure-select').val();
    if (!adv_id) { alert('<?= esc_js(__('Please select an adventure.','bluerabbit')); ?>'); return; }
    var adv_name = jQuery('#org-bulk-adventure-select option:selected').text();
    if (!confirm('<?= esc_js(__('Add all enrolled players from','bluerabbit')); ?> "' + adv_name + '" <?= esc_js(__('to this org?','bluerabbit')); ?>')) return;
    brOpConsoleOpen('<?= esc_js(__('Bulk Add from Adventure','bluerabbit')); ?>');
    brOpConsoleLog('<?= esc_js(__('Processing','bluerabbit')); ?> ' + adv_name + '…', 'info');
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'bulkPlayersFromAdventure', org_id: jQuery('#the_org_id').val(), adventure_id: adv_id },
        success: function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (!d.success) { brOpConsoleLog(d.message || '<?= esc_js(__('Error','bluerabbit')); ?>', 'error'); brOpConsoleDone(); return; }
            brOpConsoleDone(d.added + ' <?= esc_js(__('added','bluerabbit')); ?>, ' + d.already_in + ' <?= esc_js(__('already in org','bluerabbit')); ?> — ' + d.total + ' <?= esc_js(__('total in adventure','bluerabbit')); ?>.');
            var cur = parseInt(jQuery('#org-player-count').text()) || 0;
            jQuery('#org-player-count').text(cur + d.added);
        },
        error: function(){ brOpConsoleLog('<?= esc_js(__('Request failed','bluerabbit')); ?>', 'error'); brOpConsoleDone(); }
    });
}

// ── Adventure search & add ────────────────────────────────────────────────────
function orgSearchAdventures() {
    var s = jQuery('#adv-search-string').val().trim();
    if (!s) return;
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'searchAdventuresForOrg', org_id: jQuery('#the_org_id').val(), search: s },
        success: function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (!d.success || !d.results.length) {
                jQuery('#org-adv-search-results').html('<li class="br-form-hint padding-10"><?= esc_js(__('No adventures found.','bluerabbit')); ?></li>');
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

function orgAddAdventure(adventure_id, el) {
    jQuery(el).css('opacity', 0.4);
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'addAdventureToOrg', org_id: jQuery('#the_org_id').val(), adventure_id: adventure_id },
        success: function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (!d.success) return;
            jQuery(el).remove();
            var enroll = d.adventure_code ? '<?= esc_js(get_bloginfo('url')); ?>/enroll/?enroll_code=' + d.adventure_code : '—';
            var row = '<tr id="org-adv-row-' + d.adventure_id + '">'
                + '<td>' + d.adventure_id + '</td>'
                + '<td>' + d.adventure_title + '</td>'
                + '<td>' + (d.owner_name || '—') + '</td>'
                + '<td>' + (d.adventure_code ? '<a href="' + enroll + '" target="_blank">' + enroll + '</a>' : '—') + '</td>'
                + '<td><button class="br-btn red br-btn-sm" onclick="brConfirmInline(this,\'<?= esc_js(__('Remove?','bluerabbit')); ?>\',function(){ orgRemoveAdventure(' + d.adventure_id + '); });" title="Remove"><span class="icon icon-cancel"></span></button></td>'
                + '</tr>';
            jQuery('#org-adventures-list').append(row);
            jQuery('#adv-search-string').val('');
            jQuery('#org-adv-search-results').empty();
            var cnt = parseInt(jQuery('#org-adv-count').text()) || 0;
            jQuery('#org-adv-count').text(cnt + 1);
        }
    });
}

function orgRemoveAdventure(adventure_id) {
    jQuery.ajax({
        url: runAJAX.ajaxurl, method: 'POST',
        data: { action: 'removeAdventureFromOrg', org_id: jQuery('#the_org_id').val(), adventure_id: adventure_id },
        success: function(r) {
            var d = (typeof r === 'string') ? JSON.parse(r) : r;
            if (d.success) {
                jQuery('#org-adv-row-' + adventure_id).fadeOut(300, function(){ jQuery(this).remove(); });
                var cnt = parseInt(jQuery('#org-adv-count').text()) || 0;
                jQuery('#org-adv-count').text(Math.max(0, cnt - 1));
            }
        }
    });
}

// ── Stats tab ────────────────────────────────────────────────────────────────
var orgStatsInited = false;
function orgStatsInit() {
    if (orgStatsInited) return; orgStatsInited = true;
    if (typeof jQuery.fn.datetimepicker !== 'undefined') {
        jQuery('#org-activity-from, #org-activity-to').datetimepicker({ format: 'Y-m-d', timepicker: false });
    }
    if (typeof brOrgChartsInit === 'function') brOrgChartsInit();
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
jQuery(function($){
    $('#adv-search-string').on('keyup', function(e){ if (e.key === 'Enter') orgSearchAdventures(); });
    $('#org-ap-search').on('keyup', function(e){ if (e.key === 'Enter') orgApFind(); });
});
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

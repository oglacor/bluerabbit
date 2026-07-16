<?php
/**
 * Journey Manager — manage-journey.php
 * Included from page-manage-adventure.php
 * New dark-theme UX using br-stats / br-panel design system
 */

// ── Backward-compatible GET params ──────────────────────────
if (isset($_GET['path'])) {
    $path_str = '&path=' . $_GET['path'];
    $path     = $_GET['path'];
} else {
    $path_str = '';
    $path     = '';
}

$sort_mode = isset($_GET['order']) ? $_GET['order'] : '';
$show_added = false;
$h = false;

switch ($sort_mode) {
    case 'A':
        $show_added = true;
        $order = 'mech_level, mech_start_date, quest_order, quest_title';
        break;
    case 'B':
        $order = 'mech_start_date ASC, mech_level, quest_order, quest_title';
        break;
    case 'C':
        $order = 'mech_deadline DESC, mech_level, quest_order, quest_title';
        break;
    case 'D':
        $order = 'quest_title, mech_level, quest_id';
        break;
    default:
        $h = true;
        $order = 'quest_order, mech_level, mech_start_date, quest_title';
        break;
}

// ── Data loading ────────────────────────────────────────────
$quests       = BR_Quest::instance()->getQuests($adventure->adventure_id, '', "blog-post' AND quest_type!='lore", $order, $path);
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, "path|rank");
$tabis        = BR_Tabi::instance()->getTabis($adventure->adventure_id);

// ── Build JSON payload for JS ───────────────────────────────
$publish_items = isset($quests['publish']) ? $quests['publish'] : [];
$draft_items   = isset($quests['draft'])   ? $quests['draft']   : [];
$trash_items   = isset($quests['trash'])   ? $quests['trash']   : [];

$json_publish = [];
foreach ($publish_items as $q) {
    $start = ($q->mech_start_date && $q->mech_start_date !== '0000-00-00 00:00:00')
        ? date('Y/m/d H:i', strtotime($q->mech_start_date)) : '';
    $deadline = ($q->mech_deadline && $q->mech_deadline !== '0000-00-00 00:00:00')
        ? date('Y/m/d H:i', strtotime($q->mech_deadline)) : '';
    $json_publish[] = [
        'id'             => $q->quest_id,
        'type'           => $q->quest_type,
        'title'          => $q->quest_title,
        'level'          => (int)$q->mech_level,
        'xp'             => (int)$q->mech_xp,
        'bloo'           => (int)$q->mech_bloo,
        'ep'             => (int)$q->mech_ep,
        'badge'          => $q->mech_badge,
        'color'          => $q->quest_color,
        'icon'           => $q->quest_icon,
        'status'         => $q->quest_status,
        'start_date'     => $start,
        'deadline'       => $deadline,
        'achievement_id' => $q->achievement_id ? (int)$q->achievement_id : 0,
        'tabi_id'        => $q->tabi_id ? (int)$q->tabi_id : 0,
        'validate'       => (int)$q->mech_validate,
        'optional'       => (int)$q->mech_optional,
    ];
}

$json_drafts = [];
foreach ($draft_items as $q) {
    $json_drafts[] = [
        'id'             => $q->quest_id,
        'type'           => $q->quest_type,
        'title'          => $q->quest_title,
        'level'          => (int)$q->mech_level,
        'xp'             => (int)$q->mech_xp,
        'bloo'           => (int)$q->mech_bloo,
        'ep'             => (int)$q->mech_ep,
        'achievement_id' => $q->achievement_id ? (int)$q->achievement_id : 0,
    ];
}

$json_trash = [];
foreach ($trash_items as $q) {
    $json_trash[] = [
        'id'    => $q->quest_id,
        'type'  => $q->quest_type,
        'title' => $q->quest_title,
        'level' => (int)$q->mech_level,
        'xp'    => (int)$q->mech_xp,
        'bloo'  => (int)$q->mech_bloo,
    ];
}

$json_achievements = [];
if (isset($achievements['publish'])) {
    foreach ($achievements['publish'] as $a) {
        $json_achievements[] = [
            'id'   => $a->achievement_id,
            'name' => $a->achievement_name,
        ];
    }
}

$json_tabis = [];
if ($tabis) {
    foreach ($tabis as $t) {
        $json_tabis[] = [
            'id'   => $t->tabi_id,
            'name' => $t->tabi_name,
        ];
    }
}

// Resource autofill setting
$resource_autofill = isset($config['resource_auto_fill']) ? $config['resource_auto_fill'] : 0;

// Counts
$count_publish = count($publish_items);
$counts = ['quest' => 0, 'mission' => 0, 'challenge' => 0, 'survey' => 0];
$total_xp = 0;
$total_bloo = 0;
foreach ($publish_items as $q) {
    if (isset($counts[$q->quest_type])) $counts[$q->quest_type]++;
    $total_xp   += (int)$q->mech_xp;
    $total_bloo += (int)$q->mech_bloo;
}

$base_url = get_bloginfo('url');
?>

<!-- Journey Manager styles are in css/br-table.css -->

<div class="br-journey-manager">

    <!-- ════════════ HEADER BAR ════════════ -->
    <div class="br-header">
        <div class="br-header-left">
            <div class="br-icon"><span class="icon icon-journey"></span></div>
            <div>
                <h2><?php _e('Journey Manager', 'bluerabbit'); ?></h2>
                <div class="br-header-counts">
                    <span class="br-count-badge"><span class="br-cnt"><?= $count_publish; ?></span> <?php _e('Total', 'bluerabbit'); ?></span>
                    <?php if ($counts['quest'] > 0) { ?><span class="br-count-badge type-quest"><span class="br-cnt"><?= $counts['quest']; ?></span> <?php _e('Milestones', 'bluerabbit'); ?></span><?php } ?>
                    <?php if ($counts['challenge'] > 0) { ?><span class="br-count-badge type-challenge"><span class="br-cnt"><?= $counts['challenge']; ?></span> <?php _e('Challenges', 'bluerabbit'); ?></span><?php } ?>
                </div>
            </div>
        </div>
        <div class="br-header-right">
            <form id="upload_bulk_quests_form" class="br-bulk-upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">
                <input type="file" name="the_csv_file_with_quests" id="the_csv_file_with_quests" />
                <button type="button" class="br-btn ghost" onclick="uploadBulkQuests();">
                    <span class="icon icon-upload"></span> <?= __('Bulk Upload', 'bluerabbit'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- ════════════ STICKY TOOLBAR ════════════ -->
    <div class="br-toolbar">
        <!-- Search -->
        <div class="br-search">
            <span class="icon icon-search"></span>
            <input type="text" id="br-search-input" placeholder="<?php _e('Search milestones...', 'bluerabbit'); ?>">
        </div>

        <!-- Type Filter Buttons -->
        <div class="br-type-filters">
            <button class="br-type-btn t-all active" data-filter="all" title="All"><span class="icon icon-infinite"></span></button>
            <button class="br-type-btn t-quest" data-filter="quest" title="Milestones"><span class="icon icon-quest"></span></button>
            <button class="br-type-btn t-challenge" data-filter="challenge" title="Challenges"><span class="icon icon-challenge"></span></button>
        </div>

        <!-- Path dropdown -->
        <select id="br-path-filter">
            <option value=""><?= __('All Paths', 'bluerabbit'); ?></option>
            <?php if (isset($achievements['publish'])) { foreach ($achievements['publish'] as $a) { ?>
                <option value="<?= $a->achievement_id; ?>" <?= ($path == $a->achievement_id) ? 'selected' : ''; ?>><?= $a->achievement_name; ?></option>
            <?php } } ?>
        </select>

        <!-- Tabi dropdown -->
        <select id="br-tabi-filter">
            <option value=""><?= __('All Tabis', 'bluerabbit'); ?></option>
            <?php if ($tabis) { foreach ($tabis as $t) { ?>
                <option value="<?= $t->tabi_id; ?>"><?= $t->tabi_name; ?></option>
            <?php } } ?>
        </select>

        <!-- Sort dropdown -->
        <select id="br-sort">
            <option value="" <?= $sort_mode === '' ? 'selected' : ''; ?>><?= __('Journey Order', 'bluerabbit'); ?></option>
            <option value="A" <?= $sort_mode === 'A' ? 'selected' : ''; ?>><?= __('Level', 'bluerabbit'); ?></option>
            <option value="B" <?= $sort_mode === 'B' ? 'selected' : ''; ?>><?= __('Start Date', 'bluerabbit'); ?></option>
            <option value="C" <?= $sort_mode === 'C' ? 'selected' : ''; ?>><?= __('Deadline', 'bluerabbit'); ?></option>
            <option value="D" <?= $sort_mode === 'D' ? 'selected' : ''; ?>><?= __('Title', 'bluerabbit'); ?></option>
        </select>

        <!-- Resource Autofill -->
        <div class="br-autofill-wrap">
            <span><?= __('Autofill', 'bluerabbit'); ?>:</span>
            <div id="resource_autofill">
                <select id="resource-autofill" class="setting-value" onchange="saveSetting('#resource_autofill');">
                    <option value="0"><?= __('Off', 'bluerabbit'); ?></option>
                    <option value="65" <?= (is_array($resource_autofill) && $resource_autofill['value'] == '65') ? 'selected' : ''; ?>><?= __('Easy', 'bluerabbit'); ?></option>
                    <option value="50" <?= (is_array($resource_autofill) && $resource_autofill['value'] == '50') ? 'selected' : ''; ?>><?= __('Normal', 'bluerabbit'); ?></option>
                    <option value="35" <?= (is_array($resource_autofill) && $resource_autofill['value'] == '35') ? 'selected' : ''; ?>><?= __('Hard', 'bluerabbit'); ?></option>
                    <option value="25" <?= (is_array($resource_autofill) && $resource_autofill['value'] == '25') ? 'selected' : ''; ?>><?= __('Very Hard', 'bluerabbit'); ?></option>
                    <option value="10" <?= (is_array($resource_autofill) && $resource_autofill['value'] == '10') ? 'selected' : ''; ?>><?= __('Legendary', 'bluerabbit'); ?></option>
                </select>
                <input type="hidden" class="setting-id" value="<?= is_array($resource_autofill) ? $resource_autofill['id'] : ''; ?>">
                <input type="hidden" class="setting-name" value="resource_autofill">
                <input type="hidden" class="setting-label" value="Resource Autofill">
            </div>
        </div>
    </div>

    <!-- ════════════ PATH BANNER (if filtered) ════════════ -->
    <?php if (isset($current_path)) { ?>
        <div class="br-path-banner">
            <div class="br-path-thumb" style="background-image: url(<?= $current_path->achievement_badge; ?>);"></div>
            <div>
                <div class="br-path-name"><?= $current_path->achievement_name; ?></div>
                <div class="br-path-sub"><?= __('Currently filtering this path', 'bluerabbit'); ?></div>
            </div>
        </div>
    <?php } ?>

    <!-- ════════════ MILESTONE TABLE ════════════ -->
    <div class="br-table-wrap" id="table-quest">
        <!-- Table Header -->
        <div class="br-table-header <?= $use_encounters ? 'with-ep' : ''; ?>">
            <div>&nbsp;</div><!-- drag -->
            <div>&nbsp;</div><!-- type -->
            <div>&nbsp;</div><!-- thumb -->
            <div><?= __('Name', 'bluerabbit'); ?></div>
            <div class="br-text-center"><?= __('Lv', 'bluerabbit'); ?></div>
            <div class="br-text-center"><?= $xp_label; ?></div>
            <div class="br-text-center"><?= $bloo_label; ?></div>
            <?php if ($use_encounters) { ?>
                <div class="br-text-center"><?= $ep_label; ?></div>
            <?php } ?>
            <div class="br-text-center"><?= __('Status', 'bluerabbit'); ?></div>
        </div>

        <!-- JS renders one .br-tabi-group per tabi (plus "Other Milestones") here -->
        <div id="br-milestone-list">
            <!-- Rendered by brJourney.renderGroups() -->
        </div>

        <!-- Empty state (shown by JS if no items) -->
        <div class="br-empty" id="br-empty-state" style="display:none;">
            <span class="icon icon-cancel"></span>
            <?php _e('No milestones found', 'bluerabbit'); ?>
        </div>
    </div>

    <!-- ════════════ DRAFT SECTION ════════════ -->
    <div class="br-section" id="br-drafts-section">
        <div class="br-section-header" id="br-drafts-toggle">
            <h3>
                <span class="icon icon-document br-icon-accent"></span>
                <?php _e('Journey Drafts', 'bluerabbit'); ?>
                <span class="br-count-badge" id="br-draft-count"><?= count($draft_items); ?></span>
            </h3>
            <span class="br-toggle-icon icon icon-down"></span>
        </div>
        <div class="br-section-body" id="br-drafts-body">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- ════════════ TRASH SECTION ════════════ -->
    <div class="br-section" id="br-trash-section">
        <div class="br-section-header collapsed" id="br-trash-toggle">
            <h3>
                <span class="icon icon-trash br-icon-red"></span>
                <?php _e('Journey Trash', 'bluerabbit'); ?>
                <span class="br-count-badge" id="br-trash-count"><?= count($trash_items); ?></span>
            </h3>
            <span class="br-toggle-icon icon icon-down"></span>
        </div>
        <div class="br-section-body collapsed" id="br-trash-body">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- Hidden inputs required by existing JS functions -->
    <input type="hidden" id="row_type" value="quest"/>

</div><!-- /.br-journey-manager -->

<!-- ════════════ STICKY SUMMARY BAR ════════════ -->
<div class="br-summary-bar" id="br-summary-bar">
    <div class="br-summary-stat">
        <span class="icon icon-quest br-icon-primary"></span>
        <div>
            <span class="br-stat-val" id="br-total-count"><?= $count_publish; ?></span>
            <span class="br-stat-label"><?= __('Milestones', 'bluerabbit'); ?></span>
        </div>
    </div>
    <div class="br-summary-stat">
        <span class="icon icon-star br-icon-accent"></span>
        <div>
            <span class="br-stat-val" id="br-total-xp"><?= $total_xp; ?></span>
            <span class="br-stat-label"><?= __('Total', 'bluerabbit') . ' ' . $xp_label; ?></span>
        </div>
    </div>
    <div class="br-summary-stat">
        <span class="icon icon-bloo br-icon-green"></span>
        <div>
            <span class="br-stat-val" id="br-total-bloo"><?= $total_bloo; ?></span>
            <span class="br-stat-label"><?= __('Total', 'bluerabbit') . ' ' . $bloo_label; ?></span>
        </div>
    </div>
    <button class="br-btn cyan" onclick="reorder();">
        <span class="icon icon-list"></span> <?php _e('Reorder Journey', 'bluerabbit'); ?>
    </button>
</div>

<!-- ════════════════════════════════════════════════════════
     JAVASCRIPT — brJourney namespace
     ════════════════════════════════════════════════════════ -->
<script>
(function($) {
    'use strict';

    var baseUrl       = <?= json_encode($base_url); ?>;
    var adventureId   = <?= json_encode($adventure->adventure_id); ?>;
    var useEncounters = <?= $use_encounters ? 'true' : 'false'; ?>;
    var xpLabel       = <?= json_encode($xp_label); ?>;
    var blooLabel     = <?= json_encode($bloo_label); ?>;
    var epLabel       = <?= json_encode($ep_label); ?>;
    var templateUri   = <?= json_encode(get_template_directory_uri()); ?>;
    var isJourneyOrder = <?= $h ? 'true' : 'false'; ?>;

    // ── Data ─────────────────────────────────────────────────
    window.brJourney = {
        allItems:      <?= json_encode($json_publish); ?>,
        draftItems:    <?= json_encode($json_drafts); ?>,
        trashItems:    <?= json_encode($json_trash); ?>,
        achievements:  <?= json_encode($json_achievements); ?>,
        tabis:         <?= json_encode($json_tabis); ?>,
        filteredItems: [],
        groups:        [],
        activeFilter:  'all',
        searchTerm:    '',
        pathFilter:    '',
        tabiFilter:    ''
    };

    var brj = window.brJourney;

    // ── Filter logic ─────────────────────────────────────────
    brj.applyFilters = function() {
        var search = brj.searchTerm.toLowerCase();
        brj.filteredItems = brj.allItems.filter(function(item) {
            // Type filter
            if (brj.activeFilter !== 'all' && item.type !== brj.activeFilter) return false;
            // Search filter
            if (search && item.title.toLowerCase().indexOf(search) === -1) return false;
            // Path filter
            if (brj.pathFilter && item.achievement_id != brj.pathFilter) return false;
            // Tabi filter
            if (brj.tabiFilter && item.tabi_id != brj.tabiFilter) return false;
            return true;
        });
        brj.buildGroups();
        brj.renderGroups();
        brj.updateSummary();
        brj.toggleEmpty();
    };

    // ── Group filtered items by tabi, "Other Milestones" first ──────
    brj.buildGroups = function() {
        var otherLabel = <?= json_encode(__('Other Milestones', 'bluerabbit')); ?>;
        var byTabi = {};
        for (var i = 0; i < brj.filteredItems.length; i++) {
            var item = brj.filteredItems[i];
            var tid = item.tabi_id || 0;
            if (!byTabi[tid]) byTabi[tid] = [];
            byTabi[tid].push(item);
        }
        var knownTabiIds = {};
        for (var t = 0; t < brj.tabis.length; t++) knownTabiIds[brj.tabis[t].id] = true;

        var groups = [];
        if (byTabi[0] && byTabi[0].length) {
            groups.push({ tabiId: 0, tabiName: otherLabel, items: byTabi[0] });
        }
        for (var t = 0; t < brj.tabis.length; t++) {
            var tab = brj.tabis[t];
            if (byTabi[tab.id] && byTabi[tab.id].length) {
                groups.push({ tabiId: tab.id, tabiName: tab.name, items: byTabi[tab.id] });
            }
        }
        // A quest can point at a tabi that's since been trashed/unpublished (so it's
        // missing from brj.tabis) - fall back to "Other" instead of hiding it entirely.
        for (var tid in byTabi) {
            if (tid == 0 || knownTabiIds[tid]) continue;
            groups.push({ tabiId: tid, tabiName: otherLabel, items: byTabi[tid] });
        }
        brj.groups = groups;
    };

    // ── Build a single row HTML ──────────────────────────────
    brj.buildRow = function(item) {
        var epCol = useEncounters ? 'with-ep' : '';
        var statusClass = item.status === 'locked' ? 'st-locked' : 'st-publish';
        var statusIcon  = item.status === 'locked' ? 'icon-lock' : 'icon-check';
        var statusText  = item.status === 'locked' ? 'Locked' : 'Published';
        var dragClass   = isJourneyOrder ? '' : 'hidden-handle';

        var reportLink = '';
        if (item.type === 'challenge') {
            reportLink = '<a href="' + baseUrl + '/challenges-report/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-action-link" target="_blank"><span class="icon icon-chart"></span> Challenge Report</a>';
        } else if (item.type === 'survey') {
            reportLink = '<a href="' + baseUrl + '/survey-results/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-action-link" target="_blank"><span class="icon icon-chart"></span> Survey Results</a>';
        } else if (item.type === 'quest') {
            reportLink = '<a href="' + baseUrl + '/review-player-posts/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-action-link" target="_blank"><span class="icon icon-document"></span> Review</a>';
        }

        var thumbStyle = item.badge ? 'background-image:url(' + item.badge + ')' : '';

        var html = '';
        html += '<div class="row-container ' + item.type + '" id="' + item.type + '-' + item.id + '">';

        // Main data row
        html += '<div class="row br-row ' + epCol + '">';
        html += '  <div class="br-drag ' + dragClass + '"><img src="' + templateUri + '/images/drag-handle.svg" class="drag-icon"></div>';
        html += '  <div class="br-type-icon ' + item.type + '"><span class="icon icon-' + item.type + '"></span></div>';
        html += '  <div class="br-thumb" style="' + thumbStyle + '" onclick="showWPUpload(\'the_quest_badge-' + item.id + '\',\'a\',\'quest\',' + item.id + ');" id="the_quest_badge-' + item.id + '_thumb"><input type="hidden" value="' + (item.badge || '') + '" id="the_quest_badge-' + item.id + '"></div>';
        html += '  <div class="br-name">';
        html += '    <input type="text" id="the_title-' + item.type + '-' + item.id + '" value="' + brj.escAttr(item.title) + '" onchange="setTitle(' + item.id + ',\'' + item.type + '\');">';
        html += '    <input type="hidden" class="quest-id" value="' + item.id + '">';
        html += '  </div>';
        html += '  <div class="br-num"><input type="number" id="the_level-' + item.type + '-' + item.id + '" value="' + item.level + '" onchange="setLevel(' + item.id + ',\'' + item.type + '\');"></div>';
        html += '  <div class="br-num"><span class="icon icon-star"></span><input type="number" id="the_xp-' + item.type + '-' + item.id + '" value="' + item.xp + '" onchange="setXP(' + item.id + ',\'' + item.type + '\');"></div>';
        html += '  <div class="br-num"><span class="icon icon-bloo"></span><input type="number" id="the_bloo-' + item.type + '-' + item.id + '" value="' + item.bloo + '" onchange="setBLOO(' + item.id + ',\'' + item.type + '\');"></div>';
        if (useEncounters) {
            html += '  <div class="br-num"><span class="icon icon-activity"></span><input type="number" id="the_ep-' + item.type + '-' + item.id + '" value="' + item.ep + '" onchange="setEP(' + item.id + ',\'' + item.type + '\');"></div>';
        }
        html += '  <div class="br-status-cell">';
        html += '    <span class="br-status ' + statusClass + '"><span class="icon ' + statusIcon + '"></span> ' + statusText + '</span>';
        html += '    <span class="br-req-badge ' + (item.optional ? 'sidequest' : 'required') + '">' + (item.optional ? 'Side Quest' : 'Required') + '</span>';
        html += '  </div>';
        html += '</div>';

        // Action bar row
        html += '<div class="br-action-bar">';
        html += '  <a href="' + baseUrl + '/' + item.type + '/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-action-link" target="_blank"><span class="icon icon-view"></span> View</a>';
        html += '  <a href="' + baseUrl + '/new-' + item.type + '/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-action-link edit"><span class="icon icon-edit"></span> Edit</a>';
        html += '  <button class="br-action-link expand" data-target="br-qe-' + item.id + '"><span class="icon icon-down"></span> Quick Edit</button>';
        html += reportLink;
        if (item.status === 'publish') {
            html += '  <button class="br-action-link lock" onclick="brJourney.changeStatus(' + item.id + ',\'' + item.type + '\',\'locked\');"><span class="icon icon-lock"></span> Lock</button>';
        } else if (item.status === 'locked') {
            html += '  <button class="br-action-link unlock" onclick="brJourney.changeStatus(' + item.id + ',\'' + item.type + '\',\'publish\');"><span class="icon icon-check"></span> Unlock</button>';
        }
        if (item.type === 'quest') {
            var validateClass = item.validate ? 'validate-toggle on' : 'validate-toggle';
            var validateLabel = item.validate ? 'Validation Required' : 'Require Validation';
            html += '  <button class="br-action-link ' + validateClass + '" onclick="brJourney.toggleValidate(' + item.id + ');"><span class="icon icon-check"></span> ' + validateLabel + '</button>';
        }
        var optionalClass = item.optional ? 'optional-toggle on' : 'optional-toggle';
        var optionalLabel  = item.optional ? 'Mark as Required' : 'Mark as Side Quest';
        html += '  <button class="br-action-link ' + optionalClass + '" onclick="brJourney.toggleOptional(' + item.id + ');"><span class="icon icon-star"></span> ' + optionalLabel + '</button>';
        html += '  <button class="br-action-link duplicate" onclick="duplicateRow(' + item.id + ');"><span class="icon icon-duplicate"></span> Duplicate</button>';
        html += '  <button class="br-action-link trash" onclick="brJourney.changeStatus(' + item.id + ',\'' + item.type + '\',\'trash\');"><span class="icon icon-trash"></span> Trash</button>';
        html += '</div>';

        // Quick edit panel
        html += '<div class="br-quick-edit" id="br-qe-' + item.id + '">';
        html += '  <div class="br-qe-grid">';
        html += '    <div class="br-qe-field"><label>' + 'Start Date' + '</label>';
        html += '      <input type="text" class="datetimepicker" autocomplete="off" id="the_start_date-' + item.type + '-' + item.id + '" value="' + brj.escAttr(item.start_date) + '" onchange="setStartDate(' + item.id + ',\'' + item.type + '\');">';
        html += '    </div>';
        html += '    <div class="br-qe-field"><label>' + 'Deadline' + '</label>';
        html += '      <input type="text" class="datetimepicker" autocomplete="off" id="the_deadline-' + item.type + '-' + item.id + '" value="' + brj.escAttr(item.deadline) + '" onchange="setDeadline(' + item.id + ',\'' + item.type + '\');">';
        html += '    </div>';
        html += '    <div class="br-qe-field"><label>' + 'Path' + '</label>';
        html += '      <select class="update-achievement" onchange="setAchievement(' + item.id + ',\'' + item.type + '\');">';
        html += '        <option value="0"' + (item.achievement_id == 0 ? ' selected' : '') + '>All Paths</option>';
        for (var a = 0; a < brj.achievements.length; a++) {
            var ach = brj.achievements[a];
            html += '        <option value="' + ach.id + '"' + (item.achievement_id == ach.id ? ' selected' : '') + '>' + brj.escHtml(ach.name) + '</option>';
        }
        html += '      </select>';
        html += '    </div>';
        html += '    <div class="br-qe-field"><label>' + 'Tabi' + '</label>';
        html += '      <select class="update-tabi" onchange="setQuestTabi(' + item.id + ',\'' + item.type + '\');">';
        html += '        <option value="0"' + (item.tabi_id == 0 ? ' selected' : '') + '>None</option>';
        for (var t = 0; t < brj.tabis.length; t++) {
            var tab = brj.tabis[t];
            html += '        <option value="' + tab.id + '"' + (item.tabi_id == tab.id ? ' selected' : '') + '>' + brj.escHtml(tab.name) + '</option>';
        }
        html += '      </select>';
        html += '    </div>';
        html += '  </div>';
        html += '  <div class="br-qe-actions">';
        html += '    <a href="' + baseUrl + '/new-' + item.type + '/?adventure_id=' + adventureId + '&questID=' + item.id + '" class="br-btn green"><span class="icon icon-edit"></span> Edit Full ' + item.type + '</a>';
        html += '  </div>';
        html += '</div>';

        html += '</div>'; // /.row-container
        return html;
    };

    // ── Render all tabi groups (one headline + row list per tabi) ────
    brj.renderGroups = function() {
        var container = $('#br-milestone-list');
        container.empty();

        for (var g = 0; g < brj.groups.length; g++) {
            var group = brj.groups[g];
            var reqCount = 0, sideCount = 0;
            for (var i = 0; i < group.items.length; i++) {
                if (group.items[i].optional) sideCount++; else reqCount++;
            }

            var html = '<div class="br-tabi-group">';
            html += '  <div class="br-tabi-headline' + (group.tabiId === 0 ? ' no-tabi' : '') + '">';
            html += '    <span class="icon icon-' + (group.tabiId === 0 ? 'journey' : 'sabotage') + '"></span>';
            html += '    <span class="br-tabi-title">' + brj.escHtml(group.tabiName) + '</span>';
            html += '    <span class="br-tabi-stats">';
            if (reqCount)  html += '<span class="br-tabi-stat required">' + <?= json_encode(__('Required', 'bluerabbit')); ?> + ': ' + reqCount + '</span>';
            if (reqCount && sideCount) html += '<span class="br-tabi-stat-divider">|</span>';
            if (sideCount) html += '<span class="br-tabi-stat sidequest">' + <?= json_encode(__('Side Quests', 'bluerabbit')); ?> + ': ' + sideCount + '</span>';
            html += '    </span>';
            html += '  </div>';
            html += '  <div class="br-tabi-group-rows br-sortable sortable-row-container">';
            for (var i = 0; i < group.items.length; i++) {
                html += brj.buildRow(group.items[i]);
            }
            html += '  </div>';
            html += '</div>';
            container.append(html);
        }

        // Init datetimepickers on the newly rendered rows
        if (typeof $.fn.datetimepicker === 'function') {
            container.find('.datetimepicker').not('.hasDatepicker').datetimepicker({
                format: 'Y/m/d H:i',
                formatDate: 'Y/m/d',
                formatTime: 'H:i'
            });
        }
        brj.setupSortable();
    };

    // ── Toggle empty state ───────────────────────────────────
    brj.toggleEmpty = function() {
        if (brj.filteredItems.length === 0) {
            $('#br-empty-state').show();
        } else {
            $('#br-empty-state').hide();
        }
    };

    // ── Update summary bar ───────────────────────────────────
    brj.updateSummary = function() {
        var totalXP = 0, totalBloo = 0;
        for (var i = 0; i < brj.filteredItems.length; i++) {
            totalXP   += brj.filteredItems[i].xp;
            totalBloo += brj.filteredItems[i].bloo;
        }
        $('#br-total-count').text(brj.filteredItems.length);
        $('#br-total-xp').text(totalXP);
        $('#br-total-bloo').text(totalBloo);
    };

    // ── Escape helpers ───────────────────────────────────────
    brj.escAttr = function(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    };
    brj.escHtml = function(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    };

    // ── Render Draft rows ────────────────────────────────────
    brj.renderDrafts = function() {
        var container = $('#br-drafts-body');
        if (brj.draftItems.length === 0) {
            container.html('<div class="br-empty"><span class="icon icon-cancel"></span> No draft milestones</div>');
            return;
        }
        var html = '';
        for (var i = 0; i < brj.draftItems.length; i++) {
            var d = brj.draftItems[i];
            html += '<div class="br-dt-row" id="' + d.type + '-' + d.id + '">';
            html += '  <div class="br-type-icon ' + d.type + '"><span class="icon icon-' + d.type + '"></span></div>';
            html += '  <div class="br-dt-title">';
            html += '    <span style="opacity:0.4;font-size:11px;">Lv' + d.level + '</span> ' + brj.escHtml(d.title);
            html += '    <input type="hidden" class="quest-id" value="' + d.id + '">';
            html += '  </div>';
            html += '  <div class="br-num"><span class="icon icon-star" style="opacity:0.4;font-size:11px;"></span><input type="number" id="the_xp-' + d.type + '-' + d.id + '" value="' + d.xp + '" onchange="setXP(' + d.id + ',\'' + d.type + '\');"></div>';
            html += '  <div class="br-num"><span class="icon icon-bloo" style="opacity:0.4;font-size:11px;"></span><input type="number" id="the_bloo-' + d.type + '-' + d.id + '" value="' + d.bloo + '" onchange="setBLOO(' + d.id + ',\'' + d.type + '\');"></div>';
            html += '  <div>';
            html += '    <select class="update-status" onchange="updateStatus(' + d.id + ',\'' + d.type + '\');">';
            html += '      <option value="">Draft</option>';
            html += '      <option value="trash">Trash</option>';
            html += '      <option value="publish">Publish</option>';
            html += '    </select>';
            html += '  </div>';
            html += '  <div class="br-dt-links">';
            html += '    <a href="' + baseUrl + '/new-' + d.type + '/?adventure_id=' + adventureId + '&questID=' + d.id + '" class="br-act-btn edit-link" title="Edit"><span class="icon icon-edit"></span></a>';
            html += '    <a href="' + baseUrl + '/' + d.type + '/?adventure_id=' + adventureId + '&questID=' + d.id + '" class="br-act-btn view-link" target="_blank" title="View"><span class="icon icon-view"></span></a>';
            html += '  </div>';
            html += '</div>';
        }
        container.html(html);
    };

    // ── Render Trash rows ────────────────────────────────────
    brj.renderTrash = function() {
        var container = $('#br-trash-body');
        if (brj.trashItems.length === 0) {
            container.html('<div class="br-empty"><span class="icon icon-cancel"></span> No milestones in trash</div>');
            return;
        }
        var html = '';
        // Empty trash button
        html += '<div style="padding:10px 14px;text-align:right;">';
        html += '  <button class="br-btn red" id="br-empty-trash-btn" onclick="brJourney.confirmEmptyTrash();">';
        html += '    <span class="icon icon-trash"></span> Empty Trash';
        html += '  </button>';
        html += '</div>';

        for (var i = 0; i < brj.trashItems.length; i++) {
            var t = brj.trashItems[i];
            html += '<div class="br-dt-row" id="' + t.type + '-' + t.id + '">';
            html += '  <div class="br-type-icon ' + t.type + '" style="opacity:0.4;"><span class="icon icon-' + t.type + '"></span></div>';
            html += '  <div class="br-dt-title" style="opacity:0.6;">';
            html += '    <span style="opacity:0.4;font-size:11px;">Lv' + t.level + '</span> ' + brj.escHtml(t.title);
            html += '    <input type="hidden" class="quest-id" value="' + t.id + '">';
            html += '  </div>';
            html += '  <div style="opacity:0.4;text-align:center;">' + t.xp + ' XP</div>';
            html += '  <div style="opacity:0.4;text-align:center;">' + t.bloo + ' B</div>';
            html += '  <div>';
            html += '    <select class="update-status" onchange="updateStatus(' + t.id + ',\'' + t.type + '\');">';
            html += '      <option value="">Trash</option>';
            html += '      <option value="publish">Publish</option>';
            html += '      <option value="draft">Draft</option>';
            html += '      <option value="delete">Delete</option>';
            html += '    </select>';
            html += '  </div>';
            html += '</div>';
        }
        container.html(html);
    };

    // ── Confirm Empty Trash ──────────────────────────────────
    brj.confirmEmptyTrash = function() {
        if (confirm('Are you sure you want to empty the trash? This cannot be undone.')) {
            emptyTrash('quest');
        }
    };

    // ── Setup drag-and-drop (one sortable list per tabi group - dragging  ──
    // reorders within a tabi; moving to a different tabi is done via the
    // Quick Edit "Tabi" dropdown instead). "Reorder Journey" still reads
    // every .row-container in DOM order across all groups, so the saved
    // quest_order naturally clusters by tabi too.
    brj.setupSortable = function() {
        if (!isJourneyOrder) return;
        if (typeof $.fn.sortable !== 'function') return;

        $('.br-tabi-group-rows').sortable({
            handle: '.br-drag:not(.hidden-handle)',
            items: '> .row-container',
            axis: 'y',
            tolerance: 'pointer',
            placeholder: 'br-sortable-placeholder',
            forcePlaceholderSize: true,
            helper: 'clone',
            start: function(e, ui) {
                // Close any open quick-edit panels before dragging
                ui.item.find('.br-quick-edit.open').removeClass('open');
                ui.item.find('.br-action-link.expand.open').removeClass('open');
                // Set helper and placeholder to match just the main row height
                var rowHeight = ui.item.find('.br-row').outerHeight() + ui.item.find('.br-action-bar').outerHeight();
                ui.placeholder.height(rowHeight + 4);
                ui.helper.css({'overflow': 'hidden', 'max-height': rowHeight + 'px', 'opacity': 0.85});
                ui.item.css('opacity', '0.3');
            },
            stop: function(e, ui) {
                ui.item.css('opacity', '1');
            }
        });
    };

    // ── Event bindings ───────────────────────────────────────

    // Search
    var searchTimer;
    $('#br-search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        var val = $(this).val();
        searchTimer = setTimeout(function() {
            brj.searchTerm = val;
            brj.applyFilters();
        }, 200);
    });

    // Type filter buttons
    $('.br-type-btn').on('click', function() {
        $('.br-type-btn').removeClass('active');
        $(this).addClass('active');
        brj.activeFilter = $(this).data('filter');
        brj.applyFilters();
    });

    // Path filter
    $('#br-path-filter').on('change', function() {
        var val = $(this).val();
        if (val) {
            // Navigate for server-side path filtering (backward compat)
            document.location.href = baseUrl + '/manage-adventure/?adventure_id=' + adventureId + '&path=' + val;
        } else {
            brj.pathFilter = '';
            brj.applyFilters();
            // Also navigate to remove path param
            document.location.href = baseUrl + '/manage-adventure/?adventure_id=' + adventureId;
        }
    });

    // Tabi filter (client-side)
    $('#br-tabi-filter').on('change', function() {
        brj.tabiFilter = $(this).val();
        brj.applyFilters();
    });

    // Sort dropdown — navigates to reload with server-side sort
    $('#br-sort').on('change', function() {
        var val = $(this).val();
        var url = baseUrl + '/manage-adventure/?adventure_id=' + adventureId;
        if (val) url += '&order=' + val;
        if (<?= json_encode($path); ?>) url += '&path=' + <?= json_encode($path); ?>;
        document.location.href = url;
    });

    // Quick-edit expand toggle
    $(document).on('click', '.br-action-link.expand', function(e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        var panel = $('#' + targetId);
        var isOpen = panel.hasClass('open');
        $('.br-quick-edit.open').removeClass('open');
        $('.br-action-link.expand.open').removeClass('open');
        if (!isOpen) {
            panel.addClass('open');
            $(this).addClass('open');
            if (typeof $.fn.datetimepicker === 'function') {
                panel.find('.datetimepicker').not('.hasDatepicker').datetimepicker({
                    format: 'Y/m/d H:i',
                    formatDate: 'Y/m/d',
                    formatTime: 'H:i'
                });
            }
        }
    });

    // ── Status change with UX update ────────────────────────
    brj.changeStatus = function(questId, questType, newStatus) {
        confirmStatus(questId, questType, newStatus);
        var $container = $('#' + questType + '-' + questId);
        if (newStatus === 'trash') {
            $container.slideUp(300, function() { $(this).remove(); });
            brj.allItems = brj.allItems.filter(function(i) { return i.id != questId; });
            brj.applyFilters();
        } else {
            var $badge = $container.find('.br-status');
            if (newStatus === 'locked') {
                $badge.removeClass('st-publish').addClass('st-locked').html('<span class="icon icon-lock"></span> Locked');
                $container.find('.br-action-link.lock').replaceWith(
                    '<button class="br-action-link unlock" onclick="brJourney.changeStatus(' + questId + ',\'' + questType + '\',\'publish\');"><span class="icon icon-check"></span> Unlock</button>'
                );
            } else if (newStatus === 'publish') {
                $badge.removeClass('st-locked').addClass('st-publish').html('<span class="icon icon-check"></span> Published');
                $container.find('.br-action-link.unlock').replaceWith(
                    '<button class="br-action-link lock" onclick="brJourney.changeStatus(' + questId + ',\'' + questType + '\',\'locked\');"><span class="icon icon-lock"></span> Lock</button>'
                );
            }
            for (var i = 0; i < brj.allItems.length; i++) {
                if (brj.allItems[i].id == questId) { brj.allItems[i].status = newStatus; break; }
            }
        }
    };

    // ── Toggle "Require validation before awarding" (milestones only) ──
    brj.toggleValidate = function(questId) {
        var item = null;
        for (var i = 0; i < brj.allItems.length; i++) {
            if (brj.allItems[i].id == questId) { item = brj.allItems[i]; break; }
        }
        if (!item) return;
        var newValidate = item.validate ? 0 : 1;
        setValidate(questId, 'quest', newValidate);
        item.validate = newValidate;

        var $btn = $('#quest-' + questId).find('.br-action-link.validate-toggle');
        $btn.toggleClass('on', !!newValidate);
        $btn.html('<span class="icon icon-check"></span> ' + (newValidate ? 'Validation Required' : 'Require Validation'));
    };

    // ── Toggle "Required" vs "Side Quest" (mech_optional) ───────────
    // Full re-render so the tabi headline's Required/Side Quest counts stay correct.
    brj.toggleOptional = function(questId) {
        var item = null;
        for (var i = 0; i < brj.allItems.length; i++) {
            if (brj.allItems[i].id == questId) { item = brj.allItems[i]; break; }
        }
        if (!item) return;
        var newOptional = item.optional ? 0 : 1;
        setOptional(questId, item.type, newOptional);
        item.optional = newOptional;
        brj.applyFilters();
    };

    // Section collapse toggles
    $('#br-drafts-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $('#br-drafts-body').toggleClass('collapsed');
    });
    $('#br-trash-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $('#br-trash-body').toggleClass('collapsed');
    });

    // ── Init ─────────────────────────────────────────────────
    $(document).ready(function() {
        brj.applyFilters(); // builds groups, renders rows, and inits sortable
        brj.renderDrafts();
        brj.renderTrash();
    });

})(jQuery);
</script>

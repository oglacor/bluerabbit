<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$org = BR_Organization::instance()->getOrgs(br_require_id('id'));
if (!$org) { echo '<p class="br-page">' . __('Organization not found.','bluerabbit') . '</p>'; include get_stylesheet_directory().'/footer.php'; exit; }

$org_players    = BR_Organization::instance()->getOrgPlayers($org->org_id);
$org_adventures = BR_Organization::instance()->getOrgAdventures($org->org_id);
$total_players  = count($org_players);
$total_advs     = count($org_adventures); 

// Only the KPI numbers are rendered server-side; everything the charts need is
// bootstrapped from br_org_stats_bootstrap() at enqueue time.
$org_stats = BR_OrgStats::instance()->get_org_summary($org->org_id);

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
        <!-- Kept in the DOM even when empty so saving the About editor can fill
             it without a reload; .br-org-about:empty hides itself. -->
        <div class="br-org-about" id="org-about-blurb"><?= wp_kses_post($org->org_content); ?></div>
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
                <?php
                // color-select.php reads $selected_color to mark the active swatch
                // and set the preview; without it the picker opens showing nothing.
                $color_select_id = "#the-org-color";
                $selected_color  = $org->org_color;
                include TEMPLATEPATH.'/color-select.php';
                ?>
            </div>

            <div class="br-form-group">
                <label class="br-form-label"><?= __('About','bluerabbit'); ?></label>
                <?php wp_editor($org->org_content, 'the-org-content', ['quicktags'=>true,'editor_height'=>250]); ?>
            </div>

            <div class="br-form-footer">
                <span></span>
                <button class="br-btn cyan" onclick="saveOrgSettings();">
                    <span class="icon icon-check"></span> <?= __('Update Organization','bluerabbit'); ?>
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
                <span class="br-form-hint"><?= __('Copies everyone from a chosen adventure into this organization — players, game masters, NPCs and the adventure owner.','bluerabbit'); ?></span>
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
                            <th title="<?= esc_attr(__('Org Manager','bluerabbit')); ?>"><span class="icon icon-star"></span></th>
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
            <div id="org-adv-search-results" class="br-ap-results"></div>
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

        <!-- Engagement Overview — gauge + distribution, one row per unique player -->
        <div class="br-panel br-stats-loadable" id="org-eng-panel">
            <div class="br-stats-panel-loader"><?= __('Calculating…','bluerabbit'); ?></div>
            <h3 class="br-panel-title"><span class="icon icon-signal"></span> <?= __('Engagement Overview','bluerabbit'); ?></h3>
            <div class="br-stats-engagement-overview" id="org-eng-overview"></div>
            <p class="br-form-hint" id="org-eng-coverage"></p>
        </div>

        <!-- Engagement Breakdown — the five components behind the score -->
        <div class="br-panel br-stats-loadable" id="org-eng-breakdown-panel">
            <div class="br-stats-panel-loader"><?= __('Calculating…','bluerabbit'); ?></div>
            <h3 class="br-panel-title">
                <span class="icon icon-power"></span> <?= __('Engagement Breakdown','bluerabbit'); ?>
                <span class="br-panel-title-note">(<?= __('avg across all players','bluerabbit'); ?>)</span>
            </h3>
            <div class="br-stats-kpis br-stats-kpis-5" id="org-eng-breakdown"></div>
        </div>

        <!-- Engagement by Adventure -->
        <div class="br-panel br-stats-loadable" id="org-eng-adv-panel">
            <div class="br-stats-panel-loader"><?= __('Calculating…','bluerabbit'); ?></div>
            <h3 class="br-panel-title"><span class="icon icon-adventure"></span> <?= __('Engagement by Adventure','bluerabbit'); ?></h3>
            <div class="br-stats-chart-wrap" id="org-eng-adv-wrap">
                <canvas id="org-eng-adv-chart"></canvas>
            </div>
            <p class="br-form-hint"><?= __('Players are scored per adventure, so someone enrolled in several journeys appears in each bar.','bluerabbit'); ?></p>
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


<?php include (get_stylesheet_directory() . '/footer.php'); ?>

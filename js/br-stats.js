(function($) {
    'use strict';

    if (!window.brStats) return;

    var cfg = window.brStats;
    var palette = {
        primary:  '#1cc2eb',
        accent:   '#f7cb15',
        purple:   '#9f40e2',
        green:    '#24da98',
        bg:       '#04161e',
        white:    '#ffffff',
        gridLine: 'rgba(255,255,255,0.06)'
    };

    var charts = {};

    // ── Helpers ───────────────────────────────────────────

    function ajax(action, extra, callback) {
        var data = $.extend({ action: action, nonce: cfg.nonce, adventure_id: cfg.adventureId }, extra || {});
        // Returned so callers can attach .fail() — a panel that shows a loader has to
        // be able to clear it when the request errors, not just when it succeeds.
        return $.post(cfg.ajaxurl, data, callback, 'json');
    }

    function destroyChart(key) {
        if (charts[key]) { charts[key].destroy(); delete charts[key]; }
    }

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    function numFmt(n) {
        return (parseInt(n) || 0).toLocaleString();
    }

    // ── Client-side table: search + page size + pagination + sort ────────
    //
    // One controller for every table on this page, so the player, achievement
    // and item tables all behave like the enrolled-players table in
    // page-new-adventure: the whole set is in the DOM, and search / page size /
    // paging are applied to it in the browser. Rows opt in by carrying a
    // data-search attribute; sortable headers carry data-sort-col +
    // data-sort-type and read data-<col> off each row.
    function BRStatsTable(opts) {
        this.$body     = $('#' + opts.body);
        this.$search   = $('#' + opts.search);
        this.$perPage  = $('#' + opts.perPage);
        this.$visible  = $('#' + opts.visible);
        this.$empty    = $('#' + opts.empty);
        this.$pager    = $('#' + opts.pager);
        this.$table    = $('#' + opts.table);
        this.scrollTo  = opts.scrollTo || null;
        this.page      = 1;
        this.perPage   = parseInt(this.$perPage.val(), 10) || 30;
        this.sortCol   = '';
        this.sortDir   = 'desc';
        this.bind();
    }

    BRStatsTable.prototype.rows = function() {
        return this.$body.children('tr[data-search]').get();
    };

    BRStatsTable.prototype.bind = function() {
        var self = this;
        this.$search.on('keyup search', function() { self.page = 1; self.render(); });
        this.$perPage.on('change', function() {
            self.perPage = parseInt($(this).val(), 10) || 30;
            self.page = 1;
            self.render();
        });
        this.$table.on('click', '.br-sortable', function() {
            var col  = $(this).attr('data-sort-col');
            var type = $(this).attr('data-sort-type');
            if (self.sortCol === col) { self.sortDir = self.sortDir === 'asc' ? 'desc' : 'asc'; }
            else { self.sortCol = col; self.sortDir = type === 'string' ? 'asc' : 'desc'; }

            self.$table.find('.br-sort-icon').text('');
            $(this).find('.br-sort-icon').text(self.sortDir === 'asc' ? ' ▲' : ' ▼');

            var rows = self.rows();
            rows.sort(function(a, b) {
                var va = a.getAttribute('data-' + col) || '';
                var vb = b.getAttribute('data-' + col) || '';
                if (type === 'number') { va = parseFloat(va) || 0; vb = parseFloat(vb) || 0; }
                var cmp = va > vb ? 1 : (va < vb ? -1 : 0);
                return self.sortDir === 'asc' ? cmp : -cmp;
            });
            rows.forEach(function(r) { self.$body.append(r); });
            self.page = 1;
            self.render();
        });
        // Paging buttons are rebuilt on every render, so the handler is delegated.
        this.$pager.on('click', '.br-page-btn', function() {
            var p = parseInt($(this).attr('data-page'), 10);
            if (!p) return;
            self.page = p;
            self.render();
            if (self.scrollTo) {
                var el = document.getElementById(self.scrollTo);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    };

    BRStatsTable.prototype.render = function() {
        var terms = ($this_val(this.$search) || '').toLowerCase().trim();
        terms = terms ? terms.split(/\s+/) : [];
        var visible = [];
        this.rows().forEach(function(row) {
            row.style.display = 'none';
            var hay = row.getAttribute('data-search') || '';
            for (var i = 0; i < terms.length; i++) {
                if (hay.indexOf(terms[i]) < 0) return;
            }
            visible.push(row);
        });

        var total = visible.length;
        var pages = Math.max(1, Math.ceil(total / this.perPage));
        if (this.page > pages) this.page = pages;
        var start = (this.page - 1) * this.perPage;
        var end   = Math.min(start + this.perPage, total);
        for (var i = start; i < end; i++) {
            visible[i].style.display = '';
            var num = visible[i].querySelector('.br-row-num');
            if (num) num.textContent = i + 1;
        }

        if (this.$visible.length) this.$visible.text(total);
        if (this.$empty.length)   this.$empty.toggleClass('br-initially-hidden', total > 0);
        this.renderPager(pages);
    };

    BRStatsTable.prototype.renderPager = function(pages) {
        if (!this.$pager.length) return;
        if (pages <= 1) { this.$pager.html(''); return; }
        var h = '', p = this.page;
        function btn(n, label, cls) {
            return '<button class="br-page-btn' + (cls || '') + '" data-page="' + n + '">' + label + '</button>';
        }
        if (p > 1) h += btn(p - 1, '&laquo;');
        var s = Math.max(1, p - 3), e = Math.min(pages, p + 3);
        if (s > 1) {
            h += btn(1, '1');
            if (s > 2) h += '<span class="br-pagination-ellipsis">&hellip;</span>';
        }
        for (var i = s; i <= e; i++) h += btn(i, i, i === p ? ' active' : '');
        if (e < pages) {
            if (e < pages - 1) h += '<span class="br-pagination-ellipsis">&hellip;</span>';
            h += btn(pages, pages);
        }
        if (p < pages) h += btn(p + 1, '&raquo;');
        this.$pager.html(h);
    };

    function $this_val($el) { return $el.length ? $el.val() : ''; }

    // ── Panel loader ─────────────────────────────────────────────────────
    // Recalculating a segment over a 1000+ player roster takes real time, so the
    // panel says so instead of silently showing stale numbers.
    function panelLoading(panelId, on) {
        $('#' + panelId).toggleClass('br-stats-loading', !!on);
    }

    // ── Chart.js v2 shared options ───────────────────────

    var baseOpts = {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        scales: {
            xAxes: [{ gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine }, ticks: { fontColor: palette.white, fontSize: 10 } }],
            yAxes: [{ gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine }, ticks: { fontColor: palette.white, fontSize: 11, beginAtZero: true } }]
        }
    };

    function lineOpts()       { return $.extend(true, {}, baseOpts); }
    function barOpts()        { return $.extend(true, {}, baseOpts); }
    function hBarOpts() {
        var o = $.extend(true, {}, baseOpts);
        o.legend = { labels: { fontColor: palette.white, fontSize: 11 }, display: true };
        return o;
    }

    // ── XP History (line) ────────────────────────────────

    function initXpHistory(userId) {
        ajax('br_stats_xp_history', { user_id: userId }, function(res) {
            if (!res.success) return;
            var d = res.data;
            destroyChart('xp-history');
            var ctx = document.getElementById('br-xp-history-chart');
            if (!ctx) return;

            charts['xp-history'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.map(function(r) { return r.date.substring(5); }),
                    datasets: [{
                        label: cfg.labels.xp + ' Gained',
                        data: d.map(function(r) { return r.xp_gained; }),
                        borderColor: palette.primary,
                        backgroundColor: 'rgba(28,194,235,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointBackgroundColor: palette.primary,
                        fill: true
                    }]
                },
                options: lineOpts()
            });
        });
    }

    // ── Quest Funnel (horizontal bar) ────────────────────

    var funnelData = [], funnelPage = 0, funnelPerPage = 10;

    function initQuestFunnel() {
        ajax('br_stats_quest_funnel', {}, function(res) {
            if (!res.success) return;
            funnelData = res.data;
            funnelPage = 0;
            renderFunnelPage();
        });
    }

    window.brFunnelPage = function(dir) {
        var pages = Math.ceil(funnelData.length / funnelPerPage);
        funnelPage = Math.max(0, Math.min(pages - 1, funnelPage + dir));
        renderFunnelPage();
    };

    function renderFunnelPage() {
        var pages = Math.max(1, Math.ceil(funnelData.length / funnelPerPage));
        var start = funnelPage * funnelPerPage;
        var slice = funnelData.slice(start, start + funnelPerPage);

        var lbl = document.getElementById('br-funnel-page-label');
        if (lbl) lbl.textContent = (funnelPage + 1) + '/' + pages;

        destroyChart('quest-funnel');
        var ctx = document.getElementById('br-quest-funnel-chart');
        if (!ctx || !slice.length) return;

        var labels = [], completed = [], bgCompleted = [], borderC = [];
        slice.forEach(function(r) {
            var t = r.quest_title.length > 30 ? r.quest_title.substring(0, 28) + '…' : r.quest_title;
            if (r.is_locked) t = '🔒 ' + t;
            labels.push(t);
            completed.push(parseInt(r.completed_count) || 0);
            bgCompleted.push(r.is_locked ? 'rgba(255,255,255,0.08)' : palette.green);
            borderC.push(r.is_locked ? 'rgba(255,255,255,0.1)' : palette.green);
        });

        var opts = hBarOpts();
        opts.legend = { display: false };

        charts['quest-funnel'] = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Completed', data: completed, backgroundColor: bgCompleted, borderColor: borderC, borderWidth: 1 }
                ]
            },
            options: opts
        });
    }

    // ── XP Distribution (bar histogram) ──────────────────

    function initXpDistribution() {
        ajax('br_stats_xp_distribution', {}, function(res) {
            if (!res.success) return;
            var d = res.data;
            destroyChart('xp-dist');
            var ctx = document.getElementById('br-xp-distribution-chart');
            if (!ctx) return;

            charts['xp-dist'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: d.map(function(r) { return r.label; }),
                    datasets: [{
                        label: 'Players',
                        data: d.map(function(r) { return r.count; }),
                        backgroundColor: palette.purple,
                        borderColor: palette.purple,
                        borderWidth: 1
                    }]
                },
                options: barOpts()
            });
        });
    }

    // ── Activity (line) ──────────────────────────────────

    function initActivityChart(from, to) {
        var params = {};
        if (from && to) { params.from = from; params.to = to; showLoader('small'); }
        ajax('br_stats_activity_heatmap', params, function(res) {
            hideAllOverlay();
            if (!res.success) return;
            var d = res.data;
            destroyChart('activity');
            var ctx = document.getElementById('br-activity-chart');
            if (!ctx) return;

            charts['activity'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.map(function(r) { return r.date.substring(5); }),
                    datasets: [{
                        label: 'Active Users',
                        data: d.map(function(r) { return r.count; }),
                        borderColor: palette.accent,
                        backgroundColor: 'rgba(247,203,21,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointBackgroundColor: palette.accent,
                        fill: true
                    }]
                },
                options: lineOpts()
            });
        });
    }

    window.brReloadActivity = function() {
        var from = document.getElementById('br-activity-from').value;
        var to   = document.getElementById('br-activity-to').value;
        if (from && to) initActivityChart(from, to);
    };
    window.brResetActivity = function() {
        document.getElementById('br-activity-from').value = '';
        document.getElementById('br-activity-to').value = '';
        initActivityChart();
    };

    // ── Workforce Engagement (segment breakdown, horizontal bar) ──

    var segColors = [palette.primary, palette.accent, palette.purple, palette.green, '#ff9800', '#f44336', '#607d8b', '#9575cd', '#4db6ac'];

    function renderSegmentBreakdown(data) {
        if (!data) return;

        var coverage = document.getElementById('br-segment-coverage');
        if (coverage) coverage.textContent = data.label + ' data available for ' + data.coverage_pct + '% of players';
        var dimLabel = document.getElementById('br-segment-dim-label');
        if (dimLabel) dimLabel.textContent = data.label;

        destroyChart('segment');
        var ctx = document.getElementById('br-segment-chart');
        var segments = data.segments || [];
        if (ctx && segments.length) {
            charts['segment'] = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: segments.map(function(s) { return s.label; }),
                    datasets: [{
                        label: 'Avg Engagement Score',
                        data: segments.map(function(s) { return s.avg_score; }),
                        backgroundColor: segments.map(function(s, i) { return segColors[i % segColors.length]; }),
                        borderWidth: 0
                    }]
                },
                options: hBarOpts()
            });
        }

        var $body = $('#br-segment-table-body');
        if ($body.length) {
            var rows = '';
            segments.forEach(function(s) {
                rows += '<tr><td>' + esc(s.label) + '</td>'
                     + '<td class="text-center">' + numFmt(s.count) + '</td>'
                     + '<td class="text-center">' + s.avg_score + '</td>'
                     + '<td class="text-center">' + s.avg_completion_pct + '%</td></tr>';
            });
            $body.html(rows);
        }
    }

    function loadSegmentBreakdown(dimension) {
        panelLoading('br-segment-panel', true);
        ajax('br_stats_segment_breakdown', { dimension: dimension }, function(res) {
            panelLoading('br-segment-panel', false);
            if (!res.success) return;
            renderSegmentBreakdown(res.data);
        }).fail(function() {
            panelLoading('br-segment-panel', false);
        });
    }

    // ── Type Completion Doughnut ─────────────────────────

    var typeColors = {
        quest:     palette.primary,
        challenge: palette.accent,
        survey:    palette.purple,
        mission:   palette.green
    };

    function initTypeCompletion(data) {
        destroyChart('type-completion');
        var ctx = document.getElementById('br-type-completion-chart');
        if (!ctx || !data || !data.length) return;

        var labels = [], completed = [], remaining = [], bgDone = [], bgLeft = [];
        data.forEach(function(t) {
            var label = t.quest_type.charAt(0).toUpperCase() + t.quest_type.slice(1) + 's';
            labels.push(label);
            completed.push(parseInt(t.completed) || 0);
            remaining.push(Math.max(0, (parseInt(t.total) || 0) - (parseInt(t.completed) || 0)));
            bgDone.push(typeColors[t.quest_type] || palette.primary);
            bgLeft.push('rgba(255,255,255,0.06)');
        });

        charts['type-completion'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: completed,
                    backgroundColor: bgDone,
                    borderWidth: 0
                }, {
                    data: remaining,
                    backgroundColor: bgLeft,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 65,
                legend: {
                    position: 'bottom',
                    labels: { fontColor: palette.white, fontSize: 11, padding: 12, boxWidth: 12 }
                },
                tooltips: {
                    callbacks: {
                        label: function(item, chart) {
                            var ds = item.datasetIndex === 0 ? 'Done' : 'Remaining';
                            return chart.labels[item.index] + ' ' + ds + ': ' + chart.datasets[item.datasetIndex].data[item.index];
                        }
                    }
                }
            }
        });
    }

    // ── Engagement Gauge (SVG builder for AJAX panels) ───

    var engColors = { on_fire: '#f7cb15', active: '#24da98', moderate: '#1cc2eb', cooling_off: '#ff9800', dormant: '#f44336' };
    var engLabels = { on_fire: 'ON FIRE', active: 'ACTIVE', moderate: 'MODERATE', cooling_off: 'COOLING OFF', dormant: 'DORMANT' };
    var engBarColors = ['#1cc2eb', '#24da98', '#9f40e2', '#f7cb15', '#ff9800'];
    var engBarNames  = ['Recency', 'Frequency', 'Completion', 'Progression', 'Economy'];

    function buildEngagementGaugeSVG(eng) {
        var circ = 326.73;
        var off  = (circ * (1 - eng.score / 100)).toFixed(2);
        var col  = engColors[eng.level] || '#1cc2eb';
        return '<svg viewBox="0 0 120 120">'
            + '<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>'
            + '<circle cx="60" cy="60" r="52" fill="none" stroke="' + col + '" stroke-width="8"'
            + ' stroke-dasharray="' + circ + '" stroke-dashoffset="' + off + '"'
            + ' transform="rotate(-90 60 60)" stroke-linecap="round"/>'
            + '<text x="60" y="52" text-anchor="middle" fill="#fff" font-size="28" font-weight="900"'
            + ' font-family="proxima-nova-extra-condensed,sans-serif">' + eng.score + '</text>'
            + '<text x="60" y="70" text-anchor="middle" fill="' + col + '" font-size="9" font-weight="700">'
            + (engLabels[eng.level] || '') + '</text></svg>';
    }

    function buildEngagementBreakdownHTML(eng) {
        var keys   = ['recency','frequency','completion','progression','economy'];
        var details = [
            Math.round(eng.breakdown.recency.days_inactive) + 'd inactive',
            eng.breakdown.frequency.recent_completions + ' in 30d',
            eng.breakdown.completion.pct + '% done',
            'Lv. ' + eng.breakdown.progression.level,
            eng.breakdown.economy.transactions + ' txns'
        ];
        var h = '<div class="br-stats-section-title" style="margin-top:8px"><h2>Engagement Breakdown</h2></div>';
        h += '<div class="br-stats-kpis br-stats-kpis-5">';
        keys.forEach(function(k, i) {
            var c = eng.breakdown[k];
            h += '<div class="br-stats-kpi br-stats-kpi-eng" style="border-color:' + engBarColors[i] + '33">';
            h += '<span class="br-stats-kpi-value" style="color:' + engBarColors[i] + '">' + c.score + '<small>/' + c.max + '</small></span>';
            h += '<span class="br-stats-kpi-label">' + engBarNames[i] + '</span>';
            h += '<span class="br-stats-kpi-detail">' + details[i] + '</span>';
            h += '</div>';
        });
        h += '</div>';
        return h;
    }

    // ── Achievement Stats + drill-down drawer ────────────

    var achievementTable = null;

    function initAchievementStats() {
        panelLoading('br-achievement-panel', true);
        ajax('br_stats_achievement_breakdown', {}, function(res) {
            panelLoading('br-achievement-panel', false);
            if (!res.success) return;
            renderAchievementStats(res.data);
        }).fail(function() { panelLoading('br-achievement-panel', false); });
    }

    function renderAchievementStats(rows) {
        var $body = $('#br-achievement-stats-body');
        $('#br-achievement-total').text(rows ? rows.length : 0);
        if (!rows || !rows.length) {
            $body.html('<tr><td colspan="3" class="text-center br-muted">No achievements yet</td></tr>');
            $('#br-achievement-visible').text(0);
            return;
        }
        var html = rows.map(function(r) {
            return '<tr class="br-stats-clickable-row" onclick="openAchievementDetail(' + r.achievement_id + ')"'
                + ' data-search="' + esc((r.achievement_name || '').toLowerCase()) + '"'
                + ' data-name="' + esc((r.achievement_name || '').toLowerCase()) + '"'
                + ' data-earned="' + (parseInt(r.earned_count) || 0) + '"'
                + ' data-pct="' + (parseFloat(r.pct) || 0) + '">'
                + '<td>' + esc(r.achievement_name) + '</td>'
                + '<td class="text-center">' + numFmt(r.earned_count) + ' / ' + numFmt(r.total_players) + '</td>'
                + '<td class="text-center">' + r.pct + '%</td>'
                + '</tr>';
        }).join('');
        $body.html(html);

        if (!achievementTable) {
            achievementTable = new BRStatsTable({
                table: 'br-achievement-stats-table', body: 'br-achievement-stats-body',
                search: 'br-achievement-search', perPage: 'br-achievement-per-page',
                visible: 'br-achievement-visible', empty: 'br-achievement-empty',
                pager: 'br-achievement-pagination'
            });
        }
        achievementTable.render();
    }

    function playerRowHTML(p, metaOverride) {
        var meta = metaOverride || p.user_email || '';
        return '<div class="br-player-card">'
            + '<div class="br-player-avatar" style="background-image:url(' + esc(p.avatar_url) + ')"></div>'
            + '<div class="br-player-info"><span class="br-player-name">' + esc(p.display_name) + '</span>'
            + '<span class="br-player-meta">' + esc(meta) + '</span></div></div>';
    }

    function buildAchievementDetailHTML(d) {
        var have = d.have || [], haveNot = d.have_not || [];
        var h = '<div class="tabi-conditions-header"><h3 class="br-text-16 w700">Achievement Detail</h3>'
            + '<button class="br-close-btn" onclick="closeAchievementDetail();"><span class="icon icon-cancel white-color"></span></button></div>';
        h += '<div class="tabi-conditions-body">';
        h += '<div class="tabi-conditions-section"><span class="br-text-12 block grey-500">Have it (' + have.length + ')</span>';
        h += '<div class="br-player-grid">' + (have.length ? have.map(function(p) { return playerRowHTML(p, (p.achievement_applied || '').substring(0, 10)); }).join('') : '<span class="br-text-12 grey-400">None yet</span>') + '</div></div>';
        h += '<div class="tabi-conditions-section"><span class="br-text-12 block grey-500">Don\'t have it (' + haveNot.length + ')</span>';
        h += '<div class="br-player-grid">' + (haveNot.length ? haveNot.map(function(p) { return playerRowHTML(p); }).join('') : '<span class="br-text-12 grey-400">Everyone has it!</span>') + '</div></div>';
        h += '</div>';
        return h;
    }

    // brOpenDrawer/brCloseDrawer (script.js) move the drawer to <body> and back.
    // .main-content is position:relative with its own z-index, so a drawer left
    // inside the page is trapped in that stacking context and the shared
    // backdrop paints over it no matter what z-index the drawer carries.
    window.openAchievementDetail = function(achievementId) {
        $('#achievement-detail-overlay').html('<div class="tabi-conditions-header"><h3 class="br-text-16 w700">Loading...</h3></div>');
        brOpenDrawer($('#achievement-detail-overlay'));
        ajax('br_stats_achievement_detail', { achievement_id: achievementId }, function(res) {
            if (!res.success) return;
            $('#achievement-detail-overlay').html(buildAchievementDetailHTML(res.data));
        });
    };
    window.closeAchievementDetail = function() {
        brCloseDrawer($('#achievement-detail-overlay'));
    };

    // ── Item Purchase Stats + drill-down drawer ──────────

    var itemTable = null;

    function initItemStats() {
        panelLoading('br-item-panel', true);
        ajax('br_stats_item_breakdown', {}, function(res) {
            panelLoading('br-item-panel', false);
            if (!res.success) return;
            renderItemStats(res.data);
        }).fail(function() { panelLoading('br-item-panel', false); });
    }

    function renderItemStats(rows) {
        var $body = $('#br-item-stats-body');
        $('#br-item-total').text(rows ? rows.length : 0);
        if (!rows || !rows.length) {
            $body.html('<tr><td colspan="3" class="text-center br-muted">No items yet</td></tr>');
            $('#br-item-visible').text(0);
            return;
        }
        var html = rows.map(function(r) {
            return '<tr class="br-stats-clickable-row" onclick="openItemDetail(' + r.item_id + ')"'
                + ' data-search="' + esc((r.item_name || '').toLowerCase()) + '"'
                + ' data-name="' + esc((r.item_name || '').toLowerCase()) + '"'
                + ' data-purchases="' + (parseInt(r.purchase_count) || 0) + '"'
                + ' data-bloo="' + (parseInt(r.total_bloo) || 0) + '">'
                + '<td>' + esc(r.item_name) + '</td>'
                + '<td class="text-center">' + numFmt(r.purchase_count) + '</td>'
                + '<td class="text-center">' + numFmt(r.total_bloo) + '</td>'
                + '</tr>';
        }).join('');
        $body.html(html);

        if (!itemTable) {
            itemTable = new BRStatsTable({
                table: 'br-item-stats-table', body: 'br-item-stats-body',
                search: 'br-item-search', perPage: 'br-item-per-page',
                visible: 'br-item-visible', empty: 'br-item-empty',
                pager: 'br-item-pagination'
            });
        }
        itemTable.render();
    }

    function buildItemDetailHTML(rows) {
        var h = '<div class="tabi-conditions-header"><h3 class="br-text-16 w700">Purchases</h3>'
            + '<button class="br-close-btn" onclick="closeItemDetail();"><span class="icon icon-cancel white-color"></span></button></div>';
        h += '<div class="tabi-conditions-body">';
        if (!rows || !rows.length) {
            h += '<span class="br-text-12 grey-400">No purchases yet</span>';
        } else {
            h += '<table class="br-table"><thead><tr><th>Player</th><th>Date</th><th class="text-center">Amount</th></tr></thead><tbody>';
            rows.forEach(function(r) {
                h += '<tr><td>' + esc(r.display_name) + '</td><td>' + esc(r.trnx_date) + '</td><td class="text-center">' + numFmt(r.trnx_amount) + '</td></tr>';
            });
            h += '</tbody></table>';
        }
        h += '</div>';
        return h;
    }

    window.openItemDetail = function(itemId) {
        $('#item-detail-overlay').html('<div class="tabi-conditions-header"><h3 class="br-text-16 w700">Loading...</h3></div>');
        brOpenDrawer($('#item-detail-overlay'));
        ajax('br_stats_item_detail', { item_id: itemId }, function(res) {
            if (!res.success) return;
            $('#item-detail-overlay').html(buildItemDetailHTML(res.data));
        });
    };
    window.closeItemDetail = function() {
        brCloseDrawer($('#item-detail-overlay'));
    };

    // ── Time in App ───────────────────────────────────────

    function fmtDuration(seconds) {
        seconds = parseInt(seconds) || 0;
        if (seconds < 60) return seconds + 's';
        var m = Math.floor(seconds / 60);
        if (m < 60) return m + 'm ' + (seconds % 60) + 's';
        var h = Math.floor(m / 60);
        return h + 'h ' + (m % 60) + 'm';
    }

    function initTimeInApp() {
        ajax('br_stats_time_in_app', {}, function(res) {
            if (!res.success) return;
            renderTimeInApp(res.data);
        });
    }

    function renderTimeInApp(d) {
        var real = d.real || {};
        var est  = d.estimate || {};
        var $kpis = $('#br-time-in-app-kpis');

        if (!real.has_data) {
            $kpis.html('<div class="br-stats-kpi"><span class="br-stats-kpi-value">&mdash;</span>'
                + '<span class="br-stats-kpi-label">Collecting data - check back soon</span></div>');
        } else {
            var h = '';
            h += '<div class="br-stats-kpi"><span class="br-stats-kpi-value">' + fmtDuration(real.avg_session_seconds) + '</span>'
                + '<span class="br-stats-kpi-label">Avg. Session Length</span></div>';
            h += '<div class="br-stats-kpi accent"><span class="br-stats-kpi-value">' + fmtDuration(real.avg_player_total_seconds) + '</span>'
                + '<span class="br-stats-kpi-label">Avg. Total Time / Player</span></div>';
            $kpis.html(h);
        }

        var $note = $('#br-time-in-app-estimate-note');
        $note.text(est.has_data
            ? 'Estimated over the last 90 days (approximate, based on the activity log - undercounts idle time): ~' + fmtDuration(est.avg_session_seconds) + ' avg session'
            : '');
    }

    // ── Player Panel (AJAX swap) ─────────────────────────

    function loadPlayerPanel(userId) {
        var $panel = $('#br-stats-player-panel');
        $panel.addClass('br-stats-loading');

        ajax('br_stats_player_panel', { user_id: userId }, function(res) {
            $panel.removeClass('br-stats-loading');
            if (!res.success) return;

            var d = res.data;
            var params = new URLSearchParams(window.location.search);
            params.set('uid', userId);
            history.pushState({ uid: userId }, '', '?' + params.toString());

            $panel.attr('data-uid', userId);
            $panel.html(buildPlayerHTML(d));

            cfg.userId = userId;
            initXpHistory(userId);
            if (d.type_completion) initTypeCompletion(d.type_completion);

            $('html, body').animate({ scrollTop: $panel.offset().top - 80 }, 300);
            $('.br-stats-player-row').removeClass('active');
            $('.br-stats-player-row[data-uid="' + userId + '"]').addClass('active');
        }).fail(function() {
            $panel.removeClass('br-stats-loading');
        });
    }

    function buildPlayerHTML(d) {
        var s = d.summary;
        var lbl = d.labels;
        if (!s || !s.display_name) return '<div class="br-stats-panel text-center"><p class="br-text-18">No data available.</p></div>';

        var eng = d.engagement || { score: 0, level: 'dormant', breakdown: {} };
        var la  = d.last_activity || {};
        var h = '';

        // Hero
        h += '<div class="br-stats-hero">';
        h += '<div class="br-stats-hero-avatar"><img src="' + esc(s.avatar_url) + '" alt=""></div>';
        h += '<div class="br-stats-hero-info">';
        h += '<h2 class="br-stats-hero-name">' + esc(s.display_name) + '</h2>';
        h += '<span class="br-stats-hero-adventure">' + esc(d.adventure_title) + '</span>';
        h += '<div class="br-stats-hero-meta">';
        if (s.rank_name) h += '<span class="br-stats-badge ' + esc(s.rank_color) + '">' + esc(s.rank_name) + '</span>';
        h += '<span class="br-stats-hero-level">Lv. ' + (s.player_level || 1) + '</span>';
        h += '<span class="br-stats-hero-rank">#' + s.rank_position + ' of ' + s.total_players + ' players</span>';
        h += '</div>';
        h += '<div class="br-stats-last-activity">';
        if (la.days_since_login !== null)    h += '<span>Last login: <strong>' + Math.round(la.days_since_login) + 'd ago</strong></span>';
        if (la.days_since_quest !== null)    h += '<span>Last quest: <strong>' + Math.round(la.days_since_quest) + 'd ago</strong></span>';
        if (la.days_since_activity !== null) h += '<span>Last activity: <strong>' + Math.round(la.days_since_activity) + 'd ago</strong></span>';
        h += '</div>';
        h += '</div>';
        h += '<div class="br-stats-engagement-gauge">' + buildEngagementGaugeSVG(eng) + '<span class="br-stats-engagement-label">Engagement</span></div>';
        h += '</div>';

        // Currencies
        h += '<div class="br-stats-currencies">';
        h += kpiBox('xp',   numFmt(s.player_xp),   lbl.xp);
        h += kpiBox('bloo', numFmt(s.player_bloo), lbl.bloo);
        h += kpiBox('ep',   numFmt(s.player_ep),   lbl.ep);
        h += '</div>';

        // XP chart + type doughnut
        h += '<div class="br-stats-charts-row">';
        h += '<div class="br-stats-panel br-stats-two-thirds"><h3>' + esc(lbl.xp) + ' Over Time</h3><div class="br-stats-chart-wrap"><canvas id="br-xp-history-chart"></canvas></div></div>';
        h += '<div class="br-stats-panel br-stats-one-third"><h3>Completion by Type</h3><div class="br-stats-chart-wrap br-stats-doughnut-wrap"><canvas id="br-type-completion-chart"></canvas></div></div>';
        h += '</div>';

        // Quest progress
        h += '<div class="br-stats-panel"><h3>Quest Progress</h3><div class="br-stats-quest-list">';
        (d.quests || []).forEach(function(q) {
            var sc = 'locked', bp = 0, st = 'Locked';
            if (q.status === 'publish') { sc = 'complete'; bp = 100; st = 'Complete'; }
            else if (q.status) { sc = 'in-progress'; bp = 50; st = 'In Progress'; }

            h += '<div class="br-stats-quest-row">';
            h += '<div class="br-stats-quest-info"><span class="icon icon-' + esc(q.quest_icon || 'quest') + '"></span>';
            h += '<span class="br-stats-quest-title">' + esc(q.quest_title) + '</span></div>';
            h += '<div class="br-stats-quest-bar-wrap"><div class="br-stats-quest-bar ' + sc + '" style="width:' + bp + '%"></div></div>';
            h += '<span class="br-stats-quest-status ' + sc + '">' + st + '</span></div>';
        });
        h += '</div></div>';

        // Tabi progress
        if (d.tabis && d.tabis.length) {
            h += '<div class="br-stats-panel"><h3>Tabi Progress</h3><div class="br-stats-quest-list">';
            d.tabis.forEach(function(tb) {
                var tot = parseInt(tb.total_quests) || 0;
                var done = parseInt(tb.completed_quests) || 0;
                var pct = tot > 0 ? Math.round((done / tot) * 100) : 0;
                var tc = pct >= 100 ? 'complete' : (pct > 0 ? 'in-progress' : 'locked');
                h += '<div class="br-stats-quest-row">';
                h += '<div class="br-stats-quest-info"><span class="icon icon-tabi"></span><span class="br-stats-quest-title">' + esc(tb.tabi_name) + '</span></div>';
                h += '<div class="br-stats-quest-bar-wrap"><div class="br-stats-quest-bar ' + tc + '" style="width:' + pct + '%"></div></div>';
                h += '<span class="br-stats-quest-status ' + tc + '">' + done + '/' + tot + '</span></div>';
            });
            h += '</div></div>';
        }

        // Achievements
        if (d.achievements && d.achievements.length) {
            h += '<div class="br-stats-panel"><h3>Achievements</h3><div class="br-stats-achievements-grid">';
            d.achievements.forEach(function(a) {
                var earned = a.earned_at ? 'earned' : 'locked';
                h += '<div class="br-stats-achievement ' + earned + '">';
                if (a.achievement_badge) {
                    h += '<img src="' + esc(a.achievement_badge) + '" alt="' + esc(a.achievement_name) + '">';
                } else {
                    h += '<div class="br-stats-achievement-placeholder ' + esc(a.achievement_color || '') + '"><span class="icon icon-achievement"></span></div>';
                }
                h += '<span class="br-stats-achievement-name">' + esc(a.achievement_name) + '</span>';
                if (a.earned_at) h += '<span class="br-stats-achievement-date">' + a.earned_at.substring(5, 10) + '</span>';
                h += '</div>';
            });
            h += '</div></div>';
        }

        // Guild
        if (d.guild && d.guild.guild_name) {
            var g = d.guild;
            h += '<div class="br-stats-panel br-stats-guild-card"><h3>Guild</h3><div class="br-stats-guild-content">';
            if (g.guild_logo) h += '<img src="' + esc(g.guild_logo) + '" class="br-stats-guild-logo" alt="">';
            h += '<div class="br-stats-guild-info"><h4>' + esc(g.guild_name) + '</h4>';
            h += '<div class="br-stats-guild-stats">';
            h += '<span><strong>Rank:</strong> #' + g.rank + ' / ' + g.total_guilds + '</span>';
            h += '<span><strong>' + esc(lbl.xp) + ':</strong> ' + numFmt(g.total_xp) + '</span>';
            h += '<span><strong>Members:</strong> ' + g.member_count + '</span>';
            h += '</div></div></div></div>';
        }

        // SCORM
        if (d.scorm && d.scorm.length) {
            h += '<div class="br-stats-panel"><h3>SCORM Completions</h3><div class="br-table-scroll">';
            h += '<table class="br-table"><thead><tr><th>Step</th><th class="text-center">Status</th></tr></thead><tbody>';
            d.scorm.forEach(function(sc) {
                var cls = (sc.status === 'completed' || sc.status === 'passed') ? 'complete' : 'incomplete';
                h += '<tr><td>' + esc(sc.step_title) + '</td>';
                h += '<td class="text-center"><span class="br-stats-scorm-status ' + cls + '">' + esc(sc.status) + '</span></td></tr>';
            });
            h += '</tbody></table></div></div>';
        }

        // Engagement breakdown
        if (eng.breakdown) {
            h += buildEngagementBreakdownHTML(eng);
        }

        return h;
    }

    function kpiBox(cls, val, label) {
        return '<div class="br-stats-currency ' + cls + '">'
             + '<span class="br-stats-currency-value">' + val + '</span>'
             + '<span class="br-stats-currency-label">' + esc(label) + '</span></div>';
    }

    // ── Init ─────────────────────────────────────────────

    $(document).ready(function() {
        initXpHistory(cfg.userId);
        if (cfg.typeCompletion) initTypeCompletion(cfg.typeCompletion);

        if (cfg.isManager) {
            initQuestFunnel();
            initXpDistribution();
            initActivityChart();
            initAchievementStats();
            initItemStats();
            initTimeInApp();

            if (cfg.segmentBreakdown) renderSegmentBreakdown(cfg.segmentBreakdown);
            $('#br-segment-dimension').on('change', function() {
                loadSegmentBreakdown($(this).val());
            });

            $(document).on('click', '.br-stats-player-row', function(e) {
                e.preventDefault();
                var uid = $(this).data('uid');
                if (uid) loadPlayerPanel(uid);
            });

            // The whole roster is in the DOM (page-stats.php renders it in one go),
            // so search, page size, paging and sorting are all local - no round trip
            // per keystroke, and sorting covers every player rather than one page.
            if ($('#br-stats-player-body').length) {
                new BRStatsTable({
                    table: 'br-stats-player-table', body: 'br-stats-player-body',
                    search: 'br-stats-player-search', perPage: 'br-stats-player-per-page',
                    visible: 'br-stats-player-visible', empty: 'br-stats-player-empty',
                    pager: 'br-stats-player-pagination', scrollTo: 'br-stats-player-table'
                }).render();
            }
        }

        // Handle back/forward
        $(window).on('popstate', function(e) {
            var state = e.originalEvent.state;
            if (state && state.uid) {
                loadPlayerPanel(state.uid);
            }
        });
    });

})(jQuery);

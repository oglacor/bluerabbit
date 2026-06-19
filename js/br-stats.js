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
        $.post(cfg.ajaxurl, data, callback, 'json');
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

    function initQuestFunnel() {
        ajax('br_stats_quest_funnel', {}, function(res) {
            if (!res.success) return;
            var d = res.data;
            destroyChart('quest-funnel');
            var ctx = document.getElementById('br-quest-funnel-chart');
            if (!ctx) return;

            charts['quest-funnel'] = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: d.map(function(r) { return r.quest_title.length > 30 ? r.quest_title.substring(0, 28) + '…' : r.quest_title; }),
                    datasets: [
                        { label: 'Enrolled',  data: d.map(function(r) { return r.started_count; }),   backgroundColor: 'rgba(28,194,235,0.25)', borderColor: palette.primary, borderWidth: 1 },
                        { label: 'Completed', data: d.map(function(r) { return r.completed_count; }), backgroundColor: palette.green,             borderColor: palette.green,   borderWidth: 1 }
                    ]
                },
                options: hBarOpts()
            });
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

    function initActivityChart() {
        ajax('br_stats_activity_heatmap', {}, function(res) {
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

    // ── Player Panel (AJAX swap) ─────────────────────────

    function loadPlayerPanel(userId) {
        var $panel = $('#br-stats-player-panel');
        $panel.css('opacity', '0.5');

        ajax('br_stats_player_panel', { user_id: userId }, function(res) {
            $panel.css('opacity', '1');
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
        });
    }

    function buildPlayerHTML(d) {
        var s = d.summary;
        var lbl = d.labels;
        if (!s || !s.display_name) return '<div class="br-stats-panel text-center"><p class="white-color font _18">No data available.</p></div>';

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
            h += '<div class="br-stats-panel"><h3>SCORM Completions</h3>';
            h += '<table class="table transparent-bg"><thead><tr><td>Step</td><td class="text-center">Status</td></tr></thead><tbody>';
            d.scorm.forEach(function(sc) {
                var cls = (sc.status === 'completed' || sc.status === 'passed') ? 'complete' : 'incomplete';
                h += '<tr><td>' + esc(sc.step_title) + '</td>';
                h += '<td class="text-center"><span class="br-stats-scorm-status ' + cls + '">' + esc(sc.status) + '</span></td></tr>';
            });
            h += '</tbody></table></div>';
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

            $(document).on('click', '.br-stats-player-row', function(e) {
                e.preventDefault();
                var uid = $(this).data('uid');
                if (uid) loadPlayerPanel(uid);
            });
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

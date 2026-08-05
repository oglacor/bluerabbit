/* br-org-stats.js — Chart.js v2 charts for the Organisation Stats tab */
(function ($) {
    'use strict';

    var cfg = window.brOrgStats;
    if (!cfg) return;

    // ── Shared palette (mirrors br-stats.js) ─────────────────────────────────
    var palette = {
        primary:  '#1cc2eb',
        green:    '#4caf50',
        orange:   '#ff9800',
        purple:   '#9c27b0',
        red:      '#f44336',
        white:    'rgba(255,255,255,0.85)',
        gridLine: 'rgba(255,255,255,0.07)'
    };

    var COLORS = [
        '#1cc2eb','#4caf50','#ff9800','#9c27b0','#f44336',
        '#00bcd4','#8bc34a','#ffc107','#673ab7','#e91e63',
        '#03a9f4','#cddc39','#ff5722','#3f51b5','#009688'
    ];

    var charts = {};

    function destroy(key) {
        if (charts[key]) { charts[key].destroy(); delete charts[key]; }
    }

    var baseOpts = {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        scales: {
            xAxes: [{ gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine }, ticks: { fontColor: palette.white, fontSize: 10 } }],
            yAxes: [{ gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine }, ticks: { fontColor: palette.white, fontSize: 11, beginAtZero: true } }]
        }
    };

    // ── 1. Progress by Adventure (horizontal bar) ─────────────────────────────

    function initProgressChart() {
        var rows = cfg.progressByAdventure;
        if (!rows || !rows.length) return;
        var ctx = document.getElementById('org-progress-chart');
        if (!ctx) return;
        destroy('progress');
        var wrap = document.getElementById('org-progress-wrap');
        if (wrap) wrap.style.height = Math.max(160, rows.length * 50 + 60) + 'px';

        var labels   = rows.map(function(r){ return r.adventure_title; });
        var enrolled = rows.map(function(r){ return parseInt(r.enrolled_count, 10); });
        var active   = rows.map(function(r){ return parseInt(r.active_7d, 10); });
        var bgColors = rows.map(function(_, i){ return COLORS[i % COLORS.length]; });

        var opts = $.extend(true, {}, baseOpts);
        opts.legend.display = true;
        opts.legend.labels  = { fontColor: palette.white, fontSize: 11 };
        opts.scales.xAxes[0].ticks.beginAtZero = true;

        charts['progress'] = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Enrolled',
                        data: enrolled,
                        backgroundColor: bgColors.map(function(c){ return c + 'cc'; }),
                        borderColor:     bgColors,
                        borderWidth: 1
                    },
                    {
                        label: 'Active 7d',
                        data: active,
                        backgroundColor: 'rgba(76,175,80,0.6)',
                        borderColor:     palette.green,
                        borderWidth: 1
                    }
                ]
            },
            options: $.extend(true, {}, opts, {
                tooltips: {
                    callbacks: {
                        afterBody: function(items) {
                            var idx = items[0].index;
                            var r   = rows[idx];
                            return 'Avg Level: ' + (r.avg_level || '—');
                        }
                    }
                }
            })
        });
    }

    // ── 2. Daily Active Users (line) ──────────────────────────────────────────

    function initActivityChart(rows) {
        var ctx = document.getElementById('org-activity-chart');
        if (!ctx) return;
        destroy('activity');
        var labels = rows.map(function(r){ return r.date.substring(5); });
        var data   = rows.map(function(r){ return parseInt(r.count, 10); });

        charts['activity'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Active Users',
                    data: data,
                    borderColor: palette.primary,
                    backgroundColor: 'rgba(28,194,235,0.08)',
                    borderWidth: 2,
                    pointRadius: rows.length > 60 ? 0 : 2,
                    pointBackgroundColor: palette.primary,
                    fill: true
                }]
            },
            options: $.extend(true, {}, baseOpts)
        });
    }

    function orgLoadActivity() {
        var from = $('#org-activity-from').val();
        var to   = $('#org-activity-to').val();
        $.post(cfg.ajaxurl, { action: 'brOrgStatsActivity', org_id: cfg.orgId, from: from, to: to }, function(res) {
            if (res.success && res.data && res.data.rows) {
                initActivityChart(res.data.rows);
            }
        });
    }
    window.orgLoadActivity = orgLoadActivity;

    // ── 3. Segment Breakdown (horizontal bar, switchable) ────────────────────

    function renderSegmentChart(data) {
        var ctx = document.getElementById('org-segment-chart');
        if (!ctx) return;
        destroy('segment');

        var segs    = data.segments || [];
        var labels  = segs.map(function(s){ return s.label; });
        var counts  = segs.map(function(s){ return parseInt(s.count, 10); });
        var total   = counts.reduce(function(a,b){ return a+b; }, 0);
        var bgColors = segs.map(function(_, i){ return COLORS[i % COLORS.length] + 'cc'; });
        var bdColors = segs.map(function(_, i){ return COLORS[i % COLORS.length]; });

        var opts = $.extend(true, {}, baseOpts);
        opts.scales.xAxes[0].ticks.beginAtZero = true;

        charts['segment'] = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: data.label,
                    data: counts,
                    backgroundColor: bgColors,
                    borderColor: bdColors,
                    borderWidth: 1
                }]
            },
            options: $.extend(true, {}, opts, {
                tooltips: {
                    callbacks: {
                        label: function(item) {
                            var pct = total > 0 ? Math.round(item.xLabel / total * 100) : 0;
                            return item.xLabel + ' players (' + pct + '%)';
                        }
                    }
                }
            })
        });

        $('#org-segment-coverage').text(data.label + ': ' + segs.length + ' groups, ' + total + ' players with data');
    }

    function orgLoadSegment(dimension, btnEl) {
        $('.br-stats-dim-btn').removeClass('active');
        if (btnEl) $(btnEl).addClass('active');

        // If this is the initial dimension pre-loaded server-side, skip AJAX
        if (cfg.segment && cfg.segment.dimension === dimension) {
            renderSegmentChart(cfg.segment);
            return;
        }
        $.post(cfg.ajaxurl, { action: 'brOrgStatsSegment', org_id: cfg.orgId, dimension: dimension }, function(res) {
            if (res.success) renderSegmentChart(res.data);
        });
    }
    window.orgLoadSegment = orgLoadSegment;

    // ── Init (called once when Stats tab is first opened) ────────────────────

    function brOrgChartsInit() {
        initProgressChart();
        orgLoadActivity();
        orgLoadSegment(cfg.segment ? cfg.segment.dimension : 'work_country', null);
    }
    window.brOrgChartsInit = brOrgChartsInit;

}(jQuery));

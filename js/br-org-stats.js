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

    // ── 4. Engagement (gauge + distribution + breakdown + per-adventure) ─────
    //
    // Loaded on demand — the org-wide engagement scan is the heaviest query set
    // on this tab, so it never blocks the page render.

    var engColors = {
        on_fire: '#f7cb15', active: '#24da98', moderate: '#1cc2eb',
        cooling_off: '#ff9800', dormant: '#f44336', never_logged_in: '#607d8b'
    };
    var engOrder = ['on_fire', 'active', 'moderate', 'cooling_off', 'dormant', 'never_logged_in'];
    var engCompColors = {
        recency: '#1cc2eb', frequency: '#24da98', completion: '#9f40e2',
        progression: '#f7cb15', economy: '#ff9800'
    };

    // Copy comes from PHP so it stays translatable; the fallbacks keep the panel
    // rendering if a cached page predates these keys.
    var engStrings = $.extend({
        avg_score: 'AVG SCORE',
        coverage:  '%1$s players scored of %2$s enrolled — %3$s have never logged in.',
        no_data:   'No enrolled players yet.',
        error:     'Could not load engagement data.'
    }, cfg.engagementStrings || {});

    function engLabel(key) {
        return (cfg.engagementLabels && cfg.engagementLabels[key]) || key;
    }

    function esc(s) {
        return String(s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function buildGaugeSVG(score, level) {
        var circ = 326.73;
        var off  = (circ * (1 - score / 100)).toFixed(2);
        var col  = engColors[level] || palette.primary;
        return '<svg viewBox="0 0 120 120">'
            + '<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>'
            + '<circle cx="60" cy="60" r="52" fill="none" stroke="' + col + '" stroke-width="8"'
            + ' stroke-dasharray="' + circ + '" stroke-dashoffset="' + off + '"'
            + ' transform="rotate(-90 60 60)" stroke-linecap="round"/>'
            + '<text x="60" y="52" text-anchor="middle" fill="#ffffff" font-size="28" font-weight="900"'
            + ' font-family="proxima-nova-extra-condensed,sans-serif">' + score + '</text>'
            + '<text x="60" y="70" text-anchor="middle" fill="' + col + '" font-size="9" font-weight="700">'
            + esc(engStrings.avg_score) + '</text></svg>';
    }

    function renderEngagementOverview(overall) {
        var $wrap = $('#org-eng-overview');
        if (!$wrap.length) return;

        // Percentages are of every enrolled player, so the never-logged-in slice
        // is visible instead of being hidden behind the scored-only average.
        var total = Math.max(1, overall.count);
        var h = '<div class="br-stats-engagement-avg">' + buildGaugeSVG(overall.avg_score, overall.level) + '</div>';
        h += '<div class="br-stats-engagement-dist">';
        engOrder.forEach(function (key) {
            var count = overall.distribution[key] || 0;
            var pct   = Math.round((count / total) * 100);
            h += '<div class="br-stats-eng-row">'
               + '<span class="br-stats-eng-label" style="color:' + engColors[key] + '">' + esc(engLabel(key)) + '</span>'
               + '<div class="br-stats-eng-bar-wrap"><div class="br-stats-eng-bar" style="width:' + pct + '%;background:' + engColors[key] + '"></div></div>'
               + '<span class="br-stats-eng-score">' + count + '</span>'
               + '</div>';
        });
        h += '</div>';
        $wrap.html(h);

        var never = overall.distribution.never_logged_in || 0;
        $('#org-eng-coverage').text(
            engStrings.coverage
                .replace('%1$s', overall.scored)
                .replace('%2$s', overall.count)
                .replace('%3$s', never)
        );
    }

    function renderEngagementBreakdown(overall) {
        var $wrap = $('#org-eng-breakdown');
        if (!$wrap.length) return;
        var comps = cfg.engagementComponents || {};
        var ab    = overall.avg_breakdown || {};
        var h = '';

        Object.keys(comps).forEach(function (key) {
            var meta = comps[key];
            var c    = ab[key] || { score: 0, max: 0 };
            var col  = engCompColors[key] || palette.primary;
            var detail = meta.key ? (c[meta.key] || 0) + meta.suffix : '';
            h += '<div class="br-stats-kpi br-stats-kpi-eng" style="border-color:' + col + '33">'
               + '<span class="br-stats-kpi-value" style="color:' + col + '">' + c.score + '<small>/' + c.max + '</small></span>'
               + '<span class="br-stats-kpi-label">' + esc(meta.label)
               + ' <span class="br-stats-info-btn br-stats-info-icon" title="' + esc(meta.info) + '">&#9432;</span></span>'
               + (detail ? '<span class="br-stats-kpi-detail">' + esc(detail) + '</span>' : '')
               + '</div>';
        });
        $wrap.html(h);
    }

    function renderEngagementByAdventure(rows) {
        var ctx = document.getElementById('org-eng-adv-chart');
        if (!ctx) return;
        destroy('engAdv');
        rows = rows || [];
        if (!rows.length) return;

        var wrap = document.getElementById('org-eng-adv-wrap');
        if (wrap) wrap.style.height = Math.max(180, rows.length * 46 + 70) + 'px';

        var labels = rows.map(function (r) { return r.adventure_title; });
        var datasets = engOrder.map(function (key) {
            return {
                label: engLabel(key),
                data: rows.map(function (r) { return parseInt(r.distribution[key], 10) || 0; }),
                backgroundColor: engColors[key] + 'cc',
                borderColor: engColors[key],
                borderWidth: 1
            };
        });

        var opts = $.extend(true, {}, baseOpts);
        opts.legend.display = true;
        opts.legend.position = 'bottom';
        opts.legend.labels = { fontColor: palette.white, fontSize: 11, boxWidth: 12 };
        opts.scales.xAxes[0].stacked = true;
        opts.scales.yAxes[0].stacked = true;

        charts['engAdv'] = new Chart(ctx, {
            type: 'horizontalBar',
            data: { labels: labels, datasets: datasets },
            options: $.extend(true, {}, opts, {
                tooltips: {
                    mode: 'index',
                    callbacks: {
                        // The bar is a headcount split, so the adventure's own average
                        // score - the number a manager actually acts on - is appended.
                        afterTitle: function (items) {
                            var r = rows[items[0].index];
                            return 'Avg score: ' + r.avg_score + '/100';
                        }
                    }
                }
            })
        });
    }

    function orgLoadEngagement() {
        var $panels = $('#org-eng-panel, #org-eng-breakdown-panel, #org-eng-adv-panel').addClass('br-stats-loading');
        $.post(cfg.ajaxurl, { action: 'brOrgEngagement', org_id: cfg.orgId })
            .done(function (res) {
                if (!res || !res.success || !res.data) {
                    $('#org-eng-coverage').text(engStrings.error);
                    return;
                }
                var overall = res.data.overall;
                if (!overall || !overall.count) {
                    $('#org-eng-overview').empty();
                    $('#org-eng-coverage').text(engStrings.no_data);
                    return;
                }
                renderEngagementOverview(overall);
                renderEngagementBreakdown(overall);
                renderEngagementByAdventure(res.data.by_adventure);
            })
            .fail(function () {
                $('#org-eng-coverage').text(engStrings.error);
            })
            .always(function () {
                $panels.removeClass('br-stats-loading');
            });
    }
    window.orgLoadEngagement = orgLoadEngagement;

    // ── 5. Per-segment KPI table ─────────────────────────────────────────────
    //
    // The same figures as the org header, one row per segment, with the org total
    // repeated at the bottom so a pillar can be read against the whole population
    // without arithmetic.

    function segCell(value, suffix) {
        return '<td class="br-text-center">' + value + (suffix || '') + '</td>';
    }

    function segRow(s, isTotal) {
        // A completion bar in the cell: VPs scan this column first, and a number
        // alone makes 12% and 21% look the same at a glance.
        var bar = '<div class="br-seg-bar"><span style="width:' + Math.min(100, s.completion_pct) + '%"></span></div>';
        return '<tr' + (isTotal ? ' class="br-seg-total"' : '') + '>'
            + '<td>' + esc(s.label) + '</td>'
            + segCell(Number(s.players).toLocaleString())
            + segCell(Number(s.avg_xp).toLocaleString())
            + '<td class="br-text-center br-seg-completion">' + s.completion_pct + '%' + bar + '</td>'
            + segCell(Number(s.logged_in).toLocaleString() + ' <span class="br-seg-pct">(' + s.logged_in_pct + '%)</span>')
            + segCell(Number(s.active_7d).toLocaleString())
            + segCell(Number(s.active_30d).toLocaleString())
            + '</tr>';
    }

    function orgLoadSegmentSummary(dimension) {
        var $panel = $('#org-seg-summary-panel').addClass('br-stats-loading');
        $.post(cfg.ajaxurl, { action: 'brOrgSegmentSummary', org_id: cfg.orgId, dimension: dimension })
            .done(function (res) {
                if (!res || !res.success || !res.data) return;
                var d = res.data;
                $('#org-seg-summary-label').text(d.label);
                var html = (d.segments || []).map(function (s) { return segRow(s, false); }).join('');
                if (d.total) html += segRow(d.total, true);
                $('#org-seg-summary-body').html(html
                    || '<tr><td colspan="7" class="br-text-center">' + esc(engStrings.no_data) + '</td></tr>');
            })
            .always(function () { $panel.removeClass('br-stats-loading'); });
    }
    window.orgLoadSegmentSummary = orgLoadSegmentSummary;

    $(function () {
        $('.br-seg-summary-btn').on('click', function () {
            $('.br-seg-summary-btn').removeClass('active');
            $(this).addClass('active');
            orgLoadSegmentSummary($(this).data('dimension'));
        });
    });

    // ── Init (called once when Stats tab is first opened) ────────────────────

    function brOrgChartsInit() {
        initProgressChart();
        orgLoadActivity();
        orgLoadSegmentSummary('business_pillar');
        orgLoadEngagement();
        orgLoadSegment(cfg.segment ? cfg.segment.dimension : 'work_country', null);
    }
    window.brOrgChartsInit = brOrgChartsInit;

}(jQuery));

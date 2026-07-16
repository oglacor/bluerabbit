(function($) {
    'use strict';

    if (!window.brMilestoneFunnel) return;

    var cfg = window.brMilestoneFunnel;
    var palette = {
        primary: '#1cc2eb',
        green:   '#24da98',
        orange:  '#ff9800',
        white:   '#ffffff',
        gridLine: 'rgba(255,255,255,0.08)'
    };

    var chart = null;
    var BAR_WIDTH = 70; // px reserved per milestone before the chart starts horizontally scrolling

    function ajax(action, extra, callback) {
        var data = $.extend({ action: action, nonce: cfg.nonce, adventure_id: cfg.adventureId }, extra || {});
        $.post(cfg.ajaxurl, data, callback, 'json');
    }

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    // Builds the sticky y-axis overlay from the live chart's own scale so it
    // always lines up with the scrolling chart's gridlines, regardless of
    // however Chart.js decided to lay out ticks/padding for this dataset.
    function renderYAxisOverlay() {
        var $labels = $('#br-mf-yaxis-labels');
        $labels.empty();
        if (!chart) return;

        var scale = chart.scales['y'];
        if (!scale) return;

        var count = scale.ticks.length;
        for (var i = 0; i < count; i++) {
            var label = scale.ticks[i];
            var top = scale.getPixelForTick(i) - chart.chartArea.top;
            var $lbl = $('<div class="br-mf-yaxis-label"></div>').text(label).css('top', top + 'px');
            $labels.append($lbl);
        }

        // Marker for the "logged in" reference line, positioned from the same
        // live scale so it always lines up with the dashed line drawn on the canvas.
        if (cfg.loggedInCount > 0 && cfg.loggedInCount <= scale.max) {
            var lineTop = scale.getPixelForValue(cfg.loggedInCount) - chart.chartArea.top;
            var $marker = $('<div class="br-mf-yaxis-label logged-in"></div>')
                .text(cfg.loggedInPct + '%')
                .attr('title', cfg.loggedInCount + ' ' + (cfg.loggedInLabel || 'players logged in'))
                .css('top', lineTop + 'px');
            $labels.append($marker);
        }
    }

    // Draws the "logged in" reference line across the full (scrollable) width
    // of the chart, at the pixel row for cfg.loggedInCount on the y scale.
    var loggedInLinePlugin = {
        afterDraw: function(c) {
            if (!(cfg.loggedInCount > 0)) return;
            var scale = c.scales['y'];
            if (!scale || cfg.loggedInCount > scale.max) return;

            var y = scale.getPixelForValue(cfg.loggedInCount);
            var ctx2 = c.ctx;
            ctx2.save();
            ctx2.beginPath();
            ctx2.setLineDash([6, 4]);
            ctx2.lineWidth = 2;
            ctx2.strokeStyle = palette.orange;
            ctx2.moveTo(c.chartArea.left, y);
            ctx2.lineTo(c.chartArea.right, y);
            ctx2.stroke();
            ctx2.restore();
        }
    };

    function renderChart(data) {
        var $area = $('#br-mf-chart-area');
        var $wrap = $('#br-mf-scroll-wrap');
        var wrapWidth = $wrap.width() - 56; // minus sticky y-axis column

        if (!data.length) {
            $area.css('width', '100%').html('<div class="br-mf-empty">' + esc(cfg.emptyLabel || 'No milestones match this filter.') + '</div>');
            if (chart) { chart.destroy(); chart = null; }
            $('#br-mf-yaxis-labels').empty();
            return;
        }

        var neededWidth = data.length * BAR_WIDTH;
        var canvasWidth = Math.max(wrapWidth, neededWidth);
        var canvasHeight = $wrap.height();

        $area.css('width', canvasWidth + 'px').html(
            '<canvas id="br-mf-chart" width="' + canvasWidth + '" height="' + canvasHeight + '"></canvas>'
        );
        var ctx = document.getElementById('br-mf-chart');

        var labels = [], fullTitles = [], completed = [], bgCompleted = [];
        data.forEach(function(r) {
            var full = (r.is_locked ? '🔒 ' : '') + r.quest_title;
            var t = r.quest_title.length > 18 ? r.quest_title.substring(0, 16) + '…' : r.quest_title;
            if (r.is_locked) t = '🔒 ' + t;
            labels.push(t);
            fullTitles.push(full);
            completed.push(parseInt(r.completed_count) || 0);
            bgCompleted.push(r.is_locked ? 'rgba(255,255,255,0.08)' : palette.green);
        });

        if (chart) chart.destroy();

        // Y axis is scaled to total enrolled players, not the tallest bar, so a
        // milestone's completion count reads in context of the whole roster.
        var yMax = cfg.totalEnrolled > 0 ? cfg.totalEnrolled : undefined;

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { yAxisID: 'y', label: 'Completed', data: completed, backgroundColor: bgCompleted, borderWidth: 0 }
                ]
            },
            plugins: [loggedInLinePlugin],
            options: {
                responsive: false,
                maintainAspectRatio: false,
                animation: { duration: 0 },
                legend: { display: false },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: function(items) { return fullTitles[items[0].index] || ''; },
                        label: function(item) { return 'Completed: ' + item.yLabel; }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine },
                        ticks: { fontColor: palette.white, fontSize: 10, maxRotation: 60, minRotation: 60 }
                    }],
                    yAxes: [{
                        id: 'y',
                        gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine, drawTicks: false },
                        ticks: { display: false, beginAtZero: true, max: yMax }
                    }]
                }
            }
        });

        // Wait a frame so Chart.js has finished computing scale pixel positions.
        requestAnimationFrame(renderYAxisOverlay);
    }

    function loadFunnel() {
        var parts = ($('#br-mf-filter').val() || 'all|0').split('|');
        ajax('br_stats_quest_funnel', { filter_type: parts[0], filter_value: parts[1] }, function(res) {
            if (!res.success) return;
            renderChart(res.data);
        });
    }

    $(document).ready(function() {
        loadFunnel();
        $('#br-mf-filter').on('change', loadFunnel);
        $(window).on('resize', function() {
            if (chart) requestAnimationFrame(renderYAxisOverlay);
        });
    });

})(jQuery);

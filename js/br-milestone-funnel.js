(function($) {
    'use strict';

    if (!window.brMilestoneFunnel) return;

    var cfg = window.brMilestoneFunnel;
    var palette = {
        primary: '#1cc2eb',
        green:   '#24da98',
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
    }

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

        var labels = [], completed = [], bgCompleted = [];
        data.forEach(function(r) {
            var t = r.quest_title.length > 18 ? r.quest_title.substring(0, 16) + '…' : r.quest_title;
            if (r.is_locked) t = '🔒 ' + t;
            labels.push(t);
            completed.push(parseInt(r.completed_count) || 0);
            bgCompleted.push(r.is_locked ? 'rgba(255,255,255,0.08)' : palette.green);
        });

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { yAxisID: 'y', label: 'Completed', data: completed, backgroundColor: bgCompleted, borderWidth: 0 }
                ]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                animation: { duration: 0 },
                legend: { display: false },
                scales: {
                    xAxes: [{
                        gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine },
                        ticks: { fontColor: palette.white, fontSize: 10, maxRotation: 60, minRotation: 60 }
                    }],
                    yAxes: [{
                        id: 'y',
                        gridLines: { color: palette.gridLine, zeroLineColor: palette.gridLine, drawTicks: false },
                        ticks: { display: false, beginAtZero: true }
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

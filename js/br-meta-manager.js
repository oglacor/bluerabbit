(function ($) {
	'use strict';

	if (!window.brMeta) return;
	var cfg = window.brMeta;
	var lastCsvText = null;

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

	// ── Player row expand/edit ───────────────────────────

	window.brMetaToggleRow = function (playerId) {
		var $row = $('#br-meta-detail-' + playerId);
		$row.css('display', $row.css('display') === 'none' ? '' : 'none');
	};

	window.brMetaSavePlayer = function (playerId, btnEl) {
		var $detail = $(btnEl).closest('.br-detail-row');
		var fields = {};
		$detail.find('.br-meta-field').each(function () {
			fields[$(this).data('field')] = $(this).val();
		});

		$(btnEl).prop('disabled', true);
		ajax('br_meta_save_player', { player_id: playerId, fields: fields }, function (res) {
			$(btnEl).prop('disabled', false);
			if (!res.success) { alert('Save failed'); return; }

			var $row = $detail.prev('.br-meta-player-row');
			$row.find('td').eq(2).text(fields.work_country || '—');
			$row.find('td').eq(3).text(fields.work_function || '—');
			$row.find('td').eq(4).text(fields.business_pillar || '—');
			$row.find('td').eq(5).text(fields.work_level || '—');
		});
	};

	// ── Player search (client-side, current page only) ──

	$(document).on('keyup', '#br-meta-player-search', function () {
		var q = ($(this).val() || '').toLowerCase();
		$('.br-meta-player-row').each(function () {
			var s = $(this).attr('data-search') || '';
			var show = !q || s.indexOf(q) >= 0;
			$(this).css('display', show ? '' : 'none');
			if (!show) $(this).next('.br-detail-row').css('display', 'none');
		});
	});

	// ── CSV import ────────────────────────────────────────

	function readFileText(callback) {
		var input = document.getElementById('br-meta-csv-file');
		if (!input.files || !input.files[0]) { alert('Choose a CSV file first.'); return; }
		var reader = new FileReader();
		reader.onload = function (e) { callback(e.target.result); };
		reader.readAsText(input.files[0]);
	}

	function renderCsvReport(report, committed) {
		if (report.error) {
			$('#br-meta-csv-preview').show();
			$('#br-meta-csv-columns').html('<span style="color:#f44336">' + esc(report.error) + '</span>');
			$('#br-meta-csv-summary').html('');
			$('#br-meta-csv-table-body').html('');
			return;
		}

		var cols = [];
		$.each(report.mapped_columns, function (i, label) { cols.push(esc(label)); });
		$('#br-meta-csv-columns').text(cols.join(', ') || 'None recognized');

		var summary = report.matched_count + ' matched, ' + report.unmatched_count + ' unmatched (skipped)';
		if (committed) summary += ' — ' + report.updated_count + ' updated.';
		$('#br-meta-csv-summary').text(summary);

		var rows = '';
		report.rows.forEach(function (r) {
			var fieldsText = [];
			$.each(r.fields, function (col, val) { if (val) fieldsText.push(col + ': ' + val); });
			rows += '<tr' + (r.matched ? '' : ' style="opacity:0.4"') + '>';
			rows += '<td>' + r.line + '</td>';
			rows += '<td>' + esc(r.email) + '</td>';
			rows += '<td>' + (r.matched ? esc(r.display_name) : '<span style="color:#f44336">Not found</span>') + '</td>';
			rows += '<td style="font-size:11px">' + esc(fieldsText.join(', ')) + '</td>';
			rows += '</tr>';
		});
		$('#br-meta-csv-table-body').html(rows);
		$('#br-meta-csv-preview').show();
	}

	window.brMetaPreviewCsv = function () {
		readFileText(function (text) {
			lastCsvText = text;
			ajax('br_meta_preview_csv', { csv: text }, function (res) {
				if (!res.success) { alert('Preview failed'); return; }
				renderCsvReport(res.data, false);
			});
		});
	};

	window.brMetaCommitCsv = function () {
		if (!lastCsvText) { alert('Preview the file first.'); return; }
		if (!confirm('This will update player_meta for all matched rows. Continue?')) return;

		ajax('br_meta_commit_csv', { csv: lastCsvText }, function (res) {
			if (!res.success) { alert('Import failed'); return; }
			renderCsvReport(res.data, true);
			setTimeout(function () { window.location.reload(); }, 1200);
		});
	};

})(jQuery);

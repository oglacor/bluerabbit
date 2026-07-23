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

	// esc() alone is only safe inside text content - it doesn't encode quotes,
	// so building a value="..." attribute needs those escaped too.
	function escAttr(str) {
		return esc(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	// ── Player row expand/edit ───────────────────────────

	window.brMetaToggleRow = function (playerId) {
		$('#br-meta-detail-' + playerId).toggleClass('br-initially-hidden');
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
			$row.find('td').eq(2).text(fields.player_gender || '—');
			$row.find('td').eq(3).text(fields.work_country || '—');
			$row.find('td').eq(4).text(fields.work_function || '—');
			$row.find('td').eq(5).text(fields.business_pillar || '—');
			$row.find('td').eq(6).text(fields.work_level || '—');
		});
	};

	// ── Player search (full roster, not just the current page) ──
	// The table is paginated server-side, so a purely client-side filter would
	// silently find nothing for a player parked on any page but the current one.

	var $playerTbody = $('#br-meta-player-table tbody');
	var originalTbodyHTML = null;
	var searchTimer = null;

	function renderMetaRow(p) {
		var row = '<tr class="br-meta-player-row" data-uid="' + p.player_id + '" onClick="brMetaToggleRow(' + p.player_id + ');" style="cursor:pointer">'
			+ '<td class="text-center"><span class="icon icon-edit" style="opacity:0.4"></span></td>'
			+ '<td><span class="br-stats-player-name">'
			+   '<img src="' + p.avatar_url + '" class="br-stats-avatar-sm" alt="">'
			+   esc(p.display_name) + '</span></td>'
			+ '<td>' + (esc(p.player_gender) || '&mdash;') + '</td>'
			+ '<td>' + (esc(p.work_country) || '&mdash;') + '</td>'
			+ '<td>' + (esc(p.work_function) || '&mdash;') + '</td>'
			+ '<td>' + (esc(p.business_pillar) || '&mdash;') + '</td>'
			+ '<td>' + (esc(p.work_level) || '&mdash;') + '</td>'
			+ '</tr>';

		var fields = '';
		$.each(cfg.fields, function (col, label) {
			fields += '<div class="br-form-group" style="margin-bottom:10px">'
				+ '<label class="br-form-label">' + esc(label) + '</label>'
				+ '<input class="br-input br-meta-field" type="text" data-field="' + escAttr(col) + '" value="' + escAttr(p[col]) + '">'
				+ '</div>';
		});
		row += '<tr class="br-detail-row br-initially-hidden" id="br-meta-detail-' + p.player_id + '">'
			+ '<td colspan="7">'
			+ '<div class="br-form-grid">' + fields + '</div>'
			+ '<div style="text-align:right">'
			+ '<button class="br-btn br-btn-green" onClick="brMetaSavePlayer(' + p.player_id + ', this);"><span class="icon icon-check"></span> Save</button>'
			+ '</div>'
			+ '</td>'
			+ '</tr>';
		return row;
	}

	function runSearch(q) {
		ajax('br_meta_search_players', { search: q }, function (res) {
			if (!res.success) return;
			var players = res.data.players;
			if (!players || !players.length) {
				$playerTbody.html('<tr><td colspan="7" class="text-center br-muted">No players found</td></tr>');
			} else {
				$playerTbody.html(players.map(renderMetaRow).join(''));
			}
			$('.br-stats-pagination').hide();
		});
	}

	$(document).on('keyup', '#br-meta-player-search', function () {
		if (originalTbodyHTML === null) originalTbodyHTML = $playerTbody.html();
		var q = ($(this).val() || '').trim();
		clearTimeout(searchTimer);
		searchTimer = setTimeout(function () {
			if (!q) {
				$playerTbody.html(originalTbodyHTML);
				$('.br-stats-pagination').show();
				return;
			}
			runSearch(q);
		}, 300);
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

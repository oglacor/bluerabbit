/* br-org.js — behaviour for the organization pages.
 *
 * Covers page-organization.php (General / Players / Adventures / Stats tabs)
 * and page-manage-orgs.php (org list + create). Chart drawing lives in its own
 * file, br-org-stats.js, and is kicked off from orgStatsInit() below.
 *
 * All user-facing copy arrives from PHP in brOrgL10n so this file stays
 * translation-free; the fallbacks only cover a stale cached page.
 */
(function ($) {
    'use strict';

    var L = $.extend({
        searching:      'Searching…',
        result:         'result',
        results:        'results',
        noResults:      'No results found.',
        noAdventures:   'No adventures found.',
        settingsSaved:  'Organization updated',
        saveFailed:     'Could not update the organization',
        selectCsv:      'Please select a CSV file.',
        noEmails:       'No valid emails found.',
        selectAdv:      'Please select an adventure.',
        confirmBulk:    'Add everyone from "%s" to this org?',
        importTitle:    'Import Players to Org',
        emailsFound:    'email addresses found in CSV…',
        bulkTitle:      'Bulk Add from Adventure',
        processing:     'Processing',
        added:          'added',
        alreadyIn:      'already in org',
        notFound:       'not found',
        totalInAdv:     'total in adventure',
        requestFailed:  'Request failed',
        error:          'Error',
        remove:         'Remove?',
        roleFailed:     'Could not update the role.',
        orgNameNeeded:  'Please enter a name.',
        orgDeleted:     'Organization deleted',
        orgDeleteError: 'Error deleting organization',
        roles:          {}
    }, window.brOrgL10n || {});

    var siteUrl = (window.brOrgL10n && window.brOrgL10n.siteUrl) || '';

    function ajaxurl() {
        return (window.runAJAX && window.runAJAX.ajaxurl) || '/wp-admin/admin-ajax.php';
    }

    function orgId()   { return $('#the_org_id').val(); }
    function parseJs(r) { return (typeof r === 'string') ? JSON.parse(r) : r; }
    function esc(s)     { return $('<span>').text(s == null ? '' : s).html(); }

    function bumpCount(sel, delta) {
        var cur = parseInt($(sel).text(), 10) || 0;
        $(sel).text(Math.max(0, cur + delta));
    }

    // ── General settings ─────────────────────────────────────────────────────

    function saveOrgSettings() {
        var about;
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('the-org-content')) {
            about = tinyMCE.get('the-org-content').getContent();
        } else {
            about = $('#the-org-content').val();
        }
        showLoader();
        $.post(ajaxurl(), {
            action: 'updateOrg',
            nonce:  $('#nonce').val(),
            org_data: {
                id:     orgId(),
                name:   $('#the-org-name').val(),
                logo:   $('#the-org-logo').val(),
                color:  $('#the-org-color').val(),
                status: 'publish',
                about:  about
            }
        }, function (r) {
            var d = parseJs(r), ok = !!(d.success || d.updated);
            brNotify(ok ? L.settingsSaved : L.saveFailed, ok ? 'success' : 'error');
            // The title and About blurb in the header are rendered server-side,
            // so keep them honest with what was just saved instead of waiting
            // for a reload.
            if (ok) {
                $('.br-page-title').text($('#the-org-name').val());
                $('#org-about-blurb').html(about);
            }
        }).fail(function () {
            brNotify(L.saveFailed, 'error');
        }).always(hideLoader);
    }

    // Narrower than hideAllOverlay(), which also pauses audio, unfixes the page
    // and closes the start menu - none of which belongs in a save handler.
    function hideLoader() {
        $('.loader, .small-loader, .overlay-bg').removeClass('active');
    }

    // ── Player list ──────────────────────────────────────────────────────────

    function removePlayerFromOrg(player_id, org_id) {
        $.post(ajaxurl(), { action: 'removePlayerFromOrg', player_id: player_id, org_id: org_id },
            function (r) {
                if (!parseJs(r).success) return;
                $('#player-org-row-' + player_id).fadeOut(300, function () { $(this).remove(); });
                bumpCount('#org-player-count', -1);
            }
        );
    }

    // Manager toggle. The server hands back the rebuilt row, so the button's
    // colour, tooltip and next-click role can never drift out of sync here.
    function orgSetPlayerRole(player_id, role) {
        var $row = $('#player-org-row-' + player_id);
        $row.addClass('br-row-busy');
        $.post(ajaxurl(), {
            action:    'setPlayerOrgCapabilities',
            org_id:    orgId(),
            player_id: player_id,
            role:      role
        }, function (r) {
            var d = parseJs(r);
            $row.removeClass('br-row-busy');
            if (!d.success || !d.row_html) { brNotify(d.message || L.roleFailed, 'error'); return; }
            $row.replaceWith(d.row_html);
            brNotify(d.message, 'success');
        }).fail(function () {
            $row.removeClass('br-row-busy');
            brNotify(L.roleFailed, 'error');
        });
    }

    // ── Player search ────────────────────────────────────────────────────────

    function setApStatus(cls, text) {
        $('#org-ap-status').attr('class', 'br-ap-status ' + cls).text(text);
    }

    function orgApFind() {
        var s = $('#org-ap-search').val().trim();
        if (!s) return;
        setApStatus('br-ap-status-hint', L.searching);
        $('#org-ap-results').empty();
        $('#org-ap-clear').removeClass('br-initially-hidden');
        $.post(ajaxurl(), {
            action:        'findPlayersToOrg',
            nonce:         $('#search-player-nonce').val(),
            search_string: s
        }, function (html) {
            var count = $('#org-ap-results').html(html || '').find('.br-ap-row').length;
            if (count > 0) {
                setApStatus('br-ap-status-found', count + ' ' + (count === 1 ? L.result : L.results));
            } else {
                setApStatus('br-ap-status-empty', L.noResults);
            }
        });
    }

    function orgApClearSearch() {
        $('#org-ap-search').val('');
        $('#org-ap-results').empty();
        $('#org-ap-clear').addClass('br-initially-hidden');
        setApStatus('', '');
    }

    function orgApAddPlayer(player_id, btn) {
        var $row = $('#org-ap-row-' + player_id);
        $(btn).prop('disabled', true).html('<span class="br-ap-spin icon icon-refresh"></span>');
        $.post(ajaxurl(), { action: 'addPlayerToOrg', org_id: orgId(), player_id: player_id },
            function (html) {
                if (!html || !html.trim()) {
                    $row.addClass('br-ap-row-error');
                    $row.find('.br-ap-action').html('<span class="br-badge br-badge-red"><span class="icon icon-cancel"></span></span>');
                    return;
                }
                var name = $row.find('.br-ap-name').text().trim() || ('#' + player_id);
                $row.addClass('br-ap-row-done');
                $row.find('.br-ap-action').html('<span class="br-badge br-badge-green"><span class="icon icon-check"></span></span>');

                var $list = $('#org-ap-log-list');
                $('#org-ap-log').removeClass('br-initially-hidden');
                $list.prepend('<li><span class="icon icon-check"></span> ' + esc(name) + '</li>');
                $('#org-ap-log-count').text($list.find('li').length);

                $('#org-players-list').append(html);
                bumpCount('#org-player-count', 1);
            }
        );
    }

    // ── CSV import (batched, one log line per email) ──────────────────────────

    function orgImportPlayersCsv() {
        var fileInput = document.getElementById('org-csv-players');
        if (!fileInput || !fileInput.files[0]) { brNotify(L.selectCsv, 'warn'); return; }

        var reader = new FileReader();
        reader.onload = function (e) {
            var emails = [];
            e.target.result.split(/[\r\n]+/).forEach(function (line) {
                var cell = line.split(',')[0].replace(/['"]/g, '').trim().toLowerCase();
                if (cell && cell.indexOf('@') > -1 && cell !== 'email') emails.push(cell);
            });
            if (!emails.length) { brNotify(L.noEmails, 'warn'); return; }

            var CHUNK = 20, chunks = [], i;
            for (i = 0; i < emails.length; i += CHUNK) chunks.push(emails.slice(i, i + CHUNK));
            var total = emails.length, done = 0, addedTotal = 0, alreadyInTotal = 0, notFoundTotal = 0;

            brOpConsoleOpen(L.importTitle);
            brOpConsoleLog(emails.length + ' ' + L.emailsFound, 'info');
            brOpConsoleSetProgress(0, total);

            function processChunk(idx) {
                if (idx >= chunks.length) {
                    var summary = addedTotal + ' ' + L.added + ', ' + alreadyInTotal + ' ' + L.alreadyIn;
                    if (notFoundTotal) summary += ', ' + notFoundTotal + ' ' + L.notFound;
                    brOpConsoleDone(summary);
                    bumpCount('#org-player-count', addedTotal);
                    fileInput.value = '';
                    return;
                }
                $.post(ajaxurl(), { action: 'importPlayersToOrg', org_id: orgId(), emails: chunks[idx] },
                    function (r) {
                        var d = parseJs(r);
                        if (!d.success) { brOpConsoleLog(d.message || L.error, 'error'); brOpConsoleDone(); return; }
                        addedTotal     += d.added      || 0;
                        alreadyInTotal += d.already_in || 0;
                        notFoundTotal  += d.not_found  || 0;
                        done += chunks[idx].length;
                        (d.added_emails      || []).forEach(function (em) { brOpConsoleLog('+ ' + em, 'success'); });
                        (d.already_in_emails || []).forEach(function (em) { brOpConsoleLog('= ' + em, 'info'); });
                        (d.not_found_emails  || []).forEach(function (em) { brOpConsoleLog('? ' + em, 'warn'); });
                        brOpConsoleSetProgress(done, total);
                        processChunk(idx + 1);
                    }
                ).fail(function () { brOpConsoleLog(L.requestFailed, 'error'); brOpConsoleDone(); });
            }
            processChunk(0);
        };
        reader.readAsText(fileInput.files[0]);
    }

    // ── Bulk add from an adventure (players, GMs, NPCs and the owner) ─────────

    function orgBulkFromAdventure() {
        var adv_id = $('#org-bulk-adventure-select').val();
        if (!adv_id) { brNotify(L.selectAdv, 'warn'); return; }
        var adv_name = $('#org-bulk-adventure-select option:selected').text();
        if (!confirm(L.confirmBulk.replace('%s', adv_name))) return;

        brOpConsoleOpen(L.bulkTitle);
        brOpConsoleLog(L.processing + ' ' + adv_name + '…', 'info');
        $.post(ajaxurl(), { action: 'bulkPlayersFromAdventure', org_id: orgId(), adventure_id: adv_id },
            function (r) {
                var d = parseJs(r);
                if (!d.success) { brOpConsoleLog(d.message || L.error, 'error'); brOpConsoleDone(); return; }
                Object.keys(d.by_role || {}).forEach(function (role) {
                    brOpConsoleLog((L.roles[role] || role) + ': ' + d.by_role[role], 'info');
                });
                brOpConsoleDone(d.added + ' ' + L.added + ', ' + d.already_in + ' ' + L.alreadyIn
                    + ' — ' + d.total + ' ' + L.totalInAdv + '.');
                bumpCount('#org-player-count', d.added);
            }
        ).fail(function () { brOpConsoleLog(L.requestFailed, 'error'); brOpConsoleDone(); });
    }

    // ── Adventures tab ───────────────────────────────────────────────────────

    function orgSearchAdventures() {
        var s = $('#adv-search-string').val().trim();
        if (!s) return;
        $.post(ajaxurl(), { action: 'searchAdventuresForOrg', org_id: orgId(), search: s },
            function (r) {
                var d = parseJs(r);
                if (!d.success || !d.results.length) {
                    $('#org-adv-search-results').html('<div class="br-ap-status br-ap-status-empty">' + esc(L.noAdventures) + '</div>');
                    return;
                }
                var html = '';
                d.results.forEach(function (adv) {
                    html += '<div class="br-ap-row br-ap-row-click" data-adventure-id="' + adv.adventure_id + '">'
                        + '<span class="br-ap-avatar br-ap-avatar-icon"><span class="icon icon-adventure"></span></span>'
                        + '<span class="br-ap-identity"><span class="br-ap-name">' + esc(adv.adventure_title) + '</span>'
                        + '<span class="br-ap-meta">' + esc(adv.player_display_name || '') + '</span></span>'
                        + '</div>';
                });
                $('#org-adv-search-results').html(html);
            }
        );
    }

    function orgAddAdventure(adventure_id, el) {
        var $el = $(el).addClass('br-ap-row-busy');
        $.post(ajaxurl(), { action: 'addAdventureToOrg', org_id: orgId(), adventure_id: adventure_id },
            function (r) {
                var d = parseJs(r);
                if (!d.success) { $el.removeClass('br-ap-row-busy').addClass('br-ap-row-error'); return; }
                $el.remove();
                var enroll = d.adventure_code ? siteUrl + '/enroll/?enroll_code=' + d.adventure_code : '';
                var row = '<tr id="org-adv-row-' + d.adventure_id + '">'
                    + '<td>' + d.adventure_id + '</td>'
                    + '<td>' + esc(d.adventure_title) + '</td>'
                    + '<td>' + esc(d.owner_name || '—') + '</td>'
                    + '<td>' + (enroll ? '<a href="' + enroll + '" target="_blank">' + esc(enroll) + '</a>' : '—') + '</td>'
                    + '<td><button class="br-btn red br-btn-sm" data-remove-adventure="' + d.adventure_id + '" title="' + esc(L.remove) + '">'
                    + '<span class="icon icon-cancel"></span></button></td>'
                    + '</tr>';
                $('#org-adventures-list').append(row);
                $('#adv-search-string').val('');
                $('#org-adv-search-results').empty();
                bumpCount('#org-adv-count', 1);
            }
        );
    }

    function orgRemoveAdventure(adventure_id) {
        $.post(ajaxurl(), { action: 'removeAdventureFromOrg', org_id: orgId(), adventure_id: adventure_id },
            function (r) {
                if (!parseJs(r).success) return;
                $('#org-adv-row-' + adventure_id).fadeOut(300, function () { $(this).remove(); });
                bumpCount('#org-adv-count', -1);
            }
        );
    }

    // ── Stats tab (charts live in br-org-stats.js) ───────────────────────────

    var orgStatsInited = false;
    function orgStatsInit() {
        if (orgStatsInited) return;
        orgStatsInited = true;
        if (typeof $.fn.datetimepicker !== 'undefined') {
            $('#org-activity-from, #org-activity-to').datetimepicker({ format: 'Y-m-d', timepicker: false });
        }
        if (typeof brOrgChartsInit === 'function') brOrgChartsInit();
    }

    // ── Manage Orgs page ─────────────────────────────────────────────────────

    function toggleNewOrgPanel() {
        var $panel = $('#new-org-panel').toggleClass('br-initially-hidden');
        if (!$panel.hasClass('br-initially-hidden')) $('#new-org-name').trigger('focus');
    }

    function orgCreate() {
        var name = $('#new-org-name').val().trim();
        if (!name) { brNotify(L.orgNameNeeded, 'error'); return; }
        showLoader();
        $.post(ajaxurl(), {
            action: 'createOrg',
            nonce:  $('#manage-orgs-nonce').val(),
            name:   name,
            color:  $('#new-org-color').val(),
            status: $('#new-org-status').val()
        }, function (r) {
            var d = parseJs(r);
            if (!d.success) { hideLoader(); brNotify(d.message || L.error, 'error'); return; }
            // Loader stays up through the redirect - the page is on its way out.
            window.location.href = siteUrl + '/organization/?id=' + d.org_id;
        }).fail(function () { hideLoader(); brNotify(L.error, 'error'); });
    }

    function orgDelete(org_id) {
        $.post(ajaxurl(), { action: 'deleteOrg', nonce: $('#manage-orgs-nonce').val(), org_id: org_id },
            function (r) {
                if (!parseJs(r).success) { brNotify(L.orgDeleteError, 'error'); return; }
                $('#org-row-' + org_id).fadeOut(300, function () { $(this).remove(); });
                brNotify(L.orgDeleted, 'success');
            }
        );
    }

    // ── Wiring ───────────────────────────────────────────────────────────────

    $(function () {
        $('#adv-search-string').on('keyup', function (e) { if (e.key === 'Enter') orgSearchAdventures(); });
        $('#org-ap-search').on('keyup',     function (e) { if (e.key === 'Enter') orgApFind(); });

        // The clear button only belongs there while there is something to clear.
        $('#org-ap-search').on('input', function () {
            $('#org-ap-clear').toggleClass('br-initially-hidden', this.value === '');
        });

        // Delegated: these rows are built after load.
        $('#org-adv-search-results').on('click', '.br-ap-row-click', function () {
            orgAddAdventure($(this).data('adventure-id'), this);
        });
        $('#org-adventures-list').on('click', '[data-remove-adventure]', function () {
            var id = $(this).data('remove-adventure');
            brConfirmInline(this, L.remove, function () { orgRemoveAdventure(id); });
        });

        // Client-side row filters. Any input can opt in by naming the rows it
        // filters in data-filter-rows, which is what my-orgs uses per org.
        $('#search-org-players').on('input', function () { filterRows('#org-players-list tr', this.value); });
        $('#filter-orgs').on('input',        function () { filterRows('#orgs-list tr', this.value); });
        $('[data-filter-rows]').on('input',  function () { filterRows($(this).data('filter-rows'), this.value); });
    });

    function filterRows(selector, value) {
        var val = value.toLowerCase();
        $(selector).each(function () {
            $(this).toggle(!val || this.textContent.toLowerCase().indexOf(val) > -1);
        });
    }

    // Exposed for the inline onclick handlers still in the templates.
    window.saveOrgSettings       = saveOrgSettings;
    window.removePlayerFromOrg   = removePlayerFromOrg;
    window.orgSetPlayerRole      = orgSetPlayerRole;
    window.orgApFind             = orgApFind;
    window.orgApClearSearch      = orgApClearSearch;
    window.orgApAddPlayer        = orgApAddPlayer;
    window.orgImportPlayersCsv   = orgImportPlayersCsv;
    window.orgBulkFromAdventure  = orgBulkFromAdventure;
    window.orgSearchAdventures   = orgSearchAdventures;
    window.orgAddAdventure       = orgAddAdventure;
    window.orgRemoveAdventure    = orgRemoveAdventure;
    window.orgStatsInit          = orgStatsInit;
    window.toggleNewOrgPanel     = toggleNewOrgPanel;
    window.orgCreate             = orgCreate;
    window.orgDelete             = orgDelete;

}(jQuery));

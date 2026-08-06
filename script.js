//////////////////  REGISTER NEW PLAYER  ////////////////
function jumpToStepByHash() {
    let step = window.location.hash.substring(1);
    let step_number = step.replace('step-', '');
    if (!step_number) {
        step_number = 1;
    }
    jumpToStep(step_number);
}

function jumpToQuestionByHash() {
    let step = window.location.hash.substring(1);
    let step_number = step.replace('step-', '');
    if (!step_number) {
        jumpToQuestion(0);
    } else {
        jumpToQuestion(step_number);
    }

}

function changeTabByHash() {
    let tabToOpen = window.location.hash.substring(1);
    if (tabToOpen) {
        if ($('#tab-group')) {
            switchTabs('#tab-group', '#' + tabToOpen);
        } else if ($('#main-tabs')) {
            switchTabs('#main-tabs', '#' + tabToOpen);
        }
    }
}

function registerNewPlayer() {
    showLoader();
    let nickname = $('#new_user_nickname').val();
    let email = $('#new_user_email').val();
    let password = $('#new_password').val();
    let lang = $('#new_the_lang').val();
    let redirect = $('#the_redirect').val();
    let nonce = $('#register_nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'bluerabbit_add_new_player',
            redirect: redirect,
            nickname: nickname,
            email: email,
            password: password,
            lang: lang,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        },
    });
}

//////////////////  ADD PLAYERS ONE AT A TIME  ////////////////
// Replaces enrollUser()/checkUserDataExists(). Those took one exact nickname or
// email, answered yes/no, and - because enrollUser() disabled the button up
// front and unbound its handler on success without ever re-enabling it - died
// after a single add until the page was reloaded. This keeps searching and
// adding all day, and every row reports its own outcome in place.

var BRAddPlayer = {
    timer: null,
    query: '',
    seq: 0,          // guards against a slow response overwriting a newer one
    added: 0,
    pending: {}      // player_id -> true while its request is in flight
};

function brApEsc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}

function brApSetStatus(html, cls) {
    var $s = $('#br-ap-status');
    if (!html) { $s.html('').attr('class', 'br-ap-status'); return; }
    $s.html(html).attr('class', 'br-ap-status ' + (cls || ''));
}

function brApToggleCreate(show) {
    $('#br-ap-create').toggleClass('br-initially-hidden', !show);
    $('#br-ap-create-toggle').toggleClass('br-initially-hidden', !!show);
    $('#br-ap-create-error').addClass('br-initially-hidden').text('');
    if (show) {
        // Carry whatever was typed into the search over to the new-account form,
        // so looking someone up and then creating them isn't two lots of typing.
        var q = ($('#username-search').val() || '').trim();
        if (q && !$('#br-ap-new-email').val()) {
            if (q.indexOf('@') > 0) { $('#br-ap-new-email').val(q); }
            else { $('#br-ap-new-nickname').val(q); }
        }
        $('#br-ap-new-email').trigger('focus');
    }
}

function brApRowHTML(p) {
    var name = p.name || p.nickname || p.email;
    var right, cls = '';

    if (p.status === 'in') {
        cls = ' br-ap-row-in';
        var roleLabel = p.role === 'gm' ? 'GM' : (p.role === 'npc' ? 'NPC' : brApI18n.in_adventure);
        right = '<span class="br-badge br-badge-blue">' + brApEsc(roleLabel) + '</span>';
    } else {
        var label = p.status === 'out' ? brApI18n.re_add : brApI18n.add;
        right = '<button type="button" class="br-btn cyan br-btn-sm br-ap-add-btn" ' +
                'data-player="' + p.player_id + '" onClick="brApAdd(' + p.player_id + ');">' +
                '<span class="icon icon-add"></span> ' + label + '</button>';
        if (p.status === 'out') {
            right = '<span class="br-badge br-badge-amber">' + brApI18n.removed + '</span>' + right;
        }
    }

    return '<div class="br-ap-row' + cls + '" id="br-ap-row-' + p.player_id + '">' +
        '<div class="br-ap-avatar" style="background-image:url(' + brApEsc(p.avatar) + ')"></div>' +
        '<div class="br-ap-identity">' +
            '<span class="br-ap-name">' + brApEsc(name) + '</span>' +
            '<span class="br-ap-meta">' +
                '<span class="br-ap-nick"><span class="icon icon-player"></span> ' + brApEsc(p.nickname) + '</span>' +
                '<span class="br-ap-email">' + brApEsc(p.email) + '</span>' +
            '</span>' +
        '</div>' +
        '<div class="br-ap-action">' + right + '</div>' +
    '</div>';
}

function brApRenderResults(players, query) {
    var $r = $('#br-ap-results');
    if (!players.length) {
        $r.html('');
        brApSetStatus(brApI18n.no_match.replace('%s', brApEsc(query)), 'br-ap-status-empty');
        // Nothing matched, so creating the account is now the useful next step.
        brApToggleCreate(true);
        return;
    }
    $r.html(players.map(brApRowHTML).join(''));
    var free = players.filter(function (p) { return p.status !== 'in'; }).length;
    brApSetStatus(
        brApI18n.found.replace('%d', players.length).replace('%a', free),
        'br-ap-status-found'
    );
}

function brApSearch() {
    var q = ($('#username-search').val() || '').trim();
    $('#br-ap-clear').toggleClass('br-initially-hidden', q === '');

    if (q.length < 2) {
        $('#br-ap-results').html('');
        brApSetStatus(q === '' ? '' : brApI18n.keep_typing, 'br-ap-status-hint');
        return;
    }
    if (q === BRAddPlayer.query) return;
    BRAddPlayer.query = q;

    var mySeq = ++BRAddPlayer.seq;
    brApSetStatus(brApI18n.searching, 'br-ap-status-hint');

    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'brSearchRegisteredPlayers',
            nonce: $('#br-ap-nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            search: q
        },
        success: function (raw) {
            // A reply for a query the user has already typed past is stale.
            if (mySeq !== BRAddPlayer.seq) return;
            var data;
            try { data = JSON.parse(raw); } catch (e) { data = null; }
            if (!data || !data.success) {
                brApSetStatus(brApEsc((data && data.message) || brApI18n.search_error), 'br-ap-status-error');
                return;
            }
            brApRenderResults(data.players || [], q);
        },
        error: function () {
            if (mySeq !== BRAddPlayer.seq) return;
            brApSetStatus(brApI18n.search_error, 'br-ap-status-error');
        }
    });
}

function brApLog(result, label) {
    BRAddPlayer.added++;
    var extra = '';
    if (result && result.password) {
        // Only ever set for accounts this box just created.
        extra = ' <span class="br-ap-log-pass">' + brApI18n.password + ': <code>' +
                brApEsc(result.password) + '</code></span>';
    }
    $('#br-ap-log-list').prepend(
        '<li><span class="icon icon-check"></span> ' + brApEsc(label) + extra + '</li>'
    );
    $('#br-ap-log-count').text(BRAddPlayer.added);
    $('#br-ap-log').removeClass('br-initially-hidden');
    // The enrolled table above was rendered server-side and its counters are
    // recomputed by its own pager on every keystroke, so incrementing them here
    // would be a number that silently reverts. Offer a refresh instead of faking
    // state the page doesn't actually have.
    $('#br-ap-refresh').removeClass('br-initially-hidden');
}

function brApAdd(playerId) {
    if (BRAddPlayer.pending[playerId]) return;
    BRAddPlayer.pending[playerId] = true;

    var $row = $('#br-ap-row-' + playerId);
    var $btn = $row.find('.br-ap-add-btn');
    var name = $row.find('.br-ap-name').text() || '';
    var mail = $row.find('.br-ap-email').text() || '';
    $btn.prop('disabled', true).html('<span class="icon icon-rotate br-ap-spin"></span> ' + brApI18n.adding);

    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'brAddSinglePlayer',
            nonce: $('#br-ap-nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            player_id: playerId
        },
        success: function (raw) {
            delete BRAddPlayer.pending[playerId];
            var data;
            try { data = JSON.parse(raw); } catch (e) { data = null; }
            if (data && data.nonce) $('#br-ap-nonce').val(data.nonce);
            if (data && data.message) displayAjaxResponse(raw);

            if (!data || !data.success) {
                $btn.prop('disabled', false).html('<span class="icon icon-add"></span> ' + brApI18n.add);
                $row.addClass('br-ap-row-error');
                return;
            }
            // The row stays put and turns into its own receipt, so a long list of
            // adds reads back as a list of what happened.
            var already = data.result && data.result.status === 'already';
            $row.removeClass('br-ap-row-error').addClass(already ? 'br-ap-row-in' : 'br-ap-row-done');
            $row.find('.br-ap-action').html(
                '<span class="br-badge ' + (already ? 'br-badge-amber' : 'br-badge-green') + '">' +
                '<span class="icon icon-check"></span> ' +
                (already ? brApI18n.in_adventure : brApI18n.added) + '</span>'
            );
            if (!already) brApLog(data.result, (name ? name + ' — ' : '') + mail);
        },
        error: function () {
            delete BRAddPlayer.pending[playerId];
            $btn.prop('disabled', false).html('<span class="icon icon-add"></span> ' + brApI18n.add);
            $row.addClass('br-ap-row-error');
        }
    });
}

function brApCreate() {
    var email = ($('#br-ap-new-email').val() || '').trim();
    var $err  = $('#br-ap-create-error');

    if (!email || email.indexOf('@') < 1 || email.indexOf('.') < 0) {
        $err.text(brApI18n.need_email).removeClass('br-initially-hidden');
        $('#br-ap-new-email').trigger('focus');
        return;
    }
    $err.addClass('br-initially-hidden').text('');

    var $btn = $('#br-ap-create-btn');
    $btn.prop('disabled', true).html('<span class="icon icon-rotate br-ap-spin"></span> ' + brApI18n.adding);

    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'brAddSinglePlayer',
            nonce: $('#br-ap-nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            email: email,
            nickname: ($('#br-ap-new-nickname').val() || '').trim(),
            firstname: ($('#br-ap-new-first').val() || '').trim(),
            lastname: ($('#br-ap-new-last').val() || '').trim(),
            password: ($('#br-ap-new-password').val() || '').trim(),
            lang: $('#br-ap-lang').val()
        },
        success: function (raw) {
            $btn.prop('disabled', false).html('<span class="icon icon-add"></span> ' + brApI18n.create_add);
            var data;
            try { data = JSON.parse(raw); } catch (e) { data = null; }
            if (data && data.nonce) $('#br-ap-nonce').val(data.nonce);
            if (data && data.message) displayAjaxResponse(raw);

            if (!data || !data.success) {
                $err.text((data && data.result && data.result.detail) || brApI18n.create_error)
                    .removeClass('br-initially-hidden');
                return;
            }
            var r = data.result || {};
            var label = ($('#br-ap-new-first').val() || '') + ' ' + ($('#br-ap-new-last').val() || '');
            brApLog(r, (label.trim() ? label.trim() + ' — ' : '') + (r.email || email));

            // Ready for the next one straight away.
            $('#br-ap-new-email, #br-ap-new-nickname, #br-ap-new-first, #br-ap-new-last, #br-ap-new-password').val('');
            $('#username-search').val('');
            BRAddPlayer.query = '';
            $('#br-ap-results').html('');
            brApSetStatus('', '');
            brApToggleCreate(false);
            $('#username-search').trigger('focus');
        },
        error: function () {
            $btn.prop('disabled', false).html('<span class="icon icon-add"></span> ' + brApI18n.create_add);
            $err.text(brApI18n.create_error).removeClass('br-initially-hidden');
        }
    });
}

// Strings live here so the module works even if a page forgets to localise them.
var brApI18n = window.brApI18n || {};
brApI18n.add          = brApI18n.add          || 'Add';
brApI18n.re_add       = brApI18n.re_add       || 'Add back';
brApI18n.adding       = brApI18n.adding       || 'Adding…';
brApI18n.added        = brApI18n.added        || 'Added';
brApI18n.removed      = brApI18n.removed      || 'Removed earlier';
brApI18n.in_adventure = brApI18n.in_adventure || 'In this adventure';
brApI18n.searching    = brApI18n.searching    || 'Searching…';
brApI18n.keep_typing  = brApI18n.keep_typing  || 'Keep typing — at least 2 characters.';
brApI18n.found        = brApI18n.found        || '%d found, %a can be added.';
brApI18n.no_match     = brApI18n.no_match     || 'Nobody registered matches "%s". Create the account below.';
brApI18n.search_error = brApI18n.search_error || 'Search failed. Check your connection and try again.';
brApI18n.create_error = brApI18n.create_error || 'Could not create that account.';
brApI18n.need_email   = brApI18n.need_email   || 'A valid email is required.';
brApI18n.create_add   = brApI18n.create_add   || 'Create and add';
brApI18n.password     = brApI18n.password     || 'Password';

jQuery(function ($) {
    var $search = $('#username-search');
    if (!$search.length) return;

    $search.on('keyup search input', function (e) {
        if (e.key === 'Enter') { clearTimeout(BRAddPlayer.timer); brApSearch(); return; }
        clearTimeout(BRAddPlayer.timer);
        BRAddPlayer.timer = setTimeout(brApSearch, 250);
    });
    $('#br-ap-clear').on('click', function () {
        $search.val('').trigger('focus');
        BRAddPlayer.query = '';
        BRAddPlayer.seq++;
        $('#br-ap-results').html('');
        brApSetStatus('', '');
        $('#br-ap-clear').addClass('br-initially-hidden');
    });
});

function uploadBulkQuestions() {
    const upload_bulk_questions_form = document.getElementById('upload_bulk_questions_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_questions')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkQuestions');
    formData.append('adventure_id', $('#the_adventure_id').val());
    formData.append('quest_id', $('#the_quest_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                let data = JSON.parse(response);
                console.log(data.debug);
                for (let i = 0; i < data.messages.length; i++) {
                    $("#notify-message ul.content").append(data.messages[i]);
                    let notificationTimeOut1 = setTimeout(function () {
                        $("#notify-message ul.content li:last-child").addClass('active');
                        let last_message = $("#notify-message ul.content li:last-child");
                        let notificationTimeOut2 = setTimeout(function () {
                            last_message.removeClass('active');
                            let notificationTimeOut3 = setTimeout(function () {
                                last_message.remove();
                            }, 300);
                        }, 1000);
                    }, 50);
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}



function uploadBulkQuests() {
    const upload_bulk_quests_form = document.getElementById('upload_bulk_quests_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_quests')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkQuests');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                let data = JSON.parse(response);
                for (let i = 0; i < data.messages.length; i++) {
                    $("#notify-message ul.content").append(data.messages[i]);
                    $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                        $(this).remove();
                    });
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function uploadBulkItems() {
    const upload_bulk_items_form = document.getElementById('upload_bulk_items_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_items')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkItems');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                displayAjaxResponse(response);
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function uploadBulkAchievements() {
    const upload_bulk_achievments_form = document.getElementById('upload_bulk_achievments_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_achievements')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkAchievements');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                displayAjaxResponse(response);
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function uploadBulkSessions() {
    const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_sessions')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkSessions');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                let data = JSON.parse(response);
                for (let i = 0; i < data.messages.length; i++) {
                    $("#notify-message ul.content").append(data.messages[i]);
                    $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                        $(this).remove();
                    });
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function uploadBulkSpeakers() {
    const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_speakers')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkSpeakers');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                let data = JSON.parse(response);
                for (let i = 0; i < data.messages.length; i++) {
                    $("#notify-message ul.content").append(data.messages[i]);
                    $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                        $(this).remove();
                    });
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function uploadBulkUsers() {
    const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
    const formData = new FormData();

    let file = $('#the_csv_file_with_users')[0].files[0];
    formData.append('csv_file', file);
    formData.append('action', 'uploadBulkUsers');
    formData.append('adventure_id', $('#the_adventure_id').val());

    if (file) {
        showLoader();
        $.ajax({
            url: runAJAX.ajaxurl,
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            type: "POST",
            method: "POST",
            success: function (response) {
                let data = JSON.parse(response);
                if (data.success) {
                    $("#just-uploaded-users-body").html('').append(data.table_content);
                    $("#call-to-action").html(data.cta);
                    for (let i = 0; i < data.messages.length; i++) {
                        $("#notify-message ul.content").append(data.messages[i]);
                        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                            $(this).remove();
                        });
                    }
                    selectAllCheckBoxes();
                } else {
                    for (let i = 0; i < data.errors.length; i++) {
                        $("#notify-message ul.content").append(data.errors[i]);
                        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                            $(this).remove();
                        });
                    }
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-no-file-selected', 1000, '', 'player');
    }
}

function bulkEnrollUsers() {
    let new_users = [];
    let existing_users = [];
    $('.select-element:checkbox:checked').each(function (index) {

        //tr id => row-new-bulk-user-$row_index
        let row_id = $(this).attr('data-id');
        if ($('#row-new-bulk-user-' + row_id).hasClass('enroll')) {
            let existing_users_values = {
                nickname: $('#row-new-bulk-user-' + row_id + ' .nickname').text(),
                password: $('#row-new-bulk-user-' + row_id + ' .password').text(),
                email: $('#row-new-bulk-user-' + row_id + ' .email').text(),
                firstname: $('#row-new-bulk-user-' + row_id + ' .firstname').text(),
                lastname: $('#row-new-bulk-user-' + row_id + ' .lastname').text(),
                lang: $('#row-new-bulk-user-' + row_id + ' .lang').text(),
                ///////////////////////
                gender: $('#row-new-bulk-user-' + row_id + ' .gender').text(),
                work_level: $('#row-new-bulk-user-' + row_id + ' .work_level').text(),
                work_function: $('#row-new-bulk-user-' + row_id + ' .work_function').text(),
                work_sub_function: $('#row-new-bulk-user-' + row_id + ' .work_sub_function').text(),
                job_profile: $('#row-new-bulk-user-' + row_id + ' .job_profile').text(),
                buisness_pillar: $('#row-new-bulk-user-' + row_id + ' .buisness_pillar').text(),
                work_cluster: $('#row-new-bulk-user-' + row_id + ' .work_cluster').text(),
                work_country: $('#row-new-bulk-user-' + row_id + ' .work_country').text(),
                work_location: $('#row-new-bulk-user-' + row_id + ' .work_location').text(),




                user_id: $(this).attr('data-user-id'),
            };
            existing_users.push(existing_users_values);
        } else if ($('#row-new-bulk-user-' + row_id).hasClass('register')) {
            let new_users_values = {
                nickname: $('#row-new-bulk-user-' + row_id + ' .nickname').text(),
                password: $('#row-new-bulk-user-' + row_id + ' .password').text(),
                email: $('#row-new-bulk-user-' + row_id + ' .email').text(),
                firstname: $('#row-new-bulk-user-' + row_id + ' .firstname').text(),
                lastname: $('#row-new-bulk-user-' + row_id + ' .lastname').text(),
                lang: $('#row-new-bulk-user-' + row_id + ' .lang').text(),
                ////////////////////////////
                gender: $('#row-new-bulk-user-' + row_id + ' .gender').text(),
                work_level: $('#row-new-bulk-user-' + row_id + ' .work_level').text(),
                work_function: $('#row-new-bulk-user-' + row_id + ' .work_function').text(),
                work_sub_function: $('#row-new-bulk-user-' + row_id + ' .work_sub_function').text(),
                job_profile: $('#row-new-bulk-user-' + row_id + ' .job_profile').text(),
                buisness_pillar: $('#row-new-bulk-user-' + row_id + ' .buisness_pillar').text(),
                work_cluster: $('#row-new-bulk-user-' + row_id + ' .work_cluster').text(),
                work_country: $('#row-new-bulk-user-' + row_id + ' .work_country').text(),
                work_location: $('#row-new-bulk-user-' + row_id + ' .work_location').text(),
            };
            new_users.push(new_users_values);
        }

    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'bulkEnrollUsers',
            new_users: new_users,
            adventure_id: $('#the_adventure_id').val(),
            existing_users: existing_users
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function resetDemoAdventurePlayer() {
    let nonce = $('#reset_demo_nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    let player_password = $('#the_player_password').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetDemoAdventurePlayer',
            player_password: player_password,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function resetPlayerPassword() {
    let nonce = $('#reset_user_password_nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    let current_gm_password = $('#the_gm_password').val();
    let new_player_password = $('#the_player_password').val();
    let new_player_password_confirm = $('#the_player_password_confirm').val();
    let player_affected = $('#the_player_to_update').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetPlayerPassword',
            adventure_id: adventure_id,
            player_affected: player_affected,
            new_player_password: new_player_password,
            new_player_password_confirm: new_player_password_confirm,
            current_gm_password: current_gm_password,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function br_logout() {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_logout'
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            document.location.href = data.location;
        }
    });
}

////////////////////////////////////////// Rate Quest ////////////////////////////////////////////

function rateQuest(quest_id, rating) {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'rateQuest',
            quest_id: quest_id,
            rating: rating
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function do_resize(textbox) {

    let maxrows = 5;
    let txt = textbox.value;
    let cols = textbox.cols;

    let arraytxt = txt.split('\n');
    let rows = arraytxt.length;

    for (let i = 0; i < arraytxt.length; i++)
        rows += parseInt(arraytxt[i].length / cols);

    if (rows > maxrows) textbox.rows = maxrows;
    else textbox.rows = rows;
}

function formatToCurrency(amount) {
    return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
}

function animateNumber(who, speed = 500, p_delay = 0, decimals = 0, format = '') {
    let aniNumber = 0;
    $(who).each(function () {
        $(this).prop('Counter', $('.number', this).text()).stop().delay(p_delay).animate({
            Counter: $('.end-value', this).val(),
        }, {
            duration: speed,
            step: function (now) {
                if (format == 'money') {
                    aniNumber = formatToCurrency(now, 2);
                    $('.number', this).text(aniNumber);
                } else {
                    if (decimals > 0) {
                        $('.number', this).text((now.toFixed(decimals)));
                    } else {
                        $('.number', this).text(Math.ceil(now));
                    }
                }
            },
            complete: function () {
                //alert('Complete');
            }
        });
    });
}

function deadlineCountdown(the_deadline) {
    let deadlineInterval;
    let countDownDate = new Date(the_deadline).getTime();
    if (deadlineInterval) {
        clearInterval(deadlineInterval);
    }
    deadlineInterval = setInterval(function () {
        let now = new Date().getTime();
        let distance = countDownDate - now;
        let days = Math.floor(distance / (1000 * 60 * 60 * 24));
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        //let counter = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
        $('#deadline-countdown #deadline-days .number').text(days);
        $('#deadline-countdown #deadline-hours .number').text(hours);
        $('#deadline-countdown #deadline-minutes .number').text(minutes);
        $('#deadline-countdown #deadline-seconds .number').text(seconds);
        if (distance < 0) {
            clearInterval(deadlineInterval);
            $('#deadline-countdown').text("Expired!");
        }
    }, 1000);
}

function notify(message = "", icon = "check", color = "blue", message_delay = 1000) {

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_notify',
            message: message,
            icon: icon,
            color: color
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            $("#notify-message ul.content").append(data.message);
            setTimeout(function () {
                $("#notify-message ul.content li:last-child").addClass('active');
                let last_message = $("#notify-message ul.content li:last-child");
                setTimeout(function () {
                    last_message.removeClass('active');
                    setTimeout(function () {
                        last_message.remove();
                        if (data.reload) {
                            document.location.reload();
                        }
                    }, 300);

                }, message_delay);
            }, 1);



        }
    });
}

// Semantic wrapper over notify(). Callers say what happened - brNotify('Saved',
// 'success') - instead of each one picking its own icon and colour and drifting
// out of step with the rest of the app.
function brNotify(message, type = 'info', message_delay = 1500) {
    const styles = {
        success: ['check',   'green'],
        error:   ['cancel',  'red'],
        warn:    ['warning', 'amber'],
        warning: ['warning', 'amber'],
        info:    ['info',    'blue']
    };
    const style = styles[type] || styles.info;
    notify(message, style[0], style[1], message_delay);
}

function notification(message, msg_delay = 1000, var_content = null, var_icon = null) {
    $("#notify-message ul.content").append($(message).html());
    let notificationTimeOut1 = setTimeout(function () {
        $("#notify-message ul.content li:last-child").addClass('active');
        let last_message = $("#notify-message ul.content li:last-child");
        let notificationTimeOut2 = setTimeout(function () {
            last_message.removeClass('active');
            let notificationTimeOut3 = setTimeout(function () {
                last_message.remove();
            }, 300);
        }, msg_delay);
    }, 1);
}

function copyTextFrom(input_id, trigger_id) {
    $(input_id).attr('type', 'text');
    let copyText = $(input_id);
    copyText.select();
    document.execCommand("copy");
    $(input_id).attr('type', 'hidden');
    if (trigger_id) {
        $(trigger_id).addClass('active');
        let timeout = setTimeout(function () {
            $(trigger_id).removeClass('active');
        }, 1500);
    }
    notification('#msg-text-copied', 1000, 'Text copied', 'duplicate');
}

function assignInstructionsPages() {
    if ($('#quest-instructions .instructions-step').length > 1) {
        $('#last-prev-button').removeClass('hidden');
    }
    $('#quest-instructions .instructions-step').each(function (index, element) {
        $(this).attr('id', "instructions-step-" + index);
        $('input.step-id-value', this).val(index);
        $('.prev-button', this).attr('onClick', 'questStep(' + (index - 1) + ')');
        $('.next-button', this).attr('onClick', 'questStep(' + (index + 1) + ')');
        if (index <= 0) {
            $(this).addClass('active');
        }
    });

}

function showMenu(who) {
    $('.nav-group nav').removeClass('active');
    $(who).addClass('active');
}

function questStep(id) {
    $('#quest-instructions .instructions-step').removeClass('active');
    $('#instructions-step-' + id).addClass('active');
}

function animateScroll(who, center = null, difference = null) {
    //	let mytop =  Math.round($(this).offset().top - $(window).scrollTop()); - ($(who).offset().top 
    let divOffsetTop = $(who).offset().top - 30;
    if (center > 0) {
        if (difference > 0) {
            divOffsetTop = $(who).offset().top - ($(window).height() / 2) + (difference);
        } else {
            divOffsetTop = $(who).offset().top - ($(window).height() / 2) - ($(who).height() / 2);
        }
    }
    $("html, body").animate({
        scrollTop: divOffsetTop
    }, 300);
}

function animateScrollBottom(who) {
    let divOffsetTop = $(who).offset().top - $(window).height() + 150;
    $("html, body").animate({
        scrollTop: divOffsetTop
    }, 1500);
}

function loadContent(content, id = 0) {
    hideAllOverlay();
    showLoader();
    $('#overlay-content .content').html('');
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadContent',
            adventure_id: adventure_id,
            content: content,
            id: id
        }),
        method: "POST",
        success: function (data_received) {
            $('#overlay-content .content').html(data_received);
            let flipTimeout = setTimeout(function () {
                $('#overlay-content').addClass('active');
                $('.loader, .small-loader').removeClass('active');
            }, 10);
        }
    });
}

function unloadContent(who = null) {
    hideAllOverlay();
    let clearTimeout;
    if (!who) {
        clearTimeout = setTimeout(function () {
            $("#overlay-content .content").html('');
        }, 500);
    } else {
        $(who).removeClass('active');
        clearTimeout = setTimeout(function () {
            $(who).html('');
        }, 500);
    }

}

function loadTabiEditor(id = 0) {
    hideAllOverlay();
    showLoader();
    $('#tabi-editor-container').html('');
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadContent',
            adventure_id: adventure_id,
            content: 'tabi-editor',
            id: id
        }),
        method: "POST",
        success: function (data_received) {
            $('#tabi-editor-container').html(data_received);
            let tabiEditorContainerTimeout = setTimeout(function () {
                $('#tabi-editor-container').addClass('active');
                $('.loader, .small-loader').removeClass('active');
                initializeTabiEditorDrag();
            }, 10);
        }
    });
}

function initializeTabiEditorDrag() {
    $(".tabi-editor-pieces-list-sortable").sortable({
        update: function (event, ui) {
            sortZindex();
        }
    });
    $('#tabi-pieces .tabi-piece').each(function () {
        applyTransform($(this).data('piece-id'), 1);
    });
    $('#tabi-pieces .tabi-piece').draggable({
        start: function () {
            $(this).addClass("dragging");
        },
        drag: function (event, ui) {
            let piece = $(this);
            let pieceX = (ui.position.left) / $('#tabi-pieces').width() * 100;
            let pieceY = (ui.position.top) / $('#tabi-pieces').height() * 100;
            $('.piece-x', this).val(pieceX);
            $('.piece-y', this).val(pieceY);
        },
        stop: function () {
            applyTransform($(this).data('piece-id'), 1);
            $(this).removeClass("dragging");
        }
    });
}

function sortZindex() {
    $('#tabi-editor-pieces-list-sortable li.tabi-piece-list-item').each(function (index) {
        let item_id = $(this).data('piece-id');
        $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val(100 - index);
        $('.data-piece-z', this).text(100 - index);
        applyTransform(item_id);
    });

}

function editTabiPiece(item_id) {
    let piece = $('#tabi-piece-' + item_id);
    let li_piece = $('#list-item-piece-' + item_id);
    if (!piece.hasClass('editing')) {
        $('.tabi-piece, .tabi-piece-list-item').removeClass('editing');
        piece.addClass('editing');
        li_piece.addClass('editing');
    } else {
        $('.tabi-piece, .tabi-piece-list-item').removeClass('editing');
    }
}

function resetTabiPiece(item_id) {
    $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-scale`).val(1);
    $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-rotation`).val(0);
    $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val(1);
    $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-x`).val(10);
    $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-y`).val(10);
    applyTransform(item_id, 1);
}

function applyTransform(item_id, setup = null) {
    let scaleVal = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-scale`).val();
    let rotationVal = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-rotation`).val();
    let zIndex = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val();
    if (zIndex < 1) {
        zIndex = 1;
    }
    let xPos = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-x`).val();
    let yPos = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-y`).val();
    let transform_values = `scale(${scaleVal}) rotate(${rotationVal}deg)`;

    $('#tabi-piece-image-' + item_id).css({
        'transform': transform_values
    });
    $('#tabi-piece-' + item_id).css({
        'z-index': zIndex,
        'width': scaleVal + '%'
    });

    if (setup) {
        $('#tabi-piece-' + item_id).css({
            'top': yPos + '%',
            'left': xPos + '%'
        });
    }
    let item_data = {
        item_x: xPos,
        item_y: yPos,
        item_z: zIndex,
        item_scale: scaleVal,
        item_rotation: rotationVal,
    }
    $('.small-loader').addClass('active');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'saveTabiPiecePosition',
            item_id: item_id,
            item_data: item_data
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            $('.small-loader').removeClass('active');
        }
    });

}

function zUp(id) {
    let $zInput = $(`#tabi-piece-${id} .piece-z`);
    $zInput.val(+$zInput.val() + 1);
    applyTransform(id);
}

function zDown(id) {
    let $zInput = $(`#tabi-piece-${id} .piece-z`);
    if ($zInput.val() > 0) {
        $zInput.val(+$zInput.val() - 1);
    }
    applyTransform(id);
}

function scaleUp(id) {
    let $scaleInput = $(`#tabi-piece-${id} .piece-scale`);
    if ($scaleInput.val() < 100) {
        $scaleInput.val(+$scaleInput.val() + 0.25);
    }
    applyTransform(id);
}

function scaleDown(id) {
    let $scaleInput = $(`#tabi-piece-${id} .piece-scale`);
    if ($scaleInput.val() > 1) {
        $scaleInput.val(+$scaleInput.val() - 0.25);
    }
    applyTransform(id);
}

function rotateCW(id) {
    let $rotateInput = $(`#tabi-piece-${id} .piece-rotation`);
    $rotateInput.val(+$rotateInput.val() + 15);
    if ($rotateInput.val() > 345) {
        $rotateInput.val(0);
    }
    applyTransform(id);
}

function rotateCCW(id) {
    let $rotateInput = $(`#tabi-piece-${id} .piece-rotation`);
    $rotateInput.val(+$rotateInput.val() - 15);
    if ($rotateInput.val() < 15) {
        $rotateInput.val(360);
    }
    applyTransform(id);
}

function resetMilestoneSizes() {
    $(`.milestone .milestone-data .z-pos`).val(1);
    $(`.milestone`).each(function () {
        updateMilestonePosition($(this).data('id'));
    });
}

function updateMilestonePosition(id) {
    let milestone = $('#milestone-' + id);
    let topPos = $(`#milestone-${id} .milestone-data input.top`).val();
    let leftPos = $(`#milestone-${id} .milestone-data input.left`).val();
    /*
    	let xPos = $(`#milestone-${id} .milestone-data input.x-pos`).val();
    	let yPos = $(`#milestone-${id} .milestone-data input.y-pos`).val();
    	let rotation = $(`#milestone-${id} .milestone-data input.rotation`).val();
    */

    let zPos = $(`#milestone-${id} .milestone-data input.z-pos`).val();
    if (zPos < 0.5) {
        zPos = 0.5;
    } else if (zPos < 0.5) {
        zPos = 0.5;
    }
    let xPos = 0;
    let yPos = 0;
    let rotation = 0;
    let milestone_data = {
        top: topPos,
        left: leftPos,
        x: xPos,
        y: yPos,
        z: zPos,
        rotation: rotation,
    }
    $('.small-loader').addClass('active');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateMilestonePosition',
            milestone_id: id,
            milestone_data: milestone_data
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            $('.small-loader').removeClass('active');
        }
    });
}

function initializeBuilderMilestones() {
    // Set explicit dimensions on .milestone so jQuery UI resizable has a size to work from
    $('#builder .milestone').each(function () {
        let $c = $(this).find('.milestone-content');
        $(this).css({
            width: $c.outerWidth(),
            height: $c.outerHeight()
        });
    });

    $('#builder .milestone').draggable({
        handle: '.milestone-handle',
        cancel: '.ui-resizable-handle',
        snap: true,
        snapTolerance: 5,
        start: function () {
            $(this).css('transition', 'none');
            $(this).addClass("dragging");
        },
        drag: function (event, ui) {
            let posTop = (ui.position.top);
            let posLeft = (ui.position.left);
            $(`.milestone-data input.top`, this).val(posTop);
            $(`.milestone-data input.left`, this).val(posLeft);
        },
        stop: function () {
            $(this).css('transition', '');
            updateMilestonePosition($(this).data('id'));
            $(this).removeClass("dragging");
        }
    }).resizable({
        handles: 'se',
        aspectRatio: 105 / 90,
        minWidth: 105,
        minHeight: 90,
        maxHeight: 300,
        maxWidth: Math.round(300 * 105 / 90),
        start: function () {
            $(this).css('transition', 'none');
            $(this).find('.milestone-content').css('transition', 'none');
        },
        resize: function (event, ui) {
            $(this).find('.milestone-content').css({
                width: ui.size.width + 'px',
                height: ui.size.height + 'px'
            });
        },
        stop: function (event, ui) {
            $(this).css('transition', '');
            $(this).find('.milestone-content').css('transition', '');
            let id = $(this).data('id');
            let newH = Math.round(ui.size.height);
            let zVal = parseFloat((newH / 90).toFixed(2));
            zVal = Math.max(1, Math.min(5, zVal));
            $(this).find('.milestone-data .z-pos').val(zVal);
            updateMilestonePosition(id);
        }
    });
}
///////////////////////// Journey Assets //////////////////

function initializeBuilderAssets() {
    $('#builder .builder-asset').draggable({
        cancel: '.ui-resizable-handle, .asset-rotate-btn, .asset-link-input, .asset-controls',
        start: function () {
            $(this).addClass('dragging');
        },
        stop: function () {
            let id = $(this).data('asset-id');
            let top = parseInt($(this).css('top'), 10);
            let left = parseInt($(this).css('left'), 10);
            let nonce = $(this).find('.asset-nonce').val();
            jQuery.ajax({
                url: runAJAX.ajaxurl,
                data: {
                    action: 'saveJourneyAssetPosition',
                    asset_id: id,
                    top: top,
                    left: left,
                    nonce: nonce
                },
                method: 'POST'
            });
            $(this).removeClass('dragging');
        }
    }).resizable({
        handles: 'se',
        minWidth: 40,
        stop: function (event, ui) {
            let id = $(this).data('asset-id');
            let newWidth = Math.round(ui.size.width);
            $(this).css('height', '');
            $(this).find('.asset-width-val').val(newWidth);
            _saveAssetProperties(id);
        }
    });
}

function _saveAssetProperties(id) {
    let $el = $('#journey-asset-' + id);
    let nonce = $el.find('.asset-nonce').val();
    let width = parseInt($el.find('.asset-width-val').val(), 10);
    let z = parseInt($el.find('.asset-z-val').val(), 10);
    let rot = parseInt($el.find('.asset-rotation-val').val(), 10);
    $el.css({
        width: width + 'px',
        zIndex: z
    });
    $el.find('.asset-visual').css('transform', 'rotate(' + rot + 'deg)');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveJourneyAssetProperties',
            asset_id: id,
            width: width,
            z: z,
            rotation: rot,
            nonce: nonce
        },
        method: 'POST'
    });
}

function assetZUp(id) {
    let $z = $('#journey-asset-' + id + ' .asset-z-val');
    $z.val(parseInt($z.val(), 10) + 1);
    _saveAssetProperties(id);
}

function assetZDown(id) {
    let $z = $('#journey-asset-' + id + ' .asset-z-val');
    $z.val(Math.max(0, parseInt($z.val(), 10) - 1));
    _saveAssetProperties(id);
}

function startAssetRotate(event, id) {
    event.preventDefault();
    event.stopPropagation();
    let $el = $('#journey-asset-' + id);
    let $vis = $el.find('.asset-visual');
    let offset = $vis.offset();
    let centerX = offset.left + $vis.outerWidth() / 2;
    let centerY = offset.top + $vis.outerHeight() / 2;
    let initRot = parseInt($el.find('.asset-rotation-val').val(), 10) || 0;
    let startAng = Math.atan2(event.pageY - centerY, event.pageX - centerX) * 180 / Math.PI;

    $('body').addClass('asset-rotating');

    function onMove(e) {
        let angle = Math.atan2(e.pageY - centerY, e.pageX - centerX) * 180 / Math.PI;
        let newRot = Math.round((initRot + angle - startAng) % 360);
        $el.find('.asset-rotation-val').val(newRot);
        $vis.css('transform', 'rotate(' + newRot + 'deg)');
    }

    function onUp() {
        $('body').removeClass('asset-rotating');
        $(document).off('mousemove.assetRotate mouseup.assetRotate');
        _saveAssetProperties(id);
    }
    $(document).on('mousemove.assetRotate', onMove).on('mouseup.assetRotate', onUp);
}

function pickJourneyAssetImage(id) {
    let file_frame = wp.media({
        title: 'Select Graphic',
        button: {
            text: 'Use this image'
        },
        multiple: false
    });
    file_frame.on('select', function () {
        let url = file_frame.state().get('selection').first().toJSON().url;
        let $el = $('#journey-asset-' + id);
        let $vis = $el.find('.asset-visual');
        let nonce = $el.find('.asset-nonce').val();
        $vis.find('.asset-empty-placeholder').remove();
        if ($vis.find('.asset-img').length) {
            $vis.find('.asset-img').attr('src', url);
        } else {
            $vis.prepend('<img class="asset-img" src="' + url + '" alt="" draggable="false">');
        }
        $el.find('#journey-asset-img-' + id).val(url);
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'setJourneyAssetImage',
                asset_id: id,
                image: url,
                nonce: nonce
            },
            method: 'POST'
        });
    });
    file_frame.open();
}

function setAssetType(id, type) {
    let $el = $('#journey-asset-' + id);
    $el.attr('data-asset-type', type);
    $el.find('.asset-type-val').val(type);
    $el.find('.asset-type-btn').removeClass('active');
    $el.find('.asset-type-btn[data-type="' + type + '"]').addClass('active');
    // Show/hide the Set Image button
    if (type === 'graphic') {
        $el.find('.asset-graphic-only').show();
    } else {
        $el.find('.asset-graphic-only').hide();
    }
    // Update the visual preview
    let $vis = $el.find('.asset-visual');
    if (type === 'widget-status') {
        $vis.html('<div class="asset-widget-preview asset-widget-status-preview"><span class="icon icon-star"></span> Status Widget</div>');
    } else if (type === 'widget-leaderboard') {
        $vis.html('<div class="asset-widget-preview asset-widget-leaderboard-preview"><span class="icon icon-level"></span> Leaderboard Widget</div>');
    } else {
        let imgUrl = $el.find('#journey-asset-img-' + id).val();
        if (imgUrl) {
            $vis.html('<img class="asset-img" src="' + imgUrl + '" alt="" draggable="false">');
        } else {
            $vis.html('<div class="asset-empty-placeholder pointer-cursor" onclick="pickJourneyAssetImage(' + id + ')">Click to set graphic</div>');
        }
    }
    _saveAssetMeta(id);
}

function toggleAssetLink(id) {
    let $el = $('#journey-asset-' + id);
    let $row = $el.find('.asset-link-row');
    $row.toggle();
    if ($row.is(':visible')) {
        $row.find('.asset-link-input').focus();
    }
}

function saveAssetLink(id, url) {
    let $el = $('#journey-asset-' + id);
    $el.find('.asset-link-val').val(url);
    if (url) {
        $el.find('.asset-link-toggle').addClass('active');
    } else {
        $el.find('.asset-link-toggle').removeClass('active');
    }
    _saveAssetMeta(id);
}

function saveAssetTabi(id, tabiId) {
    let $el = $('#journey-asset-' + id);
    let nonce = $el.find('.asset-nonce').val();
    $el.find('.asset-tabi-val').val(tabiId);
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveJourneyAssetTabi',
            asset_id: id,
            tabi_id: tabiId,
            nonce: nonce
        },
        method: 'POST'
    });
}

function _saveAssetMeta(id) {
    let $el = $('#journey-asset-' + id);
    let nonce = $el.find('.asset-nonce').val();
    let type = $el.find('.asset-type-val').val();
    let link = $el.find('.asset-link-val').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveJourneyAssetMeta',
            asset_id: id,
            asset_type: type,
            asset_link: link,
            nonce: nonce
        },
        method: 'POST'
    });
}

function addJourneyAsset() {
    let nonce = $('#journey-asset-nonce').val();
    let adv_id = $('#builder-adventure-id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'addJourneyAsset',
            adventure_id: adv_id,
            nonce: nonce
        },
        method: 'POST',
        success: function (r) {
            let d = JSON.parse(r);
            if (d.success && d.html) {
                $('#builder').append(d.html);
                let $newAsset = $('#journey-asset-' + d.asset_id);
                $newAsset.draggable({
                    cancel: '.ui-resizable-handle, .asset-rotate-btn, .asset-link-input, .asset-controls',
                    start: function () {
                        $(this).addClass('dragging');
                    },
                    stop: function () {
                        let top = parseInt($(this).css('top'), 10);
                        let left = parseInt($(this).css('left'), 10);
                        let n = $(this).find('.asset-nonce').val();
                        jQuery.ajax({
                            url: runAJAX.ajaxurl,
                            data: {
                                action: 'saveJourneyAssetPosition',
                                asset_id: d.asset_id,
                                top: top,
                                left: left,
                                nonce: n
                            },
                            method: 'POST'
                        });
                        $(this).removeClass('dragging');
                    }
                }).resizable({
                    handles: 'se, e, s',
                    minWidth: 40,
                    stop: function (event, ui) {
                        $(this).css('height', '');
                        $(this).find('.asset-width-val').val(Math.round(ui.size.width));
                        _saveAssetProperties(d.asset_id);
                    }
                });
            }
        }
    });
}

function trashJourneyAsset(id) {
    let nonce = $('#journey-asset-' + id + ' .asset-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'trashJourneyAsset',
            asset_id: id,
            nonce: nonce
        },
        method: 'POST',
        success: function (r) {
            let d = JSON.parse(r);
            if (d.success) {
                $('#journey-asset-' + id).remove();
            }
        }
    });
}

function duplicateJourneyAsset(id) {
    let nonce = $('#journey-asset-' + id + ' .asset-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'duplicateJourneyAsset',
            asset_id: id,
            nonce: nonce
        },
        method: 'POST',
        success: function (r) {
            let d = JSON.parse(r);
            if (d.success && d.html) {
                $('#builder').append(d.html);
                $('#journey-asset-' + d.asset_id).draggable({
                    cancel: '.ui-resizable-handle, .asset-rotate-btn, .asset-link-input, .asset-controls',
                    start: function () {
                        $(this).addClass('dragging');
                    },
                    stop: function () {
                        let top = parseInt($(this).css('top'), 10);
                        let left = parseInt($(this).css('left'), 10);
                        let n = $(this).find('.asset-nonce').val();
                        jQuery.ajax({
                            url: runAJAX.ajaxurl,
                            data: {
                                action: 'saveJourneyAssetPosition',
                                asset_id: d.asset_id,
                                top: top,
                                left: left,
                                nonce: n
                            },
                            method: 'POST'
                        });
                        $(this).removeClass('dragging');
                    }
                }).resizable({
                    handles: 'se, e, s',
                    minWidth: 40,
                    stop: function (event, ui) {
                        $(this).css('height', '');
                        $(this).find('.asset-width-val').val(Math.round(ui.size.width));
                        _saveAssetProperties(d.asset_id);
                    }
                });
            }
        }
    });
}

function initializeBuilderTabis() {
    $('#builder .builder-tabi').draggable({
        cancel: '.ui-resizable-handle',
        start: function () {
            $(this).addClass('dragging');
        },
        stop: function () {
            updateTabiPosition($(this).data('tabi-id'));
            $(this).removeClass('dragging');
        }
    }).resizable({
        handles: 'se, e, s',
        class: 'br-resize-handle',
        minWidth: 80,
        minHeight: 60,
        stop: function (event, ui) {
            let id = $(this).data('tabi-id');
            _saveTabiSize(id, Math.round(ui.size.width), Math.round(ui.size.height));
        }
    });
}

function resetMilestonesToList() {
    $(`.milestone .milestone-data .z-pos`).val(0);
    $(`.milestone .milestone-data .rotation`).val(0);
    $(`.milestone`).css({
        'transform': `scale(1) rotate(0deg)`
    });
    let resetX = 50,
        resetY = 50;
    for (let i = 0; i <= $(`.milestone`).length; i++) {
        $(`.milestone.milestone-order-${i} .milestone-data input.left`).val(resetX);
        $(`.milestone.milestone-order-${i} .milestone-data input.top`).val(resetY);
        $(`.milestone.milestone-order-${i}`).css('left', resetX);
        $(`.milestone.milestone-order-${i}`).css('top', resetY);
        resetX += 150;
        if (resetX > 750) {
            resetX = 50;
            resetY += 150;
        }
    }
    $(`.milestone`).each(function () {
        updateMilestonePosition($(this).data('id'));
    });
    setTimeout(function () {
        $("#notify-message ul.content").html('');
    }, 30000);

}

function generateHexFilled(radius) {
    const results = [];

    for (let q = -radius; q <= radius; q++) {
        const r1 = Math.max(-radius, -q - radius);
        const r2 = Math.min(radius, -q + radius);
        for (let r = r1; r <= r2; r++) {
            results.push([q, r]);
        }
    }
    return results;
}

function resetMilestonePositions(groupby = 'data-color', spacing = 50, delayStep = 5, maxRowWidth = 2500, originOffset = 500) {
    const $milestones = $('.milestone');
    const groups = {
        'orange': [],
        'red': [],
        'pink': [],
        'purple': [],
        'deep-purple': [],
        'indigo': [],
        'blue': [],
        'light-blue': [],
        'cyan': [],
        'teal': [],
        'green': [],
        'light-green': [],
        'lime': [],
        'yellow': [],
        'amber': [],
        'deep-orange': [],
        'brown': [],
        'grey': [],
        'blue-grey': []
    };

    // 1. Group by color
    $milestones.each(function () {
        const $el = $(this);
        const color = $el.attr(groupby);
        if (!groups[color]) groups[color] = [];
        groups[color].push($el);
    });

    let offsetX = 0;
    let offsetY = 0;
    let groupCount = 0;

    for (let color in groups) {
        const group = groups[color];
        const centerIndex = Math.floor(group.length);
        const usedPositions = new Set();

        // Axial coordinates for beehive pattern
        const axialOffsets = [
            [0, 0],
            [1, 0],
            [0, 1],
            [-1, 1],
            [-1, 0],
            [0, -1],
            [1, -1],
            [2, 0],
            [1, 1],
            [0, 2],
            [-1, 2],
            [-2, 2],
            [-2, 1],
            [-2, 0],
            [-1, -1],
            [0, -2],
            [1, -2],
            [2, -2],
            [2, -1],
            [3, 0],
            [2, 1],
            [1, 2],
            [0, 3],
            [-1, 3],
            [-3, 1],
            [-3, 0],
            [-2, -1],
            [-1, -2],
            [0, -3],
            [1, -3],
            [3, -1],
            [4, 0],
            [3, 1],
            [2, 2],
            [1, 3],
            [0, 4],
            [-1, 4],
            [-2, 3],
            [-3, 2],
            [-4, 1],
            [-4, 0],
            [-3, -1],
            [-2, -2],
            [-1, -3],
            [0, -4],
            [1, -4],
            [2, -3],
            [3, -2],
            [4, -1],
            [5, 0],
            [4, 1],
            [3, 2],
            [2, 3],
            [1, 4],
            [0, 5],
            [-1, 5],
            [-2, 4],
            [-3, 3],
            [-4, 2],
            [-5, 1],
            [-5, 0],
            [-4, -1],
            [-3, -2],
            [-2, -3],
            [-1, -4],
            [0, -5],
            [1, -5],
            [2, -4],
            [3, -3],
            [4, -2],
            [5, -1],
            [6, 0],
            [5, 1],
            [4, 2],
            [3, 3],
            [2, 4],
            [1, 5],
            [0, 6],
            [-1, 6],
            [-2, 5],
            [-3, 4],
            [-4, 3],
            [-5, 2],
            [-6, 1],
            [-6, 0],
            [-5, -1],
            [-4, -2],
            [-3, -3],
            [-2, -4],
            [-1, -5],
            [0, -6],
            [1, -6],
            [2, -5],
            [3, -4],
            [4, -3],
            [5, -2],
            [6, -1],
            [7, 0],
            [6, 1],
            [5, 2],
            [4, 3],
            [3, 4],
            [2, 5],
            [1, 6],
            [0, 7],
            [-1, 7],
            [-2, 6],
            [-3, 5],
            [-4, 4],
            [-5, 3],
            [-6, 2],
            [-7, 1],
            [-7, 0],
            [-6, -1],
            [-5, -2],
            [-4, -3],
            [-3, -4],
            [-2, -5],
            [-1, -6],
            [0, -7],
            [1, -7],
            [2, -6],
            [3, -5],
            [4, -4],
            [5, -3],
            [6, -2],
            [7, -1],
            [8, 0],
            [7, 1],
            [6, 2],
            [5, 3],
            [4, 4],
            [3, 5],
            [2, 6],
            [1, 7],
            [0, 8],
            [-1, 8],
            [-2, 7],
            [-3, 6],
            [-4, 5],
            [-5, 4],
            [-6, 3],
            [-7, 2],
            [-8, 1],
            [-8, 0],
            [-7, -1],
            [-6, -2],
            [-5, -3],
            [-4, -4],
            [-3, -5],
            [-2, -6],
            [-1, -7],
            [0, -8]
        ];

        // Adjust group base position
        if (offsetX > maxRowWidth) {
            offsetX = 0;
            offsetY += spacing * 12;
        }

        for (let i = 0; i < group.length; i++) {
            const $m = group[i];
            const [q, r] = axialOffsets[i] || [Math.floor(i / 5), (i % 5)];

            // Convert axial to pixel (flat-topped hex layout)
            //const x = spacing * (q + r / 2) + offsetX + originOffset;
            //const y = spacing * (r * Math.sqrt(3) / 2) + offsetY + originOffset;

            // Convert axial to pixel (pointy-topped hex layout)
            const x = spacing * Math.sqrt(3) * q + offsetX + originOffset;
            const y = spacing * 2 * (r + q / 2) + offsetY + originOffset;
            console.log(`Milestone ${$m.data('id')} - Axial: (${q}, ${r}) => Pixel: (${x}, ${y})`);


            setTimeout(() => {
                $m.css({
                    left: `${x}px`,
                    top: `${y}px`
                });
                $('.axialcoords', $m).text(axialOffsets[i] ? `(${axialOffsets[i][0]}, ${axialOffsets[i][1]})` : '(0, 0)');

                $(`.milestone-data input.left`, $m).val(x);
                $(`.milestone-data input.top`, $m).val(y);



                updateMilestonePosition($m.data('id'));
            }, i * delayStep);
        }

        offsetX += spacing * 12;
        groupCount++;
    }
    setTimeout(function () {
        $("#notify-message ul.content").html('');
    }, 30000);

}




function applyTransformToMilestone(id) {
    let zVal = ($(`#milestone-${id} .milestone-data .z-pos`).val());
    if (zVal > 5) {
        scaleVal = 5;
    } else if (zVal < 1) {
        scaleVal = 1;
    }
    let baseWidth = 108;
    let baseHeight = 95;
    let scaledWidth = baseWidth * zVal;
    let scaledHeight = baseHeight * zVal;

    $(`#milestone-${id} .milestone-content`).css({
        'width': `${scaledWidth}px`,
        'height': `${scaledHeight}px`
    });
}

function milestoneReset(id) {
    $(`#milestone-${id} .milestone-data .z-pos`).val(1);
    applyTransformToMilestone(id)
    updateMilestonePosition(id);
}

function zFront(id) {
    let $zInput = $(`#milestone-${id} .milestone-data .z-pos`);

    if ($zInput.val() < 5) {
        $zInput.val(+$zInput.val() + 0.1);
    }
    applyTransformToMilestone(id)
    updateMilestonePosition(id);
}

function zBack(id) {
    let $zInput = $(`#milestone-${id} .milestone-data .z-pos`);
    if ($zInput.val() > 1) {
        $zInput.val(+$zInput.val() - 0.1);
    }
    applyTransformToMilestone(id)
    updateMilestonePosition(id);
}

function loadQuestCard(quest_id = 0) {
    showLoader();
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadQuestCard',
            quest_id: quest_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            $('#flipped-card-container').html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#flipped-card-container").addClass('active');
                $("#flipped-card-container .card").addClass('flipped');
                hideAllOverlay();
            }, 10);
        }
    });
}

function loadAchievementCard(achievement_id = 0) {
    showLoader();
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadAchievementCard',
            achievement_id: achievement_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            $('#flipped-card-container').html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#flipped-card-container").addClass('active');
                $("#flipped-card-container .card").addClass('flipped');
                hideAllOverlay();
            }, 10);
        }
    });
}

function displayAchievementCard(achievement_id = 0) {
    showLoader('small');
    $("#achievements-display").removeClass('loaded').addClass('loading');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'displayAchievementCard',
            achievement_id: achievement_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            $('#achievement-card-xp .end-value').val(data.achievement.achievement_xp);
            $('#achievement-card-bloo .end-value').val(data.achievement.achievement_bloo);
            $('#achievement-card-ep .end-value').val(data.achievement.achievement_ep);
            $('#achievements-display .achievement-card-badge').attr('style', 'background-image:url(' + data.achievement.achievement_badge + ');');
            if (data.achievement.achievement_display == 'rank') {
                $('#achievements-display .achievement-card-badge').attr('onDblClick', 'switchRank(' + data.achievement.achievement_id + ');');
                $('#achievements-display').addClass('achievement-rank');
            } else {
                $('#achievements-display .achievement-card-badge').attr('onDblClick', false);
                $('#achievements-display').removeClass('achievement-rank');
            }
            $('#achievements-display .achievement-card-badge .decor-border path').removeClass().addClass(data.achievement.achievement_color);
            $('#achievements-display .achievement-card-title').text(data.achievement.achievement_name);
            $('#achievements-display .achievement-card-message').html(data.achievement_content);
            $('#achievements-display .achievement-card-earned').text(data.achievement.achievement_earned);

            if ($('#achievement-card-actions')) {
                $('#achievement-card-actions a.edit-link').attr('href', $('#achievement-card-' + data.achievement.achievement_id + ' .achievement-data-link').val());
            }

            $('#achievement-card-' + data.achievement.achievement_id).addClass('active').siblings().removeClass('active');

            $("#achievements-display").addClass('loaded', function () {
                animateNumber('#achievement-card-xp, #achievement-card-bloo, #achievement-card-ep', 750);
                hideAllOverlay();
            });

            $("#notify-message ul.content").append(data.message);
            $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                $(this).remove();
                $("#achievements-display").removeClass('loading loaded');
            });
        }
    });
}

function isJson(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return false;
    }
}

function randomEncounter(enc_id = 0) {
    $('#overlay-content .content').html('');
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'randomEncounter',
            adventure_id: adventure_id,
            enc_id: enc_id
        }),
        method: "POST",
        success: function (data_received) {
            if (isJson(data_received)) {
                displayAjaxResponse(data_received);
            } else {
                $('#overlay-content .content').html(data_received);
            }
            hideAllOverlay();
            let flipTimeout = setTimeout(function () {
                $("#overlay-content").addClass('active');
            }, 100);
        }
    });
}

function loadStory() {
    $('#overlay-content .content').html('');
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadStory',
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            if (isJson(data_received)) {
                displayAjaxResponse(data_received);
            } else {
                $('#overlay-content .content').html(data_received);
            }
            hideAllOverlay();
            let flipTimeout = setTimeout(function () {
                $("#overlay-content").addClass('active');
            }, 100);
        }
    });
}


///////////////// Load Guild Card
function loadGuildCard(guild_id = 0) {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadGuildCard',
            guild_id: guild_id
        }),
        method: "POST",
        success: function (data_received) {
            $('#flipped-card-container').html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#flipped-card-container").addClass('active');
                $("#flipped-card-container .card").addClass('flipped');
                hideAllOverlay();
            }, 10);
        }
    });
}

function previewItem(id) {
    if (id == current_item_preview_id) {
        current_item_preview_id = 0;
        $('#hud-video-status-idle').addClass('active');
        $('.hud-screen-content').removeClass('active');
    } else {
        current_item_preview_id = id;
        $('.hud-screen-video, .hud-screen-content').removeClass('active');
        $('.hud-screen-content').addClass('flicker');

        $('#item-preview-screen .item-preview-name').text($('#item-data-' + id + ' .item-name').val());
        $('#item-preview-screen img.item-preview-image').attr('src', $('#item-data-' + id + ' .item-image').val());
        $('#item-preview-screen .item-preview-description').html($('#item-data-' + id + ' .item-description').html());
        $('#item-preview-buy-button').text($('#item-' + id + ' button.buy-item').text())
        if ($('#item-data-' + id + ' .item-id').val() > 0) {
            $('#item-preview-buy-button').attr('onClick', 'buyItem(' + $('#item-data-' + id + ' .item-id').val() + ')');
        } else {
            $('#item-preview-buy-button').attr('onClick', '');
        }
        $('#item-preview-screen .item-preview-type').removeClass('tabi-piece key consumable');
        $('#item-preview-screen .item-preview-type').text($('#item-data-' + id + ' .item-type-label').val());
        $('#item-preview-screen .item-preview-type').addClass($('#item-data-' + id + ' .item-type').val());
        setTimeout(function () {
            $('.hud-screen-content').removeClass('flicker').addClass('active');
        }, 500);
    }
}

function loadItemCard(item_id = 0) {
    if ($('#item-' + item_id)) {
        $('#item-' + item_id).siblings().removeClass("active");
        activate('#item-' + item_id);
    }
    let adventure_id = $('#the_adventure_id').val();

    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadItemCard',
            item_id: item_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            if (isJson(data_received)) {
                displayAjaxResponse(data_received);
            } else {
                $('#flipped-card-container').html(data_received);
                let flipTimeout = setTimeout(function () {
                    $("#flipped-card-container").addClass('active');
                    $("#flipped-card-container .card").addClass('flipped');
                    hideAllOverlay();
                }, 10);
            }
        }
    });
}

function loadBackpackItem(item_id = 0) {
    if ($('#item-' + item_id)) {
        $('#item-' + item_id).siblings().removeClass("active");
        activate('#item-' + item_id);
    }
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'loadBackpackItem',
            item_id: item_id
        }),
        method: "POST",
        success: function (data_received) {
            $('#flipped-card-container').html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#flipped-card-container").addClass('active');
                $("#flipped-card-container .card").addClass('flipped');
                hideAllOverlay();
            }, 10);
        }
    });
}

function loadLore(lore_id = 0) {
    if (lore_id > 0) {
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'loadLore',
                lore_id: lore_id
            }),
            method: "POST",
            success: function (data_received) {
                $('#main-loader  .main-loader-content').html(data_received);
                let flipTimeout = setTimeout(function () {
                    $("#main-container").addClass('opacity-60');
                    $("#main-loader").addClass('active');
                    hideAllOverlay();
                }, 10);
            }
        });
    }
}

function searchLore() {
    let search_string = $('#search').val();
    $("#lore-content").addClass('opacity-0');
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'searchLore',
            search_string: search_string,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            $('#lore-content').html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#lore-content").removeClass('opacity-0');
                hideAllOverlay();
            }, 10);
        }
    });
}

function unloadCard() {
    $('ul.cards li').removeClass("flipped");
    $("#flipped-card-container, #main-loader").removeClass('active');
    $("#flipped-card-container .card").removeClass('flipped');
    let clearFlipped = setTimeout(function () {
        $('#flipped-card-container, #main-loader .main-loader-content').html("")
    }, 300);
}

function clearMainLoader() {
    $("#main-container").removeClass('opacity-60');
    $("#main-loader").removeClass('active');
    let clearLoader = setTimeout(function () {
        $('#main-loader .main-loader-content').html("")
    }, 300);
}

function toggleSidebar(who) {
    if (!who) {
        $('.lateral-nav, .sidebar').removeClass('active');
    } else {
        $(who).toggleClass('active').siblings().removeClass('active');
    }

}

function loadSidebar(sidebar, filename, id) {
    if (!sidebar) {
        $('.sidebar').removeClass('active');
        $('.sidebar-asset').remove();
    } else {
        if ($(sidebar).hasClass('active')) {
            $(sidebar).removeClass('active');
        } else {
            animateScroll('#body');
            let adventure_id = $("#the_adventure_id").val();

            showLoader();
            jQuery.ajax({
                url: runAJAX.ajaxurl,
                data: ({
                    action: 'loadSidebar',
                    filename: filename,
                    adventure_id: adventure_id,
                    id: id
                }),
                method: "POST",
                success: function (data_received) {
                    $(sidebar).html(data_received);
                    $(sidebar).addClass('active');
                    hideAllOverlay();
                }
            });
        }
    }
}

function displayConfirmAction(who){
    if (!$(who).hasClass('active')) {
        $('.confirm-action-tooltip, .confirm-action, .stats-detail').removeClass('active');
        $(who).addClass('active');
    } else {
        $('.confirm-action-tooltip, .confirm-action, .stats-detail').removeClass('active');
    }
}
function showOverlay(who) {
    if (!$(who).hasClass('active')) {
        $('.confirm-action, .stats-detail').removeClass('active');
        $(who).addClass('active');
        let offset_width = $(who).offset().left + $(who).outerWidth();
        let window_width = $(window).width();
        let total = offset_width - window_width;
        if (total > 0) {
            let my_margin = -(total) + 'px';
            $(who).css({
                marginLeft: my_margin
            });
        }
    } else {
        hideAllOverlay();
    }
}

function setupAllOverlays() {
    let offset_width = $('.confirm-action').offset().left + $('.confirm-action').outerWidth();
    let window_width = $(window).width();
    let total = offset_width - window_width;
    if (total > 0) {
        let my_margin = -(total) + 'px';
        $('.confirm-action').css({
            marginLeft: my_margin
        });
    }
}

function hideAllOverlay() {
    $('.overlay-layer, #profile-box, .layer.overlay, .feedback, .layer.top-overlay').removeClass('active');
    $('.confirm-action').removeClass('active');
    $("#main-content, #footer").removeClass('fixed');
    if ($("#audio-funky").length) {
        $("#audio-funky").prop('volume', 0.1);
        $("#audio-funky").get(0).pause();
    }
    if ($('#start').hasClass('active')) {
        $('#start').removeClass('active');
        $('#start-button').removeClass('close');
        $('#taskbar').removeClass('start-active');
    }
    // The line above deactivates .overlay-layer, which the Conditions drawers
    // also carry - so this call may just have closed a drawer without going
    // through brCloseDrawer(). Put the DOM back in order rather than leaving a
    // backdrop over a drawer that is no longer there.
    if (typeof brSyncDrawerState === 'function') brSyncDrawerState();
}

function playSound(id) {
    $(id).prop('volume', 0.1);
    $(id).get(0).play();
}

function showLoader(type) {
    hideAllOverlay();
    if (type == 'small') {
        $('#small-loader').addClass('active');
    } else {
        $('#loader').addClass('active');
        $('.overlay-bg').addClass('active');
    }
}

function toggleSetting(id) {
    $(id + " .toggle-button").toggleClass('active');
    if ($(id + " .toggle-button").hasClass('active')) {
        $(id + " .setting-value").val(1);
    } else {
        $(id + " .setting-value").val(0);
    }
}

function allToggleButtonsOn(tab) {
    $(tab + " .toggle-button").addClass('active');
    $(tab + " .setting-value.radio-setting-value").val(1);
}

function allToggleButtonsOff(tab) {
    $(tab + " .toggle-button").removeClass('active');
    $(tab + " .setting-value.radio-setting-value").val(0);
}

function flipMilestone(id) {
    if (id) {
        $("#milestone-" + id).toggleClass("flipped").siblings().removeClass("flipped");
        let divOffsetTop = $("#milestone-" + id).offset().top - 120;
        $("html, body").animate({
            scrollTop: divOffsetTop
        }, 300);
    }
}

function flipLibraryCard(id) {
    if (id) {
        $(id).toggleClass("flipped").siblings().removeClass("flipped");
        let divOffsetTop = $(id + " .card-content").offset().top - 120;
        $("html, body").animate({
            scrollTop: divOffsetTop
        }, 300);
    }
}


////////////////////////////////////////// FORMS FUNCTIONALITY ////////////////////////////////////////////

function setItemType(type) {
    $("#the_item_type").val(type);
    $("button.item-type-choice, button.item-type-choice svg.icon-image").removeClass("active");
    $(`#button-${type}, #button-${type} svg.icon-image`).addClass("active");
    $('.cond-opt').prop('disabled', true);
    $('.cond-opt-' + type).prop('disabled', false);
}

function activateClass(class_on = "", class_off = "") {
    $(class_off).removeClass("active");
    $(class_on).addClass("active");
}

function countdown() {
    let time_left = $("#timer").html();
    let time_limit = $('#the_time_limit').val();
    let perc = Math.round(time_left / time_limit * 100);
    $('#challenge-timer .progress').css('width', perc + '%');
    if (time_left > 0) {
        time_left--;
        $("#timer").html(time_left);
        if (time_left <= 30 && time_left > 9) {
            //$('#countdown-sfx').get(0).play();
            $('#challenge-timer .progress').addClass("warning");
            $('')
        } else if (time_left <= 9) {
            //$('#countdown-sfx').get(0).play();
            $('#challenge-timer .progress').removeClass('warn').addClass("danger");
        }
        setTimeout(countdown, 1000);
    } else {
        $("#times-up").fadeIn(1500);
        $('#challenge-timer .progress').removeClass('warning danger').addClass("dead");
        //$('#buzzer-sfx').get(0).play();
    }
}

function checkPath() {
    $('.conditional-display').hide();
    if ($('#the_achievement_display').val() == 'badge') {
        $('.badge-display').show();
    } else if ($('#the_achievement_display').val() == 'path') {
        $('.path-display').show();
    } else if ($('#the_achievement_display').val() == 'rank') {
        $('.rank-display').show();
        $("#the_achievement_code, #the_achievement_xp, #the_achievement_bloo, #the_achievement_max, #magic-link").val('');
        $("#the_achievement_path").val(0);
    }
}

function objectiveCheck(obj_id, quest_id) {
    let keyword = $("#keyword-input-" + objective_id).val();
}

function factCheck(objective_id) {
    let keyword = $("#keyword-input-" + objective_id).val();
    if (keyword) {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'factCheck',
                objective_id: objective_id,
                keyword: keyword,
                quest_id: $('#the_quest_id').val(),
                adventure_id: $("#the_adventure_id").val()
            }),
            method: "POST",
            success: function (data_received) {
                let objective = JSON.parse(data_received);
                if (objective.no_energy == true) {
                    $("#feedback .content").html(objective.message);
                    $("#feedback").addClass('active');
                    $('.loader, .small-loader').removeClass('active');
                    $("#feedback").click(function () {
                        hideAllOverlay();
                    });
                } else {
                    $("#notify-message ul.content").append(objective.message);
                    $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                        $(this).remove();
                    });
                    if (objective.success) {
                        completed_objectives++;
                        total_objectives--;
                        createProgressionChart(completed_objectives, total_objectives, '#mission-status-chart');
                        insertSolvedObjective(objective_id);
                        $("#keyword-input-" + objective_id).removeClass('red-bg-400 white-color').addClass('lime-bg-500 blue-grey-900').attr('disabled', true);
                        $("#feedback .content").html(objective.feedback);
                        $("#feedback").addClass('active');
                        let feedbackTimeout = setTimeout(function () {
                            $("#feedback .content .objective-success-message").addClass('active');
                        }, 500);

                        $('.loader, .small-loader').removeClass('active');
                        $("#feedback").click(function () {
                            $("#feedback .content .objective-success-message").removeClass('active');
                            hideAllOverlay();
                        });
                    } else {
                        $("#keyword-input-" + objective_id).addClass('red-bg-400 white-color');
                    }

                }
            }
        });
    } else {
        $("#keyword-input-" + objective_id).removeClass('red-bg-400 white-color lime-bg-500 blue-grey-900');
    }
}

function insertSolvedObjective(id) {
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'insertSolvedObjective',
            id: id
        }),
        method: "POST",
        success: function (data_received) {
            if (data_received) {
                $("#keyword-card-" + id).html(data_received);
            } else {
                alert('No file found!');
            }
        }
    });

}

function setCurrentSlide(id) {
    let totalSlides = $('.slide').length;
    for (let i = 0; i < id; i++) {
        $(".slide-" + i).removeClass('active next').addClass('prev');
    }
    for (let i = id; i <= totalSlides; i++) {
        $(".slide-" + i).removeClass('active prev').addClass('next');
    }
    $(".slide-" + id).removeClass('next prev').addClass('active');
}


function checkRequirements(level) {
    if ($("#the_quest_type").val() == "mission") {
        $("li.type-mission").hide();
        level = 99;
    }
    let i;
    for (i = 0; i <= level; i++) {
        $("li.level-" + i).show();
    }
    for (i > level; i <= 100; i++) {
        $("li.level-" + i).hide().removeClass("active");
    }
    $("#the_quest_xp").prop('disabled', false);
}

function spinUp(who, max = 99) {
    let number = $(who).val();
    if (number < max) {
        number++;
        $(who).val(number);
    }
    checkRequirements(number);
}

function spinDown(who, min = 1) {
    let number = $(who).val();
    if (number > min) {
        number--;
        $(who).val(number);
    }
    checkRequirements(number);
}

function checkLevel(who) {
    let number = Number($(who).val());
    if (number > 99) {
        $(who).val(99);
    } else if (number < 1) {
        $(who).val(1);
    }
    checkRequirements(number);
}

function reorder() {
    let adventure_id = $("#the_adventure_id").val();
    let the_order = [];
    $("#table-quest .row-container .row .quest-id").each(function () {
        the_order.push($(this).val());
    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reorder',
            adventure_id: adventure_id,
            the_order: the_order
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function reorderItems(who) {
    let adventure_id = $("#the_adventure_id").val();
    let the_order = [];
    $(who + " tbody tr .item-id").each(function () {
        the_order.push($(this).val());
    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reorderItems',
            adventure_id: adventure_id,
            the_order: the_order
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });

}

function reorderAchievements(who) {
    let adventure_id = $("#the_adventure_id").val();
    let the_order = [];
    $(who + " tbody tr .achievement-id").each(function () {
        the_order.push($(this).val());
    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reorderAchievements',
            adventure_id: adventure_id,
            the_order: the_order
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });

}

function updateSchedule() {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateSchedule'
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function updatePlayer(adventure_id, player_id) {
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updatePlayer',
            adventure_id: adventure_id,
            player_id: player_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function activateReorder(who) {
    $(who).addClass('sortable');
    $('.reorder-actions').removeClass('hidden');
    $('.default-actions').addClass('hidden');
    $(".sortable").sortable({
        update: function (event, ui) {

        }
    });
    $(".sortable").disableSelection();

}

function deactivateReorder(who) {
    $(who).sortable("destroy").removeClass('sortable');
    $(who + " li").removeClass('ui-state-default');
    $('.default-actions').removeClass('hidden');
    $('.reorder-actions').addClass('hidden');
}

function activateMilestone(id = null, sound_on = null, sound_off = null) {
    if (id) {
        let mi = $('#milestone-' + id);
        let miContainer = $('#milestone-container-' + id);
        if (mi.hasClass('active')) {
            if (sound_off) {
                playSound(sound_off);
            }
            $('#the-journey').removeClass('milestone-on');
        } else {
            if (sound_on) {
                playSound(sound_on);
            }
            $('#the-journey').addClass('milestone-on');
        }

        $(`#the-journey .milestone:not(#milestone-${id})`).removeClass('active');
        $(`#the-journey .milestone-container:not(#milestone-container-${id})`).removeClass('baseZ');
        mi.toggleClass('active');

        if (mi.hasClass('active')) {
            $('#milestone-preview').attr({
                'class': 'milestone-preview'
            });
            $('#milestone-preview-bg').attr({
                'class': 'milestone-preview-bg'
            });
            $('#the-journey .milestone').addClass('inactive');
            mi.removeClass('inactive');

            miContainer.addClass('baseZ');

            /// FILL PREVIEW

            let preview_data = {
                'badge': $(`#milestone-${id} .milestone-data-bg`).val(),
                'title': $(`#milestone-${id} .milestone-data-title`).val(),
                'xp': $(`#milestone-${id} .milestone-data-xp`).val(),
                'bloo': $(`#milestone-${id} .milestone-data-bloo`).val(),
                'ep': $(`#milestone-${id} .milestone-data-ep`).val(),
                'level': $(`#milestone-${id} .milestone-data-level`).val(),
                'color': $(`#milestone-${id} .milestone-data-color`).val(),
                'type': $(`#milestone-${id} .milestone-data-type`).val(),
            }
            $('#milestone-preview-bg').attr('style', 'background-image:url(' + preview_data.badge + '); background-color:' + preview_data.color + ';');
            $('#milestone-preview').addClass('active ' + preview_data.type);
            $('#milestone-preview-bg').addClass('active');
            $('#milestone-preview .milestone-preview-content').html($('#milestone-' + id + ' .milestone-cta').html());
            $('#milestone-preview .milestone-preview-xp .end-value').val(preview_data.xp);
            $('#milestone-preview .milestone-preview-ep .end-value').val(preview_data.ep);
            $('#milestone-preview .milestone-preview-bloo .end-value').val(preview_data.bloo);
            animateNumber('#milestone-preview-xp', 750);
            animateNumber('#milestone-preview-bloo', 750);
            if ($('#milestone-preview-ep')) {
                animateNumber('#milestone-preview-ep', 750);
            }

        } else {
            $('#the-journey .milestone').removeClass('inactive');
            mi.removeClass('inactive');
            miContainer.removeClass('baseZ');
            $('#milestone-preview-bg').attr({
                'style': 'background-image:url();',
                'class': 'milestone-preview-bg'
            });
            $('#milestone-preview').attr({
                'class': 'milestone-preview'
            });
            $('#milestone-preview .milestone-preview-content').html('');
            $('#milestone-preview .milestone-preview-xp .end-value').val(0);
            $('#milestone-preview .milestone-preview-ep .end-value').val(0);
            $('#milestone-preview .milestone-preview-bloo .end-value').val(0);
            /// EMPTY PREVIEW
        }
        // if(scroll){
        // 	animateScroll(mi,1, 35);
        // }

    } else {
        $('#the-journey .milestone').removeClass('inactive active');
        $('#milestone-preview-bg').attr({
            'style': 'background-image:url();',
            'class': 'milestone-preview-bg'
        });
        $('#milestone-preview').attr({
            'class': 'milestone-preview'
        });
        $('#milestone-preview .milestone-preview-content').html('');
        $('#milestone-preview .milestone-preview-xp .end-value').val(0);
        $('#milestone-preview .milestone-preview-ep .end-value').val(0);
        $('#milestone-preview .milestone-preview-bloo .end-value').val(0);
        if ($('#the-journey').hasClass('milestone-on')) {
            playSound('#ui-touch-milestone-reverse');
            $('#the-journey').removeClass('milestone-on');
        }
    }
}


function playBGVideo(who = null) {
    if (who) {
        if ($(who).get(0).paused) {
            $(who).get(0).play();
        } else {
            $(who).get(0).pause();
        }
    } else {
        if ($('#main-background-video').get(0).paused) {
            $('#main-background-video').get(0).play();
        } else {
            $('#main-background-video').get(0).pause();
        }
    }
}

function activateStartMenu() {
    $('#start').toggleClass('active');
    if ($('#start').hasClass('active')) {
        $('#start-button').addClass('close');
        $('#taskbar').addClass('start-active');
    } else {
        $('#start-button').removeClass('close');
        $('#taskbar').removeClass('start-active');
    }
}

function activate(who, scroll = null, solo = null) {
    if (solo) {
        $(who).toggleClass('active');
    } else {
        $(who).siblings().removeClass('active');
        $(who).toggleClass('active');
        if ($(who).hasClass('active')) {
            $(who).siblings().addClass('inactive');
            $(who).removeClass('inactive');
        } else {
            $(who).siblings().removeClass('inactive');
            $(who).removeClass('inactive');
        }
    }
    if (scroll) {
        animateScroll(who, 1, 35);
    }
}


function reorderQuestions(who) {
    let the_order = [];
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    $(who + " li.question input.question-id-value").each(function () {
        the_order.push($(this).val());
    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reorderQuestions',
            the_order: the_order
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });

}

function toggleAllGrades(id) {
    $(".cell").removeClass('show-grade-col show-grade-row');
    $(".cell").toggleClass('show-grade');
}

function toggleColGrades(id) {
    $(".cell").removeClass('show-grade');
    $(".column-" + id).toggleClass('show-grade-col');
}

function toggleRowGrades(id) {
    $(".cell").removeClass('show-grade');
    $(".row-" + id).toggleClass('show-grade-row');
}


///////////////////////// Survey QUESTIONS //////////////////////////
function clearImage(id, updateType, q_id) {
    if ($(id).is('img')) {
        $(id).fadeOut('fast', function () {
            $(id).attr('src', '').parent().removeClass('full').addClass('empty');
            $(id).fadeIn(300);
            if (updateType && q_id) {
                updateQuestion(updateType, q_id);
            }
        });
    } else {
        $(id).val(0);
        $(id + "_thumb").css("background-image", "");
        $(id + "_thumb_video source").removeAttr('src');
        $(id + "_thumb_video").removeClass('active');
        $(id + "_thumb_video")[0].load();

    }
}


////////////////////////////// newUniqueAchievementCode ///////////////////////////
function newUniqueAchievementCode(achievement_id) {
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'newUniqueAchievementCode',
            achievement_id: achievement_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
////////////////////////////// newUniqueAchievementCode ///////////////////////////
function deleteAchievementCode(code_id) {
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'deleteAchievementCode',
            code_id: code_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
////////////////////////////// ADD QUESTIONS ///////////////////////////
function addQuestion(type, style) {
    let id = $('#the_' + type + '_id').val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'addQuestion',
            type: type,
            id: id,
            style: style,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            if (data_received) {
                $('.questions').append(data_received);
                data_received = '';
                animateScroll('#questions-bottom', 1, -300);
                $('#small-loader').removeClass('active');
            } else {
                alert('No file found!');
            }
            hideAllOverlay();
        }
    });
}

function duplicateQuestion(q_id, type) {
    let main_id = $('#the_' + type + '_id').val();
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'duplicateQuestion',
            type: type,
            q_id: q_id,
            main_id: main_id,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            if (data_received) {
                $('#questions').append(data_received);
                data_received = '';
            } else {
                alert('No file found!');
            }
            hideAllOverlay();
        }
    });
}

function updateQuestion(type, id) {
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    let q_text = $("#question-text-" + id).val();
    let q_image = $("#question-" + id + "-img").val();


    let q_description = $("#question-description-" + id).val();
    let q_range = $("#question-range-" + id).val();
    let q_display = $("#question-display-" + id).val();

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateQuestion',
            type: type,
            id: id,
            q_text: q_text,
            q_image: q_image,
            q_description: q_description,
            q_range: q_range,
            q_display: q_display,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function removeQuestion(id, type) {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    let nonce = $('#delete-question-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'removeQuestion',
            id: id,
            nonce: nonce,
            type: type,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.success) {
                if (data.just_notify) {
                    $("#notify-message .content").append(data.message);
                    $("#notify-message").show();
                    $("#notify-message").delay(1000).fadeOut(300, function () {
                        $("#notify-message .content").html('');
                    });
                }
                $("#question-" + id).fadeOut('fast', function () {
                    if ($('#accordion-tab-question-' + id)) {
                        $('#accordion-tab-question-' + id).remove();
                    }
                    $("#question-" + id).remove();
                });
                hideAllOverlay();
            }
        }
    });
}

function addOption(type, q_id) {
    let main_id = $('#the_' + type + '_id').val();
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'addOption',
            type: type,
            q_id: q_id,
            main_id: main_id,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            if (data_received) {
                $("#question-" + q_id + ' .question-options').append(data_received);
                data_received = '';
            } else {
                alert('No file found!');
            }
            hideAllOverlay();
        }
    });
}
//////////////////////////// UPDATE OPTION ON CHANGE
function updateOption(type, q_id, option_id) {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    let main_id = $('#the_' + type + '_id').val();
    let o_text = $("#option-text-" + option_id).val();
    let o_image = $("#option-image-" + option_id).val();
    let o_correct = $("#option-" + option_id + " .option-correct").val();

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateOption',
            type: type,
            q_id: q_id,
            main_id: main_id,
            option_id: option_id,
            o_text: o_text,
            o_image: o_image,
            o_correct: o_correct,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function removeOption(id, type) {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    let nonce = $('#delete-option-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'removeOption',
            id: id,
            nonce: nonce,
            type: type,
            adventure_id: adventure_id,
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.success) {
                if (data.just_notify) {
                    $("#notify-message .content").append(data.message);
                    $("#notify-message").show();
                    $("#notify-message").delay(1000).fadeOut(300, function () {
                        $("#notify-message .content").html('');
                    });
                }
                $("#option-" + id).fadeOut('fast', function () {
                    $("#option-" + id).remove();
                });
                hideAllOverlay();
            }
        }
    });
}

function updateQuestionValue(who, value) {
    $('#question-answer-value-' + who).val(value);
    if (value === 0) {
        $('#question-' + who + ' .star').removeClass('active');
        $('#question-' + who + ' .star-0').addClass('active');
    } else {
        $('#question-' + who + ' .star').removeClass('active');
        for (let i = 1; i <= value; i++) {
            $('#question-' + who + ' .star-' + i).addClass('active');
        }
    }
    submitSurveyAnswer(who);
}

function prepareMultiChoiceValue(who, opt_toggle) {

    $(opt_toggle).toggleClass('active');

    let answer_values = [];
    $('#question-' + who + ' .option.active').each(function (index, element) {
        answer_values.push($('.option-value', this).val());
    });

    $('#question-answer-value-' + who).val(answer_values);
    submitSurveyAnswer(who);
}

function fakeSubmit() {
    let adventure_id = $("#the_adventure_id").val();
    let url = $("#bloginfo_url").val();
    $("#feedback .content").html("<h1>Answers submitted!</h1>");
    $("#feedback").addClass('active');
    if (!$('.overlay-bg').is(':visible')) {
        $('.overlay-bg').fadeIn('fast');
    }
    $("#feedback").click(function () {
        document.location.href = url + "/adventure/?adventure_id=" + adventure_id;
    });
}

function submitSurveyAnswer(question_id, option_id = 0, style = "") {
    showLoader("small");
    let survey_id = $('#the_survey_id').val();
    let adventure_id = $('#the_adventure_id').val();
    let value = $('#question-answer-value-' + question_id).val();
    let send_answer = false;
    if (option_id > 0) {
        if ($("#option-" + style + option_id).hasClass('active')) {
            send_answer = false;
        } else {
            send_answer = true;
        }
    } else {
        send_answer = true;
    }
    if (send_answer) {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'submitSurveyAnswer',
                question_id: question_id,
                option_id: option_id,
                survey_id: survey_id,
                value: value,
                adventure_id: adventure_id
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
                $("#question-" + question_id).addClass('answered');
                if (option_id) {
                    $("#option-" + style + option_id).siblings().removeClass('active');
                    $("#option-" + style + option_id).addClass('active');
                }
            }
        });
    } else {
        hideAllOverlay();
    }
}


////////////////////////////// STEP SORTABLE HELPERS ///////////////////////////
function brRenumberSteps() {
    $('#steps-list > .br-step-item').each(function(i) {
        $(this).find('.br-step-order').first().text(i + 1);
    });
}

function brInitStepSortable() {
    var $sl = $('#steps-list');
    if (!$sl.length || !$.fn.sortable) return;
    try { $sl.sortable('destroy'); } catch(e) {}
    $sl.sortable({
        items: '> .br-step-item',
        handle: '.br-step-row',
        placeholder: 'ui-sortable-placeholder',
        helper: 'clone',
        tolerance: 'pointer',
        start: function(e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        update: function() {
            brRenumberSteps();
            reorderSteps();
        }
    }).disableSelection();
}

////////////////////////////// ADD STEP ///////////////////////////
function addStep(id_to_duplicate = null) {
    let quest_id = $('#the_quest_id').val();
    let adventure_id = $("#the_adventure_id").val();
    if (quest_id) {
        showLoader("small");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'addStep',
                quest_id: quest_id,
                adventure_id: adventure_id,
                id_to_duplicate: id_to_duplicate
            }),
            method: "POST",
            success: function (data_received) {
                if (isJson(data_received)) {
                    displayAjaxResponse(data_received);
                } else {
                    $('#steps-list').append(data_received);
                    $('#no-steps-label').hide();
                    brInitStepSortable();
                    brRenumberSteps();
                    let new_step_id = $('#steps-list .br-step-item:last-child input.the_step_id_val').val();
                    editStep(new_step_id);
                    data_received = '';
                }
                if (id_to_duplicate) {
                    reorderSteps();
                }
                hideAllOverlay();
            }
        });
    } else {
        $("#notify-message ul.content").append($('#msg-save-first').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
            $(this).remove();
            hideAllOverlay();
        });
    }
}
////////////////////////////// LOAD STEP ///////////////////////////
////////////////////////////// SHARED DRAWERS ///////////////////////////
//
// Every full-screen drawer/modal in the app (Step editor, the Conditions
// drawers, the Stats drill-downs, the guild roster, the AI verdict) is
// position:fixed with z-index 300, sitting above a shared backdrop at 250.
// That comparison only means anything when the two are in the SAME stacking
// context, and they usually weren't: .main-content is `position:relative;
// z-index:3`, which makes it a stacking context, so a drawer authored inside
// the page was painted as part of that subtree while a backdrop appended to
// <body> sat in the root context. The backdrop's 250 then beat the drawer's
// 300 no matter how high the drawer's number went - it covered the drawer,
// swallowed its clicks and stopped it scrolling.
//
// Chasing that with per-caller `.parent()` arguments only fixed backdrop
// vs. drawer; the drawer was still trapped under anything painted above
// .main-content, and inside a transformed ancestor (jQuery UI Sortable drag
// helpers, animations) position:fixed stops resolving against the viewport
// altogether, so the drawer is mispositioned as well as mislayered.
//
// So the drawer itself is moved to <body> when it opens and put back where it
// was when it closes. Both drawer and backdrop then live in the root stacking
// context, which is the only place a fixed-position full-screen element can be
// reasoned about. Any new drawer gets this for free by going through
// brOpenDrawer()/brCloseDrawer() - there is nothing per-drawer to remember.

// One selector listing every drawer family, so "is anything still open" and
// "close whatever is open" can never drift apart from each other.
var BR_DRAWER_OPEN_SELECTOR = '.br-step-accordion.open, .tabi-conditions-overlay.active, '
    + '.item-conditions-overlay.active, .quest-conditions-overlay.active, '
    + '.guild-roster-overlay.active, #achievement-detail-overlay.active, '
    + '#item-detail-overlay.active, #ai-validate-overlay.active, #br-rewards-overlay.active';

// Moves a drawer to <body>, leaving a comment node behind to mark where it
// belongs. Idempotent: re-opening an already-portaled drawer does nothing.
function brPortalDrawer($drawer) {
    var el = $drawer && $drawer.length ? $drawer[0] : null;
    if (!el || !el.parentNode || el.parentNode === document.body) return;
    var marker = document.createComment('br-drawer-home');
    el.parentNode.insertBefore(marker, el);
    $drawer.data('brDrawerMarker', marker);
    document.body.appendChild(el);
}

// Puts it back, so the page's own markup is unchanged once the drawer closes
// and anything walking the DOM (or a later re-render of that row) still finds
// the element where the template put it.
function brRestoreDrawer($drawer) {
    var el = $drawer && $drawer.length ? $drawer[0] : null;
    if (!el) return;
    var marker = $drawer.data('brDrawerMarker');
    if (marker && marker.parentNode) {
        marker.parentNode.insertBefore(el, marker);
        marker.parentNode.removeChild(marker);
    }
    $drawer.removeData('brDrawerMarker');
}

function brOpenDrawer($drawer, activeClass) {
    brPortalDrawer($drawer);
    $drawer.addClass(activeClass || 'active');
    brShowDrawerBackdrop();
}

function brCloseDrawer($drawer, activeClass) {
    $drawer.removeClass(activeClass || 'active');
    brRestoreDrawer($drawer);
    brHideDrawerBackdrop();
}

// The backdrop always lives on <body>, because every drawer it backs is there
// too by the time it is shown.
function brShowDrawerBackdrop() {
    var $backdrop = $('#br-drawer-backdrop');
    if (!$backdrop.length) {
        $backdrop = $('<div class="br-drawer-backdrop" id="br-drawer-backdrop" onclick="brCloseTopDrawer();"></div>');
    }
    $('body').append($backdrop);
    $('body').addClass('br-drawer-open');
}

function brHideDrawerBackdrop() {
    if (!$(BR_DRAWER_OPEN_SELECTOR).length) {
        $('body').removeClass('br-drawer-open');
    }
}

// Reconciles drawer state with whatever the DOM actually says.
//
// The Conditions drawers also carry .overlay-layer, so hideAllOverlay() - which
// fires after most AJAX responses - strips .active off them directly, behind
// these helpers' backs. Before, that left the backdrop up over a drawer that had
// already disappeared; now it would also leave the element parked on <body>.
// Calling this puts any drawer that is no longer open back where it belongs and
// re-evaluates the backdrop, so no path can leave the two out of step.
function brSyncDrawerState() {
    $('.br-step-accordion, .overlay-layer, .tabi-modal').each(function () {
        var $el = $(this);
        if (!$el.data('brDrawerMarker')) return;
        if ($el.hasClass('active') || $el.hasClass('open')) return;
        brRestoreDrawer($el);
    });
    brHideDrawerBackdrop();
}

// Closes every drawer/modal in the app that rides the shared backdrop (plus
// the Tabi modal, which doesn't use the backdrop but is the same "escape
// hatch" concept) - the single place the ESC handler and backdrop-click both
// call, so no drawer family gets missed if a new one is added later.
function brCloseTopDrawer() {
    $('.br-step-accordion.open').each(function () {
        closeStepAccordion(this.id.replace('step-accordion-', ''));
    });
    $('.tabi-conditions-overlay.active').each(function () {
        closeTabiConditionsModal(this.id.replace('tabi-conditions-overlay-', ''));
    });
    if ($('#item-conditions-overlay').hasClass('active')) { closeItemConditionsModal(); }
    if ($('#quest-conditions-overlay').hasClass('active')) { closeQuestConditionsModal(); }
    if ($('#guild-roster-overlay').hasClass('active')) { closeGuildRoster(); }
    if (typeof closeAchievementDetail === 'function' && $('#achievement-detail-overlay').hasClass('active')) { closeAchievementDetail(); }
    if (typeof closeItemDetail === 'function' && $('#item-detail-overlay').hasClass('active')) { closeItemDetail(); }
    if ($('#ai-validate-overlay').hasClass('active')) { closeAiValidateModal(); }
    if ($('#br-rewards-overlay').hasClass('active')) { claimRewards(); }
    closeTabiModal();
}

function editStep(step_id) {
    if (!step_id) {
        notification('#msg-no-id', 1000);
        return;
    }
    var $accordion = $('#step-accordion-' + step_id);
    if ($accordion.hasClass('open')) {
        closeStepAccordion(step_id);
        return;
    }
    $('.br-step-accordion.open').each(function() {
        var openId = this.id.replace('step-accordion-', '');
        closeStepAccordion(openId);
    });
    var adventure_id = $("#the_adventure_id").val();
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({ action: 'editStep', step_id: step_id, adventure_id: adventure_id }),
        method: "POST",
        success: function (data_received) {
            // Portal before the markup goes in, not after: the response carries
            // wp_editor output whose inline script boots TinyMCE, and moving a
            // live editor's iframe across the DOM re-creates it and loses its
            // contents. Injecting into the element after it has reached its
            // final home means the editor is only ever built once, in place.
            brPortalDrawer($accordion);
            $accordion.html(data_received);
            brOpenDrawer($accordion, 'open');
            $('#step-' + step_id + ' .br-step-edit-btn').addClass('active');
            $('.loader, .small-loader').removeClass('active');
        }
    });
}

function closeStepAccordion(step_id) {
    var editorId = 'step-content-' + step_id;
    if (typeof tinymce !== 'undefined') {
        try { tinymce.remove('#' + editorId); } catch(e) {}
    }
    var $accordion = $('#step-accordion-' + step_id);
    $accordion.html('');
    brCloseDrawer($accordion, 'open');
    $('#step-' + step_id + ' .br-step-edit-btn').removeClass('active');
}

////////////////////////////// UPDATE STEP ///////////////////////////
function brCollectStepSettings(sid) {
    var skin = $('#step-skin-' + sid).val();
    var settings = {};
    var step_correct = null;

    switch (skin) {
        case 'dialogue': case 'system':
            break;
        case 'audio':
            settings = { url: $('#step-audio-url-' + sid).val() };
            break;
        case 'gallery':
            var imgs = [];
            $('#step-gallery-' + sid + ' .gallery-image-url').each(function() { imgs.push($(this).val()); });
            settings = { images: imgs, layout: 'auto' };
            break;
        case 'find_item':
            settings = { item_id: $('#step-find-item-' + sid).val(), message: '' };
            break;
        case 'multiple_choice':
            var opts = [], correct = [];
            $('#step-mc-options-' + sid + ' .br-option-row').each(function() {
                var oid = $(this).find('.mc-option-id').val();
                opts.push({ id: oid, text: $(this).find('.mc-option-text').val(), image: null, correct: $(this).find('.mc-correct').is(':checked') });
                if ($(this).find('.mc-correct').is(':checked')) correct.push(oid);
            });
            settings = { question: $('#step-mc-question-' + sid).val(), question_image: $('#step-mc-image-' + sid).val(), options: opts, allow_multiple: !!parseInt($('#step-mc-multi-' + sid).val()) };
            step_correct = JSON.stringify(correct);
            break;
        case 'keyphrase': case 'cryptex':
            var raw = $('#step-kp-answers-' + sid).val();
            var answers = raw.split(',').map(function(s) { return s.trim(); }).filter(Boolean);
            settings = { prompt: $('#step-kp-prompt-' + sid).val(), case_sensitive: !!parseInt($('#step-kp-case-' + sid).val()), trim_whitespace: true };
            if (skin === 'cryptex') settings.wheel_count = parseInt($('#step-cryptex-wheels-' + sid).val()) || 7;
            step_correct = JSON.stringify(answers);
            break;
        case 'puzzle':
            settings = { image: $('#step-puzzle-image-' + sid).val(), cols: parseInt($('#step-puzzle-cols-' + sid).val()) || 3, rows: parseInt($('#step-puzzle-rows-' + sid).val()) || 3 };
            break;
        case 'scorm':
            var scormUrl = $('#scorm-launch-url-' + sid).val();
            if (scormUrl) settings = { scorm_launch_url: scormUrl };
            break;
        case 'case_study_html':
            settings = {
                launch_url: $('#cs-launch-url-' + sid).val(),
                pass_score: parseInt($('#cs-pass-score-' + sid).val()) || 14,
                total: parseInt($('#cs-total-' + sid).val()) || 20
            };
            break;
        case 'backpack_item':
            var itemId = $('#step-bi-item-' + sid).val();
            settings = { prompt: $('#step-bi-prompt-' + sid).val(), item_id: itemId, consume_item: !!parseInt($('#step-bi-consume-' + sid).val()) };
            step_correct = itemId ? JSON.stringify([itemId]) : null;
            break;
        case 'survey_choice': case 'survey_poll':
            var sopts = [];
            var container = (skin === 'survey_poll') ? '#step-sc-options-' + sid : '#step-sc-options-' + sid;
            $(container + ' .br-option-row').each(function() {
                sopts.push({ id: $(this).find('.sc-option-id').val(), text: $(this).find('.sc-option-text').val(), image: null });
            });
            settings = { question: $('#step-sc-question-' + sid).val(), options: sopts, show_results: !!parseInt($('#step-sc-results-' + sid).val()) };
            if (skin === 'survey_choice') settings.allow_multiple = !!parseInt($('#step-sc-multi-' + sid).val());
            break;
        case 'survey_rating':
            settings = { question: $('#step-sr-question-' + sid).val(), min: parseInt($('#step-sr-min-' + sid).val()), max: parseInt($('#step-sr-max-' + sid).val()), labels: { min: $('#step-sr-lmin-' + sid).val(), max: $('#step-sr-lmax-' + sid).val() } };
            break;
        case 'open_text':
            settings = { min_words: parseInt($('#step-ot-minwords-' + sid).val()) || 0, use_wp_editor: !!parseInt($('#step-ot-editor-' + sid).val()), ai_validate: !!parseInt($('#step-ot-ai-' + sid).val()), ai_strictness: $('#step-ot-ai-strictness-' + sid).val() || 'standard' };
            break;
        case 'upload_image': case 'upload_video':
            settings = { prompt: $('#step-upload-prompt-' + sid).val(), max_size_mb: parseInt($('#step-upload-maxsize-' + sid).val()) || 5 };
            break;
    }
    return { settings: JSON.stringify(settings), step_correct: step_correct };
}

function updateStep() {
    let step_id = $("#step-id").val();
    let adventure_id = $("#the_adventure_id").val();
    if (step_id) {
        var skin = $('#step-skin-' + step_id).val();
        var category = brSkinCategoryMap[skin] || 'deliver';
        if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
            tinyMCE.triggerSave();
        }
        var collected = brCollectStepSettings(step_id);

        showLoader("small");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'updateStep',
                step_id: step_id,
                adventure_id: adventure_id,
                step_title: $('#step-title-' + step_id).val(),
                step_type: category,
                step_skin: skin,
                step_content: $('#step-content-' + step_id).val(),
                step_image: $('#the_step_image_' + step_id).val(),
                step_attach: $('#step-attach-' + step_id).val(),
                step_character_name: $('#step-character-name-' + step_id).val(),
                step_character_image: $('#the_step_character_image').val(),
                step_background: $('#the_step_background').val(),
                step_achievement_group: $('#the_step_achievement_group').val(),
                step_item: 0,
                step_settings: collected.settings,
                step_correct: collected.step_correct,
                step_mistake_message: $('#step-mistake-msg-' + step_id).val(),
                step_required: $('#step-required-' + step_id).val(),
                step_xp_reward: $('#step-reward-xp-' + step_id).val(),
                step_bloo_reward: $('#step-reward-bloo-' + step_id).val(),
                step_ep_reward: $('#step-reward-ep-' + step_id).val(),
                step_item_reward: $('#step-reward-item-' + step_id).val(),
                step_achievement_reward: $('#step-reward-ach-' + step_id).val(),
                step_branch_group_id: $('#step-branch-group-' + step_id).val()
            },
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
                let content = JSON.parse(json_text);
                var sid = content.updated_step.step_id;
                var sskin = content.updated_step.step_skin || content.updated_step.step_type;

                $('#step-' + sid + ' .step-title').text(content.updated_step.step_title);
                $('#step-' + sid + ' .step-type').text(sskin);

                var stepColorMap = {
                    'dialogue':'#1cc2eb','video':'#f7cb15','audio':'#ff9800','gallery':'#42a5f5','find_item':'#e040fb',
                    'multiple_choice':'#7c4dff','keyphrase':'#00bcd4','cryptex':'#00bcd4','puzzle':'#9f40e2','backpack_item':'#e040fb','scorm':'#00bcd4','case_study_html':'#00bcd4',
                    'survey_choice':'#42a5f5','survey_rating':'#f7cb15','survey_poll':'#42a5f5','open_text':'#42a5f5','upload_image':'#ff9800','upload_video':'#ff9800',
                    'jump_to_step':'#7c4dff','branch_choice':'#9f40e2',
                    'system':'#ff9800','win':'#24da98','fail':'#f44336','choose_nickname':'#7c4dff','choose_avatar':'#7c4dff'
                };
                $('#step-' + sid).attr('style', '--step-color:' + (stepColorMap[sskin] || '#1cc2eb'));

                closeStepAccordion(sid);
                setTimeout(function() {
                    document.getElementById('step-' + sid).scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        });
    } else {
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
            $(this).remove();
            hideAllOverlay();
        });
    }
}
////////////////////////////// New Step List Item ///////////////////////////
function removeStep(step_id) {
    if (step_id) {
        closeStepAccordion(step_id);
        showLoader("small");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'removeStep',
                step_id: step_id
            }),
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
            }
        });
    } else {
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
            $(this).remove();
            hideAllOverlay();
        });
    }
}

////////////////// REORDER STEPS
function reorderSteps() {
    let adventure_id = $("#the_adventure_id").val();
    let quest_id = $("#the_quest_id").val();
    let the_order = [];
    $('#steps-list > .br-step-item').each(function () {
        the_order.push($(this).find('input.the_step_id_val').val());
    });
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reorderSteps',
            adventure_id: adventure_id,
            quest_id: quest_id,
            the_order: the_order
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


////////////////////////////// NEW STEP BUTTON ///////////////////////////
function addStepButton() {
    let step_id = $('#step-id').val();
    let quest_id = $('#the_quest_id').val();
    let adventure_id = $("#the_adventure_id").val();
    let step_type = $('#step-type-' + step_id).val();
    if (step_id) {
        $('.small-loader').addClass('active');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'addStepButton',
                step_id: step_id,
                step_type: step_type,
                quest_id: quest_id,
                adventure_id: adventure_id
            }),
            method: "POST",
            success: function (data_received) {
                if (isJson(data_received)) {
                    displayAjaxResponse(data_received);
                } else {
                    $("#notify-message ul.content").append($('#msg-new-button-added').html());
                    $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                        $(this).remove();
                    });
                    $('#step-buttons-list').append(data_received);
                    $('.small-loader').removeClass('active');
                }
            }
        });
    }
}

function removeStepButton(button_id = 0) {
    if (removeStepButton) {
        $('.small-loader').addClass('active');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'removeStepButton',
                button_id: button_id,
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
            }
        });
    }
}


////////////////////////////// UPDATE Button ///////////////////////////
function updateStepButton(btn_id) {
    let step_id = $("input#step-id").val();
    let adventure_id = $("#the_adventure_id").val();
    if (btn_id) {
        let button_text = $("#step-button-" + btn_id + " input.button_text").val();
        let button_step_next = $("#step-button-" + btn_id + " select.button_step_next").val();
        let button_image = $("#the_step_button_image-" + btn_id).val();
        $('.small-loader').addClass('active');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'updateStepButton',
                step_id: step_id,
                adventure_id: adventure_id,
                button_text: button_text,
                button_step_next: button_step_next,
                btn_id: btn_id,
                button_image: button_image
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
            }
        });
    } else {
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
            $(this).remove();
            hideAllOverlay();
        });
    }
}

function checkStepType() { brCheckStepSkin($('#step-id').val()); }

var brSkinCategoryMap = {
    'dialogue':'deliver','video':'deliver','audio':'deliver','gallery':'deliver','find_item':'deliver',
    'multiple_choice':'validate','keyphrase':'validate','cryptex':'validate','puzzle':'validate','backpack_item':'validate','scorm':'validate','case_study_html':'validate',
    'survey_choice':'collect','survey_rating':'collect','survey_poll':'collect','open_text':'collect','upload_image':'collect','upload_video':'collect',
    'jump_to_step':'flow','branch_choice':'flow',
    'system':'deliver','win':'flow','fail':'flow','choose_nickname':'deliver','choose_avatar':'deliver'
};

function brCheckStepSkin(sid) {
    var skin = $('#step-skin-' + sid).val();
    var category = brSkinCategoryMap[skin] || 'deliver';
    $('#step-category-' + sid).val(category);

    // Show/hide skin panels
    $('#step-form-' + sid + ' .br-skin-panel, #step-form-' + sid + ' .br-skin-panel-inline').each(function() {
        var skins = ($(this).data('skins') || '').split(',');
        $(this).toggle(skins.indexOf(skin) !== -1);
    });

    // Load jump buttons if needed
    if (skin === 'jump_to_step') {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: { action: 'loadStepButtonForm', button_form: 'jump', step_id: sid },
            method: "POST",
            success: function(data) { if (data) $('#step-buttons-form-container').html(data); }
        });
    }
}

function brAddMcOption(sid) {
    var id = String.fromCharCode(97 + $('#step-mc-options-' + sid + ' .br-option-row').length);
    $('#step-mc-options-' + sid).append(
        '<div class="br-option-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px">' +
        '<input type="checkbox" class="mc-correct" value="' + id + '">' +
        '<input class="br-input mc-option-text" style="flex:1" placeholder="Option text">' +
        '<input type="hidden" class="mc-option-id" value="' + id + '">' +
        '<button class="br-btn br-btn-red" style="padding:4px 8px" onClick="$(this).closest(\'.br-option-row\').remove();"><span class="icon icon-trash"></span></button>' +
        '</div>'
    );
}

function brAddScOption(sid) {
    var id = String.fromCharCode(97 + $('#step-sc-options-' + sid + ' .br-option-row').length);
    $('#step-sc-options-' + sid).append(
        '<div class="br-option-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px">' +
        '<input class="br-input sc-option-text" style="flex:1" placeholder="Option text">' +
        '<input type="hidden" class="sc-option-id" value="' + id + '">' +
        '<button class="br-btn br-btn-red" style="padding:4px 8px" onClick="$(this).closest(\'.br-option-row\').remove();"><span class="icon icon-trash"></span></button>' +
        '</div>'
    );
}

function brAddGalleryImage(sid) {
    var frame = wp.media({ multiple: false, library: { type: 'image' } });
    frame.on('select', function() {
        var url = frame.state().get('selection').first().toJSON().url;
        var container = $('#step-gallery-' + sid);
        if (container.find('.br-gallery-thumb').length >= 7) return;
        var idx = container.find('.br-gallery-thumb').length;
        container.append(
            '<div class="br-gallery-thumb" data-index="' + idx + '">' +
            '<div style="width:80px;height:80px;border-radius:6px;background:url(' + url + ') center/cover;border:1px solid rgba(255,255,255,0.1)"></div>' +
            '<button class="br-btn br-btn-red" style="padding:2px 6px;font-size:10px;margin-top:2px" onClick="$(this).closest(\'.br-gallery-thumb\').remove();"><span class="icon icon-trash"></span></button>' +
            '<input type="hidden" class="gallery-image-url" value="' + url + '">' +
            '</div>'
        );
    });
    frame.open();
}

function brRemoveGalleryImage(sid, idx) {
    $('#step-gallery-' + sid + ' .br-gallery-thumb[data-index=' + idx + ']').remove();
}




////////////////////////////// ADD Objectives ///////////////////////////
function addObjective(objective_type) {
    let id = $('#the_quest_id').val();
    let adventure_id = $("#the_adventure_id").val();
    if (id) {
        showLoader("small");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'addObjective',
                id: id,
                objective_type: objective_type,
                adventure_id: adventure_id
            }),
            method: "POST",
            success: function (data_received) {
                if (data_received) {
                    $('table#objectives').append(data_received);
                    let new_objective_id = $('table#objectives tr:last-child td.objective-id').text();
                    editObjective(new_objective_id);
                    data_received = '';

                } else {
                    alert('No file found!');
                }
                hideAllOverlay();
            }
        });
    } else {
        $("#notify-message ul.content").append($('#msg-save-first').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
            $(this).remove();
            hideAllOverlay();
        });
    }
}

//////////////////////////// UPDATE OBJECTIVE ON CHANGE
function resetQuestObjectives(quest_id) {
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetQuestObjectives',
            quest_id: quest_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
//////////////////////////// UPDATE OBJECTIVE ON CHANGE
function updateObjective(objective_id) {
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let objective_data = {
        objective_content: $('#objective_content_' + objective_id).val(),
        objective_success_message: $('#objective_success_message_' + objective_id).val(),
        objective_keyword: $('#objective-form-' + objective_id + " .objective-keyword").val(),
        objective_ep_cost: $('#objective-form-' + objective_id + " .objective-ep-cost").val(),
    };
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateObjective',
            objective_id: objective_id,
            objective_data: objective_data
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            let objective_data = JSON.parse(data_received);
            if (objective_data.success) {
                $("#objective-row-" + objective_id + " .objective-row-keyword").text(objective_data.objective.objective_keyword);
                $("#objective-row-" + objective_id + " .objective-row-ep-cost").text(objective_data.objective.ep_cost);
                $("#objective-row-" + objective_id + " .objective-hint").html(objective_data.objective.objective_content);
            }
        }
    });
}


//////////////////////////// EDIT objective
function editObjective(objective_id) {
    animateScroll('#body');
    $("#overlay-content .content").html('');
    let adventure_id = $("#the_adventure_id").val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'editObjective',
            adventure_id: adventure_id,
            objective_id: objective_id
        }),
        method: "POST",
        success: function (data_received) {
            $("#overlay-content .content").html(data_received);
            $("#overlay-content").addClass('active');
            $('.loader, .small-loader').removeClass('active');
        }
    });
}

//////////////////////////// REMOVE objective

function removeObjective(objective_id) {
    showLoader("small");
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'removeObjective',
            objective_id: objective_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.success) {
                $("#notify-message .content").append(data.message);
                $("#notify-message").show();
                $("#notify-message").delay(1000).fadeOut(300, function () {
                    $("#notify-message .content").html('');
                });
                $("#objective-" + objective_id).fadeOut('fast', function () {
                    $("#objective-" + objective_id).remove();
                });
                hideAllOverlay();
            }
        }
    });
}


function filterAdminTable(type, element) {
    $('.filter li').removeClass('active');
    if (type != 'all') {
        $(element).hide();
        $(element + "." + type).show();
        $('.filter li#filter-' + type).addClass('active');
    } else {
        $(element).show();
        $('.filter li#filter-all').addClass('active');
    }
}




////////////////////////////////////////// NEW HEXAD ////////////////////////////////////////////

function newHexad() {
    let nonce = $("#nonce-hexad").val();
    $('#new-hexad-button').attr('disabled', true);
    let type_d = 0;
    let type_f = 0;
    let type_a = 0;
    let type_p = 0;
    let type_s = 0;
    let type_ph = 0;

    $('select.type-d').each(function (index, element) {
        type_d += Number($(this).val());
    });
    $('select.type-f').each(function (index, element) {
        type_f += Number($(this).val());
    });
    $('select.type-a').each(function (index, element) {
        type_a += Number($(this).val());
    });
    $('select.type-p').each(function (index, element) {
        type_p += Number($(this).val());
    });
    $('select.type-s').each(function (index, element) {
        type_s += Number($(this).val());
    });
    $('select.type-ph').each(function (index, element) {
        type_ph += Number($(this).val());
    });
    let answers = {
        type_d: type_d,
        type_f: type_f,
        type_a: type_a,
        type_p: type_p,
        type_s: type_s,
        type_ph: type_ph,
    };
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'newHexad',
            answers: answers,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


//////////////////  SWITCH RANKS ////////////////
function switchRank(achievement_id) {
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'switchRank',
            achievement_id: achievement_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

//////////////////  SWITCH TABS ////////////////
function switchTabs(tab_group, tab) {
    $(tab_group + " > .tab, " + tab_group + "-buttons .tab-button").removeClass('active');
    $(tab + " ," + tab + "-tab-button").addClass('active');
}

//////////////////  SET RATING  ////////////////
function setRating(id, rating) {
    let nonce = $("#rating_nonce").val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setRating',
            id: id,
            rating: rating,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////////// //////////// createChart //////////////////////////////////////////
function createHexadChart(v_d, v_f, v_a, v_p, v_s, v_ph, chart_name) {
    let ctx = document.getElementById(chart_name);
    let myChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: ['Achiever', 'Player', 'Socialiser', 'Philanthropist', 'Disruptor', 'Free Spirit'],
            datasets: [{
                data: [v_a, v_p, v_s, v_ph, v_d, v_f],
                backgroundColor: [
                    'rgba(33,150,243,0.7)',
                    'rgba(103,58,183,0.7)',
                    'rgba(255,193,7,0.7)',
                    'rgba(0,150,136,0.7)',
                    'rgba(244,67,54,0.7)',
                    'rgba(233,30,99,0.7)',

                ],
                borderColor: [
                    'rgba(33,150,243,1)',
                    'rgba(103,58,183,1)',
                    'rgba(255,193,7,1)',
                    'rgba(0,150,136,1)',
                    'rgba(244,67,54,1)',
                    'rgba(233,30,99,1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            startAngle: 0,
            animation: {
                animateRotate: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}

function createProgressionChart(current_val, total_val, who) {
    let ctx = $(who);
    let myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [total_val, current_val],
                backgroundColor: [
                    $('#color-total-value').val(),
                    $('#color-current-value').val(),
                ],
                borderWidth: 0,
            }],
            labels: [
                $('#label-total-value').val(),
                $('#label-current-value').val(),
            ]
        },
        options: {
            cutoutPercentage: 10,
            legend: {
                display: 0,
            }
        },
    });
}

function createReportChart(who, the_values, the_labels, the_colors) {
    let ctx = $(who);
    let myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: the_values,
                backgroundColor: the_colors,
                borderColor: [
                    'rgba(255,255,255,0)'
                ],
                borderWidth: 1,
            }],
            labels: the_labels
        },
        options: {
            cutoutPercentage: 50,
            rotation: 1 * Math.PI,
            circumference: 1 * Math.PI,
            legend: {
                display: 0,
            }
        },
    });
}



//////////////////  Update Profile  ////////////////

function randomPlayerData() {
    let names = ["Eugenio", "Bo", "Prince", "Elmer", "Ahmad", "Clair", "Rudolph", "Tanner", "Del", "Paris", "Rogelio", "Vincent", "Milo", "Denis", "Shelby", "Wilburn", "Cesar", "Alton", "Caleb", "Lorenzo", "Signe", "Tandra", "Albertine", "Vivienne", "Clarinda", "Shemika", "Jeanette", "Jenise", "Jeanett", "Lani", "Rena", "Vella", "Tillie", "Davida", "Tatum", "Martha", "Tena", "Gianna", "Macy", "Shenna"];

    let lastnames = ["Small", "Fuentes", "Watson", "Rose", "Watkins", "Morrison", "Fox", "Bautista", "Diaz", "George", "Williams", "Pena", "Larson", "Ho", "Cuevas", "Huynh", "Stuart", "Miles", "Juarez", "Raymond", "Cabrera", "Barr", "Riddle", "Hall", "Travis", "Cantrell", "Ferrell", "Salinas", "Mercer", "Edwards", "Potter", "Crosby", "Moses", "Richards", "Riley", "Payne", "Rosales", "Barker", "Grant", "Vasquez"];

    let newName = names[Math.floor(Math.random() * names.length)];
    let newLast = lastnames[Math.floor(Math.random() * names.length)];

    $('#the_first_name').val(newName);
    $('#the_last_name').val(newLast);
    $('#the_email').val('noEmail' + (Math.random() * 10000) + '@notin.bluerabbit.io');

    $('#the_player_picture').val('');
}

function addTabi() {
    showLoader("small");
    let adventure_id = $("#the_adventure_parent_id").val();
    let nonce = $('#add-tabi-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'addTabi',
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            let my_tabi_data = JSON.parse(data_received);
            if (my_tabi_data.new_tabi_id) {
                insertTabiRow(my_tabi_data.new_tabi_id);
            }
            displayAjaxResponse(data_received);
            // $("#notify-message ul.content").append(data.message);
        }
    });
}

function insertTabiRow(tabi_id) {
    if (tabi_id) {
        showLoader("small");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'insertTabiRow',
                tabi_id: tabi_id
            }),
            method: "POST",
            success: function (data_received) {
                if (data_received) {
                    $('#table-tabis').append(data_received);
                    notification('#msg-new-tabi-row');

                } else {
                    notification('#msg-error');
                }
                hideAllOverlay();
            }
        });
    } else {
        notification('#msg-error');
    }
}

function getPlayerData(player_id) {
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'getPlayerDataJSON',
            player_id: player_id
        }),
        method: "POST",
        success: function (json_data) {
            let data = JSON.parse(json_data);
            return data;
        }
    });
}

function updateProfile() {
    showLoader("small");
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#profile_nonce').val();
    let player_data = {
        first_name: $('#the_first_name').val(),
        last_name: $('#the_last_name').val(),
        email: $('#the_email').val(),
        lang: $('#the_lang').val(),
        profile_picture: $('#the_player_picture').val(),
        player_company: $('#the_player_company').val(),
        player_website: $('#the_player_website').val(),
        player_linkedin: $('#the_player_linkedin').val(),
        player_bio: $('#the_player_bio').val(),
    }
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateProfile',
            player_data: player_data,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function setNickname(id) {
    showLoader("small");
    let nonce = $('#profile_nonce').val();
    let nickname = $('#the_player_nickname_' + id).val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setNickname',
            nickname: nickname,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function setProfilePicture(id) {
    showLoader("small");
    let nonce = $('#profile_nonce').val();
    let player_picture = $('#the_player_picture_' + id).val();
    $(".avatar-button").removeClass('active').attr('disabled', false);
    $("#avatar-button-" + id).attr('disabled', true).addClass('active');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setProfilePicture',
            player_picture: player_picture,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
//////////////////  Update Speaker  ////////////////

function updateSpeaker() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#speaker_nonce').val();
    let speaker_data = {
        id: $('#the_speaker_id').val(),
        player_id: $('#the_speaker_player_id').val(),
        adventure_id: $('#the_adventure_id').val(),
        first_name: $('#the_speaker_first_name').val(),
        last_name: $('#the_speaker_last_name').val(),
        bio: $('#the_speaker_bio').val(),
        picture: $('#the_speaker_picture').val(),
        company: $('#the_speaker_company').val(),
        website: $('#the_speaker_website').val(),
        linkedin: $('#the_speaker_linkedin').val(),
    }
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateSpeaker',
            speaker_data: speaker_data,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            if (speaker_data.player_id > 0) {
                document.location.reload();
            }
        }
    });
}
//////////////////  Set Speaker DATA  ////////////////

function setSpeakerData(id) {
    showLoader('small');
    let nonce = $('#set-speaker-nonce').val();
    let speaker_data = {
        id: $('#speaker-' + id + '-id').val(),
        adventure_id: $('#the_adventure_id').val(),
        first_name: $('#speaker-' + id + '-first-name').val(),
        last_name: $('#speaker-' + id + '-last-name').val(),
        company: $('#speaker-' + id + '-company').val(),
        website: $('#speaker-' + id + '-website').val(),
        twitter: $('#speaker-' + id + '-twitter').val(),
        linkedin: $('#speaker-' + id + '-linkedin').val(),
    }
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setSpeakerData',
            speaker_data: speaker_data,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

//////////////////  Update Session  ////////////////

function updateSession() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#session_nonce').val();
    let speaker_ids = [];

    $('input.speaker_ids:checked').each(function () {
        speaker_ids.push($(this).val());
    });
    let session_data = {
        id: $('#the_session_id').val(),
        adventure_id: $('#the_adventure_id').val(),
        title: $('#the_session_title').val(),
        room: $('#the_session_room').val(),
        start: $('#the_session_start').val(),
        end: $('#the_session_end').val(),
        quest_id: $('#the_quest_id').val(),
        speaker_ids: speaker_ids,
        status: $('#the_session_status').val(),
        description: $('#the_session_description').val(),
        achievement_id: $('#the_achievement_id').val(),
        guild_id: $('#the_guild_id').val(),
    }
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateSession',
            session_data: session_data,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

//////////////////  updatePrevLevel  ////////////////

function updatePrevLevel(level, adventure_id) {
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updatePrevLevel',
            adventure_id: adventure_id,
            level: level
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            displayAjaxResponse(data_received);
        }
    });
}


//////////////////  CLOSE INTRO  ////////////////

function closeIntro() {
    showLoader();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'closeIntro',
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.success) {
                document.location.href = data.adventure_home_url;
            }
        }
    });
}
//////////////////  RESET INTRO  ////////////////

function resetIntro() {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetIntro',
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

//////////////////  RESET PREV LEVEL  ////////////////

function resetPrevLevel() {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetPrevLevel',
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
//////////////////  RESET GUILDS  ////////////////

function resetGuilds() {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetGuilds',
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
//////////////////  reset Player Adventure  ////////////////

function resetPlayerAdventure(player_id) {
    showLoader("small");
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetPlayerAdventure',
            adventure_id: adventure_id,
            player_id: player_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

////////////////////////////////////////// UPDATE Adventure  ////////////////////////////////////////////

function updateAdventure() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();

    let unenrolled = [];
    $('ul.player-select li.unenrolled').each(function (index, element) {
        unenrolled.push($('input.player-id', this).val());
    });

    let adventure_ranks = [];
    $('table#adventure-ranks tbody tr').each(function (index, element) {
        if ($('input.rank-level', this).val() != "" && $('select.rank-achievement', this).val() > 0 && $('textarea.rank-message', this).val() != "") {
            let rank = {
                level: $('input.rank-level', this).val(),
                achievement: $('select.rank-achievement', this).val(),
                message: $('textarea.rank-message', this).val(),
                condition_type: $('select.rank-condition-type', this).val() || 'level',
            };
            adventure_ranks.push(rank);
        }
    });
    let adventure_settings = [];
    $('.setting').each(function (index, element) {
        let setting_values = {
            id: $('.setting-id', this).val(),
            name: $('.setting-name', this).val(),
            label: $('.setting-label', this).val(),
            value: $('.setting-value', this).val(),
        };
        adventure_settings.push(setting_values);
    });
    let adventure_data = {
        adventure_id: $('#the_adventure_id').val(),
        adventure_owner: $('#the_adventure_owner').val(),
        adventure_badge: $('#the_adventure_badge').val(),
        adventure_logo: $('#the_adventure_logo').val(),
        adventure_certificate_signature: $('#the_adventure_certificate_signature').val(),
        adventure_gmt: $('#the_adventure_gmt').val(),
        adventure_title: $('#the_adventure_title').val(),
        adventure_xp_label: $('#the_adventure_xp_label').val(),
        adventure_bloo_label: $('#the_adventure_bloo_label').val(),
        adventure_ep_label: $('#the_adventure_ep_label').val(),
        adventure_xp_long_label: $('#the_adventure_xp_long_label').val(),
        adventure_bloo_long_label: $('#the_adventure_bloo_long_label').val(),
        adventure_ep_long_label: $('#the_adventure_ep_long_label').val(),
        adventure_type: $('#the_adventure_type').val(),
        adventure_grade_scale: $('#the_adventure_grade_scale').val(),
        adventure_progression_type: $('#the_adventure_progression_type').val(),
        adventure_privacy: $('#the_adventure_privacy').val(),
        adventure_status: $('#the_adventure_status').val(),
        adventure_instructions: $('#the_adventure_instructions').val(),
        adventure_nickname: $('#the_adventure_nickname').val(),
        adventure_code: $('#the_adventure_code').val(),
        adventure_color: $('#the_adventure_color').val(),
        adventure_hide_schedule: $('#the_adventure_hide_schedule').val(),
        adventure_hide_quests: $('#the_adventure_hide_quests').val(),
        adventure_has_guilds: $('#the_adventure_has_guilds').val(),
        adventure_level_up_array: $('#the_adventure_level_up_array').val(),
        adventure_start_date: $('#the_adventure_start_date').val(),
        adventure_end_date: $('#the_adventure_end_date').val(),
        unenrolled: unenrolled,
        adventure_ranks: adventure_ranks,
        adventure_settings: adventure_settings
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateAdventure',
            nonce: nonce,
            adventure_data: adventure_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

function brSaveAiKey() {
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'br_save_ai_api_key',
            nonce: $('#nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            api_key: $('#the_adventure_ai_api_key').val()
        },
        success: function(raw) { displayAjaxResponse(raw); }
    });
}

function brRemoveAiKey() {
    $('#the_adventure_ai_api_key').val('');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'br_save_ai_api_key',
            nonce: $('#nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            api_key: ''
        },
        success: function(raw) { displayAjaxResponse(raw); }
    });
}

////////////////////////////////////////// Tremendous Settings  ////////////////////////////////////////////

function brSaveTremendousConfig() {
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'br_tremendous_save_config',
            nonce: $('#nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            api_key: $('#the_tremendous_api_key').val(),
            sandbox_mode: $('#the_tremendous_sandbox_mode').val(),
            funding_source_id: $('#the_tremendous_funding_source').val(),
            campaign_id: $('#the_tremendous_campaign_id').val(),
            currency_code: $('#the_tremendous_currency_code').val()
        },
        success: function(raw) {
            displayAjaxResponse(raw);
            $('#the_tremendous_api_key').val('');
        }
    });
}

function brLoadTremendousCatalog() {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'br_tremendous_get_catalog',
            nonce: $('#nonce').val(),
            adventure_id: $('#the_adventure_id').val()
        },
        success: function(raw) {
            $('.loader, .small-loader').removeClass('active');
            let data = JSON.parse(raw);
            if (!data.success || !data.products || !data.products.length) {
                $('#tremendous-catalog-list').text('Could not load the catalog - check the Tremendous connection in adventure settings.');
                return;
            }
            let selected = [];
            try { selected = JSON.parse($('#the_item_tremendous_products').val() || '[]'); } catch (e) {}
            let html = '';
            data.products.forEach(function(p) {
                let checked = selected.indexOf(p.id) >= 0 ? 'checked' : '';
                html += '<label class="br-tremendous-product-option">' +
                    '<input type="checkbox" class="tremendous-product-checkbox" value="' + p.id + '" ' + checked + '> ' +
                    (p.name || p.id) + '</label>';
            });
            $('#tremendous-catalog-list').html(html);
            $('.tremendous-product-checkbox').on('change', brSyncTremendousProducts);
        }
    });
}

function brSyncTremendousProducts() {
    let selected = [];
    $('.tremendous-product-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    $('#the_item_tremendous_products').val(JSON.stringify(selected));
}

function brTestTremendousConnection() {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'br_tremendous_test_connection',
            nonce: $('#nonce').val(),
            adventure_id: $('#the_adventure_id').val()
        },
        success: function(raw) {
            displayAjaxResponse(raw);
            let data = JSON.parse(raw);
            if (data.success && data.funding_sources) {
                let $select = $('#the_tremendous_funding_source');
                let current = $select.val();
                $select.empty();
                data.funding_sources.forEach(function(fs) {
                    $select.append($('<option>').val(fs.id).text(fs.name + (fs.id === 'BALANCE' ? '' : ' (' + fs.id + ')')));
                });
                if ($select.find('option[value="' + current + '"]').length) {
                    $select.val(current);
                }
            }
        }
    });
}

////////////////////////////////////////// Preview Template  ////////////////////////////////////////////

function previewTemplate(adv_id = null) {
    if (adv_id) {
        $('#loader').addClass('active');
        $('.overlay-bg').addClass('active');
        $("#template-" + adv_id + " .template-preview .template-preview-content").html("");
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'previewTemplate',
                adventure_id: adv_id
            }),
            method: "POST",
            success: function (template) {
                if (template) {
                    $("#template-" + adv_id + " .template-preview .template-preview-content").html(template);
                    $("#template-" + adv_id + " .template-preview").addClass('active');
                }
                $('#loader').removeClass('active');
                $('.overlay-bg').removeClass('active');
            }
        });
    } else {
        return false;
    }
}

function closeTemplatePreview() {
    $(".template-preview").removeClass('active').children(".template-preview-content").html("");
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSetting(element_id, element_value) {
    showLoader('small');
    if (element_id && $('#the_adventure_id').val() > 0) {
        let new_value = (element_value) ? element_value : $(element_id + ' .setting-value').val();
        let settings_data = [{
            id: $(element_id + ' .setting-id').val(),
            name: $(element_id + ' .setting-name').val(),
            label: $(element_id + ' .setting-label').val(),
            value: new_value
        }];
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'saveSettings',
                settings_data: settings_data,
                adventure: $('#the_adventure_id').val()
            }),
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
            }
        });
    } else {
        return false;
    }
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSettings() {
    showLoader('small');
    let settings_data = [];
    $('.setting').each(function (index, element) {
        let setting_values = {
            id: $('.setting-id', this).val(),
            name: $('.setting-name', this).val(),
            label: $('.setting-label', this).val(),
            value: $('.setting-value', this).val(),
        };
        settings_data.push(setting_values);
    });
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'saveSettings',
            settings_data: settings_data
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSysConfig() {
    showLoader('small');
    let config_data = [];
    $('.config').each(function (index, element) {
        let config_values = {
            id: $('.setting-id', this).val(),
            name: $('.setting-name', this).val(),
            label: $('.setting-label', this).val(),
            value: $('.setting-value', this).val(),
            type: $('.setting-type', this).val(),
            desc: $('.setting-desc', this).val(),
        };
        config_data.push(config_values);
    });
    let features_data = [];
    var plans = (typeof brPlans !== 'undefined') ? brPlans : [
        {plan_key:'free'},{plan_key:'pro'},{plan_key:'admin'},{plan_key:'god'}
    ];
    $('.feature').each(function (index, element) {
        let feature_values = {
            id: $('.feature-id', this).val(),
            name: $('.feature-name', this).val(),
            label: $('.feature-label', this).val(),
            type: $('.feature-type', this).val(),
            desc: $('.feature-desc', this).val(),
        };
        var self = this;
        plans.forEach(function(p) {
            var val = 0;
            var el = $('.feature-' + p.plan_key, self);
            if (el.length) {
                if (el.is(':checked')) {
                    val = 1;
                } else if (el.attr('type') === 'number' && el.val()) {
                    val = el.val();
                } else if (el.is(':checkbox') && !el.is(':checked')) {
                    val = 0;
                }
            }
            feature_values[p.plan_key] = val;
        });
        features_data.push(feature_values);
    });
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'saveSysConfig',
            config_data: config_data,
            features_data: features_data
        }),
        method: "POST",
        success: function (data_received) {
            saveAllPlanFeatures(function() {
                displayAjaxResponse(data_received);
            });
        }
    });
}

////////////////////////////////////////// CHECK ALL CHECKBOXES  ////////////////////////////////////////////

function checkAllFeatures(p_class) {
    if ($('input[type=checkbox].feature-' + p_class + ':checked').length == $('input[type=checkbox].feature-' + p_class).length) {
        $('input[type=checkbox].feature-' + p_class).prop('checked', false);
    } else {
        $('input[type=checkbox].feature-' + p_class).prop('checked', true);
    }
}

////////////////////////////////////////// PLAN MANAGEMENT  ////////////////////////////////////////////

function showNewPlanForm() {
    $('#new-plan-form').show();
    $('#new-plan-label').val('');
    $('#new-plan-notes').val('');
    $('#new-plan-clone').val('0');
}

function createNewPlan() {
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'savePlan',
            plan_label: $('#new-plan-label').val(),
            plan_notes: $('#new-plan-notes').val(),
            clone_from: $('#new-plan-clone').val()
        },
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            var resp = JSON.parse(data_received);
            if (resp.success) {
                location.reload();
            }
        }
    });
}

function deletePlanConfirm(plan_id, plan_label) {
    if (confirm('Delete plan "' + plan_label + '"? Users assigned to it will lose their plan assignment.')) {
        showLoader('small');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'deletePlan',
                plan_id: plan_id
            },
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
                var resp = JSON.parse(data_received);
                if (resp.success) {
                    location.reload();
                }
            }
        });
    }
}

function brPopulatePlanAccordions() {
    if (typeof brSysFeatures === 'undefined' || typeof brPlans === 'undefined') return;

    $('.plan-features-body').each(function() {
        var $tbody = $(this);
        var planId = $tbody.data('plan-id');
        var planKey = '';
        brPlans.forEach(function(p) { if (p.plan_id == planId) planKey = p.plan_key; });

        $tbody.empty();
        for (var fKey in brSysFeatures) {
            var f = brSysFeatures[fKey];
            var val = (planKey && f[planKey] !== undefined) ? f[planKey] : '0';
            var row = '<tr class="plan-feature-row">';
            row += '<td>' + (f.label || fKey) + '</td>';
            row += '<td class="text-center">';
            row += '<input type="hidden" class="pf-feature-id" value="' + f.id + '">';
            if (f.type === 'number') {
                row += '<input type="number" class="form-ui pf-value" value="' + val + '" style="width:80px">';
            } else {
                row += '<input type="checkbox" class="pf-value" ' + (parseInt(val) ? 'checked' : '') + '>';
            }
            row += '</td></tr>';
            $tbody.append(row);
        }
    });
}

function saveAllPlanFeatures(onDone) {
    if (typeof brPlans === 'undefined') { if (onDone) onDone(); return; }
    var pending = 0;
    $('.plan-features-body').each(function() {
        var planId = $(this).data('plan-id');
        var features_data = [];
        $(this).find('.plan-feature-row').each(function() {
            var fid = $(this).find('.pf-feature-id').val();
            var el = $(this).find('.pf-value');
            var val = el.is(':checkbox') ? (el.is(':checked') ? 1 : 0) : (el.val() || 0);
            features_data.push({ feature_id: fid, feature_value: val });
        });
        if (!features_data.length) return;
        pending++;
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: { action: 'savePlanFeatures', plan_id: planId, features_data: features_data },
            method: 'POST',
            success: function() { pending--; if (pending <= 0 && onDone) onDone(); }
        });
    });
    if (pending === 0 && onDone) onDone();
}

function savePlanFeaturesAction() {
    showLoader('small');
    saveAllPlanFeatures(function() { displayAjaxResponse('{"success":true,"message":"Plan features saved","just_notify":true}'); });
}

var planSearchTimer = null;
function searchUsersForPlanAssign() {
    clearTimeout(planSearchTimer);
    var search = $('#plan-user-search').val();
    if (search.length < 2) {
        $('#plan-user-results').html('');
        return;
    }
    planSearchTimer = setTimeout(function () {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: { action: 'searchPlayersForPlan', search: search },
            method: "POST",
            success: function (data_received) {
                var resp = JSON.parse(data_received);
                if (!resp.success) return;
                var html = '<table class="table w-full" cellpadding="5"><thead><tr><td>Name</td><td>Email</td><td>Current Plan</td><td>Assign</td></tr></thead><tbody>';
                var plans = (typeof brPlans !== 'undefined') ? brPlans : [];
                resp.players.forEach(function (p) {
                    html += '<tr>';
                    html += '<td class="font _14">' + (p.player_display_name || '') + '</td>';
                    html += '<td class="font _14">' + (p.player_email || '') + '</td>';
                    html += '<td class="font _14">' + (p.plan_label || '<em>Role default</em>') + '</td>';
                    html += '<td><select class="form-ui font _14" onChange="assignPlan(' + p.player_id + ', this.value);">';
                    html += '<option value="0"' + (!p.user_plan_id ? ' selected' : '') + '>Role default</option>';
                    plans.forEach(function (pl) {
                        html += '<option value="' + pl.plan_id + '"' + (p.user_plan_id == pl.plan_id ? ' selected' : '') + '>' + pl.plan_label + '</option>';
                    });
                    html += '</select></td></tr>';
                });
                html += '</tbody></table>';
                $('#plan-user-results').html(html);
            }
        });
    }, 400);
}

function assignPlan(player_id, plan_id) {
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'assignUserPlan',
            player_id: player_id,
            plan_id: plan_id
        },
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

////////////////////////////////////////// FEATURE CRUD  ////////////////////////////////////////////

function showAddFeatureForm() {
    $('#feature-form-title').text('Add Feature');
    $('#feature-form-id').val('0');
    $('#feature-form-name').val('').prop('readonly', false);
    $('#feature-form-label').val('');
    $('#feature-form-type').val('checkbox');
    $('#feature-form-desc').val('');
    $('#feature-form').show();
}

function editFeature(id, name, label, type, desc) {
    $('#feature-form-title').text('Edit Feature');
    $('#feature-form-id').val(id);
    $('#feature-form-name').val(name).prop('readonly', true);
    $('#feature-form-label').val(label);
    $('#feature-form-type').val(type);
    $('#feature-form-desc').val(desc);
    $('#feature-form').show();
}

function saveFeatureAction() {
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveFeature',
            feature_id: $('#feature-form-id').val(),
            feature_name: $('#feature-form-name').val(),
            feature_label: $('#feature-form-label').val(),
            feature_type: $('#feature-form-type').val(),
            feature_desc: $('#feature-form-desc').val()
        },
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            var resp = JSON.parse(data_received);
            if (resp.success) {
                location.reload();
            }
        }
    });
}

function deleteFeatureConfirm(feature_id, feature_name) {
    if (confirm('Delete feature "' + feature_name + '"? This will remove it from all plans.')) {
        showLoader('small');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'deleteFeature',
                feature_id: feature_id
            },
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
                var resp = JSON.parse(data_received);
                if (resp.success) {
                    $('#feature-row-' + feature_id).remove();
                }
            }
        });
    }
}

////////////////////////////////////////// COPY PLAN FEATURES  ////////////////////////////////////////////

function copyFromPlanAction() {
    var source_id = $('#copy-from-plan-select').val();
    var target_id = $('#editing-plan-id').val();
    if (!source_id) return;
    if (source_id == target_id) {
        alert('Cannot copy a plan onto itself.');
        return;
    }
    if (!confirm('This will overwrite all feature values for this plan. Continue?')) return;
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'copyPlanFeatures',
            target_plan_id: target_id,
            source_plan_id: source_id
        },
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            var resp = JSON.parse(data_received);
            if (resp.success) {
                location.reload();
            }
        }
    });
}

////////////////////////////////////////// ROLE DEFAULTS  ////////////////////////////////////////////

function saveRoleDefaults() {
    showLoader('small');
    var defaults = {};
    $('.role-default-select').each(function () {
        defaults[$(this).data('role')] = $(this).val();
    });
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveRoleDefaults',
            role_defaults: defaults
        },
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

////////////////////////////////////////// Toggle Correct ////////////////////////////////////////////
function toggleCorrect(who) {
    $(who + " .toggle-button.question").toggleClass('active');
    if ($(who + " .toggle-button").hasClass('active')) {
        $(who + " input.option-correct").val(1);
    } else {
        $(who + " input.option-correct").val(0);
    }
}

////////////////////////////////////////// UPDATE QUEST ////////////////////////////////////////////

function updateQuest() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();
    let quest_reqs = [];
    $('ul#quests-reqs li.active').each(function (index, element) {
        quest_reqs.push($('input.reqs-id', this).val());
    });
    let quest_achievement_reqs = [];
    $('ul#quest-achievement-reqs li.active').each(function (index, element) {
        quest_achievement_reqs.push($('input.reqs-id', this).val());
    });
    let quest_libs = [];
    $('ul#libraries li.active').each(function (index, element) {
        quest_libs.push($('input.lib-id', this).val());
    });
    let quest_objectives = [];
    $('table#quest-objectives tbody tr').each(function (index, element) {
        if ($('input.objective-content', this).val() != "") {
            let objective = {
                keyword: $('input.objective-keyword', this).val(),
                type: $('input.objective-type', this).val(),
                content: $('input.objective-content', this).val(),
            };
            quest_objectives.push(objective);
        }
    });
    let quest_questions = $('#questions .question').length;

    let steps_order = [];
    $('#steps-list > .br-step-item').each(function () {
        steps_order.push($(this).find('input.the_step_id_val').val());
    });

    let the_deadline = $('#the_quest_deadline').val() ? $('#the_quest_deadline').val() + ":00" : "";
    let the_startdate = $('#the_quest_start_date').val() ? $('#the_quest_start_date').val() + ":00" : "";

    let mech_item_reward = $("#mech_item_reward li.active input.item-id").val();
    let mech_achievement_reward = $("#the_mech_achievement_reward li.active input.achievement-reward-id").val();
    let quest_item_required = $("#item_required li.active input.item-id").val();
    // The old inline requirement grids only exist on Mission/Survey builders now -
    // Quest's builder saves reqs separately via the Conditions panel, so these keys
    // must be left out of the payload there (see BR_Quest::updateQuest() PHP guard).
    let has_old_reqs_ui = $('#quests-reqs, #quest-achievement-reqs, #item_required').length > 0;
    let quest_data = {
        quest_id: $('#the_quest_id').val(),
        quest_status: $('#the_quest_status').val(),
        quest_relevance: $('#the_quest_relevance').val(),
        quest_title: $('#the_quest_title').val(),
        quest_content: $('#the_quest_content').val(),
        quest_success_message: $('#the_quest_success_message').val(),
        quest_type: $('#the_quest_type').val(),
        quest_guild: $('#the_quest_guild').val(),
        adventure_id: $('#the_adventure_id').val(),
        achievement_id: $('#the_achievement_id').val(),
        tabi_id: $('#the_tabi_id').val(),
        quest_libs: quest_libs,
        quest_secondary_headline: $('#the_quest_secondary_headline').val(),
        quest_style: $('#the_quest_style').val(),
        quest_color: $('#the_quest_color').val(),
        quest_icon: $('#the_quest_icon').val(),
        quest_order: $('#the_quest_order').val(),
        quest_objectives: quest_objectives,
        steps_order: steps_order,
        quest_mechs: {
            mech_level: $('#the_quest_level').val(),
            mech_xp: $('#the_quest_xp').val(),
            mech_ep: $('#the_quest_ep').val(),
            mech_bloo: $('#the_quest_bloo').val(),
            mech_badge: $('#the_quest_badge').val(),
            mech_deadline: the_deadline,
            mech_start_date: the_startdate,
            mech_deadline_cost: $('#the_quest_deadline_cost').val(),
            mech_unlock_cost: $('#the_quest_unlock_cost').val(),
            mech_min_words: $('#the_quest_min_words').val(),
            mech_min_links: $('#the_quest_min_links').val(),
            mech_min_images: $('#the_quest_min_images').val(),
            mech_max_attempts: $('#the_quest_max_attempts').val(),
            mech_free_attempts: $('#the_quest_free_attempts').val(),
            mech_attempt_cost: $('#the_quest_attempt_cost').val(),
            mech_questions_to_display: $('#the_quest_questions_to_display').val(),
            mech_answers_to_win: $('#the_quest_answers_to_win').val(),
            mech_time_limit: $('#the_quest_time_limit').val(),
            mech_show_answers: $('#the_quest_show_answers').val(),
            mech_item_reward: mech_item_reward,
            mech_achievement_reward: mech_achievement_reward,
            mech_optional: $('#the_quest_optional').is(':checked') ? 1 : 0,
            mech_validate: $('#the_quest_validate').is(':checked') ? 1 : 0,
        },
        quest_questions: quest_questions
    };
    if (has_old_reqs_ui) {
        quest_data.quest_reqs = quest_reqs;
        quest_data.quest_achievement_reqs = quest_achievement_reqs;
        quest_data.quest_item_required = quest_item_required;
    }

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateQuest',
            nonce: nonce,
            quest_data: quest_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

//
////////////////////////////////////////// UPDATE Challenge ////////////////////////////////////////////

function updateChallenge() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();
    let challenge_reqs = [];
    $('ul#quests-reqs li.active').each(function (index, element) {
        challenge_reqs.push($('input.reqs-id', this).val());
    });

    /// DEADLINE AND STARTDATE
    let challenge_data = {
        challenge_id: $('#the_quest_id').val(),
        challenge_status: $('#the_quest_status').val(),
        challenge_relevance: $('#the_quest_relevance').val(),
        challenge_title: $('#the_quest_title').val(),
        challenge_objective: $('#the_quest_objective').val(),
        adventure_id: $('#the_adventure_id').val(),
        achievement_id: $('#the_achievement_id').val(),
        challenge_item_required: $("#item_required li.active input.item-id").val(),
        challenge_reqs: quest_reqs,
        challenge_mechs: {
            level: $('#the_quest_level').val(),
            xp: $('#the_quest_xp').val(),
            bloo: $('#the_quest_bloo').val(),
            badge: $('#the_quest_badge').val(),
            deadline: $('#the_quest_deadline').val(),
            start_date: $('#the_quest_start_date').val(),
            deadline_cost: $('#the_quest_deadline_cost').val(),
            max_attempts: $('#the_quest_max_attempts').val(),
            free_attempts: $('#the_quest_free_attempts').val(),
            attempt_cost: $('#the_quest_attempt_cost').val(),
            questions_to_display: $('#the_quest_questions_to_display').val(),
            answers_to_win: $('#the_quest_answers_to_win').val(),
            time_limit: $('#the_quest_time_limit').val(),
            show_answers: $('#the_quest_show_answers').val(),
            item_reward: $("#mech_item_reward li.active input.item-id").val(),
        }
    };

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateChallenge',
            nonce: nonce,
            challenge_data: challenge_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

////////////////////////////////////////// UPDATE ENCOUNTER ////////////////////////////////////////////

function updateEncounter() {
    showLoader();
    let nonce = $('#new-encounter-nonce').val();
    let encounter_data = {
        id: $('#the_enc_id').val(),
        status: $('#the_enc_status').val(),
        question: $('#the_enc_question').val(),
        correct: $('#the_enc_correct').val(),
        decoy1: $('#the_enc_decoy1').val(),
        decoy2: $('#the_enc_decoy2').val(),
        level: $('#the_enc_level').val(),
        xp: $('#the_enc_xp').val(),
        ep: $('#the_enc_ep').val(),
        bloo: $('#the_enc_bloo').val(),
        color: $('#the_enc_color').val(),
        badge: $('#the_enc_badge').val(),
        icon: $('#the_enc_icon').val(),
        path: $('#the_enc_achievement_id').val()
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateEncounter',
            nonce: nonce,
            encounter_data: encounter_data,
            adventure_id: $('#the_adventure_id').val(),
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
////////////////////////////////////////// UPDATE ORGANIZATION ////////////////////////////////////////////

function updateOrg() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#new-org-nonce').val();
    let org_data = {
        id: $('#the-org-id').val(),
        name: $('#the-org-name').val(),
        logo: $('#the-org-logo').val(),
        color: $('#the-org-color').val(),
        status: "publish",
        about: $('#the-org-content').val(),
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateOrg',
            nonce: nonce,
            org_data: org_data,
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
            unloadContent();
        }
    });
}
////////////////////////////////////////// Find Players To ORGANIZATION ////////////////////////////////////////////

function findPlayersToOrg() {
    showLoader();
    let nonce = $('#search-player-nonce').val();
    let search_string = $('#player-search-string').val();
    $('#search-players-results ul').html('');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'findPlayersToOrg',
            nonce: nonce,
            search_string: search_string,
        }),
        method: "POST",
        success: function (results) {
            if (results) {
                unloadContent();
                $('#search-players-results ul').html(results);
            }
        }
    });
}
////////////////////////////////////////// Add Player To ORGANIZATION ////////////////////////////////////////////

function addPlayerToOrg(player_id = null) {
    if (player_id) {
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                org_id: $("#the_org_id").val(),
                player_id: player_id,
                action: 'addPlayerToOrg',
            }),
            method: "POST",
            success: function (results) {
                hideAllOverlay();

                if (results) {
                    notification('#msg-player-added-to-org', 1000, 'Added to org', 'check');
                    $('#org-players-list').append(results);
                }
            }
        });
    } else {
        notification('#msg-player-not-added-to-org', 1000, 'Player not added to org', 'cancel');
    }
}
// The org manager toggle now lives in js/br-org.js (orgSetPlayerRole), which
// swaps in the row the server re-renders instead of patching it here.

////////////////////////////////////////// UPDATE SPONSOR ////////////////////////////////////////////

function updateSponsor() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#new-sponsor-nonce').val();
    let sponsor_data = {
        id: $('#the-sponsor-id').val(),
        name: $('#the-sponsor-name').val(),
        url: $('#the-sponsor-url').val(),
        logo: $('#the-sponsor-logo').val(),
        color: $('#the-sponsor-color').val(),
        level: $('#the-sponsor-level').val(),
        image: $('#the-sponsor-image').val(),
        about: $('#the-sponsor-about').val(),
        twitter: $('#the-sponsor-twitter').val(),
        linkedin: $('#the-sponsor-linkedin').val(),
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateSponsor',
            nonce: nonce,
            sponsor_data: sponsor_data,
            adventure_id: $('#the_adventure_id').val(),
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
            unloadContent();
        }
    });
}
////////////////////////////////////////// UPDATE ACHIEVEMENT ////////////////////////////////////////////

// ── Achievement auto-assign Conditions (page-new-achievement.php) ──────────
// Distinct from the single Rank Condition field above it: usable on any achievement
// type, supports several conditions at once (AND, evaluated in
// BR_Player::resetPlayer()), and two types reference one specific milestone/Tabi
// rather than a bare threshold - those need a picker instead of a number input.
var ACH_COND_OBJECT_TYPE = { specific_quest: 'quest', specific_tabi: 'tabi', tabi_pct: 'tabi' };
var ACH_COND_NO_THRESHOLD = ['specific_quest', 'specific_tabi'];

function brAchCondRowUpdate(selectEl) {
    var $row = $(selectEl).closest('.br-cond-row');
    var type = $(selectEl).val();
    var objType = ACH_COND_OBJECT_TYPE[type];
    $row.find('.br-cond-quest-picker').toggle(objType === 'quest');
    $row.find('.br-cond-tabi-picker').toggle(objType === 'tabi');
    $row.find('.br-cond-threshold').toggle(ACH_COND_NO_THRESHOLD.indexOf(type) === -1);
}

function brAddAchievementConditionRow() {
    var tpl = document.getElementById('achievement-condition-row-template');
    if (!tpl) return;
    var clone = document.importNode(tpl.content, true);
    $('#achievement-conditions-list').append(clone);
}

function saveAchievementConditions() {
    var conditions = [];
    $('#achievement-conditions-list .br-cond-row').each(function () {
        var $row = $(this);
        var type = $row.find('.br-cond-type').val();
        if (!type) return;
        var objType = ACH_COND_OBJECT_TYPE[type];
        var object_id = objType === 'quest' ? $row.find('.br-cond-quest-picker').val()
                      : objType === 'tabi' ? $row.find('.br-cond-tabi-picker').val()
                      : '';
        var threshold_value = ACH_COND_NO_THRESHOLD.indexOf(type) === -1 ? $row.find('.br-cond-threshold').val() : '';
        conditions.push({ condition_type: type, object_id: object_id, threshold_value: threshold_value });
    });

    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'saveAchievementConditions',
            achievement_id: $('#the_achievement_id').val(),
            adventure_id: $('#the_adventure_id').val(),
            conditions_json: JSON.stringify(conditions),
            nonce: $('#achievement-conditions-nonce').val()
        },
        success: function (json) { displayAjaxResponse(json); }
    });
}

$(function () {
    // Existing saved rows are rendered with every picker hidden by default (see
    // achievement-condition-row.php) - reconcile visibility with each row's actual
    // saved condition_type once on load.
    $('#achievement-conditions-list .br-cond-type').each(function () { brAchCondRowUpdate(this); });
});

function updateAchievement() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();
    let awarded_players = [];
    $('ul.player-select li.active').each(function (index, element) {
        awarded_players.push($('input.player-id', this).val());
    });
    let achievement_data = {
        a_id: $('#the_achievement_id').val(),
        a_status: $('#the_achievement_status').val(),
        a_name: $('#the_achievement_name').val(),
        a_xp: $('#the_achievement_xp').val(),
        a_ep: $('#the_achievement_ep').val(),
        a_bloo: $('#the_achievement_bloo').val(),
        a_color: $('#the_achievement_color').val(),
        a_badge: $('#the_achievement_badge').val(),
        a_deadline: $('#the_achievement_deadline').val(),
        a_max: $('#the_achievement_max').val(),
        a_display: $('#the_achievement_display').val(),
        a_group: $('#the_achievement_group').val(),
        a_path: $('#the_achievement_path').val(),
        a_rank_condition: $('#the_achievement_rank_condition').val(),
        a_rank_level: $('#the_achievement_rank_level').val(),
        branch_group_id: $('#the_branch_group_id').val(),
        magic_code: $('#the_achievement_code').val(),
        a_content: $('#the_achievement_content').val(),
        adventure_id: $('#the_adventure_id').val(),
        awarded_players: awarded_players,
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateAchievement',
            nonce: nonce,
            achievement_data: achievement_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
////////////////////////////////////////// BRANCH SELECTOR (achievement form) ////////////////////////////////////////////

function brSelectBranch(group_id, btnEl) {
    $(btnEl).closest('.br-branch-selector').find('.br-branch-opt').removeClass('active');
    $(btnEl).addClass('active');
    $('#the_branch_group_id').val(group_id);
}

function brCreateBranchInline() {
    $('#new-branch-inline').show();
    $('#new-branch-name-inline').val('').focus();
}

function brSaveNewBranchInline() {
    let group_name = $('#new-branch-name-inline').val().trim();
    if (!group_name) return;
    let adventure_id = $('#the_adventure_id').val();

    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_update_branch_group',
            adventure_id: adventure_id,
            group_id: 0,
            group_name: group_name,
            group_status: 'publish'
        }),
        method: 'POST',
        success: function (json_text) {
            displayAjaxResponse(json_text);
            let data = JSON.parse(json_text);
            if (data.success && data.group_id) {
                let $btn = $('<button type="button" class="br-branch-opt active" data-group-id="' + data.group_id + '"></button>')
                    .text(group_name + ' ')
                    .append('<span class="br-branch-count">(0)</span>')
                    .attr('onclick', 'brSelectBranch(' + data.group_id + ', this);');
                $('.br-branch-selector .br-branch-add').before($btn);
                brSelectBranch(data.group_id, $btn.get(0));
            }
            $('#new-branch-inline').hide();
            $('#new-branch-name-inline').val('');
        }
    });
}

////////////////////////////////////////// UPDATE TEAM ////////////////////////////////////////////

function updateGuild() {
    showLoader();

    let nonce = $('#nonce').val();

    let guild_players = [];
    $('ul.player-select li.active').each(function (index, element) {
        guild_players.push($('input.player-id', this).val());
    });
    let guild_data = {
        g_id: $('#the_guild_id').val(),
        g_status: $('#the_guild_status').val(),
        g_name: $('#the_guild_name').val(),
        g_group: $('#the_guild_group').val(),
        g_capacity: $('#the_guild_capacity').val(),
        g_color: $('#the_guild_color').val(),
        g_logo: $('#the_guild_logo').val(),
        g_assign_on_login: $('#the_guild_assign_on_login').val(),
        adventure_id: $('#the_adventure_id').val(),
        guild_players: guild_players
    };

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateGuild',
            nonce: nonce,
            guild_data: guild_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
////////////////////////////////////////// UPDATE BLOCKER ////////////////////////////////////////////

function updateBlocker() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();

    let fined_players = [];
    $('ul.player-select li.active').each(function (index, element) {
        fined_players.push($('input.player-id', this).val());
    });
    let blocker_data = {
        blocker_id: $('#the_blocker_id').val(),
        blocker_cost: $('#the_blocker_cost').val(),
        blocker_description: $('#the_blocker_description').val(),
        adventure_id: $('#the_adventure_id').val(),
        fined_players: fined_players
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateBlocker',
            nonce: nonce,
            blocker_data: blocker_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

////////////////////////////////////////// UPDATE ITEM ////////////////////////////////////////////

function updateItem() {
    showLoader();
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();
    let item_data = {
        item_id: $('#the_item_id').val(),
        item_name: $('#the_item_name').val(),
        item_stock: $('#the_item_stock').val(),
        item_sold: $('#the_item_sold').val(),
        item_cost: $('#the_item_cost').val(),
        item_description: $('#the_item_description').val(),
        item_secret_description: $('#the_item_secret_description').val(),
        item_type: $('#the_item_type').val(),
        item_visibility: $('#the_item_visibility').val(),
        item_badge: $('#the_item_badge').val(),
        item_secret_badge: $('#the_item_secret_badge').val(),
        item_max: $('#the_item_player_max').val(),
        item_level: $('#the_item_min_level').val(),
        item_category: $('#the_item_category').val(),
        adventure_id: $('#the_adventure_id').val(),
        item_start_date: $('#the_item_start_date').val(),
        item_deadline: $('#the_item_deadline').val(),
        achievement_id: $('#the_achievement_id').val(),
        item_x: $('#the_item_x').val(),
        item_y: $('#the_item_y').val(),
        item_z: $('#the_item_z').val(),
        tabi_id: $('#the_item_tabi').val(),
        item_tremendous_amount: $('#the_item_tremendous_amount').val(),
        item_tremendous_label: $('#the_item_tremendous_label').val(),
        item_tremendous_products: (function() {
            try { return JSON.parse($('#the_item_tremendous_products').val() || '[]'); } catch (e) { return []; }
        })(),
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateItem',
            nonce: nonce,
            item_data: item_data
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}


////////////////////////////////////////// SUBMIT PLAYER WORK ////////////////////////////////////////////

function validatePlayerWork(nextStep) {
    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    $('#pp-content-counter').html($('#the_pp_content').val());

    let pp_links = $('#pp-content-counter a').length;
    let pp_images = $('#pp-content-counter img').length;

    let pp_data = {
        quest_id: $('#the_quest_id').val(),
        adventure_id: $('#the_adventure_id').val(),
        pp_content: $('#the_pp_content').val(),
        pp_links: pp_links,
        pp_images: pp_images,
        pp_type: $('#the_pp_type').val()
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'validatePlayerWork',
            pp_data: pp_data
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.continue) {
                jumpToStep(nextStep);
            } else {
                $("#feedback .content").html(data.message);
                $("#feedback").addClass('active');
            }
        }
    });
}


function submitPlayerWork() {

    if (typeof tinyMCE == 'object' && typeof tinyMCE.triggerSave == 'function') {
        tinyMCE.triggerSave();
    }
    let nonce = $('#nonce').val();
    let override_nonce = $('#override_nonce').val();

    $('#pp-content-counter').html($('#the_pp_content').val());

    let pp_links = $('#pp-content-counter a').length;
    let pp_images = $('#pp-content-counter img').length;

    let pp_data = {
        quest_id: $('#the_quest_id').val(),
        adventure_id: $('#the_adventure_id').val(),
        pp_content: $('#the_pp_content').val(),
        pp_links: pp_links,
        pp_images: pp_images,
        pp_type: $('#the_pp_type').val()
    };
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'submitPlayerWork',
            nonce: nonce,
            override_nonce: override_nonce,
            pp_data: pp_data
        }),
        method: "POST",
        success: function (data_received) {
            if (isJson(data_received)) {
                displayAjaxResponse(data_received);
            } else {
                $("#feedback .content").html(data_received);
                let flipTimeout = setTimeout(function () {
                    $("#feedback").addClass('active');
                }, 100);
            }
            setCurrentQuest(0, 1);
            let videoElements = document.querySelectorAll("video");
            for (let videoEl of videoElements) {
                videoEl.pause();
            }

        }
    });
}


////////////////////////////////////////// START ATTEMPT ////////////////////////////////////////////

function startAttempt() {
    $('#start-attempt-btn').prop('disabled', true);
    let nonce = $('#nonce').val();
    let challenge_id = $('#the_challenge_id').val();
    let adventure_id = $('#the_adventure_id').val();
    let attempt_cost = $('#the_attempt_cost').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'startAttempt',
            nonce: nonce,
            challenge_id: challenge_id,
            adventure_id: adventure_id,
            attempt_cost: attempt_cost
        }),
        method: "POST",
        success: function (json_text) {
            let data = JSON.parse(json_text);
            if (data.att_id) {
                $("#feedback .content").html(data.message);
                $("#feedback").addClass('active');
                if (!$('.overlay-bg').is(':visible')) {
                    $('.overlay-bg').fadeIn('fast');
                }
                $("#the_attempt_id").val(data.att_id);
                $("#feedback").click(function () {
                    hideAllOverlay();
                    $("#feedback").removeClass('active').unbind('click');
                    if ($("#the_time_limit").val() > 0) {
                        countdown();
                    }
                    $('#challenge').removeClass('idle').addClass('running');
                });
                $("#start-attempt-btn").prop("disabled", true);
            } else {
                $("#feedback .content").html(data.message);
                $("#feedback").addClass('active');
                if (!$('.overlay-bg').is(':visible')) {
                    $('.overlay-bg').fadeIn('fast');
                }
                $("#feedback").click(function () {
                    $("#feedback").removeClass('active').unbind('click');
                    hideAllOverlay();
                });
            }
        }
    });
}

function navToQuestion(id) {
    animateScroll('#question-' + id);
    $('.question-number').removeClass('current');
    $('#question-number-' + id).addClass('current');
    $('#question-number-mobile-' + id).addClass('current');
}

function nextQuestion() {
    let totalQuestions = document.getElementsByClassName('challenge-question');
    let cur = parseInt($('#current-question').val());
    if (cur < totalQuestions.length - 1) {
        showQuestion(cur + 1);
    }
}

function prevQuestion() {
    let cur = parseInt($('#current-question').val());
    if (cur > 0) {
        showQuestion(cur - 1);
    }
}

function showQuestion(id) {
    let questions = document.getElementsByClassName('challenge-question');
    let who = questions[id];

    if (id <= 0) {
        $('#prev-question-button').addClass('inactive');
    } else {
        $('#prev-question-button').removeClass('inactive');
    }
    if (id >= questions.length - 1) {
        $('#next-question-button').addClass('inactive');
    } else {
        $('#next-question-button').removeClass('inactive');
    }
    $('.challenge-question').removeClass('current');
    who.classList.add('current');
    $('#current-question').val(id);
}
////////////////////////////////////////// Submit Answer ////////////////////////////////////////////

function submitAnswer(answer_id, question_id) {
    let attempt_id = $('#the_attempt_id').val();
    let adventure_id = $('#the_adventure_id').val();
    let challenge_id = $('#the_challenge_id').val();
    let question_type = $("#question-" + question_id + " .question-type").val();
    let answer_value = [];
    if (question_type == 'single') {
        if ($("li#op" + answer_id + "-" + question_id).hasClass('active')) {
            $("li#op" + answer_id + "-" + question_id).removeClass('active');
            answer_id = 0;
        } else {
            $("li#op" + answer_id + "-" + question_id).addClass('active').siblings().removeClass('active');
        }
    } else if (question_type == 'multiple') {
        if ($("li#op" + answer_id + "-" + question_id).hasClass('active')) {
            $("li#op" + answer_id + "-" + question_id).removeClass('active');
        } else {
            $("li#op" + answer_id + "-" + question_id).addClass('active');
        }
        answer_id = 0;
        $("#question-" + question_id + " .question-options li.active").each(function (index, element) {
            answer_value.push($('input.answer-id', this).val());
        });
    }
    $("#question-number-" + question_id).addClass('answered');
    $("#question-number-mobile-" + question_id).addClass('answered');

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'submitAnswer',
            question_id: question_id,
            challenge_id: challenge_id,
            attempt_id: attempt_id,
            answer_value: answer_value,
            answer_id: answer_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}

////////////////////////////////////////// Grade Challenge ////////////////////////////////////////////

function gradeChallenge() {
    let attempt_id = $('#the_attempt_id').val();
    let challenge_id = $('#the_challenge_id').val();
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'gradeChallenge',
            challenge_id: challenge_id,
            attempt_id: attempt_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            setCurrentQuest(0, 1);
            if (isJson(data_received)) {
                displayAjaxResponse(data_received);
            } else {
                $("#feedback .content").html(data_received);
                let flipTimeout = setTimeout(function () {
                    $("#feedback").addClass('active');
                    $("#challenge").removeClass('running').addClass('complete');
                }, 100);
            }

        }
    });
}
////////////////////////////////////////// Fail Quest ////////////////////////////////////////////

function failQuest() {
    let quest_id = $('#the_quest_id').val();
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'failQuest',
            quest_id: quest_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            $("#feedback .content").html(data_received);
            let flipTimeout = setTimeout(function () {
                $("#feedback").addClass('active');
            }, 100);
        }
    });
}
////////////////////////////////////////// answerEncounter ////////////////////////////////////////////
function answerEncounter(option) {
    showLoader('small');
    let enc_id = $('#current-encounter-id').val();
    let value = $("#enc-opt-" + option).text();
    $('.encounter-options button').prop('disabled', true);
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'answerEncounter',
            adventure_id: $("#the_adventure_id").val(),
            enc_id: enc_id,
            value: value
        }),
        method: "POST",
        success: function (json_text) {
            let data = JSON.parse(json_text);
            if (data.success) {
                $('#micro-status-player-ep .end-value, #status-player-ep .end-value').val(parseInt(data.EP));
                animateNumber('#micro-status-player-ep, #status-player-ep');
                let percEP = data.EP * 100 / $('#player-max-ep').val();
                $('#micro-status-ep-progress-bar, #profile-box-ep-progress-bar').css('width', percEP + '%');

                $("#notify-message ul.content").append(data.message);
                $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                    $(this).remove();
                    //$("#feedback, #overlay-content").removeClass('active');
                });
                randomEncounter();
            } else {
                $("#notify-message ul.content").append(data.message);
                $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                    $(this).remove();
                    //$("#feedback, #overlay-content").removeClass('active');
                });
                randomEncounter();
            }
        }
    });
}
////////////////////////////////////////// submitMagicCode ////////////////////////////////////////////
function submitMagicCode() {
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'magicCode',
            adventure_id: $("#the_adventure_id").val(),
            magic_code: $("#magic-code").val()
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}
////////////////////////////////////////// choosePath - ThroughMagicCode ////////////////////////////////////////////
function preChoosePath(step, path, label) {
    $('#path-choices-' + step + ' .path').removeClass('selected');
    $('#path-' + path).addClass('selected');
    $('#path-choices-' + step + ' input.selected-path').val(path);
    $('#chosen-path-text-value .step-tag-text').text(label);
}
////////////////////////////////////////// choosePath - ThroughMagicCode ////////////////////////////////////////////
function choosePath(step, next) {
    showLoader('small');
    let path = $('#path-choices-' + step + ' input.selected-path').val();
    if (path) {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'choosePath',
                adventure_id: $("#the_adventure_id").val(),
                path: path
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
                jumpToStep(next);
            }
        });
    } else {
        notification('#must-choose-' + step);
        hideAllOverlay();
    }
}
////////////////////////////////////////// triggerAchievement ////////////////////////////////////////////
function triggerAchievement(achievement_id, player_id) {
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'triggerAchievement',
            achievement_id: achievement_id,
            player_id: player_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
            let a_data = JSON.parse(json_text);
            if (a_data.action == 'assign') {
                $("#player-achievement-" + player_id + ", #player-achievement-list-" + player_id).addClass('active');
            } else {
                $("#player-achievement-" + player_id + ", #player-achievement-list-" + player_id).removeClass('active');
            }

        }
    });
}

function triggerAchievements(status = 'on') {
    let adventure_id = $('#the_adventure_id').val();
    let achievement_id = $('#the_achievement_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'triggerAchievements',
            achievement_id: achievement_id,
            adventure_id: adventure_id,
            status: status
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
            let a_data = JSON.parse(json_text);
            if (a_data.action == 'assigned-all') {
                $(".player-achievement-item").addClass('active');
            } else if (a_data.action == 'removed-all') {
                $(".player-achievement-item").removeClass('active');
            }
        }
    });
}
////////////////////////////////////////// triggerGuild ////////////////////////////////////////////
function triggerGuild(guild_id, player_id) {
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'triggerGuild',
            guild_id: guild_id,
            player_id: player_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
            let a_data = JSON.parse(json_text);
            if (a_data.action == 'assign') {
                $("#player-guild-" + player_id + ", #player-guild-list-" + player_id).addClass('active');
            } else {
                $("#player-guild-" + player_id + ", #player-guild-list-" + player_id).removeClass('active');
            }

        }
    });
}
////////////////////////////////////////// assignBulkUsersToGuild ////////////////////////////////////////////
function assignBulkUsersToGuild() {
    var fileInput = document.getElementById('the_csv_file_with_players');
    if (!fileInput || !fileInput.files[0]) {
        alert('Please select a CSV file first.');
        return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
        var lines = e.target.result.split(/[\r\n]+/);
        var emails = [];
        lines.forEach(function (line) {
            // Support single-column or multi-column CSV; grab first cell, strip quotes
            var cell = line.split(',')[0].replace(/['"]/g, '').trim().toLowerCase();
            if (cell && cell.indexOf('@') > -1 && cell !== 'email') {
                emails.push(cell);
            }
        });
        if (!emails.length) {
            alert('No valid email addresses found in the file.');
            return;
        }
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'bulkAssignGuild',
                guild_id: $('#the_guild_id').val(),
                adventure_id: $('#the_adventure_id').val(),
                nonce: $('#nonce').val(),
                emails: emails
            },
            method: 'POST',
            success: function (json_text) {
                displayAjaxResponse(json_text);
                var d = JSON.parse(json_text);
                if (d.success && d.assigned_ids) {
                    d.assigned_ids.forEach(function (pid) {
                        $('#player-guild-list-' + pid).addClass('active');
                    });
                }
                // Clear the file input so the same file can be re-submitted if needed
                fileInput.value = '';
            }
        });
    };
    reader.readAsText(fileInput.files[0]);
}
////////////////////////////////////////// brRunBatchPoll ////////////////////////////////////////////
// Generic "process a small batch -> report progress -> repeat until remaining
// is 0" driver, shared by any bulk operation that queues work server-side and
// processes it a few rows at a time (achievement CSV assignment today). Never
// moves to the next batch until the current one's response is confirmed, and
// a dropped/failed request just retries the same batch rather than losing
// progress - every row's outcome is already committed to the DB by the time
// the response comes back.
function brRunBatchPoll(ajaxData, total, onProgress, onDone, onError) {
    function poll() {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ajaxData,
            method: 'POST',
            success: function (json_text) {
                var r;
                try { r = JSON.parse(json_text); } catch (e) { r = null; }
                if (!r || !r.success) {
                    if (onError) onError(r);
                    return;
                }
                var remaining = r.remaining || 0;
                if (onProgress) onProgress(r, total - remaining, total);
                if (remaining > 0) {
                    setTimeout(poll, 50);
                } else if (onDone) {
                    onDone(r);
                }
            },
            error: function () {
                setTimeout(poll, 2000);
            }
        });
    }
    poll();
}

////////////////////////////////////////// assignBulkUsersToAchievement ////////////////////////////////////////////
function assignBulkUsersToAchievement() {
    var fileInput = document.getElementById('the_csv_file_with_players');
    if (!fileInput || !fileInput.files[0]) {
        alert('Please select a CSV file first.');
        return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
        var lines = e.target.result.split(/[\r\n]+/);
        var emails = [];
        lines.forEach(function (line) {
            var cell = line.split(',')[0].replace(/['"]/g, '').trim().toLowerCase();
            if (cell && cell.indexOf('@') > -1 && cell !== 'email') {
                emails.push(cell);
            }
        });
        if (!emails.length) {
            alert('No valid email addresses found in the file.');
            return;
        }
        brOpConsoleOpen('Bulk Assign Achievement');
        brOpConsoleLog('Found ' + emails.length + ' email address' + (emails.length !== 1 ? 'es' : '') + ' in CSV…', 'info');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'bulkAssignAchievement',
                achievement_id: $('#the_achievement_id').val(),
                adventure_id: $('#the_adventure_id').val(),
                emails: emails
            },
            method: 'POST',
            success: function (json_text) {
                var d = JSON.parse(json_text);
                if (!d.success) {
                    brOpConsoleLog((d.message || 'Error starting batch.'), 'error');
                    brOpConsoleDone();
                    return;
                }
                var total = d.total;
                var totalAssigned = 0;
                var totalAlreadyHas = 0;
                var notFoundEmails = [];
                var belowLevelEmails = [];
                var requiredLevel = null;
                brOpConsoleLog('Batch queued — processing ' + total + ' email' + (total !== 1 ? 's' : '') + '…', 'info');
                brRunBatchPoll(
                    { action: 'bulkAssignAchievementBatch', achievement_id: d.achievement_id, adventure_id: d.adventure_id },
                    total,
                    function (r, done) {
                        brOpConsoleSetProgress(done, total);
                        totalAssigned   += (r.assigned   || 0);
                        totalAlreadyHas += (r.already_has || 0);
                        (r.not_found_emails || []).forEach(function (em) { notFoundEmails.push(em); });
                        // A level rank is only handed to players who reached that level;
                        // the ones skipped are named rather than quietly missing.
                        if (r.required_level) requiredLevel = r.required_level;
                        (r.below_level_emails || []).forEach(function (em) { belowLevelEmails.push(em); });
                        (r.assigned_emails || []).forEach(function (em) {
                            brOpConsoleLog(em, 'success');
                        });
                        (r.assigned_ids || []).forEach(function (pid) {
                            $('#player-achievement-' + pid).addClass('active');
                        });
                    },
                    function () {
                        var summary = 'Done — ' + totalAssigned + ' assigned, ' + totalAlreadyHas + ' already had it';
                        if (belowLevelEmails.length) {
                            summary += ', ' + belowLevelEmails.length + ' below Level ' + requiredLevel;
                        }
                        if (notFoundEmails.length) {
                            summary += ', ' + notFoundEmails.length + ' not found.';
                        } else {
                            summary += '.';
                        }
                        brOpConsoleDone(summary);
                        if (belowLevelEmails.length) {
                            brOpConsoleLog('Not yet Level ' + requiredLevel + ', so this rank was not awarded (' + belowLevelEmails.length + '):', 'warn');
                            belowLevelEmails.forEach(function (em) {
                                brOpConsoleLog(em, 'warn');
                            });
                        }
                        if (notFoundEmails.length) {
                            brOpConsoleLog('Emails not found in this adventure (' + notFoundEmails.length + '):', 'warn');
                            notFoundEmails.forEach(function (em) {
                                brOpConsoleLog(em, 'warn');
                            });
                        }
                    },
                    function () {
                        brOpConsoleLog('Error processing batch — reload and try again.', 'error');
                        brOpConsoleDone();
                    }
                );
            }
        });
        fileInput.value = '';
    };
    reader.readAsText(fileInput.files[0]);
}

////////////////////////////////////////// BULK PLAYER IMPORT ////////////////////////////////////////////
// Front-end driven CSV import (page-new-adventure.php). The browser parses the whole
// file - there is no 50-row server cap any more - streams it back a few rows at a
// time and prints every row's outcome in a live terminal. When the sweep ends it asks
// the database which addresses actually landed and automatically re-runs the ones that
// didn't, so a single file of 1000+ players imports in one uninterrupted pass.

var BRImport = {
    rows: [],        // every parsed row, in file order
    queue: [],       // what the current sweep still has to send
    results: {},     // row index -> server result
    batch: 8,
    cursor: 0,
    done: 0,
    total: 0,
    pass: 1,
    retries: 0,
    running: false,
    cancelled: false,
    counts: { created: 0, enrolled: 0, already: 0, failed: 0 },
    t0: 0
};

// Column aliases, matched exactly against the normalised header cell (never as a
// substring - "work sub function" must not be swallowed by "function").
var BR_IMPORT_HEADERS = {
    nickname:          ['nickname', 'username', 'user', 'user_login', 'userlogin', 'login', 'usuario', 'apodo'],
    password:          ['password', 'pass', 'contrasena', 'contraseña', 'clave'],
    email:             ['email', 'e-mail', 'mail', 'correo', 'correo electronico', 'correo electrónico'],
    firstname:         ['first name', 'firstname', 'first_name', 'name', 'nombre', 'given name'],
    lastname:          ['last name', 'lastname', 'last_name', 'surname', 'apellido', 'apellidos', 'family name'],
    lang:              ['lang', 'language', 'locale', 'idioma'],
    guild:             ['guild', 'guild name', 'guild_name', 'guildname', 'team', 'group', 'equipo', 'grupo'],
    gender:            ['gender', 'sex', 'genero', 'género'],
    work_level:        ['work level', 'work_level', 'worklevel', 'level', 'nivel'],
    work_function:     ['work function', 'work_function', 'function', 'funcion', 'función'],
    work_sub_function: ['work sub function', 'work_sub_function', 'sub function', 'subfunction', 'sub-function'],
    job_profile:       ['job profile', 'job_profile', 'profile', 'perfil'],
    buisness_pillar:   ['business pillar', 'buisness pillar', 'business_pillar', 'buisness_pillar', 'pillar', 'pilar'],
    work_cluster:      ['work cluster', 'work_cluster', 'cluster'],
    work_country:      ['work country', 'work_country', 'country', 'pais', 'país'],
    work_location:     ['work location', 'work_location', 'location', 'ubicacion', 'ubicación', 'office']
};

// Fallback for files exported before the columns were named - this is the exact
// layout the old server-side uploadBulkUsers() read positionally.
var BR_IMPORT_POSITIONS = {
    nickname: 0, password: 1, email: 2, firstname: 3, lastname: 4, lang: 5,
    gender: 8, work_level: 9, work_function: 10, work_sub_function: 11, job_profile: 12,
    buisness_pillar: 13, work_cluster: 14, work_country: 15, work_location: 16
};

function brEsc(s) {
    return $('<div>').text(s === null || s === undefined ? '' : String(s)).html();
}

function brCsvCell(v) {
    return '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
}

// Full RFC-4180 style parser: quoted fields, escaped quotes and newlines inside
// cells all survive, and the delimiter is sniffed from the header line.
function brParseCSV(text) {
    if (text.charCodeAt(0) === 0xFEFF) text = text.slice(1);

    var firstLine = text.split(/\r\n|\n|\r/)[0] || '';
    var counts = { ',': 0, ';': 0, '\t': 0 };
    var quoted = false;
    for (var i = 0; i < firstLine.length; i++) {
        var ch = firstLine.charAt(i);
        if (ch === '"') quoted = !quoted;
        else if (!quoted && counts.hasOwnProperty(ch)) counts[ch]++;
    }
    var delim = ',';
    if (counts[';'] > counts[delim]) delim = ';';
    if (counts['\t'] > counts[delim]) delim = '\t';

    var rows = [], row = [], field = '', inQuotes = false;
    for (var p = 0; p < text.length; p++) {
        var c = text.charAt(p);
        if (inQuotes) {
            if (c === '"') {
                if (text.charAt(p + 1) === '"') { field += '"'; p++; }
                else inQuotes = false;
            } else field += c;
        } else if (c === '"') {
            inQuotes = true;
        } else if (c === delim) {
            row.push(field); field = '';
        } else if (c === '\n' || c === '\r') {
            if (c === '\r' && text.charAt(p + 1) === '\n') p++;
            row.push(field); field = '';
            rows.push(row); row = [];
        } else field += c;
    }
    if (field !== '' || row.length) { row.push(field); rows.push(row); }

    return rows.filter(function (r) {
        for (var i = 0; i < r.length; i++) { if (String(r[i]).trim() !== '') return true; }
        return false;
    });
}

function brImportMapRows(matrix) {
    var headerRow = matrix[0] || [];
    var norm = [];
    for (var h = 0; h < headerRow.length; h++) {
        norm.push(String(headerRow[h]).replace(/^\uFEFF/, '').trim().toLowerCase().replace(/\s+/g, ' '));
    }

    var map = {}, matched = 0;
    Object.keys(BR_IMPORT_HEADERS).forEach(function (key) {
        for (var i = 0; i < norm.length; i++) {
            if (BR_IMPORT_HEADERS[key].indexOf(norm[i]) > -1) { map[key] = i; matched++; return; }
        }
    });

    var mode, body;
    if (map.email !== undefined) {
        mode = 'named columns';
        body = matrix.slice(1);
    } else {
        mode = 'legacy column order';
        map = BR_IMPORT_POSITIONS;
        matched = 0;
        // No recognisable header, but if the legacy email column already holds an
        // address then the first line is data and must not be thrown away.
        body = String(headerRow[2] || '').indexOf('@') > -1 ? matrix : matrix.slice(1);
    }

    var rows = [], seen = {}, guilds = {}, duplicates = 0, invalid = 0;
    body.forEach(function (cells) {
        // Start every known field at '' so a column the file simply doesn't have
        // still posts as an empty string rather than the literal "undefined".
        var row = { index: rows.length };
        Object.keys(BR_IMPORT_HEADERS).forEach(function (key) { row[key] = ''; });
        Object.keys(map).forEach(function (key) {
            var idx = map[key];
            if (idx !== undefined && cells[idx] !== undefined) row[key] = String(cells[idx]).trim();
        });
        if (!row.email && !row.nickname) return;

        var key = row.email.toLowerCase();
        if (key && seen[key]) { duplicates++; return; }
        if (key) seen[key] = true;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(row.email)) invalid++;
        if (row.guild) guilds[row.guild.replace(/\s+/g, ' ').trim().toLowerCase()] = true;
        rows.push(row);
    });

    return {
        rows: rows, mode: mode, matched: matched, duplicates: duplicates, invalid: invalid,
        hasGuildColumn: map.guild !== undefined,
        guildCount: Object.keys(guilds).length
    };
}

function brImportPickFile() {
    var input = document.getElementById('the_csv_file_with_users');
    var $summary = $('#br-import-summary');
    var $start = $('#br-import-start');

    BRImport.rows = [];
    $start.prop('disabled', true);

    if (!input || !input.files || !input.files[0]) { $summary.html(''); return; }
    var file = input.files[0];
    $summary.html('<span class="br-import-note">' + brEsc('Reading ' + file.name + '…') + '</span>');

    var reader = new FileReader();
    reader.onload = function (e) {
        var text = e.target.result;
        // U+FFFD means the file was not UTF-8 - almost always a Windows-1252 export.
        if (text.indexOf('\uFFFD') > -1) {
            var legacy = new FileReader();
            legacy.onload = function (e2) { brImportShowSummary(e2.target.result, file); };
            legacy.readAsText(file, 'windows-1252');
            return;
        }
        brImportShowSummary(text, file);
    };
    reader.readAsText(file);
}

function brImportShowSummary(text, file) {
    var parsed = brImportMapRows(brParseCSV(text));
    BRImport.rows = parsed.rows;

    var $summary = $('#br-import-summary');
    if (!parsed.rows.length) {
        $summary.html('<span class="br-import-note br-import-note-bad">' +
            brEsc('No usable rows found in ' + file.name + '. Check the file has an Email column.') + '</span>');
        $('#br-import-start').prop('disabled', true);
        return;
    }

    var html = '<div class="br-import-note br-import-note-ok"><strong>' + brEsc(file.name) + '</strong> — ' +
        parsed.rows.length + ' players ready <span class="br-import-mode">(' + brEsc(parsed.mode) + ')</span></div>';
    if (parsed.duplicates) {
        html += '<div class="br-import-note br-import-note-warn">' + parsed.duplicates +
            ' duplicate email rows will be skipped</div>';
    }
    if (parsed.invalid) {
        html += '<div class="br-import-note br-import-note-warn">' + parsed.invalid +
            ' rows have a malformed email and will be reported as failed</div>';
    }
    if (parsed.guildCount) {
        html += '<div class="br-import-note">' + parsed.guildCount +
            ' distinct guild' + (parsed.guildCount === 1 ? '' : 's') +
            ' named — any that do not exist yet will be created</div>';
    } else if (parsed.hasGuildColumn) {
        html += '<div class="br-import-note">Guild column found but empty — guilds untouched</div>';
    }
    $summary.html(html);
    $('#br-import-start').prop('disabled', false);
}

////////////////////////////////////////// terminal ////////////////////////////////////////////
function brImportLog(text, cls) {
    $('#br-import-terminal').append('<div class="br-imp-line ' + (cls || '') + '">' + brEsc(text) + '</div>');
    brImportScroll();
}

function brImportScroll() {
    var t = document.getElementById('br-import-terminal');
    if (t && !BRImport.freezeScroll) t.scrollTop = t.scrollHeight;
}

function brImportLogPending(row) {
    var id = 'br-imp-line-' + row.index;
    var label = row.email || row.nickname || ('row ' + (row.index + 1));
    var html = '<div class="br-imp-line br-imp-pending" id="' + id + '">' +
        '<span class="br-imp-verb">adding</span>' +
        '<span class="br-imp-target">' + brEsc(label) + '</span>' +
        '<span class="br-imp-dots"></span>' +
        '<span class="br-imp-result">…</span></div>';
    var $existing = $('#' + id);
    if ($existing.length) $existing.replaceWith(html);
    else $('#br-import-terminal').append(html);
    brImportScroll();
}

function brImportPatchLine(index, status, detail) {
    var $line = $('#br-imp-line-' + index);
    if (!$line.length) return;
    var labels = {
        created: 'done', enrolled: 'enrolled', already: 'skipped',
        failed: 'FAILED', retry: 'retrying'
    };
    $line.removeClass('br-imp-pending br-imp-created br-imp-enrolled br-imp-already br-imp-failed br-imp-retry')
        .addClass('br-imp-' + status);
    $line.find('.br-imp-result').text(labels[status] || status);
    $line.find('.br-imp-detail').remove();
    if (detail) $line.append('<span class="br-imp-detail">' + brEsc(detail) + '</span>');
}

function brImportTime(seconds) {
    if (seconds < 60) return seconds + 's';
    var m = Math.floor(seconds / 60);
    return m + 'm ' + (seconds % 60) + 's';
}

function brImportRecount() {
    var c = { created: 0, enrolled: 0, already: 0, failed: 0 };
    var guilds = {};
    Object.keys(BRImport.results).forEach(function (k) {
        var r = BRImport.results[k];
        var s = r.status || 'failed';
        if (c[s] === undefined) c[s] = 0;
        c[s]++;
        if (r.guild_created && r.guild) guilds[r.guild] = true;
    });
    BRImport.counts = c;
    BRImport.guildsCreated = Object.keys(guilds);
    $('#br-imp-count-created').text(c.created);
    $('#br-imp-count-enrolled').text(c.enrolled + c.already);
    $('#br-imp-count-failed').text(c.failed);
    $('#br-imp-count-guilds').text(BRImport.guildsCreated.length);
}

function brImportProgress() {
    brImportRecount();
    var pct = BRImport.total ? Math.round((BRImport.done / BRImport.total) * 100) : 0;
    $('#br-import-bar').css('width', pct + '%');

    var elapsed = (new Date().getTime() - BRImport.t0) / 1000;
    var rate = elapsed > 0 ? BRImport.done / elapsed : 0;
    var text = BRImport.done + ' / ' + BRImport.total + '  ·  ' + pct + '%';
    if (BRImport.done < BRImport.total && rate > 0) {
        text += '  ·  ~' + brImportTime(Math.round((BRImport.total - BRImport.done) / rate)) + ' left';
    }
    $('#br-import-progress-text').text(text);
}

////////////////////////////////////////// runner ////////////////////////////////////////////
function brStartPlayerImport() {
    if (BRImport.running) return;
    if (!BRImport.rows.length) {
        $('#br-import-summary').html('<span class="br-import-note br-import-note-bad">' +
            'Select a CSV file first.</span>');
        return;
    }
    BRImport.running = true;
    BRImport.cancelled = false;
    BRImport.results = {};
    BRImport.pass = 1;
    BRImport.retries = 0;
    BRImport.queue = BRImport.rows.slice();
    BRImport.cursor = 0;
    BRImport.done = 0;
    BRImport.total = BRImport.queue.length;
    BRImport.t0 = new Date().getTime();

    $('#br-import-overlay').addClass('active');
    $('#br-import-terminal').html('');
    $('#br-import-bar').css('width', '0%');
    // Toggle the class rather than jQuery show()/hide() - these are .br-btn
    // (inline-flex) and an inline display would flatten the icon alignment.
    $('#br-import-cancel').removeClass('br-initially-hidden');
    $('#br-import-close, #br-import-report, #br-import-reload').addClass('br-initially-hidden');
    brImportProgress();

    brImportLog('BlueRabbit player import', 'br-imp-head');
    brImportLog('adventure #' + $('#the_adventure_id').val() + ' · ' + BRImport.total +
        ' rows queued · ' + BRImport.batch + ' per request', 'br-imp-dim');
    brImportLog('', '');
    brImportNextBatch();
}

function brCancelPlayerImport() {
    if (!BRImport.running) { brCloseImportConsole(); return; }
    BRImport.cancelled = true;
    brImportLog('cancel requested — finishing the batch in flight…', 'br-imp-warn');
}

function brCloseImportConsole() {
    if (BRImport.running) return;
    $('#br-import-overlay').removeClass('active');
}

function brImportNextBatch() {
    if (BRImport.cancelled) { brImportFinish(true); return; }
    if (BRImport.cursor >= BRImport.queue.length) { brImportVerify(); return; }

    var batch = BRImport.queue.slice(BRImport.cursor, BRImport.cursor + BRImport.batch);
    batch.forEach(function (row) { brImportLogPending(row); });

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'brImportPlayersBatch',
            nonce: $('#import_players_nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            rows: batch
        },
        method: 'POST',
        timeout: 180000,
        success: function (json_text) {
            var d = null;
            try { d = JSON.parse(json_text); } catch (e) { d = null; }
            if (!d || !d.success) {
                brImportAbort(d && d.message ? d.message : 'the server rejected the batch');
                return;
            }
            BRImport.retries = 0;
            (d.results || []).forEach(function (r) {
                BRImport.results[r.index] = r;
                brImportPatchLine(r.index, r.status || 'failed', r.detail || '');
            });
            BRImport.cursor += batch.length;
            BRImport.done = BRImport.cursor;
            brImportProgress();
            setTimeout(brImportNextBatch, 30);
        },
        error: function (xhr, status) {
            // Re-sending is always safe: a row that already landed comes back as
            // 'already', so a dropped connection costs a retry, never a duplicate.
            BRImport.retries++;
            if (BRImport.retries > 8) {
                brImportAbort('connection lost and 8 retries failed (' + status + ')');
                return;
            }
            batch.forEach(function (row) {
                brImportPatchLine(row.index, 'retry', 'connection lost, retry ' + BRImport.retries + '/8');
            });
            setTimeout(brImportNextBatch, 2000);
        }
    });
}

function brImportAbort(reason) {
    brImportLog('', '');
    brImportLog('import stopped: ' + reason, 'br-imp-bad');
    brImportFinish(true);
}

// Nothing is trusted until the database confirms it: every address in the file is
// checked against the enrolment table, and whatever is missing gets swept again.
function brImportVerify() {
    var emails = [];
    BRImport.rows.forEach(function (r) { if (r.email) emails.push(r.email); });
    if (!emails.length) { brImportFinish(false); return; }

    brImportLog('', '');
    brImportLog('— sweep ' + BRImport.pass + ' done · verifying ' + emails.length +
        ' addresses against the database —', 'br-imp-head');

    var chunks = [];
    for (var i = 0; i < emails.length; i += 200) chunks.push(emails.slice(i, i + 200));

    var missing = [], ci = 0, chunkRetries = 0;
    function nextChunk() {
        if (ci >= chunks.length) { brImportAfterVerify(missing, emails.length); return; }
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'brVerifyImportedPlayers',
                nonce: $('#import_players_nonce').val(),
                adventure_id: $('#the_adventure_id').val(),
                emails: chunks[ci]
            },
            method: 'POST',
            timeout: 120000,
            success: function (json_text) {
                var d = null;
                try { d = JSON.parse(json_text); } catch (e) { d = null; }
                if (!d || !d.success) {
                    brImportAbort(d && d.message ? d.message : 'verification failed');
                    return;
                }
                chunkRetries = 0;
                missing = missing.concat(d.missing || []);
                ci++;
                brImportLog('verified ' + Math.min(ci * 200, emails.length) + ' / ' + emails.length, 'br-imp-dim');
                setTimeout(nextChunk, 20);
            },
            error: function () {
                chunkRetries++;
                if (chunkRetries > 8) { brImportAbort('could not verify the import'); return; }
                setTimeout(nextChunk, 2000);
            }
        });
    }
    nextChunk();
}

function brImportAfterVerify(missing, checked) {
    var missingMap = {};
    missing.forEach(function (e) { missingMap[String(e).toLowerCase()] = true; });

    var retryRows = BRImport.rows.filter(function (r) {
        return r.email && missingMap[r.email.toLowerCase()];
    });

    if (!retryRows.length) {
        brImportLog('all ' + checked + ' addresses are registered and enrolled ✔', 'br-imp-ok');
        brImportFinish(false);
        return;
    }
    if (BRImport.pass >= 3) {
        brImportLog(retryRows.length + ' rows still missing after 3 passes:', 'br-imp-bad');
        retryRows.forEach(function (r) {
            var res = BRImport.results[r.index];
            brImportLog('  ' + r.email + ' — ' + ((res && res.detail) || 'unknown error'), 'br-imp-bad');
        });
        brImportFinish(false);
        return;
    }

    // Drop the ids of the first attempt's lines so the repair sweep prints fresh
    // ones at the bottom of the terminal instead of silently rewriting history
    // hundreds of lines further up.
    retryRows.forEach(function (r) { $('#br-imp-line-' + r.index).removeAttr('id'); });

    BRImport.pass++;
    BRImport.queue = retryRows;
    BRImport.cursor = 0;
    BRImport.done = 0;
    BRImport.total = retryRows.length;
    BRImport.t0 = new Date().getTime();
    brImportLog(retryRows.length + ' rows did not land — running repair sweep ' + BRImport.pass, 'br-imp-warn');
    brImportLog('', '');
    setTimeout(brImportNextBatch, 200);
}

function brImportFinish(stopped) {
    BRImport.running = false;
    brImportRecount();
    if (!stopped) $('#br-import-bar').css('width', '100%');

    var c = BRImport.counts;
    var processed = c.created + c.enrolled + c.already + c.failed;
    brImportLog('', '');
    brImportLog(stopped ? '— import stopped —' : '— import complete —', 'br-imp-head');
    brImportLog('created  : ' + c.created + '   (new accounts)', 'br-imp-created');
    brImportLog('enrolled : ' + c.enrolled + '   (existing accounts added to this adventure)', 'br-imp-enrolled');
    brImportLog('skipped  : ' + c.already + '   (already in this adventure)', 'br-imp-already');
    brImportLog('failed   : ' + c.failed, c.failed ? 'br-imp-failed' : 'br-imp-dim');
    if (BRImport.guildsCreated && BRImport.guildsCreated.length) {
        brImportLog('guilds   : ' + BRImport.guildsCreated.length + ' created', 'br-imp-ok');
        BRImport.guildsCreated.forEach(function (g) { brImportLog('           + ' + g, 'br-imp-dim'); });
    }
    brImportLog('rows     : ' + processed + ' / ' + BRImport.rows.length + ' processed', 'br-imp-dim');
    brImportLog('elapsed  : ' + brImportTime(Math.round((new Date().getTime() - BRImport.t0) / 1000)) +
        ' (this sweep)', 'br-imp-dim');
    brImportLog('', '');
    brImportLog('Download the report to keep the generated nicknames and passwords.', 'br-imp-dim');

    $('#br-import-progress-text').text(processed + ' / ' + BRImport.rows.length + ' processed');
    $('#br-import-cancel').addClass('br-initially-hidden');
    $('#br-import-close, #br-import-report, #br-import-reload').removeClass('br-initially-hidden');
}

function brImportDownloadReport() {
    var lines = ['email,nickname,password,guild,status,detail'];
    BRImport.rows.forEach(function (row) {
        var r = BRImport.results[row.index] || { status: 'not processed', detail: '' };
        lines.push([
            r.email || row.email,
            r.nickname || row.nickname,
            r.password || '',
            r.guild || row.guild || '',
            r.status,
            r.detail || ''
        ].map(brCsvCell).join(','));
    });
    var blob = new Blob(["\uFEFF" + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'player-import-report-adventure-' + $('#the_adventure_id').val() + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
}

$(window).on('beforeunload', function () {
    if (BRImport.running) {
        return 'A player import is still running. Leaving now will interrupt it.';
    }
});

////////////////////////////////////////// INLINE CONFIRM ////////////////////////////////////////////
// Two-step confirm that lives inside the button itself: the first click turns it
// into "Sure?", the second runs the action. Replaces the old .confirm-action-tooltip
// overlays, which reserved layout space while permanently invisible.
function brResetConfirm($btn) {
    if (!$btn || !$btn.length) return;
    if ($btn.data('brOriginal') !== undefined) $btn.html($btn.data('brOriginal'));
    if ($btn.data('brTimer')) clearTimeout($btn.data('brTimer'));
    $btn.removeClass('br-confirming').removeData('brOriginal').removeData('brTimer');
}

function brResetAllConfirms() {
    $('.br-confirming').each(function () { brResetConfirm($(this)); });
}

// Re-arms the role buttons of a row after a role change: every button gets its
// handler back except the one for the role the player now holds. The confirm
// wording comes from the button's own data-confirm so it stays translated.
function brRewireRoleButtons($row, adventure_id, player_id, role) {
    var call = function (r) {
        return 'setPlayerAdventureRole(' + adventure_id + ',' + player_id + ",'" + r + "');";
    };
    ['player', 'gm', 'npc'].forEach(function (r) {
        var $btn = $('button.role-button-' + r, $row);
        if (!$btn.length) return;
        brResetConfirm($btn);
        var label = $btn.attr('data-confirm');
        $btn.attr('onclick', label
            ? "brConfirmInline(this,'" + label.replace(/'/g, "\\'") + "',function(){ " + call(r) + ' });'
            : call(r));
    });
    $('button.role-button-' + role, $row).removeAttr('onclick');
}

function brConfirmInline(btn, label, fn) {
    var $btn = $(btn);
    if ($btn.hasClass('br-confirming')) {
        brResetConfirm($btn);
        fn();
        return;
    }
    brResetAllConfirms();
    $btn.data('brOriginal', $btn.html())
        .addClass('br-confirming')
        .html('<span class="icon icon-warning"></span> ' + brEsc(label));
    $btn.data('brTimer', setTimeout(function () { brResetConfirm($btn); }, 4000));
}

////////////////////////////////////////// BR TAB PANELS ////////////////////////////////////////////
// Show one .br-panel-group inside a container and mark its .br-tab-btn active.
// Self-contained on the br-* classes rather than the legacy .tabs/.tab styles,
// so a page using it does not depend on _content.scss.
function brSwitchPanel(groupSelector, panelSelector, btn) {
    $(groupSelector).children('.br-panel-group').addClass('br-initially-hidden');
    $(panelSelector).removeClass('br-initially-hidden');
    if (btn) {
        $(btn).closest('.br-tabs').find('.br-tab-btn').removeClass('active');
        $(btn).addClass('active');
    }
}

////////////////////////////////////////// GUILD LEADER ////////////////////////////////////////////
// The one guild change an NPC is allowed to make, so it has its own endpoint and
// its own nonce rather than riding on updateGuild.
function setGuildLeader(guild_id) {
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'setGuildLeader',
            nonce: $('#guild-leader-nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            guild_id: guild_id,
            player_id: $('#the_guild_leader-' + guild_id).val()
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

////////////////////////////////////////// GUILD BULK ACTIONS ////////////////////////////////////////////
// manage-guilds.php. A CSV import with a Guild column can create hundreds of
// guilds at once, so every table there supports selecting rows and moving the
// whole selection in one request.

function brGuildSelection(scope) {
    var ids = [];
    $('#guild-table-' + scope + ' .br-guild-pick:checked').each(function () {
        ids.push($(this).val());
    });
    return ids;
}

function brGuildSyncBar(scope) {
    var total = $('#guild-table-' + scope + ' .br-guild-pick').length;
    var picked = brGuildSelection(scope).length;
    $('#guild-bulk-count-' + scope).text(picked);
    $('#guild-bulk-' + scope).toggleClass('active', picked > 0);
    var $all = $('#guild-check-all-' + scope);
    $all.prop('checked', picked > 0 && picked === total);
    $all.prop('indeterminate', picked > 0 && picked < total);
}

function brGuildToggleAll(scope, el) {
    // Only rows the search filter left visible, so "select all" never silently
    // acts on guilds the operator cannot see.
    $('#guild-table-' + scope + ' tbody > tr:visible .br-guild-pick').prop('checked', el.checked);
    brGuildSyncBar(scope);
}

function brBulkGuildStatus(scope, status) {
    brGuildStatusFor(brGuildSelection(scope), status);
}

// Also used for a single row's "Delete forever": permanent deletion has to go
// through this endpoint rather than br_trash, because it is the one that also
// releases the guild's members.
function brGuildStatusFor(ids, status) {
    if (!ids || !ids.length) return;
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'brBulkGuildStatus',
            nonce: $('#bulk-guild-nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            status: status,
            guild_ids: ids
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

$(function () {
    $(document).on('change', '.br-guild-pick', function () {
        brGuildSyncBar($(this).attr('data-scope'));
    });
});

////////////////////////////////////////// postToWall ////////////////////////////////////////////
function postToWall(ann_type, target_id = "") {
    let nonce = $('#nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    let ann_content = $('#message-content').val();
    if (ann_type == 'guild') {
        let guild_id = target_id;
    }
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'postToWall',
            ann_type: ann_type,
            guild_id: guild_id,
            adventure_id: adventure_id,
            ann_content: ann_content,
            nonce: nonce
        }),
        method: "POST",
        success: function (json_text) {
            loadChat(ann_type, guild_id);
            $('#message-content').val('');
            hideAllOverlay();
        }
    });
}




////////////////////////////////////////// LOAD CHAT ////////////////////////////////////////////
function loadChat(type, guild_id = "") {
    $('.wall-nav-btn').removeClass('active');
    $(".wall-content").removeClass('active');
    showLoader();
    let myTimeout = setTimeout(function () {
        $("#message-feed").html('');
        $('.wall-content-header').removeClass('active');
        let adventure_id = $("#the_adventure_id").val();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'loadChat',
                adventure_id: adventure_id,
                type: type,
                guild_id: guild_id
            }),
            method: "POST",
            success: function (data_received) {
                $('#message-feed').html(data_received);
                let myTimeout2 = setTimeout(function () {
                    $('#message-type-' + type + guild_id).addClass('active');
                    $('#wall-content-header-' + type + guild_id).addClass('active');
                    $(".wall-content").addClass('active');
                }, 500);
                if (type == 'guild') {
                    $(".guild-post-button").addClass('hidden');
                    $("#guild-post-button-" + guild_id).removeClass('hidden');
                    $("#public-post-button, #announcement-post-button").addClass('hidden');
                } else if (type == 'public') {
                    $("#public-post-button, #announcement-post-button").removeClass('hidden');
                    $(".guild-post-button").addClass('hidden');
                }
                hideAllOverlay();
            }
        });
    }, 500);


}

function filterChat(type) {
    if (type) {
        $('.message-feed ul li.message').hide();
        $('.message-feed ul li.' + type).show();
    } else {
        $('.message-feed ul li.message').show();
    }
}

//////////////////  Buy Item ////////////////

function buyItem(item_id) {
    let nonce = $('#purchase-nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    if (item_id) {
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'buyItem',
                item_id: item_id,
                nonce: nonce,
                adventure_id: adventure_id
            }),
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
            }
        });
    }
}
//////////////////  PickUp Item ////////////////

function pickupItem(item_id, nonce) {
    let adventure_id = $('#the_adventure_id').val();
    if (item_id) {
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'pickupItem',
                item_id: item_id,
                nonce: nonce,
                adventure_id: adventure_id
            }),
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
            }
        });
    }
}
//////////////////  Check Item ////////////////

function checkItem(step_id) {
    let adventure_id = $('#the_adventure_id').val();
    let item_id = $("#step-backpack-" + step_id + " .item.active input.item-id").val();
    let nonce = $('#nonce-item-req-' + step_id).val();
    if (item_id) {
        showLoader();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'checkItem',
                item_id: item_id,
                nonce: nonce,
                adventure_id: adventure_id,
                step_id: step_id
            }),
            method: "POST",
            success: function (data_received) {
                displayAjaxResponse(data_received);
            }
        });
    } else {
        notification('#msg-no-step-req-selected', 2000);
    }
}
//////////////////  payBlocker ////////////////

function payBlocker(blocker_id) {
    let nonce = $('#nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'payBlocker',
            blocker_id: blocker_id,
            nonce: nonce,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {

            displayAjaxResponse(data_received);

        }
    });
}

//////////////////  setColorTo ////////////////
function selectImage(id, group) {
    $(group + ' .button').removeClass('active');
    $(group + ' ' + id).addClass('active');
    let image = $(group + ' ' + id + ' input.value').val();
    $('#the_quest_badge').val(image);
    $('#the_quest_badge_thumb').css('background-image', 'url(' + image + ')');
}

//////////////////  USE Item ////////////////

function useItem(trnx_id, player_id = '', use_item = 0) {
    let nonce = $('#use-item-nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'useItem',
            trnx_id: trnx_id,
            nonce: nonce,
            adventure_id: adventure_id,
            player_id: player_id,
            use_item: use_item
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

//// animateText as speech
function revealOneCharacter(list) {
    let nextChar = list.splice(0, 1)[0];
    nextChar.span.classList.add("revealed");
    nextChar.classes.forEach((c) => {
        nextChar.span.classList.add(c);
    });
    let charactersDelay = nextChar.isSpace && !nextChar.pause ? 0 : nextChar.delayAfter;

    if (list.length > 0) {
        setTimeout(function () {
            revealOneCharacter(list);
        }, charactersDelay);
    }
}



//////////////////  jumpToStep ////////////////
function skipToStep(step) {
    document.location.href = "#step-" + step;
}

function jumpToStep(step_to, ep = 0) {
    let quest_id = $("#the_quest_id").val();
    let current_step = step_to;
    setCurrentQuest(quest_id, current_step);
    $("#step-" + step_to).addClass('active');
    if ($("#step-background-video-" + step_to)) {
        $("#step-background-video-" + step_to).addClass('active');
    }
    let stepTimeout = setTimeout(function () {
        $(".step:not(#step-" + step_to + "), .step-background-video:not(#step-background-video-" + step_to + ")").removeClass('active');
    }, 300);
    let videoElements = document.querySelectorAll("video");
    for (let videoEl of videoElements) {
        videoEl.pause();
    }
    let cur_video_bg = document.getElementById(`step-background-video-${step_to}`);
    if (cur_video_bg) {
        cur_video_bg.play();
    }

}

function jumpToQuestion(question_to) {
    $(".step").removeClass('active');
    $("#step-" + question_to).addClass('active');
    let survey_id = $("#the_survey_id").val();
    setCurrentQuest(survey_id, question_to);

}


function setCurrentQuest(quest_id, step) {
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setCurrentQuest',
            quest_id: quest_id,
            step: step,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            let data = JSON.parse(data_received);
            if (data.success) {
                $("#current-quest-torch").attr('href', data.current_quest_url).removeClass('hidden');
            } else {
                $("#current-quest-torch").attr('href', '').addClass('hidden');
            }
        }
    });
}


//////////////////  purchaseDeadline ////////////////

function purchaseDeadline(quest_id) {
    let nonce = $('#purchase_nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'purchaseDeadline',
            quest_id: quest_id,
            nonce: nonce,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

//////////////////  payment ////////////////
function payment(object_id, type) {
    let nonce = $('#payment_nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'payment',
            object_id: object_id,
            type: type,
            nonce: nonce,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


//////////////////  SET GRADE  ////////////////
function setGrade(quest_id, player_id) {
    let nonce = $("#grade_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let grade = $("#the_quest_grade").val();
    if (!grade) {
        grade = $("#the_post_grade_" + quest_id + "_" + player_id).val();
    }
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setGrade',
            quest_id: quest_id,
            player_id: player_id,
            grade: grade,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            let data = JSON.parse(data_received);
            // For quests that also use Validate/Invalidate, the server derives
            // validated/not-validated straight from this grade (>0 = validated) - keep
            // that pair in sync without a reload. data.pp_status is only present when
            // this quest actually has mech_validate on (see setGrade() in BR-Quest.php).
            if (data.pp_status !== undefined) {
                refreshValidateActions(quest_id, player_id, data.pp_status === 'publish');
            }
        }
    });
}

function setPostComment(quest_id, player_id) {
    let nonce = $("#grade_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let comment = $("#the_post_comment_" + quest_id + "_" + player_id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setPostComment',
            quest_id: quest_id,
            player_id: player_id,
            comment: comment,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}




//////////////////  DOWNLOAD QUEST REVIEW CSV ////////////////
////////////////////// UPLOAD REVIEWED CSV (grade + validation_status + comment only) //////////////////
function uploadPostReviewCSV() {
    let fileInput = $('#review_csv_file')[0];
    let file = fileInput.files[0];
    if (!file) {
        notification('#msg-no-file-selected', 1000, '', 'player');
        return;
    }
    let formData = new FormData();
    formData.append('review_csv', file);
    formData.append('action', 'importPlayerPostsCSV');
    formData.append('adventure_id', $('#the_adventure_id').val());
    formData.append('quest_id', $('#the_review_quest_id').val());
    formData.append('nonce', $('#grade_nonce').val());

    showLoader();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: formData,
        processData: false,
        contentType: false,
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            fileInput.value = '';
        }
    });
}


//////////////////  VALIDATE QUEST (sets grade + pp_status) ////////////////
// validate_action: 'validate' (grade=100, publish) or 'invalidate' (grade=0, draft)
// Toggle visual active state for br-mech-checkbox-btn labels on change
$(document).on('change', '.br-mech-checkbox-btn input[type="checkbox"]', function() {
    var $label = $(this).closest('.br-mech-checkbox-btn');
    var cls = $label.data('checked-class') || 'is-checked';
    $label.toggleClass(cls, this.checked);
});

// Only one of Validate/Invalidate is ever shown at a time (a post either still needs
// validation - never reviewed or previously invalidated - or it's currently validated),
// so state changes rebuild the whole actions container rather than toggling disabled/
// class on two permanently-present buttons. Mirrors the PHP block in
// page-review-player-posts.php - keep the two in sync if either changes.
function buildValidateActionsHtml(quest_id, player_id, isValidated, showAiBtn, playerName) {
    if (isValidated) {
        return '<button class="br-btn red" onclick="validateQuest(' + quest_id + ',' + player_id + ",'invalidate');\" id=\"invalidate-btn-" + player_id + '-' + quest_id + '">' +
            '<span class="icon icon-cancel"></span> Invalidate</button>';
    }
    let html = '<button class="br-btn green" onclick="validateQuest(' + quest_id + ',' + player_id + ",'validate');\" id=\"validate-btn-" + player_id + '-' + quest_id + '">' +
        '<span class="icon icon-check"></span> Validate</button>';
    if (showAiBtn) {
        html += '<button class="br-btn" title="Ask the A.I. validator for a second opinion on this answer" ' +
            'onclick="reviewValidateWithAI(' + quest_id + ',' + player_id + ",'" + (playerName || '').replace(/'/g, "\\'") + "');\" id=\"ai-recheck-btn-" + player_id + '-' + quest_id + '">' +
            '<span class="icon icon-data"></span> Validate with A.I.</button>';
    }
    return html;
}

function refreshValidateActions(quest_id, player_id, isValidated) {
    let $container = $('#validate-actions-' + player_id + '-' + quest_id);
    if (!$container.length) { return; }
    let showAiBtn = $container.data('show-ai') == 1;
    let playerName = $container.data('player-name') || '';
    $container.html(buildValidateActionsHtml(quest_id, player_id, isValidated, showAiBtn, playerName));
}

function validateQuest(quest_id, player_id, validate_action) {
    let nonce = $("#grade_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'validatePlayerPost',
            quest_id: quest_id,
            player_id: player_id,
            validate_action: validate_action,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            let data = JSON.parse(data_received);
            // The server keeps an already-set grade as-is (Validate/Invalidate no longer
            // force it to a flat 100/0), so a graded-then-invalidated post can still have
            // grade > 0 - pp_status (not the grade value) is what actually says which
            // button should show.
            refreshValidateActions(quest_id, player_id, data.pp_status === 'publish');
            $("#the_post_grade_" + quest_id + "_" + player_id).val(data.grade);
        }
    });
}

//////////////////  VALIDATE WITH A.I. (second opinion only - never sets grade/status) ////////////////
// This is the GM's tool for sanity-checking the AI validator itself, so the verdict has
// to stay on screen and be fully readable, not a 1-second toast - see .ai-validate-overlay
// in css/_tabis.scss and reviewValidateWithAI() in classes/BR-Quest.php.
var aiStrictnessLabels = { lenient: 'Lenient', standard: 'Standard', strict: 'Strict' };

function aiEscapeHtml(str) {
    return $('<div>').text(str || '').html();
}

function openAiValidateModal(title) {
    $('#ai-validate-overlay-title').text(title || 'A.I. Validation Check');
    brOpenDrawer($('#ai-validate-overlay'));
}

function closeAiValidateModal() {
    brCloseDrawer($('#ai-validate-overlay'));
}

// One clean panel for everything earned from a single action - see
// BR_Player::resetPlayer()'s levelup/new_level/newly_earned and the
// data.newly_earned branch in displayAjaxResponse(). newlyEarned entries:
// {achievement_id, achievement_name, achievement_badge, achievement_color, is_rank, reason}.
// ═══════════════════════════════════════════════════════════════════════════
// Celebration panel — the single feedback surface for "you earned something"
// ═══════════════════════════════════════════════════════════════════════════
//
// Takes the event list any handler can attach to its response (see BR_Feedback
// on the PHP side) and renders ALL of it into one panel:
//
//     Congratulations!
//     You just leveled up and earned an achievement
//     [ 12 ]  [🏅 Rank 3 — You reached Level 3!]
//     Keep moving forward!                                    [ Close ]
//
// Everything the player gained in one action lands in one panel. Ordinary
// acknowledgements ("Settings saved") belong on the toast list via brNotify(),
// and admin batch progress belongs on the op console - this is only for things
// that were EARNED.
//
// Call it from anywhere, at any time:  brCelebrate([{type:'levelup', ...}])

function brCelebrateSentence(events) {
    var verbs = [];
    events.forEach(function (e) {
        var v = e.verb || '';
        if (v && verbs.indexOf(v) === -1) verbs.push(v);
    });
    if (!verbs.length) return '';
    var joined = verbs.length === 1
        ? verbs[0]
        : verbs.slice(0, -1).join(', ') + ' ' + (brI18n.and || 'and') + ' ' + verbs[verbs.length - 1];
    return (brI18n.you_just || 'You just') + ' ' + joined;
}

function brCelebrateCard(e) {
    var accent = e.color ? ' style="border-color:' + e.color + '55"' : '';

    // A level-up is the number itself; everything else is a badge/art card.
    if (e.type === 'levelup') {
        return '<div class="br-celebrate-card br-celebrate-card-level"' + accent + '>' +
            '<div class="br-celebrate-level-label">' + aiEscapeHtml(brI18n.level_up || 'Level Up!') + '</div>' +
            '<div class="br-celebrate-level-number">' + parseInt(e.value, 10) + '</div>' +
        '</div>';
    }

    var art = e.image
        ? '<div class="br-celebrate-art" style="background-image:url(' + encodeURI(e.image) + ')"></div>'
        : '<div class="br-celebrate-art br-celebrate-art-icon"><span class="icon ' + (e.icon || 'icon-achievement') + '"></span></div>';

    return '<div class="br-celebrate-card"' + accent + '>' +
        art +
        '<div class="br-celebrate-card-info">' +
            '<div class="br-celebrate-card-title">' + aiEscapeHtml(e.title || '') + '</div>' +
            (e.subtitle ? '<div class="br-celebrate-card-sub">' + aiEscapeHtml(e.subtitle) + '</div>' : '') +
        '</div>' +
    '</div>';
}

function brCelebrate(events) {
    events = (events || []).filter(Boolean);
    if (!events.length) return;

    $('#br-celebrate-line').text(brCelebrateSentence(events));
    $('#br-rewards-modal-body').html(events.map(brCelebrateCard).join(''));
    // brOpenDrawer brings its own backdrop, so the panel does not add a second one.
    brOpenDrawer($('#br-rewards-overlay'));
}

function brCelebrateClose() {
    brCloseDrawer($('#br-rewards-overlay'));
}

// Back-compat: anything still speaking the old levelup/newly_earned shape is
// translated into events rather than kept on a second code path.
function showRewardsOverlay(levelup, newLevel, newlyEarned) {
    var events = [];
    if (levelup) events.push({ type: 'levelup', verb: brI18n.verb_levelup || 'leveled up', value: newLevel });
    (newlyEarned || []).forEach(function (a) {
        events.push({
            type:     a.is_rank ? 'rank' : 'achievement',
            verb:     a.is_rank ? (brI18n.verb_rank || 'reached a new rank') : (brI18n.verb_achievement || 'earned an achievement'),
            title:    a.achievement_name,
            subtitle: a.reason,
            image:    a.achievement_badge,
            color:    a.achievement_color
        });
    });
    brCelebrate(events);
}

function claimRewards() {
    brCelebrateClose();
}

// The percentage grade an AI grade suggestion snaps to when this adventure grades in
// letters, so it lands on an option the <select> in the row actually has - mirrors the
// breakpoints in page-review-player-posts.php's own letter-grade <select>.
function aiSnapGradeToScale(grade, scale) {
    if (scale === 'letters') {
        let breakpoints = [100, 91.75, 83.25, 75, 66.75, 58.25, 50, 25, 0];
        for (let i = 0; i < breakpoints.length; i++) {
            if (grade >= breakpoints[i]) { return breakpoints[i]; }
        }
        return 0;
    }
    return Math.max(0, Math.min(100, Math.round(grade)));
}

// Stashed rather than round-tripped through onclick attribute strings, since the
// suggested comment is free text (AI output) and building an inline onclick out of
// arbitrary text is exactly the kind of thing that breaks on a stray quote.
var aiLastSuggestion = null;

function renderAiVerdict(data, quest_id, player_id) {
    if (!data.success) {
        aiLastSuggestion = null;
        $('#ai-validate-overlay-body').html(
            '<div class="br-ai-verdict">' +
                '<div class="br-ai-verdict-icon invalid"><span class="icon icon-cancel"></span></div>' +
                '<div class="br-ai-verdict-headline">' + aiEscapeHtml(brI18n.ot_ai_fail || "Can't check this right now") + '</div>' +
                '<div class="br-ai-verdict-reason">' + aiEscapeHtml(data.error || 'Unknown error.') + '</div>' +
            '</div>'
        );
        return;
    }
    let valid = data.valid;
    let headline = valid ? 'Looks like a genuine, complete answer' : "Doesn't look complete yet";
    let reason = data.reason ? aiEscapeHtml(data.reason) : (valid ? 'No issues found.' : "The A.I. didn't give a specific reason.");
    let strictnessLabel = aiStrictnessLabels[data.strictness] || 'Standard';

    let hasGrade = data.grade_scale && data.grade_scale !== 'none' && data.suggested_grade !== null && data.suggested_grade !== undefined;
    let snappedGrade = hasGrade ? aiSnapGradeToScale(data.suggested_grade, data.grade_scale) : null;
    let suggestedComment = data.suggested_comment || '';
    let hasSuggestion = hasGrade || !!suggestedComment;

    aiLastSuggestion = { grade: hasGrade ? snappedGrade : null, comment: suggestedComment };

    let suggestionBlock = '';
    if (hasSuggestion) {
        suggestionBlock = '<div class="br-ai-suggestion">' +
            (hasGrade ? '<div class="br-ai-suggestion-grade"><span class="br-ai-suggestion-label">Suggested grade</span><strong>' + snappedGrade + '</strong></div>' : '') +
            (suggestedComment ? '<div class="br-ai-suggestion-comment"><span class="br-ai-suggestion-label">Suggested comment</span>' + aiEscapeHtml(suggestedComment) + '</div>' : '') +
        '</div>';
    }

    // Two ways to act on this: validate exactly as-is (green), or accept the A.I.'s
    // grade/comment suggestion into the row's own fields first (orange). Available
    // either way - a GM can decide to validate even on a borderline verdict.
    let actionsBlock = '<div class="br-ai-verdict-actions">' +
        '<button class="br-btn green" onclick="validateQuest(' + quest_id + ',' + player_id + ",'validate'); closeAiValidateModal();\">" +
            '<span class="icon icon-check"></span> Validate</button>' +
        (hasSuggestion
            ? '<button class="br-btn orange" onclick="reviewValidateWithSuggestion(' + quest_id + ',' + player_id + ');">' +
                '<span class="icon icon-check"></span> Validate with Suggestion</button>'
            : '') +
    '</div>';

    $('#ai-validate-overlay-body').html(
        '<div class="br-ai-verdict">' +
            '<div class="br-ai-verdict-icon ' + (valid ? 'valid' : 'invalid') + '"><span class="icon ' + (valid ? 'icon-check' : 'icon-question') + '"></span></div>' +
            '<div class="br-ai-verdict-headline">' + headline + '</div>' +
            '<div class="br-ai-verdict-reason">' + reason + '</div>' +
            '<div class="br-ai-verdict-meta">Strictness: ' + strictnessLabel + '</div>' +
            suggestionBlock +
            actionsBlock +
        '</div>'
    );
}

// Applies the AI's suggested grade/comment to this row's own fields (chaining the same
// setGrade/setPostComment endpoints the row's inputs already use), then validates.
function reviewValidateWithSuggestion(quest_id, player_id) {
    if (!aiLastSuggestion) { return; }
    let nonce = $("#grade_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let grade = aiLastSuggestion.grade;
    let comment = aiLastSuggestion.comment;

    let steps = [];
    if (grade !== null) {
        steps.push(function (next) {
            jQuery.ajax({
                url: runAJAX.ajaxurl, method: 'POST',
                data: { action: 'setGrade', quest_id: quest_id, player_id: player_id, adventure_id: adventure_id, grade: grade, nonce: nonce },
                success: next
            });
        });
    }
    if (comment) {
        steps.push(function (next) {
            jQuery.ajax({
                url: runAJAX.ajaxurl, method: 'POST',
                data: { action: 'setPostComment', quest_id: quest_id, player_id: player_id, adventure_id: adventure_id, comment: comment, nonce: nonce },
                success: next
            });
        });
    }

    function runStep(i) {
        if (i >= steps.length) {
            if (grade !== null) { $("#the_post_grade_" + quest_id + "_" + player_id).val(grade); }
            if (comment) { $("#the_post_comment_" + quest_id + "_" + player_id).val(comment); }
            validateQuest(quest_id, player_id, 'validate');
            closeAiValidateModal();
            return;
        }
        steps[i](function () { runStep(i + 1); });
    }
    runStep(0);
}

function reviewValidateWithAI(quest_id, player_id, player_name) {
    let nonce = $("#grade_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let $btn = $('#ai-recheck-btn-' + player_id + '-' + quest_id);
    $btn.prop('disabled', true);

    openAiValidateModal(player_name);
    $('#ai-validate-overlay-body').html('<div class="br-ai-verdict"><span class="icon icon-data"></span> Checking with A.I...</div>');

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'reviewValidateWithAI',
            quest_id: quest_id,
            player_id: player_id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            let data = (typeof data_received === 'string') ? JSON.parse(data_received) : data_received;
            $btn.prop('disabled', false);
            renderAiVerdict(data, quest_id, player_id);
        },
        error: function () {
            $btn.prop('disabled', false);
            renderAiVerdict({ success: false, error: 'Something went wrong talking to the A.I. service - try again.' }, quest_id, player_id);
        }
    });
}

// ── Not wired to any button yet - built ahead of the ask. When the client asks for a
// bulk version of the button above, wire a button to call
// reviewValidateAllPendingWithAI(questId); everything else (progress modal, sequential
// AJAX loop so we never hammer the Anthropic API in parallel, auto-Validate on a pass,
// leave-alone on a fail) is already here and works today.
function reviewValidateAllPendingWithAI(quest_id) {
    let pending = [];
    // A Validate button only exists in the DOM at all when that post still needs
    // validation - see buildValidateActionsHtml() - so its mere presence is "pending".
    $('button[id^="validate-btn-"]').each(function () {
        let rest = this.id.replace('validate-btn-', '');
        let dash = rest.lastIndexOf('-');
        let pid = rest.substring(0, dash), qid = rest.substring(dash + 1);
        if (String(qid) === String(quest_id)) { pending.push(pid); }
    });

    openAiValidateModal('Validate All Pending');
    if (!pending.length) {
        $('#ai-validate-overlay-body').html('<div class="br-ai-verdict"><div class="br-ai-verdict-headline">Nothing pending</div><div class="br-ai-verdict-reason">Every submission has already been reviewed.</div></div>');
        return;
    }

    let total = pending.length, done = 0, autoValidated = 0, stillPending = 0;
    let log = [];

    function renderProgress() {
        let pct = Math.round((done / total) * 100);
        $('#ai-validate-overlay-body').html(
            '<div class="br-ai-bulk-progress">' +
                '<div class="br-ai-verdict-headline">Checking ' + done + ' of ' + total + '</div>' +
                '<div class="br-ai-bulk-progress-bar"><div style="width:' + pct + '%"></div></div>' +
                '<div class="br-ai-bulk-progress-list">' + log.slice(-8).join('') + '</div>' +
            '</div>'
        );
    }
    renderProgress();

    function next() {
        if (!pending.length) {
            $('#ai-validate-overlay-body').html(
                '<div class="br-ai-verdict">' +
                    '<div class="br-ai-verdict-icon valid"><span class="icon icon-check"></span></div>' +
                    '<div class="br-ai-verdict-headline">Done</div>' +
                    '<div class="br-ai-verdict-reason">Checked ' + total + ' pending submissions.<br>' + autoValidated + ' auto-validated, ' + stillPending + ' still need your review.</div>' +
                '</div>'
            );
            return;
        }
        let pid = pending.shift();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'reviewValidateWithAI',
                quest_id: quest_id,
                player_id: pid,
                adventure_id: $("#the_adventure_id").val(),
                nonce: $("#grade_nonce").val()
            }),
            method: "POST",
            success: function (data_received) {
                let data = (typeof data_received === 'string') ? JSON.parse(data_received) : data_received;
                done++;
                if (data.success && data.valid) {
                    autoValidated++;
                    log.push('<div class="done">#' + pid + ' - auto-validated</div>');
                    validateQuest(quest_id, pid, 'validate');
                } else {
                    stillPending++;
                    log.push('<div>#' + pid + ' - still pending</div>');
                }
                renderProgress();
                next();
            },
            error: function () {
                done++; stillPending++;
                log.push('<div>#' + pid + ' - error, skipped</div>');
                renderProgress();
                next();
            }
        });
    }
    next();
}


//////////////////  DELETE POST ////////////////

function updateStatus(id, type) { //////////////// DEPRECATED !!!!!!!
    let action = $("#" + type + "-" + id + " .update-status").val();
    if (action) {
        let what = action;
        br_confirm_trd(what, id, type);
    }
}

function confirmStatus(id, type, action) {
    $("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id, #br-locked-id").val(id);
    $("#trd-type").val(type);
    $("#trd-action").val(action);
    br_trash();
}

function br_confirm_trd(trash_action, id, type) {
    hideAllOverlay();
    let message = $("#msg-" + trash_action).html();
    $("#feedback .content").html(message);
    // Reverting a transaction is the one action here with a real accountability
    // requirement - append (not replace) a reason field only for it, since .content
    // just got overwritten by the static message above and would wipe out anything
    // placed there ahead of time.
    if (type === 'trnx') {
        $("#feedback .content").append(
            '<div class="br-form-group br-mt-sm">' +
            '<label class="br-form-label">Reason (optional)</label>' +
            '<textarea class="br-input" id="trd-reason" rows="2"></textarea>' +
            '</div>'
        );
    }
    $("#feedback").addClass('active');
    $("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id, #br-locked-id").val(id);
    $("#trd-type").val(type);
    $("#trd-action").val(trash_action);
}

function clearTRD() {
    hideAllOverlay();
    $("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id, #br-locked-id, #trd-type, #trd-action").val('');
}

function br_trash() {
    showLoader();
    let trash_action = $("#trd-action").val();
    let nonce = $('#' + trash_action + '-nonce').val();
    let adventure_id = $("#the_adventure_id").val();
    // id is read from #br-{action}-id - any new action wired through confirmStatus()
    // needs a matching hidden #br-{action}-id in footer.php or the id silently comes
    // back undefined here (this is what broke "Lock" for a while).
    let id = $('#br-' + trash_action + '-id').val();
    let type = $("#trd-type").val();
    let reload = $("#reload").val();
    let player_id = $("#trd-player-id").val();
    let reason = $("#trd-reason").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_trash',
            id: id,
            nonce: nonce,
            adventure_id: adventure_id,
            type: type,
            reload: reload,
            player_id: player_id || '',
            reason: reason || ''
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function emptyTrash(type) {
    showLoader();
    let nonce = $('#empty-trash-nonce').val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_empty_trash',
            nonce: nonce,
            adventure_id: adventure_id,
            type: type
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function resetTransactions(player_id) {
    showLoader();
    let nonce = $('#reset_nonce').val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'resetTransactions',
            nonce: nonce,
            adventure_id: adventure_id,
            player_id: player_id
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Toggle Column  //////////////////

function toggleColumn(type = '') {
    if (type) {
        $('table.table thead tr td.' + type + ' button.form-ui').toggleClass('opacity-50');
        $('table.table tbody tr td.' + type).toggle();
    }
}


///////////////////////// Set XP  //////////////////

function setXP(id, type) {
    let nonce = $("#xp-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let xp = $("#the_xp-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setXP',
            xp: xp,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


///////////////////////// Set EP  //////////////////

function setEP(id, type) {
    let nonce = $("#ep-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let ep = $("#the_ep-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setEP',
            ep: ep,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set BLOO  //////////////////

function setBLOO(id, type) {
    let nonce = $("#bloo-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let bloo = $("#the_bloo-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setBLOO',
            bloo: bloo,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set VALIDATE  //////////////////

function setValidate(id, type, validate) {
    let nonce = $("#validate-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setValidate',
            validate: validate,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set OPTIONAL (Required vs Side Quest)  //////////////////

function setOptional(id, type, optional) {
    let nonce = $("#optional-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setOptional',
            optional: optional,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set MAX PLAYERS  //////////////////

function setMaxPlayers(id) {
    let nonce = $("#max-players-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let max = $("#the_max_players-achievement-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setMaxPlayers',
            max: max,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set LEVEL  //////////////////

function setLevel(id, type) {
    let nonce = $("#level-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let level = $("#the_level-" + type + "-" + id).val();
    let auto_xp;
    if ($("#resource-autofill").val() > 0) {
        if ($("#resource-autofill").val() == 65) {
            auto_xp = Math.round(level * 1000 * 0.65);
        } else if ($("#resource-autofill").val() == 50) {
            auto_xp = Math.round(level * 1000 * 0.5);
        } else if ($("#resource-autofill").val() == 35) {
            auto_xp = Math.round(level * 1000 * 0.35);
        } else if ($("#resource-autofill").val() == 25) {
            auto_xp = Math.round(level * 1000 * 0.25);
        } else if ($("#resource-autofill").val() == 10) {
            auto_xp = Math.round(level * 1000 * 0.1);
        }
        let auto_bloo = Math.round(auto_xp / 10);
        let auto_ep = Math.round(auto_xp / 20);
        $("#the_xp-" + type + "-" + id).val((auto_xp));
        $("#the_bloo-" + type + "-" + id).val((auto_bloo));
        $("#the_ep-" + type + "-" + id).val((auto_ep));
        setXP(id, type);
        setBLOO(id, type);
        setEP(id, type);
    }
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setLevel',
            level: level,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set Dimensions  //////////////////

function setDimensions(id, type) {
    let nonce = $("#dimensions-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let width = $("#the_width-" + type + "-" + id).val();
    let height = $("#the_height-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setDimensions',
            width: width,
            height: height,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// setTabiOnJourney  //////////////////

function setTabiOnJourney(id) {
    let nonce = $("#tabi-on-journey-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader('small');
    let tabi_id;

    $(function () {
        $('.tabi-on-journey-checkbox').click(function () {
            if ($(this).is(':checked')) {
                $('.tabi-on-journey-checkbox').not(this).prop('checked', false);
            } else {
                $('.tabi-on-journey-checkbox').prop('checked', false);
            }
        });
    });
    if ($("#tabi-on-journey-" + id).is(':checked')) {
        tabi_id = id;
    } else {
        tabi_id = 0;
    }
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setTabiOnJourney',
            id: tabi_id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function setTabiAsCategory(id) {
    let nonce = $("#tabi-as-category-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let val = $("#tabi-as-category-" + id).is(':checked') ? 1 : 0;
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'setTabiAsCategory',
            id: id,
            val: val,
            adventure_id: adventure_id,
            nonce: nonce
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Tabi Modal  //////////////////

// The Tabi modal predates the shared backdrop and brings its own veil
// (#tabi-modal-overlay at z-index 200, modal at 201). Those two are siblings so
// they layer correctly against each other, but both were authored inside
// .main-content - `position:relative; z-index:3` - which caps the whole subtree
// below the fixed site chrome at z-index 51. The header and taskbar therefore
// painted over the modal. Same cause as every other drawer, same fix: move both
// to <body> so their numbers are compared in the root stacking context.
function openTabiModal(tabiId) {
    closeTabiModal();
    var $veil  = $('#tabi-modal-overlay');
    var $modal = $('#tabi-modal-' + tabiId);
    brPortalDrawer($veil);
    brPortalDrawer($modal);
    $modal.addClass('active');
    $veil.addClass('active');
    $('body').css('overflow', 'hidden');
}

function saveTabiPrerequisites(tabiId) {
    let nonce = $('#tabi-prereq-nonce-' + tabiId).val();
    let requires = [];
    $('.tabi-prereq-checkbox[data-tabi-id="' + tabiId + '"]:checked').each(function () {
        requires.push($(this).val());
    });
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveTabiPrerequisites',
            tabi_id: tabiId,
            requires: requires,
            nonce: nonce
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function closeTabiModal() {
    $('.tabi-modal').removeClass('active').each(function () { brRestoreDrawer($(this)); });
    var $veil = $('#tabi-modal-overlay');
    $veil.removeClass('active');
    brRestoreDrawer($veil);
    $('body').css('overflow', '');
}

function openTabiConditionsModal(tabiId) {
    let $overlay = $('#tabi-conditions-overlay-' + tabiId);
    let $content = $('#tabi-conditions-content-' + tabiId);
    if (!$content.data('loaded')) {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: {
                action: 'insertTabiConditionsModal',
                tabi_id: tabiId
            },
            method: 'POST',
            success: function (data_received) {
                $content.html(data_received);
                $content.data('loaded', true);
            }
        });
    }
    brOpenDrawer($overlay);
}

function closeTabiConditionsModal(tabiId) {
    brCloseDrawer($('#tabi-conditions-overlay-' + tabiId));
}

// Move a milestone into or out of this tabi, straight from the Conditions
// drawer. br_quests.tabi_id holds exactly one tabi, so ticking is "set to this
// tabi" and unticking is "set to none" - there is no many-to-many to maintain.
// Saves on change rather than waiting for the Save button, which only covers
// conditions; the checkbox is put back if the write fails so the drawer never
// shows a membership the database doesn't have.
function toggleTabiMembership(tabiId, questId, questType, input) {
    var wanted = input.checked;
    var $row = $('#tabi-member-' + tabiId + '-' + questId);
    $row.addClass('is-saving');
    input.disabled = true;

    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'setQuestTabi',
            tabi_id: wanted ? tabiId : 0,
            type: questType,
            adventure_id: $('#the_adventure_id').val(),
            id: questId,
            nonce: $('#tabi-conditions-overlay-' + tabiId + ' .tabi-quest-tabi-nonce').val()
        },
        success: function (raw) {
            $row.removeClass('is-saving');
            input.disabled = false;
            var data;
            try { data = JSON.parse(raw); } catch (e) { data = null; }
            if (data && data.new_quest_tabi_nonce) {
                $('#tabi-conditions-overlay-' + tabiId + ' .tabi-quest-tabi-nonce').val(data.new_quest_tabi_nonce);
            }
            if (!data || !data.success) {
                input.checked = !wanted;
                $row.addClass('is-error');
                displayAjaxResponse(raw);
                return;
            }
            $row.removeClass('is-error').toggleClass('is-member', wanted);
            // The row may have named another tabi as its owner; it belongs to
            // this one now, so that note is no longer true.
            if (wanted) $row.find('.tabi-member-elsewhere').remove();
            var $count = $('#tabi-member-count-' + tabiId);
            $count.text(Math.max(0, (parseInt($count.text(), 10) || 0) + (wanted ? 1 : -1)));
            displayAjaxResponse(raw);
        },
        error: function () {
            $row.removeClass('is-saving').addClass('is-error');
            input.disabled = false;
            input.checked = !wanted;
        }
    });
}

function saveTabiConditionsModal(tabiId) {
    let $body = $('.tabi-conditions-body[data-tabi-id="' + tabiId + '"]');
    let adventureId = $body.data('adventure-id');
    let nonce = $body.find('.tabi-conditions-nonce').val();

    let questIds = [];
    $body.find('.tabi-cond-quest-checkbox:checked').each(function () { questIds.push($(this).val()); });

    let achievementIds = [];
    $body.find('.tabi-cond-achievement-checkbox:checked').each(function () { achievementIds.push($(this).val()); });

    let itemId = $body.find('.tabi-cond-item-select').val();

    let conditions = {};
    $body.find('.tabi-cond-threshold-input').each(function () {
        let val = $(this).val();
        if (val !== '') conditions[$(this).data('condition-type')] = val;
    });

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveTabiConditions',
            nonce: nonce,
            tabi_id: tabiId,
            adventure_id: adventureId,
            quest_ids: questIds,
            achievement_ids: achievementIds,
            item_id: itemId,
            conditions: conditions
        },
        method: 'POST',
        success: function (data_received) {
            closeTabiConditionsModal(tabiId);
            displayAjaxResponse(data_received);
        }
    });
}

////////////////////////////// QUEST CONDITIONS (Advanced tab) ///////////////////////////
function openQuestConditionsModal(questId) {
    let $content = $('#quest-conditions-content');
    $content.html('<span class="br-text-12 grey-400">Loading...</span>');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: { action: 'insertQuestConditionsModal', quest_id: questId },
        method: 'POST',
        success: function (data_received) {
            $content.html(data_received);
        }
    });
    brOpenDrawer($('#quest-conditions-overlay'));
}

function closeQuestConditionsModal() {
    brCloseDrawer($('#quest-conditions-overlay'));
}

function saveQuestConditionsModal(questId) {
    let $body = $('#quest-conditions-overlay .tabi-conditions-body');
    let adventureId = $body.data('adventure-id');
    let nonce = $body.find('.quest-conditions-nonce').val();

    let questIds = [];
    $body.find('.tabi-cond-quest-checkbox:checked').each(function () { questIds.push($(this).val()); });

    let achievementIds = [];
    $body.find('.tabi-cond-achievement-checkbox:checked').each(function () { achievementIds.push($(this).val()); });

    let itemId = $body.find('.tabi-cond-item-select').val();

    let conditions = {};
    $body.find('.tabi-cond-threshold-input').each(function () {
        let val = $(this).val();
        if (val !== '') conditions[$(this).data('condition-type')] = val;
    });

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveQuestConditions',
            nonce: nonce,
            quest_id: questId,
            adventure_id: adventureId,
            quest_ids: questIds,
            achievement_ids: achievementIds,
            item_id: itemId,
            conditions: conditions
        },
        method: 'POST',
        success: function (data_received) {
            closeQuestConditionsModal();
            displayAjaxResponse(data_received);
        }
    });
}

function updateTabiPosition(tabiId) {
    let nonce = $('#tabi-position-nonce').val();
    let top = parseInt($('#tabi-node-' + tabiId).css('top'), 10);
    let left = parseInt($('#tabi-node-' + tabiId).css('left'), 10);
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveTabiPosition',
            tabi_id: tabiId,
            top: top,
            left: left,
            nonce: nonce
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function _saveTabiSize(id, width, height) {
    let nonce = $('#tabi-position-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveTabiSize',
            tabi_id: id,
            width: width,
            height: height,
            nonce: nonce
        },
        method: 'POST'
    });
}

///////////////////////// Set Achievement  //////////////////
function setAchievement(id, type) {
    showLoader('small');
    let nonce = $("#achievement-nonce").val();
    let achievement_id = $("#" + type + "-" + id + " select.update-achievement").val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setAchievement',
            achievement_id: achievement_id,
            type: type,
            adventure_id: adventure_id,
            id: id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set Quest Tabi  //////////////////
function setQuestTabi(id, type) {
    showLoader('small');
    let nonce = $("#quest-tabi-nonce").val();
    let tabi_id = $("#" + type + "-" + id + " select.update-tabi").val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setQuestTabi',
            tabi_id: tabi_id,
            type: type,
            adventure_id: adventure_id,
            id: id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set Guild  //////////////////
function setGuild(id, type) {
    showLoader('small');
    let nonce = $("#guild-nonce").val();
    let guild_id = $("#" + type + "-" + id + " select.update-guild").val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setGuild',
            guild_id: guild_id,
            type: type,
            adventure_id: adventure_id,
            id: id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set Speaker  //////////////////
function setSpeaker(id) {
    showLoader('small');
    let nonce = $("#set-speaker-nonce").val();
    let speaker = $("#speaker-" + id).val();
    let adventure_id = $("#the_adventure_id").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setSpeaker',
            id: id,
            speaker: speaker,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


///////////////////////// setDisplayStyle  //////////////////

function setDisplayStyle(id, type) {
    let nonce = $("#display-style-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let style = $("#the_quest_style-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setDisplayStyle',
            style: style,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set StartDate  //////////////////

function setStartDate(id, type) {
    let nonce = $("#start-date-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let start_date = $("#the_start_date-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setStartDate',
            start_date: start_date,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set Deadline  //////////////////

function setDeadline(id, type) {
    let nonce = $("#deadline-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let deadline = $("#the_deadline-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setDeadline',
            deadline: deadline,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// updateAdventureTitle  //////////////////

function updateAdventureTitle(adventure_id) {
    let nonce = $("#update-adv-title-nonce-" + adventure_id).val();
    let adv_title = $("#adventure-title-update-" + adventure_id + " input.new-adventure-title").val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updateAdventureTitle',
            adv_title: adv_title,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            activate("#adventure-title-update-" + adventure_id);
            $("#adventure-name-" + adventure_id).text(adv_title);
        }
    });
}
///////////////////////// Set Title  //////////////////

function setTitle(id, type) {
    let nonce = $("#title-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let title = $("#the_title-" + type + "-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setTitle',
            title: title,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set Title  //////////////////

function setBadge(id, type) {
    let nonce = $("#title-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let badge = $("#the_" + type + "_badge-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setBadge',
            badge: badge,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}
///////////////////////// Set Title  //////////////////

function setColor(id, color, type) {
    let nonce = $("#title-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    $("#color-trigger-" + type + "-" + id).removeClass().addClass('button-icon font _24 sq-40').css('background-color', color);
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setColor',
            color: color,
            type: type,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
            if (type == 'tabi') {
                $("#tabi-" + id).removeClass().addClass('row-container ' + color + '-bg-100');
            }

        }
    });
}

function selectColor(id, color) {
    $(id).val(color);
}

function brPickColor(uid, inputId, hex) {
    $('#' + uid + ' .br-color-swatch').removeClass('active');
    $('#' + uid + ' .br-color-swatch[data-hex="' + hex + '"]').addClass('active');
    $('#' + uid + '_preview').css('background', hex);
    $('#' + uid).data('hex', hex);
    var opacity = parseInt($('#' + uid + '_opacity').val()) / 100;
    brSetColorValue(inputId, hex, opacity);
}

function brPickIcon(uid, inputId, icon) {
    $('#' + uid + ' .br-icon-swatch').removeClass('active');
    $('#' + uid + ' .br-icon-swatch[data-icon="' + icon + '"]').addClass('active');
    $('#' + uid + '_preview').attr('class', 'br-icon-select-preview-glyph icon icon-' + icon);
    var label = icon.replace(/[-_]/g, ' ').replace(/\w\S*/g, function (t) {
        return t.charAt(0).toUpperCase() + t.substr(1).toLowerCase();
    });
    $('#' + uid + '_preview_label').text(label);
    $(inputId).val(icon);
}

function brUpdateOpacity(uid, inputId) {
    var val = parseInt($('#' + uid + '_opacity').val());
    $('#' + uid + '_opacity_val').text(val + '%');
    var hex = $('#' + uid).data('hex') || '#9e9e9e';
    var opacity = val / 100;
    var r = parseInt(hex.substring(1, 3), 16);
    var g = parseInt(hex.substring(3, 5), 16);
    var b = parseInt(hex.substring(5, 7), 16);
    $('#' + uid + '_preview').css('background', 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')');
    brSetColorValue(inputId, hex, opacity);
}

function brSetColorValue(inputId, hex, opacity) {
    if (opacity < 1) {
        var r = parseInt(hex.substring(1, 3), 16);
        var g = parseInt(hex.substring(3, 5), 16);
        var b = parseInt(hex.substring(5, 7), 16);
        $(inputId).val('rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')');
    } else {
        $(inputId).val(hex);
    }
}

///////////////////////// Set Magic Code  //////////////////

function setMagicCode(id) {
    let nonce = $("#magic-code-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let code = $("#the_magic_code-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setMagicCode',
            code: code,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set Item Category  //////////////////

function setCategory(id) {
    let nonce = $("#item-cat-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let category = $("#the_item_category-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setCategory',
            category: category,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Item Categories (real entities) //////////////////

function addItemCategory() {
    let nonce = $('#item-category-nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveItemCategory',
            nonce: nonce,
            adventure_id: adventure_id,
            category_name: 'New Category',
            category_color: 'blue-grey'
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function saveItemCategory(categoryId) {
    let nonce = $('#item-category-nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    let $row = $('#category-row-' + categoryId);
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveItemCategory',
            nonce: nonce,
            adventure_id: adventure_id,
            category_id: categoryId,
            category_name: $row.find('.category-name-input').val(),
            category_color: $row.find('.category-color-select').val()
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function trashItemCategory(categoryId) {
    let nonce = $('#item-category-nonce').val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'trashItemCategory',
            nonce: nonce,
            category_id: categoryId
        },
        method: 'POST',
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Item / Category Conditions //////////////////

function openItemConditionsModal(targetType, targetId) {
    let $content = $('#item-conditions-content');
    $content.html('<span class="br-text-12 grey-400">Loading...</span>');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'insertItemConditionsModal',
            target_type: targetType,
            target_id: targetId
        },
        method: 'POST',
        success: function (data_received) {
            $content.html(data_received);
        }
    });
    brOpenDrawer($('#item-conditions-overlay'));
}

function closeItemConditionsModal() {
    brCloseDrawer($('#item-conditions-overlay'));
}

function saveItemConditionsModal() {
    let $body = $('#item-conditions-overlay .tabi-conditions-body');
    let targetType = $body.data('target-type');
    let targetId = $body.data('target-id');
    let adventureId = $body.data('adventure-id');
    let nonce = $body.find('.item-conditions-nonce').val();

    let questIds = [];
    $body.find('.tabi-cond-quest-checkbox:checked').each(function () { questIds.push($(this).val()); });

    let achievementIds = [];
    $body.find('.tabi-cond-achievement-checkbox:checked').each(function () { achievementIds.push($(this).val()); });

    let conditions = {};
    $body.find('.tabi-cond-threshold-input').each(function () {
        let val = $(this).val();
        if (val !== '') conditions[$(this).data('condition-type')] = val;
    });

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'saveItemConditions',
            nonce: nonce,
            target_type: targetType,
            target_id: targetId,
            adventure_id: adventureId,
            quest_ids: questIds,
            achievement_ids: achievementIds,
            conditions: conditions
        },
        method: 'POST',
        success: function (data_received) {
            closeItemConditionsModal();
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set Guild Group  //////////////////

function setGuildGroup(id) {
    let nonce = $("#guild-group-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let guild_group = $("#the_guild_group-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setGuildGroup',
            guild_group: guild_group,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

///////////////////////// Set Guild Group  //////////////////

function setGuildCapacity(id) {
    let nonce = $("#guild-capacity-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let guild_capacity = $("#the_guild_capacity-" + id).val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setGuildCapacity',
            guild_capacity: guild_capacity,
            id: id,
            adventure_id: adventure_id,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


///////////////////////// Upload Image  //////////////////
function showWPUpload(who, callback, type, q_id, o_id) {
    let file_frame;
    let the_target = $('#' + who);
    // if the file_frame has already been created, just reuse it
    if (file_frame) {
        file_frame.open();
        return;
    }
    file_frame = wp.media.frames.file_frame = wp.media({
        title: $(this).data('uploader_title'),
        button: {
            text: $(this).data('uploader_button_text'),
        },
        multiple: false // set this to true for multiple file selection
    });

    file_frame.on('select', function () {
        let attachment = file_frame.state().get('selection').first().toJSON();
        if (the_target.is('img')) {
            the_target.attr('src', attachment.url);
            the_target.parent().removeClass('empty').addClass('full');
        } else if (the_target.is('textarea')) {
            let curValue = the_target.val();
            let addImg = '<img src="' + attachment.url + '" height="100">';
            let newValue = curValue + addImg;
            $(the_target).val(newValue);
            updateStep();
        } else {
            if ($('#' + who + '_thumb_video').length > 0) {
                if (attachment.type == 'video') {
                    $('#' + who + '_thumb_video source').attr('src', attachment.url);
                    $('#' + who + '_thumb_video')[0].load();
                    $('#' + who + '_thumb_video').addClass('active');
                } else if (attachment.type == 'image') {
                    $('#' + who + '_thumb_video source').removeAttr('src');
                    $('#' + who + '_thumb_video')[0].load();
                    $('#' + who + '_thumb_video').removeClass('active');
                }
            }
            $('#' + who + '_thumb').css('background-image', 'url(' + attachment.url + ')');
            the_target.val(attachment.url);
        }
        if (type && q_id) {
            if (callback == 'q') {
                updateQuestion(type, q_id);
            } else if (callback == 'o') {
                let main_id = $('#the_' + type + '_id').val();
                updateOption(type, q_id, o_id);
            } else if (callback == 'a') {
                setBadge(q_id, type);
            } else if (callback == 'step') {
                updateStepButton(q_id);
            } else if (callback == 'c') {
                updateObjective(q_id);
            }
        }
        if (callback == 'profile-autosave') {
            updateProfile();
        }
    });
    file_frame.open();
}

function showWPUploadVideo(who) {
    let file_frame;
    let the_target = $('#' + who);
    if (file_frame) {
        file_frame.open();
        return;
    }
    file_frame = wp.media.frames.file_frame = wp.media({
        title: $(this).data('uploader_title'),
        button: {
            text: $(this).data('uploader_button_text'),
        },
        library: {
            type: 'video/mp4'
        },
        multiple: false
    });
    file_frame.on('select', function () {
        let attachment = file_frame.state().get('selection').first().toJSON();
        if (attachment.type == 'video') {
            $('#' + who + '_thumb_video source').attr('src', attachment.url);
            $('#' + who + '_thumb_video')[0].load();
            $('#' + who + '_thumb_video').addClass('active');
        }
        the_target.val(attachment.url);
    });
    file_frame.open();
}
///////////////////////// Upload Multimedia  //////////////////
function showWPUploadMultimedia(who, type, q_id) {
    let file_frame;
    let the_target = $('#' + who);
    let the_target_thumb = $('#' + who + "_thumb");
    // if the file_frame has already been created, just reuse it
    if (file_frame) {
        file_frame.open();
        return;
    }
    file_frame = wp.media.frames.file_frame = wp.media({
        title: $(this).data('uploader_title'),
        button: {
            text: $(this).data('uploader_button_text'),
        },
        multiple: false // set this to true for multiple file selection
    });

    file_frame.on('select', function () {
        let attachment = file_frame.state().get('selection').first().toJSON();
        $('#' + who + " .multimedia-element").html('');
        if (attachment.type == 'video') {
            $('#' + who + " .multimedia-element").append('<video id="' + who + '_thumb" controls class="gallery-item-video"><source src="' + attachment.url + '"> </video>');
            $('#' + who + '_thumb')[0].load();
        } else if (attachment.type == 'audio') {
            $('#' + who + " .multimedia-element").append('<audio id="' + who + '_thumb" controls class="gallery-item-audio"><source src="' + attachment.url + '"> </audio>');
            $('#' + who + '_thumb')[0].load();
        } else if (attachment.type == 'image') {
            $('#' + who + " .multimedia-element").append('<img id="' + who + '_thumb" src="' + attachment.url + '">');
        }
        the_target.val(attachment.url);
        updateQuestion(type, q_id);
    });
    file_frame.open();
}
/////////////////////// UPDATE DUPLICATE BUTTON /////////////////////////

function updateDuplicateQuestButton(id) {
    let adventure_id = $('#adventure-value-' + id).val();
    $('#duplicateButton-' + id).attr('onClick', "duplicateQuest(" + id + "," + adventure_id + ")")
}
/////////////////////// UPDATE DUPLICATE BUTTON /////////////////////////

function updateDuplicateRowButton(id, type = '') {
    let adventure_id = $('#adventure-value-' + type + '-' + id).val();
    $('#duplicateRowButton-' + type + '-' + id).attr('onClick', "duplicateRow(" + id + "," + adventure_id + ",'" + type + "')")
}
/////////////////////// DUPLICATE QUEST /////////////////////////

function duplicateQuest(quest_id = 0, adventure_id = $("#adventure_target").val()) {
    showLoader();
    let quest_data = {
        quest_id: quest_id,
        adventure_id: adventure_id
    }
    let nonce = $("#duplicator_nonce").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'duplicateQuest',
            nonce: nonce,
            quest_id: quest_id,
            adventure_id: adventure_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}


/////////////////////// REMOVE FROM LIBRARY /////////////////////////

function removeFromLibrary(id = 0, type) {
    showLoader();

    let lib_id = $("#lib_id").val();
    let nonce = $("#remove_nonce").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'removeFromLibrary',
            nonce: nonce,
            type: type,
            id: id,
            lib_id: lib_id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
        }
    });
}


/////////////////////// DUPLICATE ROW /////////////////////////

function duplicateRow(id, adventure_id = $("#the_adventure_id").val(), type = $("#row_type").val()) {
    showLoader('small');
    let nonce = $("#duplicator_nonce").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'duplicateRow',
            nonce: nonce,
            adventure_id: adventure_id,
            type: type,
            id: id
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);

        }
    });
}
/////////////////////// OPERATION CONSOLE /////////////////////////

function brOpConsoleOpen(title) {
    $('#br-op-console-title').text(title || 'Processing…');
    $('#br-op-terminal').empty();
    $('#br-op-bar').css('width', '0%');
    $('#br-op-progress-text').text('');
    $('#br-op-cancel-btn').removeClass('br-initially-hidden');
    $('#br-op-close-btn').addClass('br-initially-hidden');
    $('#br-op-console').fadeIn(180);
}

function brOpConsoleLog(msg, type) {
    type = type || 'info';
    var cls = type === 'success' ? 'br-op-line-ok'
            : type === 'error'   ? 'br-op-line-err'
            : type === 'warn'    ? 'br-op-line-warn'
            : 'br-op-line-info';
    var prefix = type === 'success' ? '✓ ' : type === 'error' ? '✗ ' : type === 'warn' ? '⚠ ' : '› ';
    var $t = $('#br-op-terminal');
    $t.append('<div class="br-op-line ' + cls + '">' + prefix + $('<div>').text(msg).html() + '</div>');
    $t[0].scrollTop = $t[0].scrollHeight;
}

function brOpConsoleSetProgress(done, total) {
    var pct = total > 0 ? Math.round(done / total * 100) : 0;
    $('#br-op-bar').css('width', pct + '%');
    $('#br-op-progress-text').text(done + ' / ' + total);
}

function brOpConsoleDone(msg) {
    $('#br-op-cancel-btn').addClass('br-initially-hidden');
    $('#br-op-close-btn').removeClass('br-initially-hidden');
    if (msg) {
        var $t = $('#br-op-terminal');
        $t.append('<div class="br-op-line br-op-line-ok">✓ ' + msg + '</div>');
        $t[0].scrollTop = $t[0].scrollHeight;
    }
    $('#br-op-bar').css('width', '100%');
}

function brOpConsoleClose() {
    $('#br-op-console').fadeOut(200);
}

/////////////////////// DUPLICATE QUESTS /////////////////////////

function duplicateQuests() {
    let duplicates = [];
    let achievement_duplicates = [];
    let item_duplicates = [];
    let tabi_duplicates = [];
    let enc_duplicates = [];
    let speakers_duplicates = [];

    $('ul#quests-to-duplicate li.active.to-duplicate').each(function (index, element) {
        duplicates.push($('input.reqs-id', this).val());
    });
    $('ul#achievements-to-duplicate li.active.to-duplicate').each(function (index, element) {
        achievement_duplicates.push($('input.reqs-id', this).val());
    });
    $('ul#items-to-duplicate li.active.to-duplicate').each(function (index, element) {
        item_duplicates.push($('input.reqs-id', this).val());
    });
    $('ul#tabis-to-duplicate li.active.to-duplicate').each(function (index, element) {
        tabi_duplicates.push($('input.reqs-id', this).val());
    });
    $('ul#encounters-to-duplicate li.active.to-duplicate').each(function (index, element) {
        enc_duplicates.push($('input.reqs-id', this).val());
    });
    $('ul#speakers-to-duplicate li.active.to-duplicate').each(function (index, element) {
        speakers_duplicates.push($('input.reqs-id', this).val());
    });
    let total = duplicates.length + achievement_duplicates.length + item_duplicates.length + tabi_duplicates.length + enc_duplicates.length + speakers_duplicates.length;
    brOpConsoleOpen('Duplicator');
    brOpConsoleLog('Preparing ' + total + ' item' + (total !== 1 ? 's' : '') + '…', 'info');

    let nonce = $("#duplicator_nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    let adventure_target = $("#adventure_target").val();
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'duplicateQuests',
            nonce: nonce,
            adventure_id: adventure_id,
            adventure_target: adventure_target,
            duplicates: duplicates,
            achievement_duplicates: achievement_duplicates,
            item_duplicates: item_duplicates,
            tabi_duplicates: tabi_duplicates,
            enc_duplicates: enc_duplicates,
            speakers_duplicates: speakers_duplicates
        }),
        method: "POST",
        success: function (json_text) {
            var d;
            try { d = JSON.parse(json_text); } catch(e) { d = null; }
            if (d && d.success) {
                var count = 0;
                $('<div>').html(d.message).find('li').each(function() {
                    var text = $(this).text().replace(/\s+/g, ' ').trim();
                    if (text) { brOpConsoleLog(text, 'success'); count++; }
                });
                brOpConsoleSetProgress(count, total);
                brOpConsoleDone('Duplication complete — ' + count + ' item' + (count !== 1 ? 's' : '') + ' duplicated.');
            } else {
                var errMsg = d ? $('<div>').html(d.message || '').text().trim() : 'Unknown error.';
                brOpConsoleLog(errMsg || 'Server error — please reload and try again.', 'error');
                brOpConsoleDone();
            }
        },
        error: function() {
            brOpConsoleLog('Request failed — please reload and try again.', 'error');
            brOpConsoleDone();
        }
    });
}

function createChildAdventure(adventure_id = null) {

    if (adventure_id) {
        showLoader();
        let nonce = $("#template_duplicator_nonce").val();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'createChildAdventure',
                nonce: nonce,
                adventure_id: adventure_id,
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
            }
        });
    } else {
        return false;
    }
}

function duplicateAdventure(adventure_id = null) {

    if (adventure_id) {
        showLoader();
        let nonce = $("#duplicate_adventure_nonce").val();
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'duplicateAdventure',
                nonce: nonce,
                adventure_id: adventure_id,
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
            }
        });
    } else {
        return false;
    }
}

function openGuildRoster(guild_id) {
    var $overlay = $('#guild-roster-overlay');
    $overlay.html('<div class="tabi-conditions-header"><h3 class="br-text-16 w700">Loading...</h3></div>');
    brOpenDrawer($overlay);
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'br_guild_roster',
            nonce: $('#guild_roster_nonce').val(),
            adventure_id: $('#the_adventure_id').val(),
            guild_id: guild_id,
        }),
        method: "POST",
        dataType: "json",
        success: function (response) {
            if (response && response.success) {
                $overlay.html(response.data.html);
            }
        }
    });
}
function closeGuildRoster() {
    brCloseDrawer($('#guild-roster-overlay'));
}
/////////////////////// BULK CREATE /////////////////////////

function bulkCreate() {
    let achievements = parseInt($("#bulk-achievements").val());
    showLoader();
    let nonce = $("#bulk_nonce").val();
    let adventure_id = $("#the_adventure_id").val();

    let starting_at = 0;

    for (let i = 0; i < achievements; i++) {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'bulkCreate',
                nonce: nonce,
                adventure_id: adventure_id,
                achievements: achievements,
            }),
            method: "POST",
            success: function (json_text) {
                displayAjaxResponse(json_text);
            }
        });

    }


}

function makeid(possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", max_length = 5) {
    let text = "";
    for (let i = 0; i < max_length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

function createMagicCode(who = "") {
    let magicCode = makeid("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", 20);
    if (!who) {
        $('#the_achievement_code').val(magicCode);
        let magicLink = $('#site-url').val() + magicCode + '&adv=' + $("#the_adventure_id").val();
        $('#the_magic_link').val(magicLink);
    } else {
        $('#the_magic_code-' + who).val(magicCode);
        setMagicCode(who);
    }
}

function revertMagicCode(who, magic_code) {
    $('#the_magic_code-' + who).val(magic_code);
    setMagicCode(who);
}

function updateMagicCode() {
    if ($('#the_achievement_code').val() != "") {
        let magicLink = $('#site-url').val() + $('#the_achievement_code').val();
        $('#the_magic_link').val(magicLink);
    } else {
        clearMagicCode();
    }
}

function clearMagicCode() {
    $('#the_achievement_code, #the_magic_link').val("");
}

function addTableRow(table_id) {
    let unique_id = makeid();
    $(table_id + " tbody tr:last-child").clone().appendTo(table_id + " tbody").attr('id', 'row-' + unique_id);
    $(table_id + " tbody tr:last-child td button.remove-row").attr('onClick', "removeTableRow('#row-" + unique_id + "');");
    $(table_id + " tbody tr:last-child td input").val('');
    $(table_id + " tbody tr:last-child td select").val(0);
}

function removeTableRow(id) {
    $(id).remove();
}



function maxLevel(who) {
    if ($(who).val() > 99) {
        $(who).val(99);
    } else if ($(who).val() < 0) {
        $(who).val(0);
    }
}

function hideAchievementReward() {
    $("#the_mech_achievement_reward li").show().removeClass('active');
    let id = $("#the_achievement_id").val();
    if (id > 0) {
        $('#achievement-reward-' + id).hide();
    }
}


function checkPublishFor() {
    $("#the_achievement_id option").show();
    let id = $("#the_mech_achievement_reward li.active .achievement-reward-id").val();
    if (id > 0) {
        if ($("#the_achievement_id").val() == id) {
            $("#the_achievement_id").val(0);
        }
        $("#the_achievement_id option").show();
        $('#achievement-option-' + id).hide();
    }
}

function toggleReq(who) {
    $(who).toggleClass("active");
    if ($('#the_quest_type').val() == "mission") {
        let min = 1;
        $("ul.select-multiple li.active").each(function () {
            if ($('.reqs-level', this).val() > min) {
                min = $('.reqs-level', this).val();
            }
        });
        $("#the_quest_level").val(min);
    }
}


function toggleSingleReq(who) {
    $(who).siblings().removeClass('active');
    $(who).toggleClass('active');
}

function selectMultiple(who) {
    $(who).toggleClass("active");
}

function activateAll(who) {
    $("#all-on").addClass('hidden');
    $("#all-off").removeClass('hidden');
    $(who).addClass("active");
}

function deactivateAll(who) {
    $("#all-off").addClass('hidden');
    $("#all-on").removeClass('hidden');
    $(who).removeClass("active");
}

function activateAllPlayerType(who) {
    $("#all-on").addClass('hidden');
    $("#all-off").removeClass('hidden');
    $("ul.player-select li").removeClass('active');
    $("ul.player-select li." + who).addClass("active");
}

function setPlayerAdventureRole(adventure_id, player_id, role = 'player') {
    showLoader('small');
    let nonce = $("#player-status-nonce").val();
    let who = $('#player-' + player_id);

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'setPlayerAdventureRole',
            adventure_id: adventure_id,
            player_id: player_id,
            role: role,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}

function updatePlayerAdventureStatus(adventure_id, player_id, status) {
    showLoader('small');
    let nonce = $("#player-status-nonce").val();
    let who = $('#player-' + player_id);

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'updatePlayerAdventureStatus',
            adventure_id: adventure_id,
            player_id: player_id,
            status: status,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            displayAjaxResponse(data_received);
        }
    });
}


function testCheckedBoxes() {
    let selected = 0;
    $('.select-element:checked').each(function (index) {
        selected++;
    });
}

function selectAllCheckBoxes() {
    const selectAllCheckbox = document.getElementById("select-all");
    if (selectAllCheckbox) {
        const userCheckboxes = document.querySelectorAll(".select-element");
        selectAllCheckbox.addEventListener("change", function () {
            userCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            testCheckedBoxes();
        });
        userCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                selectAllCheckbox.checked = Array.from(userCheckboxes).every((cb) => cb.checked);
                testCheckedBoxes();
            });
        });
    }
}


//////////////////  EXPORT PLAYERS WORK ////////////////
function exportPlayersWork() {
    const headers = [];
    $('#players-table-header .header-row .cell').each(function () {
        headers.push($('.cell-text-value', this).val());
    });
    const rows = [headers]; // Build your dynamic array of rows

    $('.player-row').each(function () {
        const row = [];
        $(this).find('.cell').each(function () {
            row.push($('.cell-text-value', this).val());
        });
        rows.push(row);
    });
    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'exportPlayersWork',
            data: JSON.stringify(rows)
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'player_export.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
        }
    });

}
//////////////////  DISPLAY AJAX RESPONSE ////////////////

function displayAjaxResponse(json_data) {
    let data = JSON.parse(json_data);
    $('.loader, .small-loader').removeClass('active');
    if (data.rating) {
        $(".rating button").removeClass('amber-bg-400');
        for (let r = 0; r < data.rating; r++) {
            $(".rating button#rating-star-" + (r + 1)).addClass('amber-bg-400');
        }
    }
    if (data.debug) {
        console.log(data.debug);
    }
    if (data.role_update) {
        $("tr#player-row-" + data.player_id).fadeOut('fast', function () {
            brRewireRoleButtons($(this), $('#the_adventure_id').val(), data.player_id, data.role_update);
            $(this)
                .removeClass('role-gm role-player role-npc')
                .addClass('role-' + data.role_update)
                .attr('data-role', data.role_update)
                .fadeIn('fast');
        });
    }

    // The org player row is rebuilt server-side and swapped in by br-org.js
    // (orgSetPlayerRole). It used to be patched here against `adventure_id`,
    // which does not exist on the org page - the ReferenceError killed the
    // fadeOut callback and the row never faded back in.

    if (data.duplicate) {
        $(data.original).clone().appendTo(data.container).attr('id', data.duplicate);

        $("#" + data.duplicate + " ." + data.type + "-id").val(data.clone_id);

        $("#" + data.duplicate + " .row-title").attr({
            'id': "the_title-" + data.type + "-" + data.clone_id,
            'onChange': "setTitle(" + data.clone_id + ",'" + data.type + "');"
        });
        $("#" + data.duplicate + " .the-xp").attr({
            'onChange': "setXP(" + data.clone_id + ",'" + data.type + "');"
        });
        $("#" + data.duplicate + " .the-bloo").attr({
            'onChange': "setBLOO(" + data.clone_id + ",'" + data.type + "');"
        });
        $("#" + data.duplicate + " .magic-code").attr({
            'onChange': "setMagicCode(" + data.clone_id + ",'" + data.type + "');"
        });
        $("#" + data.duplicate + " .the-deadline").attr({
            'onChange': "setDeadline(" + data.clone_id + ",'" + data.type + "');"
        });
        $("#" + data.duplicate + " .the-start-date").attr({
            'onChange': "setStartDate(" + data.clone_id + ",'" + data.type + "');"
        });
        let bloginfo_url = $("#bloginfo_url").val();
        let adventure_id = $("#the_adventure_id").val();


        $("#" + data.duplicate + " .duplicate-button").attr({
            'onClick': "showOverlay('#confirm-duplicate-" + data.clone_id + "');"
        });
        $("#" + data.duplicate + " .duplicate-confirm").attr({
            'id': "confirm-duplicate-" + data.clone_id
        });
        $("#" + data.duplicate + " .duplicate-confirm-button").attr({
            'onClick': "duplicateRow(" + data.clone_id + ");"
        });

        $("#" + data.duplicate + " .edit-button").attr({
            'href': bloginfo_url + "/new-" + data.type + "/?adventure_id=" + adventure_id + "&" + data.type + "_id=" + data.clone_id
        });

        $("#" + data.duplicate + " .draft-button").attr({
            'onClick': "showOverlay('#confirm-draft-" + data.clone_id + "');"
        });
        $("#" + data.duplicate + " .draft-confirm").attr({
            'id': "confirm-draft-" + data.clone_id
        });
        $("#" + data.duplicate + " .draft-confirm-button").attr({
            'onClick': "confirmStatus(" + data.clone_id + ",'" + data.type + "','draft');"
        });

        $("#" + data.duplicate + " .trash-button").attr({
            'onClick': "showOverlay('#confirm-trash-" + data.clone_id + "');"
        });
        $("#" + data.duplicate + " .trash-confirm").attr({
            'id': "confirm-trash-" + data.clone_id
        });
        $("#" + data.duplicate + " .trash-confirm-button").attr({
            'onClick': "confirmStatus(" + data.clone_id + ",'" + data.type + "','trash');"
        });
        //alert(data.original);
    }

    if (data.player_adventure_status) {
        $("tr#player-row-" + data.player_id).fadeOut('fast', function () {
            $(this).remove();
        });
    }
    // One surface for everything earned. Handlers attach a `celebrate` event list
    // (BR_Feedback); the older levelup/newly_earned shape is translated into the
    // same events rather than kept on a parallel code path. The legacy #level-up
    // explosion overlay that used to run here is gone - it was the second animation
    // that fired after a level-up that also earned a rank.
    if (data.celebrate && data.celebrate.length) {
        brCelebrate(data.celebrate);
    } else if (data.levelup || (data.newly_earned && data.newly_earned.length)) {
        showRewardsOverlay(data.levelup, data.new_level, data.newly_earned);
    }
    if (data.content && data.content_target) {
        $(data.content_target).append(data.content);
    }
    if (data.remove_element) {
        $(data.remove_element).fadeOut('fast', function () {
            $(data.remove_element).remove();
        });
    }
    if (data.remove_step) {
        $('#step-' + data.step_id).fadeOut(300, function () {
            $(this).remove();
            brRenumberSteps();
        });
    }

    if (data.removed_step_button) {
        $("#step-button-" + data.button).fadeOut(300, function () {
            $(this).remove();
        });
    }
    if (data.messages) {
        let message_delay = 1000;
        if (data.message_delay) {
            message_delay = data.message_delay;
        }
        for (let i = 0; i < data.messages.length; i++) {
            $("#notify-message ul.content").append(data.messages[i]);
            $("#notify-message ul.content li:last-child").delay(300).addClass('active').delay(message_delay).removeClass('active', function () {
                $(this).remove();
            });
        }
    }

    if (data.message) {
        let message_delay = 1000;
        if (data.message_delay) {
            message_delay = data.message_delay;
        }
        if (data.just_notify) {
            $("#notify-message ul.content").append(data.message);
            setTimeout(function () {
                $("#notify-message ul.content li:last-child").addClass('active');
                let last_message = $("#notify-message ul.content li:last-child");
                setTimeout(function () {
                    last_message.removeClass('active');
                    setTimeout(function () {
                        last_message.remove();
                        if (data.reload) {
                            document.location.reload();
                        }
                    }, 300);

                }, message_delay);
            }, 1);
        } else {
            $("#feedback .content").html(data.message);
            $("#feedback").addClass('active');
            if (data.autofade) {
                $("#feedback").unbind('click');
                hideAllOverlay();
            }
            if (!data.noClose) {
                if (data.location) {
                    $("#feedback").click(function () {
                        if (data.location == 'reload') {
                            document.location.reload();
                        } else {
                            document.location.href = data.location;
                        }
                    });
                } else {
                    $("#feedback").click(function () {
                        $("#feedback").unbind('click');
                        hideAllOverlay();
                    });
                }
            }
        }
    }
    if (data.sale == true) {
        $('.hud-screen-video').removeClass('active');
        $('.hud-screen-content').removeClass('active');
        $('#hud-video-status-sale').addClass('active');
        $('#hud-video-status-sale').get(0).pause();
        $('#hud-video-status-sale').get(0).play();

        let shopkeeperSaleTimeout = setTimeout(function () {
            $('#hud-video-status-sale').removeClass('active');
            $('#hud-video-status-idle').addClass('active');
            $('#hud-video-status-idle').get(0).pause();
            $('#hud-video-status-idle').get(0).play();
        }, 5100);
    }
    if (data.update_ux) {
        if (data.update_ux.player_picture) {
            $('#profile-box-btn, #status-animated-chart, .player-picture').css('background-image', 'url(' + data.update_ux.player_picture + ')');
        }
        if (data.update_ux.nickname) {
            $('#status-player-display-name, .player-nickname').text(data.update_ux.nickname);
        }
    }
    if (data.jumpToNext) {
        if (data.jumpToNext == 'last') {
            submitPlayerWork();
        } else {
            document.location.href = `#step-${data.jumpToNext}`;
        }
    }
    if (data.question_updated) {
        $("#accordion-tab-question-" + data.question_id + " .question-text").html(data.question_updated);
    }

    if (data.loadContent) {
        $('#small-loader').addClass('active');
        $(data.loadContent.element).html('');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'loadContent',
                content: data.loadContent.file,
                id: data.loadContent.id
            }),
            method: "POST",
            success: function (data_received) {
                $(data.loadContent.element).html(data_received);
                let flipTimeout = setTimeout(function () {
                    $('#small-loader').removeClass('active');
                }, 500);
            }
        });

    }


    if (data.new_grade_nonce) {
        $("#grade_nonce").val(data.new_grade_nonce);
    }
    if (data.new_dimensions_nonce) {
        $("#dimensions_nonce").val(data.new_dimensions_nonce);
    }
    if (data.new_bloo_nonce) {
        $("#bloo-nonce").val(data.new_bloo_nonce);
    }
    if (data.new_max_players_nonce) {
        $("#max-players-nonce").val(data.new_max_players_nonce);
    }
    if (data.new_xp_nonce) {
        $("#xp-nonce").val(data.new_xp_nonce);
    }
}
let journeyState = {
    x: 0,
    y: 0,
    scale: 1
};
const MIN_SCALE = 0.3,
    MAX_SCALE = 2.0;

function viewportCenterX() {
    return document.querySelector('.journey-container').clientWidth / 2;
}

function viewportCenterY() {
    return document.querySelector('.journey-container').clientHeight / 2;
}

function applyZoom() {
    document.getElementById('the-journey').style.transform =
        `translate(${journeyState.x}px, ${journeyState.y}px) scale(${journeyState.scale})`;
}

function changeScale(delta, cx, cy) {
    const newScale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, journeyState.scale * delta));
    journeyState.x = cx - (cx - journeyState.x) * (newScale / journeyState.scale);
    journeyState.y = cy - (cy - journeyState.y) * (newScale / journeyState.scale);
    journeyState.scale = newScale;
    applyZoom();
}

function resizeJourneyMapWithPadding(padding = 300, map = 'the-journey', milestoneContainer = '.milestone-container') {
    let $map = $('#' + map);
    let maxX = 0,
        maxY = 0;
    $(milestoneContainer).each(function () {
        let x = $(this).position().left;
        let y = $(this).position().top;
        if (x > maxX) maxX = x;
        if (y > maxY) maxY = y;
    });
    $map.css({
        width: (maxX + padding) + 'px',
        height: (maxY + padding) + 'px'
    });
}

function centerJourneyMap() {
    let container = document.querySelector('.journey-container');
    let map = document.getElementById('the-journey');
    let containerWidth = container.clientWidth;
    let containerHeight = container.clientHeight;
    let mapWidth = map.offsetWidth;
    let mapHeight = map.offsetHeight;
    journeyState.x = (containerWidth - mapWidth * journeyState.scale) / 2;
    journeyState.y = (containerHeight - mapHeight * journeyState.scale) / 2;
    applyZoom();
}

function toggleJourneyView() {
    $('#the-journey').toggleClass('journey-map journey-board');
    journeyState = {
        x: 0,
        y: 0,
        scale: 1
    };
    if ($('#the-journey').hasClass('journey-map')) {
        resizeJourneyMapWithPadding();
        centerJourneyMap();
    } else {
        applyZoom();
    }
}

document.addEventListener('DOMContentLoaded', function () {

    if ($('.datetimepicker').length > 0) {
        $('.datepicker').datetimepicker({
            format: "Y-m-d",
            timepicker: false
        });
        $('.datetimepicker, .the_start_date, .deadline, .the_deadline').datetimepicker({
            format: "Y/m/d H:i"
        });
    }

    $(".sortable").sortable({
        items: "tr:not(.unsortable), li:not(.unsortable), div:not(.unsortable)",
        update: function (event, ui) {

        }
    });
    $(".sortable").disableSelection();
    $(".sortable-row-container").sortable({
        items: ".row-container",
        update: function (event, ui) {

        }
    });
    $(".sortable-with-handle").disableSelection();

    $("ul.select-single li").click(function () {
        if (!$(this).hasClass('label')) {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        }
    });
    $('#the_achievement_code, input.achievement-code').keypress(function (e) {
        let regex = new RegExp("^[a-zA-Z 0-9]");
        let str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str)) {
            return true;
        }
        e.preventDefault();
        return false;
    });
    if (hash_change_type == 'quest') {
        jumpToStepByHash();
        window.addEventListener("hashchange", jumpToStepByHash);
    }
    if (hash_change_type == 'survey') {
        jumpToQuestionByHash();
        window.addEventListener("hashchange", jumpToStepByHash);
    }
    if (hash_change_type == 'settings-tab') {
        changeTabByHash();
        window.addEventListener("hashchange", changeTabByHash);
    }
    if (hash_change_type == 'journey') {
        var _tabiHash = window.location.hash.match(/^#tabi-(\d+)$/);
        if (_tabiHash) openTabiModal(parseInt(_tabiHash[1], 10));
    }

    var journeyViewport = document.querySelector('.journey-container');
    if (journeyViewport && !journeyViewport.classList.contains('board-mode')) {
        var activePointers = new Map();
        var lastPinchDist = null;
        var dragStart = {
            x: 0,
            y: 0
        };
        var pointerDownPos = {
            x: 0,
            y: 0
        };
        var isPanning = false;
        var hadMultiTouch = false;
        var touchMoved = false;
        var DRAG_THRESHOLD = 6; // px — below this is a tap, above is a pan

        function getPinchMidpoint() {
            var pts = Array.from(activePointers.values());
            return {
                x: (pts[0].x + pts[1].x) / 2,
                y: (pts[0].y + pts[1].y) / 2
            };
        }

        function getPinchDistance() {
            var pts = Array.from(activePointers.values());
            var dx = pts[0].x - pts[1].x,
                dy = pts[0].y - pts[1].y;
            return Math.sqrt(dx * dx + dy * dy);
        }

        journeyViewport.addEventListener('pointerdown', function (e) {
            activePointers.set(e.pointerId, {
                x: e.clientX,
                y: e.clientY
            });
            if (activePointers.size === 2) {
                hadMultiTouch = true;
                lastPinchDist = getPinchDistance();
                journeyViewport.setPointerCapture(e.pointerId);
            } else if (activePointers.size === 1) {
                hadMultiTouch = false;
                isPanning = false;
                pointerDownPos = {
                    x: e.clientX,
                    y: e.clientY
                };
                dragStart = {
                    x: e.clientX - journeyState.x,
                    y: e.clientY - journeyState.y
                };
            }
        });

        journeyViewport.addEventListener('pointermove', function (e) {
            if (!activePointers.has(e.pointerId)) return;
            activePointers.set(e.pointerId, {
                x: e.clientX,
                y: e.clientY
            });

            if (activePointers.size === 2) {
                var newDist = getPinchDistance();
                if (lastPinchDist) {
                    var mid = getPinchMidpoint();
                    changeScale(newDist / lastPinchDist, mid.x, mid.y);
                }
                lastPinchDist = newDist;
            } else if (activePointers.size === 1) {
                if (!isPanning) {
                    // Only commit to panning once threshold is exceeded
                    var dx = e.clientX - pointerDownPos.x;
                    var dy = e.clientY - pointerDownPos.y;
                    if (dx * dx + dy * dy < DRAG_THRESHOLD * DRAG_THRESHOLD) return;
                    isPanning = true;
                    journeyViewport.setPointerCapture(e.pointerId); // capture only now
                }
                journeyState.x = e.clientX - dragStart.x;
                journeyState.y = e.clientY - dragStart.y;
                applyZoom();
            }
        });

        journeyViewport.addEventListener('pointerup', function (e) {
            activePointers.delete(e.pointerId);
            lastPinchDist = null;
            // touchmove.preventDefault() suppresses the browser-synthesized click on touch.
            // Re-dispatch it manually for taps: single touch, no pan, no pinch, touchmove fired.
            if (!isPanning && !hadMultiTouch && e.pointerType === 'touch' && activePointers.size === 0 && touchMoved) {
                var tapTarget = document.elementFromPoint(e.clientX, e.clientY);
                if (tapTarget) tapTarget.click();
            }
            isPanning = false;
            if (activePointers.size === 1) {
                var rem = activePointers.values().next().value;
                dragStart = {
                    x: rem.x - journeyState.x,
                    y: rem.y - journeyState.y
                };
            }
        });
        journeyViewport.addEventListener('pointercancel', function (e) {
            activePointers.delete(e.pointerId);
            lastPinchDist = null;
            isPanning = false;
        });

        // Reset touchMoved at the start of each fresh single-finger gesture
        journeyViewport.addEventListener('touchstart', function (e) {
            if (e.touches.length === 1) touchMoved = false;
        });
        // Hard-block iOS Safari pull-to-refresh; track that touchmove fired
        journeyViewport.addEventListener('touchmove', function (e) {
            touchMoved = true;
            e.preventDefault();
        }, {
            passive: false
        });
        // Wheel-to-zoom toward the cursor position
        journeyViewport.addEventListener('wheel', function (e) {
            e.preventDefault();
            var rect = journeyViewport.getBoundingClientRect();
            changeScale(e.deltaY < 0 ? 1.1 : 0.9, e.clientX - rect.left, e.clientY - rect.top);
        }, {
            passive: false
        });
    }
});

$(document).keyup(function (e) {
    if (e.keyCode === 27) {
        brCloseTopDrawer();
        hideAllOverlay();
        loadSidebar();
        unloadCard();
    } // esc
});

////////////////////////////////////////// REQUESTS ////////////////////////////////////////////
function submitRequest() {
    let nonce = $('#request-nonce').val();
    let adventure_id = $('#the_adventure_id').val();
    let subject = $('#request-subject').val().trim();
    let content = $('#request-content').val().trim();

    if (!subject || !content) {
        notify("Please fill in all fields", "cancel","red");
        return;
    }

    $('#small-loader').addClass('active');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'submitRequest',
            adventure_id: adventure_id,
            request_subject: subject,
            request_content: content,
            nonce: nonce
        },
        method: "POST",
        success: function (data) {
            $('.loader, .small-loader').removeClass('active');
            if (data) {
                $('#request-subject').val('');
                $('#request-content').val('');
                displayAjaxResponse(data);
                hideAllOverlay();
            } else {
                 notify("Error", "cancel","red");
            }
        }
    });
}

function loadRequests(status) {
    if (typeof status === 'undefined') status = 'all';
    let adventure_id = $('#the_adventure_id').val();
    let nonce = $('#request-nonce').val();
    $('.request-filter-btn').removeClass('active');
    $('#request-filter-' + status).addClass('active');
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'getRequests',
            adventure_id: adventure_id,
            status: status,
            nonce: nonce
        },
        method: "POST",
        success: function (data) {
            hideAllOverlay();
            $('#requests-list').html(data);
        }
    });
}

function loadMyRequests(status) {
    if (typeof status === 'undefined') status = 'all';
    let adventure_id = $('#the_adventure_id').val();
    $('.request-filter-btn').removeClass('active');
    $('#request-filter-' + status).addClass('active');
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'getMyRequests',
            adventure_id: adventure_id,
            status: status
        },
        method: "POST",
        success: function (data) {
            hideAllOverlay();
            $('#my-requests-list').html(data);
        }
    });
}

function updateRequestStatus(request_id, new_status) {
    let nonce = $('#request-nonce').val();
    let admin_note = $('#admin-note-' + request_id).length ? $('#admin-note-' + request_id).val() : '';
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: {
            action: 'updateRequestStatus',
            request_id: request_id,
            new_status: new_status,
            admin_note: admin_note,
            nonce: nonce
        },
        method: "POST",
        success: function (data) {
            displayAjaxResponse(data);
            hideAllOverlay();
            let currentFilter = $('.request-filter-btn.active').data('status') || 'all';
            loadRequests(currentFilter);
        }
    });
}

////////////////////////////// PLAYER STEP INTERACTIONS ///////////////////////////
function brStepAjax(stepId, questId, adventureId, response, onSuccess) {
    showLoader('small');
    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: { action: 'br_complete_step', step_id: stepId, quest_id: questId, adventure_id: adventureId, response: response },
        success: function(json) {
            displayAjaxResponse(json);
            var data = JSON.parse(json);
            if (onSuccess) onSuccess(data);
        }
    });
}

function brShowStepNext(contextId) {
    var $step = $('#' + contextId).closest('.step');
    if (!$step.length) $step = $('[id$="-' + contextId + '"]').closest('.step').first();
    if (!$step.length) return;
    var idx = $('.step').index($step);
    var $nextStep = $('.step').eq(idx + 1);
    // Hide the submit button inside the dialogue box
    $step.find('.steps-navigation.action-buttons').hide();
    // Create nav as direct child of .step-content-container (same level as dialogue-box)
    var $container = $step.find('.step-content-container');
    var $nav = $container.children('.steps-navigation.br-step-nav-injected');
    if (!$nav.length) {
        $nav = $('<div class="steps-navigation br-step-nav-injected"></div>');
        $container.append($nav);
    }
    if ($nextStep.length) {
        var nextOrder = $nextStep.attr('id').replace('step-', '');
        $nav.html(
            '<a class="step-nav-button step-next" href="#step-' + nextOrder + '">' +
            '<svg id="button-step-next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 172"><rect class="outline" x="1" y="1" width="170" height="170"/><rect class="back-color" x="15.69" y="15.69" width="140.63" height="140.63"/><polygon class="main-arrow" points="129.27 86.02 70.31 51.98 70.31 71.73 44.66 71.73 44.66 100.31 70.31 100.31 70.31 120.06 129.27 86.02"/></svg></a>'
        ).show();
    } else {
        $nav.html(
            '<button class="action-button success" onClick="submitPlayerWork();">Submit</button>'
        ).show();
    }
}

function brShowFeedback(elemId, correct, message) {
    var el = $(elemId);
    if (correct) {
        el.css({ background: 'rgba(36,218,152,0.15)', color: '#24da98', border: '1px solid rgba(36,218,152,0.3)' }).html('<span class="icon icon-check"></span> ' + (message || 'Correct!')).show();
    } else {
        el.css({ background: 'rgba(244,67,54,0.15)', color: '#f44336', border: '1px solid rgba(244,67,54,0.3)' }).html('<span class="icon icon-cancel"></span> ' + (message || 'Incorrect. Try again.')).show();
    }
}

function brSubmitMcStep(stepId, questId, advId) {
    var selected = [];
    $('#mc-options-' + stepId + ' .mc-input:checked').each(function() { selected.push($(this).val()); });
    if (!selected.length) return;
    brStepAjax(stepId, questId, advId, { selected: selected }, function(data) {
        if (data.result && data.result.correct === 1) {
            brShowFeedback('#mc-feedback-' + stepId, true);
            $('#mc-submit-' + stepId).hide();
            $('#mc-options-' + stepId + ' .mc-input').prop('disabled', true);
            $('#mc-options-' + stepId + ' .mc-input:checked').closest('.br-step-option').addClass('br-option-correct');
            brShowStepNext('mc-feedback-' + stepId);
        } else {
            brShowFeedback('#mc-feedback-' + stepId, false, data.result ? data.result.mistake_message : null);
        }
    });
}

function brSubmitKpStep(stepId, questId, advId) {
    var answer = $('#kp-answer-' + stepId).val();
    if (!answer) return;
    brStepAjax(stepId, questId, advId, { answer: answer }, function(data) {
        if (data.result && data.result.correct === 1) {
            brShowFeedback('#kp-feedback-' + stepId, true);
            $('#kp-submit-' + stepId).hide();
            $('#kp-answer-' + stepId).prop('disabled', true);
            brShowStepNext('kp-feedback-' + stepId);
        } else {
            brShowFeedback('#kp-feedback-' + stepId, false, data.result ? data.result.mistake_message : null);
            $('#kp-answer-' + stepId).val('').focus();
        }
    });
}

function brSubmitCryptexStep(stepId, questId, advId) {
    var answer = '';
    $('#cryptex-' + stepId + ' .cryptex-wheel').each(function() { answer += $(this).val(); });
    if (!answer) return;
    brStepAjax(stepId, questId, advId, { answer: answer }, function(data) {
        if (data.result && data.result.correct === 1) {
            brShowFeedback('#cryptex-feedback-' + stepId, true, 'Unlocked!');
            $('#cryptex-' + stepId + ' .cryptex-wheel').prop('disabled', true);
            brShowStepNext('cryptex-feedback-' + stepId);
        } else {
            brShowFeedback('#cryptex-feedback-' + stepId, false, data.result ? data.result.mistake_message : null);
            $('#cryptex-' + stepId + ' .cryptex-wheel').val('').first().focus();
        }
    });
}

function brSubmitGenericStep(stepId, questId, advId, extraData, contextId) {
    brStepAjax(stepId, questId, advId, extraData || {}, function(data) {
        if (data.success) {
            if (contextId) { brShowStepNext(contextId); }
            else { location.reload(); }
        }
    });
}

function brSubmitSurveyChoice(stepId, questId, advId) {
    var selected = [];
    $('#sc-options-' + stepId + ' .sc-input:checked').each(function() { selected.push($(this).val()); });
    if (!selected.length) return;
    brStepAjax(stepId, questId, advId, { selected: selected }, function(data) {
        if (data.success) {
            $('#sc-submit-' + stepId).hide();
            $('#sc-options-' + stepId + ' .sc-input').prop('disabled', true);
            $('#sc-options-' + stepId + ' .sc-input:checked').closest('.br-step-option').addClass('br-option-correct');
            brShowStepNext('sc-options-' + stepId);
        }
    });
}

function brSubmitPoll(stepId, questId, advId) {
    var selected = [];
    $('#poll-options-' + stepId + ' .poll-input:checked').each(function() { selected.push($(this).val()); });
    if (!selected.length) return;
    brStepAjax(stepId, questId, advId, { selected: selected }, function(data) {
        if (data.success) { location.reload(); }
    });
}

function brSelectRating(stepId, value) {
    $('#sr-value-' + stepId).val(value);
    $('#sr-buttons-' + stepId + ' .sr-rating-btn').removeClass('br-rating-active');
    $('#sr-buttons-' + stepId + ' .sr-rating-btn').each(function() {
        if (parseInt($(this).data('value')) <= value) {
            $(this).addClass('br-rating-active');
        }
    });
}

function brSubmitSurveyRating(stepId, questId, advId) {
    var value = $('#sr-value-' + stepId).val();
    if (!value) return;
    brStepAjax(stepId, questId, advId, { value: value }, function(data) {
        if (data.success) { location.reload(); }
    });
}

function brUploadStepImage(stepId, questId, advId) {
    var frame = wp.media({ multiple: false, library: { type: 'image' } });
    frame.on('select', function() {
        var url = frame.state().get('selection').first().toJSON().url;
        brStepAjax(stepId, questId, advId, { url: url }, function(data) {
            if (data.success) { location.reload(); }
        });
    });
    frame.open();
}

function brUploadStepVideo(stepId, questId, advId) {
    var frame = wp.media({ multiple: false, library: { type: 'video' } });
    frame.on('select', function() {
        var url = frame.state().get('selection').first().toJSON().url;
        brStepAjax(stepId, questId, advId, { url: url }, function(data) {
            if (data.success) { location.reload(); }
        });
    });
    frame.open();
}

function brChooseBranch(advId, groupId, achievementId, stepId, questId) {
    if (!confirm('This choice is permanent. Are you sure?')) return;
    showLoader('small');
    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: { action: 'br_player_branch_choice', adventure_id: advId, group_id: groupId, achievement_id: achievementId },
        success: function(json) {
            displayAjaxResponse(json);
            var data = JSON.parse(json);
            if (data.success) {
                brStepAjax(stepId, questId, advId, { group_id: groupId, achievement_id: achievementId }, function() {
                    location.reload();
                });
            }
        }
    });
}

// ── Open Text: word count + AI validation ────────────────────────
function brCountWords(html) {
    var div = document.createElement('div');
    div.innerHTML = html;
    div.querySelectorAll('img, a, iframe, video, audio').forEach(function(el) { el.remove(); });
    var text = (div.textContent || div.innerText || '').trim();
    if (!text) return 0;
    return text.split(/\s+/).filter(function(w) { return w.length > 0; }).length;
}

function brGetOpenTextContent() {
    if (typeof tinyMCE === 'object' && typeof tinyMCE.triggerSave === 'function') {
        tinyMCE.triggerSave();
    }
    return $('#the_pp_content').val() || '';
}

function brCheckOpenText(stepId) {
    var $container = $('[data-step-id="' + stepId + '"].open-field');
    var minWords = parseInt($container.attr('data-min-words')) || 0;
    var aiValidate = $container.attr('data-ai-validate') === '1';
    var $feedback = $('#ot-feedback-' + stepId);
    var content = brGetOpenTextContent();
    var wordCount = brCountWords(content);

    $feedback.removeClass('br-step-feedback-error br-step-feedback-success').html('');

    if (minWords > 0 && wordCount < minWords) {
        $feedback.addClass('br-step-feedback-error').html(
            '<span class="icon icon-cancel"></span> ' +
            brI18n.ot_min_words.replace('%d', minWords).replace('%c', wordCount)
        );
        return;
    }

    if (aiValidate) {
        $feedback.html('<span class="icon icon-data"></span> ' + brI18n.ot_ai_checking);
        showLoader('small');
        $.ajax({
            url: runAJAX.ajaxurl,
            method: 'POST',
            data: {
                action: 'br_ai_validate_text',
                step_id: stepId,
                quest_id: $container.attr('data-quest-id'),
                adventure_id: $container.attr('data-adventure-id'),
                content: content
            },
            success: function(raw) {
                var data = (typeof raw === 'string') ? JSON.parse(raw) : raw;
                if (data.valid) {
                    brOpenTextPassed(stepId, content);
                } else {
                    $feedback.addClass('br-step-feedback-error').html('<span class="icon icon-cancel"></span> ' + (data.message || brI18n.ot_ai_fail));
                }
            },
            error: function() {
                brOpenTextPassed(stepId, content);
            }
        });
        return;
    }

    brOpenTextPassed(stepId, content);
}

function brOpenTextPassed(stepId, content) {
    var $feedback = $('#ot-feedback-' + stepId);
    $feedback.removeClass('br-step-feedback-error').html('');

    // Hide editor, show answer display
    $('#ot-editor-wrap-' + stepId).hide();
    $('#ot-answer-text-' + stepId).html(content);
    $('#ot-answer-' + stepId).show();
    $('#ot-success-' + stepId).show();

    // Show next step navigation
    brShowStepNext('ot-success-' + stepId);
}

function brEditOpenText(stepId) {
    $('#ot-answer-' + stepId).hide();
    $('#ot-success-' + stepId).hide();
    // Hide the injected next nav
    $('[data-step-id="' + stepId + '"]').find('.br-step-nav-injected').remove();
    $('#ot-editor-wrap-' + stepId).show();
}

var brI18n = window.brI18n || {};
brI18n.ot_min_words = brI18n.ot_min_words || 'You need at least %d words. You have %c.';
brI18n.ot_ai_checking = brI18n.ot_ai_checking || 'Validating your content...';
brI18n.ot_ai_pass = brI18n.ot_ai_pass || 'Content validated!';
brI18n.ot_ai_fail = brI18n.ot_ai_fail || 'Your response doesn\'t seem to address the question. Please revise and try again.';
// Celebration panel. The verbs normally arrive translated inside the event payload
// (BR_Feedback); these cover the back-compat path and the joining words.
brI18n.you_just = brI18n.you_just || 'You just';
brI18n.and = brI18n.and || 'and';
brI18n.level_up = brI18n.level_up || 'Level Up!';
brI18n.verb_levelup = brI18n.verb_levelup || 'leveled up';
brI18n.verb_achievement = brI18n.verb_achievement || 'earned an achievement';
brI18n.verb_rank = brI18n.verb_rank || 'reached a new rank';
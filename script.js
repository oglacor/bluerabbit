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

function enrollUser(p_email = null) {
    showLoader();
    //$('#btn-reg-player').unbind('click');
    $('#btn-reg-player').attr('disabled', true);
    let new_user = '';
    let email = p_email;
    if (p_email == 'new') {
        email = $('#new-player-email').val();
        new_user = 'make-new';
    }
    let nickname = $('#new-player-username').val();
    let password = $('#new-player-user-password').val();
    let lang = $('#new-player-lang').val();
    let nonce = $('#register_nonce').val();

    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'enrollUser',
            adventure_id: $('#the_adventure_id').val(),
            new_user: new_user,
            nickname: nickname,
            email: email,
            password: password,
            lang: lang,
            nonce: nonce
        }),
        method: "POST",
        success: function (data_received) {
            let this_data = JSON.parse(data_received);
            if (this_data.success == false) {
                $('#btn-reg-player').attr('disabled', false);
            } else {
                $('#btn-reg-player').unbind('click');
            }
            displayAjaxResponse(data_received);
        },
    });
}

function checkUserDataExists(input_field) {
    showLoader('small');
    $('#btn-reg-player').unbind('click');
    $('#add-single-player-form, #add-single-player-form .player-data-content').removeClass('active');
    $('#new-player-username, #new-player-email, #new-player-user-password').val('');
    if (input_field.value != '') {
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                action: 'checkUserDataExists',
                value: input_field.value,
                adventure_id: $('#the_adventure_id').val()
            }),
            method: "POST",
            success: function (data_received) {
                let data = JSON.parse(data_received);
                hideAllOverlay();
                if (data.warning) {
                    $('#new-player-warnings').text(data.warning);
                    $('#new-player-warnings').removeClass().addClass(data.warning_class + " new-player-warnings");
                    $("#register_nonce").val(data.new_nonce);
                }
                if (data.user_exists == true && data.user_enroll_status == 'out') {
                    $('#add-single-player-form').addClass('active');
                    $('#btn-reg-player').click(function () {
                        enrollUser(data.user_email);
                    });
                } else if (data.user_exists == false) {
                    $('#add-single-player-form, #add-single-player-form .player-data-content').addClass('active');
                    if (data.is_email == true) {
                        $('#new-player-email').val(input_field.value).attr({
                            'readonly': true,
                            'disabled': true
                        });
                        $('#new-player-username').val('');
                    } else {
                        $('#new-player-username').val(input_field.value).attr({
                            'readonly': true,
                            'disabled': true
                        });
                        $('#new-player-email').val('');
                    }

                    $('#btn-reg-player').click(function () {
                        enrollUser('new');
                    });
                }
                $("#notify-message ul.content").append(data.message);
                $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function () {
                    $(this).remove();
                });
            },
        });
    } else {
        $('#new-player-warnings').text("Please enter a nickname or email.");
        $('#new-player-warnings').removeClass().addClass("error new-player-warnings");
        hideAllOverlay();
    }
}

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
            $('#challenge-timer .progress').removeClass('warning').addClass("danger");
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
////////////////////////////// SHARED DRAWER BACKDROP (Step + any Conditions drawer) ///////////////////////////
// One dynamically-injected backdrop reused by every drawer, instead of one per page/consumer.
// Re-parented into $container (when given) instead of always living on <body>, so its
// z-index is only ever compared against its actual drawer sibling - not the whole DOM.
// A Step drawer lives inside .br-step-item, which jQuery UI Sortable can promote into its
// own stacking context (drag transforms) - a body-level backdrop would then be compared
// against that trapped context instead of the drawer itself and could paint on top of it.
function brShowDrawerBackdrop($container) {
    var $target = $container && $container.length ? $container : $('body');
    var $backdrop = $('#br-drawer-backdrop');
    if (!$backdrop.length) {
        $backdrop = $('<div class="br-drawer-backdrop" id="br-drawer-backdrop" onclick="brCloseTopDrawer();"></div>');
    }
    $target.append($backdrop);
    $('body').addClass('br-drawer-open');
}

function brHideDrawerBackdrop() {
    var anyOpen = $('.br-step-accordion.open').length
        || $('.tabi-conditions-overlay.active, .item-conditions-overlay.active, .quest-conditions-overlay.active').length;
    if (!anyOpen) {
        $('body').removeClass('br-drawer-open');
    }
}

function brCloseTopDrawer() {
    $('.br-step-accordion.open').each(function () {
        closeStepAccordion(this.id.replace('step-accordion-', ''));
    });
    $('.tabi-conditions-overlay.active').each(function () {
        closeTabiConditionsModal(this.id.replace('tabi-conditions-overlay-', ''));
    });
    if ($('#item-conditions-overlay').hasClass('active')) { closeItemConditionsModal(); }
    if ($('#quest-conditions-overlay').hasClass('active')) { closeQuestConditionsModal(); }
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
            $accordion.html(data_received).addClass('open');
            $('#step-' + step_id + ' .br-step-edit-btn').addClass('active');
            brShowDrawerBackdrop($('#step-' + step_id));
            $('.loader, .small-loader').removeClass('active');
        }
    });
}

function closeStepAccordion(step_id) {
    var editorId = 'step-content-' + step_id;
    if (typeof tinymce !== 'undefined') {
        try { tinymce.remove('#' + editorId); } catch(e) {}
    }
    $('#step-accordion-' + step_id).removeClass('open').html('');
    $('#step-' + step_id + ' .br-step-edit-btn').removeClass('active');
    brHideDrawerBackdrop();
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
            settings = { min_words: parseInt($('#step-ot-minwords-' + sid).val()) || 0, use_wp_editor: !!parseInt($('#step-ot-editor-' + sid).val()), ai_validate: !!parseInt($('#step-ot-ai-' + sid).val()) };
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
                    'multiple_choice':'#7c4dff','keyphrase':'#00bcd4','cryptex':'#00bcd4','puzzle':'#9f40e2','backpack_item':'#e040fb','scorm':'#00bcd4',
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
    'multiple_choice':'validate','keyphrase':'validate','cryptex':'validate','puzzle':'validate','backpack_item':'validate','scorm':'validate',
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
////////////////////////////////////////// Add Player To ORGANIZATION ////////////////////////////////////////////

function setPlayerOrgCapabilities(player_id = null, role) {
    if (player_id) {
        showLoader('small');
        jQuery.ajax({
            url: runAJAX.ajaxurl,
            data: ({
                org_id: $("#the_org_id").val(),
                player_id: player_id,
                role: role,
                action: 'setPlayerOrgCapabilities',
            }),
            method: "POST",
            success: function (json_text) {
                hideAllOverlay();
                displayAjaxResponse(json_text);
            }
        });
    } else {
        notification('#msg-player-not-added-to-org', 1000, 'Player not added to org', 'cancel');
    }
}
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
        showLoader();
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
                displayAjaxResponse(json_text);
                var d = JSON.parse(json_text);
                if (d.success && d.assigned_ids) {
                    d.assigned_ids.forEach(function (pid) {
                        $('#player-achievement-' + pid).addClass('active');
                    });
                }
                fileInput.value = '';
            }
        });
    };
    reader.readAsText(fileInput.files[0]);
}
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
            // For quests that also use Validate/Invalidate, the server now derives
            // validated/not-validated straight from this grade (>0 = validated) - keep
            // the two buttons' active/disabled state in sync without a reload.
            let gradeNum = parseFloat(grade);
            if (!isNaN(gradeNum)) {
                if (gradeNum > 0) {
                    $("#validate-btn-" + player_id + "-" + quest_id).removeClass('br-form-btn-green').prop('disabled', true);
                    $("#invalidate-btn-" + player_id + "-" + quest_id).addClass('br-form-btn-red').prop('disabled', false);
                } else {
                    $("#validate-btn-" + player_id + "-" + quest_id).addClass('br-form-btn-green').prop('disabled', false);
                    $("#invalidate-btn-" + player_id + "-" + quest_id).removeClass('br-form-btn-red').prop('disabled', true);
                }
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
            // force it to a flat 100/0) - reflect whatever grade it actually resolved to,
            // not just what this button click implies.
            let grade = data.grade;
            if (grade > 0) {
                $("#validate-btn-" + player_id + "-" + quest_id).removeClass('br-form-btn-green').prop('disabled', true);
                $("#invalidate-btn-" + player_id + "-" + quest_id).addClass('br-form-btn-red').prop('disabled', false);
            } else {
                $("#validate-btn-" + player_id + "-" + quest_id).addClass('br-form-btn-green').prop('disabled', false);
                $("#invalidate-btn-" + player_id + "-" + quest_id).removeClass('br-form-btn-red').prop('disabled', true);
            }
            $("#the_post_grade_" + quest_id + "_" + player_id).val(grade);
        }
    });
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

function openTabiModal(tabiId) {
    $('.tabi-modal').removeClass('active');
    $('#tabi-modal-' + tabiId).addClass('active');
    $('#tabi-modal-overlay').addClass('active');
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
    $('.tabi-modal').removeClass('active');
    $('#tabi-modal-overlay').removeClass('active');
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
    $overlay.addClass('active');
    brShowDrawerBackdrop();
}

function closeTabiConditionsModal(tabiId) {
    $('#tabi-conditions-overlay-' + tabiId).removeClass('active');
    brHideDrawerBackdrop();
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
    $('#quest-conditions-overlay').addClass('active');
    brShowDrawerBackdrop();
}

function closeQuestConditionsModal() {
    $('#quest-conditions-overlay').removeClass('active');
    brHideDrawerBackdrop();
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
    $('#item-conditions-overlay').addClass('active');
    brShowDrawerBackdrop();
}

function closeItemConditionsModal() {
    $('#item-conditions-overlay').removeClass('active');
    brHideDrawerBackdrop();
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


///////////////////////// break parent  //////////////////

function breakParent(id, type) {
    let nonce = $("#break-parent-nonce").val();
    let adventure_id = $("#the_adventure_id").val();
    showLoader('small');
    jQuery.ajax({
        url: runAJAX.ajaxurl,
        data: ({
            action: 'breakParent',
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
    showLoader();
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
            enc_duplicates: enc_duplicates
        }),
        method: "POST",
        success: function (json_text) {
            displayAjaxResponse(json_text);
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
            let adventure_id = $('#the_adventure_id').val();
            $('button.role-button-npc', this).attr('onclick', "setPlayerAdventureRole(" + adventure_id + "," + data.player_id + ",'npc');");
            $('button.role-button-player', this).attr('onclick', "setPlayerAdventureRole(" + adventure_id + "," + data.player_id + ",'player');");
            $('button.role-button-gm', this).attr('onclick', "showOverlay('#confirm-gm-" + data.player_id + "');");

            $('button.role-button-' + data.role_update, this).removeAttr('onclick');
            $(this).removeClass('role-gm role-player role-npc').addClass('role-' + data.role_update).fadeIn('fast');
        });
    }

    if (data.org_role_update) {
        $("tr#player-org-row-" + data.player_id).fadeOut('fast', function () {
            $('button.role-button-npc', this).attr('onclick', "setPlayerAdventureRole(" + adventure_id + "," + data.player_id + ",'npc');");
            $('button.role-button-player', this).attr('onclick', "setPlayerAdventureRole(" + adventure_id + "," + data.player_id + ",'player');");
            $('button.role-button-gm', this).attr('onclick', "showOverlay('#confirm-gm-" + data.player_id + "');");

            $('button.role-button-' + data.role_update, this).removeAttr('onclick');
            $(this).removeClass('role-gm role-player role-npc').addClass('role-' + data.role_update).fadeIn('fast');
        });
    }

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
    if (data.levelup) {
        if (data.achievement_id) {
            $("#level-up .content").html(data.levelupContent).hide();
            $("#level-up").addClass('active');
            $("#level-up .level-up-bg").delay(500).fadeIn('fast', function () {
                loadAchievementCard(data.achievement_id, 1);
            });
            $("#level-up").click(function () {
                $("#level-up").unbind('click');
                hideAllOverlay();
                unloadCard();
            });
        } else {
            $("#level-up .content").html(data.levelupContent).hide();
            $("#level-up .achievement-image").attr('style', 'background-image:url(' + data.levelupBG + ');').hide();
            $("#level-up").addClass('active');
            $("#level-up .level-up-bg").delay(500).fadeIn('fast', function () {
                $("#level-up .content").fadeIn(500).delay(15000).fadeOut(1, function () {
                    hideAllOverlay();
                });
            });
            $("#level-up").click(function () {
                $("#level-up").unbind('click');
                hideAllOverlay();
            });

        }
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
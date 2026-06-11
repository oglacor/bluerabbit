/**
 * SCORM 1.2 LMS API adapter for BlueRabbit.
 *
 * Key design decisions:
 *  - Iframes start with src="about:blank" in the template. We set the real src
 *    only when the containing .step gains the .active class (lazy-load). This
 *    prevents every SCORM step on the page from loading and calling LMSInitialize
 *    simultaneously, which would corrupt the shared state object.
 *  - State is seeded synchronously in activateScormFrame() before the src is set,
 *    so LMSGetValue works on the very first call without any async fetch.
 *  - completionFired is a same-session double-call guard only. It is NEVER
 *    initialised from stored lesson_status — doing so caused a permanent deadlock
 *    for any user who had previously completed the step.
 */

(function () {
    'use strict';

    var state = {
        initialized:     false,
        lessonStatus:    'not attempted',
        lessonLocation:  '',
        suspendData:     '',
        stepId:          null,
        questId:         null,
        adventureId:     null,
        nextStep:        0,
        nonce:           null,
        completionFired: false   // same-session guard only, never set from stored data
    };

    /** Seed state from window.brScormData for the step inside stepEl. */
    function seedFromStep(stepEl) {
        var wrapper = stepEl && stepEl.querySelector('.scorm-wrapper');
        if (!wrapper) return false;

        var id   = wrapper.dataset.stepId;
        var seed = window.brScormData && window.brScormData[id];
        if (!seed) return false;

        state.lessonStatus    = seed.lessonStatus   || 'not attempted';
        state.lessonLocation  = seed.lessonLocation || '';
        state.suspendData     = seed.suspendData    || '';
        state.stepId          = String(seed.stepId);
        state.questId         = seed.questId;
        state.adventureId     = seed.adventureId;
        state.nextStep        = parseInt(seed.nextStep, 10) || 0;
        state.nonce           = seed.nonce;
        state.completionFired = false;   // always fresh — previous completion does not block re-entry
        state.initialized     = false;
        return true;
    }

    /**
     * Load the SCORM iframe for a step element.
     * Seeds state first so LMSGetValue works immediately when the content calls
     * LMSInitialize — no race condition.
     */
    function activateScormFrame(stepEl) {
        var frame = stepEl && stepEl.querySelector('.scorm-frame[data-src]');
        if (!frame) return;

        var src = frame.getAttribute('data-src');
        if (!src || frame.src === src) return;   // already loaded or no package

        seedFromStep(stepEl);
        frame.src = src;
    }

    /** Persist a single field to user meta via AJAX. Fire-and-forget. */
    function persist(key, value) {
        if (!state.stepId || typeof jQuery === 'undefined' || typeof runAJAX === 'undefined') return;
        var payload = {
            action:  'br_scorm_save_data',
            nonce:   state.nonce,
            step_id: state.stepId
        };
        payload[key] = value;
        jQuery.post(runAJAX.ajaxurl, payload);
    }

    /** Advance the quest to the next step. Same-session guard prevents double-call. */
    function advance() {
        if (state.completionFired) return;
        state.completionFired = true;

        // Reveal the fallback next button immediately so the user can always continue
        // even if they navigate back before the auto-jump fires.
        var nextBtn = document.getElementById('scorm-next-' + state.stepId);
        if (nextBtn) nextBtn.classList.add('active');

        setTimeout(function () {
            if (state.nextStep > 0) {
                if (typeof jumpToStep === 'function') jumpToStep(state.nextStep);
            } else {
                if (typeof submitPlayerWork === 'function') submitPlayerWork();
            }
        }, 600);
    }

    // ── SCORM 1.2 API ────────────────────────────────────────────────────────

    window.API = {
        LMSInitialize: function (param) {
            // State was already seeded by activateScormFrame() before src was set.
            // seedFromStep is a no-op fallback here; the important thing is the flag.
            state.initialized = true;
            return 'true';
        },

        LMSFinish: function (param) {
            state.initialized = false;
            return 'true';
        },

        LMSGetValue: function (element) {
            switch (element) {
                case 'cmi.core.lesson_status':   return state.lessonStatus;
                case 'cmi.core.lesson_location':  return state.lessonLocation;
                case 'cmi.suspend_data':          return state.suspendData;
                case 'cmi.core.entry':
                    return (state.lessonStatus === 'incomplete') ? 'resume' : 'ab-initio';
                case 'cmi.core.student_id':   return '0';
                case 'cmi.core.student_name': return '';
                default:                      return '';
            }
        },

        LMSSetValue: function (element, value) {
            switch (element) {
                case 'cmi.core.lesson_status':
                    state.lessonStatus = value;
                    persist('lesson_status', value);
                    if (value === 'completed' || value === 'passed') advance();
                    break;

                case 'cmi.core.lesson_location':
                    state.lessonLocation = value;
                    persist('lesson_location', value);
                    break;

                case 'cmi.suspend_data':
                    state.suspendData = value;
                    persist('suspend_data', value);
                    break;
            }
            return 'true';
        },

        LMSCommit:         function (param)     { return 'true'; },
        LMSGetLastError:   function ()          { return '0'; },
        LMSGetErrorString: function (errorCode) { return ''; },
        LMSGetDiagnostic:  function (errorCode) { return ''; }
    };

    // ── Lazy-load: activate the right iframe when a step becomes active ───────

    document.addEventListener('DOMContentLoaded', function () {
        // Load whichever step is already active on first render
        var active = document.querySelector('.step.active');
        if (active) activateScormFrame(active);

        // Watch for jumpToStep adding .active to other steps
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mut) {
                if (mut.attributeName === 'class' && mut.target.classList.contains('active')) {
                    activateScormFrame(mut.target);
                }
            });
        });
        document.querySelectorAll('.step').forEach(function (step) {
            observer.observe(step, { attributes: true, attributeFilter: ['class'] });
        });
    });

    // ── Admin helper: reset all user attempts ────────────────────────────────

    window.brResetScorm = function (stepId, nonce) {
        if (!confirm('Reset ALL users for this SCORM step? This cannot be undone.')) return;
        jQuery.ajax({
            url:    runAJAX.ajaxurl,
            type:   'POST',
            data:   { action: 'br_scorm_reset_all', nonce: nonce, step_id: stepId },
            success: function (raw) { displayAjaxResponse(raw); }
        });
    };

    // ── Admin helper: upload SCORM zip from step-form ────────────────────────

    window.brUploadScorm = function (stepId, adventureId) {
        var fileInput = document.getElementById('scorm-zip-' + stepId);
        if (!fileInput || !fileInput.files[0]) {
            alert('Please select a .zip file first.');
            return;
        }

        var nonce = document.getElementById('scorm-nonce-' + stepId).value;
        var fd    = new FormData();
        fd.append('action',       'br_scorm_upload');
        fd.append('nonce',        nonce);
        fd.append('step_id',      stepId);
        fd.append('adventure_id', adventureId);
        fd.append('scorm_zip',    fileInput.files[0]);

        jQuery.ajax({
            url:         runAJAX.ajaxurl,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: function (raw) {
                displayAjaxResponse(raw);
                var data = (typeof raw === 'string') ? JSON.parse(raw) : raw;
                if (data && data.success && data.launch_url) {
                    var info = document.getElementById('scorm-info-' + stepId);
                    if (info) {
                        info.className = 'font _12 green-color padding-5-0';
                        info.innerHTML = '<span class="icon icon-check"></span> ' +
                            '<span class="grey-400">' + data.launch_url + '</span>';
                    }
                    var btn = document.getElementById('scorm-upload-btn-' + stepId);
                    if (btn) btn.innerHTML = '<span class="icon icon-upload"></span> Replace Package';
                }
            }
        });
    };

})();

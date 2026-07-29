/**
 * Parent-page bridge listener for embedded HTML case study steps.
 *
 * Key design decisions:
 *  - Iframes start with src="about:blank" in the template. We set the real src
 *    only when the containing .step gains the .active class (lazy-load), same
 *    reasoning as scorm-api.js: prevents every case-study step on the page from
 *    loading and reporting simultaneously.
 *  - Unlike SCORM (same-window window.parent.API), the launch file communicates
 *    over postMessage — it's a fully self-contained third-party page, not one
 *    written to this theme's API. Every message is checked against both
 *    e.origin AND e.source (the specific iframe's contentWindow) before acting
 *    on it — there is no existing postMessage precedent in this codebase to
 *    lean on, so both checks are deliberate, not boilerplate.
 *  - The server independently re-verifies pass/fail from saved progress
 *    (BR_Step::validateStepResponse()'s 'case_study_html' branch) — this file
 *    only decides when to ask the server, never whether the player passed.
 */

(function () {
    'use strict';

    var state = {
        stepId:          null,
        questId:         null,
        adventureId:     null,
        nextStep:        0,
        nonce:           null,
        savedState:      null,
        iframeWindow:    null,
        completionFired: false,
        lastSaveAt:      0,
        pendingSaveTimer: null
    };

    var SAVE_THROTTLE_MS = 5000;

    /** Seed state from window.brCaseStudyData for the step inside stepEl. */
    function seedFromStep(stepEl) {
        var wrapper = stepEl && stepEl.querySelector('.casestudy-wrapper');
        if (!wrapper) return false;

        var id   = wrapper.dataset.stepId;
        var seed = window.brCaseStudyData && window.brCaseStudyData[id];
        if (!seed) return false;

        state.stepId          = String(seed.stepId);
        state.questId         = seed.questId;
        state.adventureId     = seed.adventureId;
        state.nextStep        = parseInt(seed.nextStep, 10) || 0;
        state.nonce           = seed.nonce;
        state.savedState      = seed.savedState || null;
        state.completionFired = false;
        state.lastSaveAt      = 0;
        if (state.pendingSaveTimer) { clearTimeout(state.pendingSaveTimer); state.pendingSaveTimer = null; }

        var frame = wrapper.querySelector('.casestudy-frame');
        state.iframeWindow = frame ? frame.contentWindow : null;
        return true;
    }

    /** Load the case-study iframe for a step element (lazy, same as SCORM). */
    function activateCaseStudyFrame(stepEl) {
        var frame = stepEl && stepEl.querySelector('.casestudy-frame[data-src]');
        if (!frame) return;

        var src = frame.getAttribute('data-src');
        if (!src || frame.src === src) return; // already loaded or not configured

        seedFromStep(stepEl);
        frame.src = src;
        // contentWindow only exists once the src assignment above has taken effect
        state.iframeWindow = frame.contentWindow;
    }

    /** Fire-and-forget progress save, throttled to at most once per 5s (trailing). */
    function saveProgress(payload) {
        if (!state.stepId || typeof jQuery === 'undefined' || typeof runAJAX === 'undefined') return;

        var doSave = function () {
            state.lastSaveAt = Date.now();
            state.pendingSaveTimer = null;
            jQuery.post(runAJAX.ajaxurl, {
                action:  'br_casestudy_progress',
                nonce:   state.nonce,
                step_id: state.stepId,
                state:   JSON.stringify(payload)
            });
        };

        var elapsed = Date.now() - state.lastSaveAt;
        if (elapsed >= SAVE_THROTTLE_MS) {
            doSave();
        } else if (!state.pendingSaveTimer) {
            state.pendingSaveTimer = setTimeout(doSave, SAVE_THROTTLE_MS - elapsed);
        }
    }

    function showStatus(stepId, message) {
        var wrapper = document.querySelector('.casestudy-wrapper[data-step-id="' + stepId + '"]');
        var container = wrapper ? wrapper.closest('.step-content-container') : null;
        if (!container) return;
        var el = container.querySelector('.br-step-feedback-error');
        if (!el) {
            el = document.createElement('div');
            el.className = 'br-step-feedback br-step-feedback-error';
            container.insertBefore(el, wrapper.nextSibling);
        }
        el.innerHTML = '<span class="icon icon-cancel"></span> ' + message;
    }

    /** Advance the quest to the next step. Same-session guard prevents double-call. */
    function advance() {
        if (state.completionFired) return;
        state.completionFired = true;

        var nextBtn = document.getElementById('casestudy-next-' + state.stepId);
        if (nextBtn) nextBtn.classList.add('active');

        var wrapper = nextBtn ? nextBtn.closest('.step-content-container') : null;
        if (wrapper && !wrapper.querySelector('.br-step-feedback-success')) {
            var fb = document.createElement('div');
            fb.className = 'br-step-feedback br-step-feedback-success';
            fb.innerHTML = '<span class="icon icon-check"></span> You have completed this content';
            wrapper.insertBefore(fb, nextBtn);
        }

        setTimeout(function () {
            if (state.nextStep > 0) {
                if (typeof jumpToStep === 'function') jumpToStep(state.nextStep);
            } else {
                if (typeof submitPlayerWork === 'function') submitPlayerWork();
            }
        }, 600);
    }

    function postComplete(d) {
        if (!state.stepId || typeof jQuery === 'undefined' || typeof runAJAX === 'undefined') return;
        jQuery.post(runAJAX.ajaxurl, {
            action:       'br_casestudy_complete',
            nonce:        state.nonce,
            step_id:      state.stepId,
            quest_id:     state.questId,
            adventure_id: state.adventureId,
            state:        JSON.stringify({ score: d.score, total: d.total, qstate: d.qstate, seen: d.seen, screen: d.screen })
        }, function (raw) {
            var res = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (res && res.success && res.result && res.result.correct !== 0) {
                advance();
            } else {
                // Server disagreed with the client's own pass claim — treat as
                // not-passed rather than silently doing nothing.
                showStatus(state.stepId, 'Not quite — you need to pass inside the activity to continue.');
            }
        });
    }

    // ── postMessage listener ─────────────────────────────────────────────────

    window.addEventListener('message', function (e) {
        if (e.origin !== window.location.origin) return;
        if (!state.iframeWindow || e.source !== state.iframeWindow) return;

        var d = e.data || {};
        if (d.type === 'br_casestudy') {
            if (d.event === 'ready') {
                if (state.savedState && state.iframeWindow) {
                    state.iframeWindow.postMessage({
                        type:   'br_casestudy_restore',
                        score:  state.savedState.score,
                        qstate: state.savedState.qstate,
                        seen:   state.savedState.seen,
                        screen: state.savedState.screen
                    }, window.location.origin);
                }
            } else if (d.event === 'answer' || d.event === 'navigate') {
                saveProgress({ score: d.score, total: d.total, qstate: d.qstate, seen: d.seen, screen: d.screen });
            } else if (d.event === 'complete' && d.pass) {
                postComplete(d);
            } else if (d.event === 'complete' && !d.pass) {
                showStatus(state.stepId, 'Score ' + d.score + '/' + d.total + ' — you need ' +
                    (window.brCaseStudyData && window.brCaseStudyData[state.stepId] ? window.brCaseStudyData[state.stepId].passScore : 14) +
                    ' to pass. Use Try Again inside the activity.');
            } else if (d.event === 'restart') {
                state.completionFired = false;
            }
        }
    });

    // ── Lazy-load: activate the right iframe when a step becomes active ───────

    document.addEventListener('DOMContentLoaded', function () {
        var active = document.querySelector('.step.active');
        if (active) activateCaseStudyFrame(active);

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mut) {
                if (mut.attributeName === 'class' && mut.target.classList.contains('active')) {
                    activateCaseStudyFrame(mut.target);
                }
            });
        });
        document.querySelectorAll('.step').forEach(function (step) {
            observer.observe(step, { attributes: true, attributeFilter: ['class'] });
        });
    });

})();

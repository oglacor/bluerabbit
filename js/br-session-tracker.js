/**
 * Time-in-app heartbeat tracker.
 *
 * Key design decisions:
 *  - sessionStorage (not localStorage) keys the current session_id to THIS TAB
 *    only, so navigating between pages in the same tab continues one session,
 *    but opening a new tab starts a separate one - matches how "a session"
 *    is normally understood.
 *  - Heartbeats only fire while the tab is visible (document.hidden === false).
 *    A backgrounded/minimized tab stops pinging and resumes (with an immediate
 *    ping) when it becomes visible again - this avoids inflating duration for
 *    a tab left open but unattended.
 *  - The server (br_session_ping) does the actual duration accounting, capping
 *    each increment - this file just decides WHEN to ping, not how much time
 *    to credit.
 */

(function () {
    'use strict';

    if (!window.brSessionTracker) return;
    var cfg = window.brSessionTracker;
    if (typeof jQuery === 'undefined' || typeof runAJAX === 'undefined') return;

    var STORAGE_KEY = 'br_session_id_' + cfg.adventureId;
    var PING_INTERVAL_MS = 30000;
    var timer = null;

    function ping() {
        var sessionId = sessionStorage.getItem(STORAGE_KEY) || '';
        jQuery.post(cfg.ajaxurl, {
            action: 'br_session_ping',
            nonce: cfg.nonce,
            adventure_id: cfg.adventureId,
            session_id: sessionId
        }, function (res) {
            if (res && res.success && res.data && res.data.session_id) {
                sessionStorage.setItem(STORAGE_KEY, res.data.session_id);
            }
        }, 'json');
    }

    function startInterval() {
        if (timer) return;
        timer = setInterval(function () {
            if (!document.hidden) ping();
        }, PING_INTERVAL_MS);
    }

    function stopInterval() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopInterval();
        } else {
            ping();
            startInterval();
        }
    });

    // Initial ping on page load (extends the existing tab session, or starts one).
    ping();
    startInterval();

})();

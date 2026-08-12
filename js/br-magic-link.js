/**
 * Magic-link landing page.
 *
 * Only job: play the reward sting when a code actually redeemed. The <audio> element
 * is rendered by page-magic-link.php on the success branch only, so its absence is
 * how this file knows the code was refused - there is no flag to check.
 *
 * Split out of the template because inline <script> is not used in this theme, and
 * because autoplay needs the same guarded treatment brCelebrate() gives it: browsers
 * refuse to play without a prior gesture, and a refused promise must not surface as
 * an unhandled rejection on a page whose whole point is good news.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var audio = document.getElementById('audio-funky');
        if (!audio) return;

        var attempt = function () {
            var p = audio.play();
            if (p && p.catch) {
                p.catch(function () {
                    // Blocked until the visitor interacts. Rearm on their first gesture;
                    // a successful play never reaches this handler, so no listener is
                    // left behind once it works.
                    document.addEventListener('click', attempt, { once: true });
                    document.addEventListener('keydown', attempt, { once: true });
                });
            }
        };

        attempt();
    });
})();

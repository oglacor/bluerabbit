<?php
/**
 * Generic operation console — dark terminal-style overlay.
 *
 * Include this partial on any page that runs a long operation (bulk duplicate,
 * bulk email send, bulk achievement assign). The overlay is driven by the
 * brOpConsole* helpers in script.js:
 *
 *   brOpConsoleOpen('Title');          // show overlay, reset state
 *   brOpConsoleLog('msg', 'info');     // add a line  (info | success | error | warn)
 *   brOpConsoleSetProgress(done, total); // update bar + counter
 *   brOpConsoleDone('Done!');          // switch to close button
 *   brOpConsoleClose();                // hide overlay
 */
?>
<div class="br-op-console-overlay" id="br-op-console" style="display:none">
	<div class="br-op-console-box">

		<div class="br-op-console-head">
			<span class="br-import-console-dots"><i></i><i></i><i></i></span>
			<span class="br-op-console-title" id="br-op-console-title"><?= __("Processing…","bluerabbit"); ?></span>
			<span class="br-op-console-progress" id="br-op-progress-text"></span>
		</div>

		<div class="br-import-bar-track">
			<div class="br-import-bar" id="br-op-bar"></div>
		</div>

		<div class="br-op-terminal" id="br-op-terminal"></div>

		<div class="br-op-console-foot">
			<button type="button" class="br-btn red" id="br-op-cancel-btn" onClick="brOpConsoleClose();">
				<span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?>
			</button>
			<button type="button" class="br-btn ghost br-initially-hidden" id="br-op-close-btn" onClick="brOpConsoleClose();">
				<span class="icon icon-check"></span> <?= __("Close","bluerabbit"); ?>
			</button>
		</div>

	</div>
</div>

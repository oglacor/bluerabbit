<?php
/**
 * Live terminal for the bulk player import (script.js -> BRImport).
 *
 * Shared by page-new-adventure.php and page-manage-players.php. It is a partial
 * rather than copy-pasted markup because the ids below are addressed directly by
 * the importer, so two drifting copies would break one page silently.
 *
 * Pair it with a file picker that calls brImportPickFile(), a #import_players_nonce
 * hidden input and a #the_adventure_id hidden input.
 */
?>
<div class="br-import-overlay" id="br-import-overlay">
	<div class="br-import-console">
		<div class="br-import-console-head">
			<span class="br-import-console-dots"><i></i><i></i><i></i></span>
			<span class="br-import-console-title"><?= __("Player Import","bluerabbit"); ?></span>
			<span class="br-import-console-progress" id="br-import-progress-text">0 / 0</span>
		</div>

		<div class="br-import-bar-track"><div class="br-import-bar" id="br-import-bar"></div></div>

		<div class="br-import-stats">
			<span class="br-import-stat br-import-stat-created">
				<strong id="br-imp-count-created">0</strong> <?= __("created","bluerabbit"); ?>
			</span>
			<span class="br-import-stat br-import-stat-enrolled">
				<strong id="br-imp-count-enrolled">0</strong> <?= __("enrolled","bluerabbit"); ?>
			</span>
			<span class="br-import-stat br-import-stat-failed">
				<strong id="br-imp-count-failed">0</strong> <?= __("failed","bluerabbit"); ?>
			</span>
			<span class="br-import-stat br-import-stat-guilds">
				<strong id="br-imp-count-guilds">0</strong> <?= __("guilds created","bluerabbit"); ?>
			</span>
		</div>

		<div class="br-import-terminal" id="br-import-terminal"></div>

		<div class="br-import-console-foot">
			<button type="button" class="br-btn red" id="br-import-cancel" onClick="brCancelPlayerImport();">
				<span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?>
			</button>
			<button type="button" class="br-btn ghost br-initially-hidden" id="br-import-report" onClick="brImportDownloadReport();">
				<span class="icon icon-download"></span> <?= __("Download report","bluerabbit"); ?>
			</button>
			<button type="button" class="br-btn cyan br-initially-hidden" id="br-import-reload" onClick="document.location.reload();">
				<span class="icon icon-repeat"></span> <?= __("Reload player list","bluerabbit"); ?>
			</button>
			<button type="button" class="br-btn ghost br-initially-hidden" id="br-import-close" onClick="brCloseImportConsole();">
				<?= __("Close","bluerabbit"); ?>
			</button>
		</div>
	</div>
</div>

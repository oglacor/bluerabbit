<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if ($adventure) { ?>
<?php
$is_manager = ($isGM || $isAdmin || $isNPC);
if (!$is_manager) {
	echo '<div class="br-page"><div class="br-panel">' . __("You don't have access to this page.", "bluerabbit") . '</div></div>';
	include (get_stylesheet_directory() . '/footer.php');
	exit;
}

$meta     = new BR_PlayerMeta();
$page     = isset($_GET['pg']) ? max(1, (int) $_GET['pg']) : 1;
$per_page = 30;
$result   = $meta->get_players_with_meta($adv_child_id, $per_page, ($page - 1) * $per_page);
$players  = $result['players'];
$total    = $result['total'];
$total_pages = max(1, ceil($total / $per_page));
?>

<script>
window.brMeta = {
	ajaxurl: '<?= admin_url("admin-ajax.php"); ?>',
	nonce: '<?= wp_create_nonce("br_stats_nonce"); ?>',
	adventureId: <?= (int) $adv_child_id; ?>,
	fields: <?= wp_json_encode( BR_PlayerMeta::FIELDS ); ?>
};
</script>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(28,194,235,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(28,194,235,0.4)">
			<span class="icon icon-settings" style="font-size:28px;color:#1cc2eb"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= __("Player Meta Manager", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?> &middot; <?= number_format($total); ?> <?= __("players", "bluerabbit"); ?></span>
		</div>
	</div>

	<!-- CSV Import -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-image"></span> <?= __("Bulk Update via CSV", "bluerabbit"); ?></h3>
		<span class="br-panel-subtitle"><?= __("First row must be a header row. Columns are matched by name (Email, Gender, Level, Function, Sub-Function, Job Profile, Business Pillar, Cluster, Country, Location) - any order, any subset. Rows are matched to existing players in this adventure by email.", "bluerabbit"); ?></span>

		<div class="br-form-group">
			<input type="file" id="br-meta-csv-file" accept=".csv,text/csv" class="br-input" style="max-width:400px">
			<button class="br-btn" style="margin-left:8px" onClick="brMetaPreviewCsv();"><span class="icon icon-check"></span> <?= __("Preview", "bluerabbit"); ?></button>
		</div>

		<div id="br-meta-csv-preview" class="br-initially-hidden">
			<div class="br-form-component" style="margin-bottom:16px">
				<span class="br-form-label" style="display:block;margin-bottom:8px"><?= __("Detected columns", "bluerabbit"); ?></span>
				<div id="br-meta-csv-columns" style="font-size:13px;color:rgba(255,255,255,0.7)"></div>
			</div>
			<div id="br-meta-csv-summary" style="margin-bottom:12px;font-size:13px;color:rgba(255,255,255,0.7)"></div>
			<div style="overflow-x:auto">
				<table class="table transparent-bg br-stats-table" id="br-meta-csv-table">
					<thead>
						<tr>
							<td><?= __("Row", "bluerabbit"); ?></td>
							<td><?= __("Email", "bluerabbit"); ?></td>
							<td><?= __("Player", "bluerabbit"); ?></td>
							<td><?= __("Mapped values", "bluerabbit"); ?></td>
						</tr>
					</thead>
					<tbody id="br-meta-csv-table-body"></tbody>
				</table>
			</div>
			<div style="margin-top:16px;text-align:right">
				<button class="br-btn br-btn-green" onClick="brMetaCommitCsv();"><span class="icon icon-check"></span> <?= __("Commit Import", "bluerabbit"); ?></button>
			</div>
		</div>
	</div>

	<!-- Players -->
	<div class="br-panel">
		<div class="br-stats-search-wrap">
			<input type="text" class="br-input br-max-w-300" id="br-meta-player-search" placeholder="<?= esc_attr__("Search players...", "bluerabbit"); ?>">
		</div>
		<table class="table transparent-bg br-stats-table" id="br-meta-player-table">
			<thead>
				<tr>
					<td></td>
					<td><?= __("Player", "bluerabbit"); ?></td>
					<td><?= __("Country", "bluerabbit"); ?></td>
					<td><?= __("Function", "bluerabbit"); ?></td>
					<td><?= __("Business Pillar", "bluerabbit"); ?></td>
					<td><?= __("Level", "bluerabbit"); ?></td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($players as $p) { ?>
				<tr class="br-meta-player-row" data-uid="<?= (int) $p['player_id']; ?>" data-search="<?= esc_attr(strtolower($p['display_name'] . ' ' . $p['user_email'])); ?>" onClick="brMetaToggleRow(<?= (int) $p['player_id']; ?>);" style="cursor:pointer">
					<td class="text-center"><span class="icon icon-edit" style="opacity:0.4"></span></td>
					<td>
						<span class="br-stats-player-name">
							<img src="<?= esc_url(get_avatar_url($p['player_id'], ['size' => 32])); ?>" class="br-stats-avatar-sm" alt="">
							<?= esc_html($p['display_name']); ?>
						</span>
					</td>
					<td><?= esc_html($p['work_country'] ?: '—'); ?></td>
					<td><?= esc_html($p['work_function'] ?: '—'); ?></td>
					<td><?= esc_html($p['business_pillar'] ?: '—'); ?></td>
					<td><?= esc_html($p['work_level'] ?: '—'); ?></td>
				</tr>
				<tr class="br-detail-row br-initially-hidden" id="br-meta-detail-<?= (int) $p['player_id']; ?>">
					<td colspan="6">
						<div class="br-form-grid">
							<?php foreach (BR_PlayerMeta::FIELDS as $col => $label) { ?>
							<div class="br-form-group" style="margin-bottom:10px">
								<label class="br-form-label"><?= esc_html($label); ?></label>
								<input class="br-input br-meta-field" type="text" data-field="<?= esc_attr($col); ?>" value="<?= esc_attr($p[$col] ?? ''); ?>">
							</div>
							<?php } ?>
						</div>
						<div style="text-align:right">
							<button class="br-btn br-btn-green" onClick="brMetaSavePlayer(<?= (int) $p['player_id']; ?>, this);"><span class="icon icon-check"></span> <?= __("Save", "bluerabbit"); ?></button>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<?php if ($total_pages > 1) { ?>
		<div class="br-stats-pagination">
			<?php for ($i = 1; $i <= $total_pages; $i++) { ?>
			<a href="?<?= http_build_query(array_merge($_GET, ['pg' => $i])); ?>" class="br-stats-page-link<?= $i == $page ? ' active' : ''; ?>"><?= $i; ?></a>
			<?php } ?>
		</div>
		<?php } ?>
	</div>

</div><!-- /.br-page -->

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
<?php } else { ?>
<script>document.location.href = "<?php echo get_bloginfo('url') . "/404"; ?>";</script>
<?php } ?>

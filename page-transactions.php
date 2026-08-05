<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure && ($isGM || $isAdmin || $isNPC)){ ?>
<?php
$transactions = $wpdb->get_results("SELECT
	a.player_id, a.player_display_name, a.player_email,
	b.trnx_id, b.trnx_use, b.trnx_date, b.trnx_amount, b.trnx_type,
	c.item_name, c.item_type, c.item_id
	FROM {$wpdb->prefix}br_players a
	JOIN {$wpdb->prefix}br_transactions b ON a.player_id = b.player_id
	JOIN {$wpdb->prefix}br_items c ON b.object_id = c.item_id
	WHERE b.adventure_id=$adventure->adventure_id AND b.trnx_status='publish'
		AND b.trnx_type IN ('consumable','key','reward','tabi-piece','gift-card')
	ORDER BY b.trnx_use ASC, b.trnx_id ASC LIMIT 2000
");

$type_badges = [
	'key'        => 'br-badge-purple',
	'consumable' => 'br-badge-red',
	'reward'     => 'br-badge-teal',
	'tabi-piece' => 'br-badge-amber',
	'gift-card'  => 'br-badge-blue',
];

// Filter dropdown options - built from the same already-loaded result set,
// no extra query needed.
$item_options = [];
$player_options = [];
foreach ($transactions as $t) {
	if (!isset($item_options[$t->item_id])) $item_options[$t->item_id] = $t->item_name;
	if (!isset($player_options[$t->player_id])) $player_options[$t->player_id] = $t->player_display_name;
}
asort($item_options);
asort($player_options);
?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar"><span class="icon icon-transactions"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
			<h1 class="br-page-title"><?= __("Transactions","bluerabbit"); ?></h1>
		</div>
	</div>

	<!-- Nav -->
	<div class="br-tabs" id="item-shop-nav">
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-basket"></span> <?= __("Shop","bluerabbit"); ?></a>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-backpack"></span> <?= __("Backpack","bluerabbit"); ?></a>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/tabis/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-sabotage"></span> <?= __("Tabis","bluerabbit"); ?></a>
		<span class="br-tab-btn active"><span class="icon icon-transactions"></span> <?= __("Transactions","bluerabbit"); ?></span>
	</div>

	<!-- Filters -->
	<div class="br-panel">
		<div class="br-trnx-controls">
			<div class="br-actions">
				<button class="br-btn" onClick="setTrnxQuickFilter('all');" title="<?= __('Show all', 'bluerabbit'); ?>">
					<span class="icon icon-infinite"></span> <?= __("All", "bluerabbit"); ?>
				</button>
				<button class="br-btn br-btn-green" onClick="setTrnxQuickFilter('new');" title="<?= __('New only', 'bluerabbit'); ?>">
					<span class="icon icon-check"></span> <?= __("New", "bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="setTrnxQuickFilter('used');" title="<?= __('Used only', 'bluerabbit'); ?>">
					<span class="icon icon-restore"></span> <?= __("Used", "bluerabbit"); ?>
				</button>
			</div>

			<!-- Item / Player filters -->
			<select class="br-input" id="trnx-filter-item">
				<option value=""><?= __("All Items", "bluerabbit"); ?></option>
				<?php foreach ($item_options as $iid => $iname) { ?>
				<option value="<?= (int) $iid; ?>"><?= esc_html($iname); ?></option>
				<?php } ?>
			</select>
			<select class="br-input" id="trnx-filter-player">
				<option value=""><?= __("All Players", "bluerabbit"); ?></option>
				<?php foreach ($player_options as $pid => $pname) { ?>
				<option value="<?= (int) $pid; ?>"><?= esc_html($pname); ?></option>
				<?php } ?>
			</select>

			<!-- Search -->
			<div class="br-trnx-search">
				<span class="icon icon-search br-trnx-search-icon"></span>
				<input type="text" class="br-input br-trnx-search-input" id="search-trnxs" placeholder="<?= __('Search transactions', 'bluerabbit'); ?>">
			</div>
			<script>
			var trnxQuickFilter = 'all';
			function setTrnxQuickFilter(f) { trnxQuickFilter = f; applyTrnxFilters(); }
			function applyTrnxFilters() {
				var q = ($('#search-trnxs').val() || '').toLowerCase();
				var itemFilter = $('#trnx-filter-item').val();
				var playerFilter = $('#trnx-filter-player').val();
				$('table#table-trnxs tbody > tr').each(function(){
					var $row = $(this);
					var show = true;
					if (trnxQuickFilter === 'new' && !$row.hasClass('new')) show = false;
					if (trnxQuickFilter === 'used' && !$row.hasClass('used')) show = false;
					if (itemFilter && $row.attr('data-item-id') !== itemFilter) show = false;
					if (playerFilter && $row.attr('data-player-id') !== playerFilter) show = false;
					if (q && $row.text().toLowerCase().indexOf(q) < 0) show = false;
					$row.toggle(show);
				});
			}
			$('#search-trnxs').on('keyup', applyTrnxFilters);
			$('#trnx-filter-item, #trnx-filter-player').on('change', applyTrnxFilters);
			</script>
		</div>
	</div>

	<!-- Table -->
	<div class="br-panel">
		<table class="br-table" id="table-trnxs">
			<thead>
				<tr>
					<th class="br-th-narrow"><?= __("ID", "bluerabbit"); ?></th>
					<th><?= __("Player", "bluerabbit"); ?></th>
					<th><?= __("Email", "bluerabbit"); ?></th>
					<th><?= __("Date", "bluerabbit"); ?></th>
					<th><?= __("Item", "bluerabbit"); ?></th>
					<th class="text-center br-th-narrow"><?= __("Use", "bluerabbit"); ?></th>
					<th class="text-center br-th-narrow"><?= __("Return", "bluerabbit"); ?></th>
					<?php if($isGM || $isAdmin){ ?>
					<th class="text-center br-th-narrow"><?= __("Delete", "bluerabbit"); ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$spentBLOO = 0;
				foreach ($transactions as $iT) {
					$spentBLOO += $iT->trnx_amount;
					$row_class = $iT->trnx_use ? 'used' : 'new';
				?>
				<tr class="<?= $row_class; ?>" data-item-id="<?= (int) $iT->item_id; ?>" data-player-id="<?= (int) $iT->player_id; ?>">
					<td>
						<span class="br-trnx-id">#<?= $iT->trnx_id; ?></span>
					</td>
					<td>
						<?php if($isAdmin || $isGM){ ?>
						<a href="<?= get_bloginfo('url') . "/backpack/?adventure_id=$adventure->adventure_id&player_id=$iT->player_id"; ?>">
							<?= esc_html($iT->player_display_name); ?>
						</a>
						<?php } else { ?>
						<?= esc_html($iT->player_display_name); ?>
						<?php } ?>
					</td>
					<td class="br-trnx-email"><?= esc_html($iT->player_email); ?></td>
					<td>
						<?php
						$trnx_date = date('M j, Y', strtotime($iT->trnx_date));
						$trnx_time = date('g:i A', strtotime($iT->trnx_date));
						?>
						<span class="br-trnx-date"><?= $trnx_date; ?></span>
						<span class="br-trnx-time"><?= $trnx_time; ?></span>
					</td>
					<td>
						<span class="br-badge <?= $type_badges[$iT->trnx_type] ?? 'br-badge-blue'; ?>">
							<?= esc_html($iT->item_name); ?>
						</span>
					</td>
					<td class="text-center">
						<?php if(!$iT->trnx_use && $iT->item_type == 'consumable'){ ?>
						<button class="br-btn br-btn-green br-trnx-btn-action" onClick="useItem(<?= "$iT->trnx_id, $iT->player_id, 1"; ?>);" title="<?= __('Mark as used', 'bluerabbit'); ?>">
							<span class="icon icon-check"></span>
						</button>
						<?php } elseif(!$iT->trnx_use) { ?>
						<span class="br-badge br-badge-green br-trnx-new-badge"><?= __("New", "bluerabbit"); ?></span>
						<?php } ?>
					</td>
					<td class="text-center">
						<?php if($iT->trnx_use){ ?>
						<button class="br-btn br-trnx-btn-action" onClick="useItem(<?= "$iT->trnx_id, $iT->player_id, 0"; ?>);" title="<?= __('Return item', 'bluerabbit'); ?>">
							<span class="icon icon-restore"></span>
						</button>
						<?php } ?>
					</td>
					<?php if($isGM || $isAdmin){ ?>
					<td class="text-center">
						<button class="br-btn br-btn-red br-trnx-btn-action" onClick="br_confirm_trd('delete',<?= $iT->trnx_id; ?>,'trnx');">
							<span class="icon icon-cancel"></span>
						</button>
					</td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<?php if($spentBLOO > 0){ ?>
		<div class="br-trnx-total">
			<span class="br-trnx-total-label"><?= __("Total Spent", "bluerabbit"); ?></span>
			<span class="br-trnx-total-value"><?= number_format($spentBLOO); ?></span>
			<span class="br-trnx-total-unit"><?= $bloo_label; ?></span>
		</div>
		<?php } ?>
	</div>

</div>

<style>
.br-trnx-controls {
	display: flex;
	align-items: center;
	gap: 12px;
	flex-wrap: wrap;
}
.br-trnx-search {
	position: relative;
}
.br-input {
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(28,194,235,0.15);
	border-radius: 6px;
	padding: 8px 14px;
	color: #ffffff;
	font-size: 13px;
	outline: none;
	transition: border-color 0.15s;
	width: 220px;
}
.br-input:focus {
	border-color: rgba(28,194,235,0.4);
	background: rgba(255,255,255,0.08);
}
.br-input::placeholder {
	color: rgba(255,255,255,0.25);
}
@media (max-width: 768px) {
	.br-trnx-controls { flex-direction: column; align-items: flex-start; }
	.br-input { width: 100%; }
}
</style>

<input type="hidden" id="use-item-nonce" value="<?= wp_create_nonce('br_use_item_nonce'); ?>">
<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>">
<?php if($isGM){ ?>
<input type="hidden" id="delete-nonce" value="<?= wp_create_nonce('delete_nonce'); ?>">
<?php } ?>
<input type="hidden" id="reload" value="true">
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_item_nonce'); ?>">
<?php } else { ?>
	<script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if ( ! ($isAdmin || $isGM || $isNPC) ) { ?>
<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$item_id = br_require_id('item_id', false) ?: 0;
$i = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=%d AND adventure_id=%d",
	$item_id, $adventure->adventure_id
));
?>

<?php if ( ! $i ) { ?>
<div class="br-page">
	<div class="br-panel text-center">
		<h2 class="br-text-24 w900"><?= __("This item doesn't exist","bluerabbit"); ?></h2>
	</div>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$alltrnx_count = (int) $wpdb->get_var($wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions WHERE object_id=%d AND trnx_type='consumable' AND trnx_status='publish' AND adventure_id=%d",
	$item_id, $adventure->adventure_id
));
$stock_left = $i->item_stock > 0 ? ($i->item_stock - $alltrnx_count) : null;

$players = $wpdb->get_results($wpdb->prepare(
	"SELECT a.player_id, a.player_level, b.player_display_name, b.player_picture, users.display_name, users.user_email
	FROM {$wpdb->prefix}br_player_adventure a
	LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
	LEFT JOIN {$wpdb->prefix}users users ON a.player_id = users.ID
	WHERE a.adventure_id=%d AND a.player_adventure_status='in'
	ORDER BY b.player_email LIMIT 1000",
	$adventure->adventure_id
));
?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<a class="br-btn" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=items"; ?>">
			<span class="icon icon-arrow-left"></span> <?= __("Manage Items","bluerabbit"); ?>
		</a>
		<div class="br-page-header-avatar" style="background-image:url(<?= esc_url($i->item_badge); ?>)"></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= __("Assign to a Player","bluerabbit"); ?></div>
			<h1 class="br-page-title"><?= esc_html($i->item_name); ?></h1>
		</div>
	</div>

	<!-- Item summary -->
	<div class="br-panel">
		<div class="br-flex br-flex-center br-gap-md br-flex-wrap">
			<div class="br-summary-stat">
				<span class="icon icon-bloo"></span>
				<div>
					<span class="br-stat-val"><?= (int) $i->item_cost; ?></span>
					<span class="br-stat-label"><?= $bloo_label; ?></span>
				</div>
			</div>
			<?php if($stock_left !== null){ ?>
			<div class="br-summary-stat">
				<div>
					<span class="br-stat-val"><?= max(0, $stock_left); ?></span>
					<span class="br-stat-label"><?= __("Stock left","bluerabbit"); ?></span>
				</div>
			</div>
			<?php } ?>
			<?php if($i->item_player_max > 0){ ?>
			<div class="br-summary-stat">
				<div>
					<span class="br-stat-val"><?= (int) $i->item_player_max; ?></span>
					<span class="br-stat-label"><?= __("Max per player","bluerabbit"); ?></span>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="br-mt-md">
			<span class="br-badge br-badge-blue"><span class="icon icon-help"></span> <?= __("Assigning still deducts the item's cost from the player's balance and still respects stock/category/max limits - it only skips the purchase window and level requirement, for players who need a GM's help completing the purchase themselves.","bluerabbit"); ?></span>
		</div>
	</div>

	<!-- Player picker -->
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Select a Player","bluerabbit"); ?></h3>
		<div class="br-psa-toolbar">
			<input type="text" class="br-input br-psa-search" id="search-assign-item-players" placeholder="<?= __('Search players...', 'bluerabbit'); ?>">
			<span class="br-psa-count"><span id="assign-item-player-count"><?= count($players); ?></span> <?= __("players", "bluerabbit"); ?></span>
		</div>
		<div class="br-player-grid" id="assign-item-player-list">
			<?php foreach($players as $p){
				$pName = $p->player_display_name ?: $p->display_name;
			?>
			<div class="br-player-card"
				 id="assign-item-player-<?= $p->player_id; ?>"
				 onClick="assignItemToPlayer(<?= "$i->item_id, $p->player_id"; ?>);"
				 data-search="<?= esc_attr(strtolower($pName . ' ' . $p->user_email)); ?>">
				<div class="br-player-avatar" style="background-image:url(<?= esc_url($p->player_picture); ?>)"></div>
				<div class="br-player-info">
					<span class="br-player-name"><?= esc_html($pName); ?></span>
					<span class="br-player-meta">Lv.<?= (int) $p->player_level; ?> &middot; <?= esc_html($p->user_email); ?></span>
				</div>
				<span class="br-action-link"><span class="icon icon-check"></span></span>
			</div>
			<?php } ?>
		</div>
	</div>

</div>

<input type="hidden" id="assign-item-nonce" value="<?= wp_create_nonce('br_assign_item_nonce'); ?>">

<script>
$('#search-assign-item-players').on('keyup', function() {
	var search = $(this).val().toLowerCase();
	$('#assign-item-player-list .br-player-card').each(function() {
		var match = !search || $(this).data('search').indexOf(search) >= 0;
		$(this).toggle(match);
	});
});

function assignItemToPlayer(item_id, player_id) {
	let nonce = $('#assign-item-nonce').val();
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({
		url: runAJAX.ajaxurl,
		data: ({
			action: 'assignItem',
			item_id: item_id,
			player_id: player_id,
			adventure_id: adventure_id,
			nonce: nonce
		}),
		method: "POST",
		success: function (data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

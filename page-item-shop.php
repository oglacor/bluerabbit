<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
	$myAchievements = BR_Achievement::instance()->getMyAchievements($adventure->adventure_id);
	$a_ids = implode(",", $myAchievements);
	$condition = count($myAchievements) > 0 ? "items.achievement_id IN ($a_ids) OR " : "";

	$adventure_items_from = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	$items = $wpdb->get_results( "SELECT
		items.*, cat.category_name,
		COUNT(DISTINCT trnxs.trnx_id) AS purchased, COUNT(DISTINCT player_trnxs.trnx_id) AS bought, tabis.tabi_name

		FROM {$wpdb->prefix}br_items items
		LEFT JOIN {$wpdb->prefix}br_item_categories cat
		ON items.item_category_id = cat.category_id

		LEFT JOIN {$wpdb->prefix}br_transactions trnxs
		ON trnxs.object_id = items.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable' OR trnxs.trnx_type='tabi-piece' OR trnxs.trnx_type='gift-card') AND trnxs.adventure_id=$adv_child_id

		LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
		ON player_trnxs.object_id = items.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable' OR player_trnxs.trnx_type='tabi-piece' OR player_trnxs.trnx_type='gift-card') AND player_trnxs.player_id=$current_user->ID AND player_trnxs.adventure_id=$adv_child_id

		LEFT JOIN  {$wpdb->prefix}br_tabis tabis
		ON items.tabi_id = tabis.tabi_id

		WHERE
		items.adventure_id=$adventure_items_from
		AND items.item_status='publish'
		AND items.item_visibility !='hidden'
		AND (items.item_type='consumable' OR items.item_type='key' OR items.item_type='tabi-piece' OR items.item_type='gift-card')
		AND ($condition items.achievement_id=0)
		AND items.item_id NOT IN (SELECT steps.step_item FROM {$wpdb->prefix}br_steps steps WHERE steps.step_item > 0 AND steps.adventure_id=$adventure_items_from AND steps.step_status='publish' AND steps.step_type = 'item-grab')

		GROUP by items.item_id ORDER BY cat.category_order ASC, items.item_level ASC, items.item_cost ASC
	");

	// Hide items whose Conditions (achievement/milestone/threshold requirements set via
	// the item or its category) aren't met yet - same "just don't show it" pattern
	// already used above for the single-achievement_id gate.
	$conditions_snapshot = BR_Conditions::instance()->buildProgressSnapshot($adv_parent_id, $adv_child_id, $current_player->player_id, $playerReset);
	$items = array_values(array_filter($items, function($i) use ($conditions_snapshot, $adv_child_id) {
		return BR_Item::instance()->evaluateItemAccess($adv_child_id, $i, $conditions_snapshot);
	}));
?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar"><span class="icon icon-basket"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
			<h1 class="br-page-title"><?= __("Item Shop","bluerabbit"); ?></h1>
		</div>
	</div>

	<!-- Nav -->
	<div class="br-tabs" id="item-shop-nav">
		<span class="br-tab-btn active"><span class="icon icon-basket"></span> <?= __("Shop","bluerabbit"); ?></span>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-backpack"></span> <?= __("Backpack","bluerabbit"); ?></a>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/tabis/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-sabotage"></span> <?= __("Tabis","bluerabbit"); ?></a>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-transactions"></span> <?= __("Transactions","bluerabbit"); ?></a>
	</div>

	<?php if(!$use_items){ ?>
		<div class="br-panel br-empty">
			<span class="icon icon-basket"></span>
			<h3><?= __("The item shop is now closed!","bluerabbit"); ?></h3>
		</div>
	<?php }elseif($items){ ?>
		<div class="br-item-grid">
			<?php foreach($items as $it){
				$can_buy = true;
				$buy_label = __("Buy","bluerabbit");

				if($buy_items=='players' && $current_player->player_adventure_role!='player'){
					$can_buy = $isAdmin;
					$buy_label = $isAdmin ? __("Blocked to GMs","bluerabbit") : __("You can't buy items","bluerabbit");
				}

				$unlimited_stock = ($it->item_type=='key') || ($it->item_stock >= 99999) || ($it->item_type=='tabi-piece' && $it->item_stock <= 0);
				$stock_left = $unlimited_stock ? null : max(0, $it->item_stock - $it->purchased);
				$sold_out = (!$unlimited_stock && $stock_left <= 0);
				$maxed_out = ($it->item_player_max > 0 && $it->bought >= $it->item_player_max);
				$level_locked = ($current_player->player_level < $it->item_level);

				if($level_locked){ $can_buy=false; $buy_label = __("Requires Level","bluerabbit")." ".(int)$it->item_level; }
				if($maxed_out){ $can_buy=false; $buy_label = __("Max owned","bluerabbit"); }
				if($sold_out){ $can_buy=false; $buy_label = __("Sold Out","bluerabbit"); }
			?>
			<div class="br-item-card <?= !$can_buy ? 'br-item-card-locked' : ''; ?>">
				<div class="br-item-card-image" style="background-image:url(<?= esc_url($it->item_badge); ?>)"></div>
				<div class="br-item-card-body">
					<h3 class="br-item-card-name"><?= esc_html($it->item_name); ?></h3>
					<?php if($it->item_type=='tabi-piece' && $it->tabi_name){ ?>
						<span class="br-badge br-badge-purple"><?= sprintf(__("Part of %s","bluerabbit"), esc_html($it->tabi_name)); ?></span>
					<?php }elseif($it->category_name){ ?>
						<span class="br-badge br-badge-blue"><?= esc_html($it->category_name); ?></span>
					<?php } ?>
					<div class="br-item-card-description"><?= apply_filters('the_content', $it->item_description); ?></div>
					<div class="br-item-card-meta">
						<span title="<?= __("Level","bluerabbit"); ?>"><span class="icon icon-level"></span> <?= (int) $it->item_level; ?></span>
						<span title="<?= $bloo_label; ?>"><span class="icon icon-bloo"></span> <?= number_format($it->item_cost); ?></span>
						<?php if($stock_left !== null){ ?>
							<span title="<?= __("Stock","bluerabbit"); ?>"><span class="icon icon-basket"></span> <?= $stock_left; ?>/<?= (int) $it->item_stock; ?></span>
						<?php }else{ ?>
							<span title="<?= __("Stock","bluerabbit"); ?>"><span class="icon icon-basket"></span> <span class="icon icon-infinite"></span></span>
						<?php } ?>
					</div>
				</div>
				<div class="br-item-card-actions">
					<?php if($can_buy){ ?>
						<button class="br-btn br-btn-green" onClick="showOverlay('#confirm-buy-<?= $it->item_id; ?>');"><?= $buy_label; ?></button>
						<div class="confirm-action overlay-layer" id="confirm-buy-<?= $it->item_id; ?>">
							<p><?= sprintf(__("Buy %1\$s for %2\$s %3\$s?","bluerabbit"), '<strong>'.esc_html($it->item_name).'</strong>', number_format($it->item_cost), $bloo_label); ?></p>
							<button class="br-btn br-btn-green" onClick="buyItem(<?= $it->item_id; ?>);"><?= __("Confirm","bluerabbit"); ?></button>
							<button class="br-btn ghost close-confirm" onClick="hideAllOverlay();"><?= __("Cancel","bluerabbit"); ?></button>
						</div>
					<?php }else{ ?>
						<button class="br-btn" disabled><?= $buy_label; ?></button>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	<?php }else{ ?>
		<div class="br-panel br-empty">
			<span class="icon icon-basket"></span>
			<h3><?= __("No items currently available","bluerabbit"); ?></h3>
			<p><?= __("More items are available as you earn achievements. Keep moving forward!","bluerabbit"); ?></p>
		</div>
	<?php } ?>

	<?php if($isGM || $isAdmin){ ?>
		<div class="br-panel text-center">
			<a class="br-btn br-btn-green" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure_id"; ?>">
				<span class="icon icon-add"></span> <?= __("Add new item","bluerabbit"); ?>
			</a>
		</div>
		<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>"/>
		<input type="hidden" id="delete-nonce" value="<?= wp_create_nonce('delete_nonce'); ?>"/>
		<input type="hidden" id="reload" value="true"/>
	<?php } ?>
	<input type="hidden" id="purchase-nonce" value="<?= wp_create_nonce('br_item_nonce'); ?>"/>

</div>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

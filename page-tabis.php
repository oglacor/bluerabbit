<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
		<?php 
	
		$player_id_get = ($isAdmin || $isGM) ? br_require_id('player_id', false) : null;
		$the_player_id_for_backpack = $player_id_get ?: $current_user->ID;
	
        $tabis = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_tabis WHERE adventure_id=$adv_parent_id AND tabi_as_category=0 AND tabi_status='publish' ORDER BY tabi_level ASC, tabi_id ASC");

    	$myTabiItems = $wpdb->get_results( "SELECT items.*, tabis.tabi_name,
		trnxs.object_id, trnxs.trnx_id, trnxs.player_id, trnxs.trnx_type, trnxs.trnx_date
		FROM  {$wpdb->prefix}br_items items 
		LEFT JOIN {$wpdb->prefix}br_transactions trnxs
		ON items.item_id = trnxs.object_id AND trnxs.trnx_type='tabi-piece' AND trnxs.trnx_status='publish' AND trnxs.adventure_id=$adv_child_id 

		JOIN {$wpdb->prefix}br_tabis tabis
		ON items.tabi_id = tabis.tabi_id


		WHERE items.adventure_id=$adv_parent_id AND items.item_status='publish' AND tabis.tabi_as_category=0
		ORDER BY items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
		?>
		<div class="br-page">

			<!-- Header -->
			<div class="br-panel br-page-header">
				<div class="br-page-header-avatar"><span class="icon icon-sabotage"></span></div>
				<div class="br-flex-1">
					<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
					<h1 class="br-page-title"><?= __("Tabis","bluerabbit"); ?></h1>
				</div>
			</div>

			<!-- Nav -->
			<div class="br-tabs" id="item-shop-nav">
				<a class="br-tab-btn" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-basket"></span> <?= __("Shop","bluerabbit"); ?></a>
				<a class="br-tab-btn" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-backpack"></span> <?= __("Backpack","bluerabbit"); ?></a>
				<span class="br-tab-btn active"><span class="icon icon-sabotage"></span> <?= __("Tabis","bluerabbit"); ?></span>
				<a class="br-tab-btn" href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-transactions"></span> <?= __("Transactions","bluerabbit"); ?></a>
			</div>

			<?php if($tabis){ ?>
				<div class="br-tabi-collection-grid" id="tabis">
					<?php foreach($tabis as $a){ ?>
						<div class="br-panel">
							<h3 class="br-panel-title"><?= esc_html($a->tabi_name); ?></h3>
							<div class="br-tabi-board" id="tabi-pieces-<?=$a->tabi_id; ?>" style="background-image: url('<?= esc_url($a->tabi_background); ?>');">
								<?php foreach($myTabiItems as $i){ ?>
									<?php if($i->tabi_id == $a->tabi_id){ ?>
										<div class="br-tabi-piece <?= $i->player_id == $current_user->ID ? 'br-tabi-piece-owned' : ''; ?>" id="tabi-piece-<?=$i->item_id; ?>" style="z-index: <?= (int) $i->item_z; ?>; top:<?= $i->item_y; ?>%; left:<?= $i->item_x; ?>%; width:<?= $i->item_scale; ?>%; transform:rotate(<?= $i->item_rotation; ?>deg);">
											<img src="<?= esc_url($i->item_badge); ?>" alt="<?= esc_attr($i->item_name); ?>" title="<?= esc_attr($i->item_name); ?>">
										</div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php }else{ ?>
				<div class="br-panel br-empty">
					<span class="icon icon-sabotage"></span>
					<h3><?= __("No tabis available",'bluerabbit');?></h3>
				</div>
			<?php } ?>

		</div>
		<input type="hidden" id="item_id_purchase" value=""/>
		<input type="hidden" id="use-item-nonce" value="<?php echo wp_create_nonce('br_use_item_nonce'); ?>"/>
	<?php }else{ ?>
		<h1><?php _e("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
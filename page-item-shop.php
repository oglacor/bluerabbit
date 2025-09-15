<?php include (get_stylesheet_directory() . '/header.php'); ?>
<svg width="0" height="0" style="position: absolute;">
  <defs>
    <clipPath id="hud-screen-clip" clipPathUnits="objectBoundingBox">
      <polygon points="
        0,0.0729 0.1026,0.0729 0.2322,0 0.8999,0 1,0.0563 1,0.4133
        0.9579,0.437 0.9579,0.6991 1,0.7228 1,0.9639 0.9359,1
        0.6533,1 0.6082,0.9747 0.0476,0.9747 0,0.949 0,0.6782
        0.0261,0.6636 0.0261,0.6034 0,0.589 0,0.4993 0.0381,0.4792
        0.0381,0.4101 0,0.3902 0,0.0729
      " />
    </clipPath>
  </defs>
</svg>
<script>
	let current_item_preview_id = 0;
</script>

<?php if($adventure){ ?>
		<?php 
		
		$player_achievements = array();
		$myAchievements = getMyAchievements($adventure->adventure_id);
		$a_ids=(implode(",",$myAchievements)); 
		$condition = count($myAchievements) > 0 ? "items.achievement_id IN ($a_ids) OR " : ""; 
		
	
		$adventure_items_from = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id; 
		$items = $wpdb->get_results( "SELECT
			items.*, COUNT(DISTINCT trnxs.trnx_id) AS purchased, COUNT(DISTINCT player_trnxs.trnx_modified) AS bought, player_trnxs.player_id, tabis.tabi_name
			
			FROM {$wpdb->prefix}br_items items
			LEFT JOIN {$wpdb->prefix}br_transactions trnxs
			ON trnxs.object_id = items.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable' OR trnxs.trnx_type='tabi-piece') AND trnxs.adventure_id=$adv_child_id 

			LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
			ON player_trnxs.object_id = items.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable' OR player_trnxs.trnx_type='tabi-piece') AND player_trnxs.player_id=$current_user->ID AND trnxs.adventure_id=$adv_child_id 

			LEFT JOIN  {$wpdb->prefix}br_achievements achievements
			ON items.achievement_id = achievements.achievement_id 

			LEFT JOIN  {$wpdb->prefix}br_tabis tabis
			ON items.tabi_id = tabis.tabi_id 

			WHERE 
			items.adventure_id=$adventure_items_from 
			AND items.item_status='publish' 
			AND (items.item_type='consumable' OR items.item_type='key' OR items.item_type='tabi-piece') 
			AND ($condition items.achievement_id=0)
			AND items.item_id NOT IN (SELECT steps.step_item FROM {$wpdb->prefix}br_steps steps WHERE steps.step_item > 0 AND steps.adventure_id=$adventure_items_from AND steps.step_status='publish' AND steps.step_type = 'item-grab') 

			GROUP by items.item_id ORDER BY items.item_category ASC, items.item_level ASC, items.item_cost ASC 
		");
		$item_categories = array();
		foreach($items as $i){
			$item_categories[]=$i->item_category;
		}
		$item_categories = array_unique($item_categories);
	
		if($isGM){
			$rewards = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_items WHERE adventure_id=$adventure->adventure_id AND item_status='publish' AND item_type='reward' ORDER BY item_id");
		}
		?>
		<nav class="tab-nav">
			<ul>
				<li class="active">
					<span class="nav-item-label"><?= __("Shop","bluerabbit"); ?></span>
				</li>
				<li>
					<a href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Backpack","bluerabbit"); ?>
					</a>
				</li>
				<li>
					<a href="<?= get_bloginfo('url')."/tabis/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Tabis","bluerabbit"); ?>
					</a>
				</li>
				<li>
					<a href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Transactions","bluerabbit"); ?>
					</a>
				</li>
			</ul>
		</nav>
		<div class="item-shop" id="item-shop">
			<div class="items">
				<?php if($items){ ?>
					<?php
					foreach($items as $key=>$i){ 
						$can_buy = true;
						$available = true; 
						$buy_button_label = __("Buy","bluerabbit");
						if(($buy_items=='players' && $current_player->player_adventure_role !='player')){
							$can_buy = false;
							$available = false;
							$buy_button_label = __("You can't buy items","bluerabbit");
							if($isAdmin){
								$can_buy = true;
								$available = true;
								$buy_button_label = __("Blocked to GMs","bluerabbit");
							}
						}
						if(!$use_item_shop){
							$can_buy = false;
							$available = false;
							$buy_button_label = __("The item Shop is now closed!","bluerabbit");
						}
						$sold_out = ($i->purchased >= $i->item_stock && $i->item_stock > 0) ? true : false;
						$maxed_out = ($i->bought >= $i->item_player_max  && $i->item_player_max > 0) ? true : false;
						$stock_left = $i->item_stock - $i->purchased;
						if($maxed_out){
							$available = false;
							$buy_button_label = __("Max owned","bluerabbit"); 
						}
						if($sold_out){
							$available = false;
							$buy_button_label = __("Sold Out","bluerabbit"); 
						}
						if($sold_out){
							$available = false;
							$buy_button_label = __("Sold Out","bluerabbit"); 
						}
						if($current_player->player_level < $i->item_level){
							$available = false;
							$buy_button_label = __("Not enough Level","bluerabbit"); 
						}
						?>
						<div class="item" onClick="previewItem(<?=$key+1; ?>);" id="item-<?=$key+1; ?>">
							<input type="hidden" class="item-badge-url" value="<?= $i->item_badge; ?>">
							<input type="hidden" class="item-type-val" value="<?= $i->item_type; ?>">
							<input type="hidden" class="player_has_it" value="<?= $player_has_it; ?>">
							<div class="item-content-container">
								<div class="item-bg">
									<svg id="item-container-<?= $i->item_id;?>" class="item-container-bg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180">
										<polygon  class="blue outline"  points="165 175 15 175 5 165 5 15 15 5 165 5 175 15 175 165 165 175"/>
										<line class="blue line"  x1="27" y1="163" x2="17" y2="153"/>
										<line  class="blue line"  x1="30" y1="160" x2="20" y2="150"/>
										<polyline class="blue line"  points="114.59 20 149.94 20 154 24.06"/>
										<polygon class="blue item-content" points="162 170 18 170 10 162 10 18 18 10 162 10 170 18 170 162 162 170"/>
										<polygon class="blue bg" points="150 160 30 160 20 150 20 30 30 20 150 20 160 30 160 150 150 160"/>
										<polyline class="yellow line" points="165 156 155 166 24 166 14 156 14 36.78"/>
										<polyline class="yellow line" points="43.12 16 152 16 162 26"/>
									</svg>
								</div>
								<div class="item-content">
									<div class="item-image-container">
										<img class="item-image" src="<?= $i->item_badge; ?>" alt="<?= $i->item_name; ?>">
									</div>
									<div class="item-level">
										<svg class="item-level-bg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26.12 26.12">
											<polygon points="16.41 .56 3.91 3.91 .56 16.41 9.71 25.56 22.21 22.21 25.56 9.71 16.41 .56"/>
										</svg>
										<span class="item-level-number"><?= $i->item_level; ?></span>
									</div>
									<h2 class="item-name"><?= $i->item_name; ?> </h2>
									<h3 class="item-cost">
										<?= toMoney($i->item_cost,"$");?>
									</h3>
									<h4 class="item-stock">
										<?php if($i->item_type == 'key' || ($i->item_type == 'tabi-piece' && $i->item_stock <= 0) || ($i->item_stock >= 99999)){ ?>
											<?= __("Stock","bluerabbit").": <span class='icon icon-infinite'></span>"; ?>
										<?php }else{ ?>
											<?= __("Stock","bluerabbit").": <span class='stock-left'>".$stock_left."</span> / ".$i->item_stock; ?>
										<?php } ?>
									</h4>
								</div>
								<div class="item-data" id="item-data-<?=$key+1; ?>">
									<input type="hidden" class="item-name" value="<?= $i->item_name; ?>">
									<input type="hidden" class="item-image" value="<?= $i->item_badge; ?>">
									<input type="hidden" class="item-type" value="<?= $i->item_type; ?>">
									<?php if($available==true){ ?>
										<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
									<?php } ?>
									<?php if($i->item_type == 'tabi-piece'){ ?>
										<input type="hidden" class="item-type-label" value="<?= __("Part of","bluerabbit")." ".$i->tabi_name; ?>">
									<?php }elseif($i->item_type == 'consumable'){ ?>
										<input type="hidden" class="item-type-label" value="<?= __("Consumable","bluerabbit"); ?>">
									<?php }elseif($i->item_type == 'key'){ ?>
										<input type="hidden" class="item-type-label" value="<?= __("Key Item","bluerabbit"); ?>">
									<?php } ?>	
									<input type="hidden" class="item-category" value="<?= $i->item_category; ?>">
									<input type="hidden" class="item-level" value="<?= $i->item_level; ?>">
									<input type="hidden" class="item-stock" value="<?= $i->item_stock; ?>">
									<input type="hidden" class="item-left" value="<?= $stock_left; ?>">
									<div class="item-description">
										<?= apply_filters('the_content',$i->item_description); ?>
									</div>
								</div>
							</div>
							<div class="item-actions">
								<?php if($available==true){ ?>
									<button class="form-ui buy-item" onClick="buyItem(<?php echo $i->item_id; ?>);">
										<?= $buy_button_label; ?> <?= toMoney($i->item_cost,"$");?>
									</button>
								<?php }else{ ?>
									<button class="form-ui buy-item disabled" disabled>
										<?= $buy_button_label; ?>
									</button>

								<?php } ?>
							</div>
						</div>
						<?php
					}
					?>
				<?php }else{ ?>
					<h1 class="text-center"><?= __("No items currently available",'bluerabbit');?></h1>
					<h3 class="text-center"><?= __("More items are available as you earn achievements. Keep moving forward!",'bluerabbit');?></h3>
				<?php }?>

			</div>
			<div class="hud-display item-shop-video active item-preview">
				<div class="hud-screen-container">
					<div class="hud-screen-content">
						<div class="item-preview-screen" id="item-preview-screen">
							<div class="item-preview-type"></div>
							<div class="item-preview-name">

							</div>
							<div class="item-preview-image-container">
								<img src="" class="item-preview-image" alt="">
							</div>
							<div class="item-preview-description">
							</div>
							<div class="item-preview-actions">
								<button class="form-ui buy-item" id="item-preview-buy-button">
									<?= __("Buy","bluerabbit"); ?>
								</button>
							</div>
						</div>
					</div>
					<svg class="hud-screen-graphics" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1920" preserveAspectRatio="xMidYMid slice">
						<path class="screen-outline" d="M959.51,30l90.49,90.49v660.66l-36.69,36.69-8.79,8.79v528.06l8.79,8.79,36.69,36.69v438.17l-51.66,51.66h-280.39l-39.89-39.89-8.79-8.79H60.06l-30.06-30.06v-496.63l19.37-19.37,8.79-8.79v-140.33l-8.79-8.79-19.37-19.37v-146.94l29.39-29.39,8.79-8.79v-157.85l-8.79-8.79-29.39-29.39V169.99h93.22l8.79-8.79L263.2,30h696.31M971.94,0H250.78L110.79,139.99H0v609.29l38.18,38.18v133L0,958.64v171.79l28.16,28.16v115.48l-28.16,28.15v521.48l47.63,47.63h609.22l48.67,48.67h305.24l69.23-69.23v-463.02l-45.48-45.48v-503.21l45.48-45.48V108.06L971.94,0h0Z"/>
						<polygon class="screen-cover" points="0 139.99 110.79 139.99 250.78 0 971.94 0 1080 108.06 1080 793.58 1034.52 839.05 1034.52 1342.27 1080 1387.74 1080 1850.77 1010.77 1920 705.53 1920 656.85 1871.33 47.63 1871.33 0 1823.7 0 1302.21 28.16 1274.06 28.16 1158.58 0 1130.43 0 958.64 38.18 920.46 38.18 787.46 0 749.28 0 139.99"/>
					</svg>
					<div class="hud-screen-video-filter" onClick="playBGVideo('.hud-screen-video.active');"></div>
					<video id="hud-video-status-idle" autoplay playsinline muted preload poster="<?= get_template_directory_uri(); ?>/images/shopkeeper.png" class="hud-screen-video active">
						<source src="<?= get_template_directory_uri(); ?>/video/shopkeeper-idle.mp4" type="video/mp4">
						Your browser does not support the video tag.
					</video>
					<video id="hud-video-status-sale" muted preload poster="<?= get_template_directory_uri(); ?>/images/shopkeeper.png" class="hud-screen-video" >
						<source src="<?= get_template_directory_uri(); ?>/video/shopkeeper-sale.mp4" type="video/mp4">
						Your browser does not support the video tag.
					</video>
					<video id="hud-video-status-item-display" muted preload poster="<?= get_template_directory_uri(); ?>/images/shopkeeper.png" class="hud-screen-video" >
						<source src="<?= get_template_directory_uri(); ?>/video/shopkeeper-item-display.mp4" type="video/mp4">
						Your browser does not support the video tag.
					</video>
				</div>
			</div>
		</div>



		<?php if($isGM || $isAdmin){ ?>
			<div class="highlight text-center foreground">
				<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adventure_id"; ?>" class="form-ui pink-bg-400 font _24">
					<span class="icon icon-add"></span>
					<?= __("Add new item","bluerabbit");?>
				</a>
			</div>
			<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
			<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>"/>
			<input type="hidden" id="reload" value="true"/>
		<?php } ?>
		<input type="hidden" id="purchase-nonce" value="<?php echo wp_create_nonce('br_item_nonce'); ?>"/>
	<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
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
		<nav class="tab-nav">
			<ul>
				<li>
					<a href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Shop","bluerabbit"); ?>
					</a>
				</li>
				<li class="active">
					<span class="nav-item-label"><?= __("Backpack","bluerabbit"); ?></span>
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
			<div class="items backpack">
				<?php if($my_items){ ?>
					<?php $current_type = ''; ?>
					<?php foreach($my_items['all'] as $key=>$i){ ?>

						<?php if($current_type != "$i->item_type-$i->tabi_id"){ ?>
							<div class="item-group <?="$i->item_type-$i->tabi_id"; ?>">
								<?php $current_type = "$i->item_type-$i->tabi_id"; ?>
								<?php if($i->item_type == 'consumable'){ ?>
									<h2><?= __("Consumables","bluerabbit"); ?></h2>
								<?php }elseif($i->item_type == 'key'){ ?>
									<h2><?= __("Key Items","bluerabbit"); ?></h2>
								<?php }elseif($i->item_type == 'tabi-piece'){ ?>
									<h2><?= $i->tabi_name; ?></h2>
								<?php }elseif($i->item_type == 'reward'){ ?>
									<h2><?= __("Rewards","bluerabbit"); ?></h2>
								<?php }else{ ?>
									<h2><?= __("Items","bluerabbit"); ?></h2>
								<?php } ?>
							</div>
						<?php } ?>
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
									<h4 class="item-stock">
											<?= __("Owned","bluerabbit").": <span class='stock-left'>".$i->total_consumables."</span>"; ?>
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
						</div>
					<?php } ?>
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
						</div>
					</div>
					<svg class="hud-screen-graphics" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1080 1920" preserveAspectRatio="xMidYMid slice">
						<path class="screen-outline" d="M959.51,30l90.49,90.49v660.66l-36.69,36.69-8.79,8.79v528.06l8.79,8.79,36.69,36.69v438.17l-51.66,51.66h-280.39l-39.89-39.89-8.79-8.79H60.06l-30.06-30.06v-496.63l19.37-19.37,8.79-8.79v-140.33l-8.79-8.79-19.37-19.37v-146.94l29.39-29.39,8.79-8.79v-157.85l-8.79-8.79-29.39-29.39V169.99h93.22l8.79-8.79L263.2,30h696.31M971.94,0H250.78L110.79,139.99H0v609.29l38.18,38.18v133L0,958.64v171.79l28.16,28.16v115.48l-28.16,28.15v521.48l47.63,47.63h609.22l48.67,48.67h305.24l69.23-69.23v-463.02l-45.48-45.48v-503.21l45.48-45.48V108.06L971.94,0h0Z"/>
						<polygon class="screen-cover" points="0 139.99 110.79 139.99 250.78 0 971.94 0 1080 108.06 1080 793.58 1034.52 839.05 1034.52 1342.27 1080 1387.74 1080 1850.77 1010.77 1920 705.53 1920 656.85 1871.33 47.63 1871.33 0 1823.7 0 1302.21 28.16 1274.06 28.16 1158.58 0 1130.43 0 958.64 38.18 920.46 38.18 787.46 0 749.28 0 139.99"/>
					</svg>
					<div class="hud-screen-video-filter" onClick="playBGVideo('.hud-screen-video.active');"></div>
					<video id="hud-video-status-idle" autoplay playsinline muted preload poster="<?= get_template_directory_uri(); ?>/images/backpack.png" class="hud-screen-video active">
						<source src="<?= get_template_directory_uri(); ?>/video/backpack-idle.mp4" type="video/mp4">
						Your browser does not support the video tag.
					</video>
				</div>
			</div>
		</div>
		<input type="hidden" id="item_id_purchase" value=""/>
		<input type="hidden" id="use-item-nonce" value="<?php echo wp_create_nonce('br_use_item_nonce'); ?>"/>
	<?php }else{ ?>
		<h1><?php _e("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
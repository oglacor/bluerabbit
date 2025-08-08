<?php include (get_stylesheet_directory() . '/header.php'); ?>
<svg width="0" height="0" style="position: absolute;">
  <defs>
    <clipPath id="tabi-screen-clip" clipPathUnits="objectBoundingBox">
        <polygon points="
            1.0,0.92709 0.9423,0.92709 0.86939,1.0 0.05628,1.0 
            0.0,0.94372 0.0,0.58668 0.02369,0.56299 0.02369,0.3009 
            0.0,0.27722 0.0,0.03606 0.03606,0.0 0.41379,0.0 
            0.43914,0.02535 0.97519,0.02535 1.0,0.05016 1.0,0.32177 
            0.98533,0.33643 0.98533,0.39657 1.0,0.41123 
            1.0,0.50071 0.98011,0.52059 0.98011,0.58986 
            1.0,0.60975 1.0,0.92709
        "/>
    </clipPath>
  </defs>
</svg>


<?php if($adventure){ ?>
		<?php 
	
		if(($isAdmin || $isGM) && isset($_GET['player_id']) ){
			$the_player_id_for_backpack = $_GET['player_id'];
		}else{
			$the_player_id_for_backpack = $current_user->ID;
		}
	
        $tabis = getTabis($adv_parent_id);
		$myTabiItems = $wpdb->get_results( "SELECT items.*, tabis.tabi_name,
		trnxs.object_id, trnxs.trnx_id, trnxs.trnx_type, trnxs.trnx_date, COUNT(items.item_id) AS total_consumables
		FROM  {$wpdb->prefix}br_items items 
		JOIN {$wpdb->prefix}br_transactions trnxs
		ON items.item_id = trnxs.object_id

		JOIN {$wpdb->prefix}br_tabis tabis
		ON items.tabi_id = tabis.tabi_id


		WHERE items.adventure_id=$adv_parent_id AND items.item_status='publish' AND trnxs.player_id=$the_player_id_for_backpack AND trnxs.adventure_id=$adv_child_id AND trnxs.trnx_type='tabi-piece' AND trnxs.trnx_status='publish'
		GROUP BY trnxs.object_id, trnxs.trnx_type ORDER BY items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
		?>
		<nav class="tab-nav">
			<ul>
				<li>
					<a href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Shop","bluerabbit"); ?>
					</a>
				</li>
				<li>
					<a href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>">
                    <?= __("Backpack","bluerabbit"); ?>
                    </a>
				</li>
				<li class="active"> 
                    <span class="nav-item-label"><?= __("Tabis","bluerabbit"); ?></span>
				</li>
				<li>
					<a href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adventure->adventure_id"; ?>">
						<?= __("Transactions","bluerabbit"); ?>
					</a>
				</li>
			</ul>
		</nav>
		<div class="tabi-collection" id="tabis">
			<div class="tabis">
                <?php if($tabis){ ?>
                    <?php foreach($tabis as $tabiKey=>$a){ ?>
                        <div class="tabi">
                            <div class="hud-display active" id="hud-display-<?=$a->tabi_id; ?>">
                                <div class="hud-screen-container active">
                                    <div class="hud-screen-content">
                                        <div class="tabi-pieces" style="background-image: url('<?= $a->tabi_background; ?>');" id="tabi-pieces-<?=$a->tabi_id; ?>">
                                            <?php foreach($myTabiItems as $i){ ?>
                                                <?php if($i->tabi_id == $a->tabi_id){ ?>
                                                    <div class="tabi-piece" id="tabi-piece-<?=$i->item_id; ?>" style="z-index: <?= $i->item_z; ?>;top:<?= $i->item_y; ?>%; left:<?= $i->item_x; ?>%; transform:rotate(<?= $i->item_rotation; ?>deg);width:<?=$i->item_scale;?>%">
                                                        <div class="tabi-piece-image">
                                                            <img src="<?= $i->item_badge; ?>" alt="<?= $i->item_name; ?>" title="<?= $i->item_name; ?>">
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <svg id="tabi-<?=$a->tabi_id; ?>" class="hud-screen-graphics" " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 1920">
                                        <polygon class="screen-cover" points="1920 1780.01 1809.21 1780.01 1669.22 1920 108.06 1920 0 1811.94 0 1126.42 45.48 1080.95 45.48 577.73 0 532.26 0 69.23 69.23 0 794.47 0 843.15 48.67 1872.37 48.67 1920 96.3 1920 617.79 1891.84 645.94 1891.84 761.42 1920 789.57 1920 961.36 1881.82 999.54 1881.82 1132.54 1920 1170.72 1920 1780.01"/>
                                        <path class="screen-outline" d="M120.49,1890l-90.49-90.49v-660.66l45.48-45.48v-528.06l-45.48-45.48V81.66l51.66-51.66h700.39l48.67,48.67h1029.22l30.06,30.06v496.63l-28.16,28.16v140.33l28.16,28.16v146.94l-38.18,38.18v157.85l38.18,38.18v566.87h-93.22l-139.99,139.99H120.49M108.06,1920h1561.16l139.99-139.99h110.79v-609.29l-38.18-38.18v-133l38.18-38.18v-171.79l-28.16-28.16v-115.48l28.16-28.15V96.3l-47.63-47.63H843.15L794.47,0H69.23L0,69.23v463.02l45.48,45.48v503.21L0,1126.42v685.51l108.06,108.06h0Z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                <?php }else{ ?>
                    <div class="sys-message">
                        <h1 class="text-center white-color"><?= __("No tabis available",'bluerabbit');?></h1>
                    </div>
                <?php } ?> 
            </div>
		</div>
		<input type="hidden" id="item_id_purchase" value=""/>
		<input type="hidden" id="use-item-nonce" value="<?php echo wp_create_nonce('br_use_item_nonce'); ?>"/>
	<?php }else{ ?>
		<h1><?php _e("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
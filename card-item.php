<?php 
	if($item->item_type == 'key'){
		$icon = 'key'; $color = 'indigo';
	}elseif($item->item_type == 'reward'){
		$icon = 'winstate'; $color = 'teal';
	}else{
		$icon = 'basket'; $color = 'pink';
	}

	$purchased = $item->purchased;
	$bought = $item->bought;
	$stock_left = $item->item_stock - $purchased;
	$max_per_player = $item->item_player_max;

	$can_buy = false;

	if($bought < $max_per_player && $stock_left > 0){
		$can_buy = true;
	}else if($max_per_player <= 0 && $stock_left > 0){
		$can_buy = true;
	}else if(($item->item_deadline != '' && $item->item_deadline != '0000-00-00 00:00:00') && strtotime($today) >= strtotime($item->item_start_date)){
		$can_buy = true;
	}else if(($item->item_deadline != '' && $item->item_deadline != '0000-00-00 00:00:00') && strtotime($today) <= strtotime($item->item_deadline)){
		$can_buy = true;
	}

?>
<div class="background blue-grey-bg-700 opacity-60 fixed" onClick="unloadCard();"></div>
<div class="card card-scene " id="<?= "item-$item->item_id"; ?>">
	<div class="card-content">
		<div class="card-face frontface">
			<?php if($isGM || $isNPC || $isAdmin){ ?>
				<a class="layer foreground icon-button font _14 sq-20 absolute top-10 left-10 green-bg-400" href="<?php echo get_bloginfo("url")."/new-item/?item_id=$item->item_id&adventure_id=$adv_parent_id"; ?>">
					<span class="icon icon-edit"></span>
				</a>
			<?php } ?>
			<button class="layer foreground absolute icon-button font _14 sq-20  top-10 right-10 red-bg-400" onClick="unloadCard();"><span class="icon icon-cancel"></span></button>
			<div class="layer background absolute sq-full top left blend-luminosity grey-bg-900 opacity-80" style="background-image: url(<?= $item->item_badge; ?>);"></div>
			<div class="layer background absolute sq-full top left grey-bg-900 opacity-80"></div>
			<div class="layer background absolute sq-full top left <?=$color;?>-gradient-900 opacity-80"></div>
			<div class="layer base absolute sq-full top left">
				<div class="card-type text-center deep-purple-bg-400 white-color" >
					<span class="icon icon-level amber-500"></span>
					<span class="number"><?=$item->item_level; ?></span>
				</div>
				<div class="layer base perfect-center absolute w-full">
					<div class="badge-container">
						<img src="<?= $item->item_badge; ?>" class="badge" >
						<img class="rotate-L-20 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-R-30 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-L-40 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-R-60 mix-blend-overlay halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-L-90 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<div class="text-center padding-10 white-color">
						<h1 class="font _20 w300 kerning-1"><?= $item->item_name; ?></h1>
					</div>
					
					<?php if(($item->item_deadline != '' && $item->item_deadline != '0000-00-00 00:00:00') && strtotime($today) < strtotime($item->item_start_date)){ ?>
					
						<?php $id = $item->item_start_date; ?>
						<?php include (get_stylesheet_directory() . '/component-date-countdown.php'); ?>
					
					<?php }elseif(($item->item_deadline != '' && $item->item_deadline != '0000-00-00 00:00:00') && strtotime($today) > strtotime($item->item_deadline)){ ?>
						<div class="card-message">
							<h3 class="font _14 w900 kerning-2 red-A400 uppercase"><?= __("Deadline Missed","bluerabbit"); ?></h3>
							<h2><?= __("This item can't be purchased anymore.","bluerabbit"); ?></h2>
						</div>
					<?php }elseif($item->item_description){ ?>
						<div class="card-message white-color">
							<?= apply_filters('the_content',$item->item_description); ?>
						</div>
					<?php } ?>
				</div>
				<?php if($item->item_type != 'reward' && $can_buy){ ?>
					<div class="text-left w-full padding-10 absolute bottom left green-bg-400">
						<span class="inline-block white-color border rounded-max green-bg-400 font _24 w900" id="item-cost-<?=$item->item_id; ?>">
							<span class="icon icon-bloo"></span>
							<span class="number">0</span>
							<input type="hidden" class="end-value" value="<?= $item->item_cost; ?>">
						</span>
						<script>animateNumber('#item-cost-<?=$item->item_id; ?>',1000,1500,0,'money');</script>
						<button class="form-ui amber-bg-400 blue-grey-900 font w300 padding-5 _14 layer base absolute v-center right-10 uppercase kerning-1 normal" onClick="activate('#confirm-buy-item-<?php echo $item->item_id; ?>');">
							<?= __("Buy now!","bluerabbit"); ?>
						</button>
					</div>
				<?php }else{ ?>
					<div class="text-left w-full padding-10 absolute bottom left grey-bg-500 grey-900">
						<span class="inline-block">
							<span class="icon icon-bloo"></span>
							<span class="number"><?= $item->item_cost; ?></span>
						</span>
						<button disabled class="form-ui black-bg grey-500 font w300 padding-5 _14 layer base absolute v-center right-10 uppercase kerning-1 normal">
							<?php
							if($item->item_type == 'key'){
								_e("You already own this item","bluerabbit"); 
							}else{
								_e("No More Left!","bluerabbit"); 
							}
							
							?>
						</button>
					</div>
				<?php } ?>
			</div>
			<?php if($item->item_type != 'reward' && $can_buy){ ?>
				<div class="card-confirm-action" id="confirm-buy-item-<?php echo $item->item_id; ?>">
					<div class="layer background absolute sq-full" onClick="activate('#confirm-buy-item-<?php echo $item->item_id; ?>');"></div>
					<div class="perfect-center layer base absolute text-center">
						<h3 class="font w900 uppercase kerning-3 yellow-400"><?php _e("Confirm Purchase","bluerabbit"); ?></h3>
						<h1 class="font padding-10 w300 kerning-3 white-color"><?= $item->item_name; ?></h1>
						<button class="form-ui font _24 w300 amber-bg-400 grey-800 uppercase kerning-2" onClick="buyItem(<?php echo $item->item_id; ?>);">
							<span class="icon icon-bloo"><?= toMoney($item->item_cost,""); ?></span>
						</button>
						<hr class="w-half margin-10 opacity-0">
						<button class="form-ui font _12 w900 red-bg-400 white-color uppercase kerning-2" onClick="activate('#confirm-buy-item-<?php echo $item->item_id; ?>');">
							<?= __("Cancel","bluerabbit"); ?>
						</button>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="card-face backface">
			<div class="layer base absolute sq-full <?=$color; ?>-bg-400" style="background-image: url(<?= $adventure->adventure_badge; ?>);"></div>
			<div class="layer base absolute sq-full <?=$color; ?>-gradient-500"></div>
			<div class="layer foreground absolute perfect-center mix-blend-overlay">
				<span class="relative block border border-all rounded-max border-10 white-color sq-200 padding-20">
					<span class="icon icon-<?=$icon;?> perfect-center font _100"></span>
				</span>
			</div>
		</div>
	</div>
</div>

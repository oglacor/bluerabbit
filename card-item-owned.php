<?php 
	if($item->item_type == 'key'){
		$icon = 'key'; $color = 'indigo';
	}elseif($item->item_type == 'reward'){
		$icon = 'winstate'; $color = 'teal';
	}else{
		$icon = 'basket'; $color = 'pink';
	}
?>

<div class="background blue-grey-bg-700 opacity-60 fixed" onClick="unloadCard();"></div>
<div class="card card-scene " id="<?= "item-$item->item_id"; ?>">
	<div class="background fixed" onClick="unloadCard();"></div>
	<div class="card-type text-center">
		<div class="background <?=$color; ?>-bg-400 border rounded-max"></div>
		<div class="highlight text-center padding-5 margin-0">
			<div class="icon-group">
				<span class="icon-button font _24 sq-40  transparent-bg icon-sm"><span class="icon icon-<?=$icon; ?>"></span></span>
				<span class="icon-content text-center white-color">
					<span class="line font _14 w600 kerning-3 uppercase"><?= __("Item","bluerabbit"); ?></span>
				</span>
				<?php if($isGM || $isNPC || $isAdmin){ ?>
					<a class="icon-button font _24 sq-40  icon-sm green-bg-400" href="<?php echo get_bloginfo("url")."/new-item/?item_id=$item->item_id&adventure_id=$item->adventure_id"; ?>">
						<span class="icon icon-edit"></span>
					</a>
					<a class="icon-button font _24 sq-40  icon-sm blue-grey-bg-700 foreground" href="<?php echo bloginfo('url')."/item/?adventure_id=$adventure->adventure_id&item_id=$item->item_id"; ?>">
						<span class="icon icon-transactions"></span>
					</a>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="container foreground boxed max-w-900">
		<div class="background grey-bg-900">
			<div class="background opacity-20 mix-blend-luminosity" style="background-image: url(<?= $item->item_badge; ?>);" >
			</div>
		</div>
		<div class="body-ui w-full foreground">
			<button class="absolute top right icon-button font _24 sq-40  transparent-bg icon-xs" onClick="unloadCard();"><span class="icon icon-cancel"></span></button>
			<div class="highlight text-center padding-20 white-color">
				<h1 class="font _36 w300 kerning-1"><?= $item->item_name; ?></h1>
				<h3 class="text-center font _14 opacity-70">
					<?= __("bought","bluerabbit")." ".get_time_ago(strtotime($item->trnx_date), $adventure->adventure_id); ?>
				</h3>
			</div>
			<div class="highlight text-center padding-0 white-color font _18">
				<div class="book-container">
					<img src="<?= $item->item_badge; ?>" style="visibility:hidden;">
					<div class="background">
						<div class="table padding-10"><div class="table-cell text-center bottom">
							<button class="icon-button font _24 sq-40  transparent-bg icon-lg opacity-70" onClick="changeSide();">
								<span class="background grey-gradient-800"></span>
								<span class="foreground icon icon-rotate"></span>
							</button>
						</div></div>
					</div>
					<div class="book-scene">
						<div class="book show-right">
							<div class="book-face front white-bg" style="background-image: url(<?= $item->item_badge; ?>);">
								<div class="front-gradient background grey-gradient-900"> </div>
							</div>
							<div class="book-face back <?= $color; ?>-bg-600">
								<div class="background <?= $color; ?>-gradient-900"></div>
								<div class="background" style="background-image: url(<?= $item->item_secret_badge; ?>);"></div>
							</div>
							<div class="book-face left grey-bg-900"></div>
							<div class="book-face right <?= $color; ?>-bg-400">
								<div class="background <?= $color; ?>-gradient-900"></div>
								<div class="foreground padding-20">
									<span class="icon-button font _24 sq-40  white-bg">
										<span class="icon icon-<?=$icon; ?> <?= $color; ?>-400"></span>
									</span>
								</div>
							</div>
							<div class="book-face top grey-bg-900"></div>
							<div class="book-face bottom grey-bg-900"></div>
						</div>
					</div>
				</div>
				<button class="form-ui <?=$color;?>-bg-400" onClick="showOverlay('#item-description-<?=$item->item_id; ?>');">
					<?= __("Description","bluerabbit"); ?>
				</button>
			</div>
			<div class="highlight text-center padding-0 margin-0">
				<div class="icon-group inline-table padding-10">
					<div class="icon-content">
					<?php if($item->item_type == 'consumable'){ ?>
						<?php if(($item->bought)>0){?>
							<?php if($use_items){ ?>
								<button class="form-ui indigo-bg-400 pull-right font _18 w700" onClick="showOverlay('#confirm-use-item-<?php echo $item->item_id; ?>');">
									<?php _e("Pick up","bluerabbit"); ?> <span class="icon icon-use"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-use-item-<?php echo $item->item_id; ?>">
									<button class="form-ui orange-bg-400 grey-800 font _18" onClick="useItem(<?php echo "$item->trnx_id, '', 1"; ?>);">
										<span class="icon icon-warning"></span><?php _e("Have you picked up your item?","bluerabbit"); ?>
									</button>
								</div>
							<?php } ?>
						<?php }elseif($item->bought<=0){?>
							<a class="form-ui green-bg-400 pull-right" href="<?php echo get_bloginfo('url')."/item-shop/?adventure_id=$item->adventure_id"; ?>">
								<span class="icon icon-shop"></span><?php _e("Go shopping","bluerabbit"); ?>
							</a>
						<?php } ?>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="layer overlay top left overlay-layer fixed sq-full" id="item-description-<?= $item->item_id; ?>">
	<div class="layer sq-full padding-20 text-right top left background fixed black-bg opacity-90" onClick="hideAllOverlay();">
		<span class="icon icon-cancel white-color font _36"></span>
	</div>
	<div class="perfect-center absolute layer base">
		<div class="background" onClick="hideAllOverlay();"></div>
		<div class="container boxed max-w-1200 foreground text-center wrap w-full">
			<h3 class="text-center font _18 uppercase w900 <?=$color;?>-A400 w-full opacity-80"> <?= __("Description","bluerabbit") ; ?> </h3>
			<h1 class="text-center font _48 <?=$color;?>-400 w-full"><?= $item->item_name ; ?></h1>
			<div class="padding-10 white-color">
				<?= apply_filters('the_content',$item->item_description); ?>
			</div>
			<div class="padding-10 white-color">
				<?= apply_filters('the_content',$item->item_secret_description); ?>
			</div>
		</div>
	</div>
</div>

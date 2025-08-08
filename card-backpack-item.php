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
	<div class="card-content">
		<div class="card-face frontface">
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
						<h1 class="font _30 w300 kerning-1"><?= $item->item_name; ?></h1>
					</div>
					<?php if($item->item_description){ ?>
						<div class="card-message white-color">
							<?= apply_filters('the_content',$item->item_description); ?>
						</div>
					<?php } ?>
				</div>
				<?php if($item->item_secret_badge || $item->item_secret_description){ ?>
					<div class="text-center w-full padding-10 absolute bottom left amber-bg-800">
						<button class="form-ui amber-bg-400 blue-grey-900 font w300 padding-5 _14  uppercase kerning-1 normal" onClick="activate('#secret-content-<?php echo $item->item_id; ?>');">
							<?= __("Examine","bluerabbit"); ?>
						</button>
					</div>
				<?php } ?>
			</div>
			<?php if($item->item_secret_badge || $item->item_secret_description){ ?>
				<div class="card-confirm-action" id="secret-content-<?php echo $item->item_id; ?>">
					<div class="layer background absolute sq-full" onClick="activate('#secret-content-<?php echo $item->item_id; ?>');"></div>
					<div class="perfect-center layer base absolute text-center">
						<?php if($item->item_secret_badge){ ?>
						<div class="badge-container">
							<img src="<?= $item->item_secret_badge; ?>" class="badge" >
							<img class="rotate-L-20 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
							<img class="rotate-R-30 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
							<img class="rotate-L-40 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
							<img class="rotate-R-60 mix-blend-overlay halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
							<img class="rotate-L-90 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
						</div>
						<?php }  ?>
						<div class="text-center padding-10 white-color">
							<h1 class="font _18 w800 kerning-1"><?= $item->item_name; ?></h1>
						</div>
						<?php if($item->item_secret_description){ ?>
							<div class="card-message white-color">
								<?= apply_filters('the_content',$item->item_secret_description); ?>
							</div>
						<?php } ?>
						<hr class="w-half margin-10 opacity-0">
						<button class="form-ui font _12 w900 red-bg-400 white-color uppercase kerning-2" onClick="activate('#secret-content-<?php echo $item->item_id; ?>');">
							<?= __("Close","bluerabbit"); ?>
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

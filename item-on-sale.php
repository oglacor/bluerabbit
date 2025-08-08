	<?php
	if($i->item_type == 'key'){
		$icon_type='key';
		$i_color = 'indigo';
	}elseif($i->item_type == 'consumable'){
		$icon_type='basket';
		$i_color = 'pink';
	}
	?>
	<div class="w-170 h-270 border rounded-20 overflow-hidden relative item-thumb inline-block <?="$i->item_type"; ?> <?="$i->item_category"; ?> filterable" id="<?="item-$i->item_id"; ?>">
		<input type="hidden" class="item-badge-url" value="<?php echo $i->item_badge; ?>">
		<input type="hidden" class="item-type-val" value="<?php echo $i->item_type; ?>">
		<input type="hidden" class="player_has_it" value="<?php echo $player_has_it; ?>">
		<div class="item-back <?=$i_color;?>-bg-400 opacity-1 absolute top left sq-full">
			<div class="background opacity-40 mix-blend-overlay"  style="background-image: url(<?= $i->item_badge;?>);"></div>
			<div class="background black-gradient opacity-40 mix-blend-overlay blend-overlay"  style="background-image: url(<?= $i->item_badge;?>);"></div>
			<div class="table sq-full">
				<div class="table-cell text-center">
					<button class="icon-button font _50 <?=$i_color;?>-400 white-bg" onClick="activate('#item-<?=$i->item_id;?>')">
						<span class="icon icon-<?=$icon_type;?>"></span>
					</button>
				</div>
			</div>
		</div>
		<div class="background white-bg opacity-20"></div>
		<div class="foreground padding-10 cursor-pointer" <?php if($current_player->player_level >= $i->item_level){ ?> onClick="loadItemCard(<?= $i->item_id; ?>);" <?php } ?>>
			<div class="deep-purple-bg-400 border rounded-max w-60 h-16 white-color text-center boxed font _12 w500 padding-5">
				<span class="icon icon-level"></span><?= $i->item_level;?>
			</div>
			<br>
			<div class="boxed sq-100 border rounded-max image-ui white-bg item-image" style="background-image: url(<?= $i->item_badge;?>);"></div>
			<div class="item-shadow"></div>
			<div class="table w-150 h-70 boxed white-color">
				<div class="table-cell text-center overflow-hidden font _18 w400 line-80">
					<?= $i->item_name; ?>
				</div>
			</div>
			<h2 class="font _20 w600 text-center white-color"><?= toMoney($i->item_cost,"$");?></h2>
		</div>
		<?php if($i->item_category){ ?>
			<div class="item-category foreground <?=$i->item_category; ?>-bg-400"></div>
		<?php } ?>
		<?php if($current_player->player_level < $i->item_level){ ?>
			<div class="foreground sq-full absolute top left">
				<div class="background deep-purple-bg-400 opacity-70"></div>
				<div class="foreground sq-full " <?php if($isGM || $isNPC || $isAdmin){ ?> onClick="loadItemCard(<?= $i->item_id; ?>);" <?php } ?>>
					<div class="table sq-full white-color">
						<div class="table-cell text-center">
							<span class="icon icon-lock font _20"></span>
							<h3 class="font _30 w900 kerning-1">
								<?= __("Level","bluerabbit")." $i->item_level";?> 
							</h3>
						</div>
					</div>
				</div>
			</div>
		<?php }elseif(!$available){ ?>
				<div class="layer background black-bg opacity-70" <?php if($isGM || $isNPC || $isAdmin){ ?> onClick="loadItemCard(<?= $i->item_id; ?>);" <?php } ?>></div>
				<div class="layer base absolute perfect-center text-center">
					<h3 class="font _18 condensed w900 <?= $i_color; ?>-bg-400 padding-10 white-color uppercase">
						<?= $selling_label; ?>
					</h3>
				</div>
		<?php } ?>
	</div>

	


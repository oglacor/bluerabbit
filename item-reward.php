<li class="card <?php echo "$i->item_type item-category-$cat "; ?>" id="milestone-item-<?php echo $i->item_id; ?>">
	<?php $badgeImage = $i->item_secret_badge ? $i->item_secret_badge : $i->item_badge; ?>
	<?php
		$icon_type='achievement';
		$i_color = 'teal';
	?>
	<input type="hidden" class="item-badge-url" value="<?php echo $i->item_badge; ?>">
	<input type="hidden" class="item-type-val" value="<?php echo $i->item_type; ?>">
	<?php if($current_player->player_level >= $i->item_level || $isAdmin || $isGM || $isNPC){ ?>
		<figure class="back" onClick="flipMilestone('item-<?php echo $i->item_id; ?>');">
			<div class="background" style="background-image: url(<?php echo $badgeImage; ?>);"></div>
			<div class="background grey-bg-900 opacity-80"></div>
			<div class="table foreground text-center">
				<div class="table-cell">
					<div class="highlight">
						<span class="icon-group">
							<span class="br-icon-btn hidden-mobile" <?php echo br_color_attr($i_color); ?>>
								<span class="icon icon-<?php echo $icon_type; ?>"></span>
							</span>
							<span class="icon-content">
								<span class="line br-text-24 w300 condensed white-color"><?php echo $i->item_name; ?></span>
							</span>
						</span>
					</div>
					<div class="highlight">
						<button class="form-ui green-bg-400 white-color">
							<span class="icon icon-bloo"></span> <?php echo BR_Utils::instance()->toMoney($i->item_cost,""); ?>
						</button>
					</div>
				</div>
			</div>
		</figure>
		<figure class="front">
			<div class="w-full h-250 relative  full-height" style="background-image: url(<?php echo $badgeImage; ?>);" >
				<div class="spacer text-center padding-10">
					<div class="background" onClick="flipMilestone('item-<?php echo $i->item_id; ?>');"></div>
					<?php if($isGM){ ?>
						<a class="br-icon-btn br-icon-btn-green foreground" href="<?php echo get_bloginfo('url')."/new-item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id"; ?>"><span class="icon icon-edit"></span></a>
						<button type="button" class="br-icon-btn br-icon-btn-red foreground" onClick="br_confirm_trd('trash',<?php echo $i->item_id; ?>,'item');" ><span class="icon icon-trash"></span></button>
					<?php } ?>
					<?php if($isGM || $isNPC){ ?>
						<a class="br-icon-btn br-icon-btn-pink foreground" href="<?php echo bloginfo('url')."/item/?adventure_id=$adventure->adventure_id&item_id=$i->item_id"; ?>">
							<span class="icon icon-transactions"></span>
						</a>
					<?php } ?>
					<button class="br-icon-btn br-icon-btn-grey white-color foreground" onClick="flipMilestone('item-<?php echo $i->item_id; ?>');"><span class="icon icon-cancel"></span></button>
				</div>

				<div class="corner circle upper-right deep-purple-bg-400 white-color br-text-20 w900 foreground">
					<?php echo $i->item_level; ?>
					<span class="tool-tip left">
						<span class="tool-tip-text"><?php _e("Required level","bluerabbit"); ?></span>
					</span>
				</div>
				<div class="spacer bottom fixed-125 text-center relative">
					<?php if($i->item_type != 'reward'){ ?>
						<?php if(!$can_buy && $i->item_type == 'consumable'){ ?>
							<button disabled class="form-ui pull-right grey-bg-200 foreground">
								<span class="icon icon-cancel"></span>
								<?php echo $buy_button_label; ?>
							</button>
						<?php }else{ ?>
							<?php if($trueStock > 0){ ?>
								<button class="form-ui green-bg-400 white-color font condensed w900 _24 uppercase foreground" onClick="showOverlay('#confirm-buy-item-<?php echo $i->item_id; ?>');">
									<?php echo $buy_button_label; ?>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-buy-item-<?php echo $i->item_id; ?>">
									<button class="form-ui grey-bg-800" onClick="buyItem(<?php echo $i->item_id; ?>);">
										<h3 class="text-center white-color br-text-18 condensed w100"><?php _e("Confirm","bluerabbit"); ?></h3>
										<span class="icon-group">
											<span class="br-icon-btn br-icon-btn-green">
												<span class="icon icon-bloo white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line white-color br-text-24 w900"><?php echo BR_Utils::instance()->toMoney($i->item_cost,""); ?></span>
											</span>
										</span>
									</button>
								</div>
							<?php }else{ ?>
								<button disabled class="form-ui pull-right grey-bg-200 foreground">
									<span class="icon icon-<?php echo $icon_type; ?>"></span>
									<?php _e("No More Items Left!","bluerabbit"); ?>
								</button>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<div class="spacer fluid bottom white-color text-center br-text-24 w300 condensed padding-10 foreground" <?php echo br_color_attr($i_color); ?>>
					<?php echo $i->item_name; ?></div>
				</div>
			</div>
<!--
			<div class="body-ui">
				<div class="content">
					<?php echo apply_filters('the_content',$i->item_description); ?>
				</div>
			</div>
-->
		</figure>
	<?php }else{ ?>
		<figure class="back white-color text-center opacity-70"> 
			<div class="background" style="background-image: url(<?php echo $badgeImage; ?>);"></div>
			<div class="background purple-bg-900 opacity-80"><span class="icon icon-lock br-text-40 white-color opacity-20"></span></div>
			<div class="table foreground text-center">
				<div class="table-cell">
					<div class="highlight">
						<span class="icon-group">
							<span class="br-icon-btn hidden-mobile" <?php echo br_color_attr($i_color); ?>>
								<span class="icon icon-<?php echo $icon_type; ?>"></span>
							</span>
							<span class="icon-content">
								<span class="line br-text-24 w300 condensed white-color"><?php echo $i->item_name; ?></span>
							</span>
						</span>
					</div>
					<div class="highlight">
						<span class="icon-group">
							<span class="br-icon-btn br-icon-btn-white purple-400 hidden-mobile">
								<span class="icon icon-lock"></span>
							</span>
							<span class="icon-content">
								<span class="line br-text-14 w300 condensed"><?php echo __("Level","bluerabbit"); ?></span>
								<span class="line br-text-24 w900"><?php echo $i->item_level; ?></span>
							</span>

						</span>
					</div>
					<span class="br-text-14 w300 condensed lime-400"><span class="icon icon-bloo"></span><?php echo __("Cost","bluerabbit")." ".BR_Utils::instance()->toMoney($i->item_cost,""); ?>
					</span>
				</div>
			</div>
		</figure>
	<?php } ?>
</li>


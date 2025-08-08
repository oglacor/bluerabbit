<?php include (get_stylesheet_directory() . '/header.php'); ?>	
<?php if($adventure){ ?>
		<?php 
		$consumables = $wpdb->get_results( "SELECT a.item_id, a.item_name, a.item_description, a.item_secret_description, a.item_type, a.item_badge, a.item_secret_badge,  a.item_level,  a.item_cost, 
		b.object_id, b.trnx_id, b.trnx_type, b.trnx_date, COUNT(a.item_id) AS total_consumables
		FROM  {$wpdb->prefix}br_items a 
		JOIN {$wpdb->prefix}br_transactions b
		ON a.item_id = b.object_id
		WHERE a.adventure_id=$adventure->adventure_id AND a.item_status='publish'  AND a.item_type='consumable' AND b.player_id=$current_user->ID AND b.trnx_type='consumable' AND b.trnx_use=0 AND b.trnx_status='publish'
		GROUP BY b.object_id, b.trnx_type");
	
		$key_items =  $wpdb->get_results( "SELECT a.item_id, a.item_name, a.item_description, a.item_secret_description, a.item_type, a.item_badge, a.item_secret_badge,  a.item_level,  a.item_cost, 
		b.object_id, b.trnx_type , b.trnx_date, COUNT(a.item_id) AS total_key
		FROM  {$wpdb->prefix}br_items a 
		JOIN {$wpdb->prefix}br_transactions b
		ON a.item_id = b.object_id
		WHERE a.adventure_id=$adventure->adventure_id AND a.item_status='publish'  AND a.item_type='key' AND b.player_id=$current_user->ID AND b.trnx_type='key' AND b.trnx_status='publish'
		GROUP BY b.object_id, b.trnx_type");
	
		$rewards = $wpdb->get_results( "SELECT items.*, trnxs.trnx_type, trnxs.player_id FROM  {$wpdb->prefix}br_items items
		LEFT JOIN {$wpdb->prefix}br_transactions trnxs 
		ON items.item_id = trnxs.object_id AND trnxs.player_id = $current_user->ID
		WHERE items.adventure_id=$adventure->adventure_id AND items.item_status='publish' AND items.item_type='reward' GROUP BY items.item_id ORDER BY items.item_order, items.item_id ");
		?>
			<div class="container boxed max-w-1200 item-shop">
				<div class="card">
					<div class="body-ui">
						<?php if($consumables){ ?>
							<div class="highlight padding-10 pink-bg-50">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  pink-bg-700 white-color">
										<span class="icon icon-basket"></span>
									</span>
									<span class="icon-content">
										<span class="font _24 line"><?php _e("My Items",'bluerabbit'); ?></span>
										<span class="font _14 line"><?php _e('Items bought in the item shop','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
							<div class="content white-bg">
								<ul class="cards items">
									<li class="card card-sizer"></li>
									<?php foreach($consumables as $i){ ?>
										<?php  include (TEMPLATEPATH . '/item.php'); ?>
									<?php } ?>
								</ul>
							</div>
							<?php if($reset_transatcions){ ?>
								<div class="highlight padding-10 red-bg-50">
									<div class="highlight-cell pull-right padding-10">
										<button class="form-ui red-bg-400 white-color" onClick="showOverlay('#confirm-reset-transactions');">
											<span class="icon icon-warning"></span><?php _e("Reset your transactions","bluerabbit"); ?>
										</button>
										<div class="confirm-action overlay-layer" id="confirm-reset-transactions">
											<button class="form-ui white-bg" onClick="resetTransactions(<?php echo $current_player->player_id; ?>);">
												<span class="icon-group">
													<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
														<span class="icon icon-trash white-color"></span>
													</span>
													<span class="icon-content">
														<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
														<span class="line font _14 grey-400"><?php _e("You can't undo this","bluerabbit"); ?></span>
													</span>
												</span>
											</button>
											<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
												<span class="icon icon-cancel white-color"></span>
											</button>
										</div>
										<input type="hidden" id="reset_nonce" value="<?php echo wp_create_nonce('reset_transactions_nonce'); ?>">
									</div>
									<br class="clear">
								</div>
							<?php } ?>
						<?php }else{ ?>
							<div class="highlight padding-10 pink-bg-50">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  pink-bg-700 white-color">
										<span class="icon icon-basket"></span>
									</span>
									<span class="icon-content">
										<span class="font _24 line"><?php _e("You haven't bought any items",'bluerabbit'); ?></span>
										<span class="font _14 line"><?php _e('Items bought in the item shop','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
						<?php } ?>
						<?php if($key_items){ ?>
							<div class="highlight padding-10 indigo-bg-50">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  light-blue-bg-700 white-color">
										<span class="icon icon-key"></span>
									</span>
									<span class="icon-content">
										<span class="font _24 line"><?php _e('My Key Items','bluerabbit'); ?></span>
										<span class="font _14 line"><?php _e('Items bought to unlock','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
							<div class="content white-bg">
								<ul class="cards items">
									<li class="card card-sizer"></li>
									<?php 
								   foreach($key_items as $i){
										include (TEMPLATEPATH . '/item.php'); 
									}
									?>
								</ul>
							</div>
						<?php } ?>
						<?php if($rewards){ ?>
							<div class="highlight padding-10 teal-bg-50">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  teal-bg-400 white-color">
										<span class="icon icon-backpack"></span>
									</span>
									<span class="icon-content">
										<span class="font _24 line"><?php _e('My collection','bluerabbit'); ?></span>
										<span class="font _14 line"><?php _e('Items earned after completing','bluerabbit'); ?></span>
									</span>
								</span>
							</div>
							<div class="content white-bg">
								<ul class="cards items">
									<li class="card card-sizer"></li>
									<?php 
									foreach($rewards as $i){
										if($i->trnx_type=='reward' && $i->player_id== $current_user->ID){ 
											include (TEMPLATEPATH . '/item.php'); 
										}else{
											include (TEMPLATEPATH . '/item-placeholder.php'); 
										}
									}
									?>
								</ul>
							</div>
						<?php } ?>
						<?php if($show_player_transactions){ ?>
						<?php 
							$itemsByPlayer = $wpdb->get_results( "SELECT a.player_id, a.player_display_name, a.player_email, b.trnx_id, b.trnx_date, b.trnx_type, b.trnx_amount, b.trnx_use, c.item_name, c.item_id
							FROM {$wpdb->prefix}br_players a
							JOIN {$wpdb->prefix}br_transactions b
							ON a.player_id = b.player_id AND (b.trnx_type='consumable' OR b.trnx_type='use' OR b.trnx_type='key')
							JOIN {$wpdb->prefix}br_items c
							ON b.object_id = c.item_id
							WHERE b.adventure_id=$adventure->adventure_id  AND b.trnx_status='publish' AND a.player_id=$current_user->ID
							ORDER BY b.trnx_id
							");
						?>	 
						<div class="highlight padding-10 grey-bg-50">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  orange-bg-400 white-color">
									<span class="icon icon-transactions"></span>
								</span>
								<span class="icon-content">
									<span class="font _24 line"><?php _e('My transactions','bluerabbit'); ?></span>
									<span class="font _14 line"><?php _e('Purchases and consumption','bluerabbit'); ?></span>
								</span>
							</span>
						</div>
						<div class="content">
							<table class="table">
								<thead>
									<tr>
										<td class="font uppercase w900 text-center"><?php _e("Item","bluerabbit"); ?></td>
										<td class=""><?php _e("Amount","bluerabbit"); ?></td>
										<td class=""><?php _e("Use","bluerabbit"); ?></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach($itemsByPlayer as $key=>$iT) { ?>
										<tr>
											<td>
												<span class="icon-group">
													<span class="icon-content">
														<span class="line font _18 w300"><?php echo $iT->item_name; ?></span>
														<span class="line font _14 w700 grey-500"><?php echo $iT->trnx_date; ?></span>
													</span>
												</span>
											</td>
											
											
											<td>
												<span class="form-ui light-green-bg-400 white-color font _16">
													<?php echo toMoney($iT->trnx_amount); ?>
												</span>
											</td>
											<td>
												<?php if($iT->trnx_use){ ?>
													<button class="form-ui grey-bg-400" disabled>
														<?php echo _e("used","bluerabbit"); ?>
													</button>
												<?php }else{ ?>
													<?php if($iT->trnx_type == 'consumable' && $use_items){ ?>
														<button class="form-ui indigo-bg-400" onClick="useItem(<?php echo "$iT->trnx_id, '', 1"; ?>);">
															<span class="icon icon-check"></span><?php echo _e("Use","bluerabbit"); ?>
														</button>
													<?php } ?>
												<?php } ?>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<?php } ?>
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
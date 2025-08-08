<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure && $_GET['item_id']){ ?>
		<?php 
			$i = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id={$_GET['item_id']} AND adventure_id=$adventure->adventure_id AND item_status='publish'");

			if($i){
				$myAchievements = getMyAchievements($adventure->adventure_id);
				$a_ids=(implode(",",$myAchievements)); 
				if($a_ids){ $condition = "items.achievement_id IN ($a_ids) OR "; }

				$items = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_items items

				LEFT JOIN  {$wpdb->prefix}br_achievements achievements
				ON items.achievement_id = achievements.achievement_id 

				WHERE items.adventure_id=$adventure->adventure_id AND items.item_status='publish' AND (items.item_type='consumable' OR items.item_type='key') AND ($condition items.achievement_id=0) ORDER BY items.item_category ASC, items.item_level ASC, items.item_cost ASC ");

				$allTransactions = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_transactions WHERE adventure_id=$adventure->adventure_id AND trnx_type='consumable' AND trnx_status='publish'AND object_id=$i->item_id");
				
				if($isGM){
					$rewards = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_items WHERE adventure_id=$adventure->adventure_id AND item_status='publish' AND item_type='reward' ORDER BY item_id");
				}

				$allTCount = array();
				$itemsBought=array();

				foreach($allTransactions as $t){
					if($t->player_id == $current_user->ID){
						$itemsBought[]=$t->object_id;
					}
					$allTCount[]=$t->object_id;
				}
				$consumption = array_count_values($allTCount);
				$my_items = array_count_values($itemsBought);



	
				if($isGM || $isNPC || $isAdmin){
					$itemTransactions = $wpdb->get_results( "SELECT a.player_id, a.player_display_name, a.player_email, b.trnx_id,  b.trnx_use, b.trnx_date, b.trnx_amount, c.item_name, c.item_type, c.item_id
					FROM {$wpdb->prefix}br_players a
					JOIN {$wpdb->prefix}br_transactions b
					ON a.player_id = b.player_id
					JOIN {$wpdb->prefix}br_items c
					ON b.object_id = c.item_id
					WHERE b.adventure_id=$adventure->adventure_id  AND b.trnx_status='publish' AND c.item_id=$i->item_id AND b.trnx_type IN ('consumable','key','reward')
					ORDER BY b.trnx_id
					");
					$badgeImage = $i->item_badge;
					if($i->item_type == 'key'){
						$icon_type='key';
						$i_color = 'indigo';
					}elseif($i->item_type == 'consumable'){
						$icon_type='basket';
						$i_color = 'pink';
					}elseif($i->item_type == 'reward'){
						$icon_type='achievement';
						$i_color = 'teal';
					}
				}
			}
		?>


		<?php if($isGM || $isNPC || $isAdmin){ ?>
			<div class="container boxed max-w-1200 white-color">
				<div class="highlight padding-10 orange-bg-200">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  indigo-bg-400"><span class="icon icon-transactions"></span></span>
						<span class="icon-content font _24 w300 indigo-800">
							<span><?php _e('Transactions','bluerabbit')." ".$i->item_name; ?></span>
						</span>
					</div>
					<div class="highlight-cell pull-right">
						<div class="input-group">
							<label class="indigo-bg-400"><span class="icon icon-search"></span></label>
							<input type="text" class="form-ui" id="search-trnxs" placeholder="<?php _e("Search transactions","bluerabbit"); ?>">
							<script>
								$('#search-trnxs').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('table#table-trnxs tbody > tr').show();           
									}else{
										$('table#table-trnxs tbody > tr').each(function(){
											var text = $(this).text().toLowerCase();
											(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
										});
									};
								});
							</script>				
						</div>
					</div>
				</div>
				<div class="w-full">
					<div class="content-loader max-w-400 relative layer base boxed white-color border rounded-20" id="<?= "item-$i->item_id"; ?>">
						<div class="deep-bg layer absolute sq-full white-bg opacity-20"></div>
						<div class="badge max-w-400 max-h-vh-half h-vh-full black-bg relative layer base border rounded-20" style="background-image: url(<?= $i->item_badge; ?>);">
							<div class="item-level absolute layer base top-10 right-10 font _24 w500 border border-all border-3 rounded-max sq-40 text-center"><?= $i->item_level;?></div>
						</div>
						<div class="content layer base relative">
							<div class="base layer relative">
								<h3 class="font _24 w600 padding-10"><?= $i->item_name;?></h3>
								<div class="font _14 w300 padding-10">
									<?= apply_filters('the_content',$i->item_description); ?>
								</div>
								<div class="border rounded-8 relative padding-10">
									<div class="layer background absolute sq-full white-bg opacity-30"></div>
									<div class="layer base relative text-center">
										<span class="icon-group inline-table">
											<span class="icon-content text-center white-color">
												<span class=" font _30 w600"><?=toMoney($i->item_cost,"$"); ?></span>
											</span>
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="content">

					<table class="text-center w-full table" id="table-trnxs">
						<thead>
							<tr>
								<td class=""><?php _e("Player","bluerabbit"); ?></td>
								<td class=""><?php _e("Amount","bluerabbit"); ?></td>
								<td class=""><?php _e("Actions","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($itemTransactions as $key=>$iT) { ?>
								<tr>
									<td>
										<span class="icon-group">
											<span class="icon-content">
												<span class="line font _18 w300"><?php echo $iT->player_display_name; ?></span>
												<span class="line font _14 w700 grey-500"><?php echo $iT->trnx_date; ?></span>
											</span>
										</span>
									</td>
									<td><?php echo $iT->trnx_amount; ?></td>
									<td>
										<?php if($iT->trnx_use){ ?>
											<button class="icon-button font _24 sq-40  icon-sm blue-bg-300" onClick="useItem(<?php echo "$iT->trnx_id, $iT->player_id, 0"; ?>);">
												<span class="icon icon-restore"></span>
											</button>

										<?php }else{ ?>
											<?php if($iT->item_type == 'consumable'){ ?>
												<button class="icon-button font _24 sq-40  icon-sm orange-bg-400" onClick="useItem(<?php echo "$iT->trnx_id , $iT->player_id, 1"; ?>);">
													<span class="icon icon-check"></span>
												</button>
											<?php } ?>
										<?php } ?>
										<?php if($isGM){ ?>
											<button class="icon-button font _24 sq-40  icon-sm red-bg-A400 white-color"  onClick="br_confirm_trd('delete',<?php echo $iT->trnx_id; ?>,'trnx');">
												<span class="icon icon-cancel"></span>
											</button>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php } ?>
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_item_nonce'); ?>"/>
		<input type="hidden" id="use-item-nonce" value="<?php echo wp_create_nonce('br_use_item_nonce'); ?>"/>
		<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
		<?php if($isGM){ ?>
			<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>"/>
		<?php } ?>
		<input type="hidden" id="reload" value="true"/>
	<?php }else{ ?>
		<h1><?php _e("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
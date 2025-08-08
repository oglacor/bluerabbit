<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure && ($isGM || $isNPC)){ ?>
		<?php 
			$transactions = $wpdb->get_results( "SELECT a.player_id, a.player_display_name, a.player_email, b.trnx_id,  b.trnx_use, b.trnx_date, b.trnx_amount, b.trnx_type, c.item_name, c.item_type, c.item_id
			FROM {$wpdb->prefix}br_players a
			JOIN {$wpdb->prefix}br_transactions b
			ON a.player_id = b.player_id
			JOIN {$wpdb->prefix}br_items c
			ON b.object_id = c.item_id
			WHERE b.adventure_id=$adventure->adventure_id  AND b.trnx_status='publish' AND b.trnx_type IN ('consumable','key','reward','tabi-piece')
			ORDER BY b.trnx_use ASC, b.trnx_id ASC LIMIT 1000
			");
		?>	 
		<div class="container boxed max-w-1200 wrap">
			<div class="body-ui w-full white-bg">
				<div class="highlight padding-10 orange-bg-200">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  indigo-bg-400"><span class="icon icon-transactions"></span></span>
						<span class="icon-content font _24 w300 indigo-800">
							<span><?= __('Transactions','bluerabbit'); ?></span>
						</span>
					</div>
					<div class="highlight-cell pull-right padding-10">
						<button class="icon-button font _24 sq-40  icon-sm black-bg" onClick="$('#table-trnxs tbody tr').show();"><span class="icon icon-infinite"></span></button>
						<button class="icon-button font _24 sq-40  icon-sm green-bg-400" onClick="$('#table-trnxs tbody tr').hide(); $('#table-trnxs tbody tr.new').show();"><span class="icon icon-check"></span></button>
						<button class="icon-button font _24 sq-40  icon-sm blue-bg-400" onClick="$('#table-trnxs tbody tr').hide(); $('#table-trnxs tbody tr.used').show();"><span class="icon icon-restore"></span></button>
					</div>
					<div class="highlight-cell pull-right">
						<div class="input-group">
							<label class="indigo-bg-400"><span class="icon icon-search"></span></label>
							<input type="text" class="form-ui" id="search-trnxs" placeholder="<?= __("Search transactions","bluerabbit"); ?>">
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

				<div class="content">

					<table class="text-center w-full table" id="table-trnxs">
						<thead>
							<tr>
								<td class=""><?= __("ID","bluerabbit"); ?></td>
								<td class=""><?= __("Player","bluerabbit"); ?></td>
								<td class=""><?= __("Email","bluerabbit"); ?></td>
								<td class=""><?= __("Date","bluerabbit"); ?></td>
								<td class=""><?= __("Item","bluerabbit"); ?></td>
								<td class=""><?= __("Use","bluerabbit"); ?></td>
								<td class=""><?= __("Return","bluerabbit"); ?></td>
								<td class=""><?= __("Delete","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php $color = array(
								'key'=>'indigo-bg-300',
								'consumable'=>'pink-bg-300',
								'reward'=>'teal-bg-300'
							);
							
							$spentBLOO =0;
							?>
							<?php foreach($transactions as $key=>$iT) { ?>
								<?php
								$spentBLOO += $iT->trnx_amount;
									if(!$iT->trnx_use){
										$status_class = 'green-bg-50 new';
									}else{
										$status_class = 'light-blue-bg-900 white-color used';
									}
								?>
								<tr class="<?= $status_class; ?>">
									<td class="">
										<span class="line font _14 w300">#<?php echo $iT->trnx_id; ?></span>
									</td>
									<td>
										<span class="icon-group inline-table">
											<span class="icon-content">
												<span class="line font _24 w700">
													<?php if($isAdmin || $isGM){ ?>
														<a href="<?= bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id&player_id=$iT->player_id"; ?>">
															<?= $iT->player_display_name; ?>
														</a>
													<?php }else{ ?>
														<?= $iT->player_display_name; ?>
													<?php } ?>
												</span>
											</span>
										</span>
									</td>
									<td>
										<span class="icon-group inline-table">
											<span class="line font _16 w300"><?= $iT->player_email; ?></span>
										</span>
									</td>
									<td>
										<span class="icon-group inline-table">
											<span class="line font _12 w300 grey-500"><?php echo $iT->trnx_date; ?></span>
										</span>
									</td>
									<td>
										<a class="form-ui <?php echo $color[$iT->trnx_type]; ?>" href="<?= bloginfo('url')."/item/?adventure_id=$adventure->adventure_id&item_id=$iT->item_id"; ?>">
											<strong><?php echo $iT->item_name; ?></strong>
										</a>
									</td>
									<td>
										<?php if(!$iT->trnx_use && $iT->item_type == 'consumable'){ ?>
											<button class="icon-button font _24 sq-40  icon-sm green-bg-400" onClick="useItem(<?php echo "$iT->trnx_id , $iT->player_id, 1"; ?>);">
												<span class="icon icon-check"></span>
											</button>
										<?php } ?>
									</td>
									<td>
										<?php if($iT->trnx_use){ ?>
											<button class="icon-button font _24 sq-40  icon-sm blue-bg-300" onClick="useItem(<?php echo "$iT->trnx_id, $iT->player_id, 0"; ?>);">
												<span class="icon icon-restore"></span>
											</button>
										<?php } ?>
									</td>
									<td>
										<?php if($isGM || $isAdmin){ ?>
											<button class="icon-button font _24 sq-40  icon-sm red-bg-400 white-color" onClick="br_confirm_trd('delete',<?php echo $iT->trnx_id; ?>,'trnx');">
												<span class="icon icon-cancel"></span>
											</button>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<h1><?= $spentBLOO; ?></h1>
				</div>
				<input type="hidden" id="use-item-nonce" value="<?php echo wp_create_nonce('br_use_item_nonce'); ?>"/>
				<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
				<?php if($isGM){ ?>
					<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>"/>
				<?php } ?>
				<input type="hidden" id="reload" value="true"/>
			</div>
		</div>
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_item_nonce'); ?>"/>
	<?php }else{ ?>
		<h1><?= __("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
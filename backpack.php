<?php 
	$myItems = $wpdb->get_results( "SELECT a.item_id, a.item_name, a.item_description, a.item_secret_description, a.item_type, a.item_badge, a.item_secret_badge,  a.item_level,  a.item_cost, 
	b.object_id, b.trnx_id, b.trnx_type, b.trnx_date, COUNT(a.item_id) AS total_consumables
	FROM  {$wpdb->prefix}br_items a 
	JOIN {$wpdb->prefix}br_transactions b
	ON a.item_id = b.object_id
	WHERE a.adventure_id=$adventure->adventure_id AND a.item_status='publish' AND b.player_id=$current_user->ID  AND trnxs.adventure_id=$adv_child_id  AND (b.trnx_type='consumable' OR b.trnx_type='key' OR b.trnx_type='reward' OR b.trnx_type='tabi-piece') AND b.trnx_use=0 AND b.trnx_status='publish'
	GROUP BY b.object_id, b.trnx_type");
?>
	<div class="background fixed top left" onClick="unloadContent();"></div>
	<div class="boxed max-w-1200 item-shop layer relative base">
		<?php if($myItems){ ?>
			<div class="relative layer base highlight padding-10 white-color">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  icon-lg brown-bg-400"><span class="icon icon-backpack"></span></span>
					<span class="icon-content">
						<span class="line font _30 white-color">
							<?php _e("Backpack","bluerabbit"); ?>
						</span>
						<span class="font _14 line"><?php _e("Items purchased or earned by completing quests",'bluerabbit'); ?></span>
					</span>
				</span>
			</div>
			<div class="relative layer base content">
				<ul class="badges">
					<?php foreach($myItems as $i){ ?>
						<?php  include (TEMPLATEPATH . '/item-in-backpack.php'); ?>
					<?php } ?>
				</ul>
			</div>
		<?php }else{ ?>

			<div class="relative layer base highlight padding-10 white-color text-center">
				<h2 class="font w900 _40"><span class="icon icon-backpack"></span><?= __("Your backpack is empty","bluerabbit"); ?></h2>
				<h3><?= __("Visit the item shop or complete quests to get more items.","bluerabbit"); ?></h3>
				<a class="form-ui pink-bg-400 margin-10" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>"><?php _e('Visit the item shop','bluerabbit'); ?></a>
			</div>
		<?php } ?>
	</div>

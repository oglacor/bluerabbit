<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($adventure){ ?>
<?php
	if($isGM){
		$blockers = $wpdb->get_results("SELECT 
			a.*, b.player_id, c.trnx_amount

			FROM {$wpdb->prefix}br_blockers a
			LEFT JOIN {$wpdb->prefix}br_player_blocker b
			ON a.blocker_id = b.blocker_id
			
			LEFT JOIN {$wpdb->prefix}br_transactions c
			ON a.blocker_id = c.object_id AND c.trnx_type='blocker'
			
			WHERE a.adventure_id=$adventure->adventure_id AND a.blocker_status='publish'
			GROUP BY a.blocker_id
		");
	}else{
		$blockers = $wpdb->get_results("SELECT 
			blockers.*, player.player_id, trnxs.trnx_amount, trnxs.object_id
			FROM {$wpdb->prefix}br_blockers blockers
			JOIN {$wpdb->prefix}br_player_blocker player
			ON blockers.blocker_id = player.blocker_id
			LEFT JOIN {$wpdb->prefix}br_transactions trnxs
			ON blockers.blocker_id = trnxs.object_id AND trnxs.trnx_type='blocker' AND trnxs.trnx_status='publish'
			WHERE blockers.adventure_id=$adventure->adventure_id AND blockers.blocker_status='publish' AND player.player_id=$current_user->ID 
		");
	}
?>
<div class="text-center padding-10">
	<span class="icon-button sq-100 white-bg border border-1 border-all white-border relative" style="background-image: url(<?= $current_player->player_picture; ?>); ">
		<span class="icon icon-lock font _60"></span>
	</span>
	<h2 class="font _48 white-color"><?php _e("Blockers","bluerabbit"); ?></h2>
</div>
<div class="container boxed max-w-1200 white-color">
	<table class="table small">
		<thead>
			<tr class="">
				<td class="id"><?php _e("ID","bluerabbit"); ?></td>
				<td class="date"><?php _e("Date","bluerabbit"); ?></td>
				<td class="desc"><?php _e("Reason","bluerabbit"); ?></td>
				<td class="cost"><?php _e("Cost","bluerabbit"); ?></td>
				<td class="actions"><?php _e("Actions","bluerabbit"); ?></td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($blockers as $key=>$b){ ?>
				<?php
				$paid= $debt=false;
				if($b->player_id==$current_user->ID){
					//has debt
					$debt=true;
					if($b->trnx_amount >= $b->blocker_cost){ 
						$paid=true;
					}else{ 
						$paid=false;
					}
				}
				?>
				<tr>
					<td class="id" id="blocker-<?php echo $b->blocker_id; ?>"><strong>#<?php echo $b->blocker_id; ?></strong></td>
					<td><strong><?php echo date('D, F jS, Y', strtotime($b->blocker_date)); ?></strong></td>
					<td><?php echo apply_filters('the_content',$b->blocker_description); ?></td>
					<td><strong><?php echo toMoney($b->blocker_cost); ?></strong></td>
					<td>
						<?php if($debt){ ?>
							<?php if($paid){ ?>
								<span class="green-bg-400 padding-10 white-color font w900"><span class='icon icon-check'></span><?php _e("Paid","bluerabbit"); ?></span>
							<?php }else{ ?> 
								<button class="form-ui cyan-bg-400" onClick="payBlocker(<?php echo $b->blocker_id; ?>)">
									<span class="icon icon-bloo"></span><?php _e("Pay","bluerabbit"); ?>
								</button>
							<?php }	?>
						<?php }	?>
						<?php if($isGM){ ?>
							<a href="<?php echo get_bloginfo('url')."/new-blocker/?adventure_id=$adventure->adventure_id&blockerID=$b->blocker_id";?>" class="icon-button font _18 sq-30  green-bg-400"><span class="icon icon-edit"></span></a>

							<button type="button" class="icon-button font _18 sq-30  red-bg-400" onClick="br_confirm_trd('trash',<?php echo $b->blocker_id; ?>,'blocker');" ><span class="icon icon-trash"></span></button>


						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_pay_blocker_nonce'); ?>"/>
		<?php if ($isGM){ ?>
			<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
			<input type="hidden" id="reload" value="true"/>
		<?php } ?>
		<br class="clear">
	</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
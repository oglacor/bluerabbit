<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($adventure){ ?>
		<?php 
	
			$limit = $leaderboard_limit ? $leaderboard_limit : 10;
			$players = $wpdb->get_results("
			SELECT 
			a.player_id, a.achievement_id, a.player_xp, a.player_bloo, a.player_level, a.player_gpa,
			b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad
			FROM {$wpdb->prefix}br_player_adventure a
			LEFT JOIN {$wpdb->prefix}br_players b
			ON a.player_id=b.player_id
			WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' AND a.player_adventure_role='player'
			GROUP BY a.player_id ORDER BY a.player_xp DESC, a.player_level DESC, a.player_bloo DESC, a.player_id ASC LIMIT $limit
			");
		?>
		<div class="layer background fixed fixed-bg" style="background-image: url(<?= $bg;?>);"></div>
		<div class="layer base relative boxed max-w-1200">
			<h1 class="text-center w-full margin-20 white-color font _40 w900 uppercase"><?= __("Leaderboard","bluerabbit");  ?></h1>
			<table class="table white-color">
				<thead>
					<tr class="">
						<td class="text-center"><?php _e("Place","bluerabbit"); ?></td>
						<td class=""><?php _e("Player","bluerabbit"); ?></td>
						<td class="text-center"><?php _e("Level","bluerabbit"); ?></td>
						<?php if($isGM){ ?>
							<td class="text-center"><?= $xp_label; ?></td>
							<td class="text-center"><?= $bloo_label; ?></td>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach($players as $key=>$p) { ?>
						<tr style="background-color: rgba(192,179,220,0.15);">
							<td class="text-center font _30 yellow-bg-400 black-color w900"><?php echo $key+1; ?></td>
							<td>
								<span class="icon-group">

									<span class="icon-button font _24 sq-40 " style="background-image: url(<?php echo $p->player_picture; ?>)"></span>
									<span class="icon-content">
										<span class="line font _18"><?= $p->player_display_name ? $p->player_display_name : $p->player_nickname; ?>
										</span>
									</span>
								</span>
							</td>
							<td class="text-center relative">
								<span class="font _24 w900 purple-400"><span class="icon icon-level"></span><?php echo $p->player_level; ?></span><br>
							</td>
							<?php if($isGM){ ?>
								<td class="text-center"><?php echo toMoney($p->player_xp,""); ?></td>
								<td class="text-center"><?php echo toMoney($p->player_bloo,""); ?></td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_item_nonce'); ?>"/>
	<?php }else{ ?>
		<h1><?php _e("Adventure doesn't exist"); ?></h1>
		<script>document.location.href="<?php bloginfo('url');?>"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
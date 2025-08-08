<?php include (get_stylesheet_directory() . '/header.php'); ?>

<div class="padding-20 white-color">
<?php
	$nonce=wp_create_nonce('blue_rabbit_guild_enrollment_nonce'); 
	$guild_code = $_GET['t'];
	global $wpdb;
	$e = array();
	$e['success'] = false;
	if(wp_verify_nonce($nonce, 'blue_rabbit_guild_enrollment_nonce')){
		$guild = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_guilds WHERE guild_code='$guild_code'");
		if($guild){
			$same_group = $wpdb->get_row( "SELECT player_guild.player_id, guild.* FROM {$wpdb->prefix}br_player_guild player_guild
			LEFT JOIN {$wpdb->prefix}br_guilds guild ON player_guild.guild_id = guild.guild_id
			WHERE guild.adventure_id=$adventure->adventure_id AND player_guild.player_id=$current_user->ID AND guild.guild_group='$guild->guild_group'");

			$same_guild = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_player_guild WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID AND guild_id='$guild->guild_id'");
			?>
			
			<?php if($same_guild){ ?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?=__("You are already a member of",'bluerabbit');?></h3>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= $guild->guild_name; ?>
						</h1>
						<img src="<?= $guild->guild_logo;; ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Joruney on!",'bluerabbit'); ?>
					</a>
				</div>
			<?php }elseif($same_group){ ?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?=__("You are already a member of",'bluerabbit');?></h3>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= $same_group->guild_name; ?>
						</h1>
						<img src="<?= $same_group->guild_logo;; ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<h3 class="font w300 _24"><?=__("You can't enroll in another guild",'bluerabbit');?></h3>
					<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Joruney on!",'bluerabbit'); ?>
					</a>
				</div>
			<?php }else{ ?>
				<?php
				$sql = "INSERT INTO {$wpdb->prefix}br_player_guild (adventure_id,player_id,guild_id) VALUES (%d,%d,%d)";
				$sql = $wpdb->prepare ($sql,$adventure->adventure_id,$current_user->ID, $guild->guild_id);
				$wpdb->query($sql);
				$wpdb->flush;
				?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?=__("Welcome!!",'bluerabbit');?></h3>
					<h2 class="font w600 _36"><?=__("You are now part of",'bluerabbit');?></h2>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= $guild->guild_name; ?>
						</h1>
						<img src="<?= $guild->guild_logo;; ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Joruney on!",'bluerabbit'); ?>
					</a>
				</div>
			<?php } ?>
		<?php }else{ // WRONG CODE ?>
			<div class="layer base relative boxed w-340 text-center">
				<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
					<?= __("This guild doesn't exist","bluerabbit"); ?>
				</h1>
				<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
					<span class="icon icon-home"></span>
					<?php _e("Back to home",'bluerabbit'); ?>
				</a>
			</div>
		<?php } ?>




	<?php }else{ ?>
		<div class="layer base relative boxed w-340 text-center">
			<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
				<?= __('Unauthorized access','bluerabbit');?>
			</h1>
			<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
				<span class="icon icon-home"></span>
				<?php _e("Back to home",'bluerabbit'); ?>
			</a>
		</div>
	<?php } ?>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

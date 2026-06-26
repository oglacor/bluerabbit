<?php include (get_stylesheet_directory() . '/header.php'); ?>

<div class="padding-20 white-color">
<?php
	$guild_code = isset($_GET['t']) ? sanitize_text_field($_GET['t']) : null;
	global $wpdb;

	if (!$guild_code) { ?>
		<div class="layer base relative boxed w-340 text-center">
			<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
				<?= __('No code provided', 'bluerabbit'); ?>
			</h1>
			<a href="<?= get_bloginfo('url'); ?>" class="form-ui blue-bg-700 big">
				<span class="icon icon-home"></span>
				<?php _e("Back to home", 'bluerabbit'); ?>
			</a>
		</div>
	<?php } else {

		$guild = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}br_guilds WHERE guild_code = %s",
			$guild_code
		));

		if ($guild) {
			$same_group = $wpdb->get_row($wpdb->prepare(
				"SELECT player_guild.player_id, guild.* FROM {$wpdb->prefix}br_player_guild player_guild
				LEFT JOIN {$wpdb->prefix}br_guilds guild ON player_guild.guild_id = guild.guild_id
				WHERE guild.adventure_id = %d AND player_guild.player_id = %d AND guild.guild_group = %s",
				$adventure->adventure_id, $current_user->ID, $guild->guild_group
			));

			$same_guild = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}br_player_guild WHERE adventure_id = %d AND player_id = %d AND guild_id = %d",
				$adventure->adventure_id, $current_user->ID, $guild->guild_id
			));

			if ($same_guild) { ?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?= __("You are already a member of", 'bluerabbit'); ?></h3>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= esc_html($guild->guild_name); ?>
						</h1>
						<img src="<?= esc_attr($guild->guild_logo); ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a1.png">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a2.png">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a3.png">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo" src="<?= get_bloginfo('template_directory'); ?>/images/a4.png">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a5.png">
					</div>
					<a href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Journey on!", 'bluerabbit'); ?>
					</a>
				</div>

			<?php } elseif ($same_group) { ?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?= __("You are already a member of", 'bluerabbit'); ?></h3>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= esc_html($same_group->guild_name); ?>
						</h1>
						<img src="<?= esc_attr($same_group->guild_logo); ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a1.png">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a2.png">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a3.png">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo" src="<?= get_bloginfo('template_directory'); ?>/images/a4.png">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a5.png">
					</div>
					<h3 class="font w300 _24"><?= __("You can't enroll in another guild", 'bluerabbit'); ?></h3>
					<a href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Journey on!", 'bluerabbit'); ?>
					</a>
				</div>

			<?php } else {
				$wpdb->query($wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}br_player_guild (adventure_id, player_id, guild_id) VALUES (%d, %d, %d)",
					$adventure->adventure_id, $current_user->ID, $guild->guild_id
				));
				$wpdb->query($wpdb->prepare(
					"UPDATE {$wpdb->prefix}br_player_adventure SET player_guild = %d WHERE adventure_id = %d AND player_id = %d",
					$guild->guild_id, $adventure->adventure_id, $current_user->ID
				));
				BR_Activity::instance()->logActivity($adventure->adventure_id, 'enroll', 'guild', '', $guild->guild_id);
				?>
				<div class="layer base relative boxed w-340 text-center">
					<h3 class="font w300 _24"><?= __("Welcome!!", 'bluerabbit'); ?></h3>
					<h2 class="font w600 _36"><?= __("You are now part of", 'bluerabbit'); ?></h2>
					<div class="guild-badge sq-300 relative inline-block">
						<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
							<?= esc_html($guild->guild_name); ?>
						</h1>
						<img src="<?= esc_attr($guild->guild_logo); ?>" class="border rounded-max sq-300 layer base relative">
						<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a1.png">
						<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a2.png">
						<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a3.png">
						<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo" src="<?= get_bloginfo('template_directory'); ?>/images/a4.png">
						<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory'); ?>/images/a5.png">
					</div>
					<a href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui deep-purple-bg-400 font _30">
						<span class="icon icon-journey"></span>
						<?php _e("Journey on!", 'bluerabbit'); ?>
					</a>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="layer base relative boxed w-340 text-center">
				<h1 class="text-center font _40 w600 kerning-1 white-color layer base">
					<?= __("This guild doesn't exist", "bluerabbit"); ?>
				</h1>
				<a href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
					<span class="icon icon-home"></span>
					<?php _e("Back to home", 'bluerabbit'); ?>
				</a>
			</div>
		<?php } ?>
	<?php } ?>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

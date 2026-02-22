<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
	if($isGM || $isAdmin || $isNPC ){
		$guilds = $wpdb->get_results("SELECT 
		
		guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity 
		
		FROM {$wpdb->prefix}br_guilds guilds
		
		LEFT JOIN {$wpdb->prefix}br_player_guild guild_players 
		ON guilds.guild_id=guild_players.guild_id
		
		WHERE guilds.adventure_id=$adventure->adventure_id AND guilds.guild_status='publish'
		GROUP BY guilds.guild_id ORDER BY guilds.guild_id ASC
		");
	}else{
		$guilds = $wpdb->get_results("SELECT 
			a.*, b.player_id

			FROM {$wpdb->prefix}br_guilds a
			JOIN {$wpdb->prefix}br_player_guild b

			ON a.guild_id = b.guild_id

			WHERE a.adventure_id=$adventure->adventure_id AND a.guild_status='publish' AND b.player_id=$current_user->ID
			GROUP BY a.guild_id ORDER BY a.guild_xp
			
		");
	}
	if($use_leaderboard){
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$leaderboard_guilds = $wpdb->get_results("
			SELECT 
				guilds.*, 
				COUNT(guild_players.player_id) AS guild_current_capacity,
				SUM(player_adventure.player_xp) AS total_player_xp,
				SUM(player_adventure.player_bloo) AS total_player_bloo
			FROM {$wpdb->prefix}br_guilds guilds
			LEFT JOIN {$wpdb->prefix}br_player_guild guild_players 
				ON guilds.guild_id = guild_players.guild_id
			LEFT JOIN {$wpdb->prefix}br_player_adventure player_adventure
				ON guild_players.player_id = player_adventure.player_id
				AND player_adventure.adventure_id = guilds.adventure_id
			WHERE guilds.adventure_id = $adventure->adventure_id AND guilds.assign_on_login=1 
			AND guilds.guild_status = 'publish'
			GROUP BY guilds.guild_id
			ORDER BY total_player_xp DESC
		");
        $leaderboard_guilds_array = array();
		$guild_xp_update = "INSERT INTO {$wpdb->prefix}br_guilds (guild_id, guild_xp) VALUES ";
		$guild_xp_update_values = array();
		$guild_xp_update_placeholders = array();
		foreach($leaderboard_guilds as $lg){
			array_push($guild_xp_update_values, $lg->guild_id, $lg->total_player_xp);
			$guild_xp_update_placeholders[] = "(%d,%d)";
            $leaderboard_guilds_array[$lg->guild_id] = $lg->total_player_xp;
		}
		$guild_xp_update .= implode(', ', $guild_xp_update_placeholders);
		$guild_xp_update .=" ON DUPLICATE KEY UPDATE guild_xp=VALUES(guild_xp)";
		$guild_xp_update_query = $wpdb->query( $wpdb->prepare("$guild_xp_update ", $guild_xp_update_values));
        print_r($wpdb->last_query);
	}
?>

<div class="guilds">
    <div class="my-guild">
        <div class="hud-title">
            <h2>
                <span class="hud-title-label"><?= __("My Guild","bluerabbit"); ?></span>
            </h2>
        </div>


        <?php foreach($guilds as $g){ ?>
            <?php include (TEMPLATEPATH . '/guild.php'); ?>
        <?php } ?>
    </div>
</div>



	<?php if($use_leaderboard) { ?>
		<div class="container boxed max-w-1200 wrap">
			<div class="highlight text-center">
				<span class="button-form-ui border rounded-max yellow-bg-500 blue-grey-800 font _24">
					<span class="icon icon-progression"></span>
					<?php _e("Leaderboard","bluerabbit"); ?>
				</span>
			</div>
			<div class="body-ui w-full">
				<div class="content">
					<?php if($leaderboard_guilds){ ?>
						<ul class="cards guilds text-center">
							<?php foreach($leaderboard_guilds as $g){ ?>
								<?php include (TEMPLATEPATH . '/guild-leaderboard.php'); ?>
							<?php } ?>
						</ul>
					<?php }else{ ?>
						<h4><?php _e("You are not part of any guild yet","bluerabbit"); ?></h4>
						<a class=" form-ui indigo" href="<?= get_bloginfo("url")."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
							<span class="icon icon-home"></span> <strong><?php _e("Back to home","bluerabbit"); ?></strong>
						</a>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>
	<div class="container boxed max-w-1200 wrap">
		<div class="highlight">
			<div class="icon-group">
				<span class="icon-button font _24 sq-40  light-green-bg-400">
					<span class="icon icon-guild white-color"></span>
				</span>
				<span class="icon-content">
					<?php if($isGM || $isAdmin || $isNPC ){ ?>
						<h1 class="font _24 white-color"><?php _e("Guilds","bluerabbit"); ?></h1>
					<?php } else { ?>
						<h1 class="font _24 white-color"><?php _e("My Guilds","bluerabbit"); ?></h1>
					<?php } ?>
				</span>
			</div>
		</div>
		<div class="body-ui w-full">
			<div class="content">
				<?php if($guilds){ ?>
					<ul class="cards guilds">
						<?php foreach($guilds as $g){ ?>
							<?php include (TEMPLATEPATH . '/guild.php'); ?>
						<?php } ?>
					</ul>
				<?php }else{ ?>
					<h4><?php _e("You are not part of any guild yet","bluerabbit"); ?></h4>
					<a class=" form-ui indigo" href="<?= get_bloginfo("url")."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
						<span class="icon icon-home"></span> <strong><?php _e("Back to home","bluerabbit"); ?></strong>
					</a>
				<?php } ?>
			</div>
			<?php if(($isGM || $isAdmin || $isNPC) && $guilds){ ?>
				<div class="content white-color">
					<table class="table transparent-bg">
						<thead>
							<tr>
								<td><?php _e("#","bluerabbit"); ?></td>
								<td><?php _e("Name","bluerabbit"); ?></td>
								<td><?php _e("Link","bluerabbit"); ?></td>
								<td class="text-center"><?php _e("XP","bluerabbit"); ?></td>
								<td class="text-center"><?php _e("BLOO","bluerabbit"); ?></td>
								<td class="text-center"><?php _e("Enrolled","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($guilds as $key=>$g){ ?>
								<tr class="transparent-bg">
									<td><?= $key+1; ?> </td>
									<td><?= $g->guild_name; ?> </td>
									<td><?= get_bloginfo('url')."/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?> </td>
									<td class="text-center"><?= $g->guild_xp;  ?></td>
									<td class="text-center"><?= $g->guild_bloo; ?></td>
									<td class="text-center"><?= "$g->guild_current_capacity / $g->guild_capacity"; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
		<br class="clear">
		<?php if ($isGM){ ?> <input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>"/> <?php } ?>
	</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
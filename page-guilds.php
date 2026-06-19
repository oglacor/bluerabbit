<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
	if($isGM || $isAdmin || $isNPC ){
		$guilds = $wpdb->get_results("SELECT 
		
		guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity 
		
		FROM {$wpdb->prefix}br_guilds guilds
		
		LEFT JOIN {$wpdb->prefix}br_player_guild guild_players 
		ON guilds.guild_id=guild_players.guild_id
		
		WHERE guilds.adventure_id=$adv_child_id AND guilds.guild_status='publish'
		GROUP BY guilds.guild_id ORDER BY guilds.guild_id ASC
		");
	}else{
		$allguilds = getGuilds($adv_child_id);
		$guilds = $allguilds['publish'];
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
			WHERE guilds.adventure_id = $adventure->adventure_id
			AND guilds.guild_status = 'publish'
			GROUP BY guilds.guild_id
			ORDER BY total_player_xp DESC
		");
        $leaderboard_guilds_array = array();
		$leaderboard_bloo_array = array();
		$guild_xp_update = "INSERT INTO {$wpdb->prefix}br_guilds (guild_id, guild_xp) VALUES ";
		$guild_xp_update_values = array();
		$guild_xp_update_placeholders = array();
		foreach($leaderboard_guilds as $lg){
			array_push($guild_xp_update_values, $lg->guild_id, $lg->total_player_xp);
			$guild_xp_update_placeholders[] = "(%d,%d)";
            $leaderboard_guilds_array[$lg->guild_id] = $lg->total_player_xp;
            $leaderboard_bloo_array[$lg->guild_id] = $lg->total_player_bloo;
		}
		$guild_xp_update .= implode(', ', $guild_xp_update_placeholders);
		$guild_xp_update .=" ON DUPLICATE KEY UPDATE guild_xp=VALUES(guild_xp)";
		$guild_xp_update_query = $wpdb->query( $wpdb->prepare("$guild_xp_update ", $guild_xp_update_values));
		$user_guild_id = !empty($guilds) ? $guilds[0]->guild_id : 0;
		$guild_rank_map = [];
		foreach($leaderboard_guilds as $rank_index => $rank_guild) {
			$guild_rank_map[$rank_guild->guild_id] = $rank_index + 1;
		}
		$guild_rank = $guild_rank_map[$user_guild_id] ?? 0;
	}
?>


<div class="guilds-page-layout">
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

    <?php if($use_leaderboard && !empty($leaderboard_guilds)) { ?>
    <div class="guild-leaderboard-section">
        <div class="hud-title">
            <h2>
                <span class="hud-title-label"><?= __("Leaderboard","bluerabbit"); ?></span>
            </h2>
        </div>
        <ul class="cards guilds">
            <?php foreach($leaderboard_guilds as $loop_index => $lg){ ?>
                <?php include (TEMPLATEPATH . '/guild-leaderboard.php'); ?>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
</div>
	<div class="container boxed max-w-1200 wrap">
		<div class="highlight">
			<div class="icon-group">
				<span class="button-icon font _24 sq-40  light-green-bg-400">
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
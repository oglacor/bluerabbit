<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
	$my_guild_list = BR_Guild::instance()->getMyGuilds($adv_child_id);

	if($use_leaderboard){
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
		if(!empty($guild_xp_update_placeholders)){
			$guild_xp_update .= implode(', ', $guild_xp_update_placeholders);
			$guild_xp_update .=" ON DUPLICATE KEY UPDATE guild_xp=VALUES(guild_xp)";
			$guild_xp_update_query = $wpdb->query( $wpdb->prepare("$guild_xp_update ", $guild_xp_update_values));
		}
		$user_guild_id = !empty($my_guild_list) ? $my_guild_list[0]->guild_id : 0;
		$guild_rank_map = [];
		foreach($leaderboard_guilds as $rank_index => $rank_guild) {
			$guild_rank_map[$rank_guild->guild_id] = $rank_index + 1;
		}
		$guild_rank = $guild_rank_map[$user_guild_id] ?? 0;

		// Top 5 stay as the big "My Guild"-style cards; everything else moves
		// into a paginated table - 91 guilds in one flat list isn't usable.
		$top_guilds = array_slice($leaderboard_guilds, 0, 5);
		$rest_guilds = array_slice($leaderboard_guilds, 5);
		$guild_per_page = 20;
		$guild_total_pages = max(1, (int) ceil(count($rest_guilds) / $guild_per_page));
		$guild_page = isset($_GET['guild_page']) ? max(1, (int) $_GET['guild_page']) : 1;
		$guild_page = min($guild_page, $guild_total_pages);
		$rest_guilds_page = array_slice($rest_guilds, ($guild_page - 1) * $guild_per_page, $guild_per_page);
	}
?>


<div class="guilds-page-layout">
    <div class="my-guild">
        <div class="hud-title">
            <h2>
                <span class="hud-title-label"><?= __("My Guild","bluerabbit"); ?></span>
            </h2>
        </div>
        <?php foreach($my_guild_list as $g){ ?>
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
        <ul class="cards guilds guild-leaderboard-top5">
            <?php foreach($top_guilds as $loop_index => $lg){ ?>
                <?php include (TEMPLATEPATH . '/guild-leaderboard.php'); ?>
            <?php } ?>
        </ul>

        <?php if(!empty($rest_guilds)){ ?>
        <div class="guild-leaderboard-table-wrap br-table-wrap" id="guild-leaderboard-table">
            <table class="br-table guild-leaderboard-table">
                <thead>
                    <tr>
                        <th class="text-center"><?= __("#","bluerabbit"); ?></th>
                        <th></th>
                        <th><?= __("Guild","bluerabbit"); ?></th>
                        <th class="text-center"><?= __("Members","bluerabbit"); ?></th>
                        <th class="text-center"><?= __("Level","bluerabbit"); ?></th>
                        <th class="text-center"><?= __("XP","bluerabbit"); ?></th>
                        <th class="text-center"><?= __("VC","bluerabbit"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rest_guilds_page as $row_index => $lg){
                        $loop_index = 5 + (($guild_page - 1) * $guild_per_page) + $row_index;
                    ?>
                        <?php include (TEMPLATEPATH . '/guild-leaderboard-row.php'); ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if($guild_total_pages > 1){ ?>
        <div class="guild-leaderboard-pagination">
            <?php for($p=1; $p<=$guild_total_pages; $p++){ ?>
                <a class="guild-lb-page<?= $p == $guild_page ? ' active' : ''; ?>" href="<?= esc_url(add_query_arg('guild_page', $p)); ?>#guild-leaderboard-table"><?= $p; ?></a>
            <?php } ?>
        </div>
        <?php } ?>
        <?php } ?>
    </div>
    <?php } ?>

    <div class="overlay-layer guild-roster-overlay" id="guild-roster-overlay"></div>
    <input type="hidden" id="guild_roster_nonce" value="<?= wp_create_nonce('br_guild_roster_nonce'); ?>"/>
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
		<br class="clear">
		<?php if ($isGM){ ?> <input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>"/> <?php } ?>
	</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
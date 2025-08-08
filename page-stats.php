<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($isGM || $isNPC || $isAdmin){ ?>
	<?php 
		$players = $wpdb->get_results("
			SELECT 
			a.*
			FROM {$wpdb->prefix}br_player_adventure a
			WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' AND a.player_adventure_role='player' AND a.player_level
		");
		$achievements = $wpdb->get_results("											   
			SELECT 
			achievements.*, players.player_id, COUNT(players.player_id) AS earned_achievements
			FROM {$wpdb->prefix}br_achievements achievements
			LEFT JOIN {$wpdb->prefix}br_player_achievement players ON achievements.achievement_id=players.achievement_id
			WHERE achievements.adventure_id=$adventure->adventure_id AND achievements.achievement_status='publish' GROUP BY achievements.achievement_id
		");

		$quests = $wpdb->get_results("											   
			SELECT 
			quests.*, players.player_id, COUNT(players.player_id) AS finished_quests
			FROM {$wpdb->prefix}br_quests quests
			LEFT JOIN {$wpdb->prefix}br_player_posts players ON quests.quest_id=players.quest_id
			LEFT JOIN {$wpdb->prefix}br_player_adventure pa ON players.player_id=pa.player_id AND pa.player_level
			WHERE quests.adventure_id=$adventure->adventure_id AND quests.quest_status='publish' AND (quests.quest_type IN ('quest', 'challenge', 'survey') )

			GROUP BY quests.quest_id
			ORDER BY quests.quest_order
		");
		$max = (count($players)*count($quests))+(count($players)*count($achievements));
		$max_quests = (count($players)*count($quests));
		$max_achievements = (count($players)*count($achievements));
		$finished =0;
		$finished_quests = 0;$finished_achievements=0;

		$totalXP = 0; 
		$totalBLOO = 0; 
		foreach($players as $p){
			$totalXP+=$p->player_xp;
			$totalBLOO+=$p->player_bloo;
		}

	?>
	<div class="container boxed max-w-1200">
		<div class="body-ui w-full white-bg">
			<div class="highlight padding-10 white-bg text-center">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  icon-lg teal-bg-400"><span class="icon icon-skill"></span></span>
					<span class="icon-content">
						<h1 class="line font _24 w900 kerning-3 uppercase">
							<?php _e("Adventure Stats","bluerabbit"); ?>
						</h1>
						<span class="line font _14 w300">
							<?= __("Total Possible Activities","bluerabbit").": <strong>$max</strong>"; ?>
						</span>
						<span class="line font _14 w300 grey-500">
							<?= __("(Enrolled players) x (Number of activities)","bluerabbit"); ?>
							<?= count($players)." x ". count($quests)." x ".count($achievements); ?>
						</span>
					</span>
				</span>
			</div>
			<h1 class="font _30 w900">Total XP: <?=$totalXP;?></h1>
			<h1 class="font _30 w900">Total BLOO: <?=$totalBLOO;?></h1>
			<div class="content w-full">
				<table class="table">
					<thead>
						<tr>
							<td><?php _e("Type", "bluerabbit"); ?></td>
							<td><?php _e("Title", "bluerabbit"); ?></td>
							<td><?php _e("Value", "bluerabbit"); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach($quests as $q){ ?>
							<?php $finished += $q->finished_quests; ?>
							<?php $finished_quests += $q->finished_quests; ?>
							<tr>
								<td><span class="icon icon-<?=$q->quest_type; ?>"></span></td>
								<td><?php echo $q->quest_title; ?></td>
								<td><?php echo $q->finished_quests; ?></td>
							</tr>
						<?php } ?>
						<?php foreach($achievements as $a){ ?>
							<?php $finished += $a->earned_achievements; ?>
							<?php $finished_achievements += $a->earned_achievements; ?>
							<tr class="purple-bg-50">
								<td><span class="icon icon-achievement"></span></td>
								<td><?php echo $a->achievement_name; ?></td>
								<td><?php echo $a->earned_achievements; ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="highlight padding-10 white-bg text-center">
				<span class="inline-block padding-10 font _24 green-bg-400 w900"><?= "$finished / $max".__("Finished activities","bluerabbit"); ?></span>
				<span class="inline-block padding-10 font _24 blue-bg-400"><?= "$finished_quests / $max_quests ".__("Finished Quests","bluerabbit"); ?></span>
				<span class="inline-block padding-10 font _24 purple-bg-400"><?= "$finished_achievements / $max_achievements ".__("Earned Achievements","bluerabbit"); ?></span>
			</div>
		</div>
	</div>
	<input type="hidden" id="grade_nonce" value="<?php echo wp_create_nonce('br_grade_nonce'); ?>"/>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404/"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

		<ul class="question-options">
			<?php 
			$oCount = 0;
			$guilds = $wpdb->get_results("
				SELECT 
				a.*, b.player_id
				FROM {$wpdb->prefix}br_guilds a
				LEFT JOIN {$wpdb->prefix}br_player_guild b
				ON a.guild_id = b.guild_id AND b.player_id=$current_user->ID
				WHERE a.adventure_id=$adventure->adventure_id AND a.guild_status='publish' AND a.guild_group='{$q['survey_question_display']}'
				GROUP BY a.guild_id ORDER BY a.guild_name ASC
			");
			foreach($guilds as $oKey=>$t) {
				$oCount ++;
				if($t->player_id != $current_user->ID || $isAdmin || $isGM || $isNPC){ 
					include (TEMPLATEPATH . '/survey-question-guild-vote-option.php');
				}
			}
			?>
		</ul>

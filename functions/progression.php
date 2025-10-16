<?php
function getPlayerProgress($adventure_id, $uID){
	global $wpdb;
	$user=getPlayerData($uID);
	$data = array();
	$data['success']=false;
	$config = getSysConfig();
	if($user){
		$adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date('Y-m-d H:i:s');
		$after = $adventure->adventure_progression_type;
		$errors = array();

		$myItems = getMyItems($adventure->adventure_id, $user->player_id);
		$myXP = 0;
		$myEP = 0;
		$myLevel = 1;
		$myBloo = 0;
		$item_rewards=array();
		$fqs=array();
		$reqs = array(
			'quests' => array(),
			'items' => array(),
			'achievements' =>array()
		);
		$reqs_ids = array(
			'items'=>array(),
			'achievements'=>array(),
		);
		$gpa = array();

		$achievements = $wpdb->get_results("SELECT
		achievements.achievement_id, achievements.achievement_name, achievements.achievement_badge, achievements.achievement_color, achievements.adventure_id, 
        achievements.achievement_xp, achievements.achievement_bloo, achievements.achievement_ep, players.player_id
		FROM {$wpdb->prefix}br_achievements achievements
		JOIN {$wpdb->prefix}br_player_achievement players
		ON achievements.achievement_id = players.achievement_id AND players.player_id=$user->player_id 
		WHERE players.adventure_id=$adv_child_id  AND players.player_id=$user->player_id AND achievements.achievement_status='publish'");

		$achievements_ids = array();
		if($achievements){
			foreach($achievements as $key=>$a){
				$achievements_ids[]=$a->achievement_id;
				$myXP += $a->achievement_xp;
				$myBloo += $a->achievement_bloo;
				$myEP += $a->achievement_ep;
			}
			$achievements_ids_str = " OR quests.achievement_id IN (".implode(",",$achievements_ids).") ";
		}else{
			$achievements_ids_str = "";
		}
		
        $pposts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_player_posts WHERE player_id=$user->player_id AND adventure_id=$adv_child_id AND pp_status='publish'");
		$player_work = [];
        foreach($pposts as $ppKey=>$pp){
            $player_work[$pp->quest_id] = $pp;
        }
  
        $quests = $wpdb->get_results("SELECT
        quests.*,
        achievements.achievement_color, achievements.achievement_name
        FROM {$wpdb->prefix}br_quests quests
        LEFT JOIN {$wpdb->prefix}br_achievements achievements
        ON quests.achievement_id = achievements.achievement_id AND achievements.achievement_status='publish'
        WHERE quests.adventure_id=$adv_parent_id AND (quests.quest_status='publish' OR quests.quest_status='hidden') AND (quests.achievement_id='' OR quests.achievement_id=NULL $achievements_ids_str ) ORDER BY quests.quest_color, quests.quest_order, quests.mech_level, quests.mech_start_date, quests.quest_title, quests.quest_id");

		$all_quests = $wpdb->get_results("SELECT
        quests.* FROM {$wpdb->prefix}br_quests quests
        WHERE quests.adventure_id=$adv_parent_id AND quests.quest_status='publish' OR quests.quest_status='hidden'
		ORDER BY quests.quest_color, quests.quest_order, quests.mech_level, quests.mech_start_date, quests.quest_title, quests.quest_id");

		$survey_questions = $wpdb->get_results("SELECT questions.*
		FROM {$wpdb->prefix}br_survey_questions questions
		JOIN  {$wpdb->prefix}br_quests surveys
		ON surveys.quest_id = questions.survey_id AND surveys.quest_status='publish'
		WHERE surveys.adventure_id=$adv_parent_id AND questions.survey_question_status='publish' GROUP BY questions.survey_question_id");

		$survey_answers = $wpdb->get_results("SELECT answers.*
		FROM {$wpdb->prefix}br_survey_answers answers
		JOIN  {$wpdb->prefix}br_quests surveys
		ON surveys.quest_id = answers.survey_id AND surveys.quest_status='publish'
		JOIN  {$wpdb->prefix}br_survey_questions questions
		ON surveys.quest_id = questions.survey_id AND questions.survey_question_status='publish'
		WHERE answers.adventure_id=$adv_child_id AND answers.player_id=$user->player_id AND (answers.survey_option_id > 0 OR answers.survey_answer_value!='') GROUP BY answers.survey_question_id");

		$surveys = array();
		foreach($survey_questions as $sq){
			$surveys['s'.$sq->survey_id]['questions'][]=$sq;
		}
		foreach($survey_answers as $sa){
			$surveys['s'.$sa->survey_id]['answers'][]=$sa;
		}

		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_attempts
		 WHERE player_id=$user->player_id AND adventure_id=$adv_child_id  AND attempt_status !='trash'");

		$player_attempts = [];

		foreach($attempts as $att){
			$player_attempts[$att->quest_id] = $att;;
		}




		$requirements = $wpdb->get_results("SELECT
            a.quest_id, a.quest_status, a.quest_guild, a.quest_title,
            b.req_object_id, b.req_type, b.req_object_id,
            c.item_name, c.item_badge,
            e.mech_badge, e.quest_type, e.quest_title, e.achievement_id

            FROM {$wpdb->prefix}br_quests a
            LEFT JOIN {$wpdb->prefix}br_reqs b
            ON a.quest_id = b.quest_id
            LEFT JOIN {$wpdb->prefix}br_items c
            ON c.item_id = b.req_object_id AND b.req_type='item'
            LEFT JOIN {$wpdb->prefix}br_quests e
            ON b.req_object_id = e.quest_id AND b.req_type='quest' AND e.quest_status='publish'

            LEFT JOIN {$wpdb->prefix}br_achievements achievements
            ON b.req_object_id = achievements.achievement_id AND b.req_type='achievement'  AND achievements.achievement_status='publish'
            LEFT JOIN {$wpdb->prefix}br_player_achievement f
            ON achievements.achievement_id = f.achievement_id AND f.player_id=$user->player_id

            WHERE a.adventure_id=$adv_parent_id AND a.quest_status='publish'
            ORDER BY a.quest_id
		");
		foreach($requirements as $r){
			if($r->quest_status == 'publish'){
				if($r->req_type=='quest'){
					$reqs['quests'][$r->quest_id][]=$r;
					$reqs_ids['quests'][$r->quest_id][]=$r->req_object_id;
				}elseif($r->req_type=='item'){
					$reqs['items'][$r->quest_id][]=$r;
					$reqs_ids['items'][$r->quest_id][]=$r->req_object_id;
				}elseif($r->req_type=='achievement'){
					$reqs['achievements'][$r->quest_id][]=$r;
					$reqs_ids['achievements'][$r->quest_id][]=$r->req_object_id;
				}
			}
		}
		$blockers = $wpdb->get_results("SELECT
		a.blocker_date, a.blocker_description, a.blocker_cost, b.player_id
		FROM {$wpdb->prefix}br_blockers a
		JOIN {$wpdb->prefix}br_player_blocker b
		ON a.blocker_id = b.blocker_id
		WHERE a.adventure_id=$adv_child_id AND b.player_id=$user->player_id AND a.blocker_status='publish'");

		$transactions = $wpdb->get_results("
		SELECT trnxs.*
		FROM {$wpdb->prefix}br_transactions trnxs
		LEFT JOIN {$wpdb->prefix}br_blockers blockers
		ON trnxs.object_id = blockers.blocker_id AND trnxs.trnx_type='blocker'
		LEFT JOIN {$wpdb->prefix}br_items items
		ON trnxs.object_id = items.item_id AND (trnxs.trnx_type='consumable' OR trnxs.trnx_type='key')
		LEFT JOIN {$wpdb->prefix}br_quests quests
		ON trnxs.object_id = quests.quest_id AND ( trnxs.trnx_type='deadline' OR  trnxs.trnx_type='unlock' OR trnxs.trnx_type='attempt')
		LEFT JOIN {$wpdb->prefix}br_challenge_attempts attempts
		ON trnxs.object_id = attempts.quest_id AND quests.quest_id = attempts.quest_id AND trnxs.trnx_type='attempt'
		WHERE trnxs.adventure_id=$adv_child_id AND trnxs.trnx_status='publish' AND trnxs.player_id=$user->player_id
		AND (quests.quest_status='publish' OR items.item_status='publish' OR blockers.blocker_status='publish')
		GROUP BY trnxs.trnx_id ORDER BY trnxs.trnx_id
		");


		foreach($quests as $qKey=>$quest){
			if($player_work[$quest->quest_id]->pp_status == 'publish'){
				$pp = $player_work[$quest->quest_id];
				if($pp->pp_grade > 0 && $after == "after" || $after == "before"){
					if(!in_array($quest->quest_id,$fqs)){
						$myEP += $quest->mech_ep;
						$myXP += $quest->mech_xp;
						if($after == "after"){
							$myBloo += ($quest->mech_bloo*$pp->pp_grade/100);
						}else{
							$myBloo += $quest->mech_bloo;
						}
						$fqs[]=$quest->quest_id;
						if($quest->mech_item_reward){
							$item_rewards[]=$quest->mech_item_reward;
						}
						if($pp->pp_grade){
							$gpa[$pp->quest_id] = $pp->pp_grade;
						}
					}
				}
			}
		}

		$debt=0;
		$paid=0;
		$spent=0;
		$totalEarned = $myBloo;
		$items = array();
		$deadlines = array();
		$unlocked = array();
		foreach($blockers as $b){
			$debt+=$b->blocker_cost;
		}
		foreach($transactions as $t){
			if($t->trnx_type == 'blocker'){
				$debt-=$t->trnx_amount;
				$paid++;
			}else if($t->trnx_type == 'attempt'){
				$paid_attempts[]=$t->object_id;
			}else if($t->trnx_type == 'deadline'){
				$deadlines[]=$t->object_id;
			}else if($t->trnx_type == 'unlock'){
				$unlocked[]=$t->object_id;
			}else if($t->trnx_type == 'consumable' || $t->trnx_type == 'key' || $t->trnx_type == 'reward' ||$t->trnx_type == 'use'){
				$items[$t->trnx_type][]=$t->object_id;
			}
			$myBloo -= $t->trnx_amount;
			$spent += $t->trnx_amount;
		}
		$tnl = 1000;
		$added = 0;
		for($l=1;$l<1000;$l++){
			$added += $l*1000;
			if(($added-1) < $myXP){
				$myLevel = $l+1;
				$tnl = $added + $myLevel*1000;
			}
		}

		$maxEP = 100+(($myLevel*($myLevel+1)/2)*20);
		$energy = $wpdb->get_results("SELECT SUM(energy) AS energy_spent FROM {$wpdb->prefix}br_player_energy_log WHERE player_id=$user->player_id AND adventure_id=$adventure_id");
		$myEP += $energy[0]->energy_spent;
		$epDiff = 0;
		if($myEP > $maxEP){
			$epDiff = $maxEP-$myEP;
			$insert = "INSERT INTO {$wpdb->prefix}br_player_energy_log (`adventure_id`, `player_id`,`energy`, `enc_option_content`,`timestamp`) VALUES (%d,%d,%d, %s, %s)";
			$insert = $wpdb->query($wpdb->prepare($insert, $adventure_id, $user->player_id, $epDiff, 'EP Cap Difference', $today));
		}
		
		if($myEP < 0){ 
			$myEP=0; 
		}else{
			$myEP += $epDiff;
		}

		$totalgpa = $gpa ? round(array_sum($gpa)/count($gpa)) : 0;
		$last_level_xp = ($myLevel*($myLevel-1))/2 * 1000;
		$updatePlayerSQL = "UPDATE {$wpdb->prefix}br_player_adventure SET player_xp=%d, player_bloo=%d, player_ep=%d, player_level=%d , player_gpa=%d WHERE player_id=%d AND adventure_id=%d ";
		$updatePlayer=$wpdb->query($wpdb->prepare($updatePlayerSQL,$myXP,$myBloo,$myEP, $myLevel, $totalgpa, $user->player_id,$adventure_id));

		$data['player']['xp']=$myXP;
		$data['player']['bloo']=$myBloo;
		$data['player']['ep']=$myEP;
		$data['player']['level']=$myLevel;
		$data['player']['xp_curr_level']=$myXP-$last_level_xp;
		$data['player']['tnl']=$tnl-$myXP;
		$data['player']['debt']=$debt;
		$data['player']['spent']=$spent;
		$data['player']['totalEarned']=$totalEarned;
		$data['player']['paid_blockers']=$paid;
		$data['player']['items']=$items;
		$data['player']['fqs']=$fqs;
		$data['player']['deadlines']=$deadlines;
		$data['player']['unlocks']=$unlocked;
		$data['player']['gpa']=$totalgpa;
		$data['reqs']=$reqs;
		$data['reqs_ids']=$reqs_ids;
		$data['achievements']=$achievements;
		$data['achievements_ids']=$achievements_ids;

		$data['all_quests']=$all_quests;
		$data['quests']=$quests;
		$data['items']=$myItems;
		$data['attempts']=$attempts;
		$data['item_rewards']=$item_rewards;
		$data['blockers']=$blockers;
		$data['guildwork']= isset($guildwork) ? $guildwork : "";
		$data['debug']=isset($debugQuery) ? $debugQuery : "";
	}else{
		$data['debug']='Player not found';
	}
	return $data;
}

function getRequirements($quest_id, $adv_id){
	global $wpdb; $current_user = wp_get_current_user();
    $data = array();
	$quest = getQuest($quest_id);
	if($quest){
		$adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adv_id AND adventure_status='publish'");
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

		////////// GET REQUIREMENTS ///////////
		$requirements = $wpdb->get_results("SELECT
			reqs.*, 
			quests.quest_id, quests.quest_title, quests.mech_badge, quests.quest_type,
			items.item_name, items.item_badge, items.item_type,
			achievements.achievement_id, achievements.achievement_name, achievements.achievement_badge, achievements.achievement_color
			FROM {$wpdb->prefix}br_reqs reqs
			LEFT JOIN {$wpdb->prefix}br_quests quests
			ON reqs.req_object_id = quests.quest_id AND reqs.req_type='quest' AND quests.quest_status='publish'
			LEFT JOIN {$wpdb->prefix}br_items items
			ON reqs.req_object_id = items.item_id AND reqs.req_type='item' AND items.item_status='publish'
			LEFT JOIN {$wpdb->prefix}br_achievements achievements
			ON reqs.req_object_id = achievements.achievement_id AND reqs.req_type='achievement' AND achievements.achievement_status='publish'
			WHERE reqs.adventure_id=$adv_parent_id AND reqs.quest_id=$quest->quest_id
		");

		$p_posts = getMyQuests($adv_child_id); // returns as array with IDs
		$p_achievements = getMyAchievements($adv_child_id); // returns IDs in one Array
		$p_items = getMyItems($adv_child_id); // returns as array with types [key,consumable,reward]

		$completed_quests = [];
		$purchased_items = [];
		$earned_achievements = [];
		foreach($requirements as $r){
			if($r->req_type == 'quest' && in_array($r->req_object_id, $p_posts)){
				$completed_quests[$r->req_object_id]=$r;
			}elseif($r->req_type == 'item' && (in_array($r->req_object_id,$p_items['ids']['key']) || in_array($r->req_object_id,$p_items['ids']['consumable']))){
				$purchased_items[$r->req_object_id]=$r;
			}elseif($r->req_type == 'achievement' && in_array($r->req_object_id,$p_achievements)){
				$earned_achievements[$r->req_object_id]=$r;
			}
		}
		$data['myPosts'] = $p_posts;
		$data['requirements'] = $requirements;
		$data['quests'] = $completed_quests;
		$data['items'] = $purchased_items;
		$data['achievements'] = $earned_achievements;
		$total = count($completed_quests)+count($purchased_items)+count($earned_achievements);

		$data['total'] = $total;
		$data['total_requirements'] = count($requirements);
		if($total >= count($requirements)){
			$data['success']=true;
		}else{
			$data['success']=false;
		}
		return $data;
	}else{
		return false;
	}
}



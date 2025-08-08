<?php	include (TEMPLATEPATH . '/header.php'); ?>
<div class="dashboard">
<div class="dashboard-content  white-bg padding-10">
<h1>DEV</h1>
	
	
	<?php 
	
	function player_hash(){
		global $wpdb; $current_user = wp_get_current_user();
		$players = $wpdb->get_results("SELECT * from {$wpdb->prefix}br_players");
		foreach( $players as $p){
			$rand_hash = random_str(30,'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_');
			$update = $wpdb->update(
				$wpdb->prefix.'br_players',
				array(
					'player_secret_code' => $rand_hash,
				),
				array(
					'player_id' => $p->player_id,
				)
			);
			print_r($wpdb->insert_id);
		}
	}
	player_hash();

	
	
/*
	function createSurvey($data){
		global $wpdb; $current_user = wp_get_current_user();
		$sql = "INSERT INTO {$wpdb->prefix}br_quests (
			`quest_author`, `adventure_id`,`achievement_id`,`mech_level`,`mech_xp`,`mech_bloo`,`mech_ep`,`quest_order`,
			`quest_title`,`quest_content`,`quest_success_message`,
			`quest_type`, `quest_color`, `quest_icon`,
			`mech_badge`,
			`mech_deadline`,`mech_start_date`
		)
		VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
		
		$quest_author = $current_user->ID;
		$adv_id = $data['adventure_id'];
		$ach_id = $data['achievement_id'];
		$level = 1;
		$xp = 1250;
		$bloo = 125;
		$ep = 65;
		$order = $data['order'];
		$quest_title = $data['session_title'];
		$quest_content = wp_trim_words($data['session_description'],50);
		$quest_success_message = $data['success_message'];
		$quest_type = $data['quest_type'];
		$quest_color = $data['quest_color'];
		$quest_icon = $data['quest_icon'];
		$mech_badge = $data['speaker_picture'];
		$mech_start_date = $data['session_start'];
		$mech_deadline = date('Y-m-d H:i:s', strtotime($data['session_end']. ' + 1 hour'));
		
		$sql = $wpdb->prepare(
			$sql, $quest_author, $adv_id, $ach_id, $level, $xp, $bloo, $ep, $order, $quest_title, $quest_content, $quest_success_message, $quest_type, $quest_color, $quest_icon, $mech_badge, $mech_deadline, $mech_start_date
		);
		$wpdb->query($sql);
		if($wpdb->insert_id){
			$new_survey_id = $wpdb->insert_id;
			/// Insert Rating question
			$rating_question_text = __("Please rate the talk ","bluerabbit")." <strong>".$data['session_title']."</strong>";
			$open_question_text = __("What comments can you leave for","bluerabbit")." ".$data['speaker'];
			$questions_query = "INSERT INTO {$wpdb->prefix}br_survey_questions 
			(survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_range) VALUES 
			(%d, %s, %s, %s, %d), (%d, %s, %s, %s, %d)";
			$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $new_survey_id, $rating_question_text, $data['speaker_picture'], 'rating', 5, $new_survey_id, $open_question_text, $data['speaker_picture'], 'open', 0 ));
			return $data['session_title']." inserted";
		}else{
			return $data['session_title']." ERROR";
			
		}
	}
	$current_user = wp_get_current_user();
	if($current_user->ID == 1){
		$sessions = getSessions(5, 'publish');
		foreach ($sessions as $key=>$ss){
			// 1) Create Survey 

			$survey_data['adventure_id'] = 5;
			$survey_data['achievement_id'] = 74;
			$survey_data['order'] = $key+1;
			$survey_data['session_title'] = $ss->session_title;
			$survey_data['session_description'] = $ss->session_description;
			$survey_data['speaker'] = $ss->speaker_first_name." ".$ss->speaker_last_name;
			$survey_data['success_message'] = "<h1>".__("Thank you for your feedback!","bluerabbit")."</h1>";
			$survey_data['success_message'] .= "<h2>".__("We will make sure","bluerabbit")." ".$ss->speaker_first_name." ".$ss->speaker_last_name." ".__("receives your comments","bluerabbit")."</h2>";
			$survey_data['quest_type'] = 'survey';
			$survey_data['quest_color'] = 'amber';
			$survey_data['quest_icon'] = 'survey';
			$survey_data['speaker_picture'] = $ss->speaker_picture;
			$survey_data['session_start'] = $ss->session_start;
			$survey_data['session_end'] = $ss->session_end;

			$new_survey = createSurvey($survey_data);
			echo "<h3>$new_survey</h3>";

		}
		
	}
*/
	
	
	?>
	
	
	
	
	
</div>
</div>

<?php include (TEMPLATEPATH . '/footer.php'); ?>


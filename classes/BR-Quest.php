<?php
class BR_Quest {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function reorder(){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$data['success'] = false;
		$adventure_id = $_POST['adventure_id'];
		$the_order = $_POST['the_order'];
		$count = 0;
		foreach($the_order as $k=>$id){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_order=%d WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$k,$id,$adventure_id,$id);
			$result = $wpdb->query($sql);
		}
		if($k+1 >= count($the_order)){
			$data['success'] = true;
			$data['message'] = "<h1>".__("Reordered","bluerabbit")."</h1>";
			$data['location'] = "reload";
			BR_Activity::instance()->logActivity($adventure_id,'reoredered','journey',serialize($the_order));
		}else{
			$data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
			$data['message'] .= "<h4>".$k."</h4>";
		}
		echo json_encode($data);
		die();
    }

    public function failQuest(){
		global $wpdb;
		$quest_id=$_POST['quest_id'];
		$adventure_id=$_POST['adventure_id'];
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id AND quest_status='publish'");

		$adventure = BR_Adventure::instance()->getAdventure($adventure_id);
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;


		BR_Activity::instance()->logActivity($adv_child_id, 'failed', 'quest','', $quest->quest_id);

		$theFile = (get_template_directory()."/failed-quest.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
    }

    public function updateQuest(){
		global $wpdb; $current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$data = array();
		$errors = array();
		if (wp_verify_nonce($_POST['nonce'], 'br_update_quest_nonce')) {
			$config = BR_Config::instance()->getSysConfig();
			$quest_data = isset($_POST['quest_data']) ? $_POST['quest_data'] : $parent_quest_data;

			$adventure_id = $quest_data['adventure_id'];
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
			$quest_id = $quest_data['quest_id'];
			$achievement_id = $quest_data['achievement_id'];
			$today = date("Y-m-d H:i:s");
			$quest_status = $quest_data['quest_status'];
			$quest_relevance = $quest_data['quest_relevance'];
			$quest_title = stripslashes_deep($quest_data['quest_title']);
			$quest_content = stripslashes_deep($quest_data['quest_content']);
			$quest_success_message = stripslashes_deep($quest_data['quest_success_message']);
			$quest_type = $quest_data['quest_type'];
			$quest_guild = 0;
			// Only present when the page still renders the old inline requirement grids
			// (Mission/Survey builders); Quest's builder now saves reqs separately via
			// the Conditions panel (saveQuestConditions), so these stay null there and
			// the reqs delete/reinsert below is skipped entirely - it must never run
			// unconditionally or it would wipe out reqs set through the new panel.
			$quest_reqs = $quest_data['quest_reqs'] ?? null;
			$quest_achievement_reqs = $quest_data['quest_achievement_reqs'] ?? null;
			$quest_libs = $quest_data['quest_libs'];
			$quest_item_required = $quest_data['quest_item_required'] ?? null;
			$quest_color = $quest_data['quest_color'];
			$quest_order = $quest_data['quest_order'];
			$quest_icon = $quest_data['quest_icon'];
			$quest_mechs = $quest_data['quest_mechs'];
			$quest_author = $current_user->ID;
			$quest_secondary_headline = stripslashes_deep($quest_data['quest_secondary_headline']);
			$quest_style = $quest_data['quest_style'];
			$quest_objectives = $quest_data['quest_objectives'];
			$tabi_id = $quest_data['tabi_id'];
			$mech_optional = intval($quest_data['quest_mechs']['mech_optional']);
			$mech_validate = intval($quest_data['quest_mechs']['mech_validate']);
			$steps_order = $quest_data['steps_order'];

			if(!$quest_title){
				$errors['title_length'] = __("Please add a quest title","bluerabbit");
			}
			if($quest_type=='challenge'){
				if($quest_mechs['mech_questions_to_display'] < 1){
					$quest_mechs['mech_questions_to_display']=1;
				}
				if($quest_mechs['mech_answers_to_win'] < 1){
					$quest_mechs['mech_answers_to_win']=1;
				}
				if($quest_data['quest_questions'] < $quest_mechs['mech_questions_to_display']){
					$quest_mechs['mech_questions_to_display'] = $quest_data['quest_questions'];
				}
				if($quest_mechs['mech_answers_to_win'] > $quest_mechs['mech_questions_to_display']){
					$quest_mechs['mech_answers_to_win'] = $quest_mechs['mech_questions_to_display'];
				}
			}
			if($quest_mechs['mech_start_date']){
				$quest_mechs['mech_start_date']=date('Y-m-d H:i:s',strtotime($quest_mechs['mech_start_date']));
			}else{
				$quest_mechs['mech_start_date']='0000-00-00 00:00:00';
			}
			if($quest_mechs['mech_deadline']){
				$quest_mechs['mech_deadline']=date('Y-m-d H:i:s',strtotime($quest_mechs['mech_deadline']));
			}else{
				$quest_mechs['mech_deadline']='0000-00-00 00:00:00';
			}
			if($quest_type!='challenge'){
				$quest_mechs['mech_max_attempts']=$quest_mechs['mech_free_attempts']=$quest_mechs['mech_attempt_cost']=0;
				$quest_mechs['mech_questions_to_display']=$quest_mechs['mech_answers_to_win']=$quest_mechs['mech_time_limit']=0;
			}
			if(!$errors){
				$quest_qr_token = BR_Utils::instance()->random_str(32, '0123456789abcdefghijklmnopqrstuvwxyz');
				$sql = "INSERT INTO {$wpdb->prefix}br_quests (
					`quest_id`,
					`quest_author`,
					`adventure_id`,`achievement_id`,
					`quest_status`,
					`quest_title`,`quest_content`,`quest_success_message`,`quest_type`,`quest_secondary_headline`,	`quest_style`,	`quest_color`,	`quest_icon`,
					`quest_date_posted`, `quest_date_modified`,
					`quest_relevance`,
					`mech_level`,`mech_xp`,`mech_bloo`,`mech_ep`,`mech_badge`,
					`mech_deadline`,`mech_start_date`,`mech_deadline_cost`,`mech_unlock_cost`,
					`mech_min_words`,`mech_min_links`,`mech_min_images`,
					`mech_max_attempts`,`mech_free_attempts`,`mech_attempt_cost`,`mech_questions_to_display`,`mech_answers_to_win`,`mech_time_limit`,`mech_show_answers`,
					`mech_item_reward`,`mech_achievement_reward`,
					`quest_order`, `quest_guild`, `tabi_id`, `mech_optional`, `mech_validate`,
					`quest_qr_token`
				)
				VALUES (%d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,  %s, %d, %d, %d, %d, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s)

				ON DUPLICATE KEY UPDATE
					`quest_author`=%d, `adventure_id`=%d, `achievement_id`=%d,
					`quest_status`=%s, `quest_title`=%s, `quest_content`=%s, `quest_success_message`=%s, `quest_type`=%s,`quest_secondary_headline`=%s, `quest_style`=%s,  `quest_color`=%s,  `quest_icon`=%s,
					`quest_date_modified`=%s,
					`quest_relevance`=%s,
					`mech_level`=%d, `mech_xp`=%d, `mech_bloo`=%d, `mech_ep`=%d, `mech_badge`=%s,
					`mech_deadline`=%s, `mech_start_date`=%s, `mech_deadline_cost`=%d, `mech_unlock_cost`=%d,
					`mech_min_words`=%d, `mech_min_links`=%d, `mech_min_images`=%d,
					`mech_max_attempts`=%d, `mech_free_attempts`=%d, `mech_attempt_cost`=%d, `mech_questions_to_display`=%d, `mech_answers_to_win`=%d, `mech_time_limit`=%d, `mech_show_answers`=%d,
					`mech_item_reward`=%d, `mech_achievement_reward`=%d,
					`quest_order`=%d, `quest_guild`=%d, `tabi_id`=%d, `mech_optional`=%d, `mech_validate`=%d,
					`quest_qr_token` = COALESCE(`quest_qr_token`, VALUES(`quest_qr_token`))
				";
			$sql = $wpdb->prepare(

					$sql,

					$quest_id,
					$quest_author,
					$adventure_id, $achievement_id,
					$quest_status,
					$quest_title, $quest_content, $quest_success_message, $quest_type, $quest_secondary_headline, $quest_style, $quest_color, $quest_icon,
					$today, $today,
					$quest_relevance,
					$quest_mechs['mech_level'], $quest_mechs['mech_xp'], $quest_mechs['mech_bloo'],  $quest_mechs['mech_ep'], $quest_mechs['mech_badge'],
					$quest_mechs['mech_deadline'], $quest_mechs['mech_start_date'], $quest_mechs['mech_deadline_cost'], $quest_mechs['mech_unlock_cost'],
					$quest_mechs['mech_min_words'], $quest_mechs['mech_min_links'], $quest_mechs['mech_min_images'],
					$quest_mechs['mech_max_attempts'], $quest_mechs['mech_free_attempts'], $quest_mechs['mech_attempt_cost'], $quest_mechs['mech_questions_to_display'], $quest_mechs['mech_answers_to_win'], $quest_mechs['mech_time_limit'], $quest_mechs['mech_show_answers'],
					$quest_mechs['mech_item_reward'], $quest_mechs['mech_achievement_reward'],
					$quest_order, $quest_guild, $tabi_id, $mech_optional, $mech_validate,
					$quest_qr_token,

					$quest_author,
					$adventure_id, $achievement_id,
					$quest_status,
					$quest_title, $quest_content, $quest_success_message, $quest_type, $quest_secondary_headline, $quest_style, $quest_color, $quest_icon,
					$today,
					$quest_relevance,
					$quest_mechs['mech_level'], $quest_mechs['mech_xp'], $quest_mechs['mech_bloo'], $quest_mechs['mech_ep'], $quest_mechs['mech_badge'],
					$quest_mechs['mech_deadline'], $quest_mechs['mech_start_date'], $quest_mechs['mech_deadline_cost'], $quest_mechs['mech_unlock_cost'],
					$quest_mechs['mech_min_words'], $quest_mechs['mech_min_links'], $quest_mechs['mech_min_images'],
					$quest_mechs['mech_max_attempts'], $quest_mechs['mech_free_attempts'], $quest_mechs['mech_attempt_cost'], $quest_mechs['mech_questions_to_display'], $quest_mechs['mech_answers_to_win'], $quest_mechs['mech_time_limit'], $quest_mechs['mech_show_answers'],
					$quest_mechs['mech_item_reward'], $quest_mechs['mech_achievement_reward'],
					$quest_order, $quest_guild, $tabi_id, $mech_optional, $mech_validate
				);
				$wpdb->query($sql);
				if($wpdb->insert_id){
					$quest_id = $wpdb->insert_id;
					// Skipped entirely when the page no longer sends these keys at all
					// (Quest's builder - reqs are saved separately via the Conditions
					// panel now). Still runs as before for Mission/Survey, which still
					// render the old inline grids and rely on this replace-on-save.
					if($quest_reqs !== null || $quest_achievement_reqs !== null || $quest_item_required !== null){
					$DELETE_query = "DELETE FROM {$wpdb->prefix}br_reqs WHERE quest_id=%d AND adventure_id=%d;";
					$wpdb->query( $wpdb->prepare("$DELETE_query ", $quest_id, $adventure_id));
					if($quest_reqs || $quest_item_required || $quest_achievement_reqs){
						$values = array();
						$place_holders = array();
						$reqs_query = "INSERT INTO {$wpdb->prefix}br_reqs (quest_id, adventure_id, req_object_id, req_type) VALUES ";
						if($quest_reqs){
							foreach($quest_reqs as $key => $q) {
								 array_push($values, $quest_id, $adventure_id, $q, 'quest');
								 $place_holders[] = "(%d, %d, %d, %s)";
							}
						}
						if($quest_achievement_reqs){
							foreach($quest_achievement_reqs as $key => $a) {
								 array_push($values, $quest_id, $adventure_id, $a, 'achievement');
								 $place_holders[] = "(%d, %d, %d, %s)";
							}
						}
						if($quest_item_required){
							 array_push($values, $quest_id, $adventure_id, $quest_item_required, 'item');
							 $place_holders[] = "(%d, %d, %d, %s)";
						}
						$reqs_query .= implode(', ', $place_holders);
						$req_insert = $wpdb->query( $wpdb->prepare("$reqs_query ", $values));

					}
					}
					if($steps_order){
						BR_Step::instance()->reorderStepProcess($steps_order);
					}
					$data['success']=true;
					if(!$quest_data['quest_id']){
						$data['location']=get_bloginfo('url')."/new-$quest_type/?adventure_id=$adventure_id&questID=$quest_id";
						BR_Activity::instance()->logActivity($adventure_id,'add','quest','',$quest_id);
					}else{
						BR_Activity::instance()->logActivity($adventure_id,'update','quest','',$quest_id);
					}
					$data['message'] .= '<h1><strong>'.$quest_title.'</strong></h1> <h4><strong>'.__("Quest Inserted Successfully!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';

					if($adventure->adventure_type=='template'){
						//Check for children and update them

						$children_update = "UPDATE {$wpdb->prefix}br_quests SET
							`quest_status`=%s, `quest_title`=%s, `quest_content`=%s, `quest_success_message`=%s, `quest_secondary_headline`=%s, `quest_color`=%s,  `quest_icon`=%s,
							`quest_date_modified`=%s, `quest_relevance`=%s,
							`mech_level`=%d, `mech_xp`=%d, `mech_bloo`=%d, `mech_ep`=%d, `mech_badge`=%s,
							`mech_deadline`=%s, `mech_start_date`=%s, `mech_deadline_cost`=%d, `mech_unlock_cost`=%d,
							`mech_min_words`=%d, `mech_min_links`=%d, `mech_min_images`=%d,
							`mech_max_attempts`=%d, `mech_free_attempts`=%d, `mech_attempt_cost`=%d, `mech_questions_to_display`=%d, `mech_answers_to_win`=%d, `mech_time_limit`=%d, `mech_show_answers`=%d
							WHERE `quest_parent`=$quest_id AND `quest_id` != $quest_id
						";
						$children_update = $wpdb->prepare($children_update, $quest_status, $quest_title, $quest_content, $quest_success_message, $quest_secondary_headline, $quest_color, $quest_icon, $today, $quest_relevance,
						$quest_mechs['mech_level'], $quest_mechs['mech_xp'], $quest_mechs['mech_bloo'], $quest_mechs['mech_ep'], $quest_mechs['mech_badge'],
						$quest_mechs['mech_deadline'], $quest_mechs['mech_start_date'], $quest_mechs['mech_deadline_cost'], $quest_mechs['mech_unlock_cost'],
						$quest_mechs['mech_min_words'], $quest_mechs['mech_min_links'], $quest_mechs['mech_min_images'],
						$quest_mechs['mech_max_attempts'], $quest_mechs['mech_free_attempts'], $quest_mechs['mech_attempt_cost'], $quest_mechs['mech_questions_to_display'], $quest_mechs['mech_answers_to_win'], $quest_mechs['mech_time_limit'], $quest_mechs['mech_show_answers']);
						$wpdb->query($children_update);
						BR_Activity::instance()->logActivity($adventure_id,'update','quest-children','',$quest_id);
					}
				}else{
					$data['message'] = '<h1><strong>'.$quest_title.'</strong></h1> <h4><strong>'.__("Data Base Error. Can't insert/update quest","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
					$data['message'].="<br><br><br>";
				}
			}else{
				$data['message'] = '<span class="icon icon-xl icon-warning"></span><h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Please Fix the following errors","bluerabbit").'</strong></h4>';
				foreach($errors as $e){
					$data['message'].="<h3>$e</h3>";
				}
			}
		}else{
			$data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();

    }

    public function rateQuest(){
		global $wpdb; $current_user = wp_get_current_user();

		$data = array();
		$errors = array();
		$quest_id = $_POST['quest_id'];
		$rating = $_POST['rating'];
		$sql = "UPDATE {$wpdb->prefix}br_player_posts SET pp_quest_rating=%d WHERE player_id=%d AND quest_id=%d";
		$sql = $wpdb->query( $wpdb->prepare($sql, $rating, $current_user->ID, $quest_id));
		$stars = "";
		for($i=0;$i<$rating;$i++){
			$stars .='<span class="br-icon-btn br-icon-btn-amber"><span class="icon icon-star"></span></span>';
		}
		BR_Activity::instance()->logActivity($adventure_id,'rated','quest','',$quest_id);
		$data['message'] = '<h1><strong>'.__("Rating updated!","bluerabbit").'</strong></h1>'.$stars.'<h5>'.__("click to close","bluerabbit").'</h5>';
		$data['success']=true;
		$data['rating']=$rating;
		echo json_encode($data);
		die();
    }

    public function uploadBulkQuests(){
		global $wpdb;
		$data = array();
		$n = new Notification();
		$adv_id = $_POST['adventure_id'];
		if (isset($_FILES['csv_file']['tmp_name'])) {
			$file = $_FILES['csv_file']['tmp_name'];
			if (!is_readable($file)) {
				$data['errors'][] = __("File not readable.","bluerabbit");
			}
			if (empty($file) || !file_exists($file)) {
				$data['errors'][] = __("No file uploaded.","bluerabbit");
			}

			$bulk_quests_query = "INSERT INTO {$wpdb->prefix}br_quests ( quest_author, adventure_id, quest_status, quest_title, quest_type, quest_color, quest_icon, mech_level, mech_xp, mech_bloo, mech_ep, mech_badge, quest_order ) VALUES ";
			$values = [];
			$place_holders = [];
			if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
				while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
					if ($row_index == 0) {
						// Skip the header row (optional)
						$row_index++;
						continue;
					}
					if($row_index <=150){

						// Assuming the CSV file has columns: name, email, age
						$quest_data = [];
						$quest_data['adventure_id']=$adv_id;;
						$quest_data['quest_author']=sanitize_text_field($file_data[0]);
						$quest_data['quest_status']=sanitize_text_field($file_data[1]);
						$quest_data['quest_title']=sanitize_text_field($file_data[2]);
						$quest_data['quest_type']=sanitize_text_field($file_data[3]);
						$quest_data['quest_color']=sanitize_text_field($file_data[4]);
						$quest_data['quest_icon']=sanitize_text_field($file_data[5]);
						$quest_data['mech_level']=sanitize_text_field($file_data[6]);
						$quest_data['mech_xp']=sanitize_text_field($file_data[7]);
						$quest_data['mech_bloo']=sanitize_text_field($file_data[8]);
						$quest_data['mech_ep']=sanitize_text_field($file_data[9]);
						$quest_data['mech_badge']=sanitize_text_field($file_data[10]);
						$quest_data['quest_order']=sanitize_text_field($file_data[11]);
						if($quest_data['quest_title']){
							array_push($values, $quest_data['quest_author'], $quest_data['adventure_id'], $quest_data['quest_status'], $quest_data['quest_title'], $quest_data['quest_type'], $quest_data['quest_color'], $quest_data['quest_icon'], $quest_data['mech_level'], $quest_data['mech_xp'], $quest_data['mech_bloo'], $quest_data['mech_ep'], $quest_data['mech_badge'], $quest_data['quest_order']);
							$place_holders[] = " (%d, %d, %s, %s, %s, %s, %s, %d, %d, %d, %d, %s, %d)";
							$msg_content = __("Milestone ",'bluerabbit').$quest_data['quest_title'].__(" inserted correctly",'bluerabbit');
							$data['messages'][] = $n->pop($msg_content,'green','check');
						}else{
							$msg_content = __("Skipping empty row in file",'bluerabbit');
							$data['messages'][] = $n->pop($msg_content,'green','check');
						}
						$row_index++;
					}
				}

				fclose($handle);

				$bulk_quests_query .= implode(', ', $place_holders);
				$bulk_quests_query = $wpdb->query( $wpdb->prepare("$bulk_quests_query ", $values));
				$msg_content = __("Journey uploaded correctly",'bluerabbit');

				$data['messages'][] = $n->pop($msg_content,'amber','check');
				$data['success'] = true;
			}else{
				$data['errors'][] =__("Cannot open file to read","bluerabbit");
			}
		}else{
			$data["errors"][] = __("File doesn't exist","bluerabbit");
		}


		echo json_encode($data);
		die();
    }

    public function validatePlayerWork(){
		global $wpdb; $current_user = wp_get_current_user();

		$roles = $current_user->roles;
		$data = array();
		$errors = array();
		$pp_data = $_POST['pp_data'];
		$quest_id = $pp_data['quest_id'];
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id");
		$pp_links = $pp_data['pp_links'];
		$pp_images = $pp_data['pp_images'];
		$min_words = $quest->mech_min_words;
		$min_links = $quest->mech_min_links;
		$min_images = $quest->mech_min_images;
		$adventure_id = $pp_data['adventure_id'];
		$pp_content = stripslashes_deep($pp_data['pp_content']);
		$pp_type = $pp_data['pp_type'];
		if(!$min_words){ $min_words = 0;}


		$words = wp_trim_words($pp_content, $min_words);
		if(!$words){
			$wordcount = 0;
		}else{
			$wordcount = count(explode(" ", $words));
		}


		if($wordcount < $min_words){
			$errors['wordcount']=__("You must write at least $min_words words","bluerabbit"). " <h1>".__("You wrote","bluerabbit")." $wordcount</h1>";
		}
		if($pp_links < $min_links){
			$errors['linkcount']=__("You must include $min_links links","bluerabbit"). " <h1>".__("You included","bluerabbit")." $pp_links</h1>";
		}
		if($pp_images < $min_images){
			$errors['imagecount']=__("You must include $min_images images","bluerabbit"). " <h1>".__("You included","bluerabbit")." $pp_images</h1>";
		}
		if($errors){
			$data['continue']=false;
			$data['message'] = '<span class="icon icon-xl icon-warning"></span><h4><strong>'.__("Please Fix the following errors","bluerabbit").'</strong></h4>';
			foreach($errors as $e){
				$data['message'].="<h3>$e</h3>";
			}
		}else{
			$data['continue']=true;
		}
		echo json_encode($data);
		die();

    }

    public function submitPlayerWork(){
		global $wpdb; $current_user = wp_get_current_user();

		$roles = $current_user->roles;
		$data = array();
		$errors = array();
		if (wp_verify_nonce($_POST['nonce'], 'br_player_post_nonce')) {
			$pp_data = $_POST['pp_data'];
			$quest_id = $pp_data['quest_id'];
			$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id");
			$pp_links = $pp_data['pp_links'];
			$pp_images = $pp_data['pp_images'];
			$min_words = $quest->mech_min_words;
			$min_links = $quest->mech_min_links;
			$min_images = $quest->mech_min_images;
			$adventure_id = $pp_data['adventure_id'];
			$pp_content = stripslashes_deep($pp_data['pp_content']);
			$pp_type = $pp_data['pp_type'];

			$adventure = BR_Adventure::instance()->getAdventure($adventure_id);
			$adv_child_id = $adventure->adventure_id;
			$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;



			if(!$min_words){ $min_words = 0;}

			if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
			$today = date("Y-m-d H:i:s");
			$player_id = $current_user->ID;

			$words = wp_trim_words($pp_content, $min_words);
			if(!$words){
				$wordcount = 0;
			}else{
				$wordcount = count(explode(" ", $words));
			}


			if($wordcount < $min_words){
				$errors['wordcount']=__("You must write at least $min_words words","bluerabbit"). " <h1>".__("You wrote","bluerabbit")." $wordcount</h1>";

			}
			if($pp_links < $min_links){
				$errors['linkcount']=__("You must include $min_links links","bluerabbit"). " <h1>".__("You included","bluerabbit")." $pp_links</h1>";
			}
			if($pp_images < $min_images){
				$errors['imagecount']=__("You must include $min_images images","bluerabbit"). " <h1>".__("You included","bluerabbit")." $pp_images</h1>";
			}
			if (wp_verify_nonce($_POST['override_nonce'], 'br_player_override_post_nonce_'.$current_user->ID)) {
				$pp_content = "Challenge Overcome by completing all steps";
				$wordcount = 999999;
				$errors = false;
				BR_Activity::instance()->logActivity($adv_child_id,'system-verification','override-player-post',$quest->quest_id);
			}
			$sql = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_content, pp_type, pp_status)
			VALUES (%d, %d, %d, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
			pp_modified=%s, pp_content=%s, pp_type=%s, pp_status=%s";
			$sql = $wpdb->prepare($sql, $quest_id, $adv_child_id, $player_id, $today, $today, $pp_content, $pp_type, 'publish', $today, $pp_content, $pp_type, 'publish');
			if(!$errors){
				$insert = $wpdb->query($sql); $new_pp_id = $wpdb->insert_id;
				if($insert !== false){
					if($quest->mech_item_reward){
						$my_item_rew = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND object_id=$quest->mech_item_reward AND trnx_status='publish'");
						$item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$quest->mech_item_reward AND item_status='publish'");
						if($my_item_rew){
							$data['message'] .= '<h4 class="lime-500"><span class="icon icon-achievement"></span> <strong>'.__("Reward already in backpack!","bluerabbit").'</strong></h4>';
						}else{
							$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
							VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
							$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $quest->mech_item_reward, $current_user->ID, 0, $item_reward->item_type, $today, $today);
							$sql = $wpdb->query($sql);
							BR_Activity::instance()->logActivity($adv_child_id,'earned','item',$quest->mech_item_reward,$quest->quest_id);
							$data['message'] .= '<h4 class="lime-500"><span class="icon icon-basket"></span> <strong>'.__("Obtained an item!","bluerabbit").'</strong></h4>';
						}
					}
					if($quest->mech_achievement_reward){
						$has_achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_achievement a
						JOIN {$wpdb->prefix}br_achievements b ON a.achievement_id=b.achievement_id
						WHERE a.player_id=$current_user->ID AND a.adventure_id=$adv_child_id AND a.achievement_id=$quest->mech_achievement_reward AND b.achievement_status='publish'");
						$branch_ok = BR_Branch::instance()->canGrantAchievement($current_user->ID, $adv_child_id, $quest->mech_achievement_reward);
						if($has_achievement_reward){
							$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Achievement already earned!","bluerabbit").'</strong></h4>';
						}elseif(!$branch_ok){
							$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Already earned an achievement from this branch!","bluerabbit").'</strong></h4>';
						}else{
							$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
							VALUES (%d, %d, %d, %s)";
							$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $quest->mech_achievement_reward, $today);
							$sql = $wpdb->query($sql);
							BR_Activity::instance()->logActivity($adv_child_id,'earned','achievement',$quest->mech_achievement_reward,$quest->quest_id);
							$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Earned an Achievement!","bluerabbit").'</strong></h4>';
						}
						if($has_achievement_reward || $branch_ok){
							$achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$quest->mech_achievement_reward");
						}
					}
					$playerState = BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
					$adv_settings = BR_Config::instance()->getSettings($adv_parent_id);

					$xp_label = $adventure->adventure_xp_label ? $adventure->adventure_xp_label : "XP";
					$bloo_label = $adventure->adventure_bloo_label ? $adventure->adventure_bloo_label : "BLOO";
					$ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";

					$theFile = (get_template_directory()."/completed-quest.php");

					if(file_exists($theFile)) {
						include ($theFile);
					}
					BR_Activity::instance()->logActivity($adv_child_id,'complete','quest',$quest->quest_id,$new_pp_id);
					die();
				}else{
					$data['message'] = '<h1><strong>'.__("Data Base Error. Can't insert/update entry","bluerabbit").'</strong></h1> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
					$data['message'].="<br><br><br>";
					BR_Activity::instance()->logActivity($adv_child_id,'error','quest','Submit Insert Fail',$quest_id);
				}

			}else{
				$data['message'] = '<span class="icon icon-xl icon-warning"></span><h4><strong>'.__("Please Fix the following errors","bluerabbit").'</strong></h4>';
				foreach($errors as $e){
					$data['message'].="<h3>$e</h3>";
				}
			}
		}else{
			$data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();

    }

    public function postToWall(){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$data['success'] = false;
		$adventure_id = $_POST['adventure_id'];
		$ann_content = stripslashes_deep($_POST['ann_content']);
		$type = $_POST['ann_type'];
		$guild_id = $_POST['guild_id'];
		$nonce = $_POST['nonce'];
		if($type=='guild' && $guild_id){
			$type = "guild-$guild_id";
		}
		if($ann_content){
			if(wp_verify_nonce($nonce, 'br_post_wall_nonce')){
				$ann = BR_Announcement::instance()->postAnn($adventure_id, $ann_content, $type);
				if($ann){
					if($type=='announcement'){
						BR_Activity::instance()->logActivity($adventure_id,'posted-announcement','wall-post',"$ann_content");
					}elseif($type=='guild'){
						BR_Activity::instance()->logActivity($adventure_id,'posted-guild','wall-post',"$ann_content");
					}else{
						BR_Activity::instance()->logActivity($adventure_id,'posted-public','wall-post',"$ann_content");
					}
					$data['success']=true;
					$data['message'].="<h1>".__("Message posted!","bluerabbit")."</h1>";
					$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
					$data['location']="reload";
				}else{
					$data['message']="<h1>".__("Can't post message!","bluerabbit")."</h1>";
					$data['message'].="<h3>".__("please try again later","bluerabbit")."</h3>";
					$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
					$data['location']="reload";
				}
			}else{
				$data['message']="<h1>".__("Unauthorized access","bluerabbit")."</h1>";
				$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
			}
		}else{
			$data['message']="<h1>".__("Please add some content","bluerabbit")."</h1>";
			$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
		}
		echo json_encode($data);
		die();
    }

    public function getMilestone($quest_id) {
        $quest = $this->getQuest($quest_id);
        if (!$quest) return false;
        $quest->milestone_type = ($quest->quest_type === 'challenge') ? 'challenge' : 'quest';
        $quest->is_challenge = ($quest->quest_type === 'challenge');
        return $quest;
    }

    public function completeMilestone($player_id, $quest_id, $adventure_id) {
        global $wpdb;

        $quest = $this->getQuest($quest_id);
        if (!$quest) return ['success' => false, 'error' => 'quest_not_found'];

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ?: $adventure->adventure_id;

        if ($adventure->adventure_gmt) { date_default_timezone_set($adventure->adventure_gmt); }
        $now = date('Y-m-d H:i:s');

        // Check if already completed via player_posts
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_player_posts
             WHERE quest_id = %d AND player_id = %d AND adventure_id = %d AND pp_status = 'publish'",
            $quest_id, $player_id, $adv_child_id
        ));
        if ($existing) return ['success' => true, 'already_complete' => true];

        // Calculate score from steps if grade_type is percentage
        $grade = null;
        if ($quest->milestone_grade_type === 'percentage') {
            $step_scores = $wpdb->get_results($wpdb->prepare(
                "SELECT ps.ps_score FROM {$wpdb->prefix}br_player_steps ps
                 JOIN {$wpdb->prefix}br_steps s ON ps.step_id = s.step_id
                 WHERE ps.quest_id = %d AND ps.player_id = %d AND ps.adventure_id = %d
                   AND s.step_required = 1 AND ps.ps_score IS NOT NULL",
                $quest_id, $player_id, $adv_child_id
            ));
            if ($step_scores) {
                $total = array_sum(array_column($step_scores, 'ps_score'));
                $grade = round($total / count($step_scores));
            }
        }

        // Insert player_post (marks milestone as complete)
        $pp_content = 'Completed via step system';
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}br_player_posts
             (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_content, pp_type, pp_status, pp_grade)
             VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %d)
             ON DUPLICATE KEY UPDATE pp_modified = %s, pp_status = 'publish', pp_grade = %d",
            $quest_id, $adv_child_id, $player_id, $now, $now, $pp_content, 'quest', 'publish', ($grade ?: 0),
            $now, ($grade ?: 0)
        ));

        $result = [
            'success' => true,
            'xp'      => (int) $quest->mech_xp,
            'ep'      => (int) $quest->mech_ep,
            'rewards' => [],
        ];

        // Bloo: grade-proportional if graded
        if ($grade && $quest->mech_validate) {
            $result['bloo'] = round($quest->mech_bloo * $grade / 100);
        } else {
            $result['bloo'] = (int) $quest->mech_bloo;
        }

        if ($grade !== null) $result['grade'] = $grade;

        // Item reward
        if ($quest->mech_item_reward) {
            $has = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
                 WHERE player_id = %d AND adventure_id = %d AND object_id = %d AND trnx_status = 'publish'",
                $player_id, $adv_child_id, $quest->mech_item_reward
            ));
            if (!$has) {
                $item = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}br_items WHERE item_id = %d", $quest->mech_item_reward
                ));
                if ($item) {
                    $wpdb->insert("{$wpdb->prefix}br_transactions", [
                        'player_id'    => $player_id,
                        'adventure_id' => $adv_child_id,
                        'object_id'    => $quest->mech_item_reward,
                        'trnx_author'  => $player_id,
                        'trnx_amount'  => 0,
                        'trnx_type'    => $item->item_type,
                        'trnx_date'    => $now,
                        'trnx_modified'=> $now,
                    ]);
                    BR_Activity::instance()->logActivity($adv_child_id, 'earned', 'item', $quest->mech_item_reward, $quest_id);
                    $result['rewards'][] = ['type' => 'item', 'id' => $quest->mech_item_reward, 'name' => $item->item_name];
                }
            }
        }

        // Achievement reward
        if ($quest->mech_achievement_reward) {
            $has = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_achievement
                 WHERE player_id = %d AND adventure_id = %d AND achievement_id = %d",
                $player_id, $adv_child_id, $quest->mech_achievement_reward
            ));
            if (!$has) {
                $wpdb->insert("{$wpdb->prefix}br_player_achievement", [
                    'player_id'           => $player_id,
                    'adventure_id'        => $adv_child_id,
                    'achievement_id'      => $quest->mech_achievement_reward,
                    'achievement_applied' => $now,
                ]);
                BR_Activity::instance()->logActivity($adv_child_id, 'earned', 'achievement', $quest->mech_achievement_reward, $quest_id);
                $result['rewards'][] = ['type' => 'achievement', 'id' => $quest->mech_achievement_reward];
            }
        }

        // Recalculate player state
        BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
        BR_Activity::instance()->logActivity($adv_child_id, 'complete', 'milestone', $quest_id);

        return $result;
    }

    public function getQuest($quest_id=NULL){
		if($quest_id){
			global $wpdb; $current_user = wp_get_current_user();
			$quest = $wpdb->get_row("SELECT quests.*, adv.adventure_title, adv.adventure_code FROM {$wpdb->prefix}br_quests quests
			LEFT JOIN {$wpdb->prefix}br_adventures adv ON quests.adventure_id = adv.adventure_id
			WHERE quests.quest_id=$quest_id");
			if($quest){
				return $quest;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }

    public function getQuests($adventure_id, $quest_type="", $quest_type_exclude="", $order="", $path=''){
		global $wpdb; $current_user = wp_get_current_user();

		if(!$order) { $order = "quest_order, mech_level, mech_start_date, quest_title"; }
		$quest_type_query = $quest_type != '' ? " AND quest_type='$quest_type'" : '';
		$quest_type_query .= $quest_type_exclude != '' ? " AND quest_type !='$quest_type_exclude'" : '';
		$quest_type_query .= $path != '' ? " AND (achievement_id=0 OR achievement_id=$path)" : '';

		$qry = $wpdb->get_results("SELECT *
		FROM {$wpdb->prefix}br_quests
		WHERE adventure_id=$adventure_id $quest_type_query
		ORDER BY $order
		");
		$result = array();
		foreach($qry as $o){
			if($o->quest_status == 'trash'){
				$result['trash'][]=$o;
			}elseif($o->quest_status == 'draft'){
				$result['draft'][]=$o;
			}elseif($o->quest_status == 'hidden'){
				$result['hidden'][]=$o;
			}elseif($o->quest_status == 'publish' || $o->quest_status == 'locked'){
				$result['publish'][]=$o;
			}
		}
		return $result;
    }

    public function getMyQuests($adventure_id){
		global $wpdb; $current_user = wp_get_current_user();

		$qry = $wpdb->get_results("
			SELECT pp.*, q.mech_validate
			FROM {$wpdb->prefix}br_player_posts pp
			JOIN {$wpdb->prefix}br_quests q ON pp.quest_id = q.quest_id
			WHERE pp.adventure_id=$adventure_id AND pp.player_id=$current_user->ID AND pp.pp_status = 'publish'
		");
		$result = array();
		foreach($qry as $pp){
			if(!$pp->mech_validate || $pp->pp_grade > 0){
				$result[]=$pp->quest_id;
			}
		}
		return $result;
    }

    public function setGrade(){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$data['success'] = false;
		$player_id = $_POST['player_id'];
		$quest_id = $_POST['quest_id'];
		$adventure_id = $_POST['adventure_id'];
		$grade = $_POST['grade'];
		$nonce = $_POST['nonce'];//br_grade_nonce
		$notification = new Notification();
		if(wp_verify_nonce($nonce, 'br_grade_nonce')){
			if($grade > 100) { $grade = 100; }elseif($grade < 0){ $grade = 0; }
			$sql = "UPDATE {$wpdb->prefix}br_player_posts SET pp_grade=%d WHERE quest_id=%d AND player_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$grade,$quest_id,$player_id,$adventure_id);
			$wpdb->query($sql);

			$data['success'] = true;
	        $msg_content = __('Grade Updated','bluerabbit');
	        $data['message'] = $notification->pop($msg_content,'green','check');
	        $data['just_notify'] =true;
			$data['new_grade_nonce'] = wp_create_nonce('br_grade_nonce');
			BR_Activity::instance()->logActivity($adventure_id,'set','grade',"",$quest_id, $player_id);
		}else{
			$data['success'] = false;
	        $msg_content = __("Post doesn't exist!",'bluerabbit').'<br>'.__('check again and reload','bluerabbit');
	        $data['message'] = $notification->pop($msg_content,'red','cancel');
	        $data['just_notify'] =true;
		}
		echo json_encode($data);
		die();
    }

    public function validatePlayerPost(){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$data['success'] = false;
		$player_id = intval($_POST['player_id']);
		$quest_id  = intval($_POST['quest_id']);
		$adventure_id = intval($_POST['adventure_id']);
		$validate_action = sanitize_text_field($_POST['validate_action']); // 'validate' or 'invalidate'
		$nonce = $_POST['nonce'];
		$notification = new Notification();

		if(wp_verify_nonce($nonce, 'br_grade_nonce')){
			if($validate_action == 'validate'){
				$grade     = 100;
				$pp_status = 'publish';
				$msg       = __('Quest Validated','bluerabbit');
				$log_type  = 'validate';
				$notif_color = 'green';
				$notif_icon  = 'check';
			} else {
				$grade     = 0;
				$pp_status = 'draft';
				$msg       = __('Quest Invalidated','bluerabbit');
				$log_type  = 'invalidate';
				$notif_color = 'red';
				$notif_icon  = 'cancel';
			}
			$sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}br_player_posts SET pp_grade=%d, pp_status=%s WHERE quest_id=%d AND player_id=%d AND adventure_id=%d",
				$grade, $pp_status, $quest_id, $player_id, $adventure_id
			);
			$wpdb->query($sql);
			$data['success'] = true;
			$data['message'] = $notification->pop($msg, $notif_color, $notif_icon);
			$data['just_notify'] = true;
			$data['new_grade_nonce'] = wp_create_nonce('br_grade_nonce');
			BR_Activity::instance()->logActivity($adventure_id, 'set', $log_type, "", $quest_id, $player_id);
		} else {
			$data['success'] = false;
			$msg_content = __("Unauthorized access",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] = true;
		}
		echo json_encode($data);
		die();
    }

    public function duplicateQuestProcess($quest_id, $adventure_id, $from_template=NULL ){
		global $wpdb; $current_user = wp_get_current_user();
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id");




		if(!isset($from_template)){
			$duplication = "
				INSERT INTO {$wpdb->prefix}br_quests
		(`quest_id`, `quest_author`, `quest_order`, `adventure_id`, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`, `quest_style`, `quest_secondary_headline`, `quest_color`, `milestone_top`, `milestone_left`, `milestone_x`, `milestone_y`, `milestone_z`, `milestone_rotation`)

				SELECT

		 NULL,`quest_author`, `quest_order`, %d, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`,  `quest_style`, `quest_secondary_headline`, `quest_color`, `milestone_top`, `milestone_left`, `milestone_x`, `milestone_y`, `milestone_z`, `milestone_rotation`

				FROM  {$wpdb->prefix}br_quests WHERE `quest_id` = %d;
			";

			$sql = $wpdb->prepare($duplication, $adventure_id, $quest_id);
		}else{
			$duplication = "
				INSERT INTO {$wpdb->prefix}br_quests
		(`quest_author`, `quest_order`, `adventure_id`, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`, `quest_style`, `quest_secondary_headline`, `quest_color`, `milestone_top`, `milestone_left`, `milestone_x`, `milestone_y`, `milestone_z`, `milestone_rotation`)

				SELECT

		 `quest_author`, `quest_order`, %d, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`,  `quest_style`, `quest_secondary_headline`, `quest_color`, `milestone_top`, `milestone_left`, `milestone_x`, `milestone_y`, `milestone_z`, `milestone_rotation`

				FROM  {$wpdb->prefix}br_quests WHERE `quest_id` = %d;
			";
			$sql = $wpdb->prepare($duplication, $adventure_id, $quest_id);
		}



		$newQuest = $wpdb->query($sql);
		$new_quest_id = $wpdb->insert_id;
		if($quest->quest_type == 'challenge'){
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','challenge',"",$quest_id);
			$all_qs = $wpdb->get_results("
				SELECT a.*, b.answer_id, b.answer_value, b.answer_image, b.answer_correct
				FROM {$wpdb->prefix}br_challenge_questions a
				LEFT JOIN {$wpdb->prefix}br_challenge_answers b
				ON a.quest_id = b.quest_id AND a.question_id=b.question_id AND b.answer_status='publish'
				WHERE a.quest_id=$quest_id AND a.question_status='publish'
			");

			$questions = array();
			foreach($all_qs as $kq=>$qs){
				$questions[$qs->question_id]['question_id']=$qs->question_id;
				$questions[$qs->question_id]['title']=$qs->question_title;
				$questions[$qs->question_id]['image']=$qs->question_image;
				$questions[$qs->question_id]['type']=$qs->question_type;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_id']=$qs->answer_id;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_value']=$qs->answer_value;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_image']=$qs->answer_image;
				$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_correct']=$qs->answer_correct;
			}
			$questions_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (quest_id, question_title, question_image, question_type, question_parent) VALUES ";
			$values = array();
			$place_holders = array();
			foreach($questions as $qs){
				$q_title_str = stripslashes_deep($qs['title']);
				array_push($values, $new_quest_id, $q_title_str, $qs['image'], $qs['type'], $qs['question_id']);
				$place_holders[] = "(%d, %s, %s, %s, %d)";
			}
			$questions_query .= implode(', ', $place_holders);
			///////////// INSERT QUESTIONS AND GET FIRST QUESITON ID INSERT to duplicate the answers and insert from there.
			$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $values));
			$question_first_id = $wpdb->insert_id;


			///////////// ANSWERS DUPLICATION >>>
			$answers_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id,  answer_value, answer_image, answer_correct, answer_parent) VALUES ";
			$a_values = array();
			$a_place_holders = array();
			foreach($questions as $question_key=>$qs){
				foreach($qs['answers'] as $answer_key=>$a){
					$a_title_str = stripslashes_deep($a['answer_value']);
					array_push($a_values, $new_quest_id, $question_first_id, $a_title_str, $a['answer_image'], $a['answer_correct'],$a['answer_id'] );
					$a_place_holders[] = "(%d, %d, %s, %s, %d, %d)";
				}
				$question_first_id++;
			}
			$answers_query .= implode(', ', $a_place_holders);
			$answers_insert = $wpdb->query( $wpdb->prepare("$answers_query ", $a_values));
			//
		}elseif($quest->quest_type == 'quest'){
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','quest',"quest-steps",$quest_id);

			// SELECT all steps and Buttons first. Ordered.
			$all_steps = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest_id AND step_status='publish' ORDER BY step_order");
			$all_buttons = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE quest_id=$quest_id AND button_status='publish'");

			// Duplicate Quest Steps

			$steps_query = "INSERT INTO {$wpdb->prefix}br_steps
			(`step_title`, `step_content`, `step_image`, `step_character_image`, `step_character_name`, `step_background`, `step_type`,  `step_attach`, `step_achievement_group`, `step_order`, `step_next`, `step_status`, `step_settings`, `step_item`, `quest_id`, `adventure_id`, `step_parent`) VALUES ";
			$values = array();
			$place_holders = array();
			foreach($all_steps as $step){
				array_push($values,
					$step->step_title,
					$step->step_content,
					$step->step_image,
					$step->step_character_image,
					$step->step_character_name,
					$step->step_background,
					$step->step_type,
					$step->step_attach,
					$step->step_achievement_group,
					(int) $step->step_order,
					(int) $step->step_next,
					$step->step_status,
					$step->step_settings,
					(int) $step->step_item,
					(int) $new_quest_id,
					(int) $adventure_id,
					(int) $step->step_id
				);
				$place_holders[] = "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %s, %s, %d, %d, %d, %d)";
			}
			$steps_query .= implode(', ', $place_holders);
			$steps_insert = $wpdb->query( $wpdb->prepare("$steps_query ", $values));
			$steps_first_id = $wpdb->insert_id;

			// Duplicate Step BUTTONS
			$btns_query = "INSERT INTO {$wpdb->prefix}br_step_buttons  (`step_id`, `quest_id`, `adventure_id`, `button_type`, `button_text`, `button_image`, `button_object_id`, `button_ep_cost`, `button_step_next`, `button_status`, `button_parent`) VALUES ";
			$btn_values = array();
			$btn_placeholders = array();
			foreach($all_steps as $step){
				foreach($all_buttons as $btn){
					if($btn->step_id == $step->step_id){
						//array_push($btn_values, $steps_first_id, $new_quest_id, $adventure_id, $btn->button_text, $btn->button_classes, $btn->button_actions );
						array_push($btn_values, $steps_first_id, $new_quest_id, $adventure_id, $btn->button_type, $btn->button_text, $btn->button_image, $btn->button_object_id, $btn->button_ep_cost, $btn->button_step_next, $btn->button_status, $btn->button_id);
						$btn_placeholders[] = "(%d, %d, %d, %s, %s, %s, %d, %d, %d, %s, %d)";
					}
				}
				$steps_first_id++;
			}
			$btns_query .= implode(', ', $btn_placeholders);
			$btns_insert = $wpdb->query( $wpdb->prepare("$btns_query ", $btn_values));



		}elseif($quest->quest_type == 'survey'){
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','survey',"",$quest_id);
			$all_qs = $wpdb->get_results("
				SELECT a.*, b.survey_option_id, b.survey_option_text, b.survey_option_image
				FROM {$wpdb->prefix}br_survey_questions a
				LEFT JOIN {$wpdb->prefix}br_survey_options b
				ON a.survey_question_id = b.survey_question_id AND b.survey_option_status='publish'
				WHERE a.survey_id=$quest_id AND a.survey_question_status='publish' ORDER BY a.survey_question_order, a.survey_question_id
			");
			$questions = array();
			foreach($all_qs as $kq=>$qs){
				$questions[$qs->survey_question_id]['question_id']=$qs->survey_question_id;
				$questions[$qs->survey_question_id]['text']=$qs->survey_question_text;
				$questions[$qs->survey_question_id]['image']=$qs->survey_question_image;
				$questions[$qs->survey_question_id]['range']=$qs->survey_question_range;
				$questions[$qs->survey_question_id]['desc']=$qs->survey_question_description;
				$questions[$qs->survey_question_id]['display']=$qs->survey_question_display;
				$questions[$qs->survey_question_id]['type']=$qs->survey_question_type;
				$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['option_id']=$qs->survey_option_id;
				$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['text']=$qs->survey_option_text;
				$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['image']=$qs->survey_option_image;
			}

			$questions_query = "INSERT INTO {$wpdb->prefix}br_survey_questions (survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_order, survey_question_description, survey_question_display, survey_question_range, survey_question_parent) VALUES ";
			$values = array();
			$place_holders = array();
			foreach($questions as $qs){
				$q_title_str = stripslashes_deep($qs['text']);
				$q_desc_str = stripslashes_deep($qs['desc']);
				array_push($values,$new_quest_id, $q_title_str, $qs['image'], $qs['type'], $qs['order'], $q_desc_str, $qs['display'], $qs['range'], $qs['question_id']);
				$place_holders[] = "(%d, %s, %s, %s, %d, %s, %s, %d, %d)";
			}
			$questions_query .= implode(', ', $place_holders);
			///////////// INSERT QUESTIONS AND GET FIRST QUESITON ID INSERT to duplicate the options and insert from there.
			$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $values));
			$question_first_id = $wpdb->insert_id;
			///////////// ANSWERS DUPLICATION >>>
			$options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_question_id,  survey_option_text, survey_option_image, survey_option_parent) VALUES ";
			$o_values = array();
			$o_place_holders = array();
			foreach($questions as $question_key=>$qs){
				foreach($qs['options'] as $option_key=>$o){

					$o_title_str = stripslashes_deep($o['text']);
					array_push($o_values, $new_quest_id, $question_first_id, $o_title_str, $o['image'], $o['option_id']);
					$o_place_holders[] = "(%d, %d, %s, %s, %d)";
				}
				$question_first_id++;
			}
			$options_query .= implode(', ', $o_place_holders);
			$options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $o_values));

		}elseif($quest->quest_type == 'mission'){
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','mission',"",$quest_id);
			$all_objectives = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_objectives WHERE quest_id=$quest_id AND objective_status='publish' ORDER BY objective_order");
			$objectives_query = "INSERT INTO {$wpdb->prefix}br_objectives  (`quest_id`, `adventure_id`, `objective_order`, `ep_cost`, `blog_post_id`, `objective_date`, `objective_modified`, `objective_keyword`, `objective_content`, `objective_success_message`, `objective_type`, `objective_status`, `objective_parent`) VALUES ";
			$values = array();
			$place_holders = array();
			foreach($all_objectives as $objs){
				array_push($values, $new_quest_id, $adventure_id, $objs->objective_order, $objs->ep_cost, $objs->blog_post_id, $objs->objective_date, $objs->objective_modified, $objs->objective_keyword, $objs->objective_content, $objs->objective_success_message, $objs->objective_type, $objs->objective_status, $objs->objective_id );
				$place_holders[] = "(%d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %d)";
			}
			$objectives_query .= implode(', ', $place_holders);
			///////////// INSERT QUESTIONS AND GET FIRST QUESITON ID INSERT to duplicate the options and insert from there.
			$objectives_insert = $wpdb->query( $wpdb->prepare("$objectives_query ", $values));

		}else{
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','quest',"",$quest_id);
		}
		$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=".$new_quest_id);
		$clone->debug = $debug;
		return $clone;
		die();
    }

    public function duplicateQuest($p_quest_id='', $p_adventure_id=''){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$errors = array();
		$data['success']=false;
		$quest_id = $_POST['quest_id'] ? $_POST['quest_id'] : $p_quest_id ;
		$adventure_id = $_POST['adventure_id'] ? $_POST['adventure_id'] : $p_adventure_id ;
		if (wp_verify_nonce($_POST['nonce'], 'duplicate_nonce')) {
			$new_quest = $this->duplicateQuestProcess($quest_id, $adventure_id);
			if($new_quest){
				$data['location'] = get_bloginfo('url')."/new-$new_quest->quest_type/?adventure_id=$adventure_id&questID=$new_quest->quest_id";
				$data['message'].="<span class='icon icon-check icon-xl'></span><br>";
				$data['message'].="<h1 class='font w900 lime-400'>".__("Quest duplicated successfully","bluerabbit")."</h1>";
				$data['message'].="<h3>".__("you will be redirected to the newly created quest","bluerabbit")."</h3>";
				$data['success']=true;
			}else{
				$data['message'].="<span class='icon icon-enemy icon-xl'></span><br>";
				$data['message'].="<h1 class='font w900 lime-400'>".__("Database error.","bluerabbit")."</h1>";
				$data['message'].="<h3>".__("Something went wrong... Please reload and try again.","bluerabbit")."</h3>";
				$data['success']=true;
			}
		}else{
			$data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();
    }

    public function bulkCreate(){
		global $wpdb; $current_user = wp_get_current_user();
		$data = array();
		$errors = array();
		$data['success']=false;
		$adventure_id = $_POST['adventure_id'];
		if (wp_verify_nonce($_POST['nonce'], 'bulk_nonce')) {
			$str = BR_Utils::instance()->random_str(20, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
			$new_quest = $this->duplicateQuestProcess($quest_id, $adventure_id);
			if($new_quest){
				$data['location'] = get_bloginfo('url')."/new-$new_quest->quest_type/?adventure_id=$adventure_id&questID=$new_quest->quest_id";
				$data['message'].="<span class='icon icon-check icon-xl'></span><br>";
				$data['message'].="<h1 class='font w900 lime-400'>".__("Quest duplicated successfully","bluerabbit")."</h1>";
				$data['message'].="<h3>".__("you will be redirected to the newly created quest","bluerabbit")."</h3>";
				$data['success']=true;
			}else{
				$data['message'].="<span class='icon icon-enemy icon-xl'></span><br>";
				$data['message'].="<h1 class='font w900 lime-400'>".__("Database error.","bluerabbit")."</h1>";
				$data['message'].="<h3>".__("Something went wrong... Please reload and try again.","bluerabbit")."</h3>";
				$data['success']=true;
			}
		}else{
			$data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();
    }

    public function duplicateQuests(){
		$data = array();
		global $wpdb; $current_user = wp_get_current_user();
		$nonce  = isset($_POST['nonce']) ? $_POST['nonce'] : [] ;
		$adventure_id  = isset($_POST['adventure_target']) ? $_POST['adventure_target'] : 0;
		$duplicates = isset($_POST['duplicates']) ? $_POST['duplicates'] : [] ;
		$achievement_duplicates = isset($_POST['achievement_duplicates']) ? $_POST['achievement_duplicates'] : [];
		$tabi_duplicates = isset($_POST['tabi_duplicates']) ? $_POST['tabi_duplicates'] : [];
		$item_duplicates = isset($_POST['item_duplicates']) ? $_POST['item_duplicates'] : [];
		$enc_duplicates = isset($_POST['enc_duplicates']) ? $_POST['enc_duplicates'] : [];
		$speakers_duplicates = isset($_POST['speakers_duplicates']) ? $_POST['speakers_duplicates'] : [];
		$roles = $current_user->roles;
		$data['success']=false;
		if(wp_verify_nonce($nonce, 'duplicate_nonce')){
			$total = 0;
			if(!empty($duplicates) || !empty($achievement_duplicates) || !empty($item_duplicates) || !empty($tabi_duplicates) || !empty($enc_duplicates)){
				$data['message'] .='<div class="boxed max-w-600 padding-20 white-color"><h1 class="br-text-30 w900">'.__("Duplicating","bluerabbit").'</h1> <ul class="margin-0 padding-0">';
				if(!empty($duplicates)){
					///////////////// QUESTS
					foreach($duplicates as $d){
						$clone = $this->duplicateQuestProcess($d,$adventure_id);
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 blue-500'>
									<span class='icon icon-$clone->quest_type'></span> $clone->quest_title
								</li>
							";
							$data['clones']['quests'][$clone->quest_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating quest",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($achievement_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($achievement_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'achievement');
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 purple-500'>
									<span class='icon icon-achievement'></span> $clone->achievement_name
								</li>
							";
							$data['clones']['achievements'][$clone->ref_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating achievement",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($item_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($item_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'item');
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 pink-500'>
									<span class='icon icon-shop'></span> $clone->item_name
								</li>
							";
							$data['clones']['items'][$clone->ref_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating item",'bluerabbit')." -[ $d ]- ".$clone->error."</li>";
						}
					}
				}
				if(!empty($tabi_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($tabi_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'tabi');
						if($clone->tabi_id){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 teal-500'>
									<span class='icon icon-activity'></span> $clone->tabi_name
								</li>
							";
							$data['clones']['tabis'][] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating Tabi",'bluerabbit')." -[ $d ]- ".$clone."</li>";
						}
					}
				}
				if(!empty($enc_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($enc_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'encounter');
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 teal-500'>
									<span class='icon icon-activity'></span> $clone->enc_question
								</li>
							";
							$data['clones']['encounters'][] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating encounter",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($speakers_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($speakers_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'speaker');
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 teal-500'>
									<span class='icon icon-activity'></span> $clone->speaker_first_name $clone->speaker_last_name
								</li>
							";
							$data['clones']['speakers'][] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating speaker",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				$data['message'] .='</ul>
				<h1 class="br-text-20">'.__("Successfully duplicated")." <strong class='br-text-30'>$total</strong> ".__("elements").'</h1>
				</div>';
				$data['success'] = true;
			}else{
				$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
				$data['message'] .= "<h1>".__("Empty duplicates","bluerabbit")."</h1>";
				$data['message'] .= "<h5>".__("select at least one element","bluerabbit")."</h5>";
			}
		}else{
			$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
			$data['message'] .= "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();
    }

    public function duplicateQuestsFromTemplate($template_nonce=NULL, $adv_target=NULL, $quests=NULL, $achievements=NULL, $items=NULL, $encounters=NULL, $speakers=NULL){
		$data = array();
		global $wpdb; $current_user = wp_get_current_user();
		$nonce  = isset($template_nonce) ? $template_nonce : NULL;
		$adventure_id  = isset($adv_target) ? $adv_target : NULL;
		$duplicates = isset($quests) ? $quests : [];
		$achievement_duplicates = isset($achievements) ? $achievements : [];
		$item_duplicates = isset($items) ? $items : [];
		$enc_duplicates = isset($encounters) ? $encounters : [];
		$speakers_duplicates = isset($speakers) ? $speakers : [];
		$roles = $current_user->roles;
		$data['success']=false;
		$from_template = true;
		if(wp_verify_nonce($nonce, 'duplicate_nonce')){
			$total = 0;
			if(!empty($duplicates) || !empty($achievement_duplicates) || !empty($item_duplicates) || !empty($enc_duplicates)){
				$data['message'] .='<div class="boxed max-w-600 padding-20 white-color"><h1 class="br-text-30 w900">'.__("Duplicating","bluerabbit").'</h1> <ul class="margin-0 padding-0">';
				if(!empty($duplicates)){
					///////////////// QUESTS
					foreach($duplicates as $d){
						$clone = $this->duplicateQuestProcess($d,$adventure_id,$from_template);
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 blue-500'>
									<span class='icon icon-$clone->quest_type'></span> $clone->quest_title
								</li>
							";
							$data['clones']['quests'][$clone->quest_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating quest",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($achievement_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($achievement_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'achievement',$from_template);
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 purple-500'>
									<span class='icon icon-achievement'></span> $clone->achievement_name
								</li>
							";
							$data['clones']['achievements'][$clone->ref_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating achievement",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($item_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($item_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'item',$from_template);
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 pink-500'>
									<span class='icon icon-shop'></span> $clone->item_name
								</li>
							";
							$data['clones']['items'][$clone->ref_id] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating item",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($enc_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($enc_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'encounter',$from_template);
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 teal-500'>
									<span class='icon icon-activity'></span> $clone->enc_question
								</li>
							";
							$data['clones']['encounters'][] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating encounter",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				if(!empty($speakers_duplicates)){
					$data['message'] .= '<br class="clear">';
					foreach($speakers_duplicates as $d){
						$clone = $this->duplicateObjectProcess($d,$adventure_id,'speaker');
						if($clone){
							$total++;
							$data['message'] .= "
								<li class='br-text-20 block padding-5 teal-500'>
									<span class='icon icon-activity'></span> $clone->speaker_first_name $clone->speaker_last_name
								</li>
							";
							$data['clones']['speakers'][] = $clone;
						}else{
							$data['message'] .=   "<li>".__("Error duplicating speaker",'bluerabbit')." -[ $d ]-</li>";
						}
					}
				}
				$data['message'] .='</ul>
				<h1 class="br-text-20">'.__("Successfully duplicated")." <strong class='br-text-30'>$total</strong> ".__("elements").'</h1>
				</div>';

				$data['success'] = true;
			}else{
				$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
				$data['message'] .= "<h1>".__("Empty duplicates","bluerabbit")."</h1>";
				$data['message'] .= "<h5>".__("select at least one element","bluerabbit")."</h5>";
			}
		}else{
			$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
			$data['message'] .= "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
			$data['location'] = get_bloginfo('url');
		}
		return $data;
    }

    public function duplicateObjectProcess($id,$adventure_id, $type, $from_template=NULL){
		$data = array();
		global $wpdb; $current_user = wp_get_current_user();
		if($type=='achievement'){
			if(isset($from_template)){
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_achievements
					(`adventure_id`, `achievement_name`, `achievement_xp`, `achievement_bloo`, `achievement_ep`, `achievement_max`, `achievement_deadline`, `achievement_badge`, `achievement_display`, `achievement_group`, `achievement_color`, `achievement_status`, `achievement_code`, `achievement_content`, `achievement_order`, `achievement_parent`, `ref_id`)
					SELECT
					%d, `achievement_name`, `achievement_xp`, `achievement_bloo`, `achievement_ep`, `achievement_max`, `achievement_deadline`, `achievement_badge`, `achievement_display`, `achievement_group`, `achievement_color`, `achievement_status`, `achievement_code`, `achievement_content`, `achievement_order`, %d, `ref_id`
					FROM  {$wpdb->prefix}br_achievements WHERE `achievement_id` = %d;
				";
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $id));
			}else{
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_achievements
					(`adventure_id`, `achievement_name`, `achievement_xp`, `achievement_bloo`, `achievement_ep`, `achievement_max`, `achievement_deadline`, `achievement_badge`, `achievement_display`, `achievement_group`, `achievement_color`, `achievement_status`, `achievement_code`, `achievement_content`, `achievement_order`,`achievement_parent`, `ref_id`)
					SELECT
					%d, `achievement_name`, `achievement_xp`, `achievement_bloo`, `achievement_ep`, `achievement_max`, `achievement_deadline`, `achievement_badge`, `achievement_display`, `achievement_group`, `achievement_color`, `achievement_status`, `achievement_code`, `achievement_content`, `achievement_order`,%d, %s
					FROM  {$wpdb->prefix}br_achievements WHERE `achievement_id` = %d;
				";
				$ref_id = BR_Utils::instance()->random_str(8,'1234567890abcdef');
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $ref_id, $id));
			}
			$clone_id=$wpdb->insert_id;
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','achievement',"",$id);
			$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=".$clone_id);
		}elseif($type=='item'){
			if(isset($from_template)){
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_items

					(`adventure_id`, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, `item_parent`, `ref_id`, `tabi_id`, `item_x`, `item_y`, `item_z`, `item_scale`, `item_rotation`, `item_visibility` )

					SELECT
					%d, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, %d, `ref_id`, `tabi_id`, `item_x`, `item_y`, `item_z`, `item_scale`, `item_rotation`, `item_visibility`

					FROM  {$wpdb->prefix}br_items WHERE `item_id` = %d;
				";
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $id));
			}else{
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_items

					(`adventure_id`, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, `item_parent`,`ref_id`, `tabi_id`, `item_x`, `item_y`, `item_z`, `item_scale`, `item_rotation`, `item_visibility` )

					SELECT
					%d, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`,%d, %s, `tabi_id`, `item_x`, `item_y`, `item_z`, `item_scale`, `item_rotation`, `item_visibility`

					FROM  {$wpdb->prefix}br_items WHERE `item_id` = %d;
				";
				$ref_id = BR_Utils::instance()->random_str(8,'1234567890abcdef');
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $ref_id, $id));
			}
			$clone_id=$wpdb->insert_id;
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','item',"",$id);
			$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=".$clone_id);
		}elseif($type=='tabi'){
	        $clone_sql = "
	            INSERT INTO {$wpdb->prefix}br_tabis

	            (`adventure_id`, `tabi_name`, `tabi_status`, `tabi_width`, `tabi_height`, `tabi_color`, `tabi_background`, `tabi_level`, `tabi_on_journey`, `tabi_as_category` )

	            SELECT
	            %d, `tabi_name`, `tabi_status`, `tabi_width`, `tabi_height`, `tabi_color`, `tabi_background`, `tabi_level`, `tabi_on_journey`, `tabi_as_category`

	            FROM  {$wpdb->prefix}br_tabis WHERE `tabi_id` = %d;
	        ";
	        $clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id));
			$clone_id=$wpdb->insert_id;
	        if($clone_id){
	            BR_Activity::instance()->logActivity($adventure_id,'duplicate','tabi',"",$id);
	            $clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=".$clone_id);
	        }else{
	            $clone = print_r($wpdb->last_query,true);
	        }
		}elseif($type=='encounter'){
			if(isset($from_template)){
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_encounters
					(`adventure_id`, `enc_question`, `enc_right_option`, `enc_decoy_option1`, `enc_decoy_option2`, `enc_badge`, `enc_color`, `enc_icon`, `enc_status`, `enc_xp`, `enc_bloo`, `enc_ep`, `enc_level`, `enc_parent`)
					SELECT
					%d, `achievement_id`, `enc_question`, `enc_right_option`, `enc_decoy_option1`, `enc_decoy_option2`, `enc_badge`, `enc_color`, `enc_icon`, `enc_status`, `enc_xp`, `enc_bloo`, `enc_ep`, `enc_level`, %d
					FROM  {$wpdb->prefix}br_encounters WHERE `enc_id` = %d;
				";
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $id));
			}else{
				$clone_sql = "
					INSERT INTO {$wpdb->prefix}br_encounters
					(`adventure_id`, `achievement_id`, `enc_question`, `enc_right_option`, `enc_decoy_option1`, `enc_decoy_option2`, `enc_badge`, `enc_color`, `enc_icon`, `enc_status`, `enc_xp`, `enc_bloo`, `enc_ep`, `enc_level`)

					SELECT

					%d, `achievement_id`, `enc_question`, `enc_right_option`, `enc_decoy_option1`, `enc_decoy_option2`, `enc_badge`, `enc_color`, `enc_icon`, `enc_status`, `enc_xp`, `enc_bloo`, `enc_ep`, `enc_level`

					FROM  {$wpdb->prefix}br_encounters WHERE `enc_id` = %d;
				";
				$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id));
			}
			$clone_id=$wpdb->insert_id;
			BR_Activity::instance()->logActivity($adventure_id,'duplicate','encounter',"",$id);
			$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_encounters WHERE enc_id=".$clone_id);

		}elseif($type=='speaker'){
			$clone_sql = "
				INSERT INTO {$wpdb->prefix}br_speakers
				(`adventure_id`, `player_id`, `speaker_first_name`, `speaker_last_name`, `speaker_bio`, `speaker_picture`, `speaker_company`, `speaker_website`, `speaker_linkedin`, `speaker_twitter`, `speaker_status`)

				SELECT

				%d, `player_id`, `speaker_first_name`, `speaker_last_name`, `speaker_bio`, `speaker_picture`, `speaker_company`, `speaker_website`, `speaker_linkedin`, `speaker_twitter`, `speaker_status`

				FROM  {$wpdb->prefix}br_speakers WHERE `speaker_id` = %d;
			";
			$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id));
			$clone_id=$wpdb->insert_id;
			if($clone_id){
				BR_Activity::instance()->logActivity($adventure_id,'duplicate','speaker',"",$id);
			}else{
				BR_Activity::instance()->logActivity($adventure_id,'duplicate_error','speaker',"",$id);
			}
			$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id=".$clone_id);
		}
		return $clone;
    }

    public function duplicateObject($p_id='', $p_adventure_id='', $p_type=''){
		$data = array();
		global $wpdb; $current_user = wp_get_current_user();
		$nonce = $_POST['nonce'];

		$adventure_id = $_POST['adventure_id'] ? $_POST['adventure_id'] : $p_adventure_id;
		$id = $_POST['id'] ? $_POST['id'] : $p_id;
		$type = $_POST['type'] ? $_POST['type'] : $p_type;

		$data['success']=false;

		if(wp_verify_nonce($nonce, 'duplicate_nonce')){
			$this->duplicateObjectProcess($id,$adventure_id, $type);
			$data['message']="$type cloned";
		}else{
			$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
			$data['message'] .= "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();
    }

    public function duplicateRow($p_id='', $p_adventure_id='', $p_type=''){
		$data = array();
		global $wpdb; $current_user = wp_get_current_user();
		$nonce = $_POST['nonce'];
		$adventure_id = $_POST['adventure_id'] ? $_POST['adventure_id'] : $p_adventure_id;
		$id = $_POST['id'] ? $_POST['id'] : $p_id;
		$type = $_POST['type'] ? $_POST['type'] : $p_type;

		$data['success']=false;
		if(wp_verify_nonce($nonce, 'duplicate_nonce')){
			if($id){
				if($type =='achievement'){
					$a = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_achievements WHERE achievement_id=$id");
					$name = $a->achievement_name;
					$duplication = "INSERT INTO {$wpdb->prefix}br_achievements( `adventure_id`, `achievement_name`, `achievement_xp`, `achievement_bloo`, `achievement_max`, `achievement_deadline`, `achievement_badge`, `achievement_color`, `achievement_status`, `achievement_content`, `achievement_order`, `achievement_display`, `achievement_path`) VALUES
					(%d, %s,  %d, %d, %d, %s,  %s,  %s, %s, %s, %d, %s, %d)";

					$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, '[COPY] - '.$a->achievement_name, $a->achievement_xp, $a->achievement_bloo, $a->achievement_max, $a->achievement_deadline, $a->achievement_badge, $a->achievement_color, $a->achievement_status,  $a->achievement_content, $a->achievement_order, $a->achievement_display, $a->achievement_path ));
					$newCloneID = $wpdb->insert_id;
					BR_Activity::instance()->logActivity($adventure_id,'duplicate','achievement',"",$id);
				}elseif($type =='session'){
					$t = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_sessions WHERE session_id=$id");
					$name = $t->session_title;
					$duplication = "INSERT INTO {$wpdb->prefix}br_sessions

					(`adventure_id`, `quest_id`, `speaker_id`, `achievement_id`,`session_order`, `session_title`, `session_start`, `session_end`, `session_room`, `session_description`,  `session_status`) VALUES
					(%d, %d,  %d, %d, %d, %s,  %s,  %s, %s, %s, %s)";
					$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, $t->quest_id, $t->speaker_id, $t->achievement_id, $t->session_order, '[COPY] - '.$t->session_title, $t->session_start, $t->session_end, $t->session_room, $t->session_description, $t->session_status));
					$newCloneID = $wpdb->insert_id;
					BR_Activity::instance()->logActivity($adventure_id,'duplicate','session',"",$id);
				}elseif($type =='item'){
					$i = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_items WHERE item_id=$id");
					$name = $i->item_name;
					$duplication = "INSERT INTO {$wpdb->prefix}br_items

					(`adventure_id`, `item_status`,`item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `achievement_id`)
					VALUES (%d, %s, %d, %s, %s, %s, %d, %s,  %s,  %s, %d, %d, %d,  %s, %d, %d)";
					$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, $i->item_status, $i->item_author, '[COPY] - '.$i->item_name, $i->item_description, $i->item_secret_description, $i->item_cost, $i->item_type, $i->item_badge, $i->item_secret_badge, $i->item_stock, $i->item_player_max, $i->item_level, $i->item_category, $i->item_order, $i->achievement_id));

					$newCloneID = $wpdb->insert_id;
					BR_Activity::instance()->logActivity($adventure_id,'duplicate','item',"",$id);
				}elseif($type =='guild'){

					$first_str = BR_Utils::instance()->random_str(12,'1234567890abcdefghijkls');
					$code_string = $first_str.$current_user->ID;
					$guild_code = str_shuffle($code_string);
					$t = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_guilds WHERE guild_id=$id");
					$name = $t->guild_name;
					$duplication = "INSERT INTO {$wpdb->prefix}br_guilds
					(`adventure_id`, `guild_name`, `guild_logo`, `guild_color`, `guild_status`, `guild_xp`, `guild_bloo`, `assign_on_login`, `guild_code`,`guild_group`)
					VALUES ( %d, %s, %s, %s, %s, %d, %d, %d, %s, %s )";
					$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, '[COPY] - '.$t->guild_name, $t->guild_logo, $t->guild_color, $t->guild_status, 0, 0, $t->assign_on_login, $guild_code, $t->guild_group));

					$newCloneID = $wpdb->insert_id;
					BR_Activity::instance()->logActivity($adventure_id,'duplicate','guild',"",$id);
				}elseif($type == 'quest'){
					$new_quest = $this->duplicateQuestProcess($id,$adventure_id);
					$newCloneID = $new_quest->quest_id;
				}


				$notification = new Notification();
				$msg_content =  __("Duplicated","bluerabbit")." $name  $newCloneID";
				$data['message'] = $notification->pop($msg_content,'amber','duplicate');
				$data['just_notify'] =true;

				$data['original'] = "#$type-$id";
				$data['duplicate'] = "$type-$newCloneID";
				$data['container'] = "#table-$type tbody";
				$data['clone_id'] = $newCloneID;
				$data['type'] = $type;
				$data['success'] = true;
			}else{

				$data['success'] = false;
				$notification = new Notification();
				$msg_content =  __("No ID Selected","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'red','cancel');
				$data['just_notify'] =true;
			}
		}else{
			$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
			$data['message'] .= "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
			$data['location'] = get_bloginfo('url');
		}
		echo json_encode($data);
		die();
    }

    public function setQuestTabi(){
		global $wpdb;
		$data = array();
		$data['success'] = false;
		$tabi_id      = intval($_POST['tabi_id']);
		$nonce        = $_POST['nonce'];
		$id           = intval($_POST['id']);
		$adventure_id = intval($_POST['adventure_id']);
		$type         = $_POST['type'];
		if(wp_verify_nonce($nonce, 'quest_tabi_nonce')){
			$allowed_types = ['quest','challenge','mission','survey','blog-post','lore','social'];
			if(in_array($type, $allowed_types)){
				$sql = "UPDATE {$wpdb->prefix}br_quests SET tabi_id=%d WHERE quest_id=%d AND adventure_id=%d";
				$sql = $wpdb->prepare($sql, $tabi_id, $id, $adventure_id);
				$the_query = $wpdb->query($sql);
				$notification = new Notification();
				if($the_query === FALSE){
					$data['success'] = false;
					$msg_content = __("Can't assign that tabi",'bluerabbit');
					$data['message'] = $notification->pop($msg_content,'red','cancel');
				}else{
					$data['success'] = true;
					BR_Activity::instance()->logActivity($adventure_id, "set","tabi","$type",$id);
					$msg_content = __('Tabi updated','bluerabbit');
					$data['message'] = $notification->pop($msg_content,'blue','tabi');
					$data['just_notify'] = true;
					$data['new_quest_tabi_nonce'] = wp_create_nonce('quest_tabi_nonce');
				}
			}
		}else{
			$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
		}
		echo json_encode($data);
		die();
    }

	// Object-reference reqs (quest/achievement/key-item) live in br_reqs keyed by
	// quest_id=the gated quest (target_type='quest', the original/default convention -
	// unlike Tabi/Item which use the target_id column + quest_id=0 sentinel, since
	// quest_id already IS the target here and predates that newer convention).
	public function getQuestReqsMap($adventure_id) {
		global $wpdb;
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT quest_id, req_type, req_object_id FROM {$wpdb->prefix}br_reqs
			WHERE adventure_id=%d AND target_type='quest'",
			$adventure_id
		));
		$map = [];
		foreach ($rows as $r) {
			$qid = (int) $r->quest_id;
			if ($r->req_type === 'quest') {
				$map[$qid]['quests'][] = (int) $r->req_object_id;
			} elseif ($r->req_type === 'achievement') {
				$map[$qid]['achievements'][] = (int) $r->req_object_id;
			} elseif ($r->req_type === 'item') {
				$map[$qid]['items'][] = (int) $r->req_object_id;
			}
		}
		return $map;
	}

	public function saveQuestReqs($adventure_id, $quest_id, $quest_ids, $achievement_ids, $item_id) {
		global $wpdb;
		$wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}br_reqs WHERE adventure_id=%d AND target_type='quest' AND quest_id=%d",
			$adventure_id, $quest_id
		));

		$values = [];
		$placeholders = [];
		foreach ((array) $quest_ids as $q) {
			array_push($values, (int) $quest_id, $adventure_id, (int) $q, 'quest');
			$placeholders[] = "(%d, %d, %d, %s)";
		}
		foreach ((array) $achievement_ids as $a) {
			array_push($values, (int) $quest_id, $adventure_id, (int) $a, 'achievement');
			$placeholders[] = "(%d, %d, %d, %s)";
		}
		if (!empty($item_id)) {
			array_push($values, (int) $quest_id, $adventure_id, (int) $item_id, 'item');
			$placeholders[] = "(%d, %d, %d, %s)";
		}
		if (empty($placeholders)) return true;

		$sql = "INSERT INTO {$wpdb->prefix}br_reqs (quest_id, adventure_id, req_object_id, req_type) VALUES "
			. implode(', ', $placeholders);
		$wpdb->query($wpdb->prepare($sql, $values));
		return true;
	}

	// Unified Conditions panel for Quests - mirrors BR_Tabi::renderTabiConditionsModal(),
	// reusing the same modal shape (quest/achievement checkboxes, single key-item select,
	// BR_Conditions threshold inputs) since Quest reqs and Tabi reqs are the same idea.
	public function renderQuestConditionsModal($quest_id) {
		global $wpdb;
		$quest = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=%d", $quest_id
		));
		if (!$quest) return '';

		$adventure_id = $quest->adventure_id;
		$quests = $wpdb->get_results($wpdb->prepare(
			"SELECT quest_id, quest_title FROM {$wpdb->prefix}br_quests
			WHERE adventure_id=%d AND quest_id!=%d AND quest_status IN ('publish','locked') AND quest_type IN ('quest','challenge','survey','mission')
			ORDER BY mech_level ASC, quest_order ASC",
			$adventure_id, $quest_id
		));
		$achievements = $wpdb->get_results($wpdb->prepare(
			"SELECT achievement_id, achievement_name FROM {$wpdb->prefix}br_achievements
			WHERE adventure_id=%d AND achievement_status='publish' ORDER BY achievement_name ASC",
			$adventure_id
		));
		$key_items = $wpdb->get_results($wpdb->prepare(
			"SELECT item_id, item_name FROM {$wpdb->prefix}br_items
			WHERE adventure_id=%d AND item_status='publish' AND item_type='key' ORDER BY item_name ASC",
			$adventure_id
		));

		$reqs_map   = $this->getQuestReqsMap($adventure_id);
		$quest_reqs = $reqs_map[(int) $quest_id] ?? ['quests' => [], 'achievements' => [], 'items' => []];
		$conditions = BR_Conditions::instance()->getConditions($adventure_id, 'quest', $quest_id);
		$condition_values = [];
		foreach ($conditions as $c) { $condition_values[$c->condition_type] = $c->threshold_value; }

		$quest_conditions_nonce = wp_create_nonce('quest_conditions_nonce');
		$theFile = get_template_directory() . '/quest-conditions-modal.php';
		if (!file_exists($theFile)) return '';
		ob_start();
		include($theFile);
		return ob_get_clean();
	}

	public function insertQuestConditionsModal($p_quest_id = null) {
		$quest_id = $p_quest_id ? $p_quest_id : $_POST['quest_id'];
		echo $this->renderQuestConditionsModal($quest_id);
		die();
	}

	public function saveQuestConditions() {
		$data = ['success' => false];
		$notification = new Notification();

		if (!wp_verify_nonce($_POST['nonce'] ?? '', 'quest_conditions_nonce')) {
			$data['message'] = "<h1>" . __("Nonce!", "bluerabbit") . "</h1><h4>" . __("click to close", "bluerabbit") . "</h4>";
			echo json_encode($data);
			die();
		}

		$quest_id     = (int) ($_POST['quest_id'] ?? 0);
		$adventure_id = (int) ($_POST['adventure_id'] ?? 0);
		if ($quest_id && $adventure_id) {
			$quest_ids       = array_map('intval', (array) ($_POST['quest_ids'] ?? []));
			$achievement_ids = array_map('intval', (array) ($_POST['achievement_ids'] ?? []));
			$item_id         = (int) ($_POST['item_id'] ?? 0);
			$this->saveQuestReqs($adventure_id, $quest_id, $quest_ids, $achievement_ids, $item_id);

			$conditions = [];
			foreach (BR_Conditions::CONDITION_TYPES as $type => $label) {
				$val = $_POST['conditions'][$type] ?? '';
				if ($val !== '') {
					$conditions[] = ['condition_type' => $type, 'threshold_value' => (float) $val];
				}
			}
			BR_Conditions::instance()->saveConditions($adventure_id, 'quest', $quest_id, $conditions);

			$data['success'] = true;
			$data['message'] = $notification->pop(__('Conditions saved', 'bluerabbit'), 'blue', 'check');
			$data['just_notify'] = true;
		}

		echo json_encode($data);
		die();
	}

}

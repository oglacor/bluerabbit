<?php
function logActivity($adv_id=0,$action='',$type='',$content='',$object_id=0,$object_child_id=0){
	global $wpdb; $current_user = wp_get_current_user();
	$log_sql = "INSERT INTO {$wpdb->prefix}br_activity_log (`adventure_id`, `player_id`, `log_action`, `log_type`, `log_object_id`, `log_object_child_id`, `log_content`, `log_date`) VALUES (%d,%d,%s,%s,%d,%d,%s,%s)";
	$today = date('Y-m-d h:i:s');
	$log_sql = $wpdb->query($wpdb->prepare($log_sql,$adv_id,$current_user->ID,$action,$type,$object_id,$object_child_id,$content,$today));
	return $log_sql;
}
function registerPost($quest_id, $adv_id, $type="quest", $content=""){
	global $wpdb; 
	$current_user = wp_get_current_user();
	$adventure = getAdventure($adv_id);
	$quest = getQuest($quest_id);

	if($adventure && $quest){
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date('Y-m-d h:i:s');
		
		$sql = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_content, pp_type)
		VALUES (%d, %d, %d, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE
		pp_modified=%s, pp_content=%s, pp_type=%s";

		$sql = $wpdb->prepare($sql, $quest->quest_id, $adv_child_id, $current_user->ID, $today, $today, $content, $type, $today, $content, $type);
		$work = $wpdb->query($sql);

		if($quest->mech_item_reward){
			$prev_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND object_id=$quest->mech_item_reward AND trnx_status='publish'");
			if(!$prev_reward){
				$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type)
				VALUES (%d, %d, %d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $quest->mech_item_reward, $current_user->ID, 0, 'reward');
				$sql = $wpdb->query($sql);
			}
		}

		if($quest->mech_achievement_reward){
			$prev_ach = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_achievement a
			JOIN {$wpdb->prefix}br_achievements b ON a.achievement_id=b.achievement_id
			WHERE a.player_id=$current_user->ID AND a.adventure_id=$adv_child_id AND a.achievement_id=$pp->mech_achievement_reward AND b.achievement_status='publish'");
			if(!$prev_ach){
				$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
				VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $quest->mech_achievement_reward, $today);
				$sql = $wpdb->query($sql);
			}
		}

		if($work !== FALSE){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}

}
function absolute_level_calc($player_id=null){
	global $wpdb; 
	if(!$player_id){
		$current_user = wp_get_current_user();
		$player_id = $current_user->ID;
	}
	$enrollment = $wpdb->get_results("
		SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$player_id
	");
	
	$abs_level = 0;
	foreach($enrollment as $e){
		if($e->player_xp > 0){
			$abs_level += $e->player_level;
		}
	}
	if($abs_level <= 0){
		$abs_level = 1;
	}
	$update = $wpdb->query("UPDATE {$wpdb->prefix}br_players SET player_absolute_level=$abs_level WHERE player_id=$player_id");
}
function resetDemoAdventurePlayer(){
	global $wpdb; 
	$data = array();
	$n = new Notification();
	$data['just_notify'] =true;
	$current_user = wp_get_current_user();
	$player_id = $current_user->ID;
	$adventure_id=$_POST['adventure_id'];
	$password=$_POST['player_password'];
	$req_password_reset_demo = getSetting('req_password_reset_demo', $adventure_id);
	
	if($req_password_reset_demo){
		$pass_check = wp_check_password( $password, $current_user->user_pass, $player_id );
		
	}else{
		$pass_check = true;
	}
	if($pass_check){
		if(wp_verify_nonce($_POST['nonce'], 'br_reset_demo_nonce')) {
			$is_back = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure ORDER BY player_id DESC LIMIT 0, 1");
			$demographics_id = $is_back->player_id+1;

			$reset_id = $wpdb->query("INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_status) VALUES ($adventure_id, $demographics_id, 'out')");
			$dem = $wpdb->update("{$wpdb->prefix}br_player_achievement", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_player_adventure", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_player_energy_log", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_player_guild", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_player_objectives", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_player_posts", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_challenge_attempts", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_survey_answers", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));
			$dem = $wpdb->update("{$wpdb->prefix}br_transactions", array( 'player_id' => $demographics_id), array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id ), array( '%d' ), array( '%d','%d' ));

			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_achievement",	array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_adventure",	array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_energy_log",	array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_guild",		array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_objectives",	array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_player_posts", 		array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_challenge_attempts", 	array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_survey_answers", 		array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );
			$no_trace = $wpdb->delete( "{$wpdb->prefix}br_transactions", 		array( 'player_id' => $player_id, 'adventure_id'=>$adventure_id  ), array( '%d','%d' ) );

			$reset_id = $wpdb->query("INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES ($adventure_id, $player_id)");

			$data['success'] = true;
			$msg_content = __('Player data erased from existance','bluerabbit');
			$data['message'] = $n->pop($msg_content,'pink','enemy');
		}else{
			$data['success'] = false;
			$msg_content = __('Wrong Nonce','bluerabbit');
			$data['message'] = $n->pop($msg_content,'red','cancel');
		}
	}else{
		$data['success'] = false;
		$msg_content = __('Verify your password','bluerabbit');
		$data['message'] = $n->pop($msg_content,'red','cancel');
	}
	echo json_encode($data);
	die();
}
//////////////////////////   Save Settings //////////////////////////////
function saveSettings(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$n = new Notification();
	$settings_data = $p_settings ? $p_settings : $_POST['settings_data'];
	if($current_user->roles[0]=='administrator'){
		$saveProcess = saveSettingsProcess($settings_data, $adventure_id);
		if($saveProcess){
			$data['success'] = true;
			$msg_content = __('Settings Updated!','bluerabbit');
			$data['message'] = $n->pop($msg_content,'teal','config');
			$data['just_notify'] =true;
			logActivity(0,'updated','settings');
		}else{
			$data['success'] = false;
			$msg_content = __('No change detected','bluerabbit');
			$data['message'] = $n->pop($msg_content,'orange','remove');
			$data['just_notify'] =true;
		}
	}else{
		$data['success'] = false;
		$msg_content = __('Not enough privileges','bluerabbit');
		$data['message'] = $n->pop($msg_content,'orange','warning');
		$data['just_notify'] =true;
		logActivity(0,'no-privileges','settings');
	}
	echo json_encode($data);
	die();
}
//////////////////////////   Save Settings Process //////////////////////////////
function saveSettingsProcess($settings_data, $adventure_id=0){
	global $wpdb; $current_user = wp_get_current_user();
	$sql = "INSERT INTO {$wpdb->prefix}br_settings (`setting_id`, `setting_name`, `setting_label`, `setting_value`, `adventure_id`) VALUES";
	$values = array();
	$ph= array();
	foreach($settings_data as $key=>$s){
		$name = $s['name'] ? sanitize_title_with_dashes($s['name']) : sanitize_title_with_dashes($key);
		$setting_value = ($s['value'] > 0) ? $s['value'] : "NULL";
		array_push($values, $s['id'], $name, $s['label'], $s['value'], $adventure_id);
		$ph[] = " (%d, %s, %s, %s, %d) ";
		if($s['name']=='default_adventure' && $s['value']){
			update_option('page_on_front', $page_check->ID, 'yes');
			update_option('show_on_front', 'page','yes');
		}else{
			update_option('page_on_front', 0,'yes');
			update_option('show_on_front', 'posts','yes');
		}
	}

	$sql .= implode(', ',$ph);
	$sql .= "ON DUPLICATE KEY UPDATE setting_name=VALUES(setting_name), setting_label=VALUES(setting_label),  setting_value=VALUES(setting_value),  adventure_id=VALUES(adventure_id)";
	
	$sql = $wpdb->query($wpdb->prepare ($sql, $values));
	return $sql;
}
//////////////////////////   Save Config //////////////////////////////
function saveSysConfig(){
	global $wpdb; $current_user = wp_get_current_user();
	$role = $current_user->roles[0];
	$data = array();
	$n = new Notification();
	
	if($role == 'administrator'){
		$config_data = $_POST['config_data'];
		$features_data = $_POST['features_data'];

		$sql = "INSERT INTO {$wpdb->prefix}br_config (`config_id`, `config_name`, `config_label`, `config_type`, `config_desc`, `config_value`) VALUES ";
		$values = array();
		$ph= array();
		foreach($config_data as $key=>$s){
			$name = $s['name'] ? sanitize_title_with_dashes($s['name']) : sanitize_title_with_dashes($key);
			$setting_value = ($s['value'] > 0) ? $s['value'] : "NULL";
			array_push($values, $s['id'], $name, $s['label'],$s['type'],$s['desc'], $s['value']);
			$ph[] = " (%d, %s, %s, %s, %s, %s) ";
			if($s['name']=='default_adventure' && $s['value']){
				update_option('page_on_front', $page_check->ID, 'yes');
				update_option('show_on_front', 'page','yes');
			}else{
				update_option('page_on_front', 0,'yes');
				update_option('show_on_front', 'posts','yes');
			}
		}
		$sql .= implode(', ',$ph);
		$sql .= "ON DUPLICATE KEY UPDATE config_name=VALUES(config_name), config_label=VALUES(config_label),  config_type=VALUES(config_type),  config_desc=VALUES(config_desc),  config_value=VALUES(config_value); ";
		$sql = $wpdb->query($wpdb->prepare ($sql, $values));

		
		
		
		$sql2 = "INSERT INTO {$wpdb->prefix}br_features (`feature_id`, `feature_name`, `feature_label`, `feature_type`, `feature_desc`, `feature_access_free`, `feature_access_pro`, `feature_access_admin`, `feature_access_god`) VALUES ";
		$fvalues = array();
		$fph= array();
		foreach($features_data as $key=>$f){
			$name = sanitize_title_with_dashes($f['name']);
			array_push($fvalues, $f['id'], $name, $f['label'],  $f['type'],  $f['desc'], $f['free'], $f['pro'], $f['admin'], $f['god']);
			$fph[] = " (%d, %s, %s, %s, %s, %d, %d, %d, %d) ";
		}

		$sql2 .= implode(', ',$fph);
		$sql2 .= "ON DUPLICATE KEY UPDATE feature_name=VALUES(feature_name), feature_label=VALUES(feature_label), feature_type=VALUES(feature_type), feature_desc=VALUES(feature_desc),  feature_access_free=VALUES(feature_access_free),  feature_access_pro=VALUES(feature_access_pro),  feature_access_admin=VALUES(feature_access_admin),  feature_access_god=VALUES(feature_access_god); ";
		
		$sql2 = $wpdb->query($wpdb->prepare ($sql2, $fvalues));
		if($sql !== FALSE){
			$data['success'] = true;
			$msg_content = __('Configuration Updated!','bluerabbit');
			$data['message'] = $n->pop($msg_content,'teal','config');
			$data['just_notify'] =true;
			logActivity(0,'updated','settings');
		}else{
			$data['success'] = false;
			$msg_content = __('No change detected','bluerabbit');
			$data['message'] = $n->pop($msg_content,'orange','remove');
			$data['just_notify'] =true;
		}
	}else{
		$data['success'] = false;
		$msg_content = __('Not enough privileges','bluerabbit');
		$data['message'] = $n->pop($msg_content,'orange','warning');
		$data['just_notify'] =true;
		logActivity(0,'attempt-to-update-by-'.$current_user->ID,'config');
	}
	echo json_encode($data);
	die();
}
////////////////////////// NEW HEXAD VALUES //////////////////////////////
function newHexad(){
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	
	$answers = $_POST['answers'];
	$nonce = $_POST['nonce'];
	$data['success']=false;
	if (wp_verify_nonce($_POST['nonce'], 'br_new_hexad_nonce')) {
		if($answers){
			$intrinsic = array($answers["type_f"],$answers["type_s"],$answers["type_ph"],$answers["type_a"]);
			$ptMax = max($intrinsic);
			if($ptMax==$answers["type_f"] ){
				$ptMaxSlug = "freespirit";
				$the_hexad_name = "Free Spirit";
			}elseif($ptMax==$answers["type_a"] ){
				$ptMaxSlug = "achiever";
				$the_hexad_name = "Achiever";
			}elseif($ptMax==$answers["type_ph"] ){
				$ptMaxSlug = "philanthropist";
				$the_hexad_name = "Philanthropist";
			}elseif($ptMax==$answers["type_s"] ){
				$ptMaxSlug = "socialiser";
				$the_hexad_name = "Socialiser";
			}
			$answers = serialize($answers);
			$sql = "INSERT INTO {$wpdb->prefix}br_hexad  (hexad_answers,player_id)
			VALUES (%s,%d)";
			$sql = $wpdb->prepare ($sql,$answers,$current_user->ID);
			$wpdb->query($sql);
			if($wpdb->insert_id){
				$sql = "UPDATE {$wpdb->prefix}br_players SET player_hexad=%s, player_hexad_slug=%s WHERE player_id=%d";
				$sql = $wpdb->prepare ($sql,$the_hexad_name,$ptMaxSlug,$current_user->ID);
				$wpdb->query($sql);
				logActivity($adventure_id,'answered','hexad',$answers);
				$data['success'] = true;
				$data['message'] = '<h2>'.__("Awesome!","bluerabbit").'</h2>'.'<h4><strong>'.__("Your player type is ","bluerabbit").$the_hexad_name.'</strong></h4>'.'<h5>'.__("click to see your results","bluerabbit").'</h5>';
				$data['location'] = get_bloginfo('url')."/my-account";
			}else{
				$data['message'] = '<h1>Process Failed</h1>';
			}
		}else{
			$data['message'] = '<h2>'.__("No answers received","bluerabbit").'</h2>';
		}
	}else{
		$data['message'] = '<h1>'.__("Unauthorized action","bluerabbit").'</h1>'.'<h5>'.__("click to close","bluerabbit").'</h5>';
	}
	echo json_encode($data);
	die();
}
 /////////////////////////////////// SET PLAYER CLASS ROLE //////////////////////////////////
function setPlayerAdventureRole(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success']=false;
	
	$adventure_id = $_POST['adventure_id'];
	$player_id = $_POST['player_id'];
	$role = $_POST['role'];
	$nonce = $_POST['nonce'];
	
	
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'br_player_adventure_status_nonce')){
		$enrollment = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$player_id");
		if($enrollment){
			$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_role=%s WHERE adventure_id=%d AND player_id=%d";
			$sql = $wpdb->query($wpdb->prepare($sql,$role,$adventure_id,$player_id));
			$data['success'] = true;
			$msg_content = __('Role Updated!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'teal','player');
			$data['role_update'] =$role;
			$data['player_id'] =$player_id;
			$data['just_notify'] =true;
			logActivity($adventure_id,'assigned-privilege','player', $role, $player_id);
		}else{
			
			$msg_content = __("Player doesn't exist in adventure",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
		}
		
	}else{
		$data['message'] = "<h1>".__("Nonce Expired!","bluerabbit")."</h1>".'<h4>'.__('click to reload','bluerabbit').'</h4>';
		$data['location'] = 'reload';
	}
	echo json_encode($data);
	die();
}
 /////////////////////////////////// Enroll Player //////////////////////////////////
function updatePlayerAdventureStatus($pData=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success']=false;
	$n = new Notification();
	if($pData){
		$adventure_id=$pData['adventure_id'];
		$player_id = $pData['player_id'];
		$status = $pData['status'];
		$nonce = $pData['nonce'];
	}else{
		$adventure_id = $_POST['adventure_id'];
		$player_id = $_POST['player_id'];
		$status = $_POST['status'];
		$nonce = $_POST['nonce'];
	}
	if(wp_verify_nonce($nonce, 'br_player_adventure_status_nonce')){
		$enrollment = $wpdb->get_row( "
		SELECT player.*, adv.adventure_owner
		FROM {$wpdb->prefix}br_player_adventure player 
		JOIN {$wpdb->prefix}br_adventures adv ON adv.adventure_id=player.adventure_id 
		WHERE player.adventure_id=$adventure_id AND player.player_id=$player_id");
		
		if($enrollment){
			if($player_id != $enrollment->adventure_owner){
				$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_status=%s WHERE adventure_id=%d AND player_id=%d";
				$sql = $wpdb->prepare ($sql, $status, $adventure_id, $player_id);
				$wpdb->query($sql);
				if($status=='out'){
					$msg_content = __("Player removed",'bluerabbit');
					$data['message'] = $n->pop($msg_content,'red','cancel');
					$data['just_notify'] =true;
					logActivity($adventure_id,'removed','player','',$player_id);
				}else{
					$msg_content = __("Player enrolled",'bluerabbit');
					$data['message'] = $n->pop($msg_content,'green','check');
					$data['just_notify'] =true;
					logActivity($adventure_id,'enrolled','player','',$player_id);
				}
				$data['player_adventure_status'] = $status;
				$data['player_id'] = $player_id;

			}else{
				$msg_content = __("Can't change the status of the owner",'bluerabbit');
				$data['message'] = $n->pop($msg_content,'red','cancel');
			}
		}else{
			$sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d)";
			logActivity($adventure_id,'enrolled','player',"First Time",$player_id);
			$sql = $wpdb->prepare ($sql,$adventure_id,$player_id);
			$wpdb->query($sql);
			$msg_content = __("Player enrolled for the first time",'bluerabbit');
			$data['message'] = $n->pop($msg_content,'red','cancel');
		}
	}else{
		$data['message'] = "<h1>".__("Nonce Expired!","bluerabbit")."</h1>".'<h4>'.__('click to reload','bluerabbit').'</h4>';
		$data['location'] = 'reload';
	}
	
	if($pData){
		return $data;
	}else{
		echo json_encode($data);
		die();
	}
	
}
/////////////////////// REORDER POSTS ////////////////////
function reorder(){
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
		logActivity($adventure_id,'reoredered','journey',serialize($the_order));
	}else{
		$data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
		$data['message'] .= "<h4>".$k."</h4>";
	}
	echo json_encode($data);
	die();
}
function reorderItems(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$the_order = $_POST['the_order'];
	$count = 0;
	foreach($the_order as $k=>$id){
		$sql = "UPDATE {$wpdb->prefix}br_items SET item_order=%d WHERE (item_id=%d AND adventure_id=%d) OR item_parent=%d";
		$sql = $wpdb->prepare ($sql, $k, $id, $adventure_id, $id);
		$result = $wpdb->query($sql);
	}
	if($k+1 >= count($the_order)){
		$data['success'] = true;
		$data['message'] = "<h1>".__("Items Reordered","bluerabbit")."</h1>";
		$data['location'] = "reload";
		logActivity($adventure_id,'reoredered','items',serialize($the_order));
	}else{
		$data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
		$data['message'] .= "<h4>".$k."</h4>";
	}
	echo json_encode($data);
	die();
}
function reorderAchievements(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$the_order = $_POST['the_order'];
	$count = 0;
	foreach($the_order as $k=>$id){
		$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_order=%d WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
		$sql = $wpdb->prepare ($sql,$k,$id,$adventure_id,$id);
		$result = $wpdb->query($sql);
	}
	if($k+1 >= count($the_order)){
		$data['success'] = true;
		$data['message'] = "<span class='icon icon-achievement icon-xl'></span>";
		$data['message'] = "<h1>".__("Achievements Reordered","bluerabbit")."</h1>";
		$data['location'] = "reload";
		logActivity($adventure_id,'reoredered','achievements',serialize($the_order));
	}else{
		$data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
		$data['message'] .= "<h4>".$k."</h4>";
	}
	echo json_encode($data);
	die();
}
function reorderQuestions(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$quest_id = $_POST['quest_id'];
	$the_order = $_POST['the_order'];
	$count = 0;
	foreach($the_order as $k=>$id){
		$sql = "UPDATE {$wpdb->prefix}br_survey_questions SET survey_question_order=%d WHERE (survey_question_id=%d) OR survey_question_parent=%d";
		$sql = $wpdb->prepare ($sql,$k,$id,$id);
		$result = $wpdb->query($sql);
	}
	if($k+1 >= count($the_order)){
		$data['success'] = true;
		$data['message'] = "<h1>".__("Questions Reordered","bluerabbit")."</h1>";
		$data['location'] = "reload";
		logActivity($adventure_id,'reoredered','survey-questions',serialize($the_order),$quest_id);
	}else{
		$data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
		$data['message'] .= "<h4>".$k."</h4>";
	}
	echo json_encode($data);
	die();
}

////////////////////////////////////// Compare //////////////////////////////////
function cmp($a, $b) {
	return strcmp($a["last_name"], $b["last_name"]);
}
////////////////////////////////////// TO MONEY //////////////////////////////////
function toMoney($value, $symbol = '', $decimals = 0){
    return $symbol . ($value < 0 ? '-' : '') . number_format(abs($value), $decimals);
}

//INSERT GALLERY

function insertGalleryItem($thumb_id, $file=NULL, $callback=NULL){
	if($thumb_id){ 
		$theFile = (get_template_directory()."/gallery-item.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>No thumb_id found</h1>";
	}
}
//INSERT GALLERY

function insertMultimediaItem($thumb_id, $file, $index, $callback=NULL){
	if($thumb_id){ 
		$theFile = (get_template_directory()."/multimedia-item.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>No thumb_id found</h1>";
	}
}


/// DOWNLOAD ALL IMAGES
function downloadAllImages()
{
	$data = array();
	$datos = $_POST['datos']; // $this->post('datos');
	$dominio = wp_upload_dir();//sin slash final
	$datos=explode("|",$datos);
	$prefix=$_POST['file_prefix'];
	$zip = new ZipArchive();
	$tmp_zip = $zip->open($dominio['basedir'].'/'.$prefix.'images.zip', ZipArchive::CREATE);
	foreach($datos as $img)
	{
		if(!empty($img))
		{
		  $download_file = file_get_contents($img);
		  $zip->addFromString(basename($img), $download_file);
		}

	}
	$zip->close();
	$fecha = new DateTime();
	$data['file'] = get_bloginfo('wpurl')."/wp-content/uploads/{$prefix}images.zip?v={$fecha->getTimestamp()}";
	echo json_encode($data);
	die();
}


/////////////////////// REORDER STEPS ////////////////////
function reorderSteps(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$quest_id = $_POST['quest_id'];
	$the_order = $_POST['the_order'];
	$notification = new Notification();
	$reordered = reorderStepProcess($the_order);
	if($reordered){
        $msg_content = __('Steps Reordered!','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'indigo','check');
        $data['just_notify'] =true;
		$data['success']=true;
		$content_log = serialize($the_order);
		logActivity($adventure_id,'reoredered','steps',$content_log, $quest_id);
	}else{
        $msg_content = __('No change made','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'blue-grey','remove');
        $data['just_notify'] =true;
		$data['success']=false;
	}
	echo json_encode($data);
	die();
}
function reorderStepProcess($the_order){
	global $wpdb;
	$step_values = array();
	$step_ph= array();
	$sql = "INSERT INTO {$wpdb->prefix}br_steps (step_id, step_order) VALUES";
	foreach($the_order as $key=>$o){
		array_push($step_values, $o, $key+1);
		$step_ph[] = " (%d, %d) ";
	}
	$sql .= implode(', ',$step_ph);
	$sql .= "ON DUPLICATE KEY UPDATE step_order=VALUES(step_order) ";
	$sql = $wpdb->query($wpdb->prepare ($sql, $step_values));
	return $sql;
}

function addStep($step_data=null){
	global $wpdb; 

	$quest_id=$_POST['quest_id'];
	$adventure_id=$_POST['adventure_id'];
	$id_to_duplicate=$_POST['id_to_duplicate'];
	$adventure = getAdventure($adventure_id);
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d h:i:s');
	$notification = new Notification();
	
	if($id_to_duplicate){
		$step_to_duplicate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$id_to_duplicate");
		if($step_to_duplicate){
			$step_duplication = "INSERT INTO {$wpdb->prefix}br_steps ( `step_title`, `step_content`, `step_image`, `step_character_image`,`step_character_name`, `step_background`, `step_type`, `step_attach`, `step_achievement_group`, `step_order`, `step_next`, `step_status`, `step_date`, `step_modified`, `step_settings`, `step_item`, `quest_id`, `adventure_id`) SELECT  `step_title`, `step_content`, `step_image`, `step_character_image`, `step_character_name`, `step_background`, `step_type`, `step_attach`, `step_achievement_group`, `step_order`, `step_next`, `step_status`, `step_date`, `step_modified`, `step_settings`, `step_item`, `quest_id`, `adventure_id` FROM  {$wpdb->prefix}br_steps WHERE `step_id` = %d;";
			$sql = $wpdb->prepare($step_duplication, $step_to_duplicate->step_id);
			$step_insert = $wpdb->query($sql);
			$step_id = $wpdb->insert_id;
			if($step_id){
				logActivity($step->adventure_id,'duplicate','step','', $step_to_duplicate->step_id, $step_id);
			}
			$step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id");
		}else{
			$step = NULL;
			$msg_content = __("The step doesn't exist",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
			$data['success']=false;
			echo json_encode($data);
			die();
		}
	}else{
		$steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest_id AND adventure_id=$adventure_id");
		$order = count($steps)+1;
		$step_insert = "INSERT INTO {$wpdb->prefix}br_steps (quest_id, adventure_id, step_date, step_modified, step_title, step_content, step_order) VALUES (%d, %d, %s, %s, %s, %s, %d)";
		$step_insert = $wpdb->query( $wpdb->prepare("$step_insert ", $quest_id, $adventure_id, $today, $today, __("New Step","bluerabbit")." $order", __("New Step Content","bluerabbit")." $order", $order));
		$step_id = $wpdb->insert_id;
		if($step_id){
			logActivity($adventure_id,'add','step','', $step_id);
		}
		$step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id AND adventure_id=$adventure_id");
	}
	
	if($step){
		$theFile = (get_template_directory()."/step-list-item.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}else{
        $msg_content = __("Couldn't create step",'bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
		$data['success']=false;
	}
	echo json_encode($data);
	die();
}
function removeStep(){
	global $wpdb; 
	$step_id=$_POST['step_id'];
	$step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id");
	if($step){
		$adventure = getAdventure($step->adventure_id);
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date('Y-m-d h:i:s');
		$step_update = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->prefix}br_steps SET step_status='trash', step_modified='%s' WHERE step_id=%d", $today, $step->step_id));
		$updated_step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step->step_id");
		$notification = new Notification();
		$data['just_notify'] =true;
		if($updated_step->step_status == 'trash'){
			$msg_content = __('Step removed!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','trash');
			$data['success']=true;
			$data['remove_step'] = true;
			$data['step_id'] = $step->step_id;
			logActivity($adventure_id, 'remove', 'step','',  $step->step_id);
		}else{
			$msg_content = __("Error, couldn't remove step",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'amber','warning');
			$data['success']=false;
		}
	}
	echo json_encode($data);
	die();
}

function editStep(){
	$step_id=$_POST['step_id'];
	$adventure_id=$_POST['adventure_id'];
	global $wpdb; 
	$s = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id AND adventure_id=$adventure_id");
	$theFile = (get_template_directory()."/step-form.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}else{
		echo __("File not found","bluerabbit");
	}
	die();
}

/// Update STEP
function updateStep(){
	global $wpdb;
    $data = array();
    $step_id=$_POST['step_id'];
    $adventure_id=$_POST['adventure_id'];
	
    $step = $wpdb->get_row("
    SELECT step.*, adv.adventure_gmt FROM {$wpdb->prefix}br_steps step 
    JOIN {$wpdb->prefix}br_adventures adv ON step.adventure_id=adv.adventure_id
    WHERE step.step_id=$step_id");
	$notification = new Notification();
	if($step){
		if ($step->adventure_gmt){ date_default_timezone_set($step->adventure_gmt); }
        $today = date('Y-m-d h:i:s');
		$step_title=stripslashes_deep($_POST['step_title']);
		$step_content=stripslashes_deep($_POST['step_content']);
		$step_type=$_POST['step_type'];
		$step_attach=$_POST['step_attach'];
		$step_character_name=$_POST['step_character_name'];
		$step_character_image=$_POST['step_character_image'];
		$step_background=$_POST['step_background'];
		$step_achievement_group=$_POST['step_achievement_group'];
		$step_item=$_POST['step_item'];
		$step_image=$_POST['step_image'];
		
		if($step_type !='path-choice'){ $step_achievement_group = NULL;}
		if($step_type !='dialogue'){ $step_attach = NULL;}
		
		$step_update = "UPDATE {$wpdb->prefix}br_steps SET  `step_modified`=%s, `step_title`=%s, `step_content`=%s,`step_type`=%s, `step_character_image`=%s, `step_background`=%s, `step_achievement_group`=%s, `step_attach`=%s  , `step_item`=%d, `step_character_name`=%s, `step_image`=%s WHERE step_id=%d";
		$step_update = $wpdb->query( $wpdb->prepare("$step_update ", $today, $step_title, $step_content, $step_type, $step_character_image, $step_background, $step_achievement_group, $step_attach, $step_item, $step_character_name, $step_image, $step->step_id));
		$updated_step = $wpdb->get_row("SELECT step.* FROM {$wpdb->prefix}br_steps step WHERE step.step_id=$step->step_id");
		$data['updated_step'] = $updated_step;
        $data['success']=true;
        $msg_content = __('Step updated','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'deep-purple','progression');
		logActivity($adventure_id, 'update', 'step','',  $step_id);
        $data['just_notify'] =true;
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if($adventure->adventure_type=='template'){
			$children_update = "UPDATE {$wpdb->prefix}br_steps SET `step_modified`=%s, `step_title`=%s, `step_content`=%s,`step_type`=%s, `step_character_image`=%s, `step_background`=%s, `step_achievement_group`=%s, `step_attach`=%s, `step_character_name`=%s, `step_image`=%s WHERE `step_parent`=$updated_step->step_id AND step_id!=$updated_step->step_id";
			
			$children_update = $wpdb->query( $wpdb->prepare("$children_update ", $today, $step_title, $step_content, $step_type, $step_character_image, $step_background, $step_achievement_group, $step_attach, $step_character_name, $step_image, $step->step_id));
			logActivity($adventure_id, 'update', 'step-children','',  $step_id);
		}
	}else{
        $data['success']=false;
        $msg_content = __('Step not found','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
    }
    echo json_encode($data);
	die();
}


///////////////////// ADD STEP BUTTON ///////////////////////


function loadStepButtonForm(){
	global $wpdb; 
	$s = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id={$_POST['step_id']} AND step_status='publish'");
	$theFile = get_template_directory().'/step-button-form-'.$_POST['button_form'].'.php';
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}

function addStepButton(){
	global $wpdb; 
	$step_id=$_POST['step_id'];
	$step_type=$_POST['step_type'];
	$quest_id=$_POST['quest_id'];
	$adventure_id=$_POST['adventure_id'];
	$adventure = getAdventure($adventure_id);
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d h:i:s');
	$button_insert = "INSERT INTO {$wpdb->prefix}br_step_buttons (step_id, quest_id, adventure_id, button_type) VALUES (%d, %d, %d, %s)";
	$button_insert = $wpdb->query( $wpdb->prepare("$button_insert ", $step_id, $quest_id, $adventure_id, $step_type));
	$button_id = $wpdb->insert_id;
	if($button_id){
		logActivity($adventure_id, 'add', 'step-button','',  $button_id);
	}
	if($step_type == 'jump'){
		 $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest_id AND adventure_id=$adventure_id AND step_status='publish' AND step_id !=$step_id ORDER BY step_order, step_id"); 
	}
	$btn = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE button_id=$button_id AND adventure_id=$adventure_id");
	$notification = new Notification();
	if($btn){
		$theFile = (get_template_directory()."/step-button-form-".$step_type."-element.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}else{
        $msg_content = __("Couldn't create button",'bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
		$data['success']=false;
	}
	echo json_encode($data);
	die();
	
	
	
}

function removeStepButton(){
	global $wpdb; 
	$button_id=$_POST['button_id'];
	$button_trash = "UPDATE {$wpdb->prefix}br_step_buttons SET button_status='delete' WHERE button_id=%d";
	$button_trash = $wpdb->query($wpdb->prepare($button_trash, $button_id));
	$notification = new Notification();
	if($button_trash){
		$data['button'] = $button_id;
        $msg_content = __('Button removed!','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'pink','delete');
        $data['just_notify'] =true;
		$data['success']=true;
		$data['removed_step_button']= true;
		echo json_encode($data);
		logActivity($adventure_id, 'remove', 'step-button','',  $button_id);
	}else{
        $msg_content = __("Couldn't remove button",'bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
		$data['success']=false;
		echo json_encode($data);
	}
	die();
}

/// Update STEP BUTTON
function updateStepButton(){
	global $wpdb;
    $data = array();
    $step_id=$_POST['step_id'];
    $adventure_id=$_POST['adventure_id'];
    $btn_id=$_POST['btn_id'];
    $button = $wpdb->get_row("
    SELECT btn.*, adv.adventure_gmt FROM {$wpdb->prefix}br_step_buttons btn 
    JOIN {$wpdb->prefix}br_adventures adv ON btn.adventure_id=adv.adventure_id
    WHERE btn.button_id=$btn_id AND btn.step_id=$step_id");
	$notification = new Notification();
	if($button){
		$button_text = stripslashes_deep($_POST['button_text']);
		$button_ep_cost = $_POST['button_ep_cost'];
		$button_step_next=$_POST['button_step_next'];
		$button_image = ($_POST['button_image']);
		
		
//            data: ({action: 'updateStepButton', step_id:step_id, adventure_id:adventure_id, button_text:button_text, button_step_next:button_step_next, btn_id:btn_id, button_image:button_image}),
		
		
		
		$button_update = "UPDATE {$wpdb->prefix}br_step_buttons SET `button_text`=%s, `button_ep_cost`=%d, `button_image`=%s, `button_step_next`=%d WHERE button_id=%d AND step_id=%d ";
		
		
		$button_update = $wpdb->query( $wpdb->prepare($button_update, $button_text, $button_ep_cost, $button_image, $button_step_next, $button->button_id, $button->step_id));
		
		
        $data['success']=true;
		$data['button']=$button;
        $msg_content = __('Button updated','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'green','check');
        $data['just_notify'] =true;
		echo json_encode($data);
		
		logActivity($adventure_id, 'update', 'step-button','',  $btn_id);

	}else{
        $data['success']=false;
        $msg_content = __('Button not found','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
		echo json_encode($data);
    }
	die();
}
/////////////////////



function failQuest(){
	global $wpdb; 
	$quest_id=$_POST['quest_id'];
	$adventure_id=$_POST['adventure_id'];
	$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id AND quest_status='publish'");

	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;


	logActivity($adv_child_id, 'failed', 'quest','', $quest->quest_id);

	$theFile = (get_template_directory()."/failed-quest.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}



/// Add objective
function addObjective(){
	$id=$_POST['id'];
	$adventure_id=$_POST['adventure_id'];
	$objective_type = $_POST['objective_type'];
	if($objective_type){
		global $wpdb; 
        $current_user = wp_get_current_user();
		$objective_insert = "INSERT INTO {$wpdb->prefix}br_objectives (quest_id, adventure_id, objective_type) VALUES (%d, %d, %s)";
		$qs_insert = $wpdb->query( $wpdb->prepare("$objective_insert ", $id, $adventure_id, $objective_type));
		$objective_id = $wpdb->insert_id;
		logActivity($adventure_id, 'add', 'objective','', $objective_id);
        $c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective_id");
		$theFile = (get_template_directory()."/objective-row.php");
	}
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}
function editObjective(){
	$objective_id=$_POST['objective_id'];
	$adventure_id=$_POST['adventure_id'];
	
	global $wpdb; 
	$c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective_id AND adventure_id=$adventure_id");
	if($c){
		$use_encounters = getSetting('use_encounters',$adventure_id);
		$theFile = (get_template_directory()."/objective-form-$c->objective_type.php");
	}
	if(file_exists($theFile)) {
		include ($theFile);
	}else{
		echo __("File not found","bluerabbit");
	}
	die();
}
/// Remove objective
function removeObjective(){
	global $wpdb; $current_user = wp_get_current_user();
    $data = array();
    $objective_id=$_POST['objective_id'];
    $objective = $wpdb->get_row("
    SELECT objectives.*, player.player_adventure_role
    FROM {$wpdb->prefix}br_objectives objectives 
    JOIN {$wpdb->prefix}br_player_adventure player ON objectives.adventure_id=player.adventure_id AND player.player_id=$current_user->ID
    WHERE objectives.objective_id=$objective_id AND objectives.objective_status='publish'
    ");
	$notification = new Notification();
    
    if($objective->player_adventure_role =='gm'){
        $remove_objective = "UPDATE {$wpdb->prefix}br_objectives SET `objective_status`=%s WHERE objective_id=%d";
        $update_query = $wpdb->query( $wpdb->prepare("$remove_objective ", 'trash', $objective_id));
        $data['success']=true;
        $msg_content = __('Objective removed!','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','trash');
        $data['just_notify'] =true;
		logActivity($objective->adventure_id, 'remove', 'objective','', $objective->objective_id);

    }else{
        $data['success']=false;
        $msg_content = __("objective doesn't exist" ,'bluerabbit');
        $data['message'] = $notification->pop($msg_content,'amber','warning');
        $data['just_notify'] =true;
    }
    echo json_encode($data);
	die();
}

/// Update objective
function resetQuestObjectives(){
	global $wpdb;
    $data = array();
    $quest_id=$_POST['quest_id'];
	$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id AND quest_status='publish'");
	$objectives = $wpdb->get_col("SELECT objective_id FROM {$wpdb->prefix}br_objectives WHERE quest_id=$quest_id");
	$objectives = implode(',',$objectives);
	$notification = new Notification();
    if($objectives){
		$objectives_delete = "DELETE FROM {$wpdb->prefix}br_player_objectives WHERE objective_id IN ($objectives)";
		$delete_query = $wpdb->query( $wpdb->prepare($objectives_delete));
        $data['success']=true;
        $msg_content = __('Objectives Reset!','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'teal','objective');
        $data['just_notify'] =true;
		logActivity($quest->adventure_id, 'reset', 'objectives','', $quest->quest_id);
    }else{
        $data['success']=false;
        $msg_content = __("No objectives to reset" ,'bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','warning');
        $data['just_notify'] =true;
    }
    echo json_encode($data);
	die();
}
function updateObjective(){
	global $wpdb;
    $data = array();
	$objective_id=$_POST['objective_id'];
    $objective = $wpdb->get_row("
    SELECT objectives.*, adv.adventure_gmt, adv.adventure_type FROM {$wpdb->prefix}br_objectives objectives 
    JOIN {$wpdb->prefix}br_adventures adv ON objectives.adventure_id=adv.adventure_id
    WHERE objectives.objective_id=$objective_id");
	$notification = new Notification();
	if($objective){
        $objective_data=$_POST['objective_data'];
		$content = stripslashes_deep($objective_data['objective_content']);
		$success_message = stripslashes_deep($objective_data['objective_success_message']);
		
		$old_keyword = $objective->objective_keyword;

		$keyword = stripslashes_deep($objective_data['objective_keyword']);
		$keyword = trim($keyword);
        
		
		if ($objective->adventure_gmt){ date_default_timezone_set($objective->adventure_gmt); }
        $today = date('Y-m-d h:i:s');
		if($old_keyword != $keyword){
			$objectives_delete = "DELETE FROM {$wpdb->prefix}br_player_objectives WHERE `objective_id`=$objective->objective_id";
			$delete_query = $wpdb->query( $wpdb->prepare($objectives_delete));
		}
		$objective_insert = "UPDATE {$wpdb->prefix}br_objectives SET `objective_modified`=%s, `objective_content`=%s, `objective_keyword`=%s,`objective_success_message`=%s, `ep_cost`=%d WHERE objective_id=%d";
		$update_query = $wpdb->query( $wpdb->prepare("$objective_insert ", $today, $content, $keyword, $success_message, $objective_data['objective_ep_cost'], $objective->objective_id));
		$data['debug'] = print_r($wpdb->last_query,true);
		$updated_objective_id = $wpdb->insert_id;
		logActivity($objective->adventure_id, 'update', 'objective','', $objective->objective_id);
		$updated_objective = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective->objective_id");
		
		if($objective->adventure_type=='template'){
			$children_update = "UPDATE {$wpdb->prefix}br_objectives SET `objective_modified`=%s, `objective_content`=%s, `objective_keyword`=%s,`objective_success_message`=%s, `ep_cost`=%d WHERE objective_parent=$objective->objective_id AND objective_id!=$objective->objective_id";

			$children_update = $wpdb->query( $wpdb->prepare("$children_update ", $today, $content, $keyword, $success_message, $objective_data['objective_ep_cost']));

			logActivity($objective->adventure_id, 'update', 'objective-children','', $objective->objective_id);
		}
		$data['objective'] = $updated_objective;
		$data['success']=true;
        $msg_content = __('Objective updated','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'amber','objectives');
        $data['just_notify'] =true;
	}else{
        $data['success']=false;
        $msg_content = __('objective not found','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
    }
    echo json_encode($data);
	die();
}

function factCheck(){
	global $wpdb; $current_user = wp_get_current_user();
    $data = array();
    $keyword=trim(strtolower($_POST['keyword']));
    $quest_id=$_POST['quest_id'];
    $adventure_id=$_POST['adventure_id'];
    $objective_id = $_POST['objective_id'];
    $player = getPlayerAdventureData($adventure_id,$current_user->ID);

	$adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

	$objective = $wpdb->get_row('
	SELECT objectives.*, adv.adventure_gmt FROM '.$wpdb->prefix.'br_objectives objectives 
	JOIN '.$wpdb->prefix.'br_adventures adv ON objectives.adventure_id=adv.adventure_id
	WHERE objectives.objective_id='.$objective_id.' AND objectives.quest_id='.$quest_id.' AND objectives.objective_status="publish" AND objectives.adventure_id='.$adv_parent_id);

    if($player->player_ep>=$objective->ep_cost){
		if ($objective->adventure_gmt){ date_default_timezone_set($objective->adventure_gmt); }
		$today = date('Y-m-d h:i:s');

		$ep_cost = $objective->ep_cost*-1;
        $insert = "INSERT INTO {$wpdb->prefix}br_player_energy_log (`adventure_id`, `player_id`,`energy`,`timestamp`) VALUES (%d,%d,%d, %s) ";
        $insert = $wpdb->query($wpdb->prepare($insert, $adv_child_id, $current_user->ID, $ep_cost, $today));
		$EP = ($player->player_ep-$objective->ep_cost);
		$data['EP'] = $EP;
 		$updatePLAYER = "UPDATE {$wpdb->prefix}br_player_adventure SET player_ep=$EP WHERE player_id=$player->player_id AND adventure_id=$adv_child_id";
		$update = $wpdb->query($updatePLAYER);
		$notification = new Notification();
		if(strtolower($objective->objective_keyword) == strtolower($keyword)){

            $insert = "INSERT INTO {$wpdb->prefix}br_player_objectives (`objective_id`, `player_id`, `adventure_id`, `timestamp`) VALUES (%d,%d,%d, %s) ";
            $insert = $wpdb->query($wpdb->prepare($insert, $objective->objective_id, $current_user->ID, $adv_child_id, $today));
			logActivity($adv_child_id,'solved','objective',$keyword, $objective->objective_id);
  
			// CHECK IF ALL OBJECTIVES ARE SOLVED
			$objectives = getObjectives($adv_child_id, $objective->quest_id, $current_user->ID);
			$objectives_completed = 0;
			foreach($objectives as $cc){
				if($cc->player_id==$current_user->ID){
					$objectives_completed++;
				}
			}
			if($objectives_completed >= count($objectives)){
				$objectives_achieved = true; // CHECK REQUIREMENTS AND INSERT INTO PLAYER_POSTS IF COMPLETED
				$completeRequirements = getRequirements($objective->quest_id, $adv_child_id );
				if($completeRequirements){
					$data['debug'] = print_r($completeRequirements, true);
					$workRegistered = registerPost($objective->quest_id, $adv_child_id, 'mission');
					if($workRegistered){
						logActivity($adv_child_id,'complete','mission', $objective->quest_id);
					}
				}
			}else{
				$objectives_achieved = false;
			}
            $data['success']= true;
			$msg_content = __('Correct!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green','check');
			$data['just_notify'] =true;
            $data['feedback'] .= "<div class='objective-success-message'>"; 
            $data['feedback'] .= "<div class='objective-success-message-header'>".__("Objective completed!","bluerabbit")."</div>";
			$data['feedback'] .= "<div class='objective-success-message-content'>";
            $data['feedback'] .= apply_filters('the_content', $objective->objective_success_message); 
            $data['feedback'] .= "</div>"; 
            $data['feedback'] .= "<div class='objective-success-message-footer'>".__("(click to close)","bluerabbit")."</div>";
            $data['feedback'] .= "</div>"; 
        }else{
			$data['success']=false;
			$msg_content = __('Wrong Keyword','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
			logActivity($adventure_id,'tried','objective',$keyword, $objective->objective_id);
        }
    }else{
        $data['success'] =false;
        $data['no_energy'] = true;
        $data['message'] .= "<h1 class='font _30 w900 pink-A400'>".__("Out of energy","bluerabbit")."</h1>";
        $data['message'] .= "<h3 class='font _20 w600 white-color'>".__("You ran out of Energy Points. To recharge, click this button or the lightning bolt on the top left.","bluerabbit")."</h1>";
        $data['message'] .= "<button class='form-ui padding-10 right teal-bg-A400 border rounded-max grey-900' onClick='loadSidebar(); randomEncounter();'><span class='icon icon-activity'></span>".__("RECHARGE","bluerabbit")."</button>";
    }
    echo json_encode($data);
	die();
}

function insertSolvedObjective(){
	$c = getObjective($_POST['id']);
	$theFile = (get_template_directory()."/objective-item-$c->objective_type-solved.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}

function spendEP(){
	global $wpdb; $current_user = wp_get_current_user();
    $data = array();
    
	$adventure_id=$_POST['adventure_id'];
    $quest_id=$_POST['quest_id'];
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	
    $player = getPlayerAdventureData($adv_child_id,$current_user->ID);
	$ep = -($_POST['ep']);
	$step_to = $_POST['step_to'];
	

	if($player->player_ep >= $ep && $adventure){
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id AND adventure_id=$adv_parent_id");
		if($quest){
			$step_to = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_order=$step_to AND quest_id=$quest->quest_id AND step_status='publish'");
			if($step_to){
				$data['step_id']= $step_to->step_id;
				$data['step_order']= $step_to->step_order;
				$data['step_type']= $step_to->step_type;
				$data['step_content']= $step_to->step_content;
			}
		}
		$today = date('Y-m-d h:i:s');
		$insert = "INSERT INTO {$wpdb->prefix}br_player_energy_log (`adventure_id`, `player_id`,`energy`, `timestamp`, `enc_option_content`) VALUES (%d,%d,%d, %s, %s) ";
		$insert = $wpdb->query($wpdb->prepare($insert, $adv_child_id, $current_user->ID, $ep, $today, 'Spent EP'));
		$EP = ($player->player_ep+$ep);
		$data['EP'] = $EP;
		$updatePLAYER = "UPDATE {$wpdb->prefix}br_player_adventure SET player_ep=$EP WHERE player_id=$player->player_id AND adventure_id=$adv_child_id";
		$update = $wpdb->query($updatePLAYER);
		logActivity($adv_child_id,'spent','ep', $EP);
    }else{
        $data['success'] =false;
        $data['no_energy'] = true;
        $data['message'] .= "<h1 class='font _30 w900 pink-A400'>".__("Out of energy","bluerabbit")."</h1>";
		$data['message'] .= "<h3 class='font _20 w600 white-color'>".__("You ran out of Energy Points. To recharge, click this button or the lightning bolt on the top left.","bluerabbit")."</h1>";
       $data['message'] .= "<button class='form-ui padding-10 right teal-bg-A400 border rounded-max grey-900' onClick='randomEncounter();'><span class='icon icon-activity'></span>".__("RECHARGE","bluerabbit")."</button>";
    }
    echo json_encode($data);
	die();
}


/// Add Question
function addQuestion(){
	global $wpdb; $current_user = wp_get_current_user();
	$type=$_POST['type'];
	$id=$_POST['id'];
	$style = $_POST['style'] ? $_POST['style'] : 'closed';
	$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$id AND quest_status='publish'");
	if($type == 'survey'){

		if($style == 'rating'){
			$range = 5;
		}else if($style=='guild-vote'){
			$survey = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$id");
			$guild_groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$survey->adventure_id AND guild_group !='' AND guild_status='publish' GROUP BY guild_group"); 
		}
		$questions_query = "INSERT INTO {$wpdb->prefix}br_survey_questions (survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_range) VALUES (%d, %s, %s, %s, %d)";
		$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $id, "", "", $style, $range));
		$qKey = $wpdb->insert_id;
		logActivity($quest->adventure_id,'add','survey-question','',$quest->quest_id);
		if(!$style || $style == 'closed'){
			$options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_question_id) VALUES ";
			$options_query .= "(%d, '', $qKey), (%d,'', $qKey)";
			$options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $id, $id));
			logActivity($quest->adventure_id,'add','survey-question-option',($style ? $style : 'closed'),$quest->quest_id);
		}
		$theFile = (get_template_directory()."/$type-question-form.php");
	}elseif($type == 'challenge'){
		$questions_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (quest_id, question_title, question_image) VALUES (%d, %s, %s)";
		$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $id, "", ""));
		$qKey = $wpdb->insert_id;
		logActivity($quest->adventure_id,'add','challenge-question','',$quest->quest_id);
		if($wpdb->insert_id){
			$options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id,  answer_value, answer_image, answer_correct) VALUES ";
			$options_query .= "(%d, $qKey, '','',1 ), (%d, $qKey, '','',0 )";
			$options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $id, $id));
			logActivity($quest->adventure_id,'add','challenge-question-option','',$quest->quest_id);
		}
		logActivity($quest->adventure_id,'add','challenge-question');
		$theFile = (get_template_directory()."/$type-question-form.php");
	}
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}
/// Duplicate Question
function duplicateQuestion(){
	global $wpdb; $current_user = wp_get_current_user();
	$question = array();
	$type=$_POST['type'];
	$adventure_id=$_POST['adventure_id'];
	$quest_id=$_POST['quest_id'];
	$main_id=$_POST['main_id'];
	$q_id = $_POST['q_id'];
	if($type == 'survey'){
		$my_question = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_survey_questions WHERE survey_question_id=$q_id AND survey_id=$main_id AND survey_question_status='publish'");
		if($my_question){
			$question['survey_question_image']= $my_question->survey_question_image;
			$question['survey_question_text']= $my_question->survey_question_text;
			$question['survey_question_type']= $my_question->survey_question_type;
			$question['survey_question_display']= $my_question->survey_question_display;
			$question['survey_question_order']= $my_question->survey_question_order+1;
			$question['survey_question_description']= $my_question->survey_question_description;
			$the_question_query = "INSERT INTO {$wpdb->prefix}br_survey_questions (survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_display, survey_question_order, survey_question_description) VALUES (%d, %s, %s, %s, %s, %d, %s)";
			$the_question_insert = $wpdb->query( $wpdb->prepare("$the_question_query ", $main_id, $my_question->survey_question_text, $my_question->survey_question_image, $my_question->survey_question_type, $my_question->survey_question_display, $question['survey_question_order'], $my_question->survey_question_description));
			$qKey = $wpdb->insert_id;
			if($my_question->survey_question_type == 'closed' || $my_question->survey_question_type == 'multi-choice'){
				$the_options = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_options WHERE survey_question_id=$q_id AND survey_id=$main_id AND survey_option_status='publish'");
				$the_options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_option_image, survey_question_id) VALUES ";
				$the_options_values = array();
				$the_options_place_holders = array();
				foreach($the_options as $key => $the_option) {
					 array_push($the_options_values, $main_id, $the_option->survey_option_text, $the_option->survey_option_image, $qKey );
					 $the_options_place_holders[] = "(%d, %s, %s, %d)";

				}
				$the_options_query .= implode(', ', $the_options_place_holders);
				$the_options_insert = $wpdb->query( $wpdb->prepare("$the_options_query ", $the_options_values));
				logActivity($adventure_id,'duplicate','survey-question','',$quest_id,$main_id);
			}
			$theFile = (get_template_directory()."/survey-question-form.php");
		}
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}elseif($type == 'challenge'){
		$questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions WHERE question_id=$q_id AND quest_id=$main_id AND question_status='publish'");
		$my_question = $questions[0];
		if($my_question){
			$question['image']= $my_question->question_image;
			$question['title']= __("[ COPY ]")." - ".$my_question->question_title;
			$the_question_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (`quest_id`, `question_title`, `question_image`, `question_type`) VALUES (%d, %s, %s, %s)";
			$the_question_insert = $wpdb->query( $wpdb->prepare("$the_question_query ", $main_id, $question['title'], $my_question->question_image, $my_question->question_type));
			$qKey = $wpdb->insert_id;
			$the_answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$q_id AND quest_id=$main_id AND answer_status='publish'");
			$the_answers_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id, answer_value, answer_image, answer_correct) VALUES ";
			$the_answer_values = array();
			$the_answers_place_holders = array();
			foreach($the_answers as $key => $the_option) {
				 array_push($the_answer_values, $main_id, $qKey, $the_option->answer_value, $the_option->answer_image, $the_option->answer_correct);
				 $the_answers_place_holders[] = "(%d, %d, %s, %s, %d)";
			}
			$the_answers_query .= implode(', ', $the_answers_place_holders);
			$the_answers_insert = $wpdb->query( $wpdb->prepare("$the_answers_query ", $the_answer_values));
			$theFile = (get_template_directory()."/challenge-question-form.php");
			$options = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$qKey ", "ARRAY_A");
			$question['answers'] = $options;
			logActivity($adventure_id,'duplicate','challenge-question','',$quest_id,$main_id);
			if(file_exists($theFile)) {
				include ($theFile);
			}
		}
	}
	die();
}
/// Update Question
function updateQuestion(){
	global $wpdb; $current_user = wp_get_current_user(); $data=array();
	$adventure_id=$_POST['adventure_id'];

	$quest_id=$_POST['quest_id'];
	$q_text = stripslashes_deep($_POST['q_text']);
	$q_image = $_POST['q_image'];
	$q_description = stripslashes_deep($_POST['q_description']);
	$q_range = $_POST['q_range'];
	$q_display = $_POST['q_display'];
	$type=$_POST['type'];
	$id=$_POST['id'];
	$notification = new Notification();
	$data['question_updated']= false;
	$data['question_id']=0;
	if($type == 'survey'){
		if($q_range < 0){ $q_range = 0; }
		$questions_query = "UPDATE {$wpdb->prefix}br_survey_questions SET
		
		survey_question_text=%s,
		survey_question_image=%s,
		survey_question_description=%s,
		survey_question_range=%d,
		survey_question_display=%s
		
		WHERE survey_question_id=%d";
		$qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $q_text, $q_image, $q_description, $q_range, $q_display, $id));
		$data['debug'] = print_r($wpdb->last_query,true);
		if($qs_insert){
			$data['success']=true;
			$msg_content = __('Question Updated','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green');
			$data['question_id']=$id;
			$data['question_updated']=$q_text;
			$data['just_notify'] =true;
			logActivity($adventure_id,'update','survey-question','',$quest_id,$id);
			
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if($adventure->adventure_type=='template'){
				$children_update = "UPDATE {$wpdb->prefix}br_survey_questions SET

				survey_question_text=%s,
				survey_question_image=%s,
				survey_question_description=%s,
				survey_question_range=%d,
				survey_question_display=%s

				WHERE survey_question_parent= $id AND survey_question_id!=$id";

				$children_update = $wpdb->query( $wpdb->prepare($children_update, $q_text, $q_image, $q_description, $q_range, $q_display));
				
				logActivity($adventure_id,'update','survey-question-children','',$quest_id,$id);
			}
			
			
		}else{
			$data['success']=false;
			$msg_content = __('Error','bluerabbit');
			$msg_content .= $error;
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
			
		}
	}else if($type == 'challenge'){
		$questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_title=%s, question_image=%s WHERE question_id=%d";
		$qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $q_text, $q_image, $id));
		if($qs_insert){
			$data['success'] = true;
			$msg_content = __('Question Updated','bluerabbit');
			$data['question_id']=$id;
			$data['question_updated']=$q_text;
			$data['message'] = $notification->pop($msg_content,'green');
			$data['just_notify'] =true;
			logActivity($adventure_id,'update','challenge-question','',$quest_id,$id);

		
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if($adventure->adventure_type=='template'){
				$children_update = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_title=%s, question_image=%s WHERE question_parent=$id AND question_id!=$id";

				$children_update = $wpdb->query( $wpdb->prepare($children_update, $q_text, $q_image));
				
				logActivity($adventure_id,'update','challenge-question-children','',$quest_id,$id);
			}
		
		
		}else{
			$msg_content = __('Error','bluerabbit');
			$msg_content .= $error;
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
		}
	}
	echo json_encode($data);
	die();
}
/// Remove Question
function removeQuestion(){
	global $wpdb; $current_user = wp_get_current_user();
	$adventure_id=$_POST['adventure_id'];
	$quest_id=$_POST['quest_id'];
	$data= array();
	$data['success']=false;
	$type=$_POST['type'];
	$id=$_POST['id'];
	if (wp_verify_nonce($_POST['nonce'], 'br_delete_question_nonce')) {
		if($type == 'survey'){
			$questions_query = "UPDATE {$wpdb->prefix}br_survey_questions SET survey_question_status='trash' WHERE survey_question_id=%d";
			$qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $id));
			if($qs_insert){
				$data['success'] = true;
				$data['message'] .= "<li>".__("Question Removed!","bluerabbit")."</li>";
				$data['just_notify'] =true;
				logActivity($adventure_id,'removed','survey-question','',$quest_id,$id);
			}else{
				$data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
			}
		}elseif($type == 'challenge'){
			$questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_status='trash' WHERE question_id=%d";
			$qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $id));
			if($qs_insert){
				$data['success'] = true;
				$data['message'] .= "<li>".__("Question Removed!","bluerabbit")."</li>";
				$data['just_notify'] =true;
				logActivity($adventure_id,'removed','challenge-question','',$quest_id,$id);
			}else{
				$data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
			}
		}
	}else{
		$data['message'] = "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
	}
	echo json_encode($data);
	die();
}

/// Add Option
function addOption(){
	$adventure_id=$_POST['adventure_id'];
	$type=$_POST['type'];
	$main_id=$_POST['main_id'];
	$qKey=$_POST['q_id'];
		global $wpdb; $current_user = wp_get_current_user();
	if($type == 'survey'){
		$options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_question_id) VALUES (%d, '', %d)";
		$options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $main_id, $qKey));
		$oKey = $wpdb->insert_id;
		logActivity($adventure_id,'add','survey-question-option','',$main_id);
	}elseif($type == 'challenge'){
		$options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, answer_value, question_id) VALUES (%d, ' ', %d)";
		$options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $main_id, $qKey));
		$oKey = $wpdb->insert_id;
		logActivity($adventure_id,'add','challenge-question-option','', $main_id);
	}
	$theFile = (get_template_directory()."/$type-question-option-form.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}
/// Update Option
function updateOption(){
	global $wpdb; $current_user = wp_get_current_user();
	$adventure_id=$_POST['adventure_id'];
	$o_text = stripslashes_deep($_POST['o_text']);
	$o_image = $_POST['o_image'];
	$type=$_POST['type'];
	$main_id=$_POST['main_id'];
	$question_id=$_POST['q_id'];
	$option_id=$_POST['option_id'];
	$o_correct=$_POST['o_correct'];
	$notification = new Notification();
	if($type == 'survey'){
		$option_query = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_text=%s, survey_option_image=%s WHERE survey_id=%d AND survey_question_id=%d AND survey_option_id=%d";
		$option_update = $wpdb->query( $wpdb->prepare($option_query, $o_text, $o_image, $main_id, $question_id, $option_id));
		if($option_update){
			$data['success']=true;
			$msg_content = __('Option Updated','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green');
			logActivity($adventure_id,'update','survey-question-option','', $option_id);
			
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if($adventure->adventure_type=='template'){
				$children_update = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_text=%s, survey_option_image=%s WHERE survey_option_parent= $option_id AND survey_option_id!=$option_id";

				$children_update = $wpdb->query( $wpdb->prepare($children_update, $o_text, $o_image));
				
				logActivity($adventure_id,'update','survey-question-option-children','',$option_id);
			}
		}else{
			$msg_content = __('Error','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
		}
		$data['just_notify'] =true;
	}elseif($type == 'challenge'){
		$option_query = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_value=%s, answer_image=%s , answer_correct=%d WHERE quest_id=%d AND question_id=%d AND answer_id=%d";
		$option_update = $wpdb->query( $wpdb->prepare($option_query, $o_text, $o_image, $o_correct, $main_id, $question_id, $option_id));
		
		if($option_update){
			$allOptionsSQL = "SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=%d AND answer_correct=1";
			$allOptions = $wpdb->query( $wpdb->prepare($allOptionsSQL, $question_id)); $allOptions = $wpdb->last_result;
			
			if(count($allOptions)>1){
				$question_type ='multiple';
				
			}else{
				$question_type ='single';
			}
			$questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_type='$question_type' WHERE question_id=%d";
			$qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $question_id));
			
			
			$data['success']=true;
			$msg_content = __('Option Updated','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green');
			logActivity($adventure_id,'update','challenge-question-option','', $option_id);
			
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if($adventure->adventure_type=='template'){
				$children_update = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_value=%s, answer_image=%s , answer_correct=%d WHERE answer_id!=$option_id AND answer_parent=$option_id";

				$children_update = $wpdb->query( $wpdb->prepare($children_update, $o_text, $o_image, $o_correct));

				$questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_type='$question_type'
				WHERE question_parent=$question_id AND question_id!=$question_id";
				$qs_insert = $wpdb->query( $wpdb->prepare($questions_query));
				
				logActivity($adventure_id,'update','challenge-question-option-children','',$option_id);
			}
			
			
		}else{
			$msg_content = __('Error','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
		}
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
/// Remove Option
function removeOption(){
	global $wpdb; $current_user = wp_get_current_user();
	$adventure_id=$_POST['adventure_id'];
	$data= array();
	$data['success']=false;
	$type=$_POST['type'];
	$id=$_POST['id'];
	if (wp_verify_nonce($_POST['nonce'], 'br_delete_option_nonce')) {
		if($type == 'survey'){
			$option_query = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_status='trash' WHERE survey_option_id=%d";
			$option_update = $wpdb->query( $wpdb->prepare($option_query, $id));
			if($option_update){
				$data['success'] = true;
				$data['message'] .= "<li>".__("Option Removed!","bluerabbit")."</li>";
				$data['just_notify'] =true;
				logActivity($adventure_id,'removed','survey-question-option','', $id);
			}else{
				$data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
			}
		}elseif($type == 'challenge'){
			$option_query = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_status='trash' WHERE answer_id=%d";
			$option_update = $wpdb->query( $wpdb->prepare($option_query, $id));
			if($option_update){
				$data['success'] = true;
				$data['message'] .= "<li>".__("Answer Removed!","bluerabbit")."</li>";
				$data['just_notify'] =true;
				logActivity($adventure_id,'removed','challenge-question-option','', $id);
			}else{
				$data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
			}
		}
	}else{
		$data['message'] = "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
	}
	echo json_encode($data);
	die();
}


/// randomEncounter
function randomEncounter(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$adventure_id = $_POST['adventure_id'];
	$enc_id = $_POST['enc_id'];
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date("Y-m-d H:i:s");
	$current_player = getPlayerAdventureData($adv_child_id, $current_user->ID);
	
	$maxEP = 100+(($current_player->player_level*($current_player->player_level+1)/2)*20);
	$notification = new Notification();
	if($adventure){
		if($current_player->player_ep < $maxEP){
			$theFile = (get_template_directory()."/random-encounter.php");
			if(file_exists($theFile)) {
				include ($theFile);
				// --------------------------------   - ----------   Activity log INSIDE RANDOM ENCOUNTER FILE 
			}else{
				$msg_content = __("Content doesn't exist",'bluerabbit');
				$data['message'] = $notification->pop($msg_content, 'red','cancel');
				$data['just_notify'] =true;
				echo json_encode($data);
			}
		}else{
			if($current_player->player_ep > $maxEP){
				$diff = $maxEP-$current_player->player_ep;
				$insert = "INSERT INTO {$wpdb->prefix}br_player_energy_log (`adventure_id`, `player_id`,`energy`, `enc_option_content`,`timestamp`) VALUES (%d,%d,%d, %s, %s)";
				$insert = $wpdb->query($wpdb->prepare($insert, $adv_child_id, $current_player->player_id, $diff, 'EP Cap Difference', $today));
			}
			$msg_content = __("Max EP reached",'bluerabbit');
			logActivity($adventure_id,'max-reached','ep');
			$data['message'] = $notification->pop($msg_content, 'cyan','max');
			$data['just_notify'] =true;
			echo json_encode($data);
		}
	}else{
		$msg_content = __("Adventure doesn't exist",'bluerabbit');
		$data['message'] = $notification->pop($msg_content, 'red','cancel');
		$data['just_notify'] =true;
		echo json_encode($data);
	}
	die();
}
/// answerEncounter
function answerEncounter(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$data['success']=false;
	$adventure_id = $_POST['adventure_id'];
	$enc = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_encounters WHERE enc_id={$_POST['enc_id']} AND enc_status='publish'");
	$value = $_POST['value'];
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date("Y-m-d H:i:s");
	$notification = new Notification();
	$ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";
	$answerInsert= "INSERT INTO {$wpdb->prefix}br_player_energy_log
	(`adventure_id`, `player_id`, `energy`, `enc_id`, `enc_option_content`, `enc_xp`, `enc_bloo`, `timestamp`)
	VALUES (%d,%d,%d,%d,%s,%d,%d, %s)";
	if($enc->enc_right_option == $value){
		$answer = $wpdb->query($wpdb->prepare($answerInsert, $adv_child_id, $current_user->ID, $enc->enc_ep, $enc->enc_id, $value, $enc->enc_xp, $enc->enc_bloo, $today ));
		$data['success']=true;
		$data['earned_ep']=$enc->enc_ep;
		//$data['message'] = $notification->energy($enc->enc_ep);

		$msg_content = __("Correct!",'bluerabbit')." <strong>+</strong> $enc->enc_ep ".$ep_label;
		$data['message'] = $notification->pop($msg_content, 'green','check');
		
		
		$player = getPlayerAdventureData($adv_child_id,$current_user->ID);
		$EP = ($player->player_ep-$objective->ep_cost);
		$data['EP'] = $EP;
		logActivity($adv_child_id,'answer','encounter','',$enc->enc_id);
 		//$updatePLAYER = "UPDATE {$wpdb->prefix}br_player_adventure SET player_ep=$EP WHERE player_id=$player->player_id AND adventure_id=$adventure_id";
		//$update = $wpdb->query($updatePLAYER);
		
		resetPlayer($adv_child_id, $current_user->ID);
	}else{
		//($adv_id=0,$action='',$type='',$content='',$object_id=0,$object_child_id=0
		logActivity($adv_child_id,'wrong-answer','encounter','',$enc->enc_id);
		$answer = $wpdb->query($wpdb->prepare($answerInsert, $adv_child_id, $current_user->ID, 0, $enc->enc_id, $value, 0, 0, $today));
		$msg_content = __("Wrong",'bluerabbit');
		$data['message'] = $notification->pop($msg_content, 'red','cancel');
	}
	echo json_encode($data);
	die();
}
/// Load Content
function loadContent(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	
	$content = isset($_POST['content']) ? $_POST['content'] : NULL ;
	$id = isset($_POST['id']) ? $_POST['id'] : NULL ;
	$adventure = isset($_POST['adventure_id']) ? getAdventure($_POST['adventure_id'])  : NULL ;
	$theFile = (get_template_directory()."/$content.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}else{
		echo "<h1>".__("Content doesn't exist","bluerabbit")."</h1>";
	}
	die();
}

/// Load Quest Card
function loadQuestCard(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$quest_id = $_POST['quest_id'];
	$adventure_id = $_POST['adventure_id'];
	$quest = $wpdb->get_row("SELECT 
		quest.*, player.player_nickname, player.player_picture FROM {$wpdb->prefix}br_quests quest 
		JOIN {$wpdb->prefix}br_players player
		ON player.player_id = quest.quest_author 
		WHERE quest.quest_id=$quest_id
	");
	
	if($quest){
		$pp = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_posts WHERE player_id=$current_user->ID AND quest_id=$quest->quest_id");
		
		$adventure = getAdventure($adventure_id);

		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
		$isGM = false;
		if($adventure->adventure_owner == $current_user->ID){
			$isGM = true;
			$isOwner = true;
		}elseif($adventure->player_adventure_role == 'gm'){
			$isGM = true;
		}elseif($adventure->player_adventure_role == 'npc'){
			$isNPC = true;
		}
		$isFinished = $pp ? true : false;
		$theFile = (get_template_directory()."/card-quest.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>".__("Quest doesn't exist","bluerabbit")."</h1>";
	}
	die();
}
/// Load Achievement Card
function loadAchievementCard(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$achievement_id = $_POST['achievement_id'];
	$adventure_id = $_POST['adventure_id'];
	$a = $wpdb->get_row("SELECT 
		achievement.*, player.player_id, player.achievement_applied FROM {$wpdb->prefix}br_achievements achievement
		LEFT JOIN {$wpdb->prefix}br_player_achievement player
		ON player.achievement_id = achievement.achievement_id 
		WHERE achievement.achievement_id=$achievement_id
	");
	if($a){
		$adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
		JOIN {$wpdb->prefix}br_player_adventure c
		ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
		WHERE a.adventure_id=$adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
		$isGM = false;
		if($adventure->adventure_owner == $current_user->ID){
			$isGM = true;
			$isOwner = true;
		}elseif($adventure->player_adventure_role == 'gm'){
			$isGM = true;
		}elseif($adventure->player_adventure_role == 'npc'){
			$isNPC = true;
		}
		$isEarned = $a->player_id==$current_user->ID ? true : false;
		
		$theFile = (get_template_directory()."/card-achievement.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>".__("Achievement doesn't exist","bluerabbit")."</h1>";
	}
	die();
}
function displayAchievementCard(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$achievement_id = $_POST['achievement_id'];
	$a = $wpdb->get_row("SELECT 
		achievement.*, player.player_id, player.achievement_applied FROM {$wpdb->prefix}br_achievements achievement
		LEFT JOIN {$wpdb->prefix}br_player_achievement player
		ON player.achievement_id = achievement.achievement_id 
		WHERE achievement.achievement_id=$achievement_id
	");
	$n = new Notification();
	if($a){
		$adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
		JOIN {$wpdb->prefix}br_player_adventure c
		ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
		WHERE a.adventure_id=$a->adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
		$isGM = false;
		if($adventure->adventure_owner == $current_user->ID){
			$isGM = true;
			$isOwner = true;
		}elseif($adventure->player_adventure_role == 'gm'){
			$isGM = true;
		}elseif($adventure->player_adventure_role == 'npc'){
			$isNPC = true;
		}
		$isEarned = $a->player_id==$current_user->ID ? true : false;
		
		$data['achievement']=$a;
		$data['achievement_content']=apply_filters("the_content",$a->achievement_content);
		if(isset($a->achievement_applied)){
			$earned = date('jS, M Y', strtotime($a->achievement_applied));
			$data['achievement']->achievement_earned = __("Earned: ","bluerabbit")." $earned";
		}
		
		$msg_content = __("Achievement loaded",'bluerabbit');
		$data['message'] = $n->pop($msg_content, 'green','check');
		$data['just_notify'] =true;
	}else{
		$msg_content = __("Achievement doesn't exist",'bluerabbit');
		$data['message'] = $n->pop($msg_content, 'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
/// Load Guild Card
function loadGuildCard(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$guild_id = $_POST['guild_id'];
	$g = $wpdb->get_row("SELECT 
		guild.*, player.player_id, player.guild_enroll_date FROM {$wpdb->prefix}br_guilds guild
		LEFT JOIN {$wpdb->prefix}br_player_guild player
		ON player.guild_id = guild.guild_id 
		WHERE guild.guild_id=$guild_id
	");
	
	if($g){
		$adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
		JOIN {$wpdb->prefix}br_player_adventure c
		ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
		WHERE a.adventure_id=$g->adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
		$isGM = false;
		if($adventure->adventure_owner == $current_user->ID){
			$isGM = true;
			$isOwner = true;
		}elseif($adventure->player_adventure_role == 'gm'){
			$isGM = true;
		}elseif($adventure->player_adventure_role == 'npc'){
			$isNPC = true;
		}
		$isAdmin = $roles[0]=='administrator' ? true : false;
		$guild_players = $wpdb->get_results("SELECT
			a.guild_id, a.guild_name,a.guild_logo, a.guild_color,c.player_hexad, c.player_hexad_slug, c.player_id, c.player_display_name, c.player_picture, d.achievement_name, e.player_xp, e.player_bloo, e.player_adventure_role
			FROM {$wpdb->prefix}br_guilds a
			JOIN {$wpdb->prefix}br_player_guild b
			ON a.guild_id = b.guild_id AND a.adventure_id=b.adventure_id
			JOIN {$wpdb->prefix}br_players c
			ON b.player_id = c.player_id
			JOIN {$wpdb->prefix}br_player_adventure e
			ON b.player_id = e.player_id  AND e.player_adventure_role = 'player'
			LEFT JOIN {$wpdb->prefix}br_achievements d
			ON e.achievement_id = d.achievement_id
			WHERE a.adventure_id=$g->adventure_id AND a.guild_status='publish' AND a.guild_id=$g->guild_id GROUP BY c.player_id
		");
		$guild_xp = $guild_bloo =0; 
		foreach($guild_players as $gp){
			$guild_xp += $gp->player_xp;
			$guild_bloo += $gp->player_bloo;
		}
		$update = $wpdb->query("UPDATE {$wpdb->prefix}br_guilds SET guild_xp=$guild_xp, guild_bloo=$guild_bloo WHERE guild_id=$g->guild_id");

		$theFile = (get_template_directory()."/card-guild.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		$data['message'] = "<h1>".__("Guild doesn't exist","bluerabbit")."</h1>";;
		echo json_encode($data);
	}
	die();
}
/// Load ITEM Card
function loadItemCard(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$isAdmin = $roles[0]=='administrator' ? true : false;
	$adventure_id = $_POST['adventure_id'];
	$item_id = $_POST['item_id'];
	$item = $wpdb->get_row("SELECT 
		item.*, COUNT(DISTINCT trnxs.trnx_id) AS purchased, COUNT(DISTINCT player_trnxs.trnx_modified) AS bought, player_trnxs.player_id, trnxs.trnx_date, trnxs.trnx_id
		
		FROM {$wpdb->prefix}br_items item
		LEFT JOIN {$wpdb->prefix}br_transactions trnxs
		ON trnxs.object_id = item.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable')

		LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
		ON player_trnxs.object_id = item.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable') AND player_trnxs.player_id=$current_user->ID AND trnxs.trnx_use=0 AND trnxs.adventure_id=$adventure_id
		WHERE item.item_id=$item_id GROUP BY item.item_id
	");
	if($item){
		$adventure = getAdventure($item->adventure_id);
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;


		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date("Y-m-d H:i:s");
		$theFile = (get_template_directory()."/card-item.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		$notification = new Notification();
		$msg_content = __("Item doesn't exist",'bluerabbit');
		$data['message'] = $notification->pop($msg_content, 'red','cancel');
		$data['just_notify'] =true;
		echo json_encode($data);
	}
	die();
}
/// Load ITEM Card
function loadBackpackItem(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$isAdmin = $roles[0]=='administrator' ? true : false;
	$item_id = $_POST['item_id'];
	$item = $wpdb->get_row("SELECT 
		item.*, player_trnxs.player_id, trnxs.trnx_date, trnxs.trnx_id
		
		FROM {$wpdb->prefix}br_items item
		LEFT JOIN {$wpdb->prefix}br_transactions trnxs
		ON trnxs.object_id = item.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable')

		LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
		ON player_trnxs.object_id = item.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable') AND player_trnxs.player_id=$current_user->ID AND trnxs.trnx_use=0
		WHERE item.item_id=$item_id GROUP BY item.item_id
	");
	if($item){
		$theFile = (get_template_directory()."/card-backpack-item.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>".__("Item doesn't exist","bluerabbit")."</h1>";
	}
	die();
}
function loadLore(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$isAdmin = $roles[0]=='administrator' ? true : false;
	$lore_id = $_POST['lore_id'];
	$lore = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$lore_id AND quest_status='publish'");
	if($lore){
		$theFile = (get_template_directory()."/lore.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
	}else{
		echo "<h1>".__("Can't find this resource","bluerabbit")."</h1>";
	}
	die();
}
function searchLore(){
	global $wpdb;
	$data=array();
	$search_str = $_POST['search_string'];
	$search_str = '%'.$search_str.'%';
	$adventure_id = $_POST['adventure_id'];

	//SELECT * FROM `c0d_1mX_br_quests` WHERE `quest_title` LIKE '%volt%' AND `quest_content` LIKE '%voltage%' 
	$lores = "SELECT * FROM {$wpdb->prefix}br_quests WHERE (`quest_title` LIKE %s OR `quest_content` LIKE %s) AND quest_type ='lore' AND quest_status='publish' AND adventure_id=$adventure_id";
	$lores = $wpdb->get_results($wpdb->prepare($lores, $search_str, $search_str));;
	if($lores){
		foreach($lores as $key=>$b){
			$theFile = (get_template_directory()."/lore-item.php");
			if(file_exists($theFile)) {
				include ($theFile);
			}
		}
	}else{
		echo "<h1 class='white-color text-center font _30 w900 uppercase'>".__("No results found. Search for something different?","bluerabbit")."</h1>";
	}
	die();
}
/// Load Sidebar
function loadSidebar(){
	global $wpdb; $current_user = wp_get_current_user();
	$data=array();
	$roles = $current_user->roles;
	$isAdmin = $roles[0]=='administrator' ? true : false;
	$filename = $_POST['filename'];
	$adventure_id = $_POST['adventure_id'];
	$adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
	JOIN {$wpdb->prefix}br_player_adventure c
	ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
	WHERE a.adventure_id=$adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
	$current_player = getPlayerAdventureData($adventure_id,$current_user->ID); 
	$isGM = false;
	if($adventure->adventure_owner == $current_user->ID){
		$isGM = true;
		$isOwner = true;
	}elseif($adventure->player_adventure_role == 'gm'){
		$isGM = true;
	}elseif($adventure->player_adventure_role == 'npc'){
		$isNPC = true;
	}
	$theFile = (get_template_directory()."/sidebar-$filename.php");
	if(file_exists($theFile)) {
		include ($theFile);
	}
	die();
}

//////////// UPDATE ADVENTURE 
function updateAdventure(){
	global $wpdb;
	$current_user = wp_get_current_user();
	
	if($current_user->roles[0] == 'administrator'){
		$features = getFeatures('admin');
		$f_role = 'admin';
	}elseif($current_user->roles[0] == 'br_game_master' || $roles[0] == 'br_npc'){
		$features = getFeatures('pro');
		$f_role = 'pro';
	}else{
		$features = getFeatures('free');
		$f_role = 'free';
	}
	$playerData = getPlayerData($current_user->ID);
	$config = getSysConfig();
	$myAdventures = $wpdb->get_col("SELECT adventure_id FROM {$wpdb->prefix}br_adventures WHERE adventure_owner=$current_user->ID");
	if(count($myAdventures) >= $features['max_adventures'][$f_role]){
		$add_adventure = false;
	}else{
		$add_adventure = true;
	}

	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_adventure_nonce')) {
		
		
		$adventure_data = $_POST['adventure_data'];
		$adventure_id = $adventure_data['adventure_id'];
		$old_adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if($old_adventure){ $add_adventure = true; }
		if($add_adventure){
			$adventure_owner = $adventure_data['adventure_owner'];
			$adventure_badge = $adventure_data['adventure_badge'];
			$adventure_logo = $adventure_data['adventure_logo'];
			$adventure_certificate_signature = $adventure_data['adventure_certificate_signature'];
			$adventure_gmt = $adventure_data['adventure_gmt'];
			$adventure_title = stripslashes_deep($adventure_data['adventure_title']);
			$adventure_xp_label = $adventure_data['adventure_xp_label'];
			$adventure_bloo_label = $adventure_data['adventure_bloo_label'];
			$adventure_ep_label = $adventure_data['adventure_ep_label'];
			$adventure_xp_long_label = $adventure_data['adventure_xp_long_label'];
			$adventure_bloo_long_label = $adventure_data['adventure_bloo_long_label'];
			$adventure_ep_long_label = $adventure_data['adventure_ep_long_label'];
			$adventure_grade_scale = $adventure_data['adventure_grade_scale'];
			$adventure_type = $adventure_data['adventure_type'];
			$adventure_progression_type = $adventure_data['adventure_progression_type'];
			$adventure_privacy = $adventure_data['adventure_privacy'];
			$adventure_status = $adventure_data['adventure_status'];
			$adventure_instructions = stripslashes_deep($adventure_data['adventure_instructions']);
			$adventure_nickname = $adventure_data['adventure_nickname'];
			$adventure_level_up_array = serialize($adventure_data['adventure_level_up_array']);
			$adventure_color = $adventure_data['adventure_color'];
			$adventure_hide_schedule = $adventure_data['adventure_hide_schedule'];
			$adventure_hide_quests = $adventure_data['adventure_hide_quests'];
			$adventure_has_guilds = $adventure_data['adventure_has_guilds'];
			$unenrolled = $adventure_data['unenrolled'];
			$adventure_ranks = $adventure_data['adventure_ranks'];
			$adventure_settings = $adventure_data['adventure_settings'];

			date_default_timezone_set($adventure_gmt);
			$today = date('Y-m-d h:i:s');
			$adventure_date_modified = date("Y-m-d H:i:s");
			if($adventure_data['adventure_start_date']){
				$adventure_start_date = date('Y-m-d H:i:s',strtotime($adventure_data['adventure_start_date']));
			}
			if($adventure_data['adventure_end_date']){
				$adventure_end_date = date('Y-m-d H:i:s',strtotime($adventure_data['adventure_end_date']));
			}

			if(!$adventure_title){
				$errors[] = __("The adventure name can't be empty","bluerabbit");
			}
			if($adventure_progression_type == 'after' && $adventure_grade_scale == 'none'){
				$errors[] = __("You can't assign rewards after grading if no grading scale is set","bluerabbit");
			}
			if(!$old_adventure->adventure_code){
				$first_str = random_str(12,'1234567890abcdef');
				$code_string = $first_str.$current_user->ID;
				$adventure_code = str_shuffle($code_string);
			}else{
				$adventure_code = $old_adventure->adventure_code;
			}
			if(!$old_adventure->adventure_topic_id){
				$notification_topic = random_str(12,'1234567890abcdef');
				$adventure_topic_id = "topicID".str_shuffle($notification_topic);
			}else{
				$adventure_topic_id = $old_adventure->adventure_topic_id;
			}
			
			if(!$old_adventure){
				$adventure_settings = $features;
			}
			
			$sql = "INSERT INTO {$wpdb->prefix}br_adventures ( adventure_id, adventure_owner, adventure_date_modified, adventure_badge, adventure_gmt, adventure_title, adventure_xp_label, adventure_bloo_label, adventure_ep_label, adventure_xp_long_label, adventure_bloo_long_label, adventure_ep_long_label, adventure_grade_scale, adventure_progression_type, adventure_privacy, adventure_status, adventure_instructions, adventure_nickname, adventure_code, adventure_color, adventure_start_date, adventure_end_date, adventure_hide_quests, adventure_topic_id, adventure_hide_schedule, adventure_has_guilds, adventure_type, adventure_certificate_signature, adventure_logo)
			VALUES (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
			adventure_owner=%d, adventure_date_modified=%s, adventure_badge=%s, adventure_gmt=%s, adventure_title=%s, adventure_xp_label=%s, adventure_bloo_label=%s, adventure_ep_label=%s, adventure_xp_long_label=%s, adventure_bloo_long_label=%s, adventure_ep_long_label=%s, adventure_grade_scale=%s, adventure_progression_type=%s, adventure_privacy=%s, adventure_status=%s, adventure_instructions=%s, adventure_nickname=%s, adventure_code=%s, adventure_color=%s, adventure_start_date=%s, adventure_end_date=%s, adventure_hide_quests=%s, adventure_topic_id=%s, adventure_hide_schedule=%s, adventure_has_guilds=%s, adventure_type=%s, adventure_certificate_signature=%s, adventure_logo=%s";
			$sql = $wpdb->prepare($sql,
			$adventure_id, $adventure_owner, $adventure_date_modified, $adventure_badge, $adventure_gmt, $adventure_title, $adventure_xp_label, $adventure_bloo_label, $adventure_ep_label, $adventure_xp_long_label, $adventure_bloo_long_label, $adventure_ep_long_label, $adventure_grade_scale, $adventure_progression_type, $adventure_privacy, $adventure_status, $adventure_instructions, $adventure_nickname, $adventure_code, $adventure_color, $adventure_start_date, $adventure_end_date, $adventure_hide_quests, $adventure_topic_id, $adventure_hide_schedule, $adventure_has_guilds, $adventure_type, $adventure_certificate_signature, $adventure_logo,
			$adventure_owner, $adventure_date_modified, $adventure_badge, $adventure_gmt, $adventure_title, $adventure_xp_label, $adventure_bloo_label, $adventure_ep_label, $adventure_xp_long_label, $adventure_bloo_long_label, $adventure_ep_long_label, $adventure_grade_scale, $adventure_progression_type, $adventure_privacy, $adventure_status, $adventure_instructions, $adventure_nickname, $adventure_code, $adventure_color, $adventure_start_date, $adventure_end_date, $adventure_hide_quests, $adventure_topic_id, $adventure_hide_schedule, $adventure_has_guilds, $adventure_type, $adventure_certificate_signature, $adventure_logo);
			
			if(!$errors){
				$wpdb->query($sql); $the_just_updated_id = $wpdb->insert_id;
				if($the_just_updated_id){
					if($adventure_id){
						$ranksDELETE .= "DELETE FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=%d;";
						$delete =$wpdb->query( $wpdb->prepare("$ranksDELETE ", $adventure_id));
						if($adventure_ranks){
							$ranks_ph = array();
							$ranks_values = array();
							$ranksSQL .= "INSERT INTO {$wpdb->prefix}br_adventure_ranks (`adventure_id`, `rank_level`, `achievement_id`)  VALUES";
							foreach($adventure_ranks as $r){
								$message = stripslashes_deep($r['message']);
								array_push($ranks_values, $adventure_id, $r['level'], $r['achievement']);
								$ranks_ph[] = "(%d, %d, %d)";
							}
							$ranksSQL .= implode(', ', $ranks_ph);
							$ranks_insert =$wpdb->query( $wpdb->prepare("$ranksSQL ", $ranks_values));
						}
						$data['message'] .= '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Updated!","bluerabbit").'</strong></h4>';
					}else{
						$adventure_id = $wpdb->insert_id;
						$data['message'] .= '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Created!","bluerabbit").'</strong></h4>';
					}
					
					$sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_role) VALUES (%d,%d,%s)
					ON DUPLICATE KEY UPDATE player_adventure_role=%s, player_adventure_status='%s'";
					$sql = $wpdb->prepare ($sql,$adventure_id,$current_user->ID,'gm', 'gm', 'in');
					
					$wpdb->query($sql);
					$data['success'] = true;
					logActivity($adventure_id,'update','adventure');
					$data['location'] = get_bloginfo('url').'/new-adventure/?adventure_id='.$adventure_id;
					
					$saveSettings = saveSettingsProcess($adventure_settings, $adventure_id);
					if($saveSettings){
						logActivity($adventure_id,'adv-settings-updated','adventure');
						//$data['message'] .= '<h3>'.__("Features saved","bluerabbit").'</h5>';
					}else{
						logActivity($adventure_id,'adv-settings-not-updated','adventure');
						//$data['message'] .= '<h3 class="font w100 white-color">'.__("Features unchanged","bluerabbit").'</h5>';
					}
				}else{
					$data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Data Base Error. Can't insert/update adventure","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
				}
				$data['message'] .= '<h5>'.__("click to close","bluerabbit").'</h5>';

				
			}else{
				$data['message'] = '<span class="icon icon-xl icon-warning"></span><h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Please Fix the following errors","bluerabbit").'</strong></h4>';
				foreach($errors as $e){
					$data['message'].="<h3>$e</h3>";
				}
			}
		}else{
			$data['message'] .= '<h1><strong>'.__("Max Adventures Reached","bluerabbit").'</strong></h1>';
			$data['message'].= '<h4><strong>'.__("You must delete one of your adventures to create a new one","bluerabbit").'</strong></h4>';
			$data['message'].= '<h5>'.__("click to close","bluerabbit").'</h5>';
		}
	}else{
		$data['message'] .= '<span class="icon icon-cancel red-400 font _70"></span>';
		$data['message'] .= '<h1><strong>'.__("Unauthorized access","bluerabbit").'</strong></h1>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();
}
function loadStory($adv_id=null){
	global $wpdb; 
	$data=array();
	$adventure_id = $adv_id ? $adv_id : $_POST['adventure_id'];
	$adventure = getAdventure($adventure_id);
	$notification = new Notification();
	if($adventure->adventure_instructions){
		$theFile = (get_template_directory()."/about-adventure.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}else{
			$msg_content = __("Content doesn't exist",'bluerabbit');
			$data['message'] = $notification->pop($msg_content, 'red','cancel');
			$data['just_notify'] =true;
			echo json_encode($data);
		}
	}
	die();
}




/// Update Quest
function updateQuest(){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_quest_nonce')) {
		$config = getSysConfig();
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
		$quest_reqs = $quest_data['quest_reqs'];
		$quest_achievement_reqs = $quest_data['quest_achievement_reqs'];
		$quest_libs = $quest_data['quest_libs'];
		$quest_item_required = $quest_data['quest_item_required'];
		$quest_color = $quest_data['quest_color'];
		$quest_order = $quest_data['quest_order'];
		$quest_icon = $quest_data['quest_icon'];
		$quest_mechs = $quest_data['quest_mechs'];
		$quest_author = $current_user->ID;
		$quest_secondary_headline = stripslashes_deep($quest_data['quest_secondary_headline']);
		$quest_style = $quest_data['quest_style'];
		$quest_objectives = $quest_data['quest_objectives'];
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
				`quest_order`, `quest_guild`
			)
			VALUES (%d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,  %s, %d, %d, %d, %d, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d)
			
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
				`quest_order`=%d, `quest_guild`=%d
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
				$quest_order, $quest_guild,
				
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
				$quest_order, $quest_guild
			);
			$wpdb->query($sql);
			if($wpdb->insert_id){
				$quest_id = $wpdb->insert_id;
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
				if($steps_order){
					reorderStepProcess($steps_order);
				}
				$data['success']=true;
				if(!$quest_data['quest_id']){
					$data['location']=get_bloginfo('url')."/new-$quest_type/?adventure_id=$adventure_id&questID=$quest_id";
					logActivity($adventure_id,'add','quest','',$quest_id);
				}else{
					logActivity($adventure_id,'update','quest','',$quest_id);
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
					logActivity($adventure_id,'update','quest-children','',$quest_id);
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
/////////////////////// RATE QUEST /////////////////////////
function rateQuest(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	$quest_id = $_POST['quest_id'];
	$rating = $_POST['rating'];
	$sql = "UPDATE {$wpdb->prefix}br_player_posts SET pp_quest_rating=%d WHERE player_id=%d AND quest_id=%d";
	$sql = $wpdb->query( $wpdb->prepare($sql, $rating, $current_user->ID, $quest_id));
	$stars = "";
	for($i=0;$i<$rating;$i++){
		$stars .='<span class="icon-button font _24 sq-40  amber-bg-400"><span class="icon icon-star"></span></span>';
	}
	logActivity($adventure_id,'rated','quest','',$quest_id);
	$data['message'] = '<h1><strong>'.__("Rating updated!","bluerabbit").'</strong></h1>'.$stars.'<h5>'.__("click to close","bluerabbit").'</h5>';
	$data['success']=true;
	$data['rating']=$rating;
	echo json_encode($data);
	die();
}
/////////////////////// UPDATE Encounter /////////////////////////
function updateEncounter(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_encounter_nonce')) {
		$enc_data = $_POST['encounter_data'];
		$adventure_id = $_POST['adventure_id'];
		$adventure = getAdventure($adventure_id);
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	

		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$id = $enc_data['id'];
		$status = $enc_data['status'];
		$question = stripslashes_deep($enc_data['question']);
		$correct = stripslashes_deep($enc_data['correct']);
		$decoy1 = stripslashes_deep($enc_data['decoy1']);
		$decoy2 = stripslashes_deep($enc_data['decoy2']);
		$level = $enc_data['level'];
		$xp = $enc_data['xp'];
		$ep = $enc_data['ep'] ? $enc_data['ep'] : 10;
		$bloo = $enc_data['bloo'];
		$color = $enc_data['color'] ? $enc_data['color'] : 'blue';
		$badge = $enc_data['badge'];
		$icon = $enc_data['icon'];
		$today = date('Y-m-d H:i:s');
		$path = $enc_data['path'];


		if(!$question){
			$errors[] = __("Add a question","bluerabbit");
		}
		if(!$correct){
			$errors[] = __("Add a correct choice","bluerabbit");
		}
		if(!$adventure){
			$errors[] = __("Adventure doesn't exist","bluerabbit");
		}
		if(!$errors){
			$sql = "INSERT INTO {$wpdb->prefix}br_encounters (`enc_id`, `adventure_id`, `achievement_id`, `enc_question`, `enc_right_option`, `enc_decoy_option1`, `enc_decoy_option2`, `enc_badge`, `enc_color`, `enc_icon`, `enc_status`, `enc_xp`, `enc_bloo`, `enc_ep`, `enc_level`, `enc_date`, `enc_modified`)
			
			VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %s, %s)
			ON DUPLICATE KEY UPDATE
			
			`achievement_id`=%d, `enc_question`=%s, `enc_right_option`=%s, `enc_decoy_option1`=%s,  `enc_decoy_option2`=%s, `enc_badge`=%s, `enc_color`=%s, `enc_icon`=%s, `enc_status`=%s, `enc_xp`=%d, `enc_bloo`=%d, `enc_ep`=%d, `enc_level`=%d, `enc_modified`=%s"
			;
			$sql = $wpdb->prepare($sql,$id, $adv_child_id, $path, $question, $correct, $decoy1, $decoy2, $badge, $color, $icon, $status, $xp, $bloo, $ep, $level, $today, $today, $path, $question, $correct, $decoy1, $decoy2, $badge, $color, $icon, $status, $xp, $bloo, $ep, $level, $today );
			
			$the_query = $wpdb->query($sql);
			$enc_id = $wpdb->insert_id;
			$data['message'] = "<h1><strong>".__("Encounter Updated!","bluerabbit")."</strong></h1>";
			$data['success'] = true;
			if(!$enc_data['id']){
				logActivity($adv_child_id,'add','encounter','',$enc_id);
			}else{
				logActivity($adv_child_id,'update','encounter','',$id);
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
/////////////////////// UPDATE SPONSOR /////////////////////////
function updateOrg(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_org_nonce')) {
		$org_data = $_POST['org_data'];
		$id = $org_data['id'];
		$name = $org_data['name'];
		$logo = $org_data['logo'];
		$color = $org_data['color'];
		$status = $org_data['status'];
		$about = stripslashes_deep($org_data['about']);
		
		$today = date("Y-m-d H:i:s");
		
		$sql = "INSERT INTO {$wpdb->prefix}br_orgs 
		(`org_id`,`org_name`,`org_logo`,`org_content`,`org_color`,`owner_id`,`org_status`)
		VALUES (%d, %s, %s, %s, %s, %d, %s)
		ON DUPLICATE KEY UPDATE

		`org_name`=%s,`org_logo`=%s,`org_content`=%s,`org_color`=%s,`owner_id`=%d,`org_status`=%s,`org_modified`=%s
		";
		$sql = $wpdb->prepare($sql, $id, $name, $logo,$about, $color,  $current_user->ID, $status, $name, $logo,$about, $color,  $current_user->ID, $status, $today);
		
		$the_query = $wpdb->query($sql);
		$org_id = $wpdb->insert_id;
		$n = new Notification();
		
		$msg_content = __('Organization Saved!','bluerabbit');
		$data['message'] = $n->pop($msg_content,'green');
		$data['success'] = true;
		if($id){
			logActivity(0,'update','org','',$org_id);
		}else{
			logActivity(0,'add','org','',$org_id);
		}
		$data['just_notify'] =true;
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
/////////////////////// UPDATE SPONSOR /////////////////////////
function updateSponsor(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_sponsor_nonce')) {
		$sponsor_data = $_POST['sponsor_data'];
		$adventure_id = $_POST['adventure_id'];
		$id = $sponsor_data['id'];
		$name = $sponsor_data['name'];
		$url = $sponsor_data['url'];
		$logo = $sponsor_data['logo'];
		$color = $sponsor_data['color'];
		$level = $sponsor_data['level'];
		$image = $sponsor_data['image'];
		$about = $sponsor_data['about'];
		$twitter = $sponsor_data['twitter'];
		$linkedin = $sponsor_data['linkedin'];
		$sql = "INSERT INTO {$wpdb->prefix}br_sponsors (`sponsor_id`, `adventure_id`, `sponsor_name`, `sponsor_url`, `sponsor_logo`, `sponsor_color`, `sponsor_level`, `sponsor_image`, `sponsor_about`, `sponsor_twitter`, `sponsor_linkedin`)

		VALUES (%d, %d, %s, %s, %s, %s, %d, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE

		`adventure_id`=%d, `sponsor_name`=%s, `sponsor_url`=%s, `sponsor_logo`=%s, `sponsor_color`=%s, `sponsor_level`=%d, `sponsor_image`=%s, `sponsor_about`=%s, `sponsor_twitter`=%s, `sponsor_linkedin`=%s
		";
		$sql = $wpdb->prepare($sql, $id, $adventure_id, $name, $url, $logo, $color, $level, $image, $about, $twitter, $linkedin, $adventure_id, $name, $url, $logo, $color, $level, $image, $about, $twitter, $linkedin);
		
		$the_query = $wpdb->query($sql);
		$sponsor_id = $wpdb->insert_id;
		$n = new Notification();
		
		$msg_content = __('Sponsor Saved!','bluerabbit');
		$data['message'] = $n->pop($msg_content,'green');
		$data['success'] = true;
		if($id){
			logActivity($adventure_id,'update','sponsor','',$sponsor_id);
		}else{
			logActivity($adventure_id,'add','sponsor','',$sponsor_id);
		}
		$data['just_notify'] =true;
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
/////////////////////// UPDATE ACHIEVEMENT /////////////////////////
function updateAchievement(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_achievement_nonce')) {
		$a_data = $_POST['achievement_data'];
		$adventure_id = $a_data['adventure_id'];

		$adventure = getAdventure($adventure_id);
		$adv_child_id = $adventure->adventure_id;
		$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	

		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$a_id = $a_data['a_id'];
		$a_status = $a_data['a_status'];
		$a_name = stripslashes_deep($a_data['a_name']);
		$a_xp = $a_data['a_xp'];
		$a_ep = $a_data['a_ep'];
		$a_bloo = $a_data['a_bloo'];
		$a_color = $a_data['a_color'];
		$a_badge = $a_data['a_badge'];
		$a_display = $a_data['a_display'];
		$a_group = $a_data['a_group'];
		$a_path = $a_data['a_path'];
		$a_max = $a_data['a_max'];
		$a_deadline = !$a_data['a_deadline'] ? "" : date('Y-m-d H:i:s',strtotime($a_data['a_deadline']));
		$a_content = stripslashes_deep($a_data['a_content']);
		$magic_code = trim(strtolower($a_data['magic_code']));
		$awarded_players = $a_data['awarded_players'];
		$libs = $a_data['libs'];
		
		if(!isset($a_data['id'])){
			$ref_id = random_str(8,'1234567890abcdef');
		}

		if(!$a_name){
			$errors[] = __("Please add an achievement name","bluerabbit");
		}
		if(!$a_badge){
			$errors[] = __("Please add an image for the achievement","bluerabbit");
		}
		if($a_display != 'path'){
			$a_group="";
		}
		if(!$a_color){
			$a_color='amber';
		}
		if(!$errors){
			$total_achievements = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_status='publish'");
			$a_order = count($total_achievements);
			$sql = "INSERT INTO {$wpdb->prefix}br_achievements (achievement_id, adventure_id, achievement_xp, achievement_ep, achievement_bloo, achievement_name, achievement_badge, achievement_status, achievement_color, achievement_code, achievement_content, achievement_deadline, achievement_max, achievement_order, achievement_display, achievement_path, achievement_group,ref_id)
			VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %d, %d, %s, %d, %s, %s)
			ON DUPLICATE KEY UPDATE
			adventure_id=%d, achievement_xp=%d, achievement_ep=%d, achievement_bloo=%d, achievement_name=%s, achievement_badge=%s, achievement_status=%s, achievement_color=%s, achievement_code=%s, achievement_content=%s, achievement_deadline=%s, achievement_max=%d, achievement_display=%s, achievement_path=%d, achievement_group=%s";
			$sql = $wpdb->prepare($sql,$a_id,$adv_parent_id,$a_xp, $a_ep, $a_bloo, $a_name, $a_badge, $a_status, $a_color, $magic_code, $a_content,$a_deadline, $a_max, $a_order, $a_display, $a_path, $a_group, $ref_id, $adventure_id,$a_xp, $a_ep, $a_bloo, $a_name, $a_badge, $a_status, $a_color, $magic_code, $a_content,$a_deadline,$a_max, $a_display, $a_path, $a_group);
			$a_query = $wpdb->query($sql);
			if($a_query){
				$updated_id = $wpdb->insert_id;
			}
			if($updated_id){
				$data['success']=true;
				if(!$a_id){
					$data['location']=get_bloginfo('url')."/new-achievement/?adventure_id=$adv_parent_id&achievement_id=$updated_id";
					logActivity($adv_parent_id,'add','achievement','',$updated_id);
				}else{
					logActivity($adv_parent_id,'update','achievement','',$a_id);
				}
				$data['message'] .= '<h1><strong>'.$a_name.'</strong></h1> <h4><strong>'.__("Achievement Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
				
				if($adventure->adventure_type=='template'){
					$children_update = "UPDATE {$wpdb->prefix}br_achievements SET achievement_xp=%d, achievement_ep=%d, achievement_bloo=%d, achievement_name=%s, achievement_badge=%s, achievement_status=%s, achievement_color=%s, achievement_code=%s, achievement_content=%s, achievement_deadline=%s, achievement_max=%d, achievement_display=%s, achievement_group=%s
					WHERE `achievement_parent`=$updated_id AND achievement_id!=$updated_id";
					
					$children_update = $wpdb->query( $wpdb->prepare("$children_update ",$a_xp, $a_ep, $a_bloo, $a_name, $a_badge, $a_status, $a_color, $magic_code, $a_content,$a_deadline, $a_max, $a_display, $a_group));
					logActivity($adv_parent_id,'update','achievement-children','',$updated_id);
				}
				
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert achievement","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
/////////////////////// UPDATE SPEAKER /////////////////////////
function updateSpeaker(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_speaker_nonce')) {
		$speaker_data = $_POST['speaker_data'];
		$speaker_id = $speaker_data['id'];
		$speaker_player_id = $speaker_data['player_id'];
		$adventure_id = $speaker_data['adventure_id'];
		$speaker_first_name = stripslashes_deep($speaker_data['first_name']);
		$speaker_last_name = stripslashes_deep($speaker_data['last_name']);
		$speaker_bio = stripslashes_deep($speaker_data['bio']);
		$speaker_picture = $speaker_data['picture'];
		$speaker_company = stripslashes_deep($speaker_data['company']);
		$speaker_website = stripslashes_deep($speaker_data['website']);
		$speaker_twitter = stripslashes_deep($speaker_data['twitter']);
		$speaker_linkedin = stripslashes_deep($speaker_data['linkedin']);
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		if(!$speaker_first_name){
			$errors[] = __("Speaker name is required","bluerabbit");
		}
		if(!$errors){
			$sql = "INSERT INTO {$wpdb->prefix}br_speakers
			(`speaker_id`, `player_id`, `adventure_id`, `speaker_first_name`, `speaker_last_name`, `speaker_bio`, `speaker_picture`, `speaker_company`, `speaker_website`, `speaker_linkedin`, `speaker_twitter`)
			VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
			`player_id`=%d, `adventure_id`=%d, `speaker_first_name`=%s, `speaker_last_name`=%s, `speaker_bio`=%s, `speaker_picture`=%s, `speaker_company`=%s, `speaker_website`=%s, `speaker_linkedin`=%s, `speaker_twitter`=%s";
			$sql = $wpdb->prepare($sql,$speaker_id, $speaker_player_id, $adventure_id, $speaker_first_name, $speaker_last_name, $speaker_bio, $speaker_picture, $speaker_company, $speaker_website,  $speaker_linkedin, $speaker_twitter, $speaker_player_id, $adventure_id, $speaker_first_name, $speaker_last_name, $speaker_bio, $speaker_picture, $speaker_company, $speaker_website, $speaker_linkedin, $speaker_twitter);
			$sql = $wpdb->query($sql);
			$updated_id = $wpdb->insert_id;

			if($sql !== FALSE ){
				$data['success']=true;
				if(!$speaker_id){
					$data['location']=get_bloginfo('url')."/new-speaker/?adventure_id=$adventure_id&speaker_id=$updated_id";
					logActivity($adventure_id,'add','speaker','',$updated_id);
				}else{
					logActivity($adventure_id,'update','speaker','',$speaker_id);
				}
				$data['message'] .= '<h1><strong>'.$speaker_first_name." ".$speaker_last_name.'</strong></h1> <h4><strong>'.__("Speaker updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert speaker","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();
}

function uploadBulkQuests(){
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
function uploadBulkQuestions(){
	global $wpdb; 
	$data = array();
	$n = new Notification();
	$adv_id = $_POST['adventure_id'];
	$quest_id = $_POST['quest_id'];
	$questions_counter = 0;
	$bulk_options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id,  answer_value, answer_correct) VALUES ";
	$options_values = [];
	$options_place_holders = [];
	if (isset($_FILES['csv_file']['tmp_name'])) {
		$file = $_FILES['csv_file']['tmp_name'];
		if (!is_readable($file)) {
			$data['errors'][] = __("File not readable.","bluerabbit");
		}
		if (empty($file) || !file_exists($file)) {
			$data['errors'][] = __("No file uploaded.","bluerabbit");
		}
		if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
			while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
				if ($row_index == 0) {
					// Skip the header row (optional)
					$row_index++;
					continue;
				}
				if($row_index <=150){
					$qd = [
						'question'=>sanitize_text_field($file_data[0]),
						'correct'=>sanitize_text_field($file_data[1]),
						'decoy1'=>sanitize_text_field($file_data[2]),
						'decoy2'=>sanitize_text_field($file_data[3]),
						'decoy3'=>sanitize_text_field($file_data[4])
					];
					if($qd['question'] && $qd['correct'] && $qd['decoy1']){ 
						$questions_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (quest_id, question_title) VALUES (%d, %s)";
						$qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $quest_id, $qd['question']));
						$qKey = $wpdb->insert_id;
						logActivity($adv_id,'add','challenge-question','', $quest_id);
						if($qKey){
							if($qd['correct']){
								$options_place_holders[] = " (%d, %d, %s, %d)";
								array_push($options_values, $quest_id, $qKey, $qd['correct'], 1);
							}
							if($qd['decoy1']){
								$options_place_holders[] = " (%d, %d, %s, %d)";
								array_push($options_values, $quest_id, $qKey, $qd['decoy1'], 0);
							}
							if($qd['decoy2']){
								$options_place_holders[] = " (%d, %d, %s, %d)";
								array_push($options_values, $quest_id, $qKey, $qd['decoy2'], 0);
							}
							if($qd['decoy3']){
								$options_place_holders[] = " (%d, %d, %s, %d)";
								array_push($options_values, $quest_id, $qKey, $qd['decoy3'], 0);
							}
							$questions_counter++;	
							$msg_content = __("Question #",'bluerabbit')."$questions_counter ".__("added!",'bluerabbit');
							$data['messages'][] = $n->pop($msg_content,'green','check');
							logActivity($adv_id,'add','challenge-question-option','',$quest_id);
						}
					}else{
						$msg_content = __("Skipping empty row in file",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'grey','check');
					}
					$row_index++;
				}
			}
			
			fclose($handle);

			$bulk_options_query .= implode(', ', $options_place_holders);
			$bulk_options_query = $wpdb->query( $wpdb->prepare("$bulk_options_query ", $options_values));
			$msg_content = "$questions_counter ".__("questions uploaded correctly",'bluerabbit');
			
			$data['messages'][] = $n->pop($msg_content,'deep-purple','check');
			$data['success'] = true;
			$data['debug'] = print_r($data['messages'],true);
		}else{
			$data['errors'][] =__("Cannot open file to read","bluerabbit");
		}
	}else{
		$data["errors"][] = __("File doesn't exist","bluerabbit");
	}
	
	
	echo json_encode($data);
	die();
}
function uploadBulkItems(){
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
		
		$bulk_items_query = "INSERT INTO {$wpdb->prefix}br_items (  `adventure_id`, `item_author`, `item_name`, `item_description`, `item_cost`, `item_type`, `item_badge`,  `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `tabi_id`, `item_status` ) VALUES "; 


		$values = [];
		$place_holders = [];
		if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
			while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
				if ($row_index == 0) {
					$row_index++;
					continue;
				}
				if($row_index <=150){
					$item_data = [];
					$item_data['adventure_id']=$adv_id;
					$item_data['item_author']=sanitize_text_field($file_data[0]);
					$item_data['item_name']=sanitize_text_field($file_data[1]);
					$item_data['item_description']=sanitize_text_field($file_data[2]);
					$item_data['item_cost']=sanitize_text_field($file_data[3]);
					$item_data['item_type']=sanitize_text_field($file_data[4]);
					$item_data['item_badge']=sanitize_text_field($file_data[5]);
					$item_data['item_stock']=sanitize_text_field($file_data[6]);
					$item_data['item_player_max']=sanitize_text_field($file_data[7]);
					$item_data['item_level']=sanitize_text_field($file_data[8]);
					$item_data['item_category']=sanitize_text_field($file_data[9]);
					$item_data['item_order']=sanitize_text_field($file_data[10]);
					$item_data['tabi_id']=sanitize_text_field($file_data[11]);
					$item_data['item_status']=sanitize_text_field($file_data[12]);

					if($item_data['item_name']){ 
						array_push($values, $item_data['adventure_id'], $item_data['item_author'], $item_data['item_name'], $item_data['item_description'], $item_data['item_cost'], $item_data['item_type'], $item_data['item_badge'], $item_data['item_stock'], $item_data['item_player_max'], $item_data['item_level'], $item_data['item_category'], $item_data['item_order'], $item_data['tabi_id'], $item_data['item_status']); 

						$place_holders[] = " (%d, %d, %s, %s, %d, %s, %s,  %d, %d, %d, %s, %d, %d, %s)";


						$msg_content = __("Item ",'bluerabbit').$item_data['item_name'].__(" inserted correctly",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}else{
						$msg_content = __("Skipping empty row in file",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}
					$row_index++;
				}
			}
			
			fclose($handle);

			$bulk_items_query .= implode(', ', $place_holders);
			$bulk_items_query = $wpdb->query( $wpdb->prepare("$bulk_items_query ", $values));
			$data['debug'] = print_r($wpdb->last_result,true);
			$msg_content = __("Items uploaded correctly",'bluerabbit');
			
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
function uploadBulkAchievements(){
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
		
		$bulk_items_query = "INSERT INTO {$wpdb->prefix}br_achievements (`adventure_id`, `achievement_name`, `achievement_badge`, `achievement_color`, `achievement_code`, `achievement_content`, `achievement_display`, `achievement_xp`, `achievement_ep`, `achievement_bloo`, `achievement_max`, `achievement_order`) VALUES "; 



		$values = [];
		$place_holders = [];
		if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
			while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
				if ($row_index == 0) {
					$row_index++;
					continue;
				}
				if($row_index <=150){



					$a_data = [];
					$a_data['adventure_id']=$adv_id;
					$a_data['achievement_name']=sanitize_text_field($file_data[0]);
					$a_data['achievement_badge']=sanitize_text_field($file_data[1]);
					$a_data['achievement_color']=sanitize_text_field($file_data[2]);

					if($file_data[3] != ''){
						$a_data['achievement_code']=sanitize_text_field($file_data[3]);
					}else{
						$a_data['achievement_code']=random_str(30);
					}
					$a_data['achievement_content']=sanitize_text_field($file_data[4]);
					$a_data['achievement_display']=sanitize_text_field($file_data[5]);
					$a_data['achievement_xp']=sanitize_text_field($file_data[6]);
					$a_data['achievement_ep']=sanitize_text_field($file_data[7]);
					$a_data['achievement_bloo']=sanitize_text_field($file_data[8]);
					$a_data['achievement_max']=sanitize_text_field($file_data[9]);
					$a_data['achievement_order']=sanitize_text_field($file_data[10]);

					if($a_data['achievement_name'] && $a_data['achievement_badge']){ 
						array_push($values, $a_data['adventure_id'], $a_data['achievement_name'], $a_data['achievement_badge'], $a_data['achievement_color'], $a_data['achievement_code'], $a_data['achievement_content'], $a_data['achievement_display'], $a_data['achievement_xp'], $a_data['achievement_ep'], $a_data['achievement_bloo'], $a_data['achievement_max'], $a_data['achievement_order']); 

						$place_holders[] = " (%d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d)";


						$msg_content = __("Achievement ",'bluerabbit').$a_data['achievement_name'].__(" inserted correctly",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}else{
						$msg_content = __("Skipping empty row in file",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}
					$row_index++;
				}
			}
			
			fclose($handle);

			$bulk_items_query .= implode(', ', $place_holders);
			$bulk_items_query = $wpdb->query( $wpdb->prepare("$bulk_items_query ", $values));
			$data['debug'] = print_r($wpdb->last_result,true);
			$msg_content = __("Achievments uploaded correctly",'bluerabbit');
			
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
function uploadBulkSpeakers(){
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
		$bulk_speakers_query = "INSERT INTO {$wpdb->prefix}br_speakers (`adventure_id`, `speaker_first_name`, `speaker_last_name`, `speaker_bio`, `speaker_picture`, `speaker_company`, `speaker_website`, `speaker_linkedin`) VALUES  ";
		$values = [];
		$place_holders = [];
		if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
			while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
				if ($row_index == 0) {
					// Skip the header row (optional)
					$row_index++;
					continue;
				}
				if($row_index <=100){
					// Assuming the CSV file has columns: name, email, age
					$firstname = sanitize_text_field($file_data[0]);
					$lastname = sanitize_text_field($file_data[1]);
					$bio = sanitize_textarea_field($file_data[2]);
					$picture = sanitize_text_field($file_data[3]);
					$company = sanitize_text_field($file_data[4]);
					$website = sanitize_text_field($file_data[5]);
					$linkedin = sanitize_text_field($file_data[6]);
					
					$bio = trim($bio,'"');
					$picture = trim($picture,'"');
					$company = trim($company,'"');
					$website = trim($website,'"');
					$linkedin = trim($linkedin,'"');
					array_push($values, $adv_id, $firstname, $lastname,  $bio, $picture, $company, $website, $linkedin );
					$place_holders[] = " (%d, %s, %s, %s, %s, %s, %s, %s)";
					$msg_content = __("Speaker",'bluerabbit')." $firstname $lastname ".__("inserted correctly",'bluerabbit');
					$data['messages'][] = $n->pop($msg_content,'green','check');
					
					$row_index++;
				}
			}
			
			fclose($handle);

			$bulk_speakers_query .= implode(', ', $place_holders);
			$bulk_speakers_insert = $wpdb->query( $wpdb->prepare("$bulk_speakers_query ", $values));
			
			$msg_content = __("Speakers inserted correctly",'bluerabbit');
			$data['debug']= print_r($wpdb->last_query,true);
			$data['messages'][] = $n->pop($msg_content,'green','check');
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

function uploadBulkSessions(){
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
		$bulk_sessions_query = "INSERT INTO {$wpdb->prefix}br_sessions (`adventure_id`,`session_title`,`session_description`,`session_start`, `session_end`, `session_room`,`quest_id`, `speaker_id`, `achievement_id`, `guild_id`) VALUES  ";

/*
  session_title	 session_description	 session_start	 session_end	 session_room	adventure_id	 quest_id	 speaker_id	 achievement_id	 guild_id

			VALUES ( %s, %s, %s, %s, %s,%d, %d, %d, %d, %d)
*/
		
		$values = [];
		$place_holders = [];
		if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
			while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
				if ($row_index == 0) {
					// Skip the header row (optional)
					$row_index++;
					continue;
				}
				if($row_index <=100){
					
					// Assuming the CSV file has columns: name, email, age
					$session_title = sanitize_text_field($file_data[0]);
					$session_description = sanitize_textarea_field($file_data[1]);
					$session_start = sanitize_text_field($file_data[2]);
					$session_end = sanitize_text_field($file_data[3]);
					$session_room = sanitize_text_field($file_data[4]);
					$quest_id = sanitize_text_field($file_data[5]);
					$speaker_id = sanitize_text_field($file_data[6]);
					$achievement_id = sanitize_text_field($file_data[7]);
					$guild_id = sanitize_text_field($file_data[8]);
					
					$session_title = trim($session_title,'"');
					$session_description = trim($session_description,'"');
					
					if($session_title != "" && $session_description != ""){ 
						array_push($values, $adv_id, $session_title, $session_description,  $session_start, $session_end, $session_room, $quest_id, $speaker_id , $achievement_id , $guild_id ); 
						$place_holders[] = " (%d, %s, %s, %s, %s, %s, %d, %d, %d, %d)";
						$msg_content = __("Session",'bluerabbit')." $session_title ".__("inserted correctly",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}else{
						$msg_content = __("Skipping empty row in file",'bluerabbit');
						$data['messages'][] = $n->pop($msg_content,'green','check');
					}
					$row_index++;
				}
			}
			
			fclose($handle);

			$bulk_sessions_query .= implode(', ', $place_holders);
			$bulk_sessions_insert = $wpdb->query( $wpdb->prepare("$bulk_sessions_query ", $values));
			
			$msg_content = __("Schedule inserted correctly",'bluerabbit');
			$data['debug']= print_r($wpdb->last_query,true);
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








/////////////////////// UPDATE SESSION /////////////////////////
function updateSession(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_session_nonce')) {
		$session_data = $_POST['session_data'];
		$session_id = $session_data['id'];
		$adventure_id = $session_data['adventure_id'];
		$session_title = stripslashes_deep($session_data['title']);
		$session_room = stripslashes_deep($session_data['room']);
		$session_start = ($session_data['start']);
		$session_end = ($session_data['end']);
		$quest_id = ($session_data['quest_id']);
		$speaker_id = ($session_data['speaker_id']);
		$achievement_id = ($session_data['achievement_id']);
		$guild_id = ($session_data['guild_id']);
		$session_status = ($session_data['status']);
		$session_description = stripslashes_deep($session_data['description']);
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		if(!$session_title){
			$errors[] = __("Session Title is Required","bluerabbit");
		}
		if(!$session_start){
			$errors[] = __("The start time is required","bluerabbit");
		}
		if(!$session_end){
			$errors[] = __("The end time is required","bluerabbit");
		}
		if(!$errors){
			$sql = "INSERT INTO {$wpdb->prefix}br_sessions
			(`session_id`, `adventure_id`, `quest_id`, `speaker_id`,  `session_title`, `session_start`, `session_end`,`session_status`, `session_description`, `session_room`, `achievement_id`, `guild_id`)
			VALUES (%d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %d, %d)
			ON DUPLICATE KEY UPDATE
			`adventure_id`=%d, `quest_id`=%d, `speaker_id`=%d, `session_title`=%s, `session_start`=%s, `session_end`=%s, `session_status`=%s, `session_description`=%s , `session_room`=%s, `achievement_id`=%d , `guild_id`=%d ";
			$sql = $wpdb->prepare($sql,$session_id, $adventure_id, $quest_id, $speaker_id, $session_title, $session_start, $session_end, $session_status, $session_description, $session_room,   $achievement_id,  $guild_id,  $adventure_id, $quest_id, $speaker_id, $session_title, $session_start, $session_end, $session_status, $session_description , $session_room , $achievement_id , $guild_id );
			$sql = $wpdb->query($sql);
			$updated_session_id = $wpdb->insert_id;
			if($updated_session_id ){
				$data['success']=true;
				if(!$session_id){
					$data['location']=get_bloginfo('url')."/new-session/?adventure_id=$adventure_id&session_id=$updated_session_id";
					logActivity($adventure_id,'add','session','',$updated_session_id);
				}else{
					logActivity($adventure_id,'update','session','',$session_id);
				}
				$data['message'] .= '<h1><strong>'.$session_title.'</strong></h1> <h4><strong>'.__("Session updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert session","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();
}

/////////////////////// UPDATE TEAM /////////////////////////
function updateGuild(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_guild_nonce')) {
		$g_data = $_POST['guild_data'];
		$g_id = $g_data['g_id'];
		$g_status = $g_data['g_status'];
		$g_name = stripslashes_deep($g_data['g_name']);
		$g_group = stripslashes_deep($g_data['g_group']);
		$g_logo = $g_data['g_logo'];
		$g_capacity = $g_data['g_capacity'];
		$g_assign_on_login = $g_data['g_assign_on_login'];
		$g_color = $g_data['g_color'];
		$adventure_id = $g_data['adventure_id'];
		$guild_players = $g_data['guild_players'];
		if(!$g_name){
			$errors[] = __("Guild name is required","bluerabbit");
		}
		if(!$g_logo){
			$errors[] = __("Please add a logo for the guild","bluerabbit");
		}
		if(!$g_color){
			$g_color='deep-orange';
		}
		if(!$errors){
			$first_str = random_str(12,'1234567890abcdefghijkls');
			$code_string = $first_str.$current_user->ID;
			$guild_code = str_shuffle($code_string);
			
			$sql = "INSERT INTO {$wpdb->prefix}br_guilds (guild_id, adventure_id, guild_name, guild_logo, guild_status, guild_color, assign_on_login, guild_code, guild_group, guild_capacity)
			VALUES (%d, %d, %s, %s, %s, %s, %d, %s, %s, %d)
			ON DUPLICATE KEY UPDATE
			adventure_id=%d, guild_name=%s, guild_logo=%s, guild_status=%s, guild_color=%s, assign_on_login=%d, guild_group=%s, guild_capacity=%s";
			$sql = $wpdb->prepare($sql,$g_id,$adventure_id,$g_name, $g_logo, $g_status, $g_color, $g_assign_on_login, $guild_code, $g_group, $g_capacity, $adventure_id,$g_name, $g_logo, $g_status, $g_color, $g_assign_on_login, $g_group, $g_capacity);
			$b_query = $wpdb->query($sql);
			$updated_g_id = $wpdb->insert_id;
			if($updated_g_id){
				$data['success']=true;
				if(!$g_id){
					$data['location']=get_bloginfo('url')."/new-guild/?adventure_id=$adventure_id&guild_id=$updated_g_id";
					logActivity($adventure_id,'add','guild','',$updated_g_id);
				}else{
					logActivity($adventure_id,'update','guild','',$g_id);
				}
				$data['message'] .= '<h1><strong>'.$g_name.'</strong></h1> <h4><strong>'.__("Guild Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert guild","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}

/////////////////////// UPDATE Blocker /////////////////////////
function updateBlocker(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_blocker_nonce')) {
		$blocker_data = $_POST['blocker_data'];
		$adventure_id = $blocker_data['adventure_id'];
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$blocker_id = $blocker_data['blocker_id'];
		$blocker_description = stripslashes_deep($blocker_data['blocker_description']);
		$blocker_cost = $blocker_data['blocker_cost'];
		$blocker_date = date("Y-m-d H:i:s");
		$fined_players = $blocker_data['fined_players'];

		if($blocker_cost <= 0){
			$errors[] = __("Blocker must cost something","bluerabbit");
		}
		if(!$blocker_description){
			$errors[] = __("Explain the reason for this blocker. Provide evidence.","bluerabbit");
		}
		if(!$errors){
				// blocker_id adventure_id blocker_date blocker_cost blocker_description
			$sql = "INSERT INTO {$wpdb->prefix}br_blockers (blocker_id, adventure_id, blocker_cost, blocker_date, blocker_description)
			VALUES (%d, %d, %d, %s, %s)
			ON DUPLICATE KEY UPDATE
			adventure_id=%d, blocker_cost=%d, blocker_date=%s, blocker_description=%s";
			$sql = $wpdb->prepare($sql,$blocker_id,$adventure_id,$blocker_cost,$blocker_date, $blocker_description,$adventure_id,$blocker_cost,$blocker_date, $blocker_description);
			$b_query = $wpdb->query($sql);
			if(!$blocker_id){
				$blocker_id = $wpdb->insert_id;
				
				logActivity($adventure_id,'add','blocker','',$blocker_id);
			}else{
				logActivity($adventure_id,'update','blocker','',$blocker_id);
			}
			$DELETE_query = "DELETE FROM {$wpdb->prefix}br_player_blocker WHERE blocker_id=%d";
			$wpdb->query( $wpdb->prepare("$DELETE_query ", $blocker_id, $adventure_id));
			if($fined_players){
				$values = array();
				$place_holders = array();
				$blockers_query = "INSERT INTO {$wpdb->prefix}br_player_blocker (blocker_id, player_id) VALUES ";
				foreach($fined_players as $key => $q) {
					 array_push($values, $blocker_id, $q);
					 $place_holders[] = "(%d, %d)";
				}
				$blockers_query .= implode(', ', $place_holders);
				$blockers_insert = $wpdb->query( $wpdb->prepare("$blockers_query ", $values));
			}

			if($b_query || $blockers_insert){
				$data['success']=true;
				$data['location']=get_bloginfo('url')."/blockers/?adventure_id=$adventure_id";
				$data['message'] .= '<h1><span class="icon icon-lock"></span></h1><h2><strong>'.__("Blocker Updated!","bluerabbit").'</strong></h2> <h5>'.__("click to close","bluerabbit").'</h5>';
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert blocker","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}

/////////////////////// UPDATE ITEM /////////////////////////
function updateItem(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$roles = $current_user->roles;
	$data = array();
	$errors = array();
	if (wp_verify_nonce($_POST['nonce'], 'br_update_item_nonce')) {
		$item_data = $_POST['item_data'];
		$adventure_id = $item_data['adventure_id'];
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$item_id = $item_data['item_id'];
		$item_name = stripslashes_deep($item_data['item_name']);
		$item_post_modified = date("Y-m-d H:i:s");
		$item_stock = $item_data['item_stock'];
		$item_sold = $item_data['item_sold'];
		$item_cost = $item_data['item_cost'];
		$item_description = stripslashes_deep($item_data['item_description']);
		$item_secret_description = stripslashes_deep($item_data['item_secret_description']);
		$item_type = $item_data['item_type'];
		$item_visibility = $item_data['item_visibility'];
		$item_badge = $item_data['item_badge'];
		$item_secret_badge = $item_data['item_secret_badge'];
		$item_max = $item_data['item_max'];
		$item_level = $item_data['item_level'];
		$item_category = $item_data['item_category'];
		$achievement_id = $item_data['achievement_id'];
		$item_start_date = $item_data['item_start_date'];
		$item_deadline = $item_data['item_deadline'];
		

		if(!$item_name){
			$errors[] = __("Please add an item name","bluerabbit");
		}
		if(!$item_badge){
			$errors[] = __("Please add an image for the item","bluerabbit");
		}
		if($item_type == 'consumable'){
			if($item_stock < $item_sold){
				$errors[] = __("The store needs more items than already sold","bluerabbit")."<br>".__("Sold","bluerabbit").": $item_sold" ;
			}
		}elseif($item_type == 'key'){
			$item_stock = 99999999;
			$item_max = 1;
			$item_category='';
		}else if( $item_type == 'reward'){
			$item_stock = 99999999;
			$item_category='';
			$item_cost = 0;
			$item_max = 1;
		}else if( $item_type == 'tabi-piece'){
			$item_x = $item_data['item_x']; 
			$item_y = $item_data['item_y']; 
			$item_z = $item_data['item_z']; 
			$tabi_id = $item_data['tabi_id']; 
			if($item_stock <= 0){
				$item_stock = 99999999;
			}else if($item_stock < $item_sold){
				$errors[] = __("The store needs more items than already sold","bluerabbit")."<br>".__("Sold","bluerabbit").": $item_sold" ;
			}
			$item_max = 1;
		}else{
			$errors[] = __("Item type doesn't exist, please select one from the options given","bluerabbit");
		}
		$sql = "INSERT INTO {$wpdb->prefix}br_items ( item_id, adventure_id, item_cost, item_stock, item_player_max, item_level, item_post_date, item_post_modified, item_author, item_name, item_description, item_type, item_badge, item_secret_badge, item_secret_description, item_category, achievement_id, item_start_date, item_deadline, item_x, item_y, item_z, tabi_id, item_visibility)
		VALUES (%d, %d, %d, %d, %d, %d, %s, %s, %s, %s,  %s, %s, %s, %s, %s, %s, %d, %s, %s, %d, %d, %d, %d, %s )
		ON DUPLICATE KEY UPDATE
		adventure_id=%d, item_cost=%d, item_stock=%d, item_player_max=%d, item_level=%d, item_post_modified=%s, item_author=%s, item_name=%s, item_description=%s, item_type=%s, item_badge=%s, item_secret_badge=%s, item_secret_description=%s, item_category=%s, achievement_id=%d, item_start_date=%s, item_deadline=%s, item_x=%d, item_y=%d, item_z=%d, tabi_id=%d, item_visibility=%s";

		$sql = $wpdb->prepare($sql, $item_id, $adventure_id, $item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $item_post_modified, $current_user->ID, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category, $achievement_id, $item_start_date, $item_deadline, $item_x, $item_y, $item_z, $tabi_id, $item_visibility, $adventure_id, $item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $current_user->ID, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category, $achievement_id, $item_start_date, $item_deadline, $item_x, $item_y, $item_z, $tabi_id, $item_visibility);
		if(!$errors){
			$wpdb->query($sql);
			$new_item_id = $wpdb->insert_id;
			
			if($wpdb->insert_id){
				$data['success']=true;
				if(!$item_id){
					$data['location']=get_bloginfo('url')."/new-item/?adventure_id=$adventure_id&item_id=$new_item_id";
					logActivity($adventure_id,'add','item',$item_type,$new_item_id);
				}else{
					logActivity($adventure_id,'update','item',$item_type,$item_id);
				}
				$data['message'] .= '<h1><strong>'.$item_name.'</strong></h1> <h4><strong>'.__("Item Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
				
				$children_update = "UPDATE {$wpdb->prefix}br_items SET item_cost=%d, item_stock=%d, item_player_max=%d, item_level=%d, item_post_modified=%s, item_status=%s, item_name=%s, item_description=%s, item_type=%s, item_badge=%s, item_secret_badge=%s, item_secret_description=%s, item_category=%s, item_start_date=%s, item_deadline=%s
				WHERE `item_parent`=$new_item_id AND item_id!=$new_item_id";

				$children_update = $wpdb->query( $wpdb->prepare("$children_update ",$item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $item_status, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category, $item_start_date, $item_deadline));

				logActivity($adventure_id,'update','item-children',$item_type,$item_id);
				
				
			}else{
				$data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert item","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
			}
		}else{
			$data['message'] = "<h1><strong>".__("Please fix the following errors","bluerabbit")."</strong></h1>";
			$data['message'].="<ul class='errors'>";
			foreach($errors as $e){
				$data['message'].="<li> $e </li>";
			}
			$data['message'].="</ul>";
		}
		
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
///////////////////////// UPDATE PROFILE ///////////////////////////
function updateProfile(){ 
	$data = array();
	
	global $wpdb; $current_user = wp_get_current_user();
	$the_player_data = $_POST["player_data"];
	$nonce = $_POST["nonce"];
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
		$data['success'] = false;
		$display_name = $the_player_data['first_name']." ".$the_player_data['last_name'];
		if($the_player_data['hexad'] == 'freespirit'){
			$the_hexad_name = "Free Spirit";
		}elseif($the_player_data['hexad'] == 'philanthropist'){
			$the_hexad_name = "Philanthropist";
		}elseif($the_player_data['hexad'] == 'socialiser'){
			$the_hexad_name = "Socialiser";
		}elseif($the_player_data['hexad'] == 'achiever'){
			$the_hexad_name = "Achiever";
		}
		$user_data = array(
			"ID"=>$current_user->ID,
			"first_name"=>$the_player_data['first_name'],
			"first_name"=>$the_player_data['first_name'],
			"last_name"=>$the_player_data['last_name'],
			"display_name"=>$display_name
		);
		wp_update_user($user_data);
		update_user_meta($current_user->ID, 'locale', $the_player_data['lang']);
		$player_picture = $the_player_data['profile_picture'];
		if(!$player_picture){
			$player_picture = get_bloginfo('template_directory')."/images/token-".rand(1,5).".png";
		}

		$update_player_sql="
		INSERT INTO {$wpdb->prefix}br_players
		(player_id, player_email, player_password, player_first, player_last, player_gmt, player_lang, player_picture, player_registered, player_display_name, player_nickname)
		VALUES (%d, %s, %s, %s, %s, %s,  %s, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE
		player_email=%s, player_first=%s, player_last=%s, player_gmt=%s, player_lang=%s, player_picture=%s, player_display_name=%s, player_nickname=%s
		";
		$the_player_data['birthdate'] =  date("Y-m-d H:i:s", strtotime($the_player_data['birthdate']));
		$update_player = $wpdb->prepare(
			$update_player_sql,
			$current_user->ID,
			$the_player_data['email'],
			'none',
			$the_player_data['first_name'],
			$the_player_data['last_name'],
			$the_player_data['timezone'],
			$the_player_data['lang'],
			$player_picture,
			$current_user->user_registered,
			$display_name,
			$current_user->user_login,
			$the_player_data['email'],
			$the_player_data['first_name'],
			$the_player_data['last_name'],
			$the_player_data['timezone'],
			$the_player_data['lang'],
			$player_picture,
			$display_name,
			$current_user->user_login
		);
		$update_player = $wpdb->query($update_player);
		$data['success'] = true;
		$msg_content = __('Profile updated!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
		logActivity(0,'update','profile');
	}else{
		$data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
		$data['success'] = false;
		$msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
		
	}
	echo json_encode($data);
	die();
}
///////////////////////// setNickname ///////////////////////////
function setNickname(){ 
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	$nickname = $_POST["nickname"];
	$nonce = $_POST["nonce"];
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
		$data['success'] = false;
		if($nickname){
			$user_data = array(
				"ID"=>$current_user->ID,
				"display_name"=>$nickname
			);
			wp_update_user($user_data);
			$update_player_sql="INSERT INTO {$wpdb->prefix}br_players (player_id, player_nickname, player_display_name) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE player_nickname=%s , player_display_name=%s";
			$update_player = $wpdb->prepare( $update_player_sql, $current_user->ID, $nickname, $nickname, $nickname, $nickname);
			$update_player = $wpdb->query($update_player);
			$data['success'] = true;
			$msg_content = __('Nickname set!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green');
			$data['just_notify'] =true;
			$data['update_ux']['nickname']=$nickname;
			logActivity(0,'update-nickname','profile');
		}else{
			$msg_content = __('Please choose a nickname!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
		}
	}else{
		$data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
		$data['success'] = false;
		$msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
		
	}
	echo json_encode($data);
	die();
}
///////////////////////// setProfilePicture ///////////////////////////
function setProfilePicture(){ 
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	$player_picture = $_POST["player_picture"];
	$nonce = $_POST["nonce"];
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
		$data['success'] = false;
		$update_player_sql="INSERT INTO {$wpdb->prefix}br_players (player_id, player_picture) VALUES (%d, %s) ON DUPLICATE KEY UPDATE player_picture=%s ";
		$update_player = $wpdb->prepare( $update_player_sql, $current_user->ID, $player_picture, $player_picture);
		$update_player = $wpdb->query($update_player);
		$data['success'] = true;
		$msg_content = __('Avatar selected!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
		$data['update_ux']['player_picture']=$player_picture;
		logActivity(0,'update-profile-picture','profile');
	}else{
		$data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
		$data['success'] = false;
		$msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
		
	}
	echo json_encode($data);
	die();
}

///////////////////////// UPDATE PROFILE ///////////////////////////
function anonimizeAdventure(){ 
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST["nonce"];
	$role = $current_user->roles;
	if($role[0]!='administrator'){
		die();
	}else{
		if($adventure_id){
			$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
			if($adventure && wp_verify_nonce($nonce, 'br_anonimize_adventure')){
				
				$players = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id");
				foreach($players as $p){
					$randStr1 = random_str(12,'0123456789abcdefghijklmnopqrstuvwxyz._-');
					$randStr2 = random_str(8,'0123456789abcdefghijklmnopqrstuvwxyz');
					$randStr3 = random_str(12,'0123456789abcdefghijklmnopqrstuvwxyz');
					$randStr4 = random_str(5,'0123456789abcdefghijklmnopqrstuvwxyz');
					$randStr5 = random_str(5,'0123456789abcdefghijklmnopqrstuvwxyz');
					
					$email = "$randStr1@anonymous.player".rand(1,5000);
					$first = "$randStr2";
					$last = "$randStr3";
					$picture = get_bloginfo('template_directory')."/images/doodle-".rand(1,7).".png";
					$display = $randStr4." ".$randStr5;
					
					$anonimizerSQL .= "UPDATE {$wpdb->prefix}br_players SET player_email='$email', player_first='$first', player_last='$last', player_gmt='', player_lang='en_US',  player_picture='$picture', player_display_name='$display', player_twitter='' WHERE player_id=$p->player_id; ";
				}
				$clean_up = $wpdb->query($anonimizerSQL);
				logActivity($adventure_id,'anonimize','all-players');
			}
		}
	}
	die();
}

/////////////////////// SUBMIT PLAYER WORK /////////////////////////
function validatePlayerWork(){
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
/////////////////////// SUBMIT PLAYER WORK /////////////////////////
function submitPlayerWork(){
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
		
		$adventure = getAdventure($adventure_id);
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
			logActivity($adv_child_id,'system-verification','override-player-post',$quest->quest_id);
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
						logActivity($adv_child_id,'earned','item',$quest->mech_item_reward,$quest->quest_id);
						$data['message'] .= '<h4 class="lime-500"><span class="icon icon-basket"></span> <strong>'.__("Obtained an item!","bluerabbit").'</strong></h4>';
					}
				}
				if($quest->mech_achievement_reward){
					$has_achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_achievement a
					JOIN {$wpdb->prefix}br_achievements b ON a.achievement_id=b.achievement_id
					WHERE a.player_id=$current_user->ID AND a.adventure_id=$adv_child_id AND a.achievement_id=$quest->mech_achievement_reward AND b.achievement_status='publish'");
					if($has_achievement_reward){
						$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Achievement already earned!","bluerabbit").'</strong></h4>';
					}else{
						$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
						VALUES (%d, %d, %d, %s)";
						$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $quest->mech_achievement_reward, $today);
						$sql = $wpdb->query($sql); 
						logActivity($adv_child_id,'earned','achievement',$quest->mech_achievement_reward,$quest->quest_id);
						$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Earned an Achievement!","bluerabbit").'</strong></h4>';
					}
					$achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$quest->mech_achievement_reward");
				}
				resetPlayer($adv_child_id, $player_id);
				$adv_settings = getSettings($adv_parent_id);
				
				$xp_label = $adventure->adventure_xp_label ? $adventure->adventure_xp_label : "XP";
				$bloo_label = $adventure->adventure_bloo_label ? $adventure->adventure_bloo_label : "BLOO";
				$ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";
				
				$theFile = (get_template_directory()."/completed-quest.php");
				
				if(file_exists($theFile)) {
					include ($theFile);
				}
				logActivity($adv_child_id,'complete','quest',$quest->quest_id,$new_pp_id);
				die();
			}else{
				$data['message'] = '<h1><strong>'.__("Data Base Error. Can't insert/update entry","bluerabbit").'</strong></h1> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
				$data['message'].="<br><br><br>";
				logActivity($adv_child_id,'error','quest','Submit Insert Fail',$quest_id);
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
/////////////////////// START ATTEMPT /////////////////////////
function startAttempt(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$challenge_id = $_POST['challenge_id'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$attempt_cost = $_POST['attempt_cost'];
	$data['success']=false;
	
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	//$challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$challenge_id AND quest_status='publish'");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	
	if(wp_verify_nonce($nonce, 'br_player_new_attempt_nonce')) {
		$ok=false;
		if($attempt_cost > 0){
			$playerData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$current_user->ID AND adventure_id=$adventure_id");
			if($playerData->player_bloo >= $attempt_cost){
				$ok=true;
			}
		}else{
			$ok=true;
		}
		if($ok){
			$newAttempt="INSERT INTO {$wpdb->prefix}br_challenge_attempts (player_id,adventure_id,quest_id, attempt_date) VALUES (%d,%d,%d, %s)";
			$wpdb->query($wpdb->prepare($newAttempt,$current_user->ID, $adventure_id,$challenge_id, $today));
			$att_id = $wpdb->insert_id;
			if($att_id){
				$data['att_id']=$att_id;
				$data['success']=true;
				$data['message'] = '<h1><span class="icon icon-challenge icon-xl"></span><br><strong>'.__("Get Ready!","bluerabbit").'</strong></h1> <h3><strong>'.__("Challenge Starting","bluerabbit").'</strong></h3>';
				if($attempt_cost > 0){
					$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
					VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
					$sql = $wpdb->prepare($sql, $current_user->ID, $adventure_id, $challenge_id, $current_user->ID, $attempt_cost, 'attempt', $today, $today);
					$sql = $wpdb->query($sql);
					$data['message'] .= '<h4 class="margin-10"><strong>'.__("Attempt Purchased","bluerabbit").'</strong></h4>';
					$data['message'] .= '<h4 class="margin-10 red-A400 font w700 _20"><span class="icon icon-warning amber-500"></span>'.__("You will spend and lose this attempt if you refresh the page before you finish the challenge","bluerabbit").'</h4>';
					logActivity($adventure_id,'purchase','challenge-attempt',"",$challenge_id);
				}else{
					$data['message'] .= '<h4 class="margin-10 red-A400 font w700 _20"><span class="icon icon-warning amber-500"></span>'.__("You will lose this free attempt if you refresh the page before you finish the challenge","bluerabbit").'</h4>';
				}
				logActivity($adventure_id,'attempt','challenge',"",$challenge_id);
				$data['message'].= '<button class="form-ui green-bg-400 white-color">'.__("click to start","bluerabbit").'</button>';
			}
		}else{
			$data['message'] = '<span class="icon icon-bloo icon-xl"></span><br><h2><strong>'.__("Not enough funds","bluerabbit").'</strong></h2>';
			$data['message'] .= '<h5>'.__("click to close","bluerabbit").'</h5>';
			$data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";
			logActivity($adventure_id,'no-funds','challenge',"",$challenge_id);
		}
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel solid-red icon-xl"></span><br><strong>'.__("Unauthorized access","bluerabbit").'</strong></h1> <h5>'.__("click to close","bluerabbit").'</h5>';
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();

}
function submitAnswer(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$challengeID = $_POST['challenge_id'];
	$attID = $_POST['attempt_id'];
	$questionID = $_POST['question_id'];
	$answer_id = $_POST['answer_id'];
	$answer_value = $_POST['answer_value'] ? implode(",",$_POST['answer_value']) : '';
	$adventure_id = $_POST['adventure_id'];

	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$data = array();
	$data['success']=false;
	$answerInsert = "INSERT INTO {$wpdb->prefix}br_challenge_attempt_answers (attempt_id, question_id, player_id, quest_id, answer_id, answer_value, timestamp)
	VALUES (%d, %d, %d, %d, %d, %s, %s) 
	ON DUPLICATE KEY UPDATE answer_id=%s, answer_value=%s, timestamp=%s";
	
	$answer =$wpdb->query($wpdb->prepare($answerInsert, $attID, $questionID, $current_user->ID, $challengeID, $answer_id, $answer_value, $today, $answer_id, $answer_value, $today));
	if($answer!==false){
		$data['success'] = true;
		logActivity($adv_child_id,'answer','challenge-question', $answer_value, $challenge_id, $answer_id);
	}else{
		$data['message'] = '<h1><span class="icon icon-cancel icon-xl"></span><br><strong>'.__("Can't insert answer!","bluerabbit").'</strong></h1> <h3><strong>'.__("Please retry the challenge","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
	}
	echo json_encode($data);
	die();
}
function submitSurveyAnswer(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$survey_id = $_POST['survey_id'];
	$question_id = $_POST['question_id'];
	$option_id = $_POST['option_id'];
	$text_value = $_POST['value'];
	$adventure_id = $_POST['adventure_id'];
	$data = array();
	$data['success']=false;
	$adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$answerInsert= "INSERT INTO {$wpdb->prefix}br_survey_answers (player_id, adventure_id, survey_id, survey_question_id, survey_option_id, survey_answer_value, survey_answer_date, survey_answer_modified) VALUES (%d,%d,%d,%d,%d,%s,%s,%s)
	ON DUPLICATE KEY UPDATE survey_option_id=%d, survey_answer_value=%s,survey_answer_modified=%s, adventure_id=%d ";
	$answer = $wpdb->query($wpdb->prepare($answerInsert, $current_user->ID, $adv_child_id, $survey_id, $question_id, $option_id, $text_value, $today, $today, $option_id, $text_value, $today, $adv_child_id));
	
	//check all answers are submitted
	$all_player_answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_answers WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND survey_id=$survey_id");
	$all_survey_questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_questions WHERE survey_id=$survey_id AND survey_question_status='publish'");
	
	if(count($all_player_answers) >= count($all_survey_questions)){
		//insert in player posts
		$sql = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_type, pp_status)
		VALUES (%d, %d, %d, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE
		pp_modified=%s, pp_status=%s";
		$sql = $wpdb->prepare($sql, $survey_id, $adv_child_id, $current_user->ID, $today, $today, 'survey', 'publish', $today, 'publish');
		$insert = $wpdb->query($sql); $new_pp_id = $wpdb->insert_id;
		logActivity($adv_child_id, 'survey-complete','survey',"",$survey_id);
	}
	logActivity($adv_child_id, 'submit','survey-answer',"",$survey_id);
	$notification = new Notification();
	
	if($answer){
		$data['success']=true;
		$msg_content = __('Answer Saved!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green');
		$data['just_notify'] =true;
	}else{
		$msg_content = __('Please try again','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
function gradeChallenge($attempt_id=0){
	global $wpdb; $current_user = wp_get_current_user();
	
	$challenge_id = $_POST['challenge_id'];
	$att_id = $_POST['attempt_id'];
	$adventure_id = $_POST['adventure_id'];
	$data = array();
	$data['success']=false;
	$player_answers = 0;
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	$challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$challenge_id AND adventure_id=$adv_parent_id AND quest_status='publish'");
	$qs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions a WHERE quest_id=$challenge_id AND question_status='publish'");
	//// Esta consulta trae sólo las respuestas correctas del intento. no encuentra respuestas erróneas.
	$single_questions = $wpdb->get_results("
		SELECT * FROM {$wpdb->prefix}br_challenge_answers answers
		JOIN {$wpdb->prefix}br_challenge_attempt_answers p_answers
		ON answers.answer_id = p_answers.answer_id
		WHERE answers.quest_id=$challenge_id AND p_answers.attempt_id=$att_id AND answers.answer_status='publish' AND answers.answer_correct=1 AND p_answers.player_id=$current_user->ID
	");
	$player_answers += count($single_questions);
	// Esta Consulta trae la lista de respuestas correctas que hay que comparar con los valores que el jugador ingreso
	$multiple_questions = $wpdb->get_results("
		SELECT answers.* FROM {$wpdb->prefix}br_challenge_answers answers
		JOIN {$wpdb->prefix}br_challenge_questions questions
		ON questions.question_id = answers.question_id
		WHERE questions.quest_id=$challenge_id AND questions.question_status='publish' AND answers.answer_status='publish' AND questions.question_type='multiple' AND answers.answer_correct=1
	");
	//RESPUESTAS DEL JUGADOR
	$multiple_answers_by_player = $wpdb->get_results(" SELECT answer_value, question_id FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE attempt_id=$att_id AND answer_id<1 AND player_id=$current_user->ID");
	$mqs = array();
	foreach($multiple_questions as $mq){
		$mqs[$mq->question_id][]=$mq->answer_id;
	}
	foreach($multiple_answers_by_player as $map){
		$myAnswers=(explode(",",$map->answer_value));
		sort($myAnswers);
		sort($mqs[$map->question_id]);
		if($myAnswers==$mqs[$map->question_id]){
			$player_answers++;
		}
	}
	if($challenge->mech_questions_to_display > count($qs) || $challenge->mech_questions_to_display <=0){
		$display = count($qs);
	}else{
		$display = $challenge->mech_questions_to_display;
	}
	$atw = $challenge->mech_answers_to_win > count($qs) ? count($qs) : $challenge->mech_answers_to_win; 
	$grade = round($player_answers*100/$display);
	$updateAttempt = "UPDATE {$wpdb->prefix}br_challenge_attempts SET attempt_status=%s, attempt_answers=%s, attempt_grade=%d WHERE player_id=%d AND quest_id=%d AND attempt_id=%d";
	if($player_answers >= $atw){
		$attempt_status = 'success';
		if($challenge->mech_item_reward){
			$my_item_rew = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND object_id=$challenge->mech_item_reward AND trnx_status='publish'");
			$item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$challenge->mech_item_reward AND item_status='publish'");
			if($my_item_rew){
				$data['message'] .= '<h4 class="lime-500"><span class="icon icon-achievement"></span> <strong>'.__("Reward already in backpack!","bluerabbit").'</strong></h4>';
			}else{
				$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
				VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
				$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $challenge->mech_item_reward, $current_user->ID, 0, $item_reward->item_type, $today, $today);
				$sql = $wpdb->query($sql);
				$data['message'] .= '<h4 class="lime-500"><span class="icon icon-achievement"></span> <strong>'.__("Obtained an item!","bluerabbit").'</strong></h4>';
				logActivity($adv_child_id,'earned','item-reward',"",$challenge->quest_id, $item_reward->item_id);
			}
		}
		if($challenge->mech_achievement_reward){

			$achievement_reward = $wpdb->get_row("SELECT ach.*, p_ach.achievement_applied 
			FROM {$wpdb->prefix}br_achievements ach
			LEFT JOIN {$wpdb->prefix}br_player_achievement p_ach
			ON p_ach.achievement_id=ach.achievement_id AND p_ach.player_id=$current_user->ID AND p_ach.adventure_id=$adv_child_id

			WHERE achievement_id=$challenge->mech_achievement_reward");

			if($achievement_reward->achievement_applied){
				$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Achievement already earned!","bluerabbit").'</strong></h4>';
			}else{
				$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
				VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $challenge->mech_achievement_reward, $today);
				$sql = $wpdb->query($sql);
				$data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Earned an Achievement!","bluerabbit").'</strong></h4>';
				logActivity($adventure_id,'earned','achievement-reward',"",$challenge->quest_id, $achievement_reward->achievement_id);
			}
		}
		$updateAttempt = $wpdb->query($wpdb->prepare($updateAttempt, $attempt_status, $player_answers, $grade ,$current_user->ID, $challenge_id, $att_id));

		registerPost($challenge->quest_id, $adv_child_id, $type="challenge");

		logActivity($adv_child_id,'complete','challenge',"", $challenge->quest_id, $att_id);

		resetPlayer($adv_child_id, $player_id);
		$adv_settings = getSettings($adv_child_id);
				
		$xp_label = $adventure->adventure_xp_label ? $adventure->adventure_xp_label : "XP";
		$bloo_label = $adventure->adventure_bloo_label ? $adventure->adventure_bloo_label : "BLOO";
		$ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";

		$theFile = (get_template_directory()."/completed-challenge.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}else{
		$attempt_status = 'fail';
		$updateAttempt = $wpdb->query($wpdb->prepare($updateAttempt, $attempt_status, $player_answers, $grade ,$current_user->ID, $challenge_id, $att_id));
		logActivity($adv_child_id,'failed','challenge',"",$challenge->quest_id, $att_id);
		resetPlayer($adv_child_id, $player_id);
		$theFile = (get_template_directory()."/failed-challenge.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}
	$data['success']=true;
	$player_id = $current_user->ID;
	resetPlayer($adv_child_id, $player_id);
	echo json_encode($data);
	die();
}
/////////////////////// CLOSE INTRO ////////////////////

function closeIntro($p_adv_id=0){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $p_adv_id ? $p_adv_id : $_POST['adventure_id'];
	
	$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_hide_intro=%d WHERE player_id=%d AND adventure_id=%d";
	$sql = $wpdb->prepare ($sql,1,$current_user->ID,$adventure_id);

	$wpdb->query($sql);
	$data['adventure_home_url'] = get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id;
	$data['success'] = true;
	echo json_encode($data);
	die();
}
////////////// resetIntro /////////////
function resetIntro($p_adventure_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	$notification = new Notification();
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if($adventure){
		$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_hide_intro=0  WHERE adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$adventure_id);
		$wpdb->query($sql);
		$data['success'] = true;
		$msg_content =  __("Intro will show again on Login","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'blue','logo');
		$data['just_notify'] =true;
	}else{
		$data['success'] = false;
		$msg_content =  __("Adventure not found!","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
////////////// resetGuilds /////////////
function resetGuilds($p_adventure_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	$notification = new Notification();
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if($adventure){
		$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=0  WHERE adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$adventure_id);
		$wpdb->query($sql);
		$sql = "DELETE FROM {$wpdb->prefix}br_player_guild WHERE adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$adventure_id);
		$wpdb->query($sql);
		$data['success'] = true;
		$msg_content =  __("All Guilds have been reset","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'green','guild');
		$data['just_notify'] =true;
		logActivity($adventure_id,'reset','guilds');
	}else{
		$data['success'] = false;
		$msg_content =  __("Adventure not found!","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
		
	}
	echo json_encode($data);
	die();
}
////////////// resetPrevLevel /////////////
function resetPlayerAdventure(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$player_id = $_POST['player_id'];
	$notification = new Notification();
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

	$player = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adv_child_id AND player_id=$player_id");
	if($adventure && $player){
		$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=0, player_xp=0, player_bloo=0, player_ep=0, achievement_id=0, player_guild=NULL, player_last_random_encounter_id=0, player_hide_intro=0  WHERE adventure_id=%d AND player_id=%d";
		$sql = $wpdb->prepare ($sql,$adv_child_id, $player->player_id);
		$wpdb->query($sql);
		$data['success'] = true;
		$msg_content =  __("The player has been reset","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'blue','logo');
		$data['just_notify'] =true;
		logActivity($adv_child_id,'reset','player-adventure',"",$player->player_id);
	}else{
		$data['success'] = false;
		$msg_content =  __("Adventure not found!","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
////////////// resetPrevLevel /////////////
function resetPrevLevel($p_adventure_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	$notification = new Notification();
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if($adventure){
		$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=0  WHERE adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$adventure_id);
		$wpdb->query($sql);
		$data['success'] = true;
		$msg_content =  __("All Prev Levels have been reset","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'purple','language');
		$data['just_notify'] =true;
	}else{
		$data['success'] = false;
		$msg_content =  __("Adventure not found!","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
/////////////////////// Update Prev Level ////////////////////
function updatePrevLevel(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adventure_id = $_POST['adventure_id'];
	$level = $_POST['level'];
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$ranks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=$adv_parent_id AND rank_level <= $level");
	$config = getSysConfig();
	$logo = $config['main_logo']['value'] ? $config['main_logo']['value'] :  get_bloginfo('template_directory')."/images/logo.png";
	if($ranks){
		$the_ranks_query = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES ";
		$the_ranks_values = array();
		$the_ranks_place_holders = array();
		foreach($ranks as $rank){
			array_push($the_ranks_values, $rank->achievement_id, $current_user->ID, $adv_child_id, $today );
			$the_ranks_place_holders[] = "(%d,%d,%d, %s)";
		}
		$the_ranks_query .= implode(', ', $the_ranks_place_holders);
		$the_ranks_query .=" ON DUPLICATE KEY UPDATE achievement_id=VALUES(achievement_id), player_id=VALUES(player_id),  adventure_id=VALUES(adventure_id), achievement_applied=VALUES(achievement_applied)";
		$the_ranks_insert = $wpdb->query( $wpdb->prepare("$the_ranks_query ", $the_ranks_values));
		$achievement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_id=$rank->achievement_id");
		switchRank($achievement->achievement_id, $adv_child_id);
		$data['levelupBG'] = $achievement->achievement_badge;
		$data['achievement_id'] = $achievement->achievement_id;
		$data['levelupContent'] = "<h3><strong>".__("LEVEL UP!","bluerabbit")."</strong></h3>";
		$data['levelupContent'] .= "<h2 class='font _30 w900'> $level </h2>";
		logActivity($adv_child_id,'level-up','player',$level, $achievement->achievement_id);
		logActivity($adv_child_id,'earned-achievement','player',$achievement->achievement_id);
	}else{
		$data['levelupContent'] = "<h3><strong>".__("Congratulations! LEVEL UP!","bluerabbit")."</strong></h3>";
		$data['levelupContent'] .= "<img src='$logo' width='300'>";
		$data['levelupContent'] .= "<h6>".__("you reached level","bluerabbit")."</h6>";
		$data['levelupContent'] .= "<h1><strong> $level </strong></h1>";
		logActivity($adv_child_id,'level-up','player',$level);
	}
	$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=%d WHERE player_id=%d AND adventure_id=%d";
	$sql = $wpdb->prepare ($sql, $level ,$current_user->ID,$adv_child_id);
	$wpdb->query($sql);
	$data['success'] = true;
	$data['levelup'] = true;
	
	
	

	echo json_encode($data);
	die();
}

////////////// DEFAULT ENROLLMENT /////////////
function defaultEnrollment($adventure_id,$uID){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$p = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$uID");
	$adventure = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_type='normal'");
	
	if(!$p){
		$sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d)";
		$sql = $wpdb->prepare ($sql,$adventure_id,$uID);
		$wpdb->query($sql);
		if($wpdb->insert_id){
			logActivity($adventure_id,'enroll','player-adventure');
			if($adventure->adventure_has_guilds){
				assignGuild($uID, $adventure->adventure_id);
			}
			$data['success'] = true;
			$data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";
			
		}
	}elseif($p->player_adventure_status=='out'){
		$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_status=%s WHERE player_id=%d AND adventure_id=%d";
		$sql = $wpdb->prepare ($sql,'in',$uID,$adventure_id);
		logActivity($adventure_id,'removed','player-adventure');
		if($wpdb->insert_id){
			if($adventure->adventure_has_guilds){
				assignGuild($uID, $adventure->adventure_id);
			}
			
			$data['success'] = true;
			$data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";
			
		}
	}
	return $data;
}

////////////// TRIGGER ACHIEVEMENT - ACHIEVEMENTS /////////////
function triggerAchievement($p_achievement_id="", $p_player_id="", $p_adventure_id=""){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$achievement_id = $p_achievement_id ? $p_achievement_id : $_POST['achievement_id'];
	$player_id = $p_player_id ? $p_player_id : $_POST['player_id'];
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	$notification = new Notification();
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");

	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;


	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$a = $wpdb->get_row("SELECT a.*, b.player_id FROM {$wpdb->prefix}br_achievements a
	LEFT JOIN  {$wpdb->prefix}br_player_achievement b
	ON a.achievement_id = b.achievement_id AND b.player_id=$player_id AND b.adventure_id=$adv_child_id
	WHERE a.adventure_id=$adv_parent_id AND a.achievement_id=$achievement_id AND a.achievement_status='publish'");
	if($a){
		if($a->achievement_group == ''){
			if(!$a->player_id){
				$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d,%d,%d, %s)";
				$sql = $wpdb->prepare ($sql,$achievement_id,$player_id, $adv_child_id, $today);
				$wpdb->query($sql);
				
				$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=$achievement_id WHERE player_id=$player_id AND adventure_id=$adv_child_id";
				$sql = $wpdb->query($wpdb->prepare ($sql));
				
				$data['success'] = true;
				$msg_content =  __("Achievement Assigned!","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'green','achievement');
				$data['just_notify'] =true;
				$data['action'] = 'assign';
				logActivity($adv_child_id,'earned','achievement',"",$player_id, $a->achievement_id);
			}else{
				$sql = "DELETE FROM {$wpdb->prefix}br_player_achievement WHERE achievement_id=%d AND player_id=%d AND adventure_id=%d";
				$sql = $wpdb->prepare ($sql,$achievement_id,$player_id, $adv_child_id);
				$wpdb->query($sql);
				
				$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=0 WHERE player_id=$player_id AND achievement_id=$achievement_id AND adventure_id=$adv_child_id";
				$sql = $wpdb->query($wpdb->prepare ($sql));
				
				$data['success'] = true;
				$msg_content =  __("Achievement removed!","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'red','cancel');
				$data['just_notify'] =true;
				$data['action'] = 'remove';
				logActivity($adv_child_id,'removed','achievement',"",$player_id, $a->achievement_id);
			}
		}else{
			$achs = $wpdb->get_results("SELECT a.*, b.player_id FROM {$wpdb->prefix}br_achievements a
			LEFT JOIN {$wpdb->prefix}br_player_achievement b
			ON a.achievement_id = b.achievement_id AND b.player_id=$player_id AND b.adventure_id=$adv_child_id
			WHERE a.adventure_id=$adv_parent_id AND a.achievement_id=$achievement_id AND a.achievement_status='publish' AND a.achievement_group='$a->achievement_group'");
			$allowed = true;
			foreach($achs as $ach){
				if($ach->player_id == $player_id && $ach->achievement_id != $achievement_id){
					$allowed = false;
				}
			}
			if($allowed){
				if(!$a->player_id){
					$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d,%d,%d, %s)";
					$sql = $wpdb->prepare ($sql,$achievement_id,$player_id, $adv_child_id, $today);
					$wpdb->query($sql);
					
					$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=$achievement_id WHERE player_id=$player_id AND adventure_id=$adv_child_id";
					$sql = $wpdb->query($wpdb->prepare ($sql));
					
					$data['success'] = true;
					$msg_content =  __("Achievement Assigned!","bluerabbit");
					$data['message'] = $notification->pop($msg_content,'green','achievement');
					$data['just_notify'] =true;
					$data['action'] = 'assign';
					logActivity($adv_child_id,'earned','achievement',"",$player_id, $a->achievement_id);
				}else{
					$sql = "DELETE FROM {$wpdb->prefix}br_player_achievement WHERE achievement_id=%d AND player_id=%d AND adventure_id=%d";
					$sql = $wpdb->prepare ($sql,$achievement_id,$player_id, $adv_child_id);
					$wpdb->query($sql);
					
					$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=0 WHERE player_id=$player_id AND achievement_id=$achievement_id AND adventure_id=$adv_child_id";
					$sql = $wpdb->query($wpdb->prepare ($sql));
					
					$data['success'] = true;
					$msg_content =  __("Achievement removed!","bluerabbit");
					$data['message'] = $notification->pop($msg_content,'red','cancel');
					$data['just_notify'] =true;
					$data['action'] = 'remove';
					logActivity($adv_child_id,'removed','achievement',"",$player_id, $a->achievement_id);
				}
			}else{
				$data['success'] = true;
				$msg_content =  __("Can't assign achievement! Already walking a different path","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'red','cancel');
				$data['just_notify'] =true;
				logActivity($adv_child_id,'denied','achievement',"",$player_id, $a->achievement_id);
			}
		}
		
	}else{
		$data['success'] = false;
		$data['message'].= '<span class="icon icon-cancel red-400 icon-lg"></span><br>';
		$data['message'].= '<h3><strong>'.__("Achievement doesn't exist!",'bluerabbit').'</strong></h3>';
	}
	echo json_encode($data);
	die();
}
function triggerAchievements($p_ach_id=NULL, $p_adv_id=NULL, $p_status=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$achievement_id = $p_ach_id ? $p_ach_id : $_POST['achievement_id'];
	$adventure_id = $p_adv_id ? $p_adv_id : $_POST['adventure_id'];
	$status = $p_status ? $p_status : $_POST['status'];

	$notification = new Notification();

	$a = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_achievements a
	WHERE a.achievement_id=$achievement_id AND a.achievement_status='publish'");
		
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $a->adventure_id;



	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	if($a){
		if($a->achievement_display == 'badge'){
			$sql = "DELETE FROM {$wpdb->prefix}br_player_achievement WHERE achievement_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql, $a->achievement_id, $adv_child_id);
			$wpdb->query($sql);
			if($status=='on'){
				$players = $wpdb->get_results("SELECT p.* FROM {$wpdb->prefix}br_player_adventure p
				LEFT JOIN {$wpdb->prefix}br_adventures adv ON p.adventure_id=adv.adventure_id AND adv.adventure_id=$adv_child_id
				WHERE p.player_adventure_status='in' AND adv.adventure_status='publish' AND p.adventure_id=$adv_child_id");
				$achievements_query = "INSERT INTO {$wpdb->prefix}br_player_achievement (`achievement_id`, `player_id`, `adventure_id`) VALUES ";
				
				$place_holders = array();
				foreach($players as $p){
					$place_holders[] = "($a->achievement_id, $p->player_id, $adv_child_id)";
				}
				$achievements_query .= implode(', ', $place_holders);
				$achievements_insert = $wpdb->query( $wpdb->prepare("$achievements_query "));
				
				
				$data['success'] = true;
				$msg_content =  __("All Achievements Assigned","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'green','achievement');
				$data['just_notify'] =true;
				$data['action'] = 'assigned-all';
				logActivity($adventure_id,'assigned-all','achievement',"", $a->achievement_id);
			}else{
				$msg_content =  __("Achievements Removed","bluerabbit");
				$data['message'] = $notification->pop($msg_content,'red','remove');
				$data['just_notify'] =true;
				$data['action'] = 'removed-all';
				logActivity($adventure_id,'removed-all','achievement',"", $a->achievement_id);
			}
		}else{
			$msg_content =  __("Can't assign to this achievement type","bluerabbit");
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
		}
	}else{
		$data['success'] = false;
		$data['message'].= '<span class="icon icon-cancel red-400 icon-lg"></span><br>';
		$data['message'].= '<h3><strong>'.__("Achievement doesn't exist!",'bluerabbit').'</strong></h3>';
	}
	echo json_encode($data);
	die();
}
////////////// TRIGGER TEAM /////////////
function triggerGuild($p_guild_id=NULL, $p_player_id=NULL, $p_adventure_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$guild_id = $p_guild_id ? $p_guild_id : $_POST['guild_id'];
	$player_id = $p_player_id ? $p_player_id : $_POST['player_id'];
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	$n = new Notification();
	$t = $wpdb->get_row("SELECT guilds.*, player_guild.player_id, player_adventure.player_guild FROM {$wpdb->prefix}br_guilds guilds
	LEFT JOIN  {$wpdb->prefix}br_player_guild player_guild
	ON guilds.guild_id = player_guild.guild_id AND player_guild.player_id=$player_id AND player_guild.adventure_id=$adventure_id
	LEFT JOIN  {$wpdb->prefix}br_player_adventure player_adventure
	ON player_guild.player_id = player_adventure.player_id AND player_adventure.player_id=$player_id AND player_adventure.adventure_id=$adventure_id
	WHERE guilds.adventure_id=$adventure_id AND guilds.guild_id=$guild_id AND guilds.guild_status='publish'");
	if($t){
		if($t->player_id != $player_id){
			$sql = "INSERT INTO {$wpdb->prefix}br_player_guild (guild_id, player_id, adventure_id) VALUES (%d,%d,%d)";
			$sql = $wpdb->prepare ($sql,$guild_id,$player_id, $adventure_id);
			$wpdb->query($sql);
			
			$data['success'] = true;
			$data['message'] = $n->pop( __('Player Assigned to Guild!','bluerabbit'),'green','guild');
			$data['just_notify'] =true;
			$data['action'] = 'assign';
			logActivity($adventure_id,'enroll','guild',"",$player_id, $guild_id);
		}else{
			$sql = "DELETE FROM {$wpdb->prefix}br_player_guild WHERE guild_id=%d AND player_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$guild_id,$player_id, $adventure_id);
			$wpdb->query($sql);
			
			if($t->player_guild == $guild_id){
				$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=%d WHERE player_id=%d AND adventure_id=%d";
				$sql = $wpdb->prepare ($sql,0,$player_id, $adventure_id);
				$wpdb->query($sql);
			}
			
			$data['success'] = true;
			$data['message'] = $n->pop( __('Player Removed from Guild!','bluerabbit'),'red','cancel');
			$data['just_notify'] =true;
			$data['action'] = 'remove';
			logActivity($adventure_id,'removed','guild',"",$player_id, $guild_id);
		}
	}else{
		$data['success'] = false;
		$data['message'].= '<span class="icon icon-cancel red-400 icon-lg"></span><br>';
		$data['message'].= '<h3><strong>'.__("Guild doesn't exist!",'bluerabbit').'</strong></h3>';
	}
	echo json_encode($data);
	die();
}
////////////// ASSIGN GUILD /////////////
function assignGuild($p_player_id="", $p_adventure_id=""){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	
	$player_id = $p_player_id ? $p_player_id : $_POST['player_id'];
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
	
	$has_guild = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$player_id AND adventure_id=$adventure_id");
	if(!$has_guild->player_guild){
		// No Guild Assigned
		$guilds = $wpdb->get_results("SELECT 
		
		guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity 
		
		FROM {$wpdb->prefix}br_guilds guilds
		
		LEFT JOIN {$wpdb->prefix}br_player_guild guild_players 
		ON guilds.guild_id=guild_players.guild_id
		
		WHERE guilds.adventure_id=$adventure_id AND guilds.guild_status='publish' AND guilds.assign_on_login=1 
		GROUP BY guilds.guild_id ORDER BY guild_current_capacity ASC, guilds.guild_id ASC
		"); 
		$guilds_data = print_r($guilds,true);
		//return $guilds_data;
		if($guilds){
			$the_guild_id = $guilds[0]->guild_id;
			$sql = "INSERT INTO {$wpdb->prefix}br_player_guild (guild_id, player_id, adventure_id) VALUES (%d,%d,%d); ";
			$sql = $wpdb->prepare ($sql,$the_guild_id,$player_id, $adventure_id);
			$wpdb->query($sql);
			$last_query = print_r($wpdb->last_query,true);
			$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=%d WHERE player_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$the_guild_id,$player_id, $adventure_id);
			$wpdb->query($sql);
			$last_query .= print_r($wpdb->last_query,true);

			return print_r($last_query);
			logActivity($adventure_id,'assigned','guild',"",$player_id, $the_guild_id);
		}else{
			
		}
	}else{
		return "player has_guild->player_guild = True";
	}
	
}
////////////// Choose Path - ACHIEVEMENTS /////////////

function choosePath(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$path = $_POST['path'];
	$adventure_id = $_POST['adventure_id'];
	$chosen_path = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$path AND adventure_id=$adventure_id AND achievement_status='publish'");
	$n = new Notification();
	if($chosen_path){
		$data = magicCode($chosen_path->achievement_code, $chosen_path->adventure_id);
	}else{
		$msg_content = __('Error','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify']=true;
	}
	echo json_encode($data);
	die();
}

////////////// MAGIC CODE - ACHIEVEMENTS /////////////

function magicCode($p_code = "", $p_adv=""){
	
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$code = $p_code ? $p_code : strtolower($_POST['magic_code']);
	$code = trim($code);
	$adventure_id = $p_adv ? $p_adv : $_POST['adventure_id'];
	
	$roles=$current_user->roles;
	if($roles[0]=='br_player' || $roles[0]=='administrator' || $roles[0]=='br_game_master'){
		$nonce = wp_create_nonce('blue_rabbit_magic_code_nonce');
	}
	
	if(wp_verify_nonce($nonce, 'blue_rabbit_magic_code_nonce')){
		$ach = $wpdb->get_row("SELECT ach.* 

		FROM {$wpdb->prefix}br_achievements ach
		LEFT JOIN {$wpdb->prefix}br_achievement_codes unique_code
		ON ach.achievement_id = unique_code.achievement_id AND unique_code.code_value='$code'

		WHERE (unique_code.code_value='$code' OR ach.achievement_code ='$code') AND ach.achievement_status='publish' AND ach.adventure_id=$adventure_id");
		
		$c = $wpdb->get_row("SELECT 
		ach.*, 
		unique_code.code_id, unique_code.code_value, unique_code.code_status, unique_code.code_redeemed, unique_code.player_id as redeemed_player_id, 
		player.player_id as achieved_player

		FROM {$wpdb->prefix}br_achievements ach
		LEFT JOIN {$wpdb->prefix}br_achievement_codes unique_code
		ON ach.achievement_id = unique_code.achievement_id AND unique_code.code_value='$code'

		LEFT JOIN {$wpdb->prefix}br_player_achievement player
		ON ach.achievement_id = player.achievement_id AND player.player_id=$current_user->ID AND player.adventure_id=$adventure_id AND player.achievement_id=$ach->achievement_id

		WHERE (unique_code.code_value='$code' OR ach.achievement_code ='$code') AND ach.achievement_status='publish' AND ach.adventure_id=$adventure_id");
		
		$error = array();
		if($c){
			$adventure = getAdventure($c->adventure_id);
			$adv_child_id = $adventure->adventure_id;
			$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

			$enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_adventure_status='in' AND player_id=$current_user->ID AND adventure_id=$adv_child_id");

			if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
			$rightnow = date('Y-m-d H:i:s');
			$data['c'] = $c;

			if($c->achievement_max > 0){
				$awarded = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_player_achievement WHERE adventure_id=$adv_child_id AND achievement_id=$c->achievement_id");
				if(count($awarded) >= $c->achievement_max){
					$data['message']= '<span class="icon icon-max red icon-xl"></span>';
					$data['message'].= '<h2 class="red-A400">'.__("You are too late!",'bluerabbit').'</h2>';
					$data['message'].= '<h3><strong>'.__("Max Awards Reached!",'bluerabbit').'</strong></h3>';
					$error['max']= __('Max awards reached',"bluerabbit");
				}
				logActivity($adv_child_id,'max-reached','magic-code',$code,$c->achievement_id);
			}
			if($c->achievement_deadline && $c->achievement_deadline != '0000-00-00 00:00:00'){
				$now = date('YmdHi');
				$deadline = date('YmdHi',strtotime($c->achievement_deadline));
				if($now > $deadline){
					$data['message']= '<span class="icon icon-deadline icon-xl"></span>';
					$data['message'].= '<h2 class="red-A400">'.__("Deadline missed!",'bluerabbit').'</h2>';
					$data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
					$error['deadline']= __('Achievement no longer available',"bluerabbit");
					logActivity($adv_child_id,'deadline','magic-code',$code,$c->achievement_id);
				}
			}
			if($c->code_status =='redeem'){
				$data['message']= '<span class="icon icon-carrot icon-xl"></span>';
				$data['message'].= '<h2 class="orange-400">'.__("This code has already been used!",'bluerabbit').'</h2>';
				$data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
				$error['carrot']= __('This code has already been used!',"bluerabbit");
				logActivity($adv_child_id,'redeemed','magic-code',$code,$c->achievement_id);
			}
			if($c->code_status =='expired'){
				$data['message']= '<span class="icon icon-deadline icon-xl"></span>';
				$data['message'].= '<h2>'.__("This code already expired!",'bluerabbit').'</h2>';
				$data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
				$error['expired']= __('This code already expired!',"bluerabbit");
				logActivity($adv_child_id,'expired','magic-code',$code,$c->achievement_id);
			}
			
			if($c->achievement_group != '' && $c->achievement_display =='path'){
				$allowed = 'YES';
				$group_achs = $wpdb->get_results("
				SELECT 
				ach.*, player.player_id as achieved_player

				FROM {$wpdb->prefix}br_achievements ach
				LEFT JOIN {$wpdb->prefix}br_player_achievement player
				ON ach.achievement_id = player.achievement_id AND player.player_id=$current_user->ID AND player.adventure_id=$adv_child_id

				WHERE ach.achievement_group='$c->achievement_group' AND ach.achievement_status = 'publish' 

				");
				
				if($group_achs){
					foreach($group_achs as $ga){
						if($ga->achieved_player == $current_user->ID){
							$allowed = 'NOT';
						}
					}
					if($allowed=='NOT'){
						$data['message']= '<span class="icon icon-cancel red icon-xl"></span>';
						$data['message'].= '<h3><strong>'.__("Already walking a different path",'bluerabbit').'</strong></h3>';
						$error['journey']= __("Already walking a different path","bluerabbit");
					}
				}
			}
			if($c->achievement_path > 0){
				$allowed = 'NOT';
				$my_achs = getMyAchievements($adv_child_id);
					
				
				if($my_achs){
					foreach($my_achs as $ma){
						if($ma == $c->achievement_path){
							$allowed = 'YES';
						}
					}
					if($allowed=='NOT'){
						$data['message']= '<span class="icon icon-cancel red icon-xl"></span>';
						$data['message'].= '<h3><strong>'.__("Wrong Code!",'bluerabbit').'</strong></h3>';
						$error['cancel']= __('Wrong Code!',"bluerabbit");
/*
						$data['message'].= '<h3><strong>'.__("You need to unlock a path before you can earn this code!",'bluerabbit').'</strong></h3>';
						$error['journey']= __('You need to unlock a path before you can earn this code',"bluerabbit");
*/
						logActivity($c->adventure_id,'attempt-outside-of-path','magic-code',$code);
					}
				}
			}

			
			if(($c->code_status == 'publish' && $code==$c->code_value) || ($c->achievement_status == 'publish' && $c->achievement_code==$code)){
				if($c->achieved_player == $current_user->ID || $c->redeemed_player_id == $current_user->ID ){
					$data['message'].= '<h2 class="light-blue-400">'.__("You already earned this achievement",'bluerabbit').'</h2>';
					$error['achiever']= __('You already earned this achievement',"bluerabbit");
				}elseif(empty($error)){
					if($code == $c->code_value){
						// Redeem the code if it comes from a unique code!
						$redeem = "UPDATE {$wpdb->prefix}br_achievement_codes SET `code_status`=%s, `code_redeemed`=%s, `code_modified`=%s, `player_id`=%d WHERE `code_id`=%d";
						$earn = $wpdb->query( $wpdb->prepare("$redeem ", 'redeem', $rightnow, $rightnow, $current_user->ID, $c->code_id));
						 
						logActivity($adv_child_id,'use-unique','magic-code',$code,$c->achievement_id);
					}
					// Assign achievement to player
					$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d,%d,%d, %s)";
					$sql = $wpdb->prepare ($sql,$ach->achievement_id, $current_user->ID, $adv_child_id, $rightnow);
					$wpdb->query($sql);
					 
					if($wpdb->insert_id){
						$data['success'] = true;
						logActivity($adv_child_id,'earned','magic-code',"",$c->achievement_id);
					}
					$data['message'] = '<div class="achievement-unlocked">';
					$data['message'].= '<h4>'.__("Awesome!",'bluerabbit').'</h4>';
					$data['message'].= '<h3><strong>'.$c->achievement_name."</strong></h3>";
					$data['message'].= '<div class="divider thin"></div>';
					$data['message'].= apply_filters('the_content',$c->achievement_content);
					$data['message'].= '<div class="divider thin"></div>';
					$data['message'].= '</div>';
					$data['message'].= '<button class="form-ui red" onClick="hideAllOverlay();"><span class="icon icon-cancel"></span>'.__('click to close','bluerabbit').'</button>';
					$number = rand(1,9);
					$data['message'].='
						<audio id="audio-funky">
							<source src="'.get_bloginfo('template_directory').'/audio/funk'.$number.'.mp3" type="audio/mpeg">
						</audio>
						<script>
							$(document).ready(function() {
								$("#audio-funky").get(0).play();
							});
						</script>';
					$data['noClose'] = true;
					$data['location'] = get_bloginfo('url')."/achievements/?adventure_id=$adv_child_id";
					
				}
			}else{
				$data['message']= '<span class="icon icon-cancel icon-xl"></span>';
				$data['message'].= '<h2 class="red-A400">'.__("Wrong Code!",'bluerabbit').'</h2>';
				$data['message'].= '<h4>'.__("This code is wrong!",'bluerabbit').'</h4>';
				$error['cancel']= __('Wrong Code',"bluerabbit");
				logActivity($adv_child_id,'attempt','magic-code',$code);
			}
		}else{
			$data['message'] ='<h1>'.__("Code Doesn't exist",'bluerabbit').'</h1> <h4>'.__("click to close",'bluerabbit').'.</h4>';
			$data['location']='reload';
			$error['cancel']= __('Wrong Code',"bluerabbit");
			logActivity($adv_child_id,'attempt','magic-code',$code);
		}
	}else{
		$data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.__('click to close','bluerabbit').'.</h4>';
	}
	$data['errors'] = $error;
	if($p_code && $p_adv){
		return $data;
	}else{
		echo json_encode($data);	
	}
	die();
}
////////////////////////////////////// BUY ITEM //////////////////////////////////
function buyItem(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure_id = $_POST['adventure_id'];
	$item_id = $_POST['item_id'];
	$nonce = $_POST['nonce'];
	$data = array();
	$data['success'] = false;
	

	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	$notification = new Notification();
	$data['message_delay'] = 2000;
	$data['just_notify']=true;
	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	if(wp_verify_nonce($nonce, 'br_item_nonce')){
		
		$playerData = getPlayerAdventureData($adv_child_id, $current_user->ID);
		$purchaseData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$item_id AND item_status='publish' AND adventure_id=$adv_parent_id");
		
		$allowedByStartDate = true;
		$allowedByDeadline = true;
		
		if(($purchaseData->item_start_date != '' && $purchaseData->item_start_date != '0000-00-00 00:00:00') && strtotime($today) < strtotime($purchaseData->item_start_date)){
			$allowedByStartDate = false;
		}
		if(($purchaseData->item_deadline != '' && $purchaseData->item_deadline != '0000-00-00 00:00:00') && strtotime($today) > strtotime($purchaseData->item_deadline)){
			$allowedByDeadline = false;
		}

		if($purchaseData->item_category != ""){
			$trnxs = $wpdb->get_results("SELECT a.* FROM {$wpdb->prefix}br_transactions a
			JOIN {$wpdb->prefix}br_items b
			ON a.object_id=b.item_id
			WHERE a.adventure_id=$adv_child_id AND a.player_id=$current_user->ID AND b.item_category='$purchaseData->item_category' AND trnx_status='publish'");
		}else{
			$trnxs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_transactions
			WHERE adventure_id=$adv_child_id AND player_id=$current_user->ID AND object_id=$item_id  AND trnx_status='publish'");
		}
		$alltrnx = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_transactions WHERE object_id=$item_id AND trnx_type='consumable' AND trnx_status='publish' AND adventure_id=$adv_child_id");
		
		$left = $purchaseData->item_stock-count($alltrnx);
		//validation
		if($allowedByStartDate){
			if($allowedByDeadline){
				if($left>0){
					if($playerData->player_bloo >= $purchaseData->item_cost ){
						if($playerData->player_level >= $purchaseData->item_level){
							if($purchaseData->item_player_max == 0 || (count($trnxs) < $purchaseData->item_player_max && $purchaseData->item_player_max > 0)){
								$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
								VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
								$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $item_id, $current_user->ID, $purchaseData->item_cost, $purchaseData->item_type, $today, $today);
								$sql = $wpdb->query($sql);
								$msg_content = __('Item Purchased!','bluerabbit');
								$data['message'] = $notification->pop($msg_content,'green','check');
								$data['noClose'] = true;
								$data['success']=true;
								$data['sale']=true;
								logActivity($adv_child_id, 'purchase','item',"$purchaseData->item_type",$item_id);
								resetPlayer($adv_child_id, $current_user->ID);
							}else{
								$msg_content = __("You can't buy any more of this item",'bluerabbit');
								$data['message'] = $notification->pop($msg_content,'red','cancel');
							}
						}else{
							$msg_content = __("Required level","bluerabbit").": $purchaseData->item_level";
							$data['message'] = $notification->pop($msg_content,'purple','level');
						}
					}else{
						$msg_content = __("Not enough funds",'bluerabbit');
						$data['message'] = $notification->pop($msg_content,'red','cancel');
					}
				}else{
					$msg_content = __("No More Items Left",'bluerabbit');
					$data['message'] = $notification->pop($msg_content,'orange','cancel');
				}
			}else{
				$msg_content = __("You missed your chance!",'bluerabbit');
				$data['message'] = $notification->pop($msg_content,'amber','deadline');
			}
		}else{
			$msg_content = __("You must wait until the item is open for purchase!",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'amber','deadline');
		}
	}else{
		$msg_content = __("Unauthorized access",'bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
	}
	echo json_encode($data);
	die();
}
////////////////////////////////////// pickupItem ITEM //////////////////////////////////
function pickupItem(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure_id = $_POST['adventure_id'];
	$item_id = $_POST['item_id'];
	$nonce = $_POST['nonce'];
	$data = array();
	$data['success'] = false;
	
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	$item = $wpdb->get_row("SELECT items.*, trnx.trnx_id FROM {$wpdb->prefix}br_items items 
	LEFT JOIN {$wpdb->prefix}br_transactions trnx ON items.item_id=trnx.object_id AND (trnx.trnx_type='key' || trnx.trnx_type='tabi-piece') AND trnx.player_id=$current_user->ID
	WHERE items.item_id=$item_id");
	$notification = new Notification();	
	if(wp_verify_nonce($nonce, 'pickup_item'.$current_user->ID.date('Ymd')) && !$item->trnx_id){
		$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
		VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
		$sql = $wpdb->prepare($sql, $current_user->ID, $item->adventure_id, $item->item_id, $current_user->ID, 0, $item->item_type, $today, $today);
		$sql = $wpdb->query($sql);
		
		$msg_content = __('Item Picked up!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','check');
		$data['just_notify']=true;
		$data['success']=true;
		
		
		logActivity($adventure_id,'pickup','item',"$item->item_type",$item->item_id);
		resetPlayer($adventure_id,$current_user->ID);
	}elseif(wp_verify_nonce($nonce, 'pickup_item'.$current_user->ID.date('Ymd')) && $item->trnx_id){
		$msg_content = __('Item already in backpack!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'indigo','backpack');
		$data['just_notify']=true;
		$data['success']=true;
	}else{
		$msg_content = __('Unauthorized access!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify']=true;
	}
	echo json_encode($data);
	die();
}
////////////////////////////////////// pickupItem ITEM //////////////////////////////////
function checkItem(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure_id = $_POST['adventure_id'];
	$item_id = $_POST['item_id'];
	$nonce = $_POST['nonce'];
	$step_id = $_POST['step_id'];
	$data = array();
	$data['success'] = false;
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$notification = new Notification();	
	if(wp_verify_nonce($nonce, 'check_item'.$current_user->ID.date('Ymd').$step_id)){
		$itemVerification = $wpdb->get_row("SELECT items.*, steps.step_id, steps.step_order, steps.quest_id FROM {$wpdb->prefix}br_items items 
		JOIN {$wpdb->prefix}br_steps steps ON items.item_id=steps.step_item
		WHERE items.item_id=$item_id AND steps.step_id=$step_id
		");
		if($itemVerification){
			$msg_content = __("That's right!",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'green','check');
			$data['just_notify']=true;
			$data['success']=true;
			$theNextStepOrder = $itemVerification->step_order +1;
			$nextStepSearch = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$itemVerification->quest_id AND step_order = $theNextStepOrder AND step_status='publish'");
			
			if($nextStepSearch){ $nextStep = $nextStepSearch->step_order; }else{ $nextStep='last'; }
			$data['debug']=print_r($nextStepSearch,true);
			$data['jumpToNext']=$nextStep;
			logActivity($adventure_id,'chose-correct-item','step-item',"$itemVerification->item_name",$itemVerification->step_id, $itemVerification->item_id);
		}else{
			$msg_content = __('Wrong Item!','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify']=true;
			$data['success']=false;
			$data['jumpToNext']=false;
			logActivity($adventure_id,'chose-wrong-item','step-item',"",$step_id, $item_id);
		}
	}else{
		$msg_content = __('Unauthorized access!','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify']=true;
	}
	echo json_encode($data);
	die();
}
////////////////////////////////////// PAYMENT FUNCTION //////////////////////////////////
function payment(){
	global $wpdb; $current_user = wp_get_current_user();
	$nonce = $_POST['nonce'];
	$adventure_id = $_POST['adventure_id'];
	$data = array();
	$data['success'] = false;
	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	$notification = new Notification();
	
	if(wp_verify_nonce($nonce, 'br_payment_nonce')){
		$object_id = $_POST['object_id'];
		$type = $_POST['type'];
		$object = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$object_id AND quest_status='publish' AND adventure_id=$adv_parent_id");
		if($object){
			$cost = $object->mech_deadline_cost;
			$player = getPlayerAdventureData($adv_child_id,$current_user->ID);
			if($player->player_bloo >= $cost){
				$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
				VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";

				$sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $object->quest_id, $current_user->ID, $cost, $type, $today, $today);
				$sql = $wpdb->query($sql);
				$msg_content = __('Transaction Completed','bluerabbit');
				$data['message'] = $notification->pop($msg_content,'green','bloo');
				$data['just_notify']=true;
				$data['location']='reload';
				$data['success']=true;

				logActivity($adv_child_id,'purchase',$type,"",$blocker_id);
				$player_id = $current_user->ID;
				resetPlayer($adv_child_id,$player_id);
			}else{
				$data['message']="<h1>".__("Not enough funds","bluerabbit")."</h1>";
				$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
			}
		}else{
			$data['message']="<h1>".__("Quest doesn't exist","bluerabbit")."</h1>";
			$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
		}
	}else{
		$data['message']="<h1>".__("Unauthorized access","bluerabbit")."</h1>";
		$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
	}
	echo json_encode($data);
	die();
}

////////////////////////////////////// PAY BLOCKER //////////////////////////////////
function payBlocker(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure_id = $_POST['adventure_id'];
	$blocker_id = $_POST['blocker_id'];
	$nonce = $_POST['nonce'];
	$data = array();
	$data['success'] = false;
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	if(wp_verify_nonce($nonce, 'br_pay_blocker_nonce')){
		$blockerData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_blockers WHERE blocker_id=$blocker_id");
		$player = getPlayerAdventureData($adventure_id,$current_user->ID);
		if($player->player_bloo >= $blockerData->blocker_cost){
			$sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
			VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
			$sql = $wpdb->prepare($sql, $current_user->ID, $adventure_id, $blocker_id, $current_user->ID, $blockerData->blocker_cost, 'blocker', $today, $today);
			$sql = $wpdb->query($sql);
			$ann_content="<strong class='subject'>".$current_user->display_name."</strong> <span class='action'>".__("payed for the blocker","bluerabbit")." </span> <strong class='object'>#$blocker_id</strong>";;
			$ann= postAnn($adventure_id, $ann_content, 'system');
			$data['message']="<h1>".__("GREAT!","bluerabbit")."</h1>";
			$data['message'].="<h2>".__("Blocker Paid!","bluerabbit")."</h2>";
			$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
			$data['location']=get_bloginfo("url")."/blockers/?adventure_id=$adventure_id";
			$data['success']=true;
			logActivity($adventure_id,'purchase','blocker',"",$blocker_id);
			$player_id = $current_user->ID;
			resetPlayer($adventure_id,$player_id);
		}else{
			$data['message']="<h1>".__("Not enough funds","bluerabbit")."</h1>";
			$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
		}
	}else{
		$data['message']="<h1>".__("Unauthorized access","bluerabbit")."</h1>";
		$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
	}
	echo json_encode($data);
	die();
}


////////////////////////////////////// USE ITEM //////////////////////////////////
function useItem(){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data = array();
	$adventure_id = $_POST['adventure_id'];
	$trnx_id =  $_POST['trnx_id'];
	$item_name = $_POST['item_name'];
	$use_item =  $_POST['use_item'] ? 1 : 0;
	$nonce = $_POST['nonce'];
	$player_id = $_POST['player_id'] ? $_POST['player_id'] : $current_user->ID;
	if($player_id != $current_user->ID){
		$player_target = get_userdata($player_id);
	}else{
		$player_target = $current_user;
	}
	$prepared = $wpdb->prepare("SELECT trnxs.*, items.item_name FROM {$wpdb->prefix}br_transactions trnxs
	JOIN {$wpdb->prefix}br_items items
	ON trnxs.object_id = items.item_id
	WHERE trnxs.trnx_id=%d AND trnxs.trnx_status='publish' AND items.item_status='publish'", $trnx_id);
	$query = $wpdb->query($prepared);
	$trnx = $wpdb->last_result;
	$trnx = $trnx[0];
	$data['success'] = false;
	if(wp_verify_nonce($nonce, 'br_use_item_nonce')){
		if($trnx){
			$sql = "UPDATE {$wpdb->prefix}br_transactions SET trnx_use=%d, player_id=%d, trnx_author=%d WHERE trnx_id=%s";
			$sql = $wpdb->prepare($sql, $use_item, $player_id, $current_user->ID, $trnx_id);
			$sql = $wpdb->query($sql);
			if($wpdb->rows_affected > 0){
				if(!$use_item && $trnx->trnx_use){
					// RETURN USE
					$data['message'].="<h1>".__("Item returned!","bluerabbit")."</h1>";
					$data['message'].="<h4><strong>".__("The player has returned the item.","bluerabbit")."</strong></h4>";
					logActivity($adventure_id,'return-use','item',"$trnx->item_name",$trnx->object_id);
				}elseif($use_item && $trnx->trnx_use){
					/// Duplicate USE
					$data['message'].="<h1>".__("Item already used!","bluerabbit")."</h1>";
					logActivity($adventure_id,'duplicate-use','item',"$trnx->item_name",$trnx->object_id);
				}elseif($use_item && !$trnx->trnx_use){
					/// Register USE
					$data['message'].="<h1>".__("Item redeemed!","bluerabbit")."</h1>";
					$data['message'].="<h4><strong>".__("We've registered you picking up your reward.","bluerabbit")."</strong></h4>";
					logActivity($adventure_id,'use','item',"$trnx->item_name",$trnx->object_id);
				}
				$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
				$data['success']=true;
				$data['location']="reload";
				resetPlayer($adventure_id,$player_id);
			}else{
				$data['message'].="<h1>".__("DB Error, please contact admin","bluerabbit")."</h1>";
				$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
			}
		}else{
			$data['message'].="<h1>".__("Transaction doesn't exist!","bluerabbit")."</h1>";
			$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
		}
	}else{
		$data['message'].="<h1>".__("Unauthorized access","bluerabbit")."</h1>";
		$data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
	}
	echo json_encode($data);
	die();
}

////////////////////////////////////// POST TO WALL //////////////////////////////////
function postToWall(){
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
			$ann = postAnn($adventure_id, $ann_content, $type);
			if($ann){
				if($type=='announcement'){
					logActivity($adventure_id,'posted-announcement','wall-post',"$ann_content");
				}elseif($type=='guild'){
					logActivity($adventure_id,'posted-guild','wall-post',"$ann_content");
				}else{
					logActivity($adventure_id,'posted-public','wall-post',"$ann_content");
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
////////////////////////////////////// POST ANN //////////////////////////////////
function postAnn($adventure_id, $ann_content, $type){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
	LEFT JOIN {$wpdb->prefix}br_player_adventure b
	ON a.adventure_id=b.adventure_id AND b.player_id=$current_user->ID
	WHERE a.adventure_id=$adventure_id");

	$ann_sql = "INSERT INTO {$wpdb->prefix}br_announcements (adventure_id, ann_content, ann_type, ann_author)
	VALUES (%d, %s, %s, %d)";
	$ann_sql = $wpdb->prepare($ann_sql, $adventure_id, $ann_content, $type, $current_user->ID);
	$ann_sql = $wpdb->query($ann_sql);
	if($wpdb->insert_id){
		return $adventure;
	}else{
		return false;
	}
	die();
}

////////////////////////////////////// LOAD CHAT //////////////////////////////////
function loadChat(){
	$type=$_POST['type'];
	$adventure_id=$_POST['adventure_id'];
	$guild_id  =$_POST['guild_id'];
	$current_user = wp_get_current_user();
	$theFile = (get_template_directory()."/msg-$type.php");
	include ($theFile);
	die();
}
/////////////////////////// CHANGE RANK //////////////////////
function switchRank($p_achievement_id="", $p_adventure_id=""){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success']=false;
	$n = new Notification();
	$achievement_id = $p_achievement_id ? $p_achievement_id : $_POST['achievement_id'];
	$adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];

	$adventure = getAdventure($adventure_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

	$sql = "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=%d WHERE player_id=%d AND adventure_id=%d";
	$sql = $wpdb->prepare($sql,$achievement_id,$current_user->ID, $adv_child_id);
	$sql = $wpdb->query($sql);
	if(!$p_achievement_id){
		$data['just_notify'] =true;
		if($sql!==false){
			$data['success'] = true;
			$msg_content = __('Rank updated','bluerabbit');
			$data['message'] = $n->pop($msg_content,'green','check');
		}else{
			$data['success'] = true;
			$msg_content = __('Error, please reload and try again','bluerabbit');
			$data['message'] = $n->pop($msg_content,'red','cancel');
		}
		echo json_encode($data);
		die();
	}else{
		return false;
	}
}

////////////////////////////////////// GET ADVENTURE //////////////////////////////////
function getAdventure($adventure_id=NULL){
	if($adventure_id){
		global $wpdb; $current_user = wp_get_current_user();

		$roles = $current_user->roles;
		if($roles[0]=='administrator'){
			$isAdmin=true;
		}
		if(is_page('new-adventure')){
			if($isAdmin){
				$adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
				LEFT JOIN {$wpdb->prefix}br_player_adventure c
				ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
				WHERE a.adventure_id=$adventure_id ");
			}else{
				$adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
				JOIN {$wpdb->prefix}br_player_adventure c
				ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
				WHERE a.adventure_id=$adventure_id ");
			}
		}else{
			$adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
			JOIN {$wpdb->prefix}br_player_adventure c
			ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
			WHERE a.adventure_id=$adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
		}
		return $adventure;
	}else{
		return false;
	}
}
////////////////////////////////////// GET ADVENTURE //////////////////////////////////
function getAdventureParent($adventure_id){
	global $wpdb; 
	$adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
	WHERE a.adventure_id=$adventure_id AND a.adventure_status='publish' AND a.adventure_type='template'");
	if($adventure){
		return $adventure;
	}else{
		return false;
	}
}

////////////////////////////////////// GET SPONSORS //////////////////////////////////
function getSponsors($adventure_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	if(isset($roles[0]) && $roles[0]=='administrator'){
		$isAdmin=true;
	}
	if(!$adventure_id){
		$sponsors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_sponsors WHERE adventure_id <= 0 AND sponsor_status = 'publish'");
	}else{
		$sponsors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_sponsors WHERE adventure_id = $adventure_id AND sponsor_status = 'publish'");
	}
	return $sponsors;
}

////////////////////////////////////// GET Organizations //////////////////////////////////
function getOrgs($org_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	if($roles[0]=='administrator'){
		$isAdmin=true;
		if($org_id){
			$org = $wpdb->get_row("SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_orgs orgs 
			LEFT JOIN {$wpdb->prefix}br_players players
			ON orgs.owner_id = players.player_id
			WHERE orgs.org_id=$org_id");
			return $org;
		}else{
			$orgs = $wpdb->get_results("SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_orgs orgs 
				LEFT JOIN {$wpdb->prefix}br_players players
				ON orgs.owner_id = players.player_id
			");
			return $orgs;
		}
	}else{
		return false ;
	}
}


function getOrgAdventures($org_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	if($roles[0]=='administrator' && $org_id){
		$adventures = $wpdb->get_row("SELECT adventures.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_adventures adventures 
		JOIN {$wpdb->prefix}br_players players
		ON adventures.adventure_owner = players.player_id
		WHERE adventures.org_id=$org_id");
		return $adventures;
	}else{
		return false ;
	}
}
function getOrgPlayers($org_id=NULL){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	if($roles[0]=='administrator' && $org_id){
		$players = $wpdb->get_results("SELECT players.*, org.role FROM {$wpdb->prefix}br_players players 
		JOIN {$wpdb->prefix}br_player_org org
		ON org.player_id = players.player_id
		WHERE org.org_id=$org_id");
		return $players;
	}else{
		return false ;
	}
}
function setPlayerOrgCapabilities(){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$n = new Notification();
	$data = array();
	if($roles[0]=='administrator'){
		$org_id = $_POST['org_id'];
		$player_id = $_POST['player_id'];
		$role = $_POST['role'];
		
		$update_string = "UPDATE {$wpdb->prefix}br_player_org SET `role`=%s WHERE `org_id`=%d AND `player_id`=%d";
		$updatedPlayer = $wpdb->query( $wpdb->prepare("$update_string ", $role, $org_id, $player_id));
		$data['org_role_update'] = true;
		$data['player_id'] = $player_id;
		$data['role_update'] = $role;
		
		$data['success'] = true;
        $msg_content = __('Role Updated','bluerabbit');
        $data['message'] = $n->pop($msg_content,'green','check');
        $data['just_notify'] =true;
	}else{
		$data['success'] = false;
        $msg_content = __('Error. Role Not Updated','bluerabbit');
        $data['message'] = $n->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
function findPlayersToOrg(){
	global $wpdb; $current_user = wp_get_current_user();
	$roles = $current_user->roles;
	if($roles[0]=='administrator' && wp_verify_nonce($_POST['nonce'], 'br_search_player_org_nonce')){
		$search_str = $_POST['search_string'];
		$search_str = '%'.$search_str.'%';
		$players_results = "SELECT players.* FROM {$wpdb->prefix}br_players players 
		WHERE (`players`.`player_email` LIKE %s 
		OR `players`.`player_first` LIKE %s 
		OR `players`.`player_last` LIKE %s 
		OR `players`.`player_display_name` LIKE %s)";
		$players_results = $wpdb->get_results($wpdb->prepare($players_results, $search_str, $search_str, $search_str, $search_str));
		if($players_results){
			foreach ($players_results as $p){
				$theFile = (get_template_directory()."/player-select-org.php");
				if(file_exists($theFile)) {
					include ($theFile);
				}
			}
			die();
		}else{
			echo "
<li class='margin-5'>
	<div class='icon-group'>
		<button class='icon-button player-picture white-bg sq-60'>

		</button>
		<div class='icon-content text-left'>
			<span class='line font _18 player-name'>".__("No results","bluerabbit")."
			</span>
		</div>
	</div>
</li>
";
		}
		die();
	}else{
		return false ;
	}
}

function addPlayerToOrg(){
	global $wpdb; $current_user = wp_get_current_user();
	$player = getPlayerData($_POST['player_id']);
	$org = getOrgs($_POST['org_id']);
	if($player && $org && $current_user->roles[0]=='administrator'){
		$addToOrg = "INSERT INTO {$wpdb->prefix}br_player_org (`player_id`, `org_id`) VALUES (%d, %d)";	
		$code_insert = $wpdb->query( $wpdb->prepare("$addToOrg ", $player->player_id, $org->org_id));
		$theFile = (get_template_directory()."/player-row-org.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}else{
		return false;
	}

}


function previewTemplate(){
	global $wpdb; $current_user = wp_get_current_user();
	$adventure_id = isset($_POST['adventure_id']) ? ($_POST['adventure_id']) : "";
	if($adventure_id){
		$theFile = (get_template_directory()."/template-preview.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}
		die();
	}else{
		return false;
	}
}
function createChildAdventure($template_id=0){
	global $wpdb; $current_user = wp_get_current_user();
	
	$data=array();

	if(isset($_POST['adventure_id'])){
		$adventure_id = $_POST['adventure_id'];
		$adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
		WHERE a.adventure_id=$adventure_id AND a.adventure_status='publish'");
	}else{
		$adventure = NULL;
	}
	$n = new Notification();
	if($adventure){
		$player_data = getPlayerData($current_user->ID);
			
		$first_str = random_str(12,'1234567890abcdef');
		$code_string = $first_str.$current_user->ID;
		$adventure_code = str_shuffle($code_string);
		
		$duplication = "
			INSERT INTO {$wpdb->prefix}br_adventures

			(`adventure_owner`, `adventure_badge`, `adventure_logo`, `adventure_gmt`, `adventure_type`, `adventure_title`, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, `adventure_code`, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, `adventure_parent`, `org_id`)

			SELECT 

			%d,`adventure_badge`, `adventure_logo`, `adventure_gmt`, 'normal', `adventure_title`, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, %s, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, %d, %d

			FROM  {$wpdb->prefix}br_adventures WHERE `adventure_id` = %d;
		";
		$sql = $wpdb->prepare($duplication, $current_user->ID, $adventure_code,  $adventure->adventure_id, $player_data->org_id, $adventure->adventure_id);
		$duplicatedAdventureQuery = $wpdb->query($sql);
		//$data['debug'] = print_r($wpdb->last_query,true);
		$newAdvID = $wpdb->insert_id;
		
		/////////// CLONE THE FEATURES
		
		$adv_features_duplication = "
			INSERT INTO {$wpdb->prefix}br_settings
			(`setting_id`, `setting_name`, `setting_label`, `setting_value`, `adventure_id`)
			SELECT 
			'', `setting_name`, `setting_label`, `setting_value`, %d
			FROM  {$wpdb->prefix}br_settings WHERE `adventure_id` = %d;
		";
		$adv_features = $wpdb->query($wpdb->prepare($adv_features_duplication, $newAdvID, $adventure->adventure_id ));
		
		// ADD PLAYERS TO ADVENTURE CURRENT USER AS NPC 
		$insertPlayerSQL = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_role) VALUES (%d,%d,%s)";
		$insertPlayerSQL = $wpdb->query($wpdb->prepare ($insertPlayerSQL, $newAdvID, $current_user->ID, 'npc'));
		$data['success'] = true;
		$data['message'] = "<h1>".__('Adventure created successfully','bluerabbit')."</h1><h4>".__('(click to continue)','bluerabbit')."</h4>";
		$data['location'] = get_bloginfo('url')."/adventure/?adventure_id=".$newAdvID;
	}else{
		$data['new_adventure_from_template'] = false;
		$data['success'] = false;
		$data['message'] = "<h1>".__('Adventure not created','bluerabbit')."</h1><h4>".__('(please refresh and try again or contact admin)','bluerabbit')."</h4>";
		$data['location'] = 'reload';
	}
	echo json_encode($data);
	die();

}



////////////////////////////////////// GET QUEST //////////////////////////////////
function getQuest($quest_id=NULL){
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

////////////////////////////////////// GET QUESTS //////////////////////////////////
function getQuests($adventure_id, $quest_type="", $quest_type_exclude="", $order="", $path=''){
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
		}elseif($o->quest_status == 'publish'){
			$result['publish'][]=$o;
		}
	}
	return $result;
}
////////////////////////////////////// GET MY QUESTS //////////////////////////////////
function getMyQuests($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");

	$qry = $wpdb->get_results("SELECT *	FROM {$wpdb->prefix}br_player_posts 
	WHERE adventure_id=$adventure_id AND player_id=$current_user->ID AND pp_status = 'publish'");
	$result = array();
	$after = $adventure->adventure_progression_type;
	foreach($qry as $ppKey=>$pp){
		if($pp->pp_status == "publish"){
			if($pp->pp_grade > 0 && $after == "after" || $after == "before"){
				$result[]=$pp->quest_id;
			}
		}
	}
	return $result;
}

////////////////////////////////////// GET MY ACHIEVEMENTS //////////////////////////////////
function getMyAchievements($adventure_id, $player_id=null){
	global $wpdb; 
	if(!$player_id){
		$current_user = wp_get_current_user();
		$player_id=$current_user->ID;
	}
	$result = $wpdb->get_col("SELECT a.achievement_id
	FROM {$wpdb->prefix}br_achievements a
	JOIN {$wpdb->prefix}br_player_achievement b
	ON a.achievement_id = b.achievement_id AND a.adventure_id=b.adventure_id AND b.player_id=$player_id
	WHERE a.adventure_id=$adventure_id AND a.achievement_status='publish' AND b.player_id=$player_id");
	return $result;
}

////////////////////////////////////// GET MY ITEMS //////////////////////////////////
function getMyItems($adventure_id, $player_id=null){
	global $wpdb; 
	if(!$player_id){
		$current_user = wp_get_current_user();
		$player_id=$current_user->ID;
	}
	
	$qry = $wpdb->get_results( "SELECT items.item_id, items.item_name, items.item_description, items.item_secret_description, items.item_type, items.item_badge, items.item_secret_badge,  items.item_level,  items.item_cost, items.tabi_id, tabis.tabi_name,
		trnxs.object_id, trnxs.trnx_id, trnxs.trnx_type, trnxs.trnx_date, COUNT(items.item_id) AS total_consumables
		FROM  {$wpdb->prefix}br_items items 
		JOIN {$wpdb->prefix}br_transactions trnxs
		ON items.item_id = trnxs.object_id

		LEFT JOIN {$wpdb->prefix}br_tabis tabis
		ON items.tabi_id = tabis.tabi_id


		WHERE items.adventure_id=$adventure_id AND items.item_status='publish' AND trnxs.player_id=$player_id AND (trnxs.trnx_type='consumable' OR trnxs.trnx_type='key' OR trnxs.trnx_type='reward' OR trnxs.trnx_type='tabi-piece') AND trnxs.trnx_use=0 AND trnxs.trnx_status='publish'
		GROUP BY trnxs.object_id, trnxs.trnx_type ORDER BY FIELD(items.item_type, 'consumable', 'key', 'tabi-piece', 'reward'), items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
	$result = array();
	foreach($qry as $o){
		$result['all'][]=$o;
		if($o->item_type == 'key' || $o->item_type == 'tabi-piece'){
			$result['key'][$o->item_id]=$o;
			$result['ids']['key']=$o->item_id;
		}elseif($o->item_type == 'consumable'){
			$result['consumable'][$o->item_id]=$o;
			$result['ids']['consumable']=$o->item_id;
		}elseif($o->item_type == 'reward'){
			$result['reward'][$o->item_id]=$o;
			$result['ids']['reward']=$o->item_id;
		}
	}
	return $result;
}
////////////////////////////////////// GET CHALLENGE //////////////////////////////////
function getChallenge($challenge_id, $adv_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = array();
	$challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests
	WHERE quest_id=$challenge_id AND quest_status='publish' AND quest_type='challenge'");


	$adventure = getAdventure($adv_id);
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	$player = getPlayerAdventureData($adv_child_id, $current_user->ID);



	$attempts = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}br_challenge_attempts` WHERE `quest_id`=$challenge_id AND `player_id`=$current_user->ID AND attempt_status !='trash' AND adventure_id=$adv_child_id");
	$result['challenge']=$challenge;
	
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }

	$requirements = $wpdb->get_results("SELECT 
	
	a.req_object_id, a.req_type, a.req_object_id,
	b.mech_level, b.mech_xp, b.mech_bloo,
	c.quest_title, c.quest_type,
	d.item_name
	
	FROM {$wpdb->prefix}br_reqs a
	LEFT JOIN {$wpdb->prefix}br_quests b
	ON a.quest_id = b.quest_id AND b.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_quests c
	ON a.req_object_id = c.quest_id AND c.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_items d
	ON a.req_object_id = d.item_id
	
	WHERE a.adventure_id=$adv_parent_id AND a.quest_id=$challenge_id
	
	");
	if($requirements){
		$reqs = array(); $reqs_ids = array();
		foreach($requirements as $r){
			$reqs[]=$r;
			$reqs_ids[$r->req_type][]=$r->req_object_id;
		}
		sort($reqs_ids['quest']);		
		$reqs_list = implode(",",$reqs_ids['quest']);

		$player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
		WHERE adventure_id=$adv_child_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
		
		sort($player_quests);
		
		$my_items = getMyItems($adv_child_id); 
		if(isset($my_items['ids']['key'])){
			$myKeyItems = $my_items['ids']['key'];
			$allItemsSet = array_intersect($myKeyItems,$reqs_ids['item']);
		}else{
			$allItemsSet = [];
		}
		$allQuestsSet = array_intersect($player_quests, $reqs_ids['quest']);
		if($allQuestsSet == $reqs_ids['quest']){ $questsReady=true;}
		if($allItemsSet == $reqs_ids['item']){ $itemsReady=true;}
		if($questsReady && $itemsReady ){
			// $reqs ok
		}else{
			$result['unavailable']=true;
			$result['locks']['requirements']=true;
			$result['message']="<span class='icon icon-xl icon-lock solid-red'></span>";
			$result['message'].="<h1><strong>".__("Requierements not met!","bluerabbit")."</strong></h1>";
			$result['message'].="<h3>".__("Can't attempt this challenge. Finish other quests and come back!","bluerabbit")."</h3>";
		}
	}
	/// Check for MAX grade
	$best_grade=array();
	$fails=0;
	foreach($attempts as $att){
		if($att->attempt_status != 'success'){
			$fails++;
		}else{
			$best_grade[]=$att->attempt_grade;
		}
	}
	$questions = $wpdb->get_results("SELECT
	a.question_id, a.question_title, a.question_image, a.question_type
	FROM {$wpdb->prefix}br_challenge_questions a
	WHERE a.quest_id=$challenge_id AND a.question_status='publish'");
	$maxGrade = count($best_grade) > 0 ? max($best_grade) : 0;
	$result['fails']=$fails;
	$result['attempts']=$attempts;
	$result['freeAttempts']=$challenge->mech_free_attempts - count($attempts);
	$result['questions']=$questions;
	if($player->player_level >= $challenge->mech_level){
		$today = date('YmdHi');
		$deadline = date('YmdHi',strtotime($challenge->mech_deadline));
		if($challenge->mech_deadline == '0000-00-00 00:00:00' || $deadline >= $today || $challenge->trnx_id){
			if($fails < $challenge->mech_max_attempts || $challenge->mech_max_attempts <=0){
				if($maxGrade >=100){
					$result['locks']['conquered']=true;
					$result['message']="<span class='icon icon-xl icon-challenge'></span><h1>".__("Challenge Already Conquered!","bluerabbit")."</h1>";
					$result['location']=get_bloginfo('url')."/post/?adventure_id=$adv_child_id&questID=$challenge_id";
				}
				$answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE quest_id=$challenge_id AND answer_status='publish'");
				$result['answers']=$answers;
			}else{
				$result['unavailable']=true;
				$result['locks']['max_attempts']=true;
				$result['message']="<span class='icon icon-xl icon-lock solid-red'></span>";
				$result['message'].="<h1><strong>".__("Max Attempts Reached!","bluerabbit")."</strong></h1>";
				$result['message'].="<h3>".__("Can't attempt this challenge anymore. Keep moving forward!","bluerabbit")."</h3>";
			}
		}else{
			$result['unavailable']=true;
			$result['locks']['deadline']=true;
			if($challenge->mech_deadline_cost > 0){
				$result['locks']['deadline_cost']=true;
			}
		}
	}else{
		$result['unavailable']=true;
		$result['locks']['level']=true;
		$result['message']="<span class='icon icon-xl icon-level solid-purple'></span><h1>".__("Available for level","bluerabbit")."<strong>$challenge->mech_level</strong></h1>";
	}
	return $result;
}
//////////////////////GET SURVEY //////////////////
function getSurvey($survey_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = array();
	$answers_query = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}br_survey_answers` WHERE `survey_id`=$survey_id AND `player_id`=$current_user->ID ");



	$survey = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$survey_id AND quest_type='survey' AND quest_status='publish'");

	$all_qs = $wpdb->get_results("SELECT a.*, b.survey_option_id, b.survey_option_text, b.survey_option_image
	FROM {$wpdb->prefix}br_survey_questions a
	LEFT JOIN {$wpdb->prefix}br_survey_options b
	ON a.survey_question_id = b.survey_question_id AND b.survey_option_status='publish'
	WHERE a.survey_id=$survey_id AND a.survey_question_status='publish' ORDER BY a.survey_question_order, a.survey_question_id
	");
	$answers = array();
	foreach ($answers_query as $an){
		$answers['a_'.$an->survey_question_id]['selected_answer'] = $an->survey_option_id;
		$answers['a_'.$an->survey_question_id]['survey_answer_value'] = $an->survey_answer_value;
	}
	$questions = array();
	foreach($all_qs as $kq=>$qs){
		$questions[$qs->survey_question_id]['text']=$qs->survey_question_text;
		$questions[$qs->survey_question_id]['image']=$qs->survey_question_image;
		$questions[$qs->survey_question_id]['range']=$qs->survey_question_range;
		$questions[$qs->survey_question_id]['survey_question_description']=$qs->survey_question_description;
		$questions[$qs->survey_question_id]['survey_question_display']=$qs->survey_question_display;
		$questions[$qs->survey_question_id]['survey_question_type']=$qs->survey_question_type;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['text']=$qs->survey_option_text;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['image']=$qs->survey_option_image;
		if(isset($answers['a_'.$qs->survey_question_id])){
			$questions[$qs->survey_question_id]['survey_answer_value']=$answers['a_'.$qs->survey_question_id]['survey_answer_value'];
			$questions[$qs->survey_question_id]['selected_answer']=$answers['a_'.$qs->survey_question_id]['selected_answer'];
		}
	}
	$result['survey']=$survey;
	$result['questions']=$questions;
	$result['answers']=$answers;
	return $result;
}
function getSurveyResults($survey_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = array();
	$answers = $wpdb->get_results("SELECT a.*, b.player_first, b.player_last FROM `{$wpdb->prefix}br_survey_answers` a
	LEFT JOIN `{$wpdb->prefix}br_players` b
	ON a.player_id = b.player_id
	
	WHERE a.survey_id=$survey_id");
	
	$survey = $wpdb->get_row("SELECT
	a.quest_id, a.quest_title, a.adventure_id, a.achievement_id, a.mech_level, a.mech_xp, a.mech_bloo, a.mech_ep, a.mech_badge, a.mech_deadline, a.mech_start_date,
	c.player_id, c.player_level,
	ach.achievement_name
	FROM {$wpdb->prefix}br_quests a
	LEFT JOIN {$wpdb->prefix}br_player_adventure c
	ON a.adventure_id=c.adventure_id AND c.player_id=$current_user->ID
	
	LEFT JOIN {$wpdb->prefix}br_achievements ach
	ON a.achievement_id = ach.achievement_id AND ach.achievement_status='publish'

	
	WHERE a.quest_id=$survey_id AND a.quest_type='survey' AND c.player_id=$current_user->ID AND c.player_adventure_status='in'");
	
	$all_qs = $wpdb->get_results("
	SELECT a.*, b.survey_option_id, b.survey_option_text, b.survey_option_image
	FROM {$wpdb->prefix}br_survey_questions a
	LEFT JOIN {$wpdb->prefix}br_survey_options b
	ON a.survey_question_id = b.survey_question_id AND b.survey_option_status='publish'
	
	WHERE a.survey_id=$survey_id AND a.survey_question_status='publish'	ORDER BY a.survey_question_type, a.survey_question_order, a.survey_question_id
	");
	$answer_values = array();
	foreach($answers as $a){
		$answer_values[$a->survey_question_id][$a->survey_option_id]++;
		$answer_values[$a->survey_question_id]['total']++;
		$answer_values[$a->survey_question_id]['values'][]=$a->survey_answer_value;
		$answer_values[$a->survey_question_id]['player'][]=$a->player_first." ".$a->player_last;
	}
	$questions = array();
	foreach($all_qs as $kq=>$qs){
		$questions[$qs->survey_question_id]['text']=$qs->survey_question_text;
		$questions[$qs->survey_question_id]['image']=$qs->survey_question_image;
		$questions[$qs->survey_question_id]['range']=$qs->survey_question_range;
		$questions[$qs->survey_question_id]['survey_question_display']=$qs->survey_question_display;
		$questions[$qs->survey_question_id]['survey_question_description']=$qs->survey_question_description;
		$questions[$qs->survey_question_id]['survey_question_type']=$qs->survey_question_type;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['text']=$qs->survey_option_text;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['image']=$qs->survey_option_image;
		$average = round($answer_values[$qs->survey_question_id][$qs->survey_option_id]/$answer_values[$qs->survey_question_id]['total']*100);
		$average = is_nan($average) ? 0 : $average;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['value']=$average;
		$questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['total_answers']=($answer_values[$qs->survey_question_id][$qs->survey_option_id]);
	}

	$result['survey']=$survey;
	$result['questions']=$questions;
	$result['answers']=$answers;
	$result['answer_values']=$answer_values;
	return $result;
}
////////////////////////////////////// GET SPEAKERS //////////////////////////////////
function getSpeakers($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	$qry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_speakers
	WHERE adventure_id=$adventure_id ORDER BY speaker_first_name");
	$result = array();
	foreach($qry as $o){
		if($o->speaker_status == 'trash'){
			$result['trash'][]=$o;
		}elseif($o->speaker_status == 'draft'){
			$result['draft'][]=$o;
		}elseif($o->speaker_status == 'publish'){
			$result['publish'][]=$o;
		}
	}
	return $result;
}
////////////////////////////////////// GET TALKS //////////////////////////////////
function getSessions($adventure_id, $p_status=''){
	global $wpdb; $current_user = wp_get_current_user();
	if($p_status=='publish'){
		
		$status = " AND sessions.session_status='$p_status' ";
		
	}elseif($p_status=='hide'){
		$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$date = date('Y-m-d');
		$status = " AND sessions.session_status='publish' AND DATE(sessions.session_start)=DATE(NOW()) ";
	}else{
		$status = "";
	}
	$qry = $wpdb->get_results("SELECT
	sessions.*,
	speakers.speaker_first_name, speakers.speaker_last_name, speakers.speaker_picture, speakers.speaker_company, speakers.speaker_bio,
	quests.quest_title, quests.quest_type
		FROM {$wpdb->prefix}br_sessions sessions
		LEFT JOIN {$wpdb->prefix}br_speakers speakers
		ON sessions.speaker_id = speakers.speaker_id
		LEFT JOIN {$wpdb->prefix}br_quests quests
		ON sessions.quest_id = quests.quest_id AND quests.quest_status = 'publish'
	WHERE sessions.adventure_id=$adventure_id $status
	ORDER BY sessions.session_start, sessions.session_order, sessions.session_id
	");
	if($p_status && $qry){
		$result = $qry;
	}else{
		$result = array();
		foreach($qry as $o){
			if($o->session_status == 'trash'){
				$result['trash'][]=$o;
			}elseif($o->session_status == 'draft'){
				$result['draft'][]=$o;
			}elseif($o->session_status == 'publish'){
				$result['publish'][]=$o;
			}
		}
	}
	return $result;
}
////////////////////////////////////// GET SPEAKER TALKS //////////////////////////////////
function getSpeakerSessions($adventure_id, $speaker_id){
	global $wpdb; $current_user = wp_get_current_user();
	$qry = $wpdb->get_results("SELECT
	sessions.*,
	speakers.speaker_first_name, speakers.speaker_last_name, speakers.speaker_picture, speakers.speaker_company, speakers.speaker_bio,
	quests.quest_title, quests.quest_type
		FROM {$wpdb->prefix}br_sessions sessions
		LEFT JOIN {$wpdb->prefix}br_speakers speakers
		ON sessions.speaker_id = speakers.speaker_id
		LEFT JOIN {$wpdb->prefix}br_quests quests
		ON sessions.quest_id = quests.quest_id AND quests.quest_status = 'publish'
	WHERE sessions.adventure_id=$adventure_id AND sessions.session_status='publish'  AND sessions.speaker_id=$speaker_id
	ORDER BY sessions.session_start, sessions.session_order, sessions.session_id
	");

	return $qry;
}


////////////////////////////////////// GET objectives //////////////////////////////////

function getObjectives($p_adv_id='', $quest_id='', $player_id=null){
	global $wpdb; 
	
	if(!$player_id){
		$current_user = wp_get_current_user();
		$player_id = $current_user->ID;
	}
    $adventure_id=$p_adv_id ? $p_adv_id : $_POST['adventure_id'];

	$adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

    $objectives = $wpdb->get_results("
        SELECT objectives.*, player.player_id, player.timestamp, player.adventure_id AS child_adventure FROM {$wpdb->prefix}br_objectives objectives
        LEFT JOIN {$wpdb->prefix}br_player_objectives player ON objectives.objective_id = player.objective_id AND player.player_id=$player_id AND player.adventure_id=$adv_child_id
        WHERE objectives.adventure_id=$adv_parent_id AND objectives.objective_status='publish' AND objectives.quest_id=$quest_id
    ");
    return $objectives;
}

function getObjective($obj_id='', $player_id=null){
	global $wpdb; 
	
	if(!$player_id){
		$current_user = wp_get_current_user();
		$player_id = $current_user->ID;
	}
    $objective = $wpdb->get_row("
        SELECT objectives.*, player.player_id, player.timestamp, player.adventure_id AS child_adventure FROM {$wpdb->prefix}br_objectives objectives
        LEFT JOIN {$wpdb->prefix}br_player_objectives player ON objectives.objective_id = player.objective_id AND player.player_id=$player_id
        WHERE objectives.objective_id=$obj_id AND objectives.objective_status='publish' 
    ");
    return $objective;
}


////////////////////////////////////// GET Tabis //////////////////////////////////
function getTabis($adventure_id){
	global $wpdb;
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_tabis
	WHERE adventure_id=$adventure_id AND tabi_status='publish'");
	return $result;
}
function getTabi($tabi_id){
	global $wpdb;
	$data = [];

	$data['tabi'] =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
	if($data['tabi']){
		$pieces = $wpdb->get_results("SELECT items.* FROM {$wpdb->prefix}br_items items
		JOIN {$wpdb->prefix}br_tabis tabis ON items.tabi_id = tabis.tabi_id
		WHERE items.tabi_id=$tabi_id AND items.item_status = 'publish' AND tabis.tabi_status='publish' ORDER BY items.item_z DESC");
		$data['pieces'] = $pieces;
		return $data;
	}else{
		return false;
	}
}
function getMyTabi($tabi_id){
	global $wpdb;
	$current_user = wp_get_current_user();
	$data = [];
	$data['tabi'] =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
	$adventure = getAdventure($data['tabi']->adventure_id);

	$adv_child_id = $adventure->adventure_id;
	$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
	if($data['tabi']){
		$tabi_id = $data['tabi']->tabi_id;
		$pieces =$wpdb->get_results( "SELECT items.*, tabis.tabi_name,
		trnxs.object_id, trnxs.trnx_id, trnxs.trnx_type, trnxs.trnx_date, COUNT(items.item_id) AS total_consumables
		FROM  {$wpdb->prefix}br_items items 
		JOIN {$wpdb->prefix}br_transactions trnxs
		ON items.item_id = trnxs.object_id

		JOIN {$wpdb->prefix}br_tabis tabis
		ON items.tabi_id = tabis.tabi_id


		WHERE items.adventure_id=$adv_parent_id AND items.item_status='publish' AND trnxs.player_id=$current_user->ID AND trnxs.adventure_id=$adv_child_id AND trnxs.trnx_type='tabi-piece' AND trnxs.trnx_status='publish' AND items.tabi_id=$tabi_id
		GROUP BY trnxs.object_id, trnxs.trnx_type ORDER BY items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
		$data['pieces'] = $pieces;
		return $data;
	}else{
		return false;
	}
}
function saveTabiPiecePosition(){
	global $wpdb; $current_user = wp_get_current_user();
	$item_id = $_POST['item_id'];
	$item_data = $_POST['item_data'];
	$notification = new Notification();
	$data['just_notify'] =true;
	$item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items items WHERE item_id=$item_id AND item_status='publish' AND item_type='tabi-piece'");
	$x = $item_data['item_x'];
	$y = $item_data['item_y'];
	$z = $item_data['item_z'];
	$scale = $item_data['item_scale'];
	$rotation = $item_data['item_rotation'];
	if($item){
		$sql = "UPDATE {$wpdb->prefix}br_items SET item_x=%s, item_y=%s, item_z=%d, item_scale=%d, item_rotation=%d WHERE item_id=$item->item_id";
		$sql = $wpdb->prepare ($sql , $x, $y, $z, $scale, $rotation );
		$wpdb->query($sql); 
		$data['success'] = true;
	}else{
		$data['success'] = false;
        $msg_content = __('Item not found','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
	}
	echo json_encode($data);
	die();


}
function updateMilestonePosition(){
	global $wpdb; $current_user = wp_get_current_user();
	$milestone_id = $_POST['milestone_id'];
	$milestone_data = $_POST['milestone_data'];
	$notification = new Notification();
	$data['just_notify'] =true;

	$milestone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests quests WHERE quest_id=$milestone_id AND quest_status='publish'");


	$x = $milestone_data['x'];
	$y = $milestone_data['y'];
	$z = $milestone_data['z'];
	$top = $milestone_data['top'];
	$left = $milestone_data['left'];
	$rotation = $milestone_data['rotation'];
	if($milestone){
		$sql = "UPDATE {$wpdb->prefix}br_quests SET milestone_x=%s, milestone_y=%s, milestone_z=%s, milestone_top=%d, milestone_left=%d, milestone_rotation=%d WHERE quest_id=$milestone->quest_id";
		$sql = $wpdb->prepare ($sql, $x, $y, $z, $top, $left, $rotation );
		$wpdb->query($sql); 
		$data['success'] = true;
        $msg_content = __('Milestone updated','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'green','check');
	}else{
		$data['success'] = false;
        $msg_content = __('Milestone not found','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
	}
	echo json_encode($data);
	die();


}
////////////////////////////////////// GET ACHIEVEMENTS //////////////////////////////////
function getAchievements($adventure_id, $display=""){
	global $wpdb; $current_user = wp_get_current_user();
	if(!$display){
		$qry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id ORDER BY FIELD(achievement_display, 'badge', 'path', 'rank'), achievement_path, achievement_order, achievement_name, achievement_id");
	}else{
		$qry = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements
		WHERE adventure_id=$adventure_id AND achievement_display REGEXP '$display' ORDER BY FIELD(achievement_display, 'badge', 'path', 'rank'), achievement_order");
	}
	$result = array();
	foreach($qry as $o){
		if($o->achievement_status == 'trash'){
			$result['trash'][]=$o;
		}elseif($o->achievement_status == 'draft'){
			$result['draft'][]=$o;
		}elseif($o->achievement_status == 'publish'){
			$result['publish'][]=$o;
		}
	}
	return $result;
}
////////////////////////////////////// GET Unique Achievement Codes //////////////////////////////////
function newUniqueAchievementCode($p_id){
	global $wpdb; $player = wp_get_current_user();
	$id = $p_id ? $p_id : $_POST['achievement_id'];
	$ach = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$id AND achievement_status='publish'");
	$notification = new Notification();

	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$ach->adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	
	if($ach){
		$str = random_str(20, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
		$new_code_query = "INSERT INTO {$wpdb->prefix}br_achievement_codes 
		(`code_value`, `code_date`, `achievement_id`, `adventure_id`)
		VALUES
		(%s, %s, %d, %d)";
		
		$code_insert = $wpdb->query( $wpdb->prepare("$new_code_query ", $str, $today, $ach->achievement_id, $ach->adventure_id));
		$new_code_id = $wpdb->insert_id;
		logActivity($adventure_id,'add','unique-achievement-code',"",$ach->achievement_id, $new_code_id);
		$data['success'] = true;
        $msg_content = __('New Code Created','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'green','qr');
        $data['just_notify'] =true;
		
		$data['content_target']='#achievement-codes-table';
		
		$c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievement_codes WHERE code_id=$new_code_id");
		$data['content']='
<tr class="white-bg padding-10" id="achievement-unique-code-'.$new_code_id.'">
	<td>
		<input id="ach-code-'.$new_code_id.'" type="hidden" class="form-ui w-full" value="'.get_bloginfo('url').'/magic-link/?c='.$c->code_value.'&adv='.$ach->adventure_id.'">
			<button class="icon-button font _24 sq-40  white-bg purple-400" onClick="copyTextFrom('."'#ach-code-$c->code_id'".','."'#legend-<?= $c->code_id'".');">
				<span class="icon icon-qr font _28"></span>
			</button>
		<?php } ?>
	</td>
	<td class="relative">
		<div class="icon-group">
			<div class="icon-content">
				<span class="line font _24 grey-800">'.$c->code_value.'</span>
			</div>
		</div>
		<span class="legend border rounded-max black-bg white-color" id="legend-'.$c->code_id.'">
			<span class="font _12  padding-10 "><?php _e("Link Copied","bluerabbit"); ?></span>
		</span>
	</td>
	<td>
		<button class="form-ui purple-bg-400 white-color font main w300 _16" onClick="copyTextFrom('."'#ach-code-$c->code_id'".','."'#legend-<?= $c->code_id'".');">
			<span class="line font _14">'.__("Copy link","bluerabbit").'</span>
		</button>
		<button class="icon-button font _24 sq-40  red-bg-400 white-color" onClick="deleteAchievementCode('.$c->code_id.');">
			<span class="icon icon-trash"></span>
		</button>
	</td>
</tr>
		';
	}else{
		$data['success'] = true;
        $msg_content = __('New Code Created','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'green','qr');
        $data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
////////////////////////////////////// Add NEW Tabi //////////////////////////////////
function addTabi(){
	global $wpdb; $player = wp_get_current_user();
	$notification = new Notification();
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$adventure = getAdventure($adventure_id);
	$data = [];
	if($adventure && wp_verify_nonce($nonce, 'add_tabi_nonce')){
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date('Y-m-d H:i:s');
		$tabis = getTabis($adventure_id);
		$new_tabi_name = __("New Tabi","bluerabbit")." ".count($tabis)+1;
		$tabi_insert = "INSERT INTO {$wpdb->prefix}br_tabis (`adventure_id`, `tabi_name`) VALUES (%d, %s)";
		$tabi_insert = $wpdb->query( $wpdb->prepare("$tabi_insert ", $adventure_id, $new_tabi_name));
		$tabi_id = $wpdb->insert_id;
		logActivity($adventure_id,'add','tabi');
		$data['success'] = true;
		$data['new_tabi_id'] = $tabi_id;
        $msg_content = __('New Tabi Created','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'green','sabotage');
        $data['just_notify'] =true;
	}else{
		$data['success'] = false;
        $msg_content = __('Error','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
function insertTabiRow($p_tabi_id){
	global $wpdb; 
	$current_user = wp_get_current_user();
	$tabi_id = $p_tabi_id ? $p_tabi_id : $_POST['tabi_id'];
	if($tabi_id){
		$a = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
		$theFile = (get_template_directory()."/tabi-row.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}else{
			return false;
		}
	}else{
		return false;
	}
	die();
}

////////////////////////////////////// Delete Achievement Code //////////////////////////////////
function deleteAchievementCode($p_id){
	global $wpdb; $player = wp_get_current_user();
	$id = $p_id ? $p_id : $_POST['code_id'];
	
	$code = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievement_codes WHERE code_id=$id");
	$notification = new Notification();
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$code->adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	
	if($code->code_status=='publish'){
		$remove = "UPDATE {$wpdb->prefix}br_achievement_codes SET `code_status`=%s, `code_modified`=%s WHERE `code_id`=%d";
		$del_code = $wpdb->query( $wpdb->prepare("$remove ", 'delete', $today, $code->code_id));

		$data['success'] = true;
        $msg_content = __('Code deleted','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','delete');
        $data['just_notify'] =true;
		$data['remove_element']="#achievement-unique-code-$code->code_id";
		logActivity($adventure_id,'removed','unique-achievement-code',"",$code->achievement_id,$code->code_id);
	}else{
		if(!$code){
			$msg_content = __("Code doesn't exist",'bluerabbit');
		}else if($code->code_status =='expired'){
			$msg_content = __("Can't delete an expired code",'bluerabbit');
		}else if($code->code_status =='redeemed'){
			$msg_content = __("Can't delete. Code already redeemed.",'bluerabbit');
		}
		$data['success'] = true;
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
////////////////////////////////////// GET Unique Achievement Codes //////////////////////////////////
function getUniqueAchievementCodes($id){
	global $wpdb;
	$qry = $wpdb->get_results("SELECT codes.*, players.player_display_name, players.player_picture FROM {$wpdb->prefix}br_achievement_codes codes
	LEFT JOIN {$wpdb->prefix}br_players players ON codes.player_id = players.player_id
	WHERE codes.achievement_id=$id AND codes.code_status!='delete'
	ORDER BY codes.code_id ASC, FIELD(codes.code_status, 'publish', 'redeem', 'expired')");
	return $qry;
}
////////////////////////////////////// GET BLOCKERS //////////////////////////////////
function getBlockers($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	$qry = $wpdb->get_results("SELECT 
	blockers.*, COUNT(players.player_id) AS total_players 
	FROM {$wpdb->prefix}br_blockers blockers 
	LEFT JOIN {$wpdb->prefix}br_player_blocker players
	ON players.blocker_id = blockers.blocker_id
	WHERE blockers.adventure_id=$adventure_id
	GROUP BY blockers.blocker_id
	");
	$result = array();
	foreach($qry as $o){
		if($o->blocker_status == 'trash'){
			$result['trash'][]=$o;
		}elseif($o->blocker_status == 'draft'){
			$result['draft'][]=$o;
		}elseif($o->blocker_status == 'publish'){
			$result['publish'][]=$o;
		}
	}
	return $result;
}
////////////////////////////////////// GET MY BLOCKERS //////////////////////////////////
function getMyBlockers($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_blockers a
	JOIN {$wpdb->prefix}br_player_blocker b
	ON a.adventure_id=b.adventure_id AND b.player_id=$current_user->ID
	WHERE a.adventure_id=$adventure_id AND a.blocker_status='publish'");
	return $result;
}
////////////////////////////////////// GET TEAMS //////////////////////////////////
function getGuilds($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	$qry = $wpdb->get_results(" SELECT
		guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity, guilds.guild_id 
		
		FROM {$wpdb->prefix}br_guilds guilds
		
		LEFT JOIN {$wpdb->prefix}br_player_guild guild_players 
		ON guilds.guild_id=guild_players.guild_id
		
		WHERE guilds.adventure_id=$adventure_id 
		GROUP BY guilds.guild_id ORDER BY guilds.guild_id ASC
	
	
	");
	$result = array();
	foreach($qry as $o){
		if($o->guild_status == 'trash'){
			$result['trash'][]=$o;
		}elseif($o->guild_status == 'draft'){
			$result['draft'][]=$o;
		}elseif($o->guild_status == 'publish'){
			$result['publish'][]=$o;
		}
	}
	return $result;
}
////////////////////////////////////// GET MY TEAMS //////////////////////////////////
function getAllGuilds($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds a
	WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' GROUP BY a.guild_id");
	return $result;
}
function getMyGuilds($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = $wpdb->get_results("SELECT a.* FROM {$wpdb->prefix}br_guilds a
	JOIN {$wpdb->prefix}br_player_guild b
	ON a.guild_id=b.guild_id AND b.player_id=$current_user->ID
	WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' AND b.player_id=$current_user->ID");
	return $result;
}
function getMyGuild($adventure_id, $guild_id){ 
	global $wpdb; $current_user = wp_get_current_user();
	
	$result = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_guilds a
	JOIN {$wpdb->prefix}br_player_guild b
	ON a.guild_id=b.guild_id AND b.player_id=$current_user->ID
	WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' AND b.player_id=$current_user->ID AND a.guild_id=$guild_id");
	return $result; 
}
////////////////////////////////////// GET ITEMS //////////////////////////////////
function getItems($adventure_id){
	global $wpdb; $current_user = wp_get_current_user();
	$qry = $wpdb->get_results("SELECT items.*, steps.quest_id, steps.step_title, steps.step_id, steps.step_order, quests.quest_title FROM {$wpdb->prefix}br_items items
	LEFT JOIN {$wpdb->prefix}br_steps steps ON items.item_id = steps.step_item
	LEFT JOIN {$wpdb->prefix}br_quests quests ON quests.quest_id = steps.quest_id
	WHERE items.adventure_id=$adventure_id GROUP BY items.item_id ORDER BY items.item_type, items.item_category, items.achievement_id, items.item_level, items.item_order, items.item_id");
	$result = array();
	foreach($qry as $o){
		if($o->item_status == 'trash'){
			$result['trash'][]=$o;
		}elseif($o->item_status == 'draft'){
			$result['draft'][]=$o;
		}elseif($o->item_status == 'publish'){
			$result['publish'][]=$o;
		}
		if($o->item_type == 'key'){
			$result['key'][]=$o;
		}elseif($o->item_type == 'consumable'){
			$result['consumable'][]=$o;
		}elseif($o->item_type == 'reward'){
			$result['reward'][]=$o;
		}elseif($o->item_type == 'tabi-piece'){
			$result['tabi-piece'][]=$o;
		}
	}
	return $result;
}

////////////////////////////////////// GET ANNOUNCEMENTS //////////////////////////////////
function getAnnouncements($adventure_id,$type='public'){
	global $wpdb; $current_user = wp_get_current_user();
	
	if($type != 'public'){
		$type_str = "a.ann_type='$type'";
	}else{
		$type_str = "(a.ann_type='public' OR a.ann_type='announcement' )";
	}
	$player_adventure_status = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$current_user->ID");
	
	if($player_adventure_status->player_adventure_role == 'gm'){ $isGM = true; }
	$anns = $wpdb->get_results("SELECT a.ann_id, a.ann_content, a.ann_author, a.ann_date, a.ann_type, b.player_picture, b.player_display_name, c.player_adventure_role FROM {$wpdb->prefix}br_announcements a
	LEFT JOIN {$wpdb->prefix}br_players b
	ON a.ann_author=b.player_id
	LEFT JOIN {$wpdb->prefix}br_player_adventure c
	ON a.ann_author=c.player_id
	WHERE a.adventure_id=$adventure_id AND $type_str AND a.ann_status='publish' GROUP BY a.ann_id ORDER BY a.ann_date DESC LIMIT 100 ");
	
	$qry =  array('anns'=>$anns,'isTeacher'=>$isGM);
	
	return $qry;
}

function setGrade(){
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
		logActivity($adventure_id,'set','grade',"",$quest_id, $player_id);
	}else{
		$data['success'] = false;
        $msg_content = __("Post doesn't exist!",'bluerabbit').'<br>'.__('check again and reload','bluerabbit');
        $data['message'] = $notification->pop($msg_content,'red','cancel');
        $data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}

/////////////////////// DUPLICATE QUEST ////////////////////

function duplicateQuestProcess($quest_id, $adventure_id, $from_template=NULL ){
	global $wpdb; $current_user = wp_get_current_user();
	$quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$quest_id");
	
	if(!isset($from_template)){
		$duplication = "
			INSERT INTO {$wpdb->prefix}br_quests
	(`quest_id`, `quest_author`, `quest_order`, `adventure_id`, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`, `quest_style`, `quest_secondary_headline`, `quest_color`)

			SELECT 

	 '',`quest_author`, `quest_order`, %d, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`,  `quest_style`, `quest_secondary_headline`, `quest_color`	

			FROM  {$wpdb->prefix}br_quests WHERE `quest_id` = %d;
		";
		
		$sql = $wpdb->prepare($duplication, $adventure_id, $quest_id);
	}else{
		$duplication = "
			INSERT INTO {$wpdb->prefix}br_quests
	(`quest_id`, `quest_author`, `quest_order`, `adventure_id`, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`, `quest_style`, `quest_secondary_headline`, `quest_color`, `quest_parent`)

			SELECT 

	 '',`quest_author`, `quest_order`, %d, `achievement_id`, `quest_date_posted`, `quest_date_modified`, `quest_status`, `quest_relevance`, `quest_title`, `quest_content`, `quest_success_message`, `quest_guild`, `quest_type`, `mech_level`, `mech_xp`, `mech_bloo`, `mech_ep`, `mech_badge`, `mech_deadline`, `mech_start_date`, `mech_deadline_cost`, `mech_unlock_cost`, `mech_min_words`, `mech_min_links`, `mech_min_images`, `mech_max_attempts`, `mech_free_attempts`, `mech_attempt_cost`, `mech_questions_to_display`, `mech_answers_to_win`, `mech_time_limit`, `mech_show_answers`, `mech_item_reward`, `mech_achievement_reward`,  `quest_style`, `quest_secondary_headline`, `quest_color`, %d	

			FROM  {$wpdb->prefix}br_quests WHERE `quest_id` = %d;
		";
		$sql = $wpdb->prepare($duplication, $adventure_id, $quest_id, $quest_id);
	}
	
	
	
	$newQuest = $wpdb->query($sql);
	$new_quest_id = $wpdb->insert_id;
	if($quest->quest_type == 'challenge'){
		logActivity($adventure_id,'duplicate','challenge',"",$quest_id);
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
		logActivity($adventure_id,'duplicate','quest',"quest-steps",$quest_id);
		
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
		logActivity($adventure_id,'duplicate','survey',"",$quest_id);
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
		logActivity($adventure_id,'duplicate','mission',"",$quest_id);
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
		logActivity($adventure_id,'duplicate','quest',"",$quest_id);
	}
	$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=".$new_quest_id);
	$clone->debug = $debug;
	return $clone;
	die();
}

function duplicateQuest($p_quest_id='', $p_adventure_id=''){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$errors = array();
	$data['success']=false;
	$quest_id = $_POST['quest_id'] ? $_POST['quest_id'] : $p_quest_id ;
	$adventure_id = $_POST['adventure_id'] ? $_POST['adventure_id'] : $p_adventure_id ;
	if (wp_verify_nonce($_POST['nonce'], 'duplicate_nonce')) {
		$new_quest = duplicateQuestProcess($quest_id, $adventure_id);
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

function bulkCreate(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$errors = array();
	$data['success']=false;
	$adventure_id = $_POST['adventure_id'];
	if (wp_verify_nonce($_POST['nonce'], 'bulk_nonce')) {
		$str = random_str(20, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
		$new_quest = duplicateQuestProcess($quest_id, $adventure_id);
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

/////////////////////// DUPLICATE QUESTS ////////////////////
function duplicateQuests(){
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	$nonce  = isset($_POST['nonce']) ? $_POST['nonce'] : [] ;
	$adventure_id  = isset($_POST['adventure_target']) ? $_POST['adventure_target'] : 0;
	$duplicates = isset($_POST['duplicates']) ? $_POST['duplicates'] : [] ;
	$achievement_duplicates = isset($_POST['achievement_duplicates']) ? $_POST['achievement_duplicates'] : [];
	$item_duplicates = isset($_POST['item_duplicates']) ? $_POST['item_duplicates'] : [];
	$enc_duplicates = isset($_POST['enc_duplicates']) ? $_POST['enc_duplicates'] : [];
	$speakers_duplicates = isset($_POST['speakers_duplicates']) ? $_POST['speakers_duplicates'] : [];
	$roles = $current_user->roles;
	$data['success']=false;
	if(wp_verify_nonce($nonce, 'duplicate_nonce')){
		$total = 0;
		if(!empty($duplicates) || !empty($achievement_duplicates) || !empty($item_duplicates) || !empty($enc_duplicates)){
			$data['message'] .='<div class="boxed max-w-600 padding-20 white-color"><h1 class="font _30 w900">'.__("Duplicating","bluerabbit").'</h1> <ul class="margin-0 padding-0">';
			if(!empty($duplicates)){
				///////////////// QUESTS
				foreach($duplicates as $d){
					$clone = duplicateQuestProcess($d,$adventure_id);
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 blue-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'achievement');
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 purple-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'item');
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 pink-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'encounter');
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 teal-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'speaker');
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 teal-500'>
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
			<h1 class="font _20">'.__("Successfully duplicated")." <strong class='font _30'>$total</strong> ".__("elements").'</h1>
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










function duplicateQuestsFromTemplate($template_nonce=NULL, $adv_target=NULL, $quests=NULL, $achievements=NULL, $items=NULL, $encounters=NULL, $speakers=NULL){
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
			$data['message'] .='<div class="boxed max-w-600 padding-20 white-color"><h1 class="font _30 w900">'.__("Duplicating","bluerabbit").'</h1> <ul class="margin-0 padding-0">';
			if(!empty($duplicates)){
				///////////////// QUESTS
				foreach($duplicates as $d){
					$clone = duplicateQuestProcess($d,$adventure_id,$from_template);
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 blue-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'achievement',$from_template);
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 purple-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'item',$from_template);
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 pink-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'encounter',$from_template);
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 teal-500'>
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
					$clone = duplicateObjectProcess($d,$adventure_id,'speaker');
					if($clone){
						$total++;
						$data['message'] .= "
							<li class='font _20 block padding-5 teal-500'>
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
			<h1 class="font _20">'.__("Successfully duplicated")." <strong class='font _30'>$total</strong> ".__("elements").'</h1>
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

/////////////////////// DUPLICATE OBJECT >>> Function for cloning Actions, Achievements, Items, Blockers, Guilds, BlogPosts, Speakers and Sessions

function duplicateObjectProcess($id,$adventure_id, $type, $from_template=NULL){
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
			$ref_id = random_str(8,'1234567890abcdef');
			$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $ref_id, $id));
		}
		$clone_id=$wpdb->insert_id;
		logActivity($adventure_id,'duplicate','achievement',"",$id);
		$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=".$clone_id);
	}elseif($type=='item'){
		if(isset($from_template)){
			$clone_sql = "
				INSERT INTO {$wpdb->prefix}br_items

				(`adventure_id`, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, `item_parent`, `ref_id`)

				SELECT 
				%d, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, %d, `ref_id`
				
				FROM  {$wpdb->prefix}br_items WHERE `item_id` = %d;
			";
			$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $id));
		}else{
			$clone_sql = "
				INSERT INTO {$wpdb->prefix}br_items

				(`adventure_id`, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`, `item_parent`,`ref_id`)

				SELECT 
				%d, `item_status`, `item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `item_deadline`, `item_start_date`,%d, %s
				
				FROM  {$wpdb->prefix}br_items WHERE `item_id` = %d;
			";
			$ref_id = random_str(8,'1234567890abcdef');
			$clone_insert = $wpdb->query( $wpdb->prepare($clone_sql, $adventure_id, $id, $ref_id, $id));
		}
		$clone_id=$wpdb->insert_id;
		logActivity($adventure_id,'duplicate','item',"",$id);
		$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=".$clone_id);
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
		logActivity($adventure_id,'duplicate','encounter',"",$id);
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
			logActivity($adventure_id,'duplicate','speaker',"",$id);
		}else{
			logActivity($adventure_id,'duplicate_error','speaker',"",$id);
		}
		$clone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id=".$clone_id);
	}
	return $clone;
}

function duplicateObject($p_id='', $p_adventure_id='', $p_type=''){
	$data = array();
	global $wpdb; $current_user = wp_get_current_user();
	$nonce = $_POST['nonce'];

	$adventure_id = $_POST['adventure_id'] ? $_POST['adventure_id'] : $p_adventure_id;
	$id = $_POST['id'] ? $_POST['id'] : $p_id;
	$type = $_POST['type'] ? $_POST['type'] : $p_type;
	
	$data['success']=false;

	if(wp_verify_nonce($nonce, 'duplicate_nonce')){
		duplicateObjectProcess($id,$adventure_id, $type);
		$data['message']="$type cloned";
	}else{
		$data['message'] = "<span class='icon icon-cancel icon-xl'></span><br>";
		$data['message'] .= "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
		$data['location'] = get_bloginfo('url');
	}
	echo json_encode($data);
	die();
}


/////////////////////// DUPLICATE Achievements ////////////////////
function duplicateRow($p_id='', $p_adventure_id='', $p_type=''){
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
				logActivity($adventure_id,'duplicate','achievement',"",$id);
			}elseif($type =='session'){
				$t = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_sessions WHERE session_id=$id");
				$name = $t->session_title;
				$duplication = "INSERT INTO {$wpdb->prefix}br_sessions
				
				(`adventure_id`, `quest_id`, `speaker_id`, `achievement_id`,`session_order`, `session_title`, `session_start`, `session_end`, `session_room`, `session_description`,  `session_status`) VALUES
				(%d, %d,  %d, %d, %d, %s,  %s,  %s, %s, %s, %s)";
				$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, $t->quest_id, $t->speaker_id, $t->achievement_id, $t->session_order, '[COPY] - '.$t->session_title, $t->session_start, $t->session_end, $t->session_room, $t->session_description, $t->session_status));
				$newCloneID = $wpdb->insert_id;
				logActivity($adventure_id,'duplicate','session',"",$id);
			}elseif($type =='item'){
				$i = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_items WHERE item_id=$id");
				$name = $i->item_name;
				$duplication = "INSERT INTO {$wpdb->prefix}br_items

				(`adventure_id`, `item_status`,`item_author`, `item_name`, `item_description`, `item_secret_description`, `item_cost`, `item_type`, `item_badge`, `item_secret_badge`, `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `achievement_id`)
				VALUES (%d, %s, %d, %s, %s, %s, %d, %s,  %s,  %s, %d, %d, %d,  %s, %d, %d)";
				$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, $i->item_status, $i->item_author, '[COPY] - '.$i->item_name, $i->item_description, $i->item_secret_description, $i->item_cost, $i->item_type, $i->item_badge, $i->item_secret_badge, $i->item_stock, $i->item_player_max, $i->item_level, $i->item_category, $i->item_order, $i->achievement_id));
												   
				$newCloneID = $wpdb->insert_id;
				logActivity($adventure_id,'duplicate','item',"",$id);
			}elseif($type =='guild'){
				
				$first_str = random_str(12,'1234567890abcdefghijkls');
				$code_string = $first_str.$current_user->ID;
				$guild_code = str_shuffle($code_string);
				$t = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_guilds WHERE guild_id=$id");
				$name = $t->guild_name;
				$duplication = "INSERT INTO {$wpdb->prefix}br_guilds
				(`adventure_id`, `guild_name`, `guild_logo`, `guild_color`, `guild_status`, `guild_xp`, `guild_bloo`, `assign_on_login`, `guild_code`,`guild_group`)
				VALUES ( %d, %s, %s, %s, %s, %d, %d, %d, %s, %s )";
				$duplication_insert = $wpdb->query( $wpdb->prepare($duplication, $adventure_id, '[COPY] - '.$t->guild_name, $t->guild_logo, $t->guild_color, $t->guild_status, 0, 0, $t->assign_on_login, $guild_code, $t->guild_group));
												   
				$newCloneID = $wpdb->insert_id;
				logActivity($adventure_id,'duplicate','guild',"",$id);
			}elseif($type == 'quest'){
				$new_quest = duplicateQuestProcess($id,$adventure_id);
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

/////////////////////// UPDATE STATUS A.K.A. Trash, Delete and Restore ////////////////////
function br_trash(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$adventure_id = $_POST['adventure_id'];
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d g:h:s');
	$nonce = $_POST['nonce'];
	$reload = $_POST['reload'];
	if(wp_verify_nonce($nonce, 'trash_nonce')){
		$status = 'trash';
		$data['message'] = "<span class='icon icon-trash icon-xl'></span><h1>".__("Sent to trash!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}elseif(wp_verify_nonce($nonce, 'delete_nonce')){
		$status = 'delete';
		$data['message'] = "<span class='icon icon-cancel icon-xl'></span><h1>".__("Deleted!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}elseif(wp_verify_nonce($nonce, 'hidden_nonce')){
		$status = 'hidden';
		$data['message'] = "<span class='icon icon-warning icon-xl'></span><h1>".__("Published as Hidden!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}elseif(wp_verify_nonce($nonce, 'publish_nonce')){
		$status = 'publish';
		$data['message'] = "<span class='icon icon-restore icon-xl'></span><h1>".__("Restored Success!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}elseif(wp_verify_nonce($nonce, 'draft_nonce')){
		$status = 'draft';
		$data['message'] = "<span class='icon icon-duplicate icon-xl'></span><h1>".__("Saved as draft!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}else{
		$status = NULL;
		$data['message'] .= "<h1>".__("Unauthorized access","bluerabbit")."</h1>".'<h4>'.__('check again and reload','bluerabbit').'</h4>';
		$data['location'] = get_bloginfo("url")."/adventure/?adventure_id=$adventure_id";
	}
	if($status){
		if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'social' || $type == 'survey' || $type == 'blog-post' || $type == 'lore'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_status=%s, quest_date_modified=%s WHERE quest_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql, $status, $today, $id,$adventure_id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_status=%s, achievement_modified=%s WHERE achievement_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status, $today, $id,$adventure_id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_status=%s, enc_modified=%s WHERE enc_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status, $today, $id,$adventure_id);
		}elseif($type == 'attempt'){
			$sql = "UPDATE {$wpdb->prefix}br_challenge_attempts SET attempt_status=%s WHERE attempt_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'blocker'){
			$sql = "UPDATE {$wpdb->prefix}br_blockers SET blocker_status=%s WHERE blocker_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'trnx'){
			$sql = "UPDATE {$wpdb->prefix}br_transactions SET trnx_status=%s, trnx_modified=%s WHERE trnx_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$today,$id,$adventure_id);
		}elseif($type == 'player_post'){
			if($status=='delete'){
				$sql = "DELETE FROM {$wpdb->prefix}br_player_posts WHERE quest_id=%d AND adventure_id=%d AND player_id=%d";
				$sql = $wpdb->prepare ($sql,$id,$adventure_id,$current_user->ID);
			}else{
				$sql = "UPDATE {$wpdb->prefix}br_player_posts SET pp_status=%s, pp_modified=%s WHERE quest_id=%d AND adventure_id=%d AND player_id=%d";
				$sql = $wpdb->prepare ($sql,$status, $today,$id,$adventure_id,$current_user->ID);
			}
		}elseif($type == 'survey-answer'){
			if($status=='delete'){
				$sql = "DELETE FROM {$wpdb->prefix}br_survey_answers WHERE survey_id=%d AND player_id=%d";
				$sql = $wpdb->prepare ($sql,$id,$current_user->ID);
			}
		}elseif($type == 'guild'){
			$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_status=%s WHERE guild_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'library'){
			$sql = "UPDATE {$wpdb->prefix}br_libraries SET lib_status=%s WHERE lib_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_status=%s, item_post_modified=%s WHERE item_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status, $today, $id, $adventure_id);
		}elseif($type == 'sponsor'){
			$sql = "UPDATE {$wpdb->prefix}br_sponsors SET sponsor_status=%s WHERE sponsor_id=%d";
			$sql = $wpdb->prepare ($sql,$status, $id);
			
		}elseif($type == 'adventure'){
			
			$roles = $current_user->roles;
			if($roles[0] =='administrator'){
				$the_adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a WHERE a.adventure_id=$id");
			}else{
				$the_adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a WHERE a.adventure_id=$id AND a.adventure_owner=$current_user->ID");
			}
			if($the_adventure){
				$sql = "UPDATE {$wpdb->prefix}br_adventures SET adventure_status=%s, adventure_date_modified=%s WHERE adventure_id=%d";
				$sql = $wpdb->prepare ($sql,$status, $today,$id);
			}else{
				$data['success'] = false;
				$data['message'] = "<span class='icon icon-cancel icon-xl'></span><h1>".__("Unauthorized to update the adventure!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
				$data['location'] = get_bloginfo('url');
				echo json_encode($data);
				die();
			}
			
		}elseif($type == 'announcement'){
			$sql = "UPDATE {$wpdb->prefix}br_announcements SET ann_status=%s WHERE ann_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'speaker'){
			$sql = "UPDATE {$wpdb->prefix}br_speakers SET speaker_status=%s WHERE speaker_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET session_status=%s WHERE session_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}elseif($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_status=%s WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
		}
		
		$wpdb->query($sql);
		logActivity($adventure_id, $status,$type,"",$id);
		
		$data['success'] = true;
		resetPlayer($adventure_id,  $current_user->ID);
		if($reload){
			$data['location']='reload';
		}else{
			$data['location'] = get_bloginfo("url")."/adventure/?adventure_id=$adventure_id";
		}
	}
	echo json_encode($data);
	die();
}
/////////////////////// UPDATE STATUS A.K.A. Trash, Delete and Restore ////////////////////
function br_empty_trash(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$nonce = $_POST['nonce'];
	$adventure_id = $_POST['adventure_id'];
	$adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d g:h:s');
	if(wp_verify_nonce($nonce, 'empty_trash_nonce'.$current_user->ID)){
		if($type == 'quest'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_status=%s, quest_date_modified=%s WHERE quest_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql, 'delete', $today, $adventure_id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_status=%s, achievement_modified=%s WHERE achievement_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete', $today,$adventure_id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_status=%s, enc_modified=%s WHERE enc_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete', $today, $adventure_id);
		}elseif($type == 'blocker'){
			$sql = "UPDATE {$wpdb->prefix}br_blockers SET blocker_status=%s WHERE blocker_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete', $adventure_id);
		}elseif($type == 'guild'){
			$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_status=%s WHERE guild_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete',$adventure_id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_status=%s, item_post_modified=%s WHERE item_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete', $today, $adventure_id);
		}elseif($type == 'speaker'){
			$sql = "UPDATE {$wpdb->prefix}br_speakers SET speaker_status=%s WHERE speaker_status='trash' AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete',$adventure_id);
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET session_status=%s WHERE session_status='trash'  AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,'delete',$adventure_id);
		}
		
		$wpdb->query($sql);
		$data['debug']=print_r($wpdb->last_query,true);
		
		logActivity($adventure_id, 'empty-trash', $type,"");
		$data['success'] = true;
		$data['reload'] = 'reload';
		$data['message'] = "<span class='icon icon-trash icon-xl'></span><h1>".__("All trashed items have been deleted!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}else{
		$data['message'] .= "<h1>".__("Unauthorized access","bluerabbit")."</h1>".'<h4>'.__('check again and reload','bluerabbit').'</h4>';
		$data['location'] = get_bloginfo("url")."/adventure/?adventure_id=$adventure_id";
		$data['debug']='NONCE '.$nonce;
	}
	echo json_encode($data);
	die();
}
/////////////////////// Reset Transactions ////////////////////
function resetTransactions(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$adventure_id = $_POST['adventure_id'];
	$player_id = $_POST['player_id'];
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce, 'reset_transactions_nonce')){
		$sql = "DELETE FROM  {$wpdb->prefix}br_transactions WHERE trnx_author=%d AND trnx_type=%d AND adventure_id=%d AND trnx_author=%d";
		$sql = $wpdb->prepare ($sql,$current_user->ID,'consumable',$adventure_id, $player_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		$player_id = $current_user->ID;
		resetPlayer($adventure_id,$player_id);
		logActivity($adventure_id, "reset","transactions","",$player_id);
		$data['message'] = "<h1>".__("All transactions deleted successfully!","bluerabbit")."</h1>";
		$data['location']= get_bloginfo("url")."/backpack/?adventure_id=$adventure_id";
	}

	echo json_encode($data);
	die();
}



//////////////////////////
function exportPlayersWork() {
    $data = json_decode(stripslashes($_POST['data']), true);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="players_export.csv"');

    $output = fopen('php://output', 'w');

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

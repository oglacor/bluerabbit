<?php
/////////////////////// SET XP ////////////////////
function setXP(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$xp = $_POST['xp'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'xp_nonce')){
		if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'social' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_xp=%d WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id, $id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_xp=%d WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id, $id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_xp=%d WHERE enc_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id);
		}
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","xp","$type",$id);
		$notification = new Notification();
		$msg_content = __('XP updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'blue','star');
		$data['just_notify'] =true;
		$data['new_xp_nonce'] = wp_create_nonce('xp_nonce');
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET EP ////////////////////
function setEP(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$ep = $_POST['ep'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'ep_nonce')){
		if($type == 'quest' ||$type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_ep=%d WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id,$id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_ep=%d WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id,$id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_ep=%d WHERE enc_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id);
		}
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","ep","$type",$id);
		$notification = new Notification();
		$msg_content = __('EP updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'teal','activity');
		$data['just_notify'] =true;
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET BLOO ////////////////////
function setBLOO(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$bloo = $_POST['bloo'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'bloo_nonce')){
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_bloo=%d WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id, $id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_cost=%d WHERE (item_id=%d AND adventure_id=%d AND (item_type='consumable' OR item_type='key' OR item_type='tabi-piece')) OR item_parent=%d";
			$sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id,$id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_bloo=%d WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id,$id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_bloo=%d WHERE enc_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","bloo","$type",$id);
		$notification = new Notification();
		$msg_content = __('BLOO updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'light-green','bloo');
		$data['just_notify'] =true;
		$data['new_bloo_nonce'] = wp_create_nonce('bloo_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET BLOO ////////////////////
function setMaxPlayers(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$max = $_POST['max'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'max_players_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_max=%d WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
		$sql = $wpdb->prepare ($sql,$max,$id,$adventure_id,$id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","max_players","achievement",$id);
		$notification = new Notification();
		$msg_content = __('Max Players updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'light-green','player');
		$data['just_notify'] =true;
		$data['new_max_players_nonce'] = wp_create_nonce('max_players_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// updateAdventureTitle ////////////////////
function updateAdventureTitle(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$adv_title = stripslashes_deep($_POST['adv_title']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'br_update_adv_title_nonce'.$adventure_id)){
		$sql = "UPDATE {$wpdb->prefix}br_adventures SET adventure_title=%s WHERE adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$adv_title,$adventure_id);
		$wpdb->query($sql);
		$data['success'] = true;
		logActivity($adventure_id, "update","title","adventure");
		$msg_content = __('Adventure title updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','check');
		$data['just_notify'] =true;
	}else{
		$msg_content = __("Nonce!","bluerabbit");
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
function setTitle(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$title = stripslashes_deep($_POST['title']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'title_nonce')){
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey' || $type == 'blog-post' || $type == 'lore'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_title=%s WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id,$id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_name=%s WHERE (item_id=%d AND adventure_id=%d) OR item_parent=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id,$id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_name=%s WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id,$id);
		}elseif($type == 'guild'){
			$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_name=%s WHERE guild_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_question=%s WHERE enc_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET session_title=%s WHERE session_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
		}elseif($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_name=%s WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
		}
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","title","$type",$id);
		$notification = new Notification();
		$msg_content = __('Title updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','check');
		$data['just_notify'] =true;
		$data['new_title_nonce'] = wp_create_nonce('title_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET TITLE ////////////////////
function setBadge(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$badge = $_POST['badge'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'title_nonce')){
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_badge=%s WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id, $id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_badge=%s WHERE (item_id=%d AND adventure_id=%d) OR item_parent=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id, $id);
		}elseif($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_background=%s WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_badge=%s WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id, $id);
		}elseif($type == 'guild'){
			$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_logo=%s WHERE guild_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
		}elseif($type == 'speaker'){
			$sql = "UPDATE {$wpdb->prefix}br_speakers SET speaker_picture=%s WHERE speaker_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
		}
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","badge","$type",$id);
		$notification = new Notification();
		$msg_content = __('Badge updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'purple','check');
		$data['just_notify'] =true;
		$data['new_title_nonce'] = wp_create_nonce('title_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET TITLE ////////////////////
function setColor(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$color = $_POST['color'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'title_nonce')){
		if($type == 'quest'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_color=%s WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id, $id);
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_color=%s WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
			$sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id, $id);
		}elseif($type == 'guild'){
			$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_color=%s WHERE guild_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
		}elseif($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_color=%s WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
		}
		$wpdb->query($sql); 
		
		$data['success'] = true;
		logActivity($adventure_id, "set","color","$type",$id);
		$notification = new Notification();
		$msg_content = __('Color updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'deep-purple','check');
		$data['just_notify'] =true;
		$data['new_title_nonce'] = wp_create_nonce('title_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET SPEAKER DATA  ////////////////////
function setSpeakerData(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$speaker = $_POST['speaker_data'];
	$id = $speaker['id'];
	$adventure_id = $speaker['adventure_id'];
	$first_name = $speaker['first_name'];
	$last_name = $speaker['last_name'];
	$company = $speaker['company'];
	$website = $speaker['website'];
	$twitter = $speaker['twitter'];
	$linkedin = $speaker['linkedin'];
	$data['debug'].=print_r($speaker,true);
	
	$nonce = $_POST['nonce'];
	$notification = new Notification();
	if(wp_verify_nonce($nonce, 'speaker_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_speakers SET 
		`speaker_first_name`=%s, `speaker_last_name`=%s,  `speaker_company`=%s, `speaker_website`=%s, `speaker_linkedin`=%s, `speaker_twitter`=%s
		WHERE speaker_id=%d AND adventure_id=%d";
		$sql = $wpdb->prepare ($sql, $first_name, $last_name, $company, $website, $linkedin, $twitter, $id, $adventure_id);
		$wpdb->query($sql); $data['debug'].=print_r($wpdb->last_query,true);
		
		$data['success'] = true;
		logActivity($adventure_id, "update","speaker","",$id);
		$msg_content = __('Speaker updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','check');
		$data['just_notify'] =true;
	}else{
		$msg_content = __('Nonce not found','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','cancel');
		$data['just_notify'] =true;
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET Item Category ////////////////////
function setCategory(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$id = $_POST['id'];
	$category = stripslashes_deep($_POST['category']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'item_cat_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_items SET item_category=%s WHERE (item_id=%d AND adventure_id=%d AND item_type='consumable') OR item_parent=%d";
		$sql = $wpdb->prepare ($sql,$category,$id,$adventure_id, $id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","item-category","",$id);
		$notification = new Notification();
		$msg_content = __('Item Category updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'pink','list');
		$data['just_notify'] =true;
		$data['new_title_nonce'] = wp_create_nonce('title_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET TEAM GROUP ////////////////////
function setGuildGroup(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$id = $_POST['id'];
	$guild_group = stripslashes_deep($_POST['guild_group']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce, 'guild_group_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_group=%s WHERE guild_id=%d AND adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$guild_group,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","guild-group","",$id);
		$notification = new Notification();
		$msg_content = __('Guild Group updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'light-green','guild');
		$data['just_notify'] =true;
		$data['new_title_nonce'] = wp_create_nonce('title_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET GUILD CAPACITY ////////////////////
function setGuildCapacity(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$id = $_POST['id'];
	$guild_capacity = stripslashes_deep($_POST['guild_capacity']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce, 'guild_capacity_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_capacity=%d WHERE guild_id=%d AND adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$guild_capacity,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","guild-capacity","",$id);
		$notification = new Notification();
		$msg_content = __('Guild Capacity updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'orange','guild');
		$data['just_notify'] =true;
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET Level ////////////////////
function setLevel(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$level = $_POST['level'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'level_nonce')){
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_level=%d WHERE (quest_id=%d AND adventure_id=%d) OR quest_parent=%d";
			$sql = $wpdb->prepare ($sql,$level,$id,$adventure_id,$id);
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_level=%d WHERE enc_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_level=%d WHERE (item_id=%d AND adventure_id=%d) OR item_parent=%d";
			$sql = $wpdb->prepare ($sql,$level,$id,$adventure_id,$id);
		}elseif($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_level=%d WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
		}
		$wpdb->query($sql);
		$data['success'] = true;
		logActivity($adventure_id, "set","level","$type",$id);
	
		$notification = new Notification();
		$msg_content = __('Level updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'deep-purple','level');
		$data['just_notify'] =true;
		$data['new_level_nonce'] = wp_create_nonce('level_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET DIMENSIONS ////////////////////
function setDimensions(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$width = $_POST['width'];
	$height = $_POST['height'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce, 'dimensions_nonce')){
		if($type == 'tabi'){
			$sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_width=%d, tabi_height=%d WHERE tabi_id=%d AND adventure_id=%d";
			$sql = $wpdb->prepare ($sql,$width,$height,$id,$adventure_id);
		}
		$wpdb->query($sql);
		$data['success'] = true;
		logActivity($adventure_id, "set","dimensions","$type",$id);
	
		$notification = new Notification();
		$msg_content = __('Dimensions updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'deep-purple','stats');
		$data['just_notify'] =true;
		$data['new_dimensions_nonce'] = wp_create_nonce('dimensions_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
////////////// setAchievement /////////////
function setAchievement(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$achievement_id = $_POST['achievement_id'];
	$nonce = $_POST['nonce'];
	$id = $_POST['id'];
	$adventure_id = $_POST['adventure_id'];
	$type = $_POST['type'];
	if(wp_verify_nonce($nonce, 'achievement_nonce')){
		if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'survey'|| $type == 'blog-post'|| $type == 'lore' || $type == 'social'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET achievement_id=%d WHERE quest_id=%d AND adventure_id=%d";
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_path=%d WHERE achievement_id=%d AND adventure_id=%d AND achievement_display='badge'";
		}elseif($type == 'encounter'){
			$sql = "UPDATE {$wpdb->prefix}br_encounters SET achievement_id=%d WHERE enc_id=%d AND adventure_id=%d";
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET achievement_id=%d WHERE item_id=%d AND adventure_id=%d";
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET achievement_id=%d WHERE session_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$achievement_id,$id,$adventure_id);
		$the_query = $wpdb->query($sql);
		logActivity($adventure_id, "set","achievement","$type",$id);
		$notification = new Notification();
		if($the_query=== FALSE){
			$data['success'] = false;
			$msg_content = __("Can't assign that achievement",'bluerabbit');
			$data['message'] = $notification->pop($msg_content,'red','cancel');
			$data['just_notify'] =true;
		}else{
			$data['success'] = true;
			$msg_content = __('Achievement updated','bluerabbit');
			$data['message'] = $notification->pop($msg_content,'purple','achievement');
			$data['just_notify'] =true;
			$data['new_achievement_nonce'] = wp_create_nonce('achievement_nonce');
		}
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
////////////// setGuild /////////////
function setGuild(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$guild_id = $_POST['guild_id'];
	$nonce = $_POST['nonce'];
	$id = $_POST['id'];
	$adventure_id = $_POST['adventure_id'];
	$type = $_POST['type'];
	if(wp_verify_nonce($nonce, 'guild_nonce')){
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET guild_id=%d  WHERE quest_id=%d AND adventure_id=%d";
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET guild_id=%d WHERE item_id=%d AND adventure_id=%d AND item_type='consumable'";
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET guild_id=%d WHERE session_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$guild_id,$id,$adventure_id);
		$wpdb->query($sql);
		$data['success'] = true;
		logActivity($adventure_id, "set","guild","$type",$id);
		$notification = new Notification();
		$msg_content = __('Guild updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','guild');
		$data['just_notify'] =true;
		$data['guild_nonce'] = wp_create_nonce('guild_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
////////////// setSpeaker /////////////
function setSpeaker(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	$data['success'] = false;
	$id = $_POST['id'];
	$speaker = $_POST['speaker'];
	$nonce = $_POST['nonce'];
	$adventure_id = $_POST['adventure_id'];
	if(wp_verify_nonce($nonce, 'speaker_nonce')){
		$sql = "UPDATE {$wpdb->prefix}br_sessions SET speaker_id=%d WHERE session_id=%d AND adventure_id=%d";
		$sql = $wpdb->prepare ($sql,$speaker,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","speaker","",$id);
		$notification = new Notification();
		$msg_content = __('speaker updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'green','megaphone');
		$data['just_notify'] =true;
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}

/////////////////////// SET DISPLAY STYLE ////////////////////
function setDisplayStyle(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$style = $_POST['style'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	
	if(wp_verify_nonce($nonce, 'display_style_nonce')){
		if($type == 'quest' || $type == 'blog-post' ||$type == 'challenge' ||$type == 'mission' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_style=%s WHERE quest_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$style,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","display-style","$type",$id);
		$notification = new Notification();
		$msg_content = __('Display Style updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'teal','calendar');
		$data['just_notify'] =true;
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET START DATE ////////////////////
function setStartDate(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$start_date = $_POST['start_date'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	
	if(wp_verify_nonce($nonce, 'start_date_nonce')){
		if($start_date){
			$start_date=date('Y-m-d H:i:s',strtotime($start_date));
		}else{
			$start_date='0000-00-00 00:00:00';
		}
		if($type == 'quest' || $type == 'blog-post' ||$type == 'challenge' ||$type == 'mission' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_start_date=%s WHERE quest_id=%d AND adventure_id=%d";
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET session_start=%s WHERE session_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$start_date,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","start-date","$type",$id);
		$notification = new Notification();
		$msg_content = __('Start date updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'cyan','calendar');
		$data['just_notify'] =true;
		$data['new_start_date_nonce'] = wp_create_nonce('start_date_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET DEADLINE ////////////////////
function setDeadline(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$type = $_POST['type'];
	$id = $_POST['id'];
	$deadline = $_POST['deadline'];
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'deadline_nonce')){
		
		if($deadline){
			$deadline=date('Y-m-d H:i:s',strtotime($deadline));
		}else{
			$deadline='0000-00-00 00:00:00';
		}
		
		if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'survey'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET mech_deadline=%s WHERE quest_id=%d AND adventure_id=%d";
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_deadline=%s WHERE achievement_id=%d AND adventure_id=%d";
		}elseif($type == 'session'){
			$sql = "UPDATE {$wpdb->prefix}br_sessions SET session_end=%s WHERE session_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$deadline,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","deadline","$type",$id);
		$notification = new Notification();
		$msg_content = __('Deadline updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','deadline');
		$data['just_notify'] =true;
		$data['new_deadline_nonce'] = wp_create_nonce('deadline_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
/////////////////////// SET Magic Code ////////////////////
function setMagicCode(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$id = $_POST['id'];
	$code = strtolower($_POST['code']);
	$adventure_id = $_POST['adventure_id'];
	$nonce = $_POST['nonce'];
	$reload = 'reload';
	if(wp_verify_nonce($nonce, 'magic_code_nonce')){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_code=%s WHERE (achievement_id=%d AND adventure_id=%d) OR achievement_parent=%d";
		$sql = $wpdb->prepare ($sql,$code,$id,$adventure_id, $id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "set","magic-code","",$id);
		$notification = new Notification();
		$msg_content = __('Magic Code updated','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'purple','magic');
		$data['just_notify'] =true;
		$data['new_magic_code_nonce'] = wp_create_nonce('magic_code_nonce');
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}
function breakParent(){
	global $wpdb; $current_user = wp_get_current_user();
	$data = array();
	
	$data['success'] = false;
	$id = $_POST['id'];
	$adventure_id = $_POST['adventure_id'];
	$type = $_POST['type'];
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce, 'break_parent_nonce')){
		
		if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'survey'|| $type == 'blog-post'|| $type == 'lore' || $type == 'social'){
			$sql = "UPDATE {$wpdb->prefix}br_quests SET quest_parent=NULL WHERE quest_id=%d AND adventure_id=%d";
		}elseif($type == 'achievement'){
			$sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_parent=NULL WHERE achievement_id=%d AND adventure_id=%d";
		}elseif($type == 'item'){
			$sql = "UPDATE {$wpdb->prefix}br_items SET item_parent=NULL WHERE item_id=%d AND adventure_id=%d";
		}
		$sql = $wpdb->prepare ($sql,$id,$adventure_id);
		$wpdb->query($sql);
		
		$data['success'] = true;
		logActivity($adventure_id, "break-parent","","",$id);
		$notification = new Notification();
		$msg_content = __('No longer attached','bluerabbit');
		$data['message'] = $notification->pop($msg_content,'red','trash');
		$data['just_notify'] =true;
	}else{
		$data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
	}
	echo json_encode($data);
	die();
}


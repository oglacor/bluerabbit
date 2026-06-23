<?php
class BR_Session {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // Source: functions/ajax.php — updateSponsor
    public function updateSponsor(){
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
                BR_Activity::instance()->logActivity($adventure_id,'update','sponsor','',$sponsor_id);
            }else{
                BR_Activity::instance()->logActivity($adventure_id,'add','sponsor','',$sponsor_id);
            }
            $data['just_notify'] =true;

        }else{
            $data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
            $data['location'] = get_bloginfo('url');
        }
        echo json_encode($data);
        die();

    }

    // Source: functions/ajax.php — updateSpeaker
    public function updateSpeaker(){
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
            if($speaker_player_id > 0){
                $player_data = BR_Player::instance()->getPlayerData($speaker_player_id);
                if($player_data){
                    $speaker_first_name = $player_data->player_first ? $player_data->player_first : $speaker_first_name;
                    $speaker_last_name = $player_data->player_last ? $player_data->player_last : $speaker_last_name;
                    $speaker_bio = $player_data->player_bio ? $player_data->player_bio : $speaker_bio;
                    $speaker_picture = $player_data->player_picture ? $player_data->player_picture : $speaker_picture;
                    $speaker_company = $player_data->player_company ? $player_data->player_company : $speaker_company;
                    $speaker_website = $player_data->player_website ? $player_data->player_website : $speaker_website;
                    $speaker_linkedin = $player_data->player_linkedin ? $player_data->player_linkedin : $speaker_linkedin;
                }
            }
            if(!$speaker_first_name){
                $errors[] = __("Speaker name is required","bluerabbit");
            }
            if(!$errors){
                $sql = "INSERT INTO {$wpdb->prefix}br_speakers
                (`speaker_id`, `player_id`, `adventure_id`, `speaker_first_name`, `speaker_last_name`, `speaker_bio`, `speaker_picture`, `speaker_company`, `speaker_website`, `speaker_linkedin`)
                VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                `player_id`=%d, `adventure_id`=%d, `speaker_first_name`=%s, `speaker_last_name`=%s, `speaker_bio`=%s, `speaker_picture`=%s, `speaker_company`=%s, `speaker_website`=%s, `speaker_linkedin`=%s";
                $sql = $wpdb->prepare($sql,$speaker_id, $speaker_player_id, $adventure_id, $speaker_first_name, $speaker_last_name, $speaker_bio, $speaker_picture, $speaker_company, $speaker_website,  $speaker_linkedin,  $speaker_player_id, $adventure_id, $speaker_first_name, $speaker_last_name, $speaker_bio, $speaker_picture, $speaker_company, $speaker_website, $speaker_linkedin);
                $sql = $wpdb->query($sql);
                $updated_id = $wpdb->insert_id;

                if($sql !== FALSE ){
                    $data['success']=true;
                    if(!$speaker_id){
                        $data['location']=get_bloginfo('url')."/new-speaker/?adventure_id=$adventure_id&speaker_id=$updated_id";
                        BR_Activity::instance()->logActivity($adventure_id,'add','speaker','',$updated_id);
                    }else{
                        BR_Activity::instance()->logActivity($adventure_id,'update','speaker','',$speaker_id);
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

    // Source: functions/ajax.php — uploadBulkSpeakers
    public function uploadBulkSpeakers(){
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

    // Source: functions/ajax.php — uploadBulkSessions
    public function uploadBulkSessions(){
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

    // Source: functions/ajax.php — updateSession
    public function updateSession(){
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
            $speaker_ids = implode(",",$session_data['speaker_ids']);
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
                (`session_id`, `adventure_id`, `quest_id`, `speaker_ids`,  `session_title`, `session_start`, `session_end`,`session_status`, `session_description`, `session_room`, `achievement_id`, `guild_id`)
                VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %d, %d)
                ON DUPLICATE KEY UPDATE
                `adventure_id`=%d, `quest_id`=%d, `speaker_ids`=%s, `session_title`=%s, `session_start`=%s, `session_end`=%s, `session_status`=%s, `session_description`=%s , `session_room`=%s, `achievement_id`=%d , `guild_id`=%d ";
                $sql = $wpdb->prepare($sql,$session_id, $adventure_id, $quest_id, $speaker_ids, $session_title, $session_start, $session_end, $session_status, $session_description, $session_room,   $achievement_id,  $guild_id,  $adventure_id, $quest_id, $speaker_ids, $session_title, $session_start, $session_end, $session_status, $session_description , $session_room , $achievement_id , $guild_id );
                $sql = $wpdb->query($sql);
                $data['debug'] = print_r($wpdb->last_query,true);
                $updated_session_id = $wpdb->insert_id;
                if($updated_session_id ){
                    $data['success']=true;
                    if(!$session_id){
                        $data['location']=get_bloginfo('url')."/new-session/?adventure_id=$adventure_id&session_id=$updated_session_id";
                        BR_Activity::instance()->logActivity($adventure_id,'add','session','',$updated_session_id);
                    }else{
                        BR_Activity::instance()->logActivity($adventure_id,'update','session','',$session_id);
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

    // Source: functions/ajax.php — getSpeakers
    public function getSpeakers($adventure_id){
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

    // Source: functions/ajax.php — getSessions
    public function getSessions($adventure_id, $p_status=''){
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
        sessions.*,	quests.quest_title, quests.quest_type
            FROM {$wpdb->prefix}br_sessions sessions
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

    // Source: functions/ajax.php — getSpeakerSessions
    public function getSpeakerSessions($adventure_id, $speaker_id){
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

    // Source: functions/ajax.php — getSponsors
    public function getSponsors($adventure_id=NULL){
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

    // Source: functions/adventure-management.php — setSpeakerData
    public function setSpeakerData(){
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
            BR_Activity::instance()->logActivity($adventure_id, "update","speaker","",$id);
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

    // Source: functions/adventure-management.php — setSpeaker
    public function setSpeaker(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","speaker","",$id);
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
}

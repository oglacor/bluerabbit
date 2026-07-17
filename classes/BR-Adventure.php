<?php
class BR_Adventure {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // From functions/ajax.php
    public function updateAdventure(){
        global $wpdb;
        $current_user = wp_get_current_user();

        $user_plan = BR_Config::instance()->getUserPlan($current_user->ID);
        $f_role = $user_plan ? $user_plan['plan_key'] : 'basic';
        $features = BR_Config::instance()->getFeatures($f_role);
        $playerData = BR_Player::instance()->getPlayerData($current_user->ID);
        $config = BR_Config::instance()->getSysConfig();
        $myAdventures = $wpdb->get_col("SELECT adventure_id FROM {$wpdb->prefix}br_adventures WHERE adventure_owner=$current_user->ID");
        $max_adv_limit = isset($features['max_adventures'][$f_role]) ? intval($features['max_adventures'][$f_role]) : 0;
        if($max_adv_limit > 0 && count($myAdventures) >= $max_adv_limit){
            $add_adventure = false;
        }else{
            $add_adventure = true;
        }

        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_adventure_nonce')) {


            $adventure_data = $_POST['adventure_data'];
            $adventure_id = intval($adventure_data['adventure_id']);
            $old_adventure = $adventure_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d", $adventure_id)) : null;
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
                $adventure_grade_scale = $adventure_data['adventure_grade_scale'] ?? 'none';
                $adventure_type = $adventure_data['adventure_type'] ?? 'normal';
                $adventure_progression_type = $adventure_data['adventure_progression_type'] ?? 'before';
                $adventure_privacy = $adventure_data['adventure_privacy'] ?? '';
                $adventure_status = $adventure_data['adventure_status'] ?? 'publish';
                $adventure_instructions = stripslashes_deep($adventure_data['adventure_instructions'] ?? '');
                $adventure_nickname = $adventure_data['adventure_nickname'] ?? '';
                $adventure_level_up_array = isset($adventure_data['adventure_level_up_array']) ? serialize($adventure_data['adventure_level_up_array']) : '';
                $adventure_color = $adventure_data['adventure_color'] ?? '';
                $adventure_hide_schedule = $adventure_data['adventure_hide_schedule'] ?? 'no';
                $adventure_hide_quests = $adventure_data['adventure_hide_quests'] ?? '';
                $adventure_has_guilds = $adventure_data['adventure_has_guilds'] ?? 0;
                $unenrolled = $adventure_data['unenrolled'] ?? [];
                $adventure_ranks = $adventure_data['adventure_ranks'] ?? [];
                $adventure_settings = $adventure_data['adventure_settings'] ?? [];

                if ($adventure_gmt && $adventure_gmt !== '0') { date_default_timezone_set($adventure_gmt); }
                $today = date('Y-m-d h:i:s');
                $adventure_date_modified = date("Y-m-d H:i:s");
                $adventure_start_date = !empty($adventure_data['adventure_start_date']) ? date('Y-m-d H:i:s', strtotime($adventure_data['adventure_start_date'])) : null;
                $adventure_end_date = !empty($adventure_data['adventure_end_date']) ? date('Y-m-d H:i:s', strtotime($adventure_data['adventure_end_date'])) : null;

                if(!$adventure_title){
                    $errors[] = __("The adventure name can't be empty","bluerabbit");
                }
                if($adventure_progression_type == 'after' && $adventure_grade_scale == 'none'){
                    $errors[] = __("You can't assign rewards after grading if no grading scale is set","bluerabbit");
                }
                if(!$old_adventure || !$old_adventure->adventure_code){
                    $first_str = BR_Utils::instance()->random_str(12,'1234567890abcdef');
                    $code_string = $first_str.$current_user->ID;
                    $adventure_code = str_shuffle($code_string);
                }else{
                    $adventure_code = $old_adventure->adventure_code;
                }
                if(!$old_adventure || !$old_adventure->adventure_topic_id){
                    $notification_topic = BR_Utils::instance()->random_str(12,'1234567890abcdef');
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
                            $ranksDELETE = "DELETE FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=%d";
                            $delete =$wpdb->query( $wpdb->prepare($ranksDELETE, $adventure_id));
                            if($adventure_ranks){
                                $ranks_ph = array();
                                $ranks_values = array();
                                $ranksSQL = "INSERT INTO {$wpdb->prefix}br_adventure_ranks (`adventure_id`, `rank_level`, `achievement_id`, `condition_type`)  VALUES";
                                foreach($adventure_ranks as $r){
                                    $message = stripslashes_deep($r['message']);
                                    $condition_type = array_key_exists($r['condition_type'] ?? '', BR_Conditions::CONDITION_TYPES) ? $r['condition_type'] : 'level';
                                    array_push($ranks_values, $adventure_id, $r['level'], $r['achievement'], $condition_type);
                                    $ranks_ph[] = "(%d, %d, %d, %s)";
                                }
                                $ranksSQL .= implode(', ', $ranks_ph);
                                $ranks_insert =$wpdb->query( $wpdb->prepare("$ranksSQL ", $ranks_values));
                            }
                            $data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Updated!","bluerabbit").'</strong></h4>';
                        }else{
                            $adventure_id = $wpdb->insert_id;
                            $data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Created!","bluerabbit").'</strong></h4>';
                        }

                        $sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_role) VALUES (%d,%d,%s)
                        ON DUPLICATE KEY UPDATE player_adventure_role=%s, player_adventure_status='%s'";
                        $sql = $wpdb->prepare ($sql,$adventure_id,$current_user->ID,'gm', 'gm', 'in');

                        $wpdb->query($sql);
                        $data['success'] = true;
                        BR_Activity::instance()->logActivity($adventure_id,'update','adventure');
                        $data['location'] = get_bloginfo('url').'/new-adventure/?adventure_id='.$adventure_id;

                        $saveSettings = BR_Config::instance()->saveSettingsProcess($adventure_settings, $adventure_id);
                        if($saveSettings){
                            BR_Activity::instance()->logActivity($adventure_id,'adv-settings-updated','adventure');
                            //$data['message'] .= '<h3>'.__("Features saved","bluerabbit").'</h5>';
                        }else{
                            BR_Activity::instance()->logActivity($adventure_id,'adv-settings-not-updated','adventure');
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

    // From functions/ajax.php
    public function loadStory($adv_id=null){
        global $wpdb;
        $data=array();
        $adventure_id = $adv_id ? $adv_id : $_POST['adventure_id'];
        $adventure = $this->getAdventure($adventure_id);
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

    // From functions/ajax.php
    public function getAdventure($adventure_id=NULL){
        if($adventure_id){
            global $wpdb; $current_user = wp_get_current_user();

            $roles = $current_user->roles;
            if($roles[0]=='administrator'){
                $isAdmin=true;
            }
            if(is_page('new-adventure')){
                if(isset($isAdmin) && $isAdmin==true){
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

    // From functions/ajax.php
    public function getAdventureParent($adventure_id){
        global $wpdb;
        $adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
        WHERE a.adventure_id=$adventure_id AND a.adventure_status='publish' AND a.adventure_type='template'");
        if($adventure){
            return $adventure;
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function previewTemplate(){
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

    // From functions/ajax.php
    public function createChildAdventure($template_id=0){
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
            $player_data = BR_Player::instance()->getPlayerData($current_user->ID);
            $new_child_title = $adventure->adventure_title." ".__("[new child]","bluerabbit");
            $first_str = BR_Utils::instance()->random_str(12,'1234567890abcdef');
            $code_string = $first_str.$current_user->ID;
            $adventure_code = str_shuffle($code_string);

            $duplication = "
                INSERT INTO {$wpdb->prefix}br_adventures

                (`adventure_owner`, `adventure_badge`, `adventure_logo`, `adventure_gmt`, `adventure_type`, `adventure_title`, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, `adventure_code`, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, `adventure_parent`, `org_id`)

                SELECT

                %d,`adventure_badge`, `adventure_logo`, `adventure_gmt`, 'normal', %s, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, %s, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, %d, %d

                FROM  {$wpdb->prefix}br_adventures WHERE `adventure_id` = %d;
            ";
            $sql = $wpdb->prepare($duplication, $current_user->ID,$new_child_title, $adventure_code,  $adventure->adventure_id, $player_data->org_id, $adventure->adventure_id);
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

    // From functions.php
    public function registerAdventureLogin($adventure_id) {
        global $wpdb; $current_user = wp_get_current_user();
        $adventure = $wpdb->get_row("
            SELECT adv.*, player.player_last_login FROM {$wpdb->prefix}br_adventures adv LEFT JOIN
            {$wpdb->prefix}br_player_adventure player ON adv.adventure_id=player.adventure_id AND player.player_id=$current_user->ID
            WHERE adv.adventure_id=$adventure_id
        ");
        $debug = print_r($wpdb->last_query,true);

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        $today_compare = date('Ymd');
        $last_login = $adventure->player_last_login ? date('Ymd', strtotime($adventure->player_last_login)) : 0;
        BR_Activity::instance()->logActivity($adventure_id,'login','adventure');
        if($today_compare > $last_login){
            $sql="UPDATE {$wpdb->prefix}br_player_adventure SET player_last_login=%s WHERE adventure_id=$adventure_id AND player_id=$current_user->ID";
            $registerLogin = $wpdb->query($wpdb->prepare($sql, $today, $adventure_id, $current_user->ID));
            return(true);
        }else{
            return(false);
        }
        die();
    }

    // From functions/adventure-management.php
    public function setXP(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","xp","$type",$id);
            $notification = new Notification();
            $msg_content = __('XP updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'blue','star');
            $data['just_notify'] =true;
            $data['new_xp_nonce'] = wp_create_nonce('xp_nonce');
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setEP(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","ep","$type",$id);
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

    // From functions/adventure-management.php
    public function setBLOO(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","bloo","$type",$id);
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

    public function setValidate(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $validate = $_POST['validate'] ? 1 : 0;
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'validate_nonce')){
            if($type == 'quest'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_validate=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$validate,$id,$adventure_id);
                $wpdb->query($sql);

                $data['success'] = true;
                BR_Activity::instance()->logActivity($adventure_id, "set","validate","$type",$id);
                $notification = new Notification();
                $msg_content = $validate ? __('Validation required before awarding','bluerabbit') : __('Validation no longer required','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green','check');
                $data['just_notify'] =true;
                $data['new_validate_nonce'] = wp_create_nonce('validate_nonce');
            }
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    public function setOptional(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $optional = $_POST['optional'] ? 1 : 0;
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'optional_nonce')){
            if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_optional=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$optional,$id,$adventure_id);
                $wpdb->query($sql);

                $data['success'] = true;
                BR_Activity::instance()->logActivity($adventure_id, "set","optional","$type",$id);
                $notification = new Notification();
                $msg_content = $optional ? __('Marked as Side Quest','bluerabbit') : __('Marked as Required','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'blue','check');
                $data['just_notify'] =true;
                $data['new_optional_nonce'] = wp_create_nonce('optional_nonce');
            }
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setMaxPlayers(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","max_players","achievement",$id);
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

    // From functions/adventure-management.php
    public function updateAdventureTitle(){
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
            BR_Activity::instance()->logActivity($adventure_id, "update","title","adventure");
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

    // From functions/adventure-management.php
    public function setTitle(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","title","$type",$id);
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

    // From functions/adventure-management.php
    public function setBadge(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","badge","$type",$id);
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

    // From functions/adventure-management.php
    public function setColor(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","color","$type",$id);
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

    // From functions/adventure-management.php
    // $category is now a br_item_categories.category_id (0 = no category), not a
    // free-text color string - the quick-edit dropdown in manage-items.php lists real
    // categories now (see BR_Item::getCategories).
    public function setCategory(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $category_id = (int) $_POST['category'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'item_cat_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_items SET item_category_id=%d WHERE (item_id=%d AND adventure_id=%d AND item_type='consumable') OR item_parent=%d";
            $sql = $wpdb->prepare ($sql, $category_id, $id, $adventure_id, $id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","item-category","",$id);
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

    // From functions/adventure-management.php
    public function setLevel(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","level","$type",$id);

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

    // From functions/adventure-management.php
    public function setDisplayStyle(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","display-style","$type",$id);
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

    // From functions/adventure-management.php
    public function setStartDate(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","start-date","$type",$id);
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

    // From functions/adventure-management.php
    public function setDeadline(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","deadline","$type",$id);
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

    // From functions/adventure-management.php
    public function setMagicCode(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","magic-code","",$id);
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

    // From functions/adventure-management.php
    public function breakParent(){
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
            BR_Activity::instance()->logActivity($adventure_id, "break-parent","","",$id);
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
}

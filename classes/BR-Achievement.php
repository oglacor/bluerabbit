<?php
class BR_Achievement {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function reorderAchievements(){
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
            BR_Activity::instance()->logActivity($adventure_id,'reoredered','achievements',serialize($the_order));
        }else{
            $data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
            $data['message'] .= "<h4>".$k."</h4>";
        }
        echo json_encode($data);
        die();
    }

    public function loadAchievementCard(){
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

    public function displayAchievementCard(){
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
            $data['achievement_content'] = apply_filters("the_content", $a->achievement_content);
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

    public function updateAchievement(){
        global $wpdb; $current_user = wp_get_current_user();



        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_achievement_nonce')) {
            $a_data = $_POST['achievement_data'];
            $adventure_id = $a_data['adventure_id'];

            $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
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

            if(!$magic_code){
                $magic_code = BR_Utils::instance()->random_str(20,'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'); 
            }


            if(!$a_name){
                $errors[] = __("Please add an achievement name","bluerabbit");
            }
            if(!$a_badge){
                $errors[] = __("Please add an image for the achievement","bluerabbit");
            }
            if($a_display != 'path'){
                $a_group="";
            }elseif($a_group === '' && $a_id){
                // Guard against the form round-tripping an empty group for an existing
                // path achievement (e.g. the group field failing to load) - that would
                // silently drop it out of its branch's one-per-player exclusivity check
                // in BR_Achievement::magicCode(). Preserve whatever is already saved.
                $a_group = (string) $wpdb->get_var($wpdb->prepare(
                    "SELECT achievement_group FROM {$wpdb->prefix}br_achievements WHERE achievement_id=%d", $a_id
                ));
            }
            if(!$a_color){
                $a_color='amber';
            }
            if(!$errors){

                $total_achievements = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_status='publish'");
                $a_order = count($total_achievements);
                $sql = "INSERT INTO {$wpdb->prefix}br_achievements (achievement_id, adventure_id, achievement_xp, achievement_ep, achievement_bloo, achievement_name, achievement_badge, achievement_status, achievement_color, achievement_code, achievement_content, achievement_deadline, achievement_max, achievement_order, achievement_display, achievement_path, achievement_group)
                VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %d, %d, %s, %d, %s)
                ON DUPLICATE KEY UPDATE
                adventure_id=%d, achievement_xp=%d, achievement_ep=%d, achievement_bloo=%d, achievement_name=%s, achievement_badge=%s, achievement_status=%s, achievement_color=%s, achievement_code=%s, achievement_content=%s, achievement_deadline=%s, achievement_max=%d, achievement_display=%s, achievement_path=%d, achievement_group=%s";
                $sql = $wpdb->prepare($sql,$a_id,$adv_parent_id,$a_xp, $a_ep, $a_bloo, $a_name, $a_badge, $a_status, $a_color, $magic_code, $a_content,$a_deadline, $a_max, $a_order, $a_display, $a_path, $a_group, $adventure_id,$a_xp, $a_ep, $a_bloo, $a_name, $a_badge, $a_status, $a_color, $magic_code, $a_content,$a_deadline,$a_max, $a_display, $a_path, $a_group);
                $a_query = $wpdb->query($sql);
                if($a_query){
                    $updated_id = $wpdb->insert_id;
                }
                if($updated_id){
                    $data['success']=true;
                        $achQrCode = BR_Utils::instance()->createQR($ach_args = array(
                            'filename' => "achievement-$updated_id-QR-$magic_code.png",
                            'content' => get_bloginfo('url')."/magic-link/?c=$magic_code&adv=$adventure->adventure_id",
                            'logo' => $a_badge
                        ));
                        $wpdb->update(
                            $wpdb->prefix.'br_achievements',
                            array('achievement_qrcode' => $achQrCode),
                            array('achievement_id' => $updated_id)
                        );
                        $branch_group_id = isset($a_data['branch_group_id']) ? (int) $a_data['branch_group_id'] : null;
                        $wpdb->update(
                            $wpdb->prefix.'br_achievements',
                            array('branch_group_id' => $branch_group_id ?: null),
                            array('achievement_id' => $updated_id)
                        );

                        // Rank threshold, editable here as well as on the adventure Settings
                        // Ranks panel - both write to the same br_adventure_ranks row, keyed
                        // by achievement_id, so whichever was saved last wins (same as any
                        // other shared-resource edit from two places).
                        $wpdb->query($wpdb->prepare(
                            "DELETE FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=%d AND achievement_id=%d",
                            $adv_parent_id, $updated_id
                        ));
                        if($a_display == 'rank' && isset($a_data['a_rank_level']) && $a_data['a_rank_level'] !== ''){
                            $a_rank_condition = $a_data['a_rank_condition'] ?? 'level';
                            if($a_rank_condition !== 'level' && !array_key_exists($a_rank_condition, BR_Conditions::CONDITION_TYPES)){
                                $a_rank_condition = 'level';
                            }
                            $wpdb->query($wpdb->prepare(
                                "INSERT INTO {$wpdb->prefix}br_adventure_ranks (adventure_id, rank_level, achievement_id, condition_type) VALUES (%d, %d, %d, %s)",
                                $adv_parent_id, (int) $a_data['a_rank_level'], $updated_id, $a_rank_condition
                            ));
                        }

                        $data['debug']= $achQrCode;

                    if(!$a_id){
                        $data['location']=get_bloginfo('url')."/new-achievement/?adventure_id=$adv_parent_id&achievement_id=$updated_id";
                        BR_Activity::instance()->logActivity($adv_parent_id,'add','achievement','',$updated_id);
                    }else{
                        BR_Activity::instance()->logActivity($adv_parent_id,'update','achievement','',$a_id);
                    }
                    $data['message'] .= '<h1><strong>'.$a_name.'</strong></h1> <h4><strong>'.__("Achievement Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';



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

    public function uploadBulkAchievements(){
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
                            $a_data['achievement_code']=BR_Utils::instance()->random_str(30);
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

    public function triggerAchievement($p_achievement_id="", $p_player_id="", $p_adventure_id=""){
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
                    BR_Activity::instance()->logActivity($adv_child_id,'earned','achievement',"",$player_id, $a->achievement_id);
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
                    BR_Activity::instance()->logActivity($adv_child_id,'removed','achievement',"",$player_id, $a->achievement_id);
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
                        BR_Activity::instance()->logActivity($adv_child_id,'earned','achievement',"",$player_id, $a->achievement_id);
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
                        BR_Activity::instance()->logActivity($adv_child_id,'removed','achievement',"",$player_id, $a->achievement_id);
                    }
                }else{
                    $data['success'] = true;
                    $msg_content =  __("Can't assign achievement! Already walking a different path","bluerabbit");
                    $data['message'] = $notification->pop($msg_content,'red','cancel');
                    $data['just_notify'] =true;
                    BR_Activity::instance()->logActivity($adv_child_id,'denied','achievement',"",$player_id, $a->achievement_id);
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

    public function bulkAssignAchievement(){
        global $wpdb;
        $data = ['success' => false];
        $notification = new Notification();

        $achievement_id = intval($_POST['achievement_id']);
        $adventure_id   = intval($_POST['adventure_id']);
        $raw_emails     = isset($_POST['emails']) ? (array)$_POST['emails'] : [];

        if (!$achievement_id || !$adventure_id || empty($raw_emails)) {
            $data['message'] = $notification->pop(__('Missing data','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $adventure = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d", $adventure_id
        ));
        if (!$adventure) {
            $data['message'] = $notification->pop(__('Adventure not found','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }
        $adv_child_id  = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        $a = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=%d AND adventure_id=%d AND achievement_status='publish'",
            $achievement_id, $adv_parent_id
        ));
        if (!$a) {
            $data['message'] = $notification->pop(__('Achievement not found','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        if ($adventure->adventure_gmt) { date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');

        $assigned     = 0;
        $already_has  = 0;
        $not_found    = 0;
        $assigned_ids = [];

        foreach ($raw_emails as $raw) {
            $email = sanitize_email(strtolower(trim($raw)));
            if (!$email) continue;

            // Player must exist and be enrolled in this adventure
            $player = $wpdb->get_row($wpdb->prepare(
                "SELECT p.player_id FROM {$wpdb->prefix}br_players p
                 JOIN {$wpdb->prefix}br_player_adventure pa
                   ON p.player_id = pa.player_id AND pa.adventure_id = %d AND pa.player_adventure_status = 'in'
                 WHERE p.player_email = %s
                 LIMIT 1",
                $adv_child_id, $email
            ));

            if (!$player) { $not_found++; continue; }

            // Already has this achievement -- keep it, do nothing
            $has_it = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_achievement WHERE achievement_id=%d AND player_id=%d AND adventure_id=%d",
                $achievement_id, $player->player_id, $adv_child_id
            ));

            if ($has_it) { $already_has++; continue; }

            // Assign
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d, %d, %d, %s)",
                $achievement_id, $player->player_id, $adv_child_id, $today
            ));
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}br_player_adventure SET achievement_id=%d WHERE player_id=%d AND adventure_id=%d",
                $achievement_id, $player->player_id, $adv_child_id
            ));

            $assigned++;
            $assigned_ids[] = $player->player_id;
            BR_Activity::instance()->logActivity($adv_child_id, 'earned', 'achievement', '', $player->player_id, $achievement_id);
        }

        $msg = sprintf(
            __('%d assigned, %d already had it, %d not found / not enrolled','bluerabbit'),
            $assigned, $already_has, $not_found
        );
        $data['success']      = true;
        $data['assigned_ids'] = $assigned_ids;
        $data['just_notify']  = true;
        $data['message']      = $notification->pop($msg, $assigned > 0 ? 'green' : 'orange', 'achievement');
        echo json_encode($data);
        die();
    }

    public function triggerAchievements($p_ach_id=NULL, $p_adv_id=NULL, $p_status=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $achievement_id = $p_ach_id ? $p_ach_id : $_POST['achievement_id'];
        $adventure_id = $p_adv_id ? $p_adv_id : $_POST['adventure_id'];
        $status = $p_status ? $p_status : $_POST['status'];

        $notification = new Notification();

        $a = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_achievements a
        WHERE a.achievement_id=$achievement_id AND a.achievement_status='publish'");

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $a->adventure_id;



        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');

        if($a){
            if($a->achievement_display !== 'rank'){
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
                    BR_Activity::instance()->logActivity($adventure_id,'assigned-all','achievement',"", $a->achievement_id);
                }else{
                    $msg_content =  __("Achievements Removed","bluerabbit");
                    $data['message'] = $notification->pop($msg_content,'red','remove');
                    $data['just_notify'] =true;
                    $data['action'] = 'removed-all';
                    BR_Activity::instance()->logActivity($adventure_id,'removed-all','achievement',"", $a->achievement_id);
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

    public function choosePath(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $path = $_POST['path'];
        $adventure_id = $_POST['adventure_id'];
        $chosen_path = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$path AND adventure_id=$adventure_id AND achievement_status='publish'");
        $n = new Notification();
        if($chosen_path){
            $data = $this->magicCode($chosen_path->achievement_code, $chosen_path->adventure_id);
        }else{
            $msg_content = __('Error','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify']=true;
        }
        echo json_encode($data);
        die();
    }

    public function magicCode($p_code = "", $p_adv=""){

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
                $adventure = BR_Adventure::instance()->getAdventure($c->adventure_id);
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
                    BR_Activity::instance()->logActivity($adv_child_id,'max-reached','magic-code',$code,$c->achievement_id);
                }
                if($c->achievement_deadline && $c->achievement_deadline != '0000-00-00 00:00:00'){
                    $now = date('YmdHi');
                    $deadline = date('YmdHi',strtotime($c->achievement_deadline));
                    if($now > $deadline){
                        $data['message']= '<span class="icon icon-deadline icon-xl"></span>';
                        $data['message'].= '<h2 class="red-A400">'.__("Deadline missed!",'bluerabbit').'</h2>';
                        $data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
                        $error['deadline']= __('Achievement no longer available',"bluerabbit");
                        BR_Activity::instance()->logActivity($adv_child_id,'deadline','magic-code',$code,$c->achievement_id);
                    }
                }
                if($c->code_status =='redeem'){
                    $data['message']= '<span class="icon icon-carrot icon-xl"></span>';
                    $data['message'].= '<h2 class="orange-400">'.__("This code has already been used!",'bluerabbit').'</h2>';
                    $data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
                    $error['carrot']= __('This code has already been used!',"bluerabbit");
                    BR_Activity::instance()->logActivity($adv_child_id,'redeemed','magic-code',$code,$c->achievement_id);
                }
                if($c->code_status =='expired'){
                    $data['message']= '<span class="icon icon-deadline icon-xl"></span>';
                    $data['message'].= '<h2>'.__("This code already expired!",'bluerabbit').'</h2>';
                    $data['message'].= '<h4>'.__("You can't use this code anymore!",'bluerabbit').'</h4>';
                    $error['expired']= __('This code already expired!',"bluerabbit");
                    BR_Activity::instance()->logActivity($adv_child_id,'expired','magic-code',$code,$c->achievement_id);
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
                    $my_achs = $this->getMyAchievements($adv_child_id);


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
                            BR_Activity::instance()->logActivity($c->adventure_id,'attempt-outside-of-path','magic-code',$code);
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

                            BR_Activity::instance()->logActivity($adv_child_id,'use-unique','magic-code',$code,$c->achievement_id);
                        }
                        // Assign achievement to player
                        $sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d,%d,%d, %s)";
                        $sql = $wpdb->prepare ($sql,$ach->achievement_id, $current_user->ID, $adv_child_id, $rightnow);
                        $wpdb->query($sql);

                        if($wpdb->insert_id){
                            $data['success'] = true;
                            BR_Activity::instance()->logActivity($adv_child_id,'earned','magic-code',"",$c->achievement_id);
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
                    BR_Activity::instance()->logActivity($adv_child_id,'attempt','magic-code',$code);
                }
            }else{
                $data['message'] ='<h1>'.__("Code Doesn't exist",'bluerabbit').'</h1> <h4>'.__("click to close",'bluerabbit').'.</h4>';
                $data['location']='reload';
                $error['cancel']= __('Wrong Code',"bluerabbit");
                BR_Activity::instance()->logActivity($adv_child_id,'attempt','magic-code',$code);
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

    public function switchRank($p_achievement_id="", $p_adventure_id=""){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success']=false;
        $n = new Notification();
        $achievement_id = $p_achievement_id ? $p_achievement_id : $_POST['achievement_id'];
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
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

    public function getMyAchievements($adventure_id, $player_id=null){
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

    public function getAchievements($adventure_id, $display=""){
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

    public function newUniqueAchievementCode($p_id){
        global $wpdb; $player = wp_get_current_user();
        $id = $p_id ? $p_id : $_POST['achievement_id'];
        $ach = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$id AND achievement_status='publish'");
        $notification = new Notification();

        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$ach->adventure_id");
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');


        if($ach){
            $str = BR_Utils::instance()->random_str(20, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $new_code_query = "INSERT INTO {$wpdb->prefix}br_achievement_codes
            (`code_value`, `code_date`, `achievement_id`, `adventure_id`)
            VALUES
            (%s, %s, %d, %d)";

            $code_insert = $wpdb->query( $wpdb->prepare("$new_code_query ", $str, $today, $ach->achievement_id, $ach->adventure_id));
            $new_code_id = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adventure_id,'add','unique-achievement-code',"",$ach->achievement_id, $new_code_id);
            $data['success'] = true;
            $msg_content = __('New Code Created','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','qr');
            $data['just_notify'] =true;

            $data['content_target']='#achievement-codes-table';

            $c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievement_codes WHERE code_id=$new_code_id");
            $link = get_bloginfo('url').'/magic-link/?c='.$c->code_value.'&adv='.$ach->adventure_id;
            $data['content']='
<tr id="achievement-unique-code-'.$new_code_id.'">
    <td style="width:50px">
        <input id="ach-code-'.$new_code_id.'" type="hidden" value="'.$link.'">
        <button class="br-step-btn br-step-btn-green" style="width:36px;height:36px" onClick="copyTextFrom(\'#ach-code-'.$c->code_id.'\',\'#legend-'.$c->code_id.'\');" title="'.__("Copy","bluerabbit").'">
            <span class="icon icon-qr" style="font-size:18px"></span>
        </button>
    </td>
    <td style="position:relative">
        <span style="font-size:15px;font-weight:600;letter-spacing:0.5px;color:rgba(255,255,255,0.85)">'.$c->code_value.'</span>
        <span class="legend border rounded-max" id="legend-'.$c->code_id.'" style="background:#24da98;color:#fff;position:absolute;top:-6px;right:10px;padding:3px 10px;border-radius:12px;font-size:11px;opacity:0;transition:opacity 0.3s">'.__("Link Copied","bluerabbit").'</span>
    </td>
    <td style="width:120px">
        <div style="display:flex;gap:4px;justify-content:flex-end">
            <button class="br-btn" style="padding:4px 10px;font-size:12px" onClick="copyTextFrom(\'#ach-code-'.$c->code_id.'\',\'#legend-'.$c->code_id.'\');">
                <span class="icon icon-duplicate"></span> '.__("Copy","bluerabbit").'
            </button>
            <button class="br-step-btn br-step-btn-red" style="width:30px;height:30px" onClick="deleteAchievementCode('.$c->code_id.');" title="'.__("Delete","bluerabbit").'">
                <span class="icon icon-trash"></span>
            </button>
        </div>
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

    public function deleteAchievementCode($p_id){
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
            BR_Activity::instance()->logActivity($adventure_id,'removed','unique-achievement-code',"",$code->achievement_id,$code->code_id);
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

    public function getUniqueAchievementCodes($id){
        global $wpdb;
        $qry = $wpdb->get_results("SELECT codes.*, players.player_display_name, players.player_picture FROM {$wpdb->prefix}br_achievement_codes codes
        LEFT JOIN {$wpdb->prefix}br_players players ON codes.player_id = players.player_id
        WHERE codes.achievement_id=$id AND codes.code_status!='delete'
        ORDER BY codes.code_id ASC, FIELD(codes.code_status, 'publish', 'redeem', 'expired')");
        return $qry;
    }

    public function setAchievement(){
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
            BR_Activity::instance()->logActivity($adventure_id, "set","achievement","$type",$id);
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
}

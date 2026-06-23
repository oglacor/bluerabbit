<?php
class BR_Encounter {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function randomEncounter(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $adventure_id = $_POST['adventure_id'];
        $enc_id = $_POST['enc_id'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date("Y-m-d H:i:s");
        $current_player = BR_Player::instance()->getPlayerAdventureData($adv_child_id, $current_user->ID);

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
                BR_Activity::instance()->logActivity($adventure_id,'max-reached','ep');
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

    public function answerEncounter(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $data['success']=false;
        $adventure_id = $_POST['adventure_id'];
        $enc = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_encounters WHERE enc_id={$_POST['enc_id']} AND enc_status='publish'");
        $value = $_POST['value'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
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


            $player = BR_Player::instance()->getPlayerAdventureData($adv_child_id,$current_user->ID);
            $EP = ($player->player_ep-$objective->ep_cost);
            $data['EP'] = $EP;
            BR_Activity::instance()->logActivity($adv_child_id,'answer','encounter','',$enc->enc_id);
             //$updatePLAYER = "UPDATE {$wpdb->prefix}br_player_adventure SET player_ep=$EP WHERE player_id=$player->player_id AND adventure_id=$adventure_id";
            //$update = $wpdb->query($updatePLAYER);

            BR_Player::instance()->resetPlayer($adv_child_id, $current_user->ID);
        }else{
            //($adv_id=0,$action='',$type='',$content='',$object_id=0,$object_child_id=0
            BR_Activity::instance()->logActivity($adv_child_id,'wrong-answer','encounter','',$enc->enc_id);
            $answer = $wpdb->query($wpdb->prepare($answerInsert, $adv_child_id, $current_user->ID, 0, $enc->enc_id, $value, 0, 0, $today));
            $msg_content = __("Wrong",'bluerabbit');
            $data['message'] = $notification->pop($msg_content, 'red','cancel');
        }
        echo json_encode($data);
        die();
    }

    public function updateEncounter(){
        global $wpdb; $current_user = wp_get_current_user();

        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_encounter_nonce')) {
            $enc_data = $_POST['encounter_data'];
            $adventure_id = $_POST['adventure_id'];
            $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
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
                    BR_Activity::instance()->logActivity($adv_child_id,'add','encounter','',$enc_id);
                }else{
                    BR_Activity::instance()->logActivity($adv_child_id,'update','encounter','',$id);
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
}

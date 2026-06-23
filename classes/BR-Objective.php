<?php
class BR_Objective {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function addObjective(){
        $id=$_POST['id'];
        $adventure_id=$_POST['adventure_id'];
        $objective_type = $_POST['objective_type'];
        if($objective_type){
            global $wpdb;
            $current_user = wp_get_current_user();
            $objective_insert = "INSERT INTO {$wpdb->prefix}br_objectives (quest_id, adventure_id, objective_type) VALUES (%d, %d, %s)";
            $qs_insert = $wpdb->query( $wpdb->prepare("$objective_insert ", $id, $adventure_id, $objective_type));
            $objective_id = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adventure_id, 'add', 'objective','', $objective_id);
            $c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective_id");
            $theFile = (get_template_directory()."/objective-row.php");
        }
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }

    public function editObjective(){
        $objective_id=$_POST['objective_id'];
        $adventure_id=$_POST['adventure_id'];

        global $wpdb;
        $c = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective_id AND adventure_id=$adventure_id");
        if($c){
            $use_encounters = BR_Config::instance()->getSetting('use_encounters',$adventure_id);
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
    public function removeObjective(){
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
            BR_Activity::instance()->logActivity($objective->adventure_id, 'remove', 'objective','', $objective->objective_id);

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
    public function resetQuestObjectives(){
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
            BR_Activity::instance()->logActivity($quest->adventure_id, 'reset', 'objectives','', $quest->quest_id);
        }else{
            $data['success']=false;
            $msg_content = __("No objectives to reset" ,'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','warning');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    public function updateObjective(){
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
            BR_Activity::instance()->logActivity($objective->adventure_id, 'update', 'objective','', $objective->objective_id);
            $updated_objective = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_objectives WHERE objective_id=$objective->objective_id");

            if($objective->adventure_type=='template'){
                $children_update = "UPDATE {$wpdb->prefix}br_objectives SET `objective_modified`=%s, `objective_content`=%s, `objective_keyword`=%s,`objective_success_message`=%s, `ep_cost`=%d WHERE objective_parent=$objective->objective_id AND objective_id!=$objective->objective_id";

                $children_update = $wpdb->query( $wpdb->prepare("$children_update ", $today, $content, $keyword, $success_message, $objective_data['objective_ep_cost']));

                BR_Activity::instance()->logActivity($objective->adventure_id, 'update', 'objective-children','', $objective->objective_id);
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

    public function factCheck(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $keyword=trim(strtolower($_POST['keyword']));
        $quest_id=$_POST['quest_id'];
        $adventure_id=$_POST['adventure_id'];
        $objective_id = $_POST['objective_id'];
        $player = BR_Player::instance()->getPlayerAdventureData($adventure_id,$current_user->ID);

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
                BR_Activity::instance()->logActivity($adv_child_id,'solved','objective',$keyword, $objective->objective_id);

                // CHECK IF ALL OBJECTIVES ARE SOLVED
                $objectives = $this->getObjectives($adv_child_id, $objective->quest_id, $current_user->ID);
                $objectives_completed = 0;
                foreach($objectives as $cc){
                    if($cc->player_id==$current_user->ID){
                        $objectives_completed++;
                    }
                }
                if($objectives_completed >= count($objectives)){
                    $objectives_achieved = true; // CHECK REQUIREMENTS AND INSERT INTO PLAYER_POSTS IF COMPLETED
                    $completeRequirements = BR_Progression::instance()->getRequirements($objective->quest_id, $adv_child_id );
                    if($completeRequirements){
                        $data['debug'] = print_r($completeRequirements, true);
                        $workRegistered = BR_Activity::instance()->registerPost($objective->quest_id, $adv_child_id, 'mission');
                        if($workRegistered){
                            BR_Activity::instance()->logActivity($adv_child_id,'complete','mission', $objective->quest_id);
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
                BR_Activity::instance()->logActivity($adventure_id,'tried','objective',$keyword, $objective->objective_id);
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

    public function insertSolvedObjective(){
        $c = $this->getObjective($_POST['id']);
        $theFile = (get_template_directory()."/objective-item-$c->objective_type-solved.php");
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }

    public function spendEP(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $adventure_id=$_POST['adventure_id'];
        $quest_id=$_POST['quest_id'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        $player = BR_Player::instance()->getPlayerAdventureData($adv_child_id,$current_user->ID);
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
            BR_Activity::instance()->logActivity($adv_child_id,'spent','ep', $EP);
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

    public function getObjectives($p_adv_id='', $quest_id='', $player_id=null){
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

    public function getObjective($obj_id='', $player_id=null){
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
}

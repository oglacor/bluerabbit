<?php
class BR_Transaction {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function resetDemoAdventurePlayer(){
        global $wpdb;
        $data = array();
        $n = new Notification();
        $data['just_notify'] =true;
        $current_user = wp_get_current_user();
        $player_id = $current_user->ID;
        $adventure_id=$_POST['adventure_id'];
        $password=$_POST['player_password'];
        $req_password_reset_demo = BR_Config::instance()->getSetting('req_password_reset_demo', $adventure_id);

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

    public function payment(){
        global $wpdb; $current_user = wp_get_current_user();
        $nonce = $_POST['nonce'];
        $adventure_id = $_POST['adventure_id'];
        $data = array();
        $data['success'] = false;
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
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
                $player = BR_Player::instance()->getPlayerAdventureData($adv_child_id,$current_user->ID);
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

                    BR_Activity::instance()->logActivity($adv_child_id,'purchase',$type,"",$blocker_id);
                    $player_id = $current_user->ID;
                    BR_Player::instance()->resetPlayer($adv_child_id,$player_id);
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

    public function resetTransactions(){
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
            BR_Player::instance()->resetPlayer($adventure_id,$player_id);
            BR_Activity::instance()->logActivity($adventure_id, "reset","transactions","",$player_id);
            $data['message'] = "<h1>".__("All transactions deleted successfully!","bluerabbit")."</h1>";
            $data['location']= get_bloginfo("url")."/backpack/?adventure_id=$adventure_id";
        }

        echo json_encode($data);
        die();
    }
}

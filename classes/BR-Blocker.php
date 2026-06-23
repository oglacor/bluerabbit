<?php
class BR_Blocker {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function updateBlocker(){
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

                    BR_Activity::instance()->logActivity($adventure_id,'add','blocker','',$blocker_id);
                }else{
                    BR_Activity::instance()->logActivity($adventure_id,'update','blocker','',$blocker_id);
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

    public function payBlocker(){
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
            $player = BR_Player::instance()->getPlayerAdventureData($adventure_id,$current_user->ID);
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
                BR_Activity::instance()->logActivity($adventure_id,'purchase','blocker',"",$blocker_id);
                $player_id = $current_user->ID;
                BR_Player::instance()->resetPlayer($adventure_id,$player_id);
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

    public function getBlockers($adventure_id){
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

    public function getMyBlockers($adventure_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_blockers a
        JOIN {$wpdb->prefix}br_player_blocker b
        ON a.adventure_id=b.adventure_id AND b.player_id=$current_user->ID
        WHERE a.adventure_id=$adventure_id AND a.blocker_status='publish'");
        return $result;
    }
}

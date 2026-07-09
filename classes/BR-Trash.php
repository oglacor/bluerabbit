<?php
class BR_Trash {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function br_trash(){
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
        $n = new Notification();
        $data['just_notify'] =true;

        if(wp_verify_nonce($nonce, 'trash_nonce')){
            $status = 'trash';
            $data['success'] = true;
            $msg_content = __('Sent to trash!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','trash');
        }elseif(wp_verify_nonce($nonce, 'delete_nonce')){
            $status = 'delete';
            $data['success'] = true;
            $msg_content = __('Deleted!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','cancel');
        }elseif(wp_verify_nonce($nonce, 'locked_nonce')){
            $status = 'locked';
            $data['success'] = true;
            $msg_content = __('Locked!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'grey','lock');
        }elseif(wp_verify_nonce($nonce, 'hidden_nonce')){
            $status = 'hidden';
            $data['success'] = true;
            $msg_content = __('Published as hidden','bluerabbit');
            $data['message'] = $n->pop($msg_content,'blue','hide');
        }elseif(wp_verify_nonce($nonce, 'publish_nonce')){
            $status = 'publish';
            $data['success'] = true;
            $msg_content = __('Restored!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'green','check');
        }elseif(wp_verify_nonce($nonce, 'draft_nonce')){
            $status = 'draft';
            $data['success'] = true;
            $msg_content = __('Restored!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'amber','document');
        }else{
            $status = NULL;
            $data['success'] = true;
            $msg_content = __('Unauthorized access!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','cancel');
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
                $target_player = isset($_POST['player_id']) ? (int) $_POST['player_id'] : $current_user->ID;
                $quest_id = (int) $id;
                if($status=='delete' || $status=='trash'){
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_posts WHERE quest_id=%d AND adventure_id=%d AND player_id=%d", $quest_id, $adventure_id, $target_player));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_steps WHERE quest_id=%d AND adventure_id=%d AND player_id=%d", $quest_id, $adventure_id, $target_player));
                    $quest = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=%d", $quest_id));
                    if ($quest) {
                        if ($quest->mech_item_reward) {
                            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_transactions WHERE player_id=%d AND adventure_id=%d AND object_id=%d AND trnx_status='publish'", $target_player, $adventure_id, $quest->mech_item_reward));
                        }
                        if ($quest->mech_achievement_reward) {
                            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_achievement WHERE player_id=%d AND adventure_id=%d AND achievement_id=%d", $target_player, $adventure_id, $quest->mech_achievement_reward));
                        }
                    }
                    BR_Player::instance()->resetPlayer($adventure_id, $target_player);
                    BR_Activity::instance()->logActivity($adventure_id, 'reset', 'player_post', "player=$target_player", $quest_id);
                    $sql = null;
                }else{
                    $sql = "UPDATE {$wpdb->prefix}br_player_posts SET pp_status=%s, pp_modified=%s WHERE quest_id=%d AND adventure_id=%d AND player_id=%d";
                    $sql = $wpdb->prepare ($sql,$status, $today,$quest_id,$adventure_id,$target_player);
                }
            }elseif($type == 'attempt'){
                $target_player = isset($_POST['player_id']) ? (int) $_POST['player_id'] : $current_user->ID;
                $attempt_id = (int) $id;
                $attempt = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_challenge_attempts WHERE attempt_id=%d", $attempt_id));
                if ($attempt && ($status=='delete' || $status=='trash')) {
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE attempt_id=%d AND player_id=%d", $attempt_id, $attempt->player_id));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_challenge_attempts WHERE attempt_id=%d", $attempt_id));
                    $remaining = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}br_challenge_attempts WHERE quest_id=%d AND player_id=%d AND adventure_id=%d AND attempt_status='success'", $attempt->quest_id, $attempt->player_id, $adventure_id));
                    if (!$remaining) {
                        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_posts WHERE quest_id=%d AND adventure_id=%d AND player_id=%d", $attempt->quest_id, $adventure_id, $attempt->player_id));
                    }
                    BR_Player::instance()->resetPlayer($adventure_id, $attempt->player_id);
                    BR_Activity::instance()->logActivity($adventure_id, 'reset', 'attempt', "player={$attempt->player_id}", $attempt_id);
                    $sql = null;
                } else {
                    $sql = "UPDATE {$wpdb->prefix}br_challenge_attempts SET attempt_status=%s WHERE attempt_id=%d AND adventure_id=%d";
                    $sql = $wpdb->prepare ($sql,$status,$id,$adventure_id);
                }
            }elseif($type == 'survey-answer'){
                $target_player = isset($_POST['player_id']) ? (int) $_POST['player_id'] : $current_user->ID;
                if($status=='delete'){
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_survey_answers WHERE survey_id=%d AND player_id=%d AND adventure_id=%d", $id, $target_player, $adventure_id));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_steps WHERE quest_id=%d AND player_id=%d AND adventure_id=%d", $id, $target_player, $adventure_id));
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}br_player_posts WHERE quest_id=%d AND adventure_id=%d AND player_id=%d", $id, $adventure_id, $target_player));
                    BR_Player::instance()->resetPlayer($adventure_id, $target_player);
                    $sql = null;
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

            if ($sql) {
                $wpdb->query($sql);
                BR_Activity::instance()->logActivity($adventure_id, $status,$type,"",$id);
                BR_Player::instance()->resetPlayer($adventure_id, $current_user->ID);
            }
            $data['success'] = true;
            if($reload){
                $data['location']='reload';
            }else{
                $data['location'] = get_bloginfo("url")."/adventure/?adventure_id=$adventure_id";
            }
        }
        echo json_encode($data);
        die();
    }

    public function br_empty_trash(){
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

            BR_Activity::instance()->logActivity($adventure_id, 'empty-trash', $type,"");
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
}

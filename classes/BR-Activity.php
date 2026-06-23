<?php
class BR_Activity {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function logActivity($adv_id=0,$action='',$type='',$content='',$object_id=0,$object_child_id=0){
        global $wpdb; $current_user = wp_get_current_user();
        $log_sql = "INSERT INTO {$wpdb->prefix}br_activity_log (`adventure_id`, `player_id`, `log_action`, `log_type`, `log_object_id`, `log_object_child_id`, `log_content`, `log_date`) VALUES (%d,%d,%s,%s,%d,%d,%s,%s)";
        $today = date('Y-m-d h:i:s');
        $log_sql = $wpdb->query($wpdb->prepare($log_sql,$adv_id,$current_user->ID,$action,$type,$object_id,$object_child_id,$content,$today));
        return $log_sql;
    }

    public function registerPost($quest_id, $adv_id, $type="quest", $content=""){
        global $wpdb;
        $current_user = wp_get_current_user();
        $adventure = BR_Adventure::instance()->getAdventure($adv_id);
        $quest = BR_Quest::instance()->getQuest($quest_id);

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
}

<?php
class BR_Announcement {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function postAnn($adventure_id, $ann_content, $type){
        global $wpdb; $current_user = wp_get_current_user();

        $adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
        LEFT JOIN {$wpdb->prefix}br_player_adventure b
        ON a.adventure_id=b.adventure_id AND b.player_id=$current_user->ID
        WHERE a.adventure_id=$adventure_id");

        $ann_sql = "INSERT INTO {$wpdb->prefix}br_announcements (adventure_id, ann_content, ann_type, ann_author)
        VALUES (%d, %s, %s, %d)";
        $ann_sql = $wpdb->prepare($ann_sql, $adventure_id, $ann_content, $type, $current_user->ID);
        $ann_sql = $wpdb->query($ann_sql);
        if($wpdb->insert_id){
            return $adventure;
        }else{
            return false;
        }
        die();
    }

    public function loadChat(){
        $type=$_POST['type'];
        $adventure_id=$_POST['adventure_id'];
        $guild_id  =$_POST['guild_id'];
        $current_user = wp_get_current_user();
        $theFile = (get_template_directory()."/msg-$type.php");
        include ($theFile);
        die();
    }

    public function getAnnouncements($adventure_id,$type='public'){
        global $wpdb; $current_user = wp_get_current_user();

        if($type != 'public'){
            $type_str = "a.ann_type='$type'";
        }else{
            $type_str = "(a.ann_type='public' OR a.ann_type='announcement' )";
        }
        $player_adventure_status = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$current_user->ID");

        if($player_adventure_status->player_adventure_role == 'gm'){ $isGM = true; }
        $anns = $wpdb->get_results("SELECT a.ann_id, a.ann_content, a.ann_author, a.ann_date, a.ann_type, b.player_picture, b.player_display_name, c.player_adventure_role FROM {$wpdb->prefix}br_announcements a
        LEFT JOIN {$wpdb->prefix}br_players b
        ON a.ann_author=b.player_id
        LEFT JOIN {$wpdb->prefix}br_player_adventure c
        ON a.ann_author=c.player_id
        WHERE a.adventure_id=$adventure_id AND $type_str AND a.ann_status='publish' GROUP BY a.ann_id ORDER BY a.ann_date DESC LIMIT 100 ");

        $qry =  array('anns'=>$anns,'isTeacher'=>$isGM);

        return $qry;
    }
}

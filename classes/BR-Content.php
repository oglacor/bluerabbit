<?php
class BR_Content {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function loadContent(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();

        $content = isset($_POST['content']) ? $_POST['content'] : NULL ;
        $id = isset($_POST['id']) ? $_POST['id'] : NULL ;
        $adventure = isset($_POST['adventure_id']) ? BR_Adventure::instance()->getAdventure($_POST['adventure_id'])  : NULL ;
        $theFile = (get_template_directory()."/$content.php");
        if(file_exists($theFile)) {
            include ($theFile);
        }else{
            echo "<h1>".__("Content doesn't exist","bluerabbit")."</h1>";
        }
        die();
    }

    public function loadQuestCard(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $quest_id = $_POST['quest_id'];
        $adventure_id = $_POST['adventure_id'];
        $quest = $wpdb->get_row("SELECT
            quest.*, player.player_nickname, player.player_picture FROM {$wpdb->prefix}br_quests quest
            JOIN {$wpdb->prefix}br_players player
            ON player.player_id = quest.quest_author
            WHERE quest.quest_id=$quest_id
        ");

        if($quest){
            $pp = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_posts WHERE player_id=$current_user->ID AND quest_id=$quest->quest_id");

            $adventure = BR_Adventure::instance()->getAdventure($adventure_id);

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
            $isFinished = $pp ? true : false;
            $theFile = (get_template_directory()."/card-quest.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            echo "<h1>".__("Quest doesn't exist","bluerabbit")."</h1>";
        }
        die();
    }

    public function loadLore(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $isAdmin = $roles[0]=='administrator' ? true : false;
        $lore_id = $_POST['lore_id'];
        $lore = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$lore_id AND quest_status='publish'");
        if($lore){
            $theFile = (get_template_directory()."/lore.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            echo "<h1>".__("Can't find this resource","bluerabbit")."</h1>";
        }
        die();
    }

    public function searchLore(){
        global $wpdb;
        $data=array();
        $search_str = $_POST['search_string'];
        $search_str = '%'.$search_str.'%';
        $adventure_id = $_POST['adventure_id'];

        //SELECT * FROM `c0d_1mX_br_quests` WHERE `quest_title` LIKE '%volt%' AND `quest_content` LIKE '%voltage%'
        $lores = "SELECT * FROM {$wpdb->prefix}br_quests WHERE (`quest_title` LIKE %s OR `quest_content` LIKE %s) AND quest_type ='lore' AND quest_status='publish' AND adventure_id=$adventure_id";
        $lores = $wpdb->get_results($wpdb->prepare($lores, $search_str, $search_str));;
        if($lores){
            foreach($lores as $key=>$b){
                $theFile = (get_template_directory()."/lore-item.php");
                if(file_exists($theFile)) {
                    include ($theFile);
                }
            }
        }else{
            echo "<h1 class='white-color text-center font _30 w900 uppercase'>".__("No results found. Search for something different?","bluerabbit")."</h1>";
        }
        die();
    }

    public function loadSidebar(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $isAdmin = $roles[0]=='administrator' ? true : false;
        $filename = $_POST['filename'];
        $adventure_id = $_POST['adventure_id'];
        $adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
        JOIN {$wpdb->prefix}br_player_adventure c
        ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
        WHERE a.adventure_id=$adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
        $current_player = BR_Player::instance()->getPlayerAdventureData($adventure_id,$current_user->ID);
        $isGM = false;
        if($adventure->adventure_owner == $current_user->ID){
            $isGM = true;
            $isOwner = true;
        }elseif($adventure->player_adventure_role == 'gm'){
            $isGM = true;
        }elseif($adventure->player_adventure_role == 'npc'){
            $isNPC = true;
        }
        $theFile = (get_template_directory()."/sidebar-$filename.php");
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }
}

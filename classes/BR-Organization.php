<?php
class BR_Organization {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function updateOrg(){
        global $wpdb; $current_user = wp_get_current_user();

        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_org_nonce')) {
            $org_data = $_POST['org_data'];
            $id = $org_data['id'];
            $name = $org_data['name'];
            $logo = $org_data['logo'];
            $color = $org_data['color'];
            $status = $org_data['status'];
            $about = stripslashes_deep($org_data['about']);

            $today = date("Y-m-d H:i:s");

            $sql = "INSERT INTO {$wpdb->prefix}br_orgs
            (`org_id`,`org_name`,`org_logo`,`org_content`,`org_color`,`owner_id`,`org_status`)
            VALUES (%d, %s, %s, %s, %s, %d, %s)
            ON DUPLICATE KEY UPDATE

            `org_name`=%s,`org_logo`=%s,`org_content`=%s,`org_color`=%s,`owner_id`=%d,`org_status`=%s,`org_modified`=%s
            ";
            $sql = $wpdb->prepare($sql, $id, $name, $logo,$about, $color,  $current_user->ID, $status, $name, $logo,$about, $color,  $current_user->ID, $status, $today);

            $the_query = $wpdb->query($sql);
            $org_id = $wpdb->insert_id;
            $n = new Notification();

            $msg_content = __('Organization Saved!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'green');
            $data['success'] = true;
            if($id){
                BR_Activity::instance()->logActivity(0,'update','org','',$org_id);
            }else{
                BR_Activity::instance()->logActivity(0,'add','org','',$org_id);
            }
            $data['just_notify'] =true;

        }else{
            $data['message'] = '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Unauthorized access","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
            $data['location'] = get_bloginfo('url');
        }
        echo json_encode($data);
        die();
    }

    public function getOrgs($org_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        if($roles[0]=='administrator'){
            $isAdmin=true;
            if($org_id){
                $org = $wpdb->get_row("SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_orgs orgs
                LEFT JOIN {$wpdb->prefix}br_players players
                ON orgs.owner_id = players.player_id
                WHERE orgs.org_id=$org_id");
                return $org;
            }else{
                $orgs = $wpdb->get_results("SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_orgs orgs
                    LEFT JOIN {$wpdb->prefix}br_players players
                    ON orgs.owner_id = players.player_id
                ");
                return $orgs;
            }
        }else{
            return false ;
        }
    }

    public function getOrgAdventures($org_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        if($roles[0]=='administrator' && $org_id){
            $adventures = $wpdb->get_row("SELECT adventures.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_adventures adventures
            JOIN {$wpdb->prefix}br_players players
            ON adventures.adventure_owner = players.player_id
            WHERE adventures.org_id=$org_id");
            return $adventures;
        }else{
            return false ;
        }
    }

    public function getOrgPlayers($org_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        if($roles[0]=='administrator' && $org_id){
            $players = $wpdb->get_results("SELECT players.*, org.role FROM {$wpdb->prefix}br_players players
            JOIN {$wpdb->prefix}br_player_org org
            ON org.player_id = players.player_id
            WHERE org.org_id=$org_id");
            return $players;
        }else{
            return false ;
        }
    }

    public function setPlayerOrgCapabilities(){
        global $wpdb; $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $n = new Notification();
        $data = array();
        if($roles[0]=='administrator'){
            $org_id = $_POST['org_id'];
            $player_id = $_POST['player_id'];
            $role = $_POST['role'];

            $update_string = "UPDATE {$wpdb->prefix}br_player_org SET `role`=%s WHERE `org_id`=%d AND `player_id`=%d";
            $updatedPlayer = $wpdb->query( $wpdb->prepare("$update_string ", $role, $org_id, $player_id));
            $data['org_role_update'] = true;
            $data['player_id'] = $player_id;
            $data['role_update'] = $role;

            $data['success'] = true;
            $msg_content = __('Role Updated','bluerabbit');
            $data['message'] = $n->pop($msg_content,'green','check');
            $data['just_notify'] =true;
        }else{
            $data['success'] = false;
            $msg_content = __('Error. Role Not Updated','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    public function findPlayersToOrg(){
        global $wpdb; $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        if($roles[0]=='administrator' && wp_verify_nonce($_POST['nonce'], 'br_search_player_org_nonce')){
            $search_str = $_POST['search_string'];
            $search_str = '%'.$search_str.'%';
            $players_results = "SELECT players.* FROM {$wpdb->prefix}br_players players
            WHERE (`players`.`player_email` LIKE %s
            OR `players`.`player_first` LIKE %s
            OR `players`.`player_last` LIKE %s
            OR `players`.`player_display_name` LIKE %s)";
            $players_results = $wpdb->get_results($wpdb->prepare($players_results, $search_str, $search_str, $search_str, $search_str));
            if($players_results){
                foreach ($players_results as $p){
                    $theFile = (get_template_directory()."/player-select-org.php");
                    if(file_exists($theFile)) {
                        include ($theFile);
                    }
                }
                die();
            }else{
                echo "
<li class='margin-5'>
    <div class='icon-group'>
        <button class='button-icon player-picture white-bg sq-60'>

        </button>
        <div class='icon-content text-left'>
            <span class='line font _18 player-name'>".__("No results","bluerabbit")."
            </span>
        </div>
    </div>
</li>
";
            }
            die();
        }else{
            return false ;
        }
    }

    public function addPlayerToOrg(){
        global $wpdb; $current_user = wp_get_current_user();
        $player = BR_Player::instance()->getPlayerData($_POST['player_id']);
        $org = $this->getOrgs($_POST['org_id']);
        if($player && $org && $current_user->roles[0]=='administrator'){
            $addToOrg = "INSERT INTO {$wpdb->prefix}br_player_org (`player_id`, `org_id`) VALUES (%d, %d)";
            $code_insert = $wpdb->query( $wpdb->prepare("$addToOrg ", $player->player_id, $org->org_id));
            $theFile = (get_template_directory()."/player-row-org.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }else{
            return false;
        }
    }
}

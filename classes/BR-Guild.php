<?php
class BR_Guild {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // Source: functions/ajax.php — updateGuild
    public function updateGuild(){
        global $wpdb; $current_user = wp_get_current_user();

        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_guild_nonce')) {
            $g_data = $_POST['guild_data'];
            $g_id = $g_data['g_id'];
            $g_status = $g_data['g_status'];
            $g_name = stripslashes_deep($g_data['g_name']);
            $g_group = stripslashes_deep($g_data['g_group']);
            $g_logo = $g_data['g_logo'];
            $g_capacity = $g_data['g_capacity'];
            $g_assign_on_login = $g_data['g_assign_on_login'];
            $g_color = $g_data['g_color'];
            $adventure_id = $g_data['adventure_id'];
            $guild_players = $g_data['guild_players'];
            if(!$g_name){
                $errors[] = __("Guild name is required","bluerabbit");
            }
            if(!$g_logo){
                $errors[] = __("Please add a logo for the guild","bluerabbit");
            }
            if(!$g_color){
                $g_color='deep-orange';
            }
            if(!$errors){
                $first_str = BR_Utils::instance()->random_str(12,'1234567890abcdefghijkls');
                $code_string = $first_str.$current_user->ID;
                $guild_code = str_shuffle($code_string);

                $sql = "INSERT INTO {$wpdb->prefix}br_guilds (guild_id, adventure_id, guild_name, guild_logo, guild_status, guild_color, assign_on_login, guild_code, guild_group, guild_capacity)
                VALUES (%d, %d, %s, %s, %s, %s, %d, %s, %s, %d)
                ON DUPLICATE KEY UPDATE
                adventure_id=%d, guild_name=%s, guild_logo=%s, guild_status=%s, guild_color=%s, assign_on_login=%d, guild_group=%s, guild_capacity=%s";
                $sql = $wpdb->prepare($sql,$g_id,$adventure_id,$g_name, $g_logo, $g_status, $g_color, $g_assign_on_login, $guild_code, $g_group, $g_capacity, $adventure_id,$g_name, $g_logo, $g_status, $g_color, $g_assign_on_login, $g_group, $g_capacity);
                $b_query = $wpdb->query($sql);
                $updated_g_id = $wpdb->insert_id;
                if($updated_g_id){
                    $data['success']=true;
                    if(!$g_id){
                        $data['location']=get_bloginfo('url')."/new-guild/?adventure_id=$adventure_id&guild_id=$updated_g_id";
                        BR_Activity::instance()->logActivity($adventure_id,'add','guild','',$updated_g_id);
                    }else{
                        BR_Activity::instance()->logActivity($adventure_id,'update','guild','',$g_id);
                    }
                    $data['message'] .= '<h1><strong>'.$g_name.'</strong></h1> <h4><strong>'.__("Guild Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
                }else{
                    $data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert guild","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
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

    // Source: functions/ajax.php — triggerGuild
    public function triggerGuild($p_guild_id=NULL, $p_player_id=NULL, $p_adventure_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $guild_id = $p_guild_id ? $p_guild_id : $_POST['guild_id'];
        $player_id = $p_player_id ? $p_player_id : $_POST['player_id'];
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
        $n = new Notification();
        $t = $wpdb->get_row("SELECT guilds.*, player_guild.player_id, player_adventure.player_guild FROM {$wpdb->prefix}br_guilds guilds
        LEFT JOIN  {$wpdb->prefix}br_player_guild player_guild
        ON guilds.guild_id = player_guild.guild_id AND player_guild.player_id=$player_id AND player_guild.adventure_id=$adventure_id
        LEFT JOIN  {$wpdb->prefix}br_player_adventure player_adventure
        ON player_guild.player_id = player_adventure.player_id AND player_adventure.player_id=$player_id AND player_adventure.adventure_id=$adventure_id
        WHERE guilds.adventure_id=$adventure_id AND guilds.guild_id=$guild_id AND guilds.guild_status='publish'");
        if($t){
            if($t->player_id != $player_id){
                $sql = "INSERT INTO {$wpdb->prefix}br_player_guild (guild_id, player_id, adventure_id) VALUES (%d,%d,%d)";
                $sql = $wpdb->prepare ($sql,$guild_id,$player_id, $adventure_id);
                $wpdb->query($sql);

                $data['success'] = true;
                $data['message'] = $n->pop( __('Player Assigned to Guild!','bluerabbit'),'green','guild');
                $data['just_notify'] =true;
                $data['action'] = 'assign';
                BR_Activity::instance()->logActivity($adventure_id,'enroll','guild',"",$player_id, $guild_id);
            }else{
                $sql = "DELETE FROM {$wpdb->prefix}br_player_guild WHERE guild_id=%d AND player_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$guild_id,$player_id, $adventure_id);
                $wpdb->query($sql);

                if($t->player_guild == $guild_id){
                    $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=%d WHERE player_id=%d AND adventure_id=%d";
                    $sql = $wpdb->prepare ($sql,0,$player_id, $adventure_id);
                    $wpdb->query($sql);
                }

                $data['success'] = true;
                $data['message'] = $n->pop( __('Player Removed from Guild!','bluerabbit'),'red','cancel');
                $data['just_notify'] =true;
                $data['action'] = 'remove';
                BR_Activity::instance()->logActivity($adventure_id,'removed','guild',"",$player_id, $guild_id);
            }
        }else{
            $data['success'] = false;
            $data['message'].= '<span class="icon icon-cancel red-400 icon-lg"></span><br>';
            $data['message'].= '<h3><strong>'.__("Guild doesn't exist!",'bluerabbit').'</strong></h3>';
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — bulkAssignGuild
    public function bulkAssignGuild(){
        global $wpdb;
        $data = ['success' => false];
        $n = new Notification();

        if (!check_ajax_referer('br_update_guild_nonce', 'nonce', false)) {
            $data['message'] = $n->pop(__('Invalid request','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $guild_id     = intval($_POST['guild_id']);
        $adventure_id = intval($_POST['adventure_id']);
        $raw_emails   = isset($_POST['emails']) ? (array)$_POST['emails'] : [];

        if (!$guild_id || !$adventure_id || empty($raw_emails)) {
            $data['message'] = $n->pop(__('Missing data','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $guild = $wpdb->get_row($wpdb->prepare(
            "SELECT guild_id FROM {$wpdb->prefix}br_guilds WHERE guild_id=%d AND adventure_id=%d AND guild_status='publish'",
            $guild_id, $adventure_id
        ));
        if (!$guild) {
            $data['message'] = $n->pop(__('Guild not found','bluerabbit'), 'red', 'cancel');
            echo json_encode($data); die();
        }

        $assigned     = 0;
        $already_in   = 0;
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
                $adventure_id, $email
            ));

            if (!$player) { $not_found++; continue; }

            // Already in this exact guild — keep them, do nothing
            $in_guild = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_guild WHERE player_id=%d AND guild_id=%d",
                $player->player_id, $guild_id
            ));

            if ($in_guild) { $already_in++; continue; }

            // Assign
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}br_player_guild (guild_id, player_id, adventure_id) VALUES (%d, %d, %d)",
                $guild_id, $player->player_id, $adventure_id
            ));
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=%d WHERE player_id=%d AND adventure_id=%d",
                $guild_id, $player->player_id, $adventure_id
            ));

            $assigned++;
            $assigned_ids[] = $player->player_id;
            BR_Activity::instance()->logActivity($adventure_id, 'enroll', 'guild', '', $player->player_id, $guild_id);
        }

        $msg = sprintf(
            __('%d assigned, %d already in guild, %d not found / not enrolled','bluerabbit'),
            $assigned, $already_in, $not_found
        );
        $data['success']      = true;
        $data['assigned_ids'] = $assigned_ids;
        $data['just_notify']  = true;
        $data['message']      = $n->pop($msg, $assigned > 0 ? 'green' : 'orange', 'guild');
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — assignGuild
    public function assignGuild($p_player_id="", $p_adventure_id=""){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;

        $player_id = $p_player_id ? $p_player_id : $_POST['player_id'];
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];

        $has_guild = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$player_id AND adventure_id=$adventure_id");
        if(!$has_guild->player_guild){
            // No Guild Assigned
            $guilds = $wpdb->get_results("SELECT

            guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity

            FROM {$wpdb->prefix}br_guilds guilds

            LEFT JOIN {$wpdb->prefix}br_player_guild guild_players
            ON guilds.guild_id=guild_players.guild_id

            WHERE guilds.adventure_id=$adventure_id AND guilds.guild_status='publish' AND guilds.assign_on_login=1
            GROUP BY guilds.guild_id ORDER BY guild_current_capacity ASC, guilds.guild_id ASC
            ");
            $guilds_data = print_r($guilds,true);
            //return $guilds_data;
            if($guilds){
                $the_guild_id = $guilds[0]->guild_id;
                $sql = "INSERT INTO {$wpdb->prefix}br_player_guild (guild_id, player_id, adventure_id) VALUES (%d,%d,%d); ";
                $sql = $wpdb->prepare ($sql,$the_guild_id,$player_id, $adventure_id);
                $wpdb->query($sql);
                $last_query = print_r($wpdb->last_query,true);
                $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=%d WHERE player_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$the_guild_id,$player_id, $adventure_id);
                $wpdb->query($sql);
                $last_query .= print_r($wpdb->last_query,true);

                return print_r($last_query);
                BR_Activity::instance()->logActivity($adventure_id,'assigned','guild',"",$player_id, $the_guild_id);
            }else{

            }
        }else{
            return "player has_guild->player_guild = True";
        }

    }

    // Source: functions/ajax.php — loadGuildCard
    public function loadGuildCard(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $guild_id = $_POST['guild_id'];
        $g = $wpdb->get_row("SELECT
            guild.*, player.player_id, player.guild_enroll_date FROM {$wpdb->prefix}br_guilds guild
            LEFT JOIN {$wpdb->prefix}br_player_guild player
            ON player.guild_id = guild.guild_id
            WHERE guild.guild_id=$guild_id
        ");

        if($g){
            $adventure = $wpdb->get_row("SELECT a.*, c.* FROM {$wpdb->prefix}br_adventures a
            JOIN {$wpdb->prefix}br_player_adventure c
            ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
            WHERE a.adventure_id=$g->adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
            $isGM = false;
            if($adventure->adventure_owner == $current_user->ID){
                $isGM = true;
                $isOwner = true;
            }elseif($adventure->player_adventure_role == 'gm'){
                $isGM = true;
            }elseif($adventure->player_adventure_role == 'npc'){
                $isNPC = true;
            }
            $isAdmin = $roles[0]=='administrator' ? true : false;
            $guild_players = $wpdb->get_results("SELECT
                a.guild_id, a.guild_name,a.guild_logo, a.guild_color,c.player_hexad, c.player_hexad_slug, c.player_id, c.player_display_name, c.player_picture, d.achievement_name, e.player_xp, e.player_bloo, e.player_adventure_role
                FROM {$wpdb->prefix}br_guilds a
                JOIN {$wpdb->prefix}br_player_guild b
                ON a.guild_id = b.guild_id AND a.adventure_id=b.adventure_id
                JOIN {$wpdb->prefix}br_players c
                ON b.player_id = c.player_id
                JOIN {$wpdb->prefix}br_player_adventure e
                ON b.player_id = e.player_id  AND e.player_adventure_role = 'player'
                LEFT JOIN {$wpdb->prefix}br_achievements d
                ON e.achievement_id = d.achievement_id
                WHERE a.adventure_id=$g->adventure_id AND a.guild_status='publish' AND a.guild_id=$g->guild_id GROUP BY c.player_id
            ");
            $guild_xp = $guild_bloo =0;
            foreach($guild_players as $gp){
                $guild_xp += $gp->player_xp;
                $guild_bloo += $gp->player_bloo;
            }
            $update = $wpdb->query("UPDATE {$wpdb->prefix}br_guilds SET guild_xp=$guild_xp, guild_bloo=$guild_bloo WHERE guild_id=$g->guild_id");

            $theFile = (get_template_directory()."/card-guild.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            $data['message'] = "<h1>".__("Guild doesn't exist","bluerabbit")."</h1>";;
            echo json_encode($data);
        }
        die();
    }

    // Source: functions/ajax.php — getGuilds
    public function getGuilds($adventure_id){
        global $wpdb; $current_user = wp_get_current_user();
        $qry = $wpdb->get_results(" SELECT
            guilds.*, COUNT(guild_players.player_id) AS guild_current_capacity, guilds.guild_id

            FROM {$wpdb->prefix}br_guilds guilds

            LEFT JOIN {$wpdb->prefix}br_player_guild guild_players
            ON guilds.guild_id=guild_players.guild_id

            WHERE guilds.adventure_id=$adventure_id
            GROUP BY guilds.guild_id ORDER BY guilds.guild_id ASC


        ");
        $result = array();
        foreach($qry as $o){
            if($o->guild_status == 'trash'){
                $result['trash'][]=$o;
            }elseif($o->guild_status == 'draft'){
                $result['draft'][]=$o;
            }elseif($o->guild_status == 'publish'){
                $result['publish'][]=$o;
            }
        }
        return $result;
    }

    // Source: functions/ajax.php — getAllGuilds
    public function getAllGuilds($adventure_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds a
        WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' GROUP BY a.guild_id");
        return $result;
    }

    // Source: functions/ajax.php — getMyGuilds
    public function getMyGuilds($adventure_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = $wpdb->get_results("SELECT a.* FROM {$wpdb->prefix}br_guilds a
        JOIN {$wpdb->prefix}br_player_guild b
        ON a.guild_id=b.guild_id AND b.player_id=$current_user->ID
        WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' AND b.player_id=$current_user->ID");
        return $result;
    }

    // Source: functions/ajax.php — getMyGuild
    public function getMyGuild($adventure_id, $guild_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_guilds a
        JOIN {$wpdb->prefix}br_player_guild b
        ON a.guild_id=b.guild_id AND b.player_id=$current_user->ID
        WHERE a.adventure_id=$adventure_id AND a.guild_status='publish' AND b.player_id=$current_user->ID AND a.guild_id=$guild_id");
        return $result;
    }

    // Source: functions/adventure-management.php — setGuild
    public function setGuild(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $guild_id = $_POST['guild_id'];
        $nonce = $_POST['nonce'];
        $id = $_POST['id'];
        $adventure_id = $_POST['adventure_id'];
        $type = $_POST['type'];
        if(wp_verify_nonce($nonce, 'guild_nonce')){
            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET guild_id=%d  WHERE quest_id=%d AND adventure_id=%d";
            }elseif($type == 'item'){
                $sql = "UPDATE {$wpdb->prefix}br_items SET guild_id=%d WHERE item_id=%d AND adventure_id=%d AND item_type='consumable'";
            }elseif($type == 'session'){
                $sql = "UPDATE {$wpdb->prefix}br_sessions SET guild_id=%d WHERE session_id=%d AND adventure_id=%d";
            }
            $sql = $wpdb->prepare ($sql,$guild_id,$id,$adventure_id);
            $wpdb->query($sql);
            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","guild","$type",$id);
            $notification = new Notification();
            $msg_content = __('Guild updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','guild');
            $data['just_notify'] =true;
            $data['guild_nonce'] = wp_create_nonce('guild_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/adventure-management.php — setGuildGroup
    public function setGuildGroup(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $guild_group = stripslashes_deep($_POST['guild_group']);
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'guild_group_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_group=%s WHERE guild_id=%d AND adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$guild_group,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","guild-group","",$id);
            $notification = new Notification();
            $msg_content = __('Guild Group updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'light-green','guild');
            $data['just_notify'] =true;
            $data['new_title_nonce'] = wp_create_nonce('title_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/adventure-management.php — setGuildCapacity
    public function setGuildCapacity(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $guild_capacity = stripslashes_deep($_POST['guild_capacity']);
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'guild_capacity_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_capacity=%d WHERE guild_id=%d AND adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$guild_capacity,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","guild-capacity","",$id);
            $notification = new Notification();
            $msg_content = __('Guild Capacity updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'orange','guild');
            $data['just_notify'] =true;
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }
}

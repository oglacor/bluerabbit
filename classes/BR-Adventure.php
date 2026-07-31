<?php
class BR_Adventure {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // From functions/ajax.php
    public function updateAdventure(){
        global $wpdb;
        $current_user = wp_get_current_user();

        $user_plan = BR_Config::instance()->getUserPlan($current_user->ID);
        $f_role = $user_plan ? $user_plan['plan_key'] : 'basic';
        $features = BR_Config::instance()->getFeatures($f_role);
        $playerData = BR_Player::instance()->getPlayerData($current_user->ID);
        $config = BR_Config::instance()->getSysConfig();
        $myAdventures = $wpdb->get_col("SELECT adventure_id FROM {$wpdb->prefix}br_adventures WHERE adventure_owner=$current_user->ID");
        $max_adv_limit = isset($features['max_adventures'][$f_role]) ? intval($features['max_adventures'][$f_role]) : 0;
        if($max_adv_limit > 0 && count($myAdventures) >= $max_adv_limit){
            $add_adventure = false;
        }else{
            $add_adventure = true;
        }

        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_adventure_nonce')) {


            $adventure_data = $_POST['adventure_data'];
            $adventure_id = intval($adventure_data['adventure_id']);
            $old_adventure = $adventure_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d", $adventure_id)) : null;
            if($old_adventure){ $add_adventure = true; }
            if($add_adventure){
                $adventure_owner = $adventure_data['adventure_owner'];
                $adventure_badge = $adventure_data['adventure_badge'];
                $adventure_logo = $adventure_data['adventure_logo'];
                $adventure_certificate_signature = $adventure_data['adventure_certificate_signature'];
                $adventure_gmt = $adventure_data['adventure_gmt'];
                $adventure_title = stripslashes_deep($adventure_data['adventure_title']);
                $adventure_xp_label = $adventure_data['adventure_xp_label'];
                $adventure_bloo_label = $adventure_data['adventure_bloo_label'];
                $adventure_ep_label = $adventure_data['adventure_ep_label'];
                $adventure_xp_long_label = $adventure_data['adventure_xp_long_label'];
                $adventure_bloo_long_label = $adventure_data['adventure_bloo_long_label'];
                $adventure_ep_long_label = $adventure_data['adventure_ep_long_label'];
                $adventure_grade_scale = $adventure_data['adventure_grade_scale'] ?? 'none';
                $adventure_type = $adventure_data['adventure_type'] ?? 'normal';
                $adventure_progression_type = $adventure_data['adventure_progression_type'] ?? 'before';
                $adventure_privacy = $adventure_data['adventure_privacy'] ?? '';
                $adventure_status = $adventure_data['adventure_status'] ?? 'publish';
                $adventure_instructions = stripslashes_deep($adventure_data['adventure_instructions'] ?? '');
                $adventure_nickname = $adventure_data['adventure_nickname'] ?? '';
                $adventure_level_up_array = isset($adventure_data['adventure_level_up_array']) ? serialize($adventure_data['adventure_level_up_array']) : '';
                $adventure_color = $adventure_data['adventure_color'] ?? '';
                $adventure_hide_schedule = $adventure_data['adventure_hide_schedule'] ?? 'no';
                $adventure_hide_quests = $adventure_data['adventure_hide_quests'] ?? '';
                $adventure_has_guilds = $adventure_data['adventure_has_guilds'] ?? 0;
                $unenrolled = $adventure_data['unenrolled'] ?? [];
                $adventure_ranks = $adventure_data['adventure_ranks'] ?? [];
                $adventure_settings = $adventure_data['adventure_settings'] ?? [];

                if ($adventure_gmt && $adventure_gmt !== '0') { date_default_timezone_set($adventure_gmt); }
                $today = date('Y-m-d h:i:s');
                $adventure_date_modified = date("Y-m-d H:i:s");
                $adventure_start_date = !empty($adventure_data['adventure_start_date']) ? date('Y-m-d H:i:s', strtotime($adventure_data['adventure_start_date'])) : null;
                $adventure_end_date = !empty($adventure_data['adventure_end_date']) ? date('Y-m-d H:i:s', strtotime($adventure_data['adventure_end_date'])) : null;

                if(!$adventure_title){
                    $errors[] = __("The adventure name can't be empty","bluerabbit");
                }
                if($adventure_progression_type == 'after' && $adventure_grade_scale == 'none'){
                    $errors[] = __("You can't assign rewards after grading if no grading scale is set","bluerabbit");
                }
                if(!$old_adventure || !$old_adventure->adventure_code){
                    $first_str = BR_Utils::instance()->random_str(12,'1234567890abcdef');
                    $code_string = $first_str.$current_user->ID;
                    $adventure_code = str_shuffle($code_string);
                }else{
                    $adventure_code = $old_adventure->adventure_code;
                }
                if(!$old_adventure || !$old_adventure->adventure_topic_id){
                    $notification_topic = BR_Utils::instance()->random_str(12,'1234567890abcdef');
                    $adventure_topic_id = "topicID".str_shuffle($notification_topic);
                }else{
                    $adventure_topic_id = $old_adventure->adventure_topic_id;
                }

                if(!$old_adventure){
                    $adventure_settings = $features;
                }

                $sql = "INSERT INTO {$wpdb->prefix}br_adventures ( adventure_id, adventure_owner, adventure_date_modified, adventure_badge, adventure_gmt, adventure_title, adventure_xp_label, adventure_bloo_label, adventure_ep_label, adventure_xp_long_label, adventure_bloo_long_label, adventure_ep_long_label, adventure_grade_scale, adventure_progression_type, adventure_privacy, adventure_status, adventure_instructions, adventure_nickname, adventure_code, adventure_color, adventure_start_date, adventure_end_date, adventure_hide_quests, adventure_topic_id, adventure_hide_schedule, adventure_has_guilds, adventure_type, adventure_certificate_signature, adventure_logo)
                VALUES (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                adventure_owner=%d, adventure_date_modified=%s, adventure_badge=%s, adventure_gmt=%s, adventure_title=%s, adventure_xp_label=%s, adventure_bloo_label=%s, adventure_ep_label=%s, adventure_xp_long_label=%s, adventure_bloo_long_label=%s, adventure_ep_long_label=%s, adventure_grade_scale=%s, adventure_progression_type=%s, adventure_privacy=%s, adventure_status=%s, adventure_instructions=%s, adventure_nickname=%s, adventure_code=%s, adventure_color=%s, adventure_start_date=%s, adventure_end_date=%s, adventure_hide_quests=%s, adventure_topic_id=%s, adventure_hide_schedule=%s, adventure_has_guilds=%s, adventure_type=%s, adventure_certificate_signature=%s, adventure_logo=%s";
                $sql = $wpdb->prepare($sql,
                $adventure_id, $adventure_owner, $adventure_date_modified, $adventure_badge, $adventure_gmt, $adventure_title, $adventure_xp_label, $adventure_bloo_label, $adventure_ep_label, $adventure_xp_long_label, $adventure_bloo_long_label, $adventure_ep_long_label, $adventure_grade_scale, $adventure_progression_type, $adventure_privacy, $adventure_status, $adventure_instructions, $adventure_nickname, $adventure_code, $adventure_color, $adventure_start_date, $adventure_end_date, $adventure_hide_quests, $adventure_topic_id, $adventure_hide_schedule, $adventure_has_guilds, $adventure_type, $adventure_certificate_signature, $adventure_logo,
                $adventure_owner, $adventure_date_modified, $adventure_badge, $adventure_gmt, $adventure_title, $adventure_xp_label, $adventure_bloo_label, $adventure_ep_label, $adventure_xp_long_label, $adventure_bloo_long_label, $adventure_ep_long_label, $adventure_grade_scale, $adventure_progression_type, $adventure_privacy, $adventure_status, $adventure_instructions, $adventure_nickname, $adventure_code, $adventure_color, $adventure_start_date, $adventure_end_date, $adventure_hide_quests, $adventure_topic_id, $adventure_hide_schedule, $adventure_has_guilds, $adventure_type, $adventure_certificate_signature, $adventure_logo);

                if(!$errors){
                    $wpdb->query($sql); $the_just_updated_id = $wpdb->insert_id;
                    if($the_just_updated_id){
                        if($adventure_id){
                            $ranksDELETE = "DELETE FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=%d";
                            $delete =$wpdb->query( $wpdb->prepare($ranksDELETE, $adventure_id));
                            if($adventure_ranks){
                                $ranks_ph = array();
                                $ranks_values = array();
                                $ranksSQL = "INSERT INTO {$wpdb->prefix}br_adventure_ranks (`adventure_id`, `rank_level`, `achievement_id`, `condition_type`)  VALUES";
                                foreach($adventure_ranks as $r){
                                    $message = stripslashes_deep($r['message']);
                                    $condition_type = array_key_exists($r['condition_type'] ?? '', BR_Conditions::CONDITION_TYPES) ? $r['condition_type'] : 'level';
                                    array_push($ranks_values, $adventure_id, $r['level'], $r['achievement'], $condition_type);
                                    $ranks_ph[] = "(%d, %d, %d, %s)";
                                }
                                $ranksSQL .= implode(', ', $ranks_ph);
                                $ranks_insert =$wpdb->query( $wpdb->prepare("$ranksSQL ", $ranks_values));
                            }
                            $data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Updated!","bluerabbit").'</strong></h4>';
                        }else{
                            $adventure_id = $wpdb->insert_id;
                            $data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Adventure Created!","bluerabbit").'</strong></h4>';
                        }

                        $sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_role) VALUES (%d,%d,%s)
                        ON DUPLICATE KEY UPDATE player_adventure_role=%s, player_adventure_status='%s'";
                        $sql = $wpdb->prepare ($sql,$adventure_id,$current_user->ID,'gm', 'gm', 'in');

                        $wpdb->query($sql);
                        $data['success'] = true;
                        BR_Activity::instance()->logActivity($adventure_id,'update','adventure');
                        $data['location'] = get_bloginfo('url').'/new-adventure/?adventure_id='.$adventure_id;

                        $saveSettings = BR_Config::instance()->saveSettingsProcess($adventure_settings, $adventure_id);
                        if($saveSettings){
                            BR_Activity::instance()->logActivity($adventure_id,'adv-settings-updated','adventure');
                            //$data['message'] .= '<h3>'.__("Features saved","bluerabbit").'</h5>';
                        }else{
                            BR_Activity::instance()->logActivity($adventure_id,'adv-settings-not-updated','adventure');
                            //$data['message'] .= '<h3 class="font w100 white-color">'.__("Features unchanged","bluerabbit").'</h5>';
                        }
                    }else{
                        $data['message'] = '<h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Data Base Error. Can't insert/update adventure","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
                    }
                    $data['message'] .= '<h5>'.__("click to close","bluerabbit").'</h5>';


                }else{
                    $data['message'] = '<span class="icon icon-xl icon-warning"></span><h1><strong>'.$adventure_title.'</strong></h1> <h4><strong>'.__("Please Fix the following errors","bluerabbit").'</strong></h4>';
                    foreach($errors as $e){
                        $data['message'].="<h3>$e</h3>";
                    }
                }
            }else{
                $data['message'] .= '<h1><strong>'.__("Max Adventures Reached","bluerabbit").'</strong></h1>';
                $data['message'].= '<h4><strong>'.__("You must delete one of your adventures to create a new one","bluerabbit").'</strong></h4>';
                $data['message'].= '<h5>'.__("click to close","bluerabbit").'</h5>';
            }
        }else{
            $data['message'] .= '<span class="icon icon-cancel red-400 font _70"></span>';
            $data['message'] .= '<h1><strong>'.__("Unauthorized access","bluerabbit").'</strong></h1>';
            $data['location'] = get_bloginfo('url');
        }
        echo json_encode($data);
        die();
    }

    // From functions/ajax.php
    public function loadStory($adv_id=null){
        global $wpdb;
        $data=array();
        $adventure_id = $adv_id ? $adv_id : $_POST['adventure_id'];
        $adventure = $this->getAdventure($adventure_id);
        $notification = new Notification();
        if($adventure->adventure_instructions){
            $theFile = (get_template_directory()."/about-adventure.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }else{
                $msg_content = __("Content doesn't exist",'bluerabbit');
                $data['message'] = $notification->pop($msg_content, 'red','cancel');
                $data['just_notify'] =true;
                echo json_encode($data);
            }
        }
        die();
    }

    // From functions/ajax.php
    public function getAdventure($adventure_id=NULL){
        if($adventure_id){
            global $wpdb; $current_user = wp_get_current_user();

            $roles = $current_user->roles;
            if($roles[0]=='administrator'){
                $isAdmin=true;
            }
            if(is_page('new-adventure')){
                if(isset($isAdmin) && $isAdmin==true){
                    $adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
                    LEFT JOIN {$wpdb->prefix}br_player_adventure c
                    ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
                    WHERE a.adventure_id=$adventure_id ");
                }else{
                    $adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
                    JOIN {$wpdb->prefix}br_player_adventure c
                    ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
                    WHERE a.adventure_id=$adventure_id ");
                }
            }else{
                $adventure = $wpdb->get_row("SELECT a.*, c.player_xp, c.player_bloo, c.player_level, c.player_prev_level, c.player_gpa, c.player_adventure_status, c.player_adventure_role, c.player_date_enrolled, c.player_last_login, c.player_hide_intro, c.player_guild, c.player_ep FROM {$wpdb->prefix}br_adventures a
                JOIN {$wpdb->prefix}br_player_adventure c
                ON a.adventure_id = c.adventure_id AND c.player_id=$current_user->ID
                WHERE a.adventure_id=$adventure_id AND c.player_adventure_status='in' AND a.adventure_status='publish'");
            }
            return $adventure;
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function getAdventureParent($adventure_id){
        global $wpdb;
        $adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
        WHERE a.adventure_id=$adventure_id AND a.adventure_status='publish' AND a.adventure_type='template'");
        if($adventure){
            return $adventure;
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function previewTemplate(){
        global $wpdb; $current_user = wp_get_current_user();
        $adventure_id = isset($_POST['adventure_id']) ? ($_POST['adventure_id']) : "";
        if($adventure_id){
            $theFile = (get_template_directory()."/template-preview.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function createChildAdventure($template_id=0){
        global $wpdb; $current_user = wp_get_current_user();

        $data=array();

        if(isset($_POST['adventure_id'])){
            $adventure_id = $_POST['adventure_id'];
            $adventure = $wpdb->get_row("SELECT a.* FROM {$wpdb->prefix}br_adventures a
            WHERE a.adventure_id=$adventure_id AND a.adventure_status='publish'");
        }else{
            $adventure = NULL;
        }
        $n = new Notification();
        if($adventure){
            $player_data = BR_Player::instance()->getPlayerData($current_user->ID);
            $new_child_title = $adventure->adventure_title." ".__("[new child]","bluerabbit");
            $first_str = BR_Utils::instance()->random_str(12,'1234567890abcdef');
            $code_string = $first_str.$current_user->ID;
            $adventure_code = str_shuffle($code_string);

            $duplication = "
                INSERT INTO {$wpdb->prefix}br_adventures

                (`adventure_owner`, `adventure_badge`, `adventure_logo`, `adventure_gmt`, `adventure_type`, `adventure_title`, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, `adventure_code`, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, `adventure_parent`, `org_id`)

                SELECT

                %d,`adventure_badge`, `adventure_logo`, `adventure_gmt`, 'normal', %s, `adventure_xp_label`, `adventure_bloo_label`, `adventure_ep_label`, `adventure_xp_long_label`, `adventure_bloo_long_label`, `adventure_ep_long_label`, `adventure_grade_scale`, `adventure_progression_type`, `adventure_privacy`, `adventure_status`, `adventure_instructions`, `adventure_nickname`, %s, `adventure_level_up_array`, `adventure_color`, `adventure_hide_quests`, `adventure_hide_schedule`, `adventure_topic_id`, `adventure_has_guilds`, %d, %d

                FROM  {$wpdb->prefix}br_adventures WHERE `adventure_id` = %d;
            ";
            $sql = $wpdb->prepare($duplication, $current_user->ID,$new_child_title, $adventure_code,  $adventure->adventure_id, $player_data->org_id, $adventure->adventure_id);
            $duplicatedAdventureQuery = $wpdb->query($sql);
            //$data['debug'] = print_r($wpdb->last_query,true);
            $newAdvID = $wpdb->insert_id;

            /////////// CLONE THE FEATURES

            $adv_features_duplication = "
                INSERT INTO {$wpdb->prefix}br_settings
                (`setting_id`, `setting_name`, `setting_label`, `setting_value`, `adventure_id`)
                SELECT
                '', `setting_name`, `setting_label`, `setting_value`, %d
                FROM  {$wpdb->prefix}br_settings WHERE `adventure_id` = %d;
            ";
            $adv_features = $wpdb->query($wpdb->prepare($adv_features_duplication, $newAdvID, $adventure->adventure_id ));

            // ADD PLAYERS TO ADVENTURE CURRENT USER AS NPC
            $insertPlayerSQL = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id, player_adventure_role) VALUES (%d,%d,%s)";
            $insertPlayerSQL = $wpdb->query($wpdb->prepare ($insertPlayerSQL, $newAdvID, $current_user->ID, 'npc'));
            $data['success'] = true;
            $data['message'] = "<h1>".__('Adventure created successfully','bluerabbit')."</h1><h4>".__('(click to continue)','bluerabbit')."</h4>";
            $data['location'] = get_bloginfo('url')."/adventure/?adventure_id=".$newAdvID;
        }else{
            $data['new_adventure_from_template'] = false;
            $data['success'] = false;
            $data['message'] = "<h1>".__('Adventure not created','bluerabbit')."</h1><h4>".__('(please refresh and try again or contact admin)','bluerabbit')."</h4>";
            $data['location'] = 'reload';
        }
        echo json_encode($data);
        die();

    }

    // Full independent copy of an adventure - fresh ids everywhere, cross-references
    // remapped to point at the new rows, zero player data (only the acting user gets a
    // fresh gm enrollment). Distinct from createChildAdventure() above, which
    // intentionally does NOT copy content and instead shares the template's rows live.
    public function duplicateAdventure(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $adventure_id = isset($_POST['adventure_id']) ? intval($_POST['adventure_id']) : 0;

        if(!wp_verify_nonce($_POST['nonce'] ?? '', 'br_duplicate_adventure_nonce') || !$adventure_id){
            $data['success'] = false;
            $data['message'] = "<h1>".__('Unauthorized access','bluerabbit')."</h1>";
            echo json_encode($data); die();
        }

        $adventure = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d", $adventure_id));
        $roles = $current_user->roles;
        $isAdmin = isset($roles[0]) && $roles[0] == 'administrator';
        $player_role = $wpdb->get_var($wpdb->prepare("SELECT player_adventure_role FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=%d AND player_id=%d", $adventure_id, $current_user->ID));

        if(!$adventure || !($isAdmin || $adventure->adventure_owner == $current_user->ID || $player_role == 'gm')){
            $data['success'] = false;
            $data['message'] = "<h1>".__('Unauthorized access','bluerabbit')."</h1>";
            echo json_encode($data); die();
        }

        $wpdb->query('START TRANSACTION');
        try {
            $new_adventure_id = $this->duplicateAdventureProcess($adventure, $current_user->ID);
            if(!$new_adventure_id){ throw new Exception('duplicateAdventureProcess returned no id'); }
            $wpdb->query('COMMIT');
            $data['success'] = true;
            $data['message'] = '<h1><strong>'.esc_html($adventure->adventure_title).'</strong></h1><h4><strong>'.__('Adventure duplicated!','bluerabbit').'</strong></h4><h5>'.__('click to continue','bluerabbit').'</h5>';
            $data['location'] = get_bloginfo('url').'/new-adventure/?adventure_id='.$new_adventure_id;
            BR_Activity::instance()->logActivity($new_adventure_id,'duplicate','adventure','',$adventure_id);
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $data['success'] = false;
            $data['message'] = '<h1>'.__('Adventure not duplicated','bluerabbit').'</h1><h4>'.__('Please try again or contact admin','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // The actual clone engine. Runs table-by-table in dependency order, building an
    // old_id => new_id map per table as it inserts (one row at a time via $wpdb->insert -
    // never a multi-row VALUES(...),(...) batch, since that's the only reliable way to
    // read back the *real* new id per source row for remapping, rather than assuming
    // contiguous auto-increment ids). Self-referencing / forward-pointing columns
    // (achievement_path, step_next, button_step_next, button_parent, enc_parent, etc.)
    // are left NULL on first insert and patched in a final pass once every map is complete.
    private function duplicateAdventureProcess($adventure, $owner_id){
        global $wpdb; $p = $wpdb->prefix;
        $adventure_id = $adventure->adventure_id;
        $utils = BR_Utils::instance();

        // 1. New adventure row
        $first_str = $utils->random_str(12,'1234567890abcdef');
        $adventure_code = str_shuffle($first_str.$owner_id);
        $topic_str = $utils->random_str(12,'1234567890abcdef');
        $adventure_topic_id = 'topicID'.str_shuffle($topic_str);

        $new_adv = (array) $adventure;
        unset($new_adv['adventure_id']);
        $new_adv['adventure_title'] = $adventure->adventure_title.' ('.__('Copy','bluerabbit').')';
        $new_adv['adventure_owner'] = $owner_id;
        $new_adv['adventure_code'] = $adventure_code;
        $new_adv['adventure_topic_id'] = $adventure_topic_id;
        $new_adv['adventure_parent'] = null;
        $new_adv['adventure_date_created'] = current_time('mysql');
        $new_adv['adventure_date_modified'] = current_time('mysql');
        if (array_key_exists('adventure_ai_api_key', $new_adv)) { $new_adv['adventure_ai_api_key'] = null; } // never carry a live API key forward

        $wpdb->insert("{$p}br_adventures", $new_adv);
        $new_adventure_id = $wpdb->insert_id;
        if(!$new_adventure_id){ throw new Exception('Could not create duplicated adventure'); }

        // 2. Independent lookup tables (no cross-refs into other content tables)
        $tabi_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_tabis WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->tabi_id;
            $rowArr = (array) $row; unset($rowArr['tabi_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_tabis", $rowArr);
            $tabi_map[$old_id] = $wpdb->insert_id;
        }

        $group_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_branch_groups WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->group_id;
            $rowArr = (array) $row; unset($rowArr['group_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_branch_groups", $rowArr);
            $group_map[$old_id] = $wpdb->insert_id;
        }

        $category_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_item_categories WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->category_id;
            $rowArr = (array) $row; unset($rowArr['category_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_item_categories", $rowArr);
            $category_map[$old_id] = $wpdb->insert_id;
        }

        $guild_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_guilds WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->guild_id;
            $rowArr = (array) $row; unset($rowArr['guild_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['guild_code'] = strtoupper($utils->random_str(8,'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'));
            $rowArr['guild_members'] = 0; // no players in the copy yet
            $wpdb->insert("{$p}br_guilds", $rowArr);
            $guild_map[$old_id] = $wpdb->insert_id;
        }

        $speaker_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_speakers WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->speaker_id;
            $rowArr = (array) $row; unset($rowArr['speaker_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_speakers", $rowArr);
            $speaker_map[$old_id] = $wpdb->insert_id;
        }

        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_sponsors WHERE adventure_id=%d", $adventure_id)) as $row){
            $rowArr = (array) $row; unset($rowArr['sponsor_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_sponsors", $rowArr);
        }

        $blocker_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_blockers WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->blocker_id;
            $rowArr = (array) $row; unset($rowArr['blocker_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $wpdb->insert("{$p}br_blockers", $rowArr);
            $blocker_map[$old_id] = $wpdb->insert_id;
        }

        // 3. Achievements - fresh magic_code + regenerated QR (the QR filename embeds
        // the achievement id, so it can never just be copied), branch_group_id remapped,
        // achievement_path (self-reference, may point forward) deferred to the final pass
        $achievement_map = array();
        $achievement_path_pending = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_achievements WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->achievement_id;
            $rowArr = (array) $row; unset($rowArr['achievement_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['branch_group_id'] = ($row->branch_group_id && isset($group_map[$row->branch_group_id])) ? $group_map[$row->branch_group_id] : null;
            $rowArr['achievement_path'] = null;
            $magic_code = $utils->random_str(20,'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $rowArr['achievement_code'] = $magic_code;
            $rowArr['achievement_qrcode'] = null;
            $wpdb->insert("{$p}br_achievements", $rowArr);
            $new_id = $wpdb->insert_id;
            $achievement_map[$old_id] = $new_id;
            if($row->achievement_path){ $achievement_path_pending[$new_id] = $row->achievement_path; }

            $qr = $utils->createQR(array(
                'filename' => "achievement-$new_id-QR-$magic_code.png",
                'content' => get_bloginfo('url')."/magic-link/?c=$magic_code&adv=$new_adventure_id",
                'logo' => $row->achievement_badge,
            ));
            $wpdb->update("{$p}br_achievements", array('achievement_qrcode' => $qr), array('achievement_id' => $new_id));
        }

        // 4. Items (adventure-scoped, not template-scoped, per functions.php:1938-1941)
        $item_map = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_items WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->item_id;
            $rowArr = (array) $row; unset($rowArr['item_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['achievement_id'] = ($row->achievement_id && isset($achievement_map[$row->achievement_id])) ? $achievement_map[$row->achievement_id] : 0;
            $rowArr['tabi_id'] = ($row->tabi_id && isset($tabi_map[$row->tabi_id])) ? $tabi_map[$row->tabi_id] : 0;
            $rowArr['item_category_id'] = ($row->item_category_id && isset($category_map[$row->item_category_id])) ? $category_map[$row->item_category_id] : null;
            $rowArr['ref_id'] = $this->freshRefId();
            $wpdb->insert("{$p}br_items", $rowArr);
            $new_id = $wpdb->insert_id;
            $item_map[$old_id] = $new_id;
        }

        // 5. Tabi prerequisites
        foreach($wpdb->get_results($wpdb->prepare(
            "SELECT tp.* FROM {$p}br_tabi_prerequisites tp INNER JOIN {$p}br_tabis t ON tp.tabi_id=t.tabi_id WHERE t.adventure_id=%d", $adventure_id
        )) as $row){
            if(!isset($tabi_map[$row->tabi_id]) || !isset($tabi_map[$row->requires_tabi_id])) continue;
            $wpdb->insert("{$p}br_tabi_prerequisites", array(
                'tabi_id' => $tabi_map[$row->tabi_id],
                'requires_tabi_id' => $tabi_map[$row->requires_tabi_id],
            ));
        }

        // 6. Journey assets
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_journey_assets WHERE adventure_id=%d", $adventure_id)) as $row){
            $rowArr = (array) $row; unset($rowArr['asset_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['tabi_id'] = ($row->tabi_id && isset($tabi_map[$row->tabi_id])) ? $tabi_map[$row->tabi_id] : 0;
            $wpdb->insert("{$p}br_journey_assets", $rowArr);
        }

        // 7. Encounters
        $encounter_map = array();
        $enc_parent_pending = array();
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_encounters WHERE adventure_id=%d", $adventure_id)) as $row){
            $old_id = $row->enc_id;
            $rowArr = (array) $row; unset($rowArr['enc_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['achievement_id'] = ($row->achievement_id && isset($achievement_map[$row->achievement_id])) ? $achievement_map[$row->achievement_id] : 0;
            $rowArr['enc_parent'] = null;
            $rowArr['ref_id'] = $this->freshRefId();
            $wpdb->insert("{$p}br_encounters", $rowArr);
            $new_id = $wpdb->insert_id;
            $encounter_map[$old_id] = $new_id;
            if($row->enc_parent){ $enc_parent_pending[$new_id] = $row->enc_parent; }
        }

        // 8. Quests + their children (steps/buttons/questions/answers/objectives),
        // uniformly for every quest_type - blog-post/lore/social quests are ordinary
        // br_quests rows with content in quest_content and simply have no children to loop.
        $quest_map = array();
        $step_map = array();
        $step_next_pending = array();
        $button_map = array();
        $button_step_next_pending = array();
        $button_parent_pending = array();
        $question_map = array();
        $answer_map = array();
        $survey_q_map = array();
        $survey_o_map = array();
        $objective_map = array();
        $objective_blogpost_pending = array();

        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_quests WHERE adventure_id=%d", $adventure_id)) as $q){
            $old_quest_id = $q->quest_id;
            $rowArr = (array) $q; unset($rowArr['quest_id']);
            $rowArr['adventure_id'] = $new_adventure_id;
            $rowArr['tabi_id'] = ($q->tabi_id && isset($tabi_map[$q->tabi_id])) ? $tabi_map[$q->tabi_id] : null;
            $rowArr['achievement_id'] = ($q->achievement_id && isset($achievement_map[$q->achievement_id])) ? $achievement_map[$q->achievement_id] : 0;
            $rowArr['mech_item_reward'] = ($q->mech_item_reward && isset($item_map[$q->mech_item_reward])) ? $item_map[$q->mech_item_reward] : null;
            $rowArr['mech_achievement_reward'] = ($q->mech_achievement_reward && isset($achievement_map[$q->mech_achievement_reward])) ? $achievement_map[$q->mech_achievement_reward] : null;
            $rowArr['quest_qr_token'] = null; // UNIQUE index - regenerates on first real use, same as a brand-new quest
            $wpdb->insert("{$p}br_quests", $rowArr);
            $new_quest_id = $wpdb->insert_id;
            $quest_map[$old_quest_id] = $new_quest_id;

            foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_steps WHERE quest_id=%d", $old_quest_id)) as $s){
                $old_step_id = $s->step_id;
                $sArr = (array) $s; unset($sArr['step_id']);
                $sArr['quest_id'] = $new_quest_id;
                $sArr['adventure_id'] = $new_adventure_id;
                $sArr['step_item'] = ($s->step_item && isset($item_map[$s->step_item])) ? $item_map[$s->step_item] : 0;
                $sArr['step_item_reward'] = ($s->step_item_reward && isset($item_map[$s->step_item_reward])) ? $item_map[$s->step_item_reward] : null;
                $sArr['step_achievement_reward'] = ($s->step_achievement_reward && isset($achievement_map[$s->step_achievement_reward])) ? $achievement_map[$s->step_achievement_reward] : null;
                $sArr['step_branch_group_id'] = ($s->step_branch_group_id && isset($group_map[$s->step_branch_group_id])) ? $group_map[$s->step_branch_group_id] : null;
                $sArr['step_next'] = null;
                $sArr['ref_id'] = $this->freshRefId();
                $wpdb->insert("{$p}br_steps", $sArr);
                $new_step_id = $wpdb->insert_id;
                $step_map[$old_step_id] = $new_step_id;
                if($s->step_next){ $step_next_pending[$new_step_id] = $s->step_next; }
            }

            foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_step_buttons WHERE quest_id=%d", $old_quest_id)) as $b){
                $old_button_id = $b->button_id;
                $bArr = (array) $b; unset($bArr['button_id']);
                $bArr['step_id'] = ($b->step_id && isset($step_map[$b->step_id])) ? $step_map[$b->step_id] : 0;
                $bArr['quest_id'] = $new_quest_id;
                $bArr['adventure_id'] = $new_adventure_id;
                // button_object_id's real target type isn't pinned down anywhere beyond
                // this duplication logic - best-effort: try every plausible map in turn.
                $bArr['button_object_id'] = $this->remapBestEffort($b->button_object_id, array($item_map, $achievement_map, $step_map));
                $bArr['button_step_next'] = null;
                $bArr['button_parent'] = null;
                $bArr['ref_id'] = $this->freshRefId();
                $wpdb->insert("{$p}br_step_buttons", $bArr);
                $new_button_id = $wpdb->insert_id;
                $button_map[$old_button_id] = $new_button_id;
                if($b->button_step_next){ $button_step_next_pending[$new_button_id] = $b->button_step_next; }
                if($b->button_parent){ $button_parent_pending[$new_button_id] = $b->button_parent; }
            }

            if($q->quest_type == 'challenge'){
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_challenge_questions WHERE quest_id=%d", $old_quest_id)) as $qq){
                    $old_q_id = $qq->question_id;
                    $qArr = (array) $qq; unset($qArr['question_id']);
                    $qArr['quest_id'] = $new_quest_id;
                    $qArr['ref_id'] = $this->freshRefId();
                    $wpdb->insert("{$p}br_challenge_questions", $qArr);
                    $new_q_id = $wpdb->insert_id;
                    $question_map[$old_q_id] = $new_q_id;
                }
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_challenge_answers WHERE quest_id=%d", $old_quest_id)) as $aa){
                    $old_a_id = $aa->answer_id;
                    $aArr = (array) $aa; unset($aArr['answer_id']);
                    $aArr['quest_id'] = $new_quest_id;
                    $aArr['question_id'] = isset($question_map[$aa->question_id]) ? $question_map[$aa->question_id] : 0;
                    $aArr['ref_id'] = $this->freshRefId();
                    $wpdb->insert("{$p}br_challenge_answers", $aArr);
                    $new_a_id = $wpdb->insert_id;
                    $answer_map[$old_a_id] = $new_a_id;
                }
            }

            if($q->quest_type == 'survey'){
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_survey_questions WHERE survey_id=%d", $old_quest_id)) as $sq){
                    $old_sq_id = $sq->survey_question_id;
                    $sqArr = (array) $sq; unset($sqArr['survey_question_id']);
                    $sqArr['survey_id'] = $new_quest_id;
                    $sqArr['ref_id'] = $this->freshRefId();
                    $wpdb->insert("{$p}br_survey_questions", $sqArr);
                    $new_sq_id = $wpdb->insert_id;
                    $survey_q_map[$old_sq_id] = $new_sq_id;
                }
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_survey_options WHERE survey_id=%d", $old_quest_id)) as $so){
                    $old_so_id = $so->survey_option_id;
                    $soArr = (array) $so; unset($soArr['survey_option_id']);
                    $soArr['survey_id'] = $new_quest_id;
                    $soArr['survey_question_id'] = isset($survey_q_map[$so->survey_question_id]) ? $survey_q_map[$so->survey_question_id] : 0;
                    $soArr['ref_id'] = $this->freshRefId();
                    $wpdb->insert("{$p}br_survey_options", $soArr);
                    $new_so_id = $wpdb->insert_id;
                    $survey_o_map[$old_so_id] = $new_so_id;
                }
            }

            if($q->quest_type == 'mission'){
                foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_objectives WHERE quest_id=%d", $old_quest_id)) as $ob){
                    $old_ob_id = $ob->objective_id;
                    $obArr = (array) $ob; unset($obArr['objective_id']);
                    $obArr['quest_id'] = $new_quest_id;
                    $obArr['adventure_id'] = $new_adventure_id;
                    $obArr['blog_post_id'] = null; // references a lore/blog-post QUEST row - patched below once quest_map is complete
                    $obArr['ref_id'] = $this->freshRefId();
                    $wpdb->insert("{$p}br_objectives", $obArr);
                    $new_ob_id = $wpdb->insert_id;
                    $objective_map[$old_ob_id] = $new_ob_id;
                    if($ob->blog_post_id){ $objective_blogpost_pending[$new_ob_id] = $ob->blog_post_id; }
                }
            }
        }

        // 8.5 Requirement gates (adventure-wide; quest_id column IS the "which quest is
        // gated" scope for target_type='quest' rows - target_type='tabi' rows use
        // quest_id=0 + target_id instead, per functions.php:1922-1927)
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_reqs WHERE adventure_id=%d", $adventure_id)) as $r){
            $target_type = $r->target_type ?: 'quest';
            if($target_type == 'quest'){
                if(!isset($quest_map[$r->quest_id])) continue;
                $new_quest_id_for_req = $quest_map[$r->quest_id];
                $new_target_id = null;
            } elseif($target_type == 'tabi'){
                if(!isset($tabi_map[$r->target_id])) continue;
                $new_quest_id_for_req = 0;
                $new_target_id = $tabi_map[$r->target_id];
            } else {
                continue; // unrecognized target_type - skip rather than dangle
            }
            $req_object_id = $this->remapReqObject($r->req_type, $r->req_object_id, $quest_map, $achievement_map, $item_map);
            if($r->req_object_id && $req_object_id === null) continue; // referent wasn't cloned - drop the gate rather than point it at garbage
            $wpdb->insert("{$p}br_reqs", array(
                'quest_id' => $new_quest_id_for_req,
                'adventure_id' => $new_adventure_id,
                'req_object_id' => $req_object_id,
                'req_type' => $r->req_type,
                'target_type' => $target_type,
                'target_id' => $new_target_id,
                'ref_id' => $this->freshRefId(),
                'req_value' => $r->req_value,
            ));
        }

        // 9. Conditions (threshold/count gates)
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_conditions WHERE adventure_id=%d", $adventure_id)) as $c){
            $target_map = null;
            switch($c->target_type){
                case 'quest': $target_map = $quest_map; break;
                case 'tabi': $target_map = $tabi_map; break;
                case 'achievement': $target_map = $achievement_map; break;
                case 'item': $target_map = $item_map; break;
                case 'item_category': $target_map = $category_map; break;
            }
            if($target_map === null || !isset($target_map[$c->target_id])) continue;
            $cArr = (array) $c; unset($cArr['condition_id']);
            $cArr['adventure_id'] = $new_adventure_id;
            $cArr['target_id'] = $target_map[$c->target_id];
            // object_id's exact meaning per condition_type isn't fully pinned down - best effort
            $cArr['object_id'] = $this->remapBestEffort($c->object_id, array($quest_map, $achievement_map, $item_map, $tabi_map, $category_map));
            $wpdb->insert("{$p}br_conditions", $cArr);
        }

        // 10. Branch rules
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_branch_rules WHERE adventure_id=%d", $adventure_id)) as $r){
            if(!isset($achievement_map[$r->achievement_id])) continue;
            $target_map = null;
            switch($r->rule_target_type){
                case 'quest': $target_map = $quest_map; break;
                case 'achievement': $target_map = $achievement_map; break;
                case 'item': $target_map = $item_map; break;
                case 'branch_group': $target_map = $group_map; break;
            }
            if($target_map === null || !isset($target_map[$r->rule_target_id])) continue;
            $wpdb->insert("{$p}br_branch_rules", array(
                'achievement_id' => $achievement_map[$r->achievement_id],
                'adventure_id' => $new_adventure_id,
                'rule_action' => $r->rule_action,
                'rule_target_type' => $r->rule_target_type,
                'rule_target_id' => $target_map[$r->rule_target_id],
                'rule_order' => $r->rule_order,
            ));
        }

        // 11. Sessions
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_sessions WHERE adventure_id=%d", $adventure_id)) as $s){
            $sArr = (array) $s; unset($sArr['session_id']);
            $sArr['adventure_id'] = $new_adventure_id;
            $sArr['quest_id'] = ($s->quest_id && isset($quest_map[$s->quest_id])) ? $quest_map[$s->quest_id] : null;
            $sArr['speaker_id'] = ($s->speaker_id && isset($speaker_map[$s->speaker_id])) ? $speaker_map[$s->speaker_id] : null;
            $sArr['achievement_id'] = ($s->achievement_id && isset($achievement_map[$s->achievement_id])) ? $achievement_map[$s->achievement_id] : 0;
            $sArr['guild_id'] = ($s->guild_id && isset($guild_map[$s->guild_id])) ? $guild_map[$s->guild_id] : null;
            if($s->speaker_ids){
                $ids = array_filter(array_map('trim', explode(',', $s->speaker_ids)));
                $mapped = array();
                foreach($ids as $sid){ if(isset($speaker_map[$sid])){ $mapped[] = $speaker_map[$sid]; } }
                $sArr['speaker_ids'] = implode(',', $mapped);
            }
            $wpdb->insert("{$p}br_sessions", $sArr);
        }

        // 12. Adventure ranks
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_adventure_ranks WHERE adventure_id=%d", $adventure_id)) as $rk){
            if(!isset($achievement_map[$rk->achievement_id])) continue;
            $wpdb->insert("{$p}br_adventure_ranks", array(
                'adventure_id' => $new_adventure_id,
                'rank_level' => $rk->rank_level,
                'achievement_id' => $achievement_map[$rk->achievement_id],
                'condition_type' => $rk->condition_type,
            ));
        }

        // 13. Feature settings (same shape createChildAdventure() already uses above)
        foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}br_settings WHERE adventure_id=%d", $adventure_id)) as $st){
            $wpdb->insert("{$p}br_settings", array(
                'setting_name' => $st->setting_name,
                'setting_label' => $st->setting_label,
                'setting_value' => $st->setting_value,
                'adventure_id' => $new_adventure_id,
            ));
        }

        // 14. Org link
        if($adventure->org_id){
            $wpdb->insert("{$p}br_org_adventure", array('org_id' => $adventure->org_id, 'adventure_id' => $new_adventure_id));
        }

        // 15. Self-enroll the acting user as gm - a fresh row, no XP/level/progress carried
        // from the source owner's own enrollment (zero player data, per instruction)
        $wpdb->insert("{$p}br_player_adventure", array(
            'adventure_id' => $new_adventure_id,
            'player_id' => $owner_id,
            'player_adventure_role' => 'gm',
            'player_adventure_status' => 'in',
        ));

        // 16. Final patch pass - every self-referencing / forward-pointing column left
        // NULL above gets remapped now that all maps are complete. Remap if the referent
        // was itself cloned this run, otherwise leave NULL - never a raw copied id.
        $this->patchParentColumn('br_achievements', 'achievement_id', 'achievement_path', $achievement_path_pending, $achievement_map);
        $this->patchParentColumn('br_encounters', 'enc_id', 'enc_parent', $enc_parent_pending, $encounter_map);
        $this->patchParentColumn('br_steps', 'step_id', 'step_next', $step_next_pending, $step_map);
        $this->patchParentColumn('br_step_buttons', 'button_id', 'button_step_next', $button_step_next_pending, $step_map);
        $this->patchParentColumn('br_step_buttons', 'button_id', 'button_parent', $button_parent_pending, $button_map);
        $this->patchParentColumn('br_objectives', 'objective_id', 'blog_post_id', $objective_blogpost_pending, $quest_map);

        // 17. JSON step_settings pass - branch-choice/find-item/backpack-item step types
        // (step-branch-choice.php:3, step-find-item.php:3, step-backpack-item.php:3) fall
        // back to reading item_id/achievement_id/group_id etc. straight out of this JSON
        // blob when the dedicated column is empty, so it has to stay consistent too.
        $json_maps = array(
            'item_id' => $item_map,
            'achievement_id' => $achievement_map,
            'group_id' => $group_map,
            'tabi_id' => $tabi_map,
            'step_id' => $step_map,
            'quest_id' => $quest_map,
        );
        foreach($wpdb->get_results($wpdb->prepare("SELECT step_id, step_settings FROM {$p}br_steps WHERE adventure_id=%d AND step_settings IS NOT NULL AND step_settings != ''", $new_adventure_id)) as $ns){
            $decoded = json_decode($ns->step_settings, true);
            if(!is_array($decoded)) continue;
            $changed = false;
            $decoded = $this->remapSettingsArray($decoded, $json_maps, $changed);
            if($changed){
                $wpdb->update("{$p}br_steps", array('step_settings' => wp_json_encode($decoded)), array('step_id' => $ns->step_id));
            }
        }

        return $new_adventure_id;
    }

    private function freshRefId(){
        return BR_Utils::instance()->random_str(8,'1234567890abcdef');
    }

    // req_type is one of 'quest'/'achievement'/'item' (confirmed exhaustively via every
    // read site: BR-Progression.php, BR-Item.php, BR-Quest.php, BR-Tabi.php)
    private function remapReqObject($type, $id, $quest_map, $achievement_map, $item_map){
        if(!$id) return $id;
        switch($type){
            case 'quest': return isset($quest_map[$id]) ? $quest_map[$id] : null;
            case 'achievement': return isset($achievement_map[$id]) ? $achievement_map[$id] : null;
            case 'item': return isset($item_map[$id]) ? $item_map[$id] : null;
            default: return $id;
        }
    }

    // For columns whose target table isn't pinned down in the codebase - try every
    // plausible map in turn, keep the value verbatim if none of them recognize it.
    private function remapBestEffort($id, $maps){
        if(!$id) return $id;
        foreach($maps as $map){
            if(isset($map[$id])) return $map[$id];
        }
        return $id;
    }

    private function patchParentColumn($table, $id_col, $col, $pending, $map){
        global $wpdb; $p = $wpdb->prefix;
        foreach($pending as $new_row_id => $old_ref_id){
            $new_ref_id = isset($map[$old_ref_id]) ? $map[$old_ref_id] : null;
            $wpdb->update("{$p}$table", array($col => $new_ref_id), array($id_col => $new_row_id));
        }
    }

    private function remapSettingsArray($arr, $json_maps, &$changed){
        foreach($arr as $key => $val){
            if(is_array($val)){
                $arr[$key] = $this->remapSettingsArray($val, $json_maps, $changed);
            } elseif(isset($json_maps[$key]) && $val && isset($json_maps[$key][$val])){
                $arr[$key] = $json_maps[$key][$val];
                $changed = true;
            }
        }
        return $arr;
    }

    // From functions.php
    public function registerAdventureLogin($adventure_id) {
        global $wpdb; $current_user = wp_get_current_user();
        $adventure = $wpdb->get_row("
            SELECT adv.*, player.player_last_login FROM {$wpdb->prefix}br_adventures adv LEFT JOIN
            {$wpdb->prefix}br_player_adventure player ON adv.adventure_id=player.adventure_id AND player.player_id=$current_user->ID
            WHERE adv.adventure_id=$adventure_id
        ");
        $debug = print_r($wpdb->last_query,true);

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        $today_compare = date('Ymd');
        $last_login = $adventure->player_last_login ? date('Ymd', strtotime($adventure->player_last_login)) : 0;
        BR_Activity::instance()->logActivity($adventure_id,'login','adventure');
        if($today_compare > $last_login){
            $sql="UPDATE {$wpdb->prefix}br_player_adventure SET player_last_login=%s WHERE adventure_id=$adventure_id AND player_id=$current_user->ID";
            $registerLogin = $wpdb->query($wpdb->prepare($sql, $today, $adventure_id, $current_user->ID));
            return(true);
        }else{
            return(false);
        }
        die();
    }

    // From functions/adventure-management.php
    public function setXP(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $xp = $_POST['xp'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'xp_nonce')){
            if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_xp=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_xp=%d WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id);
            }elseif($type == 'encounter'){
                $sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_xp=%d WHERE enc_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$xp,$id,$adventure_id);
            }
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","xp","$type",$id);
            $notification = new Notification();
            $msg_content = __('XP updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'blue','star');
            $data['just_notify'] =true;
            $data['new_xp_nonce'] = wp_create_nonce('xp_nonce');
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setEP(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $ep = $_POST['ep'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'ep_nonce')){
            if($type == 'quest' ||$type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_ep=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_ep=%d WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id);
            }elseif($type == 'encounter'){
                $sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_ep=%d WHERE enc_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$ep,$id,$adventure_id);
            }
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","ep","$type",$id);
            $notification = new Notification();
            $msg_content = __('EP updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'teal','activity');
            $data['just_notify'] =true;
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setBLOO(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $bloo = $_POST['bloo'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'bloo_nonce')){
            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_bloo=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id);
            }elseif($type == 'item'){
                $sql = "UPDATE {$wpdb->prefix}br_items SET item_cost=%d WHERE item_id=%d AND adventure_id=%d AND (item_type='consumable' OR item_type='key' OR item_type='tabi-piece' OR item_type='gift-card')";
                $sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_bloo=%d WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id);
            }elseif($type == 'encounter'){
                $sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_bloo=%d WHERE enc_id=%d AND adventure_id=%d";
            }
            $sql = $wpdb->prepare ($sql,$bloo,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","bloo","$type",$id);
            $notification = new Notification();
            $msg_content = __('BLOO updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'light-green','bloo');
            $data['just_notify'] =true;
            $data['new_bloo_nonce'] = wp_create_nonce('bloo_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    public function setValidate(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $validate = $_POST['validate'] ? 1 : 0;
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'validate_nonce')){
            if($type == 'quest'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_validate=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$validate,$id,$adventure_id);
                $wpdb->query($sql);

                $data['success'] = true;
                BR_Activity::instance()->logActivity($adventure_id, "set","validate","$type",$id);
                $notification = new Notification();
                $msg_content = $validate ? __('Validation required before awarding','bluerabbit') : __('Validation no longer required','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green','check');
                $data['just_notify'] =true;
                $data['new_validate_nonce'] = wp_create_nonce('validate_nonce');
            }
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    public function setOptional(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $optional = $_POST['optional'] ? 1 : 0;
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'optional_nonce')){
            if($type == 'quest' || $type == 'challenge' || $type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_optional=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$optional,$id,$adventure_id);
                $wpdb->query($sql);

                $data['success'] = true;
                BR_Activity::instance()->logActivity($adventure_id, "set","optional","$type",$id);
                $notification = new Notification();
                $msg_content = $optional ? __('Marked as Side Quest','bluerabbit') : __('Marked as Required','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'blue','check');
                $data['just_notify'] =true;
                $data['new_optional_nonce'] = wp_create_nonce('optional_nonce');
            }
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setMaxPlayers(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $max = $_POST['max'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'max_players_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_max=%d WHERE achievement_id=%d AND adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$max,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","max_players","achievement",$id);
            $notification = new Notification();
            $msg_content = __('Max Players updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'light-green','player');
            $data['just_notify'] =true;
            $data['new_max_players_nonce'] = wp_create_nonce('max_players_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function updateAdventureTitle(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adv_title = stripslashes_deep($_POST['adv_title']);
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'br_update_adv_title_nonce'.$adventure_id)){
            $sql = "UPDATE {$wpdb->prefix}br_adventures SET adventure_title=%s WHERE adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$adv_title,$adventure_id);
            $wpdb->query($sql);
            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "update","title","adventure");
            $msg_content = __('Adventure title updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','check');
            $data['just_notify'] =true;
        }else{
            $msg_content = __("Nonce!","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setTitle(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $title = stripslashes_deep($_POST['title']);
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'title_nonce')){
            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey' || $type == 'blog-post' || $type == 'lore'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET quest_title=%s WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'item'){
                $sql = "UPDATE {$wpdb->prefix}br_items SET item_name=%s WHERE item_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_name=%s WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'guild'){
                $sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_name=%s WHERE guild_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'encounter'){
                $sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_question=%s WHERE enc_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'session'){
                $sql = "UPDATE {$wpdb->prefix}br_sessions SET session_title=%s WHERE session_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }elseif($type == 'tabi'){
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_name=%s WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$title,$id,$adventure_id);
            }
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","title","$type",$id);
            $notification = new Notification();
            $msg_content = __('Title updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','check');
            $data['just_notify'] =true;
            $data['new_title_nonce'] = wp_create_nonce('title_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setBadge(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $badge = $_POST['badge'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'title_nonce')){
            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_badge=%s WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }elseif($type == 'item'){
                $sql = "UPDATE {$wpdb->prefix}br_items SET item_badge=%s WHERE item_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }elseif($type == 'tabi'){
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_background=%s WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_badge=%s WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }elseif($type == 'guild'){
                $sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_logo=%s WHERE guild_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }elseif($type == 'speaker'){
                $sql = "UPDATE {$wpdb->prefix}br_speakers SET speaker_picture=%s WHERE speaker_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$badge, $id, $adventure_id);
            }
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","badge","$type",$id);
            $notification = new Notification();
            $msg_content = __('Badge updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'purple','check');
            $data['just_notify'] =true;
            $data['new_title_nonce'] = wp_create_nonce('title_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setColor(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $color = $_POST['color'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'title_nonce')){
            if($type == 'quest'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET quest_color=%s WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_color=%s WHERE achievement_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
            }elseif($type == 'guild'){
                $sql = "UPDATE {$wpdb->prefix}br_guilds SET guild_color=%s WHERE guild_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
            }elseif($type == 'tabi'){
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_color=%s WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql ,$color, $id, $adventure_id);
            }
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","color","$type",$id);
            $notification = new Notification();
            $msg_content = __('Color updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'deep-purple','check');
            $data['just_notify'] =true;
            $data['new_title_nonce'] = wp_create_nonce('title_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    // $category is now a br_item_categories.category_id (0 = no category), not a
    // free-text color string - the quick-edit dropdown in manage-items.php lists real
    // categories now (see BR_Item::getCategories).
    public function setCategory(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $category_id = (int) $_POST['category'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'item_cat_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_items SET item_category_id=%d WHERE item_id=%d AND adventure_id=%d AND (item_type='consumable' OR item_type='gift-card')";
            $sql = $wpdb->prepare ($sql, $category_id, $id, $adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","item-category","",$id);
            $notification = new Notification();
            $msg_content = __('Item Category updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'pink','list');
            $data['just_notify'] =true;
            $data['new_title_nonce'] = wp_create_nonce('title_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setLevel(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $level = $_POST['level'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'level_nonce')){
            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'social' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_level=%d WHERE quest_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
            }elseif($type == 'encounter'){
                $sql = "UPDATE {$wpdb->prefix}br_encounters SET enc_level=%d WHERE enc_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
            }elseif($type == 'item'){
                $sql = "UPDATE {$wpdb->prefix}br_items SET item_level=%d WHERE item_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
            }elseif($type == 'tabi'){
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_level=%d WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$level,$id,$adventure_id);
            }
            $wpdb->query($sql);
            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","level","$type",$id);

            $notification = new Notification();
            $msg_content = __('Level updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'deep-purple','level');
            $data['just_notify'] =true;
            $data['new_level_nonce'] = wp_create_nonce('level_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setDisplayStyle(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $style = $_POST['style'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';

        if(wp_verify_nonce($nonce, 'display_style_nonce')){
            if($type == 'quest' || $type == 'blog-post' ||$type == 'challenge' ||$type == 'mission' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET quest_style=%s WHERE quest_id=%d AND adventure_id=%d";
            }
            $sql = $wpdb->prepare ($sql,$style,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","display-style","$type",$id);
            $notification = new Notification();
            $msg_content = __('Display Style updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'teal','calendar');
            $data['just_notify'] =true;
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setStartDate(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $start_date = $_POST['start_date'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';

        if(wp_verify_nonce($nonce, 'start_date_nonce')){
            if($start_date){
                $start_date=date('Y-m-d H:i:s',strtotime($start_date));
            }else{
                $start_date='0000-00-00 00:00:00';
            }
            if($type == 'quest' || $type == 'blog-post' ||$type == 'challenge' ||$type == 'mission' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_start_date=%s WHERE quest_id=%d AND adventure_id=%d";
            }elseif($type == 'session'){
                $sql = "UPDATE {$wpdb->prefix}br_sessions SET session_start=%s WHERE session_id=%d AND adventure_id=%d";
            }
            $sql = $wpdb->prepare ($sql,$start_date,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","start-date","$type",$id);
            $notification = new Notification();
            $msg_content = __('Start date updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'cyan','calendar');
            $data['just_notify'] =true;
            $data['new_start_date_nonce'] = wp_create_nonce('start_date_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setDeadline(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $deadline = $_POST['deadline'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'deadline_nonce')){

            if($deadline){
                $deadline=date('Y-m-d H:i:s',strtotime($deadline));
            }else{
                $deadline='0000-00-00 00:00:00';
            }

            if($type == 'quest' || $type == 'challenge' ||$type == 'mission' || $type == 'survey'){
                $sql = "UPDATE {$wpdb->prefix}br_quests SET mech_deadline=%s WHERE quest_id=%d AND adventure_id=%d";
            }elseif($type == 'achievement'){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_deadline=%s WHERE achievement_id=%d AND adventure_id=%d";
            }elseif($type == 'session'){
                $sql = "UPDATE {$wpdb->prefix}br_sessions SET session_end=%s WHERE session_id=%d AND adventure_id=%d";
            }
            $sql = $wpdb->prepare ($sql,$deadline,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","deadline","$type",$id);
            $notification = new Notification();
            $msg_content = __('Deadline updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','deadline');
            $data['just_notify'] =true;
            $data['new_deadline_nonce'] = wp_create_nonce('deadline_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setMagicCode(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $code = strtolower($_POST['code']);
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $reload = 'reload';
        if(wp_verify_nonce($nonce, 'magic_code_nonce')){
                $sql = "UPDATE {$wpdb->prefix}br_achievements SET achievement_code=%s WHERE achievement_id=%d AND adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$code,$id,$adventure_id);
            $wpdb->query($sql);

            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","magic-code","",$id);
            $notification = new Notification();
            $msg_content = __('Magic Code updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'purple','magic');
            $data['just_notify'] =true;
            $data['new_magic_code_nonce'] = wp_create_nonce('magic_code_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

}

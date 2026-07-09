<?php
class BR_Config {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function getSetting($name,$adv_id){
        global $wpdb;
        $data = array();
        $setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_settings WHERE setting_name='$name' AND adventure_id=$adv_id");
        if($setting){
            return $setting->setting_value;
        }else{
            return false;
        }
    }

    public function getSettings($adv_id){
        global $wpdb;
        $settings = array();
        $settings_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_settings WHERE adventure_id=$adv_id");
        foreach($settings_query as $key=>$sq){
            $settings[$sq->setting_name]['id'] = $sq->setting_id;
            $settings[$sq->setting_name]['label'] = $sq->setting_label;
            $settings[$sq->setting_name]['value'] = $sq->setting_value;
        }
        return $settings;
    }

    public function getFeatures($plan_key=NULL){
        global $wpdb;
        $features = array();

        $has_plans_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}br_plans'");
        if(!$has_plans_table){
            if($plan_key){
                $the_role = 'feature_access_'.$plan_key;
                $features_query = $wpdb->get_results("SELECT feature_id, feature_name, feature_label, feature_desc, feature_type, $the_role FROM {$wpdb->prefix}br_features WHERE $the_role != '' ");
                foreach($features_query as $key=>$f){
                    $features[$f->feature_name]['id'] = $f->feature_id;
                    $features[$f->feature_name]['label'] = $f->feature_label;
                    $features[$f->feature_name]['desc'] = $f->feature_desc;
                    $features[$f->feature_name]['type'] = $f->feature_type;
                    $features[$f->feature_name][$plan_key] = $f->$the_role;
                }
            }else{
                $features_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_features");
                foreach($features_query as $key=>$f){
                    $features[$f->feature_name]['id'] = $f->feature_id;
                    $features[$f->feature_name]['label'] = $f->feature_label;
                    $features[$f->feature_name]['desc'] = $f->feature_desc;
                    $features[$f->feature_name]['type'] = $f->feature_type;
                    $features[$f->feature_name]['free'] = $f->feature_access_free;
                    $features[$f->feature_name]['pro'] = $f->feature_access_pro;
                    $features[$f->feature_name]['admin'] = $f->feature_access_admin;
                    $features[$f->feature_name]['god'] = $f->feature_access_god;
                }
            }
            return !empty($features) ? $features : false;
        }

        if($plan_key){
            $rows = $wpdb->get_results($wpdb->prepare("
                SELECT f.feature_id, f.feature_name, f.feature_label,
                       f.feature_desc, f.feature_type, pf.feature_value
                FROM {$wpdb->prefix}br_features f
                JOIN {$wpdb->prefix}br_plan_features pf ON pf.feature_id = f.feature_id
                JOIN {$wpdb->prefix}br_plans p ON p.plan_id = pf.plan_id
                WHERE p.plan_key = %s AND p.plan_status = 'active'
            ", $plan_key));
            foreach($rows as $f){
                $features[$f->feature_name] = array(
                    'id' => $f->feature_id,
                    'label' => $f->feature_label,
                    'desc' => $f->feature_desc,
                    'type' => $f->feature_type,
                    $plan_key => $f->feature_value,
                );
            }
        }else{
            $plans = $wpdb->get_results("SELECT plan_id, plan_key FROM {$wpdb->prefix}br_plans WHERE plan_status='active'");
            $features_base = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_features");
            foreach($features_base as $f){
                $features[$f->feature_name] = array(
                    'id' => $f->feature_id,
                    'label' => $f->feature_label,
                    'desc' => $f->feature_desc,
                    'type' => $f->feature_type,
                );
            }
            foreach($plans as $p){
                $vals = $wpdb->get_results($wpdb->prepare("
                    SELECT f.feature_name, pf.feature_value
                    FROM {$wpdb->prefix}br_plan_features pf
                    JOIN {$wpdb->prefix}br_features f ON f.feature_id = pf.feature_id
                    WHERE pf.plan_id = %d
                ", $p->plan_id));
                foreach($vals as $v){
                    if(isset($features[$v->feature_name])){
                        $features[$v->feature_name][$p->plan_key] = $v->feature_value;
                    }
                }
            }
        }
        return !empty($features) ? $features : false;
    }

    public function getUserPlan($user_id){
        global $wpdb;
        $has_plans_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}br_plans'");
        if(!$has_plans_table) return null;

        if($user_id == 1){
            $god = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_plans WHERE plan_key='god' AND plan_status='active'");
            if($god){
                $wpdb->update("{$wpdb->prefix}br_players", array('user_plan_id'=>$god->plan_id), array('player_id'=>1), array('%d'), array('%d'));
                return array('plan_id'=>$god->plan_id, 'plan_key'=>$god->plan_key,
                             'plan_label'=>$god->plan_label, 'plan_type'=>$god->plan_type);
            }
        }

        $row = $wpdb->get_row($wpdb->prepare("
            SELECT pl.plan_id, pl.plan_key, pl.plan_label, pl.plan_type
            FROM {$wpdb->prefix}br_players pr
            JOIN {$wpdb->prefix}br_plans pl ON pl.plan_id = pr.user_plan_id
            WHERE pr.player_id = %d AND pl.plan_status = 'active'
        ", $user_id));
        if($row){
            return array('plan_id'=>$row->plan_id, 'plan_key'=>$row->plan_key,
                         'plan_label'=>$row->plan_label, 'plan_type'=>$row->plan_type);
        }

        $resolved = null;
        $wp_user = get_userdata($user_id);
        if($wp_user && !empty($wp_user->roles)){
            $resolved = $this->getRoleDefaultPlan($wp_user->roles[0]);
        }
        if(!$resolved){
            $resolved = $this->getRoleDefaultPlan('default');
        }
        if(!$resolved){
            $basic = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_plans WHERE plan_key='basic' AND plan_status='active'");
            if($basic){
                $resolved = array('plan_id'=>$basic->plan_id, 'plan_key'=>$basic->plan_key,
                                  'plan_label'=>$basic->plan_label, 'plan_type'=>$basic->plan_type);
            }
        }
        if($resolved){
            $wpdb->update("{$wpdb->prefix}br_players", array('user_plan_id'=>$resolved['plan_id']), array('player_id'=>$user_id), array('%d'), array('%d'));
        }
        return $resolved;
    }

    public function getRoleDefaultPlan($role){
        global $wpdb;
        $config_name = 'role_default_plan_'.$role;
        $plan_key = $wpdb->get_var($wpdb->prepare(
            "SELECT config_value FROM {$wpdb->prefix}br_config WHERE config_name = %s", $config_name
        ));
        if(!$plan_key){
            $plan_key = $wpdb->get_var(
                "SELECT config_value FROM {$wpdb->prefix}br_config WHERE config_name = 'role_default_plan_default'"
            );
        }
        if(!$plan_key) return null;

        $plan = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_plans WHERE plan_key = %s AND plan_status = 'active'", $plan_key
        ));
        if($plan){
            return array('plan_id'=>$plan->plan_id, 'plan_key'=>$plan->plan_key,
                         'plan_label'=>$plan->plan_label, 'plan_type'=>$plan->plan_type);
        }
        return null;
    }

    public function getPlans($status='active'){
        global $wpdb;
        $has_plans_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}br_plans'");
        if(!$has_plans_table) return array();

        if($status){
            $plans = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}br_plans WHERE plan_status = %s ORDER BY plan_id ASC", $status
            ));
        }else{
            $plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_plans ORDER BY plan_id ASC");
        }
        return $plans ? $plans : array();
    }

    public function getSysConfig($config_name=NULL){
        global $wpdb;
        $config = array();
        if($config_name){
            $sq = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_config WHERE config_name='$config_name'");
            if($sq){
                $config['id'] = $sq->config_id;
                $config['label'] = $sq->config_label;
                $config['desc'] = $sq->config_desc;
                $config['type'] = $sq->config_type;
                $config['value'] = $sq->config_value;
            }
        }else{
            $config_query = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_config");
            foreach($config_query as $key=>$sq){
                $config[$sq->config_name]['id'] = $sq->config_id;
                $config[$sq->config_name]['label'] = $sq->config_label;
                $config[$sq->config_name]['desc'] = $sq->config_desc;
                $config[$sq->config_name]['type'] = $sq->config_type;
                $config[$sq->config_name]['value'] = $sq->config_value;
            }
        }
        return $config;
    }

    public function saveSettings(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();
        $settings_data = $p_settings ? $p_settings : $_POST['settings_data'];
        if($current_user->roles[0]=='administrator'){
            $saveProcess = $this->saveSettingsProcess($settings_data, $adventure_id);
            if($saveProcess){
                $data['success'] = true;
                $msg_content = __('Settings Updated!','bluerabbit');
                $data['message'] = $n->pop($msg_content,'teal','config');
                $data['just_notify'] =true;
                BR_Activity::instance()->logActivity(0,'updated','settings');
            }else{
                $data['success'] = false;
                $msg_content = __('No change detected','bluerabbit');
                $data['message'] = $n->pop($msg_content,'orange','remove');
                $data['just_notify'] =true;
            }
        }else{
            $data['success'] = false;
            $msg_content = __('Not enough privileges','bluerabbit');
            $data['message'] = $n->pop($msg_content,'orange','warning');
            $data['just_notify'] =true;
            BR_Activity::instance()->logActivity(0,'no-privileges','settings');
        }
        echo json_encode($data);
        die();
    }

    public function saveSettingsProcess($settings_data, $adventure_id=0){
        global $wpdb; $current_user = wp_get_current_user();
        if (empty($settings_data) || !is_array($settings_data)) return;
        $sql = "INSERT INTO {$wpdb->prefix}br_settings (`setting_id`, `setting_name`, `setting_label`, `setting_value`, `adventure_id`) VALUES";
        $values = array();
        $ph= array();
        foreach($settings_data as $key=>$s){
            if (!is_array($s) || !isset($s['name'])) continue;
            $name = $s['name'] ? sanitize_title_with_dashes($s['name']) : sanitize_title_with_dashes($key);
            $setting_value = (isset($s['value']) && $s['value'] > 0) ? $s['value'] : "NULL";
            array_push($values, $s['id'] ?? 0, $name, $s['label'] ?? '', $s['value'] ?? '', $adventure_id);
            $ph[] = " (%d, %s, %s, %s, %d) ";
            if(($s['name'] ?? '') == 'default_adventure' && !empty($s['value'])){
                update_option('page_on_front', $page_check->ID, 'yes');
                update_option('show_on_front', 'page','yes');
            }else{
                update_option('page_on_front', 0,'yes');
                update_option('show_on_front', 'posts','yes');
            }
        }
        if (empty($ph)) return;

        $sql .= implode(', ',$ph);
        $sql .= "ON DUPLICATE KEY UPDATE setting_name=VALUES(setting_name), setting_label=VALUES(setting_label),  setting_value=VALUES(setting_value),  adventure_id=VALUES(adventure_id)";

        $sql = $wpdb->query($wpdb->prepare ($sql, $values));
        return $sql;
    }

    public function saveSysConfig(){
        global $wpdb; $current_user = wp_get_current_user();
        $role = $current_user->roles[0];
        $data = array();
        $n = new Notification();

        if($role == 'administrator'){
            $config_data = $_POST['config_data'];
            $features_data = isset($_POST['features_data']) ? $_POST['features_data'] : array();

            $sql = "INSERT INTO {$wpdb->prefix}br_config (`config_id`, `config_name`, `config_label`, `config_type`, `config_desc`, `config_value`) VALUES ";
            $values = array();
            $ph= array();
            foreach($config_data as $key=>$s){
                $name = $s['name'] ? sanitize_title_with_dashes($s['name']) : sanitize_title_with_dashes($key);
                $setting_value = ($s['value'] > 0) ? $s['value'] : "NULL";
                array_push($values, $s['id'], $name, $s['label'],$s['type'],$s['desc'], $s['value']);
                $ph[] = " (%d, %s, %s, %s, %s, %s) ";
                if($s['name']=='default_adventure' && $s['value']){
                    update_option('page_on_front', $page_check->ID, 'yes');
                    update_option('show_on_front', 'page','yes');
                }else{
                    update_option('page_on_front', 0,'yes');
                    update_option('show_on_front', 'posts','yes');
                }
            }
            $sql .= implode(', ',$ph);
            $sql .= "ON DUPLICATE KEY UPDATE config_name=VALUES(config_name), config_label=VALUES(config_label),  config_type=VALUES(config_type),  config_desc=VALUES(config_desc),  config_value=VALUES(config_value); ";
            $sql = $wpdb->query($wpdb->prepare ($sql, $values));

            if(!empty($features_data)){
                $sql2 = "INSERT INTO {$wpdb->prefix}br_features (`feature_id`, `feature_name`, `feature_label`, `feature_type`, `feature_desc`, `feature_access_free`, `feature_access_pro`, `feature_access_admin`, `feature_access_god`) VALUES ";
                $fvalues = array();
                $fph= array();
                foreach($features_data as $key=>$f){
                    $name = sanitize_title_with_dashes($f['name']);
                    $free_val = isset($f['free']) ? $f['free'] : (isset($f['basic']) ? $f['basic'] : 0);
                    $pro_val = isset($f['pro']) ? $f['pro'] : 0;
                    $admin_val = isset($f['admin']) ? $f['admin'] : (isset($f['enterprise']) ? $f['enterprise'] : 0);
                    $god_val = isset($f['god']) ? $f['god'] : 0;
                    array_push($fvalues, $f['id'], $name, $f['label'], $f['type'], $f['desc'], $free_val, $pro_val, $admin_val, $god_val);
                    $fph[] = " (%d, %s, %s, %s, %s, %d, %d, %d, %d) ";
                }
                $sql2 .= implode(', ',$fph);
                $sql2 .= "ON DUPLICATE KEY UPDATE feature_name=VALUES(feature_name), feature_label=VALUES(feature_label), feature_type=VALUES(feature_type), feature_desc=VALUES(feature_desc), feature_access_free=VALUES(feature_access_free), feature_access_pro=VALUES(feature_access_pro), feature_access_admin=VALUES(feature_access_admin), feature_access_god=VALUES(feature_access_god); ";
                $sql2 = $wpdb->query($wpdb->prepare($sql2, $fvalues));

                $has_plans_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}br_plans'");
                if($has_plans_table){
                    $plans = $wpdb->get_results("SELECT plan_id, plan_key FROM {$wpdb->prefix}br_plans WHERE plan_status='active'");
                    $plan_map = array();
                    foreach($plans as $p){ $plan_map[$p->plan_key] = $p->plan_id; }

                    foreach($features_data as $key=>$f){
                        $feature_id = intval($f['id']);
                        if(!$feature_id) continue;
                        foreach($plan_map as $pkey=>$pid){
                            $val = isset($f[$pkey]) ? $f[$pkey] : '0';
                            $wpdb->query($wpdb->prepare(
                                "INSERT INTO {$wpdb->prefix}br_plan_features (plan_id, feature_id, feature_value)
                                VALUES (%d, %d, %s)
                                ON DUPLICATE KEY UPDATE feature_value = %s",
                                $pid, $feature_id, $val, $val
                            ));
                        }
                    }
                }
            }
            if($sql !== FALSE){
                $data['success'] = true;
                $msg_content = __('Configuration Updated!','bluerabbit');
                $data['message'] = $n->pop($msg_content,'teal','config');
                $data['just_notify'] =true;
                BR_Activity::instance()->logActivity(0,'updated','settings');
            }else{
                $data['success'] = false;
                $msg_content = __('No change detected','bluerabbit');
                $data['message'] = $n->pop($msg_content,'orange','remove');
                $data['just_notify'] =true;
            }
        }else{
            $data['success'] = false;
            $msg_content = __('Not enough privileges','bluerabbit');
            $data['message'] = $n->pop($msg_content,'orange','warning');
            $data['just_notify'] =true;
            BR_Activity::instance()->logActivity(0,'attempt-to-update-by-'.$current_user->ID,'config');
        }
        echo json_encode($data);
        die();
    }

    public function savePlan(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $plan_label = sanitize_text_field($_POST['plan_label']);
        $plan_key = sanitize_title_with_dashes(strtolower($plan_label));
        $plan_notes = isset($_POST['plan_notes']) ? sanitize_textarea_field($_POST['plan_notes']) : '';
        $clone_from = isset($_POST['clone_from']) ? intval($_POST['clone_from']) : 0;
        $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;

        if(empty($plan_label)){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Plan name is required','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        if($plan_id > 0){
            $wpdb->update(
                "{$wpdb->prefix}br_plans",
                array('plan_label' => $plan_label, 'plan_notes' => $plan_notes),
                array('plan_id' => $plan_id),
                array('%s','%s'),
                array('%d')
            );
            $data['plan_id'] = $plan_id;
        }else{
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT plan_id FROM {$wpdb->prefix}br_plans WHERE plan_key = %s", $plan_key
            ));
            if($existing){
                $plan_key = $plan_key . '-' . time();
            }
            $wpdb->insert("{$wpdb->prefix}br_plans", array(
                'plan_key' => $plan_key,
                'plan_label' => $plan_label,
                'plan_type' => 'custom',
                'plan_notes' => $plan_notes,
            ), array('%s','%s','%s','%s'));
            $plan_id = $wpdb->insert_id;
            $data['plan_id'] = $plan_id;

            if($clone_from > 0){
                $source_features = $wpdb->get_results($wpdb->prepare(
                    "SELECT feature_id, feature_value FROM {$wpdb->prefix}br_plan_features WHERE plan_id = %d", $clone_from
                ));
                foreach($source_features as $sf){
                    $wpdb->insert("{$wpdb->prefix}br_plan_features", array(
                        'plan_id' => $plan_id,
                        'feature_id' => $sf->feature_id,
                        'feature_value' => $sf->feature_value,
                    ), array('%d','%d','%s'));
                }
            }
        }

        $data['success'] = true;
        $data['plan_key'] = $plan_key;
        $data['message'] = $n->pop(__('Plan saved','bluerabbit'),'teal','config');
        $data['just_notify'] = true;
        BR_Activity::instance()->logActivity(0,'saved-plan-'.$plan_key,'plans');
        echo json_encode($data);
        die();
    }

    public function deletePlan(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $plan_id = intval($_POST['plan_id']);
        $plan = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_plans WHERE plan_id = %d", $plan_id
        ));

        if(!$plan || $plan->plan_type == 'standard' || $plan->plan_type == 'system'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Cannot delete standard or system plans','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $wpdb->delete("{$wpdb->prefix}br_plan_features", array('plan_id' => $plan_id), array('%d'));
        $wpdb->update("{$wpdb->prefix}br_players", array('user_plan_id' => NULL), array('user_plan_id' => $plan_id), array('%s'), array('%d'));
        $wpdb->delete("{$wpdb->prefix}br_plans", array('plan_id' => $plan_id), array('%d'));

        $data['success'] = true;
        $data['message'] = $n->pop(__('Plan deleted','bluerabbit'),'teal','trash');
        $data['just_notify'] = true;
        BR_Activity::instance()->logActivity(0,'deleted-plan-'.$plan_id,'plans');
        echo json_encode($data);
        die();
    }

    public function savePlanFeatures(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $plan_id = intval($_POST['plan_id']);
        $features_data = $_POST['features_data'];

        foreach($features_data as $f){
            $feature_id = intval($f['feature_id']);
            $feature_value = sanitize_text_field($f['feature_value']);
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}br_plan_features (plan_id, feature_id, feature_value)
                VALUES (%d, %d, %s)
                ON DUPLICATE KEY UPDATE feature_value = %s",
                $plan_id, $feature_id, $feature_value, $feature_value
            ));
        }

        $plan = $wpdb->get_row($wpdb->prepare("SELECT plan_key FROM {$wpdb->prefix}br_plans WHERE plan_id = %d", $plan_id));
        if($plan && in_array($plan->plan_key, array('free','pro','admin','god'))){
            $col = 'feature_access_'.$plan->plan_key;
            foreach($features_data as $f){
                $feature_id = intval($f['feature_id']);
                $feature_value = sanitize_text_field($f['feature_value']);
                $wpdb->update(
                    "{$wpdb->prefix}br_features",
                    array($col => $feature_value),
                    array('feature_id' => $feature_id),
                    array('%s'),
                    array('%d')
                );
            }
        }

        $data['success'] = true;
        $data['message'] = $n->pop(__('Plan features saved','bluerabbit'),'teal','config');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function assignUserPlan(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $player_id = intval($_POST['player_id']);
        $plan_id = intval($_POST['plan_id']);

        $result = $wpdb->update(
            "{$wpdb->prefix}br_players",
            array('user_plan_id' => $plan_id > 0 ? $plan_id : NULL),
            array('player_id' => $player_id),
            array($plan_id > 0 ? '%d' : '%s'),
            array('%d')
        );

        if($result !== false){
            $data['success'] = true;
            $data['message'] = $n->pop(__('User plan updated','bluerabbit'),'teal','players');
            $data['just_notify'] = true;
        }else{
            $data['success'] = false;
            $data['message'] = $n->pop(__('Failed to update user plan','bluerabbit'),'red','warning');
            $data['just_notify'] = true;
        }
        echo json_encode($data);
        die();
    }

    public function saveFeature(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $feature_id = isset($_POST['feature_id']) ? intval($_POST['feature_id']) : 0;
        $feature_name = sanitize_title_with_dashes($_POST['feature_name']);
        $feature_label = sanitize_text_field($_POST['feature_label']);
        $feature_type = sanitize_text_field($_POST['feature_type']);
        $feature_desc = sanitize_text_field($_POST['feature_desc']);

        if(empty($feature_name) || empty($feature_label)){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Name and Label are required','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        if($feature_id > 0){
            $wpdb->update("{$wpdb->prefix}br_features", array(
                'feature_name' => $feature_name,
                'feature_label' => $feature_label,
                'feature_type' => $feature_type,
                'feature_desc' => $feature_desc,
            ), array('feature_id' => $feature_id), array('%s','%s','%s','%s'), array('%d'));
        }else{
            $wpdb->insert("{$wpdb->prefix}br_features", array(
                'feature_name' => $feature_name,
                'feature_label' => $feature_label,
                'feature_type' => $feature_type,
                'feature_desc' => $feature_desc,
            ), array('%s','%s','%s','%s'));
            $feature_id = $wpdb->insert_id;
        }

        $data['success'] = true;
        $data['feature_id'] = $feature_id;
        $data['message'] = $n->pop(__('Feature saved','bluerabbit'),'teal','config');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function deleteFeature(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $feature_id = intval($_POST['feature_id']);
        $wpdb->delete("{$wpdb->prefix}br_plan_features", array('feature_id' => $feature_id), array('%d'));
        $wpdb->delete("{$wpdb->prefix}br_features", array('feature_id' => $feature_id), array('%d'));

        $data['success'] = true;
        $data['message'] = $n->pop(__('Feature deleted','bluerabbit'),'teal','trash');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function copyPlanFeatures(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $target_plan_id = intval($_POST['target_plan_id']);
        $source_plan_id = intval($_POST['source_plan_id']);

        $wpdb->delete("{$wpdb->prefix}br_plan_features", array('plan_id' => $target_plan_id), array('%d'));

        $source_features = $wpdb->get_results($wpdb->prepare(
            "SELECT feature_id, feature_value FROM {$wpdb->prefix}br_plan_features WHERE plan_id = %d", $source_plan_id
        ));
        foreach($source_features as $sf){
            $wpdb->insert("{$wpdb->prefix}br_plan_features", array(
                'plan_id' => $target_plan_id,
                'feature_id' => $sf->feature_id,
                'feature_value' => $sf->feature_value,
            ), array('%d','%d','%s'));
        }

        $data['success'] = true;
        $data['copied'] = count($source_features);
        $data['message'] = $n->pop(sprintf(__('Copied %d features','bluerabbit'), count($source_features)),'teal','repeat');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function saveRoleDefaults(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $n = new Notification();

        if($current_user->roles[0] != 'administrator'){
            $data['success'] = false;
            $data['message'] = $n->pop(__('Not enough privileges','bluerabbit'),'orange','warning');
            $data['just_notify'] = true;
            echo json_encode($data);
            die();
        }

        $defaults = $_POST['role_defaults'];
        foreach($defaults as $role_key => $plan_key){
            $config_name = 'role_default_plan_'.sanitize_text_field($role_key);
            $plan_key = sanitize_text_field($plan_key);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT config_id FROM {$wpdb->prefix}br_config WHERE config_name = %s", $config_name
            ));
            if($exists){
                $wpdb->update("{$wpdb->prefix}br_config",
                    array('config_value' => $plan_key),
                    array('config_name' => $config_name),
                    array('%s'), array('%s')
                );
            }else{
                $wpdb->insert("{$wpdb->prefix}br_config", array(
                    'config_name' => $config_name,
                    'config_label' => 'Default plan for '.$role_key,
                    'config_type' => 'text',
                    'config_value' => $plan_key,
                ), array('%s','%s','%s','%s'));
            }
        }

        $data['success'] = true;
        $data['message'] = $n->pop(__('Role defaults saved','bluerabbit'),'teal','config');
        $data['just_notify'] = true;
        echo json_encode($data);
        die();
    }

    public function searchPlayersForPlan(){
        global $wpdb;
        $current_user = wp_get_current_user();

        if($current_user->roles[0] != 'administrator'){
            echo json_encode(array('success' => false));
            die();
        }

        $search = sanitize_text_field($_POST['search']);
        $players = $wpdb->get_results($wpdb->prepare("
            SELECT p.player_id, p.player_display_name, p.player_email, p.user_plan_id,
                   pl.plan_label, pl.plan_key
            FROM {$wpdb->prefix}br_players p
            LEFT JOIN {$wpdb->prefix}br_plans pl ON pl.plan_id = p.user_plan_id
            WHERE p.player_display_name LIKE %s OR p.player_email LIKE %s
            LIMIT 20
        ", '%'.$search.'%', '%'.$search.'%'));

        echo json_encode(array('success' => true, 'players' => $players));
        die();
    }
}

<?php
class BR_Step {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function reorderSteps(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $_POST['adventure_id'];
        $quest_id = $_POST['quest_id'];
        $the_order = $_POST['the_order'];
        $notification = new Notification();
        $reordered = $this->reorderStepProcess($the_order);
        if($reordered){
            $msg_content = __('Steps Reordered!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'indigo','check');
            $data['just_notify'] =true;
            $data['success']=true;
            $content_log = serialize($the_order);
            BR_Activity::instance()->logActivity($adventure_id,'reoredered','steps',$content_log, $quest_id);
        }else{
            $msg_content = __('No change made','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'blue-grey','remove');
            $data['just_notify'] =true;
            $data['success']=false;
        }
        echo json_encode($data);
        die();
    }

    public function reorderStepProcess($the_order){
        global $wpdb;
        $step_values = array();
        $step_ph= array();
        $sql = "INSERT INTO {$wpdb->prefix}br_steps (step_id, step_order) VALUES";
        foreach($the_order as $key=>$o){
            array_push($step_values, $o, $key+1);
            $step_ph[] = " (%d, %d) ";
        }
        $sql .= implode(', ',$step_ph);
        $sql .= "ON DUPLICATE KEY UPDATE step_order=VALUES(step_order) ";
        $sql = $wpdb->query($wpdb->prepare ($sql, $step_values));
        return $sql;
    }

    public function addStep($step_data=null){
        global $wpdb;

        $quest_id=$_POST['quest_id'];
        $adventure_id=$_POST['adventure_id'];
        $id_to_duplicate=$_POST['id_to_duplicate'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d h:i:s');
        $notification = new Notification();

        if($id_to_duplicate){
            $step_to_duplicate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$id_to_duplicate");
            if($step_to_duplicate){
                $step_duplication = "INSERT INTO {$wpdb->prefix}br_steps ( `step_title`, `step_content`, `step_image`, `step_character_image`,`step_character_name`, `step_background`, `step_type`, `step_attach`, `step_achievement_group`, `step_order`, `step_next`, `step_status`, `step_date`, `step_modified`, `step_settings`, `step_item`, `quest_id`, `adventure_id`) SELECT  `step_title`, `step_content`, `step_image`, `step_character_image`, `step_character_name`, `step_background`, `step_type`, `step_attach`, `step_achievement_group`, `step_order`, `step_next`, `step_status`, `step_date`, `step_modified`, `step_settings`, `step_item`, `quest_id`, `adventure_id` FROM  {$wpdb->prefix}br_steps WHERE `step_id` = %d;";
                $sql = $wpdb->prepare($step_duplication, $step_to_duplicate->step_id);
                $step_insert = $wpdb->query($sql);
                $step_id = $wpdb->insert_id;
                if($step_id){
                    BR_Activity::instance()->logActivity($step->adventure_id,'duplicate','step','', $step_to_duplicate->step_id, $step_id);
                }
                $step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id");
            }else{
                $step = NULL;
                $msg_content = __("The step doesn't exist",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify'] =true;
                $data['success']=false;
                echo json_encode($data);
                die();
            }
        }else{
            $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest_id AND adventure_id=$adventure_id");
            $order = count($steps)+1;
            $step_insert = "INSERT INTO {$wpdb->prefix}br_steps (quest_id, adventure_id, step_date, step_modified, step_title, step_content, step_order) VALUES (%d, %d, %s, %s, %s, %s, %d)";
            $step_insert = $wpdb->query( $wpdb->prepare("$step_insert ", $quest_id, $adventure_id, $today, $today, __("New Step","bluerabbit")." $order", __("New Step Content","bluerabbit")." $order", $order));
            $step_id = $wpdb->insert_id;
            if($step_id){
                BR_Activity::instance()->logActivity($adventure_id,'add','step','', $step_id);
            }
            $step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id AND adventure_id=$adventure_id");
        }

        if($step){
            $theFile = (get_template_directory()."/step-list-item.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }else{
            $msg_content = __("Couldn't create step",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
            $data['success']=false;
        }
        echo json_encode($data);
        die();
    }

    public function removeStep(){
        global $wpdb;
        $step_id=$_POST['step_id'];
        $step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id");
        if($step){
            $adventure = BR_Adventure::instance()->getAdventure($step->adventure_id);
            if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
            $today = date('Y-m-d h:i:s');
            $step_update = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->prefix}br_steps SET step_status='trash', step_modified='%s' WHERE step_id=%d", $today, $step->step_id));
            $updated_step = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step->step_id");
            $notification = new Notification();
            $data['just_notify'] =true;
            if($updated_step->step_status == 'trash'){
                $msg_content = __('Step removed!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','trash');
                $data['success']=true;
                $data['remove_step'] = true;
                $data['step_id'] = $step->step_id;
                BR_Activity::instance()->logActivity($adventure_id, 'remove', 'step','',  $step->step_id);
            }else{
                $msg_content = __("Error, couldn't remove step",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'amber','warning');
                $data['success']=false;
            }
        }
        echo json_encode($data);
        die();
    }

    public function editStep(){
        $step_id=$_POST['step_id'];
        $adventure_id=$_POST['adventure_id'];
        global $wpdb;
        $s = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id=$step_id AND adventure_id=$adventure_id");
        $theFile = (get_template_directory()."/step-form.php");
        if(file_exists($theFile)) {
            include ($theFile);
        }else{
            echo __("File not found","bluerabbit");
        }
        die();
    }

    public function updateStep(){
        global $wpdb;
        $data = array();
        $step_id=$_POST['step_id'];
        $adventure_id=$_POST['adventure_id'];

        $step = $wpdb->get_row("
        SELECT step.*, adv.adventure_gmt FROM {$wpdb->prefix}br_steps step
        JOIN {$wpdb->prefix}br_adventures adv ON step.adventure_id=adv.adventure_id
        WHERE step.step_id=$step_id");
        $notification = new Notification();
        if($step){
            if ($step->adventure_gmt){ date_default_timezone_set($step->adventure_gmt); }
            $today = date('Y-m-d h:i:s');
            $step_title=stripslashes_deep($_POST['step_title']);
            $step_content=stripslashes_deep($_POST['step_content'] ?? '');
            $step_type=$_POST['step_type'];
            $step_skin=isset($_POST['step_skin']) ? $_POST['step_skin'] : null;
            $step_attach=$_POST['step_attach'] ?? null;
            $step_character_name=$_POST['step_character_name'] ?? '';
            $step_character_image=$_POST['step_character_image'] ?? '';
            $step_background=$_POST['step_background'] ?? '';
            $step_achievement_group=$_POST['step_achievement_group'] ?? null;
            $step_item=(int) ($_POST['step_item'] ?? 0);
            $step_image=$_POST['step_image'] ?? '';

            $step_settings = isset($_POST['step_settings']) ? stripslashes_deep($_POST['step_settings']) : null;
            $step_correct = isset($_POST['step_correct']) ? stripslashes_deep($_POST['step_correct']) : null;
            $step_mistake_message = isset($_POST['step_mistake_message']) ? stripslashes_deep($_POST['step_mistake_message']) : null;
            $step_required = isset($_POST['step_required']) ? (int) $_POST['step_required'] : 1;
            $step_xp_reward = isset($_POST['step_xp_reward']) ? (int) $_POST['step_xp_reward'] : 0;
            $step_bloo_reward = isset($_POST['step_bloo_reward']) ? (int) $_POST['step_bloo_reward'] : 0;
            $step_ep_reward = isset($_POST['step_ep_reward']) ? (int) $_POST['step_ep_reward'] : 0;
            $step_item_reward = !empty($_POST['step_item_reward']) ? (int) $_POST['step_item_reward'] : null;
            $step_achievement_reward = !empty($_POST['step_achievement_reward']) ? (int) $_POST['step_achievement_reward'] : null;
            $step_branch_group_id = !empty($_POST['step_branch_group_id']) ? (int) $_POST['step_branch_group_id'] : null;

            if ($step_skin !== 'branch_choice') { $step_achievement_group = null; }
            if (!in_array($step_skin, ['dialogue', 'system'])) { $step_attach = null; }

            $update_data = [
                'step_modified'          => $today,
                'step_title'             => $step_title,
                'step_content'           => $step_content,
                'step_type'              => $step_type,
                'step_skin'              => $step_skin,
                'step_character_image'   => $step_character_image,
                'step_background'        => $step_background,
                'step_achievement_group' => $step_achievement_group,
                'step_attach'            => $step_attach,
                'step_item'              => $step_item,
                'step_character_name'    => $step_character_name,
                'step_image'             => $step_image,
                'step_settings'          => $step_settings,
                'step_correct'           => $step_correct,
                'step_mistake_message'   => $step_mistake_message,
                'step_required'          => $step_required,
                'step_xp_reward'         => $step_xp_reward,
                'step_bloo_reward'       => $step_bloo_reward,
                'step_ep_reward'         => $step_ep_reward,
                'step_item_reward'       => $step_item_reward,
                'step_achievement_reward'=> $step_achievement_reward,
                'step_branch_group_id'   => $step_branch_group_id,
            ];
            $wpdb->update("{$wpdb->prefix}br_steps", $update_data, ['step_id' => $step->step_id]);

            $updated_step = $wpdb->get_row("SELECT step.* FROM {$wpdb->prefix}br_steps step WHERE step.step_id=$step->step_id");
            $data['updated_step'] = $updated_step;
            $data['success']=true;
            $msg_content = __('Step updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'deep-purple','progression');
            BR_Activity::instance()->logActivity($adventure_id, 'update', 'step','',  $step_id);
            $data['just_notify'] =true;
            $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
            if($adventure->adventure_type=='template'){
                $wpdb->update("{$wpdb->prefix}br_steps", [
                    'step_modified'          => $today,
                    'step_title'             => $step_title,
                    'step_content'           => $step_content,
                    'step_type'              => $step_type,
                    'step_skin'              => $step_skin,
                    'step_character_image'   => $step_character_image,
                    'step_background'        => $step_background,
                    'step_achievement_group' => $step_achievement_group,
                    'step_attach'            => $step_attach,
                    'step_character_name'    => $step_character_name,
                    'step_image'             => $step_image,
                    'step_settings'          => $step_settings,
                    'step_correct'           => $step_correct,
                    'step_mistake_message'   => $step_mistake_message,
                    'step_required'          => $step_required,
                ], ['step_parent' => $updated_step->step_id]);
                BR_Activity::instance()->logActivity($adventure_id, 'update', 'step-children','',  $step_id);
            }
        }else{
            $data['success']=false;
            $msg_content = __('Step not found','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    public function loadStepButtonForm(){
        global $wpdb;
        $s = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id={$_POST['step_id']} AND step_status='publish'");
        $theFile = get_template_directory().'/step-button-form-'.$_POST['button_form'].'.php';
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }

    public function addStepButton(){
        global $wpdb;
        $step_id=$_POST['step_id'];
        $step_type=$_POST['step_type'];
        $quest_id=$_POST['quest_id'];
        $adventure_id=$_POST['adventure_id'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d h:i:s');
        $button_insert = "INSERT INTO {$wpdb->prefix}br_step_buttons (step_id, quest_id, adventure_id, button_type) VALUES (%d, %d, %d, %s)";
        $button_insert = $wpdb->query( $wpdb->prepare("$button_insert ", $step_id, $quest_id, $adventure_id, $step_type));
        $button_id = $wpdb->insert_id;
        if($button_id){
            BR_Activity::instance()->logActivity($adventure_id, 'add', 'step-button','',  $button_id);
        }
        if($step_type == 'jump'){
             $steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$quest_id AND adventure_id=$adventure_id AND step_status='publish' AND step_id !=$step_id ORDER BY step_order, step_id");
        }
        $btn = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE button_id=$button_id AND adventure_id=$adventure_id");
        $notification = new Notification();
        if($btn){
            $theFile = (get_template_directory()."/step-button-form-".$step_type."-element.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }else{
            $msg_content = __("Couldn't create button",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
            $data['success']=false;
        }
        echo json_encode($data);
        die();
    }

    public function removeStepButton(){
        global $wpdb;
        $button_id=$_POST['button_id'];
        $button_trash = "UPDATE {$wpdb->prefix}br_step_buttons SET button_status='delete' WHERE button_id=%d";
        $button_trash = $wpdb->query($wpdb->prepare($button_trash, $button_id));
        $notification = new Notification();
        if($button_trash){
            $data['button'] = $button_id;
            $msg_content = __('Button removed!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'pink','delete');
            $data['just_notify'] =true;
            $data['success']=true;
            $data['removed_step_button']= true;
            echo json_encode($data);
            BR_Activity::instance()->logActivity($adventure_id, 'remove', 'step-button','',  $button_id);
        }else{
            $msg_content = __("Couldn't remove button",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
            $data['success']=false;
            echo json_encode($data);
        }
        die();
    }

    /// Update STEP BUTTON
    public function updateStepButton(){
        global $wpdb;
        $data = array();
        $step_id=$_POST['step_id'];
        $adventure_id=$_POST['adventure_id'];
        $btn_id=$_POST['btn_id'];
        $button = $wpdb->get_row("
        SELECT btn.*, adv.adventure_gmt FROM {$wpdb->prefix}br_step_buttons btn
        JOIN {$wpdb->prefix}br_adventures adv ON btn.adventure_id=adv.adventure_id
        WHERE btn.button_id=$btn_id AND btn.step_id=$step_id");
        $notification = new Notification();
        if($button){
            $button_text = stripslashes_deep($_POST['button_text']);
            $button_ep_cost = $_POST['button_ep_cost'];
            $button_step_next=$_POST['button_step_next'];
            $button_image = ($_POST['button_image']);

            $button_update = "UPDATE {$wpdb->prefix}br_step_buttons SET `button_text`=%s, `button_ep_cost`=%d, `button_image`=%s, `button_step_next`=%d WHERE button_id=%d AND step_id=%d ";

            $button_update = $wpdb->query( $wpdb->prepare($button_update, $button_text, $button_ep_cost, $button_image, $button_step_next, $button->button_id, $button->step_id));

            $data['success']=true;
            $data['button']=$button;
            $msg_content = __('Button updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','check');
            $data['just_notify'] =true;
            echo json_encode($data);

            BR_Activity::instance()->logActivity($adventure_id, 'update', 'step-button','',  $btn_id);

        }else{
            $data['success']=false;
            $msg_content = __('Button not found','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
            echo json_encode($data);
        }
        die();
    }

    public function ajaxCompleteStep() {
        $current_user = wp_get_current_user();
        $data = ['success' => false, 'just_notify' => true];
        $notification = new Notification();

        $player_id    = $current_user->ID;
        $step_id      = (int) $_POST['step_id'];
        $quest_id     = (int) $_POST['quest_id'];
        $adventure_id = (int) $_POST['adventure_id'];
        $response     = isset($_POST['response']) ? $_POST['response'] : [];

        if (!$player_id || !$step_id || !$quest_id || !$adventure_id) {
            $data['message'] = $notification->pop(__('Missing parameters', 'bluerabbit'), 'red', 'warning');
            echo json_encode($data); die();
        }

        $result = $this->completeStep($player_id, $step_id, $quest_id, $adventure_id, $response);

        if ($result['success']) {
            $data['success'] = true;
            $data['result'] = $result;
            if (!empty($result['milestone_complete'])) {
                $data['message'] = $notification->pop(__('Milestone complete!', 'bluerabbit'), 'green', 'check');
            } elseif ($result['correct'] === 0) {
                $data['message'] = $notification->pop(
                    $result['mistake_message'] ?? __('Incorrect answer', 'bluerabbit'), 'red', 'cancel'
                );
            } else {
                $data['message'] = $notification->pop(__('Step completed', 'bluerabbit'), 'green', 'check');
            }
        } else {
            $data['message'] = $notification->pop(__('Could not complete step', 'bluerabbit'), 'red', 'warning');
            $data['error'] = $result['error'] ?? '';
        }

        echo json_encode($data);
        die();
    }

    public function getSteps($quest_id, $status = 'publish') {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_steps
             WHERE quest_id = %d AND step_status = %s
             ORDER BY step_order, step_id",
            $quest_id, $status
        ));
    }

    public function getStep($step_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_steps WHERE step_id = %d",
            $step_id
        ));
    }

    public function getPlayerSteps($quest_id, $player_id, $adventure_id = null) {
        global $wpdb;
        $sql = "SELECT ps.*, s.step_type, s.step_skin, s.step_order, s.step_required
                FROM {$wpdb->prefix}br_player_steps ps
                JOIN {$wpdb->prefix}br_steps s ON ps.step_id = s.step_id
                WHERE ps.quest_id = %d AND ps.player_id = %d";
        $params = [$quest_id, $player_id];
        if ($adventure_id) {
            $sql .= " AND ps.adventure_id = %d";
            $params[] = $adventure_id;
        }
        $sql .= " ORDER BY s.step_order, s.step_id";
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public function validateStepResponse($step, $response) {
        if (empty($step->step_correct)) return ['correct' => null, 'score' => null];

        $accepted = json_decode($step->step_correct, true);
        if (!is_array($accepted) || empty($accepted)) return ['correct' => null, 'score' => null];

        $skin = $step->step_skin ?: $step->step_type;

        switch ($skin) {
            case 'keyphrase':
            case 'cryptex':
                $answer = isset($response['answer']) ? trim($response['answer']) : '';
                $settings = json_decode($step->step_settings, true) ?: [];
                $case_sensitive = !empty($settings['case_sensitive']);
                foreach ($accepted as $keyword) {
                    $kw = trim($keyword);
                    if ($case_sensitive ? ($answer === $kw) : (mb_strtolower($answer) === mb_strtolower($kw))) {
                        return ['correct' => 1, 'score' => 100];
                    }
                }
                return ['correct' => 0, 'score' => 0];

            case 'multiple_choice':
                $chosen = isset($response['selected']) ? (array) $response['selected'] : [];
                sort($chosen);
                sort($accepted);
                $match = ($chosen === $accepted);
                return ['correct' => $match ? 1 : 0, 'score' => $match ? 100 : 0];

            case 'backpack_item':
                $player_id = $response['player_id'] ?? 0;
                $adventure_id = $response['adventure_id'] ?? 0;
                $item_id = $accepted[0] ?? 0;
                if (!$player_id || !$item_id) return ['correct' => 0, 'score' => 0];
                global $wpdb;
                $has = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
                     WHERE player_id = %d AND adventure_id = %d AND object_id = %d AND trnx_status = 'publish'",
                    $player_id, $adventure_id, $item_id
                ));
                return ['correct' => $has ? 1 : 0, 'score' => $has ? 100 : 0];

            case 'puzzle':
            case 'scorm':
                return ['correct' => 1, 'score' => 100];

            default:
                return ['correct' => null, 'score' => null];
        }
    }

    public function completeStep($player_id, $step_id, $quest_id, $adventure_id, $response = []) {
        global $wpdb;

        $step = $this->getStep($step_id);
        if (!$step || $step->quest_id != $quest_id) {
            return ['success' => false, 'error' => 'step_not_found'];
        }

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_player_steps
             WHERE player_id = %d AND step_id = %d AND quest_id = %d AND adventure_id = %d",
            $player_id, $step_id, $quest_id, $adventure_id
        ));

        $skin = $step->step_skin ?: $step->step_type;
        $validation = $this->validateStepResponse($step, $response);
        $ps_type = $skin;
        $ps_response = !empty($response) ? json_encode($response) : null;

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        if ($adventure && $adventure->adventure_gmt) { date_default_timezone_set($adventure->adventure_gmt); }
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $attempt = (int) $existing->ps_attempt + 1;
            $wpdb->update("{$wpdb->prefix}br_player_steps", [
                'ps_response'  => $ps_response,
                'ps_correct'   => $validation['correct'],
                'ps_score'     => $validation['score'],
                'ps_step_type' => $ps_type,
                'ps_attempt'   => $attempt,
                'ps_modified'  => $now,
            ], [
                'player_id'    => $player_id,
                'step_id'      => $step_id,
                'quest_id'     => $quest_id,
                'adventure_id' => $adventure_id,
            ]);
        } else {
            $wpdb->insert("{$wpdb->prefix}br_player_steps", [
                'quest_id'     => $quest_id,
                'adventure_id' => $adventure_id,
                'player_id'    => $player_id,
                'step_id'      => $step_id,
                'ps_date'      => $now,
                'ps_status'    => 'publish',
                'ps_step_type' => $ps_type,
                'ps_response'  => $ps_response,
                'ps_correct'   => $validation['correct'],
                'ps_attempt'   => 1,
                'ps_score'     => $validation['score'],
            ]);
        }

        $result = [
            'success'  => true,
            'correct'  => $validation['correct'],
            'score'    => $validation['score'],
            'step_id'  => $step_id,
            'skin'     => $skin,
        ];

        if ($validation['correct'] === 0 && !empty($step->step_mistake_message)) {
            $result['mistake_message'] = $step->step_mistake_message;
        }

        // Grant step-level rewards
        if ($validation['correct'] !== 0) {
            $this->grantStepRewards($player_id, $adventure_id, $step, $now);
        }

        // Check if all required steps are complete → trigger milestone completion
        if ($validation['correct'] !== 0) {
            $all_steps = $this->getSteps($quest_id);
            $completed = $wpdb->get_col($wpdb->prepare(
                "SELECT step_id FROM {$wpdb->prefix}br_player_steps
                 WHERE quest_id = %d AND player_id = %d AND adventure_id = %d
                   AND (ps_correct IS NULL OR ps_correct = 1)",
                $quest_id, $player_id, $adventure_id
            ));
            $required_done = true;
            foreach ($all_steps as $s) {
                if ($s->step_required && !in_array($s->step_id, $completed)) {
                    $required_done = false;
                    break;
                }
            }
            if ($required_done) {
                $result['milestone_complete'] = true;
                $result['milestone_result'] = BR_Quest::instance()->completeMilestone($player_id, $quest_id, $adventure_id);
            }
        }

        BR_Activity::instance()->logActivity($adventure_id, 'complete', 'step', $skin, $step_id);

        return $result;
    }

    private function grantStepRewards($player_id, $adventure_id, $step, $now) {
        global $wpdb;
        if ($step->step_item_reward) {
            $has = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_transactions
                 WHERE player_id = %d AND adventure_id = %d AND object_id = %d AND trnx_status = 'publish'",
                $player_id, $adventure_id, $step->step_item_reward
            ));
            if (!$has) {
                $item = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}br_items WHERE item_id = %d", $step->step_item_reward
                ));
                if ($item) {
                    $wpdb->insert("{$wpdb->prefix}br_transactions", [
                        'player_id'    => $player_id,
                        'adventure_id' => $adventure_id,
                        'object_id'    => $step->step_item_reward,
                        'trnx_author'  => $player_id,
                        'trnx_amount'  => 0,
                        'trnx_type'    => $item->item_type,
                        'trnx_date'    => $now,
                        'trnx_modified'=> $now,
                    ]);
                    BR_Activity::instance()->logActivity($adventure_id, 'earned', 'item', $step->step_item_reward, $step->step_id);
                }
            }
        }

        if ($step->step_achievement_reward) {
            $has = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_achievement
                 WHERE player_id = %d AND adventure_id = %d AND achievement_id = %d",
                $player_id, $adventure_id, $step->step_achievement_reward
            ));
            if (!$has && BR_Branch::instance()->canGrantAchievement($player_id, $adventure_id, $step->step_achievement_reward)) {
                $wpdb->insert("{$wpdb->prefix}br_player_achievement", [
                    'player_id'           => $player_id,
                    'adventure_id'        => $adventure_id,
                    'achievement_id'      => $step->step_achievement_reward,
                    'achievement_applied' => $now,
                ]);
                BR_Activity::instance()->logActivity($adventure_id, 'earned', 'achievement', $step->step_achievement_reward, $step->step_id);
            }
        }
    }
}

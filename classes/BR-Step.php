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

    /// Update STEP
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
            $step_content=stripslashes_deep($_POST['step_content']);
            $step_type=$_POST['step_type'];
            $step_attach=$_POST['step_attach'];
            $step_character_name=$_POST['step_character_name'];
            $step_character_image=$_POST['step_character_image'];
            $step_background=$_POST['step_background'];
            $step_achievement_group=$_POST['step_achievement_group'];
            $step_item=$_POST['step_item'];
            $step_image=$_POST['step_image'];

            if($step_type !='path-choice'){ $step_achievement_group = NULL;}
            if($step_type !='dialogue'){ $step_attach = NULL;}

            $step_update = "UPDATE {$wpdb->prefix}br_steps SET  `step_modified`=%s, `step_title`=%s, `step_content`=%s,`step_type`=%s, `step_character_image`=%s, `step_background`=%s, `step_achievement_group`=%s, `step_attach`=%s  , `step_item`=%d, `step_character_name`=%s, `step_image`=%s WHERE step_id=%d";
            $step_update = $wpdb->query( $wpdb->prepare("$step_update ", $today, $step_title, $step_content, $step_type, $step_character_image, $step_background, $step_achievement_group, $step_attach, $step_item, $step_character_name, $step_image, $step->step_id));
            $updated_step = $wpdb->get_row("SELECT step.* FROM {$wpdb->prefix}br_steps step WHERE step.step_id=$step->step_id");
            $data['updated_step'] = $updated_step;
            $data['success']=true;
            $msg_content = __('Step updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'deep-purple','progression');
            BR_Activity::instance()->logActivity($adventure_id, 'update', 'step','',  $step_id);
            $data['just_notify'] =true;
            $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
            if($adventure->adventure_type=='template'){
                $children_update = "UPDATE {$wpdb->prefix}br_steps SET `step_modified`=%s, `step_title`=%s, `step_content`=%s,`step_type`=%s, `step_character_image`=%s, `step_background`=%s, `step_achievement_group`=%s, `step_attach`=%s, `step_character_name`=%s, `step_image`=%s WHERE `step_parent`=$updated_step->step_id AND step_id!=$updated_step->step_id";

                $children_update = $wpdb->query( $wpdb->prepare("$children_update ", $today, $step_title, $step_content, $step_type, $step_character_image, $step_background, $step_achievement_group, $step_attach, $step_character_name, $step_image, $step->step_id));
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
}

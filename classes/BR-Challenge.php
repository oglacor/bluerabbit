<?php
class BR_Challenge {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    public function addQuestion(){
        global $wpdb; $current_user = wp_get_current_user();
        $type=$_POST['type'];
        $id=$_POST['id'];
        $style = $_POST['style'] ? $_POST['style'] : 'closed';
        $quest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$id AND quest_status='publish'");
        if($type == 'survey'){

            if($style == 'rating'){
                $range = 5;
            }else if($style=='guild-vote'){
                $survey = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$id");
                $guild_groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$survey->adventure_id AND guild_group !='' AND guild_status='publish' GROUP BY guild_group");
            }
            $questions_query = "INSERT INTO {$wpdb->prefix}br_survey_questions (survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_range) VALUES (%d, %s, %s, %s, %d)";
            $qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $id, "", "", $style, $range));
            $qKey = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($quest->adventure_id,'add','survey-question','',$quest->quest_id);
            if(!$style || $style == 'closed'){
                $options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_question_id) VALUES ";
                $options_query .= "(%d, '', $qKey), (%d,'', $qKey)";
                $options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $id, $id));
                BR_Activity::instance()->logActivity($quest->adventure_id,'add','survey-question-option',($style ? $style : 'closed'),$quest->quest_id);
            }
            $theFile = (get_template_directory()."/$type-question-form.php");
        }elseif($type == 'challenge'){
            $questions_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (quest_id, question_title, question_image) VALUES (%d, %s, %s)";
            $qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $id, "", ""));
            $qKey = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($quest->adventure_id,'add','challenge-question','',$quest->quest_id);
            if($wpdb->insert_id){
                $options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id,  answer_value, answer_image, answer_correct) VALUES ";
                $options_query .= "(%d, $qKey, '','',1 ), (%d, $qKey, '','',0 )";
                $options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $id, $id));
                BR_Activity::instance()->logActivity($quest->adventure_id,'add','challenge-question-option','',$quest->quest_id);
            }
            BR_Activity::instance()->logActivity($quest->adventure_id,'add','challenge-question');
            $theFile = (get_template_directory()."/$type-question-form.php");
        }
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }

    public function duplicateQuestion(){
        global $wpdb; $current_user = wp_get_current_user();
        $question = array();
        $type=$_POST['type'];
        $adventure_id=$_POST['adventure_id'];
        $quest_id=$_POST['quest_id'];
        $main_id=$_POST['main_id'];
        $q_id = $_POST['q_id'];
        if($type == 'survey'){
            $my_question = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_survey_questions WHERE survey_question_id=$q_id AND survey_id=$main_id AND survey_question_status='publish'");
            if($my_question){
                $question['survey_question_image']= $my_question->survey_question_image;
                $question['survey_question_text']= $my_question->survey_question_text;
                $question['survey_question_type']= $my_question->survey_question_type;
                $question['survey_question_display']= $my_question->survey_question_display;
                $question['survey_question_order']= $my_question->survey_question_order+1;
                $question['survey_question_description']= $my_question->survey_question_description;
                $the_question_query = "INSERT INTO {$wpdb->prefix}br_survey_questions (survey_id, survey_question_text, survey_question_image, survey_question_type, survey_question_display, survey_question_order, survey_question_description) VALUES (%d, %s, %s, %s, %s, %d, %s)";
                $the_question_insert = $wpdb->query( $wpdb->prepare("$the_question_query ", $main_id, $my_question->survey_question_text, $my_question->survey_question_image, $my_question->survey_question_type, $my_question->survey_question_display, $question['survey_question_order'], $my_question->survey_question_description));
                $qKey = $wpdb->insert_id;
                if($my_question->survey_question_type == 'closed' || $my_question->survey_question_type == 'multi-choice'){
                    $the_options = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_options WHERE survey_question_id=$q_id AND survey_id=$main_id AND survey_option_status='publish'");
                    $the_options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_option_image, survey_question_id) VALUES ";
                    $the_options_values = array();
                    $the_options_place_holders = array();
                    foreach($the_options as $key => $the_option) {
                         array_push($the_options_values, $main_id, $the_option->survey_option_text, $the_option->survey_option_image, $qKey );
                         $the_options_place_holders[] = "(%d, %s, %s, %d)";

                    }
                    $the_options_query .= implode(', ', $the_options_place_holders);
                    $the_options_insert = $wpdb->query( $wpdb->prepare("$the_options_query ", $the_options_values));
                    BR_Activity::instance()->logActivity($adventure_id,'duplicate','survey-question','',$quest_id,$main_id);
                }
                $theFile = (get_template_directory()."/survey-question-form.php");
            }
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }elseif($type == 'challenge'){
            $questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions WHERE question_id=$q_id AND quest_id=$main_id AND question_status='publish'");
            $my_question = $questions[0];
            if($my_question){
                $question['image']= $my_question->question_image;
                $question['title']= __("[ COPY ]")." - ".$my_question->question_title;
                $the_question_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (`quest_id`, `question_title`, `question_image`, `question_type`) VALUES (%d, %s, %s, %s)";
                $the_question_insert = $wpdb->query( $wpdb->prepare("$the_question_query ", $main_id, $question['title'], $my_question->question_image, $my_question->question_type));
                $qKey = $wpdb->insert_id;
                $the_answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$q_id AND quest_id=$main_id AND answer_status='publish'");
                $the_answers_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id, answer_value, answer_image, answer_correct) VALUES ";
                $the_answer_values = array();
                $the_answers_place_holders = array();
                foreach($the_answers as $key => $the_option) {
                     array_push($the_answer_values, $main_id, $qKey, $the_option->answer_value, $the_option->answer_image, $the_option->answer_correct);
                     $the_answers_place_holders[] = "(%d, %d, %s, %s, %d)";
                }
                $the_answers_query .= implode(', ', $the_answers_place_holders);
                $the_answers_insert = $wpdb->query( $wpdb->prepare("$the_answers_query ", $the_answer_values));
                $theFile = (get_template_directory()."/challenge-question-form.php");
                $options = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=$qKey ", "ARRAY_A");
                $question['answers'] = $options;
                BR_Activity::instance()->logActivity($adventure_id,'duplicate','challenge-question','',$quest_id,$main_id);
                if(file_exists($theFile)) {
                    include ($theFile);
                }
            }
        }
        die();
    }

    public function updateQuestion(){
        global $wpdb; $current_user = wp_get_current_user(); $data=array();
        $adventure_id=$_POST['adventure_id'];

        $quest_id=$_POST['quest_id'];
        $q_text = stripslashes_deep($_POST['q_text']);
        $q_image = $_POST['q_image'];
        $q_description = stripslashes_deep($_POST['q_description']);
        $q_range = $_POST['q_range'];
        $q_display = $_POST['q_display'];
        $type=$_POST['type'];
        $id=$_POST['id'];
        $notification = new Notification();
        $data['question_updated']= false;
        $data['question_id']=0;
        if($type == 'survey'){
            if($q_range < 0){ $q_range = 0; }
            $questions_query = "UPDATE {$wpdb->prefix}br_survey_questions SET

            survey_question_text=%s,
            survey_question_image=%s,
            survey_question_description=%s,
            survey_question_range=%d,
            survey_question_display=%s

            WHERE survey_question_id=%d";
            $qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $q_text, $q_image, $q_description, $q_range, $q_display, $id));
            $data['debug'] = print_r($wpdb->last_query,true);
            if($qs_insert){
                $data['success']=true;
                $msg_content = __('Question Updated','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green');
                $data['question_id']=$id;
                $data['question_updated']=$q_text;
                $data['just_notify'] =true;
                BR_Activity::instance()->logActivity($adventure_id,'update','survey-question','',$quest_id,$id);

                $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
                if($adventure->adventure_type=='template'){
                    $children_update = "UPDATE {$wpdb->prefix}br_survey_questions SET

                    survey_question_text=%s,
                    survey_question_image=%s,
                    survey_question_description=%s,
                    survey_question_range=%d,
                    survey_question_display=%s

                    WHERE survey_question_parent= $id AND survey_question_id!=$id";

                    $children_update = $wpdb->query( $wpdb->prepare($children_update, $q_text, $q_image, $q_description, $q_range, $q_display));

                    BR_Activity::instance()->logActivity($adventure_id,'update','survey-question-children','',$quest_id,$id);
                }


            }else{
                $data['success']=false;
                $msg_content = __('Error','bluerabbit');
                $msg_content .= $error;
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify'] =true;

            }
        }else if($type == 'challenge'){
            $questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_title=%s, question_image=%s WHERE question_id=%d";
            $qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $q_text, $q_image, $id));
            $data['debug'] = print_r($wpdb->last_query,true);
            if($qs_insert){
                $data['success'] = true;
                $msg_content = __('Question Updated','bluerabbit');
                $data['question_id']=$id;
                $data['question_updated']=$q_text;
                $data['message'] = $notification->pop($msg_content,'green');
                $data['just_notify'] =true;
                BR_Activity::instance()->logActivity($adventure_id,'update','challenge-question','',$quest_id,$id);


                $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");


            }else{
                $msg_content = __('Error','bluerabbit');
                $msg_content .= $error;
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify'] =true;
            }
        }
        echo json_encode($data);
        die();
    }

    public function removeQuestion(){
        global $wpdb; $current_user = wp_get_current_user();
        $adventure_id=$_POST['adventure_id'];
        $quest_id=$_POST['quest_id'];
        $data= array();
        $data['success']=false;
        $type=$_POST['type'];
        $id=$_POST['id'];
        if (wp_verify_nonce($_POST['nonce'], 'br_delete_question_nonce')) {
            if($type == 'survey'){
                $questions_query = "UPDATE {$wpdb->prefix}br_survey_questions SET survey_question_status='trash' WHERE survey_question_id=%d";
                $qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $id));
                if($qs_insert){
                    $data['success'] = true;
                    $data['message'] .= "<li>".__("Question Removed!","bluerabbit")."</li>";
                    $data['just_notify'] =true;
                    BR_Activity::instance()->logActivity($adventure_id,'removed','survey-question','',$quest_id,$id);
                }else{
                    $data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
                }
            }elseif($type == 'challenge'){
                $questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_status='trash' WHERE question_id=%d";
                $qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $id));
                if($qs_insert){
                    $data['success'] = true;
                    $data['message'] .= "<li>".__("Question Removed!","bluerabbit")."</li>";
                    $data['just_notify'] =true;
                    BR_Activity::instance()->logActivity($adventure_id,'removed','challenge-question','',$quest_id,$id);
                }else{
                    $data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
                }
            }
        }else{
            $data['message'] = "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
        }
        echo json_encode($data);
        die();
    }

    public function addOption(){
        $adventure_id=$_POST['adventure_id'];
        $type=$_POST['type'];
        $main_id=$_POST['main_id'];
        $qKey=$_POST['q_id'];
            global $wpdb; $current_user = wp_get_current_user();
        if($type == 'survey'){
            $options_query = "INSERT INTO {$wpdb->prefix}br_survey_options (survey_id, survey_option_text, survey_question_id) VALUES (%d, '', %d)";
            $options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $main_id, $qKey));
            $oKey = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adventure_id,'add','survey-question-option','',$main_id);
        }elseif($type == 'challenge'){
            $options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, answer_value, question_id) VALUES (%d, ' ', %d)";
            $options_insert = $wpdb->query( $wpdb->prepare("$options_query ", $main_id, $qKey));
            $oKey = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adventure_id,'add','challenge-question-option','', $main_id);
        }
        $theFile = (get_template_directory()."/$type-question-option-form.php");
        if(file_exists($theFile)) {
            include ($theFile);
        }
        die();
    }

    public function updateOption(){
        global $wpdb; $current_user = wp_get_current_user();
        $adventure_id=$_POST['adventure_id'];
        $o_text = stripslashes_deep($_POST['o_text']);
        $o_image = $_POST['o_image'];
        $type=$_POST['type'];
        $main_id=$_POST['main_id'];
        $question_id=$_POST['q_id'];
        $option_id=$_POST['option_id'];
        $o_correct=$_POST['o_correct'];
        $notification = new Notification();
        if($type == 'survey'){
            $option_query = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_text=%s, survey_option_image=%s WHERE survey_id=%d AND survey_question_id=%d AND survey_option_id=%d";
            $option_update = $wpdb->query( $wpdb->prepare($option_query, $o_text, $o_image, $main_id, $question_id, $option_id));
            if($option_update){
                $data['success']=true;
                $msg_content = __('Option Updated','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green');
                BR_Activity::instance()->logActivity($adventure_id,'update','survey-question-option','', $option_id);

                $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
                if($adventure->adventure_type=='template'){
                    $children_update = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_text=%s, survey_option_image=%s WHERE survey_option_parent= $option_id AND survey_option_id!=$option_id";

                    $children_update = $wpdb->query( $wpdb->prepare($children_update, $o_text, $o_image));

                    BR_Activity::instance()->logActivity($adventure_id,'update','survey-question-option-children','',$option_id);
                }
            }else{
                $msg_content = __('Error','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
            }
            $data['just_notify'] =true;
        }elseif($type == 'challenge'){
            $option_query = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_value=%s, answer_image=%s , answer_correct=%d WHERE quest_id=%d AND question_id=%d AND answer_id=%d";
            $option_update = $wpdb->query( $wpdb->prepare($option_query, $o_text, $o_image, $o_correct, $main_id, $question_id, $option_id));

            if($option_update){
                $allOptionsSQL = "SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE question_id=%d AND answer_correct=1";
                $allOptions = $wpdb->query( $wpdb->prepare($allOptionsSQL, $question_id)); $allOptions = $wpdb->last_result;

                if(count($allOptions)>1){
                    $question_type ='multiple';

                }else{
                    $question_type ='single';
                }
                $questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_type='$question_type' WHERE question_id=%d";
                $qs_insert = $wpdb->query( $wpdb->prepare($questions_query, $question_id));


                $data['success']=true;
                $msg_content = __('Option Updated','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green');
                BR_Activity::instance()->logActivity($adventure_id,'update','challenge-question-option','', $option_id);

                $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
                if($adventure->adventure_type=='template'){
                    $children_update = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_value=%s, answer_image=%s , answer_correct=%d WHERE answer_id!=$option_id AND answer_parent=$option_id";

                    $children_update = $wpdb->query( $wpdb->prepare($children_update, $o_text, $o_image, $o_correct));

                    $questions_query = "UPDATE {$wpdb->prefix}br_challenge_questions SET question_type='$question_type'
                    WHERE question_parent=$question_id AND question_id!=$question_id";
                    $qs_insert = $wpdb->query( $wpdb->prepare($questions_query));

                    BR_Activity::instance()->logActivity($adventure_id,'update','challenge-question-option-children','',$option_id);
                }


            }else{
                $msg_content = __('Error','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
            }
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    public function removeOption(){
        global $wpdb; $current_user = wp_get_current_user();
        $adventure_id=$_POST['adventure_id'];
        $data= array();
        $data['success']=false;
        $type=$_POST['type'];
        $id=$_POST['id'];
        if (wp_verify_nonce($_POST['nonce'], 'br_delete_option_nonce')) {
            if($type == 'survey'){
                $option_query = "UPDATE {$wpdb->prefix}br_survey_options SET survey_option_status='trash' WHERE survey_option_id=%d";
                $option_update = $wpdb->query( $wpdb->prepare($option_query, $id));
                if($option_update){
                    $data['success'] = true;
                    $data['message'] .= "<li>".__("Option Removed!","bluerabbit")."</li>";
                    $data['just_notify'] =true;
                    BR_Activity::instance()->logActivity($adventure_id,'removed','survey-question-option','', $id);
                }else{
                    $data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
                }
            }elseif($type == 'challenge'){
                $option_query = "UPDATE {$wpdb->prefix}br_challenge_answers SET answer_status='trash' WHERE answer_id=%d";
                $option_update = $wpdb->query( $wpdb->prepare($option_query, $id));
                if($option_update){
                    $data['success'] = true;
                    $data['message'] .= "<li>".__("Answer Removed!","bluerabbit")."</li>";
                    $data['just_notify'] =true;
                    BR_Activity::instance()->logActivity($adventure_id,'removed','challenge-question-option','', $id);
                }else{
                    $data['message'] .= "<h1>".__("Error","bluerabbit")."</h1>";
                }
            }
        }else{
            $data['message'] = "<h1>".__("Unauthorized Access","bluerabbit")."</h1>";
        }
        echo json_encode($data);
        die();
    }

    public function reorderQuestions(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $_POST['adventure_id'];
        $quest_id = $_POST['quest_id'];
        $the_order = $_POST['the_order'];
        $count = 0;
        foreach($the_order as $k=>$id){
            $sql = "UPDATE {$wpdb->prefix}br_survey_questions SET survey_question_order=%d WHERE (survey_question_id=%d) OR survey_question_parent=%d";
            $sql = $wpdb->prepare ($sql,$k,$id,$id);
            $result = $wpdb->query($sql);
        }
        if($k+1 >= count($the_order)){
            $data['success'] = true;
            $data['message'] = "<h1>".__("Questions Reordered","bluerabbit")."</h1>";
            $data['location'] = "reload";
            BR_Activity::instance()->logActivity($adventure_id,'reoredered','survey-questions',serialize($the_order),$quest_id);
        }else{
            $data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
            $data['message'] .= "<h4>".$k."</h4>";
        }
        echo json_encode($data);
        die();
    }

    public function uploadBulkQuestions(){
        global $wpdb;
        $data = array();
        $n = new Notification();
        $adv_id = $_POST['adventure_id'];
        $quest_id = $_POST['quest_id'];
        $questions_counter = 0;
        $bulk_options_query = "INSERT INTO {$wpdb->prefix}br_challenge_answers (quest_id, question_id,  answer_value, answer_correct) VALUES ";
        $options_values = [];
        $options_place_holders = [];
        if (isset($_FILES['csv_file']['tmp_name'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            if (!is_readable($file)) {
                $data['errors'][] = __("File not readable.","bluerabbit");
            }
            if (empty($file) || !file_exists($file)) {
                $data['errors'][] = __("No file uploaded.","bluerabbit");
            }
            if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
                while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
                    if ($row_index == 0) {
                        // Skip the header row (optional)
                        $row_index++;
                        continue;
                    }
                    if($row_index <=150){
                        $qd = [
                            'question'=>sanitize_text_field($file_data[0]),
                            'correct'=>sanitize_text_field($file_data[1]),
                            'decoy1'=>sanitize_text_field($file_data[2]),
                            'decoy2'=>sanitize_text_field($file_data[3]),
                            'decoy3'=>sanitize_text_field($file_data[4])
                        ];
                        if($qd['question'] && $qd['correct'] && $qd['decoy1']){
                            $questions_query = "INSERT INTO {$wpdb->prefix}br_challenge_questions (quest_id, question_title) VALUES (%d, %s)";
                            $qs_insert = $wpdb->query( $wpdb->prepare("$questions_query ", $quest_id, $qd['question']));
                            $qKey = $wpdb->insert_id;
                            BR_Activity::instance()->logActivity($adv_id,'add','challenge-question','', $quest_id);
                            if($qKey){
                                if($qd['correct']){
                                    $options_place_holders[] = " (%d, %d, %s, %d)";
                                    array_push($options_values, $quest_id, $qKey, $qd['correct'], 1);
                                }
                                if($qd['decoy1']){
                                    $options_place_holders[] = " (%d, %d, %s, %d)";
                                    array_push($options_values, $quest_id, $qKey, $qd['decoy1'], 0);
                                }
                                if($qd['decoy2']){
                                    $options_place_holders[] = " (%d, %d, %s, %d)";
                                    array_push($options_values, $quest_id, $qKey, $qd['decoy2'], 0);
                                }
                                if($qd['decoy3']){
                                    $options_place_holders[] = " (%d, %d, %s, %d)";
                                    array_push($options_values, $quest_id, $qKey, $qd['decoy3'], 0);
                                }
                                $questions_counter++;
                                $msg_content = __("Question #",'bluerabbit')."$questions_counter ".__("added!",'bluerabbit');
                                $data['messages'][] = $n->pop($msg_content,'green','check');
                                BR_Activity::instance()->logActivity($adv_id,'add','challenge-question-option','',$quest_id);
                            }
                        }else{
                            $msg_content = __("Skipping empty row in file",'bluerabbit');
                            $data['messages'][] = $n->pop($msg_content,'grey','check');
                        }
                        $row_index++;
                    }
                }

                fclose($handle);

                $bulk_options_query .= implode(', ', $options_place_holders);
                $bulk_options_query = $wpdb->query( $wpdb->prepare("$bulk_options_query ", $options_values));
                $msg_content = "$questions_counter ".__("questions uploaded correctly",'bluerabbit');

                $data['messages'][] = $n->pop($msg_content,'deep-purple','check');
                $data['success'] = true;
                $data['debug'] = print_r($data['messages'],true);
            }else{
                $data['errors'][] =__("Cannot open file to read","bluerabbit");
            }
        }else{
            $data["errors"][] = __("File doesn't exist","bluerabbit");
        }


        echo json_encode($data);
        die();
    }

    public function startAttempt(){
        global $wpdb; $current_user = wp_get_current_user();

        $data = array();
        $challenge_id = $_POST['challenge_id'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $attempt_cost = $_POST['attempt_cost'];
        $data['success']=false;

        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        //$challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND quest_id=$challenge_id AND quest_status='publish'");
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');


        if(wp_verify_nonce($nonce, 'br_player_new_attempt_nonce')) {
            $ok=false;
            if($attempt_cost > 0){
                $playerData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$current_user->ID AND adventure_id=$adventure_id");
                if($playerData->player_bloo >= $attempt_cost){
                    $ok=true;
                }
            }else{
                $ok=true;
            }
            if($ok){
                $newAttempt="INSERT INTO {$wpdb->prefix}br_challenge_attempts (player_id,adventure_id,quest_id, attempt_date) VALUES (%d,%d,%d, %s)";
                $wpdb->query($wpdb->prepare($newAttempt,$current_user->ID, $adventure_id,$challenge_id, $today));
                $att_id = $wpdb->insert_id;
                if($att_id){
                    $data['att_id']=$att_id;
                    $data['success']=true;
                    $data['message'] = '<h1><span class="icon icon-challenge icon-xl"></span><br><strong>'.__("Get Ready!","bluerabbit").'</strong></h1> <h3><strong>'.__("Challenge Starting","bluerabbit").'</strong></h3>';
                    if($attempt_cost > 0){
                        $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
                        VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
                        $sql = $wpdb->prepare($sql, $current_user->ID, $adventure_id, $challenge_id, $current_user->ID, $attempt_cost, 'attempt', $today, $today);
                        $sql = $wpdb->query($sql);
                        $data['message'] .= '<h4 class="margin-10"><strong>'.__("Attempt Purchased","bluerabbit").'</strong></h4>';
                        $data['message'] .= '<h4 class="margin-10 red-A400 font w700 _20"><span class="icon icon-warning amber-500"></span>'.__("You will spend and lose this attempt if you refresh the page before you finish the challenge","bluerabbit").'</h4>';
                        BR_Activity::instance()->logActivity($adventure_id,'purchase','challenge-attempt',"",$challenge_id);
                    }else{
                        $data['message'] .= '<h4 class="margin-10 red-A400 font w700 _20"><span class="icon icon-warning amber-500"></span>'.__("You will lose this free attempt if you refresh the page before you finish the challenge","bluerabbit").'</h4>';
                    }
                    BR_Activity::instance()->logActivity($adventure_id,'attempt','challenge',"",$challenge_id);
                    $data['message'].= '<button class="form-ui green-bg-400 white-color">'.__("click to start","bluerabbit").'</button>';
                }
            }else{
                $data['message'] = '<span class="icon icon-bloo icon-xl"></span><br><h2><strong>'.__("Not enough funds","bluerabbit").'</strong></h2>';
                $data['message'] .= '<h5>'.__("click to close","bluerabbit").'</h5>';
                $data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";
                BR_Activity::instance()->logActivity($adventure_id,'no-funds','challenge',"",$challenge_id);
            }
        }else{
            $data['message'] = '<h1><span class="icon icon-cancel solid-red icon-xl"></span><br><strong>'.__("Unauthorized access","bluerabbit").'</strong></h1> <h5>'.__("click to close","bluerabbit").'</h5>';
            $data['location'] = get_bloginfo('url');
        }
        echo json_encode($data);
        die();

    }

    public function submitAnswer(){
        global $wpdb; $current_user = wp_get_current_user();

        $challengeID = $_POST['challenge_id'];
        $attID = $_POST['attempt_id'];
        $questionID = $_POST['question_id'];
        $answer_id = $_POST['answer_id'];
        $answer_value = $_POST['answer_value'] ? implode(",",$_POST['answer_value']) : '';
        $adventure_id = $_POST['adventure_id'];

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        $data = array();
        $data['success']=false;
        $answerInsert = "INSERT INTO {$wpdb->prefix}br_challenge_attempt_answers (attempt_id, question_id, player_id, quest_id, answer_id, answer_value, timestamp)
        VALUES (%d, %d, %d, %d, %d, %s, %s)
        ON DUPLICATE KEY UPDATE answer_id=%s, answer_value=%s, timestamp=%s";

        $answer =$wpdb->query($wpdb->prepare($answerInsert, $attID, $questionID, $current_user->ID, $challengeID, $answer_id, $answer_value, $today, $answer_id, $answer_value, $today));
        if($answer!==false){
            $data['success'] = true;
            BR_Activity::instance()->logActivity($adv_child_id,'answer','challenge-question', $answer_value, $challenge_id, $answer_id);
        }else{
            $data['message'] = '<h1><span class="icon icon-cancel icon-xl"></span><br><strong>'.__("Can't insert answer!","bluerabbit").'</strong></h1> <h3><strong>'.__("Please retry the challenge","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';
        }
        echo json_encode($data);
        die();
    }

    public function gradeChallenge($attempt_id=0){
        global $wpdb; $current_user = wp_get_current_user();

        $challenge_id = $_POST['challenge_id'];
        $att_id = $_POST['attempt_id'];
        $adventure_id = $_POST['adventure_id'];
        $data = array();
        $data['success']=false;
        $player_answers = 0;
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');

        $challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$challenge_id AND adventure_id=$adv_parent_id AND quest_status='publish'");
        $qs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions a WHERE quest_id=$challenge_id AND question_status='publish'");
        //// Esta consulta trae solo las respuestas correctas del intento. no encuentra respuestas erroneas.
        $single_questions = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}br_challenge_answers answers
            JOIN {$wpdb->prefix}br_challenge_attempt_answers p_answers
            ON answers.answer_id = p_answers.answer_id
            WHERE answers.quest_id=$challenge_id AND p_answers.attempt_id=$att_id AND answers.answer_status='publish' AND answers.answer_correct=1 AND p_answers.player_id=$current_user->ID
        ");
        $player_answers += count($single_questions);
        // Esta Consulta trae la lista de respuestas correctas que hay que comparar con los valores que el jugador ingreso
        $multiple_questions = $wpdb->get_results("
            SELECT answers.* FROM {$wpdb->prefix}br_challenge_answers answers
            JOIN {$wpdb->prefix}br_challenge_questions questions
            ON questions.question_id = answers.question_id
            WHERE questions.quest_id=$challenge_id AND questions.question_status='publish' AND answers.answer_status='publish' AND questions.question_type='multiple' AND answers.answer_correct=1
        ");
        //RESPUESTAS DEL JUGADOR
        $multiple_answers_by_player = $wpdb->get_results(" SELECT answer_value, question_id FROM {$wpdb->prefix}br_challenge_attempt_answers WHERE attempt_id=$att_id AND answer_id<1 AND player_id=$current_user->ID");
        $mqs = array();
        foreach($multiple_questions as $mq){
            $mqs[$mq->question_id][]=$mq->answer_id;
        }
        foreach($multiple_answers_by_player as $map){
            $myAnswers=(explode(",",$map->answer_value));
            sort($myAnswers);
            sort($mqs[$map->question_id]);
            if($myAnswers==$mqs[$map->question_id]){
                $player_answers++;
            }
        }
        if($challenge->mech_questions_to_display > count($qs) || $challenge->mech_questions_to_display <=0){
            $display = count($qs);
        }else{
            $display = $challenge->mech_questions_to_display;
        }
        $atw = $challenge->mech_answers_to_win > count($qs) ? count($qs) : $challenge->mech_answers_to_win;
        $grade = round($player_answers*100/$display);
        $updateAttempt = "UPDATE {$wpdb->prefix}br_challenge_attempts SET attempt_status=%s, attempt_answers=%s, attempt_grade=%d WHERE player_id=%d AND quest_id=%d AND attempt_id=%d";
        if($player_answers >= $atw){
            $attempt_status = 'success';
            if($challenge->mech_item_reward){
                $my_item_rew = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND object_id=$challenge->mech_item_reward AND trnx_status='publish'");
                $item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$challenge->mech_item_reward AND item_status='publish'");
                if($my_item_rew){
                    $data['message'] .= '<h4 class="lime-500"><span class="icon icon-achievement"></span> <strong>'.__("Reward already in backpack!","bluerabbit").'</strong></h4>';
                }else{
                    $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified)
                    VALUES (%d, %d, %d, %d, %d, %s, %s, %s)";
                    $sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $challenge->mech_item_reward, $current_user->ID, 0, $item_reward->item_type, $today, $today);
                    $sql = $wpdb->query($sql);
                    $data['message'] .= '<h4 class="lime-500"><span class="icon icon-achievement"></span> <strong>'.__("Obtained an item!","bluerabbit").'</strong></h4>';
                    BR_Activity::instance()->logActivity($adv_child_id,'earned','item-reward',"",$challenge->quest_id, $item_reward->item_id);
                }
            }
            if($challenge->mech_achievement_reward){

                $achievement_reward = $wpdb->get_row("SELECT ach.*, p_ach.achievement_applied
                FROM {$wpdb->prefix}br_achievements ach
                LEFT JOIN {$wpdb->prefix}br_player_achievement p_ach
                ON p_ach.achievement_id=ach.achievement_id AND p_ach.player_id=$current_user->ID AND p_ach.adventure_id=$adv_child_id

                WHERE achievement_id=$challenge->mech_achievement_reward");

                if($achievement_reward->achievement_applied){
                    $data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Achievement already earned!","bluerabbit").'</strong></h4>';
                }else{
                    $sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
                    VALUES (%d, %d, %d, %s)";
                    $sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $challenge->mech_achievement_reward, $today);
                    $sql = $wpdb->query($sql);
                    $data['message'] .= '<h4 class="purple-400"><span class="icon icon-achievement"></span> <strong>'.__("Earned an Achievement!","bluerabbit").'</strong></h4>';
                    BR_Activity::instance()->logActivity($adventure_id,'earned','achievement-reward',"",$challenge->quest_id, $achievement_reward->achievement_id);
                }
            }
            $updateAttempt = $wpdb->query($wpdb->prepare($updateAttempt, $attempt_status, $player_answers, $grade ,$current_user->ID, $challenge_id, $att_id));

            BR_Activity::instance()->registerPost($challenge->quest_id, $adv_child_id, $type="challenge");

            BR_Activity::instance()->logActivity($adv_child_id,'complete','challenge',"", $challenge->quest_id, $att_id);

            $playerState = BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
            $adv_settings = BR_Config::instance()->getSettings($adv_child_id);

            $xp_label = $adventure->adventure_xp_label ? $adventure->adventure_xp_label : "XP";
            $bloo_label = $adventure->adventure_bloo_label ? $adventure->adventure_bloo_label : "BLOO";
            $ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";

            $theFile = (get_template_directory()."/completed-challenge.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }else{
            $attempt_status = 'fail';
            $updateAttempt = $wpdb->query($wpdb->prepare($updateAttempt, $attempt_status, $player_answers, $grade ,$current_user->ID, $challenge_id, $att_id));
            BR_Activity::instance()->logActivity($adv_child_id,'failed','challenge',"",$challenge->quest_id, $att_id);
            BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
            $theFile = (get_template_directory()."/failed-challenge.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
            die();
        }
        $data['success']=true;
        $player_id = $current_user->ID;
        BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
        echo json_encode($data);
        die();
    }

    public function getChallenge($challenge_id, $adv_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = array();
        $challenge = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests
        WHERE quest_id=$challenge_id AND quest_status='publish' AND quest_type='challenge'");


        $adventure = BR_Adventure::instance()->getAdventure($adv_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        $player = BR_Player::instance()->getPlayerAdventureData($adv_child_id, $current_user->ID);



        $attempts = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}br_challenge_attempts` WHERE `quest_id`=$challenge_id AND `player_id`=$current_user->ID AND attempt_status !='trash' AND adventure_id=$adv_child_id");
        $result['challenge']=$challenge;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }

        $requirements = $wpdb->get_results("SELECT

        a.req_object_id, a.req_type, a.req_object_id,
        b.mech_level, b.mech_xp, b.mech_bloo,
        c.quest_title, c.quest_type,
        d.item_name

        FROM {$wpdb->prefix}br_reqs a
        LEFT JOIN {$wpdb->prefix}br_quests b
        ON a.quest_id = b.quest_id AND b.quest_status='publish'
        LEFT JOIN {$wpdb->prefix}br_quests c
        ON a.req_object_id = c.quest_id AND c.quest_status='publish'
        LEFT JOIN {$wpdb->prefix}br_items d
        ON a.req_object_id = d.item_id

        WHERE a.adventure_id=$adv_parent_id AND a.quest_id=$challenge_id

        ");
        if($requirements){
            $reqs = array(); $reqs_ids = array();
            foreach($requirements as $r){
                $reqs[]=$r;
                $reqs_ids[$r->req_type][]=$r->req_object_id;
            }
            sort($reqs_ids['quest']);
            $reqs_list = implode(",",$reqs_ids['quest']);

            $player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
            WHERE adventure_id=$adv_child_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");

            sort($player_quests);

            $my_items = getMyItems($adv_child_id);
            if(isset($my_items['ids']['key'])){
                $myKeyItems = $my_items['ids']['key'];
                $allItemsSet = array_intersect($myKeyItems,$reqs_ids['item']);
            }else{
                $allItemsSet = [];
            }
            $allQuestsSet = array_intersect($player_quests, $reqs_ids['quest']);
            if($allQuestsSet == $reqs_ids['quest']){ $questsReady=true;}
            if($allItemsSet == $reqs_ids['item']){ $itemsReady=true;}
            if($questsReady && $itemsReady ){
                // $reqs ok
            }else{
                $result['unavailable']=true;
                $result['locks']['requirements']=true;
                $result['message']="<span class='icon icon-xl icon-lock solid-red'></span>";
                $result['message'].="<h1><strong>".__("Requierements not met!","bluerabbit")."</strong></h1>";
                $result['message'].="<h3>".__("Can't attempt this challenge. Finish other quests and come back!","bluerabbit")."</h3>";
            }
        }
        /// Check for MAX grade
        $best_grade=array();
        $fails=0;
        foreach($attempts as $att){
            if($att->attempt_status != 'success'){
                $fails++;
            }else{
                $best_grade[]=$att->attempt_grade;
            }
        }
        $questions = $wpdb->get_results("SELECT
        a.question_id, a.question_title, a.question_image, a.question_type
        FROM {$wpdb->prefix}br_challenge_questions a
        WHERE a.quest_id=$challenge_id AND a.question_status='publish'");
        $maxGrade = count($best_grade) > 0 ? max($best_grade) : 0;
        $result['fails']=$fails;
        $result['attempts']=$attempts;
        $result['freeAttempts']=$challenge->mech_free_attempts - count($attempts);
        $result['questions']=$questions;
        if($player->player_level >= $challenge->mech_level){
            $today = date('YmdHi');
            $deadline = date('YmdHi',strtotime($challenge->mech_deadline));
            if($challenge->mech_deadline == '0000-00-00 00:00:00' || $deadline >= $today || $challenge->trnx_id){
                if($fails < $challenge->mech_max_attempts || $challenge->mech_max_attempts <=0){
                    if($maxGrade >=100){
                        $result['locks']['conquered']=true;
                        $result['message']="<span class='icon icon-xl icon-challenge'></span><h1>".__("Challenge Already Conquered!","bluerabbit")."</h1>";
                        $result['location']=get_bloginfo('url')."/post/?adventure_id=$adv_child_id&questID=$challenge_id";
                    }
                    $answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_answers WHERE quest_id=$challenge_id AND answer_status='publish'");
                    $result['answers']=$answers;
                }else{
                    $result['unavailable']=true;
                    $result['locks']['max_attempts']=true;
                    $result['message']="<span class='icon icon-xl icon-lock solid-red'></span>";
                    $result['message'].="<h1><strong>".__("Max Attempts Reached!","bluerabbit")."</strong></h1>";
                    $result['message'].="<h3>".__("Can't attempt this challenge anymore. Keep moving forward!","bluerabbit")."</h3>";
                }
            }else{
                $result['unavailable']=true;
                $result['locks']['deadline']=true;
                if($challenge->mech_deadline_cost > 0){
                    $result['locks']['deadline_cost']=true;
                }
            }
        }else{
            $result['unavailable']=true;
            $result['locks']['level']=true;
            $result['message']="<span class='icon icon-xl icon-level solid-purple'></span><h1>".__("Available for level","bluerabbit")."<strong>$challenge->mech_level</strong></h1>";
        }
        return $result;
    }
}

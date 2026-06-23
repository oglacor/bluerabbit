<?php
class BR_Survey {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function submitSurveyAnswer(){
        global $wpdb; $current_user = wp_get_current_user();

        $survey_id = $_POST['survey_id'];
        $question_id = $_POST['question_id'];
        $option_id = $_POST['option_id'];
        $text_value = $_POST['value'];
        $adventure_id = $_POST['adventure_id'];
        $data = array();
        $data['success']=false;
        $adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        $answerInsert= "INSERT INTO {$wpdb->prefix}br_survey_answers (player_id, adventure_id, survey_id, survey_question_id, survey_option_id, survey_answer_value, survey_answer_date, survey_answer_modified) VALUES (%d,%d,%d,%d,%d,%s,%s,%s)
        ON DUPLICATE KEY UPDATE survey_option_id=%d, survey_answer_value=%s,survey_answer_modified=%s, adventure_id=%d ";
        $answer = $wpdb->query($wpdb->prepare($answerInsert, $current_user->ID, $adv_child_id, $survey_id, $question_id, $option_id, $text_value, $today, $today, $option_id, $text_value, $today, $adv_child_id));

        //check all answers are submitted
        $all_player_answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_answers WHERE player_id=$current_user->ID AND adventure_id=$adv_child_id AND survey_id=$survey_id");
        $all_survey_questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_survey_questions WHERE survey_id=$survey_id AND survey_question_status='publish'");

        if(count($all_player_answers) >= count($all_survey_questions)){
            //insert in player posts
            $sql = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_type, pp_status)
            VALUES (%d, %d, %d, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
            pp_modified=%s, pp_status=%s";
            $sql = $wpdb->prepare($sql, $survey_id, $adv_child_id, $current_user->ID, $today, $today, 'survey', 'publish', $today, 'publish');
            $insert = $wpdb->query($sql); $new_pp_id = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adv_child_id, 'survey-complete','survey',"",$survey_id);
        }
        BR_Activity::instance()->logActivity($adv_child_id, 'submit','survey-answer',"",$survey_id);
        $notification = new Notification();

        if($answer){
            $data['success']=true;
            $msg_content = __('Answer Saved!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;
        }else{
            $msg_content = __('Please try again','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    public function getSurvey($survey_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = array();
        $answers_query = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}br_survey_answers` WHERE `survey_id`=$survey_id AND `player_id`=$current_user->ID ");



        $survey = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$survey_id AND quest_type='survey' AND quest_status='publish'");

        $all_qs = $wpdb->get_results("SELECT a.*, b.survey_option_id, b.survey_option_text, b.survey_option_image
        FROM {$wpdb->prefix}br_survey_questions a
        LEFT JOIN {$wpdb->prefix}br_survey_options b
        ON a.survey_question_id = b.survey_question_id AND b.survey_option_status='publish'
        WHERE a.survey_id=$survey_id AND a.survey_question_status='publish' ORDER BY a.survey_question_order, a.survey_question_id
        ");
        $answers = array();
        foreach ($answers_query as $an){
            $answers['a_'.$an->survey_question_id]['selected_answer'] = $an->survey_option_id;
            $answers['a_'.$an->survey_question_id]['survey_answer_value'] = $an->survey_answer_value;
        }
        $questions = array();
        foreach($all_qs as $kq=>$qs){
            $questions[$qs->survey_question_id]['text']=$qs->survey_question_text;
            $questions[$qs->survey_question_id]['image']=$qs->survey_question_image;
            $questions[$qs->survey_question_id]['range']=$qs->survey_question_range;
            $questions[$qs->survey_question_id]['survey_question_description']=$qs->survey_question_description;
            $questions[$qs->survey_question_id]['survey_question_display']=$qs->survey_question_display;
            $questions[$qs->survey_question_id]['survey_question_type']=$qs->survey_question_type;
            $questions[$qs->survey_question_id]['order']=$qs->survey_question_order;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['text']=$qs->survey_option_text;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['image']=$qs->survey_option_image;
            if(isset($answers['a_'.$qs->survey_question_id])){
                $questions[$qs->survey_question_id]['survey_answer_value']=$answers['a_'.$qs->survey_question_id]['survey_answer_value'];
                $questions[$qs->survey_question_id]['selected_answer']=$answers['a_'.$qs->survey_question_id]['selected_answer'];
            }
        }
        $result['survey']=$survey;
        $result['questions']=$questions;
        $result['answers']=$answers;
        return $result;
    }

    public function getSurveyResults($survey_id){
        global $wpdb; $current_user = wp_get_current_user();

        $result = array();
        $answers = $wpdb->get_results("SELECT a.*, b.player_first, b.player_last FROM `{$wpdb->prefix}br_survey_answers` a
        LEFT JOIN `{$wpdb->prefix}br_players` b
        ON a.player_id = b.player_id

        WHERE a.survey_id=$survey_id");

        $survey = $wpdb->get_row("SELECT
        a.quest_id, a.quest_title, a.adventure_id, a.achievement_id, a.mech_level, a.mech_xp, a.mech_bloo, a.mech_ep, a.mech_badge, a.mech_deadline, a.mech_start_date,
        c.player_id, c.player_level,
        ach.achievement_name
        FROM {$wpdb->prefix}br_quests a
        LEFT JOIN {$wpdb->prefix}br_player_adventure c
        ON a.adventure_id=c.adventure_id AND c.player_id=$current_user->ID

        LEFT JOIN {$wpdb->prefix}br_achievements ach
        ON a.achievement_id = ach.achievement_id AND ach.achievement_status='publish'


        WHERE a.quest_id=$survey_id AND a.quest_type='survey' AND c.player_id=$current_user->ID AND c.player_adventure_status='in'");

        $all_qs = $wpdb->get_results("
        SELECT a.*, b.survey_option_id, b.survey_option_text, b.survey_option_image
        FROM {$wpdb->prefix}br_survey_questions a
        LEFT JOIN {$wpdb->prefix}br_survey_options b
        ON a.survey_question_id = b.survey_question_id AND b.survey_option_status='publish'

        WHERE a.survey_id=$survey_id AND a.survey_question_status='publish'	ORDER BY a.survey_question_type, a.survey_question_order, a.survey_question_id
        ");
        $answer_values = array();
        foreach($answers as $a){
            $answer_values[$a->survey_question_id][$a->survey_option_id]++;
            $answer_values[$a->survey_question_id]['total']++;
            $answer_values[$a->survey_question_id]['values'][]=$a->survey_answer_value;
            $answer_values[$a->survey_question_id]['player'][]=$a->player_first." ".$a->player_last;
        }
        $questions = array();
        foreach($all_qs as $kq=>$qs){
            $questions[$qs->survey_question_id]['text']=$qs->survey_question_text;
            $questions[$qs->survey_question_id]['image']=$qs->survey_question_image;
            $questions[$qs->survey_question_id]['range']=$qs->survey_question_range;
            $questions[$qs->survey_question_id]['survey_question_display']=$qs->survey_question_display;
            $questions[$qs->survey_question_id]['survey_question_description']=$qs->survey_question_description;
            $questions[$qs->survey_question_id]['survey_question_type']=$qs->survey_question_type;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['text']=$qs->survey_option_text;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['image']=$qs->survey_option_image;
            $average = round($answer_values[$qs->survey_question_id][$qs->survey_option_id]/$answer_values[$qs->survey_question_id]['total']*100);
            $average = is_nan($average) ? 0 : $average;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['value']=$average;
            $questions[$qs->survey_question_id]['options'][$qs->survey_option_id]['total_answers']=($answer_values[$qs->survey_question_id][$qs->survey_option_id]);
        }

        $result['survey']=$survey;
        $result['questions']=$questions;
        $result['answers']=$answers;
        $result['answer_values']=$answer_values;
        return $result;
    }
}

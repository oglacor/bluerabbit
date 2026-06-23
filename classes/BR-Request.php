<?php
class BR_Request {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function submitRequest(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $n = new Notification();
        $data['just_notify'] =true;
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'br_request_nonce')){
            $adventure_id = intval($_POST['adventure_id']);
            $subject = sanitize_text_field($_POST['request_subject']);
            $content = stripslashes_deep($_POST['request_content']);

            if($subject && $content){
                $sql = "INSERT INTO {$wpdb->prefix}br_requests
                    (adventure_id, player_id, request_subject, request_content)
                    VALUES (%d, %d, %s, %s)";
                $result = $wpdb->query($wpdb->prepare($sql, $adventure_id, $current_user->ID, $subject, $content));

                if($wpdb->insert_id){
                    BR_Activity::instance()->logActivity($adventure_id, 'submitted-request', 'request', $subject);
                    $data['success'] = true;
                    $msg_content = __('Your request has been sent!','bluerabbit');
                    $data['message'] = $n->pop($msg_content,'green','check');
                }
            }else{
                $msg_content = __('Please fill in all fields!','bluerabbit');
                $data['message'] = $n->pop($msg_content,'red','cancel');
            }
        }
        echo json_encode($data);
        die();
    }

    public function getRequests(){
        global $wpdb;
        $current_user = wp_get_current_user();

        $adventure_id = intval($_POST['adventure_id']);
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';

        $where = "WHERE r.adventure_id = %d";
        $params = array($adventure_id);

        if($status !== 'all'){
            $where .= " AND r.request_status = %s";
            $params[] = $status;
        }

        $sql = "SELECT r.*, p.player_display_name, p.player_picture, p.player_email
            FROM {$wpdb->prefix}br_requests r
            LEFT JOIN {$wpdb->prefix}br_players p ON r.player_id = p.player_id
            $where
            ORDER BY r.request_date DESC";

        $requests = $wpdb->get_results($wpdb->prepare($sql, ...$params));

        foreach($requests as $req){
            include(get_template_directory().'/request-row.php');
        }
        die();
    }

    public function getMyRequests(){
        global $wpdb;
        $current_user = wp_get_current_user();

        $adventure_id = intval($_POST['adventure_id']);
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';

        $where = "WHERE r.adventure_id = %d AND r.player_id = %d";
        $params = array($adventure_id, $current_user->ID);

        if($status !== 'all'){
            $where .= " AND r.request_status = %s";
            $params[] = $status;
        }

        $sql = "SELECT r.*
            FROM {$wpdb->prefix}br_requests r
            $where
            ORDER BY r.request_date DESC";

        $requests = $wpdb->get_results($wpdb->prepare($sql, ...$params));

        foreach($requests as $req){
            include(get_template_directory().'/my-request-row.php');
        }

        if(empty($requests)){
            echo '<p class="font _16 grey-400 text-center padding-20">' . __("You haven't sent any requests yet","bluerabbit") . '</p>';
        }
        die();
    }

    public function updateRequestStatus(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;

        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'br_request_nonce')){
            $request_id = intval($_POST['request_id']);
            $new_status = sanitize_text_field($_POST['new_status']);
            $admin_note = isset($_POST['admin_note']) ? stripslashes_deep($_POST['admin_note']) : '';

            $update_data = array(
                'request_status' => $new_status,
                'request_admin_note' => $admin_note,
                'request_resolved_by' => $current_user->ID,
                'request_resolved_date' => current_time('mysql')
            );

            $result = $wpdb->update(
                "{$wpdb->prefix}br_requests",
                $update_data,
                array('request_id' => $request_id),
                array('%s','%s','%d','%s'),
                array('%d')
            );

            if($result !== false){
                $data['success'] = true;
                $data['message'] = __("Request updated","bluerabbit");
            }
        }
        echo json_encode($data);
        die();
    }
}

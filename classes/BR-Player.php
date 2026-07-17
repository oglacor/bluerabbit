<?php
class BR_Player {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    ////////////////////////   ADD NEW PLAYER //////////////////////////////
    public function bluerabbit_add_new_player($new_player_data=NULL) {
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $config = BR_Config::instance()->getSysConfig();
        $main_logo = $config['main_logo']['value'];
        $restrict_domain =$config['restrict_domain']['value'];
        $notification = new Notification();

        if($new_player_data){
            $user_nickname	= trim($new_player_data["nickname"]);
            $user_email		= strtolower($new_player_data["email"]);
            $user_pass		= $new_player_data["password"];
            $user_lang		= $new_player_data["lang"];
            $display_name	= $new_player_data["nickname"];
            $player_first	= $new_player_data["firstname"];
            $player_last	= $new_player_data["lastname"];

            $gender	= $new_player_data["gender"];
            $work_level	= $new_player_data["work_level"];
            $work_function	= $new_player_data["work_function"];
            $work_sub_function	= $new_player_data["work_sub_function"];
            $job_profile	= $new_player_data["job_profile"];
            $buisness_pillar	= $new_player_data["buisness_pillar"];
            $work_cluster	= $new_player_data["work_cluster"];
            $work_country	= $new_player_data["work_country"];
            $work_location	= $new_player_data["work_location"];

            $default_adventure	= $new_player_data["adventure_id"];
            $nonce = $new_player_data['nonce'];
        }else{
            $user_nickname	= trim($_POST["nickname"]);
            $user_email		= strtolower($_POST["email"]);
            $user_pass		= $_POST["password"];

            $user_lang		= $_POST["lang"];
            $redirect		= $_POST["redirect"];
            $display_name	= $_POST["nickname"];
            $default_adventure = $config['default_adventure']['value'];
            $nonce = $_POST['nonce'];
        }

        if (wp_verify_nonce($nonce, 'br_register_nonce')) {
            require_once(ABSPATH . WPINC . '/registration.php');
            $errors = array();

            if($restrict_domain > 0){
                $validate_user_email = explode("@",$user_email);
                $valid_domain =$config['restrict_domain_url']['value'];
                if($validate_user_email[1] != $valid_domain){
                    $errors[] = __("This domain is not allowed","bluerabbit")."";
                }
            }

            if(isset($valid_emails) && !in_array($user_email, $valid_emails)){
                $errors[] = __("This email is not in the RSVP list","bluerabbit");
            }
            if($user_nickname=='') {
                $errors[] = __('Please type a nickname',"bluerabbit");
            }elseif(!validate_username($user_nickname)) {
                $errors[] = __('Type in a valid nickname',"bluerabbit");
            }elseif(username_exists($user_nickname)) {
                $errors[] = __('Username already taken',"bluerabbit");
            }

            if($user_email == '') {
                $errors[] = __('Please enter an email',"bluerabbit");
            }elseif(!is_email($user_email)) {
                $errors[] = __("Wrong email format","bluerabbit");
            }elseif(email_exists($user_email)) {
                $errors[] = __("Email is already registered","bluerabbit");
            }
            if($user_pass == '') {
                $errors[] = __("Type in a password","bluerabbit");
            }elseif(strlen($user_pass) > 50) {
                $errors[] = __("Password can't be longer than 50 characters","bluerabbit");
            }
            if(empty($errors)) {
                $new_user_id = wp_insert_user(array(
                    'user_login'		=> $user_nickname,
                    'user_pass'	 		=> $user_pass,
                    'user_email'		=> $user_email,
                    'user_registered'	=> date('Y-m-d H:i:s'),
                    'display_name'		=> $display_name,
                    'role'				=> 'br_player'
                ));
                if($new_user_id) {
                    $profile_pic_default = get_bloginfo('template_directory')."/images/no-profile.png";
                    $new_player_sql="INSERT INTO {$wpdb->prefix}br_players
                    (`player_id`, `player_email`, `player_password`, `player_display_name`, `player_lang`, `player_picture`, `player_nickname`, `player_first`, `player_last`)
                    VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s)";
                    $new_player = $wpdb->query($wpdb->prepare($new_player_sql, $new_user_id, $user_email,'none', $user_nickname, $user_lang, $profile_pic_default, $user_nickname, $player_first, $player_last ));

                    $new_player_meta_sql="INSERT INTO {$wpdb->prefix}br_player_meta
                    (`player_id`, `player_gender`,`work_level`,`work_function`,`work_sub_function`,`job_profile`,`business_pillar`,`work_cluster` ,`work_country`,`work_location`)
                    VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
                    $new_player_meta = $wpdb->query($wpdb->prepare($new_player_meta_sql, $new_user_id, $gender,$work_level,$work_function,$work_sub_function,$job_profile,$buisness_pillar,$work_cluster,$work_country,$work_location));



                    if($default_adventure>0){
                        $adventure = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$default_adventure");
                        if($adventure){
                            $sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d)";
                            $sql = $wpdb->prepare ($sql,$adventure->adventure_id,$new_user_id);
                            $wpdb->query($sql);
                            if($wpdb->insert_id){
                                if($adventure->adventure_has_guilds){
                                    BR_Guild::instance()->assignGuild($new_user_id, $adventure->adventure_id);
                                }
                            }
                        }
                    }
                    if($new_player_data){
                        $n = new Notification();
                        $msg_content = __('User Registered successfully!','bluerabbit');
                        $data['message'] = $n->pop($msg_content,'green');
                        $data['success'] = true;
                        $data['added-user'] = true;
                        BR_Activity::instance()->logActivity($default_adventure,'registered','new-player');
                    }else{
                        $creds['user_login'] = $user_name;
                        $creds['user_password'] = $user_pass;
                        $creds['remember'] = true;
                        $user = wp_signon($creds, false);
                        wp_clear_auth_cookie();
                        wp_set_current_user ( $new_user_id );
                        wp_set_auth_cookie  ( $new_user_id );

                        wp_new_user_notification($new_user_id);
                        update_user_meta($new_user_id,"locale",$user_lang);
                        BR_Activity::instance()->logActivity(0,'registered','new-player');

                        $logo = $main_logo ? $main_logo :  get_bloginfo('template_directory')."/images/logo.png";
                        $message = "<div class='text-center'><img src='$logo' width='200'></div>";
                        $message .= "<h1>".__('New Player Registered Successfully','bluerabbit')."</h1><h4>".__('(click to continue)','bluerabbit')."</h4>";
                        $data['message'] = $message;
                        $data['location'] = $redirect ? $redirect : get_bloginfo('url').'/adventures/';
                        $data['success'] = true;
                    }
                }else{
                    $data['message'] = "<h1>".__("There was an error, please reload and try again","bluerabbit")."</h1>";
                    $data['location'] = get_bloginfo('url');
                }
            }else{
                $data['just_notify'] =true;
                $data['errors'] = $errors;
                foreach($errors as $e){
                    $data['messages'][] = $notification->pop($e,'red','cancel');
                }
                $data['success'] = false;
            }
        }
        if($new_player_data){
            return $data;
        }else{
            echo json_encode($data);
            die();
        }
    }

    public function uploadBulkUsers(){
        global $wpdb;
        $data = array();
        $n = new Notification();
        $adv_id = $_POST['adventure_id'];
        if (isset($_FILES['csv_file']['tmp_name'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            if (!is_readable($file)) {
                $data['errors'][] = __("File not readable.","bluerabbit");
            }
            if (empty($file) || !file_exists($file)) {
                $data['errors'][] = __("No file uploaded.","bluerabbit");
            }
            if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
                while (($file_data = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($row_index == 0) {
                        // Skip the header row (optional)
                        $row_index++;
                        continue;
                    }
                    if($row_index <=50){
                        // Assuming the CSV file has columns: name, email, age
                        $nickname = sanitize_text_field($file_data[0]);
                        $password = sanitize_text_field($file_data[1]);
                        $email = sanitize_email($file_data[2]);
                        $firstname = sanitize_text_field($file_data[3]);
                        $lastname = sanitize_text_field($file_data[4]);
                        $lang = sanitize_text_field($file_data[5]);

                        $gender = sanitize_text_field($file_data[8]);
                        $work_level = sanitize_text_field($file_data[9]);
                        $work_function = sanitize_text_field($file_data[10]);
                        $work_sub_function = sanitize_text_field($file_data[11]);
                        $job_profile = sanitize_text_field($file_data[12]);
                        $buisness_pillar = sanitize_text_field($file_data[13]);
                        $work_cluster = sanitize_text_field($file_data[14]);
                        $work_country = sanitize_text_field($file_data[15]);
                        $work_location = sanitize_text_field($file_data[16]);


                        $errors='';
                        unset($data['file_errors']);

                        $enrolled  = false;
                        $registered  = false;
                        if(is_email($email)) {
                            $email_exists = get_user_by('email',$email);
                            if($email_exists){
                                $enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$email_exists->ID AND adventure_id=$adv_id AND player_adventure_status='in'");
                                if($enrolled){
                                    $data['file_errors']['enrolled'] = __("User already enrolled","bluerabbit");
                                    $enrolled = true;
                                }else{
                                    $data['file_errors']['email_taken']  = __("Email already registered","bluerabbit");
                                    if(!$registered) {
                                        $registered = $email_exists;
                                    }
                                }
                            }
                        }else{
                            $data['file_errors']['email_format'] = __("Wrong email format","bluerabbit");;
                        }
                        $username_exists = get_user_by('login',$nickname);
                        if($username_exists){
                            $enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$username_exists->ID AND adventure_id=$adv_id AND player_adventure_status='in'");
                            if($enrolled){
                                $data['file_errors']['enrolled'] = __("User already enrolled","bluerabbit");
                                $enrolled = true;
                            }else{
                                $data['file_errors']['username_taken']  = __("Nickname already registered","bluerabbit");
                                if(!$registered) {
                                    $registered = $username_exists;
                                }
                            }
                        }
                        if($data['file_errors']){
                            $errors = "<span class='icon icon-warning font _16'></span> ";
                            $errors .= implode(" | ",$data['file_errors']);
                            if($enrolled){
                                $bg_color = "grey-600 grey-bg-200 avoid";
                            }else{
                                if($registered){
                                    $bg_color = "amber-bg-200 enroll";
                                }
                            }
                        }else{
                            $bg_color = "green-bg-100 register";
                        }
                        // Insert data into the database
                        $data['users'][]=array(
                            'nickname' => $nickname,
                            'password' => $password,
                            'email' => $email,
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                        );
                        if($enrolled){
                            $checkbox_input = "";
                        }elseif($registered){
                            $checkbox_input = "<input type='checkbox' class='select-element' id='select-new-bulk-user-$row_index' data-id='$row_index' data-user-id='$registered->ID'>";
                        }else{
                            $checkbox_input = "<input type='checkbox' class='select-element' checked id='select-new-bulk-user-$row_index' data-id='$row_index'>";
                        }

                        $table_row .= "
                        <tr class='$bg_color row-new-bulk-user' id='row-new-bulk-user-$row_index'>
                            <td>$checkbox_input</td>
                            <td class='nickname'>$nickname</td>
                            <td class='password'>$password</td>
                            <td class='email'>$email</td>
                            <td class='firstname'>$firstname</td>
                            <td class='lastname'>$lastname</td>
                            <td class='lang'>$lang</td>

                            <td class='gender'>$gender</td>
                            <td class='work_level'>$work_level</td>
                            <td class='work_function'>$work_function</td>
                            <td class='work_sub_function'>$work_sub_function</td>
                            <td class='job_profile'>$job_profile</td>
                            <td class='buisness_pillar'>$buisness_pillar</td>
                            <td class='work_cluster'>$work_cluster</td>
                            <td class='work_country'>$work_country</td>
                            <td class='work_location'>$work_location</td>
                            <td class='font w700'>$errors</td>
                        </tr>
                        ";

                        $row_index++;
                    }else{
                    }
                }

                fclose($handle);
                if($row_index >= 50){
                    $cta .= "<h2 class='font _18 w600 deep-orange-400'>".__("File limited to 50 players. Upload a new file if you need to add more users.","bluerabbit")."</h2>";
                }
                if($data['file_errors']){
                    $cta .= "<h2 class='font _18 w600 red-400'>".__("There are some errors in your file.","bluerabbit")."</h2>";
                    $cta .= "<h4 class='font _14 w300 blue-grey-600'>".__("You can upload the rows that are in green or you can fix your file and try again.","bluerabbit")."</h4>";
                    if($registered){
                        $cta .= "<h4 class='font _14 w300 amber-bg-400 blue-grey-600 padding-5 margin-5'>".__("Users in yellow are registered but not enrolled, they will be added to this adventure if you select them","bluerabbit")."</h4>";
                    }

                }
                $cta .= "<br><button class='form-ui green-bg-400 font _24' onClick='bulkEnrollUsers();'>".__("Insert users")."</button>";
                $msg_content = __("Users listed correctly",'bluerabbit');
                $data['table_content'] = $table_row;
                $data['cta'] = $cta;
                $data['messages'][] = $n->pop($msg_content,'green','check');
                $data['success'] = true;
            }else{
                $data['errors'][] =__("Cannot open file to read","bluerabbit");
            }
        }else{
            $data["errors"][] = __("File doesn't exist","bluerabbit");
        }


        echo json_encode($data);
        die();
    }

    public function bulkEnrollUsers(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $data['just_notify']=true;
        $create_new_users = $_POST['new_users'];
        $enroll_new_users = $_POST['existing_users'];
        $adventure_id = $_POST['adventure_id'];
        $n = new Notification();
        if($enroll_new_users || $create_new_users){
            if($create_new_users){
                foreach($create_new_users as $key=>$nu){
                    $new_player_data = [
                        "nickname"	=> $nu["nickname"],
                        "email"		=> strtolower($nu["email"]),
                        "password"	=> $nu["password"],
                        "firstname"	=> $nu["firstname"],
                        "lastname"	=> $nu["lastname"],
                        "lang"		=> $nu["lang"],
/////////////////////////////////////////////////////////////////////////////
                        "gender"		=> $nu["gender"],
                        "work_level"		=> $nu["work_level"],
                        "work_function"		=> $nu["work_function"],
                        "work_sub_function"		=> $nu["work_sub_function"],
                        "job_profile"		=> $nu["job_profile"],
                        "buisness_pillar"		=> $nu["buisness_pillar"],
                        "work_cluster"		=> $nu["work_cluster"],
                        "work_country"		=> $nu["work_country"],
                        "work_location"		=> $nu["work_location"],

                        "adventure_id"	=> $adventure_id,
                        'nonce' => wp_create_nonce('br_register_nonce'),
                    ];
                    $new_player = $this->bluerabbit_add_new_player($new_player_data);
                    $data['messages'][] = $new_player['message'];
                }
            }
            if($enroll_new_users){
                foreach($enroll_new_users as $key=>$nu){
                    $player = get_user($nu['user_id']);
                    if($player){
                        $pData = [
                            'adventure_id'=>$adventure_id,
                            'player_id'=>$player->ID,
                            'status'=>'in',
                            'nonce'=>wp_create_nonce('br_player_adventure_status_nonce'),
                        ];
                        $new_enroll = $this->updatePlayerAdventureStatus($pData);
                        $msg_content = __("User enrolled",'bluerabbit');
                        $data['messages'][] = $n->pop($msg_content,'green','check');
                    }else{
                        $msg_content = __("Invalid user data",'bluerabbit');
                        $data['messages'][] = $n->pop($msg_content,'red','cancel');
                    }
                }
            }
            $data['reload']=true;
        }else{
            $data['message']= __("No users were selected","bluerabbit");
            $data['success'] = false;
        }
        echo json_encode($data);
        die();
    }

    public function enrollUser() {
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = array();
        $adventure_id = $_POST['adventure_id'];
        $user_nickname = $_POST["nickname"];
        $user_email = strtolower($_POST["email"]);
        $user_pass = $_POST["password"];
        $user_lang = $_POST["lang"];
        $new_user = $_POST["new_user"];
        $nonce = $_POST['nonce'];
        $data['just_notify'] = true;
        $n = new Notification();


        /////////////// ADD SECURITY >>> Current_user must be Admin, GM or NPC of the class.
        $roles = $current_user->roles[0];
        if(isset($current_user) && $roles !='br_player' && wp_verify_nonce($nonce, 'br_create_new_user'.$current_user->ID)){
            $adv = BR_Adventure::instance()->getAdventure($adventure_id);
            if(isset($adv)){
                if($adv->player_adventure_role == 'gm' || $adv->player_adventure_role == 'npc'){
                    if($new_user == 'make-new'){
                        $new_player_data = [
                            "nickname"	=> $user_nickname,
                            "email"		=> $user_email,
                            "password"	=> $user_pass,
                            "lang"		=> $user_lang,
                            "adventure_id"	=> $adventure_id,
                            'nonce' => wp_create_nonce('br_register_nonce'),
                        ];
                        $new_player = $this->bluerabbit_add_new_player($new_player_data);

                        if($new_player['success']){
                            $data['success'] = true;
                            $data['added-user'] = true;
                            $data['message'] = $new_player['message'];
                        }else{
                            $data['messages'] = $new_player['messages'];
                            $data['success'] = false;
                        }
                        BR_Activity::instance()->logActivity($adventure_id,'new-user-registered','new-player');
                    }else if(email_exists($user_email)) {
                        $player = get_user_by('email',$user_email);
                        $pData = [
                            'adventure_id'=>$adventure_id,
                            'player_id'=>$player->ID,
                            'status'=>'in',
                            'nonce'=>wp_create_nonce('br_player_adventure_status_nonce'),
                        ];
                        $new_enroll = $this->updatePlayerAdventureStatus($pData);
                        $msg_content = __("User enrolled",'bluerabbit');
                        $data['message'] = $n->pop($msg_content,'green','check');
                        $data['success'] = true;
                        $data['added-user'] = true;
                        BR_Activity::instance()->logActivity($adventure_id,'manually-enrolled-player','new-player');
                    }else{
                        $msg_content = __("User data is wrong",'bluerabbit');
                        $data['message'] = $n->pop($msg_content,'red','cancel');
                        $data['success'] = false;
                        $data['added-user'] = false;
                    }

                }else{
                    $msg_content = __('Player unauthorized to register users!','bluerabbit');
                    $data['message'] = $n->pop($msg_content,'red','warning');
                    $data['success'] = false;
                    $data['added-user'] = false;
                    BR_Activity::instance()->logActivity($adventure_id,'attempt-manual-registration-from-player','new-player');
                }
            }else{
                $msg_content = __("This adventure doesn't exist",'bluerabbit');
                $data['message'] = $n->pop($msg_content,'red','cancel');
                $data['success'] = false;
                $data['added-user'] = false;
            }
        }else{
            $msg_content = __('Unauthorized access!','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','warning');
            $data['success'] = false;
            $data['added-user'] = false;
            BR_Activity::instance()->logActivity($adventure_id,'attempt-manual-registration','new-player');
        }
        echo json_encode($data);
        die();
    }

    public function br_logout(){
        wp_logout();
        $data['location']=get_bloginfo('url').'/login';
        echo json_encode($data);
        die();
    }

    ////////////////////////////////////// checkUserDataExists //////////////////////////////////
    public function checkUserDataExists(){
        global $wpdb;
        $current_user = wp_get_current_user();

        $data = array();
        $data['success'] = false;
        $data['just_notify'] =true;
        $value = $_POST['value'];
        $adv_id = $_POST['adventure_id'];
        $notification = new Notification();

        ///////////////////// VALIDAR CAMPOS VACIOS
        if(is_email($value)) {
            $user = get_user_by('email',$value);
        }else{
            $user = get_user_by('login',$value);
        }
        $data['new_nonce'] = wp_create_nonce('br_create_new_user'.$current_user->ID);
        if(!empty($user)){
            /// FOUND BY NICKNAME OR EMAIL
            $data['user_exists'] = true;
            $data['user_first_name']=$user->first_name;
            $data['user_last_name']=$user->last_name;
            $data['user_nickname']=$user->user_login;
            $data['user_email']=$user->user_email;

            $enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$user->ID AND adventure_id=$adv_id");
            if(!$enrolled || $enrolled->player_adventure_status =='out'){
                $data['warning'] = __("User exists, not enrolled",'bluerabbit');
                $data['warning_class'] = "warning";
                $data['user_enroll_status']='out';
            }else{
                $data['warning'] = __("User already in adventure",'bluerabbit');
                $data['warning_class'] = "warning";
            }
        }else{
            if(is_email($value)) {
                $data['warning'] = __("Email available",'bluerabbit');
                $data['is_email'] = true;
            }else{
                $data['warning'] = __("Nickname available",'bluerabbit');
                $data['is_nickname'] = true;
            }

            $data['user_exists'] = false;

            $data['warning_class'] = "success";
            $data['user_enroll_status']='new_user';
        }
        $data['success'] = true;
        echo json_encode($data);
        die();
    }

    ////////////////////////////////////// RESET PLAYER //////////////////////////////////
    public function updatePlayer(){
        $data = array();

        $data['success'] = false;

        $adventure_id = $_POST['adventure_id'];
        $player_id = $_POST['player_id'];

        $notification = new Notification();
        $data['just_notify'] =true;

        $playerUpdated = $this->resetPlayer($adventure_id, $player_id);

        if($playerUpdated){
            $msg_content = __('Player updated!','bluerabbit');
            BR_Activity::instance()->logActivity($adventure_id, "update","player","",$player_id);
            $data['message'] = $notification->pop($msg_content,'green','check');
            $data['success'] = true;
        }else{
            $msg_content = __('Error - Please contact a site admin','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
        }

        echo json_encode($data);
        die();
    }

    public function resetPlayerPassword(){
        global $wpdb;
        $data = array();
        $n = new Notification();
        $data['just_notify'] =true;
        $current_user = wp_get_current_user();

        $adventure_id=$_POST['adventure_id'];

        $current_gm_password = $_POST['current_gm_password'];
        $new_player_password = trim($_POST['new_player_password']);
        $new_player_password_confirm = trim($_POST['new_player_password_confirm']);
        $player_affected = $_POST['player_affected'];

        $player_affected = $this->getPlayerData($player_affected);

        $current_gm = $this->getPlayerAdventureData($adventure_id, $current_user->ID);
        $config_auth = BR_Config::instance()->getSysConfig('allow_gm_reset_password');
        if($current_gm->player_adventure_role != 'player'){
            if($config_auth['value'] == 1){
                $pass_check = wp_check_password( $current_gm_password, $current_user->user_pass, $current_user->ID );

                if($pass_check){
                    if(wp_verify_nonce($_POST['nonce'], 'reset_user_password_nonce'.$current_user->ID)) {
                        if($new_player_password === $new_player_password_confirm && $new_player_password !== ''){

                            wp_set_password($new_player_password_confirm, $player_affected->player_id);
                            BR_Activity::instance()->logActivity($adventure_id,'update-player-password','success','new-pwd-set',$player_affected->player_id);
                            $data['success'] = true;
                            $data['message_delay'] = 2000;
                            $msg_content = __('Password updated','bluerabbit');
                            $data['message'] = $n->pop($msg_content,'green','key');
                        }else{
                            BR_Activity::instance()->logActivity($adventure_id,'update-player-password','password-mismatch');
                            $data['success'] = false;
                            $msg_content = __('Password mismatch','bluerabbit');
                            $data['message'] = $n->pop($msg_content,'amber','cancel');
                        }
                    }else{
                        BR_Activity::instance()->logActivity($adventure_id,'update-player-password','wrong-nonce');
                        $data['success'] = false;
                        $msg_content = __('Process timeout','bluerabbit');
                        $data['message'] = $n->pop($msg_content,'red','cancel');
                    }
                }else{
                    BR_Activity::instance()->logActivity($adventure_id,'update-player-password','wrong-gm-password');
                    $data['success'] = false;
                    $msg_content = __('Verify your password','bluerabbit');
                    $data['message'] = $n->pop($msg_content,'red','cancel');
                }

            }else{
                BR_Activity::instance()->logActivity($adventure_id,'update-player-password','unauthorized','system-blocked');
                $data['success'] = false;
                $msg_content = __('Unauthorized','bluerabbit');
                $data['message'] = $n->pop($msg_content,'red','cancel');
            }
        }else{
            BR_Activity::instance()->logActivity($adventure_id,'update-player-password','unauthorized','system-blocked');
            $data['success'] = false;
            $msg_content = __('Only GMs can do this','bluerabbit');
            $data['message'] = $n->pop($msg_content,'red','cancel');
        }

        echo json_encode($data);
        die();
    }

    public function setCurrentQuest($p_quest_id=null,$p_step=null, $p_adv_id=null){
        global $wpdb;
        $current_user = wp_get_current_user();
        $quest_id = ($p_quest_id) ? $p_quest_id : $_POST['quest_id'];
        $step = ($p_step) ? $p_step : $_POST['step'];
        $adventure_id = ($p_adv_id) ? $p_adv_id : $_POST['adventure_id'];

        if($quest_id > 0){
        $q = BR_Quest::instance()->getQuest($quest_id);

        $updatePlayerSQL = "UPDATE {$wpdb->prefix}br_player_adventure SET player_current_quest_id=%d, player_current_quest_step=%d WHERE player_id=%d AND adventure_id=%d ";
        $updatePlayer=$wpdb->query($wpdb->prepare($updatePlayerSQL, $quest_id, $step, $current_user->ID, $adventure_id));
        //$n = new Notification();
            $data['success'] = true;
            $data['current_quest_url']= get_bloginfo('url')."/$q->quest_type/?questID=$quest_id&adventure_id=$adventure_id#step-$step";
        }else{
            $data['success'] = false;
            $data['current_quest_url'] = "";
        }
        echo json_encode($data);
        die();
    }

    public function resetPlayer($adventure_id, $uID){
        global $wpdb;
        $user=$this->getPlayerData($uID);
        $data = array();
        $data['success']=false;
        $config = BR_Config::instance()->getSysConfig();
        if($user){
            $adventure = $wpdb->get_row("SELECT * from {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_status='publish'");
            $adv_child_id = $adventure->adventure_id;
            $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;




            if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
            $today = date('Y-m-d H:i:s');
            $after = $adventure->adventure_progression_type;
            $errors = array();

            $myItems = BR_Item::instance()->getMyItems($adventure->adventure_id, $user->player_id);
            $myXP = 0;
            $myEP = 0;
            $myLevel = 1;
            $myBloo = 0;
            $item_rewards=array();
            $fqs=array();
            $reqs = array(
                'quests' => array(),
                'items' => array(),
                'achievements' =>array()
            );
            $reqs_ids = array(
                'items'=>array(),
                'achievements'=>array(),
            );
            $gpa = array();

            $achievements = $wpdb->get_results("SELECT

            a.achievement_id, a.achievement_name, a.achievement_badge, a.achievement_color, a.adventure_id, a.achievement_xp, a.achievement_bloo, a.achievement_ep, b.player_id
            FROM {$wpdb->prefix}br_achievements a
            JOIN {$wpdb->prefix}br_player_achievement b
            ON a.achievement_id = b.achievement_id AND b.player_id=$user->player_id
            WHERE b.adventure_id=$adventure_id  AND b.player_id=$user->player_id AND a.achievement_status='publish'");

            $achievements_ids = array();
            if($achievements){
                foreach($achievements as $key=>$a){
                    $achievements_ids[]=$a->achievement_id;
                    $myXP += $a->achievement_xp;
                    $myBloo += $a->achievement_bloo;
                    $myEP += $a->achievement_ep;
                }
                $achievements_ids_str = " OR quests.achievement_id IN (".implode(",",$achievements_ids).") ";
            }else{
                $achievements_ids_str = "";
            }

            if(isset($adventure->adventure_parent)){
                $adventure_content_id = $adventure->adventure_parent;
            }else{
                $adventure_content_id = $adventure->adventure_id;
            }
            $quests = $wpdb->get_results("SELECT
            quests.*,
            pposts.pp_content, pposts.pp_grade, pposts.player_id,
            achievements.achievement_color, achievements.achievement_name
            FROM {$wpdb->prefix}br_quests quests
            LEFT JOIN {$wpdb->prefix}br_player_posts pposts
            ON quests.quest_id = pposts.quest_id AND pposts.player_id=$user->player_id AND pposts.pp_status='publish'
            LEFT JOIN {$wpdb->prefix}br_achievements achievements
            ON quests.achievement_id = achievements.achievement_id AND achievements.achievement_status='publish'
            WHERE quests.adventure_id=$adv_parent_id AND (quests.quest_status='publish' OR quests.quest_status='hidden') AND (quests.achievement_id='' OR quests.achievement_id=NULL $achievements_ids_str ) ORDER BY quests.quest_order, quests.mech_level, quests.mech_start_date, quests.quest_title");

            $survey_questions = $wpdb->get_results("SELECT questions.*
            FROM {$wpdb->prefix}br_survey_questions questions
            JOIN  {$wpdb->prefix}br_quests surveys
            ON surveys.quest_id = questions.survey_id AND surveys.quest_status='publish'
            WHERE surveys.adventure_id=$adventure_id AND questions.survey_question_status='publish' GROUP BY questions.survey_question_id");

            $survey_answers = $wpdb->get_results("SELECT answers.*
            FROM {$wpdb->prefix}br_survey_answers answers
            JOIN  {$wpdb->prefix}br_quests surveys
            ON surveys.quest_id = answers.survey_id AND surveys.quest_status='publish'
            JOIN  {$wpdb->prefix}br_survey_questions questions
            ON surveys.quest_id = questions.survey_id AND questions.survey_question_status='publish'
            WHERE surveys.adventure_id=$adventure_id AND answers.player_id=$user->player_id AND (answers.survey_option_id > 0 OR answers.survey_answer_value!='') GROUP BY answers.survey_question_id");

            $surveys = array();
            foreach($survey_questions as $sq){
                $surveys['s'.$sq->survey_id]['questions'][]=$sq;
            }
            foreach($survey_answers as $sa){
                $surveys['s'.$sa->survey_id]['answers'][]=$sa;
            }

            $attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_attempts WHERE player_id=$user->player_id AND adventure_id=$adventure_id  AND attempt_status !='trash'");

            $requirements = $wpdb->get_results("SELECT
            a.quest_id, a.quest_status, a.quest_guild, a.quest_title,
            b.req_object_id, b.req_type, b.req_object_id,
            c.item_name, c.item_badge,
            e.mech_badge, e.quest_type, e.quest_title, e.achievement_id

            FROM {$wpdb->prefix}br_quests a
            LEFT JOIN {$wpdb->prefix}br_reqs b
            ON a.quest_id = b.quest_id
            LEFT JOIN {$wpdb->prefix}br_items c
            ON c.item_id = b.req_object_id AND b.req_type='item'
            LEFT JOIN {$wpdb->prefix}br_quests e
            ON b.req_object_id = e.quest_id AND b.req_type='quest' AND e.quest_status='publish'

            LEFT JOIN {$wpdb->prefix}br_achievements achievements
            ON b.req_object_id = achievements.achievement_id AND b.req_type='achievement'  AND achievements.achievement_status='publish'
            LEFT JOIN {$wpdb->prefix}br_player_achievement f
            ON achievements.achievement_id = f.achievement_id AND f.player_id=$user->player_id

            WHERE a.adventure_id=$adventure_id AND a.quest_status='publish'
            ORDER BY a.quest_id
            ");
            foreach($requirements as $r){
                if($r->quest_status == 'publish'){
                    if($r->req_type=='quest'){
                        $reqs['quests'][$r->quest_id][]=$r;
                        $reqs_ids['quests'][$r->quest_id][]=$r->req_object_id;
                    }elseif($r->req_type=='item'){
                        $reqs['items'][$r->quest_id][]=$r;
                        $reqs_ids['items'][$r->quest_id][]=$r->req_object_id;
                    }elseif($r->req_type=='achievement'){
                        $reqs['achievements'][$r->quest_id][]=$r;
                        $reqs_ids['achievements'][$r->quest_id][]=$r->req_object_id;
                    }
                }
            }
            $blockers = $wpdb->get_results("SELECT
            a.blocker_date, a.blocker_description, a.blocker_cost, b.player_id
            FROM {$wpdb->prefix}br_blockers a
            JOIN {$wpdb->prefix}br_player_blocker b
            ON a.blocker_id = b.blocker_id
            WHERE a.adventure_id=$adventure_id AND b.player_id=$user->player_id AND a.blocker_status='publish'");

            $transactions = $wpdb->get_results("
            SELECT trnxs.*
            FROM {$wpdb->prefix}br_transactions trnxs
            LEFT JOIN {$wpdb->prefix}br_blockers blockers
            ON trnxs.object_id = blockers.blocker_id AND trnxs.trnx_type='blocker'
            LEFT JOIN {$wpdb->prefix}br_items items
            ON trnxs.object_id = items.item_id AND (trnxs.trnx_type='consumable' OR trnxs.trnx_type='key')
            LEFT JOIN {$wpdb->prefix}br_quests quests
            ON trnxs.object_id = quests.quest_id AND ( trnxs.trnx_type='deadline' OR  trnxs.trnx_type='unlock' OR trnxs.trnx_type='attempt')
            LEFT JOIN {$wpdb->prefix}br_challenge_attempts attempts
            ON trnxs.object_id = attempts.quest_id AND quests.quest_id = attempts.quest_id AND trnxs.trnx_type='attempt'
            WHERE trnxs.adventure_id=$adventure_id AND trnxs.trnx_status='publish' AND trnxs.player_id=$user->player_id
            AND (quests.quest_status='publish' OR items.item_status='publish' OR blockers.blocker_status='publish')
            GROUP BY trnxs.trnx_id ORDER BY trnxs.trnx_id
            ");

            $myGuilds = $wpdb->get_col ("SELECT a.guild_id FROM {$wpdb->prefix}br_guilds a
            JOIN {$wpdb->prefix}br_player_guild b
            ON a.guild_id = b.guild_id
            WHERE b.player_id=$user->player_id AND a.adventure_id=$adventure_id");
            $guilds_str = implode(',',$myGuilds);

            if($guilds_str){

                $guildmates = $wpdb->get_col ("SELECT b.player_id FROM {$wpdb->prefix}br_guilds a
                JOIN {$wpdb->prefix}br_player_guild b
                ON a.guild_id = b.guild_id
                WHERE a.guild_id IN ($guilds_str)");
                $guildmates_str = implode(',',$guildmates);


                $guildwork_sql = $wpdb->get_results("SELECT
                playerposts.quest_id
                FROM {$wpdb->prefix}br_player_posts playerposts
                WHERE playerposts.adventure_id=$adventure_id AND playerposts.player_id IN ($guildmates_str)");

                $guildwork=array();
                foreach($guildwork_sql as $gw){
                    $guildwork[]=$gw->quest_id;
                }
                $guildwork = array_unique($guildwork);
                sort($guildwork);


            }
            $ppInsertSQL = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, player_id, adventure_id, pp_status, pp_type) VALUES ";
            $pp_ph = array();
            $pp_values = array();

            foreach($quests as $ppKey=>$pp){
                if($pp->quest_status == 'locked') continue;
                if($pp->quest_type == 'quest' && $pp->pp_content !=""){
                    if($pp->pp_grade > 0 && $after == "after" || $after == "before"){
                        if(!in_array($pp->quest_id,$fqs)){
                            $myEP += $pp->mech_ep;
                            $myXP += $pp->mech_xp;
                            if($after == "after"){
                                $myBloo += ($pp->mech_bloo*$pp->pp_grade/100);
                            }else{
                                $myBloo += $pp->mech_bloo;
                            }
                            $fqs[]=$pp->quest_id;
                            if($pp->mech_item_reward){
                                $item_rewards[]=$pp->mech_item_reward;
                            }
                            if($pp->pp_grade){
                                $gpa[$pp->quest_id] = $pp->pp_grade;
                            }
                        }
                    }
                }elseif($pp->quest_type == 'challenge'){
                    foreach($attempts as $att){
                        if($att->quest_id==$pp->quest_id && $att->attempt_status=='success' ){
                            if(!in_array($pp->quest_id,$fqs)){
                                $myEP += $pp->mech_ep;
                                $myXP += $pp->mech_xp;
                                if($after == "after"){
                                    $myBloo += ($pp->mech_bloo*$pp->pp_grade/100);
                                }else{
                                    $myBloo += $pp->mech_bloo;
                                }
                                $fqs[]=$pp->quest_id;
                                if(isset($gpa[$pp->quest_id]) && $att->attempt_grade > $gpa[$pp->quest_id]){
                                    $gpa[$pp->quest_id] = $att->attempt_grade;
                                }
                                array_push($pp_values, $pp->quest_id, $user->player_id, $adventure_id, 'publish','challenge');
                                $pp_ph[] = " (%d, %d, %d, %s, %s) ";
                            }
                        }
                    }
                }elseif($pp->quest_type == 'survey'){
                    if(isset($surveys['s'.$pp->quest_id]['answers']) && isset ($surveys['s'.$pp->quest_id]['questions'])){
                        if(count($surveys['s'.$pp->quest_id]['answers']) >= count($surveys['s'.$pp->quest_id]['questions']) && count($surveys['s'.$pp->quest_id]['questions'])>0 ){
                            $myXP += $pp->mech_xp;
                            $myEP += $pp->mech_ep;
                            $myBloo += $pp->mech_bloo;
                            $fqs[]=$pp->quest_id;
                            array_push($pp_values, $pp->quest_id, $user->player_id, $adventure_id, 'publish','survey');
                            $pp_ph[] = " (%d, %d, %d, %s, %s) ";
                        }
                    }
                }
            }
            /// MISSIONS
            foreach($quests as $ppKey=>$pp){
                if($pp->quest_status == 'locked') continue;
                if($pp->quest_type == 'mission'){
                    $objectives = BR_Objective::instance()->getObjectives($pp->adventure_id, $pp->quest_id, $user->player_id);
                    $objectives_completed = 0;
                    foreach($objectives as $cc){
                        if($cc->player_id==$user->player_id){
                            $objectives_completed++;
                        }
                    }

                    $something_to_do = false;
                    if(count($objectives) > 0 || !empty($reqs_ids['quests'][$pp->quest_id]) || !empty($reqs_ids['items'][$pp->quest_id]) || !empty($reqs_ids['achievements'][$pp->quest_id])){
                        $something_to_do = true;
                    }

                    if($objectives_completed >= count($objectives)){
                        $objectives_achieved = true;
                    }else{
                        $objectives_achieved = false;
                    }

                    if(isset($reqs_ids['quests'][$pp->quest_id])){
                        $mFinished = array_intersect($fqs, $reqs_ids['quests'][$pp->quest_id]);
                        $mFinished=array_values($mFinished);
                        sort($mFinished);
                        sort($reqs_ids['quests'][$pp->quest_id]);
                        $qFM = ($mFinished == $reqs_ids['quests'][$pp->quest_id]) ? true : false;
                    }else{
                        $qFM = true;
                    }

                    if(isset($reqs_ids['items'][$pp->quest_id])){
                        $mItems = array_intersect($myItems['ids']['key'], $reqs_ids['items'][$pp->quest_id]);
                        $mItems=array_values($mItems);
                        sort($mItems);
                        sort($reqs_ids['items'][$pp->quest_id]);
                        $iFM = ($mItems == $reqs_ids['items'][$pp->quest_id]) ? true : false;
                    }else{
                        $iFM = true;
                    }

                    if(isset($reqs_ids['achievements'][$pp->quest_id])){
                        $mAchievements = array_intersect($achievements_ids, $reqs_ids['achievements'][$pp->quest_id]);
                        $mAchievements = array_values($mAchievements);
                        sort($mAchievements);
                        sort($reqs_ids['achievements'][$pp->quest_id]);
                        $aFM = ($mAchievements == $reqs_ids['achievements'][$pp->quest_id]) ? true : false;
                    }else{
                        $aFM = true;
                    }
                    if($qFM && $iFM && $aFM && $objectives_achieved && $something_to_do){
                        if($pp->mech_item_reward && $pp->quest_type == 'mission'){
                            $prev_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$user->player_id AND adventure_id=$adventure_id AND object_id=$pp->mech_item_reward AND trnx_status='publish'");
                            if(!$prev_reward){
                                $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type)
                                VALUES (%d, %d, %d, %d, %d, %s)";
                                $sql = $wpdb->prepare($sql, $user->player_id, $adventure_id, $pp->mech_item_reward, $user->player_id, 0, 'reward');
                                $sql = $wpdb->query($sql);
                            }
                        }

                        if($pp->mech_achievement_reward){
                            $prev_ach = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_achievement a
                            JOIN {$wpdb->prefix}br_achievements b ON a.achievement_id=b.achievement_id
                            WHERE a.player_id=$user->player_id AND a.adventure_id=$adventure_id AND a.achievement_id=$pp->mech_achievement_reward AND b.achievement_status='publish'");
                            if(!$prev_ach){
                                $sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied)
                                VALUES (%d, %d, %d, %s)";
                                $sql = $wpdb->prepare($sql, $user->player_id, $adventure_id, $pp->mech_achievement_reward, $today);
                                $sql = $wpdb->query($sql);
                            }
                        }
                        $myXP += $pp->mech_xp;
                        $myEP += $pp->mech_ep;
                        $myBloo += $pp->mech_bloo;
                        $fqs[]=$pp->quest_id;
                        array_push($pp_values, $pp->quest_id, $user->player_id, $adventure_id, 'publish','mission');
                        $pp_ph[] = " (%d, %d, %d, %s, %s) ";
                    }
                }
            }
            /////////////// UPDATE ALL SOCIAL POSTS, SURVEYS ANSWERED and Missions in PLAYER POSTS as SUCCESS
            $ppInsertSQL .= implode(', ', $pp_ph);
            $ppInsertSQL .= " ON DUPLICATE KEY UPDATE pp_status=VALUES(pp_status)";
            if(isset($pp_ph) && count($pp_ph) > 0){
                $pp_insert =$wpdb->query( $wpdb->prepare("$ppInsertSQL ", $pp_values));
            }

            $debt=0;
            $paid=0;
            $spent=0;
            $totalEarned = $myBloo;
            $items = array();
            $deadlines = array();
            $unlocked = array();
            foreach($blockers as $b){
                $debt+=$b->blocker_cost;
            }
            foreach($transactions as $t){
                if($t->trnx_type == 'blocker'){
                    $debt-=$t->trnx_amount;
                    $paid++;
                }else if($t->trnx_type == 'attempt'){
                    $paid_attempts[]=$t->object_id;
                }else if($t->trnx_type == 'deadline'){
                    $deadlines[]=$t->object_id;
                }else if($t->trnx_type == 'unlock'){
                    $unlocked[]=$t->object_id;
                }else if($t->trnx_type == 'consumable' || $t->trnx_type == 'key' || $t->trnx_type == 'reward' || $t->trnx_type == 'use' || $t->trnx_type == 'tabi-piece'){
                    $items[$t->trnx_type][]=$t->object_id;
                }
                $myBloo -= $t->trnx_amount;
                $spent += $t->trnx_amount;
            }
            $tnl = 1000;
            $added = 0;
            for($l=1;$l<1000;$l++){
                $added += $l*1000;
                if(($added-1) < $myXP){
                    $myLevel = $l+1;
                    $tnl = $added + $myLevel*1000;
                }
            }

            $maxEP = 100+(($myLevel*($myLevel+1)/2)*20);
            $energy = $wpdb->get_results("SELECT SUM(energy) AS energy_spent FROM {$wpdb->prefix}br_player_energy_log WHERE player_id=$user->player_id AND adventure_id=$adventure_id");
            $myEP += $energy[0]->energy_spent;
            $epDiff = 0;
            if($myEP > $maxEP){
                $epDiff = $maxEP-$myEP;
                $insert = "INSERT INTO {$wpdb->prefix}br_player_energy_log (`adventure_id`, `player_id`,`energy`, `enc_option_content`,`timestamp`) VALUES (%d,%d,%d, %s, %s)";
                $insert = $wpdb->query($wpdb->prepare($insert, $adventure_id, $user->player_id, $epDiff, 'EP Cap Difference', $today));
            }

            if($myEP < 0){
                $myEP=0;
            }else{
                $myEP += $epDiff;
            }

            $totalgpa = $gpa ? round(array_sum($gpa)/count($gpa)) : 0;
            $last_level_xp = ($myLevel*($myLevel-1))/2 * 1000;
            $updatePlayerSQL = "UPDATE {$wpdb->prefix}br_player_adventure SET player_xp=%d, player_bloo=%d, player_ep=%d, player_level=%d , player_gpa=%d WHERE player_id=%d AND adventure_id=%d ";
            $updatePlayer=$wpdb->query($wpdb->prepare($updatePlayerSQL,$myXP,$myBloo,$myEP, $myLevel, $totalgpa, $user->player_id,$adventure_id));

            $data['player']['xp']=$myXP;
            $data['player']['bloo']=$myBloo;
            $data['player']['ep']=$myEP;
            $data['player']['level']=$myLevel;
            $data['player']['xp_curr_level']=$myXP-$last_level_xp;
            $data['player']['tnl']=$tnl-$myXP;
            $data['player']['debt']=$debt;
            $data['player']['spent']=$spent;
            $data['player']['totalEarned']=$totalEarned;
            $data['player']['paid_blockers']=$paid;
            $data['player']['items']=$items;
            $data['player']['fqs']=$fqs;
            $data['player']['deadlines']=$deadlines;
            $data['player']['unlocks']=$unlocked;
            $data['player']['gpa']=$totalgpa;
            $data['reqs']=$reqs;
            $data['reqs_ids']=$reqs_ids;
            $data['achievements']=$achievements;
            $data['achievements_ids']=$achievements_ids;
            $data['quests']=$quests;
            $data['attempts']=$attempts;
            $data['item_rewards']=$item_rewards;
            $data['blockers']=$blockers;
            $data['guildwork']= isset($guildwork) ? $guildwork : "";
            $data['debug']=isset($debugQuery) ? $debugQuery : "";
        }else{
            $data['debug']='Player not found';
        }
        return $data;
    }

    public function getPlayerAdventureData($adventure_id, $uID, $format='OBJECT'){
        global $wpdb;
        $player = $wpdb->get_row("SELECT a.*, b.achievement_id, b.achievement_name, b.achievement_color,
        c.player_display_name, c.player_hexad, c.player_hexad_slug, c.player_picture,  c.player_lang, c.player_first, c.player_last,
        d.trnx_amount, d.trnx_id
        FROM {$wpdb->prefix}br_player_adventure a
        LEFT JOIN {$wpdb->prefix}br_achievements b
        ON a.achievement_id = b.achievement_id
        LEFT JOIN {$wpdb->prefix}br_players c
        ON a.player_id = c.player_id
        LEFT JOIN {$wpdb->prefix}br_transactions d
        ON a.adventure_id=d.adventure_id AND d.player_id=$uID

        WHERE a.player_id=$uID AND a.adventure_id=$adventure_id", $format);
        return $player;
    }

    public function br_has_seen_tutorial($tutorial_key, $uID=0){
        $uID = $uID ? $uID : wp_get_current_user()->ID;
        return (bool) get_user_meta($uID, 'br_tutorial_seen_'.$tutorial_key, true);
    }

    public function getPlayerData($uID, $format='OBJECT'){
        global $wpdb;
        $player = $wpdb->get_row("SELECT a.*, b.hexad_answers, b.hexad_date, player_org.org_id FROM {$wpdb->prefix}br_players a
        LEFT JOIN {$wpdb->prefix}br_hexad b
        ON a.player_id = b.player_id
        LEFT JOIN {$wpdb->prefix}br_player_org player_org
        ON a.player_id = player_org.player_id
        WHERE a.player_id=$uID ORDER BY b.hexad_id DESC", $format);
        return $player;
    }

    public function getPlayerDataJSON($uID){
        global $wpdb;
        $player = $wpdb->get_row("SELECT a.*, b.hexad_answers, b.hexad_date, player_org.org_id FROM {$wpdb->prefix}br_players a
        LEFT JOIN {$wpdb->prefix}br_hexad b
        ON a.player_id = b.player_id
        LEFT JOIN {$wpdb->prefix}br_player_org player_org
        ON a.player_id = player_org.player_id
        WHERE a.player_id=$uID ORDER BY b.hexad_id DESC");
        echo json_encode($player);
        die();
    }

    ////////////////////////   ABSOLUTE LEVEL CALC //////////////////////////////
    public function absolute_level_calc($player_id=null){
        global $wpdb;
        if(!$player_id || !is_numeric($player_id)){
            $current_user = wp_get_current_user();
            $player_id = $current_user->ID;
        }
        $enrollment = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_id=$player_id
        ");

        $abs_level = 0;
        foreach($enrollment as $e){
            if($e->player_xp > 0){
                $abs_level += $e->player_level;
            }
        }
        if($abs_level <= 0){
            $abs_level = 1;
        }
        $update = $wpdb->query("UPDATE {$wpdb->prefix}br_players SET player_absolute_level=$abs_level WHERE player_id=$player_id");
    }

    ////////////////////////   HEXAD //////////////////////////////
    public function createHexad(){
        $testHexad= array(
            array('player_style'=>"ph","question"=>__("It makes me happy if I am able to help others","bluerabbit")),
            array('player_style'=>"ph","question"=>__("I like helping others to orient themselves in new situations","bluerabbit")),
            array('player_style'=>"ph","question"=>__("I like sharing my knowledge with others","bluerabbit")),
            array('player_style'=>"ph","question"=>__("The well being of others is important to me","bluerabbit")),
            array('player_style'=>"s","question"=>__("Interacting with others is important to me","bluerabbit")),
            array('player_style'=>"s","question"=>__("I like being part of a team","bluerabbit")),
            array('player_style'=>"s","question"=>__("It is important for me to feel like I am part of a community","bluerabbit")),
            array('player_style'=>"s","question"=>__("I enjoy group activities","bluerabbit")),
            array('player_style'=>"f","question"=>__("It is important to me to follow my own path","bluerabbit")),
            array('player_style'=>"f","question"=>__("I often let my curiosity guide me","bluerabbit")),
            array('player_style'=>"f","question"=>__("I like to try new things","bluerabbit")),
            array('player_style'=>"f","question"=>__("Being independent is important to me","bluerabbit")),
            array('player_style'=>"a","question"=>__("I like overcoming obstacles","bluerabbit")),
            array('player_style'=>"a","question"=>__("It is important to me to always carry out my tasks completely","bluerabbit")),
            array('player_style'=>"a","question"=>__("It is difficult for me to let go of a problem before I have found a solution","bluerabbit")),
            array('player_style'=>"a","question"=>__("I like mastering difficult tasks","bluerabbit")),
            array('player_style'=>"d","question"=>__("I like to provoke","bluerabbit")),
            array('player_style'=>"d","question"=>__("I like to question the status quo","bluerabbit")),
            array('player_style'=>"d","question"=>__("I see myself as a rebel","bluerabbit")),
            array('player_style'=>"d","question"=>__("I dislike following rules","bluerabbit")),
            array('player_style'=>"p","question"=>__("I like competitions where a prize can be won","bluerabbit")),
            array('player_style'=>"p","question"=>__("Rewards are a great way to motivate me","bluerabbit")),
            array('player_style'=>"p","question"=>__("Return of investment is important to me","bluerabbit")),
            array('player_style'=>"p","question"=>__("If the reward is enough I will put in the effort","bluerabbit")),
        );

        return $testHexad;
    }

    public function newHexad(){
        $data = array();
        global $wpdb; $current_user = wp_get_current_user();

        $answers = $_POST['answers'];
        $nonce = $_POST['nonce'];
        $data['success']=false;
        if (wp_verify_nonce($_POST['nonce'], 'br_new_hexad_nonce')) {
            if($answers){
                $intrinsic = array($answers["type_f"],$answers["type_s"],$answers["type_ph"],$answers["type_a"]);
                $ptMax = max($intrinsic);
                if($ptMax==$answers["type_f"] ){
                    $ptMaxSlug = "freespirit";
                    $the_hexad_name = "Free Spirit";
                }elseif($ptMax==$answers["type_a"] ){
                    $ptMaxSlug = "achiever";
                    $the_hexad_name = "Achiever";
                }elseif($ptMax==$answers["type_ph"] ){
                    $ptMaxSlug = "philanthropist";
                    $the_hexad_name = "Philanthropist";
                }elseif($ptMax==$answers["type_s"] ){
                    $ptMaxSlug = "socialiser";
                    $the_hexad_name = "Socialiser";
                }
                $answers = serialize($answers);
                $sql = "INSERT INTO {$wpdb->prefix}br_hexad  (hexad_answers,player_id)
                VALUES (%s,%d)";
                $sql = $wpdb->prepare ($sql,$answers,$current_user->ID);
                $wpdb->query($sql);
                if($wpdb->insert_id){
                    $sql = "UPDATE {$wpdb->prefix}br_players SET player_hexad=%s, player_hexad_slug=%s WHERE player_id=%d";
                    $sql = $wpdb->prepare ($sql,$the_hexad_name,$ptMaxSlug,$current_user->ID);
                    $wpdb->query($sql);
                    BR_Activity::instance()->logActivity($adventure_id,'answered','hexad',$answers);
                    $data['success'] = true;
                    $data['message'] = '<h2>'.__("Awesome!","bluerabbit").'</h2>'.'<h4><strong>'.__("Your player type is ","bluerabbit").$the_hexad_name.'</strong></h4>'.'<h5>'.__("click to see your results","bluerabbit").'</h5>';
                    $data['location'] = get_bloginfo('url')."/my-account";
                }else{
                    $data['message'] = '<h1>Process Failed</h1>';
                }
            }else{
                $data['message'] = '<h2>'.__("No answers received","bluerabbit").'</h2>';
            }
        }else{
            $data['message'] = '<h1>'.__("Unauthorized action","bluerabbit").'</h1>'.'<h5>'.__("click to close","bluerabbit").'</h5>';
        }
        echo json_encode($data);
        die();
    }

    /////////////////////////////////// SET PLAYER CLASS ROLE //////////////////////////////////
    public function setPlayerAdventureRole(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success']=false;

        $adventure_id = $_POST['adventure_id'];
        $player_id = $_POST['player_id'];
        $role = $_POST['role'];
        $nonce = $_POST['nonce'];


        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'br_player_adventure_status_nonce')){
            $enrollment = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$player_id");
            if($enrollment){
                $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_role=%s WHERE adventure_id=%d AND player_id=%d";
                $sql = $wpdb->query($wpdb->prepare($sql,$role,$adventure_id,$player_id));
                $data['success'] = true;
                $msg_content = __('Role Updated!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'teal','player');
                $data['role_update'] =$role;
                $data['player_id'] =$player_id;
                $data['just_notify'] =true;
                BR_Activity::instance()->logActivity($adventure_id,'assigned-privilege','player', $role, $player_id);
            }else{

                $msg_content = __("Player doesn't exist in adventure",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify'] =true;
            }

        }else{
            $data['message'] = "<h1>".__("Nonce Expired!","bluerabbit")."</h1>".'<h4>'.__('click to reload','bluerabbit').'</h4>';
            $data['location'] = 'reload';
        }
        echo json_encode($data);
        die();
    }

    /////////////////////////////////// Enroll Player //////////////////////////////////
    public function updatePlayerAdventureStatus($pData=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success']=false;
        $n = new Notification();
        if($pData){
            $adventure_id=$pData['adventure_id'];
            $player_id = $pData['player_id'];
            $status = $pData['status'];
            $nonce = $pData['nonce'];
        }else{
            $adventure_id = $_POST['adventure_id'];
            $player_id = $_POST['player_id'];
            $status = $_POST['status'];
            $nonce = $_POST['nonce'];
        }
        if(wp_verify_nonce($nonce, 'br_player_adventure_status_nonce')){
            $enrollment = $wpdb->get_row( "
            SELECT player.*, adv.adventure_owner
            FROM {$wpdb->prefix}br_player_adventure player
            JOIN {$wpdb->prefix}br_adventures adv ON adv.adventure_id=player.adventure_id
            WHERE player.adventure_id=$adventure_id AND player.player_id=$player_id");

            if($enrollment){
                if($player_id != $enrollment->adventure_owner){
                    $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_status=%s WHERE adventure_id=%d AND player_id=%d";
                    $sql = $wpdb->prepare ($sql, $status, $adventure_id, $player_id);
                    $wpdb->query($sql);
                    if($status=='out'){
                        $msg_content = __("Player removed",'bluerabbit');
                        $data['message'] = $n->pop($msg_content,'red','cancel');
                        $data['just_notify'] =true;
                        BR_Activity::instance()->logActivity($adventure_id,'removed','player','',$player_id);
                    }else{
                        $msg_content = __("Player enrolled",'bluerabbit');
                        $data['message'] = $n->pop($msg_content,'green','check');
                        $data['just_notify'] =true;
                        BR_Activity::instance()->logActivity($adventure_id,'enrolled','player','',$player_id);
                    }
                    $data['player_adventure_status'] = $status;
                    $data['player_id'] = $player_id;

                }else{
                    $msg_content = __("Can't change the status of the owner",'bluerabbit');
                    $data['message'] = $n->pop($msg_content,'red','cancel');
                }
            }else{
                $sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d)";
                BR_Activity::instance()->logActivity($adventure_id,'enrolled','player',"First Time",$player_id);
                $sql = $wpdb->prepare ($sql,$adventure_id,$player_id);
                $wpdb->query($sql);
                $msg_content = __("Player enrolled for the first time",'bluerabbit');
                $data['message'] = $n->pop($msg_content,'red','cancel');
            }
        }else{
            $data['message'] = "<h1>".__("Nonce Expired!","bluerabbit")."</h1>".'<h4>'.__('click to reload','bluerabbit').'</h4>';
            $data['location'] = 'reload';
        }

        if($pData){
            return $data;
        }else{
            echo json_encode($data);
            die();
        }

    }

    ///////////////////////// UPDATE PROFILE ///////////////////////////
    public function updateProfile(){
        $data = array();

        global $wpdb; $current_user = wp_get_current_user();
        $the_player_data = $_POST["player_data"];
        $nonce = $_POST["nonce"];
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
            $data['success'] = false;
            $display_name = $the_player_data['first_name']." ".$the_player_data['last_name'];
            if($the_player_data['hexad'] == 'freespirit'){
                $the_hexad_name = "Free Spirit";
            }elseif($the_player_data['hexad'] == 'philanthropist'){
                $the_hexad_name = "Philanthropist";
            }elseif($the_player_data['hexad'] == 'socialiser'){
                $the_hexad_name = "Socialiser";
            }elseif($the_player_data['hexad'] == 'achiever'){
                $the_hexad_name = "Achiever";
            }
            $user_data = array(
                "ID"=>$current_user->ID,
                "first_name"=>$the_player_data['first_name'],
                "first_name"=>$the_player_data['first_name'],
                "last_name"=>$the_player_data['last_name'],
                "display_name"=>$display_name
            );

            wp_update_user($user_data);
            update_user_meta($current_user->ID, 'locale', $the_player_data['lang']);
            $player_picture = $the_player_data['profile_picture'];
            if(!$player_picture){
                $player_picture = get_bloginfo('template_directory')."/images/token-".rand(1,5).".png";
            }
            $player_bio = stripslashes_deep($the_player_data['player_bio']);
            $update_player_sql="
            INSERT INTO {$wpdb->prefix}br_players
            (player_id, player_email, player_password, player_first, player_last, player_gmt, player_lang, player_picture, player_registered, player_display_name, player_nickname, player_bio, player_company, player_website, player_linkedin)
            VALUES (%d, %s, %s, %s, %s, %s,  %s, %s, %s, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
            player_email=%s, player_first=%s, player_last=%s, player_gmt=%s, player_lang=%s, player_picture=%s, player_display_name=%s, player_nickname=%s, player_bio=%s, player_company=%s, player_website=%s, player_linkedin=%s
            ";
            $update_player = $wpdb->prepare(
                $update_player_sql,
                $current_user->ID,
                $the_player_data['email'],
                'none',
                $the_player_data['first_name'],
                $the_player_data['last_name'],
                $the_player_data['timezone'],
                $the_player_data['lang'],
                $player_picture,
                $current_user->user_registered,
                $display_name,
                $current_user->user_login,
                $player_bio,
                $the_player_data['player_company'],
                $the_player_data['player_website'],
                $the_player_data['player_linkedin'],
                $the_player_data['email'],
                $the_player_data['first_name'],
                $the_player_data['last_name'],
                $the_player_data['timezone'],
                $the_player_data['lang'],
                $player_picture,
                $display_name,
                $current_user->user_login,
                $player_bio,
                $the_player_data['player_company'],
                $the_player_data['player_website'],
                $the_player_data['player_linkedin']
            );
            $update_player = $wpdb->query($update_player);
            $data['success'] = true;
            $msg_content = __('Profile updated!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;
            BR_Activity::instance()->logActivity(0,'update','profile');
        }else{
            $data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
            $data['success'] = false;
            $msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;

        }
        echo json_encode($data);
        die();
    }

    ///////////////////////// setNickname ///////////////////////////
    public function setNickname(){
        $data = array();
        global $wpdb; $current_user = wp_get_current_user();
        $nickname = $_POST["nickname"];
        $nonce = $_POST["nonce"];
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
            $data['success'] = false;
            if($nickname){
                $user_data = array(
                    "ID"=>$current_user->ID,
                    "display_name"=>$nickname
                );
                wp_update_user($user_data);
                $update_player_sql="INSERT INTO {$wpdb->prefix}br_players (player_id, player_nickname, player_display_name) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE player_nickname=%s , player_display_name=%s";
                $update_player = $wpdb->prepare( $update_player_sql, $current_user->ID, $nickname, $nickname, $nickname, $nickname);
                $update_player = $wpdb->query($update_player);
                $data['success'] = true;
                $msg_content = __('Nickname set!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green');
                $data['just_notify'] =true;
                $data['update_ux']['nickname']=$nickname;
                BR_Activity::instance()->logActivity(0,'update-nickname','profile');
            }else{
                $msg_content = __('Please choose a nickname!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify'] =true;
            }
        }else{
            $data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
            $data['success'] = false;
            $msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;

        }
        echo json_encode($data);
        die();
    }

    ///////////////////////// setProfilePicture ///////////////////////////
    public function setProfilePicture(){
        $data = array();
        global $wpdb; $current_user = wp_get_current_user();
        $player_picture = $_POST["player_picture"];
        $nonce = $_POST["nonce"];
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'br_profile_post_nonce')){
            $data['success'] = false;
            $update_player_sql="INSERT INTO {$wpdb->prefix}br_players (player_id, player_picture) VALUES (%d, %s) ON DUPLICATE KEY UPDATE player_picture=%s ";
            $update_player = $wpdb->prepare( $update_player_sql, $current_user->ID, $player_picture, $player_picture);
            $update_player = $wpdb->query($update_player);
            $data['success'] = true;
            $msg_content = __('Avatar selected!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;
            $data['update_ux']['player_picture']=$player_picture;
            BR_Activity::instance()->logActivity(0,'update-profile-picture','profile');
        }else{
            $data['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1> <h4>'.'</h4>';
            $data['success'] = false;
            $msg_content = __('Unauthorized access!','bluerabbit')."<br>".__('Illegal action detected','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green');
            $data['just_notify'] =true;

        }
        echo json_encode($data);
        die();
    }

    ///////////////////////// ANONIMIZE ADVENTURE ///////////////////////////
    public function anonimizeAdventure(){
        $data = array();
        global $wpdb; $current_user = wp_get_current_user();

        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST["nonce"];
        $role = $current_user->roles;
        if($role[0]!='administrator'){
            die();
        }else{
            if($adventure_id){
                $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
                if($adventure && wp_verify_nonce($nonce, 'br_anonimize_adventure')){

                    $players = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id");
                    foreach($players as $p){
                        $randStr1 = BR_Utils::instance()->random_str(12,'0123456789abcdefghijklmnopqrstuvwxyz._-');
                        $randStr2 = BR_Utils::instance()->random_str(8,'0123456789abcdefghijklmnopqrstuvwxyz');
                        $randStr3 = BR_Utils::instance()->random_str(12,'0123456789abcdefghijklmnopqrstuvwxyz');
                        $randStr4 = BR_Utils::instance()->random_str(5,'0123456789abcdefghijklmnopqrstuvwxyz');
                        $randStr5 = BR_Utils::instance()->random_str(5,'0123456789abcdefghijklmnopqrstuvwxyz');

                        $email = "$randStr1@anonymous.player".rand(1,5000);
                        $first = "$randStr2";
                        $last = "$randStr3";
                        $picture = get_bloginfo('template_directory')."/images/doodle-".rand(1,7).".png";
                        $display = $randStr4." ".$randStr5;

                        $anonimizerSQL .= "UPDATE {$wpdb->prefix}br_players SET player_email='$email', player_first='$first', player_last='$last', player_gmt='', player_lang='en_US',  player_picture='$picture', player_display_name='$display', player_twitter='' WHERE player_id=$p->player_id; ";
                    }
                    $clean_up = $wpdb->query($anonimizerSQL);
                    BR_Activity::instance()->logActivity($adventure_id,'anonimize','all-players');
                }
            }
        }
        die();
    }

    /////////////////////// CLOSE INTRO ////////////////////
    public function closeIntro($p_adv_id=0){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $p_adv_id ? $p_adv_id : $_POST['adventure_id'];

        $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_hide_intro=%d WHERE player_id=%d AND adventure_id=%d";
        $sql = $wpdb->prepare ($sql,1,$current_user->ID,$adventure_id);

        $wpdb->query($sql);
        $data['adventure_home_url'] = get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id;
        $data['success'] = true;
        echo json_encode($data);
        die();
    }

    /////////////////////// GENERIC TUTORIAL DISMISS ////////////////////
    public function br_dismiss_tutorial(){
        $current_user = wp_get_current_user();
        $tutorial_key = sanitize_key($_POST['tutorial_key']);
        update_user_meta($current_user->ID, 'br_tutorial_seen_'.$tutorial_key, 1);
        echo json_encode(array('success' => true));
        die();
    }

    ////////////// resetIntro /////////////
    public function resetIntro($p_adventure_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
        $notification = new Notification();
        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if($adventure){
            $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_hide_intro=0  WHERE adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$adventure_id);
            $wpdb->query($sql);
            $data['success'] = true;
            $msg_content =  __("Intro will show again on Login","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'blue','logo');
            $data['just_notify'] =true;
        }else{
            $data['success'] = false;
            $msg_content =  __("Adventure not found!","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    ////////////// resetGuilds /////////////
    public function resetGuilds($p_adventure_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
        $notification = new Notification();
        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if($adventure){
            $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_guild=0  WHERE adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$adventure_id);
            $wpdb->query($sql);
            $sql = "DELETE FROM {$wpdb->prefix}br_player_guild WHERE adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$adventure_id);
            $wpdb->query($sql);
            $data['success'] = true;
            $msg_content =  __("All Guilds have been reset","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'green','guild');
            $data['just_notify'] =true;
            BR_Activity::instance()->logActivity($adventure_id,'reset','guilds');
        }else{
            $data['success'] = false;
            $msg_content =  __("Adventure not found!","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;

        }
        echo json_encode($data);
        die();
    }

    ////////////// resetPlayerAdventure /////////////
    public function resetPlayerAdventure(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $_POST['adventure_id'];
        $player_id = $_POST['player_id'];
        $notification = new Notification();
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

        $player = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adv_child_id AND player_id=$player_id");
        if($adventure && $player){
            $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=0, player_xp=0, player_bloo=0, player_ep=0, achievement_id=0, player_guild=NULL, player_last_random_encounter_id=0, player_hide_intro=0  WHERE adventure_id=%d AND player_id=%d";
            $sql = $wpdb->prepare ($sql,$adv_child_id, $player->player_id);
            $wpdb->query($sql);
            $data['success'] = true;
            $msg_content =  __("The player has been reset","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'blue','logo');
            $data['just_notify'] =true;
            BR_Activity::instance()->logActivity($adv_child_id,'reset','player-adventure',"",$player->player_id);
        }else{
            $data['success'] = false;
            $msg_content =  __("Adventure not found!","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    ////////////// resetPrevLevel /////////////
    public function resetPrevLevel($p_adventure_id=NULL){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $p_adventure_id ? $p_adventure_id : $_POST['adventure_id'];
        $notification = new Notification();
        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if($adventure){
            $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=0  WHERE adventure_id=%d";
            $sql = $wpdb->prepare ($sql,$adventure_id);
            $wpdb->query($sql);
            $data['success'] = true;
            $msg_content =  __("All Prev Levels have been reset","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'purple','language');
            $data['just_notify'] =true;
        }else{
            $data['success'] = false;
            $msg_content =  __("Adventure not found!","bluerabbit");
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    /////////////////////// Update Prev Level ////////////////////
    public function updatePrevLevel(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $_POST['adventure_id'];
        $level = $_POST['level'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');

        // Ranks can award on level (as before) or on milestone_count/journey_pct/
        // transaction_count/item_consumed_count - all evaluated here since this is
        // still the only trigger point for rank checks (fired on a detected level-up).
        $player_progress = BR_Progression::instance()->getPlayerProgress($adv_child_id, $current_user->ID);
        $snapshot = BR_Conditions::instance()->buildProgressSnapshot($adv_parent_id, $adv_child_id, $current_user->ID, $player_progress);
        $all_ranks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_adventure_ranks WHERE adventure_id=$adv_parent_id ORDER BY rank_level ASC");
        $ranks = array();
        foreach($all_ranks as $r){
            switch($r->condition_type){
                case 'milestone_count':     $met = $snapshot['milestone_count']     >= $r->rank_level; break;
                case 'journey_pct':         $met = $snapshot['journey_pct']         >= $r->rank_level; break;
                case 'transaction_count':   $met = $snapshot['transaction_count']   >= $r->rank_level; break;
                case 'item_consumed_count': $met = $snapshot['item_consumed_count'] >= $r->rank_level; break;
                default:                    $met = $level >= $r->rank_level; // 'level'
            }
            if($met) $ranks[] = $r;
        }
        $config = BR_Config::instance()->getSysConfig();
        $logo = $config['main_logo']['value'] ? $config['main_logo']['value'] :  get_bloginfo('template_directory')."/images/logo.png";
        if($ranks){
            $the_ranks_query = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES ";
            $the_ranks_values = array();
            $the_ranks_place_holders = array();
            foreach($ranks as $rank){
                array_push($the_ranks_values, $rank->achievement_id, $current_user->ID, $adv_child_id, $today );
                $the_ranks_place_holders[] = "(%d,%d,%d, %s)";
            }
            $the_ranks_query .= implode(', ', $the_ranks_place_holders);
            $the_ranks_query .=" ON DUPLICATE KEY UPDATE achievement_id=VALUES(achievement_id), player_id=VALUES(player_id),  adventure_id=VALUES(adventure_id), achievement_applied=VALUES(achievement_applied)";
            $the_ranks_insert = $wpdb->query( $wpdb->prepare("$the_ranks_query ", $the_ranks_values));
            $achievement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$adv_parent_id AND achievement_id=$rank->achievement_id");
            BR_Achievement::instance()->switchRank($achievement->achievement_id, $adv_child_id);
            $data['levelupBG'] = $achievement->achievement_badge;
            $data['achievement_id'] = $achievement->achievement_id;
            $data['levelupContent'] = "<h3><strong>".__("LEVEL UP!","bluerabbit")."</strong></h3>";
            $data['levelupContent'] .= "<h2 class='font _30 w900'> $level </h2>";
            BR_Activity::instance()->logActivity($adv_child_id,'level-up','player',$level, $achievement->achievement_id);
            BR_Activity::instance()->logActivity($adv_child_id,'earned-achievement','player',$achievement->achievement_id);
        }else{
            $data['levelupContent'] = "<h3><strong>".__("Congratulations! LEVEL UP!","bluerabbit")."</strong></h3>";
            $data['levelupContent'] .= "<img src='$logo' width='300'>";
            $data['levelupContent'] .= "<h6>".__("you reached level","bluerabbit")."</h6>";
            $data['levelupContent'] .= "<h1><strong> $level </strong></h1>";
            BR_Activity::instance()->logActivity($adv_child_id,'level-up','player',$level);
        }
        $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_prev_level=%d WHERE player_id=%d AND adventure_id=%d";
        $sql = $wpdb->prepare ($sql, $level ,$current_user->ID,$adv_child_id);
        $wpdb->query($sql);
        $data['success'] = true;
        $data['levelup'] = true;




        echo json_encode($data);
        die();
    }

    ////////////// DEFAULT ENROLLMENT /////////////
    public function defaultEnrollment($adventure_id,$uID){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $p = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure_id AND player_id=$uID");
        $adventure = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id AND adventure_type='normal'");

        if(!$p){
            $sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d)";
            $sql = $wpdb->prepare ($sql,$adventure_id,$uID);
            $wpdb->query($sql);
            if($wpdb->insert_id){
                BR_Activity::instance()->logActivity($adventure_id,'enroll','player-adventure');
                if($adventure->adventure_has_guilds){
                    BR_Guild::instance()->assignGuild($uID, $adventure->adventure_id);
                }
                $data['success'] = true;
                $data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";

            }
        }elseif($p->player_adventure_status=='out'){
            $sql = "UPDATE {$wpdb->prefix}br_player_adventure SET player_adventure_status=%s WHERE player_id=%d AND adventure_id=%d";
            $sql = $wpdb->prepare ($sql,'in',$uID,$adventure_id);
            BR_Activity::instance()->logActivity($adventure_id,'removed','player-adventure');
            if($wpdb->insert_id){
                if($adventure->adventure_has_guilds){
                    BR_Guild::instance()->assignGuild($uID, $adventure->adventure_id);
                }

                $data['success'] = true;
                $data['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure_id";

            }
        }
        return $data;
    }

    ///////////////////////// EXPORT PLAYERS WORK ///////////////////////////
    public function exportPlayersWork() {
        $data = json_decode(stripslashes($_POST['data']), true);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="players_export.csv"');

        $output = fopen('php://output', 'w');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}

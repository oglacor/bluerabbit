<?php
class BR_Item {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // Source: functions/ajax.php — reorderItems
    public function reorderItems(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $adventure_id = $_POST['adventure_id'];
        $the_order = $_POST['the_order'];
        $count = 0;
        foreach($the_order as $k=>$id){
            $sql = "UPDATE {$wpdb->prefix}br_items SET item_order=%d WHERE (item_id=%d AND adventure_id=%d) OR item_parent=%d";
            $sql = $wpdb->prepare ($sql, $k, $id, $adventure_id, $id);
            $result = $wpdb->query($sql);
        }
        if($k+1 >= count($the_order)){
            $data['success'] = true;
            $data['message'] = "<h1>".__("Items Reordered","bluerabbit")."</h1>";
            $data['location'] = "reload";
            BR_Activity::instance()->logActivity($adventure_id,'reoredered','items',serialize($the_order));
        }else{
            $data['message'] = "<h1>".__("Error","bluerabbit")."</h1>";
            $data['message'] .= "<h4>".$k."</h4>";
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — loadItemCard
    public function loadItemCard(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $isAdmin = $roles[0]=='administrator' ? true : false;
        $adventure_id = $_POST['adventure_id'];
        $item_id = $_POST['item_id'];
        $item = $wpdb->get_row("SELECT
            item.*, COUNT(DISTINCT trnxs.trnx_id) AS purchased, COUNT(DISTINCT player_trnxs.trnx_modified) AS bought, player_trnxs.player_id, trnxs.trnx_date, trnxs.trnx_id

            FROM {$wpdb->prefix}br_items item
            LEFT JOIN {$wpdb->prefix}br_transactions trnxs
            ON trnxs.object_id = item.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable')

            LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
            ON player_trnxs.object_id = item.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable') AND player_trnxs.player_id=$current_user->ID AND trnxs.trnx_use=0 AND trnxs.adventure_id=$adventure_id
            WHERE item.item_id=$item_id GROUP BY item.item_id
        ");
        if($item){
            $adventure = BR_Adventure::instance()->getAdventure($item->adventure_id);
            $adv_child_id = $adventure->adventure_id;
            $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;


            if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
            $today = date("Y-m-d H:i:s");
            $theFile = (get_template_directory()."/card-item.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            $notification = new Notification();
            $msg_content = __("Item doesn't exist",'bluerabbit');
            $data['message'] = $notification->pop($msg_content, 'red','cancel');
            $data['just_notify'] =true;
            echo json_encode($data);
        }
        die();
    }

    // Source: functions/ajax.php — loadBackpackItem
    public function loadBackpackItem(){
        global $wpdb; $current_user = wp_get_current_user();
        $data=array();
        $roles = $current_user->roles;
        $isAdmin = $roles[0]=='administrator' ? true : false;
        $item_id = $_POST['item_id'];
        $item = $wpdb->get_row("SELECT
            item.*, player_trnxs.player_id, trnxs.trnx_date, trnxs.trnx_id

            FROM {$wpdb->prefix}br_items item
            LEFT JOIN {$wpdb->prefix}br_transactions trnxs
            ON trnxs.object_id = item.item_id AND trnxs.trnx_status='publish' AND (trnxs.trnx_type='key' OR trnxs.trnx_type='consumable')

            LEFT JOIN {$wpdb->prefix}br_transactions player_trnxs
            ON player_trnxs.object_id = item.item_id AND player_trnxs.trnx_status='publish' AND (player_trnxs.trnx_type='key' OR player_trnxs.trnx_type='consumable') AND player_trnxs.player_id=$current_user->ID AND trnxs.trnx_use=0
            WHERE item.item_id=$item_id GROUP BY item.item_id
        ");
        if($item){
            $theFile = (get_template_directory()."/card-backpack-item.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            echo "<h1>".__("Item doesn't exist","bluerabbit")."</h1>";
        }
        die();
    }

    // Source: functions/ajax.php — uploadBulkItems
    public function uploadBulkItems(){
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

            $bulk_items_query = "INSERT INTO {$wpdb->prefix}br_items (  `adventure_id`, `item_author`, `item_name`, `item_description`, `item_cost`, `item_type`, `item_badge`,  `item_stock`, `item_player_max`, `item_level`, `item_category`, `item_order`, `tabi_id`, `item_status` ) VALUES ";


            $values = [];
            $place_holders = [];
            if (!$data['errors'] && ($handle = fopen($file, 'r')) !== false) {
                while (($file_data = fgetcsv($handle, 1000, ',', '"')) !== false) {
                    if ($row_index == 0) {
                        $row_index++;
                        continue;
                    }
                    if($row_index <=150){
                        $item_data = [];
                        $item_data['adventure_id']=$adv_id;
                        $item_data['item_author']=sanitize_text_field($file_data[0]);
                        $item_data['item_name']=sanitize_text_field($file_data[1]);
                        $item_data['item_description']=sanitize_text_field($file_data[2]);
                        $item_data['item_cost']=sanitize_text_field($file_data[3]);
                        $item_data['item_type']=sanitize_text_field($file_data[4]);
                        $item_data['item_badge']=sanitize_text_field($file_data[5]);
                        $item_data['item_stock']=sanitize_text_field($file_data[6]);
                        $item_data['item_player_max']=sanitize_text_field($file_data[7]);
                        $item_data['item_level']=sanitize_text_field($file_data[8]);
                        $item_data['item_category']=sanitize_text_field($file_data[9]);
                        $item_data['item_order']=sanitize_text_field($file_data[10]);
                        $item_data['tabi_id']=sanitize_text_field($file_data[11]);
                        $item_data['item_status']=sanitize_text_field($file_data[12]);

                        if($item_data['item_name']){
                            array_push($values, $item_data['adventure_id'], $item_data['item_author'], $item_data['item_name'], $item_data['item_description'], $item_data['item_cost'], $item_data['item_type'], $item_data['item_badge'], $item_data['item_stock'], $item_data['item_player_max'], $item_data['item_level'], $item_data['item_category'], $item_data['item_order'], $item_data['tabi_id'], $item_data['item_status']);

                            $place_holders[] = " (%d, %d, %s, %s, %d, %s, %s,  %d, %d, %d, %s, %d, %d, %s)";


                            $msg_content = __("Item ",'bluerabbit').$item_data['item_name'].__(" inserted correctly",'bluerabbit');
                            $data['messages'][] = $n->pop($msg_content,'green','check');
                        }else{
                            $msg_content = __("Skipping empty row in file",'bluerabbit');
                            $data['messages'][] = $n->pop($msg_content,'green','check');
                        }
                        $row_index++;
                    }
                }

                fclose($handle);

                $bulk_items_query .= implode(', ', $place_holders);
                $bulk_items_query = $wpdb->query( $wpdb->prepare("$bulk_items_query ", $values));
                $data['debug'] = print_r($wpdb->last_result,true);
                $msg_content = __("Items uploaded correctly",'bluerabbit');

                $data['messages'][] = $n->pop($msg_content,'amber','check');
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

    // Source: functions/ajax.php — updateItem
    public function updateItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $roles = $current_user->roles;
        $data = array();
        $errors = array();
        if (wp_verify_nonce($_POST['nonce'], 'br_update_item_nonce')) {
            $item_data = $_POST['item_data'];
            $adventure_id = $item_data['adventure_id'];
            $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
            if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
            $item_id = $item_data['item_id'];
            $item_name = stripslashes_deep($item_data['item_name']);
            $item_post_modified = date("Y-m-d H:i:s");
            $item_stock = $item_data['item_stock'];
            $item_sold = $item_data['item_sold'];
            $item_cost = $item_data['item_cost'];
            $item_description = stripslashes_deep($item_data['item_description']);
            $item_secret_description = stripslashes_deep($item_data['item_secret_description']);
            $item_type = $item_data['item_type'];
            $item_visibility = $item_data['item_visibility'];
            $item_badge = $item_data['item_badge'];
            $item_secret_badge = $item_data['item_secret_badge'];
            $item_max = $item_data['item_max'];
            $item_level = $item_data['item_level'];
            $item_category_id = (int) $item_data['item_category'];
            $achievement_id = $item_data['achievement_id'];
            $item_start_date = $item_data['item_start_date'];
            $item_deadline = $item_data['item_deadline'];


            if(!$item_name){
                $errors[] = __("Please add an item name","bluerabbit");
            }
            if(!$item_badge){
                $errors[] = __("Please add an image for the item","bluerabbit");
            }
            if($item_type == 'consumable'){
                if($item_stock < $item_sold){
                    $errors[] = __("The store needs more items than already sold","bluerabbit")."<br>".__("Sold","bluerabbit").": $item_sold" ;
                }
            }elseif($item_type == 'key'){
                $item_stock = 99999999;
                $item_max = 1;
                $item_category_id = 0;
            }else if( $item_type == 'reward'){
                $item_stock = 99999999;
                $item_category_id = 0;
                $item_cost = 0;
                $item_max = 1;
            }else if( $item_type == 'tabi-piece'){
                $item_x = $item_data['item_x'];
                $item_y = $item_data['item_y'];
                $item_z = $item_data['item_z'];
                $tabi_id = $item_data['tabi_id'];
                if($item_stock <= 0){
                    $item_stock = 99999999;
                }else if($item_stock < $item_sold){
                    $errors[] = __("The store needs more items than already sold","bluerabbit")."<br>".__("Sold","bluerabbit").": $item_sold" ;
                }
                $item_max = 1;
            }else{
                $errors[] = __("Item type doesn't exist, please select one from the options given","bluerabbit");
            }
            $sql = "INSERT INTO {$wpdb->prefix}br_items ( item_id, adventure_id, item_cost, item_stock, item_player_max, item_level, item_post_date, item_post_modified, item_author, item_name, item_description, item_type, item_badge, item_secret_badge, item_secret_description, item_category_id, achievement_id, item_start_date, item_deadline, item_x, item_y, item_z, tabi_id, item_visibility)
            VALUES (%d, %d, %d, %d, %d, %d, %s, %s, %s, %s,  %s, %s, %s, %s, %s, %d, %d, %s, %s, %d, %d, %d, %d, %s )
            ON DUPLICATE KEY UPDATE
            adventure_id=%d, item_cost=%d, item_stock=%d, item_player_max=%d, item_level=%d, item_post_modified=%s, item_author=%s, item_name=%s, item_description=%s, item_type=%s, item_badge=%s, item_secret_badge=%s, item_secret_description=%s, item_category_id=%d, achievement_id=%d, item_start_date=%s, item_deadline=%s, item_x=%d, item_y=%d, item_z=%d, tabi_id=%d, item_visibility=%s";

            $sql = $wpdb->prepare($sql, $item_id, $adventure_id, $item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $item_post_modified, $current_user->ID, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category_id, $achievement_id, $item_start_date, $item_deadline, $item_x, $item_y, $item_z, $tabi_id, $item_visibility, $adventure_id, $item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $current_user->ID, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category_id, $achievement_id, $item_start_date, $item_deadline, $item_x, $item_y, $item_z, $tabi_id, $item_visibility);
            if(!$errors){
                $wpdb->query($sql);
                $new_item_id = $wpdb->insert_id;

                if($wpdb->insert_id){
                    $data['success']=true;
                    if(!$item_id){
                        $data['location']=get_bloginfo('url')."/new-item/?adventure_id=$adventure_id&item_id=$new_item_id";
                        BR_Activity::instance()->logActivity($adventure_id,'add','item',$item_type,$new_item_id);
                    }else{
                        BR_Activity::instance()->logActivity($adventure_id,'update','item',$item_type,$item_id);
                    }
                    $data['message'] .= '<h1><strong>'.$item_name.'</strong></h1> <h4><strong>'.__("Item Updated!","bluerabbit").'</strong></h4> <h5>'.__("click to close","bluerabbit").'</h5>';

                    $children_update = "UPDATE {$wpdb->prefix}br_items SET item_cost=%d, item_stock=%d, item_player_max=%d, item_level=%d, item_post_modified=%s, item_status=%s, item_name=%s, item_description=%s, item_type=%s, item_badge=%s, item_secret_badge=%s, item_secret_description=%s, item_category_id=%d, item_start_date=%s, item_deadline=%s
                    WHERE `item_parent`=$new_item_id AND item_id!=$new_item_id";

                    $children_update = $wpdb->query( $wpdb->prepare("$children_update ",$item_cost, $item_stock, $item_max, $item_level, $item_post_modified, $item_status, $item_name, $item_description, $item_type, $item_badge, $item_secret_badge, $item_secret_description, $item_category_id, $item_start_date, $item_deadline));

                    BR_Activity::instance()->logActivity($adventure_id,'update','item-children',$item_type,$item_id);


                }else{
                    $data['message'] .= '<h1><span class="icon icon-cancel solid-red"></span></h1> <h4><strong>'.__("Data Base Error. Can't insert item","bluerabbit").'</strong></h4> <h5>'.__("contact admin please, click to close","bluerabbit").'</h5>';
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

    // Source: functions/ajax.php — buyItem
    public function buyItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $adventure_id = $_POST['adventure_id'];
        $item_id = $_POST['item_id'];
        $nonce = $_POST['nonce'];
        $data = array();
        $data['success'] = false;


        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        $notification = new Notification();
        $data['message_delay'] = 2000;
        $data['just_notify']=true;

        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        if(wp_verify_nonce($nonce, 'br_item_nonce')){

            $playerData = BR_Player::instance()->getPlayerAdventureData($adv_child_id, $current_user->ID);
            $purchaseData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$item_id AND item_status='publish' AND adventure_id=$adv_parent_id");

            $allowedByStartDate = true;
            $allowedByDeadline = true;

            if(($purchaseData->item_start_date != '' && $purchaseData->item_start_date != '0000-00-00 00:00:00') && strtotime($today) < strtotime($purchaseData->item_start_date)){
                $allowedByStartDate = false;
            }
            if(($purchaseData->item_deadline != '' && $purchaseData->item_deadline != '0000-00-00 00:00:00') && strtotime($today) > strtotime($purchaseData->item_deadline)){
                $allowedByDeadline = false;
            }

            if($purchaseData->item_category_id){
                $trnxs = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM {$wpdb->prefix}br_transactions a
                JOIN {$wpdb->prefix}br_items b
                ON a.object_id=b.item_id
                WHERE a.adventure_id=%d AND a.player_id=%d AND b.item_category_id=%d AND trnx_status='publish'",
                $adv_child_id, $current_user->ID, $purchaseData->item_category_id));
            }else{
                $trnxs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_transactions
                WHERE adventure_id=$adv_child_id AND player_id=$current_user->ID AND object_id=$item_id  AND trnx_status='publish'");
            }
            $alltrnx = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_transactions WHERE object_id=$item_id AND trnx_type='consumable' AND trnx_status='publish' AND adventure_id=$adv_child_id");

            $left = $purchaseData->item_stock-count($alltrnx);
            //validation
            if($allowedByStartDate){
                if($allowedByDeadline){
                    if($left>0){
                        if($playerData->player_bloo >= $purchaseData->item_cost ){
                            if($playerData->player_level >= $purchaseData->item_level){
                                $player_progress = BR_Progression::instance()->getPlayerProgress($adv_child_id, $current_user->ID);
                                $snapshot = BR_Conditions::instance()->buildProgressSnapshot($adv_parent_id, $adv_child_id, $current_user->ID, $player_progress);
                                if(BR_Item::instance()->evaluateItemAccess($adv_child_id, $purchaseData, $snapshot)){
                                    if($purchaseData->item_player_max == 0 || (count($trnxs) < $purchaseData->item_player_max && $purchaseData->item_player_max > 0)){
                                        // Deterministic reservation key for this exact purchase attempt - see
                                        // br_migrate_transaction_lock_schema() in functions.php. Scoped to
                                        // whichever cap is actually scarce for this item: stock is shop-wide
                                        // (raced by different players), player/category max is per-player
                                        // (raced by a double-click); either way the count component means two
                                        // requests racing the same stale count compute the same key.
                                        if($purchaseData->item_stock > 0 && $purchaseData->item_stock < 99999){
                                            $lock_key = "stock_{$item_id}_{$adv_child_id}_".(count($alltrnx)+1);
                                        }elseif($purchaseData->item_player_max > 0){
                                            $scope = $purchaseData->item_category_id ? "cat{$purchaseData->item_category_id}" : "item{$item_id}";
                                            $lock_key = "cap_{$current_user->ID}_{$scope}_{$adv_child_id}_".(count($trnxs)+1);
                                        }else{
                                            $lock_key = "buy_{$current_user->ID}_{$item_id}_{$adv_child_id}_".(count($trnxs)+1);
                                        }
                                        $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified, trnx_lock_key)
                                        VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s)";
                                        $sql = $wpdb->prepare($sql, $current_user->ID, $adv_child_id, $item_id, $current_user->ID, $purchaseData->item_cost, $purchaseData->item_type, $today, $today, $lock_key);
                                        $inserted = $wpdb->query($sql);
                                        if($inserted === false){
                                            // Someone else's request claimed this exact slot first (the UNIQUE
                                            // key collision) - same message a real stock/cap miss would show,
                                            // never a raw DB error.
                                            $msg_content = __("No More Items Left",'bluerabbit');
                                            $data['message'] = $notification->pop($msg_content,'orange','cancel');
                                        }else{
                                            $msg_content = __('Item Purchased!','bluerabbit');
                                            $data['message'] = $notification->pop($msg_content,'green','check');
                                            $data['noClose'] = true;
                                            $data['success']=true;
                                            $data['sale']=true;
                                            BR_Activity::instance()->logActivity($adv_child_id, 'purchase','item',"$purchaseData->item_type",$item_id);
                                            BR_Player::instance()->resetPlayer($adv_child_id, $current_user->ID);
                                        }
                                    }else{
                                        $msg_content = __("You can't buy any more of this item",'bluerabbit');
                                        $data['message'] = $notification->pop($msg_content,'red','cancel');
                                    }
                                }else{
                                    $msg_content = __("Requirements not met",'bluerabbit');
                                    $data['message'] = $notification->pop($msg_content,'purple','lock');
                                }
                            }else{
                                $msg_content = __("Required level","bluerabbit").": $purchaseData->item_level";
                                $data['message'] = $notification->pop($msg_content,'purple','level');
                            }
                        }else{
                            $msg_content = __("Not enough funds",'bluerabbit');
                            $data['message'] = $notification->pop($msg_content,'red','cancel');
                        }
                    }else{
                        $msg_content = __("No More Items Left",'bluerabbit');
                        $data['message'] = $notification->pop($msg_content,'orange','cancel');
                    }
                }else{
                    $msg_content = __("You missed your chance!",'bluerabbit');
                    $data['message'] = $notification->pop($msg_content,'amber','deadline');
                }
            }else{
                $msg_content = __("You must wait until the item is open for purchase!",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'amber','deadline');
            }
        }else{
            $msg_content = __("Unauthorized access",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — pickupItem
    public function pickupItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $adventure_id = $_POST['adventure_id'];
        $item_id = $_POST['item_id'];
        $nonce = $_POST['nonce'];
        $data = array();
        $data['success'] = false;

        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if (!empty($adventure->adventure_gmt) && in_array($adventure->adventure_gmt, timezone_identifiers_list())) {
            date_default_timezone_set($adventure->adventure_gmt);
        } else {
            date_default_timezone_set('America/Mexico_City');
        }
        $today = date('Y-m-d H:i:s');

        $item = $wpdb->get_row("SELECT items.*, trnx.trnx_id FROM {$wpdb->prefix}br_items items
        LEFT JOIN {$wpdb->prefix}br_transactions trnx ON items.item_id=trnx.object_id AND (trnx.trnx_type='key' || trnx.trnx_type='tabi-piece' || trnx.trnx_type='consumable') AND trnx.player_id=$current_user->ID AND trnx.trnx_status='publish'
        WHERE items.item_id=$item_id");
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'pickup_item'.$current_user->ID.date('Ymd')) && !$item->trnx_id){
            // A permanent per-player-per-item flag, not a counted resource, so the lock
            // key needs no slot number - it just needs to be impossible for the same
            // player to ever claim the same item twice, concurrently or not.
            $lock_key = "pickup_{$current_user->ID}_{$item->item_id}";
            $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified, trnx_lock_key)
            VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s)";
            $sql = $wpdb->prepare($sql, $current_user->ID, $item->adventure_id, $item->item_id, $current_user->ID, 0, $item->item_type, $today, $today, $lock_key);
            $inserted = $wpdb->query($sql);

            if($inserted === false){
                $msg_content = __('Item already in backpack!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'indigo','backpack');
                $data['just_notify']=true;
                $data['success']=true;
            }else{
                $msg_content = __('Item Picked up!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green','check');
                $data['just_notify']=true;
                $data['success']=true;


                BR_Activity::instance()->logActivity($adventure_id,'pickup','item',"$item->item_type",$item->item_id);
                BR_Player::instance()->resetPlayer($adventure_id,$current_user->ID);
            }
        }elseif(wp_verify_nonce($nonce, 'pickup_item'.$current_user->ID.date('Ymd')) && $item->trnx_id){
            $msg_content = __('Item already in backpack!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'indigo','backpack');
            $data['just_notify']=true;
            $data['success']=true;
        }else{
            $msg_content = __('Unauthorized access!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify']=true;
        }
        echo json_encode($data);
        die();
    }

    // GM buys/assigns an item on behalf of a player who can't work the shop UI
    // themselves ("literally buying it as if it was the user") - same cost, same
    // stock/category/player-max enforcement as a normal purchase (a free grant would
    // distort the shop economy, and isn't what "buying on their behalf" means), but
    // bypasses the start-date/deadline window and the player-level gate, since
    // overriding those for one assisted player is the entire point of the feature.
    public function assignItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $adventure_id = intval($_POST['adventure_id']);
        $item_id = intval($_POST['item_id']);
        $target_player_id = intval($_POST['player_id']);
        $nonce = $_POST['nonce'];
        $data = array();
        $data['success'] = false;
        $notification = new Notification();
        $data['message_delay'] = 2000;
        $data['just_notify'] = true;

        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $isGM = (isset($current_user->roles[0]) && $current_user->roles[0] === 'administrator');
        if($adventure){
            if($adventure->adventure_owner == $current_user->ID){ $isGM = true; }
            elseif(in_array($adventure->player_adventure_role, array('gm','npc'))){ $isGM = true; }
        }

        if(!wp_verify_nonce($nonce, 'br_assign_item_nonce') || !$adventure || !$isGM){
            $msg_content = __("You don't have permission to do this.",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            echo json_encode($data);
            die();
        }

        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');

        $playerData = BR_Player::instance()->getPlayerAdventureData($adv_child_id, $target_player_id);
        $purchaseData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$item_id AND item_status='publish' AND adventure_id=$adv_parent_id");

        if($purchaseData->item_category_id){
            $trnxs = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM {$wpdb->prefix}br_transactions a
            JOIN {$wpdb->prefix}br_items b
            ON a.object_id=b.item_id
            WHERE a.adventure_id=%d AND a.player_id=%d AND b.item_category_id=%d AND trnx_status='publish'",
            $adv_child_id, $target_player_id, $purchaseData->item_category_id));
        }else{
            $trnxs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_transactions
            WHERE adventure_id=%d AND player_id=%d AND object_id=%d AND trnx_status='publish'",
            $adv_child_id, $target_player_id, $item_id));
        }
        $alltrnx = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_transactions WHERE object_id=$item_id AND trnx_type='consumable' AND trnx_status='publish' AND adventure_id=$adv_child_id");

        $left = $purchaseData->item_stock - count($alltrnx);

        if($left > 0){
            if($playerData->player_bloo >= $purchaseData->item_cost){
                $player_progress = BR_Progression::instance()->getPlayerProgress($adv_child_id, $target_player_id);
                $snapshot = BR_Conditions::instance()->buildProgressSnapshot($adv_parent_id, $adv_child_id, $target_player_id, $player_progress);
                if(BR_Item::instance()->evaluateItemAccess($adv_child_id, $purchaseData, $snapshot)){
                    if($purchaseData->item_player_max == 0 || (count($trnxs) < $purchaseData->item_player_max && $purchaseData->item_player_max > 0)){
                        if($purchaseData->item_stock > 0 && $purchaseData->item_stock < 99999){
                            $lock_key = "stock_{$item_id}_{$adv_child_id}_".(count($alltrnx)+1);
                        }elseif($purchaseData->item_player_max > 0){
                            $scope = $purchaseData->item_category_id ? "cat{$purchaseData->item_category_id}" : "item{$item_id}";
                            $lock_key = "cap_{$target_player_id}_{$scope}_{$adv_child_id}_".(count($trnxs)+1);
                        }else{
                            $lock_key = "buy_{$target_player_id}_{$item_id}_{$adv_child_id}_".(count($trnxs)+1);
                        }
                        $sql = "INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified, trnx_lock_key)
                        VALUES (%d, %d, %d, %d, %d, %s, %s, %s, %s)";
                        $sql = $wpdb->prepare($sql, $target_player_id, $adv_child_id, $item_id, $current_user->ID, $purchaseData->item_cost, $purchaseData->item_type, $today, $today, $lock_key);
                        $inserted = $wpdb->query($sql);
                        if($inserted === false){
                            $msg_content = __("No More Items Left",'bluerabbit');
                            $data['message'] = $notification->pop($msg_content,'orange','cancel');
                        }else{
                            $msg_content = __('Item Assigned!','bluerabbit');
                            $data['message'] = $notification->pop($msg_content,'green','check');
                            $data['success'] = true;
                            BR_Activity::instance()->logActivity($adv_child_id, 'gm-assign','item',"$purchaseData->item_type",$item_id,$target_player_id);
                            BR_Player::instance()->resetPlayer($adv_child_id, $target_player_id);
                        }
                    }else{
                        $msg_content = __("You can't buy any more of this item",'bluerabbit');
                        $data['message'] = $notification->pop($msg_content,'red','cancel');
                    }
                }else{
                    $msg_content = __("Requirements not met",'bluerabbit');
                    $data['message'] = $notification->pop($msg_content,'purple','lock');
                }
            }else{
                $msg_content = __("Not enough funds",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
            }
        }else{
            $msg_content = __("No More Items Left",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'orange','cancel');
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — checkItem
    public function checkItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $adventure_id = $_POST['adventure_id'];
        $item_id = $_POST['item_id'];
        $nonce = $_POST['nonce'];
        $step_id = $_POST['step_id'];
        $data = array();
        $data['success'] = false;
        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $today = date('Y-m-d H:i:s');
        $notification = new Notification();
        if(wp_verify_nonce($nonce, 'check_item'.$current_user->ID.date('Ymd').$step_id)){
            $itemVerification = $wpdb->get_row("SELECT items.*, steps.step_id, steps.step_order, steps.quest_id FROM {$wpdb->prefix}br_items items
            JOIN {$wpdb->prefix}br_steps steps ON items.item_id=steps.step_item
            WHERE items.item_id=$item_id AND steps.step_id=$step_id
            ");
            if($itemVerification){
                $msg_content = __("That's right!",'bluerabbit');
                $data['message'] = $notification->pop($msg_content,'green','check');
                $data['just_notify']=true;
                $data['success']=true;
                $theNextStepOrder = $itemVerification->step_order +1;
                $nextStepSearch = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$itemVerification->quest_id AND step_order = $theNextStepOrder AND step_status='publish'");

                if($nextStepSearch){ $nextStep = $nextStepSearch->step_order; }else{ $nextStep='last'; }
                $data['debug']=print_r($nextStepSearch,true);
                $data['jumpToNext']=$nextStep;
                BR_Activity::instance()->logActivity($adventure_id,'chose-correct-item','step-item',"$itemVerification->item_name",$itemVerification->step_id, $itemVerification->item_id);
            }else{
                $msg_content = __('Wrong Item!','bluerabbit');
                $data['message'] = $notification->pop($msg_content,'red','cancel');
                $data['just_notify']=true;
                $data['success']=false;
                $data['jumpToNext']=false;
                BR_Activity::instance()->logActivity($adventure_id,'chose-wrong-item','step-item',"",$step_id, $item_id);
            }
        }else{
            $msg_content = __('Unauthorized access!','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify']=true;
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — useItem
    public function useItem(){
        global $wpdb; $current_user = wp_get_current_user();

        $data = array();
        $adventure_id = $_POST['adventure_id'];
        $trnx_id =  $_POST['trnx_id'];
        $item_name = $_POST['item_name'];
        $use_item =  $_POST['use_item'] ? 1 : 0;
        $nonce = $_POST['nonce'];
        $player_id = $_POST['player_id'] ? $_POST['player_id'] : $current_user->ID;
        if($player_id != $current_user->ID){
            $player_target = get_userdata($player_id);
        }else{
            $player_target = $current_user;
        }
        $prepared = $wpdb->prepare("SELECT trnxs.*, items.item_name FROM {$wpdb->prefix}br_transactions trnxs
        JOIN {$wpdb->prefix}br_items items
        ON trnxs.object_id = items.item_id
        WHERE trnxs.trnx_id=%d AND trnxs.trnx_status='publish' AND items.item_status='publish'", $trnx_id);
        $query = $wpdb->query($prepared);
        $trnx = $wpdb->last_result;
        $trnx = $trnx[0];
        $data['success'] = false;
        if(wp_verify_nonce($nonce, 'br_use_item_nonce')){
            if($trnx){
                $sql = "UPDATE {$wpdb->prefix}br_transactions SET trnx_use=%d, player_id=%d, trnx_author=%d WHERE trnx_id=%s";
                $sql = $wpdb->prepare($sql, $use_item, $player_id, $current_user->ID, $trnx_id);
                $sql = $wpdb->query($sql);
                if($wpdb->rows_affected > 0){
                    if(!$use_item && $trnx->trnx_use){
                        // RETURN USE
                        $data['message'].="<h1>".__("Item returned!","bluerabbit")."</h1>";
                        $data['message'].="<h4><strong>".__("The player has returned the item.","bluerabbit")."</strong></h4>";
                        BR_Activity::instance()->logActivity($adventure_id,'return-use','item',"$trnx->item_name",$trnx->object_id);
                    }elseif($use_item && $trnx->trnx_use){
                        /// Duplicate USE
                        $data['message'].="<h1>".__("Item already used!","bluerabbit")."</h1>";
                        BR_Activity::instance()->logActivity($adventure_id,'duplicate-use','item',"$trnx->item_name",$trnx->object_id);
                    }elseif($use_item && !$trnx->trnx_use){
                        /// Register USE
                        $data['message'].="<h1>".__("Item redeemed!","bluerabbit")."</h1>";
                        $data['message'].="<h4><strong>".__("We've registered you picking up your reward.","bluerabbit")."</strong></h4>";
                        BR_Activity::instance()->logActivity($adventure_id,'use','item',"$trnx->item_name",$trnx->object_id);
                    }
                    $data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
                    $data['success']=true;
                    $data['location']="reload";
                    BR_Player::instance()->resetPlayer($adventure_id,$player_id);
                }else{
                    $data['message'].="<h1>".__("DB Error, please contact admin","bluerabbit")."</h1>";
                    $data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
                }
            }else{
                $data['message'].="<h1>".__("Transaction doesn't exist!","bluerabbit")."</h1>";
                $data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
            }
        }else{
            $data['message'].="<h1>".__("Unauthorized access","bluerabbit")."</h1>";
            $data['message'].="<h5>".__("click to close","bluerabbit")."</h5>";
        }
        echo json_encode($data);
        die();
    }

    // Source: functions/ajax.php — getItems
    public function getItems($adventure_id){
        global $wpdb; $current_user = wp_get_current_user();
        $qry = $wpdb->get_results("SELECT items.*, steps.quest_id, steps.step_title, steps.step_id, steps.step_order, quests.quest_title FROM {$wpdb->prefix}br_items items
        LEFT JOIN {$wpdb->prefix}br_steps steps ON items.item_id = steps.step_item
        LEFT JOIN {$wpdb->prefix}br_quests quests ON quests.quest_id = steps.quest_id
        WHERE items.adventure_id=$adventure_id GROUP BY items.item_id ORDER BY items.item_type, items.item_category, items.achievement_id, items.item_level, items.item_order, items.item_id");
        $result = array();
        foreach($qry as $o){
            if($o->item_status == 'trash'){
                $result['trash'][]=$o;
            }elseif($o->item_status == 'draft'){
                $result['draft'][]=$o;
            }elseif($o->item_status == 'publish'){
                $result['publish'][]=$o;
            }
            if($o->item_type == 'key'){
                $result['key'][]=$o;
            }elseif($o->item_type == 'consumable'){
                $result['consumable'][]=$o;
            }elseif($o->item_type == 'reward'){
                $result['reward'][]=$o;
            }elseif($o->item_type == 'tabi-piece'){
                $result['tabi-piece'][]=$o;
            }
        }
        return $result;
    }

    // Source: functions/ajax.php — getMyItems
    public function getMyItems($adventure_id, $player_id=null){
        global $wpdb;
        if(!$player_id){
            $current_user = wp_get_current_user();
            $player_id=$current_user->ID;
        }

        $qry = $wpdb->get_results( "SELECT items.item_id, items.item_name, items.item_description, items.item_secret_description, items.item_type, items.item_badge, items.item_secret_badge,  items.item_level,  items.item_cost, items.tabi_id, tabis.tabi_name,
            trnxs.object_id, trnxs.trnx_id, trnxs.trnx_type, trnxs.trnx_date, COUNT(items.item_id) AS total_consumables
            FROM  {$wpdb->prefix}br_items items
            JOIN {$wpdb->prefix}br_transactions trnxs
            ON items.item_id = trnxs.object_id

            LEFT JOIN {$wpdb->prefix}br_tabis tabis
            ON items.tabi_id = tabis.tabi_id


            WHERE items.adventure_id=$adventure_id AND items.item_status='publish' AND trnxs.player_id=$player_id AND (trnxs.trnx_type='consumable' OR trnxs.trnx_type='key' OR trnxs.trnx_type='reward' OR trnxs.trnx_type='tabi-piece') AND trnxs.trnx_use=0 AND trnxs.trnx_status='publish'
            GROUP BY trnxs.object_id, trnxs.trnx_type ORDER BY FIELD(items.item_type, 'consumable', 'key', 'tabi-piece', 'reward'), items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
        $result = array();
        foreach($qry as $o){
            $result['all'][]=$o;
            if($o->item_type == 'key' || $o->item_type == 'tabi-piece'){
                $result['key'][$o->item_id]=$o;
                $result['ids']['key']=$o->item_id;
            }elseif($o->item_type == 'consumable'){
                $result['consumable'][$o->item_id]=$o;
                $result['ids']['consumable']=$o->item_id;
            }elseif($o->item_type == 'reward'){
                $result['reward'][$o->item_id]=$o;
                $result['ids']['reward']=$o->item_id;
            }
        }
        return $result;
    }

    // ── Item Categories (real entities - replaces the old free-text 19-color enum) ──
    // br_items.adventure_id is child-scoped (see buyItem()/updateItem() - unlike quests/
    // tabis/achievements which live on the parent template), so categories and any
    // conditions attached to items/categories use the same child-scoped adventure_id.

    public function getCategories($adventure_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_item_categories WHERE adventure_id=%d AND category_status='publish' ORDER BY category_order ASC, category_id ASC",
            $adventure_id
        ));
    }

    public function saveCategory() {
        global $wpdb;
        $data = ['success' => false];
        $notification = new Notification();
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'item_category_nonce')) {
            $data['message'] = "<h1>" . __("Nonce!", "bluerabbit") . "</h1>";
            echo json_encode($data);
            die();
        }
        $adventure_id = (int) ($_POST['adventure_id'] ?? 0);
        $category_id  = (int) ($_POST['category_id'] ?? 0);
        $name  = sanitize_text_field(stripslashes_deep($_POST['category_name'] ?? ''));
        $color = sanitize_text_field($_POST['category_color'] ?? 'blue-grey');

        if ($adventure_id && $name) {
            if ($category_id) {
                $wpdb->update("{$wpdb->prefix}br_item_categories",
                    ['category_name' => $name, 'category_color' => $color],
                    ['category_id' => $category_id, 'adventure_id' => $adventure_id]
                );
            } else {
                $max_order = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT MAX(category_order) FROM {$wpdb->prefix}br_item_categories WHERE adventure_id=%d", $adventure_id
                ));
                $wpdb->insert("{$wpdb->prefix}br_item_categories", [
                    'adventure_id'   => $adventure_id,
                    'category_name'  => $name,
                    'category_color' => $color,
                    'category_order' => $max_order + 1,
                ]);
                $category_id = $wpdb->insert_id;
            }
            $data['success']     = true;
            $data['category_id'] = $category_id;
            $data['message']     = $notification->pop(__('Category saved', 'bluerabbit'), 'blue', 'check');
            $data['just_notify'] = true;
            $data['location']    = 'reload';
        }
        echo json_encode($data);
        die();
    }

    public function trashCategory() {
        global $wpdb;
        $data = ['success' => false];
        $notification = new Notification();
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'item_category_nonce')) {
            echo json_encode($data);
            die();
        }
        $category_id = (int) ($_POST['category_id'] ?? 0);
        if ($category_id) {
            $wpdb->update("{$wpdb->prefix}br_item_categories", ['category_status' => 'trash'], ['category_id' => $category_id]);
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}br_items SET item_category_id=NULL WHERE item_category_id=%d", $category_id));
            $data['success']     = true;
            $data['message']     = $notification->pop(__('Category removed', 'bluerabbit'), 'red', 'trash');
            $data['just_notify'] = true;
            $data['location']    = 'reload';
        }
        echo json_encode($data);
        die();
    }

    public function reorderCategories() {
        global $wpdb;
        $data = ['success' => false];
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'item_category_nonce')) {
            echo json_encode($data);
            die();
        }
        $order = (array) ($_POST['the_order'] ?? []);
        foreach ($order as $k => $category_id) {
            $wpdb->update("{$wpdb->prefix}br_item_categories", ['category_order' => $k], ['category_id' => (int) $category_id]);
        }
        $data['success'] = true;
        echo json_encode($data);
        die();
    }

    // ── Item / Category Conditions ──
    // Achievement + specific-milestone requirements reuse br_reqs (target_type='item' or
    // 'item_category'), same pattern as Tabi requirements (BR_Tabi::getTabiReqsMap). A
    // category's conditions AND with an item's own (both must pass - see evaluateItemAccess).

    const ITEM_TARGET_TYPES = ['item', 'item_category'];

    public function getItemReqsMap($adventure_id, $target_type) {
        global $wpdb;
        if (!in_array($target_type, self::ITEM_TARGET_TYPES)) return [];
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT target_id, req_type, req_object_id FROM {$wpdb->prefix}br_reqs
            WHERE adventure_id=%d AND target_type=%s",
            $adventure_id, $target_type
        ));
        $map = [];
        foreach ($rows as $r) {
            $tid = (int) $r->target_id;
            if ($r->req_type === 'quest') {
                $map[$tid]['quests'][] = (int) $r->req_object_id;
            } elseif ($r->req_type === 'achievement') {
                $map[$tid]['achievements'][] = (int) $r->req_object_id;
            }
        }
        return $map;
    }

    public function saveItemReqs($adventure_id, $target_type, $target_id, $quest_ids, $achievement_ids) {
        global $wpdb;
        if (!in_array($target_type, self::ITEM_TARGET_TYPES)) return false;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}br_reqs WHERE adventure_id=%d AND target_type=%s AND target_id=%d",
            $adventure_id, $target_type, $target_id
        ));
        $values = [];
        $placeholders = [];
        foreach ((array) $quest_ids as $q) {
            array_push($values, $adventure_id, (int) $target_id, (int) $q, 'quest');
            $placeholders[] = "(%d, 0, '$target_type', %d, %d, %s)";
        }
        foreach ((array) $achievement_ids as $a) {
            array_push($values, $adventure_id, (int) $target_id, (int) $a, 'achievement');
            $placeholders[] = "(%d, 0, '$target_type', %d, %d, %s)";
        }
        if (empty($placeholders)) return true;
        $sql = "INSERT INTO {$wpdb->prefix}br_reqs (adventure_id, quest_id, target_type, target_id, req_object_id, req_type) VALUES "
            . implode(', ', $placeholders);
        $wpdb->query($wpdb->prepare($sql, $values));
        return true;
    }

    // Checks an item's own conditions AND (if it belongs to a category) that category's
    // conditions - both must pass. $snapshot must carry 'fqs' and 'achievement_ids' at
    // minimum (see BR_Conditions::buildProgressSnapshot).
    public function evaluateItemAccess($adventure_id, $item, $snapshot) {
        if (!$this->itemReqsMet($adventure_id, 'item', $item->item_id, $snapshot)) return false;
        if (!empty($item->item_category_id) && !$this->itemReqsMet($adventure_id, 'item_category', $item->item_category_id, $snapshot)) return false;
        if (!BR_Conditions::instance()->evaluate($adventure_id, 'item', $item->item_id, $snapshot)) return false;
        if (!empty($item->item_category_id) && !BR_Conditions::instance()->evaluate($adventure_id, 'item_category', $item->item_category_id, $snapshot)) return false;
        return true;
    }

    private function itemReqsMet($adventure_id, $target_type, $target_id, $snapshot) {
        $reqs = $this->getItemReqsMap($adventure_id, $target_type)[(int) $target_id] ?? [];
        if (!empty($reqs['quests'])) {
            $missing = array_diff($reqs['quests'], $snapshot['fqs'] ?? []);
            if (!empty($missing)) return false;
        }
        if (!empty($reqs['achievements'])) {
            $missing = array_diff($reqs['achievements'], $snapshot['achievement_ids'] ?? []);
            if (!empty($missing)) return false;
        }
        return true;
    }

    // Renders the "Conditions" modal for an item or a category: achievement/specific-
    // milestone requirements (br_reqs) plus threshold conditions (br_conditions).
    // Loaded via AJAX rather than pre-rendered for every row up front.
    public function renderItemConditionsModal($target_type, $target_id) {
        global $wpdb;
        if (!in_array($target_type, self::ITEM_TARGET_TYPES)) return '';

        $target_name = '';
        $adventure_id = 0;
        if ($target_type === 'item') {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=%d", $target_id));
            if (!$item) return '';
            $target_name  = $item->item_name;
            $adventure_id = $item->adventure_id;
        } else {
            $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_item_categories WHERE category_id=%d", $target_id));
            if (!$category) return '';
            $target_name  = $category->category_name;
            $adventure_id = $category->adventure_id;
        }

        $quests = $wpdb->get_results($wpdb->prepare(
            "SELECT quest_id, quest_title FROM {$wpdb->prefix}br_quests
            WHERE adventure_id=%d AND quest_status IN ('publish','locked') AND quest_type IN ('quest','challenge','survey','mission')
            ORDER BY quest_order ASC, quest_title ASC",
            $adventure_id
        ));
        $achievements = $wpdb->get_results($wpdb->prepare(
            "SELECT achievement_id, achievement_name FROM {$wpdb->prefix}br_achievements
            WHERE adventure_id=%d AND achievement_status='publish' ORDER BY achievement_name ASC",
            $adventure_id
        ));

        $reqs_map   = $this->getItemReqsMap($adventure_id, $target_type);
        $item_reqs  = $reqs_map[(int) $target_id] ?? ['quests' => [], 'achievements' => []];
        $conditions = BR_Conditions::instance()->getConditions($adventure_id, $target_type, $target_id);
        $condition_values = [];
        foreach ($conditions as $c) { $condition_values[$c->condition_type] = $c->threshold_value; }

        $item_conditions_nonce = wp_create_nonce('item_conditions_nonce');
        $theFile = get_template_directory() . '/item-conditions-modal.php';
        if (!file_exists($theFile)) return '';
        ob_start();
        include($theFile);
        return ob_get_clean();
    }

    public function insertItemConditionsModal($p_target_type = null, $p_target_id = null) {
        $target_type = $p_target_type ? $p_target_type : ($_POST['target_type'] ?? '');
        $target_id   = $p_target_id   ? $p_target_id   : ($_POST['target_id']   ?? 0);
        echo $this->renderItemConditionsModal($target_type, $target_id);
        die();
    }

    public function saveItemConditions() {
        $data = ['success' => false];
        $notification = new Notification();

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'item_conditions_nonce')) {
            $data['message'] = "<h1>" . __("Nonce!", "bluerabbit") . "</h1>";
            echo json_encode($data);
            die();
        }

        $target_type  = $_POST['target_type'] ?? '';
        $target_id    = (int) ($_POST['target_id'] ?? 0);
        $adventure_id = (int) ($_POST['adventure_id'] ?? 0);
        if (in_array($target_type, self::ITEM_TARGET_TYPES) && $target_id && $adventure_id) {
            $quest_ids       = array_map('intval', (array) ($_POST['quest_ids'] ?? []));
            $achievement_ids = array_map('intval', (array) ($_POST['achievement_ids'] ?? []));
            $this->saveItemReqs($adventure_id, $target_type, $target_id, $quest_ids, $achievement_ids);

            $conditions = [];
            foreach (BR_Conditions::CONDITION_TYPES as $type => $label) {
                $val = $_POST['conditions'][$type] ?? '';
                if ($val !== '') {
                    $conditions[] = ['condition_type' => $type, 'threshold_value' => (float) $val];
                }
            }
            BR_Conditions::instance()->saveConditions($adventure_id, $target_type, $target_id, $conditions);

            $data['success']     = true;
            $data['message']     = $notification->pop(__('Conditions saved', 'bluerabbit'), 'blue', 'check');
            $data['just_notify'] = true;
        }

        echo json_encode($data);
        die();
    }
}

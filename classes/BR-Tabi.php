<?php
class BR_Tabi {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    // From functions/ajax.php
    public function getTabis($adventure_id){
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_tabis
        WHERE adventure_id=$adventure_id AND tabi_status='publish' ORDER BY tabi_level ASC, tabi_id ASC");
        return $result;
    }

    // From functions/ajax.php
    public function getJourneyAssets($adventure_id) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_journey_assets
            WHERE adventure_id=$adventure_id AND asset_status='publish'
            ORDER BY asset_z ASC, asset_id ASC");
    }

    // From functions/ajax.php
    public function renderJourneyAssetHTML($asset_id) {
        global $wpdb;
        $a = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_journey_assets WHERE asset_id=$asset_id AND asset_status='publish'");
        if(!$a) return '';
        $journey_asset_nonce = wp_create_nonce('journey_asset_nonce');
        $builder_tabis = $this->getTabis($a->adventure_id);
        $theFile = get_template_directory() . '/journey-asset-builder.php';
        if(!file_exists($theFile)) return '';
        ob_start();
        include($theFile);
        return ob_get_clean();
    }

    // From functions/ajax.php
    public function insertJourneyAssetRow($asset_id) {
        echo $this->renderJourneyAssetHTML($asset_id);
        die();
    }

    // From functions/ajax.php
    public function getTabiPrerequisitesMap($adventure_id) {
        global $wpdb;
        $rows = $wpdb->get_results("
            SELECT tp.tabi_id, tp.requires_tabi_id
            FROM {$wpdb->prefix}br_tabi_prerequisites tp
            JOIN {$wpdb->prefix}br_tabis t ON tp.tabi_id = t.tabi_id
            WHERE t.adventure_id = $adventure_id
        ");
        $map = [];
        foreach($rows as $r) {
            $map[$r->tabi_id][] = (int)$r->requires_tabi_id;
        }
        return $map;
    }

    // From functions/ajax.php
    public function getCompletedTabiIds($adventure_id, $player_id) {
        global $wpdb;
        $totals = $wpdb->get_results("
            SELECT tabi_id, COUNT(*) AS total
            FROM {$wpdb->prefix}br_quests
            WHERE adventure_id = $adventure_id AND tabi_id > 0 AND quest_status = 'publish' AND (mech_optional IS NULL OR mech_optional = 0)
            GROUP BY tabi_id
        ");
        if(empty($totals)) return [];

        $done = $wpdb->get_results("
            SELECT q.tabi_id, COUNT(*) AS completed
            FROM {$wpdb->prefix}br_player_posts pp
            JOIN {$wpdb->prefix}br_quests q ON pp.quest_id = q.quest_id
            WHERE q.adventure_id = $adventure_id AND q.tabi_id > 0
            AND (q.mech_optional IS NULL OR q.mech_optional = 0)
            AND pp.player_id = $player_id AND pp.pp_status = 'publish'
            GROUP BY q.tabi_id
        ");
        $done_map = [];
        foreach($done as $d) { $done_map[$d->tabi_id] = (int)$d->completed; }

        $completed = [];
        foreach($totals as $t) {
            if(($done_map[$t->tabi_id] ?? 0) >= (int)$t->total) {
                $completed[] = (int)$t->tabi_id;
            }
        }
        return $completed;
    }

    // From functions/ajax.php
    public function getTabi($tabi_id){
        global $wpdb;
        $data = [];

        $data['tabi'] =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
        if($data['tabi']){
            $pieces = $wpdb->get_results("SELECT items.* FROM {$wpdb->prefix}br_items items
            JOIN {$wpdb->prefix}br_tabis tabis ON items.tabi_id = tabis.tabi_id
            WHERE items.tabi_id=$tabi_id AND items.item_status = 'publish' AND tabis.tabi_status='publish' ORDER BY items.item_z DESC");
            $data['pieces'] = $pieces;
            return $data;
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function getMyTabi($tabi_id){
        global $wpdb;
        $current_user = wp_get_current_user();
        $data = [];
        $data['tabi'] =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
        $adventure = BR_Adventure::instance()->getAdventure($data['tabi']->adventure_id);

        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
        if($data['tabi']){
            $tabi_id = $data['tabi']->tabi_id;
            $pieces =$wpdb->get_results( "SELECT items.*, tabis.tabi_name,
            trnxs.object_id, trnxs.trnx_id, trnxs.player_id, trnxs.trnx_type, trnxs.trnx_date
            FROM  {$wpdb->prefix}br_items items
            LEFT JOIN {$wpdb->prefix}br_transactions trnxs
            ON items.item_id = trnxs.object_id AND trnxs.trnx_type='tabi-piece' AND trnxs.trnx_status='publish' AND trnxs.adventure_id=$adv_child_id

            JOIN {$wpdb->prefix}br_tabis tabis
            ON items.tabi_id = tabis.tabi_id


            WHERE items.adventure_id=$adv_parent_id AND items.item_status='publish' AND tabis.tabi_as_category=0
            ORDER BY items.tabi_id ASC, items.item_level ASC, items.item_name ASC, items.item_id ASC");
            $data['pieces'] = $pieces;
            return $data;
        }else{
            return false;
        }
    }

    // From functions/ajax.php
    public function saveTabiPiecePosition(){
        global $wpdb; $current_user = wp_get_current_user();
        $item_id = $_POST['item_id'];
        $item_data = $_POST['item_data'];
        $notification = new Notification();
        $data['just_notify'] =true;
        $item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items items WHERE item_id=$item_id AND item_status='publish' AND item_type='tabi-piece'");
        $x = $item_data['item_x'];
        $y = $item_data['item_y'];
        $z = $item_data['item_z'];
        $scale = $item_data['item_scale'];
        $rotation = $item_data['item_rotation'];
        if($item){
            $sql = "UPDATE {$wpdb->prefix}br_items SET item_x=%s, item_y=%s, item_z=%d, item_scale=%s, item_rotation=%s WHERE item_id=$item->item_id";
            $sql = $wpdb->prepare ($sql , $x, $y, $z, $scale, $rotation );
            $wpdb->query($sql);
            $data['success'] = true;
        }else{
            $data['success'] = false;
            $msg_content = __('Item not found','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
        }
        echo json_encode($data);
        die();


    }

    // From functions/ajax.php
    public function updateMilestonePosition(){
        global $wpdb; $current_user = wp_get_current_user();
        $milestone_id = $_POST['milestone_id'];
        $milestone_data = $_POST['milestone_data'];
        $notification = new Notification();
        $data['just_notify'] =true;

        $milestone = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests quests WHERE quest_id=$milestone_id AND quest_status='publish'");


        $x = $milestone_data['x'];
        $y = $milestone_data['y'];
        $z = $milestone_data['z'];
        $top = $milestone_data['top'];
        $left = $milestone_data['left'];
        $rotation = $milestone_data['rotation'];
        if($milestone){
            $sql = "UPDATE {$wpdb->prefix}br_quests SET milestone_x=%s, milestone_y=%s, milestone_z=%s, milestone_top=%d, milestone_left=%d, milestone_rotation=%d WHERE quest_id=$milestone->quest_id";
            $sql = $wpdb->prepare ($sql, $x, $y, $z, $top, $left, $rotation );
            $wpdb->query($sql);
            $data['success'] = true;
            $msg_content = __('Milestone updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','check');
        }else{
            $data['success'] = false;
            $msg_content = __('Milestone not found','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
        }
        echo json_encode($data);
        die();


    }

    // From functions/ajax.php
    public function addTabi(){
        global $wpdb; $player = wp_get_current_user();
        $notification = new Notification();
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        $adventure = BR_Adventure::instance()->getAdventure($adventure_id);
        $data = [];
        if($adventure && wp_verify_nonce($nonce, 'add_tabi_nonce')){
            if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
            $today = date('Y-m-d H:i:s');
            $tabis = $this->getTabis($adventure_id);
            $new_tabi_name = __("New Tabi","bluerabbit")." ".count($tabis)+1;
            $tabi_insert = "INSERT INTO {$wpdb->prefix}br_tabis (`adventure_id`, `tabi_name`) VALUES (%d, %s)";
            $tabi_insert = $wpdb->query( $wpdb->prepare("$tabi_insert ", $adventure_id, $new_tabi_name));
            $tabi_id = $wpdb->insert_id;
            BR_Activity::instance()->logActivity($adventure_id,'add','tabi');
            $data['success'] = true;
            $data['new_tabi_id'] = $tabi_id;
            $msg_content = __('New Tabi Created','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','sabotage');
            $data['just_notify'] =true;
        }else{
            $data['success'] = false;
            $msg_content = __('Error','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
            $data['just_notify'] =true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/ajax.php
    public function insertTabiRow($p_tabi_id){
        global $wpdb;
        $current_user = wp_get_current_user();
        $tabi_id = $p_tabi_id ? $p_tabi_id : $_POST['tabi_id'];
        if($tabi_id){
            $a = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE tabi_id=$tabi_id AND tabi_status='publish'");
            if($a) {
                $tabis = $this->getTabis($a->adventure_id);
                $tabi_prereq_nonce = wp_create_nonce('tabi_prereq_nonce');
            }
            $theFile = (get_template_directory()."/tabi-row.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }else{
                return false;
            }
        }else{
            return false;
        }
        die();
    }

    // From functions/adventure-management.php
    public function setDimensions(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $type = $_POST['type'];
        $id = $_POST['id'];
        $width = $_POST['width'];
        $height = $_POST['height'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'dimensions_nonce')){
            if($type == 'tabi'){
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_width=%d, tabi_height=%d WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$width,$height,$id,$adventure_id);
            }
            $wpdb->query($sql);
            $data['success'] = true;
            BR_Activity::instance()->logActivity($adventure_id, "set","dimensions","$type",$id);

            $notification = new Notification();
            $msg_content = __('Dimensions updated','bluerabbit');
            $data['message'] = $notification->pop($msg_content,'deep-purple','stats');
            $data['just_notify'] =true;
            $data['new_dimensions_nonce'] = wp_create_nonce('dimensions_nonce');
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function addJourneyAsset(){
        global $wpdb;
        $data = [];
        $adventure_id = intval($_POST['adventure_id']);
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->insert("{$wpdb->prefix}br_journey_assets", [
                'adventure_id' => $adventure_id,
                'asset_top' => 350, 'asset_left' => 350,
                'asset_width' => 200, 'asset_z' => 5, 'asset_rotation' => 0,
                'asset_status' => 'publish',
            ], ['%d','%d','%d','%d','%d','%d','%s']);
            $asset_id = $wpdb->insert_id;
            $data['html'] = $this->renderJourneyAssetHTML($asset_id);
            $data['success'] = true;
            $data['asset_id'] = $asset_id;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function trashJourneyAsset(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets", ['asset_status'=>'trash'], ['asset_id'=>$asset_id], ['%s'], ['%d']);
            $data['success'] = true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function duplicateJourneyAsset(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $src = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_journey_assets WHERE asset_id=$asset_id");
            if($src){
                $wpdb->insert("{$wpdb->prefix}br_journey_assets", [
                    'adventure_id' => $src->adventure_id,
                    'tabi_id'      => $src->tabi_id ?? 0,
                    'asset_image'  => $src->asset_image,
                    'asset_top'    => $src->asset_top  + 30,
                    'asset_left'   => $src->asset_left + 30,
                    'asset_width'  => $src->asset_width,
                    'asset_z'      => $src->asset_z,
                    'asset_rotation' => $src->asset_rotation,
                    'asset_type'   => $src->asset_type ?? 'graphic',
                    'asset_link'   => $src->asset_link ?? '',
                    'asset_status' => 'publish',
                ], ['%d','%d','%s','%d','%d','%d','%d','%d','%s','%s','%s']);
                $new_id = $wpdb->insert_id;
                $data['html'] = $this->renderJourneyAssetHTML($new_id);
                $data['success'] = true;
                $data['asset_id'] = $new_id;
            }
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveJourneyAssetPosition(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $top  = intval($_POST['top']);
        $left = intval($_POST['left']);
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets",
                ['asset_top'=>$top, 'asset_left'=>$left],
                ['asset_id'=>$asset_id], ['%d','%d'], ['%d']);
            $data['success'] = true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveJourneyAssetProperties(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $width    = intval($_POST['width']);
        $z        = intval($_POST['z']);
        $rotation = intval($_POST['rotation']);
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets",
                ['asset_width'=>$width, 'asset_z'=>$z, 'asset_rotation'=>$rotation],
                ['asset_id'=>$asset_id], ['%d','%d','%d'], ['%d']);
            $data['success'] = true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setJourneyAssetImage(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $image    = esc_url_raw($_POST['image']);
        $nonce    = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets",
                ['asset_image'=>$image], ['asset_id'=>$asset_id], ['%s'], ['%d']);
            $data['success'] = true;
            $data['image']   = $image;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveJourneyAssetMeta(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $type     = sanitize_text_field($_POST['asset_type'] ?? 'graphic');
        $link     = esc_url_raw($_POST['asset_link'] ?? '');
        $nonce    = $_POST['nonce'];
        if(!in_array($type, ['graphic','widget-status','widget-leaderboard'])) $type = 'graphic';
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets",
                ['asset_type'=>$type, 'asset_link'=>$link],
                ['asset_id'=>$asset_id], ['%s','%s'], ['%d']);
            $data['success'] = true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveJourneyAssetTabi(){
        global $wpdb;
        $data = [];
        $asset_id = intval($_POST['asset_id']);
        $tabi_id  = intval($_POST['tabi_id']);
        $nonce    = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'journey_asset_nonce')){
            $wpdb->update("{$wpdb->prefix}br_journey_assets",
                ['tabi_id' => $tabi_id],
                ['asset_id' => $asset_id], ['%d'], ['%d']);
            $data['success'] = true;
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setTabiOnJourney(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();

        $data['success'] = false;
        $id = $_POST['id'];
        $adventure_id = $_POST['adventure_id'];
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'tabi_on_journey_nonce')){
            $remove_tabis_from_journey_sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_on_journey=0 WHERE adventure_id=%d";
            $remove_tabis_from_journey_sql = $wpdb->prepare ($remove_tabis_from_journey_sql,$adventure_id);
            $wpdb->query($remove_tabis_from_journey_sql);
            if($id > 0){
                BR_Activity::instance()->logActivity($adventure_id, "set","tabi_on_journey","tabi",$id);
                $msg_content = __('Tabi assigned to journey!','bluerabbit');
                $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_on_journey=1 WHERE tabi_id=%d AND adventure_id=%d";
                $sql = $wpdb->prepare ($sql,$id,$adventure_id);
                $wpdb->query($sql);
            }else{
                BR_Activity::instance()->logActivity($adventure_id, "removed","tabi_on_journey","tabi",$id);
                $msg_content = __('Tabi removed from journey!','bluerabbit');
            }

            $data['success'] = true;

            $notification = new Notification();
            $data['message'] = $notification->pop($msg_content,'deep-purple','stats');
            $data['just_notify'] =true;
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function setTabiAsCategory(){
        global $wpdb;
        $data = [];
        $data['success'] = false;
        $id           = intval($_POST['id']);
        $val          = intval($_POST['val']);
        $adventure_id = intval($_POST['adventure_id']);
        $nonce        = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'tabi_as_category_nonce')){
            $wpdb->update(
                "{$wpdb->prefix}br_tabis",
                ['tabi_as_category' => $val],
                ['tabi_id' => $id, 'adventure_id' => $adventure_id],
                ['%d'], ['%d', '%d']
            );
            $msg_content = $val
                ? __('Tabi used as category!','bluerabbit')
                : __('Tabi removed from categories!','bluerabbit');
            BR_Activity::instance()->logActivity($adventure_id, $val ? "set" : "removed", "tabi_as_category", "tabi", $id);
            $data['success'] = true;
            $notification = new Notification();
            $data['message'] = $notification->pop($msg_content, 'teal', 'stats');
            $data['just_notify'] = true;
        } else {
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveTabiPrerequisites(){
        global $wpdb;
        $data = [];
        $data['success'] = false;
        $tabi_id   = intval($_POST['tabi_id']);
        $requires  = isset($_POST['requires']) && is_array($_POST['requires']) ? array_map('intval', $_POST['requires']) : [];
        $nonce     = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'tabi_prereq_nonce')) {
            $wpdb->delete("{$wpdb->prefix}br_tabi_prerequisites", ['tabi_id' => $tabi_id], ['%d']);
            foreach($requires as $req_id) {
                if($req_id > 0 && $req_id !== $tabi_id) {
                    $wpdb->insert("{$wpdb->prefix}br_tabi_prerequisites", [
                        'tabi_id'          => $tabi_id,
                        'requires_tabi_id' => $req_id,
                    ], ['%d','%d']);
                }
            }
            $data['success'] = true;
            $notification = new Notification();
            $data['message'] = $notification->pop(__('Prerequisites saved!','bluerabbit'), 'teal', 'check');
            $data['just_notify'] = true;
        } else {
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveTabiPosition(){
        global $wpdb; $current_user = wp_get_current_user();
        $data = array();
        $data['success'] = false;
        $tabi_id = intval($_POST['tabi_id']);
        $top     = intval($_POST['top']);
        $left    = intval($_POST['left']);
        $nonce   = $_POST['nonce'];
        if(wp_verify_nonce($nonce, 'tabi_position_nonce')){
            $sql = "UPDATE {$wpdb->prefix}br_tabis SET tabi_top=%d, tabi_left=%d WHERE tabi_id=%d";
            $sql = $wpdb->prepare($sql, $top, $left, $tabi_id);
            $wpdb->query($sql);
            $data['success'] = true;
        }else{
            $data['message'] = "<h1>".__("Nonce!","bluerabbit")."</h1>".'<h4>'.__('click to close','bluerabbit').'</h4>';
        }
        echo json_encode($data);
        die();
    }

    // From functions/adventure-management.php
    public function saveTabiSize(){
        global $wpdb;
        $data = [];
        $tabi_id = intval($_POST['tabi_id']);
        $width   = intval($_POST['width']);
        $height  = intval($_POST['height']);
        $nonce   = $_POST['nonce'];
        $data['just_notify'] =true;
        $notification = new Notification();



        if(wp_verify_nonce($nonce, 'tabi_position_nonce')){
            $wpdb->update(
                "{$wpdb->prefix}br_tabis",
                ['tabi_width' => $width, 'tabi_height' => $height],
                ['tabi_id' => $tabi_id],
                ['%d', '%d'], ['%d']
            );
            $data['success'] = true;
            $msg_content = __("Tabi size updated".$width." - ".$height."- tabi:".$tabi_id,'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'green','check');
        }else{
            $data['success'] = false;
            $msg_content = __("Can't assign that achievement",'bluerabbit');
            $data['message'] = $notification->pop($msg_content,'red','cancel');
        }
        $data['debug'] = "Tabi size updated".$width." - ".$height."- tabi:".$tabi_id;
        echo json_encode($data);
        die();
    }
}

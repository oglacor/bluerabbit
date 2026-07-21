<?php
class BR_Utils {
    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {}

    public function create_slug($string){
       $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
       return $slug;
    }

    public function user_has_role($user_id, $role_name)
    {
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        return in_array($role_name, $user_roles);
    }

    public function shuffle_assoc($list) {
      if (!is_array($list)) return $list;
      $keys = array_keys($list);
      shuffle($keys);
      $random = array();
      foreach ($keys as $key) {
        $random[$key] = $list[$key];
      }
      return $random;
    }

    public function identical_values( $arrayA , $arrayB ) {
        sort( $arrayA );
        sort( $arrayB );
        return $arrayA == $arrayB;
    }

    public function substrwords($text, $maxchar, $end='...') {
        if (strlen($text) > $maxchar || $text == '') {
            $words = preg_split('/\s/', $text);
            $output = '';
            $i      = 0;
            while (1) {
                $length = strlen($output)+strlen($words[$i]);
                if ($length > $maxchar) {
                    break;
                }
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        }
        else {
            $output = $text;
        }
        return $output;
    }

    public function get_time_ago( $time, $adventure_id=0 ){
        global $wpdb;
        $adventure = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_id=$adventure_id");
        if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
        $time_difference = time() - $time;
        if( $time_difference < 1 ) { return __('less than 1 second ago',"bluerabbit"); }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  __('month',"bluerabbit"),
                    24 * 60 * 60            =>  __('day',"bluerabbit"),
                    60 * 60                 =>  __('hour',"bluerabbit"),
                    60                      =>  __('minute',"bluerabbit"),
                    1                       =>  __('second',"bluerabbit")
        );
        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;
            if( $d >= 1 )
            {
                $t = round( $d );
                return $t . ' ' . $str . ( $t > 1 ? 's' : '' );
            }
        }
    }

    public function br_est_tokens($text) { return max(1, (int)ceil(mb_strlen($text,'UTF-8')/4)); }

    public function br_est_cost($model, $in_toks, $out_cap = 800) {
      $prices = [
        'gpt-4o-mini'   => ['in'=>0.000150/1000,'out'=>0.000600/1000],
        'gpt-3.5-turbo' => ['in'=>0.000500/1000,'out'=>0.001500/1000],
        'gpt-4o'        => ['in'=>0.000500/1000,'out'=>0.001500/1000],
      ];
      $p = $prices[$model] ?? $prices['gpt-4o-mini'];
      return round($in_toks*$p['in'] + $out_cap*$p['out'], 4);
    }

    public function generate_timezone_list(){
        static $regions = array(
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach( $regions as $region )
        {
            $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
        }

        $timezone_offsets = array();
        foreach( $timezones as $timezone )
        {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        ksort($timezone_offsets);

        $timezone_list = array();
        foreach( $timezone_offsets as $timezone => $offset ) {
            $t = new DateTimeZone($timezone);
            $c = new DateTime(null, $t);
            $current_time = $c->format('g:i A');
            $timezone_list[$timezone] = "$timezone - $current_time";
        }

        return $timezone_list;
    }

    public function random_str($length, $keyspace = '!@#$&0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public function notFound($p_message,$p_color){
        $message = $p_message ? $p_message : __("Empty","bluerabbit");
        $color = $p_color ? $p_color : "blue-grey";
        $notFound = '
            <div class="highlight padding-10 '.$color.'-bg-50 text-center">
                <span class="icon-group">
                    <span class="icon-content">
                        <span class="icon icon-cancel"></span>'.$message.'
                    </span>
                </span>
            </div>
        ';
        return $notFound;
    }

    public function addNewButton($p_message,$p_color,$type=NULL, $adventure_id=NULL){
        if($type && $adventure_id){
            $message = $p_message ? $p_message : __("Add new","bluerabbit");
            $color = $p_color ? $p_color : "blue-grey";
            $hex = br_color_to_hex($color);
            $button = '
                <div class="highlight padding-10 white-bg-50 text-center">
                    <a href="'.get_bloginfo('url').'/new-'.$type.'/?adventure_id='.$adventure_id.'" class="form-ui" style="background-color:'.$hex.'">
                        <span class="icon icon-add"></span>'.$message.'
                    </a>
                </div>
            ';
            return $button;
        }else{
            return false;
        }
    }

    public function stepTag($the_text="", $id=NULL, $classes=NULL){
        include (TEMPLATEPATH.'/step-tag.php');
    }

    public function cmp($a, $b) {
        return strcmp($a["last_name"], $b["last_name"]);
    }

    public function toMoney($value, $symbol = '', $decimals = 0){
        return $symbol . ($value < 0 ? '-' : '') . number_format(abs($value), $decimals);
    }

    public function insertGalleryItem($thumb_id, $file=NULL, $callback=NULL){
        if($thumb_id){
            $file     = $file ?? '';
            $callback = $callback ?? '';
            $theFile = (get_template_directory()."/gallery-item.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }
    }

    public function insertMultimediaItem($thumb_id, $file, $index, $callback=NULL){
        if($thumb_id){
            $theFile = (get_template_directory()."/multimedia-item.php");
            if(file_exists($theFile)) {
                include ($theFile);
            }
        }else{
            echo "<h1>No thumb_id found</h1>";
        }
    }

    public function createQR($args){
        require_once(get_template_directory()."/libs/phpqrcode/qrlib.php");
        $uploads = wp_upload_dir();

        if (!empty($uploads['error'])) {
            return new WP_Error('upload_dir_error', $uploads['error']);
        }

        $filename = $args['filename'];
        $file_path = trailingslashit($uploads['path']) . $filename;
        $file_url  = trailingslashit($uploads['url']) . $filename;

        $qrcode_content = $args['content'];
        QRcode::png(
            $qrcode_content,
            $file_path,
            QR_ECLEVEL_H, 40
        );

        if($args['logo']){
            $QR = imagecreatefrompng($file_path);
            $logo = imagecreatefromstring(file_get_contents($args['logo']));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width/5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $logo_x = ($QR_width - $logo_qr_width) / 2;
            $logo_y = ($QR_height - $logo_qr_height) / 2;
            imagecopyresampled($QR, $logo, $logo_x, $logo_y, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            imagepng($QR,$file_path);
        }

        if(file_exists($file_path)){
            return($file_url);
        }else{
            return false;
        }
    }
}

<?php
class Notification{
	
	public $color='blue';
	public $icon = 'check';
	public $message = '';
	public $headline = '';

    public function __construct() {
        add_action('wp_ajax_br_notify', [$this, 'notify']);
    }
	
	// Redesigned 2026-07-09 to match the app's br-* HUD look (dark glass
	// card, cyan-bordered, condensed uppercase type) instead of the old
	// flat white icon-group/button-icon/line shape - same shape as the
	// CI4 rewrite's partials/notification.php, using the same
	// br_color_to_hex()/br_color_style() helpers already shared between
	// both codebases. Must stay a top-level <li> - script.js's
	// displayAjaxResponse() selects "#notify-message ul.content
	// li:last-child" to add the .active class that slides it into view.
	function pop($m, $c='blue', $i='check'){
		$this->color = $c;
		$this->icon = $i;
		$this->message = $m ? $m : __('No message','bluerabbit');
		if(!$m){
			$this->color = 'red';
			$this->icon = 'cancel';
		}
		$hex = br_color_to_hex($this->color);
		return "<li class='br-notify' style='border-left-color:{$hex}'>
			<span class='br-notify-icon' style='".br_color_style($this->color, 'background-color', 0.16)."; color:{$hex}'>
				<span class='icon icon-{$this->icon}'></span>
			</span>
			<span class='br-notify-text'>{$this->message}</span>
		</li>";
	}
	function energy($ep=10){
		$this->ep = $ep;
		if($this->ep > 0){
			$content = "<span class='button-form-ui cyan-A400'><span class='icon icon-activity'></span>+$this->ep</span>";
		}else{
			$content = "<span class='button-form-ui red-A400'><span class='icon icon-activity'></span>+0</span>";
		}
		return($content);
	}
	function notify(){
        $message = sanitize_text_field($_POST['message'] ?? '');
        $icon = $_POST['icon'] ?? 'warning';
        $color = $_POST['color'] ?? 'amber';
        return $this->pop($message, $color, $icon);
	}
}

<?php
class Notification{
	
	public $color='blue';
	public $icon = 'check';
	public $message = '';
	public $headline = '';

    public function __construct() {
        add_action('wp_ajax_br_notify', [$this, 'notify']);
    }
	
	function pop($m, $c='blue', $i='check'){
		$this->color = $c;
		$this->icon = $i;
		$this->message = $m;
		if($m){
			$content = "<li class='border {$this->color}-bg-400 {$this->color}-border-800 white-color'>
				<span class='icon-group'>
					<span class='icon-button font _24 sq-40  icon-sm white-bg'>
						<span class='icon icon-{$this->icon} {$this->color}-400'></span>
					</span>
					<span class='icon-content'>
						<span class='line font _16'>".$this->message."</span>
					</span>
				</span>
			</li>";
		}else{
			$content = "<li class='border white-bg red-border-A700 red-bg-A400 white-color'>
				<span class='icon-group'>
					<span class='icon-button font _24 sq-40  icon-sm white-bg'>
						<span class='icon icon-cancel red-A400'></span>
					</span>
					<span class='icon-content'>
						<span class='line font _16'>".__('No message',"bluerabbit")."</span>
					</span>
				</span>
			</li>";
		}
		return($content);
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

        $this->message = sanitize_text_field($_POST['message'] ?? '');
        $this->icon = $_POST['icon'] ?? 'warning';
        $this->color = $_POST['color'] ?? 'amber';

        $content = "<li class='border {$this->color}-bg-400 {$this->color}-border-800 white-color'>
            <span class='icon-group'>
                <span class='icon-button font _24 sq-40  icon-sm white-bg'>
                    <span class='icon icon-{$this->icon} {$this->color}-400'></span>
                </span>
                <span class='icon-content'>
                    <span class='line font _16'>".$this->message."</span>
                </span>
            </span>
        </li>";
		return($content);
	}
}

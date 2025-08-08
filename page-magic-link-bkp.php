<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php 
$code = $_GET['c'];


if($code){
	if(isset($_GET['adv'])){
		$adv_id = $adv_id = $_GET['adv'];
	}elseif ($config['default_adventure']['value']){
		$adv_id = $config['default_adventure']['value'];
	}else{
		?>
		<script> document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php die();
	}
	$adventure = getAdventure($adv_id);
	$code=strtolower($code);
	$c = $wpdb->get_row("SELECT 
	ach.*, 
	code.code_id, code.code_value, code.code_status, code.code_redeemed, code.player_id as redeemed_player_id, 
	player.player_id as achieved_player
	
	FROM {$wpdb->prefix}br_achievements ach
	LEFT JOIN {$wpdb->prefix}br_achievement_codes code
	ON ach.achievement_id = code.achievement_id AND code.code_value = '$code'
	
	LEFT JOIN {$wpdb->prefix}br_player_achievement player
	ON ach.achievement_id = player.achievement_id AND player.player_id=$current_user->ID
	
	WHERE (code.code_value='$code' OR ach.achievement_code ='$code') AND ach.achievement_status='publish' AND ach.adventure_id=$adventure->adventure_id");
	if($c){
		$enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_adventure_status='in' AND player_id=$current_user->ID");
		if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
		$today = date('Y-m-d H:i:s');
		if(($roles[0]=='br_player' || $roles[0]=='administrator' || $roles[0]=='br_game_master') && $enrolled && $adventure){
			$nonce = wp_create_nonce('blue_rabbit_magic_code_nonce');
		}
	}
	
	if(wp_verify_nonce($nonce, 'blue_rabbit_magic_code_nonce') && $c){
		$error = array();
		if($c->achievement_max > 0){
			$awarded = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_player_achievement WHERE adventure_id=$adventure->adventure_id AND achievement_id=$c->achievement_id");
			if(count($awarded) >= $c->achievement_max){
				$error['max']= __('Max awards reached',"bluerabbit");
			}
		}
		if($c->achievement_deadline && $c->achievement_deadline != '0000-00-00 00:00:00'){
			$now = date('YmdHi');
			$deadline = date('YmdHi',strtotime($c->achievement_deadline));
			if($now > $deadline){
				$error['deadline']= __('Achievement no longer available',"bluerabbit");
			}
		}
		if($c->code_status =='redeem'){
			$error['carrot']= __('This code has already been used!',"bluerabbit");
		}
		if($c->code_status =='expired'){
			$error['deadline']= __('This code already expired!',"bluerabbit");
		}
		if(($c->code_status == 'publish' && $code==strtolower($c->code_value)) || ($c->achievement_status == 'publish' && $c->achievement_code==$code)){
			if($c->achieved_player == $current_user->ID || $c->redeemed_player_id == $current_user->ID ){
				$error['achiever']= __('You already earned this achievement',"bluerabbit");
			}elseif(empty($error)){
				if($code == strtolower($c->code_value)){
					// Redeem the code if it comes from a unique code!
					$redeem = "UPDATE {$wpdb->prefix}br_achievement_codes SET `code_status`=%s, `code_redeemed`=%s, `code_modified`=%s, `player_id`=%d WHERE `code_id`=%d";
					$earn = $wpdb->query( $wpdb->prepare("$redeem ", 'redeem', $today, $today, $current_user->ID, $c->code_id));
					$wpdb->flush; 
				}
				// Assign achievement to player
				$sql = "INSERT INTO {$wpdb->prefix}br_player_achievement (achievement_id, player_id, adventure_id, achievement_applied) VALUES (%d,%d,%d, %s)";
				$sql = $wpdb->prepare ($sql,$c->achievement_id, $current_user->ID, $adventure->adventure_id, $today);
				$wpdb->query($sql);
				$wpdb->flush; 
			}
		}else{
			$error['cancel']= __('Wrong Code',"bluerabbit");
		}
		
		if(empty($error)){
			?>	
			<div class="layer background fixed sq-full top left blue-grey-bg-800 blend-overlay opacity-80" style="background-image: url('<?= $c->achievement_badge; ?>');"></div>
			<div class="layer background fixed sq-full top left opacity-80" style="background-image: url(<?= get_bloginfo('template_directory')."/images/explosion-lq.gif"; ?>);"></div>
			<div class="relative layer base boxed text-center min-w-300 max-w-900 padding-20">
				<h5 class="font text-center condensed uppercase w900 _14 padding-10 white-color"><?php _e("You earned the achievement","bluerabbit"); ?>:</h5>
				<h1 class="text-center font _48 w900 condensed padding-10 white-color">
					<?= $c->achievement_name; ?>
				</h1>
				<?php $number = rand(1,9); ?>
				<audio id="audio-funky">
					<source src="<?= get_bloginfo('template_directory')."/audio/funk$number.mp3"; ?>" type="audio/mpeg">
				</audio>
				<div class="relative">
					<div class="background black-bg opacity-80"></div>
					<div class="foreground padding-10 white-color">
						<?= apply_filters('the_content',$c->achievement_content); ?>
					</div>
				</div>
				<br>
				<a href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
					<span class="icon icon-home"></span>
					<?php _e("Back to home",'bluerabbit'); ?>
				</a>
			</div>
			<script> $(document).ready(function(){ $("#audio-funky").get(0).play(); }); </script>
		<?php }else{ ?>

		<div class="layer background fixed top left sq-full red-bg-800 blend-overlay opacity-40" style="background-image: url('<?= $c->achievement_badge; ?>');"></div>
		<div class="relative layer base boxed text-center min-w-300  max-w-900  padding-20">
			<?php foreach($error as $key=>$err){ ?>
				<h5 class="font text-center uppercase w900  _18 white-color red-bg-400 padding-10">
					<span class="icon icon-<?= $key; ?> icon-lg"></span><br><?=$err; ?>
				</h5>
			<?php } ?>
			<div class="content text-center">
				<a href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
					<span class="icon icon-home"></span>
					<?php _e("Back to home",'bluerabbit'); ?>
				</a>
			</div>
		</div>
		<?php } ?>
	<?php }else{ ?>
		<div class="layer background top left fixed sq-full amber-bg-800 blend-overlay opacity-40" style="background-image: url('<?= get_bloginfo('template_directory')."/images/ghost.png"; ?>');"></div>
		<div class="relative layer base boxed text-center min-w-300  max-w-900 padding-20">
			<h5 class="font text-center uppercase w900  _18 white-color amber-bg-400 padding-10">
				<span class="icon icon-trash icon-lg"></span><br><?php _e("Code doesn't exist!","bluerabbit"); ?>
			</h5>
			<h1 class="text-center font _48 w300 white-color padding-10">
				<?= __("Check the code and try again",'bluerabbit'); ?>
			</h1>
			<div class="content text-center">
				<a href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui blue-bg-700 big">
					<span class="icon icon-home"></span>
					<?php _e("Back to home",'bluerabbit'); ?>
				</a>
			</div>
		</div>
	<?php }  ?>
<?php }else{ ?>
	<script> document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

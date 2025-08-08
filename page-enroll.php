<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$nonce=wp_create_nonce('blue_rabbit_enrollment_nonce'); 
	$adventure_code = isset($_GET['enroll_code']) ? $_GET['enroll_code'] : NULL;
	$e = array();
	$e['success'] = false;
	if(isset($adventure_code)){
		if(wp_verify_nonce($nonce, 'blue_rabbit_enrollment_nonce')){
			$adventure = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_code='$adventure_code'");
			$adventure_max = getSetting('max_players', $adventure->adventure_id);
			$enrolled_players = $wpdb->get_results("SELECT * fROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure->adventure_id AND player_adventure_status='in' AND player_id!=$adventure->adventure_owner");
			if(isset($adventure)){
				if($adventure->adventure_type == 'normal' || $current_user->roles[0]=='administrator' ){
					$e['adventure'] = $adventure;
					if($adventure->adventure_owner != $current_user->ID){
						if(count($enrolled_players) < $adventure_max || $adventure_max<=0){
							$p = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID AND player_adventure_status='in'");
							if($p){
								$e['message'] .='<h4 class="font _16 w300">'.__("You already are enrolled in",'bluerabbit').'</h4>';
								$e['message'] .='<h1 class="font _48 w900">'.$adventure->adventure_title.'</h1>';
								$e['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id";
								$e['success'] = true;
							}else{
								$sql = "INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id,player_id) VALUES (%d,%d) ON DUPLICATE KEY UPDATE player_adventure_status='in'";
								$sql = $wpdb->prepare ($sql,$adventure->adventure_id,$current_user->ID);
								$wpdb->query($sql);
								$wpdb->flush;
								$e['success'] = true;
								$e['message'] .='<h1 class="font _48 w300">'.__("Welcome!",'bluerabbit').'</h1>';
								$e['message'] .='<h4 class="font _16 w300">'.__("you are now enrolled in",'bluerabbit').'</h4>';
								$e['message'] .='<h2 class="font _30 w900">'.$adventure->adventure_title.'</h2>';
								$e['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id";
							}
						}else{
							$e['message'] .='<span class="icon icon-cancel icon-xxl"></span>';
							$e['message'] .='<h1 class="font _48 w900">'.__('Adventure Full','bluerabbit').'</h1>';
							$e['message'] .='<h4 class="font _24 w300">'.__("This adventure doesn't have any more space. The Game Master must remove some players before you can join.",'bluerabbit').'.</h4>';
							$e['location'] = get_bloginfo('url');
						}
					}else{
						$e['location'] = get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id";
						$e['message'] ='<h1 class="font _48 w300">'.__("Welcome back! You are the owner of this adventure",'bluerabbit').'</h1>';
						$e['success'] = true;
					}
				}else{
					$e['message'] ='<h1>'.__('This adventure is a template','bluerabbit').'</h1>';
					$e['message'] .='<h4 class="font _24 w300">'.__("Players can't enroll in template adventures",'bluerabbit').'.</h4>';
				}
			}else{
				$e['message'] .='<span class="icon icon-cancel icon-xxl"></span>';
				$e['message'] .='<h1 class="font _48 w900">'.__('Wrong Code','bluerabbit').'</h1>';
				$e['message'] .='<h4 class="font _24 w300">'.__("Adventure doesn't exist",'bluerabbit').'.</h4>';
			}

		}else{
			$e['message'] ='<h1>'.__('Unauthorized access','bluerabbit').'</h1>';
		}
	}else{
		$e['message'] ='<h1>'.__('No code provided','bluerabbit').'</h1>';
	}

?>

<?php if($e['success']== true){ ?>

	<div class="layer background fixed sq-full top left blue-grey-bg-800 blend-overlay opacity-80" style="background-image: url('<?php echo $e['adventure']->adventure_badge; ?>');"></div>
	<div class="layer background fixed sq-full top left opacity-60 <?php echo $e['adventure']->adventure_color; ?>-bg-400"></div>
	<div class="layer base fixed perfect-center text-center white-color">
		<?php echo $e['message']; ?>
		<br>
		<a href="<?php echo $e['location']; ?>" class="form-ui blue-bg-700 font _30">
			<span class="icon icon-adventure"></span>
			<?php _e("Start the journey!",'bluerabbit'); ?>
		</a>
		<audio id="audio-funky">
			<source src="<?php echo get_bloginfo('template_directory'); ?>/audio/funk1.mp3" type="audio/mpeg">
		</audio>
		<script>
			$(document).ready(function() {
				$("#audio-funky").get(0).play();
			});
		</script>
	</div>

<?php }else{ ?>
	<div class="layer background fixed sq-full top left orange-bg-400 mix-blend-overlay"></div>
	<div class="layer background fixed sq-full top left orange-gradient-900 mix-blend-overlay"></div>
	<div class="layer background fixed sq-full top left grey-gradient-900 opacity-70"></div>
	<?php if(isset($e['adventure'])){ ?>
		<div class="layer background fixed sq-full top left opacity-60 <?php echo $e['adventure']->adventure_color; ?>-bg-400"></div>
	<?php }else{ ?>
		<div class="layer background fixed sq-full top left opacity-60 red-bg-400"></div>
	<?php } ?>
	<div class="layer base fixed perfect-center text-center">
		<?php echo $e['message']; ?>
		<br>
		<a href="<?php echo get_bloginfo('url'); ?>" class="form-ui blue-bg-700 font _30">
			<span class="icon icon-home"></span>
			<?php _e("Back to home",'bluerabbit'); ?>
		</a>
	</div>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

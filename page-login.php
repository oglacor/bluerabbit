<?php if(is_user_logged_in()) { 
	header("Location: ".get_bloginfo('url').'/adventures'); 
} ?>

<?php include (TEMPLATEPATH . '/logout-header.php'); ?>

<?php if($config['use_access_code']['value']){?>
	<div class="access-code">
		<div class="background black-bg"></div>
		<div class="table foreground" id="timer-box">
			<div class="table-cell text-center">
				<div class="boxed max-w-340">
					<h2 id="access-code-timer" class="font special uppercase red-A400 _100 text-left"><?= $config['access_code_time_limit']['value']*100; ?></h2>
					<h1 class="font special uppercase green-A400"><?= __("Insert Access Code","bluerabbit");?></h1>
					<input type="text" id="access-code" class="form-ui font _36 text-center special grey-bg-900 green-500">
					<button class="form-ui green-bg-A400 grey-900 margin-10 font _24 w900" onClick="confirmAccess();" id="confirm-access">
						<?= __("Confirm","bluerabbit");?>
					</button>
					<button class="form-ui grey-bg-400 grey-900 hidden margin-10 font _24 w900" disabled onClick="restartTimer();" id="restart-button">
						<?= __("Restart timer","bluerabbit");?>
					</button>
				</div>
			</div>
		</div>
		<video id="video-access-code" class="foreground" width="100%" height="100%">
			<source src="<?= $config['access_code_video']['value']; ?>">
		</video>
	</div>
<script>
	function confirmAccess(){
		var code = $('#access-code').val();
		if(code != '<?= $config['access_code_value']['value']; ?>'){
			jQuery.ajax({  
				url: runAJAX.ajaxurl,
				data: ({action: 'checkPlayerSecretCode', code:code}),
				method: "POST",
				success: function(data_received) {
					var data = JSON.parse(data_received);
					if(data.success){
						delete accessCodeCountdown();
						$('.access-code').fadeOut('fast');
					}else{
						var time_left = $("#access-code-timer").html();
						if(time_left > 500){
							time_left-= 500;
						}else{
							time_left = 0;
						}
						$("#access-code-timer").html(time_left);
					}
				},
			});
		}else{
			delete accessCodeCountdown();
			$('.access-code').fadeOut('fast');
		}
	}
	function accessCodeCountdown() {
		var time_left = $("#access-code-timer").html();
		if(time_left > 0){
			time_left--;
			$("#access-code-timer").html(time_left);
			setTimeout(accessCodeCountdown, 10);
		}else{
			$('#access-code').attr('disabled',true).addClass('opacity-30');
			$('#restart-button').attr('disabled',false).removeClass('hidden');
			$('#confirm-access').attr('disabled',true).addClass('hidden');
		}
	}
	function restartTimer(){
		$("#timer-box").hide();
		var elem = document.getElementById("video-access-code");
		$("#video-access-code").get(0).play();
		if (elem.requestFullscreen) {
			elem.requestFullscreen();
		} else if (elem.mozRequestFullScreen) { /* Firefox */
			elem.mozRequestFullScreen();
		} else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
			elem.webkitRequestFullscreen();
		} else if (elem.msRequestFullscreen) { /* IE/Edge */
			elem.msRequestFullscreen();
		}
	}
	accessCodeCountdown();
</script>
<?php } ?>

<div class="w-400 h-600 layer foreground perfect-center border rounded-5 absolute overflow-hidden " id="the-login-form">
	<div class="layer background white-bg opacity-10 sq-full"></div>
	<div class="w-full padding-20 text-center layer base relative">
		<img src="<?= $config['login_logo']['value'] ? $config['login_logo']['value'] : get_bloginfo('template_directory')."/images/logo-white-letters.png" ; ?>" class="max-w-350 max-h-100 inline-block">
	</div>
	<div class="flippable w-full layer base" id="flip-box">
	<div class="front">
		<?php if(isset($_GET['login']) && ($_GET['login'])=='failed'){ ?>
		<div class="layer relative w-full padding-5 font _16 w900 uppercase amber-400 text-center">
			<span class="icon icon-warning"></span> <?= __("Wrong Login Credentials","bluerabbit"); ?>
		</div>
		<?php } ?>
		<div class="layer relative w-full h-250">
			<div class="padding-20 layer base v-center boxed max-w-400 absolute w-full" id="login-form">
				<?php
					if(isset($_GET['enroll_code'])){
						$redirect = get_bloginfo('url')."/enroll/?enroll_code=".$_GET['enroll_code'];
					}else{
						$redirect = get_bloginfo('url')."/adventures";
					}
					$args = array(
						'remember'       => true,
						'redirect'       => $redirect,
						'id_submit'      => 'wp-submit',
						'label_username' => __( 'Nickname',"bluerabbit" ),
						'label_password' => __( 'Password',"bluerabbit"  ),
						'label_log_in'   => __( 'Log In',"bluerabbit"),
						'value_username' => '',
						'value_remember' => true
					);
					wp_login_form($args);
				?>
			</div>
		</div>
		<div class="layer base boxed relative w-full padding-20 max-w-200 border border-bottom white-border border-1 text-center">
			<a href="<?php bloginfo('wpurl'); ?>/wp-login.php?action=lostpassword" class="white-color opacity-50"><strong><?php _e("Forgot password?","bluerabbit"); ?></strong></a>
		</div>
		<?php if($config['registration']['value'] > 0){?>
			<div class="layer base relative w-full padding-30 text-center">
				<span onClick="activate('#flip-box');" class="cursor-pointer white-color">
					<?= __("Don't have an account?","bluerabbit"); ?> <strong class="purple-A400"><?php _e("Sign Up!","bluerabbit"); ?></strong>
				</span>
			</div>
		<?php } ?>
	</div>
	<?php if($config['registration']['value'] > 0){?>
		<div class="back">
			<div class="layer background opacity-0 absolute sq-full" onClick=""></div>
			<div class="layer base relative padding-20">
				<div class="text-center" id="registration-form">
					<p class="form-group w-full">
						<label class="">
							<?php _e("Nickname",'bluerabbit'); ?>
						</label>
						<input class="form-ui" id="new_user_nickname" type="text" autocomplete="fdfgdfsgs">
					</p>
					<p class="form-group w-full">
						<label class=""><?php _e('Email','bluerabbit'); ?></label>
						<input class="form-ui" id="new_user_email" type="email" autocomplete="dsfgfdsgdfs">
					</p>
					<p class="form-group w-full">
						<label class=""><?php _e('Password','bluerabbit'); ?></label>
						<input class="form-ui" id="new_password" type="password" autocomplete="new-password" maxlength="50">
					</p>
					<p class="form-group w-full">
						<label class=""><?php _e('Language','bluerabbit'); ?></label>
						<select class="form-ui" id="new_the_lang">
							<?php $langs = array(
									array("en_US","U.S. English"),
									array("es_MX","Espa&ntilde;ol"),
								);
							   $default_lang = $config['default_language']['value'];
							?>
							<?php foreach($langs as $l){ ?>
								<option value="<?php echo $l[0];?>" <?php if($default_lang == $l[0]){ echo 'selected'; } ?>><?php echo $l[1];?></option>
							<?php } ?>
						</select>
					</p>
					<input type="hidden" name="the_redirect" id="the_redirect" value="<?= $redirect; ?>"/>
					<input type="hidden" name="register_nonce" id="register_nonce" value="<?php echo wp_create_nonce('br_register_nonce'); ?>"/>
					<div class="text-center white-color">
						<button onClick="registerNewPlayer();" class="register-submit"><span class="icon icon-carrot"></span> <?php _e("Register New Player","bluerabbit"); ?></button>
						<p class="font _12 padding-5">
							<a class="yellow-400" target="_blank" href="http://www.bluerabbit.io/privacy">
								<?php _e("By registering you agree upon our privacy policy","bluerabbit"); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
			<div class="layer base relative w-full padding-10 text-center">
				<span onClick="activate('#flip-box');" class="cursor-pointer white-color">
					<?= __("Already registered?","bluerabbit"); ?> <strong class="purple-A400"><?php _e("Login!","bluerabbit"); ?></strong>
				</span>
			</div>
		</div>
	<?php } ?>
		<br clear="all">
	</div>
</div>
<?php include (TEMPLATEPATH . '/logout-footer.php'); ?>
<?php if(is_user_logged_in()) {
	header("Location: ".get_bloginfo('url').'/adventures');
} ?>

<?php include (TEMPLATEPATH . '/logout-header.php'); ?>

<?php
	if(isset($_GET['enroll_code'])){
		$redirect = get_bloginfo('url')."/enroll/?enroll_code=".$_GET['enroll_code'];
	}else{
		$redirect = get_bloginfo('url')."/adventures";
	}
	$show_reg = $config['registration']['value'] > 0;
?>

<div class="br-auth-wrap">

	<img src="<?= $config['login_logo']['value'] ? $config['login_logo']['value'] : get_bloginfo('template_directory')."/images/logo-full-for-dark-bg.png"; ?>" class="br-auth-logo" alt="">

	<div class="br-auth-card">
		<div class="br-panel">

			<?php if ($show_reg) { ?>
			<div class="br-auth-tabs">
				<button type="button" class="br-btn br-btn-active" id="auth-tab-login" onClick="brAuthShow('login');">
					<span class="icon icon-players"></span> <?= __("Log In", "bluerabbit"); ?>
				</button>
				<button type="button" class="br-btn" id="auth-tab-register" onClick="brAuthShow('register');">
					<span class="icon icon-carrot"></span> <?= __("Sign Up", "bluerabbit"); ?>
				</button>
			</div>
			<?php } ?>

			<?php if (isset($_GET['login']) && ($_GET['login']) == 'failed') { ?>
			<div class="br-auth-error">
				<span class="icon icon-warning"></span> <?= __("Wrong Login Credentials", "bluerabbit"); ?>
			</div>
			<?php } ?>

			<!-- ======================== LOGIN ======================== -->
			<div class="br-auth-panel active" id="auth-panel-login">
				<?php
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
				<div class="br-auth-links">
					<a href="<?php bloginfo('wpurl'); ?>/wp-login.php?action=lostpassword"><strong><?= __("Reset password", "bluerabbit"); ?></strong></a>
				</div>
				<?php if ($show_reg) { ?>
				<div class="br-auth-links">
					<span onClick="brAuthShow('register');" style="cursor:pointer">
						<?= __("Don't have an account?", "bluerabbit"); ?> <strong><?= __("Sign Up!", "bluerabbit"); ?></strong>
					</span>
				</div>
				<?php } ?>
			</div>

			<!-- ======================== REGISTER ======================== -->
			<?php if ($show_reg) { ?>
			<div class="br-auth-panel" id="auth-panel-register">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Nickname", 'bluerabbit'); ?></label>
					<input class="br-input" id="new_user_nickname" type="text" autocomplete="off">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __('Email', 'bluerabbit'); ?></label>
					<input class="br-input" id="new_user_email" type="email" autocomplete="off">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __('Password', 'bluerabbit'); ?></label>
					<input class="br-input" id="new_password" type="password" autocomplete="new-password" maxlength="50">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __('Language', 'bluerabbit'); ?></label>
					<select class="br-input" id="new_the_lang">
						<?php $langs = array(
								array("en_US", "U.S. English"),
								array("es_MX", "Espa&ntilde;ol"),
							);
						   $default_lang = $config['default_language']['value'];
						?>
						<?php foreach ($langs as $l) { ?>
							<option value="<?= $l[0]; ?>" <?php if ($default_lang == $l[0]) { echo 'selected'; } ?>><?= $l[1]; ?></option>
						<?php } ?>
					</select>
				</div>
				<input type="hidden" name="the_redirect" id="the_redirect" value="<?= $redirect; ?>"/>
				<input type="hidden" name="register_nonce" id="register_nonce" value="<?= wp_create_nonce('br_register_nonce'); ?>"/>

				<button onClick="registerNewPlayer();" class="br-btn br-btn-green br-btn-block"><span class="icon icon-carrot"></span> <?= __("Register New Player", "bluerabbit"); ?></button>
				<p class="br-form-hint" style="text-align:center;margin-top:12px">
					<a target="_blank" href="http://www.bluerabbit.io/privacy"><?= __("By registering you agree upon our privacy policy", "bluerabbit"); ?></a>
				</p>

				<div class="br-auth-links">
					<span onClick="brAuthShow('login');" style="cursor:pointer">
						<?= __("Already registered?", "bluerabbit"); ?> <strong><?= __("Login!", "bluerabbit"); ?></strong>
					</span>
				</div>
			</div>
			<?php } ?>

		</div>
	</div>
</div>

<script>
function brAuthShow(which) {
	$('.br-auth-panel').removeClass('active');
	$('#auth-panel-' + which).addClass('active');
	$('.br-auth-tabs .br-btn').removeClass('br-btn-active');
	$('#auth-tab-' + which).addClass('br-btn-active');
}
</script>

<?php include (TEMPLATEPATH . '/logout-footer.php'); ?>

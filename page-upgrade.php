<?php include (get_stylesheet_directory() . '/header.php'); ?>

<div class="br-page">

	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(28,194,235,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(28,194,235,0.4)">
			<span class="icon icon-player" style="font-size:28px;color:#1cc2eb"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= __("Upgrade Account", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= __("Upgrade your account to unlock all features!", "bluerabbit"); ?></span>
		</div>
	</div>

	<div class="br-panel" style="text-align:center">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="margin-bottom:16px">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="UBE87WD5GW32A">
			<input type="hidden" name="custom" value="<?= $current_user->ID; ?>">
			<input type="image" src="https://app.bluerabbit.io/br/wp-content/uploads/2019/07/paypal-monthly.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="margin-bottom:20px">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="9KDMHHEUXYCSW">
			<input type="image" src="https://app.bluerabbit.io/br/wp-content/uploads/2019/07/paypal-yearly.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<input type="hidden" name="custom" value="<?= $current_user->ID; ?>">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<a class="br-btn br-btn-blue" href="<?= get_bloginfo('url'); ?>/my-account"><?= __("Back to my account", "bluerabbit"); ?></a>
	</div>

</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

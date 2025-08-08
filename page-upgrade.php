<?php include (get_stylesheet_directory() . '/header.php'); ?>
<div class="container boxed max-w-1200">
		<div class="card">
			<div class="w-full h-250 relative  fluid" style="background-image: url(<?php echo get_bloginfo('template_directory')."/images/bg-default.jpg"; ?>);">
				<div class="spacer fixed-125">
				</div>
				<div class="spacer fluid">
					<div class="background blue-bg-700 opacity-80"></div>
					<div class="foreground">
						<div class="head-floater upper-left">
							<span class="icon-button font _24 sq-40  main-icon blue-bg-700">
								<span class="icon icon-player white-color"></span>
							</span>
						</div>
						<div class="table">
							<div class="table-cell bottom">
								<h1 class="font _48 white-color"><?php echo __("Upgrade Account","bluerabbit"); ?></h1>
								<h3 class="blue-grey-100 font _14 w300">									
									<?php echo __("Upgrade your account to unlock all features!","bluerabbit"); ?>
								</h3>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="body-ui white-bg">
				<div class="content">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="UBE87WD5GW32A">
						<input type="hidden" name="custom" value="<?php echo $current_user->ID; ?>">
						<input type="image" src="https://app.bluerabbit.io/br/wp-content/uploads/2019/07/paypal-monthly.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="9KDMHHEUXYCSW">
						<input type="image" src="https://app.bluerabbit.io/br/wp-content/uploads/2019/07/paypal-yearly.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<input type="hidden" name="custom" value="<?php echo $current_user->ID; ?>">
						<img alt=""  border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">

					</form>
					<a class="form-ui blue-bg-700 white-color font _18" href="<?php bloginfo('url'); ?>/my-account"><?php _e("Back to my account","bluerabbit"); ?></a>
				</div>
			</div>
		</div>
	</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

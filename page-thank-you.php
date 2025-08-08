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
					<h1 class="text-center">Thankyou for playing!</h1>
					<p class="text-center">
						Thankyou for subscribing to BLUErabbit. If you need any help, contact us at help@bluerabbit.io
					</p>
					<table width="90%" class="table">
						<tbody>
							<tr>
								<td><center><img src="http://app.bluerabbit.io/email-images/thankyou.png" alt=""/></center></td>
							</tr>
							<tr>
								<td><center><h4>Thanks for choosing BLUErabbit! Below you will find your receipt (you should also get this on your email). Any comments please let us know at support@bluerabbit.io</h4></center></td>
							</tr>
						</tbody>
					</table>
					<table width="90%">
						<tbody>
							<tr>
								<td>Name</td>
								<td><?php echo $_POST['first_name'].' '.$_POST['last_name'];?></td>
							</tr>
							<tr>
								<td>Email</td>
								<td><?php echo $_POST['payer_email'];?></td>
							</tr>
							<tr>
								<td>Subscription Date</td>
								<td><?php echo $_POST['subscr_date'];?></td>
							</tr>
							<tr>
								<td>Subscription Type</td>
								<td><?php echo $_POST['item_name'].' '.$_POST['item_number'];?></td>
							</tr>
						</tbody>
					</table>
					<center>
						<h3>Please contact us for any questions or comments</h3>
					</center>
					<a class="form-ui blue-bg-700 white-color font _18" href="<?php bloginfo('url'); ?>/my-account"><?php _e("Back to my account","bluerabbit"); ?></a>
					<a class="form-ui blue-bg-700 white-color font _18" href="<?php bloginfo('url'); ?>"><?php _e("Back to home","bluerabbit"); ?></a>
				</div>
			</div>
		</div>
	</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

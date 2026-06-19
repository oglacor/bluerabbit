<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($isGM || $isAdmin){ ?>
	<div class="container">
		<div class="body-ui w-full white-bg">
			<?php include(get_template_directory().'/manage-requests.php'); ?>
		</div>
	</div>
<?php }else{ ?>
	<div class="container">
		<div class="body-ui w-full white-bg text-center padding-20">
			<h3 class="font _20 grey-400"><?php _e("You don't have access to this page","bluerabbit"); ?></h3>
		</div>
	</div>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php if(!is_user_logged_in()) {
	include (TEMPLATEPATH . '/logout-header.php'); 
}else{
	include (TEMPLATEPATH . '/header.php'); 
} ?>

<div class="background fixed" style="background-image:url(<?php bloginfo('template_directory'); ?>/images/404.png);"></div>
<div class="background fixed black-bg opacity-70"></div>
<div class="table background fixed text-center">
	<div class="table-cell">
		<h1 class="font _60 w900 white-color kerning-3 condensed"><?= __("This page doesn't exist","bluerabbit"); ?></h1>
		<a class="form-ui green-bg-500" href="<?php bloginfo('url'); ?>"><?php _e("back to home","bluerabbit"); ?></a>
	</div>
</div>
<?php if(!is_user_logged_in()) {
	include (TEMPLATEPATH . '/logout-footer.php'); 
}else{
	include (TEMPLATEPATH . '/footer.php'); 
} ?>

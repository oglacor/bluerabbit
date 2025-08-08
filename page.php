<?php if(!is_user_logged_in()) {
	include (TEMPLATEPATH . '/logout-header.php'); 
}else{
	include (TEMPLATEPATH . '/header.php'); 
} ?>
<div class="background black-bg opacity-80"></div>
<div class="container boxed max-w-1200 foreground relative white-color">
	<?php if(have_posts()){?>
		<?php while(have_posts()){ the_post();?> 
			<div class="w-full h-250 relative  fluid" style="background-image: url(<?php echo $header_image; ?>);">
				<div class="spacer fluid padding-20">
					<div class="background blue-bg-700 opacity-80"></div>
					<div class="foreground text-center">
						<h1 class="font _48 white-color w900"><?php the_title(); ?></h1>
					</div>
				</div>
			</div>
			<div class="body-ui">
				<div class="content">
					<?php the_content();?>
				</div>
			</div>
		<?php }; ?>
	<?php }else{?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php }?>
</div>
<?php if(!is_user_logged_in()) {
	include (TEMPLATEPATH . '/logout-footer.php'); 
}else{
	include (TEMPLATEPATH . '/footer.php'); 
} ?>

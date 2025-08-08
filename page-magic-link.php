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
	$c = magicCode($code,$adv_id);
	$error = $c['errors'];
		if(empty($error)){
			?>	
			<div class="layer background fixed sq-full top left blue-grey-bg-800 blend-overlay opacity-80" style="background-image: url('<?= $c['c']->achievement_badge; ?>');"></div>
			<div class="layer background fixed sq-full top left opacity-80" style="background-image: url(<?= get_bloginfo('template_directory')."/images/explosion-lq.gif"; ?>);"></div>
			<div class="relative layer base boxed text-center min-w-300 max-w-900 padding-20">
				<h5 class="font text-center condensed uppercase w900 _14 padding-10 white-color"><?php _e("You earned the achievement","bluerabbit"); ?>:</h5>
				<h1 class="text-center font _48 w900 condensed padding-10 white-color">
					<?= $c['c']->achievement_name; ?>
				</h1>
				<?php $number = rand(1,9); ?>
				<audio id="audio-funky">
					<source src="<?= get_bloginfo('template_directory')."/audio/funk$number.mp3"; ?>" type="audio/mpeg">
				</audio>
				<div class="relative">
					<div class="background black-bg opacity-80"></div>
					<div class="foreground padding-10 white-color">
						<?= apply_filters('the_content',$c['c']->achievement_content); ?>
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
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

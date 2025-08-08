<!doctype html>
<?php $config = getSysConfig(); ?>
<html>
	<head>
		<meta charset="utf-8">
		<title>
			<?php bloginfo('name');?>, <?php bloginfo('description'); ?>
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<link rel="icon" type="image/gif" href="<?php bloginfo('template_directory'); ?>/images/favicon.png">
		<link rel="alternate" href="https://bluerabbit.io" hreflang="en" />

		<link rel="stylesheet" href="https://use.typekit.net/zfu4fjz.css">
		<link rel="stylesheet" href="https://bluerabbit.io/fonts/font.css">
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>">
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory');?>/css/style-framework.css">

		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/script.js"></script>
		
		<?php $gads_id = $config['google_property_id']['value'] ? $config['google_property_id']['value'] : 'G-F1QPQC2JZL' ;	?>
		
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $gads_id ; ?>"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', '<?= $gads_id ; ?>');
		</script>		
		
		<?php wp_head(); ?>
	</head>
	<div class="background black-bg fixed fixed-bg repeat-bg"  style="background-image: url(<?= $config['login_bg']['value'] ? $config['login_bg']['value'] : $config['default_bg']['value']; ?>)"></div>
	<body <?php if(is_page('login')){ ?>class="login" <?php } ?>>


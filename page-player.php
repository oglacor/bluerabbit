<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

if($_GET['player_id']){
	$player = getPlayerData($_GET['player_id']); 
}else{
?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<div class="theme blue"> 
	<div class="container boxed max-w-1200 ">
		<div class="header theme-border">
			<h1 class="theme-color">
				<span class="icon icon-hexad"></span>
				<?php echo __('Player Type','bluerabbit').": <strong>$player->player_display_name</strong>"; ?>
			</h1>
		</div>
	</div>
	<div class="container boxed max-w-1200 ">
		<div class="row">
			<div class="content">
				<div class="row">
					<div class="col-6 hexad-results <?php echo $player->player_hexad_slug; ?>">
						<div class="player-type-headline ">
							<div class="player-type" title="<?php echo $player->player_hexad; ?>">
								<span class="icon icon-hexagon type-<?php echo $player->player_hexad_slug; ?>"></span>
								<span class="icon icon-icon icon-<?php echo $player->player_hexad_slug; ?>"></span>
							</div>
							<div class="headline-square">
								<h6><?php _e("Dominant","bluerabbit"); ?>: </h6>
								<h1><?php echo $player->player_hexad; ?></h1>
							</div>
						</div>
						<div class="player-type-description">
							<?php include (TEMPLATEPATH . "/$player->player_hexad_slug.php"); ?>
							<a class="form-ui pull-left" href="https://www.gamified.uk/user-types/" target="_blank">
								<?php _e("Read all about Marczewski player types","bluerabbit"); ?>
							</a>
							<a class="form-ui pull-right" href="https://www.amazon.com/Even-Ninja-Monkeys-Like-Play/dp/1514745666/ref=sr_1_1?ie=UTF8&qid=1532534766&sr=8-1&keywords=even+ninja+monkeys+like+to+play" target="_blank">
								<span class="icon icon-hexad"></span><?php _e('Get a copy of “Even Ninja Monkeys like to Play”',"bluerabbit"); ?>
							</a>
						</div>
					</div>
					<div class="col-6 hexad-graph">
						<div class="row">
							<div class="col-6 hexad-date">
								<?php if($player->hexad_date){ ?>
									<h6><?php _e("Tested on","bluerabbit"); ?>:</h6>
									<h4><strong><?php echo date('M jS, Y', strtotime($player->hexad_date)); ?></strong></h4>
								<?php }else{ ?>
									<h6><?php _e("No test available","bluerabbit"); ?>:</h6>
								<?php } ?>
							</div>
							<div class="col-6 hexad-test-again text-right">
								<?php if($player->hexad_date){ ?>
									<a href="<?php echo get_bloginfo('url')."/new-hexad"; ?>" class="form-ui green"><?php _e("test again","bluerabbit"); ?> <span class="icon icon-hexad"></span></a>
								<?php }else{ ?>
									<a href="<?php echo get_bloginfo('url')."/new-hexad"; ?>" class="form-ui green"><?php _e("test now","bluerabbit"); ?> <span class="icon icon-hexad"></span></a>
								<?php } ?>
							</div>
						</div>
						
						<?php if($player->hexad_answers){ ?>
							<?php $hexad = unserialize($player->hexad_answers); ?>
							<?php 
								$intrinsic = array($hexad["type_f"],$hexad["type_s"],$hexad["type_ph"],$hexad["type_a"]);
								$ptMax = max($intrinsic);
								if($ptMax==$hexad["type_f"] ){
									$ptMaxSlug = "freespirit";
								}elseif($ptMax==$hexad["type_a"] ){
									$ptMaxSlug = "achiever";
								}elseif($ptMax==$hexad["type_ph"] ){
									$ptMaxSlug = "philanthropist";
								}elseif($ptMax==$hexad["type_s"] ){
									$ptMaxSlug = "socialiser";
								}
							?>

							<input type="hidden" id="pt-hexad-highest" value="<?php echo $ptMaxSlug; ?>">
							<div class="pt-chart">
								<canvas id="pt-graph-canvas" width="300" height="300"></canvas>
							</div>
							<script>
									createHexadChart(<?php echo $hexad["type_d"]; ?>,<?php echo $hexad["type_f"]; ?>,<?php echo $hexad["type_a"]; ?>,<?php echo $hexad["type_p"]; ?>,<?php echo $hexad["type_s"]; ?>,<?php echo $hexad["type_ph"]; ?>,"pt-graph-canvas");
							</script>
						<?php }else{ ?>
							<h2 class="text-center"><strong><?php _e('No player type detected','bluerabbit'); ?></strong></h2>
						<?php } ?>
						<div class="divider thin"></div>
						<div class="credits">
							<div class="APA-ref">
	<div id="copy-target-99791202" class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied">Marczewski, A. (2015). <a href="http://gamified.uk/user-types/" rel="nofollow" target="_blank">User Types</a>. In <em><a href="http://www.gamified.uk/even-ninja-monkeys-like-to-play/">Even Ninja Monkeys Like to Play</a>: Gamification, Game Thinking and Motivational Design</em> (1st ed., pp. 65-80). CreateSpace Independent Publishing Platform.</div><div class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied"><ul><li><strong>ISBN-10:</strong> 1514745666</li><li><strong>ISBN-13:</strong> 978-1514745663</li></ul><p><a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license"><img style="border-width: 0;" src="//i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" alt="Creative Commons Licence"/></a> Gamification User Types Hexad by <a href="https://www.gamified.uk/user-types" rel="cc:attributionURL">Andrzej Marczewski</a> is licensed under a <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.</p></div>
							</div>
						</div>
					</div>
				</div>
				<div class="divider long"></div>
			</div>
			<br class="clear">
		</div>
	</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

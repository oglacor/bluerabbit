<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($isGM || $isNPC || $isAdmin){ ?>
<div class="report white-bg" id="bluerabbit-report">
	<div class="report-header">
		<h5 class="report-date pull-left font w300 _18 light-blue c-700">
			<?php echo date('F dS, Y',strtotime($adventure->adventure_start_date)); ?>
		</h5>
		<div class="report-logo"> <img width="40%" src="<?php echo $adventure->adventure_badge; ?>"> </div>
	</div>
	<div class="report-footer">
		<h5 class="report-br-url">bluerabbit.io</h5>
		<div class="report-br-logo"> <img src="<?php echo get_bloginfo('template_directory')."/images/logo.png "; ?>"> </div>
	</div>
	<div class="report-cover table page-break">
		<div class="table-cell">
			<div class="report-logo"> <img src="<?php echo $adventure->adventure_badge; ?>"> </div>
			<h5 class="report-date">
				<?php echo date('F dS, Y',strtotime($adventure->adventure_start_date)); ?>
			</h5>
			<h1 class="report-title">
				<?php echo $adventure->adventure_title; ?>
			</h1>
		</div>
	</div>

	<?php
	$players = $wpdb->get_results("
	SELECT 
	a.player_id, a.achievement_id, a.player_xp, a.player_bloo, a.player_level, a.player_gpa,
	b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad
	FROM {$wpdb->prefix}br_player_adventure a
	LEFT JOIN {$wpdb->prefix}br_players b
	ON a.player_id=b.player_id
	WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in'
	GROUP BY a.player_id $order
	");
	$quests = $wpdb->get_results( "SELECT *
		FROM {$wpdb->prefix}br_quests
		WHERE adventure_id=$adventure->adventure_id AND quest_type='quest' AND quest_status='publish'
		ORDER BY quest_order, mech_level, quest_id
	" );
	?>

	<script>
		var rating_colors = ['#B0BEC5', '#FFE082', '#FFD54F', '#FFCA28', '#FFC107', '#FFB300'];
		var ratingLabels = [
			"0 <?php _e("Stars","bluerabbit"); ?>",
			"1 <?php _e("Star","bluerabbit"); ?>",
			"2 <?php _e("Stars","bluerabbit"); ?>",
			"3 <?php _e("Stars","bluerabbit"); ?>",
			"4 <?php _e("Stars","bluerabbit"); ?>",
			"5 <?php _e("Stars","bluerabbit"); ?>"
		];
		var posts_colors = ['#B0BEC5','#0288D1'];
		var postsLabels = [
			"<?php _e("Total Players","bluerabbit"); ?>",
			"<?php _e("Player Posts","bluerabbit"); ?>"
		];
	</script>
	<?php foreach ($quests as $key=>$q){ ?>
	<div class="container boxed max-w-1200 page-break">
		<table width="100%">
			<thead>
				<tr>
					<td>
						<div class="header-space">&nbsp;</div>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<div class="report-quest-headline text-right">
							<h4 class="font _18 w900 uppercase light-blue c-400"><?php echo $q->quest_type." ".($key+1); ?></h4>
							<h1 class="report-quest-title font condensed _80 w300 blue c-700">
								<?php echo $q->quest_title; ?>
							</h1>
						</div>
						<div class="report-quest-instructions">
							<?php echo apply_filters('the_content',$q->quest_content); ?>
						</div>
						<div class="report-quest-charts">
							<div class="report-quest-chart">
								<h3 class="chart-title font _18 w900 uppercase amber c-400"><?php _e("Quest Rating","bluerabbit"); ?></h3>
								<canvas id="rating-chart-<?php echo $q->quest_id; ?>" width="250" height="125"></canvas>
								<div class="bg-icon">
									<span class="icon-button font _24 sq-40  amber"> <span class="icon icon-star"></span></span>
								</div>
							</div>
							<div class="report-quest-chart">
								<h3 class="chart-title font _18 w900 uppercase light-blue c-700"><?php _e("Players vs Finished","bluerabbit"); ?></h3>
								<canvas id="posts-chart-<?php echo $q->quest_id; ?>" width="250" height="125"></canvas>
								<div class="bg-icon">
									<span class="icon-button font _24 sq-40  light-blue"> <span class="icon icon-quest"></span></span>
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td>
						<div class="footer-space">&nbsp;</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<div class="container boxed max-w-1200 page-break">
		<div class="row">
			<table width="100%">
				<thead>
					<tr>
						<td>
							<div class="header-space">&nbsp;</div>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php 
					$ratingValues = array(0,0,0,0,0,0);
					$player_posts = $wpdb->get_results("
					SELECT a.*, b.player_display_name FROM {$wpdb->prefix}br_player_posts a
					JOIN {$wpdb->prefix}br_players b
					ON a.player_id = b.player_id
					WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$q->quest_id"); 
					$postsValues = array(count($players), count($player_posts));
					?>
					<?php foreach($player_posts as $pp){ ?>
						<?php 
							if($pp->pp_quest_rating){
								$ratingValues[$pp->pp_quest_rating]+=1; 
							}else{
								$ratingValues[0]+=1; 
							}
						?>
						<tr>
							<td>
								<div class="report-post">
									<div class="report-entry">
										<?php echo apply_filters('the_content',$pp->pp_content); ?>
									</div>
									<h6 class="font w100 blue-grey c-300 author">
										- <?php echo "$pp->player_display_name"; ?>
									</h6>
								</div>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot> 
					<tr>
						<td>
							<div class="footer-space">&nbsp;</div>
						</td>
					</tr>
				</tfoot>
			</table>
			<?php ksort ($ratingValues); ?>
			<script>
				var ratingValues<?php echo $q->quest_id; ?> = [<?php echo implode(',',$ratingValues); ?>];
				var postsValues<?php echo $q->quest_id; ?> = [<?php echo implode(',',$postsValues); ?>];
				createReportChart("#rating-chart-<?php echo $q->quest_id; ?>", ratingValues<?php echo $q->quest_id; ?>,ratingLabels, rating_colors);
				
				createReportChart("#posts-chart-<?php echo $q->quest_id; ?>", postsValues<?php echo $q->quest_id; ?>,postsLabels, posts_colors);
			</script>
		</div>
	</div>
	<?php } ?>
</div>

<script>
	$( '#bluerabbit-report .report-post img' ).removeAttr( "width height" );
	$( '#bluerabbit-report .report-post div' ).removeAttr( "style" );
</script>
<input type="hidden" id="grade_nonce" value="<?php echo wp_create_nonce('br_grade_nonce'); ?>"/>
<?php }else{ ?>
<script>
	document.location.href = "<?php bloginfo('url');?>/404/";
</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
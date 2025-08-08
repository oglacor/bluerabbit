<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php $speakers = $wpdb->get_results("
	SELECT speakers.* FROM {$wpdb->prefix}br_speakers speakers
	WHERE speakers.adventure_id=$adventure->adventure_id
	ORDER BY speakers.speaker_first_name, speakers.speaker_last_name
"); 
$sessions = getSessions($adventure->adventure_id,'publish');
?>
	<div class="container boxed max-w-1200 wrap">
		<div class="w-full h-250 relative  fluid" style="background-image: url(<?php echo $adv_settings['schedule_bg']['value']; ?>);">
			<div class="spacer fluid padding-20">
				<div class="background black-bg opacity-60"></div>
				<div class="foreground">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  icon-lg indigo-bg-300"><span class="icon icon-megaphone"></span></span>
						<span class="icon-content white-color">
							<span class="line font _30 w900 condensed"><?php _e("Meet your speakers","bluerabbit"); ?></span>
						</span>
					</span>
				</div>
			</div>
		</div>
		<div class="body-ui w-full blue-grey-bg-900 padding-20">
			<?php foreach($speakers as $key=>$s){ ?>
				<div class="highlight white-bg padding-10 margin-5">
					<span class="icon-group sticky white-bg padding-5">
						<span class="icon-button font _24 sq-40  icon-lg" style="background-image: url(<?php echo "$s->speaker_picture"; ?>);"></span>
						<span class="icon-content">
							<span class="line font _24 w600 blue-700"><a href="<?php echo get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id"; ?>"><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></a></span>
							<span class="line font _14 w300 grey400"><?php echo "$s->speaker_company"; ?></span>
						</span>
					</span>
					<?php foreach($sessions as $tKey => $t){ ?>
						<?php if($s->speaker_id == $t->speaker_id){ ?>
							<?php $sessionsAvailable = true; ?>
							<span class="highlight-cell margin-5">
								<a href="<?php echo get_bloginfo('url')."/schedule/?adventure_id=$adventure->adventure_id#milestone-session-$t->session_id"; ?>" class="form-ui blue-bg-50 blue-800">
									<?php echo $t->session_title; ?>
								</a>
							</span>
						<?php } ?>
					<?php } ?>
					<?php
						if(!$sessionsAvailable){
							echo "<h4>".__("No session available","bluerabbit")."</h4>";
						}else{
							echo "<br class='clear'>";
						}		
					?>
				</div>
			<?php } ?>
		</div>
	</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

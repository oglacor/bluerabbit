<?php include (get_stylesheet_directory() . '/header.php'); ?>
<div class="background blue-bg-700 sq-full fixed layer"></div>
<div class="background black-bg opacity-80 sq-full fixed layer"></div>
<div class="layer base w-full relative schedule">
<?php 
	if($adventure->adventure_hide_schedule == 'hide'){
		$sessions = getSessions($adventure->adventure_id, 'hide'); 
	}else{
		$sessions = getSessions($adventure->adventure_id, 'publish'); 
	}
	$player_achievements = $wpdb->get_col("SELECT
	achievement_id FROM {$wpdb->prefix}br_player_achievement
	WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID");
	
	$all_achievements = $wpdb->get_results("SELECT * 
	FROM {$wpdb->prefix}br_achievements
	WHERE adventure_id=$adventure->adventure_id AND achievement_status='publish' ORDER BY achievement_id");
	$achievements = array();
	$$achievement_badge = array();
	foreach($all_achievements as $ach){
		$achievements[$ach->achievement_id] = $ach->achievement_color; 
		$achievement_badge[$ach->achievement_id] = $ach->achievement_badge; 
	}
	$all_guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_xp DESC, guild_bloo DESC, guild_name ASC");
	
	$player_guilds = $wpdb->get_col("SELECT
	guild_id FROM {$wpdb->prefix}br_player_guild
	WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID GROUP BY guild_id");
	
	$guilds =[];
	$guild_logos =[];
	foreach($all_guilds as $t){
		$guilds[$t->guild_id] = $t->guild_color; 
		$guild_logos[$t->guild_id] = $t->guild_logo; 
	}
	function current_timezone($adv_tz = 'America/MexicoCity') {
	  $zones_array = array();
	  $timestamp = time();
	  foreach(timezone_identifiers_list() as $key => $zone) {
		date_default_timezone_set($zone);
		  if($zone == $adv_tz){
			  $the_zone = ' GMT ' . date('P', $timestamp)." ".$zone;
		  }
	  }
	  return $the_zone;
	}
	$timestamp = time();
	$the_zone = ' GMT ' . date('P', $timestamp);
	?>

	
			<div class="w-full boxed max-w-1200 layer base relative text-center">
				<?php if($sessions){ ?>
					<?php $current_day = date('Ymd', strtotime($sessions[0]->session_start));?>
					<?php $date_values = explode(",",date('Y,m,d,l,F', strtotime($sessions[0]->session_start))); ?>
					<div class="layer base relative schedule-day">
						<div class="schedule-day-description layer foreground text-center purple-bg-300 schedule-day-date">
							<div class="layer background absolute sq-full blend-luminosity purple-bg-300"></div>
							<div class="layer background absolute sq-full blend-luminosity opacity-70 black-bg"></div>
							<div class="icon-group padding-10 inline-table white-color layer base relative">
								<div class="icon-content text-center top">
									<span class="line font _36 w900"><?= $date_values[2]; ?></span>
								</div>
								<div class="icon-content text-left">
									<span class="line font _10 w300"><?= $date_values[3]; ?></span>
									<span class="line font _20 w300"><?= $date_values[4]; ?></span>
								</div>
							</div>
						</div>
						<div class="schedule-day-sessions">
						<?php foreach($sessions as $key=>$session){ ?>

							<?php $session_day = date('Ymd', strtotime($session->session_start)); ?>

							<?php if($session_day > $current_day){ ?>
								<?php $current_day = $session_day; ?>
								<?php $date_values = explode(",",date('Y,m,d,l,F', strtotime($session->session_start))); ?>
							</div>
						</div>
						<div class="layer base relative schedule-day">
							<div class="schedule-day-description layer foreground text-center purple-bg-300 schedule-day-date">
								<div class="layer background absolute sq-full blend-luminosity purple-bg-300"></div>
								<div class="layer background absolute sq-full blend-luminosity opacity-70 black-bg"></div>
								<div class="icon-group padding-10 inline-table white-color layer base relative">
									<div class="icon-content text-center top">
										<span class="line font _36 w900"><?= $date_values[2]; ?></span>
									</div>
									<div class="icon-content text-left">
										<span class="line font _10 w300"><?= $date_values[3]; ?></span>
										<span class="line font _20 w300"><?= $date_values[4]; ?></span>
									</div>
								</div>
							</div>
							<div class="schedule-day-sessions">

						<?php } ?>
						<?php if((!$session->achievement_id || in_array($session->achievement_id, $player_achievements)) && (!$session->guild_id || in_array($session->guild_id, $player_guilds))){ ?>
						<div class="overflow-hidden relative layer base schedule-session" id="milestone-session-<?= $key; ?>">
							<?php if($isGM){ ?>
								<div class="corner circle small green-bg-400 top right layer foreground absolute">
									<a class="font _14 white-color" href="<?= get_bloginfo('url')."/new-session/?adventure_id=$adventure->adventure_id&session_id=$session->session_id";?>"> <span class="icon icon-edit"></span> </a>
								</div>
							<?php } ?>
							<?php $date_values = explode(",",date('Y,m,d,l,F', strtotime($session->session_start))); ?>
							<div class="foreground highlight white-color padding-5 margin-0" onClick="showOverlay('#session-detail-<?= $key; ?>'); activate('#milestone-session-<?= $key; ?>');">
								<div class=" relative w-full padding-10 flex">
									<div class="w-100 relative layer base">
										<?php $session_image = $session->speaker_picture ? $session->speaker_picture : $adventure->adventure_badge;?>
										<div class="icon-button sq-100 border border-2 border-all blue-grey-border-700 pull-left" style="background-image: url(<?= $session_image;?>);"></div>
									</div>
									<div class="icon-content text-left cursor-pointer padding-10">
										<h2 class="font _30 w600">
											<?= $session->session_title; ?>
										</h2>
										<p class="padding-10 font _16 w600 opacity-60 mix-blend-overlay">
											<strong><?= "$session->speaker_first_name $session->speaker_last_name"; ?></strong>
											<?php if($session->achievement_id){ ?>
												<button class="icon-button font _24 sq-40  border border-all border-1 <?= $achievements[$session->achievement_id];?>-400 white-bg" style="background-image: url(<?= $achievement_badge[$session->achievement_id]; ?>);">
												</button>
											<?php } ?>
											<?php if($session->guild_id){ ?>
												<button class="icon-button font _24 sq-40  border border-all border-1 <?= $guilds[$session->guild_id];?>-400 white-bg" style="background-image: url(<?= $guild_logos[$session->guild_id]; ?>);">
												</button>
											<?php } ?>
											| <span class="icon icon-time"></span> <?= date('H:i', strtotime($session->session_start)); ?> - 
											<?= date('H:i', strtotime($session->session_end)); ?> | <?= $the_zone; ?> 
											<?= $session->session_room ? " | <strong class='amber-400'>$session->session_room</strong>" : ''; ?>

										</p>
										<p class="font _18 w300 line-150">
											<?= wp_trim_words($session->session_description, 50);?>
										</p>
									</div>
								</div> 
							</div>
						</div>
						<?php } ?>
						<?php } ?>
					</div>
					<?php foreach($sessions as $key=>$session){ ?>
						<?php 
							$bg_image = $adventure->adventure_badge;
							if($session->speaker_picture){
								$bg_image = $session->speaker_picture; 
							}elseif($achievement_badge[$session->achievement_id]){
								$bg_image = $achievement_badge[$session->achievement_id]; 
							}elseif($guild_logos[$session->guild_id]){
								$bg_image = $guild_logos[$session->guild_id]; 
							}						
						?>
						<div id="session-detail-<?= $key; ?>" class="overlay-layer session-detail">
							<div class="background grey-bg-900 fixed"></div>
							<div class="background black-bg opacity-40 blend-luminosity fixed cursor-pointer" style="background-image: url(<?= $bg_image; ?>);" onClick="hideAllOverlay(); activate('#milestone-session-<?= $key; ?>');"></div>
							<div class="session-detail-content white-color">
								<div class="layer absolute top right foreground">
									<button class="icon-button font _24 sq-40  red-bg-400 icon-xs" onClick="hideAllOverlay(); activate('#milestone-session-<?= $key; ?>');"><span class="icon icon-cancel"></span></button>
								</div>
								<?php if($isGM){ ?>
									<div class="layer absolute top left foreground">
										<a class="icon-button font _24 sq-40  icon-xs font _14 green-bg-400" href="<?= get_bloginfo('url')."/new-session/?adventure_id=$adventure->adventure_id&session_id=$session->session_id";?>"> <span  class="icon icon-edit"></span> </a>
									</div>
								<?php } ?>
								<div class="highlight text-center padding-10 margin-0">
									<h1 class="font _30 w600"><?= $session->session_title; ?></h1>
								</div>
								<div class="highlight text-center padding-10 margin-0">
									<div class="background <?=$adventure->adventure_color; ?>-bg-300 opacity-20"></div>
									<div class="icon-group foreground">
										<div class="icon-content">
											<span class="line font _24 w500"><?= date('D M jS, Y', strtotime($session->session_start)); ?></span>
											<span class="line font _18 w500">
												<?php 
													echo date('H:i', strtotime($session->session_start))." - ".date('H:i', strtotime($session->session_end));
													if($session->session_room){
														echo " |  $session->session_room";
													}
												?>														
											</span>
										</div>
									</div>
								</div>
								<?php if($session->speaker_id){ ?>
								<div class="highlight text-center padding-10 margin-10">
									<div class="icon-group">
										<div class="icon-button font _24 sq-40  border border-all <?=$adventure->adventure_color; ?>-border-300" style="background-image: url(<?= $bg_image; ?>);" >
											
										</div>
										<div class="icon-content text-left">
											<span class="line font _24 w500">
												<?= "$session->speaker_first_name $session->speaker_last_name"; ?>
											</span>
											<?php if($session->speaker_company){ ?>
												<span class="line font _14 w100">
													<?= "$session->speaker_company"; ?>
												</span>
											<?php } ?>
										</div>
									</div>
								</div>
								<?php } ?>
								<div class="highlight text-center padding-10 margin-10">
									<?php if($session->quest_id){ ?>
										<?php 
										if($session->quest_type == 'quest'){ 
											$color = "blue-bg-400";
										}elseif($session->quest_type == 'challenge'){
											$color = "brown-bg-400";
										}
										?>
										<a class="form-ui <?= $color; ?>" href="<?= get_bloginfo('url')."/$session->quest_type/?adventure_id=$adventure->adventure_id&questID=$session->quest_id"; ?>">
											<span class="icon icon-<?= $session->quest_type; ?>"></span>
											<?= __("View")." $session->quest_type"; ?>
										</a>
									<?php } ?>
								</div>
								<div class="content">
									<?= apply_filters('the_content', $session->session_description); ?>
								</div>
								<?php if($session->speaker_bio){ ?>
									<div class="highlight padding-10 text-center">
										<button class="form-ui red-bg-300 font _14" target="_blank"  onClick="$('#speaker-bio-<?= $key; ?>').toggleClass('active');"><span class="icon icon-language"></span> <?php _e("Speaker Bio","bluerabbit"); ?></button>
									</div>
									<div class="speaker-bio " id="speaker-bio-<?= $key; ?>">
										<div class="background black-bg opacity-50"></div>
										<div class="line-150 font _14 padding-10 text-center foreground">
											<span class="icon-button font _24 sq-40  icon-xl" style="background-image: url(<?= $session->speaker_picture; ?>);"></span>
											<br>
											<span class="font _24 w300"><?= "$session->speaker_first_name $session->speaker_last_name"; ?></span><br>
											<?= apply_filters('the_content', $session->speaker_bio); ?>
											<button class="icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="$('#speaker-bio-<?= $key; ?>').toggleClass('active');">
												<span class="icon icon-arrow-up white-color"></span>
											</button>
										</div>
									</div>
								<?php } ?>
							</div>

						</div>
					<?php } ?>
				<?php }else{ ?>
					<div class="highlight padding-10 text-center red-bg-50 red-600">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  icon-50 white-bg red-400">
								<span class="icon icon-warning"></span>
							</span>
							<span class="icon-content">
								<span class="line font _40 w500">
									<?php _e("No Sessions Available","bluerabbit"); ?>
								</span>
							</span>
							
						</span>
					</div>
				<?php } ?>
			</div>
		</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
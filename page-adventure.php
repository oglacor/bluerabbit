<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
	<?php if(!$current_player->player_hide_intro && $adventure->adventure_instructions){ ?>
		<script>
			document.location.href="<?=get_bloginfo('url')."/about-adventure/?adventure_id=$adv_child_id"; ?>";
		</script>
	<?php }?>

	<?php
		if($adventure->adventure_has_guilds && !$current_player->player_guild && !$isGM && !$isAdmin){
			$guild_assigned = assignGuild($current_user->ID, $adventure->adventure_id);
		}
	?>

	<!-- Then check for new level-->
	<div class="content layer relative top-overlay">
		<?php 
			$today = date('Y-m-d H:i:s');
			if($my_achievements){
				$my_achievements_imploded = implode(',',$player_achievements);
				$ach_str = "AND (quests.achievement_id IN($my_achievements_imploded) OR quests.achievement_id = 0)";
			}else{
				$ach_str = "AND quests.achievement_id = 0";
			}
			$autoloads = $wpdb->get_results("SELECT quests.*, pps.player_id, pps.pp_status
			FROM {$wpdb->prefix}br_quests quests
			LEFT JOIN {$wpdb->prefix}br_player_posts pps ON quests.quest_id=pps.quest_id AND pps.player_id=$current_player->player_id
			WHERE quests.adventure_id=$adventure->adventure_id 
			AND pps.pp_status IS NULL
			AND quests.quest_type='quest' 
			AND quests.quest_status='publish' 
			AND quests.quest_relevance='autoload'
			AND quests.mech_level <= $current_player->player_level
			$ach_str
			ORDER BY quests.quest_order ASC"); 
			if($autoloads){
				$autoload = false;
				foreach($autoloads as $key=>$auto){
					if(!$autoload){ 
						if($auto->mech_start_date == '0000-00-00 00:00:00' && $auto->mech_deadline == '0000-00-00 00:00:00'){
							$autoload = $auto;
						}else{
							if($auto->mech_start_date != '0000-00-00 00:00:00' && $auto->mech_deadline == '0000-00-00 00:00:00'){
								if(strtotime($today) >= strtotime($auto->mech_start_date)){
									$autoload = $auto;
								}else{
									$autoload = false;
								}
							}
							if($auto->mech_deadline != '0000-00-00 00:00:00' && $auto->mech_start_date == '0000-00-00 00:00:00'){
								if(strtotime($today) < strtotime($auto->mech_deadline)){
									$autoload = $auto;
								}else{
									$autoload = false;
								}
							}
							if($auto->mech_deadline != '0000-00-00 00:00:00' && $auto->mech_start_date != '0000-00-00 00:00:00'){
								if(strtotime($today) >= strtotime($auto->mech_start_date) && strtotime($today) < strtotime($auto->mech_deadline)){
									$autoload = $auto;
								}else{
									$autoload = false;
								}
							}
						}
					}
				}
			}
			if($autoload){
				$finishedAutoLoad = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_posts WHERE player_id=$current_player->player_id AND quest_id=$autoload->quest_id AND adventure_id=$adventure->adventure_id");
			}
			?>		
			<?php if($autoload && !$finishedAutoLoad){ 
				$url = get_bloginfo('url')."/quest/?questID=$autoload->quest_id&adventure_id=$adventure->adventure_id";
			?>
			<script>
				document.location.href="<?= $url;?>";
			</script>
		<?php exit();
			} ?>
		<?php if($current_player->player_level > $current_player->player_prev_level){ ?>
			<audio id="audio-funky">
				<source src="<?= get_bloginfo('template_directory'); ?>/audio/levelup.mp3" type="audio/mpeg">
			</audio>
			<script>
				$(document).ready(function() {
					$("#audio-funky").get(0).play();
					updatePrevLevel(<?= "$current_player->player_level, $adventure->adventure_id"; ?>);
				});
			</script>
		<?php } ?>
	</div>
	<?php if(isset($quests) && !empty($quests)){ ?>
		<div class="journey-builder-controls">
			<button class="reset action-button" onClick="toggleJourneyView();"><?= __("Change View","bluerabbit"); ?> </button>
			<?php if(isset($isDemo) && $isDemo){ ?>
				<button class="action-button danger" onClick="showOverlay('#reset-demo-form');"><span class="icon icon-rotate"></span><?= __("Reset Demo","bluerabbit"); ?></button>
			<?php } ?>
			<button id="zoom-out" class="action-button">
				<?= __("Zoom Out","bluerabbit"); ?>
			</button>
			<button id="zoom-in" class="action-button">
				<?= __("Zoom In","bluerabbit"); ?>
			</button>
			<button id="zoom-reset" class="action-button">
				<?= __("Reset Zoom","bluerabbit"); ?>
			</button>



		</div>

		<div class="journey-container">
			<?php include (TEMPLATEPATH . '/journey.php'); ?>
		</div>



<?php 
	$tabi_on_journey = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_tabis WHERE adventure_id=$adv_parent_id AND tabi_on_journey=1 AND tabi_status='publish'"); 
	if($tabi_on_journey){ 
		$adv_tabi = getMyTabi($tabi_on_journey->tabi_id);
		?>
		<svg width="0" height="0" style="position: absolute;">
		<defs>
			<clipPath id="tabi-screen-clip" clipPathUnits="objectBoundingBox">
				<polygon points="
					1.0,0.92709 0.9423,0.92709 0.86939,1.0 0.05628,1.0 
					0.0,0.94372 0.0,0.58668 0.02369,0.56299 0.02369,0.3009 
					0.0,0.27722 0.0,0.03606 0.03606,0.0 0.41379,0.0 
					0.43914,0.02535 0.97519,0.02535 1.0,0.05016 1.0,0.32177 
					0.98533,0.33643 0.98533,0.39657 1.0,0.41123 
					1.0,0.50071 0.98011,0.52059 0.98011,0.58986 
					1.0,0.60975 1.0,0.92709
				"/>
			</clipPath>
		</defs>
		</svg>
		<div class="adv-tabi">
			<div class="tabi">
				<div class="hud-display active" id="hud-display-<?=$adv_tabi['tabi']->tabi_id; ?>">
					<div class="hud-screen-container active">
						<div class="hud-screen-content">
							<div class="tabi-pieces" style="background-image: url('<?= $adv_tabi['tabi']->tabi_background; ?>');" id="tabi-pieces-<?=$a->tabi_id; ?>">
								<?php foreach($adv_tabi['pieces'] as $i){ ?>
									<div class="tabi-piece" id="tabi-piece-<?=$i->item_id; ?>" style="z-index: <?= $i->item_z; ?>;top:<?= $i->item_y; ?>%; left:<?= $i->item_x; ?>%; transform:rotate(<?= $i->item_rotation; ?>deg);width:<?=$i->item_scale;?>%">
										<div class="tabi-piece-image">
											<img src="<?= $i->item_badge; ?>" alt="<?= $i->item_name; ?>" title="<?= $i->item_name; ?>">
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<svg id="tabi-<?=$adv_tabi['tabi']->tabi_id; ?>" class="hud-screen-graphics" " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 1920">
							<polygon class="screen-cover" points="1920 1780.01 1809.21 1780.01 1669.22 1920 108.06 1920 0 1811.94 0 1126.42 45.48 1080.95 45.48 577.73 0 532.26 0 69.23 69.23 0 794.47 0 843.15 48.67 1872.37 48.67 1920 96.3 1920 617.79 1891.84 645.94 1891.84 761.42 1920 789.57 1920 961.36 1881.82 999.54 1881.82 1132.54 1920 1170.72 1920 1780.01"/>
							<path class="screen-outline" d="M120.49,1890l-90.49-90.49v-660.66l45.48-45.48v-528.06l-45.48-45.48V81.66l51.66-51.66h700.39l48.67,48.67h1029.22l30.06,30.06v496.63l-28.16,28.16v140.33l28.16,28.16v146.94l-38.18,38.18v157.85l38.18,38.18v566.87h-93.22l-139.99,139.99H120.49M108.06,1920h1561.16l139.99-139.99h110.79v-609.29l-38.18-38.18v-133l38.18-38.18v-171.79l-28.16-28.16v-115.48l28.16-28.15V96.3l-47.63-47.63H843.15L794.47,0H69.23L0,69.23v463.02l45.48,45.48v503.21L0,1126.42v685.51l108.06,108.06h0Z"/>
						</svg>
					</div>
				</div>
			</div>
		</div>

	<?php } ?>




<div class="milestone-preview" id="milestone-preview">
	<div class="decor-corner top-right"></div>
	<div class="decor-corner top-left"></div>
	<div class="decor-corner bottom-right"></div>
	<div class="decor-corner bottom-left"></div>
	<div class="milestone-preview-resources">

		<span class="milestone-preview-xp" id="milestone-preview-xp">
			<span class="number">0</span>
			<input type="hidden" class="end-value" value="">
		</span>
		<span class="milestone-preview-bloo" id="milestone-preview-bloo">
			<span class="number">0</span>
			<input type="hidden" class="end-value" value="">
		</span>
		<?php if($use_encounters){ ?>
			<span class="milestone-preview-ep" id="milestone-preview-ep">
				<span class="number">0</span>
				<input type="hidden" class="end-value" value="">
			</span>
		<?php } ?>
	</div>
	<div class="milestone-preview-content">
	</div>
</div>
<div class="milestone-preview-bg" id="milestone-preview-bg" onClick="activateMilestone();"></div>


	<?php }else{?>	
		<div class="overflow-hidden perfect-center w-300 border rounded-8 absolute layer base padding-10 text-center">
			<div class="layer absolute background white-bg opacity-80"></div>
			<div class="layer base relative font _24 w900 uppercase blue-500">
				<?php _e("This is one empty journey","bluerabbit"); ?>
			</div>
		</div>
	<?php } ?>	

	<?php if($isGM){ ?>
		<input type="hidden" value="<?= wp_create_nonce('trash_nonce') ?>" id="trash-nonce">
		<input type="hidden" value="<?= wp_create_nonce('hidden_nonce') ?>" id="hidden-nonce">
		<input type="hidden" value="<?= wp_create_nonce('publish_nonce') ?>" id="publish-nonce">
		<input type="hidden" value="<?= wp_create_nonce('draft_nonce') ?>" id="draft-nonce">
	<?php } ?>
	<input type="hidden" id="deadline-nonce" value="<?= wp_create_nonce('deadline_nonce'); ?>"/>
	<input type="hidden" id="unlock-nonce" value="<?= wp_create_nonce('unlock_nonce'); ?>"/>

		
<?php }else{ ?>
	<h1><?php _e("Adventure doesn't exist","bluerabbit"); ?></h1>
	<script>document.location.href="<?php bloginfo('url');?>"; </script>
<?php } ?>




<script>


zoomLevel = <?= $journey_zoom_level; ?>; 
resizeJourneyMapWithPadding(-zoomLevel);

centerJourneyMap();
applyZoom();


// Zoom controls
$('#zoom-in').on('click', function () {
	zoomLevel += 100;
	applyZoom();
});

$('#zoom-out').on('click', function () {
	zoomLevel -= 100;
	applyZoom();
});

$('#zoom-reset').on('click', function () {
	zoomLevel = 0;
	applyZoom();
	centerJourneyMap();
});

</script>




<?php include (get_stylesheet_directory() . '/footer.php'); ?>


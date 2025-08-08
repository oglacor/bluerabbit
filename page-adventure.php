<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
	<?php if(!$current_player->player_hide_intro && $adventure->adventure_instructions){ ?>
		<script>
			document.location.href="<?=get_bloginfo('url')."/about-adventure/?adventure_id=$adventure->adventure_id"; ?>";
		</script>
	<?php }?>

	<?php
		if($adventure->adventure_has_guilds && !$current_player->player_guild && !$isGM && !$isAdmin){
			assignGuild($current_user->ID, $adventure->adventure_id);
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



resizeJourneyMapWithPadding(300);

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


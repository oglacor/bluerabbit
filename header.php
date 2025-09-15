<?php
	$current_user = wp_get_current_user();
	$config = getSysConfig();
	if(isset($config['support_email']['value'])){
		$support_email = $config['support_email']['value'];
	}else{
		$support_email = 'help@bluerabbit.io';
	}
	if(!isset($_SESSION['player'])){
		$_SESSION['player'] = wp_get_current_user();
	}
	$roles = $current_user->roles;
	if($current_user->roles[0] == 'administrator'){
		$features = getFeatures('admin');
		$f_role = 'admin';
	}elseif($current_user->roles[0] == 'br_game_master' || $roles[0] == 'br_npc'){
		$features = getFeatures('pro');
		$f_role = 'pro';
	}else{
		$features = getFeatures('free');
		$f_role = 'free';
	}

 	if($config['default_adventure']['value']>0){
		$adventure_id = $config['default_adventure']['value'];
		defaultEnrollment($adventure_id, $current_user->ID);
	}elseif(isset($_GET['adventure_id'])){
		$adventure_id = $_GET['adventure_id'];
	}
	if(isset($adventure_id)){
		$adventure = getAdventure($adventure_id);
        $adv_child_id = $adventure->adventure_id;
        $adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;
		$adv_settings = getSettings($adv_parent_id);

		
		if($adventure){
			if(isset($adventure->adventure_gmt)){
				date_default_timezone_set($adventure->adventure_gmt);
			}else{
				date_default_timezone_set('America/Mexico_City');
			}
			$_SESSION['adventure'] = $adventure;
			$lastLogin = registerAdventureLogin($adv_child_id);
			$current_player = getPlayerAdventureData($adv_child_id, $current_user->ID);
			if($current_player->achievement_id){
				$myRank = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$current_player->achievement_id AND achievement_status='publish'");
			}
			$isAdmin = $isNPC = $isOwner = $isGM = false;
			if($adventure->adventure_owner == $current_user->ID){
				$isGM = true;
				$isOwner = true;
			}elseif($adventure->player_adventure_role == 'gm'){
				$isGM = true;
			}elseif($adventure->player_adventure_role == 'npc'){
				$isNPC = true;
			}
			
			/* PLAYER RESET STARTS*/
			$playerReset = getPlayerProgress($adv_child_id, $current_user->ID);
			//$playerReset = getMyAdvPlayerData($adv_child_id, $current_user->ID);
			
			$quests=isset($playerReset['quests']) ? $playerReset['quests'] : [];
			$reqs=isset($playerReset['reqs']) ? $playerReset['reqs'] : NULL;
			$reqs_ids=isset($playerReset['reqs_ids']) ? $playerReset['reqs_ids'] : NULL;
			$guildwork=isset($playerReset['guildwork']) ? $playerReset['guildwork'] : NULL;
			$player=isset($playerReset['player']) ? $playerReset['player'] : NULL;
			$my_items=isset($playerReset['items']) ? $playerReset['items'] : NULL;
			$player_achievements=isset($playerReset['achievements_ids']) ? $playerReset['achievements_ids'] : NULL;
			$my_achievements= isset($playerReset['achievements']) ? $playerReset['achievements'] : NULL;
			/* PLAYER RESET END */
			
			$use_challenges = isset($adv_settings['use_challenges']['value']) ? $adv_settings['use_challenges']['value'] : "";
			$use_missions = isset($adv_settings['use_missions']['value']) ? $adv_settings['use_missions']['value'] : "";
			$use_encounters = isset($adv_settings['use_encounters']['value']) ? $adv_settings['use_encounters']['value'] : "";
			$use_surveys = isset($adv_settings['use_surveys']['value']) ? $adv_settings['use_surveys']['value'] : "";
			$show_survey_answers = isset($adv_settings['show_survey_answers']['value']) ? $adv_settings['show_survey_answers']['value'] : "";
			$show_survey_names = isset($adv_settings['show_survey_names']['value']) ? $adv_settings['show_survey_names']['value'] : "";
			$use_achievements = isset($adv_settings['use_achievements']['value']) ? $adv_settings['use_achievements']['value'] : "";
			$hide_achievements = isset($adv_settings['hide_achievements']['value']) ? $adv_settings['hide_achievements']['value'] : "";
			$allow_magic_codes = isset($adv_settings['use_magic_codes']['value']) ? $adv_settings['use_magic_codes']['value'] : "";
			$use_blockers = isset($adv_settings['use_blockers']['value']) ? $adv_settings['use_blockers']['value'] : "";
			$use_wall = isset($adv_settings['use_wall']['value']) ? $adv_settings['use_wall']['value'] : "";
			$use_guilds = isset($adv_settings['use_guilds']['value']) ? $adv_settings['use_guilds']['value'] : "";
			$use_blog = isset($adv_settings['use_blog']['value']) ? $adv_settings['use_blog']['value'] : "";
			$use_lore = isset($adv_settings['use_lore']['value']) ? $adv_settings['use_lore']['value'] : "";
			$use_leaderboard = isset($adv_settings['use_leaderboard']['value']) ? $adv_settings['use_leaderboard']['value'] : "";
			$leaderboard_limit = isset($adv_settings['leaderboard_limit']['value']) ? $adv_settings['leaderboard_limit']['value'] : "";
			$use_schedule = isset($adv_settings['use_schedule']['value']) ? $adv_settings['use_schedule']['value'] : "";
			$use_speakers = isset($adv_settings['use_speakers']['value']) ? $adv_settings['use_speakers']['value'] : "";
			$default_journey_view = isset($adv_settings['default_journey_view']['value']) ? $adv_settings['default_journey_view']['value'] : "";
			$use_item_shop = isset($adv_settings['use_item_shop']['value']) ? $adv_settings['use_item_shop']['value'] : "";
			$use_backpack = isset($adv_settings['use_backpack']['value']) ? $adv_settings['use_backpack']['value'] : "";
			$item_shop_status = isset($adv_settings['item_shop_status']['value']) ? $adv_settings['item_shop_status']['value'] : "";
			$reset_transatcions = isset($adv_settings['reset_transactions']['value']) ? $adv_settings['reset_transactions']['value'] : "";
			$buy_items = isset($adv_settings['purchase_permission']['value']) ? $adv_settings['purchase_permission']['value'] : "";
			$use_items = isset($adv_settings['use_items']['value']) ? $adv_settings['use_items']['value'] : "";
			$show_player_transactions = isset($adv_settings['show_my_transactions']['value']) ? $adv_settings['show_my_transactions']['value'] : "";
			$show_adventure_status = isset($adv_settings['show_adventure_status']['value']) ? $adv_settings['show_adventure_status']['value'] : "";
			$adventure_theme = isset($adv_settings['adventure_theme']['value']) ? $adv_settings['adventure_theme']['value'] : "default";
			$journey_zoom_level = isset($adv_settings['journey_zoom_level']['value']) ? $adv_settings['journey_zoom_level']['value'] : "0";
			
			
			if(isset($adv_settings['support_email']['value']) && $adv_settings['support_email']['value'] != ""){
				$support_email = $adv_settings['support_email']['value'];
			} 
			$isDemo = getSetting('demo_adventure', $adv_child_id);

			$xp_long_label = $adventure->adventure_xp_long_label ? $adventure->adventure_xp_long_label : "Experience Points";
			$bloo_long_label = $adventure->adventure_bloo_long_label ? $adventure->adventure_bloo_long_label : "Bloo coins";
			$ep_long_label = $adventure->adventure_ep_long_label ? $adventure->adventure_ep_long_label : "Energy Points";
			$xp_label = $adventure->adventure_xp_label ? $adventure->adventure_xp_label : "XP";
			$bloo_label = $adventure->adventure_bloo_label ? $adventure->adventure_bloo_label : "BLOO";
			$ep_label = $adventure->adventure_ep_label ? $adventure->adventure_ep_label : "EP";
			$bgs = array (
				'journey' => isset($adv_settings['journey_bg']['value']) ? $adv_settings['journey_bg']['value'] : $config['journey_bg']['value'],
				'item_shop' => isset($adv_settings['item_shop_bg']['value']) ? $adv_settings['item_shop_bg']['value'] : $config['item_shop_bg']['value'],
				'backpack' => isset($adv_settings['backpack_bg']['value']) ? $adv_settings['backpack_bg']['value'] : $config['backpack_bg']['value'],
				'guilds' => isset($adv_settings['guilds_bg']['value']) ? $adv_settings['guilds_bg']['value'] : $config['guilds_bg']['value'],
				'schedule' => isset($adv_settings['schedule_bg']['value']) ? $adv_settings['schedule_bg']['value'] : $config['schedule_bg']['value'],
				'blog' => isset($adv_settings['blog_bg']['value']) ? $adv_settings['blog_bg']['value'] : $config['blog_bg']['value'],
				'lore' => isset($adv_settings['lore_bg']['value']) ? $adv_settings['lore_bg']['value'] : $config['lore_bg']['value'],
				'wall' => isset($adv_settings['wall_bg']['value']) ? $adv_settings['wall_bg']['value'] : $config['wall_bg']['value'],
				'leaderboard' => isset($adv_settings['leaderboard_bg']['value']) ? $adv_settings['leaderboard_bg']['value'] : $config['leaderboard_bg']['value'],
				'my_work' => isset($adv_settings['my_work_bg']['value']) ? $adv_settings['my_work_bg']['value'] : $config['my_work_bg']['value'],
			);
		}else{
			unset($_SESSION['adventure']);
		}
	}else{
		$current_player = getPlayerData($current_user->ID);
		$myAdventures = $wpdb->get_col("SELECT adventure_id FROM {$wpdb->prefix}br_adventures WHERE adventure_owner=$current_user->ID");
		
		$add_adventure = false;
		$add_from_template = false;
		if(isset($config['add_adventure_from_template'])){
			$new_adv_ft = $config['add_adventure_from_template']['value'];
			if($new_adv_ft == 'gm'){
				if($roles[0] == 'br_game_master'){
					$add_from_template = true;
				}
			}else if($new_adv_ft == 'npc'){
				if($roles[0] == 'br_game_master' || $roles[0] == 'br_npc'){
					$add_from_template = true;
				}
			}else if($new_adv_ft == 'all'){
				if($roles[0] == 'br_game_master' || $roles[0] == 'br_npc' || $roles[0] == 'br_player'){
					$add_from_template = true;
				}
			}else{
				$add_from_template = false;
			}
		}
		if(isset($config['add_new_adventure'])){
			$new_adv_cap = $config['add_new_adventure']['value'];
			if($new_adv_cap == 'gm'){
				if($roles[0] == 'br_game_master'){
					$add_adventure = true;
				}
			}else if($new_adv_cap == 'npc'){
				if($roles[0] == 'br_game_master' || $roles[0] == 'br_npc'){
					$add_adventure = true;
				}
			}else if($new_adv_cap == 'all'){
				if($roles[0] == 'br_game_master' || $roles[0] == 'br_npc' || $roles[0] == 'br_player'){
					$add_adventure = true;
				}
			}else{
				$add_adventure = false;
			}
		}
		if($add_adventure == true){
			if(count($myAdventures) >= $features['max_adventures'][$f_role]){
				$add_adventure = false;
			}else{
				$add_adventure = true;
			}
		}
		
		
	}


	if($roles[0]=='administrator'){
		$isAdmin = true; $isGM = true;
		$add_from_template = true;
		$add_adventure = true;
	}
	if(is_page('adventure')){
		$bg = isset($adv_settings['journey_bg']['value']) != '' ? $adv_settings['journey_bg']['value'] : $config['journey_bg']['value'];
	}elseif(is_page('item-shop')){
		$bg = isset($adv_settings['item_shop_bg']['value']) != '' ? $adv_settings['item_shop_bg']['value'] : $config['item_shop_bg']['value'];
	}elseif(is_page('backpack')){
		$bg = isset($adv_settings['backpack_bg']['value']) != '' ? $adv_settings['backpack_bg']['value'] : $config['backpack_bg']['value'];
	}elseif(is_page('guilds')){
		$bg = isset($adv_settings['guilds_bg']['value']) != '' ? $adv_settings['guilds_bg']['value'] : $config['guilds_bg']['value'];
	}elseif(is_page('schedule')){
		$bg = isset($adv_settings['schedule_bg']['value']) != '' ? $adv_settings['schedule_bg']['value'] : $config['schedule_bg']['value'];
	}elseif(is_page('blog')){
		$bg = isset($adv_settings['blog_bg']['value']) != '' ? $adv_settings['blog_bg']['value'] : $config['blog_bg']['value'];
	}elseif(is_page('lore')){
		$bg = isset($adv_settings['lore_bg']['value']) != '' ? $adv_settings['lore_bg']['value'] : $config['lore_bg']['value'];
	}elseif(is_page('wall')){
		$bg = isset($adv_settings['wall_bg']['value']) != '' ? $adv_settings['wall_bg']['value'] : $config['wall_bg']['value'];
	}elseif(is_page('leaderboard')){
		$bg = isset($adv_settings['leaderboard_bg']['value']) != '' ? $adv_settings['leaderboard_bg']['value'] : $config['leaderboard_bg']['value'];
	}elseif(is_page('player-work')){
		$bg = isset($adv_settings['my_work_bg']['value']) != '' ? $adv_settings['my_work_bg']['value'] : $config['my_work_bg']['value'];
	}else{
		$bg = isset($adv_settings['default_bg']['value']) != '' ? $adv_settings['default_bg']['value'] : $config['default_bg']['value'];
	}
	if(!$bg){ $bg = $config['default_bg']['value']; }

	$favicon = isset($config['favicon']['value']) ? $config['favicon']['value'] : get_bloginfo('template_directory')."/images/favicon.png"; 

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>
			<?php bloginfo('name');?>
			<?php if(is_page('report')){ echo "-report"; }?>
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
		<link rel="icon" type="image/gif" href="<?=$favicon; ?>">
		<link rel="alternate" href="https://bluerabbit.io" hreflang="en" />

		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory');?>/css/jquery-ui.min.css" >
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory');?>/css/jquery.datetimepicker.min.css" >

		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;500;700&display=swap">
		<link rel="stylesheet" href="https://use.typekit.net/zfu4fjz.css">
		<link rel="stylesheet" href="https://bluerabbit.io/fonts/font.css">
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>">
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory');?>/css/style-framework.css">
		
		<script>
			let hash_change_type = false;
			<?php if(is_page('quest')){ ?>
				 hash_change_type = 'quest';
			<?php }elseif(is_page('survey')){ ?>
				 hash_change_type = 'survey';
			<?php } ?>
		</script>
		
		
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/shepherd.js@5.0.1/dist/js/shepherd.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.datetimepicker.full.min.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/Chart.js"></script>
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
	<body id="body">
		<?php if(isset($adventure) && !is_page('config')){ ?>		
			<input type="hidden" id="the_adventure_id" value="<?= $adv_child_id; ?>">
			<input type="hidden" id="the_adventure_parent_id" value="<?= $adv_parent_id; ?>">
			<input type="hidden" id="payment_nonce" value="<?= wp_create_nonce('br_payment_nonce'); ?>"/>
		<?php } ?>
		<input type="hidden" id="template_directory" value="<?php echo get_bloginfo('template_directory'); ?>">
		<input type="hidden" id="bloginfo_url" value="<?php echo get_bloginfo('url'); ?>">
		<?php $mime = (wp_check_filetype($bg));?>
		
		<?php if(!is_page('certificate')){ ?>
			<?php if(strstr($mime['type'], "video")){ ?>
				<div class="deep-bg black-bg fixed fixed-bg sq-full"></div>
				<video id="main-background-video" loop class="overlay-background-video active" autoplay>
					<source src="<?=$bg; ?>">
				</video>
			<?php }else{ ?>
				<div class="background black-bg fixed fixed-bg repeat-bg" id="main-background" style="background-image: url(<?= $bg; ?>);"></div>
			<?php } ?>
		<?php } ?>
		<?php if(isset($adventure)){
			$nextLevel = ($current_player->player_level)*($current_player->player_level+1)/2*1000;
			$percXP = $player['xp_curr_level']*100/($current_player->player_level*1000);
			$percBLOO = $player['totalEarned'] > 0 ? (100-(($player['spent'])*100/($player['totalEarned']))) : 0;
			$maxEP = 100+(($current_player->player_level*($current_player->player_level+1)/2)*20);
			$percEP = $current_player->player_ep*100/$maxEP;
		} ?>
		<?php if(isset($adventure)){ ?>
			<?php if(isset($isAdmin) && $isAdmin && $config['display_admin_nav_bar']['value']){ ?>
				<div class="admin-nav-bar h-40 padding-5 text-left layer relative feedback">
					<div class="black-bg opacity-50 layer background absolute top left sq-full"></div>
					<a class="form-ui grey-bg-400 font _12 layer base relative" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adv_parent_id";?>"><?= __("Manage","bluerabbit"); ?></a>
					<a class="form-ui grey-bg-400 font _12 layer base relative" href="<?= get_bloginfo('url')."/new-adventure/?adventure_id=$adv_parent_id";?>"><?= __("Settings","bluerabbit"); ?></a>
					<a class="form-ui grey-bg-900 font _12 pull-right layer base relative" href="<?= get_bloginfo('url')."/config";?>"><?= __("Sys Config","bluerabbit"); ?></a>
					<a class="form-ui grey-bg-600 font _12 pull-right layer base relative" href="<?= get_bloginfo('url')."/manage-adventures";?>"><?= __("Manage Adventures","bluerabbit"); ?></a>
				</div>
			<?php } ?>
		<?php } ?>
		<header class="super-header">
			<div class="nav-opener" onClick="activateStartMenu();">
				<span class="icon icon-menu"></span>
			</div>
			<div class="logo">
				<?php $main_logo = isset($config['main_logo']['value']) ? $config['main_logo']['value'] : get_bloginfo('template_directory').'/images/logo.png';?> 
				<a href="<?= get_bloginfo('url'); ?>">
					<img src="<?= $main_logo; ?>" height="30">
				</a>
			</div>
			<?php if(isset($adventure)){ ?>
			<div class="adventure-title" id="adventure-title-t_id">
				<img src="<?= get_bloginfo('template_directory').'/images/adventure-title.png';; ?>" height="30" class="adventure-title-logo">
				<a class="adventure-title-text" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id"; ?>"><?= $adventure->adventure_title;?></a>
			</div>
			<?php } ?>
			<div class="tools">
				<?php //print_r($current_player); ?>
				<?php 
				if(isset($current_player->player_current_quest_id) && ($current_player->player_current_quest_id) > 0){ 
					$cq = getQuest($current_player->player_current_quest_id); 
					$cq_link = get_bloginfo('url')."/{$cq->quest_type}/?questID={$cq->quest_id}&adventure_id={$cq->adventure_id}#step-{$current_player->player_current_quest_step}";
				}else{ 
					$current_player->player_current_quest_id = 0;
					$cq_link = ""; 
				}
				?>
				<a class="current-quest-torch icon-button <?= $current_player->player_current_quest_id == 0 ? "hidden" : ""; ?>" id="current-quest-torch" style="background-image: url(<?= get_bloginfo('template_directory'); ?>/images/icons/icon-torch.png); " href="<?= $cq_link; ?>">
				</a>
				<button class="icon-button white-bg border rounded-max" id="profile-box-btn" <?php if(isset($current_player->player_picture)){ ?>style="background-image: url(<?= $current_player->player_picture; ?>); "<?php } ?> onClick="activate('#profile-box');">
				</button>
				<?php if($current_player->player_guild){ ?>
					<?php $myGuild = getMyGuild($adventure_id, $current_player->player_guild); ?>
					<?php $myGuildExists = true; ?>
					<a class="icon-button white-bg border rounded-max" id="guild-btn" <?php if(isset($myGuild->guild_logo)){ ?>style="background-image: url(<?= $myGuild->guild_logo; ?>); "<?php } ?> href="<?= get_bloginfo('url')."/guilds/?adventure_id=$adv_child_id"; ?>">
					</a>
				<?php } ?>
				<?php if(is_page('adventure')){ ?>
					<button class="icon-button transparent-bg font _18 relative" id="tutorial-button-start" onClick="tour.start();" title="<?= __("Start Tutorial","bluerabbit"); ?>" alt="<?= __("Start Tutorial","bluerabbit"); ?>">
						<span class="background layer black-bg opacity-20 border rounded-max absolute"></span>
						<span class="icon icon-question grey-400"></span>
					</button>
				<?php } ?>
				<a class="icon-button relative transparent-bg font _18  border rounded-max" target="_blank" href="mailto:<?= $support_email; ?>" title="<?= __("Email Support","bluerabbit"); ?>" alt="<?= __("Email Support","bluerabbit"); ?>">
					<span class="background layer amber-bg-800 opacity-20 border rounded-max absolute"></span>
					<span class="icon icon-warning"></span>
				</a>
			</div>
		</header>
		<div class="main-content <?php if(is_page('players')){ echo "players-page"; }?>" id="main-content"><!-- OPEN FLEX MAIN CONTENT-->
			<div class="main-container" id="main-container"><!-- OPEN MAIN CONTAINER-->

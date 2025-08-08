<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php $rank = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$current_player->achievement_id AND achievement_status = 'publish'"); ?>
<div class="certificate-options">
	<button class="form-ui" onClick="$('.certificate-rank').toggleClass('invisible');"><?= __("Show/Hide Rank"); ?></button>
	<button class="form-ui" onClick="$('.certificate-stat').toggleClass('invisible');"><?= __("Show/Hide Stats"); ?></button>
</div>
<div class="certificate white-bg" id="bluerabbit-certificate">
	<div class="certificate-logo">
		<img src="<?= $adventure->adventure_logo; ?>" width="100%">
	</div>
	<div class="certificate-headline">
		<h3><?= __("Certificate of participation","bluerabbit"); ?></h3>
	</div>
		<div class="certificate-content">
			<h4><?= __("This is presented to","bluerabbit"); ?>: </h4>
			<h1><?= $current_player->player_first; ?></h1>
			<h2><?= $current_player->player_last; ?></h2>
			<?php
			if(isset($adventure->adventure_start_date)){
				$pretty_start_date = date('jS, M Y', strtotime($adventure->adventure_start_date)); 
			}
			if(isset($adventure->adventure_end_date)){
				$pretty_end_date = date('jS, M Y', strtotime($adventure->adventure_end_date)); 
			}

			?>
			<p>
				<?= __("For reaching level ","bluerabbit")." $current_player->player_level ".__("in the adventure ","bluerabbit")." $adventure->adventure_title"; ?>
				<br>
				<?php 
				
				if($pretty_start_date){ 
					echo __("From ","bluerabbit")." $pretty_start_date ";  
				}
				if($pretty_start_date && $pretty_end_date){ 
					echo __("to ","bluerabbit")." $pretty_end_date";  
				}elseif(!$pretty_start_date && $pretty_end_date){
					echo __("On ","bluerabbit")." $pretty_end_date"; 
				}
				?> 
			</p>
		</div>
		<div class="certificate-rank" id="certificate-rank">
			<h3><?= __("Highest Rank","bluerabbit"); ?></h3>
			
			<div class="certificate-achievement-badge">
				<div class="rank-badge" style="background-image: url(<?= $rank->achievement_badge; ?>);"></div>
				<div class="rank-badge-border <?= $rank->achievement_color;?>"></div>
				<svg class="achievement-decor-outer-hex rank-border" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 234.83 271.15">
				  <polygon class="hex-a" points="221.16 195.48 221.16 75.68 117.41 15.78 13.67 75.68 13.67 195.48 117.41 255.37 221.16 195.48"/>
				  <polygon class="hex-b" points="227.28 199.01 227.28 72.15 117.41 8.72 7.55 72.15 7.55 199.01 117.41 262.44 227.28 199.01"/>
				  <polygon class="hex-c" points="234.33 203.08 234.33 68.08 117.41 .58 .5 68.08 .5 203.08 117.41 270.58 234.33 203.08"/>
				</svg>			
			</div>
			<h2 class="certificate-achievement-name"><?= $rank->achievement_name;?>
			</h2>
		</div>
		
		<div class="certificate-stats-level-icon certificate-stat-cell-icon certificate-stat">
			<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-level.png">
		</div>	
		<div class="certificate-stats-level certificate-stat-cell certificate-stat">
			<h4><?= __("Level","bluerabbit"); ?></h4>
			<h3><?= $current_player->player_level; ?></h3>
		</div>	
		<div class="certificate-stats-xp-icon certificate-stat-cell-icon certificate-stat">
			<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-xp.png">
		</div>	
		<div class="certificate-stats-xp certificate-stat-cell certificate-stat">
			<h4><?= $adventure->adventure_xp_long_label; ?></h4>
			<h3><?= $current_player->player_xp; ?></h3>
		</div>	
		<div class="certificate-stats-bloo-icon certificate-stat-cell-icon certificate-stat">
			<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-bloo.png">
		</div>	
		<div class="certificate-stats-bloo certificate-stat-cell certificate-stat">
			<h4><?= $adventure->adventure_bloo_long_label; ?></h4>
			<h3><?= $current_player->player_bloo; ?></h3>
		</div>	
		<?php if($use_encounters){ ?>
			<div class="certificate-stats-ep-icon certificate-stat-cell-icon certificate-stat">
			<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-ep.png">
			</div>	
			<div class="certificate-stats-ep certificate-stat-cell certificate-stat">
				<h4><?= $adventure->adventure_ep_long_label; ?></h4>
				<h3><?= $current_player->player_ep; ?></h3>
			</div>	
		<?php } ?>
		<?php if($adventure->adventure_certificate_signature){ ?>
			<div class="certificate-signature">
				<img src="<?= $adventure->adventure_certificate_signature; ?>">
			</div>
		<?php } ?>
	
	
	
	
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
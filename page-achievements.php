<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
	<?php
	$paths = $wpdb->get_col("
	SELECT achs.achievement_id FROM ".$wpdb->prefix."br_achievements achs
	JOIN {$wpdb->prefix}br_player_achievement player
	ON player.achievement_id=achs.achievement_id AND player.player_id=$current_user->ID AND player.adventure_id=$adv_child_id

	WHERE achs.adventure_id=$adv_parent_id AND achs.achievement_display='path' AND achs.achievement_status='publish'

	");
	
	if($isNPC || $isGM || $isAdmin || !$hide_achievements){
		$achievements = $wpdb->get_results("SELECT 
			a.*, b.player_id, b.achievement_applied
			FROM {$wpdb->prefix}br_achievements a
			LEFT JOIN {$wpdb->prefix}br_player_achievement b
			ON a.achievement_id = b.achievement_id AND b.player_id=$current_user->ID AND b.adventure_id=$adventure->adventure_id AND b.adventure_id=$adv_child_id
			WHERE a.adventure_id=$adv_parent_id AND a.achievement_status='publish'
			GROUP BY a.achievement_id
			ORDER BY FIELD(a.achievement_display,'badge','path','rank'), a.achievement_order
		");
	}else{
		$achievements = $wpdb->get_results("SELECT 
			a.*, b.player_id, b.achievement_applied
			FROM {$wpdb->prefix}br_achievements a
			LEFT JOIN {$wpdb->prefix}br_player_achievement b
			ON a.achievement_id = b.achievement_id AND b.player_id=$current_user->ID AND b.adventure_id=$adventure->adventure_id AND b.adventure_id=$adv_child_id
			WHERE a.adventure_id=$adv_parent_id AND a.achievement_status='publish' AND b.player_id=$current_user->ID
			GROUP BY a.achievement_id
			ORDER BY FIELD(a.achievement_display,'badge','path','rank'), a.achievement_order
		");
		
	}
	
	?>
	<div class="boxed max-w-1200 layer base relative">
		<div class="headline">
			<img src="<?= get_bloginfo('template_directory')."/images/icons/icon-achievement.png";?>"> <?= __("Achievements","bluerabbit"); ?>
			<?php if($show_certificate){ ?>
				<a class="form-ui pull-right font _18" href="<?= get_bloginfo('url'); ?>/certificate/?adventure_id=<?= $adventure->adventure_id;?>"><?= __('Adventure Certificate',"bluerabbit"); ?></a>
			<?php } ?>
		</div>
		
		<?php if($isNPC || $isGM || $isAdmin){ ?>
			<div class="search sticky">
				<div class="input-group">
					<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
					<label>
						<span class="icon icon-search"></span>
					</label>
					<script>
						$('#search').keyup(function(){
							var valThis = $(this).val().toLowerCase();
							if(valThis == ""){
								$('#achievements-grid >  .achievement-element').show();           
							}else{
								$('#achievements-grid >  .achievement-element').each(function(){
									var text = $(this).text().toLowerCase();
									(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
								});
							};
						});
					</script>				
				</div>
			</div>
		<?php } ?>
		<div class="achievements">
			<div class="achievements-grid" id="achievements-grid">
				<?php  
					$badge_count=1; 
					foreach($achievements as $a){ 
						if(($a->player_id==$current_user->ID) || (!$a->achievement_path) || (in_array($a->achievement_path, $paths) )){
							include (TEMPLATEPATH . '/achievement.php'); 
							$badge_count++;
						}
						if($badge_count > 5){
							$badge_count = 1;
						}
					} 
				?>
			</div>
			<div class="achievements-display" id="achievements-display">
				<div class="achievement-card">
					<div class="achievement-card-bg"></div>
					<div class="achievement-card-header">
						<span class="achievement-card-xp" id="achievement-card-xp">
							<span class="number">0</span>
							<input type="hidden" class="end-value" value="">
						</span>
						<span class="achievement-card-bloo" id="achievement-card-bloo">
							<span class="number">0</span>
							<input type="hidden" class="end-value" value="">
						</span>
						<span class="achievement-card-ep" id="achievement-card-ep">
							<span class="number">0</span>
							<input type="hidden" class="end-value" value="">
						</span>
					</div>
					<div class="achievement-card-content">
						<div class="achievement-card-badge-container">
							<div class="achievement-card-decor-outer">
								<svg class="achievement-decor-outer-hex" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 234.83 271.15">
								  <polygon class="hex-a" points="221.16 195.48 221.16 75.68 117.41 15.78 13.67 75.68 13.67 195.48 117.41 255.37 221.16 195.48"/>
								  <polygon class="hex-b" points="227.28 199.01 227.28 72.15 117.41 8.72 7.55 72.15 7.55 199.01 117.41 262.44 227.28 199.01"/>
								  <polygon class="hex-c" points="234.33 203.08 234.33 68.08 117.41 .58 .5 68.08 .5 203.08 117.41 270.58 234.33 203.08"/>
								</svg>			
							</div>
							<div class="achievement-card-badge">
								<svg class="decor-border" viewBox="0 0 200 230">
									<path class="" d="M100,23.07l80,46v91.86l-80,46-80-46v-91.86L100,23.07M100,0L0,57.5v115l100,57.5,100-57.5V57.5L100,0h0Z"/>
								</svg>	
								<img class="achievement-card-decor-inner" src="<?= get_bloginfo('template_directory');?>/images/achievement-card-decor-inner.svg" alt=""/>
								<img class="achievement-card-decor-corners" src="<?= get_bloginfo('template_directory');?>/images/achievement-card-decor-inner-corners.svg" alt=""/>
							</div>
						</div>
						<div class="achievement-card-title"> </div>
						<div class="achievement-card-earned"> </div>
						<div class="achievement-card-message"> </div>
						<?php if ($isGM || $isNPC || $isAdmin){ ?>
							<div class="achievement-card-actions" id="achievement-card-actions">
								<?php if($isNPC){ ?>
									<a class="edit-link button form-ui blue-bg-700 font _18 w900" href=""><?= __("Assign","bluerabbit"); ?></a>
								<?php } ?>
								<?php if($isGM || $isAdmin){ ?>
									<a class="edit-link button form-ui green-bg-500 font _18 w900" href=""><?= __("Edit","bluerabbit"); ?></a>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
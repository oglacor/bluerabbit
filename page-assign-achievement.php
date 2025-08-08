<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if($adventure && ($isGM || $isAdmin || $isNPC)){
	$achievement_id = isset($_GET['achievement_id']) ? $_GET['achievement_id'] : 0;
	
	$paths = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE adventure_id=$adv_parent_id AND achievement_display!='badge' AND achievement_status='publish' AND achievement_id != $achievement_id ");
	
	$a = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE achievement_id=$achievement_id AND achievement_status='publish'");
	if(isset($a)){
		$selected_players = $wpdb->get_col("SELECT player_id FROM ".$wpdb->prefix."br_player_achievement WHERE achievement_id=$a->achievement_id AND adventure_id=$adv_child_id");
	}
	?>
		<div class="dashboard">
			<div class="dashboard-content white-bg">
				<div class="w-full padding-10 purple-bg-50">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 purple-bg-400"><span class="icon icon-achievement"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800">
								<?= __("Assign Achievement","bluerabbit"); ?>
							</span>
							<?php if(isset($a)){ ?>
								<input type="hidden" id="the_achievement_id" value="<?= $a->achievement_id; ?>">
							<?php } ?>
						</span>
					</span>
				</div>
				<div class="tabs" id="main-tabs">
					<div class="tab active max-w-900 padding-10">
						<div class="achievements assign-achievement">
							<div class="achievement-element">
								<div class="achievement-badge earned" style="background-image: url(<?= $a->achievement_badge; ?>);">
									<div class="hidden achievement-name">
										<?= $a->achievement_name;?>
									</div>
								</div>
							</div>
							<h1 class="achievement-name"><?= $a->achievement_name;?></h1>
						</div>
						<div class="highlight padding-10 grey-bg-200">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-achievement"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Magic Code","bluerabbit"); ?></span>
									<span class="line font _14 grey-800">
										<?= __("This code can be used by all players.","bluerabbit"); ?><br>
										<?= __("The achievement is assigned only once so it doesn't matter if the player scans or uses the code multiple times.","bluerabbit"); ?><br>
									</span>
								</span>
							</span>
						</div>
						<table class="table w-full" cellpadding="0">
							<thead>
								<tr class="font _12 grey-600">
									<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
									<td><?php _e('Value','bluerabbit'); ?></td>
								</tr>
							</thead>
							<tbody class="font _16">
								<tr>
									<td class="text-right w-150"><?php _e('Magic code','bluerabbit'); ?></td>
									<td>
										<div class="input-group w-full font _30">
											<label class="purple-bg-800 font _30 w900"><span class="icon icon-magic"></span></label>
											<input class="form-ui w-full" type="text" value="<?= isset($a) ? $a->achievement_code : ""; ?>" id="the_achievement_code" readonly>
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-right w-150"><?php _e('Magic link','bluerabbit'); ?></td>
									<td>
										<?php 
											if( isset($a) && $a->achievement_code){ 
												$magicLink = get_bloginfo('url')."/magic-link/?c=$a->achievement_code&adv=$a->adventure_id"; 
											}else{
												$magicLink = '';
											}
										?>
										<input type="hidden" id="site-url" value="<?= get_bloginfo('url')."/magic-link/?&c="; ?>">
										<div class="input-group w-full" id="tutorial-magic-link">
											<label class="teal-bg-400"><span class="icon icon-qr"></span></label>
											<input class="form-ui teal w-full" id="the_magic_link" readonly type="text" value="<?= $magicLink; ?>">
										</div>
									</td>
								</tr>
							</tbody>
						</table>
                        <div class="tabs" id="assign-manually">
                            <div class="tabs-header" id="assign-manually-buttons">
                                <div class="tabs-buttons">
                                    <button onClick="switchTabs('#assign-manually','#players-selection');" class="tab-button active purple-border-400" id="players-selection-tab-button">
                                        <?= __("Select Players","bluerabbit");?>
                                    </button>
                                    <button onClick="switchTabs('#assign-manually','#players-awarded');" class="tab-button purple-border-400" id="players-awarded-tab-button">
                                        <?= __("Awarded","bluerabbit"); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="tab active" id="players-selection">
                                
                                <?php include (TEMPLATEPATH . '/player-select-achievement.php'); ?>
                            </div>
                            <div class="tab" id="players-awarded">
                                <?php
                                $player_ids = implode(",",$selected_players);
                                $players = $wpdb->get_results("
                                SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug FROM {$wpdb->prefix}br_player_adventure a
                                LEFT JOIN {$wpdb->prefix}br_players b 
                                on a.player_id = b.player_id
                                WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' AND a.player_id IN ($player_ids) ORDER BY b.player_email LIMIT 1000

                                ");
                                ?>
                                <div class="highlight padding-10 grey-bg-100" id="tutorial-earned-players">
                                    <span class="icon-group">
                                        <span class="icon-button font _24 sq-40  indigo-bg-400">
                                            <?= count($players); ?>
                                        </span>
                                        <span class="icon-content">
                                            <span class="line font w500 _26"><?php _e('Awarded Players',"bluerabbit"); ?></span>
                                            <span class="line font _14 w300 grey-500"><?php _e('A list of the players that earned the achievement.','bluerabbit'); ?></span>
                                        </span>
                                    </span>
                                </div>
                                <div class="content w-full">
                                    <table class="table compact">
                                        <thead>
                                            <tr>
                                                <td><?php _e("ID","bluerabbit"); ?></td>
                                                <td><?php _e("Name","bluerabbit"); ?></td>
                                                <td><?php _e("Email","bluerabbit"); ?></td>
                                                <td><?php _e("Actions","bluerabbit"); ?></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($players as $play){ ?>
                                                <tr>
                                                    <td><?= $play->player_id; ?></td>
                                                    <td><?= $play->player_first." ".$play->player_last; ?></td>
                                                    <td><?= $play->player_email; ?></td>
                                                    <td id="player-achievement-list-<?=$play->player_id;?>" class="active">
                                                        <button class="active-content form-ui red-bg-400 white-color" onClick="triggerAchievement(<?php echo "$a->achievement_id, $play->player_id"; ?>);">
                                                            <?= __("Remove","bluerabbit"); ?>
                                                        </button>
                                                        <button class="inactive-content form-ui blue-bg-400 white-color" onClick="triggerAchievement(<?php echo "$a->achievement_id, $play->player_id"; ?>);">
                                                            <?= __("Restore","bluerabbit"); ?>
                                                        </button>
                                                    
                                                    
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
</div>

<script> checkPath(); </script>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

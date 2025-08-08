<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container">
		<div class="path-choices white-bg" id="path-choices-<?=$step->step_id;?>">
			<?php 
			$selected_path = $wpdb->get_row("
			SELECT 
			ach.*, player.player_id

			FROM {$wpdb->prefix}br_achievements ach
			JOIN {$wpdb->prefix}br_player_achievement player
			ON ach.achievement_id = player.achievement_id AND player.player_id=$current_user->ID AND player.adventure_id=$step->adventure_id

			WHERE ach.achievement_group='$step->step_achievement_group' AND ach.achievement_status = 'publish' 

			");
			
			$step_paths = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_achievements WHERE adventure_id=$step->adventure_id AND achievement_status='publish' AND achievement_group='$step->step_achievement_group' AND achievement_display='path'"); 
			
			?>
			<?php if($selected_path){ ?>
				<?php 
					$notification = new Notification();
					$msg_content = __("Can't change paths",'bluerabbit')." <br>";
					$msg_content .= __("Already walking",'bluerabbit')."<br><strong>$selected_path->achievement_name</strong>";
					$message = $notification->pop($msg_content,'red','cancel');
				?>
				<div class="hidden fixed" id="locked-path-<?= $step->step_id;?>">
					<?php echo $message; ?>
				</div>
				<?php foreach($step_paths as $p){ ?>
					<button class="path <?= $p->achievement_color;?>-border-400 <?=$p->achievement_id == $selected_path->achievement_id ? 'selected' : '';?> " id="path-<?= $p->achievement_id;?>" style="background-image: url(<?= $p->achievement_badge;?>)" onClick="notification('#locked-path-<?= $step->step_id;?>');">
						<?= $p->achievement_name; ?>
					</button>
				<?php } ?>
			<?php }else{ ?>
				<?php 
					$notification = new Notification();
					$msg_content = __("Must Choose a Path to continue",'bluerabbit')." <br>";
					$message = $notification->pop($msg_content,'deep-purple','journey');
				?>
				<div class="hidden fixed" id="must-choose-<?= $step->step_id;?>">
					<?php echo $message; ?>
				</div>
				<?php foreach($step_paths as $p){ ?>
					<div class="path cursor-pointer relative layer " id="path-<?= $p->achievement_id;?>" onClick="preChoosePath(<?= $step->step_id;?>,<?= $p->achievement_id;?>,'<?= $p->achievement_name;?>');" style="background-image: url(<?= $p->achievement_badge;?>)">
						<span class="layer absolute base perfect-center padding-5 white-bg border rounded-4 grey-800 font _16 special"><?= $p->achievement_name; ?></span>
					</div>
				<?php } ?>
			<?php } ?>
			<input class="selected-path" type="hidden" value="<?= isset($selected_path) ? $selected_path->achievement_id : ""; ?>">
		</div>
		<div class="step-content">
			<div class="step-content-text attach-content-none">
				<h1><?= __("Chosen Path","bluerabbit"); ?>:
					<?php if(isset($selected_path)){ ?>
						<strong class="chosen-path-label"><?= $selected_path->achievement_name; ?></strong>
					<?php }else{ ?>
						<strong class="chosen-path-label"><?= __("None selected","bluerabbit"); ?></strong>
					<?php } ?>
				</h1>
			</div>
			<div class="step-buttons">
				<?php if(isset($selected_path) ){ ?>
					<a class="step-next-button" href="#step-<?= $steps[($i+1)]->step_order; ?>">
						<?= __("Continue","bluerabbit"); ?>
					</a>
				<?php }else{ ?>
					<div class="inline-block relative">
						<button class="form-ui border border-all orange-border-400 orange-400 grey-bg-900 relative" id="last-button" onClick="showOverlay('#confirm-path-<?= $step->step_id; ?>');">
							<?= __("Confirm path","bluerabbit"); ?>
						</button>
						<div class="confirm-action overlay-layer confirm-and-submit" id="confirm-path-<?= $step->step_id; ?>">
							<button class="form-ui orange-bg-800 confirm-path-button" onClick="choosePath(<?= $step->step_id; ?>, <?= $steps[($i+1)]->step_order; ?>)">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40 white-bg">
										<span class="icon icon-journey orange-800"></span>
									</span>
									<span class="icon-content">
										<span class="line white-color font _18 "><?php _e("Choose this path?","bluerabbit"); ?></span>
									</span>
								</span>
							</button>
							<button class="close-confirm icon-button font _14 sq-20  blue-grey-bg-800 white-color" onClick="hideAllOverlay();">
								<span class="icon icon-cancel white-color"></span>
							</button>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

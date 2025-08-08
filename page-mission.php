<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$questID =  $_GET['questID'];
	$m = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adv_parent_id AND quest_id=$questID");

    if($m){  //// MASTER VALIDATION
	$m_reqs = $wpdb->get_results("SELECT 
		a.req_object_id, a.req_type,
		c.quest_type, c.quest_title, c.mech_badge, c.quest_id, c.achievement_id AS quest_achievement_id,
		d.item_name, d.item_badge, d.item_id,
		e.achievement_name, e.achievement_badge, e.achievement_id, e.achievement_color,
		f.achievement_applied

		FROM {$wpdb->prefix}br_reqs a
		LEFT JOIN {$wpdb->prefix}br_quests c
		ON a.req_object_id = c.quest_id AND c.quest_status='publish' AND a.quest_id=$m->quest_id
		LEFT JOIN {$wpdb->prefix}br_items d
		ON a.req_object_id = d.item_id
		LEFT JOIN {$wpdb->prefix}br_achievements e
		ON a.req_object_id = e.achievement_id
		LEFT JOIN {$wpdb->prefix}br_player_achievement f
		ON e.achievement_id = f.achievement_id AND f.player_id=$current_player->player_id

		WHERE a.adventure_id=$adv_parent_id AND a.quest_id=$m->quest_id GROUP BY a.req_object_id
	"); 
		

	$completed = 0;
	if($m_reqs){
		$mission_reqs = array(); $mission_reqs_ids = array();
		foreach($m_reqs as $r){
			$mission_reqs[$r->req_type][]=$r;
			$mission_reqs_ids[$r->req_type][]=$r->req_object_id;
		}
		if(isset($mission_reqs_ids['quest'])){ 
			sort($mission_reqs_ids['quest']);
			$reqs_quests_str = implode(",",$mission_reqs_ids['quest']);
		}else{
			$reqs_quests_str = "";
		}
		if(isset($mission_reqs_ids['item'])){ 
			sort($mission_reqs_ids['item']);
			$reqs_items_str = implode(",",$mission_reqs_ids['item']);
		}else{
			$reqs_items_str = "";
		}
		if(isset($mission_reqs_ids['item'])){ 
			sort($mission_reqs_ids['achievement']);	
			$reqs_achievements_str = implode(",",$mission_reqs_ids['achievement']);
		}else{
			$reqs_achievements_str = "";
		}
		$work = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
		WHERE adventure_id=$adventure->adventure_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_quests_str)");
		sort($work);
		
		$my_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_items a
			JOIN {$wpdb->prefix}br_transactions b
			ON a.item_id = b.object_id AND b.player_id=$current_user->ID
			WHERE a.adventure_id=$adventure_id  AND b.trnx_status='publish' AND a.item_id IN ($reqs_items_str) AND b.trnx_type='key'");
		
		$completed = count($work)+count($my_items);
		if(isset($mission_reqs['achievement'])){
			foreach($mission_reqs['achievement'] as $r){
				if($r->req_type=='achievement' && $r->achievement_applied){
					$completed++;
				}
			}
		}
	}
	$req_ep = 0;
	$objectives = getObjectives($adv_child_id, $m->quest_id);
		
	foreach ($objectives as $key=>$c){ 
		$req_ep+= $c->ep_cost;
		if($c->player_id == $current_user->ID){
			$completed++;
		}
	}
	
	$total_objectives = $objectives ? count($objectives) : 0;
	$abs_total = ($total_objectives+count($m_reqs));
	$progress = round($completed/$abs_total*100); 
		
		
		
?>
	<div class="boxed max-w-1000">
		<div class="highlight text-center">
			<div class="theme-headline amber-400 opacity-50 relative">
				<span class="icon icon-mission foreground"></span>
				<span class="headline-text amber-400 font _12 kerning-3 condensed w100 uppercase relative padding-5 foreground ">
					<?= __("Mission","bluerabbit"); ?>
				</span>
				<?php if($isGM){ ?>
					<a class="form-ui font _12 green-bg-400 padding-5" href="<?= get_bloginfo('url')."/new-mission/?adventure_id=$adv_parent_id&questID=$m->quest_id";?>">
						<span class="icon icon-edit font _10"></span><?= __("Edit","bluerabbit"); ?>
					</a>
				<?php } ?>
			</div>
			<div class="icon-group padding-0 inline-table">
				<span class="icon-content">
					<h1 class="line font _26 w300 amber-400 uppercase">
						<?= $m->quest_title; ?>
					</h1>
					<input type="hidden" id="the_quest_id" value="<?= $m->quest_id; ?>">
				</span>
			</div>
		</div>
		<div class="tabs" id="tab-group">
			<div class="tabs-header white-color padding-0 margin-0">
				<div class="tabs-buttons text-center font _14" id="tab-group-buttons">
					<button onClick="switchTabs('#tab-group','#mission-status');" class="tab-button amber-border-400 white-color active" id="mission-status-tab-button">
						<?= __("Mission Status","bluerabbit");?>
					</button>
					<button onClick="switchTabs('#tab-group','#mission-objectives');" class="tab-button amber-border-400 white-color" id="mission-objectives-tab-button">
						<?= __("Objectives","bluerabbit");?>
					</button>
				</div>
			</div>
			<div class="tab active" id="mission-status">
				<?php if($m->mech_xp > 0 || $m->mech_bloo > 0 ){ ?>
					<div class="highlight padding-10 text-center" id="tutorial-rewards">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  transparent-bg font _28 border-all border amber-400">
								<span class="halo rotate-L-40"></span>
								<span class="icon icon-star "></span>
							</span>
							<span class="icon-content white-color">
								<span class="line font _24 w100 condensed"><?= toMoney($m->mech_xp,""); ?></span>
								<span class="line font _14 w300"><?= $xp_label; ?></span>
							</span>
						</span>
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  transparent-bg font _28 border-all border green-400">
								<span class="halo rotate-R-20"></span>
								<span class="icon icon-bloo "></span>
							</span>
							<span class="icon-content white-color">
								<span class="line font _24 w100 condensed"><?= toMoney($m->mech_bloo,""); ?></span>
								<span class="line font _14 w300"><?= $bloo_label; ?></span>
							</span>
						</span>
						<?php if($use_encounters){ ?>
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  transparent-bg font _28 border-all border green-400">
									<span class="halo rotate-R-20"></span>
									<span class="icon icon-activity "></span>
								</span>
								<span class="icon-content white-color">
									<span class="line font _24 w100 condensed"><?= toMoney($m->mech_ep,""); ?></span>
									<span class="line font _14 w300"><?= $ep_label; ?></span>
								</span>
							</span>
						<?php } ?>
					</div>
				<?php } ?>
				<div class="mission-status-chart overflow-hidden">

					<span class="halo halo-5 rotate-R-120 mix-blend-overlay" style="background-image: url(<?= get_bloginfo('template_directory');?>/images/cyber-button-bg.png);"></span>
					<span class="halo halo-6 rotate-L-40 mix-blend-overlay" style="background-image: url(<?= get_bloginfo('template_directory');?>/images/cyber-button-bg.png);"></span>
					<div class="canvas-container">
						<canvas id="mission-status-chart" width="290" height="290"></canvas>
					</div>
					<span class="icon-button font _24 sq-40  grey-bg-900 icon-lg">
						<span class="icon icon-mission"></span>
						<span class="halo rotate-L-40" style="background-image: url(<?= get_bloginfo('template_directory');?>/images/cyber-button-bg.png);"></span>
						<span class="halo halo-10 rotate-L-10" style="background-image: url(<?= get_bloginfo('template_directory');?>/images/cyber-button-bg.png);"></span>
						<span class="halo halo-5 rotate-R-60" style="background-image: url(<?= get_bloginfo('template_directory');?>/images/cyber-button-bg.png);"></span>
					</span>
					<input type="hidden" id="label-total-value" value="<?= __("Missing","bluerabbit"); ?>">
					<input type="hidden" id="label-current-value" value="<?= __("Completed","bluerabbit"); ?>">
					<input type="hidden" id="color-total-value" value="rgba(141,110,99,0.5)">
					<input type="hidden" id="color-current-value" value="rgba(255,193,7,1.00)">
					<script>
						let completed_objectives = <?= $completed ? $completed : 0; ?>; 
						let total_objectives = <?= $total_objectives-$completed+count($m_reqs); ?>; 
						createProgressionChart(completed_objectives, total_objectives,'#mission-status-chart');
					</script>
					
				</div>
				<?php if($m->quest_content){ ?>
					<?php $textcolor = ($m->quest_color == 'yellow' || $m->quest_color == 'lime') ? 'grey-900' : '';?>
					<div class="w-full text-center" id="mission-brief">
						<h3 class="font _24 w900 uppercase amber-400 padding-10"><?php _e("Mission Brief","bluerabbit");?></h3>
						<?php if($req_ep > 0){ ?>
							<span class="inline-block padding-10 border rounded-max teal-bg-A400 blue-grey-800 font _18 w300">
								<span class="icon icon-warning"></span><?= __("EP","bluerabbit"); ?> <strong class="teal-900 font w900"><?= $req_ep; ?></strong>
							</span>
							<?php if($req_ep > $current_player->player_ep){ ?>
								<button class='form-ui padding-10 teal-bg-A400 border rounded-max grey-900' onClick='loadSidebar(); randomEncounter();'><span class='icon icon-activity'></span> 
									<?= __("RECHARGE","bluerabbit"); ?>
								</button>
							<?php }else{ ?>
								<button onClick="switchTabs('#tab-group','#mission-objectives');" class="form-ui padding-10 <?= $m->quest_color; ?>-bg-400 <?= $textcolor; ?> border rounded-max" id="mission-objectives-tab-button">
									<?= __("Mission Objectives","bluerabbit");?>
								</button>
							<?php } ?>
						<?php } ?>

						<div class="font _20 w300 padding-10 white-color quest-instructions">
							<?= apply_filters('the_content',$m->quest_content); ?>
						</div>
						<div class="text-center padding-10">
							<button onClick="switchTabs('#tab-group','#mission-objectives');" class="form-ui <?= $m->quest_color; ?>-bg-400 <?= $textcolor; ?> white-color" id="mission-objectives-tab-button">
								<?= __("Mission Objectives","bluerabbit");?>
							</button>
						</div>
					</div>
				<?php } ?>
				<?php if($progress >=100 && $m->quest_success_message){ ?>
					<div class="w-full" id="mission-report">
						<h3 class="font _24 w900 uppercase amber-400"><?php _e("Mission Report","bluerabbit");?></h3>
						<div class="font _20 w300 white-color quest-instructions">
							<?= apply_filters('the_content',$m->quest_success_message); ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php if($m_reqs || $objectives){ ?>
				<div class="tab" id="mission-objectives">
					<div class="highlight text-center padding-0 margin-0">
						<div class="inline-table padding-0 text-center">
							<div class="font condensed _14 uppercase text-center">
								<span class="cyan-A200 font kerning-2"><?= __("Progress","bluerabbit"); ?></span><br>
								<span class="white-color opacity-50"><?= "$completed/".($total_objectives+count($m_reqs))." ".__("Completed","bluerabbit"); ?></span>
							</div>
							<span class="progress-bar blue-grey-bg-900 inline-block">
								<span class="progress teal-bg-500" style="width: <?= $progress ? $progress."%" : "0%"; ?>;"></span>
							</span>
						</div>
					</div>
					<div class="w-full">
						<div class="card-deck">
						<?php 
							if($m_reqs){ 
								$my_achievements = getMyAchievements($adventure->adventure_id); 
								foreach($m_reqs as $r){
									$isFinished= $isEarned = false;
									if($r->req_type=="quest"){  
										foreach($work as $w){
											if($r->req_object_id==$w){
												$isFinished = true;
											}
										}
										$mi = $r;
										include (TEMPLATEPATH . '/req-milestone.php');
									}elseif($r->req_type=="item") {
										foreach($my_items as $key_item){
											if($r->req_object_id==$key_item->object_id){
												$playerHasIt = true;
											}
										}
										$mi = $r;
										include (TEMPLATEPATH . '/req-item.php');
									}elseif($r->req_type=="achievement") {
										if($r->achievement_applied){
											$isEarned = true;
										}
										$a = $r;
										include (TEMPLATEPATH . '/req-achievement.php');
									} 
								} 
							}
							if($objectives){
								foreach ($objectives as $key=>$c){
									if($c->objective_type =='keyword-input') {
										$color = 'teal-bg-800';
									}elseif($c->objective_type =='true-false') { 
										$color = 'pink-bg-800';
									}
									$solved = "";
									if($c->player_id == $current_user->ID){ 
										$solved= '-solved';
									}
									?>
									<?php include (TEMPLATEPATH . '/objective-item-'.$c->objective_type.$solved.'.php');?>
								<?php } //end foreach objective ?>
							<?php } //if objectives ?>
						</div>
						<div class="objective-success-messages">
							<?php
							if($objectives){
								foreach ($objectives as $key=>$c){
									if($c->player_id == $current_user->ID){
										include (TEMPLATEPATH . '/objective-item-success-message.php'); 
									}
								} //end foreach objective
							} //if objectives 
							?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<input type="hidden" id="purchase-nonce" value="<?php echo wp_create_nonce('br_item_nonce'); ?>"/>

<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
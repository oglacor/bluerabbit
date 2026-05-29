<div class="journey journey-map" id="the-journey">
	<?php
	$today = date('YmdHi');
	$hide_quests = $adventure->adventure_hide_quests ? $adventure->adventure_hide_quests : 'never';
	$posID = 0;
	$row = 1;
	$counter = 0;

	$tabis = getTabis($adv_parent_id);

	// Group tabi-assigned quests by tabi_id; free quests stay on the map
	$tabi_quest_map = [];
	foreach($all_quests as $q) {
		if($q->tabi_id) {
			$tabi_quest_map[$q->tabi_id][] = $q;
		}
	}

	// Tabi locking — GMs and admins bypass all locks
	$tabi_prereq_map  = getTabiPrerequisitesMap($adv_parent_id);
	$completed_tabis  = ($isGM || $isAdmin || $isNPC) ? [] : getCompletedTabiIds($adv_parent_id, $current_player->player_id);
	// Build locked map: tabi_id => bool
	$tabi_locked = [];
	if($tabis) {
		foreach($tabis as $t) {
			if($isGM || $isAdmin || $isNPC) {
				$tabi_locked[$t->tabi_id] = false;
			} elseif(!empty($tabi_prereq_map[$t->tabi_id])) {
				$missing = array_diff($tabi_prereq_map[$t->tabi_id], $completed_tabis);
				$tabi_locked[$t->tabi_id] = !empty($missing);
			} else {
				$tabi_locked[$t->tabi_id] = false;
			}
		}
	}

	foreach($all_quests as $key=>$mi){
		if($mi->tabi_id) continue; // tabi quests are shown inside tabi modals, not on the map
	?>
		<?php
        $scaleVal = ($mi->milestone_z > 5) ? 5 : $mi->milestone_z;
        $scaleVal = $scaleVal < 1 ? 1 : $scaleVal;
        $baseWidth = 108;
        $baseHeight = 95;
        $scaledWidth = $baseWidth * $scaleVal;
        $scaledHeight = $baseHeight * $scaleVal;
        if($scaleVal !=1){
            $scale = 'width: '.$scaledWidth.'px; height: '.$scaledHeight.'px;';
        }else{
            $scale = '';
        }

		?>

		<?php 
		$hideByDay = '';
		if($mi->quest_type != 'blog-post' && $mi->quest_type != 'lore'){

            if(!$mi->milestone_left || !$mi->milestone_top){
                $miTop = 350; $miLeft = 350;
            }else{
                $miTop = $mi->milestone_top;
                $miLeft = $mi->milestone_left;  
            }


		?>

		<div class="milestone-container" id="milestone-container-<?= $mi->quest_id; ?>" style="top:<?=$miTop; ?>px; left:<?=$miLeft; ?>px; order:<?=$mi->quest_order; ?>">
			<?php
				if($hide_quests=='before'){
					if($mi->mech_start_date != '0000-00-00 00:00:00' && $mi->mech_start_date != NULL){
						$date = date('YmdHi',strtotime($mi->mech_start_date));
						if($today < $date){
							$hideByDay = 'hidden';
						}
					}
				}elseif($hide_quests=='after'){
					if($mi->mech_deadline != '0000-00-00 00:00:00' && $mi->mech_deadline != NULL){
						$date = date('YmdHi',strtotime($mi->mech_deadline));
						if($today > $date){
							$hideByDay = 'hidden';
						}
					}
				}elseif($hide_quests=='both'){  
					if(($mi->mech_start_date != '0000-00-00 00:00:00' && $mi->mech_start_date != NULL) || ($mi->mech_deadline != '0000-00-00 00:00:00' && $mi->mech_deadline != NULL)){
						$start = date('YmdHi',strtotime($mi->mech_start_date));
						$end = date('YmdHi',strtotime($mi->mech_deadline));
						if($today < $start || $today > $end){
							$hideByDay = 'hidden';
						}
					}
				}
				if(($isAdmin || $isGM || $isNPC) && $hide_quests != 'never' && $hideByDay== 'hidden'){
					$hideByDay = 'opacity-60';
				}
				if($hideByDay !='hidden'){
					$elementID = $mi->quest_id;
					if($counter==0){
						$counter++;
						$first_milestone_for_tutorial = $elementID;
					}
					$permalink = get_bloginfo('url')."/$mi->quest_type/?questID=$mi->quest_id&adventure_id=$adv_child_id";
					if(in_array($mi->achievement_id, $player_achievements) || !$mi->achievement_id){ 
						if(in_array($mi->quest_id, $player['fqs'])){
							include (TEMPLATEPATH . '/milestone-finished.php');
						}else{
							if($current_player->player_level < $mi->mech_level){ 
								include (TEMPLATEPATH . '/milestone-levelup.php');
							}else{ 
								if($mi->mech_unlock_cost > 0 && !in_array($mi->quest_id,$player['unlocks'])){
									include (TEMPLATEPATH . '/milestone-unlock.php');
								}else{ 
									if($today < date('YmdHi',strtotime($mi->mech_start_date))){
										include (TEMPLATEPATH . '/milestone-startdate.php');
									}else{
										if($mi->mech_deadline != '0000-00-00 00:00:00' && $mi->mech_deadline != NULL && $today > date('YmdHi',strtotime($mi->mech_deadline )) && $mi->mech_deadline_cost <= 0){ 
											include (TEMPLATEPATH . '/milestone-deadline.php');
										}elseif($mi->mech_deadline != '0000-00-00 00:00:00' && $mi->mech_deadline != NULL && $today > date('YmdHi',strtotime($mi->mech_deadline)) && $mi->mech_deadline_cost > 0 && !in_array($mi->quest_id,$player['deadlines'])) {
											include (TEMPLATEPATH . '/milestone-deadline-cost.php');
										}else{
											if(!empty($player['fqs']) && isset($reqs_ids[$mi->quest_id])){
												$reqs_finished = $player['fqs'] ? array_intersect($player['fqs'], $reqs_ids[$mi->quest_id]) : 0;
												if($reqs_finished != $reqs_ids[$mi->quest_id]){
													include (TEMPLATEPATH . '/milestone-requirements.php');
												}else{
													$allReqs = true;
												}
											}else{
												$allReqs = true;
											}
											if($allReqs){
												if($player['debt']<=0){

													include (TEMPLATEPATH . '/milestone.php');
												}else{
													include (TEMPLATEPATH . '/milestone-blocked.php');
												}//has debt
											}//hasReqs
										}//isAlive
									} //isOpen
								} //isUnlockable
							} //isLevel
						}
					}else{
						include (TEMPLATEPATH . '/milestone-unavailable.php');
					} //isLevel
				}
			?>
			</div>
		<?php }	?>

	<?php } //end foreach; ?>

	<?php // Render tabi nodes on the map
	if($tabis) { foreach($tabis as $t) {
		$tNodeTop   = $t->tabi_top  ?: 350;
		$tNodeLeft  = $t->tabi_left ?: 350;
		$questCount = isset($tabi_quest_map[$t->tabi_id]) ? count($tabi_quest_map[$t->tabi_id]) : 0;
		$isLocked   = $tabi_locked[$t->tabi_id] ?? false;

		// Build names of required tabis that are still incomplete (for tooltip)
		$lock_label = '';
		if($isLocked && !empty($tabi_prereq_map[$t->tabi_id])) {
			$req_names = [];
			foreach($tabis as $rt) {
				if(in_array($rt->tabi_id, $tabi_prereq_map[$t->tabi_id]) && !in_array($rt->tabi_id, $completed_tabis)) {
					$req_names[] = esc_html($rt->tabi_name);
				}
			}
			$lock_label = implode(', ', $req_names);
		}
		?>
		<div class="tabi-node <?= esc_attr($t->tabi_color); ?> <?= $isLocked ? 'locked' : ''; ?>"
			 id="tabi-node-<?= $t->tabi_id; ?>"
			 data-tabi-id="<?= $t->tabi_id; ?>"
			 data-locked="<?= $isLocked ? '1' : '0'; ?>"
			 data-lock-label="<?= esc_attr($lock_label); ?>"
			 style="top:<?= $tNodeTop; ?>px; left:<?= $tNodeLeft; ?>px;"
			 onclick="openTabiModal(<?= $t->tabi_id; ?>)">
			<div class="tabi-node-icon">
				<?php if($isLocked) { ?><span class="icon icon-lock"></span><?php } else { ?><span class="icon icon-tabi"></span><?php } ?>
			</div>
			<div class="tabi-node-name"><?= esc_html($t->tabi_name); ?></div>
			<div class="tabi-node-count"><?= $isLocked ? __('Locked','bluerabbit') : $questCount.' '.__('quests','bluerabbit'); ?></div>
		</div>
	<?php } } ?>
</div>
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


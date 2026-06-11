<div class="journey journey-board" id="the-journey">
	<?php
	$today        = date('YmdHi');
	$hide_quests  = $adventure->adventure_hide_quests ? $adventure->adventure_hide_quests : 'never';
	$counter      = 0;
	$current_tabi = -1; // sentinel — no column open yet
	?>

	<?php foreach($all_quests as $key=>$mi): ?>
		<?php
		// Normalise: treat 0 and NULL as "no tabi"
		$mi_tabi_id   = ($mi->tabi_id && $mi->tabi_id > 0) ? (int) $mi->tabi_id : 0;
		$mi_tabi_name = ($mi_tabi_id && $mi->tabi_name) ? $mi->tabi_name : '';
		?>

		<?php if($mi_tabi_id !== $current_tabi): ?>
			<?php if($current_tabi !== -1): ?></div><?php endif; ?>
			<div class="board-view-column <?= $mi_tabi_id ? 'tabi-'.esc_attr($mi_tabi_id) : 'no-tabi'; ?> <?= esc_attr($mi->quest_color); ?>">
				<div class="board-column-header">
					<?php if($mi_tabi_name): ?>
						<span class="board-column-title"><?= esc_html($mi_tabi_name); ?></span>
					<?php else: ?>
						<span class="board-column-title no-tabi-label"><?= __('Other','bluerabbit'); ?></span>
					<?php endif; ?>
				</div>
			<?php $current_tabi = $mi_tabi_id; ?>
		<?php endif; ?>

		<?php if($mi->quest_type != 'blog-post' && $mi->quest_type != 'lore'): ?>
		<?php
		$hideByDay = '';
		?>

		<div class="milestone-container" id="milestone-container-<?= $mi->quest_id; ?>" style="top:<?=$mi->milestone_top; ?>px; left:<?=$mi->milestone_left; ?>px; transform:<?=$scale ?? '';?> rotate(<?= $mi->milestone_rotation;?>deg); order:<?=$mi->quest_order; ?>">
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
		<?php endif; ?>

	<?php endforeach; ?>

	<?php if($current_tabi !== -1): ?></div><?php endif; ?>
</div>

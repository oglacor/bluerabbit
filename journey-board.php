<div class="journey journey-board" id="the-journey">
	<?php
	$today        = date('YmdHi');
	$hide_quests  = $adventure->adventure_hide_quests ? $adventure->adventure_hide_quests : 'never';
	$counter      = 0;
	$current_tabi = -1; // sentinel — no section open yet

	// Pre-count required milestones vs side quests per tabi section, for the headline
	$section_required = []; // tid => ['done'=>n, 'total'=>n]
	$section_side     = []; // tid => ['done'=>n, 'total'=>n]
	foreach($all_quests as $mi){
		if($mi->quest_type == 'blog-post' || $mi->quest_type == 'lore') continue;
		$tid    = ($mi->tabi_id && $mi->tabi_id > 0) ? (int) $mi->tabi_id : 0;
		$bucket = $mi->mech_optional ? 'section_side' : 'section_required';
		$is_done = in_array($mi->quest_id, $player['fqs']);
		if($bucket == 'section_side'){
			$section_side[$tid]['total'] = ($section_side[$tid]['total'] ?? 0) + 1;
			if($is_done) $section_side[$tid]['done'] = ($section_side[$tid]['done'] ?? 0) + 1;
		}else{
			$section_required[$tid]['total'] = ($section_required[$tid]['total'] ?? 0) + 1;
			if($is_done) $section_required[$tid]['done'] = ($section_required[$tid]['done'] ?? 0) + 1;
		}
	}

	// Which tabis are fully completed (required milestones only — matches BR_Tabi unlock rules)
	$completed_tabis = ($isGM || $isAdmin || $isNPC) ? [] : BR_Tabi::instance()->getCompletedTabiIds($adv_parent_id, $current_player->player_id);
	?>

	<?php foreach($all_quests as $key=>$mi): ?>
		<?php
		// Normalise: treat 0 and NULL as "no tabi"
		$mi_tabi_id    = ($mi->tabi_id && $mi->tabi_id > 0) ? (int) $mi->tabi_id : 0;
		$mi_tabi_name  = ($mi_tabi_id && $mi->tabi_name) ? $mi->tabi_name : '';
		$mi_tabi_color = $mi_tabi_id && $mi->tabi_color_name ? $mi->tabi_color_name : 'blue-grey';
		?>

		<?php if($mi_tabi_id !== $current_tabi): ?>
			<?php if($current_tabi !== -1): ?></div></div><?php endif; ?>
			<?php
			$is_tabi_completed = $mi_tabi_id && in_array($mi_tabi_id, $completed_tabis);
			$req_done  = $section_required[$mi_tabi_id]['done']  ?? 0;
			$req_total = $section_required[$mi_tabi_id]['total'] ?? 0;
			$side_done  = $section_side[$mi_tabi_id]['done']  ?? 0;
			$side_total = $section_side[$mi_tabi_id]['total'] ?? 0;
			$headline_color = $is_tabi_completed ? 'br-green' : $mi_tabi_color;
			?>
			<div class="board-section <?= $mi_tabi_id ? 'tabi-'.esc_attr($mi_tabi_id) : 'no-tabi'; ?>">
				<div class="board-section-headline <?= $is_tabi_completed ? 'completed' : ''; ?>" style="<?= esc_attr(br_color_style($headline_color, 'border-left-color')); ?>">
					<span class="icon icon-<?= $is_tabi_completed ? 'check' : ($mi_tabi_id ? 'sabotage' : 'journey'); ?>"></span>
					<?php if($mi_tabi_name): ?>
						<span class="board-section-title"><?= esc_html($mi_tabi_name); ?></span>
					<?php else: ?>
						<span class="board-section-title no-tabi-label"><?= __('Other Milestones','bluerabbit'); ?></span>
					<?php endif; ?>
					<span class="board-section-stats">
						<?php if($req_total > 0){ ?>
							<span class="board-section-stat"><?= __('Required Milestones','bluerabbit'); ?>: <?= $req_done; ?> / <?= $req_total; ?></span>
						<?php } ?>
						<?php if($req_total > 0 && $side_total > 0){ ?>
							<span class="board-section-stat-divider">|</span>
						<?php } ?>
						<?php if($side_total > 0){ ?>
							<span class="board-section-stat"><?= __('Side Quests','bluerabbit'); ?>: <?= $side_done; ?> / <?= $side_total; ?></span>
						<?php } ?>
					</span>
				</div>
				<div class="board-section-body">
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
					// Single source of truth for milestone state (see BR_Progression::resolveMilestoneTemplate) -
					// keeps Board view's gating identical to Map view's, instead of a second, drifting copy.
					$miTemplate = BR_Progression::instance()->resolveMilestoneTemplate($mi, $player, $current_player->player_level, $player_achievements, $reqs_ids, $today);
					include (TEMPLATEPATH . "/$miTemplate.php");
				}
			?>
			</div>
		<?php endif; ?>

	<?php endforeach; ?>

	<?php if($current_tabi !== -1): ?></div></div><?php endif; ?>
</div>

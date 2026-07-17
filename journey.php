<div class="journey journey-map" id="the-journey">
	<?php
	$today = date('YmdHi');
	$hide_quests = $adventure->adventure_hide_quests ? $adventure->adventure_hide_quests : 'never';
	$posID = 0;
	$row = 1;
	$counter = 0;

	$tabis = BR_Tabi::instance()->getTabis($adv_parent_id);

	// Group tabi-assigned quests by tabi_id; free quests stay on the map
	$tabi_quest_map = [];
	foreach($all_quests as $q) {
		if($q->tabi_id) {
			$tabi_quest_map[$q->tabi_id][] = $q;
		}
	}

	// Tabi locking — GMs and admins bypass all locks
	$tabi_prereq_map  = BR_Tabi::instance()->getTabiPrerequisitesMap($adv_parent_id);
	$tabi_reqs_map    = BR_Tabi::instance()->getTabiReqsMap($adv_parent_id);
	$completed_tabis  = ($isGM || $isAdmin || $isNPC) ? [] : BR_Tabi::instance()->getCompletedTabiIds($adv_parent_id, $current_player->player_id);
	// Build locked map: tabi_id => bool (tabi-to-tabi prereqs, quest/achievement/key-item
	// reqs, and threshold conditions - see BR_Tabi::isTabiLocked)
	$tabi_locked = [];
	if($tabis) {
		foreach($tabis as $t) {
			if($isGM || $isAdmin || $isNPC) {
				$tabi_locked[$t->tabi_id] = false;
			} else {
				$tabi_locked[$t->tabi_id] = BR_Tabi::instance()->isTabiLocked($t->tabi_id, $adv_parent_id, $tabi_prereq_map, $completed_tabis, $tabi_reqs_map, $conditions_snapshot);
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
					$miTemplate = BR_Progression::instance()->resolveMilestoneTemplate($mi, $player, $current_player->player_level, $player_achievements, $reqs_ids, $today, $adv_parent_id, $conditions_snapshot);
					include (TEMPLATEPATH . "/$miTemplate.php");
				}
			?>
			</div>
		<?php }	?>

	<?php } //end foreach; ?>

	<?php // Render journey graphic assets (display only — no controls)
	$journey_assets = BR_Tabi::instance()->getJourneyAssets($adv_parent_id);
	// Pre-query leaderboard data if any leaderboard widget exists
	$_lb_players = null;
	if($journey_assets) {
		foreach($journey_assets as $_ja) {
			if(($_ja->asset_type ?? 'graphic') === 'widget-leaderboard') {
				$_lb_limit = isset($leaderboard_limit) && $leaderboard_limit ? $leaderboard_limit : 5;
				$_lb_players = $wpdb->get_results("
					SELECT a.player_level, b.player_display_name, b.player_picture
					FROM {$wpdb->prefix}br_player_adventure a
					LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id=b.player_id
					WHERE a.adventure_id={$adventure->adventure_id}
					AND a.player_adventure_status='in' AND a.player_adventure_role='player'
					GROUP BY a.player_id ORDER BY a.player_xp DESC, a.player_level DESC LIMIT $_lb_limit
				");
				break;
			}
		}
	}
	if($journey_assets) { foreach($journey_assets as $ja) {
		$_ja_type = $ja->asset_type ?? 'graphic';
		$_ja_link = $ja->asset_link ?? '';
		$_ja_tabi_id   = intval($ja->tabi_id ?? 0);
		$_ja_tabi_locked = $_ja_tabi_id && !empty($tabi_locked[$_ja_tabi_id]);
		$_ja_extra_style = $_ja_tabi_locked ? ' filter:grayscale(100%); pointer-events:none;' : '';
	?>
		<div class="journey-asset<?= $_ja_type !== 'graphic' ? ' journey-asset-widget' : ''; ?><?= $_ja_tabi_locked ? ' unavailable' : ''; ?> <?php if($_ja_link): ?>clickable<?php endif; ?>"
		     id="journey-asset-<?= $ja->asset_id; ?>"
		     style="top:<?= $ja->asset_top; ?>px; left:<?= $ja->asset_left; ?>px; width:<?= $ja->asset_width; ?>px; z-index:<?= $ja->asset_z; ?>; transform:rotate(<?= $ja->asset_rotation; ?>deg);<?= $_ja_extra_style; ?>">
			<?php if($_ja_link): ?><a href="<?= esc_url($_ja_link); ?>" target="_blank" rel="noopener" class="journey-asset-link"><?php endif; ?>

			<?php if($_ja_type === 'widget-status'): ?>
				<?php if(isset($current_player) && isset($adventure)): ?>
				<div class="journey-widget journey-widget-status" onClick="activate('#profile-box');">
                    <div class="player-info">
                        <h1 class="font _18 w300 white-color">
                            <?= $current_player->player_display_name; ?>
                        </h1>
                    </div>
   					<div class="status" <?php if($current_player->player_picture != ''){ ?>style="background-image: url(<?= $current_player->player_picture; ?>);"<?php } ?> id="status-animated-chart">
						<a href="<?= get_bloginfo('url')."/my-account/"; ?>" class="relative block">
							<img class="rotate-L-20" src="<?= get_bloginfo('template_directory')."/images/4.png";?>">
							<img class="rotate-R-30" src="<?= get_bloginfo('template_directory')."/images/3.png";?>">
							<?php if(isset($adventure) && isset($current_player->player_level)){ ?>
								<?php if($current_player->player_level > 11){ $level_img = 'max';}else{$level_img = $current_player->player_level;} ?>
								<img class="rotate-L-90" src="<?= get_bloginfo('template_directory')."/images/level-$level_img.png";?>">
							<?php } ?>
							<img class="rotate-L-40" src="<?= get_bloginfo('template_directory')."/images/2.png";?>">
							<img class="rotate-R-120" src="<?= get_bloginfo('template_directory')."/images/1.png";?>">
						</a>
					</div>
                    <div class="status-stats">
                        <div class="stat w-full">
                            <div class="stat-legend font _14">
                                <div class="left-legend w-half text-left pull-left uppercase font w900">
                                    <span class="icon icon-star"></span> <?= $xp_label; ?>
                                </div>
                                <div class="right-legend w-third text-right pull-right">
                                    <strong><?= BR_Utils::instance()->toMoney($current_player->player_xp); ?></strong> <span class="font condensed kerning-1"> / <?= BR_Utils::instance()->toMoney($nextLevel); ?></span>
                                </div>
                            </div>
                            <div class="progress-bar gradient-xp-bar relative w-full">
                                <div class="progress layer base black-bg opacity-60" style="width: <?= 100-round($percXP,3); ?>%"></div>
                            </div>
                        </div>
                        <div class="stat w-full">
                            <div class="stat-legend font _14 padding-5">
                                <div class="left-legend w-half text-left pull-left uppercase font w900">
                                    <span class="icon icon-bloo"></span> <?= $bloo_label; ?>
                                </div>
                                <div class="right-legend w-third text-right pull-right">
                                    <strong><?= BR_Utils::instance()->toMoney($player['bloo'],"$"); ?></strong> /
                                    <strong><?= BR_Utils::instance()->toMoney($player['totalEarned'],"$"); ?></strong> <span class="font condensed kerning-1"><?= __("earned","bluerabbit"); ?></span>
                                </div>
                            </div>
                            <div class="progress-bar relative w-full">
                                <div class="layer background absolute sq-full white-bg opacity-10"></div>
                                <div class="progress layer base light-green-bg-400 border rounded-max" style="width: <?= round($percBLOO,3); ?>%"></div>
                            </div>
                        </div>
                        <?php if($use_encounters && isset($adventure)): ?>
                        <div class="stat w-full">
                            <div class="stat-legend font _14 padding-5">
                                <div class="left-legend w-half text-left pull-left uppercase font w900">
                                    <span class="icon icon-activity"></span> <?= $ep_label; ?>
                                </div>
                                <div class="right-legend w-third text-right pull-right">
                                    <strong><?= $current_player->player_ep; ?></strong> / <span class="font condensed kerning-1"><?= $maxEP; ?></span>
                                </div>
                            </div>
                            <div class="progress-bar relative w-full">
                                <div class="layer background absolute sq-full white-bg opacity-10"></div>
                                <div class="progress layer base cyan-bg-A400 border rounded-max" style="width: <?= round($percEP,3); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                
				</div>
				<?php endif; ?>

			<?php elseif($_ja_type === 'widget-leaderboard'): ?>
				<div class="journey-widget journey-widget-leaderboard">
					<h3 class="font _14 w900 white-color uppercase text-center"><?= __("Leaderboard","bluerabbit"); ?></h3>
					<?php if($_lb_players): ?>
					<table class="w-full">
						<tbody>
						<?php foreach($_lb_players as $_lk => $_lp): ?>
							<tr>
								<td class="font _16 w900 yellow-bg-400 black-color text-center br-lb-rank-cell"><?= $_lk+1; ?></td>
								<td class="br-lb-name-cell">
									<?php if($_lp->player_picture): ?>
									<span class="button-icon sq-24 br-lb-avatar-inline" style="background-image: url(<?= esc_url($_lp->player_picture); ?>)"></span>
									<?php endif; ?>
									<span class="font _13"><?= esc_html($_lp->player_display_name); ?></span>
								</td>
								<td class="text-center font _14 w900 purple-400 br-lb-level-cell">Lv<?= $_lp->player_level; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php endif; ?>
				</div>

			<?php else: ?>
				<?php if($ja->asset_image): ?>
				<img src="<?= esc_url($ja->asset_image); ?>" alt="" draggable="false">
				<?php endif; ?>
			<?php endif; ?>

			<?php if($_ja_link): ?></a><?php endif; ?>
		</div>
	<?php } } ?>

	<?php // Render tabi nodes on the map — only tabis flagged as categories
	if($tabis) { foreach($tabis as $t) {
		if(!$t->tabi_as_category) continue;
		$tNodeTop   = $t->tabi_top  ?: 350;
		$tNodeLeft  = $t->tabi_left ?: 350;
		$tNodeWidth  = ($t->tabi_width  && $t->tabi_width  >= 80 && $t->tabi_width  <= 600) ? $t->tabi_width  : 160;
		$tNodeHeight = ($t->tabi_height && $t->tabi_height >= 60 && $t->tabi_height <= 600) ? $t->tabi_height : 100;
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
		<div class="tabi-node <?= esc_attr($t->tabi_color); ?> <?= $isLocked ? 'locked unavailable' : ''; ?>"
			 id="tabi-node-<?= $t->tabi_id; ?>"
			 data-tabi-id="<?= $t->tabi_id; ?>"
			 data-locked="<?= $isLocked ? '1' : '0'; ?>"
			 data-lock-label="<?= esc_attr($lock_label); ?>"
			 style="top:<?= $tNodeTop; ?>px; left:<?= $tNodeLeft; ?>px; width:<?= $tNodeWidth; ?>px; height:<?= $tNodeHeight; ?>px; background-image: url('<?= esc_url($t->tabi_background); ?>');"
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


// Prevent browser pull-to-refresh and swipe-back on all mobile browsers
document.documentElement.style.overflow = 'hidden';
document.documentElement.style.overscrollBehavior = 'none';
document.body.style.overflow = 'hidden';
document.body.style.overscrollBehavior = 'none';

// Convert legacy translateZ zoom level to 2D scale: scale = 1000 / (1000 - z)
var legacyZoom = <?= intval($journey_zoom_level); ?>;
journeyState.scale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, 1000 / (1000 - legacyZoom)));
resizeJourneyMapWithPadding(300, 'the-journey', '.milestone-container, .journey-asset, .tabi-node');
centerJourneyMap();

// Zoom controls
$('#zoom-in').on('click', function () {
	changeScale(1.15, viewportCenterX(), viewportCenterY());
});

$('#zoom-out').on('click', function () {
	changeScale(0.87, viewportCenterX(), viewportCenterY());
});

$('#zoom-reset').on('click', function () {
	journeyState = { x: 0, y: 0, scale: 1 };
	centerJourneyMap();
});
document.addEventListener('DOMContentLoaded', function () {

    var _tabiHash = window.location.hash.match(/^#tabi-(\d+)$/);
    if (_tabiHash) openTabiModal(parseInt(_tabiHash[1], 10));
});

</script>


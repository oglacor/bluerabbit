<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if(isset($isGM) || isset($isNPC) || isset($isAdmin)){ ?>
			<?php
			if(isset($_GET['order'])){
				if($_GET['order'] == 'xp'){
					$order=" ORDER BY a.player_xp DESC";
				}elseif($_GET['order'] == 'bloo'){
					$order=" ORDER BY a.player_bloo DESC";
				}elseif($_GET['order'] == 'level'){
					$order=" ORDER BY a.player_level DESC";
				}elseif($_GET['order'] == 'name'){
					$order=" ORDER BY b.player_last ASC";
				}elseif($_GET['order'] == 'gpa'){
					$order=" ORDER BY a.player_gpa DESC";
				}elseif($_GET['order'] == 'login'){
					$order=" ORDER BY a.player_last_login DESC";
				}
			}else{
				$order=" ORDER BY a.player_id ASC";
			}
			if(isset($_GET['roles']) && ($_GET['roles'] == 'all')){
				$player_roles = '';
			}else{
				$player_roles = "AND a.player_adventure_role='player'";
			}
			$players = $wpdb->get_results("
				SELECT
				a.*,
				b.player_first, b.player_last, b.player_nickname,
				b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad
				FROM {$wpdb->prefix}br_player_adventure a
				LEFT JOIN {$wpdb->prefix}br_players b
				ON a.player_id=b.player_id
				WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' $player_roles
				GROUP BY a.player_id $order
			");

			$player_posts = $wpdb->get_results("
			SELECT
			a.player_id, a.quest_id, a.adventure_id, a.pp_grade
			FROM {$wpdb->prefix}br_player_posts a
			LEFT JOIN {$wpdb->prefix}br_players b
			ON a.player_id=b.player_id
			WHERE a.adventure_id=$adventure->adventure_id

			UNION

			SELECT
			a.player_id, a.quest_id, a.adventure_id, a.attempt_grade
			FROM {$wpdb->prefix}br_challenge_attempts a
			LEFT JOIN {$wpdb->prefix}br_players b
			ON a.player_id=b.player_id
			WHERE a.adventure_id=$adventure->adventure_id AND a.attempt_status='success' ");

			$player_achievements = $wpdb->get_results("

			SELECT
			a.player_id, a.achievement_id, a.adventure_id
			FROM {$wpdb->prefix}br_player_achievement a
			WHERE a.adventure_id=$adventure->adventure_id
			");

			$player_post_by_id = array();
			$player_achievements_by_id = array();
			foreach($player_posts as $pp){
				$player_post_by_id[$pp->quest_id][$pp->player_id]['p_id']= $pp->player_id;
				$player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->pp_grade;
				if(isset($pp->attempt_grade) && $pp->attempt_grade){
					if($pp->attempt_grade > $player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->pp_grade){
						$player_post_by_id[$pp->quest_id][$pp->player_id]['grade']= $pp->attempt_grade;
					}
				}
			}
			foreach($player_achievements as $pp){
				$player_achievements_by_id[$pp->achievement_id][$pp->player_id] = $pp->player_id;
			}
			$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests
			WHERE adventure_id=$adv_parent_id AND quest_type IN ('quest','challenge','mission','survey') AND quest_status='publish' ORDER BY quest_order
			");

			$achievements = BR_Achievement::instance()->getAchievements($adv_parent_id);
			$achievements = isset($achievements['publish']) ? $achievements['publish'] : NULL;

			$grade_scale = $adventure->adventure_grade_scale;
			?>

<div class="br-page">

	<div class="br-panel br-page-header">
		<div>
			<h1 class="br-page-title"><?= __("Players", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?> &middot; <?= count($players); ?> <?= count($players) == 1 ? __("player", "bluerabbit") : __("players", "bluerabbit"); ?></span>
		</div>
		<div class="br-actions br-gap-6">
			<?php if (isset($_GET['roles']) && $_GET['roles'] == 'all') { ?>
			<a class="br-btn br-btn-mini br-btn-blue" href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id"; ?>"><?= __("Hide GMs & NPCs", "bluerabbit"); ?></a>
			<?php } else { ?>
			<a class="br-btn br-btn-mini" href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&roles=all"; ?>"><?= __("Show GMs & NPCs", "bluerabbit"); ?></a>
			<?php } ?>
			<button type="button" class="br-btn br-btn-green br-btn-mini" onClick="brExportRosterCsv();"><span class="icon icon-check"></span> <?= __("Export CSV", "bluerabbit"); ?></button>
		</div>
	</div>

	<div class="br-panel">
		<input type="text" id="roster-search" class="br-input br-w-full" placeholder="<?= esc_attr__("Search by name or email...", "bluerabbit"); ?>" onKeyup="brFilterRoster();" style="margin-bottom:16px">

		<div class="br-table-scroll br-roster-scroll">
			<table class="br-table br-roster-table" id="roster-table">
				<thead>
					<tr>
						<th class="br-roster-name-cell">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=name" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'name') ? 'br-icon-primary' : ''; ?>"><?= __("Name", "bluerabbit"); ?></a>
						</th>
						<?php if ($config['use_hexad']['value'] > 0) { ?>
						<th class="text-center"><span class="icon icon-hexad"></span></th>
						<?php } ?>
						<th class="text-center">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=level" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'level') ? 'br-icon-primary' : ''; ?>"><?= __("Level", "bluerabbit"); ?></a>
						</th>
						<th class="text-center">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=xp" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'xp') ? 'br-icon-primary' : ''; ?>"><?= $xp_label; ?></a>
						</th>
						<th class="text-center">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=bloo" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'bloo') ? 'br-icon-primary' : ''; ?>"><?= $bloo_label; ?></a>
						</th>
						<?php if ($grade_scale == 'percentage' || $grade_scale == 'letters') { ?>
						<th class="text-center">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=gpa" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'gpa') ? 'br-icon-primary' : ''; ?>"><?= __("GPA", "bluerabbit"); ?></a>
						</th>
						<?php } ?>
						<th class="text-center">
							<a href="<?= get_bloginfo('url') . "/players/?adventure_id=$adventure->adventure_id&order=login" . (isset($_GET['roles']) && $_GET['roles'] == 'all' ? '&roles=all' : ''); ?>" class="<?= (isset($_GET['order']) && $_GET['order'] == 'login') ? 'br-icon-primary' : ''; ?>"><?= __("Last Login", "bluerabbit"); ?></a>
						</th>
						<?php if ($isAdmin) { ?><th class="text-center"><?= __("Refresh", "bluerabbit"); ?></th><?php } ?>
						<?php foreach ($quests as $q) { ?>
						<th class="br-roster-col-header" title="<?= esc_attr($q->quest_title); ?>">
							<?php if ($q->mech_badge) { ?><img src="<?= esc_url($q->mech_badge); ?>" alt=""><?php } else { ?><span class="icon icon-<?= esc_attr($q->quest_type); ?>"></span><?php } ?>
						</th>
						<?php } ?>
						<?php foreach ($achievements as $a) { ?>
						<th class="br-roster-col-header" title="<?= esc_attr($a->achievement_name . ' — ' . $a->achievement_xp . ' ' . $xp_label . ' | ' . $a->achievement_bloo . ' ' . $bloo_label); ?>">
							<?php if ($a->achievement_badge) { ?><img src="<?= esc_url($a->achievement_badge); ?>" alt=""><?php } else { ?><span class="icon icon-achievement"></span><?php } ?>
						</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($players as $p) {
						$roleClass = $p->player_adventure_role == 'gm' ? 'br-roster-role-gm' : ($p->player_adventure_role == 'npc' ? 'br-roster-role-npc' : '');
						$displayName = $p->player_nickname ?: ($p->player_first ? $p->player_first . " " . $p->player_last : $p->player_email);
						$searchBlob = strtolower($displayName . ' ' . $p->player_email);
					?>
					<tr class="roster-row" data-search="<?= esc_attr($searchBlob); ?>">
						<td class="br-roster-name-cell <?= $roleClass; ?>">
							<a target="_blank" href="<?= get_bloginfo('url') . "/player-work/?adventure_id=$adventure->adventure_id&player_id=$p->player_id"; ?>"><?= esc_html($displayName); ?></a>
							<?php if ($p->player_adventure_role != 'player') { ?><span class="br-badge br-badge-blue"><?= esc_html(strtoupper($p->player_adventure_role)); ?></span><?php } ?>
						</td>
						<?php if ($config['use_hexad']['value'] > 0) { ?>
						<td class="text-center">
							<span class="button-icon font _24 sq-40 <?= $p->player_adventure_role == 'gm' ? 'border border-all border-3 teal-border-400' : ($p->player_adventure_role == 'npc' ? 'border border-all border-3 light-blue-border-800' : ''); ?>" style="background-image:url(<?= esc_url($p->player_picture); ?>)" title="<?= esc_attr($p->player_hexad); ?>"></span>
						</td>
						<?php } ?>
						<td class="text-center"><?= (int) $p->player_level; ?></td>
						<td class="text-center"><?= (int) $p->player_xp; ?></td>
						<td class="text-center"><?= (int) $p->player_bloo; ?></td>
						<?php if ($grade_scale == 'percentage' || $grade_scale == 'letters') { ?>
						<td class="text-center"><?= $p->player_gpa ? $p->player_gpa : 0; ?></td>
						<?php } ?>
						<td class="text-center"><?= $p->player_last_login ? date('M j, Y', strtotime($p->player_last_login)) : __("never", "bluerabbit"); ?></td>
						<?php if ($isAdmin) { ?>
						<td class="text-center">
							<button class="button-icon blue-bg-800" onClick="updatePlayer(<?= $adventure->adventure_id . ", " . $p->player_id; ?>);"><span class="icon icon-rotate"></span></button>
						</td>
						<?php } ?>
						<?php foreach ($quests as $q) {
							$c = $player_post_by_id[$q->quest_id][$p->player_id] ?? null;
							$the_grade = $c['grade'] ?? null;
						?>
						<td class="br-roster-cell">
							<?php if (!$c) { ?>
								<span class="br-roster-cell-pending">&#9675;</span>
							<?php } elseif ($q->quest_type == 'challenge' || $q->quest_type == 'mission') { ?>
								<span class="br-roster-cell-done"><span class="icon icon-check"></span></span>
							<?php } elseif ($grade_scale == 'percentage') { ?>
								<input class="br-roster-grade-input" type="number" min="0" max="100" value="<?= $the_grade; ?>" id="the_post_grade_<?= $q->quest_id . "_" . $p->player_id; ?>" onChange="setGrade(<?= $q->quest_id . "," . $p->player_id; ?>);">
							<?php } elseif ($grade_scale == 'letters') { ?>
								<select class="br-input" style="width:70px;font-size:11px;padding:2px 4px" id="the_post_grade_<?= $q->quest_id . "_" . $p->player_id; ?>" onChange="setGrade(<?= $q->quest_id . "," . $p->player_id; ?>);">
									<option value="100" <?php if ($the_grade == 100) echo 'selected'; ?>>A</option>
									<option value="91.75" <?php if ($the_grade < 100 && $the_grade >= 91.75) echo 'selected'; ?>>A-</option>
									<option value="83.25" <?php if ($the_grade < 91.75 && $the_grade >= 83.25) echo 'selected'; ?>>B+</option>
									<option value="75" <?php if ($the_grade < 83.25 && $the_grade >= 75) echo 'selected'; ?>>B</option>
									<option value="66.75" <?php if ($the_grade < 75 && $the_grade >= 66.75) echo 'selected'; ?>>B-</option>
									<option value="58.25" <?php if ($the_grade < 66.75 && $the_grade >= 58.25) echo 'selected'; ?>>C+</option>
									<option value="50" <?php if ($the_grade < 58.25 && $the_grade >= 50) echo 'selected'; ?>>C</option>
									<option value="25" <?php if ($the_grade < 50 && $the_grade >= 25) echo 'selected'; ?>>D</option>
									<option value="0" <?php if ($the_grade < 25) echo 'selected'; ?>>F</option>
									<option value="NULL" <?php if ($the_grade === null) echo 'selected'; ?>><?= __("No grade", "bluerabbit"); ?></option>
								</select>
							<?php } else { ?>
								<span class="br-roster-cell-done"><span class="icon icon-check"></span></span>
							<?php } ?>
						</td>
						<?php } ?>
						<?php foreach ($achievements as $a) { ?>
						<td class="br-roster-cell">
							<?php if (!empty($player_achievements_by_id[$a->achievement_id][$p->player_id])) { ?>
								<span class="br-roster-cell-done"><span class="icon icon-check"></span></span>
							<?php } else { ?>
								<span class="br-roster-cell-pending">&#9675;</span>
							<?php } ?>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>"/>

<script>
function brFilterRoster() {
	var term = $('#roster-search').val().toLowerCase();
	$('.roster-row').each(function () {
		$(this).toggle(($(this).attr('data-search') || '').indexOf(term) !== -1);
	});
}

function brExportRosterCsv() {
	var rows = [];
	$('#roster-table tr').each(function () {
		if ($(this).is(':hidden')) return;
		var cols = [];
		$(this).find('th, td').each(function () {
			var text = $(this).find('input, select').length
				? $(this).find('input, select').val()
				: $(this).text().trim();
			cols.push('"' + String(text).replace(/"/g, '""') + '"');
		});
		rows.push(cols.join(','));
	});
	var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
	var link = document.createElement('a');
	link.href = URL.createObjectURL(blob);
	link.download = 'players.csv';
	link.click();
}
</script>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404/"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

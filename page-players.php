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
				}elseif($_GET['order'] == 'gender'){
					$order=" ORDER BY pm.player_gender ASC";
				}elseif($_GET['order'] == 'location'){
					$order=" ORDER BY pm.work_location ASC";
				}elseif($_GET['order'] == 'pillar'){
					$order=" ORDER BY pm.business_pillar ASC";
				}elseif($_GET['order'] == 'country'){
					$order=" ORDER BY pm.work_country ASC";
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
				b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad,
				pm.player_gender, pm.work_location, pm.business_pillar, pm.work_country
				FROM {$wpdb->prefix}br_player_adventure a
				LEFT JOIN {$wpdb->prefix}br_players b
				ON a.player_id=b.player_id
				LEFT JOIN {$wpdb->prefix}br_player_meta pm
				ON pm.player_meta_id = (SELECT MAX(player_meta_id) FROM {$wpdb->prefix}br_player_meta WHERE player_id = a.player_id)
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

<div class="br-page-full">

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

	<input type="text" id="roster-search" class="br-input br-w-full" placeholder="<?= esc_attr__("Search by name or email...", "bluerabbit"); ?>" onKeyup="brFilterRoster();" style="margin:0 0 16px">

	<?php
	// Built once, reused for both the sticky header table and (invisibly,
	// never) the body table - see the .br-roster-head-scroll note in
	// css/br-table.scss for why the header lives in its own table instead of
	// a <thead> inside the horizontally-scrolling body table.
	$role_qs = (isset($_GET['roles']) && $_GET['roles'] == 'all') ? '&roles=all' : '';
	$sort_link = function ($key, $label) use ($adventure, $role_qs) {
		$active = (isset($_GET['order']) && $_GET['order'] == $key) ? 'br-icon-primary' : '';
		$url = get_bloginfo('url') . "/players/?adventure_id={$adventure->adventure_id}&order={$key}{$role_qs}";
		return '<a href="' . esc_url($url) . '" class="' . $active . '">' . esc_html($label) . '</a>';
	};
	ob_start();
	?>
	<tr>
		<th class="br-roster-name-cell"><?= $sort_link('name', __("Name", "bluerabbit")); ?></th>
		<?php if ($config['use_hexad']['value'] > 0) { ?>
		<th class="text-center"><span class="icon icon-hexad"></span></th>
		<?php } ?>
		<th class="text-center"><?= $sort_link('level', __("Level", "bluerabbit")); ?></th>
		<th class="text-center"><?= $sort_link('xp', $xp_label); ?></th>
		<th class="text-center"><?= $sort_link('bloo', $bloo_label); ?></th>
		<?php if ($grade_scale == 'percentage' || $grade_scale == 'letters') { ?>
		<th class="text-center"><?= $sort_link('gpa', __("GPA", "bluerabbit")); ?></th>
		<?php } ?>
		<th class="text-center"><?= $sort_link('login', __("Last Login", "bluerabbit")); ?></th>
		<?php if ($isAdmin) { ?><th class="text-center"><?= __("Refresh", "bluerabbit"); ?></th><?php } ?>
		<th class="text-center"><?= $sort_link('gender', __("Gender", "bluerabbit")); ?></th>
		<th class="text-center"><?= $sort_link('location', __("Location", "bluerabbit")); ?></th>
		<th class="text-center"><?= $sort_link('pillar', __("Business Pillar", "bluerabbit")); ?></th>
		<th class="text-center"><?= $sort_link('country', __("Country", "bluerabbit")); ?></th>
		<?php foreach ($quests as $q) {
			$needs_grade_col = ($q->quest_type == 'challenge') || (bool) $q->mech_validate;
		?>
		<th class="br-roster-col-header" title="<?= esc_attr($q->quest_title); ?>">
			<span class="br-roster-col-label"><?= esc_html($q->quest_title); ?></span>
		</th>
		<?php if ($needs_grade_col) { ?>
		<th class="br-roster-col-header" title="<?= esc_attr($q->quest_title . ' — ' . __('Grade', 'bluerabbit')); ?>">
			<span class="br-roster-col-label"><?= esc_html($q->quest_title); ?> · <?= __("Grade", "bluerabbit"); ?></span>
		</th>
		<?php } ?>
		<?php } ?>
		<?php foreach ($achievements as $a) { ?>
		<th class="br-roster-col-header" title="<?= esc_attr($a->achievement_name . ' — ' . $a->achievement_xp . ' ' . $xp_label . ' | ' . $a->achievement_bloo . ' ' . $bloo_label); ?>">
			<span class="br-roster-col-label"><?= esc_html($a->achievement_name); ?></span>
		</th>
		<?php } ?>
	</tr>
	<?php
	$roster_header_row = ob_get_clean();
	?>

	<div class="br-roster-wrap">
		<div class="br-roster-head-scroll" id="roster-head-scroll">
			<table class="br-table br-roster-table" id="roster-head-table">
				<thead><?= $roster_header_row; ?></thead>
			</table>
		</div>
		<div class="br-roster-scroll" id="roster-body-scroll">
			<table class="br-table br-roster-table" id="roster-table">
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
						<td class="text-center" data-export="<?= esc_attr($p->player_gender ?: ''); ?>"><?= esc_html($p->player_gender ?: '—'); ?></td>
						<td class="text-center" data-export="<?= esc_attr($p->work_location ?: ''); ?>"><?= esc_html($p->work_location ?: '—'); ?></td>
						<td class="text-center" data-export="<?= esc_attr($p->business_pillar ?: ''); ?>"><?= esc_html($p->business_pillar ?: '—'); ?></td>
						<td class="text-center" data-export="<?= esc_attr($p->work_country ?: ''); ?>"><?= esc_html($p->work_country ?: '—'); ?></td>
						<?php foreach ($quests as $q) {
							$needs_grade_col = ($q->quest_type == 'challenge') || (bool) $q->mech_validate;
							$c = $player_post_by_id[$q->quest_id][$p->player_id] ?? null;
							$the_grade = $c['grade'] ?? null;
							$status = !$c ? 'empty' : (($needs_grade_col && !($the_grade > 0)) ? 'pending' : 'complete');
							$status_export = $status === 'complete' ? __('Complete', 'bluerabbit') : ($status === 'pending' ? __('Pending', 'bluerabbit') : '');
						?>
						<td class="br-roster-cell" data-export="<?= esc_attr($status_export); ?>">
							<?php if ($status === 'empty') { ?>
								<span class="br-roster-cell-pending">&#9675;</span>
							<?php } elseif ($status === 'pending') { ?>
								<span class="br-roster-cell-validating"><?= __("Pending", "bluerabbit"); ?></span>
							<?php } else { ?>
								<span class="br-roster-cell-done"><span class="icon icon-check"></span></span>
							<?php } ?>
						</td>
						<?php if ($needs_grade_col) { ?>
						<td class="br-roster-cell">
							<?php if (!$c) { ?>
							<?php } elseif ($q->quest_type == 'challenge') { ?>
								<?= esc_html($the_grade); ?>
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
								<?= esc_html($the_grade); ?>
							<?php } ?>
						</td>
						<?php } ?>
						<?php } ?>
						<?php foreach ($achievements as $a) {
							$has_it = !empty($player_achievements_by_id[$a->achievement_id][$p->player_id]);
						?>
						<td class="br-roster-cell" data-export="<?= $has_it ? esc_attr__('Complete', 'bluerabbit') : ''; ?>">
							<?php if ($has_it) { ?>
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
		<div class="br-roster-scrollbar-track" id="roster-scrollbar-track"><div id="roster-scrollbar-spacer"></div></div>
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

function brRosterRowToCsv($row) {
	var cols = [];
	$row.find('th, td').each(function () {
		var $cell = $(this);
		var exportAttr = $cell.attr('data-export');
		var text;
		if (exportAttr !== undefined) {
			text = exportAttr;
		} else if ($cell.find('input, select').length) {
			text = $cell.find('input, select').val();
		} else {
			text = $cell.text().trim();
		}
		cols.push('"' + String(text == null ? '' : text).replace(/"/g, '""') + '"');
	});
	return cols.join(',');
}

function brExportRosterCsv() {
	// The header row lives in its own table now (#roster-head-table) - see
	// the .br-roster-head-scroll note in css/br-table.scss.
	var rows = [brRosterRowToCsv($('#roster-head-table thead tr'))];
	$('#roster-table tbody tr').each(function () {
		if ($(this).is(':hidden')) return;
		rows.push(brRosterRowToCsv($(this)));
	});
	var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
	var link = document.createElement('a');
	link.href = URL.createObjectURL(blob);
	link.download = 'players.csv';
	link.click();
}

// The header table has no data of its own to size its columns by (it's a
// single row), so its column widths are mirrored from the body table's
// actual rendered first row - see the .br-roster-head-scroll note in
// css/br-table.scss for why the header is a separate table in the first
// place (position:sticky can't be scoped to only the vertical axis of a
// horizontally-scrolling box - it traps the whole element).
(function () {
	var $headScroll = $('#roster-head-scroll');
	var $headTable = $('#roster-head-table');
	var $bodyScroll = $('#roster-body-scroll');
	var $bodyTable = $('#roster-table');
	var $track = $('#roster-scrollbar-track');
	var $spacer = $('#roster-scrollbar-spacer');
	if (!$headTable.length || !$bodyTable.length) return;

	function syncColumnWidths() {
		var $firstRow = $bodyTable.find('tbody tr:first-child');
		var $headCells = $headTable.find('thead th');
		if (!$firstRow.length || !$headCells.length) return;
		var $bodyCells = $firstRow.children();
		if ($bodyCells.length !== $headCells.length) return;
		$headTable.css('table-layout', 'auto');
		var widths = $bodyCells.map(function () { return $(this).outerWidth(); }).get();
		$headCells.each(function (i) {
			$(this).css({ width: widths[i] + 'px', minWidth: widths[i] + 'px', maxWidth: widths[i] + 'px' });
		});
		$headTable.css('table-layout', 'fixed');
		var fullWidth = $bodyTable.outerWidth();
		$headTable.css('width', fullWidth + 'px');
		$spacer.css('width', fullWidth + 'px');
	}
	syncColumnWidths();
	$(window).on('resize', syncColumnWidths);

	// 3-way scrollLeft sync: dragging the always-visible bottom track, or
	// scrolling the table itself (mouse drag/trackpad/shift+wheel - its own
	// native scrollbar is hidden, see .br-roster-scroll in css/br-table.scss),
	// moves all three together.
	var syncing = false;
	function syncAllFrom(source) {
		if (syncing) return;
		syncing = true;
		var left = source.scrollLeft;
		if (source !== $bodyScroll[0]) $bodyScroll.scrollLeft(left);
		if (source !== $headScroll[0]) $headScroll.scrollLeft(left);
		if (source !== $track[0]) $track.scrollLeft(left);
		syncing = false;
	}
	$bodyScroll.on('scroll', function () { syncAllFrom(this); });
	$track.on('scroll', function () { syncAllFrom(this); });
})();
</script>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404/"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

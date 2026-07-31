<?php
// Included by br_guild_roster() (functions.php) with $guild, $players, $quests,
// $achievements, $player_post_by_id, $player_achievements_by_id, $grade_scale,
// $xp_label, $bloo_label, $aid already in scope (extract()'d from
// BR_Stats::get_guild_roster()'s return, plus $aid from the including
// function's own local scope - include() shares it, no need to re-pass it).
$engagement_badge = [
	'on_fire' => 'br-badge-amber', 'active' => 'br-badge-green', 'moderate' => 'br-badge-blue',
	'cooling_off' => 'br-badge-purple', 'dormant' => 'br-badge-red', 'never_logged_in' => 'br-badge-red',
];
?>
<div class="tabi-conditions-header">
	<div class="icon-group">
		<span class="br-icon-btn" style="background-image:url(<?= esc_url($guild->guild_logo); ?>)"></span>
		<span class="icon-content">
			<h3 class="br-text-16 w700"><?= esc_html($guild->guild_name); ?></h3>
			<span class="br-text-12-muted"><?= count($players); ?> <?= count($players) == 1 ? __("player", "bluerabbit") : __("players", "bluerabbit"); ?></span>
		</span>
	</div>
	<button class="br-close-btn" onclick="closeGuildRoster();"><span class="icon icon-cancel white-color"></span></button>
</div>
<div class="tabi-conditions-body">
	<?php if (empty($players)) { ?>
		<div class="br-empty">
			<span class="icon icon-guild"></span>
			<h3><?= __("No players in this guild yet", "bluerabbit"); ?></h3>
		</div>
	<?php } else { ?>
		<div class="guild-roster-table-wrap">
			<table class="br-table br-roster-table guild-roster-table">
				<thead>
					<tr>
						<th class="br-roster-name-cell"><?= __("Name", "bluerabbit"); ?></th>
						<th class="text-center"><?= __("Level", "bluerabbit"); ?></th>
						<th class="text-center"><?= esc_html($xp_label); ?></th>
						<th class="text-center"><?= esc_html($bloo_label); ?></th>
						<th class="text-center"><?= __("Engagement", "bluerabbit"); ?></th>
						<th class="text-center"><?= __("Journey %", "bluerabbit"); ?></th>
						<th class="text-center"><?= __("Last Login", "bluerabbit"); ?></th>
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
						<th class="br-roster-col-header" title="<?= esc_attr($a->achievement_name); ?>">
							<span class="br-roster-col-label"><?= esc_html($a->achievement_name); ?></span>
						</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($players as $p) {
						$displayName = $p['player_nickname'] ?: ($p['player_first'] ? $p['player_first'] . " " . $p['player_last'] : $p['player_email']);
						$bucket = $p['engagement_bucket'];
					?>
					<tr>
						<td class="br-roster-name-cell">
							<a target="_blank" href="<?= get_bloginfo('url') . "/player-work/?adventure_id=$aid&player_id={$p['player_id']}"; ?>"><?= esc_html($displayName); ?></a>
						</td>
						<td class="text-center"><?= (int) $p['player_level']; ?></td>
						<td class="text-center"><?= (int) $p['player_xp']; ?></td>
						<td class="text-center"><?= (int) $p['player_bloo']; ?></td>
						<td class="text-center"><span class="br-badge <?= $engagement_badge[$bucket] ?? 'br-badge-blue'; ?>"><?= (int) $p['engagement_score']; ?></span></td>
						<td class="text-center"><?= $p['journey_pct']; ?>%</td>
						<td class="text-center"><?= $p['player_last_login'] ? date('M j, Y', strtotime($p['player_last_login'])) : __("never", "bluerabbit"); ?></td>
						<?php foreach ($quests as $q) {
							$needs_grade_col = ($q->quest_type == 'challenge') || (bool) $q->mech_validate;
							$c = $player_post_by_id[$q->quest_id][$p['player_id']] ?? null;
							$the_grade = $c['grade'] ?? null;
							$status = !$c ? 'empty' : (($needs_grade_col && !($the_grade > 0)) ? 'pending' : 'complete');
						?>
						<td class="br-roster-cell">
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
							<?php if ($c && $the_grade !== null) { ?><?= esc_html($the_grade); ?><?php } ?>
						</td>
						<?php } ?>
						<?php } ?>
						<?php foreach ($achievements as $a) {
							$has_it = !empty($player_achievements_by_id[$a->achievement_id][$p['player_id']]);
						?>
						<td class="br-roster-cell">
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
	<?php } ?>
</div>

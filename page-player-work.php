<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$player_id_get = ($isGM || $isAdmin || $isNPC) ? br_require_id('player_id', false) : null;
if ($player_id_get) {
	$the_player_id = $player_id_get;
	$view_player   = BR_Player::instance()->getPlayerData($the_player_id);
} else {
	$the_player_id = $current_user->ID;
	$view_player   = $current_player;
}
// Viewing your own work is the case where the journey is actionable. A GM or NPC
// looking at somebody else gets the same milestone list, but read-only: they can
// see where that player is stuck, they just can't walk the journey for them.
$is_self = ($the_player_id == $current_user->ID);

$myquests = $wpdb->get_results($wpdb->prepare("SELECT
	a.pp_grade, a.pp_modified, a.quest_id, a.pp_status,
	b.quest_title, b.quest_type,
	b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge, b.quest_success_message
	FROM {$wpdb->prefix}br_player_posts a
	LEFT JOIN {$wpdb->prefix}br_quests b ON a.quest_id = b.quest_id
	WHERE a.adventure_id=%d AND a.player_id=%d AND b.quest_status='publish'
	ORDER BY a.pp_modified
", $adventure->adventure_id, $the_player_id));

$attempts = $wpdb->get_results($wpdb->prepare("SELECT
	a.attempt_grade, a.attempt_answers, a.quest_id, a.attempt_id, a.attempt_date, a.attempt_status,
	b.quest_title, b.quest_type, b.quest_id,
	b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge
	FROM {$wpdb->prefix}br_challenge_attempts a
	LEFT JOIN {$wpdb->prefix}br_quests b ON a.quest_id = b.quest_id
	WHERE a.adventure_id=%d AND a.player_id=%d
		AND b.quest_type='challenge' AND a.attempt_status != 'trash'
	ORDER BY a.attempt_date DESC
", $adventure->adventure_id, $the_player_id));

$attempt_answers = $wpdb->get_results($wpdb->prepare("SELECT a.*, b.answer_value AS c_answer_value, b.answer_correct, c.question_title
	FROM {$wpdb->prefix}br_challenge_attempt_answers a
	LEFT JOIN {$wpdb->prefix}br_challenge_answers b ON a.answer_id = b.answer_id
	LEFT JOIN {$wpdb->prefix}br_challenge_questions c ON a.question_id = c.question_id
	LEFT JOIN {$wpdb->prefix}br_quests d ON c.quest_id = d.quest_id
	WHERE d.adventure_id=%d AND a.player_id=%d
", $adventure->adventure_id, $the_player_id));

// Case-study attempts: one row per run, pass or fail, newest first. The answers column
// holds the activity's own per-question state, so the breakdown below is whatever the
// activity actually recorded rather than a shape we imposed on it.
$cs_attempts = BR_CaseStudy::attempts_for_player($the_player_id, $adventure->adventure_id);

// ── Personal stats (same source the stats page uses) ────────────────────────
$pw_stats        = new BR_Stats();
$pw_summary      = $pw_stats->get_player_summary($the_player_id, $adv_child_id);
$pw_achievements = $pw_stats->get_player_achievements($the_player_id, $adv_child_id);
$pw_guild        = $pw_stats->get_player_guild($the_player_id, $adv_child_id);
$pw_scorm        = $pw_stats->get_player_scorm_completions($the_player_id);
$pw_tabis        = $pw_stats->get_player_tabi_progress($the_player_id, $adv_child_id);
$pw_types        = $pw_stats->get_player_type_completion($the_player_id, $adv_child_id);
$pw_last         = $pw_stats->get_player_last_activity($the_player_id, $adv_child_id);
$pw_engagement   = $pw_stats->get_player_engagement($the_player_id, $adv_child_id);

// ── Status bars (the numbers the profile-box modal shows) ───────────────────
// For your own page header.php has already computed all of this against the live
// progression state, so it is reused as-is rather than recalculated - and
// recalculating is not free of side effects (getPlayerProgress writes back).
if ($is_self) {
	$pw_level    = (int) $current_player->player_level;
	$pw_xp       = (int) $current_player->player_xp;
	$pw_bloo     = (int) $player['bloo'];
	$pw_earned   = (int) $player['totalEarned'];
	$pw_ep       = (int) $current_player->player_ep;
	$pw_next     = $nextLevel;
	$pw_perc_xp  = $percXP;
	$pw_perc_bloo= $percBLOO;
	$pw_max_ep   = $maxEP;
	$pw_perc_ep  = $percEP;
	$pw_rank     = isset($myRank) ? $myRank : null;
} else {
	$pw_pa       = BR_Player::instance()->getPlayerAdventureData($adv_child_id, $the_player_id);
	$pw_level    = max(1, (int) ($pw_pa->player_level ?? 1));
	$pw_xp       = (int) ($pw_pa->player_xp ?? 0);
	$pw_bloo     = (int) ($pw_pa->player_bloo ?? 0);
	$pw_earned   = 0;
	$pw_ep       = (int) ($pw_pa->player_ep ?? 0);
	$pw_next     = $pw_level * ($pw_level + 1) / 2 * 1000;
	$pw_last_lvl = ($pw_level * ($pw_level - 1)) / 2 * 1000;
	$pw_perc_xp  = min(100, max(0, ($pw_xp - $pw_last_lvl) * 100 / ($pw_level * 1000)));
	$pw_perc_bloo= 0;
	$pw_max_ep   = 100 + (($pw_level * ($pw_level + 1) / 2) * 20);
	$pw_perc_ep  = $pw_max_ep > 0 ? ($pw_ep * 100 / $pw_max_ep) : 0;
	$pw_rank     = (!empty($pw_pa->achievement_id))
		? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=%d AND achievement_status='publish'", $pw_pa->achievement_id))
		: null;
}

// ── Milestones, in journey order, with the journey's own availability rules ──
// resolveMilestoneTemplate() is the single source of truth for "can this player
// open this milestone" (see journey.php / journey-board.php), so the same call
// decides what is a link here instead of this page inventing its own rules.
$pw_milestones = [];
if ($is_self) {
	$pw_quests = $wpdb->get_results($wpdb->prepare(
		"SELECT quests.* FROM {$wpdb->prefix}br_quests quests
			 LEFT JOIN {$wpdb->prefix}br_tabis tabis ON quests.tabi_id = tabis.tabi_id
		 WHERE quests.adventure_id=%d AND quests.quest_status IN ('publish','locked')
		   AND quests.quest_type IN ('quest','challenge','survey','mission')
		 ORDER BY (quests.tabi_id IS NULL OR quests.tabi_id=0) ASC, tabis.tabi_level ASC, quests.tabi_id ASC, quests.quest_order ASC, quests.quest_id ASC",
		$adv_parent_id
	));
	$pw_today    = date('YmdHi');
	$pw_snapshot = BR_Conditions::instance()->buildProgressSnapshot($adv_parent_id, $adv_child_id, $the_player_id, $playerReset);
	// Templates a player can actually act on. Everything else is a wall of some
	// kind, so the row renders as text with the reason instead of as a link.
	$pw_open   = ['milestone', 'milestone-finished', 'milestone-pending'];
	$pw_reason = [
		'milestone-locked'        => __("Locked", "bluerabbit"),
		'milestone-levelup'       => __("Level up first", "bluerabbit"),
		'milestone-unlock'        => __("Needs unlocking", "bluerabbit"),
		'milestone-startdate'     => __("Not open yet", "bluerabbit"),
		'milestone-deadline'      => __("Deadline passed", "bluerabbit"),
		'milestone-deadline-cost' => __("Deadline passed", "bluerabbit"),
		'milestone-requirements'  => __("Requirements missing", "bluerabbit"),
		'milestone-unavailable'   => __("Not available", "bluerabbit"),
		'milestone-blocked'       => __("Blocked by debt", "bluerabbit"),
	];
	foreach ($pw_quests as $mi) {
		$tpl = BR_Progression::instance()->resolveMilestoneTemplate(
			$mi, $player, $current_player->player_level, $player_achievements,
			$reqs_ids, $pw_today, $adv_parent_id, $pw_snapshot
		);
		$done = ($tpl === 'milestone-finished');
		$pw_milestones[] = [
			'quest'  => $mi,
			'tpl'    => $tpl,
			'done'   => $done,
			'open'   => in_array($tpl, $pw_open, true),
			'reason' => $pw_reason[$tpl] ?? '',
			// A finished quest or challenge leads to its results page; anything
			// still to do leads to the milestone itself.
			'link'   => $done && in_array($mi->quest_type, ['quest','challenge'], true)
				? get_bloginfo('url')."/post/?adventure_id=$adv_child_id&questID=$mi->quest_id"
				: get_bloginfo('url')."/$mi->quest_type/?adventure_id=$adv_child_id&questID=$mi->quest_id",
		];
	}
} else {
	// Read-only view: completion comes straight from the posts table.
	foreach ($pw_stats->get_player_quest_progress($the_player_id, $adv_child_id) as $pq) {
		$pw_milestones[] = [
			'quest'  => (object) [
				'quest_id'    => $pq['quest_id'],
				'quest_title' => $pq['quest_title'],
				'quest_type'  => $pq['quest_type'],
				'quest_icon'  => $pq['quest_icon'],
				'mech_level'  => 0,
				'mech_xp'     => $pq['mech_xp'],
				'mech_bloo'   => $pq['mech_bloo'],
			],
			'tpl'    => $pq['status'] === 'publish' ? 'milestone-finished' : 'milestone',
			'done'   => $pq['status'] === 'publish',
			'open'   => $pq['status'] === 'publish',
			'reason' => $pq['status'] === 'publish' ? '' : __("Not completed yet", "bluerabbit"),
			'link'   => get_bloginfo('url')."/post/?adventure_id=$adv_child_id&questID={$pq['quest_id']}&uID=$the_player_id",
		];
	}
}
$pw_done_count = count(array_filter($pw_milestones, function($m){ return $m['done']; }));
$pw_earned_ach = count(array_filter($pw_achievements, function($a){ return !empty($a['earned_at']); }));

// Engagement gauge (identical scale and colours to the stats page).
$pw_eng_labels = [
	'on_fire'    => __("On Fire", "bluerabbit"),
	'active'     => __("Active", "bluerabbit"),
	'moderate'   => __("Moderate", "bluerabbit"),
	'cooling_off'=> __("Cooling Off", "bluerabbit"),
	'dormant'    => __("Dormant", "bluerabbit"),
];
$pw_eng_colors = ['on_fire'=>'#f7cb15','active'=>'#24da98','moderate'=>'#1cc2eb','cooling_off'=>'#ff9800','dormant'=>'#f44336'];
$pw_eng_score  = (int) $pw_engagement['score'];
$pw_eng_level  = $pw_engagement['level'];
$pw_eng_circ   = 326.73;
$pw_eng_offset = round($pw_eng_circ * (1 - $pw_eng_score / 100), 2);
$pw_eng_color  = $pw_eng_colors[$pw_eng_level] ?? '#1cc2eb';
?>

<script>
window.brStats = {
	ajaxurl: '<?= admin_url("admin-ajax.php"); ?>',
	nonce: '<?= wp_create_nonce("br_stats_nonce"); ?>',
	adventureId: <?= (int)$adv_child_id; ?>,
	userId: <?= (int)$the_player_id; ?>,
	isManager: false,
	labels: {
		xp: '<?= esc_js($xp_label); ?>',
		bloo: '<?= esc_js($bloo_label); ?>',
		ep: '<?= esc_js($ep_label); ?>'
	},
	adventureTitle: '<?= esc_js($adventure->adventure_title); ?>',
	typeCompletion: <?= json_encode($pw_types); ?>,
	engagement: <?= json_encode($pw_engagement); ?>,
	segmentDimensions: [],
	segmentBreakdown: null
};
</script>

<div class="br-page br-stats-page">

	<!-- Hero Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background-image:url(<?= esc_url($view_player->player_picture ?? ''); ?>)"></div>
		<div class="br-flex-1">
			<h1 class="br-page-title"><?= $is_self ? __("My Work", "bluerabbit") : __("Player Work", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($view_player->display_name ?? $view_player->player_display_name ?? ''); ?></span>
		</div>
		<?php if (!$is_self) { ?>
		<a class="br-btn ghost" href="<?= get_bloginfo('url')."/stats/?adventure_id=$adv_child_id&uid=$the_player_id"; ?>">
			<span class="icon icon-data"></span> <?= __("Full Stats", "bluerabbit"); ?>
		</a>
		<?php } ?>
	</div>

	<!-- Tabs: real panels, not scroll anchors -->
	<div class="br-tabs" id="pw-tabs">
		<button class="br-tab-btn active" onClick="brSwitchPanel('#pw-panels', '#pw-overview', this);">
			<span class="icon icon-data"></span> <?php _e("Overview", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-milestones', this);">
			<span class="icon icon-quest"></span> <?php _e("Milestones", "bluerabbit"); ?>
			<span class="br-badge br-badge-blue"><?= $pw_done_count; ?>/<?= count($pw_milestones); ?></span>
		</button>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-work', this);">
			<span class="icon icon-document"></span> <?php _e("My Answers", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-challenges', this);">
			<span class="icon icon-challenge"></span> <?php _e("Challenges", "bluerabbit"); ?>
			<span class="br-badge br-badge-red"><?= count($attempts); ?></span>
		</button>
		<?php if ($cs_attempts) { ?>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-casestudies', this);">
			<span class="icon icon-document"></span> <?php _e("Case Studies", "bluerabbit"); ?>
			<span class="br-badge br-badge-teal"><?= count($cs_attempts); ?></span>
		</button>
		<?php } ?>
		<?php if (!empty($pw_achievements)) { ?>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-achievements', this);">
			<span class="icon icon-achievement"></span> <?php _e("Achievements", "bluerabbit"); ?>
			<span class="br-badge br-badge-purple"><?= $pw_earned_ach; ?>/<?= count($pw_achievements); ?></span>
		</button>
		<?php } ?>
		<?php if ($isDemo && $is_self) { ?>
		<button class="br-tab-btn" onClick="brSwitchPanel('#pw-panels', '#pw-reset', this);">
			<span class="icon icon-rotate"></span> <?php _e("Reset", "bluerabbit"); ?>
		</button>
		<?php } ?>
	</div>

	<div id="pw-panels">

		<!-- ═══════════════ OVERVIEW ═══════════════ -->
		<div class="br-panel-group" id="pw-overview">

			<!-- Status card: the same numbers as the status modal -->
			<div class="br-panel br-pw-status">
				<div class="br-pw-status-head">
					<div class="br-pw-status-avatar" style="background-image:url(<?= esc_url($view_player->player_picture ?? ''); ?>)"></div>
					<div class="br-pw-status-id">
						<?php if ($pw_rank) { ?>
						<span class="br-stats-badge <?= esc_attr($pw_rank->achievement_color ?: $adventure->adventure_color); ?>">
							<span class="icon icon-rank"></span> <?= esc_html($pw_rank->achievement_name); ?>
						</span>
						<?php } ?>
						<h2 class="br-pw-status-name"><?= esc_html($view_player->display_name ?? $view_player->player_display_name ?? ''); ?></h2>
						<span class="br-pw-status-level"><?= __("Level", "bluerabbit"); ?> <?= $pw_level; ?></span>
					</div>
					<div class="br-stats-engagement-gauge">
						<svg viewBox="0 0 120 120">
							<circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
							<circle cx="60" cy="60" r="52" fill="none" stroke="<?= $pw_eng_color; ?>" stroke-width="8"
									stroke-dasharray="<?= $pw_eng_circ; ?>" stroke-dashoffset="<?= $pw_eng_offset; ?>"
									transform="rotate(-90 60 60)" stroke-linecap="round"/>
							<text x="60" y="52" text-anchor="middle" fill="#ffffff" font-size="28" font-weight="900" font-family="proxima-nova-extra-condensed,sans-serif"><?= $pw_eng_score; ?></text>
							<text x="60" y="70" text-anchor="middle" fill="<?= $pw_eng_color; ?>" font-size="9" font-weight="700"><?= strtoupper($pw_eng_labels[$pw_eng_level] ?? ''); ?></text>
						</svg>
						<span class="br-stats-engagement-label"><?= __("Engagement", "bluerabbit"); ?></span>
					</div>
				</div>

				<div class="br-pw-bars">
					<div class="br-pw-bar">
						<div class="br-pw-bar-legend">
							<span class="br-pw-bar-name"><span class="icon icon-star"></span> <?= $xp_long_label; ?></span>
							<span class="br-pw-bar-value">
								<strong><?= BR_Utils::instance()->toMoney($pw_xp); ?></strong> / <?= BR_Utils::instance()->toMoney($pw_next); ?>
							</span>
						</div>
						<div class="br-pw-bar-track">
							<div class="br-pw-bar-fill xp" style="width:<?= round(min(100, max(0, $pw_perc_xp)), 2); ?>%"></div>
						</div>
					</div>

					<div class="br-pw-bar">
						<div class="br-pw-bar-legend">
							<span class="br-pw-bar-name"><span class="icon icon-bloo"></span> <?= $bloo_long_label; ?></span>
							<span class="br-pw-bar-value">
								<strong><?= BR_Utils::instance()->toMoney($pw_bloo, "$"); ?></strong>
								<?php if ($pw_earned > 0) { ?>
									/ <?= BR_Utils::instance()->toMoney($pw_earned, "$"); ?> <?= __("earned", "bluerabbit"); ?>
								<?php } ?>
							</span>
						</div>
						<div class="br-pw-bar-track">
							<div class="br-pw-bar-fill bloo" style="width:<?= round(min(100, max(0, $pw_perc_bloo)), 2); ?>%"></div>
						</div>
					</div>

					<?php if ($use_encounters) { ?>
					<div class="br-pw-bar">
						<div class="br-pw-bar-legend">
							<span class="br-pw-bar-name"><span class="icon icon-activity"></span> <?= $ep_long_label; ?></span>
							<span class="br-pw-bar-value"><strong><?= $pw_ep; ?></strong> / <?= $pw_max_ep; ?></span>
						</div>
						<div class="br-pw-bar-track">
							<div class="br-pw-bar-fill ep" style="width:<?= round(min(100, max(0, $pw_perc_ep)), 2); ?>%"></div>
						</div>
					</div>
					<?php } ?>
				</div>

				<div class="br-stats-last-activity">
					<?php if (!empty($pw_summary['rank_position'])) { ?>
					<span><?= __("Rank", "bluerabbit"); ?>: <strong>#<?= $pw_summary['rank_position']; ?> <?= __("of", "bluerabbit"); ?> <?= $pw_summary['total_players']; ?></strong></span>
					<?php } ?>
					<?php if ($pw_last['days_since_login'] !== null) { ?>
					<span><?= __("Last login", "bluerabbit"); ?>: <strong><?= round($pw_last['days_since_login']); ?>d</strong></span>
					<?php } ?>
					<?php if ($pw_last['days_since_quest'] !== null) { ?>
					<span><?= __("Last milestone", "bluerabbit"); ?>: <strong><?= round($pw_last['days_since_quest']); ?>d</strong></span>
					<?php } ?>
				</div>
			</div>

			<!-- Currencies -->
			<div class="br-stats-currencies">
				<div class="br-stats-currency xp">
					<span class="br-stats-currency-value"><?= number_format($pw_xp); ?></span>
					<span class="br-stats-currency-label"><?= $xp_label; ?></span>
				</div>
				<div class="br-stats-currency bloo">
					<span class="br-stats-currency-value"><?= number_format($pw_bloo); ?></span>
					<span class="br-stats-currency-label"><?= $bloo_label; ?></span>
				</div>
				<div class="br-stats-currency ep">
					<span class="br-stats-currency-value"><?= number_format($pw_ep); ?></span>
					<span class="br-stats-currency-label"><?= $ep_label; ?></span>
				</div>
			</div>

			<!-- Charts -->
			<div class="br-stats-charts-row">
				<div class="br-stats-panel br-stats-two-thirds">
					<h3><?= $xp_label; ?> <?= __("Over Time", "bluerabbit"); ?></h3>
					<div class="br-stats-chart-wrap"><canvas id="br-xp-history-chart"></canvas></div>
				</div>
				<div class="br-stats-panel br-stats-one-third">
					<h3><?= __("Completion by Type", "bluerabbit"); ?></h3>
					<div class="br-stats-chart-wrap br-stats-doughnut-wrap"><canvas id="br-type-completion-chart"></canvas></div>
				</div>
			</div>

			<!-- Tabi progress -->
			<?php if (!empty($pw_tabis)) { ?>
			<div class="br-stats-panel">
				<h3><?= __("Tabi Progress", "bluerabbit"); ?></h3>
				<div class="br-stats-quest-list">
					<?php foreach ($pw_tabis as $tb) {
						$tb_total = (int) $tb['total_quests'];
						$tb_done  = (int) $tb['completed_quests'];
						$tb_pct   = $tb_total > 0 ? round(($tb_done / $tb_total) * 100) : 0;
						$tb_class = $tb_pct >= 100 ? 'complete' : ($tb_pct > 0 ? 'in-progress' : 'locked');
					?>
					<div class="br-stats-quest-row">
						<div class="br-stats-quest-info">
							<span class="icon icon-tabi"></span>
							<span class="br-stats-quest-title"><?= esc_html($tb['tabi_name']); ?></span>
						</div>
						<div class="br-stats-quest-bar-wrap">
							<div class="br-stats-quest-bar <?= $tb_class; ?>" style="width:<?= $tb_pct; ?>%"></div>
						</div>
						<span class="br-stats-quest-status <?= $tb_class; ?>"><?= $tb_done; ?>/<?= $tb_total; ?></span>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>

			<!-- Guild -->
			<?php if (!empty($pw_guild)) { ?>
			<div class="br-stats-panel br-stats-guild-card">
				<h3><?= __("Guild", "bluerabbit"); ?></h3>
				<div class="br-stats-guild-content">
					<?php if ($pw_guild['guild_logo']) { ?>
					<img src="<?= esc_url($pw_guild['guild_logo']); ?>" class="br-stats-guild-logo" alt="">
					<?php } ?>
					<div class="br-stats-guild-info">
						<h4><?= esc_html($pw_guild['guild_name']); ?></h4>
						<div class="br-stats-guild-stats">
							<span><strong><?= __("Rank", "bluerabbit"); ?>:</strong> #<?= $pw_guild['rank']; ?> / <?= $pw_guild['total_guilds']; ?></span>
							<span><strong><?= $xp_label; ?>:</strong> <?= number_format($pw_guild['total_xp']); ?></span>
							<span><strong><?= __("Members", "bluerabbit"); ?>:</strong> <?= $pw_guild['member_count']; ?></span>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<!-- SCORM -->
			<?php if (!empty($pw_scorm)) { ?>
			<div class="br-stats-panel">
				<h3><?= __("SCORM Completions", "bluerabbit"); ?></h3>
				<div class="br-table-scroll">
					<table class="br-table">
						<thead>
							<tr>
								<th><?= __("Step", "bluerabbit"); ?></th>
								<th class="text-center"><?= __("Status", "bluerabbit"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($pw_scorm as $sc) {
								$sc_class = ($sc['status'] === 'completed' || $sc['status'] === 'passed') ? 'complete' : 'incomplete';
							?>
							<tr>
								<td><?= esc_html($sc['step_title']); ?></td>
								<td class="text-center">
									<span class="br-stats-scorm-status <?= $sc_class; ?>"><?= esc_html(ucfirst($sc['status'])); ?></span>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php } ?>
		</div>

		<!-- ═══════════════ MILESTONES ═══════════════ -->
		<div class="br-panel-group br-initially-hidden" id="pw-milestones">
			<div class="br-panel">
				<h3 class="br-panel-title">
					<span class="icon icon-quest"></span> <?php _e("Milestone Progress", "bluerabbit"); ?>
					<span class="br-badge br-badge-blue"><?= $pw_done_count; ?>/<?= count($pw_milestones); ?></span>
				</h3>
				<?php if ($is_self) { ?>
				<p class="br-form-hint"><?php _e("Anything you can open right now is a link — click it to go and finish it. Everything else tells you what is standing in the way.", "bluerabbit"); ?></p>
				<?php } ?>

				<?php if ($pw_milestones) { ?>
				<div class="br-stats-quest-list br-pw-milestones">
					<?php foreach ($pw_milestones as $m) {
						$mq  = $m['quest'];
						$cls = $m['done'] ? 'complete' : ($m['open'] ? 'in-progress' : 'locked');
						$tag = $m['done'] ? __("Complete", "bluerabbit") : ($m['open'] ? __("Available", "bluerabbit") : $m['reason']);
					?>
					<?php if ($m['open']) { ?>
					<a class="br-stats-quest-row br-pw-milestone open" href="<?= esc_url($m['link']); ?>">
					<?php } else { ?>
					<div class="br-stats-quest-row br-pw-milestone locked">
					<?php } ?>
						<div class="br-stats-quest-info">
							<span class="icon icon-<?= esc_attr($mq->quest_icon ?: $mq->quest_type ?: 'quest'); ?>"></span>
							<span class="br-stats-quest-title"><?= esc_html($mq->quest_title); ?></span>
							<?php if (!$m['open']) { ?><span class="icon icon-lock br-pw-milestone-lock"></span><?php } ?>
						</div>
						<div class="br-pw-milestone-rewards">
							<span><span class="icon icon-star"></span> <?= (int)$mq->mech_xp; ?></span>
							<span><span class="icon icon-bloo"></span> <?= (int)$mq->mech_bloo; ?></span>
						</div>
						<span class="br-stats-quest-status <?= $cls; ?>"><?= esc_html($tag); ?></span>
					<?php if ($m['open']) { ?>
					</a>
					<?php } else { ?>
					</div>
					<?php } ?>
					<?php } ?>
				</div>
				<?php } else { ?>
				<div class="br-empty-state"><span class="icon icon-quest"></span> <?php _e("No milestones published yet", "bluerabbit"); ?></div>
				<?php } ?>
			</div>
		</div>

		<!-- ═══════════════ MY ANSWERS (submitted work) ═══════════════ -->
		<div class="br-panel-group br-initially-hidden" id="pw-work">
			<?php if ($myquests) { ?>
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-document"></span> <?php _e("Submitted Work", "bluerabbit"); ?></h3>
				<p class="br-form-hint"><?php _e("Open any of these to read back everything you wrote and every answer you gave, step by step.", "bluerabbit"); ?></p>
				<div class="br-table-scroll">
					<table class="br-table">
						<thead>
							<tr>
								<th class="text-center"><?php _e("Lvl", "bluerabbit"); ?></th>
								<th><?php _e("Name", "bluerabbit"); ?></th>
								<th class="text-center"><?= $xp_label; ?></th>
								<th class="text-center"><?= $bloo_label; ?></th>
								<th class="text-center"><?php _e("Grade", "bluerabbit"); ?></th>
								<th><?php _e("Actions", "bluerabbit"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($myquests as $q) {
								if ($q->quest_type !== 'quest') continue;
								$post_link = get_bloginfo('url') . "/post/?adventure_id=$adventure->adventure_id&questID=$q->quest_id" . ($is_self ? '' : "&uID=$the_player_id");
							?>
							<tr>
								<td class="text-center"><?= $q->mech_level; ?></td>
								<td><a href="<?= esc_url($post_link); ?>"><?= esc_html($q->quest_title); ?></a></td>
								<td class="text-center"><?= $q->mech_xp; ?></td>
								<td class="text-center"><?= $q->mech_bloo; ?></td>
								<td class="text-center">
									<?php if ($q->pp_grade !== null && $q->pp_grade !== '') { ?>
										<span class="br-badge br-badge-green"><?= $q->pp_grade; ?></span>
									<?php } else { echo '&mdash;'; } ?>
								</td>
								<td>
									<div class="br-actions">
									<?php if ($q->pp_status == 'publish') { ?>
										<a href="<?= esc_url($post_link); ?>" class="br-btn">
											<span class="icon icon-view"></span> <?php _e("View answers", "bluerabbit"); ?>
										</a>
										<?php if (!$q->pp_grade) { ?>
										<a href="<?= get_bloginfo('url') . "/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>" class="br-btn br-btn-green">
											<span class="icon icon-edit"></span> <?php _e("Edit", "bluerabbit"); ?>
										</a>
										<?php if ($canEdit) { ?>
										<button class="br-btn br-btn-red" onClick="br_confirm_trd('trash',<?= $q->quest_id; ?>,'player_post');">
											<span class="icon icon-trash"></span> <?php _e("Trash", "bluerabbit"); ?>
										</button>
										<?php } ?>
										<?php } else { ?>
										<span class="br-badge br-badge-green"><?php _e("Graded", "bluerabbit"); ?></span>
										<?php } ?>
									<?php } else { ?>
										<?php if ($canEdit) { ?>
										<button class="br-btn" onClick="br_confirm_trd('publish',<?= $q->quest_id; ?>,'player_post');">
											<span class="icon icon-restore"></span> <?php _e("Restore", "bluerabbit"); ?>
										</button>
										<button class="br-btn br-btn-red" onClick="br_confirm_trd('delete',<?= $q->quest_id; ?>,'player_post');">
											<span class="icon icon-trash"></span> <?php _e("Delete", "bluerabbit"); ?>
										</button>
										<?php } else { ?>
										<span class="br-badge br-badge-amber"><?php _e("In the trash", "bluerabbit"); ?></span>
										<?php } ?>
									<?php } ?>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php } else { ?>
			<div class="br-panel br-empty">
				<span class="icon icon-document"></span>
				<h3><?php _e("Nothing submitted yet", "bluerabbit"); ?></h3>
				<p><?php _e("Complete milestones to see your answers here.", "bluerabbit"); ?></p>
			</div>
			<?php } ?>
		</div>

		<!-- ═══════════════ CHALLENGES ═══════════════ -->
		<div class="br-panel-group br-initially-hidden" id="pw-challenges">
			<?php if ($attempts) { ?>
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-challenge"></span> <?php _e("Challenge Attempts", "bluerabbit"); ?></h3>
				<p class="br-form-hint"><?php _e("Click any attempt to see every question and the answer that was chosen.", "bluerabbit"); ?></p>
				<div class="br-table-scroll">
					<table class="br-table">
						<thead>
							<tr>
								<th><?php _e("Challenge", "bluerabbit"); ?></th>
								<th class="text-center"><?php _e("Status", "bluerabbit"); ?></th>
								<th class="text-center"><?php _e("Grade", "bluerabbit"); ?></th>
								<th><?php _e("Date", "bluerabbit"); ?></th>
								<?php if ($canEdit) { ?>
								<th><?php _e("Actions", "bluerabbit"); ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($attempts as $a) { ?>
							<tr class="br-accordion-header" onclick="brToggleAccordion(this)">
								<td>
									<span class="br-accordion-arrow"></span>
									<?= esc_html($a->quest_title); ?>
								</td>
								<td class="text-center">
									<?php if ($a->attempt_status == 'success') { ?>
									<span class="br-badge br-badge-green"><span class="icon icon-check"></span> <?php _e("Pass", "bluerabbit"); ?></span>
									<?php } else { ?>
									<span class="br-badge br-badge-red"><span class="icon icon-cancel"></span> <?php _e("Fail", "bluerabbit"); ?></span>
									<?php } ?>
								</td>
								<td class="text-center">
									<?php if ($a->attempt_grade !== null) {
										$grade_class = $a->attempt_grade >= 70 ? 'br-grade-good' : ($a->attempt_grade >= 50 ? 'br-grade-mid' : 'br-grade-bad');
									?>
									<span class="<?= $grade_class; ?>"><?= $a->attempt_grade; ?>%</span>
									<?php } else { echo '&mdash;'; } ?>
								</td>
								<td>
									<?= date('M j, Y', strtotime($a->attempt_date)); ?>
									<span class="br-text-12-muted"><?= date('g:i A', strtotime($a->attempt_date)); ?></span>
								</td>
								<?php if ($canEdit) { ?>
								<td>
									<button class="br-btn br-btn-red" onClick="event.stopPropagation(); br_confirm_trd('trash',<?= $a->attempt_id; ?>,'attempt');">
										<span class="icon icon-trash"></span> <?php _e("Trash", "bluerabbit"); ?>
									</button>
									<input type="hidden" class="quest-id" value="<?= $a->attempt_id; ?>">
								</td>
								<?php } ?>
							</tr>
							<tr class="br-accordion-body">
								<td colspan="<?= $canEdit ? 5 : 4; ?>">
									<?php
									$has_answers = false;
									foreach ($attempt_answers as $aa) {
										if ($aa->attempt_id != $a->attempt_id) continue;
										$has_answers = true;
									?>
									<div class="br-qa-block">
										<div class="br-qa-question"><?= esc_html($aa->question_title); ?></div>
										<div class="br-qa-answer">
										<?php
										if ($aa->answer_value) {
											$answer_ids = array_filter(array_map('intval', explode(',', (string) $aa->answer_value)));
											if ($answer_ids) {
												$in = implode(',', $answer_ids);
												$a_results = $wpdb->get_results("SELECT answer_value, answer_correct FROM {$wpdb->prefix}br_challenge_answers WHERE answer_id IN ($in)");
												foreach ($a_results as $ar) {
													$pill = $ar->answer_correct ? 'br-answer-correct' : 'br-answer-wrong';
													$ico  = $ar->answer_correct ? 'icon-check' : 'icon-cancel';
													echo '<span class="br-answer-pill '.$pill.'"><span class="icon '.$ico.'"></span> ' . esc_html($ar->answer_value) . '</span>';
												}
											}
										} else {
											$pill = $aa->answer_correct ? 'br-answer-correct' : 'br-answer-wrong';
											$ico  = $aa->answer_correct ? 'icon-check' : 'icon-cancel';
											echo '<span class="br-answer-pill '.$pill.'"><span class="icon '.$ico.'"></span> ' . esc_html($aa->c_answer_value) . '</span>';
										}
										?>
										</div>
									</div>
									<?php }
									if (!$has_answers) { ?>
									<div class="br-empty-state"><span class="icon icon-challenge"></span> <?php _e("No stored answers for this attempt", "bluerabbit"); ?></div>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php } else { ?>
			<div class="br-panel br-empty">
				<span class="icon icon-challenge"></span>
				<h3><?php _e("No attempts found", "bluerabbit"); ?></h3>
				<p><?php _e("Attempt challenges to see your results here.", "bluerabbit"); ?></p>
			</div>
			<?php } ?>
		</div>

		<!-- ═══════════════ CASE STUDIES ═══════════════ -->
		<?php if ($cs_attempts) { ?>
		<div class="br-panel-group br-initially-hidden" id="pw-casestudies">
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-document"></span> <?php _e("Case Study Attempts", "bluerabbit"); ?></h3>
				<p class="br-form-hint"><?php _e("Every run of an embedded case study, passed or failed, newest first. Click one to see how each question went. Retaking never removes an earlier attempt.", "bluerabbit"); ?></p>
				<div class="br-table-scroll">
					<table class="br-table">
						<thead>
							<tr>
								<th><?php _e("Case Study", "bluerabbit"); ?></th>
								<th class="text-center"><?php _e("Attempt", "bluerabbit"); ?></th>
								<th class="text-center"><?php _e("Status", "bluerabbit"); ?></th>
								<th class="text-center"><?php _e("Score", "bluerabbit"); ?></th>
								<th><?php _e("Date", "bluerabbit"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($cs_attempts as $csa) {
								$cs_answers = $csa->attempt_answers ? json_decode($csa->attempt_answers, true) : [];
								if (!is_array($cs_answers)) $cs_answers = [];
							?>
							<tr class="br-accordion-header" onclick="brToggleAccordion(this)">
								<td>
									<span class="br-accordion-arrow"></span>
									<?= esc_html($csa->step_title ?: $csa->quest_title ?: __("Case study", "bluerabbit")); ?>
									<?php if ($csa->step_title && $csa->quest_title) { ?>
									<div class="br-text-12-muted"><?= esc_html($csa->quest_title); ?></div>
									<?php } ?>
								</td>
								<td class="text-center">#<?= (int) $csa->attempt_no; ?></td>
								<td class="text-center">
									<?php
									// Four outcomes now, not two: a run can also still be
									// open, or have been left without ever being finished.
									$cs_badges = [
										'success'     => ['br-badge-green', 'icon-check',  __("Pass", "bluerabbit")],
										'fail'        => ['br-badge-red',   'icon-cancel', __("Fail", "bluerabbit")],
										'in_progress' => ['br-badge-blue',  'icon-rotate', __("In progress", "bluerabbit")],
										'abandoned'   => ['br-badge-amber', 'icon-cancel', __("Abandoned", "bluerabbit")],
									];
									list($cs_bcls, $cs_bico, $cs_blabel) = $cs_badges[$csa->attempt_status] ?? $cs_badges['fail'];
									?>
									<span class="br-badge <?= $cs_bcls; ?>"><span class="icon <?= $cs_bico; ?>"></span> <?= esc_html($cs_blabel); ?></span>
									<?php if (in_array($csa->attempt_status, ['abandoned', 'in_progress'], true) && $csa->total_questions) { ?>
									<div class="br-text-12-muted"><?= sprintf(__("reached %d of the questions", "bluerabbit"), (int) $csa->total_questions); ?></div>
									<?php } ?>
								</td>
								<td class="text-center">
									<?php if ($csa->attempt_score !== null) {
										$cs_grade_class = $csa->attempt_score >= 70 ? 'br-grade-good' : ($csa->attempt_score >= 50 ? 'br-grade-mid' : 'br-grade-bad');
									?>
									<span class="<?= $cs_grade_class; ?>"><?= (int) $csa->attempt_score; ?>%</span>
									<?php if ($csa->total_questions) { ?>
									<div class="br-text-12-muted"><?= (int) $csa->correct_count; ?>/<?= (int) $csa->total_questions; ?></div>
									<?php } ?>
									<?php } else { echo '&mdash;'; } ?>
								</td>
								<td>
									<?= date('M j, Y', strtotime($csa->attempt_date)); ?>
									<span class="br-text-12-muted"><?= date('g:i A', strtotime($csa->attempt_date)); ?></span>
								</td>
							</tr>
							<tr class="br-accordion-body">
								<td colspan="5">
									<?php if ($cs_answers) {
										$cs_qn = 0;
										foreach ($cs_answers as $cs_key => $cs_q) {
											$cs_qn++;
											// The activity owns this shape, so read what is useful and fall
											// back to showing the raw values rather than assuming fields
											// that may not exist in a future version of the HTML.
											$cs_q     = is_array($cs_q) ? $cs_q : ['value' => $cs_q];
											$cs_right = !empty($cs_q['correct']);
											$cs_label = '';
											foreach (['question', 'title', 'prompt', 'text'] as $cs_f) {
												if (!empty($cs_q[$cs_f]) && is_scalar($cs_q[$cs_f])) { $cs_label = (string) $cs_q[$cs_f]; break; }
											}
											if ($cs_label === '') $cs_label = sprintf(__("Question %s", "bluerabbit"), is_numeric($cs_key) ? $cs_qn : $cs_key);

											$cs_given = '';
											foreach (['answer', 'selected', 'choice', 'value', 'response'] as $cs_f) {
												if (isset($cs_q[$cs_f]) && $cs_q[$cs_f] !== '' && $cs_q[$cs_f] !== null) {
													$cs_given = is_array($cs_q[$cs_f]) ? implode(', ', array_map('strval', $cs_q[$cs_f])) : (string) $cs_q[$cs_f];
													break;
												}
											}
									?>
									<div class="br-qa-block">
										<div class="br-qa-question"><?= esc_html(wp_strip_all_tags($cs_label)); ?></div>
										<div class="br-qa-answer">
											<span class="br-answer-pill <?= $cs_right ? 'br-answer-correct' : 'br-answer-wrong'; ?>">
												<span class="icon <?= $cs_right ? 'icon-check' : 'icon-cancel'; ?>"></span>
												<?= $cs_given !== '' ? esc_html($cs_given) : ($cs_right ? __("Correct", "bluerabbit") : __("Incorrect", "bluerabbit")); ?>
											</span>
										</div>
									</div>
									<?php } } else { ?>
									<div class="br-empty-state"><span class="icon icon-document"></span> <?php _e("This attempt recorded a score but no per-question detail", "bluerabbit"); ?></div>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>

		<!-- ═══════════════ ACHIEVEMENTS ═══════════════ -->
		<?php if (!empty($pw_achievements)) { ?>
		<div class="br-panel-group br-initially-hidden" id="pw-achievements">
			<div class="br-panel">
				<h3 class="br-panel-title">
					<span class="icon icon-achievement"></span> <?php _e("Achievements", "bluerabbit"); ?>
					<span class="br-badge br-badge-purple"><?= $pw_earned_ach; ?>/<?= count($pw_achievements); ?></span>
				</h3>
				<div class="br-stats-achievements-grid">
					<?php foreach ($pw_achievements as $pa) { ?>
					<div class="br-stats-achievement <?= $pa['earned_at'] ? 'earned' : 'locked'; ?>">
						<?php if ($pa['achievement_badge']) { ?>
						<img src="<?= esc_url($pa['achievement_badge']); ?>" alt="<?= esc_attr($pa['achievement_name']); ?>" loading="lazy">
						<?php } else { ?>
						<div class="br-stats-achievement-placeholder <?= esc_attr($pa['achievement_color']); ?>">
							<span class="icon icon-achievement"></span>
						</div>
						<?php } ?>
						<span class="br-stats-achievement-name"><?= esc_html($pa['achievement_name']); ?></span>
						<?php if ($pa['earned_at']) { ?>
						<span class="br-stats-achievement-date"><?= date('M j', strtotime($pa['earned_at'])); ?></span>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>

		<!-- ═══════════════ RESET DEMO ═══════════════ -->
		<?php if ($isDemo && $is_self) { ?>
		<div class="br-panel-group br-initially-hidden" id="pw-reset">
			<div class="br-panel br-empty">
				<span class="icon icon-rotate"></span>
				<h3><?php _e("Reset Demo", "bluerabbit"); ?></h3>
				<p class="br-mb-md"><?php _e("This will reset all your progress.", "bluerabbit"); ?></p>
				<button class="br-btn br-btn-red" onClick="showOverlay('#reset-demo-form');">
					<span class="icon icon-rotate"></span> <?php _e("Reset Demo", "bluerabbit"); ?>
				</button>
			</div>
		</div>
		<?php } ?>

	</div>
</div>

<input type="hidden" id="reload" value="true">
<input type="hidden" id="trd-player-id" value="<?= $the_player_id; ?>">
<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>">
<input type="hidden" id="delete-nonce" value="<?= wp_create_nonce('delete_nonce'); ?>">
<input type="hidden" id="publish-nonce" value="<?= wp_create_nonce('publish_nonce'); ?>">

<script>
function brToggleAccordion(row) {
	var body = row.nextElementSibling;
	if (body && body.classList.contains('br-accordion-body')) {
		var open = body.style.display === 'table-row';
		body.style.display = open ? 'none' : 'table-row';
		row.classList.toggle('br-accordion-open', !open);
	}
}
</script>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

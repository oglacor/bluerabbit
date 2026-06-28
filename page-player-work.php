<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if (($isGM || $isAdmin || $isNPC) && isset($_GET['player_id'])) {
	$the_player_id = (int) $_GET['player_id'];
	$view_player   = BR_Player::instance()->getPlayerData($the_player_id);
} else {
	$the_player_id = $current_user->ID;
	$view_player   = $current_player;
}

$myquests = $wpdb->get_results("SELECT
	a.pp_grade, a.pp_modified, a.quest_id, a.pp_status,
	b.quest_title, b.quest_type,
	b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge, b.quest_success_message
	FROM {$wpdb->prefix}br_player_posts a
	LEFT JOIN {$wpdb->prefix}br_quests b ON a.quest_id = b.quest_id
	WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id AND b.quest_status='publish'
	ORDER BY a.pp_modified
");

$mysurveys = $wpdb->get_results("SELECT surveys.*
	FROM {$wpdb->prefix}br_survey_answers answers
	JOIN  {$wpdb->prefix}br_quests surveys
		ON surveys.quest_id = answers.survey_id AND surveys.quest_status='publish'
	JOIN  {$wpdb->prefix}br_survey_questions questions
		ON surveys.quest_id = questions.survey_id AND questions.survey_question_status='publish'
	WHERE surveys.adventure_id=$adventure_id AND answers.player_id=$the_player_id
		AND (answers.survey_option_id > 0 OR answers.survey_answer_value!='')
	GROUP BY answers.survey_id
");

$attempts = $wpdb->get_results("SELECT
	a.attempt_grade, a.attempt_answers, a.quest_id, a.attempt_id, a.attempt_date, a.attempt_status,
	b.quest_title, b.quest_type, b.quest_id,
	b.mech_level, b.mech_xp, b.mech_bloo, b.mech_badge
	FROM {$wpdb->prefix}br_challenge_attempts a
	LEFT JOIN {$wpdb->prefix}br_quests b ON a.quest_id = b.quest_id
	WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id
		AND b.quest_type='challenge' AND a.attempt_status != 'trash'
");

$attempt_answers = $wpdb->get_results("SELECT a.*, b.answer_value AS c_answer_value, b.answer_correct, c.question_title
	FROM {$wpdb->prefix}br_challenge_attempt_answers a
	LEFT JOIN {$wpdb->prefix}br_challenge_answers b ON a.answer_id = b.answer_id
	LEFT JOIN {$wpdb->prefix}br_challenge_questions c ON a.question_id = c.question_id
	LEFT JOIN {$wpdb->prefix}br_quests d ON c.quest_id = d.quest_id
	WHERE d.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id
");
?>

<div class="br-page">

	<!-- Hero Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background-image:url(<?= esc_url($view_player->player_picture ?? ''); ?>)"></div>
		<div>
			<h1 class="br-page-title"><?php _e("My Work", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($view_player->display_name ?? $view_player->player_display_name ?? ''); ?></span>
		</div>
	</div>

	<!-- Sticky Nav -->
	<div class="br-tabs br-tabs-sticky" id="post-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('quests-section', this)">
			<span class="icon icon-quest"></span> <?php _e("Quests", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('challenges-section', this)">
			<span class="icon icon-challenge"></span> <?php _e("Challenges", "bluerabbit"); ?>
		</button>
		<?php if ($use_surveys) { ?>
		<button class="br-tab-btn" onClick="brScrollTo('surveys-section', this)">
			<span class="icon icon-survey"></span> <?php _e("Surveys", "bluerabbit"); ?>
		</button>
		<?php } ?>
		<?php if ($isDemo) { ?>
		<button class="br-tab-btn" onClick="brScrollTo('reset-section', this)">
			<span class="icon icon-rotate"></span> <?php _e("Reset", "bluerabbit"); ?>
		</button>
		<?php } ?>
	</div>

	<!-- All sections visible, scroll-targeted -->
	<div id="post-tabs">

		<!-- ═══ Quests ═══ -->
		<div class="br-scroll-section" id="quests-section">
			<?php if ($myquests) { ?>
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-quest"></span> <?php _e("Quests", "bluerabbit"); ?></h3>
				<table class="br-table">
					<thead>
						<tr>
							<th class="text-center"><?php _e("Lvl", "bluerabbit"); ?></th>
							<th><?php _e("Name", "bluerabbit"); ?></th>
							<th class="text-center"><?= $xp_label; ?></th>
							<th class="text-center"><?= $bloo_label; ?></th>
							<th><?php _e("Actions", "bluerabbit"); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($myquests as $q) {
							if ($q->quest_type !== 'quest') continue;
						?>
						<tr>
							<td class="text-center"><?= $q->mech_level; ?></td>
							<td>
								<a href="<?= get_bloginfo('url') . "/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>">
									<?= esc_html($q->quest_title); ?>
								</a>
							</td>
							<td class="text-center"><?= $q->mech_xp; ?></td>
							<td class="text-center"><?= $q->mech_bloo; ?></td>
							<td>
								<div class="br-actions">
								<?php if ($q->pp_status == 'publish') { ?>
									<a href="<?= get_bloginfo('url') . "/post/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>" class="br-btn">
										<span class="icon icon-<?= $q->quest_type; ?>"></span> <?php _e("View", "bluerabbit"); ?>
									</a>
									<?php if (!$q->pp_grade) { ?>
									<a href="<?= get_bloginfo('url') . "/$q->quest_type/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>" class="br-btn br-btn-green">
										<span class="icon icon-edit"></span> <?php _e("Edit", "bluerabbit"); ?>
									</a>
									<?php if ($isGM || $isAdmin || $isNPC) { ?>
									<button class="br-btn br-btn-red" onClick="br_confirm_trd('trash',<?= $q->quest_id; ?>,'player_post');">
										<span class="icon icon-trash"></span> <?php _e("Trash", "bluerabbit"); ?>
									</button>
									<?php } ?>
									<?php } else { ?>
									<span class="br-badge br-badge-green"><?php _e("Graded", "bluerabbit"); ?></span>
									<?php } ?>
								<?php } else { ?>
									<?php if ($isGM || $isAdmin || $isNPC) { ?>
									<button class="br-btn" onClick="br_confirm_trd('publish',<?= $q->quest_id; ?>,'player_post');">
										<span class="icon icon-restore"></span> <?php _e("Restore", "bluerabbit"); ?>
									</button>
									<button class="br-btn br-btn-red" onClick="br_confirm_trd('delete',<?= $q->quest_id; ?>,'player_post');">
										<span class="icon icon-trash"></span> <?php _e("Delete", "bluerabbit"); ?>
									</button>
									<?php } ?>
								<?php } ?>
								</div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<?php } else { ?>
			<div class="br-panel br-empty">
				<span class="icon icon-quest"></span>
				<h3><?php _e("No quests found", "bluerabbit"); ?></h3>
				<p><?php _e("Complete quests to see your results here.", "bluerabbit"); ?></p>
			</div>
			<?php } ?>
		</div>

		<!-- ═══ Challenges ═══ -->
		<div class="br-scroll-section" id="challenges-section">
			<?php if ($attempts) { ?>
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-challenge"></span> <?php _e("Challenge Attempts", "bluerabbit"); ?></h3>
				<table class="br-table">
					<thead>
						<tr>
							<th><?php _e("Challenge", "bluerabbit"); ?></th>
							<th class="text-center"><?php _e("Status", "bluerabbit"); ?></th>
							<th class="text-center"><?php _e("Grade", "bluerabbit"); ?></th>
							<th><?php _e("Date", "bluerabbit"); ?></th>
							<?php if ($isGM || $isAdmin || $isNPC) { ?>
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
								<?php if ($a->attempt_grade !== null) { ?>
								<span style="color:<?= $a->attempt_grade >= 70 ? '#24da98' : ($a->attempt_grade >= 50 ? '#ffc107' : '#f44336'); ?>">
									<?= $a->attempt_grade; ?>%
								</span>
								<?php } else { echo '&mdash;'; } ?>
							</td>
							<td>
								<?php
								$date = date('M j, Y', strtotime($a->attempt_date));
								$time = date('g:i A', strtotime($a->attempt_date));
								echo "$date <span style='color:rgba(255,255,255,0.35)'>$time</span>";
								?>
							</td>
							<?php if ($isGM || $isAdmin || $isNPC) { ?>
							<td>
								<button class="br-btn br-btn-red" onClick="event.stopPropagation(); br_confirm_trd('trash',<?= $a->attempt_id; ?>,'attempt');">
									<span class="icon icon-trash"></span> <?php _e("Trash", "bluerabbit"); ?>
								</button>
								<input type="hidden" class="quest-id" value="<?= $a->attempt_id; ?>">
							</td>
							<?php } ?>
						</tr>
						<tr class="br-accordion-body">
							<td colspan="<?= ($isGM || $isAdmin || $isNPC) ? 5 : 4; ?>">
								<?php foreach ($attempt_answers as $aa) {
									if ($aa->attempt_id != $a->attempt_id) continue;
								?>
								<div class="br-qa-block">
									<div class="br-qa-question"><?= esc_html($aa->question_title); ?></div>
									<div class="br-qa-answer">
									<?php
									if ($aa->answer_value) {
										$a_results = $wpdb->get_results("SELECT answer_value, answer_correct FROM {$wpdb->prefix}br_challenge_answers WHERE answer_id IN ($aa->answer_value)");
										foreach ($a_results as $ar) {
											if ($ar->answer_correct) {
												echo '<span class="br-answer-pill br-answer-correct"><span class="icon icon-check"></span> ' . esc_html($ar->answer_value) . '</span>';
											} else {
												echo '<span class="br-answer-pill br-answer-wrong"><span class="icon icon-cancel"></span> ' . esc_html($ar->answer_value) . '</span>';
											}
										}
									} else {
										if ($aa->answer_correct) {
											echo '<span class="br-answer-pill br-answer-correct"><span class="icon icon-check"></span> ' . esc_html($aa->c_answer_value) . '</span>';
										} else {
											echo '<span class="br-answer-pill br-answer-wrong"><span class="icon icon-cancel"></span> ' . esc_html($aa->c_answer_value) . '</span>';
										}
									}
									?>
									</div>
								</div>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<?php } else { ?>
			<div class="br-panel br-empty">
				<span class="icon icon-challenge"></span>
				<h3><?php _e("No attempts found", "bluerabbit"); ?></h3>
				<p><?php _e("Attempt challenges to see your results here.", "bluerabbit"); ?></p>
			</div>
			<?php } ?>
		</div>

		<!-- ═══ Surveys ═══ -->
		<div class="br-scroll-section" id="surveys-section">
			<?php if ($mysurveys) { ?>
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-survey"></span> <?php _e("Survey Answers", "bluerabbit"); ?></h3>
				<table class="br-table">
					<thead>
						<tr>
							<th class="text-center"><?php _e("Lvl", "bluerabbit"); ?></th>
							<th><?php _e("Name", "bluerabbit"); ?></th>
							<th class="text-center"><?= $xp_label; ?></th>
							<th class="text-center"><?= $bloo_label; ?></th>
							<th><?php _e("Actions", "bluerabbit"); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($mysurveys as $s) { ?>
						<tr>
							<td class="text-center"><?= $s->mech_level; ?></td>
							<td>
								<a href="<?= get_bloginfo('url') . "/survey/?adventure_id=$adventure->adventure_id&questID=$s->quest_id"; ?>">
									<?= esc_html($s->quest_title); ?>
								</a>
							</td>
							<td class="text-center"><?= $s->mech_xp; ?></td>
							<td class="text-center"><?= $s->mech_bloo; ?></td>
							<td>
								<div class="br-actions">
									<a href="<?= get_bloginfo('url') . "/survey/?adventure_id=$adventure->adventure_id&questID=$s->quest_id"; ?>" class="br-btn">
										<span class="icon icon-survey"></span> <?php _e("View", "bluerabbit"); ?>
									</a>
									<?php if ($isGM || $isAdmin || $isNPC) { ?>
									<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-delete-<?= $s->quest_id; ?>');">
										<span class="icon icon-delete"></span> <?php _e("Delete", "bluerabbit"); ?>
									</button>
									<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?= $s->quest_id; ?>">
										<button class="form-ui grey-bg-800 delete-confirm-button" onClick="confirmStatus(<?= $s->quest_id; ?>,'survey-answer','delete');">
											<span class="icon-group">
												<span class="button-icon font _24 sq-40 icon-sm red-bg-A400">
													<span class="icon icon-delete white-color"></span>
												</span>
												<span class="icon-content">
													<span class="line white-color font _18 w900"><?php _e("Are you sure?", "bluerabbit"); ?></span>
													<span class="line white-color font _14 w300"><?php _e("You can't undo this", "bluerabbit"); ?></span>
												</span>
											</span>
										</button>
										<button class="close-confirm button-icon font _24 sq-40 blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
											<span class="icon icon-cancel white-color"></span>
										</button>
									</div>
									<?php } ?>
								</div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<?php } else { ?>
			<div class="br-panel br-empty">
				<span class="icon icon-survey"></span>
				<h3><?php _e("No survey answers found", "bluerabbit"); ?></h3>
				<p><?php _e("Answer surveys to see your results here.", "bluerabbit"); ?></p>
			</div>
			<?php } ?>
		</div>

		<!-- ═══ Reset Demo ═══ -->
		<?php if ($isDemo) { ?>
		<div class="br-scroll-section" id="reset-section">
			<div class="br-panel br-empty">
				<span class="icon icon-rotate"></span>
				<h3><?php _e("Reset Demo", "bluerabbit"); ?></h3>
				<p style="margin-bottom:16px"><?php _e("This will reset all your progress.", "bluerabbit"); ?></p>
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

function brScrollTo(id, btn) {
	document.querySelectorAll('#post-tabs-buttons .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('#post-tabs-buttons .br-tab-btn');
	if (!sections.length || !buttons.length) return;

	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			var id = entry.target.id;
			buttons.forEach(function(b, i) {
				b.classList.toggle('active', sections[i] && sections[i].id === id);
			});
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });

	sections.forEach(function(s) { observer.observe(s); });
})();
</script>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

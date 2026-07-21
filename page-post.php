<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$questID = br_require_id('questID');
	$uID = br_require_id('uID', false);
	if( $uID && $isGM){
		$player_id = $uID;
	}else{
		$player_id =  $current_user->ID;
	}
	$q = $wpdb->get_row("SELECT
	a.*,
	d.pp_content, d.pp_grade, d.pp_gm_comment, d.player_id, d.pp_date, d.pp_modified, d.pp_quest_rating

	FROM {$wpdb->prefix}br_quests a
	LEFT JOIN {$wpdb->prefix}br_player_posts d
	ON a.quest_id = d.quest_id AND d.player_id=$player_id

	WHERE a.adventure_id=$adventure->adventure_id AND a.quest_status='publish' AND a.quest_id=$questID");

	$requirements = $wpdb->get_results("SELECT

	a.req_object_id, a.req_type, a.req_object_id,
	b.mech_level, b.mech_xp, b.mech_bloo,
	c.quest_title, c.quest_type, c.mech_badge,
	d.item_name

	FROM {$wpdb->prefix}br_reqs a
	LEFT JOIN {$wpdb->prefix}br_quests b
	ON a.quest_id = b.quest_id AND b.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_quests c
	ON a.req_object_id = c.quest_id AND c.quest_status='publish'
	LEFT JOIN {$wpdb->prefix}br_items d
	ON a.req_object_id = d.item_id

	WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$questID

	");
	if($requirements){
		$reqs = array(); $reqs_ids = array();
		foreach($requirements as $r){
			$reqs[]=$r;
			$reqs_ids[$r->req_type][]=$r->req_object_id;
		}
		sort($reqs_ids['quest']);
		$reqs_list = implode(",",$reqs_ids['quest']);
		$player_quests = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_player_posts
		WHERE adventure_id=$adventure->adventure_id AND pp_status='publish' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");
		$player_challenges = $wpdb->get_col("SELECT quest_id FROM {$wpdb->prefix}br_challenge_attempts
		WHERE adventure_id=$adventure->adventure_id AND attempt_status='success' AND player_id=$current_user->ID AND quest_id IN ($reqs_list)");

		$player_work = array_merge($player_quests,$player_challenges);sort($player_work);
	}
	$item_reward = null;
	$achievement_reward = null;
	if($q->mech_item_reward){
		$item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id={$q->mech_item_reward} AND item_type='reward' AND item_status='publish'");
	}
	if($q->mech_achievement_reward){
		$achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id={$q->mech_achievement_reward} AND achievement_status='publish'");
	}

	$isChallenge = ($q && $q->quest_type == 'challenge');
	if($isChallenge){
		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_attempts WHERE quest_id=$questID AND player_id=$player_id AND (attempt_status='success' OR attempt_status='fail')");
		$grades=array();
		foreach($attempts as $att){
			$grades[]=$att->attempt_grade;
		}
		$best_grade = $grades ? max($grades) : null;
	}
?>
<?php if($q){ ?>

<div class="br-page">

	<!-- Hero -->
	<div class="br-panel br-page-header">
		<?php if($uID && $isGM){ ?>
		<a class="br-btn" href="<?= get_bloginfo('url')."/review-player-posts/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>">
			<span class="icon icon-arrow-left"></span> <?= __("Back to Review","bluerabbit"); ?>
		</a>
		<?php }else{ ?>
		<a class="br-btn" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
			<span class="icon icon-arrow-left"></span> <?= __("Back to Journey","bluerabbit"); ?>
		</a>
		<?php } ?>
		<div class="br-page-header-avatar" style="background-image:url(<?= esc_url($q->mech_badge); ?>)"></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html(ucfirst($q->quest_type)); ?></div>
			<h1 class="br-page-title"><?= esc_html($q->quest_title); ?></h1>
		</div>
	</div>

	<!-- Sticky nav -->
	<div class="br-tabs br-tabs-sticky" id="post-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('summary-section', this)">
			<span class="icon icon-quest"></span> <?= __("Summary","bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('answer-section', this)">
			<span class="icon icon-<?= $isChallenge ? 'challenge' : 'edit'; ?>"></span>
			<?= $isChallenge ? __("Results","bluerabbit") : __("Your Answer","bluerabbit"); ?>
		</button>
		<?php if($q->quest_content){ ?>
		<button class="br-tab-btn" onClick="brScrollTo('instructions-section', this)">
			<span class="icon icon-story"></span> <?= __("Instructions","bluerabbit"); ?>
		</button>
		<?php } ?>
		<?php if($q->quest_success_message){ ?>
		<button class="br-tab-btn" onClick="brScrollTo('message-section', this)">
			<span class="icon icon-check"></span> <?= __("Message","bluerabbit"); ?>
		</button>
		<?php } ?>
	</div>

	<div id="post-tabs">

		<!-- ═══ Summary ═══ -->
		<div class="br-scroll-section" id="summary-section">
			<div class="br-panel br-text-center">

				<div class="br-flex br-flex-center br-gap-md br-flex-wrap" style="justify-content:center;">
					<div class="br-summary-stat">
						<span class="icon icon-star"></span>
						<div>
							<span class="br-stat-val"><?= BR_Utils::instance()->toMoney($q->mech_xp,""); ?></span>
							<span class="br-stat-label"><?= $xp_label; ?></span>
						</div>
					</div>
					<div class="br-summary-stat">
						<span class="icon icon-bloo"></span>
						<div>
							<span class="br-stat-val"><?= BR_Utils::instance()->toMoney($q->mech_bloo,""); ?></span>
							<span class="br-stat-label"><?= $bloo_label; ?></span>
						</div>
					</div>
				</div>

				<?php if($achievement_reward){ ?>
					<div class="br-reward-card br-reward-card-purple">
						<img src="<?= esc_url($achievement_reward->achievement_badge); ?>" onClick="loadAchievementCard(<?= $achievement_reward->achievement_id; ?>);" style="cursor:pointer;">
						<div class="text-left">
							<div class="br-reward-label"><?= __("You earned an achievement","bluerabbit"); ?></div>
							<div class="br-reward-name"><?= esc_html($achievement_reward->achievement_name); ?></div>
						</div>
					</div>
				<?php } ?>
				<?php if($item_reward){ ?>
					<div class="br-reward-card br-reward-card-teal">
						<img src="<?= esc_url($item_reward->item_badge); ?>">
						<div class="text-left">
							<div class="br-reward-label"><?= __("You found an item","bluerabbit"); ?></div>
							<div class="br-reward-name"><?= esc_html($item_reward->item_name); ?></div>
						</div>
						<a class="br-btn br-btn-teal br-ml-auto" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adventure->adventure_id"; ?>">
							<span class="icon icon-backpack"></span> <?= __("Backpack","bluerabbit"); ?>
						</a>
					</div>
				<?php } ?>

				<?php if($requirements){ ?>
					<?php
						$my_items = BR_Item::instance()->getMyItems($adventure->adventure_id);
						$myKeyItems = $my_items['ids']['key'];
					?>
					<?php if(!empty($reqs)){ ?>
						<?php
							$my_quests = BR_Quest::instance()->getMyQuests($adventure->adventure_id);
							$my_achievements = BR_Achievement::instance()->getMyAchievements($adventure->adventure_id);
						?>
						<h3 class="br-panel-title br-mt-md"><span class="icon icon-lock"></span> <?= __("Requirements","bluerabbit"); ?></h3>
						<div class="card-deck">
							<?php foreach($reqs as $r){
								if($r->req_type=="quest"){
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $player_work) ? true : false;
									include (TEMPLATEPATH . '/req-milestone.php');
								}elseif($r->req_type=="item") {
									$mi = $r;
									$isFinished = in_array($r->req_object_id, $myKeyItems) ? true : false;
									include (TEMPLATEPATH . '/req-item.php');
								}elseif($r->req_type=="achievement") {
									$a = $r;
									$isEarned = in_array($r->req_object_id, $my_achievements) ? true : false;
									include (TEMPLATEPATH . '/req-achievement.php');
								}
							} ?>
						</div>
					<?php } ?>
				<?php } ?>

				<?php if(!empty($config['rate_quests']['value']) && !$isChallenge){ ?>
					<div class="br-mt-md">
						<h3 class="br-panel-title"><span class="icon icon-star"></span> <?= __("Rate this quest please!","bluerabbit"); ?></h3>
						<div class="br-flex br-flex-center br-gap-sm" style="justify-content:center;">
							<?php for($i=1;$i<=5;$i++){ ?>
							<button id="rating-star-<?= $i; ?>" class="br-icon-btn <?= $q->pp_quest_rating >= $i ? 'br-icon-btn-amber' : ''; ?>" onClick="rateQuest(<?= $q->quest_id; ?>,<?= $i; ?>);">
								<span class="icon icon-star"></span>
							</button>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

		<!-- ═══ Answer / Results ═══ -->
		<div class="br-scroll-section" id="answer-section">
			<?php if($isChallenge){ ?>
				<div class="br-panel">
					<h3 class="br-panel-title"><span class="icon icon-challenge"></span> <?= __("Challenge","bluerabbit"); ?></h3>
					<div class="br-flex br-flex-center br-gap-md br-flex-wrap br-mb-md">
						<div class="br-summary-stat">
							<div>
								<span class="br-stat-val"><?= $q->mech_answers_to_win; ?></span>
								<span class="br-stat-label"><?= __("Answers","bluerabbit"); ?></span>
							</div>
						</div>
						<div class="br-summary-stat">
							<div>
								<span class="br-stat-val"><?= $q->mech_max_attempts > 0 ? $q->mech_max_attempts : '<span class="icon icon-infinite"></span>'; ?></span>
								<span class="br-stat-label"><?= __("Max Attempts","bluerabbit"); ?></span>
							</div>
						</div>
						<div class="br-summary-stat">
							<div>
								<span class="br-stat-val"><?= $q->mech_time_limit > 0 ? $q->mech_time_limit : '<span class="icon icon-infinite"></span>'; ?></span>
								<span class="br-stat-label"><?= __("Time Limit","bluerabbit"); ?></span>
							</div>
						</div>
						<?php if($best_grade !== null){ ?>
						<div class="br-summary-stat">
							<div>
								<span class="br-stat-val"><?= $best_grade; ?></span>
								<span class="br-stat-label"><?= __("Your best grade","bluerabbit"); ?></span>
							</div>
						</div>
						<?php } ?>
					</div>

					<?php if($attempts){ ?>
						<?php
							if($q->mech_questions_to_display > 0){
								$totalqs = $q->mech_questions_to_display;
							}else{
								$qs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_challenge_questions a WHERE quest_id=$q->quest_id");
								$totalqs = count($qs);
							}
						?>
						<table class="br-table">
							<thead>
								<tr>
									<th><?= __("Attempt","bluerabbit"); ?></th>
									<th><?= __("Answers","bluerabbit"); ?></th>
									<th><?= __("Grade","bluerabbit"); ?></th>
									<th><?= __("Date","bluerabbit"); ?></th>
									<th class="text-center"><?= __("Status","bluerabbit"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($attempts as $key=>$att){ ?>
									<tr>
										<td>#<?= $key+1; ?></td>
										<td><strong><?= $att->attempt_answers; ?>/<?= $totalqs; ?></strong></td>
										<td><?= $att->attempt_grade; ?></td>
										<td><?= date('D, M jS, Y',strtotime($att->attempt_date)); ?></td>
										<td class="text-center">
											<?php if($att->attempt_status=='success'){ ?>
												<span class="br-badge br-badge-green"><span class="icon icon-check"></span> <?= __("Pass","bluerabbit"); ?></span>
											<?php }else{ ?>
												<span class="br-badge br-badge-red"><span class="icon icon-cancel"></span> <?= __("Fail","bluerabbit"); ?></span>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php }else{ ?>
						<div class="br-empty-state">
							<span class="icon icon-challenge"></span> <?= __("No attempts yet","bluerabbit"); ?>
						</div>
					<?php } ?>
				</div>
			<?php }else{ ?>
				<div class="br-panel">
					<div class="br-flex br-flex-center br-mb-md">
						<div class="br-flex-1">
							<h3 class="br-panel-title"><span class="icon icon-edit"></span> <?= __("Your answer","bluerabbit"); ?></h3>
							<?php if($q->player_id){ ?>
								<?php if($q->pp_date == $q->pp_modified){ ?>
									<span class="br-text-12-muted"><?= __("Published","bluerabbit"); ?>: <strong><?= esc_html($q->pp_date); ?></strong></span>
								<?php }else{ ?>
									<span class="br-text-12-muted"><?= __("Modified","bluerabbit"); ?>: <strong><?= esc_html($q->pp_modified); ?></strong></span>
								<?php } ?>
							<?php } ?>
						</div>
						<?php if($isGM && $adventure->adventure_grade_scale != 'none' && $q->player_id){ ?>
							<?php $the_grade = $q->pp_grade; ?>
							<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>"/>
							<div class="br-form-group">
								<label class="br-form-label"><span class="icon icon-progression"></span> <?= __("Grade","bluerabbit"); ?></label>
								<?php if($adventure->adventure_grade_scale == "percentage"){ ?>
									<input onChange="setGrade(<?= "$q->quest_id,$player_id"; ?>);" type="number" min="0" max="100" class="br-input" id="the_post_grade_<?= $q->quest_id; ?>_<?= $player_id; ?>" value="<?= $the_grade; ?>">
								<?php }elseif($adventure->adventure_grade_scale == "letters"){ ?>
									<select class="br-input" id="the_post_grade_<?= $q->quest_id; ?>_<?= $player_id; ?>" onChange="setGrade(<?= "$q->quest_id,$player_id"; ?>);">
										<option value="100" <?php if($the_grade == 100){ echo 'selected'; } ?>>A</option>
										<option value="91.75" <?php if($the_grade < 100 && $the_grade >= 91.75){ echo 'selected';  }?>>A-</option>
										<option value="83.25" <?php if($the_grade < 91.75 && $the_grade >= 83.25){ echo 'selected';  }?>>B+</option>
										<option value="75" <?php if($the_grade < 83.25 && $the_grade >= 75){ echo 'selected'; } ?>>B</option>
										<option value="66.75" <?php if($the_grade < 75 && $the_grade >= 66.75){ echo 'selected'; } ?>>B-</option>
										<option value="58.25" <?php if($the_grade < 66.75 && $the_grade >= 58.25){ echo 'selected'; } ?>>C+</option>
										<option value="50" <?php if($the_grade < 58.25 && $the_grade >= 50){ echo 'selected'; } ?>>C</option>
										<option value="25" <?php if($the_grade < 50 && $the_grade >= 25){ echo 'selected'; } ?>>D</option>
										<option value="0" <?php if($the_grade < 25 && $the_grade >= 0 || !$the_grade){ echo 'selected'; } ?>>F</option>
									</select>
								<?php } ?>
							</div>
						<?php } ?>
					</div>

					<?php if($q->player_id){ ?>
						<div class="br-tab-content">
							<?= br_render_post_content($q->pp_content, $q->pp_date); ?>
						</div>

						<?php if($q->pp_gm_comment){ ?>
							<div class="br-gm-comment br-mt-md">
								<span class="icon icon-comment"></span>
								<div>
									<div class="br-reward-label"><?= __("Feedback from your Game Master","bluerabbit"); ?></div>
									<div><?= nl2br(esc_html($q->pp_gm_comment)); ?></div>
								</div>
							</div>
						<?php } ?>

						<div class="br-mt-md text-right">
							<?php if($adventure->adventure_grade_scale == "none" || ($adventure->adventure_grade_scale != "none" && $adventure->adventure_progression_type=='before') || ($adventure->adventure_grade_scale != "none"  && !$q->pp_grade  && $adventure->adventure_progression_type=='after')) { ?>
								<a class="br-btn br-btn-green" href="<?= get_bloginfo('url').'/'.$q->quest_type.'/?adventure_id='.$adventure->adventure_id."&questID=".$q->quest_id;?>">
									<span class="icon icon-edit"></span> <?= __("Edit your answer","bluerabbit"); ?>
								</a>
							<?php }else{ ?>
								<span class="br-badge br-badge-amber"><?= __("This post has already been graded. Can't edit or delete.","bluerabbit"); ?></span>
							<?php } ?>
						</div>
					<?php }else{ ?>
						<div class="br-empty-state">
							<span class="icon icon-edit"></span> <?= __("Nothing submitted for this milestone yet","bluerabbit"); ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<!-- ═══ Instructions ═══ -->
		<?php if($q->quest_content){ ?>
		<div class="br-scroll-section" id="instructions-section">
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-story"></span> <?= __("Quest instructions","bluerabbit"); ?></h3>
				<div class="br-tab-content">
					<?= apply_filters('the_content',$q->quest_content); ?>
				</div>
			</div>
		</div>
		<?php } ?>

		<!-- ═══ Message ═══ -->
		<?php if($q->quest_success_message){ ?>
		<div class="br-scroll-section" id="message-section">
			<div class="br-panel">
				<h3 class="br-panel-title"><span class="icon icon-check"></span> <?= __("This information should help you on your journey!","bluerabbit"); ?></h3>
				<div class="br-tab-content">
					<?= apply_filters('the_content',$q->quest_success_message); ?>
				</div>
			</div>
		</div>
		<?php } ?>

	</div>

</div>

<script>
	jQuery(document).ready(function($) {
		$('img.size-full').removeAttr('width height').addClass('max-w-400');
	});

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

	document.addEventListener('DOMContentLoaded', function () {
		const containers = document.querySelectorAll('.dialogue-box');
		containers.forEach(container => {
			container.querySelectorAll('a').forEach(link => {
				link.setAttribute('target', '_blank');
				link.setAttribute('rel', 'noopener noreferrer');
			});
		});
	});
</script>
<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

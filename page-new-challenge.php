<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$questID = isset($_GET['questID']) ? (int) $_GET['questID'] : null;
$quests = $wpdb->get_results(" SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id ORDER BY mech_level ASC, quest_order ASC ");
$items = BR_Item::instance()->getItems($adventure->adventure_id);
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id);
$paths = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');
if($questID && $use_challenges){
	foreach($quests as $q){
		if($q->quest_id == $questID){
			$quest = $q;
			$requirements = $wpdb->get_results("SELECT b.req_object_id, b.req_type FROM {$wpdb->prefix}br_quests a LEFT JOIN {$wpdb->prefix}br_reqs b ON a.quest_id = b.quest_id WHERE a.quest_id=$questID AND a.quest_status='publish'");
			$reqs=array();
			foreach($requirements as $r){
				if($r->req_type=='quest') $reqs['quests'][]=$r->req_object_id;
				else if ($r->req_type=='item') $reqs['items'][]=$r->req_object_id;
			}
			if($q->quest_type == 'challenge'){
				$all_qs = $wpdb->get_results("SELECT a.*, b.answer_id, b.answer_value, b.answer_image, b.answer_correct FROM {$wpdb->prefix}br_challenge_questions a LEFT JOIN {$wpdb->prefix}br_challenge_answers b ON a.quest_id = b.quest_id AND a.question_id=b.question_id AND b.answer_status='publish' WHERE a.quest_id=$questID AND a.question_status='publish'");
				$questions = array();
				foreach($all_qs as $kq=>$qs){
					$questions[$qs->question_id]['title']=$qs->question_title;
					$questions[$qs->question_id]['image']=$qs->question_image;
					$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_id']=$qs->answer_id;
					$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_value']=$qs->answer_value;
					$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_image']=$qs->answer_image;
					$questions[$qs->question_id]['answers'][$qs->answer_id]['answer_correct']=$qs->answer_correct;
				}
			}
		}
	}
}
if($isAdmin || $isGM){
	$adventures = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_adventures a JOIN {$wpdb->prefix}br_player_adventure b ON a.adventure_id=b.adventure_id WHERE a.adventure_status='publish' AND (a.adventure_owner=$current_user->ID OR (b.player_id=$current_user->ID AND b.player_adventure_status='in' AND b.player_adventure_role='gm')) GROUP BY a.adventure_id ORDER BY a.adventure_title");
}
$is_edit = isset($quest);
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background:rgba(244,67,54,0.2);display:flex;align-items:center;justify-content:center;border-color:rgba(244,67,54,0.4)">
			<span class="icon icon-challenge" style="font-size:28px;color:#f44336"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Challenge", "bluerabbit") : __("New Challenge", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_type" value="challenge">
		<input type="hidden" id="the_quest_id" value="<?= $questID; ?>">
		<input type="hidden" id="the_challenge_id" value="<?= $questID; ?>">
		<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : count($quests); ?>">
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="tab-group-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this);">
			<span class="icon icon-tools"></span> <?= __("General", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('challenge-mechanics', this);">
			<span class="icon icon-config"></span> <?= __("Mechanics", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('content', this);">
			<span class="icon icon-document"></span> <?= __("Content", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('challenge-questions', this);">
			<span class="icon icon-question"></span> <?= __("Questions", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('advanced-options', this);">
			<span class="icon icon-config"></span> <?= __("Advanced", "bluerabbit"); ?>
		</button>
	</div>

	<!-- Tab Content -->

		<!-- ═══ GENERAL ═══ -->
		<div class="br-scroll-section" id="general"><div class="br-panel">
			<div class="highlight padding-10 grey-bg-200">
				<span class="icon-group">
					<span class="button-icon font _24 sq-40  red-bg-400"><span class="icon icon-challenge"></span></span>
					<span class="icon-content"><span class="line font _24 grey-800"><?php _e("General Settings","bluerabbit"); ?></span></span>
				</span>
			</div>
			<table class="table w-full" cellpadding="0">
				<thead><tr class="font _12 grey-600"><td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td><td><?php _e('Value','bluerabbit'); ?></td></tr></thead>
				<tbody class="font _16">
					<tr>
						<td class="text-right w-150"><?php _e('Name','bluerabbit'); ?></td>
						<td><div class="input-group w-full"><label class="light-blue-bg-800 font w900"><span class="icon icon-challenge"></span></label><input class="form-ui font _30 w-full" placeholder="<?= __("Challenge Title","bluerabbit"); ?>" type="text" value="<?= isset($quest) ? $quest->quest_title : '' ; ?>" id="the_quest_title"></div></td>
					</tr>
					<tr>
						<td class="text-right w-150"><?php _e('Color','bluerabbit'); ?></td>
						<td>
							<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
								<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : 'red' ; ?>
								<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
								<?php $color_select_id = "#the_quest_color"; include (TEMPLATEPATH . '/color-select.php'); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td class="text-right w-150"><?php _e('Icon','bluerabbit'); ?></td>
						<td>
							<div class="highlight padding-10 grey-bg-200" id="tutorial-icon-select">
								<?php $selected_icon = isset($quest->quest_icon) ? $quest->quest_icon : 'challenge' ; ?>
								<input id="the_quest_icon" class="icon-selected" type="hidden" value="<?= $selected_icon; ?>">
								<?php include (TEMPLATEPATH . '/icon-select.php'); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td class="text-right v-top"><span class="font _16 block"><?= __("Main image","bluerabbit");?></span><span class="font _12 block red-500"><?php _e("Required","bluerabbit"); ?></span></td>
						<td>
							<div class="br-gallery br-gallery-single">
								<?php $thumb_id = 'the_quest_badge'; $file = isset($quest) ? $quest->mech_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div></div>

		<!-- ═══ MECHANICS ═══ -->
		<div class="br-scroll-section" id="challenge-mechanics"><div class="br-panel">
			<?php include (get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>
			<div class="highlight padding-10 grey-bg-200">
				<span class="icon-group">
					<span class="button-icon font _24 sq-40 red-bg-400"><span class="icon icon-challenge"></span></span>
					<span class="icon-content"><span class="line font _24 grey-800"><?php _e("Challenge Mechanics","bluerabbit"); ?></span></span>
				</span>
			</div>
			<table class="table w-full" cellpadding="0">
				<thead><tr class="font _12 grey-600"><td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td><td><?php _e('Value','bluerabbit'); ?></td></tr></thead>
				<tbody class="font _16">
					<tr><td class="text-right w-150"><?php _e('Time Limit','bluerabbit'); ?></td><td><div class="input-group w-full"><input type="number" class="form-ui" id="the_quest_time_limit" value="<?= isset($quest->mech_time_limit) ? $quest->mech_time_limit : ""; ?>"><label class="pink-bg-400 font _18 condensed w900 uppercase"><?php _e('seconds','bluerabbit'); ?></label></div></td></tr>
					<tr><td class="text-right w-150"><?php _e('Questions to display','bluerabbit'); ?></td><td><input type="number" class="form-ui" id="the_quest_questions_to_display" value="<?= isset($quest->mech_questions_to_display) ? $quest->mech_questions_to_display : '' ; ?>"></td></tr>
					<tr><td class="text-right w-150"><?php _e('Answers to win','bluerabbit'); ?></td><td><input type="number" class="form-ui" id="the_quest_answers_to_win" value="<?= isset($quest->mech_answers_to_win) ? $quest->mech_answers_to_win : '' ; ?>"></td></tr>
					<tr><td class="text-right w-150"><?php _e('Max Chances','bluerabbit'); ?></td><td><div class="input-group w-full"><input type="number" min="1" class="form-ui" id="the_quest_max_attempts" value="<?= isset($quest->mech_max_attempts) ? $quest->mech_max_attempts : '' ; ?>"><label class="grey-bg-400 black-color font _12"><?php _e('zero for infinite','bluerabbit'); ?></label></div></td></tr>
					<tr><td class="text-right w-150"><?php _e('Free Attempts','bluerabbit'); ?></td><td><input type="number" class="form-ui" id="the_quest_free_attempts" value="<?= isset($quest->mech_free_attempts) ? $quest->mech_free_attempts : '' ; ?>"></td></tr>
					<tr><td class="text-right w-150"><?php _e('Attempt Cost','bluerabbit'); ?></td><td><div class="input-group w-full"><input type="number" class="form-ui" id="the_quest_attempt_cost" value="<?= isset($quest->mech_attempt_cost) ? $quest->mech_attempt_cost : '' ; ?>"><label class="grey-bg-400 black-color font _12"><?php _e('after all free attempts are used','bluerabbit'); ?></label></div></td></tr>
				</tbody>
			</table>
		</div></div>

		<!-- ═══ CONTENT ═══ -->
		<div class="br-scroll-section" id="content"><div class="br-panel">
			<?php include (get_stylesheet_directory() . '/component-quest-content.php'); ?>
		</div></div>

		<!-- ═══ QUESTIONS ═══ -->
		<div class="br-scroll-section" id="challenge-questions"><div class="br-panel">
			<div class="highlight padding-10 grey-bg-200">
				<span class="icon-group">
					<span class="button-icon font _24 sq-40 red-bg-400"><span class="icon icon-question"></span></span>
					<span class="icon-content">
						<span class="line font _24 grey-800"><?php _e("Challenge Questions","bluerabbit"); ?></span>
						<span class="line font _14 grey-500"><?php _e("The more questions the more random the challenge","bluerabbit"); ?></span>
					</span>
				</span>
			</div>
			<?php if(isset($adventure) && isset($quest)){ ?>
				<div class="highlight input-group">
					<div class="form-ui font _14">
						<form id="upload_bulk_questions_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
							<table><tbody><tr>
								<td class="w-200"><label for="the_csv_file_with_questions"><?= __("Upload Questions","bluerabbit"); ?>:</label><input type="file" name="the_csv_file_with_questions" id="the_csv_file_with_questions" size="20" /></td>
								<td class="w-100"><button type="button" onClick="uploadBulkQuestions();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button></td>
							</tr></tbody></table>
						</form>
					</div>
				</div>
				<div class="w-full">
					<div class="questions accordion" id="questions">
						<?php if(isset($questions)){ $qCount = 0; foreach($questions as $qKey=>$question){ $qCount++; include (TEMPLATEPATH . '/challenge-question-form.php'); } } ?>
					</div>
					<div id="questions-bottom"></div>
					<div class="highlight padding-10 indigo-bg-100 text-right">
						<button class="form-ui blue-bg-800 font condensed _18 w300" onClick="addQuestion('challenge');"><span class="icon icon-add"></span> <?php _e("Add Question","bluerabbit"); ?></button>
					</div>
				</div>
			<?php }else{ ?>
				<div class="highlight padding-10 red-bg-100 text-center font _24 w400"><?= __('Please save the challenge before adding questions','bluerabbit'); ?></div>
			<?php } ?>
		</div></div>

		<!-- ═══ ADVANCED ═══ -->
		<div class="br-scroll-section" id="advanced-options"><div class="br-panel">
			<?php
			include (TEMPLATEPATH . '/component-quest-additional-mechs.php');
			include (TEMPLATEPATH . '/component-quest-item-reward.php');
			include (TEMPLATEPATH . '/component-quest-achievement-reward.php');
			include (TEMPLATEPATH . '/component-quest-key-item-req.php');
			include (TEMPLATEPATH . '/component-quest-reqs.php');
			include (TEMPLATEPATH . '/component-quest-achievement-reqs.php');
			?>
		</div></div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<a class="br-btn br-btn-red" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
		<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
	</a>
	<div class="br-actions">
		<?php if(isset($paths['publish'])){ ?>
		<select id="the_achievement_id" class="br-input" style="width:auto">
			<option value="0" <?= !isset($quest->achievement_id) ? 'selected' : ''; ?>><?= __('All paths','bluerabbit'); ?></option>
			<?php foreach($paths['publish'] as $a){ ?>
				<option id="achievement-option-<?= $a->achievement_id; ?>" value="<?= $a->achievement_id;?>" <?= (isset($quest) && $quest->achievement_id == $a->achievement_id) ? 'selected' : ''; ?>><?= $a->achievement_name; ?></option>
			<?php } ?>
		</select>
		<?php } else { ?>
			<input id="the_achievement_id" type="hidden" value="0">
		<?php } ?>
		<select id="the_quest_status" class="br-input" style="width:auto">
			<option value="publish" <?= (!$is_edit || $quest->quest_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish","bluerabbit"); ?></option>
			<option value="draft" <?= ($is_edit && $quest->quest_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft","bluerabbit"); ?></option>
			<option value="locked" <?= ($is_edit && $quest->quest_status == 'locked') ? 'selected' : ''; ?>><?= __("Locked","bluerabbit"); ?></option>
			<option value="trash" <?= ($is_edit && $quest->quest_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash","bluerabbit"); ?></option>
		</select>
		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_quest_nonce'); ?>">
		<input type="hidden" id="delete-question-nonce" value="<?= wp_create_nonce('br_delete_question_nonce'); ?>">
		<input type="hidden" id="delete-option-nonce" value="<?= wp_create_nonce('br_delete_option_nonce'); ?>">
		<button id="submit-button" type="button" class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= $is_edit ? __("Update Challenge", "bluerabbit") : __("Create Challenge", "bluerabbit"); ?>
		</button>
		<?php if($is_edit){ ?>
		<button class="br-btn" onClick="showOverlay('#list-of-adventures');"><span class="icon icon-infinite"></span> <?= __("Duplicate","bluerabbit"); ?></button>
		<div class="confirm-action overlay-layer red-bg-400" id="list-of-adventures">
			<span class="line font _14 w900 white-color"><?php _e('Select destination','bluerabbit'); ?></span>
			<select class="form-ui" id="adventure_target">
				<?php foreach($adventures as $c){ ?><option value="<?= $c->adventure_id;?>"><?= $c->adventure_id == $adventure->adventure_id ? __("Same adventure","bluerabbit") : $c->adventure_title;?></option><?php } ?>
			</select><br>
			<button class="form-ui red-A400 white-bg" onClick="duplicateQuest(<?= $quest->quest_id; ?>);"><span class="icon icon-infinite"></span> <?php _e("Duplicate","bluerabbit");?></button>
			<button class="form-ui grey-bg-600 white-color" onClick="hideAllOverlay();"><span class="icon icon-cancel"></span> <?php _e("Cancel","bluerabbit");?></button>
			<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>">
		</div>
		<?php } ?>
	</div>
</div>

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('.br-tabs-sticky .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('.br-tabs-sticky .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

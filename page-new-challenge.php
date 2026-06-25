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
			<h1 class="br-page-title" id="challenge-title-label"><?= $is_edit ? __("Edit Challenge", "bluerabbit") . ' &rsaquo; ' . esc_html($quest->quest_title) : __("New Challenge", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_quest_type" value="challenge">
		<input type="hidden" id="the_quest_id" value="<?= $questID; ?>">
		<input type="hidden" id="the_challenge_id" value="<?= $questID; ?>">
		<input type="hidden" id="the_quest_order" value="<?= isset($quest) ? $quest->quest_order : count($quests); ?>">
		<?php if ($is_edit) { ?>
		<a class="br-btn" href="<?= get_bloginfo('url') . "/challenge/?questID=$quest->quest_id&adventure_id=$quest->adventure_id"; ?>" target="_blank" style="margin-left:auto">
			<span class="icon icon-view"></span> <?= __("View Challenge", "bluerabbit"); ?>
		</a>
		<?php } ?>
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

	<!-- ═══ GENERAL ═══ -->
	<div class="br-scroll-section" id="general"><div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-challenge"></span> <?= __("General Settings", "bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Name", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_quest_title"
				   placeholder="<?= __('Challenge Title', 'bluerabbit'); ?>"
				   value="<?= isset($quest) ? esc_attr($quest->quest_title) : ''; ?>"
				   onChange="$('#challenge-title-label').text('<?= __("Edit Challenge", "bluerabbit"); ?> › '+$('#the_quest_title').val());">
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
				<div class="br-form-component" id="tutorial-color-select">
					<?php $selected_color = isset($quest->quest_color) ? $quest->quest_color : 'red' ; ?>
					<input id="the_quest_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php $color_select_id = "#the_quest_color"; include (TEMPLATEPATH . '/color-select.php'); ?>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Icon", "bluerabbit"); ?></label>
				<div class="br-form-component" id="tutorial-icon-select">
					<?php $selected_icon = isset($quest->quest_icon) ? $quest->quest_icon : 'challenge' ; ?>
					<input id="the_quest_icon" class="icon-selected" type="hidden" value="<?= $selected_icon; ?>">
					<?php include (TEMPLATEPATH . '/icon-select.php'); ?>
				</div>
			</div>
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Main Image", "bluerabbit"); ?> <span style="color:#f44336;font-size:10px;letter-spacing:0">*<?= __("Required", "bluerabbit"); ?></span></label>
			<div class="br-form-component">
				<div class="br-gallery br-gallery-single">
					<?php $thumb_id = 'the_quest_badge'; $file = isset($quest) ? $quest->mech_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
				</div>
			</div>
		</div>
	</div></div>

	<!-- ═══ MECHANICS ═══ -->
	<div class="br-scroll-section" id="challenge-mechanics"><div class="br-panel">
		<?php include (get_stylesheet_directory() . '/component-quest-base-mechs.php'); ?>

		<h3 class="br-panel-title" style="margin-top:24px"><span class="icon icon-challenge"></span> <?= __("Challenge Mechanics", "bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Time Limit", "bluerabbit"); ?></label>
			<div class="br-input-group">
				<input class="br-input" type="number" id="the_quest_time_limit"
					   value="<?= isset($quest->mech_time_limit) ? $quest->mech_time_limit : ''; ?>">
				<span class="br-input-suffix"><?= __("seconds", "bluerabbit"); ?></span>
			</div>
		</div>

		<div class="br-form-grid" style="grid-template-columns:1fr 1fr 1fr">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Questions to display", "bluerabbit"); ?></label>
				<input class="br-input" type="number" id="the_quest_questions_to_display"
					   value="<?= isset($quest->mech_questions_to_display) ? $quest->mech_questions_to_display : ''; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Answers to win", "bluerabbit"); ?></label>
				<input class="br-input" type="number" id="the_quest_answers_to_win"
					   value="<?= isset($quest->mech_answers_to_win) ? $quest->mech_answers_to_win : ''; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Max Chances", "bluerabbit"); ?></label>
				<div class="br-input-group">
					<input class="br-input" type="number" min="1" id="the_quest_max_attempts"
						   value="<?= isset($quest->mech_max_attempts) ? $quest->mech_max_attempts : ''; ?>">
					<span class="br-input-suffix" style="font-size:10px"><?= __("0 = infinite", "bluerabbit"); ?></span>
				</div>
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Free Attempts", "bluerabbit"); ?></label>
				<input class="br-input" type="number" id="the_quest_free_attempts"
					   value="<?= isset($quest->mech_free_attempts) ? $quest->mech_free_attempts : ''; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Attempt Cost", "bluerabbit"); ?></label>
				<div class="br-input-group">
					<input class="br-input" type="number" id="the_quest_attempt_cost"
						   value="<?= isset($quest->mech_attempt_cost) ? $quest->mech_attempt_cost : ''; ?>">
					<span class="br-input-suffix" style="font-size:10px"><?= __("after free attempts", "bluerabbit"); ?></span>
				</div>
			</div>
		</div>
	</div></div>

	<!-- ═══ CONTENT ═══ -->
	<div class="br-scroll-section" id="content"><div class="br-panel">
		<?php include (get_stylesheet_directory() . '/component-quest-content.php'); ?>
	</div></div>

	<!-- ═══ QUESTIONS ═══ -->
	<div class="br-scroll-section" id="challenge-questions"><div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-question"></span> <?= __("Challenge Questions", "bluerabbit"); ?></h3>
		<p class="br-panel-subtitle"><?= __("The more questions the more random the challenge", "bluerabbit"); ?></p>

		<?php if(isset($adventure) && isset($quest)){ ?>
			<div class="br-form-group" style="margin-bottom:16px">
				<label class="br-form-label"><?= __("Bulk Upload", "bluerabbit"); ?></label>
				<div class="br-question-upload">
					<form id="upload_bulk_questions_form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
						<input type="file" name="the_csv_file_with_questions" id="the_csv_file_with_questions" class="br-input" style="flex:1">
						<button type="button" class="br-btn br-btn-green" onClick="uploadBulkQuestions();">
							<span class="icon icon-upload"></span> <?= __("Upload file", "bluerabbit"); ?>
						</button>
					</form>
				</div>
			</div>

			<div class="questions accordion" id="questions">
				<?php if(isset($questions)){ $qCount = 0; foreach($questions as $qKey=>$question){ $qCount++; include (TEMPLATEPATH . '/challenge-question-form.php'); } } ?>
			</div>
			<div id="questions-bottom"></div>
			<div style="padding:12px 0;text-align:right">
				<button class="br-btn br-btn-green" onClick="addQuestion('challenge');">
					<span class="icon icon-add"></span> <?= __("Add Question", "bluerabbit"); ?>
				</button>
			</div>
		<?php }else{ ?>
			<div class="br-empty-state">
				<span class="icon icon-warning" style="font-size:24px;color:#f44336"></span>
				<span><?= __('Please save the challenge before adding questions','bluerabbit'); ?></span>
			</div>
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
		<button id="submit-button" type="button" class="br-btn br-btn-green" onClick="updateQuest();">
			<span class="icon icon-check"></span>
			<?= $is_edit ? __("Update Challenge", "bluerabbit") : __("Create Challenge", "bluerabbit"); ?>
		</button>
		<?php if($is_edit){ ?>
		<button class="br-btn" onClick="showOverlay('#list-of-adventures');"><span class="icon icon-infinite"></span> <?= __("Duplicate","bluerabbit"); ?></button>
		<div class="confirm-action overlay-layer" id="list-of-adventures" style="background:rgba(30,30,30,0.95);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:16px">
			<span style="display:block;font-size:13px;font-weight:700;color:rgba(255,255,255,0.7);margin-bottom:8px"><?= __('Select destination','bluerabbit'); ?></span>
			<select class="br-input" id="adventure_target" style="margin-bottom:8px">
				<?php foreach($adventures as $c){ ?><option value="<?= $c->adventure_id;?>"><?= $c->adventure_id == $adventure->adventure_id ? __("Same adventure","bluerabbit") : $c->adventure_title;?></option><?php } ?>
			</select>
			<div style="display:flex;gap:6px">
				<button class="br-btn br-btn-green" onClick="duplicateQuest(<?= $quest->quest_id; ?>);"><span class="icon icon-infinite"></span> <?= __("Duplicate","bluerabbit");?></button>
				<button class="br-btn" onClick="hideAllOverlay();"><span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit");?></button>
			</div>
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

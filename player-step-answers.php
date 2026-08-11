<?php
/**
 * Everything a player actually entered on a milestone, step by step.
 *
 * The player's work is not one blob: each step writes its own row into
 * br_player_steps with a JSON ps_response whose shape depends on the step skin
 * (see BR_Step::completeStep and the step-*.php templates). Until now the only
 * thing shown back to the player was br_player_posts.pp_content - the open-text
 * editor's contents - so every multiple-choice answer, keyphrase, rating, poll
 * vote and upload they submitted was invisible after the fact. This partial
 * walks the milestone's steps in order and renders each stored response.
 *
 * Expects, from the including template:
 *   $pa_quest_id      milestone to render
 *   $pa_player_id     whose answers
 *   $pa_parent_id     adventure the steps are authored on (adventure_parent)
 *   $pa_child_id      adventure the player is enrolled in
 *   $pa_pp_content    (optional) br_player_posts.pp_content, used as the
 *                     fallback body for a lone open-text step - see below
 */

global $wpdb;

$pa_steps = $wpdb->get_results($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_steps
	 WHERE quest_id = %d AND adventure_id = %d AND step_status = 'publish'
	 ORDER BY step_order ASC, step_id ASC",
	$pa_quest_id, $pa_parent_id
));

$pa_saved = $wpdb->get_results($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_steps
	 WHERE quest_id = %d AND player_id = %d AND adventure_id = %d",
	$pa_quest_id, $pa_player_id, $pa_child_id
));
$pa_by_step = [];
foreach ($pa_saved as $pa_row) { $pa_by_step[(int) $pa_row->step_id] = $pa_row; }

// Same normalisation quest.php does when it picks a template, so a step authored
// under an old skin name still lands in the right branch here.
$pa_legacy = [
	'instruction' => 'dialogue', 'open' => 'open_text', 'jump' => 'jump_to_step',
	'item-grab' => 'find_item', 'item-req' => 'backpack_item', 'path-choice' => 'branch_choice',
	'choose-nickname' => 'choose_nickname', 'choose-avatar' => 'choose_avatar',
];

// Skins that collect or validate something the player entered. Pure delivery
// steps (dialogue, video, gallery…) and flow steps have no answer to show.
// This list decides which steps are listed even when *unanswered*; a step of any
// other skin still appears if the player actually stored something on it, so a
// step type added after this file was written is never silently swallowed.
$pa_answerable = [
	'multiple_choice', 'keyphrase', 'cryptex', 'puzzle', 'backpack_item', 'scorm',
	'case_study_html', 'survey_choice', 'survey_rating', 'survey_poll', 'open_text',
	'upload_image', 'upload_video', 'branch_choice',
];

// Count the open-text steps up front: pp_content only stands in for the answer
// when there is exactly one of them. Every open-text editor in quest.php is
// rendered with the same `the_pp_content` id, so with two or more the fallback
// could not tell which step the text belonged to - better to show nothing than
// to attribute an answer to the wrong question.
$pa_open_steps = 0;
foreach ($pa_steps as $pa_s) {
	$pa_skin_probe = $pa_s->step_skin ?: $pa_s->step_type;
	$pa_skin_probe = $pa_legacy[$pa_skin_probe] ?? $pa_skin_probe;
	if ($pa_skin_probe === 'open_text') $pa_open_steps++;
}

$pa_blocks = [];

foreach ($pa_steps as $pa_index => $pa_step) {
	$pa_skin = $pa_step->step_skin ?: $pa_step->step_type;
	$pa_skin = $pa_legacy[$pa_skin] ?? $pa_skin;

	$pa_settings = $pa_step->step_settings ? json_decode($pa_step->step_settings, true) : [];
	if (!is_array($pa_settings)) $pa_settings = [];
	$pa_saved_row = $pa_by_step[(int) $pa_step->step_id] ?? null;
	$pa_response  = ($pa_saved_row && $pa_saved_row->ps_response)
		? json_decode($pa_saved_row->ps_response, true)
		: [];
	if (!is_array($pa_response)) $pa_response = [];

	if (!in_array($pa_skin, $pa_answerable, true) && !$pa_response) continue;

	// The prompt: whatever the step asked, in the order the step types set it.
	$pa_question = '';
	foreach (['question', 'prompt'] as $pa_key) {
		if (!empty($pa_settings[$pa_key])) { $pa_question = $pa_settings[$pa_key]; break; }
	}
	if ($pa_question === '' && $pa_step->step_title) $pa_question = $pa_step->step_title;
	if ($pa_question === '') $pa_question = sprintf(__("Step %d", "bluerabbit"), $pa_index + 1);

	$pa_answer_html = '';
	$pa_answered    = false;
	$pa_correct     = $pa_saved_row ? $pa_saved_row->ps_correct : null;

	switch ($pa_skin) {

		case 'open_text':
			$pa_text = $pa_response['content'] ?? '';
			if ($pa_text === '' && $pa_open_steps === 1 && !empty($pa_pp_content)
				&& trim((string) $pa_pp_content) !== BR_POST_AUTO_COMPLETE_TEXT) {
				$pa_text = $pa_pp_content;
			}
			if (trim((string) $pa_text) !== '') {
				$pa_answered = true;
				$pa_answer_html = '<div class="br-answer-text">' . apply_filters('the_content', $pa_text) . '</div>';
			}
			break;

		case 'multiple_choice':
		case 'survey_choice':
		case 'survey_poll':
			$pa_chosen  = (array) ($pa_response['selected'] ?? []);
			$pa_options = $pa_settings['options'] ?? [];
			$pa_key_set = $pa_step->step_correct ? (array) json_decode($pa_step->step_correct, true) : [];
			// Graded skins mark right/wrong; survey skins have no right answer,
			// so their picks render neutral rather than as a red cross.
			$pa_graded  = ($pa_skin === 'multiple_choice' && $pa_key_set);
			if ($pa_chosen) {
				$pa_answered = true;
				foreach ($pa_chosen as $pa_pick) {
					$pa_label = (string) $pa_pick;
					foreach ($pa_options as $pa_opt) {
						if (isset($pa_opt['id']) && (string) $pa_opt['id'] === (string) $pa_pick) {
							$pa_label = $pa_opt['text'] ?? $pa_label;
							break;
						}
					}
					if ($pa_graded) {
						$pa_hit  = in_array($pa_pick, $pa_key_set);
						$pa_cls  = $pa_hit ? 'br-answer-correct' : 'br-answer-wrong';
						$pa_icon = $pa_hit ? 'icon-check' : 'icon-cancel';
					} else {
						$pa_cls  = 'br-answer-neutral';
						$pa_icon = 'icon-check';
					}
					$pa_answer_html .= '<span class="br-answer-pill ' . $pa_cls . '">'
						. '<span class="icon ' . $pa_icon . '"></span> ' . esc_html($pa_label) . '</span>';
				}
			}
			break;

		case 'keyphrase':
		case 'cryptex':
			$pa_word = trim((string) ($pa_response['answer'] ?? ''));
			if ($pa_word !== '') {
				$pa_answered = true;
				$pa_cls = ($pa_correct === '0' || $pa_correct === 0) ? 'br-answer-wrong' : 'br-answer-correct';
				$pa_answer_html = '<span class="br-answer-pill ' . $pa_cls . '">' . esc_html($pa_word) . '</span>';
			}
			break;

		case 'survey_rating':
			$pa_val = $pa_response['value'] ?? null;
			if ($pa_val !== null && $pa_val !== '') {
				$pa_answered = true;
				$pa_max = (int) ($pa_settings['max'] ?? 5);
				$pa_answer_html = '<span class="br-answer-pill br-answer-neutral">'
					. '<span class="icon icon-star"></span> ' . esc_html($pa_val)
					. ($pa_max ? ' / ' . $pa_max : '') . '</span>';
			}
			break;

		case 'upload_image':
			if (!empty($pa_response['url'])) {
				$pa_answered = true;
				$pa_answer_html = '<a class="br-answer-media" href="' . esc_url($pa_response['url']) . '" target="_blank" rel="noopener noreferrer">'
					. '<img src="' . esc_url($pa_response['url']) . '" alt="" loading="lazy"></a>';
			}
			break;

		case 'upload_video':
			if (!empty($pa_response['url'])) {
				$pa_answered = true;
				$pa_answer_html = '<video class="br-answer-media" controls src="' . esc_url($pa_response['url']) . '"></video>';
			}
			break;

		case 'branch_choice':
			if (!empty($pa_response['achievement_id'])) {
				$pa_answered = true;
				$pa_branch = $wpdb->get_var($wpdb->prepare(
					"SELECT achievement_name FROM {$wpdb->prefix}br_achievements WHERE achievement_id = %d",
					(int) $pa_response['achievement_id']
				));
				$pa_answer_html = '<span class="br-answer-pill br-answer-neutral">'
					. '<span class="icon icon-achievement"></span> '
					. esc_html($pa_branch ?: __("Path chosen", "bluerabbit")) . '</span>';
			}
			break;

		// The embedded activity records per-question state, so this is not a bare
		// completion like SCORM or puzzle - show how many landed, and how many runs it
		// took, rather than a single "Completed" pill that hid all of it.
		case 'case_study_html':
			$pa_cs = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}br_casestudy_attempts
				 WHERE player_id = %d AND step_id = %d AND adventure_id = %d
				 ORDER BY attempt_status = 'success' DESC, attempt_score DESC, attempt_id DESC
				 LIMIT 1",
				$pa_player_id, $pa_step->step_id, $pa_child_id
			));
			// Counted apart, because the step row's own ps_attempt only ever counts runs
			// that reached the end - showing that next to a total that includes walked-
			// away-from runs reads as two different answers to the same question.
			$pa_cs_counts = $wpdb->get_row($wpdb->prepare(
				"SELECT
				   SUM(attempt_status IN ('success','fail')) AS finished,
				   SUM(attempt_status IN ('in_progress','abandoned')) AS unfinished
				 FROM {$wpdb->prefix}br_casestudy_attempts
				 WHERE player_id = %d AND step_id = %d AND adventure_id = %d",
				$pa_player_id, $pa_step->step_id, $pa_child_id
			));
			$pa_cs_runs   = (int) ($pa_cs_counts->finished ?? 0);
			$pa_cs_unfin  = (int) ($pa_cs_counts->unfinished ?? 0);
			if ($pa_cs) {
				$pa_answered = true;
				// A run that was never finished is not the same as one that was failed,
				// and saying "not passed" for a case study the player walked out of
				// misreports what happened.
				$pa_unfinished = in_array($pa_cs->attempt_status, ['in_progress', 'abandoned'], true);
				if ($pa_cs->attempt_status === 'success') {
					$pa_cls = 'br-answer-correct'; $pa_ico = 'icon-check'; $pa_lbl = __("Passed", "bluerabbit");
				} elseif ($pa_unfinished) {
					$pa_cls = 'br-answer-neutral'; $pa_ico = 'icon-rotate';
					$pa_lbl = ($pa_cs->attempt_status === 'abandoned') ? __("Started, not finished", "bluerabbit") : __("In progress", "bluerabbit");
				} else {
					$pa_cls = 'br-answer-wrong'; $pa_ico = 'icon-cancel'; $pa_lbl = __("Not passed", "bluerabbit");
				}
				$pa_answer_html = '<span class="br-answer-pill ' . $pa_cls . '">'
					. '<span class="icon ' . $pa_ico . '"></span> ' . $pa_lbl
					. ($pa_cs->attempt_score !== null ? ' &mdash; ' . intval($pa_cs->attempt_score) . '%' : '')
					. ($pa_cs->total_questions ? ' (' . intval($pa_cs->correct_count) . '/' . intval($pa_cs->total_questions) . ')' : '')
					. '</span>';
				if ($pa_cs_runs > 1) {
					$pa_answer_html .= '<span class="br-answer-pill br-answer-neutral">'
						. sprintf(__("best of %d finished", "bluerabbit"), $pa_cs_runs) . '</span>';
				}
				if ($pa_cs_unfin > 0) {
					$pa_answer_html .= '<span class="br-answer-pill br-answer-neutral">'
						. sprintf(_n("%d not finished", "%d not finished", $pa_cs_unfin, "bluerabbit"), $pa_cs_unfin) . '</span>';
				}
				break;
			}
			// No attempt row yet (a step completed before attempts were recorded) - fall
			// through to the generic completion pill rather than showing nothing.

		// Skins where the "answer" is a completion, not content the player typed.
		case 'puzzle':
		case 'scorm':
		case 'backpack_item':
			if ($pa_saved_row) {
				$pa_answered = true;
				$pa_score = ($pa_saved_row->ps_score !== null && $pa_saved_row->ps_score !== '')
					? ' &mdash; ' . intval($pa_saved_row->ps_score) . '%' : '';
				$pa_cls = ($pa_correct === '0' || $pa_correct === 0) ? 'br-answer-wrong' : 'br-answer-correct';
				$pa_answer_html = '<span class="br-answer-pill ' . $pa_cls . '">'
					. '<span class="icon icon-check"></span> ' . __("Completed", "bluerabbit") . $pa_score . '</span>';
			}
			break;
	}

	// A response shape this partial doesn't know about still gets shown rather
	// than silently dropped - new step skins land here until they get a branch.
	if (!$pa_answered && $pa_response) {
		foreach ($pa_response as $pa_k => $pa_v) {
			if (is_array($pa_v)) $pa_v = implode(', ', $pa_v);
			if ($pa_v === '' || $pa_v === null) continue;
			$pa_answered = true;
			$pa_answer_html .= '<span class="br-answer-pill br-answer-neutral">'
				. esc_html($pa_k . ': ' . $pa_v) . '</span>';
		}
	}

	$pa_blocks[] = [
		'question' => $pa_question,
		'answer'   => $pa_answer_html,
		'answered' => $pa_answered,
		'attempts' => $pa_saved_row ? (int) $pa_saved_row->ps_attempt : 0,
		'date'     => $pa_saved_row ? $pa_saved_row->ps_modified : '',
		'skin'     => $pa_skin,
	];
}
?>

<?php if ($pa_blocks) { ?>
<div class="br-answer-log">
	<?php foreach ($pa_blocks as $pa_b) { ?>
	<div class="br-qa-block<?= $pa_b['answered'] ? '' : ' br-qa-unanswered'; ?>">
		<div class="br-qa-question">
			<?= esc_html(wp_strip_all_tags($pa_b['question'])); ?>
			<?php if ($pa_b['attempts'] > 1) { ?>
			<span class="br-qa-attempts"><?= sprintf(__("%d attempts", "bluerabbit"), $pa_b['attempts']); ?></span>
			<?php } ?>
		</div>
		<div class="br-qa-answer">
			<?php if ($pa_b['answered']) {
				echo $pa_b['answer'];
			} else { ?>
			<span class="br-qa-no-answer"><?= __("No answer recorded", "bluerabbit"); ?></span>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
<?php } ?>

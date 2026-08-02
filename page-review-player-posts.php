<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if ( ! ($isAdmin || $isGM || $isNPC) ) { ?>
<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$questID = br_require_id('questID', false) ?: 0;
$q = $wpdb->get_row( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id = %d AND adventure_id = %d",
	$questID, $adventure->adventure_id
) );
?>

<?php if ( ! $q ) { ?>
<div class="br-page">
	<div class="br-panel text-center">
		<h2 class="br-text-24 w900"><?= __("This milestone doesn't exist","bluerabbit"); ?></h2>
	</div>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$player_posts = $wpdb->get_results( $wpdb->prepare(
	"SELECT a.*, b.player_display_name, b.player_email
	FROM {$wpdb->prefix}br_player_posts a
	JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
	WHERE a.adventure_id = %d AND a.quest_id = %d
	ORDER BY a.pp_date DESC, b.player_display_name ",
	$adventure->adventure_id, $q->quest_id
) );
$rating = 0;

// Whether the "Validate with A.I." button on each entry has anything to work
// with: an adventure-level key, and at least one published Open Text step on
// this milestone (whichever one the player answered). This is a manual "ask
// the AI for its opinion" tool the GM reaches for on demand - it is NOT
// gated on the step's own A.I. Validation on/off toggle, since that toggle
// only controls whether AI validation runs automatically at submission time;
// a GM should still be able to sanity-check the AI's behavior on a step even
// when auto-validation is switched off there.
$has_ai_key = (bool) $wpdb->get_var( $wpdb->prepare(
	"SELECT adventure_ai_api_key FROM {$wpdb->prefix}br_adventures WHERE adventure_id = %d",
	$adventure->adventure_id
) );
$show_ai_validate_btn = $has_ai_key && (bool) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_steps
	WHERE quest_id = %d AND adventure_id = %d AND step_status = 'publish'
	AND (step_type IN ('open_text','open') OR step_skin IN ('open_text','open'))",
	$q->quest_id, $adventure->adventure_id
) );
?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<a class="br-btn" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id"; ?>">
			<span class="icon icon-arrow-left"></span> <?= __("Manage Adventure","bluerabbit"); ?>
		</a>
		<div class="br-flex-1 br-text-center">
			<div class="br-page-subtitle"><?= __("Milestone Review","bluerabbit"); ?></div>
			<h1 class="br-page-title"><?= esc_html($q->quest_title); ?></h1>
		</div>
		<a class="br-btn" href="<?= get_bloginfo('url')."/quest/?adventure_id=$adventure->adventure_id&questID=$q->quest_id"; ?>">
			<?= __("View Milestone","bluerabbit"); ?> <span class="icon icon-arrow-right"></span>
		</a>
	</div>

	<?php if ( $player_posts ) { ?>
		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-quest"></span> <?= __("Milestone Data","bluerabbit"); ?></h3>

			<div class="br-flex br-flex-center br-gap-md br-flex-wrap">
				<span class="br-text-16">
					<?= __("Total Entries","bluerabbit"); ?>: <strong><?= count($player_posts); ?></strong>
				</span>
				<?php if ( $config['rate_quests']['value'] > 0 && count($player_posts) > 0 ) { ?>
				<span class="br-text-16">
					<span class="icon icon-star"></span>
					<?= __("Avg Rating","bluerabbit"); ?>: <strong><?= round($rating / count($player_posts), 1); ?></strong>
				</span>
				<?php } ?>
			</div>
		</div>

		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-download"></span> <?= __("Grade & Review CSV","bluerabbit"); ?></h3>
			<span class="br-form-hint"><?= __("Download every entry to review offline, then upload it back with grade, validation status and comment filled in.","bluerabbit"); ?></span>

			<div class="br-flex br-mt-sm br-gap-sm br-flex-wrap">
				<a class="br-btn br-btn-green" href="<?= admin_url('admin-ajax.php').'?action=exportPlayerPostsCSV&adventure_id='.$adventure->adventure_id.'&quest_id='.$q->quest_id.'&nonce='.wp_create_nonce('br_grade_nonce'); ?>">
					<span class="icon icon-download"></span> <?= __("Download CSV","bluerabbit"); ?>
				</a>
			</div>
			<div class="br-flex br-flex-center br-mt-sm br-gap-sm br-flex-wrap">
				<input type="file" id="review_csv_file" accept=".csv">
				<button class="br-btn br-btn-blue" onClick="uploadPostReviewCSV();">
					<span class="icon icon-upload"></span> <?= __("Upload Reviewed CSV","bluerabbit"); ?>
				</button>
				<span class="br-text-12-muted"><?= __("Only updates grade, validation status and comment - everything else in the file is ignored.","bluerabbit"); ?></span>
			</div>
		</div>

		<?php if ( $q->mech_validate ) { ?>
		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-mail"></span> <?= __("Pending Validation Reminders","bluerabbit"); ?></h3>
			<span class="br-form-hint"><?= __("Emails of players who haven't passed validation yet - never reviewed, or reviewed and marked invalid. Upload this file on the Email Notifications page to send them a reminder.","bluerabbit"); ?></span>

			<div class="br-flex br-mt-sm br-gap-sm br-flex-wrap">
				<a class="br-btn br-btn-amber" href="<?= admin_url('admin-ajax.php').'?action=exportPendingReviewEmailsCSV&adventure_id='.$adventure->adventure_id.'&quest_id='.$q->quest_id.'&nonce='.wp_create_nonce('br_grade_nonce'); ?>">
					<span class="icon icon-download"></span> <?= __("Download Pending Emails","bluerabbit"); ?>
				</a>
			</div>
		</div>
		<?php } ?>

		<?php foreach ( $player_posts as $pp ) { ?>
		<?php $rating += (int) $pp->pp_quest_rating; ?>

		<div class="br-panel" id="entry-<?= $pp->player_id; ?>">

			<!-- Player info row -->
			<div class="br-flex br-flex-center br-mb-md">
				<div class="br-flex-1">
					<h2 class="br-text-24 w900">
						<?= $isAdmin ? '<span class="br-text-12-muted">#'.$pp->player_id.' </span>' : ''; ?>
						<?= esc_html($pp->player_display_name); ?>
					</h2>
					<span class="br-text-12-muted"><?= esc_html($pp->player_email); ?></span>
				</div>

				<!-- Rating (read-only) -->
				<?php if ( $config['rate_quests']['value'] > 0 ) { ?>
				<div class="br-form-group br-mr-sm">
					<label class="br-form-label"><span class="icon icon-star"></span> <?= __("Rating","bluerabbit"); ?></label>
					<input disabled class="br-input" value="<?= $pp->pp_quest_rating ? (int)$pp->pp_quest_rating : 0; ?>">
				</div>
				<?php } ?>

				<!-- Grade -->
				<?php if ( $adventure->adventure_grade_scale != 'none' ) { ?>
				<?php $the_grade = $pp->pp_grade; ?>
				<div class="br-form-group br-mr-sm">
					<label class="br-form-label"><span class="icon icon-progression"></span> <?= __("Grade","bluerabbit"); ?></label>
					<?php if ( $adventure->adventure_grade_scale == 'percentage' ) { ?>
					<input onChange="setGrade(<?= "$q->quest_id,$pp->player_id"; ?>);"
						   type="number" min="0" max="100"
						   class="br-input player-grade"
						   id="the_post_grade_<?= $q->quest_id; ?>_<?= $pp->player_id; ?>"
						   value="<?= $the_grade; ?>">
					<?php } elseif ( $adventure->adventure_grade_scale == 'letters' ) { ?>
					<select class="br-input player-grade"
							id="the_post_grade_<?= $q->quest_id; ?>_<?= $pp->player_id; ?>"
							onChange="setGrade(<?= "$q->quest_id,$pp->player_id"; ?>);">
						<option value="100"   <?= $the_grade == 100 ? 'selected' : ''; ?>>A</option>
						<option value="91.75" <?= ($the_grade < 100   && $the_grade >= 91.75) ? 'selected' : ''; ?>>A-</option>
						<option value="83.25" <?= ($the_grade < 91.75 && $the_grade >= 83.25) ? 'selected' : ''; ?>>B+</option>
						<option value="75"    <?= ($the_grade < 83.25 && $the_grade >= 75)    ? 'selected' : ''; ?>>B</option>
						<option value="66.75" <?= ($the_grade < 75    && $the_grade >= 66.75) ? 'selected' : ''; ?>>B-</option>
						<option value="58.25" <?= ($the_grade < 66.75 && $the_grade >= 58.25) ? 'selected' : ''; ?>>C+</option>
						<option value="50"    <?= ($the_grade < 58.25 && $the_grade >= 50)    ? 'selected' : ''; ?>>C</option>
						<option value="25"    <?= ($the_grade < 50    && $the_grade >= 25)    ? 'selected' : ''; ?>>D</option>
						<option value="0"     <?= $the_grade < 25 ? 'selected' : ''; ?>>F</option>
						<option value="NULL"  <?= $the_grade === null ? 'selected' : ''; ?>><?= __("No grade","bluerabbit"); ?></option>
					</select>
					<?php } ?>
				</div>
				<?php } ?>

				<!-- Validate / Invalidate -->
				<?php if ( $q->mech_validate ) { ?>
				<?php $validated = ($pp->pp_grade > 0); ?>
				<div class="br-flex br-flex-center br-gap-sm">
					<button class="<?= $validated ? 'br-btn' : 'br-btn green'; ?>"
							<?= $validated ? 'disabled' : ''; ?>
							onClick="validateQuest(<?= "$q->quest_id,$pp->player_id"; ?>, 'validate');"
							id="validate-btn-<?= $pp->player_id."-".$q->quest_id; ?>">
						<span class="icon icon-check"></span> <?= __("Validate","bluerabbit"); ?>
					</button>
					<button class="<?= ! $validated ? 'br-btn' : 'br-btn red'; ?>"
							<?= ! $validated ? 'disabled' : ''; ?>
							onClick="validateQuest(<?= "$q->quest_id,$pp->player_id"; ?>, 'invalidate');"
							id="invalidate-btn-<?= $pp->player_id."-".$q->quest_id; ?>">
						<span class="icon icon-cancel"></span> <?= __("Invalidate","bluerabbit"); ?>
					</button>
					<?php if ( $show_ai_validate_btn ) { ?>
					<button class="br-btn" title="<?= esc_attr__("Ask the A.I. validator for a second opinion on this answer","bluerabbit"); ?>"
							onClick="reviewValidateWithAI(<?= "$q->quest_id,$pp->player_id"; ?>, '<?= esc_js($pp->player_display_name); ?>');"
							id="ai-recheck-btn-<?= $pp->player_id."-".$q->quest_id; ?>">
						<span class="icon icon-data"></span> <?= __("Validate with A.I.","bluerabbit"); ?>
					</button>
					<?php } ?>
				</div>
				<?php } ?>
			</div>

			<!-- Dates -->
			<div class="br-text-12-muted br-mb-sm">
				<?= __("Published","bluerabbit"); ?> <strong><?= esc_html($pp->pp_date); ?></strong>
				&nbsp;/&nbsp;
				<?= __("Modified","bluerabbit"); ?> <strong><?= esc_html($pp->pp_modified); ?></strong>
			</div>

			<!-- Player submission content -->
			<div class="player-entry-content">
				<?= br_render_post_content($pp->pp_content, $pp->pp_date); ?>
			</div>

			<!-- GM comment (read-only to the player, shown on their own quest post) -->
			<div class="br-form-group br-mt-md">
				<label class="br-form-label"><span class="icon icon-comment"></span> <?= __("Comment for the player","bluerabbit"); ?></label>
				<textarea class="br-input" rows="3" id="the_post_comment_<?= "$q->quest_id"."_".$pp->player_id; ?>"><?= esc_textarea($pp->pp_gm_comment); ?></textarea>
				<button class="br-btn br-btn-blue br-mt-sm" onClick="setPostComment(<?= "$q->quest_id,$pp->player_id"; ?>);">
					<span class="icon icon-check"></span> <?= __("Save Comment","bluerabbit"); ?>
				</button>
			</div>

		</div><!-- .br-panel -->
		<?php } ?>

		<!-- Milestone summary -->

	<?php } else { ?>
	<div class="br-panel text-center">
		<div class="br-empty">
			<span class="icon icon-quest"></span>
			<h3><?= __("No player posts to display","bluerabbit"); ?></h3>
		</div>
	</div>
	<?php } ?>

	<div class="overlay-layer ai-validate-overlay" id="ai-validate-overlay">
		<div class="tabi-conditions-header">
			<h3 class="br-text-16 w700" id="ai-validate-overlay-title"><?= __("A.I. Validation Check","bluerabbit"); ?></h3>
			<button class="br-close-btn" onclick="closeAiValidateModal();"><span class="icon icon-cancel white-color"></span></button>
		</div>
		<div class="tabi-conditions-body" id="ai-validate-overlay-body"></div>
	</div>

</div>

<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>">
<input type="hidden" id="the_review_quest_id" value="<?= $q->quest_id; ?>">

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

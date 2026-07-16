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
	ORDER BY b.player_display_name, a.pp_modified, a.pp_date",
	$adventure->adventure_id, $q->quest_id
) );
$rating = 0;
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
					<button class="<?= $validated ? 'br-btn' : 'br-btn br-form-btn-green'; ?>"
							<?= $validated ? 'disabled' : ''; ?>
							onClick="validateQuest(<?= "$q->quest_id,$pp->player_id"; ?>, 'validate');"
							id="validate-btn-<?= $pp->player_id."-".$q->quest_id; ?>">
						<span class="icon icon-check"></span> <?= __("Validate","bluerabbit"); ?>
					</button>
					<button class="<?= ! $validated ? 'br-btn' : 'br-btn br-form-btn-red'; ?>"
							<?= ! $validated ? 'disabled' : ''; ?>
							onClick="validateQuest(<?= "$q->quest_id,$pp->player_id"; ?>, 'invalidate');"
							id="invalidate-btn-<?= $pp->player_id."-".$q->quest_id; ?>">
						<span class="icon icon-cancel"></span> <?= __("Invalidate","bluerabbit"); ?>
					</button>
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
				<?= apply_filters('the_content', $pp->pp_content); ?>
			</div>

		</div><!-- .br-panel -->
		<?php } ?>

		<!-- Milestone summary -->
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

			<div class="br-flex br-mt-sm br-gap-sm br-flex-wrap">
				<input type="hidden" id="file_prefix" value="<?= esc_attr($q->quest_type.'-'.$q->quest_id.'-'); ?>">
				<button class="br-form-btn-green" onClick="downloadQuestCSV();">
					<span class="icon icon-download"></span> <?= __("Download CSV","bluerabbit"); ?>
				</button>
				<button id="create-zip" class="br-form-btn-blue" onClick="downloadAllImages();">
					<span class="icon icon-image"></span> <?= __("Create Images Zip","bluerabbit"); ?>
				</button>
				<a id="download-zip" href="" class="br-btn br-initially-hidden" target="_blank">
					<?= __("Download Zip","bluerabbit"); ?>
				</a>
			</div>
		</div>

	<?php } else { ?>
	<div class="br-panel text-center">
		<div class="br-empty">
			<span class="icon icon-quest"></span>
			<h3><?= __("No player posts to display","bluerabbit"); ?></h3>
		</div>
	</div>
	<?php } ?>

</div>

<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>">

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

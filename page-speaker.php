<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php $speaker_id = br_require_id('speaker_id'); ?>
<?php $speaker = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id=%d", $speaker_id)); ?>
<?php $sessions = BR_Session::instance()->getSpeakerSessions($adventure->adventure_id, $speaker_id); ?>

<div class="br-page br-page-narrow">

	<div class="br-panel br-speaker-hero" style="background-image:url(<?= esc_url($speaker->speaker_picture); ?>);">
		<div class="br-speaker-hero-overlay"></div>
		<div class="br-speaker-hero-content">
			<div class="br-speaker-avatar" style="background-image:url(<?= esc_url($speaker->speaker_picture); ?>);"></div>
			<h1 class="br-page-title"><?= esc_html($speaker->speaker_first_name . ' ' . $speaker->speaker_last_name); ?></h1>
			<?php if ($speaker->speaker_company) { ?>
			<span class="br-page-subtitle"><?= esc_html($speaker->speaker_company); ?></span>
			<?php } ?>
			<div class="br-speaker-links">
				<?php if ($speaker->speaker_linkedin) { ?>
				<a href="<?= esc_url($speaker->speaker_linkedin); ?>" target="_blank" class="br-btn br-btn-mini br-btn-blue"><?= __("LinkedIn", "bluerabbit"); ?></a>
				<?php } ?>
				<?php if ($speaker->speaker_website) { ?>
				<a href="<?= esc_url($speaker->speaker_website); ?>" target="_blank" class="br-btn br-btn-mini br-btn-purple"><?= __("Website", "bluerabbit"); ?></a>
				<?php } ?>
				<?php if ($speaker->speaker_twitter) { ?>
				<a href="<?= esc_url("https://twitter.com/" . $speaker->speaker_twitter); ?>" target="_blank" class="br-btn br-btn-mini cyan">@<?= esc_html($speaker->speaker_twitter); ?></a>
				<?php } ?>
			</div>
		</div>
		<?php if ($isGM || $isAdmin) { ?>
		<a class="br-icon-btn br-icon-btn-green br-speaker-edit" href="<?= get_bloginfo('url') . "/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$speaker->speaker_id"; ?>">
			<span class="icon icon-edit"></span>
		</a>
		<?php } ?>
	</div>

	<?php if ($speaker->speaker_bio) { ?>
	<div class="br-panel">
		<h3 class="br-panel-title"><?= __("About", "bluerabbit"); ?></h3>
		<div class="br-speaker-bio"><?= apply_filters('the_content', $speaker->speaker_bio); ?></div>
	</div>
	<?php } ?>

	<div class="br-panel">
		<h3 class="br-panel-title"><?= __("Sessions", "bluerabbit"); ?></h3>
		<?php if ($sessions) { ?>
		<div class="br-speaker-sessions">
			<?php foreach ($sessions as $t) { ?>
			<div class="br-speaker-session-row">
				<div>
					<div class="br-speaker-session-title"><?= esc_html($t->session_title); ?></div>
					<div class="br-speaker-session-time"><span class="icon icon-time"></span>
						<?= date("d - M, Y", strtotime($t->session_start)) . " | " . date("H:i", strtotime($t->session_start)) . " - " . date("H:i", strtotime($t->session_end)); ?>
					</div>
					<?php if ($t->session_description) { ?>
					<p class="br-speaker-session-desc"><?= apply_filters('the_content', $t->session_description); ?></p>
					<?php } ?>
				</div>
				<?php if ($t->quest_id) { ?>
				<a class="br-btn br-btn-mini br-btn-blue" href="<?= get_bloginfo('url') . "/$t->quest_type/?adventure_id=$adventure->adventure_id&questID=$t->quest_id"; ?>">
					<?= __("View") . " " . $t->quest_type; ?>
				</a>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div class="br-empty"><span class="icon icon-schedule"></span><h3><?= __("No sessions yet", "bluerabbit"); ?></h3></div>
		<?php } ?>
	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

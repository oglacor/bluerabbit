<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	if($adventure->adventure_hide_schedule == 'hide'){
		$sessions = BR_Session::instance()->getSessions($adventure->adventure_id, 'hide');
	}else{
		$sessions = BR_Session::instance()->getSessions($adventure->adventure_id, 'publish');
	}

	$speakers = $wpdb->get_results("
		SELECT speakers.*, players.player_first, players.player_last, players.player_display_name, players.player_picture, players.player_bio, players.player_company, players.player_website, players.player_linkedin FROM {$wpdb->prefix}br_speakers speakers
		LEFT JOIN {$wpdb->prefix}br_players players ON speakers.player_id = players.player_id
		WHERE speakers.adventure_id=$adventure->adventure_id
		ORDER BY speakers.speaker_first_name, speakers.speaker_last_name
	");
	$player_achievements = $wpdb->get_col("SELECT
	achievement_id FROM {$wpdb->prefix}br_player_achievement
	WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID");

	$all_achievements = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_achievements
	WHERE adventure_id=$adventure->adventure_id AND achievement_status='publish' ORDER BY achievement_id");
	$achievements = array();
	$achievement_badge = array();
	foreach($all_achievements as $ach){
		$achievements[$ach->achievement_id] = $ach->achievement_color;
		$achievement_badge[$ach->achievement_id] = $ach->achievement_badge;
	}
	$all_guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_xp DESC, guild_bloo DESC, guild_name ASC");

	$player_guilds = $wpdb->get_col("SELECT
	guild_id FROM {$wpdb->prefix}br_player_guild
	WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID GROUP BY guild_id");

	$guilds = [];
	$guild_logos = [];
	foreach($all_guilds as $t){
		$guilds[$t->guild_id] = $t->guild_color;
		$guild_logos[$t->guild_id] = $t->guild_logo;
	}

	$the_zone = ' GMT ' . date('P', time());

	// Same per-session gating as before (achievement path / guild
	// membership), pre-filtered once so the day-grouping loop below can
	// stay a single simple pass instead of WP's original duplicated
	// day-boundary markup.
	$visible_sessions = array_values(array_filter($sessions, function($s) use ($player_achievements, $player_guilds){
		return (!$s->achievement_id || in_array($s->achievement_id, $player_achievements))
			&& (!$s->guild_id || in_array($s->guild_id, $player_guilds));
	}));
?>

<div class="br-page">

	<div class="br-panel br-page-header">
		<div>
			<h1 class="br-page-title"><?= __("Schedule", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
	</div>

	<?php if ($visible_sessions):
		$current_day = null;
	?>
		<?php foreach ($visible_sessions as $key => $session):
			$session_day = date('Ymd', strtotime($session->session_start));
			if ($session_day !== $current_day):
				if ($current_day !== null) echo '</div></div>';
				$current_day = $session_day;
				$date_values = explode(',', date('Y,m,d,l,F', strtotime($session->session_start)));
		?>
		<div class="br-panel br-schedule-day">
			<div class="br-schedule-day-header">
				<span class="br-schedule-day-number"><?= esc_html($date_values[2]); ?></span>
				<div class="br-schedule-day-labels">
					<span class="br-schedule-day-weekday"><?= esc_html($date_values[3]); ?></span>
					<span class="br-schedule-day-month"><?= esc_html($date_values[4]); ?></span>
				</div>
			</div>
			<div>
		<?php endif; ?>

			<?php $session_speaker_ids = $session->speaker_ids ? explode(',', $session->speaker_ids) : []; ?>
			<div class="br-schedule-session-card" id="milestone-session-<?= $key; ?>" onClick="showOverlay('#session-detail-<?= $key; ?>'); activate('#milestone-session-<?= $key; ?>');">
				<?php if ($isGM || $isAdmin) { ?>
				<a class="br-schedule-session-edit br-btn br-btn-sm br-btn-green" onClick="event.stopPropagation();" href="<?= get_bloginfo('url') . "/new-session/?adventure_id=$adventure->adventure_id&session_id=$session->session_id"; ?>"><span class="icon icon-edit"></span></a>
				<?php } ?>
				<?php
				$session_image = '';
				foreach ($speakers as $sp) { if (in_array($sp->speaker_id, $session_speaker_ids)) { $session_image = $sp->speaker_picture; break; } }
				$session_image = $session_image ?: $adventure->adventure_badge;
				?>
				<div class="br-schedule-session-thumb" style="background-image: url(<?= esc_url($session_image); ?>);"></div>
				<div class="br-schedule-session-body">
					<h2 class="br-schedule-session-title"><?= esc_html($session->session_title); ?></h2>
					<div class="br-schedule-session-meta">
						<?php foreach ($speakers as $sp) { if (in_array($sp->speaker_id, $session_speaker_ids)) { ?>
						<strong><?= esc_html($sp->speaker_first_name . ' ' . $sp->speaker_last_name); ?></strong>
						<?php } } ?>
						<?php if ($session->achievement_id && !empty($achievement_badge[$session->achievement_id])) { ?>
						<span class="br-schedule-badge" style="background-image: url(<?= esc_url($achievement_badge[$session->achievement_id]); ?>);"></span>
						<?php } ?>
						<?php if ($session->guild_id && !empty($guild_logos[$session->guild_id])) { ?>
						<span class="br-schedule-badge" style="background-image: url(<?= esc_url($guild_logos[$session->guild_id]); ?>);"></span>
						<?php } ?>
						<span><span class="icon icon-time"></span> <?= date('H:i', strtotime($session->session_start)); ?> - <?= date('H:i', strtotime($session->session_end)); ?> | <?= esc_html($the_zone); ?></span>
						<?php if ($session->session_room) { ?><span class="br-badge br-badge-amber"><?= esc_html($session->session_room); ?></span><?php } ?>
					</div>
					<p class="br-schedule-session-desc"><?= wp_trim_words($session->session_description, 30); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
			</div>
		</div>

		<?php foreach ($visible_sessions as $key => $session):
			$session_speaker_ids = $session->speaker_ids ? explode(',', $session->speaker_ids) : [];
			$bg_image = $adventure->adventure_badge;
			$first_speaker = null;
			foreach ($speakers as $sp) { if (in_array($sp->speaker_id, $session_speaker_ids)) { $first_speaker = $sp; break; } }
			if ($first_speaker && $first_speaker->speaker_picture) $bg_image = $first_speaker->speaker_picture;
			elseif ($session->achievement_id && !empty($achievement_badge[$session->achievement_id])) $bg_image = $achievement_badge[$session->achievement_id];
			elseif ($session->guild_id && !empty($guild_logos[$session->guild_id])) $bg_image = $guild_logos[$session->guild_id];
		?>
		<div id="session-detail-<?= $key; ?>" class="overlay-layer br-modal-overlay session-detail">
			<div class="br-modal-backdrop" onClick="hideAllOverlay(); activate('#milestone-session-<?= $key; ?>');"></div>
			<div class="br-panel br-modal br-schedule-detail">
				<div class="br-modal-header">
					<h3 class="br-modal-header-title"><?= esc_html($session->session_title); ?></h3>
					<div class="br-modal-header-actions">
						<?php if ($isGM || $isAdmin) { ?>
						<a class="br-btn br-btn-mini br-btn-green" href="<?= get_bloginfo('url') . "/new-session/?adventure_id=$adventure->adventure_id&session_id=$session->session_id"; ?>">
							<span class="icon icon-edit"></span> <?= __("Edit", "bluerabbit"); ?>
						</a>
						<?php } ?>
						<button class="br-modal-close" onClick="hideAllOverlay(); activate('#milestone-session-<?= $key; ?>');"><span class="icon icon-cancel"></span></button>
					</div>
				</div>
				<div class="br-schedule-detail-hero" style="background-image: url(<?= esc_url($bg_image); ?>);"></div>
				<div class="br-schedule-detail-body">
					<p class="br-page-subtitle">
						<?= date('D M jS, Y', strtotime($session->session_start)); ?> —
						<?= date('H:i', strtotime($session->session_start)) . ' - ' . date('H:i', strtotime($session->session_end)); ?>
						<?= $session->session_room ? ' | ' . esc_html($session->session_room) : ''; ?>
					</p>

					<?php if ($session_speaker_ids) { ?>
					<div class="br-panel br-schedule-speakers-panel">
						<?php foreach ($speakers as $sp) { if (in_array($sp->speaker_id, $session_speaker_ids)) { ?>
						<a class="br-schedule-speaker-chip" href="<?= get_bloginfo('url') . "/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$sp->speaker_id"; ?>">
							<div class="br-schedule-speaker-avatar" style="background-image: url(<?= esc_url($sp->player_picture ? $sp->player_picture : $sp->speaker_picture); ?>);"></div>
							<div>
								<div class="br-schedule-speaker-name"><?= esc_html($sp->speaker_first_name . ' ' . $sp->speaker_last_name); ?></div>
								<?php if ($sp->speaker_company) { ?><div class="br-schedule-speaker-company"><?= esc_html($sp->speaker_company); ?></div><?php } ?>
							</div>
						</a>
						<?php } } ?>
					</div>
					<?php } ?>

					<?php if ($session->quest_id) { ?>
					<a class="br-btn br-btn-blue" href="<?= get_bloginfo('url') . "/$session->quest_type/?adventure_id=$adventure->adventure_id&questID=$session->quest_id"; ?>">
						<span class="icon icon-<?= esc_attr($session->quest_type); ?>"></span> <?= __("View") . " " . $session->quest_type; ?>
					</a>
					<?php } ?>

					<div class="br-schedule-detail-description"><?= apply_filters('the_content', $session->session_description); ?></div>

					<?php if ($session->speaker_bio) { ?>
					<div class="br-panel" style="margin-top:16px">
						<h3 class="br-panel-title"><?= __("Speaker Bio", "bluerabbit"); ?></h3>
						<?= apply_filters('the_content', $session->speaker_bio); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>

	<?php else: ?>
	<div class="br-panel br-empty">
		<span class="icon icon-warning"></span>
		<h3><?= __("No Sessions Available", "bluerabbit"); ?></h3>
	</div>
	<?php endif; ?>

</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

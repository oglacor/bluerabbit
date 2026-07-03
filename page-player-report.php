<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if ( ! ( $isGM || $isNPC || $isAdmin ) ) { ?>
<script>document.location.href="<?php bloginfo('url'); ?>/404/";</script>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$stats  = new BR_Stats();
$adv_id = (int) $adventure->adventure_id;

// All enrolled players ranked by XP
$all = $wpdb->get_results( $wpdb->prepare(
	"SELECT pa.player_id, pa.player_xp, pa.player_bloo, pa.player_ep, pa.player_level,
	        pa.player_last_login, pa.player_date_enrolled,
	        p.player_display_name, p.player_picture, p.player_email
	FROM {$wpdb->prefix}br_player_adventure pa
	LEFT JOIN {$wpdb->prefix}br_players p ON pa.player_id = p.player_id
	WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in'
	ORDER BY pa.player_xp DESC",
	$adv_id
) );

$total_players = count( $all );

// Total published quests for completion %
$total_quests = (int) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_quests
	WHERE adventure_id = %d AND quest_status = 'publish'
	AND quest_type IN ('quest','challenge','survey','mission')",
	$adv_id
) );

// Total achievements possible
$total_achievements = (int) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$wpdb->prefix}br_achievements
	WHERE adventure_id = %d AND achievement_status = 'publish'",
	$adv_id
) );

$xp_label   = $adventure->adventure_xp_label   ?: 'XP';
$bloo_label = $adventure->adventure_bloo_label  ?: 'BLOO';
$ep_label   = $adventure->adventure_ep_label    ?: 'EP';

$engagement_colors = [
	'on_fire'     => '#ff6b35',
	'active'      => '#4caf50',
	'moderate'    => '#2196f3',
	'cooling_off' => '#ff9800',
	'dormant'     => '#9e9e9e',
];
$engagement_labels = [
	'on_fire'     => 'On Fire',
	'active'      => 'Active',
	'moderate'    => 'Moderate',
	'cooling_off' => 'Cooling Off',
	'dormant'     => 'Dormant',
];
?>

<style>
/* ── Report-specific screen styles ──────────────────────────────── */
.pr-wrap {
	max-width: 960px;
	margin: 0 auto;
	padding: 20px 24px 60px;
	font-family: 'Roboto', sans-serif;
	color: #263238;
	background: #fff;
}
.pr-toolbar {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 16px 0 20px;
	border-bottom: 2px solid #e0e0e0;
	margin-bottom: 28px;
}
.pr-toolbar h2 { flex: 1; margin: 0; font-size: 20px; font-weight: 700; color: #0277bd; }
.pr-toolbar .pr-meta { font-size: 13px; color: #607d8b; }

/* Cover page */
.pr-cover {
	text-align: center;
	padding: 80px 40px;
	border: 2px solid #e0e0e0;
	border-radius: 12px;
	margin-bottom: 32px;
}
.pr-cover img.pr-badge { width: 100px; height: 100px; object-fit: contain; margin-bottom: 16px; }
.pr-cover h1 { font-size: 32px; font-weight: 300; color: #0277bd; margin: 0 0 8px; }
.pr-cover h3 { font-size: 16px; color: #607d8b; font-weight: 400; margin: 0; }
.pr-cover .pr-cover-stats {
	display: flex;
	justify-content: center;
	gap: 40px;
	margin-top: 28px;
}
.pr-cover .pr-cover-stat span { display: block; }
.pr-cover .pr-cover-stat .val { font-size: 36px; font-weight: 700; color: #01579b; }
.pr-cover .pr-cover-stat .lbl { font-size: 12px; color: #78909c; text-transform: uppercase; letter-spacing: 1px; }

/* Per-player card */
.pr-player {
	border: 1px solid #eceff1;
	border-radius: 12px;
	margin-bottom: 28px;
	overflow: hidden;
	page-break-inside: avoid;
	break-inside: avoid;
}
.pr-player-header {
	background: linear-gradient(135deg, #01579b, #0277bd);
	color: #fff;
	padding: 20px 24px;
	display: flex;
	align-items: center;
	gap: 16px;
}
.pr-avatar {
	width: 56px; height: 56px;
	border-radius: 50%;
	border: 2px solid rgba(255,255,255,0.4);
	object-fit: cover;
	flex-shrink: 0;
	background: #1565c0;
}
.pr-player-info { flex: 1; }
.pr-player-info h3 { margin: 0 0 4px; font-size: 18px; font-weight: 700; }
.pr-player-info .pr-email { font-size: 12px; opacity: 0.8; }
.pr-player-info .pr-meta-row { display: flex; gap: 16px; margin-top: 6px; font-size: 13px; opacity: 0.9; }
.pr-rank-badge {
	text-align: center;
	background: rgba(255,255,255,0.15);
	border-radius: 8px;
	padding: 8px 16px;
	flex-shrink: 0;
}
.pr-rank-badge .pr-rank-num { font-size: 28px; font-weight: 700; line-height: 1; }
.pr-rank-badge .pr-rank-lbl { font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }

/* Stats row */
.pr-stats-row {
	display: flex;
	border-bottom: 1px solid #eceff1;
}
.pr-stat-cell {
	flex: 1;
	text-align: center;
	padding: 16px 12px;
	border-right: 1px solid #eceff1;
}
.pr-stat-cell:last-child { border-right: none; }
.pr-stat-cell .stat-val { font-size: 26px; font-weight: 700; color: #0277bd; line-height: 1; }
.pr-stat-cell .stat-lbl { font-size: 11px; text-transform: uppercase; color: #90a4ae; margin-top: 4px; letter-spacing: 0.5px; }

/* Progress bar */
.pr-progress-wrap { padding: 16px 24px; border-bottom: 1px solid #eceff1; }
.pr-progress-label { display: flex; justify-content: space-between; font-size: 13px; color: #607d8b; margin-bottom: 6px; }
.pr-progress-bar { height: 10px; background: #eceff1; border-radius: 5px; overflow: hidden; }
.pr-progress-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, #0277bd, #4fc3f7); transition: width 0.8s ease; }

/* Engagement badge */
.pr-engagement {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 6px 14px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 600;
}
.eng-on_fire     { background: #fff3e0; color: #e65100; }
.eng-active      { background: #e8f5e9; color: #2e7d32; }
.eng-moderate    { background: #e3f2fd; color: #1565c0; }
.eng-cooling_off { background: #fff8e1; color: #e65100; }
.eng-dormant     { background: #f5f5f5; color: #616161; }

/* Charts area */
.pr-charts {
	display: flex;
	gap: 0;
	border-bottom: 1px solid #eceff1;
}
.pr-chart-box {
	flex: 1;
	padding: 20px;
	border-right: 1px solid #eceff1;
	text-align: center;
}
.pr-chart-box:last-child { border-right: none; }
.pr-chart-box h5 { font-size: 12px; text-transform: uppercase; color: #90a4ae; letter-spacing: 0.5px; margin: 0 0 12px; font-weight: 600; }
.pr-chart-box canvas { max-width: 160px; max-height: 160px; }

.pr-chart-box-wide { flex: 1.5; text-align: left; }

/* Footer row */
.pr-footer-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 24px;
	background: #fafafa;
	font-size: 12px;
	color: #90a4ae;
	gap: 16px;
	flex-wrap: wrap;
}

/* ── Print styles ────────────────────────────────────────────────── */
@media print {
	.pr-toolbar, .taskbar, .super-header, .hud-bar,
	.br-start-menu, .shepherd-element { display: none !important; }

	.pr-wrap { padding: 0; max-width: 100%; }

	.pr-player {
		page-break-after: always;
		break-after: page;
		border: 1px solid #ccc;
		margin: 0;
		box-shadow: none;
	}
	.pr-cover { page-break-after: always; break-after: page; border: 1px solid #ccc; }

	.pr-chart-box canvas { max-width: 140px; max-height: 140px; }

	body { background: #fff !important; }
}
</style>

<div class="pr-wrap">

	<!-- Toolbar (screen only) -->
	<div class="pr-toolbar">
		<h2><?php echo esc_html( $adventure->adventure_title ); ?> — <?php _e('Player Progress Report','bluerabbit'); ?></h2>
		<span class="pr-meta"><?php echo date('F j, Y'); ?></span>
		<button class="br-form-btn-blue" onclick="window.print()">
			<span class="icon icon-download"></span> <?php _e('Print / Save PDF','bluerabbit'); ?>
		</button>
	</div>

	<!-- Cover page -->
	<div class="pr-cover">
		<?php if ( $adventure->adventure_badge ) : ?>
		<img class="pr-badge" src="<?php echo esc_url( $adventure->adventure_badge ); ?>" alt="">
		<?php endif; ?>
		<h1><?php echo esc_html( $adventure->adventure_title ); ?></h1>
		<h3><?php _e('Player Progress Report','bluerabbit'); ?> &mdash; <?php echo date('F j, Y'); ?></h3>
		<div class="pr-cover-stats">
			<div class="pr-cover-stat">
				<span class="val"><?php echo $total_players; ?></span>
				<span class="lbl"><?php _e('Players','bluerabbit'); ?></span>
			</div>
			<div class="pr-cover-stat">
				<span class="val"><?php echo $total_quests; ?></span>
				<span class="lbl"><?php _e('Milestones','bluerabbit'); ?></span>
			</div>
			<div class="pr-cover-stat">
				<span class="val"><?php echo $total_achievements; ?></span>
				<span class="lbl"><?php _e('Achievements','bluerabbit'); ?></span>
			</div>
		</div>
	</div>

	<?php
	$chart_js = '';
	foreach ( $all as $rank => $p ) :
		$pid = (int) $p->player_id;

		// Core data per player
		$quests_done = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}br_player_posts
			WHERE player_id = %d AND adventure_id = %d",
			$pid, $adv_id
		) );
		$ach_done = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}br_player_achievement
			WHERE player_id = %d AND adventure_id = %d",
			$pid, $adv_id
		) );
		$guild_name = $wpdb->get_var( $wpdb->prepare(
			"SELECT g.guild_name FROM {$wpdb->prefix}br_player_guild pg
			JOIN {$wpdb->prefix}br_guilds g ON pg.guild_id = g.guild_id
			WHERE pg.player_id = %d AND pg.adventure_id = %d LIMIT 1",
			$pid, $adv_id
		) );

		$type_rows = $stats->get_player_type_completion( $pid, $adv_id );
		$engagement = $stats->get_player_engagement( $pid, $adv_id );

		$completion_pct = $total_quests > 0 ? round( ( $quests_done / $total_quests ) * 100, 1 ) : 0;
		$eng_score  = $engagement['score'];
		$eng_level  = $engagement['level'];
		$eng_color  = $engagement_colors[ $eng_level ] ?? '#9e9e9e';
		$eng_label  = $engagement_labels[ $eng_level ] ?? $eng_level;
		$days_ago   = $engagement['days_inactive'];

		$avatar_url = $p->player_picture ?: get_template_directory_uri() . '/images/no-profile.png';

		// Build chart data for JS (rendered after DOM)
		$type_labels = [];
		$type_done   = [];
		$type_remain = [];
		$type_colors = ['#0277bd','#f57c00','#388e3c','#7b1fa2','#c62828'];
		foreach ( $type_rows as $i => $tr ) {
			$type_labels[] = ucfirst( $tr['quest_type'] );
			$type_done[]   = (int) $tr['completed'];
			$type_remain[] = max( 0, (int) $tr['total'] - (int) $tr['completed'] );
		}

		$eng_break = $engagement['breakdown'];
		$eng_chart_labels = ['Recency','Frequency','Completion','Progression','Economy'];
		$eng_chart_vals   = [
			$eng_break['recency']['score'],
			$eng_break['frequency']['score'],
			$eng_break['completion']['score'],
			$eng_break['progression']['score'],
			$eng_break['economy']['score'],
		];
		$eng_chart_colors = ['#0288d1','#43a047','#fb8c00','#8e24aa','#e53935'];

		$cid = 'p' . $pid;

		// Accumulate chart JS to run after all DOM is ready
		$chart_js .= "
createReportChart('#{$cid}-type-done', " . json_encode( $type_done ) . ", " . json_encode( $type_labels ) . ", " . json_encode( $type_colors ) . ");
createReportChart('#{$cid}-engagement', " . json_encode( $eng_chart_vals ) . ", " . json_encode( $eng_chart_labels ) . ", " . json_encode( $eng_chart_colors ) . ");
";
	?>

	<!-- Player card -->
	<div class="pr-player">

		<!-- Header -->
		<div class="pr-player-header">
			<img class="pr-avatar" src="<?php echo esc_url( $avatar_url ); ?>" alt="">
			<div class="pr-player-info">
				<h3><?php echo esc_html( $p->player_display_name ?: $p->player_email ); ?></h3>
				<div class="pr-email"><?php echo esc_html( $p->player_email ); ?></div>
				<div class="pr-meta-row">
					<span><?php _e('Level','bluerabbit'); ?> <?php echo (int) $p->player_level; ?></span>
					<?php if ( $guild_name ) : ?>
					<span>&#x25cf; <?php echo esc_html( $guild_name ); ?></span>
					<?php endif; ?>
					<span>&#x25cf; <?php _e('Enrolled','bluerabbit'); ?> <?php echo $p->player_date_enrolled ? date('M j, Y', strtotime($p->player_date_enrolled)) : '—'; ?></span>
				</div>
			</div>
			<div class="pr-rank-badge">
				<div class="pr-rank-num">#<?php echo $rank + 1; ?></div>
				<div class="pr-rank-lbl"><?php printf( __('of %d','bluerabbit'), $total_players ); ?></div>
			</div>
		</div>

		<!-- Stats row -->
		<div class="pr-stats-row">
			<div class="pr-stat-cell">
				<div class="stat-val"><?php echo number_format( $p->player_xp ); ?></div>
				<div class="stat-lbl"><?php echo esc_html( $xp_label ); ?></div>
			</div>
			<div class="pr-stat-cell">
				<div class="stat-val"><?php echo number_format( $p->player_bloo ); ?></div>
				<div class="stat-lbl"><?php echo esc_html( $bloo_label ); ?></div>
			</div>
			<div class="pr-stat-cell">
				<div class="stat-val"><?php echo number_format( $p->player_ep ); ?></div>
				<div class="stat-lbl"><?php echo esc_html( $ep_label ); ?></div>
			</div>
			<div class="pr-stat-cell">
				<div class="stat-val"><?php echo $quests_done; ?>/<?php echo $total_quests; ?></div>
				<div class="stat-lbl"><?php _e('Milestones','bluerabbit'); ?></div>
			</div>
			<div class="pr-stat-cell">
				<div class="stat-val"><?php echo $ach_done; ?>/<?php echo $total_achievements; ?></div>
				<div class="stat-lbl"><?php _e('Achievements','bluerabbit'); ?></div>
			</div>
		</div>

		<!-- Completion bar -->
		<div class="pr-progress-wrap">
			<div class="pr-progress-label">
				<span><?php _e('Overall Completion','bluerabbit'); ?></span>
				<span><strong><?php echo $completion_pct; ?>%</strong></span>
			</div>
			<div class="pr-progress-bar">
				<div class="pr-progress-fill" data-pct="<?php echo $completion_pct; ?>"></div>
			</div>
		</div>

		<!-- Charts -->
		<?php if ( ! empty( $type_rows ) ) : ?>
		<div class="pr-charts">
			<div class="pr-chart-box">
				<h5><?php _e('Completion by Type','bluerabbit'); ?></h5>
				<canvas id="<?php echo $cid; ?>-type-done" width="160" height="160"></canvas>
			</div>
			<div class="pr-chart-box">
				<h5><?php _e('Engagement Breakdown','bluerabbit'); ?></h5>
				<canvas id="<?php echo $cid; ?>-engagement" width="160" height="160"></canvas>
			</div>
			<div class="pr-chart-box pr-chart-box-wide">
				<h5><?php _e('Engagement Details','bluerabbit'); ?></h5>
				<div style="margin-bottom:10px;">
					<span class="pr-engagement eng-<?php echo $eng_level; ?>"><?php echo $eng_label; ?> &mdash; <?php echo $eng_score; ?>/100</span>
				</div>
				<table style="font-size:12px; width:100%; color:#546e7a; line-height:1.8;">
					<tr><td><?php _e('Recency','bluerabbit'); ?></td><td style="text-align:right;font-weight:700;"><?php echo $eng_break['recency']['score']; ?>/25</td></tr>
					<tr><td><?php _e('Frequency','bluerabbit'); ?></td><td style="text-align:right;font-weight:700;"><?php echo $eng_break['frequency']['score']; ?>/25</td></tr>
					<tr><td><?php _e('Completion','bluerabbit'); ?></td><td style="text-align:right;font-weight:700;"><?php echo $eng_break['completion']['score']; ?>/25</td></tr>
					<tr><td><?php _e('Progression','bluerabbit'); ?></td><td style="text-align:right;font-weight:700;"><?php echo $eng_break['progression']['score']; ?>/15</td></tr>
					<tr><td><?php _e('Economy','bluerabbit'); ?></td><td style="text-align:right;font-weight:700;"><?php echo $eng_break['economy']['score']; ?>/10</td></tr>
				</table>
			</div>
		</div>
		<?php endif; ?>

		<!-- Footer -->
		<div class="pr-footer-row">
			<span>
				<?php _e('Last login:','bluerabbit'); ?>
				<?php
				if ( $p->player_last_login && strtotime($p->player_last_login) > 0 ) {
					echo esc_html( date('M j, Y', strtotime($p->player_last_login)) );
					if ( $days_ago > 0 ) echo ' (' . round($days_ago) . ' ' . __('days ago','bluerabbit') . ')';
				} else {
					_e('Never','bluerabbit');
				}
				?>
			</span>
			<span><?php echo esc_html( $adventure->adventure_title ); ?> &mdash; <?php _e('Player Progress Report','bluerabbit'); ?></span>
		</div>

	</div><!-- .pr-player -->

	<?php endforeach; ?>

</div><!-- .pr-wrap -->

<script>
jQuery(function($) {
	// Apply progress bar widths from data attribute
	$('.pr-progress-fill').each(function() {
		$(this).css('width', $(this).data('pct') + '%');
	});
	<?php echo $chart_js; ?>
});
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

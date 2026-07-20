<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
$limit = (int) $leaderboard_limit;
if ($limit <= 0) { $limit = 10; }
$players = $wpdb->get_results($wpdb->prepare("
	SELECT
		a.player_id, a.achievement_id, a.player_xp, a.player_bloo, a.player_level, a.player_gpa,
		b.player_display_name, b.player_picture, b.player_email, b.player_hexad_slug, b.player_hexad
	FROM {$wpdb->prefix}br_player_adventure a
	LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
	WHERE a.adventure_id=%d AND a.player_adventure_status='in' AND a.player_adventure_role='player'
	GROUP BY a.player_id
	ORDER BY a.player_xp DESC, a.player_level DESC, a.player_bloo DESC, a.player_id ASC
	LIMIT %d
", $adventure->adventure_id, $limit));
$medal_colors = ['#f7cb15', '#b0bec5', '#cd7f32'];
?>

<div class="layer background fixed fixed-bg" style="background-image: url(<?= $bg; ?>);"></div>

<div class="br-page">

	<div class="br-panel br-leaderboard-header">
		<h1 class="br-page-title br-leaderboard-title"><?= __("Leaderboard", "bluerabbit"); ?></h1>
		<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
	</div>

	<div class="br-panel">
		<table class="br-table" id="br-leaderboard-table">
			<thead>
				<tr>
					<th class="text-center br-th-narrow"><?php _e("Rank", "bluerabbit"); ?></th>
					<th><?php _e("Player", "bluerabbit"); ?></th>
					<th class="text-center"><?php _e("Level", "bluerabbit"); ?></th>
					<?php if($isGM){ ?>
					<th class="text-center"><?= $xp_label; ?></th>
					<th class="text-center"><?= $bloo_label; ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach($players as $key => $p) {
					$rank = $key + 1;
					$is_me = ($p->player_id == $current_user->ID);
				?>
				<tr<?= $is_me ? ' class="br-leaderboard-highlight"' : ''; ?>>
					<td class="text-center">
						<?php if($rank <= 3){ ?>
						<span class="br-leaderboard-medal" style="background:<?= $medal_colors[$rank - 1]; ?>"><?= $rank; ?></span>
						<?php } else { ?>
						<span class="br-leaderboard-rank"><?= $rank; ?></span>
						<?php } ?>
					</td>
					<td>
						<div class="br-leaderboard-player">
							<div class="br-leaderboard-avatar" style="background-image:url(<?= esc_url($p->player_picture); ?>)"></div>
							<span class="br-leaderboard-name"><?= esc_html($p->player_display_name ?: $p->player_nickname ?? ''); ?></span>
						</div>
					</td>
					<td class="text-center">
						<span class="br-leaderboard-level"><span class="icon icon-level"></span> <?= $p->player_level; ?></span>
					</td>
					<?php if($isGM){ ?>
					<td class="text-center br-leaderboard-xp"><?= number_format($p->player_xp); ?></td>
					<td class="text-center br-leaderboard-bloo"><?= number_format($p->player_bloo); ?></td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

</div>

<style>
/* .br-page has no z-index of its own (see css/br-table.scss - kept that way so
   drawers elsewhere don't get trapped below the header/footer's stacking context).
   This is the only page that pairs .br-page with the older fixed .layer.background
   (z-index:10), so it needs its own explicit z-index here to paint above it. */
.br-page { position: relative; z-index: 11; }
.br-leaderboard-medal {
	display: inline-flex; align-items: center; justify-content: center;
	width: 36px; height: 36px; border-radius: 50%;
	font-family: "proxima-nova-extra-condensed", sans-serif;
	font-size: 20px; font-weight: 900; color: #04161e;
}
.br-leaderboard-rank {
	font-family: "proxima-nova-extra-condensed", sans-serif;
	font-size: 22px; font-weight: 700; color: rgba(255,255,255,0.35);
}
.br-leaderboard-player {
	display: flex; align-items: center; gap: 12px;
}
.br-leaderboard-avatar {
	width: 40px; height: 40px; border-radius: 50%;
	background-size: cover; background-position: center;
	border: 2px solid rgba(28,194,235,0.25); flex-shrink: 0;
}
.br-leaderboard-name {
	font-size: 15px; font-weight: 600; color: #ffffff;
}
.br-leaderboard-level {
	font-family: "proxima-nova-extra-condensed", sans-serif;
	font-size: 20px; font-weight: 900; color: #9f40e2;
}
.br-leaderboard-level .icon { font-size: 14px; margin-right: 2px; }
@media (max-width: 480px) {
	.br-leaderboard-avatar { width: 32px; height: 32px; }
	.br-leaderboard-medal  { width: 28px; height: 28px; font-size: 16px; }
}
</style>

<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_item_nonce'); ?>">
<?php } else { ?>
	<script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

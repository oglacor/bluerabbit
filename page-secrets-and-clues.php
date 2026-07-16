<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$player_id_get = ($isGM || $isAdmin || $isNPC) ? br_require_id('player_id', false) : null;
	if($player_id_get){
		$the_player_id = $player_id_get;
		$current_player = BR_Player::instance()->getPlayerData($the_player_id);
	}else{
		$the_player_id = $current_user->ID;
	}
	$myquests = $wpdb->get_results("SELECT
		a.pp_grade, a.pp_modified, a.quest_id,a.pp_status,
		b.*

		FROM {$wpdb->prefix}br_player_posts a
		LEFT JOIN {$wpdb->prefix}br_quests b
		ON a.quest_id = b.quest_id

		WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id AND b.quest_status='publish'
		ORDER BY a.pp_modified
	");
?>

<div class="br-page">

	<div class="br-panel br-secrets-title">
		<h1 class="br-page-title"><?= __("Secret Messages", "bluerabbit"); ?></h1>
	</div>

	<?php if ($myquests) { ?>
	<div class="br-card-grid">
		<?php foreach ($myquests as $q) { ?>
			<?php if ($q->quest_success_message) { ?>
			<div class="br-panel br-secrets-card" <?= br_color_attr($q->quest_color ?? '') ?>>
				<h2 class="br-secrets-message-header"><?= esc_html($q->quest_title); ?></h2>
				<div class="br-secrets-message-body"><?= apply_filters('the_content', $q->quest_success_message); ?></div>
			</div>
			<?php } ?>
		<?php } ?>
	</div>
	<?php } else { ?>
	<div class="br-panel br-empty">
		<span class="icon icon-lock"></span>
		<h3><?= __("No messages found", "bluerabbit"); ?></h3>
		<p><?= __("When you find secret messages from quests they will appear here", "bluerabbit"); ?></p>
		<a class="br-btn br-btn-blue" href="<?= get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id"; ?>">
			<span class="icon icon-journey"></span> <?= __("Go to the Journey", "bluerabbit"); ?>
		</a>
	</div>
	<?php } ?>

</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

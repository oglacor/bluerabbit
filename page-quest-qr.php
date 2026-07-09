<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

if (!$token) { ?>
	<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php die(); } ?>

<?php
$quest = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_qr_token = %s AND quest_status = 'publish'", $token
));

if (!$quest) { ?>
	<div class="layer background fixed top left sq-full amber-bg-800 blend-overlay opacity-40"></div>
	<div class="relative layer base boxed text-center min-w-300 max-w-900 padding-20">
		<h5 class="font text-center uppercase w900 _18 white-color amber-bg-400 padding-10">
			<span class="icon icon-trash icon-lg"></span><br><?= __("Invalid QR code","bluerabbit"); ?>
		</h5>
	</div>
<?php die(); } ?>

<?php
$adventure   = BR_Adventure::instance()->getAdventure($quest->adventure_id);
$adv_child_id  = $adventure->adventure_id;
$adv_parent_id = $adventure->adventure_parent ? $adventure->adventure_parent : $adventure->adventure_id;

if ($adventure->adventure_gmt) date_default_timezone_set($adventure->adventure_gmt);
$today     = date('Y-m-d H:i:s');
$player_id = $current_user->ID;

$enrolled = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE player_adventure_status='in' AND player_id=$player_id AND adventure_id=$adv_child_id");

if (!$enrolled) { ?>
	<div class="layer background fixed top left sq-full red-bg-800 blend-overlay opacity-40"></div>
	<div class="relative layer base boxed text-center min-w-300 max-w-900 padding-20">
		<h5 class="font text-center uppercase w900 _18 white-color red-bg-400 padding-10">
			<span class="icon icon-cancel icon-lg"></span><br><?= __("You are not enrolled in this adventure","bluerabbit"); ?>
		</h5>
		<a href="<?= get_bloginfo('url'); ?>" class="form-ui blue-bg-700 big"><span class="icon icon-home"></span> <?= __("Home","bluerabbit"); ?></a>
	</div>
<?php die(); } ?>

<?php
$already_done = $wpdb->get_row($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_player_posts WHERE quest_id = %d AND player_id = %d AND adventure_id = %d AND pp_status = 'publish'",
	$quest->quest_id, $player_id, $adv_child_id
));

$adv_settings = BR_Config::instance()->getSettings($adv_parent_id);
$xp_label   = $adventure->adventure_xp_label   ? $adventure->adventure_xp_label   : "XP";
$bloo_label  = $adventure->adventure_bloo_label  ? $adventure->adventure_bloo_label  : "BLOO";
$ep_label    = $adventure->adventure_ep_label    ? $adventure->adventure_ep_label    : "EP";

if (!$already_done) {
	// Insert completion record, bypassing all requirements
	$sql = "INSERT INTO {$wpdb->prefix}br_player_posts (quest_id, adventure_id, player_id, pp_date, pp_modified, pp_content, pp_type, pp_status)
		VALUES (%d, %d, %d, %s, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE pp_modified=%s, pp_status=%s";
	$wpdb->query($wpdb->prepare($sql,
		$quest->quest_id, $adv_child_id, $player_id, $today, $today, 'QR Code', 'qr', 'publish',
		$today, 'publish'
	));

	// Grant achievement reward if set
	$achievement_reward = null;
	if ($quest->mech_achievement_reward) {
		$prev_ach = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_player_achievement WHERE player_id=$player_id AND adventure_id=$adv_child_id AND achievement_id=$quest->mech_achievement_reward");
		if (!$prev_ach) {
			$wpdb->query($wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}br_player_achievement (player_id, adventure_id, achievement_id, achievement_applied) VALUES (%d, %d, %d, %s)",
				$player_id, $adv_child_id, $quest->mech_achievement_reward, $today
			));
		}
		$achievement_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_achievements WHERE achievement_id=$quest->mech_achievement_reward");
	}

	// Grant item reward if set
	$item_reward = null;
	if ($quest->mech_item_reward) {
		$prev_item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_transactions WHERE player_id=$player_id AND adventure_id=$adv_child_id AND object_id=$quest->mech_item_reward AND trnx_status='publish'");
		if (!$prev_item) {
			$item_reward = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$quest->mech_item_reward AND item_status='publish'");
			if ($item_reward) {
				$wpdb->query($wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}br_transactions (player_id, adventure_id, object_id, trnx_author, trnx_amount, trnx_type, trnx_date, trnx_modified) VALUES (%d, %d, %d, %d, %d, %s, %s, %s)",
					$player_id, $adv_child_id, $quest->mech_item_reward, $player_id, 0, $item_reward->item_type, $today, $today
				));
			}
		}
	}

	BR_Activity::instance()->logActivity($adv_child_id, 'complete', 'quest-qr', $quest->quest_id, $player_id);
	$playerState = BR_Player::instance()->resetPlayer($adv_child_id, $player_id);
}
?>

<div class="layer background fixed top left sq-full blue-grey-bg-800 blend-overlay opacity-80" style="background-image: url('<?= $quest->mech_badge; ?>');"></div>

<video id="overlay-background-video" loop autoplay class="overlay-background-video layer overlay">
	<source src="<?= get_bloginfo('template_directory')."/video/aetherite.mp4"; ?>">
</video>

<div class="layer base relative boxed text-center min-w-300 max-w-900 padding-20 white-color">
	<h3><?= esc_html($quest->quest_title); ?></h3>
	<h1 class="font w900 uppercase padding-10">
		<?= $already_done ? __("Already completed!","bluerabbit") : __("Quest completed!","bluerabbit"); ?>
	</h1>

	<?php if(!$already_done && $quest->quest_success_message){ ?>
		<div class="success-message text-center white-color padding-10 max-w-900 boxed">
			<?= apply_filters('the_content', $quest->quest_success_message); ?>
		</div>
	<?php } ?>

	<?php if(!$already_done){ ?>
	<div class="earned-resources text-center white-color relative layer base padding-20 max-w-500 boxed min-h-70">
		<div class="background layer bottom-1 absolute sq-full blue-gradient-A400 opacity-70"></div>
		<div class="layer base relative">
			<div class="icon-group inline-table" id="xp-number-earned-<?= $quest->quest_id; ?>">
				<span class="br-icon-btn br-icon-btn-sm br-icon-btn-amber"><span class="icon icon-star white-color"></span></span>
				<span class="icon-content">
					<span class="line amber-400 br-text-18 w900 number">0</span>
					<span class="line white-color br-text-12 w300 kerning-3"><?= $xp_label; ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?= $quest->mech_xp; ?>">
			</div>
			<div class="icon-group inline-table" id="bloo-number-earned-<?= $quest->quest_id; ?>">
				<span class="br-icon-btn br-icon-btn-sm br-icon-btn-green"><span class="icon icon-bloo white-color"></span></span>
				<span class="icon-content">
					<span class="line light-green-400 br-text-18 w900 number">0</span>
					<span class="line white-color br-text-12 w300 kerning-3"><?= $bloo_label; ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?= $quest->mech_bloo; ?>">
			</div>
			<?php if(isset($adv_settings['use_encounters']['value']) && $adv_settings['use_encounters']['value'] > 0){ ?>
			<div class="icon-group inline-table" id="ep-number-earned-<?= $quest->quest_id; ?>">
				<span class="br-icon-btn br-icon-btn-sm br-icon-btn-cyan"><span class="icon icon-activity blue-grey-900"></span></span>
				<span class="icon-content">
					<span class="line cyan-A400 br-text-18 w900 number">0</span>
					<span class="line white-color br-text-12 w300 kerning-3"><?= $ep_label; ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?= $quest->mech_ep; ?>">
			</div>
			<script>animateNumber('#ep-number-earned-<?= $quest->quest_id; ?>', 500, 750);</script>
			<?php } ?>
			<script>animateNumber('#xp-number-earned-<?= $quest->quest_id; ?>', 1000, 250);</script>
			<script>animateNumber('#bloo-number-earned-<?= $quest->quest_id; ?>', 750, 500);</script>
		</div>
	</div>

	<div class="rewards text-center padding-10">
		<?php if(isset($achievement_reward) && $achievement_reward){ ?>
		<div class="text-center relative padding-10 inline-block w-250">
			<div class="background layer absolute sq-full purple-gradient-400 opacity-50"></div>
			<div class="layer relative base">
				<img src="<?= $achievement_reward->achievement_badge; ?>" class="w-150 margin-5 overflow-hidden border rounded-max layer relative base cursor-pointer">
				<h4 class="line white-color font w100 _12 opacity-80"><?= __("You earned an achievement","bluerabbit"); ?></h4>
				<h3 class="line white-color font w900 _18"><?= esc_html($achievement_reward->achievement_name); ?></h3>
			</div>
		</div>
		<?php } ?>
		<?php if(isset($item_reward) && $item_reward){ ?>
		<div class="text-center relative padding-10 inline-block w-250">
			<div class="background layer absolute sq-full teal-gradient-400 opacity-50"></div>
			<img src="<?= $item_reward->item_badge; ?>" class="w-150 margin-5 overflow-hidden border rounded-max layer relative base">
			<br>
			<div class="icon-group inline-table layer relative base">
				<a class="br-icon-btn br-icon-btn-teal" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adv_child_id"; ?>">
					<span class="icon icon-backpack white-color"></span>
				</a>
				<span class="icon-content">
					<span class="line white-color font w100 _12 opacity-80"><?= __("You found an item","bluerabbit"); ?></span>
					<span class="line white-color font w900 _18"><?= esc_html($item_reward->item_name); ?></span>
				</span>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } // !already_done ?>

	<div class="padding-10 w-full text-center">
		<a href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id"; ?>" class="form-ui white-bg padding-5 margin-5 blue-A400 br-text-18 w900 uppercase opacity-50">
			<span class="icon icon-journey"></span><?= __("Back to the journey!","bluerabbit"); ?>
		</a>
		<?php
		if(!$already_done && isset($playerState)){
			$nextMilestone = BR_Progression::instance()->getNextAvailableMilestone($adv_parent_id, $adv_child_id, $quest->quest_id, $adventure, $playerState);
			if ($nextMilestone): ?>
			<a href="<?= get_bloginfo('url')."/$nextMilestone->quest_type/?questID=$nextMilestone->quest_id&adventure_id=$adv_child_id"; ?>" class="form-ui orange-bg-400 white-color padding-5 margin-5 br-text-18 w900 uppercase">
				<?= esc_html($nextMilestone->quest_title); ?> <span class="icon icon-arrow-right"></span>
			</a>
			<?php endif;
		} ?>
	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

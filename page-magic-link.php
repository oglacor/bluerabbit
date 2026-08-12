<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php
/**
 * page-magic-link.php — where a redeemed achievement code lands.
 *
 * A magic link arrives from outside the app (a printed card, a chat message, an
 * email), so it has to land on a real URL - which is why this stays a full page
 * rather than becoming an overlay on the journey. What it borrows is the look:
 * the same panel brCelebrate() opens for a level-up or a condition-awarded
 * achievement, so earning something by code feels like earning anything else.
 *
 * Three outcomes, one panel: redeemed, refused (with a reason), and no such code.
 */
$code = isset($_GET['c']) ? trim($_GET['c']) : '';

if ($code) {
	if (isset($_GET['adv'])) {
		$adv_id = (int) $_GET['adv'];
	} elseif ($config['default_adventure']['value']) {
		$adv_id = (int) $config['default_adventure']['value'];
	} else {
		wp_safe_redirect(get_bloginfo('url') . '/404');
		exit;
	}
	$adventure = BR_Adventure::instance()->getAdventure($adv_id);

	$code  = strtolower($code);
	$c     = BR_Achievement::instance()->magicCode($code, $adv_id);
	$error = isset($c['errors']) && is_array($c['errors']) ? $c['errors'] : [];
	$ach   = isset($c['c']) ? $c['c'] : null;
	// magicCode() returns early without either key when its nonce check fails, and the
	// success branch below dereferences $ach - so no achievement is itself a refusal
	// rather than a fatal.
	if (!$ach && !$error) {
		$error = ['cancel' => __("This code could not be checked. Please reload and try again.", "bluerabbit")];
	}
	$home  = get_bloginfo('url') . "/adventure/?adventure_id=" . ($adventure ? $adventure->adventure_id : $adv_id);
?>

<div class="br-magic-page">
	<!-- Kept from the original: the achievement art washed behind everything, with
	     the explosion over it. It is the one part of this screen that already read
	     as a reward, so only the panel in front of it changed. -->
	<div class="br-magic-bg" style="background-image:url('<?= esc_url($ach && $ach->achievement_badge ? $ach->achievement_badge : get_bloginfo('template_directory') . '/images/ghost.png'); ?>')"></div>
	<?php if (empty($error)) { ?>
	<div class="br-magic-explosion" style="background-image:url('<?= esc_url(get_bloginfo('template_directory') . '/images/explosion-lq.gif'); ?>')"></div>
	<?php } ?>

	<div class="br-rewards-modal br-magic-panel<?= empty($error) ? '' : ' br-magic-panel-refused'; ?>">
		<div class="br-celebrate-head">
			<div class="br-celebrate-rays" aria-hidden="true"></div>
			<?php if (empty($error)) { ?>
				<h2 class="br-celebrate-title"><?= __("Congratulations!", "bluerabbit"); ?></h2>
				<p class="br-celebrate-line"><?= __("You just earned an achievement.", "bluerabbit"); ?></p>
			<?php } else { ?>
				<h2 class="br-celebrate-title"><?= __("Not this time", "bluerabbit"); ?></h2>
				<p class="br-celebrate-line"><?= __("This code could not be redeemed.", "bluerabbit"); ?></p>
			<?php } ?>
		</div>

		<div class="br-rewards-modal-body">
			<?php if (empty($error)) { ?>
				<div class="br-celebrate-card">
					<?php if ($ach->achievement_badge) { ?>
					<div class="br-celebrate-art" style="background-image:url(<?= esc_url($ach->achievement_badge); ?>)"></div>
					<?php } else { ?>
					<div class="br-celebrate-art br-celebrate-art-icon"><span class="icon icon-achievement"></span></div>
					<?php } ?>
					<div class="br-celebrate-card-info">
						<div class="br-celebrate-card-title"><?= esc_html($ach->achievement_name); ?></div>
					</div>
				</div>
				<?php if (trim(strip_tags($ach->achievement_content)) !== '') { ?>
				<div class="br-magic-story"><?= apply_filters('the_content', $ach->achievement_content); ?></div>
				<?php } ?>

				<audio id="audio-funky" preload="auto">
					<source src="<?= esc_url(get_bloginfo('template_directory') . '/audio/funk' . rand(1, 9) . '.mp3'); ?>" type="audio/mpeg">
				</audio>
			<?php } else { ?>
				<?php foreach ($error as $key => $err) { ?>
				<div class="br-magic-reason">
					<span class="icon icon-<?= esc_attr($key); ?>"></span>
					<span><?= esc_html($err); ?></span>
				</div>
				<?php } ?>

				<?php if (!empty($c['held_achievement'])) { $held = $c['held_achievement']; ?>
				<!-- The player already holds a groupmate of this branch achievement, so
				     showing which one turns "you can't have this" into an explanation. -->
				<div class="br-celebrate-card">
					<?php if ($held->achievement_badge) { ?>
					<div class="br-celebrate-art" style="background-image:url(<?= esc_url($held->achievement_badge); ?>)"></div>
					<?php } else { ?>
					<div class="br-celebrate-art br-celebrate-art-icon"><span class="icon icon-achievement"></span></div>
					<?php } ?>
					<div class="br-celebrate-card-info">
						<div class="br-celebrate-card-sub"><?= __("You already have", "bluerabbit"); ?></div>
						<div class="br-celebrate-card-title"><?= esc_html($held->achievement_name); ?></div>
					</div>
				</div>
				<?php } ?>
			<?php } ?>
		</div>

		<div class="br-rewards-modal-footer">
			<p class="br-celebrate-outro"><?= __("Keep moving forward!", "bluerabbit"); ?></p>
			<a class="br-btn br-btn-green br-rewards-claim-btn" href="<?= esc_url($home); ?>">
				<span class="icon icon-home"></span> <?= __("Back to home", "bluerabbit"); ?>
			</a>
		</div>
	</div>
</div>

<?php } else {
	$home = get_bloginfo('url') . "/adventure/";
?>

<div class="br-magic-page">
	<div class="br-magic-bg" style="background-image:url('<?= esc_url(get_bloginfo('template_directory') . '/images/ghost.png'); ?>')"></div>

	<div class="br-rewards-modal br-magic-panel br-magic-panel-refused">
		<div class="br-celebrate-head">
			<div class="br-celebrate-rays" aria-hidden="true"></div>
			<h2 class="br-celebrate-title"><?= __("Code doesn't exist", "bluerabbit"); ?></h2>
			<p class="br-celebrate-line"><?= __("Check the code and try again.", "bluerabbit"); ?></p>
		</div>
		<div class="br-rewards-modal-body">
			<div class="br-magic-reason">
				<span class="icon icon-trash"></span>
				<span><?= __("No achievement is linked to that code.", "bluerabbit"); ?></span>
			</div>
		</div>
		<div class="br-rewards-modal-footer">
			<a class="br-btn br-btn-green br-rewards-claim-btn" href="<?= esc_url($home); ?>">
				<span class="icon icon-home"></span> <?= __("Back to home", "bluerabbit"); ?>
			</a>
		</div>
	</div>
</div>

<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

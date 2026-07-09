<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php $player_account = BR_Player::instance()->getPlayerData($current_user->ID); ?>
<?php $uMeta = get_user_meta($current_user->ID); ?>

<?php $picture = $player_account->player_picture ? $player_account->player_picture : $randprof; ?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar" style="background-image:url(<?= $picture; ?>)"></div>
		<div>
			<h1 class="br-page-title"><?= __("My Account", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= $current_user->user_login; ?> &middot; <?= __("Level", "bluerabbit"); ?> <?= $player_account->player_absolute_level; ?></span>
		</div>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="account-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this)"><span class="icon icon-player"></span> <?= __("Profile", "bluerabbit"); ?></button>
		<?php if ($config['use_hexad']['value'] > 0) { ?>
		<button class="br-tab-btn" onClick="brScrollTo('hexad', this)"><span class="icon icon-hexad"></span> <?= __("Player type", "bluerabbit"); ?></button>
		<?php } ?>
		<?php if ($config['show_upgrade']['value'] > 0) { ?>
		<button class="br-tab-btn" onClick="brScrollTo('my-account', this)"><span class="icon icon-settings"></span> <?= __("Account", "bluerabbit"); ?></button>
		<?php } ?>
	</div>

	<!-- Profile -->
	<div class="br-scroll-section" id="general">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-player"></span> <?= __("Profile", "bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Profile Picture', 'bluerabbit'); ?></label>
			<div class="br-gallery br-gallery-single">
				<?php $thumb_id = 'the_player_picture'; $file = $picture; $callback = ",'profile-autosave'"; include (TEMPLATEPATH . '/gallery-item.php'); ?>
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label" for="the_first_name"><?= __('First name', 'bluerabbit'); ?></label>
				<input class="br-input" id="the_first_name" name="the_first_name" type="text" value="<?= $player_account->player_first ? $player_account->player_first : $uMeta['first_name'][0]; ?>" onChange="updateProfile();">
			</div>
			<div class="br-form-group">
				<label class="br-form-label" for="the_last_name"><?= __('Last name', 'bluerabbit'); ?></label>
				<input class="br-input" id="the_last_name" name="the_last_name" type="text" value="<?= $player_account->player_last ? $player_account->player_last : $uMeta['last_name'][0]; ?>" onChange="updateProfile();">
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __('Nickname', 'bluerabbit'); ?></label>
				<input class="br-input" readonly type="text" value="<?= $current_user->user_login; ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label" for="the_email"><?= __('Email', 'bluerabbit'); ?></label>
				<input class="br-input" type="text" id="the_email" value="<?= $player_account->player_email ? $player_account->player_email : $current_user->user_email; ?>">
			</div>
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('Player Level', 'bluerabbit'); ?></label>
			<div class="br-form-component" style="display:flex;align-items:center;gap:14px">
				<span style="font-family:proxima-nova-extra-condensed,sans-serif;font-size:36px;font-weight:900;color:#1cc2eb"><?= $player_account->player_absolute_level; ?></span>
				<span class="br-form-hint" style="margin-top:0"><?= __("This level reflects the average between all registered adventures", "bluerabbit"); ?></span>
			</div>
		</div>

		<div class="br-form-group" style="max-width:280px">
			<label class="br-form-label" for="the_lang"><?= __('Language', 'bluerabbit'); ?></label>
			<?php $locale = $player_account->player_lang; ?>
			<?php $langs = array(
				array("en_US", "U.S. English"),
				array("es_MX", "Espa&ntilde;ol"),
			);
			?>
			<select class="br-input" id="the_lang">
				<?php foreach ($langs as $l) { ?>
					<option <?php if ($locale == $l[0]) { echo 'selected'; } ?> value="<?= $l[0]; ?>"><?= $l[1]; ?></option>
				<?php } ?>
			</select>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label" for="the_player_company"><?= __('Company Name', 'bluerabbit'); ?></label>
				<input class="br-input" type="text" value="<?= isset($player_account->player_company) ? $player_account->player_company : ""; ?>" id="the_player_company">
			</div>
			<div class="br-form-group">
				<label class="br-form-label" for="the_player_website"><?= __('Website URL', 'bluerabbit'); ?></label>
				<input class="br-input" type="text" value="<?= isset($player_account->player_website) ? $player_account->player_website : ""; ?>" id="the_player_website" placeholder="https://website.com">
			</div>
		</div>

		<div class="br-form-group">
			<label class="br-form-label" for="the_player_linkedin"><?= __('LinkedIn URL', 'bluerabbit'); ?></label>
			<input class="br-input" type="text" value="<?= isset($player_account->player_linkedin) ? $player_account->player_linkedin : ""; ?>" id="the_player_linkedin" placeholder="https://linkedin.com/in/username" style="max-width:420px">
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __('My Bio', 'bluerabbit'); ?></label>
			<?php
			if ($roles[0] == "administrator") {
				$wp_editor_settings = array('quicktags' => true, 'editor_height' => 350);
			} else {
				$wp_editor_settings = array('quicktags' => false, 'editor_height' => 350);
			}
			if (isset($player_account->player_bio)) {
				wp_editor($player_account->player_bio, 'the_player_bio', $wp_editor_settings);
			} else {
				wp_editor("", 'the_player_bio', $wp_editor_settings);
			}
			?>
		</div>
	</div>
	</div>

	<?php if ($config['use_hexad']['value'] > 0) { ?>
	<!-- Player Type -->
	<div class="br-scroll-section" id="hexad">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-hexad"></span> <?= __('My Player Type', 'bluerabbit'); ?></h3>
		<span class="br-panel-subtitle"><a href="http://gamified.uk" target="_blank" style="color:inherit;text-decoration:underline"><?php _e("Read all about Marczewski's player types", 'bluerabbit'); ?></a></span>

		<div style="text-align:center;margin-bottom:20px">
			<?php if ($player_account->hexad_date) { ?>
				<p class="br-form-hint" style="font-size:14px;color:rgba(255,255,255,0.6);margin:0 0 12px"><?= __('Last Tested', 'bluerabbit') . " " . (date('M jS, Y', strtotime($player_account->hexad_date))); ?></p>
				<a href="<?= get_bloginfo('url') . "/new-hexad"; ?>" class="br-btn br-btn-green"><?php _e("Test again", "bluerabbit"); ?> <span class="icon icon-hexad"></span></a>
			<?php } else { ?>
				<p class="br-form-hint" style="font-size:14px;color:rgba(255,255,255,0.6);margin:0 0 12px"><?= __('Never tested', 'bluerabbit'); ?></p>
				<a href="<?= get_bloginfo('url') . "/new-hexad"; ?>" class="br-btn br-btn-green"><?php _e("Test now", "bluerabbit"); ?> <span class="icon icon-hexad"></span></a>
			<?php } ?>
		</div>

		<?php if ($player_account->hexad_date) { ?>
			<?php
			$color = [
				'freespirit' => 'pink',
				'achiever' => 'blue',
				'philanthropist' => 'teal',
				'socialiser' => 'amber',
			];
			?>
			<div class="br-form-component" style="text-align:center;margin-bottom:16px">
				<div style="display:inline-flex;align-items:center;gap:14px">
					<span class="button-icon font _36" <?= br_color_attr($color[$player_account->player_hexad_slug]) ?> style="width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
						<span class="icon icon-<?= $player_account->player_hexad_slug; ?>"></span>
					</span>
					<div style="text-align:left">
						<div style="font-family:proxima-nova-extra-condensed,sans-serif;font-size:28px;font-weight:900"><?= $player_account->player_hexad; ?></div>
						<div class="br-form-hint" style="margin-top:0"><?php _e("Dominant Player Type", "bluerabbit"); ?></div>
					</div>
				</div>
				<div class="player-type-description" style="margin-top:16px;text-align:left;font-size:13px;color:rgba(255,255,255,0.7)">
					<?php include (TEMPLATEPATH . "/$player_account->player_hexad_slug.php"); ?>
				</div>
			</div>
			<div class="br-form-component" style="text-align:center">
				<?php
				$hexad = unserialize($player_account->hexad_answers);
				$intrinsic = array($hexad["type_f"], $hexad["type_s"], $hexad["type_ph"], $hexad["type_a"]);
				$ptMax = max($intrinsic);
				if ($ptMax == $hexad["type_f"]) {
					$ptMaxSlug = "freespirit";
				} elseif ($ptMax == $hexad["type_a"]) {
					$ptMaxSlug = "achiever";
				} elseif ($ptMax == $hexad["type_ph"]) {
					$ptMaxSlug = "philanthropist";
				} elseif ($ptMax == $hexad["type_s"]) {
					$ptMaxSlug = "socialiser";
				}
				?>
				<input type="hidden" id="pt-hexad-highest" value="<?= $ptMaxSlug; ?>">
				<div class="pt-chart">
					<canvas id="pt-graph-canvas" width="300" height="300"></canvas>
				</div>
				<script>
					createHexadChart(<?= $hexad["type_d"]; ?>,<?= $hexad["type_f"]; ?>,<?= $hexad["type_a"]; ?>,<?= $hexad["type_p"]; ?>,<?= $hexad["type_s"]; ?>,<?= $hexad["type_ph"]; ?>,"pt-graph-canvas");
				</script>
			</div>
			<div style="text-align:center;margin-top:16px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
				<a class="br-btn" href="https://www.gamified.uk/user-types/" target="_blank">
					<?php _e("Read all about Marczewski player types", "bluerabbit"); ?>
				</a>
				<a class="br-btn" href="https://www.amazon.com/Even-Ninja-Monkeys-Like-Play/dp/1514745666/ref=sr_1_1?ie=UTF8&qid=1532534766&sr=8-1&keywords=even+ninja+monkeys+like+to+play" target="_blank">
					<span class="icon icon-hexad"></span> <?php _e('Get a copy of “Even Ninja Monkeys like to Play”', "bluerabbit"); ?>
				</a>
			</div>
		<?php } else { ?>
			<div style="text-align:center;font-size:16px;color:rgba(255,255,255,0.5);padding:16px 0">
				<?= __("No data available", "bluerabbit"); ?>
			</div>
		<?php } ?>

		<div class="br-form-hint" style="margin-top:20px;font-size:11px;line-height:1.6">
			<div class="APA-ref">
				<div id="copy-target-99791202" class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied">Marczewski, A. (2015). <a href="http://gamified.uk/user-types/" rel="nofollow" target="_blank">User Types</a>. In <em><a href="http://www.gamified.uk/even-ninja-monkeys-like-to-play/">Even Ninja Monkeys Like to Play</a>: Gamification, Game Thinking and Motivational Design</em> (1st ed., pp. 65-80). CreateSpace Independent Publishing Platform.</div><div class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied"><ul><li><strong>ISBN-10:</strong> 1514745666</li><li><strong>ISBN-13:</strong> 978-1514745663</li></ul><p><a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license"><img style="border-width: 0;" src="//i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" alt="Creative Commons Licence"/></a> Gamification User Types Hexad by <a href="https://www.gamified.uk/user-types" rel="cc:attributionURL">Andrzej Marczewski</a> is licensed under a <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.</p></div>
			</div>
		</div>
	</div>
	</div>
	<?php } ?>

	<?php if ($config['show_upgrade']['value'] > 0) { ?>
	<!-- Account / Subscription -->
	<div class="br-scroll-section" id="my-account">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-settings"></span> <?= __("Subscription", "bluerabbit"); ?></h3>

		<div class="br-form-grid">
			<div class="br-form-component">
				<span class="br-form-label" style="display:block;margin-bottom:10px"><?php _e("Current Plan", "bluerabbit"); ?></span>
				<div style="font-family:proxima-nova-extra-condensed,sans-serif;font-size:28px;font-weight:900;color:#1cc2eb;margin-bottom:10px"><?php _e("Free", "bluerabbit"); ?></div>
				<ul style="margin:0;padding-left:18px;font-size:13px;color:rgba(255,255,255,0.6);line-height:1.8">
					<li><?php _e("Players allowed", "bluerabbit"); ?>: 200</li>
					<li><?php _e("Max Adventures", "bluerabbit"); ?>: 3</li>
					<li><?php _e("Disc Space", "bluerabbit"); ?>: 50Mb</li>
				</ul>
			</div>
			<div class="br-form-component">
				<span class="br-form-label" style="display:block;margin-bottom:10px"><?php _e("Upgrade to PRO", "bluerabbit"); ?></span>
				<div style="font-family:proxima-nova-extra-condensed,sans-serif;font-size:28px;font-weight:900;color:#f7cb15;margin-bottom:10px">$8.00 <small style="font-size:13px;opacity:0.5;font-weight:600">USD/mo</small></div>
				<ul style="margin:0;padding-left:18px;font-size:13px;color:rgba(255,255,255,0.6);line-height:1.8">
					<li><?php _e("Unlimited Players per adventure", "bluerabbit"); ?></li>
					<li><?php _e("Unlimited Adventures", "bluerabbit"); ?></li>
				</ul>
			</div>
		</div>

		<div style="text-align:center;margin-top:16px">
			<a href="https://bluerabbit.io/upgrade/" target="_blank" class="br-btn br-btn-amber" style="padding:10px 24px"><span class="icon icon-logo"></span> <?php _e("Upgrade now!", "bluerabbit"); ?></a>
		</div>
	</div>

	<?php if ($config['show_anonimize_button']['value'] > 0) { ?>
	<div class="br-panel" style="border-color:rgba(244,67,54,0.3)">
		<h3 class="br-panel-title" style="color:#f44336"><span class="icon icon-warning"></span> <?= __("Anonimizer", "bluerabbit"); ?></h3>
		<p class="br-form-hint" style="font-size:13px;color:rgba(255,255,255,0.6);margin:0 0 16px">
			<?= __("When you use the anonimizer, all your personal data converts to random names, pictures and email. Although you can still update your data back to normal, you will lose access to your account on logout. This is done with the sole purpose of keeping the demographic data of your account while protecting your privacy.", "bluerabbit"); ?>
		</p>
		<div style="text-align:center;position:relative">
			<button class="br-btn br-btn-red" style="padding:10px 20px" onClick="showOverlay('#confirm-anonimize-1');">
				<?= __("Anonimize Me", "bluerabbit"); ?>
			</button>
			<div class="confirm-action overlay-layer" id="confirm-anonimize-1">
				<button class="br-btn br-btn-amber" onClick="showOverlay('#confirm-anonimize-2');">
					<span class="icon icon-warning"></span> <?= __("Are you sure about this?", "bluerabbit"); ?>
				</button>
			</div>
			<div class="confirm-action overlay-layer" id="confirm-anonimize-2">
				<button class="br-btn br-btn-red" onClick="randomPlayerData();updateProfile();">
					<span class="icon icon-warning"></span> <?= __("Double confirm for security", "bluerabbit"); ?>
				</button>
			</div>
		</div>
	</div>
	<?php } ?>
	</div>
	<?php } ?>

</div><!-- /.br-page -->

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<span></span>
	<input type="hidden" id="profile_nonce" value='<?= wp_create_nonce('br_profile_post_nonce') ?>'>
	<button class="br-btn br-btn-green" style="padding:10px 24px;font-size:14px" onClick="updateProfile();">
		<span class="icon icon-check"></span> <?= __('Save', 'bluerabbit'); ?>
	</button>
</div>

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('#account-tabs-buttons .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	if (btn) btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('#account-tabs-buttons .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

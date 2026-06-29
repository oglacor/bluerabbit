<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$adventure_code = isset($_GET['enroll_code']) ? sanitize_text_field($_GET['enroll_code']) : null;
	$e = ['success' => false, 'message' => ''];

	if ($adventure_code) {
		$adventure = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}br_adventures WHERE adventure_code = %s",
			$adventure_code
		));

		if ($adventure) {
			$adventure_max = BR_Config::instance()->getSetting('max_players', $adventure->adventure_id);
			$enrolled_count = (int) $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id = %d AND player_adventure_status = 'in' AND player_id != %d",
				$adventure->adventure_id, $adventure->adventure_owner
			));

			if ($adventure->adventure_type == 'normal' || $current_user->roles[0] == 'administrator') {
				$e['adventure'] = $adventure;

				if ($adventure->adventure_owner == $current_user->ID) {
					$e['location'] = get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id";
					$e['message'] = '<h1 class="br-text-40 w300">' . __("Welcome back! You are the owner of this adventure", 'bluerabbit') . '</h1>';
					$e['success'] = true;

				} elseif ($adventure_max > 0 && $enrolled_count >= $adventure_max) {
					$e['message'] = '<span class="icon icon-cancel icon-xxl"></span>';
					$e['message'] .= '<h1 class="br-text-40 w900">' . __('Adventure Full', 'bluerabbit') . '</h1>';
					$e['message'] .= '<h4 class="br-text-24 w300">' . __("This adventure doesn't have any more space. The Game Master must remove some players before you can join.", 'bluerabbit') . '.</h4>';
					$e['location'] = get_bloginfo('url');

				} else {
					$already_enrolled = $wpdb->get_row($wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}br_player_adventure WHERE adventure_id = %d AND player_id = %d AND player_adventure_status = 'in'",
						$adventure->adventure_id, $current_user->ID
					));

					if ($already_enrolled) {
						$e['message'] = '<h4 class="br-text-16 w300">' . __("You already are enrolled in", 'bluerabbit') . '</h4>';
						$e['message'] .= '<h1 class="br-text-40 w900">' . $adventure->adventure_title . '</h1>';
					} else {
						$wpdb->query($wpdb->prepare(
							"INSERT INTO {$wpdb->prefix}br_player_adventure (adventure_id, player_id) VALUES (%d, %d) ON DUPLICATE KEY UPDATE player_adventure_status = 'in'",
							$adventure->adventure_id, $current_user->ID
						));
						$e['message'] = '<h1 class="br-text-40 w300">' . __("Welcome!", 'bluerabbit') . '</h1>';
						$e['message'] .= '<h4 class="br-text-16 w300">' . __("you are now enrolled in", 'bluerabbit') . '</h4>';
						$e['message'] .= '<h2 class="br-text-30 w900">' . $adventure->adventure_title . '</h2>';
						BR_Activity::instance()->logActivity($adventure->adventure_id, 'enroll', 'player', '', $current_user->ID);
					}
					$e['location'] = get_bloginfo('url') . "/adventure/?adventure_id=$adventure->adventure_id";
					$e['success'] = true;
				}
			} else {
				$e['message'] = '<h1>' . __('This adventure is a template', 'bluerabbit') . '</h1>';
				$e['message'] .= '<h4 class="br-text-24 w300">' . __("Players can't enroll in template adventures", 'bluerabbit') . '.</h4>';
			}
		} else {
			$e['message'] = '<span class="icon icon-cancel icon-xxl"></span>';
			$e['message'] .= '<h1 class="br-text-40 w900">' . __('Wrong Code', 'bluerabbit') . '</h1>';
			$e['message'] .= '<h4 class="br-text-24 w300">' . __("Adventure doesn't exist", 'bluerabbit') . '.</h4>';
		}
	} else {
		$e['message'] = '<h1>' . __('No code provided', 'bluerabbit') . '</h1>';
	}
?>

<?php if ($e['success']) { ?>

	<div class="layer background fixed sq-full top left blue-grey-bg-800 blend-overlay opacity-80" style="background-image: url('<?= $e['adventure']->adventure_badge; ?>');"></div>
	<div class="layer background fixed sq-full top left opacity-60" <?= br_color_attr($e['adventure']->adventure_color); ?>></div>
	<div class="layer base fixed perfect-center text-center white-color">
		<?= $e['message']; ?>
		<br>
		<a href="<?= $e['location']; ?>" class="form-ui blue-bg-700 br-text-30">
			<span class="icon icon-adventure"></span>
			<?php _e("Start the journey!", 'bluerabbit'); ?>
		</a>
		<audio id="audio-funky">
			<source src="<?= get_bloginfo('template_directory'); ?>/audio/funk1.mp3" type="audio/mpeg">
		</audio>
		<script>
			$(document).ready(function() {
				$("#audio-funky").get(0).play();
			});
		</script>
	</div>

<?php } else { ?>
	<div class="layer background fixed sq-full top left orange-bg-400 mix-blend-overlay"></div>
	<div class="layer background fixed sq-full top left orange-gradient-900 mix-blend-overlay"></div>
	<div class="layer background fixed sq-full top left grey-gradient-900 opacity-70"></div>
	<?php if (isset($e['adventure'])) { ?>
		<div class="layer background fixed sq-full top left opacity-60" <?= br_color_attr($e['adventure']->adventure_color); ?>></div>
	<?php } else { ?>
		<div class="layer background fixed sq-full top left opacity-60 red-bg-400"></div>
	<?php } ?>
	<div class="layer base fixed perfect-center text-center">
		<?= $e['message']; ?>
		<br>
		<a href="<?= get_bloginfo('url'); ?>" class="form-ui blue-bg-700 br-text-30">
			<span class="icon icon-home"></span>
			<?php _e("Back to home", 'bluerabbit'); ?>
		</a>
	</div>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

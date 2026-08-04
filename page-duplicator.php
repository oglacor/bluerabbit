<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if ( ! $adventure ) { ?>
<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php include (get_stylesheet_directory() . '/footer.php'); return; } ?>

<?php
$adv_id = (int) $adventure->adventure_id;

$adventures = $wpdb->get_results( $wpdb->prepare(
	"SELECT a.* FROM {$wpdb->prefix}br_adventures a
	 JOIN {$wpdb->prefix}br_player_adventure b ON a.adventure_id = b.adventure_id
	 WHERE a.adventure_status = 'publish'
	 AND (a.adventure_owner = %d OR (b.player_id = %d AND b.player_adventure_status = 'in' AND b.player_adventure_role = 'gm'))
	 GROUP BY a.adventure_id ORDER BY a.adventure_title",
	$current_user->ID, $current_user->ID
) );

$adventure_quests = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_quests
	 WHERE adventure_id = %d AND quest_status = 'publish'
	 ORDER BY quest_type, quest_relevance, quest_order, mech_level, mech_start_date",
	$adv_id
) );

$adventure_achievements = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_achievements
	 WHERE adventure_id = %d AND achievement_status = 'publish'
	 ORDER BY achievement_name, achievement_order",
	$adv_id
) );

$adventure_items = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_items
	 WHERE adventure_id = %d AND item_status = 'publish'
	 ORDER BY item_name, item_order",
	$adv_id
) );

$adventure_encounters = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_encounters
	 WHERE adventure_id = %d AND enc_status = 'publish'",
	$adv_id
) );

$adventure_speakers = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_speakers
	 WHERE adventure_id = %d AND speaker_status = 'publish'
	 ORDER BY speaker_first_name, speaker_last_name, speaker_id",
	$adv_id
) );

$adventure_tabis = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_tabis
	 WHERE adventure_id = %d AND tabi_status = 'publish'
	 ORDER BY tabi_name",
	$adv_id
) );
?>

<div class="br-page br-has-bottom-bar">

	<!-- Page Header -->
	<div class="br-panel br-page-header">
		<div class="br-icon-btn br-icon-btn-purple"><span class="icon icon-duplicate"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
			<h1 class="br-page-title"><?= __("Duplicator","bluerabbit"); ?></h1>
		</div>
		<div class="br-dup-badge">
			<span id="dup-count">0</span> <?= __("selected","bluerabbit"); ?>
		</div>
	</div>

	<!-- Milestones -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-quest"></span> <?= __("Milestones","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#quests-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#quests-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_quests) { ?>
		<ul class="selectable-list select-multiple" id="quests-to-duplicate">
			<?php $block = ''; ?>
			<?php foreach ($adventure_quests as $q) { ?>
				<?php if ($block !== $q->quest_type) { ?>
					<?php $block = $q->quest_type; ?>
					<li class="dup-type-header"><?= esc_html($block); ?></li>
				<?php } ?>
				<li id="req-<?= $q->quest_id; ?>" class="to-duplicate" onClick="toggleReq('#req-<?= $q->quest_id; ?>'); updateDupCount();">
					<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
						<span class="icon icon-<?= $q->quest_icon ? esc_attr($q->quest_icon) : 'document'; ?>"></span>
					</span>
					<span class="li-cell cell-content inactive-content padding-10 text-left"><?= esc_html($q->quest_title); ?></span>

					<span class="li-cell active-content green-bg-400 white-color br-text-18">
						<span class="icon icon-check"></span>
					</span>
					<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= esc_html($q->quest_title); ?></span>

					<span class="li-cell amber-bg-400 white-color font w900">
						<span class="icon icon-star"></span>
						<?= $q->mech_xp ? BR_Utils::instance()->toMoney($q->mech_xp, '') : 0; ?>
					</span>
					<span class="li-cell light-green-bg-400 white-color font w900">
						<span class="icon icon-bloo"></span>
						<?= $q->mech_bloo ? BR_Utils::instance()->toMoney($q->mech_bloo, '') : 0; ?>
					</span>
					<span class="li-cell deep-purple-bg-400 white-color font w900"><?= (int) $q->mech_level; ?></span>
					<input type="hidden" class="reqs-id" value="<?= $q->quest_id; ?>">
				</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-quest"></span>
			<h3><?= __("No milestones in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

	<!-- Achievements -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-achievement"></span> <?= __("Achievements","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#achievements-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#achievements-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_achievements) { ?>
		<ul class="selectable-list select-multiple" id="achievements-to-duplicate">
			<?php foreach ($adventure_achievements as $a) { ?>
			<li id="req-achievement-<?= $a->achievement_id; ?>" class="to-duplicate" onClick="toggleReq('#req-achievement-<?= $a->achievement_id; ?>'); updateDupCount();">
				<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
					<span class="icon icon-achievement"></span>
				</span>
				<span class="li-cell cell-content inactive-content padding-10 text-left"><?= esc_html($a->achievement_name); ?></span>

				<span class="li-cell active-content green-bg-400 white-color br-text-18">
					<span class="icon icon-check"></span>
				</span>
				<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= esc_html($a->achievement_name); ?></span>

				<span class="li-cell amber-bg-400 white-color font w900">
					<span class="icon icon-star"></span>
					<?= $a->achievement_xp ? BR_Utils::instance()->toMoney($a->achievement_xp, '') : 0; ?>
				</span>
				<span class="li-cell light-green-bg-400 white-color font w900">
					<span class="icon icon-bloo"></span>
					<?= $a->achievement_bloo ? BR_Utils::instance()->toMoney($a->achievement_bloo, '') : 0; ?>
				</span>
				<input type="hidden" class="reqs-id" value="<?= $a->achievement_id; ?>">
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-achievement"></span>
			<h3><?= __("No achievements in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

	<!-- Tabis -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-carrot"></span> <?= __("Tabis","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#tabis-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#tabis-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_tabis) { ?>
		<ul class="selectable-list select-multiple" id="tabis-to-duplicate">
			<?php foreach ($adventure_tabis as $a) { ?>
			<li id="req-tabi-<?= $a->tabi_id; ?>" class="to-duplicate" onClick="toggleReq('#req-tabi-<?= $a->tabi_id; ?>'); updateDupCount();">
				<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
					<span class="icon icon-carrot"></span>
				</span>
				<span class="li-cell cell-content inactive-content padding-10 text-left"><?= esc_html($a->tabi_name); ?></span>

				<span class="li-cell active-content green-bg-400 white-color br-text-18">
					<span class="icon icon-check"></span>
				</span>
				<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= esc_html($a->tabi_name); ?></span>

				<input type="hidden" class="reqs-id" value="<?= $a->tabi_id; ?>">
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-carrot"></span>
			<h3><?= __("No tabis in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

	<!-- Items -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-basket"></span> <?= __("Items","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#items-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#items-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_items) { ?>
		<ul class="selectable-list select-multiple" id="items-to-duplicate">
			<?php foreach ($adventure_items as $i) { ?>
			<?php
			if ($i->item_type == 'consumable') {
				$icon_type = 'basket';
			} elseif ($i->item_type == 'key') {
				$icon_type = 'key';
			} else {
				$icon_type = 'winstate';
			}
			?>
			<li id="req-item-<?= $i->item_id; ?>" class="to-duplicate" onClick="toggleReq('#req-item-<?= $i->item_id; ?>'); updateDupCount();">
				<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
					<span class="icon icon-<?= esc_attr($icon_type); ?>"></span>
				</span>
				<span class="li-cell cell-content inactive-content padding-10 text-left"><?= esc_html($i->item_name); ?></span>

				<span class="li-cell active-content green-bg-400 white-color br-text-18">
					<span class="icon icon-check"></span>
				</span>
				<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= esc_html($i->item_name); ?></span>

				<span class="li-cell light-green-bg-400 white-color font w900">
					<span class="icon icon-bloo"></span>
					<?= BR_Utils::instance()->toMoney($i->item_cost); ?>
				</span>
				<input type="hidden" class="reqs-id" value="<?= $i->item_id; ?>">
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-basket"></span>
			<h3><?= __("No items in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

	<!-- Encounters -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-activity"></span> <?= __("Encounters","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#encounters-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#encounters-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_encounters) { ?>
		<ul class="selectable-list select-multiple" id="encounters-to-duplicate">
			<?php foreach ($adventure_encounters as $e) { ?>
			<li id="req-enc-<?= $e->enc_id; ?>" class="to-duplicate" onClick="toggleReq('#req-enc-<?= $e->enc_id; ?>'); updateDupCount();">
				<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
					<span class="icon icon-socialiser"></span>
				</span>
				<span class="li-cell cell-content inactive-content padding-10 text-left"><?= esc_html($e->enc_question); ?></span>

				<span class="li-cell active-content green-bg-400 white-color br-text-18">
					<span class="icon icon-check"></span>
				</span>
				<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= esc_html($e->enc_question); ?></span>

				<span class="li-cell cyan-bg-400 white-color font w900">
					<span class="icon icon-activity"></span>
					<?= (int) $e->enc_ep; ?>
				</span>
				<span class="li-cell amber-bg-400 white-color font w900">
					<span class="icon icon-star"></span>
					<?= (int) $e->enc_xp; ?>
				</span>
				<span class="li-cell light-green-bg-400 white-color font w900">
					<span class="icon icon-bloo"></span>
					<?= BR_Utils::instance()->toMoney($e->enc_bloo); ?>
				</span>
				<input type="hidden" class="reqs-id" value="<?= $e->enc_id; ?>">
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-activity"></span>
			<h3><?= __("No encounters in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

	<!-- Speakers -->
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-socialiser"></span> <?= __("Speakers","bluerabbit"); ?>
			<span class="br-ml-auto br-flex br-gap-sm">
				<button class="br-form-btn-green" onClick="activateAll('#speakers-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#speakers-to-duplicate li.to-duplicate'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_speakers) { ?>
		<ul class="selectable-list select-multiple" id="speakers-to-duplicate">
			<?php foreach ($adventure_speakers as $e) { ?>
			<li id="req-speaker-<?= $e->speaker_id; ?>" class="to-duplicate" onClick="toggleReq('#req-speaker-<?= $e->speaker_id; ?>'); updateDupCount();">
				<span class="li-cell inactive-content grey-bg-300 text-center br-text-18">
					<span class="icon icon-socialiser"></span>
				</span>
				<span class="li-cell cell-content inactive-content padding-10 text-left">
					<?= esc_html("$e->speaker_first_name $e->speaker_last_name"); ?>
				</span>

				<span class="li-cell active-content green-bg-400 white-color br-text-18">
					<span class="icon icon-check"></span>
				</span>
				<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700">
					<?= esc_html("$e->speaker_first_name $e->speaker_last_name"); ?>
				</span>

				<input type="hidden" class="reqs-id" value="<?= $e->speaker_id; ?>">
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="br-empty">
			<span class="icon icon-socialiser"></span>
			<h3><?= __("No speakers in this adventure","bluerabbit"); ?></h3>
		</div>
		<?php } ?>
	</div>

</div>

<!-- Fixed bottom action bar -->
<div class="br-form-bottom-bar">
	<div class="br-form-group br-mb-0">
		<label class="br-form-label"><?= __("Target Adventure","bluerabbit"); ?></label>
		<select class="br-input" id="adventure_target">
			<?php foreach ($adventures as $c) { ?>
			<option value="<?= (int) $c->adventure_id; ?>" <?= $c->adventure_id == $adv_id ? 'selected' : ''; ?>>
				<?= esc_html($c->adventure_title); ?>
			</option>
			<?php } ?>
		</select>
	</div>
	<button class="br-btn br-btn-submit" onClick="showOverlay('#duplicate-confirm');">
		<span class="icon icon-duplicate"></span> <?= __("Duplicate Selected","bluerabbit"); ?>
	</button>
</div>

<!-- Confirm overlay -->
<div class="overlay-layer confirm-action" id="duplicate-confirm">
	<button class="br-form-btn-green" onClick="hideAllOverlay(); duplicateQuests();">
		<span class="icon icon-check"></span> <?= __("Yes, Duplicate","bluerabbit"); ?>
	</button>
	<button class="br-btn" onClick="hideAllOverlay();">
		<span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?>
	</button>
</div>

<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>">

<script>
function updateDupCount() {
	$('#dup-count').text($('.to-duplicate.active').length);
}
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

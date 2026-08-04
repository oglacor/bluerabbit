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

// Build adventures lookup for JS
$adv_titles = [];
foreach ($adventures as $c) {
	$adv_titles[(int)$c->adventure_id] = esc_js($c->adventure_title);
}
?>

<div class="br-page br-dup-page">

	<!-- Sticky page header -->
	<div class="br-panel br-page-header br-dup-sticky-header">
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
				<button class="br-form-btn-green" onClick="activateAll('#quests-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#quests-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_quests) { ?>
		<ul class="dup-list" id="quests-to-duplicate">
			<?php $block = ''; ?>
			<?php foreach ($adventure_quests as $q) { ?>
				<?php if ($block !== $q->quest_type) { ?>
					<?php $block = $q->quest_type; ?>
					<li class="dup-type-header"><?= esc_html($block); ?></li>
				<?php } ?>
				<li id="req-<?= $q->quest_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-<?= $q->quest_id; ?>'); updateDupCount();">
					<span class="dup-check"></span>
					<span class="dup-icon"><span class="icon icon-<?= $q->quest_icon ? esc_attr($q->quest_icon) : 'document'; ?>"></span></span>
					<span class="dup-name"><?= esc_html($q->quest_title); ?></span>
					<?php if ($q->mech_xp): ?><span class="dup-stat dup-stat-xp"><span class="icon icon-star"></span><?= BR_Utils::instance()->toMoney($q->mech_xp, ''); ?></span><?php endif; ?>
					<?php if ($q->mech_bloo): ?><span class="dup-stat dup-stat-bloo"><span class="icon icon-bloo"></span><?= BR_Utils::instance()->toMoney($q->mech_bloo, ''); ?></span><?php endif; ?>
					<span class="dup-stat dup-stat-level"><span class="icon icon-progression"></span><?= (int) $q->mech_level; ?></span>
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
				<button class="br-form-btn-green" onClick="activateAll('#achievements-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#achievements-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_achievements) { ?>
		<ul class="dup-list" id="achievements-to-duplicate">
			<?php foreach ($adventure_achievements as $a) { ?>
			<li id="req-achievement-<?= $a->achievement_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-achievement-<?= $a->achievement_id; ?>'); updateDupCount();">
				<span class="dup-check"></span>
				<span class="dup-icon"><span class="icon icon-achievement"></span></span>
				<span class="dup-name"><?= esc_html($a->achievement_name); ?></span>
				<?php if ($a->achievement_xp): ?><span class="dup-stat dup-stat-xp"><span class="icon icon-star"></span><?= BR_Utils::instance()->toMoney($a->achievement_xp, ''); ?></span><?php endif; ?>
				<?php if ($a->achievement_bloo): ?><span class="dup-stat dup-stat-bloo"><span class="icon icon-bloo"></span><?= BR_Utils::instance()->toMoney($a->achievement_bloo, ''); ?></span><?php endif; ?>
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
				<button class="br-form-btn-green" onClick="activateAll('#tabis-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#tabis-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_tabis) { ?>
		<ul class="dup-list" id="tabis-to-duplicate">
			<?php foreach ($adventure_tabis as $a) { ?>
			<li id="req-tabi-<?= $a->tabi_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-tabi-<?= $a->tabi_id; ?>'); updateDupCount();">
				<span class="dup-check"></span>
				<span class="dup-icon"><span class="icon icon-carrot"></span></span>
				<span class="dup-name"><?= esc_html($a->tabi_name); ?></span>
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
				<button class="br-form-btn-green" onClick="activateAll('#items-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#items-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_items) { ?>
		<ul class="dup-list" id="items-to-duplicate">
			<?php foreach ($adventure_items as $i) { ?>
			<?php
			if ($i->item_type == 'consumable')    { $icon_type = 'basket'; }
			elseif ($i->item_type == 'key')        { $icon_type = 'key'; }
			else                                   { $icon_type = 'winstate'; }
			?>
			<li id="req-item-<?= $i->item_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-item-<?= $i->item_id; ?>'); updateDupCount();">
				<span class="dup-check"></span>
				<span class="dup-icon"><span class="icon icon-<?= esc_attr($icon_type); ?>"></span></span>
				<span class="dup-name"><?= esc_html($i->item_name); ?></span>
				<?php if ($i->item_cost): ?><span class="dup-stat dup-stat-bloo"><span class="icon icon-bloo"></span><?= BR_Utils::instance()->toMoney($i->item_cost); ?></span><?php endif; ?>
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
				<button class="br-form-btn-green" onClick="activateAll('#encounters-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#encounters-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_encounters) { ?>
		<ul class="dup-list" id="encounters-to-duplicate">
			<?php foreach ($adventure_encounters as $e) { ?>
			<li id="req-enc-<?= $e->enc_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-enc-<?= $e->enc_id; ?>'); updateDupCount();">
				<span class="dup-check"></span>
				<span class="dup-icon"><span class="icon icon-socialiser"></span></span>
				<span class="dup-name"><?= esc_html($e->enc_question); ?></span>
				<?php if ($e->enc_ep): ?><span class="dup-stat dup-stat-ep"><span class="icon icon-activity"></span><?= (int) $e->enc_ep; ?></span><?php endif; ?>
				<?php if ($e->enc_xp): ?><span class="dup-stat dup-stat-xp"><span class="icon icon-star"></span><?= (int) $e->enc_xp; ?></span><?php endif; ?>
				<?php if ($e->enc_bloo): ?><span class="dup-stat dup-stat-bloo"><span class="icon icon-bloo"></span><?= BR_Utils::instance()->toMoney($e->enc_bloo); ?></span><?php endif; ?>
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
				<button class="br-form-btn-green" onClick="activateAll('#speakers-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-check"></span> <?= __("All","bluerabbit"); ?>
				</button>
				<button class="br-btn" onClick="deactivateAll('#speakers-to-duplicate li.dup-row'); updateDupCount();">
					<span class="icon icon-cancel"></span> <?= __("Clear","bluerabbit"); ?>
				</button>
			</span>
		</h3>
		<?php if ($adventure_speakers) { ?>
		<ul class="dup-list" id="speakers-to-duplicate">
			<?php foreach ($adventure_speakers as $e) { ?>
			<li id="req-speaker-<?= $e->speaker_id; ?>" class="dup-row to-duplicate" onClick="toggleReq('#req-speaker-<?= $e->speaker_id; ?>'); updateDupCount();">
				<span class="dup-check"></span>
				<span class="dup-icon"><span class="icon icon-socialiser"></span></span>
				<span class="dup-name"><?= esc_html("$e->speaker_first_name $e->speaker_last_name"); ?></span>
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

<!-- Bottom action bar -->
<div class="br-dup-bottom-bar">
	<div class="br-form-group br-mb-0 br-w-full">
		<label class="br-form-label"><?= __("Target Adventure","bluerabbit"); ?></label>
		<select class="br-input" id="adventure_target">
			<?php foreach ($adventures as $c) { ?>
			<option value="<?= (int) $c->adventure_id; ?>" <?= $c->adventure_id == $adv_id ? 'selected' : ''; ?>>
				<?= esc_html($c->adventure_title); ?>
			</option>
			<?php } ?>
		</select>
	</div>
	<button class="br-btn br-btn-submit" onClick="showDupConfirm();">
		<span class="icon icon-duplicate"></span> <?= __("Duplicate Selected","bluerabbit"); ?>
	</button>
</div>

<!-- Confirm modal -->
<div class="br-modal-backdrop" id="dup-confirm-backdrop" onClick="hideDupConfirm();" style="display:none">
	<div class="br-modal-box" onClick="event.stopPropagation();">
		<div class="br-modal-header">
			<span class="icon icon-duplicate"></span>
			<h2><?= __("Confirm Duplication","bluerabbit"); ?></h2>
		</div>
		<div class="br-modal-body">
			<p class="br-modal-intro"><?= __("You are about to duplicate the following into:","bluerabbit"); ?></p>
			<div class="br-modal-target-name" id="dup-modal-target"></div>
			<ul class="br-modal-summary" id="dup-modal-summary"></ul>
		</div>
		<div class="br-modal-footer">
			<button class="br-form-btn-green" onClick="hideDupConfirm(); duplicateQuests();">
				<span class="icon icon-check"></span> <?= __("Yes, Duplicate","bluerabbit"); ?>
			</button>
			<button class="br-btn" onClick="hideDupConfirm();">
				<span class="icon icon-cancel"></span> <?= __("Cancel","bluerabbit"); ?>
			</button>
		</div>
	</div>
</div>

<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>">

<script>
var dupAdvTitles = <?= json_encode($adv_titles); ?>;

function updateDupCount() {
	$('#dup-count').text($('.to-duplicate.active').length);
}

function showDupConfirm() {
	var counts = {
		milestones:   $('#quests-to-duplicate li.dup-row.active').length,
		achievements: $('#achievements-to-duplicate li.dup-row.active').length,
		tabis:        $('#tabis-to-duplicate li.dup-row.active').length,
		items:        $('#items-to-duplicate li.dup-row.active').length,
		encounters:   $('#encounters-to-duplicate li.dup-row.active').length,
		speakers:     $('#speakers-to-duplicate li.dup-row.active').length
	};
	var total = counts.milestones + counts.achievements + counts.tabis + counts.items + counts.encounters + counts.speakers;
	if (total === 0) {
		alert('<?= __("Please select at least one item to duplicate.","bluerabbit"); ?>');
		return;
	}
	var targetId  = parseInt($('#adventure_target').val());
	var targetName = dupAdvTitles[targetId] || $('#adventure_target option:selected').text();
	$('#dup-modal-target').text(targetName);
	var labels = {
		milestones:   '<?= __("Milestones","bluerabbit"); ?>',
		achievements: '<?= __("Achievements","bluerabbit"); ?>',
		tabis:        '<?= __("Tabis","bluerabbit"); ?>',
		items:        '<?= __("Items","bluerabbit"); ?>',
		encounters:   '<?= __("Encounters","bluerabbit"); ?>',
		speakers:     '<?= __("Speakers","bluerabbit"); ?>'
	};
	var html = '';
	$.each(counts, function(key, n) {
		if (n > 0) html += '<li><strong>' + n + '</strong> ' + labels[key] + '</li>';
	});
	$('#dup-modal-summary').html(html);
	$('#dup-confirm-backdrop').fadeIn(180);
}

function hideDupConfirm() {
	$('#dup-confirm-backdrop').fadeOut(150);
}
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

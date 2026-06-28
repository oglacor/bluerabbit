<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if (isset($adventure) && $isGM) {
	$guild_id = isset($_GET['guild_id']) ? (int) $_GET['guild_id'] : null;
	if ($guild_id) {
		$g = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}br_guilds WHERE guild_id = %d", $guild_id
		));
	}
	$is_edit = (isset($g) && $g);

	$guild_members = [];
	$guild_member_ids = [];
	$all_players = [];
	if ($is_edit) {
		$guild_members = $wpdb->get_results($wpdb->prepare(
			"SELECT pg.player_id, b.player_display_name, b.player_email, b.player_picture, b.player_first, b.player_last
			FROM {$wpdb->prefix}br_player_guild pg
			LEFT JOIN {$wpdb->prefix}br_players b ON pg.player_id = b.player_id
			WHERE pg.guild_id = %d AND pg.adventure_id = %d
			ORDER BY b.player_display_name ASC",
			$g->guild_id, $adventure->adventure_id
		));
		foreach ($guild_members as $gm) $guild_member_ids[] = $gm->player_id;

		$all_players = $wpdb->get_results($wpdb->prepare(
			"SELECT a.player_id, b.player_display_name, b.player_email, b.player_picture, b.player_first, b.player_last
			FROM {$wpdb->prefix}br_player_adventure a
			LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
			WHERE a.adventure_id = %d AND a.player_adventure_status = 'in'
			ORDER BY b.player_display_name ASC",
			$adventure->adventure_id
		));
	}
?>

<div class="br-page br-page-mid">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-green">
			<span class="icon icon-guild br-icon-lg br-icon-green"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Guild", "bluerabbit") : __("New Guild", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<input type="hidden" id="the_guild_id" value="<?= $is_edit ? $g->guild_id : ''; ?>">
	</div>

	<?php if ($is_edit) { ?>
	<!-- Sticky Nav -->
	<div class="br-tabs br-tabs-sticky">
		<button class="br-tab-btn active" onClick="brScrollTo('guild-settings', this)">
			<span class="icon icon-tools"></span> <?= __("Settings", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('guild-members-section', this)">
			<span class="icon icon-guild"></span> <?= __("Members", "bluerabbit"); ?>
			<span class="br-badge br-badge-green br-badge-ml-sm"><?= count($guild_members); ?></span>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('guild-assign-section', this)">
			<span class="icon icon-players"></span> <?= __("Assign Players", "bluerabbit"); ?>
		</button>
	</div>
	<?php } ?>

	<!-- ═══ GUILD SETTINGS ═══ -->
	<div class="br-scroll-section" id="guild-settings">
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-tools"></span> <?= __("Guild Settings", "bluerabbit"); ?></h3>

		<!-- Name -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Guild Name", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_guild_name" maxlength="255"
				   value="<?= $is_edit ? esc_attr($g->guild_name) : ''; ?>"
				   placeholder="<?= __('Enter guild name', 'bluerabbit'); ?>">
		</div>

		<!-- Logo -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Guild Logo", "bluerabbit"); ?> <span class="br-required">*<?= __("Required", "bluerabbit"); ?></span></label>
			<div class="br-form-component">
				<div class="gallery">
					<div class="gallery-item setting">
						<div class="background" style="background-image: url(<?= $is_edit ? $g->guild_logo : ''; ?>);" onClick="showWPUpload('the_guild_logo');" id="the_guild_logo_thumb"></div>
						<div class="gallery-item-options relative">
							<button class="button-icon font _24 sq-40 green-bg-400" onClick="showWPUpload('the_guild_logo');"><span class="icon icon-image"></span></button>
							<button class="button-icon font _24 sq-40 red-bg-400" onClick="clearImage('#the_guild_logo');"><span class="icon icon-trash"></span></button>
							<input type="hidden" id="the_guild_logo" value="<?= $is_edit ? $g->guild_logo : ''; ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Color -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<?php $selected_color = $is_edit ? $g->guild_color : ''; ?>
				<input id="the_guild_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
				<?php $color_select_id = '#the_guild_color'; include(TEMPLATEPATH . '/color-select.php'); ?>
			</div>
		</div>

		<!-- Auto-assign + Group + Capacity -->
		<div class="br-form-grid br-form-grid-3">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Auto-assign on login", "bluerabbit"); ?></label>
				<select id="the_guild_assign_on_login" class="br-input">
					<option value="0" <?= (!$is_edit || !$g->assign_on_login) ? 'selected' : ''; ?>><?= __("No", "bluerabbit"); ?></option>
					<option value="1" <?= ($is_edit && $g->assign_on_login) ? 'selected' : ''; ?>><?= __("Yes", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Group", "bluerabbit"); ?></label>
				<input class="br-input" type="text" id="the_guild_group" maxlength="50"
					   value="<?= $is_edit ? esc_attr($g->guild_group) : ''; ?>"
					   placeholder="<?= __('Optional', 'bluerabbit'); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Capacity", "bluerabbit"); ?></label>
				<input class="br-input" type="number" id="the_guild_capacity"
					   value="<?= $is_edit ? $g->guild_capacity : ''; ?>"
					   placeholder="<?= __('0 = no limit', 'bluerabbit'); ?>">
			</div>
		</div>

		<?php if ($is_edit) { ?>
		<!-- Enrollment Link -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Enrollment Link", "bluerabbit"); ?></label>
			<input type="text" readonly class="br-input br-input-readonly"
				   value="<?= get_bloginfo('url') . "/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?>"
				   onClick="this.select();">
		</div>

		<!-- Bulk Upload -->
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Bulk Assign Players (CSV)", "bluerabbit"); ?></label>
			<div class="br-input-row">
				<input type="file" name="the_csv_file_with_players" id="the_csv_file_with_players" class="br-input br-input-file">
				<button type="button" class="br-btn br-btn-green" onClick="assignBulkUsersToGuild();">
					<span class="icon icon-check"></span> <?= __("Upload", "bluerabbit"); ?>
				</button>
			</div>
		</div>
		<?php } ?>

		<!-- Footer -->
		<div class="br-form-footer">
			<a class="br-btn br-btn-red" href="<?= get_bloginfo('url') . '/adventure/?adventure_id=' . $adventure_id; ?>">
				<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
			</a>
			<div class="br-actions">
				<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_guild_nonce'); ?>">
				<select id="the_guild_status" class="br-input br-select-auto">
					<option value="publish" <?= (!$is_edit || $g->guild_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
					<option value="draft" <?= ($is_edit && $g->guild_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft", "bluerabbit"); ?></option>
					<option value="trash" <?= ($is_edit && $g->guild_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
				</select>
				<button id="submit-button" type="button" class="br-btn br-btn-green br-btn-submit" onClick="updateGuild();">
					<span class="icon icon-check"></span>
					<?= $is_edit ? __("Update Guild", "bluerabbit") : __("Create Guild", "bluerabbit"); ?>
				</button>
			</div>
		</div>
	</div>
	</div>

	<?php if ($is_edit) { ?>

	<!-- ═══ GUILD MEMBERS ═══ -->
	<div class="br-scroll-section" id="guild-members-section">
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-guild"></span> <?= __("Guild Members", "bluerabbit"); ?>
			<span class="br-badge br-badge-green br-badge-ml"><?= count($guild_members); ?></span>
		</h3>

		<?php if (!empty($guild_members)) { ?>
		<table class="br-table" id="guild-members-table">
			<thead>
				<tr>
					<th class="br-th-narrow"></th>
					<th><?= __("Name", "bluerabbit"); ?></th>
					<th><?= __("Email", "bluerabbit"); ?></th>
					<th class="br-th-actions"><?= __("Actions", "bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($guild_members as $gm) { ?>
				<tr id="member-row-<?= $gm->player_id; ?>">
					<td>
						<div class="br-member-avatar" style="background-image:url(<?= esc_url($gm->player_picture); ?>)"></div>
					</td>
					<td><?= esc_html($gm->player_display_name ?: ($gm->player_first . ' ' . $gm->player_last)); ?></td>
					<td class="br-text-muted-sm"><?= esc_html($gm->player_email); ?></td>
					<td>
						<button class="br-btn br-btn-red br-btn-compact" onClick="triggerGuild(<?= "$g->guild_id, $gm->player_id"; ?>); document.getElementById('member-row-<?= $gm->player_id; ?>').style.opacity='0.3';">
							<span class="icon icon-cancel"></span> <?= __("Remove", "bluerabbit"); ?>
						</button>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php } else { ?>
		<div class="br-empty br-empty-md">
			<span class="icon icon-guild"></span>
			<h3><?= __("No members yet", "bluerabbit"); ?></h3>
			<p><?= __("Assign players from the list below.", "bluerabbit"); ?></p>
		</div>
		<?php } ?>
	</div>
	</div>

	<!-- ═══ ASSIGN PLAYERS ═══ -->
	<div class="br-scroll-section" id="guild-assign-section">
	<div class="br-panel">
		<h3 class="br-panel-title">
			<span class="icon icon-players"></span> <?= __("Assign Players", "bluerabbit"); ?>
			<span class="br-helper-text">
				<?= count($all_players); ?> <?= __("enrolled", "bluerabbit"); ?>
			</span>
		</h3>

		<!-- Search -->
		<div class="br-form-group br-mb-14">
			<div class="br-search-inline">
				<span class="icon icon-search"></span>
				<input type="text" class="br-input" id="guild-player-search"
					   placeholder="<?= __('Search by name or email...', 'bluerabbit'); ?>">
			</div>
		</div>

		<table class="br-table" id="guild-all-players">
			<thead>
				<tr>
					<th class="br-th-narrow"></th>
					<th><?= __("Name", "bluerabbit"); ?></th>
					<th><?= __("Email", "bluerabbit"); ?></th>
					<th class="text-center br-th-narrow"><?= __("Status", "bluerabbit"); ?></th>
					<th class="br-th-actions"><?= __("Actions", "bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($all_players as $idx => $play) {
					$in_guild = in_array($play->player_id, $guild_member_ids);
				?>
				<tr class="guild-player-row" data-in-guild="<?= $in_guild ? '1' : '0'; ?>" id="player-guild-list-<?= $play->player_id; ?>" <?= $in_guild ? 'class="guild-player-row active"' : ''; ?>>
					<td>
						<div class="br-member-avatar" style="background-image:url(<?= esc_url($play->player_picture); ?>)"></div>
					</td>
					<td><?= esc_html($play->player_display_name ?: ($play->player_first . ' ' . $play->player_last)); ?></td>
					<td class="br-text-muted-sm"><?= esc_html($play->player_email); ?></td>
					<td class="text-center">
						<span class="active-content br-badge br-badge-green br-badge-sm"><?= __("In Guild", "bluerabbit"); ?></span>
					</td>
					<td>
						<button class="active-content br-btn br-btn-red br-btn-compact" onClick="triggerGuild(<?= "$g->guild_id, $play->player_id"; ?>);">
							<span class="icon icon-cancel"></span>
						</button>
						<button class="inactive-content br-btn br-btn-compact" onClick="triggerGuild(<?= "$g->guild_id, $play->player_id"; ?>);">
							<span class="icon icon-check"></span> <?= __("Assign", "bluerabbit"); ?>
						</button>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<!-- Pagination -->
		<div class="br-stats-pagination br-mt-sm" id="guild-pagination"></div>
	</div>
	</div>

	<script>
	(function($){
		var perPage = 50;
		var currentPage = 1;
		var $rows = $('#guild-all-players tbody .guild-player-row');
		var $search = $('#guild-player-search');
		var $pagination = $('#guild-pagination');

		function getVisibleRows() {
			return $rows.filter(function() { return $(this).css('display') !== 'none' || !$(this).data('filtered'); });
		}

		function applyFilter() {
			var term = $search.val().toLowerCase();
			$rows.each(function() {
				var text = $(this).text().toLowerCase();
				var match = !term || text.indexOf(term) >= 0;
				$(this).data('filtered', !match);
				if (!match) $(this).hide();
			});
			currentPage = 1;
			paginate();
		}

		function paginate() {
			var visible = [];
			$rows.each(function() {
				if (!$(this).data('filtered')) visible.push($(this));
				$(this).hide();
			});
			var totalPages = Math.ceil(visible.length / perPage);
			var start = (currentPage - 1) * perPage;
			var end = start + perPage;
			for (var i = start; i < end && i < visible.length; i++) {
				visible[i].show();
			}

			var html = '';
			if (totalPages > 1) {
				for (var p = 1; p <= totalPages; p++) {
					html += '<a href="#" class="br-stats-page-link' + (p === currentPage ? ' active' : '') + '" data-page="' + p + '">' + p + '</a>';
				}
			}
			$pagination.html(html);
		}

		$search.on('keyup', function() { applyFilter(); });

		$pagination.on('click', '.br-stats-page-link', function(e) {
			e.preventDefault();
			currentPage = parseInt($(this).data('page'));
			paginate();
		});

		paginate();

		// Scroll nav
		var sections = document.querySelectorAll('.br-scroll-section');
		var buttons  = document.querySelectorAll('.br-tabs-sticky .br-tab-btn');
		if (sections.length && buttons.length) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (!entry.isIntersecting) return;
					buttons.forEach(function(b, i) {
						b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id);
					});
				});
			}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
			sections.forEach(function(s) { observer.observe(s); });
		}
	})(jQuery);

	function brScrollTo(id, btn) {
		document.querySelectorAll('.br-tabs-sticky .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
		btn.classList.add('active');
		document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
	</script>

	<?php } ?>

</div>

<?php } else { ?>
	<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

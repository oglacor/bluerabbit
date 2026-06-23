<?php
$str = (isset($exclude)) ? "AND a.player_id NOT IN ($exclude)" : "";
$players = $wpdb->get_results("
	SELECT a.*,b.player_display_name, b.player_email, b.player_picture, b.player_hexad, b.player_hexad_slug, users.display_name, users.user_email FROM {$wpdb->prefix}br_player_adventure a
	LEFT JOIN {$wpdb->prefix}br_players b
	on a.player_id = b.player_id
	LEFT JOIN {$wpdb->prefix}users users
	on a.player_id = users.ID
	WHERE a.adventure_id=$adv_parent_id AND a.player_adventure_status='in' $str ORDER BY b.player_email LIMIT 1000
"); ?>

<!-- Bulk Upload -->
<div style="padding:16px 0;border-bottom:1px solid rgba(28,194,235,0.08)">
	<label class="br-form-label" style="margin-bottom:8px"><?= __('Bulk Upload Players', 'bluerabbit'); ?></label>
	<span class="br-form-hint" style="display:block;margin-bottom:10px"><?= __("Upload a CSV file with a single column and the emails of the players you want to assign", "bluerabbit"); ?></span>
	<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
		<input type="file" name="the_csv_file_with_players" id="the_csv_file_with_players" class="br-input" style="padding:7px 14px;flex:1;min-width:200px" accept=".csv">
		<button type="button" onClick="assignBulkUsersToAchievement();" class="br-btn br-btn-green">
			<span class="icon icon-upload"></span> <?= __("Upload file", "bluerabbit"); ?>
		</button>
	</div>
</div>

<!-- Search + Actions Bar -->
<div style="display:flex;align-items:center;gap:10px;padding:16px 0;flex-wrap:wrap" id="tutorial-player-select">
	<input type="text" class="br-input" id="search-achievement-players" placeholder="<?= __('Search players...', 'bluerabbit'); ?>" style="flex:1;min-width:180px">
	<span style="font-size:13px;color:rgba(255,255,255,0.4)"><span id="ach-player-count"><?= count($players); ?></span> <?= __("players", "bluerabbit"); ?></span>
	<div class="br-actions" style="gap:6px">
		<button class="br-btn br-btn-green" onClick="triggerAchievements();" style="padding:6px 14px;font-size:13px">
			<span class="icon icon-check"></span> <?= __("Assign all", "bluerabbit"); ?>
		</button>
		<button class="br-btn br-btn-red" onClick="triggerAchievements('off');" style="padding:6px 14px;font-size:13px">
			<span class="icon icon-cancel"></span> <?= __("Remove all", "bluerabbit"); ?>
		</button>
	</div>
</div>

<!-- Player Cards -->
<div class="br-player-grid" id="ach-player-list">
	<?php foreach ($players as $p) {
		$pName = $p->player_display_name ?: $p->display_name;
		$isActive = in_array($p->player_id, $selected_players);
	?>
	<div class="br-player-card player-achievement-item <?= $isActive ? 'active' : ''; ?>"
		 id="player-achievement-<?= $p->player_id; ?>"
		 onClick="triggerAchievement(<?= "$a->achievement_id, $p->player_id"; ?>);"
		 data-search="<?= esc_attr(strtolower($pName . ' ' . $p->user_email)); ?>">
		<div class="br-player-avatar" style="background-image:url(<?= esc_url($p->player_picture); ?>)"></div>
		<div class="br-player-info">
			<span class="br-player-name"><?= esc_html($pName); ?></span>
			<span class="br-player-meta">
				<span style="color:#9f40e2;font-weight:700">Lv.<?= $p->player_level; ?></span> &middot; <?= esc_html($p->user_email); ?>
			</span>
			<input type="hidden" class="player-id" value="<?= $p->player_id; ?>">
		</div>
		<span class="br-player-check icon icon-check"></span>
	</div>
	<?php } ?>
</div>

<!-- Pagination -->
<div class="br-pagination" id="ach-player-pagination"></div>

<script>
var brAchPlayers = {
	page: 1,
	perPage: 20,
	init: function() {
		var self = this;
		$('#search-achievement-players').on('keyup', function() {
			self.page = 1;
			self.render();
		});
		this.render();
	},
	render: function() {
		var search = $('#search-achievement-players').val().toLowerCase();
		var $cards = $('#ach-player-list .br-player-card');
		$cards.each(function() {
			var match = !search || $(this).data('search').indexOf(search) >= 0;
			$(this).data('filtered', match);
		});
		var $visible = $cards.filter(function() { return $(this).data('filtered'); });
		var total = $visible.length;
		var pages = Math.ceil(total / this.perPage);
		if (this.page > pages) this.page = Math.max(1, pages);
		var start = (this.page - 1) * this.perPage;
		var end = start + this.perPage;
		$cards.hide();
		$visible.slice(start, end).show();
		$('#ach-player-count').text(total);
		this.renderPagination(pages);
	},
	renderPagination: function(pages) {
		if (pages <= 1) { $('#ach-player-pagination').html(''); return; }
		var h = '', p = this.page;
		if (p > 1) h += '<button class="br-page-btn" onclick="brAchPlayers.goTo(' + (p-1) + ')">&laquo;</button>';
		var start = Math.max(1, p - 3), end = Math.min(pages, p + 3);
		if (start > 1) { h += '<button class="br-page-btn" onclick="brAchPlayers.goTo(1)">1</button>'; if (start > 2) h += '<span style="color:rgba(255,255,255,0.3)">…</span>'; }
		for (var i = start; i <= end; i++) {
			h += '<button class="br-page-btn' + (i === p ? ' active' : '') + '" onclick="brAchPlayers.goTo(' + i + ')">' + i + '</button>';
		}
		if (end < pages) { if (end < pages-1) h += '<span style="color:rgba(255,255,255,0.3)">…</span>'; h += '<button class="br-page-btn" onclick="brAchPlayers.goTo(' + pages + ')">' + pages + '</button>'; }
		if (p < pages) h += '<button class="br-page-btn" onclick="brAchPlayers.goTo(' + (p+1) + ')">&raquo;</button>';
		$('#ach-player-pagination').html(h);
	},
	goTo: function(p) {
		this.page = p;
		this.render();
		document.getElementById('ach-player-list').scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
};
$(function() { brAchPlayers.init(); });
</script>

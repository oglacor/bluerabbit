<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if($adventure && ($isGM || $isAdmin || $isNPC)){
	$achievement_id = isset($_GET['achievement_id']) ? (int) $_GET['achievement_id'] : 0;
	$paths = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE adventure_id=$adv_parent_id AND achievement_display!='badge' AND achievement_status='publish' AND achievement_id != $achievement_id ");
	$a = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."br_achievements WHERE achievement_id=$achievement_id AND achievement_status='publish'");
	if(isset($a)){
		$selected_players = $wpdb->get_col("SELECT player_id FROM ".$wpdb->prefix."br_player_achievement WHERE achievement_id=$a->achievement_id AND adventure_id=$adv_child_id");
	}
	$is_edit = (isset($a) && $a);
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-purple">
			<span class="icon icon-achievement br-icon-purple br-icon-lg"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Achievement", "bluerabbit") . ' — ' . esc_html($a->achievement_name) : __("New Achievement", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<?php if($is_edit){ ?>
			<input type="hidden" id="the_achievement_id" value="<?= $a->achievement_id; ?>">
			<?php if (!empty($a->ref_id)) { ?><input type="hidden" id="the_achievement_ref_id" value="<?= $a->ref_id; ?>"><?php } ?>
		<?php } ?>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="main-tabs-buttons">
		<?php if($isGM || $isAdmin){ ?>
		<button class="br-tab-btn active" onClick="brScrollTo('general', this);">
			<span class="icon icon-settings"></span> <?= __("Settings", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('achievement-codes', this);">
			<span class="icon icon-qr"></span> <?= __("Codes", "bluerabbit"); ?>
		</button>
		<?php } ?>
		<button class="br-tab-btn <?= $isNPC ? 'active' : ''; ?>" onClick="brScrollTo('select-players', this);">
			<span class="icon icon-players"></span> <?= __("Players", "bluerabbit"); ?>
		</button>
	</div>

	<!-- Tab Content -->
		<?php if($isGM || $isAdmin){ ?>
		<!-- ═══ SETTINGS ═══ -->
		<div class="br-scroll-section" id="general"><div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-achievement"></span> <?= __("Achievement Settings", "bluerabbit"); ?></h3>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Name", "bluerabbit"); ?></label>
				<input class="br-input br-input-lg" type="text" id="the_achievement_name"
					   value="<?= isset($a) ? esc_attr($a->achievement_name) : ''; ?>"
					   placeholder="<?= __('Achievement name', 'bluerabbit'); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Type", "bluerabbit"); ?></label>
				<select class="br-input" id="the_achievement_display" onChange="checkPath();">
					<option <?= (isset($a) && $a->achievement_display=='badge') ? 'selected' : ''; ?> value="badge"><?= __("Badge", "bluerabbit"); ?></option>
					<option <?= (isset($a) && $a->achievement_display=='rank') ? 'selected' : ''; ?> value="rank"><?= __("Rank", "bluerabbit"); ?></option>
					<option <?= (isset($a) && $a->achievement_display=='path') ? 'selected' : ''; ?> value="path"><?= __("Path", "bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Achievement Badge", "bluerabbit"); ?> <span class="br-required">*<?= __("Required", "bluerabbit"); ?></span></label>
					<div class="br-form-component">
						<div class="br-gallery br-gallery-single">
							<?php $thumb_id = 'the_achievement_badge'; $file = isset($a) ? $a->achievement_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
						</div>

                        
					</div>
				</div>
				<?php if ($is_edit && !empty($a->achievement_qrcode)) { ?>
                    <div class="br-form-group">
                        <label class="br-form-label"><?= __("QR Code", "bluerabbit"); ?></label>
                        <div class="br-form-component">
                            <div class="br-gallery br-gallery-single">
                                <div class="br-gallery-item" id="achievement-qr-code-<?= $a->achievement_id; ?>">
                                    <div class="br-gallery-thumb">
                                        <img src="<?= esc_url($a->achievement_qrcode); ?>" class="br-qr-preview" title="<?= __('QR Code', 'bluerabbit'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php } ?>
			</div>

			<div class="br-form-grid">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Color", "bluerabbit"); ?></label>
					<div class="br-form-component" id="tutorial-color-select">
						<?php $selected_color = isset($a) ? $a->achievement_color : 'purple'; ?>
						<input id="the_achievement_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
						<?php $color_select_id = "#the_achievement_color"; include(TEMPLATEPATH . '/color-select.php'); ?>
					</div>
				</div>
			</div>

			<div class="conditional-display path-display">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Branch", "bluerabbit"); ?></label>
					<span class="br-form-hint"><?= __("Assign to a branch — players choose ONE path from the branch", "bluerabbit"); ?></span>
					<?php
					$branch_groups_list = BR_Branch::instance()->getBranchGroups($adventure->adventure_id);
					$current_branch = ($is_edit && $a->branch_group_id) ? (int) $a->branch_group_id : 0;
					?>
					<input type="hidden" id="the_branch_group_id" value="<?= $current_branch; ?>">
					<input type="hidden" id="the_achievement_group" value="">
					<div class="br-branch-selector">
						<button type="button" class="br-branch-opt <?= !$current_branch ? 'active' : ''; ?>" onClick="brSelectBranch(0, this);">
							<?= __("None", "bluerabbit"); ?>
						</button>
						<?php foreach ($branch_groups_list as $bg) {
							$bg_count = count(BR_Branch::instance()->getGroupAchievements($bg->group_id));
							$is_active = ($current_branch == $bg->group_id);
						?>
						<button type="button" class="br-branch-opt <?= $is_active ? 'active' : ''; ?>" data-group-id="<?= $bg->group_id; ?>" onClick="brSelectBranch(<?= $bg->group_id; ?>, this);">
							<?= esc_html($bg->group_name); ?>
							<span class="br-branch-count">(<?= $bg_count; ?>)</span>
						</button>
						<?php } ?>
						<button type="button" class="br-branch-opt br-branch-add" onClick="brCreateBranchInline();">
							<span class="icon icon-add"></span> <?= __("New Branch", "bluerabbit"); ?>
						</button>
					</div>
					<div id="new-branch-inline" class="br-branch-inline-form">
						<input class="br-input" id="new-branch-name-inline" placeholder="<?= __('Branch name...', 'bluerabbit'); ?>">
						<button class="br-btn br-btn-green br-btn-sm" onClick="brSaveNewBranchInline();"><span class="icon icon-check"></span></button>
						<button class="br-btn ghost br-btn-sm" onClick="$('#new-branch-inline').hide();"><span class="icon icon-cancel"></span></button>
					</div>
				</div>
				<div class="br-form-group badge-display">
					<label class="br-form-label"><?= __("Available for", "bluerabbit"); ?></label>
					<select class="br-input" id="the_achievement_path">
						<option <?= (isset($a) && !$a->achievement_path) ? 'selected' : ''; ?> value="0"><?= __("All Paths", "bluerabbit"); ?></option>
						<?php foreach ($paths as $path) { ?>
						<option value="<?= $path->achievement_id; ?>" <?= (isset($a) && $a->achievement_path == $path->achievement_id) ? 'selected' : ''; ?>><?= esc_html($path->achievement_name); ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="br-form-group conditional-display badge-display">
				<label class="br-form-label"><?= __("Max Players", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Leave blank for no limit", "bluerabbit"); ?></span>
				<input class="br-input" type="number" id="the_achievement_max" value="<?= isset($a) ? $a->achievement_max : ''; ?>" placeholder="0">
			</div>

			<div class="br-form-grid conditional-display badge-display path-display">
				<div class="br-form-group">
					<label class="br-form-label"><?= $xp_long_label; ?></label>
					<input class="br-input" type="number" id="the_achievement_xp" value="<?= isset($a) ? $a->achievement_xp : ''; ?>">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= $bloo_long_label; ?></label>
					<input class="br-input" type="number" id="the_achievement_bloo" value="<?= isset($a) ? $a->achievement_bloo : ''; ?>">
				</div>
				<?php if ($use_encounters) { ?>
				<div class="br-form-group">
					<label class="br-form-label"><?= $ep_long_label; ?></label>
					<input class="br-input" type="number" id="the_achievement_ep" value="<?= isset($a) ? $a->achievement_ep : ''; ?>">
				</div>
				<?php } ?>
			</div>

			<div class="br-form-group conditional-display badge-display">
				<label class="br-form-label"><?= __("Deadline", "bluerabbit"); ?></label>
				<?php $deadline = (isset($a) && $a->achievement_deadline != "0000-00-00 00:00:00") ? date('Y/m/d H:i', strtotime($a->achievement_deadline)) : ''; ?>
				<input class="br-input datetimepicker" autocomplete="off" id="the_achievement_deadline" value="<?= $deadline; ?>" placeholder="<?= __('No deadline', 'bluerabbit'); ?>">
				<input class="the_start_date" type="hidden" value="<?= date('Y/m/d H:i'); ?>">
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Secret Message", "bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("This message will be seen once the players earn the achievement.", "bluerabbit"); ?></span>
				<?php
				$wp_editor_settings = ($roles[0] == "administrator") ? ['quicktags' => true, 'editor_height' => 350] : ['quicktags' => false, 'editor_height' => 350];
				wp_editor(isset($a) ? $a->achievement_content : '', 'the_achievement_content', $wp_editor_settings);
				?>
			</div>
		</div></div>

		<!-- ═══ CODES ═══ -->
		<div class="br-scroll-section" id="achievement-codes"><div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-magic"></span> <?= __("Magic Code", "bluerabbit"); ?></h3>
			<span class="br-section-desc"><?= __("This code can be used by all players. The reward only happens once.", "bluerabbit"); ?></span>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Magic Code", "bluerabbit"); ?></label>
				<div class="br-input-row">
					<input class="br-input br-input-lg" type="text" id="the_achievement_code"
						   value="<?= isset($a) ? $a->achievement_code : ''; ?>" onChange="updateMagicCode();">
					<button class="br-btn br-btn-green" onClick="createMagicCode();"><span class="icon icon-magic"></span> <?= __("Generate", "bluerabbit"); ?></button>
					<button class="br-btn br-btn-red" onClick="clearMagicCode();"><span class="icon icon-cancel"></span></button>
				</div>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Magic Link", "bluerabbit"); ?></label>
				<?php $magicLink = (isset($a) && $a->achievement_code) ? get_bloginfo('url') . "/magic-link/?c=$a->achievement_code&adv=$a->adventure_id" : ''; ?>
				<input type="hidden" id="site-url" value="<?= get_bloginfo('url') . '/magic-link/?&c='; ?>">
				<input class="br-input br-input-readonly" id="the_magic_link" readonly type="text" value="<?= $magicLink; ?>" onClick="this.select();">
			</div>

			<h3 class="br-panel-title br-mt-lg"><span class="icon icon-qr"></span> <?= __("Unique Codes", "bluerabbit"); ?></h3>
			<span class="br-section-desc"><?= __("These codes can only be used once per player.", "bluerabbit"); ?></span>
			<?php if(isset($a)){ ?>
				<div class="tabs" id="unique-code-tabs">
					<div class="tabs-header" id="unique-code-tabs-buttons">
						<div class="br-tabs br-tabs-inline">
							<button onClick="switchTabs('#unique-code-tabs','#unique-codes-avaliable');" class="br-tab-btn tab-button active" id="unique-codes-avaliable-tab-button">
								<span class="icon icon-check"></span> <?= __("Available", "bluerabbit"); ?>
							</button>
							<button onClick="switchTabs('#unique-code-tabs','#unique-codes-redeemed');" class="br-tab-btn tab-button" id="unique-codes-redeemed-tab-button">
								<span class="icon icon-players"></span> <?= __("Redeemed", "bluerabbit"); ?>
							</button>
							<button onClick="switchTabs('#unique-code-tabs','#unique-codes-expired');" class="br-tab-btn tab-button" id="unique-codes-expired-tab-button">
								<span class="icon icon-cancel"></span> <?= __("Expired", "bluerabbit"); ?>
							</button>
							<button class="br-btn br-ml-auto" onClick="newUniqueAchievementCode(<?= $a->achievement_id; ?>);switchTabs('#unique-code-tabs','#unique-codes-avaliable');">
								<span class="icon icon-add"></span> <?= __("Create Code", "bluerabbit"); ?>
							</button>
						</div>
					</div>

					<div class="tab active br-tab-content" id="unique-codes-avaliable">
						<?php $codes = BR_Achievement::instance()->getUniqueAchievementCodes($a->achievement_id);
						$available = array_filter($codes, function($c) { return $c->code_status == 'publish'; });
						?>
						<?php if (empty($available)) { ?>
						<div class="br-empty br-empty-sm"><span class="icon icon-qr"></span><h3><?= __("No available codes", "bluerabbit"); ?></h3></div>
						<?php } else { ?>
						<table class="br-table">
							<thead><tr><th class="br-th-narrow"></th><th><?= __("Code", "bluerabbit"); ?></th><th class="br-th-actions"><?= __("Actions", "bluerabbit"); ?></th></tr></thead>
							<tbody id="achievement-codes-table">
							<?php foreach ($codes as $key => $c) { if ($c->code_status == 'publish') { include(TEMPLATEPATH . '/achievement-unique-code.php'); } } ?>
							</tbody>
						</table>
						<details class="br-details-links">
							<summary><?= __("Show all links", "bluerabbit"); ?></summary>
							<div class="br-links-panel">
								<?php foreach ($codes as $c) { if ($c->code_status == 'publish') { ?>
								<div class="br-link-line"><?= get_bloginfo('url') . "/magic-link/?c=$c->code_value&adv=$a->adventure_id"; ?></div>
								<?php } } ?>
							</div>
						</details>
						<?php } ?>
					</div>

					<div class="tab br-tab-content" id="unique-codes-redeemed">
						<?php $redeemed = array_filter($codes, function($c) { return $c->code_status == 'redeem'; }); ?>
						<?php if (empty($redeemed)) { ?>
						<div class="br-empty br-empty-sm"><span class="icon icon-players"></span><h3><?= __("No redeemed codes", "bluerabbit"); ?></h3></div>
						<?php } else { ?>
						<table class="br-table">
							<thead><tr><th><?= __("Code", "bluerabbit"); ?></th><th><?= __("Status", "bluerabbit"); ?></th><th><?= __("Redeemed", "bluerabbit"); ?></th><th><?= __("Player", "bluerabbit"); ?></th></tr></thead>
							<tbody>
								<?php foreach ($codes as $c) { if ($c->code_status == 'redeem') { ?>
								<tr>
									<td><span class="br-code-value"><?= $c->code_value; ?></span></td>
									<td><span class="br-badge br-badge-green"><?= __("Redeemed", "bluerabbit"); ?></span></td>
									<td class="br-td-meta"><?= $c->code_redeemed; ?></td>
									<td><?= esc_html($c->player_display_name); ?></td>
								</tr>
								<?php } } ?>
							</tbody>
						</table>
						<?php } ?>
					</div>

					<div class="tab br-tab-content" id="unique-codes-expired">
						<?php $expired = array_filter($codes, function($c) { return $c->code_status == 'expired'; }); ?>
						<?php if (empty($expired)) { ?>
						<div class="br-empty br-empty-sm"><span class="icon icon-cancel"></span><h3><?= __("No expired codes", "bluerabbit"); ?></h3></div>
						<?php } else { ?>
						<table class="br-table">
							<thead><tr><th><?= __("Code", "bluerabbit"); ?></th><th><?= __("Expired", "bluerabbit"); ?></th></tr></thead>
							<tbody>
								<?php foreach ($codes as $c) { if ($c->code_status == 'expired') { ?>
								<tr>
									<td><span class="br-code-value br-code-expired"><?= $c->code_value; ?></span></td>
									<td class="br-td-meta"><?= $c->code_deadline; ?></td>
								</tr>
								<?php } } ?>
							</tbody>
						</table>
						<?php } ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="br-empty br-empty-md">
					<span class="icon icon-qr"></span>
					<h3><?= __("Must save the achievement before generating codes", "bluerabbit"); ?></h3>
				</div>
			<?php } ?>
		</div></div>
		<?php } ?>

		<!-- ═══ PLAYERS ═══ -->
		<div class="br-scroll-section" id="select-players"><div class="br-panel">
			<?php if(isset($a)){ ?>
				<div class="tabs" id="assign-manually">
					<div class="tabs-header" id="assign-manually-buttons">
						<div class="br-tabs br-tabs-inline">
							<button onClick="switchTabs('#assign-manually','#players-selection');" class="br-tab-btn tab-button active" id="players-selection-tab-button">
								<span class="icon icon-players"></span> <?= __("Select Players", "bluerabbit"); ?>
							</button>
							<button onClick="switchTabs('#assign-manually','#players-awarded');" class="br-tab-btn tab-button" id="players-awarded-tab-button">
								<span class="icon icon-achievement"></span> <?= __("Awarded", "bluerabbit"); ?>
								<span class="br-tab-count br-tab-count-purple"><?= count($selected_players); ?></span>
							</button>
						</div>
					</div>

					<div class="tab active br-tab-content-flush" id="players-selection">
						<?php include(TEMPLATEPATH . '/player-select-achievement.php'); ?>
					</div>

					<div class="tab br-tab-content" id="players-awarded">
						<?php
						$awarded_players = [];
						if (!empty($selected_players)) {
							$player_ids = implode(",", array_map('intval', $selected_players));
							$awarded_players = $wpdb->get_results("
								SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email
								FROM {$wpdb->prefix}br_player_adventure a
								LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
								WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' AND a.player_id IN ($player_ids)
								ORDER BY b.player_email LIMIT 1000
							");
						}
						?>
						<div class="br-section-header-row">
							<span class="br-section-title"><?= __("Awarded Players", "bluerabbit"); ?></span>
							<span class="br-badge br-badge-purple"><?= count($awarded_players); ?> <?= __("players", "bluerabbit"); ?></span>
						</div>

						<?php if (empty($awarded_players)) { ?>
						<div class="br-empty br-empty-sm">
							<span class="icon icon-achievement"></span>
							<h3><?= __("No players awarded yet", "bluerabbit"); ?></h3>
						</div>
						<?php } else { ?>
						<div class="br-search-bar">
							<input type="text" class="br-input br-search-input" id="search-awarded-players" placeholder="<?= __('Search awarded players...', 'bluerabbit'); ?>">
						</div>
						<table class="br-table" id="awarded-players-table">
							<thead>
								<tr>
									<th class="br-th-narrow"></th>
									<th><?= __("Player", "bluerabbit"); ?></th>
									<th><?= __("Email", "bluerabbit"); ?></th>
									<th class="br-th-actions"><?= __("Actions", "bluerabbit"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($awarded_players as $play) { ?>
								<tr id="awarded-row-<?= $play->player_id; ?>" data-search="<?= esc_attr(strtolower($play->player_first . ' ' . $play->player_last . ' ' . $play->player_email)); ?>">
									<td>
										<div class="br-player-avatar" style="background-image:url(<?= esc_url($play->player_picture); ?>)"></div>
									</td>
									<td>
										<span class="br-player-name"><?= esc_html($play->player_first . ' ' . $play->player_last); ?></span>
									</td>
									<td class="br-td-meta"><?= esc_html($play->player_email); ?></td>
									<td id="player-achievement-list-<?= $play->player_id; ?>" class="active br-td-actions">
										<button class="active-content br-btn br-btn-red br-btn-sm" onClick="triggerAchievement(<?= "$a->achievement_id, $play->player_id"; ?>);">
											<span class="icon icon-trash"></span> <?= __("Remove", "bluerabbit"); ?>
										</button>
										<button class="inactive-content br-btn br-btn-green br-btn-sm" onClick="triggerAchievement(<?= "$a->achievement_id, $play->player_id"; ?>);">
											<span class="icon icon-check"></span> <?= __("Restore", "bluerabbit"); ?>
										</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
						<div class="br-pagination" id="awarded-pagination"></div>
						<script>
						var brAwardedPlayers = {
							page: 1, perPage: 20,
							init: function() {
								var self = this;
								$('#search-awarded-players').on('keyup', function() { self.page = 1; self.render(); });
								this.render();
							},
							render: function() {
								var search = $('#search-awarded-players').val().toLowerCase();
								var $rows = $('#awarded-players-table tbody tr');
								$rows.each(function() {
									var match = !search || ($(this).data('search') || '').indexOf(search) >= 0;
									$(this).data('filtered', match);
								});
								var $visible = $rows.filter(function() { return $(this).data('filtered'); });
								var total = $visible.length, pages = Math.ceil(total / this.perPage);
								if (this.page > pages) this.page = Math.max(1, pages);
								var start = (this.page - 1) * this.perPage, end = start + this.perPage;
								$rows.hide();
								$visible.slice(start, end).show();
								this.renderPagination(pages);
							},
							renderPagination: function(pages) {
								if (pages <= 1) { $('#awarded-pagination').html(''); return; }
								var h = '', p = this.page;
								if (p > 1) h += '<button class="br-page-btn" onclick="brAwardedPlayers.goTo('+(p-1)+')">&laquo;</button>';
								for (var i = Math.max(1,p-3); i <= Math.min(pages,p+3); i++) {
									h += '<button class="br-page-btn'+(i===p?' active':'')+'" onclick="brAwardedPlayers.goTo('+i+')">'+i+'</button>';
								}
								if (p < pages) h += '<button class="br-page-btn" onclick="brAwardedPlayers.goTo('+(p+1)+')">&raquo;</button>';
								$('#awarded-pagination').html(h);
							},
							goTo: function(p) { this.page = p; this.render(); }
						};
						$(function() { brAwardedPlayers.init(); });
						</script>
						<?php } ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="br-empty br-empty-md">
					<span class="icon icon-players"></span>
					<h3><?= __("Must save the achievement before awarding players", "bluerabbit"); ?></h3>
				</div>
			<?php } ?>
		</div></div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<a class="br-btn br-btn-red" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id"; ?>">
		<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
	</a>
	<div class="br-actions">
		<?php if($isGM || $isAdmin){ ?>
		<select id="the_achievement_status" class="br-input br-input-auto">
			<option value="publish" <?= (!$is_edit || !$a->achievement_status || $a->achievement_status == 'publish') ? 'selected' : ''; ?>><?= __("Publish", "bluerabbit"); ?></option>
			<option value="draft" <?= ($is_edit && $a->achievement_status == 'draft') ? 'selected' : ''; ?>><?= __("Draft", "bluerabbit"); ?></option>
			<option value="trash" <?= ($is_edit && $a->achievement_status == 'trash') ? 'selected' : ''; ?>><?= __("Trash", "bluerabbit"); ?></option>
		</select>
		<?php } ?>
		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_achievement_nonce'); ?>">
		<button id="submit-button" type="button" class="br-btn br-btn-green br-btn-submit" onClick="updateAchievement();">
			<span class="icon icon-check"></span>
			<?= $is_edit ? __("Update Achievement", "bluerabbit") : __("Create Achievement", "bluerabbit"); ?>
		</button>
	</div>
</div>

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('.br-tabs-sticky .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('.br-tabs-sticky .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();

checkPath();

function brSelectBranch(groupId, btn) {
	$('#the_branch_group_id').val(groupId);
	$('.br-branch-opt').removeClass('active');
	$(btn).addClass('active');
}

function brCreateBranchInline() {
	$('#new-branch-inline').show();
	$('#new-branch-name-inline').val('').focus();
}

function brSaveNewBranchInline() {
	var name = $('#new-branch-name-inline').val();
	if (!name) return;
	showLoader('small');
	$.ajax({
		url: runAJAX.ajaxurl, method: 'POST',
		data: {
			action: 'br_update_branch_group',
			adventure_id: $('#the_adventure_id').val(),
			group_id: '',
			group_name: name,
			group_description: '',
			group_status: 'publish'
		},
		success: function(json) {
			displayAjaxResponse(json);
			var data = JSON.parse(json);
			if (data.success && data.group_id) {
				var newBtn = $('<button type="button" class="br-branch-opt active" data-group-id="' + data.group_id + '" onClick="brSelectBranch(' + data.group_id + ', this);">' + name + ' <span class="br-branch-count">(0)</span></button>');
				$('.br-branch-add').before(newBtn);
				brSelectBranch(data.group_id, newBtn[0]);
				$('#new-branch-inline').hide();
			}
		}
	});
}
</script>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

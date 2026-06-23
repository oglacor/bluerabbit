<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
<?php
	// Guilds
	if($isGM || $isAdmin){
		$guilds = BR_Guild::instance()->getAllGuilds($adventure->adventure_id);
	}else{
		$guilds = BR_Guild::instance()->getMyGuilds($adventure->adventure_id);
	}

	// My Activity log
	$my_activity = $wpdb->get_results($wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}br_activity_log WHERE player_id=%d AND adventure_id=%d ORDER BY log_date DESC LIMIT 100",
		$current_user->ID, $adv_child_id
	));

	// Admin log (admin/GM only)
	$admin_log = array();
	if($isGM || $isAdmin){
		$admin_log = $wpdb->get_results($wpdb->prepare(
			"SELECT l.*, p.player_display_name, p.player_picture FROM {$wpdb->prefix}br_activity_log l LEFT JOIN {$wpdb->prefix}br_players p ON l.player_id = p.player_id WHERE l.adventure_id=%d ORDER BY l.log_date DESC LIMIT 200",
			$adv_child_id
		));
	}

	// Helper: relative time
	function br_wall_time_ago($datetime){
		$now = new DateTime();
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
		if($diff->y > 0) return $diff->y . 'y ago';
		if($diff->m > 0) return $diff->m . 'mo ago';
		if($diff->d > 0) return $diff->d . 'd ago';
		if($diff->h > 0) return $diff->h . 'h ago';
		if($diff->i > 0) return $diff->i . 'm ago';
		return __('just now','bluerabbit');
	}

	// Helper: action type to icon
	function br_activity_icon($action){
		$action = strtolower($action);
		if(strpos($action,'login') !== false) return 'icon-login';
		if(strpos($action,'add') !== false || strpos($action,'update') !== false || strpos($action,'set') !== false) return 'icon-edit';
		if(strpos($action,'complete') !== false || strpos($action,'submit') !== false) return 'icon-check';
		if(strpos($action,'purchase') !== false || strpos($action,'spent') !== false) return 'icon-basket';
		if(strpos($action,'enrolled') !== false || strpos($action,'registered') !== false) return 'icon-players';
		if(strpos($action,'attempt') !== false || strpos($action,'answer') !== false || strpos($action,'solved') !== false) return 'icon-challenge';
		if(strpos($action,'duplicate') !== false) return 'icon-duplicate';
		if(strpos($action,'failed') !== false) return 'icon-cancel';
		if(strpos($action,'survey') !== false) return 'icon-survey';
		return 'icon-activity';
	}

	// Helper: action type to color class
	function br_activity_color($action){
		$action = strtolower($action);
		if(strpos($action,'complete') !== false || strpos($action,'submit') !== false) return 'green';
		if(strpos($action,'failed') !== false) return 'red';
		if(strpos($action,'purchase') !== false || strpos($action,'spent') !== false) return 'amber';
		if(strpos($action,'login') !== false) return 'blue';
		if(strpos($action,'duplicate') !== false) return 'purple';
		if(strpos($action,'enrolled') !== false || strpos($action,'registered') !== false) return 'teal';
		if(strpos($action,'attempt') !== false || strpos($action,'answer') !== false || strpos($action,'solved') !== false) return 'orange';
		return 'default';
	}
?>

<div class="br-journey-manager">

	<!-- ═══ Channel Tabs ═══ -->
	<div class="br-tabs br-tabs-sticky br-wall-tabs">
		<button type="button" class="br-tab-btn active" data-channel="public" onclick="brWall.switchChannel('public','',this);">
			<span class="icon icon-socialiser"></span> <?= __('Public','bluerabbit'); ?>
		</button>
		<?php if($guilds){ ?>
			<?php foreach($guilds as $guild){ ?>
				<button type="button" class="br-tab-btn" data-channel="guild" data-guild-id="<?= $guild->guild_id; ?>" onclick="brWall.switchChannel('guild','<?= $guild->guild_id; ?>',this);">
					<span class="icon icon-guild"></span> <?= esc_html($guild->guild_name); ?>
				</button>
			<?php } ?>
		<?php } ?>
		<?php if($isGM || $isAdmin){ ?>
			<button type="button" class="br-tab-btn" data-channel="system" onclick="brWall.switchChannel('system','',this);">
				<span class="icon icon-megaphone"></span> <?= __('System','bluerabbit'); ?>
			</button>
		<?php } ?>
		<button type="button" class="br-tab-btn" data-channel="activity" onclick="brWall.switchChannel('activity','',this);">
			<span class="icon icon-activity"></span> <?= __('My Activity','bluerabbit'); ?>
		</button>
		<?php if($isGM || $isAdmin){ ?>
			<button type="button" class="br-tab-btn" data-channel="admin-log" onclick="brWall.switchChannel('admin-log','',this);">
				<span class="icon icon-stats"></span> <?= __('Admin Log','bluerabbit'); ?>
			</button>
		<?php } ?>
	</div>

	<!-- ═══ Chat Area (shared by Public / Guild / System) ═══ -->
	<div class="br-wall-channel" id="channel-chat">

		<!-- Guild banner (shown only when a guild channel is active) -->
		<?php if($guilds){ ?>
			<?php foreach($guilds as $guild){ ?>
				<div class="br-wall-guild-banner hidden" id="guild-banner-<?= $guild->guild_id; ?>">
					<?php if($guild->guild_logo){ ?>
						<div class="br-wall-avatar" style="background-image:url(<?= $guild->guild_logo; ?>)"></div>
					<?php } ?>
					<span class="br-wall-guild-name"><?= esc_html($guild->guild_name); ?></span>
				</div>
			<?php } ?>
		<?php } ?>

		<!-- Post Composer -->
		<div class="br-wall-composer br-panel" id="wall-composer">
			<textarea id="message-content" class="br-input" rows="3" placeholder="<?= __('Write a message...','bluerabbit'); ?>"></textarea>
			<div class="br-wall-composer-actions">
				<button id="public-post-button" class="br-btn cyan" onclick="postToWall('public');">
					<span class="icon icon-wall"></span> <?= __('Post','bluerabbit'); ?>
				</button>
				<?php if($isGM || $isAdmin){ ?>
					<button id="announcement-post-button" class="br-btn purple" onclick="postToWall('announcement','<?= $adventure->adventure_code; ?>');">
						<span class="icon icon-megaphone"></span> <?= __('PA Post','bluerabbit'); ?>
					</button>
				<?php } ?>
				<?php if($guilds){ ?>
					<?php foreach($guilds as $guild){ ?>
						<button id="guild-post-button-<?=$guild->guild_id;?>" class="br-btn green hidden guild-post-button" onclick="postToWall('guild',<?=$guild->guild_id;?>);">
							<span class="icon icon-guild"></span> <?= esc_html($guild->guild_name); ?>
						</button>
					<?php } ?>
				<?php } ?>
			</div>
		</div>

		<!-- Message Feed (single div — loadChat() targets this by ID) -->
		<div id="message-feed" class="br-wall-feed">
			<?php $announcements = BR_Announcement::instance()->getAnnouncements($adventure->adventure_id); ?>
			<?php if($announcements){ ?>
				<ul class="feed">
					<?php foreach($announcements['anns'] as $m){ ?>
						<?php include (TEMPLATEPATH . '/message.php'); ?>
					<?php } ?>
					<li class="clear"></li>
				</ul>
			<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-socialiser"></span>
					<h3><?= __('No messages yet','bluerabbit'); ?></h3>
					<p><?= __('Be the first to post something!','bluerabbit'); ?></p>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- ═══ Channel: My Activity ═══ -->
	<div class="br-wall-channel hidden" id="channel-activity">
		<div class="br-panel">
			<div class="br-panel-title"><span class="icon icon-activity"></span> <?= __('My Activity Timeline','bluerabbit'); ?></div>
			<?php if($my_activity){ ?>
				<div class="br-activity-timeline">
					<?php foreach($my_activity as $log){ ?>
						<div class="br-activity-item">
							<div class="br-activity-icon <?= br_activity_color($log->log_action); ?>">
								<span class="icon <?= br_activity_icon($log->log_action); ?>"></span>
							</div>
							<div class="br-activity-content">
								<span class="br-activity-action"><?= esc_html($log->log_action); ?> <strong><?= esc_html($log->log_type); ?></strong></span>
								<?php if($log->log_content){ ?>
									<span class="br-activity-detail"><?= esc_html($log->log_content); ?></span>
								<?php } ?>
							</div>
							<div class="br-activity-time"><?= br_wall_time_ago($log->log_date); ?></div>
						</div>
					<?php } ?>
				</div>
			<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-activity"></span>
					<h3><?= __('No activity yet','bluerabbit'); ?></h3>
					<p><?= __('Your actions will appear here as you explore the adventure.','bluerabbit'); ?></p>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- ═══ Channel: Admin Log ═══ -->
	<?php if($isGM || $isAdmin){ ?>
		<div class="br-wall-channel hidden" id="channel-admin-log">
			<div class="br-panel">
				<div class="br-panel-title"><span class="icon icon-stats"></span> <?= __('Adventure Activity Log','bluerabbit'); ?></div>

				<!-- Search & Filters -->
				<div class="br-admin-log-toolbar">
					<div class="br-search">
						<span class="icon icon-search"></span>
						<input type="text" id="admin-log-search" placeholder="<?= __('Search activity...','bluerabbit'); ?>">
					</div>
					<div class="br-type-filters" id="admin-log-filters">
						<button type="button" class="br-type-btn t-all active" data-filter="all" title="All"><span class="icon icon-activity"></span></button>
						<button type="button" class="br-type-btn" data-filter="login" title="Login" style="background:rgba(28,194,235,0.25);color:#1cc2eb;"><span class="icon icon-login"></span></button>
						<button type="button" class="br-type-btn" data-filter="complete" title="Complete" style="background:rgba(36,218,152,0.25);color:#24da98;"><span class="icon icon-check"></span></button>
						<button type="button" class="br-type-btn" data-filter="purchase" title="Purchase" style="background:rgba(255,193,7,0.25);color:#ffc107;"><span class="icon icon-basket"></span></button>
						<button type="button" class="br-type-btn" data-filter="attempt" title="Attempt" style="background:rgba(255,152,0,0.25);color:#ff9800;"><span class="icon icon-challenge"></span></button>
						<button type="button" class="br-type-btn" data-filter="failed" title="Failed" style="background:rgba(244,67,54,0.25);color:#f44336;"><span class="icon icon-cancel"></span></button>
					</div>
				</div>

				<!-- Log Table -->
				<?php if($admin_log){ ?>
					<div class="br-table-wrap br-admin-log">
						<table class="br-table" id="admin-log-table">
							<thead>
								<tr>
									<th></th>
									<th><?= __('Player','bluerabbit'); ?></th>
									<th><?= __('Action','bluerabbit'); ?></th>
									<th><?= __('Type','bluerabbit'); ?></th>
									<th><?= __('Content','bluerabbit'); ?></th>
									<th class="text-right"><?= __('Date','bluerabbit'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($admin_log as $log){ ?>
									<tr class="br-admin-log-row" data-action="<?= esc_attr(strtolower($log->log_action)); ?>" data-search="<?= esc_attr(strtolower($log->player_display_name . ' ' . $log->log_action . ' ' . $log->log_type . ' ' . $log->log_content)); ?>">
										<td>
											<div class="br-player-avatar" style="background-image:url(<?= esc_attr($log->player_picture); ?>); width:32px; height:32px;"></div>
										</td>
										<td><strong><?= esc_html($log->player_display_name); ?></strong></td>
										<td>
											<span class="br-badge br-badge-<?= br_activity_color($log->log_action) == 'default' ? 'blue' : br_activity_color($log->log_action); ?>">
												<?= esc_html($log->log_action); ?>
											</span>
										</td>
										<td><?= esc_html($log->log_type); ?></td>
										<td class="br-admin-log-content"><?= esc_html($log->log_content); ?></td>
										<td class="text-right"><span class="br-wall-time"><?= br_wall_time_ago($log->log_date); ?></span></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php }else{ ?>
					<div class="br-empty">
						<span class="icon icon-stats"></span>
						<h3><?= __('No activity logged','bluerabbit'); ?></h3>
						<p><?= __('Activity will appear here as players interact with the adventure.','bluerabbit'); ?></p>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

</div><!-- .br-journey-manager -->

<!-- Hidden inputs required by existing JS -->
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_post_wall_nonce'); ?>"/>
<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>" />
<input type="hidden" id="reload" value="1" />

<script>
var brWall = {
	activeChannel: 'public',
	activeGuildId: '',
	pollTimer: null,

	init: function(){
		var self = this;

		// Admin log search
		var searchInput = document.getElementById('admin-log-search');
		if(searchInput){
			searchInput.addEventListener('input', function(){
				self.filterAdminLog();
			});
		}

		// Admin log type filters
		document.querySelectorAll('#admin-log-filters .br-type-btn').forEach(function(btn){
			btn.addEventListener('click', function(){
				document.querySelectorAll('#admin-log-filters .br-type-btn').forEach(function(b){ b.classList.remove('active'); });
				this.classList.add('active');
				self.filterAdminLog();
			});
		});

		// Start polling for the initial channel (public)
		this.startPolling();
	},

	switchChannel: function(channel, guildId, btnEl){
		guildId = guildId || '';
		this.activeChannel = channel;
		this.activeGuildId = guildId;

		// Update tab active state
		document.querySelectorAll('.br-wall-tabs .br-tab-btn').forEach(function(btn){
			btn.classList.remove('active');
		});
		if(btnEl) btnEl.classList.add('active');

		// Stop any existing polling
		this.stopPolling();

		// Determine which top-level channel div to show
		var isChatChannel = (channel === 'public' || channel === 'guild' || channel === 'system');

		// Hide all channels
		document.querySelectorAll('.br-wall-channel').forEach(function(ch){
			ch.classList.add('hidden');
		});

		if(isChatChannel){
			// Show the shared chat container
			document.getElementById('channel-chat').classList.remove('hidden');

			// Hide all guild banners
			document.querySelectorAll('.br-wall-guild-banner').forEach(function(b){ b.classList.add('hidden'); });

			if(channel === 'public'){
				loadChat('public');
				document.getElementById('wall-composer').classList.remove('hidden');
				$('#public-post-button, #announcement-post-button').removeClass('hidden');
				$('.guild-post-button').addClass('hidden');
			} else if(channel === 'guild'){
				// Show guild banner
				var banner = document.getElementById('guild-banner-' + guildId);
				if(banner) banner.classList.remove('hidden');
				loadChat('guild', guildId);
				document.getElementById('wall-composer').classList.remove('hidden');
				$('#public-post-button, #announcement-post-button').addClass('hidden');
				$('.guild-post-button').addClass('hidden');
				$('#guild-post-button-' + guildId).removeClass('hidden');
			} else if(channel === 'system'){
				loadChat('system');
				document.getElementById('wall-composer').classList.add('hidden');
			}

			this.startPolling();
		} else {
			// Show the target non-chat channel
			var target = document.getElementById('channel-' + channel);
			if(target) target.classList.remove('hidden');
		}
	},

	startPolling: function(){
		var self = this;
		this.stopPolling();
		this.pollTimer = setInterval(function(){
			if(self.activeChannel === 'public'){
				loadChat('public');
			} else if(self.activeChannel === 'guild' && self.activeGuildId){
				loadChat('guild', self.activeGuildId);
			} else if(self.activeChannel === 'system'){
				loadChat('system');
			}
		}, 15000);
	},

	stopPolling: function(){
		if(this.pollTimer){
			clearInterval(this.pollTimer);
			this.pollTimer = null;
		}
	},

	filterAdminLog: function(){
		var searchVal = (document.getElementById('admin-log-search').value || '').toLowerCase();
		var activeFilter = document.querySelector('#admin-log-filters .br-type-btn.active');
		var filterType = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';

		document.querySelectorAll('.br-admin-log-row').forEach(function(row){
			var action = row.getAttribute('data-action') || '';
			var searchText = row.getAttribute('data-search') || '';

			var matchesFilter = (filterType === 'all') || (action.indexOf(filterType) !== -1);
			var matchesSearch = (searchVal === '') || (searchText.indexOf(searchVal) !== -1);

			row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
		});
	}
};

// Initialize when DOM is ready
jQuery(document).ready(function(){
	brWall.init();
});
</script>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

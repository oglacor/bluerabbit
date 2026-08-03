<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
// Staff-only. NPCs may read the roster; everything that adds, removes or
// re-roles a player is gated on $canManagePlayers below and refused server-side
// by BR_Access for NPCs.
if(!$canViewAdmin){ ?>
	<script>document.location.href="<?php bloginfo('url'); ?>/404";</script>
	<?php include (get_stylesheet_directory() . '/footer.php'); exit;
} ?>
<?php
$players = $wpdb->get_results("
	SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug, users.user_login
	FROM {$wpdb->prefix}br_player_adventure a
	JOIN {$wpdb->prefix}users users ON a.player_id = users.ID
	LEFT JOIN {$wpdb->prefix}br_players b ON a.player_id = b.player_id
	WHERE a.adventure_id=$adv_child_id AND a.player_adventure_role='player'
	ORDER BY a.player_adventure_status LIMIT 5000
");

$active = $inactive = [];
foreach($players as $p){
	if($p->player_adventure_status == 'in') $active[] = $p;
	elseif($p->player_adventure_status == 'out') $inactive[] = $p;
}

$c_remove   = esc_js(__("Remove?","bluerabbit"));
$c_activate = esc_js(__("Activate?","bluerabbit"));
?>

<div class="br-page">

	<input type="hidden" id="the_adventure_id" value="<?= $adventure->adventure_id; ?>">
	<input type="hidden" id="player-status-nonce" value="<?= wp_create_nonce('br_player_adventure_status_nonce'); ?>"/>
	<?php if($canManagePlayers){ ?>
		<input type="hidden" id="import_players_nonce" value="<?= wp_create_nonce('br_import_players_nonce'); ?>"/>
	<?php } ?>

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-adv-header-avatar">
			<span class="icon icon-players br-adv-header-icon"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= __('Manage Players','bluerabbit'); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<div class="br-actions br-mp-header-actions">
			<?php if(!$canManagePlayers){ ?>
				<span class="br-badge br-badge-amber"><span class="icon icon-lock"></span> <?= __("View only","bluerabbit"); ?></span>
			<?php } ?>
			<a class="br-btn ghost" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id"; ?>">
				<span class="icon icon-journey"></span> <?= __('Back to the journey','bluerabbit'); ?>
			</a>
		</div>
	</div>

	<!-- Tabs -->
	<div class="br-tabs br-tabs-sticky" id="mp-tabs">
		<button class="br-tab-btn active" onClick="brSwitchPanel('#mp-panels', '#mp-active', this);">
			<span class="icon icon-players"></span> <?= __("Active","bluerabbit"); ?>
			<span class="br-badge"><?= count($active); ?></span>
		</button>
		<button class="br-tab-btn" onClick="brSwitchPanel('#mp-panels', '#mp-inactive', this);">
			<span class="icon icon-cancel"></span> <?= __("Inactive","bluerabbit"); ?>
			<span class="br-badge"><?= count($inactive); ?></span>
		</button>
		<?php if($canManagePlayers){ ?>
			<button class="br-tab-btn" onClick="brSwitchPanel('#mp-panels', '#mp-add', this);">
				<span class="icon icon-add"></span> <?= __("Add Players","bluerabbit"); ?>
			</button>
		<?php } ?>
	</div>

	<div id="mp-panels">

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- ACTIVE PLAYERS                                         -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-panel-group" id="mp-active">
		<div class="br-panel">
			<h3 class="br-panel-title">
				<span class="icon icon-players"></span> <?= __("Active players","bluerabbit"); ?>
				<span class="br-badge"><?= count($active); ?></span>
			</h3>

			<?php if($active){ ?>
				<div class="br-adv-enrolled-toolbar">
					<input type="text" class="br-input br-adv-enrolled-search" id="mp-search-active" placeholder="<?= __("Search by name, nickname or email...","bluerabbit"); ?>">
					<select class="br-input br-adv-enrolled-filter" id="mp-perpage-active">
						<option value="30">30 <?= __("per page","bluerabbit"); ?></option>
						<option value="60">60 <?= __("per page","bluerabbit"); ?></option>
						<option value="120">120 <?= __("per page","bluerabbit"); ?></option>
						<option value="500">500 <?= __("per page","bluerabbit"); ?></option>
					</select>
					<span class="br-adv-enrolled-count"><span id="mp-count-active"><?= count($active); ?></span> / <?= count($active); ?> <?= __("players","bluerabbit"); ?></span>
				</div>

				<table class="br-table br-adv-players-table">
					<thead>
						<tr>
							<th><?= __("ID","bluerabbit"); ?></th>
							<th><?= __("User Login","bluerabbit"); ?></th>
							<th><?= __("Name","bluerabbit"); ?></th>
							<th><?= __("Lastname","bluerabbit"); ?></th>
							<th><?= __("Email","bluerabbit"); ?></th>
							<th><?= __("Work","bluerabbit"); ?></th>
							<th class="text-center"><?= __("Actions","bluerabbit"); ?></th>
						</tr>
					</thead>
					<tbody id="mp-list-active">
						<?php foreach($active as $play){ ?>
							<tr id="player-row-<?= $play->player_id; ?>" class="role-player"
								data-search="<?= esc_attr(strtolower($play->player_id.' '.$play->user_login.' '.$play->player_first.' '.$play->player_last.' '.$play->player_email)); ?>">
								<td class="br-adv-id-cell"><?= $play->player_id; ?></td>
								<td><?= esc_html($play->user_login); ?></td>
								<td><?= esc_html($play->player_first); ?></td>
								<td><?= esc_html($play->player_last); ?></td>
								<td class="br-adv-email-cell"><?= esc_html($play->player_email); ?></td>
								<td class="text-center">
									<a class="br-icon-link" target="_blank" title="<?= esc_attr__("Open this player's work","bluerabbit"); ?>"
										href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adventure->adventure_id&player_id=$play->player_id"; ?>">
										<span class="icon icon-document"></span>
									</a>
								</td>
								<td class="text-center">
									<div class="br-actions br-actions-center">
										<?php if(!$canManagePlayers){ ?>
											<span class="br-adv-locked-note"><?= __("View only","bluerabbit"); ?></span>
										<?php }elseif(BR_Utils::instance()->user_has_role($play->player_id,'br_player')){ ?>
											<button type="button" class="br-btn red br-btn-sm"
												onClick="brConfirmInline(this,'<?= $c_remove; ?>',function(){ updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id"; ?>,'out'); });">
												<span class="icon icon-cancel"></span> <?= __("Remove","bluerabbit"); ?>
											</button>
											<?php if($config['allow_gm_reset_password']['value'] > 0){ ?>
												<button type="button" class="br-btn ghost br-btn-sm" onClick="loadContent('update-player-password',<?= $play->player_id; ?>);">
													<span class="icon icon-lock"></span> <?= __("Password","bluerabbit"); ?>
												</button>
											<?php } ?>
										<?php }else{ ?>
											<span class="br-adv-locked-note"><?= __("Can't remove","bluerabbit"); ?></span>
										<?php } ?>
									</div>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<div class="br-empty br-initially-hidden" id="mp-empty-active"><?= __("No players match your search.","bluerabbit"); ?></div>
				<div class="br-pagination" id="mp-pagination-active"></div>
			<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-players"></span>
					<h3><?= __("No active players yet","bluerabbit"); ?></h3>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- INACTIVE PLAYERS                                       -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-panel-group br-initially-hidden" id="mp-inactive">
		<div class="br-panel">
			<h3 class="br-panel-title">
				<span class="icon icon-cancel"></span> <?= __("Inactive players","bluerabbit"); ?>
				<span class="br-badge br-badge-red"><?= count($inactive); ?></span>
			</h3>
			<p class="br-form-hint"><?= __("Players who were removed from this adventure. Their progress is kept and comes back if you activate them again.","bluerabbit"); ?></p>

			<?php if($inactive){ ?>
				<div class="br-adv-enrolled-toolbar">
					<input type="text" class="br-input br-adv-enrolled-search" id="mp-search-inactive" placeholder="<?= __("Search by name, nickname or email...","bluerabbit"); ?>">
					<select class="br-input br-adv-enrolled-filter" id="mp-perpage-inactive">
						<option value="30">30 <?= __("per page","bluerabbit"); ?></option>
						<option value="60">60 <?= __("per page","bluerabbit"); ?></option>
						<option value="120">120 <?= __("per page","bluerabbit"); ?></option>
						<option value="500">500 <?= __("per page","bluerabbit"); ?></option>
					</select>
					<span class="br-adv-enrolled-count"><span id="mp-count-inactive"><?= count($inactive); ?></span> / <?= count($inactive); ?> <?= __("players","bluerabbit"); ?></span>
				</div>

				<table class="br-table br-adv-players-table">
					<thead>
						<tr>
							<th><?= __("ID","bluerabbit"); ?></th>
							<th><?= __("User Login","bluerabbit"); ?></th>
							<th><?= __("Name","bluerabbit"); ?></th>
							<th><?= __("Lastname","bluerabbit"); ?></th>
							<th><?= __("Email","bluerabbit"); ?></th>
							<th class="text-center"><?= __("Actions","bluerabbit"); ?></th>
						</tr>
					</thead>
					<tbody id="mp-list-inactive">
						<?php foreach($inactive as $play){ ?>
							<tr id="player-row-<?= $play->player_id; ?>" class="role-player"
								data-search="<?= esc_attr(strtolower($play->player_id.' '.$play->user_login.' '.$play->player_first.' '.$play->player_last.' '.$play->player_email)); ?>">
								<td class="br-adv-id-cell"><?= $play->player_id; ?></td>
								<td><?= esc_html($play->user_login); ?></td>
								<td><?= esc_html($play->player_first); ?></td>
								<td><?= esc_html($play->player_last); ?></td>
								<td class="br-adv-email-cell"><?= esc_html($play->player_email); ?></td>
								<td class="text-center">
									<div class="br-actions br-actions-center">
										<?php if(!$canManagePlayers){ ?>
											<span class="br-adv-locked-note"><?= __("View only","bluerabbit"); ?></span>
										<?php }else{ ?>
											<button type="button" class="br-btn br-btn-green br-btn-sm"
												onClick="brConfirmInline(this,'<?= $c_activate; ?>',function(){ updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id"; ?>,'in'); });">
												<span class="icon icon-restore"></span> <?= __("Activate","bluerabbit"); ?>
											</button>
										<?php } ?>
									</div>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<div class="br-empty br-initially-hidden" id="mp-empty-inactive"><?= __("No players match your search.","bluerabbit"); ?></div>
				<div class="br-pagination" id="mp-pagination-inactive"></div>
			<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-check"></span>
					<h3><?= __("Nobody has been removed","bluerabbit"); ?></h3>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php if($canManagePlayers){ ?>
	<!-- ═══════════════════════════════════════════════════════ -->
	<!-- ADD PLAYERS                                            -->
	<!-- ═══════════════════════════════════════════════════════ -->
	<div class="br-panel-group br-initially-hidden" id="mp-add">
		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Bulk Upload","bluerabbit"); ?></h3>
			<span class="br-form-hint"><?= __("One file, one sweep. The whole CSV is read in your browser and imported row by row — there is no row limit. Existing accounts are enrolled instead of duplicated, and the import is verified against the database when it finishes.","bluerabbit"); ?></span>

			<div class="br-form-group">
				<a href="<?= get_bloginfo('template_directory');?>/sources/bulk-upload-users.csv" class="br-btn ghost" target="_blank">
					<span class="icon icon-download"></span>
					<?= __("Download CSV Template","bluerabbit"); ?>
				</a>
			</div>

			<div class="br-import-box">
				<label for="the_csv_file_with_users" class="br-form-label"><?= __("Select CSV File","bluerabbit"); ?></label>
				<input type="file" accept=".csv,text/csv" name="the_csv_file_with_users" id="the_csv_file_with_users" class="br-input" onChange="brImportPickFile();" />
				<div id="br-import-summary" class="br-import-summary"></div>
				<div class="br-actions">
					<button type="button" id="br-import-start" class="br-btn cyan" onClick="brStartPlayerImport();" disabled>
						<span class="icon icon-players"></span>
						<?= __("Start Import","bluerabbit"); ?>
					</button>
				</div>
			</div>
		</div>

		<div class="br-panel">
			<h3 class="br-panel-title"><span class="icon icon-players"></span> <?= __("Add Players One by One","bluerabbit"); ?></h3>
			<?php include (get_stylesheet_directory() . '/add-player-box.php'); ?>
		</div>
	</div>
	<?php } ?>

	</div><!-- /#mp-panels -->

	<?php if($canManagePlayers){ include (get_stylesheet_directory() . '/import-console.php'); } ?>
</div>

<script>
jQuery(function($){
	// One pager per table. Same behaviour as the enrolled list in Adventure
	// Settings: search narrows, pagination applies to what survived the search.
	function brMakePager(scope){
		var $body = $('#mp-list-' + scope);
		if(!$body.length) return null;
		var P = {
			page: 1, perPage: 30,
			render: function(){
				var terms = ($('#mp-search-' + scope).val()||'').toLowerCase().trim();
				terms = terms ? terms.split(/\s+/) : [];
				var visible = [];
				$body.children('tr').each(function(){
					this.style.display = 'none';
					var hay = this.getAttribute('data-search')||'';
					for(var t=0; t<terms.length; t++){ if(hay.indexOf(terms[t]) < 0) return; }
					visible.push(this);
				});
				var total = visible.length;
				var pages = Math.ceil(total / this.perPage);
				if(this.page > pages) this.page = Math.max(1, pages);
				var start = (this.page - 1) * this.perPage;
				var end = Math.min(start + this.perPage, total);
				for(var i=start; i<end; i++) visible[i].style.display = '';
				$('#mp-count-' + scope).text(total);
				$('#mp-empty-' + scope).toggleClass('br-initially-hidden', total > 0);
				this.nav(pages);
			},
			nav: function(pages){
				var $p = $('#mp-pagination-' + scope);
				if(pages <= 1){ $p.html(''); return; }
				var h='', c=this.page;
				if(c>1) h+='<button class="br-page-btn" data-go="'+(c-1)+'">&laquo;</button>';
				var s=Math.max(1,c-3), e=Math.min(pages,c+3);
				if(s>1){ h+='<button class="br-page-btn" data-go="1">1</button>'; if(s>2) h+='<span class="br-pagination-ellipsis">&hellip;</span>'; }
				for(var i=s;i<=e;i++) h+='<button class="br-page-btn'+(i===c?' active':'')+'" data-go="'+i+'">'+i+'</button>';
				if(e<pages){ if(e<pages-1) h+='<span class="br-pagination-ellipsis">&hellip;</span>'; h+='<button class="br-page-btn" data-go="'+pages+'">'+pages+'</button>'; }
				if(c<pages) h+='<button class="br-page-btn" data-go="'+(c+1)+'">&raquo;</button>';
				$p.html(h);
			}
		};
		$('#mp-search-' + scope).on('keyup search', function(){ P.page=1; P.render(); });
		$('#mp-perpage-' + scope).on('change', function(){ P.perPage = parseInt($(this).val(),10)||30; P.page=1; P.render(); });
		$('#mp-pagination-' + scope).on('click', '.br-page-btn', function(){
			P.page = parseInt($(this).attr('data-go'),10)||1;
			P.render();
			document.getElementById('mp-' + scope).scrollIntoView({behavior:'smooth', block:'start'});
		});
		P.render();
		return P;
	}
	brMakePager('active');
	brMakePager('inactive');
});
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

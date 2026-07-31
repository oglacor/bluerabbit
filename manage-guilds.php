<?php
	$guilds = BR_Guild::instance()->getGuilds($adventure->adventure_id);

	// Confirms are two-step in-place buttons (brConfirmInline) rather than the old
	// .confirm-action overlays. Those overlays are position:absolute with bottom:105%
	// and there is no positioned ancestor anywhere in this page's markup, so every
	// "Are you sure?" popup was being laid out against the initial containing block -
	// parked off-screen above the viewport, which is why trashing and deleting a
	// guild here never actually did anything.
	$c_trash    = esc_js(__("Trash?","bluerabbit"));
	$c_draft    = esc_js(__("To draft?","bluerabbit"));
	$c_publish  = esc_js(__("Publish?","bluerabbit"));
	$c_delete   = esc_js(__("Delete forever?","bluerabbit"));
	$c_dup      = esc_js(__("Duplicate?","bluerabbit"));
	$c_empty    = esc_js(__("Empty the trash?","bluerabbit"));

	$counts = [
		'publish' => isset($guilds['publish']) ? count($guilds['publish']) : 0,
		'draft'   => isset($guilds['draft'])   ? count($guilds['draft'])   : 0,
		'trash'   => isset($guilds['trash'])   ? count($guilds['trash'])   : 0,
	];
?>

<div class="br-journey-manager">
<input type="hidden" id="bulk-guild-nonce" value="<?= wp_create_nonce('br_bulk_guild_nonce'); ?>" />

<!-- Published Guilds -->
<?php if(isset($guilds['publish'])){ ?>
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-guild"></span>
		<?php _e('Published Guilds','bluerabbit'); ?>
		<span class="br-badge"><?= $counts['publish']; ?></span>
	</div>
	<input type="hidden" id="guild-group-nonce" value="<?php echo wp_create_nonce('guild_group_nonce'); ?>" />
	<input type="hidden" id="guild-capacity-nonce" value="<?php echo wp_create_nonce('guild_capacity_nonce'); ?>" />

	<div class="br-toolbar">
		<div class="br-search">
			<span class="icon icon-search"></span>
			<input type="text" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
		</div>
		<script>
			$('#search').keyup(function(){
				var valThis = $(this).val().toLowerCase();
				if(valThis == ""){
					$('table#guild-table-publish tbody > tr').show();
				}else{
					$('table#guild-table-publish tbody > tr').each(function(){
						var text = $(this).text().toLowerCase();
						(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
					});
				};
				// Rows hidden by the search stay selected but are no longer counted as
				// "all", so re-sync the header checkbox against what is visible.
				brGuildSyncBar('publish');
			});
		</script>
	</div>

	<div class="br-bulk-bar" id="guild-bulk-publish">
		<span class="br-bulk-count"><strong id="guild-bulk-count-publish">0</strong> <?php _e("selected","bluerabbit"); ?></span>
		<button class="br-btn amber br-btn-sm" onClick="brConfirmInline(this,'<?= $c_draft; ?>',function(){ brBulkGuildStatus('publish','draft'); });">
			<span class="icon icon-duplicate"></span> <?php _e("Move to draft","bluerabbit"); ?>
		</button>
		<button class="br-btn red br-btn-sm" onClick="brConfirmInline(this,'<?= $c_trash; ?>',function(){ brBulkGuildStatus('publish','trash'); });">
			<span class="icon icon-trash"></span> <?php _e("Move to trash","bluerabbit"); ?>
		</button>
	</div>

	<table class="br-table" id="guild-table-publish">
		<thead>
			<tr>
				<th class="text-center br-guild-pick-cell">
					<input type="checkbox" id="guild-check-all-publish" onChange="brGuildToggleAll('publish', this);">
				</th>
				<th><?php _e("Logo","bluerabbit"); ?></th>
				<th><?php _e("Color","bluerabbit"); ?></th>
				<th><?php _e("Name","bluerabbit"); ?></th>
				<th><?php _e("Link","bluerabbit"); ?></th>
				<th><?php _e("Group","bluerabbit"); ?></th>
				<th><?php _e("Capacity","bluerabbit"); ?></th>
				<th class="text-center"><?php _e("Actions","bluerabbit"); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($guilds['publish'] as $key=>$g){ ?>
			<?php if(!$g->guild_code) {
				$first_str = BR_Utils::instance()->random_str(12,'1234567890abcdefghijkls');
				$code_string = $first_str.$current_user->ID;
				$guild_code = str_shuffle($code_string);
				$guild_code_update = $wpdb->query("UPDATE {$wpdb->prefix}br_guilds SET guild_code='$guild_code' WHERE guild_id=$g->guild_id AND adventure_id=$adventure->adventure_id");
			}
			?>
			<tr class="guild" id="guild-<?= $g->guild_id;?>">
				<td class="text-center br-guild-pick-cell">
					<input type="checkbox" class="br-guild-pick" data-scope="publish" value="<?= $g->guild_id; ?>">
				</td>
				<td class="badge">
					<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_badge-<?= $g->guild_id; ?>">
					<button class="br-guild-badge-btn" onClick="showWPUpload('the_guild_badge-<?= $g->guild_id; ?>','a','guild',<?= $g->guild_id; ?>);" id="the_guild_badge-<?= $g->guild_id; ?>_thumb" style="background-image: url(<?= $g->guild_logo; ?>);">
					</button>
				</td>
				<td class="color relative layer base">
					<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_color-<?= $g->guild_id; ?>">
					<button class="br-guild-color-btn" <?= br_color_attr($g->guild_color) ?> id="color-trigger-guild-<?= $g->guild_id; ?>" onClick="activate('#color-select-<?=$g->guild_id;?>');"><span class="icon icon-guild"></span>
					</button>
					<div class="color-select-popup" id="color-select-<?=$g->guild_id;?>">
						<?php
						$selected_color = $g->guild_color;
						$object_color_id = $g->guild_id;
						$object_type='guild';
						?>
						<?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
					</div>
				</td>
				<td>
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= esc_attr($g->guild_name); ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td>
					<input type="text" readonly class="br-input" value="<?php echo get_bloginfo('url')."/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?>">
				</td>
				<td>
					<input type="text" class="br-input" id="the_guild_group-<?= $g->guild_id; ?>" value="<?= esc_attr($g->guild_group); ?>" onChange="setGuildGroup(<?= $g->guild_id; ?>);">
				</td>
				<td>
					<div class="br-input-row">
						<span class="br-badge-amber"><?= "$g->guild_current_capacity /"; ?></span>
						<input type="text" class="br-input" id="the_guild_capacity-<?= $g->guild_id; ?>" value="<?= esc_attr($g->guild_capacity); ?>" onChange="setGuildCapacity(<?= $g->guild_id; ?>);">
					</div>
				</td>
				<td class="text-center">
					<div class="br-actions br-actions-center">
						<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green br-btn-sm" title="<?= esc_attr__("Edit","bluerabbit"); ?>">
							<span class="icon icon-edit"></span>
						</a>
						<button class="br-btn br-btn-amber br-btn-sm" title="<?= esc_attr__("Duplicate","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_dup; ?>',function(){ duplicateRow(<?= $g->guild_id; ?>); });">
							<span class="icon icon-infinite"></span>
						</button>
						<button class="br-btn br-btn-amber br-btn-sm" title="<?= esc_attr__("Move to draft","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_draft; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','draft'); });">
							<span class="icon icon-duplicate"></span>
						</button>
						<button class="br-btn br-btn-red br-btn-sm" title="<?= esc_attr__("Move to trash","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_trash; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','trash'); });">
							<span class="icon icon-trash"></span>
						</button>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<?php }else{ ?>
	<div class="br-panel">
		<div class="br-empty">
			<span class="icon icon-guild"></span>
			<h3><?php _e("No guilds found","bluerabbit"); ?></h3>
		</div>
		<?php echo BR_Utils::instance()->addNewButton(__("Add New Guild","bluerabbit"),'light-green', 'guild', $adventure->adventure_id); ?>
	</div>
<?php } ?>

<!-- Draft Guilds -->
<?php if(isset($guilds['draft'])){ ?>
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-guild"></span>
		<?php _e('Draft Guilds','bluerabbit'); ?>
		<span class="br-badge"><?= $counts['draft']; ?></span>
	</div>

	<div class="br-bulk-bar" id="guild-bulk-draft">
		<span class="br-bulk-count"><strong id="guild-bulk-count-draft">0</strong> <?php _e("selected","bluerabbit"); ?></span>
		<button class="br-btn cyan br-btn-sm" onClick="brConfirmInline(this,'<?= $c_publish; ?>',function(){ brBulkGuildStatus('draft','publish'); });">
			<span class="icon icon-restore"></span> <?php _e("Publish","bluerabbit"); ?>
		</button>
		<button class="br-btn red br-btn-sm" onClick="brConfirmInline(this,'<?= $c_trash; ?>',function(){ brBulkGuildStatus('draft','trash'); });">
			<span class="icon icon-trash"></span> <?php _e("Move to trash","bluerabbit"); ?>
		</button>
	</div>

	<table class="br-table" id="guild-table-draft">
		<thead>
			<tr>
				<th class="text-center br-guild-pick-cell">
					<input type="checkbox" id="guild-check-all-draft" onChange="brGuildToggleAll('draft', this);">
				</th>
				<th><?php _e("Name","bluerabbit"); ?></th>
				<th class="text-center"><?php _e("Actions","bluerabbit"); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($guilds['draft'] as $key=>$g){ ?>
			<tr class="guild" id="guild-<?= $g->guild_id;?>">
				<td class="text-center br-guild-pick-cell">
					<input type="checkbox" class="br-guild-pick" data-scope="draft" value="<?= $g->guild_id; ?>">
				</td>
				<td>
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= esc_attr($g->guild_name); ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td class="text-center">
					<div class="br-actions br-actions-center">
						<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green br-btn-sm" title="<?= esc_attr__("Edit","bluerabbit"); ?>">
							<span class="icon icon-edit"></span>
						</a>
						<button class="br-btn br-btn-blue br-btn-sm" title="<?= esc_attr__("Publish","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_publish; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','publish'); });">
							<span class="icon icon-restore"></span>
						</button>
						<button class="br-btn br-btn-red br-btn-sm" title="<?= esc_attr__("Move to trash","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_trash; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','trash'); });">
							<span class="icon icon-trash"></span>
						</button>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<?php }else{ ?>
	<div class="br-panel">
		<div class="br-empty">
			<span class="icon icon-guild"></span>
			<h3><?php _e("No drafts found","bluerabbit"); ?></h3>
		</div>
	</div>
<?php } ?>

<!-- Trashed Guilds -->
<?php if(isset($guilds['trash'])){ ?>
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-trash"></span>
		<?php _e('Trashed Guilds','bluerabbit'); ?>
		<span class="br-badge br-badge-red"><?= $counts['trash']; ?></span>
		<span class="br-actions br-guild-trash-actions">
			<button class="br-btn red br-btn-sm" onClick="brConfirmInline(this,'<?= $c_empty; ?>',function(){ emptyTrash('guild'); });">
				<span class="icon icon-cancel"></span> <?php _e("Empty trash","bluerabbit"); ?>
			</button>
		</span>
	</div>
	<p class="br-form-hint"><?php _e("Deleting a guild for good also removes its members from it and frees them to be assigned a new one.","bluerabbit"); ?></p>

	<div class="br-bulk-bar" id="guild-bulk-trash">
		<span class="br-bulk-count"><strong id="guild-bulk-count-trash">0</strong> <?php _e("selected","bluerabbit"); ?></span>
		<button class="br-btn cyan br-btn-sm" onClick="brConfirmInline(this,'<?= $c_publish; ?>',function(){ brBulkGuildStatus('trash','publish'); });">
			<span class="icon icon-restore"></span> <?php _e("Restore","bluerabbit"); ?>
		</button>
		<button class="br-btn red br-btn-sm" onClick="brConfirmInline(this,'<?= $c_delete; ?>',function(){ brBulkGuildStatus('trash','delete'); });">
			<span class="icon icon-cancel"></span> <?php _e("Delete forever","bluerabbit"); ?>
		</button>
	</div>

	<table class="br-table" id="guild-table-trash">
		<thead>
			<tr>
				<th class="text-center br-guild-pick-cell">
					<input type="checkbox" id="guild-check-all-trash" onChange="brGuildToggleAll('trash', this);">
				</th>
				<th><?php _e("Name","bluerabbit"); ?></th>
				<th class="text-center"><?php _e("Actions","bluerabbit"); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($guilds['trash'] as $key=>$g){ ?>
			<tr class="guild" id="guild-<?= $g->guild_id;?>">
				<td class="text-center br-guild-pick-cell">
					<input type="checkbox" class="br-guild-pick" data-scope="trash" value="<?= $g->guild_id; ?>">
				</td>
				<td>
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= esc_attr($g->guild_name); ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td class="text-center">
					<div class="br-actions br-actions-center">
						<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green br-btn-sm" title="<?= esc_attr__("Edit","bluerabbit"); ?>">
							<span class="icon icon-edit"></span>
						</a>
						<button class="br-btn br-btn-blue br-btn-sm" title="<?= esc_attr__("Restore","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_publish; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','publish'); });">
							<span class="icon icon-restore"></span>
						</button>
						<button class="br-btn br-btn-amber br-btn-sm" title="<?= esc_attr__("Move to draft","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_draft; ?>',function(){ confirmStatus(<?= $g->guild_id; ?>,'guild','draft'); });">
							<span class="icon icon-duplicate"></span>
						</button>
						<button class="br-btn red br-btn-sm" title="<?= esc_attr__("Delete forever","bluerabbit"); ?>"
							onClick="brConfirmInline(this,'<?= $c_delete; ?>',function(){ brGuildStatusFor([<?= $g->guild_id; ?>],'delete'); });">
							<span class="icon icon-cancel"></span>
						</button>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<?php }else{ ?>
	<div class="br-panel">
		<div class="br-empty">
			<span class="icon icon-trash"></span>
			<h3><?php _e("Trash is empty","bluerabbit"); ?></h3>
		</div>
	</div>
<?php } ?>
<input type="hidden" id="row_type" value="guild"/>

</div><!-- /.br-journey-manager -->

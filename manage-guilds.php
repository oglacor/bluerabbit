<?php
	$guilds = BR_Guild::instance()->getGuilds($adventure->adventure_id);
?>

<div class="br-journey-manager">
<!-- Published Guilds -->
<?php if(isset($guilds['publish'])){ ?>
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-guild"></span>
		<?php _e('Published Guilds','bluerabbit'); ?>
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
					$('table#table-guild tbody > tr').show();
				}else{
					$('table#table-guild tbody > tr').each(function(){
						var text = $(this).text().toLowerCase();
						(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
					});
				};
			});
		</script>
	</div>

	<table class="br-table" id="table-guild">
		<thead>
			<tr>
				<th><?php _e("Logo","bluerabbit"); ?></th>
				<th><?php _e("Color","bluerabbit"); ?></th>
				<th><?php _e("Name","bluerabbit"); ?></th>
				<th><?php _e("Link","bluerabbit"); ?></th>
				<th><?php _e("Group","bluerabbit"); ?></th>
				<th><?php _e("Capacity","bluerabbit"); ?></th>
				<th class="text-center" width="5%"><span class="icon icon-edit"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-infinite"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-duplicate"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-trash"></span></th>
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
				<td class="badge">
					<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_badge-<?= $g->guild_id; ?>">
					<button class="button-icon font _24 sq-40  icon-lg" onClick="showWPUpload('the_guild_badge-<?= $g->guild_id; ?>','a','guild',<?= $g->guild_id; ?>);" id="the_guild_badge-<?= $g->guild_id; ?>_thumb" style="background-image: url(<?= $g->guild_logo; ?>);">
					</button>
				</td>
				<td class="color relative layer base">
					<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_color-<?= $g->guild_id; ?>">
					<button class="button-icon font _24 sq-40 <?=$g->guild_color;?>-bg-400" id="color-trigger-guild-<?= $g->guild_id; ?>" onClick="activate('#color-select-<?=$g->guild_id;?>');"><span class="icon icon-guild"></span>
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
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td>
					<input type="text" readonly class="br-input" value="<?php echo get_bloginfo('url')."/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?>">
				</td>
				<td>
					<input type="text" class="br-input" id="the_guild_group-<?= $g->guild_id; ?>" value="<?= $g->guild_group ?>" onChange="setGuildGroup(<?= $g->guild_id; ?>);">
				</td>
				<td>
					<div class="br-input-row">
						<span class="br-badge-amber"><?= "$g->guild_current_capacity /"; ?></span>
						<input type="text" class="br-input" id="the_guild_capacity-<?= $g->guild_id; ?>" value="<?= $g->guild_capacity ?>" onChange="setGuildCapacity(<?= $g->guild_id; ?>);">
					</div>
				</td>
				<td class="text-center">
					<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green"><span class="icon icon-edit"></span></a>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-amber" onClick="showOverlay('#confirm-duplicate-<?= $g->guild_id; ?>');">
						<span class="icon icon-infinite"></span>
					</button>
					<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn amber" onClick="duplicateRow(<?= $g->guild_id; ?>);">
								<span class="icon icon-infinite"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-amber" onClick="showOverlay('#confirm-draft-<?= $g->guild_id; ?>');">
						<span class="icon icon-duplicate"></span>
					</button>
					<div class="confirm-action overlay-layer draft-confirm" id="confirm-draft-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn amber" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','draft');">
								<span class="icon icon-duplicate"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
						<span class="icon icon-trash"></span>
					</button>
					<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn red" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','trash');">
								<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table></div><!-- /.br-section-body -->
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
	</div>

	<table class="br-table" id="draft-guilds-table">
		<thead>
			<tr>
				<th width="85%"><?php _e("Name","bluerabbit"); ?></th>
				<th class="text-center" width="5%"><span class="icon icon-edit"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-restore"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-trash"></span></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($guilds['draft'] as $key=>$g){ ?>
			<tr class="guild" id="guild-<?= $g->guild_id;?>">
				<td>
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td class="text-center">
					<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green"><span class="icon icon-edit"></span></a>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-blue" onClick="showOverlay('#confirm-publish-<?= $g->guild_id; ?>');">
						<span class="icon icon-restore"></span>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-publish-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn cyan" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','publish');">
								<span class="icon icon-restore"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
						<span class="icon icon-trash"></span>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-trash-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn red" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','trash');">
								<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table></div><!-- /.br-section-body -->
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
	</div>

	<table class="br-table" id="trash-guilds-table">
		<thead>
			<tr>
				<th width="80%"><?php _e("Name","bluerabbit"); ?></th>
				<th class="text-center" width="5%"><span class="icon icon-edit"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-restore"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-duplicate"></span></th>
				<th class="text-center" width="5%"><span class="icon icon-cancel"></span></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($guilds['trash'] as $key=>$g){ ?>
			<tr class="guild" id="guild-<?= $g->guild_id;?>">
				<td>
					<input type="text" class="br-input" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
					<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
				</td>
				<td class="text-center">
					<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="br-btn br-btn-green"><span class="icon icon-edit"></span></a>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-blue" onClick="showOverlay('#confirm-publish-<?= $g->guild_id; ?>');">
						<span class="icon icon-restore"></span>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-publish-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn cyan" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','publish');">
								<span class="icon icon-restore"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-amber" onClick="showOverlay('#confirm-draft-<?= $g->guild_id; ?>');">
						<span class="icon icon-duplicate"></span>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-draft-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn amber" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','draft');">
								<span class="icon icon-duplicate"></span> <?php _e("Are you sure?","bluerabbit"); ?>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
				<td class="text-center">
					<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
						<span class="icon icon-cancel"></span>
					</button>
					<div class="confirm-action overlay-layer" id="confirm-trash-<?= $g->guild_id; ?>">
						<div class="br-confirm">
							<button class="br-btn red" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','delete');">
								<span class="icon icon-cancel"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								<span class="br-confirm-label"><?php _e("You can't undo this","bluerabbit"); ?></span>
							</button>
							<button class="br-btn ghost" onClick="hideAllOverlay();">
								<span class="icon icon-cancel"></span>
							</button>
						</div>
					</div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table></div><!-- /.br-section-body -->
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
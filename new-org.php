
<?php 
	$org = $wpdb->get_row("SELECT orgs.*, players.player_display_name, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_orgs orgs 
		LEFT JOIN {$wpdb->prefix}br_players players
		ON orgs.owner_id = players.player_id WHERE orgs.org_id=$id;
	");
?>



<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 cyan-bg-50 relative">
		<div class="icon-group">
			<div class="icon-button font _24 sq-40 cyan-bg-A400 grey-900"><span class="icon icon-run"></span></div >
			<div class="icon-content">
				<div class="line font _24 grey-800">
					<?= ($org) ? __("Edit Organization","bluerabbit") : __("New Organization","bluerabbit"); ?>
				</div>
				<input type="hidden" id="the-org-id" value="<?= $org->org_id; ?>">
			</div>
		</div>
		<button class="form-ui red-bg-400 absolute top-10 right-10" onClick="unloadContent();">
			<span class="icon icon-cancel"></span> <?= __("Close","bluerabbit"); ?>
		</button>
	</div>
	<table class="table w-full" cellpadding="0">
		<thead>
			<tr class="font _12 grey-600">
				<td class="text-right w-150"><?= __('Setting','bluerabbit'); ?></td>
				<td><?= __('Value','bluerabbit'); ?></td>
			</tr>
		</thead>
		<tbody class="font _16">
			<tr>
				<td class="text-right w-150"><?= __('Name','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" type="text" id="the-org-name" value="<?= $org->org_name ;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Logo','bluerabbit'); ?></td>
				<td>
					<div class="gallery">
						<?php insertGalleryItem('the-org-logo', $org->org_logo); ?>
					</div>
				</td>
			</tr>
			
			<tr>
				<td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
				<td>
					<?php $selected_color = $org->org_color ? $org->org_color : 'purple' ; ?>
					<input id="the-org-color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					
					<?php 
					$color_select_id = "#the-org-color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('About','bluerabbit'); ?></td>
				<td>
					<?php
					$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
					wp_editor( $org->org_content, 'the-org-content', $wp_editor_settings); 
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="footer-ui white-bg text-right">
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateOrg();">
			<span class="icon icon-check"></span>
			<?php 
			if($org){
				echo __('Update Organization','bluerabbit');
			}else{
				echo __('Create Organization','bluerabbit');
			} 
			?>
		</button>
	</div>
</div>
<input type="hidden" id="new-org-nonce" value="<?= wp_create_nonce('br_update_org_nonce'); ?>"/>

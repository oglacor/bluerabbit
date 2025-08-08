
<?php 
	global $wpdb;
	if($adventure->adventure_id){
		$sponsor = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_sponsors WHERE adventure_id=$adventure->adventure_id AND sponsor_id=$id");
	}else{
		$sponsor = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_sponsors WHERE sponsor_id=$id");
	}
?>



<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 cyan-bg-50 relative">
		<div class="icon-group">
			<div class="icon-button font _24 sq-40 cyan-bg-A400 grey-900"><span class="icon icon-run"></span></div >
			<div class="icon-content">
				<div class="line font _24 grey-800">
					<?= ($adventure && $sponsor) ? __("Edit Sponsor","bluerabbit") : __("New Sponsor","bluerabbit"); ?>
				</div>
				<input type="hidden" id="the-sponsor-id" value="<?= $sponsor->sponsor_id; ?>">
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
					<input class="form-ui w-full" type="text" id="the-sponsor-name" value="<?= $sponsor->sponsor_name ;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('URL','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" type="text" id="the-sponsor-url" value="<?= $sponsor->sponsor_url ;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Logo','bluerabbit'); ?></td>
				<td>
					<div class="gallery">
						<?php insertGalleryItem('the-sponsor-logo', $sponsor->sponsor_logo); ?>
					</div>
				</td>
			</tr>
			
			<tr>
				<td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
				<td>
					<?php $selected_color = $sponsor->sponsor_color ? $sponsor->sponsor_color : 'purple' ; ?>
					<input id="the_sponsor_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the_sponsor_color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Level','bluerabbit'); ?></td>
				<td>
					<select class="form-ui w-full" id="the-sponsor-level">
						<option value="1" <?= $sponsor->sponsor_level <= 1 ? 'selected' : ''; ?>>1</option>
						<option value="2" <?= $sponsor->sponsor_level == 2 ? 'selected' : ''; ?>>2</option>
						<option value="3" <?= $sponsor->sponsor_level == 3 ? 'selected' : ''; ?>>3</option>
						<option value="4" <?= $sponsor->sponsor_level == 4 ? 'selected' : ''; ?>>4</option>
						<option value="5" <?= $sponsor->sponsor_level == 5 ? 'selected' : ''; ?>>5</option>
						<option value="6" <?= $sponsor->sponsor_level == 6 ? 'selected' : ''; ?>>6</option>
						<option value="7" <?= $sponsor->sponsor_level == 7 ? 'selected' : ''; ?>>7</option>
						<option value="8" <?= $sponsor->sponsor_level == 8 ? 'selected' : ''; ?>>8</option>
						<option value="9" <?= $sponsor->sponsor_level == 9 ? 'selected' : ''; ?>>9</option>
						<option value="10" <?= $sponsor->sponsor_level == 10 ? 'selected' : ''; ?>>10</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Image','bluerabbit'); ?></td>
				<td>
					<div class="gallery">
						<?php insertGalleryItem('the-sponsor-image', $sponsor->sponsor_image); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('About','bluerabbit'); ?></td>
				<td>
					<?php
					$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
					wp_editor( $sponsor->sponsor_about, 'the-sponsor-about', $wp_editor_settings); 
					?>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Twitter','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" type="text" id="the-sponsor-twitter" value="<?= $sponsor->sponsor_twitter ;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('LinkedIn','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" type="text" id="the-sponsor-linkedin" value="<?= $sponsor->sponsor_linkedin ;?>">
				</td>
			</tr>
		</tbody>
	</table>
	<div class="footer-ui grey-bg-800 text-right">
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateSponsor();">
			<span class="icon icon-check"></span>
			<?php 
			if($sponsor){
				echo __('Update Sponsor','bluerabbit');
			}else{
				echo __('Create Sponsor','bluerabbit');
			} 
			?>
		</button>
	</div>
</div>
<input type="hidden" id="new-sponsor-nonce" value="<?= wp_create_nonce('br_update_sponsor_nonce'); ?>"/>

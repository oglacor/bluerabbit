<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

if($adventure && $isGM){
	$blockerID = isset($_GET['blockerID']) ? $_GET['blockerID'] : NULL;
	if(isset($blockerID)){
		$blocker = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."br_blockers WHERE blocker_id=$blockerID");
		$selected_players = $wpdb->get_col("SELECT player_id FROM ".$wpdb->prefix."br_player_blocker WHERE blocker_id=$blockerID");
	}else{
		$selected_players = array();
	}
	?>

<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 red-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-lock"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800">
					<?= ($adventure && isset($blocker)) ? __("Edit Blocker","bluerabbit") : __("New Blocker","bluerabbit"); ?>
				</span>
				<input type="hidden" id="the_blocker_id" value="<?= isset($blocker) ? $blocker->blocker_id :  ""; ?>">
			</span>
		</span>
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
				<td class="text-right w-150"><?= __('Blocker Cost','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="red-bg-A700 font w900"><span class="icon icon-bloo"></span></label>
						<input class="form-ui font _30" type="number" value="<?= isset($blocker->blocker_cost) ? $blocker->blocker_cost : ""; ?>" id="the_blocker_cost">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150">
					<?= __('Date','bluerabbit'); ?>
				</td>
				<td>
					<div class="input-group w-full">
						<?php 
						if(isset($blocker)){
							$blocker_date = date('D, F jS, Y', strtotime($blocker->blocker_date)); 
						}else{
							$blocker_date = date('D, F jS, Y');
						}

						?>
						<label class="amber-bg-400"><span class="icon icon-calendar"></span><?php _e("calendar","bluerabbit"); ?> </label>
						<input class="form-ui font _30 w-full" disabled type="datetime" value="<?php echo $blocker_date; ?>">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150">
					<?= __('Reason','bluerabbit'); ?>
					<br>
					<span class="font grey-400 font _12"><?php _e("Add evidence if possible","bluerabbit"); ?></span>
				</td>
				<td>
					<?php 
					$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
					if(isset($blocker->blocker_description)){
						wp_editor( $blocker->blocker_description, 'the_blocker_description',$wp_editor_settings); 	
					}else{
						wp_editor( "", 'the_blocker_description',$wp_editor_settings); 	
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150">
					<?= __('Players','bluerabbit'); ?>
				</td>
				<td>
					<?php 
						$player_select_title = __("Fined Players","blueabbit");
						$player_select_desc = __("Who must pay their debt in order to progress","blueabbit");
						include (TEMPLATEPATH . '/player-select-blocker.php'); 
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="w-full padding-5 grey-bg-100 text-right">
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_update_blocker_nonce'); ?>"/>
		<a class="form-ui red-bg-400 pull-left" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id; ?>">
			<span class="icon icon-xs icon-cancel"></span><?= __('Cancel','bluerabbit'); ?><br>
		</a>

		<div class="input-group inline-table"> 
			<label class="purple-bg-400 font condensed w900 uppercase white-color"><?= __('Status',"bluerabbit"); ?></label>
			<select id="the_blocker_status" class="form-ui">
				<option value="publish" <?php if(!isset($blocker->blocker_status) || $blocker->blocker_status == 'publish'){ echo 'selected'; }?>><?php _e('Publish','bluerabbit'); ?></option>
				<option value="draft" <?php if(isset($blocker->blocker_status) && $blocker->blocker_status == 'draft'){ echo 'selected'; }?>><?php _e('Draft','bluerabbit'); ?></option>
				<option value="trash" <?php if(isset($blocker->blocker_status) && $blocker->blocker_status == 'trash'){ echo 'selected'; }?>><?php _e('Trash','bluerabbit'); ?></option>
			</select>
		</div>
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateBlocker();">
			<span class="icon icon-check"></span>
			<?= ($adventure && isset($blocker)) ?  __('Update Blocker','bluerabbit') : __('Create Blocker','bluerabbit'); ?>
		</button>
	</div>
</div>


	<?php //wp_enqueue_media();?>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

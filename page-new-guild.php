<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

if(isset($adventure) && $isGM){
	$guild_id = isset($_GET['guild_id']) ? $_GET['guild_id'] : NULL ;
	$selected_players = array();
	if($guild_id !== NULL){
		$g = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."br_guilds WHERE guild_id=$guild_id");
	}
	?>

<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 purple-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40 green-bg-400"><span class="icon icon-guild"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800">
					<?= ($adventure && isset($g)) ? __("Edit Guild","bluerabbit") : __("New Guild","bluerabbit"); ?>
				</span>
				<input type="hidden" id="the_guild_id" value="<?= isset($g) ? $g->guild_id : NULL; ?>">
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
				<td class="text-right w-150"><?= __('Guild Name','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<label class="green-bg-800 font w900"><span class="icon icon-guild"></span></label>
						<input class="form-ui font _30 w-full" placeholder="<?php _e("Guild Name","bluerabbit"); ?>" maxlength="255" type="text" value="<?= isset($g) ? $g->guild_name : NULL; ?>" id="the_guild_name">
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right v-top">
					<span class="font _16 block"><?= __("Guild Logo","bluerabbit");?></span>
					<span class="font _12 block red-500">
						<?= __("Required","bluerabbit"); ?>
					</span>
				</td>
				<td>
					<div class="gallery">
						<div class="gallery-item setting">
							<div class="background" style="background-image: url(<?= isset($g) ?  $g->guild_logo : NULL ; ?>);" onClick="showWPUpload('the_guild_logo');" id="the_guild_logo_thumb"></div>
							<div class="gallery-item-options relative">
								<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_guild_logo');"><span class="icon icon-image"></span></button>
								<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_guild_logo');"> <span class="icon icon-trash"></span> </button>
								<input type="hidden" id="the_guild_logo" value="<?= isset($g) ?  $g->guild_logo : NULL ; ?>"/>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
				<td>
					<div class="highlight padding-10 grey-bg-200" id="tutorial-color-select">
						<?php $selected_color = isset($g) ? $g->guild_color : NULL; ?>
						<input id="the_guild_color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the_guild_color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Assign to players as they login','bluerabbit'); ?></td>
				<td>
						<select id="the_guild_assign_on_login" class="form-ui w-full">
							<option value="0" <?php if(!isset($g) || !$g->assign_on_login){ echo 'selected'; }?>><?php _e('No','bluerabbit'); ?></option>
							<option value="1" <?php if(isset($g) && $g->assign_on_login){ echo 'selected'; }?>><?php _e('Yes','bluerabbit'); ?></option>
						</select>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Group','bluerabbit'); ?></td>
				<td>
					<input class="form-ui  w-full" placeholder="<?php _e("Guild Group","bluerabbit"); ?>" maxlength="50" type="text" value="<?= isset($g) ? $g->guild_group : NULL; ?>" id="the_guild_group">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Capacity','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" placeholder="<?php _e("Zero for no limit","bluerabbit"); ?>" type="number" value="<?= isset($g) ? $g->guild_capacity : NULL; ?>" id="the_guild_capacity">
				</td>
			</tr>
			<?php if(isset($g)){ ?>
			<tr>
				<td class="text-right w-150"><?= __('Enrollment Link','bluerabbit'); ?></td>
				<td>
					<input type="text" readonly class="form-ui w-full" value="<?php echo get_bloginfo('url')."/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?>">
				</td>
			</tr>
			<?php } ?>

<?php 

$players = $wpdb->get_results("
SELECT a.*,b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_picture, p_guild.guild_id FROM {$wpdb->prefix}br_player_adventure a
LEFT JOIN {$wpdb->prefix}br_players b
ON a.player_id = b.player_id
LEFT JOIN {$wpdb->prefix}br_player_guild p_guild
ON b.player_id = p_guild.player_id AND p_guild.guild_id = $g->guild_id
WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' LIMIT 1000
"); 
?>
			<tr>
				<td class="text-right w-150"><?= __('Player in guild','bluerabbit'); ?></td>
				<td>
					<?php if(isset($g)){ ?>
						<table class="table compact">
							<thead>
								<tr>
									<td><?php _e("ID","bluerabbit"); ?></td>
									<td><?php _e("Name","bluerabbit"); ?></td>
									<td><?php _e("Email","bluerabbit"); ?></td>
									<td><?php _e("Actions","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach($players as $play){ ?>
									<tr>
										<td><?= $play->player_id; ?></td>
										<td><?= $play->player_first." ".$play->player_last; ?></td>
										<td><?= $play->player_email; ?></td>
										<td id="player-guild-list-<?=$play->player_id;?>" <?php if($play->guild_id == $g->guild_id){ ?>class="active"<?php } ?>>
											
												<button class="active-content form-ui red-bg-400 white-color" onClick="triggerGuild(<?php echo "$g->guild_id, $play->player_id"; ?>);">
													<?= __("Remove","bluerabbit"); ?>
												</button>
												<button class="inactive-content form-ui blue-bg-400 white-color" onClick="triggerGuild(<?php echo "$g->guild_id, $play->player_id"; ?>);">
													<?= __("Assign","bluerabbit"); ?>
												</button>
											
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } else{ ?>
						<h3>-<?= __("Save the Guild first","bluerabbit"); ?>-</h3>
					<?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="w-full text-right padding-10 margin-5 grey-bg-200">
		<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('br_update_guild_nonce'); ?>"/>
		
		<a class="form-ui red-bg-400 pull-left" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=".$adventure_id; ?>">
			<span class="icon icon-xs icon-cancel"></span><?php _e('Cancel','bluerabbit'); ?><br>
		</a>
		<div class="input-group inline-table">
			<label class="orange-bg-400"><?= __('Status','bluerabbit'); ?></label>
			<select id="the_guild_status" class="form-ui">
				<option value="publish" <?php if(!isset($g) || $g->guild_status == 'publish'){ echo 'selected'; }?>><?php _e('Publish','bluerabbit'); ?></option>
				<option value="draft" <?php if(isset($g) && $g->guild_status == 'draft'){ echo 'selected'; }?>><?php _e('Draft','bluerabbit'); ?></option>
				<option value="trash" <?php if(isset($g) && $g->guild_status == 'trash'){ echo 'selected'; }?>><?php _e('Trash','bluerabbit'); ?></option>
			</select>
		</div>
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateGuild();">
			<span class="icon icon-guild"></span>
			<?= ($adventure && isset($g)) ? __("Edit Guild","bluerabbit") : __("New Guild","bluerabbit"); ?>
		</button>
	</div>
</div>
<?php //wp_enqueue_media();?>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php $schedule = BR_Session::instance()->getSessions($adventure->adventure_id);?>
<?php $achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id,'path');?>
<?php $guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_name ASC"); ?>
<?php $speakers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_speakers WHERE adventure_id=$adventure->adventure_id AND speaker_status='publish' ORDER BY speaker_first_name ASC"); ?>

<div class="br-journey-manager">
<!-- ── Published Sessions ────────────────────────────────────── -->
<div class="br-panel">
	<div class="br-header">
		<div class="br-header-left">
			<div class="br-icon"><span class="icon icon-time"></span></div>
			<h2><?php _e('Adventure Schedule','bluerabbit'); ?></h2>
		</div>
		<div class="br-header-right">
			<form id="upload_bulk_users_form" class="form br-bulk-upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">
				<label for="the_csv_file_with_sessions" class="br-form-label"><?php _e('Upload Schedule:','bluerabbit'); ?></label>
				<input type="file" name="the_csv_file_with_sessions" id="the_csv_file_with_sessions" size="20" />
				<button type="button" onClick="uploadBulkSessions();" name="upload_csv" class="br-btn cyan"><?= __("Upload file","bluerabbit"); ?></button>
			</form>
			<div class="br-search">
				<span class="icon icon-search"></span>
				<input type="text" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
			</div>
			<script>
				$('#search').keyup(function(){
					var valThis = $(this).val().toLowerCase();
					if(valThis == ""){
						$('table#table-session tbody > tr').show();
					}else{
						$('table#table-session tbody > tr').each(function(){
							var text = $(this).text().toLowerCase();
							(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
						});
					};
				});
			</script>
		</div>
	</div>

	<?php if(isset($schedule['publish'])){ ?>
		<table class="br-table" id="table-session">
			<thead>
				<tr>
					<th><?php _e("Session Title","bluerabbit"); ?></th>
					<th><?php _e("Start","bluerabbit"); ?></th>
					<th><?php _e("End","bluerabbit"); ?></th>
					<th class="path">
						<button class="br-btn ghost" onClick="toggleColumn('path');">
							<span class="icon icon-achievement"></span>
							<?= __('Path',"bluerabbit"); ?>
						</button>
					</th>
					<th><span class="icon icon-guild"></span></th>
					<th><?php _e("Speaker","bluerabbit"); ?></th>
					<th><span class="icon icon-infinite"></span></th>
					<th><span class="icon icon-edit"></span></th>
					<th><span class="icon icon-trash"></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($schedule['publish'] as $key=>$s){ ?>
					<tr id="session-<?php echo $s->session_id;?>">
						<td>
							<input type="text" class="br-input" id="the_title-session-<?php echo $s->session_id; ?>" value="<?php echo $s->session_title; ?>" onChange="setTitle(<?php echo $s->session_id; ?>,'session');">
							<input type="hidden" class="session-id" value="<?php echo $s->session_id; ?>">
						</td>
						<td>
							<?php
							if($s->session_start != "0000-00-00 00:00:00"){
								$pretty_start_date = date('Y/m/d H:i', strtotime($s->session_start));
							}else{
								$pretty_start_date = '';
							}
							?>
							<input autocomplete="off" class="br-input the_start_date datetimepicker" id="the_start_date-session-<?php echo $s->session_id; ?>" type="text" value="<?php echo $pretty_start_date; ?>" onChange="setStartDate(<?php echo $s->session_id; ?>,'session');">
						</td>
						<td>
							<?php
							if($s->session_end != "0000-00-00 00:00:00"){
								$pretty_deadline = date('Y/m/d H:i', strtotime( $s->session_end));
							}else{
								$pretty_deadline = '';
							}
							?>
							<input autocomplete="off" class="br-input the_deadline datetimepicker" id="the_deadline-session-<?php echo $s->session_id; ?>" type="text" value="<?php echo $pretty_deadline; ?>" onChange="setDeadline(<?php echo $s->session_id; ?>,'session');">
						</td>
						<td class="path">
							<select class="br-input update-achievement" onChange="setAchievement(<?php echo $s->session_id; ?>,'session')">
								<option value="0" <?php if(!$s->achievement_id){ echo 'selected'; }?>><?php _e('All players','bluerabbit'); ?></option>
								<?php if($achievements['publish']){ ?>
									<?php foreach($achievements['publish'] as $a){ ?>
									<option value="<?php echo $a->achievement_id;?>" <?php if($s->achievement_id == $a->achievement_id){ echo 'selected'; }?>>
										<?php echo $a->achievement_name; ?>
									</option>
									<?php } ?>
								<?php } ?>
							</select>
						</td>
						<td>
							<select class="br-input update-guild" onChange="setGuild(<?php echo $s->session_id; ?>,'session')">
								<option value="0" <?php if(!$session->guild_id){ echo 'selected'; }?>><?php _e('All guilds','bluerabbit'); ?></option>
								<?php if($guilds){ ?>
									<?php foreach($guilds as $t){ ?>
										<option value="<?php echo $t->guild_id;?>" <?php if($s->guild_id == $t->guild_id){ echo 'selected'; }?>><?php echo $t->guild_name; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</td>
						<td>
							<select id="speaker-<?=$s->session_id;?>" class="br-input" onChange="setSpeaker(<?=$s->session_id;?>);">
								<option value="0" <?= !$s->speaker_id ? 'selected':'';?>>-<?= __("None","bluerabbit"); ?>-</option>
								<?php foreach($speakers as $speak){ ?>
									<option value="<?= $speak->speaker_id;?>" <?= $s->speaker_id==$speak->speaker_id ? 'selected':'';?>><?= "$speak->speaker_first_name $speak->speaker_last_name"; ?></option>
								<?php } ?>
							</select>
						</td>
						<td>
							<div class="br-actions">
								<button class="br-action-link duplicate" onClick="showOverlay('#confirm-duplicate-<?php echo $s->session_id; ?>');">
									<span class="icon icon-infinite"></span>
								</button>
								<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?php echo $s->session_id; ?>">
									<button class="br-btn amber" onClick="duplicateRow(<?php echo $s->session_id; ?>);">
										<span class="icon icon-infinite"></span>
										<?php _e("Duplicate?","bluerabbit"); ?>
									</button>
									<button class="br-btn ghost" onClick="hideAllOverlay();">
										<span class="icon icon-cancel"></span>
									</button>
								</div>
							</div>
						</td>
						<td>
							<a href="<?php echo get_bloginfo('url')."/new-session/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>" class="br-action-link edit">
								<span class="icon icon-edit"></span>
							</a>
						</td>
						<td>
							<div class="br-actions">
								<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?php echo $s->session_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $s->session_id; ?>">
									<button class="br-btn red" onClick="confirmStatus(<?php echo $s->session_id; ?>,'session','trash');">
										<span class="icon icon-trash"></span>
										<?php _e("Are you sure?","bluerabbit"); ?>
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
	<?php }else{ ?>
		<div class="br-empty">
			<span class="icon icon-cancel"></span>
			<h3><?php _e("No sessions found","bluerabbit"); ?></h3>
		</div>
	<?php } ?>
</div>

<!-- ── Trashed Sessions ──────────────────────────────────────── -->
<?php if(isset($schedule['trash'])){ ?>
<div class="br-section">
	<div class="br-section-header">
		<h3><span class="icon icon-trash"></span> <?php _e('Trashed Sessions','bluerabbit'); ?></h3>
		<div class="br-search">
			<span class="icon icon-search"></span>
			<input type="text" id="search-trashed" placeholder="<?php _e("Search","bluerabbit"); ?>">
		</div>
		<script>
			$('#search-trashed').keyup(function(){
				var valThis = $(this).val().toLowerCase();
				if(valThis == ""){
					$('table#table-trashed tbody > tr').show();
				}else{
					$('table#table-trashed tbody > tr').each(function(){
						var text = $(this).text().toLowerCase();
						(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
					});
				};
			});
		</script>
	</div>
	<div class="br-section-body">
		<table class="br-table" id="table-trashed">
			<thead>
				<tr>
					<th><?php _e("Title","bluerabbit"); ?></th>
					<th><?php _e("Start","bluerabbit"); ?></th>
					<th><?php _e("End","bluerabbit"); ?></th>
					<th><?php _e("Speaker","bluerabbit"); ?></th>
					<th><?php _e("Status","bluerabbit"); ?></th>
					<th><?php _e("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($schedule['trash'] as $key=>$s){ ?>
					<tr id="session-trash-<?php echo $s->session_id;?>">
						<td>
							<a href="<?php echo get_bloginfo('url')."/session/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>">
								<strong><?php echo "$s->session_title"; ?></strong>
								<input type="hidden" class="session-id" value="<?php echo $s->session_id; ?>">
							</a>
						</td>
						<td><?php echo $s->session_start; ?></td>
						<td><?php echo $s->session_end; ?></td>
						<td><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></td>
						<td><span class="br-badge br-badge-amber"><?php echo "$s->session_status"; ?></span></td>
						<td>
							<div class="br-actions">
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>" class="br-action-link edit">
									<span class="icon icon-edit"></span>
								</a>
								<button class="br-action-link trash" onClick="showOverlay('#confirm-option-<?php echo $s->session_id; ?>');">
									<span class="icon icon-delete"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->session_id; ?>">
									<button class="br-btn red" onClick="confirmStatus(<?php echo $s->session_id; ?>,'session','delete');">
										<span class="icon icon-delete"></span>
										<?php _e("Are you sure?","bluerabbit"); ?>
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
</div>
<?php }else{ ?>
	<div class="br-empty">
		<span class="icon icon-trash"></span>
		<h3><?php _e("No sessions found in trash","bluerabbit"); ?></h3>
	</div>
<?php } ?>
<input type="hidden" id="row_type" value="session"/>

</div><!-- /.br-journey-manager -->
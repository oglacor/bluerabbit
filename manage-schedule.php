<?php $schedule = getSessions($adventure->adventure_id);?>
<?php $achievements = getAchievements($adventure->adventure_id,'path');?>
<?php $guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_name ASC"); ?>
<?php $speakers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_speakers WHERE adventure_id=$adventure->adventure_id AND speaker_status='publish' ORDER BY speaker_first_name ASC"); ?>
		<div class="highlight padding-10 indigo-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40  indigo-bg-300"><span class="icon icon-time"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800"><?php _e('Adventure Schedule','bluerabbit'); ?></span>
				</span>
			</span>
			<div class="highlight-cell pull-right padding-10">
				<div class="search sticky">
					<div class="input-group pull-right">
						<div class="form-ui font _14">
							<form id="upload_bulk_users_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">
								<table>
									<tbody>
										<tr>
											<td class="w-200">
												<label for="the_csv_file_with_sessions" class="">Upload Schedule:</label>
												<input type="file" name="the_csv_file_with_sessions" id="the_csv_file_with_sessions" size="20" />
											</td>
											<td class="w-100">
												<button type="button" onClick="uploadBulkSessions();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
											</td>
										</tr>
									</tbody>
								</table>
							</form>
						</div>
					</div>
					<div class="input-group">
						<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
						<label>
							<span class="icon icon-search"></span>
						</label>
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
			</div>
		</div>
	<?php if(isset($schedule['publish'])){ ?>
		<div class="content">
			<table class="table compact" id="table-session">
				<thead>
					<tr>
						<td class=""><strong><?php _e("Session Title","bluerabbit"); ?></strong></td>
						<td class=""><?php _e("Start","bluerabbit"); ?></td>
						<td class=""><?php _e("End","bluerabbit"); ?></td>
						<td class="path">
							<button class="form-ui" onClick="toggleColumn('path');">
								<span class="icon icon-achievement"></span>
								<?= __('Path',"bluerabbit"); ?>
							</button>
						</td>
						<td class=""><span class="icon icon-guild"></span></td>
						<td class=""><?php _e("Speaker","bluerabbit"); ?></td>
						<td class=""><span class="icon icon-infinite"></span></td>
						<td class=""><span class="icon icon-edit"></span></td>
						<td class=""><span class="icon icon-trash"></span></td>
					</tr>
				</thead>
				<tbody class="">
					<?php foreach($schedule['publish'] as $key=>$s){ ?>
						<tr id="session-<?php echo $s->session_id;?>">
							<td class="">
								<input type="text" class="form-ui row-title" id="the_title-session-<?php echo $s->session_id; ?>" value="<?php echo $s->session_title; ?>" onChange="setTitle(<?php echo $s->session_id; ?>,'session');">
								<input type="hidden" class="session-id" value="<?php echo $s->session_id; ?>">
							</td>
							<td class="cyan-bg-50">
								<div class="input-group">
									<label>
										<span class="icon icon-calendar "></span>
									</label>
									<?php
									if($s->session_start != "0000-00-00 00:00:00"){ 
										$pretty_start_date = date('Y/m/d H:i', strtotime($s->session_start));
									}else{
										$pretty_start_date = '';
									}
									?>
									<input autocomplete="off" class="form-ui the_start_date text-center font w600 datetimepicker" autocomplete="off"  id="the_start_date-session-<?php echo $s->session_id; ?>" type="text" value="<?php echo $pretty_start_date; ?>" onChange="setStartDate(<?php echo $s->session_id; ?>,'session');" >
								</div>
							</td>
							<td class="red-bg-50">
								<div class="input-group">
									<label>
										<span class="icon icon-deadline"></span>
									</label>
									<?php
									if($s->session_end != "0000-00-00 00:00:00"){ 
										$pretty_deadline = date('Y/m/d H:i', strtotime( $s->session_end));
									}else{
										$pretty_deadline = '';
									}
									?>
									<input autocomplete="off" class="form-ui the_deadline text-center font w600 datetimepicker" autocomplete="off"  id="the_deadline-session-<?php echo $s->session_id; ?>" type="text" value="<?php echo $pretty_deadline; ?>" onChange="setDeadline(<?php echo $s->session_id; ?>,'session');" >
								</div>
							</td>
							<td class="path">
								<select class="form-ui update-achievement" onChange="setAchievement(<?php echo $s->session_id; ?>,'session')">
									<option value="0"  <?php if(!$s->achievement_id){ echo 'selected'; }?>><?php _e('All players','bluerabbit'); ?></option>
									<?php if($achievements['publish']){ ?>
										<?php foreach($achievements['publish'] as $a){ ?>
										<option value="<?php echo $a->achievement_id;?>" <?php if($s->achievement_id == $a->achievement_id){ echo 'selected'; }?>  class="font _14 <?php echo $a->achievement_color; ?>-bg-100" >
											<?php echo $a->achievement_name; ?>
										</option>
										<?php } ?>
									<?php } ?>
								</select>
							</td>
							<td>
								<select class="form-ui update-guild" onChange="setGuild(<?php echo $s->session_id; ?>,'session')">
									<option value="0" <?php if(!$session->guild_id){ echo 'selected'; }?>><?php _e('All guilds','bluerabbit'); ?></option>
									<?php if($guilds){ ?>
										<?php foreach($guilds as $t){ ?>
											<option value="<?php echo $t->guild_id;?>" class="font _14 <?php echo $t->guild_color; ?>-bg-100" <?php if($s->guild_id == $t->guild_id){ echo 'selected'; }?>><?php echo $t->guild_name; ?></option>
										<?php } ?>
									<?php } ?>
								</select>
							</td>
							<td class="">
								<select id="speaker-<?=$s->session_id;?>" class="form-ui" onChange="setSpeaker(<?=$s->session_id;?>);">
									<option value="0" <?= !$s->speaker_id ? 'selected':'';?>>-<?= __("None","bluerabbit"); ?>-</option>
									
									<?php foreach($speakers as $speak){ ?>
										<option value="<?= $speak->speaker_id;?>" <?= $s->speaker_id==$speak->speaker_id ? 'selected':'';?>><?= "$speak->speaker_first_name $speak->speaker_last_name"; ?></option>
									<?php } ?>
									
								</select>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  icon-sm amber-bg-200 grey-700 duplicate-button" onClick="showOverlay('#confirm-duplicate-<?php echo $s->session_id; ?>');">
									<span class="icon icon-infinite"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Duplicate","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?php echo $s->session_id; ?>">
									<button class="form-ui white-bg duplicate-confirm-button" onClick="duplicateRow(<?php echo $s->session_id; ?>);">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  icon-sm amber-bg-A400 icon-sm">
												<span class="icon icon-trash white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line amber-400 font _18 w900"><?php _e("Duplicate?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="">
								<a href="<?php echo get_bloginfo('url')."/new-session/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm edit-button">
									<span class="icon icon-edit"></span>
								</a>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  icon-sm  red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?php echo $s->session_id; ?>');">
									<span class="icon icon-trash"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $s->session_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $s->session_id; ?>,'session','trash');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
												<span class="icon icon-trash white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php }else{ ?> 
		<div class="highlight padding-10 red-bg-50">
			<span class="icon-group text-center">
				<span class="icon-content">
					<span class="icon icon-cancel"></span> <?php _e("No sessions found","bluerabbit"); ?>
				</span>
			</span>
		</div>
	<?php } ?>
	<?php if(isset($schedule['trash'])){ ?>
		<div class="highlight padding-10 orange-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-trash"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800"><?php _e('Trashed Sessions','bluerabbit'); ?></span>
				</span>
			</span>
			<div class="highlight-cell pull-right padding-10">
				<div class="search sticky">
					<div class="input-group">
						<input type="text" class="form-ui" id="search-trashed" placeholder="<?php _e("Search","bluerabbit"); ?>">
						<label>
							<span class="icon icon-search"></span>
						</label>
						<script>
							$('#search-trashed').keyup(function(){
								var valThis = $(this).val().toLowerCase();
								if(valThis == ""){
									$('table#trashed-trashed tbody > tr').show();           
								}else{
									$('table#trashed-trashed tbody > tr').each(function(){
										var text = $(this).text().toLowerCase();
										(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
									});
								};
							});
						</script>				
					</div>
				</div>
			</div>
		</div>
		<div class="content">
			<table class="table compact " id="table-trashed">
				<thead>
					<tr>
						<td class=""><strong><?php _e("Title","bluerabbit"); ?></strong></td>
						<td class=""><?php _e("Start","bluerabbit"); ?></td>
						<td class=""><?php _e("End","bluerabbit"); ?></td>
						<td class=""><?php _e("Speaker","bluerabbit"); ?></td>
						<td class=""><?php _e("Status","bluerabbit"); ?></td>
						<td class=""><?php _e("Actions","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody class="">
					<?php foreach($schedule['trash'] as $key=>$s){ ?>
						<tr id="speaker-<?php echo $s->speaker_id;?> purple-bg-50">
							<td class="">
								<a href="<?php echo get_bloginfo('url')."/session/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>">
									<strong><?php echo "$s->session_title"; ?></strong>
									<input type="hidden" class="session-id" value="<?php echo $s->session_id; ?>">
								</a>
							</td>
							<td class=""><?php echo $s->session_start; ?></td>
							<td class=""><?php echo $s->session_end; ?></td>
							<td class=""><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></td>
							<td class=""><?php echo "$s->session_status"; ?></td>
							<td class="">
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&session_id=$s->session_id";?>" class="icon-button font _24 sq-40  green-bg-400"><span class="icon icon-edit"></span></a>
								<button class="icon-button font _24 sq-40  red-bg-A400 white-color" onClick="showOverlay('#confirm-option-<?php echo $s->session_id; ?>');">
									<span class="icon icon-delete"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Delete Forever?","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->session_id; ?>">
									<button class="form-ui grey-bg-800" onClick="confirmStatus(<?php echo $s->session_id; ?>,'session','delete');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
												<span class="icon icon-delete white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
												<span class="line amber-500 font _14 w300"><?php _e("You can't undo this","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php }else{ ?> 
		<div class="highlight padding-10 red-bg-50">
			<span class="icon-group text-center">
				<span class="icon-content">
					<span class="icon icon-cancel"></span> <?php _e("No sessions found in trash","bluerabbit"); ?>
				</span>
			</span>
		</div>
	<?php } ?>
<input type="hidden" id="row_type" value="session"/>

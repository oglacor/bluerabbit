<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : NULL ;
if(isset($session_id)){
	$session = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_sessions WHERE session_id=$session_id"); 
}
$quests = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_quests WHERE adventure_id=$adventure_id AND (quest_type='quest' OR quest_type='challenge') AND quest_status='publish'");
$speakers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_speakers WHERE adventure_id=$adventure_id AND speaker_status='publish' ORDER BY speaker_first_name");
$paths = getAchievements($adventure->adventure_id, 'path|rank');

$guilds = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_guilds WHERE adventure_id=$adventure->adventure_id AND guild_status='publish' ORDER BY guild_name ASC");
?>





<div class="dashboard">
	<div class="dashboard-sidebar grey-bg-800 sticky padding-10">
		<div class="tabs-buttons sticky top-50" id="main-tabs-buttons">
			<ul class="margin-0 padding-0">
				
				<li class="block text-center padding-5">
					<?php if(isset($paths['publish'])){ ?>
						<select id="the_achievement_id" class="form-ui">
							<option value="0"  <?php if(!isset($session->achievement_id)){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
							<?php foreach($paths['publish'] as $a){ ?>
								<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if(isset($session) && $session->achievement_id == $a->achievement_id){ echo 'selected'; }?> ><?php echo $a->achievement_name; ?></option>
							<?php } ?>
						</select>
					<?php }else{ ?>
						<input id="the_achievement_id" type="hidden" value="0">
						<input class="form-ui" value="<?php _e('All Paths','bluerabbit'); ?>" disabled>
					<?php } ?>
				</li>
				
				<li class="block text-center">
					
					<select id="the_session_status" class="form-ui">
						<option value="publish"><?php _e("Publish", "bluerabbit"); ?></option>
						<option value="draft"><?php _e("Draft", "bluerabbit"); ?></option>
						<option value="trash"><?php _e("Trash", "bluerabbit"); ?></option>
					</select>
					
					<input type="hidden" id="session_nonce" value='<?php echo wp_create_nonce('br_session_nonce') ?>'>
					<button id="submit-button" type="button" class="form-ui green-bg-400 w-full" onClick="updateSession();">
						<span class="icon icon-check"></span>
						<?= ($adventure && $session) ? __("Update Session","bluerabbit") : __("Create Session","bluerabbit"); ?>
					</button>

				</li>
				<li class="block text-center">
					<a class="form-ui red-bg-400 font _14" href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
						<span class="icon icon-xs icon-cancel"></span><?php _e('Cancel','bluerabbit'); ?><br>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="dashboard-content white-bg">
		<div class="w-full padding-10 brown-bg-50 sticky top-50 layer overlay relative">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40 brown-bg-400"><span class="icon icon-calendar"></span></span>
				<span class="icon-content">
					<h1><?php if($adventure && isset($session)){ ?>
						<?php _e('Edit Session','bluerabbit'); ?>
						<input type="hidden" id="the_session_id" value="<?= isset($session) ? $session->session_id : ""; ?>">
					<?php }else{ ?>
						<?php _e('New Session','bluerabbit'); ?>
					<?php } ?></h1>
				</span>
			</span>
			
		</div>
		<div class="tabs" id="main-tabs">
			<div class="tab max-w-900 padding-10 active" id="speaker-content">
			<table class="table w-full" cellpadding="0">
					<thead>
						<tr class="font _12 grey-600">
							<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
							<td><?php _e('Value','bluerabbit'); ?></td>
						</tr>
					</thead>
					<tbody class="font _16">
						<tr>
							<td class="text-right w-150"><?php _e('Title','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" id="the_session_title" type="text" value="<?= isset($session) ? $session->session_title : ""; ?>" >
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Session Description','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php 
										if($roles[0]=="administrator"){
											$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
										}else{
											$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
										}
										if(isset($session->session_description)) { 
											wp_editor( $session->session_description, 'the_session_description',$wp_editor_settings); 	
										}else{
											wp_editor("", 'the_session_description',$wp_editor_settings); 	
										}
									?>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Session Start','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php
									if(isset($session->session_start) && $session->session_start != "0000-00-00 00:00:00"){ 
										$pretty_start_date = date('Y/m/d H:i', strtotime($session->session_start));
									}else{
										$pretty_start_date = '';
									}
									?>
									<div class="datepicker-group">
										<input class="form-ui w-full  text-center font w600 datetimepicker"  autocomplete="off" id="the_session_start" type="text" value="<?php echo $pretty_start_date; ?>" >
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Session End','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php
									if(isset($session->session_end) && $session->session_end != "0000-00-00 00:00:00"){ 
										$pretty_deadline = date('Y/m/d H:i', strtotime($session->session_end));
									}else{
										$pretty_deadline = '';
									}
									?>
									<div class="datepicker-group">
										<input class="form-ui text-center w-full font w600 datetimepicker" autocomplete="off"  id="the_session_end" type="text" value="<?php echo $pretty_deadline; ?>" >
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Room Information','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui" id="the_session_room" type="text" value="<?= isset($session->session_room) ? $session->session_room : ""; ?>" >
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Attach to quest','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<select id="the_quest_id" class="form-ui">
										<option value="0"><?php _e("No Quest Attached", "bluerabbit"); ?></option>
										<?php if(isset($quests)){ ?>
											<?php foreach($quests as $q){ ?>
												<?php $l = $q->quest_type; ?>
												<?php if($q->quest_type =='quest'){
													$color = "blue-bg-50";
												}else{
													$color = "brown-bg-50";
												}
												?>
												<option <?= (isset($session->quest_id) && $session->quest_id == $q->quest_id) ? 'selected' : ''; ?> value="<?php echo $q->quest_id; ?>" class="<?php echo $color; ?> font _18"><?php echo "[{$l[0]}] $q->quest_title"; ?></option>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Speaker','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<select id="the_speaker_id" class="form-ui">
										<option value="0"><?php _e("No speaker linked", "bluerabbit"); ?></option>
										<?php if(isset($speakers)){ ?>
											<?php foreach($speakers as $s){ ?>
												<option <?php echo ($session->speaker_id==$s->speaker_id) ? 'selected' : ''; ?>   value="<?php echo $s->speaker_id; ?>" class="font _18"><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></option>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
							</td>
						</tr>
						<?php if($guilds){ ?>
						<tr>
							<td class="text-right w-150"><?php _e('Guild','bluerabbit'); ?></td>
							<td>
								<select id="the_guild_id" class="form-ui">
									<option value="0" <?php if(!isset($session->guild_id)){ echo 'selected'; }?>><?php _e('All guilds','bluerabbit'); ?></option>
									<?php if(isset($guilds)){ ?>
										<?php foreach($guilds as $t){ ?>
											<option value="<?php echo $t->guild_id;?>" class="font _14 <?php echo $t->guild_color; ?>-bg-100" <?php if(isset($session->guild_id) && $session->guild_id == $t->guild_id){ echo 'selected'; }?>><?php echo $t->guild_name; ?></option>
										<?php } ?>
									<?php } ?>
								</select>
							</td>
						</tr>
						<?php } ?>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

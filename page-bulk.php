<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php 


if($adventure && $isAdmin){

?>
	<div class="boxed max-w-1200">
			<div class="text-center padding-20">
				<span class="icon-group inline-table">
					<span class="icon-button font _24 sq-40  icon-lg purple-bg-400"><span class="icon icon-duplicate"></span></span>
					<span class="icon-content">
						<span class="line font _48 white-color">
							<?php _e("Bulk Create","bluerabbit"); ?>
						</span>
					</span>
				</span>
			</div>
			<div class="body-ui w-full white-bg">
				<div class="highlight padding-20 blue-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  blue-bg-400"><span class="icon icon-quest"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Achievements","bluerabbit");?></div>
						</div>
					</div>
				</div>
				<div class="content">
					<table class="table compact">
						<thead>
							<tr>
								<td><?php _e("Field","bluerabbit"); ?></td>
								<td><?php _e("Value","bluerabbit"); ?></td>
							</tr>
						</thead>
						<tbody>
							<tr class="font _30">
								<td><?= __("Achievements to create","bluerabbit"); ?></td>
								<td><input type="text" class="form-ui" id="the_achievements_bulk_numnber" type="number"></td>
							</tr>
							<tr>
								<td class="text-right w-150"><?php _e('Achievement Prefix','bluerabbit'); ?></td>
								<td>
									<div class="input-group w-full">
										<label class="purple-bg-800 font w900"><span class="icon icon-achievement"></span></label>
										<input class="form-ui font _30 w-full" type="text" value="<?= $a->achievement_name; ?>" id="the_achievement_name">
									</div>
								</td>
							</tr>
							<td class="text-right v-top">
								<span class="font _16 block"><?= __("Achievement Badge","bluerabbit");?></span>
								<span class="font _12 block red-500">
									<?php _e("Required","bluerabbit"); ?>
								</span>
							</td>
							<td>
								<div class="gallery">
									<div class="gallery-item setting">
										<div class="background" style="background-image: url(<?= $a->achievement_badge; ?>);" onClick="showWPUpload('the_achievement_badge');" id="the_achievement_badge_thumb"></div>
										<div class="gallery-item-options relative">
											<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_achievement_badge');"><span class="icon icon-image"></span></button>
											<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_achievement_badge');"> <span class="icon icon-trash"></span> </button>
											<input type="hidden" id="the_achievement_badge" value="<?php echo $a->achievement_badge; ?>"/>
										</div>
									</div>
								</div>
							</td>
							<tr class="font _18">
								<td><?= __("XP","bluerabbit"); ?></td>
								<td><input type="text" class="form-ui" id="bulk-achievements-xp" type="number"></td>
							</tr>
							<tr class="font _18">
								<td><?= __("BLOO","bluerabbit"); ?></td>
								<td><input type="text" class="form-ui" id="bulk-achievements-xp" type="number"></td>
							</tr>
							<?php if($use_encounters){ ?>
								<tr class="font _18">
									<td><?= __("EP","bluerabbit"); ?></td>
									<td><input type="text" class="form-ui" id="bulk-achievements-xp" type="number"></td>
								</tr>
							<?php } ?>
							<tr class="font _18">
								<td><?= __("Max Players","bluerabbit"); ?></td>
								<td><input type="text" class="form-ui" id="bulk-achievements-max-players" type="number"></td>
							</tr>
							<tr class="">
								<td class="text-right w-150"><?php _e('Achievement Deadline','bluerabbit'); ?></td>
								<td>
									<?php 
									if($a && $a->achievement_deadline != "0000-00-00 00:00:00"){ 
										$deadline =date('Y/m/d H:i',strtotime($a->achievement_deadline)); 
									}else{
										$deadline = '';
									} ?>
									<input class="form-ui font w600 datetimepicker grey-900 text-left"  autocomplete="off" id="the_achievement_deadline" type="text" value="<?= $deadline; ?>" >
									<input class="the_start_date" type="hidden" value="<?= date('Y/m/d H:i');?>" >
								</td>
							</tr>
							<tr>
								<td class="text-right w-150">
									<?php _e('Secret Message',"bluerabbit"); ?>
								</td>
								<td>
									<span class="icon-group">
										<span class="icon-button font _24 sq-40  indigo-bg-400">
											<span class="icon icon-warning white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line font _14 w300 grey-500"><?php _e('This message will be seen once the players earn the achievement.','bluerabbit'); ?></span>
										</span>
									</span>
									<div class="padding-5 w-full">
										<?php 
										if($roles[0]=="administrator"){
											$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
										}else{
											$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
										}
										wp_editor( $a->achievement_content, 'the_achievement_content',$wp_editor_settings); 	
										?>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="footer-ui text-center padding-10 white-color deep-purple-bg-800">
				<div class="input-group w-full relative text-center">
					<label class="amber-bg-400 relative">
						<button class="form-ui amber-bg-400 grey-900" onClick="showOverlay('#bulk-confirm');">
							<span class="icon icon-mystery"></span> <strong><?php _e('Bulk Create','bluerabbit'); ?></strong><br>
						</button>
					</label>
					<div class="overlay-layer confirm-action" id="bulk-confirm">
						<button class="form-ui red-bg-400 white-color font _30" onClick="bulkCreate();">
							<span class="icon icon-mystery"></span> <strong><?php _e('Are you sure?','bluerabbit'); ?></strong><br>
						</button>
					</div>
				</div>
				<input type="hidden" id="bulk_nonce" value="<?= wp_create_nonce('bulk_nonce'); ?>"/>
			</div>
		</div>
<?php }else { ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php
$speaker = isset($_GET['speaker_id']) ? $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id={$_GET['speaker_id']}") : NULL;
?>

<div class="dashboard">
	<div class="dashboard-sidebar grey-bg-800 sticky padding-10">
		<div class="tabs-buttons sticky top-50" id="main-tabs-buttons">
			<ul class="margin-0 padding-0">
					<li class="block text-center">
						<input type="hidden" id="speaker_nonce" value='<?php echo wp_create_nonce('br_speaker_nonce') ?>'>
						<button id="submit-button" type="button" class="form-ui green-bg-400 w-full" onClick="updateSpeaker();">
							<span class="icon icon-check"></span>
							<?= ($adventure && $speaker) ? __("Update Speaker","bluerabbit") : __("Create Speaker","bluerabbit"); ?>
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
				<span class="icon-button font _24 sq-40 brown-bg-400"><span class="icon icon-socialiser"></span></span>
				<span class="icon-content">
					<h1><?php if($adventure && isset($speaker)){ ?>
						<?php _e('Edit Speaker','bluerabbit'); ?>
						<input type="hidden" id="the_speaker_id" value="<?= isset($speaker) ? $speaker->speaker_id : ""; ?>">
					<?php }else{ ?>
						<?php _e('New Speaker','bluerabbit'); ?>
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
							<td class="text-right v-top">
								<span class="font _16 block"><?= __("Speaker Picture","bluerabbit");?></span>
								<span class="font _12 block red-500">
									<?php _e("Required","bluerabbit"); ?>
								</span>
							</td>
							<td>
								<div class="gallery">
									<div class="gallery-item setting">
										<div class="background" style="background-image: url(<?= isset($speaker) ? $speaker->speaker_picture : ""; ?>);" onClick="showWPUpload('the_speaker_picture');" id="the_speaker_picture_thumb"></div>
										<div class="gallery-item-options relative">
											<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_speaker_picture');"><span class="icon icon-image"></span></button>
											<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_speaker_picture');"> <span class="icon icon-trash"></span> </button>
											<input type="hidden" id="the_speaker_picture" value="<?= isset($speaker) ? $speaker->speaker_picture : ""; ?>"/>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('First name','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($speaker->speaker_first_name) ? $speaker->speaker_first_name : ""; ?>" id="the_speaker_first_name">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Last name','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($speaker->speaker_last_name) ? $speaker->speaker_last_name : ""; ?>" id="the_speaker_last_name">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Company','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($speaker->speaker_company) ? $speaker->speaker_company : ""; ?>" id="the_speaker_company">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Website','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($speaker->speaker_website) ? $speaker->speaker_website : ""; ?>" id="the_speaker_website">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('LinkedIn','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<input class="form-ui font _30 w-full" type="text" value="<?= isset($speaker->speaker_linkedin) ? $speaker->speaker_linkedin : ""; ?>" id="the_speaker_linkedin">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Speaker Bio','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php 
									if($roles[0]=="administrator"){
										$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
									}else{
										$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
									}
									if(isset($speaker->speaker_bio)){ 
										wp_editor( $speaker->speaker_bio, 'the_speaker_bio',$wp_editor_settings); 	
									}else{
										wp_editor("", 'the_speaker_bio',$wp_editor_settings); 	
									}
									?>
								</div>
							</td>
						</tr>
					</tbody>
				</table>

			</div>
		</div>

	</div>
</div>


<?php include (get_stylesheet_directory() . '/footer.php'); ?>

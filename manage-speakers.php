<?php $speakers = getSpeakers($adventure->adventure_id); ?>
	<div class="container">
			<div class="body-ui">
		<div class="highlight padding-10 orange-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40  orange-bg-400"><span class="icon icon-socialiser"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800"><?php _e('Adventure Speakers','bluerabbit'); ?></span>
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
												<label for="the_csv_file_with_speakers" class="">Upload Speakers:</label>
												<input type="file" name="the_csv_file_with_speakers" id="the_csv_file_with_speakers" size="20" />
											</td>
											<td class="w-100">
												<button type="button" onClick="uploadBulkSpeakers();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
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
									$('table#table-published tbody > tr').show();           
								}else{
									$('table#table-published tbody > tr').each(function(){
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
	<?php if(isset($speakers['publish'])){ ?>
		<div class="content">
			<table class="table compact" id="table-published">
				<thead>
					<tr>
						<td class=""><?php _e("Picture","bluerabbit"); ?></td>
						<td class=""><strong><?php _e("First Name","bluerabbit"); ?></strong></td>
						<td class=""><strong><?php _e("Last Name","bluerabbit"); ?></strong></td>
						<td class=""><?php _e("Company","bluerabbit"); ?></td>
						<td class=""><?php _e("Website","bluerabbit"); ?></td>
						<td class=""><?php _e("Twitter","bluerabbit"); ?></td>
						<td class=""><?php _e("LinkedIn","bluerabbit"); ?></td>
						<td class=""><?php _e("Actions","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody class="">
					<?php foreach($speakers['publish'] as $key=>$s){ ?>
						<tr id="speaker-<?php echo $s->speaker_id;?> purple-bg-50">
							<td class="">
								<input type="hidden" value="<?= $s->speaker_picture; ?>" id="the_speaker_badge-<?= $s->speaker_id; ?>">
								<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_speaker_badge-<?= $s->speaker_id; ?>','a','speaker',<?= $s->speaker_id; ?>);" id="the_speaker_badge-<?= $s->speaker_id; ?>_thumb" style="background-image: url(<?= $s->speaker_picture; ?>);">
								</button>
							</td>
							<td class="">
								<input type="hidden" class="speaker-id" id="speaker-<?php echo $s->speaker_id;?>-id" value="<?= $s->speaker_id; ?>">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-first-name" value="<?= $s->speaker_first_name; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td> 
							<td class="">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-last-name" value="<?= $s->speaker_last_name; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td>
							<td class="">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-company" value="<?= $s->speaker_company; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td>
							<td class="">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-website" value="<?= $s->speaker_website; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td>
							<td class="">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-twitter" value="<?= $s->speaker_twitter; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td>
							<td class="">
								<input type="text" class="form-ui " id="speaker-<?php echo $s->speaker_id;?>-linkedin" value="<?= $s->speaker_linkedin; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
							</td>
							<td class="">
								<a class="icon-button font _24 sq-40  green-bg-400 icon-sm" href="<?php echo get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>">
									<span class="icon icon-view">
									</span>
								</a>
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>" class="icon-button font _24 sq-40  green-bg-400"><span class="icon icon-edit"></span></a>
								<button class="icon-button font _24 sq-40  red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?php echo $s->speaker_id; ?>');">
									<span class="icon icon-trash"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->speaker_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?php echo $s->speaker_id; ?>,'speaker','trash');">
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
		<div class="highlight padding-10 orange-bg-50">
			<span class="icon-group text-center">
				<span class="icon-content">
					<span class="icon icon-cancel"></span> <?php _e("No speakers found","bluerabbit"); ?>
				</span>
			</span>
		</div>
	<?php } ?>
	<?php if(isset($speakers['trash'])){ ?>
		<div class="highlight padding-10 orange-bg-50">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-trash"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800"><?php _e('Trashed Speakers','bluerabbit'); ?></span>
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
						<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
						<td class=""><?php _e("Company","bluerabbit"); ?></td>
						<td class=""><?php _e("Website","bluerabbit"); ?></td>
						<td class=""><?php _e("Twitter","bluerabbit"); ?></td>
						<td class=""><?php _e("LinkedIn","bluerabbit"); ?></td>
						<td class=""><?php _e("Actions","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody class="">
					<?php foreach($speakers['trash'] as $key=>$s){ ?>
						<tr id="speaker-<?php echo $s->speaker_id;?> purple-bg-50">
							<td class="">
								<strong><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></strong>
								<input type="hidden" class="speaker-id" value="<?php echo $s->speaker_id; ?>">
							</td>
							<td class=""><?php echo $s->speaker_company; ?></td>
							<td class=""><a href="<?php echo $s->speaker_website; ?>" class="icon-button font _24 sq-40 " target="_blank"><span class="icon icon-language"></span></a></td>
							<td class=""><span class="icon icon-tw"></span><?php echo $s->speaker_twitter; ?></td>
							<td class=""><a href="<?php echo $s->speaker_linkedin; ?>" class="form-ui light-blue-bg-800" target="_blank"><?php _e("LinkedIn","bluerabbit"); ?></a></td>
							<td class="relative layer base">
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>" class="relative layer base icon-button font _24 sq-40 green-bg-400"><span class="icon icon-edit"></span></a>
								<button class="icon-button font _24 sq-40  red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?php echo $s->speaker_id; ?>');">
									<span class="icon icon-trash"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->speaker_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?php echo $s->speaker_id; ?>,'speaker','trash');">
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
		<div class="highlight padding-10 orange-bg-50">
			<span class="icon-group text-center">
				<span class="icon-content">
					<span class="icon icon-cancel"></span> <?php _e("No speakers found in trash","bluerabbit"); ?>
				</span>
			</span>
		</div>
	<?php } ?>
			</div> <!--CLOSE <body-ui> -->
	</div> <!--CLOSE <container> -->

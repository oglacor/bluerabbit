<?php $speakers = BR_Session::instance()->getSpeakers($adventure->adventure_id); ?>

<div class="br-journey-manager">
<!-- ── Published Speakers ────────────────────────────────────── -->
<div class="br-panel">
	<div class="br-header">
		<div class="br-header-left">
			<div class="br-icon"><span class="icon icon-socialiser"></span></div>
			<h2><?php _e('Adventure Speakers','bluerabbit'); ?></h2>
		</div>
		<div class="br-header-right">
			<form id="upload_bulk_users_form" class="form br-bulk-upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">
				<label for="the_csv_file_with_speakers" class="br-form-label"><?php _e('Upload Speakers:','bluerabbit'); ?></label>
				<input type="file" name="the_csv_file_with_speakers" id="the_csv_file_with_speakers" size="20" />
				<button type="button" onClick="uploadBulkSpeakers();" name="upload_csv" class="br-btn cyan"><?= __("Upload file","bluerabbit"); ?></button>
			</form>
			<div class="br-search">
				<span class="icon icon-search"></span>
				<input type="text" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
			</div>
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

	<?php if(isset($speakers['publish'])){ ?>
		<table class="br-table" id="table-published">
			<thead>
				<tr>
					<th><?php _e("Picture","bluerabbit"); ?></th>
					<th><?php _e("First Name","bluerabbit"); ?></th>
					<th><?php _e("Last Name","bluerabbit"); ?></th>
					<th><?php _e("Company","bluerabbit"); ?></th>
					<th><?php _e("Website","bluerabbit"); ?></th>
					<th><?php _e("Twitter","bluerabbit"); ?></th>
					<th><?php _e("LinkedIn","bluerabbit"); ?></th>
					<th><?php _e("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($speakers['publish'] as $key=>$s){ ?>
					<tr id="speaker-<?php echo $s->speaker_id;?>">
						<td>
							<input type="hidden" value="<?= $s->speaker_picture; ?>" id="the_speaker_badge-<?= $s->speaker_id; ?>">
							<button class="br-thumb" onClick="showWPUpload('the_speaker_badge-<?= $s->speaker_id; ?>','a','speaker',<?= $s->speaker_id; ?>);" id="the_speaker_badge-<?= $s->speaker_id; ?>_thumb" style="background-image: url(<?= $s->speaker_picture; ?>);">
							</button>
						</td>
						<td>
							<input type="hidden" class="speaker-id" id="speaker-<?php echo $s->speaker_id;?>-id" value="<?= $s->speaker_id; ?>">
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-first-name" value="<?= $s->speaker_first_name; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-last-name" value="<?= $s->speaker_last_name; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-company" value="<?= $s->speaker_company; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-website" value="<?= $s->speaker_website; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-twitter" value="<?= $s->speaker_twitter; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<input type="text" class="br-input" id="speaker-<?php echo $s->speaker_id;?>-linkedin" value="<?= $s->speaker_linkedin; ?>" onChange="setSpeakerData(<?= $s->speaker_id; ?>);">
						</td>
						<td>
							<div class="br-actions">
								<a class="br-action-link edit" href="<?php echo get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>">
									<span class="icon icon-view"></span>
								</a>
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>" class="br-action-link edit">
									<span class="icon icon-edit"></span>
								</a>
								<button class="br-action-link trash" onClick="showOverlay('#confirm-option-<?php echo $s->speaker_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->speaker_id; ?>">
									<button class="br-btn red" onClick="confirmStatus(<?php echo $s->speaker_id; ?>,'speaker','trash');">
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
			<span class="icon icon-socialiser"></span>
			<h3><?php _e("No speakers found","bluerabbit"); ?></h3>
		</div>
	<?php } ?>
</div>

<!-- ── Trashed Speakers ──────────────────────────────────────── -->
<?php if(isset($speakers['trash'])){ ?>
<div class="br-section">
	<div class="br-section-header">
		<h3><span class="icon icon-trash"></span> <?php _e('Trashed Speakers','bluerabbit'); ?></h3>
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
					<th><?php _e("Name","bluerabbit"); ?></th>
					<th><?php _e("Company","bluerabbit"); ?></th>
					<th><?php _e("Website","bluerabbit"); ?></th>
					<th><?php _e("Twitter","bluerabbit"); ?></th>
					<th><?php _e("LinkedIn","bluerabbit"); ?></th>
					<th><?php _e("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($speakers['trash'] as $key=>$s){ ?>
					<tr id="speaker-trash-<?php echo $s->speaker_id;?>">
						<td>
							<strong><?php echo "$s->speaker_first_name $s->speaker_last_name"; ?></strong>
							<input type="hidden" class="speaker-id" value="<?php echo $s->speaker_id; ?>">
						</td>
						<td><?php echo $s->speaker_company; ?></td>
						<td>
							<?php if($s->speaker_website){ ?>
								<a href="<?php echo $s->speaker_website; ?>" class="br-btn" target="_blank"><span class="icon icon-language"></span></a>
							<?php } ?>
						</td>
						<td><?php echo $s->speaker_twitter; ?></td>
						<td>
							<?php if($s->speaker_linkedin){ ?>
								<a href="<?php echo $s->speaker_linkedin; ?>" class="br-btn" target="_blank"><?php _e("LinkedIn","bluerabbit"); ?></a>
							<?php } ?>
						</td>
						<td>
							<div class="br-actions">
								<a href="<?php echo get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id";?>" class="br-action-link edit">
									<span class="icon icon-edit"></span>
								</a>
								<button class="br-action-link trash" onClick="showOverlay('#confirm-option-<?php echo $s->speaker_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-option-<?php echo $s->speaker_id; ?>">
									<button class="br-btn red" onClick="confirmStatus(<?php echo $s->speaker_id; ?>,'speaker','trash');">
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
	</div>
</div>
<?php }else{ ?>
	<div class="br-empty">
		<span class="icon icon-trash"></span>
		<h3><?php _e("No speakers found in trash","bluerabbit"); ?></h3>
	</div>
<?php } ?>

</div><!-- /.br-journey-manager -->
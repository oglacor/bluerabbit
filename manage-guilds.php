<?php 
	$guilds = getGuilds($adventure->adventure_id); 
?>
		<?php if(isset($guilds['publish'])){ ?>
			<div class="highlight padding-10 light-green-bg-50">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  light-green-bg-400"><span class="icon icon-guild"></span></span>
					<span class="icon-content">
						<span class="line font _24 grey-800"><?php _e('Published Guilds','bluerabbit'); ?></span>
					</span>
				</span>
				<input type="hidden" id="guild-group-nonce" value="<?php echo wp_create_nonce('guild_group_nonce'); ?>" />
				<input type="hidden" id="guild-capacity-nonce" value="<?php echo wp_create_nonce('guild_capacity_nonce'); ?>" />
				<div class="highlight-cell pull-right padding-10">
					<div class="search sticky">
						<div class="input-group">
							<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
							<label>
								<span class="icon icon-search"></span>
							</label>
							<script>
								$('#search').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('table#table-guild tbody > tr').show();           
									}else{
										$('table#table-guild tbody > tr').each(function(){
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
			<div class="content white-bg padding-10">
				<table class="table compact table-guilds" id="table-guild">
					<thead>
						<tr>
							<td class=""><strong><?php _e("Logo","bluerabbit"); ?></strong></td>
							<td class=""><strong><?php _e("Color","bluerabbit"); ?></strong></td>
							<td class=""><strong><?php _e("Name","bluerabbit"); ?></strong></td>
							<td class=""><strong><?php _e("Link","bluerabbit"); ?></strong></td>
							<td class=""><strong><?php _e("Group","bluerabbit"); ?></strong></td>
							<td class=""><strong><?php _e("Capacity","bluerabbit"); ?></strong></td>
							<td class="text-center" width="5%"><span class="icon icon-edit"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-infinite"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-duplicate"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-trash"></span></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($guilds['publish'] as $key=>$g){ ?>
						<?php if(!$g->guild_code) {
							$first_str = random_str(12,'1234567890abcdefghijkls');
							$code_string = $first_str.$current_user->ID;
							$guild_code = str_shuffle($code_string);
							$guild_code_update = $wpdb->query("UPDATE {$wpdb->prefix}br_guilds SET guild_code='$guild_code' WHERE guild_id=$g->guild_id AND adventure_id=$adventure->adventure_id");
						}
						?>
						<tr class="guild" id="guild-<?= $g->guild_id;?>">
							<td class="badge">
								<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_badge-<?= $g->guild_id; ?>">
								<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_guild_badge-<?= $g->guild_id; ?>','a','guild',<?= $g->guild_id; ?>);" id="the_guild_badge-<?= $g->guild_id; ?>_thumb" style="background-image: url(<?= $g->guild_logo; ?>);">
								</button> 
							</td>
							<td class="color relative layer base">
								<input type="hidden" value="<?= $g->guild_logo; ?>" id="the_guild_color-<?= $g->guild_id; ?>">
								<button class="icon-button font _24 sq-40 <?=$g->guild_color;?>-bg-400" id="color-trigger-guild-<?= $g->guild_id; ?>" onClick="activate('#color-select-<?=$g->guild_id;?>');"><span class="icon icon-guild"></span>
								</button> 
								<div class="color-select-popup" id="color-select-<?=$g->guild_id;?>">
									<?php
									$selected_color = $g->guild_color; 
									$object_color_id = $g->guild_id;
									$object_type='guild';
									?>
									<?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
								</div>
							</td>
							<td>
								<div class="input-group w-full">
									<input type="text" class="form-ui  w-full" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
								</div>
								<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
							</td>
							<td>
								<div class="input-group w-full">
									<input type="text" readonly class="form-ui w-full" value="<?php echo get_bloginfo('url')."/guild-enroll/?adventure_id=$adventure->adventure_id&t=$g->guild_code"; ?>">
								</div>
							</td>
							<td>
								<div class="input-group w-full">
									<input type="text" class="form-ui w-full" id="the_guild_group-<?= $g->guild_id; ?>" value="<?= $g->guild_group ?>" onChange="setGuildGroup(<?= $g->guild_id; ?>);">
								</div>
							</td>
							<td>
								<div class="input-group w-full">
									<label class="grey-bg-900 white-color font w900"><?= "$g->guild_current_capacity / "; ?></label>
									<input type="text" class="form-ui" id="the_guild_capacity-<?= $g->guild_id; ?>" value="<?= $g->guild_capacity ?>" onChange="setGuildCapacity(<?= $g->guild_id; ?>);">
								</div>
							</td>
							<td class="text-center">
								<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit  edit-button"></span></a>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm grey-bg-800 white-color duplicate-button" onClick="showOverlay('#confirm-duplicate-<?= $g->guild_id; ?>');">
									<span class="icon icon-infinite amber-500"></span>
								</button>
								<div class="confirm-action overlay-layer duplicate-confirm" id="confirm-duplicate-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg duplicate-confirm-buton" onClick="duplicateRow(<?= $g->guild_id; ?>);">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  grey-bg-800 icon-sm">
												<span class="icon icon-infinite amber-500"></span>
											</span>
											<span class="icon-content">
												<span class="line amber-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm amber-bg-400 white-color draft-button" onClick="showOverlay('#confirm-draft-<?= $g->guild_id; ?>');">
									<span class="icon icon-duplicate"></span>
								</button>
								<div class="confirm-action overlay-layer draft-confirm" id="confirm-draft-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg draft-confirm-button" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','draft');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  amber-bg-400 icon-sm">
												<span class="icon icon-duplicate white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line amber-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm red-bg-400 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','trash');">
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
			<?php echo notFound(__("No guilds found"),'light-green'); ?>
			<?php echo addNewButton(__("Add New Guild","bluerabbit"),'light-green', 'guild', $adventure->adventure_id); ?>
		<?php } ?>
		<?php if(isset($guilds['draft'])){ ?>
			<div class="highlight padding-10 amber-bg-50">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  amber-bg-400"><span class="icon icon-guild"></span></span>
					<span class="icon-content">
						<span class="line font _24 grey-800"><?php _e('Draft Guilds','bluerabbit'); ?></span>
					</span>
				</span>
				<div class="highlight-cell pull-right padding-10">
					<div class="search sticky">
						<div class="input-group">
							<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
							<label>
								<span class="icon icon-search"></span>
							</label>
							<script>
								$('#search').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('table#draft-guilds-table tbody > tr').show();           
									}else{
										$('table#draft-guilds-table tbody > tr').each(function(){
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
				<table class="table compact table-guilds" id="draft-guilds-table">
					<thead>
						<tr>
							<td class="" width="85%"><strong><?php _e("Name","bluerabbit"); ?></strong></td>
							<td class="text-center" width="5%"><span class="icon icon-edit"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-restore"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-trash"></span></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($guilds['draft'] as $key=>$g){ ?>
						<tr class="guild" id="guild-<?= $g->guild_id;?>">
							<td>
								<div class="input-group w-full">
									<input type="text" class="form-ui" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
								</div>
								<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
							</td>
							<td class="text-center">
								<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit"></span></a>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm blue-bg-A400 white-color" onClick="showOverlay('#confirm-publish-<?= $g->guild_id; ?>');">
									<span class="icon icon-restore"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-publish-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','publish');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  blue-bg-A400 icon-sm">
												<span class="icon icon-restore white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line amber-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm red-bg-400 white-color" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-trash-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','trash');">
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
			<?php echo notFound(__("No drafts found"),'amber'); ?>
		<?php } ?>
		<?php if(isset($guilds['trash'])){ ?>
			<div class="highlight padding-10 red-bg-50">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  red-bg-400"><span class="icon icon-guild"></span></span>
					<span class="icon-content">
						<span class="line font _24 grey-800"><?php _e('Trashed Guilds','bluerabbit'); ?></span>
					</span>
				</span>
				<div class="highlight-cell pull-right padding-10">
					<div class="search sticky">
						<div class="input-group">
							<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
							<label>
								<span class="icon icon-search"></span>
							</label>
							<script>
								$('#search').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('table#trash-guilds-table tbody > tr').show();           
									}else{
										$('table#trash-guilds-table tbody > tr').each(function(){
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
				<table class="table compact table-guilds" id="trash-guilds-table">
					<thead>
						<tr>
							<td class="" width="80%"><strong><?php _e("Name","bluerabbit"); ?></strong></td>
							<td class="text-center" width="5%"><span class="icon icon-edit"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-restore"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-duplicate"></span></td>
							<td class="text-center" width="5%"><span class="icon icon-cancel"></span></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($guilds['trash'] as $key=>$g){ ?>
						<tr class="guild" id="guild-<?= $g->guild_id;?>">
							<td>
								<div class="input-group w-full">
									<input type="text" class="form-ui" id="the_title-guild-<?= $g->guild_id; ?>" value="<?= $g->guild_name; ?>" onChange="setTitle(<?= $g->guild_id; ?>,'guild');">
								</div>
								<input type="hidden" class="guild-id" value="<?= $g->guild_id; ?>">
							</td>
							<td class="text-center">
								<a href="<?php echo get_bloginfo('url')."/new-guild/?adventure_id=$adventure->adventure_id&guild_id=$g->guild_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit"></span></a>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm blue-bg-A400 white-color" onClick="showOverlay('#confirm-publish-<?= $g->guild_id; ?>');">
									<span class="icon icon-restore"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-publish-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','publish');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  blue-bg-A400 icon-sm">
												<span class="icon icon-restore white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line blue-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm amber-bg-400 white-color" onClick="showOverlay('#confirm-draft-<?= $g->guild_id; ?>');">
									<span class="icon icon-duplicate"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-draft-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','draft');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  amber-bg-400 icon-sm">
												<span class="icon icon-duplicate white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line amber-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="text-center">
								<button class="icon-button font _24 sq-40  icon-sm red-bg-A400 white-color" onClick="showOverlay('#confirm-trash-<?= $g->guild_id; ?>');">
									<span class="icon icon-cancel"></span>
								</button>
								<div class="confirm-action overlay-layer" id="confirm-trash-<?= $g->guild_id; ?>">
									<button class="form-ui white-bg" onClick="confirmStatus(<?= $g->guild_id; ?>,'guild','delete');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
												<span class="icon icon-cancel white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
												<span class="line font _14 grey-400"><?php _e("You can't undo this","bluerabbit"); ?></span>
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
			<?php echo notFound(__("Trash is empty"),'red'); ?>
		<?php } ?>
		<input type="hidden" id="row_type" value="guild"/>

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<h1 class="padding-10 font condensed white-color w900 uppercase _20 light-blue-bg-800">
	<?= __('Manage players in','bluerabbit')." <strong>$adventure->adventure_title</strong>"; ?>
</h1>
<input type="hidden" id="register_nonce" value=""/>
<input type="hidden" id="player-status-nonce" value="<?= wp_create_nonce('br_player_adventure_status_nonce'); ?>"/>
<div class="dashboard">
	<div class="dashboard-sidebar grey-bg-800 relative padding-10">
		<div class="tabs-buttons" id="tab-group-buttons">
			<ul class="margin-0 padding-0">
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden active" id="active-players-list-tab-button" onClick="switchTabs('#tab-group','#active-players-list');">
						<span class="icon icon-tools foreground relative"></span>
						<span class="foreground relative"><?= __("Active Players","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="inactive-players-list-tab-button" onClick="switchTabs('#tab-group','#inactive-players-list');">
						<span class="icon icon-document foreground relative"></span>
						<span class="foreground relative"><?= __("Inactive Players","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>

				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="add-manually-players-list-tab-button" onClick="switchTabs('#tab-group','#add-manually-players-list');">
						<span class="icon icon-document foreground relative"></span>
						<span class="foreground relative"><?= __("Add Players","bluerabbit");?></span>
						<span class="active-content background purple-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>

				</li>
				<li class="block text-center">
					<a href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id";?>" class="form-ui light-green-bg-200 green-900 font w300 _18 w-full">
						<span class="icon icon-journey"></span> <?= __('Back to the journey','bluerabbit'); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php 
	$players = $wpdb->get_results("
		SELECT a.*, b.player_display_name, b.player_picture, b.player_first, b.player_last, b.player_email, b.player_hexad, b.player_hexad_slug, users.user_login FROM {$wpdb->prefix}br_player_adventure a
		JOIN {$wpdb->prefix}users users 
		on a.player_id = users.ID
		LEFT JOIN {$wpdb->prefix}br_players b 
		on a.player_id = b.player_id
		WHERE a.adventure_id=$adv_child_id AND a.player_adventure_role='player' ORDER BY a.player_adventure_status LIMIT 1000
	");
	?>
	<div class="dashboard-content white-bg">
		<div class="tabs" id="tab-group">
			<div class="active tab max-w-1200 padding-10" id="active-players-list">
				<div class="highlight padding-10 grey-bg-200 h-60" id="tutorial-players">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Active players","bluerabbit"); ?></span>
						</span>
					</div>
					<div class="highlight-cell pull-right">
						<div class="input-group inline-table">
							<label> <span class="icon icon-search"></span> </label>
							<input type="text" class="form-ui" id="search-players" placeholder="<?= __("Search players","bluerabbit"); ?>">
							<script>
								$('#search-players').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('tbody#players-list > tr').show();           
									}else{
										$('tbody#players-list > tr').each(function(){
											var text = $(this).text().toLowerCase();
											(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
										});
									};
								});
							</script>
						</div>
					</div>

				</div>
				<div class="content">
					<div class="row">
						<table class="table compact players-list">
							<thead>
								<tr>
									<td><?= __("User Login","bluerabbit"); ?></td>
									<td><?= __("Name","bluerabbit"); ?></td>
									<td><?= __("Lastname","bluerabbit"); ?></td>
									<td><?= __("Email","bluerabbit"); ?></td>
									<td><?= __("Work","bluerabbit"); ?></td>
									<td><?= __("Remove","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody id="players-list">
								<?php foreach($players as $play){ ?>
									<?php if($play->player_adventure_status=='in'){ ?>
										<?php $player_role = $play->player_adventure_role;  ?>
										<tr id="player-row-<?= $play->player_id; ?>" class="<?= "role-$player_role"; ?>">
											<td><?= $play->user_login; ?></td>
											<td><?= $play->player_first; ?></td>
											<td><?= $play->player_last; ?></td>
											<td><?= $play->player_email; ?></td>
											<td>
												<a target="_blank" href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adventure->adventure_id&player_id=$play->player_id"; ?>">
													<span class="icon icon-document"></span> <?= __("View work","bluerabbit");  ?>
												</a>
											</td>

											<td>
												<?php if(user_has_role($play->player_id,'br_player')){ ?>
													<button class="form-ui icon-sm red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?= $play->player_id; ?>');">
														<?= __("Remove Player","bluerabbit"); ?>
													</button>
													<div class="confirm-action overlay-layer" id="confirm-option-<?= $play->player_id; ?>">
														<button class="form-ui white-bg" onClick="updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id, 'out'"; ?>);">
															<span class="icon-group">
																<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
																	<span class="icon icon-cancel white-color"></span>
																</span>
																<span class="icon-content">
																	<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
																</span>
															</span>
														</button>
														<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
															<span class="icon icon-cancel white-color"></span>
														</button>
													</div>
												<?php }else{ ?>
													<button class="form-ui icon-sm red-bg-200 white-color" disabled>
														<?= __("Can't remove","bluerabbit"); ?>
													</button>
												<?php } ?>
												<?php if($config['allow_gm_reset_password']['value'] > 0){ ?>
													<button class="form-ui icon-sm red-bg-200 white-color" onClick="loadContent('update-player-password',<?= $play->player_id; ?>);">
														<?= __("Update Password","bluerabbit"); ?>
													</button>
												<?php } ?>
											</td>
										</tr>
									<?php } ?>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="tab max-w-1200 padding-10" id="inactive-players-list">
				<div class="highlight padding-10 grey-bg-200 h-60" id="">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Inactive players","bluerabbit"); ?></span>
						</span>
					</div>
					<div class="highlight-cell pull-right">
						<div class="input-group inline-table">
							<label> <span class="icon icon-search"></span> </label>
							<input type="text" class="form-ui" id="search-inactive-players" placeholder="<?= __("Search players","bluerabbit"); ?>">
							<script>
								$('#search-inactive-players').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('tbody#inactive-players-list > tr').show();           
									}else{
										$('tbody#inactive-players-list > tr').each(function(){
											var text = $(this).text().toLowerCase();
											(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
										});
									};
								});
							</script>
						</div>
					</div>

				</div>
				<div class="content">
					<div class="row">
						<table class="table compact inactive-players-list">
							<thead>
								<tr>
									<td><?= __("User Login","bluerabbit"); ?></td>
									<td><?= __("Name","bluerabbit"); ?></td>
									<td><?= __("Lastname","bluerabbit"); ?></td>
									<td><?= __("Email","bluerabbit"); ?></td>
									<td><?= __("Restore","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody id="players-list">
								<?php foreach($players as $play){ ?>
									<?php if($play->player_adventure_status=='out'){ ?>
										<?php $player_role = $play->player_adventure_role;  ?>
										<tr id="player-row-<?= $play->player_id; ?>" class="<?= "role-$player_role"; ?>">
											<td><?= $play->player_id; ?></td>
											<td><?= $play->user_login; ?></td>
											<td><?= $play->player_first; ?></td>
											<td><?= $play->player_last; ?></td>
											<td><?= $play->player_email; ?></td>

											<td>
												<button class="form-ui icon-sm red-bg-200 white-color" onClick="showOverlay('#confirm-option-<?= $play->player_id; ?>');">
													<?= __("Activate Player","bluerabbit"); ?>
												</button>
												<div class="confirm-action overlay-layer" id="confirm-option-<?= $play->player_id; ?>">
													<button class="form-ui white-bg" onClick="updatePlayerAdventureStatus(<?= "$adventure->adventure_id, $play->player_id, 'in'"; ?>);">
														<span class="icon-group">
															<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
																<span class="icon icon-cancel white-color"></span>
															</span>
															<span class="icon-content">
																<span class="line red-A400 font _18 w900"><?= __("Are you sure?","bluerabbit"); ?></span>
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
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="tab max-w-1200 padding-10" id="add-manually-players-list">
				<div class="highlight padding-10 grey-bg-200 h-60" id="add-players">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Add players","bluerabbit"); ?></span>
						</span>
					</div>
				</div>
				<div class="content">
					<div class="highlight">
						<div class="form-ui font _14">
							<form id="upload_bulk_users_form" class="form" enctype="multipart/form-data" method="post" accept-charset="utf-8">

								<table>
									<tbody>
										<tr>
											<td class="w-200">
												<label for="the_csv_file_with_users" class="">Select CSV File:</label>
												<input type="file" name="the_csv_file_with_users" id="the_csv_file_with_users" size="20" />
											</td>
											<td class="w-100">
												<button type="button" onClick="uploadBulkUsers();" name="upload_csv" class="form-ui button"><?= __("Upload file","bluerabbit"); ?></button>
											</td>
										</tr>
									</tbody>
								</table>
							</form>

						</div>
					</div>
					<div class="row">
						<table class="table" id="just-uploaded-users">
							<thead>
								<tr>
									<td><input type="checkbox" id="select-all"></td>
									<td><?= __("Nickname","bluerabbit"); ?></td>
									<td><?= __("Password","bluerabbit"); ?></td>
									<td><?= __("Email","bluerabbit"); ?></td>
									<td><?= __("First Name","bluerabbit"); ?></td>
									<td><?= __("Last Name","bluerabbit"); ?></td>
									<td><?= __("Lang","bluerabbit"); ?></td>
									<td><?= __("Status","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody id="just-uploaded-users-body">
							</tbody>
						</table>
						<div class="call-to-action highlight text-center" id="call-to-action">
						</div>
					</div>
					<div class="highlight padding-10 grey-bg-200 h-60" id="add-players">
						<div class="icon-group">
							<span class="icon-button font _24 sq-40  orange-bg-200">
								<span class="icon icon-players white-color"></span>
							</span>
							<span class="icon-content font w500 _26">
								<span class="line font _24 w300"><?= __("Add single player","bluerabbit"); ?></span>
							</span>
						</div>
					</div>
					<div class="add-single-player">
						<div class="username-search-form">
							<h3><?= __("Check if Username or Email exists","bluerabbit"); ?></h3>
							<input class="form-ui" type="text" id="username-search" maxlength="30" placeholder="<?= __("Nickname or Email","bluerabbit");?>" onBlur="checkUserDataExists(this);">
						</div>
						<div id="new-player-warnings" class="new-player-warnings">
						</div>
						<div id="add-single-player-form" class="add-single-player-form">
							<div class="player-data-content">
								<div class="row nickame">
									<h3><?= __("Nickname","bluerabbit"); ?></h3>
									<input type="hidden" id="new-player-lang" value="<?= $current_player->player_lang;?>">
									<input class="form-ui" type="text" id="new-player-username" maxlength="30" placeholder="<?= __("Nickname","bluerabbit");?>">
								</div>
								<div class="row email">
									<h3><?= __("Email","bluerabbit"); ?></h3>
									<input class="form-ui" type="email" id="new-player-email" maxlength="255" placeholder="<?= __("Email","bluerabbit");?>">
								</div>
								<div class="row password">
									<h3><?= __("Password","bluerabbit"); ?></h3>
									<input class="form-ui" type="text" id="new-player-user-password" maxlength="18" placeholder="<?= __("Password","bluerabbit");?>">
								</div>
							</div>
							<div class="player-data-actions">
								<button id="btn-reg-player" class="form-ui"><?= __("Register player","bluerabbit");?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

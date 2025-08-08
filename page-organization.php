<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php  $org = getOrgs($_GET['id']); ?>
<h1 class="padding-10 font condensed white-color w900 uppercase _20 light-blue-bg-800">
	<?= __('Manage Organization','bluerabbit'); ?>
</h1>
<input type="hidden" id="the_org_id" value="<?= $org->org_id; ?>">
<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_org_nonce'); ?>"/>
<input type="hidden" id="search-player-nonce" value="<?= wp_create_nonce('br_search_player_org_nonce'); ?>"/>
<div class="dashboard">
	<div class="dashboard-sidebar grey-bg-800 relative padding-10">
		<div class="tabs-buttons" id="tab-group-buttons">
			<ul class="margin-0 padding-0">
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden active" id="general-tab-button" onClick="switchTabs('#tab-group','#general');">
						<span class="icon icon-tools foreground relative"></span>
						<span class="foreground relative"><?= __("General Settings","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="enrolled-players-tab-button" onClick="switchTabs('#tab-group','#enrolled-players');">
						<span class="icon icon-player foreground relative"></span>
						<span class="foreground relative"><?= __("Players","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
				<li class="block">
					<button class="form-ui w-full relative tab-button overflow-hidden" id="adventures-tab-button" onClick="switchTabs('#tab-group','#adventures');">
						<span class="icon icon-adventure foreground relative"></span>
						<span class="foreground relative"><?= __("Adventures","bluerabbit");?></span>
						<span class="active-content background orange-bg-700"></span>
						<span class="inactive-content background grey-bg-600"></span>
					</button>
				</li>
			</ul>
		</div>
	</div>
	<div class="dashboard-content white-bg">
		<div class="tabs" id="tab-group">
			<div class="active tab max-w-900 padding-10" id="general">
				<div class="highlight padding-10 grey-bg-200 sticky top left layer base">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40  indigo-bg-400">
							<span class="icon icon-adventure white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line"><?= __('General Settings',"bluerabbit"); ?></span>
							<span class="line font _14 w300 grey-500"><?= __('Basic settings','bluerabbit'); ?></span>
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
							<td class="text-right w-150"><?= __('Name','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<label class="light-blue-bg-800 font _24"><span class="icon icon-quest"></span></label>
									<input class="form-ui font _30 w-full" placeholder="Organization Name" maxlength="50" type="text" value="<?= $org->org_name; ?>" id="the-org-name">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?= __('Logo','bluerabbit'); ?></td>
							<td>
								<div class="gallery">
									<?php insertGalleryItem('the-org-logo', $org->org_logo); ?>
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?= __('Color','bluerabbit'); ?></td>
							<td>
							<input id="the-org-color" class="color-selected" type="hidden" value="<?= $selected_color; ?>">
					<?php 
					$color_select_id = "#the-org-color";
					include (TEMPLATEPATH . '/color-select.php');
					?>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?= __('About','bluerabbit'); ?></td>
							<td>
								<?php 
								$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
								wp_editor( $org->org_content, 'the-org-content', $wp_editor_settings); 
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="tab max-w-1200 padding-10" id="enrolled-players">
				<?php 
				$org_players = getOrgPlayers($org->org_id);
				$total_org_players = isset($org_players) ? count($org_players) : 0; 
				?>
				<div class="highlight padding-10 grey-bg-200" id="org-players">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Player","bluerabbit"); ?></span>
							<span class="line font _14 w900"><?= __("Total","bluerabbit")." ".$total_org_players; ?></span>
						</span>
					</div>
				</div>
				<div class="find-players">
					<div class="input-group">
						<label> <span class="icon icon-search"></span>Add Players to the org</label>
						<input type="text" class="form-ui" id="player-search-string" autocomplete="off" placeholder="<?= __("Type email to add","bluerabbit"); ?>">
						<label>
							<button class="button main" onClick="findPlayersToOrg();">Find Player</button>
						</label>
					</div>
					<div id="search-players-results" class="players-search-results">
						<ul class="player-select"></ul>
					</div>
				</div>
				<div class="highlight padding-10 grey-bg-200" id="org-players">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Player","bluerabbit"); ?></span>
							<span class="line font _14 w900"><?= __("Total","bluerabbit")." ".$total_org_players; ?></span>
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
									<td><?= __("ID","bluerabbit"); ?></td>
									<td><?= __("User Login","bluerabbit"); ?></td>
									<td><?= __("Name","bluerabbit"); ?></td>
									<td><?= __("Lastname","bluerabbit"); ?></td>
									<td><?= __("Email","bluerabbit"); ?></td>
									<td><?= __("Role","bluerabbit"); ?></td>
									<td><?= __("Remove","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody id="org-players-list">
								<?php if(isset($org_players)){ ?>
									<?php foreach($org_players as $player){ ?>
										<?php include (TEMPLATEPATH . '/player-row-org.php'); ?>
									<?php } ?>
								
								<?php }else{ ?>
								
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="tab max-w-1200 padding-10" id="adventures">
				<?php $adventures = getOrgAdventures($org->org_id);
					$total_org_adventures = isset($adventures) ? count($adventures) : 0;
				?>
				<div class="highlight padding-10 grey-bg-200" id="org-adventures">
					<div class="icon-group">
						<span class="icon-button font _24 sq-40  orange-bg-200">
							<span class="icon icon-players white-color"></span>
						</span>
						<span class="icon-content font w500 _26">
							<span class="line font _24 w300"><?= __("Adventure","bluerabbit"); ?></span>
							<span class="line font _14 w900"><?= __("Total","bluerabbit")." ".$total_org_adventures; ?></span>
						</span>
					</div>
					<div class="highlight-cell pull-right">
						<div class="input-group inline-table">
							<label> <span class="icon icon-search"></span> </label>
							<input type="text" class="form-ui" id="search-adventures" placeholder="<?= __("Search adventures","bluerabbit"); ?>">
							<script>
								$('#search-players').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('tbody#adventures-list > tr').show();           
									}else{
										$('tbody#adventures-list > tr').each(function(){
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
						<table class="table compact adventures-list">
							<thead>
								<tr>
									<td><?= __("ID","bluerabbit"); ?></td>
									<td><?= __("Adventure","bluerabbit"); ?></td>
									<td><?= __("Enroll","bluerabbit"); ?></td>
									<td><?= __("Owner","bluerabbit"); ?></td>
								</tr>
							</thead>
							<tbody id="players-list">
								<?php if(isset($adventures)){ ?>
									<?php foreach($adventures as $adv){ ?>
										<tr id="adventure-row-<?= $adv->adventure_id; ?>">
											<td><?= $adv->adventure_id; ?></td>
											<td><?= $adv->adventure_name; ?></td>
											<td><?= get_bloginfo('url')."/enroll/?enroll_code=$adventure->adventure_code"; ?></td>
											<td><?= $adv->player_display_name; ?></td>
										</tr>
									<?php } ?>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="hidden" id="msg-player-added-to-org">
	<li class='border green-bg-400 green-border-800'>
		<span class='icon-group'>
			<span class='icon-button font _24 sq-40'>
				<span class='icon white-color'></span>
			</span>
			<span class='icon-content white-color'>
				<span class='line font _16'><?= __("Player added to Org!","bluerabbit"); ?></span>
			</span>
		</span>
	</li>
</div>
<div class="hidden" id="msg-player-not-added-to-org">
	<li class='border red-bg-400 red-border-800'>
		<span class='icon-group'>
			<span class='icon-button font _24 sq-40'>
				<span class='icon white-color'></span>
			</span>
			<span class='icon-content white-color'>
				<span class='line font _16'><?= __("Error!","bluerabbit"); ?></span>
			</span>
		</span>
	</li>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

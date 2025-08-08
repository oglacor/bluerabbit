<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php $player_account = getPlayerData($current_user->ID); ?>
<?php $uMeta = get_user_meta($current_user->ID); ?>

<?php $picture = $player_account->player_picture ? $player_account->player_picture : $randprof;  ?>

<div class="w-full h-20"></div>

<div class="w-full boxed w-max-400">
	<div class="text-center w-full">
		<img src="<?= $config['main_logo']['value'] ? $config['main_logo']['value'] : get_bloginfo('template_directory').'/images/logo.png';?>" class="inline-block w-200">
	</div>
	<div class="w-full h-20"></div>
	<div class="text-center w-full">
		<div class="status" style="background-image: url(<?= $picture; ?>);" id="the_player_picture_thumb">
			<img class="rotate-R-120" src="<?= get_bloginfo('template_directory')."/images/1.png";?>">
			<button class="icon-button absolute perfect-center green-bg-400 layer foreground font _18 padding-5" onClick="showWPUpload('the_player_picture','profile-autosave');">
				<span class="icon icon-edit"></span>
			</button>
			<input type="hidden" id="the_player_picture" value="<?= $picture; ?>"/>
		</div>
	</div>
	<!-- TABS BEGIN -->
	<?php if($config['use_hexad']['value']>0 || $config['show_upgrade']['value']>0 || $config['show_anonimize_button']['value']>0) { ?>
		<div class="tabs-buttons max-w-400 boxed text-center" id="tab-group-buttons">
			<button class="form-ui transparent-bg relative active tab-button" id="general-tab-button" onClick="switchTabs('#tab-group','#general');">
				<span class="text relative layer base"><?= __("General","bluerabbit"); ?></span>
				<span class="layer background white-bg opacity-20 active-content sq-full"></span>
			</button>
			<?php if($config['use_hexad']['value']>0){ ?>
				<button class="form-ui transparent-bg relative tab-button" id="hexad-tab-button" onClick="switchTabs('#tab-group','#hexad');">
					<span class="text relative layer base"><?= __("Hexad","bluerabbit"); ?></span>
					<span class="layer background white-bg opacity-20 active-content sq-full"></span>
				</button>
			<?php } ?>
			<?php if($config['show_upgrade']['value']>0){ ?>
				<button class="form-ui transparent-bg relative tab-button" id="account-type-tab-button" onClick="switchTabs('#tab-group','#account-type');">
					<span class="text relative layer base"><?= __("Account","bluerabbit"); ?></span>
					<span class="layer background white-bg opacity-20 active-content sq-full"></span>
				</button>
			<?php } ?>
			<?php if($config['show_anonimize_button']['value']>0){ ?>
				<button class="form-ui transparent-bg relative tab-button" id="anonimize-tab-button" onClick="switchTabs('#tab-group','#anonimize');">
					<span class="text relative layer base"><?= __("Anonimize","bluerabbit"); ?></span>
					<span class="layer background white-bg opacity-20 active-content sq-full"></span>
				</button>
			<?php } ?>
		</div>
	<?php } ?>
	<div class="w-full boxed max-w-400 tabs white-color" id="tab-group">
		<div class="active tab max-w-400 padding-10" id="general">
			<div class="text-center white-color padding-20">
				<div class="icon-group padding-10 inline-table">
					<div class="icon-content">
						<span class="line font _36 w300 text-right condensed"><?= __("Player Level","bluerabbit"); ?></span>
					</div>
					<div class="icon-button font _36 sq-50 blue-bg-700">
						<span class="icon icon-logo"></span>
					</div>
					<div class="icon-content">
						<span class="line font _36 w900"><?= $player_account->player_absolute_level; ?></span>
					</div>
				</div>
			</div>
			<div class="input-group w-full">
				<label for="the_first_name" class="blue-bg-700"><?php _e('First name','bluerabbit'); ?></label>
				<input class="form-ui w-full" id="the_first_name" name="the_first_name" type="text" value="<?= $player_account->player_first ? $player_account->player_first : $uMeta['first_name'][0]; ?>"  onChange="updateProfile();">
			</div>
			<div class="input-group w-full">
				<label for="the_last_name" class="blue-bg-700"><?php _e('Last name','bluerabbit'); ?></label>
				<input class="form-ui w-full" id="the_last_name" name="the_last_name" type="text" value="<?= $player_account->player_last ? $player_account->player_last : $uMeta['last_name'][0]; ?>"  onChange="updateProfile();">
			</div>
			<div class="input-group w-full">
				<label class="deep-purple-bg-400"><?php _e('Nickname','bluerabbit'); ?></label>
				<input class="form-ui w-full" readonly type="text" value="<?=$current_user->user_login; ?>">
			</div>
			<div class="input-group w-full">
				<label class="blue-bg-700"><?php _e('Email','bluerabbit'); ?></label>
				<input class="form-ui w-full blue" type="text" id="the_email" value="<?= $player_account->player_email ? $player_account->player_email : $current_user->user_email; ?>" >
			</div>
			<?php $locale = $player_account->player_lang; ?>
			<?php $langs = array(
				array("en_US","U.S. English"),
				array("es_MX","Espa&ntilde;ol"),
			);
			?>
			<div class="input-group w-full">
				<label class="green-bg-400"><?php _e('Language','bluerabbit'); ?></label>
				<select class="form-ui w-full" id="the_lang">
					<?php foreach($langs as $l){ ?>
						<option <?php if($locale == $l[0]) { echo 'selected';} ?> value="<?= $l[0];?>"><?= $l[1];?></option>
					<?php } ?>
				</select>
			</div>
			<div class="highlight padding-10 text-center">
				<input type="hidden" id="profile_nonce" value='<?= wp_create_nonce('br_profile_post_nonce') ?>'>
				<button class="form-ui font _24 green-bg-400" onClick="updateProfile();"><?php _e('Save','bluerabbit'); ?></button>
			</div>
		</div>
		<?php if($config['show_anonimize_button']['value']>0){ ?>
			<div class="tab max-w-400 padding-10" id="anonimize">
				<div class="text-center">
					<div class="icon-group padding-10 inline-table white-color">
						<div class="icon-button font _24 sq-40 red-bg-A400">
							<span class="icon icon-warning"></span>
						</div>
						<div class="icon-content text-left">
							<span class="line font _24 w100"><?= __("Anonimizer","bluerabbit"); ?></span>
							<span class="line font _14 w900 red-A700"><?= __("This button deletes all your personal data","bluerabbit"); ?></span>
						</div>
					</div>
					<div class="padding-10 font _16 amber-bg-400 black-color">
						<?= __("When you use the anonimizer, all your personal data converts to random names, pictures and email. Although you can still update your data back to normal, you will lose access to your account on logout. This is done with the sole purpose of keeping the demographic data of your account while protecting your privacy.","bluerabbit"); ?>
					</div>
					<div class="icon-group padding-10 inline-table relative">
						<div class="icon-content">
							<button class="form-ui red-bg-A400 white-color font _30 condensed w900 uppercase " onClick="showOverlay('#confirm-anonimize-1');">
								<?= __("Anonimize Me","bluerabbit"); ?>
							</button>
							<div class="confirm-action overlay-layer" id="confirm-anonimize-1">
								<button class="form-ui amber-bg-400 black-color" onClick="showOverlay('#confirm-anonimize-2');">
									<span class="icon-group">
										<span class="icon-button font _24 sq-40  icon-sm orange-bg-400">
											<span class="icon icon-warning white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line font _24 w900"><?= __("Are you sure about this?","bluerabbit"); ?></span>
										</span>
									</span>
								</button>
							</div>
							<div class="confirm-action overlay-layer" id="confirm-anonimize-2">
								<button class="form-ui orange-bg-800" onClick="randomPlayerData();updateProfile();">
									<h3 class="text-center white-color font _18 condensed w100"><?= __("You can still update your profile, just don't close the session","bluerabbit"); ?></h3>
									<span class="icon-group">
										<span class="icon-button font _24 sq-40  icon-sm orange-bg-400">
											<span class="icon icon-warning white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line white-color font _24 w900"><?= __("Double confirm for security","bluerabbit"); ?></span>
										</span>
									</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
		
		<?php if($config['use_hexad']['value']>0){ ?>
			<div class="tab padding-10" id="hexad">
				<h3 class="white-color font _24 w300"><?php _e('My Player Type','bluerabbit'); ?></h3>
				<h5 class="font _12 w300 opacity-50 lime-500">
					<a href="http://gamified.uk" target="_blank"><?php _e("Read all about Marczewski's player types",'bluerabbit'); ?></a>
				</h5>
				<div class="text-center padding-10">
					<?php if($player_account->hexad_date){ ?>
						<h3 class="padding-10 font _18 w300 white-color opacity-80"><?= __('Last Tested','bluerabbit')." ".(date('M jS, Y', strtotime($player_account->hexad_date))); ?> </h3>
						<a href="<?= get_bloginfo('url')."/new-hexad"; ?>" class="form-ui green-bg-400">
							<?php _e("Test again","bluerabbit"); ?> <span class="icon icon-hexad"></span>
						</a>
					<?php }else{ ?>
						<h3 class="padding-10 font _18 w300 white-color opacity-80"><?= __('Never tested','bluerabbit'); ?> </h3>
						<a href="<?= get_bloginfo('url')."/new-hexad"; ?>" class="form-ui green-bg-400">
							<?php _e("Test now","bluerabbit"); ?> <span class="icon icon-hexad"></span>
						</a>
					<?php } ?>
				</div>
				<?php if($player_account->hexad_date){ ?>
					<?php 
					$color = [
						'freespirit' => 'pink',
						'achiever' => 'blue',
						'philanthropist' => 'teal',
						'socialiser' => 'amber',
					];
					?>
					<div class="w-full <?= $player_account->player_hexad_slug; ?> white-color">
						<div class="text-center">
							<div class="icon-group inline-table">
								<span class="icon-button font _36  <?= $color[$player_account->player_hexad_slug]; ?>-bg-400">
									<span class="icon icon-<?= $player_account->player_hexad_slug; ?>"></span>
								</span>
								<div class="icon-content">
									<span class="line font _36 w900 <?= $color[$player_account->player_hexad_slug]; ?>-400"><?= $player_account->player_hexad; ?></span>
									<span class="line font _16 w300"><?php _e("Dominant Player Type","bluerabbit"); ?></span>
								</div>
							</div>
						</div>
						<div class="h-10 w-full"></div>
						<div class="player-type-description">
							<?php include (TEMPLATEPATH . "/$player_account->player_hexad_slug.php"); ?>
						</div>
						<div class="h-10 w-full"></div>
					</div>
					<div class="w-full hexad-graph">
						<?php 
						$hexad = unserialize($player_account->hexad_answers);
						$intrinsic = array($hexad["type_f"],$hexad["type_s"],$hexad["type_ph"],$hexad["type_a"]);
						$ptMax = max($intrinsic);
						if($ptMax==$hexad["type_f"] ){
							$ptMaxSlug = "freespirit";
						}elseif($ptMax==$hexad["type_a"] ){
							$ptMaxSlug = "achiever";
						}elseif($ptMax==$hexad["type_ph"] ){
							$ptMaxSlug = "philanthropist";
						}elseif($ptMax==$hexad["type_s"] ){
							$ptMaxSlug = "socialiser";
						}
						?>
						<input type="hidden" id="pt-hexad-highest" value="<?= $ptMaxSlug; ?>">
						<div class="pt-chart">
							<canvas id="pt-graph-canvas" width="300" height="300"></canvas>
						</div>
						<script>
							createHexadChart(<?= $hexad["type_d"]; ?>,<?= $hexad["type_f"]; ?>,<?= $hexad["type_a"]; ?>,<?= $hexad["type_p"]; ?>,<?= $hexad["type_s"]; ?>,<?= $hexad["type_ph"]; ?>,"pt-graph-canvas");
						</script>
					</div>
					<div class="w-full text-center padding-10">
						<a class="form-ui" href="https://www.gamified.uk/user-types/" target="_blank">
							<?php _e("Read all about Marczewski player types","bluerabbit"); ?>
						</a>
						<a class="form-ui" href="https://www.amazon.com/Even-Ninja-Monkeys-Like-Play/dp/1514745666/ref=sr_1_1?ie=UTF8&qid=1532534766&sr=8-1&keywords=even+ninja+monkeys+like+to+play" target="_blank">
							<span class="icon icon-hexad"></span><?php _e('Get a copy of “Even Ninja Monkeys like to Play”',"bluerabbit"); ?>
						</a>
					</div>
				<?php }else{ ?>
					<div class="w-full text-center font _18 white-color padding-10">
						<?= __("No data available","bluerabbit"); ?>
					</div>
				<?php } ?>
				<div class="w-full padding-10 white-color">
					<div class="APA-ref">
						<div id="copy-target-99791202" class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied">Marczewski, A. (2015). <a href="http://gamified.uk/user-types/" rel="nofollow" target="_blank">User Types</a>. In <em><a href="http://www.gamified.uk/even-ninja-monkeys-like-to-play/">Even Ninja Monkeys Like to Play</a>: Gamification, Game Thinking and Motivational Design</em> (1st ed., pp. 65-80). CreateSpace Independent Publishing Platform.</div><div class="bibliography-item-copy-text content col-md-12" data-clipboard-target="copy-target-99791202" data-redirect-target="http://www.citationmachine.net/apa/cite-a-book/copied"><ul><li><strong>ISBN-10:</strong> 1514745666</li><li><strong>ISBN-13:</strong> 978-1514745663</li></ul><p><a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license"><img style="border-width: 0;" src="//i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" alt="Creative Commons Licence"/></a> Gamification User Types Hexad by <a href="https://www.gamified.uk/user-types" rel="cc:attributionURL">Andrzej Marczewski</a> is licensed under a <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" rel="nofollow" target="_blank" rel="license">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.</p></div>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if($config['show_upgrade']['value']>0){ ?>
			<div class="tab max-w-400 padding-10" id="account-type">
				<div class="content">
					<div class="divider long"></div>
					<div class="row br-sub-header theme-border text-center center-h" id="subscription">
						<?php $subscription='free'; ?>
						<div class="current-plan text-center">
							<div class="col-6 ">
								<h1 class="font _36 condensed padding-10 w900 uppercase"> <?php _e('Subscription','bluerabbit'); ?> </h1>
								<a href="<?= get_bloginfo('url')."/subscribe/"; ?>" class="icon-button font _24 sq-40  icon-xxl blue-grey"><span class="icon icon-logo"></span></a>
							</div>
							<div class="col-6">

								<h5><?php _e("Current Plan","bluerabbit"); ?>: </h5>
								<h1><strong><?php _e("Free","bluerabbit"); ?></strong></h1>
								<ul>
									<li><?php _e("Players allowed","bluerabbit"); ?>: 200</li>
									<li><?php _e("Max Adventures","bluerabbit"); ?>: 3</li>
									<li><?php _e("Disc Space","bluerabbit"); ?>: 50Mb</li>
								</ul>
							</div>
						</div>
						<div class="divider long thin"></div>
							<div class="col-12 text-center">
								<a href="https://bluerabbit.io/upgrade/" target="_blank" class="form-ui big indigo"><span class="icon icon-logo"></span><?php _e("Upgrade now!","bluerabbit"); ?></a>
							</div>
						<div class="divider long thin"></div>
						<div class="upgrade">
							<div class="col-6 border text-right">
								<h2><?php _e("Upgrade to PRO","bluerabbit"); ?></h2>
								<h1><strong>$8.00</strong></h1>
								<h3>USD/mo</h3>
							</div>
							<div class="col-6">
								<h2 class="solid-amber"><?php _e("Unlimited Players per adventure","bluerabbit"); ?></h2>
								<h2 class="solid-cyan"><?php _e("Unlimited Adventures","bluerabbit"); ?></h2>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
		<?php } ?>

		
	</div>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

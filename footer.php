			</div><!-- CLOSE MAIN CONTAINER-->
		</div><!-- CLOSE MAIN CONTENT-->
		<div class="main-loader" id="main-loader">
			<div class="main-loader-content"></div>
		</div>
		<?php
			if(isset($adventure) && !is_page('enroll')){
				$rank = isset($myRank) ? $myRank->achievement_name : $adventure->adventure_nickname;
				$rankColor = isset($myRank) ? $myRank->achievement_color : $adventure->adventure_color;
				
				if($roles[0]=='br_npc'){
					include (TEMPLATEPATH . '/nav-npc.php'); 
				}else{
					include (TEMPLATEPATH . '/nav.php'); 
				}

			}else{
				include (TEMPLATEPATH . '/nav-no-adventure.php');

			}
		?>

		<div id="flipped-card-container" class="flipped-card-container"></div>

		<div class="feedback overlay-layer layer fixed white-color" id="feedback">
			<div class="background opacity-80 black-bg layer absolute base" onClick="hideAllOverlay();"></div>
			<div class="perfect-center layer relative base">
				<div class="foreground content">
				</div>
			</div>
		</div>
		<div class="overlay-layer absolute layer feedback top left sq-full" id="overlay-content">
			<div class="background opacity-80 black-bg fixed top left" onClick="unloadContent();"></div>
			<div class="layer base relative content min-w-full min-h-full"></div>
		</div>

		<video id="overlay-background-video" loop class="overlay-background-video layer overlay">
			<source src="<?=get_bloginfo('template_directory')."/video/particle-trail-1.mp4"; ?>">
		</video>

		<div class="level-up layer feedback top left fixed sq-full" id="level-up">
			<div class="layer background opacity-80 black-bg fixed sq-full level-up-bg" style="background-image: url(<?= get_bloginfo('template_directory')."/images/explosion-lq.gif"; ?>)"></div>
			<div class="layer background opacity-60 achievement-image fixed sq-full"></div>
			<div class="layer base white-color perfect-center text-center absolute content line-200">
			</div>
		</div>
		<div class="notify-message" id="notify-message">
			<ul class="content">
			</ul>
		</div>
		<div class="confirm-logout layer feedback fixed sq-vp-full" id="confirm-logout">
			<div class="layer background red-bg-800 opacity-80 absolute" onClick="hideAllOverlay();"></div>
			<div class="layer base absolute perfect-center">
				<button class="foreground form-ui red-bg-400 font _30" onClick="br_logout();">
					<?= __("Logout","bluerabbit"); ?>
				</button><br>
				<button class="foreground form-ui" onClick="hideAllOverlay();"><?= __("Cancel","bluerabbit"); ?></button>
			</div>
		</div>
		<?php if(isset($adventure)){ ?>
		<div class="magic-code-form overlay-layer layer top-overlay fixed sq-full top left" id="contact-admin-form">
			<div class="layer absolute background blue-bg-800 opacity-80" onClick="hideAllOverlay();"></div>
			<div class="layer absolute background black-bg opacity-60" onClick="hideAllOverlay();"></div>
			<div class="layer relative base perfect-center text-center w-400">
				<h3 class="white-color font _30 w700"><span class="icon icon-mail"></span> <?php _e("Contact Admin","bluerabbit"); ?></h3>
				<p class="white-color font _14 opacity-60 padding-5"><?php _e("Send a message to the adventure admins","bluerabbit"); ?></p>
				<input class="form-ui w-full font _16" type="text" id="request-subject" placeholder="<?php _e("Subject","bluerabbit"); ?>" maxlength="255">
				<br>
				<textarea class="form-ui w-full font _14" id="request-content" rows="5" placeholder="<?php _e("Describe your request...","bluerabbit"); ?>"></textarea>
				<br>
				<button class="form-ui blue-bg-400" onClick="submitRequest();"><span class="icon icon-check"></span> <?php _e("Send","bluerabbit"); ?></button>
				<button class="form-ui grey-bg-600" onClick="hideAllOverlay();"><span class="icon icon-cancel"></span> <?php _e("Cancel","bluerabbit"); ?></button>
				<input type="hidden" id="request-nonce" value="<?php echo wp_create_nonce('br_request_nonce'); ?>" />
			</div>
		</div>
		<?php } ?>

		<?php if(isset($allow_magic_codes) && $allow_magic_codes==true){ ?>
			<div class="magic-code-form overlay-layer layer top-overlay fixed sq-full top left" id="magic-code-form">
				<div class="layer absolute background deep-purple-bg-400 opacity-80" onClick="hideAllOverlay();"></div>
				<div class="layer absolute background black-bg opacity-60" onClick="hideAllOverlay();"></div>
				<div class="layer relative base perfect-center text-center">
					<h3 class="white-color font _30 w700"><span class="icon icon-qr"></span> <?php _e("What's the code?",'bluerabbit'); ?></h3>
					<input class="magic-code-field" type="text" id="magic-code" placeholder="<?php _e("Enter code",'bluerabbit'); ?>">
					<br>
					<button class="form-ui purple-bg-400" onClick="submitMagicCode();"><span class="icon icon-check"></span> <?php _e('Send','bluerabbit'); ?></button>
				</div>
			</div>
		<?php } ?>
		<?php if(isset($isDemo) && is_page('adventure')){ ?>
			<div class="magic-code-form overlay-layer layer top-overlay fixed sq-full top left" id="reset-demo-form">
				<div class="layer absolute background red-bg-400 opacity-80" onClick="hideAllOverlay();"></div>
				<div class="layer absolute background black-bg opacity-60" onClick="hideAllOverlay();"></div>
				<div class="layer relative base perfect-center text-center w-400">
					<h1 class="font _20 w100 padding-10 w-full white-color red-bg-400 text-center">
						<span class="icon icon-quest"></span>
						<?php _e("Reset Demo","bluerabbit"); ?>
					</h1>
					<?php if(getSetting('req_password_reset_demo', $adventure->adventure_id)){ ?>
						<h3 class="white-bg grey-900 padding-5 font _14"><?= __("To reset this demo type your pasword and click reset","bluerabbit"); ?></h3>
						<br>
						<div class="input-group w-full">
							<label for="the_player_password" class="red-bg-A400 white-color font _16 w300 uppercase condensed">
								<span class="icon icon-lock"></span><?= __("Password","bluerabbit"); ?>
							</label>
							<input type="password" id="the_player_password" name="the_player_password" class="form-ui font _18 w-full">
						</div>
						<button class="form-ui red-bg-A400 font _20" onClick="resetDemoAdventurePlayer();"><?= __("Reset","bluerabbit"); ?></button>
					<?php }else{ ?>
						<h3 class="white-bg grey-900 padding-5 font _14"><?= __("To reset this demo click reset","bluerabbit"); ?></h3>
						<br>
						<button class="form-ui red-bg-A400 font _20" onClick="resetDemoAdventurePlayer();"><?= __("Reset","bluerabbit"); ?></button>
					<?php } ?>
				</div>
				<input type="hidden" id="reset_demo_nonce" value="<?php echo wp_create_nonce('br_reset_demo_nonce'); ?>" />
			</div>
		<?php } ?>




		<div class="loader layer fixed overlay-layer top left sq-full" id="loader"> 
			<div class="background grey-bg-900 icons-bg-light opacity-60" onClick="hideAllOverlay();"></div>
			<div class="background indigo-bg-900 opacity-60" onClick="hideAllOverlay();"></div>
			<img class="animated perfect-center layer base absolute" src="<?php bloginfo('template_directory'); ?>/images/loader.svg" width="150">
		</div>
		<div class="small-loader loader layer fixed overlay-layer bottom-70 right-10" id="small-loader"> 
			<img class="animated" src="<?php bloginfo('template_directory'); ?>/images/loader.svg" width="50">
		</div>

		<!-- Message inputs -->
		<div class="display-messages hidden">

			<ul>
				<li id="msg-delete">
					<span class='icon icon-cancel icon-xxl solid-red'></span>
					<h1><?php _e("Are you sure you want to DELETE this?","bluerabbit"); ?></h1>
					<h3><strong><?php _e("This action can't be undone","bluerabbit"); ?></strong></h3>
					<button class="form-ui red big" onClick="br_trash();"><?php _e("Delete this","bluerabbit"); ?></button>
					<button class="form-ui green" onClick="clearTRD();"><span class='icon icon-cancel'></span> <?php _e("Cancel","bluerabbit"); ?></button>
				</li>
				<li id="msg-draft">
					<h1><?php _e("Save as draft?","bluerabbit"); ?></h1>
					<button class="form-ui blue big" onClick="br_trash();"><span class='icon icon-restore'></span> <?php _e("Draft","bluerabbit"); ?></button>
					<button class="form-ui green" onClick="clearTRD();"><span class='icon icon-cancel'></span><?php _e("Cancel","bluerabbit"); ?></button>
				</li>
				<li id="msg-trash">
					<span class='icon icon-trash icon-xxl'></span>
					<h1><?php _e("Are you sure you want to send this to the trash?","bluerabbit"); ?></h1>
					<button class="form-ui red big" onClick="br_trash();"><span class='icon icon-trash'></span> <?php _e("Send to trash","bluerabbit"); ?></button>
					<button class="form-ui green" onClick="clearTRD();"><span class='icon icon-cancel'></span><?php _e("Cancel","bluerabbit"); ?></button>
				</li>
				<li id="msg-publish">
					<span class='icon icon-restore icon-xxl'></span>
					<h1><?php _e("Are you sure you want to restore this?","bluerabbit"); ?></h1>
					<button class="form-ui blue big" onClick="br_trash();"><span class='icon icon-restore'></span> <?php _e("Restore","bluerabbit"); ?></button>
					<button class="form-ui green" onClick="clearTRD();"><span class='icon icon-cancel'></span><?php _e("Cancel","bluerabbit"); ?></button>
				</li>
			</ul>
            <div class="hidden" id="msg-save-first">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("Please save first","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-error">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("Error","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-new-tabi-row">
                <li class='border green-bg-400 green-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-check green-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("Tabi Row Inserted","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-no-id">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("Invalid object ID","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-no-path-choice">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("You must choose a path group for this type of step","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-no-step-item">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("You must select an item for this type of step","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-no-step-req-selected">
                <li class='border red-bg-400 red-border-500'>
                    <span class='icon-group white-color '>
                        <span class='button-icon font _24 sq-40  icon-sm white-bg'>
                            <span class='icon icon-cancel red-400'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _16'><?= __("You must select the right item to proceed on this quest","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-new-button-added">
                <li class='border white-bg blue-border-400'>
                    <span class='icon-group blue-grey-900'>
                        <span class='button-icon font _24 sq-40  icon-sm blue-bg-400'>
                            <span class='icon icon-check white-color'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _18 w900'><?= __("Button added!","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-new-button-added">
                <li class='border white-bg blue-border-400'>
                    <span class='icon-group blue-grey-900'>
                        <span class='button-icon font _24 sq-40  icon-sm blue-bg-400'>
                            <span class='icon icon-check white-color'></span>
                        </span>
                        <span class='icon-content'>
                            <span class='line font _18 w900'><?= __("Button added!","bluerabbit"); ?></span>
                        </span>
                    </span>
                </li>
            </div>
            <div class="hidden" id="msg-text-copied">
				<li class='border deep-purple-bg-400 purple-border-800'>
					<span class='icon-group'>
						<span class='button-icon font _24 sq-40'>
							<span class='icon white-color'></span>
						</span>
						<span class='icon-content white-color'>
							<span class='line font _16'><?= __("Copied!","bluerabbit"); ?></span>
						</span>
					</span>
				</li>
            </div>
            <div class="hidden" id="msg-no-file-selected">
				<li class='border red-bg-400 red-border-800'>
					<span class='icon-group'>
						<span class='button-icon font _24 sq-40'>
							<span class='icon icon-cancel white-color'></span>
						</span>
						<span class='icon-content white-color'>
							<span class='line font _16'><?= __("No file selected!","bluerabbit"); ?></span>
						</span>
					</span>
				</li>
            </div>
			<input type="hidden" id="br-delete-id" value="">
			<input type="hidden" id="br-trash-id" value="">
			<input type="hidden" id="br-publish-id" value="">
			<input type="hidden" id="br-draft-id" value="">
			<input type="hidden" id="trd-type" value="">
			<input type="hidden" id="trd-action" value="">

		</div>
		<!-- END Message inputs -->
		<div class="svg-container">
			<svg class="hex-clip-svg" style="width: 0; height: 0; overflow: hidden;">
				<defs>
					<clipPath id="hex-clip-svg" clipPathUnits="objectBoundingBox">
						<polygon points="0.5 0, 1 0.25, 1 0.75, 0.5 1, 0 0.75, 0 0.25" />
					</clipPath>
				</defs>
			</svg>  	
			<svg class="hex-clip-flat-svg" style="width: 0; height: 0; overflow: hidden;">
				<defs>
					<clipPath id="hex-clip-flat-svg" clipPathUnits="objectBoundingBox">
						<polygon points="0.25 0, 0.75 0, 1 0.5, 0.75 1, 0.25 1, 0 0.5" />
					</clipPath>
				</defs>
			</svg>  	
			<svg class="hex-clip-long-flat-svg" style="width: 0; height: 0; overflow: hidden;">
				<defs>
					<clipPath id="hex-clip-long-flat-svg" clipPathUnits="objectBoundingBox">
						<polygon points="0.175 0, 0.825 0, 1 0.5, 0.825 1, 0.175 1, 0 0.5" />
					</clipPath>
				</defs>
			</svg>  	
			<svg class="hex-clip-longer-flat-svg" style="width: 0; height: 0; overflow: hidden;">
				<defs>
					<clipPath id="hex-clip-longer-flat-svg" clipPathUnits="objectBoundingBox">
						<polygon points="0.125 0, 0.875 0, 1 0.5, 0.875 1, 0.125 1, 0 0.5" />
					</clipPath>
				</defs>
			</svg>  	
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-badge">
						<path fill="#FFFFFF" stroke="#000000" stroke-width="1.5794" stroke-miterlimit="10"  d="M145.67,39.61,110.39,4.33A14.76,14.76,0,0,0,100,0H50.05A14.76,14.76,0,0,0,39.61,4.33L4.33,39.61A14.76,14.76,0,0,0,0,50.05V100a14.76,14.76,0,0,0,4.33,10.44l35.28,35.28A14.76,14.76,0,0,0,50.05,150H100a14.76,14.76,0,0,0,10.44-4.33l35.28-35.28A14.76,14.76,0,0,0,150,100V50.05A14.76,14.76,0,0,0,145.67,39.61Z"/>
					</clippath>
				</defs>
			</svg>
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-rank">
						<path fill="#FFFFFF" stroke="#000000" stroke-width="1.5794" stroke-miterlimit="10"  d="M18.31,10.7V112.75A10.72,10.72,0,0,0,23.66,122l46,26.55a10.71,10.71,0,0,0,10.7,0l46-26.55a10.72,10.72,0,0,0,5.35-9.27V10.7A10.7,10.7,0,0,0,121,0H29A10.7,10.7,0,0,0,18.31,10.7Z"/>
					</clippath>
				</defs>
			</svg>
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-path">
						<path fill="#FFFFFF" stroke="#000000" stroke-width="1.5794" stroke-miterlimit="10" d="M66.26,51.53V22.51c0-2.86-1.52-5.5-4-6.93L37.13,1.07c-2.48-1.43-5.52-1.43-8,0L4,15.58c-2.48,1.43-4,4.07-4,6.93v29.02c0,2.86,1.52,5.5,4,6.93l25.13,14.51c2.48,1.43,5.52,1.43,8,0l25.13-14.51c2.48-1.43,4-4.07,4-6.93Z"/>
					</clippath>
				</defs>
			</svg>
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-consumable">
						<path fill="#FFFFFF" stroke="#000000" stroke-width="1.5794" stroke-miterlimit="10"  d="M133.31,48.05l-2.17,1.26a60.55,60.55,0,0,0-3.31-5.76L130,42.3a6.69,6.69,0,0,0,0-11.59L78.34.9a6.65,6.65,0,0,0-6.68,0L20,30.71A6.69,6.69,0,0,0,20,42.3l2.16,1.25a60.55,60.55,0,0,0-3.31,5.76l-2.17-1.26a6.69,6.69,0,0,0-10,5.79v59.64a6.71,6.71,0,0,0,3.35,5.8l51.65,29.81a6.68,6.68,0,0,0,10-5.79v-2.88c1.1.06,2.21.09,3.32.09s2.22,0,3.32-.09v2.88a6.68,6.68,0,0,0,10,5.79L140,119.28a6.71,6.71,0,0,0,3.35-5.8V53.84A6.69,6.69,0,0,0,133.31,48.05Z"/>
					</clippath>
				</defs>
			</svg>
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-key">
						<path d="M133.81,120.61,103.12,6.09A8.22,8.22,0,0,0,93.06.28L22,19.32a8.23,8.23,0,0,0-5.81,10.07L46.88,143.91a8.22,8.22,0,0,0,10.06,5.81l71.06-19A8.23,8.23,0,0,0,133.81,120.61Z"/>
					</clippath>
				</defs>
			</svg>
			<svg width="0" height="0">
				<defs>
					<clippath id="mask-reward">
						<rect x="25" width="100" height="150" rx="8"/>
					</clippath>
				</defs>
			</svg>
		</div>
		<div class="profile-box" id="profile-box">
			<div class="layer deep-bg fixed sq-full black-bg opacity-50 top left" onClick="activate('#profile-box');"></div>
			<div class="profile-box-container layer overlay">
				<div class="layer background black-bg absolute sq-full top left" style= "background-image: url(<?= get_bloginfo('stylesheet_directory')."/images/profile-box-bg.png"; ?>); "></div>
				<button class="layer foreground absolute top-10 left-10 font _20 button-icon transparent-bg opacity-60" onClick="activate('#profile-box');">
					<span class="icon icon-cancel"></span>
				</button>
				<button class="layer foreground absolute top-10 right-10 font _20 button-icon transparent-bg opacity-60" id="logout-button" onClick="activate('#confirm-logout');">
					<span class="icon icon-power"></span>
				</button>

				<div class="layer base relative white-color text-center">
					<div class="w-full padding-20 text-center" id="current-rank">
						<?php if(isset($myRank)){?>
						<button class="form-ui transparent-bg" onClick="loadAchievementCard(<?= $myRank->achievement_id; ?>);">
							<span class="layer background absolute white-bg opacity-10 background-badge"></span>
							<span class="layer base button-icon badge-icon sq-40 font _22 <?= $myRank->achievement_color ? $myRank->achievement_color : $adventure->adventure_color;?>-bg-400">
								<span class="icon icon-rank perfect-center absolute"></span>
							</span>
							<span class="relative layer base text"><?= $myRank->achievement_name ? $myRank->achievement_name : $adventure->adventure_nickname;?></span>
						</button>
						<?php } ?>
					</div>
					<div class="status" <?php if($current_player->player_picture != ''){ ?>style="background-image: url(<?= $current_player->player_picture; ?>);"<?php } ?> id="status-animated-chart">
						<a href="<?= get_bloginfo('url')."/my-account/"; ?>" class="relative block">
							<img class="rotate-L-20" src="<?= get_bloginfo('template_directory')."/images/4.png";?>">
							<img class="rotate-R-30" src="<?= get_bloginfo('template_directory')."/images/3.png";?>">
							<?php if(isset($adventure) && isset($current_player->player_level)){ ?>
								<?php if($current_player->player_level > 11){ $level_img = 'max';}else{$level_img = $current_player->player_level;} ?>
								<img class="rotate-L-90" src="<?= get_bloginfo('template_directory')."/images/level-$level_img.png";?>">
							<?php } ?>
							<img class="rotate-L-40" src="<?= get_bloginfo('template_directory')."/images/2.png";?>">
							<img class="rotate-R-120" src="<?= get_bloginfo('template_directory')."/images/1.png";?>">
						</a>
					</div>
					<div class="w-full padding-10 text-center">
						<h1 class="font _40 w100 white-color">
							<span id="status-player-display-name"><?= $current_player->player_display_name; ?></span>
							<a href="<?= get_bloginfo('url')."/my-account/"; ?>" class="opacity-30 font _16">
								<span class="icon icon-edit"></span>
							</a>
						</h1>
						<?php if(isset($adventure) && isset($current_player->player_level) ){ ?>
							<h3 class="font _24 w900 white-color" id="status-player-level"><?= "LV ".$current_player->player_level; ?></h3>
						<?php } ?>
					</div>
					<?php if(isset($adventure) && isset($current_player->player_level)){ ?>
						<div class="status-stats" id="status-stats">
							<div class="stat w-full" id="status-xp">
								<div class="stat-legend font _14">
									<div class="left-legend w-half text-left pull-left uppercase font w900">
										<span class="icon icon-star"></span> <?= $xp_long_label; ?>
									</div>
									<div class="right-legend w-third text-right pull-right">
										<strong><?= toMoney($current_player->player_xp); ?></strong> <span class="font condensed kerning-1"> / <?= toMoney($nextLevel); ?></span>
									</div>
								</div>
								<div class="progress-bar gradient-xp-bar relative w-full">
									<div class="progress layer base black-bg opacity-60" style="width: <?= 100-(round($percXP,3));?>%"></div>
								</div>
							</div>
							<div class="stat w-full" id="status-bloo">
								<div class="stat-legend font _14 padding-5">
									<div class="left-legend w-half text-left pull-left uppercase font w900">
										<span class="icon icon-bloo"></span> <?= $bloo_long_label; ?>
									</div>
									<div class="right-legend w-third text-right pull-right">

										<strong><?= toMoney($player['bloo'],"$"); ?></strong> <span class="font condensed kerning-1"></span> /
										<strong><?= toMoney($player['totalEarned'],"$"); ?></strong> <span class="font condensed kerning-1"><?= __("earned","bluerabbit"); ?></span>
									</div>
								</div><br>
								<div class="progress-bar relative w-full">
									<div class="layer background absolute sq-full white-bg opacity-10"></div>
									<div class="progress layer base light-green-bg-400 border rounded-max" style="width: <?= round($percBLOO,3);?>%"></div>
								</div>
							</div>
							<?php if(($use_encounters) && isset($adventure)){ ?>
								<div class="stat w-full" id="status-ep">
									<div class="stat-legend font _14 padding-5">
										<div class="left-legend w-half text-left pull-left uppercase font w900">
											<span class="icon icon-activity"></span> <?= $ep_long_label; ?>
										</div>
										<div class="right-legend w-third text-right pull-right" id="status-player-ep">
											<input type="hidden" id="" class="end-value" value="<?= $current_player->player_ep; ?>" >
											<strong class="number"><?= $current_player->player_ep; ?></strong> / <span class="font condensed kerning-1"><?= $maxEP; ?></span>
										</div>
									</div><br>
									<div class="progress-bar relative w-full">
										<div class="layer background absolute sq-full white-bg opacity-10"></div>
										<div class="progress layer base cyan-bg-A400 border rounded-max" id="profile-box-ep-progress-bar" style="width: <?= round($percEP,3);?>%"></div>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if((!isset($adventure))){ ?>
						<button class="button-icon red-bg-400 button-logout" onClick="activate('#confirm-logout');">
							<span class="halo rotate-L-40"></span><span class="icon icon-power white-color"></span>
						</button>
					<?php } ?>
				</div>
			</div>
		</div>

		<audio id="ui-touch-milestone" src="<?=get_bloginfo('template_directory')."/audio/milestone-on.mp3";?>"></audio>
		<audio id="ui-touch-milestone-reverse" src="<?=get_bloginfo('template_directory')."/audio/milestone-reverse.mp3";?>"></audio>
		<audio id="ui-touch-milestone-blocked" src="<?=get_bloginfo('template_directory')."/audio/milestone-blocked.mp3";?>"></audio>
	
		<?php if(isset($adventure) && is_page('adventure')){
			include (get_stylesheet_directory() . '/tutorials/tutorial-journey.php');
		}
        ?>
        <?php if(!$current_player->player_hide_intro && !$adventure->adventure_instructions && is_page('adventure')){ ?>
            <script>
               tour.start();
            </script>
        <?php } ?>
		<?php if(is_page('adventures')){
			include (get_stylesheet_directory() . '/tutorials/tutorial-adventures-list.php');
		}
		?>
		<?php if(is_page('adventures') && !br_has_seen_tutorial('adventures-list')){ ?>
			<script>
			   tour.start();
			</script>
		<?php } ?>
		<?php if(isset($q) && $q && is_page('quest')){
			include (get_stylesheet_directory() . '/tutorials/tutorial-quest.php');
		}
		?>
		<?php if(isset($q) && $q && is_page('quest') && !br_has_seen_tutorial('quest')){ ?>
			<script>
			   tour.start();
			</script>
		<?php } ?>
		<?php if(isset($challenge_data) && $challenge_data && is_page('challenge')){
			include (get_stylesheet_directory() . '/tutorials/tutorial-challenge.php');
		}
		?>
		<?php if(isset($challenge_data) && $challenge_data && is_page('challenge') && !br_has_seen_tutorial('challenge')){ ?>
			<script>
			   tour.start();
			</script>
		<?php } ?>
		<?php if(isset($m) && $m && is_page('mission')){
			include (get_stylesheet_directory() . '/tutorials/tutorial-mission.php');
		}
		?>
		<?php if(isset($m) && $m && is_page('mission') && !br_has_seen_tutorial('mission')){ ?>
			<script>
			   tour.start();
			</script>
		<?php } ?>
		<?php
		$br_builder_pages = array('new-quest','new-challenge','new-mission','new-survey','new-adventure','new-achievement','new-speaker','new-session','new-guild','new-blocker','new-lore','new-blog-post','new-item');
		foreach($br_builder_pages as $br_builder_slug){
			if(is_page($br_builder_slug)){
				include (get_stylesheet_directory() . "/tutorials/tutorial-$br_builder_slug.php");
				if(!br_has_seen_tutorial($br_builder_slug)){
					?><script>tour.start();</script><?php
				}
				break;
			}
		}
		?>
		<input type="hidden" id="url" value="<?= get_bloginfo('url');?>">
		<footer class="taskbar" id="taskbar">
			<div class="show-on-start core-nav" id="core-nav">
				<?php if(isset($adventure)){ ?>
					<a class="button-icon deep-purple-bg-400" id="journey-btn" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id"; ?>">
						<span class="icon icon-journey white-color perfect-center"></span>
					</a>
					<?php if(isset($allow_magic_codes) && $allow_magic_codes==true){ ?>
						<button class="button-icon amber-bg-400" id="magic-code-btn" onClick="showOverlay('#magic-code-form');">
							<span class="icon icon-qr deep-purple-800"></span>
						</button>
					<?php } ?>
					<?php if($use_item_shop){ ?>
						<a class="button-icon pink-bg-400" id="item-shop-btn" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>">
							<span class="icon icon-shop white-color perfect-center"></span>
						</a>
					<?php } ?>
                    <a class="button-icon grey-bg-900" id="microsoft-teams-btn" target="_blank" href="https://teams.microsoft.com/l/channel/19:thYSSRaXLdhbiZBlDt6GmAiCAhfclbur-NLwVwD1a4c1@thread.tacv2?tenantId=f66fae02-5d36-495b-bfe0-78a6ff9f8e6e">
                        <img src="<?= get_bloginfo('template_directory')."/images/app-icons/microsoft-teams.png"; ?>">
                    </a>
                    <?php
                    // Cooper: map adventure_id → Cooper client slug
                    // Add entries here for each adventure that has Cooper enabled
                    $cooper_slug = 'unilever-digital-factory';
                    if ($cooper_slug): ?>
                    <button class="button-icon blue-bg-800" id="cooper-support-btn" onclick="openCooperModal()">
                        <img src="<?= get_bloginfo('template_directory')."/images/cooper-white.png"; ?>">
                    </button>
                    <?php endif; ?>
					<button class="button-icon orange-bg-400" id="contact-admin-btn" onClick="showOverlay('#contact-admin-form');" title="<?php _e('Contact Admin','bluerabbit'); ?>">
						<span class="icon icon-comment white-color"></span>
					</button>
					<?php if($use_encounters){ ?>
						<button class="button-icon cyan-bg-A400" id="random-encounter-btn" onClick="randomEncounter();">
							<span class="icon icon-activity grey-900"></span>
						</button>
					<?php } ?>
				<?php } ?>
			</div>
			<button type="button" class="start-button" id="start-button" onClick="activateStartMenu();">
				<span class="idle-text"><?= __("OPTIONS","bluerabbit"); ?></span>
				<span class="active-text"><?= __("CLOSE","bluerabbit"); ?></span>
			</button>
		</footer>
		<?php wp_enqueue_media();?>
		<?php wp_footer(); ?>

		<?php if (!empty($cooper_slug)): ?>
		<!-- Cooper Support Modal -->
		<div id="cooper-modal">
			<div id="cooper-backdrop" onclick="closeCooperModal()"></div>
			<div id="cooper-panel">
				<button id="cooper-close" onclick="closeCooperModal()" title="Close">×</button>
				<iframe id="cooper-iframe" src="" frameborder="0" allow="autoplay" title="Cooper Support"></iframe>
			</div>
		</div>
		<style>
			#cooper-modal {
				display: none;
				position: fixed;
				inset: 0;
				z-index: 32000;
			}
			#cooper-modal.open { display: block; }
			#cooper-backdrop {
				position: absolute;
				inset: 0;
				background: rgba(0, 0, 0, 0.45);
				backdrop-filter: blur(2px);
			}
			#cooper-panel {
				position: absolute;
				bottom: 70px;
				right: 20px;
				width: 420px;
				height: 580px;
				border-radius: 16px;
				overflow: hidden;
				box-shadow: 0 24px 64px rgba(0, 0, 0, 0.75);
				background: #0c1929;
				animation: cooperSlideIn 0.25s ease;
			}
			@keyframes cooperSlideIn {
				from { opacity: 0; transform: translateY(20px) scale(0.97); }
				to   { opacity: 1; transform: translateY(0) scale(1); }
			}
			#cooper-close {
				position: absolute;
				top: 10px;
				right: 10px;
				z-index: 1;
				background: rgba(255,255,255,0.12);
				border: none;
				color: white;
				width: 28px;
				height: 28px;
				border-radius: 50%;
				cursor: pointer;
				font-size: 18px;
				line-height: 1;
				display: flex;
				align-items: center;
				justify-content: center;
				transition: background 0.15s;
			}
			#cooper-close:hover { background: rgba(255,255,255,0.25); }
			#cooper-iframe {
				width: 100%;
				height: 100%;
				border: none;
				display: block;
			}
			@media (max-width: 480px) {
				#cooper-panel {
					bottom: 60px;
					right: 0;
					left: 0;
					width: 100%;
					height: calc(100vh - 60px);
					border-radius: 16px 16px 0 0;
				}
			}
		</style>
		<script>
			var cooperEmbedUrl = 'https://support.bluerabbit.io/<?= esc_js($cooper_slug) ?>/?embed=1';
			var cooperLoaded = false;

			function openCooperModal() {
				var modal  = document.getElementById('cooper-modal');
				var iframe = document.getElementById('cooper-iframe');
				if (!cooperLoaded) {
					iframe.src = cooperEmbedUrl;
					cooperLoaded = true;
				}
				modal.classList.add('open');
				// Close the start menu if it's open
				if (document.getElementById('start') && document.getElementById('start').classList.contains('active')) {
					activateStartMenu();
				}
			}

			function closeCooperModal() {
				document.getElementById('cooper-modal').classList.remove('open');
			}

			// Close on Escape key
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') closeCooperModal();
			});
		</script>
		<?php endif; ?>

	</body>
</html>
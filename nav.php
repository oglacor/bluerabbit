<div class="start" id="start">
	<div class="layer background sq-full absolute" onClick="activateStartMenu();"></div>
	<div class="nav-group">
		<nav class="main-nav active" id="main-nav">
			<?php if ($isAdmin || $isGM){ ?>
				<button class="show-menu-button" onClick="showMenu('#main-nav');">
					<span class="content">
						<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/></span>
						<span class="label"><?= __("The Adventure","bluerabbit"); ?></span>
					</span>
				</button>
			<?php } ?>
			<ul>
				<?php if ($isAdmin || $isGM){ ?>
					<li class="nav-header">
						<?= __("Adventure Navigation","bluerabbit"); ?>
					</li>
				<?php } ?>
				<?php if($use_achievements){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/achievements/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-achievement.png" alt=""/></span>
								<span class="label"><?= __("Achievements","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_backpack){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-backpack.png" alt=""/></span>
								<span class="label"><?= __("Backpack","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_blockers){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/blockers/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-blocker.png" alt=""/></span>
								<span class="label"><?= __("Blockers","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_guilds){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/guilds/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-guild.png" alt=""/></span>
								<span class="label"><?= __("Guilds","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_item_shop){ ?>
				<li class="nav-button">
					<a href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adv_child_id"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-item-shop.png" alt=""/></span>
							<span class="label"><?= __("Item shop","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<?php } ?>
				<li class="nav-button">
					<a class="" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id";?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-journey.png" alt=""/></span>
							<span class="label"><?= __("Journey","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<li class="nav-button">
					<a href="<?= get_bloginfo('url')."/player-work/?adventure_id=$adv_child_id"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-journal.png" alt=""/></span>
							<span class="label"><?= __("Journal","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<?php if ($use_leaderboard){ ?>
				<li class="nav-button">
					<a href="<?= get_bloginfo('url')."/leaderboard/?adventure_id=$adv_child_id"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-leaderboard.png" alt=""/></span>
							<span class="label"><?= __("Leaderboard","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<?php } ?>
				<?php if ($allow_magic_codes){ ?>
					<li class="nav-button">
						<button class="" onClick="showOverlay('#magic-code-form');">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-magic-code.png" alt=""/></span>
								<span class="label"><?= __("Magic Code","bluerabbit"); ?></span>
							</span>
						</button>
					</li>
				<?php } ?>
				<?php if ($use_blog){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/blog/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-blog-post.png" alt=""/></span>
								<span class="label"><?= __("Posts","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if($use_lore){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/lore/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-lore.png" alt=""/></span>
								<span class="label"><?= __("Resources","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<li class="nav-button">
					<a href="<?= get_bloginfo('url')."/secrets-and-clues/?adventure_id=$adv_child_id"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-treasure-chest.png" alt=""/></span>
							<span class="label"><?= __("Secrets and clues","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<?php if ($use_schedule){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/schedule/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-schedule.png" alt=""/></span>
								<span class="label"><?= __("Schedule","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_speakers){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/speakers/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-speaker.png" alt=""/></span>
								<span class="label"><?= __("Speakers","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if($adventure->adventure_instructions){ ?>
					<li class="nav-button">
						<a class="" href="<?=get_bloginfo('url')."/about-adventure/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-story.png" alt=""/></span>
								<span class="label"><?= __("Story","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if ($use_wall){ ?>
					<li class="nav-button">
						<a href="<?= get_bloginfo('url')."/wall/?adventure_id=$adv_child_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-wall.png" alt=""/></span>
								<span class="label"><?= __("Wall","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if(!$config['default_adventure']['value']){ ?>
					<li class="nav-button highlighted">
						<a href="<?= get_bloginfo('url')."/adventures"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/></span>
								<span class="label"><?= __("Adventures","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
				<?php if (($isAdmin || $isGM) && (isset($adventure->adventure_parent) && ($adventure->adventure_parent >= 0))){ ?>
					<li class="nav-button highlighted">
						<a class="" href="<?= get_bloginfo('url')."/new-adventure/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-admin-tools.png" alt=""/></span>
								<span class="label"><?= __("Template Settings","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		</nav>
		<?php if (($isAdmin || $isGM) && (isset($adventure->adventure_parent) && ($adventure->adventure_parent >= 0))){ ?>
		<nav class="admin-nav" id="admin-nav">
			<button class="show-menu-button" onClick="showMenu('#admin-nav');">
				<span class="content">
					<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-admin-tools.png" alt=""/></span>
					<span class="label"><?= __("Admin Tools","bluerabbit"); ?></span>
				</span>
			</button>
			<ul>
				<li class="nav-header">
					<?= __("Admin Tools","bluerabbit"); ?>
				</li>
				
				<li class="nav-button">
					<a class="" href="<?= get_bloginfo('url')."/players/?adventure_id=$adv_child_id";?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-players.png" alt=""/></span>
							<span class="label"><?= __("Players","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<li class="nav-button">
					<a href="<?= get_bloginfo('url')."/achievements/?adventure_id=$adv_child_id"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-achievement.png" alt=""/></span>
							<span class="label"><?= __("Achievements","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<li class="nav-button">
					<a class="" href="<?= get_bloginfo('url')."/manage-players/?adventure_id=$adv_child_id";?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-player.png" alt=""/></span>
							<span class="label"><?= __("Manage Players","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
			</ul>
		</nav>
		<?php } elseif (($isAdmin || $isGM) && (!isset($adventure->adventure_parent))) { ?>
			<nav class="admin-nav" id="admin-nav">
				<button class="show-menu-button" onClick="showMenu('#admin-nav');">
					<span class="content">
						<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-admin-tools.png" alt=""/></span>
						<span class="label"><?= __("Admin Tools","bluerabbit"); ?></span>
					</span>
				</button>
				<ul>
					<li class="nav-header">
						<?= __("Admin Tools","bluerabbit"); ?>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/new-adventure/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-admin-tools.png" alt=""/></span>
								<span class="label"><?= __("Adventure Settings","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/builder/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-design.png" alt=""/></span>
								<span class="label"><?= __("Journey Builder","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/duplicator/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-infinity.png" alt=""/></span>
								<span class="label"><?= __("Duplicator","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button highlight yellow">
						<a class="" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-content-manager.png" alt=""/></span>
								<span class="label"><?= __("Manage Content","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/players/?adventure_id=$adv_child_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-players.png" alt=""/></span>
								<span class="label"><?= __("Players","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/report/?adventure_id=$adv_child_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-report.png" alt=""/></span>
								<span class="label"><?= __("Report","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/stats/?adventure_id=$adv_child_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-stats.png" alt=""/></span>
								<span class="label"><?= __("Stats","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button highlight green"> 
						<a class="" href="<?= get_bloginfo('url')."/adventure-summary/?adventure_id=$adv_parent_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-summary.png" alt=""/></span>
								<span class="label"><?= __("Summary","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<li class="nav-button">
						<a class="" href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adv_child_id";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-transactions.png" alt=""/></span>
								<span class="label"><?= __("Transactions","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<?php if ($isAdmin){ ?>
						<li class="nav-button highlight blue">
							<a class="" href="<?= get_bloginfo('url')."/wp-admin";?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-dashboard.png" alt=""/></span>
									<span class="label"><?= __("Admin Dashboard","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
						<li class="nav-button highlight red">
							<a class="" href="<?= get_bloginfo('url')."/config/";?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-config.png" alt=""/></span>
									<span class="label"><?= __("Sys Config","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</nav>



			<nav class="add-nav" id="add-nav">
				<button class="show-menu-button" onClick="showMenu('#add-nav');">
					<span class="content">
						<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-add-mechanics.png" alt=""/></span>
						<span class="label"><?= __("Add Mechanics","bluerabbit"); ?></span>
					</span>
				</button>
				<ul>
					<li class="nav-header">
						<?= __("Add Mechanics","bluerabbit"); ?>
					</li>
					<?php if($use_achievements){ ?>
						<li class="nav-button highlight deep-purple">
							<a class="" href="<?= get_bloginfo('url')."/new-achievement/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-achievement.png" alt=""/></span>
									<span class="label"><?= __("New Achievement","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_blockers){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-blocker/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-blocker.png" alt=""/></span>
									<span class="label"><?= __("New Blocker","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_challenges){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-challenge/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-challenge.png" alt=""/></span>
									<span class="label"><?= __("New Challenge","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_encounters){ ?>
						<li class="nav-button">
							<button onClick="loadContent('new-encounter');">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-encounter.png" alt=""/></span>
									<span class="label"><?= __("New Encounter","bluerabbit"); ?></span>
								</span>
							</button>
						</li>
					<?php } ?>
					<?php if($use_guilds){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-guild/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-guild.png" alt=""/></span>
									<span class="label"><?= __("New Guild","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_item_shop){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-item/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-item.png" alt=""/></span>
									<span class="label"><?= __("New Item","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_missions){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-mission/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-mission.png" alt=""/></span>
									<span class="label"><?= __("New Mission","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_blog){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-blog-post/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-journal.png" alt=""/></span>
									<span class="label"><?= __("New Post","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<li class="nav-button highlight blue">
						<a class="" href="<?= get_bloginfo('url')."/new-quest/?adventure_id=$adv_parent_id"; ?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-quest.png" alt=""/></span>
								<span class="label"><?= __("New Quest","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
					<?php if($use_lore){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-lore/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-lore.png" alt=""/></span>
									<span class="label"><?= __("New Resource","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_schedule){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-session/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-session.png" alt=""/></span>
									<span class="label"><?= __("New Session","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_speakers){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-speaker/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-speaker.png" alt=""/></span>
									<span class="label"><?= __("New Speaker","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
					<?php if($use_surveys){ ?>
						<li class="nav-button">
							<a class="" href="<?= get_bloginfo('url')."/new-survey/?adventure_id=$adv_parent_id"; ?>">
								<span class="content">
									<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-survey.png" alt=""/></span>
									<span class="label"><?= __("New Survey","bluerabbit"); ?></span>
								</span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</nav>
		<?php } ?>
	</div>
	

</div>

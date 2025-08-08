<div class="start" id="start">
	<div class="layer background sq-full absolute" onClick="activateStartMenu();"></div>
	
	<div class="nav-group">
		<nav class="main-nav active" id="main-nav">
			<button class="show-menu-button" onClick="showMenu('#main-nav');">
				<span class="content">
					<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/></span>
					<span class="label"><?= __("The Adventure","bluerabbit"); ?></span>
				</span>
			</button>
			<ul>
				<li class="nav-header">
					<?= __("Adventure Navigation","bluerabbit"); ?>
				</li>
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
			</ul>
		</nav>

		
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
	</div>
</div>
























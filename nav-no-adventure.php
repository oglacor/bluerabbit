<div class="start" id="start">
	<div class="layer background sq-full absolute" onClick="activateStartMenu();"></div>
	
	<div class="nav-group">
		<nav class="admin-nav active" id="admin-nav">
			<ul>
				<li class="nav-button">
					<a class="" href="<?= get_bloginfo('url')."/adventures"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/></span>
							<span class="label"><?= __("My Adventures","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<li class="nav-button">
					<a class="" href="<?= get_bloginfo('url')."/manage-adventures"; ?>">
						<span class="content">
							<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-manage-adventures.png" alt=""/></span>
							<span class="label"><?= __("Manage Adventures","bluerabbit"); ?></span>
						</span>
					</a>
				</li>
				<li class="nav-button">
					<?php if($add_adventure){ ?>
						<a class="" href="<?= get_bloginfo('url')."/new-adventure/";?>">
							<span class="content">
								<span class="image">
									<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-add-badge.png" alt=""/>
									<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/>
								</span>
								<span class="label"><?= __("New Adventure","bluerabbit"); ?></span>
							</span>
						</a>
					<?php }else{ ?>
						<a class="" href="#" disabled>
							<span class="content">
								<span class="image">
									<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-add-badge.png" alt=""/>
									<img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-adventure.png" alt=""/>
								</span>
								<span class="label"><?= __("New Adventure","bluerabbit"); ?></span>
							</span>
						</a>
					<?php } ?>
				</li>
				<?php if($f_role  == 'admin'){ ?>
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
					<li class="nav-button highlight red">
						<a class="" href="<?= get_bloginfo('url')."/projects/";?>">
							<span class="content">
								<span class="image"><img src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-project.png" alt=""/></span>
								<span class="label"><?= __("Projects","bluerabbit"); ?></span>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		</nav>
	</div>
</div>

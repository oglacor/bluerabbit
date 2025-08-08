<li class="blog-post hover-animated-bg <?= ($current_player->player_level < $b->mech_level) ? 'unavailable' : ''; ?>">
	
	<div class="layer deep-bg absolute bg-color sq-full"></div>
	<div class="layer background absolute bg-image animated-element " style="background-image: url(<?= $b->mech_badge; ?>);"></div>
	
	<div class="layer base blog-post-level">
		<span class="icon icon-level"></span><?= $b->mech_level; ?>
	</div>
	<div class="layer base absolute bottom-40 blog-post-content">
		<?php if($b->achievement_name){ ?>
			<span class="block padding-5 <?= "$b->achievement_color-bg-400";?>"><span class="icon icon-rank"></span> <?= "$b->achievement_name";?></span>
		<?php } ?>
		<?php if($current_player->player_level >= $b->mech_level){ ?>
			<a class="blog-post-headline" href="<?= get_bloginfo('url')."/blog-post/?adventure_id=$adventure->adventure_id&questID=$b->quest_id"; ?>">
				<?= $b->quest_title; ?>
			</a>
			<p class="blog-post-secondary-headline" ><?= $b->quest_secondary_headline; ?></p>
		<?php }else{ ?>
			<h3 class="blog-post-headline">
				<?= $b->quest_title; ?>
			</h3>
			<p class="blog-post-secondary-headline" ><?= __("You may find this content available when your level raises. You should work more on your challenges before looking for information that's above your current rank.","bluerabbit"); ?></p>
		<?php } ?>
	</div>
	<?php if($current_player->player_level >= $b->mech_level){ ?>
		<div class="layer base absolute bottom cta">
			<a class="" href="<?= get_bloginfo('url')."/blog-post/?adventure_id=$adventure->adventure_id&questID=$b->quest_id"; ?>">
				<?= __("Read","bluerabbit"); ?>
			</a>
		</div>
	<?php } ?>
</li>




<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
	<?php
	if($isGM || $isAdmin){
		$guilds = getAllGuilds($adventure->adventure_id); 
	}else{
		$guilds = getMyGuilds($adventure->adventure_id);
	}
	?>
<div class="layer base relative boxed w-max-1200 wall">
	<?php if($guilds || ($isGM || $isAdmin)){ ?>
		<div class="wall-nav">
			<button type="button" onClick="loadChat('public');" class="form-ui active orange-bg-800  w-full wall-nav-btn" id="message-type-public">
				<span class="icon-group w-full">
					<span class="icon-button orange-bg-400 sq-40 font _24"><span class="icon icon-socialiser"></span></span>
					<span class="icon-content">
						<span class="line font _20">
							<?= __("Public Wall","bluerabbit"); ?>
						</span>
					</span>
				</span>
			</button>
			<?php foreach($guilds as $guild){ ?>
			
				<button type="button" onClick="loadChat('guild',<?= $guild->guild_id; ?>);" class="form-ui <?= $guild->guild_color; ?>-bg-800  w-full wall-nav-btn" id="message-type-guild<?= $guild->guild_id; ?>">
					<span class="icon-group w-full">
						<span class="icon-button orange-bg-400 sq-40 font _24" style="background-image: url(<?= $guild->guild_logo; ?>); "></span>
						<span class="icon-content">
							<span class="line font _20">
								<?= $guild->guild_name; ?>
							</span>
						</span>
					</span>
				</button>
			<?php } ?>
			<?php if($isGM || $isAdmin){ ?>
				<button type="button" onClick="loadChat('system');" class="form-ui blue-bg-700  w-full wall-nav-btn" id="message-type-system">
					<span class="icon-group w-full">
						<span class="icon-button white-bg blue-700 sq-40 font _24"><span class="icon icon-socialiser"></span></span>
						<span class="icon-content">
							<span class="line font _20">
								<?= __("System","bluerabbit"); ?>
							</span>
						</span>
					</span>
				</button>
			<?php } ?>
		</div>
	<?php } ?>
	<div class="wall-content active">
		<div class="wall-content-header active" id="wall-content-header-public">
			<div class="layer background absolute sq-full" style="background-image: url(<?=$adventure->adventure_badge;?>);"></div>
			<div class="layer background absolute sq-full black-bg opacity-60"></div>
			<div class="layer absolute base v-center right  padding-10 font _36 w900 italic white-color">
				<?= __("Public Wall","bluerabbit"); ?>
			</div>
		</div>
		<?php if($guilds){ ?>
			<?php foreach($guilds as $guild){ ?>
				<div class="wall-content-header" id="wall-content-header-guild<?=$guild->guild_id;?>">
					<div class="layer background absolute sq-full" style="background-image: url(<?=$guild->guild_logo;?>);"></div>
					<div class="layer background absolute sq-full <?=$guild->guild_color;?>-bg-400 opacity-60 mix-blend-color"></div>
					<div class="layer background absolute sq-full black-bg opacity-60"></div>
					<div class="layer absolute base  v-center right  padding-10 font _36 w900 italic white-color">
						<?=  $guild->guild_name;?>
					</div>
				</div>
		
		
			<?php } ?>
		<?php } ?>
		<div class="conversation layer base relative message-feed" id="message-feed">
			<?php $announcements = getAnnouncements($adventure->adventure_id); ?>
			<?php if($announcements){ ?>
				<ul class="feed">
					<?php foreach($announcements['anns'] as $m){ ?>
						<?php include (TEMPLATEPATH . '/message.php'); ?>
					<?php } ?>
					<li class="clear"></li>
				</ul>
			<?php }else{ ?>
				<h4 class="grey c-400 text-center">- <?php _e("No messages","bluerabbit"); ?> -</h4>
			<?php } ?>
		</div>
	</div>
	<br class="clear">
</div>
<div class="reply-box w-full layer foreground fixed">
	<div class="layer absolute background opacity-50 black-bg"></div>
	<div class="layer relative base boxed max-w-900 foreground">
		<div class="text-box">
			<textarea id="message-content" class="form-ui" placeholder="<?php _e("Write Message","bluerabbit"); ?>"></textarea>
		</div>
		<div class="buttons">
			<button id="public-post-button" class="form-ui deep-orange-bg-400 pull-right" onClick="postToWall('public');">
				<span class="icon icon-wall"></span> <?php _e("Post","bluerrabit"); ?>
			</button>
			<?php if($isGM || $isAdmin){ ?>
				<button id="announcement-post-button" class="form-ui pink-bg-400 pull-left" onClick="postToWall('announcement','<?= $adventure->adventure_code; ?>');">
					<span class="icon icon-megaphone"></span> <?php _e("PA Post","bluerrabit"); ?>
				</button>
			<?php } ?>
			<?php if($guilds){ ?>
				<?php foreach($guilds as $guild){ ?>
					<button id="guild-post-button-<?=$guild->guild_id;?>" class="form-ui hidden teal-bg-400 pull-right guild-post-button" onClick="postToWall('guild',<?=$guild->guild_id;?>);">
						<span class="icon icon-guild"></span><?=$guild->guild_name;?>
					</button>
				<?php } ?>
			<?php } ?>
		</div>
		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_post_wall_nonce'); ?>"/>
	</div>
</div>

	<input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>" />
	<input type="hidden" id="reload" value="1" />
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

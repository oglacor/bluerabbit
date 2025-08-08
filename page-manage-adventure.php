<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($isGM || $isAdmin){ ?>
	<?php $manage = isset($_GET['manage']) ? $_GET['manage'] : 'journey'; ?>
	<div class="w-full padding-10 grey-bg-800">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40  orange-bg-400"><span class="icon icon-tools"></span></span>
			<span class="icon-content">
				<span class="line font _24 orange-400"><?php _e('Manage Adventure','bluerabbit'); ?></span>
				<span class="line font _14 orange-100"><?php echo __('Managing','bluerabbit')." >>> <span class='font uppercase w700'>$manage</span>"; ?> </span>
			</span>
			<span class="icon-content">
				<?php if(!$manage || $manage == 'journey') { ?>
					<button class="form-ui deep-purple-bg-400 white-color">
						<span class="icon icon-journey"></span>
						<?php _e("Journey","bluerabbit");?>
					</button>
				<?php }else{ ?>
					<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=journey";  ?>
					<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
						<span class="icon icon-journey"></span>
						<?php _e("Journey","bluerabbit");?>
					</a>
				<?php } ?>
			</span>
			<?php if($use_achievements){ ?>
				<span class="icon-content">
					<?php if($manage == 'achievements') { ?>
						<button class="form-ui purple-bg-400 white-color">
							<span class="icon icon-achievement"></span>
							<?php _e("Achievements","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=achievements";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-achievement"></span>
							<?php _e("Achievements","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_encounters){ ?>
				<span class="icon-content">
					<?php if($manage == 'encounters') { ?>
						<button class="form-ui purple-bg-400 white-color">
							<span class="icon icon-battle"></span>
							<?php _e("Encounters","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=encounters";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-battle"></span>
							<?php _e("Encounters","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_item_shop || $use_backpack ){ ?>
				<span class="icon-content">
					<?php if($manage == 'items') { ?>
						<button class="form-ui pink-bg-400 white-color">
							<span class="icon icon-basket"></span>
							<?php _e("Items","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=items";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-basket"></span>
							<?php _e("Items","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_guilds){ ?>
				<span class="icon-content">
					<?php if($manage == 'guilds') { ?>
						<button class="form-ui light-green-bg-400 white-color">
							<span class="icon icon-guild"></span>
							<?php _e("Guilds","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=guilds";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-players"></span>
							<?php _e("Guilds","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_blockers){ ?>
				<span class="icon-content">
					<?php if($manage == 'blockers') { ?>
						<button class="form-ui teal-bg-400 white-color">
							<span class="icon icon-lock"></span>
							<?php _e("Blockers","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=blockers";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-lock"></span>
							<?php _e("Blockers","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_blog){ ?>
				<span class="icon-content">
					<?php if($manage == 'blog') { ?>
						<button class="form-ui indigo-bg-400 white-color">
							<span class="icon icon-duplicate"></span>
							<?php _e("Blog","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=blog";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-duplicate"></span>
							<?php _e("Blog","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_lore){ ?>
				<span class="icon-content">
					<?php if($manage == 'lore') { ?>
						<button class="form-ui orange-bg-400 white-color">
							<span class="icon icon-narrative"></span>
							<?php _e("Lore","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=lore";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-narrative"></span>
							<?php _e("Lore","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_schedule){ ?>
				<span class="icon-content">
					<?php if($manage == 'schedule') { ?>
						<button class="form-ui indigo-bg-200 white-color">
							<span class="icon icon-time"></span>
							<?php _e("Schedule","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=schedule";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-time"></span>
							<?php _e("Schedule","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
			<?php if($use_speakers){ ?>
				<span class="icon-content">
					<?php if($manage == 'speakers') { ?>
						<button class="form-ui orange-bg-400 white-color">
							<span class="icon icon-socialiser"></span>
							<?php _e("Speakers","bluerabbit");?>
						</button>
					<?php }else{ ?>
						<?php $link = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=speakers";  ?>
						<a class="form-ui grey-bg-400 white-color" href="<?php echo $link;?>">
							<span class="icon icon-socialiser"></span>
							<?php _e("Speakers","bluerabbit");?>
						</a>
					<?php } ?>
				</span>
			<?php } ?>
		</span>
	</div>			
	<div class="container">
		<div class="body-ui w-full white-bg">
			<?php 
				$theFile =  (TEMPLATEPATH . "/manage-$manage.php");
				if(file_exists($theFile)) {
					include ($theFile);
				}else{
					include (TEMPLATEPATH . "/manage-journey.php");
				}
			?>
		</div>
	</div>
	<input type="hidden" id="reload" value="1">
	<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
	<input type="hidden" id="empty-trash-nonce" value="<?php echo wp_create_nonce('empty_trash_nonce'.$current_user->ID); ?>" />
	<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>" />
	<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
	<input type="hidden" id="draft-nonce" value="<?php echo wp_create_nonce('draft_nonce'); ?>" />
	<input type="hidden" id="duplicator_nonce" value="<?php echo wp_create_nonce('duplicate_nonce'); ?>"/>
	<input type="hidden" id="achievement-nonce" value="<?php echo wp_create_nonce('achievement_nonce'); ?>" />
	<input type="hidden" id="guild-nonce" value="<?php echo wp_create_nonce('guild_nonce'); ?>" />
	<input type="hidden" id="xp-nonce" value="<?php echo wp_create_nonce('xp_nonce'); ?>" />
	<input type="hidden" id="ep-nonce" value="<?php echo wp_create_nonce('ep_nonce'); ?>" />
	<input type="hidden" id="bloo-nonce" value="<?php echo wp_create_nonce('bloo_nonce'); ?>" />
	<input type="hidden" id="level-nonce" value="<?php echo wp_create_nonce('level_nonce'); ?>" />
	<input type="hidden" id="start-date-nonce" value="<?php echo wp_create_nonce('start_date_nonce'); ?>" />
	<input type="hidden" id="deadline-nonce" value="<?php echo wp_create_nonce('deadline_nonce'); ?>" />
	<input type="hidden" id="title-nonce" value="<?php echo wp_create_nonce('title_nonce'); ?>" />
	<input type="hidden" id="display-style-nonce" value="<?php echo wp_create_nonce('display_style_nonce'); ?>" />
	<input type="hidden" id="quest-type-nonce" value="<?php echo wp_create_nonce('quest_type_nonce'); ?>" />
	<input type="hidden" id="set-speaker-nonce" value="<?php echo wp_create_nonce('speaker_nonce'); ?>" />
	<input type="hidden" id="break-parent-nonce" value="<?php echo wp_create_nonce('break_parent_nonce'); ?>" />
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
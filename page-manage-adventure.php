<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($isGM || $isAdmin){ ?>
	<?php $manage = isset($_GET['manage']) ? $_GET['manage'] : 'journey'; ?>
	<?php
	$manage_base = get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id&manage=";
	$manage_tabs = [
		['key'=>'journey',      'icon'=>'journey',     'label'=>__("Journey","bluerabbit"),      'show'=>true],
		['key'=>'achievements', 'icon'=>'achievement', 'label'=>__("Achievements","bluerabbit"), 'show'=>!empty($use_achievements)],
		['key'=>'encounters',   'icon'=>'battle',      'label'=>__("Encounters","bluerabbit"),   'show'=>!empty($use_encounters)],
		['key'=>'items',        'icon'=>'basket',      'label'=>__("Items","bluerabbit"),        'show'=>!empty($use_items) || !empty($use_backpack)],
		['key'=>'guilds',       'icon'=>'guild',       'label'=>__("Guilds","bluerabbit"),       'show'=>!empty($use_guilds)],
		['key'=>'tabis',        'icon'=>'sabotage',    'label'=>__("Tabis","bluerabbit"),        'show'=>true],
		['key'=>'blockers',     'icon'=>'lock',        'label'=>__("Blockers","bluerabbit"),     'show'=>!empty($use_blockers)],
		['key'=>'blog',         'icon'=>'duplicate',   'label'=>__("Blog","bluerabbit"),         'show'=>!empty($use_blog)],
		['key'=>'lore',         'icon'=>'narrative',   'label'=>__("Lore","bluerabbit"),         'show'=>!empty($use_lore)],
		['key'=>'schedule',     'icon'=>'time',        'label'=>__("Schedule","bluerabbit"),     'show'=>!empty($use_schedule)],
		['key'=>'speakers',     'icon'=>'socialiser',  'label'=>__("Speakers","bluerabbit"),     'show'=>!empty($use_speakers)],
		['key'=>'requests',     'icon'=>'mail',        'label'=>__("Requests","bluerabbit"),     'show'=>true],
	];
	?>
	<div class="br-tabs br-tabs-sticky">
		<?php foreach($manage_tabs as $tab){ ?>
			<?php if($tab['show']){ ?>
				<?php if($manage === $tab['key']){ ?>
					<button class="br-tab-btn active">
						<span class="icon icon-<?= $tab['icon']; ?>"></span> <?= $tab['label']; ?>
					</button>
				<?php }else{ ?>
					<a href="<?= $manage_base . $tab['key']; ?>" class="br-tab-btn">
						<span class="icon icon-<?= $tab['icon']; ?>"></span> <?= $tab['label']; ?>
					</a>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</div>

	<?php
		$theFile = (TEMPLATEPATH . "/manage-$manage.php");
		if(file_exists($theFile)) {
			include ($theFile);
		}else{
			include (TEMPLATEPATH . "/manage-journey.php");
		}
	?>
	<input type="hidden" id="reload" value="1">
	<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
	<input type="hidden" id="empty-trash-nonce" value="<?php echo wp_create_nonce('empty_trash_nonce'.$current_user->ID); ?>" />
	<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>" />
	<input type="hidden" id="locked-nonce" value="<?php echo wp_create_nonce('locked_nonce'); ?>" />
	<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
	<input type="hidden" id="draft-nonce" value="<?php echo wp_create_nonce('draft_nonce'); ?>" />
	<input type="hidden" id="duplicator_nonce" value="<?php echo wp_create_nonce('duplicate_nonce'); ?>"/>
	<input type="hidden" id="achievement-nonce" value="<?php echo wp_create_nonce('achievement_nonce'); ?>" />
	<input type="hidden" id="quest-tabi-nonce" value="<?php echo wp_create_nonce('quest_tabi_nonce'); ?>" />
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
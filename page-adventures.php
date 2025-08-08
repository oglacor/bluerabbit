<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php
unset($_SESSION['adventure']);
if($config['default_adventure']['value']>0){
	?><script>document.location.href="<?= get_bloginfo('url')."/adventure/?adventure_id={$config['default_adventure']['value']}";?>"; </script><?php
}
$player_search = ($roles[0] == 'administrator' && $_GET['player_id']) ? $_GET['player_id'] : $current_user->ID;
$show_public_adventures = $config['adventure_privacy']['value'] ? " OR (adventures.adventure_privacy = 'public' AND adventures.adventure_status = 'publish') " : "";

$adventures_sql ="SELECT
	adventures.*, 
	owner.player_nickname, 
	player.player_last_login, player.player_adventure_role, player.player_id, player.player_xp, player.player_bloo,  player.player_ep, player.player_level, player.player_gpa
	
	FROM {$wpdb->prefix}br_adventures adventures

	LEFT JOIN {$wpdb->prefix}br_players owner ON adventures.adventure_owner = owner.player_id
	
	LEFT JOIN {$wpdb->prefix}br_player_adventure player ON adventures.adventure_id = player.adventure_id AND player.player_id=$player_search AND player.player_adventure_status='in'
	
	WHERE 
	(adventures.adventure_status = 'publish' AND adventures.adventure_owner=$player_search)
	OR (adventures.adventure_status = 'publish' AND player.player_id=$player_search AND player.player_adventure_status='in')
	$show_public_adventures
	GROUP BY adventures.adventure_id 
	ORDER BY adventures.adventure_title, adventures.adventure_id
	LIMIT %d,%d
";

$page = isset($_GET['cp']) ? $_GET['cp'] : 1;
$offset = ($page - 1) * 12;
$prepared_query = $wpdb->prepare($adventures_sql, $offset, 12);
$query = $wpdb->query($prepared_query);
$adventures = $wpdb->last_result;

$total_advs = $wpdb->query($wpdb->prepare($adventures_sql, 0, 1000));
$total_advs = $wpdb->last_result;
$total_pages = ceil(count($total_advs)/12);
?>
<div class="page-header">
	<h1 class="white-color"><?= __("My Adventures","bluerabbit"); ?></h1>
</div>

<div class="adventures">
	<ul>
		<?php if($adventures){ ?>
			<?php foreach($adventures as $key=>$adv){ ?>
				<?php include (TEMPLATEPATH . '/adventure.php'); ?>
			<?php } ?>
		<?php }else{ ?>

		<?php } ?>
		
		<?php if(isset($add_adventure) || isset($add_from_template)){ ?>
			<?php if($add_adventure == true){ ?>
			<li class="adventure add-new">
				<a class="" href="<?= get_bloginfo('url'); ?>/new-adventure/">
					<span class="icon icon-add"></span><br>
					<span class="hex-text"><?= __("New Adventure","bluerabbit"); ?></span>
				</a>
			</li>
			<?php } ?>
			<?php if($add_from_template == true){ ?>
			<li class="adventure add-new from-template">
				<button class="" onClick="loadContent('library');">
					<span class="icon icon-add"></span><br>
					<span class="hex-text"><?= __("New Adventure From Template","bluerabbit"); ?></span>
				</button>
			</li>
			<?php } ?>
		<?php } ?>
		
	</ul>
</div>
<div class="pages-nav">
	<ul>
	<?php if($page > 1){ ?>
		<li class="page-button">
			<a href="<?php echo get_bloginfo('url')."/adventures/?cp=".($page-1);?>">
				<span class="icon icon-arrow-left"></span>
			</a>
		</li>
		<?php for($i=1; $i<$page; $i++){ ?>
			<li class="page-button">
				<a href="<?php echo get_bloginfo('url')."/adventures/?cp=$i";?>">
					<?= $i; ?>
				</a>
			</li>
		<?php } ?>
	<?php } ?>
		<li class="page-button current"><?= $page; ?></li>
	<?php if($total_pages > $page){ ?>
		<?php for($i=$page+1; $i<=$total_pages; $i++){ ?>
			<li class="page-button">
				<a href="<?php echo get_bloginfo('url')."/adventures/?cp=$i";?>">
					<?= $i; ?>
				</a>
			</li>
		<?php } ?>
		<li class="page-button">
			<a href="<?php echo get_bloginfo('url')."/adventures/?cp=".($page+1);?>">
				<span class="icon icon-arrow-right"></span>
			</a>
		</li>
	<?php } ?>
	</ul>
</div>
	<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>"/>
	<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>"/>
	<input type="hidden" id="draft-nonce" value="<?php echo wp_create_nonce('draft_nonce'); ?>"/>
	<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>"/>
	<input type="hidden" id="reload" value="true"/>
	
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
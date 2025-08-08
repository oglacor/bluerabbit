<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

	if($_GET['blog_page']){
		$offset = "OFFSET ".($_GET['blog_page'] - 1 )*20;
	}else{
		$offset = "";
	}
	$myAchievements = getMyAchievements($adventure->adventure_id);
	$a_ids=(implode(",",$myAchievements)); 

	$hide_quests = $adventure->adventure_hide_quests ? $adventure->adventure_hide_quests : "";
	if ($adventure->adventure_gmt){ date_default_timezone_set($adventure->adventure_gmt); }
	$today = date('Y-m-d H:i:s');
	$date_condition = $hide_quests ? "AND blog.mech_start_date <= '$today'" : "";
	if($a_ids){ $condition = "blog.achievement_id IN ($a_ids) OR "; }

	$query = "SELECT * FROM {$wpdb->prefix}br_quests blog
	
	LEFT JOIN  {$wpdb->prefix}br_achievements achievements
	ON blog.achievement_id = achievements.achievement_id 

	WHERE blog.adventure_id=$adventure->adventure_id AND blog.quest_status='publish' AND blog.mech_level <= $current_player->player_level  AND ($condition blog.achievement_id=0)  AND blog.quest_type='lore' $date_condition

	ORDER BY blog.quest_order, blog.quest_id DESC LIMIT 20 $offset";

	$total_pages = $wpdb->get_row("SELECT COUNT(*) FROM {$wpdb->prefix}br_quests blog WHERE blog.quest_status='publish' AND ($condition blog.achievement_id=0) AND blog.quest_type='lore' ORDER BY blog.quest_id DESC ","ARRAY_N");
	$total_pages = ceil($total_pages[0]/20);
	$blog = $wpdb->get_results($query); 

?>
<?php if($isGM || $isAdmin){ ?>
<div class="text-center padding-10 layer base relative">
	<a href="<?= get_bloginfo('url')."/new-lore/?adventure_id=$adventure->adventure_id"; ?>" class="form-ui button blue-bg-400 font _16 ">
		<span class="icon icon-add"></span><?= __("New Resource","bluerabbit"); ?>
	</a>
</div>
<?php } ?>
<?php if($blog){ ?>
	
	<div class="container boxed max-w-1200 sticky top layer foreground">
		<div class="input-group w-full">
			<input type="text" id="search" class="form-ui w-full" onChange="searchLore();" placeholder="<?= __("Search","bluerabbit"); ?>">
			<label class="teal-bg-400 white-color"><button class="form-ui teal-bg-500 white-color" onClick="searchLore();"><?= __("Go","bluerabbit"); ?></button></label>
			<label class="red-bg-400">
				<a class="form-ui red-bg-500" href="<?= get_bloginfo('url')."/lore/?adventure_id=$adventure->adventure_id"; ?>"><?= __("Clear Search","bluerabbit"); ?></a>
			</label>
		</div>
	</div>
	<div class="container boxed max-w-1200 relative layer base" id="lore-content">
		<?php foreach($blog as $key=>$b){ ?>
			<?php include (TEMPLATEPATH . "/lore-item.php"); ?>
		<?php } ?>
	</div>
	<div class="container boxed max-w-1200 relative layer base">
		<div class="text-center blog-posts-nav">
			<?php if($_GET['blog_page'] > 1){ ?>
				<?php $prev_page = ($_GET['blog_page']-1); ?>
				<a class="form-ui pull-left blue-grey" href="<?php echo get_bloginfo('url')."/lore/?adventure_id=$adventure->adventure_id&blog_page=$prev_page"; ?>">
					<?php _e('Prev Page',"bluerabbit");?>
				</a>
			<?php } ?>
			<?php if($_GET['blog_page'] < $total_pages && $total_pages >1){ ?>
				<?php if(!$_GET['blog_page']){ 
					$next_page=2; 
				}else{
					$next_page = ($_GET['blog_page']+1); 
				}?>
				<a class="form-ui pull-right blue-grey" href="<?php echo get_bloginfo('url')."/lore/?adventure_id=$adventure->adventure_id&blog_page=$next_page"; ?>">
					<?php _e('Next Page',"bluerabbit");?>
				</a>
			<?php } ?>
	   </div>
	</div>
	<?php if(isset($_GET['lore_id'])){ ?>
		<script>loadLore(<?= $_GET['lore_id']; ?>);</script>
	<?php } ?>

<?php }else{ ?>
	<div class="container boxed max-w-1200">
		<div class="highlight text-center font _24 w300 grey-400">
			<span class="icon icon-cancel"></span><?php _e("No resources available","bluerabbit"); ?>
		</div>
	</div>
<?php } ?>


<?php if($isGM || $isAdmin){ ?>
	<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>


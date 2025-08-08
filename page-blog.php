<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php

	if($_GET['blog_page']){
		$offset = ($_GET['blog_page'] - 1 )*10;
	}else{
		$offset = 0;
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

	WHERE blog.adventure_id=$adventure->adventure_id AND blog.quest_status='publish'  AND ($condition blog.achievement_id=0)  AND blog.quest_type='blog-post' $date_condition

	ORDER BY blog.quest_order, blog.quest_id DESC LIMIT $offset,10 ";

	$total_pages = $wpdb->get_row("SELECT COUNT(*) FROM {$wpdb->prefix}br_quests blog WHERE blog.quest_status='publish' AND ($condition blog.achievement_id=0) AND blog.quest_type='blog-post' ORDER BY blog.quest_id DESC ","ARRAY_N");

	$total_pages = ceil($total_pages[0]/10);

	$blog = $wpdb->get_results($query); 

/////   AND blog.mech_level <= $current_player->player_level
?>
<?php if($blog){ ?>

	<div class="container boxed max-w-1200">
		<div class="content w-full">
			<ul class="blog">
				<?php foreach($blog as $key=>$b){ ?>
					<?php include (TEMPLATEPATH . "/blogpost.php"); ?>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container boxed max-w-1200">
		<div class="row text-center blog-posts-nav">
			<?php if($_GET['blog_page'] > 1){ ?>
				<?php $prev_page = ($_GET['blog_page']-1); ?>
				<a class="form-ui pull-left blue-grey" href="<?php echo get_bloginfo('url')."/blog/?adventure_id=$adventure->adventure_id&blog_page=$prev_page"; ?>">
					<?php _e('Previous ten',"bluerabbit");?>
				</a>
			<?php } ?>
			<?php if($_GET['blog_page'] < $total_pages && $total_pages >1){ ?>
				<?php if(!$_GET['blog_page']){ 
					$next_page=2; 
				}else{
					$next_page = ($_GET['blog_page']+1); 
				}?>
				<a class="form-ui pull-right blue-grey" href="<?php echo get_bloginfo('url')."/blog/?adventure_id=$adventure->adventure_id&blog_page=$next_page"; ?>">
					<?php _e('Next ten',"bluerabbit");?>
				</a>
			<?php } ?>
	   </div>
	</div>

<?php }else{ ?>
	<div class="container boxed max-w-1200">
		<div class="highlight text-center font _24 w300 grey-400">
			<span class="icon icon-cancel"></span><?php _e("No posts available","bluerabbit"); ?>
		</div>
	</div>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>


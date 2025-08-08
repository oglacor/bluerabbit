<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$questID =  $_GET['questID'];
	$b = $wpdb->get_row("SELECT a.*,b.achievement_name, b.achievement_color FROM {$wpdb->prefix}br_quests a
	LEFT JOIN {$wpdb->prefix}br_achievements b ON a.achievement_id=b.achievement_id
	WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id = $questID");

	$achievements = getMyAchievements($adventure->adventure_id);

if($b->achievement_id && !in_array($b->achievement_id, $achievements) ){
	unset($b);
}

?>
<?php if($b){ ?>
	<div class="background black-bg opacity-80 fixed"></div>
    
	<div class="container boxed max-w-1200 foreground wrap">
        <div class="w-full relative  blog-head foreground">
			<div class="padding-20 text-left relative blue-grey-bg-900 white-color" >
                <input type="hidden" value="<?= $b->quest_id; ?>" id="the_quest_id">
                <input type="hidden" value="<?= $b->quest_id; ?>" id="the_blogpost_id">
				<a href="<?= get_bloginfo('url')."/blog/?adventure_id=$adventure->adventure_id"; ?>">
					<span class="icon icon-story"></span>
					<span class="label"><?= __("Back to more stories","bluerabbit"); ?></span>
				</a> > 
				<h1 class="font w700 _40 foreground">
					<?= $b->quest_title; ?>
				</h1>
			</div>
        </div>
        <div class="body-ui white-color padding-20 font _20 w300">
            <div class="content" id="blog-post-content">
                <?= apply_filters('the_content',$b->quest_content); ?>
            </div>
        </div>
	</div>
    <?php if($isGM){ ?>
        <div class="container foreground">
            <div class="footer-ui sticky-bottom text-center">
                <a class="form-ui green-bg-400" href="<?= get_bloginfo('url').'/new-blog-post/?adventure_id='.$adventure_id."&questID=".$b->quest_id;?>"><span class="icon icon-edit"></span> <?php _e("Edit","bluerabbit"); ?></a>
                <button class="form-ui red-bg-400" onClick="br_confirm_trd('trash',<?= $b->quest_id; ?>,'blog-post');"><span class=" icon icon-trash"></span> <?php _e("Trash","bluerabbit"); ?></button>
                <input type="hidden" id="trash-nonce" value="<?= wp_create_nonce('trash_nonce'); ?>" />
                <a class="form-ui orange-bg-400" href="<?= get_bloginfo('url')."/blog/?adventure_id=$adventure->adventure_id"; ?>">
                    <span class="icon icon-duplicate">
                    <?php _e("All posts","bluerabbit"); ?>
                </a>
            </div>
        </div>
    <?php } ?>
<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
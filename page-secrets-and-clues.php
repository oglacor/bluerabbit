<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php 
	if(($isGM || $isAdmin || $isNPC) && $_GET['player_id']){
		$the_player_id = $_GET['player_id'];
		$current_player = getPlayerData($the_player_id);
	}else{
		$the_player_id = $current_user->ID;
	}
	$myquests = $wpdb->get_results("SELECT 
		a.pp_grade, a.pp_modified, a.quest_id,a.pp_status,
		b.*

		FROM {$wpdb->prefix}br_player_posts a
		LEFT JOIN {$wpdb->prefix}br_quests b
		ON a.quest_id = b.quest_id

		WHERE a.adventure_id=$adventure->adventure_id AND a.player_id=$the_player_id AND b.quest_status='publish'
		ORDER BY a.pp_modified
	");
?>
<div class="layer base relative max-w-1200 boxed">
	<h1 class=" text-center inline-block font _36 w100 condensed padding-10 w-full white-color text-left border rounded-8" style="background-color: rgba(0,0,0,0.5);">
		<?php _e("Secret Messages","bluerabbit"); ?>
	</h1>
	<?php if($myquests){ ?>
			<div class="layer relative base flex wrap">
				<?php foreach($myquests as $key=>$q){ ?>
					<?php if($q->quest_success_message){ ?>
						<div class="white-color quest-success-message margin-10 padding-10 w-third min-w-300 relative grow-1">
							<div class="layer background absolute sq-full border rounded-8 <?= $q->quest_color;?>-bg-400 opacity-70"></div>
							<div class="layer background absolute sq-full border rounded-8 black-bg opacity-20" style="background-image: url(<?= $q->mech_badge;?>);"></div>
							<div class="layer base relative border rounded-8">
								<h2 class="font _24 w300 padding-10 text-center padding-5" style="background-color: rgba(0,0,0,0.3)"><?php echo $q->quest_title; ?></h2>
								<div class="quest-success-message-entry">
									<?= apply_filters('the_content',$q->quest_success_message); ?>
								</div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
	<?php }else{ ?>
		<div class="text-center padding-10 white-color">
			<h2 class="line font _24"><?php _e("No messages found","bluerabbit"); ?></h2>
			<h3 class="line font _16 w500"><?php _e("When you find secret messages from quests they will appear here","bluerabbit"); ?></h3>
			<br>
			<a class="form-ui deep-purple-bg-400" href="<?= get_bloginfo('url')."/adventure/?adventure_id=$adventure->adventure_id";?>">
				<span class="icon icon-journey"></span>
				<span class="label"><?= __("Go to the Journey","bluerabbit"); ?></span>
			</a>
		</div>
	<?php } ?>
</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

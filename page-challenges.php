<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($isAdmin || $isGM){ ?>
<div class="container boxed max-w-1200">
<?php 
	$quests = $wpdb->get_results("SELECT quests.*, ach.achievement_name, ach.achievement_color FROM {$wpdb->prefix}br_quests quests
	
	LEFT JOIN {$wpdb->prefix}br_achievements ach ON quests.achievement_id=ach.achievement_id
	WHERE quests.quest_type='challenge' AND quests.adventure_id=$adventure->adventure_id AND quests.quest_status='publish'
	ORDER BY quests.quest_order, quests.quest_id
	
	");
?>

	<div class="body-ui w-full">
		<div class="highlight red-bg-800 padding-10 page-break text-center">
			<div class="icon-group">
				<span class="icon-button font _24 sq-40  red-bg-400 icon-lg">
					<span class="icon icon-challenge"></span>
				</span>
				<span class="icon-content white-color">
					<span class="line font _36 w300"> <?= __("Challenges","bluerabbit"); ?> </span>
					<span class="line font _16 w300"> <?= __("A list to all challenges reports","bluerabbit"); ?> </span>
				</span>
			</div>
		</div>
		<?php if($quests){ ?>
			<table class="table">
				<thead>
					<tr>
						<td><?= __("ID","bluerabbit"); ?></td>
						<td><?= __("Title","bluerabbit"); ?></td>
						<td><?= __("Track","bluerabbit"); ?></td>
						<td><?= __("Publish Date","bluerabbit"); ?></td>
						<td><?= __("Attempts","bluerabbit"); ?></td>
						<td><?= __("View","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($quests as $q){ ?>
					<tr>
						<td><?= $q->quest_id; ?></td>
						<td><a target="_blank" href="<?= get_bloginfo('url')."/challenges-report/?questID={$q->quest_id}"; ?>"><?= $q->quest_title; ?></a></td>
						<td><?= $q->achievement_name; ?></td>
						<td><?= date('Y - m - d H:i:s', strtotime($q->mech_start_date)); ?></td>
						<td><?= $q->total_attempts; ?></td>
						<td><a class="icon-button font _24 sq-40  indigo-bg-400" target="_blank" href="<?= get_bloginfo('url')."/challenges-report/?questID={$q->quest_id}"; ?>"><span class="icon icon-view"></span></a></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php }else{ ?>	
			 <h1 class="font _48 text-center padding-20 w900"><?php _e("No challenges!","bluerabbit"); ?></h1>
		<?php } ?>	
	</div>
</div>
<?php }else{ ?>
<script>document.location.href="<?php echo get_bloginfo('url')."/404"; ?>";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>






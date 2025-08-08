<?php 
	$adventure_quests = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_quests 
	WHERE adventure_id=$adventure_id AND quest_status='publish' ORDER BY quest_type, quest_relevance, quest_order, mech_level, mech_start_date LIMIT 5");

	$adventure_achievements = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_achievements 
	WHERE adventure_id=$adventure_id AND achievement_status='publish' ORDER BY achievement_name, achievement_order LIMIT 5");
	
	$adventure_items = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_items
	WHERE adventure_id=$adventure_id AND item_status='publish' ORDER BY item_name, item_order LIMIT 5");
?>

<div class="template-quests template-content-list">
	<h4 class="font _16 w600 text-center padding-5"><?= __("Quests","bluerabbit"); ?></h4>
	<?php if(isset($adventure_quests) && count($adventure_quests) > 0){ ?>
		<ul>
			<?php foreach ($adventure_quests as $key=>$q){ ?>
			<li><?= $q->quest_title." | ".__("Lv","bluerabbit")." ".$q->mech_level; ?></li>
			<?php } ?>
		</ul>
	<?php }else{ ?>
		<h4 class="font _24 w600 text-center padding-5"><?= __("No Quests","bluerabbit"); ?></h4>
	<?php } ?>
</div>
<div class="template-achievements template-content-list">
	<h4 class="font _16 w600 text-center padding-5"><?= __("Achievements","bluerabbit"); ?></h4>
	<?php if(isset($adventure_achievements) && count($adventure_achievements) > 0){ ?>
		<ul>
			<?php foreach ($adventure_achievements as $key=>$a){ ?>
			<li><?= $a->achievement_name." | ".__("XP","bluerabbit")." ".$a->achievement_xp." | ".__("BLOO","bluerabbit")." ".$a->achievement_bloo; ?></li>
			<?php } ?>
		</ul>
	<?php }else{ ?>
		<h4 class="font _24 w600 text-center padding-5"><?= __("No Achievements","bluerabbit"); ?></h4>
	<?php } ?>
</div>
<div class="template-items template-content-list">
	<h4 class="font _16 w600 text-center padding-5"><?= __("Items","bluerabbit"); ?></h4>
	<?php if(isset($adventure_items) && count($adventure_items) > 0){ ?>
		<ul>
			<?php foreach ($adventure_items as $key=>$i){ ?>
			<li><?= $i->item_name." | ".__("Lv","bluerabbit")." ".$i->item_level." | ".$i->item_type; ?></li>
			<?php } ?>
		</ul>
	<?php }else{ ?>
		<h4 class="font _24 w600 text-center padding-5"><?= __("No Items","bluerabbit"); ?></h4>
	<?php } ?>
</div>

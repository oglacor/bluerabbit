<?php
// Shared Shepherd.js step fragments for the quest/challenge/mission/survey builders,
// which all share the same component-quest-*.php includes (they're all rows in br_quests).
//
// $switch_group/$switch_tab let a caller activate the right dashboard tab (via switchTabs())
// before the fragment's first step tries to attach to an element inside it — tab panes are
// `display:none` until `.active`, so Shepherd can't position against a hidden element.

function br_tab_switch_snippet($switch_group, $switch_tab){
	if(!$switch_group){ return ''; }
	ob_start();
	?>
	beforeShowPromise: function(){ switchTabs('<?= esc_js($switch_group); ?>','<?= esc_js($switch_tab); ?>'); return Promise.resolve(); },
	<?php
	return ob_get_clean();
}

function br_steps_mechanics_base($use_encounters, $switch_group = '', $switch_tab = ''){
	$resources_text = $use_encounters
		? __("Optionally tie this to a Tabi, set its level, and decide how much XP, currency, and energy it rewards.","bluerabbit")
		: __("Optionally tie this to a Tabi, set its level, and decide how much XP and currency it rewards.","bluerabbit");
	ob_start();
	?>
	{
		id: 'mech-rewards',
		title: "<?= __("Rewards & Difficulty","bluerabbit"); ?>",
		text: "<?= $resources_text; ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#the_quest_level', on: 'right' },
		buttons: [ brNextBtn() ]
	},
	{
		id: 'mech-schedule',
		title: "<?= __("Schedule","bluerabbit"); ?>",
		text: "<?= __("Optionally set a start date (when it becomes available) and a deadline (when it closes).","bluerabbit"); ?>",
		attachTo: { element: '#the_quest_start_date', on: 'right' },
		buttons: [ brNextBtn() ]
	},
	{
		id: 'mech-flags',
		title: "<?= __("Optional & Validate","bluerabbit"); ?>",
		text: "<?= __("Optional means it won't count toward unlocking a Tabi. Validate means a GM must approve it before resources are awarded.","bluerabbit"); ?>",
		attachTo: { element: '#the_quest_optional', on: 'right' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_content($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'content-fields',
		title: "<?= __("Content","bluerabbit"); ?>",
		text: "<?= __("Write the short preview text players see in the journey, the main instructions, and the message shown when they succeed.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#the_quest_secondary_headline', on: 'right' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_additional_mechs($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'additional-mechs',
		title: "<?= __("Additional Mechanics","bluerabbit"); ?>",
		text: "<?= __("Both optional: a currency cost to buy more time past the deadline, and a cost to unlock it early.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#the_quest_deadline_cost', on: 'right' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_item_reward($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'item-reward',
		title: "<?= __("Item Reward","bluerabbit"); ?>",
		text: "<?= __("Pick one item from the shop to award on completion, if any.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#tutorial-item-reward', on: 'top' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_achievement_reward($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'achievement-reward',
		title: "<?= __("Achievement Reward","bluerabbit"); ?>",
		text: "<?= __("Pick a badge or path achievement to award on completion, if any.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#tutorial-achievement-reward', on: 'top' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_key_item_req($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'key-item-req',
		title: "<?= __("Key Item Required","bluerabbit"); ?>",
		text: "<?= __("If players need a specific key item in their backpack to access this, pick it here.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#tutorial-key-item-required', on: 'top' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_quest_reqs($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'quest-reqs',
		title: "<?= __("Quests Required","bluerabbit"); ?>",
		text: "<?= __("Pick any quests that must be completed first — you can select more than one.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#tutorial-quests-required', on: 'top' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_achievement_reqs($switch_group = '', $switch_tab = ''){
	ob_start();
	?>
	{
		id: 'achievement-reqs',
		title: "<?= __("Achievements Required","bluerabbit"); ?>",
		text: "<?= __("Pick any achievements players must already have earned first — you can select more than one.","bluerabbit"); ?>",
		<?= br_tab_switch_snippet($switch_group, $switch_tab); ?>
		attachTo: { element: '#tutorial-achievements-required', on: 'top' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

function br_steps_sidebar_status(){
	ob_start();
	?>
	{
		id: 'sidebar-status',
		title: "<?= __("Status & Save","bluerabbit"); ?>",
		text: "<?= __("Publish, save as draft, or lock it — then save your changes here. This sidebar stays visible no matter which tab you're on.","bluerabbit"); ?>",
		attachTo: { element: '#submit-button', on: 'left' },
		buttons: [ brNextBtn() ]
	},
	<?php
	return ob_get_clean();
}

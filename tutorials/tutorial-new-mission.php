<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-mission');
const new_mission_steps = [
    {
        id: 'step-1',
        title: "<?= __("Mission Builder","bluerabbit"); ?>",
        text: "<?= __("Let's walk through each tab — Basics, Mechanics, Content, Objectives, Requirements, and Advanced.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-mission', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'general-1',
        title: "<?= __("Name","bluerabbit"); ?>",
        text: "<?= __("Give your mission a name.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_title', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-2',
        title: "<?= __("Look","bluerabbit"); ?>",
        text: "<?= __("Pick a color and icon so it's easy to recognize on the journey map, plus a main image.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-color-select', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?= br_steps_mechanics_base($use_encounters, '#main-tabs', '#mechanics'); ?>
    <?= br_steps_content('#main-tabs', '#content'); ?>
    {
        id: 'objectives-1',
        title: "<?= __("Mission Objectives","bluerabbit"); ?>",
        text: "<?= __("Objectives are the smaller tasks inside a mission — players chip away at them one by one. They can only be added once the mission is saved for the first time.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#main-tabs', '#objectives'); ?>
        attachTo: { element: '#tutorial-add-new-objective-bar', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'objectives-2',
        title: "<?= __("Two Objective Types","bluerabbit"); ?>",
        text: "<?= __("Keyword objectives are solved by typing the right word; True/False objectives are solved with a single tap. Add either kind here, and reset everyone's progress on them if you need to.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-add-new-objective-bar', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?= br_steps_quest_reqs('#main-tabs', '#requirements'); ?>
    <?= br_steps_key_item_req(); ?>
    <?= br_steps_achievement_reqs(); ?>
    <?= br_steps_additional_mechs('#main-tabs', '#advanced-options'); ?>
    <?= br_steps_item_reward(); ?>
    <?= br_steps_achievement_reward(); ?>
    <?= br_steps_sidebar_status(); ?>
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-mission', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_mission_steps);
</script>

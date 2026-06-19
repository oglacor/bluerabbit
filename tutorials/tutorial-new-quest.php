<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-quest');
const new_quest_steps = [
    {
        id: 'step-1',
        title: "<?= __("Quest Builder","bluerabbit"); ?>",
        text: "<?= __("Let's walk through each tab — Basics, Mechanics, Content, Steps, and Advanced.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-quest', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'general-1',
        title: "<?= __("Name & Autoload","bluerabbit"); ?>",
        text: "<?= __("Give your quest a name. 'Autoload' quests are automatically offered to qualifying players, without needing to be unlocked on the journey map.","bluerabbit"); ?>",
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
    {
        id: 'mech-quest-specific',
        title: "<?= __("Word & Media Minimums","bluerabbit"); ?>",
        text: "<?= __("If this quest asks players to write a response, you can require a minimum number of words, links, and images before it can be submitted.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_min_words', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?= br_steps_content('#main-tabs', '#content'); ?>
    <?php if(isset($quest)){ ?>
    {
        id: 'steps-1',
        title: "<?= __("Quest Steps","bluerabbit"); ?>",
        text: "<?= __("Every step the player goes through, in order. Use the pencil to edit a step, the duplicate icon to copy it, or the trash icon to remove it.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#main-tabs', '#boss-fight-steps'); ?>
        attachTo: { element: '#steps-list-table', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'steps-2',
        title: "<?= __("Add a Step","bluerabbit"); ?>",
        text: "<?= __("Add a new step here, or reorder existing ones. Once you're editing a step, click the blue ? button inside its editor for a full walkthrough of everything a step can do.","bluerabbit"); ?>",
        attachTo: { element: '.tab#boss-fight-steps button.blue-bg-300', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?= br_steps_additional_mechs('#main-tabs', '#advanced-options'); ?>
    <?= br_steps_item_reward(); ?>
    <?= br_steps_achievement_reward(); ?>
    <?= br_steps_key_item_req(); ?>
    <?= br_steps_quest_reqs(); ?>
    <?= br_steps_achievement_reqs(); ?>
    <?= br_steps_sidebar_status(); ?>
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-quest', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_quest_steps);
</script>

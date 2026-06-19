<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-survey');
const new_survey_steps = [
    {
        id: 'step-1',
        title: "<?= __("Survey Builder","bluerabbit"); ?>",
        text: "<?= __("Let's walk through each tab — General, Mechanics, Questions, and Advanced.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-survey', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'general-1',
        title: "<?= __("Name","bluerabbit"); ?>",
        text: "<?= __("Give your survey a name.","bluerabbit"); ?>",
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
    <?= br_steps_mechanics_base($use_encounters, '#tab-group', '#mechanics'); ?>
    <?php if(isset($adventure) && isset($quest)){ ?>
    {
        id: 'questions-1',
        title: "<?= __("Question Types","bluerabbit"); ?>",
        text: "<?= __("Surveys support six question types: Closed, Multi Choice, Open, Rating, Value, and (if guilds are enabled) Guild Vote. Add one of each you need here.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#survey-questions'); ?>
        attachTo: { element: '.add-new-question-header', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'questions-2',
        title: "<?= __("Editing Questions","bluerabbit"); ?>",
        text: "<?= __("Each question is its own accordion — click to expand it and edit its text, image, and options.","bluerabbit"); ?>",
        attachTo: { element: '#questions', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'questions-3',
        title: "<?= __("Reordering","bluerabbit"); ?>",
        text: "<?= __("Click Reorder to drag questions into a new order, then Save Order to confirm — or Cancel Reorder to back out.","bluerabbit"); ?>",
        attachTo: { element: '.default-actions', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?= br_steps_additional_mechs('#tab-group', '#advanced-options'); ?>
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
        buttons: [ brDoneBtn('new-survey', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_survey_steps);
</script>

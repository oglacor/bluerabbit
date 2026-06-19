<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-challenge');
const new_challenge_steps = [
    {
        id: 'step-1',
        title: "<?= __("Challenge Builder","bluerabbit"); ?>",
        text: "<?= __("Let's walk through each tab — General, Mechanics, Content, Questions, and Advanced.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-challenge', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'general-1',
        title: "<?= __("Name","bluerabbit"); ?>",
        text: "<?= __("Give your challenge a title.","bluerabbit"); ?>",
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
    <?= br_steps_mechanics_base($use_encounters, '#tab-group', '#challenge-mechanics'); ?>
    {
        id: 'mech-challenge-specific',
        title: "<?= __("Timing & Attempts","bluerabbit"); ?>",
        text: "<?= __("Set a time limit, how many questions to show, how many correct answers are needed to win, and how many attempts players get before a Bloo cost kicks in.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_time_limit', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?= br_steps_content('#tab-group', '#content'); ?>
    <?php if(isset($adventure) && isset($quest)){ ?>
    {
        id: 'questions-1',
        title: "<?= __("Bulk Upload","bluerabbit"); ?>",
        text: "<?= __("Already have questions in a spreadsheet? Upload a CSV here instead of entering them one by one.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#challenge-questions'); ?>
        attachTo: { element: '#the_csv_file_with_questions', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'questions-2',
        title: "<?= __("Questions","bluerabbit"); ?>",
        text: "<?= __("Each question is its own accordion. Click to expand it, edit its text and image, then click the toggle on each option to mark it correct or incorrect.","bluerabbit"); ?>",
        attachTo: { element: '#questions', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'questions-3',
        title: "<?= __("Add Question","bluerabbit"); ?>",
        text: "<?= __("Add as many questions as you like here — the more you add, the more random each attempt feels.","bluerabbit"); ?>",
        attachTo: { element: '#questions-bottom', on: 'top' },
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
        buttons: [ brDoneBtn('new-challenge', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_challenge_steps);
</script>

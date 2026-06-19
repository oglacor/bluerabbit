<script>
const tour = brCreateTour('quest');
const quest_steps = [
    {
        id: 'step-1',
        title: "<?= __("Quests","bluerabbit"); ?>",
        text: "<?= __("Quests are the building blocks of your adventure. Complete them to earn rewards and unlock new content.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('quest', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    <?php if(!empty($requirements) && isset($requirements_rendered) && $requirements_rendered){ ?>
    {
        id: 'step-2',
        title: "<?= __("Requirements","bluerabbit"); ?>",
        text: "<?= __("Complete these first — they're locking this quest until you do.","bluerabbit"); ?>",
        attachTo: { element: '.card-deck', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php if(isset($quest_content_rendered) && $quest_content_rendered){ ?>
    {
        id: 'step-3',
        title: "<?= __("The Quest","bluerabbit"); ?>",
        text: "<?= __("This is where the content lives — read, watch, or answer to complete it.","bluerabbit"); ?>",
        attachTo: { element: '#quest-steps', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'step-4',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('quest', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(quest_steps);
</script>

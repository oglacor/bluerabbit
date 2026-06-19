<script>
const tour = brCreateTour('adventures-list');
const adventures_list_steps = [
    {
        id: 'step-1',
        title: "<?= __("Welcome to My Adventures","bluerabbit"); ?>",
        text: "<?= __("This is where every adventure you've created or joined lives.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('adventures-list', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'step-2',
        title: "<?= __("Your Adventures","bluerabbit"); ?>",
        text: "<?= __("Each card here is an adventure you can play.","bluerabbit"); ?>",
        attachTo: { element: '.adventures', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php if($adventures){ ?>
    {
        id: 'step-3',
        title: "<?= __("Jump Back In","bluerabbit"); ?>",
        text: "<?= __("Click any adventure to open it and continue where you left off.","bluerabbit"); ?>",
        attachTo: { element: '.adventures li.adventure', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php if($add_adventure || $add_from_template){ ?>
    {
        id: 'step-4',
        title: "<?= __("Start Something New","bluerabbit"); ?>",
        text: "<?= __("Ready to build your own? Start a new adventure here.","bluerabbit"); ?>",
        attachTo: { element: '.adventure.add-new', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php if($total_pages > 1){ ?>
    {
        id: 'step-5',
        title: "<?= __("More Adventures","bluerabbit"); ?>",
        text: "<?= __("Flip through pages here if you have more adventures than fit on one screen.","bluerabbit"); ?>",
        attachTo: { element: '.pages-nav', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'step-6',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('adventures-list', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(adventures_list_steps);
</script>

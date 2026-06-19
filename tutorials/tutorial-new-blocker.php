<script>
const tour = brCreateTour('new-blocker');
const new_blocker_steps = [
    {
        id: 'step-1',
        title: "<?= __("Blocker Editor","bluerabbit"); ?>",
        text: "<?= __("Blockers are debts a player must pay off in currency before they can keep progressing.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-blocker', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'blocker-1',
        title: "<?= __("Cost","bluerabbit"); ?>",
        text: "<?= __("How much currency the player owes.","bluerabbit"); ?>",
        attachTo: { element: '#the_blocker_cost', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'blocker-2',
        title: "<?= __("Reason","bluerabbit"); ?>",
        text: "<?= __("Explain why the blocker was issued, and add evidence if you have it.","bluerabbit"); ?>",
        attachTo: { element: '#the_blocker_description', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'blocker-3',
        title: "<?= __("Fined Players","bluerabbit"); ?>",
        text: "<?= __("Pick who owes this debt — they won't be able to progress until it's paid.","bluerabbit"); ?>",
        attachTo: { element: '.highlight.padding-10', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'blocker-4',
        title: "<?= __("Status & Save","bluerabbit"); ?>",
        text: "<?= __("Publish, save as draft, or trash it — then save your changes here.","bluerabbit"); ?>",
        attachTo: { element: '#submit-button', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-blocker', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_blocker_steps);
</script>

<script>
const tour = brCreateTour('new-item');
const new_item_steps = [
    {
        id: 'step-1',
        title: "<?= __("Item Builder","bluerabbit"); ?>",
        text: "<?= __("Items can be Consumables, Keys, Rewards, or Tabi Pieces — pick a type and the options below adjust to match.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-item', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'step-2',
        title: "<?= __("Image","bluerabbit"); ?>",
        text: "<?= __("Upload an image for your item here.","bluerabbit"); ?>",
        attachTo: { element: '.dashboard-gallery-image-container', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-3',
        title: "<?= __("Name","bluerabbit"); ?>",
        text: "<?= __("Give it a name.","bluerabbit"); ?>",
        attachTo: { element: '.dashboard-input-field-container', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-4',
        title: "<?= __("Item Type","bluerabbit"); ?>",
        text: "<?= __("Choose a type here — this changes which options are relevant.","bluerabbit"); ?>",
        attachTo: { element: '.dashboard-type-selector', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-5',
        title: "<?= __("Options","bluerabbit"); ?>",
        text: "<?= __("Set its visibility, level, cost, stock, and grouping here.","bluerabbit"); ?>",
        attachTo: { element: '#item-options-panel', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    <?php if(!empty($tabis)){ ?>
    {
        id: 'step-6',
        title: "<?= __("Tabi Settings","bluerabbit"); ?>",
        text: "<?= __("If this is a Tabi Piece, assign it to a Tabi and position it here.","bluerabbit"); ?>",
        attachTo: { element: '#item-tabi-settings-panel', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'step-7',
        title: "<?= __("Description","bluerabbit"); ?>",
        text: "<?= __("Add a longer description here.","bluerabbit"); ?>",
        attachTo: { element: '.dashboard-text-area-container', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-8',
        title: "<?= __("Save","bluerabbit"); ?>",
        text: "<?= __("Assign it to a path if needed, then save here.","bluerabbit"); ?>",
        attachTo: { element: '.dashboard-save-form-container', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-9',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-item', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_item_steps);
</script>

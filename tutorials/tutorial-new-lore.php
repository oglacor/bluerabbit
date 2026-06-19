<script>
const tour = brCreateTour('new-lore');
const new_lore_steps = [
    {
        id: 'step-1',
        title: "<?= __("Resource Editor","bluerabbit"); ?>",
        text: "<?= __("Resources (Lore) are reference material players can read or download anytime — not tied to completing a quest.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-lore', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'lore-1',
        title: "<?= __("Name & Style","bluerabbit"); ?>",
        text: "<?= __("Name it, and choose whether it displays as a Resource or an Article.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_title', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'lore-2',
        title: "<?= __("File or Link","bluerabbit"); ?>",
        text: "<?= __("Type a URL, or click Select File to upload a document players can open or download.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_secondary_headline', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'lore-3',
        title: "<?= __("Content","bluerabbit"); ?>",
        text: "<?= __("The body text players read.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_content', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'lore-4',
        title: "<?= __("Image & Color","bluerabbit"); ?>",
        text: "<?= __("Pick a cover image and a color to identify it in the library.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-color-select', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'lore-5',
        title: "<?= __("Level & Path","bluerabbit"); ?>",
        text: "<?= __("Optionally require a minimum level, or restrict it to players on a specific path.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_level', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'lore-6',
        title: "<?= __("Save","bluerabbit"); ?>",
        text: "<?= __("Publish it here.","bluerabbit"); ?>",
        attachTo: { element: '#submit-button', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-lore', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_lore_steps);
</script>

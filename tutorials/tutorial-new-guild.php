<script>
const tour = brCreateTour('new-guild');
const new_guild_steps = [
    {
        id: 'step-1',
        title: "<?= __("Guild Editor","bluerabbit"); ?>",
        text: "<?= __("Guilds are teams players belong to within the adventure.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-guild', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'guild-1',
        title: "<?= __("Name & Logo","bluerabbit"); ?>",
        text: "<?= __("Name the guild and upload a logo — the logo is required.","bluerabbit"); ?>",
        attachTo: { element: '#the_guild_name', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'guild-2',
        title: "<?= __("Look","bluerabbit"); ?>",
        text: "<?= __("Pick a color to identify the guild at a glance.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-color-select', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'guild-3',
        title: "<?= __("Auto-Assign, Group & Capacity","bluerabbit"); ?>",
        text: "<?= __("Automatically assign players to this guild when they log in, optionally tag it with a group name, and cap its capacity (zero means no limit).","bluerabbit"); ?>",
        attachTo: { element: '#the_guild_assign_on_login', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?php if(isset($g)){ ?>
    {
        id: 'guild-4',
        title: "<?= __("Enrollment Link & Bulk Assign","bluerabbit"); ?>",
        text: "<?= __("Share the enrollment link to let players join themselves, or upload a CSV to assign many players at once.","bluerabbit"); ?>",
        attachTo: { element: '#the_csv_file_with_players', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'guild-5',
        title: "<?= __("Members","bluerabbit"); ?>",
        text: "<?= __("Search the adventure's roster and assign or remove members one by one.","bluerabbit"); ?>",
        attachTo: { element: '#search-players', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php }else{ ?>
    {
        id: 'guild-4',
        title: "<?= __("One More Step","bluerabbit"); ?>",
        text: "<?= __("Save the guild once first, then come back to share its enrollment link, bulk-upload members, or assign them one by one.","bluerabbit"); ?>",
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'guild-6',
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
        buttons: [ brDoneBtn('new-guild', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_guild_steps);
</script>

<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-adventure');
const new_adventure_steps = [
    {
        id: 'step-1',
        title: "<?= __("Adventure Builder","bluerabbit"); ?>",
        text: "<?= __("This is the big one — an adventure is the container for everything else: quests, challenges, missions, guilds, all of it. Let's walk through every tab.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-adventure', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'general-1',
        title: "<?= __("Identity","bluerabbit"); ?>",
        text: "<?= __("Name your adventure, give it an image and color, and (if you're an admin) set its type and owner.","bluerabbit"); ?>",
        attachTo: { element: '#the_adventure_title', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-2',
        title: "<?= __("Resource Labels","bluerabbit"); ?>",
        text: "<?= __("Rename XP, currency, and energy to fit your theme, and decide what to call your players.","bluerabbit"); ?>",
        attachTo: { element: '#the_adventure_xp_long_label', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-3',
        title: "<?= __("Time & Schedule","bluerabbit"); ?>",
        text: "<?= __("Set the adventure's time zone, choose whether to hide quests before they start or after their deadline, and control the schedule page.","bluerabbit"); ?>",
        attachTo: { element: '#the_adventure_gmt', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-4',
        title: "<?= __("Grading","bluerabbit"); ?>",
        text: "<?= __("If you grade player submissions, pick a grading scale and decide whether resources are awarded before or after grading.","bluerabbit"); ?>",
        attachTo: { element: '#the_adventure_grade_scale', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'intro-1',
        title: "<?= __("Intro Message","bluerabbit"); ?>",
        text: "<?= __("This is what players see the very first time they log into the adventure.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#adventure-intro'); ?>
        attachTo: { element: '#tutorial-adventure-intro', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'certificate-1',
        title: "<?= __("Certificate","bluerabbit"); ?>",
        text: "<?= __("Set start/end dates and upload a logo and signature — used to generate a completion certificate.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#certificate-settings'); ?>
        attachTo: { element: '#the_adventure_start_date', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?php if($adventure){ ?>
    {
        id: 'reset-1',
        title: "<?= __("Reset Tools","bluerabbit"); ?>",
        text: "<?= __("Three independent resets: replay the intro message for everyone, clear level-up tracking, or reshuffle everyone into random guilds.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#reset-settings'); ?>
        attachTo: { element: '#tutorial-reset-intro', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'ranks-1',
        title: "<?= __("Level Ranks","bluerabbit"); ?>",
        text: "<?= __("Show a special message and assign a rank achievement whenever a player reaches a chosen level.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#ranks-settings'); ?>
        attachTo: { element: '#tutorial-adventure-ranks', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'tabis-1',
        title: "<?= __("Tabis","bluerabbit"); ?>",
        text: "<?= __("Tabis are collectible avatars players build piece by piece. Create and configure them here.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#tabis-settings'); ?>
        attachTo: { element: '#tutorial-adventure-tabis', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'players-1',
        title: "<?= __("Enrolled Players","bluerabbit"); ?>",
        text: "<?= __("Search the roster and promote anyone to GM or NPC, or remove them from the adventure.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#enrolled-players'); ?>
        attachTo: { element: '#tutorial-players', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'players-2',
        title: "<?= __("Bulk Add Players","bluerabbit"); ?>",
        text: "<?= __("Download the CSV template, fill it in, and upload it here to enroll many players at once.","bluerabbit"); ?>",
        attachTo: { element: '#the_csv_file_with_users', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'players-3',
        title: "<?= __("Add One Player","bluerabbit"); ?>",
        text: "<?= __("Check whether a nickname or email is already taken, then register a single player manually here.","bluerabbit"); ?>",
        attachTo: { element: '#username-search', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'features-1',
        title: "<?= __("Features","bluerabbit"); ?>",
        text: "<?= __("Every optional system — challenges, guilds, the item shop, leaderboards, and more — is switched on or off here, alongside a few global settings like default zoom and journey view.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#features'); ?>
        attachTo: { element: '#tutorial-adventure-features', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'images-1',
        title: "<?= __("Background Images","bluerabbit"); ?>",
        text: "<?= __("Set a custom background for the journey, item shop, backpack, guilds, schedule, blog, lore, wall, leaderboard, and player-work pages — or leave any of them on the system default.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#tab-group', '#image_settings'); ?>
        attachTo: { element: '.tab#image_settings .gallery', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?= br_steps_sidebar_status(); ?>
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-adventure', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_adventure_steps);
</script>

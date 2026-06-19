<?php include (get_stylesheet_directory() . '/tutorials/tutorial-quest-components.php'); ?>
<script>
const tour = brCreateTour('new-achievement');
const new_achievement_steps = [
    {
        id: 'step-1',
        title: "<?= __("Achievement Builder","bluerabbit"); ?>",
        text: "<?= __("Achievements come in three flavors — Badges, Ranks, and Paths — and they're how you reward, gate, and track player progress.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-achievement', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    <?php if($isGM || $isAdmin){ ?>
    {
        id: 'general-1',
        title: "<?= __("Identity","bluerabbit"); ?>",
        text: "<?= __("Name it, give it a badge image, and pick a color.","bluerabbit"); ?>",
        attachTo: { element: '#the_achievement_name', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-2',
        title: "<?= __("Type","bluerabbit"); ?>",
        text: "<?= __("This is the most important choice. Badge: a one-off reward. Rank: shown as the player's title at a given level. Path: an exclusive track players choose between. The rest of this tab adapts to your choice.","bluerabbit"); ?>",
        attachTo: { element: '#the_achievement_display', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-3',
        title: "<?= __("Rewards & Limits","bluerabbit"); ?>",
        text: "<?= __("Optionally cap how many players can earn it, give it an XP/currency/energy reward, and set a deadline.","bluerabbit"); ?>",
        attachTo: { element: '#the_achievement_xp', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'general-4',
        title: "<?= __("Secret Message","bluerabbit"); ?>",
        text: "<?= __("Shown to players the moment they earn it.","bluerabbit"); ?>",
        attachTo: { element: '#the_achievement_content', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'codes-1',
        title: "<?= __("Magic Code","bluerabbit"); ?>",
        text: "<?= __("A single shared code any player can redeem — generate one here, and the magic link below updates automatically.","bluerabbit"); ?>",
        <?= br_tab_switch_snippet('#main-tabs', '#achievement-codes'); ?>
        attachTo: { element: '#the_achievement_code', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?php if(isset($a)){ ?>
    {
        id: 'codes-2',
        title: "<?= __("Unique Codes","bluerabbit"); ?>",
        text: "<?= __("Generate one-time codes instead — each can only be redeemed once. Switch between Available, Redeemed, and Expired here.","bluerabbit"); ?>",
        attachTo: { element: '#unique-code-tabs-buttons', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php } ?>
    {
        id: 'players-1',
        title: "<?= __("Assign Manually","bluerabbit"); ?>",
        text: "<?php if(isset($a)){
            _e("Select players here to award the achievement directly, without them earning it through play.","bluerabbit");
        }else{
            _e("Save the achievement once first — then come back here to assign it to players directly.","bluerabbit");
        } ?>",
        <?= br_tab_switch_snippet('#main-tabs', '#select-players'); ?>
        <?php if(isset($a)){ ?>
        attachTo: { element: '#assign-manually-buttons', on: 'bottom' },
        <?php } ?>
        buttons: [ brNextBtn() ]
    },
    <?php if(isset($a)){ ?>
    {
        id: 'players-2',
        title: "<?= __("Awarded Players","bluerabbit"); ?>",
        text: "<?= __("See everyone who's earned it, and remove or restore it for any of them.","bluerabbit"); ?>",
        beforeShowPromise: function(){ switchTabs('#assign-manually','#players-awarded'); return Promise.resolve(); },
        attachTo: { element: '#tutorial-earned-players', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php if($isGM || $isAdmin){ ?>
    <?= br_steps_sidebar_status(); ?>
    <?php } ?>
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-achievement', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_achievement_steps);
</script>

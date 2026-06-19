<script>
const tour = brCreateTour('mission');
const mission_steps = [
    {
        id: 'step-1',
        title: "<?= __("Missions","bluerabbit"); ?>",
        text: "<?= __("Missions are bigger goals made up of several objectives. Clear them all to complete the mission.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('mission', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'step-2',
        title: "<?= __("Status & Objectives","bluerabbit"); ?>",
        text: "<?= __("Use these tabs to switch between the mission status and its objectives.","bluerabbit"); ?>",
        attachTo: { element: '#tab-group-buttons', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php if($m->mech_xp > 0 || $m->mech_bloo > 0){ ?>
    {
        id: 'step-3',
        title: "<?= __("Rewards","bluerabbit"); ?>",
        text: "<?= __("Here's what you'll earn when you finish the mission.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-rewards', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'step-4',
        title: "<?= __("Progress","bluerabbit"); ?>",
        text: "<?= __("This chart tracks how close you are to completing the mission.","bluerabbit"); ?>",
        attachTo: { element: '#mission-status-chart', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php if($m->quest_content){ ?>
    {
        id: 'step-5',
        title: "<?= __("Mission Brief","bluerabbit"); ?>",
        text: "<?= __("Read your briefing here for the full mission details.","bluerabbit"); ?>",
        attachTo: { element: '#mission-brief', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    <?php if($m_reqs || $objectives){ ?>
    {
        id: 'step-6',
        title: "<?= __("Objectives","bluerabbit"); ?>",
        text: "<?= __("Each card here is something you still need to complete for this mission.","bluerabbit"); ?>",
        attachTo: { element: '#mission-objectives .card-deck', on: 'top' },
        beforeShowPromise: function() {
            switchTabs('#tab-group','#mission-objectives');
            return Promise.resolve();
        },
        buttons: [
            {
              text: BR_TUTORIAL_I18N.next,
              classes: "blue-bg-400 white-color",
              action: function(){
                  switchTabs('#tab-group','#mission-status');
                  return this.next();
              }
            }
        ]
    },
    <?php } ?>
    {
        id: 'step-7',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('mission', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(mission_steps);
</script>

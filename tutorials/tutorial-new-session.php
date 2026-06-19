<script>
const tour = brCreateTour('new-session');
const new_session_steps = [
    {
        id: 'step-1',
        title: "<?= __("Session Builder","bluerabbit"); ?>",
        text: "<?= __("Sessions are schedule entries — talks, workshops, anything with a time and place.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-session', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'session-1',
        title: "<?= __("Title & Description","bluerabbit"); ?>",
        text: "<?= __("Give the session a title and describe what it's about.","bluerabbit"); ?>",
        attachTo: { element: '#the_session_title', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'session-2',
        title: "<?= __("Schedule","bluerabbit"); ?>",
        text: "<?= __("Set when it starts and ends.","bluerabbit"); ?>",
        attachTo: { element: '#the_session_start', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'session-3',
        title: "<?= __("Room","bluerabbit"); ?>",
        text: "<?= __("Where it's happening — a room name, building, or any location info.","bluerabbit"); ?>",
        attachTo: { element: '#the_session_room', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'session-4',
        title: "<?= __("Attach to a Quest","bluerabbit"); ?>",
        text: "<?= __("Optionally link this session to a quest or challenge, so attending it ties into the adventure.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_id', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'session-5',
        title: "<?= __("Speakers","bluerabbit"); ?>",
        text: "<?= __("Check off everyone presenting at this session.","bluerabbit"); ?>",
        attachTo: { element: '.checkbox-group', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?php if($guilds){ ?>
    {
        id: 'session-6',
        title: "<?= __("Guild","bluerabbit"); ?>",
        text: "<?= __("Optionally restrict this session to a single guild instead of everyone.","bluerabbit"); ?>",
        attachTo: { element: '#the_guild_id', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'session-7',
        title: "<?= __("Status & Save","bluerabbit"); ?>",
        text: "<?= __("Publish, save as draft, or trash it — then save your changes here.","bluerabbit"); ?>",
        attachTo: { element: '#submit-button', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-session', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_session_steps);
</script>

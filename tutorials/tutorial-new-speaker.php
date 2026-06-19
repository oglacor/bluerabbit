<script>
const tour = brCreateTour('new-speaker');
const new_speaker_steps = [
    {
        id: 'step-1',
        title: "<?= __("Speaker Profile","bluerabbit"); ?>",
        text: "<?= __("Speakers can be attached to sessions on the schedule. Let's fill in their profile.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-speaker', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'speaker-1',
        title: "<?= __("Connect to a Player","bluerabbit"); ?>",
        text: "<?= __("Optionally link this speaker profile to an existing Admin, GM, or NPC account.","bluerabbit"); ?>",
        attachTo: { element: '#the_speaker_player_id', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'speaker-2',
        title: "<?= __("Picture","bluerabbit"); ?>",
        text: "<?= __("Upload a photo — this is required.","bluerabbit"); ?>",
        attachTo: { element: '#the_speaker_picture_thumb', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'speaker-3',
        title: "<?= __("Name & Company","bluerabbit"); ?>",
        text: "<?= __("First and last name, plus the company or organization they represent.","bluerabbit"); ?>",
        attachTo: { element: '#the_speaker_first_name', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'speaker-4',
        title: "<?= __("Website & LinkedIn","bluerabbit"); ?>",
        text: "<?= __("Both optional, both clickable wherever the speaker's profile is shown.","bluerabbit"); ?>",
        attachTo: { element: '#the_speaker_website', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'speaker-5',
        title: "<?= __("Bio","bluerabbit"); ?>",
        text: "<?= __("A short biography shown alongside their profile.","bluerabbit"); ?>",
        attachTo: { element: '#the_speaker_bio', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'speaker-6',
        title: "<?= __("Save","bluerabbit"); ?>",
        text: "<?= __("Save your changes here.","bluerabbit"); ?>",
        attachTo: { element: '#submit-button', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-speaker', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_speaker_steps);
</script>

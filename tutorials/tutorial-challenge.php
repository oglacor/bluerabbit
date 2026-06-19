<script>
const tour = brCreateTour('challenge');
const challenge_steps = [
    {
        id: 'step-1',
        title: "<?= __("Challenges","bluerabbit"); ?>",
        text: "<?= __("Challenges test your knowledge. Answer enough questions correctly to pass.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('challenge', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'step-2',
        title: "<?= __("Conditions","bluerabbit"); ?>",
        text: "<?= __("Here's what it takes to pass: attempts allowed, correct answers needed, and the rewards waiting for you.","bluerabbit"); ?>",
        attachTo: { element: '.challenge-conditions', on: 'left' },
        buttons: [ brNextBtn() ]
    },
    <?php if($c->mech_time_limit){ ?>
    {
        id: 'step-3',
        title: "<?= __("Timer","bluerabbit"); ?>",
        text: "<?= __("You're racing the clock. Once it hits zero, your answers are submitted automatically.","bluerabbit"); ?>",
        attachTo: { element: '#challenge-timer', on: 'bottom' },
        buttons: [ brNextBtn() ]
    },
    <?php } ?>
    {
        id: 'step-4',
        title: "<?= __("Question Navigation","bluerabbit"); ?>",
        text: "<?= __("Jump between questions here, or use this to see which ones you still need to answer.","bluerabbit"); ?>",
        attachTo: { element: '.challenge-nav', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-5',
        title: "<?= __("Answer Here","bluerabbit"); ?>",
        text: "<?= __("This is where you respond to each question.","bluerabbit"); ?>",
        attachTo: { element: '#challenge-questions', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-6',
        title: "<?= __("Ready?","bluerabbit"); ?>",
        text: "<?= __("Start your attempt here — or see exactly why it's locked if it isn't ready yet.","bluerabbit"); ?>",
        attachTo: { element: '#challenge-actions', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-7',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('challenge', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(challenge_steps);
</script>

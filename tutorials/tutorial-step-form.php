<script>
window.brStartStepFormTour = function(){
	var tour = brCreateTour('step-form');
	var steps = [
		{
			id: 'step-form-1',
			title: "<?= __("The Step Editor","bluerabbit"); ?>",
			text: "<?= __("Each step is one beat in the quest's sequence — a line of dialogue, a question, a video, and so on. Quests can have as many steps as you like.","bluerabbit"); ?>",
			buttons: [ brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>") ]
		},
		{
			id: 'step-form-2',
			title: "<?= __("Label","bluerabbit"); ?>",
			text: "<?= __("An internal label for this step — only GMs see it, players never will.","bluerabbit"); ?>",
			attachTo: { element: '#step-title-<?= $s->step_id; ?>', on: 'right' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-3',
			title: "<?= __("Type","bluerabbit"); ?>",
			text: "<?= __("This is the most important choice: Dialogue, Open Field, Jump, System Message, Win, Fail, Video, Find/Require an Item, Choose a Path, Choose Nickname/Avatar, or a SCORM package. Every other field on this screen adapts to match what you pick here.","bluerabbit"); ?>",
			attachTo: { element: '#step-type-<?= $s->step_id; ?>', on: 'right' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-4',
			title: "<?= __("Character (Dialogue only)","bluerabbit"); ?>",
			text: "<?= __("If this is a Dialogue step, choose which side the character stands on and optionally give them a name.","bluerabbit"); ?>",
			attachTo: { element: '#step-attach-<?= $s->step_id; ?>', on: 'right' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-5',
			title: "<?= __("Main Content","bluerabbit"); ?>",
			text: "<?= __("This is where most of the writing happens — dialogue, instructions, a win/fail message, or a clue — whatever fits the type you picked above.","bluerabbit"); ?>",
			attachTo: { element: '#step-content-row-<?= $s->step_id; ?>', on: 'top' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-6',
			title: "<?= __("Type-Specific Extras","bluerabbit"); ?>",
			text: "<?= __("Some types add their own fields below — a video upload, an achievement-path picker, an avatar gallery, or a SCORM package uploader. They only appear when relevant.","bluerabbit"); ?>",
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-7',
			title: "<?= __("Extra Buttons","bluerabbit"); ?>",
			text: "<?= __("Jump and Choose Avatar steps let you configure extra buttons or choices here — for example, the destinations a Jump step can branch to.","bluerabbit"); ?>",
			attachTo: { element: '#step-buttons-form-container', on: 'top' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-8',
			title: "<?= __("Background","bluerabbit"); ?>",
			text: "<?= __("Every step can have its own character image and background scene — set them here.","bluerabbit"); ?>",
			attachTo: { element: '#step-background-row-<?= $s->step_id; ?>', on: 'top' },
			buttons: [ brNextBtn() ]
		},
		{
			id: 'step-form-9',
			title: "<?= __("Save","bluerabbit"); ?>",
			text: "<?= __("Save this step when you're done. You can always reopen it to make changes.","bluerabbit"); ?>",
			attachTo: { element: '#step-update-button-<?= $s->step_id; ?>', on: 'top' },
			buttons: [ brDoneBtn('step-form', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
		}
	];
	tour.addSteps(steps);
	tour.start();
};
</script>

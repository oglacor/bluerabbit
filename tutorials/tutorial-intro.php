<script>
const tour = new Shepherd.Tour({
    defaultStepOptions: {
        classes: 'shadow-md bg-purple-dark',
        scrollTo: {
          behavior: 'smooth',
          block: 'center'
        },
    },
    useModalOverlay: true,
	keyboardNavigation:false
});
const intro_steps = [
    {
        id: 'step-1',
        title: "<?= isset($adventure) ? __("Welcome to ","bluerabbit")." $adventure->adventure_title" : ""; ?>",
        text: "<?= __("We got your back. Here's a road map so you won't get lost.","bluerabbit"); ?>",
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Skip","bluerabbit"); ?>",
              classes:"br-secondary-button white-color",
              action: function(){
                  return this.complete();
              }
            },
            {
                text: "<?= __("Start Tutorial","bluerabbit"); ?>",
                classes:"blue-bg-400 white-color",
                action: function(){
                    hideAllOverlay();
                    toggleSidebar();
                    return this.next();
                }
            }
        ]
    }, 
	<?php if(!is_page('adventures')){ ?>
    {
        id: 'step-2',
        title: "<?= __("Journey Button","bluerabbit"); ?>",
        text: "<?= __("This will take you to the journey page where you can see your progress.","bluerabbit"); ?>",
        attachTo: { 
            element: '#journey-btn', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
	<?php if(is_page('adventure')){ ?>
    {
        id: 'step-3',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("Here you can see the challenges that you must complete to reach the goal.","bluerabbit"); ?>",
        attachTo: { 
            element: '#the-journey',
            on:'left'
        },
        classes: 'blue-bg-400',
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  <?php if(count($quests) > 0){ ?>
				  activateMilestone('.milestone:first-child');
                  <?php } ?>
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-3-1',
        title: "<?= __("The Quests","bluerabbit"); ?>",
        text: "<?= __("Clicking in the quest icon will open it for you to attempt.","bluerabbit"); ?>",
        attachTo: { 
            element: '.milestone:first-child', 
            on:'bottom' 
        },
        classes: 'blue-bg-400',
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  <?php if(count($quests) > 0){ ?>
                     activateMilestone();
                  <?php } ?>
				  $('#the-journey').toggleClass('journey-map journey-list'); $('#journey-view-button .icon').toggleClass('hidden');
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-3-2',
        title: "<?= __("Journey View Button","bluerabbit"); ?>",
        text: "<?= __("You can switch the journey display by clicking here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#journey-view-button', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				$('#the-journey').toggleClass('journey-map journey-list'); $('#journey-view-button .icon').toggleClass('hidden');
				return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
    {
        id: 'step-4',
        title: "<?= __("Cooper/Nav Menu","bluerabbit"); ?>",
        text: "<?= __("Before we move forward to anything else, this is your panic button. When in doubt, click here. All navigation options are here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#cooper-btn', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  activate('#start');
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-5',
        title: "<?= __("The cooper menu","bluerabbit"); ?>",
        text: "<?= __("You'll find all navigation options here. If you get lost, look inside this menu.","bluerabbit"); ?>",
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  hideAllOverlay();
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-6',
        title: "<?= __("Your status","bluerabbit"); ?>",
        text: "<?= __("Click here to open your status screen as you progress and level up.","bluerabbit"); ?>",
        attachTo: { 
            element: '#profile-box-btn', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  showOverlay('#profile-box');
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-6-1',
        title: "<?= __("Your status","bluerabbit"); ?>",
        text: "<?= __("This is your status screen.","bluerabbit"); ?>",
        attachTo: { 
            element: '#profile-box', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
	<?php if(!is_page('adventures')){ ?>
    {
        id: 'step-7',
        title: "<?= __("Your Level","bluerabbit"); ?>",
        text: "<?= __("This will show your current level. It's important because the best rewards require higher level.","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-player-level', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-8',
        title: "<?= $xp_long_label; ?>",
        text: "<?= __("This are the points that help you level up! Earn them by scanning codes and completing challenges.","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-xp', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
    
    {
        id: 'step-9',
        title: "<?= __("Current ","bluerabbit").$bloo_label; ?>",
        text: "<?= __("Here you can see how much money you've earned. You can use it to exchange for prizes in the Item Shop!","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-bloo', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
	<?php if($use_encounters && !is_page('adventures')){ ?>
    {
        id: 'step-9-1',
        title: "<?= __("Current ","bluerabbit").$ep_label; ?>",
        text: "<?= "$ep_label ($ep_long_label) ".__("allow you to attempt certain quests and challenges. You have a maximum depending on your level.","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-ep', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-9-1',
        title: "<?= __("Current ","bluerabbit").$ep_label; ?>",
        text: "<?= __("So the higher your level, the more Energy you can store to complete harder challenges.","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-ep', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				  hideAllOverlay();
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-9-3',
        title: "<?= __("This Energy icon here","bluerabbit"); ?>",
        text: "<?= __("If you run out of")." $ep_label".__(", click here to recharge.","bluerabbit"); ?>",
        attachTo: { 
            element: '#random-encounter-btn', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				  showOverlay('#profile-box');
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
    {
        id: 'step-10',
        title: "<?= __("My Account","bluerabbit"); ?>",
        text: "<?= __("To edit your profile, click on your profile image","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-animated-chart', 
            on: 'top'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				hideAllOverlay();
				return this.next();
              }
            }
        ]
    }, 
<?php if($use_achievements && !is_page('adventures')) { ?>  
    {
        id: 'step-22',
        title: "<?= __("Code Button","bluerabbit"); ?>",
        text: "<?= __("Use this button to open the code form","bluerabbit"); ?>",
        attachTo: { 
            element: '#magic-code-btn', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				  showOverlay('#magic-code-form');
				  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-22-1',
        title: "<?= __("Code Form","bluerabbit"); ?>",
        text: "<?= __("With this field, you may introduce a code you find. It will help you level up and/or earn resources. Plus you will get a badge!","bluerabbit"); ?>",
         attachTo: { 
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-22-2',
        title: "<?= __("Code Form","bluerabbit"); ?>",
        text: "<?= __("Just type in the code (spaces and all) and click send.","bluerabbit"); ?>",
         attachTo: { 
            element: '#magic-code', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
				hideAllOverlay();
                return this.next();
              }
            }
        ]
    }, 
<?php } ?>
    {
        id: 'step-98',
        title: "<?= __("Activate Tutorial","bluerabbit"); ?>",
        text: "<?= __("You can restart this tutorial with this button.","bluerabbit"); ?>",
        attachTo: { 
            element: '#tutorial-button-start', 
            on: 'bottom'
        },
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-99',
        title: "<?= __("SUPPORT","bluerabbit"); ?>",
        text: "<?= __("If you need to reach out for support, contact ","bluerabbit").$support_email; ?>",
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Next","bluerabbit"); ?>",
              classes:"blue-bg-400 white-color",
              action: function(){
                return this.next();
              }
            }
        ]
    }, 
    
    {
        id: 'step-100',
        title: "<?= __("That's it!","bluerabbit"); ?>",
        text: "<?= __("Don't stop leveling up!","bluerabbit"); ?>",
		cancelIcon: {
			enabled:true
		},
        buttons: [
            {
              text: "<?= __("Close","bluerabbit"); ?>",
              classes:"br-end-tutorial white-color",
              action: function(){
                    hideAllOverlay();
                    return this.complete();
              }
            }
        ]
    }, 
    
];
tour.addSteps(intro_steps);
</script>

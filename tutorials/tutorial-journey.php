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
    {
        id: 'step-2_0',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("This is the journey.","bluerabbit"); ?>",
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-2_1',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("All the milestones of the adventure appear here.","bluerabbit"); ?>",
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-2_2',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("As you complete them, some new ones might be unlocked or appear.","bluerabbit"); ?>",
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-2_3',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("Clicking on the adventure title you can come back to this screen anytime.","bluerabbit"); ?>",
        attachTo: { 
            element: '#adventure-title-t_id',
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
                     activateMilestone('<?=$first_milestone_for_tutorial; ?>', '#ui-touch-milestone','#ui-touch-milestone-reverse');
                  <?php } ?>
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-2_4',
        title: "<?= __("Milestones","bluerabbit"); ?>",
        text: "<?= __("Click on the milestone to activate it","bluerabbit"); ?>",
        attachTo: { 
            element: '#milestone-<?=$first_milestone_for_tutorial; ?>', 
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-2_5',
        title: "<?= __("Milestones","bluerabbit"); ?>",
        text: "<?= __("You can read the details and start the challenge.","bluerabbit"); ?>",
        attachTo: { 
            element: '#milestone-preview', 
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-4_0',
        title: "<?= __("Navigation","bluerabbit"); ?>",
        text: "<?= __("All navigation options are in one place.","bluerabbit"); ?>",
        attachTo: { 
            element: '#start-button', 
            on:'top'
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
 				 activateStartMenu();
                 return this.next();
              }
            }
        ]
    },
    {
        id: 'step-4_1',
        title: "<?= __("Options","bluerabbit"); ?>",
        text: "<?= __("This button opens the main navigation.","bluerabbit"); ?>",
        classes: 'blue-bg-400',
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
        id: 'step-4_2',
        title: "<?= __("Options","bluerabbit"); ?>",
        text: "<?= __("You can see all available sections here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#main-nav',
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-4_3',
        title: "<?= __("Options","bluerabbit"); ?>",
        text: "<?= __("These are some quick links that might help.","bluerabbit"); ?>",
        attachTo: { 
            element: '#core-nav',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-4_4',
        title: "<?= __("The Journey","bluerabbit"); ?>",
        text: "<?= __("Quick access this page.","bluerabbit"); ?>",
        attachTo: { 
            element: '#journey-btn',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
	<?php if($use_achievements){ ?>
    {
        id: 'step-4_5',
        title: "<?= __("The Magic Code","bluerabbit"); ?>",
        text: "<?= __("Open the field to input a magic code.","bluerabbit"); ?>",
        attachTo: { 
            element: '#magic-code-btn',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
	<?php if($use_item_shop){ ?>
    {
        id: 'step-4_6',
        title: "<?= __("The Item Shop","bluerabbit"); ?>",
        text: "<?= __("You can purchase items with your resources.","bluerabbit"); ?>",
        attachTo: { 
            element: '#item-shop-btn',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-4_7',
        title: "<?= __("The Backpack","bluerabbit"); ?>",
        text: "<?= __("You can find what you have purchased in here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#my-backpack-btn',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
	<?php if($use_encounters){ ?>
    {
        id: 'step-4_5',
        title: "<?= __("Random Encounters","bluerabbit"); ?>",
        text: "<?= __("Open a random encounter to recover energy.","bluerabbit"); ?>",
        attachTo: { 
            element: '#random-encounter-btn',
            on:'top'
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
				  activateStartMenu();
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>
    {
        id: 'step-5_0',
        title: "<?= __("Profile","bluerabbit"); ?>",
        text: "<?= __("You can find your profile info in here.","bluerabbit"); ?>",
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
				activate('#profile-box');
				return this.next();
              }
            }
        ]
    }, 
    {
        id: 'step-5_1',
        title: "<?= __("Profile","bluerabbit"); ?>",
        text: "<?= __("You can see all your adventure stats here.","bluerabbit"); ?>",
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
        id: 'step-5_1',
        title: "<?= __("Current Rank","bluerabbit"); ?>",
        text: "<?= __("Depending on your achievements, you can see your current Rank here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#current-rank', 
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
        id: 'step-5_2',
        title: "<?= __("Profile","bluerabbit"); ?>",
        text: "<?= __("Your profile can be updated by clicking on the image.","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-animated-chart', 
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
        id: 'step-5_3',
        title: "<?= __("Your Level","bluerabbit"); ?>",
        text: "<?= __("Your current level in the adventure.","bluerabbit"); ?>",
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
        id: 'step-5_4',
        title: "<?= $xp_long_label; ?>",
        text: "<?= __("These points help you level up! Earned by completing milestones and achievements.","bluerabbit"); ?>",
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
        id: 'step-5_5',
        title: "<?= $bloo_long_label; ?>",
        text: "<?= __("Here you can see how much money you've earned. You can use it to exchange for items in the Item Shop!","bluerabbit"); ?>",
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
	<?php if($use_encounters){ ?>
    {
        id: 'step-5_6',
        title: "<?= $ep_long_label; ?>",
        text: "<?= __("The amount of rechargeable points you have. Recover them by completing random encounters","bluerabbit"); ?>",
        attachTo: { 
            element: '#status-ep',
            on:'top'
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
                  return this.next();
              }
            }
        ]
    }, 
	<?php } ?>

    {
        id: 'step-5_7',
        title: "Logout button",
        text: "<?= __("If you need to logout, here is your way out.","bluerabbit"); ?>",
        attachTo: { 
            element: '#logout-button',
            on:'top'
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
				activate('#profile-box');
				return this.next();
              }
            }
        ]
    }, 
    <?php if($myGuildExists){ ?>
    {
        id: 'step-6',
        title: "<?= __("Guilds","bluerabbit"); ?>",
        text: "<?= __("You can access your guild info here.","bluerabbit"); ?>",
        attachTo: { 
            element: '#guild-btn', 
            on: 'bottom'
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
        id: 'step-6',
        title: "<?= __("Guilds","bluerabbit"); ?>",
        text: "<?= __("Come often to the guild page to check the leaderboard.","bluerabbit"); ?>",
        attachTo: { 
            element: '#guild-btn', 
            on: 'bottom'
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

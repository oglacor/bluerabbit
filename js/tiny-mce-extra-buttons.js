// JavaScript Document

(function() {
	tinymce.PluginManager.add('player_data', function( editor, url ) {
		editor.addButton('player_data', {
			title: 'Insert Player Data',
			icon: ' dashicons-before dashicons-nametag',
            onclick : function() {
                editor.windowManager.open({
                    title: 'Select Player Data',
                    body: [
                        {
                            type: 'listbox', 
                            name: 'player_data', 
                            label: 'Data', 
                            'values': [
                                {text: 'Nickname', value: 'player_nickname'},
                                {text: 'Full Name', value: 'player_display_name'},
                                {text: 'First Name', value: 'player_first'},
                                {text: 'Hexad Player Type', value: 'player_hexad'}
                            ]
                        },
                    ],
                    onSubmit: function(e) { 
                        editor.focus();
                        editor.insertContent('[player_data field="'+e.data.player_data+'"]');
                    }
                });
            }
		});
	});
})();
(function() {
	tinymce.PluginManager.add('quest_instructions_page_break', function( editor, url ) {
		editor.addButton('quest_instructions_page_break', {
			title: 'Page break',
			icon: ' dashicons-before dashicons-welcome-add-page',
			onclick: function() {
				editor.insertContent('[page_break]');
			}
		});
	});
})();
(function() {
	tinymce.PluginManager.add('insert_objective', function( editor, url ) {
		editor.addButton('insert_objective', {
            title : 'Insert objective',
            text : 'Insert objective',
			onclick: function() {
                var the_values = JSON.parse($('#objectives-list').val());
                editor.windowManager.open({
                    title: 'Insert objective',
                    body: [
                        {
                            type: 'listbox', 
                            name: 'objective_data', 
                            ep: 'ep', 
                            label: 'objective', 
                            values: the_values
                        },
                    ],
                    onSubmit: function(e) { 
                        editor.focus();
                        editor.insertContent('[mission_objective energy_cost='+e.data+']' + e.data.objective_data + '[/mission_objective]');
                    }
                });
			}
		});
	});
})();

(function() {
	tinymce.PluginManager.add('mark_objective', function( editor, url ) {
		editor.addButton('mark_objective', {
            title : 'Mark objective',
            text : 'Mark objective',
			onclick: function() {
				var element = editor.selection.getNode();
				if($(element).is('img')){
					$(element).addClass('img-objective-button');
				}else{
					var selected_text = editor.selection.getContent();
					if(selected_text){
						editor.insertContent('[mission_objective energy_cost=10]'+selected_text+'[/mission_objective]');
					}
				}
			}
		});
	});
})();
(function() {
	tinymce.PluginManager.add('boss_fight_hit', function( editor, url ) {
		editor.addButton('boss_fight_hit', {
            title : 'Boss fight hit',
            text : 'Select step from fight to activate',
			onclick: function() {
                var the_values = JSON.parse($('#steps-list-values').val());
                editor.windowManager.open({
                    title: 'Boss fight hit',
                    body: [
                        {
                            type: 'listbox', 
                            name: 'step_data', 
                            label: 'step', 
                            values: the_values
                        },
                    ],
                    onSubmit: function(e) { 
						var selected_text = editor.selection.getContent();
                        editor.focus();
                        editor.insertContent('[boss_fight_hit step_id='+e.data.step_data+' ep=0]' + selected_text + '[/boss_fight_hit]');
                    }
                });
			}
		});
	});
})();

(function() {
	tinymce.PluginManager.add('spend_ep', function( editor, url ) {
		editor.addButton('spend_ep', {
            title : 'Spend EP',
            text : 'Spend EP',
			onclick: function() {
				var element = editor.selection.getNode();
				var selected_text = editor.selection.getContent();
				if(selected_text){
					editor.insertContent('[spend_ep ep="10"]'+selected_text+'[/spend_ep]');
				}
			}
		});
	});
})();
(function() {
	tinymce.PluginManager.add('animate_number', function( editor, url ) {
		editor.addButton('animate_number', {
            title : 'Animate Number',
			icon: ' dashicons-before dashicons-calculator',
			onclick: function() {
				var element = editor.selection.getNode();
				var selected_text = editor.selection.getContent();
				if(selected_text){
					editor.insertContent('[animate_number number=10 speed=1500 delay=0 decimals=0 label=""]'+selected_text+'[/animate_number]');
				}else{
					editor.insertContent('[animate_number number=10 speed=1500 delay=0 decimals=0 label=""] >>Symbol Here<< [/animate_number]');
				}
			}
		});
	});
})();

(function() {
	tinymce.PluginManager.add('opened_backpack', function( editor, url ) {
		editor.addButton('opened_backpack', {
			title: 'Add Backpack',
			icon: ' dashicons-before dashicons-universal-access',
			onclick: function() {
				var the_items = JSON.parse($('#key-items-for-opened-backpack').val());
				var the_steps = JSON.parse($('#steps-list-values').val());
                editor.windowManager.open({
                    title: 'Select the correct key item',
                    body: [
                        {
                            type: 'listbox',
                            name: 'correct_item',
                            label: 'Correct Item',
                            values: the_items
                        },
                        {
                            type: 'listbox',
                            name: 'step_correct',
                            label: 'Step Correct',
                            values: the_steps
                        },
                        {
                            type: 'listbox',
                            name: 'step_wrong',
                            label: 'Step Wrong',
                            values: the_steps,
							classes:"red-bg-400 white-color"
                        },
                        {
                            type: 'textbox',
                            name: 'items_array',
                            label: 'Items to show (separate IDs by comma)',
                            values: '',
							classes:'form-ui w-full'
                        },
                        {
                            type: 'textbox',
                            name: 'ep_cost',
                            label: 'Ep Cost',
                            values: '0',
							classes:'form-ui w-full'
                        },
                    ],
                    onSubmit: function(e) { 
						//e.data.items_data
						var adv_id=$('#the_adventure_id').val();
						editor.insertContent('[backpack adventure_id='+adv_id+' correct_item='+e.data.correct_item+' step_correct='+e.data.step_correct+' step_wrong='+e.data.step_wrong+' items="'+e.data.items_array+'" ep='+e.data.ep_cost+']');
                    }
                });
			}
		});
	});
})();

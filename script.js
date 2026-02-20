//////////////////  REGISTER NEW PLAYER  ////////////////
function jumpToStepByHash(){
	let step = window.location.hash.substring(1);
	let step_number = step.replace('step-','');
	if(!step_number){ step_number = 1;}
	jumpToStep(step_number); 
}
function jumpToQuestionByHash(){
	let step = window.location.hash.substring(1);
	let step_number = step.replace('step-','');
	if(!step_number){
		jumpToQuestion(0); 
	}else{
		jumpToQuestion(step_number); 
	}
	
}

function registerNewPlayer(){
	showLoader();
	let nickname = $('#new_user_nickname').val();
	let email = $('#new_user_email').val();
	let password = $('#new_password').val();
	let lang = $('#new_the_lang').val();
	let redirect = $('#the_redirect').val();
	let nonce = $('#register_nonce').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'bluerabbit_add_new_player', redirect:redirect, nickname:nickname, email:email, password:password, lang:lang, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		},
	});
}
function enrollUser(p_email=null){
	showLoader();
	//$('#btn-reg-player').unbind('click');
	$('#btn-reg-player').attr('disabled',true);
	let new_user='';
	let email = p_email;
	if(p_email == 'new'){
		email = $('#new-player-email').val();
		new_user='make-new';
	}
	let nickname = $('#new-player-username').val();
	let password = $('#new-player-user-password').val();
	let lang = $('#new-player-lang').val();
	let nonce = $('#register_nonce').val();

	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'enrollUser', adventure_id:$('#the_adventure_id').val(), new_user:new_user, nickname:nickname, email:email, password:password, lang:lang, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			let this_data = JSON.parse(data_received);
			if(this_data.success==false){
				$('#btn-reg-player').attr('disabled', false);
			}else{
				$('#btn-reg-player').unbind('click');
			}
			displayAjaxResponse(data_received);
		},
	});
}
function checkUserDataExists(input_field){
	showLoader('small');
	$('#btn-reg-player').unbind('click');
	$('#add-single-player-form, #add-single-player-form .player-data-content').removeClass('active');
	$('#new-player-username, #new-player-email, #new-player-user-password').val('');
	if(input_field.value != ''){
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'checkUserDataExists', value:input_field.value, adventure_id:$('#the_adventure_id').val()}),
			method: "POST",
			success: function(data_received) {
				let data = JSON.parse(data_received);
				hideAllOverlay();
				if(data.warning){
					$('#new-player-warnings').text(data.warning);
					$('#new-player-warnings').removeClass().addClass(data.warning_class+" new-player-warnings");
					$("#register_nonce").val(data.new_nonce);
				}
				if(data.user_exists == true && data.user_enroll_status == 'out'){
					$('#add-single-player-form').addClass('active');
					$('#btn-reg-player').click(function(){
						enrollUser(data.user_email);
					});
				}else if(data.user_exists == false){
					$('#add-single-player-form, #add-single-player-form .player-data-content').addClass('active');
					if(data.is_email == true){
						$('#new-player-email').val(input_field.value).attr({'readonly':true, 'disabled':true});
						$('#new-player-username').val('');
					}else{
						$('#new-player-username').val(input_field.value).attr({'readonly':true, 'disabled':true});
						$('#new-player-email').val('');
					}

					$('#btn-reg-player').click(function(){
						enrollUser('new');
					});
				}
				$("#notify-message ul.content").append(data.message);
				$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
					$(this).remove();
				});
			},
		});
	}else{
		$('#new-player-warnings').text("Please enter a nickname or email.");
		$('#new-player-warnings').removeClass().addClass("error new-player-warnings");
		hideAllOverlay();
	}
}
function uploadBulkQuestions(){
	const upload_bulk_questions_form = document.getElementById('upload_bulk_questions_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_questions')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkQuestions'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	formData.append('quest_id', $('#the_quest_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				let data = JSON.parse(response);
				console.log(data.debug);
				for(let i=0; i<data.messages.length; i++){
					$("#notify-message ul.content").append(data.messages[i]);
					let notificationTimeOut1 = setTimeout ( function (){
						$("#notify-message ul.content li:last-child").addClass('active');
						let last_message = $("#notify-message ul.content li:last-child");
						let notificationTimeOut2 = setTimeout(function (){
							last_message.removeClass('active');
							let notificationTimeOut3 = setTimeout(function (){
								last_message.remove();
							},300);
						}, 1000); 
					},50);
				}
				hideAllOverlay();
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}



function uploadBulkQuests(){
	const upload_bulk_quests_form = document.getElementById('upload_bulk_quests_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_quests')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkQuests'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				let data = JSON.parse(response);
				for(let i=0; i<data.messages.length; i++){
					$("#notify-message ul.content").append(data.messages[i]);
					$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
				}
				hideAllOverlay();
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}
function uploadBulkItems(){
	const upload_bulk_items_form = document.getElementById('upload_bulk_items_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_items')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkItems'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				displayAjaxResponse(response);
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}
function uploadBulkAchievements(){
	const upload_bulk_achievments_form = document.getElementById('upload_bulk_achievments_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_achievements')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkAchievements'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				displayAjaxResponse(response);
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}
function uploadBulkSessions(){
	const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_sessions')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkSessions'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				let data = JSON.parse(response);
				for(let i=0; i<data.messages.length; i++){
					$("#notify-message ul.content").append(data.messages[i]);
					$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
				}
				hideAllOverlay();
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}
function uploadBulkSpeakers(){
	const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_speakers')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkSpeakers'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				let data = JSON.parse(response);
				for(let i=0; i<data.messages.length; i++){
					$("#notify-message ul.content").append(data.messages[i]);
					$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
				}
				hideAllOverlay();
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}
function uploadBulkUsers(){
	const upload_bulk_users_form = document.getElementById('upload_bulk_users_form');
	const formData = new FormData();

	let file = $('#the_csv_file_with_users')[0].files[0]; 
	formData.append('csv_file', file); 
	formData.append('action', 'uploadBulkUsers'); 
	formData.append('adventure_id', $('#the_adventure_id').val()); 
	
	if(file){
		showLoader();
		$.ajax({
			url: runAJAX.ajaxurl,
			data: formData,
			processData: false, // Required for FormData
			contentType: false, // Required for FormData
			type: "POST",
			method: "POST",
			success: function(response){
				let data = JSON.parse(response);
				if(data.success){
					$("#just-uploaded-users-body").html('').append(data.table_content);
					$("#call-to-action").html(data.cta);
					for(let i=0; i<data.messages.length; i++){
						$("#notify-message ul.content").append(data.messages[i]);
						$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
							$(this).remove();
						});
					}
					selectAllCheckBoxes();
				}else{
					for(let i=0; i<data.errors.length; i++){
						$("#notify-message ul.content").append(data.errors[i]);
						$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
							$(this).remove();
						});
					}
				}
				hideAllOverlay();
			}
		});
	}else{
		notification('#msg-no-file-selected', 1000,'','player');
	}
}

function bulkEnrollUsers(){
	let new_users = [];
	let existing_users = [];
	$('.select-element:checkbox:checked').each(function( index ) {
		
		//tr id => row-new-bulk-user-$row_index
		let row_id = $(this).attr('data-id');
		if($('#row-new-bulk-user-'+row_id).hasClass('enroll')){
			let existing_users_values={
				nickname:$('#row-new-bulk-user-'+row_id+' .nickname').text(),
				password:$('#row-new-bulk-user-'+row_id+' .password').text(),
				email:$('#row-new-bulk-user-'+row_id+' .email').text(),
				firstname:$('#row-new-bulk-user-'+row_id+' .firstname').text(),
				lastname:$('#row-new-bulk-user-'+row_id+' .lastname').text(),
				lang:$('#row-new-bulk-user-'+row_id+' .lang').text(),
				///////////////////////
				gender:$('#row-new-bulk-user-'+row_id+' .gender').text(),
				work_level:$('#row-new-bulk-user-'+row_id+' .work_level').text(),
				work_function:$('#row-new-bulk-user-'+row_id+' .work_function').text(),
				work_sub_function:$('#row-new-bulk-user-'+row_id+' .work_sub_function').text(),
				job_profile:$('#row-new-bulk-user-'+row_id+' .job_profile').text(),
				buisness_pillar:$('#row-new-bulk-user-'+row_id+' .buisness_pillar').text(),
				work_cluster:$('#row-new-bulk-user-'+row_id+' .work_cluster').text(),
				work_country:$('#row-new-bulk-user-'+row_id+' .work_country').text(),
				work_location:$('#row-new-bulk-user-'+row_id+' .work_location').text(),




				user_id:$(this).attr('data-user-id'),
			};
			existing_users.push(existing_users_values);
		}else if($('#row-new-bulk-user-'+row_id).hasClass('register')){
			let new_users_values={
				nickname:$('#row-new-bulk-user-'+row_id+' .nickname').text(),
				password:$('#row-new-bulk-user-'+row_id+' .password').text(),
				email:$('#row-new-bulk-user-'+row_id+' .email').text(),
				firstname:$('#row-new-bulk-user-'+row_id+' .firstname').text(),
				lastname:$('#row-new-bulk-user-'+row_id+' .lastname').text(),
				lang:$('#row-new-bulk-user-'+row_id+' .lang').text(),
				////////////////////////////
				gender:$('#row-new-bulk-user-'+row_id+' .gender').text(),
				work_level:$('#row-new-bulk-user-'+row_id+' .work_level').text(),
				work_function:$('#row-new-bulk-user-'+row_id+' .work_function').text(),
				work_sub_function:$('#row-new-bulk-user-'+row_id+' .work_sub_function').text(),
				job_profile:$('#row-new-bulk-user-'+row_id+' .job_profile').text(),
				buisness_pillar:$('#row-new-bulk-user-'+row_id+' .buisness_pillar').text(),
				work_cluster:$('#row-new-bulk-user-'+row_id+' .work_cluster').text(),
				work_country:$('#row-new-bulk-user-'+row_id+' .work_country').text(),
				work_location:$('#row-new-bulk-user-'+row_id+' .work_location').text(),
			};
			new_users.push(new_users_values);
		}

	});
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'bulkEnrollUsers', new_users:new_users, adventure_id:$('#the_adventure_id').val(), existing_users:existing_users}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function resetDemoAdventurePlayer(){
	let nonce = $('#reset_demo_nonce').val();
	let adventure_id = $('#the_adventure_id').val();
	let player_password = $('#the_player_password').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetDemoAdventurePlayer', player_password:player_password, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function resetPlayerPassword(){
	let nonce = $('#reset_user_password_nonce').val();
	let adventure_id = $('#the_adventure_id').val();
	let current_gm_password = $('#the_gm_password').val();
	let new_player_password = $('#the_player_password').val();
	let new_player_password_confirm = $('#the_player_password_confirm').val();
	let player_affected = $('#the_player_to_update').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetPlayerPassword',adventure_id:adventure_id, player_affected:player_affected, new_player_password:new_player_password, new_player_password_confirm:new_player_password_confirm, current_gm_password:current_gm_password, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function br_logout(){
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'br_logout'}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			document.location.href=data.location;
		}
	});
}

////////////////////////////////////////// Rate Quest ////////////////////////////////////////////

function rateQuest(quest_id,rating){
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'rateQuest', quest_id:quest_id,rating:rating}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function do_resize(textbox) {

	let maxrows=5; 
	let txt=textbox.value;
	let cols=textbox.cols;

	let arraytxt=txt.split('\n');
	let rows=arraytxt.length; 

	for (let i=0;i<arraytxt.length;i++) 
		rows+=parseInt(arraytxt[i].length/cols);

	if (rows>maxrows) textbox.rows=maxrows;
	else textbox.rows=rows;
}

function formatToCurrency(amount){
	return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
}

function animateNumber(who, speed=500,p_delay=0, decimals=0, format=''){
	let aniNumber = 0;
	$(who).each(function () {
		$(this).prop('Counter',$('.number',this).text()).stop().delay(p_delay).animate({
			Counter: $('.end-value',this).val(),
		}, {
			duration: speed,
			step: function (now) {
				if(format =='money'){
					aniNumber = formatToCurrency(now,2);
					$('.number',this).text(aniNumber);
				}else{
					if(decimals > 0){
						$('.number',this).text((now.toFixed(decimals)));
					}else{
						$('.number',this).text(Math.ceil(now));
					}
				}
			},
			complete: function(){
				//alert('Complete');
			}
		});
	});
}
function deadlineCountdown (the_deadline){
	let deadlineInterval;
	let countDownDate = new Date(the_deadline).getTime();
	if(deadlineInterval){
		clearInterval(deadlineInterval);
	}
	deadlineInterval = setInterval(function() {
		let now = new Date().getTime();
		let distance = countDownDate - now;
		let days = Math.floor(distance / (1000 * 60 * 60 * 24));
		let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		let seconds = Math.floor((distance % (1000 * 60)) / 1000);
		//let counter = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
		$('#deadline-countdown #deadline-days .number').text(days);
		$('#deadline-countdown #deadline-hours .number').text(hours);
		$('#deadline-countdown #deadline-minutes .number').text(minutes);
		$('#deadline-countdown #deadline-seconds .number').text(seconds);
		if (distance < 0) {
			clearInterval(deadlineInterval);
			$('#deadline-countdown').text("Expired!");
		}
	}, 1000);
}

function notify(message="", icon="check", color="blue", message_delay=1000){

	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'br_notify', message: message, icon: icon, color: color}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			$("#notify-message ul.content").append(data.message);
			setTimeout ( function (){
				$("#notify-message ul.content li:last-child").addClass('active');
				let last_message = $("#notify-message ul.content li:last-child");
				setTimeout(function (){
					last_message.removeClass('active');
					setTimeout(function (){
						last_message.remove();
						if(data.reload){
							document.location.reload();
						}
					},300);

				}, message_delay); 
			},1);



		}
	});
}
function notification(message, msg_delay=1000, var_content=null, var_icon=null){
	$("#notify-message ul.content").append($(message).html());
	let notificationTimeOut1 = setTimeout ( function (){
		$("#notify-message ul.content li:last-child").addClass('active');
		let last_message = $("#notify-message ul.content li:last-child");
		let notificationTimeOut2 = setTimeout(function (){
			last_message.removeClass('active');
			let notificationTimeOut3 = setTimeout(function (){
				last_message.remove();
			},300);
		}, msg_delay); 
	},1);
}

function copyTextFrom(input_id, trigger_id) {
	$(input_id).attr('type','text');
	let copyText =$(input_id);
	copyText.select();
	document.execCommand("copy");
	$(input_id).attr('type','hidden');
	if(trigger_id){
		$(trigger_id).addClass('active');
		let timeout = setTimeout(function(){
			$(trigger_id).removeClass('active');
		},1500);
	}
	notification('#msg-text-copied', 1000, 'Text copied','duplicate');
}

function assignInstructionsPages(){
	if($('#quest-instructions .instructions-step').length > 1){
		$('#last-prev-button').removeClass('hidden');
	}
	$('#quest-instructions .instructions-step').each(function (index,element){
		$(this).attr('id',"instructions-step-"+index);
		$('input.step-id-value',this).val(index);
		$('.prev-button',this).attr('onClick','questStep('+(index-1)+')');
		$('.next-button',this).attr('onClick','questStep('+(index+1)+')');
		if(index <=0){
			$(this).addClass('active');
		}
	});
	
}
function showMenu(who){
	$('.nav-group nav').removeClass('active');
	$(who).addClass('active');
}
function questStep(id){
	$('#quest-instructions .instructions-step').removeClass('active');
	$('#instructions-step-'+id).addClass('active');
}

function animateScroll(who, center=null, difference=null){
//	let mytop =  Math.round($(this).offset().top - $(window).scrollTop()); - ($(who).offset().top 
	let divOffsetTop = $(who).offset().top-30;
	if(center > 0){
		if(difference > 0){
			divOffsetTop = $(who).offset().top-($(window).height()/2)+(difference);
		}else{
			divOffsetTop = $(who).offset().top-($(window).height()/2)-($(who).height()/2);
		}
	}
	$("html, body").animate({ scrollTop: divOffsetTop }, 300);
}
function animateScrollBottom(who){
	let divOffsetTop = $(who).offset().top - $( window ).height()+150;
	$("html, body").animate({ scrollTop: divOffsetTop }, 1500);
}
function loadContent(content,id=0){
	hideAllOverlay();
	showLoader();
	$('#overlay-content .content').html('');
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadContent', adventure_id: adventure_id, content:content, id:id}),
		method: "POST",
		success: function(data_received) {
			$('#overlay-content .content').html(data_received);
			let flipTimeout = setTimeout(function (){
				$('#overlay-content').addClass('active');
				$('.loader, .small-loader').removeClass('active');
			},10);
		}
	});
}
function unloadContent(who=null){
	hideAllOverlay();
	let clearTimeout;
	if(!who){
		 clearTimeout =	setTimeout(function(){ $("#overlay-content .content").html('');  }, 500);
	}else{
		$(who).removeClass('active');
		clearTimeout = setTimeout(function(){ $(who).html('');  }, 500);
	}

}

function loadTabiEditor(id=0){
	hideAllOverlay();
	showLoader();
	$('#tabi-editor-container').html('');
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadContent', adventure_id: adventure_id, content:'tabi-editor', id:id}),
		method: "POST",
		success: function(data_received) {
			$('#tabi-editor-container').html(data_received);
			let tabiEditorContainerTimeout = setTimeout(function (){
				$('#tabi-editor-container').addClass('active');
				$('.loader, .small-loader').removeClass('active');
				initializeTabiEditorDrag();
			},10);
		}
	});
}

function initializeTabiEditorDrag(){
	$(".tabi-editor-pieces-list-sortable").sortable({
		update: function(event, ui) {
			sortZindex();
		}
	});
	$('#tabi-pieces .tabi-piece').each(function(){
		applyTransform($(this).data('piece-id'),1);
	});
	$('#tabi-pieces .tabi-piece').draggable({
		start: function () {
			$(this).addClass("dragging");
		},
		drag: function (event, ui) {
			let piece = $(this);
			let pieceX = (ui.position.left) / $('#tabi-pieces').width() * 100;
			let pieceY = (ui.position.top) / $('#tabi-pieces').height() * 100;
			$('.piece-x',this).val(pieceX);
			$('.piece-y',this).val(pieceY);
		},
		stop: function () {
			applyTransform($(this).data('piece-id'),1);
			$(this).removeClass("dragging");
		}
	});
}
function sortZindex(){
	$('#tabi-editor-pieces-list-sortable li.tabi-piece-list-item').each(function(index){
		let item_id = $(this).data('piece-id');
		$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val(100-index);
		$('.data-piece-z',this).text(100-index);
		applyTransform(item_id);
	});

}
function editTabiPiece(item_id){
	let piece = $('#tabi-piece-'+item_id);
	let li_piece = $('#list-item-piece-'+item_id);
	if(!piece.hasClass('editing')){
		$('.tabi-piece, .tabi-piece-list-item').removeClass('editing');
		piece.addClass('editing');
		li_piece.addClass('editing');
	}else{
		$('.tabi-piece, .tabi-piece-list-item').removeClass('editing');
	}
}
function resetTabiPiece(item_id){
	$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-scale`).val(10);
	$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-rotation`).val(0);
	$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val(1);
	$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-x`).val(10);
	$(`#tabi-piece-${item_id} .tabi-piece-data input.piece-y`).val(10);
	applyTransform(item_id,1);
}
function applyTransform(item_id, setup=null){
	let scaleVal = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-scale`).val();
	let rotationVal = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-rotation`).val();
	let zIndex = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-z`).val();
    if(zIndex < 1) { zIndex = 1; }
	let xPos = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-x`).val();
	let yPos = $(`#tabi-piece-${item_id} .tabi-piece-data input.piece-y`).val();
	let transform_values = `scale(${zIndex}) rotate(${rotationVal}deg)`;

	$('#tabi-piece-image-'+item_id).css({'transform':transform_values});
	$('#tabi-piece-'+item_id).css({'z-index':zIndex, 'width':scaleVal+'%'});

	if(setup){
		$('#tabi-piece-'+item_id).css({'top':yPos+'%', 'left':xPos+'%'});
	}
	let item_data = {
		item_x: xPos,
		item_y: yPos,
		item_z: zIndex,
		item_scale: scaleVal,
		item_rotation: rotationVal,
	}
	$('.small-loader').addClass('active');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'saveTabiPiecePosition', item_id:item_id, item_data:item_data}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
			$('.small-loader').removeClass('active');
		}
	});
	
}

function zUp(id){
	let $zInput = $(`#tabi-piece-${id} .piece-z`);
	$zInput.val(+$zInput.val() + 1);
	applyTransform(id);
}
function zDown(id){
	let $zInput = $(`#tabi-piece-${id} .piece-z`);
	if($zInput.val() > 0){
		$zInput.val(+$zInput.val() - 1);
	}
	applyTransform(id);
}
function scaleUp(id){
	let $scaleInput = $(`#tabi-piece-${id} .piece-scale`);
	if($scaleInput.val() < 100){
		$scaleInput.val(+$scaleInput.val() + 1);
	}
	applyTransform(id);
}
function scaleDown(id){
	let $scaleInput = $(`#tabi-piece-${id} .piece-scale`);
	if($scaleInput.val() > 1){
		$scaleInput.val(+$scaleInput.val() - 1);
	}
	applyTransform(id);
}
function rotateCW(id){
	let $rotateInput = $(`#tabi-piece-${id} .piece-rotation`);
	$rotateInput.val(+$rotateInput.val() + 15);
	if($rotateInput.val() > 345){
		$rotateInput.val(0);
	}
	applyTransform(id);
}
function rotateCCW(id){
	let $rotateInput = $(`#tabi-piece-${id} .piece-rotation`);
	$rotateInput.val(+$rotateInput.val() - 15);
	if($rotateInput.val() < 15){
		$rotateInput.val(360);
	}
	applyTransform(id);
}
function resetMilestoneSizes(){
    $(`.milestone .milestone-data .z-pos`).val(1);
	$(`.milestone`).each(function(){
		updateMilestonePosition($(this).data('id'));
	});
}

function updateMilestonePosition(id){
	let milestone = $('#milestone-'+id);
	let topPos = $(`#milestone-${id} .milestone-data input.top`).val();
	let leftPos = $(`#milestone-${id} .milestone-data input.left`).val();
/*
	let xPos = $(`#milestone-${id} .milestone-data input.x-pos`).val();
	let yPos = $(`#milestone-${id} .milestone-data input.y-pos`).val();
	let rotation = $(`#milestone-${id} .milestone-data input.rotation`).val();
*/

	let zPos = $(`#milestone-${id} .milestone-data input.z-pos`).val();
    if(zPos < 0.5) { zPos = 0.5; }else if(zPos < 0.5) { zPos = 0.5; }
	let xPos = 0;
	let yPos = 0;
	let rotation =0;
	let milestone_data = {
		top: topPos,
		left: leftPos,
		x: xPos,
		y: yPos,
		z: zPos,
		rotation: rotation,
	}
	$('.small-loader').addClass('active');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateMilestonePosition', milestone_id:id, milestone_data:milestone_data}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
			$('.small-loader').removeClass('active');
		}
	});
}
function initializeBuilderMilestones(){
	$('#builder .milestone').draggable({
		handle: '.milestone-handle',
		snap:true,
		snapTolerance: 5,
		start: function () {
			$(this).addClass("dragging");
		},
		drag: function (event, ui) {
			let posTop = (ui.position.top);
			let posLeft = (ui.position.left);
			$(`.milestone-data input.top`,this).val(posTop);
			$(`.milestone-data input.left`,this).val(posLeft);
		},
		stop: function () {
			updateMilestonePosition($(this).data('id'));
			$(this).removeClass("dragging");
		}
	});
}
function resetMilestonesToList(){
	$(`.milestone .milestone-data .z-pos`).val(0);
	$(`.milestone .milestone-data .rotation`).val(0);
	$(`.milestone`).css({'transform':`scale(1) rotate(0deg)`});
	let resetX = 50, resetY = 50;
	for(let i=0; i<=$(`.milestone`).length; i++){
		$(`.milestone.milestone-order-${i} .milestone-data input.left`).val(resetX);	
		$(`.milestone.milestone-order-${i} .milestone-data input.top`).val(resetY);
		$(`.milestone.milestone-order-${i}`).css('left',resetX);	
		$(`.milestone.milestone-order-${i}`).css('top',resetY);
		resetX += 150;
		if(resetX > 750){
			resetX = 50;
			resetY += 150;
		}
	}
	$(`.milestone`).each(function(){
		updateMilestonePosition($(this).data('id'));
	});
	setTimeout ( function (){
		$("#notify-message ul.content").html('');
	}, 30000); 
	
}

function generateHexFilled(radius) {
	const results = [];

	for (let q = -radius; q <= radius; q++) {
		const r1 = Math.max(-radius, -q - radius);
		const r2 = Math.min(radius, -q + radius);
		for (let r = r1; r <= r2; r++) {
			results.push([q, r]);
		}
	}
	return results;
}

function resetMilestonePositions(groupby= 'data-color', spacing = 50, delayStep = 5, maxRowWidth = 1500, originOffset = 250) {
	const $milestones = $('.milestone');
	const groups = {
		'red': [],
		'pink': [],
		'teal': [],
		'indigo': [],
		'blue': [],
		'cyan': [],
		'deep-orange': [],
		'purple': [],
		'brown': [],
		'amber': []
	};

	// 1. Group by color
	$milestones.each(function () {
		const $el = $(this);
		const color = $el.attr(groupby);
		if (!groups[color]) groups[color] = [];
		groups[color].push($el);
	});

	let offsetX = 0;
	let offsetY = 0;
	let groupCount = 0;

	for (let color in groups) {
		const group = groups[color];
		const centerIndex = Math.floor(group.length);
		const usedPositions = new Set();

		// Axial coordinates for beehive pattern
const axialOffsets = [
  [0, 0],
  [1, 0], [0, 1], [-1, 1], [-1, 0], [0, -1], [1, -1],
  [2, 0], [1, 1], [0, 2], [-1, 2],[-2, 2], [-2, 1], [-2, 0], [-1, -1], [0, -2], [1, -2], [2, -2], [2, -1],
  [3, 0], [2, 1], [1, 2], [0, 3], [-1, 3], [-3, 1], [-3, 0], [-2, -1], [-1, -2], [0, -3], [1, -3], [3, -1],
  [4, 0], [3, 1], [2, 2], [1, 3], [0, 4], [-1, 4], [-2, 3], [-3, 2], [-4, 1], [-4, 0],
  [-3, -1], [-2, -2], [-1, -3], [0, -4], [1, -4], [2, -3], [3, -2], [4, -1],
  [5, 0], [4, 1], [3, 2], [2, 3], [1, 4], [0, 5], [-1, 5], [-2, 4], [-3, 3], [-4, 2], [-5, 1], [-5, 0],
  [-4, -1], [-3, -2], [-2, -3], [-1, -4], [0, -5], [1, -5], [2, -4], [3, -3], [4, -2], [5, -1],
  [6, 0], [5, 1], [4, 2], [3, 3], [2, 4], [1, 5], [0, 6], [-1, 6], [-2, 5], [-3, 4], [-4, 3], [-5, 2], [-6, 1], [-6, 0],
  [-5, -1], [-4, -2], [-3, -3], [-2, -4], [-1, -5], [0, -6], [1, -6], [2, -5], [3, -4], [4, -3], [5, -2], [6, -1],
  [7, 0], [6, 1], [5, 2], [4, 3], [3, 4], [2, 5], [1, 6], [0, 7], [-1, 7], [-2, 6], [-3, 5], [-4, 4], [-5, 3], [-6, 2], [-7, 1], [-7, 0],
  [-6, -1], [-5, -2], [-4, -3], [-3, -4], [-2, -5], [-1, -6], [0, -7], [1, -7], [2, -6], [3, -5], [4, -4], [5, -3], [6, -2], [7, -1],
  [8, 0], [7, 1], [6, 2], [5, 3], [4, 4], [3, 5], [2, 6], [1, 7], [0, 8], [-1, 8], [-2, 7], [-3, 6], [-4, 5], [-5, 4], [-6, 3], [-7, 2], [-8, 1], [-8, 0],
  [-7, -1], [-6, -2], [-5, -3], [-4, -4], [-3, -5], [-2, -6], [-1, -7], [0, -8]
];

		// Adjust group base position
		if (offsetX > maxRowWidth) {
			offsetX = 0;
			offsetY += spacing * 12	;
		}

		for (let i = 0; i < group.length; i++) {
			const $m = group[i];
			const [q, r] = axialOffsets[i] || [Math.floor(i / 5), (i % 5)];

			// Convert axial to pixel (flat-topped hex layout)
			//const x = spacing * (q + r / 2) + offsetX + originOffset;
			//const y = spacing * (r * Math.sqrt(3) / 2) + offsetY + originOffset;

			// Convert axial to pixel (pointy-topped hex layout)
			const x = spacing * Math.sqrt(3) * q + offsetX + originOffset;
			const y = spacing * 2 * (r + q / 2) + offsetY + originOffset;


			setTimeout(() => {
				$m.css({ left: `${x}px`, top: `${y}px` });
				$('.axialcoords', $m).text(axialOffsets[i] ? `(${axialOffsets[i][0]}, ${axialOffsets[i][1]})` : '(0, 0)');

				$(`.milestone-data input.left`, $m).val(x);	
				$(`.milestone-data input.top`, $m).val(y);



				updateMilestonePosition($m.data('id'));
			}, i * delayStep);
		}

		offsetX += spacing * 12;
		groupCount++;
	}
	setTimeout ( function (){
		$("#notify-message ul.content").html('');
	}, 30000); 

}




function applyTransformToMilestone(id){
	let zVal = ($(`#milestone-${id} .milestone-data .z-pos`).val());
	if(zVal > 5){
		scaleVal = 5;
	}else if(zVal < 1){
        scaleVal = 1;
    }
    let baseWidth = 108;
    let baseHeight = 95;
    let scaledWidth = baseWidth * zVal;
    let scaledHeight = baseHeight * zVal;

	$(`#milestone-${id} .milestone-content`).css({'width':`${scaledWidth}px`, 'height':`${scaledHeight}px`});
}
function milestoneReset(id){
	$(`#milestone-${id} .milestone-data .z-pos`).val(1);
	applyTransformToMilestone(id)
	updateMilestonePosition(id);
}
function zFront(id){
	let $zInput = $(`#milestone-${id} .milestone-data .z-pos`);
	
	if($zInput.val() < 5){
		$zInput.val(+$zInput.val() + 0.1);
	}
	applyTransformToMilestone(id)
	updateMilestonePosition(id);
}
function zBack(id){
	let $zInput = $(`#milestone-${id} .milestone-data .z-pos`);
	if($zInput.val() > 1){
		$zInput.val(+$zInput.val() - 0.1);
	}
	applyTransformToMilestone(id)
	updateMilestonePosition(id);
}

function loadQuestCard(quest_id=0){
	showLoader();
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl, 
		data: ({action: 'loadQuestCard', quest_id: quest_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			$('#flipped-card-container').html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#flipped-card-container").addClass('active'); 
				$("#flipped-card-container .card").addClass('flipped'); 
				hideAllOverlay();
			},10);
		}
	});
}

function loadAchievementCard(achievement_id=0){
	showLoader();
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadAchievementCard', achievement_id: achievement_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			$('#flipped-card-container').html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#flipped-card-container").addClass('active'); 
				$("#flipped-card-container .card").addClass('flipped');
				hideAllOverlay();
			},10);
		}
	});
}

function displayAchievementCard(achievement_id=0){
	showLoader('small');
	$("#achievements-display").removeClass('loaded').addClass('loading');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'displayAchievementCard', achievement_id: achievement_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			$('#achievement-card-xp .end-value').val(data.achievement.achievement_xp);
			$('#achievement-card-bloo .end-value').val(data.achievement.achievement_bloo);
			$('#achievement-card-ep .end-value').val(data.achievement.achievement_ep);
			$('#achievements-display .achievement-card-badge').attr('style','background-image:url('+data.achievement.achievement_badge+');');
			if(data.achievement.achievement_display == 'rank'){
				$('#achievements-display .achievement-card-badge').attr('onDblClick','switchRank('+data.achievement.achievement_id+');');
				$('#achievements-display').addClass('achievement-rank');
			}else{
				$('#achievements-display .achievement-card-badge').attr('onDblClick',false);
				$('#achievements-display').removeClass('achievement-rank');
			}
			$('#achievements-display .achievement-card-badge .decor-border path').removeClass().addClass(data.achievement.achievement_color);
			$('#achievements-display .achievement-card-title').text(data.achievement.achievement_name);
			$('#achievements-display .achievement-card-message').html(data.achievement.achievement_content);
			$('#achievements-display .achievement-card-earned').text(data.achievement.achievement_earned);
			
			if($('#achievement-card-actions')){
				$('#achievement-card-actions a.edit-link').attr('href',$('#achievement-card-'+data.achievement.achievement_id+' .achievement-data-link').val());
			}
			
			$('#achievement-card-'+data.achievement.achievement_id).addClass('active').siblings().removeClass('active');
			
			$("#achievements-display").addClass('loaded', function(){
				animateNumber('#achievement-card-xp, #achievement-card-bloo, #achievement-card-ep',750);
				hideAllOverlay();
			});
			
			$("#notify-message ul.content").append(data.message);
			$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
				$(this).remove();
				$("#achievements-display").removeClass('loading loaded');
			});
		}
	});
}

function isJson(str) {
	try {
		return JSON.parse(str);
	} catch (e) {
		return false;
	}
}
function randomEncounter(enc_id=0){
	$('#overlay-content .content').html('');
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'randomEncounter',  adventure_id: adventure_id, enc_id:enc_id}),
		method: "POST",
		success: function(data_received) {
			if(isJson(data_received)){
				displayAjaxResponse(data_received);
			}else{
				$('#overlay-content .content').html(data_received);
			}
			hideAllOverlay();
			let flipTimeout = setTimeout(function (){
				$("#overlay-content").addClass('active'); 
			},100);
		}
	});
}
function loadStory(){
	$('#overlay-content .content').html('');
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadStory',  adventure_id: adventure_id}),
		method: "POST",
		success: function(data_received) {
			if(isJson(data_received)){
				displayAjaxResponse(data_received);
			}else{
				$('#overlay-content .content').html(data_received);
			}
			hideAllOverlay();
			let flipTimeout = setTimeout(function (){
				$("#overlay-content").addClass('active'); 
			},100);
		}
	});
}


///////////////// Load Guild Card
function loadGuildCard(guild_id=0){
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadGuildCard', guild_id: guild_id}),
		method: "POST",
		success: function(data_received) {
			$('#flipped-card-container').html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#flipped-card-container").addClass('active'); 
				$("#flipped-card-container .card").addClass('flipped'); 
				hideAllOverlay();
			},10);
		}
	});
}

function previewItem(id){
	if(id == current_item_preview_id){
		current_item_preview_id = 0;
		$('#hud-video-status-idle').addClass('active');
		$('.hud-screen-content').removeClass('active');
	}else{
		current_item_preview_id = id;
		$('.hud-screen-video, .hud-screen-content').removeClass('active');
		$('.hud-screen-content').addClass('flicker');

		$('#item-preview-screen .item-preview-name').text($('#item-data-'+id+' .item-name').val());
		$('#item-preview-screen img.item-preview-image').attr('src',$('#item-data-'+id+' .item-image').val());
		$('#item-preview-screen .item-preview-description').html($('#item-data-'+id+' .item-description').html());
		$('#item-preview-buy-button').text($('#item-'+id+' button.buy-item').text())
		if($('#item-data-'+id+' .item-id').val() > 0){
			$('#item-preview-buy-button').attr('onClick','buyItem('+$('#item-data-'+id+' .item-id').val()+')');
		}else{
			$('#item-preview-buy-button').attr('onClick','');
		}
		$('#item-preview-screen .item-preview-type').removeClass('tabi-piece key consumable');
		$('#item-preview-screen .item-preview-type').text($('#item-data-'+id+' .item-type-label').val());
		$('#item-preview-screen .item-preview-type').addClass($('#item-data-'+id+' .item-type').val());
		setTimeout(function (){
			$('.hud-screen-content').removeClass('flicker').addClass('active');
		}, 500);
	}
}
function loadItemCard(item_id=0){
	if($('#item-'+item_id)){
		$('#item-'+item_id).siblings().removeClass("active");
		activate('#item-'+item_id);
	}
	let adventure_id = $('#the_adventure_id').val();

	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadItemCard', item_id: item_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			if(isJson(data_received)){
				displayAjaxResponse(data_received);
			}else{
				$('#flipped-card-container').html(data_received);
				let flipTimeout = setTimeout(function (){
					$("#flipped-card-container").addClass('active'); 
					$("#flipped-card-container .card").addClass('flipped'); 
					hideAllOverlay();
				},10);
			}
		}
	});
}

function loadBackpackItem(item_id=0){
	if($('#item-'+item_id)){
		$('#item-'+item_id).siblings().removeClass("active");
		activate('#item-'+item_id);
	}
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'loadBackpackItem', item_id: item_id}),
		method: "POST",
		success: function(data_received) {
			$('#flipped-card-container').html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#flipped-card-container").addClass('active'); 
				$("#flipped-card-container .card").addClass('flipped'); 
				hideAllOverlay();
			},10);
		}
	});
}

function loadLore(lore_id=0){
	if(lore_id > 0){
		showLoader();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'loadLore', lore_id: lore_id}),
			method: "POST",
			success: function(data_received) {
				$('#main-loader  .main-loader-content').html(data_received);
				let flipTimeout = setTimeout(function (){
					$("#main-container").addClass('opacity-60'); 
					$("#main-loader").addClass('active'); 
					hideAllOverlay();
				},10);
			}
		});
	}
}
function searchLore(){
	let search_string = $('#search').val();
	$("#lore-content").addClass('opacity-0');
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'searchLore', search_string: search_string, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			$('#lore-content').html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#lore-content").removeClass('opacity-0'); 
				hideAllOverlay();
			},10);
		}
	});
}

function unloadCard(){
	$('ul.cards li').removeClass("flipped");
	$("#flipped-card-container, #main-loader").removeClass('active'); 
	$("#flipped-card-container .card").removeClass('flipped');
	let clearFlipped = setTimeout(function(){$('#flipped-card-container, #main-loader .main-loader-content').html("")},300);
}
function clearMainLoader(){
	$("#main-container").removeClass('opacity-60'); 
	$("#main-loader").removeClass('active'); 
	let clearLoader = setTimeout(function(){$('#main-loader .main-loader-content').html("")},300);
}
function toggleSidebar(who){
    if(!who){
        $('.lateral-nav, .sidebar').removeClass('active');
    }else{
       $(who).toggleClass('active').siblings().removeClass('active'); 
    }
	
}
function loadSidebar(sidebar, filename, id){
	if(!sidebar){
		$('.sidebar').removeClass('active');
		$('.sidebar-asset').remove();
	}else{
		if($(sidebar).hasClass('active')){
			$(sidebar).removeClass('active');
		}else{
			animateScroll('#body');
			let adventure_id = $("#the_adventure_id").val();

			showLoader();
			jQuery.ajax({  
				url: runAJAX.ajaxurl,
				data: ({action: 'loadSidebar', filename:filename, adventure_id:adventure_id, id:id}),
				method: "POST",
				success: function(data_received) {
					$(sidebar).html(data_received);
					$(sidebar).addClass('active');
					hideAllOverlay();
				}
			});
		}
	}
}


function showOverlay(who){
	if(!$(who).hasClass('active')){
		$('.confirm-action, .stats-detail').removeClass('active');
		$(who).addClass('active');
		let offset_width = $(who).offset().left + $(who).outerWidth();
		let window_width = $(window).width();
		let total = offset_width - window_width;
		if(total > 0){
			let my_margin = -(total)+'px';
			$(who).css( { marginLeft : my_margin } );
		}
	}else{
		hideAllOverlay();	
	}
}
function setupAllOverlays(){
	let offset_width = $('.confirm-action').offset().left + $('.confirm-action').outerWidth();
	let window_width = $(window).width();
	let total = offset_width - window_width;
	if(total > 0){
		let my_margin = -(total)+'px';
		$('.confirm-action').css( { marginLeft : my_margin } );
	}
}

function hideAllOverlay(){
	$('.overlay-layer, #profile-box, .layer.overlay, .layer.feedback, .layer.top-overlay').removeClass('active');
	$('.confirm-action').removeClass('active');
	$("#main-content, #footer").removeClass('fixed'); 
	if($("#audio-funky").length){
		$("#audio-funky").prop('volume',0.1);
		$("#audio-funky").get(0).pause();
	}
	if($('#start').hasClass('active')){
		$('#start').removeClass('active');
		$('#start-button').removeClass('close');
		$('#taskbar').removeClass('start-active');
	}
}
function playSound(id){
	$(id).prop('volume',0.1);
	$(id).get(0).play();
}

function showLoader(type){
	hideAllOverlay();	
	if(type=='small'){
		$('#small-loader').addClass('active');
	}else{
		$('#loader').addClass('active');
		$('.overlay-bg').addClass('active');
	}
}

function toggleSetting(id){
	$(id+" .toggle-button").toggleClass('active');
	if($(id+" .toggle-button").hasClass('active')){
		$(id+" .setting-value").val(1);
	}else{
		$(id+" .setting-value").val(0);
	}
}
function allToggleButtonsOn(tab){
	$(tab+" .toggle-button").addClass('active');
	$(tab+" .setting-value.radio-setting-value").val(1);
}
function allToggleButtonsOff(tab){
	$(tab+" .toggle-button").removeClass('active');
	$(tab+" .setting-value.radio-setting-value").val(0);
}
function flipMilestone(id){
	if(id){
		$("#milestone-"+id).toggleClass("flipped").siblings().removeClass("flipped");
		let divOffsetTop = $("#milestone-"+id).offset().top-120;
		$("html, body").animate({ scrollTop: divOffsetTop }, 300);
	}
}
function flipLibraryCard(id){
	if(id){
		$(id).toggleClass("flipped").siblings().removeClass("flipped");
		let divOffsetTop = $(id+" .card-content").offset().top-120;
		$("html, body").animate({ scrollTop: divOffsetTop }, 300);
	}
}


/////////Download all images

function downloadAllImages(){
	showLoader(); 
    let urls_='';

    let cc=0;
	let url ='';
    $("img.downloadable").each(function(i,v){
		url=$(v).attr("src");
		
		if(cc==0)
		{
			urls_=url;	
		}else{
			urls_=urls_+"|"+url;
		}
		cc++;
    });
	let file_prefix=  $("#file_prefix").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'downloadAllImages', datos:urls_ , file_prefix:file_prefix}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	}); 
}


////////////////////////////////////////// FORMS FUNCTIONALITY ////////////////////////////////////////////

function setItemType(type){
	$("#the_item_type").val(type);
	$("button.item-type-choice, button.item-type-choice svg.icon-image").removeClass("active");
	$(`#button-${type}, #button-${type} svg.icon-image`).addClass("active");
	$('.form-ui.cond-opt').prop('disabled',true);
	$('.form-ui.cond-opt-'+type).prop('disabled',false);
}
function activateClass(class_on="", class_off=""){
	$(class_off).removeClass("active");
	$(class_on).addClass("active");
}
function countdown(){
	let time_left = $("#timer").html();
	let time_limit = $('#the_time_limit').val();
	let perc = Math.round(time_left/time_limit*100);
	$('#challenge-timer .progress').css('width',perc+'%');
	if(time_left > 0){
		time_left--;
		$("#timer").html(time_left);
		if(time_left <=30 && time_left >9){
			//$('#countdown-sfx').get(0).play();
			$('#challenge-timer .progress').addClass("warning");
			$('')
		}else if(time_left <=9){
			//$('#countdown-sfx').get(0).play();
			$('#challenge-timer .progress').removeClass('warning').addClass("danger");
		}
		setTimeout(countdown, 1000);
	}else{
		$("#times-up").fadeIn(1500);
		$('#challenge-timer .progress').removeClass('warning danger').addClass("dead");
		//$('#buzzer-sfx').get(0).play();
	}
}
function checkPath(){
	$('.conditional-display').hide();
	if($('#the_achievement_display').val()=='badge'){
		$('.badge-display').show();
	}else if($('#the_achievement_display').val()=='path'){
		$('.path-display').show();
		$("#the_achievement_xp, #the_achievement_bloo, #the_achievement_max").val('');
	}else if($('#the_achievement_display').val()=='rank'){
		$('.rank-display').show();
		$("#the_achievement_code, #the_achievement_xp, #the_achievement_bloo, #the_achievement_max, #magic-link").val('');
		$("#the_achievement_path").val(0);
	}
}

function objectiveCheck(obj_id, quest_id){
	let keyword = $("#keyword-input-"+objective_id).val();
}
function factCheck(objective_id){
	let keyword = $("#keyword-input-"+objective_id).val();
	if(keyword){
		jQuery.ajax({   
			url: runAJAX.ajaxurl,
			data: ({action: 'factCheck', objective_id:objective_id, keyword:keyword, quest_id:$('#the_quest_id').val(), adventure_id:$("#the_adventure_id").val()}),
			method: "POST",
			success: function(data_received) {
				let objective = JSON.parse(data_received);
				if(objective.no_energy == true){
					$("#feedback .content").html(objective.message);
					$("#feedback").addClass('active');
					$('.loader, .small-loader').removeClass('active');
					$("#feedback").click(function(){
						hideAllOverlay();
					});
				}else{
					$("#notify-message ul.content").append(objective.message);
					$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
					if(objective.success){
						completed_objectives ++;
						total_objectives--;
						createProgressionChart(completed_objectives, total_objectives,'#mission-status-chart');
						insertSolvedObjective(objective_id);
						$("#keyword-input-"+objective_id).removeClass('red-bg-400 white-color').addClass('lime-bg-500 blue-grey-900').attr('disabled',true);
						$("#feedback .content").html(objective.feedback);
						$("#feedback").addClass('active');
						let feedbackTimeout = setTimeout(function (){
							$("#feedback .content .objective-success-message").addClass('active');
						},500);
						
						$('.loader, .small-loader').removeClass('active');
						$("#feedback").click(function(){
							$("#feedback .content .objective-success-message").removeClass('active');
							hideAllOverlay();
						});
					}else{
						$("#keyword-input-"+objective_id).addClass('red-bg-400 white-color');
					}
					
				}
			}
		});
	}else{
		$("#keyword-input-"+objective_id).removeClass('red-bg-400 white-color lime-bg-500 blue-grey-900');
	}
}

function insertSolvedObjective(id){
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'insertSolvedObjective', id:id}),
		method: "POST",
		success: function(data_received) {
			if(data_received){
				$("#keyword-card-"+id).html(data_received);
			}else{
				alert('No file found!');
			}
		}
	});

}

function setCurrentSlide(id){
	let totalSlides = $('.slide').length;
	for(let i=0; i<id; i++){
		$(".slide-"+i).removeClass('active next').addClass('prev');
	}
	for(let i=id; i<=totalSlides; i++){
		$(".slide-"+i).removeClass('active prev').addClass('next');
	}
	$(".slide-"+id).removeClass('next prev').addClass('active');
}


function checkRequirements(level){
	if($("#the_quest_type").val() == "mission"){
		$("li.type-mission").hide();
		level = 99;
	}
	let i;
	for(i=0; i<=level;i++){
		$("li.level-"+i).show();
	}
	for(i>level; i<=100; i++){
		$("li.level-"+i).hide().removeClass("active");
	}
	$("#the_quest_xp").prop('disabled',false);
}

function spinUp(who,max=99){
	let number = $(who).val();
	if(number < max){
		number++;
		$(who).val(number);
	}
	checkRequirements(number);
}
function spinDown(who,min=1){
	let number = $(who).val();
	if(number > min){
		number--;
		$(who).val(number);
	}
	checkRequirements(number);
}
function checkLevel(who){
	let number = Number($(who).val());
	if(number > 99){
		$(who).val(99);
	}else if(number < 1){
		$(who).val(1);
	}
	checkRequirements(number);
}

function reorder(){
	let adventure_id = $("#the_adventure_id").val();
	let the_order = [];
	$("#table-quest .row-container .row .quest-id").each(function(){
		the_order.push($(this).val());
	});
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'reorder', adventure_id:adventure_id, the_order:the_order}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
function reorderItems(who){
	let adventure_id = $("#the_adventure_id").val();
	let the_order = [];
	$(who+ " tbody tr .item-id").each(function(){
		the_order.push($(this).val());
	});
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'reorderItems', adventure_id:adventure_id, the_order:the_order}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});

}
function reorderAchievements(who){
	let adventure_id = $("#the_adventure_id").val();
	let the_order = [];
	$(who+ " tbody tr .achievement-id").each(function(){
		the_order.push($(this).val());
	});
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'reorderAchievements', adventure_id:adventure_id,the_order:the_order}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});

}
function updateSchedule(){
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateSchedule'}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	}); 
}
function updatePlayer(adventure_id, player_id){
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updatePlayer', adventure_id:adventure_id, player_id:player_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	}); 
}

function activateReorder(who){
	$(who).addClass('sortable');
	$('.reorder-actions').removeClass('hidden');
	$('.default-actions').addClass('hidden');
	$(".sortable").sortable({
		update: function(event, ui) {
		
		}
	});
	$(".sortable").disableSelection();
	
}
function deactivateReorder(who){
	$(who).sortable("destroy").removeClass('sortable');
	$(who+" li").removeClass('ui-state-default');
	$('.default-actions').removeClass('hidden');
	$('.reorder-actions').addClass('hidden');
}
function activateMilestone(id=null, sound_on=null, sound_off=null){
	if(id){
		let mi = $('#milestone-'+id);
		let miContainer = $('#milestone-container-'+id);
		if(mi.hasClass('active')){
			if(sound_off){	playSound(sound_off); }
			$('#the-journey').removeClass('milestone-on');
		}else{
			if(sound_on){	playSound(sound_on); }
			$('#the-journey').addClass('milestone-on');
		}
		
		$(`#the-journey .milestone:not(#milestone-${id})`).removeClass('active');
		$(`#the-journey .milestone-container:not(#milestone-container-${id})`).removeClass('baseZ');
		mi.toggleClass('active');
		
		if(mi.hasClass('active')){
			$('#milestone-preview').attr({'class':'milestone-preview'});
			$('#milestone-preview-bg').attr({'class':'milestone-preview-bg'});
			$('#the-journey .milestone').addClass('inactive');
			mi.removeClass('inactive');

			miContainer.addClass('baseZ');
			
			/// FILL PREVIEW
			
			let preview_data = {
				'badge': $(`#milestone-${id} .milestone-data-bg`).val(),
				'title': $(`#milestone-${id} .milestone-data-title`).val(),
				'xp': $(`#milestone-${id} .milestone-data-xp`).val(),
				'bloo': $(`#milestone-${id} .milestone-data-bloo`).val(),
				'ep': $(`#milestone-${id} .milestone-data-ep`).val(),
				'level': $(`#milestone-${id} .milestone-data-level`).val(),
				'color': $(`#milestone-${id} .milestone-data-color`).val(),
				'type': $(`#milestone-${id} .milestone-data-type`).val(),
			}
			$('#milestone-preview-bg').attr('style','background-image:url('+preview_data.badge+');').addClass(preview_data.color+'-bg-400');
			$('#milestone-preview').addClass('active '+preview_data.type);
			$('#milestone-preview-bg').addClass('active');
			$('#milestone-preview .milestone-preview-content').html($('#milestone-'+id+' .milestone-cta').html());
			$('#milestone-preview .milestone-preview-xp .end-value').val(preview_data.xp);
			$('#milestone-preview .milestone-preview-ep .end-value').val(preview_data.ep);
			$('#milestone-preview .milestone-preview-bloo .end-value').val(preview_data.bloo);
			animateNumber('#milestone-preview-xp',750);
			animateNumber('#milestone-preview-bloo',750);
			if($('#milestone-preview-ep')){
				animateNumber('#milestone-preview-ep',750);
			}
			
		}else{
			$('#the-journey .milestone').removeClass('inactive');
			mi.removeClass('inactive');
			miContainer.removeClass('baseZ');
			$('#milestone-preview-bg').attr({'style':'background-image:url();','class':'milestone-preview-bg'});
			$('#milestone-preview').attr({'class':'milestone-preview'});
			$('#milestone-preview .milestone-preview-content').html('');
			$('#milestone-preview .milestone-preview-xp .end-value').val(0);
			$('#milestone-preview .milestone-preview-ep .end-value').val(0);
			$('#milestone-preview .milestone-preview-bloo .end-value').val(0);
			/// EMPTY PREVIEW
		}
		// if(scroll){
		// 	animateScroll(mi,1, 35);
		// }
		
	}else{
		$('#the-journey .milestone').removeClass('inactive active');
		$('#milestone-preview-bg').attr({'style':'background-image:url();','class':'milestone-preview-bg'});
		$('#milestone-preview').attr({'class':'milestone-preview'});
		$('#milestone-preview .milestone-preview-content').html('');
		$('#milestone-preview .milestone-preview-xp .end-value').val(0);
		$('#milestone-preview .milestone-preview-ep .end-value').val(0);
		$('#milestone-preview .milestone-preview-bloo .end-value').val(0);
		if($('#the-journey').hasClass('milestone-on')){
			playSound('#ui-touch-milestone-reverse');
			$('#the-journey').removeClass('milestone-on');
		}
	}
}


function playBGVideo(who=null){
	if(who){
		if ($(who).get(0).paused) {
			$(who).get(0).play();
		} else {
			$(who).get(0).pause();
		}	
	}else{
		if ($('#main-background-video').get(0).paused) {
			$('#main-background-video').get(0).play();
		} else {
			$('#main-background-video').get(0).pause();
		}	
	}
}
function activateStartMenu(){
	$('#start').toggleClass('active');
	if($('#start').hasClass('active')){
		$('#start-button').addClass('close');
		$('#taskbar').addClass('start-active');
	}else{
		$('#start-button').removeClass('close');
		$('#taskbar').removeClass('start-active');
	}
}
function activate(who, scroll=null, solo=null){
	if(solo){
		$(who).toggleClass('active');
	}else{
		$(who).siblings().removeClass('active');
		$(who).toggleClass('active');
		if($(who).hasClass('active')){
			$(who).siblings().addClass('inactive');
			$(who).removeClass('inactive');
		}else{
			$(who).siblings().removeClass('inactive');
			$(who).removeClass('inactive');
		}
	}
	if(scroll){
		animateScroll(who,1, 35);
	}
}


function reorderQuestions(who){
	let the_order = [];
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	$(who+ " li.question input.question-id-value").each(function(){
		the_order.push($(this).val());
	});
	showLoader(); 
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'reorderQuestions', the_order:the_order}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});

}

function toggleAllGrades(id){
	$(".cell").removeClass('show-grade-col show-grade-row');
	$(".cell").toggleClass('show-grade');
}
function toggleColGrades(id){
	$(".cell").removeClass('show-grade');
	$(".column-"+id).toggleClass('show-grade-col');
}
function toggleRowGrades(id){
	$(".cell").removeClass('show-grade');
	$(".row-"+id).toggleClass('show-grade-row');
}


///////////////////////// Survey QUESTIONS //////////////////////////
function clearImage(id,updateType,q_id){
	if($(id).is('img')){
		$(id).fadeOut('fast',function(){
			$(id).attr('src','').parent().removeClass('full').addClass('empty');
			$(id).fadeIn(300);
			if(updateType && q_id){
				updateQuestion(updateType,q_id);
			}
		});
	}else{
		$(id).val(0);
		$(id+"_thumb").css("background-image",""); 
		$(id+"_thumb_video source").removeAttr('src');
		$(id+"_thumb_video").removeClass('active');
		$(id+"_thumb_video")[0].load();
		
	}
}


////////////////////////////// newUniqueAchievementCode ///////////////////////////
function newUniqueAchievementCode(achievement_id){
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'newUniqueAchievementCode', achievement_id:achievement_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
////////////////////////////// newUniqueAchievementCode ///////////////////////////
function deleteAchievementCode(code_id){
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'deleteAchievementCode', code_id:code_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
////////////////////////////// ADD QUESTIONS ///////////////////////////
function addQuestion(type, style){
	let id = $('#the_'+type+'_id').val();
	let adventure_id = $("#the_adventure_id").val();
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'addQuestion', type:type, id:id, style:style,adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			if(data_received){
				$('.questions').append(data_received);
				data_received = '';
				animateScroll('#questions-bottom',1,-300);
				$('#small-loader').removeClass('active');
			}else{
				alert('No file found!');
			}
			hideAllOverlay();
		}
	});
}
function duplicateQuestion(q_id, type){
	let main_id = $('#the_'+type+'_id').val();
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'duplicateQuestion',type:type, q_id:q_id, main_id:main_id,adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			if(data_received){
				$('#questions').append(data_received);
				data_received = '';
			}else{
				alert('No file found!');
			}
			hideAllOverlay();
		}
	});
}

function updateQuestion(type,id){
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	let q_text = $("#question-text-"+id).val();
	let q_image = $("#question-"+id+"-img").attr('src');
	let q_description=$("#question-description-"+id).val();
	let q_range=$("#question-range-"+id).val();
	let q_display=$("#question-display-"+id).val();
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateQuestion', type:type, id:id, q_text:q_text, q_image:q_image,q_description:q_description, q_range:q_range, q_display:q_display,adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
function removeQuestion(id, type){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	let nonce = $('#delete-question-nonce').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'removeQuestion', id:id, nonce:nonce, type:type, adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			if(data.success){
				if(data.just_notify){
					$("#notify-message .content").append(data.message);
					$("#notify-message").show();
					$("#notify-message").delay(1000).fadeOut(300, function(){
						$("#notify-message .content").html('');
					});
				}
				$("#question-"+id).fadeOut('fast',function(){
					if($('#accordion-tab-question-'+id)){
						$('#accordion-tab-question-'+id).remove();
					}
					$("#question-"+id).remove();
				}); 
				hideAllOverlay();
			}
		}
	});
}

function addOption(type,q_id){
	let main_id = $('#the_'+type+'_id').val();
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'addOption', type:type, q_id:q_id, main_id:main_id, adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			if(data_received){
				$("#question-"+q_id+' .question-options').append(data_received);
				data_received = '';
			}else{
				alert('No file found!');
			}
			hideAllOverlay();
		}
	});
}
//////////////////////////// UPDATE OPTION ON CHANGE
function updateOption(type,q_id,option_id){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	let main_id = $('#the_'+type+'_id').val();
	let o_text = $("#option-text-"+option_id).val();
	let o_image = $("#option-image-"+option_id).val();
	let o_correct = $("#option-"+option_id+" .option-correct").val();
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateOption', type:type, q_id:q_id, main_id:main_id, option_id:option_id, o_text:o_text, o_image:o_image, o_correct:o_correct, adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function removeOption(id, type){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	let nonce = $('#delete-option-nonce').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'removeOption', id:id, nonce:nonce, type:type, adventure_id:adventure_id,quest_id:quest_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			if(data.success){
				if(data.just_notify){
					$("#notify-message .content").append(data.message);
					$("#notify-message").show();
					$("#notify-message").delay(1000).fadeOut(300, function(){
						$("#notify-message .content").html('');
					});
				}
				$("#option-"+id).fadeOut('fast',function(){
					$("#option-"+id).remove();
				}); 
				hideAllOverlay();
			}
		}
	});
}
function updateQuestionValue(who,value){
	$('#question-answer-value-'+who).val(value);
	if(value === 0){
		$('#question-'+who+' .star').removeClass('active');
		$('#question-'+who+' .star-0').addClass('active');
	}else{
		$('#question-'+who+' .star').removeClass('active');
		for(let i=1; i<=value;i++){
			$('#question-'+who+' .star-'+i).addClass('active');
		}
	}
	submitSurveyAnswer(who);
}
function prepareMultiChoiceValue(who, opt_toggle){
	
	$(opt_toggle).toggleClass('active');
	
	let answer_values =[];
	$('#question-'+who+' .option.active').each(function(index, element) {
		answer_values.push($('.option-value',this).val());
	});
	
	$('#question-answer-value-'+who).val(answer_values);
	submitSurveyAnswer(who);
}
function fakeSubmit(){
	let adventure_id = $("#the_adventure_id").val();
	let url = $("#bloginfo_url").val();
	$("#feedback .content").html("<h1>Answers submitted!</h1>");
	$("#feedback").addClass('active');
	if(!$('.overlay-bg').is(':visible')){
		$('.overlay-bg').fadeIn('fast');
	}
	$("#feedback").click(function(){
		document.location.href=url+"/adventure/?adventure_id="+adventure_id;
	});
}
function submitSurveyAnswer(question_id,option_id=0, style=""){
	let survey_id = $('#the_survey_id').val();
	let adventure_id = $('#the_adventure_id').val();
	let value = $('#question-answer-value-'+question_id).val();
	showLoader("small");
	let send_answer = false;
	if(option_id > 0){
		if($("#option-"+style+option_id).hasClass('active')){
			send_answer = false;
		}else{
			send_answer = true;
		}
	}else{
		send_answer = true;
	}
	if(send_answer){
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({
				action:'submitSurveyAnswer', 
				question_id:question_id,
				option_id:option_id,
				survey_id:survey_id,
				value:value,
				adventure_id:adventure_id
			}),
			method: "POST",
			success: function(json_text) {
				displayAjaxResponse(json_text);
				$("#question-"+question_id).addClass('answered');
				if(option_id){
					$("#option-"+style+option_id).siblings().removeClass('active');
					$("#option-"+style+option_id).addClass('active');
				}
			}
		});
	}else{
		hideAllOverlay();
	}
}


////////////////////////////// ADD STEP ///////////////////////////
function addStep(id_to_duplicate=null){
	let quest_id = $('#the_quest_id').val();
	let adventure_id = $("#the_adventure_id").val();
    if(quest_id){
       showLoader("small");
       jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'addStep', quest_id:quest_id, adventure_id:adventure_id,id_to_duplicate:id_to_duplicate}),
            method: "POST",
            success: function(data_received) {
				if(isJson(data_received)){
					displayAjaxResponse(data_received);
				}else{
                    $('tbody#steps-list').append(data_received);
					let new_step_id = $('tbody#steps-list tr:last-child input.the_step_id_val').val();
					editStep(new_step_id);
                    data_received = '';
                }
				if(id_to_duplicate){
					reorderSteps();
				}
                hideAllOverlay();
            }
        });
    }else{
        $("#notify-message ul.content").append($('#msg-save-first').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
            $(this).remove();
            hideAllOverlay();
        });
    }
}
////////////////////////////// LOAD STEP ///////////////////////////
function editStep(step_id){
	animateScroll('#body');
	$("#overlay-content .content").html('');
	let adventure_id = $("#the_adventure_id").val();

	if(step_id){
		showLoader("small");
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'editStep', step_id:step_id, adventure_id:adventure_id}),
			method: "POST",
			success: function(data_received) {
				$("#overlay-content .content").html(data_received);
				$("#overlay-content").addClass('active');
				$('.loader, .small-loader').removeClass('active');
			}
		});
	}else{
		$("#notify-message ul.content").append($('#msg-no-id').html());
		$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
			$(this).remove();
			hideAllOverlay();
		});
	}
}

////////////////////////////// UPDATE STEP ///////////////////////////
function updateStep(){
	let step_id = $("#step-id").val();
	let adventure_id = $("#the_adventure_id").val();
    if(step_id){
		let step_type = $('#step-type-'+step_id).val();
		if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
			tinyMCE.triggerSave(); 
		}
		let step_content = $('#step-content-'+step_id).val();
		let step_title = $('#step-title-'+step_id).val();
		let step_attach = $('#step-attach-'+step_id).val();
		let step_image = $('#the_step_image_'+step_id).val();
		let step_character_name = $('#step-character-name-'+step_id).val();
		let step_character_image = $('#the_step_character_image').val();
		let step_background = $('#the_step_background').val();
		let step_achievement_group = $('#the_step_achievement_group').val();
		let step_item = 0;
		if(step_type == 'item-grab' || step_type == 'item-req'){
			step_item = $('#step-select-items-'+step_id+' .step-item.active input.item-id').val();
		}
		if(step_type == 'path-choice' && !step_achievement_group){
			notification('#msg-no-path-choice', 2000);
		}else if((step_type == 'item-grab' || step_type == 'item-req') && !step_item){
			notification('#msg-no-step-item', 2000);
		}else{
			showLoader("small");
			jQuery.ajax({  
				url: runAJAX.ajaxurl,
				data: ({
					action: 'updateStep', 
					step_id:step_id, 
					adventure_id:adventure_id, 
					step_title:step_title, 
					step_type:step_type, 
					step_image:step_image, 
					step_attach:step_attach, 
					step_content:step_content, 
					step_character_name:step_character_name,
					step_character_image:step_character_image,
					step_background:step_background,
					step_achievement_group:step_achievement_group,
					step_character_name:step_character_name,
					step_item:step_item,
				}),
				method: "POST",
				success: function(json_text) {
					displayAjaxResponse(json_text);
					let content = JSON.parse(json_text);
					
					if(step_type == 'path-choice'){
						$("tbody#steps-list #step-"+content.updated_step.step_id+" .step-title").text(content.updated_step.step_title+" [Group: "+content.updated_step.step_achievement_group+"]");
					}else{
						$("tbody#steps-list #step-"+content.updated_step.step_id+" .step-title").text(content.updated_step.step_title);
					}
					$("tbody#steps-list #step-"+content.updated_step.step_id+" .step-type").text(content.updated_step.step_type);
					
					if(content.updated_step.step_type == 'dialogue'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step blue-bg-50');
					}else if(content.updated_step.step_type == 'jump'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step indigo-bg-50');
					}else if(content.updated_step.step_type == 'system'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step orange-bg-50');
					}else if(content.updated_step.step_type == 'win'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step light-green-bg-100 white-color');
					}else if(content.updated_step.step_type == 'fail'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step red-bg-100 white-color');
					}else if(content.updated_step.step_type == 'item-req' || content.updated_step.step_type == 'item-grab'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step pink-bg-50');
					}else if(content.updated_step.step_type == 'path-choice'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step purple-bg-50');
					}else if(content.updated_step.step_type == 'choose-avatar' || content.updated_step.step_type == 'choose-nickname'){
						$("tbody#steps-list #step-"+content.updated_step.step_id).removeClass().addClass('step indigo-bg-50');
					}
					$("#overlay-content .content").html('');
				}
			});
			
		}
		
    }else{
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
            $(this).remove();
            hideAllOverlay();
        });
    }
}
////////////////////////////// New Step List Item ///////////////////////////
function removeStep(step_id){
    if(step_id){
        showLoader("small");
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'removeStep', step_id:step_id}),
            method: "POST",
            success: function(data_received) {
				displayAjaxResponse(data_received);
            }
        });
    }else{
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
            $(this).remove();
            hideAllOverlay();
        });
    }
}

////////////////// REORDER STEPS
function reorderSteps(){
	let adventure_id = $("#the_adventure_id").val();
	let quest_id = $("#the_quest_id").val();
	let the_order = [];
	$('tbody#steps-list tr.step').each(function(){
		the_order.push($('input.the_step_id_val',this).val());
	});
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'reorderSteps', adventure_id:adventure_id,  quest_id:quest_id, the_order:the_order}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


////////////////////////////// NEW STEP BUTTON ///////////////////////////
function addStepButton(){
	let step_id = $('#step-id').val();
	let quest_id = $('#the_quest_id').val();
	let adventure_id = $("#the_adventure_id").val();
	let step_type = $('#step-type-'+step_id).val();
    if(step_id){
        $('.small-loader').addClass('active');
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'addStepButton', step_id:step_id, step_type:step_type, quest_id:quest_id, adventure_id:adventure_id}),
            method: "POST",
            success: function(data_received) {
				if(isJson(data_received)){
					displayAjaxResponse(data_received);
				}else{
					$("#notify-message ul.content").append($('#msg-new-button-added').html());
					$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
					$('#step-buttons-list').append(data_received);
					$('.small-loader').removeClass('active');
                }
            }
        });
    }
}
function removeStepButton(button_id=0){
    if(removeStepButton){
        $('.small-loader').addClass('active');
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'removeStepButton', button_id:button_id, }),
            method: "POST",
            success: function(json_text) {
				displayAjaxResponse(json_text);
            }
        });
    }
}


////////////////////////////// UPDATE Button ///////////////////////////
function updateStepButton(btn_id){
	let step_id = $("input#step-id").val();
	let adventure_id = $("#the_adventure_id").val();
    if(btn_id){
		let button_text = $("#step-button-"+btn_id+" input.button_text").val();
		let button_step_next = $("#step-button-"+btn_id+" select.button_step_next").val();
		let button_image = $("#the_step_button_image-"+btn_id).val();
        $('.small-loader').addClass('active');
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'updateStepButton', step_id:step_id, adventure_id:adventure_id, button_text:button_text, button_step_next:button_step_next, btn_id:btn_id, button_image:button_image}),
            method: "POST",
            success: function(json_text) {
				displayAjaxResponse(json_text);
            }
        });
    }else{
        $("#notify-message ul.content").append($('#msg-no-id').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
            $(this).remove();
            hideAllOverlay();
        });
    }
}

function checkStepType(){
	$('#step-buttons-form-container').html('');
	$('.conditional-display').hide();
	let s_id = $('#step-id').val();
	let step_type = $('#step-type-'+s_id).val();
	let type_display = '.'+step_type+'-display';
	$(type_display).show();
	if($('#step-type-'+s_id).val()!='path-choice'){
		$('#the_step_achievement_group').val('');
	}
	jQuery.ajax({   
		url: runAJAX.ajaxurl,
		data: ({action: 'loadStepButtonForm', button_form:$('#step-type-'+s_id).val(), step_id:$('#step-id').val()}),
		method: "POST",
		success: function(data) {
			if(data){
				$('#step-buttons-form-container').html(data);
			}
		}
	});
}




////////////////////////////// ADD Objectives ///////////////////////////
function addObjective(objective_type){
	let id = $('#the_quest_id').val();
	let adventure_id = $("#the_adventure_id").val();
    if(id){
        showLoader("small");
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'addObjective', id:id, objective_type:objective_type, adventure_id:adventure_id}),
            method: "POST",
            success: function(data_received) {
                if(data_received){
                    $('table#objectives').append(data_received);
					let new_objective_id = $('table#objectives tr:last-child td.objective-id').text();
					editObjective(new_objective_id);
                    data_received = '';
					
                }else{
                    alert('No file found!');
                }
                hideAllOverlay();
            }
        });
    }else{
        $("#notify-message ul.content").append($('#msg-save-first').html());
        $("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
            $(this).remove();
            hideAllOverlay();
        });
    }
}

//////////////////////////// UPDATE OBJECTIVE ON CHANGE
function resetQuestObjectives(quest_id){
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetQuestObjectives', quest_id:quest_id }),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
//////////////////////////// UPDATE OBJECTIVE ON CHANGE
function updateObjective(objective_id){
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
    let objective_data = {
		objective_content : $('#objective_content_'+objective_id).val(),
		objective_success_message : $('#objective_success_message_'+objective_id).val(),
		objective_keyword : $('#objective-form-'+objective_id+" .objective-keyword").val(),
		objective_ep_cost : $('#objective-form-'+objective_id+" .objective-ep-cost").val(),
    };
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateObjective', objective_id:objective_id, objective_data:objective_data }),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
			let objective_data = JSON.parse(data_received);
			if(objective_data.success){
				$("#objective-row-"+objective_id+" .objective-row-keyword").text(objective_data.objective.objective_keyword);
				$("#objective-row-"+objective_id+" .objective-row-ep-cost").text(objective_data.objective.ep_cost);
				$("#objective-row-"+objective_id+" .objective-hint").html(objective_data.objective.objective_content);
			}
		}
	});
}


//////////////////////////// EDIT objective
function editObjective(objective_id){
	animateScroll('#body');
	$("#overlay-content .content").html('');
	let adventure_id = $("#the_adventure_id").val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'editObjective', adventure_id:adventure_id, objective_id:objective_id}),
		method: "POST",
		success: function(data_received) {
			$("#overlay-content .content").html(data_received);
			$("#overlay-content").addClass('active');
			$('.loader, .small-loader').removeClass('active');
		}
	});
}

//////////////////////////// REMOVE objective

function removeObjective(objective_id){
	showLoader("small");
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'removeObjective', objective_id:objective_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
 			if(data.success){
                $("#notify-message .content").append(data.message);
                $("#notify-message").show();
                $("#notify-message").delay(1000).fadeOut(300, function(){
                    $("#notify-message .content").html('');
                });
				$("#objective-"+objective_id).fadeOut('fast',function(){
					$("#objective-"+objective_id).remove();
				});
				hideAllOverlay();
			}
		}
	});
}


function filterAdminTable(type, element){
	$('.filter li').removeClass('active');
	if(type!='all'){
		$(element).hide();
		$(element+"."+type).show();
		$('.filter li#filter-'+type).addClass('active');
	}else{
		$(element).show();
		$('.filter li#filter-all').addClass('active');
	}
}




////////////////////////////////////////// NEW HEXAD ////////////////////////////////////////////

function newHexad(){ 
	let nonce = $("#nonce-hexad").val();
	$('#new-hexad-button').attr('disabled',true);
	let type_d = 0;
	let type_f = 0;
	let type_a = 0;
	let type_p = 0;
	let type_s = 0;
	let type_ph = 0;
	
	$('select.type-d').each(function(index, element) {
		type_d += Number($(this).val());
	});
	$('select.type-f').each(function(index, element) {
		type_f += Number($(this).val());
	});
	$('select.type-a').each(function(index, element) {
		type_a += Number($(this).val());
	});
	$('select.type-p').each(function(index, element) {
		type_p += Number($(this).val());
	});
	$('select.type-s').each(function(index, element) {
		type_s += Number($(this).val());
	});
	$('select.type-ph').each(function(index, element) {
		type_ph += Number($(this).val());
	});
	let answers = {
		type_d:type_d,
		type_f:type_f,
		type_a:type_a,
		type_p:type_p,
		type_s:type_s,
		type_ph:type_ph,
	};
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'newHexad', answers:answers,nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


//////////////////  SWITCH RANKS ////////////////
function switchRank(achievement_id){
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'switchRank', achievement_id:achievement_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

//////////////////  SWITCH TABS ////////////////
function switchTabs(tab_group, tab){
	$(tab_group+" > .tab, "+tab_group+"-buttons .tab-button").removeClass('active');
	$(tab+" ,"+tab+"-tab-button").addClass('active');
}

//////////////////  SET RATING  ////////////////
function setRating(id,rating){
	let nonce = $("#rating_nonce").val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setRating', id:id, rating:rating, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////////// //////////// createChart //////////////////////////////////////////
function createHexadChart(v_d,v_f,v_a,v_p,v_s,v_ph, chart_name){
	let ctx = document.getElementById(chart_name);
	let myChart = new Chart(ctx, {
		type: 'polarArea',
		data: {
			labels: [  'Achiever', 'Player', 'Socialiser','Philanthropist','Disruptor','Free Spirit'],
			datasets: [
				{
					data: [v_a,v_p,v_s,v_ph,v_d,v_f],
				backgroundColor: [
					'rgba(33,150,243,0.7)',
					'rgba(103,58,183,0.7)',
					'rgba(255,193,7,0.7)',
					'rgba(0,150,136,0.7)',
					'rgba(244,67,54,0.7)',
					'rgba(233,30,99,0.7)',
					
				],
				borderColor: [
					'rgba(33,150,243,1)',
					'rgba(103,58,183,1)',
					'rgba(255,193,7,1)',
					'rgba(0,150,136,1)',
					'rgba(244,67,54,1)',
					'rgba(233,30,99,1)',
				],
					borderWidth: 1
				}
			]
		},
		options: {
			startAngle:0,
			animation:{
				animateRotate: false,
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero:true
					}
				}]
			}
		}
	});
}
function createProgressionChart(current_val,total_val,who){
	let ctx = $(who);
	let myChart = new Chart(ctx, {
		type: 'doughnut',
		data : {
			datasets: [{
				data: [total_val, current_val],
				backgroundColor: [
					$('#color-total-value').val(),
					$('#color-current-value').val(),
				],
				borderWidth: 0,
			}],
			labels: [
				$('#label-total-value').val(),
				$('#label-current-value').val(),
			]
		},
		options: {
			cutoutPercentage:10,
			legend:{
				display:0,
			}
		},
	});
}
function createReportChart(who, the_values, the_labels, the_colors){
	let ctx = $(who);
	let myChart = new Chart(ctx, {
		type: 'doughnut',
		data : {
			datasets: [{
				data: the_values,
				backgroundColor: the_colors,
				borderColor: [
					'rgba(255,255,255,0)'
				],
				borderWidth: 1,
			}],
			labels: the_labels
		},
		options: {
			cutoutPercentage:50,
			rotation: 1 * Math.PI,
			circumference: 1 * Math.PI,
			legend:{
				display:0,
			}
		},
	});
}



//////////////////  Update Profile  ////////////////

function randomPlayerData(){
	let names = [ "Eugenio", "Bo", "Prince", "Elmer", "Ahmad", "Clair", "Rudolph", "Tanner", "Del", "Paris", "Rogelio", "Vincent", "Milo", "Denis", "Shelby", "Wilburn", "Cesar", "Alton", "Caleb", "Lorenzo", "Signe", "Tandra", "Albertine", "Vivienne", "Clarinda", "Shemika", "Jeanette", "Jenise", "Jeanett", "Lani", "Rena", "Vella", "Tillie", "Davida", "Tatum", "Martha", "Tena", "Gianna", "Macy", "Shenna"];
	
	let lastnames =[ "Small", "Fuentes", "Watson", "Rose", "Watkins", "Morrison", "Fox", "Bautista", "Diaz", "George", "Williams", "Pena", "Larson", "Ho", "Cuevas", "Huynh", "Stuart", "Miles", "Juarez", "Raymond", "Cabrera", "Barr", "Riddle", "Hall", "Travis", "Cantrell", "Ferrell", "Salinas", "Mercer", "Edwards", "Potter", "Crosby", "Moses", "Richards", "Riley", "Payne", "Rosales", "Barker", "Grant", "Vasquez"];
	
	let newName = names[Math.floor(Math.random() * names.length)];
	let newLast = lastnames[Math.floor(Math.random() * names.length)];
	
	$('#the_first_name').val(newName);
	$('#the_last_name').val(newLast);
	$('#the_email').val('noEmail'+(Math.random()*10000)+'@notin.bluerabbit.io');
	
	$('#the_player_picture').val('');
}

function addTabi(){
	showLoader("small");
	let adventure_id = $("#the_adventure_parent_id").val();
	let nonce = $('#add-tabi-nonce').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'addTabi', adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			let my_tabi_data = JSON.parse(data_received);
			if(my_tabi_data.new_tabi_id){
				insertTabiRow(my_tabi_data.new_tabi_id);
			}
			displayAjaxResponse(data_received);
			// $("#notify-message ul.content").append(data.message);
		}
	});
}
function insertTabiRow(tabi_id){
    if(tabi_id){
        showLoader("small");
        jQuery.ajax({  
            url: runAJAX.ajaxurl,
            data: ({action: 'insertTabiRow', tabi_id:tabi_id}),
            method: "POST",
            success: function(data_received) {
                if(data_received){
                    $('#table-tabis').append(data_received);
					notification('#msg-new-tabi-row');

                }else{
                   	notification('#msg-error');
                }
                hideAllOverlay();
            }
        });
    }else{
		notification('#msg-error');
    }
}



function updateProfile(){
	showLoader("small");
	let nonce = $('#profile_nonce').val();
	let player_data = {
		first_name: $('#the_first_name').val(),
		last_name: $('#the_last_name').val(),
		email: $('#the_email').val(),
		lang: $('#the_lang').val(),
		profile_picture : $('#the_player_picture').val(),
	}
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateProfile', player_data:player_data, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
function setNickname(id){
	showLoader("small");
	let nonce = $('#profile_nonce').val();
	let	nickname = $('#the_player_nickname_'+id).val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setNickname', nickname:nickname, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
function setProfilePicture(id){
	showLoader("small");
	let nonce = $('#profile_nonce').val();
	let	player_picture = $('#the_player_picture_'+id).val();
	$(".avatar-button").removeClass('active').attr('disabled',false);
	$("#avatar-button-"+id).attr('disabled',true).addClass('active');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setProfilePicture', player_picture:player_picture, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
//////////////////  Update Speaker  ////////////////

function updateSpeaker(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#speaker_nonce').val();
	let speaker_data = {
		id: $('#the_speaker_id').val(),
		adventure_id: $('#the_adventure_id').val(),
		first_name: $('#the_speaker_first_name').val(),
		last_name: $('#the_speaker_last_name').val(),
		bio :$('#the_speaker_bio').val(),
		picture :$('#the_speaker_picture').val(),
		company :$('#the_speaker_company').val(),
		website :$('#the_speaker_website').val(),
		twitter :$('#the_speaker_twitter').val(),
		linkedin :$('#the_speaker_linkedin').val(),
	}
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateSpeaker', speaker_data:speaker_data, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
//////////////////  Set Speaker DATA  ////////////////

function setSpeakerData(id){
	showLoader('small');
	let nonce = $('#set-speaker-nonce').val();
	let speaker_data = {
		id: $('#speaker-'+id+'-id').val(),
		adventure_id: $('#the_adventure_id').val(),
		first_name: $('#speaker-'+id+'-first-name').val(),
		last_name: $('#speaker-'+id+'-last-name').val(),
		company :$('#speaker-'+id+'-company').val(),
		website :$('#speaker-'+id+'-website').val(),
		twitter :$('#speaker-'+id+'-twitter').val(),
		linkedin :$('#speaker-'+id+'-linkedin').val(),
	}
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setSpeakerData', speaker_data:speaker_data, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

//////////////////  Update Session  ////////////////

function updateSession(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#session_nonce').val();
	
	let session_data = {
		id: $('#the_session_id').val(),
		adventure_id: $('#the_adventure_id').val(),
		title: $('#the_session_title').val(),
		room: $('#the_session_room').val(),
		start: $('#the_session_start').val(),
		end :$('#the_session_end').val(),
		quest_id :$('#the_quest_id').val(),
		speaker_id :$('#the_speaker_id').val(),
		status :$('#the_session_status').val(),
		description :$('#the_session_description').val(),
		achievement_id :$('#the_achievement_id').val(),
		guild_id :$('#the_guild_id').val(),
	}
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateSession', session_data:session_data, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

//////////////////  updatePrevLevel  ////////////////

function updatePrevLevel(level, adventure_id){
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updatePrevLevel', adventure_id:adventure_id, level:level}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			displayAjaxResponse(data_received);
		}
	});
}


//////////////////  CLOSE INTRO  ////////////////

function closeIntro(){
	showLoader();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'closeIntro', adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			if(data.success){
				document.location.href = data.adventure_home_url;
			}
		}
	});
}
//////////////////  RESET INTRO  ////////////////

function resetIntro(){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetIntro', adventure_id:adventure_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

//////////////////  RESET PREV LEVEL  ////////////////

function resetPrevLevel(){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetPrevLevel', adventure_id:adventure_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
//////////////////  RESET GUILDS  ////////////////

function resetGuilds(){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetGuilds', adventure_id:adventure_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
//////////////////  reset Player Adventure  ////////////////

function resetPlayerAdventure(player_id){
	showLoader("small");
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetPlayerAdventure', adventure_id:adventure_id, player_id:player_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

////////////////////////////////////////// UPDATE Adventure  ////////////////////////////////////////////

function updateAdventure(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	
	let unenrolled =[];
	$('ul.player-select li.unenrolled').each(function(index, element) {
		unenrolled.push($('input.player-id',this).val());
	});

	let adventure_ranks =[];
	$('table#adventure-ranks tbody tr').each(function(index, element) {
		if($('input.rank-level',this).val()!= "" && $('select.rank-achievement',this).val()>0 && $('textarea.rank-message',this).val()!=""){
			let rank={
				level:$('input.rank-level',this).val(),
				achievement:$('select.rank-achievement',this).val(),
				message:$('textarea.rank-message',this).val(),
			};
			adventure_ranks.push(rank);
		}
	});
	let adventure_settings =[];
	$('.setting').each(function(index, element) {
		let setting_values={
			id:$('.setting-id',this).val(),
			name:$('.setting-name',this).val(),
			label:$('.setting-label',this).val(),
			value:$('.setting-value',this).val(),
		};
		adventure_settings.push(setting_values);
	});
	let adventure_data = {
		adventure_id : $('#the_adventure_id').val(),
		adventure_owner : $('#the_adventure_owner').val(),
		adventure_badge : $('#the_adventure_badge').val(),
		adventure_logo : $('#the_adventure_logo').val(),
		adventure_certificate_signature : $('#the_adventure_certificate_signature').val(),
		adventure_gmt : $('#the_adventure_gmt').val(),
		adventure_title : $('#the_adventure_title').val(),
		adventure_xp_label : $('#the_adventure_xp_label').val(),
		adventure_bloo_label : $('#the_adventure_bloo_label').val(),
		adventure_ep_label : $('#the_adventure_ep_label').val(),
		adventure_xp_long_label : $('#the_adventure_xp_long_label').val(),
		adventure_bloo_long_label : $('#the_adventure_bloo_long_label').val(),
		adventure_ep_long_label : $('#the_adventure_ep_long_label').val(),
		adventure_type : $('#the_adventure_type').val(),
		adventure_grade_scale : $('#the_adventure_grade_scale').val(),
		adventure_progression_type : $('#the_adventure_progression_type').val(),
		adventure_privacy : $('#the_adventure_privacy').val(),
		adventure_status : $('#the_adventure_status').val(),
		adventure_instructions : $('#the_adventure_instructions').val(),
		adventure_nickname : $('#the_adventure_nickname').val(),
		adventure_code : $('#the_adventure_code').val(),
		adventure_color : $('#the_adventure_color').val(),
		adventure_hide_schedule : $('#the_adventure_hide_schedule').val(),
		adventure_hide_quests : $('#the_adventure_hide_quests').val(),
		adventure_has_guilds : $('#the_adventure_has_guilds').val(),
		adventure_level_up_array : $('#the_adventure_level_up_array').val(),
		adventure_start_date : $('#the_adventure_start_date').val(),
		adventure_end_date : $('#the_adventure_end_date').val(),
		unenrolled: unenrolled,
		adventure_ranks:adventure_ranks,
		adventure_settings:adventure_settings
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateAdventure', 
			nonce:nonce,
			adventure_data:adventure_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

////////////////////////////////////////// Preview Template  ////////////////////////////////////////////

function previewTemplate(adv_id = null){
	if(adv_id){
		$('#loader').addClass('active'); $('.overlay-bg').addClass('active');
		$("#template-"+adv_id+" .template-preview .template-preview-content").html("");
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({
				action: 'previewTemplate', 
				adventure_id:adv_id
			}),
			method: "POST",
			success: function(template) {
				if(template){
					$("#template-"+adv_id+" .template-preview .template-preview-content").html(template);
					$("#template-"+adv_id+" .template-preview").addClass('active');
				}
				$('#loader').removeClass('active'); $('.overlay-bg').removeClass('active');
			}
		});
	}else{
		return false;
	}
}
function closeTemplatePreview(){
	$(".template-preview").removeClass('active').children(".template-preview-content").html("");
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSetting(element_id, element_value){
	showLoader('small');
	if(element_id && $('#the_adventure_id').val() > 0){
		let new_value = (element_value) ? element_value : $(element_id+' .setting-value').val();
		let settings_data=[{
			id:$(element_id+' .setting-id').val(),
			name:$(element_id+' .setting-name').val(),
			label:$(element_id+' .setting-label').val(),
			value:new_value
		}];
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'saveSettings', settings_data:settings_data, adventure:$('#the_adventure_id').val()}),
			method: "POST",
			success: function(data_received) {
				displayAjaxResponse(data_received);
			}
		});
	}else{
		return false;
	}
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSettings(){
	showLoader('small');
	let settings_data=[];
	$('.setting').each(function(index, element) {
		let setting_values={
			id:$('.setting-id',this).val(),
			name:$('.setting-name',this).val(),
			label:$('.setting-label',this).val(),
			value:$('.setting-value',this).val(),
		};
		settings_data.push(setting_values);
	});
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'saveSettings', settings_data:settings_data}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
////////////////////////////////////////// Save Settings  ////////////////////////////////////////////
function saveSysConfig(){
	showLoader('small');
	let config_data=[];
	$('.config').each(function(index, element) {
		let config_values={
			id:$('.setting-id',this).val(),
			name:$('.setting-name',this).val(),
			label:$('.setting-label',this).val(),
			value:$('.setting-value',this).val(),
			type:$('.setting-type',this).val(),
			desc:$('.setting-desc',this).val(),
		};
		config_data.push(config_values);
	});
	let features_data=[];
	$('.feature').each(function(index, element) {
		let free = 0; 
		let pro = 0;
		let admin = 0;
		let god = 0;
		if($('.feature-free',this).is(':checked')){ free = 1;} else if($('.feature-free',this).val()){ free = $('.feature-free',this).val();}
		if($('.feature-pro',this).is(':checked')){ pro = 1;} else if($('.feature-pro',this).val()){ pro = $('.feature-pro',this).val();}
		if($('.feature-admin',this).is(':checked')){ admin = 1;} else if($('.feature-admin',this).val()){ admin = $('.feature-admin',this).val();}
		if($('.feature-god',this).is(':checked')){ god = 1;} else if($('.feature-god',this).val()){ god = $('.feature-god',this).val();}
		let feature_values={
			id:$('.feature-id',this).val(),
			name:$('.feature-name',this).val(),
			label:$('.feature-label',this).val(),
			type:$('.feature-type',this).val(),
			desc:$('.feature-desc',this).val(),
			free:free,
			pro:pro,
			admin:admin,
			god:god,
		};
		features_data.push(feature_values);
	});
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'saveSysConfig', config_data:config_data, features_data: features_data}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

////////////////////////////////////////// CHECK ALL CHECKBOXES  ////////////////////////////////////////////

function checkAllFeatures(p_class){
	if($('input[type=checkbox].feature-'+p_class+':checked').length == $('input[type=checkbox].feature-'+p_class).length){
		$('input[type=checkbox].feature-'+p_class).prop('checked', false);
	}else{
		$('input[type=checkbox].feature-'+p_class).prop('checked', true);
	}
}


////////////////////////////////////////// Toggle Correct ////////////////////////////////////////////
function toggleCorrect(who){
	$(who+" .toggle-button.question").toggleClass('active');
	if($(who+" .toggle-button").hasClass('active')){
		$(who+" input.option-correct").val(1);
	}else{
		$(who+" input.option-correct").val(0);
	}
}

////////////////////////////////////////// UPDATE QUEST ////////////////////////////////////////////

function updateQuest(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	let quest_reqs =[];
	$('ul#quests-reqs li.active').each(function(index, element) {
		quest_reqs.push($('input.reqs-id',this).val());
	});
	let quest_achievement_reqs =[];
	$('ul#quest-achievement-reqs li.active').each(function(index, element) {
		quest_achievement_reqs.push($('input.reqs-id',this).val());
	});
	let quest_libs =[];
	$('ul#libraries li.active').each(function(index, element) {
		quest_libs.push($('input.lib-id',this).val());
	});
	let quest_objectives =[];
	$('table#quest-objectives tbody tr').each(function(index, element) {
		if($('input.objective-content',this).val()!= ""){
			let objective={
				keyword:$('input.objective-keyword',this).val(),
				type:$('input.objective-type',this).val(),
				content:$('input.objective-content',this).val(),
			};
			quest_objectives.push(objective);
		}
	});
	let quest_questions = $('#questions .question').length;

	let steps_order = [];
	$('tbody#steps-list tr.step').each(function(){
		steps_order.push($('input.the_step_id_val',this).val());
	});
	
	let the_deadline = $('#the_quest_deadline').val()?$('#the_quest_deadline').val()+":00":"";
	let the_startdate = $('#the_quest_start_date').val()?$('#the_quest_start_date').val()+":00":"";
	
	let mech_item_reward = $("#mech_item_reward li.active input.item-id").val();
	let mech_achievement_reward = $("#the_mech_achievement_reward li.active input.achievement-reward-id").val();
	let quest_item_required = $("#item_required li.active input.item-id").val();
	let quest_data = { 
		quest_id : $('#the_quest_id').val(),
		quest_status : $('#the_quest_status').val(), 
		quest_relevance : $('#the_quest_relevance').val(), 
		quest_title : $('#the_quest_title').val(), 
		quest_content : $('#the_quest_content').val(),
		quest_success_message : $('#the_quest_success_message').val(),
		quest_type : $('#the_quest_type').val(),
		quest_guild : $('#the_quest_guild').val(),
		adventure_id : $('#the_adventure_id').val(),
		achievement_id : $('#the_achievement_id').val(),
		quest_reqs:quest_reqs,
		quest_libs:quest_libs,
		quest_item_required:quest_item_required,
		quest_achievement_reqs:quest_achievement_reqs,
		quest_secondary_headline:$('#the_quest_secondary_headline').val(),
		quest_style:$('#the_quest_style').val(),
		quest_color:$('#the_quest_color').val(),
		quest_icon:$('#the_quest_icon').val(),
		quest_order:$('#the_quest_order').val(),
		quest_objectives:quest_objectives,
		steps_order:steps_order,
		quest_mechs: {
			mech_level : $('#the_quest_level').val(),
			mech_xp:$('#the_quest_xp').val(),
			mech_ep:$('#the_quest_ep').val(),
			mech_bloo:$('#the_quest_bloo').val(),
			mech_badge: $('#the_quest_badge').val(),
			mech_deadline : the_deadline,
			mech_start_date: the_startdate,
			mech_deadline_cost: $('#the_quest_deadline_cost').val(),
			mech_unlock_cost : $('#the_quest_unlock_cost').val(),
			mech_min_words : $('#the_quest_min_words').val(),
			mech_min_links : $('#the_quest_min_links').val(),
			mech_min_images : $('#the_quest_min_images').val(),
			mech_max_attempts : $('#the_quest_max_attempts').val(),
			mech_free_attempts : $('#the_quest_free_attempts').val(),
			mech_attempt_cost : $('#the_quest_attempt_cost').val(),
			mech_questions_to_display : $('#the_quest_questions_to_display').val(),
			mech_answers_to_win : $('#the_quest_answers_to_win').val(),
			mech_time_limit : $('#the_quest_time_limit').val(),
			mech_show_answers : $('#the_quest_show_answers').val(),
			mech_item_reward:mech_item_reward,
			mech_achievement_reward:mech_achievement_reward,
		},
		quest_questions:quest_questions
	};
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateQuest', 
			nonce:nonce,
			quest_data:quest_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

//
////////////////////////////////////////// UPDATE Challenge ////////////////////////////////////////////

function updateChallenge(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	let challenge_reqs =[];
	$('ul#quests-reqs li.active').each(function(index, element) {
		challenge_reqs.push($('input.reqs-id',this).val());
	});
	
	/// DEADLINE AND STARTDATE
	let challenge_data = { 
		challenge_id : $('#the_quest_id').val(),
		challenge_status : $('#the_quest_status').val(), 
		challenge_relevance : $('#the_quest_relevance').val(), 
		challenge_title : $('#the_quest_title').val(), 
		challenge_objective : $('#the_quest_objective').val(),
		adventure_id : $('#the_adventure_id').val(),
		achievement_id : $('#the_achievement_id').val(),
		challenge_item_required: $("#item_required li.active input.item-id").val(),
		challenge_reqs:quest_reqs,
		challenge_mechs: {
			level : $('#the_quest_level').val(),
			xp:$('#the_quest_xp').val(),
			bloo:$('#the_quest_bloo').val(),
			badge: $('#the_quest_badge').val(),
			deadline : $('#the_quest_deadline').val(),
			start_date: $('#the_quest_start_date').val(),
			deadline_cost: $('#the_quest_deadline_cost').val(),
			max_attempts : $('#the_quest_max_attempts').val(),
			free_attempts : $('#the_quest_free_attempts').val(),
			attempt_cost : $('#the_quest_attempt_cost').val(),
			questions_to_display : $('#the_quest_questions_to_display').val(),
			answers_to_win : $('#the_quest_answers_to_win').val(),
			time_limit : $('#the_quest_time_limit').val(),
			show_answers : $('#the_quest_show_answers').val(),
			item_reward: $("#mech_item_reward li.active input.item-id").val(),
		}
	};
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateChallenge', 
			nonce:nonce,
			challenge_data:challenge_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

////////////////////////////////////////// UPDATE ENCOUNTER ////////////////////////////////////////////

function updateEncounter(){
	showLoader();
	let nonce = $('#new-encounter-nonce').val();
	let encounter_data = { 
		id : $('#the_enc_id').val(),
		status : $('#the_enc_status').val(),
		question : $('#the_enc_question').val(),
		correct : $('#the_enc_correct').val(),
		decoy1 : $('#the_enc_decoy1').val(),
		decoy2 : $('#the_enc_decoy2').val(),
		level : $('#the_enc_level').val(),
		xp : $('#the_enc_xp').val(),
		ep : $('#the_enc_ep').val(),
		bloo : $('#the_enc_bloo').val(),
		color : $('#the_enc_color').val(),
		badge : $('#the_enc_badge').val(),
		icon : $('#the_enc_icon').val(),
		path : $('#the_enc_achievement_id').val()
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateEncounter', 
			nonce:nonce,
			encounter_data:encounter_data,
			adventure_id : $('#the_adventure_id').val(), 
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
////////////////////////////////////////// UPDATE ORGANIZATION ////////////////////////////////////////////

function updateOrg(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#new-org-nonce').val();
	let org_data = { 
		id : $('#the-org-id').val(),
		name : $('#the-org-name').val(),
		logo : $('#the-org-logo').val(),
		color : $('#the-org-color').val(),
		status : "publish",
		about : $('#the-org-content').val(),
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateOrg', 
			nonce:nonce,
			org_data:org_data,
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
			unloadContent();
		}
	});
}
////////////////////////////////////////// Find Players To ORGANIZATION ////////////////////////////////////////////

function findPlayersToOrg(){
	showLoader();
	let nonce = $('#search-player-nonce').val();
	let search_string = $('#player-search-string').val() ;
	$('#search-players-results ul').html('');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'findPlayersToOrg', 
			nonce:nonce,
			search_string:search_string,
		}),
		method: "POST",
		success: function(results) {
			if(results){
				unloadContent();
				$('#search-players-results ul').html(results);
			}
		}
	});
}
////////////////////////////////////////// Add Player To ORGANIZATION ////////////////////////////////////////////

function addPlayerToOrg(player_id=null){
	if(player_id){
		showLoader();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({
				org_id : $("#the_org_id").val(),
				player_id : player_id,
				action: 'addPlayerToOrg', 
			}),
			method: "POST",
			success: function(results) {
				hideAllOverlay();

				if(results){
					notification('#msg-player-added-to-org', 1000, 'Added to org','check');
					$('#org-players-list').append(results);
				}
			}
		});
	}else{
		notification('#msg-player-not-added-to-org', 1000, 'Player not added to org','cancel');
	}
}
////////////////////////////////////////// Add Player To ORGANIZATION ////////////////////////////////////////////

function setPlayerOrgCapabilities(player_id=null, role){
	if(player_id){
		showLoader('small');
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({
				org_id : $("#the_org_id").val(),
				player_id : player_id,
				role : role,
				action: 'setPlayerOrgCapabilities', 
			}),
			method: "POST",
			success: function(json_text) {
				hideAllOverlay();
				displayAjaxResponse(json_text);
			}
		});
	}else{
		notification('#msg-player-not-added-to-org', 1000, 'Player not added to org','cancel');
	}
}
////////////////////////////////////////// UPDATE SPONSOR ////////////////////////////////////////////

function updateSponsor(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#new-sponsor-nonce').val();
	let sponsor_data = { 
		id : $('#the-sponsor-id').val(),
		name : $('#the-sponsor-name').val(),
		url : $('#the-sponsor-url').val(),
		logo : $('#the-sponsor-logo').val(),
		color : $('#the-sponsor-color').val(),
		level : $('#the-sponsor-level').val(),
		image : $('#the-sponsor-image').val(),
		about : $('#the-sponsor-about').val(),
		twitter : $('#the-sponsor-twitter').val(),
		linkedin : $('#the-sponsor-linkedin').val(),
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateSponsor', 
			nonce:nonce,
			sponsor_data:sponsor_data,
			adventure_id : $('#the_adventure_id').val(), 
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
			unloadContent();
		}
	});
}
////////////////////////////////////////// UPDATE ACHIEVEMENT ////////////////////////////////////////////

function updateAchievement(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	let libs =[];
	$('ul#libraries li.active').each(function(index, element) {
		libs.push($('input.lib-id',this).val());
	});
	let awarded_players =[];
	$('ul.player-select li.active').each(function(index, element) {
		awarded_players.push($('input.player-id',this).val());
	});
	let achievement_data = { 
		a_id : $('#the_achievement_id').val(),
		a_status : $('#the_achievement_status').val(), 
		a_name : $('#the_achievement_name').val(), 
		a_xp : $('#the_achievement_xp').val(), 
		a_ep : $('#the_achievement_ep').val(), 
		a_bloo : $('#the_achievement_bloo').val(), 
		a_color : $('#the_achievement_color').val(), 
		a_badge : $('#the_achievement_badge').val(), 
		a_deadline : $('#the_achievement_deadline').val(), 
		a_max : $('#the_achievement_max').val(), 
		a_display : $('#the_achievement_display').val(), 
		a_group : $('#the_achievement_group').val(), 
		a_path : $('#the_achievement_path').val(), 
		magic_code : $('#the_achievement_code').val(), 
		a_content : $('#the_achievement_content').val(), 
		adventure_id : $('#the_adventure_id').val(), 
		awarded_players:awarded_players,
		libs:libs
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateAchievement', 
			nonce:nonce,
			achievement_data:achievement_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
////////////////////////////////////////// UPDATE TEAM ////////////////////////////////////////////

function updateGuild(){
	showLoader();
	
	let nonce = $('#nonce').val();
	
	let guild_players =[];
	$('ul.player-select li.active').each(function(index, element) {
		guild_players.push($('input.player-id',this).val());
	});
	let guild_data = { 
		g_id : $('#the_guild_id').val(),
		g_status : $('#the_guild_status').val(), 
		g_name : $('#the_guild_name').val(), 
		g_group : $('#the_guild_group').val(), 
		g_capacity : $('#the_guild_capacity').val(), 
		g_color : $('#the_guild_color').val(), 
		g_logo : $('#the_guild_logo').val(), 
		g_assign_on_login : $('#the_guild_assign_on_login').val(), 
		adventure_id : $('#the_adventure_id').val(), 
		guild_players:guild_players
	};
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateGuild', 
			nonce:nonce,
			guild_data:guild_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
////////////////////////////////////////// UPDATE BLOCKER ////////////////////////////////////////////

function updateBlocker(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	
	let fined_players =[];
	$('ul.player-select li.active').each(function(index, element) {
		fined_players.push($('input.player-id',this).val());
	});
	let blocker_data = { 
		blocker_id : $('#the_blocker_id').val(),
		blocker_cost : $('#the_blocker_cost').val(),
		blocker_description : $('#the_blocker_description').val(), 
		adventure_id : $('#the_adventure_id').val(), 
		fined_players:fined_players
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateBlocker', 
			nonce:nonce,
			blocker_data:blocker_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

////////////////////////////////////////// UPDATE ITEM ////////////////////////////////////////////

function updateItem(){
	showLoader();
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	let item_data = { 
		item_id : $('#the_item_id').val(),
		item_name : $('#the_item_name').val(), 
		item_stock : $('#the_item_stock').val(), 
		item_sold : $('#the_item_sold').val(), 
		item_cost : $('#the_item_cost').val(), 
		item_description : $('#the_item_description').val(), 
		item_secret_description : $('#the_item_secret_description').val(), 
		item_type : $('#the_item_type').val(), 
		item_visibility : $('#the_item_visibility').val(), 
		item_badge : $('#the_item_badge').val(), 
		item_secret_badge : $('#the_item_secret_badge').val(), 
		item_max : $('#the_item_player_max').val(), 
		item_level : $('#the_item_min_level').val(), 
		item_category : $('#the_item_category').val(), 
		adventure_id : $('#the_adventure_id').val(),
		item_start_date : $('#the_item_start_date').val(),
		item_deadline : $('#the_item_deadline').val(),
		achievement_id : $('#the_achievement_id').val(),
		item_x : $('#the_item_x').val(), 
		item_y : $('#the_item_y').val(), 
		item_z : $('#the_item_z').val(), 
		tabi_id : $('#the_item_tabi').val(), 
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'updateItem', 
			nonce:nonce,
			item_data:item_data
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}


////////////////////////////////////////// SUBMIT PLAYER WORK ////////////////////////////////////////////

function validatePlayerWork(nextStep){
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	$('#pp-content-counter').html($('#the_pp_content').val());
	
	let pp_links = $('#pp-content-counter a').length;
	let pp_images = $('#pp-content-counter img').length;
	
	let pp_data = { 
		quest_id : $('#the_quest_id').val(),
		adventure_id : $('#the_adventure_id').val(),
		pp_content : $('#the_pp_content').val(), 
		pp_links : pp_links,
		pp_images : pp_images,
		pp_type : $('#the_pp_type').val()
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'validatePlayerWork', 
			pp_data:pp_data
		}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			if(data.continue){
				jumpToStep(nextStep);
			}else{
				$("#feedback .content").html(data.message);
				$("#feedback").addClass('active');
			}
		}
	});
}


function submitPlayerWork(){
	
	if(typeof tinyMCE=='object' && typeof tinyMCE.triggerSave == 'function') { 
		tinyMCE.triggerSave(); 
	}
	let nonce = $('#nonce').val();
	let override_nonce = $('#override_nonce').val();
	
	$('#pp-content-counter').html($('#the_pp_content').val());
	
	let pp_links = $('#pp-content-counter a').length;
	let pp_images = $('#pp-content-counter img').length;
	
	let pp_data = { 
		quest_id : $('#the_quest_id').val(),
		adventure_id : $('#the_adventure_id').val(),
		pp_content : $('#the_pp_content').val(), 
		pp_links : pp_links,
		pp_images : pp_images,
		pp_type : $('#the_pp_type').val()
	};
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'submitPlayerWork', 
			nonce:nonce,
			override_nonce:override_nonce,
			pp_data:pp_data
		}),
		method: "POST",
		success: function(data_received) {
			if(isJson(data_received)){
				displayAjaxResponse(data_received);
			}else{
				$("#feedback .content").html(data_received);
				let flipTimeout = setTimeout(function (){
					$("#feedback").addClass('active'); 
				},100);
			}
			setCurrentQuest(0,1);
			let videoElements = document.querySelectorAll("video"); 
			for (let videoEl of videoElements) {
				videoEl.pause();
			}

		}
	});
}
 

////////////////////////////////////////// START ATTEMPT ////////////////////////////////////////////

function startAttempt(){
	$('#start-attempt-btn').prop('disabled',true);
	let nonce = $('#nonce').val();
	let challenge_id = $('#the_challenge_id').val();
	let adventure_id = $('#the_adventure_id').val();
	let attempt_cost = $('#the_attempt_cost').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action: 'startAttempt', 
			nonce:nonce,
			challenge_id:challenge_id,
			adventure_id:adventure_id,
			attempt_cost:attempt_cost
		}),
		method: "POST",
		success: function(json_text) {
			let data = JSON.parse(json_text);
			if(data.att_id){
				$("#feedback .content").html(data.message);
				$("#feedback").addClass('active');
				if(!$('.overlay-bg').is(':visible')){
					$('.overlay-bg').fadeIn('fast');
				}
				$("#the_attempt_id").val(data.att_id);
				$("#feedback").click(function(){
					hideAllOverlay();
					$("#feedback").removeClass('active').unbind('click');
					if($("#the_time_limit").val() > 0){
						countdown();
					}
					$('#challenge').removeClass('idle').addClass('running');
				});
				$("#start-attempt-btn").prop("disabled",true);
			}else{
				$("#feedback .content").html(data.message);
				$("#feedback").addClass('active');
				if(!$('.overlay-bg').is(':visible')){
					$('.overlay-bg').fadeIn('fast');
				}
				$("#feedback").click(function(){
					$("#feedback").removeClass('active').unbind('click');
					hideAllOverlay();
				});
			}
		}
	});
}

function navToQuestion(id){
	animateScroll('#question-'+id);
	$('.question-number').removeClass('current');
	$('#question-number-'+id).addClass('current');
	$('#question-number-mobile-'+id).addClass('current');
}

function nextQuestion(){
	let totalQuestions = document.getElementsByClassName('challenge-question');
	let cur = parseInt($('#current-question').val());
	if(cur < totalQuestions.length-1){
		showQuestion(cur+1);
	}
}
function prevQuestion(){
	let cur = parseInt($('#current-question').val());
	if(cur > 0){
		showQuestion(cur-1);
	}
}
function showQuestion(id){
	let questions = document.getElementsByClassName('challenge-question');
	let who = questions[id];
	
	if(id <= 0){ $('#prev-question-button').addClass('inactive'); }else{ $('#prev-question-button').removeClass('inactive'); }
	if(id >= questions.length-1){ $('#next-question-button').addClass('inactive'); }else{ $('#next-question-button').removeClass('inactive'); }
	$('.challenge-question').removeClass('current');
	who.classList.add('current');
	$('#current-question').val(id);
}
////////////////////////////////////////// Submit Answer ////////////////////////////////////////////

function submitAnswer(answer_id, question_id){
	let attempt_id = $('#the_attempt_id').val();
	let adventure_id = $('#the_adventure_id').val();
	let challenge_id = $('#the_challenge_id').val();
	let question_type = $("#question-"+question_id+" .question-type").val();
	let answer_value = [];
	if(question_type == 'single'){
		if($("li#op"+answer_id+"-"+question_id).hasClass('active')){
			$("li#op"+answer_id+"-"+question_id).removeClass('active');
			answer_id = 0;
		}else{
			$("li#op"+answer_id+"-"+question_id).addClass('active').siblings().removeClass('active');
		}
	}else if(question_type == 'multiple'){
		if($("li#op"+answer_id+"-"+question_id).hasClass('active')){
			$("li#op"+answer_id+"-"+question_id).removeClass('active');
		}else{
			$("li#op"+answer_id+"-"+question_id).addClass('active');
		}
		answer_id = 0;
		$("#question-"+question_id+" .question-options li.active").each(function(index, element) {
			answer_value.push($('input.answer-id',this).val());
		});
	}
	$("#question-number-"+question_id).addClass('answered');
	$("#question-number-mobile-"+question_id).addClass('answered');

	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action:'submitAnswer', 
			question_id:question_id,
			challenge_id:challenge_id,
			attempt_id:attempt_id,
			answer_value:answer_value,
			answer_id:answer_id,
			adventure_id:adventure_id
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}

////////////////////////////////////////// Grade Challenge ////////////////////////////////////////////

function gradeChallenge(){
	let attempt_id = $('#the_attempt_id').val();
	let challenge_id = $('#the_challenge_id').val();
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action:'gradeChallenge', 
			challenge_id:challenge_id,
			attempt_id:attempt_id,
			adventure_id:adventure_id
		}),
		method: "POST",
		success: function(data_received) {
			setCurrentQuest(0,1);
			if(isJson(data_received)){
				displayAjaxResponse(data_received);
			}else{
				$("#feedback .content").html(data_received);
				let flipTimeout = setTimeout(function (){
					$("#feedback").addClass('active'); 
					$("#challenge").removeClass('running').addClass('complete');
				},100);
			}
			
		}
	});
}
////////////////////////////////////////// Fail Quest ////////////////////////////////////////////

function failQuest(){
	let quest_id = $('#the_quest_id').val();
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({
			action:'failQuest', 
			quest_id:quest_id,
			adventure_id:adventure_id
		}),
		method: "POST",
		success: function(data_received) {
			$("#feedback .content").html(data_received);
			let flipTimeout = setTimeout(function (){
				$("#feedback").addClass('active'); 
			},100);
		}
	});
}
////////////////////////////////////////// answerEncounter ////////////////////////////////////////////
function answerEncounter(option){
	showLoader('small');
	let enc_id = $('#current-encounter-id').val();
	let value = $("#enc-opt-"+option).text();
	$('.encounter-options button').prop('disabled',true);
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'answerEncounter', adventure_id:$("#the_adventure_id").val(), enc_id:enc_id, value:value}),
		method: "POST",
		success: function(json_text) {
			let data = JSON.parse(json_text);
			if(data.success){
				$('#micro-status-player-ep .end-value, #status-player-ep .end-value').val(parseInt(data.EP));
				animateNumber('#micro-status-player-ep, #status-player-ep');
				let percEP = data.EP * 100/ $('#player-max-ep').val();
				$('#micro-status-ep-progress-bar, #profile-box-ep-progress-bar').css('width',percEP+'%');
				
				$("#notify-message ul.content").append(data.message);
				$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
					$(this).remove();
					//$("#feedback, #overlay-content").removeClass('active');
				});
				randomEncounter();
			}else{
				$("#notify-message ul.content").append(data.message);
				$("#notify-message ul.content li:last-child").delay(1000).fadeOut(300, function(){
					$(this).remove();
					//$("#feedback, #overlay-content").removeClass('active');
				});
				randomEncounter();
			}
		}
	});
}
////////////////////////////////////////// submitMagicCode ////////////////////////////////////////////
function submitMagicCode(){
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'magicCode',adventure_id:$("#the_adventure_id").val(), magic_code:$("#magic-code").val()}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
////////////////////////////////////////// choosePath - ThroughMagicCode ////////////////////////////////////////////
function preChoosePath(step, path, label){
	$('#path-choices-'+step+' .path').removeClass('selected');
	$('#path-'+path).addClass('selected');
	$('#path-choices-'+step+' input.selected-path').val(path);
	$('#step-'+step+' .chosen-path-label').text(label);
}
////////////////////////////////////////// choosePath - ThroughMagicCode ////////////////////////////////////////////
function choosePath(step, next){
	showLoader('small');
	let path = $('#path-choices-'+step+' input.selected-path').val();
	if(path){
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'choosePath',adventure_id:$("#the_adventure_id").val(), path:path}),
			method: "POST",
			success: function(json_text) {
				displayAjaxResponse(json_text);
				jumpToStep(next);
			}
		});
	}else{
		notification('#must-choose-'+step);
		hideAllOverlay();
	}
}
////////////////////////////////////////// triggerAchievement ////////////////////////////////////////////
function triggerAchievement(achievement_id, player_id){
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'triggerAchievement', achievement_id:achievement_id, player_id:player_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
			let a_data = JSON.parse(json_text);
			if(a_data.action =='assign'){
				$("#player-achievement-"+player_id+", #player-achievement-list-"+player_id).addClass('active');
			}else{
				$("#player-achievement-"+player_id+", #player-achievement-list-"+player_id).removeClass('active');
			}

		}
	});
}
function triggerAchievements(status='on'){
	let adventure_id = $('#the_adventure_id').val();
	let achievement_id = $('#the_achievement_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'triggerAchievements', achievement_id:achievement_id, adventure_id:adventure_id, status:status}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
			let a_data = JSON.parse(json_text);
			if(a_data.action =='assigned-all'){
				$(".player-achievement-item").addClass('active');
			}else if(a_data.action =='removed-all'){
				$(".player-achievement-item").removeClass('active');
			}
		}
	});
}
////////////////////////////////////////// triggerGuild ////////////////////////////////////////////
function triggerGuild(guild_id, player_id){
	let adventure_id = $('#the_adventure_id').val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'triggerGuild', guild_id:guild_id, player_id:player_id, adventure_id:adventure_id}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
			let a_data = JSON.parse(json_text);
			if(a_data.action =='assign'){
				$("#player-guild-"+player_id+", #player-guild-list-"+player_id).addClass('active');
			}else{
				$("#player-guild-"+player_id+", #player-guild-list-"+player_id).removeClass('active');
			}

		}
	});
}
////////////////////////////////////////// postToWall ////////////////////////////////////////////
function postToWall(ann_type, target_id=""){
	let nonce = $('#nonce').val();
	let adventure_id = $('#the_adventure_id').val();
	let ann_content = $('#message-content').val();
	if(ann_type=='guild'){
		let guild_id = target_id;
	}
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'postToWall', ann_type:ann_type, guild_id:guild_id, adventure_id:adventure_id, ann_content:ann_content, nonce:nonce}),
		method: "POST",
		success: function(json_text) {
			loadChat(ann_type, guild_id);
			$('#message-content').val('');
			hideAllOverlay();
		}
	});
}




////////////////////////////////////////// LOAD CHAT ////////////////////////////////////////////
function loadChat(type, guild_id=""){
	$('.wall-nav-btn').removeClass('active');
	$(".wall-content").removeClass('active');
	showLoader();
	let myTimeout = setTimeout(function(){
		$("#message-feed").html('');
		$('.wall-content-header').removeClass('active');
		let adventure_id = $("#the_adventure_id").val();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'loadChat', adventure_id:adventure_id,type:type, guild_id:guild_id}),
			method: "POST",
			success: function(data_received){
				$('#message-feed').html(data_received);
				let myTimeout2 = setTimeout(function(){
					$('#message-type-'+type+guild_id).addClass('active');
					$('#wall-content-header-'+type+guild_id).addClass('active');
					$(".wall-content").addClass('active');
				},500);
				if(type=='guild'){
					$(".guild-post-button").addClass('hidden');
					$("#guild-post-button-"+guild_id).removeClass('hidden');
					$("#public-post-button, #announcement-post-button").addClass('hidden');
				}else if(type=='public'){
					$("#public-post-button, #announcement-post-button").removeClass('hidden');
					$(".guild-post-button").addClass('hidden');
				}
				hideAllOverlay();
			}
		}); 
	},500);
	
	
}

function filterChat(type){
	if(type){
		$('.message-feed ul li.message').hide();
		$('.message-feed ul li.'+type).show();
	}else{
		$('.message-feed ul li.message').show();
	}
}

//////////////////  Buy Item ////////////////

function buyItem(item_id){ 
	let nonce = $('#purchase-nonce').val();
	let adventure_id = $('#the_adventure_id').val(); 
	if(item_id){
		showLoader();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'buyItem', item_id:item_id, nonce:nonce, adventure_id:adventure_id}),
			method: "POST",
			success: function(data_received) {
				displayAjaxResponse(data_received);
			}
		});
	}
}
//////////////////  PickUp Item ////////////////

function pickupItem(item_id, nonce){ 
	let adventure_id = $('#the_adventure_id').val(); 
	if(item_id){
		showLoader();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'pickupItem', item_id:item_id, nonce:nonce, adventure_id:adventure_id}),
			method: "POST",
			success: function(data_received) {
				displayAjaxResponse(data_received);
			}
		});
	}
}
//////////////////  Check Item ////////////////

function checkItem(step_id){ 
	let adventure_id = $('#the_adventure_id').val();
	let item_id = $("#step-backpack-"+step_id+" .item.active input.item-id").val();
	let nonce = $('#nonce-item-req-'+step_id).val();
	if(item_id){
		showLoader();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'checkItem', item_id:item_id, nonce:nonce, adventure_id:adventure_id, step_id:step_id}),
			method: "POST",
			success: function(data_received) {
				displayAjaxResponse(data_received);
			}
		});
	}else{
		notification('#msg-no-step-req-selected', 2000);
	}
}
//////////////////  payBlocker ////////////////

function payBlocker(blocker_id){ 
	let nonce = $('#nonce').val();
	let adventure_id = $('#the_adventure_id').val();
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'payBlocker', blocker_id:blocker_id, nonce:nonce, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			
			displayAjaxResponse(data_received);
			
		}
	});
}

//////////////////  setIconTo ////////////////
function setIconTo(the_icon){
	$('.icon-select button.form-ui').removeClass('active');
	$("."+the_icon+"-icon-button").addClass('active');
	$("input.icon-selected").val(the_icon);
}
//////////////////  setColorTo ////////////////
function selectImage(id,group){
	$(group+' .button').removeClass('active');
	$(group+' '+id).addClass('active');
	let image = $(group+' '+id+' input.value').val();
	$('#the_quest_badge').val(image);
	$('#the_quest_badge_thumb').css('background-image', 'url('+image+')');
}

//////////////////  USE Item ////////////////

function useItem(trnx_id, player_id='', use_item = 0){ 
	let nonce = $('#use-item-nonce').val();
	let adventure_id = $('#the_adventure_id').val(); 
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'useItem', trnx_id:trnx_id, nonce:nonce, adventure_id:adventure_id, player_id:player_id, use_item:use_item }),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

//// animateText as speech
function revealOneCharacter(list) {
   let nextChar = list.splice(0, 1)[0];
   nextChar.span.classList.add("revealed");
   nextChar.classes.forEach((c) => {
	  nextChar.span.classList.add(c);
   });
   let charactersDelay = nextChar.isSpace && !nextChar.pause ? 0 : nextChar.delayAfter;

   if (list.length > 0) {
	  setTimeout(function () {
		 revealOneCharacter(list);
	  }, charactersDelay);
   }
}



//////////////////  jumpToStep ////////////////
function skipToStep(step){
	document.location.href="#step-"+step;
}
function jumpToStep(step_to, ep=0){ 
	let quest_id = $("#the_quest_id").val();
	let current_step = step_to;
	setCurrentQuest(quest_id, current_step);
	$("#step-"+step_to).addClass('active');
	if($("#step-background-video-"+step_to)){
		$("#step-background-video-"+step_to).addClass('active');
	}
	let stepTimeout = setTimeout(function (){
		$(".step:not(#step-"+step_to+"), .step-background-video:not(#step-background-video-"+step_to+")").removeClass('active');
	},300);
	let videoElements = document.querySelectorAll("video"); 
	for (let videoEl of videoElements) {
		videoEl.pause();
	}
	let cur_video_bg = document.getElementById(`step-background-video-${step_to}`);
	if(cur_video_bg){
		cur_video_bg.play();
	}

}
function jumpToQuestion(question_to){
	$(".step").removeClass('active');
	$("#step-"+question_to).addClass('active');
	let survey_id = $("#the_survey_id").val();
	setCurrentQuest(survey_id, question_to);
	
}


function setCurrentQuest(quest_id,step){
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setCurrentQuest', quest_id:quest_id, step:step, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			let data = JSON.parse(data_received);
			if(data.success){
				$("#current-quest-torch").attr('href',data.current_quest_url).removeClass('hidden');
			}else{
				$("#current-quest-torch").attr('href','').addClass('hidden');				
			}
		}
	});
}


//////////////////  purchaseDeadline ////////////////

function purchaseDeadline(quest_id){ 
	let nonce = $('#purchase_nonce').val();
	let adventure_id = $('#the_adventure_id').val(); 
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'purchaseDeadline', quest_id:quest_id, nonce:nonce, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

//////////////////  payment ////////////////
function payment(object_id, type){ 
	let nonce = $('#payment_nonce').val();
	let adventure_id = $('#the_adventure_id').val(); 
	showLoader();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'payment', object_id:object_id, type:type, nonce:nonce, adventure_id:adventure_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


//////////////////  SET GRADE  ////////////////
function setGrade(quest_id,player_id){
	let nonce = $("#grade_nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let grade = $("#the_quest_grade").val();
	if(!grade){
		grade = $("#the_post_grade_"+quest_id+"_"+player_id).val();
	}
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setGrade', quest_id:quest_id, player_id:player_id, grade:grade, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}




//////////////////  DELETE POST ////////////////

function updateStatus(id,type){  //////////////// DEPRECATED !!!!!!!
	let action = $("#"+type+"-"+id+" .update-status").val();
	if(action){
		let what = action; 
		br_confirm_trd(what,id,type);
	}
}
function confirmStatus(id,type,action){
	$("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id").val(id);
	$("#trd-type").val(type);
	$("#trd-action").val(action);
	br_trash(); 
}
function br_confirm_trd(trash_action,id,type){
	hideAllOverlay();
	let message = $("#msg-"+trash_action).html();
	$("#feedback .content").html(message);
	$("#feedback").addClass('active');
	$("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id").val(id);
	$("#trd-type").val(type);
	$("#trd-action").val(trash_action);
}
function clearTRD(){
	hideAllOverlay();
	$("#br-delete-id, #br-trash-id, #br-publish-id, #br-draft-id, #trd-type, #trd-action").val('');
}

function br_trash(){
	showLoader();
	let trash_action = $("#trd-action").val();
	let nonce = $('#'+trash_action+'-nonce').val();
	let adventure_id = $("#the_adventure_id").val();
	let id = $('#br-'+trash_action+'-id').val();
	let type = $("#trd-type").val();
	let reload = $("#reload").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'br_trash', id:id, nonce:nonce, adventure_id:adventure_id, type:type,reload:reload}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function emptyTrash(type){
	showLoader();
	let nonce = $('#empty-trash-nonce').val();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'br_empty_trash', nonce:nonce, adventure_id:adventure_id, type:type}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function resetTransactions(player_id){
	showLoader();
	let nonce = $('#reset_nonce').val();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'resetTransactions', nonce:nonce, adventure_id:adventure_id, player_id:player_id}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Toggle Column  //////////////////

function toggleColumn(type=''){
	if(type){
		$('table.table thead tr td.'+type+' button.form-ui').toggleClass('opacity-50');
		$('table.table tbody tr td.'+type).toggle();
	}
}


///////////////////////// Set XP  //////////////////

function setXP(id,type){
	let nonce = $("#xp-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let xp = $("#the_xp-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setXP', xp:xp, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


///////////////////////// Set EP  //////////////////

function setEP(id,type){
	let nonce = $("#ep-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let ep = $("#the_ep-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setEP', ep:ep, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set BLOO  //////////////////

function setBLOO(id,type){
	let nonce = $("#bloo-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let bloo = $("#the_bloo-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setBLOO', bloo:bloo, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set MAX PLAYERS  //////////////////

function setMaxPlayers(id){
	let nonce = $("#max-players-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let max = $("#the_max_players-achievement-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setMaxPlayers', max:max, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set LEVEL  //////////////////

function setLevel(id,type){
	let nonce = $("#level-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let level = $("#the_level-"+type+"-"+id).val();
	let auto_xp;
	if($("#resource-autofill").val()>0){
		if($("#resource-autofill").val()==65){
			auto_xp = Math.round(level*1000*0.65);
		}else if($("#resource-autofill").val()==50){
			auto_xp = Math.round(level*1000*0.5);
		}else if($("#resource-autofill").val()==35){
			auto_xp = Math.round(level*1000*0.35);
		}else if($("#resource-autofill").val()==25){
			auto_xp = Math.round(level*1000*0.25);
		}else if($("#resource-autofill").val()==10){
			auto_xp = Math.round(level*1000*0.1);
		}
		let auto_bloo = Math.round(auto_xp/10);
		let auto_ep = Math.round(auto_xp/20);
		$("#the_xp-"+type+"-"+id).val((auto_xp));
		$("#the_bloo-"+type+"-"+id).val((auto_bloo));
		$("#the_ep-"+type+"-"+id).val((auto_ep));
		setXP(id,type);
		setBLOO(id,type);
		setEP(id,type);
	}
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setLevel', level:level, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set Dimensions  //////////////////

function setDimensions(id,type){
	let nonce = $("#dimensions-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let width = $("#the_width-"+type+"-"+id).val();
	let height = $("#the_height-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setDimensions', width:width,  height:height, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// setTabiOnJourney  //////////////////

function setTabiOnJourney(id){
	let nonce = $("#tabi-on-journey-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	showLoader('small'); 
	let tabi_id;

	$(function() {
		$('.tabi-on-journey-checkbox').click(function() {
			if ($(this).is(':checked')) {
				$('.tabi-on-journey-checkbox').not(this).prop('checked', false);
			} else {
				$('.tabi-on-journey-checkbox').prop('checked', false);
			}
		});
	});	
	if($("#tabi-on-journey-"+id).is(':checked')){
		tabi_id = id;
	}else{
		tabi_id = 0;
	}
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setTabiOnJourney', id:tabi_id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set Achievement  //////////////////
function setAchievement(id,type){
	showLoader('small');
	let nonce = $("#achievement-nonce").val();
	let achievement_id = $("#"+type+"-"+id+" select.update-achievement").val();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setAchievement', achievement_id:achievement_id, type:type, adventure_id:adventure_id, id:id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set Guild  //////////////////
function setGuild(id,type){
	showLoader('small');
	let nonce = $("#guild-nonce").val();
	let guild_id = $("#"+type+"-"+id+" select.update-guild").val();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setGuild', guild_id:guild_id, type:type, adventure_id:adventure_id, id:id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set Speaker  //////////////////
function setSpeaker(id){
	showLoader('small');
	let nonce = $("#set-speaker-nonce").val();
	let speaker = $("#speaker-"+id).val();
	let adventure_id = $("#the_adventure_id").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setSpeaker', id:id, speaker:speaker, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


///////////////////////// setDisplayStyle  //////////////////

function setDisplayStyle(id,type){
	let nonce = $("#display-style-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let style = $("#the_quest_style-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setDisplayStyle', style:style, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set StartDate  //////////////////

function setStartDate(id,type){
	let nonce = $("#start-date-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let start_date = $("#the_start_date-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setStartDate', start_date:start_date, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set Deadline  //////////////////

function setDeadline(id,type){
	let nonce = $("#deadline-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let deadline = $("#the_deadline-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setDeadline', deadline:deadline, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// updateAdventureTitle  //////////////////

function updateAdventureTitle(adventure_id){
	let nonce = $("#update-adv-title-nonce-"+adventure_id).val();
	let adv_title = $("#adventure-title-update-"+adventure_id+" input.new-adventure-title").val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updateAdventureTitle', adv_title:adv_title, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
			activate("#adventure-title-update-"+adventure_id);
			$("#adventure-name-"+adventure_id).text(adv_title);
		}
	});
}
///////////////////////// Set Title  //////////////////

function setTitle(id,type){
	let nonce = $("#title-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let title = $("#the_title-"+type+"-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setTitle', title:title, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set Title  //////////////////

function setBadge(id,type){
	let nonce = $("#title-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let badge = $("#the_"+type+"_badge-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setBadge', badge:badge, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}
///////////////////////// Set Title  //////////////////

function setColor(id,color,type){
	let nonce = $("#title-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	$("#color-trigger-"+type+"-"+id).removeClass().addClass('icon-button font _24 sq-40 '+color+'-bg-400');
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setColor', color:color, type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
			if(type=='tabi'){
				$("#tabi-"+id).removeClass().addClass('row-container '+color+'-bg-100');
			}

		}
	});
}
function selectColor(id,color){
	$(id).val(color);
}

///////////////////////// Set Magic Code  //////////////////

function setMagicCode(id){
	let nonce = $("#magic-code-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let code = $("#the_magic_code-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setMagicCode', code:code, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set Item Category  //////////////////

function setCategory(id){
	let nonce = $("#item-cat-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let category = $("#the_item_category-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setCategory', category:category, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set Guild Group  //////////////////

function setGuildGroup(id){
	let nonce = $("#guild-group-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let guild_group = $("#the_guild_group-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setGuildGroup', guild_group:guild_group, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

///////////////////////// Set Guild Group  //////////////////

function setGuildCapacity(id){
	let nonce = $("#guild-capacity-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let guild_capacity = $("#the_guild_capacity-"+id).val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setGuildCapacity', guild_capacity:guild_capacity, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


///////////////////////// Upload Image  //////////////////
function showWPUpload(who, callback, type, q_id, o_id){
	let file_frame;
	let the_target = $('#'+who);
	// if the file_frame has already been created, just reuse it
	if (file_frame) {
		file_frame.open();
		return;
	}
	file_frame = wp.media.frames.file_frame = wp.media({
		title: $( this ).data('uploader_title'),
		button: {
			text: $( this ).data('uploader_button_text'),
		},
		multiple: false // set this to true for multiple file selection
	});

	file_frame.on('select', function(){
		let attachment = file_frame.state().get('selection').first().toJSON();
		if(the_target.is('img')){
			the_target.attr('src', attachment.url);
			the_target.parent().removeClass('empty').addClass('full');
		}else if(the_target.is('textarea')){
			let curValue = the_target.val();
			let addImg = '<img src="'+attachment.url+'" height="100">';
			let newValue = curValue + addImg;
			$(the_target).val(newValue);
			updateStep();
		}else{
			if($('#'+who+'_thumb_video').length > 0){
				if(attachment.type=='video'){
					$('#'+who+'_thumb_video source').attr('src', attachment.url);
					$('#'+who+'_thumb_video')[0].load();
					$('#'+who+'_thumb_video').addClass('active');
				}else if(attachment.type=='image'){
					$('#'+who+'_thumb_video source').removeAttr('src');
					$('#'+who+'_thumb_video')[0].load();
					$('#'+who+'_thumb_video').removeClass('active');
				}
			}
			$('#'+who+'_thumb').css('background-image', 'url('+attachment.url+')');
			the_target.val(attachment.url);
		}
		if(type && q_id){ 
			if(callback == 'q'){
				updateQuestion(type, q_id);
			}else if(callback=='o'){
				let main_id = $('#the_'+type+'_id').val();
				updateOption(type, q_id, o_id);
			}else if(callback=='a'){
				setBadge(q_id,type);
			}else if(callback=='step'){
				updateStepButton(q_id);
			}else if(callback=='c'){
				updateObjective(q_id);
			} 
		}
		if(callback=='profile-autosave'){
			updateProfile();
		}
	});
	file_frame.open();
}
function showWPUploadVideo(who){
	let file_frame;
	let the_target = $('#'+who);
	if (file_frame) {
		file_frame.open();
		return;
	}
	file_frame = wp.media.frames.file_frame = wp.media({
		title: $( this ).data('uploader_title'),
		button: {
			text: $( this ).data('uploader_button_text'),
		},
		library: {
			type: 'video/mp4'
		},
		multiple: false
	});
	file_frame.on('select', function(){
		let attachment = file_frame.state().get('selection').first().toJSON();
		if(attachment.type=='video'){
			$('#'+who+'_thumb_video source').attr('src', attachment.url);
			$('#'+who+'_thumb_video')[0].load();
			$('#'+who+'_thumb_video').addClass('active');
		}
		the_target.val(attachment.url);
	});
	file_frame.open();
}
///////////////////////// Upload Multimedia  //////////////////
function showWPUploadMultimedia(who, type, q_id){
	let file_frame;
	let the_target = $('#'+who);
	let the_target_thumb = $('#'+who+"_thumb");
	// if the file_frame has already been created, just reuse it
	if (file_frame) {
		file_frame.open();
		return;
	}
	file_frame = wp.media.frames.file_frame = wp.media({
		title: $( this ).data('uploader_title'),
		button: {
			text: $( this ).data('uploader_button_text'),
		},
		multiple: false // set this to true for multiple file selection
	});

	file_frame.on('select', function(){
		let attachment = file_frame.state().get('selection').first().toJSON();
		$('#'+who+" .multimedia-element").html('');
		if(attachment.type=='video'){
			$('#'+who+" .multimedia-element").append('<video id="'+who+'_thumb" controls class="gallery-item-video"><source src="'+attachment.url+'"> </video>');
			$('#'+who+'_thumb')[0].load();
		}else if(attachment.type=='audio'){
			$('#'+who+" .multimedia-element").append('<audio id="'+who+'_thumb" controls class="gallery-item-audio"><source src="'+attachment.url+'"> </audio>');
			$('#'+who+'_thumb')[0].load();
		}else if(attachment.type=='image'){
			$('#'+who+" .multimedia-element").append('<img id="'+who+'_thumb" src="'+attachment.url+'">');
		}
		the_target.val(attachment.url);
		updateQuestion(type, q_id);
	});
	file_frame.open();
}
/////////////////////// UPDATE DUPLICATE BUTTON /////////////////////////

function updateDuplicateQuestButton(id){
	let adventure_id = $('#adventure-value-'+id).val();
	$('#duplicateButton-'+id).attr('onClick',"duplicateQuest("+id+","+adventure_id+")")
}
/////////////////////// UPDATE DUPLICATE BUTTON /////////////////////////

function updateDuplicateRowButton(id,type=''){
	let adventure_id = $('#adventure-value-'+type+'-'+id).val();
	$('#duplicateRowButton-'+type+'-'+id).attr('onClick',"duplicateRow("+id+","+adventure_id+",'"+type+"')")
}
/////////////////////// DUPLICATE QUEST /////////////////////////

function duplicateQuest(quest_id=0, adventure_id=$("#adventure_target").val()){
	showLoader();
	let quest_data = {
		quest_id : quest_id, 
		adventure_id : adventure_id
	}
	let nonce = $("#duplicator_nonce").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'duplicateQuest', 
			nonce:nonce, 
			quest_id : quest_id,
			adventure_id : adventure_id
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}


///////////////////////// break parent  //////////////////

function breakParent(id,type){
	let nonce = $("#break-parent-nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	showLoader('small');
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'breakParent', type:type, id:id, adventure_id:adventure_id, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}



/////////////////////// REMOVE FROM LIBRARY /////////////////////////

function removeFromLibrary(id=0,type){
	showLoader();
	
	let lib_id = $("#lib_id").val();
	let nonce = $("#remove_nonce").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'removeFromLibrary', 
			nonce:nonce, 
			type:type, 
			id : id,
			lib_id : lib_id
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}


/////////////////////// DUPLICATE ROW /////////////////////////

function duplicateRow(id, adventure_id = $("#the_adventure_id").val(), type = $("#row_type").val()){
	showLoader('small');
	let nonce = $("#duplicator_nonce").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'duplicateRow', 
			nonce:nonce, 
			adventure_id : adventure_id,
			type : type,
			id : id
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);

		}
	});
}
/////////////////////// DUPLICATE QUESTS /////////////////////////

function duplicateQuests(){
	let duplicates =[];
	let achievement_duplicates =[];
	let item_duplicates =[];
	let enc_duplicates =[];
	let speakers_duplicates =[];
	
	$('ul#quests-to-duplicate li.active.to-duplicate').each(function(index, element) {
		duplicates.push($('input.reqs-id',this).val());
	});
	$('ul#achievements-to-duplicate li.active.to-duplicate').each(function(index, element) {
		achievement_duplicates.push($('input.reqs-id',this).val());
	});
	$('ul#items-to-duplicate li.active.to-duplicate').each(function(index, element) {
		item_duplicates.push($('input.reqs-id',this).val());
	});
	$('ul#encounters-to-duplicate li.active.to-duplicate').each(function(index, element) {
		enc_duplicates.push($('input.reqs-id',this).val());
	});
	$('ul#speakers-to-duplicate li.active.to-duplicate').each(function(index, element) {
		speakers_duplicates.push($('input.reqs-id',this).val());
	});
	showLoader();
	let nonce = $("#duplicator_nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	let adventure_target = $("#adventure_target").val();
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'duplicateQuests', 
			nonce:nonce, 
			adventure_id : adventure_id,
			adventure_target : adventure_target,
			duplicates : duplicates,
			achievement_duplicates : achievement_duplicates,
			item_duplicates : item_duplicates,
			enc_duplicates : enc_duplicates
		}),
		method: "POST",
		success: function(json_text) {
			displayAjaxResponse(json_text);
		}
	});
}
function createChildAdventure(adventure_id = null){
	
	if(adventure_id){
		showLoader();
		let nonce = $("#template_duplicator_nonce").val();
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'createChildAdventure', 
				nonce:nonce, 
				adventure_id : adventure_id,
			}),
			method: "POST",
			success: function(json_text) {
				displayAjaxResponse(json_text);
			}
		});
	}else{
		return false;
	}
}
/////////////////////// BULK CREATE /////////////////////////

function bulkCreate(){
	let achievements = parseInt($("#bulk-achievements").val());
	showLoader();
	let nonce = $("#bulk_nonce").val();
	let adventure_id = $("#the_adventure_id").val();
	
	let starting_at = 0;
	
	for(let i=0; i<achievements; i++){
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'bulkCreate', 
				nonce:nonce, 
				adventure_id : adventure_id,
				achievements : achievements,
			}),
			method: "POST",
			success: function(json_text) {
				displayAjaxResponse(json_text);
			}
		});
		
	}
	
	
}

function makeid(possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", max_length=5){
    let text = "";
    for(let i=0; i < max_length; i++ ){
		text += possible.charAt(Math.floor(Math.random() * possible.length));
	}
    return text;
}

function createMagicCode(who=""){
	let magicCode = makeid("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",20);
	if(!who){
		$('#the_achievement_code').val(magicCode);
		let magicLink = $('#site-url').val() + magicCode+'&adv='+$("#the_adventure_id").val();
		$('#the_magic_link').val(magicLink);
	}else{
		$('#the_magic_code-'+who).val(magicCode);
		setMagicCode(who);
	}
}
function revertMagicCode(who, magic_code){
	$('#the_magic_code-'+who).val(magic_code);
	setMagicCode(who);
}

function updateMagicCode(){
	if($('#the_achievement_code').val() != ""){
	let magicLink = $('#site-url').val() + $('#the_achievement_code').val();
	$('#the_magic_link').val(magicLink);
	}else{
		clearMagicCode();
	}
}
function clearMagicCode(){
	$('#the_achievement_code, #the_magic_link').val("");
}

function addTableRow(table_id){
	let unique_id = makeid();
	$(table_id+" tbody tr:last-child").clone().appendTo(table_id+" tbody").attr('id','row-'+unique_id);
	$(table_id+" tbody tr:last-child td button.remove-row").attr('onClick', "removeTableRow('#row-"+unique_id+"');");
	$(table_id+" tbody tr:last-child td input").val('');
	$(table_id+" tbody tr:last-child td select").val(0);
}  

function removeTableRow(id){
	$(id).remove();
}  



function maxLevel(who){
	if($(who).val() > 99){
		$(who).val(99);
	}else if($(who).val() <0){
		$(who).val(0);
	}
}

function hideAchievementReward(){
	$("#the_mech_achievement_reward li").show().removeClass('active');
	let id = $("#the_achievement_id").val();
	if(id > 0){
		$('#achievement-reward-'+id).hide();
	}
}


function checkPublishFor(){
	$("#the_achievement_id option").show();
	let id = $("#the_mech_achievement_reward li.active .achievement-reward-id").val();
	if(id>0){
		if($("#the_achievement_id").val() == id){
			$("#the_achievement_id").val(0);
		}
		$("#the_achievement_id option").show();
		$('#achievement-option-'+id).hide();
	}
}

function toggleReq(who){
	$(who).toggleClass("active");
	if($('#the_quest_type').val() == "mission"){
		let min = 1;
		$("ul.select-multiple li.active").each(function(){
			if($('.reqs-level',this).val() > min){
				min = $('.reqs-level',this).val();
			}
		});
		$("#the_quest_level").val(min);
	}
}


function toggleSingleReq(who){
	$(who).siblings().removeClass('active');
	$(who).toggleClass('active');
}

function selectMultiple(who){
	$(who).toggleClass("active");
}
function activateAll(who){
	$("#all-on").addClass('hidden');
	$("#all-off").removeClass('hidden');
	$(who).addClass("active");
}
function deactivateAll(who){
	$("#all-off").addClass('hidden');
	$("#all-on").removeClass('hidden');
	$(who).removeClass("active");
}
function activateAllPlayerType(who){
	$("#all-on").addClass('hidden');
	$("#all-off").removeClass('hidden');
	$("ul.player-select li").removeClass('active');
	$("ul.player-select li."+who).addClass("active");
}

function setPlayerAdventureRole(adventure_id, player_id, role='player'){
	showLoader('small');
	let nonce = $("#player-status-nonce").val();
	let who = $('#player-'+player_id);
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'setPlayerAdventureRole', adventure_id:adventure_id, player_id:player_id, role:role, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}

function updatePlayerAdventureStatus(adventure_id, player_id, status){
	showLoader('small');
	let nonce = $("#player-status-nonce").val();
	let who = $('#player-'+player_id);
	
	jQuery.ajax({  
		url: runAJAX.ajaxurl,
		data: ({action: 'updatePlayerAdventureStatus', adventure_id:adventure_id, player_id:player_id, status:status, nonce:nonce}),
		method: "POST",
		success: function(data_received) {
			displayAjaxResponse(data_received);
		}
	});
}


function testCheckedBoxes(){
	let selected = 0;
	$('.select-element:checked').each(function( index ) {
		selected++;
	});
}
function selectAllCheckBoxes(){
	const selectAllCheckbox = document.getElementById("select-all");
	if(selectAllCheckbox){
		const userCheckboxes = document.querySelectorAll(".select-element");
		selectAllCheckbox.addEventListener("change", function () {
			userCheckboxes.forEach((checkbox) => {
				checkbox.checked = selectAllCheckbox.checked;
			});
			testCheckedBoxes();
		});
		userCheckboxes.forEach((checkbox) => {
			checkbox.addEventListener("change", function () {
				selectAllCheckbox.checked = Array.from(userCheckboxes).every((cb) => cb.checked);
				testCheckedBoxes();
			});
		});
	}
}


//////////////////  EXPORT PLAYERS WORK ////////////////
function exportPlayersWork(){
    const headers = [];
    $('#players-table-header .header-row .cell').each(function () {
		headers.push($('.cell-text-value',this).val());
    });
    const rows = [headers]; // Build your dynamic array of rows

    $('.player-row').each(function () {
        const row = [];
        $(this).find('.cell').each(function () {
			row.push($('.cell-text-value',this).val());
        });
        rows.push(row);
    });
    $.ajax({
        url: runAJAX.ajaxurl,
        method: 'POST',
        data: {
            action: 'exportPlayersWork',
            data: JSON.stringify(rows)
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'player_export.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
        }
    });
	
}
//////////////////  DISPLAY AJAX RESPONSE ////////////////

function displayAjaxResponse(json_data){
	let data = JSON.parse(json_data);
	$('.loader, .small-loader').removeClass('active');
	if(data.rating){
		$(".rating button").removeClass('amber-bg-400');
		for(let r=0;r<data.rating;r++){
			$(".rating button#rating-star-"+(r+1)).addClass('amber-bg-400');
		}
	}
	if(data.debug){
		console.log(data.debug);
	}
	if(data.role_update){
		$("tr#player-row-"+data.player_id).fadeOut('fast',function(){
			let adventure_id = $('#the_adventure_id').val();
			$('button.role-button-npc',this).attr('onclick',"setPlayerAdventureRole("+adventure_id+","+data.player_id+",'npc');");
			$('button.role-button-player',this).attr('onclick',"setPlayerAdventureRole("+adventure_id+","+data.player_id+",'player');");
			$('button.role-button-gm',this).attr('onclick',"showOverlay('#confirm-gm-"+data.player_id+"');");

			$('button.role-button-'+data.role_update,this).removeAttr('onclick');
			$(this).removeClass('role-gm role-player role-npc').addClass('role-'+data.role_update).fadeIn('fast');
		});
	}
	
	if(data.org_role_update){
		$("tr#player-org-row-"+data.player_id).fadeOut('fast',function(){
			$('button.role-button-npc',this).attr('onclick',"setPlayerAdventureRole("+adventure_id+","+data.player_id+",'npc');");
			$('button.role-button-player',this).attr('onclick',"setPlayerAdventureRole("+adventure_id+","+data.player_id+",'player');");
			$('button.role-button-gm',this).attr('onclick',"showOverlay('#confirm-gm-"+data.player_id+"');");

			$('button.role-button-'+data.role_update,this).removeAttr('onclick');
			$(this).removeClass('role-gm role-player role-npc').addClass('role-'+data.role_update).fadeIn('fast');
		});
	}
	
	if(data.duplicate){
		$(data.original).clone().appendTo(data.container).attr('id',data.duplicate);
		
		$("#"+data.duplicate+" ."+data.type+"-id").val(data.clone_id);
		
		$("#"+data.duplicate+" .row-title").attr({
			'id':"the_title-"+data.type+"-"+data.clone_id,
			'onChange':"setTitle("+data.clone_id+",'"+data.type+"');"
		});
		$("#"+data.duplicate+" .the-xp").attr({
			'onChange':"setXP("+data.clone_id+",'"+data.type+"');"
		});
		$("#"+data.duplicate+" .the-bloo").attr({
			'onChange':"setBLOO("+data.clone_id+",'"+data.type+"');"
		});
		$("#"+data.duplicate+" .magic-code").attr({
			'onChange':"setMagicCode("+data.clone_id+",'"+data.type+"');"
		});
		$("#"+data.duplicate+" .the-deadline").attr({
			'onChange':"setDeadline("+data.clone_id+",'"+data.type+"');"
		});
		$("#"+data.duplicate+" .the-start-date").attr({
			'onChange':"setStartDate("+data.clone_id+",'"+data.type+"');"
		});
		let bloginfo_url = $("#bloginfo_url").val();
		let adventure_id = $("#the_adventure_id").val();
		
		
		$("#"+data.duplicate+" .duplicate-button").attr({ 'onClick':"showOverlay('#confirm-duplicate-"+data.clone_id+"');" });
		$("#"+data.duplicate+" .duplicate-confirm").attr({ 'id':"confirm-duplicate-"+data.clone_id });
		$("#"+data.duplicate+" .duplicate-confirm-button").attr({ 'onClick':"duplicateRow("+data.clone_id+");" });
		
		$("#"+data.duplicate+" .edit-button").attr({ 'href':bloginfo_url+"/new-"+data.type+"/?adventure_id="+adventure_id+"&"+data.type+"_id="+data.clone_id });
		
		$("#"+data.duplicate+" .draft-button").attr({ 'onClick':"showOverlay('#confirm-draft-"+data.clone_id+"');" });
		$("#"+data.duplicate+" .draft-confirm").attr({ 'id':"confirm-draft-"+data.clone_id });
		$("#"+data.duplicate+" .draft-confirm-button").attr({ 'onClick':"confirmStatus("+data.clone_id+",'"+data.type+"','draft');" });

		$("#"+data.duplicate+" .trash-button").attr({ 'onClick':"showOverlay('#confirm-trash-"+data.clone_id+"');" });
		$("#"+data.duplicate+" .trash-confirm").attr({ 'id':"confirm-trash-"+data.clone_id });
		$("#"+data.duplicate+" .trash-confirm-button").attr({ 'onClick':"confirmStatus("+data.clone_id+",'"+data.type+"','trash');" });
		//alert(data.original);
	}
	
	if(data.player_adventure_status){
		$("tr#player-row-"+data.player_id).fadeOut('fast',function(){
			$(this).remove();
		});
	}
	if(data.file){
		$("#create-zip").remove();
		$("#download-zip").removeClass('hidden').attr('href',data.file);
	}
	if(data.levelup){
		if(data.achievement_id){
			$("#level-up .content").html(data.levelupContent).hide();
			$("#level-up").addClass('active');
			$("#level-up .level-up-bg").delay(500).fadeIn('fast', function(){
				loadAchievementCard(data.achievement_id,1);
			});
			$("#level-up").click(function(){
				$("#level-up").unbind('click');
				hideAllOverlay();unloadCard();
			});
		}else{
			$("#level-up .content").html(data.levelupContent).hide();
			$("#level-up .achievement-image").attr('style','background-image:url('+data.levelupBG+');').hide();
			$("#level-up").addClass('active');
			$("#level-up .level-up-bg").delay(500).fadeIn('fast', function(){
				$("#level-up .content").fadeIn(500).delay(15000).fadeOut(1,function(){
					hideAllOverlay();
				});
			});
			$("#level-up").click(function(){
				$("#level-up").unbind('click');
				hideAllOverlay();
			});
			
		}
	}
	if(data.content && data.content_target){
		$(data.content_target).append(data.content);
	}
	if(data.remove_element){
		$(data.remove_element).fadeOut('fast',function(){
			$(data.remove_element).remove();
		});
	}
	if(data.remove_step){
		$('#step-'+data.step_id).fadeOut(300,function(){
			$(this).remove();
		});
	}

	if(data.removed_step_button){
		$("#step-button-"+data.button).fadeOut(300,function(){
			$(this).remove();
		});
	}
	if(data.messages){
		let message_delay = 1000;
		if(data.message_delay){
			message_delay = data.message_delay;
		}
		for(let i=0; i<data.messages.length; i++){
			$("#notify-message ul.content").append(data.messages[i]);
			$("#notify-message ul.content li:last-child").delay(300).addClass('active').delay(message_delay).removeClass('active', function(){
				$(this).remove();
			});
		}
	}

	if(data.message){
		let message_delay = 1000;
		if(data.message_delay){
			message_delay = data.message_delay;
		}
		if(data.just_notify){
			$("#notify-message ul.content").append(data.message);
			setTimeout ( function (){
				$("#notify-message ul.content li:last-child").addClass('active');
				let last_message = $("#notify-message ul.content li:last-child");
				setTimeout(function (){
					last_message.removeClass('active');
					setTimeout(function (){
						last_message.remove();
						if(data.reload){
							document.location.reload();
						}
					},300);

				}, message_delay); 
			},1);
		}else{
			$("#feedback .content").html(data.message);
			$("#feedback").addClass('active');
			if(data.autofade){
				$("#feedback").unbind('click');
				hideAllOverlay();
			}
			if(!data.noClose){
				if(data.location){		
					$("#feedback").click(function(){
						if(data.location == 'reload'){
							document.location.reload();
						}else{
							document.location.href=data.location;
						}
					});
				}else{
					$("#feedback").click(function(){
						$("#feedback").unbind('click');
						hideAllOverlay();
					});
				}
			}
		}
	}
	if(data.sale == true){
		$('.hud-screen-video').removeClass('active');
		$('.hud-screen-content').removeClass('active');
		$('#hud-video-status-sale').addClass('active');
		$('#hud-video-status-sale').get(0).pause();
		$('#hud-video-status-sale').get(0).play();
		
		let shopkeeperSaleTimeout = setTimeout(function(){
			$('#hud-video-status-sale').removeClass('active');
			$('#hud-video-status-idle').addClass('active');
			$('#hud-video-status-idle').get(0).pause();
			$('#hud-video-status-idle').get(0).play();
		},5100);
	}
	if(data.update_ux){
		if(data.update_ux.player_picture){
			$('#profile-box-btn, #status-animated-chart, .player-picture').css('background-image','url('+data.update_ux.player_picture+')');
		}
		if(data.update_ux.nickname){
			$('#status-player-display-name, .player-nickname').text(data.update_ux.nickname);
		}
	}
	if(data.jumpToNext){
		if(data.jumpToNext=='last'){
			submitPlayerWork();
		}else{
			document.location.href=`#step-${data.jumpToNext}`;
		}
	}
	if(data.question_updated){
		$("#accordion-tab-question-"+data.question_id+" .question-text").html(data.question_updated);
	}
	
	if(data.loadContent){
		$('#small-loader').addClass('active');
		$(data.loadContent.element).html('');
		jQuery.ajax({  
			url: runAJAX.ajaxurl,
			data: ({action: 'loadContent', content:data.loadContent.file, id:data.loadContent.id}),
			method: "POST",
			success: function(data_received) {
				$(data.loadContent.element).html(data_received);
				let flipTimeout = setTimeout(function (){
					$('#small-loader').removeClass('active');
				},500);
			}
		});
		
	}
	
	
	if(data.new_grade_nonce){
		$("#grade_nonce").val(data.new_grade_nonce);
	}
	if(data.new_dimensions_nonce){
		$("#dimensions_nonce").val(data.new_dimensions_nonce);
	}
	if(data.new_bloo_nonce){
		$("#bloo-nonce").val(data.new_bloo_nonce);
	}
	if(data.new_max_players_nonce){
		$("#max-players-nonce").val(data.new_max_players_nonce);
	}
	if(data.new_xp_nonce){
		$("#xp-nonce").val(data.new_xp_nonce);
	}
}
let zoomLevel = 0;

function toggleJourneyView(){
	$('#the-journey').toggleClass('journey-map journey-board');
	zoomLevel = 0;
	if(($('#the-journey').hasClass('journey-map'))){
		resizeJourneyMapWithPadding();
		centerJourneyMap();
	}else{
		zoomLevel = 0;
	}
}

function applyZoom() {
	if (zoomLevel > 400) { 
		zoomLevel = 400;
	} else if (zoomLevel < -1000) {
		zoomLevel = -1000;
	}
	let transformValue = `translateZ(${zoomLevel}px)`;

	if(zoomLevel >= 100){
		transformValue = `translateZ(${zoomLevel}px) translateX(150px)`;
	}

	let $map = $('#the-journey');
	$map.css('transform', transformValue);
	resizeJourneyMapWithPadding(-zoomLevel);
}

function resizeJourneyMapWithPadding(padding = 1300, map='the-journey', milestoneContainer='.milestone-container') {
	let $map = $('#' + map);

	if(!$map.attr('data-width')){
		let maxX = 0, maxY = 0;

		$(milestoneContainer).each(function () {
			let x = $(this).position().left;
			let y = $(this).position().top;

			if (x > maxX) maxX = x;
			if (y > maxY) maxY = y;
		});
		// Set width/height of the map to fit all children + padding
		$map.css({
			width: (maxX + padding) + 'px',
			height: (maxY + padding) + 'px'
		});
		$map.attr('data-width', maxX);
		$map.attr('data-height', maxY);
	}else{
		let currentWidth = parseInt($map.attr('data-width'))+300;
		let currentHeight = parseInt($map.attr('data-height'))+300;
		$map.css({
			width: currentWidth + 'px',
			height: currentHeight + 'px'
		});
	}
	console.log($map.css('width'), $map.css('height'));
}
function centerJourneyMap() {
	let $container = $('.journey-container');
	let $map = $('#the-journey');

	let containerWidth = $container.width();
	let containerHeight = $container.height();

	let mapWidth = $map.outerWidth();
	let mapHeight = $map.outerHeight();

	let scrollLeft = (mapWidth - containerWidth) / 2;
	let scrollTop = (mapHeight - containerHeight) / 2;

	$container.animate({
		scrollTop: scrollTop,
		scrollLeft: scrollLeft
	}, 400);
}



document.addEventListener('DOMContentLoaded', function () {

	if($('.datetimepicker').length > 0){
		$('.datepicker').datetimepicker({
			format:"Y-m-d",
			timepicker:false
		});
		$('.datetimepicker, .the_start_date, .deadline, .the_deadline').datetimepicker({
			format:"Y/m/d H:i"
		});
	}

	$(".sortable").sortable({
		items: "tr:not(.unsortable), li:not(.unsortable), div:not(.unsortable)",
		update: function(event, ui) {
		
		}
	});
	$(".sortable").disableSelection();
	$(".sortable-row-container").sortable({
		items: ".row-container",
		update: function(event, ui) {
		
		}
	});
	$(".sortable-with-handle").disableSelection();
	
	$("ul.select-single li").click(function(){
		if(!$(this).hasClass('label')){
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
		}
	});
	$('#the_achievement_code, input.achievement-code').keypress(function (e) {
		let regex = new RegExp("^[a-zA-Z 0-9]");
		let str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		if (regex.test(str)) {
			return true;
		}
		e.preventDefault();
		return false;
	});
	if(hash_change_type=='quest'){
		jumpToStepByHash();
		window.addEventListener("hashchange", jumpToStepByHash);
	}
	if(hash_change_type=='survey'){
		jumpToQuestionByHash();
		window.addEventListener("hashchange", jumpToQuestionByHash);
	}
});

$(document).keyup(function(e) {
  if (e.keyCode === 27){
	  hideAllOverlay();
	  loadSidebar();
	  unloadCard();
  }  // esc
});


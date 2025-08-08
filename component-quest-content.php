						<div class="highlight padding-10 grey-bg-200">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40 red-bg-400"><span class="icon icon-quest"></span></span>
								<span class="icon-content">
									<span class="line font _24 grey-800"><?php _e("Content","bluerabbit"); ?></span>
								</span>
							</span>
						</div>
						<table class="table w-full" cellpadding="0">
							<thead>
								<tr class="font _12 grey-600">
									<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
									<td><?php _e('Value','bluerabbit'); ?></td>
								</tr>
							</thead>
							<tbody class="font _16">
								<tr>
									<td class="text-right v-top">
										<span class="font _16 block"><?= __("Short Description","bluerabbit");?></span>
										<span class="font _12 block grey-500">
											<?php _e("Players will read this in the preview when clicking in the journey","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<textarea class="form-ui grey-bg-50 border border-all blue-border-700 border-2" rows="3" maxlength="200" id="the_quest_secondary_headline"><?= isset($quest) ? $quest->quest_secondary_headline : ""; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="text-right v-top">
										<span class="font _16 block"><?= __("Instructions","bluerabbit");?></span>
										<span class="font _12 block grey-500">
											<?php _e("Describe what the players must do to earn the reward","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<?php 
										if($roles[0]=="administrator"){
											$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>350);
										}else{
											$wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>350);
										}
										if(isset($quest)){
											wp_editor( $quest->quest_content, 'the_quest_content',$wp_editor_settings); 	
										}else{
											wp_editor('','the_quest_content',$wp_editor_settings); 	
										}
										?>
									</td>
								</tr>
								<tr>
									<td class="text-right v-top">
										<span class="font _16 block"><?= __("Success Message","bluerabbit");?></span>
										<span class="font _12 block grey-500">
											<?php _e("Reward your players with information after completing this quest","bluerabbit"); ?>
										</span>
									</td>
									<td>
										<?php 
										if(isset($quest)){
											wp_editor( $quest->quest_success_message, 'the_quest_success_message',$wp_editor_settings); 	
										}else{
											wp_editor('','the_quest_success_message',$wp_editor_settings); 	
										}
										?>
									</td>
								</tr>
							</tbody>
						</table>

				<div class="highlight padding-10 grey-bg-200">
					<span class="icon-group">
						<span class="icon-button font _24 sq-40 blue-grey-bg-400"><span class="icon icon-config"></span></span>
						<span class="icon-content">
							<span class="line font _24 grey-800"><?php _e("Mechanics","bluerabbit"); ?></span>
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
						<?php if($quest->quest_parent != 0){ ?>
						<tr>
							<td class="text-right w-150"><?php _e('Quest Parent','bluerabbit'); ?></td>
							<td>
								<?php $quest_parent = getQuest($quest->quest_parent); ?>
								<h1><?= $quest_parent->quest_title; ?></h1>
								<h3><?= __("Adventure","bluerabbit");?>: <?= $quest_parent->adventure_title; ?></h3>
								<?php $the_link = get_bloginfo('url')."/enroll/?enroll_code=$quest_parent->adventure_code"; ?>
								<a href="<?= $the_link  ?>"><?= $the_link; ?></a>
							</td>
						</tr>
						
						<?php } ?>
						<tr>
							<td class="text-right w-150"><?php _e('Level','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<label class="light-blue-bg-800 font w900"><span class="icon icon-level"></span></label>
	<input class="number form-ui" type="number" max="99" min="1" id="the_quest_level" value="<?= isset($quest->mech_level) ? $quest->mech_level : 1 ; ?>" onBlur="checkLevel('#the_quest_level');" onChange="checkLevel('#the_quest_level');">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('XP','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<label class="light-blue-bg-800 font w900"><span class="icon icon-star"></span></label>
									<input class="number form-ui" type="number" min="0" id="the_quest_xp" value="<?= isset($quest->mech_xp) ? $quest->mech_xp : 1 ; ?>">
								</div>
							</td>
						</tr>
						<?php if(isset($use_encounters)){ ?>
							<tr>
								<td class="text-right w-150"><?php _e('EP','bluerabbit'); ?></td>
								<td>
									<div class="input-group w-full">
										<label class="light-blue-bg-800 font w900"><span class="icon icon-activity"></span></label>
										<input class="number form-ui" type="number" min="0" id="the_quest_ep" value="<?= isset($quest->mech_ep) ? $quest->mech_ep : 1 ; ?>">
									</div>
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="text-right w-150"><?php _e('BLOO','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<label class="light-blue-bg-800 font w900"><span class="icon icon-bloo"></span></label>
									<input class="number form-ui" type="number" min="0" id="the_quest_bloo" value="<?= isset($quest->mech_bloo) ? $quest->mech_bloo : 1 ; ?>">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Start Date','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php
									if(isset($quest) && $quest->mech_start_date != "0000-00-00 00:00:00" && $quest->mech_deadline != NULL){ 
										$pretty_start_date = date('Y/m/d H:i', strtotime($quest->mech_start_date));
									}else{
										$pretty_start_date = '';
									}
									?>
									<label class="cyan-bg-400 font w900"><span class="icon icon-calendar"></span></label>
									<input class="form-ui text-center font w600 the_start_date"  autocomplete="off" id="the_quest_start_date" value="<?= $pretty_start_date; ?>">
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-right w-150"><?php _e('Deadline','bluerabbit'); ?></td>
							<td>
								<div class="input-group w-full">
									<?php
									if(isset($quest) && $quest->mech_deadline != "0000-00-00 00:00:00" && $quest->mech_deadline != NULL){ 
										$pretty_deadline = date('Y/m/d H:i', strtotime($quest->mech_deadline));
									}else{
										$pretty_deadline = '';
									}
									?>
									<label class="red-bg-800 font w900"><span class="icon icon-calendar"></span></label>
									<input class="form-ui text-center font w600 the_deadline"  autocomplete="off" id="the_quest_deadline" value="<?= $pretty_deadline; ?>">
								</div>
							</td>
						</tr>
					</tbody>
				</table>

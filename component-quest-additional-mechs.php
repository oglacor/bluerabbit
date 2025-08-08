					<div class="highlight padding-10 grey-bg-100">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  grey-bg-400">
								<span class="icon icon-config"></span>
							</span>
							<span class="icon-content font _24">
								<span class="line grey-400"><?php _e("Additional Mechanics","bluerabbit"); ?></span>
								<span class="line font _14 grey-500"><?php _e("These are completely optional","bluerabbit"); ?></span>
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
								<td class="text-right w-150"><?php _e('Deadline Cost','bluerabbit'); ?></td>
								<td>
									<div class="input-group w-full">
										<label class="red-bg-400 font w900"><span class="icon icon-deadline"></span></label>
										<input class="number form-ui" type="number"  id="the_quest_deadline_cost" value="<?= isset($quest) ? $quest->mech_deadline_cost : ""; ?>">
									</div>
								</td>
							</tr>
							<tr>
								<td class="text-right w-150"><?php _e('Unlock Cost','bluerabbit'); ?></td>
								<td>
									<div class="input-group w-full">
										<label class="cyan-bg-400 font w900"><span class="icon icon-lock"></span></label>
										<input class="number form-ui" type="number"  id="the_quest_unlock_cost" value="<?= isset($quest) ? $quest->mech_unlock_cost : ""; ?>">
									</div>
								</td>
							</tr>
						</tbody>
					</table>

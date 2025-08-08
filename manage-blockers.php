<?php $blockers = getBlockers($adventure->adventure_id); ?>
			<div class="highlight padding-10 teal-bg-50">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  teal-bg-300"><span class="icon icon-lock"></span></span>
					<span class="icon-content">
						<span class="line font _24 grey-800"><?php _e('Blockers','bluerabbit'); ?></span>
					</span>
				</span>
				<div class="highlight-cell pull-right padding-10">
					<div class="search sticky">
						<div class="input-group">
							<input type="text" class="form-ui" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
							<label>
								<span class="icon icon-search"></span>
							</label>
							<script>
								$('#search').keyup(function(){
									var valThis = $(this).val().toLowerCase();
									if(valThis == ""){
										$('table#table-blog tbody > tr').show();           
									}else{
										$('table#table-blog tbody > tr').each(function(){
											var text = $(this).text().toLowerCase();
											(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
										});
									};
								});
							</script>				
						</div>
					</div>
				</div>
			</div>
			<?php if(isset($blockers['publish'])){ ?>
				<div class="content">
					<table class="table small" id="table-session">
						<thead>
							<tr>
								<td width="70%"><?php echo __("Date","bluerabbit"); ?></td>
								<td width="15%"><?php echo __("# Blocked","bluerabbit"); ?></td>
								<td width="5%"><span class="icon icon-bloo"></span></td>
								<td width="5%"><span class="icon icon-edit"></span></td>
								<td width="5%"><span class="icon icon-trash"></span></td>
							</tr>
						</thead>
						<tbody class="">
							<?php foreach($blockers['publish'] as $key=>$t){ ?>
								<tr class="quest-item blocker" id="blocker-<?php echo $t->blocker_id;?>">
									<td>
										<?php echo date('D, F jS, Y', strtotime($t->blocker_date)); ?>
										<input type="hidden" class="blocker-id" value="<?php echo $t->blocker_id; ?>">
									</td>
									<td><?= $t->total_players; ?></td>
									<td><?= $t->blocker_cost; ?></td>
									<td>
										<a href="<?php echo get_bloginfo('url')."/new-blocker/?adventure_id=$adventure->adventure_id&blockerID=$t->blocker_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit"></span></a>
									</td>
									<td>
										<button class="icon-button font _24 sq-40  icon-sm red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?php echo $t->blocker_id; ?>');">
											<span class="icon icon-trash"></span>
											<span class="tool-tip bottom">
												<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
											</span>
										</button>
										<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $t->blocker_id; ?>">
											<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','trash');">
												<span class="icon-group">
													<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
														<span class="icon icon-trash white-color"></span>
													</span>
													<span class="icon-content">
														<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
													</span>
												</span>
											</button>
											<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
												<span class="icon icon-cancel white-color"></span>
											</button>
										</div>

									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php }else{ ?> 
				<div class="highlight padding-10 indigo-bg-50">
					<span class="icon-group text-center">
						<span class="icon-content">
							<span class="icon icon-cancel"></span> <?php _e("No blockers found","bluerabbit"); ?>
						</span>
					</span>
				</div>
			<?php } ?>
			<?php if(isset($blockers['trash'])){ ?>
				<div class="highlight padding-10 red-bg-50">
					<span class="icon-group text-center">
						<span class="icon-content font _24">
							<span class="icon icon-delete"></span> <?php _e("Trashed Blockers","bluerabbit"); ?>
						</span>
					</span>
				</div>
				<div class="content">
					<table class="table small" id="table-session">
						<thead>
							<tr>
								<td width="65%"><?php echo __("Date","bluerabbit"); ?></td>
								<td width="15%"><?php echo __("# Blocked","bluerabbit"); ?></td>
								<td width="5%"><span class="icon icon-bloo"></span></td>
								<td width="5%"><span class="icon icon-edit"></span></td>
								<td width="5%"><span class="icon icon-restore"></span></td>
								<td width="5%"><span class="icon icon-delete"></span></td>
							</tr>
						</thead>
						<tbody class="">
							<?php foreach($blockers['trash'] as $key=>$t){ ?>
								<tr class="quest-item blocker" id="blocker-<?php echo $t->blocker_id;?>">
									<td>
										<?php echo date('D, F jS, Y', strtotime($t->blocker_date)); ?>
										<input type="hidden" class="blocker-id" value="<?php echo $t->blocker_id; ?>">
									</td>
									<td><?= $t->total_players; ?></td>
									<td><?= $t->blocker_cost; ?></td>
									<td>
										<a href="<?php echo get_bloginfo('url')."/new-blocker/?adventure_id=$adventure->adventure_id&blockerID=$t->blocker_id";?>" class="icon-button font _24 sq-40  green-bg-400 icon-sm"><span class="icon icon-edit"></span></a>
									</td>
									<td>
										<button class="icon-button font _24 sq-40  icon-sm blue-bg-200 white-color restore-button" onClick="showOverlay('#confirm-restore-<?php echo $t->blocker_id; ?>');">
											<span class="icon icon-restore"></span>
											<span class="tool-tip bottom">
												<span class="tool-tip-text font _12"><?php _e("Restore","bluerabbit"); ?></span>
											</span>
										</button>
										<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?php echo $t->blocker_id; ?>">
											<button class="form-ui white-bg restore-confirm-button" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','publish');">
												<span class="icon-group">
													<span class="icon-button font _24 sq-40  blue-bg-A400 icon-sm">
														<span class="icon icon-restore white-color"></span>
													</span>
													<span class="icon-content">
														<span class="line blue-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
													</span>
												</span>
											</button>
											<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
												<span class="icon icon-cancel white-color"></span>
											</button>
										</div>
									</td>
									<td>
										<button class="icon-button font _24 sq-40  icon-sm red-bg-200 white-color delete-button" onClick="showOverlay('#confirm-delete-<?php echo $t->blocker_id; ?>');">
											<span class="icon icon-delete"></span>
											<span class="tool-tip bottom">
												<span class="tool-tip-text font _12"><?php _e("Delete","bluerabbit"); ?></span>
											</span>
										</button>
										<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?php echo $t->blocker_id; ?>">
											<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','delete');">
												<span class="icon-group">
													<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
														<span class="icon icon-delete white-color"></span>
													</span>
													<span class="icon-content">
														<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
													</span>
												</span>
											</button>
											<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
												<span class="icon icon-cancel white-color"></span>
											</button>
										</div>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php }else{ ?> 
				<div class="highlight padding-10 red-bg-50">
					<span class="icon-group text-center">
						<span class="icon-content">
							<span class="icon icon-delete"></span> <?php _e("No blockers found in trash","bluerabbit"); ?>
						</span>
					</span>
				</div>
			<?php } ?>

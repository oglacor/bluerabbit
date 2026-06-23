<?php $blockers = BR_Blocker::instance()->getBlockers($adventure->adventure_id); ?>

<div class="br-journey-manager">
<!-- Blockers Header -->
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-lock"></span>
		<?php _e('Blockers','bluerabbit'); ?>
	</div>

	<div class="br-toolbar">
		<div class="br-search">
			<span class="icon icon-search"></span>
			<input type="text" id="search" placeholder="<?php _e("Search","bluerabbit"); ?>">
		</div>
		<script>
			$('#search').keyup(function(){
				var valThis = $(this).val().toLowerCase();
				if(valThis == ""){
					$('table#table-blocker tbody > tr').show();
				}else{
					$('table#table-blocker tbody > tr').each(function(){
						var text = $(this).text().toLowerCase();
						(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
					});
				};
			});
		</script>
	</div>

	<!-- Published Blockers -->
	<?php if(isset($blockers['publish'])){ ?>
	<table class="br-table" id="table-blocker">
		<thead>
			<tr>
				<th width="70%"><?php echo __("Date","bluerabbit"); ?></th>
				<th width="15%"><?php echo __("# Blocked","bluerabbit"); ?></th>
				<th width="5%"><span class="icon icon-bloo"></span></th>
				<th width="5%"><span class="icon icon-edit"></span></th>
				<th width="5%"><span class="icon icon-trash"></span></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($blockers['publish'] as $key=>$t){ ?>
				<tr class="quest-item blocker" id="blocker-<?php echo $t->blocker_id;?>">
					<td>
						<?php echo date('D, F jS, Y', strtotime($t->blocker_date)); ?>
						<input type="hidden" class="blocker-id" value="<?php echo $t->blocker_id; ?>">
					</td>
					<td><?= $t->total_players; ?></td>
					<td><?= $t->blocker_cost; ?></td>
					<td>
						<a href="<?php echo get_bloginfo('url')."/new-blocker/?adventure_id=$adventure->adventure_id&blockerID=$t->blocker_id";?>" class="br-btn br-btn-green"><span class="icon icon-edit"></span></a>
					</td>
					<td>
						<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-trash-<?php echo $t->blocker_id; ?>');">
							<span class="icon icon-trash"></span>
						</button>
						<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $t->blocker_id; ?>">
							<div class="br-confirm">
								<button class="br-btn red" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','trash');">
									<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</div>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table></div><!-- /.br-section-body -->
	<?php }else{ ?>
		<div class="br-empty">
			<span class="icon icon-lock"></span>
			<h3><?php _e("No blockers found","bluerabbit"); ?></h3>
		</div>
	<?php } ?>
</div>

<!-- Trashed Blockers -->
<?php if(isset($blockers['trash'])){ ?>
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-trash"></span>
		<?php _e('Trashed Blockers','bluerabbit'); ?>
	</div>

	<table class="br-table" id="table-blocker-trash">
		<thead>
			<tr>
				<th width="65%"><?php echo __("Date","bluerabbit"); ?></th>
				<th width="15%"><?php echo __("# Blocked","bluerabbit"); ?></th>
				<th width="5%"><span class="icon icon-bloo"></span></th>
				<th width="5%"><span class="icon icon-edit"></span></th>
				<th width="5%"><span class="icon icon-restore"></span></th>
				<th width="5%"><span class="icon icon-delete"></span></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($blockers['trash'] as $key=>$t){ ?>
				<tr class="quest-item blocker" id="blocker-<?php echo $t->blocker_id;?>">
					<td>
						<?php echo date('D, F jS, Y', strtotime($t->blocker_date)); ?>
						<input type="hidden" class="blocker-id" value="<?php echo $t->blocker_id; ?>">
					</td>
					<td><?= $t->total_players; ?></td>
					<td><?= $t->blocker_cost; ?></td>
					<td>
						<a href="<?php echo get_bloginfo('url')."/new-blocker/?adventure_id=$adventure->adventure_id&blockerID=$t->blocker_id";?>" class="br-btn br-btn-green"><span class="icon icon-edit"></span></a>
					</td>
					<td>
						<button class="br-btn br-btn-blue" onClick="showOverlay('#confirm-restore-<?php echo $t->blocker_id; ?>');">
							<span class="icon icon-restore"></span>
						</button>
						<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?php echo $t->blocker_id; ?>">
							<div class="br-confirm">
								<button class="br-btn cyan" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','publish');">
									<span class="icon icon-restore"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</div>
					</td>
					<td>
						<button class="br-btn br-btn-red" onClick="showOverlay('#confirm-delete-<?php echo $t->blocker_id; ?>');">
							<span class="icon icon-delete"></span>
						</button>
						<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?php echo $t->blocker_id; ?>">
							<div class="br-confirm">
								<button class="br-btn red" onClick="confirmStatus(<?php echo $t->blocker_id; ?>,'blocker','delete');">
									<span class="icon icon-delete"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</div>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table></div><!-- /.br-section-body -->
</div>
<?php }else{ ?>
<div class="br-panel">
	<div class="br-empty">
		<span class="icon icon-trash"></span>
		<h3><?php _e("No blockers found in trash","bluerabbit"); ?></h3>
	</div>
</div>
<?php } ?>

</div><!-- /.br-journey-manager -->
<?php
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, "path");
$encounters = $wpdb->get_results("
SELECT enc.*, ach.achievement_name
FROM {$wpdb->prefix}br_encounters enc
LEFT JOIN {$wpdb->prefix}br_achievements ach ON enc.achievement_id=ach.achievement_id
WHERE enc.adventure_id=$adventure->adventure_id
ORDER BY enc.enc_level, FIELD(enc.enc_status,'publish','trash')");
?>

<div class="br-journey-manager">
<!-- ════════════ PUBLISHED ENCOUNTERS ════════════ -->
<div class="br-section">
	<div class="br-section-header" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3>
			<span class="icon icon-battle"></span> <?php _e('Published Encounters','bluerabbit'); ?>
			<a class="br-btn cyan" href="<?= get_bloginfo('url'); ?>/new-encounter/?adventure_id=<?= $adventure->adventure_id; ?>" onclick="event.stopPropagation();">
				<span class="icon icon-add"></span> <?php _e('New','bluerabbit'); ?>
			</a>
		</h3>
		<div class="br-header-right">
			<div class="br-search">
				<span class="icon icon-search"></span>
				<input type="text" id="search-encounters" placeholder="<?php _e('Search','bluerabbit'); ?>" onclick="event.stopPropagation();">
			</div>
			<span class="br-toggle-icon icon icon-arrow-down"></span>
		</div>
	</div>
	<div class="br-section-body">
		<?php if($encounters){ ?>
			<table class="br-table" id="table-encounter">
				<thead>
					<tr>
						<td class="text-center"><?php _e("ID","bluerabbit"); ?></td>
						<td><?php _e("Question","bluerabbit"); ?></td>
						<td class="text-center"><span class="icon icon-level"></span></td>
						<td class="text-center"><span class="icon icon-activity"></span></td>
						<td class="text-center"><span class="icon icon-star"></span></td>
						<td class="text-center"><span class="icon icon-bloo"></span></td>
						<td><span class="icon icon-achievement"></span></td>
						<td><span class="icon icon-view"></span></td>
						<td><span class="icon icon-edit"></span></td>
						<td><span class="icon icon-trash"></span></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($encounters as $key=>$e){ ?>
						<?php if($e->enc_status == 'publish'){ ?>
							<tr id="encounter-<?=  $e->enc_id;?>">
								<td class="text-center"><?= $e->enc_id; ?></td>
								<td><?= $e->enc_question; ?></td>
								<td>
									<input type="number" class="br-input br-enc-input-narrow" id="the_level-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_level; ?>" onChange="setLevel(<?= $e->enc_id; ?>,'encounter');">
								</td>
								<td>
									<input type="number" class="br-input br-enc-input-narrow" id="the_ep-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_ep; ?>" onChange="setEP(<?= $e->enc_id; ?>,'encounter');">
								</td>
								<td>
									<input type="number" class="br-input br-enc-input-narrow" id="the_xp-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_xp; ?>" onChange="setXP(<?= $e->enc_id; ?>,'encounter');">
								</td>
								<td>
									<input type="number" class="br-input br-enc-input-narrow" id="the_bloo-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_bloo; ?>" onChange="setBLOO(<?= $e->enc_id; ?>,'encounter');">
								</td>
								<td>
									<select class="br-input update-achievement br-enc-select-path" onChange="setAchievement(<?= $e->enc_id; ?>,'encounter')">
										<option value="0" <?php if(!$e->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
										<?php if($achievements['publish']){ ?>
											<?php foreach($achievements['publish'] as $a){ ?>
											<option value="<?= $a->achievement_id;?>" <?php if($e->achievement_id == $a->achievement_id){ echo 'selected'; }?>>
												<?= $a->achievement_name; ?>
											</option>
											<?php } ?>
										<?php } ?>
									</select>
								</td>
								<td>
									<button class="br-action-link" onClick="randomEncounter(<?= $e->enc_id; ?>);">
										<span class="icon icon-view"></span>
									</button>
								</td>
								<td>
									<a class="br-action-link edit" href="<?= get_bloginfo('url'); ?>/new-encounter/?adventure_id=<?= $adventure->adventure_id; ?>&enc_id=<?= $e->enc_id; ?>">
										<span class="icon icon-edit"></span>
									</a>
								</td>
								<td>
									<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?=  $e->enc_id; ?>');">
										<span class="icon icon-trash"></span>
									</button>
									<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?=  $e->enc_id; ?>">
										<button class="br-btn red" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','trash');">
											<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
										</button>
										<button class="br-btn ghost" onClick="hideAllOverlay();">
											<span class="icon icon-cancel"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		<?php }else{ ?>
			<div class="br-empty">
				<span class="icon icon-battle"></span>
				<h3><?php _e("No encounters found","bluerabbit"); ?></h3>
			</div>
		<?php } ?>
	</div>
</div>

<!-- ════════════ TRASHED ENCOUNTERS ════════════ -->
<div class="br-section">
	<div class="br-section-header collapsed" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3><span class="icon icon-trash"></span> <?php _e('Trash','bluerabbit'); ?>
			<?php
			$trash_count = 0;
			if($encounters){
				foreach($encounters as $e){
					if($e->enc_status == 'trash') $trash_count++;
				}
			}
			if($trash_count > 0){ ?>
				<span class="br-badge br-badge-red"><?= $trash_count; ?></span>
			<?php } ?>
		</h3>
		<span class="br-toggle-icon icon icon-arrow-down"></span>
	</div>
	<div class="br-section-body collapsed">
		<?php if($encounters){ ?>
			<?php
			$has_trash = false;
			foreach($encounters as $e){
				if($e->enc_status == 'trash'){ $has_trash = true; break; }
			}
			?>
			<?php if($has_trash){ ?>
				<table class="br-table" id="table-encounter-trash">
					<thead>
						<tr>
							<td class="text-center"><?php _e("ID","bluerabbit"); ?></td>
							<td><?php _e("Question","bluerabbit"); ?></td>
							<td class="text-center"><span class="icon icon-level"></span></td>
							<td class="text-center"><span class="icon icon-activity"></span></td>
							<td class="text-center"><span class="icon icon-star"></span></td>
							<td class="text-center"><span class="icon icon-bloo"></span></td>
							<td><span class="icon icon-achievement"></span></td>
							<td><span class="icon icon-restore"></span></td>
							<td><span class="icon icon-delete"></span></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach($encounters as $key=>$e){ ?>
							<?php if($e->enc_status == 'trash'){ ?>
								<tr id="encounter-<?=  $e->enc_id;?>">
									<td class="text-center"><?= $e->enc_id; ?></td>
									<td><?= $e->enc_question; ?></td>
									<td class="text-center"><?= $e->enc_level; ?></td>
									<td class="text-center"><?= $e->enc_ep; ?></td>
									<td class="text-center"><?= $e->enc_xp; ?></td>
									<td class="text-center"><?= $e->enc_bloo; ?></td>
									<td><?= $e->achievement_name; ?></td>
									<td>
										<button class="br-action-link" onClick="showOverlay('#confirm-restore-<?=  $e->enc_id; ?>');">
											<span class="icon icon-restore"></span>
										</button>
										<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?=  $e->enc_id; ?>">
											<button class="br-btn cyan" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','publish');">
												<span class="icon icon-restore"></span> <?php _e("Are you sure?","bluerabbit"); ?>
											</button>
											<button class="br-btn ghost" onClick="hideAllOverlay();">
												<span class="icon icon-cancel"></span>
											</button>
										</div>
									</td>
									<td>
										<button class="br-action-link trash" onClick="showOverlay('#confirm-delete-<?=  $e->enc_id; ?>');">
											<span class="icon icon-delete"></span>
										</button>
										<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?=  $e->enc_id; ?>">
											<button class="br-btn red" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','delete');">
												<span class="icon icon-delete"></span> <?php _e("Are you sure?","bluerabbit"); ?>
											</button>
											<button class="br-btn ghost" onClick="hideAllOverlay();">
												<span class="icon icon-cancel"></span>
											</button>
										</div>
									</td>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			<?php }else{ ?>
				<div class="br-empty">
					<span class="icon icon-trash"></span>
					<h3><?php _e("No encounters in trash","bluerabbit"); ?></h3>
				</div>
			<?php } ?>
		<?php }else{ ?>
			<div class="br-empty">
				<span class="icon icon-trash"></span>
				<h3><?php _e("No encounters found","bluerabbit"); ?></h3>
			</div>
		<?php } ?>
	</div>
</div>

<script>
$('#search-encounters').keyup(function(){
	var valThis = $(this).val().toLowerCase();
	if(valThis == ""){
		$('table#table-encounter tbody > tr').show();
	}else{
		$('table#table-encounter tbody > tr').each(function(){
			var text = $(this).text().toLowerCase();
			(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
		});
	};
});
</script>

</div><!-- /.br-journey-manager -->
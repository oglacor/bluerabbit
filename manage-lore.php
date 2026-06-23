<?php
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path');
$results = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_quests
	WHERE adventure_id=$adventure->adventure_id AND quest_type='lore'
	ORDER BY quest_type, quest_order, quest_id
	");

	$lore = array();
	foreach($results as $o){
		if($o->quest_status == 'trash'){
			$lore['trash'][]=$o;
		}elseif($o->quest_status == 'draft'){
			$lore['draft'][]=$o;
		}elseif($o->quest_status == 'hidden'){
			$lore['hidden'][]=$o;
		}elseif($o->quest_status == 'publish'){
			$lore['publish'][]=$o;
		}
	}

?>

<div class="br-journey-manager">
<!-- ════════════ PUBLISHED LORE ════════════ -->
<div class="br-section">
	<div class="br-section-header" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3><span class="icon icon-document"></span> <?php _e('Adventure Lore','bluerabbit'); ?></h3>
		<div class="br-header-right">
			<div class="br-search">
				<span class="icon icon-search"></span>
				<input type="text" id="search-lore" placeholder="<?php _e('Search','bluerabbit'); ?>">
			</div>
			<span class="br-toggle-icon icon icon-arrow-down"></span>
		</div>
	</div>
	<div class="br-section-body">
		<?php if(isset($lore['publish'])){ ?>
			<table class="br-table" id="table-quest">
				<thead>
					<tr>
						<td><?= __("Image","bluerabbit"); ?></td>
						<td><?= __("Name","bluerabbit"); ?></td>
						<td width="20%" class="text-center"><?= __("Path","bluerabbit"); ?></td>
						<td width="10%" class="text-center"><span class="icon icon-edit"></span></td>
						<td width="10%" class="text-center"><span class="icon icon-trash"></span></td>
					</tr>
				</thead>
				<tbody>
				<?php foreach($lore['publish'] as $key=>$b){ ?>
					<tr id="lore-<?= $b->quest_id;?>" class="lore">
						<td class="badge">
							<input type="hidden" value="<?= $b->mech_badge; ?>" id="the_quest_badge-<?= $b->quest_id; ?>">
							<button class="button-icon font _24 sq-40  icon-lg" onClick="showWPUpload('the_quest_badge-<?= $b->quest_id; ?>','a','quest',<?= $b->quest_id; ?>);" id="the_quest_badge-<?= $q->quest_id; ?>_thumb" style="background-image: url(<?= $b->mech_badge; ?>);">
							</button>
						</td>
						<td>
							<input type="text" class="br-input" style="min-width: 200px;" id="the_title-lore-<?= $b->quest_id; ?>" value="<?= $b->quest_title; ?>" onChange="setTitle(<?= $b->quest_id; ?>,'lore');">
							<p style="opacity: 0.5; font-size: 13px; margin: 2px 0 0;"><?= $b->quest_secondary_headline; ?></p>
							<input type="hidden" class="quest-id" value="<?= $b->quest_id;?>">
						</td>
						<td class="path text-center">
							<select class="br-input update-achievement" onChange="setAchievement(<?= $b->quest_id; ?>,'<?= $b->quest_type; ?>')">
								<option value="0"  <?php if(!$b->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
								<?php if($achievements['publish']){ ?>
									<?php foreach($achievements['publish'] as $a){ ?>
									<option value="<?= $a->achievement_id;?>" <?php if($b->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</td>
						<td class="text-center">
							<a href="<?= get_bloginfo('url')."/new-lore/?adventure_id=$adventure->adventure_id&questID=$b->quest_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span> <?php _e("Edit","bluerabbit"); ?></a>
						</td>
						<td class="text-center relative">
							<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?= $b->quest_id; ?>');">
								<span class="icon icon-trash"></span> <?php _e("Trash","bluerabbit"); ?>
							</button>
							<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $b->quest_id; ?>">
								<button class="br-btn red" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','trash');">
									<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<!-- Reorder sticky bar -->
			<div class="br-action-bar" style="justify-content: center; padding: 12px;">
				<button class="br-btn cyan" onclick="reorder('#table-quest')">
					<span class="icon icon-list"></span> <?php _e("Reorder Resources","bluerabbit"); ?>
				</button>
			</div>
		<?php }else{ ?>
			<div class="br-empty">
				<span class="icon icon-document"></span>
				<h3><?php _e("No posts found","bluerabbit"); ?></h3>
			</div>
		<?php } ?>
	</div>
</div>

<!-- ════════════ TRASHED LORE ════════════ -->
<div class="br-section">
	<div class="br-section-header collapsed" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3><span class="icon icon-trash"></span> <?php _e('Trashed Resources','bluerabbit'); ?>
			<?php if(isset($lore['trash'])){ ?>
				<span class="br-badge br-badge-red"><?= count($lore['trash']); ?></span>
			<?php } ?>
		</h3>
		<span class="br-toggle-icon icon icon-arrow-down"></span>
	</div>
	<div class="br-section-body collapsed">
		<?php if(isset($lore['trash'])){ ?>
			<table class="br-table" id="table-blog-trash">
				<thead>
					<tr>
						<td width="80%"><?= __("Title","bluerabbit"); ?></td>
						<td width="10%"><span class="icon icon-restore"></span></td>
						<td width="10%"><span class="icon icon-delete"></span></td>
					</tr>
				</thead>
				<tbody>
				<?php foreach($lore['trash'] as $key=>$b){ ?>
					<tr id="lore-<?= $b->quest_id;?>" class="lore">
						<td><?= $b->quest_title; ?></td>
						<td>
							<button class="br-action-link" onClick="showOverlay('#confirm-restore-<?= $b->quest_id; ?>');">
								<span class="icon icon-restore"></span>
							</button>
							<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?= $b->quest_id; ?>">
								<button class="br-btn cyan" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','publish');">
									<span class="icon icon-restore"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</td>
						<td>
							<button class="br-action-link trash" onClick="showOverlay('#confirm-delete-<?= $b->quest_id; ?>');">
								<span class="icon icon-delete"></span>
							</button>
							<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?= $b->quest_id; ?>">
								<button class="br-btn red" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','delete');">
									<span class="icon icon-trash"></span> <?php _e("Are you sure?","bluerabbit"); ?>
								</button>
								<button class="br-btn ghost" onClick="hideAllOverlay();">
									<span class="icon icon-cancel"></span>
								</button>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php }else{ ?>
			<div class="br-empty">
				<span class="icon icon-trash"></span>
				<h3><?php _e("No posts found in trash","bluerabbit"); ?></h3>
			</div>
		<?php } ?>
	</div>
</div>

<script>
$('#search-lore').keyup(function(){
	var valThis = $(this).val().toLowerCase();
	if(valThis == ""){
		$('table#table-quest tbody > tr, table#table-blog-trash tbody > tr').show();
	}else{
		$('table#table-quest tbody > tr, table#table-blog-trash tbody > tr').each(function(){
			var text = $(this).text().toLowerCase();
			(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
		});
	};
});
</script>

</div><!-- /.br-journey-manager -->
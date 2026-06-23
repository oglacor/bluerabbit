<?php
$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path');
$results = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_quests
	WHERE adventure_id=$adventure->adventure_id AND quest_type='blog-post'
	ORDER BY quest_type, quest_order, quest_id
	");

	$blogposts = array();
	foreach($results as $o){
		if($o->quest_status == 'trash'){
			$blogposts['trash'][]=$o;
		}elseif($o->quest_status == 'draft'){
			$blogposts['draft'][]=$o;
		}elseif($o->quest_status == 'hidden'){
			$blogposts['hidden'][]=$o;
		}elseif($o->quest_status == 'publish'){
			$blogposts['publish'][]=$o;
		}
	}

?>

<div class="br-journey-manager">
<!-- ════════════ PUBLISHED BLOG ENTRIES ════════════ -->
<div class="br-section">
	<div class="br-section-header" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3><span class="icon icon-document"></span> <?php _e('Blog Entries','bluerabbit'); ?></h3>
		<div class="br-header-right">
			<div class="br-search">
				<span class="icon icon-search"></span>
				<input type="text" id="search-blog" placeholder="<?php _e('Search','bluerabbit'); ?>">
			</div>
			<span class="br-toggle-icon icon icon-arrow-down"></span>
		</div>
	</div>
	<div class="br-section-body">
		<?php if(isset($blogposts['publish'])){ ?>
			<table class="br-table" id="table-quest">
				<thead>
					<tr>
						<td class="text-center"><span class="icon icon-image"></span></td>
						<td><?= __("Title","bluerabbit"); ?></td>
						<td><?= __("Style","bluerabbit"); ?></td>
						<td><?= __("Path","bluerabbit"); ?></td>
						<td><?= __("Start Date","bluerabbit"); ?></td>
						<td width="5%"><span class="icon icon-edit"></span></td>
						<td width="5%"><span class="icon icon-trash"></span></td>
					</tr>
				</thead>
				<tbody class="sortable">
				<?php foreach($blogposts['publish'] as $key=>$b){ ?>
					<tr id="blog-post-<?= $b->quest_id;?>" class="blog-post">
						<td class="badge">
							<input type="hidden" value="<?= $b->mech_badge; ?>" id="the_quest_badge-<?= $b->quest_id; ?>">
							<button class="button-icon font _24 sq-40  icon-lg" onClick="showWPUpload('the_quest_badge-<?= $b->quest_id; ?>','a','quest',<?= $b->quest_id; ?>);" id="the_quest_badge-<?= $q->quest_id; ?>_thumb" style="background-image: url(<?= $b->mech_badge; ?>);">
							</button>
						</td>
						<td>
							<strong><?= $b->quest_title; ?></strong>
							<p style="opacity: 0.5; font-size: 13px; margin: 2px 0 0;"><?= $b->quest_secondary_headline; ?></p>
							<input type="hidden" class="quest-id" value="<?= $b->quest_id;?>">
						</td>
						<td class="style">
							<select id="the_quest_style-<?= $b->quest_type; ?>-<?= $b->quest_id; ?>" class="br-input" onChange="setDisplayStyle(<?= $b->quest_id; ?>,'<?= $b->quest_type; ?>');" >
								<option value="text-right"  <?php if($b->quest_style || $b->quest_style=='text-right'){ echo 'selected'; }?>><?php _e('Text on right','bluerabbit'); ?></option>
								<option value="text-left"  <?php if($b->quest_style=='text-left'){ echo 'selected'; }?>><?php _e('Text on Left','bluerabbit'); ?></option>
								<option value="news-highlight"  <?php if($b->quest_style=='news-highlight'){ echo 'selected'; }?>><?php _e('News Highlight','bluerabbit'); ?></option>
								<option value="headline"  <?php if($b->quest_style=='headline'){ echo 'selected'; }?>><?php _e('Headline','bluerabbit'); ?></option>
							</select>
						</td>
						<td class="path">
							<select class="br-input update-achievement" onChange="setAchievement(<?= $b->quest_id; ?>,'<?= $b->quest_type; ?>')">
								<option value="0"  <?php if(!$b->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
								<?php if($achievements['publish']){ ?>
									<?php foreach($achievements['publish'] as $a){ ?>
									<option value="<?= $a->achievement_id;?>" <?php if($b->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</td>
						<td class="start-date">
							<?php
							if($b->mech_start_date != "0000-00-00 00:00:00"){
								$pretty_start_date = date('Y/m/d H:i', strtotime($b->mech_start_date));
							}else{
								$pretty_start_date = '';
							}
							?>
							<input autocomplete="off" class="br-input datetimepicker" id="the_start_date-<?= $b->quest_type; ?>-<?= $b->quest_id; ?>" type="text" value="<?= $pretty_start_date; ?>" onChange="setStartDate(<?= $b->quest_id; ?>,'<?= $b->quest_type; ?>');">
						</td>

						<td>
							<a href="<?= get_bloginfo('url')."/new-blog-post/?adventure_id=$adventure->adventure_id&questID=$b->quest_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span> <?php _e("Edit","bluerabbit"); ?></a>
						</td>
						<td>
							<button class="br-action-link trash" onClick="showOverlay('#confirm-trash-<?= $b->quest_id; ?>');">
								<span class="icon icon-trash"></span> <?php _e("Trash","bluerabbit"); ?>
							</button>
							<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $b->quest_id; ?>">
								<button class="br-btn red" onClick="confirmStatus(<?= $b->quest_id; ?>,'blog-post','trash');">
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
					<span class="icon icon-list"></span> <?php _e("Reorder Posts","bluerabbit"); ?>
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

<!-- ════════════ TRASHED BLOG ENTRIES ════════════ -->
<div class="br-section">
	<div class="br-section-header collapsed" onclick="$(this).toggleClass('collapsed'); $(this).next('.br-section-body').toggleClass('collapsed');">
		<h3><span class="icon icon-trash"></span> <?php _e('Trashed Blog Entries','bluerabbit'); ?>
			<?php if(isset($blogposts['trash'])){ ?>
				<span class="br-badge br-badge-red"><?= count($blogposts['trash']); ?></span>
			<?php } ?>
		</h3>
		<span class="br-toggle-icon icon icon-arrow-down"></span>
	</div>
	<div class="br-section-body collapsed">
		<?php if(isset($blogposts['trash'])){ ?>
			<table class="br-table" id="table-blog-trash">
				<thead>
					<tr>
						<td width="70%"><?= __("Title","bluerabbit"); ?></td>
						<td width="10%"><span class="icon icon-edit"></span></td>
						<td width="10%"><span class="icon icon-restore"></span></td>
						<td width="10%"><span class="icon icon-delete"></span></td>
					</tr>
				</thead>
				<tbody>
				<?php foreach($blogposts['trash'] as $key=>$b){ ?>
					<tr id="blog_post-<?= $b->quest_id;?>" class="blog-post">
						<td><?= $b->quest_title; ?></td>
						<td>
							<a href="<?= get_bloginfo('url')."/new-blog-post/?adventure_id=$adventure->adventure_id&questID=$b->quest_id";?>" class="br-action-link edit"><span class="icon icon-edit"></span></a>
						</td>
						<td>
							<button class="br-action-link" onClick="showOverlay('#confirm-restore-<?= $b->quest_id; ?>');">
								<span class="icon icon-restore"></span>
							</button>
							<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?= $b->quest_id; ?>">
								<button class="br-btn cyan" onClick="confirmStatus(<?= $b->quest_id; ?>,'blog-post','publish');">
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
								<button class="br-btn red" onClick="confirmStatus(<?= $b->quest_id; ?>,'blog-post','delete');">
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
$('#search-blog').keyup(function(){
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
<?php 
$achievements = getAchievements($adventure->adventure_id, 'path');
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
<div class="highlight padding-10 indigo-bg-50">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40  indigo-bg-300"><span class="icon icon-document"></span></span>
		<span class="icon-content">
			<span class="line font _24 grey-800"><?php _e('Adventure Lore','bluerabbit'); ?></span>
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

<?php if(isset($lore['publish'])){ ?>
	<div class="content">
		<table class="table table-quests" id="table-quest">
			<thead>
				<tr>
					<td><?= __("Image","bluerabbit"); ?></td>
					<td><?= __("Name","bluerabbit"); ?></td>
					<td width="20%" class="text-center"><?= __("Path","bluerabbit"); ?></td>
					<td width="10%" class="text-center"><span class="icon icon-edit"></span></td>
					<td width="10%" class="text-center"><span class="icon icon-trash"></span></td>
				</tr>
			</thead>
			<tbody class="">
			<?php foreach($lore['publish'] as $key=>$b){ ?>
				<tr id="lore-<?= $b->quest_id;?>" class="lore">
					<td class="badge">
						<input type="hidden" value="<?= $b->mech_badge; ?>" id="the_quest_badge-<?= $b->quest_id; ?>">
						<button class="icon-button font _24 sq-40  icon-lg" onClick="showWPUpload('the_quest_badge-<?= $b->quest_id; ?>','a','quest',<?= $b->quest_id; ?>);" id="the_quest_badge-<?= $q->quest_id; ?>_thumb" style="background-image: url(<?= $b->mech_badge; ?>);">
						</button>
					</td>
					<td class="">
						<input type="text" class="form-ui w-full min-w-200" id="the_title-lore-<?= $b->quest_id; ?>" value="<?= $b->quest_title; ?>" onChange="setTitle(<?= $b->quest_id; ?>,'lore');">
						<p class="font _14 grey-600"><?= $b->quest_secondary_headline; ?></p>
						<input type="hidden" class="quest-id" value="<?= $b->quest_id;?>">
					</td>
					<td class="path text-center">
						<select class="form-ui update-achievement" onChange="setAchievement(<?= $b->quest_id; ?>,'<?= $b->quest_type; ?>')">
							<option value="0"  <?php if(!$b->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
							<?php if($achievements['publish']){ ?>
								<?php foreach($achievements['publish'] as $a){ ?>
								<option value="<?= $a->achievement_id;?>" <?php if($b->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</td>
					<td class="text-center">
						<a href="<?= get_bloginfo('url')."/new-lore/?adventure_id=$adventure->adventure_id&questID=$b->quest_id";?>" class="icon-button font _24 sq-40  icon-sm light-green-bg-400"><span class="icon icon-edit"></span></a>
					</td>
					<td class="text-center relative">
						<button class="icon-button font _24 sq-40  icon-sm red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $b->quest_id; ?>');">
							<span class="icon icon-trash"></span>
							<span class="tool-tip bottom">
								<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
							</span>
						</button>
						<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $b->quest_id; ?>">
							<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','trash');">
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
			<div class="highlight padding-10 blue-bg-800 white-color sticky-bottom text-center">
				<div class="icon-group">
					<div class="icon-content">
						<button class="form-ui blue-bg-400 font _16 main w300" onclick="reorder('#table-quest')">
							<span class="icon icon-list"></span> <strong><?php _e("Reorder Resources","bluerabbit"); ?></strong>
						</button>
					</div>
				</div>
			</div>
	</div>
<?php }else{ ?> 
	<div class="highlight padding-10 indigo-bg-50">
		<span class="icon-group text-center">
			<span class="icon-content">
				<span class="icon icon-cancel"></span> <?php _e("No posts found","bluerabbit"); ?>
			</span>
		</span>
	</div>
<?php } ?>
<?php if(isset($lore['trash'])){ ?>
	<div class="highlight padding-10 red-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40  red-bg-300"><span class="icon icon-trash"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800"><?php _e('Trashed Resources','bluerabbit'); ?></span>
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
								$('table#table-blog-trash tbody > tr').show();           
							}else{
								$('table#table-blog-trash tbody > tr').each(function(){
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
	<div class="content">
		<table class="table small" id="table-blog-trash">
			<thead>
				<tr>
					<td width="80%"><?= __("Title","bluerabbit"); ?></td>
					<td width="10%"><span class="icon icon-restore"></span></td>
					<td width="10%"><span class="icon icon-delete"></span></td>
				</tr>
			</thead>
			<tbody class="">
			<?php foreach($lore['trash'] as $key=>$b){ ?>
				<tr id="lore-<?= $b->quest_id;?>" class="lore">
					<td class=""><?= $b->quest_title; ?></td>
					<td>
						<button class="icon-button font _24 sq-40  icon-sm  blue-bg-200 white-color restore-button" onClick="showOverlay('#confirm-restore-<?= $b->quest_id; ?>');">
							<span class="icon icon-restore"></span>
							<span class="tool-tip bottom">
								<span class="tool-tip-text font _12"><?php _e("Restore","bluerabbit"); ?></span>
							</span>
						</button>
						<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?= $b->quest_id; ?>">
							<button class="form-ui white-bg restore-confirm-button" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','publish');">
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
						<button class="icon-button font _24 sq-40  icon-sm  red-bg-200 white-color delete-button" onClick="showOverlay('#confirm-delete-<?= $b->quest_id; ?>');">
							<span class="icon icon-delete"></span>
							<span class="tool-tip bottom">
								<span class="tool-tip-text font _12"><?php _e("Delete","bluerabbit"); ?></span>
							</span>
						</button>
						<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?= $b->quest_id; ?>">
							<button class="form-ui white-bg delete-confirm-button" onClick="confirmStatus(<?= $b->quest_id; ?>,'lore','delete');">
								<span class="icon-group">
									<span class="icon-button font _24 sq-40  red-bg-A400 icon-sm">
										<span class="icon icon-trash white-color"></span>
									</span>
									<span class="icon-content">
										<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
										<span class="line grey-600 font _14 w900"><?php _e("You can't undo this!","bluerabbit"); ?></span>
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
				<span class="icon icon-cancel"></span> <?php _e("No posts found in trash","bluerabbit"); ?>
			</span>
		</span>
	</div>
<?php } ?>

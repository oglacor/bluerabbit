<?php
$achievements = getAchievements($adventure->adventure_id, "path");
$encounters = $wpdb->get_results("
SELECT enc.*, ach.achievement_name 
FROM {$wpdb->prefix}br_encounters enc
LEFT JOIN {$wpdb->prefix}br_achievements ach ON enc.achievement_id=ach.achievement_id
WHERE enc.adventure_id=$adventure->adventure_id
ORDER BY enc.enc_level, FIELD(enc.enc_status,'publish','trash')");
?>

	<div class="highlight padding-10 orange-bg-50">
		<span class="icon-group">
			<span class="icon-button font _24 sq-40  orange-bg-400"><span class="icon icon-battle"></span></span>
			<span class="icon-content">
				<span class="line font _24 grey-800"><?php _e('Published Encounters','bluerabbit'); ?></span>
			</span>
			<button class="icon-button font _24 sq-40  blue-bg-700" onClick="loadContent('new-encounter');">
				<span class="icon icon-add"></span>
			</button>
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
								$('table#table-achievement tbody > tr').show();           
							}else{
								$('table#table-achievement tbody > tr').each(function(){
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
<?php if($encounters){ ?>
	<div class="content">
		<table class="table table-encounter" id="table-encounter">
			<thead>
				<tr>
					<td class="text-center"><strong><?php _e("ID","bluerabbit"); ?></strong></td>
					<td class=""><strong><?php _e("Question","bluerabbit"); ?></strong></td>
					<td class="text-center w-100"><span class="icon icon-level"></span></td>
					<td class="text-center w-100"><span class="icon icon-activity"></span></td>
					<td class="text-center w-100"><span class="icon icon-star"></span></td>
					<td class="text-center w-100"><span class="icon icon-bloo"></span></td>
					<td class=""><span class="icon icon-achievement"></span></td>
					<td class=""><span class="icon icon-view"></span></td>
					<td class=""><span class="icon icon-edit"></span></td>
					<td class=""><span class="icon icon-trash"></span></td>
				</tr>
			</thead>
			<tbody class="">
				<?php foreach($encounters as $key=>$e){ ?>
					<?php if($e->enc_status == 'publish'){ ?>
						<tr id="encounter-<?=  $e->enc_id;?>">
							<td class="text-center"><?= $e->enc_id; ?></td>
							<td class=""><?= $e->enc_question; ?></td>
							<td class="">
								<input type="number" class="form-ui w-100" id="the_level-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_level; ?>" onChange="setLevel(<?= $e->enc_id; ?>,'encounter');">
							</td>
							<td class="">
								<input type="number" class="form-ui w-100" id="the_ep-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_ep; ?>" onChange="setEP(<?= $e->enc_id; ?>,'encounter');">
							</td>
							<td class="">
								<input type="number" class="form-ui w-100" id="the_xp-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_xp; ?>" onChange="setXP(<?= $e->enc_id; ?>,'encounter');">
							</td>
							<td class="">
								<input type="number" class="form-ui w-100" id="the_bloo-encounter-<?= $e->enc_id; ?>" value="<?= $e->enc_bloo; ?>" onChange="setBLOO(<?= $e->enc_id; ?>,'encounter');">
							</td>
							<td class="">
								<select class="form-ui update-achievement w-150" onChange="setAchievement(<?= $e->enc_id; ?>,'encounter')">
									<option value="0" <?php if(!$e->achievement_id){ echo 'selected'; }?>><?php _e('All paths','bluerabbit'); ?></option>
									<?php if($achievements['publish']){ ?>
										<?php foreach($achievements['publish'] as $a){ ?>
										<option class="<?= $a->achievement_color."-bg-400"; ?> padding-5 margin-5" value="<?= $a->achievement_id;?>" <?php if($e->achievement_id == $a->achievement_id){ echo 'selected'; }?>>
											<?= $a->achievement_name; ?>
										</option>
										<?php } ?>
									<?php } ?>
								</select>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  blue-bg-400" onClick="randomEncounter(<?= $e->enc_id; ?>);">
									<span class="icon icon-view"></span>
								</button>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  green-bg-400" onClick="loadContent('new-encounter',<?= $e->enc_id; ?>);">
									<span class="icon icon-edit"></span>
								</button>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?=  $e->enc_id; ?>');">
									<span class="icon icon-trash"></span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?=  $e->enc_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','trash');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  red-bg-A400">
												<span class="icon icon-trash white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="content">
		<h1><?= __("Trash","bluerabbit"); ?></h1>
		<table class="table table-encounter-trash" id="table-encounter-trash">
			<thead>
				<tr>
					<td class="text-center"><strong><?php _e("ID","bluerabbit"); ?></strong></td>
					<td class=""><strong><?php _e("Question","bluerabbit"); ?></strong></td>
					<td class="text-center w-100"><span class="icon icon-level"></span></td>
					<td class="text-center w-100"><span class="icon icon-activity"></span></td>
					<td class="text-center w-100"><span class="icon icon-star"></span></td>
					<td class="text-center w-100"><span class="icon icon-bloo"></span></td>
					<td class=""><span class="icon icon-achievement"></span></td>
					<td class=""><span class="icon icon-restore"></span></td>
					<td class=""><span class="icon icon-delete"></span></td>
				</tr>
			</thead>
			<tbody class="">
				<?php foreach($encounters as $key=>$e){ ?>
					<?php if($e->enc_status == 'trash'){ ?>
						<tr id="encounter-<?=  $e->enc_id;?>">
							<td class="text-center"><?= $e->enc_id; ?></td>
							<td class=""><?= $e->enc_question; ?></td>
							<td class=""><?= $e->enc_level; ?></td>
							<td class=""><?= $e->enc_ep; ?></td>
							<td class=""><?= $e->enc_xp; ?></td>
							<td class=""><?= $e->enc_bloo; ?></td>
							<td class=""><?= $e->achievement_name; ?></td>
							<td class="">
								<button class="icon-button font _24 sq-40  light-blue-bg-300 white-color restore-button" onClick="showOverlay('#confirm-restore-<?=  $e->enc_id; ?>');">
									<span class="icon icon-restore"></span>
								</button>
								<div class="confirm-action overlay-layer restore-confirm" id="confirm-restore-<?=  $e->enc_id; ?>">
									<button class="form-ui white-bg restore-confirm-button" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','publish');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40 light-blue-bg-300">
												<span class="icon icon-restore white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line blue-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _18 sq-30 blue-grey-bg-800 white-color" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
							<td class="">
								<button class="icon-button font _24 sq-40  red-bg-200 white-color delete-button" onClick="showOverlay('#confirm-delete-<?=  $e->enc_id; ?>');">
									<span class="icon icon-delete"></span>
								</button>
								<div class="confirm-action overlay-layer delete-confirm" id="confirm-delete-<?=  $e->enc_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?=  $e->enc_id; ?>,'encounter','delete');">
										<span class="icon-group">
											<span class="icon-button font _24 sq-40  red-bg-A400">
												<span class="icon icon-delete white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
												<span class="line grey-800 font _18 w300"><?php _e("You can't undo this","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _18 sq-30 blue-grey-bg-800 white-color" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
<?php }else{ ?> 
	<div class="highlight padding-10 deep-purple-bg-50">
		<span class="icon-group text-center">
			<span class="icon-content">
				<span class="icon icon-cancel"></span> <?php _e("No encounters found","bluerabbit"); ?>
			</span>
		</span>
	</div>
<?php } ?>

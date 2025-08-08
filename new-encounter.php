
<?php 
	global $wpdb;
	$encounter = $wpdb->get_row(" SELECT * FROM {$wpdb->prefix}br_encounters WHERE adventure_id=$adventure->adventure_id AND enc_id=$id");
	$paths = getAchievements($adventure->adventure_id, 'path|rank');
?>


<div class="boxed w-full max-w-900 padding-10 white-bg">
	<div class="w-full padding-10 cyan-bg-50 relative">
		<div class="icon-group">
			<div class="icon-button font _24 sq-40 cyan-bg-A400 grey-900"><span class="icon icon-run"></span></div >
			<div class="icon-content">
				<div class="line font _24 grey-800">
					<?= ($adventure && isset($encounter)) ? __("Edit Encounter","bluerabbit") : __("New Encounter","bluerabbit"); ?>
				</div>
				<input type="hidden" id="the_enc_id" value="<?= isset($encounter) ? $encounter->enc_id : ""; ?>">
			</div>
		</div>
		<button class="form-ui red-bg-400 absolute top-10 right-10" onClick="unloadContent();">
			<span class="icon icon-cancel"></span> <?= __("Close","bluerabbit"); ?>
		</button>
	</div>
	<table class="table w-full" cellpadding="0">
		<thead>
			<tr class="font _12 grey-600">
				<td class="text-right w-150"><?= __('Setting','bluerabbit'); ?></td>
				<td><?= __('Value','bluerabbit'); ?></td>
			</tr>
		</thead>
		<tbody class="font _16">
			<tr>
				<td class="text-right w-150"><?= __('Encounter Level','bluerabbit'); ?></td>
				<td>
					<input class="form-ui w-full" type="number" max="99" min="1" id="the_enc_level" value="<?php if(isset($encounter->enc_level)){echo $encounter->enc_level;}else{echo '1';} ?>" onBlur="checkLevel('#the_enc_level');" onChange="checkLevel('#the_enc_level');">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Path available','bluerabbit'); ?></td>
				<td>
					<select id="the_enc_achievement_id" class="form-ui">
						<option value="0"  <?php if(!isset($encounter->achievement_id)){ echo 'selected'; }?>><?= __('All paths','bluerabbit'); ?></option>
						<?php if(isset($paths)){ ?>
							<?php foreach($paths['publish'] as $a){ ?>
								<?php if($a->achievement_display =='path'){ ?>
								<option value="<?= $a->achievement_id;?>" <?php if(isset($encounter->achievement_id) && $encounter->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?= $a->achievement_name; ?></option>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Question','bluerabbit'); ?></td>
				<td>
					<div class="input-group w-full">
						<textarea class="form-ui font _24 foreground grey-bg-100" placeholder="<?= __("Type your question","bluerabbit"); ?>" rows="4" value="<?= isset($encounter->enc_question) ? $encounter->enc_question : ""; ?>" id="the_enc_question"><?= isset($encounter->enc_question) ? $encounter->enc_question : ""; ?></textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150 green-bg-400 white-color">
					<?= __('Correct Option','bluerabbit'); ?>
				</td>
				<td>
					<textarea class="form-ui font _18" placeholder="<?= __("Correct option","bluerabbit"); ?>" rows="3"  value="<?= isset($encounter->enc_right_option) ? $encounter->enc_right_option : ""; ?>" id="the_enc_correct"><?= isset($encounter->enc_right_option) ? $encounter->enc_right_option : ""; ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150 red-bg-400 white-color">
					<?= __('Decoy Option 1','bluerabbit'); ?>
				</td>
				<td>
					<textarea class="form-ui font _18" placeholder="<?= __("Decoy option","bluerabbit"); ?>" rows="3"  value="<?= isset($encounter->enc_decoy_option1) ? $encounter->enc_decoy_option1 : ""; ?>" id="the_enc_decoy1"><?= isset($encounter->enc_decoy_option1) ? $encounter->enc_decoy_option1 : ""; ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150 red-bg-400 white-color">
					<?= __('Decoy Option 2','bluerabbit'); ?>
				</td>
				<td>
					<textarea class="form-ui font _18" placeholder="<?= __("Decoy option","bluerabbit"); ?>" rows="3"  value="<?= isset($encounter->enc_decoy_option2) ? $encounter->enc_decoy_option2 : ""; ?>" id="the_enc_decoy2"><?= isset($encounter->enc_decoy_option2) ? $encounter->enc_decoy_option2 : ""; ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('Image','bluerabbit'); ?></td>
				<td>
					<div class="gallery">
						<div class="gallery-item setting">
							<div class="background" style="background-image: url(<?= isset($encounter->enc_badge) ? $encounter->enc_badge : "" ; ?>);" onClick="showWPUpload('the_enc_badge');" id="the_enc_badge_thumb"></div>
							<div class="gallery-item-options relative">
								<button class="icon-button font _24 sq-40  green-bg-400" onClick="showWPUpload('the_enc_badge');"><span class="icon icon-image"></span></button>
								<button class="icon-button font _24 sq-40  red-bg-400" onClick="clearImage('#the_enc_badge');"> <span class="icon icon-trash"></span> </button>
								<input type="hidden" id="the_enc_badge" value="<?= isset($encounter->enc_badge) ? $encounter->enc_badge : "" ; ?>"/>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('EP','bluerabbit'); ?></td>
				<td>
					<input type="number" class="form-ui w-full" id="the_enc_ep" value="<?= isset($encounter->enc_ep) ? $encounter->enc_ep : 10;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('XP','bluerabbit'); ?></td>
				<td>
					<input type="number" class="form-ui w-full" id="the_enc_xp" value="<?= isset($encounter->enc_xp) ? $encounter->enc_xp : 0;?>">
				</td>
			</tr>
			<tr>
				<td class="text-right w-150"><?= __('BLOO','bluerabbit'); ?></td>
				<td>
					<input type="number" class="form-ui w-full" id="the_enc_bloo" value="<?= isset($encounter->enc_bloo) ? $encounter->enc_bloo : 0;?>">
				</td>
			</tr>
		</tbody>
	</table>
	<div class="footer-ui grey-bg-800 text-right">
		<button id="submit-button" type="button" class="form-ui green-bg-400 " onClick="updateEncounter();">
			<span class="icon icon-check"></span>
			<?php 
			if($adventure && isset($encounter)){
				echo __('Update Encounter','bluerabbit');
			}else{
				echo __('Create Encounter','bluerabbit');
			} 
			?>
		</button>
		<div class="input-group inline-table"> 
			<label class="purple-bg-400 font condensed w900 uppercase white-color"><?= __('Status',"bluerabbit"); ?></label>
			<select id="the_enc_status" class="form-ui">
				<option value="publish" <?php if(!isset($a->achievement_status) || $a->achievement_status == 'publish'){ echo 'selected'; }?>><?= __('Publish','bluerabbit'); ?></option>
				<option value="trash" <?php if(isset($a->achievement_status) && $a->achievement_status == 'trash'){ echo 'selected'; }?>><?= __('Trash','bluerabbit'); ?></option>
			</select>
		</div>
	</div>
</div>
<input type="hidden" id="new-encounter-nonce" value="<?= wp_create_nonce('br_update_encounter_nonce'); ?>"/>

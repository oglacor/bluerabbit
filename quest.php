<?php 
	$steps = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_steps WHERE quest_id=$q->quest_id AND step_status='publish' AND adventure_id=$adv_parent_id ORDER BY step_order ASC, step_id ASC") ;
	$buttons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_step_buttons WHERE quest_id=$q->quest_id AND adventure_id=$adv_parent_id AND button_status='publish'") ;
	$step_buttons = array();
	foreach($buttons as $btn){
		$step_buttons[$btn->step_id][$btn->button_id] = $btn;
	}
	$override = true;

?>

<?php if($q->pp_content){ ?>
	<div class="layer overlay fixed top-20 right-50">
		<a class="form-ui blue-grey-bg-600 white-color font _14" href="<?= get_bloginfo('url')."/post/?questID=$q->quest_id&adventure_id=$adventure->adventure_id";?>"><?= __("Quest Results","bluerabbit"); ?></a>
	</div>
<?php } ?>
<div class="steps" id="quest-steps">
	<?php if(!$steps){ ?>
		<?php 
			$override = false;
			$i=0;
			$step = new stdClass();
			$step->step_order = 1;
			$step->step_id = $q->quest_id;
			$step->step_content = $q->quest_content;
			include (TEMPLATEPATH . "/step-open.php");
		?>
	<?php }else{ ?>

		<?php foreach($steps as $i=>$step){
			//$step = $steps[$i];
			if($step->step_type == 'instruction'){
				$step->step_type = 'dialogue';
			}
			if($step->step_type == 'open'){
				$override = false;
			}
			include (TEMPLATEPATH . "/step-$step->step_type.php");
		} ?>
		<?php if($isGM){ ?>
			<div class="steps-admin-nav">
				<div class="input-group w-full">
					<label class="green-bg-400">
						<a href="<?= get_bloginfo('url'); ?>/new-quest/?adventure_id=<?= $adv_parent_id; ?>&questID=<?=$q->quest_id; ?>">
							<?= __("Edit Current Quest","bluerabbit"); ?>
						</a>
					</label>
					<label class="black-bg" for="steps-nav-select"><?= __("Skip to Step:"); ?></label>
					<select id="steps-nav-select" name="steps-nav-select" class="form-ui" onChange="skipToStep($('#steps-nav-select').val());">
						<option value="0"> - <?= __("Select a step to go","bluerabbit"); ?> -</option>
						<?php foreach($steps as $i=>$step){ ?>
							<option value="<?= $i+1; ?>">
								[<?= $i+1; ?>] <?= $step->step_title; ?>
							</option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>


		<?php if($override){ ?>
			<input type="hidden" id="override_nonce" value="<?= wp_create_nonce('br_player_override_post_nonce_'.$current_user->ID); ?>"/>
		<?php } ?>
	<?php } ?>
	<input type="hidden" id="profile_nonce" value='<?= wp_create_nonce('br_profile_post_nonce') ?>'>
</div>

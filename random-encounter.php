<?php
	global $wpdb; 
	$achs = $wpdb->get_col("SELECT achievement_id
	FROM {$wpdb->prefix}br_player_achievement
	WHERE adventure_id=$adventure->adventure_id AND player_id=$current_user->ID");
	if($achs){
		$achs_str = "AND (enc.achievement_id IN (".implode(',',$achs).") OR enc.achievement_id=0)";
	}else{
		$achs_str = "AND enc.achievement_id=0";
	}
	if($enc_id){
		$enc = $wpdb->get_row("SELECT enc.* FROM {$wpdb->prefix}br_encounters enc WHERE enc.adventure_id=$adventure->adventure_id AND enc.enc_id = $enc_id");
	}else{
		$enc = $wpdb->get_row("SELECT enc.* FROM {$wpdb->prefix}br_encounters enc WHERE enc.adventure_id=$adventure->adventure_id AND enc.enc_id != $current_player->player_last_random_encounter_id AND enc.enc_level <=$current_player->player_level $achs_str ORDER BY RAND() LIMIT 1");
	}
	logActivity($adventure->adventure_id,'attempt','encounter','',$enc->enc_id);	
?>

<?php if($enc){ ?>
	<?php 
		$update = "UPDATE {$wpdb->prefix}br_player_adventure SET player_last_random_encounter_id=%d WHERE player_id=$current_user->ID AND adventure_id=$adventure->adventure_id";
		$update_player_rand_enc = $wpdb->query($wpdb->prepare($update,$enc->enc_id)); 
	?>
	<div class="layer background opacity-0 black-bg fixed top left sq-full" onClick="unloadContent();"></div>
	<div class="max-w-600 layer base absolute perfect-center min-w-300" id="<?= "encounter-$enc->enc_id"; ?>">
		<div class="container foreground boxed max-w-900 border rounded-10 overflow-hidden">
			<div class="background grey-bg-900 "></div>
			<div class="body-ui w-full foreground">
				<h3 class="font _18 white-color text-center padding-10 w600 uppercase"><?= __("A wild question has appeared!","bluerabbit"); ?></h3>
				<div class="highlight text-center padding-20 white-color">
					<?php if($enc->enc_badge){ ?><img src="<?= $enc->enc_badge; ?>" width="340" class="max-h-300"><?php } ?>
					<h1 class="font _24 w300 kerning-1"><?= $enc->enc_question; ?></h1>
				</div>
				<div class="highlight text-center padding-20 white-color encounter-options">
					<?php
						$options = array($enc->enc_right_option, $enc->enc_decoy_option1, $enc->enc_decoy_option2);
						shuffle($options);
					?>
					<input type="hidden" id="current-encounter-id" value="<?= $enc->enc_id; ?>">
					<button class="form-ui border rounded-max w-full font _18 main w500 margin-5 cyan-bg-700" onClick="answerEncounter(0);" id="enc-opt-0"><?= $options[0]; ?></button>
					<button class="form-ui border rounded-max w-full font _18 main w500 margin-5 cyan-bg-700" onClick="answerEncounter(1);" id="enc-opt-1"><?= $options[1]; ?></button>
					<button class="form-ui border rounded-max w-full font _18 main w500 margin-5 cyan-bg-700" onClick="answerEncounter(2);" id="enc-opt-2"><?= $options[2]; ?></button>
				</div>
				<div class="padding-10 text-center">
					<button class="form-ui red-bg-400" onClick="document.location.reload();">
						<?= __("Close","bluerabbit"); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
<?php }else{ ?>
	<div class="max-w-500 perfect-center absolute layer base" onClick="unloadContent();">
		<div class="container foreground boxed max-w-900 border rounded-10 overflow-hidden">
			<div class="background grey-bg-900 "></div>
			<div class="body-ui w-full foreground">
				<div class="highlight text-center padding-20 white-color font _30">
					<?= __("No available encounters at your level","bluerabbit"); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
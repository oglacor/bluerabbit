<?php
	$player_to_update =getPlayerData($id);
	$player_name = $player_to_update->player_display_name ? $player_to_update->player_display_name : $player_to_update->player_email;
?>
<div class="magic-code-form overlay-layer layer top-overlay fixed sq-full top left active" id="reset-demo-form">
	<div class="layer absolute background indigo-bg-400 opacity-80"></div>
	<div class="layer absolute background black-bg opacity-80" onClick="hideAllOverlay();"></div>
	<div class="layer relative base perfect-center text-center w-400 white-bg border rounded-8 padding-10">
		<h1 class="font _20 w100 padding-10 w-full white-color red-bg-400 text-center">
			<span class="icon icon-quest"></span>
			<?php _e("Update Player Password","bluerabbit"); ?>
		</h1>

		<h3 class="padding-5 font w900 special"><?= __("Player","bluerabbit"); ?>: <?= $player_name;?></h3>
		
		
		<div class="input-group w-full padding-10">
			<label for="the_player_password" class="purple-bg-400 white-color font _16 w300 uppercase condensed">
				<span class="icon icon-lock"></span><?= __("New password","bluerabbit"); ?>
			</label>
			<input type="password"  autocomplete="off"  id="the_player_password" name="the_player_password" placeholder="<?= __("For","bluerabbit")." $player_name"; ?>" class="form-ui font _18 w-full">
		</div>
		<div class="input-group w-full padding-10">
			<label for="the_player_password" class="purple-bg-400 white-color font _16 w300 uppercase condensed">
				<span class="icon icon-lock"></span><?= __("Confirm Password","bluerabbit"); ?>
			</label>
			<input type="password" autocomplete="off" id="the_player_password_confirm" name="the_player_password_confirm" placeholder="<?= __("For","bluerabbit")." $player_name"; ?>" class="form-ui font _18 w-full">
		</div>
		<h3 class="blue-grey-bg-000 white-color font _20 w300 uppercase condensed text-center">
				<span class="icon icon-lock"></span><?= __("Game Master / NPC Password","bluerabbit"); ?>
		</h3>
		<div class="input-group w-full padding-10">
			<input type="password" id="the_gm_password" autocomplete="off"  name="the_gm_password" class="form-ui font _18 w-full"  placeholder="<?= __("Your password","bluerabbit"); ?>">
		</div>
		<button class="form-ui red-bg-A400 font _16" onClick="resetPlayerPassword();"><?= __("Reset password for","bluerabbit"); ?>: <?= $player_name; ?></button>
	</div>
	<input type="hidden" id="reset_user_password_nonce" value="<?php echo wp_create_nonce('reset_user_password_nonce'.$current_user->ID); ?>" />
	<input type="hidden" id="the_player_to_update" value="<?php echo $player_to_update->player_id; ?>" />
</div>

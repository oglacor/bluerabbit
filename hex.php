<?php
$adventureLink = get_bloginfo('url')."/adventure/?adventure_id=$adv->adventure_id"; 
$player_role = $adv->player_adventure_role;
$isGM = $adv->player_adventure_role == 'gm' ? true : false;
$isNPC = $adv->player_adventure_role == 'npc' ? true : false;
$isPlayer = $adv->player_adventure_role == 'player' ? true : false;

$isOwner = $adv->adventure_owner == $current_user->ID ? true : false;
$adv_color = $adv->adventure_color ? $adv->adventure_color : 'blue-grey';



?>
<div class="hex hex-<?=$key;?>" id="adventure-<?=$adv->adventure_id;?>">
	<div class="hex-data white-color">
		<div class="adventure-status opacity-50">
			<span class="icon icon-level purple-300"></span><?=$adv->player_level;?> | 
			<span class="icon icon-star amber-300"></span><?=$adv->player_xp;?> | 
			<span class="icon icon-bloo light-green-300"></span><?=$adv->player_bloo;?>
		</div>
		<h1><?=$adv->adventure_title; ?></h1>
		
		<?php if($adv->player_last_login){ ?>
			<span class="font _14 w100 opacity-70 white-color block">
				<?php echo __("Last login","bluerabbit").": ".get_time_ago(strtotime($adv->player_last_login), $adv->adventure_id); ?>
			</span>
		<?php }else{ ?>
			<span class="font _14 w100 opacity-70 white-color block">
				<?php echo __("Never logged in","bluerabbit"); ?>
			</span>
		<?php } ?>
		<?php if($isOwner || $isNPC || $isGM || $isAdmin){ ?>
			<button id="<?php echo "button-link-$adv->adventure_id"; ?>" class="form-ui black-bg font _14" onClick="copyTextFrom(<?php echo "'#adventure-link-$adv->adventure_id'"; ?>);">
				<span class="icon icon-qr"></span> <?=$adv->adventure_code;?>
			</button>
			<input id="<?php echo "adventure-link-$adv->adventure_id"; ?>" type="hidden" class="form-ui w-full" value="<?php echo get_bloginfo('url')."/enroll/?enroll_code=$adv->adventure_code"; ?>">
		<?php } ?>
	</div>
	<div class="hex-actions">
		<a class="big-hex-button <?=$adv_color; ?>-bg-400" href="<?=$adventureLink;?>"><span class="icon icon-run"></span></a>
		<?php if($isOwner || $isNPC || $isGM || $isAdmin){ ?>
			<div class="hex-actions-details">
				<a class="small-hex-button edit-button grey-900" href="<?= get_bloginfo('url')."/new-adventure/?adventure_id=$adv->adventure_id"; ?>"><span class="icon icon-edit"></span></a>
				<button class="small-hex-button trash-button red-400" onClick="showOverlay('#confirm-trash-<?php echo $adv->adventure_id; ?>');"><span class="icon icon-delete"></span></button>
				<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $adv->adventure_id; ?>">
					<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $adv->adventure_id; ?>,'adventure','trash');">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
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
			</div>
		<?php }else{ ?>
			<div class="hex-actions-details opacity-40"></div>
		<?php } ?>
	</div>
	<div class="hex-bg <?=$adv_color; ?>-bg-400" style="background-image: url(<?= $adv->adventure_badge; ?>)">
		<a href="<?=$adventureLink;?>" class="block sq-full"></a>
	</div>
	<div class="hex-border <?=$adv_color; ?>-bg-400"></div>
</div>
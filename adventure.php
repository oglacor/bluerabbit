<?php
$adventureLink = get_bloginfo('url')."/adventure/?adventure_id=$adv->adventure_id"; 
$player_role = $adv->player_adventure_role;
$isGM = $adv->player_adventure_role == 'gm' ? true : false;
$isNPC = $adv->player_adventure_role == 'npc' ? true : false;
$isPlayer = $adv->player_adventure_role == 'player' ? true : false;

$isOwner = $adv->adventure_owner == $current_user->ID ? true : false;
$adv_color = $adv->adventure_color ? $adv->adventure_color : 'blue-grey';
?>
	<li class="adventure <?= isset($adv->adventure_type) ? $adv->adventure_type : ""; ?>" id="adventure-<?=$adv->adventure_id;?>">
		<div class="adventure-image <?= $adv_color; ?>-bg-400" style="background-image: url(<?= $adv->adventure_badge; ?>)">
			<a class="background black-bg opacity-70" href="<?=$adventureLink; ?>" >&nbsp;</a>
			<?php if(isset($adv->adventure_type)  && $adv->adventure_type =='template') { ?>
				<div class="adventure-type orange-bg-400 grey-900 text-left">
					<?= __("Template", "bluerabbit"); ?>
				</div>
			<?php } ?>
		</div>
		<div class="adventure-content">
			<div class="adventure-name">
				<h1><a href="<?=$adventureLink; ?>" id="adventure-name-<?= $adv->adventure_id; ?>"><?= $adv->adventure_title; ?></a></h1>
				<?php if($isNPC){ ?>
					<p class="change-title" onClick="activate('#adventure-title-update-<?=$adv->adventure_id;?>');">
							<?= __('Change title','bluerabbit');?>
					
					<?php /* if($adv->player_id){ ?>
						<?php 
						if($isOwner){
							_e("Owner","bluerabbit");
						}else if($isGM){ 
							_e("Game Master","bluerabbit");
						}else if($isNPC){
							_e("NPC","bluerabbit");
						}elseif($isPlayer){
							_e("Player","bluerabbit");
						}
						?> | 
						<?php if($adv->player_last_login){ ?>
							<strong><?= __("Last login","bluerabbit").": ".get_time_ago(strtotime($adv->player_last_login), $adv->adventure_id); ?></strong>
						<?php }else{ ?>
							<strong><?= __("Never logged in","bluerabbit"); ?></strong>
						<?php }  ?>
					<?php }else{ ?>
						<strong><?= __("Not enrolled in adventure","bluerabbit"); ?></strong>
					<?php } */ ?>
					</p>
				<?php } ?>
			</div>
			<div class="adventure-status">
				<p>
					<em><span class="icon icon-level purple-bg-400 white-color"></span><?=$adv->player_level;?></em>
				</p>
				<p>
					<em><span class="icon icon-star amber-bg-400 white-color"></span><?=$adv->player_xp;?></em> 
					<em><span class="icon icon-bloo green-bg-400 white-color"></span><?=$adv->player_bloo;?></em>
					<?php if(isset($adv->player_ep) && $adv->player_ep > 0) { ?>
						<em><span class="icon icon-activity teal-bg-400 white-color"></span><?=$adv->player_ep;?></em>
					<?php } ?>
				</p>
			</div>
			<div class="adventure-play-button">
				<?php if($adv->player_id){ ?>
					<a class="<?= $adv_color; ?>-bg-400 " href="<?= $adventureLink; ?>"><?= __('Play!','bluerabbit'); ?></a>
				<?php }else{ ?>
					<button class="<?= $adv_color; ?>-bg-400" onClick="showOverlay('#confirm-enroll-<?=$adv->adventure_id;?>');">
						<?= __('Enroll','bluerabbit'); ?>
					</button>
					<div class="confirm-action overlay-layer text-center" id="confirm-enroll-<?php echo $adv->adventure_id; ?>">
						<a class="form-ui white-bg" href="<?php echo get_bloginfo('url')."/enroll/?enroll_code=$adv->adventure_code"; ?>">
							<span class="icon-group">
								<span class="icon-button font _24 sq-40  teal-bg-400">
									<span class="icon icon-activity white-color"></span>
								</span>
								<span class="icon-content">
									<span class="line teal-400 font _18 w900"><?php _e("Enroll in this adventure?","bluerabbit"); ?></span>
								</span>
							</span>
						</a>
						<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
							<span class="icon icon-cancel white-color"></span>
						</button>
					</div>
				<?php } ?>
			</div>

			<?php if(($isOwner || $isGM) && ($roles[0] != 'br_npc')){ ?>
				<div class="adventure-actions">
					<div class="adventure-enroll-code blue-grey-bg-700 white-color" onClick="copyTextFrom(<?= "'#adventure-link-$adv->adventure_id'"; ?>);">
						<div class="button-icon">
							<span class="icon icon-qr"></span>
						</div>
						<div class="button-text">
							<h3 class="big-text"><?= __('Enroll code','bluerabbit').": ".$adv->adventure_code;?></h3>
							<h3 class="small-text"><?= __('Click to copy URL','bluerabbit');?></h3>
						</div>
						<input id="<?php echo "adventure-link-$adv->adventure_id"; ?>" type="hidden" class="form-ui w-full" value="<?php echo get_bloginfo('url')."/enroll/?enroll_code=$adv->adventure_code"; ?>">
					</div>
					<a class="form-ui grey-bg-200 blue-grey-800" href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adv->adventure_id"; ?>"><?= __("Manage","bluerabbit"); ?></a>
					<a class="form-ui grey-bg-200 blue-grey-800" href="<?= get_bloginfo('url')."/new-adventure/?adventure_id=$adv->adventure_id"; ?>"><?= __("Edit","bluerabbit"); ?></a>
					<button class="trash-adventure button red-bg-300 form-ui" onClick="showOverlay('#confirm-trash-<?php echo $adv->adventure_id; ?>');">Trash</button>
					<div class="confirm-action overlay-layer red-bg-300" id="confirm-trash-<?php echo $adv->adventure_id; ?>">
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
			<?php } ?>
		</div>
		<?php if($isNPC){ ?>
			<div id="adventure-title-update-<?=$adv->adventure_id;?>" class="adventure-title-update-form">
				<h2 class="font _20 w900"><?= __("New Title","bluerabbit"); ?></h2>
				<input type="text" class="form-ui new-adventure-title" value="<?= $adv->adventure_title;?>"><br>
				<button class="button form-ui red-bg-400 white-color" onClick="activate('#adventure-title-update-<?=$adv->adventure_id;?>');"><?= __("Cancel","bluerabbit"); ?></button>
				<button class="button form-ui green-bg-400 white-color" onClick="updateAdventureTitle(<?=$adv->adventure_id;?>);"><?= __("Save","bluerabbit"); ?></button>
			</div>
			<input type="hidden" id="update-adv-title-nonce-<?=$adv->adventure_id;?>" value="<?= wp_create_nonce('br_update_adv_title_nonce'.$adv->adventure_id); ?>"/>
		<?php } ?>

	</li>

<div class="achievement-element cursor-pointer element-index-<?=$badge_count;?> achievement-type-<?=$a->achievement_display;?>" id="<?= "achievement-card-$a->achievement_id"; ?>" 

	<?php if($a->achievement_applied || $isAdmin || $isNPC || $isGM){ ?> onClick="displayAchievementCard(<?= "$a->achievement_id"; ?>);" <?php } ?> 
	>
	<?= $a->achievement_name;?>

	<input type="hidden" class="achievement-data-id" value="<?= "$a->achievement_id"; ?>">
	<?php if($isNPC || $isGM){ ?>
		<input type="hidden" class="achievement-data-link" value="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$a->adventure_id&achievement_id=$a->achievement_id";?>">
	<?php } ?>
	 
	
	<input type="hidden" class="achievement-data-id" value="<?= "achievement-card-$a->achievement_id"; ?>">
	<div class="achievement-badge <?= ($a->achievement_applied) ? 'earned' : '';?>" style="background-image: url(<?= $a->achievement_badge; ?>);">
		<div class="hidden achievement-name">
			<?= $a->achievement_name;?>
		</div>
	</div>
	<?php if(($a->achievement_applied)){ ?>
		<?php if($a->achievement_display =='rank'){ ?>
			<div class="achievement-rank-decoration">
				<svg class="rank-border" viewBox="0 0 180 156">
				  <polygon class="<?= $a->achievement_color;?>" points="113.58 2 39.4 2 2.31 66.24 39.4 130.48 113.58 130.48 150.67 66.24 113.58 2"/>
				</svg>
				<img class="rank-icon" src="<?= get_bloginfo('template_directory'); ?>/images/icons/icon-rank.png" alt=""/>
			</div>
		<?php } ?>
		<div class="achievement-decoration">
			<svg class="decor-border" viewBox="0 0 180 156">
			  <path class="<?= $a->achievement_color;?>" d="M104.93,13.12l29.93,51.88-29.93,51.88h-59.85l-29.93-51.88L45.07,13.12h59.85M112.5,0H37.5L0,65l37.5,65h75l37.5-65L112.5,0h0Z"/>
			</svg>
			<svg class="decor-corners" viewBox="0 0 150 130">
			  <polygon class="<?= $a->achievement_color;?>" points="144.47 74.58 150 65 144.47 55.42 144.47 60.79 146.9 65 144.47 69.21 144.47 74.58"/>
			  <polygon class="<?= $a->achievement_color;?>" points="105.93 2.69 110.95 2.69 113.45 7.02 118.1 9.71 112.5 0 101.28 0 105.93 2.69"/>
			  <polygon class="<?= $a->achievement_color;?>" points="36.55 7.02 39.05 2.69 44.07 2.69 48.72 0 37.5 0 31.9 9.71 36.55 7.02"/>
			  <polygon class="<?= $a->achievement_color;?>" points="5.53 69.21 3.1 65 5.53 60.79 5.53 55.42 0 65 5.53 74.58 5.53 69.21"/>
			  <polygon class="<?= $a->achievement_color;?>" points="113.45 122.98 110.95 127.31 105.93 127.31 101.28 130 112.5 130 118.1 120.29 113.45 122.98"/>
			  <polygon class="<?= $a->achievement_color;?>" points="44.07 127.31 39.05 127.31 36.55 122.98 31.9 120.29 37.5 130 48.72 130 44.07 127.31"/>
		  </svg>
		</div>
	<?php }else{ ?>
		<div class="achievement-decoration">
			<svg class="decor-corners" viewBox="0 0 150 130">
			  <polygon class="white" points="144.47 74.58 150 65 144.47 55.42 144.47 60.79 146.9 65 144.47 69.21 144.47 74.58"/>
			  <polygon class="white" points="105.93 2.69 110.95 2.69 113.45 7.02 118.1 9.71 112.5 0 101.28 0 105.93 2.69"/>
			  <polygon class="white" points="36.55 7.02 39.05 2.69 44.07 2.69 48.72 0 37.5 0 31.9 9.71 36.55 7.02"/>
			  <polygon class="white" points="5.53 69.21 3.1 65 5.53 60.79 5.53 55.42 0 65 5.53 74.58 5.53 69.21"/>
			  <polygon class="white" points="113.45 122.98 110.95 127.31 105.93 127.31 101.28 130 112.5 130 118.1 120.29 113.45 122.98"/>
			  <polygon class="white" points="44.07 127.31 39.05 127.31 36.55 122.98 31.9 120.29 37.5 130 48.72 130 44.07 127.31"/>
			</svg>
		</div>
	<?php } ?>
	<!--
	<div class="achievement-content padding-5">
		<span class="achievement-name block font _18 uppercase w900"><?= $a->achievement_name;?></span>
		<?php if(($a->achievement_applied) && ($a->achievement_xp || $a->achievement_bloo || $a->achievement_ep )) { ?>
			<span class="achievement-resources font _12 opacity-80">
		<?php if($a->achievement_xp){ ?><span class="inline-block"><span class="icon icon-star amber-bg-400 border rounded-max"></span> <?="$a->achievement_xp $xp_label";?></span> <?php }?>
		<?php if($a->achievement_bloo){ ?><span class="inline-block"><span class="icon icon-bloo green-bg-400 border rounded-max"></span> <?="$a->achievement_bloo $bloo_label";?></span> <?php }?>
		<?php if($a->achievement_ep){ ?><span class="inline-block"><span class="icon icon-activity cyan-bg-400 border rounded-max"></span> <?="$a->achievement_ep $ep_label";?></span> <?php }?>
			</span>
		<?php } ?>
	</div>
	<?php if($isNPC){ ?>
		<a class="button form-ui green font _12 w900" href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$a->adventure_id&achievement_id=$a->achievement_id";?>"><?= __("Assign","bluerabbit"); ?></a>
	<?php } ?>
	<?php if($isGM ){ ?>
		<a class="button form-ui green font _12 w900" href="<?php echo get_bloginfo('url')."/new-achievement/?adventure_id=$a->adventure_id&achievement_id=$a->achievement_id";?>"><?= __("Edit","bluerabbit"); ?></a>
	<?php } ?>
-->
</div>

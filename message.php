<li class="message <?php echo $m->ann_type; ?> <?php if($m->ann_author == $current_user->ID) { echo 'mine'; }?>" id="ann-<?php echo $m->ann_id; ?>">
		
	<div class="message-profile-picture">
		<?php if($m->ann_type =="announcement"){ ?>
			<span class="icon-button font _24 sq-40  pink-bg-400 player-picture"><span class="icon-megaphone icon"></span></span>
		<?php }else{?>
			<span class="icon-button font _24 sq-40  player-picture <?php if($m->ann_author == $current_user->ID) { echo 'mine'; }?>" style="background-image: url('<?= $m->player_picture; ?>');">
				<span class="icon"></span>
			</span>
		<?php } ?>
	</div>
	<div class="message-content layer base relative overflow-hidden">
		<?php if($m->ann_type =="announcement"){ ?>
			<div class="layer background absolute sq-full" style="background-image: url(<?= $m->player_picture; ?>);"></div>
		<?php }else{ ?>
			<div class="layer background absolute sq-full mix-blend-overlay opacity-50 blur1" style="background-image: url(<?= $m->player_picture; ?>);"></div>
		<?php } ?>
		<div class="layer base relative">
			<h4 class="author <?php if($m->ann_author == $current_user->ID) { echo 'mine'; }?>"><?php echo $m->player_display_name." ".__("says","bluerabbit"); ?>:</h4>
			<?php echo apply_filters('the_content',$m->ann_content); ?>
			<h6 class="date"> <?php echo date('F jS Y, g:iA', strtotime($m->ann_date)) ; ?></h6>
		</div>
	</div>
	<?php if($announcements['isTeacher']) {?>
		<div class="message-settings layer base">
			<button class="icon-button font _24 sq-40  icon-sm red-bg-400" onClick="br_confirm_trd(<?php echo "'trash', $m->ann_id, 'announcement'"; ?>);"><span class="icon icon-trash"></span></button>
		</div>
	<?php } ?>
	<br class="clear">
</li>

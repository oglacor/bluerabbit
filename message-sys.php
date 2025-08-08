
<li class="message <?php echo $m->ann_type; ?>" id="ann-<?php echo $m->ann_id; ?>">
	<div class="message-profile-picture">
		<span class="icon-button font _24 sq-40  player-picture" style="background-image: url('<?php echo $m->player_picture; ?>');">
		</span>
	</div>
	<div class="message-content white-bg">
		<div class="font _16"><?php echo $m->ann_content; ?></h4>
		<h6 class="font _12"> <?php echo date('F jS Y, g:iA', strtotime($m->ann_date)) ; ?></h6>
	</div>
</li>

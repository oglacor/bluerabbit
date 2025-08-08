<li class="message <?php echo $m->ann_type; ?> <?php if($m->ann_author == $current_user->ID && $m->ann_type != 'announcement') { echo 'text-right'; }?> theme-border" id="ann-<?php echo $m->ann_id; ?>">
	<div class="message-meta">
		<span class="icon-button font _24 sq-40  player-picture" style="background-image: url('<?php echo $m->player_picture; ?>');"><span class="icon"></span></span>
		<h4 class="author"><?php echo $m->ann_content; ?></h4>
		<h6 class="date"> <?php echo date('F jS Y, g:iA', strtotime($m->ann_date)) ; ?></h6>
	</div>
</li>

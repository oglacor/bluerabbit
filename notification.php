
<?php if($n->post_author == $current_user->ID) { $align='text-right'; }else{$align ="";} ?>

<li class="notification <?php echo $n->post_type; ?>" id="notification-id-<?= $n->ID; ?>">
	<div class="notification-header">
		<?php if($n->post_type=='announcement'){ ?>
			<span class="icon icon-megaphone icon-button font _24 sq-40  red"></span>
			<h4 class="author <?php echo $align; ?>"><?php echo $n->post_author_name." ".__("says","bluerabbit"); ?>:</h4>
		<?php }elseif($n->post_type=='public'){ ?>
			<h4 class="author <?php echo $align; ?>"><?php echo $n->post_author_name." ".__("says","bluerabbit"); ?>:</h4>
		<?php }elseif($n->post_type=="auto"){ ?>
			<span class="icon-button font _24 sq-40  blue icon icon-logo"></span>
			<h4 class="author <?php echo $align; ?>"><?php echo "BLUErabbit ".__("says","bluerabbit"); ?>:</h4>
		<?php } ?>
		<h6 class="date <?php echo $align; ?>"><?php echo $n->post_date; ?></h6>
	</div>
		
	<div class="notification-content <?php echo $align; ?>">
		<?php echo $n->post_content; ?>
	</div>
		
		
		
</li>

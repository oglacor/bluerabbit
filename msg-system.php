<?php $announcements = getAnnouncements($adventure_id,'system'); ?>
<?php if($announcements){ ?>
	<ul class="feed">
		<?php foreach($announcements['anns'] as $m){ ?>
			<?php include (TEMPLATEPATH . '/message-sys.php'); ?>
		<?php } ?>
		<li class="clear"></li>
	</ul>
<?php }else{ ?>
	<h4 class="grey c-400 text-center">- <?php _e("No messages","bluerabbit"); ?> -</h4>
<?php } ?>

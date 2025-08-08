<?php $announcements = getAnnouncements($adventure_id,"guild-$guild_id"); ?>
<?php if($announcements){ ?>
	<ul class="feed">
		<?php foreach($announcements['anns'] as $m){ ?>
			<?php include (TEMPLATEPATH . '/message.php'); ?>
		<?php } ?>
		<li class="clear"></li>
	</ul>
<?php }else{ ?>
	<h4 class="grey c-400 text-center">- <?php _e("No messages","bluerabbit"); ?> -</h4>
<?php } ?>

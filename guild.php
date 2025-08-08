<li class="card <?php echo $g->guild_color; ?>-border-400 border-2 border" id="guild-<?php echo $g->guild_id; ?>">
	
	<img src="<?= $g->guild_logo; ?>" class="badge cursor-pointer border rounded-max"  onClick="loadGuildCard(<?= "$g->guild_id,'#guild-$g->guild_id'"; ?>);">

	<?php if($g->assign_on_login){ ?>
		<img class="rotate-Z-L-20 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
		<img class="rotate-Z-R-30 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
		<img class="rotate-Z-L-40 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
		<img class="rotate-Z-R-60 mix-blend-overlay halo floor-halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
		<img class="rotate-Z-L-90 mix-blend-overlay halo floor-halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
	<?php }else{ ?>
		<div class="border rounded-20 border-all border-3 opacity-50 white-border rotate-Z-R-10 halo floor-halo" style="width: 160px; height: 160px">
			<img class="" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>" width="100%">
		</div>
	<?php } ?>
	<div class="highlight text-center font _24 w300 white-color">
		<?= $g->guild_name; ?>
	</div>
</li>




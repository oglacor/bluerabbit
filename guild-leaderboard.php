<li class="card" id="guild-lb-<?php echo $g->guild_id; ?>">
	<img src="<?= $g->guild_logo; ?>" class="badge border rounded-max">
	<img class="rotate-Z-R-20 mix-blend-overlay halo floor-halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
	<div class="highlight text-center font _24 w300 white-color">
		<span class="button-form-ui <?= $g->guild_color; ?>-bg-400 font _20">
			<?= $g->guild_name; ?>
		</span><br>
		<span class="button-form-ui amber-bg-200 font _14 blue-grey-700 w900 kerning-1" id="guild-lb-xp-<?php echo $g->guild_id; ?>">
			<span class="icon icon-star"></span>
			<span class="number">0</span>
			<input type="hidden" class="end-value" value="<?= $g->total_player_xp; ?>">
		</span>
		
	</div>
	<script>animateNumber('#guild-lb-xp-<?=$g->guild_id; ?>');</script>
</li>




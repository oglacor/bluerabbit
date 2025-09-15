

<div class="background blue-grey-bg-700 opacity-60 fixed" onClick="unloadCard();"></div>
<div class="card card-scene " id="<?= "guild-$g->guild_id"; ?>">
	<div class="card-content">
		<div class="card-face frontface">
			<?php if($isGM || $isNPC || $isAdmin){ ?>
				<a class="layer foreground icon-button font _14 sq-20 absolute top-10 left-10 green-bg-400" href="<?= get_bloginfo("url")."/new-guild/?guild_id=$g->guild_id&adventure_id=$g->adventure_id"; ?>">
					<span class="icon icon-edit"></span>
				</a>
			<?php } ?>
			<button class="layer foreground absolute icon-button font _14 sq-20  top-10 right-10 red-bg-400" onClick="unloadCard();"><span class="icon icon-cancel"></span></button>

			<div class="layer background absolute sq-full top left blend-luminosity grey-bg-900 opacity-80" style="background-image: url(<?= $g->guild_logo; ?>);"></div>
			<div class="layer background absolute sq-full top left grey-bg-900 opacity-80"></div>
			<div class="layer background absolute sq-full top left <?=$g->guild_color;?>-gradient-900 opacity-60"></div>
			<div class="layer base absolute sq-full top left">
				<div class="card-type text-center lime-bg-400 blue-grey-900" >
					<span class="inline-block" id="xp-number-g-<?=$g->guild_id; ?>">
						<span class="icon icon-star"></span>
						<span class="number">0</span>
						<input type="hidden" class="end-value" value="<?= $guild_xp; ?>">
					</span>
					<script>animateNumber('#xp-number-g-<?=$g->guild_id; ?>',1500,1500);</script>
					<span class="inline-block" id="bloo-number-g-<?=$g->guild_id; ?>">
						<span class="icon icon-bloo"></span>
						<span class="number">0</span>
						<input type="hidden" class="end-value" value="<?= $guild_bloo; ?>">
					</span>
					<script>animateNumber('#bloo-number-g-<?=$g->guild_id; ?>',1500,1500);</script>
				</div>
				<div class="layer base perfect-center absolute">
					<div class="badge-container">
						<img src="<?= $g->guild_logo; ?>" class="badge" >
						<img class="rotate-L-20 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
						<img class="rotate-R-30 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
						<img class="rotate-L-40 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
						<img class="rotate-R-60 mix-blend-overlay halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
						<img class="rotate-L-90 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
					</div>
					<div class="text-center w-full">
						<h1 class="font _30 w600 condensed kerning-1 padding-10"><?= $g->guild_name; ?></h1>
						<a href="<?= get_bloginfo('url')."/guilds/?adventure_id=$g->adventure_id"; ?>" class="form-ui grey-bg-100 blue-grey-900 font _16 w900">
							<?= __("View Guilds","bluerabbit"); ?>
						</a>
					</div>
						
					<?php if($isGM || $isAdmin || $isNPC){ ?>
						<div class="card-message padding-10 white-color text-center">
							<?php if($guild_players ){ ?>
								<button class="form-ui <?= ($g->guild_color == 'yellow') ? "grey-800 $g->guild_color" : $g->guild_color; ?>-bg-400" onClick="showOverlay('#guild-details-<?=$g->guild_id; ?>')">
									<?= __("Guild Members","bluerabbit"); ?>
								</button>
							<?php }else{ ?>
								<h2 class="white-color font _24 w900 kerning-3 padding-10 uppercase text-center">
									<?= __("No Members","bluerabbit"); ?>
								</h2>
								<h3 class="font _14 w100"><?= __("Share the enroll link","bluerabbit"); ?></h3>
								<button id="<?= "button-link-$g->guild_id"; ?>" class="form-ui blue-bg-800 font _16 normal" onClick="copyTextFrom(<?= "'#guild-link-$g->guild_id'"; ?>);">
									<?= __("Click to copy enroll code","bluerabbit"); ?>
								</button>
								<input id="<?= "guild-link-$g->guild_id"; ?>" type="hidden" class="form-ui w-full" value="<?= get_bloginfo('url')."/guild-enroll/?adventure_id=$g->adventure_id&t=$g->guild_code"; ?>">
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="card-face backface">
			<div class="layer base absolute sq-full <?=$g->guild_color; ?>-bg-400" style="background-image: url(<?= $adventure->adventure_badge; ?>);"></div>
			<div class="layer base absolute sq-full <?=$g->guild_color; ?>-gradient-500"></div>
			<div class="layer base absolute perfect-center sq-200 border border-all rounded-max <?=$g->guild_color; ?>-border-400 border-4" style="background-image: url(<?= $g->guild_logo; ?>);"></div>
		</div>
	</div>


	
	
	
	


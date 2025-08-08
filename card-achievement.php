<div class="background blue-grey-bg-700 opacity-60 fixed" onClick="unloadCard();"></div>
	<div class="card card-scene " id="<?= "achievement-$a->achievement_id"; ?>">
		<div class="card-content">
			<div class="card-face frontface">
				<?php if($isGM || $isNPC || $isAdmin){ ?>
					<a class="layer foreground icon-button font _14 sq-20 absolute top-10 left-10 green-bg-400" href="<?php echo get_bloginfo("url")."/new-achievement/?achievement_id=$a->achievement_id&adventure_id=$adv_parent_id"; ?>">
						<span class="icon icon-edit"></span>
					</a>
				<?php } ?>
				<button class="layer foreground absolute icon-button font _14 sq-20  top-10 right-10 red-bg-400" onClick="unloadCard();"><span class="icon icon-cancel"></span></button>
				<div class="layer background absolute sq-full top left blend-luminosity grey-bg-900 opacity-80" style="background-image: url(<?= $a->achievement_badge; ?>);"></div>
				<div class="layer background absolute sq-full top left grey-bg-900 opacity-80"></div>
				<div class="layer background absolute sq-full top left <?=$a->achievement_color;?>-gradient-900 opacity-80"></div>
				<div class="layer base absolute sq-full top left">
					<div class="card-type text-center purple-bg-400" >
						<div class="">
							<span class="inline-block" id="xp-number-a-<?=$a->achievment_id; ?>">
								<span class="icon icon-star amber-500"></span>
								<span class="number">0</span>
								<input type="hidden" class="end-value" value="<?=$a->achievement_xp; ?>">
							</span>
							<script>animateNumber('#xp-number-a-<?=$a->achievment_id; ?>',1500,1500);</script>
							<span class="inline-block" id="bloo-number-a-<?=$a->achievment_id; ?>">
								<span class="icon icon-bloo lime-500"></span>
								<span class="number">0</span>
								<input type="hidden" class="end-value" value="<?= $a->achievement_bloo; ?>">
							</span>
							<script>animateNumber('#bloo-number-a-<?=$a->achievment_id; ?>',2500,1500);</script>
						</div>
					</div>
					<div class="layer base perfect-center absolute">
						<div class="badge-container">
							<img src="<?= $a->achievement_badge; ?>" class="badge" >
							<img class="rotate-L-20 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a1.png";?>">
							<img class="rotate-R-30 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a2.png";?>">
							<img class="rotate-L-40 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a3.png";?>">
							<img class="rotate-R-60 mix-blend-overlay halo " src="<?= get_bloginfo('template_directory')."/images/a4.png";?>">
							<img class="rotate-L-90 mix-blend-overlay halo opacity-20" src="<?= get_bloginfo('template_directory')."/images/a5.png";?>">
						</div>
						<div class="text-center padding-10 white-color">
							<h1 class="font _40 w300 kerning-1"><?= $a->achievement_name; ?></h1>
							<?php if($a->achievement_applied){ ?>
								<h3 class="text-center font _14 opacity-70">
									<span class="icon icon-time"></span>
									<?= __("earned","bluerabbit")." ".get_time_ago(strtotime($a->achievement_applied), $adv_child_id); ?>
								</h3>
							<?php } ?>
						</div>
						<?php if($a->achievement_content){ ?>
							<div class="card-message padding-10 white-color">
								<?= apply_filters('the_content',$a->achievement_content); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="card-face backface">
				<div class="layer base absolute sq-full <?=$a->achievement_color; ?>-bg-400" style="background-image: url(<?= $adventure->adventure_badge; ?>);"></div>
				<div class="layer base absolute sq-full <?=$a->achievement_color; ?>-gradient-500"></div>
				<div class="layer foreground absolute perfect-center mix-blend-overlay">
					<span class="relative block border border-all rounded-max border-10 white-color sq-200 padding-20">
						<?php if($a->achievement_display =='rank'){
								$icon = 'rank';
							}elseif($a->achievement_display =='path'){
								$icon = 'story';
							}else{
								$icon = 'achievement';
							}
						?>
						<span class="icon icon-<?=$icon;?> perfect-center font _100"></span>
					</span>
				</div>
			</div>
		</div>
	</div>
		<div class="loader layer fixed overlay-layer top left sq-full" id="loader"> 
			<div class="background grey-bg-900 icons-bg-light opacity-60" onClick="hideAllOverlay();"></div>
			<div class="background indigo-bg-900 opacity-60" onClick="hideAllOverlay();"></div>
			<img class="animated perfect-center layer base absolute" src="<?php bloginfo('template_directory'); ?>/images/loader.svg" width="150">
		</div>
		<div class="small-loader loader layer fixed overlay-layer bottom-70 right-10" id="small-loader"> 
			<img class="animated" src="<?php bloginfo('template_directory'); ?>/images/loader.svg" width="50">
		</div>
		<div class="overlay-bg overlay-layer " onClick="hideAllOverlay();"></div>
		<div class="feedback overlay-layer layer fixed white-color" id="feedback">
			<div class="background opacity-80 black-bg layer absolute base" onClick="hideAllOverlay();"></div>
			<div class="perfect-center layer relative base">
				<div class="foreground content">
				</div>
			</div>
		</div>
		<div class="w-full white-color font _14 w300 layer base fixed bottom padding-10  black-bg">
			<?php $globalsponsors = getSponsors(); ?>
			<?php if(isset($globalsponsors)){ ?>
				<div class="text-center">
				<?php foreach($globalsponsors as $k=>$gs){ ?>
				<a class="opacity-40 inline-block padding-5" href="<?= $gs->sponsor_url ? $gs->sponsor_url : "#";?>" target="_blank"><img src="<?= $gs->sponsor_logo;?>" height="40"></a>
				<?php } ?>
				</div>
			<?php } ?>
			<div class="text-right">
				<a class="opacity-40" target="_blank" href="mailto:help@bluerabbit.io"><span class="icon icon-warning"></span><?= __("Email Support","bluerabbit"); ?></a>
			</div>
		</div>

		<?php wp_footer(); ?>
	</body>
</html>
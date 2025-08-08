<div class="card" id="keyword-card-<?= $c->objective_id; ?>">
	<div class="background teal-bg-800 blend-overlay"></div>
	<?php $bg = isset($m) ? $m->mech_badge : get_bloginfo('template_directory').'/images/polygons.png'; ?>
	<div class="background mix-blend-overlay opacity-30  background-image" style="background-image: url(<?= $bg; ?>)"></div>
	<div class="background mix-blend-overlay grey-gradient-900 opacity-50"></div>
	<div class="background blue-grey-gradient-900  "></div>
	<div class="layer base relative text-center">
		<h4 class="layer base white-color text-center font _14 uppercase special w-full padding-5">
			<span class="icon icon-check"></span>
			<?= __("Completed","bluerabbit");?>
		</h4>
		<div class="font w100 _16 white-color text-center">
			<?= apply_filters('the_content', $c->objective_content); ?>
		</div>
	</div>
	<h3 class="layer base light-green-400 text-center cursor-pointer" onClick="showOverlay('#success-message-<?= $c->objective_id; ?>');">
		<span class="icon icon-key"></span>
		<span class="icon icon-arrow-right"></span>
		<?= $c->objective_keyword; ?>
	</h3>
</div>

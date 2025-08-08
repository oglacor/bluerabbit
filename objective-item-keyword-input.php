<div class="card" id="keyword-card-<?= $c->objective_id; ?>">
	<div class="background teal-bg-800 blend-overlay"></div>
	<?php $bg = isset($m) ? $m->mech_badge : get_bloginfo('template_directory').'/images/polygons.png'; ?>
	<div class="background mix-blend-overlay opacity-30  background-image" style="background-image: url(<?= $bg; ?>)"></div>
	<div class="background mix-blend-overlay grey-gradient-900 opacity-50"></div>
	<div class="background blue-grey-gradient-900  "></div>
	<div class="layer base relative text-center">
		<div class="font w100 _16 white-color padding-10 text-center">
			<?= apply_filters('the_content', $c->objective_content); ?>
		</div>
		<div class="input-group inline-table">
			<label class="teal-bg-A400 teal-900"><span class="icon icon-key"></span></label>
			<input autocomplete="off" id="keyword-input-<?= $c->objective_id; ?>"type="text" class="form-ui keyword" onChange="factCheck(<?= $c->objective_id; ?>);" value="">
		</div>
	</div>
</div>

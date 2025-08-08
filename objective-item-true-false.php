<div class="card" id="keyword-card-<?= $c->objective_id; ?>">
	<div class="background pink-bg-800 blend-overlay"></div>
	<?php $bg = isset($m) ? $m->mech_badge : get_bloginfo('template_directory').'/images/polygons.png'; ?>
	<div class="background mix-blend-overlay opacity-30  background-image" style="background-image: url(<?= $bg; ?>)"></div>
	<div class="background mix-blend-overlay grey-gradient-900 opacity-50"></div>
	<div class="background blue-grey-gradient-900  "></div>
	<div class="layer base relative text-center">
		<div class="font w100 _16 white-color padding-10 text-center">
			<?= apply_filters('the_content', $c->objective_content); ?>
		</div>
		<div class="input-group inline-table">
			<button class="icon-button font _24 sq-40  blue-grey-bg-900 teal-A400 icon-lg border border-all border-2 teal-border-500" onClick="$('#keyword-input-<?= $c->objective_id; ?>').val('True'); factCheck(<?= $c->objective_id; ?>);">
				<span class="icon icon-like"></span>
			</button>
			<button class="icon-button font _24 sq-40  blue-grey-bg-900 teal-A400 icon-lg border border-all border-2 teal-border-500"  onClick="$('#keyword-input-<?= $c->objective_id; ?>').val('False'); factCheck(<?= $c->objective_id; ?>);">
				<span class="icon icon-dislike"></span>
			</button>
			<input id="keyword-input-<?= $c->objective_id; ?>" type="hidden" value="">
		</div>
	</div>
</div>

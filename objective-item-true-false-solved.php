<div class="card" id="keyword-card-<?= $c->objective_id; ?>">
	<h4 class="layer base white-color text-center font _14 uppercase special w-full padding-5">
		<span class="icon icon-check"></span>
		<?= __("Completed","bluerabbit");?>
	</h4>
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
			<?php if($c->objective_keyword == 'True'){ ?>
				<span class="icon-button font _24 sq-40  square blue-grey-900 teal-bg-A400 icon-lg">
					<span class="icon icon-like"></span>
				</span>
			<?php }elseif($c->objective_keyword == 'False'){ ?>
				<span class="icon-button font _24 sq-40  square blue-grey-900 teal-bg-A400 icon-lg">
					<span class="icon icon-dislike"></span>
				</span>
			<?php } ?>
		</div>
	</div>
</div>

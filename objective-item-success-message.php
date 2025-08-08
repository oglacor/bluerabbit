<div class="fixed layer feedback overlay-layer top left sq-full" onClick="hideAllOverlay();" id="success-message-<?= $c->objective_id; ?>">
	<div class="layer background black-bg opacity-90 absolute"></div>
	<div class="layer base perfect-center relative">
		<h3 class="light-green-400 padding-5 font _18 w900">
			<span class="icon icon-key"></span>
			<span class="icon icon-arrow-right"></span>
			<?= $c->objective_keyword; ?>
		</h3>
		<div class="white-color padding-5">
			<?= apply_filters('the_content', $c->objective_success_message); ?>
		</div>
	</div>
</div>

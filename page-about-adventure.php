<?php include (TEMPLATEPATH . '/header.php'); ?>
<div class="boxed max-w-900 layer base relative white-color padding-20">
	<div class="text-center padding-10">
		<span class="icon-button sq-300 white-bg border border-1 border-all white-border" style="background-image: url(<?= $adventure->adventure_badge; ?>); "></span>
		<h2 class="font _48 white-color"><?= $adventure->adventure_title; ?></h2>
	</div>
	<div class="content">
		<?= apply_filters('the_content', $adventure->adventure_instructions);?>
	</div>
	<div class="padding-20 text-center">
		<button class="form-ui blue-bg-400" onClick="closeIntro();">
			<span class="icon icon-freespirit"></span> <?= __("On to the adventure!","bluerabbit"); ?>
		</button>
	</div>
</div>
<?php include (TEMPLATEPATH . '/footer.php'); ?>

	<div class="highlight text-center padding-0 white-color font _20">
		<h3 class="font uppercase _18 w300 kerning-2 padding-10 foreground"><?= __("Can purchase in","bluerabbit"); ?></h3>
		<span class="icon-group font special padding-20  white-color border rounded-max" id="deadline-countdown">
			<div class="background black-bg opacity-60  border rounded-max"></div>
			<span class="font special icon-button font _20 sq-40  orange-300 transparent-bg" id="deadline-days">
				<span class="number"></span>
				<span class="legend active bottom font _10 text-center lowercase main"><?= __("Days","bluerabbit"); ?></span>
				<span class="halo rotate-L-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
			</span>
			<span class="font special icon-button font _20 sq-40  cyan-600 transparent-bg" id="deadline-hours">
				<span class="number"></span>
				<span class="legend active bottom font _10 text-center lowercase main"><?= __("Hours","bluerabbit"); ?></span>
				<span class="halo rotate-R-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
			</span>
			<span class="font special icon-button font _20 sq-40  cyan-600 transparent-bg" id="deadline-minutes">
				<span class="number"></span>
				<span class="legend active bottom font _10 text-center lowercase main"><?= __("Minutes","bluerabbit"); ?></span>
				<span class="halo rotate-L-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
			</span>
			<span class="font special icon-button font _20 sq-40  blue-grey-200 transparent-bg" id="deadline-seconds">
				<span class="number"></span>
				<span class="legend active bottom font _10 text-center lowercase main"><?= __("Seconds","bluerabbit"); ?></span>
				<span class="halo rotate-R-30" style="background-image: url(<?= get_bloginfo('template_directory')."/images/countdown-halo.png"; ?>)"></span>
			</span>
		</span>
		<script>
			deadlineCountdown('<?= date('M j, Y H:i:s',strtotime($id)); ?>');
		</script>
	</div>

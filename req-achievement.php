<!-- EARNED AS TEACHER -->
<?php  $earned = ($a->achievement_applied) ? true : false; ?>
	<div class="card achievement" id="<?= "req-item-$a->achievement_id"; ?>">
		<div class="background <?=$mi_color; ?> border rounded-8 blend-overlay"></div>
		<div class="background mix-blend-overlay border rounded-8 opacity-30  background-image" style="background-image: url(<?= $a->achievement_badge; ?>);"></div>
		<div class="background mix-blend-overlay border rounded-8 grey-gradient-900 opacity-50"></div>
		<div class="background blue-grey-gradient-900 border rounded-8"></div>
		<div class="layer base relative border rounded-8 text-center">
			<?php if($earned){ ?>
				<h3 class="font _24 w900 white-color"><?= $a->achievement_name; ?></h3>
				<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $a->achievement_badge; ?>);">
				</div>
				<span class="icon-button absolute font _36 lime-bg-400 layer overlay req-status">
					<span class="icon-check lime-900 perfect-center"></span>
				</span>
				<br>
				<button class="form-ui font _18 green-bg-400" onClick="loadAchievementCard(<?= $a->achievement_id; ?>);">
					<?= __("View","bluerabbit"); ?>
				</button>
			<?php }else{?>
				<h3 class="font _24 w900 white-color"><?= $a->achievement_name; ?></h3>
				<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $a->achievement_badge; ?>);">
				</div>
				<span class="icon-button perfect-center absolute font _36 red-bg-400 layer overlay req-status">
					<span class="icon-cancel perfect-center"></span>
				</span>
				<br>
				<button class="form-ui font _18 amber-bg-400 deep-purple-800" onClick="showOverlay('#magic-code-form');">
					<?= __("Try a magic code","bluerabbit"); ?>
				</button>
			<?php }?>
		</div>
	</div>

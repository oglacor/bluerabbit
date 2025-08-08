	<div class="milestone <?= "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="purple">
		<input type="hidden" class="milestone-data-type" value="levelup">
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2 class="red-bg-400"><?= $mi->mech_level; ?></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<h2 class="purple-bg-A400"> <span class="icon icon-lock inline-block"></span> <?= __("Available at level","bluerabbit")." $mi->mech_level"; ?></h2>
			</div>
		</div>
		<div class="milestone-bg-color black-bg opacity-20"></div>
		<div class="milestone-bg-badge grey-bg-800 blend-luminosity opacity-50" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="white-color font _18 w600 inline-block absolute perfect-center block">
				<span class="icon icon-lock font _30 white-color"></span>
			</span>
		</div>
		<?php if($isGM || $isAdmin){ ?>
			<a class="hex-button flat milestone-edit-button layer foreground" href="<?= get_bloginfo("url")."/new-$mi->quest_type/?questID=$mi->quest_id&adventure_id=$mi->adventure_id"; ?>">
				<div class="hex layer deep-bg green-bg-400 opacity-80"></div>
				<div class="perfect-center layer absolute base font condensed _12 w100 white-color w-full text-center">
					<span class="icon icon-edit inline-block"></span>
				</div>
			</a>
		<?php } ?>
		<div class="milestone-activate-button" onClick="activateMilestone(<?= $elementID; ?>, '#ui-touch-milestone','#ui-touch-milestone-reverse');"></div>
	</div>

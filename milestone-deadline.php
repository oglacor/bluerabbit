	<div class="milestone <?php echo "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="red">
		<input type="hidden" class="milestone-data-type" value="deadline-missed">
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2><span class="icon icon-lock"></span></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<h2 class="red-bg-A400 white-color padding-5">
					<span class="icon icon-lock inline-block"></span>
					<?= __("Deadline missed","bluerabbit")." ".date('D, M jS, Y | H:i',strtotime($mi->mech_deadline)); ?>
				</h2>
			</div>
		</div>
		<div class="milestone-bg-color red-bg-A400"></div>
		<div class="milestone-bg-badge red-bg-900 blend-luminosity" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="icon icon-deadline red-A400 font _30 inline-block absolute perfect-center block"></span>
			<?php if($isAdmin || $isGM){ ?>
				<span class="absolute v-center left block icon icon-<?= $mi->quest_type; ?> font _24 black-color opacity-50"></span>
			<?php } ?>
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

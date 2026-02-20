	<div class="milestone finished <?php echo "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>" style="<?= $scale; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="<?= $mi->mech_color; ?>">
		<input type="hidden" class="milestone-data-type" value="finished">
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2 class="blue-grey-900 lime-bg-A400"><span class="icon icon-check"></span></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<h2 class="finished-legend"><?= __("Finished","bluerabbit"); ?></h2>
				<?php if($mi->quest_secondary_headline){ ?>
					<p>
						<?= $mi->quest_secondary_headline; ?>
					</p>
				<?php } ?>
				<a class="form-ui button lime-bg-500 blue-grey-900" href="<?= $permalink; ?>">
					<span class="icon icon-<?= $mi->quest_icon ? $mi->quest_icon : 'run'; ?> inline-block"><?= __("Review","bluerabbit"); ?></span>
				</a>
			</div>
		</div>
		<div class="milestone-bg-color lime-bg-A400"></div>
		<div class="milestone-bg-badge green-bg-500 blend-luminosity" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="icon icon-check lime-A400 font _30 inline-block absolute perfect-center block"></span>
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

	<div class="milestone <?= "$mi->quest_type finshed $hideByDay $left_side level{$mi->mech_level}";  ?>" id="milestone-<?= $elementID; ?>" style="<?= $scale; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="<?= $mi->quest_color; ?>">
		<input type="hidden" class="milestone-data-type" value="available">
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2><?= $mi->mech_level; ?></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<?php if($mi->quest_secondary_headline){ ?>
					<p>
						<?= $mi->quest_secondary_headline; ?>
					</p>
				<?php } ?>
				<div class="milestone-preview-actions">
					<a class="form-ui button green-bg-400" href="<?= $permalink ; ?>"><?= __("Do it now!","bluerabbit"); ?></a>
				</div>
			</div>
		</div>
		<div class="milestone-level">
			<h2><?= $mi->mech_level; ?></h2>
		</div>
		<div class="milestone-bg-color <?= $mi->quest_color; ?>-bg-400"></div>
		<div class="milestone-bg-badge" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<?php if($isAdmin || $isGM){ ?>
				<span class="absolute v-center left block icon icon-<?= $mi->quest_type; ?> font _24 black-color opacity-50"></span>
			<?php } ?>
		</div>
		<?php if($isGM || $isAdmin){ ?>
			<a class="hex-button flat milestone-edit-button layer foreground" href="<?= get_bloginfo("url")."/new-$mi->quest_type/?questID=$mi->quest_id&adventure_id=$adv_parent_id"; ?>">
				<div class="hex layer deep-bg green-bg-400 opacity-80"></div>
				<div class="perfect-center layer absolute base font condensed _12 w100 white-color w-full text-center">
					<span class="icon icon-edit inline-block"></span>
				</div>
			</a>
		<?php } ?>
		<div class="milestone-activate-button" onClick="activateMilestone(<?= $elementID; ?>, '#ui-touch-milestone','#ui-touch-milestone-reverse');"></div>
	</div>

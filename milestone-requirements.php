	<div class="milestone <?php echo "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>" style="<?= $scale; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="amber">
		<input type="hidden" class="milestone-data-type" value="requirements">
        <div class="milestone-modal-content">
            <div class="milestone-image" style="background-image: url(<?= $mi->mech_badge; ?>);">
            </div>
            <h1 class="milestone-headline"><?= $mi->quest_title; ?></h1>
            <?php if($mi->quest_secondary_headline){ ?>
                <h2 class="milestone-subheadline">
                    <?= $mi->quest_secondary_headline; ?>
                </h2>
            <?php } ?>
            <div class="milestone-action">
                <button class="action-button requirements">
                    <?= __("Complete the required quests first","bluerabbit"); ?>
                </button>
            </div>
            <div class="milestone-modal-divider"></div>
            <h2 class="milestone-level"><?= __("Level","bluerabbit"); ?>: <?= $mi->mech_level; ?></h2>
            <h2 class="milestone-xp"><?=$xp_label; ?>: <?= $mi->mech_xp; ?></h2>
            <h2 class="milestone-bloo"><?=$bloo_label; ?>: <?= $mi->mech_bloo; ?></h2>
            <?php if($mi->mech_ep > 0){ ?>
                <h2 class="milestone-ep"><?=$ep_label; ?>: <?= $mi->mech_ep; ?></h2>
            <?php } ?>
  			<?php if($isGM || $isAdmin){ ?>
				<a class="milestone-gm-actions form-ui" href="<?= get_bloginfo("url")."/new-$mi->quest_type/?questID=$mi->quest_id&adventure_id=$adv_parent_id"; ?>">
					<span class="icon icon-edit inline-block"></span> <?= __("Edit","bluerabbit"); ?>
				</a>
			<?php } ?> 
       </div>
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2><span class="icon icon-lock"></span></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<h2 class="amber-bg-A400 blue-grey-900 padding-5">
					<span class="icon icon-lock inline-block"></span>
					<?= __("Complete the required quests first","bluerabbit"); ?>
				</h2>
			</div>
		</div>
		<div class="milestone-bg-color amber-bg-A400"></div>
		<div class="milestone-bg-badge amber-bg-900 blend-luminosity" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="icon icon-lock amber-A400 font _30 inline-block absolute perfect-center block"></span>
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

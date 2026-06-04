	<div class="milestone opacity-30 unavailable <?php echo "$mi->quest_type $hideByDay level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>" style="<?= $scale; ?>">
		<div class="milestone-bg-color purple-bg-A400 opacity-50"></div>
		<div class="milestone-bg-color grey-bg-300 opacity-20"></div>
		<div class="milestone-bg-badge grey-bg-100 blend-luminosity opacity-50" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="white-color font _18 w600 inline-block absolute perfect-center block">
				<span class="icon icon-lock font _30 white-color"></span>
			</span>
		</div>
		<?php if($isGM || $isAdmin){ ?>
            <input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
            <input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
            <input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
            <input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
            <input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
            <input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
            <input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
            <input type="hidden" class="milestone-data-color" value="grey">
            <input type="hidden" class="milestone-data-type" value="levelup">
            <div class="milestone-modal-content">
                <div class="milestone-image" style="background-image: url(<?= $mi->mech_badge; ?>);">
                </div>
                <h1 class="milestone-headline"><?= $mi->quest_title; ?></h1>
                <?php if($mi->quest_secondary_headline){ ?>
                    <h2 class="milestone-subheadline">
                        <?= $mi->quest_secondary_headline; ?>
                    </h2>
                <?php } ?>
                <div class="milestone-action ">
                    <button class="action-button unavailable">
                        <span class="icon icon-lock"></span>
                    </button>
                </div>
                <div class="milestone-modal-divider"></div>
                <h2 class="milestone-level"><?= __("Level","bluerabbit"); ?>: <?= $mi->mech_level; ?></h2>
                <h2 class="milestone-xp"><?=$xp_label; ?>: <?= $mi->mech_xp; ?></h2>
                <h2 class="milestone-bloo"><?=$bloo_label; ?>: <?= $mi->mech_bloo; ?></h2>
                <?php if($mi->mech_ep > 0){ ?>
                    <h2 class="milestone-ep"><?=$ep_label; ?>: <?= $mi->mech_ep; ?></h2>
                <?php } ?>
            </div>
            <div class="milestone-cta">
                <div class="milestone-preview-level">
                    <h2 class="red-bg-400"><?= $mi->mech_level; ?></h2>
                </div>
                <div class="milestone-preview-title">
                    <h1><?= $mi->quest_title; ?></h1>
                    <h2 class="purple-bg-A400"> <span class="icon icon-lock inline-block"></span> <?= __("Unavailable by PATH","bluerabbit"); ?></h2>
                </div>
            </div>
			<a class="hex-button flat milestone-edit-button layer foreground" href="<?= get_bloginfo("url")."/new-$mi->quest_type/?questID=$mi->quest_id&adventure_id=$mi->adventure_id"; ?>">
				<div class="hex layer deep-bg green-bg-400 opacity-80"></div>
				<div class="perfect-center layer absolute base font condensed _12 w100 white-color w-full text-center">
					<span class="icon icon-edit inline-block"></span>
				</div>
			</a>
			<div class="milestone-activate-button" onClick="activateMilestone(<?= $elementID; ?>, '#ui-touch-milestone','#ui-touch-milestone-reverse');"></div>
		<?php } ?>
	</div>

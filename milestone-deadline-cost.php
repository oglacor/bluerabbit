	<div class="milestone <?php echo "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>" style="<?= $scale; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="red">
		<input type="hidden" class="milestone-data-type" value="deadline-cost">
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
                <button class="action-button deadline-unlock" onClick='payment(<?=$mi->quest_id;?>,"deadline");'>
                    <?= __("Deadline missed","bluerabbit")." ".date('D, M jS, Y | H:i',strtotime($mi->mech_deadline)); ?><br>
					<?= __("Unlock for","bluerabbit");?><span class='icon icon-bloo'></span><?= toMoney($mi->mech_deadline_cost,""); ?>
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
				<h2><span class="icon icon-lock"></span></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<h2 class="red-bg-A400 white-color padding-5">
					<span class="icon icon-lock inline-block"></span>
					<?= __("Deadline missed","bluerabbit")." ".date('D, M jS, Y | H:i',strtotime($mi->mech_deadline)); ?>
				</h2>
				<button class="button layer foreground relative form-ui locked-mark red-bg-500" onClick='payment(<?=$mi->quest_id;?>,"deadline");'> 
					<?= __("Unlock for","bluerabbit");?><span class='icon icon-bloo'></span><?= toMoney($mi->mech_deadline_cost,""); ?><br>
				</button>
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

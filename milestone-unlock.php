	<div class="milestone <?php echo "$mi->quest_type $hideByDay $left_side level{$mi->mech_level}";  ?>"  id="milestone-<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-id" value="<?= $elementID; ?>">
		<input type="hidden" class="milestone-data-title" value="<?= $mi->quest_title; ?>">
		<input type="hidden" class="milestone-data-xp" value="<?= $mi->mech_xp; ?>">
		<input type="hidden" class="milestone-data-bloo" value="<?= $mi->mech_bloo; ?>">
		<input type="hidden" class="milestone-data-ep" value="<?= $mi->mech_ep; ?>">
		<input type="hidden" class="milestone-data-level" value="<?= $mi->mech_level; ?>">
		<input type="hidden" class="milestone-data-bg" value="<?= $mi->mech_badge; ?>">
		<input type="hidden" class="milestone-data-color" value="purple">
		<input type="hidden" class="milestone-data-type" value="unlock-cost">
		
		<div class="milestone-cta">
			<div class="milestone-preview-level">
				<h2 class="deep-purple-bg-400"><?= $mi->mech_level;?></h2>
			</div>
			<div class="milestone-preview-title">
				<h1><?= $mi->quest_title; ?></h1>
				<p>
					<span class="icon icon-bloo inline-block"></span>
					<strong><?= __("Quest must be purchased","bluerabbit"); ?></strong>
				</p>
				<button class="button layer foreground relative form-ui locked-mark teal-bg-500" onClick='payment(<?=$mi->quest_id;?>,"unlock");'> 
					<?= __("Puchase quest for:","bluerabbit");?> <span class='icon icon-bloo'></span><?= toMoney($mi->mech_unlock_cost,""); ?><br>
				</button>
			</div>
		</div>
	
		<div class="milestone-bg-color teal-bg-A400"></div>
		<div class="milestone-bg-badge blue-grey-bg-900 blend-luminosity" style="background-image: url(<?= $mi->mech_badge; ?>);">
			<span class="icon-button absolute perfect-center block teal-bg-400 sq-40"><span class="icon icon-bloo white-color font _26"></span></span>
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

<div class="layer base relative lore-item">
	<div class="layer background absolute sq-full top left <?=$b->quest_color;?>-bg-400 opacity-30"></div>
	<div class="icon-group layer base w-full realtive padding-0">
		<?php if($b->quest_style =='resource'){ ?>
			<div class="icon-content sq-100 background layer relative">
				<a href="<?= $b->quest_secondary_headline;?>" class="block layer base absolute sq-full top left" target="_blank" title="<?= __("Download","bluerabbit"); ?>">
					<img src="<?= $b->mech_badge; ?>" class="max-w-100 absolute perfect-center">
				</a>
			</div>
			<div class="icon-content white-color font _24 w700">
				<div class="icon-content white-color">
					<div class="line font _24 w700">
						<a href="<?= $b->quest_secondary_headline;?>" target="_blank" title="<?= __("Download","bluerabbit"); ?>">
							<?=$b->quest_title;?>
						</a>
					</div>
					<div class="line font _16 w300 max-h-32 overflow-hidden"><?= wp_trim_words($b->quest_content, 20);?></div>
				</div>
			</div>
			<div class="icon-content white-color font _16 w700">
				<a href="<?= $b->quest_secondary_headline;?>" class="button form-ui transparent-bg" target="_blank" title="<?= __("Download","bluerabbit"); ?>">
					<?= __("Download","bluerabbit"); ?>
				</a>
			</div>
		<?php }elseif($b->quest_style =='article'){  ?>
			<div class="icon-content sq-100 background layer relative">
				<button onClick="loadLore(<?= $b->quest_id; ?>);" class="block layer base absolute sq-full top left" target="_blank" title="<?= __("Download","bluerabbit"); ?>">
					<img src="<?= $b->mech_badge; ?>" class="max-w-100 absolute perfect-center">
				</button>
			</div>
			<div class="icon-content white-color">
				<div class="line font _24 w700"><?=$b->quest_title;?></div>
				<div class="line font _16 w300 max-h-32 overflow-hidden"><?= wp_trim_words($b->quest_content, 20);?></div>
			</div>
			<div class="icon-content white-color font _16 w700">
				<button onClick="loadLore(<?= $b->quest_id; ?>);" class="button form-ui transparent-bg" >
					<span class="icon icon-narrative"></span><?= __("Read","bluerabbit"); ?>
				</button>
			</div>
		<?php } ?>
		
		<?php if($isGM || $isAdmin){ ?>
			<div class="icon-content">
				<a class="form-ui font _16 green-bg-200 w-100 white-color text-center" href="<?= get_bloginfo('url')."/new-lore/?adventure_id=$adventure->adventure_id&questID=$b->quest_id"; ?>">
					<span class="icon icon-edit"></span><?= __("Edit","bluerabbit"); ?>
				</a>
				<br>
				<button class="form-ui font _16 red-bg-200 w-100 white-color text-center" onClick="showOverlay('#confirm-trash-<?= $b->quest_id; ?>');">
					<span class="icon icon-trash"></span><?= __("Trash","bluerabbit"); ?>
				</button>
				<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $b->quest_id; ?>">
					<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $b->quest_id; ?>,'quest','trash');">
						<span class="icon-group">
							<span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
								<span class="icon icon-trash white-color"></span>
							</span>
							<span class="icon-content">
								<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
							</span>
						</span>
					</button>
					<button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
						<span class="icon icon-cancel white-color"></span>
					</button>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

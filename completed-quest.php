<div class="layer foreground relative">
	<h3><?= $quest->quest_title;?></h3>
	<h1 class="font w900 uppercase padding-10"><?= __("Quest completed!","bluerabbit"); ?></h1>
	<?php if($quest->quest_success_message){ ?>
		<div class="success-message text-center white-color padding-10 max-w-900 boxed">
			<?= apply_filters('the_content', $quest->quest_success_message); ?>
		</div>
	<?php } ?>
	<div class="earned-resources text-center white-color relative layer base padding-20 max-w-500 boxed min-h-70">
		<div class="background layer bottom-1 absolute sq-full blue-gradient-A400 opacity-70"></div>
		<div class="layer base relative">
			<div class="icon-group inline-table" id="xp-number-earned-<?=$quest->quest_id; ?>">
				<span class="icon-button font _20 sq-30  amber-bg-400">
					<span class="icon icon-star white-color"></span>
				</span>
				<span class="icon-content">
					<span class="line amber-400 font _18 w900 number">0</span>
					<span class="line white-color font _10 w300 kerning-3"><?= $xp_label ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?=$quest->mech_xp; ?>">
			</div>
			<div class="icon-group inline-table" id="bloo-number-earned-<?=$quest->quest_id; ?>">
				<span class="icon-button font _20 sq-30  light-green-bg-400">
					<span class="icon icon-bloo white-color"></span>
				</span>
				<span class="icon-content">
					<span class="line light-green-400 font _18 w900 number">0</span>
					<span class="line white-color font _10 w300 kerning-3"><?=$bloo_label; ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?=$quest->mech_bloo; ?>">
			</div>
			<?php if($adv_settings['use_encounters']['value'] > 0){ ?>
			<div class="icon-group inline-table" id="ep-number-earned-<?=$quest->quest_id; ?>">
				<span class="icon-button font _20 sq-30 cyan-bg-A400 ">
					<span class="icon icon-activity blue-grey-900"></span>
				</span>
				<span class="icon-content">
					<span class="line cyan-A400 font _18 w900 number">0</span>
					<span class="line white-color font _10 w300 kerning-3"><?= $ep_label ?></span>
				</span>
				<input type="hidden" class="end-value" value="<?=$quest->mech_ep; ?>">
			</div>
			<script>animateNumber('#ep-number-earned-<?=$quest->quest_id; ?>', 500, 750);</script>
			<?php } ?>
			<script>animateNumber('#xp-number-earned-<?=$quest->quest_id; ?>', 1000, 250);</script>
			<script>animateNumber('#bloo-number-earned-<?=$quest->quest_id; ?>', 750, 500);</script>
		</div>
	</div>
	<div class="rewards text-center padding-10">
		<?php if($achievement_reward){ ?>
			<div class="text-center relative padding-10 inline-block w-250">
				<div class="background layer absolute sq-full purple-gradient-400 opacity-50"></div>
				<div class="layer relative base">
					<img src="<?= $achievement_reward->achievement_badge;?>" class="w-150 margin-5 overflow-hidden border rounded-max layer relative base cursor-pointer">
					<h4 class="line white-color font w100 _12 opacity-80"><?php _e("You earned an achievement","bluerabbit");?></h4>
					<h3 class="line white-color font w900 _18"><?= $achievement_reward->achievement_name;?></h3>
				</div>
			</div>
		<?php } ?>
		<?php if($item_reward){ ?>
			<div class="text-center relative padding-10 inline-block w-250">
				<div class="background layer absolute sq-full teal-gradient-400 opacity-50"></div>
				<img src="<?= $item_reward->item_badge;?>" class="w-150 margin-5 overflow-hidden border rounded-max layer relative base">
				<br>
				<div class="icon-group inline-table layer relative base">
					<a class="icon-button font _24 sq-40 teal-bg-400 font _28" href="<?= get_bloginfo('url')."/backpack/?adventure_id=$adv_child_id";?>">
						<span class="icon icon-backpack white-color"></span>
					</a>
					<span class="icon-content">
						<span class="line white-color font w100 _12 opacity-80"><?php _e("You found an item","bluerabbit");?></span>
						<span class="line white-color font w900 _18"><?= $item_reward->item_name;?></span>
					</span>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="padding-10 w-full text-center">
		<a href="<?=get_bloginfo('url')."/adventure/?adventure_id=$adv_child_id";?>" class="form-ui white-bg padding-5 margin-5 blue-A400 font _18 w900 uppercase opacity-50">
			<span class="icon icon-journey"></span><?= __("Back to the journey!","bluerabbit"); ?>
		</a>
	</div>
</div>
<script>
	$("#overlay-background-video source").attr('src',"<?=get_bloginfo('template_directory')."/video/aetherite.mp4"; ?>");
	$('#overlay-background-video')[0].load();
	$('#overlay-background-video').addClass('active',function(){
		$("#overlay-background-video").get(0).play();
	});
	
</script>

<div class="layer background sq-full opacity-0 fixed" onClick="clearMainLoader();"></div>
<div class="layer background blue-grey-bg-900 opacity-90 fixed" onClick="clearMainLoader();"></div>
<div class="layer base relative padding-20">
	<div class="relative layer base boxed white-color overflow-hidden border rounded-20">
		<div class="content layer base relative">
			<div class="base layer relative">
				<h3 class="font _24 w600 padding-10"><?= $lore->quest_title;?></h3>
				<div class="font _16 w300 padding-10 lore-content">
					<?= apply_filters('the_content', $lore->quest_content); ?>
				</div>
				<div class="text-center w-full">
					<button class="form-ui red-bg-400" onClick="clearMainLoader();"><span class="icon icon-cancel"></span><?= __("Close","bluerabbit"); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>


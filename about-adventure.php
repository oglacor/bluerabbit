<div class="layer background sq-full fixed" style="background-image: url(<?= $adventure->adventure_badge; ?>);"></div>    
<div class="layer background sq-full fixed black-bg opacity-80" onClick="closeIntro();"></div>    
<div class="layer base absolute perfect-center white-color padding-20">
	<div class="base layer relative max-w-900 boxed"> 
		<button class="icon-button red-bg-400 absolute layer base top-10 right-10 font _18" onClick="closeIntro();"><span class="icon icon-cancel"></span></button>
		<div class="h-50"></div>
		<div class="content">
			<?= apply_filters('the_content', $adventure->adventure_instructions);?>
		</div>
		<div class="padding-20 text-center">
			<button class="form-ui blue-bg-400" onClick="closeIntro();">
				<span class="icon icon-freespirit"></span> <?= __("On to the adventure!","bluerabbit"); ?>
			</button>
		</div>
	</div> 
</div>

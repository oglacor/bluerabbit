
<div class="layer base relative">
	<span class="icon icon-cancel font _100 red-A400"></span>
	<h3><?= $quest->quest_title;?></h3>
	<h1 class="font _40 w900 uppercase padding-10"><?= __("Quest Failed!","bluerabbit"); ?></h1>
	
	<div class="padding-10 w-full text-center">
		<a href="<?=get_bloginfo('url')."/quest/?questID=$quest->quest_id&adventure_id=$adv_child_id";?>" class="form-ui white-bg padding-5 margin-5 red-A400 font _24 w900 uppercase opacity-50">
			<span class="icon icon-challenge"></span><?= __("Try Again!","bluerabbit"); ?>
		</a>
	</div>
</div>
<script>
	$("#overlay-background-video source").attr('src',"<?=get_bloginfo('template_directory')."/video/glass-break.mp4"; ?>");
	$('#overlay-background-video')[0].load();
	$('#overlay-background-video').addClass('active',function(){
		$("#overlay-background-video").get(0).play();
	});
	
</script>

<?php 
$str = (isset($exclude)) ? "AND a.player_id NOT IN ($exclude)" : "";
$players = $wpdb->get_results("
	SELECT a.*,b.player_display_name, b.player_email, b.player_picture, b.player_hexad, b.player_hexad_slug, users.display_name, users.user_email FROM {$wpdb->prefix}br_player_adventure a
	LEFT JOIN {$wpdb->prefix}br_players b
	on a.player_id = b.player_id
	LEFT JOIN {$wpdb->prefix}users users
	on a.player_id = users.ID
	WHERE a.adventure_id=$adv_parent_id AND a.player_adventure_status='in' $str ORDER BY b.player_email LIMIT 1000
"); ?>


<div class="highlight padding-10 <?= $selected_color ? $selected_color : 'purple'; ?>-bg-50 padding-0" id="tutorial-player-select">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40  deep-purple-bg-400"><span class="icon icon-players"></span></span>
		<span class="icon-content">
			<span class="line font _24 grey-800"><?=__("Assign Manually","bluerabbit"); ?></span> 
			<span class="line font _14 w400 grey-600"><?=__("Assign achievement manually to the players","bluerabbit"); ?></span>
		</span>
	</span>
	<div class="highlight-cell pull-right">
		<div class="icon-group">
			<div class="icon-content">
				<div class="input-group sticky">
					<label>
						<span class="icon icon-search"></span>
					</label>
					<input type="text" class="form-ui" id="search-players" placeholder="<?php _e("Search players","bluerabbit"); ?>">
					<script>
						$('#search-players').keyup(function(){
							var valThis = $(this).val().toLowerCase();
							if(valThis == ""){
								$('ul.player-select > li').show();           
							}else{
								$('ul.player-select > li').each(function(){
									var text = $(this).text().toLowerCase();
									(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
								});
							};
						});
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="highlight padding-10 <?= $selected_color ? $selected_color : 'purple'; ?>-bg-50 padding-0" id="tutorial-player-select">
	<button class="button form-ui" onClick="triggerAchievements();"><?= __("Assign all","bluerabbit"); ?></button>
	<button class="button form-ui red-bg-400" onClick="triggerAchievements('off');"><?= __("Remove all","bluerabbit"); ?></button>
</div>
<div class="content">
	<ul class="player-select select-multiple theme-border">
		<?php foreach($players as $p){ ?>
			<li id="player-achievement-<?= $p->player_id; ?>" onClick="triggerAchievement(<?= "$a->achievement_id, $p->player_id"; ?>);" 
				class="margin-5 player-achievement-item <?php if(in_array($p->player_id,$selected_players)){ echo 'active'; } ?> <?= $p->player_hexad_slug; ?> level-<?= $p->player_level; ?>">
				
				<div class="icon-group">
					<button class="icon-button player-picture white-bg sq-60" style="background-image: url(<?= $p->player_picture; ?>);">
						
					</button>
					<div class="icon-content text-left">
						<span class="line font _18 player-name">
							<?php if($p->player_display_name != ''){
								echo $p->player_display_name;
							}else{
								echo $p->display_name;
							}
								
						 	?>
						</span>
						<span class="line player-email font _12">
							<span class='icon icon-level deep-purple-400'></span><strong class='deep-purple-400'><?= $p->player_level; ?></strong> | 
							<?= $p->user_email; ?>
						</span>
						<input type="hidden" class="player-id" value="<?= $p->player_id; ?>">
					</div>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>

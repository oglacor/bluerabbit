<?php 
if($exclude){
	$str = "AND a.player_id NOT IN ($exclude)";
}

$players = $wpdb->get_results("
SELECT a.*,b.player_display_name, b.player_picture, b.player_hexad, b.player_hexad_slug FROM {$wpdb->prefix}br_player_adventure a
LEFT JOIN {$wpdb->prefix}br_players b
on a.player_id = b.player_id
WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' $str LIMIT 1000
"); ?>


<div class="highlight padding-10 <?php echo $selected_color ? $selected_color : 'purple'; ?>-bg-50 padding-0">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40  deep-purple-bg-400"><span class="icon icon-players"></span></span>
		<span class="icon-content">
			<span class="line font _24 grey-800"><?php echo $player_select_title ? $player_select_title : __("Players","bluerabbit"); ?></span>
			<?php if($player_select_desc){ ?>
				<span class="line font _14 w400 grey-600"><?php echo $player_select_desc; ?></span>
			<?php } ?>

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
	<div class="highlight-cell pull-right padding-10">
		<button class="form-ui black-bg" id="all-on" onClick="activateAll('.player-select li');"><?php _e("All On","bluerabbit"); ?></button>
		<button class="form-ui red-bg-400 hidden" id="all-off" onClick="deactivateAll('.player-select li');"><?php _e("All Off","bluerabbit"); ?></button>
		<button class="form-ui pink-bg-400" id="hexad-fs" onClick="activateAllPlayerType('freespirit');"><span class="icon icon-freespirit"></span></button>
		<button class="form-ui green-bg-400" id="hexad-ph" onClick="activateAllPlayerType('philanthropist');"><span class="icon icon-philanthropist"></span></button>
		<button class="form-ui blue-bg-400" id="hexad-a" onClick="activateAllPlayerType('achiever');"><span class="icon icon-achiever"></span></button>
		<button class="form-ui amber-bg-400" id="hexad-s" onClick="activateAllPlayerType('socialiser');"><span class="icon icon-socialiser"></span></button>
	</div>
</div>
<div class="content">
	<ul class="player-select select-multiple theme-border">
		<?php foreach($players as $p){ ?>
			<li id="player-<?php echo $p->player_id; ?>" onClick="selectMultiple('#player-<?php echo $p->player_id; ?>');" 
				class="<?php if(in_array($p->player_id,$selected_players)){ echo 'active'; } ?> <?php echo $p->player_hexad_slug; ?> level-<?php echo $p->player_level; ?>">
				<div class="player-picture" style="background-image: url(<?php echo $p->player_picture; ?>);">
					<div class="player-picture-content <?php echo $p->player_hexad_slug; ?>">
						<strong><?php echo $p->player_level; ?></strong>
						<span class="icon icon-<?php echo $p->player_hexad_slug; ?>"></span>
					</div>
				</div>
				<span class="player-name"><?php echo $p->player_display_name; ?></span>
				<input type="hidden" class="player-id" value="<?php echo $p->player_id; ?>">
			</li>
		<?php } ?>
	</ul>
</div>

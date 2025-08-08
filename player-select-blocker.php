<?php 
$str = isset($exclude) ? "AND a.player_id NOT IN ($exclude)" : "";
$players = $wpdb->get_results("
SELECT a.*,b.player_display_name, b.player_picture, b.player_hexad, b.player_hexad_slug FROM {$wpdb->prefix}br_player_adventure a
LEFT JOIN {$wpdb->prefix}br_players b
on a.player_id = b.player_id
WHERE a.adventure_id=$adventure->adventure_id AND a.player_adventure_status='in' $str LIMIT 1000
"); ?>

<div class="highlight padding-10 <?= isset($selected_color) ? $selected_color : 'purple'; ?>-bg-50 padding-0">
	<span class="icon-group">
		<span class="icon-button font _24 sq-40  deep-purple-bg-400"><span class="icon icon-players"></span></span>
		<span class="icon-content">
			<span class="line font _24 grey-800"><?= isset($player_select_title) ? $player_select_title : __("Players","bluerabbit"); ?></span>
			<?php if(isset($player_select_desc)){ ?>
				<span class="line font _14 w400 grey-600"><?= $player_select_desc; ?></span>
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
</div>

<div class="content">
	<ul class="player-select select-multiple theme-border">
		<?php if(isset($players)){ ?>
			<?php foreach($players as $p){ ?>
				<li id="player-blocker-<?= $p->player_id; ?>" onClick="selectMultiple('#player-blocker-<?php echo $p->player_id; ?>');" 
					class="<?php if(in_array($p->player_id, $selected_players)){ echo 'active'; } ?> <?= $p->player_hexad_slug; ?> level-<?= $p->player_level; ?>">

					<div class="icon-group">
						<button class="icon-button player-picture white-bg sq-60" style="background-image: url(<?= $p->player_picture; ?>);">

						</button>
						<div class="icon-content text-left">
							<span class="line font _18 player-name"><?= $p->player_display_name; ?></span>
							<span class="line player-email font _12">
								<span class="icon icon-level"></span><?= $p->player_level; ?>
							</span>
							<input type="hidden" class="player-id" value="<?= $p->player_id; ?>">
						</div>
					</div>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>




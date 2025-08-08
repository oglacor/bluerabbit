<?php include (get_stylesheet_directory() . '/header.php'); ?>
	<?php if(($isAdmin || $isGM || $isNPC)){  ?>
		<?php 
			$questID =  $_GET['questID'];
			$q = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_quests WHERE quest_id=$questID AND adventure_id=$adventure->adventure_id");
		?>
		<?php if($q){  ?>
			<?php
				$player_posts = $wpdb->get_results("
				SELECT a.*, b.player_display_name, b.player_email FROM {$wpdb->prefix}br_player_posts a
				JOIN {$wpdb->prefix}br_players b
				ON a.player_id = b.player_id
				WHERE a.adventure_id=$adventure->adventure_id AND a.quest_id=$q->quest_id ORDER BY b.player_display_name, a.pp_modified, a.pp_date"); 
			?>
			<table class="table compact">
				<thead>
					<tr>
						<td width="10%" class="font _14 w900 white-color">
							<a href="<?= get_bloginfo('url')."/manage-adventure/?adventure_id=$adventure->adventure_id";?>"><span class="icon icon-arrow-left"></span><?= __("Manage Adventure","bluerabbit"); ?></a>
						</td>
						<td width="80%" class="text-center">
							<h3 class="white-color font _18 uppercase">
								<?= __("Quest review","bluerabbit"); ?>
							</h3>
							<h1 class="white-color font _30 w900">
								<?= $q->quest_title;?>
							</h1>
						</td>
						<td width="10%" class="font _14 w900 white-color">
							<a href="<?= get_bloginfo('url')."/quest/?adventure_id=$adventure->adventure_id&questID=$q->quest_id";?>"><?= __("View Quest","bluerabbit"); ?><span class="icon icon-arrow-right"></span></a>
						</td>
					</tr>
				</thead>
			</table>
			<div class="h-20"></div>

			<?php if($player_posts){ ?>
				<div class="boxed max-w-1200 white-color layer base relative" id="player-submissions">
					<h4 class="font _24 white-color w900 uppercase padding-5 text-center"><?= __("Player Entries","bluerabbit"); ?></h4>
					<?php foreach($player_posts as $pp){ ?>
						<div class="w-full layer base relative margin-10">
							<div class="layer background sq-full absolute top left blue-bg-400 opacity-50"></div>
							<div class="layer base w-full relative flex padding-20">
								<div class="grow-1">
									<h2 class="font _30 padding-10 text-center layer base w700 "><?= $isAdmin ? $pp->player_id." | " : ""; ?><?= $pp->player_display_name; ?></h2>
									<h3 class="font _18 text-center layer base w300 grey-300"><?= $pp->player_email; ?></h3>
								</div>
								<?php if($config['rate_quests']['value']>0){ ?>
									<div class="grow-1">
										<?php $rating += $pp->pp_quest_rating; ?>
										<div class="input-group">
											<label class="blue-grey-bg-600 font w900"><span class="icon icon-star"></span><?= __("Rating","bluerabbit"); ?></label>
											<input disabled class="form-ui blue-grey-bg-800 white-color border border-all blue-grey-border-800" value="<?= $pp->pp_quest_rating ? $pp->pp_quest_rating : 0; ?>">
										</div>
									</div>
								<?php } ?>
								<?php if($adventure->adventure_grade_scale != "none"){ ?>
									<div class="grow-1">
										<?php $the_grade = $pp->pp_grade;?>
										<div class="input-group">
											<label class="blue-grey-bg-600 font w900"><span class="icon icon-progression"></span><?= __("Grade","bluerabbit"); ?></label>
											<?php if($adventure->adventure_grade_scale == "percentage"){ ?>
												<input onChange="setGrade(<?= "$q->quest_id,$pp->player_id"; ?>);" type="number" min="0" max="100" class="form-ui blue-grey-bg-800 white-color border border-all blue-grey-border-800"  id="<?= "the_post_grade_{$q->quest_id}_{$pp->player_id}"; ?>" value="<?= $the_grade; ?>">
											<?php }elseif($adventure->adventure_grade_scale == "letters"){   ?>
												<select class="form-ui" id="<?= "the_post_grade_{$q->quest_id}_{$pp->player_id}"; ?>" onChange="setGrade(<?= "$q->quest_id,$pp->player_id"; ?>);">
													<option value="100" <?php if($the_grade == 100){ echo 'selected'; } ?>>A</option>
													<option value="91.75" <?php if($the_grade < 100 && $the_grade >= 91.75){ echo 'selected';  }?>>A-</option>
													<option value="83.25" <?php if($the_grade < 91.75 && $the_grade >= 83.25){ echo 'selected';  }?>>B+</option>
													<option value="75" <?php if($the_grade < 83.25 && $the_grade >= 75){ echo 'selected'; } ?>>B</option>
													<option value="66.75" <?php if($the_grade < 75 && $the_grade >= 66.75){ echo 'selected'; } ?>>B-</option>
													<option value="58.25" <?php if($the_grade < 66.75 && $the_grade >= 58.25){ echo 'selected'; } ?>>C+</option>
													<option value="50" <?php if($the_grade < 58.25 && $the_grade >= 50){ echo 'selected'; } ?>>C</option>
													<option value="25" <?php if($the_grade < 50 && $the_grade >= 25){ echo 'selected'; } ?>>D</option>
													<option value="0" <?php if($the_grade < 25){ echo 'selected'; } ?>>F</option>
													<option value="NULL" <?php if($the_grade===NULL){ echo 'selected'; } ?>><?php _e("No grade","bluerabbit"); ?></option>
												</select>
											<?php }  ?>
										</div>
									</div>
								<?php }  ?>
								
							</div>
							<div class="layer base w-full relative flex">
								<div class="padding-20 player-entry-data w-full">
									<div class=" padding-5">
										<h4 class="font _14 pull-right text-right">
											<?= __("Published","bluerabbit");?> <span class="font w900"><?=$pp->pp_date; ?></span> / <?= __("Modified","bluerabbit");?> <span class="font w900"><?=$pp->pp_modified; ?></span><h4>
										</h4>
										<h2 class="font _18 yellow-400 w900 uppercase"><?= __("Player Entry","bluerabbit"); ?>:</h2>
									</div>
									<div class="padding-5">
										<?= apply_filters('the_content',$pp->pp_content); ?>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="highlight padding-10">
						<h2 class="font _20 w900 uppercase padding-10"><?= __("Quest data","bluerabbit"); ?></h2>
						<h4 class="font _14 white-color w900 uppercase padding-5 text-center"><?= __("Total Entries","bluerabbit")." <strong>".count($player_posts)."</strong>"; ?></h4>
						<span class="icon-group">
							<span class="icon-button font _18 sq-30  amber-bg-400">
								<span class="icon icon-star"></span>
							</span>
							<span class="icon-content">
								<span class="line font _24"><?= __("Quest Rating","bluerabbit")." <strong>".$rating/count($player_posts)."</strong>"; ?></span>
							</span>
						</span>
						<input type="hidden" id="file_prefix" value="<?= $q->quest_type.'-'.$q->quest_id.'-'; ?>">
						<span class="icon-content">
							<button id="create-zip" class="form-ui blue-bg-400" onClick="downloadAllImages();"><span class="icon icon-image"></span><?= __("Create Images Zip","bluerabbit"); ?></button>
						</span>
						<span class="icon-content">
							<a id="download-zip" href="" class="form-ui orange-bg-400 hidden" target="_blank"><?= __("Download Zip","bluerabbit"); ?></a>
						</span>
					</div>
				</div>
				<br class="clear">
				<input type="hidden" id="grade_nonce" value="<?= wp_create_nonce('br_grade_nonce'); ?>"/>
			<?php }else{ ?>
				- <h2 class="white-color font _30 text-center padding-20"><?= __("No player posts to display","bluerabbit"); ?></h2> -
			<?php } ?>
		<?php }else{ ?>
			- <h2 class="white-color font _24 text-center padding-20"><?= __("This quest doesn't exist","bluerabbit"); ?></h2> -
		<?php } ?>
	<?php }else{ ?>
		<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
	<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php 
if($adventure){
	$adventures = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}br_adventures a
	JOIN {$wpdb->prefix}br_player_adventure b 
	ON a.adventure_id=b.adventure_id
	WHERE a.adventure_status='publish' AND (a.adventure_owner=$current_user->ID OR (b.player_id=$current_user->ID AND b.player_adventure_status='in' AND b.player_adventure_role='gm')) GROUP BY a.adventure_id ORDER BY a.adventure_title
	
	" );
	$adventure_quests = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_quests 
	WHERE adventure_id=$adventure->adventure_id AND quest_status='publish' ORDER BY quest_type, quest_relevance, quest_order, mech_level, mech_start_date");
	
	$adventure_achievements = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_achievements 
	WHERE adventure_id=$adventure->adventure_id AND achievement_status='publish' ORDER BY achievement_name, achievement_order");

	$adventure_items = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_items
	WHERE adventure_id=$adventure->adventure_id AND item_status='publish' ORDER BY item_name, item_order");
	
	$adventure_encounters = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_encounters
	WHERE adventure_id=$adventure->adventure_id AND enc_status='publish'");
	
	$adventure_speakers = $wpdb->get_results("SELECT *
	FROM {$wpdb->prefix}br_speakers
	WHERE adventure_id=$adventure->adventure_id AND speaker_status='publish' ORDER BY speaker_first_name, speaker_last_name, speaker_id");

?>
	<div class="boxed max-w-1200">
			<div class="text-center padding-20">
				<span class="icon-group inline-table">
					<span class="icon-button font _24 sq-40  icon-lg purple-bg-400"><span class="icon icon-duplicate"></span></span>
					<span class="icon-content">
						<span class="line font _48 white-color">
							<?php _e("Duplicator","bluerabbit"); ?>
						</span>
					</span>
				</span>
			</div>
			<div class="body-ui w-full white-bg">
				<div class="highlight padding-20 blue-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  blue-bg-400"><span class="icon icon-quest"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Quests","bluerabbit");?></div>
						</div>
					</div>
					<div class="icon-group pull-right">
						<div class="icon-content">
							<button class="form-ui font _16 green-bg-400" onClick="activateAll('#quests-to-duplicate li.to-duplicate');">
								<span class="icon icon-check"></span>
								<?php _e("Select All","bluerabbit"); ?>
							</button>
							<button class="form-ui font _16 red-bg-400" onClick="deactivateAll('#quests-to-duplicate li.to-duplicate');">
								<span class="icon icon-cancel"></span>
								<?php _e("Clear Selection","bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="content">
					<ul class="selectable-list select-multiple" id="quests-to-duplicate">
						<?php $block=''; ?>
						<?php foreach ($adventure_quests as $key=>$q){ ?>
							<?php if($block != $q->quest_type){ ?>
								<?php $block = $q->quest_type; ?>
								<li class="grey-bg-900 white-color text-center font _30 w900 kerning-1 uppercase"><?=$block;?></li>
							<?php } ?>
							<li id="req-<?= $q->quest_id; ?>" class="<?= $q->quest_type; ?> white-bg level-<?= $q->mech_level; ?> to-duplicate" onClick="toggleReq('#req-<?= $q->quest_id; ?>');">
								<span class="li-cell inactive-content grey-bg-300 text-center font _18">
									<span class="icon icon-<?= $q->quest_icon ? $q->quest_icon : 'document'; ?>"></span>
								</span>
								<span class="li-cell cell-content inactive-content padding-10 text-left"><?= $q->quest_title; ?></span>

								<span class="li-cell active-content green-bg-400 white-color font _18">
									<span class="icon icon-check white-color"></span>
								</span>
								<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= $q->quest_title; ?></span>
								<span class="li-cell amber-bg-400 white-color font w900">
									<span class="icon icon-star"></span>
									<?= $q->mech_xp ? toMoney($q->mech_xp,'') : 0; ?>
								</span>
								<span class="li-cell light-green-bg-400 white-color font w900">
									<span class="icon icon-bloo"></span>
									<?= $q->mech_bloo ? toMoney($q->mech_bloo,'') : 0; ?>
								</span>
								<span class="li-cell deep-purple-bg-400 white-color font w900">
									<?= $q->mech_level; ?>
								</span>

								<input type="hidden" class="reqs-id" value="<?= $q->quest_id; ?>">
								<input type="hidden" class="reqs-level" value="<?= $q->mech_level; ?>">
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="highlight padding-20 purple-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  purple-bg-400"><span class="icon icon-achievement"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Achievements","bluerabbit");?></div>
						</div>
					</div>
					<div class="icon-group pull-right">
						<div class="icon-content">
							<button class="form-ui font _16 green-bg-400" onClick="activateAll('#achievements-to-duplicate li.to-duplicate');">
								<span class="icon icon-check"></span>
								<?php _e("Select All","bluerabbit"); ?>
							</button>
							<button class="form-ui font _16 red-bg-400" onClick="deactivateAll('#achievements-to-duplicate li.to-duplicate');">
								<span class="icon icon-cancel"></span>
								<?php _e("Clear Selection","bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="content">
					<ul class="selectable-list select-multiple" id="achievements-to-duplicate">
						<?php foreach ($adventure_achievements as $key=>$a){ ?>
						
							<li id="req-achievement-<?= $a->achievement_id; ?>" class="achievement white-bg to-duplicate" onClick="toggleReq('#req-achievement-<?= $a->achievement_id; ?>');">
								<span class="li-cell inactive-content grey-bg-300 text-center font _18">
									<span class="icon icon-achievement"></span>
								</span>
								<span class="li-cell cell-content inactive-content padding-10 text-left"><?= $a->achievement_name; ?></span>

								<span class="li-cell active-content green-bg-400 white-color font _18">
									<span class="icon icon-check white-color"></span>
								</span>
								<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= $a->achievement_name; ?></span>

								<span class="li-cell amber-bg-400 white-color font w900">
									<span class="icon icon-star"></span>
									<?= $a->achievement_xp ? toMoney($a->achievement_xp,'') : 0; ?>
								</span>
								<span class="li-cell light-green-bg-400 white-color font w900">
									<span class="icon icon-bloo"></span>
									<?= $a->achievement_bloo ? toMoney($a->achievement_bloo,'') : 0; ?>
								</span>

								<input type="hidden" class="reqs-id" value="<?= $a->achievement_id; ?>">
							</li>
						
						<?php } ?>
					</ul>
				</div>
				<div class="highlight padding-20 pink-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  pink-bg-400"><span class="icon icon-basket"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Items","bluerabbit");?></div>
						</div>
					</div>
					<div class="icon-group pull-right">
						<div class="icon-content">
							<button class="form-ui font _16 green-bg-400" onClick="activateAll('#items-to-duplicate li.to-duplicate');">
								<span class="icon icon-check"></span>
								<?php _e("Select All","bluerabbit"); ?>
							</button>
							<button class="form-ui font _16 red-bg-400" onClick="deactivateAll('#items-to-duplicate li.to-duplicate');">
								<span class="icon icon-cancel"></span>
								<?php _e("Clear Selection","bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="content">
					<ul class="selectable-list select-multiple" id="items-to-duplicate">
						<?php foreach ($adventure_items as $key=>$i){ ?>
							<?php
							if($i->item_type=='consumable'){
								$icon_type='basket';
								$type_label = __("Consumable","bluerabbit");
								$i_color = 'pink';
							}elseif($i->item_type=='key'){
								$icon_type='key';
								$type_label = __("Key","bluerabbit");
								$i_color = 'indigo';
							}elseif($i->item_type=='reward'){
								$icon_type='winstate';
								$type_label = __("Reward","bluerabbit");
								$i_color = 'teal';
							}
							?>
						
							<li id="req-item-<?= $i->item_id; ?>" class="item white-bg level-<?= $i->item_level; ?> to-duplicate" onClick="toggleReq('#req-item-<?= $i->item_id; ?>');">
								<span class="li-cell inactive-content grey-bg-300 text-center font _18">
									<span class="icon icon-<?=$icon_type;?>"></span>
								</span>
								<span class="li-cell cell-content inactive-content padding-10 text-left"><?= $i->item_name; ?></span>

								<span class="li-cell active-content green-bg-400 white-color font _18">
									<span class="icon icon-check white-color"></span>
								</span>
								<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= $i->item_name; ?></span>

								<span class="li-cell light-green-bg-400 white-color font w900">
									<span class="icon icon-bloo"></span>
									<?= toMoney($i->item_cost); ?>
								</span>

								<input type="hidden" class="reqs-id" value="<?= $i->item_id; ?>">
							</li>
						
						<?php } ?>
					</ul>
				</div>
				<div class="highlight padding-20 cyan-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  cyan-bg-A400 blue-grey-900"><span class="icon icon-activity"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Encounters","bluerabbit");?></div>
						</div>
					</div>
					<div class="icon-group pull-right">
						<div class="icon-content">
							<button class="form-ui font _16 green-bg-400" onClick="activateAll('#encounters-to-duplicate li.to-duplicate');">
								<span class="icon icon-check"></span>
								<?php _e("Select All","bluerabbit"); ?>
							</button>
							<button class="form-ui font _16 red-bg-400" onClick="deactivateAll('#encounters-to-duplicate li.to-duplicate');">
								<span class="icon icon-cancel"></span>
								<?php _e("Clear Selection","bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="content">
					<ul class="selectable-list select-multiple" id="encounters-to-duplicate">
						<?php foreach ($adventure_encounters as $key=>$e){ ?>
							<li id="req-enc-<?= $e->enc_id; ?>" class="item white-bg level-<?= $e->enc_level; ?> to-duplicate" onClick="toggleReq('#req-enc-<?= $e->enc_id; ?>');">
								<span class="li-cell inactive-content grey-bg-300 text-center font _18">
									<span class="icon icon-socialiser"></span>
								</span>
								<span class="li-cell cell-content inactive-content padding-10 text-left"><?= $e->enc_question; ?></span>

								<span class="li-cell active-content green-bg-400 white-color font _18">
									<span class="icon icon-check white-color"></span>
								</span>
								<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= $e->enc_question; ?></span>

								<span class="li-cell cyan-bg-400 white-color font w900">
									<span class="icon icon-activity"></span>
									<?= ($e->enc_ep); ?>
								</span>
								<span class="li-cell amber-bg-400 white-color font w900">
									<span class="icon icon-star"></span>
									<?= ($e->enc_xp); ?>
								</span>
								<span class="li-cell light-green-bg-400 white-color font w900">
									<span class="icon icon-bloo"></span>
									<?= toMoney($e->enc_bloo); ?>
								</span>

								<input type="hidden" class="reqs-id" value="<?= $e->enc_id; ?>">
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="highlight padding-20 orange-bg-100">
					<div class="icon-group">
						<div class="icon-button font _24 sq-40  orange-bg-A400 blue-grey-900"><span class="icon icon-socialiser"></span></div>
						<div class="icon-content">
							<div class="line font _24"><?= __("Speakers","bluerabbit");?></div>
						</div>
					</div>
					<div class="icon-group pull-right">
						<div class="icon-content">
							<button class="form-ui font _16 green-bg-400" onClick="activateAll('#speakers-to-duplicate li.to-duplicate');">
								<span class="icon icon-check"></span>
								<?php _e("Select All","bluerabbit"); ?>
							</button>
							<button class="form-ui font _16 red-bg-400" onClick="deactivateAll('#speakers-to-duplicate li.to-duplicate');">
								<span class="icon icon-cancel"></span>
								<?php _e("Clear Selection","bluerabbit"); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="content">
					<ul class="selectable-list select-multiple" id="speakers-to-duplicate">
						<?php foreach ($adventure_speakers as $key=>$e){ ?>
							<li id="req-speaker-<?= $e->speaker_id; ?>" class="item white-bg to-duplicate" onClick="toggleReq('#req-speaker-<?= $e->speaker_id; ?>');">
								<span class="li-cell inactive-content grey-bg-300 text-center font _18">
									<span class="icon icon-activity"></span>
								</span>
								<span class="li-cell cell-content inactive-content padding-10 text-left"><?= "$e->speaker_first_name $e->speaker_last_name"; ?></span>

								<span class="li-cell active-content green-bg-400 white-color font _18">
									<span class="icon icon-check white-color"></span>
								</span>
								<span class="li-cell cell-content active-content padding-10 text-left green-400 font w700"><?= "$e->speaker_first_name $e->speaker_last_name"; ?></span>
								<input type="hidden" class="reqs-id" value="<?= $e->speaker_id; ?>">
							</li>
						<?php } ?>
					</ul>
				</div>
			</div>
			<div class="footer-ui text-center padding-10 white-color deep-purple-bg-800">
				<div class="input-group w-full relative">
				<label class="font w900 amber-bg-400 grey-900"><?php _e('Adventure target','bluerabbit'); ?></label>
					<select class="form-ui font _18 padding-10" id="adventure_target">
						<?php foreach($adventures as $c){ ?>
							<option value="<?= $c->adventure_id;?>">
								<?= $c->adventure_title;?>
							</option>
						<?php } ?>
					</select>
					<label class="amber-bg-400 relative">
						<button class="form-ui amber-bg-400 grey-900" onClick="showOverlay('#duplicate-confirm');">
							<span class="icon icon-infinite"></span> <strong><?php _e('Duplicate selected objects','bluerabbit'); ?></strong><br>
						</button>
					</label>
					<div class="overlay-layer confirm-action" id="duplicate-confirm">
						<button class="form-ui red-bg-400 white-color font _30" onClick="duplicateQuests();">
							<span class="icon icon-infinite"></span> <strong><?php _e('Are you sure?','bluerabbit'); ?></strong><br>
						</button>
					</div>
				</div>
				<input type="hidden" id="duplicator_nonce" value="<?= wp_create_nonce('duplicate_nonce'); ?>"/>
			</div>
		</div>
<?php }else { ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

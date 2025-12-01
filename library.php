<?php 
	$templates = $wpdb->get_results("SELECT adventures.*, players.player_first, players.player_last, players.player_display_name, players.player_nickname, players.player_email, players.player_picture, orgs.org_name FROM {$wpdb->prefix}br_adventures adventures 
		JOIN {$wpdb->prefix}br_players players ON adventures.adventure_owner = players.player_id
		LEFT JOIN {$wpdb->prefix}br_player_org player_org ON players.player_id = player_org.player_id
		LEFT JOIN {$wpdb->prefix}br_orgs orgs ON orgs.org_id = player_org.org_id
		WHERE adventures.adventure_type='template' AND adventures.adventure_status='publish';
	");
?>



<div class="boxed w-full max-w-900 padding-10 grey-bg-100">
	<div class="w-full padding-10 relative">
		<div class="icon-group">
			<div class="icon-button font _24 sq-40 orange-bg-400"><span class="icon icon-narrative"></span></div >
			<div class="icon-content">
				<div class="line font _24 w300 grey-800">
					<?= __("Library","bluerabbit"); ?>
				</div>
				<input type="hidden" id="the-org-id" value="<?= $org->org_id; ?>">
			</div>
		</div>
		<button class="form-ui red-bg-400 absolute top-10 right-10" onClick="unloadContent();">
			<span class="icon icon-cancel"></span> <?= __("Close","bluerabbit"); ?>
		</button>
	</div>
	<div class="templates">
		<?php if(isset($templates)){ ?>
			<?php foreach($templates as $key=>$t){ ?>
				<div class="template" id="template-<?=$t->adventure_id;?>">
					<div class="template-content">
						<div class="template-name">
							<span class="icon-group">
								<span class="icon-button sq-60 grey-bg-100" style="background-image: url(<?= isset($t->adventure_badge) ? $t->adventure_badge : "";?>)"></span>
								<span class="icon-content">
									<span class="line grey-900 font _20 w600"><?= $t->adventure_title;?></span>
									<span class="line grey-500 font _18 w300">
										<?= __("Created by: ","bluerabbit");?>
										<?= isset($t->player_display_name) ? $t->player_display_name : "";?>
									</span>
								</span>
							</span>
						</div>
						<div class="template-org grey-900 font _16 w600">
							<?= $t->org_name ;?>
						</div>
						<div class="template-actions">
							<button class="form-ui button" onClick="previewTemplate(<?=$t->adventure_id; ?>)"><?= __("Preview","bluerabbit");?></button>
						</div>
					</div>
					<div class="template-preview">
						<div class="template-preview-content"></div>
						<div class="template-preview-footer">
							<button class="button form-ui red-bg-400 font _14" onClick="closeTemplatePreview();"><span class="icon icon-cancel"></span><?= __("Close","bluerabbit");?></button>
							<button class="button form-ui green-bg-400 font _14" onClick="createAdventureFromTemplate(<?= $t->adventure_id;?>);">
								<span class="icon icon-check"></span><?= __("Use this template","bluerabbit");?>
							</button>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
	<div class="footer-ui white-bg text-right">
		<button class="form-ui red-bg-400" onClick="unloadContent();">
			<span class="icon icon-cancel"></span> <?= __("Close","bluerabbit"); ?>
		</button>
	</div>
</div>

<input type="hidden" id="template_duplicator_nonce" value="<?= wp_create_nonce('br_template_duplicator_nonce'.$current_user->ID); ?>"/>

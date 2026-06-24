<?php
$tabis = BR_Tabi::instance()->getTabis($adventure->adventure_id);
$tabi_prereq_nonce = wp_create_nonce('tabi_prereq_nonce');
$tabi_as_category_nonce = wp_create_nonce('tabi_as_category_nonce');
$tabi_count = $tabis ? count($tabis) : 0;
?>

<div class="br-journey-manager">

	<!-- ════════════ HEADER ════════════ -->
	<div class="br-panel" style="margin-bottom:0;border-radius:12px 12px 0 0;">
		<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;justify-content:space-between;">
			<div style="display:flex;align-items:center;gap:14px;">
				<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(159,64,226,0.15);border-radius:10px;font-size:22px;color:#9f40e2;">
					<span class="icon icon-sabotage"></span>
				</div>
				<div>
					<h2 class="br-panel-title" style="margin:0;"><?php _e('Tabi Manager', 'bluerabbit'); ?></h2>
					<span style="font-size:13px;color:rgba(255,255,255,0.45);">
						<?= sprintf(__('%d tabis in this adventure', 'bluerabbit'), $tabi_count); ?>
					</span>
				</div>
			</div>
			<div class="br-actions">
				<button class="br-btn cyan" onClick="addTabi();">
					<span class="icon icon-add"></span> <?= __("Add Tabi", "bluerabbit"); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- ════════════ COLUMN HEADERS ════════════ -->
	<div class="br-panel" style="margin-bottom:0;margin-top:2px;border-radius:0;padding:10px 16px;">
		<div style="display:grid;grid-template-columns:40px 1fr 50px 50px 70px 70px 70px 80px 80px;gap:8px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.3);font-weight:700;">
			<div style="text-align:center;">#</div>
			<div><?= __("Name","bluerabbit"); ?></div>
			<div><?= __("Img","bluerabbit"); ?></div>
			<div><?= __("Color","bluerabbit"); ?></div>
			<div style="text-align:center;"><?= __("Level","bluerabbit"); ?></div>
			<div style="text-align:center;"><?= __("Width","bluerabbit"); ?></div>
			<div style="text-align:center;"><?= __("Height","bluerabbit"); ?></div>
			<div style="text-align:center;"><?= __("Journey","bluerabbit"); ?></div>
			<div style="text-align:center;"><?= __("Category","bluerabbit"); ?></div>
		</div>
	</div>

	<!-- ════════════ TABIS LIST ════════════ -->
	<div class="br-section-body" id="table-tabis">
		<?php if($tabis && count($tabis) > 0){ ?>
			<?php foreach($tabis as $avKey => $a){ ?>
				<?php $rowNumber = $avKey + 1; ?>
				<div class="br-panel" style="margin-bottom:2px;border-radius:0;padding:0;">

					<!-- Main row -->
					<div style="display:grid;grid-template-columns:40px 1fr 50px 50px 70px 70px 70px 80px 80px;gap:8px;align-items:center;padding:12px 16px;">

						<!-- # -->
						<div style="font-size:13px;color:rgba(255,255,255,0.3);text-align:center;">
							<?= $rowNumber; ?>
							<input type="hidden" class="tabi_id" value="<?= $a->tabi_id; ?>">
							<input type="hidden" class="tabi-id" value="<?= $a->tabi_id; ?>">
						</div>

						<!-- Name -->
						<div class="br-name">
							<input type="text" class="br-input" id="the_title-tabi-<?= $a->tabi_id; ?>" value="<?= esc_attr($a->tabi_name); ?>" onChange="setTitle(<?= $a->tabi_id; ?>,'tabi');" style="font-weight:600;">
						</div>

						<!-- Thumb -->
						<div>
							<input type="hidden" value="<?= $a->tabi_background; ?>" id="the_tabi_badge-<?= $a->tabi_id; ?>">
							<div class="br-thumb" onClick="showWPUpload('the_tabi_badge-<?= $a->tabi_id; ?>','a','tabi',<?= $a->tabi_id; ?>);" id="the_tabi_badge-<?= $a->tabi_id; ?>_thumb" style="background-image:url(<?= $a->tabi_background; ?>);width:40px;height:40px;"></div>
						</div>

						<!-- Color -->
						<div>
							<button class="br-type-icon" id="color-trigger-tabi-<?= $a->tabi_id; ?>" onClick="activate('#color-select-<?= $a->tabi_id; ?>');" style="background:rgba(255,255,255,0.08);width:34px;height:34px;border:2px solid <?= $a->tabi_color; ?>;border-radius:6px;cursor:pointer;">
								<span style="display:block;width:20px;height:20px;border-radius:4px;<?= br_color_attr($a->tabi_color, 'bg', true) ?>"></span>
							</button>
							<input type="hidden" value="<?= $a->tabi_color; ?>" id="the_tabi_color-<?= $a->tabi_id; ?>">
						</div>

						<!-- Level -->
						<div class="br-num">
							<input type="number" class="br-input" id="the_level-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_level; ?>" onChange="setLevel(<?= $a->tabi_id; ?>,'tabi');" style="text-align:center;padding:6px;">
						</div>

						<!-- Width -->
						<div class="br-num">
							<input type="number" class="br-input" id="the_width-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_width; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');" style="text-align:center;padding:6px;">
						</div>

						<!-- Height -->
						<div class="br-num">
							<input type="number" class="br-input" id="the_height-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_height; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');" style="text-align:center;padding:6px;">
						</div>

						<!-- Journey -->
						<div style="text-align:center;">
							<label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:rgba(255,255,255,0.6);">
								<input type="checkbox" id="tabi-on-journey-<?= $a->tabi_id; ?>" <?= $a->tabi_on_journey ? 'checked' : ''; ?> onChange="setTabiOnJourney(<?= $a->tabi_id; ?>);">
								<?= __("Map","bluerabbit"); ?>
							</label>
						</div>

						<!-- Category -->
						<div style="text-align:center;">
							<label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:rgba(255,255,255,0.6);">
								<input type="checkbox" id="tabi-as-category-<?= $a->tabi_id; ?>" <?= $a->tabi_as_category ? 'checked' : ''; ?> onChange="setTabiAsCategory(<?= $a->tabi_id; ?>);">
								<?= __("Group","bluerabbit"); ?>
							</label>
						</div>
					</div>

					<!-- Action bar -->
					<div class="br-action-bar">
						<span class="br-action-link" onClick="loadTabiEditor('<?= $a->tabi_id; ?>');" style="cursor:pointer;">
							<span class="icon icon-edit"></span> <?= __("Edit Parts/Layers","bluerabbit"); ?>
						</span>
						<button class="br-action-link expand" data-target="tabi-details-<?= $a->tabi_id; ?>">
							<span class="icon icon-down"></span> <?= __("Prerequisites","bluerabbit"); ?>
						</button>
						<button class="br-action-link trash" onClick="confirmStatus(<?= $a->tabi_id; ?>,'tabi','trash');">
							<span class="icon icon-trash"></span> <?= __("Trash","bluerabbit"); ?>
						</button>
					</div>

					<!-- Color select (hidden, toggled by activate) -->
					<div class="color-select-row" id="color-select-<?= $a->tabi_id; ?>" style="display:none;padding:8px 16px;background:rgba(4,22,30,0.5);">
						<?php
						$selected_color = $a->tabi_color;
						$object_color_id = $a->tabi_id;
						$object_type = 'tabi';
						?>
						<?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
					</div>

					<!-- Prerequisites panel (expandable) -->
					<div class="br-quick-edit" id="tabi-details-<?= $a->tabi_id; ?>">
						<?php
						global $wpdb;
						$this_prereqs = $wpdb->get_col("SELECT requires_tabi_id FROM {$wpdb->prefix}br_tabi_prerequisites WHERE tabi_id = $a->tabi_id");
						$this_prereqs = array_map('intval', $this_prereqs);
						?>
						<div class="br-qe-grid">
							<div class="br-qe-field" style="grid-column:1/-1;">
								<label><?= __('Requires (must complete before unlocking)','bluerabbit'); ?></label>
								<div style="display:flex;flex-wrap:wrap;gap:8px;padding:8px 0;">
									<?php if($tabis) { foreach($tabis as $pt) {
										if($pt->tabi_id == $a->tabi_id) continue; ?>
										<label style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:6px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);cursor:pointer;font-size:13px;color:rgba(255,255,255,0.7);transition:all 0.15s;">
											<input type="checkbox"
												class="tabi-prereq-checkbox"
												data-tabi-id="<?= $a->tabi_id; ?>"
												value="<?= $pt->tabi_id; ?>"
												<?= in_array((int)$pt->tabi_id, $this_prereqs) ? 'checked' : ''; ?>
												onChange="saveTabiPrerequisites(<?= $a->tabi_id; ?>);">
											<?= esc_html($pt->tabi_name); ?>
										</label>
									<?php } } else { ?>
										<span style="font-size:12px;color:rgba(255,255,255,0.3);"><?= __('No other tabis in this adventure.','bluerabbit'); ?></span>
									<?php } ?>
								</div>
							</div>
						</div>
						<input type="hidden" class="tabi-prereq-nonce" value="<?= $tabi_prereq_nonce; ?>">
					</div>

				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="br-empty">
				<span class="icon icon-sabotage"></span>
				<h3><?= __("No tabis created yet","bluerabbit"); ?></h3>
				<p><?= __("Click 'Add Tabi' to create your first one.","bluerabbit"); ?></p>
			</div>
		<?php } ?>
	</div>

	<input type="hidden" id="row_type" value="tabi"/>
	<input type="hidden" id="tabi-on-journey-nonce" value="<?= wp_create_nonce('tabi_on_journey_nonce'); ?>">
	<input type="hidden" id="tabi-as-category-nonce" value="<?= $tabi_as_category_nonce; ?>">
	<input type="hidden" id="add-tabi-nonce" value="<?= wp_create_nonce('add_tabi_nonce'); ?>">
	<input type="hidden" id="dimensions-nonce" value="<?= wp_create_nonce('dimensions_nonce'); ?>">

</div><!-- /.br-journey-manager -->

<div class="tabi-editor-container" id="tabi-editor-container"></div>

<script>
(function($){
	// Prerequisites expand toggle
	$(document).on('click', '.br-action-link.expand', function(e){
		e.preventDefault();
		var targetId = $(this).data('target');
		var panel = $('#' + targetId);
		var isOpen = panel.hasClass('open');
		$('.br-quick-edit.open').removeClass('open');
		$('.br-action-link.expand.open').removeClass('open');
		if(!isOpen){
			panel.addClass('open');
			$(this).addClass('open');
		}
	});
})(jQuery);
</script>

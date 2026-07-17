<?php
$tabis = BR_Tabi::instance()->getTabis($adventure->adventure_id);
$tabi_prereq_nonce = wp_create_nonce('tabi_prereq_nonce');
$tabi_as_category_nonce = wp_create_nonce('tabi_as_category_nonce');
$tabi_count = $tabis ? count($tabis) : 0;
?>

<div class="br-journey-manager">

	<!-- ════════════ HEADER ════════════ -->
	<div class="br-panel br-manage-header-panel">
		<div class="br-manage-header-row">
			<div class="br-manage-header-left">
				<div class="br-manage-icon-box">
					<span class="icon icon-sabotage"></span>
				</div>
				<div>
					<h2 class="br-panel-title br-manage-panel-title"><?php _e('Tabi Manager', 'bluerabbit'); ?></h2>
					<span class="br-manage-subtitle">
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
	<div class="br-panel br-manage-col-header-panel">
		<div class="br-tabi-grid br-tabi-col-header">
			<div class="br-text-center">#</div>
			<div><?= __("Name","bluerabbit"); ?></div>
			<div><?= __("Img","bluerabbit"); ?></div>
			<div><?= __("Color","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Level","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Width","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Height","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Journey","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Category","bluerabbit"); ?></div>
		</div>
	</div>

	<!-- ════════════ TABIS LIST ════════════ -->
	<div class="br-section-body" id="table-tabis">
		<?php if($tabis && count($tabis) > 0){ ?>
			<?php foreach($tabis as $avKey => $a){ ?>
				<?php $rowNumber = $avKey + 1; ?>
				<div class="br-panel br-tabi-row-panel">

					<!-- Main row -->
					<div class="br-tabi-grid br-tabi-data-row">

						<!-- # -->
						<div class="br-tabi-num">
							<?= $rowNumber; ?>
							<input type="hidden" class="tabi_id" value="<?= $a->tabi_id; ?>">
							<input type="hidden" class="tabi-id" value="<?= $a->tabi_id; ?>">
						</div>

						<!-- Name -->
						<div class="br-name">
							<input type="text" class="br-input br-tabi-input-bold" id="the_title-tabi-<?= $a->tabi_id; ?>" value="<?= esc_attr($a->tabi_name); ?>" onChange="setTitle(<?= $a->tabi_id; ?>,'tabi');">
						</div>

						<!-- Thumb -->
						<div>
							<input type="hidden" value="<?= $a->tabi_background; ?>" id="the_tabi_badge-<?= $a->tabi_id; ?>">
							<div class="br-thumb" onClick="showWPUpload('the_tabi_badge-<?= $a->tabi_id; ?>','a','tabi',<?= $a->tabi_id; ?>);" id="the_tabi_badge-<?= $a->tabi_id; ?>_thumb" style="background-image:url(<?= $a->tabi_background; ?>);"></div>
						</div>

						<!-- Color -->
						<div>
							<button class="br-type-icon br-tabi-color-btn" id="color-trigger-tabi-<?= $a->tabi_id; ?>" onClick="activate('#color-select-<?= $a->tabi_id; ?>');" style="border-color:<?= $a->tabi_color; ?>;">
								<span class="br-tabi-color-swatch" style="<?= br_color_attr($a->tabi_color, 'bg', true) ?>"></span>
							</button>
							<input type="hidden" value="<?= $a->tabi_color; ?>" id="the_tabi_color-<?= $a->tabi_id; ?>">
						</div>

						<!-- Level -->
						<div class="br-num">
							<input type="number" class="br-input br-tabi-input-center" id="the_level-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_level; ?>" onChange="setLevel(<?= $a->tabi_id; ?>,'tabi');">
						</div>

						<!-- Width -->
						<div class="br-num">
							<input type="number" class="br-input br-tabi-input-center" id="the_width-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_width; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');">
						</div>

						<!-- Height -->
						<div class="br-num">
							<input type="number" class="br-input br-tabi-input-center" id="the_height-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_height; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');">
						</div>

						<!-- Journey -->
						<div class="br-text-center">
							<label class="br-tabi-check-label">
								<input type="checkbox" id="tabi-on-journey-<?= $a->tabi_id; ?>" <?= $a->tabi_on_journey ? 'checked' : ''; ?> onChange="setTabiOnJourney(<?= $a->tabi_id; ?>);">
								<?= __("Map","bluerabbit"); ?>
							</label>
						</div>

						<!-- Category -->
						<div class="br-text-center">
							<label class="br-tabi-check-label">
								<input type="checkbox" id="tabi-as-category-<?= $a->tabi_id; ?>" <?= $a->tabi_as_category ? 'checked' : ''; ?> onChange="setTabiAsCategory(<?= $a->tabi_id; ?>);">
								<?= __("Group","bluerabbit"); ?>
							</label>
						</div>
					</div>

					<!-- Action bar -->
					<div class="br-action-bar">
						<span class="br-action-link" onClick="loadTabiEditor('<?= $a->tabi_id; ?>');">
							<span class="icon icon-edit"></span> <?= __("Edit Parts/Layers","bluerabbit"); ?>
						</span>
						<button class="br-action-link expand" data-target="tabi-details-<?= $a->tabi_id; ?>">
							<span class="icon icon-down"></span> <?= __("Prerequisites","bluerabbit"); ?>
						</button>
						<button class="br-action-link" onClick="openTabiConditionsModal(<?= $a->tabi_id; ?>);">
							<span class="icon icon-check"></span> <?= __("Conditions","bluerabbit"); ?>
						</button>
						<div class="overlay-layer tabi-conditions-overlay" id="tabi-conditions-overlay-<?= $a->tabi_id; ?>">
							<div class="tabi-conditions-modal-content" id="tabi-conditions-content-<?= $a->tabi_id; ?>">
								<span class="br-text-12 grey-400"><?php _e("Loading...","bluerabbit"); ?></span>
							</div>
						</div>
						<button class="br-action-link trash" onClick="confirmStatus(<?= $a->tabi_id; ?>,'tabi','trash');">
							<span class="icon icon-trash"></span> <?= __("Trash","bluerabbit"); ?>
						</button>
					</div>

					<!-- Color select (hidden, toggled by activate) -->
					<div class="color-select-row br-tabi-color-select-row" id="color-select-<?= $a->tabi_id; ?>">
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
								<div class="br-tabi-prereq-wrap">
									<?php if($tabis) { foreach($tabis as $pt) {
										if($pt->tabi_id == $a->tabi_id) continue; ?>
										<label class="br-tabi-prereq-label">
											<input type="checkbox"
												class="tabi-prereq-checkbox"
												data-tabi-id="<?= $a->tabi_id; ?>"
												value="<?= $pt->tabi_id; ?>"
												<?= in_array((int)$pt->tabi_id, $this_prereqs) ? 'checked' : ''; ?>
												onChange="saveTabiPrerequisites(<?= $a->tabi_id; ?>);">
											<?= esc_html($pt->tabi_name); ?>
										</label>
									<?php } } else { ?>
										<span class="br-tabi-no-others"><?= __('No other tabis in this adventure.','bluerabbit'); ?></span>
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

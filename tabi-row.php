<?php
/**
 * One tabi row in the Tabi Manager.
 *
 * Included twice, and that is the point: manage-tabis.php loops over it for the
 * initial render, and BR_Tabi::insertTabiRow() echoes it on its own when "Add
 * Tabi" appends a new one over AJAX. They used to be two separate copies of the
 * markup, so the redesigned manager listed new tabis in the pre-redesign row
 * style until the page was reloaded.
 *
 * Expects from the including scope:
 *   $a                  tabi row object (required)
 *   $rowNumber          1-based position shown in the # column
 *   $tabis              all tabis in the adventure, for the prerequisite list
 *   $tabi_prereq_nonce  nonce for saveTabiPrerequisites()
 */
global $wpdb;

$rowNumber         = $rowNumber ?? 0;
$tabis             = $tabis ?? [];
$tabi_prereq_nonce = $tabi_prereq_nonce ?? wp_create_nonce('tabi_prereq_nonce');

$this_prereqs = array_map('intval', $wpdb->get_col($wpdb->prepare(
	"SELECT requires_tabi_id FROM {$wpdb->prefix}br_tabi_prerequisites WHERE tabi_id = %d",
	$a->tabi_id
)));
?>
<div class="br-panel br-tabi-row-panel" id="tabi-<?= (int)$a->tabi_id; ?>">

	<!-- Main row -->
	<div class="br-tabi-grid br-tabi-data-row">

		<!-- # -->
		<div class="br-tabi-num">
			<?= (int)$rowNumber; ?>
			<input type="hidden" class="tabi_id" value="<?= (int)$a->tabi_id; ?>">
			<input type="hidden" class="tabi-id" value="<?= (int)$a->tabi_id; ?>">
		</div>

		<!-- Name -->
		<div class="br-name">
			<input type="text" class="br-input br-tabi-input-bold" id="the_title-tabi-<?= (int)$a->tabi_id; ?>" value="<?= esc_attr($a->tabi_name); ?>" onChange="setTitle(<?= (int)$a->tabi_id; ?>,'tabi');">
		</div>

		<!-- Thumb -->
		<div>
			<input type="hidden" value="<?= esc_attr($a->tabi_background); ?>" id="the_tabi_badge-<?= (int)$a->tabi_id; ?>">
			<div class="br-thumb" onClick="showWPUpload('the_tabi_badge-<?= (int)$a->tabi_id; ?>','a','tabi',<?= (int)$a->tabi_id; ?>);" id="the_tabi_badge-<?= (int)$a->tabi_id; ?>_thumb" style="background-image:url(<?= esc_url($a->tabi_background); ?>);"></div>
		</div>

		<!-- Color -->
		<div>
			<button class="br-type-icon br-tabi-color-btn" id="color-trigger-tabi-<?= (int)$a->tabi_id; ?>" onClick="activate('#color-select-<?= (int)$a->tabi_id; ?>');" style="<?= esc_attr(br_color_attr($a->tabi_color, 'border', true)); ?>">
				<span class="br-tabi-color-swatch" style="<?= esc_attr(br_color_attr($a->tabi_color, 'bg', true)); ?>"></span>
			</button>
			<input type="hidden" value="<?= esc_attr($a->tabi_color); ?>" id="the_tabi_color-<?= (int)$a->tabi_id; ?>">
		</div>

		<!-- Level -->
		<div class="br-num">
			<input type="number" class="br-input br-tabi-input-center" id="the_level-tabi-<?= (int)$a->tabi_id; ?>" value="<?= esc_attr($a->tabi_level); ?>" onChange="setLevel(<?= (int)$a->tabi_id; ?>,'tabi');">
		</div>

		<!-- Width -->
		<div class="br-num">
			<input type="number" class="br-input br-tabi-input-center" id="the_width-tabi-<?= (int)$a->tabi_id; ?>" value="<?= esc_attr($a->tabi_width); ?>" onChange="setDimensions(<?= (int)$a->tabi_id; ?>,'tabi');">
		</div>

		<!-- Height -->
		<div class="br-num">
			<input type="number" class="br-input br-tabi-input-center" id="the_height-tabi-<?= (int)$a->tabi_id; ?>" value="<?= esc_attr($a->tabi_height); ?>" onChange="setDimensions(<?= (int)$a->tabi_id; ?>,'tabi');">
		</div>

		<!-- Journey -->
		<div class="br-text-center">
			<label class="br-tabi-check-label">
				<input type="checkbox" id="tabi-on-journey-<?= (int)$a->tabi_id; ?>" <?= $a->tabi_on_journey ? 'checked' : ''; ?> onChange="setTabiOnJourney(<?= (int)$a->tabi_id; ?>);">
				<?= __("Map","bluerabbit"); ?>
			</label>
		</div>

		<!-- Category -->
		<div class="br-text-center">
			<label class="br-tabi-check-label">
				<input type="checkbox" id="tabi-as-category-<?= (int)$a->tabi_id; ?>" <?= $a->tabi_as_category ? 'checked' : ''; ?> onChange="setTabiAsCategory(<?= (int)$a->tabi_id; ?>);">
				<?= __("Group","bluerabbit"); ?>
			</label>
		</div>
	</div>

	<!-- Action bar -->
	<div class="br-action-bar">
		<span class="br-action-link" onClick="loadTabiEditor('<?= (int)$a->tabi_id; ?>');">
			<span class="icon icon-edit"></span> <?= __("Edit Parts/Layers","bluerabbit"); ?>
		</span>
		<button class="br-action-link expand" data-target="tabi-details-<?= (int)$a->tabi_id; ?>">
			<span class="icon icon-down"></span> <?= __("Prerequisites","bluerabbit"); ?>
		</button>
		<button class="br-action-link" onClick="openTabiConditionsModal(<?= (int)$a->tabi_id; ?>);">
			<span class="icon icon-check"></span> <?= __("Conditions","bluerabbit"); ?>
		</button>
		<div class="overlay-layer tabi-conditions-overlay" id="tabi-conditions-overlay-<?= (int)$a->tabi_id; ?>">
			<div class="tabi-conditions-modal-content" id="tabi-conditions-content-<?= (int)$a->tabi_id; ?>">
				<span class="br-text-12 grey-400"><?php _e("Loading...","bluerabbit"); ?></span>
			</div>
		</div>
		<button class="br-action-link trash" onClick="confirmStatus(<?= (int)$a->tabi_id; ?>,'tabi','trash');">
			<span class="icon icon-trash"></span> <?= __("Trash","bluerabbit"); ?>
		</button>
	</div>

	<!-- Color select (hidden, toggled by activate) -->
	<div class="color-select-row br-tabi-color-select-row" id="color-select-<?= (int)$a->tabi_id; ?>">
		<?php
		$selected_color  = $a->tabi_color;
		$object_color_id = $a->tabi_id;
		$object_type     = 'tabi';
		include (TEMPLATEPATH . '/component-set-color.php');
		?>
	</div>

	<!-- Prerequisites panel (expandable) -->
	<div class="br-quick-edit" id="tabi-details-<?= (int)$a->tabi_id; ?>">
		<div class="br-qe-grid">
			<div class="br-qe-field br-qe-field-full">
				<label><?= __('Requires (must complete before unlocking)','bluerabbit'); ?></label>
				<div class="br-tabi-prereq-wrap">
					<?php $has_others = false; ?>
					<?php foreach($tabis as $pt) {
						if($pt->tabi_id == $a->tabi_id) continue;
						$has_others = true; ?>
						<label class="br-tabi-prereq-label">
							<input type="checkbox"
								class="tabi-prereq-checkbox"
								data-tabi-id="<?= (int)$a->tabi_id; ?>"
								value="<?= (int)$pt->tabi_id; ?>"
								<?= in_array((int)$pt->tabi_id, $this_prereqs, true) ? 'checked' : ''; ?>
								onChange="saveTabiPrerequisites(<?= (int)$a->tabi_id; ?>);">
							<?= esc_html($pt->tabi_name); ?>
						</label>
					<?php } ?>
					<?php if(!$has_others) { ?>
						<span class="br-tabi-no-others"><?= __('No other tabis in this adventure.','bluerabbit'); ?></span>
					<?php } ?>
				</div>
			</div>
		</div>
		<input type="hidden" class="tabi-prereq-nonce" id="tabi-prereq-nonce-<?= (int)$a->tabi_id; ?>" value="<?= esc_attr($tabi_prereq_nonce); ?>">
	</div>

</div>

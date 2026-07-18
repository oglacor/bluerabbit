<div class="row-container <?= $a->tabi_color; ?>-bg-100" id="<?= "tabi-$a->tabi_id"; ?>">
    <div class="row admin-row with-tabi-assign">
        <div class="cell cell-tabi-id">
            <?= $rowNumber; ?>	
            <input type="hidden" class="tabi_id" value="<?= $a->tabi_id; ?>">
        </div>
        <div class="cell cell-name">
            <input type="text" class="form-ui w-full" id="the_title-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_name; ?>" onChange="setTitle(<?= $a->tabi_id; ?>,'tabi');">
            <input type="hidden" class="tabi-id" value="<?= $a->tabi_id; ?>">
            <div class="quest-links br-text-16 w600 grey-600">
                <span class="inline-block cursor-pointer" onClick="loadTabiEditor('<?= $a->tabi_id; ?>');">
                    <?php _e("Edit Parts/Layers","bluerabbit"); ?>
                </span>
            </div>
        </div>
        <div class="cell cell-badge">
            <input type="hidden" value="<?= $a->tabi_background; ?>" id="the_tabi_badge-<?= $a->tabi_id; ?>">
            <button class="br-icon-btn" onClick="showWPUpload('the_tabi_badge-<?= $a->tabi_id; ?>','a','tabi',<?= $a->tabi_id; ?>);" id="the_tabi_badge-<?= $a->tabi_id; ?>_thumb" style="background-image: url(<?= $a->tabi_background; ?>);">
            </button>
        </div>
        <div class="cell cell-color relative layer base">
            <button class="br-icon-btn" <?= br_color_attr($a->tabi_color) ?> id="color-trigger-tabi-<?= $a->tabi_id; ?>" onClick="activate('#color-select-<?=$a->tabi_id;?>');">
            </button> 
            <input type="hidden" value="<?= $a->tabi_color; ?>" id="the_tabi_color-<?= $a->tabi_id; ?>">
        </div>
        <div class="cell cell-level">
            <div class="input-group">
                <input type="number" class="form-ui w-full" id="the_level-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_level; ?>" onChange="setLevel(<?= $a->tabi_id; ?>,'tabi');">
            </div>
        </div>
        <div class="cell cell-width">
            <div class="input-group">
                <input type="number" class="form-ui w-full" id="the_width-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_width; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');">
            </div>
        </div>
        <div class="cell cell-height">
            <div class="input-group">
                <input type="number" class="form-ui w-full" id="the_height-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_height; ?>" onChange="setDimensions(<?= $a->tabi_id; ?>,'tabi');">
            </div>
        </div>
        <div class="cell cell-location">
            <div class="input-group">
                <input type="checkbox" class="form-ui tabi-on-journey-checkbox" id="tabi-on-journey-<?= $a->tabi_id; ?>" <?= $a->tabi_on_journey ? 'checked' : ''; ?> onChange="setTabiOnJourney(<?= $a->tabi_id; ?>);">
            </div>
        </div>
    </div>
    <div class="row admin-row color-select-row" id="color-select-<?= $a->tabi_id; ?>">
        <?php
        $selected_color = $a->tabi_color; 
        $object_color_id = $a->tabi_id;
        $object_type='tabi';
        ?>
        <?php include (TEMPLATEPATH . '/component-set-color.php'); ?>
    </div>
    <div class="row admin-row tabi-prereq-row">
        <div class="cell cell-full">
            <div class="tabi-prereq-list">
                <label class="tabi-prereq-label orange-bg-100">
                    <?= __('Use to group quests','bluerabbit'); ?>
                    <?php if(!isset($tabi_as_category_nonce)) { $tabi_as_category_nonce = wp_create_nonce('tabi_as_category_nonce'); } ?>
                    <input type="checkbox" class="form-ui tabi-as-category-checkbox" id="tabi-as-category-<?= $a->tabi_id; ?>" <?= $a->tabi_as_category ? 'checked' : ''; ?> onChange="setTabiAsCategory(<?= $a->tabi_id; ?>);">
                </label>
            </div>
        </div>
    </div>
    <?php
    global $wpdb;
    $this_prereqs = $wpdb->get_col("SELECT requires_tabi_id FROM {$wpdb->prefix}br_tabi_prerequisites WHERE tabi_id = $a->tabi_id");
    $this_prereqs = array_map('intval', $this_prereqs);
    if(!isset($tabi_prereq_nonce)) { $tabi_prereq_nonce = wp_create_nonce('tabi_prereq_nonce'); }
    ?>
    <div class="row admin-row tabi-prereq-row" id="tabi-prereq-row-<?= $a->tabi_id; ?>">
        <div class="cell cell-full">
            <span class="br-text-12 block grey-500"><?= __('Requires (must complete before unlocking):','bluerabbit'); ?></span>
            <div class="tabi-prereq-list">
                <?php if(isset($tabis) && $tabis) { foreach($tabis as $pt) {
                    if($pt->tabi_id == $a->tabi_id) continue; ?>
                    <label class="tabi-prereq-label <?= $pt->tabi_color ?>-bg-100">
                        <input type="checkbox"
                               class="tabi-prereq-checkbox"
                               data-tabi-id="<?= $a->tabi_id; ?>"
                               value="<?= $pt->tabi_id; ?>"
                               <?= in_array((int)$pt->tabi_id, $this_prereqs) ? 'checked' : ''; ?>
                               onChange="saveTabiPrerequisites(<?= $a->tabi_id; ?>);">
                        <?= esc_html($pt->tabi_name); ?>
                    </label>
                <?php } } else { ?>
                    <span class="br-text-12 grey-400"><?= __('No other tabis in this adventure.','bluerabbit'); ?></span>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" class="tabi-prereq-nonce" id="tabi-prereq-nonce-<?= $a->tabi_id; ?>" value="<?= $tabi_prereq_nonce; ?>">
    </div>
    <div class="row admin-row quick-edit" id="quick-edit-tabi"<?=$a->tabi_id; ?>">
        <div class="cell cell-start-date">
        </div>
        <div class="cell cell-deadline">
        </div>
        <div class="cell cell-path">
        </div>
        <div class="cell cell-actions">
            <button class="form-ui button br-text-12 blue-bg-200 white-color conditions-button" onClick="openTabiConditionsModal(<?= $a->tabi_id; ?>);">
                <?php _e("Conditions","bluerabbit"); ?>
            </button>
            <div class="overlay-layer tabi-conditions-overlay" id="tabi-conditions-overlay-<?= $a->tabi_id; ?>">
                <div class="tabi-conditions-modal-content" id="tabi-conditions-content-<?= $a->tabi_id; ?>">
                    <span class="br-text-12 grey-400"><?php _e("Loading...","bluerabbit"); ?></span>
                </div>
            </div>
            <button class="form-ui button br-text-12 red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $a->tabi_id; ?>');">
                <?php _e("Send to trash","bluerabbit"); ?>
            </button>
            <div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $a->tabi_id; ?>">
                <button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $a->tabi_id; ?>,'tabi','trash');">
                    <span class="icon-group">
                        <span class="br-icon-btn br-icon-btn-red-dark">
                            <span class="icon icon-trash white-color"></span>
                        </span>
                        <span class="icon-content">
                            <span class="line red-A400 br-text-18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
                        </span>
                    </span>
                </button>
                <button class="br-close-btn" onClick="hideAllOverlay();">
                    <span class="icon icon-cancel white-color"></span>
                </button>
            </div>
        </div>
    </div>
</div>

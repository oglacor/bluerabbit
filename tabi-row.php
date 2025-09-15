<div class="row-container <?= $a->tabi_color; ?>-bg-100" id="<?= "tabi-$a->tabi_id"; ?>">
    <div class="row admin-row with-tabi-assign">
        <div class="cell cell-tabi-id">
            <?= $rowNumber; ?>	
            <input type="hidden" class="tabi_id" value="<?= $a->tabi_id; ?>">
        </div>
        <div class="cell cell-name">
            <input type="text" class="form-ui w-full" id="the_title-tabi-<?= $a->tabi_id; ?>" value="<?= $a->tabi_name; ?>" onChange="setTitle(<?= $a->tabi_id; ?>,'tabi');">
            <input type="hidden" class="tabi-id" value="<?= $a->tabi_id; ?>">
            <div class="quest-links font _16 w600 grey-600">
                <span class="inline-block cursor-pointer" onClick="loadTabiEditor('<?= $a->tabi_id; ?>');">
                    <?php _e("Edit Parts/Layers","bluerabbit"); ?>
                </span>
            </div>
        </div>
        <div class="cell cell-badge">
            <input type="hidden" value="<?= $a->tabi_background; ?>" id="the_tabi_badge-<?= $a->tabi_id; ?>">
            <button class="icon-button font _24 sq-40 " onClick="showWPUpload('the_tabi_badge-<?= $a->tabi_id; ?>','a','tabi',<?= $a->tabi_id; ?>);" id="the_tabi_badge-<?= $a->tabi_id; ?>_thumb" style="background-image: url(<?= $a->tabi_background; ?>);">
            </button>
        </div>
        <div class="cell cell-color relative layer base">
            <button class="icon-button font _24 sq-40 <?=$a->tabi_color;?>-bg-400" id="color-trigger-tabi-<?= $a->tabi_id; ?>" onClick="activate('#color-select-<?=$a->tabi_id;?>');">
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
                <input type="checkbox" class="form-ui tabi-on-journey-checkbox" id="tabi-on-journey-<?= $a->tabi_id; ?>" <?= $a->tabi_fixed ? 'checked' : ''; ?> onChange="setTabiOnJourney(<?= $a->tabi_id; ?>);">
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
    <div class="row admin-row quick-edit" id="quick-edit-tabi"<?=$a->tabi_id; ?>">
        <div class="cell cell-start-date">
        </div>
        <div class="cell cell-deadline">
        </div>
        <div class="cell cell-path">
        </div>
        <div class="cell cell-actions">
            <button class="form-ui button font _12 red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?= $a->tabi_id; ?>');">
                <?php _e("Send to trash","bluerabbit"); ?>
            </button>
            <div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?= $a->tabi_id; ?>">
                <button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?= $a->tabi_id; ?>,'tabi','trash');">
                    <span class="icon-group">
                        <span class="icon-button font _24 sq-40  icon-sm red-bg-A400 icon-sm">
                            <span class="icon icon-trash white-color"></span>
                        </span>
                        <span class="icon-content">
                            <span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
                        </span>
                    </span>
                </button>
                <button class="close-confirm icon-button font _24 sq-40  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
                    <span class="icon icon-cancel white-color"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<svg width="0" height="0" style="position: absolute;">
  <defs>
    <clipPath id="tabi-screen-clip" clipPathUnits="objectBoundingBox">
        <polygon points="
            1.0,0.92709 0.9423,0.92709 0.86939,1.0 0.05628,1.0 
            0.0,0.94372 0.0,0.58668 0.02369,0.56299 0.02369,0.3009 
            0.0,0.27722 0.0,0.03606 0.03606,0.0 0.41379,0.0 
            0.43914,0.02535 0.97519,0.02535 1.0,0.05016 1.0,0.32177 
            0.98533,0.33643 0.98533,0.39657 1.0,0.41123 
            1.0,0.50071 0.98011,0.52059 0.98011,0.58986 
            1.0,0.60975 1.0,0.92709
        "/>
    </clipPath>
  </defs>
</svg>


<?php $tabi_data = getTabi($id); 
$t = $tabi_data['tabi'];
$tabi_pieces = $tabi_data['pieces']

?>
<div id="tabi-editor" class="tabi-editor">
    <div class="tabi-editor-header">
        <h2 class=""><?= __("Current TABI","bluerabbit"); ?></h2>
        <h1 class=""><?= $t->tabi_name; ?></h2>
        <button class="form-ui" onClick="unloadContent('#tabi-editor-container')"><?= __("Close","bluerabbit"); ?></button>
    </div>
    <div class="tabi-builder" id="tabi-builder">
        <div class="tabi-hud">
            <div class="hud-display active" id="hud-display-<?=$t->tabi_id; ?>">
                <div class="hud-screen-container active">
                    <div class="hud-screen-content">
                        <div class="tabi-pieces" id="tabi-pieces" style="background-image: url('<?= $t->tabi_background; ?>');">
                            <?php if($tabi_pieces){ ?>
                                <?php foreach($tabi_pieces as $piece){ ?>
                                    <div class="tabi-piece" id="tabi-piece-<?= $piece->item_id; ?>" data-piece-id="<?= $piece->item_id; ?>" style="width:<?= $piece->item_scale > 0 ? $piece->item_scale : 10 ; ?>%;">
                                        <div class="tabi-piece-controls">
                                            <button class="z-up" onClick="zUp(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-z-up.svg">
                                            </button>
                                            <button class="z-down" onClick="zDown(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-z-down.svg">
                                            </button>
                                            <button class="scale-up" onClick="scaleUp(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-scale-up.svg">
                                            </button>
                                            <button class="scale-down" onClick="scaleDown(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-scale-down.svg">
                                            </button>
                                            <button class="rotate-cw" onClick="rotateCW(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-rotate-cw.svg">
                                            </button>
                                            <button class="rotate-ccw" onClick="rotateCCW(<?= $piece->item_id; ?>);">
                                                <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-rotate-ccw.svg">
                                            </button>
                                        </div>
                                        <div class="tabi-piece-image" id="tabi-piece-image-<?= $piece->item_id;?>">
                                            <img src="<?= $piece->item_badge; ?>" alt="">
                                        </div>
                                        <div class="tabi-piece-data">
                                            <input type="hidden" class="piece-id" value="<?= $piece->item_id; ?>">
                                            <input type="hidden" class="piece-x" value="<?= $piece->item_x; ?>">
                                            <input type="hidden" class="piece-y" value="<?= $piece->item_y; ?>">
                                            <input type="hidden" class="piece-z" value="<?= $piece->item_z > 0 ? $piece->item_z : 1; ?>">
                                            <input type="hidden" class="piece-scale" value="<?= $piece->item_scale > 0 ? $piece->item_scale : 10 ; ?>">
                                            <input type="hidden" class="piece-rotation" value="<?= $piece->item_rotation; ?>">
                                            <input type="hidden" class="piece-name" value="<?= $piece->item_name; ?>">
                                            <input type="hidden" class="piece-image" value="">
                                        </div>
                                        <div class="tabi-piece-content">
                                            <div class="tabi-piece-name"><?= $piece->item_name; ?></div>
                                            <div class="tabi-piece-content-meta">
                                                <div class="tabi-piece-z tabi-meta">
                                                    z: <span class="tabi-meta-text"><?= $piece->item_z; ?></span>
                                                </div>
                                                <div class="tabi-piece-scale tabi-meta">
                                                    scale: <span class="tabi-meta-value"><?= $piece->item_scale; ?></span>
                                                </div>
                                                <div class="tabi-piece-rotation tabi-meta">
                                                    <span class="tabi-meta-value"><?= $piece->item_rotation; ?></span>&deg;
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <svg id="tabi-<?=$t->tabi_id; ?>" class="hud-screen-graphics" " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 1920">
                        <polygon class="screen-cover" points="1920 1780.01 1809.21 1780.01 1669.22 1920 108.06 1920 0 1811.94 0 1126.42 45.48 1080.95 45.48 577.73 0 532.26 0 69.23 69.23 0 794.47 0 843.15 48.67 1872.37 48.67 1920 96.3 1920 617.79 1891.84 645.94 1891.84 761.42 1920 789.57 1920 961.36 1881.82 999.54 1881.82 1132.54 1920 1170.72 1920 1780.01"/>
                        <path class="screen-outline" d="M120.49,1890l-90.49-90.49v-660.66l45.48-45.48v-528.06l-45.48-45.48V81.66l51.66-51.66h700.39l48.67,48.67h1029.22l30.06,30.06v496.63l-28.16,28.16v140.33l28.16,28.16v146.94l-38.18,38.18v157.85l38.18,38.18v566.87h-93.22l-139.99,139.99H120.49M108.06,1920h1561.16l139.99-139.99h110.79v-609.29l-38.18-38.18v-133l38.18-38.18v-171.79l-28.16-28.16v-115.48l28.16-28.15V96.3l-47.63-47.63H843.15L794.47,0H69.23L0,69.23v463.02l45.48,45.48v503.21L0,1126.42v685.51l108.06,108.06h0Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <div class="tabi-editor-pieces-list">
        <div class="tabi-editor-pieces-list-header">
            <h3><?= __("Available Pieces","bluerabbit"); ?></h3>
        </div>
        <div class="tabi-editor-pieces-list-content">
            <?php if($tabi_pieces){ ?>
                <ul class="tabi-editor-pieces-list-sortable" id="tabi-editor-pieces-list-sortable">
                <?php foreach($tabi_pieces as $piece){ ?>
                    <li class="tabi-piece-list-item" data-piece-id="<?= $piece->item_id; ?>" id="list-item-piece-<?= $piece->item_id; ?>">
                        <div class="tabi-piece-name-container">
                            <button class="select" onClick="editTabiPiece(<?= $piece->item_id; ?>);">
                                <span class="select-text"><?= __("Select","bluerabbit"); ?></span>
                                <span class="editing-text"><?= __("Stop","bluerabbit"); ?></span>
                            </button>
                            <button class="reset" onClick="resetTabiPiece(<?= $piece->item_id; ?>);"><?= __("Reset","bluerabbit"); ?></button>
                            <div class="tabi-piece-item-name">
                                <?= $piece->item_name; ?>
                            </div>
                        </div>
                        <div class="tabi-piece-data-container">
                            <div class="data-table">
                            <div class="data-header data-row">
                                <div class="data-cell"><?= __("Scale","bluerabbit"); ?></div>
                                <div class="data-cell"><?= __("Rotation","bluerabbit"); ?></div>
                                <div class="data-cell"><?= __("Z-Index","bluerabbit"); ?></div>
                            </div>
                            <div class="data-body data-row">
                                <div class="data-cell data-piece-scale"><?= $piece->item_scale; ?></div>
                                <div class="data-cell data-piece-rotation"><?= $piece->item_rotation; ?></div>
                                <div class="data-cell data-piece-z"><?= $piece->item_z; ?></div>
                            </div>
                        </div>
                    </li>
                <?php } ?>
                </ul>
            <?php } else { ?>
                <p><?= __("No pieces available","bluerabbit"); ?></p>
            <?php } ?>
        </div>
    </div>
</div>


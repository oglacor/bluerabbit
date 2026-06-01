<?php
// Variables in scope: $a (asset row), $journey_asset_nonce
$img_id = "journey-asset-img-{$a->asset_id}";
?>
<div class="journey-asset builder-asset"
     id="journey-asset-<?= $a->asset_id; ?>"
     data-asset-id="<?= $a->asset_id; ?>"
     style="top:<?= $a->asset_top; ?>px; left:<?= $a->asset_left; ?>px; width:<?= $a->asset_width; ?>px; z-index:<?= $a->asset_z; ?>;">

    <div class="asset-visual" style="transform:rotate(<?= $a->asset_rotation; ?>deg);">
        <?php if($a->asset_image){ ?>
            <img class="asset-img" src="<?= esc_url($a->asset_image); ?>" alt="" draggable="false">
        <?php }else{ ?>
            <div class="asset-empty-placeholder pointer-cursor" onClick="pickJourneyAssetImage(<?= $a->asset_id; ?>);"><?= __('Click to set graphic','bluerabbit'); ?></div>
        <?php } ?>
    </div>

    <input type="hidden" id="<?= $img_id; ?>" value="<?= esc_url($a->asset_image); ?>">

    <div class="asset-controls">
        <button class="asset-btn" title="<?= __('Set image','bluerabbit'); ?>"
                onClick="pickJourneyAssetImage(<?= $a->asset_id; ?>);">
            <span class="icon icon-image"></span>
        </button>
        <button class="asset-btn" title="<?= __('Bring forward','bluerabbit'); ?>"
                onClick="assetZUp(<?= $a->asset_id; ?>);">
            <span class="icon icon-arrow-up"></span>
        </button>
        <button class="asset-btn" title="<?= __('Send back','bluerabbit'); ?>"
                onClick="assetZDown(<?= $a->asset_id; ?>);">
            <span class="icon icon-arrow-down"></span>
        </button>
        <button class="asset-btn" title="<?= __('Duplicate','bluerabbit'); ?>"
                onClick="duplicateJourneyAsset(<?= $a->asset_id; ?>);">
            <span class="icon icon-duplicate"></span>
        </button>
        <button class="asset-btn danger" title="<?= __('Remove','bluerabbit'); ?>"
                onClick="trashJourneyAsset(<?= $a->asset_id; ?>);">
            <span class="icon icon-trash"></span>
        </button>
    </div>
    <button class="asset-btn asset-rotate-btn" title="<?= __('Drag to rotate','bluerabbit'); ?>"
            onMouseDown="startAssetRotate(event, <?= $a->asset_id; ?>);">
        <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-rotate.svg" alt="Rotate">
    </button>

    <input type="hidden" class="asset-nonce" value="<?= $journey_asset_nonce; ?>">
    <input type="hidden" class="asset-width-val" value="<?= $a->asset_width; ?>">
    <input type="hidden" class="asset-z-val" value="<?= $a->asset_z; ?>">
    <input type="hidden" class="asset-rotation-val" value="<?= $a->asset_rotation; ?>">
</div>

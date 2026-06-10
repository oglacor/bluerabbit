<?php
// Variables in scope: $a (asset row), $journey_asset_nonce
$img_id     = "journey-asset-img-{$a->asset_id}";
$asset_type = $a->asset_type ?? 'graphic';
$asset_link = $a->asset_link ?? '';
?>
<div class="journey-asset builder-asset"
     id="journey-asset-<?= $a->asset_id; ?>"
     data-asset-id="<?= $a->asset_id; ?>"
     data-asset-type="<?= esc_attr($asset_type); ?>"
     style="top:<?= $a->asset_top; ?>px; left:<?= $a->asset_left; ?>px; width:<?= $a->asset_width; ?>px; z-index:<?= $a->asset_z; ?>;">

    <div class="asset-visual" style="transform:rotate(<?= $a->asset_rotation; ?>deg);">
        <?php if($asset_type === 'widget-status'): ?>
            <div class="asset-widget-preview asset-widget-status-preview">
                <span class="icon icon-star"></span> <?= __('Status Widget','bluerabbit'); ?>
            </div>
        <?php elseif($asset_type === 'widget-leaderboard'): ?>
            <div class="asset-widget-preview asset-widget-leaderboard-preview">
                <span class="icon icon-level"></span> <?= __('Leaderboard Widget','bluerabbit'); ?>
            </div>
        <?php else: ?>
            <?php if($a->asset_image): ?>
                <img class="asset-img" src="<?= esc_url($a->asset_image); ?>" alt="" draggable="false">
            <?php else: ?>
                <div class="asset-empty-placeholder pointer-cursor" onClick="pickJourneyAssetImage(<?= $a->asset_id; ?>);"><?= __('Click to set graphic','bluerabbit'); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <input type="hidden" id="<?= $img_id; ?>" value="<?= esc_url($a->asset_image ?? ''); ?>">

    <div class="asset-controls">
        <div class="asset-controls-row">
            <button class="asset-btn asset-type-btn<?= $asset_type==='graphic'?' active':''; ?>"
                    data-type="graphic"
                    title="<?= __('Graphic','bluerabbit'); ?>"
                    onClick="setAssetType(<?= $a->asset_id; ?>, 'graphic');">IMG</button>
            <button class="asset-btn asset-type-btn<?= $asset_type==='widget-status'?' active':''; ?>"
                    data-type="widget-status"
                    title="<?= __('Status Widget','bluerabbit'); ?>"
                    onClick="setAssetType(<?= $a->asset_id; ?>, 'widget-status');">STAT</button>
            <button class="asset-btn asset-type-btn<?= $asset_type==='widget-leaderboard'?' active':''; ?>"
                    data-type="widget-leaderboard"
                    title="<?= __('Leaderboard Widget','bluerabbit'); ?>"
                    onClick="setAssetType(<?= $a->asset_id; ?>, 'widget-leaderboard');">RANK</button>
            <span class="asset-sep"></span>
            <button class="asset-btn asset-graphic-only" title="<?= __('Set image','bluerabbit'); ?>"
                    style="<?= $asset_type!=='graphic'?'display:none;':''; ?>"
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
            <button class="asset-btn asset-link-toggle<?= $asset_link?' active':''; ?>"
                    title="<?= __('Set link URL','bluerabbit'); ?>"
                    onClick="toggleAssetLink(<?= $a->asset_id; ?>);">URL</button>
            <button class="asset-btn" title="<?= __('Duplicate','bluerabbit'); ?>"
                    onClick="duplicateJourneyAsset(<?= $a->asset_id; ?>);">
                <span class="icon icon-duplicate"></span>
            </button>
            <button class="asset-btn danger" title="<?= __('Remove','bluerabbit'); ?>"
                    onClick="trashJourneyAsset(<?= $a->asset_id; ?>);">
                <span class="icon icon-trash"></span>
            </button>
        </div>
        <div class="asset-link-row" style="display:<?= $asset_link ? 'flex' : 'none'; ?>;">
            <input type="url" class="asset-link-input" placeholder="https://..."
                   value="<?= esc_attr($asset_link); ?>"
                   onBlur="saveAssetLink(<?= $a->asset_id; ?>, this.value);"
                   onKeyDown="if(event.key==='Enter'){this.blur();}">
        </div>
    </div>

    <button class="asset-btn asset-rotate-btn" title="<?= __('Drag to rotate','bluerabbit'); ?>"
            onMouseDown="startAssetRotate(event, <?= $a->asset_id; ?>);">
        <img src="<?= get_bloginfo('template_directory'); ?>/images/tabi-ux/tabi-piece-controller-rotate.svg" alt="Rotate">
    </button>

    <input type="hidden" class="asset-nonce" value="<?= $journey_asset_nonce; ?>">
    <input type="hidden" class="asset-width-val" value="<?= $a->asset_width; ?>">
    <input type="hidden" class="asset-z-val" value="<?= $a->asset_z; ?>">
    <input type="hidden" class="asset-rotation-val" value="<?= $a->asset_rotation; ?>">
    <input type="hidden" class="asset-type-val" value="<?= esc_attr($asset_type); ?>">
    <input type="hidden" class="asset-link-val" value="<?= esc_attr($asset_link); ?>">
</div>

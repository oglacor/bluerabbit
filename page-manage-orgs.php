<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if (!$isAdmin) { wp_redirect(get_bloginfo('url').'/404'); exit; }
global $wpdb;
$orgs = $wpdb->get_results(
    "SELECT o.*,
        (SELECT COUNT(*) FROM {$wpdb->prefix}br_player_org po WHERE po.org_id = o.org_id) AS player_count,
        (SELECT COUNT(*) FROM {$wpdb->prefix}br_org_adventure oa WHERE oa.org_id = o.org_id) AS adventure_count
     FROM {$wpdb->prefix}br_orgs o
     ORDER BY o.org_name ASC"
);
?>
<div class="br-page">

<input type="hidden" id="manage-orgs-nonce" value="<?= wp_create_nonce('br_manage_orgs_nonce'); ?>">

<!-- ── Page Header ──────────────────────────────────────────────────────── -->
<div class="br-panel br-page-header">
    <div class="br-page-header-avatar br-avatar-orange">
        <span class="icon icon-tools br-icon-lg br-icon-orange"></span>
    </div>
    <div class="br-flex-1">
        <span class="br-page-subtitle"><?= __('Create and manage your organizations','bluerabbit'); ?></span>
        <h1 class="br-page-title"><?= __('Organizations','bluerabbit'); ?></h1>
    </div>
    <div class="br-actions br-ml-auto">
        <span class="br-badge br-badge-blue"><?= count($orgs); ?> <?= __('orgs','bluerabbit'); ?></span>
        <button class="br-btn cyan" onclick="toggleNewOrgPanel();">
            <span class="icon icon-add"></span> <?= __('New Organization','bluerabbit'); ?>
        </button>
    </div>
</div>

<!-- ── New Org Form ─────────────────────────────────────────────────────── -->
<div class="br-panel br-initially-hidden" id="new-org-panel">
    <h3 class="br-panel-title"><span class="icon icon-add"></span> <?= __('New Organization','bluerabbit'); ?></h3>

    <div class="br-form-group">
        <label class="br-form-label"><?= __('Name','bluerabbit'); ?></label>
        <input class="br-input" type="text" id="new-org-name" maxlength="80"
               placeholder="<?= esc_attr(__('Organization name…','bluerabbit')); ?>">
    </div>

    <div class="br-form-group">
        <label class="br-form-label"><?= __('Color','bluerabbit'); ?></label>
        <?php
        // A new org starts on a real colour rather than an empty picker, and
        // $selected_color is what tells color-select.php which swatch is active.
        $selected_color  = '#1cc2eb';
        $color_select_id = "#new-org-color";
        ?>
        <input id="new-org-color" class="color-selected" type="hidden" value="<?= esc_attr($selected_color); ?>">
        <?php include TEMPLATEPATH.'/color-select.php'; ?>
    </div>

    <div class="br-form-group">
        <label class="br-form-label"><?= __('Status','bluerabbit'); ?></label>
        <select class="br-input" id="new-org-status">
            <option value="publish"><?= __('Published','bluerabbit'); ?></option>
            <option value="draft"><?= __('Draft','bluerabbit'); ?></option>
        </select>
    </div>

    <div class="br-form-footer">
        <button class="br-btn ghost" onclick="toggleNewOrgPanel();">
            <span class="icon icon-cancel"></span> <?= __('Cancel','bluerabbit'); ?>
        </button>
        <button class="br-btn green" onclick="orgCreate();">
            <span class="icon icon-check"></span> <?= __('Create Organization','bluerabbit'); ?>
        </button>
    </div>
</div>

<!-- ── Org List ─────────────────────────────────────────────────────────── -->
<div class="br-panel" id="orgs-list-panel">
    <?php if ($orgs): ?>
    <div class="br-panel-header">
        <h3 class="br-panel-title">
            <span class="icon icon-tools"></span> <?= __('All Organizations','bluerabbit'); ?>
        </h3>
        <input type="text" class="br-input br-input-auto" id="filter-orgs"
               placeholder="<?= esc_attr(__('Filter…','bluerabbit')); ?>">
    </div>
    <div class="br-table-scroll">
        <table class="br-table">
            <thead>
                <tr>
                    <th><?= __('ID','bluerabbit'); ?></th>
                    <th><?= __('Name','bluerabbit'); ?></th>
                    <th><?= __('Status','bluerabbit'); ?></th>
                    <th><?= __('Players','bluerabbit'); ?></th>
                    <th><?= __('Adventures','bluerabbit'); ?></th>
                    <th><?= __('Actions','bluerabbit'); ?></th>
                </tr>
            </thead>
            <tbody id="orgs-list">
                <?php foreach ($orgs as $o):
                    $status_class = $o->org_status === 'publish' ? 'br-badge-green' : 'br-badge-amber';
                ?>
                <tr id="org-row-<?= (int)$o->org_id; ?>">
                    <td><?= (int)$o->org_id; ?></td>
                    <td><span class="br-text-medium"><?= esc_html($o->org_name); ?></span></td>
                    <td><span class="br-badge <?= $status_class; ?>"><?= esc_html($o->org_status); ?></span></td>
                    <td><?= (int)$o->player_count; ?></td>
                    <td><?= (int)$o->adventure_count; ?></td>
                    <td>
                        <div class="br-actions">
                            <a class="br-btn cyan br-btn-sm"
                               href="<?= esc_url(get_bloginfo('url').'/organization/?id='.(int)$o->org_id); ?>">
                                <span class="icon icon-tools"></span> <?= __('Manage','bluerabbit'); ?>
                            </a>
                            <button class="br-btn red br-btn-sm"
                                title="<?= esc_attr(__('Delete organization','bluerabbit')); ?>"
                                onclick="brConfirmInline(this,'<?= esc_js(__('Delete org?','bluerabbit')); ?>',function(){ orgDelete(<?= (int)$o->org_id; ?>); });">
                                <span class="icon icon-cancel"></span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="br-empty">
        <span class="icon icon-tools"></span>
        <h3><?= __('No organizations yet','bluerabbit'); ?></h3>
        <p><?= __('Click "New Organization" to create your first one.','bluerabbit'); ?></p>
    </div>
    <?php endif; ?>
</div>

</div><!-- /.br-page -->


<?php include (get_stylesheet_directory() . '/footer.php'); ?>

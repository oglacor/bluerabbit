<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
global $wpdb;

// Orgs where the current player is manager
$managed_orgs = $wpdb->get_results($wpdb->prepare(
    "SELECT o.*
     FROM {$wpdb->prefix}br_orgs o
     JOIN {$wpdb->prefix}br_player_org po ON o.org_id = po.org_id
     WHERE po.player_id = %d AND po.role = 'manager' AND o.org_status = 'publish'
     ORDER BY o.org_name ASC",
    $current_user->ID
));

if (!$managed_orgs && !$isAdmin) {
    wp_redirect(get_bloginfo('url') . '/404'); exit;
}
?>
<div class="br-page">

<!-- ── Page Header ──────────────────────────────────────────────────────── -->
<div class="br-panel br-page-header">
    <div class="br-page-header-avatar br-avatar-teal">
        <span class="icon icon-players br-icon-lg br-icon-teal"></span>
    </div>
    <div class="br-flex-1">
        <span class="br-page-subtitle"><?= __('Your organization dashboard','bluerabbit'); ?></span>
        <h1 class="br-page-title"><?= __('My Organizations','bluerabbit'); ?></h1>
    </div>
    <div class="br-actions br-ml-auto">
        <span class="br-badge br-badge-teal"><?= count($managed_orgs); ?> <?= __('managed','bluerabbit'); ?></span>
    </div>
</div>

<?php if (!$managed_orgs): ?>
<div class="br-panel">
    <div class="br-empty">
        <span class="icon icon-players"></span>
        <h3><?= __('No organizations assigned','bluerabbit'); ?></h3>
        <p><?= __('Ask an administrator to assign you as a manager of an organization.','bluerabbit'); ?></p>
    </div>
</div>
<?php endif; ?>

<?php foreach ($managed_orgs as $managed_org):
    // Players in this org who are also in any adventure this manager owns or runs as GM
    $org_players = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT p.player_id, p.player_display_name, p.player_first, p.player_last,
                p.player_email, p.player_picture, p.player_absolute_level,
                GROUP_CONCAT(DISTINCT a.adventure_title ORDER BY a.adventure_title SEPARATOR ', ') AS adventure_names
         FROM {$wpdb->prefix}br_players p
         JOIN {$wpdb->prefix}br_player_org po  ON p.player_id = po.player_id  AND po.org_id = %d
         JOIN {$wpdb->prefix}br_player_adventure pa ON p.player_id = pa.player_id
              AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'
         JOIN {$wpdb->prefix}br_adventures a ON pa.adventure_id = a.adventure_id
              AND (a.adventure_owner = %d OR a.adventure_id IN (
                  SELECT adventure_id FROM {$wpdb->prefix}br_player_adventure
                  WHERE player_id = %d AND player_adventure_role = 'gm'
              ))
         GROUP BY p.player_id
         ORDER BY p.player_display_name ASC",
        $managed_org->org_id, $current_user->ID, $current_user->ID
    )) ?: [];
    $total = count($org_players);
?>

<div class="br-panel">
    <div class="br-panel-header">
        <h3 class="br-panel-title">
            <span class="icon icon-tools"></span> <?= esc_html($managed_org->org_name); ?>
        </h3>
        <span class="br-badge br-badge-blue"><?= $total; ?> <?= __('players','bluerabbit'); ?></span>
        <?php if ($total > 0): ?>
        <input type="text" class="br-input br-input-auto" placeholder="<?= esc_attr(__('Filter…','bluerabbit')); ?>"
               oninput="orgMgrFilter(this, 'org-mgr-list-<?= (int)$managed_org->org_id; ?>');">
        <?php endif; ?>
    </div>
    <p class="br-form-hint"><?= __('Showing members enrolled in your adventures.','bluerabbit'); ?></p>

    <?php if ($org_players): ?>
    <div class="br-table-scroll">
        <table class="br-table">
            <thead>
                <tr>
                    <th><?= __('Player','bluerabbit'); ?></th>
                    <th><?= __('Email','bluerabbit'); ?></th>
                    <th><?= __('Level','bluerabbit'); ?></th>
                    <th><?= __('Adventures','bluerabbit'); ?></th>
                </tr>
            </thead>
            <tbody id="org-mgr-list-<?= (int)$managed_org->org_id; ?>">
                <?php foreach ($org_players as $p):
                    $name = $p->player_display_name ?: trim($p->player_first . ' ' . $p->player_last);
                ?>
                <tr>
                    <td>
                        <div class="br-actions">
                            <?php if ($p->player_picture): ?>
                            <img class="br-ap-avatar" src="<?= esc_url($p->player_picture); ?>"
                                 alt="<?= esc_attr($name); ?>">
                            <?php else: ?>
                            <div class="br-ap-avatar"></div>
                            <?php endif; ?>
                            <span class="w500"><?= esc_html($name); ?></span>
                        </div>
                    </td>
                    <td><?= esc_html($p->player_email); ?></td>
                    <td><?= (int)$p->player_absolute_level; ?></td>
                    <td class="br-text-12-muted"><?= esc_html($p->adventure_names); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="br-empty">
        <span class="icon icon-players"></span>
        <p><?= __('No players from your adventures are in this organization yet.','bluerabbit'); ?></p>
    </div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

</div><!-- /.br-page -->

<script>
function orgMgrFilter(input, tbodyId) {
    var val = input.value.toLowerCase();
    document.querySelectorAll('#' + tbodyId + ' tr').forEach(function(tr){
        tr.style.display = (!val || tr.textContent.toLowerCase().includes(val)) ? '' : 'none';
    });
}
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

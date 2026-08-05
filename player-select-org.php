<?php
$_pid   = (int)$p->player_id;
$_name  = $p->player_display_name ?: trim($p->player_first . ' ' . $p->player_last);
$_level = (int)($p->player_absolute_level ?? 0);
$_pic   = $p->player_picture ?? '';
?>
<div class="br-ap-row" id="org-ap-row-<?= $_pid; ?>">
	<?php if ($_pic): ?>
	<img class="br-ap-avatar" src="<?= esc_url($_pic); ?>" alt="<?= esc_attr($_name); ?>">
	<?php else: ?>
	<div class="br-ap-avatar"></div>
	<?php endif; ?>
	<div class="br-ap-identity">
		<span class="br-ap-name"><?= esc_html($_name); ?></span>
		<span class="br-ap-meta">Lv <?= $_level; ?> &middot; <span class="br-ap-email"><?= esc_html($p->player_email); ?></span></span>
	</div>
	<div class="br-ap-action">
		<button class="br-btn cyan br-btn-sm" onclick="orgApAddPlayer(<?= $_pid; ?>, this);">
			<span class="icon icon-add"></span> <?= __('Add','bluerabbit'); ?>
		</button>
	</div>
</div>

<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure && ($isGM || $isNPC || $isAdmin)){ ?>
<?php
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$where = $wpdb->prepare("o.adventure_id=%d", $adventure->adventure_id);
if ($status_filter) {
	$where .= $wpdb->prepare(" AND o.status=%s", $status_filter);
}
$orders = $wpdb->get_results("SELECT
	o.*, p.player_display_name, p.player_email, i.item_name
	FROM {$wpdb->prefix}br_tremendous_orders o
	LEFT JOIN {$wpdb->prefix}br_players p ON o.player_id = p.player_id
	LEFT JOIN {$wpdb->prefix}br_items i ON o.item_id = i.item_id
	WHERE $where
	ORDER BY o.created_at DESC LIMIT 1000
");

$status_badges = array(
	'sent'              => 'br-badge-green',
	'pending'           => 'br-badge-amber',
	'failed'            => 'br-badge-red',
	'duplicate_blocked' => 'br-badge-purple',
);
?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar"><span class="icon icon-bloo"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
			<h1 class="br-page-title"><?= __("Tremendous Gift Card Orders","bluerabbit"); ?></h1>
		</div>
	</div>

	<!-- Filters -->
	<div class="br-panel">
		<div class="br-actions">
			<a class="br-btn <?= !$status_filter ? 'br-btn-blue' : ''; ?>" href="<?= get_bloginfo('url')."/tremendous-orders/?adventure_id=$adventure->adventure_id"; ?>"><?= __("All","bluerabbit"); ?></a>
			<?php foreach(array('sent','pending','failed','duplicate_blocked') as $s){ ?>
				<a class="br-btn <?= $status_filter==$s ? 'br-btn-blue' : ''; ?>" href="<?= get_bloginfo('url')."/tremendous-orders/?adventure_id=$adventure->adventure_id&status=$s"; ?>"><?= esc_html(ucwords(str_replace('_',' ',$s))); ?></a>
			<?php } ?>
		</div>
	</div>

	<!-- Orders table -->
	<div class="br-panel">
		<table class="br-table" id="table-tremendous-orders">
			<thead>
				<tr>
					<th><?= __("Date","bluerabbit"); ?></th>
					<th><?= __("Player","bluerabbit"); ?></th>
					<th><?= __("Item","bluerabbit"); ?></th>
					<th class="text-center"><?= __("Amount","bluerabbit"); ?></th>
					<th class="text-center"><?= __("Status","bluerabbit"); ?></th>
					<th class="text-center"><?= __("Mode","bluerabbit"); ?></th>
					<th><?= __("Tremendous","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if($orders){ foreach($orders as $o){
					$rewards_host = $o->sandbox ? 'https://testflight.tremendous.com' : 'https://www.tremendous.com';
				?>
				<tr>
					<td>
						<?php $d = strtotime($o->created_at); ?>
						<span><?= date('M j, Y', $d); ?></span>
						<span class="br-text-12-muted"><?= date('g:i A', $d); ?></span>
					</td>
					<td>
						<?= esc_html($o->player_display_name); ?>
						<div class="br-text-12-muted"><?= esc_html($o->recipient_email); ?></div>
					</td>
					<td><?= esc_html($o->item_name); ?></td>
					<td class="text-center"><?= number_format((float) $o->amount, 2); ?> <?= esc_html($o->currency_code); ?></td>
					<td class="text-center">
						<span class="br-badge <?= $status_badges[$o->status] ?? 'br-badge-blue'; ?>"><?= esc_html(ucwords(str_replace('_',' ',$o->status))); ?></span>
					</td>
					<td class="text-center">
						<span class="br-badge <?= $o->sandbox ? 'br-badge-amber' : 'br-badge-green'; ?>"><?= $o->sandbox ? __("Sandbox","bluerabbit") : __("Production","bluerabbit"); ?></span>
					</td>
					<td>
						<?php if($o->tremendous_order_id){ ?>
							<a href="<?= esc_url($rewards_host . '/rewards/' . $o->tremendous_order_id); ?>" target="_blank" class="br-action-link">
								<span class="icon icon-link"></span> <?= esc_html($o->tremendous_order_id); ?>
							</a>
						<?php }else{ ?>
							<span class="br-text-12-muted">&mdash;</span>
						<?php } ?>
					</td>
				</tr>
				<?php } }else{ ?>
				<tr><td colspan="7" class="text-center"><?= __("No Tremendous orders yet.","bluerabbit"); ?></td></tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

</div>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

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
	ORDER BY o.created_at DESC LIMIT 2000
");

$status_badges = array(
	'sent'              => 'br-badge-green',
	'pending'           => 'br-badge-amber',
	'failed'            => 'br-badge-red',
	'duplicate_blocked' => 'br-badge-purple',
);

// 'sent' only means Tremendous accepted the order. Delivery happens afterwards and can
// still fail, so the two are shown as separate columns rather than one status that
// silently stops being true.
$delivery_badges = array(
	'delivered' => 'br-badge-green',
	'redeemed'  => 'br-badge-green',
	'failed'    => 'br-badge-red',
	'canceled'  => 'br-badge-red',
	'refunded'  => 'br-badge-purple',
);

$tremendous_config  = BR_Tremendous::instance()->getConfig($adventure->adventure_id);
$webhook_configured = $tremendous_config && !empty($tremendous_config->webhook_secret);

// Events that arrived but could not be attributed or verified. Surfaced because a
// webhook silently landing in a table nobody reads is the same as no webhook at all.
$unapplied = $wpdb->get_results($wpdb->prepare(
	"SELECT e.* FROM {$wpdb->prefix}br_tremendous_events e
	 LEFT JOIN {$wpdb->prefix}br_tremendous_orders o ON o.order_id = e.matched_order_id
	 WHERE e.applied = 0 AND (o.adventure_id = %d OR e.matched_order_id IS NULL)
	 ORDER BY e.event_id DESC LIMIT 10",
	$adventure->adventure_id
));
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
					<th class="text-center"><?= __("Delivery","bluerabbit"); ?></th>
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
						<?php if($o->status !== 'sent'){ $reason = BR_Tremendous::describeFailure($o->api_response); ?>
							<?php if($reason){ ?>
								<div class="br-order-reason"><?= esc_html($reason); ?></div>
							<?php } ?>
						<?php } ?>
					</td>
					<td class="text-center">
						<?php if($o->delivery_status){ ?>
							<span class="br-badge <?= $delivery_badges[$o->delivery_status] ?? 'br-badge-blue'; ?>"><?= esc_html(ucwords($o->delivery_status)); ?></span>
							<?php if($o->last_event_at){ ?>
								<div class="br-text-12-muted"><?= date('M j, g:i A', strtotime($o->last_event_at)); ?></div>
							<?php } ?>
						<?php }elseif($o->status === 'sent'){ ?>
							<span class="br-text-12-muted"><?= $webhook_configured ? __("Awaiting update","bluerabbit") : __("No webhook set up","bluerabbit"); ?></span>
						<?php }else{ ?>
							<span class="br-text-12-muted">&mdash;</span>
						<?php } ?>
					</td>
					<td class="text-center">
						<span class="br-badge <?= $o->sandbox ? 'br-badge-amber' : 'br-badge-green'; ?>"><?= $o->sandbox ? __("Sandbox","bluerabbit") : __("Production","bluerabbit"); ?></span>
					</td>
					<td>
						<?php if($o->tremendous_reward_id || $o->tremendous_order_id){
							// The dashboard addresses rewards, not orders. Older rows only
							// have the order id - link those to the order instead of
							// building a /rewards/ URL that will 404.
							$link = $o->tremendous_reward_id
								? $rewards_host . '/rewards/' . $o->tremendous_reward_id
								: $rewards_host . '/orders/' . $o->tremendous_order_id;
						?>
							<a href="<?= esc_url($link); ?>" target="_blank" class="br-action-link">
								<span class="icon icon-link"></span> <?= esc_html($o->tremendous_reward_id ?: $o->tremendous_order_id); ?>
							</a>
						<?php }else{ ?>
							<span class="br-text-12-muted">&mdash;</span>
						<?php } ?>
					</td>
				</tr>
				<?php } }else{ ?>
				<tr><td colspan="8" class="text-center"><?= __("No Tremendous orders yet.","bluerabbit"); ?></td></tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<?php if(!$webhook_configured){ ?>
	<div class="br-panel">
		<div class="br-panel-note br-panel-note-alert">
			<span class="icon icon-cancel"></span>
			<span><?= __("No webhook signing secret is saved for this adventure, so delivery updates cannot be trusted and are not applied. Add the webhook in your Tremendous dashboard and paste its secret into the adventure's Tremendous settings.","bluerabbit"); ?></span>
		</div>
	</div>
	<?php } ?>

	<?php if($unapplied){ ?>
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-cancel"></span> <?= __("Webhook events not applied","bluerabbit"); ?></h3>
		<span class="br-form-hint"><?= __("Received and stored, but not acted on. Nothing is discarded - if an event type is unrecognised or a signature did not verify, it shows up here.","bluerabbit"); ?></span>
		<table class="br-table">
			<thead>
				<tr>
					<th><?= __("Received","bluerabbit"); ?></th>
					<th><?= __("Event","bluerabbit"); ?></th>
					<th><?= __("Reference","bluerabbit"); ?></th>
					<th><?= __("Why not applied","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($unapplied as $e){ ?>
				<tr>
					<td><?= date('M j, g:i A', strtotime($e->received_at)); ?></td>
					<td><?= esc_html($e->event_type ?: '—'); ?></td>
					<td class="br-text-12-muted"><?= esc_html($e->external_id ?: ($e->tremendous_id ?: '—')); ?></td>
					<td class="br-order-reason"><?= esc_html($e->note ?: '—'); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>

</div>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

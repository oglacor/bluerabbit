<?php
$request_counts = $wpdb->get_row($wpdb->prepare("SELECT
	COUNT(*) as total,
	SUM(CASE WHEN request_status='pending' THEN 1 ELSE 0 END) as pending,
	SUM(CASE WHEN request_status='read' THEN 1 ELSE 0 END) as is_read,
	SUM(CASE WHEN request_status='resolved' THEN 1 ELSE 0 END) as resolved,
	SUM(CASE WHEN request_status='dismissed' THEN 1 ELSE 0 END) as dismissed
	FROM {$wpdb->prefix}br_requests WHERE adventure_id=%d", $adventure->adventure_id));
?>

<div class="br-journey-manager">
<div class="br-panel">
	<div class="br-panel-title">
		<span class="icon icon-mail"></span>
		<?php _e('Player Requests','bluerabbit'); ?>
		<span class="br-badge-blue"><?php echo $request_counts->total . ' ' . __('total','bluerabbit'); ?></span>
		<span class="br-badge-amber"><?php echo $request_counts->pending . ' ' . __('pending','bluerabbit'); ?></span>
	</div>

	<div class="br-toolbar">
		<div class="br-search">
			<span class="icon icon-search"></span>
			<input type="text" id="search-requests" placeholder="<?php _e("Search","bluerabbit"); ?>">
		</div>
		<script>
			$('#search-requests').keyup(function(){
				var valThis = $(this).val().toLowerCase();
				if(valThis == ""){
					$('#requests-list .request-card').show();
				}else{
					$('#requests-list .request-card').each(function(){
						var text = $(this).text().toLowerCase();
						(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
					});
				}
			});
		</script>
	</div>

	<div class="br-actions" style="padding:12px 0;gap:6px">
		<button class="br-btn cyan request-filter-btn active" id="request-filter-all" data-status="all" onClick="loadRequests('all');">
			<?php _e("All","bluerabbit"); ?> (<?= $request_counts->total; ?>)
		</button>
		<button class="br-btn amber request-filter-btn" id="request-filter-pending" data-status="pending" onClick="loadRequests('pending');">
			<?php _e("Pending","bluerabbit"); ?> (<?= $request_counts->pending; ?>)
		</button>
		<button class="br-btn ghost request-filter-btn" id="request-filter-read" data-status="read" onClick="loadRequests('read');">
			<?php _e("Read","bluerabbit"); ?> (<?= $request_counts->is_read; ?>)
		</button>
		<button class="br-btn green request-filter-btn" id="request-filter-resolved" data-status="resolved" onClick="loadRequests('resolved');">
			<?php _e("Resolved","bluerabbit"); ?> (<?= $request_counts->resolved; ?>)
		</button>
		<button class="br-btn ghost request-filter-btn" id="request-filter-dismissed" data-status="dismissed" onClick="loadRequests('dismissed');">
			<?php _e("Dismissed","bluerabbit"); ?> (<?= $request_counts->dismissed; ?>)
		</button>
	</div>
</div>

<input type="hidden" id="request-nonce" value="<?php echo wp_create_nonce('br_request_nonce'); ?>" />

<div class="br-section-body" id="requests-list">
	<?php
	$requests = $wpdb->get_results($wpdb->prepare("SELECT r.*, p.player_display_name, p.player_picture, p.player_email
		FROM {$wpdb->prefix}br_requests r
		LEFT JOIN {$wpdb->prefix}br_players p ON r.player_id = p.player_id
		WHERE r.adventure_id = %d
		ORDER BY r.request_date DESC", $adventure->adventure_id));
	foreach($requests as $req){
		include(get_template_directory().'/request-row.php');
	}
	if(empty($requests)){
		echo '<div class="br-panel"><div class="br-empty"><span class="icon icon-mail"></span><h3>' . __("No requests yet","bluerabbit") . '</h3></div></div>';
	}
	?>
</div>

</div><!-- /.br-journey-manager -->
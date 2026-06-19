<?php
$request_counts = $wpdb->get_row($wpdb->prepare("SELECT
	COUNT(*) as total,
	SUM(CASE WHEN request_status='pending' THEN 1 ELSE 0 END) as pending,
	SUM(CASE WHEN request_status='read' THEN 1 ELSE 0 END) as is_read,
	SUM(CASE WHEN request_status='resolved' THEN 1 ELSE 0 END) as resolved,
	SUM(CASE WHEN request_status='dismissed' THEN 1 ELSE 0 END) as dismissed
	FROM {$wpdb->prefix}br_requests WHERE adventure_id=%d", $adventure->adventure_id));
?>
<div class="highlight padding-10 blue-bg-50">
	<span class="icon-group">
		<span class="button-icon font _24 sq-40 blue-bg-400"><span class="icon icon-mail"></span></span>
		<span class="icon-content">
			<span class="line font _24 grey-800"><?php _e('Player Requests','bluerabbit'); ?></span>
			<span class="line font _14 grey-500"><?php echo $request_counts->total . ' ' . __('total','bluerabbit') . ' / ' . $request_counts->pending . ' ' . __('pending','bluerabbit'); ?></span>
		</span>
	</span>
	<div class="highlight-cell pull-right padding-10">
		<div class="search sticky">
			<div class="input-group">
				<input type="text" class="form-ui" id="search-requests" placeholder="<?php _e("Search","bluerabbit"); ?>">
				<label>
					<span class="icon icon-search"></span>
				</label>
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
		</div>
	</div>
</div>
<div class="padding-10">
	<button class="form-ui blue-bg-400 white-color request-filter-btn active" id="request-filter-all" data-status="all" onClick="loadRequests('all');">
		<?php _e("All","bluerabbit"); ?> (<?= $request_counts->total; ?>)
	</button>
	<button class="form-ui orange-bg-400 white-color request-filter-btn" id="request-filter-pending" data-status="pending" onClick="loadRequests('pending');">
		<?php _e("Pending","bluerabbit"); ?> (<?= $request_counts->pending; ?>)
	</button>
	<button class="form-ui cyan-bg-400 white-color request-filter-btn" id="request-filter-read" data-status="read" onClick="loadRequests('read');">
		<?php _e("Read","bluerabbit"); ?> (<?= $request_counts->is_read; ?>)
	</button>
	<button class="form-ui green-bg-400 white-color request-filter-btn" id="request-filter-resolved" data-status="resolved" onClick="loadRequests('resolved');">
		<?php _e("Resolved","bluerabbit"); ?> (<?= $request_counts->resolved; ?>)
	</button>
	<button class="form-ui grey-bg-400 white-color request-filter-btn" id="request-filter-dismissed" data-status="dismissed" onClick="loadRequests('dismissed');">
		<?php _e("Dismissed","bluerabbit"); ?> (<?= $request_counts->dismissed; ?>)
	</button>
</div>
<input type="hidden" id="request-nonce" value="<?php echo wp_create_nonce('br_request_nonce'); ?>" />
<div id="requests-list" class="padding-10">
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
		echo '<p class="font _16 grey-400 text-center padding-20">' . __("No requests yet","bluerabbit") . '</p>';
	}
	?>
</div>

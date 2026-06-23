<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>
	<?php
	$my_counts = $wpdb->get_row($wpdb->prepare("SELECT
		COUNT(*) as total,
		SUM(CASE WHEN request_status='pending' THEN 1 ELSE 0 END) as pending,
		SUM(CASE WHEN request_status='read' THEN 1 ELSE 0 END) as is_read,
		SUM(CASE WHEN request_status='resolved' THEN 1 ELSE 0 END) as resolved,
		SUM(CASE WHEN request_status='dismissed' THEN 1 ELSE 0 END) as dismissed
		FROM {$wpdb->prefix}br_requests
		WHERE adventure_id=%d AND player_id=%d", $adventure->adventure_id, $current_user->ID));
	?>

<div class="br-journey-manager">

	<!-- Header -->
	<div class="br-panel" style="border-radius:12px 12px 0 0;margin-bottom:0;">
		<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
			<div style="display:flex;align-items:center;gap:14px;">
				<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(33,150,243,0.15);border-radius:10px;font-size:22px;color:#2196f3;">
					<span class="icon icon-mail"></span>
				</div>
				<div>
					<h2 class="br-panel-title" style="margin:0;"><?php _e('My Requests', 'bluerabbit'); ?></h2>
					<span style="font-size:13px;color:rgba(255,255,255,0.45);">
						<?= $my_counts->total . ' ' . __('sent','bluerabbit'); ?>
					</span>
				</div>
			</div>
			<div class="br-actions">
				<button class="br-btn cyan" onClick="showOverlay('#contact-admin-form');">
					<span class="icon icon-add"></span> <?php _e("New Request","bluerabbit"); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Filters -->
	<div class="br-panel" style="border-radius:0;margin-bottom:0;padding:12px 24px;">
		<div class="br-actions" style="gap:6px;">
			<button class="br-btn cyan request-filter-btn active" id="request-filter-all" data-status="all" onClick="loadMyRequests('all');">
				<?php _e("All","bluerabbit"); ?> (<?= $my_counts->total; ?>)
			</button>
			<button class="br-btn amber request-filter-btn" id="request-filter-pending" data-status="pending" onClick="loadMyRequests('pending');">
				<?php _e("Pending","bluerabbit"); ?> (<?= $my_counts->pending; ?>)
			</button>
			<button class="br-btn green request-filter-btn" id="request-filter-resolved" data-status="resolved" onClick="loadMyRequests('resolved');">
				<?php _e("Resolved","bluerabbit"); ?> (<?= $my_counts->resolved; ?>)
			</button>
			<button class="br-btn ghost request-filter-btn" id="request-filter-dismissed" data-status="dismissed" onClick="loadMyRequests('dismissed');">
				<?php _e("Dismissed","bluerabbit"); ?> (<?= $my_counts->dismissed; ?>)
			</button>
		</div>
	</div>

	<!-- Request list -->
	<div class="br-section-body" id="my-requests-list" style="border-radius:0 0 12px 12px;">
		<?php
		$my_requests = $wpdb->get_results($wpdb->prepare("SELECT *
			FROM {$wpdb->prefix}br_requests r
			WHERE r.adventure_id = %d AND r.player_id = %d
			ORDER BY r.request_date DESC", $adventure->adventure_id, $current_user->ID));

		if($my_requests){
			foreach($my_requests as $req){
				include(get_template_directory().'/my-request-row.php');
			}
		}else{
		?>
			<div class="br-empty">
				<span class="icon icon-mail"></span>
				<h3><?= __("No requests yet","bluerabbit"); ?></h3>
				<p><?= __("Use the button above to send a request to the admins","bluerabbit"); ?></p>
			</div>
		<?php } ?>
	</div>

</div><!-- /.br-journey-manager -->

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

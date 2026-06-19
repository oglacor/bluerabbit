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
	<div class="layer base relative boxed w-max-1200">
		<div class="highlight padding-10 blue-bg-50">
			<span class="icon-group">
				<span class="button-icon font _24 sq-40 blue-bg-400"><span class="icon icon-mail"></span></span>
				<span class="icon-content">
					<span class="line font _24 grey-800"><?php _e('My Requests','bluerabbit'); ?></span>
					<span class="line font _14 grey-500"><?php echo $my_counts->total . ' ' . __('sent','bluerabbit'); ?></span>
				</span>
			</span>
			<div class="pull-right padding-10">
				<button class="form-ui blue-bg-400 white-color" onClick="showOverlay('#contact-admin-form');">
					<span class="icon icon-add"></span> <?php _e("New Request","bluerabbit"); ?>
				</button>
			</div>
		</div>

		<div class="padding-10">
			<button class="form-ui blue-bg-400 white-color request-filter-btn active" id="request-filter-all" data-status="all" onClick="loadMyRequests('all');">
				<?php _e("All","bluerabbit"); ?> (<?= $my_counts->total; ?>)
			</button>
			<button class="form-ui orange-bg-400 white-color request-filter-btn" id="request-filter-pending" data-status="pending" onClick="loadMyRequests('pending');">
				<?php _e("Pending","bluerabbit"); ?> (<?= $my_counts->pending; ?>)
			</button>
			<button class="form-ui green-bg-400 white-color request-filter-btn" id="request-filter-resolved" data-status="resolved" onClick="loadMyRequests('resolved');">
				<?php _e("Resolved","bluerabbit"); ?> (<?= $my_counts->resolved; ?>)
			</button>
			<button class="form-ui grey-bg-400 white-color request-filter-btn" id="request-filter-dismissed" data-status="dismissed" onClick="loadMyRequests('dismissed');">
				<?php _e("Dismissed","bluerabbit"); ?> (<?= $my_counts->dismissed; ?>)
			</button>
		</div>

		<div id="my-requests-list" class="padding-10">
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
				echo '<div class="text-center padding-40">';
				echo '<span class="icon icon-mail font _60 grey-300"></span>';
				echo '<h3 class="font _18 grey-400 margin-top-10">' . __("No requests yet","bluerabbit") . '</h3>';
				echo '<p class="font _14 grey-400">' . __("Use the button above to send a request to the admins","bluerabbit") . '</p>';
				echo '</div>';
			}
			?>
		</div>
	</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404";</script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

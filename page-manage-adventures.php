<?php include (get_stylesheet_directory() . '/header.php'); ?>
<input type="hidden" id="duplicate_adventure_nonce" value="<?= wp_create_nonce('br_duplicate_adventure_nonce'); ?>"/>
<?php
	$player_search = ($roles[0] == 'administrator' ? br_require_id('player_id', false) : null) ?: $current_user->ID;
	$page = isset($_GET['cp']) ? $_GET['cp'] : 1;
	$per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 20;

	$status = isset($_GET['status']) ? $_GET['status'] : 'publish';
	$type = isset($_GET['type']) ? $_GET['type'] : NULL;


	$offset = ($page - 1) * $per_page;

	$search_type = $type ? "AND adventures.adventure_type='$type' " : "";
	$search_conditions = " adventures.adventure_status = '$status' $search_type";

	$add_to_search_url = "&status=$status";
	if($type){
		$add_to_search_url.= "&type=$type";
	}


	if($isAdmin){
		$admin_player_id_filter = br_require_id('player_id', false);
		$player_search = $admin_player_id_filter ? "AND adventures.adventure_owner = $admin_player_id_filter " : "";
		if($player_search){
			$add_to_search_url.= "&player_id=".$admin_player_id_filter;
		}
		$adventures_sql = "
			SELECT adventures.*, players.player_first, players.player_last, players.player_email, players.player_nickname FROM {$wpdb->prefix}br_adventures adventures
			LEFT JOIN {$wpdb->prefix}br_players players ON adventures.adventure_owner = players.player_id

			WHERE
			$search_conditions
			$player_search

			GROUP BY adventures.adventure_id
			ORDER BY FIELD(adventures.adventure_type,'template','normal'),adventures.adventure_title, adventures.adventure_id
			LIMIT %d, %d
		";
	}else{
		$adventures_sql = "
		SELECT * FROM {$wpdb->prefix}br_adventures adventures
		LEFT JOIN {$wpdb->prefix}br_player_adventure players ON adventures.adventure_id = players.adventure_id
		WHERE (adventures.adventure_owner = $current_user->ID AND adventures.adventure_status != 'delete') OR (players.player_id =$current_user->ID AND players.player_adventure_role ='gm' AND adventures.adventure_status != 'delete') GROUP BY adventures.adventure_id
		ORDER BY FIELD(adventures.adventure_status, 'publish', 'draft', 'trash') ASC, adventures.adventure_title, adventures.adventure_id
		LIMIT %d, %d
		";
	}
	$prepared_query = $wpdb->prepare($adventures_sql, $offset, $per_page);
	$adventures = $wpdb->get_results($prepared_query);
	$total_advs = $wpdb->get_results($wpdb->prepare($adventures_sql, 0,99999));
	$total_pages = ceil(count($total_advs)/$per_page);

	$status_badges = [
		'publish' => 'br-badge-green',
		'draft'   => 'br-badge-amber',
		'trash'   => 'br-badge-red',
		'delete'  => 'br-badge-red',
	];
	$is_mine_filter = isset($admin_player_id_filter) && $admin_player_id_filter == $current_user->ID;
?>
<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-orange"><span class="icon icon-tools br-icon-lg br-icon-orange"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= __('Edit, delete & restore your adventures','bluerabbit'); ?></div>
			<h1 class="br-page-title"><?= __('Manage Adventures','bluerabbit'); ?></h1>
		</div>
	</div>

	<!-- Filters -->
	<div class="br-tabs">
		<a class="br-tab-btn <?= (!$type && $status=='publish') ? 'active' : ''; ?>" href="<?php echo get_bloginfo('url')."/manage-adventures/";?>"><?= __("All","bluerabbit"); ?></a>
		<a class="br-tab-btn <?= $type=='template' ? 'active' : ''; ?>" href="<?php echo get_bloginfo('url')."/manage-adventures/?type=template";?>"><?= __("Templates","bluerabbit"); ?></a>
		<a class="br-tab-btn <?= $status=='draft' ? 'active' : ''; ?>" href="<?php echo get_bloginfo('url')."/manage-adventures/?status=draft";?>"><?= __("Drafts","bluerabbit"); ?></a>
		<a class="br-tab-btn <?= $status=='trash' ? 'active' : ''; ?>" href="<?php echo get_bloginfo('url')."/manage-adventures/?status=trash";?>"><?= __("Trash","bluerabbit"); ?></a>
		<a class="br-tab-btn <?= $is_mine_filter ? 'active' : ''; ?>" href="<?php echo get_bloginfo('url')."/manage-adventures/?player_id=$current_user->ID";?>"><?= __("Mine","bluerabbit"); ?></a>
	</div>

	<?php if($adventures){ ?>
	<div class="br-panel">
		<table class="br-table">
			<thead>
				<tr>
					<th class="br-th-narrow text-center"><?php _e("ID","bluerabbit"); ?></th>
					<th><?php _e("Adventure","bluerabbit"); ?></th>
					<th><?php _e("Owner","bluerabbit"); ?></th>
					<th><?php _e("Status","bluerabbit"); ?></th>
					<th class="text-center"><?php _e("Actions","bluerabbit"); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($adventures as $key=>$a) { ?>
				<tr>
					<td class="text-center"><?php echo $a->adventure_id; ?></td>
					<td>
						<span class="icon-group">
							<span class="br-icon-btn" style="background-image: url(<?php echo $a->adventure_badge; ?>)"></span>
							<span class="icon-content">
								<span class="line br-text-16">
									<?php if($a->adventure_status=='publish'){ ?>
										<a href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$a->adventure_id"; ?>"><?php echo $a->adventure_title; ?></a>
									<?php }elseif($a->adventure_status=='trash' || $a->adventure_status=='draft'){ ?>
										<a href="<?php echo get_bloginfo('url')."/new-adventure/?adventure_id=$a->adventure_id"; ?>"><?php echo $a->adventure_title; ?></a>
									<?php }else{ ?>
										<?php echo $a->adventure_title; ?>
									<?php } ?>
								</span>
								<br>
								<span class="line br-text-12-muted">
									<?php
									if($a->adventure_status!='delete'){ ?>
									<a href="<?= get_bloginfo('url')."/enroll/?enroll_code=$a->adventure_code"; ?>"><?= get_bloginfo('url')."/enroll/?enroll_code=$a->adventure_code"; ?></a>
									<?php }else{
										echo __("Adventure deleted","bluerabbit")." | ".__("Owner","bluerabbit").": $a->player_nickname // $a->adventure_owner";
									}
									?>
								</span>
							</span>
						</span>
					</td>

					<td>
						<div class="line br-text-16">
							<a href="<?php echo get_bloginfo('url')."/manage-adventures/?player_id=$a->adventure_owner";?>" title="<?= __("Show adventures from this user","bluerabbit");?>">
								<?php
								if($a->player_first != ''){
									echo $a->player_first." ".$a->player_last;
								}else{
									echo $a->player_nickname;
								}
								?>
							</a>
						</div>
						<div class="br-text-12-muted"><?= $a->player_email;?></div>
					</td>
					<td>
						<span class="br-badge <?= $status_badges[$a->adventure_status] ?? 'br-badge-blue'; ?>"><?= $a->adventure_status; ?></span>
						<?php if($a->adventure_type=='template'){ ?>
						<span class="br-badge br-badge-purple"><?= __("Template","bluerabbit"); ?></span>
						<?php } ?>
					</td>
					<td class="text-center">
						<div class="br-actions br-actions-center">
							<?php if($a->adventure_status !='delete' && ($isAdmin || $a->player_adventure_role=='gm')){ ?>
								<a class="br-icon-btn br-icon-btn-sm br-icon-btn-green" href="<?php echo get_bloginfo('url')."/new-adventure/?adventure_id=$a->adventure_id"; ?>"><span class="icon icon-edit"></span></a>
								<button class="br-icon-btn br-icon-btn-sm br-icon-btn-blue white-color" onClick="duplicateAdventure(<?php echo $a->adventure_id; ?>);">
									<span class="icon icon-duplicate"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text br-text-12"><?php _e("Duplicate","bluerabbit"); ?></span>
									</span>
								</button>
							<?php } ?>
							<?php if($a->adventure_status =='publish'){ ?>
							<button class="br-icon-btn br-icon-btn-sm br-icon-btn-red white-color trash-button" onClick="showOverlay('#confirm-trash-<?php echo $a->adventure_id; ?>');">
								<span class="icon icon-trash"></span>
								<span class="tool-tip bottom">
									<span class="tool-tip-text br-text-12"><?php _e("Send to trash","bluerabbit"); ?></span>
								</span>
							</button>
							<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $a->adventure_id; ?>">
								<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','trash');">
									<span class="icon-group">
										<span class="br-icon-btn br-icon-btn-sm br-icon-btn-red-dark">
											<span class="icon icon-trash white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line red-A400 br-text-18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
										</span>
									</span>
								</button>
								<button class="br-close-btn br-icon-btn-sm" onClick="hideAllOverlay();">
									<span class="icon icon-cancel white-color"></span>
								</button>
							</div>
							<?php } ?>
							<?php if($a->adventure_status =='trash'){ ?>
							<button class="br-icon-btn br-icon-btn-sm br-icon-btn-blue white-color trash-button" onClick="showOverlay('#confirm-restore-<?php echo $a->adventure_id; ?>');">
								<span class="icon icon-restore"></span>
								<span class="tool-tip bottom">
									<span class="tool-tip-text br-text-12"><?php _e("Restore","bluerabbit"); ?></span>
								</span>
							</button>
							<div class="confirm-action overlay-layer trash-confirm" id="confirm-restore-<?php echo $a->adventure_id; ?>">
								<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','publish');">
									<span class="icon-group">
										<span class="br-icon-btn br-icon-btn-sm br-icon-btn-blue">
											<span class="icon icon-restore white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line red-A400 br-text-18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
										</span>
									</span>
								</button>
								<button class="br-close-btn br-icon-btn-sm" onClick="hideAllOverlay();">
									<span class="icon icon-cancel white-color"></span>
								</button>
							</div>
							<button class="br-icon-btn br-icon-btn-sm br-icon-btn-red-dark white-color delete-button" onClick="showOverlay('#confirm-delete-<?php echo $a->adventure_id; ?>');">
								<span class="icon icon-delete"></span>
								<span class="tool-tip bottom">
									<span class="tool-tip-text br-text-12"><?php _e("Delete Forever","bluerabbit"); ?></span>
								</span>
							</button>
							<div class="confirm-action overlay-layer trash-confirm" id="confirm-delete-<?php echo $a->adventure_id; ?>">
								<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','delete');">
									<span class="icon-group">
										<span class="br-icon-btn br-icon-btn-sm br-icon-btn-red-dark">
											<span class="icon icon-delete white-color"></span>
										</span>
										<span class="icon-content">
											<span class="line red-A400 br-text-18 w500"><?php _e("You really want to delete the adventure?","bluerabbit"); ?></span>
											<span class="line grey-600 br-text-14 w900"><?php _e("You can't undo this","bluerabbit"); ?></span>
										</span>
									</span>
								</button>
								<button class="br-close-btn br-icon-btn-sm" onClick="hideAllOverlay();">
									<span class="icon icon-cancel white-color"></span>
								</button>
							</div>
							<?php } ?>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<?php if($total_pages > 1){ ?>
	<div class="br-pagination">
		<?php if($page > 1){ ?>
			<a class="br-page-btn" href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=".($page-1).$add_to_search_url;?>">
				<span class="icon icon-arrow-left"></span>
			</a>
			<?php for($i=1; $i<$page; $i++){ ?>
				<a class="br-page-btn" href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=$i".$add_to_search_url;?>">
					<?= $i; ?>
				</a>
			<?php } ?>
		<?php } ?>
			<span class="br-page-btn active"><?= $page; ?></span>
		<?php if($total_pages > $page){ ?>
			<?php for($i=$page+1; $i<=$total_pages; $i++){ ?>
				<a class="br-page-btn" href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=$i".$add_to_search_url;?>">
					<?= $i; ?>
				</a>
			<?php } ?>
			<a class="br-page-btn" href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=".($page+1).$add_to_search_url;?>">
				<span class="icon icon-arrow-right"></span>
			</a>
		<?php } ?>
	</div>
	<?php } ?>

	<?php }else{ ?>
	<div class="br-panel br-empty">
		<span class="icon icon-tools"></span>
		<h3><?= __("No adventures found","bluerabbit"); ?></h3>
		<p><?= __("Try a different filter, or create a new adventure to get started.","bluerabbit"); ?></p>
	</div>
	<?php } ?>

</div>

<input type="hidden" id="reload" value="1">
<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>" />
<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
<input type="hidden" id="draft-nonce" value="<?php echo wp_create_nonce('draft_nonce'); ?>" />

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

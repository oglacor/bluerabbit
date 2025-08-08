<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
	$player_search = ($roles[0] == 'administrator' && $_GET['player_id']) ? $_GET['player_id'] : $current_user->ID;
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
		$player_search = isset($_GET['player_id']) ? "AND adventures.adventure_owner = {$_GET['player_id']} " : "";
		if($player_search){
			$add_to_search_url.= "&player_id=".$_GET['player_id'];
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
	
?>
<div class="container boxed max-w-1200">
	<div class="body-ui">
		<div class="highlight padding-10 grey-bg-800">
			<span class="icon-group">
				<span class="icon-button font _24 sq-40  orange-bg-400"><span class="icon icon-tools"></span></span>
				<span class="icon-content">
					<span class="line font _24 orange-400"><?php _e('Manage Adventures','bluerabbit'); ?></span>
					<span class="line font _14 orange-100"><?php echo __('Edit, delete & restore your adventures','bluerabbit'); ?> </span>
				</span>
			</span>
			<div class="pull-right paddding-5">
				<a class="button form-ui" href="<?php echo get_bloginfo('url')."/manage-adventures/";?>" ><?= __("All","bluerabbit"); ?></a>
				<a class="button form-ui" href="<?php echo get_bloginfo('url')."/manage-adventures/?type=template";?>" ><?= __("Templates","bluerabbit"); ?></a>
				<a class="button form-ui" href="<?php echo get_bloginfo('url')."/manage-adventures/?status=draft";?>" ><?= __("Drafts","bluerabbit"); ?></a>
				<a class="button form-ui" href="<?php echo get_bloginfo('url')."/manage-adventures/?status=trash";?>" ><?= __("Trash","bluerabbit"); ?></a>
				<a class="button form-ui" href="<?php echo get_bloginfo('url')."/manage-adventures/?player_id=$current_user->ID";?>" ><?= __("Mine","bluerabbit"); ?></a>
			</div>
		</div>			
		<div class="content">
			<table class="table white-color">
				<thead>
					<tr>
						<td class="text-center"><?php _e("ID","bluerabbit"); ?></td>
						<td class=""><?php _e("Adventure","bluerabbit"); ?></td>
						<td class=""><?php _e("Owner","bluerabbit"); ?></td>
						<td class=""><?php _e("Status","bluerabbit"); ?></td>
						<td class="text-center"><?php _e("Actions","bluerabbit"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($adventures as $key=>$a) { ?>
						<?php
							if($a->adventure_status=='trash'){
								$row_color='red-bg-100';
							}elseif($a->adventure_status=='draft'){
								$row_color='amber-bg-100';
							}elseif($a->adventure_status=='delete'){
								$row_color='red-bg-800 white-color';
							}else{
								$row_color='';
							}
							if($a->adventure_type=='template'){
								$row_color='yellow-bg-400 grey-900';
							}
						?>
						<tr class="<?=$row_color; ?>" >
							<td class="text-center font _16 light-blue-bg-200 blue-800 w500"><?php echo $a->adventure_id; ?></td>
							<td>
								<span class="icon-group">
									<span class="icon-button font _24 sq-40 " style="background-image: url(<?php echo $a->adventure_badge; ?>)"></span>
									<span class="icon-content">
										<span class="line font _24">
											<?php if($a->adventure_status=='publish'){ ?>
												<a href="<?php echo get_bloginfo('url')."/adventure/?adventure_id=$a->adventure_id"; ?>"><?php echo $a->adventure_title; ?></a>
											<?php }elseif($a->adventure_status=='trash' || $a->adventure_status=='draft'){ ?>
												<a href="<?php echo get_bloginfo('url')."/new-adventure/?adventure_id=$a->adventure_id"; ?>"><?php echo $a->adventure_title; ?></a>
											<?php }else{ ?>
												<?php echo $a->adventure_title; ?>
											<?php } ?>
										</span>
										<br>
										<span class="line font _14">
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
								<h3 class="font _18 w300">
									<a href="<?php echo get_bloginfo('url')."/manage-adventures/?player_id=$a->adventure_owner";?>" title="<?= __("Show adventures from this user","bluerabbit");?>">
										<?php 
										if($a->player_first != ''){
											echo $a->player_first." ".$a->player_last;
										}else{
											echo $a->player_nickname;
										}
										?>
									</a>
								</h3>
								<p class="font _14 w600 padding-5">
								<?= $a->player_email;?>
								</p>
							</td>
							<td class="font _14 uppercase w300">
								<?= $a->adventure_status; ?>
							</td>
							<td class="text-center">
								<?php if($a->adventure_status !='delete' && ($isAdmin || $a->player_adventure_role=='gm')){ ?>
									<a class="icon-button font _16 sq-30  icon-sm green-bg-400" href="<?php echo get_bloginfo('url')."/new-adventure/?adventure_id=$a->adventure_id"; ?>"><span class="icon icon-edit"></span></a>
								<?php } ?>
								<?php if($a->adventure_status =='publish'){ ?>
								<button class="icon-button font _16 sq-30  icon-sm red-bg-200 white-color trash-button" onClick="showOverlay('#confirm-trash-<?php echo $a->adventure_id; ?>');">
									<span class="icon icon-trash"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Send to trash","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-trash-<?php echo $a->adventure_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','trash');">
										<span class="icon-group">
											<span class="icon-button font _16 sq-30  icon-sm red-bg-A400 icon-sm">
												<span class="icon icon-trash white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _16 sq-30  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
								<?php } ?>
								<?php if($a->adventure_status =='trash'){ ?>
								<button class="icon-button font _16 sq-30  icon-sm blue-bg-400 white-color trash-button" onClick="showOverlay('#confirm-restore-<?php echo $a->adventure_id; ?>');">
									<span class="icon icon-restore"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Restore","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-restore-<?php echo $a->adventure_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','publish');">
										<span class="icon-group">
											<span class="icon-button font _16 sq-30  icon-sm blue-bg-A400 icon-sm">
												<span class="icon icon-restore white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w900"><?php _e("Are you sure?","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _16 sq-30  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
								<button class="icon-button font _16 sq-30  icon-sm red-bg-800 white-color delete-button" onClick="showOverlay('#confirm-delete-<?php echo $a->adventure_id; ?>');">
									<span class="icon icon-delete"></span>
									<span class="tool-tip bottom">
										<span class="tool-tip-text font _12"><?php _e("Delete Forever","bluerabbit"); ?></span>
									</span>
								</button>
								<div class="confirm-action overlay-layer trash-confirm" id="confirm-delete-<?php echo $a->adventure_id; ?>">
									<button class="form-ui white-bg trash-confirm-button" onClick="confirmStatus(<?php echo $a->adventure_id; ?>,'adventure','delete');">
										<span class="icon-group">
											<span class="icon-button font _16 sq-30  icon-sm red-bg-A400 icon-sm">
												<span class="icon icon-delete white-color"></span>
											</span>
											<span class="icon-content">
												<span class="line red-A400 font _18 w500"><?php _e("You really want to delete the adventure?","bluerabbit"); ?></span>
												<span class="line grey-600 font _14 w900"><?php _e("You can't undo this","bluerabbit"); ?></span>
											</span>
										</span>
									</button>
									<button class="close-confirm icon-button font _16 sq-30  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
										<span class="icon icon-cancel white-color"></span>
									</button>
								</div>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="pages-nav">
			<ul>
			<?php if($page > 1){ ?>
				<li class="page-button">
					<a href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=".($page-1).$add_to_search_url;?>">
						<span class="icon icon-arrow-left"></span>
					</a>
				</li>
				<?php for($i=1; $i<$page; $i++){ ?>
					<li class="page-button">
						<a href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=$i".$add_to_search_url;?>">
							<?= $i; ?>
						</a>
					</li>
				<?php } ?>
			<?php } ?>
				<li class="page-button current"><?= $page; ?></li>
			<?php if($total_pages > $page){ ?>
				<?php for($i=$page+1; $i<=$total_pages; $i++){ ?>
					<li class="page-button">
						<a href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=$i".$add_to_search_url;?>">
							<?= $i; ?>
						</a>
					</li>
				<?php } ?>
				<li class="page-button">
					<a href="<?php echo get_bloginfo('url')."/manage-adventures/?cp=".($page+1).$add_to_search_url;?>">
						<span class="icon icon-arrow-right"></span>
					</a>
				</li>
			<?php } ?>
			</ul>
		</div>
	</div>	
</div>	



<input type="hidden" id="reload" value="1">
<input type="hidden" id="trash-nonce" value="<?php echo wp_create_nonce('trash_nonce'); ?>" />
<input type="hidden" id="delete-nonce" value="<?php echo wp_create_nonce('delete_nonce'); ?>" />
<input type="hidden" id="publish-nonce" value="<?php echo wp_create_nonce('publish_nonce'); ?>" />
<input type="hidden" id="draft-nonce" value="<?php echo wp_create_nonce('draft_nonce'); ?>" />

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
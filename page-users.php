<?php include (get_stylesheet_directory() . '/header.php'); ?>

<?php if($isAdmin){ ?>
		<div class="container boxed max-w-1200">
			<div class="highlight text-center padding-20">
				<span class="icon-group">
					<span class="icon-button font _24 sq-40  blue-bg-600"><span class="icon icon-players"></span></span>
					<span class="icon-content">
						<span class="line font _36 w700">
							<?php _e("Registered Users","bluerabbit"); ?>
						</span>
					</span>
				</span>
			</div>
			<div class="content padding-10 white-bg">
				<?php $players = get_users(array('orderby'=>'ID')); ?>
				<div class="input-group">
					<label>
						<span class="icon icon-search"></span>
					</label>
					<input type="text" class="form-ui" id="search-players" placeholder="<?php _e("Search users","bluerabbit"); ?>">
					<script>
						$('#search-players').keyup(function(){
							var valThis = $(this).val().toLowerCase();
							if(valThis == ""){
								$('#users tbody > tr').show();           
							}else{
								$('#users tbody > tr').each(function(){
									var text = $(this).text().toLowerCase();
									(text.indexOf(valThis) >= 0) ? $(this).show() : $(this).hide();
								});
							};
						});
					</script>
				</div>
				<table class="table text-center" id="users">
					<thead>
						<tr>
							<td><?php _e("KEY","bluerabbit"); ?></td>
							<td><?php _e("ID","bluerabbit"); ?></td>
							<td><?php _e("First","bluerabbit"); ?></a></td>
							<td><?php _e("Last","bluerabbit"); ?></a></td>
							<td><?php _e("Display Name","bluerabbit"); ?></a></td>
							<td><?php _e("Email","bluerabbit"); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($players as $k=>$p) { ?>
							<tr>
								<td><?php echo $k+1; ?></td>
								<td><?php echo $p->ID; ?></td>
								<td><?php echo $p->first_name; ?></td>
								<td><?php echo $p->last_name; ?></td>
								<td><?php echo $p->display_name; ?></td>
								<td><?php echo $p->user_email; ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404/"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
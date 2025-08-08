<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php $speaker = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_speakers WHERE speaker_id={$_GET['speaker_id']}"); ?>
<?php $sessions = getSpeakerSessions($adventure->adventure_id,$_GET['speaker_id']); ?>
	<div class="container boxed max-w-1200">
		<div class="w-full relative layer base overflow-hidden">
			<div class="background fixed-bg blur7" style="background-image: url(<?= $speaker->speaker_picture; ?>);"></div>
			<div class="background orange-bg-300 opacity-20"></div>
			<div class="padding-20 foreground text-center">
				<span class="icon-button font _24 sq-200" style="background-image: url(<?= $speaker->speaker_picture; ?>);"></span>
				<h1 class="text-center padding-10">
					<span class="background light-blue-bg-700 opacity-50"></span>
					<span class="foreground white-color font _30 w300 uppercase"><?= "$speaker->speaker_first_name $speaker->speaker_last_name"; ?></span>
				</h1>
				<div class="text-center relative">
					<div class="padding-5 margin-5">
						<span class="background grey-bg-700 opacity-50"></span>
						<span class="foreground white-color font _18 w600"><?= "$speaker->speaker_company"; ?></span>
					</div>
				</div>
				<div class="text-center padding-5">
					<?php if($speaker->speaker_linkedin){ ?>
						<a href="<?= $speaker->speaker_linkedin; ?>" target="_blank" class="form-ui font main _18 w300 light-blue-bg-700 white-color"><?php _e("LinkedIn",'bluerabbit'); ?></a>
					<?php } ?>
					<?php if($speaker->speaker_website){ ?>
						<a href="<?= $speaker->speaker_website; ?>" target="_blank" class="form-ui font main _18 w300 purple-bg-400 white-color"><?php _e("Website",'bluerabbit'); ?></a>
					<?php } ?>
					<?php if($speaker->speaker_twitter){ ?>
						<a href="<?= "https://twitter.com/".$speaker->speaker_twitter; ?>" target="_blank" class="form-ui font main _18 w300 blue-bg-400 white-color"><?= "@".$speaker->speaker_twitter; ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="w-full blue-bg-400 layer base relative speaker-sessions">
			<div class="content white-color">
				<div class="background blue-bg-800 opacity-70"></div>
				<div class="foreground font _18 w300 padding-10">
					<h3 class="font _18 w900 amber-400 uppercase text-center"><?= __("About","bluerabbit"); ?></h3>
					<?= apply_filters('the_content',$speaker->speaker_bio); ?>
				</div>
			</div>

			<br class="clear">
			<?php if($isGM) { ?>
				<div class="highlight padding-10 text-center layer base relative">
					<a class="form-ui green-bg-400" href="<?= get_bloginfo('url')."/new-speaker/?adventure_id=$adventure->adventure_id&speaker_id=$speaker->speaker_id"; ?>">
						<span class="icon icon-edit"></span>
						<?php _e("Edit",'bluerabbit'); ?>
					</a>
				</div>
			<?php } ?>
		</div>
		<div class="w-full blue-bg-400 layer base relative speaker-sessions">
			<?php if($sessions){ ?>
				<h2 class="font _24 w700 uppercase kerning-2 text-center padding-10 white-color blue-bg-800"><?php _e("Sessions",'bluerabbit'); ?></h2>
				<?php foreach($sessions as $t){ ?>
					<div class="w-full text-center white-color layer base relative padding-10">
						<h3 class="font _30 w600 padding-10"><?= $t->session_title; ?></h3>
						<h5 class="font _14 w300"><?= date("d - M, Y",strtotime($t->session_start))." | ".date("H:i",strtotime($t->session_start))." - ".date("H:i",strtotime($t->session_end)); ?></h5>
						<?php if($t->quest_id){ ?>
							<a class="form-ui" href="<?= get_bloginfo('url')."/$t->quest_type/?adventure_id=$adventure->adventure_id&questID=$t->quest_id"; ?>">
								<?= __("View")." $t->quest_type"; ?>
							</a>
						<?php } ?>
						<div class="font _16 w300">
							<?= apply_filters('the_content',$t->session_description); ?>
						</div>
					</div>
				<?php }?>
			<?php }else{ ?>
				<div class="highlight padding-10 black-bg text-center"><?php _e("No sessions yet",'bluerabbit'); ?></div>
			<?php } ?>
		</div>
	</div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container open-field">
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center">
				<div class="step-content">	
					<?= apply_filters('the_content',$step->step_content);  ?>
				</div>
				<div class="step-content-text-editor editor" id="step-content-text-<?=$step->step_id;?>">
					<?php 
						$wp_editor_settings = array(
							'quicktags'=> false, 'textarea_rows'=>5, 'media_buttons'=>false,
							'tinymce' => array(
								'toolbar1'=> 'bold,italic,separator,alignleft,aligncenter,alignright,separator,link,bullist,wp_add_media',
							),
						);
						wp_editor($q->pp_content, 'the_pp_content', $wp_editor_settings); 	
					?>
					<div class="hidden" id="pp-content-counter"></div>
				</div>
			</div>
			<div class="edge-right"></div>
			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<?php include (TEMPLATEPATH . "/step-nav-button-back.php"); ?>
		<?php include (TEMPLATEPATH . "/step-nav-button-next.php"); ?>
	</div>
</div>

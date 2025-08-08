	<div class="padding-10 blue-bg-700 white-color w-full sticky top layer overlay">
		<h2 class="font _24 w900">
			<span class="icon icon-objectives"></span>
			<?= __("Select the item the player will earn","bluerabbit"); ?>
		</h2>
	</div>
	<div class="padding-10 amber-bg-700 white-color w-full sticky top layer overlay">
		<h3 class="font _18 w300 opacity-70 grey-900">
			<?= __("If you don't see an item here, it may be because you added it to another step in this quest","bluerabbit"); ?>
		</h3>
	</div>

	<div class="padding-10 white-color w-full relative">
		<?php
		if($s->step_item){
			$step_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_items
			WHERE adventure_id=$s->adventure_id AND item_status='publish' AND (item_type='key' OR item_type='tabi-piece') AND item_id NOT IN (SELECT step_item FROM {$wpdb->prefix}br_steps WHERE step_item > 0 AND quest_id=$s->quest_id AND step_item != $s->step_item)");
		}else{
			$step_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}br_items
			WHERE adventure_id=$s->adventure_id AND item_status='publish' AND (item_type='key' OR item_type='tabi-piece') AND item_id NOT IN (SELECT step_item FROM {$wpdb->prefix}br_steps WHERE step_item > 0 AND quest_id=$s->quest_id)");
		}
		?>
		<?php if($step_items){ ?>
			<ul class="selectable-list grid step-items" id="step-select-items-<?= $s->step_id;?>">
				<?php foreach ($step_items as $key=>$i){ ?>
					<?php $status = $i->item_id==$s->step_item ? 'active' : ''; ?>
					<li id="step-item-<?= $i->item_id; ?>" class="<?= $status; ?> purple-border-400 border border-all border-2 white-bg step-item" onClick="toggleSingleReq('#step-item-<?= $i->item_id; ?>');" style="background-image: url(<?= $i->item_badge; ?>);">
						<input type="hidden" class="item-id" value="<?= $i->item_id; ?>">
						<div class="layer background absolute sq-full top left color-overlay"></div>
						<span class="icon-button green-bg-400 active-content font _18 absolute top-10 right-10">
							<span class="icon icon-check"></span>
						</span>
						<div class="layer base absolute perfect-center text-center achievement-name">
							<span class="font _18"><?= $i->item_name; ?></span>
						</div>
					</li>
				<?php }	?>
			</ul>
		<?php }else{ ?>
			<div class="text-center padding-10">
				<div class="font _20 blue-grey-900 w100 block text-center padding-5">
					<?php _e("No key items available",'bluerabbit'); ?>
				</div>
				<div class="block text-center padding-5">
					<a href="<?= get_bloginfo('url')."/new-item/?adventure_id=$s->adventure_id&type=key"; ?>" target="_blank" class="form-ui item-shop">
						<?php _e("Add new key item",'bluerabbit'); ?>
					</a>
				</div>
			</div>
		<?php } ?>
	</div>










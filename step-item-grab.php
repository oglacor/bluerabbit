<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container grab-item">
		<div class="step-warning-sign">
			<svg id="warning-sign-<?= $step->step_order; ?>" class="warning-sign" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 163.38 141.49">
				<polygon class="yellow outline" points="81.69 4 3.46 139.49 159.92 139.49 81.69 4"/>
				<path class="yellow fill" d="M87.48,112.29c0,1.58-.57,2.92-1.71,4.03-1.14,1.11-2.5,1.67-4.08,1.67s-2.94-.56-4.07-1.67c-1.14-1.11-1.71-2.46-1.71-4.03s.57-2.94,1.71-4.08,2.5-1.71,4.07-1.71,2.94.57,4.08,1.71,1.71,2.5,1.71,4.08ZM87.1,61.35c0,1.41-.46,6.14-1.39,14.19-.93,8.05-1.75,15.84-2.45,23.36h-3.29c-.62-7.52-1.39-15.3-2.3-23.36-.92-8.05-1.37-12.78-1.37-14.19,0-1.6.51-2.96,1.52-4.05,1.01-1.1,2.31-1.65,3.88-1.65s2.87.54,3.89,1.63c1.01,1.08,1.52,2.44,1.52,4.07Z"/>
			</svg>
		</div>
		<?= stepTag(__("You found an item on your path!","bluerabbit")); ?>
		<div class="step-item-to-grab"> 
			<?php $item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}br_items WHERE item_id=$step->step_item");?>
			<?php $pickupNonce = wp_create_nonce('pickup_item'.$current_user->ID.date('Ymd')); ?>
			<div class="item">
				<div class="item-content-container">
					<div class="item-bg">
						<svg id="item-container-<?= $item->item_id;?>" class="item-container-bg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180">
							<polygon  class="blue outline"  points="165 175 15 175 5 165 5 15 15 5 165 5 175 15 175 165 165 175"/>
							<line class="blue line"  x1="27" y1="163" x2="17" y2="153"/>
							<line  class="blue line"  x1="30" y1="160" x2="20" y2="150"/>
							<polyline class="blue line"  points="114.59 20 149.94 20 154 24.06"/>
							<polygon class="blue item-content" points="162 170 18 170 10 162 10 18 18 10 162 10 170 18 170 162 162 170"/>
							<polygon class="blue bg" points="150 160 30 160 20 150 20 30 30 20 150 20 160 30 160 150 150 160"/>
							<polyline class="yellow line" points="165 156 155 166 24 166 14 156 14 36.78"/>
							<polyline class="yellow line" points="43.12 16 152 16 162 26"/>
						</svg>
					</div>
					<div class="item-content">
						<div class="item-image-container">
							<img class="item-image" src="<?= $item->item_badge; ?>" alt="<?= $item->item_name; ?>">
						</div>
						<div class="item-level">
							<svg class="item-level-bg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26.12 26.12">
								<polygon points="16.41 .56 3.91 3.91 .56 16.41 9.71 25.56 22.21 22.21 25.56 9.71 16.41 .56"/>
							</svg>
							<span class="item-level-number"><?= $item->item_level; ?></span>
						</div>
						<h2 class="item-name"><?= $item->item_name; ?> </h2>
					</div>
				</div>
			</div>

		</div>
		<div class="action-buttons">
			<?php if($i >= count($steps)-1){ ?>
				<button class="action-button warning" onClick="pickupItem(<?=$item->item_id;?>,'<?= $pickupNonce; ?>');submitPlayerWork();">
					<?= __("Grab the item and finish","bluerabbit"); ?>
				</button>
			<?php }else{ ?>
				<a class="action-button warning" href="#step-<?= $steps[($i+1)]->step_order; ?>" id="button-system-<?=$step->step_id;?>" onClick="pickupItem(<?=$item->item_id;?>,'<?= $pickupNonce; ?>');">
					<?= __("Grab the item and continue","bluerabbit"); ?>
				</a>
			<?php } ?>

		</div>
	</div>
</div>

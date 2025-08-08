<div class="step <?= $i==0 ? 'active' : ''; ?>" id="step-<?= $step->step_order; ?>">
	<?php include (TEMPLATEPATH . "/steps-background.php"); ?>
	<div class="step-content-container item-required">
		<?= stepTag(__("You need an item to continue","bluerabbit")); ?>
		<div class="dialogue-box" id="step-content-text-<?=$step->step_order;?>">
			<div class="corner-tl"></div>
			<div class="edge-top"></div>
			<div class="corner-tr"></div>

			<div class="edge-left"></div>
			<div class="center"><?= apply_filters('the_content',$step->step_content);  ?></div>
			<div class="edge-right"></div>

			<div class="corner-bl"></div>
			<div class="edge-bottom"></div>
			<div class="corner-br"></div>
		</div>
		<nav class="tab-nav">
			<ul>
				<li class="active">
					<span class="nav-item-label"><?= __("Your Backpack","bluerabbit"); ?></span>
				</li>
			</ul>
		</nav>
		<div class="step-backpack" id="step-backpack-<?= $step->step_id; ?>">
			<?php if($my_items){ ?>
				<?php $current_type = ''; ?>
				<?php foreach($my_items['key'] as $key=>$item){ ?>
					<div class="item" id="backpack-item-<?= $item->item_id; ?>-<?= $step->step_order;?>" onClick="toggleSingleReq('#backpack-item-<?= $item->item_id; ?>-<?= $step->step_order;?>');">
						<div class="item-content-container">
							<input type="hidden" class="item-id" value="<?= $item->item_id; ?>">
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
				<?php } ?>
			<?php }else{ ?>
				<h1 class="text-center"><?= __("No items currently available",'bluerabbit');?></h1>
				<h3 class="text-center"><?= __("More items are available as you earn achievements. Keep moving forward!",'bluerabbit');?></h3>
			<?php }?>
		</div>
		<div class="action-buttons">
			<?php $checkItemNonce = wp_create_nonce('check_item'.$current_user->ID.date('Ymd').$step->step_id); ?>
			<input type="hidden" id="nonce-item-req-<?= $step->step_id;?>" value="<?= $checkItemNonce; ?>">
			<button class="action-button confirm" id="button-system-<?=$step->step_id;?>" onClick="checkItem(<?= $step->step_id;?>);">
				<?= __("Use this item","bluerabbit"); ?>
			</button>
		</div>
	</div>
</div>

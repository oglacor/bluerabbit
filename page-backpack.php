<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php if($adventure){ ?>

<div class="br-page">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar"><span class="icon icon-backpack"></span></div>
		<div class="br-flex-1">
			<div class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></div>
			<h1 class="br-page-title"><?= __("Backpack","bluerabbit"); ?></h1>
		</div>
	</div>

	<!-- Nav -->
	<div class="br-tabs" id="item-shop-nav">
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-basket"></span> <?= __("Shop","bluerabbit"); ?></a>
		<span class="br-tab-btn active"><span class="icon icon-backpack"></span> <?= __("Backpack","bluerabbit"); ?></span>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/tabis/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-sabotage"></span> <?= __("Tabis","bluerabbit"); ?></a>
		<a class="br-tab-btn" href="<?= get_bloginfo('url')."/transactions/?adventure_id=$adventure->adventure_id"; ?>"><span class="icon icon-transactions"></span> <?= __("Transactions","bluerabbit"); ?></a>
	</div>

	<?php if($my_items){ ?>
		<?php $current_type = ''; ?>
		<div class="br-item-grid">
			<?php foreach($my_items['all'] as $i){ ?>

				<?php if($current_type != "$i->item_type-$i->tabi_id"){ ?>
					<?php $current_type = "$i->item_type-$i->tabi_id"; ?>
					<div class="br-item-group-header">
						<h3>
							<?php if($i->item_type == 'consumable'){ ?>
								<?= __("Consumables","bluerabbit"); ?>
							<?php }elseif($i->item_type == 'key'){ ?>
								<?= __("Key Items","bluerabbit"); ?>
							<?php }elseif($i->item_type == 'tabi-piece'){ ?>
								<?= esc_html($i->tabi_name); ?>
							<?php }elseif($i->item_type == 'reward'){ ?>
								<?= __("Rewards","bluerabbit"); ?>
							<?php }else{ ?>
								<?= __("Items","bluerabbit"); ?>
							<?php } ?>
						</h3>
					</div>
				<?php } ?>

				<div class="br-item-card">
					<div class="br-item-card-image" style="background-image:url(<?= esc_url($i->item_badge); ?>)"></div>
					<div class="br-item-card-body">
						<h3 class="br-item-card-name"><?= esc_html($i->item_name); ?></h3>
						<?php if($i->item_type=='tabi-piece' && $i->tabi_name){ ?>
							<span class="br-badge br-badge-purple"><?= sprintf(__("Part of %s","bluerabbit"), esc_html($i->tabi_name)); ?></span>
						<?php }elseif($i->item_type=='key'){ ?>
							<span class="br-badge br-badge-blue"><?= __("Key Item","bluerabbit"); ?></span>
						<?php } ?>
						<div class="br-item-card-description"><?= apply_filters('the_content', $i->item_description); ?></div>
						<div class="br-item-card-meta">
							<span title="<?= __("Level","bluerabbit"); ?>"><span class="icon icon-level"></span> <?= (int) $i->item_level; ?></span>
							<?php if($i->item_type=='consumable' || $i->item_type=='gift-card'){ ?>
								<span title="<?= __("Owned","bluerabbit"); ?>"><span class="icon icon-basket"></span> <?= (int) $i->total_consumables; ?></span>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php }else{ ?>
		<div class="br-panel br-empty">
			<span class="icon icon-backpack"></span>
			<h3><?= __("No items currently available","bluerabbit"); ?></h3>
			<p><?= __("More items are available as you earn achievements. Keep moving forward!","bluerabbit"); ?></p>
		</div>
	<?php } ?>

</div>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>"; </script>
<?php } ?>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>

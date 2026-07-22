<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php
if($adventure && ($isGM || $isAdmin)){
	$item_id = br_require_id('item_id', false);
	if($item_id){
		$i = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."br_items WHERE item_id=$item_id");
		$trnx = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."br_transactions WHERE object_id=$item_id AND adventure_id=$adventure->adventure_id AND trnx_type='consumable' AND trnx_status='publish'");
		$items_sold = count($trnx);
	}
	$achievements = BR_Achievement::instance()->getAchievements($adventure->adventure_id, 'path|rank');
	$tabis = BR_Tabi::instance()->getTabis($adventure->adventure_id);
	$item_categories = BR_Item::instance()->getCategories($adventure->adventure_id);
	$is_edit = isset($i) && $i;
	$current_type = $is_edit ? $i->item_type : 'consumable';
	$br_tremendous_allowed = !($my_features && isset($my_features['allow_use_tremendous'][$f_role]) && !$my_features['allow_use_tremendous'][$f_role]);
	$item_tremendous_products = ($is_edit && $i->item_tremendous_products) ? json_decode($i->item_tremendous_products, true) : array();
	if (!is_array($item_tremendous_products)) $item_tremendous_products = array();
?>

<div class="br-page br-has-bottom-bar">

	<!-- Header -->
	<div class="br-panel br-page-header">
		<div class="br-page-header-avatar br-avatar-red">
			<span class="icon icon-basket br-icon-red br-icon-lg"></span>
		</div>
		<div>
			<h1 class="br-page-title"><?= $is_edit ? __("Edit Item", "bluerabbit") . ' — ' . esc_html($i->item_name) : __("New Item", "bluerabbit"); ?></h1>
			<span class="br-page-subtitle"><?= esc_html($adventure->adventure_title); ?></span>
		</div>
		<?php if($is_edit){ ?>
			<input type="hidden" id="the_item_id" value="<?= $i->item_id; ?>">
		<?php } ?>
	</div>

	<!-- Sticky Tabs -->
	<div class="br-tabs br-tabs-sticky" id="main-tabs-buttons">
		<button class="br-tab-btn active" onClick="brScrollTo('general', this);">
			<span class="icon icon-settings"></span> <?= __("Settings", "bluerabbit"); ?>
		</button>
		<button class="br-tab-btn" onClick="brScrollTo('item-tabi-settings-panel', this);">
			<span class="icon icon-sabotage"></span> <?= __("Tabi Placement", "bluerabbit"); ?>
		</button>
	</div>

	<!-- ═══ SETTINGS ═══ -->
	<div class="br-scroll-section" id="general"><div class="br-panel" id="item-options-panel">
		<h3 class="br-panel-title"><span class="icon icon-basket"></span> <?= __("Item Settings", "bluerabbit"); ?></h3>

		<input type="hidden" id="the_item_type" value="<?= $current_type; ?>">

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Name", "bluerabbit"); ?></label>
			<input class="br-input br-input-lg" type="text" id="the_item_name"
				   value="<?= $is_edit ? esc_attr($i->item_name) : ''; ?>"
				   placeholder="<?= __('Item name', 'bluerabbit'); ?>">
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Type", "bluerabbit"); ?></label>
			<div class="dashboard-type-selector">
				<button type="button" class="item-type-choice type-choice" id="button-consumable" onClick="setItemType('consumable');">
					<svg id="icon-consumable" class="icon-image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300">
						<polyline class="blue line-E" data-name="blue line-E" points="291.44 218.24 291.44 250.91 251.61 290.74 230.03 290.74"/>
						<line class="blue line-D" data-name="blue line-D" x1="276.05" y1="242.09" x2="243.81" y2="274.32"/>
						<line class="blue line-C" data-name="blue line-C" x1="275.26" y1="156.44" x2="275.26" y2="96.46"/>
						<polyline class="blue line-B" data-name="blue line-B" points="113.6 277.93 51.66 277.93 25.42 251.7"/>
						<polyline class="blue line-A" data-name="blue line-A" points="45.75 45.81 116.66 45.81 148.03 14.43 215.8 14.43 239.26 37.9 258.63 37.9"/>
						<path class="blue outline circle-2" data-name="blue circle-2" d="M26.2,45.94c0,5.4,4.37,9.77,9.77,9.77s9.78-4.38,9.78-9.77-4.38-9.78-9.78-9.78-9.77,4.38-9.77,9.78Z"/>
						<path class="blue outline circle-1" data-name="blue circle-1" d="M259.14,37.03c0,5.4,4.37,9.77,9.77,9.77s9.78-4.38,9.78-9.77-4.38-9.78-9.78-9.78-9.77,4.38-9.77,9.78Z"/>
						<polyline class="yellow line-D" data-name="yellow line-D" points="207.67 23.31 151.11 23.31 120.71 53.71 65.16 53.71"/>
						<line class="yellow line-C" data-name="yellow line-C" x1="25.42" y1="163.58" x2="25.42" y2="112.25"/>
						<line class="yellow line-B" data-name="yellow line-B" x1="275.26" y1="227.3" x2="275.26" y2="188.6"/>
						<polyline class="yellow line-A" data-name="yellow line-A" points="152.19 290.74 48.39 290.74 8.56 250.91 8.56 230.87"/>
						<path class="blue icon-body-A" d="M229.55,117.68h-22.27l-38.98-38.98-15.25,15.25,23.72,23.72h-55.81l23.72-23.72-15.25-15.25-38.98,38.98h-18.02c-9.14,0-16.58,7.44-16.58,16.58v16.31h9.55l18.03,70.74h134.36l18.02-70.74h10.33v-16.31c0-9.14-7.44-16.58-16.58-16.58ZM159.39,93.95l8.91-8.91,32.64,32.64h-17.82l-23.72-23.72ZM129.43,85.04l8.91,8.91-23.72,23.72h-17.82l32.63-32.64ZM214.29,216.82h-127.38l-16.88-66.25h161.14l-16.88,66.25ZM241.65,146.08H60.33v-11.82c0-6.67,5.43-12.1,12.1-12.1h157.12c6.67,0,12.1,5.43,12.1,12.1v11.82Z"/>
						<polygon class="blue icon-body-B" points="200.9 159.52 196.49 158.72 192.5 180.56 174.56 180.56 176.51 159.32 172.04 158.92 170.06 180.56 152.09 180.56 152.09 159.12 147.6 159.12 147.6 180.56 129.63 180.56 127.66 158.92 123.19 159.32 125.13 180.56 107.19 180.56 103.2 158.72 98.79 159.52 102.64 180.56 97.5 180.56 97.5 185.04 103.46 185.04 106.91 203.92 111.32 203.12 108.02 185.04 125.54 185.04 127.25 203.72 131.72 203.32 130.04 185.04 147.6 185.04 147.6 203.52 152.09 203.52 152.09 185.04 169.65 185.04 167.98 203.32 172.45 203.72 174.15 185.04 191.68 185.04 188.37 203.12 192.78 203.92 196.24 185.04 202.19 185.04 202.19 180.56 197.06 180.56 200.9 159.52"/>
						<polyline class="corner yellow top-right" data-name="corner yellow top-right" points="233.33 2 298 2 298 117.5"/>
						<polyline class="corner blue top-left" data-name="corner blue top-left" points="94.61 2 2 2 2 57.64"/>
					</svg>
					<span class="button-legend"><?= __("Consumable"); ?></span>
				</button>
				<button type="button" class="item-type-choice type-choice" id="button-key" onClick="setItemType('key');">
					<svg id="icon-key" class="icon-image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300">
						<polyline class="blue line-C" data-name="blue line-C" points="248.49 254.29 181.3 254.29 164.25 271.34 36.12 271.34"/>
						<polyline class="blue line-B" data-name="blue line-B" points="108.66 15.16 247.16 15.16 285.23 53.24 285.23 72.4"/>
						<polyline class="blue line-A" data-name="blue line-A" points="14.77 148.67 14.77 53.24 52.85 15.16 73.48 15.16"/>
						<path class="blue outline circle-A" data-name="blue circle-A" d="M268.37,254.16c0-5.16-4.18-9.35-9.35-9.35s-9.35,4.18-9.35,9.35,4.19,9.35,9.35,9.35,9.35-4.19,9.35-9.35Z"/>
						<line class="yellow line-E" data-name="yellow line-E" x1="30.23" y1="52.72" x2="61.06" y2="21.9"/>
						<polyline class="yellow line-D" data-name="yellow line-D" points="36.12 284.84 167.53 284.84 211.84 240.53 240.41 240.53"/>
						<line class="yellow line-C" data-name="yellow line-C" x1="274.2" y1="94.84" x2="274.2" y2="226.06"/>
						<line class="yellow line-B" data-name="yellow line-B" x1="30.23" y1="94.84" x2="30.23" y2="200.91"/>
						<polyline class="yellow line-A" data-name="yellow line-A" points="108.66 27.4 244.03 27.4 257.91 41.28 257.91 122.92"/>
						<circle class="blue outline icon-body-C" cx="190.65" cy="110.32" r="10.46"/>
						<path class="blue outline icon-body-B" d="M163.06,74.03h33.33c10.53,0,19.07,8.55,19.07,19.07v49.21c0,10.53-8.55,19.07-19.07,19.07h-33.33c-3.6,0-6.53-2.92-6.53-6.53v-74.3c0-3.6,2.92-6.53,6.53-6.53Z" transform="translate(-28.76 165.99) rotate(-45)"/>
						<polygon class="blue outline icon-body-A" points="151.27 135.97 130.02 157.23 118.33 157.23 108.14 167.42 108.14 174.96 97.95 185.15 87.46 185.15 71.19 201.42 71.19 225.89 95.9 225.89 168.55 153.25 151.27 135.97"/>
						<polyline class="corner yellow top-right" data-name="corner yellow top-right" points="233.33 2 298 2 298 117.5"/>
						<polyline class="corner blue bottom-left" data-name="corner blue bottom-left" points="2 205.39 2 298 57.64 298"/>
					</svg>
					<span class="button-legend"><?= __("Key"); ?></span>
				</button>
				<button type="button" class="item-type-choice type-choice" id="button-reward" onClick="setItemType('reward');">
					<svg id="icon-reward" class="icon-image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300">
						<circle class="yellow circle-A" data-name="yellow circle-A" cx="27.32" cy="137.92" r="8.26"/>
						<polyline class="yellow line-C" data-name="yellow line-C" points="27.32 129.65 27.32 37.71 49.87 15.16 165.69 15.16"/>
						<line class="yellow line-B" data-name="yellow line-B" x1="198.41" y1="270.16" x2="258.31" y2="270.16"/>
						<line class="yellow line-A" data-name="yellow line-A" x1="270.29" y1="26.19" x2="270.29" y2="171.43"/>
						<polygon class="yellow triangle-A" data-name="yellow triangle-A" points="13.73 15.16 24.76 15.16 13.73 26.19 13.73 15.16"/>
						<circle class="blue icon-body-D" cx="150.1" cy="181.56" r="57.37"/>
						<polygon class="blue icon-body-C" points="172.18 215.54 150.1 199.5 128.02 215.54 136.45 189.58 114.37 173.54 141.66 173.54 150.1 147.58 158.53 173.54 185.83 173.54 163.75 189.58 172.18 215.54"/>
						<path class="blue outline icon-body-B" d="M196.62,94.95l-6.94-25.9-23.99,41.56c12.81,2.8,24.35,8.97,33.65,17.55l23.18-40.15-25.9,6.94Z"/>
						<path class="blue outline icon-body-A" d="M99.24,49.51l-7.81,29.15-22.66-6.07,32.09,55.58c9.3-8.58,20.84-14.75,33.65-17.55l-35.28-61.1Z"/>
						<polyline class="blue line-C" data-name="blue line-C" points="14.41 101.14 14.41 35.86 35.11 15.16"/>
						<polyline class="blue line-B" data-name="blue line-B" points="286.27 178.41 270.29 194.39 270.29 284.84 193.68 284.84 178.99 270.16 27.73 270.16"/>
						<line class="blue line-A" data-name="blue line-A" x1="258.31" y1="36.51" x2="258.31" y2="153.17"/>
						<polyline class="corner blue bottom-left" data-name="corner blue bottom-left" points="2 270.16 2 298 124.27 298"/>
						<polyline class="corner yellow top-right" data-name="corner yellow top-right" points="270.29 2 298 2 298 168.98"/>
					</svg>
					<span class="button-legend"><?= __("Reward"); ?></span>
				</button>
				<button type="button" class="item-type-choice type-choice" id="button-tabi-piece" onClick="setItemType('tabi-piece');">
					<svg id="icon-tabi" class="icon-image" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300">
						<polygon class="blue triangle-A" data-name="blue triangle-A" points="251.58 39.45 240.92 57.91 262.24 57.91 251.58 39.45"/>
						<polygon class="yellow triangle-A" data-name="yellow triangle-A" points="231.26 25.79 241.92 44.26 252.58 25.79 231.26 25.79"/>
						<path class="blue icon-body" data-name="blue icon-body" d="M198.34,115.48v-7.26c0-25.74-20.56-47.1-46.53-47.27-4.04,0-7.41,2.24-9.12,5.48-3.02-2.37-7.06-2.86-10.55-1.26-16.63,7.61-27.38,24.32-27.38,42.57v6.41c0,10.34,4.03,20.05,11.33,27.36l4.35,4.35v-2.86c2.66,5.04,6.34,9.51,9.88,12.53v10.88h-6.81v2.55c0,15.46,12.57,28.03,28.03,28.03s28.03-12.57,28.03-28.03v-2.55h-6.79v-10.88c3.5-2.99,7.18-7.43,9.86-12.5l.02,18.34h2.54c7.24,0,13.13-5.89,13.13-13.13v-27.66h-5.09v27.66c0,3.54-2.31,6.56-5.5,7.63-.05-56.67-.01-17.32-.01-39.64-.04,0-.02.15-.02-1.96l-8.26-2.2c-11.37-3.03-20.69-10.9-25.59-21.59l-7.25-15.84v-3.44c0-2.85,2.3-5.15,5.14-5.15,22.6,0,41.49,18.99,41.49,42.18v7.26h5.09ZM115.36,132.58c-3.58-5.43-5.49-11.78-5.49-18.43v-6.41c0-16.26,9.58-31.15,24.4-37.94,3.11-1.42,6.51.55,7.15,3.6-7.14,2.69-13.55,8.25-18.21,15.82-5.06,8.23-7.85,18.57-7.85,29.1v14.26ZM174.34,171.49c-1.27,11.45-11.01,20.39-22.8,20.39s-21.52-8.94-22.79-20.39h6.67v-12.4c9.95,5.65,22.33,5.65,32.28,0v12.4h6.65ZM149.24,92.59c5.53,12.08,16.07,20.98,28.91,24.4l4.47,1.19v5.22c-.26,10.09-4.31,19.54-11.5,26.73-10.82,10.82-28.32,10.82-39.14,0-7.44-7.44-11.53-17.33-11.53-27.85v-3.96c0-18.39,9.25-34.75,22.27-39.97l6.52,14.24ZM149.24,92.59"/>
						<path class="blue icon-head" data-name="blue icon-head" d="M77.57,197.59v37.16h147.97v-37.16c0-17.2-14-31.2-31.2-31.2h-9.5v5.09h9.5c14.4,0,26.11,11.71,26.11,26.11v32.06h-20.4v-29.69h-5.09v29.69h-24.59l22.8-49.46-4.62-2.13-23.79,51.59h-26.4l-23.79-51.59-4.63,2.13,22.81,49.46h-24.58v-29.69h-5.09v29.69h-20.4v-32.06c0-14.39,11.71-26.11,26.11-26.11h9.49v-5.09h-9.49c-17.2,0-31.2,14-31.2,31.2h0ZM77.57,197.59"/>
						<polyline class="blue line-D" data-name="blue line-D" points="17.75 64.97 17.75 39.3 36.9 20.14"/>
						<line class="blue line-C" data-name="blue line-C" x1="77.57" y1="36.51" x2="225.54" y2="36.51"/>
						<line class="blue line-B" data-name="blue line-B" x1="256.41" y1="140.47" x2="256.41" y2="257.14"/>
						<polyline class="blue line-A" data-name="blue line-A" points="67.47 277.93 159.88 277.93 172.49 265.32 212.1 265.32 227.66 280.88 265.27 280.88"/>
						<line class="yellow line-C" data-name="yellow line-C" x1="269.22" y1="98.66" x2="269.22" y2="223.59"/>
						<line class="yellow line-B" data-name="yellow line-B" x1="45.99" y1="265.7" x2="105.9" y2="265.7"/>
						<polyline class="yellow line-A" data-name="yellow line-A" points="27.32 210.46 27.32 43.08 49.87 20.53 286.27 20.53"/>
						<polygon class="yellow triangle-B" data-name="yellow triangle-B" points="16.52 20.67 27.56 20.67 16.52 31.7 16.52 20.67"/>
						<polygon class="blue triangle-B" data-name="blue triangle-B" points="51.51 42.03 40.48 42.03 51.51 31 51.51 42.03"/>
						<circle class="yellow circle-A" data-name="yellow circle-A" cx="27.32" cy="220.09" r="8.26"/>
						<polyline class="corner yellow top-right" data-name="corner yellow top-right" points="233.33 2 298 2 298 117.5"/>
						<polyline class="corner blue bottom-left" data-name="corner blue bottom-left" points="2 205.39 2 298 57.64 298"/>
					</svg>
					<span class="button-legend"><?= __("Tabi Piece"); ?></span>
				</button>
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Item Badge", "bluerabbit"); ?></label>
				<div class="br-form-component">
					<div class="br-gallery br-gallery-single">
						<?php $thumb_id = 'the_item_badge'; $file = $is_edit ? $i->item_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Display in shop", "bluerabbit"); ?></label>
				<select id="the_item_visibility" class="br-input cond-opt cond-opt-consumable">
					<option value="visible" <?= (!$is_edit || $i->item_visibility=='visible' || !$i->item_visibility) ? 'selected' : ''; ?>><?= __("Visible","bluerabbit"); ?></option>
					<option value="hidden" <?= ($is_edit && $i->item_visibility=='hidden') ? 'selected' : ''; ?>><?= __("Hidden","bluerabbit"); ?></option>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Level", "bluerabbit"); ?></label>
				<input type="number" min="1" class="br-input cond-opt cond-opt-tabi-piece cond-opt-consumable cond-opt-key" value="<?= $is_edit ? $i->item_level : "1"; ?>" id="the_item_min_level">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Cost", "bluerabbit"); ?></label>
				<div class="br-input-group">
					<input class="br-input cond-opt cond-opt-tabi-piece cond-opt-consumable cond-opt-key" type="number" value="<?= $is_edit ? $i->item_cost : 0; ?>" id="the_item_cost">
					<span class="br-input-suffix"><?= $bloo_label; ?></span>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Stock", "bluerabbit"); ?></label>
				<input class="br-input cond-opt cond-opt-tabi-piece cond-opt-consumable" type="number" value="<?= $is_edit ? $i->item_stock : ""; ?>" id="the_item_stock">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Max per player", "bluerabbit"); ?></label>
				<input class="br-input cond-opt cond-opt-tabi-piece cond-opt-consumable" type="number" value="<?= $is_edit ? $i->item_player_max : 0; ?>" id="the_item_player_max">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Category", "bluerabbit"); ?></label>
				<select id="the_item_category" class="br-input cond-opt cond-opt-consumable">
					<option value="0" <?= !$is_edit || !$i->item_category_id ? 'selected' : ''; ?>>- <?= __("No Category","bluerabbit"); ?> -</option>
					<?php foreach($item_categories as $cat){ ?>
						<option value="<?= $cat->category_id; ?>" <?= $is_edit && $i->item_category_id==$cat->category_id ? 'selected' : ''; ?>><?= esc_html($cat->category_name); ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Available for", "bluerabbit"); ?></label>
				<?php if(!empty($achievements['publish'])){ ?>
					<select id="the_achievement_id" class="br-input">
						<option value="0" <?php if(!$is_edit || !$i->achievement_id){ echo 'selected'; }?>><?= __('All paths','bluerabbit'); ?></option>
						<?php foreach($achievements['publish'] as $a){ ?>
							<option id="achievement-option-<?php echo $a->achievement_id; ?>" value="<?php echo $a->achievement_id;?>" <?php if($is_edit && $i->achievement_id == $a->achievement_id){ echo 'selected'; }?>><?php echo $a->achievement_name; ?></option>
						<?php } ?>
					</select>
				<?php }else{ ?>
					<input id="the_achievement_id" type="hidden" value="0">
					<input class="br-input" value="<?php _e('All Paths','bluerabbit'); ?>" disabled>
				<?php } ?>
			</div>
		</div>

		<?php if($is_edit){ ?>
		<div class="br-form-group">
			<button type="button" class="br-btn ghost" onClick="openItemConditionsModal('item', <?= $i->item_id; ?>);">
				<span class="icon icon-check"></span> <?= __("Conditions","bluerabbit"); ?>
			</button>
		</div>
		<?php } ?>

		<?php if($br_tremendous_allowed){ ?>
		<div class="br-panel-subsection" id="tremendous-reward-panel">
			<h3 class="br-panel-title"><span class="icon icon-bloo"></span> <?= __("Gift Card Reward (Tremendous)","bluerabbit"); ?></h3>
			<span class="br-form-hint"><?= __("When enabled, purchasing this item also sends the player a real gift card by email through Tremendous.com, on top of the normal BLOO cost above. Only applies to Consumable items.","bluerabbit"); ?></span>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Enable gift card for this item","bluerabbit"); ?></label>
				<input type="checkbox" class="cond-opt cond-opt-consumable" id="the_item_tremendous_enabled" <?= ($is_edit && $i->item_tremendous_enabled) ? 'checked' : ''; ?>>
			</div>

			<div class="br-form-grid br-form-grid-2">
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Amount","bluerabbit"); ?></label>
					<input type="number" step="0.01" min="0" class="br-input cond-opt cond-opt-consumable" id="the_item_tremendous_amount" value="<?= $is_edit ? esc_attr($i->item_tremendous_amount) : ''; ?>" placeholder="25.00">
				</div>
				<div class="br-form-group">
					<label class="br-form-label"><?= __("Label shown to the player","bluerabbit"); ?></label>
					<input type="text" class="br-input cond-opt cond-opt-consumable" id="the_item_tremendous_label" value="<?= $is_edit ? esc_attr($i->item_tremendous_label) : ''; ?>" placeholder="<?= __('e.g. "$25 Gift Card of your choice"','bluerabbit'); ?>">
				</div>
			</div>

			<div class="br-form-group">
				<label class="br-form-label"><?= __("Products (what the player can choose from)","bluerabbit"); ?></label>
				<span class="br-form-hint"><?= __("Leave empty to let the player choose from every product available on your Tremendous account.","bluerabbit"); ?></span>
				<div class="br-actions">
					<button type="button" class="br-btn" onClick="brLoadTremendousCatalog();"><?= __("Load Catalog","bluerabbit"); ?></button>
				</div>
				<div id="tremendous-catalog-list" class="br-form-hint"></div>
				<input type="hidden" id="the_item_tremendous_products" value="<?= esc_attr(wp_json_encode($item_tremendous_products)); ?>">
			</div>
		</div>
		<?php } ?>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Description", "bluerabbit"); ?></label>
			<?php
			$wp_editor_settings = ($roles[0]=="administrator") ? array('quicktags'=>true,'editor_height'=>200) : array('quicktags'=>false,'editor_height'=>200);
			wp_editor($is_edit ? $i->item_description : '', 'the_item_description', $wp_editor_settings);
			?>
		</div>
	</div></div>

	<!-- ═══ TABI PLACEMENT ═══ -->
	<div class="br-scroll-section" id="item-tabi-settings-panel"><div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-sabotage"></span> <?= __("Tabi Settings", "bluerabbit"); ?></h3>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Tabi", "bluerabbit"); ?></label>
			<?php if(!empty($tabis)){ ?>
				<select id="the_item_tabi" class="br-input cond-opt cond-opt-tabi-piece">
					<option value="0" <?php if( !$is_edit || !$i->tabi_id){ echo 'selected'; }?>><?php _e('No Tabi','bluerabbit'); ?></option>
					<?php foreach($tabis as $a){ ?>
						<option id="tabi-option-<?php echo $a->tabi_id; ?>" value="<?php echo $a->tabi_id;?>" <?php if($is_edit && $i->tabi_id == $a->tabi_id){ echo 'selected'; }?>><?php echo $a->tabi_name; ?></option>
					<?php } ?>
				</select>
			<?php }else{ ?>
				<input id="the_item_tabi" type="hidden" value="0">
				<span class="br-form-hint"><?php _e('No Tabis available','bluerabbit'); ?></span>
			<?php } ?>
		</div>

		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("X-Axis", "bluerabbit"); ?></label>
				<div class="br-input-group">
					<input class="br-input cond-opt cond-opt-tabi-piece" type="number" value="<?= $is_edit ? $i->item_x : 0; ?>" id="the_item_x">
					<span class="br-input-suffix"><?= __("PX","bluerabbit"); ?></span>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Y-Axis", "bluerabbit"); ?></label>
				<div class="br-input-group">
					<input class="br-input cond-opt cond-opt-tabi-piece" type="number" value="<?= $is_edit ? $i->item_y : 0; ?>" id="the_item_y">
					<span class="br-input-suffix"><?= __("PX","bluerabbit"); ?></span>
				</div>
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Z-Index", "bluerabbit"); ?></label>
				<input class="br-input cond-opt cond-opt-tabi-piece" type="number" value="<?= $is_edit ? $i->item_z : 0; ?>" id="the_item_z">
			</div>
		</div>

		<div class="br-form-group">
			<label class="br-form-label"><?= __("Positioned image", "bluerabbit"); ?></label>
			<div class="br-form-component">
				<div class="br-gallery br-gallery-single">
					<?php $thumb_id = 'the_item_secret_badge'; $file = $is_edit ? $i->item_secret_badge : ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
				</div>
			</div>
		</div>
	</div></div>

</div>

<!-- Fixed Bottom Bar -->
<div class="br-form-bottom-bar">
	<a class="br-btn br-btn-red" href="<?= get_bloginfo('url')."/item-shop/?adventure_id=$adventure->adventure_id"; ?>">
		<span class="icon icon-cancel"></span> <?= __("Cancel", "bluerabbit"); ?>
	</a>
	<div class="br-actions">
		<input type="hidden" id="nonce" value="<?= wp_create_nonce('br_update_item_nonce'); ?>">
		<button id="submit-button" type="button" class="br-btn br-btn-green br-btn-submit" onClick="updateItem();">
			<span class="icon icon-check"></span>
			<?php if($adventure_id && $is_edit){ ?>
				<?= __('Update Item','bluerabbit'); ?>
			<?php }else{ ?>
				<?= __('Create Item','bluerabbit'); ?>
			<?php } ?>
		</button>
	</div>
</div>

<div class="overlay-layer item-conditions-overlay" id="item-conditions-overlay">
	<div class="tabi-conditions-modal-content" id="item-conditions-content">
		<span class="br-text-12 grey-400"><?php _e("Loading...","bluerabbit"); ?></span>
	</div>
</div>

<script>
function brScrollTo(id, btn) {
	document.querySelectorAll('.br-tabs-sticky .br-tab-btn').forEach(function(b) { b.classList.remove('active'); });
	btn.classList.add('active');
	document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
(function() {
	var sections = document.querySelectorAll('.br-scroll-section');
	var buttons  = document.querySelectorAll('.br-tabs-sticky .br-tab-btn');
	if (!sections.length || !buttons.length) return;
	var observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (!entry.isIntersecting) return;
			buttons.forEach(function(b, i) { b.classList.toggle('active', sections[i] && sections[i].id === entry.target.id); });
		});
	}, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });
	sections.forEach(function(s) { observer.observe(s); });
})();

setItemType('<?= $current_type; ?>');
</script>

<?php //wp_enqueue_media();?>

<?php }else{ ?>
	<script>document.location.href="<?php bloginfo('url');?>/404"; </script>
<?php } ?>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>

<?php
	$icon_color = "";
	if($mi->item_type == 'consumable'){
		$mi_color = $icon_color = 'pink';
	}elseif($mi->item_type == 'key'){
		$mi_color = $icon_color = 'indigo';
	}elseif($mi->item_type == 'reward'){
		$mi_color = $icon_color = 'teal';
	}
?>
<div class="card item" id="<?= "req-item-$r->item_id"; ?>">
	<div class="background <?=$mi_color; ?> border rounded-8 blend-overlay"></div>
	<div class="background mix-blend-overlay border rounded-8 opacity-30  background-image" style="background-image: url(<?= $mi->item_badge; ?>);"></div>
	<div class="background mix-blend-overlay border rounded-8 grey-gradient-900 opacity-50"></div>
	<div class="background blue-grey-gradient-900 border rounded-8"></div>
	<div class="layer base relative text-center">
		<?php if($playerHasIt){ ?>
			<h3 class="font _24 w900 white-color"><?= $mi->item_name; ?></h3>
			<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $mi->item_badge; ?>);">
			</div>
			<span class="icon-button absolute font _36 lime-bg-400 layer overlay req-status">
				<span class="icon-check lime-900 perfect-center"></span>
			</span>
			<br>
			<button class="form-ui font _18 green-bg-400" onClick="loadItemCard(<?= $mi->item_id; ?>);">
				<?= __("View","bluerabbit"); ?>
			</button>
		<?php }else{?>
			<h3 class="font _24 w900 white-color"><?= $mi->item_name; ?></h3>
			<div class="sq-100 relative border rounded-max text-center inline-block background text-center margin-10 overflow-hidden"  style="background-image: url(<?= $mi->item_badge; ?>);">
			</div>
			<span class="icon-button perfect-center absolute font _36 red-bg-400 layer overlay req-status">
				<span class="icon-cancel perfect-center"></span>
			</span>
			<br>
			<button class="form-ui font _18 blue-bg-400" onClick="loadItemCard(<?= $mi->item_id; ?>);">
				<?= __("Get it now!","bluerabbit"); ?>
			</button>
		<?php }?>
	</div>
</div>


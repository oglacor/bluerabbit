<?php isset($selected_color) ? $selected_color : ""; ?>
<ul class="color-select select-single">
	<?php 
	$color_array = array('red','pink','purple','deep-purple','indigo','blue','light-blue','cyan','teal','green','light-green','lime','yellow','amber','orange','deep-orange','brown','grey','blue-grey'); 
	?>
	<?php foreach($color_array as $ca){ ?>
		<li class="<?=$ca;?>-bg-500 <?php if(isset($selected_color) && $selected_color == $ca){echo 'active';} ?>" onClick="selectColor('<?=$color_select_id;?>','<?=$ca;?>');"><span class="icon icon-check"></span></li>
	<?php } ?>
</ul>


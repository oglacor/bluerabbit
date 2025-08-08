<tr>
	<td><?php echo $key; ?></td>
	<td>
		<?php echo $q['text']; ?>
	</td>
	<?php 
	$rating=array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0);
	foreach($answers[$key]['values'] as $answer){
		$rating[$answer]++;
	} 
	?>	
	<?php foreach ($rating as $key=>$r){ ?>
		<td class="text-center"><?php echo $r; ?></td>
	<?php } ?>
	<td class="text-center"><?php echo array_sum($rating); ?></td>
</tr>


<?php $active = ($q['selected_answer'] == $t->guild_id) ? 'active' : ''; ?>
	<?php $onClick = "submitSurveyAnswer($key, $t->guild_id, 'guild-$key');";?>

<li class="option cursor-pointer <?php echo "$active"; ?>" id="option-guild-<?php echo $key.$t->guild_id; ?>" onClick="<?php echo $onClick; ?>" >
	<div class="background" style="background-image: url(<?php echo $t->guild_logo; ?>);"></div>
	<div class="background opacity-80 check text-center">
		<span class="icon icon-check lime-500"></span>
	</div>
	<div class="table foreground">
		<div class="table-cell font _26 white-color w500 text-center bottom">
			<div class="blue-grey-bg-800"><?php echo $t->guild_name; ?></div>
		</div>
	</div>
</li>

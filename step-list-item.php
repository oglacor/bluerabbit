<?php
	if($step->step_type == 'dialogue'){
		$step_color = 'blue-bg-50';
	}else if($step->step_type == 'jump'){
		$step_color = 'indigo-bg-50';
	}else if($step->step_type == 'system'){
		$step_color = 'orange-bg-50';
	}else if($step->step_type == 'win'){
		$step_color = 'light-green-bg-100';
	}else if($step->step_type == 'fail'){
		$step_color = 'red-bg-100';
	}else if($step->step_type == 'item-req' || $step->step_type == 'item-grab'){
		$step_color = 'pink-bg-50';
	}else if($step->step_type == 'path-choice'){
		$step_color = 'purple-bg-50';
	}else if($step->step_type == 'choose-avatar' || $step->step_type == 'choose-nickname'){
		$step_color = 'indigo-bg-50';
	}else{
		$step_color = 'white-bg';
	}
?>
<tr class="step <?= $step_color; ?>" id="step-<?= $step->step_id; ?>">
	<td class="text-center">
		<button class="sq-40 form-ui text-center white-color teal-bg-400 font w900 normal"><?= $step->step_order; ?></button>
		<input type="hidden" class="the_step_id_val" value="<?= $step->step_id; ?>" >
	</td>
	<td class="text-center font w900 uppercase step-type padding-5">
		<?= $step->step_type; ?>
	</td>
	<td class="step-title padding-5">
		<?= $step->step_title; ?><?= $step->step_type == 'path-choice' ? "  [Group: $step->step_achievement_group]" : ""; ?>
	</td>
	<td class="text-center padding-5">
		<button class="icon-button amber-bg-400 sq-30 font _18 white-color" onClick="addStep(<?= $step->step_id;?>);">
			<span class="icon icon-duplicate"></span>
		</button>
		<button class="icon-button green-bg-400 sq-30 font _18 white-color" onClick="editStep(<?= $step->step_id;?>);">
			<span class="icon icon-edit"></span>
		</button>
		<button class="icon-button red-bg-400 sq-30 font _18 white-color" onClick="removeStep(<?= $step->step_id;?>);">
			<span class="icon icon-trash"></span>
		</button>
	
	</td>
</tr>

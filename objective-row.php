<tr class="padding-10" id="objective-row-<?= $c->objective_id;?>">
	<td class="objective-id"><?= $c->objective_id;?></td>
	<td class="objective-hint"><?= $c->objective_content;?> </td>
	<td class="objective-row-keyword"><?= $c->objective_keyword;?> </td>
	<td class="objective-type">
		<?php if($c->objective_type =='keyword-input') {
				echo __("Keyword Input","bluerabbit");
			}elseif($c->objective_type =='true-false') { 
				echo __("True / False","bluerabbit");
			}
		?>
	</td>
	<?php if($use_encounters){ ?>
		<td class="objective-row-ep-cost"><?= $c->ep_cost;?> </td>
	<?php } ?>
	<td class="objective-edit-button">
		<button class="form-ui green-bg-400" onClick="editObjective(<?= $c->objective_id;?>);">
			<span class="icon icon-edit"></span> <?= __("Edit","bluerabbit"); ?>
		</button>
	</td>
	<td class="relative objective-delete-button">
		<button class="form-ui red-bg-400 white-color" onClick="showOverlay('#confirm-remove-<?= $c->objective_id; ?>');">
			<span class="icon icon-trash"></span><?php _e("Delete","bluerabbit"); ?>
		</button>
		<div class="confirm-action overlay-layer" id="confirm-remove-<?= $c->objective_id; ?>">
			<button class="form-ui yellow-bg-400 grey-900" onClick="removeObjective(<?= $c->objective_id; ?>);">
				<span class="icon-group">
					<span class="icon-button grey-bg-900 font _14 sq-20">
						<span class="icon icon-warning yellow-400"></span>
					</span>
					<span class="icon-content">
						<span class="line font _18 w900 block" style="white-space: nowrap;"><?php _e("Are you sure?","bluerabbit"); ?></span>
						<span class="line font _14 block" style="white-space: nowrap;"><?php _e("You can't undo this","bluerabbit"); ?></span>
					</span>
				</span>
			</button>
			<button class="close-confirm icon-button font _14 sq-20  blue-grey-bg-800 white-color icon-sm" onClick="hideAllOverlay();">
				<span class="icon icon-cancel white-color"></span>
			</button>
		</div>
	</td>
</tr>

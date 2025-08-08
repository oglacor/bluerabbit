<?php
$objective_content_editor_id = "objective_content_".$c->objective_id;
$objective_success_editor_id = "objective_success_message_".$c->objective_id;
/*

	case 'image-search' : $color='blue'; $icon='image';  break;
	case 'keyword-input : $color='cyan'; $icon='commet';  break;
	case 'true-false' : $color='teal'; $icon='like icon-dislike';  break;
	case 'color-select' : $color='green'; $icon='thumb-view';  break;
	case 'cryptex' : $color='yellow'; $icon='escape-room';  break;
	case 'pin' : $color='orange'; $icon='lock';  break;
	case 'qr-code' : $color='pink'; $icon='qr';  break;
	default : $color='deep-purple'; $icon='objective';  break;
*/
?>
<div class="w-full h-70"></div>
<div class="objective white-bg max-w-900 boxed relative layer base" id="objective-form-<?= $c->objective_id; ?>">
	<input type="hidden" class="objective-id-value" value="<?= $c->objective_id; ?>">
	<input type="hidden" value="keyword-input" class="objective-type">
	<input type="hidden" value="<?= $c->objective_order; ?>" class="objective-order">
	<div class="padding-10 teal-bg-400 white-color w-full relative">
		<h3 class="font _24 w900">
			<span class="icon icon-objectives"></span>
			<?= __("Edit Input Keyword","bluerabbit"); ?>
		</h3>
		<button class="icon-button red-bg-500 absolute top-5 right-5 sq-36 font _18" onClick="tinymce.remove('#<?= $objective_success_editor_id;?>');hideAllOverlay();"><span class="icon icon-cancel"></span></button>
	</div>
	<table class="table w-full" cellpadding="0">
		<thead>
			<tr class="font _12 grey-600">
				<td class="text-right w-150"><?php _e('Setting','bluerabbit'); ?></td>
				<td><?php _e('Value','bluerabbit'); ?></td>
			</tr>
		</thead>
		<tbody class="font _16">
			<tr>
				<td class="text-right line-60">
					<?= __("Keyword","bluerabbit"); ?><br>
				</td>
				<td>
          	  		<input class="form-ui objective-keyword teal-bg-50 grey-900 w-full font _30 w600" id="objective-text-<?= $key; ?>" type="text" maxlength="36" placeholder="<?php _e('Type the Keyword to find','bluerabbit'); ?>" value="<?= $c->objective_keyword; ?>">
					<span class="font _12 red-300"><span class="icon icon-warning"></span><?= __("Updating the keyword will reset the objective for the players","bluerabbit"); ?></span>
				</td>
			</tr>
			<tr>
				<td class="text-right line-60">
					<?= __("Hint","bluerabbit"); ?><br>
					<span class="font _12 grey-400"><?= __("Clue or question","bluerabbit"); ?></span>
				</td>
				<td>
					<?php
					$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
					
					
					wp_editor( $c->objective_content, $objective_content_editor_id, $wp_editor_settings); 
					?>
					
				</td>
			</tr>
			<?php if($use_encounters){ ?>
			<tr>
				<td class="text-right">
					<span class="icon icon-activity"></span>
					<?= __("EP Cost","bluerabbit"); ?>
				</td>
				<td><input id="ep-cost-<?= $key; ?>" type="number" placeholder="<?php _e('Ep Cost','bluerabbit'); ?>" class="form-ui objective-ep-cost white-bg font _20"value="<?= $c->ep_cost; ?>"></td>
			</tr>
			<?php }else{ ?>
				<input id="ep-cost-<?= $key; ?>" type="hidden" value="0" disabled>
			<?php } ?>
			<tr>
				<td class="text-right">
					<?= __("Success Message","bluerabbit"); ?>
				</td>
				<td>
					<?php
					$wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>250);
					
					
					wp_editor( $c->objective_success_message, $objective_success_editor_id, $wp_editor_settings); 
					?>
				</td>
			</tr>
		</tbody>
	</table>
    <div class="w-full text-center padding-10">
        <button class="form-ui green-bg-400 white-color" onClick="updateObjective(<?= $c->objective_id; ?>);">
            <span class="icon icon-check"></span><?php _e("Update objective","bluerabbit"); ?>
        </button>
    </div>
</div>

<script>
	tinyMCE.execCommand( 'mceAddEditor', true, '<?= $objective_success_editor_id; ?>' );
	tinyMCE.execCommand( 'mceAddEditor', true, '<?= $objective_content_editor_id; ?>' );
	
</script>

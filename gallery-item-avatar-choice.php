<?php
/**
 * Gallery item variant for step-button avatar choices.
 * Has an extra delete button to remove the step button.
 *
 * Variables: $thumb_id, $image_url, $callback, $btn (step button object)
 */
if ( ! isset( $thumb_id ) || ! $thumb_id ) return;
$image_url = $image_url ?? '';
$callback  = $callback  ?? '';
$has_file  = ! empty( $image_url );
$mime      = $has_file ? wp_check_filetype( $image_url ) : [ 'type' => '' ];
$is_video  = $has_file && isset( $mime['type'] ) && strstr( $mime['type'], 'video' );
?>
<div class="br-gallery-item" id="step-button-<?= $btn->button_id; ?>">
	<div class="br-gallery-thumb" onClick="showWPUpload('<?= esc_attr( $thumb_id ); ?>' <?= $callback; ?>);" id="<?= esc_attr( $thumb_id ); ?>_thumb"
		 style="<?= $has_file && ! $is_video ? 'background-image:url(' . esc_url( $image_url ) . ')' : ''; ?>">
		<?php if ( ! $has_file ) { ?>
		<span class="br-gallery-placeholder"><span class="icon icon-image"></span></span>
		<?php } ?>
		<?php if ( $is_video ) { ?>
		<video id="<?= esc_attr( $thumb_id ); ?>_thumb_video" class="br-gallery-video active" controls>
			<source src="<?= esc_url( $image_url ); ?>">
		</video>
		<?php } ?>
	</div>
	<div class="br-gallery-actions">
		<button type="button" class="br-gallery-btn br-gallery-btn-upload" onClick="showWPUpload('<?= esc_attr( $thumb_id ); ?>' <?= $callback; ?>);" title="<?= esc_attr__( 'Choose image', 'bluerabbit' ); ?>">
			<span class="icon icon-image"></span>
		</button>
		<button type="button" class="br-gallery-btn br-gallery-btn-remove" onClick="clearImage('#<?= esc_attr( $thumb_id ); ?>');" title="<?= esc_attr__( 'Remove image', 'bluerabbit' ); ?>">
			<span class="icon icon-trash"></span>
		</button>
		<button type="button" class="br-gallery-btn br-gallery-btn-delete" onClick="removeStepButton(<?= $btn->button_id; ?>);" title="<?= esc_attr__( 'Delete button', 'bluerabbit' ); ?>">
			<span class="icon icon-cancel"></span>
		</button>
	</div>
	<input type="hidden" id="<?= esc_attr( $thumb_id ); ?>" value="<?= esc_attr( $image_url ); ?>">
</div>

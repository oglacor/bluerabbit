<?php
/**
 * Reusable gallery item component for image/video upload.
 *
 * Variables (set before including):
 *   $thumb_id  (string)  — unique ID for the hidden input and thumbnail (required)
 *   $file      (string)  — current image/video URL (optional, default '')
 *   $callback  (string)  — extra JS callback param for showWPUpload (optional, default '')
 *
 * Usage:
 *   <?php $thumb_id = 'the_quest_badge'; $file = $quest->mech_badge ?? ''; include(TEMPLATEPATH . '/gallery-item.php'); ?>
 *   OR via helper:
 *   <?php BR_Utils::instance()->insertGalleryItem('the_quest_badge', $quest->mech_badge); ?>
 */
if ( ! isset( $thumb_id ) || ! $thumb_id ) return;
$file     = $file     ?? '';
$callback = $callback ?? '';
$has_file = ! empty( $file );
$mime     = $has_file ? wp_check_filetype( $file ) : [ 'type' => '' ];
$is_video = $has_file && isset( $mime['type'] ) && strstr( $mime['type'], 'video' );
?>
<div class="br-gallery-item" id="<?= esc_attr( $thumb_id ); ?>_wrap">
	<div class="br-gallery-thumb" onClick="showWPUpload('<?= esc_attr( $thumb_id ); ?>' <?= $callback; ?>);" id="<?= esc_attr( $thumb_id ); ?>_thumb"
		 style="<?= $has_file && ! $is_video ? 'background-image:url(' . esc_url( $file ) . ')' : ''; ?>">
		<?php if ( ! $has_file ) { ?>
		<span class="br-gallery-placeholder"><span class="icon icon-image"></span></span>
		<?php } ?>
		<?php if ( $is_video ) { ?>
		<video id="<?= esc_attr( $thumb_id ); ?>_thumb_video" class="br-gallery-video active" controls>
			<source src="<?= esc_url( $file ); ?>">
		</video>
		<?php } ?>
	</div>
	<div class="br-gallery-actions">
		<button type="button" class="br-gallery-btn br-gallery-btn-upload" onClick="showWPUpload('<?= esc_attr( $thumb_id ); ?>' <?= $callback; ?>);" title="<?= esc_attr__( 'Choose image', 'bluerabbit' ); ?>">
			<span class="icon icon-image"></span>
		</button>
		<button type="button" class="br-gallery-btn br-gallery-btn-remove" onClick="clearImage('#<?= esc_attr( $thumb_id ); ?>');" title="<?= esc_attr__( 'Remove', 'bluerabbit' ); ?>">
			<span class="icon icon-trash"></span>
		</button>
	</div>
	<input type="hidden" id="<?= esc_attr( $thumb_id ); ?>" value="<?= esc_attr( $file ); ?>">
</div>

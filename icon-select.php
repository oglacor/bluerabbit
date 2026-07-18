<?php
/**
 * Icon picker component — icon swatches grid + live preview.
 *
 * Variables (set before including):
 *   $icon_select_id  (string)  — jQuery selector for the hidden input, e.g. "#the_quest_icon"
 *   $selected_icon   (string)  — current icon slug, e.g. "quest"
 */
$selected_icon = $selected_icon ?? '';

$icons = [
	'stopwatch', 'sabotage', 'tools', 'tw', 'run', 'time', 'socialiser', 'megaphone',
	'max', 'narrative', 'goal', 'quest', 'comment', 'story', 'logo', 'mission',
	'magic', 'freespirit', 'document', 'enemy', 'favorite', 'world', 'ticket',
	'achiever', 'boundaries', 'activity', 'carrot', 'challenge', 'escape-room',
	'class', 'environment', 'tag', 'objectives', 'winstate',
];

if ( ! in_array( $selected_icon, $icons, true ) ) {
	$selected_icon = $icons[11]; // 'quest'
}

$icon_label = function ( $icon ) {
	return ucwords( str_replace( [ '-', '_' ], ' ', $icon ) );
};

$uid = 'bris_' . substr( md5( $icon_select_id ), 0, 6 );
?>
<div class="br-icon-select" id="<?= $uid; ?>">
	<div class="br-icon-select-preview">
		<span class="br-icon-select-preview-glyph icon icon-<?= esc_attr( $selected_icon ); ?>" id="<?= $uid; ?>_preview"></span>
		<span class="br-icon-select-preview-label" id="<?= $uid; ?>_preview_label"><?= esc_html( $icon_label( $selected_icon ) ); ?></span>
	</div>
	<div class="br-icon-swatches">
		<?php foreach ( $icons as $icon ) { ?>
		<button type="button" class="br-icon-swatch<?= $selected_icon === $icon ? ' active' : ''; ?>"
				data-icon="<?= esc_attr( $icon ); ?>"
				title="<?= esc_attr( $icon_label( $icon ) ); ?>"
				onClick="brPickIcon('<?= $uid; ?>', '<?= esc_js( $icon_select_id ); ?>', '<?= esc_js( $icon ); ?>');">
			<span class="icon icon-<?= esc_attr( $icon ); ?>"></span>
		</button>
		<?php } ?>
	</div>
</div>

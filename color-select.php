<?php
/**
 * Color picker component — hex swatches + opacity slider.
 *
 * Variables (set before including):
 *   $color_select_id  (string)  — jQuery selector for the hidden input, e.g. "#the_quest_color"
 *   $selected_color   (string)  — current value: legacy name ("red") or hex ("#f44336") or rgba()
 *
 * Stores the final value as hex (e.g. "#f44336") or rgba (e.g. "rgba(244,67,54,0.5)") in the hidden input.
 * Legacy color names are resolved to hex on render so old data works seamlessly.
 */
$selected_color   = $selected_color ?? '';
$selected_hex     = br_color_to_hex( $selected_color );
$selected_opacity = 100;

if ( preg_match( '/^rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([\d.]+)\s*\)$/', $selected_hex, $m ) ) {
	$selected_hex     = sprintf( '#%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3] );
	$selected_opacity = (int) round( (float) $m[4] * 100 );
}

$preview_color = $selected_opacity < 100
	? sprintf( 'rgba(%d,%d,%d,%s)', hexdec( substr( $selected_hex, 1, 2 ) ), hexdec( substr( $selected_hex, 3, 2 ) ), hexdec( substr( $selected_hex, 5, 2 ) ), $selected_opacity / 100 )
	: $selected_hex;

$swatches = [
	'#ef9a9a' => 'Light Red',     '#f44336' => 'Red',          '#c62828' => 'Dark Red',
	'#f48fb1' => 'Light Pink',    '#e91e63' => 'Pink',         '#ad1457' => 'Dark Pink',
	'#ff6e6e' => 'Coral',         '#ff80ab' => 'Rose',
	'#ffab91' => 'Light Orange',  '#ff9800' => 'Orange',       '#ff5722' => 'Deep Orange',  '#bf360c' => 'Dark Orange',
	'#fff176' => 'Light Yellow',  '#f7cb15' => 'Yellow',       '#ffc107' => 'Amber',        '#ffd54f' => 'Gold',
	'#dce775' => 'Light Lime',    '#c6ff00' => 'Neon Lime',    '#8bc34a' => 'Lime',
	'#a5d6a7' => 'Light Green',   '#69f0ae' => 'Mint',         '#24da98' => 'Green',        '#2e7d32' => 'Dark Green',
	'#80cbc4' => 'Light Teal',    '#00bcd4' => 'Teal',         '#00695c' => 'Dark Teal',
	'#80deea' => 'Light Cyan',    '#1cc2eb' => 'Cyan',         '#00838f' => 'Dark Cyan',
	'#90caf9' => 'Light Blue',    '#42a5f5' => 'Sky',          '#2196f3' => 'Blue',         '#1565c0' => 'Dark Blue',
	'#b388ff' => 'Light Indigo',  '#7c4dff' => 'Indigo',       '#304ffe' => 'Dark Indigo',
	'#ce93d8' => 'Light Purple',  '#9f40e2' => 'Purple',       '#6a1b9a' => 'Dark Purple',
	'#e040fb' => 'Magenta',
	'#ffffff' => 'White',         '#b0bec5' => 'Light Slate',  '#78909c' => 'Slate',        '#455a64' => 'Charcoal',  '#000000' => 'Black',
];

$uid = 'brcs_' . substr( md5( $color_select_id ), 0, 6 );
?>
<div class="br-color-select" id="<?= $uid; ?>" data-hex="<?= esc_attr( $selected_hex ); ?>">
	<div class="br-color-swatches">
		<?php foreach ( $swatches as $hex => $label ) { ?>
		<button type="button" class="br-color-swatch<?= $selected_hex === $hex ? ' active' : ''; ?>"
				style="background:<?= $hex; ?>;<?= $hex === '#ffffff' ? 'border:1px solid rgba(255,255,255,0.2);' : ''; ?><?= $hex === '#000000' ? 'border:1px solid rgba(255,255,255,0.15);' : ''; ?>"
				data-hex="<?= $hex; ?>"
				title="<?= esc_attr( $label ); ?>"
				onClick="brPickColor('<?= $uid; ?>', '<?= esc_js( $color_select_id ); ?>', '<?= $hex; ?>');">
			<span class="icon icon-check"></span>
		</button>
		<?php } ?>
	</div>
	<div class="br-color-opacity-row">
		<span class="br-color-preview" id="<?= $uid; ?>_preview" style="background:<?= esc_attr( $preview_color ); ?>"></span>
		<label class="br-color-opacity-label"><?= __("Opacity","bluerabbit"); ?></label>
		<input type="range" class="br-color-opacity-slider" id="<?= $uid; ?>_opacity" min="10" max="100" step="5" value="<?= $selected_opacity; ?>"
			   onInput="brUpdateOpacity('<?= $uid; ?>', '<?= esc_js( $color_select_id ); ?>');">
		<span class="br-color-opacity-value" id="<?= $uid; ?>_opacity_val"><?= $selected_opacity; ?>%</span>
	</div>
</div>

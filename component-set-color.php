<?php
/**
 * Inline color picker for manage pages (tabis, guilds, achievements).
 * Calls setColor() instead of selectColor() — different callback.
 *
 * Variables: $selected_color, $object_color_id, $object_type
 */
$selected_color = $selected_color ?? '';
$selected_hex   = br_color_to_hex( $selected_color );

if ( preg_match( '/^rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([\d.]+)\s*\)$/', $selected_hex, $m ) ) {
	$selected_hex = sprintf( '#%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3] );
}

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
?>
<div class="br-color-swatches">
	<?php foreach ( $swatches as $hex => $label ) { ?>
	<button type="button" class="br-color-swatch<?= $selected_hex === $hex ? ' active' : ''; ?>"
			style="background:<?= $hex; ?>;<?= $hex === '#ffffff' ? 'border:1px solid rgba(255,255,255,0.2);' : ''; ?><?= $hex === '#000000' ? 'border:1px solid rgba(255,255,255,0.15);' : ''; ?>"
			title="<?= esc_attr( $label ); ?>"
			onClick="jQuery(this).siblings().removeClass('active');jQuery(this).addClass('active');setColor(<?= $object_color_id; ?>,'<?= $hex; ?>','<?= $object_type; ?>');">
		<span class="icon icon-check"></span>
	</button>
	<?php } ?>
</div>

<?php
/**
 * Inline color picker for manage pages (tabis, guilds, achievements).
 * Calls setColor() instead of selectColor() — different callback.
 *
 * Variables: $selected_color, $object_color_id, $object_type
 */
$selected_color = $selected_color ?? '';
$selected_hex   = br_color_to_hex( $selected_color );

$swatches = [
	'#f44336' => 'Red',       '#e91e63' => 'Pink',      '#9f40e2' => 'Purple',
	'#7c4dff' => 'Indigo',    '#2196f3' => 'Blue',      '#1cc2eb' => 'Cyan',
	'#00bcd4' => 'Teal',      '#24da98' => 'Green',     '#69f0ae' => 'Mint',
	'#8bc34a' => 'Lime',      '#c6ff00' => 'Neon Lime', '#f7cb15' => 'Yellow',
	'#ffc107' => 'Amber',     '#ff9800' => 'Orange',    '#ff5722' => 'Deep Orange',
	'#ff6e6e' => 'Coral',     '#e040fb' => 'Magenta',   '#ff80ab' => 'Rose',
	'#42a5f5' => 'Sky',       '#ffd54f' => 'Gold',
	'#78909c' => 'Slate',     '#455a64' => 'Charcoal',  '#ffffff' => 'White',
	'#000000' => 'Black',
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

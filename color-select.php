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

$uid = 'brcs_' . substr( md5( $color_select_id ), 0, 6 );
?>
<div class="br-color-select" id="<?= $uid; ?>">
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
		<span class="br-color-preview" id="<?= $uid; ?>_preview" style="background:<?= esc_attr( $selected_hex ); ?>"></span>
		<label class="br-color-opacity-label"><?= __("Opacity","bluerabbit"); ?></label>
		<input type="range" class="br-color-opacity-slider" id="<?= $uid; ?>_opacity" min="10" max="100" step="5" value="100"
			   onInput="brUpdateOpacity('<?= $uid; ?>', '<?= esc_js( $color_select_id ); ?>');">
		<span class="br-color-opacity-value" id="<?= $uid; ?>_opacity_val">100%</span>
	</div>
</div>

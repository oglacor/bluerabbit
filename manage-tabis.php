<?php
$tabis = BR_Tabi::instance()->getTabis($adventure->adventure_id);
$tabi_prereq_nonce = wp_create_nonce('tabi_prereq_nonce');
$tabi_as_category_nonce = wp_create_nonce('tabi_as_category_nonce');
$tabi_count = $tabis ? count($tabis) : 0;
?>

<div class="br-journey-manager">

	<!-- ════════════ HEADER ════════════ -->
	<div class="br-panel br-manage-header-panel">
		<div class="br-manage-header-row">
			<div class="br-manage-header-left">
				<div class="br-manage-icon-box">
					<span class="icon icon-sabotage"></span>
				</div>
				<div>
					<h2 class="br-panel-title br-manage-panel-title"><?php _e('Tabi Manager', 'bluerabbit'); ?></h2>
					<span class="br-manage-subtitle">
						<?= sprintf(__('%d tabis in this adventure', 'bluerabbit'), $tabi_count); ?>
					</span>
				</div>
			</div>
			<div class="br-actions">
				<button class="br-btn cyan" onClick="addTabi();">
					<span class="icon icon-add"></span> <?= __("Add Tabi", "bluerabbit"); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- ════════════ COLUMN HEADERS ════════════ -->
	<div class="br-panel br-manage-col-header-panel">
		<div class="br-tabi-grid br-tabi-col-header">
			<div class="br-text-center">#</div>
			<div><?= __("Name","bluerabbit"); ?></div>
			<div><?= __("Img","bluerabbit"); ?></div>
			<div><?= __("Color","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Level","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Width","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Height","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Journey","bluerabbit"); ?></div>
			<div class="br-text-center"><?= __("Category","bluerabbit"); ?></div>
		</div>
	</div>

	<!-- ════════════ TABIS LIST ════════════ -->
	<div class="br-section-body" id="table-tabis">
		<?php if($tabis && count($tabis) > 0){ ?>
			<?php foreach($tabis as $avKey => $a){ ?>
				<?php
				// The same partial BR_Tabi::insertTabiRow() echoes when "Add Tabi" appends
				// a row over AJAX - one copy of the markup, so the two cannot drift apart
				// again and leave newly added tabis rendered in the pre-redesign style.
				$rowNumber = $avKey + 1;
				include (TEMPLATEPATH . "/tabi-row.php");
				?>
			<?php } ?>
		<?php } else { ?>
			<div class="br-empty">
				<span class="icon icon-sabotage"></span>
				<h3><?= __("No tabis created yet","bluerabbit"); ?></h3>
				<p><?= __("Click 'Add Tabi' to create your first one.","bluerabbit"); ?></p>
			</div>
		<?php } ?>
	</div>

	<input type="hidden" id="row_type" value="tabi"/>
	<input type="hidden" id="tabi-on-journey-nonce" value="<?= wp_create_nonce('tabi_on_journey_nonce'); ?>">
	<input type="hidden" id="tabi-as-category-nonce" value="<?= $tabi_as_category_nonce; ?>">
	<input type="hidden" id="add-tabi-nonce" value="<?= wp_create_nonce('add_tabi_nonce'); ?>">
	<input type="hidden" id="dimensions-nonce" value="<?= wp_create_nonce('dimensions_nonce'); ?>">

</div><!-- /.br-journey-manager -->

<div class="tabi-editor-container" id="tabi-editor-container"></div>

<script>
(function($){
	// Prerequisites expand toggle
	$(document).on('click', '.br-action-link.expand', function(e){
		e.preventDefault();
		var targetId = $(this).data('target');
		var panel = $('#' + targetId);
		var isOpen = panel.hasClass('open');
		$('.br-quick-edit.open').removeClass('open');
		$('.br-action-link.expand.open').removeClass('open');
		if(!isOpen){
			panel.addClass('open');
			$(this).addClass('open');
		}
	});
})(jQuery);
</script>

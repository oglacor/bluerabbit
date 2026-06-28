<?php
$branch_groups = BR_Branch::instance()->getBranchGroups($adventure->adventure_id);
$path_achievements = $wpdb->get_results($wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}br_achievements
	 WHERE adventure_id = %d AND achievement_display IN ('path','rank') AND achievement_status = 'publish'
	 ORDER BY achievement_name",
	$adventure->adventure_id
));
$assigned_ids = [];
foreach ($path_achievements as $pa) {
	if ($pa->branch_group_id) $assigned_ids[] = $pa->achievement_id;
}
$available_achievements = array_filter($path_achievements, function($a) { return empty($a->branch_group_id); });
?>

<div class="br-journey-manager">

	<!-- ════════════ HEADER ════════════ -->
	<div class="br-panel br-manage-header-panel">
		<div class="br-manage-header-row">
			<div class="br-manage-header-left">
				<div class="br-manage-icon-box">
					<span class="icon icon-quest"></span>
				</div>
				<div>
					<h2 class="br-panel-title br-manage-panel-title"><?php _e('Branch Groups', 'bluerabbit'); ?></h2>
					<span class="br-manage-subtitle">
						<?= __("Group path achievements so players can choose between them", "bluerabbit"); ?>
					</span>
				</div>
			</div>
			<button class="br-btn cyan" onClick="brShowGroupForm();">
				<span class="icon icon-add"></span> <?= __("New Branch", "bluerabbit"); ?>
			</button>
		</div>
	</div>

	<?php if (empty($path_achievements)) { ?>
	<div class="br-panel br-branch-warning-panel">
		<span class="br-branch-warning-text">
			<span class="icon icon-warning br-icon-accent"></span>
			<?= __("No PATH or RANK achievements found. Create path achievements in the Achievements tab first.", "bluerabbit"); ?>
		</span>
	</div>
	<?php } ?>

	<!-- ════════════ NEW GROUP FORM (inline) ════════════ -->
	<div id="branch-group-form" class="br-branch-form">
		<div class="br-panel br-branch-form-panel">
			<input type="hidden" id="branch-group-id" value="">
			<div class="br-branch-form-row">
				<div class="br-form-group br-branch-form-input-wrap">
					<label class="br-form-label"><?= __("Branch Name", "bluerabbit"); ?></label>
					<input class="br-input" id="branch-group-name" placeholder="<?= __('e.g. Class, Faction, House', 'bluerabbit'); ?>">
				</div>
				<div class="br-branch-form-actions">
					<button class="br-btn br-btn-green" onClick="brSaveBranchGroup();"><span class="icon icon-check"></span> <?= __("Save", "bluerabbit"); ?></button>
					<button class="br-btn ghost" onClick="brHideGroupForm();"><?= __("Cancel", "bluerabbit"); ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- ════════════ GROUPS LIST ════════════ -->
	<div class="br-section-body br-branch-list" id="branch-groups-list">
		<?php if ($branch_groups) { ?>
			<?php foreach ($branch_groups as $bg) { ?>
			<?php $group_achievements = BR_Branch::instance()->getGroupAchievements($bg->group_id); ?>
			<div class="br-panel br-branch-group-panel" id="branch-group-<?= $bg->group_id; ?>">

				<!-- Group title row -->
				<div class="br-branch-group-title-row">
					<div class="br-branch-group-name-row">
						<span class="br-branch-group-name"><?= esc_html($bg->group_name); ?></span>
						<span class="br-badge br-branch-badge"><?= count($group_achievements); ?> <?= __("paths", "bluerabbit"); ?></span>
					</div>
					<?php if (empty($group_achievements)) { ?>
					<button class="br-btn br-btn-red br-branch-delete-btn" onClick="brDeleteGroup(<?= $bg->group_id; ?>);">
						<span class="icon icon-trash"></span> <?= __("Delete", "bluerabbit"); ?>
					</button>
					<?php } ?>
				</div>

				<!-- Assigned achievements -->
				<div class="br-branch-achievements br-branch-ach-list" id="branch-ach-list-<?= $bg->group_id; ?>">
					<?php if ($group_achievements) { foreach ($group_achievements as $ga) { ?>
					<div class="br-branch-ach-card" id="branch-ach-<?= $ga->achievement_id; ?>">
						<?php if ($ga->achievement_badge) { ?>
						<div class="br-branch-ach-thumb" style="background-image:url(<?= esc_attr($ga->achievement_badge); ?>)"></div>
						<?php } else { ?>
						<div class="br-branch-ach-placeholder"><span class="icon icon-achievement br-icon-purple"></span></div>
						<?php } ?>
						<div>
							<span class="br-branch-ach-name"><?= esc_html($ga->achievement_name); ?></span>
							<span class="br-branch-ach-type"><?= $ga->achievement_display; ?></span>
						</div>
						<button class="br-btn ghost br-branch-remove-btn" onClick="brRemoveAchFromGroup(<?= $ga->achievement_id; ?>, <?= $bg->group_id; ?>);" title="<?= __('Remove', 'bluerabbit'); ?>">
							<span class="icon icon-cancel br-icon-red"></span>
						</button>
					</div>
					<?php } } else { ?>
					<span class="br-branch-empty-text"><?= __("No achievements assigned yet. Add one below.", "bluerabbit"); ?></span>
					<?php } ?>
				</div>

				<!-- Add achievement -->
				<div class="br-branch-add-row">
					<select class="br-input br-branch-add-select" id="add-ach-select-<?= $bg->group_id; ?>">
						<option value=""><?= __("Select a path achievement...", "bluerabbit"); ?></option>
						<?php foreach ($available_achievements as $aa) { ?>
						<option value="<?= $aa->achievement_id; ?>"><?= esc_html($aa->achievement_name); ?> (<?= $aa->achievement_display; ?>)</option>
						<?php } ?>
						<?php foreach ($group_achievements as $ga) { /* already assigned — don't show */ } ?>
					</select>
					<button class="br-btn cyan br-branch-add-btn" onClick="brAssignAchToGroup(<?= $bg->group_id; ?>);">
						<span class="icon icon-add"></span> <?= __("Add", "bluerabbit"); ?>
					</button>
				</div>

			</div>
			<?php } ?>
		<?php } else { ?>
			<div class="br-empty">
				<span class="icon icon-quest"></span>
				<h3><?= __("No branches yet", "bluerabbit"); ?></h3>
				<p><?= __("Create a branch to group path achievements together. Players will choose one path from the group.", "bluerabbit"); ?></p>
			</div>
		<?php } ?>
	</div>

</div><!-- /.br-journey-manager -->

<script>
function brShowGroupForm(id, name) {
	$('#branch-group-id').val(id || '');
	$('#branch-group-name').val(name || '');
	$('#branch-group-form').slideDown(200);
	$('#branch-group-name').focus();
}
function brHideGroupForm() { $('#branch-group-form').slideUp(200); }

function brSaveBranchGroup() {
	var name = $('#branch-group-name').val();
	if (!name) return;
	showLoader('small');
	$.ajax({
		url: runAJAX.ajaxurl, method: 'POST',
		data: {
			action: 'br_update_branch_group',
			adventure_id: $('#the_adventure_id').val(),
			group_id: $('#branch-group-id').val(),
			group_name: name,
			group_description: '',
			group_status: 'publish'
		},
		success: function(json) { displayAjaxResponse(json); setTimeout(function(){ location.reload(); }, 600); }
	});
}

function brAssignAchToGroup(groupId) {
	var achId = $('#add-ach-select-' + groupId).val();
	if (!achId) return;
	showLoader('small');
	$.ajax({
		url: runAJAX.ajaxurl, method: 'POST',
		data: { action: 'br_assign_achievement_to_group', achievement_id: achId, group_id: groupId },
		success: function(json) { displayAjaxResponse(json); setTimeout(function(){ location.reload(); }, 600); }
	});
}

function brRemoveAchFromGroup(achId, groupId) {
	showLoader('small');
	$.ajax({
		url: runAJAX.ajaxurl, method: 'POST',
		data: { action: 'br_remove_achievement_from_group', achievement_id: achId },
		success: function(json) {
			displayAjaxResponse(json);
			$('#branch-ach-' + achId).fadeOut(200, function(){ $(this).remove(); });
		}
	});
}

function brDeleteGroup(groupId) {
	showLoader('small');
	$.ajax({
		url: runAJAX.ajaxurl, method: 'POST',
		data: { action: 'br_delete_branch_group', group_id: groupId },
		success: function(json) { displayAjaxResponse(json); setTimeout(function(){ location.reload(); }, 600); }
	});
}
</script>

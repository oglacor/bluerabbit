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
	<div class="br-panel" style="margin-bottom:0;border-radius:12px 12px 0 0;">
		<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;justify-content:space-between;">
			<div style="display:flex;align-items:center;gap:14px;">
				<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(159,64,226,0.15);border-radius:10px;font-size:22px;color:#9f40e2;">
					<span class="icon icon-quest"></span>
				</div>
				<div>
					<h2 class="br-panel-title" style="margin:0;"><?php _e('Branch Groups', 'bluerabbit'); ?></h2>
					<span style="font-size:13px;color:rgba(255,255,255,0.45);">
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
	<div class="br-panel" style="margin-top:2px;border-radius:0;padding:16px;text-align:center">
		<span style="color:rgba(255,255,255,0.5);font-size:14px">
			<span class="icon icon-warning" style="color:#f7cb15"></span>
			<?= __("No PATH or RANK achievements found. Create path achievements in the Achievements tab first.", "bluerabbit"); ?>
		</span>
	</div>
	<?php } ?>

	<!-- ════════════ NEW GROUP FORM (inline) ════════════ -->
	<div id="branch-group-form" style="display:none;margin-top:2px;">
		<div class="br-panel" style="border-radius:0;">
			<input type="hidden" id="branch-group-id" value="">
			<div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
				<div class="br-form-group" style="flex:1;min-width:200px;margin:0">
					<label class="br-form-label"><?= __("Branch Name", "bluerabbit"); ?></label>
					<input class="br-input" id="branch-group-name" placeholder="<?= __('e.g. Class, Faction, House', 'bluerabbit'); ?>">
				</div>
				<div style="display:flex;gap:6px;padding-bottom:2px">
					<button class="br-btn br-btn-green" onClick="brSaveBranchGroup();"><span class="icon icon-check"></span> <?= __("Save", "bluerabbit"); ?></button>
					<button class="br-btn ghost" onClick="brHideGroupForm();"><?= __("Cancel", "bluerabbit"); ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- ════════════ GROUPS LIST ════════════ -->
	<div class="br-section-body" id="branch-groups-list" style="border-radius:0 0 12px 12px;">
		<?php if ($branch_groups) { ?>
			<?php foreach ($branch_groups as $bg) { ?>
			<?php $group_achievements = BR_Branch::instance()->getGroupAchievements($bg->group_id); ?>
			<div class="br-panel" style="margin-bottom:2px;border-radius:0;padding:16px" id="branch-group-<?= $bg->group_id; ?>">

				<!-- Group title row -->
				<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
					<div style="display:flex;align-items:center;gap:10px">
						<span style="font-size:17px;font-weight:700;color:#fff"><?= esc_html($bg->group_name); ?></span>
						<span class="br-badge" style="font-size:10px;background:rgba(159,64,226,0.15);color:#9f40e2"><?= count($group_achievements); ?> <?= __("paths", "bluerabbit"); ?></span>
					</div>
					<?php if (empty($group_achievements)) { ?>
					<button class="br-btn br-btn-red" style="padding:4px 10px;font-size:11px" onClick="brDeleteGroup(<?= $bg->group_id; ?>);">
						<span class="icon icon-trash"></span> <?= __("Delete", "bluerabbit"); ?>
					</button>
					<?php } ?>
				</div>

				<!-- Assigned achievements -->
				<div class="br-branch-achievements" id="branch-ach-list-<?= $bg->group_id; ?>" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
					<?php if ($group_achievements) { foreach ($group_achievements as $ga) { ?>
					<div class="br-branch-ach-card" id="branch-ach-<?= $ga->achievement_id; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
						<?php if ($ga->achievement_badge) { ?>
						<div style="width:36px;height:36px;border-radius:6px;background:url(<?= esc_attr($ga->achievement_badge); ?>) center/cover;flex-shrink:0"></div>
						<?php } else { ?>
						<div style="width:36px;height:36px;border-radius:6px;background:rgba(159,64,226,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0"><span class="icon icon-achievement" style="color:#9f40e2"></span></div>
						<?php } ?>
						<div>
							<span style="font-weight:600;font-size:14px;color:#fff"><?= esc_html($ga->achievement_name); ?></span>
							<span style="display:block;font-size:11px;color:rgba(255,255,255,0.35)"><?= $ga->achievement_display; ?></span>
						</div>
						<button class="br-btn ghost" style="padding:2px 6px;font-size:10px;margin-left:4px" onClick="brRemoveAchFromGroup(<?= $ga->achievement_id; ?>, <?= $bg->group_id; ?>);" title="<?= __('Remove', 'bluerabbit'); ?>">
							<span class="icon icon-cancel" style="color:#f44336"></span>
						</button>
					</div>
					<?php } } else { ?>
					<span style="font-size:13px;color:rgba(255,255,255,0.3);padding:4px 0"><?= __("No achievements assigned yet. Add one below.", "bluerabbit"); ?></span>
					<?php } ?>
				</div>

				<!-- Add achievement -->
				<div style="display:flex;gap:6px;align-items:center">
					<select class="br-input" id="add-ach-select-<?= $bg->group_id; ?>" style="flex:1;max-width:300px;padding:6px 12px;font-size:13px">
						<option value=""><?= __("Select a path achievement...", "bluerabbit"); ?></option>
						<?php foreach ($available_achievements as $aa) { ?>
						<option value="<?= $aa->achievement_id; ?>"><?= esc_html($aa->achievement_name); ?> (<?= $aa->achievement_display; ?>)</option>
						<?php } ?>
						<?php foreach ($group_achievements as $ga) { /* already assigned — don't show */ } ?>
					</select>
					<button class="br-btn cyan" style="padding:6px 12px;font-size:12px" onClick="brAssignAchToGroup(<?= $bg->group_id; ?>);">
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

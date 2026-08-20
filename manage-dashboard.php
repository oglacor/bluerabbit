<?php
/**
 * Adventure Dashboard — manage-dashboard.php
 * Included from page-manage-adventure.php, and the tab that opens by default.
 *
 * One read-only screen answering "what does this adventure hold, and is it running
 * smoothly". Every content row is split by STATUS rather than reduced to a total,
 * because locked and hidden content is what silently breaks a journey: a Tabi
 * holding nine locked milestones looks finished on the map and cannot be completed,
 * and hidden milestones are trivially forgotten because nothing else in the
 * management UI shows them.
 *
 * Counts come from BR_Stats, which uses the same status vocabulary as the completion
 * logic (br_journey_status_sql: publish/locked/hidden are the journey, draft and
 * trash are outside it). No second definition lives here.
 *
 * Read-only by design: no JS, no forms. Every row links to the tab that can edit it.
 */

$dash_stats  = new BR_Stats();
$dash_aid    = (int) $adventure->adventure_id;
$health      = $dash_stats->get_adventure_health( $dash_aid );
$inventory   = $dash_stats->get_content_inventory( $dash_aid );
$dash_base   = get_bloginfo('url') . "/manage-adventure/?adventure_id=$dash_aid&manage=";

// Row: key (matches the inventory map) => label, icon, tab to link to, feature flag.
// A row whose feature is switched off is still shown when it holds content — that is
// precisely the content nobody remembers owning. Rows with neither are dropped.
$dash_sections = [
	[
		'title' => __('Journey', 'bluerabbit'),
		'icon'  => 'journey',
		'rows'  => [
			['key'=>'quest',               'label'=>__('Milestones','bluerabbit'),          'icon'=>'quest',      'tab'=>'journey',  'on'=>true],
			['key'=>'challenge',           'label'=>__('Challenges','bluerabbit'),          'icon'=>'challenge',  'tab'=>'journey',  'on'=>!empty($use_challenges)],
			['key'=>'challenge-questions', 'label'=>__('Challenge questions','bluerabbit'), 'icon'=>'question',   'tab'=>'journey',  'on'=>!empty($use_challenges)],
			['key'=>'survey',              'label'=>__('Surveys','bluerabbit'),             'icon'=>'survey',     'tab'=>'journey',  'on'=>true],
			['key'=>'survey-questions',    'label'=>__('Survey questions','bluerabbit'),    'icon'=>'question',   'tab'=>'journey',  'on'=>true],
			['key'=>'mission',             'label'=>__('Missions','bluerabbit'),            'icon'=>'mission',    'tab'=>'journey',  'on'=>true],
			['key'=>'steps',               'label'=>__('Steps','bluerabbit'),               'icon'=>'list',       'tab'=>'journey',  'on'=>true],
			['key'=>'tabis',               'label'=>__('Tabis','bluerabbit'),               'icon'=>'tabi',       'tab'=>'tabis',    'on'=>true],
			['key'=>'journey-assets',      'label'=>__('Journey assets','bluerabbit'),      'icon'=>'image',      'tab'=>'journey',  'on'=>true],
			['key'=>'branches',            'label'=>__('Branches','bluerabbit'),            'icon'=>'link',       'tab'=>'branches', 'on'=>true],
			['key'=>'blockers',            'label'=>__('Blockers','bluerabbit'),            'icon'=>'lock',       'tab'=>'blockers', 'on'=>!empty($use_blockers)],
			['key'=>'objectives',          'label'=>__('Objectives','bluerabbit'),          'icon'=>'objectives', 'tab'=>'',         'on'=>true],
		],
	],
	[
		'title' => __('Rewards & economy', 'bluerabbit'),
		'icon'  => 'achievement',
		'rows'  => [
			['key'=>'achievements',    'label'=>__('Achievements','bluerabbit'),   'icon'=>'achievement',  'tab'=>'achievements', 'on'=>!empty($use_achievements)],
			['key'=>'items',           'label'=>__('Items','bluerabbit'),          'icon'=>'basket',       'tab'=>'items',        'on'=>!empty($use_items) || !empty($use_backpack)],
			['key'=>'item-categories', 'label'=>__('Item categories','bluerabbit'),'icon'=>'shop',         'tab'=>'items',        'on'=>!empty($use_items) || !empty($use_backpack)],
			['key'=>'transactions',    'label'=>__('Transactions','bluerabbit'),   'icon'=>'transactions', 'tab'=>'',             'on'=>true],
			['key'=>'encounters',      'label'=>__('Encounters','bluerabbit'),     'icon'=>'battle',       'tab'=>'encounters',   'on'=>!empty($use_encounters)],
		],
	],
	[
		'title' => __('Community & content', 'bluerabbit'),
		'icon'  => 'players',
		'rows'  => [
			['key'=>'guilds',        'label'=>__('Guilds','bluerabbit'),        'icon'=>'guild',      'tab'=>'guilds',   'on'=>!empty($use_guilds)],
			['key'=>'blog-post',     'label'=>__('Blog posts','bluerabbit'),    'icon'=>'document',   'tab'=>'blog',     'on'=>!empty($use_blog)],
			['key'=>'lore',          'label'=>__('Lore','bluerabbit'),          'icon'=>'narrative',  'tab'=>'lore',     'on'=>!empty($use_lore)],
			['key'=>'sessions',      'label'=>__('Schedule sessions','bluerabbit'), 'icon'=>'schedule', 'tab'=>'schedule', 'on'=>!empty($use_schedule)],
			['key'=>'speakers',      'label'=>__('Speakers','bluerabbit'),      'icon'=>'socialiser', 'tab'=>'speakers', 'on'=>!empty($use_speakers)],
			['key'=>'announcements', 'label'=>__('Announcements','bluerabbit'), 'icon'=>'megaphone',  'tab'=>'',         'on'=>true],
			['key'=>'sponsors',      'label'=>__('Sponsors','bluerabbit'),      'icon'=>'star',       'tab'=>'',         'on'=>true],
			['key'=>'requests',      'label'=>__('Requests','bluerabbit'),      'icon'=>'mail',       'tab'=>'requests', 'on'=>true],
		],
	],
];

// Things the GM should look at, built only from numbers already on this page. Each
// entry is [ severity, icon, text ] — severity picks the note treatment.
$dash_flags = [];
$locked_total = 0;
$hidden_total = 0;
$draft_total  = 0;
foreach ($dash_sections as $sec) {
	foreach ($sec['rows'] as $r) {
		if (!isset($inventory[$r['key']])) continue;
		$locked_total += $inventory[$r['key']]['locked'];
		$hidden_total += $inventory[$r['key']]['hidden'];
		$draft_total  += $inventory[$r['key']]['draft'];
	}
}
if ($locked_total > 0) {
	$dash_flags[] = ['alert', 'lock', sprintf(
		/* translators: %d = number of locked content rows */
		_n('%d piece of content is locked. Locked content still counts towards journey and Tabi completion, so players cannot finish while it stays that way.',
		   '%d pieces of content are locked. Locked content still counts towards journey and Tabi completion, so players cannot finish while it stays that way.',
		   $locked_total, 'bluerabbit'), $locked_total)];
}
if ($hidden_total > 0) {
	$dash_flags[] = ['info', 'eye', sprintf(
		_n('%d piece of content is hidden — live, but off the map until something reveals it.',
		   '%d pieces of content are hidden — live, but off the map until something reveals it.',
		   $hidden_total, 'bluerabbit'), $hidden_total)];
}
if ($health['awaiting_review'] > 0) {
	$dash_flags[] = ['alert', 'edit', sprintf(
		_n('%d submission is waiting for validation.', '%d submissions are waiting for validation.',
		   $health['awaiting_review'], 'bluerabbit'), $health['awaiting_review'])];
}
if ($health['open_requests'] > 0) {
	$dash_flags[] = ['alert', 'mail', sprintf(
		_n('%d player request is still open.', '%d player requests are still open.',
		   $health['open_requests'], 'bluerabbit'), $health['open_requests'])];
}
if ($draft_total > 0) {
	$dash_flags[] = ['info', 'document', sprintf(
		_n('%d draft is unpublished and invisible to players.', '%d drafts are unpublished and invisible to players.',
		   $draft_total, 'bluerabbit'), $draft_total)];
}
?>

<div class="br-page br-dashboard">

	<div class="br-dash-kpis">
		<div class="br-dash-kpi">
			<span class="icon icon-players br-dash-kpi-icon"></span>
			<span class="br-dash-kpi-value"><?= number_format_i18n($health['players']); ?></span>
			<span class="br-dash-kpi-label"><?= __('Players enrolled','bluerabbit'); ?></span>
			<span class="br-dash-kpi-note"><?= sprintf(__('%s running the adventure','bluerabbit'), number_format_i18n($health['staff'])); ?></span>
		</div>
		<div class="br-dash-kpi">
			<span class="icon icon-activity br-dash-kpi-icon"></span>
			<span class="br-dash-kpi-value"><?= number_format_i18n($health['active_7d']); ?></span>
			<span class="br-dash-kpi-label"><?= __('Active this week','bluerabbit'); ?></span>
			<span class="br-dash-kpi-note"><?= $health['active_pct']; ?>% <?= __('of players','bluerabbit'); ?></span>
		</div>
		<div class="br-dash-kpi">
			<span class="icon icon-progression br-dash-kpi-icon"></span>
			<span class="br-dash-kpi-value"><?= $health['completion_pct']; ?>%</span>
			<span class="br-dash-kpi-label"><?= __('Average completion','bluerabbit'); ?></span>
			<span class="br-dash-kpi-note"><?= sprintf(__('%s required milestones each','bluerabbit'), number_format_i18n($health['required'])); ?></span>
		</div>
		<div class="br-dash-kpi<?= $health['awaiting_review'] > 0 ? ' br-dash-kpi-alert' : ''; ?>">
			<span class="icon icon-edit br-dash-kpi-icon"></span>
			<span class="br-dash-kpi-value"><?= number_format_i18n($health['awaiting_review']); ?></span>
			<span class="br-dash-kpi-label"><?= __('Awaiting validation','bluerabbit'); ?></span>
			<span class="br-dash-kpi-note"><?= __('Submitted, never reviewed','bluerabbit'); ?></span>
		</div>
		<div class="br-dash-kpi<?= $health['open_requests'] > 0 ? ' br-dash-kpi-alert' : ''; ?>">
			<span class="icon icon-mail br-dash-kpi-icon"></span>
			<span class="br-dash-kpi-value"><?= number_format_i18n($health['open_requests']); ?></span>
			<span class="br-dash-kpi-label"><?= __('Open requests','bluerabbit'); ?></span>
			<span class="br-dash-kpi-note"><a href="<?= $dash_base; ?>requests"><?= __('Go to Requests','bluerabbit'); ?></a></span>
		</div>
	</div>

	<?php if ($dash_flags) { ?>
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-warning"></span> <?= __('Worth a look','bluerabbit'); ?></h3>
		<?php foreach ($dash_flags as $flag) { ?>
			<div class="br-panel-note<?= $flag[0] === 'alert' ? ' br-panel-note-alert' : ''; ?>">
				<span class="icon icon-<?= $flag[1]; ?>"></span>
				<span><?= $flag[2]; ?></span>
			</div>
		<?php } ?>
	</div>
	<?php } ?>

	<?php foreach ($dash_sections as $section) {
		// Drop a whole section when nothing in it exists and none of its features are on.
		$section_rows = [];
		foreach ($section['rows'] as $row) {
			$data = $inventory[$row['key']] ?? null;
			$total = $data ? $data['total'] : 0;
			if (!$total && !$row['on']) continue;
			$row['data']  = $data;
			$row['total'] = $total;
			$section_rows[] = $row;
		}
		if (!$section_rows) continue;
	?>
	<div class="br-panel">
		<h3 class="br-panel-title"><span class="icon icon-<?= $section['icon']; ?>"></span> <?= $section['title']; ?></h3>
		<div class="br-table-scroll">
			<table class="br-table br-dash-table">
				<thead>
					<tr>
						<th><?= __('Content','bluerabbit'); ?></th>
						<th class="text-center"><?= __('Live','bluerabbit'); ?></th>
						<th class="text-center"><?= __('Locked','bluerabbit'); ?></th>
						<th class="text-center"><?= __('Hidden','bluerabbit'); ?></th>
						<th class="text-center"><?= __('Draft','bluerabbit'); ?></th>
						<th class="text-center"><?= __('Trash','bluerabbit'); ?></th>
						<th class="text-right"><?= __('Total','bluerabbit'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($section_rows as $row) {
					$d = $row['data'];
					// A row can exist with a vocabulary of its own (requests are pending or
					// resolved, never published). Those land in 'other' and are spelled out
					// under the label rather than forced into a column that would lie.
					$other_detail = '';
					if ($d && $d['other'] > 0) {
						$parts = [];
						foreach ($d['statuses'] as $raw => $n) {
							if (in_array($raw, ['publish','locked','hidden','draft','trash'], true)) continue;
							$parts[] = esc_html($raw) . ' ' . number_format_i18n($n);
						}
						$other_detail = implode(' · ', $parts);
					}
				?>
					<tr>
						<td>
							<span class="br-dash-row-label">
								<span class="icon icon-<?= $row['icon']; ?>"></span>
								<?php if ($row['tab']) { ?>
									<a href="<?= $dash_base . $row['tab']; ?>"><?= $row['label']; ?></a>
								<?php } else { ?>
									<?= $row['label']; ?>
								<?php } ?>
								<?php if (!$row['on']) { ?>
									<span class="br-badge br-badge-amber" title="<?= esc_attr__('This mechanic is switched off for the adventure, but the content is still here.','bluerabbit'); ?>"><?= __('Off','bluerabbit'); ?></span>
								<?php } ?>
							</span>
							<?php if ($other_detail) { ?>
								<span class="br-dash-row-detail"><?= $other_detail; ?></span>
							<?php } ?>
						</td>
						<td class="text-center"><span class="br-dash-num<?= $d && $d['publish'] ? '' : ' br-dash-num-zero'; ?>"><?= $d ? number_format_i18n($d['publish']) : 0; ?></span></td>
						<td class="text-center"><span class="br-dash-num<?= $d && $d['locked'] ? ' br-dash-num-locked' : ' br-dash-num-zero'; ?>"><?= $d ? number_format_i18n($d['locked']) : 0; ?></span></td>
						<td class="text-center"><span class="br-dash-num<?= $d && $d['hidden'] ? ' br-dash-num-hidden' : ' br-dash-num-zero'; ?>"><?= $d ? number_format_i18n($d['hidden']) : 0; ?></span></td>
						<td class="text-center"><span class="br-dash-num<?= $d && $d['draft'] ? ' br-dash-num-draft' : ' br-dash-num-zero'; ?>"><?= $d ? number_format_i18n($d['draft']) : 0; ?></span></td>
						<td class="text-center"><span class="br-dash-num<?= $d && $d['trash'] ? '' : ' br-dash-num-zero'; ?>"><?= $d ? number_format_i18n($d['trash']) : 0; ?></span></td>
						<td class="text-right"><span class="br-dash-num br-dash-num-total"><?= number_format_i18n($row['total']); ?></span></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php } ?>

	<p class="br-dash-footnote">
		<?= __('Live, Locked and Hidden all count towards journey and Tabi completion — a player has to finish them. Draft and Trash do not.','bluerabbit'); ?>
	</p>

</div>

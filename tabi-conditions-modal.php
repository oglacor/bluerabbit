<div class="tabi-conditions-header">
    <h3 class="br-text-16 w700"><?= sprintf(__('Conditions for %s', 'bluerabbit'), esc_html($tabi->tabi_name)); ?></h3>
    <button class="br-close-btn" onclick="closeTabiConditionsModal(<?= (int) $tabi->tabi_id; ?>);">
        <span class="icon icon-cancel white-color"></span>
    </button>
</div>

<div class="tabi-conditions-body" data-tabi-id="<?= (int) $tabi->tabi_id; ?>" data-adventure-id="<?= (int) $adventure_id; ?>">

    <?php
    // ── Membership ──────────────────────────────────────────────────────
    // Which milestones live in this tabi. A milestone carries a single
    // tabi_id, so ticking one here takes it out of whatever tabi it was in -
    // that is spelled out on the row rather than left to be discovered after
    // the fact. Saved on the spot via setQuestTabi(), not with the Save button
    // below, because it writes br_quests while everything else here writes
    // conditions - one button that saved both would be lying about one of them.
    $in_this_tabi = 0;
    foreach ($quests as $q) { if ((int) $q->tabi_id === (int) $tabi->tabi_id) $in_this_tabi++; }
    ?>
    <div class="tabi-conditions-section">
        <h4 class="tabi-conditions-section-title">
            <span class="icon icon-tabi"></span>
            <?= __('Milestones in this Tabi', 'bluerabbit'); ?>
            <span class="br-badge br-badge-blue" id="tabi-member-count-<?= (int) $tabi->tabi_id; ?>"><?= $in_this_tabi; ?></span>
        </h4>
        <span class="tabi-conditions-hint"><?= __('Tick to move a milestone into this tabi, untick to take it out. Saved immediately.', 'bluerabbit'); ?></span>
        <div class="tabi-member-list">
            <?php if ($quests) { foreach ($quests as $q) {
                $is_mine = ((int) $q->tabi_id === (int) $tabi->tabi_id);
                $other   = (!$is_mine && (int) $q->tabi_id > 0 && isset($tabi_names[(int) $q->tabi_id]))
                    ? $tabi_names[(int) $q->tabi_id]->tabi_name : '';
            ?>
                <label class="tabi-member-row<?= $is_mine ? ' is-member' : ''; ?>"
                       id="tabi-member-<?= (int) $tabi->tabi_id; ?>-<?= (int) $q->quest_id; ?>">
                    <input type="checkbox" class="tabi-member-checkbox" <?= $is_mine ? 'checked' : ''; ?>
                           onchange="toggleTabiMembership(<?= (int) $tabi->tabi_id; ?>, <?= (int) $q->quest_id; ?>, '<?= esc_js($q->quest_type); ?>', this);">
                    <span class="tabi-member-check"><span class="icon icon-check"></span></span>
                    <span class="tabi-member-name"><?= esc_html($q->quest_title); ?></span>
                    <span class="tabi-member-tags">
                        <span class="tabi-member-type"><?= esc_html($q->quest_type); ?></span>
                        <?php if ($other) { ?>
                            <span class="tabi-member-elsewhere" title="<?= esc_attr__('Ticking this moves it out of that tabi', 'bluerabbit'); ?>">
                                <span class="icon icon-tabi"></span> <?= esc_html($other); ?>
                            </span>
                        <?php } ?>
                    </span>
                </label>
            <?php } } else { ?>
                <span class="tabi-conditions-empty"><?= __('No milestones in this adventure.', 'bluerabbit'); ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="tabi-conditions-section">
        <h4 class="tabi-conditions-section-title">
            <span class="icon icon-lock"></span>
            <?= __('Requires these milestones completed', 'bluerabbit'); ?>
        </h4>
        <div class="tabi-prereq-list">
            <?php if ($quests) { foreach ($quests as $q) { ?>
                <label class="tabi-prereq-label blue-bg-100">
                    <input type="checkbox" class="tabi-cond-quest-checkbox" value="<?= (int) $q->quest_id; ?>"
                           <?= in_array((int) $q->quest_id, $tabi_reqs['quests'] ?? []) ? 'checked' : ''; ?>>
                    <?= esc_html($q->quest_title); ?>
                </label>
            <?php } } else { ?>
                <span class="tabi-conditions-empty"><?= __('No milestones in this adventure.', 'bluerabbit'); ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="tabi-conditions-section">
        <h4 class="tabi-conditions-section-title">
            <span class="icon icon-achievement"></span>
            <?= __('Requires these achievements', 'bluerabbit'); ?>
        </h4>
        <div class="tabi-prereq-list">
            <?php if ($achievements) { foreach ($achievements as $a) { ?>
                <label class="tabi-prereq-label purple-bg-100">
                    <input type="checkbox" class="tabi-cond-achievement-checkbox" value="<?= (int) $a->achievement_id; ?>"
                           <?= in_array((int) $a->achievement_id, $tabi_reqs['achievements'] ?? []) ? 'checked' : ''; ?>>
                    <?= esc_html($a->achievement_name); ?>
                </label>
            <?php } } else { ?>
                <span class="tabi-conditions-empty"><?= __('No achievements in this adventure.', 'bluerabbit'); ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="tabi-conditions-section">
        <h4 class="tabi-conditions-section-title">
            <span class="icon icon-backpack"></span>
            <?= __('Requires this key item', 'bluerabbit'); ?>
        </h4>
        <select class="form-ui tabi-cond-item-select">
            <option value="0"><?= __('None', 'bluerabbit'); ?></option>
            <?php $current_item_id = !empty($tabi_reqs['items']) ? (int) $tabi_reqs['items'][0] : 0; ?>
            <?php if ($key_items) { foreach ($key_items as $i) { ?>
                <option value="<?= (int) $i->item_id; ?>" <?= $current_item_id === (int) $i->item_id ? 'selected' : ''; ?>>
                    <?= esc_html($i->item_name); ?>
                </option>
            <?php } } ?>
        </select>
    </div>

    <div class="tabi-conditions-section">
        <h4 class="tabi-conditions-section-title">
            <span class="icon icon-progression"></span>
            <?= __('Threshold conditions', 'bluerabbit'); ?>
        </h4>
        <span class="tabi-conditions-hint"><?= __('Leave blank to skip.', 'bluerabbit'); ?></span>
        <?php foreach (BR_Conditions::simpleTypes() as $type => $label) { ?>
            <div class="input-group tabi-cond-threshold-row">
                <label><?= esc_html__($label, 'bluerabbit'); ?></label>
                <input type="number" class="form-ui tabi-cond-threshold-input" data-condition-type="<?= esc_attr($type); ?>"
                       value="<?= esc_attr($condition_values[$type] ?? ''); ?>" min="0" step="0.01">
            </div>
        <?php } ?>
    </div>

    <input type="hidden" class="tabi-conditions-nonce" value="<?= $tabi_conditions_nonce; ?>">
    <input type="hidden" class="tabi-quest-tabi-nonce" value="<?= $quest_tabi_nonce; ?>">
</div>

<div class="tabi-conditions-footer">
    <button class="br-btn br-btn-blue br-btn-submit" onclick="saveTabiConditionsModal(<?= (int) $tabi->tabi_id; ?>);">
        <span class="icon icon-check"></span> <?= __('Save Conditions', 'bluerabbit'); ?>
    </button>
</div>

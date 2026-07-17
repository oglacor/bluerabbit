<div class="tabi-conditions-header">
    <h3 class="br-text-16 w700"><?= sprintf(__('Conditions for %s', 'bluerabbit'), esc_html($quest->quest_title)); ?></h3>
    <button class="br-close-btn" onclick="closeQuestConditionsModal();">
        <span class="icon icon-cancel white-color"></span>
    </button>
</div>

<div class="tabi-conditions-body" data-adventure-id="<?= (int) $adventure_id; ?>">

    <div class="tabi-conditions-section">
        <span class="br-text-12 block grey-500"><?= __('Requires these milestones completed:', 'bluerabbit'); ?></span>
        <div class="tabi-prereq-list">
            <?php if ($quests) { foreach ($quests as $q) { ?>
                <label class="tabi-prereq-label blue-bg-100">
                    <input type="checkbox" class="tabi-cond-quest-checkbox" value="<?= (int) $q->quest_id; ?>"
                           <?= in_array((int) $q->quest_id, $quest_reqs['quests'] ?? []) ? 'checked' : ''; ?>>
                    <?= esc_html($q->quest_title); ?>
                </label>
            <?php } } else { ?>
                <span class="br-text-12 grey-400"><?= __('No other milestones in this adventure.', 'bluerabbit'); ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="tabi-conditions-section">
        <span class="br-text-12 block grey-500"><?= __('Requires these achievements:', 'bluerabbit'); ?></span>
        <div class="tabi-prereq-list">
            <?php if ($achievements) { foreach ($achievements as $a) { ?>
                <label class="tabi-prereq-label purple-bg-100">
                    <input type="checkbox" class="tabi-cond-achievement-checkbox" value="<?= (int) $a->achievement_id; ?>"
                           <?= in_array((int) $a->achievement_id, $quest_reqs['achievements'] ?? []) ? 'checked' : ''; ?>>
                    <?= esc_html($a->achievement_name); ?>
                </label>
            <?php } } else { ?>
                <span class="br-text-12 grey-400"><?= __('No achievements in this adventure.', 'bluerabbit'); ?></span>
            <?php } ?>
        </div>
    </div>

    <div class="tabi-conditions-section">
        <span class="br-text-12 block grey-500"><?= __('Requires this key item:', 'bluerabbit'); ?></span>
        <select class="form-ui tabi-cond-item-select">
            <option value="0"><?= __('None', 'bluerabbit'); ?></option>
            <?php $current_item_id = !empty($quest_reqs['items']) ? (int) $quest_reqs['items'][0] : 0; ?>
            <?php if ($key_items) { foreach ($key_items as $i) { ?>
                <option value="<?= (int) $i->item_id; ?>" <?= $current_item_id === (int) $i->item_id ? 'selected' : ''; ?>>
                    <?= esc_html($i->item_name); ?>
                </option>
            <?php } } ?>
        </select>
    </div>

    <div class="tabi-conditions-section">
        <span class="br-text-12 block grey-500"><?= __('Threshold conditions (leave blank to skip):', 'bluerabbit'); ?></span>
        <?php foreach (BR_Conditions::CONDITION_TYPES as $type => $label) { ?>
            <div class="input-group tabi-cond-threshold-row">
                <label class="br-text-12 grey-600"><?= esc_html__($label, 'bluerabbit'); ?></label>
                <input type="number" class="form-ui tabi-cond-threshold-input" data-condition-type="<?= esc_attr($type); ?>"
                       value="<?= esc_attr($condition_values[$type] ?? ''); ?>" min="0" step="0.01">
            </div>
        <?php } ?>
    </div>

    <input type="hidden" class="quest-conditions-nonce" value="<?= $quest_conditions_nonce; ?>">
</div>

<div class="tabi-conditions-footer">
    <button class="br-btn br-btn-blue br-btn-submit" onclick="saveQuestConditionsModal(<?= (int) $quest->quest_id; ?>);">
        <span class="icon icon-check"></span> <?= __('Save Conditions', 'bluerabbit'); ?>
    </button>
</div>

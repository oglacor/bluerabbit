<?php
$lb_guild_level = 1;
$lb_added = 0;
$average_xp = round($lg->total_player_xp / $lg->guild_current_capacity);

for($ll=1;$ll<30;$ll++){
    $lb_added += $ll*1000;
    if(($lb_added-1) < $average_xp){
        $lb_guild_level = $ll+1;
    }
}
$is_my_guild_row = isset($user_guild_id) && $lg->guild_id == $user_guild_id;
?>
<li class="card<?= $is_my_guild_row ? ' is-my-guild' : ''; ?>" id="guild-lb-<?= $lg->guild_id; ?>">
    <div class="guild-rank"><?= $loop_index + 1; ?></div>
    <div class="guild-lb-badge">
        <img src="<?= $lg->guild_logo; ?>" class="badge">
    </div>
    <div class="guild-lb-info">
        <div class="guild-lb-name"><?= $lg->guild_name; ?></div>
        <div class="guild-lb-stats">
            <span class="guild-lb-stat">
                <span class="stat-label"><?= __("Members","bluerabbit"); ?>:</span>
                <span class="stat-value teal"><?= $lg->guild_current_capacity; ?></span>
            </span>
            <span class="guild-lb-stat">
                <span class="stat-label"><?= __("Level","bluerabbit"); ?>:</span>
                <span class="stat-value"><?= $lb_guild_level; ?></span>
            </span>
            <span class="guild-lb-stat">
                <span class="stat-label"><?= __("XP","bluerabbit"); ?>:</span>
                <span class="stat-value blue" id="guild-lb-xp-<?= $lg->guild_id; ?>">
                    <span class="number">0</span>
                    <input type="hidden" class="end-value" value="<?= $lg->total_player_xp; ?>">
                </span>
            </span>
            <span class="guild-lb-stat">
                <span class="stat-label"><?= __("VC","bluerabbit"); ?>:</span>
                <span class="stat-value yellow">$<?= number_format($lg->total_player_bloo); ?></span>
            </span>
        </div>
    </div>
    <script>animateNumber('#guild-lb-xp-<?= $lg->guild_id; ?>');</script>
</li>

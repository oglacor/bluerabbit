<?php
// Top-5 leaderboard card - $lg / $loop_index come from page-guilds.php's
// foreach over $top_guilds. Styled like the My Guild card (guild.php) per
// the client's request that the top 5 "look like the My Guild column".
$lb_guild_level = 1;
$lb_added = 0;
$average_xp = $lg->guild_current_capacity > 0 ? round($lg->total_player_xp / $lg->guild_current_capacity) : 0;

for($ll=1;$ll<30;$ll++){
    $lb_added += $ll*1000;
    if(($lb_added-1) < $average_xp){
        $lb_guild_level = $ll+1;
    }
}
$is_my_guild_row = isset($user_guild_id) && $lg->guild_id == $user_guild_id;
$can_view_roster = $isGM || $isAdmin || $isNPC || $is_my_guild_row;
?>
<li class="my-guild-card guild-lb-card<?= $can_view_roster ? ' guild-lb-clickable' : ''; ?><?= $is_my_guild_row ? ' is-my-guild' : ''; ?>" id="guild-lb-<?= $lg->guild_id; ?>" <?= $can_view_roster ? 'onclick="openGuildRoster(' . $lg->guild_id . ');"' : ''; ?>>
    <div class="guild-lb-rank-ribbon"><?= $loop_index + 1; ?></div>
    <div class="my-guild-card-bg">
        <svg class="my-guild-card-border" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 802 502">
            <path d="M777,501H25l-24-24V25L25,1h752l24,24v452l-24,24Z"/>
        </svg>
    </div>
    <div class="my-guild-card-content">
        <div class="my-guild-card-info">
            <h2 class="guild-name"><?= esc_html($lg->guild_name); ?></h2>
            <div class="guild-members-box">
                <span class="guild-members-count"><?= $lg->guild_current_capacity; ?></span>
                <span class="guild-members-label"><?= __("Members","bluerabbit"); ?></span>
            </div>
            <div class="guild-stats-list">
                <div class="guild-stat">
                    <span class="guild-stat-label"><?= __("Level","bluerabbit"); ?></span>
                    <span class="guild-stat-value level"><?= $lb_guild_level; ?></span>
                </div>
                <div class="guild-stat">
                    <span class="guild-stat-label"><?= __("XP","bluerabbit"); ?></span>
                    <span class="guild-stat-value xp" id="guild-lb-xp-<?= $lg->guild_id; ?>">
                        <span class="number">0</span>
                        <input type="hidden" class="end-value" value="<?= (int) $lg->total_player_xp; ?>">
                    </span>
                </div>
                <div class="guild-stat">
                    <span class="guild-stat-label"><?= __("VC","bluerabbit"); ?></span>
                    <span class="guild-stat-value bloo">$<?= number_format((int) $lg->total_player_bloo); ?></span>
                </div>
            </div>
        </div>
        <div class="my-guild-card-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 440 440" class="guild-badge">
                <defs>
                    <clipPath id="hexClip-lb-<?= $lg->guild_id; ?>">
                        <polygon points="366.89 305.42 366.89 135.8 220 50.99 73.11 135.8 73.11 305.42 220 390.22 366.89 305.42"/>
                    </clipPath>
                </defs>
                <polygon class="hexagon-overlay" points="380.7 312.78 380.7 127.22 220 34.44 59.3 127.22 59.3 312.78 220 405.56 380.7 312.78"/>
                <polygon class="hexagon-overlay" points="373.06 308.37 373.06 131.63 220 43.26 66.94 131.63 66.94 308.37 220 396.74 373.06 308.37"/>
                <polygon class="hexagon-yellow-border" points="395.5 321.32 395.5 118.68 220 17.35 44.5 118.68 44.5 321.32 220 422.65 395.5 321.32"/>
                <polygon class="hexagon-yellow-details" points="395.5 321.32 395.5 118.68 220 17.35 44.5 118.68 44.5 321.32 220 422.65 395.5 321.32"/>
                <image
                    href="<?= esc_url($lg->guild_logo); ?>"
                    width="440"
                    height="440"
                    clip-path="url(#hexClip-lb-<?= $lg->guild_id; ?>)"
                    preserveAspectRatio="xMidYMid slice"
                    filter="url(#softShadow)"
                />
                <polygon class="hexagon-white-line-overlay" points="366.89 305.42 366.89 135.8 220 50.99 73.11 135.8 73.11 305.42 220 390.22 366.89 305.42"/>
                <polygon class="hexagon-white-line-overlay" points="356.03 298.53 356.03 141.47 220 62.93 83.98 141.47 83.98 298.53 220 377.07 356.03 298.53"/>
            </svg>
        </div>
    </div>
</li>
<script>animateNumber('#guild-lb-xp-<?= $lg->guild_id; ?>');</script>

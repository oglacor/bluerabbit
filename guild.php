        <div class="my-guild-card">
            <div class="my-guild-card-bg">
                <svg class="my-guild-card-border" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 802 502">
                    <path d="M777,501H25l-24-24V25L25,1h752l24,24v452l-24,24Z"/>
                </svg>
            </div>
            <div class="my-guild-card-content">
                <div class="my-guild-card-info">
                    <h2 class="guild-name">
                        <?= $g->guild_name; ?>
                    </h2>
                    <div class="guild-members-box">
                        <span class="guild-members-count"><?= $g->guild_current_capacity; ?></span>
                        <span class="guild-members-label"><?= __("Members","bluerabbit"); ?></span>
                    </div>
                    <?php
                    $guild_level = 1;
                    $added = 0;
                    for($l=1;$l<30;$l++){
                        $added += $l*1000;
                        if(($added-1) < $g->guild_xp){
                            $guild_level = $l+1;
                        }
                    }
                    ?>
                    <div class="guild-stats-list">
                        <div class="guild-stat">
                            <span class="guild-stat-label"><?= __("Level","bluerabbit"); ?></span>
                            <span class="guild-stat-value level"><?= $guild_level; ?></span>
                        </div>
                        <div class="guild-stat">
                            <span class="guild-stat-label"><?= $xp_long_label; ?></span>
                            <span class="guild-stat-value xp"><?= number_format($leaderboard_guilds_array[$g->guild_id] ?? $g->guild_xp); ?></span>
                        </div>
                        <div class="guild-stat">
                            <span class="guild-stat-label"><?= $bloo_long_label; ?></span>
                            <span class="guild-stat-value bloo">$<?= number_format($leaderboard_bloo_array[$g->guild_id] ?? $g->guild_bloo); ?></span>
                        </div>
                    </div>
                    <?php $this_guild_rank = isset($guild_rank_map) ? ($guild_rank_map[$g->guild_id] ?? 0) : $guild_rank; ?>
                    <?php if(!empty($this_guild_rank)){ ?>
                    <div class="guild-position-box">
                        <span class="guild-position-number"><?= $this_guild_rank; ?></span>
                        <span class="guild-position-label"><?= __("Current Position","bluerabbit"); ?></span>
                    </div>
                    <?php } ?>
                </div>
                <div class="my-guild-card-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 440 440" class="guild-badge">
                        <defs>
                            <clipPath id="hexClip">
                                <polygon points="366.89 305.42 366.89 135.8 220 50.99 73.11 135.8 73.11 305.42 220 390.22 366.89 305.42"/>
                            </clipPath>
                        </defs>
                        <polygon class="hexagon-overlay" points="380.7 312.78 380.7 127.22 220 34.44 59.3 127.22 59.3 312.78 220 405.56 380.7 312.78"/>
                        <polygon class="hexagon-overlay" points="373.06 308.37 373.06 131.63 220 43.26 66.94 131.63 66.94 308.37 220 396.74 373.06 308.37"/>
                        <polygon class="hexagon-yellow-border" points="395.5 321.32 395.5 118.68 220 17.35 44.5 118.68 44.5 321.32 220 422.65 395.5 321.32"/>
                        <polygon class="hexagon-yellow-details" points="395.5 321.32 395.5 118.68 220 17.35 44.5 118.68 44.5 321.32 220 422.65 395.5 321.32"/>
                        <image
                            href="<?= $g->guild_logo;?>"
                            width="440"
                            height="440"
                            clip-path="url(#hexClip)"
                            preserveAspectRatio="xMidYMid slice"
                            filter="url(#softShadow)"
                        />
                        <polygon class="hexagon-white-line-overlay" points="366.89 305.42 366.89 135.8 220 50.99 73.11 135.8 73.11 305.42 220 390.22 366.89 305.42"/>
                        <polygon class="hexagon-white-line-overlay" points="356.03 298.53 356.03 141.47 220 62.93 83.98 141.47 83.98 298.53 220 377.07 356.03 298.53"/>
                    </svg>
                </div>
            </div>
        </div>

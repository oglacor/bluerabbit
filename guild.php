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
                    <table class="my-guild-card-stats">
                        <tbody>
                            <tr class="teal">
                                <td><?= __("Members"); ?>:</td>
                                <?php
                                $guild_members = $wpdb->get_results("SELECT COUNT(*) as count FROM {$wpdb->prefix}br_player_guild WHERE guild_id = $g->guild_id");
                                ?>
                                <td><?= count($guild_members); ?></td>
                            </tr>
                            <tr class="white">
                                <td><?= __("Level"); ?>:</td>
                                <td>
                                    <?php 
                                    $guild_level = 1;
                                    for($l=1;$l<1000;$l++){
                                        $added += $l*1000;
                                        if(($added-1) < $g->guild_xp){
                                            $guild_level = $l+1;
                                        }
                                    }
                                    echo $guild_level;
                                    ?>
                                </td>
                            </tr>
                            <tr class="blue">
                                <td><?= $xp_long_label; ?>:</td>
                                <td><?= $leaderboard_guilds_array[$g->guild_id]; ?></td>
                            </tr>
                            <tr class="yellow">
                                <td><?= $bloo_long_label; ?>:</td>
                                <td><?= $g->guild_bloo; ?></td>
                            </tr>
                        </tbody>
                    </table>
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
        


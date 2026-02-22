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
                                <td><?= $xp_label; ?>:</td>
                                <td><?= $leaderboard_guilds_array[$g->guild_id]; ?></td>
                            </tr>
                            <tr class="yellow">
                                <td><?= $bloo_label; ?>:</td>
                                <td><?= $g->guild_bloo; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="my-guild-card-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 322.12 367.54">
                        <polygon class="hexagon-overlay" points="305.91 267.4 305.91 100.14 161.06 16.51 16.21 100.14 16.21 267.4 161.06 351.03 305.91 267.4"/>
                        <polygon class="hexagon-yellow-border" points="319.25 275.1 319.25 92.44 161.06 1.1 2.87 92.44 2.87 275.1 161.06 366.43 319.25 275.1"/>
                        <polygon class="hexagon-yellow-details" points="319.25 275.1 319.25 92.44 161.06 1.1 2.87 92.44 2.87 275.1 161.06 366.43 319.25 275.1"/>
                        <polygon class="hexagon-overlay" points="299.03 263.42 299.03 104.11 161.06 24.46 23.09 104.11 23.09 263.42 161.06 343.08 299.03 263.42"/>
                        <path class="hexagon-white-line-overlay" d="M161.06,32.54l130.97,75.62v151.23l-130.97,75.62L30.09,259.39V108.15L161.06,32.54M161.06,30.88L28.66,107.32v152.89l132.41,76.44,132.41-76.44V107.32L161.06,30.88h0Z"/>
                        <polygon class="hexagon-white-line-overlay" points="289.4 257.86 289.4 109.68 161.06 35.58 32.73 109.68 32.73 257.86 161.06 331.95 289.4 257.86"/>
                        <polygon class="hexagon-white-line-overlay" id="guild-<?=$g->guild_id;?>-mask" points="289.4 257.86 289.4 109.68 161.06 35.58 32.73 109.68 32.73 257.86 161.06 331.95 289.4 257.86"/>
                    </svg>                    
                </div>
            </div>
        </div>
        
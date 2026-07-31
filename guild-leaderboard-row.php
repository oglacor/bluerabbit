<?php
// Compact leaderboard table row for guilds ranked 6th and below (paginated) -
// $lg / $loop_index come from page-guilds.php's foreach over $rest_guilds_page.
$row_guild_level = 1;
$row_added = 0;
$row_avg_xp = $lg->guild_current_capacity > 0 ? round($lg->total_player_xp / $lg->guild_current_capacity) : 0;
for($rl=1;$rl<30;$rl++){
    $row_added += $rl*1000;
    if(($row_added-1) < $row_avg_xp){
        $row_guild_level = $rl+1;
    }
}
$is_my_guild_row = isset($user_guild_id) && $lg->guild_id == $user_guild_id;
$can_view_roster = $isGM || $isAdmin || $isNPC || $is_my_guild_row;
?>
<tr class="<?= $can_view_roster ? 'guild-lb-clickable' : ''; ?><?= $is_my_guild_row ? ' is-my-guild' : ''; ?>" id="guild-lb-<?= $lg->guild_id; ?>" <?= $can_view_roster ? 'onclick="openGuildRoster(' . $lg->guild_id . ');"' : ''; ?>>
    <td class="text-center guild-lb-table-rank"><?= $loop_index + 1; ?></td>
    <td class="guild-lb-table-badge"><img src="<?= esc_url($lg->guild_logo); ?>" class="badge" alt=""></td>
    <td class="guild-lb-table-name"><?= esc_html($lg->guild_name); ?></td>
    <td class="text-center"><?= (int) $lg->guild_current_capacity; ?></td>
    <td class="text-center"><?= $row_guild_level; ?></td>
    <td class="text-center"><?= number_format((int) $lg->total_player_xp); ?></td>
    <td class="text-center">$<?= number_format((int) $lg->total_player_bloo); ?></td>
</tr>

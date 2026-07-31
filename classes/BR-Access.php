<?php
/**
 * BR_Access — who is allowed to do what inside an adventure.
 *
 * The three per-adventure roles (player_adventure_role) are:
 *
 *   gm     full control: edits content, manages players, sends mail
 *   npc    "surveillance" - sees everything a GM sees and plays the adventure
 *          like any other player, but never edits content, never adds or
 *          removes users, never sends mail
 *   player plays the adventure
 *
 * Hiding a nav link is not access control, so the NPC restriction is enforced
 * at the one place every write actually passes through: admin-ajax.php. The
 * gate below runs before any handler and rejects an NPC unless the requested
 * action is on the allowlist of things NPCs legitimately need - the read-only
 * views plus everything required to actually play. An allowlist is used rather
 * than a denylist because there are 200+ registered actions and a forgotten
 * entry must fail closed (an NPC sees a clear "view-only" message) rather than
 * silently granting write access.
 */
class BR_Access {

    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {}

    /**
     * Actions an NPC may run. Everything not listed here is refused for NPCs.
     * Admins, owners and GMs are never affected by this list.
     */
    const NPC_ALLOWED = [

        // ── Session, chrome and read-only renderers ──────────────────────
        'br_logout', 'br_notify', 'br_session_ping', 'br_dismiss_tutorial',
        'closeIntro', 'loadContent', 'loadChat', 'loadStory', 'loadLore', 'searchLore',
        'loadQuestCard', 'loadItemCard', 'loadAchievementCard', 'loadBackpackItem',
        'loadGuildCard', 'displayAchievementCard', 'loadStepButtonForm',
        'insertItemConditionsModal', 'insertQuestConditionsModal', 'insertTabiConditionsModal',

        // ── Playing the adventure ────────────────────────────────────────
        // An NPC is a full player: they progress, submit work, spend and earn.
        'br_complete_step', 'br_player_branch_choice', 'br_casestudy_complete',
        'br_casestudy_progress', 'br_scorm_save_data', 'br_ai_validate_text',
        'submitPlayerWork', 'submitAnswer', 'submitSurveyAnswer', 'startAttempt',
        'answerEncounter', 'randomEncounter', 'failQuest', 'choosePath', 'setCurrentQuest',
        'getObjectives', 'insertSolvedObjective', 'resetQuestObjectives',
        'rateQuest', 'magicCode', 'postToWall', 'submitRequest', 'getMyRequests', 'switchRank',
        'buyItem', 'useItem', 'pickupItem', 'spendEP', 'payBlocker', 'payment', 'checkItem',
        // NOTE: triggerAchievement / triggerAchievements / triggerGuild are deliberately
        // NOT here. They read player_id straight from the request, so they are the GM's
        // grant-and-revoke tools (page-assign-achievement, player-select-achievement,
        // page-new-guild), not gameplay. An NPC with those could award achievements to
        // anyone and move players between guilds. Achievements earned by actually
        // playing are granted server-side inside step completion, which never passes
        // through admin-ajax and so is unaffected.

        // ── Their own profile ────────────────────────────────────────────
        'updateProfile', 'setProfilePicture', 'setNickname', 'newHexad',

        // ── Surveillance: read-only views and exports ────────────────────
        'br_stats_player_panel', 'br_stats_search_players', 'br_stats_xp_distribution',
        'br_stats_xp_history', 'br_stats_quest_funnel', 'br_stats_activity_heatmap',
        'br_stats_time_in_app', 'br_stats_segment_breakdown', 'br_stats_item_breakdown',
        'br_stats_item_detail', 'br_stats_achievement_breakdown', 'br_stats_achievement_detail',
        'br_guild_roster', 'getRequests',
        'exportPlayerPostsCSV', 'exportPlayersWork',
        'br_meta_search_players', 'br_meta_preview_csv', 'br_meta_export_csv',

        // ── The one thing an NPC may change ──────────────────────────────
        'setGuildLeader',
    ];

    /**
     * Per-adventure role for a user: 'gm' (includes the owner), 'npc', 'player',
     * or '' when they are not enrolled. Administrators are always 'gm'.
     */
    public function adventureRole($adventure_id, $user_id = null) {
        global $wpdb;
        $adventure_id = intval($adventure_id);
        $user_id      = $user_id ? intval($user_id) : get_current_user_id();
        if (!$user_id) return '';

        $user = get_userdata($user_id);
        if ($user && in_array('administrator', (array)$user->roles)) return 'gm';
        if (!$adventure_id) return '';

        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT adventure_owner FROM {$wpdb->prefix}br_adventures WHERE adventure_id=%d", $adventure_id
        ));
        if ($owner && $owner == $user_id) return 'gm';

        $role = $wpdb->get_var($wpdb->prepare(
            "SELECT player_adventure_role FROM {$wpdb->prefix}br_player_adventure
             WHERE adventure_id=%d AND player_id=%d AND player_adventure_status='in'",
            $adventure_id, $user_id
        ));
        return $role ? $role : '';
    }

    public function isNpc($adventure_id, $user_id = null) {
        return $this->adventureRole($adventure_id, $user_id) === 'npc';
    }

    /** May edit content / manage players / send mail in this adventure. */
    public function canEdit($adventure_id, $user_id = null) {
        return $this->adventureRole($adventure_id, $user_id) === 'gm';
    }

    /** May see the admin/surveillance pages (GM or NPC). */
    public function canViewAdmin($adventure_id, $user_id = null) {
        return in_array($this->adventureRole($adventure_id, $user_id), ['gm','npc'], true);
    }

    /**
     * Decides whether the *current request* is being made by an NPC.
     *
     * When the request names an adventure the per-adventure role decides, which
     * is what makes it possible to be a GM in one adventure and an NPC in
     * another.
     *
     * With no adventure named there is nothing to scope to, and simply allowing
     * the request would hand every NPC a trivial bypass: drop adventure_id from
     * the payload and the gate stops applying. So the no-adventure case fails
     * closed - anyone holding an NPC seat anywhere (or the account-level br_npc
     * role) is treated as an NPC. Nothing legitimate is lost, because every
     * action that genuinely has no adventure_id (logout, own profile, hexad) is
     * on the allowlist and never reaches this check.
     */
    private function requestIsFromNpc() {
        global $wpdb;
        $user = wp_get_current_user();
        if (!$user || !$user->ID) return false;
        if (in_array('administrator', (array)$user->roles)) return false;

        $adventure_id = 0;
        foreach (['adventure_id','adv_id','the_adventure_id'] as $key) {
            if (isset($_REQUEST[$key]) && intval($_REQUEST[$key])) {
                $adventure_id = intval($_REQUEST[$key]);
                break;
            }
        }
        if ($adventure_id) return $this->isNpc($adventure_id, $user->ID);

        if (in_array('br_npc', (array)$user->roles)) return true;

        return (bool)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure
             WHERE player_id=%d AND player_adventure_role='npc' AND player_adventure_status='in'",
            $user->ID
        ));
    }

    /** Hooked on admin_init, which admin-ajax.php fires before dispatching. */
    public function guardAjax() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) return;

        $action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : '';
        if ($action === '') return;
        if (in_array($action, self::NPC_ALLOWED, true)) return;
        if (!$this->requestIsFromNpc()) return;

        $n = new Notification();
        echo json_encode([
            'success'     => false,
            'just_notify' => true,
            'npc_blocked' => true,
            'message'     => $n->pop(
                __('View-only: NPCs can see everything but cannot make this change.','bluerabbit'),
                'red', 'lock'
            ),
        ]);
        die();
    }
}

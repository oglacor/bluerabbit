<?php

class BR_CaseStudy {

    // ── AJAX: player saves in-progress state (screen/qstate/seen) ─────────────

    public static function ajax_progress() {
        $user_id = get_current_user_id();

        if ( ! check_ajax_referer( 'br_casestudy_data_' . $user_id, 'nonce', false ) ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        $step_id      = intval( $_POST['step_id'] );
        $quest_id     = intval( $_POST['quest_id'] ?? 0 );
        $adventure_id = intval( $_POST['adventure_id'] ?? 0 );
        $state        = isset( $_POST['state'] ) ? wp_unslash( $_POST['state'] ) : '';

        if ( ! $step_id || $state === '' ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        // Cap payload size — this is a client-supplied opaque blob (screen/qstate/
        // seen), the same class of data as SCORM's suspend_data, but SCORM never
        // bounded it. 64KB comfortably covers 20 questions worth of qstate.
        if ( strlen( $state ) > 65536 ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        // Light per-user/per-step throttle — silently accept but skip the write
        // if called again inside 2s. Guards a runaway/buggy tab hammering this
        // endpoint; SCORM's equivalent handler has no such guard at all.
        $last_save_key = "br_casestudy_last_save_$step_id";
        $now           = microtime( true );
        $last_save     = (float) get_user_meta( $user_id, $last_save_key, true );
        if ( $last_save && ( $now - $last_save ) < 2 ) {
            echo json_encode( [ 'success' => true ] );
            die();
        }
        update_user_meta( $user_id, $last_save_key, $now );

        // Whether the player already had saved state decides whether this is a fresh run
        // or the continuation of one. It has to be read BEFORE the write below, because
        // after it there is always state. A retake deletes the state precisely so that
        // the next save reads as a new run.
        $had_state = (bool) get_user_meta( $user_id, "br_casestudy_state_$step_id", true );

        update_user_meta( $user_id, "br_casestudy_state_$step_id", $state );

        // An attempt begins here, not at the end. A run the player never finishes used to
        // leave no trace at all - the only rows written were completions, so "attempts"
        // could never include the ones that were walked away from.
        if ( $quest_id && $adventure_id ) {
            $decoded = json_decode( $state, true );
            self::touch_attempt( $user_id, $step_id, $quest_id, $adventure_id, is_array( $decoded ) ? $decoded : [], $had_state );
        }

        echo json_encode( [ 'success' => true ] );
        die();
    }

    // ── AJAX: player finishes the activity ─────────────────────────────────────

    public static function ajax_complete() {
        $current_user = wp_get_current_user();
        $user_id      = $current_user->ID;
        $n            = new Notification();
        $data         = [ 'success' => false, 'just_notify' => true ];

        if ( ! check_ajax_referer( 'br_casestudy_data_' . $user_id, 'nonce', false ) ) {
            $data['message'] = $n->pop( __( 'Security check failed.', 'bluerabbit' ), 'red', 'cancel' );
            echo json_encode( $data ); die();
        }

        $step_id      = intval( $_POST['step_id'] );
        $quest_id     = intval( $_POST['quest_id'] );
        $adventure_id = intval( $_POST['adventure_id'] );
        $state        = isset( $_POST['state'] ) ? wp_unslash( $_POST['state'] ) : '';

        if ( ! $step_id || ! $quest_id || ! $adventure_id ) {
            $data['message'] = $n->pop( __( 'Missing parameters.', 'bluerabbit' ), 'red', 'warning' );
            echo json_encode( $data ); die();
        }

        // A step already completed used to return early, which meant a retake was
        // impossible AND invisible. Retakes are now unlimited and every one of them is
        // recorded; what completion still guards is the REWARD, granted once by
        // completeStep()'s own bookkeeping, not by refusing to run.
        $already_done = (bool) get_user_meta( $user_id, "br_casestudy_done_$step_id", true );

        if ( $state !== '' && strlen( $state ) <= 65536 ) {
            update_user_meta( $user_id, "br_casestudy_state_$step_id", $state );
        }

        // The real correctness check happens inside BR_Step::validateStepResponse()
        // ('case_study_html' branch) — it independently re-reads the qstate we just
        // persisted above rather than trusting anything in this request or in
        // $response. This is the same shared entry point every other validated
        // step type goes through (multiple_choice, keyphrase, backpack_item, ...).
        //
        // The state is passed through now so completeStep stores it in ps_response.
        // It was called with [] before, which is why every case-study step had a NULL
        // response and showed as a bare "Completed" everywhere answers are listed.
        $decoded  = $state !== '' ? json_decode( $state, true ) : null;
        $response = is_array( $decoded ) ? $decoded : [];
        $result   = BR_Step::instance()->completeStep( $user_id, $step_id, $quest_id, $adventure_id, $response );

        if ( ! $result['success'] ) {
            $data['message'] = $n->pop( __( 'Could not complete step.', 'bluerabbit' ), 'red', 'warning' );
            echo json_encode( $data ); die();
        }

        $passed = ( $result['correct'] !== 0 );
        self::record_attempt( $user_id, $step_id, $quest_id, $adventure_id, $response, $result, $passed );

        $data['success'] = true;
        $data['result']  = $result;

        if ( $passed ) {
            // Kept as the first-pass marker: the template reads it to leave Next open,
            // and overwriting it on every retake would lose when they actually passed.
            if ( ! $already_done ) {
                update_user_meta( $user_id, "br_casestudy_done_$step_id", current_time( 'mysql' ) );
            }
            // Best score, not last - a retake that goes worse should not erase a pass.
            $best = (int) get_user_meta( $user_id, "br_casestudy_score_$step_id", true );
            if ( (int) $result['score'] > $best ) {
                update_user_meta( $user_id, "br_casestudy_score_$step_id", (int) $result['score'] );
            }
            $data['message'] = ! empty( $result['milestone_complete'] )
                ? $n->pop( __( 'Milestone complete!', 'bluerabbit' ), 'green', 'check' )
                : $n->pop( __( 'Case study complete!', 'bluerabbit' ), 'green', 'check' );
        } else {
            $data['message'] = $n->pop( __( "Not quite — try again when you're ready.", 'bluerabbit' ), 'amber', 'warning' );
        }

        echo json_encode( $data );
        die();
    }

    // ── AJAX: player restarts the activity ────────────────────────────────────

    // Clears only the in-progress state, so the iframe starts from a blank run. The
    // completion marker, the best score and every recorded attempt survive - a retake is
    // additional evidence, not a reason to erase what came before. This exists because
    // the activity restores itself from saved state on load, so without clearing it a
    // "completed" run reopens on its results screen and there is nothing to retake.
    public static function ajax_retake() {
        $user_id = get_current_user_id();
        $data    = [ 'success' => false, 'just_notify' => true ];
        $n       = new Notification();

        if ( ! check_ajax_referer( 'br_casestudy_data_' . $user_id, 'nonce', false ) ) {
            $data['message'] = $n->pop( __( 'Security check failed.', 'bluerabbit' ), 'red', 'cancel' );
            echo json_encode( $data ); die();
        }

        $step_id = intval( $_POST['step_id'] );
        if ( ! $step_id ) {
            $data['message'] = $n->pop( __( 'Missing parameters.', 'bluerabbit' ), 'red', 'warning' );
            echo json_encode( $data ); die();
        }

        delete_user_meta( $user_id, "br_casestudy_state_$step_id" );
        delete_user_meta( $user_id, "br_casestudy_last_save_$step_id" );

        // Choosing to start over IS abandoning the run in progress - the one moment we
        // can say so without waiting for it to go quiet.
        global $wpdb;
        $adventure_id = intval( $_POST['adventure_id'] ?? 0 );
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}br_casestudy_attempts SET attempt_status='abandoned'
             WHERE player_id=%d AND step_id=%d AND attempt_status='in_progress'"
             . ( $adventure_id ? " AND adventure_id=$adventure_id" : '' ),
            $user_id, $step_id
        ) );

        $data['success'] = true;
        $data['message'] = $n->pop( __( 'Starting a fresh attempt.', 'bluerabbit' ), 'green', 'check' );
        echo json_encode( $data );
        die();
    }

    // ── Attempt history ───────────────────────────────────────────────────────

    // A run that has been touched but not finished in this long is treated as walked
    // away from. Deliberately generous: a case study spread over a lunch break is still
    // one attempt, and being wrong in this direction only delays the label.
    const STALE_HOURS = 24;

    // Marks unfinished runs abandoned once they go quiet. Safe to be wrong: if the player
    // does come back, touch_attempt() reopens the same row rather than starting a new one,
    // so a resumed run stops being abandoned again.
    private static function sweep_stale( $user_id = 0, $step_id = 0 ) {
        global $wpdb;
        $sql    = "UPDATE {$wpdb->prefix}br_casestudy_attempts
                   SET attempt_status='abandoned'
                   WHERE attempt_status='in_progress'
                     AND COALESCE(attempt_updated, attempt_date) < DATE_SUB(%s, INTERVAL %d HOUR)";
        $params = [ current_time( 'mysql' ), self::STALE_HOURS ];
        if ( $user_id ) { $sql .= " AND player_id=%d"; $params[] = (int) $user_id; }
        if ( $step_id ) { $sql .= " AND step_id=%d";   $params[] = (int) $step_id; }
        $wpdb->query( $wpdb->prepare( $sql, $params ) );
    }

    private static function qstate_of( $response ) {
        return ( isset( $response['qstate'] ) && is_array( $response['qstate'] ) ) ? $response['qstate'] : [];
    }

    private static function correct_in( $qstate ) {
        $n = 0;
        foreach ( $qstate as $q ) {
            if ( is_array( $q ) && ! empty( $q['correct'] ) ) $n++;
        }
        return $n;
    }

    // The open row for this player/step, if there is one.
    //
    // $include_abandoned exists for exactly one caller: a player resuming a run the sweep
    // had already given up on. It must NOT apply when finishing a run, or a completion
    // arriving with no run in progress reaches back and overwrites an unrelated abandoned
    // attempt from a previous session - turning a walked-away-from run into a pass.
    private static function open_attempt( $user_id, $step_id, $adventure_id, $include_abandoned = false ) {
        global $wpdb;
        $statuses = $include_abandoned ? "('in_progress','abandoned')" : "('in_progress')";
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_casestudy_attempts
             WHERE player_id=%d AND step_id=%d AND adventure_id=%d
               AND attempt_status IN $statuses
             ORDER BY attempt_id DESC LIMIT 1",
            $user_id, $step_id, $adventure_id
        ) );
    }

    // Opens an attempt on the first save of a run, and updates it in place on every save
    // after that. $had_state is what distinguishes the two: no saved state means the
    // player is starting fresh, so whatever was still open belongs to a run they left.
    private static function touch_attempt( $user_id, $step_id, $quest_id, $adventure_id, $response, $had_state ) {
        global $wpdb;
        self::sweep_stale( $user_id, $step_id );

        $qstate = self::qstate_of( $response );
        $fields = [
            'attempt_status'  => 'in_progress',
            'attempt_score'   => null,
            'correct_count'   => self::correct_in( $qstate ),
            'total_questions' => count( $qstate ),
            'attempt_answers' => $qstate ? wp_json_encode( $qstate ) : null,
            'attempt_updated' => current_time( 'mysql' ),
        ];

        // Only a player who still has saved state is continuing something, so only they
        // may reclaim an abandoned row.
        $open = self::open_attempt( $user_id, $step_id, $adventure_id, $had_state );

        if ( $open && $had_state ) {
            // Continuing a run - including one the sweep had already given up on.
            $wpdb->update( "{$wpdb->prefix}br_casestudy_attempts", $fields, [ 'attempt_id' => $open->attempt_id ] );
            return (int) $open->attempt_id;
        }

        if ( $open ) {
            // Starting fresh while something was still open: that one was abandoned.
            $wpdb->update( "{$wpdb->prefix}br_casestudy_attempts",
                [ 'attempt_status' => 'abandoned' ], [ 'attempt_id' => $open->attempt_id ] );
        }

        $wpdb->insert( "{$wpdb->prefix}br_casestudy_attempts", array_merge( $fields, [
            'player_id'    => (int) $user_id,
            'adventure_id' => (int) $adventure_id,
            'quest_id'     => (int) $quest_id,
            'step_id'      => (int) $step_id,
            'attempt_no'   => self::next_attempt_no( $user_id, $step_id, $adventure_id ),
            'attempt_date' => current_time( 'mysql' ),
        ] ) );
        return (int) $wpdb->insert_id;
    }

    private static function next_attempt_no( $user_id, $step_id, $adventure_id ) {
        global $wpdb;
        return 1 + (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_casestudy_attempts WHERE player_id=%d AND step_id=%d AND adventure_id=%d",
            $user_id, $step_id, $adventure_id
        ) );
    }

    // Closes the run the player was on. Updates the row opened during play rather than
    // inserting a second one, so finishing does not double-count the attempt; inserts
    // only when the activity completed without ever reporting progress.
    private static function record_attempt( $user_id, $step_id, $quest_id, $adventure_id, $response, $result, $passed ) {
        global $wpdb;

        $qstate = self::qstate_of( $response );
        $fields = [
            'attempt_status'  => $passed ? 'success' : 'fail',
            'attempt_score'   => isset( $result['score'] ) ? (int) $result['score'] : null,
            'correct_count'   => self::correct_in( $qstate ),
            'total_questions' => count( $qstate ),
            'attempt_answers' => $qstate ? wp_json_encode( $qstate ) : null,
            'attempt_updated' => current_time( 'mysql' ),
        ];

        $open = self::open_attempt( $user_id, $step_id, $adventure_id );
        if ( $open ) {
            $wpdb->update( "{$wpdb->prefix}br_casestudy_attempts", $fields, [ 'attempt_id' => $open->attempt_id ] );
            return;
        }

        $wpdb->insert( "{$wpdb->prefix}br_casestudy_attempts", array_merge( $fields, [
            'player_id'    => (int) $user_id,
            'adventure_id' => (int) $adventure_id,
            'quest_id'     => (int) $quest_id,
            'step_id'      => (int) $step_id,
            'attempt_no'   => self::next_attempt_no( $user_id, $step_id, $adventure_id ),
            'attempt_date' => current_time( 'mysql' ),
        ] ) );
    }

    public static function attempts_for_player( $player_id, $adventure_id ) {
        global $wpdb;
        // Label quiet runs before listing them, so the page never shows a months-old
        // attempt as still in progress.
        self::sweep_stale( $player_id );
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, s.step_title, q.quest_title
             FROM {$wpdb->prefix}br_casestudy_attempts a
             LEFT JOIN {$wpdb->prefix}br_steps s ON s.step_id = a.step_id
             LEFT JOIN {$wpdb->prefix}br_quests q ON q.quest_id = a.quest_id
             WHERE a.player_id = %d AND a.adventure_id = %d
             ORDER BY a.attempt_date DESC",
            $player_id, $adventure_id
        ) );
    }
}

<?php

class BR_CaseStudy {

    // ── AJAX: player saves in-progress state (screen/qstate/seen) ─────────────

    public static function ajax_progress() {
        $user_id = get_current_user_id();

        if ( ! check_ajax_referer( 'br_casestudy_data_' . $user_id, 'nonce', false ) ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        $step_id = intval( $_POST['step_id'] );
        $state   = isset( $_POST['state'] ) ? wp_unslash( $_POST['state'] ) : '';

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

        update_user_meta( $user_id, "br_casestudy_state_$step_id", $state );

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

        $data['success'] = true;
        $data['message'] = $n->pop( __( 'Starting a fresh attempt.', 'bluerabbit' ), 'green', 'check' );
        echo json_encode( $data );
        die();
    }

    // ── Attempt history ───────────────────────────────────────────────────────

    // One row per attempt, passed or failed. qstate is stored exactly as the activity
    // sent it: its shape is defined by the vendor's HTML, and normalising it here would
    // quietly discard any field we did not think to keep.
    private static function record_attempt( $user_id, $step_id, $quest_id, $adventure_id, $response, $result, $passed ) {
        global $wpdb;

        $qstate = ( isset( $response['qstate'] ) && is_array( $response['qstate'] ) ) ? $response['qstate'] : [];
        $correct_count = 0;
        foreach ( $qstate as $q ) {
            if ( is_array( $q ) && ! empty( $q['correct'] ) ) $correct_count++;
        }

        $attempt_no = 1 + (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}br_casestudy_attempts WHERE player_id=%d AND step_id=%d AND adventure_id=%d",
            $user_id, $step_id, $adventure_id
        ) );

        $wpdb->insert( "{$wpdb->prefix}br_casestudy_attempts", [
            'player_id'       => (int) $user_id,
            'adventure_id'    => (int) $adventure_id,
            'quest_id'        => (int) $quest_id,
            'step_id'         => (int) $step_id,
            'attempt_no'      => $attempt_no,
            'attempt_status'  => $passed ? 'success' : 'fail',
            'attempt_score'   => isset( $result['score'] ) ? (int) $result['score'] : null,
            'correct_count'   => $correct_count,
            'total_questions' => count( $qstate ),
            'attempt_answers' => $qstate ? wp_json_encode( $qstate ) : null,
            'attempt_date'    => current_time( 'mysql' ),
        ] );
    }

    public static function attempts_for_player( $player_id, $adventure_id ) {
        global $wpdb;
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

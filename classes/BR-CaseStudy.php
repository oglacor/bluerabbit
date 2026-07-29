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

        // Idempotent — a second completion call for an already-done step is a
        // no-op success, not a re-grant.
        $done_at = get_user_meta( $user_id, "br_casestudy_done_$step_id", true );
        if ( $done_at ) {
            $data['success'] = true;
            $data['message'] = $n->pop( __( 'Already completed.', 'bluerabbit' ), 'green', 'check' );
            echo json_encode( $data ); die();
        }

        if ( $state !== '' && strlen( $state ) <= 65536 ) {
            update_user_meta( $user_id, "br_casestudy_state_$step_id", $state );
        }

        // The real correctness check happens inside BR_Step::validateStepResponse()
        // ('case_study_html' branch) — it independently re-reads the qstate we just
        // persisted above rather than trusting anything in this request or in
        // $response. This is the same shared entry point every other validated
        // step type goes through (multiple_choice, keyphrase, backpack_item, ...).
        $result = BR_Step::instance()->completeStep( $user_id, $step_id, $quest_id, $adventure_id, [] );

        if ( ! $result['success'] ) {
            $data['message'] = $n->pop( __( 'Could not complete step.', 'bluerabbit' ), 'red', 'warning' );
            echo json_encode( $data ); die();
        }

        $data['success'] = true;
        $data['result']  = $result;

        if ( $result['correct'] !== 0 ) {
            update_user_meta( $user_id, "br_casestudy_done_$step_id", current_time( 'mysql' ) );
            update_user_meta( $user_id, "br_casestudy_score_$step_id", (int) $result['score'] );
            $data['message'] = ! empty( $result['milestone_complete'] )
                ? $n->pop( __( 'Milestone complete!', 'bluerabbit' ), 'green', 'check' )
                : $n->pop( __( 'Case study complete!', 'bluerabbit' ), 'green', 'check' );
        } else {
            $data['message'] = $n->pop( __( "Not quite — you need 70% to pass. Try again.", 'bluerabbit' ), 'amber', 'warning' );
        }

        echo json_encode( $data );
        die();
    }
}

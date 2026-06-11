<?php

class BR_SCORM {

    /**
     * Unzip a SCORM package, parse imsmanifest.xml, return the launch URL.
     * Reusable by any caller — no side effects beyond filesystem writes.
     */
    public static function install_package( $zip_path, $adventure_id, $step_id ) {
        $upload     = wp_upload_dir();
        $dest       = $upload['basedir'] . "/bluerabbit-scorm/$adventure_id/$step_id/";
        $dest_url   = $upload['baseurl'] . "/bluerabbit-scorm/$adventure_id/$step_id/";

        if ( is_dir( $dest ) ) {
            self::rrmdir( $dest );
        }
        wp_mkdir_p( $dest );

        $zip = new ZipArchive();
        if ( $zip->open( $zip_path ) !== true ) {
            return new WP_Error( 'zip_open', __( 'Could not open SCORM zip.', 'bluerabbit' ) );
        }

        // Zip-slip guard: reject any entry that escapes $dest
        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $name = $zip->getNameIndex( $i );
            if ( strpos( $name, '..' ) !== false || strpos( $name, "\0" ) !== false ) {
                $zip->close();
                return new WP_Error( 'zip_traversal', __( 'Invalid zip contents.', 'bluerabbit' ) );
            }
        }

        $zip->extractTo( $dest );
        $zip->close();

        $manifest = $dest . 'imsmanifest.xml';
        if ( ! file_exists( $manifest ) ) {
            return new WP_Error( 'no_manifest', __( 'imsmanifest.xml not found in zip.', 'bluerabbit' ) );
        }

        $launch = self::parse_manifest_launch( $manifest );
        if ( ! $launch ) {
            return new WP_Error( 'no_launch', __( 'Could not determine launch URL from imsmanifest.xml.', 'bluerabbit' ) );
        }

        return $dest_url . $launch;
    }

    /**
     * Parse imsmanifest.xml and return the href of the default SCO.
     */
    private static function parse_manifest_launch( $manifest_path ) {
        libxml_use_internal_errors( true );
        $xml = simplexml_load_file( $manifest_path );
        if ( ! $xml ) {
            return null;
        }

        // Strip namespace prefixes so we can use simple element access
        $xml_str = file_get_contents( $manifest_path );
        $xml_str = preg_replace( '/(<\/?)[a-zA-Z]+:/', '$1', $xml_str );   // strip ns prefixes
        $xml_str = preg_replace( '/ xmlns[^=]*="[^"]*"/', '', $xml_str );  // strip xmlns attrs
        $xml      = simplexml_load_string( $xml_str );
        if ( ! $xml ) {
            return null;
        }

        $default_org = (string) $xml->organizations['default'];
        $launch_ref  = null;

        foreach ( $xml->organizations->organization as $org ) {
            if ( ! $default_org || (string) $org['identifier'] === $default_org ) {
                $item = $org->item[0] ?? null;
                if ( $item ) {
                    $launch_ref = (string) $item['identifierref'];
                    break;
                }
            }
        }

        if ( ! $launch_ref ) {
            return null;
        }

        foreach ( $xml->resources->resource as $resource ) {
            if ( (string) $resource['identifier'] === $launch_ref ) {
                return (string) $resource['href'];
            }
        }

        return null;
    }

    // ── AJAX: admin uploads SCORM zip ─────────────────────────────────────────

    public static function ajax_upload() {
        global $wpdb;
        $n = new Notification();

        if ( ! check_ajax_referer( 'br_scorm_upload', 'nonce', false ) ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'Security check failed.', 'bluerabbit' ), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        $step_id      = intval( $_POST['step_id'] );
        $adventure_id = intval( $_POST['adventure_id'] );

        if ( ! $step_id || ! $adventure_id ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'Invalid parameters.', 'bluerabbit' ), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        if ( empty( $_FILES['scorm_zip']['tmp_name'] ) ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'No file uploaded.', 'bluerabbit' ), 'amber', 'warning' ), 'just_notify' => true ] );
            die();
        }

        $file = $_FILES['scorm_zip'];
        if ( strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) !== 'zip' ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'File must be a .zip.', 'bluerabbit' ), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        $launch_url = self::install_package( $file['tmp_name'], $adventure_id, $step_id );

        if ( is_wp_error( $launch_url ) ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( $launch_url->get_error_message(), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}br_steps SET step_settings = %s WHERE step_id = %d AND adventure_id = %d",
            json_encode( [ 'scorm_launch_url' => $launch_url ] ),
            $step_id,
            $adventure_id
        ) );

        echo json_encode( [
            'success'    => true,
            'launch_url' => $launch_url,
            'message'    => $n->pop( __( 'SCORM package installed!', 'bluerabbit' ), 'green', 'check' ),
            'just_notify'=> true,
        ] );
        die();
    }

    // ── AJAX: player saves lesson_status / lesson_location / suspend_data ─────

    public static function ajax_save_data() {
        $user_id = get_current_user_id();

        if ( ! check_ajax_referer( 'br_scorm_data_' . $user_id, 'nonce', false ) ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        $step_id = intval( $_POST['step_id'] );
        if ( ! $step_id ) {
            echo json_encode( [ 'success' => false ] );
            die();
        }

        if ( isset( $_POST['lesson_status'] ) ) {
            update_user_meta( $user_id, "br_scorm_lesson_status_$step_id",   sanitize_text_field( $_POST['lesson_status'] ) );
        }
        if ( isset( $_POST['lesson_location'] ) ) {
            update_user_meta( $user_id, "br_scorm_lesson_location_$step_id", sanitize_text_field( $_POST['lesson_location'] ) );
        }
        if ( isset( $_POST['suspend_data'] ) ) {
            // suspend_data is arbitrary base64/JSON — store unslashed
            update_user_meta( $user_id, "br_scorm_suspend_data_$step_id", wp_unslash( $_POST['suspend_data'] ) );
        }

        echo json_encode( [ 'success' => true ] );
        die();
    }

    // ── Admin: reset all user attempts for a SCORM step ──────────────────────

    /**
     * Delete every player's saved SCORM state for the given step.
     * Uses the same meta key names that ajax_save_data() writes.
     */
    public static function reset_all_users( $step_id ) {
        global $wpdb;
        foreach ( [
            "br_scorm_lesson_status_$step_id",
            "br_scorm_lesson_location_$step_id",
            "br_scorm_suspend_data_$step_id",
        ] as $key ) {
            $wpdb->delete( $wpdb->usermeta, [ 'meta_key' => $key ] );
        }
    }

    /** AJAX: reset all user attempts for a step (GM/admin only). */
    public static function ajax_reset_all() {
        $n = new Notification();

        if ( ! check_ajax_referer( 'br_scorm_reset_all', 'nonce', false ) ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'Security check failed.', 'bluerabbit' ), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            echo json_encode( [ 'success' => false, 'message' => $n->pop( __( 'Not allowed.', 'bluerabbit' ), 'red', 'cancel' ), 'just_notify' => true ] );
            die();
        }

        $step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
        if ( $step_id ) {
            self::reset_all_users( $step_id );
        }

        echo json_encode( [
            'success'     => true,
            'message'     => $n->pop( __( 'All attempts cleared.', 'bluerabbit' ), 'green', 'check' ),
            'just_notify' => true,
        ] );
        die();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function rrmdir( $dir ) {
        foreach ( array_diff( scandir( $dir ), [ '.', '..' ] ) as $file ) {
            $path = "$dir/$file";
            is_dir( $path ) ? self::rrmdir( $path ) : unlink( $path );
        }
        rmdir( $dir );
    }
}

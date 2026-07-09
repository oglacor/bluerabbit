<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_PlayerMeta {

	// Whitelist of editable player_meta columns. Never interpolate a
	// client-supplied column name into SQL - always go through this map.
	const FIELDS = [
		'player_gender'     => 'Gender',
		'work_level'        => 'Level',
		'work_function'     => 'Function',
		'work_sub_function' => 'Sub-Function',
		'job_profile'        => 'Job Profile',
		'business_pillar'   => 'Business Pillar',
		'work_cluster'      => 'Cluster',
		'work_country'      => 'Country',
		'work_location'     => 'Location',
	];

	// Header aliases accepted in an imported CSV, normalized (lowercase,
	// letters/digits only) => target db column. The db column name itself
	// and its label are always accepted too (added in normalize_headers()).
	const HEADER_ALIASES = [
		'gender'        => 'player_gender',
		'sex'           => 'player_gender',
		'level'         => 'work_level',
		'worklevel'     => 'work_level',
		'function'      => 'work_function',
		'subfunction'   => 'work_sub_function',
		'jobprofile'    => 'job_profile',
		'jobtitle'      => 'job_profile',
		'title'         => 'job_profile',
		'businesspillar'=> 'business_pillar',
		'pillar'        => 'business_pillar',
		'cluster'       => 'work_cluster',
		'country'       => 'work_country',
		'location'      => 'work_location',
		'site'          => 'work_location',
		'workcountry'   => 'work_country',
		'worklocation'  => 'work_location',
		'email'         => '_email',
		'useremail'     => '_email',
		'playeremail'   => '_email',
	];

	private function normalize_header( string $h ): string {
		return preg_replace( '/[^a-z0-9]/', '', strtolower( trim( $h ) ) );
	}

	// Portable: swap $wpdb for PDO to migrate.
	// Players enrolled in the adventure, with their player_meta joined
	// (one row per player_id, most recent player_meta_id wins - the table
	// has no unique index on player_id so duplicates are possible).
	public function get_players_with_meta( int $adventure_id, int $limit = 50, int $offset = 0, string $search = '' ): array {
		global $wpdb;

		$search_sql = '';
		$params     = [ $adventure_id ];
		if ( $search !== '' ) {
			$search_sql = "AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
			$like       = '%' . $wpdb->esc_like( $search ) . '%';
			$params[]   = $like;
			$params[]   = $like;
		}

		$sql = "SELECT
				pa.player_id, u.display_name, u.user_email,
				pm.player_gender, pm.work_level, pm.work_function, pm.work_sub_function,
				pm.job_profile, pm.business_pillar, pm.work_cluster, pm.work_country, pm.work_location
			FROM {$wpdb->prefix}br_player_adventure pa
			LEFT JOIN {$wpdb->users} u ON pa.player_id = u.ID
			LEFT JOIN {$wpdb->prefix}br_player_meta pm
				ON pm.player_meta_id = (
					SELECT MAX(player_meta_id) FROM {$wpdb->prefix}br_player_meta
					WHERE player_id = pa.player_id
				)
			WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'
			$search_sql
			ORDER BY u.display_name ASC
			LIMIT %d OFFSET %d";

		$params[] = $limit;
		$params[] = $offset;
		$players  = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );

		$count_sql    = "SELECT COUNT(*) FROM {$wpdb->prefix}br_player_adventure pa
			LEFT JOIN {$wpdb->users} u ON pa.player_id = u.ID
			WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'
			$search_sql";
		$count_params = $search !== '' ? [ $adventure_id, $like, $like ] : [ $adventure_id ];
		$total        = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$count_params ) );

		return [ 'players' => $players, 'total' => $total ];
	}

	// Upsert one player's meta fields. Only whitelisted columns are ever
	// written. Updates the most recent row for player_id if one exists,
	// otherwise inserts a new one.
	public function save_player_meta( int $player_id, array $fields ): bool {
		global $wpdb;

		$data = [];
		foreach ( self::FIELDS as $col => $label ) {
			if ( array_key_exists( $col, $fields ) ) {
				$data[ $col ] = sanitize_text_field( $fields[ $col ] );
			}
		}
		if ( empty( $data ) ) return false;

		$existing_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(player_meta_id) FROM {$wpdb->prefix}br_player_meta WHERE player_id = %d",
			$player_id
		) );

		if ( $existing_id ) {
			return (bool) $wpdb->update( "{$wpdb->prefix}br_player_meta", $data, [ 'player_meta_id' => $existing_id ] ) !== false;
		}

		$data['player_id'] = $player_id;
		return (bool) $wpdb->insert( "{$wpdb->prefix}br_player_meta", $data ) !== false;
	}

	// Parses CSV text (first row = headers), matches each row to an
	// existing player enrolled in $adventure_id by email, and maps
	// recognized headers to player_meta columns. When $dry_run is false,
	// upserts matched rows via save_player_meta(). Returns a report used
	// for both the preview step and the commit step (same validation path
	// for both, so what you previewed is exactly what gets written).
	public function import_csv( int $adventure_id, string $csv_content, bool $dry_run = true ): array {
		global $wpdb;

		$rows = $this->parse_csv( $csv_content );
		if ( empty( $rows ) ) {
			return [ 'error' => __( 'No rows found in file.', 'bluerabbit' ) ];
		}

		$header = array_shift( $rows );
		$column_map = []; // csv column index => db field (or '_email')
		foreach ( $header as $i => $h ) {
			$norm = $this->normalize_header( $h );
			if ( $norm === '' ) continue;
			foreach ( self::FIELDS as $col => $label ) {
				if ( $norm === $col || $norm === $this->normalize_header( $label ) ) {
					if ( ! in_array( $col, $column_map, true ) ) $column_map[ $i ] = $col;
					continue 2;
				}
			}
			if ( isset( self::HEADER_ALIASES[ $norm ] ) ) {
				$target = self::HEADER_ALIASES[ $norm ];
				if ( ! in_array( $target, $column_map, true ) ) $column_map[ $i ] = $target;
			}
		}

		$email_col = array_search( '_email', $column_map, true );
		if ( $email_col === false ) {
			return [ 'error' => __( 'No email column found - add an "Email" column to match rows to existing players.', 'bluerabbit' ) ];
		}

		// Players enrolled in this adventure, keyed by lowercased email.
		$enrolled = $wpdb->get_results( $wpdb->prepare(
			"SELECT pa.player_id, u.display_name, u.user_email
			FROM {$wpdb->prefix}br_player_adventure pa
			LEFT JOIN {$wpdb->users} u ON pa.player_id = u.ID
			WHERE pa.adventure_id = %d AND pa.player_adventure_status = 'in' AND pa.player_adventure_role = 'player'",
			$adventure_id
		), ARRAY_A );
		$by_email = [];
		foreach ( $enrolled as $p ) $by_email[ strtolower( trim( $p['user_email'] ) ) ] = $p;

		$mapped_fields = array_values( array_diff( $column_map, [ '_email' ] ) );

		$report = [
			'mapped_columns'   => array_map( function ( $col ) { return $col === '_email' ? __( 'Email', 'bluerabbit' ) : ( self::FIELDS[ $col ] ?? $col ); }, $column_map ),
			'matched_count'    => 0,
			'unmatched_count'  => 0,
			'updated_count'    => 0,
			'rows'             => [],
		];

		foreach ( $rows as $line_num => $row ) {
			if ( count( array_filter( $row, function ( $v ) { return trim( (string) $v ) !== ''; } ) ) === 0 ) continue; // blank line

			$email = isset( $row[ $email_col ] ) ? strtolower( trim( $row[ $email_col ] ) ) : '';
			$player = $by_email[ $email ] ?? null;

			$fields = [];
			foreach ( $column_map as $i => $col ) {
				if ( $col === '_email' ) continue;
				$fields[ $col ] = isset( $row[ $i ] ) ? trim( $row[ $i ] ) : '';
			}

			$row_report = [
				'line'         => $line_num + 2, // +1 header, +1 for 1-indexed display
				'email'        => $email,
				'matched'      => (bool) $player,
				'display_name' => $player['display_name'] ?? null,
				'fields'       => $fields,
			];

			if ( $player ) {
				$report['matched_count']++;
				if ( ! $dry_run ) {
					if ( $this->save_player_meta( (int) $player['player_id'], $fields ) ) {
						$report['updated_count']++;
					}
				}
			} else {
				$report['unmatched_count']++;
			}

			$report['rows'][] = $row_report;
		}

		return $report;
	}

	private function parse_csv( string $content ): array {
		$content = preg_replace( '/^\xEF\xBB\xBF/', '', $content ); // strip BOM
		$stream  = fopen( 'php://temp', 'r+' );
		fwrite( $stream, $content );
		rewind( $stream );

		$rows = [];
		while ( ( $row = fgetcsv( $stream ) ) !== false ) {
			if ( $row === [ null ] ) continue;
			$rows[] = $row;
		}
		fclose( $stream );
		return $rows;
	}
}

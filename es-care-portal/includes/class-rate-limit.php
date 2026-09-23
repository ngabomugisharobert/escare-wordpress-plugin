<?php
/**
 * Atomic per-action abuse counters.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Rate_Limit {

	/**
	 * Action limits: hits per window (seconds).
	 *
	 * @return array<string,array{limit:int,window:int}>
	 */
	public static function limits() {
		return array(
			'register'     => array( 'limit' => 10, 'window' => HOUR_IN_SECONDS ),
			'job_publish'  => array( 'limit' => 20, 'window' => DAY_IN_SECONDS ),
			'apply'        => array( 'limit' => 40, 'window' => DAY_IN_SECONDS ),
			'request'      => array( 'limit' => 20, 'window' => HOUR_IN_SECONDS ),
			'contact'      => array( 'limit' => 10, 'window' => HOUR_IN_SECONDS ),
			'assessment'   => array( 'limit' => 30, 'window' => HOUR_IN_SECONDS ),
			'verify_resend'=> array( 'limit' => 6, 'window' => HOUR_IN_SECONDS ),
			'lost_password'=> array( 'limit' => 10, 'window' => 5 * MINUTE_IN_SECONDS ),
			'delete_request'=> array( 'limit' => 6, 'window' => HOUR_IN_SECONDS ),
		);
	}

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_rate_limits';
	}

	/**
	 * Create the counter table.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				action_key varchar(64) NOT NULL,
				client_key varchar(64) NOT NULL,
				hits int(10) unsigned NOT NULL DEFAULT 0,
				window_start datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY action_client (action_key, client_key),
				KEY window_start (window_start)
			) {$charset};"
		);
	}

	/**
	 * Increment and test a named action for the current client and optional account.
	 *
	 * @param string $action  Action key.
	 * @param string $account Optional account identifier.
	 * @return bool True when the request is allowed.
	 */
	public static function allow( $action, $account = '' ) {
		$limits = self::limits();

		if ( ! isset( $limits[ $action ] ) ) {
			return true;
		}

		$limit  = (int) $limits[ $action ]['limit'];
		$window = (int) $limits[ $action ]['window'];
		$keys   = array( self::client_key() );

		if ( $account ) {
			$keys[] = 'acct_' . substr( hash_hmac( 'sha256', strtolower( (string) $account ), wp_salt( 'nonce' ) ), 0, 40 );
		}

		foreach ( $keys as $key ) {
			if ( ! self::hit( $action, $key, $limit, $window ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Send a generic 429 response and stop.
	 */
	public static function reject() {
		status_header( 429 );
		nocache_headers();
		header( 'Retry-After: 300' );

		if ( wp_doing_ajax() ) {
			wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again later.', 'es-care-portal' ) ), 429 );
		}

		$fallback = ESC_Portal_Helpers::get_page_url( 'login' );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'rate-limited', 'error' );
	}

	/**
	 * Trusted client identifier. Uses REMOTE_ADDR unless a trusted proxy is configured
 * with ESC_PORTAL_TRUSTED_PROXIES (comma-separated IPs). Forwarded headers are
 * ignored unless the immediate client is in that list, so spoofed X-Forwarded-For
 * values cannot reset rate limits.
	 *
	 * @return string
	 */
	public static function client_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

		$trusted = array();
		if ( defined( 'ESC_PORTAL_TRUSTED_PROXIES' ) && ESC_PORTAL_TRUSTED_PROXIES ) {
			$trusted = array_filter( array_map( 'trim', explode( ',', (string) ESC_PORTAL_TRUSTED_PROXIES ) ) );
		}

		if ( $trusted && in_array( $ip, $trusted, true ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$candidate = trim( $forwarded[0] );
			if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
				$ip = $candidate;
			}
		}

		if ( 'unknown' !== $ip && false === filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = 'invalid';
		}

		return substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 40 );
	}

	/**
	 * @param string $action Action.
	 * @param string $key    Client key.
	 * @param int    $limit  Max hits.
	 * @param int    $window Window seconds.
	 * @return bool
	 */
	private static function hit( $action, $key, $limit, $window ) {
		global $wpdb;

		$table = self::table();
		$now   = gmdate( 'Y-m-d H:i:s' );
		$cut   = gmdate( 'Y-m-d H:i:s', time() - $window );

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (action_key, client_key, hits, window_start) VALUES (%s, %s, 1, %s)
				ON DUPLICATE KEY UPDATE
					hits = IF(window_start < %s, 1, hits + 1),
					window_start = IF(window_start < %s, %s, window_start)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$action,
				$key,
				$now,
				$cut,
				$cut,
				$now
			)
		);

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT hits FROM {$table} WHERE action_key = %s AND client_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$action,
				$key
			)
		);

		return $count <= $limit;
	}
}

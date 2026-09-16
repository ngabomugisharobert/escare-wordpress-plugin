<?php
/**
 * Request hardening and abuse controls.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Security {

	const NOT_FOUND_WINDOW = 300;
	const NOT_FOUND_LIMIT  = 30;
	const BLOCK_TTL        = 900;

	/**
	 * Register security hooks.
	 */
	public static function init() {
		add_action( 'send_headers', array( __CLASS__, 'security_headers' ) );
		add_action( 'template_redirect', array( __CLASS__, 'limit_not_found_abuse' ), 9999 );
	}

	/**
	 * Add conservative headers that are compatible with WordPress and Elementor.
	 */
	public static function security_headers() {
		if ( headers_sent() ) {
			return;
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=()' );
	}

	/**
	 * Temporarily block anonymous clients generating excessive 404 responses.
	 *
	 * This is defense in depth. A host-level WAF remains the best place to
	 * absorb distributed bot traffic before WordPress/PHP runs.
	 */
	public static function limit_not_found_abuse() {
		if ( is_user_logged_in() || ESC_Portal_Auth::is_logged_in() ) {
			return;
		}

		$key = self::client_key();

		if ( get_transient( 'esc_404_block_' . $key ) ) {
			self::send_rate_limit_response();
		}

		if ( ! is_404() ) {
			return;
		}

		$count_key = 'esc_404_count_' . $key;
		$count     = (int) get_transient( $count_key ) + 1;

		set_transient( $count_key, $count, self::NOT_FOUND_WINDOW );

		if ( $count >= self::NOT_FOUND_LIMIT ) {
			set_transient( 'esc_404_block_' . $key, 1, self::BLOCK_TTL );
			delete_transient( $count_key );
			self::send_rate_limit_response();
		}
	}

	/**
	 * A keyed, non-reversible client identifier.
	 *
	 * @return string
	 */
	private static function client_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

		if ( 'unknown' !== $ip && false === filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = 'invalid';
		}

		return substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 32 );
	}

	/**
	 * End an abusive request with HTTP 429.
	 */
	private static function send_rate_limit_response() {
		status_header( 429 );
		nocache_headers();
		header( 'Retry-After: ' . self::BLOCK_TTL );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo esc_html__( 'Too many invalid requests. Please try again later.', 'es-care-portal' );
		exit;
	}
}

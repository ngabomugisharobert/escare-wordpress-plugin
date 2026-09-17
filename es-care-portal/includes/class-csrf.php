<?php
/**
 * Per-browser CSRF protection for logged-out portal forms.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_CSRF {

	const COOKIE = 'esc_portal_csrf';
	const FIELD  = 'esc_browser_token';
	const TTL    = YEAR_IN_SECONDS;

	/**
	 * Ensure a browser token cookie exists.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'ensure_cookie' ), 1 );
	}

	/**
	 * Issue a CSRF cookie for anonymous visitors.
	 */
	public static function ensure_cookie() {
		if ( headers_sent() ) {
			return;
		}

		if ( ! empty( $_COOKIE[ self::COOKIE ] ) && is_string( $_COOKIE[ self::COOKIE ] ) && strlen( $_COOKIE[ self::COOKIE ] ) >= 32 ) {
			return;
		}

		self::set_cookie( self::generate() );
	}

	/**
	 * @return string
	 */
	public static function token() {
		if ( ! empty( $_COOKIE[ self::COOKIE ] ) && is_string( $_COOKIE[ self::COOKIE ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
		}

		$token = self::generate();
		self::set_cookie( $token );

		return $token;
	}

	/**
	 * Hidden input for anonymous forms.
	 *
	 * @return string
	 */
	public static function field() {
		return '<input type="hidden" name="' . esc_attr( self::FIELD ) . '" value="' . esc_attr( self::token() ) . '">';
	}

	/**
	 * Validate the browser token plus Origin/Referer for anonymous POSTs.
	 *
	 * @return bool
	 */
	public static function verify() {
		$posted = isset( $_POST[ self::FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::FIELD ] ) ) : '';
		$cookie = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';

		if ( ! $posted || ! $cookie || ! hash_equals( $cookie, $posted ) ) {
			return false;
		}

		return self::origin_is_local();
	}

	/**
	 * Rotate the browser token after a successful authentication.
	 */
	public static function rotate() {
		self::set_cookie( self::generate() );
	}

	/**
	 * @return bool
	 */
	public static function origin_is_local() {
		$home = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$refs = array();

		if ( ! empty( $_SERVER['HTTP_ORIGIN'] ) ) {
			$refs[] = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ), PHP_URL_HOST );
		}

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$refs[] = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), PHP_URL_HOST );
		}

		$refs = array_filter( $refs );

		if ( ! $refs ) {
			return true;
		}

		foreach ( $refs as $host ) {
			if ( $home && is_string( $host ) && strtolower( $host ) === strtolower( $home ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	private static function generate() {
		try {
			return bin2hex( random_bytes( 32 ) );
		} catch ( Exception $exception ) {
			return wp_generate_password( 64, false, false );
		}
	}

	/**
	 * @param string $value Token.
	 */
	private static function set_cookie( $value ) {
		$secure = is_ssl();
		$path   = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				self::COOKIE,
				$value,
				array(
					'expires'  => time() + self::TTL,
					'path'     => $path,
					'domain'   => $domain,
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			setcookie( self::COOKIE, $value, time() + self::TTL, $path, $domain, $secure, true );
		}

		$_COOKIE[ self::COOKIE ] = $value;
	}
}

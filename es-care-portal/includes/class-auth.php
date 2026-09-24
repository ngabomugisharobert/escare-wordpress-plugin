<?php
/**
 * Portal registration, login, sessions, and password reset.
 * Independent of WordPress users.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Auth {

	const COOKIE     = 'esc_portal_session';
	const MAX_FAILS  = 5;
	const IP_MAX_FAILS = 20;
	const LOCK_TTL   = 900;
	const SESSION_TTL = 1209600; // 14 days.

	/**
	 * @var object|null|false
	 */
	private static $current = false;

	/**
	 * Register hooks.
	 */
	public static function init() {
		self::bind( 'esc_register', array( __CLASS__, 'handle_register' ) );
		self::bind( 'esc_login', array( __CLASS__, 'handle_login' ) );
		self::bind( 'esc_logout', array( __CLASS__, 'handle_logout' ) );
		self::bind( 'esc_lost_password', array( __CLASS__, 'handle_lost_password' ) );
		self::bind( 'esc_reset_password', array( __CLASS__, 'handle_reset_password' ) );
		self::bind( 'esc_profile', array( 'ESC_Portal_Profile', 'handle_save' ) );
		self::bind( 'esc_apply', array( 'ESC_Portal_Apply', 'handle_apply' ) );
		self::bind( 'esc_withdraw', array( 'ESC_Portal_Apply', 'handle_withdraw' ) );
		self::bind( 'esc_save_job_front', array( 'ESC_Portal_Employer', 'handle_save_job' ) );
		self::bind( 'esc_delete_job_front', array( 'ESC_Portal_Employer', 'handle_delete_job' ) );
		self::bind( 'esc_portal_status', array( __CLASS__, 'handle_status' ) );
		self::bind( 'esc_portal_user', array( __CLASS__, 'handle_user_update' ) );
		self::bind( 'esc_admin_delete_user', array( __CLASS__, 'handle_admin_delete_user' ) );
		self::bind( 'esc_admin_delete_application', array( __CLASS__, 'handle_admin_delete_application' ) );
		self::bind( 'esc_verify_email', array( __CLASS__, 'handle_verify_email' ) );
		self::bind( 'esc_resend_verification', array( __CLASS__, 'handle_resend_verification' ) );
		self::bind( 'esc_resend_verification_public', array( __CLASS__, 'handle_resend_verification_public' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_handle_public_verify' ) );
		self::bind( 'esc_moderate_job', array( __CLASS__, 'handle_moderate_job' ) );
		self::bind( 'esc_change_password', array( 'ESC_Portal_Account', 'handle_change_password' ) );
		self::bind( 'esc_delete_account', array( 'ESC_Portal_Account', 'handle_delete_account' ) );
		self::bind( 'esc_submit_assessment', array( 'ESC_Portal_Assessments', 'handle_submit' ) );
		self::bind( 'esc_admin_save_assessment', array( 'ESC_Portal_Assessments', 'handle_admin_save' ) );
		self::bind( 'esc_admin_assessment_action', array( 'ESC_Portal_Assessments', 'handle_admin_action' ) );
		self::bind( 'esc_service_request', array( 'ESC_Portal_Forms', 'handle_request' ) );
		self::bind( 'esc_contact', array( 'ESC_Portal_Forms', 'handle_contact' ) );
		self::bind( 'esc_download_form', array( 'ESC_Portal_Forms', 'handle_download' ) );
		add_action( 'admin_post_esc_form_upload', array( 'ESC_Portal_Forms', 'handle_admin_upload' ) );
		add_action( 'admin_post_esc_form_delete', array( 'ESC_Portal_Forms', 'handle_admin_delete' ) );
	}

	/**
	 * Hook a form action for both WP-logged-in and guest requests.
	 *
	 * @param string   $action   admin-post action.
	 * @param callable $callback Callback.
	 */
	public static function bind( $action, $callback ) {
		add_action( 'admin_post_' . $action, $callback );
		add_action( 'admin_post_nopriv_' . $action, $callback );
	}

	/**
	 * Current portal user, or null.
	 *
	 * @return object|null
	 */
	public static function current_user() {
		if ( false !== self::$current ) {
			return self::$current;
		}

		self::$current = self::user_from_cookie();

		return self::$current;
	}

	/**
	 * @return bool
	 */
	public static function is_logged_in() {
		return (bool) self::current_user();
	}

	/**
	 * @return int
	 */
	public static function current_user_id() {
		$user = self::current_user();
		return $user ? (int) $user->id : 0;
	}

	/**
	 * Start a session for a portal user.
	 *
	 * @param int  $user_id  User ID.
	 * @param bool $remember Remember.
	 */
	public static function login_user( $user_id, $remember = true ) {
		global $wpdb;

		$user_id = absint( $user_id );
		try {
			$token = bin2hex( random_bytes( 32 ) );
		} catch ( Exception $exception ) {
			$token = wp_generate_password( 64, true, true );
		}
		$hash    = self::hash_token( $token );
		$ttl     = $remember ? self::SESSION_TTL : DAY_IN_SECONDS * 2;
		$expires = gmdate( 'Y-m-d H:i:s', time() + $ttl );

		$wpdb->query( 'DELETE FROM ' . ESC_Portal_Users::sessions_table() . ' WHERE expires <= UTC_TIMESTAMP()' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->insert(
			ESC_Portal_Users::sessions_table(),
			array(
				'user_id'    => $user_id,
				'token_hash' => $hash,
				'expires'    => $expires,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s' )
		);

		ESC_Portal_Users::update( $user_id, array( 'last_login' => current_time( 'mysql' ) ) );

		self::set_cookie( $user_id . '|' . $token, $ttl );
		ESC_Portal_CSRF::rotate();
		self::$current = ESC_Portal_Users::get( $user_id );
	}

	/**
	 * Revoke every active session belonging to a dashboard user.
	 *
	 * @param int $user_id Dashboard user ID.
	 */
	public static function revoke_user_sessions( $user_id ) {
		global $wpdb;

		$wpdb->delete(
			ESC_Portal_Users::sessions_table(),
			array( 'user_id' => absint( $user_id ) ),
			array( '%d' )
		);
	}

	/**
	 * End the current portal session.
	 */
	public static function logout_user() {
		$parts = self::cookie_parts();

		if ( $parts ) {
			global $wpdb;
			$wpdb->delete(
				ESC_Portal_Users::sessions_table(),
				array(
					'user_id'    => $parts['user_id'],
					'token_hash' => self::hash_token( $parts['token'] ),
				),
				array( '%d', '%s' )
			);
		}

		self::set_cookie( '', - YEAR_IN_SECONDS );
		self::$current = null;
	}

	/**
	 * Registration handler.
	 */
	public static function handle_register() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'register' );

		if ( ! isset( $_POST['esc_register_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_register_nonce'] ) ), 'esc_register' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		if ( self::is_logged_in() ) {
			wp_safe_redirect( ESC_Portal_Helpers::get_page_url( 'dashboard' ) );
			exit;
		}

		$first    = isset( $_POST['esc_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_first_name'] ) ) : '';
		$last     = isset( $_POST['esc_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_last_name'] ) ) : '';
		$email    = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';
		$phone    = isset( $_POST['esc_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_phone'] ) ) : '';
		$password = isset( $_POST['esc_password'] ) ? (string) wp_unslash( $_POST['esc_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$confirm  = isset( $_POST['esc_password_confirm'] ) ? (string) wp_unslash( $_POST['esc_password_confirm'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$role     = isset( $_POST['esc_role'] ) ? sanitize_key( wp_unslash( $_POST['esc_role'] ) ) : '';
		$company  = isset( $_POST['esc_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_company_name'] ) ) : '';
		$redirect = ESC_Portal_Helpers::requested_redirect();
		$sticky   = array(
			'first_name'   => $first,
			'last_name'    => $last,
			'email'        => $email,
			'phone'        => $phone,
			'role'         => $role,
			'company_name' => $company,
		);

		if ( ! ESC_Portal_Rate_Limit::allow( 'register' ) ) {
			self::fail_register( $fallback, 'rate-limited', $sticky );
		}

		if ( ! in_array( $role, ESC_Portal_Users::public_roles(), true ) ) {
			self::fail_register( $fallback, 'role-required', $sticky );
		}

		if ( ! $first || ! $last || ! $email || ! $phone || ! $password ) {
			self::fail_register( $fallback, 'required', $sticky );
		}

		if ( ESC_Portal_Users::ROLE_EMPLOYER === $role && ! $company ) {
			self::fail_register( $fallback, 'company-required', $sticky );
		}

		if ( ! is_email( $email ) ) {
			self::fail_register( $fallback, 'invalid-email', $sticky );
		}

		if ( $password !== $confirm ) {
			self::fail_register( $fallback, 'password-mismatch', $sticky );
		}

		if ( ! ESC_Portal_Users::is_strong_password( $password ) ) {
			self::fail_register( $fallback, 'weak-password', $sticky );
		}

		$status = ESC_Portal_Users::STATUS_PENDING_EMAIL;

		$user_id = ESC_Portal_Users::create(
			array(
				'email'        => $email,
				'password'     => $password,
				'first_name'   => $first,
				'last_name'    => $last,
				'phone'        => $phone,
				'role'         => $role,
				'company_name' => $company,
				'status'       => $status,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			$code = 'esc_exists' === $user_id->get_error_code() ? 'email-exists' : 'required';
			self::fail_register( $fallback, $code, $sticky );
		}

		ESC_Portal_Helpers::forget_form();
		self::issue_verification( $user_id );

		if ( $redirect ) {
			ESC_Portal_Users::update_meta( $user_id, 'post_verify_redirect', $redirect );
		}

		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'verify-email', 'info' );
	}

	/**
	 * Redirect back to register with submitted fields restored (never passwords).
	 *
	 * @param string               $url    Register URL.
	 * @param string               $code   Notice code.
	 * @param array<string,string> $sticky Non-secret fields.
	 */
	private static function fail_register( $url, $code, $sticky ) {
		ESC_Portal_Helpers::remember_form( 'register', $sticky );
		ESC_Portal_Helpers::redirect_notice( $url, $code, 'error' );
	}

	/**
	 * Login handler.
	 */
	public static function handle_login() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'login' );

		if ( ! isset( $_POST['esc_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_login_nonce'] ) ), 'esc_login' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$email    = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';
		$password = isset( $_POST['esc_password'] ) ? (string) wp_unslash( $_POST['esc_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$remember = ! empty( $_POST['esc_remember'] );
		$redirect = ESC_Portal_Helpers::requested_redirect();

		if ( self::is_locked( $email ) ) {
			ESC_Portal_Helpers::remember_form( 'login', array( 'email' => $email ) );
			ESC_Portal_Helpers::redirect_notice( $fallback, 'locked-out', 'error' );
		}

		$user = ESC_Portal_Users::authenticate( $email, $password );

		if ( is_wp_error( $user ) ) {
			self::record_failure( $email );
			$map = array(
				'esc_disabled'      => 'account-disabled',
				'esc_pending_email' => 'pending-email',
			);
			$code = isset( $map[ $user->get_error_code() ] ) ? $map[ $user->get_error_code() ] : 'invalid-login';
			ESC_Portal_Helpers::remember_form( 'login', array( 'email' => $email ) );
			ESC_Portal_Helpers::redirect_notice( $fallback, $code, 'error' );
		}

		ESC_Portal_Helpers::forget_form();
		self::clear_failures( $email );
		self::login_user( $user->id, $remember );

		$dest = $redirect ? $redirect : ESC_Portal_Helpers::get_page_url( 'dashboard' );
		ESC_Portal_Helpers::redirect_notice( $dest, 'logged-in', 'success' );
	}

	/**
	 * Logout handler.
	 */
	public static function handle_logout() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'esc_logout' ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		self::logout_user();
		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'logged-out', 'info' );
	}

	/**
	 * Lost password handler.
	 */
	public static function handle_lost_password() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'lost-password' );

		if ( ! isset( $_POST['esc_lost_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_lost_password_nonce'] ) ), 'esc_lost_password' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$email = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';

		if ( ! ESC_Portal_Rate_Limit::allow( 'lost_password', $email ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'reset-sent', 'success' );
		}

		$user  = $email ? ESC_Portal_Users::get_by_email( $email ) : null;

		if ( $user ) {
			$key = wp_generate_password( 32, false, false );
			ESC_Portal_Users::update(
				$user->id,
				array(
					'reset_key'     => hash_hmac( 'sha256', $key, wp_salt( 'auth' ) ),
					'reset_expires' => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS ),
				)
			);
			ESC_Portal_Emails::password_reset( $user, $key );
		}

		ESC_Portal_Helpers::redirect_notice( $fallback, 'reset-sent', 'success' );
	}

	/**
	 * Reset password handler.
	 */
	public static function handle_reset_password() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'reset-password' );

		if ( ! isset( $_POST['esc_reset_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_reset_password_nonce'] ) ), 'esc_reset_password' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$key      = isset( $_POST['esc_key'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_key'] ) ) : '';
		$email    = isset( $_POST['esc_login'] ) ? sanitize_email( wp_unslash( $_POST['esc_login'] ) ) : '';
		$password = isset( $_POST['esc_password'] ) ? (string) wp_unslash( $_POST['esc_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$confirm  = isset( $_POST['esc_password_confirm'] ) ? (string) wp_unslash( $_POST['esc_password_confirm'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$user = self::validate_reset( $key, $email );

		if ( ! $user ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'reset-invalid', 'error' );
		}

		$back = add_query_arg(
			array(
				'key'   => $key,
				'login' => rawurlencode( $email ),
			),
			$fallback
		);

		if ( $password !== $confirm ) {
			ESC_Portal_Helpers::redirect_notice( $back, 'password-mismatch', 'error' );
		}

		if ( ! ESC_Portal_Users::is_strong_password( $password ) ) {
			ESC_Portal_Helpers::redirect_notice( $back, 'weak-password', 'error' );
		}

		ESC_Portal_Users::update(
			$user->id,
			array(
				'password'      => $password,
				'reset_key'     => '',
				'reset_expires' => '',
			)
		);
		self::revoke_user_sessions( $user->id );

		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'password-reset', 'success' );
	}

	/**
	 * Employer/admin application status change.
	 */
	public static function handle_status() {
		$dashboard = ESC_Portal_Helpers::get_page_url( 'dashboard' );

		if ( ! isset( $_POST['esc_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_status_nonce'] ) ), 'esc_portal_status' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'nonce', 'error' );
		}

		$application_id = isset( $_POST['esc_application_id'] ) ? absint( $_POST['esc_application_id'] ) : 0;

		if ( ! $application_id || ! ESC_Portal_Helpers::can_review_application( $application_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		$old_status = (string) get_post_meta( $application_id, '_esc_status', true );
		$new_status = isset( $_POST['esc_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_status'] ) ) : $old_status;
		$notes      = isset( $_POST['esc_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_notes'] ) ) : get_post_meta( $application_id, '_esc_notes', true );
		$statuses   = ESC_Portal_Helpers::application_statuses();

		if ( ! isset( $statuses[ $new_status ] ) ) {
			$new_status = $old_status;
		}

		update_post_meta( $application_id, '_esc_status', $new_status );
		update_post_meta( $application_id, '_esc_notes', $notes );

		if ( $old_status !== $new_status ) {
			ESC_Portal_Emails::status_changed( $application_id, $old_status, $new_status );
		}

		ESC_Portal_Helpers::redirect_notice(
			add_query_arg(
				array(
					'esc_view'    => 'applications',
					'application' => $application_id,
				),
				$dashboard
			),
			'status-saved',
			'success'
		);
	}

	/**
	 * Portal admin updates a portal user.
	 */
	public static function handle_user_update() {
		$dashboard = ESC_Portal_Helpers::get_page_url( 'dashboard' );

		if ( ! ESC_Portal_Users::is_admin() && ! current_user_can( 'manage_options' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		if ( ! isset( $_POST['esc_user_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_user_nonce'] ) ), 'esc_portal_user' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'nonce', 'error' );
		}

		$user_id = isset( $_POST['esc_user_id'] ) ? absint( $_POST['esc_user_id'] ) : 0;
		$role    = isset( $_POST['esc_role'] ) ? sanitize_key( wp_unslash( $_POST['esc_role'] ) ) : '';
		$status  = isset( $_POST['esc_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_status'] ) ) : '';

		if ( ! $user_id || ! ESC_Portal_Users::get( $user_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		$data = array();

		if ( isset( ESC_Portal_Users::roles()[ $role ] ) ) {
			$data['role'] = $role;
		}

		if ( ESC_Portal_Users::is_valid_status( $status ) ) {
			$data['status'] = $status;
		}

		if ( $data ) {
			ESC_Portal_Users::update( $user_id, $data );
		}

		$dest = current_user_can( 'manage_options' ) && isset( $_POST['esc_from_wp'] )
			? admin_url( 'admin.php?page=esc-portal-users' )
			: ESC_Portal_Helpers::dashboard_url( 'users' );

		ESC_Portal_Helpers::redirect_notice( $dest, 'user-updated', 'success' );
	}

	/**
	 * Portal admin permanently deletes another dashboard user.
	 */
	public static function handle_admin_delete_user() {
		$destination = ESC_Portal_Helpers::dashboard_url( 'users' );

		if ( ! self::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'not-allowed', 'error' );
		}

		$user_id = isset( $_POST['esc_user_id'] ) ? absint( $_POST['esc_user_id'] ) : 0;
		$nonce   = isset( $_POST['esc_delete_user_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_delete_user_nonce'] ) ) : '';

		if ( ! $user_id || ! wp_verify_nonce( $nonce, 'esc_admin_delete_user_' . $user_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'nonce', 'error' );
		}

		if ( $user_id === self::current_user_id() ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'cannot-delete-self', 'error' );
		}

		if ( ! ESC_Portal_Users::delete( $user_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'not-allowed', 'error' );
		}

		ESC_Portal_Helpers::redirect_notice( $destination, 'user-deleted', 'success' );
	}

	/**
	 * Portal admin moves an application to Trash.
	 */
	public static function handle_admin_delete_application() {
		$destination = ESC_Portal_Helpers::dashboard_url( 'applications' );

		if ( ! self::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'not-allowed', 'error' );
		}

		$application_id = isset( $_POST['esc_application_id'] ) ? absint( $_POST['esc_application_id'] ) : 0;
		$nonce          = isset( $_POST['esc_delete_application_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_delete_application_nonce'] ) ) : '';
		$application    = $application_id ? get_post( $application_id ) : null;

		if ( ! $application_id || ! wp_verify_nonce( $nonce, 'esc_admin_delete_application_' . $application_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'nonce', 'error' );
		}

		if ( ! $application || 'esc_application' !== $application->post_type || ! wp_trash_post( $application_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $destination, 'not-allowed', 'error' );
		}

		ESC_Portal_Helpers::redirect_notice( $destination, 'application-deleted', 'success' );
	}

	/**
	 * @param string $key   Reset key.
	 * @param string $email Email.
	 * @return object|null
	 */
	public static function validate_reset( $key, $email ) {
		$user = ESC_Portal_Users::get_by_email( $email );

		if ( ! $user || ! $key ) {
			return null;
		}

		$row = ESC_Portal_Users::get_row_raw( $user->id );

		if ( ! $row || empty( $row->reset_key ) || empty( $row->reset_expires ) ) {
			return null;
		}

		if ( strtotime( $row->reset_expires . ' UTC' ) < time() ) {
			return null;
		}

		$expected = hash_hmac( 'sha256', $key, wp_salt( 'auth' ) );

		if ( ! hash_equals( $row->reset_key, $expected ) ) {
			return null;
		}

		return $user;
	}

	/**
	 * Logout URL.
	 *
	 * @return string
	 */
	public static function logout_url() {
		return wp_nonce_url( admin_url( 'admin-post.php?action=esc_logout' ), 'esc_logout' );
	}

	/**
	 * Issue a single-use email verification token.
	 *
	 * @param int $user_id User ID.
	 */
	public static function issue_verification( $user_id ) {
		try {
			$token = bin2hex( random_bytes( 32 ) );
		} catch ( Exception $exception ) {
			$token = wp_generate_password( 64, false, false );
		}

		ESC_Portal_Users::update_meta( $user_id, 'email_verify_hash', hash_hmac( 'sha256', $token, wp_salt( 'auth' ) ) );
		ESC_Portal_Users::update_meta( $user_id, 'email_verify_expires', (string) ( time() + DAY_IN_SECONDS ) );

		$user = ESC_Portal_Users::get( $user_id );
		if ( $user ) {
			ESC_Portal_Emails::verify_email( $user, $token );
		}
	}

	/**
	 * Handle a public verification link on the sign-in page.
	 */
	public static function maybe_handle_public_verify() {
		if ( empty( $_GET['esc_verify'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		self::handle_verify_email();
	}

	/**
	 * Complete email verification.
	 */
	public static function handle_verify_email() {
		$uid   = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login = ESC_Portal_Helpers::get_page_url( 'login' );
		$user  = $uid ? ESC_Portal_Users::get( $uid ) : null;

		if ( ! $user || ! $token ) {
			ESC_Portal_Helpers::redirect_notice( $login, 'reset-invalid', 'error' );
		}

		$hash     = (string) ESC_Portal_Users::get_meta( $user->id, 'email_verify_hash', '' );
		$expires  = (int) ESC_Portal_Users::get_meta( $user->id, 'email_verify_expires', 0 );
		$expected = hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );

		if ( ! $hash || time() > $expires || ! hash_equals( $hash, $expected ) ) {
			ESC_Portal_Helpers::redirect_notice( $login, 'reset-invalid', 'error' );
		}

		ESC_Portal_Users::update_meta( $user->id, 'email_verify_hash', '' );
		ESC_Portal_Users::update_meta( $user->id, 'email_verify_expires', '' );
		ESC_Portal_Users::update_meta( $user->id, 'email_verified_at', current_time( 'mysql' ) );
		ESC_Portal_Users::update( $user->id, array( 'status' => ESC_Portal_Users::STATUS_ACTIVE ) );
		ESC_Portal_Emails::welcome( $user->id );
		self::login_user( $user->id, true );

		$dest = (string) ESC_Portal_Users::get_meta( $user->id, 'post_verify_redirect', '' );
		ESC_Portal_Users::update_meta( $user->id, 'post_verify_redirect', '' );

		if ( ! $dest ) {
			$dest = ESC_Portal_Helpers::get_page_url( 'dashboard' );
		}

		ESC_Portal_Helpers::redirect_notice( $dest, 'email-confirmed', 'success' );
	}

	/**
	 * Public resend of a pending verification email.
	 */
	public static function handle_resend_verification_public() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'login' );

		if ( ! isset( $_POST['esc_resend_public_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_resend_public_nonce'] ) ), 'esc_resend_verification_public' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$email = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';

		if ( ! ESC_Portal_Rate_Limit::allow( 'verify_resend', $email ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'verify-resent', 'success' );
		}

		$user = $email ? ESC_Portal_Users::get_by_email( $email ) : null;

		if ( $user && ESC_Portal_Users::STATUS_PENDING_EMAIL === $user->status ) {
			self::issue_verification( $user->id );
		}

		ESC_Portal_Helpers::redirect_notice( $fallback, 'verify-resent', 'success' );
	}

	/**
	 * Resend activation email from the admin dashboard.
	 */
	public static function handle_resend_verification() {
		$dest = ESC_Portal_Helpers::dashboard_url( 'users' );

		if ( ! self::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( $dest, 'not-allowed', 'error' );
		}

		$user_id = isset( $_POST['esc_user_id'] ) ? absint( $_POST['esc_user_id'] ) : 0;
		$nonce   = isset( $_POST['esc_resend_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_resend_nonce'] ) ) : '';
		$user    = $user_id ? ESC_Portal_Users::get( $user_id ) : null;

		if ( ! $user || ! wp_verify_nonce( $nonce, 'esc_resend_verification_' . $user_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dest, 'nonce', 'error' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'verify_resend', $user->email ) ) {
			ESC_Portal_Helpers::redirect_notice( $dest, 'rate-limited', 'error' );
		}

		self::issue_verification( $user->id );
		ESC_Portal_Helpers::redirect_notice( $dest, 'verify-resent', 'success' );
	}

	/**
	 * Approve or reject a pending job listing.
	 */
	public static function handle_moderate_job() {
		$dest = ESC_Portal_Helpers::dashboard_url( 'jobs' );

		if ( ! self::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( $dest, 'not-allowed', 'error' );
		}

		$job_id = isset( $_POST['esc_job_id'] ) ? absint( $_POST['esc_job_id'] ) : 0;
		$nonce  = isset( $_POST['esc_moderate_job_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_moderate_job_nonce'] ) ) : '';
		$action = isset( $_POST['esc_moderate'] ) ? sanitize_key( wp_unslash( $_POST['esc_moderate'] ) ) : '';
		$job    = $job_id ? get_post( $job_id ) : null;

		if ( ! $job || 'esc_job' !== $job->post_type || ! wp_verify_nonce( $nonce, 'esc_moderate_job_' . $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dest, 'nonce', 'error' );
		}

		$owner = ESC_Portal_Users::get( (int) get_post_meta( $job_id, '_esc_employer_id', true ) );

		if ( 'approve' === $action ) {
			wp_update_post(
				array(
					'ID'          => $job_id,
					'post_status' => 'publish',
				)
			);
			wp_cache_delete( 'esc_job_locations', 'esc_portal' );
			if ( $owner ) {
				ESC_Portal_Emails::job_moderated( $owner, $job_id, 'approved' );
			}
			ESC_Portal_Helpers::redirect_notice( $dest, 'job-approved', 'success' );
		}

		wp_update_post(
			array(
				'ID'          => $job_id,
				'post_status' => 'draft',
			)
		);
		if ( $owner ) {
			ESC_Portal_Emails::job_moderated( $owner, $job_id, 'rejected' );
		}
		ESC_Portal_Helpers::redirect_notice( $dest, 'job-rejected', 'info' );
	}

	/**
	 * @return object|null
	 */
	private static function user_from_cookie() {
		global $wpdb;

		$parts = self::cookie_parts();

		if ( ! $parts ) {
			return null;
		}

		$hash = self::hash_token( $parts['token'] );
		$row  = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . ESC_Portal_Users::sessions_table() . ' WHERE user_id = %d AND token_hash = %s AND expires > UTC_TIMESTAMP() LIMIT 1', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$parts['user_id'],
				$hash
			)
		);

		if ( ! $row ) {
			return null;
		}

		$user = ESC_Portal_Users::get( $parts['user_id'] );

		if ( ! $user || ESC_Portal_Users::STATUS_ACTIVE !== $user->status ) {
			return null;
		}

		return $user;
	}

	/**
	 * @return array{user_id:int,token:string}|null
	 */
	private static function cookie_parts() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return null;
		}

		$raw = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
		$bits = explode( '|', $raw, 2 );

		if ( 2 !== count( $bits ) ) {
			return null;
		}

		return array(
			'user_id' => absint( $bits[0] ),
			'token'   => $bits[1],
		);
	}

	/**
	 * @param string $token Token.
	 * @return string
	 */
	private static function hash_token( $token ) {
		return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
	}

	/**
	 * @param string $value Cookie value.
	 * @param int    $ttl   TTL seconds.
	 */
	private static function set_cookie( $value, $ttl ) {
		$expires = time() + $ttl;
		$secure  = is_ssl();
		$path    = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
		$domain  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				self::COOKIE,
				$value,
				array(
					'expires'  => $expires,
					'path'     => $path,
					'domain'   => $domain,
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			setcookie( self::COOKIE, $value, $expires, $path, $domain, $secure, true );
		}

		if ( $ttl > 0 ) {
			$_COOKIE[ self::COOKIE ] = $value;
		} else {
			unset( $_COOKIE[ self::COOKIE ] );
		}
	}

	/**
	 * @param string $login Login or email.
	 * @return string
	 */
	private static function lock_key( $login ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return 'esc_login_fail_' . hash_hmac( 'sha256', strtolower( $login ) . '|' . $ip, wp_salt( 'nonce' ) );
	}

	/**
	 * @return string
	 */
	private static function ip_lock_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return 'esc_login_ip_' . hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
	}

	/**
	 * @param string $login Login.
	 * @return bool
	 */
	private static function is_locked( $login ) {
		return (int) get_transient( self::lock_key( $login ) ) >= self::MAX_FAILS
			|| (int) get_transient( self::ip_lock_key() ) >= self::IP_MAX_FAILS;
	}

	/**
	 * @param string $login Login.
	 */
	private static function record_failure( $login ) {
		$key      = self::lock_key( $login );
		$ip_key   = self::ip_lock_key();
		$fails    = (int) get_transient( $key ) + 1;
		$ip_fails = (int) get_transient( $ip_key ) + 1;

		set_transient( $key, $fails, self::LOCK_TTL );
		set_transient( $ip_key, $ip_fails, self::LOCK_TTL );
	}

	/**
	 * @param string $login Login.
	 */
	private static function clear_failures( $login ) {
		delete_transient( self::lock_key( $login ) );
	}
}

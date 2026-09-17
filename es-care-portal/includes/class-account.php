<?php
/**
 * Change password and delete portal account.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Account {

	/**
	 * Change password while logged in.
	 */
	public static function handle_change_password() {
		$fallback = ESC_Portal_Helpers::dashboard_url( 'password' );

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_password_nonce'] ) ), 'esc_change_password' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$current = isset( $_POST['esc_current_password'] ) ? (string) wp_unslash( $_POST['esc_current_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$new     = isset( $_POST['esc_password'] ) ? (string) wp_unslash( $_POST['esc_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$confirm = isset( $_POST['esc_password_confirm'] ) ? (string) wp_unslash( $_POST['esc_password_confirm'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$user    = ESC_Portal_Auth::current_user();
		$row     = ESC_Portal_Users::get_row_raw( $user->id );

		if ( ! $row || ! wp_check_password( $current, $row->password ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'wrong-password', 'error' );
		}

		if ( $new !== $confirm ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'password-mismatch', 'error' );
		}

		if ( ! ESC_Portal_Users::is_strong_password( $new ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'weak-password', 'error' );
		}

		ESC_Portal_Users::update( $user->id, array( 'password' => $new ) );
		ESC_Portal_Auth::revoke_user_sessions( $user->id );
		ESC_Portal_Auth::login_user( $user->id, true );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'password-changed', 'success' );
	}

	/**
	 * Permanently remove a seeker or employer portal account.
	 */
	public static function handle_delete_account() {
		$fallback = ESC_Portal_Helpers::dashboard_url( 'password' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ( ! ESC_Portal_Users::is_seeker() && ! ESC_Portal_Users::is_employer() ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_delete_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_delete_nonce'] ) ), 'esc_delete_account' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$confirm = isset( $_POST['esc_delete_confirm'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_delete_confirm'] ) ) : '';

		if ( 'DELETE' !== $confirm ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'required', 'error' );
		}

		$user_id = ESC_Portal_Auth::current_user_id();
		if ( ! ESC_Portal_Users::delete( $user_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'save-failed', 'error' );
		}

		ESC_Portal_Auth::logout_user();
		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'account-deleted', 'info' );
	}
}

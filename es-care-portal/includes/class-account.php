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
	 * Request that staff delete a seeker or employer portal account.
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

		$user = ESC_Portal_Auth::current_user();

		if ( ! $user ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ESC_Portal_Users::deletion_requested( $user->id ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'deletion-pending', 'info' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'delete_request', (string) $user->id ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'rate-limited', 'error' );
		}

		ESC_Portal_Users::update_meta( $user->id, 'deletion_requested_at', current_time( 'mysql' ) );

		$lines = array(
			sprintf(
				/* translators: %s: role label */
				__( 'This %s asked to delete their portal account.', 'es-care-portal' ),
				ESC_Portal_Users::role_label( $user->role )
			),
			sprintf(
				/* translators: %s: full name */
				__( 'Name: %s', 'es-care-portal' ),
				$user->display_name
			),
			sprintf(
				/* translators: %s: email */
				__( 'Email: %s', 'es-care-portal' ),
				$user->email
			),
		);

		if ( ! empty( $user->phone ) ) {
			$lines[] = sprintf(
				/* translators: %s: phone */
				__( 'Phone: %s', 'es-care-portal' ),
				$user->phone
			);
		}

		if ( ! empty( $user->company_name ) ) {
			$lines[] = sprintf(
				/* translators: %s: company name */
				__( 'Company: %s', 'es-care-portal' ),
				$user->company_name
			);
		}

		$lines[] = __( 'Review the account and delete it from Dashboard Users if you approve.', 'es-care-portal' );

		$subject = __( 'Account deletion request', 'es-care-portal' );
		$message = implode( "\n", $lines );

		ESC_Portal_Forms::save_request( (int) $user->id, $user->display_name, $user->email, $subject, $message );
		ESC_Portal_Emails::account_deletion_requested( $user );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'deletion-requested', 'success' );
	}
}

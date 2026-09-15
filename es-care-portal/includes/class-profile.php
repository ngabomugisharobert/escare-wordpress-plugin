<?php
/**
 * Portal profile storage (custom user table).
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Profile {

	/**
	 * Register hooks (bound from Auth::init).
	 */
	public static function init() {}

	/**
	 * Profile values for a portal user.
	 *
	 * @param int $user_id Portal user ID.
	 * @return array
	 */
	public static function get( $user_id ) {
		$user  = ESC_Portal_Users::get( $user_id );
		$certs = ESC_Portal_Users::get_meta( $user_id, 'certifications', array() );
		$avail = ESC_Portal_Users::get_meta( $user_id, 'availability', array() );

		return array(
			'first_name'              => $user ? $user->first_name : '',
			'last_name'               => $user ? $user->last_name : '',
			'email'                   => $user ? $user->email : '',
			'phone'                   => $user ? $user->phone : '',
			'company_name'            => $user ? $user->company_name : '',
			'role'                    => $user ? $user->role : '',
			'city'                    => (string) ESC_Portal_Users::get_meta( $user_id, 'city', '' ),
			'state'                   => (string) ESC_Portal_Users::get_meta( $user_id, 'state', '' ),
			'zip'                     => (string) ESC_Portal_Users::get_meta( $user_id, 'zip', '' ),
			'home_address'            => (string) ESC_Portal_Users::get_meta( $user_id, 'home_address', '' ),
			'years_at_address'        => (string) ESC_Portal_Users::get_meta( $user_id, 'years_at_address', '' ),
			'daytime_phone'           => (string) ESC_Portal_Users::get_meta( $user_id, 'daytime_phone', '' ),
			'evening_phone'           => (string) ESC_Portal_Users::get_meta( $user_id, 'evening_phone', '' ),
			'drivers_license'         => (string) ESC_Portal_Users::get_meta( $user_id, 'drivers_license', '' ),
			'professional_license'    => (string) ESC_Portal_Users::get_meta( $user_id, 'professional_license', '' ),
			'date_of_birth'           => (string) ESC_Portal_Users::get_meta( $user_id, 'date_of_birth', '' ),
			'salary_desired'          => (string) ESC_Portal_Users::get_meta( $user_id, 'salary_desired', '' ),
			'available_to_start'      => (string) ESC_Portal_Users::get_meta( $user_id, 'available_to_start', '' ),
			'over_18'                 => (string) ESC_Portal_Users::get_meta( $user_id, 'over_18', '' ),
			'emergency_name'          => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_name', '' ),
			'emergency_phone'         => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_phone', '' ),
			'emergency_relationship'  => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_relationship', '' ),
			'emergency_address'       => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_address', '' ),
			'emergency_city'          => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_city', '' ),
			'emergency_state'         => (string) ESC_Portal_Users::get_meta( $user_id, 'emergency_state', '' ),
			'certifications'          => is_array( $certs ) ? $certs : array(),
			'certifications_other'    => (string) ESC_Portal_Users::get_meta( $user_id, 'certifications_other', '' ),
			'years_experience'        => (string) ESC_Portal_Users::get_meta( $user_id, 'years_experience', '' ),
			'availability'            => is_array( $avail ) ? $avail : array(),
			'work_authorization'      => (string) ESC_Portal_Users::get_meta( $user_id, 'work_authorization', '' ),
		);
	}

	/**
	 * Save profile for the current portal user.
	 */
	public static function handle_save() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'profile' );

		if ( ESC_Portal_Auth::is_logged_in() && ESC_Portal_Users::is_employer() ) {
			$fallback = ESC_Portal_Helpers::dashboard_url( 'profile' );
		}

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login', array( 'redirect_to' => $fallback ) ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_profile_nonce'] ) ), 'esc_profile' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$user_id = ESC_Portal_Auth::current_user_id();
		$data    = self::sanitize_posted();

		if ( ! $data['first_name'] || ! $data['last_name'] || ! $data['phone'] ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'required', 'error' );
		}

		$update = array(
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name'],
			'phone'      => $data['phone'],
		);

		if ( ESC_Portal_Users::is_employer() ) {
			$update['company_name'] = isset( $_POST['esc_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_company_name'] ) ) : '';

			if ( ! $update['company_name'] ) {
				ESC_Portal_Helpers::redirect_notice( $fallback, 'company-required', 'error' );
			}
		}

		ESC_Portal_Users::update( $user_id, $update );
		self::save_meta( $user_id, $data );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'profile-saved', 'success' );
	}

	/**
	 * Persist extra profile fields.
	 *
	 * @param int   $user_id User ID.
	 * @param array $data    Sanitized data.
	 */
	public static function save_meta( $user_id, $data ) {
		ESC_Portal_Users::update_meta( $user_id, 'city', $data['city'] );
		ESC_Portal_Users::update_meta( $user_id, 'state', $data['state'] );
		ESC_Portal_Users::update_meta( $user_id, 'zip', $data['zip'] );
		ESC_Portal_Users::update_meta( $user_id, 'certifications', $data['certifications'] );
		ESC_Portal_Users::update_meta( $user_id, 'certifications_other', $data['certifications_other'] );
		ESC_Portal_Users::update_meta( $user_id, 'years_experience', $data['years_experience'] );
		ESC_Portal_Users::update_meta( $user_id, 'availability', $data['availability'] );
		ESC_Portal_Users::update_meta( $user_id, 'work_authorization', $data['work_authorization'] );
	}

	/**
	 * Sanitize posted profile/application fields.
	 *
	 * @return array
	 */
	public static function sanitize_posted() {
		$auth      = isset( $_POST['esc_work_authorization'] ) ? sanitize_key( wp_unslash( $_POST['esc_work_authorization'] ) ) : '';
		$auth_opts = ESC_Portal_Helpers::work_auth_options();

		if ( $auth && ! isset( $auth_opts[ $auth ] ) ) {
			$auth = '';
		}

		$years = isset( $_POST['esc_years_experience'] ) ? absint( wp_unslash( $_POST['esc_years_experience'] ) ) : 0;

		if ( $years > 60 ) {
			$years = 60;
		}

		return array(
			'first_name'           => isset( $_POST['esc_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_first_name'] ) ) : '',
			'last_name'            => isset( $_POST['esc_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_last_name'] ) ) : '',
			'email'                => isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '',
			'phone'                => isset( $_POST['esc_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_phone'] ) ) : '',
			'city'                 => isset( $_POST['esc_city'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_city'] ) ) : '',
			'state'                => isset( $_POST['esc_state'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_state'] ) ) : '',
			'zip'                  => isset( $_POST['esc_zip'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_zip'] ) ) : '',
			'cover_letter'         => isset( $_POST['esc_cover_letter'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_cover_letter'] ) ) : '',
			'certifications'       => ESC_Portal_Helpers::sanitize_choice_list(
				isset( $_POST['esc_certifications'] ) ? wp_unslash( $_POST['esc_certifications'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				ESC_Portal_Helpers::certifications()
			),
			'certifications_other' => isset( $_POST['esc_certifications_other'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_certifications_other'] ) ) : '',
			'years_experience'     => (string) $years,
			'availability'         => ESC_Portal_Helpers::sanitize_choice_list(
				isset( $_POST['esc_availability'] ) ? wp_unslash( $_POST['esc_availability'] ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				ESC_Portal_Helpers::availability_options()
			),
			'work_authorization'   => $auth,
		);
	}
}

<?php
/**
 * Job application submit and withdraw for portal job seekers.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Apply {

	/**
	 * Register hooks (bound from Auth::init).
	 */
	public static function init() {}

	/**
	 * Submit an application.
	 */
	public static function handle_apply() {
		$job_id = isset( $_POST['esc_job_id'] ) ? absint( $_POST['esc_job_id'] ) : 0;
		$apply  = ESC_Portal_Helpers::dashboard_url( 'apply' );

		if ( $job_id ) {
			$apply = ESC_Portal_Helpers::get_page_url( 'apply', array( 'job' => $job_id ) );
		}

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login', array( 'redirect_to' => $apply ) ), 'login-required', 'error' );
		}

		if ( ! ESC_Portal_Users::is_seeker() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'dashboard' ), 'seeker-only', 'error' );
		}

		if ( ! isset( $_POST['esc_apply_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_apply_nonce'] ) ), 'esc_apply' ) ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'nonce', 'error' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'apply', (string) ESC_Portal_Auth::current_user_id() ) ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'rate-limited', 'error' );
		}

		if ( ! $job_id || 'esc_job' !== get_post_type( $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'careers' ), 'invalid-job', 'error' );
		}

		if ( ! ESC_Portal_Helpers::is_job_open( $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( get_permalink( $job_id ), 'job-closed', 'error' );
		}

		$user = ESC_Portal_Auth::current_user();

		if ( ESC_Portal_CPT_Application::find_active( $user->id, $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'dashboard' ), 'duplicate', 'error' );
		}

		$data = self::sanitize_application();

		if ( ! $data['first_name'] || ! $data['last_name'] || ! $data['phone'] || ! $data['home_address'] || ! $data['city'] || ! $data['state'] || ! $data['zip'] ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'required', 'error' );
		}

		if ( ! $data['emergency_name'] || ! $data['emergency_phone'] || ! $data['emergency_address'] || ! $data['emergency_city'] ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'required', 'error' );
		}

		if ( ! $data['available_to_start'] || ! $data['over_18'] || '' === $data['years_experience'] || ! $data['work_authorization'] ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'required', 'error' );
		}

		if ( empty( $data['certifications'] ) ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'required', 'error' );
		}

		$doc_types = ESC_Portal_Uploads::document_types();

		foreach ( $doc_types as $doc ) {
			$field = $doc['field'];

			if ( empty( $_FILES[ $field ] ) || empty( $_FILES[ $field ]['tmp_name'] ) ) {
				ESC_Portal_Helpers::redirect_notice( $apply, 'upload-required', 'error' );
			}
		}

		$data['email'] = $user->email;
		$job_title     = get_the_title( $job_id );
		$title         = sprintf(
			/* translators: 1: applicant name, 2: job title */
			__( '%1$s — %2$s', 'es-care-portal' ),
			trim( $data['first_name'] . ' ' . $data['last_name'] ),
			$job_title
		);

		$application_id = wp_insert_post(
			array(
				'post_type'   => 'esc_application',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_author' => 1,
			),
			true
		);

		if ( is_wp_error( $application_id ) || ! $application_id ) {
			ESC_Portal_Helpers::redirect_notice( $apply, 'required', 'error' );
		}

		$stored_files = array();

		foreach ( $doc_types as $doc_key => $doc ) {
			$field  = $doc['field'];
			$stored = ESC_Portal_Uploads::handle_upload( $_FILES[ $field ], $user->id, $application_id, $doc_key ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( is_wp_error( $stored ) ) {
				foreach ( $stored_files as $path ) {
					ESC_Portal_Uploads::delete_file( $path );
				}
				wp_delete_post( $application_id, true );
				$code = 'esc_upload_storage' === $stored->get_error_code() ? 'storage-unavailable' : 'upload-failed';
				ESC_Portal_Helpers::redirect_notice( $apply, $code, 'error' );
			}

			$stored_files[ $doc_key ] = $stored;
			update_post_meta( $application_id, $doc['meta_file'], $stored );
			update_post_meta(
				$application_id,
				$doc['meta_name'],
				isset( $_FILES[ $field ]['name'] ) ? sanitize_file_name( wp_unslash( $_FILES[ $field ]['name'] ) ) : sanitize_file_name( $doc_key )
			);
		}

		$meta_ok = update_post_meta( $application_id, '_esc_job_id', $job_id )
			&& update_post_meta( $application_id, '_esc_user_id', $user->id )
			&& update_post_meta( $application_id, '_esc_status', 'pending' );

		if ( ! $meta_ok ) {
			foreach ( $stored_files as $path ) {
				ESC_Portal_Uploads::delete_file( $path );
			}
			wp_delete_post( $application_id, true );
			ESC_Portal_Helpers::redirect_notice( $apply, 'save-failed', 'error' );
		}

		update_post_meta( $application_id, '_esc_notes', '' );

		ESC_Portal_CPT_Application::save_snapshot( $application_id, $data );
		ESC_Portal_Users::update(
			$user->id,
			array(
				'first_name' => $data['first_name'],
				'last_name'  => $data['last_name'],
				'phone'      => $data['phone'],
			)
		);
		ESC_Portal_Profile::save_meta( $user->id, $data );
		self::save_application_profile_meta( $user->id, $data );

		ESC_Portal_Emails::application_received( $application_id );
		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::dashboard_url( 'apply' ), 'applied', 'success' );
	}

	/**
	 * Sanitize posted application fields.
	 *
	 * @return array
	 */
	public static function sanitize_application() {
		$data = ESC_Portal_Profile::sanitize_posted();

		$full = isset( $_POST['esc_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_full_name'] ) ) : '';

		if ( $full ) {
			$parts = preg_split( '/\s+/', $full, 2 );
			$data['first_name'] = isset( $parts[0] ) ? $parts[0] : '';
			$data['last_name']  = isset( $parts[1] ) ? $parts[1] : $data['first_name'];
		}

		$states = ESC_Portal_Helpers::us_states();
		$state  = isset( $_POST['esc_state'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['esc_state'] ) ) ) : '';
		$estate = isset( $_POST['esc_emergency_state'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['esc_emergency_state'] ) ) ) : '';

		if ( $state && ! isset( $states[ $state ] ) ) {
			$state = sanitize_text_field( $state );
		}

		if ( $estate && ! isset( $states[ $estate ] ) ) {
			$estate = '';
		}

		$over_18 = isset( $_POST['esc_over_18'] ) ? sanitize_key( wp_unslash( $_POST['esc_over_18'] ) ) : '';

		if ( ! in_array( $over_18, array( 'yes', 'no' ), true ) ) {
			$over_18 = '';
		}

		$data['state']                   = $state;
		$data['home_address']            = isset( $_POST['esc_home_address'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_home_address'] ) ) : '';
		$data['years_at_address']        = isset( $_POST['esc_years_at_address'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_years_at_address'] ) ) : '';
		$data['daytime_phone']           = isset( $_POST['esc_daytime_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_daytime_phone'] ) ) : '';
		$data['evening_phone']           = isset( $_POST['esc_evening_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_evening_phone'] ) ) : '';
		$data['professional_license']    = isset( $_POST['esc_professional_license'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_professional_license'] ) ) : '';
		$data['date_of_birth']           = isset( $_POST['esc_date_of_birth'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_date_of_birth'] ) ) : '';
		$data['salary_desired']          = isset( $_POST['esc_salary_desired'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_salary_desired'] ) ) : '';
		$data['available_to_start']      = isset( $_POST['esc_available_to_start'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_available_to_start'] ) ) : '';
		$data['over_18']                 = $over_18;
		$data['emergency_name']          = isset( $_POST['esc_emergency_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_emergency_name'] ) ) : '';
		$data['emergency_phone']         = isset( $_POST['esc_emergency_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_emergency_phone'] ) ) : '';
		$data['emergency_relationship']  = isset( $_POST['esc_emergency_relationship'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_emergency_relationship'] ) ) : '';
		$data['emergency_address']       = isset( $_POST['esc_emergency_address'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_emergency_address'] ) ) : '';
		$data['emergency_city']          = isset( $_POST['esc_emergency_city'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_emergency_city'] ) ) : '';
		$data['emergency_state']         = $estate;

		return $data;
	}

	/**
	 * Persist non-sensitive application details on the seeker profile.
	 *
	 * @param int   $user_id User ID.
	 * @param array $data    Sanitized data.
	 */
	public static function save_application_profile_meta( $user_id, $data ) {
		$keys = array(
			'home_address',
			'years_at_address',
			'daytime_phone',
			'evening_phone',
			'professional_license',
			'date_of_birth',
			'salary_desired',
			'available_to_start',
			'over_18',
			'emergency_name',
			'emergency_phone',
			'emergency_relationship',
			'emergency_address',
			'emergency_city',
			'emergency_state',
		);

		foreach ( $keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				ESC_Portal_Users::update_meta( $user_id, $key, $data[ $key ] );
			}
		}
	}

	/**
	 * Withdraw a pending application.
	 */
	public static function handle_withdraw() {
		$dashboard = ESC_Portal_Helpers::dashboard_url( 'apply' );

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_withdraw_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_withdraw_nonce'] ) ), 'esc_withdraw' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'nonce', 'error' );
		}

		$application_id = isset( $_POST['esc_application_id'] ) ? absint( $_POST['esc_application_id'] ) : 0;
		$user_id        = ESC_Portal_Auth::current_user_id();

		if ( ! $application_id || 'esc_application' !== get_post_type( $application_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		$owner  = (int) get_post_meta( $application_id, '_esc_user_id', true );
		$status = (string) get_post_meta( $application_id, '_esc_status', true );

		if ( $owner !== $user_id || 'pending' !== $status ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		update_post_meta( $application_id, '_esc_status', 'withdrawn' );
		update_post_meta( $application_id, '_esc_withdrawn_at', gmdate( 'c' ) );
		ESC_Portal_Helpers::redirect_notice( $dashboard, 'withdrawn', 'success' );
	}

	/**
	 * Apply URL for a job.
	 *
	 * @param int $job_id Job ID.
	 * @return string
	 */
	public static function apply_url( $job_id ) {
		return ESC_Portal_Helpers::get_page_url( 'apply', array( 'job' => absint( $job_id ) ) );
	}
}

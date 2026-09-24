<?php
/**
 * Application custom post type and query helpers.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_CPT_Application {

	/**
	 * Register the application post type (not public).
	 */
	public static function register() {
		register_post_type(
			'esc_application',
			array(
				'labels'              => array(
					'name'          => __( 'Applications', 'es-care-portal' ),
					'singular_name' => __( 'Application', 'es-care-portal' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'rewrite'             => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}

	/**
	 * Find a non-withdrawn application for a user + job.
	 *
	 * @param int $user_id User ID.
	 * @param int $job_id  Job ID.
	 * @return int Application post ID or 0.
	 */
	public static function find_active( $user_id, $job_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_esc_user_id',
						'value' => (int) $user_id,
					),
					array(
						'key'   => '_esc_job_id',
						'value' => (int) $job_id,
					),
					array(
						'key'     => '_esc_status',
						'value'   => 'withdrawn',
						'compare' => '!=',
					),
				),
			)
		);

		if ( empty( $query->posts ) ) {
			return 0;
		}

		return (int) $query->posts[0];
	}

	/**
	 * Applications for a user.
	 *
	 * @param int $user_id User ID.
	 * @return WP_Post[]
	 */
	public static function for_user( $user_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'meta_key'       => '_esc_user_id',
				'meta_value'     => (int) $user_id,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		return $query->posts;
	}

	/**
	 * Snapshot array stored on an application.
	 *
	 * @param int $application_id Application ID.
	 * @return array
	 */
	public static function get_snapshot( $application_id ) {
		$certs = get_post_meta( $application_id, '_esc_certifications', true );
		$avail = get_post_meta( $application_id, '_esc_availability', true );

		return array(
			'first_name'              => (string) get_post_meta( $application_id, '_esc_first_name', true ),
			'last_name'               => (string) get_post_meta( $application_id, '_esc_last_name', true ),
			'email'                   => (string) get_post_meta( $application_id, '_esc_email', true ),
			'phone'                   => (string) get_post_meta( $application_id, '_esc_phone', true ),
			'home_address'            => (string) get_post_meta( $application_id, '_esc_home_address', true ),
			'city'                    => (string) get_post_meta( $application_id, '_esc_city', true ),
			'state'                   => (string) get_post_meta( $application_id, '_esc_state', true ),
			'zip'                     => (string) get_post_meta( $application_id, '_esc_zip', true ),
			'years_at_address'        => (string) get_post_meta( $application_id, '_esc_years_at_address', true ),
			'daytime_phone'           => (string) get_post_meta( $application_id, '_esc_daytime_phone', true ),
			'evening_phone'           => (string) get_post_meta( $application_id, '_esc_evening_phone', true ),
			'professional_license'    => (string) get_post_meta( $application_id, '_esc_professional_license', true ),
			'date_of_birth'           => (string) get_post_meta( $application_id, '_esc_date_of_birth', true ),
			'salary_desired'          => (string) get_post_meta( $application_id, '_esc_salary_desired', true ),
			'available_to_start'      => (string) get_post_meta( $application_id, '_esc_available_to_start', true ),
			'over_18'                 => (string) get_post_meta( $application_id, '_esc_over_18', true ),
			'emergency_name'          => (string) get_post_meta( $application_id, '_esc_emergency_name', true ),
			'emergency_phone'         => (string) get_post_meta( $application_id, '_esc_emergency_phone', true ),
			'emergency_relationship'  => (string) get_post_meta( $application_id, '_esc_emergency_relationship', true ),
			'emergency_address'       => (string) get_post_meta( $application_id, '_esc_emergency_address', true ),
			'emergency_city'          => (string) get_post_meta( $application_id, '_esc_emergency_city', true ),
			'emergency_state'         => (string) get_post_meta( $application_id, '_esc_emergency_state', true ),
			'cover_letter'            => (string) get_post_meta( $application_id, '_esc_cover_letter', true ),
			'resume_file'             => (string) get_post_meta( $application_id, '_esc_resume_file', true ),
			'resume_name'             => (string) get_post_meta( $application_id, '_esc_resume_name', true ),
			'food_handler_file'       => (string) get_post_meta( $application_id, '_esc_food_handler_file', true ),
			'food_handler_name'       => (string) get_post_meta( $application_id, '_esc_food_handler_name', true ),
			'cpr_first_aid_file'      => (string) get_post_meta( $application_id, '_esc_cpr_first_aid_file', true ),
			'cpr_first_aid_name'      => (string) get_post_meta( $application_id, '_esc_cpr_first_aid_name', true ),
			'license_file'            => (string) get_post_meta( $application_id, '_esc_license_file', true ),
			'license_name'            => (string) get_post_meta( $application_id, '_esc_license_name', true ),
			'certifications'          => is_array( $certs ) ? $certs : array(),
			'certifications_other'    => (string) get_post_meta( $application_id, '_esc_certifications_other', true ),
			'years_experience'        => (string) get_post_meta( $application_id, '_esc_years_experience', true ),
			'availability'            => is_array( $avail ) ? $avail : array(),
			'work_authorization'      => (string) get_post_meta( $application_id, '_esc_work_authorization', true ),
			'job_id'                  => (int) get_post_meta( $application_id, '_esc_job_id', true ),
			'user_id'                 => (int) get_post_meta( $application_id, '_esc_user_id', true ),
			'status'                  => (string) get_post_meta( $application_id, '_esc_status', true ),
			'notes'                   => (string) get_post_meta( $application_id, '_esc_notes', true ),
		);
	}

	/**
	 * Persist snapshot meta.
	 *
	 * @param int   $application_id Application ID.
	 * @param array $data           Snapshot data.
	 */
	public static function save_snapshot( $application_id, $data ) {
		$fields = array(
			'_esc_first_name'             => isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : '',
			'_esc_last_name'              => isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '',
			'_esc_email'                  => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'_esc_phone'                  => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
			'_esc_home_address'           => isset( $data['home_address'] ) ? sanitize_text_field( $data['home_address'] ) : '',
			'_esc_city'                   => isset( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '',
			'_esc_state'                  => isset( $data['state'] ) ? sanitize_text_field( $data['state'] ) : '',
			'_esc_zip'                    => isset( $data['zip'] ) ? sanitize_text_field( $data['zip'] ) : '',
			'_esc_years_at_address'       => isset( $data['years_at_address'] ) ? sanitize_text_field( $data['years_at_address'] ) : '',
			'_esc_daytime_phone'          => isset( $data['daytime_phone'] ) ? sanitize_text_field( $data['daytime_phone'] ) : '',
			'_esc_evening_phone'          => isset( $data['evening_phone'] ) ? sanitize_text_field( $data['evening_phone'] ) : '',
			'_esc_professional_license'   => isset( $data['professional_license'] ) ? sanitize_text_field( $data['professional_license'] ) : '',
			'_esc_date_of_birth'          => isset( $data['date_of_birth'] ) ? sanitize_text_field( $data['date_of_birth'] ) : '',
			'_esc_salary_desired'         => isset( $data['salary_desired'] ) ? sanitize_text_field( $data['salary_desired'] ) : '',
			'_esc_available_to_start'     => isset( $data['available_to_start'] ) ? sanitize_text_field( $data['available_to_start'] ) : '',
			'_esc_over_18'                => isset( $data['over_18'] ) ? sanitize_key( $data['over_18'] ) : '',
			'_esc_emergency_name'         => isset( $data['emergency_name'] ) ? sanitize_text_field( $data['emergency_name'] ) : '',
			'_esc_emergency_phone'        => isset( $data['emergency_phone'] ) ? sanitize_text_field( $data['emergency_phone'] ) : '',
			'_esc_emergency_relationship' => isset( $data['emergency_relationship'] ) ? sanitize_text_field( $data['emergency_relationship'] ) : '',
			'_esc_emergency_address'      => isset( $data['emergency_address'] ) ? sanitize_text_field( $data['emergency_address'] ) : '',
			'_esc_emergency_city'         => isset( $data['emergency_city'] ) ? sanitize_text_field( $data['emergency_city'] ) : '',
			'_esc_emergency_state'        => isset( $data['emergency_state'] ) ? sanitize_text_field( $data['emergency_state'] ) : '',
			'_esc_cover_letter'           => isset( $data['cover_letter'] ) ? sanitize_textarea_field( $data['cover_letter'] ) : '',
			'_esc_certifications_other'   => isset( $data['certifications_other'] ) ? sanitize_text_field( $data['certifications_other'] ) : '',
			'_esc_years_experience'       => isset( $data['years_experience'] ) ? (string) absint( $data['years_experience'] ) : '',
			'_esc_work_authorization'     => isset( $data['work_authorization'] ) ? sanitize_key( $data['work_authorization'] ) : '',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $application_id, $key, $value );
		}

		$certs = ESC_Portal_Helpers::sanitize_choice_list(
			isset( $data['certifications'] ) ? $data['certifications'] : array(),
			ESC_Portal_Helpers::certifications()
		);
		$avail = ESC_Portal_Helpers::sanitize_choice_list(
			isset( $data['availability'] ) ? $data['availability'] : array(),
			ESC_Portal_Helpers::availability_options()
		);

		$auth_opts = ESC_Portal_Helpers::work_auth_options();
		$auth      = $fields['_esc_work_authorization'];

		if ( $auth && ! isset( $auth_opts[ $auth ] ) ) {
			update_post_meta( $application_id, '_esc_work_authorization', '' );
		}

		update_post_meta( $application_id, '_esc_certifications', $certs );
		update_post_meta( $application_id, '_esc_availability', $avail );
	}
}

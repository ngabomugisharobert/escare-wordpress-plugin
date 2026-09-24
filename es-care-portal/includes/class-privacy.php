<?php
/**
 * Privacy exporters, erasers, identity purge, and retention.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Privacy {

	const CRON_HOOK = 'esc_portal_retention_cleanup';
	const IDENTITY_KEYS = array(
		'_esc_ssn',
		'_esc_drivers_license',
	);
	const IDENTITY_USER_META = array(
		'ssn',
		'drivers_license',
	);

	/**
	 * Register privacy and deletion hooks.
	 */
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'delete_resume_for_post' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_retention' ) );
		self::schedule();
	}

	/**
	 * Schedule daily retention.
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Permanently delete stored SSN and driver’s-license values.
	 *
	 * @return int Number of metadata rows removed.
	 */
	public static function purge_identity_fields() {
		global $wpdb;

		$removed = 0;

		foreach ( self::IDENTITY_KEYS as $key ) {
			$removed += (int) $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> ''",
					$key
				)
			);
		}

		$meta = ESC_Portal_Users::meta_table();
		foreach ( self::IDENTITY_USER_META as $key ) {
			$removed += (int) $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$meta} WHERE meta_key = %s AND meta_value <> ''", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$key
				)
			);
		}

		if ( $removed ) {
			ESC_Portal_Health::log( 'info', 'privacy', 'Purged ' . $removed . ' sensitive identity field values.' );
		}

		update_option( 'esc_portal_identity_purged', array( 'count' => $removed, 'at' => current_time( 'mysql' ) ), false );

		return $removed;
	}

	/**
	 * @param array $exporters Exporters.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['es-care-portal'] = array(
			'exporter_friendly_name' => __( 'ES Care Portal', 'es-care-portal' ),
			'callback'               => array( __CLASS__, 'export_data' ),
		);

		return $exporters;
	}

	/**
	 * @param array $erasers Erasers.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['es-care-portal'] = array(
			'eraser_friendly_name' => __( 'ES Care Portal', 'es-care-portal' ),
			'callback'             => array( __CLASS__, 'erase_data' ),
		);

		return $erasers;
	}

	/**
	 * @param string $email Email.
	 * @return array
	 */
	public static function export_data( $email ) {
		$user   = ESC_Portal_Users::get_by_email( $email );
		$groups = array();

		if ( $user ) {
			$groups[] = array(
				'group_id'    => 'esc-portal-user',
				'group_label' => __( 'Portal account', 'es-care-portal' ),
				'item_id'     => 'user-' . $user->id,
				'data'        => array(
					array( 'name' => __( 'Email', 'es-care-portal' ), 'value' => $user->email ),
					array( 'name' => __( 'Name', 'es-care-portal' ), 'value' => $user->display_name ),
					array( 'name' => __( 'Phone', 'es-care-portal' ), 'value' => $user->phone ),
					array( 'name' => __( 'Role', 'es-care-portal' ), 'value' => $user->role ),
					array( 'name' => __( 'Company', 'es-care-portal' ), 'value' => $user->company_name ),
				),
			);

			$apps = ESC_Portal_CPT_Application::for_user( $user->id );
			foreach ( $apps as $app ) {
				$snap     = ESC_Portal_CPT_Application::get_snapshot( $app->ID );
				$groups[] = array(
					'group_id'    => 'esc-portal-application',
					'group_label' => __( 'Job applications', 'es-care-portal' ),
					'item_id'     => 'application-' . $app->ID,
					'data'        => array(
						array( 'name' => __( 'Job', 'es-care-portal' ), 'value' => get_the_title( $snap['job_id'] ) ),
						array( 'name' => __( 'Status', 'es-care-portal' ), 'value' => $snap['status'] ),
						array( 'name' => __( 'City', 'es-care-portal' ), 'value' => $snap['city'] ),
						array( 'name' => __( 'Phone', 'es-care-portal' ), 'value' => $snap['phone'] ),
					),
				);
			}
		}

		return array(
			'data' => $groups,
			'done' => true,
		);
	}

	/**
	 * @param string $email Email.
	 * @return array
	 */
	public static function erase_data( $email ) {
		$user     = ESC_Portal_Users::get_by_email( $email );
		$removed  = false;
		$messages = array();

		if ( $user ) {
			self::anonymize_user_records( $user->id );
			$removed = ESC_Portal_Users::delete( $user->id );
			$messages[] = __( 'Portal account and associated personal data were erased.', 'es-care-portal' );
		}

		return array(
			'items_removed'  => (bool) $removed,
			'items_retained' => false,
			'messages'       => $messages,
			'done'           => true,
		);
	}

	/**
	 * Anonymize applications and delete resume files for a portal user.
	 *
	 * @param int $user_id User ID.
	 */
	public static function anonymize_user_records( $user_id ) {
		$apps = ESC_Portal_CPT_Application::for_user( $user_id );

		foreach ( $apps as $app ) {
			self::anonymize_application( $app->ID );
		}
	}

	/**
	 * @param int $application_id Application ID.
	 */
	public static function anonymize_application( $application_id ) {
		self::delete_resume_for_post( $application_id );

		$fields = array(
			'_esc_first_name'            => __( 'Deleted', 'es-care-portal' ),
			'_esc_last_name'             => __( 'User', 'es-care-portal' ),
			'_esc_email'                 => '',
			'_esc_phone'                 => '',
			'_esc_home_address'          => '',
			'_esc_city'                  => '',
			'_esc_state'                 => '',
			'_esc_zip'                   => '',
			'_esc_daytime_phone'         => '',
			'_esc_evening_phone'         => '',
			'_esc_ssn'                   => '',
			'_esc_drivers_license'       => '',
			'_esc_date_of_birth'         => '',
			'_esc_emergency_name'        => '',
			'_esc_emergency_phone'       => '',
			'_esc_emergency_address'     => '',
			'_esc_emergency_city'        => '',
			'_esc_cover_letter'          => '',
			'_esc_resume_file'           => '',
			'_esc_resume_name'           => '',
			'_esc_food_handler_file'     => '',
			'_esc_food_handler_name'     => '',
			'_esc_cpr_first_aid_file'    => '',
			'_esc_cpr_first_aid_name'    => '',
			'_esc_license_file'          => '',
			'_esc_license_name'          => '',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $application_id, $key, $value );
		}

		wp_update_post(
			array(
				'ID'         => $application_id,
				'post_title' => __( 'Anonymized application', 'es-care-portal' ),
			)
		);
	}

	/**
	 * Delete the stored resume when an application is permanently removed.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function delete_resume_for_post( $post_id ) {
		if ( 'esc_application' !== get_post_type( $post_id ) ) {
			return;
		}

		ESC_Portal_Uploads::delete_application_files( $post_id );
	}

	/**
	 * Retention in years from settings.
	 *
	 * @return int
	 */
	public static function retention_years() {
		$settings = ESC_Portal_Helpers::get_settings();
		$years    = isset( $settings['retention_years'] ) ? absint( $settings['retention_years'] ) : 3;

		return max( 1, min( 10, $years ) );
	}

	/**
	 * Delete or anonymize expired applications.
	 *
	 * @param bool $dry_run Report only.
	 * @return array{expired:int,deleted:int}
	 */
	public static function run_retention( $dry_run = false ) {
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( self::retention_years() * YEAR_IN_SECONDS ) );
		$query  = new WP_Query(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => array( 'publish', 'trash' ),
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'column' => 'post_date_gmt',
						'before' => $cutoff,
					),
				),
			)
		);

		$ids = $query->posts;
		if ( $dry_run ) {
			return array(
				'expired' => (int) $query->found_posts,
				'deleted' => 0,
			);
		}

		$deleted = 0;
		foreach ( $ids as $id ) {
			self::delete_resume_for_post( $id );
			if ( wp_delete_post( $id, true ) ) {
				$deleted++;
			}
		}

		if ( $deleted ) {
			ESC_Portal_Health::log( 'info', 'privacy', 'Retention removed ' . $deleted . ' expired applications.' );
		}

		update_option(
			'esc_portal_retention_last_run',
			array(
				'at'      => current_time( 'mysql' ),
				'deleted' => $deleted,
				'expired' => (int) $query->found_posts,
			),
			false
		);

		return array(
			'expired' => (int) $query->found_posts,
			'deleted' => $deleted,
		);
	}
}

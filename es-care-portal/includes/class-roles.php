<?php
/**
 * Custom roles and capabilities.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Roles {

	/**
	 * Job CPT capabilities.
	 *
	 * @return string[]
	 */
	public static function job_caps() {
		return array(
			'edit_esc_job',
			'read_esc_job',
			'delete_esc_job',
			'edit_esc_jobs',
			'edit_others_esc_jobs',
			'publish_esc_jobs',
			'read_private_esc_jobs',
			'delete_esc_jobs',
			'delete_private_esc_jobs',
			'delete_published_esc_jobs',
			'delete_others_esc_jobs',
			'edit_private_esc_jobs',
			'edit_published_esc_jobs',
			'create_esc_jobs',
		);
	}

	/**
	 * Caps granted to recruiters and administrators.
	 *
	 * @return string[]
	 */
	public static function staff_caps() {
		return array_merge(
			self::job_caps(),
			array(
				'review_esc_applications',
				'edit_esc_applications',
				'read_esc_applications',
				'delete_esc_applications',
			)
		);
	}

	/**
	 * Create roles and grant capabilities.
	 */
	public static function add_roles() {
		remove_role( 'esc_applicant' );
		remove_role( 'esc_recruiter' );

		$admin = get_role( 'administrator' );

		if ( $admin ) {
			foreach ( self::staff_caps() as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove custom roles and caps on uninstall.
	 */
	public static function remove_roles() {
		remove_role( 'esc_applicant' );
		remove_role( 'esc_recruiter' );

		$admin = get_role( 'administrator' );

		if ( $admin ) {
			foreach ( self::staff_caps() as $cap ) {
				$admin->remove_cap( $cap );
			}
		}
	}
}

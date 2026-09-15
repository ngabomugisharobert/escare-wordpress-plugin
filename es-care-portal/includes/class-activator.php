<?php
/**
 * Activation, deactivation, and default pages.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		ESC_Portal_Users::install();
		ESC_Portal_Assessments::install();
		ESC_Portal_Forms::install();
		ESC_Portal_Roles::add_roles();
		ESC_Portal_CPT_Job::register();
		ESC_Portal_CPT_Job::register_taxonomy();
		ESC_Portal_CPT_Application::register();
		self::create_pages();
		self::seed_job_categories();
		self::ensure_settings();
		ESC_Portal_Uploads::ensure_directory();
		flush_rewrite_rules();
		update_option( ESC_Portal_Helpers::VERSION_KEY, ESC_PORTAL_VERSION, false );
	}

	/**
	 * Create tables on existing installs without requiring a re-activate.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( ESC_Portal_Helpers::VERSION_KEY, '' );

		if ( $installed === ESC_PORTAL_VERSION ) {
			return;
		}

		ESC_Portal_Users::install();
		ESC_Portal_Assessments::install();
		ESC_Portal_Forms::install();
		ESC_Portal_Roles::add_roles();
		self::create_pages();
		ESC_Portal_Uploads::ensure_directory();
		update_option( ESC_Portal_Helpers::VERSION_KEY, ESC_PORTAL_VERSION, false );
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create frontend pages once.
	 */
	public static function create_pages() {
		$stored = get_option( ESC_Portal_Helpers::PAGES_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$pages = array(
			'register'        => array(
				'title'    => __( 'Register', 'es-care-portal' ),
				'slug'     => 'register',
				'shortcode'=> '[esc_register]',
			),
			'login'           => array(
				'title'    => __( 'Sign In', 'es-care-portal' ),
				'slug'     => 'sign-in',
				'shortcode'=> '[esc_login]',
			),
			'dashboard'       => array(
				'title'     => __( 'Dashboard', 'es-care-portal' ),
				'slug'      => 'portal-dashboard',
				'shortcode' => '[esc_dashboard]',
			),
			'profile'         => array(
				'title'     => __( 'Profile', 'es-care-portal' ),
				'slug'      => 'portal-profile',
				'shortcode' => '[esc_profile]',
			),
			'careers'         => array(
				'title'    => __( 'Careers', 'es-care-portal' ),
				'slug'     => 'careers',
				'shortcode'=> '[esc_jobs]',
			),
			'apply'           => array(
				'title'     => __( 'Apply', 'es-care-portal' ),
				'slug'      => 'apply',
				'shortcode' => '[esc_apply]',
			),
			'post-job'        => array(
				'title'     => __( 'Post a Job', 'es-care-portal' ),
				'slug'      => 'post-a-job',
				'shortcode' => '[esc_job_form]',
			),
			'lost-password'   => array(
				'title'    => __( 'Lost Password', 'es-care-portal' ),
				'slug'     => 'lost-password',
				'shortcode'=> '[esc_lost_password]',
			),
			'reset-password'  => array(
				'title'    => __( 'Reset Password', 'es-care-portal' ),
				'slug'     => 'reset-password',
				'shortcode'=> '[esc_reset_password]',
			),
		);

		foreach ( $pages as $key => $config ) {
			$existing_id = isset( $stored[ $key ] ) ? absint( $stored[ $key ] ) : 0;

			if ( $existing_id && get_post( $existing_id ) ) {
				continue;
			}

			$found = get_page_by_path( $config['slug'] );

			if ( $found instanceof WP_Post ) {
				$stored[ $key ] = (int) $found->ID;
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $config['title'],
					'post_name'    => $config['slug'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => $config['shortcode'],
					'post_author'  => 1,
				),
				true
			);

			if ( ! is_wp_error( $page_id ) ) {
				$stored[ $key ] = (int) $page_id;
			}
		}

		update_option( ESC_Portal_Helpers::PAGES_KEY, $stored, false );
	}

	/**
	 * Seed default job categories if none exist.
	 */
	public static function seed_job_categories() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'esc_job_category',
				'hide_empty' => false,
				'number'     => 1,
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return;
		}

		$defaults = array(
			'CNA',
			'HHA',
			'RN',
			'LPN',
			'Caregiver',
			'Administrative',
		);

		foreach ( $defaults as $name ) {
			if ( ! term_exists( $name, 'esc_job_category' ) ) {
				wp_insert_term( $name, 'esc_job_category' );
			}
		}
	}

	/**
	 * Ensure settings option exists.
	 */
	public static function ensure_settings() {
		if ( false === get_option( ESC_Portal_Helpers::OPTION_KEY, false ) ) {
			$settings                        = ESC_Portal_Helpers::default_settings();
			$settings['notification_email'] = get_option( 'admin_email' );
			update_option( ESC_Portal_Helpers::OPTION_KEY, $settings, false );
		}
	}
}

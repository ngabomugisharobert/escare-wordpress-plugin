<?php
/**
 * Plugin bootstrap.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Plugin {

	/**
	 * @var ESC_Portal_Plugin|null
	 */
	private static $instance = null;

	/**
	 * @return ESC_Portal_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Wire modules.
	 */
	private function hooks() {
		ESC_Portal_Activator::maybe_upgrade();

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( 'ESC_Portal_CPT_Job', 'register' ) );
		add_action( 'init', array( 'ESC_Portal_CPT_Job', 'register_taxonomy' ) );
		add_action( 'init', array( 'ESC_Portal_CPT_Application', 'register' ) );

		ESC_Portal_CSRF::init();
		ESC_Portal_Auth::init();
		ESC_Portal_Emails::init();
		ESC_Portal_Mail_Queue::init();
		ESC_Portal_Privacy::init();
		ESC_Portal_Profile::init();
		ESC_Portal_Apply::init();
		ESC_Portal_Uploads::init();
		ESC_Portal_Security::init();
		ESC_Portal_Shortcodes::init();
		ESC_Portal_Blocks::init();
		ESC_Portal_CPT_Job::init_admin();
		ESC_Portal_Admin::init();

		add_action( 'elementor/loaded', array( 'ESC_Portal_Elementor', 'init' ) );

		if ( did_action( 'elementor/loaded' ) ) {
			ESC_Portal_Elementor::init();
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'get_pages', array( $this, 'filter_nav_pages' ), 10, 2 );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'es-care-portal', false, dirname( ESC_PORTAL_BASENAME ) . '/languages' );
	}

	/**
	 * Public CSS/JS on portal surfaces.
	 */
	public function enqueue_public() {
		if ( ! $this->should_load_public_assets() ) {
			return;
		}

		wp_enqueue_style(
			'esc-portal',
			ESC_PORTAL_URL . 'public/css/portal.css',
			array(),
			ESC_PORTAL_VERSION
		);

		wp_add_inline_style( 'esc-portal', ESC_Portal_Blocks::theme_css() );

		wp_enqueue_script(
			'esc-portal',
			ESC_PORTAL_URL . 'public/js/portal.js',
			array(),
			ESC_PORTAL_VERSION,
			true
		);

		wp_localize_script(
			'esc-portal',
			'escPortal',
			array(
				'showPassword'  => __( 'Show password', 'es-care-portal' ),
				'hidePassword'  => __( 'Hide password', 'es-care-portal' ),
				'resumeTooBig'  => __( 'That resume is larger than the allowed file size.', 'es-care-portal' ),
				'zeroResults'   => __( 'No matching rows on this page.', 'es-care-portal' ),
				'resultCount'   => __( '%1$s of %2$s on this page (%3$s total)', 'es-care-portal' ),
				'moderationOk'  => __( 'Approve this listing?', 'es-care-portal' ),
				'verifyResend'  => __( 'Send another verification email?', 'es-care-portal' ),
			)
		);
	}

	/**
	 * Admin CSS/JS on portal screens.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$load   = false;

		if ( $screen && in_array( $screen->post_type, array( 'esc_job', 'esc_application' ), true ) ) {
			$load = true;
		}

		if ( is_string( $hook ) && false !== strpos( $hook, 'esc-' ) ) {
			$load = true;
		}

		if ( ! $load ) {
			return;
		}

		wp_enqueue_style(
			'esc-portal-admin',
			ESC_PORTAL_URL . 'admin/css/admin.css',
			array(),
			ESC_PORTAL_VERSION
		);

		wp_enqueue_script(
			'esc-portal-admin',
			ESC_PORTAL_URL . 'admin/js/admin.js',
			array(),
			ESC_PORTAL_VERSION,
			true
		);
	}

	/**
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( $classes ) {
		if ( $this->should_load_public_assets() ) {
			$classes[] = 'esc-portal';
		}

		if ( is_page() ) {
			$auth_pages = array( 'register', 'login', 'lost-password', 'reset-password' );
			foreach ( $auth_pages as $slug ) {
				if ( get_queried_object_id() === ESC_Portal_Helpers::get_page_id( $slug ) ) {
					$classes[] = 'esc-portal-auth';
					break;
				}
			}
		}

		return $classes;
	}

	/**
	 * @return bool
	 */
	private function should_load_public_assets() {
		if ( is_singular( 'esc_job' ) || is_post_type_archive( 'esc_job' ) || is_tax( 'esc_job_category' ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();
			$tags = array(
				'esc_register',
				'esc_login',
				'esc_dashboard',
				'esc_profile',
				'esc_jobs',
				'esc_apply',
				'esc_job_form',
				'esc_lost_password',
				'esc_reset_password',
				'esc_contact',
				'esc_dash_sidebar',
				'esc_dash_home',
				'esc_dash_view',
				'esc_portal_notice',
			);

			if ( $post ) {
				foreach ( $tags as $tag ) {
					if ( has_shortcode( (string) $post->post_content, $tag ) ) {
						return true;
					}
				}

				// Elementor-built pages store data in post meta, not classic shortcodes.
				if ( get_post_meta( $post->ID, '_elementor_edit_mode', true ) ) {
					return true;
				}
			}

			$portal_ids = array();

			foreach ( ESC_Portal_Helpers::page_slugs() as $slug ) {
				$id = ESC_Portal_Helpers::get_page_id( $slug );

				if ( $id ) {
					$portal_ids[] = $id;
				}
			}

			return in_array( get_queried_object_id(), $portal_ids, true );
		}

		return false;
	}

	/**
	 * Keep utility portal pages out of automatic page lists in the theme nav.
	 *
	 * @param WP_Post[] $pages Pages.
	 * @return WP_Post[]
	 */
	public function filter_nav_pages( $pages ) {
		if ( is_admin() || ! is_array( $pages ) ) {
			return $pages;
		}

		$hide = array();

		foreach ( array( 'dashboard', 'profile', 'apply', 'post-job', 'lost-password', 'reset-password' ) as $slug ) {
			$id = ESC_Portal_Helpers::get_page_id( $slug );

			if ( $id ) {
				$hide[] = $id;
			}
		}

		if ( ! $hide ) {
			return $pages;
		}

		return array_values(
			array_filter(
				$pages,
				function ( $page ) use ( $hide ) {
					return ! in_array( (int) $page->ID, $hide, true );
				}
			)
		);
	}
}

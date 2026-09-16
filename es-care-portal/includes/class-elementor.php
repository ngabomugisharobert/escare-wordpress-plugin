<?php
/**
 * Elementor integration bootstrap.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Elementor {

	/**
	 * Hook registration.
	 */
	public static function init() {
		static $done = false;

		if ( $done ) {
			return;
		}

		$done = true;

		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * @param \Elementor\Elements_Manager $elements_manager Manager.
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'esc-care-portal',
			array(
				'title' => __( 'ES Care Portal', 'es-care-portal' ),
				'icon'  => 'fa fa-heart',
			)
		);
	}

	/**
	 * Enqueue portal CSS in Elementor contexts.
	 */
	public static function enqueue_assets() {
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
				'showPassword' => __( 'Show password', 'es-care-portal' ),
				'hidePassword' => __( 'Hide password', 'es-care-portal' ),
			)
		);
	}

	/**
	 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once ESC_PORTAL_DIR . 'includes/elementor/class-widget-base.php';
		require_once ESC_PORTAL_DIR . 'includes/elementor/class-widget-module.php';

		$widgets_manager->register( new ESC_Portal_Elementor_Widget_Module() );
	}

	/**
	 * Whether Elementor is available.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return did_action( 'elementor/loaded' );
	}
}

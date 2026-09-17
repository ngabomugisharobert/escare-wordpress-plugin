<?php
/**
 * Modular portal blocks shared by shortcodes and Elementor.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Blocks {

	/**
	 * Register modular shortcodes.
	 */
	public static function init() {
		add_shortcode( 'esc_dash_sidebar', array( __CLASS__, 'sidebar' ) );
		add_shortcode( 'esc_dash_home', array( __CLASS__, 'home_tiles' ) );
		add_shortcode( 'esc_dash_view', array( __CLASS__, 'view' ) );
		add_shortcode( 'esc_portal_notice', array( __CLASS__, 'notice' ) );
	}

	/**
	 * @return string
	 */
	public static function notice() {
		return ESC_Portal_Helpers::render_query_notice();
	}

	/**
	 * Role-aware dashboard sidebar.
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public static function sidebar( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'role' => 'auto',
			),
			$atts,
			'esc_dash_sidebar'
		);

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return '';
		}

		$user = ESC_Portal_Auth::current_user();
		$role = self::resolve_role( $atts['role'], $user );

		if ( 'employer' === $role ) {
			$view = ESC_Portal_Helpers::current_dashboard_view( 'employer' );
			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-sidebar">' . ESC_Portal_Helpers::get_template(
				'partials/employer-sidebar',
				array(
					'view' => $view,
				)
			) . '</div>';
		}

		if ( 'seeker' === $role ) {
			$view = ESC_Portal_Helpers::current_dashboard_view( 'seeker' );
			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-sidebar">' . ESC_Portal_Helpers::get_template(
				'partials/seeker-sidebar',
				array(
					'view' => $view,
				)
			) . '</div>';
		}

		return '';
	}

	/**
	 * Home tile grids for seeker or employer.
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public static function home_tiles( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'role' => 'auto',
			),
			$atts,
			'esc_dash_home'
		);

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return '';
		}

		$user = ESC_Portal_Auth::current_user();
		$role = self::resolve_role( $atts['role'], $user );

		if ( 'employer' === $role ) {
			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-home">' . ESC_Portal_Helpers::get_template(
				'partials/employer-home',
				array(
					'user' => $user,
				)
			) . '</div>';
		}

		if ( 'seeker' === $role ) {
			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-home">' . ESC_Portal_Helpers::get_template(
				'partials/seeker-home',
				array(
					'user'             => $user,
					'profile_complete' => ESC_Portal_Helpers::is_profile_complete( $user->id ),
				)
			) . '</div>';
		}

		return '';
	}

	/**
	 * Render a specific dashboard view for the current role.
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public static function view( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'role' => 'auto',
				'view' => '',
			),
			$atts,
			'esc_dash_view'
		);

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return '';
		}

		$user = ESC_Portal_Auth::current_user();
		$role = self::resolve_role( $atts['role'], $user );

		if ( 'employer' === $role ) {
			$view = $atts['view'] ? sanitize_key( $atts['view'] ) : ESC_Portal_Helpers::current_dashboard_view( 'employer' );
			$args = array(
				'user'         => $user,
				'view'         => $view,
				'jobs'         => ESC_Portal_Employer::jobs_for( $user->id )->posts,
				'applications' => array(),
				'profile'      => ESC_Portal_Profile::get( $user->id ),
				'requests'     => ESC_Portal_Forms::requests_for_user( $user->id ),
				'edit_job'     => null,
			);

			$job_ids = wp_list_pluck( $args['jobs'], 'ID' );
			$args['applications'] = self::applications_for_jobs( $job_ids );

			if ( 'post' === $view ) {
				$job_id = isset( $_GET['job'] ) ? absint( $_GET['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $job_id && ESC_Portal_Helpers::can_manage_job( $job_id ) ) {
					$args['edit_job'] = get_post( $job_id );
				}
			}

			$partial = 'partials/employer-' . $view;
			if ( ! file_exists( ESC_PORTAL_DIR . 'public/templates/' . $partial . '.php' ) ) {
				$partial = 'partials/employer-home';
			}

			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-view">' . ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template( $partial, $args ) . '</div>';
		}

		if ( 'seeker' === $role ) {
			$view = $atts['view'] ? sanitize_key( $atts['view'] ) : ESC_Portal_Helpers::current_dashboard_view( 'seeker' );
			$aid  = isset( $_GET['assessment'] ) ? absint( $_GET['assessment'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$args = array(
				'user'             => $user,
				'view'             => $view,
				'profile_complete' => ESC_Portal_Helpers::is_profile_complete( $user->id ),
				'applications'     => ESC_Portal_CPT_Application::for_user( $user->id ),
				'assessments'      => ESC_Portal_Assessments::all_active(),
				'attempts'         => ESC_Portal_Assessments::attempts_for_user( $user->id ),
				'forms'            => ESC_Portal_Forms::all(),
				'requests'         => ESC_Portal_Forms::requests_for_user( $user->id ),
				'profile'          => ESC_Portal_Profile::get( $user->id ),
				'settings'         => ESC_Portal_Helpers::public_settings(),
				'jobs'             => get_posts(
					array(
						'post_type'      => 'esc_job',
						'post_status'    => 'publish',
						'posts_per_page' => 20,
						'meta_key'       => '_esc_job_status',
						'meta_value'     => 'open',
					)
				),
				'assessment'       => null,
				'questions'        => array(),
			);

			if ( 'take' === $view ) {
				$args['assessment'] = ESC_Portal_Assessments::get( $aid );
				$args['questions']  = $args['assessment'] ? ESC_Portal_Assessments::questions( $aid ) : array();
				if ( ! $args['assessment'] || ! $args['questions'] ) {
					$view         = 'assessments';
					$args['view'] = $view;
				}
			}

			$partial = 'partials/seeker-' . $view;
			if ( ! file_exists( ESC_PORTAL_DIR . 'public/templates/' . $partial . '.php' ) ) {
				$partial = 'partials/seeker-home';
			}

			return '<div class="esc-portal-wrap esc-dash-wrap esc-block-view">' . ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template( $partial, $args ) . '</div>';
		}

		return '';
	}

	/**
	 * @param string $role Requested role.
	 * @param object $user Portal user.
	 * @return string
	 */
	private static function resolve_role( $role, $user ) {
		$role = sanitize_key( $role );

		if ( 'seeker' === $role || 'employer' === $role ) {
			if ( 'seeker' === $role && ESC_Portal_Users::is_seeker( $user ) ) {
				return 'seeker';
			}
			if ( 'employer' === $role && ESC_Portal_Users::is_employer( $user ) ) {
				return 'employer';
			}
			return '';
		}

		if ( ESC_Portal_Users::is_employer( $user ) ) {
			return 'employer';
		}

		if ( ESC_Portal_Users::is_seeker( $user ) ) {
			return 'seeker';
		}

		return '';
	}

	/**
	 * @param int[] $job_ids Job IDs.
	 * @return WP_Post[]
	 */
	private static function applications_for_jobs( $job_ids ) {
		if ( empty( $job_ids ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_esc_job_id',
						'value'   => array_map( 'intval', $job_ids ),
						'compare' => 'IN',
					),
				),
			)
		);

		return $query->posts;
	}

	/**
	 * Inline CSS variables from settings.
	 *
	 * @return string
	 */
	public static function theme_css() {
		$s = ESC_Portal_Helpers::get_settings();

		$accent  = ! empty( $s['color_accent'] ) ? $s['color_accent'] : '#4caf50';
		$side    = ! empty( $s['color_sidebar'] ) ? $s['color_sidebar'] : '#66bb6a';
		$header  = ! empty( $s['color_sidebar_header'] ) ? $s['color_sidebar_header'] : '#2e7d32';
		$tile    = ! empty( $s['color_tile'] ) ? $s['color_tile'] : '#4caf50';
		$cta     = ! empty( $s['color_cta'] ) ? $s['color_cta'] : '#2e7d32';

		return '.esc-portal-wrap,.esc-dash-wrap{--esc-teal:' . esc_attr( $accent ) . ';--esc-teal-dark:' . esc_attr( $cta ) . ';--esc-navy:' . esc_attr( $cta ) . ';--esc-side-nav:' . esc_attr( $side ) . ';--esc-side-header:' . esc_attr( $header ) . ';--esc-tile:' . esc_attr( $tile ) . ';--esc-cta:' . esc_attr( $cta ) . ';}'
			. '.esc-side-kicker,.esc-subnav-kicker{background:var(--esc-side-header)!important;}'
			. '.esc-side-nav a,.esc-subnav-links a{background:transparent!important;color:var(--esc-navy)!important;}'
			. '.esc-side-nav a:hover,.esc-subnav-links a:hover{background:#eef7e6!important;}'
			. '.esc-side-nav a[aria-current="page"],.esc-subnav-links a[aria-current="page"],.esc-side-nav a[aria-current="page"]:hover,.esc-subnav-links a[aria-current="page"]:hover{background:var(--esc-cta)!important;color:#fff!important;}'
			. '.esc-subnav-logout,.esc-side-nav a.esc-subnav-logout{color:var(--esc-danger)!important;background:transparent!important;}'
			. '.esc-subnav-logout:hover,.esc-side-nav a.esc-subnav-logout:hover{background:#fef2f2!important;}'
			. '.esc-tile-icon{color:var(--esc-navy)!important;}'
			. '.esc-dash-title{color:var(--esc-navy)!important;}'
			. '.esc-tile,.esc-dash-copy,.esc-dash-panel,.esc-notice--info{color:var(--esc-ink,#212121)!important;}'
			. '.esc-dash-copy a,.esc-notice--info a{color:var(--esc-navy)!important;}'
			. '.esc-tile{border-color:color-mix(in srgb,var(--esc-tile) 35%,#fff)!important;}'
			. '.esc-button:not(.esc-button--danger){background:var(--esc-teal)!important;}'
			. '.esc-button--ghost{color:var(--esc-navy)!important;border-color:var(--esc-navy)!important;background:transparent!important;}'
			. '.esc-modal-close,.esc-modal-close:hover{background:transparent!important;color:#4b5563!important;width:2rem!important;height:2rem!important;min-width:0!important;padding:0!important;border:0!important;border-radius:50%!important;box-shadow:none!important;}'
			. '.esc-modal-close:hover,.esc-modal-close:focus-visible{background:#f3f4f6!important;color:#111827!important;}'
			. '.esc-app-section-head,.esc-app-title{background:var(--esc-teal);}'
			. '.esc-app-title{background:transparent!important;color:var(--esc-navy)!important;border-bottom-color:var(--esc-teal)!important;}';
	}
}

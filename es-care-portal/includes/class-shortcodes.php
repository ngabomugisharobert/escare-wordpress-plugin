<?php
/**
 * Frontend shortcodes.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public static function init() {
		add_shortcode( 'esc_register', array( __CLASS__, 'register_form' ) );
		add_shortcode( 'esc_login', array( __CLASS__, 'login_form' ) );
		add_shortcode( 'esc_dashboard', array( __CLASS__, 'dashboard' ) );
		add_shortcode( 'esc_profile', array( __CLASS__, 'profile' ) );
		add_shortcode( 'esc_jobs', array( __CLASS__, 'jobs' ) );
		add_shortcode( 'esc_apply', array( __CLASS__, 'apply' ) );
		add_shortcode( 'esc_job_form', array( __CLASS__, 'job_form' ) );
		add_shortcode( 'esc_lost_password', array( __CLASS__, 'lost_password' ) );
		add_shortcode( 'esc_reset_password', array( __CLASS__, 'reset_password' ) );
		add_shortcode( 'esc_logout', array( __CLASS__, 'logout_link' ) );
		add_shortcode( 'esc_contact', array( __CLASS__, 'contact_form' ) );
	}

	/**
	 * @return string
	 */
	public static function register_form() {
		if ( ESC_Portal_Auth::is_logged_in() ) {
			return self::already_signed_in();
		}

		$redirect = ESC_Portal_Helpers::requested_redirect();

		if ( ! $redirect && isset( $_GET['job'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect = ESC_Portal_Apply::apply_url( absint( $_GET['job'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'register',
			array(
				'redirect_to' => $redirect,
				'sticky'      => ESC_Portal_Helpers::recall_form( 'register' ),
			)
		);
	}

	/**
	 * @return string
	 */
	public static function login_form() {
		if ( ESC_Portal_Auth::is_logged_in() ) {
			return self::already_signed_in();
		}

		$redirect = ESC_Portal_Helpers::requested_redirect();

		if ( ! $redirect && isset( $_GET['job'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect = ESC_Portal_Apply::apply_url( absint( $_GET['job'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'login',
			array(
				'redirect_to' => $redirect,
				'sticky'      => ESC_Portal_Helpers::recall_form( 'login' ),
			)
		);
	}

	/**
	 * @return string
	 */
	public static function dashboard() {
		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return self::auth_prompt( ESC_Portal_Helpers::get_page_url( 'dashboard' ) );
		}

		$user = ESC_Portal_Auth::current_user();
		$args = array(
			'user' => $user,
		);

		$template = 'dashboard-seeker';

		if ( ESC_Portal_Users::is_employer( $user ) ) {
			$template     = 'dashboard-employer';
			$view         = ESC_Portal_Helpers::current_dashboard_view( 'employer' );
			$args['view'] = $view;
			$args         = array_merge( $args, self::employer_view_args( $user, $view ) );
		} elseif ( ESC_Portal_Users::is_admin( $user ) ) {
			$template     = 'dashboard-admin';
			$view         = ESC_Portal_Helpers::current_dashboard_view( 'admin' );
			$args['view'] = $view;
			$args         = array_merge( $args, self::admin_view_args( $user, $view ) );
		} else {
			$view         = ESC_Portal_Helpers::current_dashboard_view();
			$args['view'] = $view;
			$args         = array_merge( $args, self::seeker_view_args( $user, $view ) );
		}

		$app_id = isset( $_GET['application'] ) ? absint( $_GET['application'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $app_id && ESC_Portal_Helpers::can_review_application( $app_id ) ) {
			$args['review'] = array(
				'id'   => $app_id,
				'snap' => ESC_Portal_CPT_Application::get_snapshot( $app_id ),
			);
		}

		$html = ESC_Portal_Helpers::get_template( $template, $args );

		if ( in_array( $template, array( 'dashboard-seeker', 'dashboard-employer', 'dashboard-admin' ), true ) ) {
			return $html;
		}

		return ESC_Portal_Helpers::render_query_notice() . $html;
	}

	/**
	 * @return string
	 */
	public static function profile() {
		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return self::auth_prompt( ESC_Portal_Helpers::get_page_url( 'profile' ) );
		}

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'profile',
			array(
				'profile' => ESC_Portal_Profile::get( ESC_Portal_Auth::current_user_id() ),
				'user'    => ESC_Portal_Auth::current_user(),
			)
		);
	}

	/**
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public static function jobs( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'count' => 20,
			),
			$atts,
			'esc_jobs'
		);

		$category = isset( $_GET['esc_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['esc_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type     = isset( $_GET['esc_type'] ) ? sanitize_key( wp_unslash( $_GET['esc_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$location = isset( $_GET['esc_location'] ) ? sanitize_text_field( wp_unslash( $_GET['esc_location'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$args = array(
			'post_type'      => 'esc_job',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, absint( $atts['count'] ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$meta_query = array();

		if ( $type && isset( ESC_Portal_Helpers::employment_types()[ $type ] ) ) {
			$meta_query[] = array(
				'key'   => '_esc_employment_type',
				'value' => $type,
			);
		}

		if ( $location ) {
			$meta_query[] = array(
				'key'     => '_esc_location',
				'value'   => $location,
				'compare' => 'LIKE',
			);
		}

		if ( $meta_query ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}

			$args['meta_query'] = $meta_query;
		}

		if ( $category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'esc_job_category',
					'field'    => is_numeric( $category ) ? 'term_id' : 'slug',
					'terms'    => $category,
				),
			);
		}

		$query = new WP_Query( $args );

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'jobs',
			array(
				'query'      => $query,
				'category'   => $category,
				'type'       => $type,
				'location'   => $location,
				'categories' => get_terms(
					array(
						'taxonomy'   => 'esc_job_category',
						'hide_empty' => true,
					)
				),
				'locations'  => ESC_Portal_CPT_Job::distinct_locations(),
				'types'      => ESC_Portal_Helpers::employment_types(),
			)
		);
	}

	/**
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public static function apply( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'esc_apply'
		);

		$job_id = absint( $atts['id'] );

		if ( ! $job_id && isset( $_GET['job'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$job_id = absint( $_GET['job'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( ! $job_id || 'esc_job' !== get_post_type( $job_id ) ) {
			return '<div class="esc-portal-wrap">' . ESC_Portal_Helpers::render_query_notice() . '<p class="esc-notice esc-notice--error">' . esc_html__( 'Select a job from Careers to apply.', 'es-care-portal' ) . '</p></div>';
		}

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return self::auth_prompt( ESC_Portal_Apply::apply_url( $job_id ), $job_id );
		}

		if ( ! ESC_Portal_Users::is_seeker() ) {
			if ( ESC_Portal_Users::is_employer() ) {
				$message = __( 'You are signed in as an employer, so you cannot access job seeker pages.', 'es-care-portal' );
			} elseif ( ESC_Portal_Users::is_admin() ) {
				$message = __( 'You are signed in as a portal admin, so you cannot apply for jobs.', 'es-care-portal' );
			} else {
				$message = __( 'Only job seekers can apply for positions.', 'es-care-portal' );
			}

			return '<div class="esc-portal-wrap"><p class="esc-notice esc-notice--error">' . esc_html( $message ) . '</p><p><a class="esc-button" href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) . '">' . esc_html__( 'Go to dashboard', 'es-care-portal' ) . '</a></p></div>';
		}

		$user_id = ESC_Portal_Auth::current_user_id();
		$active  = ESC_Portal_CPT_Application::find_active( $user_id, $job_id );

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'apply',
			array(
				'job_id'   => $job_id,
				'open'     => ESC_Portal_Helpers::is_job_open( $job_id ),
				'existing' => $active,
				'profile'  => ESC_Portal_Profile::get( $user_id ),
				'settings' => ESC_Portal_Helpers::public_settings(),
				'user'     => ESC_Portal_Auth::current_user(),
			)
		);
	}

	/**
	 * Employer job form.
	 *
	 * @return string
	 */
	public static function job_form() {
		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return self::auth_prompt( ESC_Portal_Helpers::get_page_url( 'post-job' ) );
		}

		$user = ESC_Portal_Auth::current_user();

		if ( ! ESC_Portal_Users::is_employer( $user ) && ! ESC_Portal_Users::is_admin( $user ) ) {
			return '<div class="esc-portal-wrap"><p class="esc-notice esc-notice--error">' . esc_html__( 'You are signed in as a job seeker, so you cannot access employer pages.', 'es-care-portal' ) . '</p><p><a class="esc-button" href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) . '">' . esc_html__( 'Go to dashboard', 'es-care-portal' ) . '</a></p></div>';
		}

		$job_id = isset( $_GET['job'] ) ? absint( $_GET['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$job    = $job_id ? get_post( $job_id ) : null;

		if ( $job_id && ( ! $job || ! ESC_Portal_Helpers::can_manage_job( $job_id ) ) ) {
			return '<div class="esc-portal-wrap"><p class="esc-notice esc-notice--error">' . esc_html__( 'You cannot edit that job.', 'es-care-portal' ) . '</p></div>';
		}

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'job-form',
			array(
				'job' => $job,
			)
		);
	}

	/**
	 * @return string
	 */
	public static function lost_password() {
		if ( ESC_Portal_Auth::is_logged_in() ) {
			return self::already_signed_in();
		}

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template( 'lost-password' );
	}

	/**
	 * @return string
	 */
	public static function reset_password() {
		$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login = isset( $_GET['login'] ) ? sanitize_email( wp_unslash( $_GET['login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$valid = (bool) ESC_Portal_Auth::validate_reset( $key, $login );

		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'reset-password',
			array(
				'key'   => $key,
				'login' => $login,
				'valid' => $valid,
			)
		);
	}

	/**
	 * Public Contact Us form. No login required.
	 *
	 * @return string
	 */
	public static function contact_form() {
		return ESC_Portal_Helpers::render_query_notice() . ESC_Portal_Helpers::get_template(
			'contact',
			array(
				'user'   => ESC_Portal_Auth::current_user(),
				'sticky' => ESC_Portal_Helpers::recall_form( 'contact' ),
			)
		);
	}

	/**
	 * @return string
	 */
	public static function logout_link() {
		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			return '';
		}

		return '<a class="esc-logout" href="' . esc_url( ESC_Portal_Auth::logout_url() ) . '">' . esc_html__( 'Sign out', 'es-care-portal' ) . '</a>';
	}

	/**
	 * Load only the employer view that is currently on screen.
	 *
	 * @param object $user Portal user.
	 * @param string $view View key.
	 * @return array
	 */
	private static function employer_view_args( $user, $view ) {
		$args = array(
			'jobs'         => array(),
			'jobs_total'   => 0,
			'applications' => array(),
			'profile'      => array(),
			'requests'     => array(),
			'edit_job'     => null,
			'settings'     => ESC_Portal_Helpers::public_settings(),
			'table_req'    => ESC_Portal_Helpers::table_request( array( 'date', 'title', 'status' ) ),
		);

		if ( 'profile' === $view ) {
			$args['profile'] = ESC_Portal_Profile::get( $user->id );
		}

		if ( 'request' === $view ) {
			$args['requests'] = ESC_Portal_Forms::requests_for_user( $user->id );
		}

		if ( 'post' === $view ) {
			$job_id = isset( $_GET['job'] ) ? absint( $_GET['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $job_id && ESC_Portal_Helpers::can_manage_job( $job_id ) ) {
				$args['edit_job'] = get_post( $job_id );
			}
		}

		if ( 'jobs' === $view ) {
			$req        = $args['table_req'];
			$jobs_query = ESC_Portal_Employer::jobs_for(
				$user->id,
				false,
				array(
					'posts_per_page' => $req['number'],
					'paged'          => $req['paged'],
					's'              => $req['search'],
				)
			);
			$args['jobs']       = $jobs_query->posts;
			$args['jobs_total'] = (int) $jobs_query->found_posts;
			$job_ids            = get_posts(
				array(
					'post_type'      => 'esc_job',
					'post_status'    => array( 'publish', 'pending', 'draft' ),
					'posts_per_page' => 200,
					'fields'         => 'ids',
					'meta_key'       => '_esc_employer_id',
					'meta_value'     => (int) $user->id,
				)
			);
			$args['applications'] = self::applications_for_jobs( $job_ids, $req );
			$args['apps_total']   = self::applications_count( $job_ids );
		}

		return $args;
	}

	/**
	 * Load only the admin view that is currently on screen.
	 *
	 * @param object $user Portal user.
	 * @param string $view View key.
	 * @return array
	 */
	private static function admin_view_args( $user, $view ) {
		$req  = ESC_Portal_Helpers::table_request( array( 'created_at', 'email', 'role', 'last_name', 'status', 'date', 'title', 'subject', 'name' ) );
		$args = array(
			'table_req'       => $req,
			'users'           => array(),
			'users_total'     => 0,
			'jobs'            => array(),
			'jobs_total'      => 0,
			'applications'    => array(),
			'apps_total'      => 0,
			'requests'        => array(),
			'requests_total'  => 0,
			'user_counts'     => ESC_Portal_Users::counts_by_role(),
			'home_metrics'    => array(),
			'employers'       => array(),
		);

		if ( 'home' === $view ) {
			$args['home_metrics'] = self::admin_home_metrics();
			return $args;
		}

		if ( 'users' === $view ) {
			$args['users'] = ESC_Portal_Users::query(
				array(
					'number'  => $req['number'],
					'offset'  => $req['offset'],
					'search'  => $req['search'],
					'role'    => $req['role'],
					'status'  => $req['status'],
					'orderby' => $req['orderby'],
					'order'   => $req['order'],
				)
			);
			$args['users_total'] = ESC_Portal_Users::query_count(
				array(
					'search' => $req['search'],
					'role'   => $req['role'],
					'status' => $req['status'],
				)
			);
		}

		if ( 'jobs' === $view ) {
			$jobs_query = ESC_Portal_Employer::jobs_for(
				$user->id,
				true,
				array(
					'posts_per_page' => $req['number'],
					'paged'          => $req['paged'],
					's'              => $req['search'],
					'meta_status'    => in_array( $req['status'], array( 'open', 'closed' ), true ) ? $req['status'] : '',
					'post_status'    => 'pending' === $req['status'] ? array( 'pending' ) : ( 'rejected' === $req['status'] ? array( 'draft' ) : array( 'publish', 'pending', 'draft' ) ),
				)
			);
			$args['jobs']       = $jobs_query->posts;
			$args['jobs_total'] = (int) $jobs_query->found_posts;
			$employer_ids       = array();

			foreach ( $args['jobs'] as $job ) {
				$employer_ids[] = absint( get_post_meta( $job->ID, '_esc_employer_id', true ) );
			}

			$args['employers'] = ESC_Portal_Users::get_many( $employer_ids );
		}

		if ( 'applications' === $view ) {
			$args['applications'] = self::applications_for_jobs( array(), $req );
			$args['apps_total']   = self::applications_count( array(), $req );
		}

		return $args;
	}

	/**
	 * Lightweight admin home metrics.
	 *
	 * @return array
	 */
	private static function admin_home_metrics() {
		$open = new WP_Query(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_esc_job_status',
				'meta_value'     => 'open',
			)
		);
		$pending_jobs = new WP_Query(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => 'pending',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return array(
			'open_jobs'     => (int) $open->found_posts,
			'pending_jobs'  => (int) $pending_jobs->found_posts,
			'pending_apps'  => self::applications_count( array(), array( 'status' => 'pending' ) ),
			'contacts'      => ESC_Portal_Forms::query_requests_count(),
		);
	}

	/**
	 * Load only the seeker view that is currently on screen.
	 *
	 * @param object $user Portal user.
	 * @param string $view View key.
	 * @return array
	 */
	private static function seeker_view_args( $user, $view ) {
		$args = array(
			'profile_complete' => ESC_Portal_Helpers::is_profile_complete( $user->id ),
			'applications'     => array(),
			'assessments'      => array(),
			'attempts'         => array(),
			'forms'            => array(),
			'requests'         => array(),
			'profile'          => array(),
			'settings'         => ESC_Portal_Helpers::public_settings(),
			'jobs'             => array(),
			'assessment'       => null,
			'questions'        => array(),
		);

		if ( in_array( $view, array( 'home', 'apply' ), true ) ) {
			$args['jobs'] = get_posts(
				array(
					'post_type'      => 'esc_job',
					'post_status'    => 'publish',
					'posts_per_page' => 20,
					'meta_key'       => '_esc_job_status',
					'meta_value'     => 'open',
				)
			);
		}

		if ( in_array( $view, array( 'home', 'apply', 'results' ), true ) ) {
			$args['applications'] = ESC_Portal_CPT_Application::for_user( $user->id );
		}

		if ( in_array( $view, array( 'home', 'assessments', 'take' ), true ) ) {
			$args['assessments'] = ESC_Portal_Assessments::all_active();
		}

		if ( in_array( $view, array( 'home', 'results' ), true ) ) {
			$args['attempts'] = ESC_Portal_Assessments::attempts_for_user( $user->id );
		}

		if ( 'forms' === $view ) {
			$args['forms'] = ESC_Portal_Forms::all();
		}

		if ( 'request' === $view ) {
			$args['requests'] = ESC_Portal_Forms::requests_for_user( $user->id );
		}

		if ( in_array( $view, array( 'apply', 'home' ), true ) ) {
			$args['profile'] = ESC_Portal_Profile::get( $user->id );
		}

		if ( 'take' === $view ) {
			$aid                = isset( $_GET['assessment'] ) ? absint( $_GET['assessment'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['assessment'] = ESC_Portal_Assessments::get( $aid );
			$args['questions']  = $args['assessment'] ? ESC_Portal_Assessments::questions( $aid ) : array();

			if ( ! $args['assessment'] || ! $args['questions'] ) {
				$args['view'] = 'assessments';
			}
		}

		return $args;
	}

	/**
	 * Applications for a set of jobs. Empty job list = all applications (admin).
	 *
	 * @param int[] $job_ids Job IDs.
	 * @param array $req     Optional table request.
	 * @return WP_Post[]
	 */
	private static function applications_for_jobs( $job_ids, $req = array() ) {
		$per_page = ! empty( $req['number'] ) ? min( 100, max( 1, absint( $req['number'] ) ) ) : 25;
		$paged    = ! empty( $req['paged'] ) ? max( 1, absint( $req['paged'] ) ) : 1;
		$args     = array(
			'post_type'      => 'esc_application',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( ! empty( $req['search'] ) ) {
			$args['s'] = $req['search'];
		}

		$meta_query = array();

		if ( ! empty( $req['status'] ) && isset( ESC_Portal_Helpers::application_statuses()[ $req['status'] ] ) ) {
			$meta_query[] = array(
				'key'   => '_esc_status',
				'value' => $req['status'],
			);
		}

		if ( ! empty( $job_ids ) ) {
			$meta_query[] = array(
				'key'     => '_esc_job_id',
				'value'   => array_map( 'intval', $job_ids ),
				'compare' => 'IN',
			);
		} elseif ( is_array( $job_ids ) && empty( $job_ids ) && ! ESC_Portal_Users::is_admin() ) {
			return array();
		}

		if ( $meta_query ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * @param int[] $job_ids Job IDs.
	 * @param array $req     Optional filters.
	 * @return int
	 */
	private static function applications_count( $job_ids, $req = array() ) {
		$args = array(
			'post_type'      => 'esc_application',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		);

		$meta_query = array();

		if ( ! empty( $req['search'] ) ) {
			$args['s'] = $req['search'];
		}

		if ( ! empty( $req['status'] ) && isset( ESC_Portal_Helpers::application_statuses()[ $req['status'] ] ) ) {
			$meta_query[] = array(
				'key'   => '_esc_status',
				'value' => $req['status'],
			);
		}

		if ( ! empty( $job_ids ) ) {
			$meta_query[] = array(
				'key'     => '_esc_job_id',
				'value'   => array_map( 'intval', $job_ids ),
				'compare' => 'IN',
			);
		}

		if ( $meta_query ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );

		return (int) $query->found_posts;
	}

	/**
	 * @return string
	 */
	private static function already_signed_in() {
		$html  = '<div class="esc-portal-wrap">';
		$html .= '<p class="esc-notice esc-notice--info">' . esc_html__( 'You are already signed in to the careers portal.', 'es-care-portal' ) . '</p>';
		$html .= '<p><a class="esc-button" href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) . '">' . esc_html__( 'Go to dashboard', 'es-care-portal' ) . '</a></p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * @param string $redirect Redirect after auth.
	 * @param int    $job_id   Optional job ID.
	 * @return string
	 */
	private static function auth_prompt( $redirect, $job_id = 0 ) {
		$login_args = array( 'redirect_to' => $redirect );
		$reg_args   = array( 'redirect_to' => $redirect );

		if ( $job_id ) {
			$login_args['job'] = $job_id;
			$reg_args['job']   = $job_id;
		}

		$html  = '<div class="esc-portal-wrap">';
		$html .= ESC_Portal_Helpers::render_query_notice();
		$html .= '<div class="esc-card"><h2>' . esc_html__( 'Sign in to continue', 'es-care-portal' ) . '</h2>';
		$html .= '<p>' . esc_html__( 'Access on-demand staffing, 24/7 shift coverage, and openings for vetted RNs, LPNs, and CNAs — for facilities and caregivers.', 'es-care-portal' ) . '</p>';
		$html .= '<p class="esc-actions"><a class="esc-button" href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'login', $login_args ) ) . '">' . esc_html__( 'Sign in', 'es-care-portal' ) . '</a> ';
		$html .= '<a class="esc-button esc-button--ghost" href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'register', $reg_args ) ) . '">' . esc_html__( 'Create an account', 'es-care-portal' ) . '</a></p>';
		$html .= '</div></div>';

		return $html;
	}
}

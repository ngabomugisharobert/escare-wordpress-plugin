<?php
/**
 * Staff admin screens: overview, applications, settings.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Admin {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_filter( 'parent_file', array( __CLASS__, 'parent_file' ) );
		add_filter( 'submenu_file', array( __CLASS__, 'submenu_file' ) );
		add_action( 'admin_post_esc_save_application', array( __CLASS__, 'handle_save_application' ) );
		add_action( 'admin_post_esc_save_settings', array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'admin_post_esc_create_dashboard_user', array( __CLASS__, 'handle_create_dashboard_user' ) );
		add_action( 'admin_post_esc_create_portal_admin', array( __CLASS__, 'handle_create_dashboard_user' ) );
		add_action( 'admin_post_esc_retry_mail', array( __CLASS__, 'handle_retry_mail' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'ES Care Portal', 'es-care-portal' ),
			__( 'ES Care Portal', 'es-care-portal' ),
			'review_esc_applications',
			'esc-portal',
			array( __CLASS__, 'render_overview' ),
			'dashicons-heart',
			26
		);

		add_submenu_page(
			'esc-portal',
			__( 'Overview', 'es-care-portal' ),
			__( 'Overview', 'es-care-portal' ),
			'review_esc_applications',
			'esc-portal',
			array( __CLASS__, 'render_overview' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Jobs', 'es-care-portal' ),
			__( 'Jobs', 'es-care-portal' ),
			'edit_esc_jobs',
			'edit.php?post_type=esc_job'
		);

		add_submenu_page(
			'esc-portal',
			__( 'Job Categories', 'es-care-portal' ),
			__( 'Job Categories', 'es-care-portal' ),
			'edit_esc_jobs',
			'edit-tags.php?taxonomy=esc_job_category&post_type=esc_job'
		);

		add_submenu_page(
			'esc-portal',
			__( 'Applications', 'es-care-portal' ),
			__( 'Applications', 'es-care-portal' ),
			'review_esc_applications',
			'esc-applications',
			array( __CLASS__, 'render_applications' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Application', 'es-care-portal' ),
			__( 'Application', 'es-care-portal' ),
			'review_esc_applications',
			'esc-application',
			array( __CLASS__, 'render_application' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Dashboard Users', 'es-care-portal' ),
			__( 'Dashboard Users', 'es-care-portal' ),
			'manage_options',
			'esc-portal-users',
			array( __CLASS__, 'render_users' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Assessments', 'es-care-portal' ),
			__( 'Assessments', 'es-care-portal' ),
			'review_esc_applications',
			'esc-portal-assessments',
			array( __CLASS__, 'render_assessments' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Employment Forms', 'es-care-portal' ),
			__( 'Employment Forms', 'es-care-portal' ),
			'manage_options',
			'esc-portal-forms',
			array( __CLASS__, 'render_forms' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Service Requests', 'es-care-portal' ),
			__( 'Service Requests', 'es-care-portal' ),
			'review_esc_applications',
			'esc-portal-requests',
			array( __CLASS__, 'render_requests' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Settings', 'es-care-portal' ),
			__( 'Settings', 'es-care-portal' ),
			'manage_options',
			'esc-portal-settings',
			array( __CLASS__, 'render_settings' )
		);

		add_submenu_page(
			'esc-portal',
			__( 'Health', 'es-care-portal' ),
			__( 'Health', 'es-care-portal' ),
			'manage_options',
			'esc-portal-health',
			array( __CLASS__, 'render_health' )
		);

		remove_submenu_page( 'esc-portal', 'esc-application' );
	}

	/**
	 * Keep Jobs under the portal menu.
	 *
	 * @param string $parent Parent file.
	 * @return string
	 */
	public static function parent_file( $parent ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'esc_job' === $screen->post_type ) {
			return 'esc-portal';
		}

		if ( $screen && 'esc_job_category' === $screen->taxonomy ) {
			return 'esc-portal';
		}

		return $parent;
	}

	/**
	 * Highlight the correct submenu.
	 *
	 * @param string $submenu Submenu file.
	 * @return string
	 */
	public static function submenu_file( $submenu ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( isset( $_GET['page'] ) && 'esc-application' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return 'esc-applications';
		}

		if ( $screen && 'esc_job' === $screen->post_type && 'edit-tags' !== $screen->base ) {
			return 'edit.php?post_type=esc_job';
		}

		if ( $screen && 'esc_job_category' === $screen->taxonomy ) {
			return 'edit-tags.php?taxonomy=esc_job_category&post_type=esc_job';
		}

		return $submenu;
	}

	/**
	 * Overview dashboard.
	 */
	public static function render_overview() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		$pending = new WP_Query(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_esc_status',
				'meta_value'     => 'pending',
			)
		);

		$open_jobs = new WP_Query(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => '_esc_job_status',
						'value' => 'open',
					),
				),
			)
		);

		$recent = get_posts(
			array(
				'post_type'      => 'esc_application',
				'post_status'    => 'publish',
				'posts_per_page' => 8,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		ESC_Portal_Helpers::admin_view(
			'overview',
			array(
				'pending_count' => (int) $pending->found_posts,
				'open_jobs'     => (int) $open_jobs->found_posts,
				'recent'        => $recent,
				'user_counts'   => ESC_Portal_Users::counts_by_role(),
				'table_name'    => ESC_Portal_Users::table(),
			)
		);
	}

	/**
	 * Applications list.
	 */
	public static function render_applications() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		require_once ESC_PORTAL_DIR . 'includes/class-applications-list-table.php';

		$table = new ESC_Portal_Applications_List_Table();
		$table->prepare_items();

		ESC_Portal_Helpers::admin_view(
			'applications',
			array(
				'table' => $table,
			)
		);
	}

	/**
	 * Single application.
	 */
	public static function render_application() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $id || 'esc_application' !== get_post_type( $id ) ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Application not found.', 'es-care-portal' ) . '</h1></div>';
			return;
		}

		ESC_Portal_Helpers::admin_view(
			'application-detail',
			array(
				'application_id' => $id,
				'snap'           => ESC_Portal_CPT_Application::get_snapshot( $id ),
			)
		);
	}

	/**
	 * Settings screen.
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Helpers::admin_view(
			'settings',
			array(
				'settings' => ESC_Portal_Helpers::get_settings(),
				'pages'    => get_option( ESC_Portal_Helpers::PAGES_KEY, array() ),
			)
		);
	}

	/**
	 * Operational health.
	 */
	public static function render_health() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Helpers::admin_view(
			'health',
			array(
				'health' => ESC_Portal_Health::snapshot(),
			)
		);
	}

	/**
	 * Retry a queued email.
	 */
	public static function handle_retry_mail() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$id    = isset( $_POST['esc_mail_id'] ) ? absint( $_POST['esc_mail_id'] ) : 0;
		$nonce = isset( $_POST['esc_retry_mail_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_retry_mail_nonce'] ) ) : '';

		if ( ! $id || ! wp_verify_nonce( $nonce, 'esc_retry_mail_' . $id ) ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		ESC_Portal_Mail_Queue::retry( $id );
		wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-health', 'esc_updated' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Save status and notes.
	 */
	public static function handle_save_application() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$application_id = isset( $_POST['esc_application_id'] ) ? absint( $_POST['esc_application_id'] ) : 0;
		$nonce          = isset( $_POST['esc_application_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_application_nonce'] ) ) : '';

		if ( ! $application_id || ! wp_verify_nonce( $nonce, 'esc_save_application_' . $application_id ) ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		if ( 'esc_application' !== get_post_type( $application_id ) ) {
			wp_die( esc_html__( 'Application not found.', 'es-care-portal' ) );
		}

		$old_status = (string) get_post_meta( $application_id, '_esc_status', true );
		$new_status = isset( $_POST['esc_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_status'] ) ) : $old_status;
		$notes      = isset( $_POST['esc_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_notes'] ) ) : '';
		$statuses   = ESC_Portal_Helpers::application_statuses();

		if ( ! isset( $statuses[ $new_status ] ) ) {
			$new_status = $old_status;
		}

		update_post_meta( $application_id, '_esc_status', $new_status );
		update_post_meta( $application_id, '_esc_notes', $notes );

		if ( $old_status !== $new_status ) {
			ESC_Portal_Emails::status_changed( $application_id, $old_status, $new_status );
		}

		$url = add_query_arg(
			array(
				'page'        => 'esc-application',
				'id'          => $application_id,
				'esc_updated' => '1',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Save plugin settings.
	 */
	public static function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$nonce = isset( $_POST['esc_settings_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_settings_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'esc_save_settings' ) ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		$email = isset( $_POST['notification_email'] ) ? sanitize_email( wp_unslash( $_POST['notification_email'] ) ) : '';
		$max   = isset( $_POST['max_file_mb'] ) ? absint( wp_unslash( $_POST['max_file_mb'] ) ) : 5;
		$types = isset( $_POST['allowed_types'] ) ? (array) wp_unslash( $_POST['allowed_types'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$clean_types = array();
		$allowed     = array( 'pdf', 'doc', 'docx' );

		foreach ( $types as $type ) {
			$type = strtolower( sanitize_key( $type ) );

			if ( in_array( $type, $allowed, true ) ) {
				$clean_types[] = $type;
			}
		}

		if ( empty( $clean_types ) ) {
			$clean_types = $allowed;
		}

		if ( $max < 1 ) {
			$max = 1;
		}

		if ( $max > 25 ) {
			$max = 25;
		}

		$defaults = ESC_Portal_Helpers::default_settings();
		$current  = ESC_Portal_Helpers::get_settings();

		$smtp_host       = isset( $_POST['smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_host'] ) ) : '';
		$smtp_port       = isset( $_POST['smtp_port'] ) ? absint( wp_unslash( $_POST['smtp_port'] ) ) : 465;
		$smtp_encryption = isset( $_POST['smtp_encryption'] ) ? sanitize_key( wp_unslash( $_POST['smtp_encryption'] ) ) : 'ssl';
		$smtp_username   = isset( $_POST['smtp_username'] ) ? sanitize_email( wp_unslash( $_POST['smtp_username'] ) ) : '';
		$smtp_from_email = isset( $_POST['smtp_from_email'] ) ? sanitize_email( wp_unslash( $_POST['smtp_from_email'] ) ) : '';
		$smtp_from_name  = isset( $_POST['smtp_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_from_name'] ) ) : '';
		$smtp_password   = isset( $current['smtp_password'] ) ? $current['smtp_password'] : '';
		$password_input  = isset( $_POST['smtp_password'] ) ? (string) wp_unslash( $_POST['smtp_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( $password_input ) {
			$encrypted = ESC_Portal_Emails::encrypt_secret( $password_input );
			if ( $encrypted ) {
				$smtp_password = $encrypted;
			}
		} elseif ( is_string( $smtp_password ) && 0 === strpos( $smtp_password, 'enc:' ) ) {
			$legacy_password = ESC_Portal_Emails::decrypt_secret( $smtp_password );
			$encrypted       = $legacy_password ? ESC_Portal_Emails::encrypt_secret( $legacy_password ) : '';
			if ( $encrypted ) {
				$smtp_password = $encrypted;
			}
		}

		if ( ! in_array( $smtp_encryption, array( 'ssl', 'tls' ), true ) ) {
			$smtp_encryption = 'ssl';
		}

		$smtp_port = max( 1, min( 65535, $smtp_port ) );

		ESC_Portal_Helpers::update_settings(
			array(
				'notification_email'   => $email ? $email : get_option( 'admin_email' ),
				'smtp_enabled'         => isset( $_POST['smtp_enabled'] ) ? 1 : 0,
				'smtp_host'            => $smtp_host,
				'smtp_port'            => $smtp_port,
				'smtp_encryption'      => $smtp_encryption,
				'smtp_username'        => $smtp_username,
				'smtp_password'        => $smtp_password,
				'smtp_from_email'      => $smtp_from_email ? $smtp_from_email : $smtp_username,
				'smtp_from_name'       => $smtp_from_name ? $smtp_from_name : get_bloginfo( 'name' ),
				'max_file_mb'          => $max,
				'allowed_types'        => $clean_types,
				'color_accent'         => self::sanitize_hex( isset( $_POST['color_accent'] ) ? wp_unslash( $_POST['color_accent'] ) : '', $defaults['color_accent'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'color_sidebar'        => self::sanitize_hex( isset( $_POST['color_sidebar'] ) ? wp_unslash( $_POST['color_sidebar'] ) : '', $defaults['color_sidebar'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'color_sidebar_header' => self::sanitize_hex( isset( $_POST['color_sidebar_header'] ) ? wp_unslash( $_POST['color_sidebar_header'] ) : '', $defaults['color_sidebar_header'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'color_tile'           => self::sanitize_hex( isset( $_POST['color_tile'] ) ? wp_unslash( $_POST['color_tile'] ) : '', $defaults['color_tile'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'color_cta'            => self::sanitize_hex( isset( $_POST['color_cta'] ) ? wp_unslash( $_POST['color_cta'] ) : '', $defaults['color_cta'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'tile_seeker'          => self::sanitize_tile_list( isset( $_POST['tile_seeker'] ) ? wp_unslash( $_POST['tile_seeker'] ) : array(), array( 'apply', 'assessments', 'results', 'forms' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'tile_employer'        => self::sanitize_tile_list( isset( $_POST['tile_employer'] ) ? wp_unslash( $_POST['tile_employer'] ) : array(), array( 'post', 'jobs', 'profile', 'membership' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'retention_years'      => max( 1, min( 10, isset( $_POST['retention_years'] ) ? absint( wp_unslash( $_POST['retention_years'] ) ) : 3 ) ),
				'delete_data_on_uninstall' => isset( $_POST['delete_data_on_uninstall'] ) ? 1 : 0,
			)
		);

		$args = array(
			'page'        => 'esc-portal-settings',
			'esc_updated' => '1',
		);

		if ( isset( $_POST['esc_save_and_test'] ) ) {
			$test_email = isset( $_POST['smtp_test_email'] ) ? sanitize_email( wp_unslash( $_POST['smtp_test_email'] ) ) : '';
			$sent       = false;

			if ( $test_email && is_email( $test_email ) ) {
				$sent = ESC_Portal_Emails::send_now(
					$test_email,
					__( 'ES Care Portal SMTP test', 'es-care-portal' ),
					__( 'Success! ES Care Portal sent this message using your built-in SMTP settings.', 'es-care-portal' )
				);
			}

			$args['esc_mail_test'] = $sent ? 'success' : 'failed';
		}

		wp_safe_redirect(
			add_query_arg( $args, admin_url( 'admin.php' ) )
		);
		exit;
	}

	/**
	 * Assessment attempts.
	 */
	public static function render_assessments() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Helpers::admin_view(
			'assessments',
			array(
				'attempts' => ESC_Portal_Assessments::all_attempts(),
			)
		);
	}

	/**
	 * Employment form uploads.
	 */
	public static function render_forms() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Helpers::admin_view(
			'forms',
			array(
				'forms' => ESC_Portal_Forms::all(),
			)
		);
	}

	/**
	 * Candidate service requests.
	 */
	public static function render_requests() {
		if ( ! current_user_can( 'review_esc_applications' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Helpers::admin_view(
			'requests',
			array(
				'requests' => ESC_Portal_Forms::all_requests(),
			)
		);
	}

	/**
	 * Dashboard users list in WP admin (custom table, not wp_users).
	 */
	public static function render_users() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'es-care-portal' ) );
		}

		ESC_Portal_Users::ensure_tables();

		$req = ESC_Portal_Helpers::table_request( array( 'created_at', 'email', 'role', 'last_name', 'status' ) );

		ESC_Portal_Helpers::admin_view(
			'users',
			array(
				'users'      => ESC_Portal_Users::query(
					array(
						'number'  => $req['number'],
						'offset'  => $req['offset'],
						'search'  => $req['search'],
						'role'    => $req['role'],
						'status'  => $req['status'],
						'orderby' => $req['orderby'],
						'order'   => $req['order'],
					)
				),
				'users_total'=> ESC_Portal_Users::query_count(
					array(
						'search' => $req['search'],
						'role'   => $req['role'],
						'status' => $req['status'],
					)
				),
				'table_req'  => $req,
				'counts'     => ESC_Portal_Users::counts_by_role(),
				'table_name' => ESC_Portal_Users::table(),
			)
		);
	}

	/**
	 * Create a dashboard user (job seeker, employer, or portal admin).
	 */
	public static function handle_create_dashboard_user() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$nonce = isset( $_POST['esc_dashboard_user_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_dashboard_user_nonce'] ) ) : '';

		if ( ! $nonce && isset( $_POST['esc_portal_admin_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['esc_portal_admin_nonce'] ) );
		}

		$valid = wp_verify_nonce( $nonce, 'esc_create_dashboard_user' ) || wp_verify_nonce( $nonce, 'esc_create_portal_admin' );

		if ( ! $valid ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		$first    = isset( $_POST['esc_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_first_name'] ) ) : '';
		$last     = isset( $_POST['esc_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_last_name'] ) ) : '';
		$email    = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';
		$phone    = isset( $_POST['esc_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_phone'] ) ) : '';
		$company  = isset( $_POST['esc_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_company_name'] ) ) : '';
		$password = isset( $_POST['esc_password'] ) ? (string) wp_unslash( $_POST['esc_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$role     = isset( $_POST['esc_role'] ) ? sanitize_key( wp_unslash( $_POST['esc_role'] ) ) : ESC_Portal_Users::ROLE_ADMIN;

		if ( ! isset( ESC_Portal_Users::roles()[ $role ] ) ) {
			$role = ESC_Portal_Users::ROLE_ADMIN;
		}

		$url = admin_url( 'admin.php?page=esc-portal-users' );

		if ( ! $first || ! $last || ! is_email( $email ) || ! ESC_Portal_Users::is_strong_password( $password ) ) {
			wp_safe_redirect( add_query_arg( 'esc_error', rawurlencode( __( 'Enter a valid name, email, and an 8+ character password with uppercase, lowercase, and a number.', 'es-care-portal' ) ), $url ) );
			exit;
		}

		if ( ESC_Portal_Users::ROLE_EMPLOYER === $role && ! $company ) {
			wp_safe_redirect( add_query_arg( 'esc_error', rawurlencode( __( 'A company name is required for employer accounts.', 'es-care-portal' ) ), $url ) );
			exit;
		}

		ESC_Portal_Users::ensure_tables();

		$result = ESC_Portal_Users::create(
			array(
				'first_name'   => $first,
				'last_name'    => $last,
				'email'        => $email,
				'phone'        => $phone,
				'company_name' => $company,
				'password'     => $password,
				'role'         => $role,
			)
		);

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( 'esc_error', rawurlencode( $result->get_error_message() ), $url ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( 'esc_updated', '1', $url ) );
		exit;
	}

	/**
	 * Sanitize a hex color for settings.
	 *
	 * @param string $color   Color.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private static function sanitize_hex( $color, $fallback = '#4caf50' ) {
		$color = sanitize_hex_color( is_string( $color ) ? $color : '' );
		return $color ? $color : $fallback;
	}

	/**
	 * @param mixed    $posted  Posted values.
	 * @param string[] $allowed Allowed keys.
	 * @return string[]
	 */
	private static function sanitize_tile_list( $posted, $allowed ) {
		$out = array();

		if ( ! is_array( $posted ) ) {
			return $allowed;
		}

		foreach ( $posted as $item ) {
			$key = sanitize_key( $item );
			if ( in_array( $key, $allowed, true ) ) {
				$out[] = $key;
			}
		}

		return $out ? $out : $allowed;
	}

	/**
	 * Admin notices for saved screens.
	 */
	public static function notices() {
		$error = isset( $_GET['esc_error'] ) ? sanitize_text_field( wp_unslash( $_GET['esc_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $_GET['esc_updated'] ) && ! $error ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || empty( $screen->id ) || false === strpos( $screen->id, 'esc-' ) ) {
			return;
		}

		if ( $error ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
			return;
		}

		$mail_test = isset( $_GET['esc_mail_test'] ) ? sanitize_key( wp_unslash( $_GET['esc_mail_test'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'success' === $mail_test ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'SMTP settings saved and the test email was sent successfully.', 'es-care-portal' ) . '</p></div>';
			return;
		}

		if ( 'failed' === $mail_test ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'SMTP settings were saved, but the test email could not be sent. Verify the host, port, encryption, username, and mailbox password.', 'es-care-portal' ) . '</p></div>';
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Saved.', 'es-care-portal' ) . '</p></div>';
	}
}

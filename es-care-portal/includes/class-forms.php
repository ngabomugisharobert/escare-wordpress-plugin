<?php
/**
 * Employment form downloads and service requests.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Forms {

	/**
	 * Create tables and private storage.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$forms   = $wpdb->prefix . 'esc_forms';
		$reqs    = $wpdb->prefix . 'esc_requests';

		dbDelta(
			"CREATE TABLE {$forms} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title varchar(190) NOT NULL,
				stored varchar(190) NOT NULL,
				original_name varchar(190) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$reqs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				subject varchar(190) NOT NULL,
				message text NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id)
			) {$charset};"
		);

		self::ensure_directory();
	}

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_forms';
	}

	/**
	 * @return string
	 */
	public static function requests_table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_requests';
	}

	/**
	 * @return string
	 */
	public static function directory() {
		$uploads = wp_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . 'esc-forms';
	}

	/**
	 * Guard the forms folder.
	 */
	public static function ensure_directory() {
		$dir = self::directory();

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( ! file_exists( $dir . '/index.php' ) ) {
			file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * @return object[]
	 */
	public static function all() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY created_at DESC' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int $user_id User ID.
	 * @return object[]
	 */
	public static function requests_for_user( $user_id ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::requests_table() . ' WHERE user_id = %d ORDER BY created_at DESC LIMIT 20', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $user_id )
			)
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return object[]
	 */
	public static function all_requests() {
		global $wpdb;

		$rows = $wpdb->get_results(
			'SELECT r.*, u.first_name, u.last_name, u.email FROM ' . self::requests_table() . ' r LEFT JOIN ' . ESC_Portal_Users::table() . ' u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 100' // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * WP admin upload handler.
	 */
	public static function handle_admin_upload() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$nonce = isset( $_POST['esc_form_upload_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_form_upload_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'esc_form_upload' ) ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		$title = isset( $_POST['esc_form_title'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_form_title'] ) ) : '';

		if ( ! $title || empty( $_FILES['esc_form_file']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_error' => '1' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		self::ensure_directory();

		$check = wp_check_filetype_and_ext(
			$_FILES['esc_form_file']['tmp_name'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			isset( $_FILES['esc_form_file']['name'] ) ? $_FILES['esc_form_file']['name'] : '' // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		);

		$ext = ! empty( $check['ext'] ) ? strtolower( $check['ext'] ) : '';

		if ( ! in_array( $ext, array( 'pdf', 'doc', 'docx' ), true ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_error' => '1' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$stored = wp_generate_password( 16, false, false ) . '.' . $ext;
		$dest   = trailingslashit( self::directory() ) . $stored;

		if ( ! @move_uploaded_file( $_FILES['esc_form_file']['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_error' => '1' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		global $wpdb;

		$wpdb->insert(
			self::table(),
			array(
				'title'         => $title,
				'stored'        => $stored,
				'original_name' => isset( $_FILES['esc_form_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['esc_form_file']['name'] ) ) : $stored,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_updated' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Delete a form.
	 */
	public static function handle_admin_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'es-care-portal' ) );
		}

		$id    = isset( $_POST['esc_form_id'] ) ? absint( $_POST['esc_form_id'] ) : 0;
		$nonce = isset( $_POST['esc_form_delete_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_form_delete_nonce'] ) ) : '';

		if ( ! $id || ! wp_verify_nonce( $nonce, 'esc_form_delete_' . $id ) ) {
			wp_die( esc_html__( 'The form expired. Please try again.', 'es-care-portal' ) );
		}

		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $row ) {
			$path = trailingslashit( self::directory() ) . basename( $row->stored );

			if ( is_file( $path ) ) {
				wp_delete_file( $path );
			}

			$wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_updated' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Authenticated download for job seekers and staff.
	 */
	public static function handle_download() {
		$id    = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $id || ! wp_verify_nonce( $nonce, 'esc_download_form_' . $id ) ) {
			wp_die( esc_html__( 'Invalid download link.', 'es-care-portal' ), 403 );
		}

		$allowed = current_user_can( 'review_esc_applications' ) || ESC_Portal_Users::is_seeker() || ESC_Portal_Users::is_admin();

		if ( ! $allowed ) {
			wp_die( esc_html__( 'You are not allowed to download this file.', 'es-care-portal' ), 403 );
		}

		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $row ) {
			wp_die( esc_html__( 'File not found.', 'es-care-portal' ), 404 );
		}

		$path = trailingslashit( self::directory() ) . basename( $row->stored );

		if ( ! is_readable( $path ) ) {
			wp_die( esc_html__( 'File not found.', 'es-care-portal' ), 404 );
		}

		$name = $row->original_name ? sanitize_file_name( $row->original_name ) : basename( $path );
		$mime = wp_check_filetype( $path );

		nocache_headers();
		header( 'Content-Type: ' . ( ! empty( $mime['type'] ) ? $mime['type'] : 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/**
	 * @param int $id Form ID.
	 * @return string
	 */
	public static function download_url( $id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'esc_download_form',
					'form_id' => absint( $id ),
				),
				admin_url( 'admin-post.php' )
			),
			'esc_download_form_' . absint( $id )
		);
	}

	/**
	 * Service request from a job seeker or employer.
	 */
	public static function handle_request() {
		$fallback = ESC_Portal_Helpers::dashboard_url( 'request' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ( ! ESC_Portal_Users::is_seeker() && ! ESC_Portal_Users::is_employer() ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_request_nonce'] ) ), 'esc_service_request' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$subject = isset( $_POST['esc_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_subject'] ) ) : '';
		$message = isset( $_POST['esc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_message'] ) ) : '';

		if ( ! $subject || ! $message ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'required', 'error' );
		}

		global $wpdb;

		$user = ESC_Portal_Auth::current_user();

		$wpdb->insert(
			self::requests_table(),
			array(
				'user_id'    => $user->id,
				'subject'    => $subject,
				'message'    => $message,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s' )
		);

		$settings = ESC_Portal_Helpers::get_settings();

		if ( ! empty( $settings['notification_email'] ) ) {
			wp_mail(
				$settings['notification_email'],
				sprintf(
					/* translators: %s: subject */
					__( 'Service request: %s', 'es-care-portal' ),
					$subject
				),
				sprintf(
					"%s (%s)\n\n%s",
					$user->display_name,
					$user->email,
					$message
				)
			);
		}

		ESC_Portal_Helpers::redirect_notice( $fallback, 'request-sent', 'success' );
	}
}

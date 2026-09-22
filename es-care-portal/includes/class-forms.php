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
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				name varchar(190) NOT NULL DEFAULT '',
				email varchar(190) NOT NULL DEFAULT '',
				subject varchar(190) NOT NULL,
				message text NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY user_created (user_id, created_at),
				KEY created_at (created_at)
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
		if ( defined( 'ESC_PORTAL_PRIVATE_DIR' ) && ESC_PORTAL_PRIVATE_DIR ) {
			return trailingslashit( ESC_PORTAL_PRIVATE_DIR ) . 'esc-forms';
		}

		return trailingslashit( dirname( ABSPATH ) ) . 'esc-portal-private/esc-forms';
	}

	/**
	 * Legacy forms directory.
	 *
	 * @return string
	 */
	public static function legacy_directory() {
		$uploads = wp_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . 'esc-forms';
	}

	/**
	 * Guard the forms folder.
	 */
	public static function ensure_directory() {
		$dir = self::directory();

		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		ESC_Portal_Uploads::write_deny_files( $dir );

		$legacy = self::legacy_directory();
		if ( is_dir( $legacy ) ) {
			ESC_Portal_Uploads::write_deny_files( $legacy );
		}

		return is_writable( $dir );
	}

	/**
	 * Move legacy form files to private storage.
	 *
	 * @return int
	 */
	public static function migrate_existing_files() {
		if ( ! self::ensure_directory() ) {
			return 0;
		}

		$legacy = self::legacy_directory();
		$dest   = self::directory();
		$moved  = 0;

		if ( ! is_dir( $legacy ) || realpath( $legacy ) === realpath( $dest ) ) {
			return 0;
		}

		$files = glob( trailingslashit( $legacy ) . '*.*' );
		if ( ! $files ) {
			return 0;
		}

		foreach ( $files as $file ) {
			$name = basename( $file );
			if ( in_array( $name, array( '.htaccess', 'index.php', 'web.config' ), true ) ) {
				continue;
			}
			if ( @rename( $file, trailingslashit( $dest ) . $name ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$moved++;
			}
		}

		return $moved;
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
	 * @param object $row Request row.
	 * @return object
	 */
	public static function hydrate_request( $row ) {
		$row = (object) $row;
		$name  = isset( $row->display_name ) ? trim( (string) $row->display_name ) : '';
		$email = isset( $row->email ) ? (string) $row->email : '';

		if ( ! $name ) {
			$name = trim( ( isset( $row->first_name ) ? $row->first_name : '' ) . ' ' . ( isset( $row->last_name ) ? $row->last_name : '' ) );
		}

		if ( ! $name && ! empty( $row->contact_name ) ) {
			$name = (string) $row->contact_name;
		}

		if ( ! $email && ! empty( $row->user_email ) ) {
			$email = (string) $row->user_email;
		}

		if ( ! $email && ! empty( $row->contact_email ) ) {
			$email = (string) $row->contact_email;
		}

		$row->display_name = $name ? $name : $email;
		$row->email        = $email;

		return $row;
	}

	/**
	 * @param array $args Query args.
	 * @return object[]
	 */
	public static function query_requests( $args = array() ) {
		global $wpdb;

		$sql  = self::build_requests_sql( $args, false );
		$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$out  = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$out[] = self::hydrate_request( $row );
			}
		}

		return $out;
	}

	/**
	 * @param array $args Query args.
	 * @return int
	 */
	public static function query_requests_count( $args = array() ) {
		global $wpdb;

		return (int) $wpdb->get_var( self::build_requests_sql( $args, true ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param array $args  Query args.
	 * @param bool  $count Count only.
	 * @return string
	 */
	private static function build_requests_sql( $args, $count = false ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'search'  => '',
				'number'  => 25,
				'offset'  => 0,
				'orderby' => 'created_at',
				'order'   => 'DESC',
			)
		);

		$req_table  = self::requests_table();
		$user_table = ESC_Portal_Users::table();
		$where      = array( '1=1' );
		$params     = array();

		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(r.subject LIKE %s OR r.message LIKE %s OR r.name LIKE %s OR r.email LIKE %s OR u.email LIKE %s OR u.first_name LIKE %s OR u.last_name LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$orderby = 'r.created_at';
		$allowed = array(
			'created_at' => 'r.created_at',
			'subject'    => 'r.subject',
			'email'      => 'COALESCE(NULLIF(u.email, \'\'), r.email)',
			'name'       => 'COALESCE(NULLIF(TRIM(CONCAT(IFNULL(u.first_name, \'\'), \' \', IFNULL(u.last_name, \'\'))), \'\'), r.name)',
		);

		if ( isset( $allowed[ $args['orderby'] ] ) ) {
			$orderby = $allowed[ $args['orderby'] ];
		}

		$order  = ( 'ASC' === strtoupper( (string) $args['order'] ) ) ? 'ASC' : 'DESC';
		$select = $count
			? 'COUNT(*)'
			: 'r.id, r.user_id, r.subject, r.message, r.created_at, r.name AS contact_name, r.email AS contact_email, u.first_name, u.last_name, u.email AS user_email, COALESCE(NULLIF(TRIM(CONCAT(IFNULL(u.first_name, \'\'), \' \', IFNULL(u.last_name, \'\'))), \'\'), r.name) AS display_name, COALESCE(NULLIF(u.email, \'\'), r.email) AS email';
		$sql    = "SELECT {$select} FROM {$req_table} r LEFT JOIN {$user_table} u ON u.id = r.user_id WHERE " . implode( ' AND ', $where );

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( ! $count ) {
			$sql .= $wpdb->prepare(
				" ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				max( 1, (int) $args['number'] ),
				max( 0, (int) $args['offset'] )
			);
		}

		return $sql;
	}

	/**
	 * @return object[]
	 */
	public static function all_requests() {
		return self::query_requests(
			array(
				'number' => 100,
				'offset' => 0,
			)
		);
	}

	/**
	 * Absolute path for a stored form file.
	 *
	 * @param string $stored Relative name.
	 * @return string
	 */
	public static function absolute_path( $stored ) {
		$stored = basename( $stored );
		$private = trailingslashit( self::directory() ) . $stored;
		if ( is_readable( $private ) ) {
			return $private;
		}

		$legacy = trailingslashit( self::legacy_directory() ) . $stored;
		return is_readable( $legacy ) ? $legacy : $private;
	}
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

		$ok = $wpdb->insert(
			self::table(),
			array(
				'title'         => $title,
				'stored'        => $stored,
				'original_name' => isset( $_FILES['esc_form_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['esc_form_file']['name'] ) ) : $stored,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( ! $ok ) {
			wp_delete_file( $dest );
			wp_safe_redirect( add_query_arg( array( 'page' => 'esc-portal-forms', 'esc_error' => '1' ), admin_url( 'admin.php' ) ) );
			exit;
		}

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
			$path = self::absolute_path( $row->stored );

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

		$path = self::absolute_path( $row->stored );

		if ( ! is_readable( $path ) ) {
			wp_die( esc_html__( 'File not found.', 'es-care-portal' ), 404 );
		}

		$name = $row->original_name ? sanitize_file_name( $row->original_name ) : basename( $path );
		$mime = wp_check_filetype( $path );

		nocache_headers();
		header( 'Content-Type: ' . ( ! empty( $mime['type'] ) ? $mime['type'] : 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		header( "Content-Security-Policy: sandbox; default-src 'none'" );
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
	 * Service request from a logged-in job seeker or employer.
	 */
	public static function handle_request() {
		$fallback = ESC_Portal_Helpers::dashboard_url( 'request' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ( ! ESC_Portal_Users::is_seeker() && ! ESC_Portal_Users::is_employer() ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_request_nonce'] ) ), 'esc_service_request' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'request', (string) ESC_Portal_Auth::current_user_id() ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'rate-limited', 'error' );
		}

		$user    = ESC_Portal_Auth::current_user();
		$subject = isset( $_POST['esc_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_subject'] ) ) : '';
		$message = isset( $_POST['esc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_message'] ) ) : '';

		if ( ! self::save_request( $user ? (int) $user->id : 0, $user ? $user->display_name : '', $user ? $user->email : '', $subject, $message ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, $subject && $message ? 'save-failed' : 'required', 'error' );
		}

		ESC_Portal_Emails::contact_received( $user->display_name, $user->email, $subject, $message );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'request-sent', 'success' );
	}

	/**
	 * Public Contact Us form — no account required.
	 */
	public static function handle_contact() {
		$fallback = ESC_Portal_Helpers::get_page_url( 'contact' );

		if ( ! empty( $_POST['esc_website'] ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'contact-sent', 'success' );
		}

		if ( ! isset( $_POST['esc_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_contact_nonce'] ) ), 'esc_contact' ) || ! ESC_Portal_CSRF::verify() ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		$name    = isset( $_POST['esc_name'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_name'] ) ) : '';
		$email   = isset( $_POST['esc_email'] ) ? sanitize_email( wp_unslash( $_POST['esc_email'] ) ) : '';
		$subject = isset( $_POST['esc_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_subject'] ) ) : '';
		$message = isset( $_POST['esc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['esc_message'] ) ) : '';
		$user    = ESC_Portal_Auth::current_user();
		$account = $email ? $email : ( $user ? (string) $user->id : '' );

		if ( ! ESC_Portal_Rate_Limit::allow( 'contact', $account ) ) {
			ESC_Portal_Helpers::remember_form(
				'contact',
				array(
					'name'    => $name,
					'email'   => $email,
					'subject' => $subject,
					'message' => $message,
				)
			);
			ESC_Portal_Helpers::redirect_notice( $fallback, 'rate-limited', 'error' );
		}

		if ( $user ) {
			if ( ! $name ) {
				$name = $user->display_name;
			}
			if ( ! $email ) {
				$email = $user->email;
			}
		}

		if ( ! $name || ! is_email( $email ) || ! $subject || ! $message || strlen( $message ) > 4000 ) {
			ESC_Portal_Helpers::remember_form(
				'contact',
				array(
					'name'    => $name,
					'email'   => $email,
					'subject' => $subject,
					'message' => $message,
				)
			);
			ESC_Portal_Helpers::redirect_notice( $fallback, 'required', 'error' );
		}

		if ( ! self::save_request( $user ? (int) $user->id : 0, $name, $email, $subject, $message ) ) {
			ESC_Portal_Helpers::remember_form(
				'contact',
				array(
					'name'    => $name,
					'email'   => $email,
					'subject' => $subject,
					'message' => $message,
				)
			);
			ESC_Portal_Helpers::redirect_notice( $fallback, 'save-failed', 'error' );
		}

		ESC_Portal_Helpers::forget_form();
		ESC_Portal_Emails::contact_received( $name, $email, $subject, $message );
		ESC_Portal_Helpers::redirect_notice( $fallback, 'contact-sent', 'success' );
	}

	/**
	 * @param int    $user_id Sender portal user ID, or 0 for guests.
	 * @param string $name    Sender name.
	 * @param string $email   Sender email.
	 * @param string $subject Subject.
	 * @param string $message Message.
	 * @return bool
	 */
	public static function save_request( $user_id, $name, $email, $subject, $message ) {
		$subject = sanitize_text_field( $subject );
		$message = sanitize_textarea_field( $message );
		$name    = sanitize_text_field( $name );
		$email   = sanitize_email( $email );

		if ( ! $subject || ! $message ) {
			return false;
		}

		global $wpdb;

		$ok = $wpdb->insert(
			self::requests_table(),
			array(
				'user_id'    => absint( $user_id ),
				'name'       => $name,
				'email'      => $email,
				'subject'    => $subject,
				'message'    => $message,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return (bool) $ok;
	}
}

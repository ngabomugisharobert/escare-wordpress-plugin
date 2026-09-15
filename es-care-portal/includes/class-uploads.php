<?php
/**
 * Private resume storage and authenticated downloads.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Uploads {

	/**
	 * Folder name under uploads.
	 *
	 * @var string
	 */
	const DIRNAME = 'esc-resumes';

	/**
	 * Hook download handler.
	 */
	public static function init() {
		add_action( 'admin_post_esc_download_resume', array( __CLASS__, 'handle_download' ) );
		add_action( 'admin_post_nopriv_esc_download_resume', array( __CLASS__, 'handle_download' ) );
	}

	/**
	 * Absolute directory for resumes.
	 *
	 * @return string
	 */
	public static function directory() {
		$uploads = wp_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . self::DIRNAME;
	}

	/**
	 * Create the private directory and guard files.
	 */
	public static function ensure_directory() {
		$dir = self::directory();

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$htaccess = $dir . '/.htaccess';
		$index    = $dir . '/index.php';

		if ( ! file_exists( $htaccess ) ) {
			$rules  = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n";
			$rules .= "<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
			file_put_contents( $htaccess, $rules ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * Allowed MIME types keyed by extension.
	 *
	 * @return array<string,string>
	 */
	public static function allowed_mimes() {
		$settings = ESC_Portal_Helpers::get_settings();
		$allowed  = array();
		$map      = array(
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);

		foreach ( (array) $settings['allowed_types'] as $ext ) {
			$ext = strtolower( sanitize_key( $ext ) );

			if ( isset( $map[ $ext ] ) ) {
				$allowed[ $ext ] = $map[ $ext ];
			}
		}

		return $allowed ? $allowed : $map;
	}

	/**
	 * @return int
	 */
	public static function max_bytes() {
		$settings = ESC_Portal_Helpers::get_settings();

		return max( 1, absint( $settings['max_file_mb'] ) ) * MB_IN_BYTES;
	}

	/**
	 * Handle a resume upload and return stored relative filename.
	 *
	 * @param array $file            $_FILES entry.
	 * @param int   $user_id         User ID.
	 * @param int   $application_id  Application ID.
	 * @return string|WP_Error Relative filename or error.
	 */
	public static function handle_upload( $file, $user_id, $application_id ) {
		self::ensure_directory();

		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'esc_upload_missing', __( 'Please attach a resume or CV.', 'es-care-portal' ) );
		}

		if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error( 'esc_upload_error', __( 'The resume could not be uploaded.', 'es-care-portal' ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;

		if ( $size <= 0 || $size > self::max_bytes() ) {
			return new WP_Error( 'esc_upload_size', __( 'The resume exceeds the maximum file size.', 'es-care-portal' ) );
		}

		$filename = isset( $file['name'] ) ? $file['name'] : '';
		$check    = wp_check_filetype_and_ext( $file['tmp_name'], $filename, self::allowed_mimes() );
		$ext      = ! empty( $check['ext'] ) ? strtolower( $check['ext'] ) : '';
		$type     = ! empty( $check['type'] ) ? $check['type'] : '';
		$allowed  = self::allowed_mimes();

		if ( ! $ext || ! isset( $allowed[ $ext ] ) || ! $type ) {
			return new WP_Error( 'esc_upload_type', __( 'Resume must be a PDF, DOC, or DOCX file.', 'es-care-portal' ) );
		}

		$real_mime = '';

		if ( function_exists( 'finfo_open' ) ) {
			$finfo     = finfo_open( FILEINFO_MIME_TYPE );
			$real_mime = $finfo ? (string) finfo_file( $finfo, $file['tmp_name'] ) : '';

			if ( $finfo ) {
				finfo_close( $finfo );
			}
		}

		$ok_mimes   = array_values( $allowed );
		$ok_mimes[] = 'application/zip'; // some servers report docx as zip.

		if ( $real_mime && ! in_array( $real_mime, $ok_mimes, true ) ) {
			if ( ! ( 'docx' === $ext && in_array( $real_mime, array( 'application/zip', 'application/octet-stream' ), true ) ) ) {
				return new WP_Error( 'esc_upload_mime', __( 'Resume must be a PDF, DOC, or DOCX file.', 'es-care-portal' ) );
			}
		}

		$stored = absint( $user_id ) . '_' . absint( $application_id ) . '_' . wp_generate_password( 16, false, false ) . '.' . $ext;
		$dest   = trailingslashit( self::directory() ) . $stored;

		if ( ! @move_uploaded_file( $file['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return new WP_Error( 'esc_upload_move', __( 'The resume could not be stored.', 'es-care-portal' ) );
		}

		@chmod( $dest, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		return $stored;
	}

	/**
	 * Absolute path for a stored relative name.
	 *
	 * @param string $stored Relative filename.
	 * @return string
	 */
	public static function absolute_path( $stored ) {
		$stored = basename( $stored );

		if ( ! $stored || false !== strpos( $stored, '..' ) ) {
			return '';
		}

		return trailingslashit( self::directory() ) . $stored;
	}

	/**
	 * Authenticated resume download for staff.
	 */
	public static function handle_download() {
		$application_id = isset( $_GET['application_id'] ) ? absint( $_GET['application_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce          = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $application_id || ! wp_verify_nonce( $nonce, 'esc_download_resume_' . $application_id ) ) {
			wp_die( esc_html__( 'Invalid download link.', 'es-care-portal' ), 403 );
		}

		if ( ! current_user_can( 'review_esc_applications' ) && ! ESC_Portal_Helpers::can_review_application( $application_id ) ) {
			wp_die( esc_html__( 'You are not allowed to download this file.', 'es-care-portal' ), 403 );
		}

		if ( 'esc_application' !== get_post_type( $application_id ) ) {
			wp_die( esc_html__( 'Application not found.', 'es-care-portal' ), 404 );
		}

		$stored = (string) get_post_meta( $application_id, '_esc_resume_file', true );
		$path   = self::absolute_path( $stored );

		if ( ! $path || ! is_readable( $path ) ) {
			wp_die( esc_html__( 'The resume file is missing.', 'es-care-portal' ), 404 );
		}

		$download_name = (string) get_post_meta( $application_id, '_esc_resume_name', true );

		if ( ! $download_name ) {
			$download_name = basename( $path );
		}

		$download_name = sanitize_file_name( $download_name );
		$mime          = wp_check_filetype( $path );
		$filetype      = ! empty( $mime['type'] ) ? $mime['type'] : 'application/octet-stream';

		nocache_headers();
		header( 'Content-Type: ' . $filetype );
		header( 'Content-Disposition: attachment; filename="' . $download_name . '"' );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );

		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/**
	 * Staff download URL.
	 *
	 * @param int $application_id Application ID.
	 * @return string
	 */
	public static function download_url( $application_id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'          => 'esc_download_resume',
					'application_id'  => absint( $application_id ),
				),
				admin_url( 'admin-post.php' )
			),
			'esc_download_resume_' . absint( $application_id )
		);
	}
}

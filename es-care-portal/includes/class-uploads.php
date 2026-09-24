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
	 * Preferred private directory, outside the web root when possible.
	 *
	 * @return string
	 */
	public static function directory() {
		if ( defined( 'ESC_PORTAL_PRIVATE_DIR' ) && ESC_PORTAL_PRIVATE_DIR ) {
			return trailingslashit( ESC_PORTAL_PRIVATE_DIR ) . self::DIRNAME;
		}

		return trailingslashit( dirname( ABSPATH ) ) . 'esc-portal-private/' . self::DIRNAME;
	}

	/**
	 * Legacy public-uploads path used before private storage.
	 *
	 * @return string
	 */
	public static function legacy_directory() {
		$uploads = wp_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . self::DIRNAME;
	}

	/**
	 * Whether new sensitive uploads are allowed.
	 *
	 * @return bool
	 */
	public static function is_ready() {
		$dir = self::directory();

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		return is_dir( $dir ) && is_writable( $dir );
	}

	/**
	 * Create the private directory and guard files.
	 *
	 * @return bool
	 */
	public static function ensure_directory() {
		$dir = self::directory();

		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			ESC_Portal_Health::log( 'error', 'storage', 'Private resume directory could not be created.' );
			return false;
		}

		if ( ! is_writable( $dir ) ) {
			ESC_Portal_Health::log( 'error', 'storage', 'Private resume directory is not writable.' );
			return false;
		}

		self::write_deny_files( $dir );

		$legacy = self::legacy_directory();
		if ( is_dir( $legacy ) ) {
			self::write_deny_files( $legacy );
		}

		return true;
	}

	/**
	 * @param string $dir Directory.
	 */
	public static function write_deny_files( $dir ) {
		$htaccess  = $dir . '/.htaccess';
		$index     = $dir . '/index.php';
		$webconfig = $dir . '/web.config';

		if ( ! file_exists( $htaccess ) ) {
			$rules  = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n";
			$rules .= "<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
			file_put_contents( $htaccess, $rules ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $webconfig ) ) {
			$rules = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><security><authorization><remove users=\"*\" roles=\"\" verbs=\"\"/><add accessType=\"Deny\" users=\"*\"/></authorization></security></system.webServer></configuration>\n";
			file_put_contents( $webconfig, $rules ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * Required application document types (certificates + CV).
	 *
	 * @return array<string,array{label:string,field:string,meta_file:string,meta_name:string,required:bool,images:bool}>
	 */
	public static function document_types() {
		return array(
			'food_handler'  => array(
				'label'     => __( 'Food handling certificate', 'es-care-portal' ),
				'field'     => 'esc_food_handler',
				'meta_file' => '_esc_food_handler_file',
				'meta_name' => '_esc_food_handler_name',
				'required'  => true,
				'images'    => true,
			),
			'cpr_first_aid' => array(
				'label'     => __( 'CPR / First Aid certificate', 'es-care-portal' ),
				'field'     => 'esc_cpr_first_aid',
				'meta_file' => '_esc_cpr_first_aid_file',
				'meta_name' => '_esc_cpr_first_aid_name',
				'required'  => true,
				'images'    => true,
			),
			'license'       => array(
				'label'     => __( 'License', 'es-care-portal' ),
				'field'     => 'esc_license_file',
				'meta_file' => '_esc_license_file',
				'meta_name' => '_esc_license_name',
				'required'  => true,
				'images'    => true,
			),
			'resume'        => array(
				'label'     => __( 'CV / Resume', 'es-care-portal' ),
				'field'     => 'esc_resume',
				'meta_file' => '_esc_resume_file',
				'meta_name' => '_esc_resume_name',
				'required'  => true,
				'images'    => false,
			),
		);
	}

	/**
	 * @param string $doc_type Document type key.
	 * @return array|null
	 */
	public static function document_type( $doc_type ) {
		$types = self::document_types();
		$key   = sanitize_key( $doc_type );

		return isset( $types[ $key ] ) ? $types[ $key ] : null;
	}

	/**
	 * Allowed MIME types keyed by extension (document uploads).
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
	 * MIME map for a document type (certificates may include images).
	 *
	 * @param string $doc_type Document type key.
	 * @return array<string,string>
	 */
	public static function allowed_mimes_for( $doc_type ) {
		$allowed = self::allowed_mimes();
		$doc     = self::document_type( $doc_type );

		if ( $doc && ! empty( $doc['images'] ) ) {
			$allowed['jpg']  = 'image/jpeg';
			$allowed['jpeg'] = 'image/jpeg';
			$allowed['png']  = 'image/png';
		}

		return $allowed;
	}

	/**
	 * Accept attribute for a file input.
	 *
	 * @param string $doc_type Document type key.
	 * @return string
	 */
	public static function accept_attr( $doc_type ) {
		$parts = array();

		foreach ( self::allowed_mimes_for( $doc_type ) as $ext => $mime ) {
			$parts[] = '.' . $ext;
			$parts[] = $mime;
		}

		return implode( ',', array_unique( $parts ) );
	}

	/**
	 * Human-readable allowed formats for a document type.
	 *
	 * @param string $doc_type Document type key.
	 * @return string
	 */
	public static function formats_help( $doc_type ) {
		$doc = self::document_type( $doc_type );

		if ( $doc && ! empty( $doc['images'] ) ) {
			return __( 'PDF, DOC, DOCX, JPG, or PNG.', 'es-care-portal' );
		}

		return __( 'PDF, DOC, or DOCX.', 'es-care-portal' );
	}

	/**
	 * @return int
	 */
	public static function max_bytes() {
		$settings = ESC_Portal_Helpers::get_settings();

		return max( 1, absint( $settings['max_file_mb'] ) ) * MB_IN_BYTES;
	}

	/**
	 * Handle a document upload and return stored relative filename.
	 *
	 * @param array  $file            $_FILES entry.
	 * @param int    $user_id         User ID.
	 * @param int    $application_id  Application ID.
	 * @param string $doc_type        Document type key.
	 * @return string|WP_Error Relative filename or error.
	 */
	public static function handle_upload( $file, $user_id, $application_id, $doc_type = 'resume' ) {
		$doc = self::document_type( $doc_type );

		if ( ! $doc ) {
			$doc_type = 'resume';
			$doc      = self::document_type( 'resume' );
		}

		$label = $doc['label'];

		if ( ! self::ensure_directory() ) {
			return new WP_Error( 'esc_upload_storage', __( 'Private file storage is unavailable.', 'es-care-portal' ) );
		}

		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error(
				'esc_upload_missing',
				/* translators: %s: document label */
				sprintf( __( 'Please attach your %s.', 'es-care-portal' ), $label )
			);
		}

		if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error(
				'esc_upload_error',
				/* translators: %s: document label */
				sprintf( __( 'The %s could not be uploaded.', 'es-care-portal' ), $label )
			);
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;

		if ( $size <= 0 || $size > self::max_bytes() ) {
			return new WP_Error(
				'esc_upload_size',
				/* translators: %s: document label */
				sprintf( __( 'The %s exceeds the maximum file size.', 'es-care-portal' ), $label )
			);
		}

		$filename = isset( $file['name'] ) ? $file['name'] : '';
		$allowed  = self::allowed_mimes_for( $doc_type );
		$check    = wp_check_filetype_and_ext( $file['tmp_name'], $filename, $allowed );
		$ext      = ! empty( $check['ext'] ) ? strtolower( $check['ext'] ) : '';
		$type     = ! empty( $check['type'] ) ? $check['type'] : '';

		if ( ! $ext || ! isset( $allowed[ $ext ] ) || ! $type ) {
			return new WP_Error(
				'esc_upload_type',
				/* translators: 1: document label, 2: allowed formats */
				sprintf( __( '%1$s must be a %2$s', 'es-care-portal' ), $label, self::formats_help( $doc_type ) )
			);
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
			$docx_ok = ( 'docx' === $ext && in_array( $real_mime, array( 'application/zip', 'application/octet-stream' ), true ) );
			$img_ok  = in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) && in_array( $real_mime, array( 'image/jpeg', 'image/png', 'image/jpg' ), true );

			if ( ! $docx_ok && ! $img_ok ) {
				return new WP_Error(
					'esc_upload_mime',
					/* translators: 1: document label, 2: allowed formats */
					sprintf( __( '%1$s must be a %2$s', 'es-care-portal' ), $label, self::formats_help( $doc_type ) )
				);
			}
		}

		$stored = absint( $user_id ) . '_' . absint( $application_id ) . '_' . sanitize_key( $doc_type ) . '_' . wp_generate_password( 12, false, false ) . '.' . $ext;
		$dest   = trailingslashit( self::directory() ) . $stored;

		if ( ! @move_uploaded_file( $file['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return new WP_Error(
				'esc_upload_move',
				/* translators: %s: document label */
				sprintf( __( 'The %s could not be stored.', 'es-care-portal' ), $label )
			);
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

		$private = trailingslashit( self::directory() ) . $stored;
		if ( is_readable( $private ) ) {
			return $private;
		}

		$legacy = trailingslashit( self::legacy_directory() ) . $stored;
		if ( is_readable( $legacy ) ) {
			return $legacy;
		}

		return $private;
	}

	/**
	 * Delete a stored resume file.
	 *
	 * @param string $stored Relative name.
	 * @return bool
	 */
	public static function delete_file( $stored ) {
		$path = self::absolute_path( $stored );

		if ( $path && is_file( $path ) ) {
			return (bool) wp_delete_file( $path );
		}

		return false;
	}

	/**
	 * Move legacy files from public uploads into private storage.
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

			$target = trailingslashit( $dest ) . $name;
			if ( @rename( $file, $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				@chmod( $target, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$moved++;
			}
		}

		if ( $moved ) {
			ESC_Portal_Health::log( 'info', 'storage', 'Migrated ' . $moved . ' resume files to private storage.' );
		}

		return $moved;
	}

	/**
	 * Storage health for the admin dashboard.
	 *
	 * @return array
	 */
	public static function health() {
		$dir      = self::directory();
		$writable = self::ensure_directory();
		$denied   = true;
		$probe    = trailingslashit( self::legacy_directory() ) . 'index.php';

		if ( file_exists( $probe ) ) {
			$uploads = wp_upload_dir();
			if ( ! empty( $uploads['baseurl'] ) ) {
				$url      = trailingslashit( $uploads['baseurl'] ) . self::DIRNAME . '/index.php';
				$response = wp_remote_get(
					$url,
					array(
						'timeout'     => 5,
						'redirection' => 0,
					)
				);
				$code = (int) wp_remote_retrieve_response_code( $response );
				if ( $code && $code < 400 ) {
					$denied = false;
				}
			}
		}

		return array(
			'path'             => $dir,
			'writable'         => $writable,
			'outside_uploads'  => false === strpos( wp_normalize_path( $dir ), wp_normalize_path( WP_CONTENT_DIR . '/uploads' ) ),
			'http_denied'      => $denied,
		);
	}

	/**
	 * Authenticated document download for staff / reviewers.
	 */
	public static function handle_download() {
		$application_id = isset( $_GET['application_id'] ) ? absint( $_GET['application_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$doc_type       = isset( $_GET['doc'] ) ? sanitize_key( wp_unslash( $_GET['doc'] ) ) : 'resume'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce          = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! self::document_type( $doc_type ) ) {
			$doc_type = 'resume';
		}

		$doc = self::document_type( $doc_type );

		if ( ! $application_id || ! wp_verify_nonce( $nonce, 'esc_download_resume_' . $application_id . '_' . $doc_type ) ) {
			// Backward-compatible nonce for older resume-only links.
			if ( 'resume' !== $doc_type || ! wp_verify_nonce( $nonce, 'esc_download_resume_' . $application_id ) ) {
				wp_die( esc_html__( 'Invalid download link.', 'es-care-portal' ), 403 );
			}
		}

		if ( ! current_user_can( 'review_esc_applications' ) && ! ESC_Portal_Helpers::can_review_application( $application_id ) ) {
			wp_die( esc_html__( 'You are not allowed to download this file.', 'es-care-portal' ), 403 );
		}

		if ( 'esc_application' !== get_post_type( $application_id ) ) {
			wp_die( esc_html__( 'Application not found.', 'es-care-portal' ), 404 );
		}

		$stored = (string) get_post_meta( $application_id, $doc['meta_file'], true );
		$path   = self::absolute_path( $stored );

		if ( ! $path || ! is_readable( $path ) ) {
			wp_die(
				esc_html(
					/* translators: %s: document label */
					sprintf( __( 'The %s file is missing.', 'es-care-portal' ), $doc['label'] )
				),
				404
			);
		}

		$download_name = (string) get_post_meta( $application_id, $doc['meta_name'], true );

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
		header( "Content-Security-Policy: sandbox; default-src 'none'" );

		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/**
	 * Staff download URL for an application document.
	 *
	 * @param int    $application_id Application ID.
	 * @param string $doc_type       Document type key.
	 * @return string
	 */
	public static function download_url( $application_id, $doc_type = 'resume' ) {
		if ( ! self::document_type( $doc_type ) ) {
			$doc_type = 'resume';
		}

		return wp_nonce_url(
			add_query_arg(
				array(
					'action'         => 'esc_download_resume',
					'application_id' => absint( $application_id ),
					'doc'            => sanitize_key( $doc_type ),
				),
				admin_url( 'admin-post.php' )
			),
			'esc_download_resume_' . absint( $application_id ) . '_' . sanitize_key( $doc_type )
		);
	}

	/**
	 * Delete every stored document for an application.
	 *
	 * @param int $application_id Application ID.
	 */
	public static function delete_application_files( $application_id ) {
		foreach ( self::document_types() as $doc ) {
			$stored = (string) get_post_meta( $application_id, $doc['meta_file'], true );

			if ( $stored ) {
				self::delete_file( $stored );
				delete_post_meta( $application_id, $doc['meta_file'] );
				delete_post_meta( $application_id, $doc['meta_name'] );
			}
		}
	}
}

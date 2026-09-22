<?php
/**
 * Shared helpers, option keys, and field dictionaries.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Helpers {

	const OPTION_KEY = 'esc_portal_settings';
	const PAGES_KEY  = 'esc_portal_pages';
	const VERSION_KEY = 'esc_portal_version';

	/**
	 * Default plugin settings.
	 *
	 * @return array
	 */
	public static function default_settings() {
		return array(
			'notification_email'    => '',
			'smtp_enabled'          => 0,
			'smtp_host'             => 'escareservices.com',
			'smtp_port'             => 465,
			'smtp_encryption'       => 'ssl',
			'smtp_username'         => 'info@escareservices.com',
			'smtp_password'         => '',
			'smtp_from_email'       => 'info@escareservices.com',
			'smtp_from_name'        => '',
			'max_file_mb'           => 5,
			'allowed_types'         => array( 'pdf', 'doc', 'docx' ),
			'color_accent'          => '#4caf50',
			'color_sidebar'         => '#66bb6a',
			'color_sidebar_header'  => '#2e7d32',
			'color_tile'            => '#4caf50',
			'color_cta'             => '#2e7d32',
			'tile_seeker'           => array( 'apply', 'assessments', 'results', 'forms' ),
			'tile_employer'         => array( 'post', 'jobs', 'profile', 'membership' ),
			'retention_years'       => 3,
			'delete_data_on_uninstall' => 0,
		);
	}

	/**
	 * @return array
	 */
	public static function get_settings() {
		$saved    = get_option( self::OPTION_KEY, array() );
		$defaults = self::default_settings();

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = wp_parse_args( $saved, $defaults );

		if ( empty( $settings['notification_email'] ) ) {
			$settings['notification_email'] = get_option( 'admin_email' );
		}

		if ( ! is_array( $settings['allowed_types'] ) ) {
			$settings['allowed_types'] = $defaults['allowed_types'];
		}

		if ( ! is_array( $settings['tile_seeker'] ) ) {
			$settings['tile_seeker'] = $defaults['tile_seeker'];
		}

		if ( ! is_array( $settings['tile_employer'] ) ) {
			$settings['tile_employer'] = $defaults['tile_employer'];
		}

		$settings['max_file_mb']      = max( 1, absint( $settings['max_file_mb'] ) );
		$settings['retention_years']  = max( 1, min( 10, absint( $settings['retention_years'] ) ) );
		$settings['delete_data_on_uninstall'] = empty( $settings['delete_data_on_uninstall'] ) ? 0 : 1;

		return $settings;
	}

	/**
	 * Settings safe to pass into frontend templates and widgets.
	 *
	 * @return array
	 */
	public static function public_settings() {
		$settings = self::get_settings();

		foreach ( array( 'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name' ) as $key ) {
			unset( $settings[ $key ] );
		}

		return $settings;
	}

	/**
	 * @param array $settings Settings to persist.
	 */
	public static function update_settings( $settings ) {
		update_option( self::OPTION_KEY, $settings, false );
	}

	/**
	 * @return string[]
	 */
	public static function page_slugs() {
		return array(
			'register',
			'login',
			'dashboard',
			'profile',
			'careers',
			'apply',
			'post-job',
			'lost-password',
			'reset-password',
			'contact',
		);
	}

	/**
	 * @param string $slug Page key.
	 * @return int
	 */
	public static function get_page_id( $slug ) {
		$pages = get_option( self::PAGES_KEY, array() );

		if ( ! is_array( $pages ) || empty( $pages[ $slug ] ) ) {
			return 0;
		}

		return absint( $pages[ $slug ] );
	}

	/**
	 * @param string $slug Page key.
	 * @param array  $args Query args.
	 * @return string
	 */
	public static function get_page_url( $slug, $args = array() ) {
		$id = self::get_page_id( $slug );

		if ( $id ) {
			$url = get_permalink( $id );
		} else {
			$url = home_url( '/' );
		}

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * Dashboard URL with an optional inner view.
	 *
	 * @param string $view View key.
	 * @param array  $args Extra query args.
	 * @return string
	 */
	public static function dashboard_url( $view = '', $args = array() ) {
		if ( $view && 'home' !== $view ) {
			$args['esc_view'] = $view;
		}

		return self::get_page_url( 'dashboard', $args );
	}

	/**
	 * Current dashboard view for seekers or employers.
	 *
	 * @param string $role Role context: seeker|employer.
	 * @return string
	 */
	public static function current_dashboard_view( $role = 'seeker' ) {
		$view = isset( $_GET['esc_view'] ) ? sanitize_key( wp_unslash( $_GET['esc_view'] ) ) : 'home'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$allowed = array( 'home', 'apply', 'assessments', 'take', 'results', 'forms', 'password', 'request' );

		if ( 'employer' === $role ) {
			$allowed = array( 'home', 'profile', 'jobs', 'password', 'request', 'membership', 'post' );
		} elseif ( 'admin' === $role ) {
			$allowed = array( 'home', 'users', 'jobs', 'applications', 'contact' );
		}

		return in_array( $view, $allowed, true ) ? $view : 'home';
	}

	/**
	 * @return array<string,string>
	 */
	public static function certifications() {
		return array(
			'cna'        => __( 'CNA', 'es-care-portal' ),
			'hha'        => __( 'HHA', 'es-care-portal' ),
			'rn'         => __( 'RN', 'es-care-portal' ),
			'lpn'        => __( 'LPN', 'es-care-portal' ),
			'cpr_bls'    => __( 'CPR / BLS', 'es-care-portal' ),
			'first_aid'  => __( 'First Aid', 'es-care-portal' ),
			'other'      => __( 'Other', 'es-care-portal' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function employment_types() {
		return array(
			'full-time' => __( 'Full-time', 'es-care-portal' ),
			'part-time' => __( 'Part-time', 'es-care-portal' ),
			'prn'       => __( 'PRN', 'es-care-portal' ),
			'contract'  => __( 'Contract', 'es-care-portal' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function availability_options() {
		return array(
			'full-time' => __( 'Full-time', 'es-care-portal' ),
			'part-time' => __( 'Part-time', 'es-care-portal' ),
			'weekends'  => __( 'Weekends', 'es-care-portal' ),
			'nights'    => __( 'Nights', 'es-care-portal' ),
			'live-in'   => __( 'Live-in', 'es-care-portal' ),
		);
	}

	/**
	 * US states for application forms.
	 *
	 * @return array<string,string>
	 */
	public static function us_states() {
		return array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function work_auth_options() {
		return array(
			'authorized'  => __( 'Authorized to work in the United States', 'es-care-portal' ),
			'sponsorship' => __( 'Will require sponsorship now or in the future', 'es-care-portal' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function application_statuses() {
		return array(
			'pending'    => __( 'Pending', 'es-care-portal' ),
			'reviewing'  => __( 'Reviewing', 'es-care-portal' ),
			'interview'  => __( 'Interview', 'es-care-portal' ),
			'hired'      => __( 'Hired', 'es-care-portal' ),
			'rejected'   => __( 'Rejected', 'es-care-portal' ),
			'withdrawn'  => __( 'Withdrawn', 'es-care-portal' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function job_statuses() {
		return array(
			'open'     => __( 'Open', 'es-care-portal' ),
			'closed'   => __( 'Closed', 'es-care-portal' ),
			'pending'  => __( 'Pending review', 'es-care-portal' ),
			'rejected' => __( 'Rejected', 'es-care-portal' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function portal_roles() {
		return ESC_Portal_Users::roles();
	}

	/**
	 * @param object|null $user Portal user.
	 * @return bool
	 */
	public static function is_seeker( $user = null ) {
		return ESC_Portal_Users::is_seeker( $user );
	}

	/**
	 * @param object|null $user Portal user.
	 * @return bool
	 */
	public static function is_employer( $user = null ) {
		return ESC_Portal_Users::is_employer( $user );
	}

	/**
	 * @param object|null $user Portal user.
	 * @return bool
	 */
	public static function is_portal_admin( $user = null ) {
		return ESC_Portal_Users::is_admin( $user );
	}

	/**
	 * WordPress administrators can still review in wp-admin.
	 *
	 * @param int $application_id Application ID.
	 * @return bool
	 */
	public static function can_review_application( $application_id ) {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'review_esc_applications' ) ) {
			return true;
		}

		$user = ESC_Portal_Auth::current_user();

		if ( ! $user ) {
			return false;
		}

		if ( ESC_Portal_Users::ROLE_ADMIN === $user->role ) {
			return true;
		}

		if ( ESC_Portal_Users::ROLE_EMPLOYER !== $user->role ) {
			return false;
		}

		$job_id = (int) get_post_meta( $application_id, '_esc_job_id', true );

		return $job_id && (int) get_post_meta( $job_id, '_esc_employer_id', true ) === (int) $user->id;
	}

	/**
	 * Whether the current portal user may manage a job.
	 *
	 * @param int $job_id Job ID.
	 * @return bool
	 */
	public static function can_manage_job( $job_id ) {
		$user = ESC_Portal_Auth::current_user();

		if ( ! $user ) {
			return false;
		}

		if ( ESC_Portal_Users::ROLE_ADMIN === $user->role ) {
			return true;
		}

		if ( ESC_Portal_Users::ROLE_EMPLOYER !== $user->role ) {
			return false;
		}

		return (int) get_post_meta( $job_id, '_esc_employer_id', true ) === (int) $user->id;
	}

	/**
	 * Whether a job currently accepts applications.
	 *
	 * @param int $job_id Job ID.
	 * @return bool
	 */
	public static function is_job_open( $job_id ) {
		$job_id = absint( $job_id );

		if ( ! $job_id || 'esc_job' !== get_post_type( $job_id ) || 'publish' !== get_post_status( $job_id ) ) {
			return false;
		}

		$status = get_post_meta( $job_id, '_esc_job_status', true );

		if ( 'closed' === $status ) {
			return false;
		}

		$closing = get_post_meta( $job_id, '_esc_closing_date', true );

		if ( $closing && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $closing ) ) {
			$end = strtotime( $closing . ' 23:59:59' );

			if ( $end && time() > $end ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array  $submitted Submitted keys.
	 * @param array  $allowed   Allowed dictionary.
	 * @return string[]
	 */
	public static function sanitize_choice_list( $submitted, $allowed ) {
		if ( ! is_array( $submitted ) ) {
			$submitted = array();
		}

		$clean = array();

		foreach ( $submitted as $value ) {
			$value = sanitize_key( $value );

			if ( isset( $allowed[ $value ] ) ) {
				$clean[] = $value;
			}
		}

		return array_values( array_unique( $clean ) );
	}

	/**
	 * @param string $status Status key.
	 * @return string
	 */
	public static function format_status( $status ) {
		$key      = sanitize_key( $status );
		$statuses = array_merge( self::application_statuses(), self::job_statuses(), ESC_Portal_Users::statuses() );

		return isset( $statuses[ $key ] ) ? $statuses[ $key ] : $status;
	}

	/**
	 * Listing status considering WordPress post_status and job meta.
	 *
	 * @param WP_Post|int $job Job.
	 * @return string
	 */
	public static function listing_status( $job ) {
		$post = $job instanceof WP_Post ? $job : get_post( $job );

		if ( ! $post ) {
			return 'closed';
		}

		if ( 'pending' === $post->post_status ) {
			return 'pending';
		}

		if ( 'draft' === $post->post_status ) {
			return 'rejected';
		}

		$status = (string) get_post_meta( $post->ID, '_esc_job_status', true );

		return $status ? $status : 'open';
	}

	/**
	 * @param string[] $keys  Choice keys.
	 * @param array    $dict  Dictionary.
	 * @return string
	 */
	public static function format_choices( $keys, $dict ) {
		if ( ! is_array( $keys ) ) {
			return '';
		}

		$labels = array();

		foreach ( $keys as $key ) {
			if ( isset( $dict[ $key ] ) ) {
				$labels[] = $dict[ $key ];
			}
		}

		return implode( ', ', $labels );
	}

	/**
	 * Redirect with a notice code.
	 *
	 * @param string $url    URL.
	 * @param string $notice Notice code.
	 * @param string $type   success|error|info.
	 */
	public static function redirect_notice( $url, $notice, $type = 'success' ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'esc_notice' => rawurlencode( $notice ),
					'esc_type'   => $type,
				),
				$url
			)
		);
		exit;
	}

	/**
	 * Print a frontend notice from the query string.
	 *
	 * @return string
	 */
	public static function render_query_notice() {
		if ( empty( $_GET['esc_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '';
		}

		$code = sanitize_key( wp_unslash( $_GET['esc_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['esc_type'] ) ? sanitize_key( wp_unslash( $_GET['esc_type'] ) ) : 'success'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! in_array( $type, array( 'success', 'error', 'info' ), true ) ) {
			$type = 'info';
		}

		$message = self::notice_message( $code );

		if ( ! $message ) {
			return '';
		}

		return '<div class="esc-notice esc-notice--' . esc_attr( $type ) . '" role="status">' . esc_html( $message ) . '</div>';
	}

	const FORM_COOKIE = 'esc_portal_form';
	const FORM_TTL    = 600;

	/**
	 * Allowed sticky fields per public form.
	 *
	 * @param string $form Form key.
	 * @return string[]
	 */
	public static function form_fields( $form ) {
		$map = array(
			'register' => array( 'first_name', 'last_name', 'email', 'phone', 'role', 'company_name' ),
			'login'    => array( 'email' ),
			'contact'  => array( 'name', 'email', 'subject', 'message' ),
		);

		return isset( $map[ $form ] ) ? $map[ $form ] : array();
	}

	/**
	 * Keep non-password form values across a validation redirect.
	 *
	 * @param string               $form   Form key.
	 * @param array<string,string> $values Submitted values.
	 */
	public static function remember_form( $form, $values ) {
		$form    = sanitize_key( $form );
		$allowed = self::form_fields( $form );

		if ( ! $allowed ) {
			return;
		}

		$clean = array();

		foreach ( $allowed as $key ) {
			if ( ! isset( $values[ $key ] ) || ! is_scalar( $values[ $key ] ) ) {
				continue;
			}

			$value           = (string) $values[ $key ];
			$clean[ $key ] = 'message' === $key ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		}

		try {
			$token = bin2hex( random_bytes( 16 ) );
		} catch ( Exception $exception ) {
			$token = wp_generate_password( 32, false, false );
		}

		set_transient(
			'esc_form_' . $token,
			array(
				'f' => $form,
				'v' => $clean,
			),
			self::FORM_TTL
		);

		self::set_form_cookie( $token, time() + self::FORM_TTL );
	}

	/**
	 * Read sticky values for a form. Empty when missing or expired.
	 *
	 * @param string $form Form key.
	 * @return array<string,string>
	 */
	public static function recall_form( $form ) {
		$form = sanitize_key( $form );

		if ( empty( $_COOKIE[ self::FORM_COOKIE ] ) || ! is_string( $_COOKIE[ self::FORM_COOKIE ] ) ) {
			return array();
		}

		$token = sanitize_key( wp_unslash( $_COOKIE[ self::FORM_COOKIE ] ) );

		if ( ! $token ) {
			return array();
		}

		$data = get_transient( 'esc_form_' . $token );

		if ( ! is_array( $data ) || empty( $data['f'] ) || $data['f'] !== $form ) {
			return array();
		}

		$out    = array();
		$values = isset( $data['v'] ) && is_array( $data['v'] ) ? $data['v'] : array();

		foreach ( self::form_fields( $form ) as $key ) {
			if ( ! isset( $values[ $key ] ) || ! is_scalar( $values[ $key ] ) ) {
				continue;
			}

			$value       = (string) $values[ $key ];
			$out[ $key ] = 'message' === $key ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		}

		if ( isset( $out['email'] ) ) {
			$out['email'] = sanitize_email( $out['email'] );
		}

		return $out;
	}

	/**
	 * Drop sticky form cookie and matching transient.
	 */
	public static function forget_form() {
		if ( ! empty( $_COOKIE[ self::FORM_COOKIE ] ) && is_string( $_COOKIE[ self::FORM_COOKIE ] ) ) {
			$token = sanitize_key( wp_unslash( $_COOKIE[ self::FORM_COOKIE ] ) );
			if ( $token ) {
				delete_transient( 'esc_form_' . $token );
			}
		}

		if ( ! headers_sent() ) {
			self::set_form_cookie( '', time() - YEAR_IN_SECONDS );
		}

		unset( $_COOKIE[ self::FORM_COOKIE ] );
	}

	/**
	 * @param string $value   Cookie payload.
	 * @param int    $expires Unix timestamp.
	 */
	private static function set_form_cookie( $value, $expires ) {
		$secure = is_ssl();
		$path   = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				self::FORM_COOKIE,
				$value,
				array(
					'expires'  => $expires,
					'path'     => $path,
					'domain'   => $domain,
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			setcookie( self::FORM_COOKIE, $value, $expires, $path, $domain, $secure, true );
		}

		if ( $value ) {
			$_COOKIE[ self::FORM_COOKIE ] = $value;
		}
	}

	/**
	 * @param string $code Notice code.
	 * @return string
	 */
	public static function notice_message( $code ) {
		$map = array(
			'registered'        => __( 'Your account is ready. Welcome to ES Care Services.', 'es-care-portal' ),
			'logged-in'         => __( 'You are signed in.', 'es-care-portal' ),
			'logged-out'        => __( 'You have been signed out.', 'es-care-portal' ),
			'profile-saved'     => __( 'Your profile has been saved.', 'es-care-portal' ),
			'applied'           => __( 'Your application was submitted. We will be in touch.', 'es-care-portal' ),
			'withdrawn'         => __( 'Your application was withdrawn.', 'es-care-portal' ),
			'reset-sent'        => __( 'If that email is registered, a reset link is on its way.', 'es-care-portal' ),
			'password-reset'    => __( 'Your password was updated. You can sign in now.', 'es-care-portal' ),
			'login-required'    => __( 'Please sign in to continue.', 'es-care-portal' ),
			'job-closed'        => __( 'This position is no longer accepting applications.', 'es-care-portal' ),
			'duplicate'         => __( 'You have already applied for this position.', 'es-care-portal' ),
			'invalid-job'       => __( 'That job could not be found.', 'es-care-portal' ),
			'upload-required'   => __( 'Please attach a resume or CV.', 'es-care-portal' ),
			'upload-failed'     => __( 'The resume could not be uploaded. Check the file type and size.', 'es-care-portal' ),
			'locked-out'        => __( 'Too many failed sign-in attempts. Please wait 15 minutes and try again.', 'es-care-portal' ),
			'invalid-login'     => __( 'The email or password is incorrect.', 'es-care-portal' ),
			'email-exists'      => __( 'An account with that email already exists. Sign in instead.', 'es-care-portal' ),
			'password-mismatch' => __( 'The passwords did not match.', 'es-care-portal' ),
			'weak-password'     => __( 'Use at least 8 characters with uppercase, lowercase, and a number.', 'es-care-portal' ),
			'invalid-email'     => __( 'Please enter a valid email address.', 'es-care-portal' ),
			'required'          => __( 'Please fill in all required fields.', 'es-care-portal' ),
			'nonce'             => __( 'The form expired. Please try again.', 'es-care-portal' ),
			'reset-invalid'     => __( 'This reset link is invalid or has expired.', 'es-care-portal' ),
			'not-allowed'       => __( 'You cannot do that.', 'es-care-portal' ),
			'company-required'  => __( 'Please enter your company name.', 'es-care-portal' ),
			'role-required'     => __( 'Please choose Job Seeker or Employer.', 'es-care-portal' ),
			'account-disabled'  => __( 'This account has been disabled.', 'es-care-portal' ),
			'status-saved'      => __( 'Application status saved.', 'es-care-portal' ),
			'user-updated'      => __( 'User updated.', 'es-care-portal' ),
			'job-saved'         => __( 'Job listing saved.', 'es-care-portal' ),
			'job-deleted'       => __( 'Job listing removed.', 'es-care-portal' ),
			'user-deleted'      => __( 'Dashboard user permanently deleted.', 'es-care-portal' ),
			'application-deleted' => __( 'Application moved to Trash.', 'es-care-portal' ),
			'cannot-delete-self' => __( 'You cannot delete the portal administrator account you are currently using.', 'es-care-portal' ),
			'seeker-only'       => __( 'Only job seekers can apply for positions.', 'es-care-portal' ),
			'password-changed'  => __( 'Your password has been updated.', 'es-care-portal' ),
			'wrong-password'    => __( 'Your current password is incorrect.', 'es-care-portal' ),
			'account-deleted'   => __( 'Your account has been deleted.', 'es-care-portal' ),
			'assessment-passed' => __( 'You passed the assessment. Download employment forms when you are ready.', 'es-care-portal' ),
			'assessment-failed' => __( 'Your assessment was recorded. You can review the score and try again.', 'es-care-portal' ),
			'request-sent'      => __( 'Your service request was sent. We will follow up with you.', 'es-care-portal' ),
			'contact-sent'      => __( 'Thanks — your message was sent. We will follow up with you.', 'es-care-portal' ),
			'verify-email'      => __( 'Check your email to verify this employer account. An administrator must then approve it before you can post jobs.', 'es-care-portal' ),
			'email-verified'    => __( 'Your email is verified. An administrator will review your employer account shortly.', 'es-care-portal' ),
			'pending-email'     => __( 'Please verify your email address before signing in.', 'es-care-portal' ),
			'pending-admin'     => __( 'Your employer account is waiting for administrator approval.', 'es-care-portal' ),
			'employer-approved' => __( 'The employer account has been approved.', 'es-care-portal' ),
			'employer-rejected' => __( 'The employer account has been rejected and disabled.', 'es-care-portal' ),
			'verify-resent'     => __( 'A new verification email was sent.', 'es-care-portal' ),
			'job-pending'       => __( 'Your job listing was submitted for administrator review.', 'es-care-portal' ),
			'job-approved'      => __( 'The job listing is now published.', 'es-care-portal' ),
			'job-rejected'      => __( 'The job listing was rejected.', 'es-care-portal' ),
			'rate-limited'      => __( 'Too many requests. Please wait a few minutes and try again.', 'es-care-portal' ),
			'save-failed'       => __( 'The information could not be saved. Please try again.', 'es-care-portal' ),
			'storage-unavailable' => __( 'File storage is unavailable. Please contact the site administrator.', 'es-care-portal' ),
		);

		return isset( $map[ $code ] ) ? $map[ $code ] : '';
	}

	/**
	 * Load a public template.
	 *
	 * @param string $name Template name without extension.
	 * @param array  $args Variables.
	 * @return string
	 */
	public static function get_template( $name, $args = array() ) {
		$path = ESC_PORTAL_DIR . 'public/templates/' . $name . '.php';

		if ( ! file_exists( $path ) ) {
			return '';
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		ob_start();
		include $path;
		return (string) ob_get_clean();
	}

	/**
	 * Load an admin view.
	 *
	 * @param string $name View name without extension.
	 * @param array  $args Variables.
	 */
	public static function admin_view( $name, $args = array() ) {
		$path = ESC_PORTAL_DIR . 'admin/views/' . $name . '.php';

		if ( ! file_exists( $path ) ) {
			return;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $path;
	}

	/**
	 * Profile completeness for dashboard prompt.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_profile_complete( $user_id ) {
		$phone = ESC_Portal_Users::get_meta( $user_id, 'city', '' ) && ESC_Portal_Users::get_meta( $user_id, 'state', '' );
		$certs = ESC_Portal_Users::get_meta( $user_id, 'certifications', array() );
		$years = ESC_Portal_Users::get_meta( $user_id, 'years_experience', '' );
		$auth  = ESC_Portal_Users::get_meta( $user_id, 'work_authorization', '' );
		$user  = ESC_Portal_Users::get( $user_id );

		if ( $user && ESC_Portal_Users::ROLE_EMPLOYER === $user->role ) {
			return (bool) $user->company_name && $user->phone;
		}

		return (bool) $user && $user->phone && $phone && ! empty( $certs ) && '' !== $years && $auth;
	}

	/**
	 * Current request redirect target, if local.
	 *
	 * @return string
	 */
	public static function requested_redirect() {
		$redirect = '';

		if ( ! empty( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect = wp_unslash( $_REQUEST['redirect_to'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$redirect = wp_validate_redirect( esc_url_raw( $redirect ), '' );

		return $redirect;
	}

	/**
	 * Parse allowlisted list-table request args from the query string.
	 *
	 * @param array $allowed_orderby Allowed orderby keys.
	 * @return array
	 */
	public static function table_request( $allowed_orderby = array() ) {
		$has_query = isset( $_GET['esc_q'] ) || isset( $_GET['esc_orderby'] ) || isset( $_GET['esc_paged'] ) || isset( $_GET['esc_status'] ) || isset( $_GET['esc_role'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce     = isset( $_GET['_esc_table'] ) ? sanitize_text_field( wp_unslash( $_GET['_esc_table'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $has_query && ! wp_verify_nonce( $nonce, 'esc_portal_table' ) ) {
			$has_query = false;
		}

		$orderby = ( $has_query && isset( $_GET['esc_orderby'] ) ) ? sanitize_key( wp_unslash( $_GET['esc_orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = ( $has_query && isset( $_GET['esc_order'] ) ) ? strtoupper( sanitize_key( wp_unslash( $_GET['esc_order'] ) ) ) : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged   = ( $has_query && isset( $_GET['esc_paged'] ) ) ? absint( $_GET['esc_paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$per     = ( $has_query && isset( $_GET['esc_per_page'] ) ) ? absint( $_GET['esc_per_page'] ) : 25; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search  = ( $has_query && isset( $_GET['esc_q'] ) ) ? sanitize_text_field( wp_unslash( $_GET['esc_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status  = ( $has_query && isset( $_GET['esc_status'] ) ) ? sanitize_key( wp_unslash( $_GET['esc_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$role    = ( $has_query && isset( $_GET['esc_role'] ) ) ? sanitize_key( wp_unslash( $_GET['esc_role'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = $allowed_orderby ? $allowed_orderby[0] : 'created_at';
		}

		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$per   = min( 100, max( 10, $per ) );
		$paged = max( 1, $paged );

		return array(
			'orderby' => $orderby,
			'order'   => $order,
			'paged'   => $paged,
			'number'  => $per,
			'offset'  => ( $paged - 1 ) * $per,
			'search'  => $search,
			'status'  => $status,
			'role'    => $role,
		);
	}

	/**
	 * Render server-side pagination links.
	 *
	 * @param int    $total Total items.
	 * @param array  $req   table_request() result.
	 * @param string $url   Base URL.
	 * @return string
	 */
	public static function pagination_html( $total, $req, $url ) {
		$total  = max( 0, (int) $total );
		$per    = max( 1, (int) $req['number'] );
		$paged  = max( 1, (int) $req['paged'] );
		$pages  = max( 1, (int) ceil( $total / $per ) );
		$paged  = min( $paged, $pages );
		$from   = $total ? ( ( $paged - 1 ) * $per ) + 1 : 0;
		$to     = min( $total, $paged * $per );
		$args   = self::table_query_args( $req );

		if ( $total ) {
			$summary = sprintf(
				/* translators: 1: first item on page, 2: last item on page, 3: total items */
				__( 'Showing %1$s–%2$s of %3$s', 'es-care-portal' ),
				number_format_i18n( $from ),
				number_format_i18n( $to ),
				number_format_i18n( $total )
			);
		} else {
			$summary = __( 'No results', 'es-care-portal' );
		}

		$html  = '<nav class="esc-pagination" aria-label="' . esc_attr__( 'Table pagination', 'es-care-portal' ) . '">';
		$html .= '<p class="esc-pagination-summary">' . esc_html( $summary ) . '</p>';

		if ( $pages > 1 ) {
			$html .= '<div class="esc-pagination-pages">';

			if ( $paged > 1 ) {
				$html .= '<a class="esc-page-btn" href="' . esc_url( add_query_arg( array_merge( $args, array( 'esc_paged' => $paged - 1 ) ), $url ) ) . '">' . esc_html__( 'Previous', 'es-care-portal' ) . '</a>';
			} else {
				$html .= '<span class="esc-page-btn is-disabled" aria-disabled="true">' . esc_html__( 'Previous', 'es-care-portal' ) . '</span>';
			}

			$window = 2;
			$start  = max( 1, $paged - $window );
			$end    = min( $pages, $paged + $window );

			if ( $start > 1 ) {
				$html .= '<a class="esc-page-btn" href="' . esc_url( add_query_arg( array_merge( $args, array( 'esc_paged' => 1 ) ), $url ) ) . '">1</a>';
				if ( $start > 2 ) {
					$html .= '<span class="esc-page-ellipsis" aria-hidden="true">…</span>';
				}
			}

			for ( $page = $start; $page <= $end; $page++ ) {
				if ( $page === $paged ) {
					$html .= '<span class="esc-page-btn is-current" aria-current="page">' . esc_html( number_format_i18n( $page ) ) . '</span>';
				} else {
					$html .= '<a class="esc-page-btn" href="' . esc_url( add_query_arg( array_merge( $args, array( 'esc_paged' => $page ) ), $url ) ) . '">' . esc_html( number_format_i18n( $page ) ) . '</a>';
				}
			}

			if ( $end < $pages ) {
				if ( $end < $pages - 1 ) {
					$html .= '<span class="esc-page-ellipsis" aria-hidden="true">…</span>';
				}
				$html .= '<a class="esc-page-btn" href="' . esc_url( add_query_arg( array_merge( $args, array( 'esc_paged' => $pages ) ), $url ) ) . '">' . esc_html( number_format_i18n( $pages ) ) . '</a>';
			}

			if ( $paged < $pages ) {
				$html .= '<a class="esc-page-btn" href="' . esc_url( add_query_arg( array_merge( $args, array( 'esc_paged' => $paged + 1 ) ), $url ) ) . '">' . esc_html__( 'Next', 'es-care-portal' ) . '</a>';
			} else {
				$html .= '<span class="esc-page-btn is-disabled" aria-disabled="true">' . esc_html__( 'Next', 'es-care-portal' ) . '</span>';
			}

			$html .= '</div>';
		}

		$html .= '</nav>';

		return $html;
	}

	/**
	 * Sortable column URL.
	 *
	 * @param string $column Column key.
	 * @param array  $req    Request.
	 * @param string $url    Base URL.
	 * @return string
	 */
	public static function sort_url( $column, $req, $url ) {
		$order = ( $req['orderby'] === $column && 'ASC' === $req['order'] ) ? 'DESC' : 'ASC';

		return add_query_arg(
			array_merge(
				self::table_query_args( $req ),
				array(
					'esc_orderby' => $column,
					'esc_order'   => $order,
					'esc_paged'   => 1,
				)
			),
			$url
		);
	}

	/**
	 * Allowlisted query args for table filters and pagination.
	 *
	 * @param array $req Request.
	 * @return array
	 */
	public static function table_query_args( $req ) {
		return array(
			'esc_q'        => isset( $req['search'] ) ? $req['search'] : '',
			'esc_status'   => isset( $req['status'] ) ? $req['status'] : '',
			'esc_role'     => isset( $req['role'] ) ? $req['role'] : '',
			'esc_orderby'  => isset( $req['orderby'] ) ? $req['orderby'] : '',
			'esc_order'    => isset( $req['order'] ) ? $req['order'] : 'DESC',
			'esc_per_page' => isset( $req['number'] ) ? $req['number'] : 25,
			'_esc_table'   => wp_create_nonce( 'esc_portal_table' ),
		);
	}

	/**
	 * Accessible sortable table header.
	 *
	 * @param string $column Column key.
	 * @param string $label  Visible label.
	 * @param array  $req    Request.
	 * @param string $url    Base URL.
	 * @return string
	 */
	public static function table_th( $column, $label, $req, $url ) {
		$sorted = isset( $req['orderby'] ) && $req['orderby'] === $column;
		$dir    = $sorted && isset( $req['order'] ) && 'ASC' === $req['order'] ? 'ascending' : ( $sorted ? 'descending' : 'none' );
		$class  = 'esc-sort';

		if ( $sorted ) {
			$class .= ( 'ascending' === $dir ) ? ' is-asc' : ' is-desc';
		}

		return '<th scope="col" aria-sort="' . esc_attr( $dir ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( self::sort_url( $column, $req, $url ) ) . '">' . esc_html( $label ) . '</a></th>';
	}
}

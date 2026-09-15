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
			'notification_email' => '',
			'max_file_mb'        => 5,
			'allowed_types'      => array( 'pdf', 'doc', 'docx' ),
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

		$settings['max_file_mb'] = max( 1, absint( $settings['max_file_mb'] ) );

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
			'open'   => __( 'Open', 'es-care-portal' ),
			'closed' => __( 'Closed', 'es-care-portal' ),
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
		$statuses = self::application_statuses();
		$key      = sanitize_key( $status );

		return isset( $statuses[ $key ] ) ? $statuses[ $key ] : $status;
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
			'weak-password'     => __( 'Please choose a password with at least 8 characters.', 'es-care-portal' ),
			'invalid-email'     => __( 'Please enter a valid email address.', 'es-care-portal' ),
			'required'          => __( 'Please fill in all required fields.', 'es-care-portal' ),
			'nonce'             => __( 'The form expired. Please try again.', 'es-care-portal' ),
			'reset-invalid'     => __( 'This reset link is invalid or has expired.', 'es-care-portal' ),
			'not-allowed'       => __( 'You cannot do that.', 'es-care-portal' ),
			'company-required'  => __( 'Please enter your company name.', 'es-care-portal' ),
			'account-disabled'  => __( 'This account has been disabled.', 'es-care-portal' ),
			'status-saved'      => __( 'Application status saved.', 'es-care-portal' ),
			'user-updated'      => __( 'User updated.', 'es-care-portal' ),
			'job-saved'         => __( 'Job listing saved.', 'es-care-portal' ),
			'job-deleted'       => __( 'Job listing removed.', 'es-care-portal' ),
			'seeker-only'       => __( 'Only job seekers can apply for positions.', 'es-care-portal' ),
			'password-changed'  => __( 'Your password has been updated.', 'es-care-portal' ),
			'wrong-password'    => __( 'Your current password is incorrect.', 'es-care-portal' ),
			'account-deleted'   => __( 'Your account has been deleted.', 'es-care-portal' ),
			'assessment-passed' => __( 'You passed the assessment. Download employment forms when you are ready.', 'es-care-portal' ),
			'assessment-failed' => __( 'Your assessment was recorded. You can review the score and try again.', 'es-care-portal' ),
			'request-sent'      => __( 'Your service request was sent. We will follow up with you.', 'es-care-portal' ),
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
}

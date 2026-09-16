<?php
/**
 * Transactional emails.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Emails {

	/**
	 * Register built-in SMTP configuration.
	 */
	public static function init() {
		add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ), 99999 );
		add_filter( 'wp_mail_from', array( __CLASS__, 'mail_from' ), 99999 );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'mail_from_name' ), 99999 );
	}

	/**
	 * Configure WordPress' PHPMailer with the portal SMTP account.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer instance.
	 */
	public static function configure_smtp( $phpmailer ) {
		$settings = ESC_Portal_Helpers::get_settings();

		if ( empty( $settings['smtp_enabled'] ) || empty( $settings['smtp_host'] ) ) {
			return;
		}

		$password = self::decrypt_secret( isset( $settings['smtp_password'] ) ? $settings['smtp_password'] : '' );

		$phpmailer->isSMTP();
		$phpmailer->Host       = $settings['smtp_host'];
		$phpmailer->Port       = max( 1, min( 65535, absint( $settings['smtp_port'] ) ) );
		$phpmailer->SMTPAuth   = ! empty( $settings['smtp_username'] );
		$phpmailer->Username   = isset( $settings['smtp_username'] ) ? $settings['smtp_username'] : '';
		$phpmailer->Password   = $password;
		$phpmailer->SMTPAutoTLS = false;

		$encryption = isset( $settings['smtp_encryption'] ) ? sanitize_key( $settings['smtp_encryption'] ) : '';
		$phpmailer->SMTPSecure = in_array( $encryption, array( 'ssl', 'tls' ), true ) ? $encryption : 'ssl';
	}

	/**
	 * Use the configured SMTP sender address.
	 *
	 * @param string $from Existing address.
	 * @return string
	 */
	public static function mail_from( $from ) {
		$settings = ESC_Portal_Helpers::get_settings();
		$email    = isset( $settings['smtp_from_email'] ) ? sanitize_email( $settings['smtp_from_email'] ) : '';

		return ! empty( $settings['smtp_enabled'] ) && $email ? $email : $from;
	}

	/**
	 * Use the configured SMTP sender name.
	 *
	 * @param string $name Existing name.
	 * @return string
	 */
	public static function mail_from_name( $name ) {
		$settings  = ESC_Portal_Helpers::get_settings();
		$from_name = isset( $settings['smtp_from_name'] ) ? sanitize_text_field( $settings['smtp_from_name'] ) : '';

		return ! empty( $settings['smtp_enabled'] ) && $from_name ? $from_name : $name;
	}

	/**
	 * Encrypt an SMTP password before saving it.
	 *
	 * @param string $secret Plain secret.
	 * @return string
	 */
	public static function encrypt_secret( $secret ) {
		if ( '' === $secret || ! function_exists( 'openssl_encrypt' ) || ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return '';
		}

		$key = hash( 'sha256', wp_salt( 'auth' ), true );

		if ( in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true ) ) {
			$iv     = openssl_random_pseudo_bytes( 12 );
			$tag    = '';
			$cipher = openssl_encrypt( $secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );

			if ( false !== $cipher && 16 === strlen( $tag ) ) {
				return 'enc2:' . base64_encode( $iv . $tag . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			}
		}

		$iv     = openssl_random_pseudo_bytes( 16 );
		$cipher = openssl_encrypt( $secret, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $cipher ) {
			return '';
		}

		return 'enc:' . base64_encode( $iv . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypt a saved SMTP password.
	 *
	 * @param string $stored Encrypted secret.
	 * @return string
	 */
	public static function decrypt_secret( $stored ) {
		if ( ! is_string( $stored ) || ! function_exists( 'openssl_decrypt' ) ) {
			return '';
		}

		$key = hash( 'sha256', wp_salt( 'auth' ), true );

		if ( 0 === strpos( $stored, 'enc2:' ) ) {
			$raw = base64_decode( substr( $stored, 5 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

			if ( false === $raw || strlen( $raw ) <= 28 ) {
				return '';
			}

			$iv     = substr( $raw, 0, 12 );
			$tag    = substr( $raw, 12, 16 );
			$cipher = substr( $raw, 28 );
			$secret = openssl_decrypt( $cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );

			return false === $secret ? '' : $secret;
		}

		if ( 0 !== strpos( $stored, 'enc:' ) ) {
			return '';
		}

		$raw = base64_decode( substr( $stored, 4 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $raw || strlen( $raw ) <= 16 ) {
			return '';
		}

		$iv     = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		$secret = openssl_decrypt( $cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		return false === $secret ? '' : $secret;
	}

	/**
	 * @return string[]
	 */
	private static function headers() {
		return array( 'Content-Type: text/html; charset=UTF-8' );
	}

	/**
	 * @return string
	 */
	private static function from_name() {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * @param string $title Heading.
	 * @param string $body  Inner HTML.
	 * @return string
	 */
	private static function wrap( $title, $body ) {
		$site = esc_html( self::from_name() );

		return '<!DOCTYPE html><html><body style="font-family:Segoe UI,system-ui,-apple-system,Roboto,Helvetica Neue,Arial,sans-serif;color:#212121;line-height:1.5;background:#eef7e6;padding:24px;">'
			. '<div style="max-width:640px;margin:0 auto;background:#fff;padding:32px;border:1px solid #d7e8c8;border-radius:8px;">'
			. '<p style="margin:0 0 8px;letter-spacing:.12em;text-transform:uppercase;font-size:12px;color:#4caf50;">' . $site . '</p>'
			. '<h1 style="font-size:22px;margin:0 0 16px;">' . esc_html( $title ) . '</h1>'
			. $body
			. '</div></body></html>';
	}

	/**
	 * Welcome email after registration.
	 *
	 * @param int $user_id User ID.
	 */
	public static function welcome( $user_id ) {
		$user = ESC_Portal_Users::get( $user_id );

		if ( ! $user || ! $user->email ) {
			return;
		}

		$dashboard = ESC_Portal_Helpers::get_page_url( 'dashboard' );
		$careers   = ESC_Portal_Helpers::get_page_url( 'careers' );
		$role      = ESC_Portal_Users::role_label( $user->role );
		$body      = '<p>' . sprintf(
			/* translators: %s: first name */
			esc_html__( 'Hello %s,', 'es-care-portal' ),
			esc_html( $user->first_name ? $user->first_name : $user->display_name )
		) . '</p>';
		$body     .= '<p>' . sprintf(
			/* translators: %s: role label */
			esc_html__( 'Your %s account is ready.', 'es-care-portal' ),
			esc_html( $role )
		) . '</p>';
		$body     .= '<p><a href="' . esc_url( $dashboard ) . '">' . esc_html__( 'Open your dashboard', 'es-care-portal' ) . '</a> &nbsp;|&nbsp; ';
		$body     .= '<a href="' . esc_url( $careers ) . '">' . esc_html__( 'Browse careers', 'es-care-portal' ) . '</a></p>';

		wp_mail(
			$user->email,
			sprintf(
				/* translators: %s: site name */
				__( 'Welcome to %s', 'es-care-portal' ),
				self::from_name()
			),
			self::wrap( __( 'Account created', 'es-care-portal' ), $body ),
			self::headers()
		);
	}

	/**
	 * Password reset email for a portal user.
	 *
	 * @param object $user Portal user.
	 * @param string $key  Raw reset key.
	 */
	public static function password_reset( $user, $key ) {
		$reset = ESC_Portal_Helpers::get_page_url(
			'reset-password',
			array(
				'key'   => $key,
				'login' => rawurlencode( $user->email ),
			)
		);

		$body  = '<p>' . sprintf(
			/* translators: %s: first name */
			esc_html__( 'Hello %s,', 'es-care-portal' ),
			esc_html( $user->first_name ? $user->first_name : $user->display_name )
		) . '</p>';
		$body .= '<p>' . esc_html__( 'Use the link below to choose a new password. It expires in one hour.', 'es-care-portal' ) . '</p>';
		$body .= '<p><a href="' . esc_url( $reset ) . '">' . esc_html__( 'Reset your password', 'es-care-portal' ) . '</a></p>';

		wp_mail(
			$user->email,
			__( 'Reset your password', 'es-care-portal' ),
			self::wrap( __( 'Password reset', 'es-care-portal' ), $body ),
			self::headers()
		);
	}

	/**
	 * Applicant + staff notices for a new application.
	 *
	 * @param int $application_id Application ID.
	 */
	public static function application_received( $application_id ) {
		$snap = ESC_Portal_CPT_Application::get_snapshot( $application_id );
		$job  = get_the_title( $snap['job_id'] );

		if ( ! empty( $snap['email'] ) ) {
			$body  = '<p>' . sprintf(
				/* translators: %s: first name */
				esc_html__( 'Hello %s,', 'es-care-portal' ),
				esc_html( $snap['first_name'] )
			) . '</p>';
			$body .= '<p>' . sprintf(
				/* translators: %s: job title */
				esc_html__( 'We received your application for %s. Our team will review it and follow up if there is a match.', 'es-care-portal' ),
				esc_html( $job )
			) . '</p>';
			$body .= '<p><a href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) . '">' . esc_html__( 'View your applications', 'es-care-portal' ) . '</a></p>';

			wp_mail(
				$snap['email'],
				sprintf(
					/* translators: %s: job title */
					__( 'Application received: %s', 'es-care-portal' ),
					$job
				),
				self::wrap( __( 'Application received', 'es-care-portal' ), $body ),
				self::headers()
			);
		}

		$settings = ESC_Portal_Helpers::get_settings();
		$staff    = $settings['notification_email'];

		if ( $staff && is_email( $staff ) ) {
			$admin_url = add_query_arg(
				array(
					'page' => 'esc-application',
					'id'   => $application_id,
				),
				admin_url( 'admin.php' )
			);

			$body  = '<p>' . sprintf(
				/* translators: 1: applicant name, 2: job title */
				esc_html__( '%1$s applied for %2$s.', 'es-care-portal' ),
				esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ),
				esc_html( $job )
			) . '</p>';
			$body .= '<p><a href="' . esc_url( $admin_url ) . '">' . esc_html__( 'Review application', 'es-care-portal' ) . '</a></p>';

			wp_mail(
				$staff,
				sprintf(
					/* translators: %s: job title */
					__( 'New application: %s', 'es-care-portal' ),
					$job
				),
				self::wrap( __( 'New job application', 'es-care-portal' ), $body ),
				self::headers()
			);
		}

		$employer_id = (int) get_post_meta( $snap['job_id'], '_esc_employer_id', true );
		$employer    = $employer_id ? ESC_Portal_Users::get( $employer_id ) : null;

		if ( $employer && $employer->email && ( ! $staff || strtolower( $employer->email ) !== strtolower( $staff ) ) ) {
			$dash = ESC_Portal_Helpers::get_page_url( 'dashboard' );
			$body = '<p>' . sprintf(
				/* translators: 1: applicant name, 2: job title */
				esc_html__( '%1$s applied for %2$s.', 'es-care-portal' ),
				esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ),
				esc_html( $job )
			) . '</p>';
			$body .= '<p><a href="' . esc_url( $dash ) . '">' . esc_html__( 'Review in your dashboard', 'es-care-portal' ) . '</a></p>';

			wp_mail(
				$employer->email,
				sprintf(
					/* translators: %s: job title */
					__( 'New application: %s', 'es-care-portal' ),
					$job
				),
				self::wrap( __( 'New job application', 'es-care-portal' ), $body ),
				self::headers()
			);
		}
	}

	/**
	 * Confirm a service request to its sender and notify staff.
	 *
	 * @param object $user    Portal user.
	 * @param string $subject Request subject.
	 * @param string $message Request message.
	 */
	public static function service_request_received( $user, $subject, $message ) {
		if ( $user && ! empty( $user->email ) && is_email( $user->email ) ) {
			$body  = '<p>' . sprintf(
				/* translators: %s: first name */
				esc_html__( 'Hello %s,', 'es-care-portal' ),
				esc_html( $user->first_name ? $user->first_name : $user->display_name )
			) . '</p>';
			$body .= '<p>' . esc_html__( 'We received your service request and will follow up with you.', 'es-care-portal' ) . '</p>';
			$body .= '<p><strong>' . esc_html__( 'Subject:', 'es-care-portal' ) . '</strong> ' . esc_html( $subject ) . '</p>';
			$body .= '<p><strong>' . esc_html__( 'Message:', 'es-care-portal' ) . '</strong><br>' . nl2br( esc_html( $message ) ) . '</p>';

			wp_mail(
				$user->email,
				sprintf(
					/* translators: %s: request subject */
					__( 'Service request received: %s', 'es-care-portal' ),
					$subject
				),
				self::wrap( __( 'Service request received', 'es-care-portal' ), $body ),
				self::headers()
			);
		}

		$settings = ESC_Portal_Helpers::get_settings();
		$staff    = ! empty( $settings['notification_email'] ) ? $settings['notification_email'] : '';

		if ( $staff && is_email( $staff ) && ( ! $user || strtolower( $staff ) !== strtolower( $user->email ) ) ) {
			$body  = '<p>' . sprintf(
				/* translators: 1: sender name, 2: sender email */
				esc_html__( '%1$s (%2$s) submitted a service request.', 'es-care-portal' ),
				esc_html( $user ? $user->display_name : '' ),
				esc_html( $user ? $user->email : '' )
			) . '</p>';
			$body .= '<p><strong>' . esc_html__( 'Subject:', 'es-care-portal' ) . '</strong> ' . esc_html( $subject ) . '</p>';
			$body .= '<p><strong>' . esc_html__( 'Message:', 'es-care-portal' ) . '</strong><br>' . nl2br( esc_html( $message ) ) . '</p>';

			wp_mail(
				$staff,
				sprintf(
					/* translators: %s: request subject */
					__( 'Service request: %s', 'es-care-portal' ),
					$subject
				),
				self::wrap( __( 'New service request', 'es-care-portal' ), $body ),
				self::headers()
			);
		}
	}

	/**
	 * Confirm an assessment submission.
	 *
	 * @param object $user       Portal user.
	 * @param object $assessment Assessment row.
	 * @param object $result     Submission result.
	 */
	public static function assessment_received( $user, $assessment, $result ) {
		if ( ! $user || empty( $user->email ) || ! is_email( $user->email ) || ! $assessment || ! $result ) {
			return;
		}

		$outcome = $result->passed ? __( 'Passed', 'es-care-portal' ) : __( 'Not passed', 'es-care-portal' );
		$body    = '<p>' . sprintf(
			/* translators: %s: first name */
			esc_html__( 'Hello %s,', 'es-care-portal' ),
			esc_html( $user->first_name ? $user->first_name : $user->display_name )
		) . '</p>';
		$body   .= '<p>' . esc_html__( 'Your assessment submission was received and recorded.', 'es-care-portal' ) . '</p>';
		$body   .= '<p><strong>' . esc_html__( 'Assessment:', 'es-care-portal' ) . '</strong> ' . esc_html( $assessment->title ) . '<br>';
		$body   .= '<strong>' . esc_html__( 'Score:', 'es-care-portal' ) . '</strong> ' . esc_html( (string) $result->score ) . '%<br>';
		$body   .= '<strong>' . esc_html__( 'Result:', 'es-care-portal' ) . '</strong> ' . esc_html( $outcome ) . '</p>';
		$body   .= '<p><a href="' . esc_url( ESC_Portal_Helpers::dashboard_url( 'results' ) ) . '">' . esc_html__( 'View assessment results', 'es-care-portal' ) . '</a></p>';

		wp_mail(
			$user->email,
			sprintf(
				/* translators: %s: assessment title */
				__( 'Assessment received: %s', 'es-care-portal' ),
				$assessment->title
			),
			self::wrap( __( 'Assessment received', 'es-care-portal' ), $body ),
			self::headers()
		);
	}

	/**
	 * Confirm a job listing submission.
	 *
	 * @param object $user   Portal user.
	 * @param int    $job_id Job post ID.
	 * @param bool   $updated Whether an existing job was updated.
	 */
	public static function job_saved( $user, $job_id, $updated = false ) {
		if ( ! $user || empty( $user->email ) || ! is_email( $user->email ) ) {
			return;
		}

		$title = get_the_title( $job_id );
		$body  = '<p>' . sprintf(
			/* translators: %s: first name */
			esc_html__( 'Hello %s,', 'es-care-portal' ),
			esc_html( $user->first_name ? $user->first_name : $user->display_name )
		) . '</p>';
		$body .= '<p>' . ( $updated
			? esc_html__( 'Your job listing update was received and saved.', 'es-care-portal' )
			: esc_html__( 'Your job listing submission was received and published.', 'es-care-portal' )
		) . '</p>';
		$body .= '<p><strong>' . esc_html__( 'Job:', 'es-care-portal' ) . '</strong> ' . esc_html( $title ) . '</p>';
		$body .= '<p><a href="' . esc_url( get_permalink( $job_id ) ) . '">' . esc_html__( 'View job listing', 'es-care-portal' ) . '</a></p>';

		wp_mail(
			$user->email,
			sprintf(
				/* translators: %s: job title */
				__( 'Job listing received: %s', 'es-care-portal' ),
				$title
			),
			self::wrap( __( 'Job listing received', 'es-care-portal' ), $body ),
			self::headers()
		);
	}

	/**
	 * Notify applicant of a status change.
	 *
	 * @param int    $application_id Application ID.
	 * @param string $old_status     Previous status.
	 * @param string $new_status     New status.
	 */
	public static function status_changed( $application_id, $old_status, $new_status ) {
		if ( $old_status === $new_status ) {
			return;
		}

		if ( in_array( $new_status, array( 'pending' ), true ) ) {
			return;
		}

		$snap  = ESC_Portal_CPT_Application::get_snapshot( $application_id );
		$job   = get_the_title( $snap['job_id'] );
		$label = ESC_Portal_Helpers::format_status( $new_status );

		if ( empty( $snap['email'] ) ) {
			return;
		}

		$body  = '<p>' . sprintf(
			/* translators: %s: first name */
			esc_html__( 'Hello %s,', 'es-care-portal' ),
			esc_html( $snap['first_name'] )
		) . '</p>';
		$body .= '<p>' . sprintf(
			/* translators: 1: job title, 2: status label */
			esc_html__( 'The status of your application for %1$s is now: %2$s.', 'es-care-portal' ),
			esc_html( $job ),
			esc_html( $label )
		) . '</p>';
		$body .= '<p><a href="' . esc_url( ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) . '">' . esc_html__( 'View your dashboard', 'es-care-portal' ) . '</a></p>';

		wp_mail(
			$snap['email'],
			sprintf(
				/* translators: %s: job title */
				__( 'Application update: %s', 'es-care-portal' ),
				$job
			),
			self::wrap( __( 'Application update', 'es-care-portal' ), $body ),
			self::headers()
		);
	}
}

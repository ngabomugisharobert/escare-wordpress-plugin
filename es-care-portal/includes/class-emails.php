<?php
/**
 * Transactional emails.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Emails {

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

		return '<!DOCTYPE html><html><body style="font-family:Georgia,serif;color:#1c1917;line-height:1.5;background:#f6f1ea;padding:24px;">'
			. '<div style="max-width:640px;margin:0 auto;background:#fff;padding:32px;border:1px solid #e7e1d8;">'
			. '<p style="margin:0 0 8px;letter-spacing:.12em;text-transform:uppercase;font-size:12px;color:#1a7a74;">' . $site . '</p>'
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

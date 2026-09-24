<?php
/**
 * Employer frontend job posting.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Employer {

	/**
	 * Jobs owned by an employer (or all jobs for portal admins).
	 *
	 * @param int  $user_id Employer ID.
	 * @param bool $all     All jobs.
	 * @return WP_Post[]
	 */
	public static function jobs_for( $user_id, $all = false, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'posts_per_page' => 25,
				'paged'          => 1,
				's'              => '',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'meta_status'    => '',
			)
		);

		$query_args = array(
			'post_type'      => 'esc_job',
			'post_status'    => $args['post_status'],
			'posts_per_page' => min( 100, max( 1, absint( $args['posts_per_page'] ) ) ),
			'paged'          => max( 1, absint( $args['paged'] ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			's'              => $args['s'],
		);

		$meta_query = array();

		if ( ! $all ) {
			$meta_query[] = array(
				'key'   => '_esc_employer_id',
				'value' => (int) $user_id,
			);
		}

		if ( ! empty( $args['meta_status'] ) && isset( ESC_Portal_Helpers::job_statuses()[ $args['meta_status'] ] ) ) {
			$meta_query[] = array(
				'key'   => '_esc_job_status',
				'value' => $args['meta_status'],
			);
		}

		if ( $meta_query ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );

		return $query;
	}

	/**
	 * Save a job from the frontend form.
	 */
	public static function handle_save_job() {
		$fallback = ESC_Portal_Helpers::dashboard_url( 'post' );

		if ( ! empty( $_POST['esc_redirect_view'] ) && 'jobs' === sanitize_key( wp_unslash( $_POST['esc_redirect_view'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$fallback = ESC_Portal_Helpers::dashboard_url( 'jobs' );
		}

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login', array( 'redirect_to' => $fallback ) ), 'login-required', 'error' );
		}

		$user = ESC_Portal_Auth::current_user();

		if ( ! ESC_Portal_Users::is_employer( $user ) && ! ESC_Portal_Users::is_admin( $user ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'dashboard' ), 'not-allowed', 'error' );
		}

		if ( ! isset( $_POST['esc_job_front_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_job_front_nonce'] ) ), 'esc_save_job_front' ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'nonce', 'error' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'job_publish', (string) $user->id ) ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'rate-limited', 'error' );
		}

		$job_id  = isset( $_POST['esc_job_id'] ) ? absint( $_POST['esc_job_id'] ) : 0;
		$title   = isset( $_POST['esc_job_title'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_job_title'] ) ) : '';
		$body    = isset( $_POST['esc_job_content'] ) ? wp_kses_post( wp_unslash( $_POST['esc_job_content'] ) ) : '';
		$updated = (bool) $job_id;

		if ( ! $title || ! $body ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'required', 'error' );
		}

		if ( $job_id && ! ESC_Portal_Helpers::can_manage_job( $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'dashboard' ), 'not-allowed', 'error' );
		}

		$payload = array(
			'post_type'    => 'esc_job',
			'post_status'  => ESC_Portal_Users::is_admin( $user ) ? 'publish' : 'pending',
			'post_title'   => $title,
			'post_content' => $body,
			'post_author'  => 1,
		);

		if ( $job_id ) {
			$existing      = get_post( $job_id );
			$payload['ID'] = $job_id;

			// Keep the current WordPress status so editing a live listing does not unpublish it.
			if ( $existing && in_array( $existing->post_status, array( 'publish', 'pending', 'draft' ), true ) ) {
				$payload['post_status'] = $existing->post_status;
			}

			$result = wp_update_post( $payload, true );
		} else {
			$result = wp_insert_post( $payload, true );
		}

		if ( is_wp_error( $result ) || ! $result ) {
			ESC_Portal_Helpers::redirect_notice( $fallback, 'save-failed', 'error' );
		}

		$job_id = (int) $result;
		$owner  = ESC_Portal_Users::is_admin( $user ) && $job_id && get_post_meta( $job_id, '_esc_employer_id', true )
			? (int) get_post_meta( $job_id, '_esc_employer_id', true )
			: (int) $user->id;

		if ( ESC_Portal_Users::is_employer( $user ) || ! get_post_meta( $job_id, '_esc_employer_id', true ) ) {
			update_post_meta( $job_id, '_esc_employer_id', $owner );
		}

		$location = isset( $_POST['esc_location'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_location'] ) ) : '';
		$shift    = isset( $_POST['esc_shift'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_shift'] ) ) : '';
		$pay      = isset( $_POST['esc_pay_range'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_pay_range'] ) ) : '';
		$closing  = isset( $_POST['esc_closing_date'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_closing_date'] ) ) : '';
		$type     = isset( $_POST['esc_employment_type'] ) ? sanitize_key( wp_unslash( $_POST['esc_employment_type'] ) ) : '';
		$status   = isset( $_POST['esc_job_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_job_status'] ) ) : 'open';
		$cat      = isset( $_POST['esc_job_category'] ) ? absint( wp_unslash( $_POST['esc_job_category'] ) ) : 0;
		$assess_id = isset( $_POST['esc_assessment_id'] ) ? absint( wp_unslash( $_POST['esc_assessment_id'] ) ) : 0;

		$types    = ESC_Portal_Helpers::employment_types();
		$statuses = ESC_Portal_Helpers::job_statuses();

		if ( $type && ! isset( $types[ $type ] ) ) {
			$type = '';
		}

		if ( ! isset( $statuses[ $status ] ) ) {
			$status = 'open';
		}

		if ( $closing && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $closing ) ) {
			$closing = '';
		}

		if ( $assess_id && ! ESC_Portal_Assessments::get( $assess_id ) ) {
			$assess_id = 0;
		}

		update_post_meta( $job_id, '_esc_location', $location );
		update_post_meta( $job_id, '_esc_shift', $shift );
		update_post_meta( $job_id, '_esc_pay_range', $pay );
		update_post_meta( $job_id, '_esc_closing_date', $closing );
		update_post_meta( $job_id, '_esc_employment_type', $type );
		update_post_meta( $job_id, '_esc_job_status', $status );

		if ( $assess_id ) {
			update_post_meta( $job_id, '_esc_assessment_id', $assess_id );
		} else {
			delete_post_meta( $job_id, '_esc_assessment_id' );
		}

		if ( $cat ) {
			wp_set_object_terms( $job_id, array( $cat ), 'esc_job_category' );
		}

		ESC_Portal_Emails::job_saved( $user, $job_id, $updated );
		if ( ! $updated && ! ESC_Portal_Users::is_admin( $user ) ) {
			ESC_Portal_Emails::job_pending_review( $user, $job_id );
		}
		wp_cache_delete( 'esc_job_locations', 'esc_portal' );

		if ( $updated ) {
			$notice = 'job-updated';
		} elseif ( ! ESC_Portal_Users::is_admin( $user ) && 'publish' !== get_post_status( $job_id ) ) {
			$notice = 'job-pending';
		} else {
			$notice = 'job-saved';
		}

		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::dashboard_url( 'jobs' ), $notice, 'success' );
	}

	/**
	 * Delete a job listing.
	 */
	public static function handle_delete_job() {
		$dashboard = ESC_Portal_Helpers::dashboard_url( 'jobs' );

		if ( ! ESC_Portal_Auth::is_logged_in() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_delete_job_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_delete_job_nonce'] ) ), 'esc_delete_job_front' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'nonce', 'error' );
		}

		$job_id = isset( $_POST['esc_job_id'] ) ? absint( $_POST['esc_job_id'] ) : 0;

		if ( ! $job_id || ! ESC_Portal_Helpers::can_manage_job( $job_id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dashboard, 'not-allowed', 'error' );
		}

		wp_trash_post( $job_id );
		ESC_Portal_Helpers::redirect_notice( $dashboard, 'job-deleted', 'success' );
	}
}

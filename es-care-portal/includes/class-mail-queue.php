<?php
/**
 * Queued transactional email with bounded retries.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Mail_Queue {

	const CRON_HOOK = 'esc_portal_process_mail_queue';
	const MAX_TRIES = 5;
	const BATCH     = 10;

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_mail_queue';
	}

	/**
	 * Create the queue table.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mail_to varchar(190) NOT NULL,
				subject varchar(255) NOT NULL,
				body longtext NOT NULL,
				headers longtext,
				status varchar(20) NOT NULL DEFAULT 'queued',
				attempts tinyint(3) unsigned NOT NULL DEFAULT 0,
				next_attempt datetime NOT NULL,
				last_error varchar(255) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY status_next (status, next_attempt)
			) {$charset};"
		);
	}

	/**
	 * Register cron.
	 */
	public static function init() {
		add_action( self::CRON_HOOK, array( __CLASS__, 'process' ) );
		add_action( 'wp_mail_failed', array( __CLASS__, 'capture_failure' ) );
		self::schedule();
	}

	/**
	 * Ensure the cron event exists.
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, 'hourly', self::CRON_HOOK );
		}

		if ( ! wp_next_scheduled( 'esc_portal_process_mail_queue_fast' ) ) {
			wp_schedule_event( time() + 30, 'esc_portal_five_minutes', 'esc_portal_process_mail_queue_fast' );
		}

		add_action( 'esc_portal_process_mail_queue_fast', array( __CLASS__, 'process' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 * @param array $schedules Schedules.
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		if ( ! isset( $schedules['esc_portal_five_minutes'] ) ) {
			$schedules['esc_portal_five_minutes'] = array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every five minutes (ES Care Portal)', 'es-care-portal' ),
			);
		}

		return $schedules;
	}

	/**
	 * Queue a portal email. Stores no extra applicant PII beyond the message itself.
	 *
	 * @param string          $to      Recipient.
	 * @param string          $subject Subject.
	 * @param string          $body    HTML body.
	 * @param string|string[] $headers Headers.
	 * @return int|false Queue ID.
	 */
	public static function enqueue( $to, $subject, $body, $headers = array() ) {
		global $wpdb;

		if ( ! is_email( $to ) ) {
			return false;
		}

		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			self::table(),
			array(
				'mail_to'      => sanitize_email( $to ),
				'subject'      => sanitize_text_field( $subject ),
				'body'         => (string) $body,
				'headers'      => wp_json_encode( (array) $headers ),
				'status'       => 'queued',
				'attempts'     => 0,
				'next_attempt' => $now,
				'created_at'   => $now,
				'updated_at'   => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		if ( ! $ok ) {
			ESC_Portal_Health::log( 'error', 'mail', 'Failed to enqueue mail for ' . sanitize_email( $to ) );
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Send due messages.
	 */
	public static function process() {
		global $wpdb;

		$table = self::table();
		$now   = current_time( 'mysql' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = %s AND next_attempt <= %s ORDER BY id ASC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'queued',
				$now,
				self::BATCH
			)
		);

		if ( ! $rows ) {
			self::cleanup();
			return;
		}

		foreach ( $rows as $row ) {
			self::send_row( $row );
		}

		self::cleanup();
	}

	/**
	 * Retry a single queued message from admin.
	 *
	 * @param int $id Queue ID.
	 * @return bool
	 */
	public static function retry( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', absint( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $row ) {
			return false;
		}

		$wpdb->update(
			self::table(),
			array(
				'status'       => 'queued',
				'next_attempt' => current_time( 'mysql' ),
				'updated_at'   => current_time( 'mysql' ),
			),
			array( 'id' => (int) $row->id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		$row->status = 'queued';
		return self::send_row( $row );
	}

	/**
	 * @return array{queued:int,failed:int,sent:int}
	 */
	public static function counts() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT status, COUNT(*) AS total FROM ' . self::table() . ' GROUP BY status' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$out  = array(
			'queued' => 0,
			'failed' => 0,
			'sent'   => 0,
		);

		if ( $rows ) {
			foreach ( $rows as $row ) {
				if ( isset( $out[ $row->status ] ) ) {
					$out[ $row->status ] = (int) $row->total;
				}
			}
		}

		return $out;
	}

	/**
	 * Recent failed messages for manual retry.
	 *
	 * @param int $limit Limit.
	 * @return object[]
	 */
	public static function failed_rows( $limit = 10 ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, mail_to, subject, last_error, updated_at FROM ' . self::table() . ' WHERE status = %s ORDER BY id DESC LIMIT %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'failed',
				max( 1, absint( $limit ) )
			)
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Last failed wp_mail() message captured during a send.
	 *
	 * @var string
	 */
	private static $last_error = '';

	/**
	 * @param WP_Error $error Mail error.
	 */
	public static function capture_failure( $error ) {
		if ( ! $error instanceof WP_Error ) {
			return;
		}

		self::$last_error = self::sanitize_error( $error->get_error_message() );
	}

	/**
	 * @param object $row Queue row.
	 * @return bool
	 */
	private static function send_row( $row ) {
		global $wpdb;

		self::$last_error = '';
		$headers          = json_decode( (string) $row->headers, true );
		if ( ! is_array( $headers ) ) {
			$headers = array();
		}

		$sent = ESC_Portal_Emails::send_now( $row->mail_to, $row->subject, $row->body, $headers );
		$now  = current_time( 'mysql' );

		if ( $sent ) {
			$wpdb->update(
				self::table(),
				array(
					'status'     => 'sent',
					'attempts'   => (int) $row->attempts + 1,
					'last_error' => '',
					'updated_at' => $now,
				),
				array( 'id' => (int) $row->id ),
				array( '%s', '%d', '%s', '%s' ),
				array( '%d' )
			);
			return true;
		}

		$attempts = (int) $row->attempts + 1;
		$failed   = $attempts >= self::MAX_TRIES;
		$delay    = min( DAY_IN_SECONDS, pow( 2, $attempts ) * MINUTE_IN_SECONDS );
		$error    = self::$last_error ? self::$last_error : __( 'Delivery failed.', 'es-care-portal' );

		$wpdb->update(
			self::table(),
			array(
				'status'       => $failed ? 'failed' : 'queued',
				'attempts'     => $attempts,
				'next_attempt' => gmdate( 'Y-m-d H:i:s', time() + $delay ),
				'last_error'   => $error,
				'updated_at'   => $now,
			),
			array( 'id' => (int) $row->id ),
			array( '%s', '%d', '%s', '%s', '%s' ),
			array( '%d' )
		);

		ESC_Portal_Health::log( $failed ? 'error' : 'warning', 'mail', 'Mail #' . (int) $row->id . ' failed: ' . $error );

		return false;
	}

	/**
	 * Drop old sent/failed rows.
	 */
	private static function cleanup() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . self::table() . ' WHERE status = %s AND updated_at < %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'sent',
				gmdate( 'Y-m-d H:i:s', time() - 14 * DAY_IN_SECONDS )
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . self::table() . ' WHERE status = %s AND updated_at < %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'failed',
				gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS )
			)
		);
	}

	/**
	 * @param string $message Raw error.
	 * @return string
	 */
	private static function sanitize_error( $message ) {
		$message = wp_strip_all_tags( (string) $message );
		$message = preg_replace( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[redacted-email]', $message );
		$message = preg_replace( '/\b(?:password|passwd|pwd|secret|token)\b[^\s]*/i', '[redacted]', $message );

		return substr( (string) $message, 0, 240 );
	}
}

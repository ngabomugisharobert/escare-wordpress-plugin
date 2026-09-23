<?php
/**
 * Sanitized operational logs and admin health indicators.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Health {

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_logs';
	}

	/**
	 * Create the log table.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				level varchar(20) NOT NULL DEFAULT 'info',
				channel varchar(40) NOT NULL DEFAULT 'portal',
				message varchar(255) NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY created_at (created_at),
				KEY level (level)
			) {$charset};"
		);
	}

	/**
	 * @param string $level   info|warning|error.
	 * @param string $channel Channel.
	 * @param string $message Sanitized message.
	 */
	public static function log( $level, $channel, $message ) {
		global $wpdb;

		$level   = in_array( $level, array( 'info', 'warning', 'error' ), true ) ? $level : 'info';
		$channel = sanitize_key( $channel );
		$message = self::sanitize_message( $message );

		if ( ! $message ) {
			return;
		}

		$wpdb->insert(
			self::table(),
			array(
				'level'      => $level,
				'channel'    => $channel,
				'message'    => $message,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Snapshot used by the admin health screen.
	 *
	 * @return array
	 */
	public static function snapshot() {
		$mail     = ESC_Portal_Mail_Queue::counts();
		$storage  = ESC_Portal_Uploads::health();
		$cron     = wp_next_scheduled( ESC_Portal_Mail_Queue::CRON_HOOK );
		$retain   = wp_next_scheduled( ESC_Portal_Privacy::CRON_HOOK );
		$last     = get_option( 'esc_portal_retention_last_run', array() );
		$settings = ESC_Portal_Helpers::get_settings();

		return array(
			'schema_version'     => (int) get_option( ESC_Portal_Schema::SCHEMA_KEY, 0 ),
			'target_schema'      => ESC_Portal_Schema::TARGET,
			'plugin_version'     => ESC_PORTAL_VERSION,
			'mail_queued'        => $mail['queued'],
			'mail_failed'        => $mail['failed'],
			'mail_sent'          => $mail['sent'],
			'mail_cron'          => $cron ? gmdate( 'c', $cron ) : '',
			'retention_cron'     => $retain ? gmdate( 'c', $retain ) : '',
			'retention_last'     => $last,
			'storage_writable'   => ! empty( $storage['writable'] ),
			'storage_path'       => isset( $storage['path'] ) ? $storage['path'] : '',
			'storage_private'    => ! empty( $storage['outside_uploads'] ),
			'http_denied'        => ! empty( $storage['http_denied'] ),
			'smtp_enabled'       => ! empty( $settings['smtp_enabled'] ),
			'smtp_conflicts'     => ESC_Portal_Emails::conflicting_plugins(),
			'migrating'          => (bool) get_transient( 'esc_portal_migrating' ),
			'identity_purged'    => get_option( 'esc_portal_identity_purged', array() ),
			'failed_mail'        => ESC_Portal_Mail_Queue::failed_rows( 10 ),
			'logs'               => self::recent( 20 ),
		);
	}

	/**
	 * @param int $limit Limit.
	 * @return object[]
	 */
	public static function recent( $limit = 20 ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' ORDER BY id DESC LIMIT %d', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				max( 1, absint( $limit ) )
			)
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param string $message Message.
	 * @return string
	 */
	private static function sanitize_message( $message ) {
		$message = wp_strip_all_tags( (string) $message );
		$message = preg_replace( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[redacted-email]', $message );
		$message = preg_replace( '/\b(?:password|passwd|secret|token|ssn)\b[^\s]*/i', '[redacted]', $message );

		return substr( (string) $message, 0, 240 );
	}
}

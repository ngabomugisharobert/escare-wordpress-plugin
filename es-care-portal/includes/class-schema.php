<?php
/**
 * Versioned schema and data migrations.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Schema {

	const SCHEMA_KEY = 'esc_portal_schema_version';
	const TARGET     = 3;

	/**
	 * Run pending migrations at most once per request.
	 */
	public static function maybe_upgrade() {
		$current = (int) get_option( self::SCHEMA_KEY, 0 );

		if ( $current >= self::TARGET ) {
			return;
		}

		if ( get_transient( 'esc_portal_migrating' ) ) {
			return;
		}

		set_transient( 'esc_portal_migrating', 1, 5 * MINUTE_IN_SECONDS );

		try {
			self::install_tables();
			ESC_Portal_Roles::add_roles();

			for ( $version = $current + 1; $version <= self::TARGET; $version++ ) {
				$method = 'migrate_' . $version;
				if ( is_callable( array( __CLASS__, $method ) ) ) {
					call_user_func( array( __CLASS__, $method ) );
				}
				update_option( self::SCHEMA_KEY, $version, false );
			}

			update_option( ESC_Portal_Helpers::VERSION_KEY, ESC_PORTAL_VERSION, false );
			ESC_Portal_Health::log( 'info', 'schema', 'Schema upgraded to version ' . self::TARGET . '.' );
		} catch ( Exception $exception ) {
			ESC_Portal_Health::log( 'error', 'schema', 'Schema upgrade failed: ' . $exception->getMessage() );
		}

		delete_transient( 'esc_portal_migrating' );
	}

	/**
	 * Create or update all plugin tables.
	 */
	public static function install_tables() {
		ESC_Portal_Users::install();
		ESC_Portal_Assessments::install();
		ESC_Portal_Forms::install();
		ESC_Portal_Rate_Limit::install();
		ESC_Portal_Mail_Queue::install();
		ESC_Portal_Health::install();
	}

	/**
	 * Schema 2: indexes, unique usermeta, purge identity fields, private storage.
	 */
	protected static function migrate_2() {
		self::dedupe_usermeta();
		self::apply_indexes();
		ESC_Portal_Privacy::purge_identity_fields();
		ESC_Portal_Uploads::migrate_existing_files();
		ESC_Portal_Forms::migrate_existing_files();
		ESC_Portal_Emails::reencrypt_legacy_secret();
		ESC_Portal_Activator::ensure_settings();
		ESC_Portal_Mail_Queue::schedule();
		ESC_Portal_Privacy::schedule();
	}

	/**
	 * Keep a single row per user/meta_key before adding a unique index.
	 */
	protected static function dedupe_usermeta() {
		global $wpdb;

		$table = ESC_Portal_Users::meta_table();
		$dups  = $wpdb->get_results( "SELECT user_id, meta_key, MAX(umeta_id) AS keep_id FROM {$table} GROUP BY user_id, meta_key HAVING COUNT(*) > 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $dups ) {
			return;
		}

		foreach ( $dups as $dup ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$table} WHERE user_id = %d AND meta_key = %s AND umeta_id <> %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					(int) $dup->user_id,
					$dup->meta_key,
					(int) $dup->keep_id
				)
			);
		}
	}

	/**
	 * Add composite indexes that match common queries.
	 */
	protected static function apply_indexes() {
		self::maybe_add_index( ESC_Portal_Users::meta_table(), 'user_meta', 'UNIQUE KEY user_meta (user_id, meta_key)' );
		self::maybe_add_index( ESC_Portal_Users::sessions_table(), 'user_token_expires', 'KEY user_token_expires (user_id, token_hash, expires)' );
		self::maybe_add_index( ESC_Portal_Assessments::attempts_table(), 'user_assessment_id', 'KEY user_assessment_id (user_id, assessment_id, id)' );
		self::maybe_add_index( ESC_Portal_Forms::requests_table(), 'user_created', 'KEY user_created (user_id, created_at)' );
	}

	/**
	 * Schema 3: guest contact messages and a public Contact Us page.
	 */
	protected static function migrate_3() {
		self::maybe_add_column( ESC_Portal_Forms::requests_table(), 'name', 'name varchar(190) NOT NULL DEFAULT \'\' AFTER user_id' );
		self::maybe_add_column( ESC_Portal_Forms::requests_table(), 'email', 'email varchar(190) NOT NULL DEFAULT \'\' AFTER name' );
		self::maybe_add_index( ESC_Portal_Forms::requests_table(), 'created_at', 'KEY created_at (created_at)' );
		ESC_Portal_Activator::create_pages();
	}

	/**
	 * @param string $table  Table name.
	 * @param string $column Column name.
	 * @param string $ddl    Column DDL fragment.
	 */
	protected static function maybe_add_column( $table, $column, $ddl ) {
		global $wpdb;

		$found = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table . ' LIKE %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$column
			)
		);

		if ( $found ) {
			return;
		}

		$wpdb->query( "ALTER TABLE {$table} ADD {$ddl}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param string $table Table name.
	 * @param string $name  Index name.
	 * @param string $ddl   Index DDL fragment.
	 */
	protected static function maybe_add_index( $table, $name, $ddl ) {
		global $wpdb;

		$found = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW INDEX FROM ' . $table . ' WHERE Key_name = %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$name
			)
		);

		if ( $found ) {
			return;
		}

		$wpdb->query( "ALTER TABLE {$table} ADD {$ddl}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}

<?php
/**
 * Custom portal users, meta, and sessions — not WordPress users.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Users {

	const MIN_PASSWORD_LENGTH = 8;

	const ROLE_SEEKER   = 'job_seeker';
	const ROLE_EMPLOYER = 'employer';
	const ROLE_ADMIN    = 'admin';

	const STATUS_PENDING_EMAIL = 'pending_email';
	const STATUS_PENDING_ADMIN = 'pending_admin';
	const STATUS_ACTIVE        = 'active';
	const STATUS_DISABLED      = 'disabled';

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_users';
	}

	/**
	 * @return string
	 */
	public static function meta_table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_usermeta';
	}

	/**
	 * @return string
	 */
	public static function sessions_table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_sessions';
	}

	/**
	 * @return array<string,string>
	 */
	public static function roles() {
		return array(
			self::ROLE_SEEKER   => __( 'Job Seeker', 'es-care-portal' ),
			self::ROLE_EMPLOYER => __( 'Employer', 'es-care-portal' ),
			self::ROLE_ADMIN    => __( 'Admin', 'es-care-portal' ),
		);
	}

	/**
	 * Roles that can self-register.
	 *
	 * @return string[]
	 */
	public static function public_roles() {
		return array( self::ROLE_SEEKER, self::ROLE_EMPLOYER );
	}

	/**
	 * @return string[]
	 */
	public static function statuses() {
		return array(
			self::STATUS_PENDING_EMAIL => __( 'Pending activation', 'es-care-portal' ),
			self::STATUS_PENDING_ADMIN => __( 'Pending approval', 'es-care-portal' ),
			self::STATUS_ACTIVE        => __( 'Active', 'es-care-portal' ),
			self::STATUS_DISABLED      => __( 'Disabled', 'es-care-portal' ),
		);
	}

	/**
	 * @param string $status Status key.
	 * @return bool
	 */
	public static function is_valid_status( $status ) {
		return isset( self::statuses()[ $status ] );
	}

	/**
	 * Create plugin tables.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$users   = self::table();
		$meta    = self::meta_table();
		$sess    = self::sessions_table();

		dbDelta(
			"CREATE TABLE {$users} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(190) NOT NULL,
				password varchar(255) NOT NULL,
				first_name varchar(100) NOT NULL DEFAULT '',
				last_name varchar(100) NOT NULL DEFAULT '',
				phone varchar(50) NOT NULL DEFAULT '',
				role varchar(20) NOT NULL DEFAULT 'job_seeker',
				company_name varchar(190) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'active',
				reset_key varchar(64) NOT NULL DEFAULT '',
				reset_expires datetime DEFAULT NULL,
				last_login datetime DEFAULT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				KEY role (role),
				KEY status (status)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$meta} (
				umeta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				meta_key varchar(191) NOT NULL DEFAULT '',
				meta_value longtext,
				PRIMARY KEY  (umeta_id),
				KEY user_id (user_id),
				KEY meta_key (meta_key(191)),
				UNIQUE KEY user_meta (user_id, meta_key)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$sess} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				token_hash varchar(64) NOT NULL,
				expires datetime NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY token_hash (token_hash),
				KEY expires (expires),
				KEY user_token_expires (user_id, token_hash, expires)
			) {$charset};"
		);
	}

	/**
	 * Whether the dashboard users table exists.
	 *
	 * @return bool
	 */
	public static function tables_exist() {
		global $wpdb;

		$name = self::table();
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $name ) );

		return $found === $name;
	}

	/**
	 * Create tables if missing. Prefer schema migrations over calling this on every request.
	 */
	public static function ensure_tables() {
		if ( ! self::tables_exist() ) {
			self::install();
		}
	}

	/**
	 * @return int
	 */
	public static function query_count( $args = array() ) {
		global $wpdb;

		$sql = self::build_query_sql( $args, true );
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param int[] $ids User IDs.
	 * @return array<int,object>
	 */
	public static function get_many( $ids ) {
		global $wpdb;

		$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );

		if ( ! $ids ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id IN (' . $placeholders . ')', $ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows         = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$out          = array();

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$user = self::hydrate( $row );
				if ( $user ) {
					$out[ $user->id ] = $user;
				}
			}
		}

		return $out;
	}

	/**
	 * Counts of dashboard users by role.
	 *
	 * @return array<string,int>
	 */
	public static function counts_by_role() {
		global $wpdb;

		self::ensure_tables();

		$counts = array(
			self::ROLE_SEEKER   => 0,
			self::ROLE_EMPLOYER => 0,
			self::ROLE_ADMIN    => 0,
		);

		if ( ! self::tables_exist() ) {
			return $counts;
		}

		$rows = $wpdb->get_results( 'SELECT role, COUNT(*) AS total FROM ' . self::table() . ' GROUP BY role' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $rows ) {
			foreach ( $rows as $row ) {
				if ( isset( $counts[ $row->role ] ) ) {
					$counts[ $row->role ] = (int) $row->total;
				}
			}
		}

		return $counts;
	}

	/**
	 * Normalize a DB row into an object.
	 *
	 * @param object|array|null $row Row.
	 * @return object|null
	 */
	public static function hydrate( $row ) {
		if ( ! $row ) {
			return null;
		}

		$row = (object) $row;
		$row->id           = (int) $row->id;
		$row->email        = (string) $row->email;
		$row->first_name   = (string) $row->first_name;
		$row->last_name    = (string) $row->last_name;
		$row->phone        = (string) $row->phone;
		$row->role         = (string) $row->role;
		$row->company_name = (string) $row->company_name;
		$row->status       = (string) $row->status;
		$row->display_name = trim( $row->first_name . ' ' . $row->last_name );

		if ( ! $row->display_name ) {
			$row->display_name = $row->email;
		}

		return $row;
	}

	/**
	 * @param int $id User ID.
	 * @return object|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id ) {
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return self::hydrate( $row );
	}

	/**
	 * @param string $email Email.
	 * @return object|null
	 */
	public static function get_by_email( $email ) {
		global $wpdb;

		$email = sanitize_email( $email );

		if ( ! $email ) {
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE email = %s', $email ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return self::hydrate( $row );
	}

	/**
	 * @param array $args Query args.
	 * @return object[]
	 */
	public static function query( $args = array() ) {
		global $wpdb;

		$sql  = self::build_query_sql( $args, false );
		$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$out  = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$out[] = self::hydrate( $row );
			}
		}

		return $out;
	}

	/**
	 * @param array $args    Query args.
	 * @param bool  $count   Count only.
	 * @return string
	 */
	private static function build_query_sql( $args, $count = false ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'role'     => '',
				'status'   => '',
				'search'   => '',
				'number'   => 25,
				'offset'   => 0,
				'orderby'  => 'created_at',
				'order'    => 'DESC',
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( $args['role'] && isset( self::roles()[ $args['role'] ] ) ) {
			$where[]  = 'role = %s';
			$params[] = $args['role'];
		}

		if ( $args['status'] && self::is_valid_status( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}

		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(email LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR company_name LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$orderby = in_array( $args['orderby'], array( 'created_at', 'email', 'role', 'last_name', 'status' ), true ) ? $args['orderby'] : 'created_at';
		$order   = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$limit   = min( 100, max( 1, absint( $args['number'] ) ) );
		$offset  = max( 0, absint( $args['offset'] ) );

		$select = $count ? 'SELECT COUNT(*)' : 'SELECT *';
		$sql    = $select . ' FROM ' . self::table() . ' WHERE ' . implode( ' AND ', $where );

		if ( ! $count ) {
			$sql .= " ORDER BY {$orderby} {$order} LIMIT {$offset}, {$limit}";
		}

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return $sql;
	}

	/**
	 * Require a long password with mixed character classes.
	 *
	 * @param string $password Plain password.
	 * @return bool
	 */
	public static function is_strong_password( $password ) {
		$password = (string) $password;

		return strlen( $password ) >= self::MIN_PASSWORD_LENGTH
			&& (bool) preg_match( '/[a-z]/', $password )
			&& (bool) preg_match( '/[A-Z]/', $password )
			&& (bool) preg_match( '/[0-9]/', $password );
	}

	/**
	 * @param array $data User data.
	 * @return int|WP_Error
	 */
	public static function create( $data ) {
		global $wpdb;

		self::ensure_tables();

		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$role  = isset( $data['role'] ) ? sanitize_key( $data['role'] ) : self::ROLE_SEEKER;
		$password = isset( $data['password'] ) ? (string) $data['password'] : '';

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'esc_email', __( 'Please enter a valid email address.', 'es-care-portal' ) );
		}

		if ( $password && ! self::is_strong_password( $password ) ) {
			return new WP_Error( 'esc_password', __( 'Use at least 8 characters with uppercase, lowercase, and a number.', 'es-care-portal' ) );
		}

		if ( ! isset( self::roles()[ $role ] ) ) {
			$role = self::ROLE_SEEKER;
		}

		if ( self::get_by_email( $email ) ) {
			return new WP_Error( 'esc_exists', __( 'An account with that email already exists. Sign in instead.', 'es-care-portal' ) );
		}

		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			self::table(),
			array(
				'email'        => $email,
				'password'     => wp_hash_password( $password ? $password : wp_generate_password( 20, true, true ) ),
				'first_name'   => isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : '',
				'last_name'    => isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '',
				'phone'        => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
				'role'         => $role,
				'company_name' => isset( $data['company_name'] ) ? sanitize_text_field( $data['company_name'] ) : '',
				'status'       => isset( $data['status'] ) && self::is_valid_status( sanitize_key( $data['status'] ) ) ? sanitize_key( $data['status'] ) : self::STATUS_ACTIVE,
				'created_at'   => $now,
				'updated_at'   => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $ok ) {
			return new WP_Error( 'esc_create', __( 'The account could not be created.', 'es-care-portal' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * @param int   $id   User ID.
	 * @param array $data Fields.
	 * @return bool
	 */
	public static function update( $id, $data ) {
		global $wpdb;

		$id  = absint( $id );
		$set = array( 'updated_at' => current_time( 'mysql' ) );
		$fmt = array( '%s' );

		$map = array(
			'first_name'   => '%s',
			'last_name'    => '%s',
			'phone'        => '%s',
			'company_name' => '%s',
			'role'         => '%s',
			'status'       => '%s',
			'email'        => '%s',
			'reset_key'    => '%s',
			'reset_expires'=> '%s',
			'last_login'   => '%s',
		);

		foreach ( $map as $key => $placeholder ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$value = $data[ $key ];

			if ( 'email' === $key ) {
				$value = sanitize_email( $value );
			} elseif ( 'role' === $key ) {
				$value = sanitize_key( $value );
				if ( ! isset( self::roles()[ $value ] ) ) {
					continue;
				}
			} elseif ( 'status' === $key ) {
				$value = sanitize_key( $value );
				if ( ! self::is_valid_status( $value ) ) {
					continue;
				}
			} elseif ( in_array( $key, array( 'reset_expires', 'last_login' ), true ) ) {
				$value = $value ? $value : null;
			} else {
				$value = sanitize_text_field( (string) $value );
			}

			$set[ $key ] = $value;
			$fmt[]       = $placeholder;
		}

		if ( ! empty( $data['password'] ) && self::is_strong_password( $data['password'] ) ) {
			$set['password'] = wp_hash_password( $data['password'] );
			$fmt[]           = '%s';
		}

		return false !== $wpdb->update( self::table(), $set, array( 'id' => $id ), $fmt, array( '%d' ) );
	}

	/**
	 * Permanently delete a dashboard user and associated custom-table data.
	 *
	 * Jobs and applications are retained as business records.
	 *
	 * @param int $id Dashboard user ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id || ! self::get( $id ) ) {
			return false;
		}

		ESC_Portal_Privacy::anonymize_user_records( $id );

		$wpdb->delete( self::sessions_table(), array( 'user_id' => $id ), array( '%d' ) );
		$wpdb->delete( self::meta_table(), array( 'user_id' => $id ), array( '%d' ) );
		$wpdb->delete( ESC_Portal_Assessments::attempts_table(), array( 'user_id' => $id ), array( '%d' ) );
		$wpdb->delete( ESC_Portal_Forms::requests_table(), array( 'user_id' => $id ), array( '%d' ) );

		return false !== $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * @param string $email    Email.
	 * @param string $password Plain password.
	 * @return object|WP_Error
	 */
	public static function authenticate( $email, $password ) {
		$user = self::get_by_email( $email );

		if ( ! $user ) {
			return new WP_Error( 'esc_login', __( 'The email or password is incorrect.', 'es-care-portal' ) );
		}

		$row = self::get_row_raw( $user->id );

		if ( ! $row || ! wp_check_password( $password, $row->password ) ) {
			return new WP_Error( 'esc_login', __( 'The email or password is incorrect.', 'es-care-portal' ) );
		}

		if ( self::STATUS_DISABLED === $user->status ) {
			return new WP_Error( 'esc_disabled', __( 'This account has been disabled.', 'es-care-portal' ) );
		}

		if ( self::STATUS_PENDING_EMAIL === $user->status ) {
			return new WP_Error( 'esc_pending_email', __( 'Please activate your account from the email we sent before signing in.', 'es-care-portal' ) );
		}

		return $user;
	}

	/**
	 * Whether this account has a pending deletion request.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function deletion_requested( $user_id ) {
		return (bool) self::get_meta( absint( $user_id ), 'deletion_requested_at', '' );
	}

	/**
	 * Activate accounts that were waiting for administrator approval.
	 *
	 * @return int Rows updated.
	 */
	public static function activate_awaiting_approval() {
		global $wpdb;

		if ( ! self::tables_exist() ) {
			return 0;
		}

		return (int) $wpdb->update(
			self::table(),
			array(
				'status'     => self::STATUS_ACTIVE,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'status' => self::STATUS_PENDING_ADMIN ),
			array( '%s', '%s' ),
			array( '%s' )
		);
	}

	/**
	 * Raw row including password hash.
	 *
	 * @param int $id User ID.
	 * @return object|null
	 */
	public static function get_row_raw( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', absint( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	public static function get_meta( $user_id, $key, $default = '' ) {
		global $wpdb;

		$value = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT meta_value FROM ' . self::meta_table() . ' WHERE user_id = %d AND meta_key = %s LIMIT 1', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $user_id ),
				$key
			)
		);

		if ( null === $value ) {
			return $default;
		}

		$maybe = maybe_unserialize( $value );

		return $maybe;
	}

	/**
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Value.
	 */
	public static function update_meta( $user_id, $key, $value ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$key     = sanitize_key( $key );
		$stored  = maybe_serialize( $value );
		$table   = self::meta_table();

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (user_id, meta_key, meta_value) VALUES (%d, %s, %s)
				ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$user_id,
				$key,
				$stored
			)
		);
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	public static function status_label( $status ) {
		$statuses = self::statuses();
		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
	}

	/**
	 * @param object|null $user User.
	 * @return bool
	 */
	public static function is_seeker( $user = null ) {
		$user = $user ? $user : ESC_Portal_Auth::current_user();
		return $user && self::ROLE_SEEKER === $user->role;
	}

	/**
	 * @param object|null $user User.
	 * @return bool
	 */
	public static function is_employer( $user = null ) {
		$user = $user ? $user : ESC_Portal_Auth::current_user();
		return $user && self::ROLE_EMPLOYER === $user->role;
	}

	/**
	 * @param object|null $user User.
	 * @return bool
	 */
	public static function is_admin( $user = null ) {
		$user = $user ? $user : ESC_Portal_Auth::current_user();
		return $user && self::ROLE_ADMIN === $user->role;
	}

	/**
	 * Guests and job seekers may apply. Portal admin and employer sessions may not.
	 * A WordPress administrator with no portal session is treated as a guest.
	 *
	 * @param object|null|false $user Portal user. false uses the current portal session.
	 * @return bool
	 */
	public static function can_apply_to_jobs( $user = false ) {
		if ( false === $user ) {
			$user = class_exists( 'ESC_Portal_Auth' ) ? ESC_Portal_Auth::current_user() : null;
		}

		if ( ! $user ) {
			return true;
		}

		return self::is_seeker( $user );
	}

	/**
	 * Format a role label.
	 *
	 * @param string $role Role key.
	 * @return string
	 */
	public static function role_label( $role ) {
		$roles = self::roles();
		return isset( $roles[ $role ] ) ? $roles[ $role ] : $role;
	}
}

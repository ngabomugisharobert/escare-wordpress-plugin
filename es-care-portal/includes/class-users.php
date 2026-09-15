<?php
/**
 * Custom portal users, meta, and sessions — not WordPress users.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Users {

	const ROLE_SEEKER   = 'job_seeker';
	const ROLE_EMPLOYER = 'employer';
	const ROLE_ADMIN    = 'admin';

	const STATUS_ACTIVE   = 'active';
	const STATUS_DISABLED = 'disabled';

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
				KEY meta_key (meta_key(191))
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
				KEY expires (expires)
			) {$charset};"
		);
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

		$args = wp_parse_args(
			$args,
			array(
				'role'     => '',
				'status'   => '',
				'search'   => '',
				'number'   => 100,
				'offset'   => 0,
				'orderby'  => 'created_at',
				'order'    => 'DESC',
			)
		);

		$where = array( '1=1' );
		$params = array();

		if ( $args['role'] && isset( self::roles()[ $args['role'] ] ) ) {
			$where[]  = 'role = %s';
			$params[] = $args['role'];
		}

		if ( $args['status'] ) {
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

		$orderby = in_array( $args['orderby'], array( 'created_at', 'email', 'role', 'last_name' ), true ) ? $args['orderby'] : 'created_at';
		$order   = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$limit   = max( 1, absint( $args['number'] ) );
		$offset  = max( 0, absint( $args['offset'] ) );

		$sql = 'SELECT * FROM ' . self::table() . ' WHERE ' . implode( ' AND ', $where ) . " ORDER BY {$orderby} {$order} LIMIT {$offset}, {$limit}";

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

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
	 * @param array $data User data.
	 * @return int|WP_Error
	 */
	public static function create( $data ) {
		global $wpdb;

		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$role  = isset( $data['role'] ) ? sanitize_key( $data['role'] ) : self::ROLE_SEEKER;

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'esc_email', __( 'Please enter a valid email address.', 'es-care-portal' ) );
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
				'password'     => isset( $data['password'] ) ? wp_hash_password( $data['password'] ) : wp_hash_password( wp_generate_password( 20, true, true ) ),
				'first_name'   => isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : '',
				'last_name'    => isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '',
				'phone'        => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
				'role'         => $role,
				'company_name' => isset( $data['company_name'] ) ? sanitize_text_field( $data['company_name'] ) : '',
				'status'       => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : self::STATUS_ACTIVE,
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
			} elseif ( in_array( $key, array( 'role', 'status' ), true ) ) {
				$value = sanitize_key( $value );
			} elseif ( in_array( $key, array( 'reset_expires', 'last_login' ), true ) ) {
				$value = $value ? $value : null;
			} else {
				$value = sanitize_text_field( (string) $value );
			}

			$set[ $key ] = $value;
			$fmt[]       = $placeholder;
		}

		if ( ! empty( $data['password'] ) ) {
			$set['password'] = wp_hash_password( $data['password'] );
			$fmt[]           = '%s';
		}

		return false !== $wpdb->update( self::table(), $set, array( 'id' => $id ), $fmt, array( '%d' ) );
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

		return $user;
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

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT umeta_id FROM ' . self::meta_table() . ' WHERE user_id = %d AND meta_key = %s LIMIT 1', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$user_id,
				$key
			)
		);

		if ( $exists ) {
			$wpdb->update(
				self::meta_table(),
				array( 'meta_value' => $stored ),
				array(
					'user_id'  => $user_id,
					'meta_key' => $key,
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
			return;
		}

		$wpdb->insert(
			self::meta_table(),
			array(
				'user_id'    => $user_id,
				'meta_key'   => $key,
				'meta_value' => $stored,
			),
			array( '%d', '%s', '%s' )
		);
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

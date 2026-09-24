<?php
/**
 * Pre-hire assessments and attempts.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_Assessments {

	/**
	 * Create tables and seed the default test.
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset     = $wpdb->get_charset_collate();
		$assessments = $wpdb->prefix . 'esc_assessments';
		$questions   = $wpdb->prefix . 'esc_assessment_questions';
		$attempts    = $wpdb->prefix . 'esc_assessment_attempts';

		dbDelta(
			"CREATE TABLE {$assessments} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title varchar(190) NOT NULL,
				description text,
				pass_score tinyint(3) unsigned NOT NULL DEFAULT 70,
				status varchar(20) NOT NULL DEFAULT 'active',
				PRIMARY KEY  (id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$questions} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				assessment_id bigint(20) unsigned NOT NULL,
				question text NOT NULL,
				choices longtext NOT NULL,
				correct tinyint(3) unsigned NOT NULL DEFAULT 0,
				sort tinyint(3) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				KEY assessment_id (assessment_id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$attempts} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				assessment_id bigint(20) unsigned NOT NULL,
				score tinyint(3) unsigned NOT NULL DEFAULT 0,
				passed tinyint(1) unsigned NOT NULL DEFAULT 0,
				answers longtext,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY assessment_id (assessment_id),
				KEY user_assessment_id (user_id, assessment_id, id)
			) {$charset};"
		);

		self::seed();
	}

	/**
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_assessments';
	}

	/**
	 * @return string
	 */
	public static function questions_table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_assessment_questions';
	}

	/**
	 * @return string
	 */
	public static function attempts_table() {
		global $wpdb;
		return $wpdb->prefix . 'esc_assessment_attempts';
	}

	/**
	 * Seed the default pre-hire test once.
	 */
	public static function seed() {
		global $wpdb;

		$exists = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $exists ) {
			return;
		}

		$wpdb->insert(
			self::table(),
			array(
				'title'       => __( 'Pre-Hire Assessment Test', 'es-care-portal' ),
				'description' => __( 'A short care-readiness quiz covering safety, privacy, and professional conduct.', 'es-care-portal' ),
				'pass_score'  => 70,
				'status'      => 'active',
			),
			array( '%s', '%s', '%d', '%s' )
		);

		$assessment_id = (int) $wpdb->insert_id;
		$items         = self::default_questions();

		foreach ( $items as $i => $item ) {
			$wpdb->insert(
				self::questions_table(),
				array(
					'assessment_id' => $assessment_id,
					'question'      => $item['question'],
					'choices'       => wp_json_encode( $item['choices'] ),
					'correct'       => $item['correct'],
					'sort'          => $i + 1,
				),
				array( '%d', '%s', '%s', '%d', '%d' )
			);
		}
	}

	/**
	 * @return array
	 */
	private static function default_questions() {
		return array(
			array(
				'question' => __( 'A client has fallen. What should you do first?', 'es-care-portal' ),
				'choices'  => array(
					__( 'Help them stand immediately', 'es-care-portal' ),
					__( 'Stay with them, check for injury, and report according to procedure', 'es-care-portal' ),
					__( 'Leave the room to find a family member', 'es-care-portal' ),
					__( 'Wait to mention it until the end of the shift', 'es-care-portal' ),
				),
				'correct'  => 1,
			),
			array(
				'question' => __( 'Client health information should be:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Shared on social media if names are removed', 'es-care-portal' ),
					__( 'Discussed only with authorized care team members', 'es-care-portal' ),
					__( 'Told to neighbors who ask how they are doing', 'es-care-portal' ),
					__( 'Saved in a personal notebook you take home', 'es-care-portal' ),
				),
				'correct'  => 1,
			),
			array(
				'question' => __( 'The most important way to prevent spreading infection is:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Wearing perfume', 'es-care-portal' ),
					__( 'Skipping gloves to save supplies', 'es-care-portal' ),
					__( 'Proper hand hygiene before and after care', 'es-care-portal' ),
					__( 'Opening windows only', 'es-care-portal' ),
				),
				'correct'  => 2,
			),
			array(
				'question' => __( 'If you suspect a client is being abused, you should:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Ignore it if the client seems embarrassed', 'es-care-portal' ),
					__( 'Confront the family at the bedside', 'es-care-portal' ),
					__( 'Report it immediately through the required channels', 'es-care-portal' ),
					__( 'Wait a week to see if it happens again', 'es-care-portal' ),
				),
				'correct'  => 2,
			),
			array(
				'question' => __( 'Professional boundaries mean you should not:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Be kind and respectful', 'es-care-portal' ),
					__( 'Accept large gifts, lend money, or share personal financial details', 'es-care-portal' ),
					__( 'Listen when a client wants to talk', 'es-care-portal' ),
					__( 'Follow the care plan', 'es-care-portal' ),
				),
				'correct'  => 1,
			),
			array(
				'question' => __( 'During personal care you should:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Rush to finish as quickly as possible', 'es-care-portal' ),
					__( 'Keep the client covered, explain each step, and protect their dignity', 'es-care-portal' ),
					__( 'Invite other household members to watch', 'es-care-portal' ),
					__( 'Skip documentation', 'es-care-portal' ),
				),
				'correct'  => 1,
			),
			array(
				'question' => __( 'If a client is unresponsive and not breathing normally, you should:', 'es-care-portal' ),
				'choices'  => array(
					__( 'Call emergency services and begin trained emergency response', 'es-care-portal' ),
					__( 'Wait for the next scheduled nurse visit', 'es-care-portal' ),
					__( 'Give them water', 'es-care-portal' ),
					__( 'Move them to another room first', 'es-care-portal' ),
				),
				'correct'  => 0,
			),
			array(
				'question' => __( 'Accurate documentation is important because:', 'es-care-portal' ),
				'choices'  => array(
					__( 'It is optional if the shift was quiet', 'es-care-portal' ),
					__( 'It supports safe care, communication, and legal records', 'es-care-portal' ),
					__( 'Only supervisors read notes', 'es-care-portal' ),
					__( 'You can fill it in from memory next week', 'es-care-portal' ),
				),
				'correct'  => 1,
			),
		);
	}

	/**
	 * @return object[]
	 */
	public static function all() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return object[]
	 */
	public static function all_active() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::table() . " WHERE status = 'active' ORDER BY title ASC, id ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Question count keyed by assessment ID.
	 *
	 * @return array<int,int>
	 */
	public static function question_counts() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT assessment_id, COUNT(*) AS total FROM ' . self::questions_table() . ' GROUP BY assessment_id' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$map  = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$map[ (int) $row->assessment_id ] = (int) $row->total;
			}
		}

		return $map;
	}

	/**
	 * Assessment IDs currently assigned to at least one job.
	 *
	 * @return int[]
	 */
	public static function assigned_ids() {
		$ids = get_posts(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'posts_per_page' => 500,
				'fields'         => 'ids',
				'meta_key'       => '_esc_assessment_id',
				'meta_compare'   => '>',
				'meta_value'     => 0,
			)
		);

		$assigned = array();

		foreach ( (array) $ids as $job_id ) {
			$aid = absint( get_post_meta( $job_id, '_esc_assessment_id', true ) );
			if ( $aid ) {
				$assigned[ $aid ] = $aid;
			}
		}

		return array_values( $assigned );
	}

	/**
	 * Active assessments a seeker should see: those assigned to their applications,
	 * plus any active assessment not assigned to any job (general pool).
	 *
	 * @param int $user_id Seeker user ID.
	 * @return object[]
	 */
	public static function for_seeker( $user_id ) {
		$user_id     = absint( $user_id );
		$active      = self::all_active();
		$assigned    = self::assigned_ids();
		$assigned_map = array_fill_keys( $assigned, true );
		$needed      = array();

		if ( $user_id ) {
			$apps = ESC_Portal_CPT_Application::for_user( $user_id );

			foreach ( $apps as $app ) {
				$status = (string) get_post_meta( $app->ID, '_esc_status', true );
				if ( 'withdrawn' === $status ) {
					continue;
				}

				$job_id = absint( get_post_meta( $app->ID, '_esc_job_id', true ) );
				$aid    = $job_id ? absint( get_post_meta( $job_id, '_esc_assessment_id', true ) ) : 0;

				if ( $aid ) {
					$needed[ $aid ] = $aid;
				}
			}
		}

		$out = array();

		foreach ( $active as $row ) {
			$id = (int) $row->id;
			$is_general = empty( $assigned_map[ $id ] );
			$is_needed  = ! empty( $needed[ $id ] );

			if ( $is_general || $is_needed ) {
				$row->for_jobs = array();
				$out[ $id ]    = $row;
			}
		}

		if ( $needed ) {
			foreach ( $needed as $aid ) {
				if ( isset( $out[ $aid ] ) ) {
					continue;
				}
				$row = self::get( $aid );
				if ( $row && 'active' === $row->status ) {
					$row->for_jobs = array();
					$out[ $aid ]   = $row;
				}
			}

			// Attach job titles for context on the seeker list.
			if ( $user_id ) {
				$apps = ESC_Portal_CPT_Application::for_user( $user_id );
				foreach ( $apps as $app ) {
					if ( 'withdrawn' === (string) get_post_meta( $app->ID, '_esc_status', true ) ) {
						continue;
					}
					$job_id = absint( get_post_meta( $app->ID, '_esc_job_id', true ) );
					$aid    = $job_id ? absint( get_post_meta( $job_id, '_esc_assessment_id', true ) ) : 0;
					if ( $aid && isset( $out[ $aid ] ) ) {
						$title = get_the_title( $job_id );
						if ( $title && ! in_array( $title, $out[ $aid ]->for_jobs, true ) ) {
							$out[ $aid ]->for_jobs[] = $title;
						}
					}
				}
			}
		}

		return array_values( $out );
	}

	/**
	 * @param int $id Assessment ID.
	 * @return object|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', absint( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $row ? $row : null;
	}

	/**
	 * Create or update an assessment and replace its questions.
	 *
	 * @param array $data {
	 *     @type int    $id
	 *     @type string $title
	 *     @type string $description
	 *     @type int    $pass_score
	 *     @type string $status
	 *     @type array  $questions List of {question, choices[4], correct}.
	 * }
	 * @return int|WP_Error Assessment ID.
	 */
	public static function save( $data ) {
		global $wpdb;

		$id          = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
		$title       = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';
		$pass_score  = isset( $data['pass_score'] ) ? absint( $data['pass_score'] ) : 70;
		$status      = isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'active';
		$questions   = isset( $data['questions'] ) && is_array( $data['questions'] ) ? $data['questions'] : array();

		if ( ! $title ) {
			return new WP_Error( 'esc_assessment_title', __( 'Please enter an assessment title.', 'es-care-portal' ) );
		}

		$pass_score = max( 1, min( 100, $pass_score ) );
		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

		$clean_questions = array();

		foreach ( $questions as $item ) {
			$question = isset( $item['question'] ) ? sanitize_text_field( $item['question'] ) : '';
			$choices  = isset( $item['choices'] ) && is_array( $item['choices'] ) ? array_values( $item['choices'] ) : array();
			$correct  = isset( $item['correct'] ) ? absint( $item['correct'] ) : 0;

			$choices = array_map( 'sanitize_text_field', $choices );
			$choices = array_pad( array_slice( $choices, 0, 4 ), 4, '' );

			if ( ! $question ) {
				continue;
			}

			$filled = array_filter( $choices );
			if ( count( $filled ) < 2 ) {
				continue;
			}

			if ( $correct > 3 ) {
				$correct = 0;
			}

			if ( '' === $choices[ $correct ] ) {
				$correct = 0;
			}

			$clean_questions[] = array(
				'question' => $question,
				'choices'  => $choices,
				'correct'  => $correct,
			);
		}

		if ( empty( $clean_questions ) ) {
			return new WP_Error( 'esc_assessment_questions', __( 'Add at least one question with two or more answer choices.', 'es-care-portal' ) );
		}

		$payload = array(
			'title'       => $title,
			'description' => $description,
			'pass_score'  => $pass_score,
			'status'      => $status,
		);
		$format  = array( '%s', '%s', '%d', '%s' );

		if ( $id ) {
			$existing = self::get( $id );
			if ( ! $existing ) {
				return new WP_Error( 'esc_assessment_missing', __( 'That assessment could not be found.', 'es-care-portal' ) );
			}

			$wpdb->update( self::table(), $payload, array( 'id' => $id ), $format, array( '%d' ) );
		} else {
			$wpdb->insert( self::table(), $payload, $format );
			$id = (int) $wpdb->insert_id;

			if ( ! $id ) {
				return new WP_Error( 'esc_assessment_save', __( 'The assessment could not be saved.', 'es-care-portal' ) );
			}
		}

		$wpdb->delete( self::questions_table(), array( 'assessment_id' => $id ), array( '%d' ) );

		foreach ( $clean_questions as $i => $item ) {
			$wpdb->insert(
				self::questions_table(),
				array(
					'assessment_id' => $id,
					'question'      => $item['question'],
					'choices'       => wp_json_encode( $item['choices'] ),
					'correct'       => $item['correct'],
					'sort'          => $i + 1,
				),
				array( '%d', '%s', '%s', '%d', '%d' )
			);
		}

		return $id;
	}

	/**
	 * @param int    $id     Assessment ID.
	 * @param string $status active|inactive.
	 * @return bool
	 */
	public static function set_status( $id, $status ) {
		global $wpdb;

		$id     = absint( $id );
		$status = sanitize_key( $status );

		if ( ! $id || ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			return false;
		}

		return false !== $wpdb->update(
			self::table(),
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete an assessment and its questions. Attempts are kept for history but orphaned titles fall back.
	 *
	 * @param int $id Assessment ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id || ! self::get( $id ) ) {
			return false;
		}

		$wpdb->delete( self::questions_table(), array( 'assessment_id' => $id ), array( '%d' ) );
		$deleted = $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );

		// Clear job assignments pointing at this assessment.
		$jobs = get_posts(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'posts_per_page' => 500,
				'fields'         => 'ids',
				'meta_key'       => '_esc_assessment_id',
				'meta_value'     => $id,
			)
		);

		foreach ( (array) $jobs as $job_id ) {
			delete_post_meta( $job_id, '_esc_assessment_id' );
		}

		return (bool) $deleted;
	}

	/**
	 * Portal admin: save assessment form.
	 */
	public static function handle_admin_save() {
		$dash = ESC_Portal_Helpers::dashboard_url( 'assessments' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_assessment_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_assessment_admin_nonce'] ) ), 'esc_admin_save_assessment' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'nonce', 'error' );
		}

		$id = isset( $_POST['esc_assessment_id'] ) ? absint( $_POST['esc_assessment_id'] ) : 0;

		$raw_questions = isset( $_POST['esc_q'] ) && is_array( $_POST['esc_q'] ) ? wp_unslash( $_POST['esc_q'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$questions     = array();

		foreach ( $raw_questions as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$questions[] = array(
				'question' => isset( $row['question'] ) ? $row['question'] : '',
				'choices'  => isset( $row['choices'] ) && is_array( $row['choices'] ) ? $row['choices'] : array(),
				'correct'  => isset( $row['correct'] ) ? $row['correct'] : 0,
			);
		}

		$result = self::save(
			array(
				'id'          => $id,
				'title'       => isset( $_POST['esc_assessment_title'] ) ? wp_unslash( $_POST['esc_assessment_title'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'description' => isset( $_POST['esc_assessment_description'] ) ? wp_unslash( $_POST['esc_assessment_description'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'pass_score'  => isset( $_POST['esc_pass_score'] ) ? absint( $_POST['esc_pass_score'] ) : 70,
				'status'      => isset( $_POST['esc_assessment_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_assessment_status'] ) ) : 'active',
				'questions'   => $questions,
			)
		);

		if ( is_wp_error( $result ) ) {
			$code = 'esc_assessment_questions' === $result->get_error_code() || 'esc_assessment_title' === $result->get_error_code() ? 'required' : 'save-failed';
			$back = $id ? ESC_Portal_Helpers::dashboard_url( 'assessment', array( 'assessment' => $id ) ) : ESC_Portal_Helpers::dashboard_url( 'assessment' );
			ESC_Portal_Helpers::redirect_notice( $back, $code, 'error' );
		}

		ESC_Portal_Helpers::redirect_notice(
			ESC_Portal_Helpers::dashboard_url( 'assessments' ),
			$id ? 'assessment-updated' : 'assessment-saved',
			'success'
		);
	}

	/**
	 * Portal admin: toggle status or delete.
	 */
	public static function handle_admin_action() {
		$dash = ESC_Portal_Helpers::dashboard_url( 'assessments' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ! ESC_Portal_Users::is_admin() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_assessment_action_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_assessment_action_nonce'] ) ), 'esc_admin_assessment_action' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'nonce', 'error' );
		}

		$id     = isset( $_POST['esc_assessment_id'] ) ? absint( $_POST['esc_assessment_id'] ) : 0;
		$action = isset( $_POST['esc_assessment_action'] ) ? sanitize_key( wp_unslash( $_POST['esc_assessment_action'] ) ) : '';

		if ( ! $id || ! self::get( $id ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'not-allowed', 'error' );
		}

		if ( 'activate' === $action ) {
			self::set_status( $id, 'active' );
			ESC_Portal_Helpers::redirect_notice( $dash, 'assessment-updated', 'success' );
		}

		if ( 'deactivate' === $action ) {
			self::set_status( $id, 'inactive' );
			ESC_Portal_Helpers::redirect_notice( $dash, 'assessment-updated', 'success' );
		}

		if ( 'delete' === $action ) {
			self::delete( $id );
			ESC_Portal_Helpers::redirect_notice( $dash, 'assessment-deleted', 'success' );
		}

		ESC_Portal_Helpers::redirect_notice( $dash, 'not-allowed', 'error' );
	}

	/**
	 * @param int $assessment_id Assessment ID.
	 * @return object[]
	 */
	public static function questions( $assessment_id ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::questions_table() . ' WHERE assessment_id = %d ORDER BY sort ASC, id ASC', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $assessment_id )
			)
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $row ) {
			$decoded      = json_decode( (string) $row->choices, true );
			$row->choices = is_array( $decoded ) ? $decoded : array();
		}

		return $rows;
	}

	/**
	 * @param int $user_id User ID.
	 * @return object[]
	 */
	public static function attempts_for_user( $user_id ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT a.*, t.title FROM ' . self::attempts_table() . ' a INNER JOIN ' . self::table() . ' t ON t.id = a.assessment_id WHERE a.user_id = %d ORDER BY a.created_at DESC', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $user_id )
			)
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Recent attempts for staff review.
	 *
	 * @return object[]
	 */
	public static function all_attempts() {
		global $wpdb;

		$rows = $wpdb->get_results(
			'SELECT a.*, t.title, u.first_name, u.last_name, u.email FROM ' . self::attempts_table() . ' a INNER JOIN ' . self::table() . ' t ON t.id = a.assessment_id LEFT JOIN ' . ESC_Portal_Users::table() . ' u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 200' // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Latest attempt for a user + assessment.
	 *
	 * @param int $user_id        User ID.
	 * @param int $assessment_id  Assessment ID.
	 * @return object|null
	 */
	public static function latest_attempt( $user_id, $assessment_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::attempts_table() . ' WHERE user_id = %d AND assessment_id = %d ORDER BY id DESC LIMIT 1', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $user_id ),
				absint( $assessment_id )
			)
		);
	}

	/**
	 * Grade and store an attempt.
	 *
	 * @param int   $user_id        User ID.
	 * @param int   $assessment_id  Assessment ID.
	 * @param array $posted         Posted answers keyed by question id.
	 * @return object|WP_Error
	 */
	public static function submit( $user_id, $assessment_id, $posted ) {
		global $wpdb;

		$assessment = self::get( $assessment_id );
		$questions  = self::questions( $assessment_id );

		if ( ! $assessment || ! $questions ) {
			return new WP_Error( 'esc_assessment', __( 'That assessment could not be found.', 'es-care-portal' ) );
		}

		$correct = 0;
		$answers = array();

		foreach ( $questions as $question ) {
			$qid    = (int) $question->id;
			$choice = isset( $posted[ $qid ] ) ? absint( $posted[ $qid ] ) : -1;
			$ok     = ( $choice === (int) $question->correct );
			$answers[ $qid ] = $choice;

			if ( $ok ) {
				$correct++;
			}
		}

		$total = count( $questions );
		$score = $total ? (int) round( ( $correct / $total ) * 100 ) : 0;
		$pass  = $score >= (int) $assessment->pass_score ? 1 : 0;

		$wpdb->insert(
			self::attempts_table(),
			array(
				'user_id'        => absint( $user_id ),
				'assessment_id'  => absint( $assessment_id ),
				'score'          => $score,
				'passed'         => $pass,
				'answers'        => wp_json_encode( $answers ),
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s' )
		);

		if ( ! $wpdb->insert_id ) {
			return new WP_Error( 'esc_assessment_save', __( 'The assessment could not be saved.', 'es-care-portal' ) );
		}

		return (object) array(
			'id'     => (int) $wpdb->insert_id,
			'score'  => $score,
			'passed' => (bool) $pass,
		);
	}

	/**
	 * Handle frontend submission.
	 */
	public static function handle_submit() {
		$dash = ESC_Portal_Helpers::dashboard_url( 'assessments' );

		if ( ! ESC_Portal_Auth::is_logged_in() || ! ESC_Portal_Users::is_seeker() ) {
			ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::get_page_url( 'login' ), 'login-required', 'error' );
		}

		if ( ! isset( $_POST['esc_assessment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_assessment_nonce'] ) ), 'esc_submit_assessment' ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'nonce', 'error' );
		}

		if ( ! ESC_Portal_Rate_Limit::allow( 'assessment', (string) ESC_Portal_Auth::current_user_id() ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'rate-limited', 'error' );
		}

		$assessment_id = isset( $_POST['esc_assessment_id'] ) ? absint( $_POST['esc_assessment_id'] ) : 0;
		$posted        = isset( $_POST['esc_answer'] ) && is_array( $_POST['esc_answer'] ) ? wp_unslash( $_POST['esc_answer'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$result = self::submit( ESC_Portal_Auth::current_user_id(), $assessment_id, $posted );

		if ( is_wp_error( $result ) ) {
			ESC_Portal_Helpers::redirect_notice( $dash, 'not-allowed', 'error' );
		}

		$user       = ESC_Portal_Auth::current_user();
		$assessment = self::get( $assessment_id );
		ESC_Portal_Emails::assessment_received( $user, $assessment, $result );

		ESC_Portal_Helpers::redirect_notice( ESC_Portal_Helpers::dashboard_url( 'results' ), $result->passed ? 'assessment-passed' : 'assessment-failed', $result->passed ? 'success' : 'info' );
	}
}

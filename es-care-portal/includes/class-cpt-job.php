<?php
/**
 * Job custom post type, taxonomy, and admin meta.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

class ESC_Portal_CPT_Job {

	/**
	 * Register the job post type.
	 */
	public static function register() {
		$labels = array(
			'name'               => __( 'Jobs', 'es-care-portal' ),
			'singular_name'      => __( 'Job', 'es-care-portal' ),
			'add_new'            => __( 'Add Job', 'es-care-portal' ),
			'add_new_item'       => __( 'Add New Job', 'es-care-portal' ),
			'edit_item'          => __( 'Edit Job', 'es-care-portal' ),
			'new_item'           => __( 'New Job', 'es-care-portal' ),
			'view_item'          => __( 'View Job', 'es-care-portal' ),
			'search_items'       => __( 'Search Jobs', 'es-care-portal' ),
			'not_found'          => __( 'No jobs found.', 'es-care-portal' ),
			'not_found_in_trash' => __( 'No jobs found in Trash.', 'es-care-portal' ),
			'all_items'          => __( 'Jobs', 'es-care-portal' ),
			'menu_name'          => __( 'Jobs', 'es-care-portal' ),
		);

		register_post_type(
			'esc_job',
			array(
				'labels'              => $labels,
				'public'              => true,
				'has_archive'         => 'jobs',
				'rewrite'             => array(
					'slug'       => 'job',
					'with_front' => false,
				),
				'supports'            => array( 'title', 'editor', 'excerpt' ),
				'menu_icon'           => 'dashicons-id-alt',
				'show_in_rest'        => false,
				'show_in_menu'        => false,
				'capability_type'     => array( 'esc_job', 'esc_jobs' ),
				'map_meta_cap'        => true,
				'exclude_from_search' => false,
			)
		);
	}

	/**
	 * Register job category taxonomy.
	 */
	public static function register_taxonomy() {
		register_taxonomy(
			'esc_job_category',
			'esc_job',
			array(
				'labels'            => array(
					'name'          => __( 'Job Categories', 'es-care-portal' ),
					'singular_name' => __( 'Job Category', 'es-care-portal' ),
					'search_items'  => __( 'Search Job Categories', 'es-care-portal' ),
					'all_items'     => __( 'All Job Categories', 'es-care-portal' ),
					'edit_item'     => __( 'Edit Job Category', 'es-care-portal' ),
					'update_item'   => __( 'Update Job Category', 'es-care-portal' ),
					'add_new_item'  => __( 'Add New Job Category', 'es-care-portal' ),
					'new_item_name' => __( 'New Job Category Name', 'es-care-portal' ),
					'menu_name'     => __( 'Categories', 'es-care-portal' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'rewrite'           => array(
					'slug'       => 'job-category',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Admin columns, meta boxes, templates.
	 */
	public static function init_admin() {
		add_action( 'add_meta_boxes_esc_job', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_esc_job', array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'manage_esc_job_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_esc_job_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'single_template', array( __CLASS__, 'single_template' ) );
		add_filter( 'archive_template', array( __CLASS__, 'archive_template' ) );
		add_filter( 'taxonomy_template', array( __CLASS__, 'archive_template' ) );
		add_action( 'wp_after_insert_post', array( __CLASS__, 'maybe_default_status' ), 10, 3 );
	}

	/**
	 * Job details meta box.
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'esc_job_details',
			__( 'Job Details', 'es-care-portal' ),
			array( __CLASS__, 'render_meta_box' ),
			'esc_job',
			'normal',
			'high'
		);
	}

	/**
	 * @param WP_Post $post Post.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'esc_save_job', 'esc_job_nonce' );

		$location   = get_post_meta( $post->ID, '_esc_location', true );
		$type       = get_post_meta( $post->ID, '_esc_employment_type', true );
		$shift      = get_post_meta( $post->ID, '_esc_shift', true );
		$pay        = get_post_meta( $post->ID, '_esc_pay_range', true );
		$closing    = get_post_meta( $post->ID, '_esc_closing_date', true );
		$status     = get_post_meta( $post->ID, '_esc_job_status', true );
		$assessment = absint( get_post_meta( $post->ID, '_esc_assessment_id', true ) );

		if ( ! $status ) {
			$status = 'open';
		}

		$types       = ESC_Portal_Helpers::employment_types();
		$statuses    = ESC_Portal_Helpers::job_statuses();
		$assessments = ESC_Portal_Assessments::all_active();
		?>
		<div class="esc-job-meta">
			<p>
				<label for="esc_location"><?php esc_html_e( 'Location', 'es-care-portal' ); ?></label>
				<input type="text" class="widefat" id="esc_location" name="esc_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'City, State or Remote', 'es-care-portal' ); ?>">
			</p>
			<p>
				<label for="esc_employment_type"><?php esc_html_e( 'Employment type', 'es-care-portal' ); ?></label>
				<select id="esc_employment_type" name="esc_employment_type" class="widefat">
					<option value=""><?php esc_html_e( 'Select type', 'es-care-portal' ); ?></option>
					<?php foreach ( $types as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="esc_shift"><?php esc_html_e( 'Shift', 'es-care-portal' ); ?></label>
				<input type="text" class="widefat" id="esc_shift" name="esc_shift" value="<?php echo esc_attr( $shift ); ?>" placeholder="<?php esc_attr_e( 'Days, nights, rotating…', 'es-care-portal' ); ?>">
			</p>
			<p>
				<label for="esc_pay_range"><?php esc_html_e( 'Pay range', 'es-care-portal' ); ?></label>
				<input type="text" class="widefat" id="esc_pay_range" name="esc_pay_range" value="<?php echo esc_attr( $pay ); ?>" placeholder="<?php esc_attr_e( 'e.g. $18–$22 / hour', 'es-care-portal' ); ?>">
			</p>
			<p>
				<label for="esc_closing_date"><?php esc_html_e( 'Closing date', 'es-care-portal' ); ?></label>
				<input type="date" id="esc_closing_date" name="esc_closing_date" value="<?php echo esc_attr( $closing ); ?>">
			</p>
			<p>
				<label for="esc_job_status"><?php esc_html_e( 'Listing status', 'es-care-portal' ); ?></label>
				<select id="esc_job_status" name="esc_job_status">
					<?php foreach ( $statuses as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="esc_assessment_id"><?php esc_html_e( 'Required assessment', 'es-care-portal' ); ?></label>
				<select id="esc_assessment_id" name="esc_assessment_id" class="widefat">
					<option value="0"><?php esc_html_e( 'None', 'es-care-portal' ); ?></option>
					<?php foreach ( $assessments as $a ) : ?>
						<option value="<?php echo esc_attr( (string) $a->id ); ?>" <?php selected( $assessment, (int) $a->id ); ?>><?php echo esc_html( $a->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST['esc_job_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_job_nonce'] ) ), 'esc_save_job' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_esc_job', $post_id ) ) {
			return;
		}

		$location = isset( $_POST['esc_location'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_location'] ) ) : '';
		$shift    = isset( $_POST['esc_shift'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_shift'] ) ) : '';
		$pay      = isset( $_POST['esc_pay_range'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_pay_range'] ) ) : '';
		$closing  = isset( $_POST['esc_closing_date'] ) ? sanitize_text_field( wp_unslash( $_POST['esc_closing_date'] ) ) : '';
		$type     = isset( $_POST['esc_employment_type'] ) ? sanitize_key( wp_unslash( $_POST['esc_employment_type'] ) ) : '';
		$status   = isset( $_POST['esc_job_status'] ) ? sanitize_key( wp_unslash( $_POST['esc_job_status'] ) ) : 'open';
		$assess   = isset( $_POST['esc_assessment_id'] ) ? absint( wp_unslash( $_POST['esc_assessment_id'] ) ) : 0;

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

		if ( $assess && ! ESC_Portal_Assessments::get( $assess ) ) {
			$assess = 0;
		}

		update_post_meta( $post_id, '_esc_location', $location );
		update_post_meta( $post_id, '_esc_shift', $shift );
		update_post_meta( $post_id, '_esc_pay_range', $pay );
		update_post_meta( $post_id, '_esc_closing_date', $closing );
		update_post_meta( $post_id, '_esc_employment_type', $type );
		update_post_meta( $post_id, '_esc_job_status', $status );

		if ( $assess ) {
			update_post_meta( $post_id, '_esc_assessment_id', $assess );
		} else {
			delete_post_meta( $post_id, '_esc_assessment_id' );
		}

		wp_cache_delete( 'esc_job_locations', 'esc_portal' );
	}

	/**
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['esc_location'] = __( 'Location', 'es-care-portal' );
				$new['esc_type']     = __( 'Type', 'es-care-portal' );
				$new['esc_status']   = __( 'Status', 'es-care-portal' );
				$new['esc_apps']     = __( 'Applications', 'es-care-portal' );
			}
		}

		return $new;
	}

	/**
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'esc_location':
				echo esc_html( (string) get_post_meta( $post_id, '_esc_location', true ) );
				break;
			case 'esc_type':
				$type  = get_post_meta( $post_id, '_esc_employment_type', true );
				$types = ESC_Portal_Helpers::employment_types();
				echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : '' );
				break;
			case 'esc_status':
				$open = ESC_Portal_Helpers::is_job_open( $post_id );
				echo $open ? esc_html__( 'Open', 'es-care-portal' ) : esc_html__( 'Closed', 'es-care-portal' );
				break;
			case 'esc_apps':
				$count = self::application_count( $post_id );
				$url   = add_query_arg(
					array(
						'page' => 'esc-applications',
						'job'  => $post_id,
					),
					admin_url( 'admin.php' )
				);
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( (string) $count ) . '</a>';
				break;
		}
	}

	/**
	 * @param int $job_id Job ID.
	 * @return int
	 */
	public static function application_count( $job_id ) {
		$counts = self::application_counts( array( $job_id ) );
		return isset( $counts[ (int) $job_id ] ) ? $counts[ (int) $job_id ] : 0;
	}

	/**
	 * Batch application counts keyed by job ID.
	 *
	 * @param int[] $job_ids Job IDs.
	 * @return array<int,int>
	 */
	public static function application_counts( $job_ids ) {
		global $wpdb;

		$job_ids = array_values( array_filter( array_map( 'absint', (array) $job_ids ) ) );
		$out     = array();

		foreach ( $job_ids as $id ) {
			$out[ $id ] = 0;
		}

		if ( ! $job_ids ) {
			return $out;
		}

		$placeholders = implode( ',', array_fill( 0, count( $job_ids ), '%d' ) );
		$sql          = "SELECT pm.meta_value AS job_id, COUNT(p.ID) AS total
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '_esc_job_id'
			AND p.post_type = 'esc_application'
			AND p.post_status = 'publish'
			AND pm.meta_value IN ({$placeholders})
			GROUP BY pm.meta_value";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $job_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$out[ (int) $row->job_id ] = (int) $row->total;
			}
		}

		return $out;
	}

	/**
	 * @param string $template Template path.
	 * @return string
	 */
	public static function single_template( $template ) {
		if ( is_singular( 'esc_job' ) ) {
			$plugin = ESC_PORTAL_DIR . 'public/templates/single-job.php';

			if ( file_exists( $plugin ) ) {
				return $plugin;
			}
		}

		return $template;
	}

	/**
	 * @param string $template Template path.
	 * @return string
	 */
	public static function archive_template( $template ) {
		if ( is_post_type_archive( 'esc_job' ) || is_tax( 'esc_job_category' ) ) {
			$plugin = ESC_PORTAL_DIR . 'public/templates/archive-job.php';

			if ( file_exists( $plugin ) ) {
				return $plugin;
			}
		}

		return $template;
	}

	/**
	 * Collect unique stored job locations for filters.
	 *
	 * @return string[]
	 */
	public static function distinct_locations() {
		$cached = wp_cache_get( 'esc_job_locations', 'esc_portal' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$results = $wpdb->get_col(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '_esc_location'
			AND pm.meta_value != ''
			AND p.post_type = 'esc_job'
			AND p.post_status = 'publish'
			ORDER BY meta_value ASC"
		);

		if ( ! is_array( $results ) ) {
			return array();
		}

		$results = array_values( array_unique( array_map( 'strval', $results ) ) );
		wp_cache_set( 'esc_job_locations', $results, 'esc_portal', HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Default new jobs to Open when no status was saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 * @param bool    $update  Whether this is an update.
	 */
	public static function maybe_default_status( $post_id, $post, $update ) {
		if ( $update || ! $post instanceof WP_Post || 'esc_job' !== $post->post_type ) {
			return;
		}

		if ( ! get_post_meta( $post_id, '_esc_job_status', true ) ) {
			update_post_meta( $post_id, '_esc_job_status', 'open' );
		}
	}
}

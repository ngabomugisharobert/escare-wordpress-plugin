<?php
/**
 * Applications list table for WP admin.
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ESC_Portal_Applications_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'application',
				'plural'   => 'applications',
				'ajax'     => false,
			)
		);
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return array(
			'applicant' => __( 'Applicant', 'es-care-portal' ),
			'job'       => __( 'Job', 'es-care-portal' ),
			'status'    => __( 'Status', 'es-care-portal' ),
			'date'      => __( 'Submitted', 'es-care-portal' ),
			'resume'    => __( 'Documents', 'es-care-portal' ),
		);
	}

	/**
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'date' => array( 'date', true ),
		);
	}

	/**
	 * Query applications.
	 */
	public function prepare_items() {
		$per_page = 20;
		$paged    = $this->get_pagenum();
		$search   = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$job_id   = isset( $_REQUEST['job'] ) ? absint( $_REQUEST['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status   = isset( $_REQUEST['esc_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['esc_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$meta_query = array();

		if ( $job_id ) {
			$meta_query[] = array(
				'key'   => '_esc_job_id',
				'value' => $job_id,
			);
		}

		if ( $status && isset( ESC_Portal_Helpers::application_statuses()[ $status ] ) ) {
			$meta_query[] = array(
				'key'   => '_esc_status',
				'value' => $status,
			);
		}

		$args = array(
			'post_type'      => 'esc_application',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'orderby'        => 'date',
			'order'          => ( isset( $_REQUEST['order'] ) && 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'ASC' : 'DESC', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);

		if ( $search ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_esc_email',
					'value'   => $search,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_esc_first_name',
					'value'   => $search,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_esc_last_name',
					'value'   => $search,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_esc_phone',
					'value'   => $search,
					'compare' => 'LIKE',
				),
			);
		}

		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}

		if ( $meta_query ) {
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = $query->posts;

		$this->set_pagination_args(
			array(
				'total_items' => (int) $query->found_posts,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Extra filters.
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$job_id = isset( $_REQUEST['job'] ) ? absint( $_REQUEST['job'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_REQUEST['esc_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['esc_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$jobs   = get_posts(
			array(
				'post_type'      => 'esc_job',
				'post_status'    => 'any',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="alignleft actions">
			<label class="screen-reader-text" for="filter-by-job"><?php esc_html_e( 'Filter by job', 'es-care-portal' ); ?></label>
			<select name="job" id="filter-by-job">
				<option value="0"><?php esc_html_e( 'All jobs', 'es-care-portal' ); ?></option>
				<?php foreach ( $jobs as $job ) : ?>
					<option value="<?php echo esc_attr( (string) $job->ID ); ?>" <?php selected( $job_id, $job->ID ); ?>><?php echo esc_html( get_the_title( $job ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<label class="screen-reader-text" for="filter-by-status"><?php esc_html_e( 'Filter by status', 'es-care-portal' ); ?></label>
			<select name="esc_status" id="filter-by-status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::application_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'Filter', 'es-care-portal' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}

	/**
	 * @param WP_Post $item Item.
	 * @return string
	 */
	protected function column_applicant( $item ) {
		$snap = ESC_Portal_CPT_Application::get_snapshot( $item->ID );
		$name = trim( $snap['first_name'] . ' ' . $snap['last_name'] );
		$url  = add_query_arg(
			array(
				'page' => 'esc-application',
				'id'   => $item->ID,
			),
			admin_url( 'admin.php' )
		);

		$actions = array(
			'view' => '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Review', 'es-care-portal' ) . '</a>',
		);

		return '<strong><a href="' . esc_url( $url ) . '">' . esc_html( $name ? $name : $item->post_title ) . '</a></strong>'
			. $this->row_actions( $actions )
			. '<div class="esc-muted">' . esc_html( $snap['email'] ) . '</div>';
	}

	/**
	 * @param WP_Post $item Item.
	 * @return string
	 */
	protected function column_job( $item ) {
		$job_id = (int) get_post_meta( $item->ID, '_esc_job_id', true );
		$title  = $job_id ? get_the_title( $job_id ) : '';

		return $title ? esc_html( $title ) : '&mdash;';
	}

	/**
	 * @param WP_Post $item Item.
	 * @return string
	 */
	protected function column_status( $item ) {
		$status = (string) get_post_meta( $item->ID, '_esc_status', true );
		$label  = ESC_Portal_Helpers::format_status( $status );

		return '<span class="esc-status esc-status--' . esc_attr( sanitize_html_class( $status ) ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * @param WP_Post $item Item.
	 * @return string
	 */
	protected function column_date( $item ) {
		return esc_html( get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item ) );
	}

	/**
	 * @param WP_Post $item Item.
	 * @return string
	 */
	protected function column_resume( $item ) {
		$links = array();

		foreach ( ESC_Portal_Uploads::document_types() as $doc_key => $doc ) {
			$file = get_post_meta( $item->ID, $doc['meta_file'], true );

			if ( ! $file ) {
				continue;
			}

			$links[] = '<a href="' . esc_url( ESC_Portal_Uploads::download_url( $item->ID, $doc_key ) ) . '">' . esc_html( $doc['label'] ) . '</a>';
		}

		return $links ? implode( '<br>', $links ) : '&mdash;';
	}

	/**
	 * @param WP_Post $item        Item.
	 * @param string  $column_name Column.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return '';
	}

	/**
	 * @return string
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'table-view-list', 'posts' );
	}

	/**
	 * Empty state.
	 */
	public function no_items() {
		esc_html_e( 'No applications found.', 'es-care-portal' );
	}
}

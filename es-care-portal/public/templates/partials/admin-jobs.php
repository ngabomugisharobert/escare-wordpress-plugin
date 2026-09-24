<?php
/**
 * Portal admin jobs table.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $jobs
 * @var int       $jobs_total
 * @var array     $table_req
 * @var array     $employers
 */

defined( 'ABSPATH' ) || exit;

$jobs       = is_array( $jobs ) ? $jobs : array();
$jobs_total = isset( $jobs_total ) ? (int) $jobs_total : count( $jobs );
$req        = isset( $table_req ) && is_array( $table_req ) ? $table_req : ESC_Portal_Helpers::table_request( array( 'date', 'title', 'status' ) );
$employers  = isset( $employers ) && is_array( $employers ) ? $employers : array();
$base       = ESC_Portal_Helpers::dashboard_url( 'jobs' );
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Jobs', 'es-care-portal' ); ?></h2>
	<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'post' ) ); ?>"><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'All job listings across employers. New employer listings wait here until they are approved.', 'es-care-portal' ); ?></p>

<div class="esc-card esc-data-panel">
	<form method="get" class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-jobs" action="<?php echo esc_url( $base ); ?>">
		<input type="hidden" name="esc_view" value="jobs">
		<input type="hidden" name="_esc_table" value="<?php echo esc_attr( wp_create_nonce( 'esc_portal_table' ) ); ?>">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search jobs', 'es-care-portal' ); ?></span>
			<input type="search" name="esc_q" class="esc-data-search-input" value="<?php echo esc_attr( $req['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search title, location, company…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter" for="esc-filter-job-status">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select id="esc-filter-job-status" name="esc_status" data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::job_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Filter', 'es-care-portal' ); ?></button>
		<p class="esc-data-count" aria-live="polite" data-esc-total="<?php echo esc_attr( (string) $jobs_total ); ?>"></p>
	</form>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-jobs">
			<thead>
				<tr>
					<?php echo ESC_Portal_Helpers::table_th( 'title', __( 'Title', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<th scope="col"><?php esc_html_e( 'Company', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Location', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Type', 'es-care-portal' ); ?></th>
					<?php echo ESC_Portal_Helpers::table_th( 'status', __( 'Status', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'date', __( 'Posted', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $jobs ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="7"><?php esc_html_e( 'No jobs match this search.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $jobs as $job ) : ?>
						<?php
						$status   = ESC_Portal_Helpers::listing_status( $job );
						$location = (string) get_post_meta( $job->ID, '_esc_location', true );
						$type     = (string) get_post_meta( $job->ID, '_esc_employment_type', true );
						$employer = absint( get_post_meta( $job->ID, '_esc_employer_id', true ) );
						$owner    = isset( $employers[ $employer ] ) ? $employers[ $employer ] : null;
						$company  = $owner && $owner->company_name ? $owner->company_name : ( $owner ? $owner->display_name : '' );
						$type_lbl = isset( ESC_Portal_Helpers::employment_types()[ $type ] ) ? ESC_Portal_Helpers::employment_types()[ $type ] : $type;
						?>
						<tr
							data-esc-title="<?php echo esc_attr( strtolower( get_the_title( $job ) ) ); ?>"
							data-esc-company="<?php echo esc_attr( strtolower( $company ) ); ?>"
							data-esc-location="<?php echo esc_attr( strtolower( $location ) ); ?>"
							data-esc-type="<?php echo esc_attr( strtolower( $type_lbl ) ); ?>"
							data-esc-status="<?php echo esc_attr( $status ); ?>"
							data-esc-date="<?php echo esc_attr( get_post_time( 'U', true, $job ) ); ?>"
							data-esc-search="<?php echo esc_attr( strtolower( get_the_title( $job ) . ' ' . $company . ' ' . $location . ' ' . $type_lbl ) ); ?>"
						>
							<td data-label="<?php esc_attr_e( 'Title', 'es-care-portal' ); ?>"><strong><?php echo esc_html( get_the_title( $job ) ); ?></strong></td>
							<td data-label="<?php esc_attr_e( 'Company', 'es-care-portal' ); ?>"><?php echo esc_html( $company ? $company : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Location', 'es-care-portal' ); ?>"><?php echo esc_html( $location ? $location : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Type', 'es-care-portal' ); ?>"><?php echo esc_html( $type_lbl ? $type_lbl : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'es-care-portal' ); ?>">
								<span class="esc-status esc-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ESC_Portal_Helpers::format_status( $status ) ); ?></span>
							</td>
							<td data-label="<?php esc_attr_e( 'Posted', 'es-care-portal' ); ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ), $job ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Actions', 'es-care-portal' ); ?>">
								<div class="esc-row-action-group">
									<a class="esc-button esc-button--small" href="<?php echo esc_url( ESC_Portal_Helpers::job_edit_url( $job->ID ) ); ?>"><?php esc_html_e( 'Edit', 'es-care-portal' ); ?></a>
									<a class="esc-button esc-button--ghost esc-button--small" href="<?php echo esc_url( get_permalink( $job ) ); ?>"><?php esc_html_e( 'View', 'es-care-portal' ); ?></a>
									<?php if ( 'pending' === $job->post_status ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<?php wp_nonce_field( 'esc_moderate_job_' . $job->ID, 'esc_moderate_job_nonce' ); ?>
											<input type="hidden" name="action" value="esc_moderate_job">
											<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job->ID ); ?>">
											<input type="hidden" name="esc_moderate" value="approve">
											<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Approve', 'es-care-portal' ); ?></button>
										</form>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<?php wp_nonce_field( 'esc_moderate_job_' . $job->ID, 'esc_moderate_job_nonce' ); ?>
											<input type="hidden" name="action" value="esc_moderate_job">
											<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job->ID ); ?>">
											<input type="hidden" name="esc_moderate" value="reject">
											<button type="submit" class="esc-button esc-button--danger esc-button--small"><?php esc_html_e( 'Reject', 'es-care-portal' ); ?></button>
										</form>
									<?php endif; ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Move this job and its public listing to Trash?', 'es-care-portal' ); ?>">
										<?php wp_nonce_field( 'esc_delete_job_front', 'esc_delete_job_nonce' ); ?>
										<input type="hidden" name="action" value="esc_delete_job_front">
										<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job->ID ); ?>">
										<button type="submit" class="esc-button esc-button--danger esc-button--small"><?php esc_html_e( 'Delete', 'es-care-portal' ); ?></button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php echo ESC_Portal_Helpers::pagination_html( $jobs_total, $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

<?php
/**
 * Portal admin applications table.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $applications
 * @var int       $apps_total
 * @var array     $table_req
 * @var array     $review
 */

defined( 'ABSPATH' ) || exit;

$applications = is_array( $applications ) ? $applications : array();
$apps_total   = isset( $apps_total ) ? (int) $apps_total : count( $applications );
$req          = isset( $table_req ) && is_array( $table_req ) ? $table_req : ESC_Portal_Helpers::table_request( array( 'date', 'status' ) );
$base         = ESC_Portal_Helpers::dashboard_url( 'applications' );
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Applications', 'es-care-portal' ); ?></h2>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'Review candidate applications. Search and pagination cover the complete result set.', 'es-care-portal' ); ?></p>

<?php if ( ! empty( $review ) ) : ?>
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/review-application.php'; ?>
<?php endif; ?>

<div class="esc-card esc-data-panel">
	<form method="get" class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-applications" action="<?php echo esc_url( $base ); ?>">
		<input type="hidden" name="esc_view" value="applications">
		<input type="hidden" name="_esc_table" value="<?php echo esc_attr( wp_create_nonce( 'esc_portal_table' ) ); ?>">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search applications', 'es-care-portal' ); ?></span>
			<input type="search" name="esc_q" class="esc-data-search-input" value="<?php echo esc_attr( $req['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search applicant or job…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter" for="esc-filter-app-status">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select id="esc-filter-app-status" name="esc_status" data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::application_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="submit" class="esc-button esc-button--small"><?php esc_html_e( 'Filter', 'es-care-portal' ); ?></button>
		<p class="esc-data-count" aria-live="polite" data-esc-total="<?php echo esc_attr( (string) $apps_total ); ?>"></p>
	</form>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-applications">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Applicant', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Job', 'es-care-portal' ); ?></th>
					<?php echo ESC_Portal_Helpers::table_th( 'status', __( 'Status', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo ESC_Portal_Helpers::table_th( 'date', __( 'Submitted', 'es-care-portal' ), $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $applications ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="6"><?php esc_html_e( 'No applications match this search.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $applications as $application ) : ?>
						<?php
						$snap      = ESC_Portal_CPT_Application::get_snapshot( $application->ID );
						$applicant = trim( $snap['first_name'] . ' ' . $snap['last_name'] );
						$email     = isset( $snap['email'] ) ? (string) $snap['email'] : '';
						$job_title = get_the_title( $snap['job_id'] );
						$status    = isset( $snap['status'] ) ? (string) $snap['status'] : 'pending';
						$review_url = add_query_arg(
							array(
								'esc_view'    => 'applications',
								'application' => $application->ID,
							),
							ESC_Portal_Helpers::get_page_url( 'dashboard' )
						);
						?>
						<tr
							data-esc-applicant="<?php echo esc_attr( strtolower( $applicant ) ); ?>"
							data-esc-email="<?php echo esc_attr( strtolower( $email ) ); ?>"
							data-esc-job="<?php echo esc_attr( strtolower( $job_title ) ); ?>"
							data-esc-status="<?php echo esc_attr( $status ); ?>"
							data-esc-date="<?php echo esc_attr( get_post_time( 'U', true, $application ) ); ?>"
							data-esc-search="<?php echo esc_attr( strtolower( $applicant . ' ' . $email . ' ' . $job_title ) ); ?>"
						>
							<td data-label="<?php esc_attr_e( 'Applicant', 'es-care-portal' ); ?>"><strong><?php echo esc_html( $applicant ? $applicant : '—' ); ?></strong></td>
							<td data-label="<?php esc_attr_e( 'Email', 'es-care-portal' ); ?>"><?php echo esc_html( $email ? $email : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Job', 'es-care-portal' ); ?>"><?php echo esc_html( $job_title ? $job_title : '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Status', 'es-care-portal' ); ?>">
								<span class="esc-status esc-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ESC_Portal_Helpers::format_status( $status ) ); ?></span>
							</td>
							<td data-label="<?php esc_attr_e( 'Submitted', 'es-care-portal' ); ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ), $application ) ); ?></td>
							<td data-label="<?php esc_attr_e( 'Actions', 'es-care-portal' ); ?>">
								<div class="esc-row-action-group">
									<a class="esc-button esc-button--small" href="<?php echo esc_url( $review_url ); ?>"><?php esc_html_e( 'Review', 'es-care-portal' ); ?></a>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Move this application to Trash?', 'es-care-portal' ); ?>">
										<?php wp_nonce_field( 'esc_admin_delete_application_' . $application->ID, 'esc_delete_application_nonce' ); ?>
										<input type="hidden" name="action" value="esc_admin_delete_application">
										<input type="hidden" name="esc_application_id" value="<?php echo esc_attr( (string) $application->ID ); ?>">
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
	<?php echo ESC_Portal_Helpers::pagination_html( $apps_total, $req, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

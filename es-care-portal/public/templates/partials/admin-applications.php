<?php
/**
 * Portal admin applications table.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $applications
 * @var array     $review
 */

defined( 'ABSPATH' ) || exit;

$applications = is_array( $applications ) ? $applications : array();
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Applications', 'es-care-portal' ); ?></h2>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'Review candidate applications. Search, filter by status, and sort any column.', 'es-care-portal' ); ?></p>

<?php if ( ! empty( $review ) ) : ?>
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/review-application.php'; ?>
<?php endif; ?>

<div class="esc-card esc-data-panel">
	<div class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-applications">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search applications', 'es-care-portal' ); ?></span>
			<input type="search" class="esc-data-search-input" placeholder="<?php esc_attr_e( 'Search applicant or job…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::application_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<p class="esc-data-count" aria-live="polite"></p>
	</div>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-applications">
			<thead>
				<tr>
					<th><button type="button" class="esc-sort" data-esc-sort="applicant"><?php esc_html_e( 'Applicant', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="email"><?php esc_html_e( 'Email', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="job"><?php esc_html_e( 'Job', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="date" data-esc-sort-type="date"><?php esc_html_e( 'Submitted', 'es-care-portal' ); ?></button></th>
					<th><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $applications ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="6"><?php esc_html_e( 'No applications yet.', 'es-care-portal' ); ?></td>
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
								'esc_view'     => 'applications',
								'application'  => $application->ID,
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
</div>

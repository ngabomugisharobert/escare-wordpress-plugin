<?php
/**
 * Portal admin jobs table.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $jobs
 */

defined( 'ABSPATH' ) || exit;

$jobs = is_array( $jobs ) ? $jobs : array();
?>
<div class="esc-dash-toolbar">
	<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Jobs', 'es-care-portal' ); ?></h2>
	<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'post-job' ) ); ?>"><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a>
</div>
<p class="esc-dash-copy"><?php esc_html_e( 'All job listings across employers. Search, filter by status, and sort columns.', 'es-care-portal' ); ?></p>

<div class="esc-card esc-data-panel">
	<div class="esc-data-toolbar" data-esc-table-toolbar="esc-admin-jobs">
		<label class="esc-data-search">
			<span class="screen-reader-text"><?php esc_html_e( 'Search jobs', 'es-care-portal' ); ?></span>
			<input type="search" class="esc-data-search-input" placeholder="<?php esc_attr_e( 'Search title, location, company…', 'es-care-portal' ); ?>">
		</label>
		<label class="esc-data-filter">
			<span><?php esc_html_e( 'Status', 'es-care-portal' ); ?></span>
			<select data-esc-filter="status">
				<option value=""><?php esc_html_e( 'All statuses', 'es-care-portal' ); ?></option>
				<?php foreach ( ESC_Portal_Helpers::job_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<p class="esc-data-count" aria-live="polite"></p>
	</div>

	<div class="esc-table-wrap">
		<table class="esc-table esc-data-table" id="esc-admin-jobs">
			<thead>
				<tr>
					<th><button type="button" class="esc-sort" data-esc-sort="title"><?php esc_html_e( 'Title', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="company"><?php esc_html_e( 'Company', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="location"><?php esc_html_e( 'Location', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="type"><?php esc_html_e( 'Type', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="status"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></button></th>
					<th><button type="button" class="esc-sort" data-esc-sort="date" data-esc-sort-type="date"><?php esc_html_e( 'Posted', 'es-care-portal' ); ?></button></th>
					<th><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $jobs ) ) : ?>
					<tr class="esc-data-empty">
						<td colspan="7"><?php esc_html_e( 'No jobs yet.', 'es-care-portal' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $jobs as $job ) : ?>
						<?php
						$status   = get_post_meta( $job->ID, '_esc_job_status', true );
						$status   = $status ? $status : 'open';
						$location = (string) get_post_meta( $job->ID, '_esc_location', true );
						$type     = (string) get_post_meta( $job->ID, '_esc_employment_type', true );
						$employer = absint( get_post_meta( $job->ID, '_esc_employer_id', true ) );
						$owner    = $employer ? ESC_Portal_Users::get( $employer ) : null;
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
									<a class="esc-button esc-button--ghost esc-button--small" href="<?php echo esc_url( get_permalink( $job ) ); ?>"><?php esc_html_e( 'View', 'es-care-portal' ); ?></a>
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
</div>

<?php
/**
 * Employer company jobs + applications.
 *
 * @package ESC_Portal
 *
 * @var WP_Post[] $jobs
 * @var WP_Post[] $applications
 * @var array     $review
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="esc-dash-panel">
	<h2 class="esc-dash-title"><?php esc_html_e( 'Company Jobs', 'es-care-portal' ); ?></h2>
	<p class="esc-dash-copy"><?php esc_html_e( 'Edit or remove your listings, and review candidates who have applied.', 'es-care-portal' ); ?></p>

	<div class="esc-actions">
		<a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'post' ) ); ?>"><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a>
	</div>

	<?php if ( ! empty( $review ) ) : ?>
		<?php include ESC_PORTAL_DIR . 'public/templates/partials/review-application.php'; ?>
	<?php endif; ?>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Your jobs', 'es-care-portal' ); ?></h3>
		<?php if ( empty( $jobs ) ) : ?>
			<p><?php esc_html_e( 'You have not posted any jobs yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<div class="esc-table-wrap">
				<table class="esc-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Title', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $jobs as $job ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_permalink( $job ) ); ?>"><?php echo esc_html( get_the_title( $job ) ); ?></a></td>
								<td><?php echo esc_html( ESC_Portal_Helpers::format_status( ESC_Portal_Helpers::listing_status( $job ) ) ); ?></td>
								<td>
									<div class="esc-row-action-group">
										<a class="esc-button esc-button--small" href="<?php echo esc_url( ESC_Portal_Helpers::job_edit_url( $job->ID ) ); ?>"><?php esc_html_e( 'Edit', 'es-care-portal' ); ?></a>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-esc-confirm="<?php echo esc_attr__( 'Remove this job listing?', 'es-care-portal' ); ?>">
											<?php wp_nonce_field( 'esc_delete_job_front', 'esc_delete_job_nonce' ); ?>
											<input type="hidden" name="action" value="esc_delete_job_front">
											<input type="hidden" name="esc_job_id" value="<?php echo esc_attr( (string) $job->ID ); ?>">
											<button type="submit" class="esc-button esc-button--danger esc-button--small"><?php esc_html_e( 'Remove', 'es-care-portal' ); ?></button>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Applications', 'es-care-portal' ); ?></h3>
		<?php if ( empty( $applications ) ) : ?>
			<p><?php esc_html_e( 'No applications yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<div class="esc-table-wrap">
				<table class="esc-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Applicant', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Job', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Review', 'es-care-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $applications as $application ) : ?>
							<?php $snap = ESC_Portal_CPT_Application::get_snapshot( $application->ID ); ?>
							<tr>
								<td><?php echo esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ); ?></td>
								<td><?php echo esc_html( get_the_title( $snap['job_id'] ) ); ?></td>
								<td><span class="esc-status esc-status--<?php echo esc_attr( $snap['status'] ); ?>"><?php echo esc_html( ESC_Portal_Helpers::format_status( $snap['status'] ) ); ?></span></td>
								<td><a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'jobs', array( 'application' => $application->ID ) ) ); ?>"><?php esc_html_e( 'Open', 'es-care-portal' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
</section>

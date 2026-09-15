<?php
/**
 * Portal admin dashboard.
 *
 * @package ESC_Portal
 *
 * @var object    $user
 * @var object[]  $users
 * @var WP_Post[] $jobs
 * @var WP_Post[] $applications
 * @var array     $review
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="esc-portal-wrap">
	<?php include ESC_PORTAL_DIR . 'public/templates/partials/account-nav.php'; ?>
	<header class="esc-hero">
		<p class="esc-kicker"><?php esc_html_e( 'Portal admin', 'es-care-portal' ); ?></p>
		<h2><?php esc_html_e( 'Users, jobs, and applications', 'es-care-portal' ); ?></h2>
	</header>

	<?php if ( ! empty( $review ) ) : ?>
		<?php include ESC_PORTAL_DIR . 'public/templates/partials/review-application.php'; ?>
	<?php endif; ?>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Portal users', 'es-care-portal' ); ?></h3>
		<table class="esc-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Email', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Role', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Update', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $users as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->display_name ); ?></td>
						<td><?php echo esc_html( $row->email ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="esc-inline-form">
								<?php wp_nonce_field( 'esc_portal_user', 'esc_user_nonce' ); ?>
								<input type="hidden" name="action" value="esc_portal_user">
								<input type="hidden" name="esc_user_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
								<select name="esc_role">
									<?php foreach ( ESC_Portal_Users::roles() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row->role, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="esc_status">
									<option value="active" <?php selected( $row->status, 'active' ); ?>><?php esc_html_e( 'Active', 'es-care-portal' ); ?></option>
									<option value="disabled" <?php selected( $row->status, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'es-care-portal' ); ?></option>
								</select>
								<button type="submit" class="esc-button"><?php esc_html_e( 'Save', 'es-care-portal' ); ?></button>
							</form>
						</td>
						<td><?php echo esc_html( $row->status ); ?></td>
						<td><?php echo esc_html( ESC_Portal_Users::role_label( $row->role ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Jobs', 'es-care-portal' ); ?></h3>
		<p><a class="esc-button" href="<?php echo esc_url( ESC_Portal_Helpers::get_page_url( 'post-job' ) ); ?>"><?php esc_html_e( 'Post a job', 'es-care-portal' ); ?></a></p>
		<?php if ( empty( $jobs ) ) : ?>
			<p><?php esc_html_e( 'No jobs yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<ul class="esc-job-list">
				<?php foreach ( $jobs as $job ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $job ) ); ?>"><?php echo esc_html( get_the_title( $job ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>

	<section class="esc-card">
		<h3><?php esc_html_e( 'Applications', 'es-care-portal' ); ?></h3>
		<?php if ( empty( $applications ) ) : ?>
			<p><?php esc_html_e( 'No applications yet.', 'es-care-portal' ); ?></p>
		<?php else : ?>
			<table class="esc-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Applicant', 'es-care-portal' ); ?></th>
						<th><?php esc_html_e( 'Job', 'es-care-portal' ); ?></th>
						<th><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $applications as $application ) : ?>
						<?php $snap = ESC_Portal_CPT_Application::get_snapshot( $application->ID ); ?>
						<tr>
							<td><?php echo esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ); ?></td>
							<td><?php echo esc_html( get_the_title( $snap['job_id'] ) ); ?></td>
							<td><?php echo esc_html( ESC_Portal_Helpers::format_status( $snap['status'] ) ); ?></td>
							<td><a href="<?php echo esc_url( add_query_arg( 'application', $application->ID, ESC_Portal_Helpers::get_page_url( 'dashboard' ) ) ); ?>"><?php esc_html_e( 'Review', 'es-care-portal' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</section>
</div>

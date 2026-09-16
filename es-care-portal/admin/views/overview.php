<?php
/**
 * Admin overview.
 *
 * @package ESC_Portal
 *
 * @var int      $pending_count Pending applications.
 * @var int      $open_jobs     Open jobs.
 * @var WP_Post[] $recent       Recent applications.
 * @var array<string,int> $user_counts Dashboard user counts.
 * @var string $table_name Dashboard users table.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $user_counts ) || ! is_array( $user_counts ) ) {
	$user_counts = array(
		'job_seeker' => 0,
		'employer'   => 0,
		'admin'      => 0,
	);
}

if ( empty( $table_name ) ) {
	$table_name = ESC_Portal_Users::table();
}
?>
<div class="wrap esc-admin">
	<h1><?php esc_html_e( 'ES Care Portal', 'es-care-portal' ); ?></h1>
	<p class="esc-admin-lede"><?php esc_html_e( 'Jobs, applicants, and hiring status for ES Care Services.', 'es-care-portal' ); ?></p>

	<div class="notice notice-info inline">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: database table name */
					__( 'Dashboard accounts (job seeker, employer, portal admin) live in %s. WordPress Administrators stay in Users → All Users and manage the site separately.', 'es-care-portal' ),
					$table_name
				)
			);
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=esc-portal-users' ) ); ?>"><?php esc_html_e( 'Manage dashboard users', 'es-care-portal' ); ?></a>
		</p>
	</div>

	<div class="esc-admin-stats">
		<a class="esc-admin-stat" href="<?php echo esc_url( admin_url( 'admin.php?page=esc-applications&esc_status=pending' ) ); ?>">
			<strong><?php echo esc_html( (string) $pending_count ); ?></strong>
			<span><?php esc_html_e( 'Pending applications', 'es-care-portal' ); ?></span>
		</a>
		<a class="esc-admin-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=esc_job' ) ); ?>">
			<strong><?php echo esc_html( (string) $open_jobs ); ?></strong>
			<span><?php esc_html_e( 'Open jobs', 'es-care-portal' ); ?></span>
		</a>
		<a class="esc-admin-stat" href="<?php echo esc_url( admin_url( 'admin.php?page=esc-portal-users' ) ); ?>">
			<strong><?php echo esc_html( (string) ( (int) $user_counts['job_seeker'] + (int) $user_counts['employer'] + (int) $user_counts['admin'] ) ); ?></strong>
			<span><?php esc_html_e( 'Dashboard users', 'es-care-portal' ); ?></span>
		</a>
	</div>

	<h2><?php esc_html_e( 'Recent applications', 'es-care-portal' ); ?></h2>
	<?php if ( empty( $recent ) ) : ?>
		<p><?php esc_html_e( 'No applications yet.', 'es-care-portal' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Applicant', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Job', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Status', 'es-care-portal' ); ?></th>
					<th><?php esc_html_e( 'Submitted', 'es-care-portal' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent as $item ) : ?>
					<?php
					$snap = ESC_Portal_CPT_Application::get_snapshot( $item->ID );
					$url  = add_query_arg(
						array(
							'page' => 'esc-application',
							'id'   => $item->ID,
						),
						admin_url( 'admin.php' )
					);
					?>
					<tr>
						<td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( trim( $snap['first_name'] . ' ' . $snap['last_name'] ) ); ?></a></td>
						<td><?php echo esc_html( get_the_title( $snap['job_id'] ) ); ?></td>
						<td><span class="esc-status esc-status--<?php echo esc_attr( $snap['status'] ); ?>"><?php echo esc_html( ESC_Portal_Helpers::format_status( $snap['status'] ) ); ?></span></td>
						<td><?php echo esc_html( get_the_date( get_option( 'date_format' ), $item ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

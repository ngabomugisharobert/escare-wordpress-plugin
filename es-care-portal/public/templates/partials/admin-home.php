<?php
/**
 * Portal admin overview.
 *
 * @package ESC_Portal
 *
 * @var object    $user
 * @var object[]  $users
 * @var WP_Post[] $jobs
 * @var WP_Post[] $applications
 * @var array     $user_counts
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $user_counts ) || ! is_array( $user_counts ) ) {
	$user_counts = ESC_Portal_Users::counts_by_role();
}

$pending = 0;
foreach ( (array) $applications as $application ) {
	$snap = ESC_Portal_CPT_Application::get_snapshot( $application->ID );
	if ( 'pending' === $snap['status'] ) {
		++$pending;
	}
}

$open_jobs = 0;
foreach ( (array) $jobs as $job ) {
	if ( 'open' === get_post_meta( $job->ID, '_esc_job_status', true ) ) {
		++$open_jobs;
	}
}

$total_users = (int) $user_counts['job_seeker'] + (int) $user_counts['employer'] + (int) $user_counts['admin'];
?>
<h2 class="esc-dash-title esc-dash-title--rule"><?php esc_html_e( 'Overview', 'es-care-portal' ); ?></h2>
<p class="esc-dash-copy">
	<?php
	echo esc_html(
		sprintf(
			/* translators: %s: admin first name */
			__( 'Welcome back, %s. Manage dashboard users, jobs, and applications from the menus.', 'es-care-portal' ),
			$user->first_name ? $user->first_name : $user->display_name
		)
	);
	?>
</p>

<div class="esc-admin-dash-stats">
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'users' ) ); ?>">
		<strong><?php echo esc_html( (string) $total_users ); ?></strong>
		<span><?php esc_html_e( 'Users', 'es-care-portal' ); ?></span>
	</a>
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'jobs' ) ); ?>">
		<strong><?php echo esc_html( (string) $open_jobs ); ?></strong>
		<span><?php esc_html_e( 'Open jobs', 'es-care-portal' ); ?></span>
	</a>
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'applications' ) ); ?>">
		<strong><?php echo esc_html( (string) $pending ); ?></strong>
		<span><?php esc_html_e( 'Pending applications', 'es-care-portal' ); ?></span>
	</a>
</div>

<div class="esc-tiles esc-tiles--account" style="margin-top:1.5rem;">
	<a class="esc-tile" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'users' ) ); ?>">
		<span class="esc-tile-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><circle cx="17" cy="9" r="2.5"/><path d="M14.5 19a4.5 4.5 0 0 1 6.5-4"/></svg>
		</span>
		<?php esc_html_e( 'Users', 'es-care-portal' ); ?>
	</a>
	<a class="esc-tile" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'jobs' ) ); ?>">
		<span class="esc-tile-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
		</span>
		<?php esc_html_e( 'Jobs', 'es-care-portal' ); ?>
	</a>
	<a class="esc-tile" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'applications' ) ); ?>">
		<span class="esc-tile-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4M9 13h6M9 17h4"/></svg>
		</span>
		<?php esc_html_e( 'Applications', 'es-care-portal' ); ?>
	</a>
</div>

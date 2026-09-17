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

$pending = isset( $home_metrics['pending_apps'] ) ? (int) $home_metrics['pending_apps'] : 0;
$open_jobs = isset( $home_metrics['open_jobs'] ) ? (int) $home_metrics['open_jobs'] : 0;
$pending_jobs = isset( $home_metrics['pending_jobs'] ) ? (int) $home_metrics['pending_jobs'] : 0;

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
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( add_query_arg( array( 'esc_status' => 'pending', '_esc_table' => wp_create_nonce( 'esc_portal_table' ) ), ESC_Portal_Helpers::dashboard_url( 'jobs' ) ) ); ?>">
		<strong><?php echo esc_html( (string) $pending_jobs ); ?></strong>
		<span><?php esc_html_e( 'Jobs awaiting review', 'es-care-portal' ); ?></span>
	</a>
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'applications' ) ); ?>">
		<strong><?php echo esc_html( (string) $pending ); ?></strong>
		<span><?php esc_html_e( 'Pending applications', 'es-care-portal' ); ?></span>
	</a>
	<a class="esc-admin-dash-stat" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'contact' ) ); ?>">
		<strong><?php echo esc_html( (string) ( isset( $home_metrics['contacts'] ) ? (int) $home_metrics['contacts'] : 0 ) ); ?></strong>
		<span><?php esc_html_e( 'Contact messages', 'es-care-portal' ); ?></span>
	</a>
</div>

<div class="esc-tiles" style="margin-top:1.5rem;">
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
	<a class="esc-tile" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'contact' ) ); ?>">
		<span class="esc-tile-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
		</span>
		<?php esc_html_e( 'Contact Us', 'es-care-portal' ); ?>
	</a>
</div>

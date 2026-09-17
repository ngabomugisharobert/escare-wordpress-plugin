<?php
/**
 * Plugin uninstall.
 *
 * By default, portal data is preserved. Administrators can opt in to a full
 * wipe from Settings → Privacy and retention.
 *
 * @package ESC_Portal
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-roles.php';

$settings = get_option( 'esc_portal_settings', array() );
$wipe     = is_array( $settings ) && ! empty( $settings['delete_data_on_uninstall'] );

ESC_Portal_Roles::remove_roles();

if ( ! $wipe ) {
	delete_option( 'esc_portal_version' );
	flush_rewrite_rules();
	return;
}

global $wpdb;

$pages = get_option( 'esc_portal_pages', array() );
$signature = array(
	'[esc_register]',
	'[esc_login]',
	'[esc_dashboard]',
	'[esc_profile]',
	'[esc_jobs]',
	'[esc_apply]',
	'[esc_job_form]',
	'[esc_lost_password]',
	'[esc_reset_password]',
);

if ( is_array( $pages ) ) {
	foreach ( $pages as $page_id ) {
		$page_id = absint( $page_id );
		$page    = $page_id ? get_post( $page_id ) : null;

		if ( ! $page || 'page' !== $page->post_type ) {
			continue;
		}

		$content = (string) $page->post_content;
		foreach ( $signature as $shortcode ) {
			if ( false !== strpos( $content, $shortcode ) ) {
				wp_trash_post( $page_id );
				break;
			}
		}
	}
}

$cpts = array( 'esc_job', 'esc_application' );
foreach ( $cpts as $type ) {
	$ids = get_posts(
		array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
		)
	);

	foreach ( $ids as $id ) {
		$file = get_post_meta( $id, '_esc_resume_file', true );
		if ( $file ) {
			$basename = basename( (string) $file );
			$dirs     = array();
			if ( defined( 'ESC_PORTAL_PRIVATE_DIR' ) && ESC_PORTAL_PRIVATE_DIR ) {
				$dirs[] = trailingslashit( ESC_PORTAL_PRIVATE_DIR ) . 'esc-resumes';
			}
			$dirs[] = trailingslashit( dirname( ABSPATH ) ) . 'esc-portal-private/esc-resumes';
			$uploads = wp_upload_dir();
			if ( ! empty( $uploads['basedir'] ) ) {
				$dirs[] = trailingslashit( $uploads['basedir'] ) . 'esc-resumes';
			}
			foreach ( $dirs as $dir ) {
				$path = trailingslashit( $dir ) . $basename;
				if ( is_file( $path ) ) {
					wp_delete_file( $path );
				}
			}
		}
		wp_delete_post( $id, true );
	}
}

$tables = array(
	$wpdb->prefix . 'esc_users',
	$wpdb->prefix . 'esc_usermeta',
	$wpdb->prefix . 'esc_sessions',
	$wpdb->prefix . 'esc_assessments',
	$wpdb->prefix . 'esc_assessment_questions',
	$wpdb->prefix . 'esc_assessment_attempts',
	$wpdb->prefix . 'esc_forms',
	$wpdb->prefix . 'esc_requests',
	$wpdb->prefix . 'esc_rate_limits',
	$wpdb->prefix . 'esc_mail_queue',
	$wpdb->prefix . 'esc_logs',
);

foreach ( $tables as $table ) {
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

wp_clear_scheduled_hook( 'esc_portal_process_mail_queue' );
wp_clear_scheduled_hook( 'esc_portal_process_mail_queue_fast' );
wp_clear_scheduled_hook( 'esc_portal_retention_cleanup' );

delete_option( 'esc_portal_pages' );
delete_option( 'esc_portal_settings' );
delete_option( 'esc_portal_version' );
delete_option( 'esc_portal_schema_version' );
delete_option( 'esc_portal_identity_purged' );
delete_option( 'esc_portal_retention_last_run' );

flush_rewrite_rules();

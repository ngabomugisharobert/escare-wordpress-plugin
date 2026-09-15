<?php
/**
 * Plugin uninstall.
 *
 * Removes roles, pages created by the plugin, and options.
 * Applications, jobs, and resume files are left in place.
 *
 * @package ESC_Portal
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-roles.php';

ESC_Portal_Roles::remove_roles();

$pages = get_option( 'esc_portal_pages', array() );

if ( is_array( $pages ) ) {
	foreach ( $pages as $page_id ) {
		$page_id = absint( $page_id );

		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}
}

delete_option( 'esc_portal_pages' );
delete_option( 'esc_portal_settings' );
delete_option( 'esc_portal_version' );

flush_rewrite_rules();

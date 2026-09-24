<?php
/**
 * Plugin Name:       ES Care Portal
 * Plugin URI:        https://escare-services.com
 * Description:       Applicant registration, login, job listings, applications, and Elementor-ready portal modules for ES Care Services LLC.
 * Version:           2.0.30
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            ES Care Services LLC
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       es-care-portal
 * Domain Path:       /languages
 *
 * @package ESC_Portal
 */

defined( 'ABSPATH' ) || exit;

define( 'ESC_PORTAL_VERSION', '2.0.30' );
define( 'ESC_PORTAL_FILE', __FILE__ );
define( 'ESC_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'ESC_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'ESC_PORTAL_BASENAME', plugin_basename( __FILE__ ) );

$esc_portal_includes = array(
	'class-helpers.php',
	'class-users.php',
	'class-roles.php',
	'class-health.php',
	'class-rate-limit.php',
	'class-csrf.php',
	'class-uploads.php',
	'class-cpt-job.php',
	'class-cpt-application.php',
	'class-emails.php',
	'class-mail-queue.php',
	'class-privacy.php',
	'class-schema.php',
	'class-auth.php',
	'class-profile.php',
	'class-apply.php',
	'class-employer.php',
	'class-assessments.php',
	'class-forms.php',
	'class-account.php',
	'class-security.php',
	'class-blocks.php',
	'class-shortcodes.php',
	'class-elementor.php',
	'class-admin.php',
	'class-activator.php',
	'class-plugin.php',
);

foreach ( $esc_portal_includes as $esc_portal_file ) {
	require_once ESC_PORTAL_DIR . 'includes/' . $esc_portal_file;
}

register_activation_hook( ESC_PORTAL_FILE, array( 'ESC_Portal_Activator', 'activate' ) );
register_deactivation_hook( ESC_PORTAL_FILE, array( 'ESC_Portal_Activator', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'ESC_Portal_Plugin', 'instance' ) );

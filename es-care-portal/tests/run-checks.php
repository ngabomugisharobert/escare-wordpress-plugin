<?php
/**
 * Static checks and PHP syntax validation for ES Care Portal 2.0.
 *
 * Usage: php tests/run-checks.php
 *
 * @package ESC_Portal
 */

$root  = dirname( __DIR__ );
$fails = 0;

function esc_check_fail( &$fails, $message ) {
	$fails++;
	fwrite( STDERR, "FAIL: {$message}\n" );
}

function esc_check_pass( $message ) {
	fwrite( STDOUT, "OK: {$message}\n" );
}

$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root ) );
$php_files = array();

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
		continue;
	}

	$path = $file->getPathname();
	if ( false !== strpos( $path, '/vendor/' ) ) {
		continue;
	}

	$php_files[] = $path;
	$output      = array();
	$code        = 0;
	exec( ( defined( 'PHP_BINARY' ) && PHP_BINARY ? PHP_BINARY : 'php' ) . ' -l ' . escapeshellarg( $path ) . ' 2>&1', $output, $code );

	if ( 0 !== $code ) {
		esc_check_fail( $fails, implode( "\n", $output ) );
	}
}

if ( ! $fails ) {
	esc_check_pass( 'PHP syntax for ' . count( $php_files ) . ' files' );
}

$needles = array(
	'UNIQUE KEY user_meta'            => $root . '/includes/class-users.php',
	'pending_email'                   => $root . '/includes/class-users.php',
	'STATUS_PENDING_ADMIN'            => $root . '/includes/class-users.php',
	'ON DUPLICATE KEY UPDATE'         => $root . '/includes/class-users.php',
	'ESC_PORTAL_PRIVATE_DIR'          => $root . '/includes/class-uploads.php',
	'Private file storage is unavailable' => $root . '/includes/class-uploads.php',
	'esc_browser_token'               => $root . '/includes/class-csrf.php',
	'ESC_PORTAL_TRUSTED_PROXIES'      => $root . '/includes/class-rate-limit.php',
	'aes-256-gcm'                     => $root . '/includes/class-emails.php',
	'esc_portal_process_mail_queue'   => $root . '/includes/class-mail-queue.php',
	'purge_identity_fields'           => $root . '/includes/class-privacy.php',
	'retention_years'                 => $root . '/includes/class-helpers.php',
	'delete_data_on_uninstall'        => $root . '/uninstall.php',
	'scope="col"'                     => $root . '/public/templates/partials/admin-users.php',
	'aria-sort'                       => $root . '/includes/class-helpers.php',
	'esc_approve_employer'            => $root . '/public/templates/partials/admin-users.php',
	'esc_moderate_job'                => $root . '/public/templates/partials/admin-jobs.php',
	'esc_contact'                     => $root . '/public/templates/contact.php',
	'table_request'                   => $root . '/includes/class-shortcodes.php',
	'zeroResults'                     => $root . '/public/js/portal.js',
	'maybe_upgrade'                    => $root . '/includes/class-plugin.php',
);

foreach ( $needles as $needle => $file ) {
	$contents = file_get_contents( $file );
	if ( false === strpos( $contents, $needle ) ) {
		esc_check_fail( $fails, $file . ' is missing "' . $needle . '"' );
	} else {
		esc_check_pass( basename( $file ) . ' contains ' . $needle );
	}
}

$ssn_files = array(
	$root . '/public/templates/partials/application-fields.php',
	$root . '/admin/views/application-detail.php',
	$root . '/includes/class-apply.php',
);

foreach ( $ssn_files as $file ) {
	$contents = file_get_contents( $file );
	if ( preg_match( '/esc_ssn|Social Security|_esc_ssn|drivers_license/', $contents ) ) {
		esc_check_fail( $fails, $file . ' still references SSN or driver’s license collection' );
	} else {
		esc_check_pass( basename( $file ) . ' has no SSN/license collection fields' );
	}
}

$uninstall = file_get_contents( $root . '/uninstall.php' );
if ( false === strpos( $uninstall, 'delete_data_on_uninstall' ) || false === strpos( $uninstall, 'return;' ) ) {
	esc_check_fail( $fails, 'uninstall.php must preserve data unless deletion is selected' );
} else {
	esc_check_pass( 'uninstall.php preserves data by default' );
}

$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
if ( preg_match( '/ensure_tables\(\)|Roles::add_roles\(/', $plugin ) ) {
	esc_check_fail( $fails, 'bootstrap still writes schema/roles on every request' );
} else {
	esc_check_pass( 'plugin bootstrap no longer runs ensure_tables/add_roles per request' );
}

if ( $fails ) {
	fwrite( STDERR, "\n{$fails} check(s) failed.\n" );
	exit( 1 );
}

fwrite( STDOUT, "\nAll static checks passed.\n" );
exit( 0 );

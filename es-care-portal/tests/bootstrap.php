<?php
/**
 * PHPUnit bootstrap.
 *
 * Set WP_TESTS_DIR to a WordPress test suite to run integration tests.
 *
 * @package ESC_Portal
 */

$esc_wp_tests = getenv( 'WP_TESTS_DIR' );

if ( ! $esc_wp_tests ) {
	$esc_wp_tests = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( file_exists( $esc_wp_tests . '/includes/functions.php' ) ) {
	require_once $esc_wp_tests . '/includes/functions.php';

	tests_add_filter(
		'muplugins_loaded',
		static function () {
			require dirname( __DIR__ ) . '/es-care-portal.php';
		}
	);

	require $esc_wp_tests . '/includes/bootstrap.php';
	return;
}

if ( ! class_exists( 'PHPUnit\\Framework\\TestCase' ) && ! class_exists( 'PHPUnit_Framework_TestCase' ) ) {
	return;
}

<?php
/**
 * WordPress integration tests for the portal improvement program.
 *
 * These tests skip when WP_TESTS_DIR is not available. Run
 * `php tests/run-checks.php` for syntax and static verification.
 *
 * @package ESC_Portal
 */

if ( ! class_exists( 'WP_UnitTestCase' ) ) {
	return;
}

class ESC_Portal_Improvement_Tests extends WP_UnitTestCase {

	public function test_schema_target_is_versioned() {
		$this->assertSame( 2, ESC_Portal_Schema::TARGET );
	}

	public function test_employer_statuses_include_verification_and_approval() {
		$statuses = ESC_Portal_Users::statuses();
		$this->assertArrayHasKey( 'pending_email', $statuses );
		$this->assertArrayHasKey( 'pending_admin', $statuses );
		$this->assertArrayHasKey( 'active', $statuses );
		$this->assertArrayHasKey( 'disabled', $statuses );
	}

	public function test_table_request_rejects_unknown_orderby() {
		$_GET = array(
			'esc_orderby' => 'password',
			'esc_order'   => 'ASC',
			'esc_paged'   => '2',
			'esc_per_page'=> '500',
			'_esc_table'  => wp_create_nonce( 'esc_portal_table' ),
		);

		$req = ESC_Portal_Helpers::table_request( array( 'email', 'status' ) );
		$this->assertSame( 'email', $req['orderby'] );
		$this->assertSame( 100, $req['number'] );
		$this->assertSame( 2, $req['paged'] );
	}

	public function test_identity_purge_keys_are_ssn_and_license_only() {
		$this->assertSame( array( '_esc_ssn', '_esc_drivers_license' ), ESC_Portal_Privacy::IDENTITY_KEYS );
	}

	public function test_job_statuses_include_moderation_states() {
		$statuses = ESC_Portal_Helpers::job_statuses();
		$this->assertArrayHasKey( 'pending', $statuses );
		$this->assertArrayHasKey( 'rejected', $statuses );
	}

	public function test_retention_default_is_three_years() {
		$settings = ESC_Portal_Helpers::default_settings();
		$this->assertSame( 3, (int) $settings['retention_years'] );
		$this->assertSame( 0, (int) $settings['delete_data_on_uninstall'] );
	}

	public function test_csrf_field_name() {
		$this->assertSame( 'esc_browser_token', ESC_Portal_CSRF::FIELD );
	}

	public function test_rate_limit_actions_are_named() {
		$limits = ESC_Portal_Rate_Limit::limits();
		foreach ( array( 'register', 'job_publish', 'apply', 'request', 'assessment', 'verify_resend', 'contact', 'lost_password' ) as $action ) {
			$this->assertArrayHasKey( $action, $limits );
		}
	}

	public function test_public_registration_cannot_create_portal_admin() {
		$this->assertNotContains( ESC_Portal_Users::ROLE_ADMIN, ESC_Portal_Users::public_roles() );
	}

	public function test_private_storage_is_outside_uploads_by_default() {

	public function test_private_storage_is_outside_uploads_by_default() {
		$dir = ESC_Portal_Uploads::directory();
		$this->assertStringNotContainsString( '/uploads/esc-resumes', wp_normalize_path( $dir ) );
	}

	public function test_strong_password_rules() {
		$this->assertFalse( ESC_Portal_Users::is_strong_password( 'short' ) );
		$this->assertTrue( ESC_Portal_Users::is_strong_password( 'ValidPass1' ) );
	}
}

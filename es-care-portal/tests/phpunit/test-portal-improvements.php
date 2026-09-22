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
		$this->assertSame( 3, ESC_Portal_Schema::TARGET );
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
		$this->assertSame( array( ESC_Portal_Users::ROLE_SEEKER, ESC_Portal_Users::ROLE_EMPLOYER ), ESC_Portal_Users::public_roles() );
	}

	public function test_private_storage_is_outside_uploads_by_default() {
		$dir = ESC_Portal_Uploads::directory();
		$this->assertStringNotContainsString( '/uploads/esc-resumes', wp_normalize_path( $dir ) );
	}

	public function test_strong_password_rules() {
		$this->assertFalse( ESC_Portal_Users::is_strong_password( 'short' ) );
		$this->assertTrue( ESC_Portal_Users::is_strong_password( 'ValidPass1' ) );
	}

	public function test_portal_roles_are_mutually_exclusive() {
		$seeker   = (object) array( 'role' => ESC_Portal_Users::ROLE_SEEKER );
		$employer = (object) array( 'role' => ESC_Portal_Users::ROLE_EMPLOYER );
		$admin    = (object) array( 'role' => ESC_Portal_Users::ROLE_ADMIN );

		$this->assertTrue( ESC_Portal_Users::is_seeker( $seeker ) );
		$this->assertFalse( ESC_Portal_Users::is_employer( $seeker ) );
		$this->assertFalse( ESC_Portal_Users::is_admin( $seeker ) );

		$this->assertTrue( ESC_Portal_Users::is_employer( $employer ) );
		$this->assertFalse( ESC_Portal_Users::is_seeker( $employer ) );
		$this->assertFalse( ESC_Portal_Users::is_admin( $employer ) );

		$this->assertTrue( ESC_Portal_Users::is_admin( $admin ) );
		$this->assertFalse( ESC_Portal_Users::is_seeker( $admin ) );
		$this->assertFalse( ESC_Portal_Users::is_employer( $admin ) );
	}

	public function test_dashboard_view_allowlists_match_role_menus() {
		$seeker = ESC_Portal_Helpers::dashboard_views( 'seeker' );
		$this->assertContains( 'apply', $seeker );
		$this->assertContains( 'assessments', $seeker );
		$this->assertNotContains( 'users', $seeker );

		$employer = ESC_Portal_Helpers::dashboard_views( 'employer' );
		$this->assertContains( 'jobs', $employer );
		$this->assertContains( 'post', $employer );
		$this->assertNotContains( 'apply', $employer );

		$admin = ESC_Portal_Helpers::dashboard_views( 'admin' );
		$this->assertContains( 'conduct', $seeker );
		$this->assertContains( 'conduct', $employer );
		$this->assertSame( array( 'home', 'users', 'jobs', 'applications', 'conduct' ), $admin );
		$this->assertNotContains( 'contact', $admin );
		$this->assertNotContains( 'apply', $admin );
	}

	public function test_unknown_admin_view_falls_back_to_home() {
		$_GET['esc_view'] = 'contact';
		$this->assertSame( 'home', ESC_Portal_Helpers::current_dashboard_view( 'admin' ) );
		$_GET['esc_view'] = 'users';
		$this->assertSame( 'users', ESC_Portal_Helpers::current_dashboard_view( 'admin' ) );
		unset( $_GET['esc_view'] );
	}

	public function test_can_apply_to_jobs_is_guest_or_seeker_only() {
		$seeker   = (object) array( 'role' => ESC_Portal_Users::ROLE_SEEKER );
		$employer = (object) array( 'role' => ESC_Portal_Users::ROLE_EMPLOYER );
		$admin    = (object) array( 'role' => ESC_Portal_Users::ROLE_ADMIN );

		$this->assertTrue( ESC_Portal_Users::can_apply_to_jobs( null ) );
		$this->assertTrue( ESC_Portal_Users::can_apply_to_jobs( $seeker ) );
		$this->assertFalse( ESC_Portal_Users::can_apply_to_jobs( $employer ) );
		$this->assertFalse( ESC_Portal_Users::can_apply_to_jobs( $admin ) );
	}

	public function test_careers_and_single_job_templates_use_portal_apply_helper() {
		$jobs   = file_get_contents( ESC_PORTAL_DIR . 'public/templates/jobs.php' );
		$single = file_get_contents( ESC_PORTAL_DIR . 'public/templates/single-job.php' );
		$this->assertStringContainsString( 'can_apply_to_jobs', $jobs );
		$this->assertStringContainsString( 'can_apply_to_jobs', $single );
		$this->assertStringNotContainsString( "current_user_can( 'manage_options' )", $jobs );
		$this->assertStringNotContainsString( 'is_user_logged_in()', $single );
	}

	public function test_portal_admin_sidebar_omits_contact_us() {
		$sidebar = file_get_contents( ESC_PORTAL_DIR . 'public/templates/partials/admin-sidebar.php' );
		$this->assertStringNotContainsString( "'contact'", $sidebar );
		$this->assertStringContainsString( "'applications'", $sidebar );
		$this->assertStringContainsString( "'conduct'", $sidebar );
	}
}

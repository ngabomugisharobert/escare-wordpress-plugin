<?php
/**
 * Job seeker dashboard.
 *
 * @package ESC_Portal
 *
 * @var object    $user
 * @var bool      $profile_complete
 * @var WP_Post[] $applications
 * @var string    $view
 * @var object[]  $assessments
 * @var object[]  $attempts
 * @var object[]  $forms
 * @var object[]  $requests
 * @var WP_Post[] $jobs
 * @var object    $assessment
 * @var object[]  $questions
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $view ) ) {
	$view = 'home';
}

$partial = ESC_PORTAL_DIR . 'public/templates/partials/seeker-' . $view . '.php';

if ( ! file_exists( $partial ) ) {
	$partial = ESC_PORTAL_DIR . 'public/templates/partials/seeker-home.php';
	$view    = 'home';
}
?>
<div class="esc-portal-wrap esc-dash-wrap">
	<div class="esc-dash">
		<?php include ESC_PORTAL_DIR . 'public/templates/partials/seeker-sidebar.php'; ?>
		<div class="esc-dash-main">
			<?php echo ESC_Portal_Helpers::render_query_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php include $partial; ?>
		</div>
	</div>
</div>

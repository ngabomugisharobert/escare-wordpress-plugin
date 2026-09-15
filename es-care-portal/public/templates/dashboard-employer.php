<?php
/**
 * Employer dashboard shell.
 *
 * @package ESC_Portal
 *
 * @var object    $user
 * @var string    $view
 * @var WP_Post[] $jobs
 * @var WP_Post[] $applications
 * @var array     $profile
 * @var object[]  $requests
 * @var array     $review
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $view ) ) {
	$view = 'home';
}

$partial = ESC_PORTAL_DIR . 'public/templates/partials/employer-' . $view . '.php';

if ( ! file_exists( $partial ) ) {
	$partial = ESC_PORTAL_DIR . 'public/templates/partials/employer-home.php';
	$view    = 'home';
}
?>
<div class="esc-portal-wrap esc-dash-wrap">
	<div class="esc-dash">
		<?php include ESC_PORTAL_DIR . 'public/templates/partials/employer-sidebar.php'; ?>
		<div class="esc-dash-main">
			<?php echo ESC_Portal_Helpers::render_query_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php include $partial; ?>
		</div>
	</div>
</div>

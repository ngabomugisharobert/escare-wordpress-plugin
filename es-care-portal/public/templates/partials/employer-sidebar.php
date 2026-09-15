<?php
/**
 * Employer dashboard sidebar.
 *
 * @package ESC_Portal
 *
 * @var string $view
 */

defined( 'ABSPATH' ) || exit;

$items = array(
	'home'     => __( 'My Dashboard', 'es-care-portal' ),
	'profile'  => __( 'Company Profile', 'es-care-portal' ),
	'jobs'     => __( 'Company Jobs', 'es-care-portal' ),
	'password' => __( 'Change Password', 'es-care-portal' ),
);
$active = $view;

if ( 'post' === $active ) {
	$active = 'jobs';
}
?>
<aside class="esc-side" aria-label="<?php esc_attr_e( 'Employer menu', 'es-care-portal' ); ?>">
	<p class="esc-side-kicker"><?php esc_html_e( 'For Employers/Companies', 'es-care-portal' ); ?></p>
	<nav class="esc-side-nav">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
	<a class="esc-side-cta" href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( 'request' ) ); ?>"<?php echo 'request' === $view ? ' aria-current="page"' : ''; ?>>
		<span class="esc-side-cta-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 5h11a2 2 0 0 1 2 2v12H7a2 2 0 0 1-2-2V5z"/><path d="M7 5V3.8A1.8 1.8 0 0 1 8.8 2H19a2 2 0 0 1 2 2v13"/><path d="M8 10h8M8 14h5"/></svg>
		</span>
		<span>
			<strong><?php esc_html_e( 'Service Request', 'es-care-portal' ); ?></strong>
			<em><?php esc_html_e( 'Request any of our Services >>', 'es-care-portal' ); ?></em>
		</span>
	</a>
</aside>

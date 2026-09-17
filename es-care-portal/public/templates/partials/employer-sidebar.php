<?php
/**
 * Employer dashboard secondary menu.
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
	'request'  => __( 'Service Request', 'es-care-portal' ),
);
$active = $view;

if ( 'post' === $active ) {
	$active = 'jobs';
}
?>
<header class="esc-side esc-subnav" aria-label="<?php esc_attr_e( 'Employer menu', 'es-care-portal' ); ?>">
	<nav class="esc-side-nav esc-subnav-links">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a class="esc-subnav-logout" href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
</header>

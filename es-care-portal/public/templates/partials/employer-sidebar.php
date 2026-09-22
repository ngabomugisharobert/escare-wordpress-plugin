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
	'conduct'  => __( 'Code of Conduct', 'es-care-portal' ),
);
$active = $view;

if ( 'post' === $active ) {
	$active = 'jobs';
}

$current_label = isset( $items[ $active ] ) ? $items[ $active ] : __( 'Menu', 'es-care-portal' );
?>
<header class="esc-side esc-subnav">
	<p class="esc-side-kicker esc-subnav-kicker esc-subnav-kicker--bar"><?php esc_html_e( 'Employer', 'es-care-portal' ); ?></p>
	<button
		type="button"
		class="esc-subnav-toggle"
		aria-expanded="false"
		aria-controls="esc-dash-nav-employer"
		aria-label="<?php echo esc_attr( sprintf( __( 'Dashboard menu, %s', 'es-care-portal' ), $current_label ) ); ?>"
	>
		<span class="esc-subnav-toggle-copy">
			<span class="esc-side-kicker esc-subnav-kicker"><?php esc_html_e( 'Employer', 'es-care-portal' ); ?></span>
			<span class="esc-subnav-current"><?php echo esc_html( $current_label ); ?></span>
		</span>
		<span class="esc-subnav-chevron" aria-hidden="true"></span>
	</button>
	<nav id="esc-dash-nav-employer" class="esc-side-nav esc-subnav-links" aria-label="<?php esc_attr_e( 'Employer menu', 'es-care-portal' ); ?>">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a class="esc-subnav-logout" href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
</header>

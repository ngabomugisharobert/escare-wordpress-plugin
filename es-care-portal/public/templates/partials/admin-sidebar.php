<?php
/**
 * Portal admin secondary menu.
 *
 * @package ESC_Portal
 *
 * @var string $view
 */

defined( 'ABSPATH' ) || exit;

$items = array(
	'home'         => __( 'Overview', 'es-care-portal' ),
	'users'        => __( 'Users', 'es-care-portal' ),
	'jobs'         => __( 'Jobs', 'es-care-portal' ),
	'applications' => __( 'Applications', 'es-care-portal' ),
	'assessments'  => __( 'Assessments', 'es-care-portal' ),
	'conduct'      => __( 'Code of Conduct', 'es-care-portal' ),
);

$active = $view;

if ( 'post' === $active ) {
	$active = 'jobs';
}

if ( 'assessment' === $active ) {
	$active = 'assessments';
}

$current_label = isset( $items[ $active ] ) ? $items[ $active ] : __( 'Menu', 'es-care-portal' );
?>
<header class="esc-side esc-subnav">
	<p class="esc-side-kicker esc-subnav-kicker esc-subnav-kicker--bar"><?php esc_html_e( 'Portal Admin', 'es-care-portal' ); ?></p>
	<button
		type="button"
		class="esc-subnav-toggle"
		aria-expanded="false"
		aria-controls="esc-dash-nav-admin"
		aria-label="<?php echo esc_attr( sprintf( __( 'Dashboard menu, %s', 'es-care-portal' ), $current_label ) ); ?>"
	>
		<span class="esc-subnav-toggle-copy">
			<span class="esc-side-kicker esc-subnav-kicker"><?php esc_html_e( 'Portal Admin', 'es-care-portal' ); ?></span>
			<span class="esc-subnav-current"><?php echo esc_html( $current_label ); ?></span>
		</span>
		<span class="esc-subnav-chevron" aria-hidden="true"></span>
	</button>
	<nav id="esc-dash-nav-admin" class="esc-side-nav esc-subnav-links" aria-label="<?php esc_attr_e( 'Admin menu', 'es-care-portal' ); ?>">
		<?php foreach ( $items as $key => $label ) : ?>
			<a href="<?php echo esc_url( ESC_Portal_Helpers::dashboard_url( $key ) ); ?>"<?php echo $active === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a class="esc-subnav-logout" href="<?php echo esc_url( ESC_Portal_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'es-care-portal' ); ?></a>
	</nav>
</header>
